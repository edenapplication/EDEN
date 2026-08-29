<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\Alerte;
use Illuminate\Http\Request;

class AlerteController extends Controller
{
    /**
     * Centre de notifications
     */
    public function index(Request $request)
    {
        $query = Alerte::with(['employe']);

        // Filtres
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('statut')) {
            $query->where('statut', $request->statut);
        }

        if ($request->filled('priorite')) {
            $query->where('priorite', $request->priorite);
        }

        // Tri
        $alertes = $query->orderByDesc('priorite')
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        // Statistiques
        $stats = [
            'non_lu' => Alerte::nonLu()->count(),
            'total' => Alerte::actives()->count(),
            'critique' => Alerte::priorite('critique')->nonLu()->count(),
        ];

        return view('rh.alertes.index', compact('alertes', 'stats'));
    }

    /**
     * Marquer une alerte comme lue
     */
    public function marquerLu($id)
    {
        $alerte = Alerte::findOrFail($id);
        $alerte->marquerLu();

        return back()->with('success', 'Alerte marquée comme lue.');
    }

    /**
     * Marquer une alerte comme traitée
     */
    public function marquerTraite($id)
    {
        $alerte = Alerte::findOrFail($id);
        $alerte->marquerTraite();

        return back()->with('success', 'Alerte marquée comme traitée.');
    }

    /**
     * Ignorer une alerte
     */
    public function marquerIgnore($id)
    {
        $alerte = Alerte::findOrFail($id);
        $alerte->marquerIgnore();

        return back()->with('success', 'Alerte ignorée.');
    }

    /**
     * Marquer toutes les alertes comme lues
     */
    public function toutMarquerLu()
    {
        Alerte::nonLu()->update([
            'statut' => 'lu',
            'date_lecture' => now(),
        ]);

        return back()->with('success', 'Toutes les alertes ont été marquées comme lues.');
    }

    /**
     * Supprimer une alerte
     */
    public function destroy($id)
    {
        $alerte = Alerte::findOrFail($id);
        $alerte->delete();

        return back()->with('success', 'Alerte supprimée.');
    }

    /**
     * Obtenir le nombre d'alertes non lues (AJAX)
     */
    public function countNonLu()
    {
        $count = Alerte::nonLu()->count();
        return response()->json(['count' => $count]);
    }
}