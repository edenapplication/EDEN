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
        // ✅ Plus de filtre sur prix_logistique
        ->whereHas('beneficiaires')       // Uniquement les dossiers avec bénéficiaires
        ->orderByDesc('created_at');

    // Recherche
    if ($request->filled('q')) {
        $q = trim($request->q);
        $query->where(function ($qry) use ($q) {
            $qry->whereHas('client', fn($cq) =>
                $cq->where('name', 'LIKE', "%{$q}%")
                   ->orWhere('phone', 'LIKE', "%{$q}%"))
                ->orWhere('nom_dossier', 'LIKE', "%{$q}%")
                ->orWhereHas('beneficiaires', fn($bq) =>        // ✅ Recherche bénéficiaire
                    $bq->where('nom', 'LIKE', "%{$q}%")
                       ->orWhere('telephone', 'LIKE', "%{$q}%"));
        });
    }

    // Filtre statut
    if ($request->filled('statut')) {
        if ($request->statut === 'paye') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) >= prix_logistique');
        } elseif ($request->statut === 'non_paye') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) = 0');
        } elseif ($request->statut === 'partiel') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) < prix_logistique');
        }
    }

    if ($request->filled('grand_site_id')) {
        $query->where('grand_site_id', $request->grand_site_id);
    }

    $dossiers = $query->get();
    $dossiersGroupes = $dossiers->groupBy(fn($d) => $d->client_id ?? 0);

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

        // ✅ VÉRIFICATION SERVEUR : prix logistique obligatoire
        if (($dossier->prix_logistique ?? 0) <= 0) {
            return response()->json([
                'success' => false,
                'message' => '⚠️ Le prix logistique doit être défini AVANT d\'enregistrer un paiement. Utilisez le bouton "Définir le prix".',
            ], 422);
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

        // Détection ANT
        $dateDuBon = \Carbon\Carbon::parse($request->date_paiement)->format('Y-m-d');
        $dateAujourdhui = now()->format('Y-m-d');
        $prefixeANT = ($dateDuBon !== $dateAujourdhui) ? 'ANT-' : '';

        // Créer le bon
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

            $benefExiste = \App\Models\Beneficiaire::where('id', $beneficiaireId)
                ->where('dossier_client_id', $dossier->id)
                ->exists();

            if (!$benefExiste) {
                throw new \Exception('⚠️ Bénéficiaire invalide pour ce dossier.');
            }

            // ANT
            if ($dateDuBon !== $dateAujourdhui && $dossier->client) {
                $dossier->client->update(['antidate' => true]);
            }

            $paiement = PaiementLogistique::create([
                'dossier_client_id' => $dossier->id,
                'beneficiaire_id'   => $beneficiaireId,
                'montant'           => $montant,
                'date_paiement'     => $request->date_paiement,
                'note'              => 'Bon ' . $bon->numero_bon,
                'user_reference'    => $reference,
            ]);

            BeneficiairePaiement::create([
                'beneficiaire_id'   => $beneficiaireId,
                'dossier_client_id' => $dossier->id,
                'paiement_type'     => 'logistique',
                'paiement_id'       => $paiement->id,
                'montant'           => $montant,
                'date_paiement'     => $request->date_paiement,
                'user_reference'    => $reference,
                'bon_id'            => $bon->id,
            ]);

            $paiementsCrees++;
        }

        $nouveauTotal = PaiementLogistique::where('dossier_client_id', $dossier->id)->sum('montant');
        $bon->update(['total_logistique_cumul' => $nouveauTotal]);

        DB::commit();

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
            'beneficiaires',
            'affectations.site',
            'affectations.tf',
        ])
        ->whereHas('beneficiaires')
        ->orderByDesc('created_at');

    // ─── Recherche ───
    if ($request->filled('q')) {
        $q = trim($request->q);
        $query->where(function ($qry) use ($q) {
            $qry->whereHas('client', fn($cq) =>
                $cq->where('name', 'LIKE', "%{$q}%")
                   ->orWhere('phone', 'LIKE', "%{$q}%"))
                ->orWhere('nom_dossier', 'LIKE', "%{$q}%")
                ->orWhereHas('beneficiaires', fn($bq) =>
                    $bq->where('nom', 'LIKE', "%{$q}%")
                       ->orWhere('telephone', 'LIKE', "%{$q}%"));
        });
    }

    // ─── Filtre grand site ───
    if ($request->filled('grand_site_id')) {
        $query->where('grand_site_id', $request->grand_site_id);
    }

    // ─── Filtre statut ───
    if ($request->filled('statut')) {
        if ($request->statut === 'paye') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) >= prix_logistique');
        } elseif ($request->statut === 'non_paye') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) = 0');
        } elseif ($request->statut === 'partiel') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) < prix_logistique');
        } elseif ($request->statut === 'sans_prix') {
            $query->where(function ($q) {
                $q->whereNull('prix_logistique')->orWhere('prix_logistique', 0);
            });
        }
    }

    // ─── Filtre date implantation prévue ───
    if ($request->filled('date_implantation_prevue')) {
        $query->whereHas('beneficiaires', function ($q) use ($request) {
            $q->whereDate('implantation_prevue', $request->date_implantation_prevue);
        });
    }

   $dossiers = $query->get();
    $grandSites = GrandSite::orderBy('nom')->get();
    $stats      = $this->calculerStatsLogistique($request);
     $activite = $this->calculerActiviteComptable($request);

    // ✅ Total encaissé global (tous utilisateurs confondus)
    $totalEncaisseGlobal = \App\Models\PaiementLogistique::query()
        ->sum('montant');

    // ✅ Encaissements par utilisateur
    $encaissementsParUser = \App\Models\PaiementLogistique::query()
        ->select('user_reference', \DB::raw('SUM(montant) as total'), \DB::raw('COUNT(*) as nb'))
        ->groupBy('user_reference')
        ->orderByDesc('total')
        ->get();

    return view('admin.comptable.rapport-logistique', compact(
        'dossiers', 'grandSites', 'stats', 'totalEncaisseGlobal', 'encaissementsParUser','activite'
    ));
}

