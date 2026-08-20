<?php
namespace App\Http\Controllers;

use App\Models\BonPaiement;
use App\Models\DossierClient;
use App\Models\PaiementDossier;
use App\Models\PaiementTechnique;
use App\Models\PaiementMorcellement;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class BonPaiementController extends Controller
{
    // Liste des bons d'un dossier
    public function index(DossierClient $dossier)
    {
        $dossier->load([
            'client','grandSite','bons',
            'paiements','paiementsTechniques','paiementsMorcellements',
        ]);
        return view('admin.bons.index', compact('dossier'));
    }

    // Formulaire de création
    public function creer(DossierClient $dossier)
    {
        $dossier->load([
            'client','grandSite',
            'paiements','paiementsTechniques','paiementsMorcellements',
        ]);

        // Totaux déjà versés
        $totaux = $this->calculerTotaux($dossier);

        return view('admin.bons.creer', compact('dossier','totaux'));
    }

    // Enregistrer un bon
    public function store(Request $request, DossierClient $dossier)
    {
        $request->validate([
            'date_bon'              => 'required|date',
            'versement_dossier'     => 'nullable|numeric|min:0',
            'versement_technique'   => 'nullable|numeric|min:0',
            'versement_logistique'  => 'nullable|numeric|min:0',
            'versement_morcellement'=> 'nullable|numeric|min:0',
            'afficher_reste'        => 'nullable|boolean',
            'notes'                 => 'nullable|string',
        ]);

        $dossier->load([
            'paiements',
            'paiementsTechniques',
            'paiementsMorcellements',
        ]);

        // Enregistrer les paiements liés
        $vDossier     = floatval($request->versement_dossier     ?? 0);
        $vTechnique   = floatval($request->versement_technique   ?? 0);
        $vLogistique  = floatval($request->versement_logistique  ?? 0);
        $vMorcellement= floatval($request->versement_morcellement?? 0);

        if ($vDossier > 0) {
            PaiementDossier::create([
                'dossier_client_id' => $dossier->id,
                'montant'           => $vDossier,
                'date_paiement'     => $request->date_bon,
                'note'              => 'Bon ' . BonPaiement::genererNumero(),
            ]);
        }
        if ($vTechnique > 0) {
            PaiementTechnique::create([
                'dossier_client_id' => $dossier->id,
                'montant'           => $vTechnique,
                'date_paiement'     => $request->date_bon,
                'note'              => 'Technique',
            ]);
        }
        if ($vLogistique > 0) {
            \App\Models\PaiementLogistique::create([
                'dossier_client_id' => $dossier->id,
                'montant'           => $vLogistique,
                'date_paiement'     => $request->date_bon,
                'note'              => 'Logistique',
            ]);
        }
        if ($vMorcellement > 0) {
            PaiementMorcellement::create([
                'dossier_client_id' => $dossier->id,
                'montant'           => $vMorcellement,
                'date_paiement'     => $request->date_bon,
                'note'              => 'Morcellement',
            ]);
        }

        // Recharger pour avoir les nouveaux totaux cumulés
        $dossier->load([
            'paiements',
            'paiementsTechniques',
            'paiementsMorcellements',
        ]);

        // Calculer les logistiques
        $totalLogistique = \App\Models\PaiementLogistique::where('dossier_client_id', $dossier->id)->sum('montant');

        $bon = BonPaiement::create([
            'dossier_client_id'        => $dossier->id,
            'numero_bon'               => BonPaiement::genererNumero(),
            'date_bon'                 => $request->date_bon,
            'versement_dossier'        => $vDossier,
            'versement_technique'      => $vTechnique,
            'versement_logistique'     => $vLogistique,
            'versement_morcellement'   => $vMorcellement,
            'total_dossier_cumul'      => $dossier->paiements->sum('montant'),
            'total_technique_cumul'    => $dossier->paiementsTechniques->sum('montant'),
            'total_logistique_cumul'   => $totalLogistique,
            'total_morcellement_cumul' => $dossier->paiementsMorcellements->sum('montant'),
            'afficher_reste'           => $request->boolean('afficher_reste', true),
            'notes'                    => $request->notes,
        ]);

        return redirect()->route('bons.show', $bon->id)
                         ->with('success', 'Bon créé — ' . $bon->numero_bon);
    }

    // Détail d'un bon
    public function show(BonPaiement $bon)
    {
        $bon->load('dossier.client','dossier.grandSite');
        return view('admin.bons.show', compact('bon'));
    }

    // PDF
    public function pdf(BonPaiement $bon)
    {
        $bon->load('dossier.client','dossier.grandSite');
        $pdf = Pdf::loadView('admin.bons.pdf', compact('bon'))
                  ->setPaper([0, 0, 595, 420], 'portrait'); // A5 paysage ≈ reçu
        $nomFichier = 'bon_' . $bon->numero_bon . '_' . $bon->date_bon->format('Y-m-d') . '.pdf';
        return $pdf->download($nomFichier);
    }

    // Supprimer un bon
    public function destroy(BonPaiement $bon)
    {
        $dossierId = $bon->dossier_client_id;
        $bon->delete();
        return redirect()->route('bons.index', $dossierId)
                         ->with('success', 'Bon supprimé.');
    }

    // Toggle afficher reste
    public function toggleReste(BonPaiement $bon)
    {
        $bon->update(['afficher_reste' => !$bon->afficher_reste]);
        return response()->json(['success' => true, 'afficher_reste' => $bon->afficher_reste]);
    }

    private function calculerTotaux(DossierClient $dossier): array
    {
        return [
            'dossier'     => $dossier->paiements->sum('montant'),
            'technique'   => $dossier->paiementsTechniques->sum('montant'),
            'logistique'  => \App\Models\PaiementLogistique::where('dossier_client_id', $dossier->id)->sum('montant'),
            'morcellement'=> $dossier->paiementsMorcellements->sum('montant'),
        ];
    }
}