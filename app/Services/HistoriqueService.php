<?php
// app/Services/HistoriqueService.php

namespace App\Services;

use App\Models\HistoriqueAffectation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HistoriqueService
{
    /**
     * ═══════════════════════════════════════════════════════════════
     * 📝 MÉTHODE PRINCIPALE — Enregistre une entrée dans l'historique
     * ═══════════════════════════════════════════════════════════════
     */
    public static function log(
        int $dossierId,
        string $typeAction,
        string $resume,
        ?string $cibleType = null,
        ?int $cibleId = null,
        ?array $avant = null,
        ?array $apres = null,
        $beneficiaireId = null
    ): void {
        try {
            HistoriqueAffectation::create([
                'dossier_client_id' => $dossierId,
                'beneficiaire_id'   => $beneficiaireId,
                'type_action'       => $typeAction,
                'user_id'           => Auth::id(),
                'cible_type'        => $cibleType,
                'cible_id'          => $cibleId,
                'resume'            => mb_substr($resume, 0, 500),
                'donnees_avant'     => $avant,
                'donnees_apres'     => $apres,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur HistoriqueService::log — ' . $e->getMessage());
        }
    }

    /**
     * ═══════════════════════════════════════════════════════════════
     * 🎨 HELPERS DE FORMATAGE
     * ═══════════════════════════════════════════════════════════════
     */

    /**
     * 📅 Formater une date en français : JJ/MM/AAAA à HHhMM
     *
     * Exemples :
     *   formatDate('2026-10-07 19:14:00') → "07/10/2026 à 19h14"
     *   formatDate('2026-10-07')          → "07/10/2026"
     *   formatDate(null)                  → ""
     */
    public static function formatDate($date, bool $avecHeure = true): string
    {
        if (empty($date)) return '';

        try {
            $carbon = $date instanceof \Carbon\Carbon
                ? $date
                : \Carbon\Carbon::parse($date);

            if ($avecHeure && $carbon->format('H:i') !== '00:00') {
                return $carbon->format('d/m/Y') . ' à ' . $carbon->format('H\hi');
            }

            return $carbon->format('d/m/Y');
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * 📍 Formater la localisation complète
     *
     * Exemple :
     *   formatLieu('EDEN BASTOS', 'Site A', 'TF-001', 'B')
     *   → "dans le Grand Site EDEN BASTOS, le Site A, le TF TF-001, le Bloc B"
     */
    public static function formatLieu(
        ?string $grandSite,
        ?string $site,
        ?string $tf,
        ?string $bloc
    ): string {
        $parties = [];

        $parties[] = "le Grand Site " . ($grandSite ?: '(non défini)');
        $parties[] = "le Site "       . ($site      ?: '(non défini)');
        $parties[] = "le TF "         . ($tf        ?: '(non défini)');
        $parties[] = "le Bloc "       . ($bloc      ?: '(non défini)');

        return "dans " . implode(', ', $parties);
    }

    /**
     * 📦 Formater une liste de lots
     *
     * Exemples :
     *   1 lot :  formatLots([['numero' => '8']])                             → "le Lot 8"
     *   2 lots : formatLots([['numero' => '1'], ['numero' => '2']])          → "les Lots 1 et 2"
     *   4 lots : formatLots([...4 lots...])                                  → "les Lots 1, 2, 3 et 4"
     */
    public static function formatLots(array $lots): string
    {
        if (empty($lots)) return '';

        $numeros = collect($lots)
            ->pluck('numero')
            ->filter()
            ->values()
            ->toArray();

        if (empty($numeros)) return '';

        $nb = count($numeros);

        if ($nb === 1) {
            return "le Lot " . $numeros[0];
        }

        if ($nb === 2) {
            return "les Lots " . $numeros[0] . " et " . $numeros[1];
        }

        $dernier = array_pop($numeros);
        return "les Lots " . implode(', ', $numeros) . " et " . $dernier;
    }

    /**
     * 🔢 Formater un nombre de lots
     *
     * Exemples :
     *   formatNbLots(1) → "1 lot"
     *   formatNbLots(4) → "4 lots"
     */
    public static function formatNbLots(int $nb): string
    {
        return $nb . ' ' . ($nb > 1 ? 'lots' : 'lot');
    }

    /**
     * 📐 Formater une superficie
     *
     * Exemple :
     *   formatSuperficie(500) → "500 m²"
     *   formatSuperficie(1500) → "1 500 m²"
     */
    public static function formatSuperficie($superficie): string
    {
        if (empty($superficie)) return '';

        return number_format($superficie, 0, ',', ' ') . ' m²';
    }

    /**
     * 👤 Formater un nom de bénéficiaire
     *
     * Exemples :
     *   formatBenef('mcj')                   → "du bénéficiaire « mcj »"
     *   formatBenef('mcj', 'au')             → "au bénéficiaire « mcj »"
     *   formatBenef('mcj', 'pour le')        → "pour le bénéficiaire « mcj »"
     */
    public static function formatBenef(?string $nom, string $prefixe = 'du'): string
    {
        if (empty($nom)) return '';

        return "{$prefixe} bénéficiaire « {$nom} »";
    }

    /**
     * 💬 Formater une note
     *
     * Exemple :
     *   formatNote('Première attribution') → "avec la note « Première attribution »"
     */
    public static function formatNote(?string $note): string
    {
        if (empty($note)) return '';

        return "avec la note « {$note} »";
    }

    /**
     * 🚫 Formater un motif de refus
     *
     * Exemple :
     *   formatMotif('Non conforme') → "motif : « Non conforme »"
     */
    public static function formatMotif(?string $motif): string
    {
        if (empty($motif)) return '';

        return "motif : « {$motif} »";
    }

    /**
     * 👷 Formater un géomètre
     *
     * Exemple :
     *   formatGeometre('Ing. Paul') → "le géomètre « Ing. Paul »"
     */
    public static function formatGeometre(?string $nom): string
    {
        if (empty($nom)) return '';

        return "le géomètre « {$nom} »";
    }

    /**
     * 📊 Formater l'étape bénéficiaire
     *
     * Exemple :
     *   formatEtape('deja_implante') → "Déjà implanté"
     */
    public static function formatEtape(?string $etape): string
    {
        if (empty($etape)) return '(non définie)';

        $etapesConfig = \App\Models\Beneficiaire::etapesConfig();

        return $etapesConfig[$etape]['label'] ?? $etape;
    }

    /**
     * 🔄 Formater une transition (avant → après)
     *
     * Exemples :
     *   formatTransition('2026-10-07 19:14', '2026-10-07 20:14', 'date')
     *     → "du 07/10/2026 à 19h14 au 07/10/2026 à 20h14"
     *   formatTransition('Ancien Nom', 'Nouveau Nom')
     *     → "de « Ancien Nom » à « Nouveau Nom »"
     */
    public static function formatTransition($avant, $apres, string $type = 'texte'): string
    {
        if ($type === 'date') {
            $avantTxt = self::formatDate($avant) ?: '(aucune)';
            $apresTxt = self::formatDate($apres) ?: '(aucune)';
            return "du {$avantTxt} au {$apresTxt}";
        }

        if ($type === 'nombre') {
            $avantTxt = ($avant !== null && $avant !== '') ? $avant : '(vide)';
            $apresTxt = ($apres !== null && $apres !== '') ? $apres : '(vide)';
            return "de {$avantTxt} à {$apresTxt}";
        }

        // Texte (par défaut)
        $avantTxt = ($avant !== null && $avant !== '') ? $avant : '(vide)';
        $apresTxt = ($apres !== null && $apres !== '') ? $apres : '(vide)';
        return "de « {$avantTxt} » à « {$apresTxt} »";
    }
}