<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Opportunity;
use App\Models\Clients;
use Illuminate\Support\Facades\Auth;

class OpportunityController extends Controller
{
    public function index()
    {
        $clients = Clients::get();
        return view('opportunities_kanban', compact('clients'));
    }

    public function kanbanData()
    {
        // Simple manual eager loading or left join fallback if relationship is not fully defined
        $opportunities = Opportunity::leftJoin('clients', 'opportunities.customer_id', '=', 'clients.id')
            ->select('opportunities.*', 'clients.company as company_name', 'clients.name as client_name')
            ->orderBy('opportunities.updated_at', 'desc')
            ->get();

        return response()->json(['data' => $opportunities]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:clients,id',
            'name' => 'required|string|max:255',
            'amount' => 'nullable|numeric',
            'expected_close_date' => 'nullable|date',
            'stage' => 'required|string'
        ]);

        Opportunity::create([
            'customer_id' => $request->customer_id,
            'user_id' => Auth::id() ?? 1,
            'name' => $request->name,
            'stage' => $request->stage,
            'amount' => $request->amount,
            'expected_close_date' => $request->expected_close_date
        ]);

        return back()->with('success', 'Opportunity created successfully.');
    }

    public function updateStage(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:opportunities,id',
            'stage' => 'required|string'
        ]);

        $opportunity = Opportunity::find($request->id);
        if ($opportunity) {
            $opportunity->stage = $request->stage;
            if ($request->has('reason')) {
                $opportunity->win_loss_reason = $request->reason;
            }
            $opportunity->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}