private function calculerActiviteComptable(Request $request): array
{
    $comptableReferences = \App\Models\User::where('role', 'comptable')
        ->where('actif', true)
        ->pluck('reference')
        ->filter()
        ->toArray();

    // ✅ Base : paiements dont le dossier a ≥1 bénéficiaire filtré
    $query = \App\Models\PaiementLogistique::query()
        ->whereHas('dossier.beneficiaires', function ($q) use ($request) {
            // Filtre date d'implantation (identique à rapport())
            if ($request->filled('date_implantation')) {
                $q->whereDate('implantation_prevue', $request->date_implantation);
            } elseif ($request->filled('date_implantation_debut') || $request->filled('date_implantation_fin')) {
                if ($request->filled('date_implantation_debut')) {
                    $q->whereDate('implantation_prevue', '>=', $request->date_implantation_debut);
                }
                if ($request->filled('date_implantation_fin')) {
                    $q->whereDate('implantation_prevue', '<=', $request->date_implantation_fin);
                }
            }
        });

    // ✅ Filtre grand site
    if ($request->filled('grand_site_id')) {
        $query->whereHas('dossier', function ($q) use ($request) {
            $q->where('grand_site_id', $request->grand_site_id);
        });
    }

    // ✅ Filtre recherche
    if ($request->filled('q')) {
        $q = trim($request->q);
        $query->whereHas('dossier', function ($dq) use ($q) {
            $dq->where('nom_dossier', 'LIKE', "%{$q}%")
               ->orWhereHas('client', fn($cq) =>
                   $cq->where('name', 'LIKE', "%{$q}%")
                      ->orWhere('phone', 'LIKE', "%{$q}%"));
        });
    }

    // ✅ Filtre statut (via sous-requête, identique à rapport())
    if ($request->filled('statut')) {
        if ($request->statut === 'paye') {
            $query->whereHas('dossier', function ($q) {
                $q->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) >= prix_logistique');
            });
        } elseif ($request->statut === 'non_paye') {
            $query->whereHas('dossier', function ($q) {
                $q->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) = 0');
            });
        } elseif ($request->statut === 'partiel') {
            $query->whereHas('dossier', function ($q) {
                $q->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) < prix_logistique');
            });
        }
    }

    // ✅ Comptables
    $totalComptable = (clone $query)->whereIn('user_reference', $comptableReferences)->sum('montant');
    $nbComptable    = (clone $query)->whereIn('user_reference', $comptableReferences)->count();

    // ✅ Autres
    $totalAutres = (clone $query)->where(function ($q) use ($comptableReferences) {
        $q->whereNotIn('user_reference', $comptableReferences)->orWhereNull('user_reference');
    })->sum('montant');

    $nbAutres = (clone $query)->where(function ($q) use ($comptableReferences) {
        $q->whereNotIn('user_reference', $comptableReferences)->orWhereNull('user_reference');
    })->count();

    return [
        'total_comptable' => $totalComptable,
        'nb_comptable'    => $nbComptable,
        'total_autres'    => $totalAutres,
        'nb_autres'       => $nbAutres,
        'total_global'    => $totalComptable + $totalAutres,
    ];
}

