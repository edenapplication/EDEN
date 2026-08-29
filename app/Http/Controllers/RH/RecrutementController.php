<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Candidat;
use App\Models\RH\Entretien;
use App\Models\RH\TestCandidat;
use App\Models\RH\SourceCandidature;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class RecrutementController extends Controller
{
    /**
     * Liste des candidats
     */
    public function index(Request $request)
    {
        $query = Candidat::with(['source', 'dernierEntretien']);

        // Filtres
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }
        if ($request->filled('search')) {
            $query->recherche($request->search);
        }
        if ($request->filled('source_id')) {
            $query->where('source_id', $request->source_id);
        }

        $candidats = $query->orderByDesc('date_candidature')->paginate(20)->withQueryString();

        // Statistiques
        $stats = [
            'total' => Candidat::count(),
            'actifs' => Candidat::actifs()->count(),
            'recu' => Candidat::statut('recu')->count(),
            'preselectionne' => Candidat::statut('preselectionne')->count(),
            'entretien_rh' => Candidat::statut('entretien_rh')->count(),
            'embauche' => Candidat::statut('embauche')->count(),
            'rejete' => Candidat::statut('rejete')->count(),
        ];

        $sources = SourceCandidature::actif()->get();

        return view('rh.recrutement.index', compact('candidats', 'stats', 'sources'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $sources = SourceCandidature::actif()->get();
        return view('rh.recrutement.create', compact('sources'));
    }

    /**
     * Enregistrer un candidat
     */
    public function store(Request $request)
    {
        $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'telephone' => 'nullable|string|max:20',
            'poste_demande' => 'nullable|string|max:150',
            'date_candidature' => 'required|date',
            'source_id' => 'nullable|exists:rh_sources_candidature,id',
            'cv' => 'nullable|file|max:5120|mimes:pdf,doc,docx',
            'lettre_motivation' => 'nullable|file|max:5120|mimes:pdf,doc,docx',
        ]);

        $data = $request->all();

        // Upload CV
        if ($request->hasFile('cv')) {
            $data['cv_path'] = $request->file('cv')->store('rh/recrutement/cv', 'public');
        }

        // Upload Lettre de motivation
        if ($request->hasFile('lettre_motivation')) {
            $data['lettre_motivation_path'] = $request->file('lettre_motivation')->store('rh/recrutement/lettres', 'public');
        }

        $candidat = Candidat::create($data);

        return redirect()->route('rh.recrutement.show', $candidat->id)
            ->with('success', 'Candidature enregistrée avec succès.');
    }

    /**
     * Afficher un candidat
     */
    public function show($id)
    {
        $candidat = Candidat::with(['source', 'entretiens', 'tests'])
            ->findOrFail($id);

        $sources = SourceCandidature::actif()->get();

        return view('rh.recrutement.show', compact('candidat', 'sources'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $candidat = Candidat::findOrFail($id);
        $sources = SourceCandidature::actif()->get();
        return view('rh.recrutement.edit', compact('candidat', 'sources'));
    }

    /**
     * Mettre à jour un candidat
     */
    public function update(Request $request, $id)
    {
        $candidat = Candidat::findOrFail($id);

        $request->validate([
            'nom' => 'required|string|max:100',
            'prenom' => 'required|string|max:100',
            'email' => 'nullable|email|max:150',
            'telephone' => 'nullable|string|max:20',
            'poste_demande' => 'nullable|string|max:150',
            'date_candidature' => 'required|date',
            'source_id' => 'nullable|exists:rh_sources_candidature,id',
            'cv' => 'nullable|file|max:5120|mimes:pdf,doc,docx',
            'lettre_motivation' => 'nullable|file|max:5120|mimes:pdf,doc,docx',
        ]);

        $data = $request->all();

        // Upload CV
        if ($request->hasFile('cv')) {
            if ($candidat->cv_path) {
                Storage::disk('public')->delete($candidat->cv_path);
            }
            $data['cv_path'] = $request->file('cv')->store('rh/recrutement/cv', 'public');
        }

        // Upload Lettre de motivation
        if ($request->hasFile('lettre_motivation')) {
            if ($candidat->lettre_motivation_path) {
                Storage::disk('public')->delete($candidat->lettre_motivation_path);
            }
            $data['lettre_motivation_path'] = $request->file('lettre_motivation')->store('rh/recrutement/lettres', 'public');
        }

        $candidat->update($data);

        return redirect()->route('rh.recrutement.show', $candidat->id)
            ->with('success', 'Candidature mise à jour.');
    }

    /**
     * Changer le statut d'un candidat
     */
    public function changerStatut(Request $request, $id)
    {
        $candidat = Candidat::findOrFail($id);

        $request->validate([
            'statut' => 'required|in:recu,preselectionne,entretien_rh,entretien_hierarchique,test,valide,embauche,rejete',
        ]);

        if (!$candidat->peutPasserA($request->statut)) {
            return back()->with('error', 'Transition de statut non autorisée.');
        }

        $candidat->statut = $request->statut;

        // Si embauche, créer automatiquement l'employé
        if ($request->statut === 'embauche' && $candidat->estEmbauchable()) {
            $employe = $candidat->creerEmploye();
            if ($employe) {
                $candidat->save();
                return redirect()->route('rh.employes.show', $employe->id)
                    ->with('success', 'Candidat embauché ! Employé créé : ' . $employe->matricule);
            }
        }

        $candidat->save();

        return back()->with('success', 'Statut mis à jour : ' . $candidat->statut_label);
    }

    /**
     * Ajouter un entretien
     */
    public function storeEntretien(Request $request, $id)
    {
        $candidat = Candidat::findOrFail($id);

        $request->validate([
            'type' => 'required|in:rh,hierarchique,technique,final',
            'date_entretien' => 'required|date',
            'heure' => 'nullable',
            'lieu' => 'nullable|string|max:255',
            'evaluateur' => 'nullable|string|max:150',
            'evaluateur_poste' => 'nullable|string|max:150',
            'note' => 'nullable|integer|min:1|max:5',
            'points_forts' => 'nullable|string',
            'points_faibles' => 'nullable|string',
            'remarques' => 'nullable|string',
            'decision' => 'nullable|in:positif,negatif,en_attente',
        ]);

        Entretien::create([
            'candidat_id' => $candidat->id,
            'type' => $request->type,
            'date_entretien' => $request->date_entretien,
            'heure' => $request->heure,
            'lieu' => $request->lieu,
            'evaluateur' => $request->evaluateur,
            'evaluateur_poste' => $request->evaluateur_poste,
            'note' => $request->note,
            'points_forts' => $request->points_forts,
            'points_faibles' => $request->points_faibles,
            'remarques' => $request->remarques,
            'decision' => $request->decision ?? 'en_attente',
        ]);

        // Avancer automatiquement le statut si entretien positif
        if ($request->decision === 'positif') {
            if ($candidat->peutPasserA('valide')) {
                $candidat->statut = 'valide';
                $candidat->save();
            }
        }

        return back()->with('success', 'Entretien enregistré.');
    }

    /**
     * Ajouter un test
     */
    public function storeTest(Request $request, $id)
    {
        $candidat = Candidat::findOrFail($id);

        $request->validate([
            'type_test' => 'required|string|max:100',
            'date_test' => 'required|date',
            'note' => 'nullable|numeric|min:0|max:20',
            'resultats' => 'nullable|string',
            'appreciation' => 'nullable|string',
            'fichier' => 'nullable|file|max:5120|mimes:pdf,doc,docx,xlsx',
        ]);

        $data = $request->all();

        if ($request->hasFile('fichier')) {
            $data['fichier_path'] = $request->file('fichier')->store('rh/recrutement/tests', 'public');
        }

        TestCandidat::create([
            'candidat_id' => $candidat->id,
            'type_test' => $request->type_test,
            'date_test' => $request->date_test,
            'note' => $request->note,
            'resultats' => $request->resultats,
            'appreciation' => $request->appreciation,
            'fichier_path' => $data['fichier_path'] ?? null,
        ]);

        return back()->with('success', 'Test enregistré.');
    }

    /**
     * Supprimer un candidat
     */
    public function destroy($id)
    {
        $candidat = Candidat::findOrFail($id);

        if ($candidat->statut === 'embauche') {
            return back()->with('error', 'Impossible de supprimer un candidat embauché.');
        }

        $candidat->delete();

        return redirect()->route('rh.recrutement.index')
            ->with('success', 'Candidature supprimée.');
    }

    /**
     * Télécharger un document
     */
    public function downloadDocument($id, $type)
    {
        $candidat = Candidat::findOrFail($id);

        $path = match($type) {
            'cv' => $candidat->cv_path,
            'lettre' => $candidat->lettre_motivation_path,
            'test' => request('fichier_id') ? TestCandidat::find(request('fichier_id'))?->fichier_path : null,
            default => null,
        };

        if (!$path || !Storage::disk('public')->exists($path)) {
            return back()->with('error', 'Fichier introuvable.');
        }

        return Storage::disk('public')->download($path);
    }

    /**
     * PDF liste des candidats
     */
    public function pdfListe(Request $request)
    {
        $query = Candidat::with(['source']);

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $candidats = $query->orderByDesc('date_candidature')->get();

        $pdf = Pdf::loadView('rh.recrutement.pdf_liste', compact('candidats'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('candidats_' . now()->format('Y-m-d') . '.pdf');
    }
}