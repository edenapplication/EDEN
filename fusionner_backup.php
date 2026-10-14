<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════\n";
echo "🔀 FUSION DES PAIEMENTS (Backup → Base actuelle)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$MODE = false;  // false = simulation, true = exécution

echo "Mode : " . ($MODE ? "⚠️  EXÉCUTION" : "🔍 SIMULATION") . "\n\n";

// ═══════════════════════════════════════════════════════════
// 1. ATTACHER LE BACKUP À LA BASE ACTUELLE
// ═══════════════════════════════════════════════════════════
echo "📎 Attachement du backup...\n";

DB::statement("ATTACH DATABASE '" . database_path('database.sqlite.backup_20260930_090128') . "' AS backup");

echo "✅ Backup attaché\n\n";

// ═══════════════════════════════════════════════════════════
// 2. ANALYSER LES PAIEMENTS MANQUANTS
// ═══════════════════════════════════════════════════════════
echo "═══════════════════════════════════════════════════════\n";
echo "🔍 ANALYSE DES PAIEMENTS MANQUANTS\n";
echo "═══════════════════════════════════════════════════════\n\n";

$tables = [
    'paiements_techniques'  => 'Paiements techniques',
    'paiements_dossier'     => 'Paiements dossier',
    'paiements_logistiques' => 'Paiements logistiques',
    'paiements_morcellements' => 'Paiements morcellements',
];

$totalManquant = 0;

foreach ($tables as $table => $label) {
    // Compter dans le backup
    $countBackup = DB::select("SELECT COUNT(*) as c FROM backup.{$table}")[0]->c;
    
    // Compter dans la base actuelle
    $countActuel = DB::select("SELECT COUNT(*) as c FROM main.{$table}")[0]->c;
    
    // Compter ceux qui existent dans le backup mais pas dans la base
    $manquant = $countBackup - $countActuel;
    
    echo "{$label}\n";
    echo "  Backup     : {$countBackup}\n";
    echo "  Actuel     : {$countActuel}\n";
    echo "  Différence : " . ($manquant > 0 ? "+{$manquant} à récupérer" : "—") . "\n\n";
    
    if ($manquant > 0) {
        $totalManquant += $manquant;
    }
}

echo "───────────────────────────────────────────────────────\n";
echo "TOTAL À RÉCUPÉRER : {$totalManquant} paiements\n";
echo "═══════════════════════════════════════════════════════\n\n";

// ═══════════════════════════════════════════════════════════
// 3. EXÉCUTER LA FUSION (si mode = true)
// ═══════════════════════════════════════════════════════════
if ($MODE) {
    echo "═══════════════════════════════════════════════════════\n";
    echo "🔀 EXÉCUTION DE LA FUSION\n";
    echo "═══════════════════════════════════════════════════════\n\n";
    
    foreach ($tables as $table => $label) {
        echo "Fusion de {$label}...\n";
        
        // Insérer les paiements du backup qui n'existent pas dans la base actuelle
        // Critère : même dossier + même montant + même date
        $sql = "
            INSERT INTO main.{$table} 
                (dossier_client_id, montant, date_paiement, note, user_reference, created_at, updated_at)
            SELECT 
                b.dossier_client_id, 
                b.montant, 
                b.date_paiement, 
                b.note, 
                b.user_reference,
                b.created_at,
                b.updated_at
            FROM backup.{$table} b
            WHERE NOT EXISTS (
                SELECT 1 FROM main.{$table} m
                WHERE m.dossier_client_id = b.dossier_client_id
                  AND m.montant = b.montant
                  AND date(m.date_paiement) = date(b.date_paiement)
            )
        ";
        
        $inserted = DB::statement($sql);
        
        echo "  ✅ Terminé\n\n";
    }
    
    echo "═══════════════════════════════════════════════════════\n";
    echo "✅ FUSION TERMINÉE\n";
    echo "═══════════════════════════════════════════════════════\n";
}

// ═══════════════════════════════════════════════════════════
// 4. DÉTACHER LE BACKUP
// ═══════════════════════════════════════════════════════════
DB::statement("DETACH DATABASE backup");

echo "\n📎 Backup détaché\n";

if (!$MODE) {
    echo "\n💡 Pour EXÉCUTER, modifiez \$MODE = true et relancez\n";
}