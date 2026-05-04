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

class RapportController extends Controller
{
    private function getOptions(): array
    {
        return [
            'grandsites'  => GrandSite::orderBy('nom')->get(),
            'sites'       => Site::with('grandSite')->orderBy('name')->get(),
            'tfs'         => Tf::with('site')->orderBy('title')->get(),
            'clients'     => Client::orderBy('name')->get(['id','name','phone']),
            'origines'    => ['eden', 'famille'],
            'types'       => ['deja_implante', 'implantation_prevue', 'dossier_technique', 'morcellement'],
            'statuts'     => ['none', 'en_cours', 'complet'],
            'colonnes'    => [
                'grand_site'    => 'Grand Site',
                'site'          => 'Site',
                'tf'            => 'TF',
                'lot'           => 'Lot',
                'client'        => 'Client',
                'telephone'     => 'Téléphone',
                'commercial'    => 'Commercial',
                'facilitateur'  => 'Facilitateur',
                'chauffeur'     => 'Chauffeur',
                'agent'         => 'Agent commercial',
                'direction'     => 'Direction',
                'grand_site_dossier' => 'GS souhaité',
                'superficie'    => 'Superficie m²',
                'prix'          => 'Prix FCFA',
                'paye'          => 'Payé FCFA',
                'reste'         => 'Reste FCFA',
                'date_prevue'   => 'Date prévue',
                'date_confirmee'=> 'Date confirmée',
                'date_morcel'   => 'Date morcellement',
                'statut_dossier'=> 'Statut dossier',
                'progression'   => 'Progression paiement',
                'nom_dossier'   => 'Nom dossier',
            ],
        ];
    }

    // =============================================
    // DONNÉES DEPUIS DOSSIERS CLIENTS (+ lots si liés)
    // =============================================
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
            'lots.tf.site.grandSite',
            'lots.dossier',
        ]);

        // Filtres clients
        if ($request->filled('clients_ids'))
            $query->whereIn('client_id', $request->clients_ids);

        // Filtre grand site souhaité dans le dossier
        if ($request->filled('grand_sites'))
            $query->whereIn('grand_site_id', $request->grand_sites);

        // Filtres via les lots liés
        if ($request->filled('sites'))
            $query->whereHas('lots.tf', fn($q) => $q->whereIn('site_id', $request->sites));

        if ($request->filled('tfs'))
            $query->whereHas('lots', fn($q) => $q->whereIn('tf_id', $request->tfs));

        if ($request->filled('types'))
            $query->whereHas('lots', fn($q) => $q->whereIn('type', $request->types));

        if ($request->filled('statuts_dossier'))
            $query->whereHas('lots.dossier', fn($q) => $q->whereIn('statut', $request->statuts_dossier));

        // Filtre période (date_paiement)
        if ($request->filled('date_debut'))
            $query->whereHas('paiements', fn($q) => $q->where('date_paiement', '>=', $request->date_debut));
        if ($request->filled('date_fin'))
            $query->whereHas('paiements', fn($q) => $q->where('date_paiement', '<=', $request->date_fin));

        return $query->get();
    }

    private function getTotaux($dossiers): array
    {
        $totalPaye = $totalReste = $totalPrix = $totalSup = 0;

        foreach ($dossiers as $d) {
            $paye       = $d->paiements->sum('montant');
            $prix       = $d->prix_superficie ?? 0;
            $totalPaye  += $paye;
            $totalPrix  += $prix;
            $totalReste += max(0, $prix - $paye);
            $totalSup   += $d->superficie_voulue ?? 0;
        }

        return [
            'nb_dossiers'          => $dossiers->count(),
            'nb_clients'           => $dossiers->pluck('client_id')->unique()->count(),
            'nb_avec_lot'          => $dossiers->filter(fn($d) => $d->lots->count() > 0)->count(),
            'nb_sans_lot'          => $dossiers->filter(fn($d) => $d->lots->count() === 0)->count(),
            'superficie_totale'    => $totalSup,
            'prix_total'           => $totalPrix,
            'total_paye'           => $totalPaye,
            'total_reste'          => $totalReste,
            'avg_progression'      => $dossiers->count() > 0
                ? round($dossiers->map(fn($d) => $d->prix_superficie > 0
                    ? ($d->paiements->sum('montant') / $d->prix_superficie) * 100
                    : 0)->avg())
                : 0,
            'nb_commerciaux'       => $dossiers->pluck('commercial_id')->filter()->unique()->count(),
        ];
    }

    public function index(Request $request)
    {
        $options          = $this->getOptions();
        $dossiers         = collect();
        $totaux           = null;
        $colonnes         = $options['colonnes'];
        $colonnesChoisies = $request->colonnes ?? array_keys($colonnes);
        $rapports         = Rapport::latest()->get();

        if ($request->anyFilled(['grand_sites','sites','tfs','types','statuts_dossier','date_debut','date_fin','clients_ids'])) {
            $dossiers = $this->getDossiers($request);
            $totaux   = $this->getTotaux($dossiers);
        }

        return view('admin.rapport.index', compact(
            'options', 'dossiers', 'totaux',
            'colonnes', 'colonnesChoisies', 'rapports'
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
        \Storage::disk('public')->put($path, $pdf->output());

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
        if ($rapport->fichier_pdf) \Storage::disk('public')->delete($rapport->fichier_pdf);
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

    private function exportCsv($dossiers, $totaux, $colonnesChoisies)
    {
        $allColonnes = $this->getOptions()['colonnes'];
        $headers     = array_values(array_intersect_key($allColonnes, array_flip($colonnesChoisies)));

        $callback = function() use ($dossiers, $headers, $colonnesChoisies) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($file, $headers, ';');

            foreach ($dossiers as $d) {
                $paye  = $d->paiements->sum('montant');
                $reste = max(0, ($d->prix_superficie ?? 0) - $paye);
                $prog  = $d->prix_superficie > 0 ? round(($paye / $d->prix_superficie) * 100) : 0;
                $lot   = $d->lots->first();
                $row   = [];

                foreach ($colonnesChoisies as $col) {
                    $row[] = match($col) {
                        'grand_site'        => $lot?->tf?->site?->grandSite?->nom ?? '-',
                        'site'              => $lot?->tf?->site?->name ?? '-',
                        'tf'                => $lot?->tf?->title ?? '-',
                        'lot'               => $lot ? strtoupper($lot->code) : 'Sans lot',
                        'client'            => $d->client?->name ?? '-',
                        'telephone'         => $d->client?->phone ?? '-',
                        'commercial'        => $d->commercial?->name ?? '-',
                        'facilitateur'      => $d->facilitateur?->nom ?? '-',
                        'chauffeur'         => $d->conducteur?->nom ?? '-',
                        'agent'             => $d->agentCommercial?->nom ?? '-',
                        'direction'         => $d->direction ?? '-',
                        'grand_site_dossier'=> $d->grandSite?->nom ?? '-',
                        'superficie'        => $d->superficie_voulue ?? 0,
                        'prix'              => $d->prix_superficie ?? 0,
                        'paye'              => $paye,
                        'reste'             => $reste,
                        'date_prevue'       => $lot?->date_prevue?->format('d/m/Y') ?? '-',
                        'date_confirmee'    => $lot?->date_confirmee?->format('d/m/Y') ?? '-',
                        'date_morcel'       => $lot?->date_morcellement?->format('d/m/Y') ?? '-',
                        'statut_dossier'    => $lot?->dossier?->statut ?? 'none',
                        'progression'       => $prog . '%',
                        'nom_dossier'       => $d->nom_dossier ?? '-',
                        default             => '-',
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