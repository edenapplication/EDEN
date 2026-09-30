<?php
namespace App\Http\Controllers;

use App\Models\DossierClient;
use App\Models\Client;
use App\Models\Beneficiaire;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Illuminate\Support\Facades\Log;

class DossierClientController extends Controller
{
    // ════════════════════════════════════════════════════════════════
    // ✅ SUPPRIMER UN DOSSIER
    // ════════════════════════════════════════════════════════════════
    public function supprimerDossier($dossierId)
    {
        try {
            $dossier = DossierClient::findOrFail($dossierId);
            $clientId = $dossier->client_id;

            $dossier->paiements()->delete();
            if (method_exists($dossier, 'paiementsTechniques'))
                $dossier->paiementsTechniques()->delete();
            if (method_exists($dossier, 'paiementsMorcellements'))
                $dossier->paiementsMorcellements()->delete();

            $dossier->delete();

            return response()->json([
                'success'   => true,
                'client_id' => $clientId,
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ════════════════════════════════════════════════════════════════
    // ✅ MISE À JOUR PRIX
    // ════════════════════════════════════════════════════════════════
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

    // ════════════════════════════════════════════════════════════════
    // ✅ EXPORT EXCEL (Sélection stricte)
    // ════════════════════════════════════════════════════════════════
    // Règles :
    //   - Rien coché                → TOUT (clients + bénéficiaires)
    //   - Clients cochés            → CES clients + LEURS bénéficiaires
    //   - Bénéficiaires cochés      → CES bénéficiaires uniquement
    //   - Clients + Bénéf. cochés   → CES clients + CES bénéf. + bénéf. des clients
    // ════════════════════════════════════════════════════════════════

    public function exportExcel(Request $request)
    {
        try {
            // ✅ Récupérer les IDs sélectionnés séparément
            $clientIds = $request->input('client_ids', []);
            $benefIds  = $request->input('beneficiaire_ids', []);

            // ✅ Compatibilité ancien format (ids[]) → traités comme clients
            $legacyIds = $request->input('ids', []);
            if (!empty($legacyIds)) {
                $clientIds = array_merge($clientIds, $legacyIds);
            }

            $hasClientSelection = !empty($clientIds);
            $hasBenefSelection  = !empty($benefIds);
            $hasAnySelection    = $hasClientSelection || $hasBenefSelection;

            $dossiers      = collect();
            $beneficiaires = collect();

            // ═══════════════════════════════════════════════════════════
            // 1. CLIENTS
            // ═══════════════════════════════════════════════════════════
            if ($hasClientSelection) {
                // ✅ Uniquement les clients sélectionnés
                $dossiers = $this->getDossiersQuery($request)
                    ->whereIn('client_id', $clientIds)
                    ->orderByDesc('created_at')
                    ->get();
            } elseif (!$hasAnySelection) {
                // ✅ Aucune sélection → tous les clients
                $dossiers = $this->getDossiersQuery($request)
                    ->orderByDesc('created_at')
                    ->get();
            }
            // Sinon (bénéfs cochés sans clients) → $dossiers reste vide

            // ═══════════════════════════════════════════════════════════
            // 2. BÉNÉFICIAIRES
            // ═══════════════════════════════════════════════════════════
            if ($hasBenefSelection && !$hasClientSelection) {
                // ✅ Bénéficiaires cochés SANS clients → UNIQUEMENT ces bénéfs
                $beneficiaires = $this->getBeneficiairesQuery($request)
                    ->whereIn('id', $benefIds)
                    ->orderByDesc('created_at')
                    ->get();

            } elseif ($hasBenefSelection && $hasClientSelection) {
                // ✅ Clients + bénéfs cochés → les bénéfs cochés + bénéfs des clients
                $beneficiaires = $this->getBeneficiairesQuery($request)
                    ->where(function ($q) use ($benefIds, $clientIds) {
                        $q->whereIn('id', $benefIds)
                          ->orWhereHas('dossier', fn($sub) => $sub->whereIn('client_id', $clientIds));
                    })
                    ->orderByDesc('created_at')
                    ->get();

            } elseif ($hasClientSelection && !$hasBenefSelection) {
                // ✅ Clients cochés SANS bénéfs → bénéfs de ces clients
                $beneficiaires = $this->getBeneficiairesQuery($request)
                    ->whereHas('dossier', fn($q) => $q->whereIn('client_id', $clientIds))
                    ->orderByDesc('created_at')
                    ->get();

            } else {
                // ✅ Aucune sélection → tous les bénéfs
                $beneficiaires = $this->getBeneficiairesQuery($request)
                    ->orderByDesc('created_at')
                    ->get();
            }

            // ═══════════════════════════════════════════════════════════
            // 3. GÉNÉRER L'EXCEL
            // ═══════════════════════════════════════════════════════════
            if ($dossiers->isEmpty() && $beneficiaires->isEmpty()) {
                return $this->generateEmptyExcel();
            }

            return $this->generateExcel($dossiers, $beneficiaires);

        } catch (\Throwable $e) {
            Log::error('Erreur exportExcel: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de l\'export: ' . $e->getMessage());
        }
    }

    // ════════════════════════════════════════════════════════════════
    // ✅ HELPERS : Requêtes avec relations + filtres
    // ════════════════════════════════════════════════════════════════

    private function getDossiersQuery(Request $request)
    {
        $query = DossierClient::with([
            'client',
            'grandSite',
            'paiements' => fn($q) => $q->orderBy('date_paiement')->limit(1),
            'affectations.lot',
            'affectations.grandSite',
        ]);

        if ($request->filled('du'))            $query->whereDate('created_at', '>=', $request->du);
        if ($request->filled('au'))            $query->whereDate('created_at', '<=', $request->au);
        if ($request->filled('grand_site_id')) $query->where('grand_site_id', $request->grand_site_id);

        return $query;
    }

    private function getBeneficiairesQuery(Request $request)
    {
        $query = Beneficiaire::with([
            'dossier.client',
            'dossier.grandSite',
            'dossier.paiements' => fn($q) => $q->orderBy('date_paiement')->limit(1),
            'affectations.lot',
            'affectations.grandSite',
            'client',
        ]);

        if ($request->filled('du'))            $query->whereDate('created_at', '>=', $request->du);
        if ($request->filled('au'))            $query->whereDate('created_at', '<=', $request->au);
        if ($request->filled('grand_site_id')) $query->whereHas('dossier', fn($q) => $q->where('grand_site_id', $request->grand_site_id));

        return $query;
    }

    // ════════════════════════════════════════════════════════════════
    // ✅ GÉNÉRATION DU FICHIER EXCEL
    // ════════════════════════════════════════════════════════════════

    private function generateExcel($dossiers, $beneficiaires = null)
    {
        $beneficiaires = $beneficiaires ?? collect();

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Clients & Bénéficiaires');

        // ═══ EN-TÊTES ═══
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
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('1E3A5F');
            $sheet->getStyle($col.'1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        $row = 2;

        // ═══════════════════════════════════════════════════════════
        // 1. CLIENTS
        // ═══════════════════════════════════════════════════════════
        foreach ($dossiers as $dossier) {
            $client = $dossier->client;
            if (!$client) continue;

            $nom       = $client->name ?? '';
            $telephone = $client->phone ?? '';
            $sexe      = $client->sexe ?? 'non_renseigne';

            // Intitulé = Grand Site(s) de ses lots
            $grandsSites = $dossier->affectations
                ->pluck('grandSite.nom')
                ->filter()
                ->unique();

            if ($grandsSites->isEmpty() && $dossier->grandSite) {
                $grandsSites = collect([$dossier->grandSite->nom]);
            }

            $intitule = $grandsSites->implode(', ') ?: ($dossier->nom_dossier ?? '-');

            // Superficie = somme des superficies des lots
            $superficieTotale = $dossier->affectations->sum(
                fn($aff) => $aff->lot?->superficie ?? 0
            );

            if ($superficieTotale === 0 && $dossier->superficie_voulue) {
                $superficieTotale = $dossier->superficie_voulue;
            }

            // Date paiement
            $premierPaiement = $dossier->paiements->first();
            $datePaiement = $premierPaiement
                ? \Carbon\Carbon::parse($premierPaiement->date_paiement)->format('d/m/Y')
                : '';

            // Email généré
            $emailGen = $nom
                ? strtolower(str_replace([' ',"'"], ['.',''],
                    iconv('UTF-8','ASCII//TRANSLIT',$nom))).'@edengroup.cm'
                : '';

            // Sexe
            $sexeLabel = match($sexe) {
                'masculin' => 'Masculin',
                'feminin'  => 'Féminin',
                default    => 'Non renseigné',
            };

            // Identifiant
            $identifiant = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nom));
            $identifiant = substr($identifiant, 0, 15) ?: 'user';

            $sheet->setCellValue('A'.$row, $identifiant);
            $sheet->setCellValue('B'.$row, 'eden');
            $sheet->setCellValue('C'.$row, $nom);
            $sheet->setCellValue('D'.$row, $emailGen);
            $sheet->setCellValue('E'.$row, $telephone);
            $sheet->setCellValue('F'.$row, 'Yaoundé');
            $sheet->setCellValue('G'.$row, $sexeLabel);
            $sheet->setCellValue('H'.$row, 'client');
            $sheet->setCellValue('I'.$row, 'Oui');
            $sheet->setCellValue('J'.$row, $intitule);
            $sheet->setCellValue('K'.$row, $superficieTotale ?: '');
            $sheet->setCellValue('L'.$row, $datePaiement);
            $sheet->setCellValue('M'.$row, 'EDEN GROUP');
            $sheet->setCellValue('N'.$row, '');
            $sheet->setCellValue('O'.$row, '');
            $sheet->setCellValue('P'.$row, '');
            $row++;
        }

        // ═══════════════════════════════════════════════════════════
        // 2. BÉNÉFICIAIRES
        // ═══════════════════════════════════════════════════════════
        foreach ($beneficiaires as $benef) {
            $dossier = $benef->dossier;
            $client  = $benef->client ?? $dossier?->client;

            $nom       = $benef->nom ?? '';
            $telephone = $benef->telephone ?? ($client?->phone ?? '');

            // Intitulé = Grand Site(s) de ses lots
            $grandsSites = $benef->affectations
                ->pluck('grandSite.nom')
                ->filter()
                ->unique();

            if ($grandsSites->isEmpty() && $dossier?->grandSite) {
                $grandsSites = collect([$dossier->grandSite->nom]);
            }

            $intitule = $grandsSites->implode(', ') ?: '-';

            // Superficie = somme des superficies des lots
            $superficieTotale = $benef->affectations->sum(
                fn($aff) => $aff->lot?->superficie ?? 0
            );

            // Date paiement
            $premierPaiement = $dossier?->paiements->first();
            $datePaiement = $premierPaiement
                ? \Carbon\Carbon::parse($premierPaiement->date_paiement)->format('d/m/Y')
                : '';

            // Email généré
            $emailGen = $nom
                ? strtolower(str_replace([' ',"'"], ['.',''],
                    iconv('UTF-8','ASCII//TRANSLIT',$nom))).'@edengroup.cm'
                : '';

            // Sexe
            $sexe = $client?->sexe ?? 'non_renseigne';
            $sexeLabel = match($sexe) {
                'masculin' => 'Masculin',
                'feminin'  => 'Féminin',
                default    => 'Non renseigné',
            };

            // Identifiant
            $identifiant = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $nom));
            $identifiant = substr($identifiant, 0, 15) ?: 'user';

            $sheet->setCellValue('A'.$row, $identifiant);
            $sheet->setCellValue('B'.$row, 'eden');
            $sheet->setCellValue('C'.$row, $nom);
            $sheet->setCellValue('D'.$row, $emailGen);
            $sheet->setCellValue('E'.$row, $telephone);
            $sheet->setCellValue('F'.$row, 'Yaoundé');
            $sheet->setCellValue('G'.$row, $sexeLabel);
            $sheet->setCellValue('H'.$row, 'client');
            $sheet->setCellValue('I'.$row, 'Oui');
            $sheet->setCellValue('J'.$row, $intitule);
            $sheet->setCellValue('K'.$row, $superficieTotale ?: '');
            $sheet->setCellValue('L'.$row, $datePaiement);
            $sheet->setCellValue('M'.$row, 'EDEN GROUP');
            $sheet->setCellValue('N'.$row, '');
            $sheet->setCellValue('O'.$row, '');
            $sheet->setCellValue('P'.$row, '');
            $row++;
        }

        // ═══ AUTO SIZE ═══
        foreach (range('A','P') as $col)
            $sheet->getColumnDimension($col)->setAutoSize(true);

        // ═══ TÉLÉCHARGEMENT ═══
        $fileName = 'clients_beneficiaires_'.now()->format('Y-m-d_H-i').'.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->stream(function() use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // ✅ EXCEL VIDE
    // ════════════════════════════════════════════════════════════════

    private function generateEmptyExcel()
    {
        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Clients & Bénéficiaires');

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
                  ->setFillType(Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('1E3A5F');
            $sheet->getStyle($col.'1')->getFont()->getColor()->setRGB('FFFFFF');
        }

        foreach (range('A','P') as $col)
            $sheet->getColumnDimension($col)->setAutoSize(true);

        $sheet->setCellValue('A2', 'Aucun élément trouvé');

        $fileName = 'clients_beneficiaires_vide_'.now()->format('Y-m-d').'.xlsx';
        $writer   = new Xlsx($spreadsheet);

        return response()->stream(function() use ($writer) {
            $writer->save('php://output');
        }, 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$fileName.'"',
            'Cache-Control' => 'max-age=0',
        ]);
    }

    // ════════════════════════════════════════════════════════════════
    // ✅ MAJ ÉTAPE
    // ════════════════════════════════════════════════════════════════

    public function majEtape(Request $request, $dossierId)
    {
        try {
            $request->validate([
                'etape'  => 'required|in:implantation_prevue,deja_implante,dossier_technique,morcellement',
                'date'   => 'required|date',
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

            $data = [];

            if ($active) {
                foreach ($etapes as $etapeNom => $ordre) {
                    if ($ordre <= $etapes[$etapeSelectionnee]) {
                        if (empty($dossier->{$champs[$etapeNom]})) {
                            $data[$champs[$etapeNom]] = ($etapeNom === $etapeSelectionnee)
                                ? $request->date
                                : now()->format('Y-m-d');
                        }
                    }
                }
                $data['etape_actuelle'] = $etapeSelectionnee;
            } else {
                foreach ($etapes as $etapeNom => $ordre) {
                    if ($ordre >= $etapes[$etapeSelectionnee]) {
                        $data[$champs[$etapeNom]] = null;
                    }
                }
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
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}