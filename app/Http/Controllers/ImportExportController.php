<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportExportController extends Controller
{
    private array $tablesOrder = [
        'grand_sites', 'sites', 'tfs', 'lots',
        'clients', 'commerciaux', 'conducteurs', 'facilitateurs', 'agents_commerciaux',
        'dossiers_clients', 'paiements_dossier', 'dossiers_techniques',
        'zone_groupes', 'rapports', 'visites', 'visiteurs',

        'rh_directions', 'rh_services', 'rh_postes', 'rh_employes',
        'rh_employe_documents', 'rh_bulletins_paie', 'rh_absences',
        'rh_prets', 'rh_heures_sup', 'rh_sanctions', 'rh_retards',
        'rh_recapitulatifs',

        'users',
    ];

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
        $tableStats = [];

        foreach ($this->tablesOrder as $table) {
            try {
                $tableStats[$table] = DB::table($table)->count();
            } catch (\Throwable $e) {
                $tableStats[$table] = null;
            }
        }

        return view('admin.import-export', compact('tableStats'));
    }

    // =========================================================
    // EXPORT SQLITE CLEAN
    // =========================================================
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

        foreach ($tables as $table) {
            if (!Schema::hasTable($table)) continue;
            $sql .= $this->exportTable($table);
        }

        $filename = 'eden_' . $module . '_' . now()->format('Y-m-d_His') . '.sql';

        return response($sql, 200, [
            'Content-Type' => 'application/sql',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    private function genererEntete(string $module): string
    {
        return "-- =====================================\n"
            . "-- Eden Group Export (SQLite)\n"
            . "-- Module: {$module}\n"
            . "-- Date: " . now() . "\n"
            . "-- =====================================\n\n";
    }

    // =========================================================
    // EXPORT TABLE
    // =========================================================
    private function exportTable(string $table): string
    {
        $sql = "-- TABLE {$table}\n";
        $sql .= "DELETE FROM {$table};\n";

        $rows = DB::table($table)->get();

        if ($rows->isEmpty()) {
            return $sql . "-- vide\n\n";
        }

        $cols = array_keys((array)$rows->first());
        $colsStr = implode(',', array_map(fn($c) => "`$c`", $cols));

        $chunks = $rows->chunk(500);

        foreach ($chunks as $chunk) {
            $values = $chunk->map(function ($row) {
                return '(' . implode(',', array_map(function ($v) {
                    if ($v === null) return 'NULL';
                    if (is_numeric($v)) return $v;
                    return "'" . addslashes($v) . "'";
                }, (array)$row)) . ')';
            })->implode(",\n");

            $sql .= "INSERT INTO {$table} ({$colsStr}) VALUES\n{$values};\n";
        }

        return $sql . "\n";
    }

    // =========================================================
    // IMPORT SQLITE CLEAN
    // =========================================================
    public function import(Request $request)
    {
        $request->validate([
            'fichier_sql' => 'required|file|mimes:sql,txt|max:102400',
            'confirmer' => 'required|accepted',
        ]);

        $file = $request->file('fichier_sql');
        $content = file_get_contents($file->getRealPath());

        if (!str_contains($content, 'Eden Group')) {
            return back()->with('error', 'Fichier invalide');
        }

        try {
            DB::statement('PRAGMA journal_mode=WAL');
            DB::statement('PRAGMA foreign_keys=OFF');

            $statements = $this->parseSQL($content);

            $executed = 0;
            $errors = [];

            foreach ($statements as $stmt) {
                $stmt = trim($stmt);

                if (
                    $stmt === '' ||
                    str_starts_with($stmt, '--') ||
                    str_starts_with($stmt, 'SET ') ||
                    str_contains($stmt, 'FOREIGN_KEY_CHECKS')
                ) {
                    continue;
                }

                try {
                    DB::statement($stmt);
                    $executed++;
                } catch (\Throwable $e) {
                    $errors[] = substr($stmt, 0, 80) . ' => ' . $e->getMessage();
                }
            }

            DB::statement('PRAGMA foreign_keys=ON');

            $msg = "Import OK : {$executed} requêtes";

            if (!empty($errors)) {
                session(['import_errors' => array_slice($errors, 0, 20)]);
                $msg .= " + erreurs ignorées";
            }

            return back()->with('success', $msg);

        } catch (\Throwable $e) {
            DB::statement('PRAGMA foreign_keys=ON');
            return back()->with('error', $e->getMessage());
        }
    }

    // =========================================================
    // PARSER SQL
    // =========================================================
    private function parseSQL(string $sql): array
    {
        $statements = [];
        $current = '';
        $inString = false;
        $quote = '';

        for ($i = 0; $i < strlen($sql); $i++) {
            $char = $sql[$i];
            $prev = $sql[$i - 1] ?? '';

            if (!$inString && $char === '-' && $sql[$i + 1] === '-') {
                while ($i < strlen($sql) && $sql[$i] !== "\n") $i++;
                continue;
            }

            if (($char === "'" || $char === '"') && $prev !== '\\') {
                if (!$inString) {
                    $inString = true;
                    $quote = $char;
                } elseif ($quote === $char) {
                    $inString = false;
                }
            }

            if ($char === ';' && !$inString) {
                $statements[] = trim($current);
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (trim($current)) {
            $statements[] = trim($current);
        }

        return $statements;
    }
}