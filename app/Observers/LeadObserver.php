<?php

namespace App\Observers;

use App\Models\Leads;

class LeadObserver
{
    /**
     * Handle the Leads "created" event.
     */
    public function created(Leads $leads): void
    {
        $automationService = new \App\Services\AutomationService();
        $automationService->process('lead_created', $leads);
    }

    /**
     * Handle the Leads "updated" event.
     */
    public function updated(Leads $leads): void
    {
        if ($leads->wasChanged('status')) {
            $automationService = new \App\Services\AutomationService();
            $automationService->process('lead_status_changed', $leads);
        }
    }

    /**
     * Handle the Leads "deleted" event.
     */
    public function deleted(Leads $leads): void
    {
        //
    }

    /**
     * Handle the Leads "restored" event.
     */
    public function restored(Leads $leads): void
    {
        //
    }

    /**
     * Handle the Leads "force deleted" event.
     */
    public function forceDeleted(Leads $leads): void
    {
        //
    }
}
