<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\CnpsAffiliation;
use App\Models\RH\CnpsDeclaration;
use App\Models\RH\CnpsLigneDeclaration;
use App\Models\RH\Employe;
use App\Models\RH\BulletinPaie;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CnpsController extends Controller
{
    /**
     * Tableau de bord CNPS
     */
    public function index()
    {
        $periode = now()->format('Y-m');
        $mois = Carbon::createFromFormat('Y-m', $periode);

        // Statistiques
        $stats = [
            'total_employes' => Employe::where('actif', true)->count(),
            'affilies' => CnpsAffiliation::where('situation_affiliation', 'affilie')->count(),
            'non_affilies' => CnpsAffiliation::where('situation_affiliation', 'non_affilie')->count(),
            'sans_affiliation' => Employe::where('actif', true)
                ->whereDoesntHave('cnpsAffiliation')
                ->count(),
        ];

        // Déclaration du mois
        $declaration = CnpsDeclaration::where('periode', $periode)->first();

        // Dernières déclarations
        $dernieresDeclarations = CnpsDeclaration::with(['validePar'])
            ->orderByDesc('periode')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        // Évolution des cotisations (6 derniers mois)
        $evolution = collect();
        for ($i = 5; $i >= 0; $i--) {
            $p = now()->subMonths($i)->format('Y-m');
            $decl = CnpsDeclaration::where('periode', $p)->first();
            $evolution->push([
                'mois' => Carbon::createFromFormat('Y-m', $p)->translatedFormat('M Y'),
                'total' => $decl ? $decl->total_cnps : 0,
                'statut' => $decl ? $decl->statut_label : 'Aucune',
            ]);
        }

        return view('rh.cnps.index', compact('stats', 'declaration', 'dernieresDeclarations', 'evolution', 'periode'));
    }

    /**
     * Liste des déclarations CNPS
     */
    public function declarations(Request $request)
    {
        $query = CnpsDeclaration::with(['validePar']);

        if ($request->filled('periode')) {
            $query->where('periode', $request->periode);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $declarations = $query->orderByDesc('periode')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'total' => CnpsDeclaration::count(),
            'a_declarer' => CnpsDeclaration::where('statut', 'a_declarer')->count(),
            'declare' => CnpsDeclaration::where('statut', 'declare')->count(),
            'paye' => CnpsDeclaration::where('statut', 'paye')->count(),
        ];

        return view('rh.cnps.declarations', compact('declarations', 'stats'));
    }

    /**
     * Afficher le formulaire de création de déclaration
     */
    public function createDeclaration()
    {
        $periode = request('periode', now()->format('Y-m'));

        // Vérifier si une déclaration existe déjà pour cette période
        $existe = CnpsDeclaration::where('periode', $periode)->exists();

        // Récupérer les employés actifs avec leur affiliation CNPS
        $employes = Employe::with(['cnpsAffiliation'])
            ->where('actif', true)
            ->orderBy('nom')
            ->get();

        return view('rh.cnps.create_declaration', compact('periode', 'existe', 'employes'));
    }

    /**
     * Enregistrer une déclaration CNPS
     */
    public function storeDeclaration(Request $request)
    {
        $request->validate([
            'periode' => 'required|date_format:Y-m',
            'date_declaration' => 'nullable|date',
            'date_echeance' => 'nullable|date|after:date_declaration',
        ]);

        // Vérifier les doublons
        if (CnpsDeclaration::where('periode', $request->periode)->exists()) {
            return back()->with('error', 'Une déclaration existe déjà pour cette période.');
        }

        // Créer la déclaration
        $declaration = CnpsDeclaration::create([
            'periode' => $request->periode,
            'mois' => Carbon::createFromFormat('Y-m', $request->periode)->month,
            'annee' => Carbon::createFromFormat('Y-m', $request->periode)->year,
            'reference' => CnpsDeclaration::genererReference(),
            'date_declaration' => $request->date_declaration,
            'date_echeance' => $request->date_echeance,
            'statut' => 'a_declarer',
            'observations' => $request->observations,
        ]);

        return redirect()->route('rh.cnps.declarations.show', $declaration->id)
            ->with('success', 'Déclaration créée avec succès. Référence : ' . $declaration->reference);
    }

    /**
     * Afficher une déclaration
     */
    public function showDeclaration($id)
    {
        $declaration = CnpsDeclaration::with(['lignes.employe', 'validePar'])
            ->findOrFail($id);

        return view('rh.cnps.show_declaration', compact('declaration'));
    }

    /**
     * Ajouter des employés à une déclaration
     */
    public function ajouterEmployes(Request $request, $id)
    {
        $declaration = CnpsDeclaration::findOrFail($id);

        if (!$declaration->peutEtreModifiee()) {
            return back()->with('error', 'Cette déclaration ne peut plus être modifiée.');
        }

        $request->validate([
            'employes' => 'required|array',
            'employes.*' => 'exists:rh_employes,id',
        ]);

        $count = 0;
        foreach ($request->employes as $employeId) {
            $employe = Employe::with(['cnpsAffiliation'])->find($employeId);

            // Vérifier si déjà présent
            if (CnpsLigneDeclaration::where('declaration_id', $declaration->id)
                ->where('employe_id', $employeId)
                ->exists()) {
                continue;
            }

            // Récupérer le bulletin de paie de la période
            $bulletin = BulletinPaie::where('employe_id', $employeId)
                ->where('periode', $declaration->periode)
                ->first();

            // Calculer le salaire soumis
            $salaireSoumis = $bulletin ? $bulletin->salaire_brut : $employe->salaire_base;

            // Calculer les cotisations
            $cotisations = CnpsLigneDeclaration::calculerLigne($salaireSoumis);

            CnpsLigneDeclaration::create([
                'declaration_id' => $declaration->id,
                'employe_id' => $employeId,
                'bulletin_paie_id' => $bulletin?->id,
                'numero_cnps' => $employe->cnpsAffiliation?->numero_cnps ?? $employe->numero_cnps,
                'nom' => $employe->nom,
                'prenom' => $employe->prenom,
                'matricule' => $employe->matricule,
                'salaire_soumis' => $salaireSoumis,
                'cotisation_salariale' => $cotisations['cotisation_salariale'],
                'cotisation_patronale' => $cotisations['cotisation_patronale'],
                'total_cnps' => $cotisations['total_cnps'],
            ]);

            $count++;
        }

        // Mettre à jour les totaux
        $declaration->mettreAJourTotaux();

        return back()->with('success', $count . ' employé(s) ajouté(s) à la déclaration.');
    }

    /**
     * Supprimer une ligne de déclaration
     */
    public function supprimerLigne($declarationId, $ligneId)
    {
        $declaration = CnpsDeclaration::findOrFail($declarationId);

        if (!$declaration->peutEtreModifiee()) {
            return back()->with('error', 'Cette déclaration ne peut plus être modifiée.');
        }

        $ligne = CnpsLigneDeclaration::where('declaration_id', $declarationId)
            ->findOrFail($ligneId);

        $ligne->delete();

        // Mettre à jour les totaux
        $declaration->mettreAJourTotaux();

        return back()->with('success', 'Employé retiré de la déclaration.');
    }

    /**
     * Générer la DIPE (PDF)
     */
    public function generateDIPE($id)
    {
        $declaration = CnpsDeclaration::with(['lignes.employe'])
            ->findOrFail($id);

        $pdf = Pdf::loadView('rh.cnps.dipe', compact('declaration'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('DIPE_' . $declaration->reference . '.pdf');
    }

    /**
     * Changer le statut d'une déclaration
     */
    public function changerStatut(Request $request, $id)
    {
        $declaration = CnpsDeclaration::findOrFail($id);

        $request->validate([
            'statut' => 'required|in:a_declarer,declare,facture_recue,paye,justifie',
            'date_paiement' => 'required_if:statut,paye|nullable|date',
            'observations' => 'nullable|string',
        ]);

        $updateData = [
            'statut' => $request->statut,
            'observations' => $request->observations ?? $declaration->observations,
        ];

        if ($request->statut === 'paye' && $request->filled('date_paiement')) {
            $updateData['date_paiement'] = $request->date_paiement;
        }

        if ($request->statut === 'declare' && !$declaration->date_declaration) {
            $updateData['date_declaration'] = now();
        }

        if (in_array($request->statut, ['declare', 'facture_recue', 'paye', 'justifie'])) {
            $updateData['date_validation'] = now();
            $updateData['valide_par'] = auth()->id();
        }

        $declaration->update($updateData);

        return back()->with('success', 'Statut mis à jour : ' . $declaration->statut_label);
    }

    /**
     * Suivi des affiliations CNPS
     */
    public function affiliations(Request $request)
    {
        $query = CnpsAffiliation::with(['employe']);

        if ($request->filled('situation')) {
            $query->where('situation_affiliation', $request->situation);
        }

        if ($request->filled('search')) {
            $query->whereHas('employe', function ($q) use ($request) {
                $q->where('nom', 'LIKE', "%{$request->search}%")
                    ->orWhere('prenom', 'LIKE', "%{$request->search}%")
                    ->orWhere('matricule', 'LIKE', "%{$request->search}%");
            });
        }

        $affiliations = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        // Employés sans affiliation
        $sansAffiliation = Employe::where('actif', true)
            ->whereDoesntHave('cnpsAffiliation')
            ->orderBy('nom')
            ->get();

        return view('rh.cnps.affiliations', compact('affiliations', 'sansAffiliation'));
    }

    /**
     * Créer ou mettre à jour une affiliation CNPS
     */
    public function storeAffiliation(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:rh_employes,id',
            'numero_cnps' => 'nullable|string|max:50',
            'date_affiliation' => 'nullable|date',
            'centre_cnps' => 'nullable|string|max:100',
            'situation_affiliation' => 'required|in:affilie,non_affilie,en_cours,radie',
            'categorie_cnps' => 'nullable|string|max:50',
            'salaire_soumis' => 'nullable|numeric|min:0',
        ]);

        $affiliation = CnpsAffiliation::updateOrCreate(
            ['employe_id' => $request->employe_id],
            $request->only([
                'numero_cnps',
                'date_affiliation',
                'centre_cnps',
                'situation_affiliation',
                'categorie_cnps',
                'salaire_soumis',
                'notes',
            ])
        );

        // Mettre à jour l'employé avec les infos CNPS
        $employe = Employe::find($request->employe_id);
        if ($employe) {
            $employe->update([
                'numero_cnps' => $request->numero_cnps ?? $employe->numero_cnps,
                'date_affiliation_cnps' => $request->date_affiliation ?? $employe->date_affiliation_cnps,
                'centre_cnps' => $request->centre_cnps ?? $employe->centre_cnps,
                'situation_affiliation_cnps' => $request->situation_affiliation ?? $employe->situation_affiliation_cnps,
            ]);
        }

        return back()->with('success', 'Affiliation CNPS mise à jour avec succès.');
    }

    /**
     * Supprimer une affiliation
     */
    public function destroyAffiliation($id)
    {
        $affiliation = CnpsAffiliation::findOrFail($id);
        $affiliation->delete();

        return back()->with('success', 'Affiliation supprimée.');
    }
}