<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RH\Contrat;
use Carbon\Carbon;

class AlerteContrats extends Command
{
    protected $signature = 'rh:alertes-contrats';
    protected $description = 'Alertes pour les contrats arrivant à échéance';

    public function handle()
    {
        $this->info('📋 Vérification des contrats...');

        // Contrats CDD arrivant à échéance dans les 30 jours
        $contrats = Contrat::where('statut', 'actif')
            ->whereNotNull('date_fin')
            ->whereBetween('date_fin', [
                Carbon::now()->startOfDay(),
                Carbon::now()->addDays(30)->endOfDay()
            ])
            ->with('employe')
            ->orderBy('date_fin')
            ->get();

        if ($contrats->isEmpty()) {
            $this->line('✅ Aucun contrat arrivant à échéance dans les 30 jours.');
        } else {
            foreach ($contrats as $contrat) {
                $jours = Carbon::now()->diffInDays($contrat->date_fin);
                $this->line("⚠️ Contrat de {$contrat->employe?->nom} {$contrat->employe?->prenom} expire dans {$jours} jours le {$contrat->date_fin->format('d/m/Y')}");
            }
        }

        // Renouvellements possibles
        $renouvelables = Contrat::where('statut', 'termine')
            ->where('est_renouvelable', true)
            ->where('nb_renouvellements', '<', 'renouvellement_max')
            ->whereDate('date_fin', '>=', Carbon::now()->subDays(30)->toDateString())
            ->with('employe')
            ->get();

        if ($renouvelables->isNotEmpty()) {
            foreach ($renouvelables as $contrat) {
                $this->line("🔄 Contrat de {$contrat->employe?->nom} {$contrat->employe?->prenom} est renouvelable ({$contrat->nb_renouvellements}/{$contrat->renouvellement_max} renouvellements)");
            }
        }

        return 0;
    }
}