<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ImportExportController extends Controller
{
    // Tables à exporter/importer dans l'ordre (respect des FK)
    private array $tables = [
        'grand_sites', 'sites', 'commerciaux', 'clients',
        'conducteurs', 'facilitateurs', 'agents_commerciaux',
        'tfs', 'lots', 'dossiers_clients', 'paiements_dossier',
        'dossiers_techniques',
    ];

    public function index()
    {
        return view('admin.import_export.index');
    }

    public function export(Request $request)
    {
        $request->validate([
            'tables' => 'required|array',
        ]);

        $data = [];

        foreach ($request->tables as $table) {
            if (!in_array($table, $this->tables)) continue;
            if (!Schema::hasTable($table)) continue;
            $data[$table] = DB::table($table)->get()->toArray();
        }

        $json     = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $filename = 'eden_export_' . now()->format('Y-m-d_His') . '.json';

        return response($json, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:json',
            'mode'    => 'required|in:merge,replace',
        ]);

        $content = file_get_contents($request->file('fichier')->getRealPath());
        $data    = json_decode($content, true);

        if (!$data) {
            return back()->with('error', 'Fichier JSON invalide');
        }

        $imported = 0;
        $errors   = [];

        DB::transaction(function() use ($data, $request, &$imported, &$errors) {
            foreach ($this->tables as $table) {
                if (!isset($data[$table])) continue;
                if (!Schema::hasTable($table)) continue;

                try {
                    if ($request->mode === 'replace') {
                        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                        DB::table($table)->truncate();
                        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    }

                    foreach ($data[$table] as $row) {
                        $row = (array) $row;
                        if ($request->mode === 'merge') {
                            DB::table($table)->upsert($row, ['id'], array_keys($row));
                        } else {
                            DB::table($table)->insert($row);
                        }
                        $imported++;
                    }
                } catch (\Throwable $e) {
                    $errors[] = "Table {$table} : " . $e->getMessage();
                }
            }
        });

        if ($errors) {
            return back()->with('warning', 'Import partiel. Erreurs : ' . implode(' | ', $errors));
        }

        return back()->with('success', "{$imported} lignes importées avec succès");
    }
}