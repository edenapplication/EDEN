<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class NettoyerTemporaires extends Command
{
    protected $signature = 'rh:nettoyer-temporaires';
    protected $description = 'Nettoie les fichiers temporaires du module RH';

    public function handle()
    {
        $this->info('🧹 Nettoyage des fichiers temporaires...');

        $disk = Storage::disk('public');
        $dossiers = ['rh/temp', 'rh/cache', 'rh/export_temp'];

        foreach ($dossiers as $dossier) {
            if ($disk->exists($dossier)) {
                $fichiers = $disk->files($dossier);
                $compteur = 0;

                foreach ($fichiers as $fichier) {
                    // Supprimer les fichiers de plus de 7 jours
                    $lastModified = $disk->lastModified($fichier);
                    if (time() - $lastModified > 7 * 24 * 60 * 60) {
                        $disk->delete($fichier);
                        $compteur++;
                    }
                }

                $this->line("📁 {$dossier} : {$compteur} fichier(s) supprimé(s)");
            }
        }

        $this->info('✅ Nettoyage terminé.');
        return 0;
    }
}