<?php
namespace App\Http\Controllers\Feb;

use App\Http\Controllers\Controller;
use App\Models\Feb\Fiche;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use NumberToWords\NumberToWords;

class AdminFicheController extends Controller
{
    public function index(Request $request)
    {
        $query = Fiche::with('utilisateur.agence')->where('statut','soumise');

        if ($request->filled('utilisateur_id')) $query->where('utilisateur_id', $request->utilisateur_id);
        if ($request->filled('agence_id')) {
            $query->whereHas('utilisateur', fn($q) => $q->where('agence_id', $request->agence_id));
        }
        if ($request->filled('du')) $query->whereDate('soumise_at', '>=', $request->du);
        if ($request->filled('au')) $query->whereDate('soumise_at', '<=', $request->au);
        if ($request->filled('vue')) $query->where('vue_admin', $request->vue === '1');

        $fiches = $query->orderByDesc('soumise_at')->paginate(20);

        $utilisateurs = \App\Models\Feb\Utilisateur::orderBy('nom')->get();
        $agences      = \App\Models\Feb\Agence::orderBy('nom')->get();

        return view('feb.admin.fiches.index', compact('fiches','utilisateurs','agences'));
    }

    public function show(Fiche $fiche)
    {
        $fiche->load('sections.colonnes','sections.lignes','utilisateur.agence');
        // Marquer comme vue
        if (!$fiche->vue_admin) $fiche->update(['vue_admin' => true]);
        return view('feb.admin.fiches.show', compact('fiche'));
    }

    public function marquerVue(Fiche $fiche)
    {
        $fiche->update(['vue_admin' => true]);
        return back()->with('success', 'Marquée comme vue.');
    }

   public function pdf(Fiche $fiche)
{
    // Charger toutes les relations nécessaires
    $fiche->load([
        'sections.colonnes',
        'sections.lignes',
        'utilisateur.agence',
        'destinataires'  // Les destinataires seront disponibles dans la vue
    ]);

    // Calcul du montant total
    $totalGlobal = 0;

    foreach ($fiche->sections as $section) {
        $colPT = $section->colonnes
            ->first(fn($c) => preg_match('/prix.?total|montant.?total/i', $c->libelle));

        if ($colPT) {
            foreach ($section->lignes as $ligne) {
                // ✅ Nettoyage correct des valeurs
                $valeur = $ligne->valeurs[$colPT->id] ?? '0';
                $valeurNettoyee = preg_replace('/[^0-9,.]/', '', $valeur);
                $valeurNettoyee = str_replace(',', '.', $valeurNettoyee);
                $totalGlobal += floatval($valeurNettoyee);
            }
        }
    }

    // Conversion en lettres avec majuscules
    $numberToWords = new NumberToWords();
    $converter = $numberToWords->getNumberTransformer('fr');
    $totalLettre = strtoupper(ucfirst($converter->toWords($totalGlobal))) . " FRANC CFA";

    // Génération du PDF
    $pdf = Pdf::loadView('feb.fiches.pdf', [
        'fiche' => $fiche,
        'totalGlobal' => $totalGlobal,
        'totalLettre' => $totalLettre,
        'hasDestinataires' => $fiche->destinataires->isNotEmpty()
    ])
    ->setPaper('a4', 'landscape');

    // ✅ Nouveau nom de fichier : Titre-nom-date.pdf
    $titre = $fiche->titre ?? 'fiche';
    // Supprimer les accents et caractères spéciaux
    $titre = iconv('UTF-8', 'ASCII//TRANSLIT', $titre);
    $titre = preg_replace('/[^a-zA-Z0-9\- ]/', '', $titre);
    $titre = Str::slug($titre, '-');
    
    $nomPersonne = $fiche->utilisateur->nom_complet ?? 'utilisateur';
    $nomPersonne = iconv('UTF-8', 'ASCII//TRANSLIT', $nomPersonne);
    $nomPersonne = preg_replace('/[^a-zA-Z0-9\- ]/', '', $nomPersonne);
    $nomPersonne = Str::slug($nomPersonne, '-');
    
    $date = $fiche->soumise_at ? $fiche->soumise_at->format('Y-m-d') : now()->format('Y-m-d');
    
    $nomFichier = $titre . '-' . $nomPersonne . '-' . $date . '.pdf';

    return $pdf->download($nomFichier);
}

}