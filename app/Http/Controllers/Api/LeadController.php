<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Leads;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class LeadController extends Controller
{
    /**
     * Store a newly created lead in storage.
     * and handle auto-assignment, scoring, and duplicate detection.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mob' => 'required|string|max:20',
            'company' => 'nullable|string|max:255',
            'source' => 'nullable|string|max:255',
            'purpose' => 'nullable|string|max:255',
        ]);

        // 1. Duplicate Detection (by Email or Phone)
        $isDuplicate = Leads::where('email', $validatedData['email'])
            ->orWhere('mob', $validatedData['mob'])
            ->exists();

        // 2. Lead Scoring Algorithm (Basic Example)
        $score = 0;
        if (!empty($validatedData['company']))
            $score += 20;
        if (!empty($validatedData['purpose']))
            $score += 30;
        if ($validatedData['source'] === 'Website')
            $score += 10;
        else if ($validatedData['source'] === 'Ads')
            $score += 20;

        // 3. Auto-Assignment Logic (Round Robin Example based on least assigned leads)
        // Find a sales agent (uid) with the least number of new leads assigned.
        // Assuming roles or specific condition determines who is a 'sales agent'. Let's pick any user for now or specify logic.
        // $assignedUser = User::withCount(['leads' => function($query) {
        //    $query->where('status', 'New');
        // }])->orderBy('leads_count', 'asc')->first();

        // Since we don't have the User <-> Leads relationship confirmed, we'll do a simple raw query:
        // Find the user ID that appears least in leads.assigned column.
        $assignedUserId = \DB::table('users')
            ->leftJoin('leads', 'users.id', '=', 'leads.assigned')
            ->select('users.id', \DB::raw('count(leads.id) as leads_count'))
            // Optional: ->where('users.role', 'sales') 
            ->groupBy('users.id')
            ->orderBy('leads_count', 'asc')
            ->first();

        // 4. Create Lead
        $lead = Leads::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'mob' => $validatedData['mob'],
            'company' => $validatedData['company'] ?? null,
            'source' => $validatedData['source'] ?? 'API',
            'purpose' => $validatedData['purpose'] ?? null,
            'status' => 'New',
            'is_duplicate' => $isDuplicate,
            'score' => $score,
            'assigned' => $assignedUserId ? $assignedUserId->id : null,
            // default required fields to something valid if needed based on table definition:
            'cid' => 1, // Assuming Company ID 1 for now if required
            'uid' => $assignedUserId ? $assignedUserId->id : 1, // Created By
        ]);

        return response()->json([
            'message' => 'Lead captured successfully',
            'data' => $lead
        ], 201);
    }
}
