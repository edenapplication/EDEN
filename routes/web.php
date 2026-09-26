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
use App\Http\Controllers\PaiementTechniqueController;
use App\Http\Controllers\PaiementMorcellementController;
use App\Http\Controllers\DossierClientController;
use App\Http\Controllers\AffectationController;
use App\Http\Controllers\BonPaiementController; 
use App\Http\Controllers\BeneficiaireController;

use App\Http\Controllers\RH\DashboardRHController;
use App\Http\Controllers\RH\EmployeController;
use App\Http\Controllers\RH\PaieController;
use App\Http\Controllers\RH\AbsenceController;
use App\Http\Controllers\RH\PretController;
use App\Http\Controllers\RH\SanctionController;
use App\Http\Controllers\RH\RetardController;
use App\Http\Controllers\RH\DirectionController;
use App\Http\Controllers\RH\CongeController;
use App\Http\Controllers\RH\ContratController;
use App\Http\Controllers\RH\CnpsController;
use App\Http\Controllers\RH\DepartController;
use App\Http\Controllers\RH\AlerteController;
use App\Http\Controllers\RH\HoraireController;
use App\Http\Controllers\RH\RecrutementController;
use App\Http\Controllers\RH\SanteController;

use App\Http\Controllers\Feb\DestinataireController;
use App\Models\User;
use Illuminate\Http\Request;

// ── WEBHOOK DEPLOY ─────────────────────────────────────────────
Route::post('/deploy', function () {
    exec('cd /var/www/html && git pull origin main');
    exec('cd /var/www/html && php artisan optimize:clear');
    return response('DEPLOY OK', 200);
});

