<?php

namespace App\Http\Controllers;

use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Fcmregs;
use App\Models\Companies;
use App\Models\User;
use App\Models\Task;
use App\Models\Task_working_hours;
use App\Models\Attendances;
use App\Models\Leads;
use App\Models\Lead_comments;
use Carbon\Carbon;

class ApiController extends Controller
{
    protected $messaging;

    public function __construct()
    {
        try {

            // Initialize Firebase Factory with service account credentials
            $factory = (new Factory)
                ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));

            $this->messaging = $factory->createMessaging();

        } catch (\Exception $e) {

            Log::error('Firebase Initialization Error: ' . $e->getMessage());
            abort(500, 'Unable to initialize Firebase');

        }
    }

    public function registerFcm(Request $request)
    {
        $validated = $request->validate([
            'regID' => 'required|string',
            'mono' => 'nullable|string|max:15',
            'click_action' => 'nullable|url',
        ]);

        $regId = $validated['regID'];
        $mono = $validated['mono'];
        $url = $validated['click_action'] ?? 'https://esecrm.com';

        // Log incoming request data
        Log::info('Register FCM Request', compact('regId', 'mono', 'url'));

        // Check for existing FCM registration
        $fcmreg = Fcmregs::where('mono', $mono)->orWhere('regID', $regId)->first();

        if ($fcmreg) {
            // Update existing record
            if (empty($fcmreg->mono)) {
                $fcmreg->mono = $mono;
                $fcmreg->save();
                return response()->json(['status' => 'Device ID Updated!'], 200);
            } else {
                $fcmreg->regID = $regId;
                $fcmreg->save();
                return response()->json(['status' => 'Device Token Updated!'], 200);
            }
        }

        // Create a new record
        $newFcmReg = new Fcmregs();
        $newFcmReg->regID = $regId;
        $newFcmReg->mono = $mono;
        $newFcmReg->save();

        try {
            // Create the notification message
            $message = CloudMessage::fromArray([
                'token' => $regId,
                'notification' => [
                    'title' => 'Esecrm',
                    'body' => 'Welcome to esecrm',
                ],
                'data' => [
                    'click_action' => $url,
                ],
            ]);

            // Send the notification
            $this->messaging->send($message);

            return response()->json(['status' => 'Device Token Registered and Notification Sent!'], 200);
        } catch (\Exception $e) {
            Log::error('Firebase Notification Error: ' . $e->getMessage());
            return response()->json([
                'status' => 'Device registered, but notification failed.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function sendNotification(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string',
            'msg' => 'required|string',
            'mono' => 'required|string|max:15',
        ]);

        $title = $validated['title'];
        $msg = $validated['msg'];
        $mono = $validated['mono'];

        $fcmregs = Fcmregs::where('mono', $mono)->get();

        if ($fcmregs->isEmpty()) {
            return response()->json(['status' => 'No devices found for the provided mobile number.'], 404);
        }

        try {
            foreach ($fcmregs as $fcmreg) {
                if (empty($fcmreg->regID)) {
                    Log::warning('Skipping notification for missing regID for mono: ' . $mono);
                    continue;
                }

                $message = CloudMessage::fromArray([
                    'token' => $fcmreg->regID,
                    'notification' => [
                        'title' => $title,
                        'body' => $msg,
                    ],
                ]);

                $report = $this->messaging->send($message);
                Log::info('FCM Send Success (API)', ['report' => $report, 'token' => $fcmreg->regID]);
            }

            return response()->json(['status' => 'Message Sent'], 200);
        } catch (\Exception $e) {
            Log::error('FCM Send Error (API)', [
                'message' => $e->getMessage(),
                'mono' => $mono,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function checkLogin()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }

        $email = $_SESSION['loginEmail'] ?? '';
        $password = $_SESSION['loginPassword'] ?? '';

        return response()->json(['email' => $email, 'password' => $password], 200);
    }

    public function attendancePost(Request $request)
    {
        $clientIp = $request->ip();

        // Check if provided IP matches client IP
        if ($request->userIp !== $clientIp) {
            Log::warning('Attendance IP Mismatch', [
                'user_id' => $request->user_id,
                'request_ip' => $request->userIp,
                'detected_ip' => $clientIp
            ]);
            return response()->json([
                'message' => 'Unauthorized device or IP address.',
                'client_ip' => $clientIp
            ], 403);
        }

        // Validate request
        /*$request->validate([
            'user_id'   => 'required|exists:users,id',
            'date'      => 'required|date',
            'check_in'  => 'nullable|date_format:H:i',
            'check_out' => 'nullable|date_format:H:i|after:check_in',
            'status'    => 'nullable|string',
            'remarks'   => 'nullable|string',
        ])*/
        ;

        $user = User::where('email', $request->user_id)
            ->orWhere('id', $request->user_id)
            ->first();

        if (!$user) {
            return response()->json(['message' => 'User not found.'], 404);
        }

        $workingTimes = json_decode($request->working_times ?? null);

        $attendance = Attendances::where('user_id', $user->id)
            ->where('date', $request->date)
            ->first();

        if ($attendance) {
            // Update check-out and other details
            if ($request->check_out) {
                $attendance->check_out = $request->check_out;
            }
            if ($request->remarks !== null) {
                $attendance->remarks = $request->remarks;
            }
            if ($request->status !== null) {
                $attendance->status = $request->status;
            }

            $now = Carbon::now();

            // Get latest task for user
            $taskHistory = Task_working_hours::leftJoin('tasks', 'task_working_hours.taskid', '=', 'tasks.id')
                ->select('task_working_hours.*')
                ->where('tasks.uid', $user->id)
                ->where('task_working_hours.hours', 0)
                ->where('task_working_hours.status', 0)
                ->orderBy('task_working_hours.created_at', 'desc') // to get the latest
                ->first();

            if ($taskHistory) {
                $start = Carbon::parse($taskHistory->start_time);
                $duration = $start->diffInMinutes($now);

                $taskHistory->end_time = $now->format('d-m-Y h:i:s a');
                $taskHistory->hours = $duration;
                $taskHistory->status = 1;
                $taskHistory->save();

                $task = Task::find($taskHistory->taskid);
                if ($task) {
                    $task->label = "#ff9800";
                    $task->status = '1';
                    $task->save();
                }
            }
        } else {
            // New attendance check-in
            $attendance = new Attendances();
            $attendance->user_id = $user->id;
            $attendance->cid = $user->cid; // Explicitly set cid as Auth::check() is false in API
            $attendance->date = $request->date;
            $attendance->check_in = $request->check_in;
            $attendance->check_out = $request->check_out;
            $attendance->status = $request->status ?? 'Present';
            $attendance->remarks = $request->remarks;

            // // New task entry
            // $checkTask = Task::where('uid', $user->id)->orderBy("created_at", "DESC")->first();
            // if ($checkTask) {
            //     $now = Carbon::now();
            //     $taskWorking = new Task_working_hours();
            //     $taskWorking->taskid = $checkTask->id;
            //     $taskWorking->start_time = $now->format('d-m-Y h:i:s a');
            //     $taskWorking->end_time = $now->format('d-m-Y h:i:s a');
            //     $taskWorking->hours = 0;
            //     $taskWorking->status = 0;
            //     $taskWorking->save();

            //     $task = Task::find($checkTask->id);
            //     if ($task) {
            //         $task->label = "#2196f3";
            //         $task->status = '0';
            //         $task->save();
            //     }
            // }

            //$checkTask = Task::where('uid', $user->id)->latest()->first();
            $checkTask = Task_working_hours::leftJoin('tasks', 'task_working_hours.taskid', '=', 'tasks.id')
                ->select('task_working_hours.*')
                ->where('tasks.uid', $user->id)
                ->orderBy('task_working_hours.created_at', 'desc')
                ->first();

            if ($checkTask) {
                $now = Carbon::now();

                $taskWorking = new Task_working_hours();
                $taskWorking->taskid = $checkTask->taskid;
                $taskWorking->start_time = $now;
                $taskWorking->end_time = $now;
                $taskWorking->hours = 0;
                $taskWorking->status = 0;
                $taskWorking->save();

                $task = Task::find($checkTask->taskid);
                if ($task) {
                    $task->label = "#2196f3";
                    $task->status = '0';
                    $task->save();
                }
            }
        }

        $attendance->save();

        return response()->json([
            'message' => 'Attendance recorded successfully',
            'data' => $attendance
        ]);
    }

    public function enquiryPost(Request $request)
    {
        // Optional: Validate domain and token
        //$expectedDomain = 'yourdomain.com'; // Replace with real domain
        //$incomingDomain = parse_url($request->website, PHP_URL_HOST);

        $token = $request->query('token');
        $getCompanyId = explode('ese$$', $token);
        $validateToken = md5("eseCRMLeadSync") . 'ese$$' . ($getCompanyId[1] ?? '');

        if ($token !== $validateToken) { // || $incomingDomain !== $expectedDomain
            return response()->json([
                'success' => false,
                'message' => 'Invalid token or unauthorized domain.',
            ], 403);
        }

        $company = Companies::where('id', '=', ($getCompanyId[1] ?? ''))->first();

        $location = json_encode($request->address ?? '');

        if (empty($request->id)) {
            // --- Phase 2: Duplicate Detection ---
            $isDuplicate = 0;
            if (!empty($request->email) || !empty($request->mob)) {
                $existingQuery = Leads::where('cid', $company->id ?? '')
                    ->where(function ($q) use ($request) {
                        if (!empty($request->email))
                            $q->orWhere('email', $request->email);
                        if (!empty($request->mob))
                            $q->orWhere('mob', $request->mob);
                    });
                if ($existingQuery->exists()) {
                    $isDuplicate = 1;
                }
            }

            $lead = new Leads();
            $lead->cid = $company->id ?? '';
            $lead->name = $request->name ?? '';
            $lead->email = $request->email ?? '';
            $lead->mob = $request->mob ?? '';
            $lead->gstno = $request->gstno ?? '';
            $lead->whatsapp = $request->whatsapp ?? '';
            $lead->company = $request->company ?? '';
            $lead->position = $request->position ?? '';
            $lead->industry = $request->industry ?? '';
            $lead->location = $location ?? '';
            $lead->website = '';

            // --- Phase 2: Auto-Assignment Logic ---
            $assignee = $request->assigned;
            if (empty($assignee)) {
                $leastLoadedUser = \Illuminate\Support\Facades\DB::table('users')
                    ->leftJoin('leads', 'users.name', '=', 'leads.assigned')
                    ->where('users.cid', $company->id ?? '')
                    ->select('users.name', \Illuminate\Support\Facades\DB::raw('COUNT(leads.id) as leads_count'))
                    ->groupBy('users.id', 'users.name')
                    ->orderBy('leads_count', 'asc')
                    ->first();
                $assignee = $leastLoadedUser ? $leastLoadedUser->name : '';
            }
            $lead->assigned = $assignee;

            // --- Phase 2: Lead Scoring Algorithm ---
            $score = 0;
            if (!empty($request->name))
                $score += 10;
            if (!empty($request->email))
                $score += 20;
            if (!empty($request->mob) || !empty($request->whatsapp))
                $score += 20;
            if (!empty($request->company))
                $score += 10;
            if (!empty($request->position))
                $score += 10;
            if (!empty($request->industry))
                $score += 10;
            if (!empty($request->value) && is_numeric($request->value) && $request->value > 0)
                $score += 20;

            $lead->score = min($score, 100);
            $lead->is_duplicate = $isDuplicate;

            $lead->purpose = $request->subject ?? '';
            $lead->values = $request->value ?? '';
            $lead->language = $request->language ?? '';
            $lead->poc = $request->website ?? '';

            $lead->status = '0';

            if ($lead->save()) {
                if (!empty($request->nxtDate) || !empty($request->message)) {

                    $leadComments = new Lead_comments();

                    $leadComments->lead_id = $lead->id ?? '';
                    $leadComments->msg = $request->message ?? '';
                    $leadComments->next_date = $request->nxtDate ?? null;

                    $leadComments->save();

                }

                return response()->json([
                    'success' => true,
                    'message' => 'Lead successfully added.',
                    'data' => $lead,
                ], 201);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to add lead.',
            ], 500);
        } else {
            // Update existing lead
            $lead = Leads::find($request->id);
            if (!$lead) {
                return response()->json([
                    'success' => false,
                    'message' => 'Lead not found.',
                ], 404);
            }

            $lead->fill([
                'cid' => $company->id ?? '',
                'name' => $request->name ?? '',
                'email' => $request->email ?? '',
                'mob' => $request->mob ?? '',
                'gstno' => $request->gstno ?? '',
                'whatsapp' => $request->whatsapp ?? '',
                'company' => $request->company ?? '',
                'position' => $request->position ?? '',
                'industry' => $request->industry ?? '',
                'location' => $location ?? '',
                'website' => $request->website ?? '',
                'assigned' => $request->assigned ?? '',
                'purpose' => $request->purpose ?? '',
                'values' => $request->value ?? '',
                'language' => $request->language ?? '',
                'poc' => $request->poc ?? '',
                'status' => $request->status ?? '10',
            ]);

            if ($lead->save()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Lead updated successfully.',
                    'data' => $lead,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update lead.',
            ], 500);
        }
    }
}

?>