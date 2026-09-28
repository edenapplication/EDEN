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
use Illuminate\Support\Facades\Schema;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ContratController extends Controller
{
    /**
     * Liste des contrats
     */
    public function index(Request $request)
    {
        $query = Contrat::with(['employe', 'typeContrat', 'direction', 'service', 'poste']);

        if ($request->filled('employe_id'))      $query->where('employe_id', $request->employe_id);
        if ($request->filled('type_contrat_id')) $query->where('type_contrat_id', $request->type_contrat_id);
        if ($request->filled('statut'))          $query->where('statut', $request->statut);
        if ($request->filled('direction_id'))    $query->where('direction_id', $request->direction_id);
        if ($request->filled('mois')) {
            $query->whereYear('date_debut', Carbon::parse($request->mois)->year)
                  ->whereMonth('date_debut', Carbon::parse($request->mois)->month);
        }

        $contrats = $query->orderByDesc('date_debut')->paginate(20)->withQueryString();

        $employes     = Employe::where('actif', true)->orderBy('nom')->get();
        $typesContrat = TypeContrat::actif()->get();
        $directions   = Direction::orderBy('nom')->get();

        $stats = [
            'total'        => Contrat::count(),
            'actifs'       => Contrat::where('statut', 'actif')->count(),
            'en_attente'   => Contrat::where('statut', 'en_attente')->count(),
            'termines'     => Contrat::whereIn('statut', ['termine', 'resilie'])->count(),
            'alerte_essai' => Contrat::alertePeriodeEssai(7)->count(),
            'alerte_fin'   => Contrat::alerteFin(15)->count(),
        ];

        return view('rh.contrats.index', compact('contrats', 'employes', 'typesContrat', 'directions', 'stats'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $employes     = Employe::where('actif', true)->orderBy('nom')->get();
        $typesContrat = TypeContrat::actif()->get();
        $directions   = Direction::orderBy('nom')->get();
        $services     = Service::with('direction')->orderBy('nom')->get();
        $postes       = Poste::orderBy('intitule')->get();
        $agences      = AgenceSite::actif()->get();

        return view('rh.contrats.create', compact('employes', 'typesContrat', 'directions', 'services', 'postes', 'agences'));
    }

    /**
     * Enregistrer un nouveau contrat
     */
    public function store(Request $request)
    {
        // ✅ VALIDATION COMPLÈTE
        $validated = $request->validate([
            'employe_id'               => 'required|exists:rh_employes,id',
            'type_contrat_id'          => 'required|exists:rh_types_contrat,id',
            'date_debut'               => 'required|date',
            'date_fin'                 => 'nullable|date|after:date_debut',
            'periode_essai_jours'      => 'nullable|integer|min:0|max:365',
            'salaire_base'             => 'required|numeric|min:0',
            'statut'                   => 'required|in:en_attente,valide,actif',
            'numero_contrat'           => 'nullable|string|max:50|unique:rh_contrats,numero_contrat',
            'direction_id'             => 'nullable|exists:rh_directions,id',
            'service_id'               => 'nullable|exists:rh_services,id',
            'poste_id'                 => 'nullable|exists:rh_postes,id',
            'agence_site_id'           => 'nullable|exists:rh_agence_sites,id',
            'mode_paiement'            => 'nullable|in:VIREMENT,CHEQUE,ESPECES',
            'responsable_hierarchique' => 'nullable|string|max:255',
            'lieu_travail'             => 'nullable|string|max:255',
            'horaires'                 => 'nullable|string|max:255',
            'conditions_particulieres' => 'nullable|string',
            'notes'                    => 'nullable|string',
            'date_signature'           => 'nullable|date',
            'est_renouvelable'         => 'nullable|boolean',
        ], [
            'date_fin.after' => 'La date de fin doit être postérieure à la date de début.',
        ]);

        // ✅ Vérifier qu'il n'y a pas déjà un contrat actif
        if ($validated['statut'] === 'actif') {
            $existeActif = Contrat::where('employe_id', $validated['employe_id'])
                ->where('statut', 'actif')
                ->exists();

            if ($existeActif) {
                return back()
                    ->withErrors(['statut' => 'Cet employé a déjà un contrat actif.'])
                    ->withInput();
            }
        }

        $data = $validated;

        // ✅ Générer un numéro si absent
        if (empty($data['numero_contrat'])) {
            $data['numero_contrat'] = Contrat::genererNumeroContrat(
                $data['employe_id'],
                Carbon::parse($data['date_debut'])
            );
        }

        // ✅ Période d'essai
        if (!empty($data['periode_essai_jours']) && $data['periode_essai_jours'] > 0) {
            $data['date_fin_periode_essai'] = Carbon::parse($data['date_debut'])
                ->addDays((int) $data['periode_essai_jours']);
        }

        $contrat = Contrat::create($data);

        if ($contrat->statut === 'actif') {
            $this->mettreAJourEmploye($contrat);
        }

        return redirect()
            ->route('rh.contrats.show', $contrat->id)
            ->with('success', 'Contrat créé avec succès. Numéro : ' . $contrat->numero_contrat);
    }

    /**
     * Afficher un contrat
     */
    public function show($id)
    {
        $contrat = Contrat::with([
            'employe', 'typeContrat', 'direction',
            'service', 'poste', 'agenceSite', 'validePar'
        ])->findOrFail($id);

        $historique = Contrat::where('employe_id', $contrat->employe_id)
                            ->where('id', '!=', $id)
                            ->orderByDesc('date_debut')
                            ->get();

        return view('rh.contrats.show', compact('contrat', 'historique'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $contrat      = Contrat::findOrFail($id);
        $employes     = Employe::where('actif', true)->orderBy('nom')->get();
        $typesContrat = TypeContrat::actif()->get();
        $directions   = Direction::orderBy('nom')->get();
        $services     = Service::with('direction')->orderBy('nom')->get();
        $postes       = Poste::orderBy('intitule')->get();
        $agences      = AgenceSite::actif()->get();

        return view('rh.contrats.edit', compact('contrat', 'employes', 'typesContrat', 'directions', 'services', 'postes', 'agences'));
    }

    /**
     * Mettre à jour un contrat
     */
    public function update(Request $request, $id)
    {
        $contrat = Contrat::findOrFail($id);

        $validated = $request->validate([
            'type_contrat_id'          => 'required|exists:rh_types_contrat,id',
            'date_debut'               => 'required|date',
            'date_fin'                 => 'nullable|date|after:date_debut',
            'periode_essai_jours'      => 'nullable|integer|min:0|max:365',
            'salaire_base'             => 'required|numeric|min:0',
            'statut'                   => 'required|in:en_attente,valide,actif,suspendu,termine,resilie,annule',
            'direction_id'             => 'nullable|exists:rh_directions,id',
            'service_id'               => 'nullable|exists:rh_services,id',
            'poste_id'                 => 'nullable|exists:rh_postes,id',
            'agence_site_id'           => 'nullable|exists:rh_agence_sites,id',
            'mode_paiement'            => 'nullable|in:VIREMENT,CHEQUE,ESPECES',
            'responsable_hierarchique' => 'nullable|string|max:255',
            'lieu_travail'             => 'nullable|string|max:255',
            'horaires'                 => 'nullable|string|max:255',
            'conditions_particulieres' => 'nullable|string',
            'notes'                    => 'nullable|string',
            'date_signature'           => 'nullable|date',
            'est_renouvelable'         => 'nullable|boolean',
        ]);

        // ✅ Vérifier qu'il n'y a pas un autre contrat actif pour le même employé
        if ($validated['statut'] === 'actif') {
            $existeActif = Contrat::where('employe_id', $contrat->employe_id)
                ->where('statut', 'actif')
                ->where('id', '!=', $contrat->id)
                ->exists();

            if ($existeActif) {
                return back()
                    ->withErrors(['statut' => 'Cet employé a déjà un autre contrat actif.'])
                    ->withInput();
            }
        }

        $data = $validated;

        // ✅ Recalculer la période d'essai
        if (!empty($data['periode_essai_jours']) && $data['periode_essai_jours'] > 0) {
            $data['date_fin_periode_essai'] = Carbon::parse($data['date_debut'])
                ->addDays((int) $data['periode_essai_jours']);
        } else {
            $data['date_fin_periode_essai'] = null;
        }

        $contrat->update($data);

        if ($contrat->statut === 'actif') {
            $this->mettreAJourEmploye($contrat);
        }

        return redirect()
            ->route('rh.contrats.show', $contrat->id)
            ->with('success', 'Contrat mis à jour avec succès');
    }

    /**
     * Supprimer un contrat
     */
    public function destroy($id)
    {
        $contrat = Contrat::findOrFail($id);

        if ($contrat->statut === 'actif') {
            return back()->with('error', 'Impossible de supprimer un contrat actif. Résiliez-le d\'abord.');
        }

        $contrat->delete();

        return redirect()
            ->route('rh.contrats.index')
            ->with('success', 'Contrat supprimé avec succès');
    }

    /**
     * Valider un contrat (en_attente → valide)
     */
    public function valider(Request $request, $id)
    {
        $contrat = Contrat::findOrFail($id);

        if ($contrat->statut !== 'en_attente') {
            return back()->with('error', 'Seul un contrat en attente peut être validé.');
        }

        $contrat->update([
            'statut'          => 'valide',
            'date_validation' => now(),
            'valide_par'      => auth()->id(),
        ]);

        return back()->with('success', 'Contrat validé avec succès');
    }

    /**
     * Activer un contrat (valide → actif)
     */
    public function activer(Request $request, $id)
    {
        $contrat = Contrat::findOrFail($id);

        if ($contrat->statut !== 'valide') {
            return back()->with('error', 'Seul un contrat validé peut être activé.');
        }

        // ✅ Vérifier qu'il n'y a pas déjà un contrat actif
        $existeActif = Contrat::where('employe_id', $contrat->employe_id)
            ->where('statut', 'actif')
            ->where('id', '!=', $contrat->id)
            ->exists();

        if ($existeActif) {
            return back()->with('error', 'Cet employé a déjà un contrat actif.');
        }

        $contrat->update(['statut' => 'actif']);
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

        $validated = $request->validate([
            'motif_resiliation' => 'nullable|string|max:255',
            'date_resiliation'  => 'required|date',
        ]);

        // ✅ Vérification manuelle de la date
        if (Carbon::parse($validated['date_resiliation'])->lt($contrat->date_debut)) {
            return back()
                ->withErrors(['date_resiliation' => 'La date de résiliation doit être postérieure à la date de début.'])
                ->withInput();
        }

        $contrat->update([
            'statut'   => 'resilie',
            'date_fin' => $validated['date_resiliation'],
            'notes'    => ($contrat->notes ? $contrat->notes . "\n" : '')
                        . 'Résilié le ' . now()->format('d/m/Y')
                        . ' - Motif: ' . ($validated['motif_resiliation'] ?? 'Non spécifié'),
        ]);

        $employe = $contrat->employe;
        if ($employe) {
            $employe->update([
                'actif'        => false,
                'date_sortie'  => $validated['date_resiliation'],
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
            return back()->with('error', 'Ce contrat ne peut pas être renouvelé.');
        }

        // ✅ Construction dynamique de la règle date_debut
        $dateDebutRule = 'required|date';
        if ($ancienContrat->date_fin) {
            $dateDebutRule .= '|after_or_equal:' . $ancienContrat->date_fin->format('Y-m-d');
        }

        $validated = $request->validate([
            'date_debut'          => $dateDebutRule,
            'date_fin'            => 'nullable|date|after:date_debut',
            'periode_essai_jours' => 'nullable|integer|min:0|max:365',
            'salaire_base'        => 'required|numeric|min:0',
        ]);

        $nouveauContrat = $ancienContrat->renouveler([
            'date_debut'          => $validated['date_debut'],
            'date_fin'            => $validated['date_fin'] ?? null,
            'periode_essai_jours' => $validated['periode_essai_jours'] ?? 0,
            'salaire_base'        => $validated['salaire_base'],
            'salaire_brut'        => $validated['salaire_base'],
        ]);

        if ($nouveauContrat->statut === 'actif') {
            $this->mettreAJourEmploye($nouveauContrat);
        }

        return redirect()
            ->route('rh.contrats.show', $nouveauContrat->id)
            ->with('success', 'Contrat renouvelé. Nouveau numéro : ' . $nouveauContrat->numero_contrat);
    }

    /**
     * Télécharger le PDF d'un contrat
     */
    public function pdf($id)
    {
        $contrat = Contrat::with(['employe', 'typeContrat', 'direction', 'service', 'poste'])
                          ->findOrFail($id);

        $pdf = Pdf::loadView('rh.contrats.pdf', compact('contrat'))->setPaper('a4');

        return $pdf->download('contrat_' . $contrat->numero_contrat . '.pdf');
    }

    /**
     * Mettre à jour les informations de l'employé à partir du contrat actif
     */
    private function mettreAJourEmploye(Contrat $contrat)
    {
        $employe = $contrat->employe;
        if (!$employe) return;

        if ($contrat->statut === 'actif') {
            $employe->update([
                'salaire_base'           => $contrat->salaire_base,
                'direction_id'           => $contrat->direction_id   ?? $employe->direction_id,
                'service_id'             => $contrat->service_id     ?? $employe->service_id,
                'poste_id'               => $contrat->poste_id       ?? $employe->poste_id,
                'agence_site_id'         => $contrat->agence_site_id ?? $employe->agence_site_id,
                'intitule_poste'         => $contrat->poste?->intitule ?? $employe->intitule_poste,
                'type_contrat'           => $contrat->typeContrat?->nom ?? $employe->type_contrat,
                'mode_paiement'          => $contrat->mode_paiement  ?? $employe->mode_paiement,
                'date_integration'       => $contrat->date_debut,
                'date_fin_periode_essai' => $contrat->date_fin_periode_essai,
                'actif'                  => true,
            ]);

            // ✅ Sécuriser si la colonne n'existe pas
            if (Schema::hasColumn('rh_employes', 'contrat_actif_id')) {
                $employe->update(['contrat_actif_id' => $contrat->id]);
            }
        }
    }
}