<?php
namespace App\Http\Controllers;

use App\Models\BonPaiement;
use App\Models\DossierClient;
use App\Models\User;
use App\Models\PaiementDossier;
use App\Models\PaiementTechnique;
use App\Models\PaiementMorcellement;
use App\Models\PaiementLogistique;
use App\Models\Visite; // ✅ Correction : use App\Models\Visite
use App\Models\Visiteur;
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

    // Formulaire de création - VÉRIFICATION OBLIGATOIRE
    public function creer(Request $request, DossierClient $dossier)
    {
        // ✅ 1. Vérifier si la référence est dans l'URL
        $reference = $request->query('reference');
        
        // ✅ 2. Si pas de référence dans l'URL, vérifier dans la session
        if (!$reference) {
            $reference = session('reference_utilisateur');
        }
        
        // ✅ 3. Si toujours pas de référence, rediriger vers l'index avec erreur
        if (!$reference) {
            return redirect()->route('bons.index', $dossier->id)
                             ->with('error', '🔑 Veuillez entrer votre référence pour créer un bon de paiement.');
        }

        // ✅ 4. Nettoyer la référence (trim seulement - garder la casse)
        $reference = trim($reference);

        // ✅ 5. Vérifier que la référence existe en base (SENSIBLE à la casse)
        $user = User::where('reference', $reference)->first();
        if (!$user) {
            // Nettoyer la session si la référence est invalide
            session()->forget('reference_utilisateur');
            
            return redirect()->route('bons.index', $dossier->id)
                             ->with('error', '❌ La référence "' . $reference . '" n\'existe pas. Vérifiez la casse (majuscules/minuscules).');
        }

        // ✅ 6. Stocker la référence dans la session pour les prochaines requêtes
        session(['reference_utilisateur' => $reference]);

        // ✅ 7. Charger les données du dossier
        $dossier->load([
            'client','grandSite',
            'paiements','paiementsTechniques','paiementsMorcellements',
        ]);

        // ✅ 8. Totaux déjà versés
        $totaux = $this->calculerTotaux($dossier);

        // ✅ 9. Passer l'utilisateur à la vue
        return view('admin.bons.creer', compact('dossier','totaux', 'user'));
    }

    // Enregistrer un bon
    public function store(Request $request, DossierClient $dossier)
    {
        // ✅ 1. Vérifier la référence (sécurité)
        $reference = $request->input('reference') ?? session('reference_utilisateur');
        
        if (!$reference) {
            return back()->withInput()->with('error', '🔑 Référence requise.');
        }
        
        // ✅ 2. Nettoyer la référence (trim seulement - garder la casse)
        $reference = trim($reference);
        
        // ✅ 3. Vérifier que la référence existe en base (SENSIBLE à la casse)
        $user = User::where('reference', $reference)->first();
        if (!$user) {
            session()->forget('reference_utilisateur');
            return back()->withInput()->with('error', '❌ Référence "' . $reference . '" invalide. Vérifiez la casse.');
        }

        // ✅ 4. Validation des données
        $request->validate([
            'date_bon'              => 'required|date',
            'versement_dossier'     => 'nullable|numeric|min:0',
            'versement_technique'   => 'nullable|numeric|min:0',
            'versement_logistique'  => 'nullable|numeric|min:0',
            'versement_morcellement'=> 'nullable|numeric|min:0',
            'afficher_reste'        => 'nullable|boolean',
            'notes'                 => 'nullable|string',
        ]);

        // ✅ 5. Charger les paiements existants
        $dossier->load([
            'paiements',
            'paiementsTechniques',
            'paiementsMorcellements',
        ]);

        // ✅ 6. Enregistrer les paiements liés
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
                'user_reference'    => $reference,
            ]);
        }
        if ($vTechnique > 0) {
            PaiementTechnique::create([
                'dossier_client_id' => $dossier->id,
                'montant'           => $vTechnique,
                'date_paiement'     => $request->date_bon,
                'note'              => 'Technique',
                'user_reference'    => $reference,
            ]);
        }
        if ($vLogistique > 0) {
            PaiementLogistique::create([
                'dossier_client_id' => $dossier->id,
                'montant'           => $vLogistique,
                'date_paiement'     => $request->date_bon,
                'note'              => 'Logistique',
                'user_reference'    => $reference,
            ]);
        }
        if ($vMorcellement > 0) {
            PaiementMorcellement::create([
                'dossier_client_id' => $dossier->id,
                'montant'           => $vMorcellement,
                'date_paiement'     => $request->date_bon,
                'note'              => 'Morcellement',
                'user_reference'    => $reference,
            ]);
        }

        // ✅ 7. Recharger pour avoir les nouveaux totaux cumulés
        $dossier->load([
            'paiements',
            'paiementsTechniques',
            'paiementsMorcellements',
        ]);

        // ✅ 8. Calculer les logistiques
        $totalLogistique = PaiementLogistique::where('dossier_client_id', $dossier->id)->sum('montant');

        // ✅ 9. Créer le bon
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
            'user_reference'           => $reference,
            'user_id'                  => $user->id,
        ]);

        // ✅ 10. CRÉER UNE VISITE AUTOMATIQUEMENT
        $this->creerVisiteDepuisBon($bon, $dossier, $user);

        // ✅ 11. Nettoyer la session après création
        session()->forget('reference_utilisateur');

        return redirect()->route('bons.show', $bon->id)
                         ->with('success', 'Bon créé — ' . $bon->numero_bon . ' (Référence: ' . $reference . ')');
    }

    /**
     * Créer une visite automatiquement à partir d'un bon de paiement
     */
    private function creerVisiteDepuisBon(BonPaiement $bon, DossierClient $dossier, User $user)
{
    $client = $dossier->client;
    
    // 1. Créer ou récupérer le visiteur
    $visiteur = Visiteur::firstOrCreate(
        ['nom' => $client->name],
        [
            'numero' => $client->phone,
            'type' => 'client',
        ]
    );

    // ✅ 2. Vérifier si une visite existe déjà pour ce visiteur à cette date
    $dateVisite = $bon->date_bon ?? now();
    $visiteExistante = Visite::where('visiteur_id', $visiteur->id)
                              ->whereDate('date_visite', $dateVisite)
                              ->first();

    if ($visiteExistante) {
        // ✅ Une visite existe déjà, on met à jour plutôt que de créer
        $detailsPaiements = [];
        if ($bon->versement_dossier > 0) {
            $detailsPaiements[] = '📁 Parcelle: ' . number_format($bon->versement_dossier, 0, ',', ' ') . ' FCFA';
        }
        if ($bon->versement_technique > 0) {
            $detailsPaiements[] = '🛠️ Technique: ' . number_format($bon->versement_technique, 0, ',', ' ') . ' FCFA';
        }
        if ($bon->versement_logistique > 0) {
            $detailsPaiements[] = '🚗 Logistique: ' . number_format($bon->versement_logistique, 0, ',', ' ') . ' FCFA';
        }
        if ($bon->versement_morcellement > 0) {
            $detailsPaiements[] = '✂️ Morcellement: ' . number_format($bon->versement_morcellement, 0, ',', ' ') . ' FCFA';
        }

        $nouvelleNote = '💳 Paiement effectué - Bon ' . $bon->numero_bon . "\n";
        $nouvelleNote .= implode("\n", $detailsPaiements);
        $nouvelleNote .= "\n📋 Dossier: " . ($dossier->nom_dossier ?? 'N/A');

        // ✅ Mettre à jour la visite existante
        $visiteExistante->update([
            'note' => $nouvelleNote,
            'bon_id' => $bon->id,
            'paiement_lie' => true,
        ]);

        return;
    }

    // 3. Construire la note
    $detailsPaiements = [];
    if ($bon->versement_dossier > 0) {
        $detailsPaiements[] = '📁 Parcelle: ' . number_format($bon->versement_dossier, 0, ',', ' ') . ' FCFA';
    }
    if ($bon->versement_technique > 0) {
        $detailsPaiements[] = '🛠️ Technique: ' . number_format($bon->versement_technique, 0, ',', ' ') . ' FCFA';
    }
    if ($bon->versement_logistique > 0) {
        $detailsPaiements[] = '🚗 Logistique: ' . number_format($bon->versement_logistique, 0, ',', ' ') . ' FCFA';
    }
    if ($bon->versement_morcellement > 0) {
        $detailsPaiements[] = '✂️ Morcellement: ' . number_format($bon->versement_morcellement, 0, ',', ' ') . ' FCFA';
    }

    $note = '💳 Paiement effectué - Bon ' . $bon->numero_bon . "\n";
    $note .= implode("\n", $detailsPaiements);
    $note .= "\n📋 Dossier: " . ($dossier->nom_dossier ?? 'N/A');

    // 4. Créer la visite
    Visite::create([
        'visiteur_id'       => $visiteur->id,
        'client_id'         => $client->id,
        'dossier_client_id' => $dossier->id,
        'grand_site_id'     => $dossier->grand_site_id,
        'site_id'           => null,
        'date_visite'       => $dateVisite,
        'heure_arrivee'     => now()->format('H:i:s'),
        'heure_depart'      => null,
        'type_personne'     => 'client',
        'note'              => $note,
        'paiement_lie'      => true,
        'bon_id'            => $bon->id,
    ]);
}

    // Détail d'un bon
    public function show(BonPaiement $bon)
    {
        $bon->load('dossier.client','dossier.grandSite', 'user');
        return view('admin.bons.show', compact('bon'));
    }

    // PDF
    public function pdf(BonPaiement $bon)
    {
        $bon->load('dossier.client','dossier.grandSite', 'user');
        $pdf = Pdf::loadView('admin.bons.pdf', compact('bon'))
                  ->setPaper([0, 0, 595, 420], 'portrait');
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
            'logistique'  => PaiementLogistique::where('dossier_client_id', $dossier->id)->sum('montant'),
            'morcellement'=> $dossier->paiementsMorcellements->sum('montant'),
        ];
    }
}