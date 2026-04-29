<?php

namespace App\Http\Controllers;

use App\Models\CrmTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CrmTaskController extends Controller
{
    public function index()
    {
        $tasks = CrmTask::orderBy('due_date', 'asc')
            ->get();
        return view('crm_tasks', compact('tasks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string',
            'due_date' => 'nullable|date',
            'rel_type' => 'nullable|string',
            'rel_id' => 'nullable|integer',
            'project_id' => 'nullable|integer',
            'parent_id' => 'nullable|integer',
        ]);

        CrmTask::create([
            'user_id' => Auth::id() ?? 1,
            'rel_type' => $request->rel_type,
            'rel_id' => $request->rel_id,
            'project_id' => $request->project_id,
            'parent_id' => $request->parent_id,
            'name' => $request->name,
            'type' => $request->type,
            'due_date' => $request->due_date,
            'status' => 'Pending'
        ]);

        return back()->with('success', 'Task created successfully.');
    }

    public function updateStatus(Request $request)
    {
        $request->validate(['id' => 'required|exists:crm_tasks,id', 'status' => 'required|string']);
        $task = CrmTask::find($request->id);
        if ($task) {
            $task->status = $request->status;
            $task->save();
            return response()->json(['success' => true]);
        }
        return response()->json(['success' => false], 404);
    }
}
