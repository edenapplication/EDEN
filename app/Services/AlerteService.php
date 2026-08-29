<?php

namespace App\Services;

use App\Models\RH\Alerte;
use App\Models\RH\Employe;
use App\Models\RH\Contrat;
use App\Models\RH\Conge;
use App\Models\RH\Absence;
use App\Models\RH\CnpsDeclaration;
use App\Models\User;
use Carbon\Carbon;

class AlerteService
{
    /**
     * Vérifier toutes les conditions d'alerte
     */
    public function verifierTout(): array
    {
        $resultats = [];

        // 1. Contrats
        $resultats['contrats'] = $this->verifierContrats();

        // 2. Congés
        $resultats['conges'] = $this->verifierConges();

        // 3. Absences en attente
        $resultats['absences'] = $this->verifierAbsences();

        // 4. CNPS
        $resultats['cnps'] = $this->verifierCnps();

        // 5. Documents expirés
        $resultats['documents'] = $this->verifierDocuments();

        // 6. Période d'essai
        $resultats['periode_essai'] = $this->verifierPeriodeEssai();

        // 7. Visites médicales
        $resultats['visites'] = $this->verifierVisitesMedicales();

        return $resultats;
    }

    /**
     * Vérifier les alertes liées aux contrats
     */
    public function verifierContrats(): array
    {
        $alertes = [];
        $now = Carbon::now();

        // 1. CDD arrivant à échéance
        $cddEcheance = Contrat::where('statut', 'actif')
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [$now, $now->copy()->addDays(30)])
            ->with('employe')
            ->get();

        foreach ($cddEcheance as $contrat) {
            $jours = $now->diffInDays($contrat->date_fin);
            $priorite = $jours <= 7 ? 'critique' : ($jours <= 15 ? 'haute' : 'normale');

            $alerte = Alerte::createAlerte([
                'employe_id' => $contrat->employe_id,
                'type' => 'contrat',
                'titre' => "Fin de CDD dans {$jours} jours",
                'message' => "Le contrat CDD de {$contrat->employe?->nom} {$contrat->employe?->prenom} se termine le {$contrat->date_fin->format('d/m/Y')}.",
                'lien' => route('rh.contrats.show', $contrat->id),
                'data' => [
                    'contrat_id' => $contrat->id,
                    'date_fin' => $contrat->date_fin,
                    'jours_restants' => $jours,
                ],
                'priorite' => $priorite,
            ]);

            $alertes[] = $alerte;
        }

        // 2. Contrats renouvelables
        $renouvelables = Contrat::where('statut', 'termine')
            ->where('est_renouvelable', true)
            ->where('nb_renouvellements', '<', 'renouvellement_max')
            ->whereDate('date_fin', '>=', $now->subDays(30)->toDateString())
            ->with('employe')
            ->get();

        foreach ($renouvelables as $contrat) {
            $alerte = Alerte::createAlerte([
                'employe_id' => $contrat->employe_id,
                'type' => 'contrat',
                'titre' => "Contrat renouvelable",
                'message' => "Le contrat de {$contrat->employe?->nom} {$contrat->employe?->prenom} est renouvelable ({$contrat->nb_renouvellements}/{$contrat->renouvellement_max} renouvellements).",
                'lien' => route('rh.contrats.show', $contrat->id),
                'data' => ['contrat_id' => $contrat->id],
                'priorite' => 'normale',
            ]);

            $alertes[] = $alerte;
        }

