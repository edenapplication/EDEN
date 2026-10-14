<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BonPaiement;
use App\Models\PaiementTechnique;
use App\Models\PaiementDossier;
use App\Models\PaiementLogistique;
use App\Models\PaiementMorcellement;

echo "═══════════════════════════════════════════════════════\n";
echo "🔧 RECRÉATION DES PAIEMENTS MANQUANTS\n";
echo "═══════════════════════════════════════════════════════\n\n";

$MODE = true;  // false = diagnostic, true = recréer

$total = 0;

foreach (BonPaiement::all() as $bon) {
    $dossierId = $bon->dossier_client_id;
    $ref = $bon->user_reference ?? 'SYSTEM';
    $date = $bon->date_bon;
    
    // ═══════════════════════════════════════════════════════
    // Paiement TECHNIQUE
    // ═══════════════════════════════════════════════════════
    if ($bon->versement_technique > 0) {
        $existe = PaiementTechnique::where('dossier_client_id', $dossierId)
            ->where('montant', $bon->versement_technique)
            ->exists();
        
        if (!$existe) {
            echo "📌 {$bon->numero_bon} → TECHNIQUE {$bon->versement_technique} FCFA manquant\n";
            if ($MODE) {
                PaiementTechnique::create([
                    'dossier_client_id' => $dossierId,
                    'montant'           => $bon->versement_technique,
                    'date_paiement'     => $date,
                    'note'              => 'Bon ' . $bon->numero_bon,
                    'user_reference'    => $ref,
                ]);
                echo "   ✅ Recréé\n";
                $total++;
            }
        }
    }
    
    // ═══════════════════════════════════════════════════════
    // Paiement DOSSIER
    // ═══════════════════════════════════════════════════════
    if ($bon->versement_dossier > 0) {
        $existe = PaiementDossier::where('dossier_client_id', $dossierId)
            ->where('montant', $bon->versement_dossier)
            ->exists();
        
        if (!$existe) {
            echo "📌 {$bon->numero_bon} → DOSSIER {$bon->versement_dossier} FCFA manquant\n";
            if ($MODE) {
                PaiementDossier::create([
                    'dossier_client_id' => $dossierId,
                    'montant'           => $bon->versement_dossier,
                    'date_paiement'     => $date,
                    'note'              => 'Bon ' . $bon->numero_bon,
                    'user_reference'    => $ref,
                ]);
                echo "   ✅ Recréé\n";
                $total++;
            }
        }
    }
    
    // ═══════════════════════════════════════════════════════
    // Paiement LOGISTIQUE
    // ═══════════════════════════════════════════════════════
    if ($bon->versement_logistique > 0) {
        $existe = PaiementLogistique::where('dossier_client_id', $dossierId)
            ->where('montant', $bon->versement_logistique)
            ->exists();
        
        if (!$existe) {
            echo "📌 {$bon->numero_bon} → LOGISTIQUE {$bon->versement_logistique} FCFA manquant\n";
            if ($MODE) {
                PaiementLogistique::create([
                    'dossier_client_id' => $dossierId,
                    'montant'           => $bon->versement_logistique,
                    'date_paiement'     => $date,
                    'note'              => 'Bon ' . $bon->numero_bon,
                    'user_reference'    => $ref,
                ]);
                echo "   ✅ Recréé\n";
                $total++;
            }
        }
    }
    
    // ═══════════════════════════════════════════════════════
    // Paiement MORCELLEMENT
    // ═══════════════════════════════════════════════════════
    if ($bon->versement_morcellement > 0) {
        $existe = PaiementMorcellement::where('dossier_client_id', $dossierId)
            ->where('montant', $bon->versement_morcellement)
            ->exists();
        
        if (!$existe) {
            echo "📌 {$bon->numero_bon} → MORCELLEMENT {$bon->versement_morcellement} FCFA manquant\n";
            if ($MODE) {
                PaiementMorcellement::create([
                    'dossier_client_id' => $dossierId,
                    'montant'           => $bon->versement_morcellement,
                    'date_paiement'     => $date,
                    'note'              => 'Bon ' . $bon->numero_bon,
                    'user_reference'    => $ref,
                ]);
                echo "   ✅ Recréé\n";
                $total++;
            }
        }
    }
}

echo "\n═══════════════════════════════════════════════════════\n";
echo "📊 RÉSUMÉ\n";
echo "═══════════════════════════════════════════════════════\n";
echo "Mode             : " . ($MODE ? "RECRÉATION" : "DIAGNOSTIC") . "\n";
echo "Paiements créés  : {$total}\n";
echo "═══════════════════════════════════════════════════════\n";