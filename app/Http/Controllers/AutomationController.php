<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Automation;

class AutomationController extends Controller
{
    public function index()
    {
        $automations = Automation::orderBy('created_at', 'desc')->get();
        return view('automations', compact('automations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'trigger_event' => 'required|string|max:255',
            'action' => 'required|string|max:255'
        ]);

        Automation::create([
            'trigger_event' => $request->trigger_event,
            'action' => $request->action,
            'status' => 'Active'
        ]);

        return back()->with('success', 'Automation rule created successfully.');
    }

    public function toggleStatus(Request $request)
    {
        $request->validate(['id' => 'required|exists:automations,id']);
        $automation = Automation::find($request->id);
        if ($automation) {
            $automation->status = $automation->status == 'Active' ? 'Inactive' : 'Active';
            $automation->save();
            return response()->json(['success' => true, 'status' => $automation->status]);
        }
        return response()->json(['success' => false], 404);
    }
}
