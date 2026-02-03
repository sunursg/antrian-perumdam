<?php

namespace App\Http\Controllers\Operator;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OperatorPageController extends Controller
{
    public function page()
    {
        return view('pages.operator');
    }

    public function login(Request $request)
    {
        $creds = $request->validate([
            'email' => ['required','email'],
            'password' => ['required','string'],
        ]);

        if (Auth::attempt($creds, true)) {
            $request->session()->regenerate();

            /** @var User|null $user */
            $user = Auth::user();

            if (! $user || ! $user->hasAnyRole(['ADMIN', 'SUPER_ADMIN'])) {
                Auth::logout();
                return back()->withErrors(['email' => 'Akses ditolak. Akun ini bukan petugas atau super admin.']);
            }

            return redirect('/operator');
        }

        return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/operator');
    }
}
