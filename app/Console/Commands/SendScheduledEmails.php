<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Models\EmailTemplate;
use App\Models\Contracts;
use App\Models\Invoices;
use App\Models\NotificationHistory;
use App\Models\User;
use App\Models\SmtpSettings;
use App\Mail\CustomMailable;

class SendScheduledEmails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-scheduled-emails';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send emails for contracts and invoices based on template reminder days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting scheduled email reminders...");

        // Fetch all active templates with reminder_days
        $templates = EmailTemplate::where('is_active', true)
                                  ->whereNotNull('reminder_days')
                                  ->get();

        foreach ($templates as $template) {

            // Determine which date field to track
            $eventField = match($template->module) {
                'contracts' => 'end_date',
                'invoices'  => 'due_date',
                default     => null,
            };

            if (!$eventField) {
                \Log::warning("Template ID {$template->id} has unknown module '{$template->module}'");
                continue;
            }

            // Loop through each reminder day
            foreach ($template->reminder_days as $daysBefore) {

                $targetDate = now()->addDays($daysBefore)->toDateString();

                // Fetch the relevant items
                $items = match($template->module) {
                    'contracts' => Contracts::with('client')->where($eventField, $targetDate)->get(),
                    'invoices'  => Invoices::with('client')->where($eventField, $targetDate)->get(),
                    default     => collect(),
                };

                foreach ($items as $item) {

                    $client = $item->client;
                    if (!$client || !$client->email) {
                        \Log::warning("Item ID {$item->id} has no client email.");
                        continue;
                    }

                    // Check if reminder was already sent
                    $alreadySent = NotificationHistory::where('module', $template->module)
                        ->where('item_id', $item->id)
                        ->where('template_id', $template->id)
                        ->where('days_before', $daysBefore)
                        ->exists();

                    if ($alreadySent) {
                        \Log::info("Reminder already sent for {$client->email}, Template: {$template->subject}, Days before: {$daysBefore}");
                        continue;
                    }

                    // Replace placeholders in email body
                    $template_subject = $template->subject;
                    $template_subject = str_replace([
                        '{{client_name}}',
                        '{{company_name}}',
                        '{{client_contract}}',
                        '{{invoice_number}}',
                        '{{amount}}',
                        '{{due_date}}',
                        '{{end_date}}'
                    ], [
                        $client->name ?? '',
                        config('app.name'),
                        $item->subject ?? $item->id ?? '',
                        $item->invoice_number ?? '',
                        $item->total_amount ?? $item->value ?? '',
                        $item->due_date ?? '',
                        $item->end_date ?? '',
                    ], $template_subject);

                    // Replace placeholders in email body
                    $body = $template->body;
                    $body = str_replace([
                        '{{client_name}}',
                        '{{company_name}}',
                        '{{client_contract}}',
                        '{{invoice_number}}',
                        '{{amount}}',
                        '{{due_date}}',
                        '{{end_date}}'
                    ], [
                        $client->name ?? '',
                        config('app.name'),
                        $item->subject ?? $item->id ?? '',
                        $item->invoice_number ?? '',
                        $item->total_amount ?? $item->value ?? '',
                        date_format(date_create($item->due_date ?? ''),'d M, Y'),
                        date_format(date_create($item->end_date ?? ''),'d M, Y'),
                    ], $body);

                    // Fetch SMTP settings per user
                    $user = User::leftJoin('roles', 'users.role', '=', 'roles.id')
                        ->select('users.*')
                        ->where('users.cid', $client->cid ?? null)
                        ->where('roles.title', 'Admin')
                        ->first();
//dd($user);
                    $smtpSettings = SmtpSettings::where('user_id', $user->id ?? null)->first();
                    if (!$smtpSettings && $user && $user->cid) {
                        $smtpSettings = SmtpSettings::where('cid', $user->cid)->first();
                    }

                    $fromAddress = $smtpSettings?->from_address ?? config('mail.from.address');
                    $fromName = $smtpSettings?->from_name ?? config('mail.from.name');

                    // Prepare view data
                    $viewName = 'emails.template'; // Blade template file
                    $viewData = [
                        'name' => $client->name ?? 'Sir/Mam',
                        'messages' => nl2br($body),
                        'company' => session('companies')->name ?? config('app.name'),
                        'signature' => nl2br($user->esign ?? ''),
                    ];

                    $subject = $template_subject;

                    // Debug log before sending
                    \Log::info("Preparing email for client: {$client->email}, Template: {$template->subject}, Module: {$template->module}, Days before: {$daysBefore}");
                    \Log::debug("Email body for {$client->email}: " . $body);

                    // Send the email
                    try {
                        $mailable = new CustomMailable(
                            $subject,
                            $viewName,
                            $viewData,
                            $fromAddress,
                            $fromName
                        );

                        Mail::to($client->email)->send($mailable);

                        \Log::info("Email sent to {$client->email} successfully.");

                        // Save notification history
                        NotificationHistory::create([
                            'module' => $template->module,
                            'item_id' => $item->id,
                            'template_id' => $template->id,
                            'days_before' => $daysBefore,
                            'recipient_email' => $client->email,
                            'sent_at' => now(),
                        ]);

                    } catch (\Exception $e) {
                        \Log::error("Failed to send email to {$client->email}: " . $e->getMessage());
                    }
                }
            }
        }

        $this->info("Scheduled email reminders finished.");
    }
}
