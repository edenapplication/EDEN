<?php
namespace App\Http\Controllers;

use App\Models\Lot;
use App\Models\ZoneGroupe;
use Illuminate\Http\Request;

class DossierTechniqueController extends Controller
{
    // Afficher le dossier technique d'un lot
    public function show(Lot $lot)
    {
        return view('admin.dossier_technique.show', compact('lot'));
    }

    // Activer/désactiver le dossier technique d'un lot
    public function toggle(Lot $lot)
    {
        $lot->dossier_technique = !$lot->dossier_technique;
        $lot->save();

        return response()->json([
            'success' => true,
            'dossier_technique' => $lot->dossier_technique
        ]);
    }

    // Afficher le dossier technique d'une zone
    public function showZone(ZoneGroupe $zone)
    {
        return view('admin.dossier_technique.zone', compact('zone'));
    }

    // Activer/désactiver le dossier technique d'une zone
    public function toggleZone(ZoneGroupe $zone)
    {
        $zone->dossier_technique = !$zone->dossier_technique;
        $zone->save();

        return response()->json([
            'success' => true,
            'dossier_technique' => $zone->dossier_technique
        ]);
    }
}