        return $alertes;
    }

    /**
     * Vérifier les alertes liées aux congés
     */
    public function verifierConges(): array
    {
        $alertes = [];
        $now = Carbon::now();

        // Congés planifiés dans les 7 prochains jours
        $conges = Conge::where('statut', 'planifie')
            ->whereBetween('date_debut', [$now, $now->copy()->addDays(7)])
            ->with('employe')
            ->get();

        foreach ($conges as $conge) {
            $jours = $now->diffInDays($conge->date_debut);

            $alerte = Alerte::createAlerte([
                'employe_id' => $conge->employe_id,
                'type' => 'conge',
                'titre' => "Congé dans {$jours} jours",
                'message' => "{$conge->employe?->nom} {$conge->employe?->prenom} commence son congé le {$conge->date_debut->format('d/m/Y')} pour {$conge->nb_jours} jours.",
                'lien' => route('rh.conges.index'),
                'data' => ['conge_id' => $conge->id],
                'priorite' => $jours <= 3 ? 'haute' : 'normale',
            ]);

            $alertes[] = $alerte;
        }

        return $alertes;
    }

    /**
     * Vérifier les absences en attente
     */
    public function verifierAbsences(): array
    {
        $alertes = [];

        $absences = Absence::where('statut', 'en_attente')
            ->with('employe')
            ->get();

        foreach ($absences as $absence) {
            $alerte = Alerte::createAlerte([
                'employe_id' => $absence->employe_id,
                'type' => 'absence',
                'titre' => "Demande d'absence en attente",
                'message' => "Demande d'absence de {$absence->employe?->nom} {$absence->employe?->prenom} du {$absence->date_debut->format('d/m/Y')} au {$absence->date_fin->format('d/m/Y')}.",
                'lien' => route('rh.absences.index'),
                'data' => ['absence_id' => $absence->id],
                'priorite' => 'normale',
            ]);

            $alertes[] = $alerte;
        }

        return $alertes;
    }

    /**
     * Vérifier les alertes CNPS
     */
    public function verifierCnps(): array
    {
        $alertes = [];
        $moisPrecedent = Carbon::now()->subMonth()->format('Y-m');

        // Déclarations en retard
        $declarations = CnpsDeclaration::where('periode', $moisPrecedent)
            ->where('statut', 'a_declarer')
            ->get();

        foreach ($declarations as $declaration) {
            $alerte = Alerte::createAlerte([
                'type' => 'cnps',
                'titre' => "Déclaration CNPS en retard",
                'message' => "La déclaration CNPS {$declaration->reference} du mois {$declaration->mois_label} n'a pas encore été déclarée.",
                'lien' => route('rh.cnps.declarations.show', $declaration->id),
                'data' => ['declaration_id' => $declaration->id],
                'priorite' => 'haute',
            ]);

            $alertes[] = $alerte;
        }

        return $alertes;
    }

    /**
     * Vérifier les documents expirés
     */
    public function verifierDocuments(): array
    {
        $alertes = [];
        $now = Carbon::now();

        $employes = Employe::with('documents')
            ->where('actif', true)
            ->get();

        foreach ($employes as $employe) {
            foreach ($employe->documents as $doc) {
                // Vérifier la date d'expiration (si stockée dans les métadonnées)
                // Cette fonction peut être étendue selon les besoins
            }
        }

        return $alertes;
    }

    /**
     * Vérifier les périodes d'essai
     */
    public function verifierPeriodeEssai(): array
    {
        $alertes = [];
        $now = Carbon::now();

        $contrats = Contrat::where('statut', 'actif')
            ->whereNotNull('date_fin_periode_essai')
            ->whereBetween('date_fin_periode_essai', [$now, $now->copy()->addDays(15)])
            ->with('employe')
            ->get();

        foreach ($contrats as $contrat) {
            $jours = $now->diffInDays($contrat->date_fin_periode_essai);
            $priorite = $jours <= 3 ? 'critique' : ($jours <= 7 ? 'haute' : 'normale');

            $alerte = Alerte::createAlerte([
                'employe_id' => $contrat->employe_id,
                'type' => 'contrat',
                'titre' => "Fin de période d'essai dans {$jours} jours",
                'message' => "La période d'essai de {$contrat->employe?->nom} {$contrat->employe?->prenom} se termine le {$contrat->date_fin_periode_essai->format('d/m/Y')}.",
                'lien' => route('rh.contrats.show', $contrat->id),
                'data' => ['contrat_id' => $contrat->id],
                'priorite' => $priorite,
            ]);

            $alertes[] = $alerte;
        }

        return $alertes;
    }

    /**
     * Vérifier les visites médicales
     */
    public function verifierVisitesMedicales(): array
    {
        $alertes = [];
        // À implémenter lorsque le module Santé sera créé
        return $alertes;
    }

    /**
     * Nettoyer les anciennes alertes
     */
    public function nettoyerAnciennesAlertes(int $jours = 90): void
    {
        Alerte::where('statut', 'traite')
            ->where('created_at', '<', Carbon::now()->subDays($jours))
            ->delete();

        Alerte::where('statut', 'ignore')
            ->where('created_at', '<', Carbon::now()->subDays($jours))
            ->delete();
    }
}