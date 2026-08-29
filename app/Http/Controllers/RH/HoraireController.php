<?php

namespace App\Http\Controllers\RH;

use App\Http\Controllers\Controller;
use App\Models\RH\HoraireService;
use App\Models\RH\Service;
use Illuminate\Http\Request;

class HoraireController extends Controller
{
    /**
     * Récupérer les horaires d'un service
     */
    public function index($serviceId)
    {
        $horaires = HoraireService::where('service_id', $serviceId)->get();
        $service = Service::findOrFail($serviceId);
        
        return response()->json([
            'service' => $service,
            'horaires' => $horaires
        ]);
    }

    /**
     * Enregistrer ou mettre à jour les horaires d'un service
     */
    public function store(Request $request)
    {
        $request->validate([
            'service_id' => 'required|exists:rh_services,id',
            'horaires' => 'required|array',
            'horaires.*.debut' => 'nullable|date_format:H:i',
            'horaires.*.fin' => 'nullable|date_format:H:i|after:horaires.*.debut',
        ]);

        $serviceId = $request->service_id;
        
        // Supprimer les anciens horaires
        HoraireService::where('service_id', $serviceId)->delete();

        // Créer les nouveaux horaires
        foreach ($request->horaires as $jour => $data) {
            $estTravaille = isset($data['est_travaille']) && $data['est_travaille'] == 1;
            
            HoraireService::create([
                'service_id' => $serviceId,
                'jour' => $jour,
                'heure_debut' => $data['debut'] ?? null,
                'heure_fin' => $data['fin'] ?? null,
                'est_travaille' => $estTravaille,
                'est_ferie' => false,
            ]);
        }

        return back()->with('success', 'Horaires enregistrés avec succès.');
    }

    /**
     * Mettre à jour les horaires d'un service
     */
    public function update(Request $request, $serviceId)
    {
        $request->validate([
            'horaires' => 'required|array',
            'horaires.*.debut' => 'nullable|date_format:H:i',
            'horaires.*.fin' => 'nullable|date_format:H:i|after:horaires.*.debut',
        ]);

        // Supprimer les anciens horaires
        HoraireService::where('service_id', $serviceId)->delete();

        // Créer les nouveaux horaires
        foreach ($request->horaires as $jour => $data) {
            $estTravaille = isset($data['est_travaille']) && $data['est_travaille'] == 1;
            
            HoraireService::create([
                'service_id' => $serviceId,
                'jour' => $jour,
                'heure_debut' => $data['debut'] ?? null,
                'heure_fin' => $data['fin'] ?? null,
                'est_travaille' => $estTravaille,
                'est_ferie' => false,
            ]);
        }

        return back()->with('success', 'Horaires mis à jour avec succès.');
    }
}