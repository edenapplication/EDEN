<?php
namespace App\Http\Controllers;

use App\Models\DossierClient;
use App\Models\Client;
use App\Models\Commercial;
use App\Models\Conducteur;
use App\Models\Facilitateur;
use App\Models\AgentCommercial;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DossierClientController extends Controller
{
    // ✅ Supprimer un dossier (sans supprimer le client)
    public function supprimerDossier($dossierId)
    {
        try {
            $dossier = DossierClient::findOrFail($dossierId);
            $clientId = $dossier->client_id;

            // Supprimer les paiements liés
            $dossier->paiements()->delete();
            if (method_exists($dossier, 'paiementsTechniques'))
                $dossier->paiementsTechniques()->delete();
            if (method_exists($dossier, 'paiementsMorcellements'))
                $dossier->paiementsMorcellements()->delete();

            // Supprimer les fichiers CNI
            if ($dossier->cni_images) {
                foreach ($dossier->cni_images as $image) {
                    $path = storage_path('app/public/' . $image);
                    if (file_exists($path)) {
                        unlink($path);
                    }
                }
            }

            $dossier->delete();

            return response()->json([
                'success'   => true,
                'client_id' => $clientId,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ✅ Mise à jour prix — avec log pour debug
    public function majPrix(Request $request, $dossierId)
    {
        try {
            $request->validate([
                'prix_superficie'   => 'nullable|numeric|min:0',
                'prix_technique'    => 'nullable|numeric|min:0',
                'prix_morcellement' => 'nullable|numeric|min:0',
                'prix_logistique'   => 'nullable|numeric|min:0',
            ]);

            $dossier = DossierClient::findOrFail($dossierId);

            $data = [];
            if ($request->has('prix_superficie'))   $data['prix_superficie']   = $request->prix_superficie   ?? 0;
            if ($request->has('prix_technique'))    $data['prix_technique']    = $request->prix_technique    ?? 0;
            if ($request->has('prix_morcellement')) $data['prix_morcellement'] = $request->prix_morcellement ?? 0;
            if ($request->has('prix_logistique'))   $data['prix_logistique']   = $request->prix_logistique   ?? 0;

            $dossier->update($data);

            return response()->json([
                'success'          => true,
                'prix_superficie'  => $dossier->fresh()->prix_superficie,
                'prix_technique'   => $dossier->fresh()->prix_technique,
                'prix_morcellement'=> $dossier->fresh()->prix_morcellement,
                'prix_logistique'  => $dossier->fresh()->prix_logistique,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ✅ Mise à jour des étapes
    public function majEtape(Request $request, $dossierId)
    {
        try {
            $request->validate([
                'etape'  => 'required|in:implantation_prevue,deja_implante,dossier_technique,morcellement',
                'date'   => 'nullable|date',
                'active' => 'boolean',
            ]);

            $dossier = DossierClient::findOrFail($dossierId);

            $etapes = [
                'implantation_prevue' => 1,
                'deja_implante'       => 2,
                'dossier_technique'   => 3,
                'morcellement'        => 4,
            ];

            $champs = [
                'implantation_prevue' => 'date_implantation_prevue',
                'deja_implante'       => 'date_deja_implante',
                'dossier_technique'   => 'date_dossier_technique',
                'morcellement'        => 'date_morcellement',
            ];

            $etapeSelectionnee = $request->etape;
            $active            = $request->boolean('active', true);
            $date              = $request->date;

            $data = [];

            if ($active && $date) {
                // ✅ Activation avec date
                // Mettre à jour la date de l'étape sélectionnée
                $data[$champs[$etapeSelectionnee]] = $date;
                
                // Pour les étapes précédentes, si pas de date, mettre la date du jour
                foreach ($etapes as $etapeNom => $ordre) {
                    if ($ordre < $etapes[$etapeSelectionnee] && empty($dossier->{$champs[$etapeNom]})) {
                        $data[$champs[$etapeNom]] = now()->format('Y-m-d');
                    }
                }
                $data['etape_actuelle'] = $etapeSelectionnee;
            } else {
                // ✅ Désactivation ou suppression
                // Effacer cette étape et toutes les suivantes
                foreach ($etapes as $etapeNom => $ordre) {
                    if ($ordre >= $etapes[$etapeSelectionnee]) {
                        $data[$champs[$etapeNom]] = null;
                    }
                }
                // Revenir à l'étape précédente
                $etapePrecedente = null;
                foreach ($etapes as $etapeNom => $ordre) {
                    if ($ordre < $etapes[$etapeSelectionnee] && !empty($dossier->{$champs[$etapeNom]})) {
                        $etapePrecedente = $etapeNom;
                    }
                }
                $data['etape_actuelle'] = $etapePrecedente;
            }

            $dossier->update($data);

            return response()->json([
                'success'       => true,
                'etape_actuelle'=> $dossier->fresh()->etape_actuelle,
                'dates'         => [
                    'implantation_prevue' => $dossier->fresh()->date_implantation_prevue?->format('d/m/Y'),
                    'deja_implante'       => $dossier->fresh()->date_deja_implante?->format('d/m/Y'),
                    'dossier_technique'   => $dossier->fresh()->date_dossier_technique?->format('d/m/Y'),
                    'morcellement'        => $dossier->fresh()->date_morcellement?->format('d/m/Y'),
                ],
            ]);

        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Erreur majEtape: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // Export Excel
    public function exportExcel(Request $request)
    {
        $query = DossierClient::with([
            'client',
            'grandSite',
            'paiements' => fn($q) => $q->orderBy('date_paiement')->limit(1),
        ]);

        if ($request->filled('du'))           $query->whereDate('created_at', '>=', $request->du);
        if ($request->filled('au'))           $query->whereDate('created_at', '<=', $request->au);
        if ($request->filled('grand_site_id'))$query->where('grand_site_id', $request->grand_site_id);

        $dossiers    = $query->orderByDesc('created_at')->get();
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Dossiers Clients');

        $headers = [
            'A'=>'Identifiant','B'=>'Mot de passe','C'=>'Nom','D'=>'Email',
            'E'=>'Téléphone','F'=>'Ville','G'=>'Sexe','H'=>'Rôle','I'=>'Actif',
            'J'=>'Intitulé dossier','K'=>'Superficie (m²)',
            'L'=>'Date paiement (YYYY-MM-DD)','M'=>'Notes dossier',
            'N'=>'Identifiant accès 2','O'=>'Identifiant accès 3','P'=>'Identifiant accès 4',
        ];

        foreach ($headers as $col => $header) {
            $sheet->setCellValue($col.'1', $header);
            $sheet->getStyle($col.'1')->getFont()->setBold(true);
            $sheet->getStyle($col.'1')->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('1E3A5F');
            $sheet->getStyle($col.'1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        $row = 2;
        foreach ($dossiers as $dossier) {
            $client          = $dossier->client;
            $nom             = $client?->name ?? '';
            $telephone       = $client?->phone ?? '';
            $grandSiteNom    = $dossier->grandSite?->nom ?? $dossier->nom_dossier ?? '';
            $superficie      = $dossier->superficie_voulue ?? '';
            $premierPaiement = $dossier->paiements->first();
            $datePaiement    = $premierPaiement
                ? \Carbon\Carbon::parse($premierPaiement->date_paiement)->format('d/m/Y')
                : '';
            $emailGen = $nom
                ? strtolower(str_replace([' ',"'"], ['.',''],
                    iconv('UTF-8','ASCII//TRANSLIT',$nom))).'@edengroup.cm'
                : '';

            $sheet->setCellValue('A'.$row, $nom);
            $sheet->setCellValue('B'.$row, 'eden');
            $sheet->setCellValue('C'.$row, $nom);
            $sheet->setCellValue('D'.$row, $emailGen);
            $sheet->setCellValue('E'.$row, $telephone);
            $sheet->setCellValue('F'.$row, 'Yaoundé');
            $sheet->setCellValue('G'.$row, 'masculin');
            $sheet->setCellValue('H'.$row, 'client');
            $sheet->setCellValue('I'.$row, 'Oui');
            $sheet->setCellValue('J'.$row, $grandSiteNom);
            $sheet->setCellValue('K'.$row, $superficie);
            $sheet->setCellValue('L'.$row, $datePaiement);
            $sheet->setCellValue('M'.$row, 'EDEN GROUP');
            $sheet->setCellValue('N'.$row, '');
            $sheet->setCellValue('O'.$row, '');
            $sheet->setCellValue('P'.$row, '');
            $row++;
        }

        foreach (range('A','P') as $col)
            $sheet->getColumnDimension($col)->setAutoSize(true);

        $fileName = 'dossiers_clients_'.now()->format('Y-m-d').'.xlsx';
        $writer   = new Xlsx($spreadsheet);
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="'.$fileName.'"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    // ✅ Récupérer les CNI d'un dossier
    public function getCni($dossierId)
    {
        try {
            $dossier = DossierClient::findOrFail($dossierId);
            return response()->json([
                'cni' => $dossier->cni_images ?? []
            ]);
        } catch (\Throwable $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // ✅ Supprimer une image CNI spécifique
    public function deleteCniImage(Request $request, $dossierId)
    {
        try {
            $dossier = DossierClient::findOrFail($dossierId);
            $image = $request->input('image');
            
            if (!$image) {
                return response()->json(['success' => false, 'message' => 'Image non spécifiée'], 400);
            }

            $cnis = $dossier->cni_images ?? [];
            
            // Supprimer l'image du tableau
            $key = array_search($image, $cnis);
            if ($key !== false) {
                unset($cnis[$key]);
                $cnis = array_values($cnis);
                
                // Supprimer le fichier physique
                $path = storage_path('app/public/' . $image);
                if (file_exists($path)) {
                    unlink($path);
                }
                
                $dossier->cni_images = $cnis;
                $dossier->save();
                
                return response()->json(['success' => true]);
            }
            
            return response()->json(['success' => false, 'message' => 'Image non trouvée'], 404);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}