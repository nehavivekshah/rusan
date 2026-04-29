<?php

namespace App\Traits;

use App\Models\Activity;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

/**
 * ActivityLogger Trait
 *
 * Use in any Controller to log user activity with a single call:
 *
 *   $this->logActivity('Lead Created', 'leads', $lead->id, $lead->name, "Added lead {$lead->name}");
 */
trait ActivityLogger
{
    /**
     * Log a CRM activity to the `activities` table.
     *
     * @param  string      $type         Human-readable action label  e.g. "Lead Created"
     * @param  string      $module       Module key                   e.g. "leads"
     * @param  int|null    $subjectId    ID of the affected record
     * @param  string|null $subjectLabel Display name of the record
     * @param  string|null $description  Full sentence for the live feed
     * @param  string|null $value        Optional numeric / monetary value
     */
    protected function logActivity(
        string  $type,
        string  $module       = '',
        ?int    $subjectId    = null,
        ?string $subjectLabel = null,
        ?string $description  = null,
        ?string $value        = null
    ): void {
        try {
            $user = Auth::user();
            if (!$user) return;          // never log unauthenticated actions

            Activity::create([
                'user_id'       => $user->id,
                'cid'           => $user->cid ?? null,
                'type'          => $type,
                'module'        => $module ?: null,
                'subject_id'    => $subjectId,
                'subject_label' => $subjectLabel,
                'description'   => $description ?? $type,
                'value'         => $value,
                'ip_address'    => Request::ip(),
                'user_agent'    => substr(Request::userAgent() ?? '', 0, 255),
            ]);
        } catch (\Throwable $e) {
            // Never let activity logging break the main request
            \Illuminate\Support\Facades\Log::error('ActivityLogger error: ' . $e->getMessage());
        }
    }

    /**
     * Shortcut — log a login event (called from AuthController).
     */
    protected function logLogin(): void
    {
        $this->logActivity('Login', 'auth', null, null, 'User logged in');
    }

    /**
     * Shortcut — log a logout event.
     */
    protected function logLogout(): void
    {
        $this->logActivity('Logout', 'auth', null, null, 'User logged out');
    }
}
