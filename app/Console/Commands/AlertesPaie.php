<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RH\Contrat;
use App\Models\RH\BulletinPaie;
use App\Models\RH\Employe;
use App\Models\RH\CnpsDeclaration;
use Carbon\Carbon;

class AlertesPaie extends Command
{
    protected $signature = 'rh:alertes-paie';
    protected $description = 'Génère les alertes pour la paie et les contrats';

    public function handle()
    {
        $this->info('📊 Génération des alertes paie...');

        // 1. Alertes fin de période d'essai (J-7, J-3, J-1)
        $this->alertesPeriodeEssai();

        // 2. Alertes fin de CDD (J-15, J-7, J-3)
        $this->alertesFinCDD();

        // 3. Alertes bulletins non générés pour le mois
        $this->alertesBulletinsManquants();

        // 4. Alertes déclarations CNPS en retard
        $this->alertesCnpsEnRetard();

        $this->info('✅ Alertes paie générées avec succès.');
    }

    private function alertesPeriodeEssai()
    {
        $dates = [
            'J-7' => now()->addDays(7),
            'J-3' => now()->addDays(3),
            'J-1' => now()->addDay(),
        ];

        foreach ($dates as $label => $date) {
            $contrats = Contrat::where('statut', 'actif')
                ->whereNotNull('date_fin_periode_essai')
                ->whereDate('date_fin_periode_essai', $date->toDateString())
                ->with('employe')
                ->get();

            foreach ($contrats as $contrat) {
                $this->line("⚠️ {$label} - Période d'essai de {$contrat->employe?->nom} {$contrat->employe?->prenom} se termine le {$contrat->date_fin_periode_essai->format('d/m/Y')}");
                
                // Log dans la base de données (à implémenter)
                // Notification::create(...)
            }
        }
    }

    private function alertesFinCDD()
    {
        $dates = [
            'J-15' => now()->addDays(15),
            'J-7' => now()->addDays(7),
            'J-3' => now()->addDays(3),
        ];

        foreach ($dates as $label => $date) {
            $contrats = Contrat::where('statut', 'actif')
                ->whereNotNull('date_fin')
                ->whereDate('date_fin', $date->toDateString())
                ->with('employe')
                ->get();

            foreach ($contrats as $contrat) {
                $this->line("⚠️ {$label} - CDD de {$contrat->employe?->nom} {$contrat->employe?->prenom} se termine le {$contrat->date_fin->format('d/m/Y')}");
            }
        }
    }

    private function alertesBulletinsManquants()
    {
        $periode = now()->format('Y-m');
        $employes = Employe::where('actif', true)->get();

        $count = 0;
        foreach ($employes as $employe) {
            $existe = BulletinPaie::where('employe_id', $employe->id)
                ->where('periode', $periode)
                ->exists();

            if (!$existe) {
                $this->line("⚠️ Bulletin manquant pour {$employe->nom} {$employe->prenom} - {$periode}");
                $count++;
            }
        }

        if ($count === 0) {
            $this->line("✅ Tous les bulletins sont générés pour {$periode}");
        }
    }

    private function alertesCnpsEnRetard()
    {
        $periode = now()->subMonth()->format('Y-m');
        
        $declaration = CnpsDeclaration::where('periode', $periode)
            ->whereIn('statut', ['a_declarer', 'declare'])
            ->first();

        if ($declaration) {
            $this->line("⚠️ Déclaration CNPS du mois {$periode} est toujours en statut '{$declaration->statut_label}'");
        } else {
            $this->line("✅ Aucune déclaration CNPS en retard pour {$periode}");
        }
    }
}