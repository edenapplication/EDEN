<?php

namespace App\Http\Controllers;

use App\Models\Bloc;
use App\Models\LotAffectation;
use App\Models\Affectation;
use App\Models\DossierClient;
use App\Models\GrandSite;
use App\Models\Site;
use App\Models\Tf;
use App\Services\HistoriqueService;      // ✅ AJOUT
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;      // ✅ AJOUT
use Illuminate\Support\Facades\Log;       // ✅ AJOUT
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LotsImport;
use App\Exports\LotsExport;
use App\Exports\LotsTemplateExport;

class AffectationController extends Controller
{
    // ============================================================
    // DASHBOARD
    // ============================================================
    public function index()
    {
        $grandSites = GrandSite::orderBy('nom')->get();
        $stats = [
            'blocs'       => Bloc::count(),
            'lots'        => LotAffectation::count(),
            'disponibles' => LotAffectation::where('disponible', true)->count(),
            'affectes'    => LotAffectation::where('disponible', false)->count(),
        ];
        return view('admin.affectations.index', compact('grandSites','stats'));
    }

    // ============================================================
    // BLOCS
    // ============================================================
    public function blocs(Request $request)
    {
        $query = Bloc::with(['grandSite','site','tf'])
                     ->withCount(['lots','lotsDisponibles']);

        if ($request->filled('grand_site_id')) $query->where('grand_site_id', $request->grand_site_id);
        if ($request->filled('tf_id'))         $query->where('tf_id', $request->tf_id);

        $blocs      = $query->orderBy('code')->get();
        $grandSites = GrandSite::orderBy('nom')->get();

        return view('admin.affectations.blocs', compact('blocs','grandSites'));
    }

    public function storeBloc(Request $request)
    {
        $request->validate([
            'grand_site_id' => 'required|exists:grand_sites,id',
            'site_id'       => 'nullable|exists:sites,id',
            'tf_id'         => 'nullable|exists:tfs,id',
            'codes'         => 'required|string',
        ]);

        $codes   = array_filter(array_map('trim', explode(';', $request->codes)));
        $created = 0;
        $skipped = 0;

        foreach ($codes as $code) {
            if (empty($code)) continue;
            $exists = Bloc::where('tf_id', $request->tf_id)
                          ->where('code', strtoupper($code))
                          ->exists();
            if ($exists) { $skipped++; continue; }

            Bloc::create([
                'grand_site_id' => $request->grand_site_id,
                'site_id'       => $request->site_id,
                'tf_id'         => $request->tf_id,
                'code'          => strtoupper($code),
                'description'   => $request->description,
            ]);
            $created++;
        }

        $msg = $created . ' bloc(s) créé(s)';
        if ($skipped) $msg .= ', ' . $skipped . ' ignoré(s) (déjà existant)';

        return back()->with('success', $msg);
    }

    public function updateBloc(Request $request, $id)
    {
        $bloc = Bloc::findOrFail($id);
        $request->validate(['code' => 'required|string|max:50']);
        $bloc->update([
            'code'        => strtoupper($request->code),
            'description' => $request->description,
            'actif'       => $request->boolean('actif', true),
        ]);
        return response()->json(['success' => true]);
    }

    public function destroyBloc($id)
    {
        $bloc = Bloc::findOrFail($id);
        if ($bloc->lots()->where('disponible', false)->exists()) {
            return response()->json(['success' => false, 'message' => 'Ce bloc contient des lots affectés.'], 422);
        }
        $bloc->delete();
        return response()->json(['success' => true]);
    }

    // ============================================================
    // LOTS
    // ============================================================
    public function lots(Request $request)
    {
        $query = LotAffectation::with(['grandSite','site','tf','bloc','affectation.client'])
                               ->orderBy('numero');

        if ($request->filled('bloc_id'))       $query->where('bloc_id', $request->bloc_id);
        if ($request->filled('disponible'))    $query->where('disponible', $request->disponible === '1');
        if ($request->filled('grand_site_id')) $query->where('grand_site_id', $request->grand_site_id);

        $lots       = $query->get();
        $grandSites = GrandSite::orderBy('nom')->get();
        $blocs      = Bloc::with('tf')->orderBy('code')->get();

        return view('admin.affectations.lots', compact('lots','grandSites','blocs'));
    }

