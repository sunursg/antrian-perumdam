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

        // Hanya izinkan ADMIN / SUPER_ADMIN masuk panel.
        if (! Auth::user()->hasAnyRole(['ADMIN', 'SUPER_ADMIN'])) {
            return redirect()
                ->route("filament.$panel.auth.login")
                ->withErrors(['email' => 'Akses panel hanya untuk ADMIN atau SUPER_ADMIN.']);
        }

        // Jika ADMIN membuka dashboard default, arahkan ke halaman operator.
        if (
            Auth::user()->hasRole('ADMIN')
            && ! Auth::user()->hasRole('SUPER_ADMIN')
            && $routeName === "filament.$panel.pages.dashboard"
        ) {
            return redirect()->route("filament.$panel.pages.operator-console");
        }

        return $next($request);
    }
}