public function rapportExport(Request $request)
{
    // ✅ Note + frais de transport
    $noteRapport = trim($request->input('note', ''));
    if (empty($noteRapport)) $noteRapport = null;

    $fraisTransport = floatval($request->input('frais_transport', 0));
    if ($fraisTransport < 0) $fraisTransport = 0;

    // ═══════════════════════════════════════════════════════════
    // ✅ CONSTRUCTION DE LA REQUÊTE
    // ═══════════════════════════════════════════════════════════
    $query = DossierClient::with([
            'client',
            'grandSite',
            'paiementsLogistiques',
            'beneficiaires',
            'affectations.site',
            'affectations.tf',
        ])
        ->whereHas('beneficiaires')
        ->orderByDesc('created_at');

    // ─── Recherche ───
    if ($request->filled('q')) {
        $q = trim($request->q);
        $query->where(function ($qry) use ($q) {
            $qry->whereHas('client', fn($cq) =>
                $cq->where('name', 'LIKE', "%{$q}%")
                   ->orWhere('phone', 'LIKE', "%{$q}%"))
                ->orWhere('nom_dossier', 'LIKE', "%{$q}%")
                ->orWhereHas('beneficiaires', fn($bq) =>
                    $bq->where('nom', 'LIKE', "%{$q}%")
                       ->orWhere('telephone', 'LIKE', "%{$q}%"));
        });
    }

    // ─── Filtre grand site ───
    if ($request->filled('grand_site_id')) {
        $query->where('grand_site_id', $request->grand_site_id);
    }

    // ─── Filtre statut ───
    if ($request->filled('statut')) {
        if ($request->statut === 'paye') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) >= prix_logistique');
        } elseif ($request->statut === 'non_paye') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) = 0');
        } elseif ($request->statut === 'partiel') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) < prix_logistique');
        } elseif ($request->statut === 'sans_prix') {
            $query->where(function ($q) {
                $q->whereNull('prix_logistique')->orWhere('prix_logistique', 0);
            });
        }
    }

    // ─── ✅ Filtre date d'implantation (jour unique prioritaire) ───
    if ($request->filled('date_implantation')) {
        $query->whereHas('beneficiaires', function ($q) use ($request) {
            $q->whereDate('implantation_prevue', $request->date_implantation);
        });
    } elseif ($request->filled('date_implantation_debut') || $request->filled('date_implantation_fin')) {
        $query->whereHas('beneficiaires', function ($q) use ($request) {
            if ($request->filled('date_implantation_debut')) {
                $q->whereDate('implantation_prevue', '>=', $request->date_implantation_debut);
            }
            if ($request->filled('date_implantation_fin')) {
                $q->whereDate('implantation_prevue', '<=', $request->date_implantation_fin);
            }
        });
    }

    $dossiers = $query->get();
    $stats    = $this->calculerStatsLogistique($request);
    $activite = $this->calculerActiviteComptable($request);

    $resteAReverser = max(0, $activite['total_comptable'] - $fraisTransport);

    // ═══════════════════════════════════════════════════════════
    // ✅ TITRE DYNAMIQUE
    // ═══════════════════════════════════════════════════════════
    $titreRapport = 'Rapport des versements pour la Logistique des implantations';

    if ($request->filled('date_implantation')) {
        $titreRapport .= ' du ' . \Carbon\Carbon::parse($request->date_implantation)->format('d/m/Y');
    } elseif ($request->filled('date_implantation_debut') || $request->filled('date_implantation_fin')) {
        $debut = $request->filled('date_implantation_debut')
            ? \Carbon\Carbon::parse($request->date_implantation_debut)->format('d/m/Y') : null;
        $fin = $request->filled('date_implantation_fin')
            ? \Carbon\Carbon::parse($request->date_implantation_fin)->format('d/m/Y') : null;

        if ($debut && $fin && $debut === $fin) {
            $titreRapport .= ' du ' . $debut;
        } elseif ($debut && $fin) {
            $titreRapport .= ' du ' . $debut . ' au ' . $fin;
        } elseif ($debut) {
            $titreRapport .= ' à partir du ' . $debut;
        } elseif ($fin) {
            $titreRapport .= ' jusqu\'au ' . $fin;
        }
    }

    // ═══════════════════════════════════════════════════════════
    // 📄 EXPORT PDF
    // ═══════════════════════════════════════════════════════════
    if ($request->type === 'pdf') {
        $pdf = Pdf::loadView('admin.comptable.rapport-logistique-pdf', compact(
                'dossiers', 'stats', 'noteRapport', 'titreRapport',
                'activite', 'fraisTransport', 'resteAReverser'
            ))
            ->setPaper('a4', 'landscape')
            ->setOptions(['defaultFont' => 'sans-serif']);

        $nomFichier    = 'rapport_logistique_' . now()->format('Y-m-d_His') . '.pdf';
        $cheminRelatif = 'rapports_logistiques/' . $nomFichier;

        \Storage::disk('public')->put($cheminRelatif, $pdf->output());

        // Nombre bénéficiaires filtrés
        $nbBenefsFiltres = 0;
        foreach ($dossiers as $d) {
            $benefs = $d->beneficiaires ?? collect();

            if ($request->filled('date_implantation')) {
                $benefs = $benefs->filter(fn($b) =>
                    $b->implantation_prevue &&
                    \Carbon\Carbon::parse($b->implantation_prevue)->format('Y-m-d') === $request->date_implantation);
            } elseif ($request->filled('date_implantation_debut') || $request->filled('date_implantation_fin')) {
                $benefs = $benefs->filter(function ($b) use ($request) {
                    if (!$b->implantation_prevue) return false;
                    $dateBenef = \Carbon\Carbon::parse($b->implantation_prevue)->format('Y-m-d');
                    if ($request->filled('date_implantation_debut') && $dateBenef < $request->date_implantation_debut) return false;
                    if ($request->filled('date_implantation_fin') && $dateBenef > $request->date_implantation_fin) return false;
                    return true;
                });
            }
            $nbBenefsFiltres += $benefs->count();
        }

        \App\Models\RapportLogistique::create([
            'titre'                 => $titreRapport,
            'note'                  => $noteRapport,
            'type'                  => 'pdf',
            'chemin_fichier'        => $cheminRelatif,
            'nom_fichier'           => $nomFichier,
            'nb_dossiers'           => $dossiers->count(),
            'nb_beneficiaires'      => $nbBenefsFiltres,
            'total_prix'            => $stats['prix_logistique_total'] ?? 0,
            'total_paye'            => $stats['paye_logistique_total'] ?? 0,
            'total_reste'           => $stats['reste_logistique_total'] ?? 0,
            'total_global_encaisse' => $activite['total_global'],
            'frais_transport'       => $fraisTransport,
            'reste_a_reverser'      => $resteAReverser,
            'filtres'               => $request->only([
                'q', 'grand_site_id', 'statut',
                'date_implantation', 'date_implantation_debut', 'date_implantation_fin',
            ]),
            'user_id'               => auth()->id(),
        ]);

        return response()->download(storage_path('app/public/' . $cheminRelatif), $nomFichier);
    }

    // ═══════════════════════════════════════════════════════════
    // 📊 EXPORT CSV
    // ═══════════════════════════════════════════════════════════
    $filename = 'rapport_logistique_' . now()->format('Y-m-d_H-i') . '.csv';

    $headers = [
        'Content-Type'        => 'text/csv; charset=UTF-8',
        'Content-Disposition' => "attachment; filename=\"{$filename}\"",
    ];

    $callback = function () use (
        $dossiers, $noteRapport, $titreRapport,
        $activite, $fraisTransport, $resteAReverser
    ) {
        $file = fopen('php://output', 'w');
        fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Titre
        fputcsv($file, [$titreRapport], ';');
        fputcsv($file, ['Généré le ' . now()->format('d/m/Y à H:i')], ';');
        fputcsv($file, [], ';');

        // Note
        if (!empty($noteRapport)) {
            fputcsv($file, ['NOTE DU COMPTABLE'], ';');
            fputcsv($file, [$noteRapport], ';');
            fputcsv($file, [], ';');
        }

        // Activité comptable
        fputcsv($file, ['ACTIVITE COMPTABLE'], ';');
        fputcsv($file, ['Total encaissé par les comptables', $activite['total_comptable']], ';');
        fputcsv($file, ['Nombre de paiements comptables', $activite['nb_comptable']], ';');
        fputcsv($file, ['Frais de transport', $fraisTransport], ';');
        fputcsv($file, ['Reste à reverser', $resteAReverser], ';');
        fputcsv($file, [], ';');

        // Autres
        fputcsv($file, ['AUTRES ENCAISSEMENTS'], ';');
        fputcsv($file, ['Total encaissé par les autres', $activite['total_autres']], ';');
        fputcsv($file, ['Nombre de paiements autres', $activite['nb_autres']], ';');
        fputcsv($file, [], ';');

        // Tableau
        fputcsv($file, [
            '#', 'Bénéficiaire', 'Téléphone', 'Grand Site', 'Site', 'TF',
            'Implantation prévue', 'Réf. Logistique', 'Payé', 'Reste',
        ], ';');

        $dateUnique      = request('date_implantation');
        $dateDebutFiltre = request('date_implantation_debut');
        $dateFinFiltre   = request('date_implantation_fin');
        $filtreDateActif = $dateUnique || $dateDebutFiltre || $dateFinFiltre;

        $row = 0;
        foreach ($dossiers as $d) {
            $payeLogi = $d->paiementsLogistiques->sum('montant');
            $prixLogi = $d->prix_logistique ?? 0;
            $benefs   = $d->beneficiaires ?? collect();

            if ($filtreDateActif) {
                $benefs = $benefs->filter(function ($b) use ($dateUnique, $dateDebutFiltre, $dateFinFiltre) {
                    if (!$b->implantation_prevue) return false;
                    $dateBenef = \Carbon\Carbon::parse($b->implantation_prevue)->format('Y-m-d');

                    if ($dateUnique) return $dateBenef === $dateUnique;

                    if ($dateDebutFiltre && $dateBenef < $dateDebutFiltre) return false;
                    if ($dateFinFiltre && $dateBenef > $dateFinFiltre) return false;
                    return true;
                })->values();
            }

            if ($benefs->count() === 0) continue;
            $nbBenefs = max(1, $benefs->count());

            $premiereAffectation = $d->affectations->first();
            $siteNom = $premiereAffectation?->site?->name ?? '-';
            $tfNom   = $premiereAffectation?->tf?->title ?? '-';

            foreach ($benefs as $b) {
                $row++;

                $payeBenef = \App\Models\BeneficiairePaiement::where('dossier_client_id', $d->id)
                    ->where('beneficiaire_id', $b->id)
                    ->where('paiement_type', 'logistique')
                    ->sum('montant');

                if ($payeBenef == 0 && $payeLogi > 0) {
                    $payeBenef = $payeLogi / $nbBenefs;
                }

                $prixBenef  = $prixLogi / $nbBenefs;
                $resteBenef = max(0, $prixBenef - $payeBenef);

                fputcsv($file, [
                    $row,
                    $b->nom ?? '-',
                    $b->telephone ?? '-',
                    $d->grandSite?->nom ?? '-',
                    $siteNom,
                    $tfNom,
                    $b->implantation_prevue
                        ? \Carbon\Carbon::parse($b->implantation_prevue)->format('d/m/Y')
                        : 'non planifiée',
                    $prixBenef,
                    $payeBenef,
                    $resteBenef,
                ], ';');
            }
        }

        fclose($file);
    };

    return response()->stream($callback, 200, $headers);
}

