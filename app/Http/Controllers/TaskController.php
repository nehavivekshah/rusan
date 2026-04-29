<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\AuthController;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Task;
use App\Models\Projects;
use App\Models\Task_comments;
use App\Models\Task_working_hours;
use App\Models\Todo_lists;

class TaskController extends Controller
{
    use \App\Traits\ActivityLogger;

    protected $taskService;

    public function __construct(\App\Services\TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function task(Request $request)
    {
        $projectId = $request->has('project_id') && $request->project_id !== ''
            ? (int) $request->project_id
            : null;

        $data = $this->taskService->getKanbanData($projectId);
        return view('task', $data);
    }

    public function taskPost(Request $request)
    {
        $request->validate([
            'msg'          => 'nullable|string|max:5000',
            'title'        => 'nullable|string|max:500',
            'des'          => 'nullable|string|max:5000',
            'label'        => 'nullable|string|max:50',
            'uid'          => 'required|exists:users,id',
            'project_id'   => 'nullable|integer',
            'parent_id'    => 'nullable|integer',
            'due_date'     => 'nullable|date',
            'assignee_ids' => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
        ]);

        $tasklist = Task::where('uid', '=', $request->uid)->orderBy('position', 'asc')->get();

        $task = new Task();
        $task->uid        = $request->uid ?: Auth::id();
        $task->project_id = $request->project_id;
        $task->parent_id  = $request->parent_id;
        $task->due_date   = $request->due_date;
        $task->title      = $request->title ?: $request->msg;
        $task->des        = $request->des ?: $request->msg;
        $task->label      = $request->label ?: '#787878';
        $task->whr        = '0';
        $task->position   = '0';
        $task->status     = '0'; // Open


        foreach ($tasklist as $k => $singletask) {
            $tasks           = Task::find($singletask->id);
            $tasks->position = $k + 1;
            $tasks->updated_at = Now();
            $tasks->update();
        }

        $task->save();

        // Sync assignees (always include the primary user)
        $assigneeIds = collect($request->assignee_ids ?? [])->push($request->uid)->unique()->values()->toArray();
        $task->assignees()->sync($assigneeIds);

        $this->logActivity('Task Created', 'tasks', $task->id, $task->title, "Created task: {$task->title}");

        return back()->with('success', 'New Task Added');
    }

    public function taskEdit(Request $request)
    {
        $projectId = $request->has('project_id') && $request->project_id !== ''
            ? (int) $request->project_id
            : null;

        $data = $this->taskService->getKanbanData($projectId);

        $taskSingle = Task::where('id', '=', $request->id)->with('assignees')->get();
        $userSingle = User::where('id', '=', $taskSingle[0]->uid)->get();

        $taskHistory = Task_working_hours::where('taskid', '=', $request->id)
            ->orderBy('id', 'DESC')->get();

        $taskComments = Task_comments::leftJoin('users', 'users.id', '=', 'task_comments.uid')
            ->select('users.name', 'task_comments.*')
            ->where('task_comments.taskid', '=', $request->id)
            ->orderBy('task_comments.id', 'DESC')->get();

        $taskAttachments = \App\Models\TaskAttachment::where('task_id', $request->id)
            ->orderBy('id', 'DESC')->get();

        $data = array_merge($data, [
            'taskSingle'      => $taskSingle,
            'userSingle'      => $userSingle,
            'taskHistory'     => $taskHistory,
            'taskComments'    => $taskComments,
            'taskAttachments' => $taskAttachments
        ]);

        return view('task', $data);
    }

    public function getTaskDetailsAjax($id)
    {
        $taskSingle = Task::select('tasks.*')->where('id', '=', $id)->with('assignees')->get();
        if ($taskSingle->isEmpty()) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $userSingle = User::select('users.name')->where('id', '=', $taskSingle[0]->uid)->get();

        $taskHistory = Task_working_hours::select('task_working_hours.*')
            ->where('taskid', '=', $id)
            ->orderBy('id', 'DESC')->get();

        $taskComments = Task_comments::leftJoin('users', 'users.id', '=', 'task_comments.uid')
            ->select('users.name', 'task_comments.*')
            ->where('task_comments.taskid', '=', $id)
            ->orderBy('task_comments.id', 'DESC')->get();

        $taskAttachments = \App\Models\TaskAttachment::where('task_id', $id)
            ->orderBy('id', 'DESC')->get();

        // All users in this company for multi-assign select
        $allUsers = User::orderBy('name')->get();

        // All projects for project picker in popup
        $projects = Projects::orderBy('name')->get();

        return view('inc.task.popup', [
            'taskSingle'      => $taskSingle,
            'userSingle'      => $userSingle,
            'taskHistory'     => $taskHistory,
            'taskComments'    => $taskComments,
            'taskAttachments' => $taskAttachments,
            'allUsers'        => $allUsers,
            'projects'        => $projects,
        ])->render();
    }

    public function tasksubmit(Request $request)
    {
        if (!empty($request->deltaskid)) {

            $request->validate(['deltaskid' => 'required|exists:tasks,id']);
            $tasks = Task::find($request->deltaskid);
            $tasks->delete();
            return response(['success' => 'Deleted']);

        } else if (!empty($request->userId)) {

            $request->validate([
                'userId' => 'required|exists:users,id',
                'updatedPositions' => 'required|array',
                'updatedPositions.*.taskId' => 'required|exists:tasks,id',
                'updatedPositions.*.position' => 'required|integer',
            ]);

            if (!empty($request->updatedPositions)) {
                foreach ($request->updatedPositions as $taskData) {
                    $task = Task::find($taskData['taskId']);
                    if ($task) {
                        $task->uid      = $request->userId;
                        $task->position = $taskData['position'];
                        $task->update();
                    }
                }
                return response(['success' => 'Positions updated successfully']);
            }
            return response(['error' => 'No data provided']);

        } else if (!empty($request->tskId)) {

            $request->validate([
                'tskId' => 'required|exists:tasks,id',
                'label' => 'required|string',
            ]);

            $tasks        = Task::find($request->tskId);
            $tasks->label = $request->label;
            $tasks->update();
            return response(['success' => 'Updated']);

        } else if (!empty($request->tskstartId)) {

            $taskHistory = Task_working_hours::where('id', $request->tskstartId)
                ->where('status', 0)
                ->first();

            if ($taskHistory) {
                $Task_working_hours            = Task_working_hours::find($request->tskstartId);
                $Task_working_hours->end_time  = Carbon::now()->format('d-m-Y h:i:s a');
                $Task_working_hours->hours     = $request->tskhr;
                $Task_working_hours->status    = '1';
                $Task_working_hours->update();

                $tid = $taskHistory->taskid ?? null;
                if ($tid) {
                    $tasks = Task::find($tid);
                    if ($tasks) {
                        $tasks->label  = "#ff9800";
                        $tasks->status = '1';
                        $tasks->update();
                    }
                }
                return response(['success' => 'Updated']);
            } else {
                $task             = new Task_working_hours();
                $task->taskid     = $request->tskstartId;
                $task->start_time = Carbon::now()->format('d-m-Y h:i:s a');
                $task->end_time   = Carbon::now()->format('d-m-Y h:i:s a');
                $task->hours      = '0';
                $task->status     = '0';
                $task->save();

                $tasks = Task::find($request->tskstartId);
                if ($tasks) {
                    $tasks->label  = "#2196f3";
                    $tasks->status = '0';
                    $tasks->update();
                }
                return response(['success' => 'Inserted']);
            }

        } else if (!empty($request->commenttaskid)) {

            $request->validate([
                'commenttaskid' => 'required|exists:tasks,id',
                'taskcomment' => 'required|string|max:5000',
            ]);

            $task_comments           = new Task_comments();
            $task_comments->uid      = Auth::user()->id;
            $task_comments->taskid   = $request->commenttaskid;
            $task_comments->comments = $request->taskcomment;
            $task_comments->save();

            $taskComments = Task_comments::leftJoin('users', 'users.id', '=', 'task_comments.uid')
                ->select('users.name', 'task_comments.*')
                ->where('task_comments.taskid', '=', $request->commenttaskid)
                ->orderBy('task_comments.id', 'DESC')->get();

            $messages = '<div class="d-flex flex-column gap-3">';
            foreach ($taskComments as $c) {
                $isMine = $c->uid == Auth::user()->id;
                $initial = strtoupper(substr($c->name ?? 'U', 0, 1));
                $bgStyle = $isMine ? 'background:rgba(0,102,102,0.12);color:#006666;' : 'background:rgba(26,115,232,0.10);color:#1a73e8;';
                $boxBg = $isMine ? 'background:#006666; color:#fff;' : 'background:#fff; border:1px solid #e8eaed;';
                $nameCol = $isMine ? 'color:rgba(255,255,255,0.9);' : 'color:#202124;';
                $dateCol = $isMine ? 'color:rgba(255,255,255,0.7);' : 'color:#9aa0a6;';
                $align = $isMine ? 'right' : 'left';
                $rev = $isMine ? 'flex-row-reverse' : '';
                $formattedDate = \Carbon\Carbon::parse($c->created_at)->format('d M Y, H:i');

                $messages .= '
                    <div class="d-flex gap-3 ' . $rev . '">
                        <div style="width:30px; height:30px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:0.75rem; font-weight:700; ' . $bgStyle . '">
                            ' . $initial . '
                        </div>
                        <div class="p-2 px-3 rounded shadow-sm" style="' . $boxBg . ' max-width:85%;">
                            <div class="small fw-bold mb-1" style="' . $nameCol . '">' . e($c->name ?? 'Unknown') . '</div>
                            <div class="small" style="line-height:1.4;">' . e($c->comments ?? '') . '</div>
                            <div style="font-size:0.65rem; margin-top:6px; ' . $dateCol . ' text-align:' . $align . ';">
                                ' . $formattedDate . '
                            </div>
                        </div>
                    </div>';
            }
            $messages .= '</div>';

            return response(['success' => 'Submitted', 'message' => $messages]);

        } else if (!empty($request->taskid)) {

            $request->validate([
                'taskid' => 'required|exists:tasks,id',
                'tasktitle' => 'nullable|string|max:500',
                'taskdes' => 'nullable|string|max:5000',
            ]);

            $tskId = $request->taskid ?? '';
            $tasks = Task::find($tskId);
            if (!empty($request->tasktitle)) {
                $tasks->title = $request->tasktitle;
            } else {
                $tasks->des = $request->taskdes;
            }
            $tasks->update();
            return response(['success' => 'Updated']);
        }
    }

    /**
     * AJAX: Update task assignees and/or project from the detail popup.
     */
    public function updateTaskMeta(Request $request)
    {
        $request->validate([
            'task_id'        => 'required|exists:tasks,id',
            'assignee_ids'   => 'nullable|array',
            'assignee_ids.*' => 'exists:users,id',
            'project_id'     => 'nullable|integer|exists:projects,id',
            'status'         => 'nullable|string',
        ]);

        $task = Task::findOrFail($request->task_id);

        // Update project
        if ($request->has('project_id')) {
            $task->project_id = $request->project_id ?: null;
            $task->save();
        }

        // Update status and sync color (label)
        if ($request->has('status')) {
            $task->status = $request->status;
            
            // Auto-link status color to label
            $statusColors = [
                '0' => '#80868b', // Open
                '1' => '#ea4335', // Urgent
                '2' => '#f29900', // Pending
                '3' => '#1a73e8', // In Progress
                '4' => '#34a853', // Done
                '5' => '#006666', // Closed
            ];
            $task->label = $statusColors[$request->status] ?? '#80868b';
            
            $task->save();

            // Log task completion
            if ($request->status == '4') {
                $this->logActivity('Task Completed', 'tasks', $task->id, $task->title, "Completed task: {$task->title}");
            } else {
                $this->logActivity('Task Status Changed', 'tasks', $task->id, $task->title, "Changed task status: {$task->title}");
            }
        }

        // Sync assignees
        if ($request->has('assignee_ids')) {
            $ids = collect($request->assignee_ids ?? [])->push($task->uid)->unique()->values()->toArray();
            $task->assignees()->sync($ids);
        }

        // Return updated assignees HTML for partial reload
        $task->load('assignees');
        $avatarHtml      = '';
        $boardAvatarHtml = '';
        $count = $task->assignees->count();

        foreach ($task->assignees as $u) {
            $initial = strtoupper(substr($u->name, 0, 1));
            // For Modal detail popup
            $avatarHtml .= '<div class="et-avatar-chip" title="' . e($u->name) . '">' . $initial . '</div>';
        }

        // For Kanban card (take first 4)
        foreach ($task->assignees->take(4) as $u) {
            $initial = strtoupper(substr($u->name, 0, 1));
            $boardAvatarHtml .= '<div class="tk-assignee-chip" title="' . e($u->name) . '">' . $initial . '</div>';
        }
        if ($count > 4) {
            $boardAvatarHtml .= '<div class="tk-assignee-chip tk-assignee-more">+' . ($count - 4) . '</div>';
        }

        return response()->json([
            'success'         => true,
            'avatarHtml'      => $avatarHtml,
            'boardAvatarHtml' => $boardAvatarHtml,
            'assigneeIds'     => $task->assignees->pluck('id'),
        ]);
    }

    /* ── Todo-list (personal checklist) methods ── */

    public function index()
    {
        $tasks = Todo_lists::where('uid', Auth::id())->orderBy('position', 'DESC')->get();
        return response()->json($tasks);
    }

    public function store(Request $request)
    {
        \Log::info("Attempting to store todo item", ['user_id' => Auth::id(), 'data' => $request->all()]);
        $task            = new Todo_lists;
        $task->text      = $request->text;
        $task->uid       = Auth::id();
        $task->completed = $request->completed ? 1 : 0;
        $maxPos          = Todo_lists::where('uid', Auth::id())->max('position');
        $task->position  = ($maxPos ?? 0) + 1;

        if ($request->has('reminder_at')) {
            $task->reminder_at  = !empty($request->reminder_at) ? Carbon::parse($request->reminder_at) : null;
            $task->is_notified  = 0;
        }

        $task->save();
        \Log::info("Todo item saved successfully", ['task_id' => $task->id]);
        return response()->json($task);
    }

    public function update(Request $request, $id)
    {
        $task = Todo_lists::findOrFail($id);
        if ($request->has('text'))      { $task->text      = $request->text; }
        if ($request->has('completed')) { $task->completed = $request->completed ? 1 : 0; }

        if ($request->has('reminder_at')) {
            $task->reminder_at = !empty($request->reminder_at) ? Carbon::parse($request->reminder_at) : null;
            if ($task->reminder_at && $task->reminder_at > Carbon::now()) {
                $task->is_notified = 0;
            }
        }

        $task->save();
        return response()->json($task);
    }

    public function reorder(Request $request)
    {
        $order = $request->order;
        $count = count($order);
        foreach ($order as $index => $id) {
            Todo_lists::where('id', $id)->update(['position' => $count - $index]);
        }
        return response()->json(['message' => 'Order updated']);
    }

    public function saveToken(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            \Log::info("Saving FCM token for user ID {$user->id}: " . substr($request->token, 0, 20) . "...");
            DB::table('users')->where('id', $user->id)->update(['fcm_token' => $request->token]);
            return response()->json([
                'message'      => 'Token saved',
                'user_id'      => $user->id,
                'token_prefix' => substr($request->token, 0, 10)
            ]);
        }
        \Log::warning("Token save attempted but no user is authenticated.");
        return response()->json(['message' => 'User not found'], 404);
    }