    public function storeLots(Request $request)
    {
        $request->validate([
            'grand_site_id' => 'required|exists:grand_sites,id',
            'site_id'       => 'nullable|exists:sites,id',
            'tf_id'         => 'nullable|exists:tfs,id',
            'bloc_id'       => 'required|exists:blocs,id',
            'numeros'       => 'required|string',
        ]);

        $numeros = array_filter(array_map('trim', explode(';', $request->numeros)));
        $created = 0;
        $skipped = 0;

        foreach ($numeros as $numero) {
            if (empty($numero)) continue;
            $exists = LotAffectation::where('bloc_id', $request->bloc_id)
                                    ->where('numero', $numero)
                                    ->exists();
            if ($exists) { $skipped++; continue; }

            LotAffectation::create([
                'grand_site_id' => $request->grand_site_id,
                'site_id'       => $request->site_id,
                'tf_id'         => $request->tf_id,
                'bloc_id'       => $request->bloc_id,
                'numero'        => $numero,
                'superficie'    => $request->superficie ?: null,
            ]);
            $created++;
        }

        $msg = $created . ' lot(s) créé(s)';
        if ($skipped) $msg .= ', ' . $skipped . ' ignoré(s)';

        return back()->with('success', $msg);
    }

    public function updateLot(Request $request, $id)
    {
        $lot = LotAffectation::findOrFail($id);
        $lot->update([
            'numero'     => $request->numero,
            'superficie' => $request->superficie,
            'actif'      => $request->boolean('actif', true),
        ]);
        return response()->json(['success' => true]);
    }

    public function destroyLot($id)
    {
        $lot = LotAffectation::findOrFail($id);
        if (!$lot->disponible) {
            return response()->json(['success' => false, 'message' => 'Ce lot est déjà affecté.'], 422);
        }
        $lot->delete();
        return response()->json(['success' => true]);
    }

