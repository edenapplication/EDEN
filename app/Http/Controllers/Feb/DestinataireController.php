<?php
// app/Http/Controllers/Feb/DestinataireController.php

namespace App\Http\Controllers\Feb;

use App\Http\Controllers\Controller;
use App\Models\Feb\Destinataire;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DestinataireController extends Controller
{
    /**
     * Liste tous les destinataires (pour le select)
     */
    public function index()
    {
        $destinataires = Destinataire::orderBy('nom')->get();
        return response()->json($destinataires);
    }

    /**
     * Recherche de destinataires (autocomplete)
     */
    public function search(Request $request)
    {
        $term = $request->get('q', '');
        $destinataires = Destinataire::recherche($term)
            ->orderBy('nom')
            ->limit(10)
            ->get();
        
        return response()->json($destinataires);
    }

    /**
     * Créer un nouveau destinataire
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255|unique:feb_destinataires,nom'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }

        $destinataire = Destinataire::create([
            'nom' => trim($request->nom)
        ]);

        return response()->json([
            'message' => 'Destinataire créé avec succès',
            'destinataire' => $destinataire
        ], 201);
    }

    /**
     * Supprimer un destinataire
     * (optionnel - si vous voulez permettre la suppression)
     */
    public function destroy($id)
    {
        $destinataire = Destinataire::findOrFail($id);
        
        // Vérifier si le destinataire est utilisé
        if ($destinataire->fiches()->count() > 0) {
            return response()->json([
                'message' => 'Ce destinataire est utilisé dans des fiches et ne peut pas être supprimé.'
            ], 422);
        }
        
        $destinataire->delete();
        
        return response()->json([
            'message' => 'Destinataire supprimé avec succès'
        ]);
    }
}