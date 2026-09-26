<?php
// app/Services/HistoriqueService.php

namespace App\Services;

use App\Models\HistoriqueAffectation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HistoriqueService
{
    /**
     * Enregistre une entrée dans l'historique.
     */
    public static function log(
        int $dossierId,
        string $typeAction,
        string $resume,
        ?string $cibleType = null,
        ?int $cibleId = null,
        ?array $avant = null,
        ?array $apres = null
    ): void {
        try {
            HistoriqueAffectation::create([
                'dossier_client_id' => $dossierId,
                'type_action'       => $typeAction,
                'user_id'           => Auth::id(),
                'cible_type'        => $cibleType,
                'cible_id'          => $cibleId,
                'resume'            => mb_substr($resume, 0, 500),
                'donnees_avant'     => $avant,
                'donnees_apres'     => $apres,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur HistoriqueService::log — ' . $e->getMessage());
        }
    }
}