<?php

namespace App\Http\Controllers;

use App\Models\DossierClient;
use App\Models\PaiementTechnique;
use App\Models\PaiementLogistique;
use App\Models\BonPaiement;
use App\Models\Visite;
use App\Models\Visiteur;
use App\Models\GrandSite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Barryvdh\DomPDF\Facade\Pdf;

class ComptableController extends Controller
{
    // ═══════════════════════════════════════════════════════════════
    // 🔐 AUTHENTIFICATION COMPTABLE
    // ═══════════════════════════════════════════════════════════════

    /**
     * Afficher le formulaire de connexion comptable
     */
    public function showLogin()
    {
        // Si déjà connecté en tant que comptable/admin → rediriger
        if (Auth::check() && in_array(Auth::user()->role, ['comptable', 'admin'])) {
            return redirect()->route('comptable.dashboard');
        }

        return view('comptable.login');
    }

    /**
     * Traiter la connexion comptable
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'email' => '❌ Identifiants incorrects.',
            ]);
        }

        $user = Auth::user();

        // Vérifier que le compte est actif
        if (!$user->actif) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => '🚫 Votre compte est désactivé. Contactez l\'administrateur.',
            ]);
        }

        // Vérifier le rôle
        if (!in_array($user->role, ['comptable', 'admin'])) {
            Auth::logout();
            throw ValidationException::withMessages([
                'email' => '⛔ Cet espace est réservé au personnel comptable.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('comptable.dashboard'))
            ->with('success', '✅ Bienvenue ' . $user->name . ' — Espace comptable');
    }

    /**
     * Déconnexion comptable
     */
   public function logout(Request $request)
{
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    // ✅ CORRECTION : comptable.auth.login (et non comptable.login)
    return redirect()->route('comptable.auth.login')
        ->with('success', '👋 Vous êtes déconnecté.');
}

    // ═══════════════════════════════════════════════════════════════
    // 🏠 DASHBOARD COMPTABLE
    // ═══════════════════════════════════════════════════════════════

    public function dashboard()
    {
        $user = Auth::user();

        // Stats globales
        $stats = $this->calculerStatsTechnique(new Request());

        // Pourcentages
        $total = $stats['total_dossiers'];
        $stats['pct_payes']     = $total > 0 ? round(($stats['nb_payes'] / $total) * 100) : 0;
        $stats['pct_partiels']  = $total > 0 ? round(($stats['nb_partiels'] / $total) * 100) : 0;
        $stats['pct_non_payes'] = $total > 0 ? round(($stats['nb_non_payes'] / $total) * 100) : 0;

        // Mes paiements d'aujourd'hui
        $reference = $user->reference ?? $user->email ?? ('USER-' . $user->id);
        $mesPaiements = PaiementTechnique::where('user_reference', $reference)
            ->whereDate('created_at', today())
            ->get();

        // 10 derniers paiements globaux
        $derniersPaiements = PaiementTechnique::with(['dossier.client'])
            ->orderByDesc('date_paiement')
            ->orderByDesc('id')
            ->limit(10)
            ->get();

        return view('comptable.dashboard', compact(
            'stats', 'mesPaiements', 'derniersPaiements'
        ));
    }

    // ═══════════════════════════════════════════════════════════════
    // 💳 LISTE DES PAIEMENTS TECHNIQUES
    // ═══════════════════════════════════════════════════════════════

