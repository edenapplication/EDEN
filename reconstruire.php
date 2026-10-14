<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\BonPaiement;
use App\Models\PaiementTechnique;
use App\Models\PaiementDossier;
use App\Models\PaiementLogistique;
use App\Models\PaiementMorcellement;
use App\Models\Visite;

echo "═══════════════════════════════════════════════════════\n";
echo "🔧 RECONSTRUCTION DES PAIEMENTS PERDUS\n";
echo "═══════════════════════════════════════════════════════\n\n";

// ═══════════════════════════════════════════════════════════
// MODE : false = simulation, true = exécution
// ═══════════════════════════════════════════════════════════
$MODE = false;

echo "Mode : " . ($MODE ? "⚠️  EXÉCUTION" : "🔍 SIMULATION") . "\n\n";

$stats = [
    'tech_cree'    => 0,
    'dossier_cree' => 0,
    'logi_cree'    => 0,
    'morc_cree'    => 0,
    'total_bons'   => 0,
    'total_visites'=> 0,
];

// ═══════════════════════════════════════════════════════════
// SOURCE 1 : LES BONS DE PAIEMENT
// ═══════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "📋 SOURCE 1 : Bons de paiement\n";
echo "═══════════════════════════════════════════════════════\n";

$bons = BonPaiement::all();
$stats['total_bons'] = $bons->count();
echo "Total bons : {$stats['total_bons']}\n\n";

foreach ($bons as $bon) {
    $dossierId = $bon->dossier_client_id;
    $ref       = $bon->user_reference ?? 'RECONSTRUIT';
    $date      = $bon->date_bon;

    // ─── TECHNIQUE ───
    if ($bon->versement_technique > 0) {
        $existe = PaiementTechnique::where('dossier_client_id', $dossierId)
            ->where('montant', $bon->versement_technique)
            ->whereDate('date_paiement', $date)
            ->exists();

        if (!$existe) {
            if ($MODE) {
                PaiementTechnique::create([
                    'dossier_client_id' => $dossierId,
                    'montant'           => $bon->versement_technique,
                    'date_paiement'     => $date,
                    'note'              => 'Bon ' . $bon->numero_bon,
                    'user_reference'    => $ref,
                ]);
            }
            $stats['tech_cree']++;
        }
    }

    // ─── DOSSIER ───
    if ($bon->versement_dossier > 0) {
        $existe = PaiementDossier::where('dossier_client_id', $dossierId)
            ->where('montant', $bon->versement_dossier)
            ->whereDate('date_paiement', $date)
            ->exists();

        if (!$existe) {
            if ($MODE) {
                PaiementDossier::create([
                    'dossier_client_id' => $dossierId,
                    'montant'           => $bon->versement_dossier,
                    'date_paiement'     => $date,
                    'note'              => 'Bon ' . $bon->numero_bon,
                    'user_reference'    => $ref,
                ]);
            }
            $stats['dossier_cree']++;
        }
    }

    // ─── LOGISTIQUE ───
    if ($bon->versement_logistique > 0) {
        $existe = PaiementLogistique::where('dossier_client_id', $dossierId)
            ->where('montant', $bon->versement_logistique)
            ->whereDate('date_paiement', $date)
            ->exists();

        if (!$existe) {
            if ($MODE) {
                PaiementLogistique::create([
                    'dossier_client_id' => $dossierId,
                    'montant'           => $bon->versement_logistique,
                    'date_paiement'     => $date,
                    'note'              => 'Bon ' . $bon->numero_bon,
                    'user_reference'    => $ref,
                ]);
            }
            $stats['logi_cree']++;
        }
    }

    // ─── MORCELLEMENT ───
    if ($bon->versement_morcellement > 0) {
        $existe = PaiementMorcellement::where('dossier_client_id', $dossierId)
            ->where('montant', $bon->versement_morcellement)
            ->whereDate('date_paiement', $date)
            ->exists();

        if (!$existe) {
            if ($MODE) {
                PaiementMorcellement::create([
                    'dossier_client_id' => $dossierId,
                    'montant'           => $bon->versement_morcellement,
                    'date_paiement'     => $date,
                    'note'              => 'Bon ' . $bon->numero_bon,
                    'user_reference'    => $ref,
                ]);
            }
            $stats['morc_cree']++;
        }
    }
}

