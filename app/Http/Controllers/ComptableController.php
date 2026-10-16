<?php

namespace App\Http\Controllers;

use App\Models\DossierClient;
use App\Models\PaiementLogistique;
use App\Models\BeneficiairePaiement;
use App\Models\BonPaiement;
use App\Models\Visite;
use App\Models\Visiteur;
use App\Models\GrandSite;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class ComptableController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // 🔐 AUTHENTIFICATION COMPTABLE
    // ═══════════════════════════════════════════════════════════════

    public function showLogin()
    {
        if (Auth::check() && in_array(Auth::user()->role, ['comptable', 'admin'])) {
            return redirect()->route('comptable.dashboard');
        }
        return view('admin.comptable.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages(['email' => '❌ Identifiants incorrects.']);
        }

        $user = Auth::user();

        if (!$user->actif) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => '🚫 Compte désactivé.']);
        }

        if (!in_array($user->role, ['comptable', 'admin'])) {
            Auth::logout();
            throw ValidationException::withMessages(['email' => '⛔ Espace réservé au personnel comptable.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('comptable.dashboard'))
            ->with('success', '✅ Bienvenue ' . $user->name);
    }

   public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('comptable.auth.login')  // ✅ /comptable/login
        ->with('success', '✅ Déconnecté.');
}

    // ═══════════════════════════════════════════════════════════════
    // 🏠 DASHBOARD COMPTABLE (LOGISTIQUE)
    // ═══════════════════════════════════════════════════════════════

    public function dashboard()
    {
        $user = Auth::user();

        $stats = $this->calculerStatsLogistique(new Request());

        $total = $stats['total_dossiers'];
        $stats['pct_payes']     = $total > 0 ? round(($stats['nb_payes'] / $total) * 100) : 0;
        $stats['pct_partiels']  = $total > 0 ? round(($stats['nb_partiels'] / $total) * 100) : 0;
        $stats['pct_non_payes'] = $total > 0 ? round(($stats['nb_non_payes'] / $total) * 100) : 0;

        $reference = $user->reference ?? $user->email ?? ('USER-' . $user->id);
        $mesPaiements = PaiementLogistique::where('user_reference', $reference)
            ->whereDate('created_at', today())
            ->get();

        $derniersPaiements = PaiementLogistique::with(['dossier.client'])
            ->orderByDesc('date_paiement')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('admin.comptable.dashboard', compact(
            'stats', 'mesPaiements', 'derniersPaiements'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // 💰 LISTE DES PAIEMENTS LOGISTIQUES
    // ═══════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = DossierClient::with([
                'client',
                'grandSite',
                'beneficiaires',
                'paiementsLogistiques',
            ])
            ->whereRaw('COALESCE(prix_logistique, 0) > 0')
            ->orderByDesc('created_at');

        // Recherche
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($qry) use ($q) {
                $qry->whereHas('client', fn($cq) =>
                    $cq->where('name', 'LIKE', "%{$q}%")
                       ->orWhere('phone', 'LIKE', "%{$q}%"))
                    ->orWhere('nom_dossier', 'LIKE', "%{$q}%");
            });
        }

        // Filtre statut
        if ($request->filled('statut')) {
            if ($request->statut === 'paye') {
                $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                                  WHERE dossier_client_id = dossiers_clients.id) >= prix_logistique');
            } elseif ($request->statut === 'non_paye') {
                $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                                  WHERE dossier_client_id = dossiers_clients.id) = 0');
            } elseif ($request->statut === 'partiel') {
                $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                                  WHERE dossier_client_id = dossiers_clients.id) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                                  WHERE dossier_client_id = dossiers_clients.id) < prix_logistique');
            }
        }

        if ($request->filled('grand_site_id')) {
            $query->where('grand_site_id', $request->grand_site_id);
        }

        $dossiers = $query->paginate(50)->withQueryString();
        $dossiersGroupes = $dossiers->getCollection()->groupBy(fn($d) => $d->client_id ?? 0);

        $grandSites = GrandSite::orderBy('nom')->get();
        $stats = $this->calculerStatsLogistique($request);

        $dossiersSansPrix = DossierClient::where(function ($q) {
            $q->whereNull('prix_logistique')->orWhere('prix_logistique', 0);
        })->count();

        return view('admin.comptable.index', compact(
            'dossiers', 'dossiersGroupes', 'grandSites', 'stats', 'dossiersSansPrix'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // 👥 BÉNÉFICIAIRES D'UN DOSSIER
    // ═══════════════════════════════════════════════════════════════

    public function beneficiairesDossier(DossierClient $dossier)
    {
        $dossier->load(['beneficiaires']);

        $prixLogi = $dossier->prix_logistique ?? 0;
        $totalLogi = PaiementLogistique::where('dossier_client_id', $dossier->id)->sum('montant');

        $beneficiaires = $dossier->beneficiaires->map(function ($b) {
            $payeLogi = BeneficiairePaiement::where('beneficiaire_id', $b->id)
                ->where('paiement_type', 'logistique')->sum('montant');

            return [
                'id'         => $b->id,
                'nom'        => $b->nom,
                'telephone'  => $b->telephone,
                'paye_logi'  => $payeLogi,
                'total_paye' => $payeLogi,
            ];
        });

        return response()->json([
            'success' => true,
            'dossier' => [
                'id'              => $dossier->id,
                'nom_dossier'     => $dossier->nom_dossier,
                'prix_logistique' => $prixLogi,
                'total_logi'      => $totalLogi,
                'reste_logi'      => max(0, $prixLogi - $totalLogi),
            ],
            'beneficiaires' => $beneficiaires,
        ]);
    }

    // ═══════════════════════════════════════════════════════════
// PAIEMENT MULTI-BÉNÉFICIAIRES (LOGISTIQUE)
// ═══════════════════════════════════════════════════════════
public function payerMulti(Request $request, DossierClient $dossier)
{
    try {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => '🔒 Session expirée.'], 401);
        }

        $reference = $user->reference ?? $user->email ?? ('USER-' . $user->id);

        $validator = Validator::make($request->all(), [
            'versements'                   => 'required|array|min:1',
            'versements.*.beneficiaire_id' => 'required|exists:beneficiaires,id',
            'versements.*.type'            => 'required|in:logistique',
            'versements.*.montant'         => 'required|numeric|min:1',
            'date_paiement'                => 'required|date',
            'note'                         => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
            ], 422);
        }

        DB::beginTransaction();

        // ✅ Détection ANT
        $dateDuBon = \Carbon\Carbon::parse($request->date_paiement)->format('Y-m-d');
        $dateAujourdhui = now()->format('Y-m-d');
        $prefixeANT = ($dateDuBon !== $dateAujourdhui) ? 'ANT-' : '';

        // ✅ Créer le bon
        $dossier->load(['paiements']);
        $totalLogiAvant = PaiementLogistique::where('dossier_client_id', $dossier->id)->sum('montant');
        $totalVersement = collect($request->versements)->sum('montant');

        $bon = BonPaiement::create([
            'dossier_client_id'        => $dossier->id,
            'numero_bon'               => $prefixeANT . BonPaiement::genererNumero(),
            'date_bon'                 => $request->date_paiement,
            'versement_dossier'        => 0,
            'versement_technique'      => 0,
            'versement_logistique'     => $totalVersement,
            'versement_morcellement'   => 0,
            'total_dossier_cumul'      => 0,
            'total_technique_cumul'    => 0,
            'total_logistique_cumul'   => $totalLogiAvant + $totalVersement,
            'total_morcellement_cumul' => 0,
            'afficher_reste'           => true,
            'notes'                    => $request->note ?? 'Paiement logistique multi-bénéficiaires',
            'user_reference'           => $reference,
            'user_id'                  => $user->id,
        ]);

        $paiementsCrees = 0;

        foreach ($request->versements as $v) {
            $beneficiaireId = $v['beneficiaire_id'];
            $montant        = floatval($v['montant']);

            // Vérifier que le bénéficiaire appartient au dossier
            $benefExiste = \App\Models\Beneficiaire::where('id', $beneficiaireId)
                ->where('dossier_client_id', $dossier->id)
                ->exists();

            if (!$benefExiste) {
                throw new \Exception('⚠️ Bénéficiaire invalide pour ce dossier.');
            }

            // ✅ Après création du bon
$dateDuBon = \Carbon\Carbon::parse($request->date_paiement)->format('Y-m-d');
$dateAujourdhui = now()->format('Y-m-d');

if ($dateDuBon !== $dateAujourdhui && $dossier->client) {
    $dossier->client->update(['antidate' => true]);
}

            // Créer le paiement logistique
            $paiement = PaiementLogistique::create([
                'dossier_client_id' => $dossier->id,
                'beneficiaire_id'   => $beneficiaireId,
                'montant'           => $montant,
                'date_paiement'     => $request->date_paiement,
                'note'              => 'Bon ' . $bon->numero_bon,
                'user_reference'    => $reference,
            ]);

            // ✅ Traçabilité AVEC bon_id
            BeneficiairePaiement::create([
                'beneficiaire_id'   => $beneficiaireId,
                'dossier_client_id' => $dossier->id,
                'paiement_type'     => 'logistique',
                'paiement_id'       => $paiement->id,
                'montant'           => $montant,
                'date_paiement'     => $request->date_paiement,
                'user_reference'    => $reference,
                'bon_id'            => $bon->id,   // ✅ LIEN DIRECT
            ]);

            $paiementsCrees++;
        }

        // Mettre à jour les totaux cumulés du bon
        $nouveauTotal = PaiementLogistique::where('dossier_client_id', $dossier->id)->sum('montant');
        $bon->update(['total_logistique_cumul' => $nouveauTotal]);

        DB::commit();

        // Créer la visite
        $this->creerVisiteDepuisBon($bon, $dossier, $user);

        return response()->json([
            'success'         => true,
            'message'         => '✅ ' . $paiementsCrees . ' paiement(s) enregistré(s) — Bon ' . $bon->numero_bon,
            'bon_numero'      => $bon->numero_bon,
            'bon_id'          => $bon->id,
            'total_verse'     => $totalVersement,
            'paiements_crees' => $paiementsCrees,
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur payerMulti: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => '❌ ' . $e->getMessage(),
        ], 500);
    }
}

    // ═══════════════════════════════════════════════════════════════
    // 👤 CRÉER UN BÉNÉFICIAIRE (AJAX)
    // ═══════════════════════════════════════════════════════════════

    public function creerBeneficiaireAjax(Request $request, DossierClient $dossier)
    {
        try {
            $validator = Validator::make($request->all(), [
                'nom'       => 'required|string|max:150',
                'telephone' => 'nullable|string|max:50',
                'client_id' => 'nullable|exists:clients,id',
                'cni'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            if (!$request->filled('client_id') && !$request->hasFile('cni')) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ La CNI est obligatoire pour un nouveau bénéficiaire.',
                ], 422);
            }

            $cniPath = null;
            if ($request->hasFile('cni')) {
                $cniPath = $request->file('cni')->store('cni_beneficiaires', 'public');
            }

            $benef = \App\Models\Beneficiaire::create([
                'dossier_client_id'    => $dossier->id,
                'client_id'            => $request->client_id ?: $dossier->client_id,
                'nom'                  => $request->nom,
                'telephone'            => $request->telephone,
                'cni_path'             => $cniPath,
                'superficie_attribuee' => 0,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bénéficiaire créé',
                'beneficiaire' => [
                    'id'        => $benef->id,
                    'nom'       => $benef->nom,
                    'telephone' => $benef->telephone,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur creerBeneficiaireAjax : ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // 📜 HISTORIQUE D'UN DOSSIER (LOGISTIQUE)
    // ═══════════════════════════════════════════════════════════════

    public function historique(DossierClient $dossier)
    {
        $dossier->load(['client', 'grandSite', 'paiementsLogistiques']);
        $paiements = $dossier->paiementsLogistiques()
            ->orderByDesc('date_paiement')
            ->get();

        return view('admin.comptable.historique', compact('dossier', 'paiements'));
    }

    // ═══════════════════════════════════════════════════════════════
    // 🕐 HISTORIQUE GLOBAL (LOGISTIQUE)
    // ═══════════════════════════════════════════════════════════════

    public function historiqueGlobal(Request $request)
    {
        $query = PaiementLogistique::with(['dossier.client', 'dossier.grandSite', 'beneficiaire'])
            ->orderByDesc('date_paiement')
            ->orderByDesc('id');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($qry) use ($q) {
                $qry->whereHas('dossier.client', fn($cq) =>
                    $cq->where('name', 'LIKE', "%{$q}%")
                       ->orWhere('phone', 'LIKE', "%{$q}%"))
                    ->orWhereHas('dossier', fn($dq) =>
                        $dq->where('nom_dossier', 'LIKE', "%{$q}%"));
            });
        }

        if ($request->filled('du')) {
            $query->whereDate('date_paiement', '>=', $request->du);
        }
        if ($request->filled('au')) {
            $query->whereDate('date_paiement', '<=', $request->au);
        }
        if ($request->filled('reference')) {
            $query->where('user_reference', $request->reference);
        }

        $paiements = $query->paginate(50)->withQueryString();
        $totalFiltre = (clone $query)->reorder()->sum('montant');

        return view('admin.comptable.historique-global', compact('paiements', 'totalFiltre'));
    }

    // ═══════════════════════════════════════════════════════════════
    // 👤 MES PAIEMENTS (LOGISTIQUE)
    // ═══════════════════════════════════════════════════════════════

    public function mesPaiements(Request $request)
    {
        $user = Auth::user();
        $reference = $user->reference ?? $user->email ?? ('USER-' . $user->id);

        $query = PaiementLogistique::with(['dossier.client', 'dossier.grandSite'])
            ->where('user_reference', $reference)
            ->orderByDesc('date_paiement')
            ->orderByDesc('id');

        if ($request->filled('du')) {
            $query->whereDate('date_paiement', '>=', $request->du);
        }
        if ($request->filled('au')) {
            $query->whereDate('date_paiement', '<=', $request->au);
        }

        $paiements = $query->paginate(50)->withQueryString();

        $stats = [
            'total_montant' => (clone $query)->reorder()->sum('montant'),
            'total_count'   => (clone $query)->reorder()->count(),
            'aujourdhui'    => PaiementLogistique::where('user_reference', $reference)
                                ->whereDate('created_at', today())->sum('montant'),
            'ce_mois'       => PaiementLogistique::where('user_reference', $reference)
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->sum('montant'),
        ];

        return view('admin.comptable.mes-paiements', compact('paiements', 'stats'));
    }

    // ═══════════════════════════════════════════════════════════════
    // 📊 RAPPORT LOGISTIQUE
    // ═══════════════════════════════════════════════════════════════

   public function rapport(Request $request)
{
    $query = DossierClient::with([
            'client',
            'grandSite',
            'paiementsLogistiques',
            'beneficiaires',        // ✅ pour récupérer implantation_prevue
        ])
        ->whereRaw('COALESCE(prix_logistique, 0) > 0')
        ->orderByDesc('created_at');

    // ─── Recherche ───
    if ($request->filled('q')) {
        $q = trim($request->q);
        $query->where(function ($qry) use ($q) {
            $qry->whereHas('client', fn($cq) =>
                $cq->where('name', 'LIKE', "%{$q}%")
                   ->orWhere('phone', 'LIKE', "%{$q}%"))
                ->orWhere('nom_dossier', 'LIKE', "%{$q}%");
        });
    }

    // ─── Filtre grand site ───
    if ($request->filled('grand_site_id')) {
        $query->where('grand_site_id', $request->grand_site_id);
    }

    // ─── Filtre statut ───
    if ($request->filled('statut')) {
        if ($request->statut === 'paye') {
            $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) >= prix_logistique');
        } elseif ($request->statut === 'non_paye') {
            $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) = 0');
        } elseif ($request->statut === 'partiel') {
            $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) < prix_logistique');
        }
    }

    // ─── ✅ Filtre par date d'implantation prévue (sur les BÉNÉFICIAIRES) ───
    if ($request->filled('date_implantation_prevue')) {
        $query->whereHas('beneficiaires', function ($q) use ($request) {
            $q->whereDate('implantation_prevue', $request->date_implantation_prevue);
        });
    }

    $dossiers   = $query->paginate(50)->withQueryString();
    $grandSites = GrandSite::orderBy('nom')->get();
    $stats      = $this->calculerStatsLogistique($request);

    return view('admin.comptable.rapport-logistique', compact('dossiers', 'grandSites', 'stats'));
}

