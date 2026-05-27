<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

            $clientId  = $request->client_id ?: null;
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
                'dossier_client_id' => $request->dossier_client_id ?: null,
                'nom'               => $request->nom,
                'owner_name'        => $ownerName,
                'points'            => $request->points,
                'lot_ids'           => $lotIds,
                'superficie_totale' => $superfTotale,
                'type'              => $request->type ?: null,
                'date_prevue'       => $request->date_prevue ?: null,
                'date_confirmee'    => $request->date_confirmee ?: null,
                'date_morcellement' => $request->date_morcellement ?: null,
            ]);

            // ✅ Créer dossier technique via DB::table() pour éviter NOT NULL sur lot_id dans SQLite
            if ($clientId) {
                $existant = DB::table('dossiers_techniques')
                    ->where('zone_groupe_id', $zone->id)
                    ->first();

                if (!$existant) {
                    DB::table('dossiers_techniques')->insert([
                        'zone_groupe_id' => $zone->id,
                        'lot_id'         => null,   // ✅ null explicite via DB directement
                        'progression'    => 0,
                        'statut'         => 'none',
                        'created_at'     => now()->toDateTimeString(),
                        'updated_at'     => now()->toDateTimeString(),
                    ]);
                }
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

            // Owner name — figé à la première affectation
            if (!$zone->client_id && $request->client_id) {
                $client           = Client::find($request->client_id);
                $zone->client_id  = $request->client_id;
                $zone->owner_name = $client?->name;
            } elseif (!$zone->client_id && $request->filled('nom')) {
                $zone->owner_name = $request->nom;
            }

            if ($request->filled('nom'))               $zone->nom               = $request->nom;
            if ($request->filled('dossier_client_id')) $zone->dossier_client_id = $request->dossier_client_id;

            $lotIds                  = $request->lot_ids ?? $zone->lot_ids ?? [];
            $zone->lot_ids           = $lotIds;
            $zone->superficie_totale = !empty($lotIds) ? Lot::whereIn('id', $lotIds)->sum('superficie') : 0;
            $zone->type              = $request->type ?: null;

            if ($request->type === 'implantation_prevue')
                $zone->date_prevue = $request->date_prevue ?: null;
            elseif (in_array($request->type, ['deja_implante', 'dossier_technique']))
                $zone->date_confirmee = $request->date_confirmee ?: null;
            elseif ($request->type === 'morcellement')
                $zone->date_morcellement = $request->date_morcellement ?: null;

            $zone->save();

            // ✅ Créer dossier technique via DB::table() si client présent
            if ($zone->client_id) {
                $existant = DB::table('dossiers_techniques')
                    ->where('zone_groupe_id', $zone->id)
                    ->first();

                if (!$existant) {
                    DB::table('dossiers_techniques')->insert([
                        'zone_groupe_id' => $zone->id,
                        'lot_id'         => null,
                        'progression'    => 0,
                        'statut'         => 'none',
                        'created_at'     => now()->toDateTimeString(),
                        'updated_at'     => now()->toDateTimeString(),
                    ]);
                }
            }

            return response()->json(['success' => true, 'zone' => $zone]);

        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $zone = ZoneGroupe::findOrFail($id);
            DB::table('dossiers_techniques')->where('zone_groupe_id', $id)->delete();
            $zone->delete();
            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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
            'id'                => $zone->id,
            'nom'               => $zone->nom,
            'owner_name'        => $zone->owner_name,
            'type'              => $zone->type,
            'superficie_totale' => $zone->superficie_totale,
            'client_id'         => $zone->client_id,
            'phone'             => $zone->client?->phone,
            'dossiers'          => $dossiers,
            'dossier_tech_url'  => $zone->dossierTechnique ? "/admin/dossier-zone/{$zone->id}" : null,
            'dossier_tech_prog' => $zone->dossierTechnique?->progression ?? 0,
        ]);
    }
}