echo "   ✅ Source 1 terminée\n\n";

// ═══════════════════════════════════════════════════════════
// SOURCE 2 : LES VISITES (pour les paiements SANS bon)
// ═══════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "📋 SOURCE 2 : Visites avec paiements\n";
echo "═══════════════════════════════════════════════════════\n";

$visites = Visite::where('paiement_lie', true)->get();
$stats['total_visites'] = $visites->count();
echo "Total visites : {$stats['total_visites']}\n\n";

$visites_extra = 0;

foreach ($visites as $visite) {
    if (!$visite->note) continue;

    // Extraire les paiements depuis la note
    // Format : "📁 Parcelle: 150000 FCFA" ou "🛠️ Technique: 350000 FCFA"
    $patterns = [
        'tech'    => ['/🛠️\s*Technique\s*:\s*([\d\s]+)\s*FCFA/u', PaiementTechnique::class],
        'dossier' => ['/📁\s*Parcelle\s*:\s*([\d\s]+)\s*FCFA/u', PaiementDossier::class],
        'logi'    => ['/🚗\s*Logistique\s*:\s*([\d\s]+)\s*FCFA/u', PaiementLogistique::class],
        'morc'    => ['/✂️\s*Morcellement\s*:\s*([\d\s]+)\s*FCFA/u', PaiementMorcellement::class],
    ];

    foreach ($patterns as $type => [$pattern, $modelClass]) {
        if (preg_match($pattern, $visite->note, $matches)) {
            $montant = (int) str_replace([' ', "\xC2\xA0"], '', $matches[1]);

            if ($montant > 0) {
                $dossierId = $visite->dossier_client_id;
                if (!$dossierId) continue;

                // Vérifier qu'il n'existe pas déjà
                $existe = $modelClass::where('dossier_client_id', $dossierId)
                    ->where('montant', $montant)
                    ->whereDate('date_paiement', $visite->date_visite)
                    ->exists();

                if (!$existe) {
                    if ($MODE) {
                        $modelClass::create([
                            'dossier_client_id' => $dossierId,
                            'montant'           => $montant,
                            'date_paiement'     => $visite->date_visite,
                            'note'              => 'Visite #' . $visite->id,
                            'user_reference'    => 'RECONSTRUIT',
                        ]);
                    }
                    $stats[$type . '_cree']++;
                    $visites_extra++;
                }
            }
        }
    }
}

echo "   ✅ Source 2 terminée\n\n";

// ═══════════════════════════════════════════════════════════
// RÉSUMÉ
// ═══════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "📊 RÉSUMÉ DE LA RECONSTRUCTION\n";
echo "═══════════════════════════════════════════════════════\n";
echo "Mode             : " . ($MODE ? "EXÉCUTION" : "SIMULATION") . "\n\n";
echo "Sources analysées :\n";
echo "  - Bons          : {$stats['total_bons']}\n";
echo "  - Visites       : {$stats['total_visites']}\n\n";
echo "Paiements à recréer :\n";
echo "  🛠️  Techniques    : {$stats['tech_cree']}\n";
echo "  📁 Dossier       : {$stats['dossier_cree']}\n";
echo "  🚗 Logistiques   : {$stats['logi_cree']}\n";
echo "  ✂️  Morcellements : {$stats['morc_cree']}\n";
echo "───────────────────────────────────────────────────────\n";
echo "TOTAL             : " . (
    $stats['tech_cree'] + 
    $stats['dossier_cree'] + 
    $stats['logi_cree'] + 
    $stats['morc_cree']
) . "\n";
echo "═══════════════════════════════════════════════════════\n";

if (!$MODE) {
    echo "\n💡 Pour EXÉCUTER, modifiez le fichier :\n";
    echo "   \$MODE = true;\n";
    echo "   Puis relancez : php reconstruire.php\n";
} else {
    echo "\n✅ Reconstruction terminée\n";
}