    public function updateSuperficieMultiple(Request $request)
    {
        $request->validate([
            'lot_ids'    => 'required|array|min:1',
            'lot_ids.*'  => 'exists:lots_affectation,id',
            'superficie' => 'required|numeric|min:0'
        ]);

        $updated = LotAffectation::whereIn('id', $request->lot_ids)
            ->where('disponible', true)
            ->update(['superficie' => $request->superficie]);

        if ($updated === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun lot disponible à modifier.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => $updated . ' lot(s) mis à jour',
            'updated' => $updated
        ]);
    }

    // ============================================================
    // ✅ IMPORT / EXPORT EXCEL
    // ============================================================

    /**
     * ✅ EXPORT — Télécharge les lots en Excel
     */
    public function exportLots(Request $request)
    {
        $filters = [
            'grand_site_id' => $request->grand_site_id,
            'bloc_id'       => $request->bloc_id,
            'disponible'    => $request->disponible,
        ];

        $filename = 'lots_' . date('Y-m-d_H-i') . '.xlsx';

        return Excel::download(new LotsExport($filters), $filename);
    }

    /**
     * ✅ TEMPLATE — Télécharge un fichier Excel vide pré-rempli
     */
    public function downloadTemplate()
    {
        $filename = 'modele_import_lots.xlsx';
        return Excel::download(new LotsTemplateExport(), $filename);
    }

    /**
     * ✅ IMPORT — Importe un fichier Excel
     */
    public function importLots(Request $request)
    {
        $request->validate([
            'fichier' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $import = new LotsImport();
            Excel::import($import, $request->file('fichier'));

            $parts = [];

            if ($import->imported > 0) {
                $parts[] = "✅ {$import->imported} lot(s) importé(s)";
            }
            if ($import->blocsCreated > 0) {
                $parts[] = "🏗️ {$import->blocsCreated} bloc(s) créé(s)";
            }
            if ($import->skipped > 0) {
                $parts[] = "⚠ {$import->skipped} bloc(s) ignoré(s)";
            }

            $msg = empty($parts) ? "Aucune donnée importée." : implode(' — ', $parts);

            $response = [
                'success'       => true,
                'message'       => $msg,
                'imported'      => $import->imported,
                'blocs_created' => $import->blocsCreated,
                'skipped'       => $import->skipped,
                'errors'        => array_slice($import->errors, 0, 20),
            ];

            if ($request->wantsJson()) {
                return response()->json($response);
            }

            return back()->with('success', $msg);

        } catch (\Exception $e) {
            $error = "Erreur d'import : " . $e->getMessage();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $error
                ], 422);
            }

            return back()->withErrors($error);
        }
    }

    // ============================================================
    // ✅ AFFECTATION À UN DOSSIER (avec historique)
    // ============================================================
    public function affecter(Request $request, DossierClient $dossier)
    {
        $request->validate([
            'lot_ids'          => 'required|array|min:1',
            'lot_ids.*'        => 'exists:lots_affectation,id',
            'date_affectation' => 'required|date',
            'notes'            => 'nullable|string',
        ]);

        $affectes     = 0;
        $errors       = [];
        $lotsAffectes = [];

        foreach ($request->lot_ids as $lotId) {
            $lot = LotAffectation::with('bloc')->find($lotId);

            if (!$lot || !$lot->disponible) {
                $errors[] = 'Lot ' . ($lot?->numero ?? $lotId) . ' non disponible.';
                continue;
            }

            Affectation::create([
                'grand_site_id'      => $lot->grand_site_id,
                'site_id'            => $lot->site_id,
                'tf_id'              => $lot->tf_id,
                'bloc_id'            => $lot->bloc_id,
                'lot_affectation_id' => $lot->id,
                'client_id'          => $dossier->client_id,
                'dossier_client_id'  => $dossier->id,
                'date_affectation'   => $request->date_affectation,
                'statut'             => 'actif',
                'notes'              => $request->notes,
            ]);

            $lot->update(['disponible' => false]);

            $lotsAffectes[] = [
                'lot_id'   => $lot->id,
                'numero'   => $lot->numero,
                'bloc'     => $lot->bloc?->code,
                'superficie' => $lot->superficie,
            ];

            $affectes++;
        }

        // ✅ HISTORIQUE : une seule entrée pour l'ensemble de l'affectation
        if ($affectes > 0) {
            $resumeLots = collect($lotsAffectes)
                ->map(fn($l) => "Lot {$l['numero']}" . ($l['bloc'] ? " (Bloc {$l['bloc']})" : ''))
                ->implode(', ');

            HistoriqueService::log(
                $dossier->id,
                'affectation_lot',
                "Affectation de {$affectes} lot(s) — {$resumeLots}",
                'lot',
                null,
                null,
                [
                    'lots'             => $lotsAffectes,
                    'date_affectation' => $request->date_affectation,
                    'notes'            => $request->notes,
                ]
            );
        }

        $msg = $affectes . ' lot(s) affecté(s).';
        if (!empty($errors)) $msg .= ' Erreurs : ' . implode(', ', $errors);

        return response()->json(['success' => true, 'message' => $msg]);
    }

    // ============================================================
    // ✅ ANNULER UNE AFFECTATION (avec historique)
    // ============================================================
    public function annuler(Affectation $affectation)
    {
        $affectation->load(['lot', 'bloc', 'dossier']);

        $dossier = $affectation->dossier;

        $avant = [
            'affectation_id' => $affectation->id,
            'lot_id'         => $affectation->lot_affectation_id,
            'lot_num'        => $affectation->lot?->numero,
            'bloc'           => $affectation->bloc?->code,
            'date'           => $affectation->date_affectation?->format('d/m/Y'),
            'notes'          => $affectation->notes,
        ];

        $affectation->lot?->update(['disponible' => true]);
        $affectation->update(['statut' => 'annule']);

        // ✅ HISTORIQUE
        if ($dossier) {
            HistoriqueService::log(
                $dossier->id,
                'annulation_affectation',
                "Annulation du lot {$avant['lot_num']}"
                    . ($avant['bloc'] ? " (Bloc {$avant['bloc']})" : '')
                    . " — affecté le {$avant['date']}",
                'lot',
                $avant['lot_id'],
                $avant,
                null
            );
        }

        return response()->json(['success' => true]);
    }

    // ============================================================
    // ✅ SUPPRIMER DÉFINITIVEMENT UNE AFFECTATION (avec historique)
    // ============================================================
    public function destroy(Affectation $affectation)
    {
        $affectation->load(['lot', 'bloc', 'dossier']);

        $dossier = $affectation->dossier;

        $avant = [
            'affectation_id' => $affectation->id,
            'lot_id'         => $affectation->lot_affectation_id,
            'lot_num'        => $affectation->lot?->numero,
            'bloc'           => $affectation->bloc?->code,
            'date'           => $affectation->date_affectation?->format('d/m/Y'),
            'statut'         => $affectation->statut,
            'notes'          => $affectation->notes,
        ];

        // Libérer le lot
        $affectation->lot?->update(['disponible' => true]);

        // Supprimer définitivement
        $affectation->delete();

        // ✅ HISTORIQUE
        if ($dossier) {
            HistoriqueService::log(
                $dossier->id,
                'annulation_affectation',
                "Suppression définitive du lot {$avant['lot_num']}"
                    . ($avant['bloc'] ? " (Bloc {$avant['bloc']})" : '')
                    . " — affecté le {$avant['date']}",
                'lot',
                $avant['lot_id'],
                $avant,
                null
            );
        }

        return response()->json(['success' => true]);
    }

    // ============================================================
    // ✅ HISTORIQUE D'UN DOSSIER (API)
    // ============================================================
    public function historique(DossierClient $dossier)
    {
        $historiques = $dossier->historiques()
            ->with('user:id,name')
            ->get()
            ->map(function ($h) {
                return [
                    'id'          => $h->id,
                    'type_action' => $h->type_action,
                    'icone'       => $h->icone,
                    'couleur'     => $h->couleur,
                    'resume'      => $h->resume,
                    'user'        => $h->user?->name,
                    'date'        => $h->created_at->format('d/m/Y H:i'),
                    'avant'       => $h->donnees_avant,
                    'apres'       => $h->donnees_apres,
                ];
            });

        return response()->json([
            'success'    => true,
            'historiques' => $historiques,
        ]);
    }

    // ============================================================
    // API JSON pour sélecteurs dynamiques
    // ============================================================
    public function apiSites(GrandSite $grandSite)
    {
        return response()->json(
            Site::where('grand_site_id', $grandSite->id)->orderBy('name')->get(['id','name'])
        );
    }

    public function apiTfs(Site $site)
    {
        return response()->json(
            Tf::where('site_id', $site->id)->orderBy('title')->get(['id','title'])
        );
    }

    public function apiBlocs(Tf $tf)
    {
        return response()->json(
            Bloc::where('tf_id', $tf->id)
                ->where('actif', true)
                ->orderBy('code')
                ->get(['id','code'])
        );
    }

    public function apiLots(Bloc $bloc)
    {
        return response()->json(
            LotAffectation::where('bloc_id', $bloc->id)
                ->where('disponible', true)
                ->where('actif', true)
                ->orderBy('numero')
                ->get(['id','numero','superficie'])
        );
    }

    // ============================================================
