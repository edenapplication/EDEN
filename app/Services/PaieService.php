<?php

namespace App\Services;

use App\Models\RH\Employe;
use App\Models\RH\Contrat;
use App\Models\RH\BulletinPaie;
use App\Models\RH\CnpsDeclaration;
use Carbon\Carbon;

class PaieService
{
    /**
     * Générer un bulletin de paie pour un employé
     */
    public function genererBulletin(Employe $employe, string $periode, string $vague, string $datePaiement): BulletinPaie
    {
        // Récupérer le contrat actif
        $contrat = $employe->contratActif;

        // Calculer le salaire de base
        $salaireBase = $contrat ? $contrat->salaire_base : $employe->salaire_base;

        // Créer le bulletin
        $bulletin = BulletinPaie::create([
            'employe_id' => $employe->id,
            'contrat_id' => $contrat?->id,
            'periode' => $periode,
            'mois_annee' => Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y'),
            'date_paiement' => $datePaiement,
            'vague' => $vague,
            'salaire_brut' => $salaireBase,
            'base_cnps' => $salaireBase,
            'montant_fixe' => 1000,
            'statut' => 'brouillon',
            'est_generer_auto' => true,
        ]);

        // Recalculer tous les totaux
        $bulletin->recalculer();
        $bulletin->save();

        return $bulletin;
    }

    /**
     * Générer les bulletins en masse pour une vague
     */
    public function genererMasse(string $periode, string $vague, string $datePaiement): array
    {
        $employes = Employe::where('actif', true)
            ->where('vague_paiement', $vague)
            ->get();

        $crees = 0;
        $erreurs = [];

        foreach ($employes as $employe) {
            try {
                // Vérifier si le bulletin existe déjà
                $existe = BulletinPaie::where('employe_id', $employe->id)
                    ->where('periode', $periode)
                    ->where('vague', $vague)
                    ->exists();

                if (!$existe) {
                    $this->genererBulletin($employe, $periode, $vague, $datePaiement);
                    $crees++;
                }
            } catch (\Exception $e) {
                $erreurs[] = $employe->matricule . ' : ' . $e->getMessage();
            }
        }

        return [
            'crees' => $crees,
            'erreurs' => $erreurs,
        ];
    }

    /**
     * Récupérer les employés à inclure dans une déclaration CNPS
     */
    public function getEmployesPourDeclaration(string $periode): array
    {
        $employes = Employe::with(['cnpsAffiliation'])
            ->where('actif', true)
            ->get();

        $result = [];

        foreach ($employes as $employe) {
            // Vérifier si l'employé a un bulletin pour cette période
            $bulletin = BulletinPaie::where('employe_id', $employe->id)
                ->where('periode', $periode)
                ->first();

            // Si pas de bulletin, utiliser le salaire de base
            $salaireSoumis = $bulletin ? $bulletin->salaire_brut : $employe->salaire_base;

            $result[] = [
                'employe' => $employe,
                'salaire_soumis' => $salaireSoumis,
                'cotisation_salariale' => CnpsDeclaration::calculerCotisationSalariale($salaireSoumis),
                'cotisation_patronale' => CnpsDeclaration::calculerCotisationPatronale($salaireSoumis),
                'total_cnps' => CnpsDeclaration::calculerTotalCnps($salaireSoumis),
                'bulletin' => $bulletin,
            ];
        }

        return $result;
    }
}