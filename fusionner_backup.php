<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════\n";
echo "🔀 FUSION paiements_dossier (avec vérification FK)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$MODE = false;  // false = simulation, true = exécution

echo "Mode : " . ($MODE ? "⚠️  EXÉCUTION" : "🔍 SIMULATION") . "\n\n";

// Attacher le backup
DB::statement("ATTACH DATABASE '" . database_path('database.sqlite.backup_20260930_090128') . "' AS backup");

// ═══════════════════════════════════════════════════════════
// ANALYSE
// ═══════════════════════════════════════════════════════════
echo "📊 Analyse...\n\n";

$countBackup = DB::select("SELECT COUNT(*) as c FROM backup.paiements_dossier")[0]->c;
$countActuel = DB::select("SELECT COUNT(*) as c FROM main.paiements_dossier")[0]->c;

echo "Backup     : {$countBackup}\n";
echo "Actuel     : {$countActuel}\n\n";

// ═══════════════════════════════════════════════════════════
// COMPTER CEUX QUI PEUVENT ÊTRE INSÉRÉS
// (dont le client_id existe OU est null)
// ═══════════════════════════════════════════════════════════
$sqlCount = "
    SELECT COUNT(*) as c
    FROM backup.paiements_dossier b
    WHERE NOT EXISTS (
        SELECT 1 FROM main.paiements_dossier m
        WHERE m.dossier_client_id = b.dossier_client_id
          AND m.montant = b.montant
          AND date(m.date_paiement) = date(b.date_paiement)
    )
    AND (
        b.client_id IS NULL 
        OR EXISTS (SELECT 1 FROM main.clients c WHERE c.id = b.client_id)
    )
";

$countInsertable = DB::select($sqlCount)[0]->c;
echo "✅ Paiements insérables (client_id valide) : {$countInsertable}\n\n";

// Ceux qui seront ignorés
$countIgnorer = $countBackup - $countActuel - $countInsertable;
echo "⚠️  Paiements ignorés (client_id manquant)  : {$countIgnorer}\n\n";

// ═══════════════════════════════════════════════════════════
// EXÉCUTION
// ═══════════════════════════════════════════════════════════
if ($MODE && $countInsertable > 0) {
    echo "═══════════════════════════════════════════════════════\n";
    echo "🔀 EXÉCUTION\n";
    echo "═══════════════════════════════════════════════════════\n\n";

    $sql = "
        INSERT INTO main.paiements_dossier 
            (dossier_client_id, client_id, montant, reste, date_paiement, note, user_reference, created_at, updated_at)
        SELECT 
            b.dossier_client_id, 
            b.client_id,
            b.montant, 
            b.reste,
            b.date_paiement, 
            b.note, 
            'BACKUP-30SEPT' AS user_reference,
            b.created_at,
            b.updated_at
        FROM backup.paiements_dossier b
        WHERE NOT EXISTS (
            SELECT 1 FROM main.paiements_dossier m
            WHERE m.dossier_client_id = b.dossier_client_id
              AND m.montant = b.montant
              AND date(m.date_paiement) = date(b.date_paiement)
        )
        AND (
            b.client_id IS NULL 
            OR EXISTS (SELECT 1 FROM main.clients c WHERE c.id = b.client_id)
        )
    ";

    try {
        DB::statement($sql);
        
        $nouveau = DB::select("SELECT COUNT(*) as c FROM main.paiements_dossier")[0]->c;
        echo "✅ Fusion réussie\n";
        echo "   Total maintenant : {$nouveau}\n";
    } catch (\Exception $e) {
        echo "❌ Erreur : " . $e->getMessage() . "\n";
    }
}

DB::statement("DETACH DATABASE backup");
echo "\n📎 Backup détaché\n";

if (!$MODE) {
    echo "\n💡 Pour exécuter, modifiez \$MODE = true et relancez\n";
}