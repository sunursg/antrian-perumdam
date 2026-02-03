<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureFilamentLoginOrSuperAdmin
{
    /**
     * Force Filament /admin to show login, and only allow SUPER_ADMIN in.
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

        // Redirect ADMIN ke halaman operator (bukan panel Filament)
        if (Auth::user()->hasRole('ADMIN') && !Auth::user()->hasRole('SUPER_ADMIN')) {
            return redirect('/operator');
        }

        // Hanya SUPER_ADMIN boleh lanjut ke panel
        if (! Auth::user()->hasRole('SUPER_ADMIN')) {
            return redirect()
                ->route("filament.$panel.auth.login")
                ->withErrors(['email' => 'Hanya SUPER_ADMIN yang boleh masuk panel admin.']);
        }

        return $next($request);
    }
}
