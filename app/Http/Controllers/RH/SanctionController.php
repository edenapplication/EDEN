<?php
namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Sanction;
use App\Models\RH\Employe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class SanctionController extends Controller
{
    // ✅ Valeurs conformes SQLite — PAS d'accent
    const STATUTS = ['en_attente', 'valide', 'annule'];

    public function index(Request $request)
    {
        $query = Sanction::with('employe.direction');

        if ($request->filled('employe_id')) $query->where('employe_id', $request->employe_id);
        if ($request->filled('type'))       $query->where('type', $request->type);
        if ($request->filled('statut'))     $query->where('statut', $request->statut);
        if ($request->filled('mois')) {
            if (config('database.default') === 'sqlite') {
                $query->whereRaw("strftime('%Y-%m', date) = ?", [$request->mois]);
            } else {
                $query->whereRaw("DATE_FORMAT(date, '%Y-%m') = ?", [$request->mois]);
            }
        }

        $sanctions = $query->orderByDesc('date')->get();
        $employes  = Employe::where('actif', true)->orderBy('nom')->get();

        $moisCourant = now()->format('Y-m');
        $totalMois   = $sanctions
            ->filter(fn($s) => substr((string) $s->date, 0, 7) === $moisCourant)
            ->sum('montant');

        return view('rh.sanctions.index', compact('sanctions', 'employes', 'totalMois'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employe_id'  => 'required|exists:rh_employes,id',
            'type'        => 'required|in:avertissement,mise_a_pied,amende,autre',
            'date'        => 'required|date',
            'motif'       => 'required|string',
            'duree_jours' => 'nullable|integer|min:0',
            'montant'     => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        // ✅ DB directement pour éviter tout problème de cast
        DB::table('rh_sanctions')->insert([
            'employe_id'  => $request->employe_id,
            'type'        => $request->type,
            'date'        => $request->date,
            'motif'       => $request->motif,
            'duree_jours' => $request->duree_jours ?? null,
            'montant'     => $request->montant ?? 0,
            'description' => $request->description ?? null,
            'statut'      => 'en_attente', // ✅ sans accent
            'created_at'  => now()->toDateTimeString(),
            'updated_at'  => now()->toDateTimeString(),
        ]);

        return back()->with('success', 'Sanction enregistree');
    }

    public function update(Request $request, $id)
    {
        $sanction = Sanction::findOrFail($id);

        // ✅ Mapping des statuts avec et sans accent → version sans accent
        $mappingStatuts = [
            'valide'     => 'valide',
            'validé'     => 'valide',
            'annule'     => 'annule',
            'annulé'     => 'annule',
            'en_attente' => 'en_attente',
            'notifie'    => 'en_attente',
            'notifié'    => 'en_attente',
        ];

        $data = $request->only(['employe_id','type','date','motif','duree_jours','montant','description','statut']);

        // Normaliser le statut si présent
        if (isset($data['statut'])) {
            $statut = $mappingStatuts[$data['statut']] ?? $data['statut'];
            if (!in_array($statut, self::STATUTS)) {
                return back()->with('error', 'Statut invalide.');
            }
            $data['statut'] = $statut;
        }

        // ✅ DB directement — évite tout cast Eloquent + CHECK constraint SQLite
        DB::table('rh_sanctions')
            ->where('id', $id)
            ->update(array_merge($data, ['updated_at' => now()->toDateTimeString()]));

        return back()->with('success', 'Sanction mise a jour');
    }

    public function destroy($id)
    {
        Sanction::findOrFail($id)->delete();
        return redirect()->route('rh.sanctions.index')->with('success', 'Sanction supprimee');
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