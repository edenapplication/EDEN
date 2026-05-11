<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Absence;
use App\Models\RH\Employe;
use Illuminate\Http\Request;

class AbsenceController extends Controller
{
    public function index(Request $request)
    {
        $query = Absence::with('employe.direction');

        if ($request->filled('employe_id'))
            $query->where('employe_id', $request->employe_id);

        if ($request->filled('type_absence'))
            $query->where('type_absence', $request->type_absence);

        if ($request->filled('statut'))
            $query->where('statut', $request->statut);

        if ($request->filled('mois'))
            $query->whereMonth('date_debut', date('m', strtotime($request->mois . '-01')))
                  ->whereYear('date_debut',  date('Y', strtotime($request->mois . '-01')));

        $absences = $query->orderBy('date_debut', 'desc')->paginate(20)->withQueryString();
        $employes = Employe::where('actif', true)->orderBy('nom')->get();

        return view('rh.absences.index', compact('absences', 'employes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'   => 'required|exists:rh_employes,id',
            'date_debut'   => 'required|date',
            'date_fin'     => 'required|date|after_or_equal:date_debut',
            'type_absence' => 'required',
        ]);

        $nbJours = \Carbon\Carbon::parse($request->date_debut)
                                 ->diffInDays(\Carbon\Carbon::parse($request->date_fin)) + 1;
        if ($request->type_journee === 'demi-journée') $nbJours = (int)ceil($nbJours / 2);

        $absence = Absence::create(array_merge($request->all(), [
            'nombre_jours' => $nbJours,
            'reference'    => 'ABS-' . str_pad(Absence::count() + 1, 4, '0', STR_PAD_LEFT),
        ]));

        return response()->json(['success' => true, 'absence' => $absence->load('employe')]);
    }

    public function update(Request $request, $id)
    {
        $absence = Absence::findOrFail($id);
        $absence->update($request->all());
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Absence::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }

    public function approuver($id)
    {
        $absence = Absence::with('employe')->findOrFail($id);
        $absence->update(['statut' => 'approuvé']);

        // Déduire du solde de congés si c'est un congé
        if ($absence->type_absence === 'Congés') {
            $absence->employe->increment('conges_pris', $absence->nombre_jours);
        }

        return response()->json(['success' => true]);
    }

    public function refuser($id)
    {
        Absence::findOrFail($id)->update(['statut' => 'refusé']);
        return response()->json(['success' => true]);
    }
}