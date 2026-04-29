<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * CheckPermission Middleware
 *
 * Usage on route:  ->middleware('permission:leads,add')
 *
 * $module  = the feature key (leads, clients, contracts, etc.)
 * $action  = assign | add | edit | delete | export | import
 *
 * Bypass logic:
 *   - Master users (role == 'master') always pass.
 *   - Roles with features/permissions = 'All' always pass.
 *   - Otherwise check that  "{$module}_{$action}" is in the role's permissions string.
 */
class CheckPermission
{
    public function handle(Request $request, Closure $next, string $module, string $action = 'assign')
    {
        // Not logged in — let auth middleware handle it
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // Master user bypasses all checks
        if ($user->role == 'master') {
            return $next($request);
        }

        $roles = session('roles');

        if (!$roles) {
            return $this->deny($request, $module, $action);
        }

        $features    = array_filter(explode(',', ($roles->features    ?? '')));
        $permissions = array_filter(explode(',', ($roles->permissions ?? '')));

        // Role with 'All' access passes everything
        if (in_array('All', $features) || in_array('All', $permissions)) {
            return $next($request);
        }

        // Check exact permission key e.g. "leads_add"
        $key = "{$module}_{$action}";

        if (in_array($key, $permissions)) {
            return $next($request);
        }

        return $this->deny($request, $module, $action);
    }

    private function deny(Request $request, string $module, string $action)
    {
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'error'   => true,
                'message' => "You do not have permission to perform '{$action}' on '{$module}'.",
            ], 403);
        }

        return redirect()->back()
            ->with('error', "⛔ Access Denied — you don't have permission to {$action} {$module}.");
    }
}
