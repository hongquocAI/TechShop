<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm() { return view('auth.login'); }
    public function registerForm() { return view('auth.register'); }

    public function login(Request $request)
    {
        $cred = $request->validate(['email' => 'required|email', 'password' => 'required']);
        if (! Auth::attempt($cred, $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('Email hoặc mật khẩu không đúng.')])->onlyInput('email');
        }
        $request->session()->regenerate(); // chống session fixation

        return redirect()->intended(Auth::user()->isAdmin() ? route('admin.dashboard') : '/');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|max:100', 'email' => 'required|email|unique:users',
            'phone' => 'nullable|max:15', 'password' => 'required|min:6|confirmed',
        ]);
        Auth::login(User::create($data));
        $request->session()->regenerate();

        return redirect('/');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
