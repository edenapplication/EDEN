<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\RH\Employe;

class RegenerateMatricules extends Command
{
    protected $signature   = 'employes:regenerer-matricules';
    protected $description = 'Régénère les matricules de tous les employés selon EDG_MM_AA_ID';

    public function handle()
    {
        $isSQLite = config('database.default') === 'sqlite';

        // ✅ Désactiver les contraintes FK avant de modifier les matricules
        if ($isSQLite) {
            DB::statement('PRAGMA foreign_keys=OFF');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
        }

        $employes = Employe::orderBy('id')->get();
        $this->info("Régénération de {$employes->count()} matricules...");

        $bar = $this->output->createProgressBar($employes->count());
        $bar->start();

        $modifies = 0;

        foreach ($employes as $employe) {
            $ancien   = $employe->matricule;
            $nouveau  = Employe::genererMatricule($employe->id, $employe->date_integration?->format('Y-m-d'));

            if ($ancien !== $nouveau) {
                // ✅ Utiliser DB directement pour éviter les événements Eloquent
                DB::table('rh_employes')
                    ->where('id', $employe->id)
                    ->update(['matricule' => $nouveau]);

                $this->newLine();
                $this->line("  ID {$employe->id} : {$ancien} → {$nouveau}");
                $modifies++;
            }

            $bar->advance();
        }

        // ✅ Réactiver les contraintes FK
        if ($isSQLite) {
            DB::statement('PRAGMA foreign_keys=ON');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        $bar->finish();
        $this->newLine(2);
        $this->info("✅ Terminé : {$modifies} matricule(s) modifié(s).");
        return 0;
    }
}