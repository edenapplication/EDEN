<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Pret;
use App\Models\RH\Employe;
use Illuminate\Http\Request;

class PretController extends Controller
{
    public function index(Request $request)
    {
        $query = Pret::with('employe');
        if ($request->filled('statut'))  $query->where('statut', $request->statut);
        if ($request->filled('type'))    $query->where('type',   $request->type);
        if ($request->filled('employe')) $query->whereHas('employe', fn($q) => $q->where('nom', 'LIKE', "%{$request->employe}%"));

        $prets    = $query->orderBy('date_debut', 'desc')->paginate(20)->withQueryString();
        $employes = Employe::where('actif', true)->orderBy('nom')->get();
        $totalEnCours = Pret::where('statut', 'en_cours')->sum('montant');

        return view('rh.prets.index', compact('prets', 'employes', 'totalEnCours'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'  => 'required|exists:rh_employes,id',
            'type'        => 'required|in:pret,acompte',
            'montant'     => 'required|numeric|min:1',
            'date_debut'  => 'required|date',
        ]);

        $mensualite = null;
        if ($request->duree_mois > 0) {
            $mensualite = round($request->montant / $request->duree_mois, 2);
        }

        $pret = Pret::create(array_merge($request->all(), ['mensualite' => $mensualite]));
        return response()->json(['success' => true, 'pret' => $pret->load('employe')]);
    }

    public function update(Request $request, $id)
{
    $pret = Pret::findOrFail($id);

    // Si on envoie un montant de remboursement à ajouter
    if ($request->filled('montant_rembourse_ajout')) {
        $pret->montant_rembourse += $request->montant_rembourse_ajout;
        if ($pret->montant_rembourse >= $pret->montant) {
            $pret->montant_rembourse = $pret->montant;
            $pret->statut = 'remboursé';
        }
        $pret->save();
        return response()->json(['success' => true]);
    }

    $pret->update($request->all());
    return response()->json(['success' => true]);
}

    public function destroy($id)
    {
        Pret::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}