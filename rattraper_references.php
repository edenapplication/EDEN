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
echo "🧹 NETTOYAGE DES PAIEMENTS ORPHELINS\n";
echo "═══════════════════════════════════════════════════════\n\n";

// ═══════════════════════════════════════════════════════════
// MODE : Mettre à TRUE pour supprimer, FALSE pour diagnostic seulement
// ═══════════════════════════════════════════════════════════
$MODE_SUPPRESSION = true;  // ⚠️ true = supprime, false = voit seulement

echo "Mode : " . ($MODE_SUPPRESSION ? "🗑️  SUPPRESSION" : "🔍 DIAGNOSTIC SEUL") . "\n\n";

$totalOrphelins = 0;
$totalSupprimes = 0;

// ═══════════════════════════════════════════════════════════
// 1. PAIEMENTS TECHNIQUES
// ═══════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "📌 PAIEMENTS TECHNIQUES\n";
echo "═══════════════════════════════════════════════════════\n";

$orphelins = 0;
foreach (PaiementTechnique::all() as $p) {
    $bon = BonPaiement::where('dossier_client_id', $p->dossier_client_id)
        ->where('versement_technique', $p->montant)
        ->first();
    
    if (!$bon) {
        $orphelins++;
        $totalOrphelins++;
        
        echo "  ⚠️  #{$p->id} | dossier={$p->dossier_client_id} | {$p->montant} FCFA | ref=" . ($p->user_reference ?? 'NULL') . "\n";
        
        if ($MODE_SUPPRESSION) {
            $p->delete();
            $totalSupprimes++;
            echo "      → 🗑️  SUPPRIMÉ\n";
        }
    }
}
echo "\n  Résultat : {$orphelins} orphelin(s) " . ($MODE_SUPPRESSION ? "supprimé(s)" : "détecté(s)") . "\n\n";

// ═══════════════════════════════════════════════════════════
// 2. PAIEMENTS DOSSIER
// ═══════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "📌 PAIEMENTS DOSSIER (superficie)\n";
echo "═══════════════════════════════════════════════════════\n";

$orphelins = 0;
foreach (PaiementDossier::all() as $p) {
    $bon = BonPaiement::where('dossier_client_id', $p->dossier_client_id)
        ->where('versement_dossier', $p->montant)
        ->first();
    
    if (!$bon) {
        $orphelins++;
        $totalOrphelins++;
        
        echo "  ⚠️  #{$p->id} | dossier={$p->dossier_client_id} | {$p->montant} FCFA | ref=" . ($p->user_reference ?? 'NULL') . "\n";
        
        if ($MODE_SUPPRESSION) {
            $p->delete();
            $totalSupprimes++;
            echo "      → 🗑️  SUPPRIMÉ\n";
        }
    }
}
echo "\n  Résultat : {$orphelins} orphelin(s) " . ($MODE_SUPPRESSION ? "supprimé(s)" : "détecté(s)") . "\n\n";

// ═══════════════════════════════════════════════════════════
// 3. PAIEMENTS LOGISTIQUES
// ═══════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "📌 PAIEMENTS LOGISTIQUES\n";
echo "═══════════════════════════════════════════════════════\n";

$orphelins = 0;
foreach (PaiementLogistique::all() as $p) {
    $bon = BonPaiement::where('dossier_client_id', $p->dossier_client_id)
        ->where('versement_logistique', $p->montant)
        ->first();
    
    if (!$bon) {
        $orphelins++;
        $totalOrphelins++;
        
        echo "  ⚠️  #{$p->id} | dossier={$p->dossier_client_id} | {$p->montant} FCFA | ref=" . ($p->user_reference ?? 'NULL') . "\n";
        
        if ($MODE_SUPPRESSION) {
            $p->delete();
            $totalSupprimes++;
            echo "      → 🗑️  SUPPRIMÉ\n";
        }
    }
}
echo "\n  Résultat : {$orphelins} orphelin(s) " . ($MODE_SUPPRESSION ? "supprimé(s)" : "détecté(s)") . "\n\n";

// ═══════════════════════════════════════════════════════════
// 4. PAIEMENTS MORCELLEMENTS
// ═══════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "📌 PAIEMENTS MORCELLEMENTS\n";
echo "═══════════════════════════════════════════════════════\n";

$orphelins = 0;
foreach (PaiementMorcellement::all() as $p) {
    $bon = BonPaiement::where('dossier_client_id', $p->dossier_client_id)
        ->where('versement_morcellement', $p->montant)
        ->first();
    
    if (!$bon) {
        $orphelins++;
        $totalOrphelins++;
        
        echo "  ⚠️  #{$p->id} | dossier={$p->dossier_client_id} | {$p->montant} FCFA | ref=" . ($p->user_reference ?? 'NULL') . "\n";
        
        if ($MODE_SUPPRESSION) {
            $p->delete();
            $totalSupprimes++;
            echo "      → 🗑️  SUPPRIMÉ\n";
        }
    }
}
echo "\n  Résultat : {$orphelins} orphelin(s) " . ($MODE_SUPPRESSION ? "supprimé(s)" : "détecté(s)") . "\n\n";

// ═══════════════════════════════════════════════════════════
// RÉSUMÉ FINAL
// ═══════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "📊 RÉSUMÉ FINAL\n";
echo "═══════════════════════════════════════════════════════\n";
echo "Mode                 : " . ($MODE_SUPPRESSION ? "SUPPRESSION" : "DIAGNOSTIC") . "\n";
echo "Total orphelins      : {$totalOrphelins}\n";

if ($MODE_SUPPRESSION) {
    echo "Total supprimés      : {$totalSupprimes}\n";
    echo "Erreurs              : " . ($totalOrphelins - $totalSupprimes) . "\n";
}

echo "───────────────────────────────────────────────────────\n";

// État actuel des tables
echo "\n📊 ÉTAT ACTUEL DES TABLES :\n";
echo "  Paiements techniques    : " . PaiementTechnique::count() . "\n";
echo "  Paiements dossier       : " . PaiementDossier::count() . "\n";
echo "  Paiements logistiques   : " . PaiementLogistique::count() . "\n";
echo "  Paiements morcellements : " . PaiementMorcellement::count() . "\n";
echo "  Bons de paiement        : " . BonPaiement::count() . "\n";

echo "\n═══════════════════════════════════════════════════════\n";

if ($MODE_SUPPRESSION) {
    echo "✅ Nettoyage terminé\n";
} else {
    echo "💡 Passez \$MODE_SUPPRESSION à true pour supprimer\n";
}
echo "═══════════════════════════════════════════════════════\n";