    public function index(Request $request)
{
    $query = DossierClient::with([
            'client',
            'grandSite',
            'paiementsTechniques',
        ])
        ->orderByDesc('created_at');

    // 🔍 Filtre recherche
    if ($request->filled('q')) {
        $q = trim($request->q);
        $query->where(function ($qry) use ($q) {
            $qry->whereHas('client', fn($cq) =>
                $cq->where('name', 'LIKE', "%{$q}%")
                   ->orWhere('phone', 'LIKE', "%{$q}%"))
                ->orWhere('nom_dossier', 'LIKE', "%{$q}%");
        });
    }

    // 📊 Filtre statut
    if ($request->filled('statut')) {
        if ($request->statut === 'paye') {
            $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                              WHERE dossier_client_id = dossiers_clients.id) >= prix_technique');
        } elseif ($request->statut === 'non_paye') {
            $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                              WHERE dossier_client_id = dossiers_clients.id) = 0');
        } elseif ($request->statut === 'partiel') {
            $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                              WHERE dossier_client_id = dossiers_clients.id) > 0')
                  ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                              WHERE dossier_client_id = dossiers_clients.id) < prix_technique');
        }
    }

    // 🏢 Filtre grand site
    if ($request->filled('grand_site_id')) {
        $query->where('grand_site_id', $request->grand_site_id);
    }

    // ✅ Récupérer les dossiers groupés par client
    $dossiers = $query->paginate(50)->withQueryString();

    // ✅ Grouper par client pour l'affichage
    $dossiersGroupes = $dossiers->getCollection()->groupBy(function ($d) {
        return $d->client_id ?? 0;
    });

    $grandSites = GrandSite::orderBy('nom')->get();
    $stats = $this->calculerStatsTechnique($request);

    return view('admin.comptable.index', compact(
        'dossiers', 'dossiersGroupes', 'grandSites', 'stats'
    ));
}

    // ═══════════════════════════════════════════════════════════════
    // 💳 PAIEMENT AJAX
    // ═══════════════════════════════════════════════════════════════

    public function payerAjax(Request $request, DossierClient $dossier)
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => '🔒 Session expirée. Reconnectez-vous.'
                ], 401);
            }

            $reference = $user->reference ?? $user->email ?? ('USER-' . $user->id);

            $validator = Validator::make($request->all(), [
                'montant'       => 'required|numeric|min:1',
                'date_paiement' => 'required|date',
                'note'          => 'nullable|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                ], 422);
            }

            $dossier->load('paiementsTechniques');
            $dejaPaye = $dossier->paiementsTechniques->sum('montant');
            $prixRef  = $dossier->prix_technique ?? 0;
            $montant  = floatval($request->montant);

            if ($prixRef > 0 && ($dejaPaye + $montant) > $prixRef) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ Dépassement ! Total : ' .
                        number_format($dejaPaye + $montant, 0, ',', ' ') .
                        ' FCFA > Référence : ' . number_format($prixRef, 0, ',', ' ') . ' FCFA'
                ], 422);
            }

            // Créer le paiement
            PaiementTechnique::create([
                'dossier_client_id' => $dossier->id,
                'montant'           => $montant,
                'date_paiement'     => $request->date_paiement,
                'note'              => $request->note ?? 'Paiement technique (Comptable)',
                'user_reference'    => $reference,
            ]);

            // Créer le bon
            $dossier->load(['paiements', 'paiementsTechniques', 'paiementsMorcellements']);
            $totalLogistique = PaiementLogistique::where('dossier_client_id', $dossier->id)->sum('montant');

            $bon = BonPaiement::create([
                'dossier_client_id'        => $dossier->id,
                'numero_bon'               => BonPaiement::genererNumero(),
                'date_bon'                 => $request->date_paiement,
                'versement_dossier'        => 0,
                'versement_technique'      => $montant,
                'versement_logistique'     => 0,
                'versement_morcellement'   => 0,
                'total_dossier_cumul'      => $dossier->paiements->sum('montant'),
                'total_technique_cumul'    => $dossier->paiementsTechniques->sum('montant'),
                'total_logistique_cumul'   => $totalLogistique,
                'total_morcellement_cumul' => $dossier->paiementsMorcellements->sum('montant'),
                'afficher_reste'           => true,
                'notes'                    => 'Paiement technique via interface comptable',
                'user_reference'           => $reference,
                'user_id'                  => $user->id,
            ]);

            $this->creerVisiteDepuisBon($bon, $dossier, $user);

            // Recalculer
            $dossier->refresh();
            $dossier->load('paiementsTechniques');
            $nouveauPaye  = $dossier->paiementsTechniques->sum('montant');
            $nouveauReste = max(0, $prixRef - $nouveauPaye);
            $pct = $prixRef > 0 ? min(100, round(($nouveauPaye / $prixRef) * 100)) : 0;

            if ($nouveauPaye >= $prixRef && $prixRef > 0) {
                $statut = 'paye';     $label = '✅ Payé';        $couleur = '#16a34a';
            } elseif ($nouveauPaye > 0) {
                $statut = 'partiel';  $label = '⏳ Partiel (' . $pct . '%)';  $couleur = '#f59e0b';
            } else {
                $statut = 'non_paye'; $label = '❌ Non payé';    $couleur = '#dc2626';
            }

            return response()->json([
                'success'    => true,
                'message'    => '✅ Paiement de ' . number_format($montant, 0, ',', ' ') .
                                ' FCFA enregistré !',
                'bon_numero' => $bon->numero_bon,
                'data' => [
                    'dossier_id'    => $dossier->id,
                    'deja_paye'     => $nouveauPaye,
                    'deja_paye_fmt' => number_format($nouveauPaye, 0, ',', ' '),
                    'reste'         => $nouveauReste,
                    'reste_fmt'     => number_format($nouveauReste, 0, ',', ' '),
                    'prix_ref'      => $prixRef,
                    'prix_ref_fmt'  => number_format($prixRef, 0, ',', ' '),
                    'pourcentage'   => $pct,
                    'statut'        => $statut,
                    'label'         => $label,
                    'couleur'       => $couleur,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur payerAjax: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // 📜 HISTORIQUE D'UN DOSSIER
    // ═══════════════════════════════════════════════════════════════

    public function historique(DossierClient $dossier)
    {
        $dossier->load(['client', 'grandSite', 'paiementsTechniques']);
        $paiements = $dossier->paiementsTechniques()
            ->orderByDesc('date_paiement')
            ->get();

        return view('admin.comptable.historique', compact('dossier', 'paiements'));
    }

    // ═══════════════════════════════════════════════════════════════
    // 📊 RAPPORT TECHNIQUE
    // ═══════════════════════════════════════════════════════════════

    public function rapport(Request $request)
    {
        $query = DossierClient::with(['client', 'grandSite', 'paiementsTechniques'])
            ->whereRaw('COALESCE(prix_technique, 0) > 0')
            ->orderByDesc('created_at');

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

        if ($request->filled('statut')) {
            if ($request->statut === 'paye') {
                $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                                  WHERE dossier_client_id = dossiers_clients.id) >= prix_technique');
            } elseif ($request->statut === 'non_paye') {
                $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                                  WHERE dossier_client_id = dossiers_clients.id) = 0');
            } elseif ($request->statut === 'partiel') {
                $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                                  WHERE dossier_client_id = dossiers_clients.id) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                                  WHERE dossier_client_id = dossiers_clients.id) < prix_technique');
            }
        }

        $dossiers   = $query->paginate(50)->withQueryString();
        $grandSites = GrandSite::orderBy('nom')->get();
        $stats      = $this->calculerStatsTechnique($request);

        return view('admin.comptable.rapport-technique', compact('dossiers', 'grandSites', 'stats'));
    }

    // ═══════════════════════════════════════════════════════════════
    // 📄 EXPORT DU RAPPORT
    // ═══════════════════════════════════════════════════════════════

    public function rapportExport(Request $request)
    {
        $query = DossierClient::with(['client', 'grandSite', 'paiementsTechniques'])
            ->whereRaw('COALESCE(prix_technique, 0) > 0')
            ->orderByDesc('created_at');

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
        if ($request->filled('statut')) {
            if ($request->statut === 'paye') {
                $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                                  WHERE dossier_client_id = dossiers_clients.id) >= prix_technique');
            } elseif ($request->statut === 'non_paye') {
                $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                                  WHERE dossier_client_id = dossiers_clients.id) = 0');
            } elseif ($request->statut === 'partiel') {
                $query->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                                  WHERE dossier_client_id = dossiers_clients.id) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques 
                                  WHERE dossier_client_id = dossiers_clients.id) < prix_technique');
            }
        }

        $dossiers = $query->get();
        $stats    = $this->calculerStatsTechnique($request);

        if ($request->type === 'pdf') {
            $pdf = Pdf::loadView(
                'admin.comptable.rapport-technique-pdf',
                compact('dossiers', 'stats')
            )->setPaper('a4', 'landscape')->setOptions(['defaultFont' => 'sans-serif']);

            return $pdf->download('rapport_technique_' . now()->format('Y-m-d') . '.pdf');
        }

        // Export CSV
        $filename = 'rapport_technique_' . now()->format('Y-m-d_H-i') . '.csv';
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($dossiers) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($file, [
                '#', 'Client', 'Téléphone', 'Dossier', 'Grand Site',
                'Réf. Technique', 'Payé', 'Reste', 'Progression %',
                'Statut', 'Dernier paiement',
            ], ';');

            foreach ($dossiers as $i => $d) {
                $paye  = $d->paiementsTechniques->sum('montant');
                $prix  = $d->prix_technique ?? 0;
                $reste = max(0, $prix - $paye);
                $pct   = $prix > 0 ? min(100, round(($paye / $prix) * 100)) : 0;
                $statut = $paye >= $prix && $prix > 0
                    ? 'Payé'
                    : ($paye > 0 ? 'Partiel' : 'Non payé');
                $dernier = $d->paiementsTechniques->sortByDesc('date_paiement')->first();

                fputcsv($file, [
                    $i + 1,
                    $d->client?->name ?? '-',
                    $d->client?->phone ?? '-',
                    $d->nom_dossier ?? '-',
                    $d->grandSite?->nom ?? '-',
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
    // 🕐 HISTORIQUE GLOBAL
    // ═══════════════════════════════════════════════════════════════

    public function historiqueGlobal(Request $request)
    {
        $query = PaiementTechnique::with(['dossier.client', 'dossier.grandSite'])
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

        $paiements   = $query->paginate(50)->withQueryString();
        $totalFiltre = (clone $query)->reorder()->sum('montant');

        return view('comptable.historique-global', compact('paiements', 'totalFiltre'));
    }

    // ═══════════════════════════════════════════════════════════════
    // 👤 MES PAIEMENTS
    // ═══════════════════════════════════════════════════════════════

    public function mesPaiements(Request $request)
    {
        $user = Auth::user();
        $reference = $user->reference ?? $user->email ?? ('USER-' . $user->id);

        $query = PaiementTechnique::with(['dossier.client', 'dossier.grandSite'])
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
            'aujourdhui'    => PaiementTechnique::where('user_reference', $reference)
                                ->whereDate('created_at', today())->sum('montant'),
            'ce_mois'       => PaiementTechnique::where('user_reference', $reference)
                                ->whereMonth('created_at', now()->month)
                                ->whereYear('created_at', now()->year)->sum('montant'),
        ];

        return view('comptable.mes-paiements', compact('paiements', 'stats'));
    }

    // ═══════════════════════════════════════════════════════════════
    // 🛠️ MÉTHODES PRIVÉES
    // ═══════════════════════════════════════════════════════════════

    private function calculerStatsTechnique(Request $request): array
    {
        $query = DossierClient::with('paiementsTechniques')
            ->whereRaw('COALESCE(prix_technique, 0) > 0');

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
        $payePayes = 0; $payePartiels = 0; $restePartiels = 0;
        $prixNonPayes = 0; $prixTotal = 0; $payeTotal = 0; $progSum = 0;

        foreach ($tous as $d) {
            $paye  = $d->paiementsTechniques->sum('montant');
            $prix  = $d->prix_technique ?? 0;
            $reste = max(0, $prix - $paye);
            $pct   = $prix > 0 ? min(100, ($paye / $prix) * 100) : 0;

            $prixTotal += $prix;
            $payeTotal += $paye;
            $progSum   += $pct;

            if ($paye >= $prix && $prix > 0) {
                $nbPayes++;
                $payePayes += $paye;
            } elseif ($paye > 0) {
                $nbPartiels++;
                $payePartiels  += $paye;
                $restePartiels += $reste;
            } else {
                $nbNonPayes++;
                $prixNonPayes += $prix;
            }
        }

        $paiementPeriode = 0;
        if ($request->filled('date_debut') || $request->filled('date_fin')) {
            $dateQuery = PaiementTechnique::whereIn('dossier_client_id', $tous->pluck('id'));
            if ($request->filled('date_debut')) {
                $dateQuery->whereDate('date_paiement', '>=', $request->date_debut);
            }
            if ($request->filled('date_fin')) {
                $dateQuery->whereDate('date_paiement', '<=', $request->date_fin);
            }
            $paiementPeriode = $dateQuery->sum('montant');
        }

        $total = $tous->count();

        return [
            'total_dossiers'         => $total,
            'nb_payes'               => $nbPayes,
            'nb_partiels'            => $nbPartiels,
            'nb_non_payes'           => $nbNonPayes,
            'prix_technique_total'   => $prixTotal,
            'paye_technique_total'   => $payeTotal,
            'reste_technique_total'  => max(0, $prixTotal - $payeTotal),
            'paye_payes'             => $payePayes,
            'paye_partiels'          => $payePartiels,
            'reste_partiels'         => $restePartiels,
            'prix_non_payes'         => $prixNonPayes,
            'progression_moyenne'    => $total > 0 ? round($progSum / $total) : 0,
            'paiement_periode_total' => $paiementPeriode,
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

        $detailsPaiements = [];
        if ($bon->versement_technique > 0) {
            $detailsPaiements[] = '🛠️ Technique: ' .
                number_format($bon->versement_technique, 0, ',', ' ') . ' FCFA';
        }

        $note = '💳 Paiement effectué - Bon ' . $bon->numero_bon . "\n";
        $note .= implode("\n", $detailsPaiements);
        $note .= "\n📋 Dossier: " . ($dossier->nom_dossier ?? 'N/A');

        if ($visiteExistante) {
            $visiteExistante->update([
                'note'         => $note,
                'bon_id'       => $bon->id,
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