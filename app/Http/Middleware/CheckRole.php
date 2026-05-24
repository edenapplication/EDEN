<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if (!$user->actif) {
            Auth::logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Votre compte est désactivé.']);
        }

        if (!in_array($user->role, $roles)) {
            // ✅ Réponse JSON pour les requêtes AJAX
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Accès non autorisé.',
                ], 403);
            }

            // ✅ Page d'erreur propre au lieu de page blanche
            return response()->view('errors.403', [
                'role'    => $user->role,
                'roleLabel' => ['admin' => 'Administrateur', 'rh' => 'RH', 'commercial' => 'Commercial'][$user->role] ?? $user->role,
            ], 403);
        }

        return $next($request);
    }
}