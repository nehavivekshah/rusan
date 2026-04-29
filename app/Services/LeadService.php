<?php

namespace App\Services;

use App\Models\Leads;
use App\Models\User;
use App\Models\Task;
use App\Models\Lead_comments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LeadService extends BaseService
{
    /**
     * Get paginated leads with complex sorting and priority logic.
     */
    public function getPaginatedLeads($search = null, $status = null, $perPage = 50)
    {
        $today = Carbon::now()->format('Y-m-d H:i:s');

        $query = Leads::leftJoin('lead_comments', function ($join) {
            $join->on('leads.id', '=', 'lead_comments.lead_id')
                ->whereIn('lead_comments.next_date', function ($query) {
                    $query->select(DB::raw('MAX(next_date)'))
                        ->from('lead_comments')
                        ->whereColumn('lead_comments.lead_id', 'leads.id');
                });
        })
        ->select(
            'leads.*',
            'lead_comments.msg',
            DB::raw('MAX(lead_comments.next_date) as next_date'),
            DB::raw('MAX(lead_comments.created_at) as last_talk'),
            DB::raw("
                CASE
                    WHEN leads.status = 1 AND MAX(lead_comments.next_date) <= '$today' THEN 1
                    WHEN leads.status = 0 THEN 2
                    WHEN leads.status = 1 THEN 3
                    WHEN leads.status = 9 THEN 4
                    ELSE 5
                END as priority_order
            ")
        )
        ->groupBy('leads.id', 'lead_comments.msg'); // Simplified grouping, usually depends on DB config

        // Tenant filtering is handled by Global Scope automatically now

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('leads.name', 'like', "%{$search}%")
                    ->orWhere('leads.company', 'like', "%{$search}%")
                    ->orWhere('leads.email', 'like', "%{$search}%")
                    ->orWhere('leads.mob', 'like', "%{$search}%")
                    ->orWhere('leads.whatsapp', 'like', "%{$search}%")
                    ->orWhere('leads.location', 'like', "%{$search}%")
                    ->orWhere('leads.tags', 'like', "%{$search}%")
                    ->orWhere('lead_comments.msg', 'like', "%{$search}%");
            });
        }

        if ($status !== null) {
            $query->where('leads.status', '=', $status);
        }

        return $query->orderBy('priority_order', 'asc')
            ->orderBy(DB::raw('MAX(lead_comments.next_date)'), 'asc')
            ->paginate($perPage);
    }

    /**
     * Calculate lead score based on data completeness.
     */
    public function calculateScore(array $data)
    {
        $score = 0;
        if (!empty($data['name'])) $score += 10;
        if (!empty($data['email'])) $score += 20;
        if (!empty($data['mob']) || !empty($data['whatsapp'])) $score += 20;
        if (!empty($data['company'])) $score += 10;
        if (!empty($data['position'])) $score += 10;
        if (!empty($data['industry'])) $score += 10;
        if (!empty($data['value']) && is_numeric($data['value']) && $data['value'] > 0) $score += 20;

        return min($score, 100);
    }

    /**
     * Find the least loaded user for auto-assignment.
     */
    public function getLeastLoadedUser($companyId)
    {
        $user = DB::table('users')
            ->leftJoin('leads', 'users.id', '=', 'leads.assigned')
            ->where('users.cid', $companyId)
            ->where('users.status', '1') // Only active users
            ->select('users.id', DB::raw('COUNT(leads.id) as leads_count'))
            ->groupBy('users.id')
            ->orderBy('leads_count', 'asc')
            ->first();

        return $user ? $user->id : Auth::id();
    }

    /**
     * Check if a lead is a duplicate.
     */
    public function isDuplicate($email, $mobile, $companyId)
    {
        if (empty($email) && empty($mobile)) return false;

        return Leads::where('cid', $companyId)
            ->where(function ($q) use ($email, $mobile) {
                if ($email) $q->orWhere('email', $email);
                if ($mobile) $q->orWhere('mob', $mobile);
            })
            ->exists();
    }

    /**
     * Basic follow-up task creation.
     */
    public function createFollowUpTask(Leads $lead)
    {
        $task = new Task();
        $task->cid = $lead->cid;
        $task->uid = Auth::id();
        $task->title = "Initial Follow-up: " . $lead->name;
        $task->des = "New lead acquired. Please verify details and initiate contact with " . $lead->name . " from " . ($lead->company ?? 'direct source') . ".";
        $task->status = '1'; 
        $task->label = '#4e73df';
        $task->whr = '0';
        return $task->save();
    }
}
