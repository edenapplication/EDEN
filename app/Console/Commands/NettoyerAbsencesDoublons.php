<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RH\Absence;
use Illuminate\Support\Facades\DB;

class NettoyerAbsencesDoublons extends Command
{
    protected $signature = 'rh:nettoyer-absences-doublons {--dry-run : Simuler sans supprimer}';
    protected $description = 'Supprime les absences injustifiées en double (même employé, même date)';

    public function handle()
    {
        $this->info('🔍 Recherche des absences injustifiées en double...');

        $dryRun = $this->option('dry-run');

        // Récupérer les doublons (même employé, même date)
        $doublons = DB::table('rh_absences')
            ->select('employe_id', 'date_debut', DB::raw('COUNT(*) as total'))
            ->where('type_absence', 'Absence injustifiée')
            ->where('statut', 'refusé')
            ->groupBy('employe_id', 'date_debut')
            ->having('total', '>', 1)
            ->get();

        if ($doublons->isEmpty()) {
            $this->info('✅ Aucune absence injustifiée en double trouvée.');
            return 0;
        }

        $this->info("📊 {$doublons->count()} groupe(s) de doublons trouvé(s).");

        $totalSupprimees = 0;

        foreach ($doublons as $d) {
            // Récupérer toutes les absences pour cet employé et cette date
            $absences = Absence::where('employe_id', $d->employe_id)
                ->where('date_debut', $d->date_debut)
                ->where('type_absence', 'Absence injustifiée')
                ->where('statut', 'refusé')
                ->orderBy('created_at')
                ->get();

            $count = $absences->count();
            $this->line("   - Employé ID {$d->employe_id}, Date {$d->date_debut}: {$count} absence(s)");

            // Garder la première, supprimer les autres
            $premiere = $absences->shift();

            if ($dryRun) {
                $this->line("     [DRY-RUN] Supprimerait {$absences->count()} absence(s)");
            } else {
                foreach ($absences as $a) {
                    $a->delete();
                    $totalSupprimees++;
                }
                $this->line("     ✅ {$absences->count()} absence(s) supprimée(s)");
            }
        }

        if ($dryRun) {
            $this->info("🔍 [DRY-RUN] Terminé. {$totalSupprimees} absence(s) seraient supprimées.");
        } else {
            $this->info("✅ Nettoyage terminé. {$totalSupprimees} absence(s) supprimée(s).");
        }

        return 0;
    }
}