<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Pret;
use App\Models\RH\Employe;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PretController extends Controller
{
    // ✅ Valeurs statut conformes SQLite (sans accent, sans caractères spéciaux)
    const STATUTS = ['en_cours', 'rembourse', 'annule'];

    public function index(Request $request)
    {
        $query = Pret::with('employe.direction');

        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);
        if ($request->filled('statut'))     $query->where('statut', $request->statut);
        if ($request->filled('type'))       $query->where('type', $request->type);

        $prets    = $query->orderByDesc('date_debut')->get();
        $employes = Employe::where('actif', true)->orderBy('nom')->get();

        return view('rh.prets.index', compact('prets', 'employes'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id' => 'required|exists:rh_employes,id',
            'type'       => 'required|in:acompte,pret',
            'montant'    => 'required|numeric|min:1',
            'date_debut' => 'required|date',
            'duree_mois' => 'nullable|integer|min:1',
            'motif'      => 'nullable|string|max:255',
        ]);

        $mensualite    = null;
        $dateFinPrevue = null;

        if ($request->type === 'pret' && $request->duree_mois > 0) {
            $mensualite    = round($request->montant / $request->duree_mois, 0);
            $dateFinPrevue = Carbon::parse($request->date_debut)
                ->addMonths((int) $request->duree_mois)->format('Y-m-d');
        } elseif ($request->type === 'acompte') {
            $mensualite    = $request->montant;
            $dateFinPrevue = Carbon::parse($request->date_debut)->addMonth()->format('Y-m-d');
        }

        Pret::create([
            'employe_id'        => $request->employe_id,
            'type'              => $request->type,
            'montant'           => $request->montant,
            'montant_rembourse' => 0,
            'duree_mois'        => $request->duree_mois,
            'mensualite'        => $mensualite,
            'date_debut'        => $request->date_debut,
            'date_fin_prevue'   => $dateFinPrevue,
            'motif'             => $request->motif,
            'statut'            => 'en_cours', // ✅ sans accent
        ]);

        return back()->with('success', 'Pret/acompte enregistre');
    }

    public function update(Request $request, $id)
    {
        $pret = Pret::findOrFail($id);

        // ✅ Remboursement partiel
        if ($request->filled('montant_rembourse_ajout')) {
            $ajout = floatval($request->montant_rembourse_ajout);
            if ($ajout <= 0) return back()->with('error', 'Montant invalide.');

            $pret->montant_rembourse += $ajout;

            if ($pret->montant_rembourse >= $pret->montant) {
                $pret->montant_rembourse = $pret->montant;
                $pret->statut = 'rembourse'; // ✅ sans accent
            }

            $pret->save();
            return back()->with('success', 'Remboursement enregistre');
        }

        // ✅ Changement de statut
        if ($request->filled('statut')) {
            $statut = $request->statut;

            // ✅ Normaliser : convertir les valeurs avec accents si elles arrivent
            $mapping = [
                'remboursé' => 'rembourse',
                'rembourse' => 'rembourse',
                'annulé'    => 'annule',
                'annule'    => 'annule',
                'en_cours'  => 'en_cours',
            ];
            $statut = $mapping[$statut] ?? $statut;

            if (!in_array($statut, self::STATUTS)) {
                return back()->with('error', 'Statut invalide : ' . $statut);
            }

            // ✅ Utiliser DB directement pour éviter tout cast Eloquent
            \Illuminate\Support\Facades\DB::table('rh_prets')
                ->where('id', $id)
                ->update([
                    'statut'     => $statut,
                    'updated_at' => now()->toDateTimeString(),
                ]);

            return back()->with('success', 'Statut mis a jour');
        }

        return back()->with('error', 'Aucune donnee a mettre a jour.');
    }

    public function destroy($id)
    {
        Pret::findOrFail($id)->delete();
        return back()->with('success', 'Pret supprime');
    }
}