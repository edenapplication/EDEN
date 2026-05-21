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
            'grand_sites','sites','tfs','lots','clients','commerciaux',
            'conducteurs','facilitateurs','agents_commerciaux',
            'dossiers_clients','paiements_dossier','dossiers_techniques',
            'zone_groupes','rapports','visites','visiteurs',
        ],
        'rh' => [
            'rh_directions','rh_services','rh_postes','rh_employes',
            'rh_employe_documents','rh_bulletins_paie','rh_absences',
            'rh_prets','rh_heures_sup','rh_sanctions','rh_retards',
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

    // =====================================================
    // INDEX
    // =====================================================
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

        return view('admin.import-export', [
            'tableStats' => $tableStats,
            'driver' => $this->driver()
        ]);
    }

    // =====================================================
    // EXPORT JSON
    // =====================================================
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
                'source'  => 'Eden Group',
                'module'  => $module,
                'date'    => now()->toIso8601String(),
                'driver'  => $this->driver(),
                'version' => '3.0-json',
            ],
            'tables' => [],
        ];

        foreach ($tables as $table) {

            if (!Schema::hasTable($table)) continue;

            $export['tables'][$table] = DB::table($table)
                ->get()
                ->map(function ($row) {
                    return json_decode(json_encode($row), true);
                })
                ->toArray();
        }

        $json = json_encode(
            $export,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        );

        return response($json, 200, [
            'Content-Type' => 'application/json',
            'Content-Disposition' => 'attachment; filename="eden_export.json"',
        ]);
    }

    // =====================================================
    // IMPORT JSON (ULTRA STABLE)
    // =====================================================
    public function import(Request $request)
    {
        $request->validate([
            'fichier_sql' => 'required|file|mimes:json|max:102400',
            'confirmer'   => 'required|accepted',
            'mode'        => 'nullable|in:replace,merge',
        ]);

        $content = file_get_contents($request->file('fichier_sql')->getRealPath());
        $mode = $request->input('mode', 'merge');

        return $this->importJSON($content, $mode);
    }

    // =====================================================
    // IMPORT JSON CORE
    // =====================================================
    private function importJSON(string $content, string $mode)
    {
        $data = json_decode($content, true);

        if (!$data || !isset($data['tables'])) {
            return back()->with('error', '❌ JSON invalide ou corrompu');
        }

        $tables = $data['tables'];
        $isSQLite = $this->isSQLite();
        $count = 0;

        try {
            // disable FK
            if ($isSQLite) {
                DB::statement('PRAGMA foreign_keys=OFF');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
            }

            foreach ($tables as $table => $rows) {

                if (!Schema::hasTable($table)) continue;

                if ($mode === 'replace') {
                    DB::table($table)->delete();
                }

                foreach ($rows as $row) {

                    if (isset($row['id'])) {

                        DB::table($table)->updateOrInsert(
                            ['id' => $row['id']],
                            $row
                        );

                    } else {
                        DB::table($table)->insert($row);
                    }

                    $count++;
                }
            }

        } finally {
            if ($isSQLite) {
                DB::statement('PRAGMA foreign_keys=ON');
            } else {
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
            }
        }

        return back()->with('success', "✅ Import terminé : $count lignes importées");
    }
}