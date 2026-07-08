<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\DossierClient;
use App\Models\PaiementDossier;
use App\Models\Commercial;
use App\Models\Conducteur;
use App\Models\Facilitateur;
use App\Models\AgentCommercial;
use App\Models\GrandSite;


class SuiviClientController extends Controller
{

public function create(Request $request)
    {
        $options  = $this->options();
        $clientId = $request->client_id;
        $clientPre= $clientId ? Client::find($clientId) : null;
        return view('admin.suivi_client.create', compact('options','clientPre'));
    }
    
    private function options(): array
    {
        return [
            'commerciaux'  => Commercial::orderBy('name')->get(),
            'conducteurs'  => Conducteur::orderBy('nom')->get(),
            'facilitateurs'=> Facilitateur::orderBy('nom')->get(),
            'agents'       => AgentCommercial::orderBy('nom')->get(),
            'grandsites'   => GrandSite::orderBy('nom')->get(),
            'directions'   => [
                'baffoussam'         => 'Baffoussam',
                'bagante'            => 'Bagante',
                'dschang'            => 'Dschang',
                'direction_generale' => 'Direction Générale',
            ],
        ];
    }

  public function index(Request $request)
{
    $query = Client::with([
        'dossiers.grandSite',
        'dossiers.paiementsTechniques',
        'dossiers.paiementsMorcellements'
    ])->orderByDesc('created_at');

    // Recherche
    if ($request->filled('q')) {
        $q = trim($request->q);
        $query->where(function($qry) use ($q) {
            $qry->where('name', 'LIKE', "%{$q}%")
                ->orWhere('phone', 'LIKE', "%{$q}%")
                ->orWhereHas('dossiers', function($dq) use ($q) {
                    $dq->where('nom_dossier', 'LIKE', "%{$q}%");
                });
        });
    }

    if ($request->filled('du'))
        $query->whereDate('created_at', '>=', $request->du);

    if ($request->filled('au'))
        $query->whereDate('created_at', '<=', $request->au);

    if ($request->filled('grand_site_id')) {
        $query->whereHas('dossiers', fn($q) => 
            $q->where('grand_site_id', $request->grand_site_id));
    }

    // ✅ Sans pagination : tout afficher
    $clients = $query->get();

    $grandSites = \App\Models\GrandSite::orderBy('nom')->get();

    return view('admin.suivi_client.index', compact('clients', 'grandSites'));
}

    public function store(Request $request)
    {
        $request->validate([
            'name'               => 'required|string|max:255',
            'phone'              => 'required|string',
            'nom_dossier'        => 'required|string|max:255',
            'commercial_id'      => 'nullable|exists:commerciaux,id',
            'conducteur_id'      => 'nullable|exists:conducteurs,id',
            'facilitateur_id'    => 'nullable|exists:facilitateurs,id',
            'agent_commercial_id'=> 'nullable|exists:agents_commerciaux,id',
            'grand_site_id'      => 'nullable|exists:grand_sites,id',
            'direction'          => 'nullable|string',
            'superficie_voulue'  => 'nullable|numeric',
            'prix_superficie'    => 'nullable|numeric',
        ]);

        $client = Client::firstOrCreate(
            ['phone' => $request->phone],
            ['name'  => $request->name]
        );

        $agentId = $request->agent_commercial_id;
        if (!$agentId && $request->agent_nom) {
            $a = AgentCommercial::create(['nom' => $request->agent_nom, 'numero' => $request->agent_numero]);
            $agentId = $a->id;
        }

        $conducteurId = $request->conducteur_id;
        if (!$conducteurId && $request->conducteur_nom) {
            $c = Conducteur::create(['nom' => $request->conducteur_nom, 'numero' => $request->conducteur_numero]);
            $conducteurId = $c->id;
        }

        $facilitateurId = $request->facilitateur_id;
        if (!$facilitateurId && $request->facilitateur_nom) {
            $f = Facilitateur::create(['nom' => $request->facilitateur_nom, 'numero' => $request->facilitateur_numero]);
            $facilitateurId = $f->id;
        }

        DossierClient::create([
            'client_id'          => $client->id,
            'nom_dossier'        => $request->nom_dossier,
            'commercial_id'      => $request->commercial_id,
            'conducteur_id'      => $conducteurId,
            'facilitateur_id'    => $facilitateurId,
            'agent_commercial_id'=> $agentId,
            'grand_site_id'      => $request->grand_site_id,
            'direction'          => $request->direction,
            'superficie_voulue'  => $request->superficie_voulue,
            'prix_superficie'    => $request->prix_superficie,
        ]);

        return redirect()->route('suivi-client.show', $client->id)
                         ->with('success', 'Client et dossier créés');
    }

