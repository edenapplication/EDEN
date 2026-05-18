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
use App\Http\Controllers\DossierTechniqueController;
use App\Http\Controllers\SuiviClientController;
use App\Http\Controllers\AgentCommercialController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\VisiteController;
use App\Http\Controllers\ZoneGroupeController;
use App\Http\Controllers\Admin\UserController;

use App\Http\Controllers\RH\DashboardRHController;
use App\Http\Controllers\RH\EmployeController;
use App\Http\Controllers\RH\PaieController;
use App\Http\Controllers\RH\AbsenceController;
use App\Http\Controllers\RH\PretController;
use App\Http\Controllers\RH\SanctionController;
use App\Http\Controllers\RH\RetardController;
use App\Http\Controllers\RH\DirectionController;

// ── AUTHENTIFICATION ─────────────────────────────────────────
Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// ── PAGE D'ACCUEIL (modules) ─────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home')->middleware('auth');

// ── ADMIN ─────────────────────────────────────────────────────
Route::prefix('admin')->middleware(['auth', 'check.role:admin,rh,commercial'])->group(function () {

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

    // PAIEMENTS — commercial + admin
    Route::middleware('check.role:admin,commercial')->group(function () {
        Route::post('/paiements-dossier/{dossierId}',  [PaiementDossierController::class, 'store'])->name('paiements-dossier.store');
        Route::get('/paiements-dossier/{dossierId}',   [PaiementDossierController::class, 'index'])->name('paiements-dossier.index');
        Route::delete('/paiements-dossier/{id}',       [PaiementDossierController::class, 'destroy'])->name('paiements-dossier.destroy');
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
});