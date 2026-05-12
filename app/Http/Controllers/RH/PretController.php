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
        $query = Pret::with('employe.direction');

        if ($request->filled('employe_id'))
            $query->where('employe_id', $request->employe_id);
        if ($request->filled('statut'))
            $query->where('statut', $request->statut);
        if ($request->filled('type'))
            $query->where('type', $request->type);

        $prets    = $query->orderByDesc('date_debut')->get();
        $employes = Employe::where('actif', true)->orderBy('nom')->get();

        return view('rh.prets.index', compact('prets', 'employes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'    => 'required|exists:rh_employes,id',
            'type'          => 'required|in:acompte,pret',
            'montant'       => 'required|numeric|min:1',
            'date_demande'  => 'required|date',
            'duree_mois'    => 'nullable|integer|min:1',
            'motif'         => 'nullable|string|max:255',
        ]);

        $mensualite = null;
        if ($request->type === 'pret' && $request->duree_mois > 0) {
            $mensualite = round($request->montant / $request->duree_mois, 0);
        } elseif ($request->type === 'acompte') {
            $mensualite = $request->montant; // remboursé en une fois
        }

        Pret::create([
            'employe_id'       => $request->employe_id,
            'type'             => $request->type,
            'montant'          => $request->montant,
            'montant_rembourse'=> 0,
            'duree_mois'       => $request->duree_mois,
            'mensualite'       => $mensualite,
            'date_demande'     => $request->date_demande,
            'motif'            => $request->motif,
            'statut'           => 'en_cours',
        ]);

        return back()->with('success', 'Prêt/acompte enregistré');
    }

    public function update(Request $request, $id)
    {
        $pret = Pret::findOrFail($id);

        // ✅ Remboursement
        if ($request->filled('montant_rembourse_ajout')) {
            $ajout = floatval($request->montant_rembourse_ajout);
            if ($ajout <= 0) return back()->with('error', 'Montant invalide.');

            $pret->montant_rembourse += $ajout;

            if ($pret->montant_rembourse >= $pret->montant) {
                $pret->montant_rembourse = $pret->montant;
                $pret->statut           = 'rembourse';
            }

            $pret->save();
            return back()->with('success', 'Remboursement enregistré');
        }

        // ✅ Modification du statut
        if ($request->filled('statut')) {
            $pret->update(['statut' => $request->statut]);
            return back()->with('success', 'Statut mis à jour');
        }

        return back()->with('error', 'Aucune donnée à mettre à jour.');
    }

    public function destroy($id)
    {
        Pret::findOrFail($id)->delete();
        return back()->with('success', 'Prêt supprimé');
    }
}