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
    private function options(): array
    {
        return [
            'commerciaux'   => Commercial::orderBy('name')->get(),
            'conducteurs'   => Conducteur::orderBy('nom')->get(),
            'facilitateurs' => Facilitateur::orderBy('nom')->get(),
            'agents'        => AgentCommercial::orderBy('nom')->get(),
            'grandsites'    => GrandSite::orderBy('nom')->get(),
            'directions'    => [
                'baffoussam'         => 'Baffoussam',
                'bagante'            => 'Bagante',
                'dschang'            => 'Dschang',
                'direction_generale' => 'Direction Générale',
            ],
        ];
    }

    public function index(Request $request)
{
    $query = Client::with(['dossiers.grandSite']);

    if ($request->filled('search')) {
        $s = $request->search;
        $query->where(fn($q) =>
            $q->where('name',  'LIKE', "%{$s}%")
              ->orWhere('phone', 'LIKE', "%{$s}%")
        );
    }

    if ($request->filled('grand_site_id')) {
        $query->whereHas('dossiers', fn($q) =>
            $q->where('grand_site_id', $request->grand_site_id)
        );
    }

    if ($request->filled('direction')) {
        $query->whereHas('dossiers', fn($q) =>
            $q->where('direction', $request->direction)
        );
    }

    $clients    = $query->latest()->paginate(15)->withQueryString();
    $grandsites = GrandSite::orderBy('nom')->get();

    // ✅ Si requête AJAX → retourner uniquement la liste partielle
    if ($request->ajax() || $request->get('ajax')) {
        return view('admin.suivi_client._liste', compact('clients'));
    }

    return view('admin.suivi_client.index', compact('clients', 'grandsites'));
}

    public function create(Request $request)
    {
        $options   = $this->options();
        $clientId  = $request->client_id;
        $clientPre = $clientId ? Client::find($clientId) : null;
        return view('admin.suivi_client.create', compact('options', 'clientPre'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'                => 'required|string|max:255',
            'phone'               => 'required|string',
            'nom_dossier'         => 'required|string|max:255',
            'commercial_id'       => 'nullable|exists:commerciaux,id',
            'conducteur_id'       => 'nullable|exists:conducteurs,id',
            'facilitateur_id'     => 'nullable|exists:facilitateurs,id',
            'agent_commercial_id' => 'nullable|exists:agents_commerciaux,id',
            'grand_site_id'       => 'nullable|exists:grand_sites,id',
            'direction'           => 'nullable|string',
            'superficie_voulue'   => 'nullable|numeric',
            'prix_superficie'     => 'nullable|numeric',
        ]);

        // CLIENT — nom figé à la création, jamais modifié
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
            'client_id'           => $client->id,
            'nom_dossier'         => $request->nom_dossier,
            'commercial_id'       => $request->commercial_id,
            'conducteur_id'       => $conducteurId,
            'facilitateur_id'     => $facilitateurId,
            'agent_commercial_id' => $agentId,
            'grand_site_id'       => $request->grand_site_id,
            'direction'           => $request->direction,
            'superficie_voulue'   => $request->superficie_voulue,
            'prix_superficie'     => $request->prix_superficie,
        ]);

       return redirect()->route('suivi-client.show', $client->id)->with('success', 'Client et dossier créés');
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

    // Charger le dossier sélectionné (via query string) ou le premier par défaut
    $dossierId = $request->dossier_id ?? $client->dossiers->first()?->id;
    $dossier   = $client->dossiers->firstWhere('id', $dossierId)
                 ?? $client->dossiers->first();

    return view('admin.suivi_client.edit', compact('client', 'options', 'clientPre', 'dossier'));
}

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        // ✅ Mettre à jour le téléphone du client
        if ($request->filled('phone')) {
            $client->update(['phone' => $request->phone]);
        }

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
                'nom_dossier'         => $request->nom_dossier,
                'commercial_id'       => $request->commercial_id,
                'conducteur_id'       => $conducteurId,
                'facilitateur_id'     => $facilitateurId,
                'agent_commercial_id' => $agentId,
                'grand_site_id'       => $request->grand_site_id,
                'direction'           => $request->direction,
                'superficie_voulue'   => $request->superficie_voulue,
                'prix_superficie'     => $request->prix_superficie,
            ]);
        }

        return redirect()->route('suivi-client.show', $client->id)->with('success', 'Mis à jour avec succès');
    }

    // ✅ Supprimer un client
    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->dossiers()->delete();
        $client->delete();
        return redirect()->route('suivi-client.index')->with('success', 'Client supprimé');
    }

    public function dossiers($id)
    {
        $client = Client::with('dossiers')->findOrFail($id);
        return response()->json($client->dossiers);
    }
}