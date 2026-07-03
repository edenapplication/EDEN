<?php
namespace App\Http\Controllers\Feb;

use App\Http\Controllers\Controller;
use App\Models\Feb\Fiche;
use App\Models\Feb\Utilisateur;
use App\Models\Feb\Agence;

class AdminController extends Controller
{
    public function index()
{
    // ✅ Uniquement les fiches SOUMISES (pas brouillon)
    $stats = [
        'fiches_total' => \App\Models\Feb\Fiche::where('statut', 'soumise')->count(),
        'fiches_new'   => \App\Models\Feb\Fiche::where('statut', 'soumise')
                              ->where('vue_admin', false)->count(),
        'utilisateurs' => \App\Models\Feb\Utilisateur::where('actif', true)->count(),
        'agences'      => \App\Models\Feb\Agence::count(),
    ];

    $nouvelles = \App\Models\Feb\Fiche::with('utilisateur.agence')
        ->where('statut',    'soumise')
        ->where('vue_admin', false)
        ->orderByDesc('soumise_at')
        ->limit(10)
        ->get();

    $recentes = \App\Models\Feb\Fiche::with('utilisateur.agence')
        ->where('statut', 'soumise')
        ->orderByDesc('soumise_at')
        ->limit(20)
        ->get();

    return view('feb.admin.index', compact('stats', 'nouvelles', 'recentes'));
}
}