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
use App\Models\Client;
use App\Models\DocumentPdf;
use App\Models\HistoriqueAffectation;
use App\Services\HistoriqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\LotsImport;
use App\Exports\LotsExport;
use App\Exports\LotsTemplateExport;

class AffectationController extends Controller
{
    // ============================================================
    // 🔒 HELPER : informations sur les affectations en attente
    // ============================================================
    private function verifierBlocageCreation(): array
    {
        $debutSemaineDerniere = now()->subWeek()->startOfWeek();
        $finSemaineDerniere   = now()->subWeek()->endOfWeek();

        $count = Affectation::where('statut', 'actif')
            ->where('statut_acceptation', 'en_attente')
            ->whereBetween('date_implantation', [$debutSemaineDerniere, $finSemaineDerniere])
            ->count();

        return [
            'bloque'  => $count > 0,
            'count'   => $count,
            'message' => $count > 0
                ? "⚠ {$count} affectation(s) de la semaine dernière sont encore en attente. Pensez à les traiter."
                : null,
        ];
    }

    // ============================================================
    // DASHBOARD
    // ============================================================
   public function index()
{
    $grandSites = GrandSite::with([
        'sites.tfs'   // ✅ Charge sites + tfs en 2 requêtes
    ])->orderBy('nom')->get();

    $stats = [
        'blocs'       => Bloc::count(),
        'lots'        => LotAffectation::count(),
        'disponibles' => LotAffectation::where('disponible', true)->count(),
        'affectes'    => LotAffectation::where('disponible', false)->count(),
    ];

    return view('admin.affectations.index', compact('grandSites', 'stats'));
}

