<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Site;
use App\Models\GrandSite;

class SiteController extends Controller
{
    public function index($grandSiteId)
{
    $grandsite = GrandSite::findOrFail($grandSiteId);

    $sites = Site::where('grand_site_id', $grandSiteId)
        ->with(['tfs.lots', 'tfs.zoneGroupes'])
        ->get()
        ->map(function($site) {
            $lots  = $site->tfs->flatMap(fn($tf) => $tf->lots);
            $zones = $site->tfs->flatMap(fn($tf) => $tf->zoneGroupes);

            $site->stat_tfs         = $site->tfs->count();
            $site->stat_lots        = $lots->count();
            $site->stat_zones       = $zones->count();
            $site->stat_eden        = $lots->where('origine','eden')->count();
            $site->stat_famille     = $lots->where('origine','famille')->count();
            $site->stat_implant     = $lots->where('type','implantation_prevue')->count() + $zones->where('type','implantation_prevue')->count();
            $site->stat_implante    = $lots->where('type','deja_implante')->count()       + $zones->where('type','deja_implante')->count();
            $site->stat_dossier     = $lots->where('type','dossier_technique')->count()   + $zones->where('type','dossier_technique')->count();
            $site->stat_morcel      = $lots->where('type','morcellement')->count()        + $zones->where('type','morcellement')->count();
            $total  = $site->stat_lots + $site->stat_zones;
            $actifs = $lots->whereIn('type',['implantation_prevue','deja_implante','dossier_technique','morcellement'])->count()
                    + $zones->whereIn('type',['implantation_prevue','deja_implante','dossier_technique','morcellement'])->count();
            $site->stat_activite    = $total > 0 ? round(($actifs / $total) * 100) : 0;
            $site->stat_superficie  = $lots->sum('superficie');
            return $site;
        });

    return view('admin.sites.index', compact('grandsite', 'sites'));
}

    public function create($grand_site_id)
    {
        $grandsite = GrandSite::findOrFail($grand_site_id);
        return view('admin.sites.create', compact('grandsite'));
    }

    public function store(Request $request, $grand_site_id)
    {
        $request->validate([
            'name'        => 'required|string',
            'description' => 'nullable|string',
            'svg'         => 'nullable|file',
        ]);

        $svgPath = null;

        if ($request->hasFile('svg') && $request->file('svg')->isValid()) {
            $file        = $request->file('svg');
            $filename    = time() . '_' . $file->getClientOriginalName();
            $destination = storage_path('app/public/maps');
            if (!file_exists($destination)) mkdir($destination, 0777, true);
            $file->move($destination, $filename);
            $svgPath = 'storage/maps/' . $filename;
        }

        Site::create([
            'grand_site_id' => $grand_site_id,
            'name'          => $request->name,
            'description'   => $request->description,
            'svg_path'      => $svgPath,
            'is_active'     => true,
        ]);

        return redirect()->route('sites.index', $grand_site_id)->with('success', 'Site ajouté');
    }

