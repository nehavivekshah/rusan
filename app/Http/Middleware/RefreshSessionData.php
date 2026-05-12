<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Companies;
use App\Models\Roles;

class RefreshSessionData
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Refresh company and role in session to ensure they are always up-to-date
            // This prevents issues where an admin changes a user's role or plan but it doesn't reflect until logout.
            $company = Companies::find($user->cid);
            $role = Roles::find($user->role);

            session([
                'companies' => $company,
                'roles' => $role,
            ]);
        }

        return $next($request);
    }
}
