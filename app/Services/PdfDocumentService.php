<?php

namespace App\Services;

use App\Models\DocumentPdf;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class PdfDocumentService
{
    /**
     * Génère et stocke un PDF en base.
     *
     * @param string $type       'programmation_initiale' | 'programmation_active'
     * @param \Carbon\Carbon $dateSemaine   Doit être un dimanche
     * @param array $lignes      Collection de lignes à afficher
     * @param array $stats       Stats pour l'en-tête
     * @param string $vue        Nom de la vue Blade
     * @return DocumentPdf
     */
    public static function genererEtStocker(string $type, $dateSemaine, $lignes, array $stats, string $vue = 'admin.affectations.pdf-modele'): DocumentPdf
    {
        // 1. Générer le PDF
        $data = [
            'type'            => $type,
            'dateSemaine'     => $dateSemaine,
            'lignes'          => $lignes,
            'stats'           => $stats,
            'dateGeneration'  => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView($vue, $data)->setPaper('A4', 'landscape');

        // 2. Nom + chemin
        $nomFichier = 'programmation_' . $type . '_' . $dateSemaine->format('Y-m-d') . '_' . now()->format('His') . '.pdf';
        $cheminRelatif = 'documents_pdf/' . $nomFichier;

        // 3. Sauvegarder sur le disque public
        Storage::disk('public')->put($cheminRelatif, $pdf->output());

        // 4. Enregistrer en base
        return DocumentPdf::create([
            'type'              => $type,
            'date_semaine'      => $dateSemaine->format('Y-m-d'),
            'nom_fichier'       => $nomFichier,
            'chemin_fichier'    => $cheminRelatif,
            'nb_lignes'         => $lignes->count(),
            'nb_lots'           => $stats['total_lots'] ?? 0,
            'superficie_totale' => $stats['total_superficie'] ?? 0,
            'user_id'           => auth()->id(),
        ]);
    }

    /**
     * Retourne tous les dimanches ayant des documents.
     */
    public static function getDimanchesDisponibles()
    {
        return DocumentPdf::select('date_semaine')
            ->distinct()
            ->orderByDesc('date_semaine')
            ->pluck('date_semaine');
    }
}