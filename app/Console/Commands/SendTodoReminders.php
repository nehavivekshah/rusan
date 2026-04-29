<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SendTodoReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'todo:send-reminders';
    protected $description = 'Send Firebase notifications for due todo list items';

    public function handle()
    {
        $now = \Carbon\Carbon::now();

        // Find tasks that are due, not completed, and not yet notified
        $tasks = \App\Models\Todo_lists::where('reminder_at', '<=', $now)
            ->where('is_notified', 0)
            ->where('completed', 0)
            ->whereNotNull('reminder_at')
            ->get();

        if ($tasks->count() > 0) {
            $this->info("Found {$tasks->count()} tasks to notify.");

            try {
                $factory = (new \Kreait\Firebase\Factory)
                    ->withServiceAccount(base_path(env('FIREBASE_CREDENTIALS')));
                $messaging = $factory->createMessaging();

                foreach ($tasks as $task) {
                    $user = \App\Models\User::find($task->uid);

                    if ($user && $user->fcm_token) {
                        try {
                            $message = \Kreait\Firebase\Messaging\CloudMessage::withTarget('token', $user->fcm_token)
                                ->withNotification(\Kreait\Firebase\Messaging\Notification::create(
                                    'Todo Reminder',
                                    $task->text
                                ))
                                ->withData(['task_id' => $task->id]);

                            $messaging->send($message);
                            $this->info("Notification sent to user {$user->id} for task {$task->id}");
                        } catch (\Exception $e) {
                            $this->error("Failed to send notification for task {$task->id}: " . $e->getMessage());
                        }
                    }

                    // Mark as notified regardless of success to avoid spamming
                    $task->is_notified = 1;
                    $task->save();
                }
            } catch (\Exception $e) {
                $this->error("Firebase Error: " . $e->getMessage());
            }
        } else {
            $this->info("No due tasks found.");
        }
    }
}