public function rapportExport(Request $request)
{
    $query = DossierClient::with([
            'client',
            'grandSite',
            'paiementsLogistiques',
            'beneficiaires',
        ])
        ->whereRaw('COALESCE(prix_logistique, 0) > 0')
        ->orderByDesc('created_at');

    // ─── Recherche ───
    if ($request->filled('q')) {
        $q = trim($request->q);
        $query->where(function ($qry) use ($q) {
            $qry->whereHas('client', fn($cq) =>
                $cq->where('name', 'LIKE', "%{$q}%")
                   ->orWhere('phone', 'LIKE', "%{$q}%"))
                ->orWhere('nom_dossier', 'LIKE', "%{$q}%");
        });
    }

    // ─── Filtre grand site ───
    if ($request->filled('grand_site_id')) {
        $query->where('grand_site_id', $request->grand_site_id);
    }

    // ─── Filtre statut ───
    if ($request->filled('statut')) {
        if ($request->statut === 'paye') {
            $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) >= prix_logistique');
        } elseif ($request->statut === 'non_paye') {
            $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) = 0');
        } elseif ($request->statut === 'partiel') {
            $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) < prix_logistique');
        }
    }

    // ─── ✅ Filtre par date d'implantation prévue (BÉNÉFICIAIRES) ───
    if ($request->filled('date_implantation_prevue')) {
        $query->whereHas('beneficiaires', function ($q) use ($request) {
            $q->whereDate('implantation_prevue', $request->date_implantation_prevue);
        });
    }

    $dossiers = $query->get();
    $stats    = $this->calculerStatsLogistique($request);

    // ═══════════════════════════════════════════════════════════
    // 📄 EXPORT PDF
    // ═══════════════════════════════════════════════════════════
    if ($request->type === 'pdf') {
        $pdf = Pdf::loadView('admin.comptable.rapport-logistique-pdf', compact('dossiers', 'stats'))
            ->setPaper('a4', 'landscape')
            ->setOptions(['defaultFont' => 'sans-serif']);

        return $pdf->download('rapport_logistique_' . now()->format('Y-m-d') . '.pdf');
    }

    // ═══════════════════════════════════════════════════════════
    // 📊 EXPORT CSV
    // ═══════════════════════════════════════════════════════════
    $filename = 'rapport_logistique_' . now()->format('Y-m-d_H-i') . '.csv';

    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $callback = function () use ($dossiers) {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($file, [
            '#',
            'Client',
            'Téléphone',
            'Dossier',
            'Grand Site',
            'Implantation prévue',
            'Bénéficiaire(s)',
            'Réf. Logistique',
            'Payé',
            'Reste',
            'Progression %',
            'Statut',
            'Dernier paiement',
        ], ';');

        foreach ($dossiers as $i => $d) {
            $paye  = $d->paiementsLogistiques->sum('montant');
            $prix  = $d->prix_logistique ?? 0;
            $reste = max(0, $prix - $paye);
            $pct   = $prix > 0 ? min(100, round(($paye / $prix) * 100)) : 0;
            $statut = $paye >= $prix && $prix > 0 ? 'Payé' : ($paye > 0 ? 'Partiel' : 'Non payé');

            $dernier = $d->paiementsLogistiques->sortByDesc('date_paiement')->first();

            // ✅ Date implantation prévue (la plus récente sur les bénéficiaires)
            $dateImplantationPrevue = ($d->beneficiaires ?? collect())
                ->whereNotNull('implantation_prevue')
                ->sortByDesc('implantation_prevue')
                ->first()?->implantation_prevue;

            // ✅ Bénéficiaires avec montant payé si disponible
            $benefsPaiement = \App\Models\BeneficiairePaiement::with('beneficiaire')
                ->where('dossier_client_id', $d->id)
                ->where('paiement_type', 'logistique')
                ->get()
                ->groupBy('beneficiaire_id');

            $benefsListe = [];
            foreach ($benefsPaiement as $benefId => $paiements) {
                $totalBenef = $paiements->sum('montant');
                $nomBenef   = $paiements->first()->beneficiaire?->nom ?? 'Inconnu';
                $benefsListe[] = "{$nomBenef} ({$totalBenef} FCFA)";
            }

            $benefsSansPaiement = ($d->beneficiaires ?? collect())->filter(function ($b) use ($benefsPaiement) {
                return !isset($benefsPaiement[$b->id]);
            });
            foreach ($benefsSansPaiement as $b) {
                $benefsListe[] = "{$b->nom} (0 FCFA)";
            }

            $benefsTexte = !empty($benefsListe) ? implode(' | ', $benefsListe) : '-';

            fputcsv($file, [
                $i + 1,
                $d->client?->name ?? '-',
                $d->client?->phone ?? '-',
                $d->nom_dossier ?? '-',
                $d->grandSite?->nom ?? '-',
                $dateImplantationPrevue
                    ? \Carbon\Carbon::parse($dateImplantationPrevue)->format('d/m/Y')
                    : '-',
                $benefsTexte,
                $prix,
                $paye,
                $reste,
                $pct,
                $statut,
                $dernier ? \Carbon\Carbon::parse($dernier->date_paiement)->format('d/m/Y') : '-',
            ], ';');
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

    // ═══════════════════════════════════════════════════════════════
    // ⚙️ DÉFINIR LE PRIX LOGISTIQUE
    // ═══════════════════════════════════════════════════════════════

    public function definirPrix(Request $request, DossierClient $dossier)
    {
        try {
            $validator = Validator::make($request->all(), [
                'prix_logistique' => 'required|numeric|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $prix = floatval($request->prix_logistique);

            if ($dossier->prix_logistique > 0) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ Un prix logistique est déjà défini.',
                ], 422);
            }

            $dossier->update(['prix_logistique' => $prix]);

            return response()->json([
                'success' => true,
                'message' => 'Prix logistique défini : ' . number_format($prix, 0, ',', ' ') . ' FCFA',
                'prix'    => $prix,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur definirPrix: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // 🛠️ MÉTHODES PRIVÉES
    // ═══════════════════════════════════════════════════════════════

    private function calculerStatsLogistique(Request $request): array
    {
        $query = DossierClient::with('paiementsLogistiques')
            ->whereRaw('COALESCE(prix_logistique, 0) > 0');

        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function ($qry) use ($q) {
                $qry->whereHas('client', fn($cq) =>
                    $cq->where('name', 'LIKE', "%{$q}%")
                       ->orWhere('phone', 'LIKE', "%{$q}%"))
                    ->orWhere('nom_dossier', 'LIKE', "%{$q}%");
            });
        }

        if ($request->filled('grand_site_id')) {
            $query->where('grand_site_id', $request->grand_site_id);
        }

        $tous = $query->get();

        $nbPayes = 0; $nbPartiels = 0; $nbNonPayes = 0;
        $prixTotal = 0; $payeTotal = 0; $progSum = 0;

        foreach ($tous as $d) {
            $paye = $d->paiementsLogistiques->sum('montant');
            $prix = $d->prix_logistique ?? 0;
            $pct = $prix > 0 ? min(100, ($paye / $prix) * 100) : 0;

            $prixTotal += $prix;
            $payeTotal += $paye;
            $progSum += $pct;

            if ($paye >= $prix && $prix > 0) {
                $nbPayes++;
            } elseif ($paye > 0) {
                $nbPartiels++;
            } else {
                $nbNonPayes++;
            }
        }

        return [
            'total_dossiers'         => $tous->count(),
            'nb_payes'               => $nbPayes,
            'nb_partiels'            => $nbPartiels,
            'nb_non_payes'           => $nbNonPayes,
            'prix_logistique_total'  => $prixTotal,
            'paye_logistique_total'  => $payeTotal,
            'reste_logistique_total' => max(0, $prixTotal - $payeTotal),
            'progression_moyenne'    => $tous->count() > 0 ? round($progSum / $tous->count()) : 0,
        ];
    }

    private function creerVisiteDepuisBon(BonPaiement $bon, DossierClient $dossier, $user)
    {
        $client = $dossier->client;

        $visiteur = Visiteur::firstOrCreate(
            ['nom' => $client->name],
            ['numero' => $client->phone, 'type' => 'client']
        );

        $dateVisite = $bon->date_bon ?? now();
        $visiteExistante = Visite::where('visiteur_id', $visiteur->id)
            ->whereDate('date_visite', $dateVisite)
            ->first();

        $note = '💳 Paiement logistique - Bon ' . $bon->numero_bon . "\n";
        if ($bon->versement_logistique > 0) {
            $note .= '🚗 Logistique: ' . number_format($bon->versement_logistique, 0, ',', ' ') . ' FCFA';
        }
        $note .= "\n📋 Dossier: " . ($dossier->nom_dossier ?? 'N/A');

        if ($visiteExistante) {
            $visiteExistante->update([
                'note' => $note,
                'bon_id' => $bon->id,
                'paiement_lie' => true,
            ]);
            return;
        }

        Visite::create([
            'visiteur_id'       => $visiteur->id,
            'client_id'         => $client->id,
            'dossier_client_id' => $dossier->id,
            'grand_site_id'     => $dossier->grand_site_id,
            'site_id'           => null,
            'date_visite'       => $dateVisite,
            'heure_arrivee'     => now()->format('H:i:s'),
            'heure_depart'      => null,
            'type_personne'     => 'client',
            'note'              => $note,
            'paiement_lie'      => true,
            'bon_id'            => $bon->id,
        ]);
    }
}