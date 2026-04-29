<?php

namespace App\Services;

use App\Models\User;
use App\Models\Task;
use App\Models\Projects;
use App\Models\Task_working_hours;
use Illuminate\Support\Facades\Auth;

class TaskService
{
    /**
     * Get tasks grouped by user for the Kanban board.
     * Supports optional project_id filter and multi-user assignees.
     *
     * @return array
     */
    public function getKanbanData(?int $projectId = null)
    {
        $roles     = session('roles');
        $roleArray = explode(',', ($roles->permissions ?? ''));
        $canAddTask = in_array('tasks_add', $roleArray) || in_array('All', $roleArray);

        if ($roles->features == 'All') {
            $users = User::where('cid', '=', Auth::user()->cid)->orderBy('id', 'DESC')->get();
        } else {
            $users = User::where('id', '=', Auth::user()->id)->get();
            $assignedIds = explode(',', ($users[0]->assign ?? ''));
            if (!empty(array_filter($assignedIds))) {
                $assignedUsers = User::whereIn('id', $assignedIds)->get();
                $users = $users->merge($assignedUsers);
            }
        }

        // All projects for this company (for filter dropdown)
        $projects = Projects::where('cid', Auth::user()->cid)
            ->orderBy('name', 'asc')
            ->get();

        $kanbanData = [];

        foreach ($users as $user) {
            // Base query: owned tasks OR tasks where user is in task_assignees
            $query = Task::where(function ($q) use ($user) {
                    $q->where('uid', $user->id)
                      ->orWhereHas('assignees', fn($a) => $a->where('user_id', $user->id));
                })
                ->with(['project', 'parent', 'assignees'])
                ->orderBy('position', 'asc');

            // Apply project filter if set
            if ($projectId) {
                $query->where('project_id', $projectId);
            }

            $tasks = $query->get();

            // Enrich tasks
            $enrichedTasks = $tasks->map(function ($task) {
                $taskHistory = Task_working_hours::where('taskid', '=', $task->id)->get();
                $task->is_highlighted   = (!empty($taskHistory[0]->id) && $taskHistory[0]->status == '0');
                $task->attachment_count = \App\Models\TaskAttachment::where('task_id', $task->id)->count();
                return $task;
            });

            $kanbanData[] = [
                'user'  => $user,
                'tasks' => $enrichedTasks,
            ];
        }

        return [
            'kanbanData'  => $kanbanData,
            'canAddTask'  => $canAddTask,
            'users'       => $users,
            'projects'    => $projects,
            'activeProjectId' => $projectId,
        ];
    }
}
