<?php

namespace App\Http\Controllers\Counter;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CounterAuthController extends Controller
{
    public function showLogin(Request $request): View|RedirectResponse
    {
        if (Auth::guard('operator')->check()) {
            return redirect()->route('counter.index');
        }

        return view('pages.counter-login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!Auth::guard('operator')->attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['email' => 'Email atau password tidak valid.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::guard('operator')->user();
        if (!$user || !$user->hasAnyRole(['ADMIN', 'SUPER_ADMIN'])) {
            Auth::guard('operator')->logout();
            // Don't invalidate the whole session here if we want to keep 'web' logged in
            // But we might need to regenerate tokens for security
            
            return back()
                ->withErrors(['email' => 'Akun tidak memiliki akses operator loket.'])
                ->onlyInput('email');
        }

        return redirect()->route('counter.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('operator')->logout();
        // $request->session()->invalidate(); // Only invalidate if we want to kill ALL guards
        // $request->session()->regenerateToken();

        return redirect()->route('counter.login');
    }

    public function index(): View
    {
        return view('pages.counter');
    }
}

