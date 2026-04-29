<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Leads;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class LeadUIController extends Controller
{
    /**
     * Display the Kanban board view.
     */
    public function kanbanView()
    {
        $getUsers = User::where('status', '1')
            ->orderBy('name')
            ->get();

        return view('leads_kanban', compact('getUsers'));
    }

    /**
     * Fetch leads for the Kanban board — supports per-stage pagination.
     *
     * Query params:
     *   stage  (int)  - lead status integer (0,1,2,3,5,9). Omit for summary-only (counts).
     *   page   (int)  - page number (default 1)
     *   limit  (int)  - cards per page (default 15, max 50)
     */
    public function kanbanData(Request $request)
    {
        $stageMap = [
            'New'       => 0,
            'Contacted' => 1,
            'Qualified' => 2,
            'Proposal'  => 3,
            'Closed'    => 5,
            'Lost'      => 9,
        ];

        // ── Shared filter builder ──────────────────────────────────────────
        $buildQuery = function ($statusInt) use ($request) {
            $q = Leads::where('status', $statusInt);

            if ($s = trim($request->get('search', ''))) {
                $q->where(function ($sub) use ($s) {
                    $sub->where('name', 'like', "%$s%")
                        ->orWhere('company', 'like', "%$s%")
                        ->orWhere('mob', 'like', "%$s%");
                });
            }
            if ($assigned = $request->get('assigned')) {
                $q->where('assigned', (int) $assigned);
            }
            if ($from = $request->get('date_from')) {
                $q->whereDate('created_at', '>=', $from);
            }
            if ($to = $request->get('date_to')) {
                $q->whereDate('created_at', '<=', $to);
            }
            if ($industry = $request->get('industry')) {
                $q->where('industry', $industry);
            }
            return $q;
        };

        // Summary-only mode: return counts for all stages
        if ($request->missing('stage')) {
            $counts = [];
            foreach ($stageMap as $label => $statusInt) {
                $counts[$label] = $buildQuery($statusInt)->count();
            }
            return response()->json(['counts' => $counts]);
        }

        // Per-stage paginated mode
        $stage  = (int) $request->get('stage', 0);
        $limit  = min((int) $request->get('limit', 15), 50);
        $page   = max((int) $request->get('page', 1), 1);
        $offset = ($page - 1) * $limit;

        $query = $buildQuery($stage);
        $total = $query->count();

        $leads = (clone $query)
            ->orderByRaw('CASE WHEN assigned IS NULL OR assigned = 0 THEN 0 ELSE 1 END ASC')
            ->orderBy('updated_at', 'desc')
            ->skip($offset)
            ->take($limit)
            ->get(['id', 'name', 'company', 'mob', 'whatsapp', 'email', 'values', 'poc', 'source', 'purpose', 'score', 'status', 'assigned']);

        return response()->json([
            'data'     => $leads,
            'total'    => $total,
            'page'     => $page,
            'limit'    => $limit,
            'has_more' => ($offset + $limit) < $total,
        ]);
    }

    /**
     * Update the status of a lead via Drag & Drop.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'id'     => 'required|exists:leads,id',
            'status' => 'required|integer',
        ]);

        $lead = Leads::find($request->id);
        if ($lead) {
            $lead->status = $request->status;
            $lead->save();
            return response()->json(['success' => true, 'message' => 'Status updated']);
        }

        return response()->json(['success' => false, 'message' => 'Lead not found'], 404);
    }

    /**
     * Get a unique list of industries from the leads table for filtering.
     */
    public function getLeadIndustries()
    {
        $industries = Leads::whereNotNull('industry')
            ->where('industry', '!=', '')
            ->distinct()
            ->orderBy('industry')
            ->pluck('industry');

        return response()->json(['industries' => $industries]);
    }
}
