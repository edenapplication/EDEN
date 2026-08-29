<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Depart;
use App\Models\RH\Employe;
use App\Models\RH\Contrat;
use App\Models\RH\MotifDepart;
use App\Models\RH\SoldeToutCompte;
use App\Models\RH\CertificatCessation;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class DepartController extends Controller
{
    /**
     * Liste des départs
     */
    public function index(Request $request)
    {
        $query = Depart::with(['employe', 'motifDepart', 'soldeToutCompte']);

        // Filtres
        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('mois')) {
            $query->whereYear('date_depart', Carbon::parse($request->mois)->year)
                  ->whereMonth('date_depart', Carbon::parse($request->mois)->month);
        }

        $departs = $query->orderByDesc('date_depart')->paginate(20)->withQueryString();
        
        // Statistiques
        $stats = [
            'total' => Depart::count(),
            'en_attente' => Depart::where('statut', 'en_attente')->count(),
            'valide' => Depart::where('statut', 'valide')->count(),
            'termine' => Depart::where('statut', 'termine')->count(),
            'annule' => Depart::where('statut', 'annule')->count(),
        ];

        $employes = Employe::where('actif', true)->orderBy('nom')->get();

        return view('rh.departs.index', compact('departs', 'stats', 'employes'));
    }

    /**
     * Formulaire de création d'un départ
     */
    public function create(Request $request)
    {
        $employe = null;
        if ($request->filled('employe_id')) {
            $employe = Employe::with(['contratActif', 'direction', 'service'])->find($request->employe_id);
        }

        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        $motifs = MotifDepart::actif()->get();

        return view('rh.departs.create', compact('employes', 'motifs', 'employe'));
    }

    /**
     * Enregistrer un départ
     */
    public function store(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:rh_employes,id',
            'motif_depart_id' => 'nullable|exists:rh_motifs_depart,id',
            'motif_libre' => 'nullable|string|max:255',
            'date_depart' => 'required|date|after_or_equal:today',
            'date_notification' => 'nullable|date',
            'date_preavis' => 'nullable|date|before_or_equal:date_depart',
            'observations' => 'nullable|string',
        ]);

        // Vérifier que l'employé n'a pas déjà un départ en cours
        $existant = Depart::where('employe_id', $request->employe_id)
            ->whereIn('statut', ['en_attente', 'valide', 'en_cours'])
            ->exists();

        if ($existant) {
            return back()->with('error', 'Cet employé a déjà un départ en cours.');
        }

        // Récupérer le dernier contrat
        $dernierContrat = Contrat::where('employe_id', $request->employe_id)
            ->where('statut', 'actif')
            ->first();

        $depart = Depart::create([
            'employe_id' => $request->employe_id,
            'motif_depart_id' => $request->motif_depart_id,
            'dernier_contrat_id' => $dernierContrat?->id,
            'date_depart' => $request->date_depart,
            'date_notification' => $request->date_notification,
            'date_preavis' => $request->date_preavis,
            'motif_libre' => $request->motif_libre,
            'statut' => 'en_attente',
            'observations' => $request->observations,
        ]);

        return redirect()->route('rh.departs.show', $depart->id)
            ->with('success', 'Départ enregistré avec succès.');
    }

    /**
     * Afficher un départ
     */
    public function show($id)
    {
        $depart = Depart::with([
            'employe', 
            'motifDepart', 
            'dernierContrat',
            'soldeToutCompte',
            'certificatCessation',
            'validePar'
        ])->findOrFail($id);

        // Historique des départs du même employé
        $historique = Depart::where('employe_id', $depart->employe_id)
            ->where('id', '!=', $id)
            ->orderByDesc('date_depart')
            ->get();

        return view('rh.departs.show', compact('depart', 'historique'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $depart = Depart::findOrFail($id);
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        $motifs = MotifDepart::actif()->get();

        return view('rh.departs.edit', compact('depart', 'employes', 'motifs'));
    }

    /**
     * Mettre à jour un départ
     */
    public function update(Request $request, $id)
    {
        $depart = Depart::findOrFail($id);

        $request->validate([
            'motif_depart_id' => 'nullable|exists:rh_motifs_depart,id',
            'motif_libre' => 'nullable|string|max:255',
            'date_depart' => 'required|date',
            'date_notification' => 'nullable|date',
            'date_preavis' => 'nullable|date|before_or_equal:date_depart',
            'statut' => 'required|in:en_attente,valide,en_cours,termine,annule',
            'observations' => 'nullable|string',
        ]);

        $depart->update($request->only([
            'motif_depart_id',
            'motif_libre',
            'date_depart',
            'date_notification',
            'date_preavis',
            'statut',
            'observations',
        ]));

        // Si le statut devient "termine", archiver l'employé
        if ($request->statut === 'termine') {
            $depart->employe->update([
                'actif' => false,
                'date_sortie' => $depart->date_depart,
                'cause_depart' => $depart->motifDepart?->nom ?? $depart->motif_libre,
            ]);
        }

        return redirect()->route('rh.departs.show', $depart->id)
            ->with('success', 'Départ mis à jour avec succès.');
    }

    /**
     * Valider un départ (en_attente -> valide)
     */
    public function valider($id)
    {
        $depart = Depart::findOrFail($id);

        if ($depart->statut !== 'en_attente') {
            return back()->with('error', 'Seul un départ en attente peut être validé.');
        }

        $depart->update([
            'statut' => 'valide',
            'valide_par' => auth()->id(),
            'date_validation' => now(),
        ]);

        return back()->with('success', 'Départ validé avec succès.');
    }

    /**
     * Générer le solde de tout compte
     */
    public function genererSolde($id)
    {
        $depart = Depart::findOrFail($id);

        // Vérifier si un solde existe déjà
        if ($depart->soldeToutCompte) {
            return back()->with('error', 'Un solde de tout compte existe déjà pour ce départ.');
        }

        // Générer le solde
        $solde = $depart->genererSoldeToutCompte();

        return redirect()->route('rh.departs.show', $depart->id)
            ->with('success', 'Solde de tout compte généré avec succès.');
    }

    /**
     * Générer le certificat de cessation
     */
    public function genererCertificat($id)
    {
        $depart = Depart::findOrFail($id);

        // Vérifier si un certificat existe déjà
        if ($depart->certificatCessation) {
            return back()->with('error', 'Un certificat de cessation existe déjà pour ce départ.');
        }

        // Générer le certificat
        $certificat = $depart->genererCertificatCessation();

        return redirect()->route('rh.departs.show', $depart->id)
            ->with('success', 'Certificat de cessation généré avec succès.');
    }

    /**
     * Télécharger le PDF du solde de tout compte
     */
    public function pdfSolde($id)
    {
        $solde = SoldeToutCompte::with(['employe', 'depart.motifDepart'])->findOrFail($id);

        $pdf = Pdf::loadView('rh.departs.pdf_solde', compact('solde'))
            ->setPaper('a4');

        return $pdf->download('solde_tout_compte_' . $solde->employe->matricule . '.pdf');
    }

    /**
     * Télécharger le PDF du certificat de cessation
     */
    public function pdfCertificat($id)
    {
        $certificat = CertificatCessation::with(['employe', 'depart.motifDepart'])->findOrFail($id);

        $pdf = Pdf::loadView('rh.departs.pdf_certificat', compact('certificat'))
            ->setPaper('a4');

        return $pdf->download('certificat_cessation_' . $certificat->employe->matricule . '.pdf');
    }

    /**
     * Supprimer un départ
     */
    public function destroy($id)
    {
        $depart = Depart::findOrFail($id);

        if ($depart->statut === 'termine') {
            return back()->with('error', 'Impossible de supprimer un départ terminé.');
        }

        $depart->delete();

        return redirect()->route('rh.departs.index')
            ->with('success', 'Départ supprimé avec succès.');
    }

    /**
     * Annuler un départ
     */
    public function annuler($id)
    {
        $depart = Depart::findOrFail($id);

        if (!in_array($depart->statut, ['en_attente', 'valide'])) {
            return back()->with('error', 'Seul un départ en attente ou validé peut être annulé.');
        }

        $depart->update(['statut' => 'annule']);

        return back()->with('success', 'Départ annulé avec succès.');
    }
}