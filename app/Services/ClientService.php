<?php

namespace App\Services;

use App\Models\Clients;
use App\Models\Projects;
use App\Models\Recoveries;
use App\Models\Eselicenses;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ClientService extends BaseService
{
    /**
     * Get aggregated recovery data for all projects.
     */
    public function getRecoveriesSummary()
    {
        return Recoveries::leftJoin('clients', 'recoveries.client_id', '=', 'clients.id')
            ->leftJoin('projects', 'recoveries.project_id', '=', 'projects.id')
            ->select(
                'projects.id',
                'projects.batchNo',
                'clients.name',
                'clients.company',
                'clients.mob',
                'clients.whatsapp',
                'clients.industry',
                'clients.email',
                'clients.poc',
                DB::raw('MAX(projects.name) as project'),
                DB::raw('MAX(projects.amount) as project_amount'),
                DB::raw('MAX(projects.note) as project_note'),
                DB::raw('MAX(projects.deployment_url) as deployment_url'),
                DB::raw('MAX(projects.amount) - COALESCE(SUM(recoveries.paid), 0) as remaining_amount'),
                DB::raw('MAX(recoveries.reminder) as reminder'),
                DB::raw('CASE WHEN MAX(projects.amount) - COALESCE(SUM(recoveries.paid), 0) <= 0 THEN 1 ELSE 0 END as status')
            )
            ->groupBy(
                'projects.id',
                'projects.batchNo',
                'clients.name',
                'clients.company',
                'clients.mob',
                'clients.whatsapp',
                'clients.industry',
                'clients.email',
                'clients.poc'
            )
            ->orderByRaw("
                CASE
                    WHEN MAX(projects.amount) - COALESCE(SUM(recoveries.paid), 0) <= 0 THEN 2
                    WHEN DATE(MAX(recoveries.reminder)) <= CURDATE() 
                         AND TIME(MAX(recoveries.reminder)) <= CURTIME() THEN 0
                    ELSE 1
                END,
                MAX(recoveries.reminder) DESC
            ")
            ->get();
    }

    /**
     * Handle recovery/reminider creation and notification.
     */
    public function recordRecovery(array $data)
    {
        $recovery = new Recoveries();
        $recovery->client_id = $data['client_id'];
        $recovery->project_id = $data['project_id'];
        $recovery->paid = $data['received'] ?? 0;
        $recovery->note = $data['note'] ?? '';
        $recovery->status = $data['status'] ?? '0';
        
        if (isset($data['reminderDate'])) {
            $recovery->reminder = $data['reminderDate'];
        }

        if ($recovery->save()) {
            $client = Clients::find($data['client_id']);
            if ($client && $client->email) {
                if (($data['send'] ?? '') == '1' && ($data['received'] ?? 0) > 0) {
                    $this->sendMail(
                        $client->email,
                        'Thank You !!',
                        'emails.welcome',
                        ['name' => $client->name, 'messages' => "<p style='font-weight:bold;'>Payment Received</p>" . ($data['note'] ?? '')]
                    );
                } elseif (!empty($data['reminderDate'])) {
                    $reminderMsg = "<b>Reminder Date:</b> " . Carbon::parse($data['reminderDate'])->format('d M, Y') . "<br><b>Remaining Bal.</b>" . ($data['bal'] ?? '') . "<br>" . ($data['note'] ?? '');
                    $this->sendMail(
                        $client->email,
                        'Payment Reminder Alert!!',
                        'emails.welcome',
                        ['name' => $client->name, 'messages' => $reminderMsg]
                    );
                }
            }
            return $recovery;
        }
        return false;
    }

    /**
     * Find or create a client by phone/email.
     */
    public function firstOrCreateClient(array $data)
    {
        $client = Clients::where('mob', $data['phone'] ?? $data['mobile'] ?? '')->first();
        
        if (!$client) {
            $client = new Clients();
            $client->name = $data['name'] ?? '';
            $client->email = $data['email'] ?? '';
            $client->mob = $data['phone'] ?? $data['mobile'] ?? '';
            $client->company = $data['company'] ?? '';
            $client->save();
        }
        
        return $client;
    }

    /**
     * Manage project creation/update.
     */
    public function updateOrCreateProject(array $data, $projectId = null)
    {
        $project = $projectId ? Projects::find($projectId) : new Projects();
        
        $project->client_id = $data['client_id'];
        $project->name = $data['project_name'] ?? $data['project'] ?? '';
        $project->batchNo = $data['btno'] ?? $data['batchNo'] ?? '';
        $project->type = $data['type'] ?? '';
        $project->amount = $data['amount'] ?? $data['cost'] ?? 0;
        $project->note = $data['note'] ?? '';
        $project->deployment_url = $data['website'] ?? '';
        $project->save();
        
        return $project;
    }
}
