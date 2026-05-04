<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DossierTechnique;
use App\Models\Lot;

class DossierTechniqueController extends Controller
{
    // =============================================
    // AFFICHER — retourne la vue avec le dossier
    // =============================================
    public function show($lotId)
    {
        $lot = Lot::with('tf.site.grandSite')->findOrFail($lotId);

        $dossier = DossierTechnique::firstOrCreate(
            ['lot_id' => $lotId],
            [
                'progression' => 0,
                'statut'      => 'none',
            ]
        );

        return view('admin.dossier.show', compact('dossier', 'lot'));
    }

    // =============================================
    // TOGGLE — cocher/décocher une étape
    // =============================================
    public function toggle(Request $request, $lotId)
    {
        $request->validate([
            'etape' => 'required|in:montage_dossier,bon_pour_ccp,controle,mise_a_jour,secretariat,signature',
        ]);

        $dossier = DossierTechnique::firstOrCreate(
            ['lot_id' => $lotId],
            ['progression' => 0, 'statut' => 'none']
        );

        $etape          = $request->etape;
        $dossier->$etape = !$dossier->$etape; // toggle

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