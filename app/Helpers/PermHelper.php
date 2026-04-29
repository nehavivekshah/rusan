<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;

/**
 * PermHelper — centralised permission check helper.
 *
 * Usage:
 *   PermHelper::can('leads', 'add')     ← true/false
 *   PermHelper::canAny('leads')          ← true if any permission on module
 *
 * In Blade via registered directive in AppServiceProvider:
 *   @can_perm('leads', 'add') … @end_can_perm
 */
class PermHelper
{
    /** Cached permissions for current request */
    private static ?array $perms = null;
    private static ?array $feats = null;

    private static function load(): void
    {
        if (self::$perms !== null) return;

        $roles = session('roles');
        self::$feats = array_filter(explode(',', ($roles->features    ?? '')));
        self::$perms = array_filter(explode(',', ($roles->permissions ?? '')));
    }

    /**
     * Does the current user have the given module + action permission?
     */
    public static function can(string $module, string $action = 'assign'): bool
    {
        if (!Auth::check()) return false;

        $user = Auth::user();

        // Master always allowed
        if ($user->role === 'master') return true;

        self::load();

        // All-access role
        if (in_array('All', self::$feats) || in_array('All', self::$perms)) {
            return true;
        }

        return in_array("{$module}_{$action}", self::$perms);
    }

    /**
     * Does the user have ANY permission on the given module?
     */
    public static function canAny(string $module): bool
    {
        return self::can($module, 'assign')
            || self::can($module, 'add')
            || self::can($module, 'edit')
            || self::can($module, 'delete')
            || self::can($module, 'export')
            || self::can($module, 'import');
    }

    /**
     * Abort with 403 if user cannot perform the action.
     * Call from controllers.
     */
    public static function authorize(string $module, string $action = 'assign'): void
    {
        if (!self::can($module, $action)) {
            abort(403, "You don't have permission to {$action} {$module}.");
        }
    }
}
