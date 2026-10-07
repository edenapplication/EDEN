<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GeometreAuthController extends Controller
{
    /**
     * Affiche le formulaire de connexion géomètre
     */
    public function showLogin()
    {
        // Si déjà connecté en tant que géomètre → rediriger vers le CHOIX DE LA DATE
        if (Auth::check() && Auth::user()->role === 'geometre') {
            return redirect()->route('affectations.programmation-choix');
        }

        return view('auth.geometre-login');
    }

    /**
     * Traite la connexion géomètre
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:4',
        ], [
            'email.required'    => 'L\'adresse email est obligatoire.',
            'email.email'       => 'L\'adresse email n\'est pas valide.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min'      => 'Le mot de passe doit contenir au moins 4 caractères.',
        ]);

        $credentials = $request->only('email', 'password');
        $remember    = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {

            // ✅ Vérifier que c'est bien un géomètre
            if (Auth::user()->role !== 'geometre') {
                Auth::logout();
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => '❌ Ce compte n\'est pas un compte géomètre.',
                    ]);
            }

            // ✅ Vérifier que le compte est actif
            if (isset(Auth::user()->actif) && !Auth::user()->actif) {
                Auth::logout();
                return back()
                    ->withInput($request->only('email'))
                    ->withErrors([
                        'email' => '❌ Votre compte a été désactivé. Contactez l\'administrateur.',
                    ]);
            }

            $request->session()->regenerate();

            // ✅ Redirection vers le CHOIX DE LA DATE
            return redirect()->intended(route('affectations.programmation-choix'));
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => '❌ Email ou mot de passe incorrect.',
            ]);
    }

    /**
     * Déconnexion géomètre
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('geometre.login')
            ->with('success', '✅ Vous avez été déconnecté.');
    }
}