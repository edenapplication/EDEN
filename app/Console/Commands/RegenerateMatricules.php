<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RH\Employe;

class RegenerateMatricules extends Command
{
    protected $signature   = 'employes:regenerer-matricules';
    protected $description = 'Régénère les matricules de tous les employés selon EDG_MM_AA_ID';

    public function handle()
    {
        $employes = Employe::orderBy('id')->get();
        $this->info("Régénération de {$employes->count()} matricules...");

        $bar = $this->output->createProgressBar($employes->count());
        $bar->start();

        foreach ($employes as $employe) {
            $ancienMatricule  = $employe->matricule;
            $nouveauMatricule = Employe::genererMatricule($employe->id, $employe->date_integration?->format('Y-m-d'));

            if ($ancienMatricule !== $nouveauMatricule) {
                $employe->matricule = $nouveauMatricule;
                $employe->save();
                $this->newLine();
                $this->line("  ID {$employe->id} : {$ancienMatricule} → {$nouveauMatricule}");
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('✅ Matricules régénérés avec succès.');
        return 0;
    }
}