    // ============================================================
    // BLOCS
    // ============================================================
    public function blocs(Request $request)
    {
        $query = Bloc::with(['grandSite', 'site', 'tf'])
                     ->withCount(['lots', 'lotsDisponibles']);

        if ($request->filled('grand_site_id')) $query->where('grand_site_id', $request->grand_site_id);
        if ($request->filled('tf_id'))         $query->where('tf_id', $request->tf_id);

        $blocs      = $query->orderBy('code')->get();
        $grandSites = GrandSite::orderBy('nom')->get();

        return view('admin.affectations.blocs', compact('blocs', 'grandSites'));
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
    $query = LotAffectation::with([
                    'grandSite',
                    'site',
                    'tf',
                    'bloc',
                    'affectation.client'
                ])
                // ✅ TRI par ordre de création (du plus ancien au plus récent)
                ->orderBy('grand_site_id')
                ->orderBy('site_id')
                ->orderBy('tf_id')
                ->orderBy('bloc_id')
                ->orderBy('id');   // ← garantit l'ordre de création à l'intérieur d'un bloc

    // ✅ Filtres en cascade
    if ($request->filled('grand_site_id')) {
        $query->where('grand_site_id', $request->grand_site_id);
    }
    if ($request->filled('site_id')) {
        $query->where('site_id', $request->site_id);
    }
    if ($request->filled('tf_id')) {
        $query->where('tf_id', $request->tf_id);
    }
    if ($request->filled('bloc_id')) {
        $query->where('bloc_id', $request->bloc_id);
    }
    if ($request->filled('disponible')) {
        $query->where('disponible', $request->disponible === '1');
    }

    $lots       = $query->get();
    $grandSites = GrandSite::orderBy('nom')->get();
    $blocs      = Bloc::with('tf')->orderBy('code')->get();

    // ✅ Pour les filtres dynamiques : charger les sites/tfs/blocs selon les filtres actuels
    $sites = collect();
    $tfs   = collect();

    if ($request->filled('grand_site_id')) {
        $sites = Site::where('grand_site_id', $request->grand_site_id)
            ->orderBy('name')
            ->get(['id', 'name', 'grand_site_id']);
    } else {
        $sites = Site::orderBy('name')->get(['id', 'name', 'grand_site_id']);
    }

    if ($request->filled('site_id')) {
        $tfs = Tf::where('site_id', $request->site_id)
            ->orderBy('title')
            ->get(['id', 'title', 'site_id']);
    } elseif ($request->filled('grand_site_id')) {
        $tfs = Tf::whereHas('site', function ($q) use ($request) {
            $q->where('grand_site_id', $request->grand_site_id);
        })->orderBy('title')->get(['id', 'title', 'site_id']);
    } else {
        $tfs = Tf::orderBy('title')->get(['id', 'title', 'site_id']);
    }

    if ($request->filled('tf_id')) {
        $blocs = Bloc::where('tf_id', $request->tf_id)
            ->orderBy('code')
            ->get(['id', 'code', 'tf_id']);
    } elseif ($request->filled('site_id')) {
        $blocs = Bloc::whereHas('tf', function ($q) use ($request) {
            $q->where('site_id', $request->site_id);
        })->orderBy('code')->get(['id', 'code', 'tf_id']);
    } elseif ($request->filled('grand_site_id')) {
        $blocs = Bloc::whereHas('tf.site', function ($q) use ($request) {
            $q->where('grand_site_id', $request->grand_site_id);
        })->orderBy('code')->get(['id', 'code', 'tf_id']);
    } else {
        $blocs = Bloc::with('tf')->orderBy('code')->get(['id', 'code', 'tf_id']);
    }

    return view('admin.affectations.lots', compact(
        'lots', 'grandSites', 'sites', 'tfs', 'blocs'
    ));
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
    // AFFECTATION À UN DOSSIER
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

            $dejaActif = Affectation::where('lot_affectation_id', $lot->id)
                ->where('statut', 'actif')
                ->exists();

            if ($dejaActif) {
                $errors[] = 'Lot ' . $lot->numero . ' a déjà une affectation active.';
                continue;
            }

            Affectation::create([
                'grand_site_id'       => $lot->grand_site_id,
                'site_id'             => $lot->site_id,
                'tf_id'               => $lot->tf_id,
                'bloc_id'             => $lot->bloc_id,
                'lot_affectation_id'  => $lot->id,
                'client_id'           => $dossier->client_id,
                'dossier_client_id'   => $dossier->id,
                'date_affectation'    => $request->date_affectation,
                'statut'              => 'actif',
                'etape_programmation' => 'nouvelle',
                'notes'               => $request->notes,
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
    // ✅ AFFECTATION À UN BÉNÉFICIAIRE
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

            $dejaActif = Affectation::where('lot_affectation_id', $lot->id)
                ->where('statut', 'actif')
                ->exists();

            if ($dejaActif) {
                $errors[] = 'Lot ' . $lot->numero . ' a déjà une affectation active.';
                continue;
            }

            Affectation::create([
                'grand_site_id'       => $lot->grand_site_id,
                'site_id'             => $lot->site_id,
                'tf_id'               => $lot->tf_id,
                'bloc_id'             => $lot->bloc_id,
                'lot_affectation_id'  => $lot->id,
                'client_id'           => $dossier->client_id,
                'dossier_client_id'   => $dossier->id,
                'beneficiaire_id'     => $beneficiaire->id,
                'date_affectation'    => $request->date_affectation,
                'statut'              => 'actif',
                'etape_programmation' => 'nouvelle',
                'notes'               => $request->notes,
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
            $nouvelleSuperficie = Affectation::where('beneficiaire_id', $beneficiaire->id)
                ->where('statut', 'actif')
                ->join('lots_affectation', 'affectations.lot_affectation_id', '=', 'lots_affectation.id')
                ->sum('lots_affectation.superficie');

            $nouveauxLotsTexte = Affectation::where('beneficiaire_id', $beneficiaire->id)
                ->where('statut', 'actif')
                ->with('lot')
                ->get()
                ->pluck('lot.numero')
                ->filter()
                ->unique()
                ->implode(', ');

            $beneficiaire->update([
                'superficie_attribuee' => $nouvelleSuperficie,
                'lots_texte'           => $nouveauxLotsTexte,
            ]);

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
    // ANNULER UNE AFFECTATION
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

        if ($affectation->beneficiaire_id) {
            $benef = Beneficiaire::find($affectation->beneficiaire_id);
            if ($benef) {
                $nouvelleSuperficie = Affectation::where('beneficiaire_id', $benef->id)
                    ->where('statut', 'actif')
                    ->join('lots_affectation', 'affectations.lot_affectation_id', '=', 'lots_affectation.id')
                    ->sum('lots_affectation.superficie');

                $nouveauxLotsTexte = Affectation::where('beneficiaire_id', $benef->id)
                    ->where('statut', 'actif')
                    ->with('lot')
                    ->get()
                    ->pluck('lot.numero')
                    ->filter()
                    ->unique()
                    ->implode(', ');

                $benef->update([
                    'superficie_attribuee' => $nouvelleSuperficie,
                    'lots_texte'           => $nouveauxLotsTexte,
                ]);
            }
        }

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

        $affectation->lot?->update(['disponible' => true]);
        $affectation->delete();

        if ($benefId) {
            $benef = Beneficiaire::find($benefId);
            if ($benef) {
                $nouvelleSuperficie = Affectation::where('beneficiaire_id', $benef->id)
                    ->where('statut', 'actif')
                    ->join('lots_affectation', 'affectations.lot_affectation_id', '=', 'lots_affectation.id')
                    ->sum('lots_affectation.superficie');

                $nouveauxLotsTexte = Affectation::where('beneficiaire_id', $benef->id)
                    ->where('statut', 'actif')
                    ->with('lot')
                    ->get()
                    ->pluck('lot.numero')
                    ->filter()
                    ->unique()
                    ->implode(', ');

                $benef->update([
                    'superficie_attribuee' => $nouvelleSuperficie,
                    'lots_texte'           => $nouveauxLotsTexte,
                ]);
            }
        }

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
            Site::where('grand_site_id', $grandSite->id)->orderBy('name')->get(['id', 'name'])
        );
    }

    public function apiTfs(Site $site)
    {
        return response()->json(
            Tf::where('site_id', $site->id)->orderBy('title')->get(['id', 'title'])
        );
    }

    public function apiBlocs(Tf $tf)
    {
        return response()->json(
            Bloc::where('tf_id', $tf->id)
                ->where('actif', true)
                ->orderBy('code')
                ->get(['id', 'code'])
        );
    }

    public function apiLots(Bloc $bloc)
    {
        return response()->json(
            LotAffectation::where('bloc_id', $bloc->id)
                ->where('disponible', true)
                ->where('actif', true)
                ->orderBy('numero')
                ->get(['id', 'numero', 'superficie'])
        );
    }

    // ============================================================
    // ✅ MODIFIER UNE AFFECTATION
    // ============================================================
    public function update(Request $request, Affectation $affectation)
    {
        try {
            if ($affectation->statut_acceptation && $affectation->statut_acceptation !== 'en_attente') {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Cette affectation est figée (déjà acceptée ou refusée).',
                ], 422);
            }

            $request->validate([
                'lot_affectation_id' => 'nullable|exists:lots_affectation,id',
                'date_affectation'   => 'required|date',
                'notes'              => 'nullable|string',
            ]);

            $affectation->load(['lot', 'bloc', 'dossier', 'beneficiaire']);

            $dossier = $affectation->dossier;

            $avant = [
                'affectation_id'   => $affectation->id,
                'lot_id'           => $affectation->lot_affectation_id,
                'lot_num'          => $affectation->lot?->numero,
                'bloc'             => $affectation->bloc?->code,
                'bloc_id'          => $affectation->bloc_id,
                'grand_site'       => $affectation->grandSite?->nom,
                'date'             => $affectation->date_affectation?->format('Y-m-d'),
                'date_formatee'    => $affectation->date_affectation?->format('d/m/Y'),
                'notes'            => $affectation->notes,
                'beneficiaire_nom' => $affectation->beneficiaire?->nom,
            ];

            $nouveauLotId = $request->lot_affectation_id;
            $lotChange    = false;
            $isEchange    = false;

            if ($nouveauLotId && $nouveauLotId != $affectation->lot_affectation_id) {
                $nouveauLot = LotAffectation::with('bloc')->find($nouveauLotId);

                if (!$nouveauLot) {
                    return response()->json([
                        'success' => false,
                        'message' => '❌ Lot introuvable.',
                    ], 422);
                }

                if ($nouveauLot->bloc_id != $affectation->bloc_id) {
                    return response()->json([
                        'success' => false,
                        'message' => '❌ Le nouveau lot doit être dans le même bloc ('
                                   . ($affectation->bloc?->code ?? '?') . ').',
                    ], 422);
                }

                $affectationExistante = Affectation::where('lot_affectation_id', $nouveauLot->id)
                    ->where('statut', 'actif')
                    ->where('id', '!=', $affectation->id)
                    ->first();

                if ($affectationExistante) {
                    if ($affectationExistante->beneficiaire_id != $affectation->beneficiaire_id) {
                        return response()->json([
                            'success' => false,
                            'message' => '❌ Le lot ' . $nouveauLot->numero . ' est déjà affecté à '
                                       . ($affectationExistante->beneficiaire?->nom ?? 'quelqu\'un d\'autre') . '.',
                        ], 422);
                    }

                    $ancienLot = $affectation->lot;

                    $affectationExistante->update([
                        'lot_affectation_id' => $ancienLot->id,
                        'grand_site_id'      => $ancienLot->grand_site_id,
                        'site_id'            => $ancienLot->site_id,
                        'tf_id'              => $ancienLot->tf_id,
                        'bloc_id'            => $ancienLot->bloc_id,
                    ]);

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

            $affectation->update([
                'date_affectation' => $request->date_affectation,
                'notes'            => $request->notes,
            ]);

            $affectation->refresh();
            $affectation->load(['lot', 'bloc', 'beneficiaire']);

            $apres = [
                'affectation_id'   => $affectation->id,
                'lot_id'           => $affectation->lot_affectation_id,
                'lot_num'          => $affectation->lot?->numero,
                'bloc'             => $affectation->bloc?->code,
                'bloc_id'          => $affectation->bloc_id,
                'grand_site'       => $affectation->grandSite?->nom,
                'date'             => $affectation->date_affectation?->format('Y-m-d'),
                'date_formatee'    => $affectation->date_affectation?->format('d/m/Y'),
                'notes'            => $affectation->notes,
                'beneficiaire_nom' => $affectation->beneficiaire?->nom,
            ];

            $changements = [];

            if ($lotChange) {
                $changements[] = "Lot : {$avant['lot_num']} → {$apres['lot_num']}";
            }

            if (($avant['date'] ?? null) !== ($apres['date'] ?? null)) {
                $d1 = $avant['date'] ? \Carbon\Carbon::parse($avant['date'])->format('d/m/Y') : '—';
                $d2 = $apres['date'] ? \Carbon\Carbon::parse($apres['date'])->format('d/m/Y') : '—';
                $changements[] = "Date : {$d1} → {$d2}";
            }

            if (($avant['notes'] ?? null) !== ($apres['notes'] ?? null)) {
                $changements[] = "Notes modifiées";
            }

            $resume = empty($changements) ? "Aucun changement détecté" : implode(' ; ', $changements);
            $hasChanges = $lotChange
                || (($avant['date'] ?? null) !== ($apres['date'] ?? null))
                || (($avant['notes'] ?? null) !== ($apres['notes'] ?? null));

            if ($dossier && $hasChanges) {
                $typeResume = $isEchange ? "🔄 Échange de lots" : "Modification";
                $nomComplet = $typeResume
                    . " du lot {$apres['lot_num']}"
                    . ($apres['bloc'] ? " (Bloc {$apres['bloc']})" : '')
                    . " — {$resume}"
                    . ($affectation->beneficiaire ? " — Bénéficiaire : « {$affectation->beneficiaire->nom} »" : '');

                HistoriqueService::log(
                    $dossier->id,
                    'modification_affectation',
                    $nomComplet,
                    'affectation',
                    $affectation->id,
                    $avant,
                    $apres,
                    $affectation->beneficiaire_id
                );
            }

            if ($affectation->beneficiaire_id) {
                $benef = Beneficiaire::find($affectation->beneficiaire_id);
                if ($benef) {
                    $nouvelleSuperficie = Affectation::where('beneficiaire_id', $benef->id)
                        ->where('statut', 'actif')
                        ->join('lots_affectation', 'affectations.lot_affectation_id', '=', 'lots_affectation.id')
                        ->sum('lots_affectation.superficie');

                    $nouveauxLotsTexte = Affectation::where('beneficiaire_id', $benef->id)
                        ->where('statut', 'actif')
                        ->with('lot')
                        ->get()
                        ->pluck('lot.numero')
                        ->filter()
                        ->unique()
                        ->implode(', ');

                    $benef->update([
                        'superficie_attribuee' => $nouvelleSuperficie,
                        'lots_texte'           => $nouveauxLotsTexte,
                    ]);
                }
            }

            return response()->json([
                'success'    => true,
                'message'    => $isEchange ? '✅ Échange de lots effectué.' : '✅ Affectation modifiée.',
                'avant'      => $avant,
                'apres'      => $apres,
                'resume'     => $resume,
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

                    $aff->lot?->update(['disponible' => true]);
                    $aff->delete();
                }
            }

            $lotsAjoutes = [];
            if (!empty($request->lots_a_ajouter)) {
                foreach ($request->lots_a_ajouter as $lotId) {
                    $lot = LotAffectation::with('bloc')->find($lotId);

                    if (!$lot || !$lot->disponible) continue;

                    $dejaActif = Affectation::where('lot_affectation_id', $lot->id)
                        ->where('statut', 'actif')
                        ->exists();

                    if ($dejaActif) continue;

                    Affectation::create([
                        'grand_site_id'       => $lot->grand_site_id,
                        'site_id'             => $lot->site_id,
                        'tf_id'               => $lot->tf_id,
                        'bloc_id'             => $lot->bloc_id,
                        'lot_affectation_id'  => $lot->id,
                        'client_id'           => $dossier->client_id,
                        'dossier_client_id'   => $dossier->id,
                        'beneficiaire_id'     => $beneficiaire->id,
                        'date_affectation'    => $date,
                        'statut'              => 'actif',
                        'etape_programmation' => 'nouvelle',
                        'notes'               => $notes,
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
                'point_depart'     => $pointDepart,
                'point_arrivee'    => $pointArrivee,
            ];

            $nouvelleSuperficie = Affectation::where('beneficiaire_id', $beneficiaire->id)
                ->where('statut', 'actif')
                ->join('lots_affectation', 'affectations.lot_affectation_id', '=', 'lots_affectation.id')
                ->sum('lots_affectation.superficie');

            $nouveauxLotsTexte = Affectation::where('beneficiaire_id', $beneficiaire->id)
                ->where('statut', 'actif')
                ->with('lot')
                ->get()
                ->pluck('lot.numero')
                ->filter()
                ->unique()
                ->implode(', ');

            $beneficiaire->update([
                'superficie_attribuee' => $nouvelleSuperficie,
                'lots_texte'           => $nouveauxLotsTexte,
            ]);

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

            $resume = empty($messages) ? "Aucun changement de lots" : implode(' | ', $messages);

            $nomComplet = "Modification des lots de « {$beneficiaire->nom} »"
                . " — {$resume}"
                . " (Avant : " . count($avantLotsList) . " lot(s)"
                . " → Après : " . count($apresLotsList) . " lot(s))";

            if (!empty($lotsRetires) || !empty($lotsAjoutes)) {
                HistoriqueService::log(
                    $dossier->id,
                    'modification_affectation',
                    $nomComplet,
                    'beneficiaire',
                    $beneficiaire->id,
                    $avant,
                    $apres,
                    $beneficiaire->id
                );
            }

            $total = count($lotsRetires) + count($lotsAjoutes);

            return response()->json([
                'success' => true,
                'message' => "✅ {$total} modification(s) appliquée(s).",
                'retires' => $lotsRetires,
                'ajoutes' => $lotsAjoutes,
                'avant'   => $avant,
                'apres'   => $apres,
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

    // ============================================================
    // ✅ AFFECTATION RAPIDE — Vue principale
    // ============================================================
    public function affectationRapide()
    {
        $grandSites = GrandSite::orderBy('nom')->get();

        $stats = [
            'lots_disponibles'     => LotAffectation::where('disponible', true)->where('actif', true)->count(),
            'grand_sites'          => GrandSite::count(),
            'affectations_du_jour' => Affectation::whereDate('created_at', today())->count(),
            'beneficiaires'        => Beneficiaire::count(),
        ];

        return view('admin.affectations.rapide', compact('grandSites', 'stats'));
    }

    // ============================================================
    // ✅ AFFECTATION RAPIDE — Recherche unifiée
    // ============================================================
    public function rechercherPersonne(Request $request)
    {
        $q = trim($request->input('q', ''));

        if (strlen($q) < 2) {
            return response()->json(['success' => true, 'resultats' => []]);
        }

        $resultats = [];

        $clientsQuery = Client::where(function ($query) use ($q) {
                $query->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('phone', 'LIKE', "%{$q}%");
            })
            ->with(['dossiers.grandSite'])
            ->limit(10)
            ->get();

        foreach ($clientsQuery as $client) {
            $dejaBenef = Beneficiaire::where('client_id', $client->id)->exists();

            $resultats[] = [
                'type'        => 'client',
                'id'          => $client->id,
                'nom'         => $client->name,
                'telephone'   => $client->phone,
                'is_benef'    => $dejaBenef,
                'nb_dossiers' => $client->dossiers->count(),
                'dossiers'    => $client->dossiers->map(fn ($d) => [
                    'id'         => $d->id,
                    'nom'        => $d->nom_dossier,
                    'grand_site' => $d->grandSite?->nom,
                ])->values(),
            ];
        }

        $benefQuery = Beneficiaire::where(function ($query) use ($q) {
                $query->where('nom', 'LIKE', "%{$q}%")
                      ->orWhere('telephone', 'LIKE', "%{$q}%");
            })
            ->with(['dossier.grandSite'])
            ->limit(10)
            ->get();

        foreach ($benefQuery as $benef) {
            if (collect($resultats)->where('type', 'client')->where('id', $benef->client_id)->isNotEmpty()) {
                continue;
            }

            $resultats[] = [
                'type'        => 'beneficiaire',
                'id'          => $benef->id,
                'nom'         => $benef->nom,
                'telephone'   => $benef->telephone,
                'is_benef'    => true,
                'nb_dossiers' => 1,
                'dossiers'    => [[
                    'id'         => $benef->dossier?->id,
                    'nom'        => $benef->dossier?->nom_dossier,
                    'grand_site' => $benef->dossier?->grandSite?->nom,
                ]],
            ];
        }

        return response()->json(['success' => true, 'resultats' => $resultats]);
    }

    // ============================================================
    // ✅ AFFECTATION RAPIDE — Affecter
    // ============================================================
    public function affecterRapide(Request $request)
    {
        $request->validate([
            'type_personne'    => 'required|in:client,beneficiaire',
            'personne_id'      => 'required|integer',
            'dossier_id'       => 'required|integer|exists:dossiers_clients,id',
            'lot_ids'          => 'required|array|min:1',
            'lot_ids.*'        => 'required|integer|exists:lots_affectation,id',
            'date_affectation' => 'nullable|date',
            'notes'            => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $dossier         = DossierClient::findOrFail($request->dossier_id);
            $dateAffectation = $request->date_affectation ?? now()->format('Y-m-d');

            $beneficiaire        = null;
            $conversionEffectuee = false;

            if ($request->type_personne === 'client') {
                $client = Client::findOrFail($request->personne_id);

                if ($client->dossiers()->where('id', $dossier->id)->doesntExist()) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => '❌ Ce client n\'est pas rattaché au dossier sélectionné.',
                    ], 422);
                }

                $beneficiaire = Beneficiaire::where('dossier_client_id', $dossier->id)
                    ->where('client_id', $client->id)
                    ->first();

                if (!$beneficiaire) {
                    $cniPath = null;
                    if (!empty($dossier->cni_images) && is_array($dossier->cni_images)) {
                        $cniPath = $dossier->cni_images[0] ?? null;
                    }

                    $beneficiaire = Beneficiaire::create([
                        'dossier_client_id'    => $dossier->id,
                        'client_id'            => $client->id,
                        'nom'                  => $client->name,
                        'telephone'            => $client->phone,
                        'cni_path'             => $cniPath,
                        'superficie_attribuee' => 0,
                        'notes'                => "Créé automatiquement via affectation rapide",
                    ]);

                    $conversionEffectuee = true;

                    HistoriqueService::log(
                        $dossier->id,
                        'ajout_beneficiaire',
                        "🔄 Client « {$client->name} » transformé automatiquement en bénéficiaire (affectation rapide)",
                        'beneficiaire',
                        $beneficiaire->id,
                        null,
                        ['client_id' => $client->id, 'auto_conversion' => true],
                        $beneficiaire->id
                    );
                }
            } else {
                $beneficiaire = Beneficiaire::findOrFail($request->personne_id);

                if ($beneficiaire->dossier_client_id != $dossier->id) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => '❌ Ce bénéficiaire n\'appartient pas à ce dossier.',
                    ], 422);
                }
            }

            $lots = LotAffectation::with('bloc')
                ->whereIn('id', $request->lot_ids)
                ->get();

            if ($lots->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => '❌ Aucun lot valide sélectionné.',
                ], 422);
            }

            $lotsIndispo = $lots->where('disponible', false);
            if ($lotsIndispo->isNotEmpty()) {
                DB::rollBack();
                $noms = $lotsIndispo->pluck('numero')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => "❌ Lots indisponibles : {$noms}",
                ], 422);
            }

            foreach ($lots as $lot) {
                $dejaActif = Affectation::where('lot_affectation_id', $lot->id)
                    ->where('statut', 'actif')
                    ->exists();

                if ($dejaActif) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "❌ Le lot {$lot->numero} a déjà une affectation active.",
                    ], 422);
                }
            }

