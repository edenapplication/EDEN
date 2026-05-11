<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DossierTechnique;
use App\Models\Lot;
use App\Models\ZoneGroupe;

class DossierTechniqueController extends Controller
{
    // ── Dossier d'un LOT ──────────────────────────────────────
    public function show($lotId)
    {
        $lot = Lot::with([
            'tf.site.grandSite',
            'client',
            'dossierClient.paiements',
        ])->findOrFail($lotId);

        // Le dossier technique existe seulement si le lot a un client
        $dossier = DossierTechnique::where('lot_id', $lotId)
                                    ->whereNull('zone_groupe_id')
                                    ->firstOrFail();

        $dossierClient = $lot->dossierClient ?? $lot->client?->dossiers->first();

        return view('admin.dossier.show', compact('dossier', 'lot', 'dossierClient'));
    }

    // ── Dossier d'une ZONE GROUPE ─────────────────────────────
    public function showZone($zoneId)
    {
        $zone = ZoneGroupe::with([
            'client',
            'dossierClient.paiements',
            'tf.site.grandSite',
        ])->findOrFail($zoneId);

        $dossier = DossierTechnique::where('zone_groupe_id', $zoneId)
                                    ->whereNull('lot_id')
                                    ->firstOrFail();

        $dossierClient = $zone->dossierClient ?? $zone->client?->dossiers->first();

        return view('admin.dossier.show_zone', compact('dossier', 'zone', 'dossierClient'));
    }

    // ── Toggle étape (LOT) ────────────────────────────────────
    public function toggle(Request $request, $lotId)
    {
        $request->validate([
            'etape' => 'required|in:montage_dossier,bon_pour_ccp,controle,mise_a_jour,secretariat,signature',
        ]);

        $dossier = DossierTechnique::where('lot_id', $lotId)
                                    ->whereNull('zone_groupe_id')
                                    ->firstOrFail();

        $etape           = $request->etape;
        $dossier->$etape = !$dossier->$etape;
        $dossier->progression = $dossier->calculerProgression();
        $dossier->statut      = $dossier->calculerStatut();
        $dossier->save();

        return response()->json([
            'success'    => true,
            'progression'=> $dossier->progression,
            'statut'     => $dossier->statut,
            'etape'      => $etape,
            'valeur'     => $dossier->$etape,
        ]);
    }

    // ── Toggle étape (ZONE GROUPE) ────────────────────────────
    public function toggleZone(Request $request, $zoneId)
    {
        $request->validate([
            'etape' => 'required|in:montage_dossier,bon_pour_ccp,controle,mise_a_jour,secretariat,signature',
        ]);

        $dossier = DossierTechnique::where('zone_groupe_id', $zoneId)
                                    ->whereNull('lot_id')
                                    ->firstOrFail();

        $etape           = $request->etape;
        $dossier->$etape = !$dossier->$etape;
        $dossier->progression = $dossier->calculerProgression();
        $dossier->statut      = $dossier->calculerStatut();
        $dossier->save();

        return response()->json([
            'success'    => true,
            'progression'=> $dossier->progression,
            'statut'     => $dossier->statut,
            'etape'      => $etape,
            'valeur'     => $dossier->$etape,
        ]);
    }
}