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

    // ✅ Détecter le driver de la BD courante
    private function driver(): string
    {
        return config('database.default');
    }

    private function isSQLite(): bool
    {
        return $this->driver() === 'sqlite';
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
    // EXPORT — génère JSON universel (compatible MySQL + SQLite)
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

        $export = [
            'meta' => [
                'source'     => 'Eden Group',
                'module'     => $module,
                'date'       => now()->toIso8601String(),
                'driver'     => $this->driver(),
                'version'    => '2.0',
            ],
            'tables' => [],
        ];

        foreach ($tables as $table) {
            try {
                if (!Schema::hasTable($table)) continue;
                $rows = DB::table($table)->get()->toArray();
                // Convertir objets en tableaux
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
    // IMPORT — lit JSON et insère dans MySQL OU SQLite
    // =========================================================
    public function import(Request $request)
    {
        $request->validate([
            'fichier_sql' => 'required|file|mimes:json,txt,sql|max:102400',
            'confirmer'   => 'required|accepted',
            'mode'        => 'nullable|in:replace,merge',
        ]);

        $file    = $request->file('fichier_sql');
        $content = file_get_contents($file->getRealPath());
        $mode    = $request->input('mode', 'merge');

        // ✅ Détecter si c'est un JSON ou un SQL
        $isJson = str_starts_with(trim($content), '{');

        if ($isJson) {
            return $this->importJSON($content, $mode);
        } else {
            return $this->importSQL($content);
        }
    }

    // =========================================================
    // IMPORT JSON (format universel)
    // =========================================================
    private function importJSON(string $content, string $mode)
    {
        $data = json_decode($content, true);

        if (!$data || !isset($data['meta']) || $data['meta']['source'] !== 'Eden Group') {
            return back()->with('error', 'Fichier JSON invalide ou non reconnu comme export Eden Group.');
        }

        $tables    = $data['tables'] ?? [];
        $executed  = 0;
        $errors    = [];
        $isSQLite  = $this->isSQLite();

        try {
            // Désactiver les FK selon le driver
            if ($isSQLite) {
                DB::statement('PRAGMA foreign_keys=OFF');
                DB::statement('PRAGMA journal_mode=WAL');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                DB::statement('SET NAMES utf8mb4');
            }

            // Ordre des tables pour respecter les FK
            $orderedTables = array_intersect($this->tablesOrder, array_keys($tables));
            // Ajouter les tables non listées à la fin
            $extraTables   = array_diff(array_keys($tables), $this->tablesOrder);
            $orderedTables = array_merge($orderedTables, $extraTables);

            foreach ($orderedTables as $table) {
                $rows = $tables[$table] ?? [];
                if (!Schema::hasTable($table)) continue;

                try {
                    if ($mode === 'replace') {
                        if ($isSQLite) {
                            DB::table($table)->delete();
                        } else {
                            DB::statement("TRUNCATE TABLE `{$table}`");
                        }
                    }

                    // Insérer par lots de 100
                    $chunks = array_chunk($rows, 100);
                    foreach ($chunks as $chunk) {
                        foreach ($chunk as $row) {
                            try {
                                if ($mode === 'merge') {
                                    // Upsert : update si existe, insert sinon
                                    $existing = DB::table($table)->where('id', $row['id'] ?? null)->first();
                                    if ($existing) {
                                        DB::table($table)->where('id', $row['id'])->update($row);
                                    } else {
                                        DB::table($table)->insert($row);
                                    }
                                } else {
                                    DB::table($table)->insert($row);
                                }
                                $executed++;
                            } catch (\Throwable $e) {
                                $errors[] = "Table {$table} ID " . ($row['id'] ?? '?') . " : " . $e->getMessage();
                            }
                        }
                    }

                } catch (\Throwable $e) {
                    $errors[] = "Table {$table} : " . $e->getMessage();
                }
            }

        } finally {
            // Réactiver les FK
            if ($isSQLite) {
                DB::statement('PRAGMA foreign_keys=ON');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }

        $msg = "Import JSON terminé : {$executed} enregistrement(s) traité(s).";
        if (!empty($errors)) {
            session(['import_errors' => array_slice($errors, 0, 20)]);
            $msg .= " " . count($errors) . " erreur(s).";
        }

        return back()->with('success', $msg);
    }

    // =========================================================
    // IMPORT SQL LEGACY (anciens exports .sql)
    // =========================================================
    private function importSQL(string $content)
    {
        if (!str_contains($content, 'Eden Group')) {
            return back()->with('error', 'Fichier SQL invalide : pas un export Eden Group.');
        }

        $isSQLite = $this->isSQLite();
        $executed = 0;
        $errors   = [];

        try {
            if ($isSQLite) {
                DB::statement('PRAGMA foreign_keys=OFF');
                DB::statement('PRAGMA journal_mode=WAL');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                DB::statement('SET NAMES utf8mb4');
            }

            $statements = $this->parseSQL($content);

            foreach ($statements as $stmt) {
                $stmt = trim($stmt);
                if (empty($stmt) || str_starts_with($stmt, '--')) continue;

                // Ignorer les commandes MySQL si on est sur SQLite
                if ($isSQLite) {
                    if (preg_match('/^(SET |TRUNCATE |SET FOREIGN)/i', $stmt)) continue;
                    // Convertir TRUNCATE → DELETE
                    $stmt = preg_replace('/TRUNCATE TABLE\s+`?(\w+)`?/i', 'DELETE FROM $1', $stmt);
                    // Supprimer les backticks (non supportés par SQLite)
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
            if ($isSQLite) {
                DB::statement('PRAGMA foreign_keys=ON');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }

        $msg = "Import SQL terminé : {$executed} instruction(s).";
        if (!empty($errors)) {
            session(['import_errors' => array_slice($errors, 0, 20)]);
            $msg .= " " . count($errors) . " erreur(s) ignorée(s).";
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
            $prev = $i > 0 ? $sql[$i-1] : '';

            if (!$inString && $char === '-' && isset($sql[$i+1]) && $sql[$i+1] === '-') {
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