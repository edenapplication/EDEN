<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;

class ImportExportController extends Controller
{
    // Tables à exporter — ordre important pour les FK
    private array $tablesOrder = [
        // Foncier
        'grand_sites', 'sites', 'tfs', 'lots',
        'clients', 'commerciaux', 'conducteurs', 'facilitateurs', 'agents_commerciaux',
        'dossiers_clients', 'paiements_dossier', 'dossiers_techniques',
        'zone_groupes', 'rapports', 'visites', 'visiteurs',
        // RH
        'rh_directions', 'rh_services', 'rh_postes', 'rh_employes',
        'rh_employe_documents', 'rh_bulletins_paie', 'rh_absences',
        'rh_prets', 'rh_heures_sup', 'rh_sanctions', 'rh_retards',
        'rh_recapitulatifs',
        // Auth
        'users',
    ];

    // Modules disponibles pour export partiel
    private array $modules = [
        'foncier' => [
            'grand_sites', 'sites', 'tfs', 'lots',
            'clients', 'commerciaux', 'conducteurs', 'facilitateurs', 'agents_commerciaux',
            'dossiers_clients', 'paiements_dossier', 'dossiers_techniques',
            'zone_groupes', 'rapports', 'visites', 'visiteurs',
        ],
        'rh' => [
            'rh_directions', 'rh_services', 'rh_postes', 'rh_employes',
            'rh_employe_documents', 'rh_bulletins_paie', 'rh_absences',
            'rh_prets', 'rh_heures_sup', 'rh_sanctions', 'rh_retards',
            'rh_recapitulatifs',
        ],
        'users' => ['users'],
    ];

    public function index()
    {
        // Taille estimée par table
        $tableStats = [];
        foreach ($this->tablesOrder as $table) {
            try {
                $count = DB::table($table)->count();
                $tableStats[$table] = $count;
            } catch (\Throwable $e) {
                $tableStats[$table] = null; // table inexistante
            }
        }
        return view('admin.import-export', compact('tableStats'));
    }

    // ============================================================
    // EXPORT — génère un fichier SQL complet ou partiel
    // ============================================================
    public function export(Request $request)
    {
        $request->validate([
            'module' => 'required|in:all,foncier,rh,users',
        ]);

        $module = $request->module;
        $tables = $module === 'all'
            ? $this->tablesOrder
            : ($this->modules[$module] ?? []);

        $sql  = $this->genererEntete($module);
        $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            try {
                if (!Schema::hasTable($table)) continue;
                $sql .= $this->exportTable($table);
            } catch (\Throwable $e) {
                $sql .= "-- ERREUR table {$table}: " . $e->getMessage() . "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'eden_' . $module . '_' . now()->format('Y-m-d_His') . '.sql';

        return response($sql, 200, [
            'Content-Type'        => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private function genererEntete(string $module): string
    {
        return "-- ================================================\n"
             . "-- Eden Group — Export SQL\n"
             . "-- Module  : {$module}\n"
             . "-- Date    : " . now()->format('Y-m-d H:i:s') . "\n"
             . "-- Serveur : " . config('database.connections.mysql.host') . "\n"
             . "-- Base    : " . config('database.connections.mysql.database') . "\n"
             . "-- ================================================\n\n"
             . "SET NAMES utf8mb4;\n"
             . "SET CHARACTER SET utf8mb4;\n\n";
    }

    private function exportTable(string $table): string
    {
        $sql  = "-- Table: {$table}\n";
        $sql .= "TRUNCATE TABLE `{$table}`;\n";

        $rows = DB::table($table)->get();
        if ($rows->isEmpty()) {
            $sql .= "-- (vide)\n\n";
            return $sql;
        }

        $cols    = array_keys((array) $rows->first());
        $colsStr = implode(', ', array_map(fn($c) => "`{$c}`", $cols));

        // Grouper par lots de 500 pour éviter les requêtes trop longues
        $chunks = $rows->chunk(500);
        foreach ($chunks as $chunk) {
            $values = $chunk->map(function($row) {
                $vals = array_map(function($v) {
                    if ($v === null) return 'NULL';
                    if (is_numeric($v) && !preg_match('/^0\d/', (string)$v)) return $v;
                    return "'" . addslashes((string)$v) . "'";
                }, (array) $row);
                return '(' . implode(', ', $vals) . ')';
            })->implode(",\n  ");

            $sql .= "INSERT INTO `{$table}` ({$colsStr}) VALUES\n  {$values};\n";
        }

        $sql .= "\n";
        return $sql;
    }

    // ============================================================
    // IMPORT — lit et exécute un fichier SQL
    // ============================================================
    public function import(Request $request)
    {
        $request->validate([
            'fichier_sql' => 'required|file|mimes:sql,txt|max:102400', // 100 MB max
            'confirmer'   => 'required|accepted',
        ]);

        $file    = $request->file('fichier_sql');
        $content = file_get_contents($file->getRealPath());

        // Vérifier que c'est bien un fichier Eden Group
        if (!str_contains($content, 'Eden Group')) {
            return back()->with('error', 'Fichier invalide : ce n\'est pas un export Eden Group.');
        }

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::statement('SET NAMES utf8mb4');

            // Découper en instructions SQL individuelles
            $statements = $this->parseSQL($content);
            $executed   = 0;
            $errors     = [];

            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if (empty($stmt) || str_starts_with($stmt, '--')) continue;
                try {
                    DB::statement($stmt);
                    $executed++;
                } catch (\Throwable $e) {
                    $errors[] = substr($stmt, 0, 80) . ' → ' . $e->getMessage();
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1');

            $msg = "Import terminé : {$executed} instruction(s) exécutée(s).";
            if (!empty($errors)) {
                $msg .= " " . count($errors) . " erreur(s) ignorée(s).";
                session(['import_errors' => array_slice($errors, 0, 20)]);
            }

            return back()->with('success', $msg);

        } catch (\Throwable $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return back()->with('error', 'Erreur import : ' . $e->getMessage());
        }
    }

    // Découpe le SQL en instructions individuelles (gère les ; dans les strings)
    private function parseSQL(string $sql): array
    {
        $statements = [];
        $current    = '';
        $inString   = false;
        $stringChar = '';
        $len        = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i-1] : '';

            // Ignorer les commentaires de ligne
            if (!$inString && $char === '-' && isset($sql[$i+1]) && $sql[$i+1] === '-') {
                while ($i < $len && $sql[$i] !== "\n") $i++;
                continue;
            }

            // Gestion des strings
            if (($char === "'" || $char === '"') && $prev !== '\\') {
                if (!$inString) { $inString = true; $stringChar = $char; }
                elseif ($char === $stringChar) { $inString = false; }
            }

            if ($char === ';' && !$inString) {
                $statements[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }
        if (trim($current)) $statements[] = trim($current);

        return $statements;
    }
}