            $superficieTotale = $lots->sum('superficie');
            $lotsAffectes = [];

            foreach ($lots as $lot) {
                Affectation::create([
                    'grand_site_id'       => $lot->grand_site_id,
                    'site_id'             => $lot->site_id,
                    'tf_id'               => $lot->tf_id,
                    'bloc_id'             => $lot->bloc_id,
                    'lot_affectation_id'  => $lot->id,
                    'client_id'           => $dossier->client_id,
                    'dossier_client_id'   => $dossier->id,
                    'beneficiaire_id'     => $beneficiaire->id,
                    'date_affectation'    => $dateAffectation,
                    'statut'              => 'actif',
                    'etape_programmation' => 'nouvelle',
                    'notes'               => $request->notes,
                ]);

                $lot->update(['disponible' => false]);

                $lotsAffectes[] = [
                    'lot_id'     => $lot->id,
                    'numero'     => $lot->numero,
                    'bloc'       => $lot->bloc?->code,
                    'superficie' => $lot->superficie,
                ];
            }

            $nouvelleSuperficie = Affectation::where('beneficiaire_id', $beneficiaire->id)
                ->where('statut', 'actif')
                ->join('lots_affectation', 'affectations.lot_affectation_id', '=', 'lots_affectation.id')
                ->sum('lots_affectation.superficie');

            $beneficiaire->update([
                'superficie_attribuee' => $nouvelleSuperficie,
                'lots_texte'           => Affectation::where('beneficiaire_id', $beneficiaire->id)
                    ->where('statut', 'actif')
                    ->with('lot')
                    ->get()
                    ->pluck('lot.numero')
                    ->filter()
                    ->unique()
                    ->implode(', '),
            ]);

            $resumeLots = collect($lotsAffectes)
                ->map(fn ($l) => "Lot {$l['numero']}" . ($l['bloc'] ? " (Bloc {$l['bloc']})" : ''))
                ->implode(', ');

            HistoriqueService::log(
                $dossier->id,
                'affectation_lot',
                "📦 " . count($lotsAffectes) . " lot(s) affecté(s) à « {$beneficiaire->nom} » "
                    . "via affectation rapide — {$resumeLots} "
                    . "— " . number_format($superficieTotale, 0, ',', ' ') . " m²",
                'beneficiaire',
                $beneficiaire->id,
                null,
                [
                    'lots'             => $lotsAffectes,
                    'superficie'       => $superficieTotale,
                    'date_affectation' => $dateAffectation,
                    'conversion_auto'  => $conversionEffectuee,
                    'par_geometre'     => auth()->id(),
                ],
                $beneficiaire->id
            );

            DB::commit();

            return response()->json([
                'success'           => true,
                'message'           => $conversionEffectuee
                    ? "✅ Client converti en bénéficiaire + " . count($lotsAffectes) . " lot(s) affecté(s)."
                    : "✅ " . count($lotsAffectes) . " lot(s) affecté(s) avec succès.",
                'beneficiaire_id'   => $beneficiaire->id,
                'conversion'        => $conversionEffectuee,
                'superficie_totale' => $nouvelleSuperficie,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur affectation rapide : ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

  public function listeAffectations(Request $request)
{
    $query = Affectation::with([
            'beneficiaire',
            'client',
            'grandSite',
            'site',
            'tf',
            'bloc',
            'lot',
            'acceptePar',
            'refusePar',
            'geometre',
        ])
        ->where('statut', 'actif')
        ->whereIn('etape_programmation', ['nouvelle', 'date_attribuee', 'programmee', 'finalisee']);

    // ─── Recherche ───
    if ($request->filled('q')) {
        $q = $request->q;
        $query->where(function ($sub) use ($q) {
            $sub->whereHas('beneficiaire', fn ($b) => $b->where('nom', 'LIKE', "%{$q}%"))
                ->orWhereHas('client', fn ($c) => $c->where('name', 'LIKE', "%{$q}%"))
                ->orWhereHas('lot', fn ($l) => $l->where('numero', 'LIKE', "%{$q}%"));
        });
    }

    if ($request->filled('grand_site_id')) {
        $query->where('grand_site_id', $request->grand_site_id);
    }

    if ($request->filled('bloc_id')) {
        $query->where('bloc_id', $request->bloc_id);
    }

    // ─── 📄 Filtre ATTRIBUTION (nouveau) ───
    // Filtre sur la date d'affectation
    if ($request->filled('jour_attribution')) {
        $query->whereDate('date_affectation', $request->jour_attribution);
    }

    // ─── 📅 Filtre PLANIFICATION (existant) ───
    // Filtre sur la date d'implantation
    if ($request->filled('jour')) {
        $query->whereDate('date_implantation', $request->jour);
    }

    // ─── Plage de dates ───
    if ($request->filled('du')) {
        $query->whereDate('date_affectation', '>=', $request->du);
    }
    if ($request->filled('au')) {
        $query->whereDate('date_affectation', '<=', $request->au);
    }

    $toutesAffectations = $query->orderByDesc('date_affectation')
        ->orderByDesc('id')
        ->get();

    // ─── Groupement (inchangé) ───
    $groupes = $toutesAffectations->groupBy(function ($aff) {
        return implode('-', [
            $aff->beneficiaire_id ?? 'c' . $aff->client_id,
            $aff->grand_site_id ?? 0,
            $aff->site_id       ?? 0,
            $aff->tf_id         ?? 0,
            $aff->bloc_id       ?? 0,
        ]);
    });

    $lignes = $groupes->map(function ($affs, $key) {
        $premier   = $affs->first();
        $lotsTries = $affs->sortBy(fn ($a) => $a->lot?->numero)->values();
        $superficieTotale = $affs->sum(fn ($a) => $a->lot?->superficie ?? 0);

        return [
            'cle'                 => $key,
            'affectations'        => $affs,
            'affectation_ids'     => $affs->pluck('id')->toArray(),
            'beneficiaire'        => $premier->beneficiaire?->nom
                                  ?? $premier->client?->name
                                  ?? '—',
            'telephone'           => $premier->beneficiaire?->telephone
                                  ?? $premier->client?->phone,
            'beneficiaire_id'     => $premier->beneficiaire_id,
            'client_id'           => $premier->client_id,
            'grand_site'          => $premier->grandSite?->nom,
            'grand_site_id'       => $premier->grand_site_id,
            'site'                => $premier->site?->name,
            'tf'                  => $premier->tf?->title,
            'bloc'                => $premier->bloc?->code,
            'bloc_id'             => $premier->bloc_id,
            'lots'                => $lotsTries->pluck('lot.numero')->filter()->implode(', '),
            'lots_count'          => $affs->count(),
            'superficie_totale'   => $superficieTotale,
            'date_affectation'    => $premier->date_affectation,
            'notes'               => $premier->notes,
            'statut_acceptation'  => $premier->statut_acceptation,
            'date_implantation'   => $premier->date_implantation,
            'geometre_id'         => $premier->geometre_id,
            'geometre_nom'        => $premier->geometre?->name,
        ];
    })->values();

    // ─── Pagination ───
    $perPage = 30;
    $page    = (int) $request->input('page', 1);
    $total   = $lignes->count();
    $items   = $lignes->slice(($page - 1) * $perPage, $perPage)->values();

    $affectations = new \Illuminate\Pagination\LengthAwarePaginator(
        $items, $total, $perPage, $page,
        ['path' => $request->url(), 'query' => $request->query()]
    );

    $stats = [
        'total_lignes'     => $total,
        'total_lots'       => $lignes->sum('lots_count'),
        'total_superficie' => $lignes->sum('superficie_totale'),
    ];

    $grandSites = GrandSite::orderBy('nom')->get();
    $blocs      = Bloc::with('tf')->orderBy('code')->get();

    return view('admin.affectations.liste', compact(
        'affectations', 'stats', 'grandSites', 'blocs'
    ));
}

    // ============================================================
    // ✅ DÉTAILS D'UN GROUPE
    // ============================================================
    public function detailsGroupe(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'IDs manquants'], 422);
        }

        $affs = Affectation::with(['beneficiaire', 'client', 'grandSite', 'site', 'tf', 'bloc', 'lot'])
            ->whereIn('id', $ids)
            ->where('statut', 'actif')
            ->get();

        if ($affs->isEmpty()) {
            return response()->json(['success' => false, 'message' => 'Aucune affectation trouvée'], 404);
        }

        $premier = $affs->first();
        $grandSites = GrandSite::orderBy('nom')->get(['id', 'nom']);

