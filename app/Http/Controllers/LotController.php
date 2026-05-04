<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lot;
use App\Models\Client;
use App\Models\DossierClient;

class LotController extends Controller
{
    public function store(Request $request)  { return $this->saveLot(new Lot(), $request); }
    public function update(Request $request, $id) { return $this->saveLot(Lot::findOrFail($id), $request); }

    private function saveLot(Lot $lot, Request $request)
    {
        try {
            $request->validate([
                'tf_id'            => 'required',
                'svg_id'           => 'required',
                'code'             => 'required',
                'type'             => 'nullable|in:deja_implante,implantation_prevue,dossier_technique,morcellement',
                'client_id'        => 'nullable|exists:clients,id',
                'dossier_client_id'=> 'nullable|exists:dossiers_clients,id',
                'superficie'       => 'nullable|numeric',
                'date_prevue'      => 'nullable|date',
                'date_confirmee'   => 'nullable|date',
                'date_morcellement'=> 'nullable|date',
            ]);

            $lot->tf_id  = $request->tf_id;
            $lot->svg_id = $request->svg_id;
            $lot->code   = $request->code;
            $lot->type   = $request->type;

            // ✅ DOSSIER CLIENT
            if ($request->dossier_client_id) {
                $lot->dossier_client_id = $request->dossier_client_id;
            }

            // ✅ CLIENT — on ne met à jour owner_name QUE si c'est implantation_prevue
            // et seulement si le lot n'a pas encore de client
            if ($request->type === 'implantation_prevue') {
                // Seulement si pas encore de client sur ce lot
                if (!$lot->client_id) {
                    $clientId = $request->client_id;

                    // Ou récupérer via le dossier
                    if (!$clientId && $request->dossier_client_id) {
                        $d = DossierClient::find($request->dossier_client_id);
                        if ($d) $clientId = $d->client_id;
                    }

                    if ($clientId) {
                        $lot->client_id  = $clientId;
                        $client          = Client::find($clientId);
                        $lot->owner_name = $client?->name; // ✅ Posé UNE SEULE fois
                    }
                }
                // Date et superficie
                $lot->date_prevue = $request->date_prevue ?: null;
                $lot->superficie  = $request->superficie;

            } elseif ($request->type === 'deja_implante') {
                // ✅ Pas de mise à jour du client ni de owner_name
                $lot->date_confirmee = $request->date_confirmee ?: null;
                $lot->superficie     = $request->superficie;

            } elseif ($request->type === 'dossier_technique') {
                // ✅ Pas de mise à jour du client ni de owner_name
                $lot->date_confirmee = $request->date_confirmee ?: null;

            } elseif ($request->type === 'morcellement') {
    
    $lot->superficie        = $request->superficie;
    $lot->date_morcellement = $request->date_morcellement ?: null;
    
}

            $lot->color = match ($request->type) {
                'implantation_prevue'=> '#6c757d',
                'deja_implante'      => '#28a745',
                'dossier_technique'  => '#dc3545',
                'morcellement'       => '#fd7e14',
                default              => '#0d6efd',
            };

            $lot->save();
            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
            ], 500);
        }
    }

    public function setOrigin(Request $request)
    {
        try {
            $request->validate([
                'tf_id'     => 'required',
                'svg_id'    => 'required',
                'origine'   => 'required|in:eden,famille',
                'superficie'=> 'required|numeric',
            ]);

            $lot = Lot::firstOrCreate(
                ['tf_id' => $request->tf_id, 'svg_id' => $request->svg_id],
                ['code'  => $request->svg_id]
            );

            $lot->origine    = $request->origine;
            $lot->superficie = $request->superficie;
            $lot->type       = null;
           $lot->color = $request->origine === 'eden'
    ? 'rgba(13, 110, 253, 0.25)'
    : null;
            $lot->save();

            return response()->json(['success' => true]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function clientSearch(Request $request)
    {
        $q = $request->get('q', '');
        return response()->json(
            Client::where('name', 'LIKE', "%{$q}%")
                  ->orWhere('phone', 'LIKE', "%{$q}%")
                  ->limit(10)->get(['id', 'name', 'phone'])
        );
    }

    public function clientPanel($id)
    {
        $client = Client::with([
            'dossiers.facilitateur',
            'dossiers.commercial',
            'dossiers.agentCommercial',
            'dossiers.conducteur',
            'dossiers.grandSite',
            'dossiers.paiements',
            'lots.dossier',
            'lots.tf.site.grandSite',
        ])->findOrFail($id);

        return response()->json([
            'name'    => $client->name,
            'phone'   => $client->phone,
            'dossiers'=> $client->dossiers->map(function($d) {
                $totalPaye = $d->paiements->sum('montant');
                $reste     = max(0, ($d->prix_superficie ?? 0) - $totalPaye);
                return [
                    'id'          => $d->id,
                    'nom'         => $d->nom_dossier,
                    'facilitateur'=> $d->facilitateur?->nom,
                    'commercial'  => $d->commercial?->name,
                    'agent'       => $d->agentCommercial?->nom,
                    'conducteur'  => $d->conducteur?->nom,
                    'grand_site'  => $d->grandSite?->nom,
                    'direction'   => match($d->direction) {
                        'baffoussam'         => 'Baffoussam',
                        'bagante'            => 'Bagante',
                        'direction_generale' => 'Direction Générale',
                        default              => $d->direction,
                    },
                    'superficie'  => $d->superficie_voulue,
                    'prix_ref'    => $d->prix_superficie ? number_format($d->prix_superficie, 0, ',', ' ') : '-',
                    'total_paye'  => number_format($totalPaye, 0, ',', ' '),
                    'reste'       => number_format($reste, 0, ',', ' '),
                    'progression' => $d->prix_superficie > 0 ? round(($totalPaye / $d->prix_superficie) * 100) : 0,
                    'paiements'   => $d->paiements->sortByDesc('date_paiement')->map(fn($p) => [
                        'date'    => $p->date_paiement,
                        'montant' => number_format($p->montant, 0, ',', ' '),
                        'note'    => $p->note,
                    ])->values(),
                ];
            }),
            'lots' => $client->lots->map(fn($lot) => [
                'code'             => strtoupper($lot->code),
                'type'             => $lot->type ? str_replace('_', ' ', $lot->type) : null,
                'color'            => match($lot->type) {
                    'implantation_prevue' => '#090a0a',
                    'deja_implante'       => '#28a745',
                    'dossier_technique'   => '#ff0019',
                    'morcellement'        => '#ff7605',
                    default               => '#0d6efd',
                },
                'prog'             => $lot->dossier?->progression ?? 0,
                'date_prevue'      => $lot->date_prevue?->format('d/m/Y'),
                'date_confirmee'   => $lot->date_confirmee?->format('d/m/Y'),
                'date_sortie'      => $lot->dossier?->date_sortie,
                'date_morcellement'=> $lot->date_morcellement?->format('d/m/Y'),
            ]),
        ]);
    }

    public function hoverInfo($id)
    {
        $lot = Lot::with([
            'client.dossiers.paiements',
            'dossierClient.paiements',
            'tf.site.grandSite',
        ])->findOrFail($id);

        if (!$lot->client) return response()->json(['has_client' => false]);

        $dossier       = $lot->dossierClient;
        $paiements_lot = $dossier
            ? $dossier->paiements->map(fn($p) => ['montant' => $p->montant, 'date_paiement' => $p->date_paiement])
            : collect();

        $paiements_client = $lot->client->dossiers->flatMap(fn($d) =>
            $d->paiements->map(fn($p) => [
                'grand_site'    => $lot->tf?->site?->grandSite?->nom ?? '-',
                'site'          => $lot->tf?->site?->name ?? '-',
                'tf'            => $lot->tf?->title ?? '-',
                'lot'           => strtoupper($lot->code),
                'dossier'       => $d->nom_dossier,
                'montant'       => $p->montant,
                'date_paiement' => $p->date_paiement,
            ])
        );

        return response()->json([
            'has_client'       => true,
            'client_name'      => $lot->client->name,
            'client_phone'     => $lot->client->phone,
            'paiements_lot'    => $paiements_lot,
            'paiements_client' => $paiements_client,
            'total_lot'        => $paiements_lot->sum('montant'),
            'total_client'     => $paiements_client->sum('montant'),
        ]);
    }

    public function vendus(Request $request)
    {
        $query = Lot::with([
            'client', 'tf.site.grandSite',
            'dossierClient.commercial', 'dossierClient.paiements',
            'dossier',
        ]);

        if ($request->filled('grand_site'))
            $query->whereHas('tf.site', fn($q) => $q->where('grand_site_id', $request->grand_site));
        if ($request->filled('site'))
            $query->whereHas('tf', fn($q) => $q->where('site_id', $request->site));
        if ($request->filled('tf'))
            $query->where('tf_id', $request->tf);
        if ($request->filled('bloc'))
            $query->where('code', 'LIKE', $request->bloc . '%');
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('owner_name', 'LIKE', "%{$s}%")
                ->orWhereHas('client', fn($c) => $c->where('name', 'LIKE', "%{$s}%")->orWhere('phone', 'LIKE', "%{$s}%"))
            );
        }

        $lots       = $query->get();
        $grandsites = \App\Models\GrandSite::orderBy('nom')->get();
        $sites      = \App\Models\Site::when($request->grand_site, fn($q) => $q->where('grand_site_id', $request->grand_site))->orderBy('name')->get();
        $tfs        = \App\Models\Tf::when($request->site, fn($q) => $q->where('site_id', $request->site))->orderBy('title')->get();
        $blocs      = Lot::pluck('code')->map(fn($c) => strtoupper(substr($c, 0, 1)))->unique()->sort()->values();

        return view('admin.lots.vendus', compact('lots', 'grandsites', 'sites', 'tfs', 'blocs'));
    }
}