<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\GrandSite;
use App\Models\Site;
use App\Models\Tf;
use App\Models\Lot;
use App\Models\Client;
use App\Models\DossierClient;
use App\Models\Rapport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class RapportController extends Controller
{
    private function getOptions(): array
    {
        return [
            'grandsites'  => GrandSite::orderBy('nom')->get(),
            'sites'       => Site::with('grandSite')->orderBy('name')->get(),
            'tfs'         => Tf::with('site')->orderBy('title')->get(),
            'clients'     => Client::orderBy('name')->get(['id','name','phone']),
            'types'       => ['disponible', 'indisponible', 'actif', 'inactif'],
            'statuts'     => ['none', 'en_cours', 'complet'],

            // ═══════════════════════════════════════════════════════════════
            // ✅ COLONNES RÉORGANISÉES
            // Ordre : Client → Dossier → Acteurs → Affectations → Paiements → Dates
            // ═══════════════════════════════════════════════════════════════
            'colonnes'    => [

                // 👤 1. INFORMATIONS PERSONNELLES DU CLIENT
                'client'             => 'Client',
                'telephone'          => 'Téléphone',
                'sexe'               => 'Sexe',

                // 📁 2. INFORMATIONS DU DOSSIER
                'nom_dossier'        => 'Nom Dossier',
                'grand_site_dossier' => 'GS souhaité',
                'superficie'         => 'Superficie m²',
                'direction'          => 'Direction',
                'statut_dossier'     => 'Statut Dossier',

                // 👥 3. ACTEURS
                'commercial'         => 'Commercial',
                'facilitateur'       => 'Facilitateur',
                'chauffeur'          => 'Chauffeur',
                'agent'              => 'Agent commercial',

                // 🗺️ 4. AFFECTATIONS
                'grand_site'         => 'Grand Site',
                'site'               => 'Site',
                'tf'                 => 'TF',
                'bloc'               => 'Bloc',
                'lot'                => 'Lot',

                // 💰 5. PAIEMENTS
                'prix_superficie'    => 'Prix Superficie',
                'prix_technique'     => 'Prix Technique',
                'prix_logistique'    => 'Prix Logistique',
                'prix_morcellement'  => 'Prix Morcellement',
                'paye_superficie'    => 'Payé Superficie',
                'paye_technique'     => 'Payé Technique',
                'paye_logistique'    => 'Payé Logistique',
                'paye_morcellement'  => 'Payé Morcellement',
                'total_paye'         => 'Total Payé',
                'total_reste'        => 'Total Reste',
                'progression'        => 'Progression',

                // 📅 6. DATES
                'date_implantation'  => 'Date Implantation',
                'date_dossier_tech'  => 'Date Dossier Tech.',
                'date_morcellement'  => 'Date Morcellement',
            ],
        ];
    }

    private function getDossiers(Request $request)
    {
        $query = DossierClient::with([
            'client',
            'commercial',
            'conducteur',
            'facilitateur',
            'agentCommercial',
            'grandSite',
            'paiements',
            'paiementsTechniques',
            'paiementsMorcellements',
            'paiementsLogistiques',
            'affectations.grandSite',
            'affectations.bloc',
            'affectations.lot',
            'bons',
        ]);

        // ═══════════════════════════════════════════════════════════════
        // ✅ FILTRES : appliqués UNIQUEMENT si une valeur est sélectionnée
        // ═══════════════════════════════════════════════════════════════

        // ── Filtre clients ──
        if ($request->filled('clients_ids') && is_array($request->clients_ids) && count($request->clients_ids) > 0) {
            $query->whereIn('client_id', $request->clients_ids);
        }

        // ── Filtre grand site souhaité ──
        if ($request->filled('grand_sites') && is_array($request->grand_sites) && count($request->grand_sites) > 0) {
            $query->whereIn('grand_site_id', $request->grand_sites);
        }

        // ── Filtre grand site affectation ──
        if ($request->filled('sites') && is_array($request->sites) && count($request->sites) > 0) {
            $query->whereHas('affectations', function($q) use ($request) {
                $q->whereHas('grandSite', function($q2) use ($request) {
                    $q2->whereIn('id', $request->sites);
                });
            });
        }

        // ── Filtre TF ──
        if ($request->filled('tfs') && is_array($request->tfs) && count($request->tfs) > 0) {
            $query->whereHas('affectations', function($q) use ($request) {
                $q->whereHas('bloc', function($q2) use ($request) {
                    $q2->whereHas('tf', function($q3) use ($request) {
                        $q3->whereIn('id', $request->tfs);
                    });
                });
            });
        }

        // ── Filtre types/disponibilité ──
        if ($request->filled('types') && is_array($request->types) && count($request->types) > 0) {
            $query->whereHas('affectations', function($q) use ($request) {
                $q->whereHas('lot', function($q2) use ($request) {
                    $q2->where(function($subQ) use ($request) {
                        if (in_array('disponible', $request->types)) {
                            $subQ->orWhere('disponible', true);
                        }
                        if (in_array('indisponible', $request->types)) {
                            $subQ->orWhere('disponible', false);
                        }
                        if (in_array('actif', $request->types)) {
                            $subQ->orWhere('actif', true);
                        }
                        if (in_array('inactif', $request->types)) {
                            $subQ->orWhere('actif', false);
                        }
                    });
                });
            });
        }

        // ── Filtre statut dossier ──
        if ($request->filled('statuts_dossier') && is_array($request->statuts_dossier) && count($request->statuts_dossier) > 0) {
            $query->where(function($q) use ($request) {
                if (in_array('none', $request->statuts_dossier)) {
                    $q->orWhereNull('etape_actuelle');
                }
                if (in_array('en_cours', $request->statuts_dossier)) {
                    $q->orWhereIn('etape_actuelle', [
                        'implantation_prevue', 'deja_implante', 'dossier_technique'
                    ]);
                }
                if (in_array('complet', $request->statuts_dossier)) {
                    $q->orWhere('etape_actuelle', 'morcellement');
                }
            });
        }

        // ── Filtre date début ──
        if ($request->filled('date_debut')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('paiements', fn($q2) => $q2->where('date_paiement', '>=', $request->date_debut))
                  ->orWhereHas('paiementsTechniques', fn($q2) => $q2->where('date_paiement', '>=', $request->date_debut))
                  ->orWhereHas('paiementsMorcellements', fn($q2) => $q2->where('date_paiement', '>=', $request->date_debut))
                  ->orWhereHas('paiementsLogistiques', fn($q2) => $q2->where('date_paiement', '>=', $request->date_debut));
            });
        }

        // ── Filtre date fin ──
        if ($request->filled('date_fin')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('paiements', fn($q2) => $q2->where('date_paiement', '<=', $request->date_fin))
                  ->orWhereHas('paiementsTechniques', fn($q2) => $q2->where('date_paiement', '<=', $request->date_fin))
                  ->orWhereHas('paiementsMorcellements', fn($q2) => $q2->where('date_paiement', '<=', $request->date_fin))
                  ->orWhereHas('paiementsLogistiques', fn($q2) => $q2->where('date_paiement', '<=', $request->date_fin));
            });
        }

        return $query->orderByDesc('created_at')->get();
    }

    // =============================================
    // CALCUL DES TOTAUX RÉELS
    // =============================================
    private function getTotaux($dossiers): array
    {
        $totalPrixSuperficie = 0;
        $totalPrixTechnique = 0;
        $totalPrixLogistique = 0;
        $totalPrixMorcellement = 0;

        $totalPayeSuperficie = 0;
        $totalPayeTechnique = 0;
        $totalPayeLogistique = 0;
        $totalPayeMorcellement = 0;

        $totalSuperficie = 0;
        $nbAvecAffectation = 0;

        foreach ($dossiers as $d) {
            // Prix
            $totalPrixSuperficie   += $d->prix_superficie   ?? 0;
            $totalPrixTechnique    += $d->prix_technique    ?? 0;
            $totalPrixLogistique   += $d->prix_logistique   ?? 0;
            $totalPrixMorcellement += $d->prix_morcellement ?? 0;

            // Paiements réels
            $totalPayeSuperficie   += $d->paiements->sum('montant');
            $totalPayeTechnique    += $d->paiementsTechniques->sum('montant');
            $totalPayeLogistique   += $d->paiementsLogistiques?->sum('montant') ?? 0;
            $totalPayeMorcellement += $d->paiementsMorcellements->sum('montant');

            // Superficie
            $totalSuperficie += $d->superficie_voulue ?? 0;

            // Affectations
            if ($d->affectations->count() > 0) {
                $nbAvecAffectation++;
            }
        }

        $totalPrix = $totalPrixSuperficie + $totalPrixTechnique + $totalPrixLogistique + $totalPrixMorcellement;
        $totalPaye = $totalPayeSuperficie + $totalPayeTechnique + $totalPayeLogistique + $totalPayeMorcellement;

        return [
            'nb_dossiers'              => $dossiers->count(),
            'nb_clients'               => $dossiers->pluck('client_id')->unique()->count(),
            'nb_avec_lot'              => $nbAvecAffectation,
            'nb_sans_lot'              => $dossiers->count() - $nbAvecAffectation,
            'nb_commerciaux'           => $dossiers->pluck('commercial_id')->filter()->unique()->count(),
            'superficie_totale'        => $totalSuperficie,
            'prix_superficie_total'    => $totalPrixSuperficie,
            'prix_technique_total'     => $totalPrixTechnique,
            'prix_logistique_total'    => $totalPrixLogistique,
            'prix_morcellement_total'  => $totalPrixMorcellement,
            'prix_total'               => $totalPrix,
            'paye_superficie_total'    => $totalPayeSuperficie,
            'paye_technique_total'     => $totalPayeTechnique,
            'paye_logistique_total'    => $totalPayeLogistique,
            'paye_morcellement_total'  => $totalPayeMorcellement,
            'total_paye'               => $totalPaye,
            'total_reste'              => max(0, $totalPrix - $totalPaye),
            'avg_progression'          => $totalPrix > 0
                ? round(($totalPaye / $totalPrix) * 100)
                : 0,
        ];
    }

    public function index(Request $request)
    {
        $options          = $this->getOptions();
        $dossiers         = collect();
        $totaux           = null;
        $colonnes         = $options['colonnes'];
        $colonnesChoisies = $request->colonnes ?? array_keys($colonnes);

        // ✅ TOUJOURS charger les dossiers (avec ou sans filtres)
        $dossiers = $this->getDossiers($request);
        $totaux   = $this->getTotaux($dossiers);

        return view('admin.rapport.index', compact(
            'options', 'dossiers', 'totaux',
            'colonnes', 'colonnesChoisies'
        ));
    }

    public function sauvegarder(Request $request)
    {
        $request->validate([
            'titre'       => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $dossiers            = $this->getDossiers($request);
        $totaux              = $this->getTotaux($dossiers);
        $colonnesChoisies    = $request->colonnes ?? array_keys($this->getOptions()['colonnes']);
        $rapport_titre       = $request->titre;
        $rapport_description = $request->description ?? '';

        $pdf = Pdf::loadView('admin.rapport.pdf', compact(
            'dossiers', 'totaux', 'colonnesChoisies', 'rapport_titre', 'rapport_description'
        ))->setPaper('a4', 'landscape')->setOptions(['defaultFont' => 'sans-serif']);

        $filename = 'rapport_' . now()->format('Y-m-d_His') . '.pdf';
        $path     = 'rapports/' . $filename;
        Storage::disk('public')->put($path, $pdf->output());

        Rapport::create([
            'titre'       => $request->titre,
            'description' => $request->description,
            'filtres'     => $request->except(['_token', 'titre', 'description']),
            'fichier_pdf' => $path,
        ]);

        return redirect()->route('rapport.liste')->with('success', 'Rapport sauvegardé');
    }

    public function liste()
    {
        $rapports = Rapport::latest()->paginate(20);
        return view('admin.rapport.liste', compact('rapports'));
    }

    public function destroy($id)
    {
        $rapport = Rapport::findOrFail($id);
        if ($rapport->fichier_pdf) Storage::disk('public')->delete($rapport->fichier_pdf);
        $rapport->delete();
        return back()->with('success', 'Rapport supprimé');
    }

    public function export(Request $request)
    {
        $dossiers            = $this->getDossiers($request);
        $totaux              = $this->getTotaux($dossiers);
        $colonnesChoisies    = $request->colonnes ?? array_keys($this->getOptions()['colonnes']);
        $type                = $request->get('type', 'pdf');
        $rapport_titre       = $request->titre ?? 'Rapport EDEN GROUP';
        $rapport_description = $request->description ?? '';

        if ($type === 'pdf') {
            $pdf = Pdf::loadView('admin.rapport.pdf', compact(
                'dossiers', 'totaux', 'colonnesChoisies', 'rapport_titre', 'rapport_description'
            ))->setPaper('a4', 'landscape')->setOptions(['defaultFont' => 'sans-serif']);
            return $pdf->download('rapport_' . now()->format('Y-m-d') . '.pdf');
        }

        if ($type === 'excel') return $this->exportCsv($dossiers, $totaux, $colonnesChoisies);
    }

    // =============================================
    // EXPORT CSV AVEC VRAIES DONNÉES
    // =============================================
    private function exportCsv($dossiers, $totaux, $colonnesChoisies)
    {
        $allColonnes = $this->getOptions()['colonnes'];
        $headers     = array_values(array_intersect_key($allColonnes, array_flip($colonnesChoisies)));

        $callback = function() use ($dossiers, $headers, $colonnesChoisies) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers, ';');

            foreach ($dossiers as $d) {
                // Calculs réels
                $payeSuperficie   = $d->paiements->sum('montant');
                $payeTechnique    = $d->paiementsTechniques->sum('montant');
                $payeLogistique   = $d->paiementsLogistiques?->sum('montant') ?? 0;
                $payeMorcellement = $d->paiementsMorcellements->sum('montant');
                $totalPaye        = $payeSuperficie + $payeTechnique + $payeLogistique + $payeMorcellement;

                $prixSuperficie   = $d->prix_superficie ?? 0;
                $prixTechnique    = $d->prix_technique ?? 0;
                $prixLogistique   = $d->prix_logistique ?? 0;
                $prixMorcellement = $d->prix_morcellement ?? 0;
                $totalPrix        = $prixSuperficie + $prixTechnique + $prixLogistique + $prixMorcellement;
                $totalReste       = max(0, $totalPrix - $totalPaye);
                $progression      = $totalPrix > 0 ? round(($totalPaye / $totalPrix) * 100) : 0;

                // ✅ TOUTES les affectations
                $affectations = $d->affectations;

                // ✅ Concaténation de tous les lots
                $lots = $affectations->filter(fn($a) => $a->lot)
                                     ->map(fn($a) => strtoupper($a->lot->numero))
                                     ->implode(', ');

                $row = [];
                foreach ($colonnesChoisies as $col) {
                    $row[] = match($col) {
                        'grand_site'         => $affectations->pluck('grandSite.nom')->filter()->unique()->implode(', ') ?: '-',
                        'site'               => $affectations->pluck('grandSite.nom')->filter()->unique()->implode(', ') ?: '-',
                        'tf'                 => $affectations->pluck('bloc.tf.title')->filter()->unique()->implode(', ') ?: '-',
                        'bloc'               => $affectations->pluck('bloc.code')->filter()->unique()->implode(', ') ?: '-',
                        'lot'                => $lots ?: 'Sans lot',
                        'client'             => $d->client?->name ?? '-',
                        'telephone'          => $d->client?->phone ?? '-',
                        'sexe'               => match($d->client?->sexe) {
                            'masculin' => 'Masculin',
                            'feminin'  => 'Féminin',
                            default    => '-'
                        },
                        'commercial'         => $d->commercial?->name ?? '-',
                        'facilitateur'       => $d->facilitateur?->nom ?? '-',
                        'chauffeur'          => $d->conducteur?->nom ?? '-',
                        'agent'              => $d->agentCommercial?->nom ?? '-',
                        'direction'          => $d->direction ?? '-',
                        'grand_site_dossier' => $d->grandSite?->nom ?? '-',
                        'superficie'         => $d->superficie_voulue ?? 0,
                        'prix_superficie'    => $prixSuperficie,
                        'prix_technique'     => $prixTechnique,
                        'prix_logistique'    => $prixLogistique,
                        'prix_morcellement'  => $prixMorcellement,
                        'paye_superficie'    => $payeSuperficie,
                        'paye_technique'     => $payeTechnique,
                        'paye_logistique'    => $payeLogistique,
                        'paye_morcellement'  => $payeMorcellement,
                        'total_paye'         => $totalPaye,
                        'total_reste'        => $totalReste,
                        'progression'        => $progression . '%',
                        'date_implantation'  => $d->date_implantation_prevue?->format('d/m/Y') ?? '-',
                        'date_dossier_tech'  => $d->date_dossier_technique?->format('d/m/Y') ?? '-',
                        'date_morcellement'  => $d->date_morcellement?->format('d/m/Y') ?? '-',
                        'statut_dossier'     => $d->etape_actuelle ?? 'none',
                        'nom_dossier'        => $d->nom_dossier ?? '-',
                        default              => '-',
                    };
                }
                fputcsv($file, $row, ';');
            }
            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="rapport_' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}