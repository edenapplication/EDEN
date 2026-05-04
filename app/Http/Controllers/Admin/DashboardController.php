<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\GrandSite;
use App\Models\Site;
use App\Models\Tf;
use App\Models\Lot;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $grandSiteId = $request->grand_site;
        $siteId      = $request->site;
        $tfId        = $request->tf;
        $block       = $request->block;

        // =============================================
        // ÉTAT GLOBAL (sans filtre)
        // =============================================
        $allLots = Lot::with('dossier')->get();

        $global_famille              = $allLots->where('origine', 'famille')->count();
        $global_eden                 = $allLots->where('origine', 'eden')->whereNull('type')->count();
        $global_deja_implante        = $allLots->where('type', 'deja_implante')->count();
        $global_implantation_prevue  = $allLots->where('type', 'implantation_prevue')->count();
        $global_dossier_technique    = $allLots->where('type', 'dossier_technique')->count();
        $global_morcellement         = $allLots->where('type', 'morcellement')->count();

        $global_superficie_famille    = $allLots->where('origine', 'famille')->sum('superficie');
        $global_superficie_eden       = $allLots->where('origine', 'eden')->whereNull('type')->sum('superficie');
        $global_superficie_deja       = $allLots->where('type', 'deja_implante')->sum('superficie');
        $global_superficie_prevue     = $allLots->where('type', 'implantation_prevue')->sum('superficie');
        $global_superficie_dossier    = $allLots->where('type', 'dossier_technique')->sum('superficie');
        $global_superficie_morcellement= $allLots->where('type', 'morcellement')->sum('superficie');

        // Activité globale = lots avec type défini / total lots eden
        $total_eden_global  = $allLots->where('origine', 'eden')->count();
        $actifs_global      = $allLots->where('origine', 'eden')->whereNotNull('type')->count();
        $global_activite_pct = $total_eden_global > 0 ? round(($actifs_global / $total_eden_global) * 100) : 0;

        // =============================================
        // LOTS FILTRÉS
        // =============================================
        $lotsQuery = Lot::with(['tf.site.grandSite', 'dossier'])
            ->when($grandSiteId, fn($q) =>
                $q->whereHas('tf.site', fn($s) => $s->where('grand_site_id', $grandSiteId))
            )
            ->when($siteId, fn($q) =>
                $q->whereHas('tf', fn($t) => $t->where('site_id', $siteId))
            )
            ->when($tfId, fn($q) => $q->where('tf_id', $tfId))
            ->when($block, fn($q) => $q->where('code', 'LIKE', $block . '%'));

        $lots = $lotsQuery->get();

        $lots_count              = $lots->count();
        $lots_eden_sans_type     = $lots->where('origine','eden')->whereNull('type')->count();
        $lots_implantation_prevue= $lots->where('type','implantation_prevue')->count();
        $lots_deja_implante      = $lots->where('type','deja_implante')->count();
        $lots_dossier_technique  = $lots->where('type','dossier_technique')->count();
        $lots_morcellement       = $lots->where('type','morcellement')->count();

        $superficie_famille           = $lots->where('origine','famille')->sum('superficie');
        $superficie_eden              = $lots->where('origine','eden')->whereNull('type')->sum('superficie');
        $superficie_implantation_prevue= $lots->where('type','implantation_prevue')->sum('superficie');
        $superficie_deja_implante     = $lots->where('type','deja_implante')->sum('superficie');
        $superficie_dossier           = $lots->where('type','dossier_technique')->sum('superficie');
        $superficie_morcellement      = $lots->where('type','morcellement')->sum('superficie');

        // Activité filtrée
        $total_eden_filtre  = $lots->where('origine','eden')->count();
        $actifs_filtre      = $lots->where('origine','eden')->whereNotNull('type')->count();
        $activite_filtree_pct = $total_eden_filtre > 0 ? round(($actifs_filtre / $total_eden_filtre) * 100) : 0;

        // =============================================
        // OPTIONS FILTRES
        // =============================================
        $grandsites = GrandSite::orderBy('nom')->get();

        $sites = Site::when($grandSiteId, fn($q) => $q->where('grand_site_id', $grandSiteId))
            ->orderBy('name')->get();

        $tfs = Tf::when($siteId, fn($q) => $q->where('site_id', $siteId))
            ->orderBy('title')->get();

        $blocksList = Lot::pluck('code')
            ->map(fn($c) => strtoupper(substr($c, 0, 1)))
            ->unique()->sort()->values();

        return view('admin.dashboard', compact(
            // global
            'global_famille', 'global_eden',
            'global_deja_implante', 'global_implantation_prevue',
            'global_dossier_technique', 'global_morcellement',
            'global_superficie_famille', 'global_superficie_eden',
            'global_superficie_deja', 'global_superficie_prevue',
            'global_superficie_dossier', 'global_superficie_morcellement',
            'global_activite_pct',
            // filtrés
            'lots', 'lots_count',
            'lots_eden_sans_type', 'lots_implantation_prevue',
            'lots_deja_implante', 'lots_dossier_technique', 'lots_morcellement',
            'superficie_famille', 'superficie_eden',
            'superficie_implantation_prevue', 'superficie_deja_implante',
            'superficie_dossier', 'superficie_morcellement',
            'activite_filtree_pct',
            // options
            'grandsites', 'sites', 'tfs', 'blocksList',
            'grandSiteId', 'siteId', 'tfId', 'block'
        ));
    }
}