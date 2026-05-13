<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Retard;
use App\Models\RH\Employe;
use App\Models\RH\Direction;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class RetardController extends Controller
{
    public function index(Request $request)
    {
        $query = Retard::with('employe.direction.services', 'employe.service');

        if ($request->filled('employe_id'))
            $query->where('employe_id', $request->employe_id);
        if ($request->filled('direction_id'))
            $query->whereHas('employe', fn($q) => $q->where('direction_id', $request->direction_id));
        if ($request->filled('mois'))
            $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$request->mois]);

        $retards    = $query->orderByDesc('date')->get();
        $employes   = Employe::where('actif', true)->orderBy('nom')->get();
        $directions = Direction::orderBy('nom')->get();

        $parDirection = $retards->groupBy('employe.direction.nom')->map(fn($g) => [
            'nb'       => $g->count(),
            'employes' => $g->groupBy('employe_id')->map(fn($ge) => [
                'nom'     => $ge->first()->employe?->nom . ' ' . $ge->first()->employe?->prenom,
                'nb'      => $ge->count(),
                'service' => $ge->first()->employe?->service?->nom ?? '-',
            ])->sortByDesc('nb')->values(),
        ])->sortByDesc('nb');

        $parService = $retards->groupBy('employe.service.nom')->map(fn($g) => [
            'nb' => $g->count(),
        ])->sortByDesc('nb');

        return view('rh.retards.index', compact('retards','employes','directions','parDirection','parService'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:rh_employes,id',
            'date'       => 'required|date',
        ]);
        Retard::create($request->all());
        return back()->with('success', 'Retard enregistré');
    }

    public function update(Request $request, $id)
    {
        Retard::findOrFail($id)->update($request->all());
        return back()->with('success', 'Retard mis à jour');
    }

    public function destroy($id)
    {
        Retard::findOrFail($id)->delete();
        return redirect()->route('rh.retards.index')->with('success', 'Retard supprimé');
    }

    public function pdfListe(Request $request)
    {
        $query = Retard::with('employe.direction');
        if ($request->filled('employe_id'))
            $query->where('employe_id', $request->employe_id);
        if ($request->filled('direction_id'))
            $query->whereHas('employe', fn($q) => $q->where('direction_id', $request->direction_id));
        if ($request->filled('mois'))
            $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$request->mois]);

        $retards    = $query->orderByDesc('date')->get();
        $parDirection = $retards->groupBy('employe.direction.nom')->map(fn($g) => [
            'nb' => $g->count(),
        ]);

        $pdf = Pdf::loadView('rh.retards.pdf_liste', compact('retards', 'parDirection'))
                   ->setPaper('a4', 'landscape');
        return $pdf->download('retards_' . now()->format('Y-m-d') . '.pdf');
    }
}