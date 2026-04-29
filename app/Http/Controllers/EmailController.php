<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduledEmail;
use App\Jobs\SendScheduledEmailJob;
use Carbon\Carbon;

class EmailController extends Controller
{
    public function sendEmail(Request $request)
    {
        $request->validate([
            'recipient' => 'required|email',
            'subject' => 'required|string|max:255',
            'body' => 'required|string',
            'send_at' => 'required|date|after:now',
        ]);

        $scheduledEmail = ScheduledEmail::create([
            'recipient' => $request->recipient,
            'subject' => $request->subject,
            'body' => $request->body,
            'send_at' => new Carbon($request->send_at),
        ]);

        SendScheduledEmailJob::dispatch($scheduledEmail)->delay(new Carbon($request->send_at));

        return response()->json(['message' => 'Email scheduled successfully.']);
    }
}
