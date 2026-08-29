<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AlerteService;

class VerifierAlertes extends Command
{
    protected $signature = 'rh:verifier-alertes';
    protected $description = 'Vérifie toutes les conditions d\'alerte et génère les notifications';

    protected AlerteService $alerteService;

    public function __construct(AlerteService $alerteService)
    {
        parent::__construct();
        $this->alerteService = $alerteService;
    }

    public function handle()
    {
        $this->info('🔍 Vérification des alertes en cours...');

        $resultats = $this->alerteService->verifierTout();

        $total = 0;
        foreach ($resultats as $type => $alertes) {
            $total += count($alertes);
            $this->line("📌 {$type}: " . count($alertes) . " alerte(s) générée(s)");
        }

        // Nettoyer les anciennes alertes
        $this->alerteService->nettoyerAnciennesAlertes();

        $this->info("✅ {$total} alerte(s) générée(s) avec succès.");

        return 0;
    }
}