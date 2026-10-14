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

// Attacher le backup
DB::statement("ATTACH DATABASE '" . database_path('database.sqlite.backup_20260930_090128') . "' AS backup");

// ═══════════════════════════════════════════════════════════
// CONFIGURATION DES TABLES (colonnes réellement existantes)
// ═══════════════════════════════════════════════════════════
$tables = [
    'paiements_techniques' => [
        'label'    => 'Paiements techniques',
        'colonnes' => ['dossier_client_id', 'client_id', 'montant', 'reste', 'date_paiement', 'note'],
    ],
    'paiements_dossier' => [
        'label'    => 'Paiements dossier',
        'colonnes' => ['dossier_client_id', 'client_id', 'montant', 'reste', 'date_paiement', 'note'],
    ],
    'paiements_logistiques' => [
        'label'    => 'Paiements logistiques',
        'colonnes' => ['dossier_client_id', 'client_id', 'montant', 'reste', 'date_paiement', 'note'],
    ],
    'paiements_morcellements' => [
        'label'    => 'Paiements morcellements',
        'colonnes' => ['dossier_client_id', 'client_id', 'montant', 'reste', 'date_paiement', 'note'],
    ],
];

echo "═══════════════════════════════════════════════════════\n";
echo "🔍 ANALYSE DES PAIEMENTS MANQUANTS\n";
echo "═══════════════════════════════════════════════════════\n\n";

$totalManquant = 0;

foreach ($tables as $table => $config) {
    $countBackup = DB::select("SELECT COUNT(*) as c FROM backup.{$table}")[0]->c;
    $countActuel = DB::select("SELECT COUNT(*) as c FROM main.{$table}")[0]->c;
    $manquant = $countBackup - $countActuel;

    echo "{$config['label']}\n";
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
// EXÉCUTION DE LA FUSION
// ═══════════════════════════════════════════════════════════
if ($MODE) {
    echo "═══════════════════════════════════════════════════════\n";
    echo "🔀 EXÉCUTION DE LA FUSION\n";
    echo "═══════════════════════════════════════════════════════\n\n";

    foreach ($tables as $table => $config) {
        echo "Fusion de {$config['label']}...\n";
        
        // Colonnes présentes dans les 2 tables
        $cols = $config['colonnes'];
        $colsList = implode(', ', $cols);
        $colsSelect = 'b.' . implode(', b.', $cols);
        
        // Ajouter user_reference (nouvelle colonne absente du backup)
        $colsList .= ', user_reference, created_at, updated_at';
        $colsSelect .= ", 'BACKUP-30SEPT' AS user_reference, b.created_at, b.updated_at";

        $sql = "
            INSERT INTO main.{$table} ({$colsList})
            SELECT {$colsSelect}
            FROM backup.{$table} b
            WHERE NOT EXISTS (
                SELECT 1 FROM main.{$table} m
                WHERE m.dossier_client_id = b.dossier_client_id
                  AND m.montant = b.montant
                  AND date(m.date_paiement) = date(b.date_paiement)
            )
        ";
        
        // ⚠️ Vérifier que client_id et reste existent bien
        // (sinon, à retirer de la config)
        
        DB::statement($sql);
        
        echo "  ✅ Terminé\n\n";
    }
    
    echo "═══════════════════════════════════════════════════════\n";
    echo "✅ FUSION TERMINÉE\n";
    echo "═══════════════════════════════════════════════════════\n";
}

DB::statement("DETACH DATABASE backup");
echo "\n📎 Backup détaché\n";

if (!$MODE) {
    echo "\n💡 Pour EXÉCUTER, modifiez \$MODE = true et relancez\n";
}