<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Mail\CustomMailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use App\Http\Controllers\AuthController;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\SmtpSettings;
use App\Models\User;
use App\Models\Leads;
use App\Models\Clients;
use App\Models\Eselicenses;
use App\Models\Companies;
use App\Models\Contracts;
use App\Models\Projects;
use App\Models\Lead_comments;
use App\Models\Recoveries;
use App\Models\Invoices;
use App\Models\Invoice_items;
use App\Models\CustomerDepartments;

use App\Services\ClientService;
use App\Traits\ActivityLogger;

class ClientController extends Controller
{
    use ActivityLogger;

    protected $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }
    public function getProjects($clientId)
    {
        // Fetch projects for the given client ID
        $projects = Projects::where('client_id', $clientId)->select('id', 'name', 'amount', 'batchNo')->get();

        // Return projects as JSON
        return response()->json(['projects' => $projects]);
    }

    public function clientList(Request $request)
    {
        $clients = Clients::select('id', 'name', 'company', 'email', 'mob', 'location')->where('name', '!=', '')->orderBy('name', 'ASC')->get();

        return json_encode(['clients' => $clients]);
    }

    public function getClient($clientId)
    {
        $client = Clients::find($clientId);
        if ($client) {
            return response()->json([
                'client' => [
                    'name' => $client->name,
                    'company' => $client->company,
                    'mobile' => $client->mob,
                    'whatsapp' => $client->whatsapp,
                ]
            ]);
        } else {
            return response()->json(['client' => null]);
        }
    }

    public function recovery($id = null, $title = null)
    {
        // Validate project exists
        $project = Projects::find($id);
        if (!$project) {
            return response('<div class="p-4 text-center text-danger"><i class="bx bx-error" style="font-size:1.5rem;"></i><p class="mt-2">Project not found. The associated project may have been deleted.</p></div>', 404);
        }

        $client = Clients::where('id', $project->client_id)->first();
        $recoveries = Recoveries::where('project_id', $id);
        $totalPaid = Recoveries::where('project_id', $id)->sum('paid');

        if ($title == "Received") {
            $recoveries = $recoveries->where('paid', '!=', '0')->get();
            return view('inc.recovery.received', compact('recoveries', 'project', 'totalPaid', 'client'));
        } else {
            $recoveries = $recoveries->get();
            return view('inc.recovery.reminder', compact('recoveries', 'project', 'totalPaid', 'client'));
        }
    }

    public function recoveryPost(Request $request)
    {
        if ($this->clientService->recordRecovery($request->all())) {
            return redirect('recoveries')->with('success', 'Operation completed successfully.');
        }
        return back()->with('error', 'Failed to process recovery.');
    }

    public function recoveries()
    {
        $recoveries = $this->clientService->getRecoveriesSummary(Auth::user()->cid);
        $totalRemaining = $recoveries->sum('remaining_amount');

        return view('recoveries', ['totalRemaining' => $totalRemaining, 'recoveries' => $recoveries]);
    }

    public function manageRecovery(Request $request)
    {
        $id = $request->id;
        $projectId = $request->project_id;
        $recoveries = null;

        if ($id) {
            $recoveries = Recoveries::leftjoin('clients', 'recoveries.client_id', '=', 'clients.id')
                ->leftjoin('projects', 'recoveries.project_id', '=', 'projects.id')
                ->select(
                    'projects.batchNo',
                    'clients.name as client_name',
                    'clients.company as client_company',
                    'clients.mob as client_mob',
                    'clients.whatsapp as client_whatsapp',
                    'clients.industry as client_industry',
                    'clients.email as client_email',
                    'clients.poc as client_poc',
                    'projects.name as project_name',
                    'projects.amount as project_amount',
                    'projects.deployment_url',
                    'projects.note as project_note',
                    'recoveries.note as recovery_note',
                    'recoveries.*'
                )
                ->where('recoveries.id', $id)
                ->first();
        } elseif ($projectId) {
            // Mock a recovery object structure pre-filled with project/client data
            $project = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
                ->select('projects.*', 'clients.name as client_name', 'clients.company as client_company', 'clients.email as client_email', 'clients.mob as client_mob', 'clients.whatsapp as client_whatsapp', 'clients.industry as client_industry', 'clients.poc as client_poc')
                ->where('projects.id', $projectId)
                ->first();

            if ($project) {
                $recoveries = (object) [
                    'project_id' => $project->id,
                    'client_id' => $project->client_id,
                    'batchNo' => $project->batchNo,
                    'client_name' => $project->client_name,
                    'client_company' => $project->client_company,
                    'client_email' => $project->client_email,
                    'client_mob' => $project->client_mob,
                    'client_whatsapp' => $project->client_whatsapp,
                    'client_industry' => $project->client_industry,
                    'client_poc' => $project->client_poc,
                    'project_name' => $project->name,
                    'project_amount' => $project->amount,
                    'project_note' => $project->note,
                    'recovery_note' => '',
                ];
            }
        }

        $clients = Clients::orderBy('name', 'ASC')->get();
        $projects = [];
        if ($recoveries && isset($recoveries->client_id)) {
            $projects = Projects::where('client_id', $recoveries->client_id)->get();
        }

        $viewData = [
            'recoveries' => $recoveries,
            'clients' => $clients,
            'projects' => $projects,
            'previous_url' => $request->input('previous_url') ?: url()->previous()
        ];

        if ($request->has('ajax')) {
            return view('manageRecoveryForm', $viewData);
        }

        return view('manageRecovery', $viewData);
    }

    public function updateRecoveryAmount(Request $request)
    {
        $request->validate([
            'id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
        ]);

        $record = Recoveries::find($request->id); // Replace 'Recovery' with your model
        if ($record) {
            $record->paid = $request->amount; // Update the amount
            $record->save();

            return response()->json(['message' => 'Amount updated successfully.']);
        }

        return response()->json(['message' => 'Record not found.'], 404);
    }

    public function manageRecoveryPost(Request $request)
    {
        $clientId = $request->clientId;
        $projectId = $request->projectId;

        // 1. Resolve Client
        if ($clientId === 'new') {
            $client = $this->clientService->firstOrCreateClient([
                'phone' => $request->phone,
                'name' => $request->name,
                'company' => $request->company,
                'email' => $request->email
            ]);
            $clientId = $client->id;
        } else {
            // Update existing client details
            $client = Clients::find($clientId);
            if ($client) {
                $client->name = $request->name;
                $client->company = $request->company;
                $client->mob = $request->phone;
                $client->email = $request->email;
                $client->save();
            }
        }

        // 2. Resolve Project
        if ($projectId === 'new') {
            $project = $this->clientService->updateOrCreateProject(array_merge($request->all(), [
                'client_id' => $clientId,
                'project_name' => $request->project // Using manual project name
            ]), null);
            $projectId = $project->id;
        } else {
            $project = Projects::find($projectId);
            if ($project) {
                $project->amount = $request->amount; // Update project amount based on form
                $project->batchNo = $request->btno ?? $project->batchNo; // Update batch number
                $project->save();
            }
        }

        // 3. Handle Recovery (Create or Update)
        $recovery = null;
        if ($request->id) {
            $recovery = Recoveries::find($request->id);
        }

        if ($recovery) {
            // Update existing recovery
            $recovery->client_id = $clientId;
            $recovery->project_id = $projectId;
            $recovery->note = $request->note ?? '';
            $recovery->save();
            $redirectUrl = $request->input('previous_url') ?: 'recoveries';
            return redirect($redirectUrl)->with('success', 'Recovery updated successfully.');
        } else {
            // Create new recovery
            $this->clientService->recordRecovery([
                'client_id' => $clientId,
                'project_id' => $projectId,
                'received' => $request->received,
                'note' => $request->note,
                'reminderDate' => $request->reminder ?: now(),
                'status' => $request->status ?: '0',
                'send' => '1' // Send email if needed based on recordRecovery logic
            ]);
            $redirectUrl = $request->input('previous_url') ?: 'recoveries';
            return redirect($redirectUrl)->with('success', 'Recovery added successfully.');
        }
    }

    public function contracts()
    {
        $contracts = Contracts::leftjoin('clients', 'contracts.client_id', '=', 'clients.id')
            ->select('clients.name', 'clients.email', 'clients.mob', 'clients.whatsapp', 'clients.company', 'contracts.*')
            ->orderByRaw("
                CASE contracts.status
                    WHEN 'Draft' THEN 1
                    WHEN 'Sent' THEN 2
                    WHEN 'Accepted' THEN 3
                    WHEN 'Declined' THEN 4
                    WHEN 'Expired' THEN 5
                    ELSE 6
                END
            ")
            ->orderBy('contracts.end_date', 'DESC')
            ->get();

        // Add priority and rowClass
        $contracts = $contracts->map(function ($contract) {
            $endDate = \Carbon\Carbon::parse($contract->end_date ?? null);
            $today = \Carbon\Carbon::today();
            $diffInDays = $today->diffInDays($endDate, false);

            if ($diffInDays < 0) {
                $priority = 1; // expired
                $rowClass = 'table-danger';
            } elseif ($diffInDays <= 7) {
                $priority = 2; // critical
                $rowClass = 'table-warning';
            } elseif ($diffInDays <= 15) {
                $priority = 3; // warning
                $rowClass = 'table-warning';
            } elseif ($diffInDays <= 30) {
                $priority = 4; // mild warning
                $rowClass = 'table-warning';
            } else {
                $priority = 5; // normal
                $rowClass = '';
            }

            $contract->priority = $priority;
            $contract->rowClass = $rowClass;
            return $contract;
        })
            ->sortBy([
                ['priority', 'asc'],
                ['end_date', 'asc']
            ])
            ->values();

        return view('contracts', ['contracts' => $contracts]);
    }

    public function manageContract(Request $request)
    {
        $id = $request->id;
        $contract = null;

        if ($id) {
            $contract = Contracts::where('id', '=', $id)->first();
        }

        $clients = Clients::where('status', '=', '1')->get();

        $viewData = [
            'contract' => $contract,
            'clients' => $clients,
        ];

        // If called as AJAX modal request, return pure partial — no layout, no DataTables scripts
        if ($request->has('ajax')) {
            return view('manageContractForm', $viewData);
        }

        $viewData['previous_url'] = $request->input('previous_url', url()->previous());

        return view('manageContract', $viewData);
    }

    public function manageContractPost(Request $request)
    {
        $validatedData = $request->validate([
            'client_id' => 'required|exists:clients,id',
            'subject' => 'required|string|max:255',
            'value' => 'nullable|numeric',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'description' => 'nullable|string',
            'contract_type' => 'required|string|max:255',
            'custom_contract_type' => 'nullable|string|max:255',
        ]);

        // Use custom contract type if provided
        $contractType = $validatedData['contract_type'] === 'new'
            ? $validatedData['custom_contract_type']
            : $validatedData['contract_type'];

        if ($contractType === null) {
            return back()->withErrors(['custom_contract_type' => 'Please enter a custom contract type.'])->withInput();
        }

        // Check if this is an update or new
        $contract = $request->id ? Contracts::findOrFail($request->id) : new Contracts();

        $contract->client_id = $validatedData['client_id'];
        $contract->subject = $validatedData['subject'];
        $contract->value = $validatedData['value'];
        $contract->start_date = $validatedData['start_date'];
        $contract->end_date = $validatedData['end_date'];
        $contract->des = $validatedData['description'] ?? '';
        $contract->contract_type = $contractType;

        $contract->save();

        $redirectUrl = $request->input('previous_url') ?: '/contracts';
        return redirect($redirectUrl)->with('success', $request->id ? 'Contract updated successfully.' : 'Contract added successfully.');
    }

    public function projects(Request $request)
    {
        $search = $request->get('search');
        $query = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
            ->leftJoin('users as sales', 'projects.closed_by', '=', 'sales.id')
            ->leftJoin(
                DB::raw("(SELECT project_id, SUM(paid) as total_paid FROM recoveries GROUP BY project_id) as rec_totals"),
                'projects.id',
                '=',
                'rec_totals.project_id'
            )
            ->select(
                'projects.*',
                'clients.name as client_name',
                'clients.company as client_company',
                'clients.mob as client_mob',
                'clients.whatsapp as client_whatsapp',
                'sales.name as salesperson_name',
                DB::raw('COALESCE(rec_totals.total_paid, 0) as total_paid')
            );

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('projects.name', 'LIKE', "%{$search}%")
                    ->orWhere('clients.name', 'LIKE', "%{$search}%")
                    ->orWhere('clients.company', 'LIKE', "%{$search}%");
            });
        }

        $projects = $query->orderByRaw('CASE WHEN COALESCE(rec_totals.total_paid, 0) < projects.amount THEN 0 ELSE 1 END ASC')
            ->orderBy('projects.status', 'DESC')
            ->orderBy('projects.created_at', 'DESC')
            ->get();

        return view('projects', ['projects' => $projects, 'search' => $search]);
    }

    public function singleProjectGet(Request $request)
    {
        $id = $request->id;
        $project = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
            ->select('projects.*', 'clients.name as client_name', 'clients.company as client_company', 'clients.email as client_email', 'clients.mob as client_mob', 'clients.location as client_location')
            ->where('projects.id', $id)
            ->first();

        if (!$project) {
            return response()->json(['error' => 'Project not found'], 404);
        }

        $recoveries = Recoveries::where('project_id', $id)->orderBy('id', 'DESC')->get();
        $license = Eselicenses::where('project_id', $id)->orderBy('id', 'DESC')->first();

        return response()->json([
            'project' => $project,
            'recoveries' => $recoveries,
            'license' => $license
        ]);
    }

    public function viewProject(Request $request, $id)
    {
        // Multi-tenancy: Projects uses BelongsToCompany (TenantScope), so this
        // automatically filters to the current tenant's cid. Abort 404 if not found or
        // if it belongs to a different tenant.
        $project = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
            ->leftJoin('users as sales', 'projects.closed_by', '=', 'sales.id')
            ->select('projects.*', 'clients.name as client_name', 'clients.company as client_company', 'clients.email as client_email', 'clients.mob as client_mob', 'clients.location as client_location', 'clients.whatsapp as client_whatsapp', 'sales.name as salesperson_name')
            ->where('projects.id', $id)
            ->first();

        if (!$project) {
            abort(404, 'Project not found');
        }

        // All models below use BelongsToCompany → TenantScope which auto-adds a
        // `cid` WHERE clause. Filtering by project_id (which is already tenant-owned)
        // gives us a double-lock: correct project AND correct tenant.
        $recoveries = Recoveries::where('project_id', $id)->orderBy('id', 'DESC')->get();

        // Multi-tenancy: Eselicenses uses TenantScope (cid filter auto-applied).
        $license = Eselicenses::where('project_id', $id)->orderBy('id', 'DESC')->first();

        // Multi-tenancy: Invoices uses TenantScope (cid filter auto-applied).
        $invoices = Invoices::where('project_id', $id)->orderBy('date', 'DESC')->orderBy('id', 'DESC')->get();

        // Primary Tasks related to project (parent tasks only, with subtasks eager loaded)
        // Multi-tenancy: Task uses TenantScope (cid filter auto-applied).
        $tasks = \App\Models\Task::where('project_id', $id)
            ->whereNull('parent_id')
            ->with('subtasks')
            ->orderBy('id', 'asc')
            ->get();

        // Proposals: scoped to client_id that belongs to this tenant only.
        // We use Clients::find() which is also tenant-scoped, so $client will be null
        // if it doesn't belong to the current tenant — preventing cross-tenant proposal leakage.
        $client = \App\Models\Clients::find($project->client_id);
        $leadIds = [];
        if ($client) {
            $leadIds[] = $client->id;
            if (!empty($client->commentLeadID)) {
                $leadIds[] = (int) $client->commentLeadID;
            }
        }

        // Multi-tenancy: Proposals uses TenantScope (cid filter auto-applied).
        // The additional whereIn('lead_id', $leadIds) further anchors results to
        // verified tenant-owned client IDs only.
        $proposals = empty($leadIds)
            ? collect()
            : \App\Models\Proposals::whereIn('lead_id', $leadIds)
                ->orderBy('proposal_date', 'DESC')
                ->orderBy('id', 'DESC')
                ->get();

        return view('project-view', compact('project', 'recoveries', 'license', 'invoices', 'tasks', 'proposals'));
    }

    public function licensing()
    {
        $query = Eselicenses::leftjoin('projects', 'eselicenses.project_id', 'projects.id')
            ->leftjoin('clients', 'projects.client_id', 'clients.id')
            ->select('clients.name as client_name', 'projects.name as project_name', 'projects.deployment_url', 'eselicenses.*');

        $licenses = $query->orderBy('eselicenses.expiry_date', 'ASC')->get();

        // Calculate Stats
        $today = now();
        $thirtyDays = now()->addDays(30);

        $stats = [
            'total' => $licenses->count(),
            'active' => $licenses->where('expiry_date', '>=', $today)->count(),
            'expired' => $licenses->where('expiry_date', '<', $today)->count(),
            'expiring_soon' => $licenses->where('expiry_date', '>=', $today)
                ->where('expiry_date', '<=', $thirtyDays)->count(),
        ];

        return view('licenses', [
            'licenses' => $licenses,
            'stats' => $stats
        ]);
    }

    public function manageLicense(Request $request)
    {
        $id = $request->id ?? '';
        $project_id = $request->project_id ?? null;

        // Load existing license (edit mode)
        $license = null;
        if ($id) {
            $license = Eselicenses::leftjoin('projects', 'eselicenses.project_id', 'projects.id')
                ->leftjoin('clients', 'projects.client_id', 'clients.id')
                ->select('clients.name as client_name', 'clients.company', 'clients.mob', 'clients.email', 'projects.name as project_name', 'projects.deployment_url', 'projects.type', 'projects.amount', 'projects.note', 'eselicenses.*')
                ->where('eselicenses.id', '=', $id)
                ->first();
        }

        // If project_id supplied (e.g. from project/view "Add License" button),
        // fetch project + client for form pre-population (new license only).
        $preloadProject = null;
        if ($project_id && !$id) {
            $preloadProject = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
                ->select(
                    'projects.id as project_id',
                    'projects.name as project_name',
                    'projects.amount as project_amount',
                    'projects.type as project_type',
                    'projects.note as project_note',
                    'projects.deployment_url',
                    'projects.client_id',
                    'clients.name as client_name',
                    'clients.company as client_company',
                    'clients.email as client_email',
                    'clients.mob as client_mob'
                )
                ->where('projects.id', $project_id)
                ->first();
        }

        $projects = Projects::leftjoin('clients', 'clients.id', 'projects.client_id')
            ->select('clients.name as client_name', 'clients.company', 'clients.email', 'clients.mob', 'projects.*')
            ->orderBy('projects.name', 'ASC')
            ->get();

        $viewData = [
            'license' => $license,
            'projects' => $projects,
            'project_id' => $project_id,
            'preloadProject' => $preloadProject,
            'previous_url' => $request->input('previous_url', url()->previous()),
        ];

        if ($request->has('ajax')) {
            return view('manageLicenseForm', $viewData);
        }

        return view('manageLicense', $viewData);
    }

    public function manageLicensePost(Request $request)
    {
        $validatedData = $request->validate([
            'project_id' => 'nullable|exists:projects,id',
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'mobile' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'project_name' => 'required|string|max:255',
            'type' => 'nullable|string|max:255',
            'cost' => 'nullable|numeric',
            'website' => 'required|url|max:255',
            'technology_stack' => 'required|string|max:255',
            'note' => 'nullable|string',
            'license_key' => 'required|string|max:255|unique:eselicenses,eselicense_key,' . ($request->id ?? 'NULL'),
            'expiry_date' => 'required|date',
        ]);

        $client = $this->clientService->firstOrCreateClient($validatedData);
        $project = $this->clientService->updateOrCreateProject(array_merge($validatedData, ['client_id' => $client->id]), $request->project_id);

        $license = $request->id ? Eselicenses::findOrFail($request->id) : new Eselicenses();
        $license->fill([
            'project_id' => $project->id,
            'eselicense_key' => $request->license_key,
            'expiry_date' => $request->expiry_date,
            'technology_stack' => $request->technology_stack
        ]);

        if ($license->save()) {
            $redirectUrl = $request->input('previous_url') ?: 'licensing';
            return redirect($redirectUrl)->with('success', 'License details successfully processed.');
        }

        return back()->with('error', 'Failed to process license.');
    }

    public function clients(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $industry = $request->input('industry');
        $lifecycle_stage = $request->input('lifecycle_stage');

        $query = Clients::where('name', '!=', '');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('company', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%")
                    ->orWhere('mob', 'LIKE', "%{$search}%");
            });
        }

        if ($status !== null && $status !== '') {
            $query->where('status', '=', $status);
        }

        if (!empty($industry)) {
            $query->where('industry', '=', $industry);
        }

        if (!empty($lifecycle_stage)) {
            $query->where('lifecycle_stage', '=', $lifecycle_stage);
        }

        $clients = $query->orderBy('created_at', 'DESC')->get();

        // Dynamically fetch available industries for the dropdown
        $industryQuery = Clients::select('industry')->whereNotNull('industry')->where('industry', '!=', '')->distinct();
        $availableIndustries = $industryQuery->pluck('industry');

        return view('clients', [
            'clients' => $clients,
            'search' => $search,
            'status' => $status,
            'industry' => $industry,
            'lifecycle_stage' => $lifecycle_stage,
            'availableIndustries' => $availableIndustries
        ]);
    }

    public function clientPost(Request $request)
    {
        return $this->clients($request);
    }

    /**
     * Toggle a customer's active / inactive status via AJAX.
     */
    public function toggleClientStatus(Request $request)
    {
        $request->validate(['id' => 'required|exists:clients,id']);

        $client = Clients::findOrFail($request->id);
        $client->status = $client->status == '1' ? '0' : '1';
        $client->save();

        $label = $client->status == '1' ? 'Active' : 'Inactive';
        $this->logActivity(
            "Customer {$label}",
            'clients',
            $client->id,
            $client->name,
            "Set customer {$client->name} as {$label}"
        );

        return response()->json([
            'success' => true,
            'status' => $client->status,
            'label' => $label,
        ]);
    }

    public function manageClient(Request $request)
    {
        $clients = Clients::with('departments')->where('id', '=', $request->id)->first();
        $leadOrigin = null;
        $interactions = collect();
        $proposals = collect();
        $projects = collect();
        $invoices = collect();

        if ($request->id && $clients) {
            $interactions = \App\Models\Interaction::where('rel_type', 'Client')
                ->where('rel_id', $request->id)
                ->orderBy('created_at', 'desc')
                ->get();

            // Fetch Journey Data
            if (!empty($clients->commentLeadID)) {
                $leadOrigin = \App\Models\Leads::find($clients->commentLeadID);
            }

            // Proposals (related to this client, or the original lead)
            $leadIds = [$clients->id];
            if ($leadOrigin)
                $leadIds[] = $leadOrigin->id;

            $proposals = \App\Models\Proposals::whereIn('lead_id', $leadIds)
                ->orderBy('created_at', 'desc')
                ->get();

            $projects = \App\Models\Projects::where('client_id', $request->id)
                ->orderBy('created_at', 'desc')
                ->get();

            $invoices = \App\Models\Invoices::where('client_id', $request->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('manageClient', [
            'clients' => $clients,
            'interactions' => $interactions,
            'leadOrigin' => $leadOrigin,
            'proposals' => $proposals,
            'projects' => $projects,
            'invoices' => $invoices,
            'previous_url' => $request->input('previous_url', url()->previous())
        ]);

    }

    public function storeInteraction(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'type' => 'required|string',
            'content' => 'required_without:attachment',
            'attachment' => 'nullable|file|max:10240' // max 10MB
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('interactions', 'public');
        }

        \App\Models\Interaction::create([
            'user_id' => \Illuminate\Support\Facades\Auth::id(),
            'rel_type' => 'Client',
            'rel_id' => $request->client_id,
            'type' => $request->type,
            'content' => $request->input('content'),
            'attachment_path' => $path
        ]);

        return back()->with('success', 'Interaction/Document added successfully.');
    }

    public function manageClientPost(Request $request)
    {
        $editId = $request->id; // from hidden field

        $emailRule = Rule::unique('clients', 'email')->where(function ($query) {
            return $query->where('cid', Auth::user()->cid);
        });
        if ($editId) {
            $emailRule->ignore($editId);
        }

        $mobRule = Rule::unique('clients', 'mob')->where(function ($query) {
            return $query->where('cid', Auth::user()->cid);
        });
        if ($editId) {
            $mobRule->ignore($editId);
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                $emailRule
            ],
            'mob' => [
                'required',
                'string',
                'max:20',
                $mobRule
            ]
        ], [
            'email.unique' => 'This email address is already registered to a customer in your company.',
            'mob.unique' => 'This mobile number is already registered to a customer in your company.'
        ]);

        $location = json_encode($request->address ?? []);

        if (empty($editId)) {
            // Convert lead to client
            $client = new Clients();
            $client->name = $request->name ?? '';
            $client->company = $request->company ?? '';
            $client->gstno = $request->gst ?? '';
            $client->email = $request->email ?? '';
            $client->mob = $request->mob ?? '';
            $client->alterMob = $request->alterMob ?? '';
            $client->location = $location ?? '';
            $client->source = $request->source ?? '';
            $client->poc = $request->poc ?? '';
            $client->purpose = $request->purpose ?? '';
            $client->status = '1';
            $client->whatsapp = $request->whatsapp ?? '';
            $client->industry = $request->industry ?? '';
            $client->position = $request->position ?? '';
            $client->website = $request->website ?? '';
            $client->values = $request->values ?? '';
            $client->language = $request->language ?? '';
            $client->tags = $request->tags ?? '';
            $client->lifecycle_stage = $request->lifecycle_stage ?? null;

            if ($client->save()) {
                // Save Departments
                $submittedDeptIds = [];
                if ($request->has('departments')) {
                    foreach ($request->departments as $dept) {
                        if (!empty($dept['name'])) {
                            $d = CustomerDepartments::updateOrCreate(
                                ['id' => $dept['id'] ?? null],
                                [
                                    'client_id' => $client->id,
                                    'name' => $dept['name'],
                                    'location' => $dept['location'] ?? null,
                                    'poc' => $dept['poc'] ?? null,
                                ]
                            );
                            $submittedDeptIds[] = $d->id;
                        }
                    }
                }
                // Delete removed departments
                CustomerDepartments::where('client_id', $client->id)
                    ->whereNotIn('id', $submittedDeptIds)
                    ->delete();

                $this->logActivity('Customer Created', 'clients', $client->id, $client->name, "Added new customer: {$client->name}");

                return redirect('clients')->with('success', 'New customer successfully added.');
            } else {
                return back()->with('error', 'Failed to list new client.');
            }
        } else {
            // Updating an existing lead or converting to a client
            $leadSingle = Clients::find($editId);

            if (!$leadSingle) {
                return back()->with('error', 'Client not found.');
            }

            // Update existing lead
            $leadSingle->name = $request->name ?? '';
            $leadSingle->company = $request->company ?? '';
            $leadSingle->gstno = $request->gst ?? '';
            $leadSingle->email = $request->email ?? '';
            $leadSingle->mob = $request->mob ?? '';
            $leadSingle->alterMob = $request->alterMob ?? '';
            $leadSingle->location = $location ?? '';
            $leadSingle->source = $request->source ?? '';
            $leadSingle->poc = $request->poc ?? '';
            $leadSingle->purpose = $request->purpose ?? '';
            $leadSingle->status = $request->status ?? $leadSingle->status;
            $leadSingle->whatsapp = $request->whatsapp ?? '';
            $leadSingle->industry = $request->industry ?? '';
            $leadSingle->position = $request->position ?? '';
            $leadSingle->website = $request->website ?? '';
            $leadSingle->values = $request->values ?? '';
            $leadSingle->language = $request->language ?? '';
            $leadSingle->tags = $request->tags ?? '';
            $leadSingle->lifecycle_stage = $request->lifecycle_stage ?? null;

            if ($leadSingle->update()) {
                // Save Departments
                $submittedDeptIds = [];
                if ($request->has('departments')) {
                    foreach ($request->departments as $dept) {
                        if (!empty($dept['name'])) {
                            $d = CustomerDepartments::updateOrCreate(
                                ['id' => $dept['id'] ?? null],
                                [
                                    'client_id' => $leadSingle->id,
                                    'name' => $dept['name'],
                                    'location' => $dept['location'] ?? null,
                                    'poc' => $dept['poc'] ?? null,
                                ]
                            );
                            $submittedDeptIds[] = $d->id;
                        }
                    }
                }
                // Delete removed departments
                CustomerDepartments::where('client_id', $leadSingle->id)
                    ->whereNotIn('id', $submittedDeptIds)
                    ->delete();

                $this->logActivity('Customer Updated', 'clients', $leadSingle->id, $leadSingle->name, "Updated customer: {$leadSingle->name}");

                $redirectUrl = $request->input('previous_url') ?: back()->getTargetUrl();
                return redirect($redirectUrl)->with('success', 'client successfully updated.');
            } else {
                return back()->with('error', 'Failed to update lead.');
            }

        }
    }

    public function singleClientGet(Request $request)
    {
        $id = ($request->id ?? '');
        $page = ($request->pagename ?? '');
        if ($page == 'client') {

            $client = Clients::with('departments')->where('id', '=', $id)->first();
            if (!$client)
                return response()->json(['error' => 'Client not found'], 404);

            $interactions = \App\Models\Interaction::where('rel_type', 'Client')
                ->where('rel_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            $leadOrigin = null;
            if (!empty($client->commentLeadID)) {
                $leadOrigin = \App\Models\Leads::find($client->commentLeadID);
            }

            $leadIds = [$client->id];
            if ($leadOrigin)
                $leadIds[] = $leadOrigin->id;

            $proposals = \App\Models\Proposals::whereIn('lead_id', $leadIds)
                ->orderBy('proposal_date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $projects = \App\Models\Projects::where('client_id', $id)
                ->orderBy('created_at', 'desc')
                ->get();

            $invoices = \App\Models\Invoices::where('client_id', $id)
                ->orderBy('date', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            return response()->json([
                'clients' => $client,
                'interactions' => $interactions,
                'proposals' => $proposals,
                'projects' => $projects,
                'invoices' => $invoices
            ]);
        }
    }

    public function invoices(Request $request)
    {
        $cid = Auth::User()->cid;
        $type = $request->get('type');

        // Fetch distinct types for the filter dropdown
        $availableTypes = Invoices::whereNotNull('invoice')
            ->where('invoice', '!=', '')
            ->distinct()
            ->pluck('invoice');

        $query = Invoices::leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->select('clients.name as client_name', 'clients.company as client_company', 'clients.email as client_email', 'invoices.*');

        // Apply invoice type filter if provided
        if ($type) {
            $query->where('invoices.invoice', $type);
        }

        // Sort by Invoice Number DESC (Logic: handle numeric extraction for accurate sorting)
        // Using orderByRaw to handle cases where invoice_number might have leading zeros or non-numeric characters
        $invoices = $query->orderByRaw('CAST(invoices.invoice_number AS UNSIGNED) DESC')
            ->orderBy('invoices.id', 'DESC')
            ->get();

        return view('invoices', [
            'invoices' => $invoices,
            'availableTypes' => $availableTypes,
            'currentType' => $type
        ]);
    }

    public function manageInvoice(Request $request)
    {
        $id = $request->id ?? null;
        $project_id = $request->project_id ?? null;

        // If there's an ID, load one invoice
        if ($id) {
            $invoice = Invoices::where('id', $id)->first();
            // Bypass TenantScope for invoice_items — old items may have cid=NULL
            // The parent invoice is already tenant-scoped, so invoice_id is safe
            $invoiceItems = Invoice_items::withoutGlobalScope(\App\Scopes\TenantScope::class)
                ->where('invoice_id', $id)->get();
        } else {
            $invoice = null;
            $invoiceItems = collect();
        }

        // If project_id supplied (e.g. from project/view "Create Invoice" button),
        // fetch the project and its client so we can pre-populate the form.
        $preloadProject = null;
        $preloadClient = null;
        if ($project_id && !$id) {
            $preloadProject = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
                ->select(
                    'projects.id as project_id',
                    'projects.name as project_name',
                    'projects.amount as project_amount',
                    'projects.type as project_type',
                    'projects.note as project_note',
                    'projects.client_id',
                    'clients.name as client_name',
                    'clients.company as client_company',
                    'clients.email as client_email',
                    'clients.mob as client_mob',
                    'clients.gstno as client_gstno',
                    'clients.location as client_location'
                )
                ->where('projects.id', $project_id)
                ->first();

            if ($preloadProject) {
                $preloadClient = Clients::find($preloadProject->client_id);
            }
        }

        $clients = Clients::orderBy('name', 'ASC')->get();

        $companies = Companies::where('id', '=', Auth::User()->cid)->first();

        return view('manageInvoice', [
            'invoice' => $invoice,
            'invoiceItems' => $invoiceItems,
            'clients' => $clients,
            'companies' => $companies,
            'project_id' => $project_id,
            'preloadProject' => $preloadProject,
            'preloadClient' => $preloadClient,
            'previous_url' => $request->input('previous_url', url()->previous()),
        ]);
    }


    public function manageInvoicePost(Request $request)
    {
        $validatedData = $request->validate([
            'invoice_number' => 'required|max:255',
            'invoice_type' => 'nullable|max:255',
            'client_id' => 'required|integer|exists:clients,id',
            'date' => 'required|date',
            'due_date' => 'nullable|date',
            'status' => 'nullable|in:unpaid,paid,partial',
            'reference' => 'nullable|string|max:255',

            'payment_mode' => 'nullable|string|max:255',
            'currency' => 'nullable|string|max:10',
            'sales_agent' => 'nullable|string|max:255',
            'discount_type' => 'nullable|in:none,before-tax,after-tax',
            'recurring_invoice' => 'nullable|boolean',

            'billing_address' => 'nullable|string',
            'client_gst' => 'nullable|string',
            'shipping_address' => 'nullable|string',

            'discount_mode' => 'nullable|in:flat,percentage',
            'discount' => 'nullable|numeric',
            'adjustment' => 'nullable|numeric',

            'admin_note' => 'nullable|string',
            'client_note' => 'nullable|string',
            'terms' => 'nullable|string',

            // If you're editing an existing invoice
            'id' => 'nullable|integer|exists:invoices,id',
            'project_id' => 'nullable|integer|exists:projects,id',
        ]);

        // 2) Check if we are updating or creating a new invoice
        if (!empty($validatedData['id'])) {
            // Update existing invoice
            $invoice = Invoices::findOrFail($validatedData['id']);
        } else {
            // Create new invoice
            $invoice = new Invoices();
        }

        // 3) Assign validated data to the invoice model
        $invoice->invoice_number = $validatedData['invoice_number'];
        $invoice->invoice = $validatedData['invoice_type'];
        $invoice->client_id = $validatedData['client_id'];
        $invoice->date = $validatedData['date'];
        $invoice->due_date = $validatedData['due_date'] ?? null;
        $invoice->status = $validatedData['status'] ?? 'unpaid';
        $invoice->reference = $validatedData['reference'] ?? null;

        $invoice->payment_mode = $validatedData['payment_mode'] ?? null;
        $invoice->currency = $validatedData['currency'] ?? 'USD';
        $invoice->sales_agent = $validatedData['sales_agent'] ?? null;
        $invoice->discount_type = $validatedData['discount_type'] ?? 'none';
        $invoice->project_id = $validatedData['project_id'] ?? null;
        $invoice->recurring_invoice = !empty($validatedData['recurring_invoice']);

        $invoice->bank_details = json_encode($request->bank_details ?? []);
        $invoice->billing_address = $validatedData['billing_address'] ?? null;
        $invoice->client_gstno = $validatedData['client_gst'] ?? null;
        $invoice->shipping_address = $validatedData['shipping_address'] ?? null;

        $invoice->discount_mode = $validatedData['discount_mode'] ?? 'flat';
        $invoice->discount = $validatedData['discount'] ?? 0;
        $invoice->adjustment = $validatedData['adjustment'] ?? 0;
        $invoice->total_amount = $request->gtAmount ?? 0;

        $invoice->admin_note = $validatedData['admin_note'] ?? null;
        $invoice->client_note = $validatedData['client_note'] ?? null;
        $invoice->terms = $validatedData['terms'] ?? null;

        // 4) Save the invoice to get an ID (if new)
        $invoice->save();

        if ($request->has('invoice_items')) {
            // Remove old items if updating — bypass TenantScope to catch items with NULL cid
            if (!empty($validatedData['id'])) {
                Invoice_items::withoutGlobalScope(\App\Scopes\TenantScope::class)
                    ->where('invoice_id', $invoice->id)->delete();
            }

            foreach ($request->input('invoice_items', []) as $itemData) {
                // --- Extract Basic Item Data ---
                $shortDesc = $itemData['short_description'] ?? '';
                $longDesc = $itemData['long_description'] ?? '';
                $sac_code = $itemData['sac_code'] ?? '';
                // Use float for quantity if you allow fractional quantities (like hours)
                $quantity = !empty($itemData['quantity']) ? (float) $itemData['quantity'] : 0;
                $price = !empty($itemData['price']) ? (float) $itemData['price'] : 0;

                // --- Skip Empty/Meaningless Rows ---
                // Skip if description/name is missing AND quantity or price is zero/missing
                if (empty($shortDesc) && empty($longDesc) && ($quantity <= 0 || $price <= 0)) {
                    continue;
                }

                // --- START: Parse Tax Rates ---
                $selected_tax_values = isset($itemData['tax_rate']) && is_array($itemData['tax_rate'])
                    ? $itemData['tax_rate']
                    : [];

                $cgst_percent = 0.0;
                $sgst_percent = 0.0;
                $igst_percent = 0.0;
                $vat_percent = 0.0;
                // Add other tax types if necessary

                foreach ($selected_tax_values as $tax_value_string) {
                    // $tax_value_string will be like "0:0.0500", "1:0.0500", etc.
                    $parts = explode(':', $tax_value_string);

                    if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                        $tax_index = (int) $parts[0];
                        $tax_rate_decimal = (float) $parts[1];
                        $tax_rate_percent = $tax_rate_decimal * 100.0; // Convert to percentage

                        switch ($tax_index) {
                            case 0:
                                $cgst_percent = $tax_rate_percent;
                                break;
                            case 1:
                                $sgst_percent = $tax_rate_percent;
                                break;
                            case 2:
                                $igst_percent = $tax_rate_percent;
                                break;
                            case 3:
                                $vat_percent = $tax_rate_percent;
                                break;
                            // Add more cases if needed
                            default:
                                // Log::warning("Unexpected tax index [{$tax_index}] found for invoice ID [{$invoice->id}]");
                                break;
                        }
                    } else {
                        // Log::warning("Malformed tax value '{$tax_value_string}' received for invoice ID [{$invoice->id}]");
                    }
                }
                // --- END: Parse Tax Rates ---


                // --- Create & Save Invoice Item ---
                $invoiceItem = new Invoice_items();
                $invoiceItem->invoice_id = $invoice->id;
                $invoiceItem->short_description = $shortDesc;
                $invoiceItem->long_description = $longDesc;
                $invoiceItem->sac_code = $sac_code;
                $invoiceItem->quantity = $quantity; // Ensure your DB column can handle float if needed
                $invoiceItem->price = $price;

                // Assign the *parsed* tax percentages
                $invoiceItem->cgst_percent = $cgst_percent;
                $invoiceItem->sgst_percent = $sgst_percent;
                $invoiceItem->igst_percent = $igst_percent;
                $invoiceItem->vat_percent = $vat_percent;
                // Add assignments for other tax types if you have them

                $invoiceItem->save();
            }
        }

        $this->logActivity('Invoice Saved', 'invoices', $invoice->id, $invoice->invoice_no ?? "#{$invoice->id}", "Invoice #{$invoice->id} saved", (string) ($invoice->grand_total ?? ''));

        // 6) Redirect or return a response
        $redirectUrl = $request->input('previous_url') ?: route('manageInvoice', ('id=' . $invoice->id ?? ''));
        return redirect($redirectUrl)->with('success', 'Invoice saved successfully!');
    }

    public function manageInvoiceClientPost(Request $request)
    {
        $client = new Clients();
        $client->name = $request->name ?? '';
        $client->company = $request->company ?? '';
        $client->email = $request->email ?? '';
        $client->mob = $request->mob ?? '';
        $client->alterMob = $request->alterMob ?? '';
        $client->location = json_encode($request->address ?? '');
        $client->source = $request->source ?? '';
        $client->poc = $request->poc ?? '';
        $client->purpose = $request->purpose ?? '';
        $client->status = '0';
        $client->whatsapp = $request->whatsapp ?? '';
        $client->industry = $request->industry ?? '';
        $client->position = $request->position ?? '';
        $client->website = $request->website ?? '';
        $client->values = $request->values ?? '';
        $client->language = $request->language ?? '';
        $client->tags = $request->tags ?? '';

        if ($client->save()) {
            return back()->with('success', 'New Client successfully added.');
        } else {
            return back()->with('error', 'Failed to convert lead to client.');
        }

    }

    public function invoicePreview($id)
    {
        // Fetch the invoice with client details
        $invoice = Invoices::leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->leftJoin('companies', 'clients.cid', '=', 'companies.id')
            ->select('companies.name as cn', 'companies.mob as cm', 'companies.email as ce', 'companies.img', 'companies.gst as cgst', 'companies.vat as cvat', 'companies.address', 'companies.city', 'companies.state', 'companies.zipcode', 'companies.country', 'companies.bank_details', 'clients.name', 'clients.company', 'clients.email', 'clients.mob', 'clients.location', 'invoices.*')
            ->where('invoices.id', '=', $id)
            ->first();

        // Fetch the invoice items — bypass TenantScope for items with NULL cid
        $invoice_items = Invoice_items::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('invoice_id', '=', $id)->get();

        // Check if invoice exists before proceeding
        if (!$invoice) {
            return abort(404, 'Invoice not found');
        }

        // Pass both invoice and invoice items to the view
        return view('invoices.preview', compact('invoice', 'invoice_items'));
    }

    public function invoicePdfPreview($id)
    {
        // Fetch the invoice with client details
        $invoice = Invoices::leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->leftJoin('companies', 'clients.cid', '=', 'companies.id')
            ->select('companies.name as cn', 'companies.mob as cm', 'companies.email as ce', 'companies.img', 'companies.gst as cgst', 'companies.vat as cvat', 'companies.address', 'companies.city', 'companies.state', 'companies.zipcode', 'companies.country', 'companies.bank_details', 'clients.name', 'clients.company', 'clients.email', 'clients.mob', 'clients.location', 'invoices.*')
            ->where('invoices.id', '=', $id)
            ->first();

        // Fetch the invoice items — bypass TenantScope for items with NULL cid
        $invoice_items = Invoice_items::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('invoice_id', '=', $id)->get();

        // Get company logo in base64
        $imagePath = public_path('assets/images/company/' . $invoice->img);
        $base64 = '';
        if (!empty($invoice->img) && is_file($imagePath)) {
            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
            $data = file_get_contents($imagePath);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        // Get user signature in base64
        $userSign = Auth::user()->imgsign ?? 'default.png';
        $signPath = public_path('assets/images/signs/' . $userSign);
        $signBase64 = '';
        if (!empty($userSign) && is_file($signPath)) {
            $signData = file_get_contents($signPath);
            $signType = pathinfo($signPath, PATHINFO_EXTENSION);
            $signBase64 = 'data:image/' . $signType . ';base64,' . base64_encode($signData);
        }

        // Load the PDF view for preview
        $pdf = Pdf::loadView('invoices.download', compact('invoice', 'invoice_items', 'base64', 'signBase64'));

        // Remove all characters except letters and digits
        $invoice->invoice_number = preg_replace('/[^A-Za-z0-9]/', '', $invoice->invoice_number);

        // Preview the PDF in browser
        return $pdf->stream('Invoice-' . $invoice->invoice_number . '.pdf');
    }

    public function invoiceDownload($id)
    {
        // Fetch the invoice with client details
        $invoice = Invoices::leftJoin('clients', 'invoices.client_id', '=', 'clients.id')
            ->leftJoin('companies', 'clients.cid', '=', 'companies.id')
            ->select('companies.name as cn', 'companies.mob as cm', 'companies.email as ce', 'companies.img', 'companies.gst as cgst', 'companies.vat as cvat', 'companies.address', 'companies.city', 'companies.state', 'companies.zipcode', 'companies.country', 'companies.bank_details', 'clients.name', 'clients.company', 'clients.email', 'clients.mob', 'clients.location', 'invoices.*')
            ->where('invoices.id', '=', $id)
            ->first();

        // Fetch the invoice items — bypass TenantScope for items with NULL cid
        $invoice_items = Invoice_items::withoutGlobalScope(\App\Scopes\TenantScope::class)
            ->where('invoice_id', '=', $id)->get();

        // Get company logo in base64
        $imagePath = public_path('assets/images/company/' . $invoice->img);
        $base64 = '';
        if (!empty($invoice->img) && is_file($imagePath)) {
            $type = pathinfo($imagePath, PATHINFO_EXTENSION);
            $data = file_get_contents($imagePath);
            $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        // Get user signature in base64
        $userSign = Auth::user()->imgsign ?? 'default.png';
        $signPath = public_path('assets/images/signs/' . $userSign);
        $signBase64 = '';
        if (!empty($userSign) && is_file($signPath)) {
            $signData = file_get_contents($signPath);
            $signType = pathinfo($signPath, PATHINFO_EXTENSION);
            $signBase64 = 'data:image/' . $signType . ';base64,' . base64_encode($signData);
        }

        // Load the PDF view
        $pdf = Pdf::loadView('invoices.download', compact('invoice', 'invoice_items', 'base64', 'signBase64'));

        // Remove all characters except letters and digits
        $invoice->invoice_number = preg_replace('/[^A-Za-z0-9]/', '', $invoice->invoice_number);

        return $pdf->download('Invoice-' . $invoice->invoice_number . '.pdf');
    }

    /**
     * Show the form for creating / editing a project.
     */
    public function manageProject(Request $request)
    {
        $id = $request->id ?? null;
        $project = null;

        if ($id) {
            $project = Projects::leftJoin('clients', 'projects.client_id', '=', 'clients.id')
                ->select('projects.*', 'clients.name as client_name', 'clients.company as client_company')
                ->where('projects.id', $id)
                ->first();
        }

        // Load clients for the dropdown (only current company)
        $clients = Clients::where('name', '!=', '')
            ->orderBy('name', 'ASC')
            ->get(['id', 'name', 'company']);

        // Load users for "Closed by" dropdown (only current company)
        $users = User::where('status', '=', '1')
            ->orderBy('name', 'ASC')
            ->get(['id', 'name']);

        // Auto-generate Project ID for NEW projects OR if existing one is empty
        $generatedId = null;
        if (!$id || ($project && empty($project->project_id_custom))) {
            // Use project's creation year if editing, otherwise current year
            $year = ($project && $project->created_at) ? date('Y', strtotime($project->created_at)) : date('Y');
            $count = Projects::whereYear('created_at', $year)->count() + 1;
            $generatedId = 'PROJ-' . $year . '-' . str_pad($count, 3, '0', STR_PAD_LEFT);
        }

        return view('manageProject', [
            'project' => $project,
            'clients' => $clients,
            'users' => $users,
            'generatedId' => $generatedId,
            'previous_url' => $request->input('previous_url', url()->previous())
        ]);
    }

    /**
     * Save (create or update) a project.
     */
    public function manageProjectPost(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects')->where(function ($query) use ($request) {
                    return $query->where('client_id', $request->client_id)
                        ->where('cid', Auth::user()->cid);
                })->ignore($request->id)
            ],
            'project_id_custom' => 'nullable|string|max:100',
            'closed_by' => 'nullable|exists:users,id',

            'start_date' => 'nullable|date',
            'deadline' => 'nullable|date',
            'type' => 'nullable|string|max:100',
            'amount' => 'nullable|numeric|min:0',
            'status' => 'nullable|integer',
            'note' => 'nullable|string',
            'tags' => 'nullable|string',
            'deployment_url' => 'nullable|url|max:255',
        ]);

        $project = $request->id ? Projects::findOrFail($request->id) : new Projects();

        $project->client_id = $request->client_id;
        $project->name = $request->name;
        $project->batchNo = $request->batchNo ?? '';
        $project->project_id_custom = $request->project_id_custom;
        $project->closed_by = $request->closed_by;

        $project->start_date = $request->start_date;
        $project->deadline = $request->deadline;
        $project->type = $request->type ?? '';
        $project->amount = $request->amount ?? 0;
        $project->status = $request->status ?? 1;
        $project->note = $request->note ?? '';
        $project->tags = $request->tags ?? '';
        $project->deployment_url = $request->deployment_url ?? '';
        $project->save();

        $redirectUrl = $request->input('previous_url') ?: '/projects';
        return redirect($redirectUrl)->with(
            'success',
            $request->id
            ? 'Project updated successfully.'
            : 'Project created successfully.'
        );
    }
}
