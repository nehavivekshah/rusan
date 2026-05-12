<?php

namespace App\Services;

use App\Models\EmailInbox;
use App\Models\ReceivedEmail;
use App\Models\Leads;
use Illuminate\Support\Facades\Log;

class EmailSyncService extends BaseService
{
    /**
     * Sync all active inboxes for a company.
     */
    public function syncAll($cid)
    {
        $inboxes = EmailInbox::where('cid', $cid)->where('status', 'active')->get();
        foreach ($inboxes as $inbox) {
            $this->syncInbox($inbox);
        }
    }

    /**
     * Sync a specific inbox.
     */
    public function syncInbox(EmailInbox $inbox)
    {
        if (!function_exists('imap_open')) {
            Log::warning("IMAP extension is not enabled. Skipping sync for {$inbox->email}");
            return ['success' => false, 'message' => 'IMAP extension missing on server.'];
        }

        $server = "{" . $inbox->imap_host . ":" . $inbox->imap_port . "/imap/" . $inbox->imap_encryption . "}INBOX";
        
        try {
            $mbox = @imap_open($server, $inbox->username, $inbox->password);
            if (!$mbox) {
                Log::error("IMAP Connection Failed for {$inbox->email}: " . imap_last_error());
                return ['success' => false, 'message' => imap_last_error()];
            }

            $since = $inbox->last_sync_at ? date('d-M-Y', strtotime($inbox->last_sync_at)) : date('d-M-Y', strtotime('-1 day'));
            $emails = imap_search($mbox, 'SINCE ' . $since);

            if ($emails) {
                rsort($emails);
                foreach ($emails as $num) {
                    $header = imap_headerinfo($mbox, $num);
                    $messageId = $header->message_id ?? null;

                    if ($messageId && !ReceivedEmail::where('message_id', $messageId)->exists()) {
                        $this->storeEmail($mbox, $num, $header, $inbox);
                    }
                }
            }

            imap_close($mbox);
            $inbox->last_sync_at = now();
            $inbox->save();

            return ['success' => true];
        } catch (\Exception $e) {
            Log::error("EmailSync Error: " . $e->getMessage());
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    protected function storeEmail($mbox, $num, $header, $inbox)
    {
        $from = $header->from[0];
        $fromEmail = $from->mailbox . '@' . $from->host;
        $fromName = $from->personal ?? null;

        $bodyText = imap_fetchbody($mbox, $num, 1);
        $bodyHtml = imap_fetchbody($mbox, $num, 2) ?: $bodyText;

        // Try to link to a Lead
        $lead = Leads::where('email', $fromEmail)->where('cid', $inbox->cid)->first();

        ReceivedEmail::create([
            'cid'        => $inbox->cid,
            'inbox_id'   => $inbox->id,
            'message_id' => $header->message_id,
            'from_email' => $fromEmail,
            'from_name'  => $fromName,
            'subject'    => $header->subject,
            'body_html'  => $bodyHtml,
            'body_text'  => $bodyText,
            'received_at'=> date('Y-m-d H:i:s', $header->udate),
            'lead_id'    => $lead?->id
        ]);
    }
}
