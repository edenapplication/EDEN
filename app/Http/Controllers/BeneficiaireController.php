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
            'sexe'      => 'nullable|in:masculin,feminin', 
            'cni'                  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
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

        // ✅ Récupérer les lots AVEC LES RELATIONS (grandSite, site, tf, bloc)
        $lotIds = $request->lot_ids ?? [];
        $lots = !empty($lotIds)
            ? LotAffectation::with(['grandSite', 'site', 'tf', 'bloc'])->whereIn('id', $lotIds)->get()
            : collect();

        // ✅ On ne bloque PLUS sur la superficie — on autorise le dépassement
        $superficieTotale = 0;
        if ($lots->isNotEmpty()) {
            $lotsIndisponibles = $lots->where('disponible', false);
            if ($lotsIndisponibles->isNotEmpty()) {
                $noms = $lotsIndisponibles->pluck('numero')->implode(', ');
                return response()->json([
                    'success' => false,
                    'message' => "❌ Les lots suivants ne sont pas disponibles : {$noms}",
                ], 422);
            }

            $superficieTotale = $lots->sum('superficie');
        }

        // ✅ Upload CNI — DÉSORMAIS OPTIONNELLE
        $cniPath = null;
        if ($request->hasFile('cni') && $request->file('cni')->isValid()) {
            try {
                $cniPath = $request->file('cni')->store('beneficiaires/cni', 'public');
                Log::info('✅ CNI uploadée', ['path' => $cniPath]);
            } catch (\Exception $e) {
                Log::warning('⚠️ Échec upload CNI (ignoré)', ['error' => $e->getMessage()]);
                $cniPath = null;
            }
        }

        // ✅ Créer le bénéficiaire
        $beneficiaire = Beneficiaire::create([
            'dossier_client_id'    => $dossier->id,
            'client_id'            => $clientId,
            'nom'                  => $request->nom,
            'sexe'      => $request->sexe, 
            'telephone'            => $request->telephone,
            'cni_path'             => $cniPath,
            'lots_texte'           => $lots->pluck('numero')->implode(', ') ?: null,
            'superficie_attribuee' => $superficieTotale,
            'notes'                => $request->notes,
        ]);

        // ✅ Créer les affectations
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

            // ✅ ENRICHIR avec grand_site, site, tf
            $lotsAffectes[] = [
                'lot_id'     => $lot->id,
                'numero'     => $lot->numero,
                'bloc'       => $lot->bloc?->code,
                'grand_site' => $lot->grandSite?->nom,
                'site'       => $lot->site?->name,
                'tf'         => $lot->tf?->title,
                'superficie' => $lot->superficie,
            ];
        }

        // ✅ HISTORIQUE — NOUVEAU MESSAGE NATUREL
        $resume = $this->buildHistoriqueMessage(
            $beneficiaire,
            $lotsAffectes,
            $superficieTotale,
            $request->notes
        );

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

/**
 * ═══════════════════════════════════════════════════════════════
 * 🎯 Construire le message d'historique (nouveau format)
 * ═══════════════════════════════════════════════════════════════
 */
