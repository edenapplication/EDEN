<?php
// app/Http/Controllers/BeneficiaireController.php

namespace App\Http\Controllers;

use App\Models\Beneficiaire;
use App\Models\DossierClient;
use App\Services\HistoriqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BeneficiaireController extends Controller
{
    // ════════════════════════════════════════════════════════════
    // STORE — Ajouter un bénéficiaire
    // ════════════════════════════════════════════════════════════
    public function store(Request $request, DossierClient $dossier)
    {
        try {
            $request->validate([
                'nom'                 => 'required|string|max:255',
                'telephone'           => 'nullable|string|max:50',
                'cni'                 => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'lots_texte'          => 'nullable|string|max:255',
                'superficie_attribuee'=> 'required|numeric|min:0.01',
                'notes'               => 'nullable|string|max:1000',
            ]);

            $superficieDemandee = (float) $request->superficie_attribuee;
            $superficieRestante = $dossier->superficie_restante;

            if ($superficieDemandee > $superficieRestante) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ La superficie attribuée dépasse la superficie disponible.\n"
                               . "Restante : " . number_format($superficieRestante, 0, ',', ' ') . " m².",
                ], 422);
            }

            $cniPath = $request->file('cni')->store('beneficiaires/cni', 'public');

            $beneficiaire = Beneficiaire::create([
                'dossier_client_id'   => $dossier->id,
                'nom'                 => $request->nom,
                'telephone'           => $request->telephone,
                'cni_path'            => $cniPath,
                'lots_texte'          => $request->lots_texte,
                'superficie_attribuee'=> $superficieDemandee,
                'notes'               => $request->notes,
            ]);

            // ✅ HISTORIQUE
            HistoriqueService::log(
                $dossier->id,
                'ajout_beneficiaire',
                "Ajout du bénéficiaire « {$beneficiaire->nom} » — "
                    . number_format($superficieDemandee, 0, ',', ' ') . " m²"
                    . ($beneficiaire->lots_texte ? " (Lots : {$beneficiaire->lots_texte})" : ''),
                'beneficiaire',
                $beneficiaire->id,
                null,
                $beneficiaire->toArray()
            );

            return response()->json([
                'success'      => true,
                'message'      => '✅ Bénéficiaire ajouté.',
                'beneficiaire' => $this->formatBeneficiaire($beneficiaire),
                'superficie_dossier'   => $dossier->superficie_dossier,
                'superficie_attribuee' => $dossier->superficie_attribuee,
                'superficie_restante'  => $dossier->superficie_restante,
                'pourcentage'          => $dossier->pourcentage_attribue,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur store beneficiaire: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ════════════════════════════════════════════════════════════
    // UPDATE — Modifier un bénéficiaire
    // ════════════════════════════════════════════════════════════
    public function update(Request $request, Beneficiaire $beneficiaire)
    {
        try {
            $request->validate([
                'nom'                 => 'required|string|max:255',
                'telephone'           => 'nullable|string|max:50',
                'cni'                 => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'lots_texte'          => 'nullable|string|max:255',
                'superficie_attribuee'=> 'required|numeric|min:0.01',
                'notes'               => 'nullable|string|max:1000',
            ]);

            $dossier = $beneficiaire->dossier;
            $superficieDemandee = (float) $request->superficie_attribuee;
            $superficieDispo    = $dossier->superficie_restante + $beneficiaire->superficie_attribuee;

            if ($superficieDemandee > $superficieDispo) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ Superficie insuffisante. Disponible : "
                               . number_format($superficieDispo, 0, ',', ' ') . " m².",
                ], 422);
            }

            // 📸 Snapshot AVANT
            $avant = $beneficiaire->toArray();

            $data = [
                'nom'                 => $request->nom,
                'telephone'           => $request->telephone,
                'lots_texte'          => $request->lots_texte,
                'superficie_attribuee'=> $superficieDemandee,
                'notes'               => $request->notes,
            ];

            if ($request->hasFile('cni')) {
                if ($beneficiaire->cni_path) {
                    Storage::disk('public')->delete($beneficiaire->cni_path);
                }
                $data['cni_path'] = $request->file('cni')->store('beneficiaires/cni', 'public');
            }

            $beneficiaire->update($data);
            $beneficiaire->refresh();

            // ✅ HISTORIQUE avec diff détaillé
            $diff = $this->construireResumeModification($avant, $beneficiaire->toArray());

            HistoriqueService::log(
                $dossier->id,
                'modification_beneficiaire',
                "Modification de « {$beneficiaire->nom} » — {$diff}",
                'beneficiaire',
                $beneficiaire->id,
                $avant,
                $beneficiaire->toArray()
            );

            return response()->json([
                'success'      => true,
                'message'      => '✅ Bénéficiaire mis à jour.',
                'beneficiaire' => $this->formatBeneficiaire($beneficiaire),
                'superficie_dossier'   => $dossier->superficie_dossier,
                'superficie_attribuee' => $dossier->superficie_attribuee,
                'superficie_restante'  => $dossier->superficie_restante,
                'pourcentage'          => $dossier->pourcentage_attribue,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur update beneficiaire: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ════════════════════════════════════════════════════════════
    // DESTROY — Supprimer un bénéficiaire
    // ════════════════════════════════════════════════════════════
    public function destroy(Beneficiaire $beneficiaire)
    {
        try {
            $dossier = $beneficiaire->dossier;
            $avant   = $beneficiaire->toArray();
            $nom     = $beneficiaire->nom;

            if ($beneficiaire->cni_path) {
                Storage::disk('public')->delete($beneficiaire->cni_path);
            }

            $beneficiaire->delete();

            // ✅ HISTORIQUE
            HistoriqueService::log(
                $dossier->id,
                'suppression_beneficiaire',
                "Suppression du bénéficiaire « {$nom} » — "
                    . number_format($avant['superficie_attribuee'], 0, ',', ' ') . " m²",
                'beneficiaire',
                $avant['id'],
                $avant,
                null
            );

            return response()->json([
                'success'              => true,
                'message'              => '🗑️ Bénéficiaire supprimé.',
                'superficie_dossier'   => $dossier->superficie_dossier,
                'superficie_attribuee' => $dossier->superficie_attribuee,
                'superficie_restante'  => $dossier->superficie_restante,
                'pourcentage'          => $dossier->pourcentage_attribue,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur destroy beneficiaire: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ════════════════════════════════════════════════════════════
    // HELPERS
    // ════════════════════════════════════════════════════════════
    private function formatBeneficiaire(Beneficiaire $b): array
    {
        return [
            'id'                   => $b->id,
            'nom'                  => $b->nom,
            'telephone'            => $b->telephone,
            'cni_url'              => $b->cni_url,
            'cni_path'             => $b->cni_path,
            'lots_texte'           => $b->lots_texte,
            'superficie_attribuee' => $b->superficie_attribuee,
            'superficie_formatee'  => $b->superficie_formatee,
            'notes'                => $b->notes,
            'created_at'           => $b->created_at?->format('d/m/Y H:i'),
        ];
    }

    private function construireResumeModification(array $avant, array $apres): string
    {
        $changements = [];

        $champs = [
            'nom'                  => 'Nom',
            'telephone'            => 'Téléphone',
            'lots_texte'           => 'Lots',
            'superficie_attribuee' => 'Superficie',
            'notes'                => 'Notes',
        ];

        foreach ($champs as $champ => $label) {
            $old = $avant[$champ] ?? null;
            $new = $apres[$champ] ?? null;

            if ($champ === 'superficie_attribuee') {
                if ((float)$old !== (float)$new) {
                    $changements[] = sprintf(
                        '%s : %s → %s m²',
                        $label,
                        number_format((float)$old, 0, ',', ' '),
                        number_format((float)$new, 0, ',', ' ')
                    );
                }
            } elseif ($old !== $new) {
                $changements[] = sprintf(
                    '%s : « %s » → « %s »',
                    $label,
                    $old ?? '—',
                    $new ?? '—'
                );
            }
        }

        // CNI changée ?
        if (($avant['cni_path'] ?? null) !== ($apres['cni_path'] ?? null)) {
            $changements[] = 'CNI remplacée';
        }

        return $changements ? implode(' ; ', $changements) : 'aucun changement détecté';
    }

    // ════════════════════════════════════════════════════════════════
// ✅ MAJ ÉTAPE D'UN BÉNÉFICIAIRE
// ════════════════════════════════════════════════════════════════
public function majEtape(Request $request, Beneficiaire $beneficiaire)
{
    try {
        $etape  = $request->input('etape');
        $date   = $request->input('date');
        $active = $request->input('active');

        if (!$etape || !in_array($etape, ['implantation_prevue', 'deja_implante', 'dossier_technique', 'morcellement'])) {
            return response()->json(['success' => false, 'message' => 'Étape invalide'], 400);
        }

        $etapesConfig = Beneficiaire::etapesConfig();
        $champDate    = $etapesConfig[$etape]['champ'];

        if ($active && !$date) {
            return response()->json(['success' => false, 'message' => 'Une date est requise'], 400);
        }

        if ($active && $date) {
            $beneficiaire->$champDate = $date;
        } else {
            $beneficiaire->$champDate = null;
        }

        $etapesOrdre = Beneficiaire::etapesOrdre();
        $ordreEtape  = $etapesOrdre[$etape];

        if ($active && $date) {
            $beneficiaire->etape_actuelle = $etape;
        } else {
            $nouvelleEtape = null;
            foreach ($etapesOrdre as $cle => $ordre) {
                if ($ordre < $ordreEtape) {
                    $champ = $etapesConfig[$cle]['champ'];
                    if ($beneficiaire->$champ) {
                        $nouvelleEtape = $cle;
                    }
                }
            }
            $beneficiaire->etape_actuelle = $nouvelleEtape;
        }

        $beneficiaire->save();

        $dates = [];
        foreach ($etapesConfig as $cle => $cfg) {
            $champ = $cfg['champ'];
            $dates[$cle] = $beneficiaire->$champ ? $beneficiaire->$champ->format('d/m/Y') : null;
        }

        return response()->json([
            'success'        => true,
            'etape_actuelle' => $beneficiaire->etape_actuelle,
            'dates'          => $dates,
        ]);

    } catch (\Exception $e) {
        Log::error('Erreur majEtape beneficiaire: ' . $e->getMessage());
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

}