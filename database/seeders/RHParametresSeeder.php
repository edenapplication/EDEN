<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RH\TypeContrat;
use App\Models\RH\AgenceSite;
use App\Models\RH\NiveauCheleon;
use App\Models\RH\SourceCandidature;
use App\Models\RH\TypeAbsence;
use App\Models\RH\TypeSanction;
use App\Models\RH\TypeFormation;
use App\Models\RH\MotifDepart;

class RHParametresSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Types de contrat
        $typesContrat = [
            ['code' => 'CDI', 'nom' => 'Contrat à Durée Indéterminée', 'categorie' => 'CDI', 'est_renouvelable' => true],
            ['code' => 'CDD', 'nom' => 'Contrat à Durée Déterminée', 'categorie' => 'CDD', 'est_renouvelable' => true, 'duree_maximale_mois' => 24],
            ['code' => 'STAGE_ACA', 'nom' => 'Stage académique', 'categorie' => 'STAGE', 'est_renouvelable' => false, 'duree_maximale_mois' => 6],
            ['code' => 'STAGE_PRO', 'nom' => 'Stage professionnel', 'categorie' => 'STAGE', 'est_renouvelable' => false, 'duree_maximale_mois' => 6],
            ['code' => 'STAGE_REM', 'nom' => 'Stage rémunéré', 'categorie' => 'STAGE', 'est_renouvelable' => false, 'duree_maximale_mois' => 12],
            ['code' => 'PRESTA', 'nom' => 'Prestation de services', 'categorie' => 'PRESTATION', 'est_renouvelable' => true],
            ['code' => 'TEMPO', 'nom' => 'Contrat temporaire', 'categorie' => 'CDD', 'est_renouvelable' => false],
            ['code' => 'CONSULT', 'nom' => 'Consultant', 'categorie' => 'PRESTATION', 'est_renouvelable' => true],
            ['code' => 'AUTRE', 'nom' => 'Autre type de contrat', 'categorie' => 'AUTRE', 'est_renouvelable' => false],
        ];
        foreach ($typesContrat as $data) {
            TypeContrat::firstOrCreate(['code' => $data['code']], $data);
        }

        // 2. Agences / Sites
        $agences = [
            ['code' => 'YDE', 'nom' => 'Yaoundé - Siège', 'ville' => 'Yaoundé'],
            ['code' => 'DLA', 'nom' => 'Douala - Agence', 'ville' => 'Douala'],
            ['code' => 'BUE', 'nom' => 'Buéa - Agence', 'ville' => 'Buéa'],
            ['code' => 'GAR', 'nom' => 'Garoua - Agence', 'ville' => 'Garoua'],
        ];
        foreach ($agences as $data) {
            AgenceSite::firstOrCreate(['code' => $data['code']], $data);
        }

        // 3. Niveaux / Échelons
        $niveaux = [
            ['code' => '0', 'nom' => 'Niveau 0', 'categorie' => 'AGENT', 'ordre' => 0],
            ['code' => '1A', 'nom' => 'Niveau 1A', 'categorie' => 'AGENT', 'ordre' => 1],
            ['code' => '1B', 'nom' => 'Niveau 1B', 'categorie' => 'AGENT', 'ordre' => 2],
            ['code' => '2A', 'nom' => 'Niveau 2A', 'categorie' => 'MAITRISE', 'ordre' => 3],
            ['code' => '2B', 'nom' => 'Niveau 2B', 'categorie' => 'MAITRISE', 'ordre' => 4],
            ['code' => '2C', 'nom' => 'Niveau 2C', 'categorie' => 'MAITRISE', 'ordre' => 5],
            ['code' => '2D', 'nom' => 'Niveau 2D', 'categorie' => 'MAITRISE', 'ordre' => 6],
            ['code' => '3A', 'nom' => 'Niveau 3A', 'categorie' => 'CADRE', 'ordre' => 7],
            ['code' => '3B', 'nom' => 'Niveau 3B', 'categorie' => 'CADRE', 'ordre' => 8],
            ['code' => '3C', 'nom' => 'Niveau 3C', 'categorie' => 'CADRE', 'ordre' => 9],
            ['code' => '3D', 'nom' => 'Niveau 3D', 'categorie' => 'CADRE', 'ordre' => 10],
            ['code' => '4A', 'nom' => 'Niveau 4A', 'categorie' => 'CADRE_SUPERIEUR', 'ordre' => 11],
            ['code' => '4B', 'nom' => 'Niveau 4B', 'categorie' => 'CADRE_SUPERIEUR', 'ordre' => 12],
            ['code' => '4C', 'nom' => 'Niveau 4C', 'categorie' => 'CADRE_SUPERIEUR', 'ordre' => 13],
            ['code' => '5', 'nom' => 'Niveau 5', 'categorie' => 'CADRE_SUPERIEUR', 'ordre' => 14],
            ['code' => '6', 'nom' => 'Niveau 6', 'categorie' => 'CADRE_SUPERIEUR', 'ordre' => 15],
            ['code' => '7A', 'nom' => 'Niveau 7A', 'categorie' => 'CADRE_SUPERIEUR', 'ordre' => 16],
            ['code' => '7B', 'nom' => 'Niveau 7B', 'categorie' => 'CADRE_SUPERIEUR', 'ordre' => 17],
            ['code' => '8', 'nom' => 'Niveau 8', 'categorie' => 'CADRE_SUPERIEUR', 'ordre' => 18],
        ];
        foreach ($niveaux as $data) {
            NiveauCheleon::firstOrCreate(['code' => $data['code']], $data);
        }

        // 4. Sources de candidature
        $sources = [
            ['code' => 'ANNONCE', 'nom' => 'Annonce en ligne'],
            ['code' => 'COOPT', 'nom' => 'Cooptation'],
            ['code' => 'SITE', 'nom' => 'Site web entreprise'],
            ['code' => 'RESEAU', 'nom' => 'Réseaux sociaux'],
            ['code' => 'ECOLE', 'nom' => 'École / Université'],
            ['code' => 'AGENCE', 'nom' => 'Agence de recrutement'],
            ['code' => 'SPONT', 'nom' => 'Candidature spontanée'],
            ['code' => 'AUTRE', 'nom' => 'Autre source'],
        ];
        foreach ($sources as $data) {
            SourceCandidature::firstOrCreate(['code' => $data['code']], $data);
        }

        // 5. Types d'absence
        $typesAbsence = [
            ['code' => 'CONGE_ANNUEL', 'nom' => 'Congé annuel', 'est_remunere' => true, 'necessite_justificatif' => false, 'plafond_jours_annuel' => 15],
            ['code' => 'MALADIE', 'nom' => 'Congé maladie', 'est_remunere' => true, 'necessite_justificatif' => true],
            ['code' => 'PERMISSION', 'nom' => 'Permission', 'est_remunere' => true, 'necessite_justificatif' => false],
            ['code' => 'MATERNITE', 'nom' => 'Congé maternité', 'est_remunere' => true, 'necessite_justificatif' => true],
            ['code' => 'PATERNITE', 'nom' => 'Congé paternité', 'est_remunere' => true, 'necessite_justificatif' => true],
            ['code' => 'AUTORISEE', 'nom' => 'Absence autorisée', 'est_remunere' => true, 'necessite_justificatif' => false],
            ['code' => 'INJUSTIFIEE', 'nom' => 'Absence injustifiée', 'est_remunere' => false, 'necessite_justificatif' => false],
            ['code' => 'MISSION', 'nom' => 'Mission', 'est_remunere' => true, 'necessite_justificatif' => true],
        ];
        foreach ($typesAbsence as $data) {
            TypeAbsence::firstOrCreate(['code' => $data['code']], $data);
        }

        // 6. Types de sanction
        $typesSanction = [
            ['code' => 'OBS', 'nom' => 'Observation', 'gravite' => 'faible', 'impact_financier' => false, 'impact_carriere' => false],
            ['code' => 'AVERT', 'nom' => 'Avertissement', 'gravite' => 'faible', 'impact_financier' => false, 'impact_carriere' => true],
            ['code' => 'BLAME', 'nom' => 'Blâme', 'gravite' => 'moyenne', 'impact_financier' => false, 'impact_carriere' => true],
            ['code' => 'MISE_A_PIED', 'nom' => 'Mise à pied', 'gravite' => 'elevee', 'impact_financier' => true, 'impact_carriere' => true, 'duree_max_jours' => 30],
            ['code' => 'RETENUE', 'nom' => 'Retenue sur salaire', 'gravite' => 'moyenne', 'impact_financier' => true, 'impact_carriere' => false],
            ['code' => 'LICENCIEMENT', 'nom' => 'Licenciement', 'gravite' => 'tres_elevee', 'impact_financier' => false, 'impact_carriere' => true],
        ];
        foreach ($typesSanction as $data) {
            TypeSanction::firstOrCreate(['code' => $data['code']], $data);
        }

        // 7. Types de formation
        $typesFormation = [
            ['code' => 'INTERNE', 'nom' => 'Formation interne', 'categorie' => 'INTERNE'],
            ['code' => 'EXTERNE', 'nom' => 'Formation externe', 'categorie' => 'EXTERNE'],
            ['code' => 'E_LEARNING', 'nom' => 'E-learning / Distanciel', 'categorie' => 'E_LEARNING'],
            ['code' => 'HYBRIDE', 'nom' => 'Formation hybride', 'categorie' => 'HYBRIDE'],
            ['code' => 'COACHING', 'nom' => 'Coaching / Mentorat', 'categorie' => 'INTERNE'],
        ];
        foreach ($typesFormation as $data) {
            TypeFormation::firstOrCreate(['code' => $data['code']], $data);
        }

        // 8. Motifs de départ
        $motifsDepart = [
            ['code' => 'DEMISSION', 'nom' => 'Démission', 'categorie' => 'DEMISSION', 'est_volontaire' => true, 'necessite_preavis' => true, 'preavis_jours' => 30],
            ['code' => 'LICENCIEMENT', 'nom' => 'Licenciement', 'categorie' => 'LICENCIEMENT', 'est_volontaire' => false, 'necessite_preavis' => true, 'preavis_jours' => 30],
            ['code' => 'FIN_CDD', 'nom' => 'Fin de CDD', 'categorie' => 'FIN_CONTRAT', 'est_volontaire' => false, 'necessite_preavis' => false],
            ['code' => 'FIN_STAGE', 'nom' => 'Fin de stage', 'categorie' => 'FIN_CONTRAT', 'est_volontaire' => false, 'necessite_preavis' => false],
            ['code' => 'RETRAITE', 'nom' => 'Retraite', 'categorie' => 'RETRAITE', 'est_volontaire' => true, 'necessite_preavis' => true, 'preavis_jours' => 60],
            ['code' => 'RUPTURE_CONV', 'nom' => 'Rupture conventionnelle', 'categorie' => 'AUTRE', 'est_volontaire' => true, 'necessite_preavis' => false],
            ['code' => 'MUTATION', 'nom' => 'Mutation / Transfert', 'categorie' => 'AUTRE', 'est_volontaire' => false, 'necessite_preavis' => false],
            ['code' => 'ABANDON', 'nom' => 'Abandon de poste', 'categorie' => 'LICENCIEMENT', 'est_volontaire' => false, 'necessite_preavis' => false],
            ['code' => 'AUTRE', 'nom' => 'Autre motif', 'categorie' => 'AUTRE', 'est_volontaire' => false, 'necessite_preavis' => false],
        ];
        foreach ($motifsDepart as $data) {
            MotifDepart::firstOrCreate(['code' => $data['code']], $data);
        }

        $this->command->info('✅ Paramètres RH initialisés avec succès !');
    }
}