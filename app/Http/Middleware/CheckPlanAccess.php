<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckPlanAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Only check if user is authenticated
        if (!Auth::check()) {
            return $next($request);
        }

        // Master admin bypasses these limits?
        // Wait, if master is managing subscriptions, they shouldn't be blocked from their own features.
        // But the user said "this features need to block on every whare".
        // Let's assume standard plan companies are blocked. Master admin might be viewing a standard company?
        // Master admin has its own logic, but let's just check the company plan.
        $company = session('companies');
        $plan = strtolower($company->plan ?? '');
        $premiumPlans = ['premium', 'pro'];
        
        // If they are on premium/pro, let them through
        if (in_array($plan, $premiumPlans)) {
            return $next($request);
        }

        $restrictedSegments = [
            'projects', 'manage-project', 'project', 'delete-project',
            'proposals', 'manage-proposal', 'quotation', 'delete-proposal',
            'invoices', 'manage-invoice', 'delete-invoice',
            'contracts', 'manage-contract', 'delete-contract',
            'recoveries', 'manage-recovery', 'recovery', 'delete-recovery-amount', 'delete-recovery-project',
            'opportunities',
            'campaigns', 
            'automations',
            'reports',
            'attendances', 'manage-attendance', 'delete-attendance',
            'users', 'manage-user', 'admins', 'employees', 'delete-user',
            'my-company',
            'my-profile',
            'smtp-settings',
            'email-templates',
            'reset-password',
            'role-settings', 'manage-role-setting'
        ];

        $segment1 = strtolower($request->segment(1));

        if (in_array($segment1, $restrictedSegments)) {
            // Master can access contracts regardless of plan
            if (Auth::check() && Auth::user()->role === 'master' && in_array($segment1, ['contracts', 'manage-contract', 'delete-contract'])) {
                return $next($request);
            }

            if ($request->ajax() || $request->expectsJson()) {
                return response()->json(['error' => 'Upgrade to Premium or Pro to unlock this feature.'], 403);
            }
            return redirect('/home')->with('error', 'Upgrade to Premium or Pro to unlock this feature.');
        }

        return $next($request);
    }
}
