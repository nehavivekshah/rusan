<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Console\Commands\SendScheduledEmails;
use Illuminate\Support\Facades\Artisan;

class SchedulerTestController extends Controller
{
    public function run()
    {
        // Run the scheduled email command manually
        Artisan::call('app:send-scheduled-emails');

        // Optional: get command output
        $output = Artisan::output();

        return response()->json([
            'status' => 'success',
            'message' => 'Scheduler executed',
            'output' => $output
        ]);
    }
}
