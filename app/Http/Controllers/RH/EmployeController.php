<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Employe;
use App\Models\RH\Direction;
use App\Models\RH\Service;
use App\Models\RH\Poste;
use App\Models\RH\EmployeDocument;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class EmployeController extends Controller
{
    private function options(): array
    {
        return [
            'directions'  => Direction::orderBy('nom')->get(),
            'services'    => Service::with('direction')->orderBy('nom')->get(),
            'postes'      => Poste::orderBy('intitule')->get(),
            'contrats'    => ['CDI', 'CDD', 'PRE-EMPLOI', 'STAGE', 'PRESTATAIRE'],
            'categories'  => ['0','1A','1B','2A','2B','2C','2D','3A','3B','3C','3D','4A','4B','4C','5','6','7A','7B','8'],
            'vagues'      => ['VAGUE 1', 'VAGUE 2'],
            'situations'  => ['Marié(e)', 'Célibataire', 'Fiancé(e)', 'Divorcé(e)', 'Veuf(ve)'],
            'niveaux'     => ['BACC+5', 'BACC+4', 'BACC+3', 'BACC+2', 'BACC', 'BEP', 'CAP', 'BEPC', 'SANS'],
        ];
    }

   public function index(Request $request)
{
    $query = Employe::with(['direction', 'service', 'poste']);

    if ($request->filled('search'))
        $query->where(fn($q) => $q
            ->where('nom',        'LIKE', "%{$request->search}%")
            ->orWhere('prenom',   'LIKE', "%{$request->search}%")
            ->orWhere('matricule','LIKE', "%{$request->search}%")
            ->orWhere('telephone','LIKE', "%{$request->search}%")
        );

    if ($request->filled('direction_id'))
        $query->where('direction_id', $request->direction_id);
    if ($request->filled('type_contrat'))
        $query->where('type_contrat', $request->type_contrat);
    if ($request->filled('vague'))
        $query->where('vague_paiement', $request->vague);

    // Statut : actif par défaut, inactif si demandé
    $showInactif = $request->statut === 'inactif';
    $query->where('actif', !$showInactif);

    $employes   = $query->orderBy('nom')->paginate(20)->withQueryString();
    $directions = Direction::orderBy('nom')->get();

    // ✅ Stats globales (tous employés actifs)
    $stats = [
        'total'  => Employe::where('actif', true)->count(),
        'hommes' => Employe::where('actif', true)->where('sexe', 'M')->count(),
        'femmes' => Employe::where('actif', true)->where('sexe', 'F')->count(),
        'cdi'    => Employe::where('actif', true)->where('type_contrat', 'CDI')->count(),
        'archives'=> Employe::where('actif', false)->count(),
    ];

    // ✅ Stats du filtre courant (résultats filtrés avant pagination)
    $queryFiltre = Employe::query();
    if ($request->filled('search'))
        $queryFiltre->where(fn($q) => $q
            ->where('nom',        'LIKE', "%{$request->search}%")
            ->orWhere('prenom',   'LIKE', "%{$request->search}%")
            ->orWhere('matricule','LIKE', "%{$request->search}%")
        );
    if ($request->filled('direction_id'))
        $queryFiltre->where('direction_id', $request->direction_id);
    if ($request->filled('type_contrat'))
        $queryFiltre->where('type_contrat', $request->type_contrat);
    if ($request->filled('vague'))
        $queryFiltre->where('vague_paiement', $request->vague);
    $queryFiltre->where('actif', !$showInactif);

    $employes_filtres = $queryFiltre->get();
    $statsFiltre = [
        'total'  => $employes_filtres->count(),
        'hommes' => $employes_filtres->where('sexe', 'M')->count(),
        'femmes' => $employes_filtres->where('sexe', 'F')->count(),
        'cdi'    => $employes_filtres->where('type_contrat', 'CDI')->count(),
    ];

    $filtreActif = $request->filled('search') || $request->filled('direction_id')
                || $request->filled('type_contrat') || $request->filled('vague');

    return view('rh.employes.index', compact(
        'employes', 'directions', 'stats', 'statsFiltre', 'filtreActif', 'showInactif'
    ));
}

    public function create()
    {
        $options   = $this->options();
        $matricule = 'EDG_MM_AA_XXXX';
        return view('rh.employes.create', compact('options', 'matricule'));
    }

   public function store(Request $request)
{
    $request->validate([
        'nom'              => 'required|string|max:100',
        'prenom'           => 'required|string|max:100',
        'sexe'             => 'required|in:M,F',
        'date_integration' => 'required|date',
        'type_contrat'     => 'required',
        'salaire_base'     => 'required|numeric|min:0',
    ]);

    // ✅ Créer sans matricule d'abord pour obtenir l'ID
    $employe = Employe::create(array_merge(
        $request->all(),
        ['matricule' => 'TEMP'] // temporaire
    ));

    // ✅ Générer le matricule avec l'ID réel
    $employe->matricule = Employe::genererMatricule($employe->id, $request->date_integration);
    $employe->save();

    return redirect()->route('rh.employes.show', $employe->id)
                     ->with('success', 'Employé créé — Matricule : ' . $employe->matricule);
}

    public function show($id)
    {
        $employe = Employe::with([
            'direction', 'service', 'poste',
            'documents',
            'bulletins'  => fn($q) => $q->orderBy('periode', 'desc')->limit(6),
            'absences'   => fn($q) => $q->orderBy('date_debut', 'desc')->limit(10),
            'prets'      => fn($q) => $q->where('statut', 'en_cours'),
            'sanctions'  => fn($q) => $q->orderBy('date', 'desc')->limit(5),
            'retards'    => fn($q) => $q->orderByDesc('date')->limit(10),
        ])->findOrFail($id);

        $totalAbsences   = $employe->absences->sum('nombre_jours');
        $totalRetards    = $employe->retards->count();
        $dernierBulletin = $employe->bulletins->first();

        return view('rh.employes.show', compact('employe', 'totalAbsences', 'totalRetards', 'dernierBulletin'));
    }

    public function edit($id)
    {
        $employe = Employe::findOrFail($id);
        $options = $this->options();
        return view('rh.employes.edit', compact('employe', 'options'));
    }

    public function update(Request $request, $id)
    {
        $employe = Employe::findOrFail($id);
        $employe->update($request->all());
        return redirect()->route('rh.employes.show', $id)->with('success', 'Mis à jour');
    }

    public function destroy($id)
    {
        $employe = Employe::findOrFail($id);
        $employe->update(['actif' => false, 'date_sortie' => now()]);
        return back()->with('success', 'Employé archivé');
    }

    public function export(Request $request)
    {
        $employes = Employe::with(['direction', 'service'])->where('actif', true)->get();
        if ($request->type === 'pdf') {
            $pdf = Pdf::loadView('rh.employes.pdf', compact('employes'))->setPaper('a4', 'landscape');
            return $pdf->download('employes_' . now()->format('Y-m-d') . '.pdf');
        }
        $callback = function() use ($employes) {
            $f = fopen('php://output', 'w');
            fprintf($f, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($f, ['Matricule','Nom','Prénom','Sexe','Direction','Poste','Contrat','Catégorie','Date intégration','Salaire base','Vague'], ';');
            foreach ($employes as $e) {
                fputcsv($f, [
                    $e->matricule, $e->nom, $e->prenom, $e->sexe,
                    $e->direction?->nom ?? '-', $e->intitule_poste ?? '-',
                    $e->type_contrat, $e->categorie ?? '-',
                    $e->date_integration?->format('d/m/Y'),
                    $e->salaire_base, $e->vague_paiement ?? '-',
                ], ';');
            }
            fclose($f);
        };
        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="employes_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }

    // ============================================================
    // PHOTO
    // ============================================================
    public function uploadPhoto(Request $request, $id)
    {
        $request->validate(['photo' => 'required|image|max:2048']);
        $employe = Employe::findOrFail($id);

        // ✅ Supprimer l'ancienne photo si elle existe
        if ($employe->photo_path && Storage::disk('public')->exists($employe->photo_path)) {
            Storage::disk('public')->delete($employe->photo_path);
        }

        $path = $request->file('photo')->store('rh/photos', 'public');
        $employe->update(['photo_path' => $path]);
        return back()->with('success', 'Photo mise à jour');
    }

    // ============================================================
    // PDF FICHE EMPLOYÉ
    // ============================================================
    public function pdfFiche($id)
    {
        $employe = Employe::with([
            'direction', 'service', 'poste',
            'bulletins' => fn($q) => $q->orderBy('periode', 'desc')->limit(6),
            'absences'  => fn($q) => $q->orderBy('date_debut', 'desc')->limit(10),
            'prets'     => fn($q) => $q->where('statut', 'en_cours'),
            'sanctions' => fn($q) => $q->orderBy('date', 'desc')->limit(5),
            'retards'   => fn($q) => $q->orderByDesc('date')->limit(10),
            'documents',
        ])->findOrFail($id);

        $totalAbsences   = $employe->absences->sum('nombre_jours');
        $totalRetards    = $employe->retards->count();
        $dernierBulletin = $employe->bulletins->first();
        $pretRestant     = $employe->prets->sum(fn($p) => max(0, $p->montant - $p->montant_rembourse));

        $pdf = Pdf::loadView('rh.employes.pdf_fiche',
            compact('employe', 'totalAbsences', 'totalRetards', 'dernierBulletin', 'pretRestant')
        )->setPaper('a4');

        return $pdf->download('fiche_' . $employe->matricule . '.pdf');
    }

    // ============================================================
    // DOCUMENTS
    // ============================================================
    public function uploadDocument(Request $request, $id)
    {
        $request->validate([
            'fichier' => 'required|file|max:10240',
            'nom'     => 'required|string|max:100',
            'type'    => 'required|in:cv,plan_localisation,cni,diplome,contrat,autre',
        ]);

        $employe = Employe::findOrFail($id);
        $file    = $request->file('fichier');
        $path    = $file->store("rh/documents/{$employe->matricule}", 'public');

        EmployeDocument::create([
            'employe_id'     => $employe->id,
            'nom'            => $request->nom,
            'type'           => $request->type,
            'fichier_path'   => $path,
            'fichier_nom'    => $file->getClientOriginalName(),
            'fichier_taille' => $file->getSize(),
            'description'    => $request->description,
        ]);

        return back()->with('success', 'Document ajouté');
    }

    public function downloadDocument($id, $docId)
    {
        $doc = EmployeDocument::where('employe_id', $id)->findOrFail($docId);
        return response()->download(storage_path('app/public/' . $doc->fichier_path), $doc->fichier_nom);
    }

    public function deleteDocument($docId)
    {
        $doc = EmployeDocument::findOrFail($docId);
        if (Storage::disk('public')->exists($doc->fichier_path)) {
            Storage::disk('public')->delete($doc->fichier_path);
        }
        $doc->delete();
        return back()->with('success', 'Document supprimé');
    }
}