   public function show($siteId)
{
    $site = \App\Models\Site::with('grandSite')->findOrFail($siteId);
    $tfs  = \App\Models\Tf::where('site_id', $siteId)->get();
    $tfIds = $tfs->pluck('id');

    // Lots du site
    $lots = \App\Models\Lot::whereIn('tf_id', $tfIds)->get();

    // Zones groupes du site
    $zones = \App\Models\ZoneGroupe::whereIn('tf_id', $tfIds)->get();

    // Stats lots
    $stats = [
        'nb_tfs'                => $tfs->count(),
        'nb_lots'               => $lots->count(),
        'nb_zones'              => $zones->count(),
        'famille'               => $lots->where('origine', 'famille')->count(),
        'eden'                  => $lots->where('origine', 'eden')->count(),
        'implantation_prevue'   => $lots->where('type', 'implantation_prevue')->count()
                                 + $zones->where('type', 'implantation_prevue')->count(),
        'deja_implante'         => $lots->where('type', 'deja_implante')->count()
                                 + $zones->where('type', 'deja_implante')->count(),
        'dossier_technique'     => $lots->where('type', 'dossier_technique')->count()
                                 + $zones->where('type', 'dossier_technique')->count(),
        'morcellement'          => $lots->where('type', 'morcellement')->count()
                                 + $zones->where('type', 'morcellement')->count(),
        'sup_totale'            => $lots->sum('superficie') + $zones->sum('superficie_totale'),
        'sup_famille'           => $lots->where('origine', 'famille')->sum('superficie'),
        'sup_eden'              => $lots->where('origine', 'eden')->sum('superficie'),
        'sup_implantation'      => $lots->where('type', 'implantation_prevue')->sum('superficie')
                                 + $zones->where('type', 'implantation_prevue')->sum('superficie_totale'),
        'sup_implante'          => $lots->where('type', 'deja_implante')->sum('superficie')
                                 + $zones->where('type', 'deja_implante')->sum('superficie_totale'),
        'sup_dossier'           => $lots->where('type', 'dossier_technique')->sum('superficie')
                                 + $zones->where('type', 'dossier_technique')->sum('superficie_totale'),
        'sup_morcellement'      => $lots->where('type', 'morcellement')->sum('superficie')
                                 + $zones->where('type', 'morcellement')->sum('superficie_totale'),
    ];

    $totalActif  = $stats['implantation_prevue'] + $stats['deja_implante']
                 + $stats['dossier_technique']   + $stats['morcellement'];
    $totalGeneral = $stats['nb_lots'] + $stats['nb_zones'];
    $stats['activite_pct'] = $totalGeneral > 0 ? round(($totalActif / $totalGeneral) * 100) : 0;

    // Stats par TF
    $statsTf = $tfs->map(function($tf) use ($lots, $zones) {
    $lotsT  = $lots->where('tf_id', $tf->id);
    $zonesT = $zones->where('tf_id', $tf->id);

    // ✅ Nombre de blocs = lettres uniques du code de lot (A, B, C...)
    $blocs = $lotsT->map(fn($l) => strtoupper(substr($l->code, 0, 1)))
                   ->unique()->count();

    $actif  = $lotsT->whereIn('type', ['implantation_prevue','deja_implante','dossier_technique','morcellement'])->count()
            + $zonesT->whereIn('type', ['implantation_prevue','deja_implante','dossier_technique','morcellement'])->count();
    $total  = $lotsT->count() + $zonesT->count();

    return [
        'tf'                  => $tf,
        'lots'                => $lotsT->count(),
        'zones'               => $zonesT->count(),
        'blocs'               => $blocs,
        'eden'                => $lotsT->where('origine', 'eden')->count(),
        'famille'             => $lotsT->where('origine', 'famille')->count(),
        'actif_pct'           => $total > 0 ? round(($actif / $total) * 100) : 0,
        'implantation_prevue' => $lotsT->where('type','implantation_prevue')->count() + $zonesT->where('type','implantation_prevue')->count(),
        'deja_implante'       => $lotsT->where('type','deja_implante')->count()       + $zonesT->where('type','deja_implante')->count(),
        'dossier_technique'   => $lotsT->where('type','dossier_technique')->count()   + $zonesT->where('type','dossier_technique')->count(),
        'morcellement'        => $lotsT->where('type','morcellement')->count()        + $zonesT->where('type','morcellement')->count(),
    ];
});

    return view('admin.sites.show', compact('site', 'tfs', 'lots', 'zones', 'stats', 'statsTf'));
}

    public function edit(Site $site)
    {
        return view('admin.sites.edit', compact('site'));
    }

    public function update(Request $request, Site $site)
    {
        $request->validate([
            'name'        => 'required|string',
            'description' => 'nullable|string',
            'svg'         => 'nullable|file',
        ]);

        $svgPath = $site->svg_path;

        if ($request->hasFile('svg') && $request->file('svg')->isValid()) {
            $file        = $request->file('svg');
            $filename    = time() . '_' . $file->getClientOriginalName();
            $destination = storage_path('app/public/maps');
            if (!file_exists($destination)) mkdir($destination, 0777, true);
            $file->move($destination, $filename);
            $svgPath = 'storage/maps/' . $filename;
        }

        $site->update([
            'name'        => $request->name,
            'description' => $request->description,
            'svg_path'    => $svgPath,
        ]);

        return redirect()->route('sites.index', $site->grand_site_id)->with('success', 'Site modifié');
    }

    public function destroy(Site $site)
    {
        $grand_site_id = $site->grand_site_id;

        if ($site->svg_path) {
            $fp = storage_path('app/public/maps/' . basename($site->svg_path));
            if (file_exists($fp)) unlink($fp);
        }

        $site->delete();

        return redirect()->route('sites.index', $grand_site_id)->with('success', 'Site supprimé');
    }
}