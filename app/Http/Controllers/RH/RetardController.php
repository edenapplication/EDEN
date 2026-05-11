<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Retard;
use App\Models\RH\Employe;
use Illuminate\Http\Request;

class RetardController extends Controller
{
    public function index(Request $request)
    {
        $query = Retard::with('employe');
        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);
        if ($request->filled('mois'))
            $query->whereMonth('date', date('m', strtotime($request->mois.'-01')))
                  ->whereYear('date',  date('Y', strtotime($request->mois.'-01')));

        $retards  = $query->orderBy('date', 'desc')->paginate(30)->withQueryString();
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        $totalDeductions = Retard::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('montant_deduction');

        return view('rh.retards.index', compact('retards', 'employes', 'totalDeductions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'    => 'required|exists:rh_employes,id',
            'date'          => 'required|date',
            'minutes_retard'=> 'required|integer|min:1',
        ]);
        $retard = Retard::create($request->all());
        return response()->json(['success' => true, 'retard' => $retard->load('employe')]);
    }

    public function update(Request $request, $id)
    {
        Retard::findOrFail($id)->update($request->all());
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Retard::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}