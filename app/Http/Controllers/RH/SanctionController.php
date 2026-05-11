<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Sanction;
use App\Models\RH\Employe;
use Illuminate\Http\Request;

class SanctionController extends Controller
{
    public function index(Request $request)
    {
        $query = Sanction::with('employe');
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('mois'))
            $query->whereMonth('date', date('m', strtotime($request->mois.'-01')))
                  ->whereYear('date',  date('Y', strtotime($request->mois.'-01')));

        $sanctions = $query->orderBy('date', 'desc')->paginate(20)->withQueryString();
        $employes  = Employe::where('actif', true)->orderBy('nom')->get();
        $totalMois = Sanction::whereMonth('date', now()->month)->whereYear('date', now()->year)->sum('montant');

        return view('rh.sanctions.index', compact('sanctions', 'employes', 'totalMois'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:rh_employes,id',
            'date'       => 'required|date',
            'motif'      => 'required|string',
            'type'       => 'required',
        ]);
        $sanction = Sanction::create($request->all());
        return response()->json(['success' => true, 'sanction' => $sanction->load('employe')]);
    }

    public function update(Request $request, $id)
    {
        Sanction::findOrFail($id)->update($request->all());
        return response()->json(['success' => true]);
    }

    public function destroy($id)
    {
        Sanction::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}