        return response()->json([
            'success' => true,
            'groupe' => [
                'affectation_ids'   => $affs->pluck('id')->toArray(),
                'beneficiaire_id'   => $premier->beneficiaire_id,
                'client_id'         => $premier->client_id,
                'beneficiaire'      => $premier->beneficiaire?->nom ?? $premier->client?->name,
                'telephone'         => $premier->beneficiaire?->telephone ?? $premier->client?->phone,
                'dossier_client_id' => $premier->dossier_client_id,
                'grand_site'        => $premier->grandSite?->nom,
                'site'              => $premier->site?->name,
                'tf'                => $premier->tf?->title,
                'bloc'              => $premier->bloc?->code,
                'bloc_id'           => $premier->bloc_id,
                'date_affectation'  => $premier->date_affectation?->format('Y-m-d'),
                'notes'             => $premier->notes,
                'lots'              => $affs->map(fn ($a) => [
                    'affectation_id' => $a->id,
                    'lot_id'         => $a->lot_affectation_id,
                    'numero'         => $a->lot?->numero,
                    'superficie'     => $a->lot?->superficie,
                ])->sortBy('numero')->values(),
                'superficie_totale' => $affs->sum(fn ($a) => $a->lot?->superficie ?? 0),
            ],
            'grandSites' => $grandSites,
        ]);
    }

    // ============================================================
    // ✅ AJOUTER DES LOTS À UN GROUPE
    // ============================================================
    public function ajouterLotsAuGroupe(Request $request)
    {
        $request->validate([
            'affectation_ids'   => 'required|array|min:1',
            'affectation_ids.*' => 'integer|exists:affectations,id',
            'lot_ids'           => 'required|array|min:1',
            'lot_ids.*'         => 'integer|exists:lots_affectation,id',
            'date_affectation'  => 'nullable|date',
            'notes'             => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $refAff = Affectation::findOrFail($request->affectation_ids[0]);
            $beneficiaireId = $refAff->beneficiaire_id;
            $dossierId      = $refAff->dossier_client_id;
            $clientId       = $refAff->client_id;

            $dateAffectation = $request->date_affectation ?? now()->format('Y-m-d');
            $lots = LotAffectation::with('bloc')
                ->whereIn('id', $request->lot_ids)
                ->get();

            if ($lots->isEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => '❌ Aucun lot valide sélectionné.',
                ], 422);
            }

            $indispo = $lots->where('disponible', false);
            if ($indispo->isNotEmpty()) {
                DB::rollBack();
                $noms = $indispo->pluck('numero')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => "❌ Lots indisponibles : {$noms}",
                ], 422);
            }

            foreach ($lots as $lot) {
                $dejaActif = Affectation::where('lot_affectation_id', $lot->id)
                    ->where('statut', 'actif')
                    ->exists();

                if ($dejaActif) {
                    DB::rollBack();
                    return response()->json([
                        'success' => false,
                        'message' => "❌ Le lot {$lot->numero} a déjà une affectation active.",
                    ], 422);
                }
            }

            $lotsAjoutes = [];

            foreach ($lots as $lot) {
                Affectation::create([
                    'grand_site_id'       => $lot->grand_site_id,
                    'site_id'             => $lot->site_id,
                    'tf_id'               => $lot->tf_id,
                    'bloc_id'             => $lot->bloc_id,
                    'lot_affectation_id'  => $lot->id,
                    'client_id'           => $clientId,
                    'dossier_client_id'   => $dossierId,
                    'beneficiaire_id'     => $beneficiaireId,
                    'date_affectation'    => $dateAffectation,
                    'statut'              => 'actif',
                    'etape_programmation' => 'nouvelle',
                    'notes'               => $request->notes,
                ]);

                $lot->update(['disponible' => false]);

                $lotsAjoutes[] = [
                    'lot_id'     => $lot->id,
                    'numero'     => $lot->numero,
                    'bloc'       => $lot->bloc?->code,
                    'superficie' => $lot->superficie,
                ];
            }

            if ($beneficiaireId) {
                $benef = Beneficiaire::find($beneficiaireId);
                if ($benef) {
                    $nouvelleSuperficie = Affectation::where('beneficiaire_id', $benef->id)
                        ->where('statut', 'actif')
                        ->join('lots_affectation', 'affectations.lot_affectation_id', '=', 'lots_affectation.id')
                        ->sum('lots_affectation.superficie');

                    $benef->update([
                        'superficie_attribuee' => $nouvelleSuperficie,
                        'lots_texte'           => Affectation::where('beneficiaire_id', $benef->id)
                            ->where('statut', 'actif')
                            ->with('lot')
                            ->get()
                            ->pluck('lot.numero')
                            ->filter()
                            ->unique()
                            ->implode(', '),
                    ]);
                }
            }

            $resumeLots = collect($lotsAjoutes)
                ->map(fn ($l) => "Lot {$l['numero']}" . ($l['bloc'] ? " (Bloc {$l['bloc']})" : ''))
                ->implode(', ');

            HistoriqueService::log(
                $dossierId,
                'affectation_lot',
                "📦 " . count($lotsAjoutes) . " lot(s) ajouté(s) au groupe — {$resumeLots}",
                'beneficiaire',
                $beneficiaireId,
                null,
                [
                    'lots'             => $lotsAjoutes,
                    'date_affectation' => $dateAffectation,
                    'par_geometre'     => auth()->id(),
                ],
                $beneficiaireId
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "✅ " . count($lotsAjoutes) . " lot(s) ajouté(s) au groupe.",
                'lots'    => $lotsAjoutes,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur ajouterLotsAuGroupe : ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ ACCEPTER UN GROUPE D'AFFECTATIONS
    // ============================================================
    public function accepterAffectation(Request $request)
    {
        $request->validate([
            'affectation_ids'   => 'required|array|min:1',
            'affectation_ids.*' => 'integer|exists:affectations,id',
            'date_acceptation'  => 'required|date',
        ], [
            'date_acceptation.required' => 'La date d\'acceptation est obligatoire.',
        ]);

        try {
            DB::beginTransaction();

            $ids  = $request->affectation_ids;
            $affs = Affectation::with(['beneficiaire', 'lot', 'bloc'])
                ->whereIn('id', $ids)
                ->where('statut_acceptation', 'en_attente')
                ->get();

            if ($affs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Aucune affectation en attente trouvée.',
                ], 422);
            }

            $lotsAcceptes = $affs->map(fn ($aff) => [
                'numero'     => $aff->lot?->numero,
                'bloc'       => $aff->bloc?->code,
                'superficie' => $aff->lot?->superficie,
            ])->values()->toArray();

            Affectation::whereIn('id', $ids)
                ->where('statut_acceptation', 'en_attente')
                ->update([
                    'statut_acceptation' => 'accepte',
                    'date_acceptation'   => $request->date_acceptation,
                    'accepte_le'         => now(),
                    'accepte_par'        => auth()->id(),
                    'motif_refus'        => null,
                    'date_refus'         => null,
                    'refuse_le'          => null,
                    'refuse_par'         => null,
                ]);

            $benefIds = $affs->pluck('beneficiaire_id')->filter()->unique()->toArray();

            foreach ($benefIds as $benefId) {
                $benef = Beneficiaire::find($benefId);
                if ($benef) {
                    $benef->update([
                        'deja_implante'  => $request->date_acceptation,
                        'etape_actuelle' => 'deja_implante',
                    ]);
                }
            }

            $premier = $affs->first();
            $nom     = $premier->beneficiaire?->nom ?? '—';
            $nbLots  = $affs->count();
            $dateFmt = \Carbon\Carbon::parse($request->date_acceptation)->format('d/m/Y');

            HistoriqueService::log(
                $premier->dossier_client_id,
                'affectation_lot',
                "✅ Affectation ACCEPTÉE le {$dateFmt} — « {$nom} » — {$nbLots} lot(s)",
                'beneficiaire',
                $premier->beneficiaire_id,
                [
                    'statut_acceptation' => 'en_attente',
                    'lots'               => $lotsAcceptes,
                ],
                [
                    'statut_acceptation' => 'accepte',
                    'date_acceptation'   => $request->date_acceptation,
                    'etape_benef'        => 'deja_implante',
                    'lots'               => $lotsAcceptes,
                ],
                $premier->beneficiaire_id
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "✅ {$nbLots} affectation(s) acceptée(s) le {$dateFmt}",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur accepterAffectation : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ REFUSER UN GROUPE D'AFFECTATIONS
    // ============================================================
    public function refuserAffectation(Request $request)
    {
        $request->validate([
            'affectation_ids'   => 'required|array|min:1',
            'affectation_ids.*' => 'integer|exists:affectations,id',
            'date_refus'        => 'required|date',
            'motif_refus'       => 'required|string|min:5|max:1000',
        ], [
            'date_refus.required'  => 'La date du refus est obligatoire.',
            'motif_refus.required' => 'Le motif du refus est obligatoire.',
            'motif_refus.min'      => 'Le motif doit contenir au moins 5 caractères.',
        ]);

        try {
            DB::beginTransaction();

            $ids  = $request->affectation_ids;
            $affs = Affectation::with(['beneficiaire', 'lot', 'bloc', 'dossier'])
                ->whereIn('id', $ids)
                ->where('statut_acceptation', 'en_attente')
                ->get();

            if ($affs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Aucune affectation en attente trouvée.',
                ], 422);
            }

            $lotsRefuses = $affs->map(fn ($aff) => [
                'numero'     => $aff->lot?->numero,
                'bloc'       => $aff->bloc?->code,
                'superficie' => $aff->lot?->superficie,
            ])->values()->toArray();

            foreach ($affs as $aff) {
                $aff->lot?->update(['disponible' => true]);
            }

            Affectation::whereIn('id', $ids)
    ->where('statut_acceptation', 'en_attente')
    ->update([
        'statut'             => 'annule',
                    'statut_acceptation' => 'refuse',
                    'date_refus'         => $request->date_refus,
                    'refuse_le'          => now(),
                    'motif_refus'        => $request->motif_refus,
                    'refuse_par'         => auth()->id(),
                    'date_acceptation'   => null,
                    'accepte_le'         => null,
                    'accepte_par'        => null,
                ]);

            $benefIds = $affs->pluck('beneficiaire_id')->filter()->unique()->toArray();

            foreach ($benefIds as $benefId) {
                $benef = Beneficiaire::find($benefId);
                if ($benef) {
                    $nouvelleSuperficie = Affectation::where('beneficiaire_id', $benef->id)
                        ->where('statut', 'actif')
                        ->where('statut_acceptation', '!=', 'refuse')
                        ->join('lots_affectation', 'affectations.lot_affectation_id', '=', 'lots_affectation.id')
                        ->sum('lots_affectation.superficie');

                    $nouveauxLotsTexte = Affectation::where('beneficiaire_id', $benef->id)
                        ->where('statut', 'actif')
                        ->where('statut_acceptation', '!=', 'refuse')
                        ->with('lot')
                        ->get()
                        ->pluck('lot.numero')
                        ->filter()
                        ->unique()
                        ->implode(', ');

                    $benef->update([
                        'superficie_attribuee' => $nouvelleSuperficie,
                        'lots_texte'           => $nouveauxLotsTexte,
                    ]);
                }
            }

            $premier = $affs->first();
            $nom     = $premier->beneficiaire?->nom ?? '—';
            $nbLots  = $affs->count();
            $dateFmt = \Carbon\Carbon::parse($request->date_refus)->format('d/m/Y');

            HistoriqueService::log(
                $premier->dossier_client_id,
                'annulation_affectation',
                "❌ Affectation REFUSÉE le {$dateFmt} — « {$nom} » — {$nbLots} lot(s)"
                    . " — Motif : {$request->motif_refus}",
                'beneficiaire',
                $premier->beneficiaire_id,
                [
                    'statut_acceptation' => 'en_attente',
                    'lots'               => $lotsRefuses,
                ],
                [
                    'statut'             => 'annule',
                    'statut_acceptation' => 'refuse',
                    'date_refus'         => $request->date_refus,
                    'motif'              => $request->motif_refus,
                    'lots'               => $lotsRefuses,
                ],
                $premier->beneficiaire_id
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "❌ {$nbLots} affectation(s) refusée(s) le {$dateFmt}",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur refuserAffectation : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ METTRE À JOUR L'IMPLANTATION D'UN GROUPE
    // ============================================================
    public function updateImplantationGroupe(Request $request)
    {
        $request->validate([
            'affectation_ids'   => 'required|array|min:1',
            'affectation_ids.*' => 'integer|exists:affectations,id',
            'date_implantation' => 'nullable|date',
            'geometre_id'       => 'nullable|exists:users,id',
        ]);

        try {
            DB::beginTransaction();

            $ids  = $request->affectation_ids;
            $affs = Affectation::with(['beneficiaire', 'geometre', 'lot', 'bloc'])
                ->whereIn('id', $ids)
                ->get();

            if ($affs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Aucune affectation trouvée.',
                ], 422);
            }

            $nonEnAttente = $affs->where('statut_acceptation', '!=', 'en_attente');

            if ($nonEnAttente->isNotEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => '❌ Impossible de modifier : affectation déjà acceptée ou refusée.',
                ], 422);
            }

            $premier = $affs->first();

            $lotsImplantes = $affs->map(fn ($aff) => [
                'numero'     => $aff->lot?->numero,
                'bloc'       => $aff->bloc?->code,
                'superficie' => $aff->lot?->superficie,
            ])->values()->toArray();

            $avant = [
                'date_implantation' => $premier->date_implantation?->format('Y-m-d H:i'),
                'geometre_id'       => $premier->geometre_id,
                'geometre_nom'      => $premier->geometre?->name,
                'lots'              => $lotsImplantes,
            ];

            Affectation::whereIn('id', $ids)->update([
                'date_implantation' => $request->date_implantation,
                'geometre_id'       => $request->geometre_id,
            ]);

            $apres = [
                'date_implantation' => $request->date_implantation
                    ? \Carbon\Carbon::parse($request->date_implantation)->format('Y-m-d H:i')
                    : null,
                'geometre_id'       => $request->geometre_id,
                'geometre_nom'      => $request->geometre_id
                    ? \App\Models\User::find($request->geometre_id)?->name
                    : null,
                'lots'              => $lotsImplantes,
            ];

            $hasChanges = ($avant['date_implantation'] !== $apres['date_implantation'])
                       || ($avant['geometre_id']       !== $apres['geometre_id']);

            if ($hasChanges && $premier->dossier_client_id) {
                $changements = [];

                if ($avant['date_implantation'] !== $apres['date_implantation']) {
                    $changements[] = "Implantation : "
                        . ($avant['date_implantation'] ?? '—')
                        . " → " . ($apres['date_implantation'] ?? '—');
                }
                if ($avant['geometre_id'] !== $apres['geometre_id']) {
                    $changements[] = "Géomètre : "
                        . ($avant['geometre_nom'] ?? '—')
                        . " → " . ($apres['geometre_nom'] ?? '—');
                }

                HistoriqueService::log(
                    $premier->dossier_client_id,
                    'modification_affectation',
                    "🔧 Implantation (" . count($ids) . " lot(s)) — " . implode(' ; ', $changements)
                        . ($premier->beneficiaire ? " — Bénéficiaire : « {$premier->beneficiaire->nom} »" : ''),
                    'affectation',
                    $premier->id,
                    $avant,
                    $apres,
                    $premier->beneficiaire_id
                );
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Implantation mise à jour sur ' . count($ids) . ' affectation(s).',
                'avant'   => $avant,
                'apres'   => $apres,
                'updated' => count($ids),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur updateImplantationGroupe : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ METTRE À JOUR LA PROGRAMMATION D'UN GROUPE
    // ============================================================
    public function updateProgrammation(Request $request)
    {
        $request->validate([
            'affectation_ids'    => 'required|array|min:1',
            'affectation_ids.*'  => 'integer|exists:affectations,id',
            'heure_implantation' => 'nullable|date_format:H:i',
            'geometre_id'        => 'nullable|exists:users,id',
            'statut_presence'    => 'nullable|in:en_attente,present,retard,absent',
        ]);

        try {
            DB::beginTransaction();

            $ids  = $request->affectation_ids;
            $affs = Affectation::whereIn('id', $ids)->get();

            if ($affs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Aucune affectation trouvée.',
                ], 422);
            }

            $nonAutorisees = $affs->whereNotIn('statut_acceptation', ['en_attente', 'accepte']);
            if ($nonAutorisees->isNotEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => '❌ Impossible de modifier : affectation déjà refusée.',
                ], 422);
            }

            $updateData = [];
            if ($request->has('heure_implantation'))  $updateData['heure_implantation']  = $request->heure_implantation;
            if ($request->has('geometre_id'))         $updateData['geometre_id']         = $request->geometre_id;
            if ($request->has('statut_presence'))     $updateData['statut_presence']     = $request->statut_presence;

            if (empty($updateData)) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ Aucune donnée à mettre à jour.',
                ], 422);
            }

            Affectation::whereIn('id', $ids)->update($updateData);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Programmation mise à jour sur ' . count($ids) . ' affectation(s).',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur updateProgrammation : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ TOGGLE FRAIS LOGISTIQUE
    // ============================================================
    public function toggleFraisLogistique(Request $request)
    {
        $request->validate([
            'affectation_ids'       => 'required|array|min:1',
            'affectation_ids.*'     => 'integer|exists:affectations,id',
            'frais_logistique_paye' => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();

            $ids  = $request->affectation_ids;
            $affs = Affectation::whereIn('id', $ids)->get();

            if ($affs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Aucune affectation trouvée.',
                ], 422);
            }

            $nonAutorisees = $affs->whereNotIn('statut_acceptation', ['en_attente', 'accepte']);
            if ($nonAutorisees->isNotEmpty()) {
                DB::rollBack();
                return response()->json([
                    'success' => false,
                    'message' => '❌ Impossible de modifier : affectation déjà refusée.',
                ], 422);
            }

            Affectation::whereIn('id', $ids)->update([
                'frais_logistique_paye' => $request->frais_logistique_paye,
            ]);

            DB::commit();

            $msg = $request->frais_logistique_paye ? '✅ Frais marqués comme PAYÉS' : '❌ Frais marqués comme NON PAYÉS';

            return response()->json([
                'success' => true,
                'message' => $msg . ' (' . count($ids) . ' affectation(s))',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur toggleFraisLogistique : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ CHECK SEMAINE DERNIÈRE (API)
    // ============================================================
    public function checkSemaineDerniere()
    {
        $blocage = $this->verifierBlocageCreation();

        return response()->json([
            'bloque'  => $blocage['bloque'],
            'count'   => $blocage['count'],
            'message' => $blocage['message'],
        ]);
    }

    // ============================================================
    // ✅ IMPLANTATION MULTIPLE
    // ============================================================
    public function updateImplantationMultiple(Request $request)
    {
        $request->validate([
            'lignes'            => 'required|array|min:1',
            'lignes.*.ids'      => 'required|array|min:1',
            'lignes.*.ids.*'    => 'integer|exists:affectations,id',
            'date_implantation' => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $dateImplantation = $request->date_implantation;
            $totalMisAJour    = 0;
            $totalBloques     = 0;

            foreach ($request->lignes as $ligne) {
                $ids  = $ligne['ids'];
                $affs = Affectation::with(['beneficiaire', 'lot', 'bloc'])
                    ->whereIn('id', $ids)
                    ->get();

                $enAttente = $affs->where('statut_acceptation', 'en_attente');

                if ($enAttente->isEmpty()) {
                    $totalBloques += $affs->count();
                    continue;
                }

                $idsModifiables = $enAttente->pluck('id')->toArray();

                $lotsImplantes = $enAttente->map(fn ($aff) => [
                    'numero'     => $aff->lot?->numero,
                    'bloc'       => $aff->bloc?->code,
                    'superficie' => $aff->lot?->superficie,
                ])->values()->toArray();

                $avant = [
                    'date_implantation' => $enAttente->first()?->date_implantation?->format('Y-m-d H:i'),
                    'lots'              => $lotsImplantes,
                ];

                Affectation::whereIn('id', $idsModifiables)->update([
                    'date_implantation' => $dateImplantation,
                ]);

                $apres = [
                    'date_implantation' => \Carbon\Carbon::parse($dateImplantation)->format('Y-m-d H:i'),
                    'lots'              => $lotsImplantes,
                ];

                $premier = $enAttente->first();
                if ($premier && $premier->dossier_client_id) {
                    HistoriqueService::log(
                        $premier->dossier_client_id,
                        'modification_affectation',
                        "🔧 Implantation multiple (" . count($idsModifiables) . " lot(s)) — Date : "
                            . ($avant['date_implantation'] ?? '—')
                            . " → " . $apres['date_implantation']
                            . ($premier->beneficiaire ? " — Bénéficiaire : « {$premier->beneficiaire->nom} »" : ''),
                        'affectation',
                        $premier->id,
                        $avant,
                        $apres,
                        $premier->beneficiaire_id
                    );
                }

                $totalMisAJour += count($idsModifiables);
                $totalBloques  += ($affs->count() - count($idsModifiables));
            }

            DB::commit();

            $msg = "✅ Implantation mise à jour sur {$totalMisAJour} affectation(s).";
            if ($totalBloques > 0) {
                $msg .= " — {$totalBloques} ignorée(s) (déjà acceptée/refusée).";
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'updated' => $totalMisAJour,
                'ignored' => $totalBloques,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur updateImplantationMultiple : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ PROGRAMMATION MULTIPLE
    // ============================================================
    public function updateProgrammationMultiple(Request $request)
    {
        $request->validate([
            'lignes'             => 'required|array|min:1',
            'lignes.*.ids'       => 'required|array|min:1',
            'lignes.*.ids.*'     => 'integer|exists:affectations,id',
            'date_implantation'  => 'nullable|date',
            'heure_implantation' => 'nullable|date_format:H:i',
            'geometre_id'        => 'nullable|exists:users,id',
            'statut_presence'    => 'nullable|in:en_attente,present,retard,absent',
        ]);

        try {
            DB::beginTransaction();

            $updateData = [];
            if ($request->filled('date_implantation'))  $updateData['date_implantation']  = $request->date_implantation;
            if ($request->filled('heure_implantation')) $updateData['heure_implantation'] = $request->heure_implantation;
            if ($request->filled('geometre_id'))        $updateData['geometre_id']        = $request->geometre_id;
            if ($request->filled('statut_presence'))    $updateData['statut_presence']    = $request->statut_presence;

            if (empty($updateData)) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ Aucune donnée à mettre à jour.',
                ], 422);
            }

            $totalMisAJour = 0;
            $totalBloques  = 0;

            foreach ($request->lignes as $ligne) {
                $affs = Affectation::whereIn('id', $ligne['ids'])->get();
                $enAttente = $affs->where('statut_acceptation', 'en_attente');

                if ($enAttente->isEmpty()) {
                    $totalBloques += $affs->count();
                    continue;
                }

                $idsModifiables = $enAttente->pluck('id')->toArray();
                Affectation::whereIn('id', $idsModifiables)->update($updateData);

                $totalMisAJour += count($idsModifiables);
                $totalBloques  += ($affs->count() - count($idsModifiables));
            }

            DB::commit();

            $msg = "✅ Programmation mise à jour sur {$totalMisAJour} affectation(s).";
            if ($totalBloques > 0) {
                $msg .= " — {$totalBloques} ignorée(s).";
            }

            return response()->json([
                'success' => true,
                'message' => $msg,
                'updated' => $totalMisAJour,
                'ignored' => $totalBloques,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur updateProgrammationMultiple : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ ACCEPTER PROGRAMMATION MULTIPLE
    // ============================================================
    public function accepterProgrammationMultiple(Request $request)
    {
        $request->validate([
            'affectation_ids'   => 'required|array|min:1',
            'affectation_ids.*' => 'integer|exists:affectations,id',
            'date_acceptation'  => 'required|date',
        ]);

        try {
            DB::beginTransaction();

            $ids  = $request->affectation_ids;
            $affs = Affectation::with(['beneficiaire', 'lot', 'bloc'])
                ->whereIn('id', $ids)
                ->where('statut_acceptation', 'en_attente')
                ->get();

            if ($affs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Aucune affectation en attente trouvée.',
                ], 422);
            }

            $lotsAcceptes = $affs->map(fn ($aff) => [
                'numero'     => $aff->lot?->numero,
                'bloc'       => $aff->bloc?->code,
                'superficie' => $aff->lot?->superficie,
            ])->values()->toArray();

            Affectation::whereIn('id', $affs->pluck('id')->toArray())
                ->where('statut_acceptation', 'en_attente')
                ->update([
                    'statut_acceptation' => 'accepte',
                    'date_acceptation'   => $request->date_acceptation,
                    'accepte_le'         => now(),
                    'accepte_par'        => auth()->id(),
                    'motif_refus'        => null,
                    'date_refus'         => null,
                    'refuse_le'          => null,
                    'refuse_par'         => null,
                ]);

            $benefIds = $affs->pluck('beneficiaire_id')->filter()->unique()->toArray();

            foreach ($benefIds as $benefId) {
                $benef = Beneficiaire::find($benefId);
                if ($benef) {
                    $benef->update([
                        'deja_implante'  => $request->date_acceptation,
                        'etape_actuelle' => 'deja_implante',
                    ]);
                }
            }

            $premier = $affs->first();
            $dateFmt = \Carbon\Carbon::parse($request->date_acceptation)->format('d/m/Y');

            HistoriqueService::log(
                $premier->dossier_client_id,
                'affectation_lot',
                "✅ Programmation ACCEPTÉE le {$dateFmt} — « "
                    . ($premier->beneficiaire?->nom ?? '—')
                    . " » — {$affs->count()} lot(s)",
                'beneficiaire',
                $premier->beneficiaire_id,
                [
                    'statut_acceptation' => 'en_attente',
                    'lots'               => $lotsAcceptes,
                ],
                [
                    'statut_acceptation' => 'accepte',
                    'date_acceptation'   => $request->date_acceptation,
                    'etape_benef'        => 'deja_implante',
                    'lots'               => $lotsAcceptes,
                ],
                $premier->beneficiaire_id
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "✅ {$affs->count()} affectation(s) acceptée(s) le {$dateFmt}",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur accepterProgrammationMultiple : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ REFUSER PROGRAMMATION MULTIPLE
    // ============================================================
    public function refuserProgrammationMultiple(Request $request)
    {
        $request->validate([
            'affectation_ids'   => 'required|array|min:1',
            'affectation_ids.*' => 'integer|exists:affectations,id',
            'date_refus'        => 'required|date',
            'motif_refus'       => 'required|string|min:5|max:1000',
        ]);

        try {
            DB::beginTransaction();

            $ids  = $request->affectation_ids;
            $affs = Affectation::with(['beneficiaire', 'lot', 'bloc'])
                ->whereIn('id', $ids)
                ->where('statut_acceptation', 'en_attente')
                ->get();

            if ($affs->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Aucune affectation en attente trouvée.',
                ], 422);
            }

            $lotsRefuses = $affs->map(fn ($aff) => [
                'numero'     => $aff->lot?->numero,
                'bloc'       => $aff->bloc?->code,
                'superficie' => $aff->lot?->superficie,
            ])->values()->toArray();

            foreach ($affs as $aff) {
                $aff->lot?->update(['disponible' => true]);
            }

           Affectation::whereIn('id', $ids)
    ->where('statut_acceptation', 'en_attente')
    ->update([
        'statut'             => 'annule',
                    'statut_acceptation' => 'refuse',
                    'date_refus'         => $request->date_refus,
                    'refuse_le'          => now(),
                    'motif_refus'        => $request->motif_refus,
                    'refuse_par'         => auth()->id(),
                    'date_acceptation'   => null,
                    'accepte_le'         => null,
                    'accepte_par'        => null,
                ]);

            $benefIds = $affs->pluck('beneficiaire_id')->filter()->unique()->toArray();

            foreach ($benefIds as $benefId) {
                $benef = Beneficiaire::find($benefId);
                if ($benef) {
                    $nouvelleSuperficie = Affectation::where('beneficiaire_id', $benef->id)
                        ->where('statut', 'actif')
                        ->where('statut_acceptation', '!=', 'refuse')
                        ->join('lots_affectation', 'affectations.lot_affectation_id', '=', 'lots_affectation.id')
                        ->sum('lots_affectation.superficie');

                    $nouveauxLotsTexte = Affectation::where('beneficiaire_id', $benef->id)
                        ->where('statut', 'actif')
                        ->where('statut_acceptation', '!=', 'refuse')
                        ->with('lot')
                        ->get()
                        ->pluck('lot.numero')
                        ->filter()
                        ->unique()
                        ->implode(', ');

                    $benef->update([
                        'superficie_attribuee' => $nouvelleSuperficie,
                        'lots_texte'           => $nouveauxLotsTexte,
                    ]);
                }
            }

            $premier = $affs->first();
            $dateFmt = \Carbon\Carbon::parse($request->date_refus)->format('d/m/Y');

            HistoriqueService::log(
                $premier->dossier_client_id,
                'annulation_affectation',
                "❌ Programmation REFUSÉE le {$dateFmt} — « "
                    . ($premier->beneficiaire?->nom ?? '—')
                    . " » — {$affs->count()} lot(s) — Motif : {$request->motif_refus}",
                'beneficiaire',
                $premier->beneficiaire_id,
                [
                    'statut_acceptation' => 'en_attente',
                    'lots'               => $lotsRefuses,
                ],
                [
                    'statut'             => 'annule',
                    'statut_acceptation' => 'refuse',
                    'date_refus'         => $request->date_refus,
                    'motif'              => $request->motif_refus,
                    'lots'               => $lotsRefuses,
                ],
                $premier->beneficiaire_id
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "❌ {$affs->count()} affectation(s) refusée(s) le {$dateFmt}",
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur refuserProgrammationMultiple : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ HISTORIQUE DES AFFECTATIONS
    // ============================================================
    public function historiqueAffectations(Request $request)
    {
        $query = HistoriqueAffectation::with(['user:id,name', 'dossier.client', 'beneficiaire'])
            ->whereIn('type_action', [
                'affectation_lot',
                'modification_affectation',
                'annulation_affectation',
            ]);

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->where('resume', 'LIKE', "%{$q}%")
                    ->orWhereHas('beneficiaire', fn ($b) => $b->where('nom', 'LIKE', "%{$q}%"))
                    ->orWhereHas('dossier.client', fn ($c) => $c->where('name', 'LIKE', "%{$q}%"));
            });
        }

        if ($request->filled('grand_site_id')) {
            $query->whereHas('dossier', function ($d) use ($request) {
                $d->where('grand_site_id', $request->grand_site_id);
            });
        }

        if ($request->filled('du')) {
            $query->whereDate('created_at', '>=', $request->du);
        }
        if ($request->filled('au')) {
            $query->whereDate('created_at', '<=', $request->au);
        }

        $historiques = $query->orderByDesc('created_at')->paginate(50);

        $stats = [
            'total_entrees'            => $historiques->total(),
            'affectation_lot'          => HistoriqueAffectation::where('type_action', 'affectation_lot')->count(),
            'modification_affectation' => HistoriqueAffectation::where('type_action', 'modification_affectation')->count(),
            'annulation_affectation'   => HistoriqueAffectation::where('type_action', 'annulation_affectation')->count(),
        ];

        $grandSites = GrandSite::orderBy('nom')->get();
        $geometres  = \App\Models\User::whereIn('role', ['geometre', 'admin'])
            ->where('actif', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.affectations.historique', compact(
            'historiques', 'stats', 'grandSites', 'geometres'
        ));
    }

    // ============================================================
    // ✅ HELPER — Récupère la date active depuis la session
    // ============================================================
    private function getDateActiveSession(): ?\Carbon\Carbon
    {
        $dateSession = session('date_active');

        if ($dateSession) {
            try {
                return \Carbon\Carbon::parse($dateSession);
            } catch (\Exception $e) {
                return null;
            }
        }

        return null;
    }

   public function choixDimanche(Request $request)
{
    $moisParam = $request->input('mois', now()->format('Y-m'));
    try {
        $moisCourant = \Carbon\Carbon::createFromFormat('Y-m', $moisParam)->startOfMonth();
    } catch (\Exception $e) {
        $moisCourant = now()->startOfMonth();
    }

    $debutMois   = $moisCourant->copy()->startOfMonth();
    $finMois     = $moisCourant->copy()->endOfMonth();
    $moisLabel   = ucfirst($moisCourant->translatedFormat('F Y'));
    $moisPrecedent = $moisCourant->copy()->subMonth()->format('Y-m');
    $moisSuivant   = $moisCourant->copy()->addMonth()->format('Y-m');

    $dateSelectionnee = session('date_active');
    if ($dateSelectionnee) {
        try {
            $dateSelectionnee = \Carbon\Carbon::parse($dateSelectionnee)->format('Y-m-d');
        } catch (\Exception $e) {
            $dateSelectionnee = null;
        }
    }

    // ═══════════════════════════════════════════════════════════
    // 🔒 DATE LIMITE : Dernière date non-terminée
    //    Toute date > cette limite est VERROUILLÉE
    // ═══════════════════════════════════════════════════════════

    // Cherche la PLUS ANCIENNE affectation non-terminée
    // (statut_acceptation = 'en_attente' sur une date passée)
    $dateLimite = Affectation::where('statut', 'actif')
        ->where('statut_acceptation', 'en_attente')
        ->whereNotNull('date_implantation')
        ->whereDate('date_implantation', '<', now()->format('Y-m-d'))
        ->min('date_implantation');

    $dateVerrouillage = $dateLimite
        ? \Carbon\Carbon::parse($dateLimite)->format('Y-m-d')
        : null;

    // Infos sur les affectations en attente pour le message
    $nbEnAttente = 0;
    $datesEnAttente = [];
    if ($dateVerrouillage) {
        $queryAttente = Affectation::where('statut', 'actif')
            ->where('statut_acceptation', 'en_attente')
            ->whereNotNull('date_implantation')
            ->whereDate('date_implantation', '<=', $dateVerrouillage);

        $nbEnAttente = $queryAttente->count();
        $datesEnAttente = (clone $queryAttente)
            ->select('date_implantation')
            ->distinct()
            ->pluck('date_implantation')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m/Y'))
            ->toArray();
    }

    // ═══════════════════════════════════════════════════════════
    // Statistiques par date pour le mois affiché
    // ═══════════════════════════════════════════════════════════
    $affectationsParDate = Affectation::where('statut', 'actif')
        ->whereBetween('date_implantation', [
            $debutMois->copy()->startOfDay(),
            $finMois->copy()->endOfDay(),
        ])
        ->whereNotNull('date_implantation')
        ->get()
        ->groupBy(fn ($aff) => \Carbon\Carbon::parse($aff->date_implantation)->format('Y-m-d'))
        ->map(function ($affs) {
            return [
                'total'      => $affs->count(),
                'en_attente' => $affs->where('statut_acceptation', 'en_attente')->count(),
                'acceptees'  => $affs->where('statut_acceptation', 'accepte')->count(),
                'refusees'   => $affs->where('statut_acceptation', 'refuse')->count(),
            ];
        });

    $debutGrille = $debutMois->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
    $finGrille   = $finMois->copy()->endOfWeek(\Carbon\Carbon::SUNDAY);

    $calendrier = [];
    $jour = $debutGrille->copy();
    $semaine = [];

    while ($jour <= $finGrille) {
        $dateStr    = $jour->format('Y-m-d');
        $dansLeMois = $jour->month === $moisCourant->month;

        if ($dansLeMois) {
            $stats = $affectationsParDate->get($dateStr, [
                'total'      => 0,
                'en_attente' => 0,
                'acceptees'  => 0,
                'refusees'   => 0,
            ]);

            // ✅ Détermine si cette date est verrouillée
            $verrouillee = false;
            if ($dateVerrouillage) {
                // Une date est verrouillée si elle est APRÈS la date limite
                // ET que ce n'est pas la date limite elle-même
                $verrouillee = \Carbon\Carbon::parse($dateStr)->gt(\Carbon\Carbon::parse($dateVerrouillage));
            }

            $semaine[] = [
                'jour'        => $jour->day,
                'date_str'    => $dateStr,
                'is_today'    => $jour->isToday(),
                'is_selected' => $dateStr === $dateSelectionnee,
                'is_weekend'  => $jour->isWeekend(),
                'is_past'     => $jour->lt(now()->startOfDay()),
                'total'       => $stats['total'],
                'en_attente'  => $stats['en_attente'],
                'acceptees'   => $stats['acceptees'],
                'refusees'    => $stats['refusees'],
                'verrouillee' => $verrouillee,
            ];
        } else {
            $semaine[] = null;
        }

        if ($jour->dayOfWeek === \Carbon\Carbon::SUNDAY) {
            $calendrier[] = $semaine;
            $semaine = [];
        }

        $jour->addDay();
    }

    return view('admin.affectations.choix-dimanche', compact(
        'calendrier',
        'moisLabel',
        'moisPrecedent',
        'moisSuivant',
        'dateSelectionnee',
        'dateVerrouillage',   // ✅ Nouveau
        'nbEnAttente',        // ✅ Nouveau
        'datesEnAttente'      // ✅ Nouveau
    ));
}

    // ============================================================
    // ✅ ACTION — Définir la DATE active (POST)
    // ============================================================
    public function setDimanche(Request $request)
    {
        $request->validate([
            'dimanche' => 'required|date',
        ]);

        $date = \Carbon\Carbon::parse($request->dimanche)->format('Y-m-d');

        session(['date_active' => $date]);

        return redirect()
            ->route('affectations.programmation')   // ✅ Redirige vers Étape 1
            ->with('success', '✅ Date du ' . \Carbon\Carbon::parse($date)->format('d/m/Y') . ' sélectionnée.');
    }

   public function programmationInitiale(Request $request)
{
    $dateActive = $this->getDateActiveSession();

    $query = Affectation::with([
        'beneficiaire', 'client', 'grandSite', 'site', 'tf', 'bloc', 'lot',
        'geometre', 'dossier.facilitateur',
    ])
    ->where('statut', 'actif')
    // ✅ Afficher UNIQUEMENT les clôturées (envoyées depuis la liste)
    ->where('etape_programmation', 'cloturee')
    ->where('statut_acceptation', 'en_attente');

    if ($dateActive) {
        $query->where(function($q) use ($dateActive) {
            $q->whereDate('date_implantation', $dateActive->format('Y-m-d'))
              ->orWhereNull('date_implantation');
        });
    }

    $affectations = $query->orderBy('heure_implantation')
        ->orderBy('grand_site_id')
        ->orderBy('bloc_id')
        ->orderBy('lot_affectation_id')
        ->get();

    $lignes = $affectations->groupBy(function ($aff) {
        return implode('-', [
            $aff->beneficiaire_id ?? 'c' . $aff->client_id,
            $aff->bloc_id ?? 0,
        ]);
    })->map(function ($affs, $key) {
        $premier   = $affs->first();
        $lotsTries = $affs->sortBy(fn ($a) => $a->lot?->numero)->values();

        $etape = $premier->etape_programmation;
        $validee = in_array($etape, ['programmee', 'finalisee']);

        return [
            'affectation_ids'    => $affs->pluck('id')->toArray(),
            'beneficiaire'       => $premier->beneficiaire?->nom ?? $premier->client?->name ?? '—',
            'telephone'          => $premier->beneficiaire?->telephone ?? '—',
            'titre_foncier'      => $premier->dossier?->titre_foncier ?? $premier->tf?->title ?? '—',
            'grand_site'         => $premier->grandSite?->nom,
            'site'               => $premier->site?->name,
            'bloc'               => $premier->bloc?->code,
            'lots'               => $lotsTries->pluck('lot.numero')->filter()->implode(', '),
            'superficie'         => $affs->sum(fn ($a) => $a->lot?->superficie ?? 0),
            'facilitateur'       => $premier->dossier?->facilitateur?->nom ?? '—',
            'heure_implantation' => $premier->heure_implantation,
            'date_implantation'  => $premier->date_implantation,
            'geometre_id'        => $premier->geometre_id,
            'geometre_nom'       => $premier->geometre?->name,
            'frais_paye'         => $premier->frais_logistique_paye,
            'statut_presence'    => $premier->statut_presence ?? 'en_attente',
            'etape_programmation'=> $etape,
            'validee'            => $validee,
            'statut_acceptation' => $premier->statut_acceptation,
        ];
    })->values();

    $stats = [
        'total'      => $lignes->count(),
        'sans_geo'   => $lignes->where('geometre_id', null)->where('validee', false)->count(),
        'sans_heure' => $lignes->where('heure_implantation', null)->where('validee', false)->count(),
        'validees'   => $lignes->where('validee', true)->count(),
        'superficie' => $lignes->sum('superficie'),
    ];

    $geometres = \App\Models\User::whereIn('role', ['geometre', 'admin'])
        ->where('actif', true)
        ->orderBy('name')
        ->get(['id', 'name']);

    return view('admin.affectations.programmation-initiale', [
        'lignes'         => $lignes,
        'stats'          => $stats,
        'geometres'      => $geometres,
        'dateActive'     => $dateActive,
    ]);
}

    // ============================================================
    // ✅ PAGE 2 — Programmation active (Étape 2 — Appréciations)
    // ============================================================
   public function programmationActive(Request $request)
{
    $dateActive = $this->getDateActiveSession();

    $query = Affectation::with([
        'beneficiaire', 'client', 'grandSite', 'site', 'tf', 'bloc', 'lot',
        'geometre', 'dossier.facilitateur',
    ])
    ->where('statut', 'actif')
    // ✅ ÉTAPE 2 : uniquement les affectations déjà programmées
    ->whereIn('etape_programmation', ['programmee', 'finalisee'])
    ->whereIn('statut_acceptation', ['en_attente', 'accepte']);

    // ✅ Filtre sur la date
    if ($dateActive) {
        $query->whereDate('date_implantation', $dateActive->format('Y-m-d'));
    }

    $affectations = $query->orderBy('heure_implantation')
        ->orderBy('grand_site_id')
        ->orderBy('bloc_id')
        ->orderBy('lot_affectation_id')
        ->get();

    $lignes = $affectations->groupBy(function ($aff) {
        return implode('-', [
            $aff->beneficiaire_id ?? 'c' . $aff->client_id,
            $aff->bloc_id ?? 0,
        ]);
    })->map(function ($affs, $key) {
        $premier   = $affs->first();
        $lotsTries = $affs->sortBy(fn ($a) => $a->lot?->numero)->values();

        $etape   = $premier->etape_programmation;
        $validee = $etape === 'finalisee';

        return [
            'affectation_ids'    => $affs->pluck('id')->toArray(),
            'beneficiaire'       => $premier->beneficiaire?->nom ?? $premier->client?->name ?? '—',
            'telephone'          => $premier->beneficiaire?->telephone ?? '—',
            'titre_foncier'      => $premier->dossier?->titre_foncier ?? $premier->tf?->title ?? '—',
            'grand_site'         => $premier->grandSite?->nom,
            'site'               => $premier->site?->name,
            'bloc'               => $premier->bloc?->code,
            'lots'               => $lotsTries->pluck('lot.numero')->filter()->implode(', '),
            'superficie'         => $affs->sum(fn ($a) => $a->lot?->superficie ?? 0),
            'facilitateur'       => $premier->dossier?->facilitateur?->nom ?? '—',
            'heure_implantation' => $premier->heure_implantation,
            'date_implantation'  => $premier->date_implantation,
            'geometre_id'        => $premier->geometre_id,
            'geometre_nom'       => $premier->geometre?->name,
            'frais_paye'         => $premier->frais_logistique_paye,
            'statut_presence'    => $premier->statut_presence ?? 'en_attente',
            'etape_programmation'=> $etape,
            'validee'            => $validee,
            'statut_acceptation' => $premier->statut_acceptation,
            'date_acceptation'   => $premier->date_acceptation,
            'motif_refus'        => $premier->motif_refus,
        ];
    })->values();

    $stats = [
        'total'      => $lignes->count(),
        'acceptes'   => $lignes->where('statut_acceptation', 'accepte')->count(),
        'refuses'    => $lignes->where('statut_acceptation', 'refuse')->count(),
        'presents'   => $lignes->where('statut_presence', 'present')->count(),
        'retards'    => $lignes->where('statut_presence', 'retard')->count(),
        'absents'    => $lignes->where('statut_presence', 'absent')->count(),
        'superficie' => $lignes->sum('superficie'),
    ];

    $geometres = \App\Models\User::whereIn('role', ['geometre', 'admin'])
        ->where('actif', true)
        ->orderBy('name')
        ->get(['id', 'name']);

    return view('admin.affectations.programmation-active', [
        'lignes'         => $lignes,
        'stats'          => $stats,
        'geometres'      => $geometres,
        'dateActive'     => $dateActive,
    ]);
}
    // ============================================================
    // ✅ HELPERS — Génération PDF
    // ============================================================

    private function grouperLignesPourPdf($affectations)
    {
        return $affectations->groupBy(function ($aff) {
            return implode('-', [
                $aff->beneficiaire_id ?? 'c' . $aff->client_id,
                $aff->bloc_id ?? 0,
            ]);
        })->map(function ($affs) {
            $premier   = $affs->first();
            $lotsTries = $affs->sortBy(fn ($a) => $a->lot?->numero)->values();

            $statutFinal = match(true) {
                $premier->statut_acceptation === 'accepte' => 'Accepté',
                $premier->statut_acceptation === 'refuse'  => 'Refusé',
                $premier->statut_presence === 'present'    => 'Présent',
                $premier->statut_presence === 'retard'     => 'Retard',
                $premier->statut_presence === 'absent'     => 'Absent',
                default                                     => 'En attente',
            };

            $classeStatut = match(true) {
                $premier->statut_acceptation === 'accepte' => 'accepte',
                $premier->statut_acceptation === 'refuse'  => 'refuse',
                $premier->statut_presence === 'present'    => 'present',
                $premier->statut_presence === 'retard'     => 'retard',
                $premier->statut_presence === 'absent'     => 'absent',
                default                                     => 'en_attente',
            };

            return [
                'affectation_ids'    => $affs->pluck('id')->toArray(),
                'beneficiaire'       => $premier->beneficiaire?->nom ?? $premier->client?->name ?? '—',
                'telephone'          => $premier->beneficiaire?->telephone ?? '—',
                'titre_foncier'      => $premier->dossier?->titre_foncier ?? $premier->tf?->title ?? '—',
                'grand_site'         => $premier->grandSite?->nom,
                'site'               => $premier->site?->name,
                'bloc'               => $premier->bloc?->code,
                'lots'               => $lotsTries->pluck('lot.numero')->filter()->implode(', '),
                'superficie'         => $affs->sum(fn ($a) => $a->lot?->superficie ?? 0),
                'facilitateur'       => $premier->dossier?->facilitateur?->nom ?? '—',
                'heure_implantation' => $premier->heure_implantation,
                'date_implantation'  => $premier->date_implantation,
                'date_affectation'   => $premier->date_affectation,
                'geometre_id'        => $premier->geometre_id,
                'geometre_nom'       => $premier->geometre?->name,
                'frais_paye'         => $premier->frais_logistique_paye,
                'statut_presence'    => $premier->statut_presence ?? 'en_attente',
                'statut_acceptation' => $premier->statut_acceptation,
                'statut_final'       => $statutFinal,
                'classe_statut'      => $classeStatut,
                'date_acceptation'   => $premier->date_acceptation,
                'date_refus'         => $premier->date_refus,
                'motif_refus'        => $premier->motif_refus,
            ];
        })->values();
    }

    private function genererEtStockerPdf(
        string $type,
        $dateSemaine,
        $lignes,
        array $stats,
        string $titreDocument = null,
        bool $avecGeometre = false
    ): DocumentPdf {
        $titreDocument = $titreDocument ?: 'Rapport programmation';

        $slug = \Illuminate\Support\Str::slug($titreDocument);
        if (empty($slug)) {
            $slug = 'rapport';
        }

        $nomFichier    = $slug . '-' . now()->format('Y-m-d-His') . '.pdf';
        $cheminRelatif = 'documents_pdf/' . $nomFichier;

        $data = [
            'type'           => $type,
            'dateSemaine'    => $dateSemaine,
            'lignes'         => $lignes,
            'stats'          => $stats,
            'titreDocument'  => $titreDocument,
            'avecGeometre'   => $avecGeometre,
            'dateGeneration' => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('admin.affectations.rapport-programmation-pdf', $data)
            ->setPaper('A4', 'landscape');

        Storage::disk('public')->put($cheminRelatif, $pdf->output());

        return DocumentPdf::create([
            'type'              => $type,
            'nom_fichier'       => $nomFichier,
            'date_semaine'      => $dateSemaine instanceof \Carbon\Carbon
                                    ? $dateSemaine->format('Y-m-d')
                                    : now()->format('Y-m-d'),
            'chemin_fichier'    => $cheminRelatif,
            'nb_lignes'         => $lignes->count(),
            'nb_lots'           => $stats['total_lots'] ?? 0,
            'superficie_totale' => $stats['total_superficie'] ?? 0,
            'user_id'           => auth()->id(),
        ]);
    }

 /**
 * ✅ Valider l'Étape 1 → génère le PDF ET passe à 'programmee'
 */
public function validerEtape1(Request $request)
{
    try {
        DB::beginTransaction();

        $affectationIds = $request->input('affectation_ids', []);
        $titreDocument  = trim($request->input('titre_document', ''));
        $typeRapport    = $request->input('type_rapport', 'initiale');
        $jourFiltre     = $request->input('jour', null);

        if (empty($titreDocument) || strlen($titreDocument) < 3) {
            return response()->json([
                'success' => false,
                'message' => '❌ Le titre du document est obligatoire (min. 3 caractères).',
            ], 422);
        }

        // ═══════════════════════════════════════════════════════════
        // 🔒 CAS 1 : CLÔTURE depuis la LISTE
        //    → AUCUNE vérification géo/heure
        //    → Statut : 'cloturee' (prête pour Étape 1)
        // ═══════════════════════════════════════════════════════════
        if ($typeRapport === 'cloture') {

            Affectation::whereIn('id', $affectationIds)
                ->whereIn('etape_programmation', ['nouvelle', 'date_attribuee'])
                ->update(['etape_programmation' => 'cloturee']);

            $affectations = Affectation::with([
                'beneficiaire', 'client', 'grandSite', 'site', 'tf', 'bloc', 'lot',
                'geometre', 'dossier.facilitateur',
            ])
            ->whereIn('id', $affectationIds)
            ->get();

            $lignes = $this->grouperLignesPourPdf($affectations);

            $stats = [
                'total_lots'       => $lignes->sum(fn($l) => count(array_filter(explode(',', $l['lots'] ?? '')))),
                'total_superficie' => $lignes->sum('superficie'),
                'acceptes'         => 0,
                'refuses'          => 0,
            ];

            $dateRef = $jourFiltre
                ? \Carbon\Carbon::parse($jourFiltre)
                : now();

            $doc = $this->genererEtStockerPdf(
                'programmation_finale',
                $dateRef,
                $lignes,
                $stats,
                $titreDocument
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Rapport de clôture généré — Les lignes sont prêtes pour l\'Étape 1 (géomètre + heure à attribuer).',
                'doc'     => [
                    'id'  => $doc->id,
                    'url' => $doc->url,
                ],
            ]);
        }

        // ═══════════════════════════════════════════════════════════
        // 📄 CAS 2 : VALIDATION depuis l'ÉTAPE 1
        //    → Vérification géo/heure OBLIGATOIRE
        //    → Statut : 'programmee' (prête pour Étape 2)
        // ═══════════════════════════════════════════════════════════

        // ✅ VÉRIFICATION géo + heure
        $incompletes = Affectation::whereIn('id', $affectationIds)
            ->where(function ($q) {
                $q->whereNull('geometre_id')
                  ->orWhereNull('heure_implantation');
            })
            ->count();

        if ($incompletes > 0) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => "❌ {$incompletes} affectation(s) sans géomètre ou heure. Complétez-les d'abord.",
            ], 422);
        }

        // ✅ Passer à 'programmee'
        Affectation::whereIn('id', $affectationIds)
            ->whereIn('etape_programmation', ['cloturee', 'nouvelle', 'date_attribuee'])
            ->update(['etape_programmation' => 'programmee']);

        $affectations = Affectation::with([
            'beneficiaire', 'client', 'grandSite', 'site', 'tf', 'bloc', 'lot',
            'geometre', 'dossier.facilitateur',
        ])
        ->whereIn('id', $affectationIds)
        ->get();

        $lignes = $this->grouperLignesPourPdf($affectations);

        $stats = [
            'total_lots'       => $lignes->sum(fn($l) => count(array_filter(explode(',', $l['lots'] ?? '')))),
            'total_superficie' => $lignes->sum('superficie'),
            'acceptes'         => 0,
            'refuses'          => 0,
        ];

        $doc = $this->genererEtStockerPdf(
            'programmation_initiale',
            $this->getDateActiveSession() ?? now(),
            $lignes,
            $stats,
            $titreDocument
        );

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => '✅ Étape 1 validée — ' . count($affectationIds) . ' affectation(s) prêtes pour l\'Étape 2.',
            'doc'     => [
                'id'  => $doc->id,
                'url' => $doc->url,
            ],
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur validerEtape1: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => '❌ Erreur : ' . $e->getMessage(),
        ], 500);
    }
}

   public function validerEtape2(Request $request)
{
    try {
        DB::beginTransaction();

        $affectationIds = $request->input('affectation_ids', []);

        if (empty($affectationIds)) {
            return response()->json([
                'success' => false,
                'message' => '❌ Aucune affectation sélectionnée.',
            ], 422);
        }

        // ✅ Marque comme CLÔTURÉE (pas encore programmée)
        Affectation::whereIn('id', $affectationIds)
            ->whereIn('etape_programmation', ['nouvelle', 'date_attribuee'])
            ->update(['etape_programmation' => 'cloturee']);

        DB::commit();

        return response()->json([
            'success' => true,
            'message' => '✅ Étape 1 validée — Les affectations sont prêtes pour l\'Étape 1 (géomètre + heure).',
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur validerEtape2: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => '❌ Erreur : ' . $e->getMessage(),
        ], 500);
    }
}

    // ============================================================
    // ✅ ÉTAPE 3 — Génère le "Rapport des appréciations"
    // ============================================================
    public function validerEtape3(Request $request)
    {
        try {
            DB::beginTransaction();

            $affectationIds = $request->input('affectation_ids', []);
            $titreDocument  = trim($request->input('titre_document', ''));
            $dateActive     = $this->getDateActiveSession();

            if (empty($titreDocument) || strlen($titreDocument) < 3) {
                return response()->json([
                    'success' => false,
                    'message' => '❌ Le titre du document est obligatoire (min. 3 caractères).',
                ], 422);
            }

            Affectation::whereIn('id', $affectationIds)
                ->whereIn('etape_programmation', ['nouvelle', 'date_attribuee', 'programmee'])
                ->update(['etape_programmation' => 'finalisee']);

            $affectations = Affectation::with([
                'beneficiaire', 'client', 'grandSite', 'site', 'tf', 'bloc', 'lot',
                'geometre', 'dossier.facilitateur',
            ])
            ->whereIn('id', $affectationIds)
            ->get();

            $lignes = $this->grouperLignesPourPdf($affectations);

            $stats = [
                'total_lots'       => $lignes->sum(fn($l) => count(array_filter(explode(',', $l['lots'] ?? '')))),
                'total_superficie' => $lignes->sum('superficie'),
                'acceptes'         => $lignes->where('statut_acceptation', 'accepte')->count(),
                'refuses'          => $lignes->where('statut_acceptation', 'refuse')->count(),
            ];

            $doc = $this->genererEtStockerPdf(
                'programmation_active',
                $dateActive ?? now(),
                $lignes,
                $stats,
                $titreDocument
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => '✅ Rapport des appréciations enregistré sous « ' . $titreDocument . ' ».',
                'doc'     => [
                    'id'  => $doc->id,
                    'url' => $doc->url,
                ],
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erreur validerEtape3: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ RAPPORT PDF — ATTRIBUTIONS (date d'affectation)
    // ============================================================
    public function rapportProgrammationPdf(Request $request)
    {
        $titreDocument = trim($request->input('titre_document', ''));

        if (empty($titreDocument) || strlen($titreDocument) < 3) {
            return response()->json([
                'success' => false,
                'message' => '❌ Le titre du document est obligatoire (min. 3 caractères).',
            ], 422);
        }

        $donnees = $this->construireDonneesRapport($request);

        $doc = $this->genererEtStockerPdf(
            'rapport_programmation',
            now(),
            $donnees['lignes'],
            $donnees['stats'],
            $titreDocument,
            false
        );

        return response()->json([
            'success' => true,
            'message' => '✅ Rapport des attributions généré sous « ' . $titreDocument . ' ».',
            'doc'     => [
                'id'  => $doc->id,
                'url' => $doc->url,
            ],
        ]);
    }

    // ============================================================
    // ✅ RAPPORT PDF — PLANIFICATION (date d'implantation)
    // ============================================================
    public function rapportProgrammationGeometrePdf(Request $request)
    {
        $titreDocument = trim($request->input('titre_document', ''));

        if (empty($titreDocument) || strlen($titreDocument) < 3) {
            return response()->json([
                'success' => false,
                'message' => '❌ Le titre du document est obligatoire (min. 3 caractères).',
            ], 422);
        }

        $donnees = $this->construireDonneesRapport($request);

        $doc = $this->genererEtStockerPdf(
            'rapport_programmation_date',
            now(),
            $donnees['lignes'],
            $donnees['stats'],
            $titreDocument,
            false
        );

        return response()->json([
            'success' => true,
            'message' => '✅ Rapport de planification généré sous « ' . $titreDocument . ' ».',
            'doc'     => [
                'id'  => $doc->id,
                'url' => $doc->url,
            ],
        ]);
    }

    // ============================================================
    // Helper — Construire les données pour les rapports 3 & 4
    // ============================================================
    private function construireDonneesRapport(Request $request): array
    {
        $query = Affectation::with([
            'beneficiaire', 'client', 'grandSite', 'site', 'tf', 'bloc', 'lot',
            'geometre', 'dossier.facilitateur',
        ])
        ->where('statut', 'actif')
        ->where('statut_acceptation', 'en_attente')
        ->whereIn('etape_programmation', ['nouvelle', 'date_attribuee']);

        if ($request->filled('jour')) {
            $query->whereDate('date_implantation', $request->jour);
        }

        if ($request->filled('du')) {
            $query->whereDate('date_affectation', '>=', $request->du);
        }
        if ($request->filled('au')) {
            $query->whereDate('date_affectation', '<=', $request->au);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($sub) use ($q) {
                $sub->whereHas('beneficiaire', fn ($b) => $b->where('nom', 'LIKE', "%{$q}%"))
                    ->orWhereHas('client', fn ($c) => $c->where('name', 'LIKE', "%{$q}%"))
                    ->orWhereHas('lot', fn ($l) => $l->where('numero', 'LIKE', "%{$q}%"));
            });
        }

        if ($request->filled('grand_site_id')) {
            $query->where('grand_site_id', $request->grand_site_id);
        }
        if ($request->filled('bloc_id')) {
            $query->where('bloc_id', $request->bloc_id);
        }

        $affectations = $query
            ->orderByDesc('date_implantation')
            ->orderByDesc('date_affectation')
            ->orderByDesc('id')
            ->get();

        $lignes = $affectations->groupBy(function ($aff) {
            return implode('-', [
                $aff->beneficiaire_id ?? 'c' . $aff->client_id,
                $aff->grand_site_id ?? 0,
                $aff->site_id       ?? 0,
                $aff->tf_id         ?? 0,
                $aff->bloc_id       ?? 0,
            ]);
        })->map(function ($affs) {
            $premier   = $affs->first();
            $lotsTries = $affs->sortBy(fn ($a) => $a->lot?->numero)->values();

            return [
                'beneficiaire'       => $premier->beneficiaire?->nom ?? $premier->client?->name ?? '—',
                'telephone'          => $premier->beneficiaire?->telephone ?? $premier->client?->phone ?? '—',
                'grand_site'         => $premier->grandSite?->nom ?? '—',
                'site'               => $premier->site?->name ?? '—',
                'titre_foncier'      => $premier->tf?->title ?? '—',
                'bloc'               => $premier->bloc?->code,
                'lots'               => $lotsTries->pluck('lot.numero')->filter()->implode(', '),
                'superficie'         => $affs->sum(fn ($a) => $a->lot?->superficie ?? 0),
                'date_affectation'   => $premier->date_affectation,
                'date_implantation'  => $premier->date_implantation,
            ];
        })->values();

        $stats = [
            'total_lignes'     => $lignes->count(),
            'total_lots'       => $lignes->sum(fn($l) => count(array_filter(explode(',', $l['lots'] ?? '')))),
            'total_superficie' => $lignes->sum('superficie'),
        ];

        return [
            'lignes' => $lignes,
            'stats'  => $stats,
        ];
    }

    // ============================================================
    // 📄 DOCUMENTS — Les 5 types + "tous"
    // ============================================================

    public function documentsInitiales(Request $request)
    {
        return $this->documentsParType(
            $request,
            'programmation_initiale',
            '📅 Rapport d\'implantation',
            'Rapports générés lors de la validation de l\'Étape 1 — avant appréciation.',
            '#5b21b6',
            '#7c3aed',
            '#ede9fe',
            'initiales'
        );
    }

    public function documentsAvantDate(Request $request)
    {
        return $this->documentsParType(
            $request,
            'rapport_programmation',
            '📄 Rapport des attributions',
            'Rapports basés sur la date d\'affectation (création des affectations).',
            '#7c3aed',
            '#a855f7',
            '#ede9fe',
            'avant-date'
        );
    }

    public function documentsActives(Request $request)
    {
        return $this->documentsParType(
            $request,
            'programmation_active',
            '🎯 Rapport des appréciations',
            'Rapports générés lors de la validation de l\'Étape 2 (acceptation / refus / présence).',
            '#d97706',
            '#f59e0b',
            '#fef3c7',
            'actives'
        );
    }

    public function documentsAvecDate(Request $request)
    {
        return $this->documentsParType(
            $request,
            'rapport_programmation_date',
            '📅 Rapport de planification des implantations',
            'Rapports opérationnels avec la date d\'implantation de chaque affectation.',
            '#f59e0b',
            '#fbbf24',
            '#fef3c7',
            'avec-date'
        );
    }

    public function documentsFinales(Request $request)
    {
        return $this->documentsParType(
            $request,
            'programmation_finale',
            '🔒 Rapport de clôture de planification des implantations',
            'Rapports finaux qui clôturent la journée avec tous les statuts.',
            '#15803d',
            '#16a34a',
            '#dcfce7',
            'finales'
        );
    }

    // ============================================================
    // 🗂️ DOCUMENTS — TOUS
    // ============================================================
    public function documentsTous(Request $request)
    {
        $query = DocumentPdf::with('user')
            ->orderByDesc('date_semaine')
            ->orderByDesc('created_at');

        if ($request->filled('jour')) {
            $query->whereDate('date_semaine', $request->jour);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('q')) {
            $query->where('nom_fichier', 'LIKE', '%' . $request->q . '%');
        }

        $documents = $query->paginate(20)->appends($request->query());

        return view('admin.affectations.documents-tous', compact('documents'));
    }

    // ============================================================
    // 📄 HELPER — Liste filtrée par type (SANS onglets mixtes)
    // ============================================================
    private function documentsParType(
        Request $request,
        string $type,
        string $titre,
        string $sousTitre,
        string $couleurPrincipale,
        string $couleurSecondaire,
        string $couleurFond,
        string $slugVue
    ) {
        $query = DocumentPdf::with('user')
            ->where('type', $type)
            ->orderByDesc('date_semaine')
            ->orderByDesc('created_at');

        if ($request->filled('jour')) {
            $query->whereDate('date_semaine', $request->jour);
        }
        if ($request->filled('q')) {
            $query->where('nom_fichier', 'LIKE', '%' . $request->q . '%');
        }

        $documents = $query->paginate(20)->appends($request->query());

        $statsGlobales = [
            'total'      => DocumentPdf::where('type', $type)->count(),
            'lignes'     => DocumentPdf::where('type', $type)->sum('nb_lignes'),
            'lots'       => DocumentPdf::where('type', $type)->sum('nb_lots'),
            'superficie' => DocumentPdf::where('type', $type)->sum('superficie_totale'),
        ];

        return view('admin.affectations.documents-type', compact(
            'documents',
            'titre',
            'sousTitre',
            'couleurPrincipale',
            'couleurSecondaire',
            'couleurFond',
            'statsGlobales',
            'slugVue'
        ));
    }
 private const DELETE_DOCUMENT_PASSWORD = 'supprimer';
   /**
 * ═══════════════════════════════════════════════════════════════
 * 🗑️ SUPPRESSION D'UN DOCUMENT PDF (avec mot de passe)
 * ═══════════════════════════════════════════════════════════════
 */
public function destroyDocument(Request $request, DocumentPdf $document)
{
    $request->validate([
        'password' => 'required|string',
    ], [
        'password.required' => 'Le mot de passe est obligatoire.',
    ]);

    // ✅ Vérification avec la constante
    if ($request->password !== self::DELETE_DOCUMENT_PASSWORD) {
        return response()->json([
            'success' => false,
            'message' => '❌ Mot de passe incorrect.',
        ], 403);
    }

    try {
        // 1. Supprimer le fichier physique
        if ($document->chemin_fichier && Storage::disk('public')->exists($document->chemin_fichier)) {
            Storage::disk('public')->delete($document->chemin_fichier);
        }

        // 2. Log pour tracer la suppression
        Log::info('🗑️ Document PDF supprimé', [
            'document_id' => $document->id,
            'nom_fichier' => $document->nom_fichier,
            'type'        => $document->type,
            'user_id'     => auth()->id(),
            'user_name'   => auth()->user()?->name,
        ]);

        // 3. Supprimer en base
        $document->delete();

        return response()->json([
            'success' => true,
            'message' => '✅ Document supprimé avec succès.',
        ]);

    } catch (\Exception $e) {
        Log::error('❌ Erreur suppression document : ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => '❌ Erreur lors de la suppression : ' . $e->getMessage(),
        ], 500);
    }
}

}