<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RH\Employe;
use App\Models\RH\Contrat;
use App\Models\RH\Absence;
use App\Models\RH\CnpsDeclaration;
use Carbon\Carbon;

class RapportHebdomadaireRH extends Command
{
    protected $signature = 'rh:rapport-hebdomadaire';
    protected $description = 'Génère le rapport hebdomadaire RH';

    public function handle()
    {
        $this->info('📊 Génération du rapport hebdomadaire RH...');

        $debutSemaine = Carbon::now()->startOfWeek();
        $finSemaine = Carbon::now()->endOfWeek();

        // Statistiques de la semaine
        $stats = [
            'nouveaux_employes' => Employe::whereBetween('created_at', [$debutSemaine, $finSemaine])->count(),
            'departs' => Employe::whereBetween('date_sortie', [$debutSemaine, $finSemaine])->count(),
            'absences' => Absence::whereBetween('date_debut', [$debutSemaine, $finSemaine])->count(),
            'contrats_signes' => Contrat::whereBetween('date_signature', [$debutSemaine, $finSemaine])->count(),
        ];

        $this->line("👥 Nouveaux employés : {$stats['nouveaux_employes']}");
        $this->line("🚪 Départs : {$stats['departs']}");
        $this->line("🗓️ Absences : {$stats['absences']}");
        $this->line("📄 Contrats signés : {$stats['contrats_signes']}");

        // Alertes CNPS
        $declarationsEnAttente = CnpsDeclaration::where('statut', 'a_declarer')->count();
        if ($declarationsEnAttente > 0) {
            $this->line("⚠️ {$declarationsEnAttente} déclaration(s) CNPS en attente");
        }

        $this->info('✅ Rapport hebdomadaire terminé.');
        return 0;
    }
}