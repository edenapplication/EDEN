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
use App\Models\Beneficiaire;
use App\Models\BeneficiairePaiement;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

// ═══════════════════════════════════════════════════════════
// ENREGISTRER UN BON
// ═══════════════════════════════════════════════════════════
public function store(Request $request, DossierClient $dossier)
{
    // ✅ 1. Vérifier la référence
    $reference = $request->input('reference') ?? session('reference_utilisateur');

    if (!$reference) {
        return back()->withInput()->with('error', '🔑 Référence requise.');
    }

    $reference = trim($reference);

    $user = User::where('reference', $reference)->first();
    if (!$user) {
        session()->forget('reference_utilisateur');
        return back()->withInput()->with('error', '❌ Référence "' . $reference . '" invalide.');
    }

    // ✅ 2. Validation
    $request->validate([
        'date_bon'                     => 'required|date',
        'versement_dossier'            => 'nullable|numeric|min:0',
        'versement_morcellement'       => 'nullable|numeric|min:0',
        'versements'                   => 'nullable|array',
        'versements.*.type'            => 'required_with:versements|in:technique,logistique',
        'versements.*.montant'         => 'required_with:versements|numeric|min:1',
        'afficher_reste'               => 'nullable|boolean',
        'notes'                        => 'nullable|string',
    ]);

    $vDossier      = floatval($request->versement_dossier ?? 0);
    $vMorcellement = floatval($request->versement_morcellement ?? 0);
    $versements    = $request->input('versements', []);

    $vTechnique  = 0;
    $vLogistique = 0;
    foreach ($versements as $v) {
        if ($v['type'] === 'technique') $vTechnique += floatval($v['montant']);
        if ($v['type'] === 'logistique') $vLogistique += floatval($v['montant']);
    }

    DB::beginTransaction();

    try {
        // ✅ 3. Détection ANT
        $dateDuBon = \Carbon\Carbon::parse($request->date_bon)->format('Y-m-d');
        $dateAujourdhui = now()->format('Y-m-d');
        $prefixeANT = ($dateDuBon !== $dateAujourdhui) ? 'ANT-' : '';

        // ✅ 4. Créer le bon
        $dossier->refresh();
        $dossier->load(['paiements', 'paiementsTechniques', 'paiementsMorcellements']);
        $totalLogistique = PaiementLogistique::where('dossier_client_id', $dossier->id)->sum('montant');

        $bon = BonPaiement::create([
            'dossier_client_id'        => $dossier->id,
            'numero_bon'               => $prefixeANT . BonPaiement::genererNumero(),
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

        

        // ✅ 5. Parcelle
        if ($vDossier > 0) {
            PaiementDossier::create([
                'dossier_client_id' => $dossier->id,
                'montant'           => $vDossier,
                'date_paiement'     => $request->date_bon,
                'note'              => 'Bon ' . $bon->numero_bon,
                'user_reference'    => $reference,
            ]);
        }

        // ✅ 6. Morcellement
        if ($vMorcellement > 0) {
            PaiementMorcellement::create([
                'dossier_client_id' => $dossier->id,
                'montant'           => $vMorcellement,
                'date_paiement'     => $request->date_bon,
                'note'              => 'Bon ' . $bon->numero_bon,
                'user_reference'    => $reference,
            ]);
        }

        // ✅ 7. Versements par bénéficiaire
        foreach ($versements as $v) {
            $benefId   = $v['beneficiaire_id'] ?? null;
            $type      = $v['type'];
            $montant   = floatval($v['montant']);

            if ($montant <= 0) continue;

            // Créer le bénéficiaire si nouveau
            if ($benefId === '__NEW__') {
                $nomNew = trim($v['new_benef_nom'] ?? '');
                if (empty($nomNew)) {
                    throw new \Exception('⚠️ Nom du nouveau bénéficiaire obligatoire.');
                }

                $nouveau = Beneficiaire::create([
                    'dossier_client_id'    => $dossier->id,
                    'client_id'            => $dossier->client_id,
                    'nom'                  => $nomNew,
                    'telephone'            => $v['new_benef_tel'] ?? null,
                    'superficie_attribuee' => 0,
                ]);
                $benefId = $nouveau->id;
            } else {
                $ok = Beneficiaire::where('id', $benefId)
                    ->where('dossier_client_id', $dossier->id)
                    ->exists();
                if (!$ok) throw new \Exception('⚠️ Bénéficiaire invalide.');
            }

            // Créer le paiement
            if ($type === 'technique') {
                $paiement = PaiementTechnique::create([
                    'dossier_client_id' => $dossier->id,
                    'beneficiaire_id'   => $benefId,
                    'montant'           => $montant,
                    'date_paiement'     => $request->date_bon,
                    'note'              => 'Bon ' . $bon->numero_bon,
                    'user_reference'    => $reference,
                ]);
            } else {
                $paiement = PaiementLogistique::create([
                    'dossier_client_id' => $dossier->id,
                    'beneficiaire_id'   => $benefId,
                    'montant'           => $montant,
                    'date_paiement'     => $request->date_bon,
                    'note'              => 'Bon ' . $bon->numero_bon,
                    'user_reference'    => $reference,
                ]);
            }

            // ✅ Traçabilité AVEC bon_id
            BeneficiairePaiement::create([
                'beneficiaire_id'   => $benefId,
                'dossier_client_id' => $dossier->id,
                'paiement_type'     => $type,
                'paiement_id'       => $paiement->id,
                'montant'           => $montant,
                'date_paiement'     => $request->date_bon,
                'user_reference'    => $reference,
                'bon_id'            => $bon->id,   // ✅ LIEN DIRECT
            ]);
        }

        // ✅ 8. Mettre à jour les cumuls
        $dossier->refresh();
        $dossier->load(['paiements', 'paiementsTechniques', 'paiementsMorcellements']);
        $totalLogistique = PaiementLogistique::where('dossier_client_id', $dossier->id)->sum('montant');

        $bon->update([
            'total_dossier_cumul'      => $dossier->paiements->sum('montant'),
            'total_technique_cumul'    => $dossier->paiementsTechniques->sum('montant'),
            'total_logistique_cumul'   => $totalLogistique,
            'total_morcellement_cumul' => $dossier->paiementsMorcellements->sum('montant'),
        ]);

        // ✅ 12. Marquer le client comme antidaté si nécessaire
$dateDuBon = \Carbon\Carbon::parse($request->date_bon)->format('Y-m-d');
$dateAujourdhui = now()->format('Y-m-d');

if ($dateDuBon !== $dateAujourdhui && $dossier->client) {
    $dossier->client->update(['antidate' => true]);
}

        // ✅ 9. Créer la visite
        $this->creerVisiteDepuisBon($bon, $dossier, $user);

        DB::commit();

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Erreur création bon: ' . $e->getMessage());
        return back()->withInput()->with('error', '❌ ' . $e->getMessage());
    }

    session()->forget('reference_utilisateur');

    return redirect()->route('bons.show', $bon->id)
        ->with('success', 'Bon créé — ' . $bon->numero_bon);
}

    /**
     * Créer une visite automatiquement à partir d'un bon de paiement
     */
    private function creerVisiteDepuisBon(BonPaiement $bon, DossierClient $dossier, $user)
{
    $client = $dossier->client;
    if (!$client) return;

    $visiteur = Visiteur::firstOrCreate(
        ['nom' => $client->name],
        ['numero' => $client->phone, 'type' => 'client']
    );

    $dateVisite = $bon->date_bon ?? now();

    // ✅ Chercher une visite existante du même client le même jour
    $visiteExistante = Visite::where('visiteur_id', $visiteur->id)
        ->whereDate('date_visite', $dateVisite)
        ->first();

    // ✅ Construire la phrase naturelle
    $parties = [];
    if ($bon->versement_dossier > 0) {
        $parties[] = "la parcelle (" . number_format($bon->versement_dossier, 0, ',', ' ') . " FCFA)";
    }
    if ($bon->versement_technique > 0) {
        $parties[] = "le dossier technique (" . number_format($bon->versement_technique, 0, ',', ' ') . " FCFA)";
    }
    if ($bon->versement_logistique > 0) {
        $parties[] = "la logistique (" . number_format($bon->versement_logistique, 0, ',', ' ') . " FCFA)";
    }
    if ($bon->versement_morcellement > 0) {
        $parties[] = "le morcellement (" . number_format($bon->versement_morcellement, 0, ',', ' ') . " FCFA)";
    }

    $totalBon = $bon->versement_dossier
              + $bon->versement_technique
            + $bon->versement_logistique
            + $bon->versement_morcellement;

    $detailsPaiement = implode(', ', $parties);
    $dossierNom = $dossier->nom_dossier ?? 'N/A';

    $nouvellePhrase = "Le client a effectué un paiement de "
                    . number_format($totalBon, 0, ',', ' ') . " FCFA "
                    . "pour {$detailsPaiement} "
                    . "sur le dossier « {$dossierNom} » "
                    . "(Bon n° {$bon->numero_bon}).";

    if ($visiteExistante) {
        // ✅ FUSIONNER : ajouter la nouvelle phrase à la suite
        $ancienneNote = $visiteExistante->note ?? '';
        $nouvelleNote = $ancienneNote
            ? $ancienneNote . "\n" . $nouvellePhrase
            : $nouvellePhrase;

        $visiteExistante->update([
            'note'         => $nouvelleNote,
            'bon_id'       => $bon->id,
            'paiement_lie' => true,
            'heure_depart' => now()->format('H:i:s'),
        ]);
        return;
    }

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
        'note'              => $nouvellePhrase,
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
   // ═══════════════════════════════════════════════════════════════
// SUPPRIMER UN BON (avec suppression des paiements liés)
// ═══════════════════════════════════════════════════════════════
public function destroy(BonPaiement $bon)
{
    $dossierId = $bon->dossier_client_id;
    $dossier   = $bon->dossier;

    // ═══════════════════════════════════════════════════════════
    // 🗑️ 1. SUPPRIMER LES PAIEMENTS LIÉS À CE BON
    // ═══════════════════════════════════════════════════════════
    $reference = $bon->user_reference;
    $date      = $bon->date_bon;

    // ✅ Paiement Dossier (superficie)
    if ($bon->versement_dossier > 0) {
        PaiementDossier::where('dossier_client_id', $dossierId)
            ->whereDate('date_paiement', $date)
            ->where('montant', $bon->versement_dossier)
            ->when($reference, fn($q) => $q->where('user_reference', $reference))
            ->delete();
    }

    // ✅ Paiement Technique
    if ($bon->versement_technique > 0) {
        PaiementTechnique::where('dossier_client_id', $dossierId)
            ->whereDate('date_paiement', $date)
            ->where('montant', $bon->versement_technique)
            ->when($reference, fn($q) => $q->where('user_reference', $reference))
            ->delete();
    }

    // ✅ Paiement Logistique
    if ($bon->versement_logistique > 0) {
        PaiementLogistique::where('dossier_client_id', $dossierId)
            ->whereDate('date_paiement', $date)
            ->where('montant', $bon->versement_logistique)
            ->when($reference, fn($q) => $q->where('user_reference', $reference))
            ->delete();
    }

    // ✅ Paiement Morcellement
    if ($bon->versement_morcellement > 0) {
        PaiementMorcellement::where('dossier_client_id', $dossierId)
            ->whereDate('date_paiement', $date)
            ->where('montant', $bon->versement_morcellement)
            ->when($reference, fn($q) => $q->where('user_reference', $reference))
            ->delete();
    }

    // ═══════════════════════════════════════════════════════════
    // 🗑️ 2. SUPPRIMER LA VISITE LIÉE (si elle existe)
    // ═══════════════════════════════════════════════════════════
    Visite::where('bon_id', $bon->id)->delete();

    // ═══════════════════════════════════════════════════════════
    // 🗑️ 3. SUPPRIMER LE BON
    // ═══════════════════════════════════════════════════════════
    $bon->delete();

    return redirect()->route('bons.index', $dossierId)
        ->with('success', '✅ Bon supprimé et paiements associés retirés.');
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