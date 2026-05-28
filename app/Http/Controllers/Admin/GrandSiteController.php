<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrandSite;
use Illuminate\Http\Request;

class GrandSiteController extends Controller
{
    public function index()
    {
        $grandsites = GrandSite::withCount('sites')
    ->with(['sites.tfs.lots', 'sites.tfs.zoneGroupes'])
    ->get()
    ->map(function($gs) {
        $lots  = $gs->sites->flatMap(fn($s) => $s->tfs->flatMap(fn($tf) => $tf->lots));
        $zones = $gs->sites->flatMap(fn($s) => $s->tfs->flatMap(fn($tf) => $tf->zoneGroupes));
        $gs->stat_lots      = $lots->count();
        $gs->stat_zones     = $zones->count();
        $gs->stat_tfs       = $gs->sites->sum(fn($s) => $s->tfs->count());
        $gs->stat_eden      = $lots->where('origine','eden')->count();
        $gs->stat_famille   = $lots->where('origine','famille')->count();
        $gs->stat_superficie= $lots->sum('superficie');
        $total  = $gs->stat_lots + $gs->stat_zones;
        $actifs = $lots->whereIn('type',['implantation_prevue','deja_implante','dossier_technique','morcellement'])->count()
                + $zones->whereIn('type',['implantation_prevue','deja_implante','dossier_technique','morcellement'])->count();
        $gs->stat_activite  = $total > 0 ? round(($actifs / $total) * 100) : 0;
        return $gs;
    });
        return view('admin.grand_sites.index', compact('grandsites'));
    }

    public function create()
    {
        return view('admin.grand_sites.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        GrandSite::create($request->only('nom', 'description'));

        return redirect()->route('grand-sites.index')->with('success', 'Grand site créé');
    }

    public function edit($id)
    {
        $grandsite = GrandSite::findOrFail($id);
        return view('admin.grand_sites.edit', compact('grandsite'));
    }

    public function update(Request $request, $id)
    {
        $grandsite = GrandSite::findOrFail($id);

        $request->validate([
            'nom'         => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $grandsite->update($request->only('nom', 'description'));

        return redirect()->route('grand-sites.index')->with('success', 'Modifié');
    }

    public function destroy($id)
    {
        GrandSite::findOrFail($id)->delete();
        return back()->with('success', 'Supprimé');
    }
}