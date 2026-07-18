<?php
namespace App\Http\Controllers\Feb;

use App\Http\Controllers\Controller;
use App\Models\Feb\Fiche;
use App\Models\Feb\Section;
use App\Models\Feb\Colonne;
use App\Models\Feb\Ligne;
use App\Models\Feb\Utilisateur;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Str;
use NumberToWords\NumberToWords;

class FicheController extends Controller
{
    protected function utilisateur(): Utilisateur
    {
        return Utilisateur::with('agence')->findOrFail(session('feb_user_id'));
    }

    // Liste des fiches
    public function index(Request $request)
    {
        $user  = $this->utilisateur();
        $query = Fiche::where('utilisateur_id', $user->id)->with('sections');

        if ($request->filled('du'))     $query->whereDate('created_at', '>=', $request->du);
        if ($request->filled('au'))     $query->whereDate('created_at', '<=', $request->au);
        if ($request->filled('statut')) $query->where('statut', $request->statut);

        $fiches = $query->orderByDesc('created_at')->get();
        return view('feb.index', compact('user', 'fiches'));
    }

    // ✅ Créer nouvelle fiche (page vide)
    public function creer()
    {
        $user     = $this->utilisateur();
        $colonnes = Colonne::where('actif', true)->orderBy('ordre')->get();
        $modele   = null;
        return view('feb.fiches.creer', compact('user', 'colonnes', 'modele'));
    }

    // ✅ Continuer un brouillon
    public function continuer(Fiche $fiche)
    {
        $this->autoriser($fiche);
        abort_if($fiche->statut !== 'brouillon', 403, 'Cette fiche est déjà soumise.');
        $fiche->load('sections.colonnes', 'sections.lignes');
        $user     = $this->utilisateur();
        $colonnes = Colonne::where('actif', true)->orderBy('ordre')->get();
        $modele   = null;
        // On passe la fiche comme brouillon à continuer
        return view('feb.fiches.creer', compact('user', 'colonnes', 'modele', 'fiche'));
    }

    // ✅ Utiliser comme modèle
    public function utiliserModele(Fiche $fiche)
    {
        $this->autoriser($fiche);
        $fiche->load('sections.colonnes', 'sections.lignes');
        $user     = $this->utilisateur();
        $colonnes = Colonne::where('actif', true)->orderBy('ordre')->get();
        $modele   = $fiche;
        $fiche    = null; // pas de brouillon à continuer
        return view('feb.fiches.creer', compact('user', 'colonnes', 'modele', 'fiche'));
    }

