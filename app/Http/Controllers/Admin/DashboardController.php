<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GrandSite;
use App\Models\Site;
use App\Models\Tf;
use App\Models\Lot;
use App\Models\ZoneGroupe;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $grandSiteId = $request->grand_site;
        $siteId      = $request->site;
        $tfId        = $request->tf;
        $block       = $request->block;

        // ============================================================
        // STATS GLOBALES — Lots
        // ============================================================
        $allLots = Lot::all();

        $global_famille   = $allLots->where('origine', 'famille')->count();
        $global_eden      = $allLots->where('origine', 'eden')->count();

        $global_superficie_famille = $allLots->where('origine', 'famille')->sum('superficie');
        $global_superficie_eden    = $allLots->where('origine', 'eden')->sum('superficie');

        // Lots typés (compte)
        $global_implantation_prevue = $allLots->where('type', 'implantation_prevue')->count();
        $global_deja_implante       = $allLots->where('type', 'deja_implante')->count();
        $global_dossier_technique   = $allLots->where('type', 'dossier_technique')->count();
        $global_morcellement        = $allLots->where('type', 'morcellement')->count();

        // ✅ Ajouter les zones groupes aux comptes globaux
        $allZones = ZoneGroupe::all();

        $global_implantation_prevue += $allZones->where('type', 'implantation_prevue')->count();
        $global_deja_implante       += $allZones->where('type', 'deja_implante')->count();
        $global_dossier_technique   += $allZones->where('type', 'dossier_technique')->count();
        $global_morcellement        += $allZones->where('type', 'morcellement')->count();

        // Superficies globales avec zones groupes
        $global_superficie_implantation = $allLots->where('type', 'implantation_prevue')->sum('superficie')
            + $allZones->where('type', 'implantation_prevue')->sum('superficie_totale');
        $global_superficie_deja         = $allLots->where('type', 'deja_implante')->sum('superficie')
            + $allZones->where('type', 'deja_implante')->sum('superficie_totale');
        $global_superficie_dossier      = $allLots->where('type', 'dossier_technique')->sum('superficie')
            + $allZones->where('type', 'dossier_technique')->sum('superficie_totale');
        $global_superficie_morcellement = $allLots->where('type', 'morcellement')->sum('superficie')
            + $allZones->where('type', 'morcellement')->sum('superficie_totale');

        $global_total_actif   = $global_implantation_prevue + $global_deja_implante + $global_dossier_technique + $global_morcellement;
        $global_total_lots_zg = $allLots->count() + $allZones->count();
        $global_activite_pct  = $global_total_lots_zg > 0 ? round(($global_total_actif / $global_total_lots_zg) * 100) : 0;

        // ============================================================
        // QUERY FILTRÉE — Lots
        // ============================================================
        $query = Lot::with(['tf.site.grandSite', 'client']);

        if ($grandSiteId)
            $query->whereHas('tf.site', fn($q) => $q->where('grand_site_id', $grandSiteId));
        if ($siteId)
            $query->whereHas('tf', fn($q) => $q->where('site_id', $siteId));
        if ($tfId)
            $query->where('tf_id', $tfId);
        if ($block)
            $query->where('code', 'LIKE', $block . '%');

        $lots       = $query->get();
        $lots_count = $lots->count();

        // ✅ Zones groupes filtrées
        $zgQuery = ZoneGroupe::query();
        if ($grandSiteId)
            $zgQuery->whereHas('tf.site', fn($q) => $q->where('grand_site_id', $grandSiteId));
        if ($siteId)
            $zgQuery->whereHas('tf', fn($q) => $q->where('site_id', $siteId));
        if ($tfId)
            $zgQuery->where('tf_id', $tfId);

        $zonesFiltered = $zgQuery->get();

        // Lots EDEN sans type
        $lots_eden_sans_type = $lots->where('origine', 'eden')->whereNull('type')->count();

        // Types filtrés — Lots + Zones
        $lots_implantation_prevue = $lots->where('type', 'implantation_prevue')->count()
            + $zonesFiltered->where('type', 'implantation_prevue')->count();
        $lots_deja_implante       = $lots->where('type', 'deja_implante')->count()
            + $zonesFiltered->where('type', 'deja_implante')->count();
        $lots_dossier_technique   = $lots->where('type', 'dossier_technique')->count()
            + $zonesFiltered->where('type', 'dossier_technique')->count();
        $lots_morcellement        = $lots->where('type', 'morcellement')->count()
            + $zonesFiltered->where('type', 'morcellement')->count();

        // Superficies filtrées — Lots + Zones
        $superficie_famille = $lots->where('origine', 'famille')->sum('superficie');
        $superficie_eden    = $lots->where('origine', 'eden')->sum('superficie');

        $superficie_implantation_prevue = $lots->where('type', 'implantation_prevue')->sum('superficie')
            + $zonesFiltered->where('type', 'implantation_prevue')->sum('superficie_totale');
        $superficie_deja_implante       = $lots->where('type', 'deja_implante')->sum('superficie')
            + $zonesFiltered->where('type', 'deja_implante')->sum('superficie_totale');
        $superficie_dossier             = $lots->where('type', 'dossier_technique')->sum('superficie')
            + $zonesFiltered->where('type', 'dossier_technique')->sum('superficie_totale');
        $superficie_morcellement        = $lots->where('type', 'morcellement')->sum('superficie')
            + $zonesFiltered->where('type', 'morcellement')->sum('superficie_totale');

        $total_actif_filtre    = $lots_implantation_prevue + $lots_deja_implante + $lots_dossier_technique + $lots_morcellement;
        $total_filtre_lots_zg  = $lots_count + $zonesFiltered->count();
        $activite_filtree_pct  = $total_filtre_lots_zg > 0 ? round(($total_actif_filtre / $total_filtre_lots_zg) * 100) : 0;

        // ============================================================
        // LISTES FILTRES
        // ============================================================
        $grandsites  = GrandSite::orderBy('nom')->get();
        $sites       = Site::when($grandSiteId, fn($q) => $q->where('grand_site_id', $grandSiteId))->orderBy('name')->get();
        $tfs         = Tf::when($siteId, fn($q) => $q->where('site_id', $siteId))->orderBy('title')->get();
        $blocksList  = Lot::pluck('code')->map(fn($c) => strtoupper(substr($c, 0, 1)))->unique()->sort()->values();

        return view('admin.dashboard', compact(
            // Globaux
            'global_famille', 'global_eden',
            'global_superficie_famille', 'global_superficie_eden',
            'global_implantation_prevue', 'global_deja_implante',
            'global_dossier_technique', 'global_morcellement',
            'global_superficie_implantation', 'global_superficie_deja',
            'global_superficie_dossier', 'global_superficie_morcellement',
            'global_activite_pct',
            // Filtrés
            'lots', 'lots_count', 'lots_eden_sans_type',
            'lots_implantation_prevue', 'lots_deja_implante',
            'lots_dossier_technique', 'lots_morcellement',
            'superficie_famille', 'superficie_eden',
            'superficie_implantation_prevue', 'superficie_deja_implante',
            'superficie_dossier', 'superficie_morcellement',
            'activite_filtree_pct',
            // Filtres UI
            'grandsites', 'sites', 'tfs', 'blocksList',
            'grandSiteId', 'siteId', 'tfId', 'block',
        ));
    }
}