private function buildHistoriqueMessage(
    Beneficiaire $beneficiaire,
    array $lotsAffectes,
    float $superficieTotale,
    ?string $notes
): string {
    // Pas de lots
    if (empty($lotsAffectes)) {
        return "Ajout du bénéficiaire « {$beneficiaire->nom} » sans aucun lot affecté";
    }

    $premierLot = $lotsAffectes[0];

    $grandSite = $premierLot['grand_site'] ?? null;
    $site      = $premierLot['site']       ?? null;
    $tf        = $premierLot['tf']         ?? null;
    $bloc      = $premierLot['bloc']       ?? null;

    $nbLots    = count($lotsAffectes);
    $nbLotsTxt = HistoriqueService::formatNbLots($nbLots);
    $lieuTxt   = HistoriqueService::formatLieu($grandSite, $site, $tf, $bloc);
    $lotsTxt   = HistoriqueService::formatLots($lotsAffectes);
    $supTxt    = HistoriqueService::formatSuperficie($superficieTotale);
    $noteTxt   = HistoriqueService::formatNote($notes);

    $resume = "Ajout du bénéficiaire « {$beneficiaire->nom} » avec {$nbLotsTxt}, "
            . "{$lieuTxt}, {$lotsTxt}, pour une superficie "
            . ($nbLots > 1 ? 'totale ' : '')
            . "de {$supTxt}";

    if ($noteTxt) {
        $resume .= ", {$noteTxt}";
    }

    return $resume;
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
            'sexe'      => 'nullable|in:masculin,feminin',
            'cni'                  => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            'notes'                => 'nullable|string|max:1000',
        ]);

        $dossier = $beneficiaire->dossier;
        $avant   = $beneficiaire->toArray();

        $data = [
            'nom'        => $request->nom,
            'telephone'  => $request->telephone,
            'notes'      => $request->notes,
            'sexe'       => $request->sexe,
        ];

        $cniAjoutee = false;

        // CNI (optionnel)
        if ($request->hasFile('cni') && $request->file('cni')->isValid()) {
            if ($beneficiaire->cni_path) {
                Storage::disk('public')->delete($beneficiaire->cni_path);
            }
            $data['cni_path'] = $request->file('cni')->store('beneficiaires/cni', 'public');
            $cniAjoutee = true;
        }

        $beneficiaire->update($data);
        $beneficiaire->refresh();

        // ✅ NOUVEAU MESSAGE NATUREL
        $resume = $this->buildModificationBenefMessage(
            $avant,
            $beneficiaire->toArray(),
            $cniAjoutee
        );

        // Ne logger que s'il y a des changements
        if ($resume !== null) {
            HistoriqueService::log(
                $dossier->id,
                'modification_beneficiaire',
                $resume,
                'beneficiaire',
                $beneficiaire->id,
                $avant,
                $beneficiaire->toArray(),
                $beneficiaire->id
            );
        }

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

        // ✅ Compter les lots AVANT suppression
        $nbLotsLiberes = $beneficiaire->affectations->count();
        $superficieLiberee = $beneficiaire->affectations->sum(fn($a) => $a->lot?->superficie ?? 0);

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
            // ✅ NOUVEAU MESSAGE NATUREL
            $resume = $this->buildSuppressionBenefMessage(
                $nom,
                $nbLotsLiberes,
                $superficieLiberee
            );

            HistoriqueService::log(
                $dossier->id,
                'suppression_beneficiaire',
                $resume,
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

/**
 * ═══════════════════════════════════════════════════════════════
 * 🎯 Construire le message de modification d'un bénéficiaire
 * ═══════════════════════════════════════════════════════════════
 * 
 * @return string|null  null si aucun changement significatif
 */
private function buildModificationBenefMessage(
    array $avant,
    array $apres,
    bool $cniAjoutee
): ?string {
    $changements = [];
    $nomAffiche  = $apres['nom'] ?? $avant['nom'] ?? '—';

    // Changement de nom
    if (($avant['nom'] ?? null) !== ($apres['nom'] ?? null)) {
        $changements[] = "le nom est passé de « " . ($avant['nom'] ?? '(vide)') . " » à « " . ($apres['nom'] ?? '(vide)') . " »";
    }

    // Changement de téléphone
    if (($avant['telephone'] ?? null) !== ($apres['telephone'] ?? null)) {
        $changements[] = "le téléphone est passé de « " . ($avant['telephone'] ?? '(vide)') . " » à « " . ($apres['telephone'] ?? '(vide)') . " »";
    }

    // Changement de sexe
if (($avant['sexe'] ?? null) !== ($apres['sexe'] ?? null)) {
    $avantSexe  = $avant['sexe']  ?? '(vide)';
    $apresSexe  = $apres['sexe']  ?? '(vide)';
    $labelAvant = match($avantSexe) {
        'masculin' => 'Masculin',
        'feminin'  => 'Féminin',
        default    => '(vide)',
    };
    $labelApres = match($apresSexe) {
        'masculin' => 'Masculin',
        'feminin'  => 'Féminin',
        default    => '(vide)',
    };
    $changements[] = "le sexe est passé de « {$labelAvant} » à « {$labelApres} »";
}

    // Changement de notes
    if (($avant['notes'] ?? null) !== ($apres['notes'] ?? null)) {
        $changements[] = "les notes ont été modifiées";
    }

    // CNI ajoutée
    if ($cniAjoutee) {
        $changements[] = "la CNI a été ajoutée";
    }

    // Aucun changement
    if (empty($changements)) {
        return null;
    }

    $resume = "Modification du bénéficiaire « {$nomAffiche} » : " . implode(', ', $changements);

    return $resume;
}

/**
 * ═══════════════════════════════════════════════════════════════
 * 🎯 Construire le message de suppression d'un bénéficiaire
 * ═══════════════════════════════════════════════════════════════
 */
private function buildSuppressionBenefMessage(
    string $nom,
    int $nbLotsLiberes,
    float $superficieLiberee
): string {
    $resume = "Suppression définitive du bénéficiaire « {$nom} »";

    if ($nbLotsLiberes > 0) {
        $nbLotsTxt = HistoriqueService::formatNbLots($nbLotsLiberes);
        $supTxt    = HistoriqueService::formatSuperficie($superficieLiberee);

        $resume .= ", {$nbLotsTxt} libéré(s)";

        if ($supTxt) {
            $resume .= ", superficie libérée : {$supTxt}";
        }
    } else {
        $resume .= " (aucun lot affecté)";
    }

    return $resume;
}

/**
 * ═══════════════════════════════════════════════════════════════
 * 🎯 Construire le message de changement d'étape
 * ═══════════════════════════════════════════════════════════════
 */
private function buildEtapeMessage(
    string $beneficiaireNom,
    string $etape,
    ?string $date,
    ?string $ancienneEtape,
    ?string $nouvelleEtape
): string {
    $etapeLabel = HistoriqueService::formatEtape($etape);
    $dateTxt    = $date ? HistoriqueService::formatDate($date) : null;

    // Définition / mise à jour de l'étape
    if ($date && $nouvelleEtape === $etape) {
        if ($ancienneEtape === $etape) {
            // Mise à jour de la date seulement
            return "Mise à jour de la date pour l'étape « {$etapeLabel} » du bénéficiaire « {$beneficiaireNom} » : {$dateTxt}";
        }

        // Première définition ou transition vers une étape supérieure
        $ancienLabel = $ancienneEtape ? HistoriqueService::formatEtape($ancienneEtape) : null;

        if ($ancienLabel && $ancienLabel !== $etapeLabel) {
            return "Changement d'étape pour le bénéficiaire « {$beneficiaireNom} » : passage de « {$ancienLabel} » à « {$etapeLabel} », le {$dateTxt}";
        }

        return "Définition de l'étape « {$etapeLabel} » pour le bénéficiaire « {$beneficiaireNom} », le {$dateTxt}";
    }

    // Suppression de l'étape
    if ($ancienneEtape === $etape && $nouvelleEtape !== $etape) {
        $nouveauLabel = $nouvelleEtape ? HistoriqueService::formatEtape($nouvelleEtape) : null;

        if ($nouveauLabel) {
            return "Suppression de l'étape « {$etapeLabel} » pour le bénéficiaire « {$beneficiaireNom} » (retour à « {$nouveauLabel} »)";
        }

        return "Suppression de l'étape « {$etapeLabel} » pour le bénéficiaire « {$beneficiaireNom} »";
    }

    // Fallback
    return "Mise à jour de l'étape « {$etapeLabel} » pour le bénéficiaire « {$beneficiaireNom} »";
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

        // ✅ Capturer l'ancienne étape AVANT modification
        $ancienneEtape = $beneficiaire->etape_actuelle;
        $ancienneDate  = $beneficiaire->$champDate;

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

        // ✅ NOUVEAU : Historique du changement d'étape
        $dossier = $beneficiaire->dossier;
        if ($dossier && ($ancienneEtape !== $beneficiaire->etape_actuelle || $ancienneDate != $beneficiaire->$champDate)) {
            $resume = $this->buildEtapeMessage(
                $beneficiaire->nom,
                $etape,
                $active ? $date : null,
                $ancienneEtape,
                $beneficiaire->etape_actuelle
            );

            HistoriqueService::log(
                $dossier->id,
                'etape_beneficiaire',
                $resume,
                'beneficiaire',
                $beneficiaire->id,
                [
                    'etape_avant' => $ancienneEtape,
                    'date_avant'  => $ancienneDate?->format('Y-m-d'),
                ],
                [
                    'etape_apres' => $beneficiaire->etape_actuelle,
                    'date_apres'  => $beneficiaire->$champDate?->format('Y-m-d'),
                ],
                $beneficiaire->id
            );
        }

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
            'sexe'                 => $b->sexe,  
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

    public function exportPdf(Request $request)
{
    try {
        $ids = $request->input('beneficiaire_ids', []);
        if (empty($ids)) return response()->json(['error' => 'Aucun bénéficiaire sélectionné'], 400);

        $clientIds = Beneficiaire::whereIn('id', $ids)->pluck('client_id')->filter()->unique()->values()->toArray();

        $clients = \App\Models\Client::with([
            'dossiers.grandSite','dossiers.affectations.grandSite','dossiers.affectations.bloc',
            'dossiers.affectations.lot','dossiers.affectations.beneficiaire',
            'dossiers.paiementsTechniques','dossiers.paiementsMorcellements',
            'dossiers.paiements','dossiers.paiementsLogistiques',
            'dossiers.beneficiaires','dossiers.historiques.user',
        ])->whereIn('id', $clientIds)->get();

        $clients->each(function($client) use ($ids) {
            $client->dossiers->each(function($dossier) use ($ids) {
                $dossier->setRelation('beneficiaires', $dossier->beneficiaires->whereIn('id', $ids)->values());
                $dossier->setRelation('affectations', $dossier->affectations->whereIn('beneficiaire_id', $ids)->values());
            });
        });

        $data = [
            'clients'       => $clients,
            'date_export'   => now()->format('d/m/Y H:i'),
            'total_clients' => $clients->count(),
            'titre'         => 'Export bénéficiaires sélectionnés',
        ];

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.suivi_client.export_pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        return $pdf->download('beneficiaires_selectionnes_' . now()->format('Y-m-d') . '.pdf');

    } catch (\Exception $e) {
        Log::error('Erreur exportPdf benef: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

public function exportExcel(Request $request)
{
    try {
        $ids = $request->input('beneficiaire_ids', []);
        if (empty($ids)) return response()->json(['error' => 'Aucun bénéficiaire sélectionné'], 400);

        $filename = 'beneficiaires_selectionnes_' . date('Y-m-d_H-i') . '.xlsx';
        return \Maatwebsite\Excel\Facades\Excel::download(
            new \App\Exports\LotsExport(['beneficiaire_ids' => $ids]),
            $filename
        );
    } catch (\Exception $e) {
        Log::error('Erreur exportExcel benef: ' . $e->getMessage());
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

}