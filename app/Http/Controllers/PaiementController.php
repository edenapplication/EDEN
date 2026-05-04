<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Paiement;
use App\Models\Lot;

class PaiementController extends Controller
{
    public function store(Request $request, $lotId)
    {
        $lot = Lot::findOrFail($lotId);

        $request->validate([
            'montant' => 'required|numeric|min:1',
            'date_paiement' => 'required|date',
        ]);

        $totalPaye = Paiement::where('lot_id', $lotId)->sum('montant');

        $reste = ($lot->prix ?? 0) - ($totalPaye + $request->montant);
        if ($reste < 0) $reste = 0;

        if ($request->montant > $reste) {
        return response()->json([
            'success' => false,
            'message' => "Montant trop élevé. Reste à payer : " . $reste
        ], 422);
    }

        $paiement = Paiement::create([
            'lot_id' => $lotId,
            'client_id' => $lot->client_id,
            'montant' => $request->montant,
            'reste' => $reste,
            'date_paiement' => $request->date_paiement,
        ]);

        return response()->json([
            'success' => true,
            'paiement' => $paiement,
            'total_paye' => $totalPaye + $request->montant,
            'reste' => $reste
        ]);
    }

    public function index($lotId)
    {
        $paiements = Paiement::where('lot_id', $lotId)
            ->orderBy('created_at', 'desc')
            ->get();

        $total = $paiements->sum('montant');

        return response()->json([
            'paiements' => $paiements,
            'total' => $total
        ]);
    }

     public function history($id)
    {
        $paiements = Paiement::where('lot_id', $id)
            ->orderBy('date_paiement', 'desc')
            ->get();

        return response()->json($paiements);
    }

}