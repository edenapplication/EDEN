<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrandSite;
use App\Models\Site;
use App\Models\Tf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrandSiteController extends Controller
{
    // ============================================================
    // 📋 LISTE
    // ============================================================
    public function index()
    {
        $grandsites = GrandSite::withCount('sites')
            ->with(['sites.tfs.lots', 'sites.tfs.zoneGroupes'])
            ->get()
            ->map(function($gs) {
                $lots  = $gs->sites->flatMap(fn($s) => $s->tfs->flatMap(fn($tf) => $tf->lots));
                $zones = $gs->sites->flatMap(fn($s) => $s->tfs->flatMap(fn($tf) => $tf->zoneGroupes));

                $gs->stat_lots       = $lots->count();
                $gs->stat_zones      = $zones->count();
                $gs->stat_tfs        = $gs->sites->sum(fn($s) => $s->tfs->count());
                $gs->stat_eden       = $lots->where('origine','eden')->count();
                $gs->stat_famille    = $lots->where('origine','famille')->count();
                $gs->stat_superficie = $lots->sum('superficie');

                $total  = $gs->stat_lots + $gs->stat_zones;
                $actifs = $lots->whereIn('type',['implantation_prevue','deja_implante','dossier_technique','morcellement'])->count()
                        + $zones->whereIn('type',['implantation_prevue','deja_implante','dossier_technique','morcellement'])->count();

                $gs->stat_activite = $total > 0 ? round(($actifs / $total) * 100) : 0;

                return $gs;
            });

        return view('admin.grand_sites.index', compact('grandsites'));
    }

    // ============================================================
    // ➕ CRÉER (classique — un seul)
    // ============================================================
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

    // ============================================================
    // ✏️ MODIFIER
    // ============================================================
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

    // ============================================================
    // 🗑️ SUPPRIMER
    // ============================================================
    public function destroy($id)
    {
        GrandSite::findOrFail($id)->delete();
        return back()->with('success', 'Supprimé');
    }

  // ============================================================
// 🏢 CRÉER DES GRANDS SITES (plusieurs à la fois)
// ============================================================

public function createGrandSites()
{
    return view('admin.creation.grands-sites');
}

public function storeGrandSites(Request $request)
{
    $request->validate([
        'noms' => 'required|string',
    ]);

    $noms = array_filter(array_map('trim', explode(';', $request->noms)));

    if (empty($noms)) {
        return back()->withErrors('❌ Saisissez au moins un nom.');
    }

    $created = 0;
    $skipped = 0;

    DB::beginTransaction();

    try {
        foreach ($noms as $nom) {
            if (GrandSite::where('nom', $nom)->exists()) {
                $skipped++;
                continue;
            }
            GrandSite::create(['nom' => $nom]);
            $created++;
        }

        DB::commit();

        $msg = "✅ {$created} Grand Site(s) créé(s)";
        if ($skipped) $msg .= " — {$skipped} ignoré(s) (déjà existant)";

        return redirect()
            ->route('affectations.index')
            ->with('success', $msg);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->withErrors('❌ Erreur : ' . $e->getMessage());
    }
}

// ============================================================
// 📍 CRÉER DES SITES (plusieurs à la fois)
// ============================================================

public function createSites()
{
    $grandSites = GrandSite::orderBy('nom')->get();
    return view('admin.creation.sites', compact('grandSites'));
}

public function storeSites(Request $request)
{
    $request->validate([
        'grand_site_id' => 'required|exists:grand_sites,id',
        'noms'          => 'required|string',
    ]);

    $grandSite = GrandSite::findOrFail($request->grand_site_id);
    $noms = array_filter(array_map('trim', explode(';', $request->noms)));

    if (empty($noms)) {
        return back()->withErrors('❌ Saisissez au moins un nom.');
    }

    $created = 0;
    $skipped = 0;

    DB::beginTransaction();

    try {
        foreach ($noms as $nom) {
            $exists = Site::where('grand_site_id', $grandSite->id)
                ->where('name', $nom)
                ->exists();

            if ($exists) { $skipped++; continue; }

            Site::create([
                'grand_site_id' => $grandSite->id,
                'name'          => $nom,
                'is_active'     => true,
            ]);
            $created++;
        }

        DB::commit();

        $msg = "✅ {$created} Site(s) créé(s) pour « {$grandSite->nom} »";
        if ($skipped) $msg .= " — {$skipped} ignoré(s)";

        return redirect()
            ->route('affectations.index')
            ->with('success', $msg);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->withErrors('❌ Erreur : ' . $e->getMessage());
    }
}

// ============================================================
// 📄 CRÉER DES TFs (plusieurs à la fois)
// ============================================================

public function createTfs()
{
    $grandSites = GrandSite::orderBy('nom')->get();
    return view('admin.creation.tfs', compact('grandSites'));
}

public function storeTfs(Request $request)
{
    $request->validate([
        'site_id' => 'required|exists:sites,id',
        'titres'  => 'required|string',
    ]);

    $site = Site::findOrFail($request->site_id);
    $titres = array_filter(array_map('trim', explode(';', $request->titres)));

    if (empty($titres)) {
        return back()->withErrors('❌ Saisissez au moins un titre.');
    }

    $created = 0;
    $skipped = 0;

    DB::beginTransaction();

    try {
        foreach ($titres as $titre) {
            $exists = Tf::where('site_id', $site->id)
                ->where('title', $titre)
                ->exists();

            if ($exists) { $skipped++; continue; }

            Tf::create([
                'site_id'     => $site->id,
                'svg_zone_id' => 'tf_auto_' . time() . '_' . rand(1000, 9999) . '_' . uniqid(),
                'title'       => $titre,
                'color'       => sprintf('#%06X', mt_rand(0, 0xFFFFFF)),
                'status'      => 'available',
                'is_active'   => true,
            ]);
            $created++;
        }

        DB::commit();

        $msg = "🎉 {$created} TF(s) créé(s) pour « {$site->name} »";
        if ($skipped) $msg .= " — {$skipped} ignoré(s)";

        return redirect()
            ->route('affectations.index')
            ->with('success', $msg);

    } catch (\Exception $e) {
        DB::rollBack();
        return back()->withInput()->withErrors('❌ Erreur : ' . $e->getMessage());
    }
}

// ============================================================
// 🔄 API : Sites d'un Grand Site
// ============================================================

public function apiSitesDuGrandSite(GrandSite $grandSite)
{
    return response()->json(
        Site::where('grand_site_id', $grandSite->id)
            ->orderBy('name')
            ->get(['id', 'name'])
    );
}

}