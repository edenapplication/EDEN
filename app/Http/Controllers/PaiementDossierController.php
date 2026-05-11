<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaiementDossier;
use App\Models\DossierClient;
use App\Models\Visite;
use App\Models\Visiteur;

class PaiementDossierController extends Controller
{
    public function store(Request $request, $dossierId)
    {
        $dossier = DossierClient::with('client')->findOrFail($dossierId);

        $request->validate([
            'montant'       => 'required|numeric|min:1',
            'date_paiement' => 'required|date',
            'note'          => 'nullable|string',
        ]);

        $totalPaye = PaiementDossier::where('dossier_client_id', $dossierId)->sum('montant');
        $prix      = $dossier->prix_superficie ?? 0;
        $nouveauTotal = $totalPaye + $request->montant;
        $reste     = max(0, $prix - $nouveauTotal);

        if ($prix > 0 && $nouveauTotal > $prix) {
            return response()->json([
                'success' => false,
                'message' => 'Montant trop élevé. Reste à payer : ' . number_format($prix - $totalPaye, 0, ',', ' ') . ' FCFA',
            ], 422);
        }

        $paiement = PaiementDossier::create([
            'dossier_client_id' => $dossierId,
            'client_id'         => $dossier->client_id,
            'montant'           => $request->montant,
            'reste'             => $reste,
            'date_paiement'     => $request->date_paiement,
            'note'              => $request->note,
        ]);

        // ✅ Enregistrer automatiquement dans le registre des visites
        $this->enregistrerVisite($dossier, $paiement, $request->date_paiement);

        return response()->json([
            'success'    => true,
            'paiement'   => $paiement,
            'total_paye' => $nouveauTotal,
            'reste'      => $reste,
        ]);
    }

    // =============================================
    // VISITE AUTOMATIQUE AU PAIEMENT
    // =============================================
    private function enregistrerVisite(DossierClient $dossier, PaiementDossier $paiement, string $date)
    {
        $client = $dossier->client;
        if (!$client) return;

        // Trouver ou créer le visiteur correspondant au client
        $visiteur = Visiteur::firstOrCreate(
            ['nom' => $client->name, 'numero' => $client->phone ?? ''],
            ['type' => 'client']
        );

        // Ne pas créer de doublon si déjà une visite ce jour pour ce visiteur
        $dejaVenu = Visite::where('visiteur_id', $visiteur->id)
            ->where('date_visite', $date)
            ->exists();

        if (!$dejaVenu) {
            Visite::create([
                'visiteur_id'      => $visiteur->id,
                'client_id'        => $client->id,
                'dossier_client_id'=> $dossier->id,
                'grand_site_id'    => $dossier->grand_site_id,
                'date_visite'      => $date,
                'heure_arrivee'    => null,
                'heure_depart'     => null,
                'type_personne'    => 'client',
                'note'             => 'Paiement dossier : ' . ($dossier->nom_dossier ?? 'Sans nom'),
                'paiement_lie'     => true,
                'paiement_id'      => $paiement->id,
            ]);
        }
    }

    public function index($dossierId)
    {
        $paiements = PaiementDossier::where('dossier_client_id', $dossierId)
            ->orderBy('date_paiement', 'desc')
            ->get();
        return response()->json(['paiements' => $paiements, 'total' => $paiements->sum('montant')]);
    }

    public function destroy($id)
    {
        PaiementDossier::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}