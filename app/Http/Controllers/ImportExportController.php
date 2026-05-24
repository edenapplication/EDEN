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

    private function driver(): string
    {
        return config('database.default');
    }

    private function isSQLite(): bool
    {
        return $this->driver() === 'sqlite';
    }

    // ✅ Désactiver FK selon driver
    private function disableFK(): void
    {
        if ($this->isSQLite()) {
            DB::statement('PRAGMA foreign_keys=OFF');
            DB::statement('PRAGMA journal_mode=WAL');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::statement('SET NAMES utf8mb4');
        }
    }

    // ✅ Réactiver FK selon driver
    private function enableFK(): void
    {
        if ($this->isSQLite()) {
            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

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
        $driver = $this->driver();
        return view('admin.import-export', compact('tableStats', 'driver'));
    }

    // =========================================================
    // EXPORT — JSON universel compatible MySQL + SQLite
    // =========================================================
    public function export(Request $request)
    {
        $request->validate([
            'module' => 'required|in:all,foncier,rh,users',
        ]);

        $module = $request->module;
        $tables = $module === 'all' ? $this->tablesOrder : ($this->modules[$module] ?? []);

        $export = [
            'meta' => [
                'source'  => 'Eden Group',
                'module'  => $module,
                'date'    => now()->toIso8601String(),
                'driver'  => $this->driver(),
                'version' => '2.0',
            ],
            'tables' => [],
        ];

        foreach ($tables as $table) {
            try {
                if (!Schema::hasTable($table)) continue;
                $rows = DB::table($table)->get()->toArray();
                $export['tables'][$table] = array_map(fn($r) => (array) $r, $rows);
            } catch (\Throwable $e) {
                $export['tables'][$table] = [];
            }
        }

        $json     = json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        $filename = 'eden_' . $module . '_' . now()->format('Y-m-d_His') . '.json';

        return response($json, 200, [
            'Content-Type'        => 'application/json; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    // =========================================================
    // IMPORT — détecte JSON ou SQL
    // =========================================================
    public function import(Request $request)
    {
        $request->validate([
            'fichier_sql' => 'required|file|max:102400',
            'confirmer'   => 'required|accepted',
            'mode'        => 'nullable|in:replace,merge',
        ]);

        $content = file_get_contents($request->file('fichier_sql')->getRealPath());
        $mode    = $request->input('mode', 'merge');

        return str_starts_with(trim($content), '{')
            ? $this->importJSON($content, $mode)
            : $this->importSQL($content);
    }

    // =========================================================
    // IMPORT JSON
    // =========================================================
    private function importJSON(string $content, string $mode)
    {
        $data = json_decode($content, true);

        if (!$data || ($data['meta']['source'] ?? '') !== 'Eden Group') {
            return back()->with('error', 'Fichier JSON invalide ou non reconnu.');
        }

        $tables   = $data['tables'] ?? [];
        $executed = 0;
        $errors   = [];

        // Ordre correct pour les FK
        $ordered = array_values(array_intersect($this->tablesOrder, array_keys($tables)));
        $extra   = array_values(array_diff(array_keys($tables), $this->tablesOrder));
        $ordered = array_merge($ordered, $extra);

        $this->disableFK();

        try {
            foreach ($ordered as $table) {
                if (!Schema::hasTable($table)) continue;

                $rows = $tables[$table] ?? [];
                if (empty($rows)) continue;

                try {
                    // Vider la table si mode replace
                    if ($mode === 'replace') {
                        DB::table($table)->delete();
                    }

                    foreach ($rows as $row) {
                        // ✅ Nettoyer la ligne — supprimer les clés inexistantes dans la table cible
                        $row = $this->nettoyerLigne($table, $row);
                        if (empty($row)) continue;

                        try {
                            if ($mode === 'merge' && isset($row['id'])) {
                                $exists = DB::table($table)->where('id', $row['id'])->exists();
                                if ($exists) {
                                    DB::table($table)->where('id', $row['id'])->update($row);
                                } else {
                                    DB::table($table)->insert($row);
                                }
                            } else {
                                // En replace, on insère directement
                                DB::table($table)->insert($row);
                            }
                            $executed++;
                        } catch (\Throwable $e) {
                            $errors[] = "[{$table}] ID " . ($row['id'] ?? '?') . " : " . $e->getMessage();
                        }
                    }

                } catch (\Throwable $e) {
                    $errors[] = "[{$table}] " . $e->getMessage();
                }
            }
        } finally {
            $this->enableFK();
        }

        $msg = "Import terminé : {$executed} enregistrement(s).";
        if (!empty($errors)) {
            session(['import_errors' => array_slice($errors, 0, 30)]);
            $msg .= " " . count($errors) . " erreur(s).";
        }

        return back()->with('success', $msg);
    }

    // ✅ Garder uniquement les colonnes qui existent dans la table cible
    private function nettoyerLigne(string $table, array $row): array
    {
        try {
            $colonnes = Schema::getColumnListing($table);
            if (empty($colonnes)) return $row;
            return array_intersect_key($row, array_flip($colonnes));
        } catch (\Throwable $e) {
            return $row;
        }
    }

    // =========================================================
    // IMPORT SQL LEGACY
    // =========================================================
    private function importSQL(string $content)
    {
        if (!str_contains($content, 'Eden Group')) {
            return back()->with('error', 'Fichier SQL invalide : pas un export Eden Group.');
        }

        $executed = 0;
        $errors   = [];

        $this->disableFK();

        try {
            $statements = $this->parseSQL($content);

            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if (empty($stmt) || str_starts_with($stmt, '--')) continue;

                if ($this->isSQLite()) {
                    if (preg_match('/^(SET\s|TRUNCATE\s)/i', $stmt)) continue;
                    $stmt = preg_replace('/TRUNCATE\s+TABLE\s+`?(\w+)`?/i', 'DELETE FROM "$1"', $stmt);
                    $stmt = str_replace('`', '"', $stmt);
                }

                try {
                    DB::statement($stmt);
                    $executed++;
                } catch (\Throwable $e) {
                    $errors[] = substr($stmt, 0, 100) . ' → ' . $e->getMessage();
                }
            }
        } finally {
            $this->enableFK();
        }

        $msg = "Import SQL : {$executed} instruction(s).";
        if (!empty($errors)) {
            session(['import_errors' => array_slice($errors, 0, 20)]);
            $msg .= " " . count($errors) . " erreur(s).";
        }

        return back()->with('success', $msg);
    }

    private function parseSQL(string $sql): array
    {
        $statements = [];
        $current    = '';
        $inString   = false;
        $quote      = '';
        $len        = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $char = $sql[$i];
            $prev = $i > 0 ? $sql[$i - 1] : '';

            if (!$inString && $char === '-' && isset($sql[$i + 1]) && $sql[$i + 1] === '-') {
                while ($i < $len && $sql[$i] !== "\n") $i++;
                continue;
            }

            if (($char === "'" || $char === '"') && $prev !== '\\') {
                if (!$inString) { $inString = true; $quote = $char; }
                elseif ($char === $quote) { $inString = false; }
            }

            if ($char === ';' && !$inString) {
                $s = trim($current);
                if ($s) $statements[] = $s;
                $current = '';
            } else {
                $current .= $char;
            }
        }

        if (trim($current)) $statements[] = trim($current);
        return $statements;
    }
}