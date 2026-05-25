<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Sanction;
use App\Models\RH\Employe;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class SanctionController extends Controller
{
    public function index(Request $request)
    {
        $query = Sanction::with('employe.direction');

        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);
        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('statut'))     $query->where('statut', $request->statut);
        if ($request->filled('mois'))
            $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$request->mois]);

        // ✅ get() et non paginate() pour éviter l'erreur ->links()
        $sanctions = $query->orderByDesc('date')->get();
        $employes  = Employe::where('actif', true)->orderBy('nom')->get();

        $moisCourant = now()->format('Y-m');
        $totalMois   = $sanctions
            ->filter(fn($s) => substr((string)$s->date, 0, 7) === $moisCourant)
            ->sum('montant');

        return view('rh.sanctions.index', compact('sanctions', 'employes', 'totalMois'));
    }

    public function store(Request $request)
{
    $request->validate([
        'employe_id' => 'required|exists:rh_employes,id',
        'type'       => 'required|in:avertissement,mise_a_pied,amende,autre',
        'date'       => 'required|date',
        'motif'      => 'required|string',
        'statut'     => 'nullable|in:en_attente,valide,annule',
    ]);

    Sanction::create([
        'employe_id'  => $request->employe_id,
        'type'        => $request->type,
        'date'        => $request->date,
        'motif'       => $request->motif,
        'duree_jours' => $request->duree_jours,
        'montant'     => $request->montant ?? 0,
        'description' => $request->description,
        'statut'      => $request->statut ?? 'en_attente',
    ]);

    return back()->with('success', 'Sanction enregistrée');
}

    public function update(Request $request, $id)
{
    $sanction = Sanction::findOrFail($id);

    $request->validate([
        'employe_id' => 'sometimes|exists:rh_employes,id',
        'type'       => 'sometimes|in:avertissement,mise_a_pied,amende,autre',
        'date'       => 'sometimes|date',
        'motif'      => 'sometimes|string',
        'statut'     => 'sometimes|in:en_attente,valide,annule',
        'duree_jours'=> 'nullable|integer',
        'montant'    => 'nullable|numeric',
        'description'=> 'nullable|string',
    ]);

    $sanction->update($request->only([
        'employe_id',
        'type',
        'date',
        'motif',
        'statut',
        'duree_jours',
        'montant',
        'description'
    ]));

    return back()->with('success', 'Sanction mise à jour');
}
    public function destroy($id)
    {
        Sanction::findOrFail($id)->delete();
        return redirect()->route('rh.sanctions.index')->with('success', 'Sanction supprimée');
    }

    public function pdfListe(Request $request)
    {
        $query = Sanction::with('employe.direction');
        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);
        if ($request->filled('type'))       $query->where('type', $request->type);
        $sanctions = $query->orderByDesc('date')->get();
        $pdf = Pdf::loadView('rh.sanctions.pdf_liste', compact('sanctions'))
                   ->setPaper('a4', 'landscape');
        return $pdf->download('sanctions_' . now()->format('Y-m-d') . '.pdf');
    }
}