public function rapportsSauvegardes(Request $request)
{
    $query = \App\Models\RapportLogistique::with('user')
        ->orderByDesc('created_at');

    // Filtre par titre / nom de fichier
    if ($request->filled('q')) {
        $q = trim($request->q);
        $query->where(function ($qry) use ($q) {
            $qry->where('titre', 'LIKE', "%{$q}%")
                ->orWhere('nom_fichier', 'LIKE', "%{$q}%");
        });
    }

    // Filtre par période
    if ($request->filled('date_debut')) {
        $query->whereDate('created_at', '>=', $request->date_debut);
    }
    if ($request->filled('date_fin')) {
        $query->whereDate('created_at', '<=', $request->date_fin);
    }

    $rapports = $query->paginate(30)->withQueryString();

    return view('admin.comptable.rapports-sauvegardes', compact('rapports'));
}

public function destroyRapportSauvegarde(\App\Models\RapportLogistique $rapport)
{
    try {
        if ($rapport->chemin_fichier && \Storage::disk('public')->exists($rapport->chemin_fichier)) {
            \Storage::disk('public')->delete($rapport->chemin_fichier);
        }
        $rapport->delete();

        return back()->with('success', 'Rapport supprimé avec succès.');
    } catch (\Exception $e) {
        \Log::error('Erreur destroyRapportSauvegarde : ' . $e->getMessage());
        return back()->with('error', 'Erreur lors de la suppression.');
    }
}


