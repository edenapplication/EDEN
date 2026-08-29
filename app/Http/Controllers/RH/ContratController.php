<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Contrat;
use App\Models\RH\Employe;
use App\Models\RH\Direction;
use App\Models\RH\Service;
use App\Models\RH\Poste;
use App\Models\RH\AgenceSite;
use App\Models\RH\TypeContrat;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ContratController extends Controller
{
    /**
     * Afficher la liste des contrats
     */
    public function index(Request $request)
    {
        $query = Contrat::with(['employe', 'typeContrat', 'direction', 'service', 'poste']);

        // Filtres
        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }
        if ($request->filled('type_contrat_id')) {
            $query->where('type_contrat_id', $request->type_contrat_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('direction_id')) {
            $query->where('direction_id', $request->direction_id);
        }
        if ($request->filled('mois')) {
            $query->whereYear('date_debut', Carbon::parse($request->mois)->year)
                  ->whereMonth('date_debut', Carbon::parse($request->mois)->month);
        }

        $contrats = $query->orderByDesc('date_debut')->paginate(20)->withQueryString();

        // Données pour les filtres
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        $typesContrat = TypeContrat::actif()->get();
        $directions = Direction::orderBy('nom')->get();

        // Statistiques
        $stats = [
            'total' => Contrat::count(),
            'actifs' => Contrat::where('statut', 'actif')->count(),
            'en_attente' => Contrat::where('statut', 'en_attente')->count(),
            'termines' => Contrat::whereIn('statut', ['termine', 'resilie'])->count(),
            'alerte_essai' => Contrat::alertePeriodeEssai(7)->count(),
            'alerte_fin' => Contrat::alerteFin(15)->count(),
        ];

        return view('rh.contrats.index', compact('contrats', 'employes', 'typesContrat', 'directions', 'stats'));
    }

    /**
     * Afficher le formulaire de création
     */
    public function create()
    {
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        $typesContrat = TypeContrat::actif()->get();
        $directions = Direction::orderBy('nom')->get();
        $services = Service::with('direction')->orderBy('nom')->get();
        $postes = Poste::orderBy('intitule')->get();
        $agences = AgenceSite::actif()->get();

        return view('rh.contrats.create', compact('employes', 'typesContrat', 'directions', 'services', 'postes', 'agences'));
    }

    /**
     * Enregistrer un nouveau contrat
     */
    public function store(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:rh_employes,id',
            'type_contrat_id' => 'required|exists:rh_types_contrat,id',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'periode_essai_jours' => 'nullable|integer|min:0|max:365',
            'salaire_base' => 'required|numeric|min:0',
            'statut' => 'required|in:en_attente,valide,actif',
            'numero_contrat' => 'nullable|string|max:50|unique:rh_contrats,numero_contrat',
        ]);

        $data = $request->all();

        // Générer un numéro de contrat si non fourni
        if (empty($data['numero_contrat'])) {
            $data['numero_contrat'] = Contrat::genererNumeroContrat($request->employe_id, Carbon::parse($request->date_debut));
        }

        // Calculer la date de fin de période d'essai
        if ($request->filled('periode_essai_jours') && $request->periode_essai_jours > 0) {
            $data['date_fin_periode_essai'] = Carbon::parse($request->date_debut)->addDays((int) $request->periode_essai_jours);
        }

        $contrat = Contrat::create($data);

        // Si le contrat est actif, mettre à jour les infos de l'employé
        if ($contrat->statut === 'actif') {
            $this->mettreAJourEmploye($contrat);
        }

        return redirect()->route('rh.contrats.show', $contrat->id)
                         ->with('success', 'Contrat créé avec succès. Numéro : ' . $contrat->numero_contrat);
    }

    /**
     * Afficher un contrat
     */
    public function show($id)
    {
        $contrat = Contrat::with([
            'employe',
            'typeContrat',
            'direction',
            'service',
            'poste',
            'agenceSite',
            'validePar'
        ])->findOrFail($id);

        // Historique des contrats du même employé
        $historique = Contrat::where('employe_id', $contrat->employe_id)
                            ->where('id', '!=', $id)
                            ->orderByDesc('date_debut')
                            ->get();

        return view('rh.contrats.show', compact('contrat', 'historique'));
    }

    /**
     * Afficher le formulaire d'édition
     */
    public function edit($id)
    {
        $contrat = Contrat::findOrFail($id);
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        $typesContrat = TypeContrat::actif()->get();
        $directions = Direction::orderBy('nom')->get();
        $services = Service::with('direction')->orderBy('nom')->get();
        $postes = Poste::orderBy('intitule')->get();
        $agences = AgenceSite::actif()->get();

        return view('rh.contrats.edit', compact('contrat', 'employes', 'typesContrat', 'directions', 'services', 'postes', 'agences'));
    }

    /**
     * Mettre à jour un contrat
     */
    public function update(Request $request, $id)
    {
        $contrat = Contrat::findOrFail($id);

        $request->validate([
            'type_contrat_id' => 'required|exists:rh_types_contrat,id',
            'date_debut' => 'required|date',
            'date_fin' => 'nullable|date|after:date_debut',
            'periode_essai_jours' => 'nullable|integer|min:0|max:365',
            'salaire_base' => 'required|numeric|min:0',
            'statut' => 'required|in:en_attente,valide,actif,suspendu,termine,resilie,annule',
        ]);

        $data = $request->all();

        // Recalculer la date de fin de période d'essai
        if ($request->filled('periode_essai_jours') && $request->periode_essai_jours > 0) {
            $data['date_fin_periode_essai'] = Carbon::parse($request->date_debut)->addDays((int) $request->periode_essai_jours);
        } else {
            $data['date_fin_periode_essai'] = null;
        }

        $contrat->update($data);

        // Si le contrat est actif, mettre à jour les infos de l'employé
        if ($contrat->statut === 'actif') {
            $this->mettreAJourEmploye($contrat);
        }

        return redirect()->route('rh.contrats.show', $contrat->id)
                         ->with('success', 'Contrat mis à jour avec succès');
    }

    /**
     * Supprimer un contrat
     */
    public function destroy($id)
    {
        $contrat = Contrat::findOrFail($id);

        // Empêcher la suppression des contrats actifs
        if ($contrat->statut === 'actif') {
            return back()->with('error', 'Impossible de supprimer un contrat actif. Veuillez d\'abord le résilier ou le terminer.');
        }

        $contrat->delete();

        return redirect()->route('rh.contrats.index')
                         ->with('success', 'Contrat supprimé avec succès');
    }

    /**
     * Valider un contrat (changement de statut en_attente -> valide)
     */
    public function valider(Request $request, $id)
    {
        $contrat = Contrat::findOrFail($id);

        if ($contrat->statut !== 'en_attente') {
            return back()->with('error', 'Seul un contrat en attente peut être validé.');
        }

        $contrat->update([
            'statut' => 'valide',
            'date_validation' => now(),
            'valide_par' => auth()->id(),
        ]);

        return back()->with('success', 'Contrat validé avec succès');
    }

    /**
     * Activer un contrat (valide -> actif)
     */
    public function activer(Request $request, $id)
    {
        $contrat = Contrat::findOrFail($id);

        if ($contrat->statut !== 'valide') {
            return back()->with('error', 'Seul un contrat validé peut être activé.');
        }

        $contrat->update(['statut' => 'actif']);

        // Mettre à jour les infos de l'employé
        $this->mettreAJourEmploye($contrat);

        return back()->with('success', 'Contrat activé avec succès');
    }

    /**
     * Résilier un contrat
     */
    public function resilier(Request $request, $id)
    {
        $contrat = Contrat::findOrFail($id);

        if (!in_array($contrat->statut, ['actif', 'suspendu'])) {
            return back()->with('error', 'Seul un contrat actif ou suspendu peut être résilié.');
        }

        $request->validate([
            'motif_resiliation' => 'nullable|string|max:255',
            'date_resiliation' => 'required|date|after_or_equal:date_debut',
        ]);

        $contrat->update([
            'statut' => 'resilie',
            'date_fin' => $request->date_resiliation,
            'notes' => ($contrat->notes ? $contrat->notes . "\n" : '') . 'Résilié le ' . now()->format('d/m/Y') . ' - Motif: ' . ($request->motif_resiliation ?? 'Non spécifié'),
        ]);

        // Mettre à jour l'employé
        $employe = $contrat->employe;
        if ($employe) {
            $employe->update([
                'actif' => false,
                'date_sortie' => $request->date_resiliation,
                'cause_depart' => 'Résiliation de contrat',
            ]);
        }

        return back()->with('success', 'Contrat résilié avec succès');
    }

    /**
     * Renouveler un contrat
     */
    public function renouveler(Request $request, $id)
    {
        $ancienContrat = Contrat::findOrFail($id);

        if (!$ancienContrat->peutEtreRenouvele()) {
            return back()->with('error', 'Ce contrat ne peut pas être renouvelé (non renouvelable ou nombre max atteint).');
        }

        $request->validate([
            'date_debut' => 'required|date|after_or_equal:' . $ancienContrat->date_fin,
            'date_fin' => 'nullable|date|after:date_debut',
            'periode_essai_jours' => 'nullable|integer|min:0|max:365',
            'salaire_base' => 'required|numeric|min:0',
        ]);

        // Créer le nouveau contrat
        $nouveauContrat = $ancienContrat->renouveler([
            'date_debut' => $request->date_debut,
            'date_fin' => $request->date_fin,
            'periode_essai_jours' => $request->periode_essai_jours ?? 0,
            'salaire_base' => $request->salaire_base,
            'salaire_brut' => $request->salaire_base,
        ]);

        // Mettre à jour l'employé si le nouveau contrat est actif
        if ($nouveauContrat->statut === 'actif') {
            $this->mettreAJourEmploye($nouveauContrat);
        }

        return redirect()->route('rh.contrats.show', $nouveauContrat->id)
                         ->with('success', 'Contrat renouvelé avec succès. Nouveau numéro : ' . $nouveauContrat->numero_contrat);
    }

    /**
     * Télécharger le PDF d'un contrat
     */
    public function pdf($id)
    {
        $contrat = Contrat::with(['employe', 'typeContrat', 'direction', 'service', 'poste'])
                          ->findOrFail($id);

        $pdf = Pdf::loadView('rh.contrats.pdf', compact('contrat'))
                  ->setPaper('a4');

        return $pdf->download('contrat_' . $contrat->numero_contrat . '.pdf');
    }

    /**
     * Mettre à jour les informations de l'employé à partir du contrat
     */
    private function mettreAJourEmploye(Contrat $contrat)
{
    $employe = $contrat->employe;
    if (!$employe) {
        return;
    }

    // Si le contrat est actif, mettre à jour le salaire
    if ($contrat->statut === 'actif') {
        $employe->update([
            'salaire_base' => $contrat->salaire_base,
            'direction_id' => $contrat->direction_id ?? $employe->direction_id,
            'service_id' => $contrat->service_id ?? $employe->service_id,
            'poste_id' => $contrat->poste_id ?? $employe->poste_id,
            'agence_site_id' => $contrat->agence_site_id ?? $employe->agence_site_id,
            'intitule_poste' => $contrat->poste?->intitule ?? $employe->intitule_poste,
            'type_contrat' => $contrat->typeContrat?->nom ?? $employe->type_contrat,
            'mode_paiement' => $contrat->mode_paiement ?? $employe->mode_paiement,
            'date_integration' => $contrat->date_debut,
            'date_fin_periode_essai' => $contrat->date_fin_periode_essai,
            'actif' => true,
        ]);

        // Mettre à jour le contrat actif dans l'employé
        $employe->update(['contrat_actif_id' => $contrat->id]);
    }
}

}