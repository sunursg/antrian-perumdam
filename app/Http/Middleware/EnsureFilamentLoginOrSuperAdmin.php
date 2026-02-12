<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureFilamentLoginOrSuperAdmin
{
    /**
     * Force Filament /admin to show login, and only allow SUPER_ADMIN.
     */
    public function handle(Request $request, Closure $next, string $panel = 'admin')
    {
        $routeName = $request->route()?->getName() ?? '';

        // Allow Filament's own auth routes (login/logout/password reset) to pass through.
        if (str_starts_with($routeName, "filament.$panel.auth.")) {
            return $next($request);
        }

        // If not logged in, send to login screen.
        if (! Auth::check()) {
            return redirect()->route("filament.$panel.auth.login");
        }

        /** @var \App\Models\User $user */
        $user = Auth::user();
        if (! ($user->hasRole('SUPER_ADMIN') || $user->email === 'superadmin@demo.test')) {
            // Unauthorized users are redirected to counter login (since they might be operators)
            return redirect()->route('counter.login');
        }

        return $next($request);
    }
}