    public function destroy($id)
    {
        $task = Todo_lists::findOrFail($id);
        $task->delete();
        return response()->json(['message' => 'Task deleted']);
    }

    public function uploadAttachment(Request $request)
    {
        $request->validate([
            'task_id' => 'required',
            'file'    => 'required|mimes:pdf,doc,docx,jpg,jpeg,png,xls,xlsx,txt|max:10240'
        ]);

        try {
            if ($request->hasFile('file')) {
                $file         = $request->file('file');
                $originalName = $file->getClientOriginalName();
                $fileSize     = $file->getSize();
                $extension    = $file->getClientOriginalExtension();
                $fileName     = time() . '_' . uniqid() . '.' . $extension;
                $path         = public_path('assets/task_attachments');
                
                if (!file_exists($path)) {
                    mkdir($path, 0777, true);
                }
                
                $file->move($path, $fileName);

                $attachment = \App\Models\TaskAttachment::create([
                    'task_id'     => $request->task_id,
                    'file_path'   => 'assets/task_attachments/' . $fileName,
                    'file_name'   => $originalName,
                    'file_type'   => $extension,
                    'file_size'   => $fileSize,
                    'uploaded_by' => Auth::id(),
                ]);

                return response()->json(['status' => 'success', 'attachment' => $attachment]);
            }
        } catch (\Exception $e) {
            \Log::error('Upload Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }

        return response()->json(['status' => 'error', 'message' => 'File not found'], 400);
    }

    public function deleteAttachment($id)
    {
        $attachment = \App\Models\TaskAttachment::findOrFail($id);

        if (\Illuminate\Support\Facades\File::exists(public_path($attachment->file_path))) {
            \Illuminate\Support\Facades\File::delete(public_path($attachment->file_path));
        }

        $attachment->delete();
        return response()->json(['status' => 'success']);
    }

    public function clearAll()
    {
        Todo_lists::where('uid', Auth::id())->delete();
        return response()->json(['message' => 'All tasks cleared']);
    }
}
