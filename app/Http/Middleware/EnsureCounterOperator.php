<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureCounterOperator
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::guard('operator')->check()) {
            return redirect()->route('counter.login');
        }

        $user = Auth::guard('operator')->user();
        if (!$user || !$user->hasAnyRole(['ADMIN', 'SUPER_ADMIN'])) {
            Auth::guard('operator')->logout();

            return redirect()
                ->route('counter.login')
                ->withErrors(['email' => 'Akun ini tidak memiliki akses operator loket.']);
        }

        return $next($request);
    }
}

