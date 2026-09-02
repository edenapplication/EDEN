<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\DossierClient;
use App\Models\PaiementDossier;
use App\Models\Commercial;
use App\Models\Conducteur;
use App\Models\Facilitateur;
use App\Models\AgentCommercial;
use App\Models\GrandSite;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use PDF;

class SuiviClientController extends Controller
{
    public function create(Request $request)
    {
        $options  = $this->options();
        $clientId = $request->client_id;
        $clientPre= $clientId ? Client::find($clientId) : null;
        return view('admin.suivi_client.create', compact('options','clientPre'));
    }
    
    private function options(): array
    {
        return [
            'commerciaux'  => Commercial::orderBy('name')->get(),
            'conducteurs'  => Conducteur::orderBy('nom')->get(),
            'facilitateurs'=> Facilitateur::orderBy('nom')->get(),
            'agents'       => AgentCommercial::orderBy('nom')->get(),
            'grandsites'   => GrandSite::orderBy('nom')->get(),
            'directions'   => [
                'baffoussam'         => 'Baffoussam',
                'bagante'            => 'Bagante',
                'dschang'            => 'Dschang',
                'direction_generale' => 'Direction Générale',
            ],
        ];
    }

    // ════════════════════════════════════════════════════════════════
    // ✅ INDEX AVEC FILTRES AMÉLIORÉS
    // ════════════════════════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = Client::with([
            'dossiers.grandSite',
            'dossiers.paiements',
            'dossiers.paiementsTechniques',
            'dossiers.paiementsMorcellements',
            'dossiers.affectations.grandSite',
            'dossiers.affectations.bloc',
            'dossiers.affectations.lot',
        ])->orderByDesc('created_at');

        // 🔍 Recherche
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function($qry) use ($q) {
                $qry->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('phone', 'LIKE', "%{$q}%")
                    ->orWhereHas('dossiers', function($dq) use ($q) {
                        $dq->where('nom_dossier', 'LIKE', "%{$q}%");
                    });
            });
        }

        // 📅 Dates
        if ($request->filled('du'))
            $query->whereDate('created_at', '>=', $request->du);
        if ($request->filled('au'))
            $query->whereDate('created_at', '<=', $request->au);

        // 🏢 Grand Site
        if ($request->filled('grand_site_id')) {
            $query->whereHas('dossiers', fn($q) => 
                $q->where('grand_site_id', $request->grand_site_id));
        }

        // 🆕 Statut nouveau/ancien
        if ($request->filled('status')) {
            if ($request->status == 'new') {
                $query->where('is_new', true);
            } elseif ($request->status == 'old') {
                $query->where('is_new', false);
            }
        }

        // ✅ Filtre par paiement technique soldé
        if ($request->filled('technique_solde')) {
            if ($request->technique_solde == 'solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_technique, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques WHERE dossier_client_id = dossiers_clients.id) >= prix_technique');
                });
            } elseif ($request->technique_solde == 'non_solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_technique, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques WHERE dossier_client_id = dossiers_clients.id) < prix_technique');
                });
            }
        }

        // ✅ Filtre par paiement morcellement soldé
        if ($request->filled('morcellement_solde')) {
            if ($request->morcellement_solde == 'solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_morcellement, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_morcellements WHERE dossier_client_id = dossiers_clients.id) >= prix_morcellement');
                });
            } elseif ($request->morcellement_solde == 'non_solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_morcellement, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_morcellements WHERE dossier_client_id = dossiers_clients.id) < prix_morcellement');
                });
            }
        }

        // ✅ Filtre par paiement dossier (superficie) soldé - CORRIGÉ
        if ($request->filled('dossier_solde')) {
            if ($request->dossier_solde == 'solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_superficie, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_dossier WHERE dossier_client_id = dossiers_clients.id) >= prix_superficie');
                });
            } elseif ($request->dossier_solde == 'non_solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_superficie, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_dossier WHERE dossier_client_id = dossiers_clients.id) < prix_superficie');
                });
            }
        }

        // ✅ Filtre par paiement logistique soldé
        if ($request->filled('logistique_solde')) {
            if ($request->logistique_solde == 'solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_logistique, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques WHERE dossier_client_id = dossiers_clients.id) >= prix_logistique');
                });
            } elseif ($request->logistique_solde == 'non_solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_logistique, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques WHERE dossier_client_id = dossiers_clients.id) < prix_logistique');
                });
            }
        }

        $clients = $query->get();
        $grandSites = GrandSite::orderBy('nom')->get();

        return view('admin.suivi_client.index', compact('clients', 'grandSites'));
    }

    // ════════════════════════════════════════════════════════════════
    // ✅ EXPORT PDF
    // ════════════════════════════════════════════════════════════════

    public function exportPdf(Request $request)
    {
        $query = Client::with([
            'dossiers.grandSite',
            'dossiers.affectations.grandSite',
            'dossiers.affectations.bloc',
            'dossiers.affectations.lot',
            'dossiers.paiementsTechniques',
            'dossiers.paiementsMorcellements',
            'dossiers.paiements',
            'dossiers.paiementsLogistiques',
        ]);

        // Appliquer les filtres
        if ($request->filled('q')) {
            $q = trim($request->q);
            $query->where(function($qry) use ($q) {
                $qry->where('name', 'LIKE', "%{$q}%")
                    ->orWhere('phone', 'LIKE', "%{$q}%");
            });
        }
        if ($request->filled('du'))
            $query->whereDate('created_at', '>=', $request->du);
        if ($request->filled('au'))
            $query->whereDate('created_at', '<=', $request->au);
        if ($request->filled('grand_site_id')) {
            $query->whereHas('dossiers', fn($q) => 
                $q->where('grand_site_id', $request->grand_site_id));
        }
        if ($request->filled('status')) {
            if ($request->status == 'new') {
                $query->where('is_new', true);
            } elseif ($request->status == 'old') {
                $query->where('is_new', false);
            }
        }
        if ($request->filled('technique_solde')) {
            if ($request->technique_solde == 'solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_technique, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques WHERE dossier_client_id = dossiers_clients.id) >= prix_technique');
                });
            } elseif ($request->technique_solde == 'non_solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_technique, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_techniques WHERE dossier_client_id = dossiers_clients.id) < prix_technique');
                });
            }
        }
        if ($request->filled('morcellement_solde')) {
            if ($request->morcellement_solde == 'solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_morcellement, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_morcellements WHERE dossier_client_id = dossiers_clients.id) >= prix_morcellement');
                });
            } elseif ($request->morcellement_solde == 'non_solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_morcellement, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_morcellements WHERE dossier_client_id = dossiers_clients.id) < prix_morcellement');
                });
            }
        }
        if ($request->filled('dossier_solde')) {
            if ($request->dossier_solde == 'solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_superficie, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_dossier WHERE dossier_client_id = dossiers_clients.id) >= prix_superficie');
                });
            } elseif ($request->dossier_solde == 'non_solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_superficie, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_dossier WHERE dossier_client_id = dossiers_clients.id) < prix_superficie');
                });
            }
        }
        if ($request->filled('logistique_solde')) {
            if ($request->logistique_solde == 'solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_logistique, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques WHERE dossier_client_id = dossiers_clients.id) >= prix_logistique');
                });
            } elseif ($request->logistique_solde == 'non_solde') {
                $query->whereHas('dossiers', function($q) {
                    $q->whereRaw('COALESCE(prix_logistique, 0) > 0')
                      ->whereRaw('(SELECT COALESCE(SUM(montant), 0) FROM paiements_logistiques WHERE dossier_client_id = dossiers_clients.id) < prix_logistique');
                });
            }
        }

        $clients = $query->get();
        
        $data = [
            'clients' => $clients,
            'date_export' => now()->format('d/m/Y H:i'),
            'total_clients' => $clients->count(),
        ];

        $pdf = PDF::loadView('admin.suivi_client.export_pdf', $data);
        $pdf->setPaper('A4', 'landscape');
        
        return $pdf->download('clients_export_' . now()->format('Y-m-d') . '.pdf');
    }

    public function store(Request $request)
{
    try {
        $request->validate([
            'name'               => 'required|string|max:255',
            'phone'              => 'required|string',
            'nom_dossier'        => 'required|string|max:255',
            'commercial_id'      => 'nullable|exists:commerciaux,id',
            'conducteur_id'      => 'nullable|exists:conducteurs,id',
            'facilitateur_id'    => 'nullable|exists:facilitateurs,id',
            'agent_commercial_id'=> 'nullable|exists:agents_commerciaux,id',
            'grand_site_id'      => 'nullable|exists:grand_sites,id',
            'direction'          => 'nullable|string',
            'superficie_voulue'  => 'nullable|numeric',
            'prix_superficie'    => 'required|numeric|min:0',
            'prix_technique'     => 'required|numeric|min:0',
            'prix_logistique'    => 'required|numeric|min:0',
            'prix_morcellement'  => 'nullable|numeric|min:0',
            'cni_images'         => 'nullable|array',
            'cni_images.*'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $client = Client::firstOrCreate(
            ['phone' => $request->phone],
            ['name'  => $request->name, 'is_new' => true]
        );

        $agentId = $request->agent_commercial_id;
        if (!$agentId && $request->agent_nom) {
            $a = AgentCommercial::create(['nom' => $request->agent_nom, 'numero' => $request->agent_numero]);
            $agentId = $a->id;
        }

        $conducteurId = $request->conducteur_id;
        if (!$conducteurId && $request->conducteur_nom) {
            $c = Conducteur::create(['nom' => $request->conducteur_nom, 'numero' => $request->conducteur_numero]);
            $conducteurId = $c->id;
        }

        $facilitateurId = $request->facilitateur_id;
        if (!$facilitateurId && $request->facilitateur_nom) {
            $f = Facilitateur::create(['nom' => $request->facilitateur_nom, 'numero' => $request->facilitateur_numero]);
            $facilitateurId = $f->id;
        }

        $cniImages = [];
        if ($request->hasFile('cni_images')) {
            foreach ($request->file('cni_images') as $file) {
                if ($file && $file->isValid()) {
                    $path = $file->store('cni', 'public');
                    if ($path) {
                        $cniImages[] = $path;
                    }
                }
            }
        }

        DossierClient::create([
            'client_id'          => $client->id,
            'nom_dossier'        => $request->nom_dossier,
            'commercial_id'      => $request->commercial_id,
            'conducteur_id'      => $conducteurId,
            'facilitateur_id'    => $facilitateurId,
            'agent_commercial_id'=> $agentId,
            'grand_site_id'      => $request->grand_site_id,
            'direction'          => $request->direction,
            'superficie_voulue'  => $request->superficie_voulue,
            'prix_superficie'    => $request->prix_superficie,
            'prix_technique'     => $request->prix_technique,
            'prix_logistique'    => $request->prix_logistique,
            'prix_morcellement'  => $request->prix_morcellement ?? 0,
            'cni_images'         => $cniImages,
        ]);

        return redirect()->route('suivi-client.show', $client->id)
                         ->with('success', 'Client et dossier créés avec succès');

    } catch (\Illuminate\Validation\ValidationException $e) {
        // ✅ CORRECTION : Utiliser withErrors au lieu de implode()
        return back()->withErrors($e->errors())->withInput();
        
    } catch (\Exception $e) {
        Log::error('Erreur création dossier: ' . $e->getMessage());
        return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
    }
}

   // App\Http\Controllers\SuiviClientController.php

public function show($id)
{
    try {
        $client = Client::with([
            'dossiers.commercial',
            'dossiers.conducteur',
            'dossiers.facilitateur',
            'dossiers.agentCommercial',
            'dossiers.grandSite',
            'dossiers.paiements',
            'dossiers.paiementsTechniques',
            'dossiers.paiementsMorcellements',
            'dossiers.paiementsLogistiques',
            'dossiers.bons',
            'dossiers.affectations.grandSite',
            'dossiers.affectations.bloc',
            'dossiers.affectations.lot',
            'lots.tf.site.grandSite',
            'lots.dossier',
            'visites.visiteur',
            'visites.grandSite',
            'visites.site',
        ])->findOrFail($id);

        // ✅ Récupérer le premier dossier OU null si aucun
        $dossier = $client->dossiers->first();
        
        // ✅ Si pas de dossier, on passe une collection vide
        $affectations = $dossier ? $dossier->affectations : collect();

        // ✅ Vérifier les CNI (si dossier existe)
        if ($dossier && $dossier->cni_images) {
            $cnisValides = [];
            foreach ($dossier->cni_images as $img) {
                if (file_exists(storage_path('app/public/' . $img))) {
                    $cnisValides[] = $img;
                }
            }
            $dossier->cni_images = $cnisValides;
        }

        return view('admin.suivi_client.show', compact('client', 'affectations', 'dossier'));

    } catch (\Exception $e) {
        Log::error('Erreur show client ' . $id . ': ' . $e->getMessage());
        return back()->with('error', 'Erreur lors du chargement du client: ' . $e->getMessage());
    }
}

    public function edit(Request $request, $id)
    {
        $client    = Client::with('dossiers')->findOrFail($id);
        $options   = $this->options();
        $clientPre = null;
        $dossierId = $request->dossier_id ?? $client->dossiers->first()?->id;
        $dossier   = $client->dossiers->firstWhere('id', $dossierId)
                     ?? $client->dossiers->first();

        return view('admin.suivi_client.edit', compact('client','options','clientPre','dossier'));
    }

    public function update(Request $request, $id)
    {
        try {
            $client = Client::findOrFail($id);

            $request->validate([
                'name'               => 'nullable|string|max:255',
                'phone'              => 'nullable|string',
                'nom_dossier'        => 'required|string|max:255',
                'commercial_id'      => 'nullable|exists:commerciaux,id',
                'conducteur_id'      => 'nullable|exists:conducteurs,id',
                'facilitateur_id'    => 'nullable|exists:facilitateurs,id',
                'agent_commercial_id'=> 'nullable|exists:agents_commerciaux,id',
                'grand_site_id'      => 'nullable|exists:grand_sites,id',
                'direction'          => 'nullable|string',
                'superficie_voulue'  => 'nullable|numeric',
                'prix_superficie'    => 'required|numeric|min:0',
                'prix_technique'     => 'required|numeric|min:0',
                'prix_logistique'    => 'required|numeric|min:0',
                'prix_morcellement'  => 'nullable|numeric|min:0',
                'cni_images.*'       => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:10240',
            ]);

            $updateData = [];
            if ($request->filled('phone')) $updateData['phone'] = $request->phone;
            if ($request->filled('name'))  $updateData['name']  = $request->name;
            if (!empty($updateData)) $client->update($updateData);

            $agentId = $request->agent_commercial_id;
            if (!$agentId && $request->agent_nom) {
                $a = AgentCommercial::create(['nom' => $request->agent_nom, 'numero' => $request->agent_numero]);
                $agentId = $a->id;
            }

            $conducteurId = $request->conducteur_id;
            if (!$conducteurId && $request->conducteur_nom) {
                $c = Conducteur::create(['nom' => $request->conducteur_nom, 'numero' => $request->conducteur_numero]);
                $conducteurId = $c->id;
            }

            $facilitateurId = $request->facilitateur_id;
            if (!$facilitateurId && $request->facilitateur_nom) {
                $f = Facilitateur::create(['nom' => $request->facilitateur_nom, 'numero' => $request->facilitateur_numero]);
                $facilitateurId = $f->id;
            }

            $dossierId = $request->dossier_id ?? $client->dossiers->first()?->id;
            if ($dossierId) {
                $dossier = DossierClient::where('id', $dossierId)
                    ->where('client_id', $client->id)
                    ->firstOrFail();

                $cniImages = $dossier->cni_images ?? [];
                
                if ($request->hasFile('cni_images')) {
                    if (!empty($cniImages)) {
                        foreach ($cniImages as $oldImage) {
                            $oldPath = storage_path('app/public/' . $oldImage);
                            if (file_exists($oldPath)) {
                                unlink($oldPath);
                            }
                        }
                    }
                    
                    $cniImages = [];
                    foreach ($request->file('cni_images') as $file) {
                        $path = $file->store('cni', 'public');
                        if ($path) {
                            $cniImages[] = $path;
                        }
                    }
                }

                $dossier->update([
                    'nom_dossier'        => $request->nom_dossier,
                    'commercial_id'      => $request->commercial_id,
                    'conducteur_id'      => $conducteurId,
                    'facilitateur_id'    => $facilitateurId,
                    'agent_commercial_id'=> $agentId,
                    'grand_site_id'      => $request->grand_site_id,
                    'direction'          => $request->direction,
                    'superficie_voulue'  => $request->superficie_voulue,
                    'prix_superficie'    => $request->prix_superficie,
                    'prix_technique'     => $request->prix_technique,
                    'prix_logistique'    => $request->prix_logistique,
                    'prix_morcellement'  => $request->prix_morcellement ?? 0,
                    'cni_images'         => $cniImages,
                ]);
            }

            return redirect()->route('suivi-client.show', $client->id)
                             ->with('success', 'Mis à jour avec succès');

        } catch (\Exception $e) {
            Log::error('Erreur mise à jour dossier: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function modifierNom(Request $request, $clientId)
    {
        $request->validate(['nom' => 'required|string|max:150']);
        $client = Client::findOrFail($clientId);
        $client->update(['name' => $request->nom]);
        return response()->json(['success' => true, 'nom' => $client->name]);
    }

    public function destroy($id)
    {
        $client = Client::findOrFail($id);
        $client->dossiers()->delete();
        $client->delete();
        return redirect()->route('suivi-client.index')->with('success', 'Client supprimé');
    }

    public function destroyDossier(DossierClient $dossier)
    {
        $client = $dossier->client;
        
        if ($dossier->cni_images) {
            foreach ($dossier->cni_images as $image) {
                $path = storage_path('app/public/' . $image);
                if (file_exists($path)) {
                    unlink($path);
                }
            }
        }
        
        $dossier->paiements()->delete();
        $dossier->paiementsTechniques()->delete();
        $dossier->paiementsMorcellements()->delete();
        $dossier->delete();

        return redirect()
            ->route('suivi-client.show', $client->id)
            ->with('success', 'Dossier supprimé avec succès.');
    }

    public function dossiers($id)
    {
        $client = Client::with('dossiers')->findOrFail($id);
        return response()->json($client->dossiers);
    }

    // ============================================================
    // ✅ MÉTHODE POUR LES ÉTAPES
    // ============================================================
    public function majEtape(Request $request, DossierClient $dossier)
    {
        try {
            $etape = $request->input('etape');
            $date = $request->input('date');
            $active = $request->input('active');

            if (!$etape || !in_array($etape, ['implantation_prevue', 'deja_implante', 'dossier_technique', 'morcellement'])) {
                return response()->json(['success' => false, 'message' => 'Étape invalide'], 400);
            }

            $etapesConfig = DossierClient::etapesConfig();
            $champDate = $etapesConfig[$etape]['champ'];

            if ($active && !$date) {
                return response()->json(['success' => false, 'message' => 'Une date est requise pour activer une étape'], 400);
            }

            if ($active && $date) {
                $dossier->$champDate = $date;
            } else {
                $dossier->$champDate = null;
            }

            $etapesOrdre = DossierClient::etapesOrdre();
            $ordreEtape = $etapesOrdre[$etape];

            if ($active && $date) {
                $dossier->etape_actuelle = $etape;
            } else {
                $nouvelleEtape = null;
                foreach ($etapesOrdre as $cle => $ordre) {
                    if ($ordre < $ordreEtape) {
                        $champ = $etapesConfig[$cle]['champ'];
                        if ($dossier->$champ) {
                            $nouvelleEtape = $cle;
                        }
                    }
                }
                $dossier->etape_actuelle = $nouvelleEtape;
            }

            $dossier->save();

            $dates = [];
            foreach ($etapesConfig as $cle => $cfg) {
                $champ = $cfg['champ'];
                $dates[$cle] = $dossier->$champ ? $dossier->$champ->format('d/m/Y') : null;
            }

            return response()->json([
                'success' => true,
                'etape_actuelle' => $dossier->etape_actuelle,
                'dates' => $dates,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur majEtape: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ TOGGLE NEW STATUS
    // ============================================================
    public function toggleNew($clientId)
    {
        try {
            $client = Client::findOrFail($clientId);
            $isNew = $client->toggleNew();

            return response()->json([
                'success' => true,
                'is_new' => $isNew,
                'message' => $isNew ? 'Client marqué comme nouveau' : 'Statut "nouveau" retiré',
                'badge' => $client->is_new_badge,
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur toggleNew: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage(),
            ], 500);
        }
    }

    // ============================================================
    // ✅ ACTIONS GROUPÉES
    // ============================================================
    public function actionsGroup(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            $action = $request->input('action');
            
            if (empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun client sélectionné'
                ], 400);
            }

            $clients = Client::whereIn('id', $ids)->get();
            
            switch ($action) {
                case 'toggle_new':
                    foreach ($clients as $client) {
                        $client->toggleNew();
                    }
                    $message = count($ids) . ' client(s) marqué(s) comme ' . ($clients->first()->is_new ? 'nouveau' : 'ancien');
                    break;
                    
                case 'mark_as_new':
                    foreach ($clients as $client) {
                        $client->markAsNew();
                    }
                    $message = count($ids) . ' client(s) marqué(s) comme nouveau(x)';
                    break;
                    
                case 'mark_as_old':
                    foreach ($clients as $client) {
                        $client->markAsConfirmed();
                    }
                    $message = count($ids) . ' client(s) marqué(s) comme ancien(s)';
                    break;
                    
                case 'delete':
                    foreach ($clients as $client) {
                        $client->dossiers()->delete();
                        $client->delete();
                    }
                    $message = count($ids) . ' client(s) supprimé(s)';
                    break;
                    
                case 'export_whatsapp':
                    return $this->exportWhatsApp($clients);
                    
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Action non reconnue'
                    ], 400);
            }
            
            return response()->json([
                'success' => true,
                'message' => $message
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur actions groupées: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // ✅ EXPORT WHATSAPP
    // ============================================================
    public function exportWhatsApp($clients)
    {
        try {
            $whatsappNumber = '237653350503';

            $message = "📢 *EDEN GROUP - Nouveaux clients*\n\n";
            $message .= "Voici la liste des clients récents :\n\n";
            
            foreach ($clients as $index => $client) {
                $message .= ($index + 1) . ". *" . $client->name . "*\n";
                $message .= "   📞 Tél : " . ($client->phone ?? 'Non renseigné') . "\n";
                if ($client->is_new) {
                    $message .= "   🆕 *Nouveau client*\n";
                }
                if ($client->dossiers->count() > 0) {
                    $message .= "   📂 " . $client->dossiers->count() . " dossier(s)\n";
                }
                $message .= "\n";
            }
            
            $message .= "\n---\n";
            $message .= "📅 Envoyé le " . now()->format('d/m/Y à H:i');
            $message .= "\n🔗 EDEN GROUP - Suivi Client";
            
            $encodedMessage = urlencode($message);
            $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$encodedMessage}";
            
            return response()->json([
                'success' => true,
                'whatsapp_url' => $whatsappUrl,
                'message' => $message,
                'count' => $clients->count()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Erreur export WhatsApp: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur : ' . $e->getMessage()
            ], 500);
        }
    }
}