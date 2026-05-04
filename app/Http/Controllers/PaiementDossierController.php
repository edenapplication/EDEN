<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaiementDossier;
use App\Models\DossierClient;

class PaiementDossierController extends Controller
{
    public function store(Request $request, $dossierId)
    {
        $dossier = DossierClient::findOrFail($dossierId);

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

        return response()->json([
            'success'     => true,
            'paiement'    => $paiement,
            'total_paye'  => $nouveauTotal,
            'reste'       => $reste,
        ]);
    }

    public function index($dossierId)
    {
        $paiements = PaiementDossier::where('dossier_client_id', $dossierId)
            ->orderBy('date_paiement', 'desc')
            ->get();

        return response()->json([
            'paiements' => $paiements,
            'total'     => $paiements->sum('montant'),
        ]);
    }

    public function destroy($id)
    {
        PaiementDossier::findOrFail($id)->delete();
        return response()->json(['success' => true]);
    }
}