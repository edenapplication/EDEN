<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Admin\GrandSiteController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\TfController;
use App\Http\Controllers\LotController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\CommercialController;
use App\Http\Controllers\PaiementDossierController;
use App\Http\Controllers\DossierTechniqueController; // ✅ ICI
use App\Http\Controllers\SuiviClientController;
use App\Http\Controllers\AgentCommercialController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\VisiteController;
use App\Http\Controllers\ZoneGroupeController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\PaiementTechniqueController;
use App\Http\Controllers\PaiementMorcellementController;
use App\Http\Controllers\DossierClientController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\BonPaiementController;

use App\Http\Controllers\RH\DashboardRHController;
use App\Http\Controllers\RH\EmployeController;
use App\Http\Controllers\RH\PaieController;
use App\Http\Controllers\RH\AbsenceController;
use App\Http\Controllers\RH\PretController;
use App\Http\Controllers\RH\SanctionController;
use App\Http\Controllers\RH\RetardController;
use App\Http\Controllers\RH\DirectionController;

use App\Http\Controllers\Feb\DestinataireController;
use App\Models\User;
use Illuminate\Http\Request;


Route::post('/deploy', function () {
    exec('cd /var/www/html && git pull origin main');
    exec('cd /var/www/html && php artisan optimize:clear');

    return response('DEPLOY OK', 200);
});

// ── AUTHENTIFICATION ─────────────────────────────────────────
Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// ── PAGE D'ACCUEIL (modules) ─────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home')->middleware('auth');

