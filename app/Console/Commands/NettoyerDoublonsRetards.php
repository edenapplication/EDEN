<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RH\Retard;
use App\Models\RH\Absence;
use Carbon\Carbon;

class NettoyerDoublonsRetards extends Command
{
    protected $signature = 'rh:nettoyer-doublons-retards';
    protected $description = 'Supprime les doublons de retards (même employé, même date) et crée des absences injustifiées si nécessaire';

    public function handle()
    {
        $this->info('🔍 Nettoyage des doublons de retards...');

        // Récupérer tous les retards groupés par employé et date
        $doublons = Retard::select('employe_id', 'date')
            ->groupBy('employe_id', 'date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $supprimes = 0;
        $absencesCrees = 0;

        foreach ($doublons as $d) {
            $retards = Retard::where('employe_id', $d->employe_id)
                ->where('date', $d->date)
                ->orderBy('created_at')
                ->get();

            // Garder le premier, supprimer les autres
            $premier = $retards->shift();
            foreach ($retards as $r) {
                $r->delete();
                $supprimes++;
            }

            // Vérifier si une absence existe déjà pour cette date
            $absenceExiste = Absence::where('employe_id', $d->employe_id)
                ->whereDate('date_debut', '<=', $d->date)
                ->whereDate('date_fin', '>=', $d->date)
                ->exists();

            // Si le retard gardé n'a pas de retard ni d'heures sup, créer une absence
            if ($premier->minutes_retard === 0 && $premier->minutes_sup === 0 && !$absenceExiste) {
                $reference = 'ABS-INJ-' . strtoupper(substr(uniqid(), -6));
                Absence::create([
                    'employe_id' => $d->employe_id,
                    'reference' => $reference,
                    'date_debut' => $d->date,
                    'date_fin' => $d->date,
                    'nombre_jours' => 1,
                    'motif' => 'Nettoyage doublons - Pointage sans retard',
                    'type_journee' => 'journée',
                    'type_absence' => 'Absence injustifiée',
                    'justificatif_fourni' => false,
                    'statut' => 'refusé',
                    'observations' => 'Généré automatiquement après nettoyage des doublons',
                ]);
                $absencesCrees++;
            }
        }

        $this->info("✅ Nettoyage terminé :");
        $this->line("   - {$supprimes} doublon(s) supprimé(s)");
        $this->line("   - {$absencesCrees} absence(s) injustifiée(s) créée(s)");

        return 0;
    }
}