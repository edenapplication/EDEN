<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaiementMorcellement;

class PaiementMorcellementController extends Controller
{
    public function store(Request $request, $dossierId)
    {
        try {
            $request->validate([
                'montant'       => 'required|numeric|min:1',
                'date_paiement' => 'required|date',
                'note'          => 'nullable|string|max:255',
            ]);

            PaiementMorcellement::create([
                'dossier_client_id' => $dossierId,
                'montant'           => $request->montant,
                'date_paiement'     => $request->date_paiement,
                'note'              => $request->note,
            ]);

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

public function destroy($id)
{
    $paiement = PaiementMorcellement::findOrFail($id);
    $paiement->delete();

    return back()->with('success', 'Paiement supprimé avec succès.');
}
}