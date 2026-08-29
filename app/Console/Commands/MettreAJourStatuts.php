<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RH\Contrat;
use App\Models\RH\Conge;
use Carbon\Carbon;

class MettreAJourStatuts extends Command
{
    protected $signature = 'rh:mettre-a-jour-statuts';
    protected $description = 'Met à jour automatiquement les statuts des contrats et congés';

    public function handle()
    {
        $this->info('🔄 Mise à jour automatique des statuts...');

        // 1. Contrats : ceux qui ont dépassé la date de fin
        $contrats = Contrat::whereIn('statut', ['actif', 'valide'])
            ->whereNotNull('date_fin')
            ->whereDate('date_fin', '<', Carbon::now())
            ->get();

        foreach ($contrats as $contrat) {
            $contrat->update(['statut' => 'termine']);
            $this->line("📄 Contrat {$contrat->numero_contrat} marqué comme terminé");
        }

        // 2. Congés : mise à jour automatique
        $conges = Conge::where('statut', 'planifie')
            ->whereDate('date_debut', '<=', Carbon::now())
            ->get();

        foreach ($conges as $conge) {
            if (Carbon::now()->between($conge->date_debut, $conge->date_fin)) {
                $conge->update(['statut' => 'en_cours']);
                $this->line("🏖️ Congé de {$conge->employe?->nom} {$conge->employe?->prenom} marqué comme en cours");
            } elseif (Carbon::now()->gt($conge->date_fin)) {
                $conge->update(['statut' => 'termine']);
                $this->line("🏖️ Congé de {$conge->employe?->nom} {$conge->employe?->prenom} marqué comme terminé");
            }
        }

        $this->info('✅ Mise à jour terminée.');
        return 0;
    }
}