// ✅ AFFECTATION À UN BÉNÉFICIAIRE
// ============================================================
public function affecterBeneficiaire(Request $request, \App\Models\Beneficiaire $beneficiaire)
{
    $request->validate([
        'lot_ids'          => 'required|array|min:1',
        'lot_ids.*'        => 'exists:lots_affectation,id',
        'date_affectation' => 'required|date',
        'notes'            => 'nullable|string',
    ]);

    $dossier      = $beneficiaire->dossier;
    $affectes     = 0;
    $errors       = [];
    $lotsAffectes = [];

    foreach ($request->lot_ids as $lotId) {
        $lot = LotAffectation::with('bloc')->find($lotId);

        if (!$lot || !$lot->disponible) {
            $errors[] = 'Lot ' . ($lot?->numero ?? $lotId) . ' non disponible.';
            continue;
        }

        Affectation::create([
            'grand_site_id'      => $lot->grand_site_id,
            'site_id'            => $lot->site_id,
            'tf_id'              => $lot->tf_id,
            'bloc_id'            => $lot->bloc_id,
            'lot_affectation_id' => $lot->id,
            'client_id'          => $dossier->client_id,
            'dossier_client_id'  => $dossier->id,
            'beneficiaire_id'    => $beneficiaire->id,   // ✅
            'date_affectation'   => $request->date_affectation,
            'statut'             => 'actif',
            'notes'              => $request->notes,
        ]);

        $lot->update(['disponible' => false]);

        $lotsAffectes[] = [
            'lot_id'     => $lot->id,
            'numero'     => $lot->numero,
            'bloc'       => $lot->bloc?->code,
            'superficie' => $lot->superficie,
        ];

        $affectes++;
    }

    // ✅ HISTORIQUE
    if ($affectes > 0) {
        $resumeLots = collect($lotsAffectes)
            ->map(fn($l) => "Lot {$l['numero']}" . ($l['bloc'] ? " (Bloc {$l['bloc']})" : ''))
            ->implode(', ');

        HistoriqueService::log(
            $dossier->id,
            'affectation_lot',
            "Affectation de {$affectes} lot(s) au bénéficiaire « {$beneficiaire->nom} » — {$resumeLots}",
            'lot',
            null,
            null,
            [
                'beneficiaire_id'   => $beneficiaire->id,
                'beneficiaire_nom'  => $beneficiaire->nom,
                'lots'              => $lotsAffectes,
                'date_affectation'  => $request->date_affectation,
                'notes'             => $request->notes,
            ]
        );
    }

    $msg = $affectes . ' lot(s) affecté(s) au bénéficiaire.';
    if (!empty($errors)) $msg .= ' Erreurs : ' . implode(', ', $errors);

    return response()->json(['success' => true, 'message' => $msg]);
}

}