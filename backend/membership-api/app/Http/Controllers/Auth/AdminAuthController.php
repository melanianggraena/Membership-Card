<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        return Auth::check() ? redirect()->route('dashboard') : view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($request->boolean('remember')) {
            config(['session.lifetime' => 60 * 24 * 30, 'session.expire_on_close' => false]);
        }

        if (! Auth::attempt($credentials)) {
            return back()->withInput($request->only('email'))->withErrors(['email' => 'Email atau password tidak sesuai.']);
        }

        $request->session()->regenerate();
        return redirect()->intended(route('dashboard'))->with('success', 'Selamat datang kembali.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
