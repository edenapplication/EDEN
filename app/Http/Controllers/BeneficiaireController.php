<?php
// app/Http/Controllers/BeneficiaireController.php

namespace App\Http\Controllers;

use App\Models\Beneficiaire;
use App\Models\DossierClient;
use App\Models\LotAffectation;
use App\Models\Affectation;
use App\Services\HistoriqueService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class BeneficiaireController extends Controller
{
    // ════════════════════════════════════════════════════════════
    // STORE — Ajouter un bénéficiaire (avec lots pré-sélectionnés)
    // ════════════════════════════════════════════════════════════
    public function store(Request $request, DossierClient $dossier)
{
    try {
        $request->validate([
            'nom'                  => 'required|string|max:255',
            'telephone'            => 'nullable|string|max:50',
            'cni'                  => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'notes'                => 'nullable|string|max:1000',
            'client_id'            => 'nullable|exists:clients,id',
            'lot_ids'              => 'nullable|array',
            'lot_ids.*'            => 'nullable|exists:lots_affectation,id',
            'date_affectation'     => 'nullable|date',
        ]);

        // ✅ Vérification client
        $clientId = $request->client_id;
        if ($clientId && $clientId != $dossier->client_id) {
            return response()->json([
                'success' => false,
                'message' => '❌ Le client sélectionné n\'est pas le propriétaire de ce dossier.',
            ], 422);
        }

        // ✅ Récupérer les lots (peut être vide)
        $lotIds = $request->lot_ids ?? [];
        $lots = !empty($lotIds)
            ? LotAffectation::with('bloc')->whereIn('id', $lotIds)->get()
            : collect();

        // ✅ MODIFIÉ : On ne bloque PLUS si vide, on continue
        $superficieTotale = 0;
        if ($lots->isNotEmpty()) {
            // Vérifier que tous les lots sont disponibles
            $lotsIndisponibles = $lots->where('disponible', false);
            if ($lotsIndisponibles->isNotEmpty()) {
                $noms = $lotsIndisponibles->pluck('numero')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => "❌ Les lots suivants ne sont pas disponibles : {$noms}",
                ], 422);
            }

            $superficieTotale = $lots->sum('superficie');

            // Vérifier superficie restante du dossier
            $superficieRestante = $dossier->superficie_restante;
            if ($superficieTotale > $superficieRestante) {
                return response()->json([
                    'success' => false,
                    'message' => "❌ La superficie totale des lots ({$superficieTotale} m²) "
                               . "dépasse la superficie disponible du dossier ({$superficieRestante} m²).",
                ], 422);
            }
        }

        // ✅ Upload CNI
        $cniPath = null;
        if ($request->hasFile('cni') && $request->file('cni')->isValid()) {
            $cniPath = $request->file('cni')->store('beneficiaires/cni', 'public');
            Log::info('✅ CNI uploadée', ['path' => $cniPath]);
        }

        if (!$cniPath) {
            return response()->json([
                'success' => false,
                'message' => '❌ Erreur lors de l\'upload de la CNI.',
            ], 500);
        }

        // ✅ Créer le bénéficiaire (superficie peut être 0)
        $beneficiaire = Beneficiaire::create([
            'dossier_client_id'    => $dossier->id,
            'client_id'            => $clientId,
            'nom'                  => $request->nom,
            'telephone'            => $request->telephone,
            'cni_path'             => $cniPath,
            'lots_texte'           => $lots->pluck('numero')->implode(', ') ?: null,
            'superficie_attribuee' => $superficieTotale,
            'notes'                => $request->notes,
        ]);

        // ✅ Créer les affectations (seulement si des lots ont été sélectionnés)
        $dateAffectation = $request->date_affectation ?? now()->format('Y-m-d');
        $lotsAffectes = [];

        foreach ($lots as $lot) {
            Affectation::create([
                'grand_site_id'      => $lot->grand_site_id,
                'site_id'            => $lot->site_id,
                'tf_id'              => $lot->tf_id,
                'bloc_id'            => $lot->bloc_id,
                'lot_affectation_id' => $lot->id,
                'client_id'          => $dossier->client_id,
                'dossier_client_id'  => $dossier->id,
                'beneficiaire_id'    => $beneficiaire->id,
                'date_affectation'   => $dateAffectation,
                'statut'             => 'actif',
                'notes'              => $request->notes,
            ]);

            $lot->update(['disponible' => false]);

            $lotsAffectes[] = [
                'lot_id'     => $lot->id,
                'numero'     => $lot->numero,
                'bloc'       => $lot->bloc?->code,
                'superficie' => $lot->superficie,
            ];
        }

        // ✅ HISTORIQUE (message adapté selon qu'il y a des lots ou non)
        if (!empty($lotsAffectes)) {
            $resumeLots = collect($lotsAffectes)
                ->map(fn($l) => "Lot {$l['numero']}" . ($l['bloc'] ? " (Bloc {$l['bloc']})" : ''))
                ->implode(', ');

            $resume = "Ajout du bénéficiaire « {$beneficiaire->nom} » "
                    . "avec " . count($lotsAffectes) . " lot(s) — {$resumeLots} "
                    . "— Superficie totale : " . number_format($superficieTotale, 0, ',', ' ') . " m²";
        } else {
            $resume = "Ajout du bénéficiaire « {$beneficiaire->nom} » (sans lot affecté)";
        }

        HistoriqueService::log(
            $dossier->id,
            'ajout_beneficiaire',
            $resume,
            'beneficiaire',
            $beneficiaire->id,
            null,
            [
                'beneficiaire_nom'    => $beneficiaire->nom,
                'superficie_totale'   => $superficieTotale,
                'lots'                => $lotsAffectes,
                'date_affectation'    => $dateAffectation,
                'notes'               => $request->notes,
            ],
            $beneficiaire->id
        );

        $message = !empty($lotsAffectes)
            ? '✅ Bénéficiaire ajouté avec ' . count($lotsAffectes) . ' lot(s).'
            : '✅ Bénéficiaire ajouté (sans lot).';

        return response()->json([
            'success'              => true,
            'message'              => $message,
            'beneficiaire'         => $this->formatBeneficiaire($beneficiaire),
            'superficie_dossier'   => $dossier->superficie_dossier,
            'superficie_attribuee' => $dossier->superficie_attribuee,
            'superficie_restante'  => $dossier->superficie_restante,
            'pourcentage'          => $dossier->pourcentage_attribue,
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Erreur de validation',
            'errors'  => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        Log::error('Erreur store beneficiaire: ' . $e->getMessage(), [
            'trace' => $e->getTraceAsString(),
        ]);
        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
        ], 500);
    }
}

    // ════════════════════════════════════════════════════════════
    // UPDATE — Modifier un bénéficiaire
    // ════════════════════════════════════════════════════════════
    public function update(Request $request, Beneficiaire $beneficiaire)
    {
        try {
            $request->validate([
                'nom'                  => 'required|string|max:255',
                'telephone'            => 'nullable|string|max:50',
                'cni'                  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
                'notes'                => 'nullable|string|max:1000',
            ]);

            $dossier = $beneficiaire->dossier;
            $avant = $beneficiaire->toArray();

            $data = [
                'nom'        => $request->nom,
                'telephone'  => $request->telephone,
                'notes'      => $request->notes,
            ];

            // CNI (optionnel)
            if ($request->hasFile('cni') && $request->file('cni')->isValid()) {
                if ($beneficiaire->cni_path) {
                    Storage::disk('public')->delete($beneficiaire->cni_path);
                }
                $data['cni_path'] = $request->file('cni')->store('beneficiaires/cni', 'public');
            }

            $beneficiaire->update($data);
            $beneficiaire->refresh();

            // ✅ Historique
            $diff = $this->construireResumeModification($avant, $beneficiaire->toArray());

            HistoriqueService::log(
                $dossier->id,
                'modification_beneficiaire',
                "Modification de « {$beneficiaire->nom} » — {$diff}",
                'beneficiaire',
                $beneficiaire->id,
                $avant,
                $beneficiaire->toArray(),
                $beneficiaire->id
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

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur de validation',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Erreur update beneficiaire: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════════════
    // DESTROY
    // ════════════════════════════════════════════════════════════
    public function destroy(Beneficiaire $beneficiaire)
    {
        try {
            $dossier = $beneficiaire->dossier;
            $avant   = $beneficiaire->toArray();
            $nom     = $beneficiaire->nom;
            $benefId = $beneficiaire->id;

            // ✅ Libérer les lots affectés
            foreach ($beneficiaire->affectations as $aff) {
                $aff->lot?->update(['disponible' => true]);
                $aff->delete();
            }

            if ($beneficiaire->cni_path) {
                Storage::disk('public')->delete($beneficiaire->cni_path);
            }

            $beneficiaire->delete();

            if ($dossier) {
                HistoriqueService::log(
                    $dossier->id,
                    'suppression_beneficiaire',
                    "Suppression du bénéficiaire « {$nom} »",
                    'beneficiaire',
                    $benefId,
                    $avant,
                    null,
                    $benefId
                );
            }

            return response()->json([
                'success' => true,
                'message' => '🗑️ Bénéficiaire supprimé.',
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur destroy beneficiaire: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ════════════════════════════════════════════════════════════
    // ✅ WHATSAPP BÉNÉFICIAIRE
    // ════════════════════════════════════════════════════════════
    public function whatsappBeneficiaire(Beneficiaire $beneficiaire)
    {
        $beneficiaire->load(['dossier.client', 'dossier.grandSite', 'affectations.lot', 'affectations.bloc']);

        $dossier = $beneficiaire->dossier;
        $client  = $dossier?->client;

        $message  = "📢 *EDEN GROUP - Bénéficiaire*\n\n";
        $message .= "👤 *" . $beneficiaire->nom . "*\n";
        if ($beneficiaire->telephone) $message .= "📞 " . $beneficiaire->telephone . "\n";
        $message .= "📐 *Superficie :* " . number_format($beneficiaire->superficie_attribuee, 0, ',', ' ') . " m²\n";

        if ($dossier) {
            $message .= "\n📁 *Dossier :* " . $dossier->nom_dossier . "\n";
            $message .= "🏢 *Grand Site :* " . ($dossier->grandSite?->nom ?? '-') . "\n";
        }
        if ($client) $message .= "👤 *Client parent :* " . $client->name . "\n";

        if ($beneficiaire->affectations->count() > 0) {
            $message .= "\n📦 *Lots affectés :* " . $beneficiaire->affectations->count() . "\n";
            foreach ($beneficiaire->affectations as $aff) {
                $message .= "  • Lot " . ($aff->lot?->numero ?? '?')
                          . " (Bloc " . ($aff->bloc?->code ?? '-') . ")\n";
            }
        }

        $message .= "\n📅 " . now()->format('d/m/Y à H:i');
        $message .= "\n🔗 EDEN GROUP";

        $whatsappNumber = $beneficiaire->telephone
            ? preg_replace('/[^0-9]/', '', $beneficiaire->telephone)
            : '237653350503';
        if (strlen($whatsappNumber) === 9) $whatsappNumber = '237' . $whatsappNumber;

        return redirect("https://wa.me/{$whatsappNumber}?text=" . urlencode($message));
    }

    // ════════════════════════════════════════════════════════════
    // MAJ ÉTAPE
    // ════════════════════════════════════════════════════════════
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

            if ($active && $date) $beneficiaire->$champDate = $date;
            else $beneficiaire->$champDate = null;

            $etapesOrdre = Beneficiaire::etapesOrdre();
            $ordreEtape  = $etapesOrdre[$etape];

            if ($active && $date) {
                $beneficiaire->etape_actuelle = $etape;
            } else {
                $nouvelleEtape = null;
                foreach ($etapesOrdre as $cle => $ordre) {
                    if ($ordre < $ordreEtape) {
                        $champ = $etapesConfig[$cle]['champ'];
                        if ($beneficiaire->$champ) $nouvelleEtape = $cle;
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
            'client_id'            => $b->client_id ?? null,
            'created_at'           => $b->created_at?->format('d/m/Y H:i'),
        ];
    }

    private function construireResumeModification(array $avant, array $apres): string
    {
        $changements = [];
        $champs = [
            'nom'        => 'Nom',
            'telephone'  => 'Téléphone',
            'notes'      => 'Notes',
        ];

        foreach ($champs as $champ => $label) {
            $old = $avant[$champ] ?? null;
            $new = $apres[$champ] ?? null;
            if ($old !== $new) {
                $changements[] = sprintf('%s : « %s » → « %s »', $label, $old ?? '—', $new ?? '—');
            }
        }

        if (($avant['cni_path'] ?? null) !== ($apres['cni_path'] ?? null)) {
            $changements[] = 'CNI remplacée';
        }

        return $changements ? implode(' ; ', $changements) : 'aucun changement détecté';
    }
}