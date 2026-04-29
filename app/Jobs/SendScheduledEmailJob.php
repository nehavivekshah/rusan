<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Mail\ScheduledEmail;
use Illuminate\Support\Facades\Mail;
use App\Models\ScheduledEmail as ScheduledEmailModel;

class SendScheduledEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $scheduledEmail;

    public function __construct(ScheduledEmailModel $scheduledEmail)
    {
        $this->scheduledEmail = $scheduledEmail;
    }

    public function handle()
    {
        $data = [
            'title' => $this->scheduledEmail->subject,
            'content' => $this->scheduledEmail->body,
        ];

        Mail::to($this->scheduledEmail->recipient)->send(new ScheduledEmail($data));

        $this->scheduledEmail->delete();
    }
}