private function calculerStatsLogistique(Request $request): array
{
    $query = DossierClient::with(['paiementsLogistiques', 'beneficiaires'])
        ->whereHas('beneficiaires');

    // ─── Filtres existants ───
    if ($request->filled('q')) {
        $q = trim($request->q);
        $query->where(function ($qry) use ($q) {
            $qry->whereHas('client', fn($cq) =>
                $cq->where('name', 'LIKE', "%{$q}%")
                   ->orWhere('phone', 'LIKE', "%{$q}%"))
                ->orWhere('nom_dossier', 'LIKE', "%{$q}%")
                ->orWhereHas('beneficiaires', fn($bq) =>
                    $bq->where('nom', 'LIKE', "%{$q}%")
                       ->orWhere('telephone', 'LIKE', "%{$q}%"));
        });
    }

    if ($request->filled('grand_site_id')) {
        $query->where('grand_site_id', $request->grand_site_id);
    }

    if ($request->filled('statut')) {
        if ($request->statut === 'paye') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) >= prix_logistique');
        } elseif ($request->statut === 'non_paye') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) = 0');
        } elseif ($request->statut === 'partiel') {
            $query->whereRaw('COALESCE(prix_logistique, 0) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques 
                              WHERE dossier_client_id = dossiers_clients.id) < prix_logistique');
        } elseif ($request->statut === 'sans_prix') {
            $query->where(function ($q) {
                $q->whereNull('prix_logistique')->orWhere('prix_logistique', 0);
            });
        }
    }

    if ($request->filled('date_implantation')) {
        $query->whereHas('beneficiaires', function ($q) use ($request) {
            $q->whereDate('implantation_prevue', $request->date_implantation);
        });
    } elseif ($request->filled('date_implantation_debut') || $request->filled('date_implantation_fin')) {
        $query->whereHas('beneficiaires', function ($q) use ($request) {
            if ($request->filled('date_implantation_debut')) {
                $q->whereDate('implantation_prevue', '>=', $request->date_implantation_debut);
            }
            if ($request->filled('date_implantation_fin')) {
                $q->whereDate('implantation_prevue', '<=', $request->date_implantation_fin);
            }
        });
    }

    $tous = $query->get();

    // ✅ Filtres date pour bénéficiaires (identiques à la vue)
    $dateUnique      = $request->input('date_implantation');
    $dateDebutFiltre = $request->input('date_implantation_debut');
    $dateFinFiltre   = $request->input('date_implantation_fin');
    $filtreDateActif = $dateUnique || $dateDebutFiltre || $dateFinFiltre;

    $nbPayes = 0; $nbPartiels = 0; $nbNonPayes = 0; $nbAucunFrais = 0;
    $prixTotal = 0; $payeTotal = 0;
    $progSum = 0;
    $nbBenefsFiltres = 0;

    foreach ($tous as $d) {
        $payeDossier = $d->paiementsLogistiques->sum('montant');
        $prixDossier = $d->prix_logistique ?? 0;

        $benefs = $d->beneficiaires ?? collect();

        // ✅ FILTRER les bénéficiaires par date (comme la vue)
        if ($filtreDateActif) {
            $benefs = $benefs->filter(function ($b) use ($dateUnique, $dateDebutFiltre, $dateFinFiltre) {
                if (!$b->implantation_prevue) return false;
                $dateBenef = \Carbon\Carbon::parse($b->implantation_prevue)->format('Y-m-d');

                if ($dateUnique) return $dateBenef === $dateUnique;

                if ($dateDebutFiltre && $dateBenef < $dateDebutFiltre) return false;
                if ($dateFinFiltre && $dateBenef > $dateFinFiltre) return false;
                return true;
            })->values();
        }

        // ✅ IGNORER le dossier si aucun bénéficiaire ne matche
        if ($benefs->count() === 0) continue;

        $nbBenefs = max(1, $benefs->count());

        // ✅ Compter PAR BÉNÉFICIAIRE (cohérent avec la vue)
        foreach ($benefs as $b) {
            $nbBenefsFiltres++;

            $payeBenef = \App\Models\BeneficiairePaiement::where('dossier_client_id', $d->id)
                ->where('beneficiaire_id', $b->id)
                ->where('paiement_type', 'logistique')
                ->sum('montant');

            if ($payeBenef == 0 && $payeDossier > 0) {
                $payeBenef = $payeDossier / $nbBenefs;
            }

            $prixBenef  = $prixDossier / $nbBenefs;

            $prixTotal += $prixBenef;
            $payeTotal += $payeBenef;

            if ($prixBenef == 0 && $payeBenef == 0) {
                $nbAucunFrais++;
            } elseif ($payeBenef >= $prixBenef && $prixBenef > 0) {
                $nbPayes++;
            } elseif ($payeBenef > 0) {
                $nbPartiels++;
            } else {
                $nbNonPayes++;
            }
        }

        // Progression par dossier filtré
        $pct = $prixDossier > 0 ? min(100, ($payeDossier / $prixDossier) * 100) : 0;
        $progSum += $pct;
    }

    // ✅ Recalculer le nombre de dossiers qui matchent réellement
    $nbDossiersFiltres = $tous->filter(function ($d) use ($filtreDateActif, $dateUnique, $dateDebutFiltre, $dateFinFiltre) {
        if (!$filtreDateActif) return $d->beneficiaires->count() > 0;

        return $d->beneficiaires->filter(function ($b) use ($dateUnique, $dateDebutFiltre, $dateFinFiltre) {
            if (!$b->implantation_prevue) return false;
            $dateBenef = \Carbon\Carbon::parse($b->implantation_prevue)->format('Y-m-d');

            if ($dateUnique) return $dateBenef === $dateUnique;

            if ($dateDebutFiltre && $dateBenef < $dateDebutFiltre) return false;
            if ($dateFinFiltre && $dateBenef > $dateFinFiltre) return false;
            return true;
        })->count() > 0;
    })->count();

    return [
        'total_dossiers'         => $nbDossiersFiltres,
        'total_beneficiaires'    => $nbBenefsFiltres,
        'nb_payes'               => $nbPayes,
        'nb_partiels'            => $nbPartiels,
        'nb_non_payes'           => $nbNonPayes,
        'nb_aucun_frais'         => $nbAucunFrais,
        'prix_logistique_total'  => $prixTotal,
        'paye_logistique_total'  => $payeTotal,
        'reste_logistique_total' => max(0, $prixTotal - $payeTotal),
        'progression_moyenne'    => $nbDossiersFiltres > 0 ? round($progSum / $nbDossiersFiltres) : 0,
    ];
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

    

    private function creerVisiteDepuisBon(BonPaiement $bon, DossierClient $dossier, $user)
{
    $client = $dossier->client;
    if (!$client) return;

    $visiteur = Visiteur::firstOrCreate(
        ['nom' => $client->name],
        ['numero' => $client->phone, 'type' => 'client']
    );

    $dateVisite = $bon->date_bon ?? now();

    // ✅ Chercher une visite existante du même client le même jour
    $visiteExistante = Visite::where('visiteur_id', $visiteur->id)
        ->whereDate('date_visite', $dateVisite)
        ->first();

    // ✅ Construire la phrase naturelle (comme pour une visite normale)
    $parties = [];
    if ($bon->versement_dossier > 0) {
        $parties[] = "la parcelle (" . number_format($bon->versement_dossier, 0, ',', ' ') . " FCFA)";
    }
    if ($bon->versement_technique > 0) {
        $parties[] = "le dossier technique (" . number_format($bon->versement_technique, 0, ',', ' ') . " FCFA)";
    }
    if ($bon->versement_logistique > 0) {
        $parties[] = "la logistique (" . number_format($bon->versement_logistique, 0, ',', ' ') . " FCFA)";
    }
    if ($bon->versement_morcellement > 0) {
        $parties[] = "le morcellement (" . number_format($bon->versement_morcellement, 0, ',', ' ') . " FCFA)";
    }

    $totalBon = $bon->versement_dossier
              + $bon->versement_technique
            + $bon->versement_logistique
            + $bon->versement_morcellement;

    $detailsPaiement = implode(', ', $parties);
    $dossierNom      = $dossier->nom_dossier ?? 'N/A';

    $nouvellePhrase = "Paiement de " . number_format($totalBon, 0, ',', ' ') . " FCFA "
                    . "pour {$detailsPaiement} "
                    . "sur le dossier « {$dossierNom} » "
                    . "(Bon n° {$bon->numero_bon}).";

    if ($visiteExistante) {
        // ✅ FUSIONNER : ajouter la nouvelle phrase à la suite de l'ancienne
        $ancienneNote = $visiteExistante->note ?? '';
        $nouvelleNote = $ancienneNote
            ? $ancienneNote . "\n" . $nouvellePhrase
            : $nouvellePhrase;

        $visiteExistante->update([
            'note'         => $nouvelleNote,
            'bon_id'       => $bon->id,
            'paiement_lie' => true,
            'heure_depart' => now()->format('H:i:s'),
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
        'note'              => $nouvellePhrase,
        'paiement_lie'      => true,
        'bon_id'            => $bon->id,
    ]);
}
}