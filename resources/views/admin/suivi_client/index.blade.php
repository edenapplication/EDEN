@extends('admin.layout')
@section('content')

<style>
.client-card {
    background:white; border-radius:12px; padding:16px; margin-bottom:10px;
    box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #1d4ed8; transition:0.2s;
}
.client-card:hover { box-shadow:0 6px 20px rgba(0,0,0,0.1); transform:translateY(-2px); }
.client-card.selected {
    border-left-color: #16a34a;
    background: #f0fdf4;
}

.filtre-box { background:white; border-radius:12px; padding:14px; margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
.dossier-pill {
    display:inline-flex; align-items:center; gap:6px;
    background:#f1f5f9; border-radius:8px; padding:4px 10px;
    font-size:11px; color:#374151; margin-right:6px; margin-top:4px;
}
#search-live { font-size:15px; padding:10px 16px; border-radius:10px; border:2px solid #e2e8f0; }
#search-live:focus { border-color:#1d4ed8; outline:none; }
.highlight { background:#fef9c3; border-radius:3px; padding:0 2px; }

/* BADGE NOUVEAU */
.badge-new {
    background: #10b981; color: #fff;
    padding: 4px 12px; border-radius: 50px;
    font-size: 11px; font-weight: 700;
    text-transform: uppercase;
    animation: pulse-new 2s ease-in-out infinite;
    display: inline-block; margin-left: 8px;
}
.badge-old {
    background: #e2e8f0; color: #64748b;
    padding: 4px 12px; border-radius: 50px;
    font-size: 11px; font-weight: 600;
    display: inline-block; margin-left: 8px;
}

/* BADGE SEXE */
.badge-sex-masculin {
    background: #dbeafe; color: #1d4ed8;
    padding: 2px 10px; border-radius: 50px;
    font-size: 10px; font-weight: 600;
    display: inline-block; margin-left: 4px;
}
.badge-sex-feminin {
    background: #fce4ec; color: #dc2626;
    padding: 2px 10px; border-radius: 50px;
    font-size: 10px; font-weight: 600;
    display: inline-block; margin-left: 4px;
}

@keyframes pulse-new {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.8; }
}

.btn-toggle-new {
    font-size: 11px; padding: 4px 12px;
    border-radius: 6px; border: 1px solid #e2e8f0;
    background: #f8fafc; cursor: pointer;
    transition: all 0.2s; font-weight: 600;
}
.btn-toggle-new:hover { background: #f1f5f9; }
.btn-toggle-new.is-new {
    background: #10b981; color: #fff; border-color: #10b981;
}

/* BARRE D'ACTIONS GROUPÉES */
.action-bar {
    background: white; border-radius: 12px;
    padding: 12px 16px; margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: none; align-items: center; gap: 12px;
    flex-wrap: wrap; border: 2px solid #16a34a;
    position: sticky; top: 0; z-index: 100;
}
.action-bar.visible { display: flex; }
.action-bar .count { font-weight: 700; color: #16a34a; font-size: 14px; }
.action-bar .btn-group { display: flex; gap: 6px; flex-wrap: wrap; }
.action-bar .btn {
    font-size: 12px; padding: 6px 14px;
    border-radius: 6px; font-weight: 600;
    border: none; cursor: pointer;
    transition: all 0.2s;
}
.action-bar .btn-primary { background: #1d4ed8; color: #fff; }
.action-bar .btn-success { background: #16a34a; color: #fff; }
.action-bar .btn-danger { background: #dc2626; color: #fff; }
.action-bar .btn-warning { background: #f59e0b; color: #fff; }
.action-bar .btn-info { background: #0891b2; color: #fff; }
.action-bar .btn-outline { background: transparent; border: 1.5px solid #e2e8f0; color: #64748b; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:500px; max-width:95%; }

.toast-notification {
    position: fixed; bottom: 20px; right: 20px;
    background: #1f2937; color: #fff;
    padding: 12px 20px; border-radius: 8px;
    font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 9999; max-width: 400px;
    animation: slideInToast 0.3s ease;
}
.toast-notification.success { background: #16a34a; }
.toast-notification.error { background: #dc2626; }
.toast-notification.warning { background: #f59e0b; }
.toast-notification.info { background: #0891b2; }

@keyframes slideInToast {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

.client-checkbox {
    width: 18px; height: 18px; cursor: pointer;
    accent-color: #1d4ed8; margin-right: 8px; flex-shrink: 0;
}

/* BADGES LOTS AFFECTÉS */
.lots-badge-container {
    display: flex; flex-wrap: wrap; gap: 4px;
    margin-top: 6px; align-items: center;
}
.lot-badge {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 8px; border-radius: 10px;
    font-size: 9px; font-weight: 700;
    background: #f0fdf4; color: #15803d;
    border: 1px solid #86efac; white-space: nowrap;
}
.lot-badge.benef { background: #faf5ff; color: #7c3aed; border-color: #c4b5fd; }
.lot-badge.dossier { background: #eff6ff; color: #1d4ed8; border-color: #93c5fd; }
.lots-count-badge {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 8px; border-radius: 10px;
    font-size: 9px; font-weight: 700;
    background: #dcfce7; color: #15803d;
    border: 1.5px solid #86efac;
}

/* BÉNÉFICIAIRES INDÉPENDANTS */
.benef-section {
    margin-top: 10px; padding: 12px;
    background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
    border: 1.5px solid #e9d5ff; border-radius: 10px;
}
.benef-section-header {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 8px;
}
.benef-section-title {
    font-size: 11px; font-weight: 700; color: #7c3aed;
    text-transform: uppercase;
    display: flex; align-items: center; gap: 6px;
}
.benef-section-count {
    background: #7c3aed; color: white;
    padding: 1px 8px; border-radius: 10px;
    font-size: 9px; font-weight: 700;
}
.benef-bulk-actions { display: flex; gap: 4px; }
.benef-bulk-btn {
    font-size: 9px; padding: 2px 8px; border-radius: 5px;
    border: 1px solid #c4b5fd; background: white;
    color: #7c3aed; cursor: pointer; font-weight: 600;
}
.benef-bulk-btn:hover { background: #ede9fe; }

.benef-card {
    display: flex; align-items: flex-start; gap: 8px;
    padding: 10px 12px; background: white;
    border: 1.5px solid #c4b5fd; border-radius: 8px;
    margin-bottom: 8px; transition: all 0.2s;
    cursor: pointer;
}
.benef-card:hover {
    border-color: #7c3aed;
    box-shadow: 0 2px 8px rgba(124, 58, 237, 0.12);
    transform: translateX(2px);
}
.benef-card.selected {
    background: #ede9fe;
    border-color: #7c3aed;
    box-shadow: 0 0 0 2px rgba(124, 58, 237, 0.2);
}

.benef-checkbox {
    width: 16px; height: 16px; cursor: pointer;
    accent-color: #7c3aed; flex-shrink: 0; margin-top: 8px;
}

.benef-avatar {
    width: 36px; height: 36px; border-radius: 50%;
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: white; display: flex; align-items: center;
    justify-content: center; font-weight: 700;
    font-size: 13px; flex-shrink: 0;
}

.benef-info { flex: 1; min-width: 0; }

.benef-nom {
    font-size: 13px; font-weight: 700; color: #1e3a5f;
    display: flex; align-items: center; gap: 5px;
    flex-wrap: wrap;
}

.benef-detail-badges {
    display: flex; flex-wrap: wrap;
    gap: 4px; margin-top: 6px;
}
.benef-detail-badges .badge-site {
    background: linear-gradient(135deg, #dbeafe, #bfdbfe);
    color: #1d4ed8; padding: 2px 8px;
    border-radius: 6px; font-weight: 700;
    border: 1px solid #93c5fd;
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 9px;
}
.benef-detail-badges .badge-tf {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    color: #92400e; padding: 2px 8px;
    border-radius: 6px; font-weight: 700;
    border: 1px solid #fcd34d;
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 9px;
}
.benef-detail-badges .badge-bloc {
    background: linear-gradient(135deg, #fce7f3, #fbcfe8);
    color: #9d174d; padding: 2px 8px;
    border-radius: 6px; font-weight: 700;
    border: 1px solid #f9a8d4;
    display: inline-flex; align-items: center; gap: 3px;
    font-size: 9px;
}
.benef-detail-badges .badge-lot {
    background: linear-gradient(135deg, #dcfce7, #bbf7d0);
    color: #166534; padding: 2px 6px;
    border-radius: 6px; font-weight: 700;
    border: 1px solid #86efac;
    display: inline-flex; align-items: center; gap: 2px;
    font-size: 9px;
}
.benef-detail-badges .badge-superficie {
    background: linear-gradient(135deg, #7c3aed, #a855f7);
    color: white; padding: 3px 10px;
    border-radius: 8px; font-weight: 800;
    font-size: 10px;
    box-shadow: 0 2px 6px rgba(124,58,237,0.3);
    display: inline-flex; align-items: center; gap: 3px;
}
.benef-detail-badges .badge-count {
    background: #f1f5f9; color: #64748b;
    padding: 2px 6px; border-radius: 6px;
    font-weight: 600; font-size: 9px;
    display: inline-flex; align-items: center; gap: 2px;
}

.benef-meta {
    font-size: 10px; color: #64748b;
    display: flex; gap: 8px; flex-wrap: wrap;
    margin-top: 4px;
}
.benef-meta-item {
    display: inline-flex; align-items: center; gap: 3px;
}
.benef-meta-item strong { color: #7c3aed; }

.benef-actions {
    display: flex; gap: 3px; flex-shrink: 0;
    margin-top: 4px;
}
.benef-action-btn {
    width: 26px; height: 26px; border-radius: 6px;
    border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    font-size: 12px; transition: all 0.2s;
}
.benef-action-btn.whatsapp { background: #dcfce7; color: #16a34a; }
.benef-action-btn.whatsapp:hover { background: #16a34a; color: white; transform: scale(1.1); }
.benef-action-btn.view { background: #dbeafe; color: #1d4ed8; }
.benef-action-btn.view:hover { background: #1d4ed8; color: white; transform: scale(1.1); }

.benef-etape-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #16a34a; display: inline-block;
    box-shadow: 0 0 0 2px #dcfce7; flex-shrink: 0;
}
.benef-etape-dot.vide {
    background: #cbd5e1;
    box-shadow: 0 0 0 2px #f1f5f9;
}

.benef-etape-pill {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 1px 6px; border-radius: 8px;
    font-size: 9px; font-weight: 700;
}

.selection-summary {
    display: flex; gap: 12px; align-items: center;
    font-size: 11px; color: #64748b;
    padding: 6px 12px; background: #f8fafc;
    border-radius: 8px; margin-bottom: 8px;
}
.selection-summary .badge-count {
    background: #dbeafe; color: #1d4ed8;
    padding: 2px 8px; border-radius: 10px;
    font-weight: 700; font-size: 10px;
}
.selection-summary .badge-count.benef {
    background: #faf5ff; color: #7c3aed;
}

/* MODAL ÉTAPE GROUPÉE */
.etape-groupee-label {
    display:inline-flex; align-items:center; gap:6px;
    background:white; border:2px solid #e2e8f0;
    border-radius:8px; padding:8px 14px;
    cursor:pointer; font-size:12px; font-weight:700;
    color:#64748b; transition:all 0.2s;
}
</style>

{{-- EN-TÊTE --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">👤 Suivi Clients</h2>
        <div style="font-size:13px;color:#64748b;" id="compteur-clients">
            {{ $clients->count() }} client(s)
            <span style="margin-left:10px;color:#10b981;">
                🆕 {{ $clients->where('is_new', true)->count() }} nouveau(x)
            </span>
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button onclick="selectionnerTout()" class="btn btn-outline-secondary btn-sm">
            ☑ Sélectionner tout
        </button>
        <button onclick="deselectionnerTout()" class="btn btn-outline-secondary btn-sm">
            ☐ Désélectionner
        </button>
        <a href="{{ route('dossiers.export-excel', request()->all()) }}" class="btn btn-success btn-sm">
            📥 Export Excel (filtres)
        </a>
        <button onclick="exporterPdf()" class="btn btn-danger btn-sm" title="Exporter en PDF">
            📄 PDF
        </button>
        <a href="{{ route('suivi-client.create') }}" class="btn btn-primary btn-sm">+ Nouveau client</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- BARRE D'ACTIONS GROUPÉES --}}
<div class="action-bar" id="actionBar">
    <span class="count" id="selectedCount">0</span>
    <span style="font-size:13px;color:#64748b;">élément(s) sélectionné(s)</span>
    <div class="btn-group">
        <span style="font-size:10px;color:#94a3b8;align-self:center;margin-right:4px;">Clients :</span>
        <button class="btn btn-success" onclick="actionGroupee('mark_as_new')">🆕 Nouveaux</button>
        <button class="btn btn-warning" onclick="actionGroupee('mark_as_old')">📌 Anciens</button>
        
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary btn-sm dropdown-toggle" 
                    data-bs-toggle="dropdown" aria-expanded="false">
                👤 Sexe
            </button>
            <ul class="dropdown-menu">
                <li><button class="dropdown-item" onclick="actionGroupee('set_masculin')">👨 Masculin</button></li>
                <li><button class="dropdown-item" onclick="actionGroupee('set_feminin')">👩 Féminin</button></li>
            </ul>
        </div>

        {{-- ✅ ÉTAPE D'AVANCEMENT --}}
        <button class="btn" style="background:#f59e0b;color:white;" onclick="ouvrirModalEtapeGroupee()">
            📊 Étape
        </button>

        <button class="btn btn-primary" onclick="actionGroupee('export_whatsapp')">💬 WhatsApp</button>
        <button class="btn btn-success" onclick="actionGroupee('export_excel_selected')">📥 Excel sélection</button>
        <button class="btn btn-danger" onclick="actionGroupee('export_pdf_selected')">📄 PDF sélection</button>
        <button class="btn btn-info" onclick="actionGroupee('export_documents')">📁 Docs</button>
        <button class="btn btn-danger" onclick="actionGroupee('delete')">🗑 Supprimer</button>
        <button class="btn btn-outline" onclick="deselectionnerTout()">✖ Annuler</button>
    </div>
</div>

{{-- RÉSUMÉ SÉLECTION --}}
<div class="selection-summary" id="selectionSummary" style="display:none;">
    <span>📊 Sélection :</span>
    <span class="badge-count" id="count-clients">0 client(s)</span>
    <span class="badge-count benef" id="count-benefs">0 bénéficiaire(s)</span>
</div>

{{-- FILTRES --}}
<div class="filtre-box">
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;">🔍 Recherche</label>
            <input type="text" id="search-live" class="form-control form-control-sm"
                   placeholder="Nom ou téléphone..."
                   value="{{ request('q') }}"
                   oninput="filtrerClients(this.value)">
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Statut</label>
            <select id="filtre-statut" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>🆕 Nouveaux</option>
                <option value="old" {{ request('status') == 'old' ? 'selected' : '' }}>Anciens</option>
            </select>
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Du</label>
            <input type="date" name="du" id="filtre-du" class="form-control form-control-sm" value="{{ request('du') }}">
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Au</label>
            <input type="date" name="au" id="filtre-au" class="form-control form-control-sm" value="{{ request('au') }}">
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Grand Site</label>
            <select id="filtre-site" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                @foreach($grandSites as $gs)
                    <option value="{{ $gs->id }}" {{ request('grand_site_id')==$gs->id?'selected':'' }}>
                        {{ $gs->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">👤 Sexe</label>
            <select id="filtre-sexe" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="masculin" {{ request('sexe') == 'masculin' ? 'selected' : '' }}>👨 Masculin</option>
                <option value="feminin" {{ request('sexe') == 'feminin' ? 'selected' : '' }}>👩 Féminin</option>
            </select>
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">🛠️ Technique</label>
            <select id="filtre-technique" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="solde" {{ request('technique_solde') == 'solde' ? 'selected' : '' }}>✅ Soldé</option>
                <option value="non_solde" {{ request('technique_solde') == 'non_solde' ? 'selected' : '' }}>⏳ Non soldé</option>
            </select>
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">✂️ Morcel.</label>
            <select id="filtre-morcellement" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="solde" {{ request('morcellement_solde') == 'solde' ? 'selected' : '' }}>✅ Soldé</option>
                <option value="non_solde" {{ request('morcellement_solde') == 'non_solde' ? 'selected' : '' }}>⏳ Non soldé</option>
            </select>
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">📁 Dossier</label>
            <select id="filtre-dossier" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="solde" {{ request('dossier_solde') == 'solde' ? 'selected' : '' }}>✅ Soldé</option>
                <option value="non_solde" {{ request('dossier_solde') == 'non_solde' ? 'selected' : '' }}>⏳ Non soldé</option>
            </select>
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">🚗 Logi.</label>
            <select id="filtre-logistique" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="solde" {{ request('logistique_solde') == 'solde' ? 'selected' : '' }}>✅ Soldé</option>
                <option value="non_solde" {{ request('logistique_solde') == 'non_solde' ? 'selected' : '' }}>⏳ Non soldé</option>
            </select>
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">📦 Lots</label>
            <select id="filtre-lots" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="avec" {{ request('lots') == 'avec' ? 'selected' : '' }}>✅ Avec lots</option>
                <option value="sans" {{ request('lots') == 'sans' ? 'selected' : '' }}>⭕ Sans lots</option>
            </select>
        </div>
        <div class="col-md-1 d-flex gap-1">
            <button onclick="appliquerFiltresServeur()" class="btn btn-primary btn-sm">🔍</button>
            <a href="{{ route('suivi-client.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
    </div>
</div>

{{-- LISTE --}}
<div id="liste-clients">
    @forelse($clients as $client)
    <div class="client-card"
         data-nom="{{ strtolower($client->name) }}"
         data-phone="{{ $client->phone }}"
         data-is-new="{{ $client->is_new ? 'true' : 'false' }}"
         data-sex="{{ $client->sexe ?? 'non_renseigne' }}"
         data-id="{{ $client->id }}">
        <div class="d-flex justify-content-between align-items-start">
            <div style="flex:1;display:flex;align-items:flex-start;gap:8px;">
                <input type="checkbox" class="client-checkbox" 
                       onchange="toggleSelection(this, {{ $client->id }})"
                       data-client-id="{{ $client->id }}">
                       
                <div style="flex:1;">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <div style="font-weight:700;font-size:15px;color:#1e3a5f;"
                             id="nom-{{ $client->id }}" class="client-nom">
                            {{ $client->name }}
                        </div>
                        
                        @if($client->is_new)
                            <span class="badge-new" id="badge-{{ $client->id }}">🆕 Nouveau</span>
                        @else
                            <span class="badge-old" id="badge-{{ $client->id }}">Ancien</span>
                        @endif
                        
                        @if($client->sexe == 'masculin')
                            <span class="badge-sex-masculin">👨 Masculin</span>
                        @elseif($client->sexe == 'feminin')
                            <span class="badge-sex-feminin">👩 Féminin</span>
                        @endif
                        
                        <button onclick="ouvrirEditNom({{ $client->id }}, '{{ addslashes($client->name) }}')"
                                style="background:none;border:none;color:#f59e0b;cursor:pointer;font-size:13px;padding:2px 6px;"
                                title="Modifier le nom">✏️</button>
                        
                        <button class="btn-toggle-new {{ $client->is_new ? 'is-new' : '' }}"
                                onclick="toggleNew({{ $client->id }})"
                                id="btn-new-{{ $client->id }}">
                            @if($client->is_new) ✅ Nouveau @else 🔄 Marquer nouveau @endif
                        </button>

                        <button onclick="envoyerWhatsAppClient({{ $client->id }})"
                                class="btn btn-sm"
                                style="background:#dcfce7;color:#16a34a;font-size:11px;padding:3px 10px;border:none;border-radius:6px;font-weight:600;"
                                title="Envoyer WhatsApp">
                            💬 WhatsApp
                        </button>
                    </div>
                    
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">
                        📞 {{ $client->phone ?? '-' }}
                        &nbsp;·&nbsp; 📂 <span id="nb-dossiers-{{ $client->id }}">{{ $client->dossiers->count() }}</span> dossier(s)
                        &nbsp;·&nbsp; 📅 {{ $client->created_at?->format('d/m/Y') ?? '-' }}
                    </div>

                    {{-- ═══════════════════════════════════════════════════════════
                         📦 LOTS AFFECTÉS (dossier) — Résumé rapide
                         ═══════════════════════════════════════════════════════════ --}}
                    @php
                        $lotsDossierTotal = 0;
                        $lotsBenefTotal = 0;
                        foreach ($client->dossiers as $d) {
                            $lotsDossierTotal += $d->affectations->whereNull('beneficiaire_id')->count();
                            $lotsBenefTotal   += $d->affectations->whereNotNull('beneficiaire_id')->count();
                        }
                    @endphp

                    @if($lotsDossierTotal + $lotsBenefTotal > 0)
                    <div class="lots-badge-container" style="margin-top:6px;">
                        <span class="lots-count-badge">
                            📦 {{ $lotsDossierTotal + $lotsBenefTotal }} lot(s) au total
                        </span>
                        @if($lotsDossierTotal > 0)
                            <span class="lot-badge dossier" style="font-size:8px;">
                                Dossier : {{ $lotsDossierTotal }}
                            </span>
                        @endif
                        @if($lotsBenefTotal > 0)
                            <span class="lot-badge benef" style="font-size:8px;">
                                Bénéf. : {{ $lotsBenefTotal }}
                            </span>
                        @endif
                    </div>
                    @endif

                    {{-- ═══════════════════════════════════════════════════════════
     💰 STATUTS DE PAIEMENT PAR DOSSIER (CÔTE À CÔTE)
     ═══════════════════════════════════════════════════════════ --}}
@if($client->dossiers->count() > 0)
<div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));gap:10px;margin-top:8px;">
    @foreach($client->dossiers as $d)
        @php
            // Totaux payés
            $tD = $d->paiements->sum('montant');
            $tT = $d->paiementsTechniques->sum('montant');
            $tM = $d->paiementsMorcellements->sum('montant');
            $tL = $d->paiementsLogistiques?->sum('montant') ?? 0;

            // Prix de référence
            $rD = $d->prix_superficie    ?? 0;
            $rT = $d->prix_technique     ?? 0;
            $rM = $d->prix_morcellement  ?? 0;
            $rL = $d->prix_logistique    ?? 0;

            // Statuts
            $statutD = $rD > 0 ? ($tD >= $rD ? 'solde' : ($tD > 0 ? 'en_cours' : 'vide')) : ($tD > 0 ? 'en_cours' : 'vide');
            $statutT = $rT > 0 ? ($tT >= $rT ? 'solde' : ($tT > 0 ? 'en_cours' : 'vide')) : ($tT > 0 ? 'en_cours' : 'vide');
            $statutM = $rM > 0 ? ($tM >= $rM ? 'solde' : ($tM > 0 ? 'en_cours' : 'vide')) : ($tM > 0 ? 'en_cours' : 'vide');
            $statutL = $rL > 0 ? ($tL >= $rL ? 'solde' : ($tL > 0 ? 'en_cours' : 'vide')) : ($tL > 0 ? 'en_cours' : 'vide');

            $couleurs = [
                'solde'    => ['bg' => '#dcfce7', 'border' => '#86efac', 'text' => '#15803d', 'icone' => '✅'],
                'en_cours' => ['bg' => '#fef3c7', 'border' => '#fcd34d', 'text' => '#b45309', 'icone' => '⏳'],
                'vide'     => ['bg' => '#f1f5f9', 'border' => '#cbd5e1', 'text' => '#64748b', 'icone' => '⭕']
            ];

            $totalPaye = $tD + $tT + $tL + $tM;
            $totalRef  = $rD + $rT + $rL + $rM;
            $tousSoldes = ($statutD === 'solde' || $tD == 0)
                       && ($statutT === 'solde' || $tT == 0)
                       && ($statutL === 'solde' || $tL == 0)
                       && ($statutM === 'solde' || $tM == 0);
            $pctGlobal = $totalRef > 0 ? round(($totalPaye / $totalRef) * 100) : 0;
        @endphp

        <div style="background:#f8fafc;border-radius:8px;padding:10px 12px;border:1px solid #e2e8f0;">

            {{-- En-tête dossier --}}
            <div style="font-size:11px;color:#1e3a5f;font-weight:700;margin-bottom:8px;
                        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
                 title="{{ $d->nom_dossier }}">
                📂 {{ $d->nom_dossier }}
                <span style="color:#6b7280;font-weight:normal;font-size:10px;">
                    ({{ $d->created_at?->format('d/m/Y') ?? '-' }})
                    · {{ $d->superficie_voulue ? number_format($d->superficie_voulue, 0, ',', ' ') . ' m²' : '-' }}
                </span>
            </div>

            {{-- Statuts de paiement (compact) --}}
            <div style="display:flex;flex-wrap:wrap;gap:4px;">

                {{-- Dossier --}}
                @if($tD > 0 || $rD > 0)
                <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:10px;font-size:9px;font-weight:700;background:{{ $couleurs[$statutD]['bg'] }};color:{{ $couleurs[$statutD]['text'] }};border:1px solid {{ $couleurs[$statutD]['border'] }};">
                    {{ $couleurs[$statutD]['icone'] }} 📁
                    @if($statutD === 'solde')
                        <span style="background:#15803d22;padding:0 5px;border-radius:6px;">SOLDÉ</span>
                    @elseif($statutD === 'en_cours')
                        <span>{{ $rD > 0 ? number_format(round(($tD/$rD)*100)) . '%' : '' }}</span>
                    @endif
                </span>
                @endif

                {{-- Technique --}}
                @if($tT > 0 || $rT > 0)
                <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:10px;font-size:9px;font-weight:700;background:{{ $couleurs[$statutT]['bg'] }};color:{{ $couleurs[$statutT]['text'] }};border:1px solid {{ $couleurs[$statutT]['border'] }};">
                    {{ $couleurs[$statutT]['icone'] }} 🛠️
                    @if($statutT === 'solde')
                        <span style="background:#15803d22;padding:0 5px;border-radius:6px;">SOLDÉ</span>
                    @elseif($statutT === 'en_cours')
                        <span>{{ $rT > 0 ? number_format(round(($tT/$rT)*100)) . '%' : '' }}</span>
                    @endif
                </span>
                @endif

                {{-- Logistique --}}
                @if($tL > 0 || $rL > 0)
                <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:10px;font-size:9px;font-weight:700;background:{{ $couleurs[$statutL]['bg'] }};color:{{ $couleurs[$statutL]['text'] }};border:1px solid {{ $couleurs[$statutL]['border'] }};">
                    {{ $couleurs[$statutL]['icone'] }} 🚗
                    @if($statutL === 'solde')
                        <span style="background:#15803d22;padding:0 5px;border-radius:6px;">SOLDÉ</span>
                    @elseif($statutL === 'en_cours')
                        <span>{{ $rL > 0 ? number_format(round(($tL/$rL)*100)) . '%' : '' }}</span>
                    @endif
                </span>
                @endif

                {{-- Morcellement --}}
                @if($tM > 0 || $rM > 0)
                <span style="display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:10px;font-size:9px;font-weight:700;background:{{ $couleurs[$statutM]['bg'] }};color:{{ $couleurs[$statutM]['text'] }};border:1px solid {{ $couleurs[$statutM]['border'] }};">
                    {{ $couleurs[$statutM]['icone'] }} ✂️
                    @if($statutM === 'solde')
                        <span style="background:#15803d22;padding:0 5px;border-radius:6px;">SOLDÉ</span>
                    @elseif($statutM === 'en_cours')
                        <span>{{ $rM > 0 ? number_format(round(($tM/$rM)*100)) . '%' : '' }}</span>
                    @endif
                </span>
                @endif
            </div>

            {{-- Barre de progression globale --}}
            @if($totalRef > 0)
            <div style="margin-top:6px;padding:5px 10px;border-radius:6px;background:{{ $tousSoldes ? '#dcfce7' : ($totalPaye > 0 ? '#fef3c7' : '#f1f5f9') }};border:1px solid {{ $tousSoldes ? '#86efac' : ($totalPaye > 0 ? '#fcd34d' : '#cbd5e1') }};display:flex;justify-content:space-between;align-items:center;font-size:10px;">
                <span style="font-weight:700;color:{{ $tousSoldes ? '#15803d' : ($totalPaye > 0 ? '#b45309' : '#64748b') }};">
                    {{ $tousSoldes ? '✅ SOLDÉ' : ($totalPaye > 0 ? '⏳ ' . $pctGlobal . '%' : '⭕ NON PAYÉ') }}
                </span>
                <span style="font-weight:700;color:{{ $tousSoldes ? '#15803d' : ($totalPaye > 0 ? '#b45309' : '#64748b') }};font-size:9px;">
                    {{ number_format($totalPaye, 0, ',', ' ') }} / {{ number_format($totalRef, 0, ',', ' ') }} FCFA
                </span>
            </div>
            @endif
        </div>
    @endforeach
</div>
@endif
                    {{-- ═══════════════════════════════════════════════════════════
                         👥 BÉNÉFICIAIRES
                         ═══════════════════════════════════════════════════════════ --}}
                    @php
                        $tousBeneficiaires = collect();
                        foreach ($client->dossiers as $d) {
                            foreach ($d->beneficiaires as $b) {
                                $tousBeneficiaires->push([
                                    'benef' => $b,
                                    'dossier' => $d,
                                ]);
                            }
                        }
                    @endphp

                    @if($tousBeneficiaires->count() > 0)
                    <div class="benef-section" data-client-id="{{ $client->id }}">
                        <div class="benef-section-header">
                            <div class="benef-section-title">
                                👥 Bénéficiaires
                                <span class="benef-section-count">{{ $tousBeneficiaires->count() }}</span>
                            </div>
                            <div class="benef-bulk-actions">
                                <button class="benef-bulk-btn" onclick="selectionnerBenefsClient({{ $client->id }}, true)">
                                    ☑ Tout
                                </button>
                                <button class="benef-bulk-btn" onclick="selectionnerBenefsClient({{ $client->id }}, false)">
                                    ☐ Aucun
                                </button>
                            </div>
                        </div>

                        @foreach($tousBeneficiaires as $item)
                            @php
                                $b = $item['benef'];
                                $d = $item['dossier'];

                                $benefAffs = $b->affectations()
                                    ->with(['lot', 'bloc', 'grandSite', 'tf', 'site'])
                                    ->where('statut', 'actif')
                                    ->get();

                                $nbLotsB = $benefAffs->count();

                                $superficieTotale = $benefAffs->sum(function($aff) {
                                    return $aff->lot?->superficie ?? 0;
                                });

                                $etapeActive = $b->etape_actuelle ?? null;
                                $etapesConfig = \App\Models\Beneficiaire::etapesConfig();
                                $etapeInfo = $etapeActive && isset($etapesConfig[$etapeActive])
                                    ? $etapesConfig[$etapeActive]
                                    : null;

                                $lotsParGrandSite = $benefAffs->groupBy(function($aff) {
                                    return $aff->grandSite?->nom ?? 'Site inconnu';
                                });
                            @endphp

                            <div class="benef-card" 
                                 data-benef-id="{{ $b->id }}"
                                 data-client-id="{{ $client->id }}"
                                 data-dossier-id="{{ $d->id }}"
                                 onclick="toggleBenefSelection(event, {{ $b->id }})">
                                
                                <input type="checkbox" 
                                       class="benef-checkbox"
                                       data-benef-id="{{ $b->id }}"
                                       data-client-id="{{ $client->id }}"
                                       onclick="event.stopPropagation(); toggleBenefSelection(event, {{ $b->id }})">

                                <div class="benef-avatar">
                                    {{ strtoupper(substr($b->nom, 0, 1)) }}
                                </div>

                                <div class="benef-info">
                                    <div class="benef-nom">
                                        <span class="benef-etape-dot {{ $etapeActive ? '' : 'vide' }}"></span>
                                        👤 {{ $b->nom }}
                                        @if($b->client_id)
                                            <span style="background:#dbeafe;color:#1d4ed8;font-size:8px;
                                                         padding:1px 6px;border-radius:5px;font-weight:700;">
                                                🔗 Client
                                            </span>
                                        @endif
                                    </div>

                                    @if($benefAffs->count() > 0)
                                    <div class="benef-detail-badges">
                                        @foreach($lotsParGrandSite as $grandSiteNom => $affsGrandSite)
                                            <span class="badge-site">🏢 {{ $grandSiteNom }}</span>

                                            @php
                                                $lotsParTf = $affsGrandSite->groupBy(function($aff) {
                                                    return $aff->tf?->title ?? 'TF inconnu';
                                                });
                                            @endphp

                                            @foreach($lotsParTf as $tfNom => $affsTf)
                                                <span class="badge-tf">📄 {{ $tfNom }}</span>

                                                @php
                                                    $lotsParBloc = $affsTf->groupBy(function($aff) {
                                                        return $aff->bloc?->code ?? '?';
                                                    });
                                                @endphp

                                                @foreach($lotsParBloc as $blocCode => $affsBloc)
                                                    <span class="badge-bloc">🏗️ Bloc {{ $blocCode }}</span>

                                                    @foreach($affsBloc as $aff)
                                                        <span class="badge-lot">
                                                            📦 Lot {{ $aff->lot?->numero ?? '?' }}
                                                            @if($aff->lot?->superficie)
                                                                <span style="font-weight:400;font-size:8px;">
                                                                    ({{ number_format($aff->lot->superficie, 0, ',', ' ') }} m²)
                                                                </span>
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        @endforeach

                                        <span class="badge-superficie">
                                            📐 {{ number_format($superficieTotale, 0, ',', ' ') }} m²
                                        </span>

                                        <span class="badge-count">
                                            📦 {{ $nbLotsB }} lot(s)
                                        </span>
                                    </div>
                                    @else
                                        <div class="benef-detail-badges" style="margin-top:6px;">
                                            <span style="font-size:10px;color:#94a3b8;font-style:italic;">
                                                Aucun lot affecté
                                            </span>
                                            <span class="badge-superficie" style="opacity:0.5;">
                                                📐 0 m²
                                            </span>
                                        </div>
                                    @endif

                                    @if($etapeInfo)
                                    <div style="margin-top:6px;display:flex;gap:6px;flex-wrap:wrap;align-items:center;">
                                        <span class="benef-etape-pill"
                                              style="background:{{ $etapeInfo['bg'] }};color:{{ $etapeInfo['color'] }};border:1px solid {{ $etapeInfo['color'] }}33;">
                                            {{ $etapeInfo['icon'] }} {{ $etapeInfo['label'] }}
                                        </span>
                                    </div>
                                    @endif

                                    <div class="benef-meta" style="margin-top:4px;">
                                        @if($b->telephone)
                                            <span class="benef-meta-item">📞 {{ $b->telephone }}</span>
                                        @endif
                                        <span class="benef-meta-item" style="color:#94a3b8;">
                                            📂 {{ $d->nom_dossier }}
                                        </span>
                                    </div>
                                </div>

                                <div class="benef-actions" onclick="event.stopPropagation();">
                                    <button class="benef-action-btn whatsapp"
                                            onclick="envoyerWhatsAppBenef({{ $b->id }})"
                                            title="Envoyer WhatsApp">💬</button>
                                    <button class="benef-action-btn view"
                                            onclick="voirBenefDetail({{ $b->id }}, {{ $client->id }})"
                                            title="Voir détails">👁</button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @endif

                </div>
            </div>
            <a href="{{ route('suivi-client.show', $client->id) }}"
               class="btn btn-primary btn-sm ms-2" style="white-space:nowrap;">Voir →</a>
        </div>
    </div>
    @empty
    <div style="text-align:center;padding:40px;color:#94a3b8;">
        <div style="font-size:40px;">📭</div>
        <div style="font-weight:700;margin-top:10px;">Aucun client trouvé</div>
    </div>
    @endforelse
</div>

{{-- MODAL MODIFIER NOM --}}
<div class="modal-overlay" id="overlayNom" onclick="fermerEditNom()"></div>
<div class="modal-box" id="modalNom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;">✏️ Modifier le nom</h5>
        <button onclick="fermerEditNom()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <input type="text" id="input-nouveau-nom" class="form-control mb-3" placeholder="Nouveau nom du client">
    <div class="d-flex justify-content-end gap-2">
        <button onclick="fermerEditNom()" class="btn btn-light">Annuler</button>
        <button onclick="sauvegarderNom()" class="btn btn-warning">💾 Enregistrer</button>
    </div>
</div>

{{-- MODAL CONFIRMATION ACTION --}}
<div class="modal-overlay" id="modalConfirmationOverlay" onclick="fermerModalConfirmation()"></div>
<div class="modal-box" id="modalConfirmation">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;" id="modalConfirmationTitre">⚠️ Confirmation</h5>
        <button onclick="fermerModalConfirmation()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <p id="modalConfirmationMessage" style="font-size:14px;color:#64748b;"></p>
    <div id="modalConfirmationListe" style="max-height:200px;overflow-y:auto;margin:12px 0;"></div>
    <div class="d-flex justify-content-end gap-2">
        <button onclick="fermerModalConfirmation()" class="btn btn-light">Annuler</button>
        <button onclick="executerActionConfirmee()" class="btn btn-danger" id="modalConfirmationBtn">Confirmer</button>
    </div>
</div>

{{-- MODAL PROGRESS DOCUMENTS --}}
<div class="modal-overlay" id="modalProgressOverlay"></div>
<div class="modal-box" id="modalProgress" style="width:600px; max-width:95%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;">📁 Récupération des documents</h5>
        <button onclick="fermerModalProgress()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <div id="progressContent">
        <div style="margin-bottom:12px;">
            <div style="display:flex;justify-content:space-between;font-size:13px;color:#64748b;">
                <span id="progressLabel">Préparation...</span>
                <span id="progressPercent">0%</span>
            </div>
            <div style="height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;margin-top:4px;">
                <div id="progressBar" style="width:0%;height:100%;background:#1d4ed8;border-radius:3px;transition:width 0.3s;"></div>
            </div>
        </div>
        <div id="progressDetails" style="font-size:12px;color:#94a3b8;max-height:200px;overflow-y:auto;padding:8px;background:#f8fafc;border-radius:8px;"></div>
    </div>
    <div id="progressResult" style="display:none;text-align:center;padding:20px 0;">
        <div style="font-size:48px;margin-bottom:12px;">✅</div>
        <h5 style="color:#16a34a;">Documents récupérés avec succès !</h5>
        <p id="resultMessage" style="color:#64748b;font-size:13px;"></p>
        <button onclick="fermerModalProgress()" class="btn btn-primary">Fermer</button>
    </div>
</div>

{{-- MODAL DÉTAIL BÉNÉFICIAIRE --}}
<div class="modal-overlay" id="benefDetailOverlay" onclick="fermerBenefDetail()"></div>
<div class="modal-box" id="benefDetail" style="width:520px; max-width:95%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#7c3aed;font-weight:800;margin:0;">👤 Détail bénéficiaire</h5>
        <button onclick="fermerBenefDetail()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <div id="benefDetailContent">Chargement...</div>
</div>

{{-- MODAL ÉTAPE GROUPÉE --}}
<div class="modal-overlay" id="etapeGroupeeOverlay" onclick="fermerModalEtapeGroupee()"></div>
<div class="modal-box" id="etapeGroupeeModal" style="width:600px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;">
            📊 Appliquer une étape d'avancement
        </h5>
        <button onclick="fermerModalEtapeGroupee()" 
                style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>

    <div id="etapeGroupeeResume" 
         style="background:#f1f5f9;border-radius:8px;padding:12px;
                margin-bottom:14px;font-size:12px;color:#475569;">
    </div>

    <div style="margin-bottom:14px;">
        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:8px;">
            📍 Étape à appliquer *
        </label>
        <div style="display:flex;flex-wrap:wrap;gap:8px;" id="etapeGroupeeOptions">
            @php
                $etapesDossier = \App\Models\DossierClient::etapesConfig();
            @endphp
            @foreach($etapesDossier as $cle => $cfg)
                <label class="etape-groupee-label" data-etape="{{ $cle }}">
                    <input type="radio" name="etapeGroupeeRadio" value="{{ $cle }}"
                           style="width:14px;height:14px;"
                           onchange="onEtapeGroupeeChange()">
                    <span style="font-size:16px;">{{ $cfg['icon'] }}</span>
                    {{ $cfg['label'] }}
                </label>
            @endforeach
        </div>
    </div>

    <div style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:6px;">
            📅 Date de l'étape *
        </label>
        <input type="date" id="etapeGroupeeDate" class="form-control"
               value="{{ now()->format('Y-m-d') }}">
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button onclick="fermerModalEtapeGroupee()" class="btn btn-light btn-sm">
            Annuler
        </button>
        <button onclick="validerEtapeGroupee()" class="btn btn-warning btn-sm"
                style="font-weight:700;color:white;">
            ✅ Appliquer
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF = window.CSRF || '{{ csrf_token() }}';

let clientIdCourant = null;
let selectedClients = new Set();
let selectedBenefs  = new Set();

// ════════════════════════════════════════════════════════════════
// EXPORT PDF
// ════════════════════════════════════════════════════════════════
function exporterPdf() {
    const q = document.getElementById('search-live')?.value || '';
    const du = document.getElementById('filtre-du')?.value || '';
    const au = document.getElementById('filtre-au')?.value || '';
    const site = document.getElementById('filtre-site')?.value || '';
    const statut = document.getElementById('filtre-statut')?.value || '';
    const sexe = document.getElementById('filtre-sexe')?.value || '';
    const technique = document.getElementById('filtre-technique')?.value || '';
    const morcellement = document.getElementById('filtre-morcellement')?.value || '';
    const dossier = document.getElementById('filtre-dossier')?.value || '';
    const logistique = document.getElementById('filtre-logistique')?.value || '';
    const lots = document.getElementById('filtre-lots')?.value || '';
    
    let url = '{{ route("suivi-client.export-pdf") }}?';
    if (q) url += 'q=' + encodeURIComponent(q) + '&';
    if (du) url += 'du=' + du + '&';
    if (au) url += 'au=' + au + '&';
    if (site) url += 'grand_site_id=' + site + '&';
    if (statut) url += 'status=' + statut + '&';
    if (sexe) url += 'sexe=' + sexe + '&';
    if (technique) url += 'technique_solde=' + technique + '&';
    if (morcellement) url += 'morcellement_solde=' + morcellement + '&';
    if (dossier) url += 'dossier_solde=' + dossier + '&';
    if (logistique) url += 'logistique_solde=' + logistique + '&';
    if (lots) url += 'lots=' + lots + '&';
    
    window.open(url, '_blank');
}

// ════════════════════════════════════════════════════════════════
// SÉLECTION CLIENTS
// ════════════════════════════════════════════════════════════════
function toggleSelection(checkbox, clientId) {
    if (checkbox.checked) {
        selectedClients.add(clientId);
        document.querySelector(`.client-card[data-id="${clientId}"]`)?.classList.add('selected');
    } else {
        selectedClients.delete(clientId);
        document.querySelector(`.client-card[data-id="${clientId}"]`)?.classList.remove('selected');
    }
    mettreAJourActionBar();
}

// ════════════════════════════════════════════════════════════════
// SÉLECTION BÉNÉFICIAIRES
// ════════════════════════════════════════════════════════════════
function toggleBenefSelection(event, benefId) {
    event.stopPropagation();
    
    const cb = document.querySelector(`.benef-checkbox[data-benef-id="${benefId}"]`);
    const card = document.querySelector(`.benef-card[data-benef-id="${benefId}"]`);
    
    if (event.target.type === 'checkbox') {
        // déjà togglé
    } else {
        cb.checked = !cb.checked;
    }
    
    if (cb.checked) {
        selectedBenefs.add(benefId);
        card?.classList.add('selected');
    } else {
        selectedBenefs.delete(benefId);
        card?.classList.remove('selected');
    }
    
    mettreAJourActionBar();
}

function selectionnerBenefsClient(clientId, select) {
    const section = document.querySelector(`.benef-section[data-client-id="${clientId}"]`);
    if (!section) return;
    
    const cards = section.querySelectorAll('.benef-card');
    cards.forEach(card => {
        const benefId = parseInt(card.dataset.benefId);
        const cb = card.querySelector('.benef-checkbox');
        
        cb.checked = select;
        if (select) {
            selectedBenefs.add(benefId);
            card.classList.add('selected');
        } else {
            selectedBenefs.delete(benefId);
            card.classList.remove('selected');
        }
    });
    
    mettreAJourActionBar();
}

function selectionnerTout() {
    document.querySelectorAll('.client-card:not([style*="display: none"])').forEach(card => {
        const cb = card.querySelector('.client-checkbox');
        if (cb) {
            cb.checked = true;
            const id = parseInt(cb.dataset.clientId);
            selectedClients.add(id);
            card.classList.add('selected');
        }
    });
    
    document.querySelectorAll('.client-card:not([style*="display: none"]) .benef-checkbox').forEach(cb => {
        cb.checked = true;
        const benefId = parseInt(cb.dataset.benefId);
        selectedBenefs.add(benefId);
        cb.closest('.benef-card')?.classList.add('selected');
    });
    
    mettreAJourActionBar();
}

function deselectionnerTout() {
    document.querySelectorAll('.client-checkbox').forEach(cb => {
        cb.checked = false;
        const id = parseInt(cb.dataset.clientId);
        selectedClients.delete(id);
        document.querySelector(`.client-card[data-id="${id}"]`)?.classList.remove('selected');
    });
    
    document.querySelectorAll('.benef-checkbox').forEach(cb => {
        cb.checked = false;
        const benefId = parseInt(cb.dataset.benefId);
        selectedBenefs.delete(benefId);
        cb.closest('.benef-card')?.classList.remove('selected');
    });
    
    mettreAJourActionBar();
}

function mettreAJourActionBar() {
    const bar = document.getElementById('actionBar');
    const summary = document.getElementById('selectionSummary');
    const total = selectedClients.size + selectedBenefs.size;
    
    document.getElementById('selectedCount').textContent = total;
    
    if (total > 0) {
        bar.classList.add('visible');
        summary.style.display = 'flex';
        document.getElementById('count-clients').textContent = selectedClients.size + ' client(s)';
        document.getElementById('count-benefs').textContent = selectedBenefs.size + ' bénéficiaire(s)';
    } else {
        bar.classList.remove('visible');
        summary.style.display = 'none';
    }
}

// ════════════════════════════════════════════════════════════════
// ACTIONS GROUPÉES
// ════════════════════════════════════════════════════════════════
function actionGroupee(action) {
    const idsClients = Array.from(selectedClients);
    const idsBenefs  = Array.from(selectedBenefs);
    const ids = idsClients;

    if (idsClients.length === 0 && idsBenefs.length === 0) {
        showToast('⚠️ Aucun élément sélectionné', 'warning');
        return;
    }

    const actionsMessages = {
        'mark_as_new': {title:'🆕 Marquer comme nouveaux', message:`Marquer ${idsClients.length} client(s) comme NOUVEAUX ?`, btnText:'Marquer', btnClass:'btn-success'},
        'mark_as_old': {title:'📌 Marquer comme anciens', message:`Marquer ${idsClients.length} client(s) comme ANCIENS ?`, btnText:'Marquer', btnClass:'btn-warning'},
        'set_masculin': {title:'👨 Marquer comme Masculin', message:`Marquer ${idsClients.length} client(s) comme MASCULIN ?`, btnText:'Marquer', btnClass:'btn-primary'},
        'set_feminin': {title:'👩 Marquer comme Féminin', message:`Marquer ${idsClients.length} client(s) comme FÉMININ ?`, btnText:'Marquer', btnClass:'btn-primary'},
        'delete': {title:'🗑 Supprimer', message:`Supprimer ${idsClients.length} client(s) ? Action irréversible.`, btnText:'Supprimer', btnClass:'btn-danger'},
        'export_whatsapp': {title:'💬 Envoyer sur WhatsApp', message:`Envoyer les informations de ${idsClients.length} client(s) sur WhatsApp ?`, btnText:'Envoyer', btnClass:'btn-primary'},
        'export_pdf_selected': {title:'📄 Exporter en PDF', message:`Exporter les ${idsClients.length} client(s) sélectionné(s) en PDF ?`, btnText:'Exporter', btnClass:'btn-danger'},
        'export_excel_selected': {title:'📥 Exporter en Excel', message:`Exporter les éléments sélectionnés en Excel ?`, btnText:'Exporter', btnClass:'btn-success'},
        'export_documents': {title:'📁 Récupérer tous les documents', message:`Récupérer tous les documents des ${idsClients.length} client(s) sélectionné(s) ?`, btnText:'Récupérer', btnClass:'btn-info'}
    };

    const config = actionsMessages[action];
    if (!config) return;

    document.getElementById('modalConfirmationTitre').textContent = config.title;
    document.getElementById('modalConfirmationMessage').textContent = config.message;
    document.getElementById('modalConfirmationBtn').textContent = config.btnText;
    document.getElementById('modalConfirmationBtn').className = `btn ${config.btnClass}`;
    
    const liste = document.getElementById('modalConfirmationListe');
    let html = '';
    
    if (idsClients.length > 0) {
        html += '<div style="font-size:12px;color:#1d4ed8;font-weight:700;margin-bottom:6px;">👤 Clients :</div>';
        idsClients.forEach(id => {
            const card = document.querySelector(`.client-card[data-id="${id}"]`);
            if (card) {
                const nom = card.querySelector('.client-nom')?.textContent || 'Inconnu';
                const phone = card.dataset.phone || '';
                html += `<div style="padding:3px 8px;font-size:12px;border-bottom:1px solid #f1f5f9;">• ${nom} ${phone ? '- ' + phone : ''}</div>`;
            }
        });
    }
    
    if (idsBenefs.length > 0) {
        html += '<div style="font-size:12px;color:#7c3aed;font-weight:700;margin:8px 0 6px 0;">👥 Bénéficiaires :</div>';
        idsBenefs.forEach(id => {
            const card = document.querySelector(`.benef-card[data-benef-id="${id}"]`);
            if (card) {
                const nom = card.querySelector('.benef-nom')?.textContent?.trim() || 'Inconnu';
                html += `<div style="padding:3px 8px;font-size:12px;border-bottom:1px solid #f1f5f9;">• ${nom}</div>`;
            }
        });
    }
    
    liste.innerHTML = html;
    
    document.getElementById('modalConfirmationOverlay').style.display = 'block';
    document.getElementById('modalConfirmation').style.display = 'block';
    document.getElementById('modalConfirmationBtn').dataset.action = action;
}

function fermerModalConfirmation() {
    document.getElementById('modalConfirmationOverlay').style.display = 'none';
    document.getElementById('modalConfirmation').style.display = 'none';
}

function fermerModalProgress() {
    document.getElementById('modalProgressOverlay').style.display = 'none';
    document.getElementById('modalProgress').style.display = 'none';
}

// ════════════════════════════════════════════════════════════════
// EXÉCUTION DE L'ACTION CONFIRMÉE
// ════════════════════════════════════════════════════════════════
function executerActionConfirmee() {
    const action = document.getElementById('modalConfirmationBtn').dataset.action;
    const idsClients = Array.from(selectedClients);
    const idsBenefs  = Array.from(selectedBenefs);
    const ids        = idsClients;

    fermerModalConfirmation();

    // Export documents
    if (action === 'export_documents') {
        exporterDocuments(idsClients);
        return;
    }

    // Export PDF sélection
    if (action === 'export_pdf_selected') {
        exporterPdfSelection(idsClients);
        return;
    }

    // Export Excel sélection
    if (action === 'export_excel_selected') {
        const params = new URLSearchParams();
        idsClients.forEach(id => params.append('client_ids[]', id));
        idsBenefs.forEach(id => params.append('beneficiaire_ids[]', id));
        window.location.href = '/admin/dossiers/export-excel?' + params.toString();
        return;
    }

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('/admin/suivi-client/actions-group', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({ ids: ids, action: action })
    })
    .then(response => response.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();

        if (data.success) {
            if (action === 'export_whatsapp' && data.whatsapp_url) {
                window.open(data.whatsapp_url, '_blank');
                showToast(`✅ ${data.count} client(s) envoyé(s) sur WhatsApp !`, 'success');
            } else if (action === 'delete') {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            showToast('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau : ' + error.message, 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// EXPORTER PDF DES SÉLECTIONNÉS
// ════════════════════════════════════════════════════════════════
function exporterPdfSelection(ids) {
    if (window.EdenLoader) window.EdenLoader.show();

    const params = new URLSearchParams();
    ids.forEach(id => params.append('ids[]', id));

    fetch('/admin/suivi-client/export-pdf-selected?' + params.toString(), {
        method: 'GET',
        headers: { 'X-CSRF-TOKEN': CSRF }
    })
    .then(response => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (response.ok) return response.blob();
        throw new Error('Erreur lors de l\'export');
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'clients_selectionnes_' + new Date().toISOString().split('T')[0] + '.pdf';
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
        showToast('✅ Export PDF terminé !', 'success');
    })
    .catch(error => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur : ' + error.message, 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// EXPORTER DOCUMENTS
// ════════════════════════════════════════════════════════════════
function exporterDocuments(ids) {
    document.getElementById('modalProgressOverlay').style.display = 'block';
    document.getElementById('modalProgress').style.display = 'block';
    document.getElementById('progressResult').style.display = 'none';
    document.getElementById('progressContent').style.display = 'block';
    document.getElementById('progressBar').style.width = '0%';
    document.getElementById('progressPercent').textContent = '0%';
    document.getElementById('progressLabel').textContent = 'Préparation des documents...';
    document.getElementById('progressDetails').innerHTML = '';

    let total = ids.length;
    let completed = 0;
    let documents = [];
    let errors = [];

    function updateProgress() {
        const percent = Math.round((completed / total) * 100);
        document.getElementById('progressBar').style.width = percent + '%';
        document.getElementById('progressPercent').textContent = percent + '%';
        document.getElementById('progressLabel').textContent = `Traitement ${completed}/${total} clients...`;
    }

    function processClient(clientId) {
        return fetch('/admin/suivi-client/export-documents/' + clientId, {
            method: 'GET',
            headers: { 'X-CSRF-TOKEN': CSRF }
        })
        .then(response => response.json())
        .then(data => {
            completed++;
            updateProgress();
            const details = document.getElementById('progressDetails');
            if (data.success) {
                const div = document.createElement('div');
                div.style.cssText = 'padding:4px 8px;border-bottom:1px solid #e2e8f0;color:#16a34a;';
                div.textContent = '✅ ' + data.client_name + ' - ' + data.documents_count + ' document(s) trouvé(s)';
                details.appendChild(div);
                if (data.documents) documents = documents.concat(data.documents);
            } else {
                const div = document.createElement('div');
                div.style.cssText = 'padding:4px 8px;border-bottom:1px solid #e2e8f0;color:#dc2626;';
                div.textContent = '❌ ' + (data.client_name || 'Client ' + clientId) + ' - ' + (data.message || 'Erreur');
                details.appendChild(div);
                errors.push(data.message || 'Erreur');
            }
            details.scrollTop = details.scrollHeight;
        })
        .catch(error => {
            completed++;
            updateProgress();
            const div = document.createElement('div');
            div.style.cssText = 'padding:4px 8px;border-bottom:1px solid #e2e8f0;color:#dc2626;';
            div.textContent = '❌ Client ' + clientId + ' - Erreur réseau';
            document.getElementById('progressDetails').appendChild(div);
            errors.push('Erreur réseau pour le client ' + clientId);
        });
    }

    let index = 0;
    const concurrency = 5;

    function processNext() {
        if (index >= total) {
            document.getElementById('progressLabel').textContent = 'Terminé !';
            document.getElementById('progressBar').style.width = '100%';
            document.getElementById('progressPercent').textContent = '100%';

            setTimeout(() => {
                document.getElementById('progressContent').style.display = 'none';
                document.getElementById('progressResult').style.display = 'block';
                document.getElementById('resultMessage').textContent = 
                    `${documents.length} document(s) récupéré(s) pour ${total - errors.length} client(s) sur ${total}.${errors.length > 0 ? ' ' + errors.length + ' erreur(s).' : ''}`;
                if (documents.length > 0) telechargerDocumentsZip(documents);
            }, 500);
            return;
        }
        const clientId = ids[index];
        index++;
        processClient(clientId).then(() => processNext());
    }

    for (let i = 0; i < Math.min(concurrency, total); i++) { processNext(); }
}

function telechargerDocumentsZip(documents) {
    if (documents.length === 0) { showToast('⚠️ Aucun document à télécharger', 'warning'); return; }
    if (documents.length === 1) {
        if (documents[0].url) window.open(documents[0].url, '_blank');
        return;
    }

    fetch('/admin/suivi-client/download-documents-zip', {
        method: 'POST',
        headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({ documents: documents })
    })
    .then(response => {
        if (response.ok) return response.blob();
        throw new Error('Erreur lors de la création du ZIP');
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'documents_clients_' + new Date().toISOString().split('T')[0] + '.zip';
        document.body.appendChild(a);
        a.click();
        a.remove();
        window.URL.revokeObjectURL(url);
        showToast('✅ Tous les documents ont été téléchargés !', 'success');
    })
    .catch(error => showToast('❌ Erreur lors du téléchargement : ' + error.message, 'error'));
}

// ════════════════════════════════════════════════════════════════
// WHATSAPP INDIVIDUEL
// ════════════════════════════════════════════════════════════════
function envoyerWhatsAppClient(clientId) {
    window.open(`/admin/clients/${clientId}/whatsapp`, '_blank');
}

function envoyerWhatsAppBenef(benefId) {
    window.open(`/admin/beneficiaires/${benefId}/whatsapp`, '_blank');
}

function voirBenefDetail(benefId, clientId) {
    window.location.href = `/admin/suivi-client/${clientId}#benef-${benefId}`;
}

function fermerBenefDetail() {
    document.getElementById('benefDetailOverlay').style.display = 'none';
    document.getElementById('benefDetail').style.display = 'none';
}

// ════════════════════════════════════════════════════════════════
// TOGGLE NEW STATUS
// ════════════════════════════════════════════════════════════════
function toggleNew(clientId) {
    const btn = document.getElementById('btn-new-' + clientId);
    const badge = document.getElementById('badge-' + clientId);
    const card = document.querySelector(`.client-card[data-id="${clientId}"]`);

    if (!btn) return;

    btn.disabled = true;
    btn.textContent = '⏳ ...';

    fetch('/admin/suivi-client/toggle-new/' + clientId, {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json'},
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_new) {
                btn.className = 'btn-toggle-new is-new';
                btn.innerHTML = '✅ Nouveau';
                badge.className = 'badge-new';
                badge.textContent = '🆕 Nouveau';
                if (card) card.dataset.isNew = 'true';
                showToast('✅ Client marqué comme nouveau', 'success');
            } else {
                btn.className = 'btn-toggle-new';
                btn.innerHTML = '🔄 Marquer nouveau';
                badge.className = 'badge-old';
                badge.textContent = 'Ancien';
                if (card) card.dataset.isNew = 'false';
                showToast('✅ Statut "nouveau" retiré', 'success');
            }
            mettreAJourCompteur();
        } else {
            showToast('❌ ' + data.message, 'error');
        }
    })
    .catch(error => showToast('❌ Erreur réseau', 'error'))
    .finally(() => btn.disabled = false);
}

function mettreAJourCompteur() {
    const cards = document.querySelectorAll('#liste-clients .client-card');
    let total = cards.length;
    let nouveaux = 0;
    cards.forEach(c => { if (c.dataset.isNew === 'true') nouveaux++; });
    const compteur = document.getElementById('compteur-clients');
    if (compteur) {
        compteur.innerHTML = `${total} client(s) <span style="margin-left:10px;color:#10b981;">🆕 ${nouveaux} nouveau(x)</span>`;
    }
}

// ════════════════════════════════════════════════════════════════
// TOAST
// ════════════════════════════════════════════════════════════════
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ════════════════════════════════════════════════════════════════
// FILTRAGE DYNAMIQUE
// ════════════════════════════════════════════════════════════════
function filtrerClients(terme) {
    const t = terme.trim().toLowerCase();
    const cards = document.querySelectorAll('#liste-clients .client-card');
    let visible = 0;
    let nouveaux = 0;

    cards.forEach(card => {
        const nom = card.dataset.nom || '';
        const phone = card.dataset.phone || '';
        const isNew = card.dataset.isNew === 'true';
        const match = !t || nom.includes(t) || phone.includes(t);
        card.style.display = match ? '' : 'none';
        if (match) { visible++; if (isNew) nouveaux++; }

        if (match && t) {
            const nomEl = card.querySelector('.client-nom');
            if (nomEl) {
                const texte = nomEl.dataset.original || nomEl.innerText;
                nomEl.dataset.original = texte;
                const regex = new RegExp(`(${t})`, 'gi');
                nomEl.innerHTML = texte.replace(regex, '<span class="highlight">$1</span>');
            }
        } else {
            const nomEl = card.querySelector('.client-nom');
            if (nomEl && nomEl.dataset.original) {
                nomEl.innerText = nomEl.dataset.original;
            }
        }
    });

    const compteur = document.getElementById('compteur-clients');
    if (compteur) {
        compteur.innerHTML = `${visible} client(s) <span style="margin-left:10px;color:#10b981;">🆕 ${nouveaux} nouveau(x)</span>`;
    }
}

function appliquerFiltresServeur() {
    const du = document.getElementById('filtre-du')?.value;
    const au = document.getElementById('filtre-au')?.value;
    const site = document.getElementById('filtre-site')?.value;
    const statut = document.getElementById('filtre-statut')?.value;
    const sexe = document.getElementById('filtre-sexe')?.value;
    const technique = document.getElementById('filtre-technique')?.value;
    const morcellement = document.getElementById('filtre-morcellement')?.value;
    const dossier = document.getElementById('filtre-dossier')?.value;
    const logistique = document.getElementById('filtre-logistique')?.value;
    const lots = document.getElementById('filtre-lots')?.value;
    const q = document.getElementById('search-live')?.value;
    const url = new URL(window.location.href);
    du ? url.searchParams.set('du', du) : url.searchParams.delete('du');
    au ? url.searchParams.set('au', au) : url.searchParams.delete('au');
    site ? url.searchParams.set('grand_site_id', site) : url.searchParams.delete('grand_site_id');
    statut ? url.searchParams.set('status', statut) : url.searchParams.delete('status');
    sexe ? url.searchParams.set('sexe', sexe) : url.searchParams.delete('sexe');
    technique ? url.searchParams.set('technique_solde', technique) : url.searchParams.delete('technique_solde');
    morcellement ? url.searchParams.set('morcellement_solde', morcellement) : url.searchParams.delete('morcellement_solde');
    dossier ? url.searchParams.set('dossier_solde', dossier) : url.searchParams.delete('dossier_solde');
    logistique ? url.searchParams.set('logistique_solde', logistique) : url.searchParams.delete('logistique_solde');
    lots ? url.searchParams.set('lots', lots) : url.searchParams.delete('lots');
    q ? url.searchParams.set('q', q) : url.searchParams.delete('q');
    window.location.href = url.toString();
}

// ════════════════════════════════════════════════════════════════
// MODIFIER NOM CLIENT
// ════════════════════════════════════════════════════════════════
function ouvrirEditNom(clientId, nomActuel) {
    clientIdCourant = clientId;
    document.getElementById('input-nouveau-nom').value = nomActuel;
    document.getElementById('overlayNom').style.display = 'block';
    document.getElementById('modalNom').style.display = 'block';
    setTimeout(() => document.getElementById('input-nouveau-nom').focus(), 100);
}

function fermerEditNom() {
    document.getElementById('overlayNom').style.display = 'none';
    document.getElementById('modalNom').style.display = 'none';
    clientIdCourant = null;
}

function sauvegarderNom() {
    const nom = document.getElementById('input-nouveau-nom').value.trim();
    if (!nom) { alert('Le nom ne peut pas être vide.'); return; }
    fetch('/admin/clients/' + clientIdCourant + '/modifier-nom', {
        method: 'POST',
        headers: {'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF},
        body: JSON.stringify({ nom }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const el = document.getElementById('nom-' + clientIdCourant);
            if (el) {
                el.innerText = data.nom;
                el.dataset.original = data.nom;
                const card = el.closest('.client-card');
                if (card) card.dataset.nom = data.nom.toLowerCase();
            }
            fermerEditNom();
            showToast('✅ Nom mis à jour', 'success');
        } else alert(data.message || 'Erreur');
    });
}

// ════════════════════════════════════════════════════════════════
// MODAL ÉTAPE GROUPÉE
// ════════════════════════════════════════════════════════════════
function ouvrirModalEtapeGroupee() {
    const idsClients = Array.from(selectedClients);
    const idsBenefs  = Array.from(selectedBenefs);

    if (idsClients.length === 0 && idsBenefs.length === 0) {
        showToast('⚠️ Aucun élément sélectionné', 'warning');
        return;
    }

    const resume = document.getElementById('etapeGroupeeResume');
    let html = '<div style="font-weight:700;color:#1e3a5f;margin-bottom:8px;">📊 Éléments sélectionnés :</div>';

    if (idsClients.length > 0) {
        html += `<div style="margin-bottom:4px;color:#1d4ed8;">
            👤 <strong>${idsClients.length} client(s)</strong>
        </div>`;
    }
    if (idsBenefs.length > 0) {
        html += `<div style="color:#7c3aed;">
            👥 <strong>${idsBenefs.length} bénéficiaire(s)</strong>
        </div>`;
    }

    resume.innerHTML = html;

    document.querySelectorAll('input[name="etapeGroupeeRadio"]').forEach(r => {
        r.checked = false;
    });
    document.querySelectorAll('.etape-groupee-label').forEach(l => {
        l.style.background = 'white';
        l.style.borderColor = '#e2e8f0';
        l.style.color = '#64748b';
    });
    document.getElementById('etapeGroupeeDate').value = new Date().toISOString().split('T')[0];

    document.getElementById('etapeGroupeeOverlay').style.display = 'block';
    document.getElementById('etapeGroupeeModal').style.display = 'block';
}

function fermerModalEtapeGroupee() {
    document.getElementById('etapeGroupeeOverlay').style.display = 'none';
    document.getElementById('etapeGroupeeModal').style.display = 'none';
}

function onEtapeGroupeeChange() {
    document.querySelectorAll('.etape-groupee-label').forEach(label => {
        const radio = label.querySelector('input[type="radio"]');
        const etape = label.dataset.etape;

        if (radio.checked) {
            const colors = {
                'implantation_prevue': { bg: '#f5f3ff', border: '#7c3aed', text: '#7c3aed' },
                'deja_implante':       { bg: '#f0fdf4', border: '#16a34a', text: '#16a34a' },
                'dossier_technique':   { bg: '#fff1f2', border: '#dc2626', text: '#dc2626' },
                'morcellement':        { bg: '#fefce8', border: '#ca8a04', text: '#ca8a04' },
            };
            const c = colors[etape] || { bg: '#eff6ff', border: '#1d4ed8', text: '#1d4ed8' };
            label.style.background = c.bg;
            label.style.borderColor = c.border;
            label.style.color = c.text;
        } else {
            label.style.background = 'white';
            label.style.borderColor = '#e2e8f0';
            label.style.color = '#64748b';
        }
    });
}

function validerEtapeGroupee() {
    const etapeRadio = document.querySelector('input[name="etapeGroupeeRadio"]:checked');
    const date = document.getElementById('etapeGroupeeDate').value;

    if (!etapeRadio) {
        showToast('⚠️ Sélectionnez une étape', 'warning');
        return;
    }
    if (!date) {
        showToast('⚠️ La date est obligatoire', 'warning');
        return;
    }

    const idsClients = Array.from(selectedClients);
    const idsBenefs  = Array.from(selectedBenefs);

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('/admin/etape-groupee', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({
            etape: etapeRadio.value,
            date: date,
            client_ids: idsClients,
            beneficiaire_ids: idsBenefs,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();

        if (data.success) {
            showToast('✅ ' + data.message, 'success');
            fermerModalEtapeGroupee();
            deselectionnerTout();
            setTimeout(() => location.reload(), 1200);
        } else {
            let msg = data.message || 'Erreur';
            if (data.errors) msg = Object.values(data.errors).flat().join('\n');
            showToast('❌ ' + msg, 'error');
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau : ' + e.message, 'error');
    });
}

// Scroll vers bénéficiaire si hash présent
document.addEventListener('DOMContentLoaded', () => {
    const q = new URLSearchParams(window.location.search).get('q');
    if (q) filtrerClients(q);

    if (window.location.hash && window.location.hash.startsWith('#benef-')) {
        const el = document.querySelector(window.location.hash);
        if (el) {
            setTimeout(() => {
                el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                el.style.transition = 'background 0.5s';
                el.style.background = '#ede9fe';
                setTimeout(() => { el.style.background = ''; }, 2000);
            }, 300);
        }
    }
});
</script>
@endsection