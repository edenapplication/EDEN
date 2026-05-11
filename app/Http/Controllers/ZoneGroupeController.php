<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ZoneGroupe;
use App\Models\Client;
use App\Models\Lot;
use App\Models\DossierClient;
use App\Models\DossierTechnique;

class ZoneGroupeController extends Controller
{
    public function store(Request $request)
    {
        try {
            $request->validate([
                'tf_id'  => 'required|exists:tfs,id',
                'points' => 'required|array|min:3',
                'nom'    => 'nullable|string|max:100',
            ]);

            $clientId  = $request->client_id;
            $ownerName = $request->nom;

            if ($clientId) {
                $client    = Client::find($clientId);
                $ownerName = $client?->name;
            }

            $lotIds       = $request->lot_ids ?? [];
            $superfTotale = !empty($lotIds) ? Lot::whereIn('id', $lotIds)->sum('superficie') : 0;

            $zone = ZoneGroupe::create([
                'tf_id'             => $request->tf_id,
                'client_id'         => $clientId,
                'dossier_client_id' => $request->dossier_client_id,
                'nom'               => $request->nom,
                'owner_name'        => $ownerName,
                'points'            => $request->points,
                'lot_ids'           => $lotIds,
                'superficie_totale' => $superfTotale,
                'type'              => $request->type,
                'date_prevue'       => $request->date_prevue,
                'date_confirmee'    => $request->date_confirmee,
                'date_morcellement' => $request->date_morcellement,
            ]);

            // ✅ CRÉER AUTOMATIQUEMENT LE DOSSIER TECHNIQUE si client existant
           if ($clientId) {
    DossierTechnique::firstOrCreate(
        ['zone_groupe_id' => $zone->id],
        ['lot_id' => null, 'progression' => 0, 'statut' => 'none']
    );
}

            return response()->json(['success' => true, 'zone' => $zone]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $zone = ZoneGroupe::findOrFail($id);

            $clientId = $request->client_id ?? $zone->client_id;

            // Owner name — figé à la première affectation
            if (!$zone->client_id && $request->client_id) {
                $client           = Client::find($request->client_id);
                $zone->client_id  = $request->client_id;
                $zone->owner_name = $client?->name;
            } elseif (!$zone->client_id && $request->filled('nom')) {
                $zone->owner_name = $request->nom;
            }

            if ($request->filled('nom'))              $zone->nom              = $request->nom;
            if ($request->filled('dossier_client_id')) $zone->dossier_client_id = $request->dossier_client_id;

            $lotIds               = $request->lot_ids ?? $zone->lot_ids ?? [];
            $zone->lot_ids        = $lotIds;
            $zone->superficie_totale = Lot::whereIn('id', $lotIds)->sum('superficie');

            $zone->type = $request->type;

            if ($request->type === 'implantation_prevue')
                $zone->date_prevue = $request->date_prevue ?: null;
            elseif (in_array($request->type, ['deja_implante', 'dossier_technique']))
                $zone->date_confirmee = $request->date_confirmee ?: null;
            elseif ($request->type === 'morcellement')
                $zone->date_morcellement = $request->date_morcellement ?: null;

            $zone->save();

            // ✅ CRÉER AUTOMATIQUEMENT LE DOSSIER TECHNIQUE si client vient d'être affecté
            if ($zone->client_id) {
    DossierTechnique::firstOrCreate(
        ['zone_groupe_id' => $zone->id],
        ['lot_id' => null, 'progression' => 0, 'statut' => 'none']
    );
}

            return response()->json(['success' => true, 'zone' => $zone]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $zone = ZoneGroupe::findOrFail($id);
        // Supprimer aussi le dossier technique lié
        DossierTechnique::where('zone_groupe_id', $id)->delete();
        $zone->delete();
        return response()->json(['success' => true]);
    }

    public function panel($id)
    {
        $zone = ZoneGroupe::with([
            'client.dossiers.paiements',
            'dossierClient.paiements',
            'client.dossiers.commercial',
            'client.dossiers.facilitateur',
            'client.dossiers.grandSite',
            'dossierTechnique',
        ])->findOrFail($id);

        $dossiers = $zone->client?->dossiers->map(function($d) {
            $totalPaye = $d->paiements->sum('montant');
            $reste     = max(0, ($d->prix_superficie ?? 0) - $totalPaye);
            return [
                'id'          => $d->id,
                'nom'         => $d->nom_dossier,
                'commercial'  => $d->commercial?->name,
                'facilitateur'=> $d->facilitateur?->nom,
                'grand_site'  => $d->grandSite?->nom,
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
        }) ?? collect();

        return response()->json([
            'id'               => $zone->id,
            'nom'              => $zone->nom,
            'owner_name'       => $zone->owner_name,
            'type'             => $zone->type,
            'superficie_totale'=> $zone->superficie_totale,
            'client_id'        => $zone->client_id,
            'phone'            => $zone->client?->phone,
            'dossiers'         => $dossiers,
            // ✅ Lien vers le dossier technique
            'dossier_tech_url' => $zone->dossierTechnique ? "/admin/dossier-zone/{$zone->id}" : null,
            'dossier_tech_prog'=> $zone->dossierTechnique?->progression ?? 0,
        ]);
    }
}