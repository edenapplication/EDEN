<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) return $this->redirectAfterLogin();
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (!Auth::attempt($credentials, $remember)) {
            return back()->withErrors(['email' => 'Identifiants incorrects.'])->withInput();
        }

        $user = Auth::user();
        if (!$user->actif) {
            Auth::logout();
            return back()->withErrors(['email' => 'Ce compte est désactivé.'])->withInput();
        }

        $request->session()->regenerate();
        return $this->redirectAfterLogin();
    }

    private function redirectAfterLogin()
    {
        return match(Auth::user()->role) {
            'rh'         => redirect()->route('rh.dashboard'),
            'commercial' => redirect()->route('suivi-client.index'),
            default      => redirect()->route('home'),
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}