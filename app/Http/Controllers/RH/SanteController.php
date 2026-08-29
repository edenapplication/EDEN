<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\VisiteMedicale;
use App\Models\RH\AccidentTravail;
use App\Models\RH\TrousseSecours;
use App\Models\RH\Employe;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class SanteController extends Controller
{
    /**
     * Dashboard Santé & Sécurité
     */
    public function index()
    {
        // Visites médicales
        $stats = [
            'visites_total' => VisiteMedicale::count(),
            'visites_planifiees' => VisiteMedicale::statut('planifie')->count(),
            'visites_effectuees' => VisiteMedicale::statut('effectue')->count(),
            'visites_expirees' => VisiteMedicale::expirees()->count(),
            'visites_a_expirer' => VisiteMedicale::aExpirer(30)->count(),
        ];

        // Accidents
        $stats['accidents_total'] = AccidentTravail::count();
        $stats['accidents_en_cours'] = AccidentTravail::enCours()->count();

        // Trousse de secours
        $trousses = TrousseSecours::all();
        $stats['trousses_ok'] = TrousseSecours::statut('ok')->count();
        $stats['trousses_alerte'] = TrousseSecours::statut('alerte')->count();

        // Dernières visites
        $dernieresVisites = VisiteMedicale::with(['employe'])
            ->orderByDesc('date_visite')
            ->limit(5)
            ->get();

        // Derniers accidents
        $derniersAccidents = AccidentTravail::with(['employe'])
            ->orderByDesc('date_accident')
            ->limit(5)
            ->get();

        return view('rh.sante.index', compact('stats', 'dernieresVisites', 'derniersAccidents', 'trousses'));
    }

    /**
     * Liste des visites médicales
     */
    public function visites(Request $request)
    {
        $query = VisiteMedicale::with(['employe']);

        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $visites = $query->orderByDesc('date_visite')->paginate(20)->withQueryString();

        $employes = Employe::where('actif', true)->orderBy('nom')->get();

        return view('rh.sante.visites', compact('visites', 'employes'));
    }

    /**
     * Formulaire de création d'une visite
     */
    public function createVisite()
    {
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        return view('rh.sante.create_visite', compact('employes'));
    }

    /**
     * Enregistrer une visite
     */
    public function storeVisite(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:rh_employes,id',
            'type' => 'required|in:embauche,periodique,reprise,accident',
            'date_visite' => 'required|date',
            'aptitude' => 'required|in:apte,apte_avec_restriction,inapte',
            'prochaine_visite' => 'nullable|date|after:date_visite',
            'certificat' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $data = $request->all();

        if ($request->hasFile('certificat')) {
            $data['certificat_path'] = $request->file('certificat')->store('rh/sante/certificats', 'public');
            $data['certificat_fourni'] = true;
        }

        $data['statut'] = 'effectue';

        VisiteMedicale::create($data);

        return redirect()->route('rh.sante.visites')
            ->with('success', 'Visite médicale enregistrée avec succès.');
    }

    /**
     * Afficher une visite
     */
    public function showVisite($id)
    {
        $visite = VisiteMedicale::with(['employe'])->findOrFail($id);
        return view('rh.sante.show_visite', compact('visite'));
    }

    /**
     * Modifier une visite
     */
    public function editVisite($id)
    {
        $visite = VisiteMedicale::findOrFail($id);
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        return view('rh.sante.edit_visite', compact('visite', 'employes'));
    }

    /**
     * Mettre à jour une visite
     */
    public function updateVisite(Request $request, $id)
    {
        $visite = VisiteMedicale::findOrFail($id);

        $request->validate([
            'employe_id' => 'required|exists:rh_employes,id',
            'type' => 'required|in:embauche,periodique,reprise,accident',
            'date_visite' => 'required|date',
            'aptitude' => 'required|in:apte,apte_avec_restriction,inapte',
            'prochaine_visite' => 'nullable|date|after:date_visite',
            'certificat' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $data = $request->all();

        if ($request->hasFile('certificat')) {
            if ($visite->certificat_path) {
                Storage::disk('public')->delete($visite->certificat_path);
            }
            $data['certificat_path'] = $request->file('certificat')->store('rh/sante/certificats', 'public');
            $data['certificat_fourni'] = true;
        }

        $visite->update($data);

        return redirect()->route('rh.sante.visites')
            ->with('success', 'Visite médicale mise à jour.');
    }

    /**
     * Supprimer une visite
     */
    public function destroyVisite($id)
    {
        $visite = VisiteMedicale::findOrFail($id);
        if ($visite->certificat_path) {
            Storage::disk('public')->delete($visite->certificat_path);
        }
        $visite->delete();

        return redirect()->route('rh.sante.visites')
            ->with('success', 'Visite supprimée.');
    }

    /**
     * Liste des accidents
     */
    public function accidents(Request $request)
    {
        $query = AccidentTravail::with(['employe']);

        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }
        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        $accidents = $query->orderByDesc('date_accident')->paginate(20)->withQueryString();

        $employes = Employe::where('actif', true)->orderBy('nom')->get();

        return view('rh.sante.accidents', compact('accidents', 'employes'));
    }

    /**
     * Formulaire de création d'un accident
     */
    public function createAccident()
    {
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        return view('rh.sante.create_accident', compact('employes'));
    }

    /**
     * Enregistrer un accident
     */
    public function storeAccident(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:rh_employes,id',
            'date_accident' => 'required|date',
            'lieu' => 'required|string|max:255',
            'description' => 'required|string',
            'nature_blessures' => 'nullable|string|max:255',
            'temoin1_nom' => 'nullable|string|max:150',
            'temoin2_nom' => 'nullable|string|max:150',
            'rapport' => 'nullable|file|max:5120|mimes:pdf,doc,docx',
            'constat' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $data = $request->all();
        $data['declare_par'] = auth()->id();
        $data['statut'] = 'declare';

        if ($request->hasFile('rapport')) {
            $data['rapport_path'] = $request->file('rapport')->store('rh/sante/rapports', 'public');
        }

        if ($request->hasFile('constat')) {
            $data['constat_path'] = $request->file('constat')->store('rh/sante/constats', 'public');
        }

        AccidentTravail::create($data);

        return redirect()->route('rh.sante.accidents')
            ->with('success', 'Accident déclaré avec succès.');
    }

    /**
     * Afficher un accident
     */
    public function showAccident($id)
    {
        $accident = AccidentTravail::with(['employe', 'declarePar'])->findOrFail($id);
        return view('rh.sante.show_accident', compact('accident'));
    }

    /**
     * Modifier un accident
     */
    public function editAccident($id)
    {
        $accident = AccidentTravail::findOrFail($id);
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        return view('rh.sante.edit_accident', compact('accident', 'employes'));
    }

    /**
     * Mettre à jour un accident
     */
    public function updateAccident(Request $request, $id)
    {
        $accident = AccidentTravail::findOrFail($id);

        $request->validate([
            'employe_id' => 'required|exists:rh_employes,id',
            'date_accident' => 'required|date',
            'lieu' => 'required|string|max:255',
            'description' => 'required|string',
            'statut' => 'required|in:declare,en_cours,cloture,annule',
            'date_retour' => 'nullable|date|after:date_accident',
            'rapport' => 'nullable|file|max:5120|mimes:pdf,doc,docx',
            'constat' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png',
        ]);

        $data = $request->all();

        if ($request->hasFile('rapport')) {
            if ($accident->rapport_path) {
                Storage::disk('public')->delete($accident->rapport_path);
            }
            $data['rapport_path'] = $request->file('rapport')->store('rh/sante/rapports', 'public');
        }

        if ($request->hasFile('constat')) {
            if ($accident->constat_path) {
                Storage::disk('public')->delete($accident->constat_path);
            }
            $data['constat_path'] = $request->file('constat')->store('rh/sante/constats', 'public');
        }

        $accident->update($data);

        return redirect()->route('rh.sante.accidents')
            ->with('success', 'Accident mis à jour.');
    }

    /**
     * Supprimer un accident
     */
    public function destroyAccident($id)
    {
        $accident = AccidentTravail::findOrFail($id);
        
        if ($accident->rapport_path) {
            Storage::disk('public')->delete($accident->rapport_path);
        }
        if ($accident->constat_path) {
            Storage::disk('public')->delete($accident->constat_path);
        }
        if ($accident->certificat_medical_path) {
            Storage::disk('public')->delete($accident->certificat_medical_path);
        }
        
        $accident->delete();

        return redirect()->route('rh.sante.accidents')
            ->with('success', 'Accident supprimé.');
    }

    /**
     * Gestion des trousses de secours
     */
    public function trousses()
    {
        $trousses = TrousseSecours::orderBy('localisation')->get();
        return view('rh.sante.trousses', compact('trousses'));
    }

    /**
     * Ajouter une trousse
     */
    public function storeTrousse(Request $request)
    {
        $request->validate([
            'localisation' => 'required|string|max:255',
            'date_verification' => 'required|date',
            'prochaine_verification' => 'nullable|date|after:date_verification',
            'contenu' => 'nullable|string',
            'statut' => 'required|in:ok,alerte,vide',
        ]);

        TrousseSecours::create($request->all());

        return back()->with('success', 'Trousse de secours ajoutée.');
    }

    /**
     * Mettre à jour une trousse
     */
    public function updateTrousse(Request $request, $id)
    {
        $trousse = TrousseSecours::findOrFail($id);

        $request->validate([
            'localisation' => 'required|string|max:255',
            'date_verification' => 'required|date',
            'prochaine_verification' => 'nullable|date|after:date_verification',
            'contenu' => 'nullable|string',
            'statut' => 'required|in:ok,alerte,vide',
        ]);

        $trousse->update($request->all());

        return back()->with('success', 'Trousse de secours mise à jour.');
    }

    /**
     * Supprimer une trousse
     */
    public function destroyTrousse($id)
    {
        TrousseSecours::findOrFail($id)->delete();
        return back()->with('success', 'Trousse de secours supprimée.');
    }

    /**
     * PDF - Liste des visites
     */
    public function pdfVisites(Request $request)
    {
        $query = VisiteMedicale::with(['employe']);

        if ($request->filled('employe_id')) {
            $query->where('employe_id', $request->employe_id);
        }

        $visites = $query->orderByDesc('date_visite')->get();

        $pdf = Pdf::loadView('rh.sante.pdf_visites', compact('visites'))
            ->setPaper('a4', 'landscape');

        return $pdf->download('visites_medicales_' . now()->format('Y-m-d') . '.pdf');
    }
}