<?php

namespace App\Http\Controllers;

use App\Models\Bloc;
use App\Models\LotAffectation;
use App\Models\Affectation;
use App\Models\DossierClient;
use App\Models\GrandSite;
use App\Models\Site;
use App\Models\Tf;
use App\Models\Beneficiaire;
use App\Services\HistoriqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
    // IMPORT / EXPORT EXCEL
    // ============================================================
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

    public function downloadTemplate()
    {
        $filename = 'modele_import_lots.xlsx';
        return Excel::download(new LotsTemplateExport(), $filename);
    }

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
    // AFFECTATION À UN DOSSIER (avec historique)
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
                'lot_id'     => $lot->id,
                'numero'     => $lot->numero,
                'bloc'       => $lot->bloc?->code,
                'superficie' => $lot->superficie,
            ];

            $affectes++;
        }

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
    // ✅ AFFECTATION À UN BÉNÉFICIAIRE (avec historique)
    // ============================================================
    public function affecterBeneficiaire(Request $request, Beneficiaire $beneficiaire)
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

        if (!$dossier) {
            return response()->json([
                'success' => false,
                'message' => '❌ Ce bénéficiaire n\'est lié à aucun dossier.',
            ], 422);
        }

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
                'beneficiaire_id'    => $beneficiaire->id,
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
                'beneficiaire',
                $beneficiaire->id,
                null,
                [
                    'beneficiaire_id'  => $beneficiaire->id,
                    'beneficiaire_nom' => $beneficiaire->nom,
                    'lots'             => $lotsAffectes,
                    'date_affectation' => $request->date_affectation,
                    'notes'            => $request->notes,
                ],
                $beneficiaire->id
            );
        }

        $msg = $affectes . ' lot(s) affecté(s) au bénéficiaire.';
        if (!empty($errors)) $msg .= ' Erreurs : ' . implode(', ', $errors);

        return response()->json(['success' => true, 'message' => $msg]);
    }

    // ============================================================
    // ANNULER UNE AFFECTATION (statut = annule)
    // ============================================================
    public function annuler(Affectation $affectation)
    {
        $affectation->load(['lot', 'bloc', 'dossier', 'beneficiaire']);

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
                null,
                $affectation->beneficiaire_id
            );
        }

        return response()->json(['success' => true]);
    }

    // ============================================================
    // SUPPRIMER DÉFINITIVEMENT UNE AFFECTATION
    // ============================================================
    public function destroy(Affectation $affectation)
    {
        $affectation->load(['lot', 'bloc', 'dossier', 'beneficiaire']);

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

        $benefId = $affectation->beneficiaire_id;

        // Libérer le lot
        $affectation->lot?->update(['disponible' => true]);

        // Supprimer définitivement
        $affectation->delete();

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
                null,
                $benefId
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
            ->orderByDesc('created_at')
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
            'success'     => true,
            'historiques' => $historiques,
        ]);
    }

    // ============================================================
    // ✅ HISTORIQUE D'UN BÉNÉFICIAIRE (API)
    // ============================================================
    public function historiqueBeneficiaire(Beneficiaire $beneficiaire)
    {
        $dossier = $beneficiaire->dossier;

        if (!$dossier) {
            return response()->json([
                'success'     => true,
                'historiques' => [],
            ]);
        }

        $historiques = $dossier->historiques()
            ->where('beneficiaire_id', $beneficiaire->id)
            ->with('user:id,name')
            ->orderByDesc('created_at')
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
            'success'     => true,
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
// ✅ MODIFIER UNE AFFECTATION (avec support échange + historique enrichi)
// ============================================================
public function update(Request $request, Affectation $affectation)
{
    try {
        $request->validate([
            'lot_affectation_id' => 'nullable|exists:lots_affectation,id',
            'date_affectation'   => 'required|date',
            'notes'              => 'nullable|string',
        ]);

        $affectation->load(['lot', 'bloc', 'dossier', 'beneficiaire']);

        $dossier = $affectation->dossier;

        // ═══════════════════════════════════════════════════════════
        // 📸 SNAPSHOT AVANT (état complet)
        // ═══════════════════════════════════════════════════════════
        $avant = [
            'affectation_id' => $affectation->id,
            'lot_id'         => $affectation->lot_affectation_id,
            'lot_num'        => $affectation->lot?->numero,
            'bloc'           => $affectation->bloc?->code,
            'bloc_id'        => $affectation->bloc_id,
            'grand_site'     => $affectation->grandSite?->nom,
            'date'           => $affectation->date_affectation?->format('Y-m-d'),
            'date_formatee'  => $affectation->date_affectation?->format('d/m/Y'),
            'notes'          => $affectation->notes,
            'beneficiaire_nom' => $affectation->beneficiaire?->nom,
        ];

        $nouveauLotId = $request->lot_affectation_id;
        $lotChange    = false;
        $isEchange    = false;
        $ancienLotEchange = null;

        // ═══════════════════════════════════════════════════════════
        // 🔄 CHANGEMENT DE LOT
        // ═══════════════════════════════════════════════════════════
        if ($nouveauLotId && $nouveauLotId != $affectation->lot_affectation_id) {
            $nouveauLot = LotAffectation::with('bloc')->find($nouveauLotId);

            if (!$nouveauLot) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Lot introuvable.',
                ], 422);
            }

            // ✅ RÈGLE 1 : Le nouveau lot doit être dans le même bloc
            if ($nouveauLot->bloc_id != $affectation->bloc_id) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Le nouveau lot doit être dans le même bloc ('
                               . ($affectation->bloc?->code ?? '?') . ').',
                ], 422);
            }

            // ✅ RÈGLE 2 : Vérifier si le lot est déjà affecté
            $affectationExistante = Affectation::where('lot_affectation_id', $nouveauLot->id)
                ->where('statut', 'actif')
                ->where('id', '!=', $affectation->id)
                ->first();

            if ($affectationExistante) {
                // ❌ Occupé par quelqu'un d'autre
                if ($affectationExistante->beneficiaire_id != $affectation->beneficiaire_id) {
                    return response()->json([
                        'success' => false,
                        'message' => '❌ Le lot ' . $nouveauLot->numero . ' est déjà affecté à '
                                   . ($affectationExistante->beneficiaire?->nom ?? 'quelqu\'un d\'autre') . '.',
                    ], 422);
                }

                // ✅ ÉCHANGE : Le lot appartient au même bénéficiaire
                $ancienLot = $affectation->lot;
                $ancienLotEchange = $ancienLot;

                // L'affectation existante prend l'ancien lot
                $affectationExistante->update([
                    'lot_affectation_id' => $ancienLot->id,
                    'grand_site_id'      => $ancienLot->grand_site_id,
                    'site_id'            => $ancienLot->site_id,
                    'tf_id'              => $ancienLot->tf_id,
                    'bloc_id'            => $ancienLot->bloc_id,
                ]);

                // Notre affectation prend le nouveau lot
                $affectation->update([
                    'lot_affectation_id' => $nouveauLot->id,
                    'grand_site_id'      => $nouveauLot->grand_site_id,
                    'site_id'            => $nouveauLot->site_id,
                    'tf_id'              => $nouveauLot->tf_id,
                    'bloc_id'            => $nouveauLot->bloc_id,
                ]);

                $lotChange = true;
                $isEchange = true;

            } else {
                // ✅ Le lot est libre → affectation normale
                $affectation->lot?->update(['disponible' => true]);
                $nouveauLot->update(['disponible' => false]);

                $affectation->update([
                    'lot_affectation_id' => $nouveauLot->id,
                    'grand_site_id'      => $nouveauLot->grand_site_id,
                    'site_id'            => $nouveauLot->site_id,
                    'tf_id'              => $nouveauLot->tf_id,
                    'bloc_id'            => $nouveauLot->bloc_id,
                ]);

                $lotChange = true;
            }
        }

        // ═══════════════════════════════════════════════════════════
        // 📅 MISE À JOUR DATE + NOTES
        // ═══════════════════════════════════════════════════════════
        $affectation->update([
            'date_affectation' => $request->date_affectation,
            'notes'            => $request->notes,
        ]);

        $affectation->refresh();
        $affectation->load(['lot', 'bloc', 'beneficiaire']);

        // ═══════════════════════════════════════════════════════════
        // 📸 SNAPSHOT APRÈS (état complet)
        // ═══════════════════════════════════════════════════════════
        $apres = [
            'affectation_id' => $affectation->id,
            'lot_id'         => $affectation->lot_affectation_id,
            'lot_num'        => $affectation->lot?->numero,
            'bloc'           => $affectation->bloc?->code,
            'bloc_id'        => $affectation->bloc_id,
            'grand_site'     => $affectation->grandSite?->nom,
            'date'           => $affectation->date_affectation?->format('Y-m-d'),
            'date_formatee'  => $affectation->date_affectation?->format('d/m/Y'),
            'notes'          => $affectation->notes,
            'beneficiaire_nom' => $affectation->beneficiaire?->nom,
        ];

        // ═══════════════════════════════════════════════════════════
        // ✅ CONSTRUIRE LE RÉSUMÉ DÉTAILLÉ
        // ═══════════════════════════════════════════════════════════
        $changements = [];
        $detailsChangements = [
            'lot'   => null,
            'date'  => null,
            'notes' => null,
        ];

        // 📦 CHANGEMENT DE LOT
        if ($lotChange) {
            $changements[] = "Lot : {$avant['lot_num']} → {$apres['lot_num']}";
            $detailsChangements['lot'] = [
                'avant' => [
                    'numero' => $avant['lot_num'],
                    'bloc'   => $avant['bloc'],
                ],
                'apres' => [
                    'numero' => $apres['lot_num'],
                    'bloc'   => $apres['bloc'],
                ],
            ];
        }

        // 📅 CHANGEMENT DE DATE
        if (($avant['date'] ?? null) !== ($apres['date'] ?? null)) {
            $d1 = $avant['date'] 
                ? \Carbon\Carbon::parse($avant['date'])->format('d/m/Y') 
                : '—';
            $d2 = $apres['date'] 
                ? \Carbon\Carbon::parse($apres['date'])->format('d/m/Y') 
                : '—';
            $changements[] = "Date : {$d1} → {$d2}";
            $detailsChangements['date'] = [
                'avant' => $d1,
                'apres' => $d2,
            ];
        }

        // 📝 CHANGEMENT DE NOTES
        $noteAvant = $avant['notes'] ?? null;
        $noteApres = $apres['notes'] ?? null;

        if ($noteAvant !== $noteApres) {
            $changements[] = "Notes modifiées";
            $detailsChangements['notes'] = [
                'avant' => $noteAvant,
                'apres' => $noteApres,
            ];
        }

        $resume = empty($changements)
            ? "Aucun changement détecté"
            : implode(' ; ', $changements);

        // ═══════════════════════════════════════════════════════════
        // 📜 HISTORIQUE (si modifications réelles)
        // ═══════════════════════════════════════════════════════════
        $hasChanges = $lotChange 
            || (($avant['date'] ?? null) !== ($apres['date'] ?? null)) 
            || ($noteAvant !== $noteApres);

        if ($dossier && $hasChanges) {
            // Résumé enrichi
            $typeResume = $isEchange ? "🔄 Échange de lots" : "Modification";
            $nomComplet = $typeResume 
                . " du lot {$apres['lot_num']}"
                . ($apres['bloc'] ? " (Bloc {$apres['bloc']})" : '')
                . " — {$resume}"
                . ($affectation->beneficiaire
                    ? " — Bénéficiaire : « {$affectation->beneficiaire->nom} »"
                    : '');

            // ✅ Historique avec données enrichies
            HistoriqueService::log(
                $dossier->id,
                'modification_affectation',
                $nomComplet,
                'affectation',
                $affectation->id,
                // ✅ AVANT enrichi
                array_merge($avant, [
                    'beneficiaire_nom' => $affectation->beneficiaire?->nom,
                    'date_formatee'    => $avant['date'] 
                        ? \Carbon\Carbon::parse($avant['date'])->format('d/m/Y') 
                        : null,
                    'point_depart'     => [
                        'lot'   => $avant['lot_num'],
                        'bloc'  => $avant['bloc'],
                        'date'  => $avant['date'],
                        'notes' => $avant['notes'],
                    ],
                ]),
                // ✅ APRÈS enrichi
                array_merge($apres, [
                    'beneficiaire_nom'  => $affectation->beneficiaire?->nom,
                    'date_formatee'     => $apres['date'] 
                        ? \Carbon\Carbon::parse($apres['date'])->format('d/m/Y') 
                        : null,
                    'changements'       => $detailsChangements,
                    'is_echange'        => $isEchange,
                    'point_depart'      => [
                        'lot'   => $avant['lot_num'],
                        'bloc'  => $avant['bloc'],
                        'date'  => $avant['date'],
                        'notes' => $avant['notes'],
                    ],
                    'point_arrivee'     => [
                        'lot'   => $apres['lot_num'],
                        'bloc'  => $apres['bloc'],
                        'date'  => $apres['date'],
                        'notes' => $apres['notes'],
                    ],
                ]),
                $affectation->beneficiaire_id
            );
        }

        // ═══════════════════════════════════════════════════════════
        // ✅ RÉPONSE
        // ═══════════════════════════════════════════════════════════
        return response()->json([
            'success'   => true,
            'message'   => $isEchange 
                ? '✅ Échange de lots effectué.'
                : '✅ Affectation modifiée.',
            'avant'     => $avant,
            'apres'     => $apres,
            'resume'    => $resume,
            'is_echange' => $isEchange,
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors'  => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('❌ Erreur update affectation: ' . $e->getMessage(), [
            'affectation_id' => $affectation->id ?? null,
            'trace'          => $e->getTraceAsString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

// ============================================================
// ✅ MODIFIER LES LOTS D'UN BÉNÉFICIAIRE (multi-lots)
// ============================================================
public function modifierLots(Request $request, Beneficiaire $beneficiaire)
{
    try {
        $request->validate([
            'date_affectation' => 'required|date',
            'notes'            => 'nullable|string',
            'lots_a_retirer'   => 'nullable|array',
            'lots_a_retirer.*' => 'exists:affectations,id',
            'lots_a_ajouter'   => 'nullable|array',
            'lots_a_ajouter.*' => 'exists:lots_affectation,id',
        ]);

        $dossier = $beneficiaire->dossier;
        if (!$dossier) {
            return response()->json([
                'success' => false,
                'message' => '❌ Bénéficiaire sans dossier.',
            ], 422);
        }

        $date  = $request->date_affectation;
        $notes = $request->notes;

        // ═══════════════════════════════════════════════════════
        // 📸 SNAPSHOT AVANT — État complet du bénéficiaire
        // ═══════════════════════════════════════════════════════
        $avantAffectations = Affectation::with(['lot', 'bloc', 'grandSite'])
            ->where('beneficiaire_id', $beneficiaire->id)
            ->where('dossier_client_id', $dossier->id)
            ->where('statut', 'actif')
            ->get();

        $avantLotsList = $avantAffectations->map(function ($aff) {
            return [
                'affectation_id' => $aff->id,
                'lot_id'         => $aff->lot_affectation_id,
                'numero'         => $aff->lot?->numero,
                'bloc'           => $aff->bloc?->code,
                'superficie'     => $aff->lot?->superficie,
            ];
        })->values()->toArray();

        // ✅ Point de départ COMPLET (utilisé ensuite dans l'historique)
        $pointDepart = [
            'lot'   => implode(', ', array_filter(array_column($avantLotsList, 'numero'))),
            'bloc'  => $avantAffectations->first()?->bloc?->code,
            'date'  => $avantAffectations->first()?->date_affectation?->format('Y-m-d'),
            'notes' => $avantAffectations->first()?->notes,
        ];

        $avant = [
            'lots'             => $avantLotsList,
            'nb_lots'          => count($avantLotsList),
            'date'             => $avantAffectations->first()?->date_affectation?->format('Y-m-d'),
            'notes'            => $avantAffectations->first()?->notes,
            'beneficiaire_nom' => $beneficiaire->nom,
            'point_depart'     => $pointDepart,
        ];

        // ═══════════════════════════════════════════════════════
        // 1. RETIRER LES LOTS
        // ═══════════════════════════════════════════════════════
        $lotsRetires = [];
        if (!empty($request->lots_a_retirer)) {
            foreach ($request->lots_a_retirer as $affId) {
                $aff = Affectation::with('lot', 'bloc')->find($affId);

                if (!$aff) continue;
                if ($aff->beneficiaire_id != $beneficiaire->id) continue;

                $lotsRetires[] = [
                    'affectation_id' => $aff->id,
                    'lot_id'         => $aff->lot_affectation_id,
                    'numero'         => $aff->lot?->numero,
                    'bloc'           => $aff->bloc?->code,
                    'superficie'     => $aff->lot?->superficie,
                ];

                // Libérer le lot
                $aff->lot?->update(['disponible' => true]);

                // Supprimer l'affectation
                $aff->delete();
            }
        }

        // ═══════════════════════════════════════════════════════
        // 2. AJOUTER LES LOTS
        // ═══════════════════════════════════════════════════════
        $lotsAjoutes = [];
        if (!empty($request->lots_a_ajouter)) {
            foreach ($request->lots_a_ajouter as $lotId) {
                $lot = LotAffectation::with('bloc')->find($lotId);

                if (!$lot || !$lot->disponible) continue;

                Affectation::create([
                    'grand_site_id'      => $lot->grand_site_id,
                    'site_id'            => $lot->site_id,
                    'tf_id'              => $lot->tf_id,
                    'bloc_id'            => $lot->bloc_id,
                    'lot_affectation_id' => $lot->id,
                    'client_id'          => $dossier->client_id,
                    'dossier_client_id'  => $dossier->id,
                    'beneficiaire_id'    => $beneficiaire->id,
                    'date_affectation'   => $date,
                    'statut'             => 'actif',
                    'notes'              => $notes,
                ]);

                $lot->update(['disponible' => false]);

                $lotsAjoutes[] = [
                    'lot_id'     => $lot->id,
                    'numero'     => $lot->numero,
                    'bloc'       => $lot->bloc?->code,
                    'superficie' => $lot->superficie,
                ];
            }
        }

        // ═══════════════════════════════════════════════════════
        // 3. METTRE À JOUR DATE + NOTES DES LOTS CONSERVÉS
        // ═══════════════════════════════════════════════════════
        $lotsConserves = array_diff(
            array_column($avantLotsList, 'affectation_id'),
            array_column($lotsRetires, 'affectation_id')
        );

        if (!empty($lotsConserves)) {
            Affectation::whereIn('id', $lotsConserves)
                ->update([
                    'date_affectation' => $date,
                    'notes'            => $notes,
                ]);
        }

        // ═══════════════════════════════════════════════════════
        // 📸 SNAPSHOT APRÈS — État complet du bénéficiaire
        // ═══════════════════════════════════════════════════════
        $apresAffectations = Affectation::with(['lot', 'bloc'])
            ->where('beneficiaire_id', $beneficiaire->id)
            ->where('dossier_client_id', $dossier->id)
            ->where('statut', 'actif')
            ->get();

        $apresLotsList = $apresAffectations->map(function ($aff) {
            return [
                'affectation_id' => $aff->id,
                'lot_id'         => $aff->lot_affectation_id,
                'numero'         => $aff->lot?->numero,
                'bloc'           => $aff->bloc?->code,
                'superficie'     => $aff->lot?->superficie,
            ];
        })->values()->toArray();

        // ✅ Point d'arrivée COMPLET
        $pointArrivee = [
            'lot'   => implode(', ', array_filter(array_column($apresLotsList, 'numero'))),
            'bloc'  => $apresAffectations->first()?->bloc?->code,
            'date'  => $date,
            'notes' => $notes,
        ];

        $apres = [
            'lots'             => $apresLotsList,
            'nb_lots'          => count($apresLotsList),
            'date'             => $date,
            'notes'            => $notes,
            'beneficiaire_nom' => $beneficiaire->nom,
            'point_depart'     => $pointDepart,      // ✅ AUSSI dans apres (pour le JS)
            'point_arrivee'    => $pointArrivee,
        ];

        // ═══════════════════════════════════════════════════════
        // 4. CONSTRUIRE LE RÉSUMÉ
        // ═══════════════════════════════════════════════════════
        $messages = [];

        if (!empty($lotsRetires)) {
            $noms = collect($lotsRetires)
                ->map(fn($l) => "Lot {$l['numero']}" . ($l['bloc'] ? " ({$l['bloc']})" : ''))
                ->implode(', ');
            $messages[] = "🗑️ Retiré : {$noms}";
        }

        if (!empty($lotsAjoutes)) {
            $noms = collect($lotsAjoutes)
                ->map(fn($l) => "Lot {$l['numero']}" . ($l['bloc'] ? " ({$l['bloc']})" : ''))
                ->implode(', ');
            $messages[] = "➕ Ajouté : {$noms}";
        }

        $resume = empty($messages)
            ? "Aucun changement de lots"
            : implode(' | ', $messages);

        // ✅ Nom complet du résumé (utilisé dans l'historique)
        $nomComplet = "Modification des lots de « {$beneficiaire->nom} »"
            . " — {$resume}"
            . " (Avant : " . count($avantLotsList) . " lot(s)"
            . " → Après : " . count($apresLotsList) . " lot(s))";

        // ═══════════════════════════════════════════════════════
        // 5. HISTORIQUE ENRICHI (avec point_depart / point_arrivee)
        // ═══════════════════════════════════════════════════════
        if (!empty($lotsRetires) || !empty($lotsAjoutes)) {
            // ✅ Enrichir AVANT avec point_depart
            $avantEnrichi = array_merge($avant, [
                'point_depart' => $pointDepart,
                'lots'         => $avantLotsList,
                'nb_lots'      => count($avantLotsList),
            ]);

            // ✅ Enrichir APRES avec point_arrivee ET point_depart
            $apresEnrichi = array_merge($apres, [
                'point_arrivee' => $pointArrivee,
                'point_depart'  => $pointDepart,
                'lots'          => $apresLotsList,
                'nb_lots'       => count($apresLotsList),
            ]);

            HistoriqueService::log(
                $dossier->id,
                'modification_affectation',
                $nomComplet,
                'beneficiaire',
                $beneficiaire->id,
                $avantEnrichi,
                $apresEnrichi,
                $beneficiaire->id
            );
        }

        $total = count($lotsRetires) + count($lotsAjoutes);

        return response()->json([
            'success'   => true,
            'message'   => "✅ {$total} modification(s) appliquée(s).",
            'retires'   => $lotsRetires,
            'ajoutes'   => $lotsAjoutes,
            'avant'     => $avant,
            'apres'     => $apres,
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors'  => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Erreur modifierLots: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

}