    public function show($id)
    {
        $client = Client::with([
            'dossiers.commercial',
            'dossiers.conducteur',
            'dossiers.facilitateur',
            'dossiers.agentCommercial',
            'dossiers.grandSite',
            'dossiers.paiements',
            'dossiers.paiementsTechniques',
            'dossiers.paiementsMorcellements',
            'lots.tf.site.grandSite',
            'lots.dossier',
        ])->findOrFail($id);

        return view('admin.suivi_client.show', compact('client'));
    }

    public function edit(Request $request, $id)
    {
        $client    = Client::with('dossiers')->findOrFail($id);
        $options   = $this->options();
        $clientPre = null;
        $dossierId = $request->dossier_id ?? $client->dossiers->first()?->id;
        $dossier   = $client->dossiers->firstWhere('id', $dossierId)
                     ?? $client->dossiers->first();

        return view('admin.suivi_client.edit', compact('client','options','clientPre','dossier'));
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        // ✅ Mettre à jour le nom ET le téléphone
        $updateData = [];
        if ($request->filled('phone')) $updateData['phone'] = $request->phone;
        if ($request->filled('name'))  $updateData['name']  = $request->name;
        if (!empty($updateData)) $client->update($updateData);

        $agentId = $request->agent_commercial_id;
        if (!$agentId && $request->agent_nom) {
            $a = AgentCommercial::create(['nom' => $request->agent_nom, 'numero' => $request->agent_numero]);
            $agentId = $a->id;
        }

        $conducteurId = $request->conducteur_id;
        if (!$conducteurId && $request->conducteur_nom) {
            $c = Conducteur::create(['nom' => $request->conducteur_nom, 'numero' => $request->conducteur_numero]);
            $conducteurId = $c->id;
        }

        $facilitateurId = $request->facilitateur_id;
        if (!$facilitateurId && $request->facilitateur_nom) {
            $f = Facilitateur::create(['nom' => $request->facilitateur_nom, 'numero' => $request->facilitateur_numero]);
            $facilitateurId = $f->id;
        }

        $dossierId = $request->dossier_id ?? $client->dossiers->first()?->id;
        if ($dossierId) {
            $dossier = DossierClient::where('id', $dossierId)
                ->where('client_id', $client->id)
                ->firstOrFail();
            $dossier->update([
                'nom_dossier'        => $request->nom_dossier,
                'commercial_id'      => $request->commercial_id,
                'conducteur_id'      => $conducteurId,
                'facilitateur_id'    => $facilitateurId,
                'agent_commercial_id'=> $agentId,
                'grand_site_id'      => $request->grand_site_id,
                'direction'          => $request->direction,
                'superficie_voulue'  => $request->superficie_voulue,
                'prix_superficie'    => $request->prix_superficie,
            ]);
        }

        return redirect()->route('suivi-client.show', $client->id)
                         ->with('success', 'Mis à jour avec succès');
    }

    // ✅ Modifier uniquement le nom (appel AJAX)
    public function modifierNom(Request $request, $clientId)
    {
        $request->validate(['nom' => 'required|string|max:150']);
        $client = Client::findOrFail($clientId);
        $client->update(['name' => $request->nom]);
        return response()->json(['success' => true, 'nom' => $client->name]);
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->dossiers()->delete();
        $client->delete();
        return redirect()->route('suivi-client.index')->with('success', 'Client supprimé');
    }

    public function destroyDossier(DossierClient $dossier)
{
    $client = $dossier->client;

    // Supprimer les paiements liés
    $dossier->paiements()->delete();
    $dossier->paiementsTechniques()->delete();
    $dossier->paiementsMorcellements()->delete();

    // Supprimer le dossier
    $dossier->delete();

    return redirect()
        ->route('suivi-client.show', $client->id)
        ->with('success', 'Dossier supprimé avec succès.');
}

    public function dossiers($id)
    {
        $client = Client::with('dossiers')->findOrFail($id);
        return response()->json($client->dossiers);
    }
}