// ── AUTHENTIFICATION ────────────────────────────────────────────
Route::get('/login',  [LoginController::class, 'showLogin'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/logout',[LoginController::class, 'logout'])->name('logout');

// ── PAGE D'ACCUEIL ──────────────────────────────────────────────
Route::get('/', [HomeController::class, 'index'])->name('home')->middleware('auth');

// ═══════════════════════════════════════════════════════════════════
// ADMIN — Routes avec permissions par rôle
// ═══════════════════════════════════════════════════════════════════
Route::prefix('admin')->middleware(['auth', 'check.role:admin,rh,commercial'])->group(function () {

    // ═════════════════════════════════════════════════════════════════
    // ✅ 1. ROUTES ADMIN UNIQUEMENT
    // ═════════════════════════════════════════════════════════════════
    Route::middleware('check.role:admin')->group(function () {

        // ── GESTION ACCÈS (utilisateurs) ──
        Route::get('/users',               [UserController::class, 'index'])->name('admin.users.index');
        Route::post('/users',              [UserController::class, 'store'])->name('admin.users.store');
        Route::put('/users/{id}',          [UserController::class, 'update'])->name('admin.users.update');
        Route::delete('/users/{id}',       [UserController::class, 'destroy'])->name('admin.users.destroy');
        Route::post('/users/{id}/toggle',  [UserController::class, 'toggleActif'])->name('admin.users.toggle');

        // ── IMPORT / EXPORT ──
        Route::get('/import-export',        [ImportExportController::class, 'index'])->name('import-export.index');
        Route::post('/import-export/export',[ImportExportController::class, 'export'])->name('import-export.export');
        Route::post('/import-export/import',[ImportExportController::class, 'import'])->name('import-export.import');

        // ── DASHBOARD ──
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    });

    // ═════════════════════════════════════════════════════════════════
    // ✅ 2. ROUTES ADMIN + COMMERCIAL (toutes les routes métier)
    // ═════════════════════════════════════════════════════════════════
    Route::middleware('check.role:admin,commercial')->group(function () {

        // ── GRAND SITES (CRUD complet) ──
        Route::get('/grand-sites',           [GrandSiteController::class, 'index'])->name('grand-sites.index');
        Route::get('/grand-sites/create',    [GrandSiteController::class, 'create'])->name('grand-sites.create');
        Route::post('/grand-sites',          [GrandSiteController::class, 'store'])->name('grand-sites.store');
        Route::get('/grand-sites/{id}/edit', [GrandSiteController::class, 'edit'])->name('grand-sites.edit');
        Route::put('/grand-sites/{id}',      [GrandSiteController::class, 'update'])->name('grand-sites.update');
        Route::delete('/grand-sites/{id}',   [GrandSiteController::class, 'destroy'])->name('grand-sites.destroy');

        // ── SITES (CRUD complet) ──
        Route::get('/grand-sites/{grand_site_id}/sites',        [SiteController::class, 'index'])->name('sites.index');
        Route::get('/grand-sites/{grand_site_id}/sites/create', [SiteController::class, 'create'])->name('sites.create');
        Route::post('/grand-sites/{grand_site_id}/sites',       [SiteController::class, 'store'])->name('sites.store');
        Route::get('/sites/{site}/edit',                        [SiteController::class, 'edit'])->name('sites.edit');
        Route::put('/sites/{site}',                             [SiteController::class, 'update'])->name('sites.update');
        Route::delete('/sites/{site}',                          [SiteController::class, 'destroy'])->name('sites.destroy');
        Route::get('/sites/{site}',                             [SiteController::class, 'show'])->name('sites.show');

        // ── TF (CRUD complet) ──
        Route::post('/tf/store',      [TfController::class, 'store'])->name('tf.store');
        Route::put('/tf/update/{tf}', [TfController::class, 'update'])->name('tf.update');
        Route::get('/tf/{tf}',        [TfController::class, 'show'])->name('tf.show');

        // ── LOTS (CRUD complet) ──
        Route::get('/lots/vendus',           [LotController::class, 'vendus'])->name('lots.vendus');
        Route::get('/lots/client-search',    [LotController::class, 'clientSearch'])->name('lots.clientSearch');
        Route::get('/lots/client-panel/{id}',[LotController::class, 'clientPanel'])->name('lots.clientPanel');
        Route::get('/lots/{id}/hover-info',  [LotController::class, 'hoverInfo'])->name('lots.hoverInfo');
        Route::get('/lots/client-visites/{clientId}', [LotController::class, 'clientVisites'])->name('lots.clientVisites');
        Route::post('/lots/store',      [LotController::class, 'store'])->name('lots.store');
        Route::post('/lots/set-origin', [LotController::class, 'setOrigin'])->name('lots.setOrigin');
        Route::put('/lots/{id}',        [LotController::class, 'update'])->name('lots.update');
        Route::delete('/lots/{id}',     [LotController::class, 'destroy'])->name('lots.destroy');

        // ── ZONES GROUPES (CRUD complet) ──
        Route::post('/zone-groupes',         [ZoneGroupeController::class, 'store'])->name('zone-groupes.store');
        Route::put('/zone-groupes/{id}',     [ZoneGroupeController::class, 'update'])->name('zone-groupes.update');
        Route::delete('/zone-groupes/{id}',  [ZoneGroupeController::class, 'destroy'])->name('zone-groupes.destroy');
        Route::get('/zone-groupes/{id}/panel', [ZoneGroupeController::class, 'panel'])->name('zone-groupes.panel');

        // ── BLOCS & LOTS (Affectation - CRUD complet) ──
        Route::get('/affectations',                    [AffectationController::class, 'index'])->name('affectations.index');
        Route::get('/affectations/blocs',              [AffectationController::class, 'blocs'])->name('affectations.blocs');
        Route::get('/affectations/lots',               [AffectationController::class, 'lots'])->name('affectations.lots');
        Route::post('blocs',               [AffectationController::class, 'storeBloc'])->name('affectations.blocs.store');
        Route::put('blocs/{id}',           [AffectationController::class, 'updateBloc'])->name('affectations.blocs.update');
        Route::delete('blocs/{id}',        [AffectationController::class, 'destroyBloc'])->name('affectations.blocs.destroy');
        Route::post('lots',                [AffectationController::class, 'storeLots'])->name('affectations.lots.store');
        Route::put('lots/{id}',            [AffectationController::class, 'updateLot'])->name('affectations.lots.update');
        Route::delete('lots/{id}',         [AffectationController::class, 'destroyLot'])->name('affectations.lots.destroy');
        Route::post('lots/superficie-multiple', [AffectationController::class, 'updateSuperficieMultiple'])->name('affectations.lots.superficie-multiple');
        // ✅ IMPORT / EXPORT EXCEL DES LOTS
Route::get('/affectations/lots/export',   [AffectationController::class, 'exportLots'])      ->name('affectations.lots.export');
Route::get('/affectations/lots/template', [AffectationController::class, 'downloadTemplate'])->name('affectations.lots.template');
Route::post('/affectations/lots/import',  [AffectationController::class, 'importLots'])      ->name('affectations.lots.import');
        Route::post('/affectations/affecter/{dossier}',[AffectationController::class, 'affecter'])->name('affectations.affecter');
        Route::delete('/affectations/{affectation}',   [AffectationController::class, 'annuler'])->name('affectations.annuler');

        // ── API AFFECTATIONS ──
        Route::get('/affectations/api/sites/{grandSite}', [AffectationController::class, 'apiSites'])->name('affectations.api.sites');
        Route::get('/affectations/api/tfs/{site}',        [AffectationController::class, 'apiTfs'])->name('affectations.api.tfs');
        Route::get('/affectations/api/blocs/{tf}',        [AffectationController::class, 'apiBlocs'])->name('affectations.api.blocs');
        Route::get('/affectations/api/lots/{bloc}',       [AffectationController::class, 'apiLots'])->name('affectations.api.lots');

        // ── COMMERCIAUX (CRUD complet) ──
        Route::get('/commerciaux',         [CommercialController::class, 'index'])->name('commerciaux.index');
        Route::post('/commerciaux',        [CommercialController::class, 'store'])->name('commerciaux.store');
        Route::put('/commerciaux/{id}',    [CommercialController::class, 'update'])->name('commerciaux.update');
        Route::delete('/commerciaux/{id}', [CommercialController::class, 'destroy'])->name('commerciaux.destroy');

        // ── AGENTS COMMERCIAUX (CRUD complet) ──
        Route::get('/agents',              [AgentCommercialController::class, 'index'])->name('agents.index');
        Route::post('/agents',             [AgentCommercialController::class, 'store'])->name('agents.store');
        Route::put('/agents/{id}',         [AgentCommercialController::class, 'update'])->name('agents.update');
        Route::delete('/agents/{id}',      [AgentCommercialController::class, 'destroy'])->name('agents.destroy');

        // ── SUIVI CLIENT (CRUD complet) ──
        Route::get('/suivi-client',               [SuiviClientController::class, 'index'])->name('suivi-client.index');
        Route::get('/suivi-client/create',        [SuiviClientController::class, 'create'])->name('suivi-client.create');
        Route::post('/suivi-client',              [SuiviClientController::class, 'store'])->name('suivi-client.store');
        Route::get('/suivi-client/{id}',          [SuiviClientController::class, 'show'])->name('suivi-client.show');
        Route::get('/suivi-client/{id}/edit',     [SuiviClientController::class, 'edit'])->name('suivi-client.edit');
        Route::put('/suivi-client/{id}',          [SuiviClientController::class, 'update'])->name('suivi-client.update');
        Route::get('/suivi-client/{id}/dossiers', [SuiviClientController::class, 'dossiers'])->name('suivi-client.dossiers');
        Route::delete('/suivi-client/{id}',       [SuiviClientController::class, 'destroy'])->name('suivi-client.destroy');

        // ── SUIVI CLIENT - ACTIONS SPÉCIFIQUES ──
        Route::post('/suivi-client/toggle-new/{client}', [SuiviClientController::class, 'toggleNew'])->name('suivi-client.toggle-new');
        Route::post('/suivi-client/actions-group',       [SuiviClientController::class, 'actionsGroup'])->name('suivi-client.actions-group');
        Route::get('/suivi-client/export-pdf',           [SuiviClientController::class, 'exportPdf'])->name('suivi-client.export-pdf');
        Route::get('/suivi-client/export-pdf-selected',  [SuiviClientController::class, 'exportPdfSelected'])->name('suivi-client.export-pdf-selected');
        Route::get('/suivi-client/export-documents/{client}', [SuiviClientController::class, 'exportDocuments'])->name('suivi-client.export-documents');
        Route::post('/suivi-client/download-documents-zip',   [SuiviClientController::class, 'downloadDocumentsZip'])->name('suivi-client.download-documents-zip');

        // ── CLIENTS ──
        Route::post('/clients/{clientId}/modifier-nom', [SuiviClientController::class, 'modifierNom'])->name('clients.modifier-nom');

        // ── DOSSIERS ──
        Route::post('/dossiers/{dossierId}/maj-prix', [DossierClientController::class, 'majPrix'])->name('dossiers.maj-prix');
        Route::post('/dossiers/{dossier}/maj-etape',  [SuiviClientController::class, 'majEtape'])->name('dossiers.maj-etape');
        Route::get('/dossiers/{dossier}/cni',         [DossierClientController::class, 'getCni'])->name('dossiers.cni');
        Route::delete('/dossiers/{dossier}/cni',      [DossierClientController::class, 'deleteCniImage'])->name('dossiers.cni.delete');
        Route::delete('/dossiers/{dossier}/supprimer',[DossierClientController::class, 'supprimerDossier'])->name('dossiers.supprimer');
        Route::delete('/suivi-client/dossiers/{dossier}',[SuiviClientController::class, 'destroyDossier'])->name('suivi-client.dossiers.destroy');

        // ── EXPORT EXCEL ──
        Route::get('/dossiers/export-excel', [DossierClientController::class, 'exportExcel'])->name('dossiers.export-excel');

        // ── PAIEMENTS ──
        Route::post('/paiements-dossier/{dossierId}', [PaiementDossierController::class, 'store']);
        Route::get('/paiements-dossier/{dossierId}', [PaiementDossierController::class, 'index']);
        Route::post('/paiements-technique/{dossierId}', [PaiementTechniqueController::class, 'store']);
        Route::post('/paiements-morcellement/{dossierId}', [PaiementMorcellementController::class, 'store']);
        Route::delete('/admin/paiements-techniques/{id}', [PaiementTechniqueController::class, 'destroy'])->name('paiements-techniques.destroy');
        Route::delete('/admin/paiements-morcellements/{id}', [PaiementMorcellementController::class, 'destroy'])->name('paiements-morcellements.destroy');
        Route::delete('/admin/paiements-dossiers/{id}', [PaiementDossierController::class, 'destroy'])->name('paiements-dossiers.destroy');

        // ── BONS DE PAIEMENT ──
        Route::get('bons/{dossier}',          [BonPaiementController::class, 'index'])->name('bons.index');
        Route::post('bons/{dossier}',         [BonPaiementController::class, 'store'])->name('bons.store');
        Route::get('bons/{dossier}/creer',    [BonPaiementController::class, 'creer'])->name('bons.creer');
        Route::get('bons/detail/{bon}',       [BonPaiementController::class, 'show'])->name('bons.show');
        Route::get('bons/detail/{bon}/pdf',   [BonPaiementController::class, 'pdf'])->name('bons.pdf');
        Route::delete('bons/detail/{bon}',    [BonPaiementController::class, 'destroy'])->name('bons.destroy');
        Route::post('bons/detail/{bon}/toggle-reste', [BonPaiementController::class, 'toggleReste'])->name('bons.toggle-reste');

        // ── DOSSIER TECHNIQUE ──
        Route::get('/dossier/{lot}',               [DossierTechniqueController::class, 'show'])->name('dossier.show');
        Route::post('/dossier/toggle/{lot}',       [DossierTechniqueController::class, 'toggle'])->name('dossier.toggle');
        Route::get('/dossier-zone/{zone}',         [DossierTechniqueController::class, 'showZone'])->name('dossier.zone.show');
        Route::post('/dossier-zone/toggle/{zone}', [DossierTechniqueController::class, 'toggleZone'])->name('dossier.zone.toggle');

        // ── RAPPORTS ──
        Route::get('/rapport',              [RapportController::class, 'index'])->name('rapport.index');
        Route::post('/rapport/sauvegarder', [RapportController::class, 'sauvegarder'])->name('rapport.sauvegarder');
        Route::get('/rapport/liste',        [RapportController::class, 'liste'])->name('rapport.liste');
        Route::match(['get','post'],'/rapport/export',[RapportController::class, 'export'])->name('rapport.export');
        Route::delete('/rapport/{id}',      [RapportController::class, 'destroy'])->name('rapport.destroy');

        // ── VISITES ──
        Route::get('/visites',                 [VisiteController::class, 'index'])->name('visites.index');
        Route::post('/visites',                [VisiteController::class, 'store'])->name('visites.store');
        Route::put('/visites/{id}',            [VisiteController::class, 'update'])->name('visites.update');
        Route::delete('/visites/{id}',         [VisiteController::class, 'destroy'])->name('visites.destroy');
        Route::get('/visites/export',          [VisiteController::class, 'export'])->name('visites.export');
        Route::post('/visites/import',         [VisiteController::class, 'import'])->name('visites.import');
        Route::get('/visites/visiteur-search', [VisiteController::class, 'visiteurSearch'])->name('visites.visiteurSearch');

        // ── VERIFICATION RÉFÉRENCE ──
        Route::post('/verifier-reference', function (Request $request) {
            $reference = trim($request->input('reference'));
            $existe = User::where('reference', $reference)->exists();
            return response()->json([
                'existe' => $existe,
                'reference' => $reference
            ]);
        });

        // ── FEB ADMIN (admin uniquement) ──
        Route::get('/feb', [App\Http\Controllers\Feb\AdminController::class, 'index'])->name('admin.feb.index');
        Route::resource('feb/agences',      App\Http\Controllers\Feb\AgenceController::class)->names('admin.feb.agences');
        Route::resource('feb/colonnes',     App\Http\Controllers\Feb\ColonneController::class)->names('admin.feb.colonnes');
        Route::resource('feb/utilisateurs', App\Http\Controllers\Feb\UtilisateurController::class)->names('admin.feb.utilisateurs');
        Route::post('feb/utilisateurs/{id}/toggle', [App\Http\Controllers\Feb\UtilisateurController::class, 'toggle'])->name('admin.feb.utilisateurs.toggle');
        Route::get('feb/fiches',                  [App\Http\Controllers\Feb\AdminFicheController::class, 'index'])->name('admin.feb.fiches.index');
        Route::get('feb/fiches/{fiche}',          [App\Http\Controllers\Feb\AdminFicheController::class, 'show'])->name('admin.feb.fiches.show');
        Route::get('feb/fiches/{fiche}/pdf',      [App\Http\Controllers\Feb\AdminFicheController::class, 'pdf'])->name('admin.feb.fiches.pdf');
        Route::post('feb/fiches/{fiche}/marquer', [App\Http\Controllers\Feb\AdminFicheController::class, 'marquerVue'])->name('admin.feb.fiches.marquer');
    });

    // ═════════════════════════════════════════════════════════════════
    // ✅ 3. ROUTES RH UNIQUEMENT (admin + rh)
    // ═════════════════════════════════════════════════════════════════
    Route::prefix('rh')->middleware('check.role:admin,rh')->group(function () {

        // ── DASHBOARD RH ──
        Route::get('/dashboard', [DashboardRHController::class, 'index'])->name('rh.dashboard');

        // ── EMPLOYÉS ──
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

        // ── PAIE ──
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

        // ── ABSENCES ──
        Route::get('/absences',                   [AbsenceController::class, 'index'])->name('rh.absences.index');
        Route::post('/absences',                  [AbsenceController::class, 'store'])->name('rh.absences.store');
        Route::post('/absences/nettoyer-doublons', [AbsenceController::class, 'nettoyerAbsencesDoublons'])->name('rh.absences.nettoyer-doublons');
        Route::put('/absences/{id}',              [AbsenceController::class, 'update'])->name('rh.absences.update');
        Route::delete('/absences/{id}',           [AbsenceController::class, 'destroy'])->name('rh.absences.destroy');
        Route::post('/absences/{id}/approuver',   [AbsenceController::class, 'approuver'])->name('rh.absences.approuver');
        Route::post('/absences/{id}/refuser',     [AbsenceController::class, 'refuser'])->name('rh.absences.refuser');
        Route::get('/absences/liste-pdf',         [AbsenceController::class, 'pdfListe'])->name('rh.absences.pdf-liste');
        Route::get('/absences/{id}/pdf',          [AbsenceController::class, 'pdf'])->name('rh.absences.pdf');

        // ── PRÊTS ──
        Route::get('/prets',          [PretController::class, 'index'])->name('rh.prets.index');
        Route::post('/prets',         [PretController::class, 'store'])->name('rh.prets.store');
        Route::put('/prets/{id}',     [PretController::class, 'update'])->name('rh.prets.update');
        Route::delete('/prets/{id}',  [PretController::class, 'destroy'])->name('rh.prets.destroy');

        // ── SANCTIONS ──
        Route::get('/sanctions',              [SanctionController::class, 'index'])->name('rh.sanctions.index');
        Route::post('/sanctions',             [SanctionController::class, 'store'])->name('rh.sanctions.store');
        Route::get('/sanctions/pdf-liste',    [SanctionController::class, 'pdfListe'])->name('rh.sanctions.pdf-liste');
        Route::put('/sanctions/{id}',         [SanctionController::class, 'update'])->name('rh.sanctions.update');
        Route::delete('/sanctions/{id}',      [SanctionController::class, 'destroy'])->name('rh.sanctions.destroy');

        // ── RETARDS ──
        Route::get('/retards',             [RetardController::class, 'index'])->name('rh.retards.index');
        Route::post('/retards',            [RetardController::class, 'store'])->name('rh.retards.store');
        Route::get('/retards/pdf-liste',   [RetardController::class, 'pdfListe'])->name('rh.retards.pdf-liste');
        Route::get('/retards/nettoyer-doublons', [RetardController::class, 'nettoyerDoublons'])->name('rh.retards.nettoyer-doublons');
        Route::post('/retards/import-excel', [RetardController::class, 'importExcel'])->name('rh.retards.import-excel');
        Route::put('/retards/{id}',        [RetardController::class, 'update'])->name('rh.retards.update');
        Route::delete('/retards/{id}',     [RetardController::class, 'destroy'])->name('rh.retards.destroy');
        Route::get('/retards/par-direction',[RetardController::class, 'parDirection'])->name('rh.retards.par-direction');

        // ── DIRECTIONS ──
        Route::get('/directions',         [DirectionController::class, 'index'])->name('rh.directions.index');
        Route::post('/directions',        [DirectionController::class, 'storeDirection'])->name('rh.directions.store');
        Route::put('/directions/{id}',    [DirectionController::class, 'updateDirection'])->name('rh.directions.update');
        Route::delete('/directions/{id}', [DirectionController::class, 'destroyDirection'])->name('rh.directions.destroy');
        Route::post('/services',          [DirectionController::class, 'storeService'])->name('rh.services.store');
        Route::put('/services/{id}',      [DirectionController::class, 'updateService'])->name('rh.services.update');
        Route::delete('/services/{id}',   [DirectionController::class, 'destroyService'])->name('rh.services.destroy');
        Route::post('/postes',            [DirectionController::class, 'storePoste'])->name('rh.postes.store');
        Route::delete('/postes/{id}',     [DirectionController::class, 'destroyPoste'])->name('rh.postes.destroy');

        // ── CONGÉS ──
        Route::prefix('conges')->name('rh.conges.')->group(function () {
            Route::get('/', [CongeController::class, 'index'])->name('index');
            Route::post('/', [CongeController::class, 'store'])->name('store');
            Route::get('/planning-pdf', [CongeController::class, 'planningPdf'])->name('planning-pdf');
            Route::put('/{id}', [CongeController::class, 'update'])->name('update');
            Route::delete('/{id}', [CongeController::class, 'destroy'])->name('destroy');
            Route::get('/{id}/pdf', [CongeController::class, 'pdf'])->name('pdf');
        });

        // ── CONTRATS ──
        Route::resource('contrats', ContratController::class, [
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
        Route::get('contrats/{id}/pdf', [ContratController::class, 'pdf'])->name('rh.contrats.pdf');
        Route::post('contrats/{id}/valider', [ContratController::class, 'valider'])->name('rh.contrats.valider');
        Route::post('contrats/{id}/renouveler', [ContratController::class, 'renouveler'])->name('rh.contrats.renouveler');
        Route::post('contrats/{id}/resilier', [ContratController::class, 'resilier'])->name('rh.contrats.resilier');

        // ── CNPS ──
        Route::prefix('cnps')->name('rh.cnps.')->group(function () {
            Route::get('/', [CnpsController::class, 'index'])->name('index');
            Route::get('declarations', [CnpsController::class, 'declarations'])->name('declarations');
            Route::get('declarations/create', [CnpsController::class, 'createDeclaration'])->name('declarations.create');
            Route::post('declarations', [CnpsController::class, 'storeDeclaration'])->name('declarations.store');
            Route::get('declarations/{id}', [CnpsController::class, 'showDeclaration'])->name('declarations.show');
            Route::post('declarations/{id}/employes', [CnpsController::class, 'ajouterEmployes'])->name('declarations.ajouter-employes');
            Route::delete('declarations/{declarationId}/lignes/{ligneId}', [CnpsController::class, 'supprimerLigne'])->name('declarations.supprimer-ligne');
            Route::get('declarations/{id}/dipe', [CnpsController::class, 'generateDIPE'])->name('declarations.dipe');
            Route::post('declarations/{id}/statut', [CnpsController::class, 'changerStatut'])->name('declarations.statut');
            Route::get('affiliations', [CnpsController::class, 'affiliations'])->name('affiliations');
            Route::post('affiliations', [CnpsController::class, 'storeAffiliation'])->name('affiliations.store');
            Route::delete('affiliations/{id}', [CnpsController::class, 'destroyAffiliation'])->name('affiliations.destroy');
        });

        // ── DÉPARTS ──
        Route::prefix('departs')->name('rh.departs.')->group(function () {
            Route::get('/', [DepartController::class, 'index'])->name('index');
            Route::get('/create', [DepartController::class, 'create'])->name('create');
            Route::post('/', [DepartController::class, 'store'])->name('store');
            Route::get('/{id}', [DepartController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [DepartController::class, 'edit'])->name('edit');
            Route::put('/{id}', [DepartController::class, 'update'])->name('update');
            Route::delete('/{id}', [DepartController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/valider', [DepartController::class, 'valider'])->name('valider');
            Route::post('/{id}/annuler', [DepartController::class, 'annuler'])->name('annuler');
            Route::post('/{id}/generer-solde', [DepartController::class, 'genererSolde'])->name('generer-solde');
            Route::post('/{id}/generer-certificat', [DepartController::class, 'genererCertificat'])->name('generer-certificat');
            Route::get('/solde/{id}/pdf', [DepartController::class, 'pdfSolde'])->name('pdf-solde');
            Route::get('/certificat/{id}/pdf', [DepartController::class, 'pdfCertificat'])->name('pdf-certificat');
        });

        // ── ALERTES ──
        Route::prefix('alertes')->name('rh.alertes.')->group(function () {
            Route::get('/', [AlerteController::class, 'index'])->name('index');
            Route::post('/{id}/marquer-lu', [AlerteController::class, 'marquerLu'])->name('marquer-lu');
            Route::post('/{id}/marquer-traite', [AlerteController::class, 'marquerTraite'])->name('marquer-traite');
            Route::post('/{id}/marquer-ignore', [AlerteController::class, 'marquerIgnore'])->name('marquer-ignore');
            Route::post('/tout-marquer-lu', [AlerteController::class, 'toutMarquerLu'])->name('tout-marquer-lu');
            Route::delete('/{id}', [AlerteController::class, 'destroy'])->name('destroy');
            Route::get('/count-non-lu', [AlerteController::class, 'countNonLu'])->name('count-non-lu');
        });

        // ── HORAIRES ──
        Route::post('/horaires', [HoraireController::class, 'store'])->name('rh.horaires.store');

        // ── RECRUTEMENT ──
        Route::prefix('recrutement')->name('rh.recrutement.')->group(function () {
            Route::get('/', [RecrutementController::class, 'index'])->name('index');
            Route::get('/create', [RecrutementController::class, 'create'])->name('create');
            Route::post('/', [RecrutementController::class, 'store'])->name('store');
            Route::get('/export-pdf', [RecrutementController::class, 'pdfListe'])->name('export-pdf');
            Route::get('/{id}', [RecrutementController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [RecrutementController::class, 'edit'])->name('edit');
            Route::put('/{id}', [RecrutementController::class, 'update'])->name('update');
            Route::delete('/{id}', [RecrutementController::class, 'destroy'])->name('destroy');
            Route::post('/{id}/statut', [RecrutementController::class, 'changerStatut'])->name('statut');
            Route::post('/{id}/entretien', [RecrutementController::class, 'storeEntretien'])->name('entretien.store');
            Route::post('/{id}/test', [RecrutementController::class, 'storeTest'])->name('test.store');
            Route::get('/{id}/download/{type}', [RecrutementController::class, 'downloadDocument'])->name('download');
        });

        // ── SANTÉ & SÉCURITÉ ──
        Route::prefix('sante')->name('rh.sante.')->group(function () {
            Route::get('/', [SanteController::class, 'index'])->name('index');
            Route::get('/visites', [SanteController::class, 'visites'])->name('visites');
            Route::get('/visites/create', [SanteController::class, 'createVisite'])->name('visites.create');
            Route::post('/visites', [SanteController::class, 'storeVisite'])->name('visites.store');
            Route::get('/visites/pdf', [SanteController::class, 'pdfVisites'])->name('visites.pdf');
            Route::get('/visites/{id}', [SanteController::class, 'showVisite'])->name('visites.show');
            Route::get('/visites/{id}/edit', [SanteController::class, 'editVisite'])->name('visites.edit');
            Route::put('/visites/{id}', [SanteController::class, 'updateVisite'])->name('visites.update');
            Route::delete('/visites/{id}', [SanteController::class, 'destroyVisite'])->name('visites.destroy');
            Route::get('/accidents', [SanteController::class, 'accidents'])->name('accidents');
            Route::get('/accidents/create', [SanteController::class, 'createAccident'])->name('accidents.create');
            Route::post('/accidents', [SanteController::class, 'storeAccident'])->name('accidents.store');
            Route::get('/accidents/{id}', [SanteController::class, 'showAccident'])->name('accidents.show');
            Route::get('/accidents/{id}/edit', [SanteController::class, 'editAccident'])->name('accidents.edit');
            Route::put('/accidents/{id}', [SanteController::class, 'updateAccident'])->name('accidents.update');
            Route::delete('/accidents/{id}', [SanteController::class, 'destroyAccident'])->name('accidents.destroy');
            Route::get('/trousses', [SanteController::class, 'trousses'])->name('trousses');
            Route::post('/trousses', [SanteController::class, 'storeTrousse'])->name('trousses.store');
            Route::put('/trousses/{id}', [SanteController::class, 'updateTrousse'])->name('trousses.update');
            Route::delete('/trousses/{id}', [SanteController::class, 'destroyTrousse'])->name('trousses.destroy');
        });
    });
});

// ════════════════════════════════════════════════════════════════
// BÉNÉFICIAIRES (répartition de dossier)
// ════════════════════════════════════════════════════════════════
Route::prefix('admin')->middleware(['auth'])->group(function () {
    Route::post  ('dossiers/{dossier}/beneficiaires',
                  [BeneficiaireController::class, 'store'])
                  ->name('beneficiaires.store');
    Route::put   ('beneficiaires/{beneficiaire}',
                  [BeneficiaireController::class, 'update'])
                  ->name('beneficiaires.update');
    Route::delete('beneficiaires/{beneficiaire}',
                  [BeneficiaireController::class, 'destroy'])
                  ->name('beneficiaires.destroy');
     Route::delete('affectations/{affectation}',
                  [AffectationController::class, 'destroy'])
                  ->name('affectations.destroy');

    // ✅ Historique d'un dossier (API)
    Route::get('dossiers/{dossier}/historique',
               [AffectationController::class, 'historique'])
               ->name('dossiers.historique');
    // ✅ Étapes d'un bénéficiaire
    Route::post('beneficiaires/{beneficiaire}/maj-etape',
                [BeneficiaireController::class, 'majEtape'])
                ->name('beneficiaires.maj-etape');

    // ✅ Affectation de lots à un bénéficiaire
    Route::post('beneficiaires/{beneficiaire}/affecter-lots',
                [AffectationController::class, 'affecterBeneficiaire'])
                ->name('beneficiaires.affecter-lots');
});

// ═══════════════════════════════════════════════════════════════════
// MODULE FEB — Espace utilisateur (accessible à tous les rôles)
// ═══════════════════════════════════════════════════════════════════
Route::prefix('feb')->name('feb.')->group(function () {

    // Auth FEB
    Route::get('login',  [App\Http\Controllers\Feb\AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [App\Http\Controllers\Feb\AuthController::class, 'login'])->name('login.post');
    Route::post('logout',[App\Http\Controllers\Feb\AuthController::class, 'logout'])->name('logout');

    Route::middleware('feb.auth')->group(function () {
        Route::get('/',      [App\Http\Controllers\Feb\FicheController::class, 'index'])->name('index');
        Route::get('fiches', [App\Http\Controllers\Feb\FicheController::class, 'index'])->name('fiches.index');

        // Destinataires
        Route::get('destinataires', [DestinataireController::class, 'index'])->name('destinataires.index');
        Route::get('destinataires/search', [DestinataireController::class, 'search'])->name('destinataires.search');
        Route::post('destinataires', [DestinataireController::class, 'store'])->name('destinataires.store');
        Route::delete('destinataires/{id}', [DestinataireController::class, 'destroy'])->name('destinataires.destroy');

        // Fiches - routes fixes AVANT les routes avec paramètres
        Route::get('fiches/creer',            [App\Http\Controllers\Feb\FicheController::class, 'creer'])->name('fiches.creer');
        Route::post('fiches/creer-soumettre', [App\Http\Controllers\Feb\FicheController::class, 'creerEtSoumettre'])->name('fiches.creer-soumettre');

        // Routes avec paramètres APRÈS
        Route::get('fiches/{fiche}/continuer',[App\Http\Controllers\Feb\FicheController::class, 'continuer'])->name('fiches.continuer');
        Route::get('fiches/{fiche}/utiliser', [App\Http\Controllers\Feb\FicheController::class, 'utiliserModele'])->name('fiches.utiliser');
        Route::get('fiches/{fiche}/pdf',      [App\Http\Controllers\Feb\FicheController::class, 'pdf'])->name('fiches.pdf');
    });
});