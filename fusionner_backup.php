<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

echo "═══════════════════════════════════════════════════════\n";
echo "🔀 FUSION paiements_dossier (double vérification FK)\n";
echo "═══════════════════════════════════════════════════════\n\n";

$MODE = true;  // EXÉCUTION

echo "Mode : " . ($MODE ? "⚠️  EXÉCUTION" : "🔍 SIMULATION") . "\n\n";

DB::statement("ATTACH DATABASE '" . database_path('database.sqlite.backup_20260930_090128') . "' AS backup");

// ═══════════════════════════════════════════════════════════
// ANALYSE DÉTAILLÉE
// ═══════════════════════════════════════════════════════════
echo "📊 Analyse...\n\n";

$countBackup = DB::select("SELECT COUNT(*) as c FROM backup.paiements_dossier")[0]->c;
$countActuel = DB::select("SELECT COUNT(*) as c FROM main.paiements_dossier")[0]->c;

echo "Backup     : {$countBackup}\n";
echo "Actuel     : {$countActuel}\n\n";

// ═══════════════════════════════════════════════════════════
// COMPTAGE DES CAS
// ═══════════════════════════════════════════════════════════

// 1. Paiements avec dossier_client_id invalide
$countDossierInvalide = DB::select("
    SELECT COUNT(*) as c FROM backup.paiements_dossier b
    WHERE NOT EXISTS (SELECT 1 FROM main.dossiers_clients d WHERE d.id = b.dossier_client_id)
")[0]->c;
echo "⚠️  Dossier client_id invalide : {$countDossierInvalide}\n";

// 2. Paiements avec client_id invalide
$countClientInvalide = DB::select("
    SELECT COUNT(*) as c FROM backup.paiements_dossier b
    WHERE b.client_id IS NOT NULL 
      AND NOT EXISTS (SELECT 1 FROM main.clients c WHERE c.id = b.client_id)
")[0]->c;
echo "⚠️  Client_id invalide        : {$countClientInvalide}\n\n";

// 3. Paiements insérables (les 2 FK valides)
$countInsertable = DB::select("
    SELECT COUNT(*) as c FROM backup.paiements_dossier b
    WHERE NOT EXISTS (
        SELECT 1 FROM main.paiements_dossier m
        WHERE m.dossier_client_id = b.dossier_client_id
          AND m.montant = b.montant
          AND date(m.date_paiement) = date(b.date_paiement)
    )
    AND EXISTS (SELECT 1 FROM main.dossiers_clients d WHERE d.id = b.dossier_client_id)
    AND (b.client_id IS NULL OR EXISTS (SELECT 1 FROM main.clients c WHERE c.id = b.client_id))
")[0]->c;

echo "✅ Paiements insérables       : {$countInsertable}\n\n";

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
        AND EXISTS (SELECT 1 FROM main.dossiers_clients d WHERE d.id = b.dossier_client_id)
        AND (b.client_id IS NULL OR EXISTS (SELECT 1 FROM main.clients c WHERE c.id = b.client_id))
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

// ═══════════════════════════════════════════════════════════
// SECONDE PASSE : paiements avec dossier valide mais client invalide
// ═══════════════════════════════════════════════════════════
if ($MODE) {
    echo "\n";
    echo "═══════════════════════════════════════════════════════\n";
    echo "🔀 SECONDE PASSE (client_id → NULL)\n";
    echo "═══════════════════════════════════════════════════════\n\n";

    $countPasse2 = DB::select("
        SELECT COUNT(*) as c FROM backup.paiements_dossier b
        WHERE NOT EXISTS (
            SELECT 1 FROM main.paiements_dossier m
            WHERE m.dossier_client_id = b.dossier_client_id
              AND m.montant = b.montant
              AND date(m.date_paiement) = date(b.date_paiement)
        )
        AND EXISTS (SELECT 1 FROM main.dossiers_clients d WHERE d.id = b.dossier_client_id)
        AND b.client_id IS NOT NULL 
        AND NOT EXISTS (SELECT 1 FROM main.clients c WHERE c.id = b.client_id)
    ")[0]->c;

    echo "Paiements avec client manquant : {$countPasse2}\n";
    echo "→ On les insère avec client_id = NULL\n\n";

    if ($countPasse2 > 0) {
        $sql2 = "
            INSERT INTO main.paiements_dossier 
                (dossier_client_id, client_id, montant, reste, date_paiement, note, user_reference, created_at, updated_at)
            SELECT 
                b.dossier_client_id, 
                NULL,
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
            AND EXISTS (SELECT 1 FROM main.dossiers_clients d WHERE d.id = b.dossier_client_id)
            AND b.client_id IS NOT NULL 
            AND NOT EXISTS (SELECT 1 FROM main.clients c WHERE c.id = b.client_id)
        ";

        try {
            DB::statement($sql2);
            $nouveau = DB::select("SELECT COUNT(*) as c FROM main.paiements_dossier")[0]->c;
            echo "✅ Seconde passe réussie\n";
            echo "   Total maintenant : {$nouveau}\n";
        } catch (\Exception $e) {
            echo "❌ Erreur : " . $e->getMessage() . "\n";
        }
    }
}

DB::statement("DETACH DATABASE backup");
echo "\n📎 Backup détaché\n";