<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComptableMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->route('login')
                ->with('error', '🔒 Vous devez être connecté.');
        }

        $user = Auth::user();

        // Rôles autorisés à accéder à l'interface comptable
        $rolesAutorises = ['admin', 'comptable', 'caissier', 'super_admin'];

        if (!in_array($user->role ?? '', $rolesAutorises) && !($user->is_admin ?? false)) {
            abort(403, '⛔ Accès réservé au comptable.');
        }

        return $next($request);
    }
}