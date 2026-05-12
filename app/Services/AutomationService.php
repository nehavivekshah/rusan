<?php

namespace App\Services;

use App\Models\Automation;
use App\Models\Leads;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AutomationService extends BaseService
{
    /**
     * Process automations for a given trigger event.
     */
    public function process($event, $target)
    {
        $automations = Automation::where('trigger_event', $event)
            ->where('status', 'Active')
            ->where('cid', $target->cid)
            ->get();

        foreach ($automations as $automation) {
            if ($this->checkConditions($automation, $target)) {
                $this->executeAction($automation, $target);
            }
        }
    }

    protected function checkConditions($automation, $target)
    {
        if (!$automation->conditions) return true;

        $conditions = json_decode($automation->conditions, true);
        if (!$conditions) return true;

        foreach ($conditions as $field => $value) {
            if ($target->{$field} != $value) {
                return false;
            }
        }

        return true;
    }

    protected function executeAction($automation, $target)
    {
        $payload = json_decode($automation->action_payload, true) ?: [];

        switch ($automation->action) {
            case 'assign_lead':
                $this->assignLead($target, $payload);
                break;
            case 'send_welcome_email':
                $this->sendWelcomeEmail($target, $payload);
                break;
            case 'send_whatsapp':
                $this->sendWhatsApp($target, $payload);
                break;
        }
    }

    protected function assignLead($lead, $payload)
    {
        $userId = $payload['user_id'] ?? null;
        if ($userId) {
            $lead->assigned = $userId;
            $lead->save();
            Log::info("Automation: Assigned lead {$lead->id} to user {$userId}");
        }
    }

    protected function sendWelcomeEmail($lead, $payload)
    {
        if (!$lead->email) return;

        $template = $payload['template'] ?? 'emails.welcome';
        $this->sendMail(
            $lead->email,
            $payload['subject'] ?? 'Welcome!',
            $template,
            ['name' => $lead->name],
            null,
            $lead->cid
        );
    }

    protected function sendWhatsApp($lead, $payload)
    {
        if (!$lead->mob) return;

        $whatsapp = new WhatsAppService();
        $whatsapp->sendMessage($lead->mob, $payload['message'] ?? 'Hello from Rusan CRM');
    }
}
