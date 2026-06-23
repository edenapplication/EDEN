<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Conge;
use App\Models\RH\Employe;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class CongeController extends Controller
{
    public function index(Request $request)
    {
        $query = Conge::with('employe.direction');

        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);
        if ($request->filled('statut'))     $query->where('statut', $request->statut);
        if ($request->filled('mois')) {
            $debut = Carbon::parse($request->mois)->startOfMonth();
            $fin   = Carbon::parse($request->mois)->endOfMonth();
            $query->where(function($q) use ($debut, $fin) {
                $q->whereBetween('date_debut', [$debut, $fin])
                  ->orWhereBetween('date_fin',  [$debut, $fin]);
            });
        }

        $conges   = $query->orderBy('date_debut', 'desc')->get();
        $employes = Employe::where('actif', true)->orderBy('nom')->get();

        $employes->each(function ($e) {

    $pris = Conge::where('employe_id', $e->id)
        ->whereYear('date_debut', now()->year)
        ->where('statut', '!=', 'annule')
        ->sum('nb_jours');

    $e->jours_restants = max(0, 15 - $pris);
});

        // Stats
        $stats = [
            'total'    => $conges->count(),
            'planifie' => $conges->where('statut','planifie')->count(),
            'en_cours' => $conges->where('statut','en_cours')->count(),
            'termine'  => $conges->where('statut','termine')->count(),
            'annule'   => $conges->where('statut','annule')->count(),
        ];
        $conges->each(function ($c) {

    $joursPris = Conge::where('employe_id', $c->employe_id)
        ->whereYear('date_debut', now()->year)
        ->where('statut', '!=', 'annule')
        ->sum('nb_jours');

    $c->employe->jours_pris = $joursPris;
    $c->employe->jours_restants = max(0, 15 - $joursPris);
});
        return view('rh.conges.index', compact('conges','employes','stats'));
    }

    public function store(Request $request)
    {
        $request->validate([
    'employe_id' => 'required|exists:rh_employes,id',
    'date_debut' => 'required|date',
    'nb_jours'   => 'required|integer|min:1|max:15',
    'motif'      => 'nullable|string|max:255',
    'notes'      => 'nullable|string',
]);

        $dateFin = Conge::calculerDateFin($request->date_debut,$request->nb_jours);
        $joursPris = Conge::where('employe_id', $request->employe_id)
    ->whereYear('date_debut', now()->year)
    ->where('statut', '!=', 'annule')
    ->sum('nb_jours');

$reste = 15 - $joursPris;

if ($request->nb_jours > $reste) {

    return back()->with(
        'error',
        "Cet employé ne possède plus que {$reste} jour(s) de congé."
    );
}

        Conge::create([
            'employe_id' => $request->employe_id,
            'date_debut' => $request->date_debut,
            'date_fin'   => $dateFin,
            'nb_jours' => $request->nb_jours,
            'motif'      => $request->motif,
            'statut'     => 'planifie',
            'notes'      => $request->notes,
        ]);

        return back()->with('success', 'Congé planifié avec succès.');
    }

    public function update(Request $request, $id)
    {
        $conge = Conge::findOrFail($id);

        $request->validate([
            'employe_id' => 'sometimes|exists:rh_employes,id',
            'date_debut' => 'sometimes|date',
            'nb_jours' => 'sometimes|integer|min:1|max:15',
            'statut'     => 'sometimes|in:planifie,en_cours,termine,annule',
            'motif'      => 'nullable|string|max:255',
            'notes'      => 'nullable|string',
        ]);

        $data = $request->only(['employe_id','motif','statut','notes']);

        if ($request->filled('date_debut')) {

    $nbJours = $request->nb_jours ?? $conge->nb_jours;

    $data['date_debut'] = $request->date_debut;
    $data['nb_jours']   = $nbJours;

    $data['date_fin'] = Conge::calculerDateFin(
        $request->date_debut,
        $nbJours
    );
}

        if ($request->filled('nb_jours')) {

    $joursPris = Conge::where('employe_id',
            $request->employe_id ?? $conge->employe_id)
        ->whereYear('date_debut', now()->year)
        ->where('statut', '!=', 'annule')
        ->where('id', '!=', $conge->id)
        ->sum('nb_jours');

    $reste = 15 - $joursPris;

    if ($request->nb_jours > $reste) {
        return back()->with(
            'error',
            "Cet employé ne possède plus que {$reste} jour(s) disponibles."
        );
    }
}

        $conge->update($data);

        return back()->with('success', 'Congé mis à jour.');
    }

    public function destroy($id)
    {
        Conge::findOrFail($id)->delete();
        return back()->with('success', 'Congé supprimé.');
    }

    // PDF d'un seul congé (attestation)
    public function pdf($id)
    {
        $conge = Conge::with('employe.direction')->findOrFail($id);
        $pdf   = Pdf::loadView('rh.conges.pdf_attestation', compact('conge'))
                    ->setPaper('a4', 'portrait');
        return $pdf->download("conge_{$conge->employe->matricule}_{$conge->date_debut->format('Y-m-d')}.pdf");
    }

    // PDF planning global
    public function planningPdf(Request $request)
    {
        $query = Conge::with('employe.direction');

        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);
        if ($request->filled('statut'))     $query->where('statut', $request->statut);
        if ($request->filled('mois')) {
            $debut = Carbon::parse($request->mois)->startOfMonth();
            $fin   = Carbon::parse($request->mois)->endOfMonth();
            $query->where(function($q) use ($debut, $fin) {
                $q->whereBetween('date_debut', [$debut, $fin])
                  ->orWhereBetween('date_fin',  [$debut, $fin]);
            });
        }

        $conges = $query->orderBy('date_debut')->get();
        $mois   = $request->mois ?? now()->format('Y-m');

        $pdf = Pdf::loadView('rh.conges.pdf_planning', compact('conges','mois'))
                  ->setPaper('a4', 'landscape');
        return $pdf->download("planning_conges_{$mois}.pdf");
    }
}