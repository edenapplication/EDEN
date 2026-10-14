<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════\n";
echo "🔀 FUSION DES PAIEMENTS (Backup → Base actuelle)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$MODE = true;   // ✅ Mode EXÉCUTION

echo "Mode : " . ($MODE ? "⚠️  EXÉCUTION" : "🔍 SIMULATION") . "\n\n";

DB::statement("ATTACH DATABASE '" . database_path('database.sqlite.backup_20260930_090128') . "' AS backup");

// ═══════════════════════════════════════════════════════════
// CONFIGURATION EXACTE DES COLONNES PAR TABLE
// ═══════════════════════════════════════════════════════════
$tables = [
    'paiements_techniques' => [
        'label'    => 'Paiements techniques',
        'colonnes' => ['dossier_client_id', 'montant', 'date_paiement', 'note', 'created_at', 'updated_at'],
    ],
    'paiements_dossier' => [
        'label'    => 'Paiements dossier',
        'colonnes' => ['dossier_client_id', 'client_id', 'montant', 'reste', 'date_paiement', 'note', 'created_at', 'updated_at'],
    ],
    'paiements_logistiques' => [
        'label'    => 'Paiements logistiques',
        'colonnes' => ['dossier_client_id', 'montant', 'date_paiement', 'note', 'created_at', 'updated_at'],
    ],
    'paiements_morcellements' => [
        'label'    => 'Paiements morcellements',
        'colonnes' => ['dossier_client_id', 'montant', 'date_paiement', 'note', 'created_at', 'updated_at'],
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
// EXÉCUTION
// ═══════════════════════════════════════════════════════════
if ($MODE) {
    echo "═══════════════════════════════════════════════════════\n";
    echo "🔀 EXÉCUTION DE LA FUSION\n";
    echo "═══════════════════════════════════════════════════════\n\n";

    foreach ($tables as $table => $config) {
        echo "Fusion de {$config['label']}...\n";

        $colonnesCommunes = $config['colonnes'];

        // Colonnes à insérer : communes + user_reference (inséré après 'note')
        $colonnesInsert = $colonnesCommunes;
        $pos = array_search('note', $colonnesInsert);
        if ($pos !== false) {
            array_splice($colonnesInsert, $pos + 1, 0, 'user_reference');
        } else {
            $colonnesInsert[] = 'user_reference';
        }

        $colsList = implode(', ', $colonnesInsert);

        // SELECT : colonnes communes + 'BACKUP-30SEPT' pour user_reference
        $selectParts = array_map(fn($c) => "b.{$c}", $colonnesCommunes);
        $posSelect = array_search('note', $colonnesCommunes);
        if ($posSelect !== false) {
            array_splice($selectParts, $posSelect + 1, 0, "'BACKUP-30SEPT' AS user_reference");
        } else {
            $selectParts[] = "'BACKUP-30SEPT' AS user_reference";
        }

        $colsSelect = implode(', ', $selectParts);

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

        try {
            DB::statement($sql);
            
            // Compter le nouveau total
            $nouveau = DB::select("SELECT COUNT(*) as c FROM main.{$table}")[0]->c;
            echo "  ✅ Terminé — Total maintenant : {$nouveau}\n\n";
        } catch (\Exception $e) {
            echo "  ❌ ERREUR : " . $e->getMessage() . "\n\n";
        }
    }

    echo "═══════════════════════════════════════════════════════\n";
    echo "✅ FUSION TERMINÉE\n";
    echo "═══════════════════════════════════════════════════════\n";
}

DB::statement("DETACH DATABASE backup");
echo "\n📎 Backup détaché\n";