    // ✅ Créer ET soumettre (ou sauvegarder brouillon)
public function creerEtSoumettre(Request $request)
{
    try {
        $request->validate([
            'titre'              => 'required|string|max:200',
            'sections'           => 'required|array|min:1',
            'sections.*.titre'   => 'required|string|max:150',
            'sections.*.colonnes'=> 'nullable|array',
            'sections.*.lignes'  => 'nullable|array',
            'destinataires'      => 'nullable|array',
            'destinataires.*'    => 'exists:feb_destinataires,id', // ✅ Correction : feb_destinataires
        ]);

        $user   = $this->utilisateur();
        $action = $request->action ?? 'soumettre';

        // Si brouillon existant à mettre à jour
        $ficheId = $request->fiche_id;
        if ($ficheId) {
            $fiche = Fiche::where('id', $ficheId)
                          ->where('utilisateur_id', $user->id)
                          ->where('statut', 'brouillon')
                          ->firstOrFail();
            $fiche->update([
                'titre'       => $request->titre,
                'description' => $request->description,
                'statut'      => $action === 'soumettre' ? 'soumise' : 'brouillon',
                'vue_admin'   => false,
                'soumise_at'  => $action === 'soumettre' ? now() : null,
            ]);
            
            // Supprimer anciennes sections
            $fiche->sections()->each(function($s) {
                $s->lignes()->delete();
                $s->colonnes()->detach();
                $s->delete();
            });
            
            // ✅ Supprimer les anciens destinataires
            $fiche->destinataires()->detach();
            
        } else {
            // ✅ Générer le numéro de fiche
            $numeroFiche = 'FEB-' . date('Y') . '-' . str_pad(Fiche::count() + 1, 4, '0', STR_PAD_LEFT);
            
            $fiche = Fiche::create([
                'utilisateur_id' => $user->id,
                'numero_fiche'   => $numeroFiche,
                'titre'          => $request->titre,
                'description'    => $request->description,
                'statut'         => $action === 'soumettre' ? 'soumise' : 'brouillon',
                'vue_admin'      => false,
                'soumise_at'     => $action === 'soumettre' ? now() : null,
                'modele_id'      => $request->modele_id ?? null,
            ]);
        }

        // ============================================================
        // ✅ GESTION DES DESTINATAIRES
        // ============================================================
        if ($request->has('destinataires') && !empty($request->destinataires)) {
            // Créer un tableau associatif avec l'ordre
            $destinataires = collect($request->destinataires)->mapWithKeys(function ($id, $index) {
                return [$id => ['ordre' => $index + 1]];
            })->toArray();
            
            $fiche->destinataires()->attach($destinataires);
        }

        // ============================================================
        // GESTION DES SECTIONS
        // ============================================================
        foreach ($request->sections as $i => $sData) {
            $section = Section::create([
                'fiche_id' => $fiche->id,
                'titre'    => $sData['titre'],
                'ordre'    => $i,
            ]);

            if (!empty($sData['colonnes'])) {
                foreach ($sData['colonnes'] as $j => $colonneId) {
                    $section->colonnes()->attach((int)$colonneId, ['ordre' => $j]);
                }
            }

            if (!empty($sData['lignes'])) {
                foreach ($sData['lignes'] as $num => $valeurs) {
                    $hasData = !empty(array_filter($valeurs, fn($v) => $v !== '' && $v !== null));
                    if (!$hasData) continue;
                    Ligne::create([
                        'section_id'   => $section->id,
                        'numero_ligne' => $num + 1,
                        'valeurs'      => $valeurs,
                    ]);
                }
            }
        }

        return response()->json([
            'success'    => true,
            'action'     => $action,
            'fiche_id'   => $fiche->id,
            'pdf_url'    => $action === 'soumettre' ? route('feb.fiches.pdf', $fiche->id) : null,
            'retour_url' => route('feb.fiches.index'),
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors' => $e->errors()
        ], 422);
    } catch (\Throwable $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'trace' => $e->getTraceAsString() // Pour le débogage
        ], 500);
    }
}

    // PDF
  public function pdf(Fiche $fiche)
{
    $this->autoriser($fiche);

    $fiche->load(
        'sections.colonnes',
        'sections.lignes',
        'utilisateur.agence',
        'destinataires'
    );

    $totalGlobal = 0;

    foreach($fiche->sections as $section){
        $colPT = $section->colonnes
            ->first(fn($c) => preg_match('/prix.?total|montant.?total/i', $c->libelle));

        if($colPT){
            foreach($section->lignes as $ligne){
                $valeur = $ligne->valeurs[$colPT->id] ?? '0';
                $valeurNettoyee = preg_replace('/[^0-9,.]/', '', $valeur);
                $valeurNettoyee = str_replace(',', '.', $valeurNettoyee);
                $totalGlobal += floatval($valeurNettoyee);
            }
        }
    }

    $numberToWords = new NumberToWords();
    $converter = $numberToWords->getNumberTransformer('fr');
    $totalLettre = strtoupper(ucfirst($converter->toWords($totalGlobal))) . " FRANC CFA";

    $pdf = Pdf::loadView(
        'feb.fiches.pdf',
        compact('fiche', 'totalGlobal', 'totalLettre')
    )
    ->setPaper('a4', 'landscape');

    // ✅ Nouveau nom de fichier : Titre-nom-date.pdf
    $titre = Str::slug($fiche->titre ?? 'fiche', '-');
    $nomPersonne = Str::slug($fiche->utilisateur->nom_complet ?? 'utilisateur', '-');
    $date = $fiche->soumise_at ? $fiche->soumise_at->format('Y-m-d') : now()->format('Y-m-d');
    
    $nomFichier = $titre . '-' . $nomPersonne . '-' . $date . '.pdf';

    return $pdf->download($nomFichier);
}

    private function autoriser(Fiche $fiche): void
    {
        abort_if($fiche->utilisateur_id !== (int)session('feb_user_id'), 403);
    }
}