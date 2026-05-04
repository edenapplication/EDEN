<?php

use Illuminate\Support\Facades\Route;
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

Route::get('/', fn() => redirect()->route('grand-sites.index'));

Route::prefix('admin')->group(function () {

    // DASHBOARD
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // GRAND SITES
    Route::get('/grand-sites',           [GrandSiteController::class, 'index'])->name('grand-sites.index');
    Route::get('/grand-sites/create',    [GrandSiteController::class, 'create'])->name('grand-sites.create');
    Route::post('/grand-sites',          [GrandSiteController::class, 'store'])->name('grand-sites.store');
    Route::get('/grand-sites/{id}/edit', [GrandSiteController::class, 'edit'])->name('grand-sites.edit');
    Route::put('/grand-sites/{id}',      [GrandSiteController::class, 'update'])->name('grand-sites.update');
    Route::delete('/grand-sites/{id}',   [GrandSiteController::class, 'destroy'])->name('grand-sites.destroy');

    // SITES
    Route::get('/grand-sites/{grand_site_id}/sites',        [SiteController::class, 'index'])->name('sites.index');
    Route::get('/grand-sites/{grand_site_id}/sites/create', [SiteController::class, 'create'])->name('sites.create');
    Route::post('/grand-sites/{grand_site_id}/sites',       [SiteController::class, 'store'])->name('sites.store');
    Route::get('/sites/{site}',                             [SiteController::class, 'show'])->name('sites.show');
    Route::get('/sites/{site}/edit',                        [SiteController::class, 'edit'])->name('sites.edit');
    Route::put('/sites/{site}',                             [SiteController::class, 'update'])->name('sites.update');
    Route::delete('/sites/{site}',                          [SiteController::class, 'destroy'])->name('sites.destroy');

    // TF
    Route::post('/tf/store',      [TfController::class, 'store'])->name('tf.store');
    Route::get('/tf/{tf}',        [TfController::class, 'show'])->name('tf.show');
    Route::put('/tf/update/{tf}', [TfController::class, 'update'])->name('tf.update');

    // LOTS — fixes AVANT {id}
    Route::get('/lots/vendus',           [LotController::class, 'vendus'])->name('lots.vendus');
    Route::post('/lots/store',           [LotController::class, 'store'])->name('lots.store');
    Route::post('/lots/set-origin',      [LotController::class, 'setOrigin'])->name('lots.setOrigin');
    Route::get('/lots/client-search',    [LotController::class, 'clientSearch'])->name('lots.clientSearch');
    Route::get('/lots/client-panel/{id}',[LotController::class, 'clientPanel'])->name('lots.clientPanel');
    Route::get('/lots/{id}/hover-info',  [LotController::class, 'hoverInfo'])->name('lots.hoverInfo');
    Route::put('/lots/{id}',             [LotController::class, 'update'])->name('lots.update');

    // DOSSIER TECHNIQUE (checklist)
    Route::get('/dossier/{lot}',         [DossierTechniqueController::class, 'show'])->name('dossier.show');
    Route::post('/dossier/toggle/{lot}', [DossierTechniqueController::class, 'toggle'])->name('dossier.toggle');

    // PAIEMENTS DOSSIER — nouvelle logique
    Route::post('/paiements-dossier/{dossierId}',   [PaiementDossierController::class, 'store'])->name('paiements-dossier.store');
    Route::get('/paiements-dossier/{dossierId}',    [PaiementDossierController::class, 'index'])->name('paiements-dossier.index');
    Route::delete('/paiements-dossier/{id}',        [PaiementDossierController::class, 'destroy'])->name('paiements-dossier.destroy');

    // COMMERCIAUX
    Route::get('/commerciaux',         [CommercialController::class, 'index'])->name('commerciaux.index');
    Route::post('/commerciaux',        [CommercialController::class, 'store'])->name('commerciaux.store');
    Route::put('/commerciaux/{id}',    [CommercialController::class, 'update'])->name('commerciaux.update');
    Route::delete('/commerciaux/{id}', [CommercialController::class, 'destroy'])->name('commerciaux.destroy');

    // AGENTS COMMERCIAUX
    Route::get('/agents',              [AgentCommercialController::class, 'index'])->name('agents.index');
    Route::post('/agents',             [AgentCommercialController::class, 'store'])->name('agents.store');
    Route::delete('/agents/{id}',      [AgentCommercialController::class, 'destroy'])->name('agents.destroy');
    Route::put('/agents/{id}', [AgentCommercialController::class, 'update'])->name('agents.update');

    // SUIVI CLIENT
    Route::get('/suivi-client',               [SuiviClientController::class, 'index'])->name('suivi-client.index');
    Route::get('/suivi-client/create',        [SuiviClientController::class, 'create'])->name('suivi-client.create');
    Route::post('/suivi-client',              [SuiviClientController::class, 'store'])->name('suivi-client.store');
    Route::get('/suivi-client/{id}',          [SuiviClientController::class, 'show'])->name('suivi-client.show');
    Route::get('/suivi-client/{id}/edit',     [SuiviClientController::class, 'edit'])->name('suivi-client.edit');
    Route::put('/suivi-client/{id}',          [SuiviClientController::class, 'update'])->name('suivi-client.update');
    Route::get('/suivi-client/{id}/dossiers', [SuiviClientController::class, 'dossiers'])->name('suivi-client.dossiers');

    // RAPPORT
    Route::get('/rapport',              [RapportController::class, 'index'])->name('rapport.index');
    Route::post('/rapport/sauvegarder', [RapportController::class, 'sauvegarder'])->name('rapport.sauvegarder');
    Route::get('/rapport/liste',        [RapportController::class, 'liste'])->name('rapport.liste');
    Route::match(['get', 'post'], '/rapport/export', [RapportController::class, 'export'])
    ->name('rapport.export');
    Route::delete('/rapport/{id}',      [RapportController::class, 'destroy'])->name('rapport.destroy');

    // IMPORT / EXPORT BASE DE DONNÉES
    Route::get('/import-export',        [ImportExportController::class, 'index'])->name('import-export.index');
    Route::post('/import-export/export',[ImportExportController::class, 'export'])->name('import-export.export');
    Route::post('/import-export/import',[ImportExportController::class, 'import'])->name('import-export.import');
});