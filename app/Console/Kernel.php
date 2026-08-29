<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Les commandes Artisan enregistrées dans l'application.
     *
     * @var array
     */
    protected $commands = [
        // Commandes personnalisées
        \App\Console\Commands\AlertesPaie::class,
        \App\Console\Commands\AlerteContrats::class,
        \App\Console\Commands\AlerteDocuments::class,
        \App\Console\Commands\AlerteVisitesMedicales::class,
    ];

    /**
     * Définir le planning des commandes de l'application.
     */
    protected function schedule(Schedule $schedule): void
    {
        // ============================================================
        // ALERTES RH - Tous les jours à 8h00
        // ============================================================
        $schedule->command('rh:alertes-paie')->dailyAt('08:00');

        // ============================================================
        // ALERTES CONTRATS - Tous les jours à 8h30
        // ============================================================
        $schedule->command('rh:alertes-contrats')->dailyAt('08:30');

        // ============================================================
        // ALERTES DOCUMENTS - Tous les jours à 9h00
        // ============================================================
        $schedule->command('rh:alertes-documents')->dailyAt('09:00');

        // ============================================================
        // ALERTES VISITES MÉDICALES - Tous les lundis à 8h00
        // ============================================================
        $schedule->command('rh:alertes-visites-medicales')->weeklyOn(1, '08:00');

        // ============================================================
        // RAPPORT HEBDOMADAIRE RH - Tous les lundis à 7h00
        // ============================================================
        $schedule->command('rh:rapport-hebdomadaire')->weeklyOn(1, '07:00');

        // ============================================================
        // RAPPORT MENSUEL RH - 1er jour du mois à 6h00
        // ============================================================
        $schedule->command('rh:rapport-mensuel')->monthlyOn(1, '06:00');

        // ============================================================
        // NETTOYAGE DES FICHIERS TEMPORAIRES - Tous les dimanches à 3h00
        // ============================================================
        $schedule->command('rh:nettoyer-temporaires')->weeklyOn(0, '03:00');

        // ============================================================
        // MISE À JOUR DES STATUTS AUTOMATIQUES - Tous les jours à 1h00
        // ============================================================
        $schedule->command('rh:mettre-a-jour-statuts')->dailyAt('01:00');

        // ============================================================
        // COMMANDES LARAVEL INTÉGRÉES
        // ============================================================
        $schedule->command('telescope:prune')->daily(); // Nettoyer Telescope
        $schedule->command('queue:work --stop-when-empty')->everyMinute()->withoutOverlapping();
    }

    /**
     * Enregistrer les commandes de l'application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    protected function schedule(Schedule $schedule): void
{
    // ... autres commandes ...

    // Vérification des alertes toutes les 30 minutes
    $schedule->command('rh:verifier-alertes')->everyThirtyMinutes();

    // Envoi des notifications par email toutes les heures
    $schedule->command('rh:envoyer-notifications --canal=email')->hourly();

    // Envoi des notifications d'interface toutes les 5 minutes
    $schedule->command('rh:envoyer-notifications --canal=interface')->everyFiveMinutes();

    // Nettoyage des anciennes alertes tous les jours
    $schedule->command('rh:verifier-alertes')->dailyAt('02:00');
}

}