// ── ADMIN ─────────────────────────────────────────────────────
Route::prefix('admin')->middleware(['auth', 'check.role:admin,rh,commercial'])->group(function () {

   Route::post('/verifier-reference', function (Request $request) {

    $reference = trim($request->input('reference'));

    $existe = \App\Models\User::where('reference', $reference)->exists();

    return response()->json([
        'existe' => $existe,
        'reference' => $reference
    ]);
});

 Route::post('/suivi-client/toggle-new/{client}', [SuiviClientController::class, 'toggleNew'])
        ->name('suivi-client.toggle-new');

 Route::post('/suivi-client/actions-group', [SuiviClientController::class, 'actionsGroup'])
        ->name('suivi-client.actions-group');

 Route::get('/suivi-client/export-pdf', [SuiviClientController::class, 'exportPdf'])
        ->name('suivi-client.export-pdf');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->middleware('check.role:admin')
        ->name('admin.dashboard');

    // GRAND SITES — admin seulement
    Route::middleware('check.role:admin')->group(function () {
        Route::get('/grand-sites',           [GrandSiteController::class, 'index'])->name('grand-sites.index');
        Route::get('/grand-sites/create',    [GrandSiteController::class, 'create'])->name('grand-sites.create');
        Route::post('/grand-sites',          [GrandSiteController::class, 'store'])->name('grand-sites.store');
        Route::get('/grand-sites/{id}/edit', [GrandSiteController::class, 'edit'])->name('grand-sites.edit');
        Route::put('/grand-sites/{id}',      [GrandSiteController::class, 'update'])->name('grand-sites.update');
        Route::delete('/grand-sites/{id}',   [GrandSiteController::class, 'destroy'])->name('grand-sites.destroy');

        Route::get('/grand-sites/{grand_site_id}/sites',        [SiteController::class, 'index'])->name('sites.index');
        Route::get('/grand-sites/{grand_site_id}/sites/create', [SiteController::class, 'create'])->name('sites.create');
        Route::post('/grand-sites/{grand_site_id}/sites',       [SiteController::class, 'store'])->name('sites.store');
        Route::get('/sites/{site}/edit',                        [SiteController::class, 'edit'])->name('sites.edit');
        Route::put('/sites/{site}',                             [SiteController::class, 'update'])->name('sites.update');
        Route::delete('/sites/{site}',                          [SiteController::class, 'destroy'])->name('sites.destroy');
    });

    Route::get('/sites/{site}', [SiteController::class, 'show'])->name('sites.show');

    // TF
    Route::middleware('check.role:admin')->group(function () {
        Route::post('/tf/store',      [TfController::class, 'store'])->name('tf.store');
        Route::put('/tf/update/{tf}', [TfController::class, 'update'])->name('tf.update');
    });
    Route::get('/tf/{tf}', [TfController::class, 'show'])->name('tf.show');

    // LOTS
    Route::get('/lots/vendus',           [LotController::class, 'vendus'])->name('lots.vendus');
    Route::get('/lots/client-search',    [LotController::class, 'clientSearch'])->name('lots.clientSearch');
    Route::get('/lots/client-panel/{id}',[LotController::class, 'clientPanel'])->name('lots.clientPanel');
    Route::get('/lots/{id}/hover-info',  [LotController::class, 'hoverInfo'])->name('lots.hoverInfo');
    Route::get('/lots/client-visites/{clientId}', [LotController::class, 'clientVisites'])->name('lots.clientVisites');
    Route::middleware('check.role:admin')->group(function () {
        Route::post('/lots/store',      [LotController::class, 'store'])->name('lots.store');
        Route::post('/lots/set-origin', [LotController::class, 'setOrigin'])->name('lots.setOrigin');
        Route::put('/lots/{id}',        [LotController::class, 'update'])->name('lots.update');
        Route::delete('/lots/{id}',     [LotController::class, 'destroy'])->name('lots.destroy');
    });

    // ZONES GROUPES
    Route::middleware('check.role:admin')->group(function () {
        Route::post('/zone-groupes',         [ZoneGroupeController::class, 'store'])->name('zone-groupes.store');
        Route::put('/zone-groupes/{id}',     [ZoneGroupeController::class, 'update'])->name('zone-groupes.update');
        Route::delete('/zone-groupes/{id}',  [ZoneGroupeController::class, 'destroy'])->name('zone-groupes.destroy');
    });
    Route::get('/zone-groupes/{id}/panel', [ZoneGroupeController::class, 'panel'])->name('zone-groupes.panel');

    // DOSSIER TECHNIQUE
    Route::get('/dossier/{lot}',               [DossierTechniqueController::class, 'show'])->name('dossier.show');
    Route::post('/dossier/toggle/{lot}',       [DossierTechniqueController::class, 'toggle'])->name('dossier.toggle');
    Route::get('/dossier-zone/{zone}',         [DossierTechniqueController::class, 'showZone'])->name('dossier.zone.show');
    Route::post('/dossier-zone/toggle/{zone}', [DossierTechniqueController::class, 'toggleZone'])->name('dossier.zone.toggle');
    Route::delete('/suivi-client/dossiers/{dossier}',[SuiviClientController::class, 'destroyDossier'])->name('suivi-client.dossiers.destroy');
    
   Route::post('/dossiers/{dossier}/maj-etape', [SuiviClientController::class, 'majEtape'])
    ->name('dossiers.maj-etape');

    // ✅ Routes pour les CNI
    Route::get('/dossiers/{dossier}/cni', [DossierClientController::class, 'getCni'])
        ->name('dossiers.cni');
    Route::delete('/dossiers/{dossier}/cni', [DossierClientController::class, 'deleteCniImage'])
        ->name('dossiers.cni.delete');

    // ✅ Route pour supprimer un dossier (via AJAX)
    Route::delete('/dossiers/{dossier}/supprimer', [DossierClientController::class, 'supprimerDossier'])
        ->name('dossiers.supprimer');

    // PAIEMENTS — commercial + admin
    Route::middleware('check.role:admin,commercial')->group(function () {

        Route::delete('/admin/paiements-techniques/{id}', [PaiementTechniqueController::class, 'destroy'])->name('paiements-techniques.destroy');
        Route::delete('/admin/paiements-morcellements/{id}', [PaiementMorcellementController::class, 'destroy'])->name('paiements-morcellements.destroy');
        Route::delete('/admin/paiements-dossiers/{id}', [PaiementDossierController::class, 'destroy'])->name('paiements-dossiers.destroy');

        // Paiement dossier
        Route::post('/paiements-dossier/{dossierId}', [PaiementDossierController::class, 'store']);
        Route::get('/paiements-dossier/{dossierId}', [PaiementDossierController::class, 'index']);

        // Bons de paiement
        Route::get('bons/{dossier}',          [BonPaiementController::class, 'index'])  ->name('bons.index');
        Route::post('bons/{dossier}',         [BonPaiementController::class, 'store'])  ->name('bons.store');
        Route::get('bons/{dossier}/creer',    [BonPaiementController::class, 'creer'])  ->name('bons.creer');
        Route::get('bons/detail/{bon}',       [BonPaiementController::class, 'show'])   ->name('bons.show');
        Route::get('bons/detail/{bon}/pdf',   [BonPaiementController::class, 'pdf'])    ->name('bons.pdf');
        Route::delete('bons/detail/{bon}',    [BonPaiementController::class, 'destroy'])->name('bons.destroy');
        Route::post('bons/detail/{bon}/toggle-reste', [BonPaiementController::class, 'toggleReste'])->name('bons.toggle-reste');

        // Paiement technique
        Route::post('/paiements-technique/{dossierId}', [PaiementTechniqueController::class, 'store']);

        // Paiement morcellement
        Route::post('/paiements-morcellement/{dossierId}', [PaiementMorcellementController::class, 'store']);
    });

    // COMMERCIAUX — admin seulement
    Route::middleware('check.role:admin')->group(function () {
        Route::get('/commerciaux',         [CommercialController::class, 'index'])->name('commerciaux.index');
        Route::post('/commerciaux',        [CommercialController::class, 'store'])->name('commerciaux.store');
        Route::put('/commerciaux/{id}',    [CommercialController::class, 'update'])->name('commerciaux.update');
        Route::delete('/commerciaux/{id}', [CommercialController::class, 'destroy'])->name('commerciaux.destroy');
        Route::get('/agents',              [AgentCommercialController::class, 'index'])->name('agents.index');
        Route::post('/agents',             [AgentCommercialController::class, 'store'])->name('agents.store');
        Route::put('/agents/{id}',         [AgentCommercialController::class, 'update'])->name('agents.update');
        Route::delete('/agents/{id}',      [AgentCommercialController::class, 'destroy'])->name('agents.destroy');
    });

    // SUIVI CLIENT — admin + commercial
    Route::middleware('check.role:admin,commercial')->group(function () {
        Route::get('/suivi-client',               [SuiviClientController::class, 'index'])->name('suivi-client.index');
        Route::get('/suivi-client/create',        [SuiviClientController::class, 'create'])->name('suivi-client.create');
        Route::post('/suivi-client',              [SuiviClientController::class, 'store'])->name('suivi-client.store');
        Route::get('/suivi-client/{id}',          [SuiviClientController::class, 'show'])->name('suivi-client.show');
        Route::get('/suivi-client/{id}/edit',     [SuiviClientController::class, 'edit'])->name('suivi-client.edit');
        Route::put('/suivi-client/{id}',          [SuiviClientController::class, 'update'])->name('suivi-client.update');
        Route::get('/suivi-client/{id}/dossiers', [SuiviClientController::class, 'dossiers'])->name('suivi-client.dossiers');
        Route::delete('/suivi-client/{id}',       [SuiviClientController::class, 'destroy'])->name('suivi-client.destroy');
    });

    Route::middleware('check.role:admin,commercial')->group(function () {
        Route::post('/clients/{clientId}/modifier-nom',
            [SuiviClientController::class, 'modifierNom'])->name('clients.modifier-nom');

        // Mise à jour prix dossier
        Route::post('/dossiers/{dossierId}/maj-prix',
            [DossierClientController::class, 'majPrix'])->name('dossiers.maj-prix');

        // Export Excel
        Route::get('/dossiers/export-excel',
            [DossierClientController::class, 'exportExcel'])->name('dossiers.export-excel');
    });

    // RAPPORT — admin
    Route::middleware('check.role:admin')->group(function () {
        Route::get('/rapport',              [RapportController::class, 'index'])->name('rapport.index');
        Route::post('/rapport/sauvegarder', [RapportController::class, 'sauvegarder'])->name('rapport.sauvegarder');
        Route::get('/rapport/liste',        [RapportController::class, 'liste'])->name('rapport.liste');
        Route::match(['get','post'],'/rapport/export',[RapportController::class, 'export'])->name('rapport.export');
        Route::delete('/rapport/{id}',      [RapportController::class, 'destroy'])->name('rapport.destroy');
        Route::get('/import-export',        [ImportExportController::class, 'index'])->name('import-export.index');
        Route::post('/import-export/export',[ImportExportController::class, 'export'])->name('import-export.export');
        Route::post('/import-export/import',[ImportExportController::class, 'import'])->name('import-export.import');
    });

    // VISITES — admin + commercial
    Route::middleware('check.role:admin,commercial')->group(function () {
        Route::get('/visites',                 [VisiteController::class, 'index'])->name('visites.index');
        Route::post('/visites',                [VisiteController::class, 'store'])->name('visites.store');
        Route::put('/visites/{id}',            [VisiteController::class, 'update'])->name('visites.update');
        Route::delete('/visites/{id}',         [VisiteController::class, 'destroy'])->name('visites.destroy');
        Route::get('/visites/export',          [VisiteController::class, 'export'])->name('visites.export');
        Route::post('/visites/import',         [VisiteController::class, 'import'])->name('visites.import');
        Route::get('/visites/visiteur-search', [VisiteController::class, 'visiteurSearch'])->name('visites.visiteurSearch');
    });

    // GESTION UTILISATEURS — admin seulement
    Route::middleware('check.role:admin')->group(function () {
        Route::get('/users',               [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/users',              [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{id}',          [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{id}',       [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/users/{id}/toggle',  [UserController::class, 'toggleActif'])->name('admin.users.toggle');
    });
});

// ── RH ────────────────────────────────────────────────────────
Route::prefix('rh')->middleware(['auth', 'check.role:admin,rh'])->group(function () {

    Route::get('/dashboard', [DashboardRHController::class, 'index'])->name('rh.dashboard');

    Route::get('/employes',                        [EmployeController::class, 'index'])->name('rh.employes.index');
    Route::get('/employes/create',                 [EmployeController::class, 'create'])->name('rh.employes.create');
    Route::get('/employes/export',                 [EmployeController::class, 'export'])->name('rh.employes.export');
    Route::post('/employes',                       [EmployeController::class, 'store'])->name('rh.employes.store');
    Route::get('/employes/{id}',                   [EmployeController::class, 'show'])->name('rh.employes.show');
    Route::post('/employes/{id}/photo',            [EmployeController::class, 'uploadPhoto'])->name('rh.employes.photo');
    Route::get('/employes/{id}/edit',              [EmployeController::class, 'edit'])->name('rh.employes.edit');
    Route::put('/employes/{id}',                   [EmployeController::class, 'update'])->name('rh.employes.update');
    Route::delete('/employes/{id}',                [EmployeController::class, 'destroy'])->name('rh.employes.destroy');
    Route::post('/employes/{id}/documents',        [EmployeController::class, 'uploadDocument'])->name('rh.employes.documents.store');
    Route::get('/employes/{id}/documents/{docId}', [EmployeController::class, 'downloadDocument'])->name('rh.employes.documents.download');
    Route::delete('/employes/documents/{docId}',   [EmployeController::class, 'deleteDocument'])->name('rh.employes.documents.destroy');
    Route::get('/employes/{id}/pdf',               [EmployeController::class, 'pdfFiche'])->name('rh.employes.pdf');

    Route::get('/paie',                [PaieController::class, 'index'])->name('rh.paie.index');
    Route::get('/paie/pdf-liste',      [PaieController::class, 'pdfListe'])->name('rh.paie.pdf-liste');
    Route::get('/paie/create',         [PaieController::class, 'create'])->name('rh.paie.create');
    Route::get('/paie/recapitulatif',  [PaieController::class, 'recapitulatif'])->name('rh.paie.recapitulatif');
    Route::post('/paie/generer-masse', [PaieController::class, 'genererMasse'])->name('rh.paie.generer');
    Route::post('/paie',               [PaieController::class, 'store'])->name('rh.paie.store');
    Route::get('/paie/{id}',           [PaieController::class, 'show'])->name('rh.paie.show');
    Route::get('/paie/{id}/pdf',       [PaieController::class, 'pdf'])->name('rh.paie.pdf');
    Route::put('/paie/{id}',           [PaieController::class, 'update'])->name('rh.paie.update');
    Route::delete('/paie/{id}',        [PaieController::class, 'destroy'])->name('rh.paie.destroy');
    Route::post('/paie/{id}/valider',  [PaieController::class, 'valider'])->name('rh.paie.valider');

    Route::get('/absences',                   [AbsenceController::class, 'index'])->name('rh.absences.index');
    Route::post('/absences',                  [AbsenceController::class, 'store'])->name('rh.absences.store');
    Route::post('/absences/nettoyer-doublons', [AbsenceController::class, 'nettoyerAbsencesDoublons'])->name('rh.absences.nettoyer-doublons');
    Route::put('/absences/{id}',              [AbsenceController::class, 'update'])->name('rh.absences.update');
    Route::delete('/absences/{id}',           [AbsenceController::class, 'destroy'])->name('rh.absences.destroy');
    Route::post('/absences/{id}/approuver',   [AbsenceController::class, 'approuver'])->name('rh.absences.approuver');
    Route::post('/absences/{id}/refuser',     [AbsenceController::class, 'refuser'])->name('rh.absences.refuser');
    Route::get('/absences/liste-pdf',         [AbsenceController::class, 'pdfListe'])->name('rh.absences.pdf-liste');
    Route::get('/absences/{id}/pdf',          [AbsenceController::class, 'pdf'])->name('rh.absences.pdf');

    Route::get('/prets',          [PretController::class, 'index'])->name('rh.prets.index');
    Route::post('/prets',         [PretController::class, 'store'])->name('rh.prets.store');
    Route::put('/prets/{id}',     [PretController::class, 'update'])->name('rh.prets.update');
    Route::delete('/prets/{id}',  [PretController::class, 'destroy'])->name('rh.prets.destroy');

    Route::get('/sanctions',              [SanctionController::class, 'index'])->name('rh.sanctions.index');
    Route::post('/sanctions',             [SanctionController::class, 'store'])->name('rh.sanctions.store');
    Route::get('/sanctions/pdf-liste',    [SanctionController::class, 'pdfListe'])->name('rh.sanctions.pdf-liste');
    Route::put('/sanctions/{id}',         [SanctionController::class, 'update'])->name('rh.sanctions.update');
    Route::delete('/sanctions/{id}',      [SanctionController::class, 'destroy'])->name('rh.sanctions.destroy');

    Route::get('/retards',             [RetardController::class, 'index'])->name('rh.retards.index');
    Route::post('/retards',            [RetardController::class, 'store'])->name('rh.retards.store');
    Route::get('/retards/pdf-liste',   [RetardController::class, 'pdfListe'])->name('rh.retards.pdf-liste');
    // ✅ MEILLEURE SOLUTION - Utiliser GET
Route::get('/retards/nettoyer-doublons', [RetardController::class, 'nettoyerDoublons'])->name('rh.retards.nettoyer-doublons');
    Route::post('/retards/import-excel', [RetardController::class, 'importExcel'])->name('rh.retards.import-excel');
    Route::put('/retards/{id}',        [RetardController::class, 'update'])->name('rh.retards.update');
    Route::delete('/retards/{id}',     [RetardController::class, 'destroy'])->name('rh.retards.destroy');
    Route::get('/retards/par-direction',[RetardController::class, 'parDirection'])->name('rh.retards.par-direction');

    Route::get('/directions',         [DirectionController::class, 'index'])->name('rh.directions.index');
    Route::post('/directions',        [DirectionController::class, 'storeDirection'])->name('rh.directions.store');
    Route::put('/directions/{id}',    [DirectionController::class, 'updateDirection'])->name('rh.directions.update');
    Route::delete('/directions/{id}', [DirectionController::class, 'destroyDirection'])->name('rh.directions.destroy');
    Route::post('/services',          [DirectionController::class, 'storeService'])->name('rh.services.store');
    Route::put('/services/{id}',      [DirectionController::class, 'updateService'])->name('rh.services.update');
    Route::delete('/services/{id}',   [DirectionController::class, 'destroyService'])->name('rh.services.destroy');
    Route::post('/postes',            [DirectionController::class, 'storePoste'])->name('rh.postes.store');
    Route::delete('/postes/{id}',     [DirectionController::class, 'destroyPoste'])->name('rh.postes.destroy');

    // ===== CONGÉS =====
Route::prefix('conges')->name('rh.conges.')->group(function () {
    // ✅ Routes fixes AVANT les routes avec paramètres
    Route::get('/', [App\Http\Controllers\RH\CongeController::class, 'index'])->name('index');
    Route::post('/', [App\Http\Controllers\RH\CongeController::class, 'store'])->name('store');
    Route::get('/planning-pdf', [App\Http\Controllers\RH\CongeController::class, 'planningPdf'])->name('planning-pdf');
    
    // ⚠️ Routes avec paramètres APRÈS
    Route::put('/{id}', [App\Http\Controllers\RH\CongeController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\RH\CongeController::class, 'destroy'])->name('destroy');
    Route::get('/{id}/pdf', [App\Http\Controllers\RH\CongeController::class, 'pdf'])->name('pdf');
});
    // ===== CONTRATS =====
Route::resource('contrats', App\Http\Controllers\RH\ContratController::class, [
    'names' => [
        'index' => 'rh.contrats.index',
        'create' => 'rh.contrats.create',
        'store' => 'rh.contrats.store',
        'show' => 'rh.contrats.show',
        'edit' => 'rh.contrats.edit',
        'update' => 'rh.contrats.update',
        'destroy' => 'rh.contrats.destroy',
    ]
])->parameters(['contrats' => 'contrat']);

// Routes supplémentaires pour les contrats
Route::get('contrats/{id}/pdf', [App\Http\Controllers\RH\ContratController::class, 'pdf'])->name('rh.contrats.pdf');
Route::post('contrats/{id}/valider', [App\Http\Controllers\RH\ContratController::class, 'valider'])->name('rh.contrats.valider');
Route::post('contrats/{id}/renouveler', [App\Http\Controllers\RH\ContratController::class, 'renouveler'])->name('rh.contrats.renouveler');
Route::post('contrats/{id}/resilier', [App\Http\Controllers\RH\ContratController::class, 'resilier'])->name('rh.contrats.resilier');

// ===== CNPS =====
Route::prefix('cnps')->name('rh.cnps.')->group(function () {
    Route::get('/', [App\Http\Controllers\RH\CnpsController::class, 'index'])->name('index');
    
    // Déclarations
    Route::get('declarations', [App\Http\Controllers\RH\CnpsController::class, 'declarations'])->name('declarations');
    Route::get('declarations/create', [App\Http\Controllers\RH\CnpsController::class, 'createDeclaration'])->name('declarations.create');
    Route::post('declarations', [App\Http\Controllers\RH\CnpsController::class, 'storeDeclaration'])->name('declarations.store');
    Route::get('declarations/{id}', [App\Http\Controllers\RH\CnpsController::class, 'showDeclaration'])->name('declarations.show');
    Route::post('declarations/{id}/employes', [App\Http\Controllers\RH\CnpsController::class, 'ajouterEmployes'])->name('declarations.ajouter-employes');
    Route::delete('declarations/{declarationId}/lignes/{ligneId}', [App\Http\Controllers\RH\CnpsController::class, 'supprimerLigne'])->name('declarations.supprimer-ligne');
    Route::get('declarations/{id}/dipe', [App\Http\Controllers\RH\CnpsController::class, 'generateDIPE'])->name('declarations.dipe');
    Route::post('declarations/{id}/statut', [App\Http\Controllers\RH\CnpsController::class, 'changerStatut'])->name('declarations.statut');
    
    // Affiliations
    Route::get('affiliations', [App\Http\Controllers\RH\CnpsController::class, 'affiliations'])->name('affiliations');
    Route::post('affiliations', [App\Http\Controllers\RH\CnpsController::class, 'storeAffiliation'])->name('affiliations.store');
    Route::delete('affiliations/{id}', [App\Http\Controllers\RH\CnpsController::class, 'destroyAffiliation'])->name('affiliations.destroy');
});

// ===== DÉPARTS =====
Route::prefix('departs')->name('rh.departs.')->group(function () {
    Route::get('/', [App\Http\Controllers\RH\DepartController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\RH\DepartController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\RH\DepartController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\RH\DepartController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\RH\DepartController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\RH\DepartController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\RH\DepartController::class, 'destroy'])->name('destroy');
    
    // Actions
    Route::post('/{id}/valider', [App\Http\Controllers\RH\DepartController::class, 'valider'])->name('valider');
    Route::post('/{id}/annuler', [App\Http\Controllers\RH\DepartController::class, 'annuler'])->name('annuler');
    Route::post('/{id}/generer-solde', [App\Http\Controllers\RH\DepartController::class, 'genererSolde'])->name('generer-solde');
    Route::post('/{id}/generer-certificat', [App\Http\Controllers\RH\DepartController::class, 'genererCertificat'])->name('generer-certificat');
    
    // PDF
    Route::get('/solde/{id}/pdf', [App\Http\Controllers\RH\DepartController::class, 'pdfSolde'])->name('pdf-solde');
    Route::get('/certificat/{id}/pdf', [App\Http\Controllers\RH\DepartController::class, 'pdfCertificat'])->name('pdf-certificat');
});

// ===== ALERTES =====
Route::prefix('alertes')->name('rh.alertes.')->group(function () {
    Route::get('/', [App\Http\Controllers\RH\AlerteController::class, 'index'])->name('index');
    Route::post('/{id}/marquer-lu', [App\Http\Controllers\RH\AlerteController::class, 'marquerLu'])->name('marquer-lu');
    Route::post('/{id}/marquer-traite', [App\Http\Controllers\RH\AlerteController::class, 'marquerTraite'])->name('marquer-traite');
    Route::post('/{id}/marquer-ignore', [App\Http\Controllers\RH\AlerteController::class, 'marquerIgnore'])->name('marquer-ignore');
    Route::post('/tout-marquer-lu', [App\Http\Controllers\RH\AlerteController::class, 'toutMarquerLu'])->name('tout-marquer-lu');
    Route::delete('/{id}', [App\Http\Controllers\RH\AlerteController::class, 'destroy'])->name('destroy');
    Route::get('/count-non-lu', [App\Http\Controllers\RH\AlerteController::class, 'countNonLu'])->name('count-non-lu');
});

Route::get('/alertes/count-non-lu', [App\Http\Controllers\RH\AlerteController::class, 'countNonLu'])
    ->name('rh.alertes.count-non-lu');

     Route::post('/horaires', [App\Http\Controllers\RH\HoraireController::class, 'store'])->name('rh.horaires.store');
    
     // ===== RECRUTEMENT =====
Route::prefix('recrutement')->name('rh.recrutement.')->group(function () {
    Route::get('/', [App\Http\Controllers\RH\RecrutementController::class, 'index'])->name('index');
    Route::get('/create', [App\Http\Controllers\RH\RecrutementController::class, 'create'])->name('create');
    Route::post('/', [App\Http\Controllers\RH\RecrutementController::class, 'store'])->name('store');
    Route::get('/export-pdf', [App\Http\Controllers\RH\RecrutementController::class, 'pdfListe'])->name('export-pdf');

    Route::get('/{id}', [App\Http\Controllers\RH\RecrutementController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [App\Http\Controllers\RH\RecrutementController::class, 'edit'])->name('edit');
    Route::put('/{id}', [App\Http\Controllers\RH\RecrutementController::class, 'update'])->name('update');
    Route::delete('/{id}', [App\Http\Controllers\RH\RecrutementController::class, 'destroy'])->name('destroy');
    
    // Actions
    Route::post('/{id}/statut', [App\Http\Controllers\RH\RecrutementController::class, 'changerStatut'])->name('statut');
    Route::post('/{id}/entretien', [App\Http\Controllers\RH\RecrutementController::class, 'storeEntretien'])->name('entretien.store');
    Route::post('/{id}/test', [App\Http\Controllers\RH\RecrutementController::class, 'storeTest'])->name('test.store');
    Route::get('/{id}/download/{type}', [App\Http\Controllers\RH\RecrutementController::class, 'downloadDocument'])->name('download');
});

// ===== SANTÉ & SÉCURITÉ =====
Route::prefix('sante')->name('rh.sante.')->group(function () {
    Route::get('/', [App\Http\Controllers\RH\SanteController::class, 'index'])->name('index');
    
    // Visites médicales
    Route::get('/visites', [App\Http\Controllers\RH\SanteController::class, 'visites'])->name('visites');
    Route::get('/visites/create', [App\Http\Controllers\RH\SanteController::class, 'createVisite'])->name('visites.create');
    Route::post('/visites', [App\Http\Controllers\RH\SanteController::class, 'storeVisite'])->name('visites.store');
        Route::get('/visites/pdf', [App\Http\Controllers\RH\SanteController::class, 'pdfVisites'])->name('visites.pdf');

    Route::get('/visites/{id}', [App\Http\Controllers\RH\SanteController::class, 'showVisite'])->name('visites.show');
    Route::get('/visites/{id}/edit', [App\Http\Controllers\RH\SanteController::class, 'editVisite'])->name('visites.edit');
    Route::put('/visites/{id}', [App\Http\Controllers\RH\SanteController::class, 'updateVisite'])->name('visites.update');
    Route::delete('/visites/{id}', [App\Http\Controllers\RH\SanteController::class, 'destroyVisite'])->name('visites.destroy');
    
    // Accidents
    Route::get('/accidents', [App\Http\Controllers\RH\SanteController::class, 'accidents'])->name('accidents');
    Route::get('/accidents/create', [App\Http\Controllers\RH\SanteController::class, 'createAccident'])->name('accidents.create');
    Route::post('/accidents', [App\Http\Controllers\RH\SanteController::class, 'storeAccident'])->name('accidents.store');
    Route::get('/accidents/{id}', [App\Http\Controllers\RH\SanteController::class, 'showAccident'])->name('accidents.show');
    Route::get('/accidents/{id}/edit', [App\Http\Controllers\RH\SanteController::class, 'editAccident'])->name('accidents.edit');
    Route::put('/accidents/{id}', [App\Http\Controllers\RH\SanteController::class, 'updateAccident'])->name('accidents.update');
    Route::delete('/accidents/{id}', [App\Http\Controllers\RH\SanteController::class, 'destroyAccident'])->name('accidents.destroy');
    
    // Trousses de secours
    Route::get('/trousses', [App\Http\Controllers\RH\SanteController::class, 'trousses'])->name('trousses');
    Route::post('/trousses', [App\Http\Controllers\RH\SanteController::class, 'storeTrousse'])->name('trousses.store');
    Route::put('/trousses/{id}', [App\Http\Controllers\RH\SanteController::class, 'updateTrousse'])->name('trousses.update');
    Route::delete('/trousses/{id}', [App\Http\Controllers\RH\SanteController::class, 'destroyTrousse'])->name('trousses.destroy');
});

});

// ================================================================
// MODULE FEB — Espace utilisateur
// ================================================================
Route::prefix('feb')->name('feb.')->group(function () {

    // Auth FEB
    Route::get('login',  [App\Http\Controllers\Feb\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [App\Http\Controllers\Feb\AuthController::class, 'login'])->name('login.post');
    Route::post('logout',[App\Http\Controllers\Feb\AuthController::class, 'logout'])->name('logout');

    Route::middleware('feb.auth')->group(function () {

        // Accueil
        Route::get('/',      [App\Http\Controllers\Feb\FicheController::class, 'index'])->name('index');
        Route::get('fiches', [App\Http\Controllers\Feb\FicheController::class, 'index'])->name('fiches.index');

        // Routes pour les destinataires
        Route::get('destinataires', [DestinataireController::class, 'index'])->name('destinataires.index');
        Route::get('destinataires/search', [DestinataireController::class, 'search'])->name('destinataires.search');
        Route::post('destinataires', [DestinataireController::class, 'store'])->name('destinataires.store');
        Route::delete('destinataires/{id}', [DestinataireController::class, 'destroy'])->name('destinataires.destroy');
        
        // ✅ IMPORTANT : routes fixes AVANT les routes avec paramètres {fiche}
        Route::get('fiches/creer',            [App\Http\Controllers\Feb\FicheController::class, 'creer'])           ->name('fiches.creer');
        Route::post('fiches/creer-soumettre', [App\Http\Controllers\Feb\FicheController::class, 'creerEtSoumettre'])->name('fiches.creer-soumettre');

        // ✅ Routes avec paramètres APRÈS les routes fixes
        Route::get('fiches/{fiche}/continuer',[App\Http\Controllers\Feb\FicheController::class, 'continuer'])       ->name('fiches.continuer');
        Route::get('fiches/{fiche}/utiliser', [App\Http\Controllers\Feb\FicheController::class, 'utiliserModele'])  ->name('fiches.utiliser');
        Route::get('fiches/{fiche}/pdf',      [App\Http\Controllers\Feb\FicheController::class, 'pdf'])             ->name('fiches.pdf');
    });
});

// ================================================================
// MODULE FEB — Admin
// ================================================================
Route::prefix('admin/feb')->name('admin.feb.')->middleware(['auth','check.role:admin'])->group(function () {

    Route::get('/', [App\Http\Controllers\Feb\AdminController::class, 'index'])->name('index');

    // Agences
    Route::resource('agences',      App\Http\Controllers\Feb\AgenceController::class)     ->names('agences');

    // Colonnes
    Route::resource('colonnes',     App\Http\Controllers\Feb\ColonneController::class)    ->names('colonnes');

    // Utilisateurs FEB
    Route::resource('utilisateurs', App\Http\Controllers\Feb\UtilisateurController::class)->names('utilisateurs');
    Route::post('utilisateurs/{id}/toggle', [App\Http\Controllers\Feb\UtilisateurController::class, 'toggle'])->name('utilisateurs.toggle');

    // ✅ Fiches admin — routes fixes AVANT {fiche}
    Route::get('fiches',                  [App\Http\Controllers\Feb\AdminFicheController::class, 'index'])    ->name('fiches.index');
    Route::get('fiches/{fiche}',          [App\Http\Controllers\Feb\AdminFicheController::class, 'show'])     ->name('fiches.show');
    Route::get('fiches/{fiche}/pdf',      [App\Http\Controllers\Feb\AdminFicheController::class, 'pdf'])      ->name('fiches.pdf');
    Route::post('fiches/{fiche}/marquer', [App\Http\Controllers\Feb\AdminFicheController::class, 'marquerVue'])->name('fiches.marquer');
});


// ===== GESTION BLOCS & LOTS (admin) =====
Route::prefix('admin/affectations')->name('affectations.')->middleware(['auth','check.role:admin'])->group(function() {

    // Dashboard affectation
    Route::get('/',                    [App\Http\Controllers\AffectationController::class, 'index'])         ->name('index');

    // Blocs
    Route::get('blocs',                [App\Http\Controllers\AffectationController::class, 'blocs'])         ->name('blocs');
    Route::post('blocs',               [App\Http\Controllers\AffectationController::class, 'storeBloc'])     ->name('blocs.store');
    Route::put('blocs/{id}',           [App\Http\Controllers\AffectationController::class, 'updateBloc'])    ->name('blocs.update');
    Route::delete('blocs/{id}',        [App\Http\Controllers\AffectationController::class, 'destroyBloc'])   ->name('blocs.destroy');

    // Lots
    Route::get('lots',                 [App\Http\Controllers\AffectationController::class, 'lots'])          ->name('lots');
    Route::post('lots',                [App\Http\Controllers\AffectationController::class, 'storeLots'])     ->name('lots.store');
    Route::put('lots/{id}',            [App\Http\Controllers\AffectationController::class, 'updateLot'])     ->name('lots.update');
    Route::delete('lots/{id}',         [App\Http\Controllers\AffectationController::class, 'destroyLot'])    ->name('lots.destroy');
    
    // ✅ AJOUTER CETTE ROUTE
    Route::post('lots/superficie-multiple', [App\Http\Controllers\AffectationController::class, 'updateSuperficieMultiple'])
        ->name('lots.superficie-multiple');

    // Affectation à un dossier
    Route::post('affecter/{dossier}',  [App\Http\Controllers\AffectationController::class, 'affecter'])      ->name('affecter');
    Route::delete('{affectation}',     [App\Http\Controllers\AffectationController::class, 'annuler'])        ->name('annuler');

    // API JSON pour les sélecteurs dynamiques
    Route::get('api/sites/{grandSite}',    [App\Http\Controllers\AffectationController::class, 'apiSites'])  ->name('api.sites');
    Route::get('api/tfs/{site}',           [App\Http\Controllers\AffectationController::class, 'apiTfs'])    ->name('api.tfs');
    Route::get('api/blocs/{tf}',           [App\Http\Controllers\AffectationController::class, 'apiBlocs'])  ->name('api.blocs');
    Route::get('api/lots/{bloc}',          [App\Http\Controllers\AffectationController::class, 'apiLots'])   ->name('api.lots');
});