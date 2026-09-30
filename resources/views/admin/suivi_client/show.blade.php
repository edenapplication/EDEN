@extends('admin.layout')
@section('content')

<style>
.section-card { background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:20px; margin-bottom:16px; }
.section-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px; }
.info-row { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
.info-row span:first-child { color:#64748b; }
.info-row span:last-child  { font-weight:600; color:#1e3a5f; }
.pay-row { display:flex; justify-content:space-between; font-size:12px; padding:4px 0; border-bottom:1px solid #f8fafc; }
.pay-amt { font-weight:700; }
.dossier-tab { cursor:pointer; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600; background:#f1f5f9; color:#64748b; }
.dossier-tab.active { background:#0d6efd; color:white; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.3); z-index:9999; width:400px; max-height:90vh; overflow-y:auto; }

/* ═══════════════════════════════════════════════════════════════
   ONGLETS PRINCIPAUX
   ═══════════════════════════════════════════════════════════════ */
.main-tabs {
    display:flex; gap:6px; margin-bottom:16px;
    background:white; padding:6px; border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    flex-wrap:wrap;
}
.main-tab {
    flex:1; min-width:120px;
    padding:10px 16px; border-radius:8px;
    font-size:13px; font-weight:700;
    text-align:center; cursor:pointer;
    background:#f1f5f9; color:#64748b;
    transition:all 0.2s;
    display:flex; align-items:center; justify-content:center; gap:6px;
}
.main-tab:hover { background:#e2e8f0; }
.main-tab.active {
    background:linear-gradient(135deg, #7c3aed, #a855f7);
    color:white;
    box-shadow:0 4px 12px rgba(124,58,237,0.3);
}
.main-tab .count {
    background:rgba(255,255,255,0.3);
    padding:1px 8px; border-radius:10px;
    font-size:11px; font-weight:700;
}
.main-tab:not(.active) .count {
    background:#cbd5e1; color:#475569;
}

.tab-content { display:none; }
.tab-content.active { display:block; animation:fadeIn 0.3s; }
@keyframes fadeIn {
    from { opacity:0; transform:translateY(10px); }
    to { opacity:1; transform:translateY(0); }
}

/* ═══════════════════════════════════════════════════════════════
   NAVIGATION DES DOSSIERS (ONGLETS VISIBLES ET DISTINCTS)
   ═══════════════════════════════════════════════════════════════ */
.dossier-tabs-container {
    background:white;
    border-radius:12px;
    padding:14px 18px;
    margin-bottom:16px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    border:1px solid #e2e8f0;
}
.dossier-tabs-title {
    font-size:11px;
    font-weight:700;
    color:#64748b;
    text-transform:uppercase;
    margin-bottom:12px;
    display:flex;
    align-items:center;
    gap:6px;
}
.dossier-tabs-nav {
    display:flex;
    gap:10px;
    flex-wrap:wrap;
}
.dossier-tab {
    cursor:pointer;
    padding:12px 20px;
    border-radius:10px;
    font-size:13px;
    font-weight:700;
    background:#f1f5f9;
    color:#64748b;
    border:2px solid transparent;
    transition:all 0.2s;
    display:flex;
    align-items:center;
    gap:10px;
}
.dossier-tab:hover {
    background:#e2e8f0;
    transform:translateY(-2px);
    box-shadow:0 4px 8px rgba(0,0,0,0.08);
}
.dossier-tab.active {
    background:linear-gradient(135deg, #0d6efd, #2563eb);
    color:white;
    border-color:#0d6efd;
    box-shadow:0 6px 16px rgba(13, 110, 253, 0.35);
    transform:translateY(-2px);
}
.dossier-tab .dossier-num {
    background:rgba(255,255,255,0.35);
    padding:2px 10px;
    border-radius:12px;
    font-size:11px;
    font-weight:800;
    min-width:26px;
    text-align:center;
}
.dossier-tab:not(.active) .dossier-num {
    background:#cbd5e1;
    color:#475569;
}
.dossier-tab .dossier-info {
    display:flex;
    flex-direction:column;
    gap:2px;
    text-align:left;
}
.dossier-tab .dossier-nom {
    font-size:13px;
    font-weight:700;
}
.dossier-tab .dossier-site {
    font-weight:400;
    font-size:10px;
    opacity:0.85;
}
.dossier-tab .dossier-badge-count {
    background:rgba(255,255,255,0.35);
    padding:2px 8px;
    border-radius:10px;
    font-size:10px;
    font-weight:700;
}
.dossier-tab:not(.active) .dossier-badge-count {
    background:#e2e8f0;
    color:#475569;
}

/* Panneau dossier */
.dossier-panel {
    position:relative;
    padding-top:28px;
}
.dossier-panel-indicator {
    position:absolute;
    top:0;
    left:20px;
    background:linear-gradient(135deg, #0d6efd, #2563eb);
    color:white;
    padding:5px 16px;
    border-radius:0 0 10px 10px;
    font-size:10px;
    font-weight:800;
    letter-spacing:0.8px;
    box-shadow:0 4px 10px rgba(13,110,253,0.3);
    z-index:10;
}

/* ═══════════════════════════════════════════════════════════════
   ACCORDÉONS
   ═══════════════════════════════════════════════════════════════ */
.accordion {
    background:white; border-radius:12px;
    box-shadow:0 2px 8px rgba(0,0,0,0.06);
    margin-bottom:10px; overflow:hidden;
    border:1px solid #e2e8f0;
}
.accordion-header {
    padding:14px 18px; cursor:pointer;
    display:flex; justify-content:space-between; align-items:center;
    background:#f8fafc; user-select:none;
    transition:background 0.2s;
    font-weight:700; font-size:13px; color:#1e3a5f;
}
.accordion-header:hover { background:#f1f5f9; }
.accordion-header.open { background:#eff6ff; }
.accordion-header .chevron {
    transition:transform 0.3s;
    font-size:14px; color:#64748b;
    flex-shrink:0;
}
.accordion-header.open .chevron { transform:rotate(90deg); }
.accordion-content {
    max-height:0; overflow:hidden;
    transition:max-height 0.4s ease;
}
.accordion-content.open { max-height:8000px; }
.accordion-body { padding:16px 18px; border-top:1px solid #e2e8f0; }

/* ═══════════════════════════════════════════════════════════════
   BADGES BÉNÉFICIAIRE (VISIBLE SANS DÉPLIER)
   ═══════════════════════════════════════════════════════════════ */
.benef-summary {
    display:flex; flex-wrap:wrap; gap:6px;
    font-size:11px; align-items:center;
}
.benef-summary .badge-site {
    background:linear-gradient(135deg, #dbeafe, #bfdbfe);
    color:#1d4ed8; padding:3px 10px;
    border-radius:8px; font-weight:700;
    border:1px solid #93c5fd;
    display:inline-flex; align-items:center; gap:4px;
}
.benef-summary .badge-tf {
    background:linear-gradient(135deg, #fef3c7, #fde68a);
    color:#92400e; padding:3px 10px;
    border-radius:8px; font-weight:700;
    border:1px solid #fcd34d;
    display:inline-flex; align-items:center; gap:4px;
}
.benef-summary .badge-bloc {
    background:linear-gradient(135deg, #fce7f3, #fbcfe8);
    color:#9d174d; padding:3px 10px;
    border-radius:8px; font-weight:700;
    border:1px solid #f9a8d4;
    display:inline-flex; align-items:center; gap:4px;
}
.benef-summary .badge-lot {
    background:linear-gradient(135deg, #dcfce7, #bbf7d0);
    color:#166534; padding:3px 8px;
    border-radius:8px; font-weight:700;
    border:1px solid #86efac;
    display:inline-flex; align-items:center; gap:3px;
}
.benef-summary .badge-superficie {
    background:linear-gradient(135deg, #7c3aed, #a855f7);
    color:white; padding:4px 12px;
    border-radius:10px; font-weight:800;
    font-size:12px;
    box-shadow:0 2px 6px rgba(124,58,237,0.3);
    display:inline-flex; align-items:center; gap:4px;
}
.benef-summary .badge-count {
    background:#f1f5f9; color:#64748b;
    padding:3px 8px; border-radius:8px;
    font-weight:600; font-size:10px;
    display:inline-flex; align-items:center; gap:3px;
}

/* ═══════════════════════════════════════════════════════════════
   BADGES GÉNÉRAUX
   ═══════════════════════════════════════════════════════════════ */
.badge-new {
    background: #10b981; color: #fff;
    padding: 4px 12px; border-radius: 50px;
    font-size: 11px; font-weight: 700;
    text-transform: uppercase;
    display: inline-block; margin-left: 10px;
    animation: pulse-new 2s ease-in-out infinite;
}
.badge-old {
    background: #e2e8f0; color: #64748b;
    padding: 4px 12px; border-radius: 50px;
    font-size: 11px; font-weight: 600;
    display: inline-block; margin-left: 10px;
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

/* ═══════════════════════════════════════════════════════════════
   TOAST
   ═══════════════════════════════════════════════════════════════ */
.toast-notification {
    position: fixed; bottom: 20px; right: 20px;
    background: #1f2937; color: #fff;
    padding: 12px 20px; border-radius: 8px;
    font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 99999; max-width: 400px;
    animation: slideInToast 0.3s ease;
}
@keyframes slideInToast {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* ═══════════════════════════════════════════════════════════════
   PAIEMENT BLOCS
   ═══════════════════════════════════════════════════════════════ */
.paiement-blocs { display:grid; grid-template-columns:repeat(3, 1fr); gap:14px; }
.paiement-bloc {
    border-radius:12px; padding:16px;
    border:1px solid #e2e8f0;
    display:flex; flex-direction:column;
}
.paiement-bloc.bloc-dossier   { background:#eff6ff; border-left:4px solid #0d6efd; }
.paiement-bloc.bloc-technique { background:#fff7ed; border-left:4px solid #ea580c; }
.paiement-bloc.bloc-morcel    { background:#fefce8; border-left:4px solid #ca8a04; }
.paiement-bloc h6 { font-weight:700; font-size:13px; margin-bottom:10px; }
.pay-total-row { display:flex; justify-content:space-between; font-size:12px; padding:4px 0; }
.pay-historique { margin-top:10px; max-height:140px; overflow-y:auto; }

/* ═══════════════════════════════════════════════════════════════
   RESPONSIVE
   ═══════════════════════════════════════════════════════════════ */
@media (max-width: 992px) {
    .paiement-blocs { grid-template-columns:1fr; }
    .main-tab { min-width:100px; font-size:11px; }
    .dossier-tab { padding:10px 14px; font-size:12px; }
    .dossier-tab .dossier-info { font-size:11px; }
}
</style>


{{-- EN-TÊTE CLIENT --}}
<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div>
        <a href="{{ route('suivi-client.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
        <h2 class="d-inline ms-2">
            👤 {{ $client->name }}
            @if($client->is_new)
                <span class="badge-new" id="badge-detail-{{ $client->id }}">🆕 Nouveau</span>
            @else
                <span class="badge-old" id="badge-detail-{{ $client->id }}">Ancien</span>
            @endif
        </h2>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button class="btn-toggle-new {{ $client->is_new ? 'is-new' : '' }}"
                onclick="toggleNew({{ $client->id }})"
                id="btn-new-detail-{{ $client->id }}">
            @if($client->is_new) ✅ Nouveau @else 🔄 Marquer nouveau @endif
        </button>
        @if($dossier)
            <a href="{{ route('suivi-client.create') }}?client_id={{ $client->id }}"
               class="btn btn-primary btn-sm">📂 Nouveau dossier</a>
        @endif
        <a href="{{ route('suivi-client.edit', $client->id) }}" class="btn btn-warning btn-sm">✏️ Modifier</a>
    </div>
</div>

@php
    $nbDossiers = $client->dossiers->count();
    $nbBenefs   = $client->dossiers->sum(fn($d) => $d->beneficiaires->count());
    $nbVisites  = $client->visites->count();
@endphp

{{-- ONGLETS PRINCIPAUX --}}
<div class="main-tabs">
    <div class="main-tab active" data-tab="tab-infos" onclick="switchTab('tab-infos')">
        📋 <span>Informations</span>
    </div>
    @if($dossier)
    <div class="main-tab" data-tab="tab-dossiers" onclick="switchTab('tab-dossiers')">
        📂 <span>Dossier</span>
        <span class="count">{{ $nbDossiers }}</span>
    </div>
    <div class="main-tab" data-tab="tab-benefs" onclick="switchTab('tab-benefs')">
        👥 <span>Bénéficiaires</span>
        <span class="count">{{ $nbBenefs }}</span>
    </div>
    @endif
    <div class="main-tab" data-tab="tab-visites" onclick="switchTab('tab-visites')">
        🚶 <span>Visites</span>
        <span class="count">{{ $nbVisites }}</span>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     TAB 1 : INFORMATIONS
     ═══════════════════════════════════════════════════════════════ --}}
<div id="tab-infos" class="tab-content active">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="section-card">
                <h5>📋 Informations client</h5>
                <div class="info-row"><span>Téléphone</span><span>{{ $client->phone ?? '-' }}</span></div>
                <div class="info-row"><span>Lots attribués</span><span>{{ $client->lots->count() }}</span></div>
                <div class="info-row"><span>Dossiers</span><span>{{ $client->dossiers->count() }}</span></div>
                <div class="info-row">
                    <span>Statut</span>
                    <span>
                        @if($client->is_new)
                            <span style="color:#10b981;font-weight:700;">🆕 Nouveau client</span>
                        @else
                            <span style="color:#64748b;">Client existant</span>
                        @endif
                    </span>
                </div>
                <div class="info-row"><span>Date création</span><span>{{ $client->created_at?->format('d/m/Y H:i') ?? '-' }}</span></div>
            </div>
        </div>

        @if($dossier)
        <div class="col-md-6">
            <div class="section-card">
                <h5>📊 Étapes du dossier</h5>
                @php
                    $etapesConfig = \App\Models\DossierClient::etapesConfig();
                    $etapesOrdre  = \App\Models\DossierClient::etapesOrdre();
                    $etapeActuelle= $dossier->etape_actuelle;
                @endphp

                <div style="display:flex;gap:8px;flex-wrap:wrap;">
                    @foreach($etapesConfig as $cle => $cfg)
                    @php
                        $ordreEtape  = $etapesOrdre[$cle];
                        $ordreActuel = $etapeActuelle ? ($etapesOrdre[$etapeActuelle] ?? 0) : 0;
                        $estFait     = $ordreEtape <= $ordreActuel;
                        $champ       = $cfg['champ'];
                        $dateEtape   = $dossier->$champ;
                    @endphp
                    <div onclick="ouvrirModalEtape({{ $dossier->id }}, '{{ $cle }}', '{{ $cfg['label'] }}', {{ $estFait ? 'true' : 'false' }}, '{{ $dateEtape ? \Carbon\Carbon::parse($dateEtape)->format('Y-m-d') : '' }}')"
                         style="
                            display:flex;align-items:center;gap:6px;
                            padding:8px 14px;border-radius:20px;
                            border:2px solid {{ $estFait ? $cfg['color'] : '#e2e8f0' }};
                            background:{{ $estFait ? $cfg['bg'] : 'white' }};
                            font-size:12px;font-weight:700;color:{{ $estFait ? $cfg['color'] : '#94a3b8' }};
                            cursor:pointer;transition:all 0.3s;
                         ">
                        <span>{{ $cfg['icon'] }}</span>
                        {{ $cfg['label'] }}
                        @if($estFait && $dateEtape)
                            <span style="font-size:10px;background:white;padding:0 8px;border-radius:8px;border:1px solid #e2e8f0;">
                                {{ \Carbon\Carbon::parse($dateEtape)->format('d/m/Y') }}
                            </span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     TAB 2 : DOSSIER
     ═══════════════════════════════════════════════════════════════ --}}
@if($dossier)
<div id="tab-dossiers" class="tab-content">

    {{-- ✅ NAVIGATION DES DOSSIERS (visibles et distincts) --}}
    @if($client->dossiers->count() > 1)
    <div class="dossier-tabs-container">
        <div class="dossier-tabs-title">
            📂 Sélectionnez un dossier ({{ $client->dossiers->count() }} disponibles)
        </div>
        <div class="dossier-tabs-nav">
            @foreach($client->dossiers as $i => $d)
                <div class="dossier-tab {{ $i === 0 ? 'active' : '' }}"
                     data-dossier-id="{{ $d->id }}"
                     onclick="showDossier('dossier-{{ $d->id }}', this, event)">
                    <span class="dossier-num">{{ $i + 1 }}</span>
                    <div class="dossier-info">
                        <span class="dossier-nom">📂 {{ $d->nom_dossier }}</span>
                        @if($d->grandSite)
                            <span class="dossier-site">🏢 {{ $d->grandSite->nom }}</span>
                        @endif
                    </div>
                    <span class="dossier-badge-count">
                        {{ $d->beneficiaires->count() }} 👥
                    </span>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ✅ BOUCLE DES DOSSIERS (tous sont rendus, seul le premier est visible) --}}
    @foreach($client->dossiers as $i => $dossier)
    <div id="dossier-{{ $dossier->id }}"
         class="dossier-panel"
         data-dossier-num="{{ $i + 1 }}"
         data-dossier-total="{{ $client->dossiers->count() }}"
         style="{{ $i > 0 ? 'display:none;' : '' }}">

        {{-- Indicateur visuel du dossier actif --}}
        <div class="dossier-panel-indicator">
            📂 DOSSIER {{ $i + 1 }} / {{ $client->dossiers->count() }}
        </div>

        {{-- En-tête dossier --}}
        <div class="section-card">
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                <h5 class="mb-0">📂 {{ $dossier->nom_dossier }}</h5>
                <div class="d-flex gap-1 flex-wrap">
                    <a href="{{ route('bons.index', $dossier->id) }}"
                       class="btn btn-outline-primary btn-sm" style="font-size:11px;">
                        🧾 {{ $dossier->bons->count() }} bon(s)
                    </a>
                    <button onclick="envoyerWhatsAppDossier({{ $dossier->id }})"
                            class="btn btn-success btn-sm" style="font-size:11px;">
                        💬 WhatsApp
                    </button>
                    <button onclick="ouvrirHistoriqueAffectations({{ $dossier->id }})"
                            class="btn btn-info btn-sm" style="font-size:11px;color:white;">
                        📜 Hist. affectations
                    </button>
                    <a href="#" 
                       class="btn btn-primary btn-sm" style="font-size:11px;"
                       onclick="demanderReferenceCreate({{ $dossier->id }})">
                        + Nouveau paiement
                    </a>
                    <form action="{{ route('suivi-client.dossiers.destroy', $dossier->id) }}"
                          method="POST"
                          onsubmit="return confirm('Supprimer ce dossier ?')"
                          style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="btn btn-danger btn-sm">🗑 Supprimer</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Accordéon Informations --}}
        <div class="accordion">
            <div class="accordion-header" onclick="toggleAccordion(this)">
                <span>📋 Informations générales</span>
                <span class="chevron">▶</span>
            </div>
            <div class="accordion-content">
                <div class="accordion-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-row"><span>🔗 Facilitateur</span>
                                <span>{{ $dossier->facilitateur?->nom ?? '-' }}</span>
                            </div>
                            <div class="info-row"><span>🧭 Direction</span>
                                <span>{{ match($dossier->direction) { 'baffoussam'=>'Baffoussam','bagante'=>'Bagante','direction_generale'=>'Direction Générale',default=>'-' } }}</span>
                            </div>
                            <div class="info-row"><span>🏢 Grand Site souhaité</span>
                                <span>{{ $dossier->grandSite?->nom ?? '-' }}</span>
                            </div>
                            <div class="info-row"><span>📐 Superficie voulue</span>
                                <span>{{ $dossier->superficie_voulue ? number_format($dossier->superficie_voulue, 0, ',', ' ') . ' m²' : '-' }}</span>
                            </div>
                            <div class="info-row"><span>🧑‍💼 Commercial</span>
                                <span>{{ $dossier->commercial?->name ?? '-' }}</span>
                            </div>
                            <div class="info-row"><span>🤝 Agent commercial</span>
                                <span>{{ $dossier->agentCommercial?->nom ?? '-' }}</span>
                            </div>
                            <div class="info-row"><span>🚗 Chauffeur</span>
                                <span>{{ $dossier->conducteur?->nom ?? '-' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-row">
                                <span>📎 CNI</span>
                                <span>
                                    @php $cnis = $dossier->cni_images ?? []; @endphp
                                    @if(count($cnis) > 0)
                                        <div style="display:flex; gap:4px; flex-wrap:wrap; align-items:center;">
                                            @foreach($cnis as $img)
                                                <a href="{{ asset('storage/' . $img) }}" target="_blank" 
                                                   style="display:inline-block; width:30px; height:30px; border-radius:4px; overflow:hidden; border:1px solid #e2e8f0;">
                                                    <img src="{{ asset('storage/' . $img) }}" 
                                                         alt="CNI" 
                                                         style="width:100%; height:100%; object-fit:cover;">
                                                </a>
                                            @endforeach
                                            <span style="font-size:11px; color:#64748b; margin-left:4px;">({{ count($cnis) }})</span>
                                        </div>
                                    @else
                                        <span style="color:#94a3b8;">Aucune</span>
                                    @endif
                                </span>
                            </div>
                            <div class="info-row"><span>💰 Prix superficie</span>
                                <span>{{ $dossier->prix_superficie ? number_format($dossier->prix_superficie, 0, ',', ' ') . ' FCFA' : '-' }}</span>
                            </div>
                            <div class="info-row"><span>💰 Prix technique</span>
                                <span>{{ $dossier->prix_technique ? number_format($dossier->prix_technique, 0, ',', ' ') . ' FCFA' : '-' }}</span>
                            </div>
                            <div class="info-row"><span>💰 Prix logistique</span>
                                <span>{{ $dossier->prix_logistique ? number_format($dossier->prix_logistique, 0, ',', ' ') . ' FCFA' : '-' }}</span>
                            </div>
                            <div class="info-row"><span>💰 Prix morcellement</span>
                                <span>{{ $dossier->prix_morcellement ? number_format($dossier->prix_morcellement, 0, ',', ' ') . ' FCFA' : '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Accordéon Paiements --}}
        @php
            $totalDossier   = $dossier->paiements->sum('montant');
            $prixRef        = $dossier->prix_superficie    ?? 0;
            $resteDossier   = max(0, $prixRef - $totalDossier);
            $prixTech       = $dossier->prix_technique     ?? 0;
            $prixMorcel     = $dossier->prix_morcellement  ?? 0;
            $prixLogistique = $dossier->prix_logistique    ?? 0;
            $totalTechnique = $dossier->paiementsTechniques->sum('montant');
            $totalMorcel    = $dossier->paiementsMorcellements->sum('montant');
            $resteTech      = max(0, $prixTech - $totalTechnique);
            $resteMorcel    = max(0, $prixMorcel - $totalMorcel);
        @endphp

        <div class="accordion">
            <div class="accordion-header" onclick="toggleAccordion(this)">
                <span>💰 Paiements & Prix</span>
                <span class="chevron">▶</span>
            </div>
            <div class="accordion-content">
                <div class="accordion-body">
                    <div style="background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:14px;border:1px solid #e2e8f0;">
                        <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:10px;">
                            💰 Prix de référence
                        </div>
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label style="font-size:11px;color:#64748b;">💰 Superficie (FCFA)</label>
                                <div style="display:flex;gap:6px;">
                                    <input type="number" id="prix-superficie-{{ $dossier->id }}"
                                           class="form-control form-control-sm" value="{{ $prixRef }}">
                                    <button onclick="majPrix({{ $dossier->id }})"
                                            class="btn btn-primary btn-sm" style="font-size:11px;">✓</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label style="font-size:11px;color:#ea580c;">🛠️ Technique (FCFA)</label>
                                <div style="display:flex;gap:6px;">
                                    <input type="number" id="prix-technique-{{ $dossier->id }}"
                                           class="form-control form-control-sm" value="{{ $prixTech }}">
                                    <button onclick="majPrix({{ $dossier->id }})"
                                            class="btn btn-warning btn-sm" style="font-size:11px;">✓</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label style="font-size:11px;color:#7c3aed;">🚗 Logistique (FCFA)</label>
                                <div style="display:flex;gap:6px;">
                                    <input type="number" id="prix-logistique-{{ $dossier->id }}"
                                           class="form-control form-control-sm" value="{{ $prixLogistique }}">
                                    <button onclick="majPrix({{ $dossier->id }})"
                                            class="btn btn-sm" style="background:#7c3aed;color:white;font-size:11px;">✓</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label style="font-size:11px;color:#ca8a04;">✂️ Morcellement (FCFA)</label>
                                <div style="display:flex;gap:6px;">
                                    <input type="number" id="prix-morcellement-{{ $dossier->id }}"
                                           class="form-control form-control-sm" value="{{ $prixMorcel }}">
                                    <button onclick="majPrix({{ $dossier->id }})"
                                            class="btn btn-sm" style="background:#ca8a04;color:white;font-size:11px;">✓</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="paiement-blocs">
                        <div class="paiement-bloc bloc-dossier">
                            <h6 style="color:#0d6efd;">📁 Paiement Parcelle</h6>
                            @if($prixRef > 0)
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Référence</span>
                                <span style="font-weight:600;">{{ number_format($prixRef, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endif
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Payé</span>
                                <span class="pay-amt" style="color:#16a34a;">{{ number_format($totalDossier, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Reste</span>
                                <span style="font-weight:700;color:{{ $resteDossier > 0 ? '#dc2626' : '#16a34a' }};">
                                    {{ number_format($resteDossier, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                            @if($prixRef > 0)
                            <div style="height:6px;background:#e2e8f0;border-radius:3px;margin:8px 0;">
                                @php $pct = $prixRef > 0 ? min(100, round(($totalDossier/$prixRef)*100)) : 0; @endphp
                                <div style="width:{{ $pct }}%;height:100%;background:#0d6efd;border-radius:3px;"></div>
                            </div>
                            <div style="font-size:10px;text-align:right;color:#0d6efd;font-weight:700;">{{ $pct }}%</div>
                            @endif
                            <div class="pay-historique">
                                @forelse($dossier->paiements->sortByDesc('date_paiement') as $p)
                                <div class="pay-row" style="align-items:flex-start;gap:10px;">
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:12px;">{{ $p->date_paiement }}</div>
                                        @if($p->note)
                                            <div style="font-size:11px;color:#64748b;word-break:break-word;">{{ $p->note }}</div>
                                        @endif
                                    </div>
                                    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                        <span class="pay-amt" style="color:#16a34a;">
                                            {{ number_format($p->montant,0,',',' ') }}
                                        </span>
                                        <form action="{{ route('paiements-dossiers.destroy', $p->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Supprimer ?')"
                                                    style="background:none;border:none;color:#dc2626;cursor:pointer;">🗑</button>
                                        </form>
                                    </div>
                                </div>
                                @empty
                                <div style="color:#94a3b8;font-size:11px;">Aucun paiement</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="paiement-bloc bloc-technique">
                            <h6 style="color:#ea580c;">🛠️ Paiement Technique</h6>
                            @if($prixTech > 0)
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Référence</span>
                                <span style="font-weight:600;">{{ number_format($prixTech, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endif
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Payé</span>
                                <span class="pay-amt" style="color:#ea580c;">{{ number_format($totalTechnique, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Reste</span>
                                <span style="font-weight:700;color:{{ $resteTech > 0 ? '#dc2626' : '#16a34a' }};">
                                    {{ number_format($resteTech, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                            @if($prixTech > 0)
                            <div style="height:6px;background:#e2e8f0;border-radius:3px;margin:8px 0;">
                                @php $pctT = $prixTech > 0 ? min(100, round(($totalTechnique/$prixTech)*100)) : 0; @endphp
                                <div style="width:{{ $pctT }}%;height:100%;background:#ea580c;border-radius:3px;"></div>
                            </div>
                            <div style="font-size:10px;text-align:right;color:#ea580c;font-weight:700;">{{ $pctT }}%</div>
                            @endif
                            <div class="pay-historique">
                                @forelse($dossier->paiementsTechniques->sortByDesc('date_paiement') as $p)
                                <div class="pay-row" style="align-items:flex-start;gap:10px;">
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:12px;">{{ $p->date_paiement }}</div>
                                        @if($p->note)
                                            <div style="font-size:11px;color:#64748b;word-break:break-word;">{{ $p->note }}</div>
                                        @endif
                                    </div>
                                    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                        <span class="pay-amt" style="color:#16a34a;">
                                            {{ number_format($p->montant,0,',',' ') }}
                                        </span>
                                        <form action="{{ route('paiements-techniques.destroy', $p->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Supprimer ?')"
                                                    style="background:none;border:none;color:#dc2626;cursor:pointer;">🗑</button>
                                        </form>
                                    </div>
                                </div>
                                @empty
                                <div style="color:#94a3b8;font-size:11px;">Aucun paiement</div>
                                @endforelse
                            </div>
                        </div>

                        <div class="paiement-bloc bloc-morcel">
                            <h6 style="color:#ca8a04;">✂️ Paiement Morcellement</h6>
                            @if($prixMorcel > 0)
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Référence</span>
                                <span style="font-weight:600;">{{ number_format($prixMorcel, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endif
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Payé</span>
                                <span class="pay-amt" style="color:#ca8a04;">{{ number_format($totalMorcel, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Reste</span>
                                <span style="font-weight:700;color:{{ $resteMorcel > 0 ? '#dc2626' : '#16a34a' }};">
                                    {{ number_format($resteMorcel, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                            @if($prixMorcel > 0)
                            <div style="height:6px;background:#e2e8f0;border-radius:3px;margin:8px 0;">
                                @php $pctM = $prixMorcel > 0 ? min(100, round(($totalMorcel/$prixMorcel)*100)) : 0; @endphp
                                <div style="width:{{ $pctM }}%;height:100%;background:#ca8a04;border-radius:3px;"></div>
                            </div>
                            <div style="font-size:10px;text-align:right;color:#ca8a04;font-weight:700;">{{ $pctM }}%</div>
                            @endif
                            <div class="pay-historique">
                                @forelse($dossier->paiementsMorcellements->sortByDesc('date_paiement') as $p)
                                <div class="pay-row" style="align-items:flex-start;gap:10px;">
                                    <div style="flex:1;min-width:0;">
                                        <div style="font-size:12px;">{{ $p->date_paiement }}</div>
                                        @if($p->note)
                                            <div style="font-size:11px;color:#64748b;word-break:break-word;">{{ $p->note }}</div>
                                        @endif
                                    </div>
                                    <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                        <span class="pay-amt" style="color:#16a34a;">
                                            {{ number_format($p->montant,0,',',' ') }}
                                        </span>
                                        <form action="{{ route('paiements-morcellements.destroy', $p->id) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" onclick="return confirm('Supprimer ?')"
                                                    style="background:none;border:none;color:#dc2626;cursor:pointer;">🗑</button>
                                        </form>
                                    </div>
                                </div>
                                @empty
                                <div style="color:#94a3b8;font-size:11px;">Aucun paiement</div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Accordéon Bénéficiaires --}}
        @php
            $supDossier    = $dossier->superficie_dossier;
            $supAttribuee  = $dossier->superficie_attribuee;
            $supRestante   = $dossier->superficie_restante;
            $pctAttribue   = $dossier->pourcentage_attribue;
            $beneficiaires = $dossier->beneficiaires->sortBy('nom');
        @endphp

        <div class="accordion">
            <div class="accordion-header" onclick="toggleAccordion(this)">
                <span>👥 Bénéficiaires ({{ $beneficiaires->count() }})</span>
                <span class="chevron">▶</span>
            </div>
            <div class="accordion-content">
                <div class="accordion-body">

                    {{-- Indicateur de répartition --}}
                    <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:10px;
                                padding:12px;margin-bottom:14px;">
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;flex-wrap:wrap;gap:8px;">
                            <div style="font-size:11px;font-weight:700;color:#7c3aed;">
                                📐 Répartition de la superficie
                            </div>
                            <button onclick="ouvrirModalBenef({{ $dossier->id }}, null)"
                                    class="btn btn-sm"
                                    style="background:#7c3aed;color:white;font-size:11px;font-weight:600;">
                                + Ajouter un bénéficiaire
                            </button>
                        </div>
                        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                            <div>
                                <div style="font-size:10px;color:#64748b;">Dossier</div>
                                <div style="font-size:13px;font-weight:800;color:#1e3a5f;">
                                    {{ number_format($supDossier, 0, ',', ' ') }} m²
                                </div>
                            </div>
                            <div>
                                <div style="font-size:10px;color:#64748b;">Attribuée</div>
                                <div style="font-size:13px;font-weight:800;color:#7c3aed;">
                                    {{ number_format($supAttribuee, 0, ',', ' ') }} m²
                                </div>
                            </div>
                            <div>
                                <div style="font-size:10px;color:#64748b;">Restante</div>
                                <div style="font-size:13px;font-weight:800;
                                            color:{{ $supRestante > 0 ? '#16a34a' : '#dc2626' }};">
                                    {{ number_format($supRestante, 0, ',', ' ') }} m²
                                </div>
                            </div>
                        </div>
                        <div style="height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden;margin-top:10px;">
                            <div style="width:{{ $pctAttribue }}%;height:100%;
                                        background:{{ $pctAttribue >= 100 ? '#dc2626' : '#7c3aed' }};
                                        border-radius:4px;"></div>
                        </div>
                        <div style="font-size:10px;text-align:right;color:#7c3aed;font-weight:700;margin-top:4px;">
                            {{ $pctAttribue }}% attribué
                        </div>
                    </div>

                    {{-- Liste des bénéficiaires --}}
                    @forelse($beneficiaires as $b)
                        @php
                            $benefAffectations = $b->affectations()
                                ->with(['lot', 'bloc', 'grandSite', 'tf', 'site'])
                                ->where('statut', 'actif')
                                ->get();

                            $superficieTotale = $benefAffectations->sum(function($aff) {
                                return $aff->lot?->superficie ?? 0;
                            });

                            $benefEtapesConfig = \App\Models\Beneficiaire::etapesConfig();
                            $benefEtapesOrdre  = \App\Models\Beneficiaire::etapesOrdre();
                            $benefEtapeActuelle = $b->etape_actuelle;
                            $benefHistoriques = $dossier->historiques
                                ->where('beneficiaire_id', $b->id)
                                ->sortByDesc('created_at');

                            $lotsParGrandSite = $benefAffectations->groupBy(function($aff) {
                                return $aff->grandSite?->nom ?? 'Site inconnu';
                            });

                            $nbLotsTotal = $benefAffectations->count();
                        @endphp

                        <div class="accordion" id="benef-{{ $b->id }}" style="margin-bottom:8px;">
                            <div class="accordion-header" onclick="toggleAccordion(this)">
                                <div style="display:flex;flex-direction:column;gap:6px;flex:1;min-width:0;">
                                    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                                        <span style="font-size:16px;">👤</span>
                                        <strong style="font-size:13px;">{{ $b->nom }}</strong>
                                        @if($b->telephone)
                                            <span style="font-weight:400;color:#64748b;font-size:11px;">
                                                📞 {{ $b->telephone }}
                                            </span>
                                        @endif
                                        <span class="badge-superficie">
                                            📐 {{ number_format($superficieTotale, 0, ',', ' ') }} m²
                                        </span>
                                        <span class="badge-count">
                                            📦 {{ $nbLotsTotal }} lot(s)
                                        </span>
                                    </div>

                                    @if($benefAffectations->count() > 0)
                                    <div class="benef-summary">
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
                                                                <span style="font-weight:400;font-size:9px;">
                                                                    ({{ number_format($aff->lot->superficie, 0, ',', ' ') }} m²)
                                                                </span>
                                                            @endif
                                                        </span>
                                                    @endforeach
                                                @endforeach
                                            @endforeach
                                        @endforeach
                                    </div>
                                    @else
                                    <div style="font-size:11px;color:#94a3b8;font-style:italic;">
                                        Aucun lot affecté
                                    </div>
                                    @endif
                                </div>
                                <span class="chevron">▶</span>
                            </div>
                            <div class="accordion-content">
                                <div class="accordion-body">

                                    <div style="display:flex;gap:6px;justify-content:flex-end;margin-bottom:12px;">
                                        <button onclick='event.stopPropagation(); ouvrirModalBenef({{ $dossier->id }}, @json($b))'
                                                class="btn btn-warning btn-sm" style="font-size:11px;">
                                            ✏️ Modifier
                                        </button>
                                        <button onclick='event.stopPropagation(); supprimerBenef({{ $b->id }}, {{ $dossier->id }})'
                                                class="btn btn-danger btn-sm" style="font-size:11px;">
                                            🗑 Supprimer
                                        </button>
                                    </div>

                                    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-bottom:14px;">
                                        @if($b->cni_url)
                                            <a href="{{ $b->cni_url }}" target="_blank"
                                               style="background:#eff6ff;color:#1d4ed8;font-size:11px;
                                                      padding:4px 12px;border-radius:6px;font-weight:600;
                                                      text-decoration:none;">
                                                📎 Voir CNI
                                            </a>
                                        @endif
                                        @if($b->client_id)
                                            <span style="background:#dbeafe;color:#1d4ed8;font-size:11px;
                                                         padding:4px 12px;border-radius:6px;font-weight:700;">
                                                🔗 Client existant
                                            </span>
                                        @endif
                                        <span style="background:#f0fdf4;color:#16a34a;font-size:11px;
                                                     padding:4px 12px;border-radius:6px;font-weight:700;">
                                            📐 Total : {{ number_format($superficieTotale, 0, ',', ' ') }} m²
                                        </span>
                                    </div>

                                    @if($b->notes)
                                        <div style="font-size:11px;color:#64748b;margin-bottom:14px;
                                                    background:#f1f5f9;padding:8px 12px;border-radius:6px;">
                                            📝 {{ $b->notes }}
                                        </div>
                                    @endif

                                    {{-- Lots affectés (détails) --}}
                                    @if($benefAffectations->count() > 0)
                                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;
                                                padding:12px;margin-bottom:14px;">
                                        <div style="font-size:11px;font-weight:700;color:#16a34a;
                                                    text-transform:uppercase;margin-bottom:10px;
                                                    display:flex;justify-content:space-between;align-items:center;">
                                            <span>📦 Lots affectés ({{ $benefAffectations->count() }})</span>
                                            <span style="background:#166534;color:white;padding:3px 10px;
                                                         border-radius:8px;font-weight:700;font-size:11px;">
                                                📐 {{ number_format($superficieTotale, 0, ',', ' ') }} m²
                                            </span>
                                        </div>

                                        @php
                                            $benefGroupes = $benefAffectations->groupBy(function($aff) {
                                                return $aff->bloc_id . '-' . $aff->date_affectation?->format('Y-m-d');
                                            });
                                        @endphp

                                        <div style="display:flex;flex-wrap:wrap;gap:8px;">
                                            @foreach($benefGroupes as $grp)
                                                @php
                                                    $premier  = $grp->first();
                                                    $nbLots   = $grp->count();
                                                    $lotsList = $grp->pluck('lot.numero')->implode(', ');
                                                    $ids      = $grp->pluck('id')->implode(',');
                                                    $dateGrp  = $premier->date_affectation?->format('Y-m-d');
                                                    $notesGrp = $premier->notes;
                                                    $lotsData = $grp->map(function($a) {
                                                        return [
                                                            'id'         => $a->id,
                                                            'lot_id'     => $a->lot_affectation_id,
                                                            'numero'     => $a->lot?->numero,
                                                            'superficie' => $a->lot?->superficie,
                                                        ];
                                                    })->values();
                                                    $superficieGrp = $grp->sum(fn($a) => $a->lot?->superficie ?? 0);
                                                @endphp

                                                <div style="background:white;border:1px solid #86efac;border-radius:6px;
                                                            padding:8px 12px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;"
                                                     id="benef-groupe-{{ $premier->id }}">

                                                    <span style="font-weight:600;font-size:12px;color:#1e3a5f;">
                                                        {{ $premier->grandSite?->nom ?? '-' }}
                                                        — Bloc <strong>{{ $premier->bloc?->code ?? '-' }}</strong>
                                                        — (Lot {{ $lotsList }})
                                                    </span>

                                                    <span style="font-size:10px;color:#64748b;">
                                                        📅 {{ $premier->date_affectation?->format('d/m/Y') }}
                                                    </span>

                                                    <span style="font-size:10px;color:#166534;background:#dcfce7;
                                                                 padding:2px 8px;border-radius:4px;font-weight:700;">
                                                        📐 {{ number_format($superficieGrp, 0, ',', ' ') }} m²
                                                    </span>

                                                    <button onclick='event.stopPropagation(); ouvrirModalModifierGroupe(
                                                                {{ $b->id }},
                                                                {{ $dossier->id }},
                                                                {{ $premier->bloc_id }},
                                                                @json($premier->bloc?->code),
                                                                @json($premier->grandSite?->nom),
                                                                @json($lotsData),
                                                                @json($dateGrp),
                                                                @json($notesGrp)
                                                            )'
                                                            style="background:none;border:none;color:#f59e0b;
                                                                   cursor:pointer;font-size:14px;padding:0 4px;"
                                                            title="Modifier les lots de ce groupe">✏️</button>

                                                    <button onclick='event.stopPropagation(); annulerGroupeBenefAffectation("{{ $ids }}", this)'
                                                            style="background:none;border:none;color:#dc2626;
                                                                   cursor:pointer;font-size:13px;padding:0 4px;"
                                                            title="Annuler toutes les affectations du groupe">✕</button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- Formulaire affectation rapide --}}
                                    <div style="background:#f8fafc;border-radius:8px;padding:12px;margin-bottom:14px;
                                                border:1px solid #e2e8f0;">
                                        <div style="font-size:11px;font-weight:700;color:#64748b;
                                                    text-transform:uppercase;margin-bottom:10px;">
                                            ➕ Affecter des lots à ce bénéficiaire
                                        </div>

                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-3">
                                                <label style="font-size:10px;font-weight:600;color:#374151;">Grand Site</label>
                                                <select class="form-control form-control-sm"
                                                        id="benef-aff-gs-{{ $b->id }}"
                                                        onchange="benefAffChargerSites(this.value, {{ $b->id }})">
                                                    <option value="">-- Choisir --</option>
                                                    @foreach(\App\Models\GrandSite::orderBy('nom')->get() as $gs)
                                                        <option value="{{ $gs->id }}">{{ $gs->nom }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label style="font-size:10px;font-weight:600;color:#374151;">Site</label>
                                                <select class="form-control form-control-sm"
                                                        id="benef-aff-site-{{ $b->id }}"
                                                        onchange="benefAffChargerTfs(this.value, {{ $b->id }})">
                                                    <option value="">-- Choisir --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label style="font-size:10px;font-weight:600;color:#374151;">TF</label>
                                                <select class="form-control form-control-sm"
                                                        id="benef-aff-tf-{{ $b->id }}"
                                                        onchange="benefAffChargerBlocs(this.value, {{ $b->id }})">
                                                    <option value="">-- Choisir --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label style="font-size:10px;font-weight:600;color:#374151;">Bloc</label>
                                                <select class="form-control form-control-sm"
                                                        id="benef-aff-bloc-{{ $b->id }}"
                                                        onchange="benefAffChargerLots(this.value, {{ $b->id }})">
                                                    <option value="">-- Choisir --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label style="font-size:10px;font-weight:600;color:#374151;">Date</label>
                                                <input type="date" class="form-control form-control-sm"
                                                       id="benef-aff-date-{{ $b->id }}"
                                                       value="{{ now()->format('Y-m-d') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label style="font-size:10px;font-weight:600;color:#374151;">Notes</label>
                                                <input type="text" class="form-control form-control-sm"
                                                       id="benef-aff-notes-{{ $b->id }}"
                                                       placeholder="Optionnel">
                                            </div>
                                        </div>

                                        <div style="margin-top:8px;" id="benef-aff-lots-container-{{ $b->id }}">
                                            <div style="color:#94a3b8;font-size:11px;padding:4px 0;">
                                                ℹ️ Sélectionnez un Grand Site, Site, TF et Bloc pour voir les lots disponibles
                                            </div>
                                        </div>

                                        <div style="margin-top:8px;display:flex;gap:10px;align-items:center;">
                                            <button onclick="validerBenefAffectation({{ $b->id }})"
                                                    class="btn btn-success btn-sm" style="font-size:11px;"
                                                    id="benef-aff-btn-{{ $b->id }}" disabled>
                                                ✅ Affecter les lots sélectionnés
                                            </button>
                                            <span style="font-size:10px;color:#94a3b8;"
                                                  id="benef-aff-compteur-{{ $b->id }}">
                                                0 lot(s) sélectionné(s)
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Étapes bénéficiaire --}}
                                    <div style="background:#f8fafc;border-radius:8px;padding:12px;
                                                border:1px solid #e2e8f0;margin-bottom:14px;">
                                        <div style="font-size:11px;font-weight:700;color:#64748b;
                                                    text-transform:uppercase;margin-bottom:10px;">
                                            📊 Étapes du bénéficiaire
                                        </div>

                                        <div style="display:flex;gap:8px;flex-wrap:wrap;">
                                            @foreach($benefEtapesConfig as $cle => $cfg)
                                                @php
                                                    $ordreEtape  = $benefEtapesOrdre[$cle];
                                                    $ordreActuel = $benefEtapeActuelle ? ($benefEtapesOrdre[$benefEtapeActuelle] ?? 0) : 0;
                                                    $estFait     = $ordreEtape <= $ordreActuel;
                                                    $champ       = $cfg['champ'];
                                                    $dateEtape   = $b->$champ;
                                                @endphp

                                                <div onclick='event.stopPropagation(); ouvrirModalEtapeBenef({{ $b->id }}, "{{ $cle }}", "{{ $cfg['label'] }}", {{ $estFait ? 'true' : 'false' }}, "{{ $dateEtape ? \Carbon\Carbon::parse($dateEtape)->format('Y-m-d') : '' }}")'
                                                     style="
                                                        display:flex;align-items:center;gap:5px;
                                                        padding:6px 12px;border-radius:16px;
                                                        border:2px solid {{ $estFait ? $cfg['color'] : '#e2e8f0' }};
                                                        background:{{ $estFait ? $cfg['bg'] : 'white' }};
                                                        font-size:11px;font-weight:700;
                                                        color:{{ $estFait ? $cfg['color'] : '#94a3b8' }};
                                                        cursor:pointer;transition:all 0.2s;
                                                     ">
                                                    <span>{{ $cfg['icon'] }}</span>
                                                    {{ $cfg['label'] }}
                                                    @if($estFait && $dateEtape)
                                                        <span style="font-size:9px;background:white;padding:0 6px;border-radius:8px;border:1px solid #e2e8f0;">
                                                            {{ \Carbon\Carbon::parse($dateEtape)->format('d/m/Y') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Historique bénéficiaire --}}
                                    @if($benefHistoriques->count() > 0)
                                    <div style="background:#f8fafc;border-radius:8px;
                                                border:1px solid #e2e8f0;overflow:hidden;">

                                        <div onclick="event.stopPropagation(); toggleBenefHistorique({{ $b->id }})"
                                             style="background:#f1f5f9;padding:10px 14px;
                                                    display:flex;justify-content:space-between;
                                                    align-items:center;cursor:pointer;">
                                            <div style="font-size:11px;font-weight:700;color:#64748b;
                                                        text-transform:uppercase;display:flex;
                                                        align-items:center;gap:6px;">
                                                <span id="benef-histo-icon-{{ $b->id }}"
                                                      style="font-size:12px;transition:transform 0.2s;">▶</span>
                                                📜 Historique du bénéficiaire
                                                <span style="background:#7c3aed;color:white;
                                                             padding:1px 8px;border-radius:10px;
                                                             font-size:9px;font-weight:700;">
                                                    {{ $benefHistoriques->count() }}
                                                </span>
                                            </div>
                                        </div>

                                        <div id="benef-histo-content-{{ $b->id }}" style="display:none;padding:12px;">
                                            @foreach($benefHistoriques as $h)
                                                @php
                                                    $iconeH = match($h->type_action) {
                                                        'ajout_beneficiaire'         => '➕',
                                                        'modification_beneficiaire'  => '✏️',
                                                        'suppression_beneficiaire'   => '🗑️',
                                                        'affectation_lot'            => '📦',
                                                        'modification_affectation'   => '✏️',
                                                        'annulation_affectation'     => '↩️',
                                                        default                      => '📌',
                                                    };
                                                    $couleurH = match($h->type_action) {
                                                        'ajout_beneficiaire'         => '#16a34a',
                                                        'modification_beneficiaire'  => '#f59e0b',
                                                        'suppression_beneficiaire'   => '#dc2626',
                                                        'affectation_lot'            => '#0d6efd',
                                                        'modification_affectation'   => '#f59e0b',
                                                        'annulation_affectation'     => '#7c3aed',
                                                        default                      => '#64748b',
                                                    };

                                                    $avH = $h->donnees_avant;
                                                    $apH = $h->donnees_apres;
                                                    $departH = $apH['point_depart'] ?? [
                                                        'lot'   => $avH['lot_num'] ?? null,
                                                        'bloc'  => $avH['bloc'] ?? null,
                                                        'date'  => $avH['date'] ?? null,
                                                        'notes' => $avH['notes'] ?? null,
                                                    ];
                                                    $arriveeH = $apH['point_arrivee'] ?? [
                                                        'lot'   => $apH['lot_num'] ?? null,
                                                        'bloc'  => $apH['bloc'] ?? null,
                                                        'date'  => $apH['date'] ?? null,
                                                        'notes' => $apH['notes'] ?? null,
                                                    ];
                                                @endphp

                                                <div style="background:white;border-left:3px solid {{ $couleurH }};
                                                            border-radius:6px;padding:10px 12px;margin-bottom:8px;
                                                            font-size:11px;box-shadow:0 1px 3px rgba(0,0,0,0.04);">

                                                    <div style="color:#1e3a5f;font-weight:600;">
                                                        {{ $iconeH }} {{ $h->resume }}
                                                    </div>
                                                    <div style="font-size:9px;color:#94a3b8;margin-top:3px;">
                                                        📅 {{ $h->created_at->format('d/m/Y H:i') }}
                                                        @if($h->user)
                                                            · 👤 {{ $h->user->name }}
                                                        @endif
                                                    </div>

                                                    @if($h->type_action === 'modification_affectation' && $avH && $apH)
                                                        <details style="margin-top:8px;" open>
                                                            <summary style="cursor:pointer;color:#f59e0b;font-weight:700;
                                                                            padding:4px 0;list-style:none;font-size:11px;">
                                                                🔍 <strong>Détail des changements</strong>
                                                            </summary>
                                                            <div style="background:white;border-radius:8px;padding:10px;
                                                                        margin-top:6px;border:1px solid #e2e8f0;">

                                                                <div style="background:#fef2f2;border-left:3px solid #dc2626;
                                                                            padding:8px 10px;border-radius:6px;
                                                                            margin-bottom:8px;">
                                                                    <div style="font-size:9px;font-weight:700;color:#991b1b;
                                                                                text-transform:uppercase;margin-bottom:5px;">
                                                                        📍 Départ
                                                                    </div>
                                                                    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:4px;">
                                                                        @if($departH['lot'])
                                                                            @foreach(explode(',', $departH['lot']) as $n)
                                                                                @if(trim($n))
                                                                                    <span style="background:#fee2e2;color:#991b1b;
                                                                                                 padding:2px 8px;border-radius:4px;
                                                                                                 font-weight:700;font-size:10px;
                                                                                                 text-decoration:line-through;
                                                                                                 border:1px solid #fca5a5;">
                                                                                        Lot {{ trim($n) }}
                                                                                    </span>
                                                                                @endif
                                                                            @endforeach
                                                                        @else
                                                                            <span style="color:#7f1d1d;">—</span>
                                                                        @endif
                                                                    </div>
                                                                    <div style="font-size:10px;color:#7f1d1d;">
                                                                        📅 {{ $departH['date'] ? \Carbon\Carbon::parse($departH['date'])->format('d/m/Y') : '—' }}
                                                                        @if($departH['notes'])
                                                                            <br>📝 « {{ $departH['notes'] }} »
                                                                        @endif
                                                                    </div>
                                                                </div>

                                                                <div style="background:#f0fdf4;border-left:3px solid #16a34a;
                                                                            padding:8px 10px;border-radius:6px;">
                                                                    <div style="font-size:9px;font-weight:700;color:#166534;
                                                                                text-transform:uppercase;margin-bottom:5px;">
                                                                        🎯 Arrivée
                                                                    </div>
                                                                    <div style="display:flex;flex-wrap:wrap;gap:4px;margin-bottom:4px;">
                                                                        @if($arriveeH['lot'])
                                                                            @foreach(explode(',', $arriveeH['lot']) as $n)
                                                                                @if(trim($n))
                                                                                    <span style="background:#dcfce7;color:#166534;
                                                                                                 padding:2px 8px;border-radius:4px;
                                                                                                 font-weight:700;font-size:10px;
                                                                                                 border:1px solid #86efac;">
                                                                                        ✅ Lot {{ trim($n) }}
                                                                                    </span>
                                                                                @endif
                                                                            @endforeach
                                                                        @else
                                                                            <span style="color:#14532d;">—</span>
                                                                        @endif
                                                                    </div>
                                                                    <div style="font-size:10px;color:#14532d;">
                                                                        📅 {{ $arriveeH['date'] ? \Carbon\Carbon::parse($arriveeH['date'])->format('d/m/Y') : '—' }}
                                                                        @if($arriveeH['notes'])
                                                                            <br>📝 « {{ $arriveeH['notes'] }} »
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </details>
                                                    @endif

                                                    @if($h->type_action === 'affectation_lot' && $apH && !empty($apH['lots']))
                                                        <div style="margin-top:6px;background:#eff6ff;
                                                                    border-radius:6px;padding:6px 8px;
                                                                    border:1px dashed #93c5fd;">
                                                            <strong style="font-size:10px;color:#1d4ed8;">📦 Lots attribués :</strong>
                                                            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:4px;">
                                                                @foreach($apH['lots'] as $lot)
                                                                    <span style="background:#dbeafe;color:#1d4ed8;
                                                                                 padding:2px 8px;border-radius:4px;
                                                                                 font-size:10px;font-weight:700;">
                                                                        Lot {{ $lot['numero'] ?? '?' }}
                                                                        @if(!empty($lot['bloc'])) ({{ $lot['bloc'] }}) @endif
                                                                    </span>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                </div>
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;color:#94a3b8;font-size:12px;padding:40px;
                                    background:#f8fafc;border-radius:12px;">
                            <div style="font-size:40px;margin-bottom:10px;">👥</div>
                            <div style="font-weight:700;">Aucun bénéficiaire</div>
                            <button onclick="ouvrirModalBenef({{ $dossier->id }}, null)"
                                    class="btn btn-primary btn-sm mt-3">
                                + Ajouter un bénéficiaire
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Accordéon Historique général --}}
        @php
            $historiques = $dossier->historiques ?? collect();
        @endphp

        <div class="accordion">
            <div class="accordion-header" onclick="toggleAccordion(this)">
                <span>📜 Historique général ({{ $historiques->count() }})</span>
                <span class="chevron">▶</span>
            </div>
            <div class="accordion-content">
                <div class="accordion-body">
                    @forelse($historiques as $h)
                        <div style="background:#f8fafc;border-left:3px solid {{ $h->couleur }};
                                    border-radius:6px;padding:10px 12px;margin-bottom:8px;">
                            <div style="font-size:12px;color:#1e3a5f;font-weight:600;">
                                {{ $h->icone }} {{ $h->resume }}
                            </div>
                            <div style="font-size:10px;color:#94a3b8;margin-top:4px;">
                                📅 {{ $h->created_at->format('d/m/Y H:i') }}
                                @if($h->user)
                                    · 👤 {{ $h->user->name }}
                                @endif
                            </div>
                        </div>
                    @empty
                        <div style="text-align:center;color:#94a3b8;font-size:12px;padding:16px;">
                            Aucun historique
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>
    @endforeach
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     TAB 3 : BÉNÉFICIAIRES (vue globale)
     ═══════════════════════════════════════════════════════════════ --}}
@if($dossier)
<div id="tab-benefs" class="tab-content">
    <div class="section-card">
        <h5>👥 Tous les bénéficiaires</h5>
        <p style="color:#64748b;font-size:12px;">
            Pour voir les détails par dossier, ouvrez l'onglet <strong>📂 Dossier</strong> et sélectionnez le dossier.
        </p>

        @php
            $tousBenefs = collect();
            foreach ($client->dossiers as $d) {
                foreach ($d->beneficiaires as $b) {
                    $tousBenefs->push(['b' => $b, 'd' => $d]);
                }
            }
        @endphp

        @forelse($tousBenefs as $item)
            @php
                $b = $item['b'];
                $d = $item['d'];
                $benefAffs = $b->affectations()->with(['lot', 'bloc', 'grandSite', 'tf'])->where('statut', 'actif')->get();
                $superficieTotale = $benefAffs->sum(fn($aff) => $aff->lot?->superficie ?? 0);
            @endphp
            <div style="background:#faf5ff;border-left:3px solid #7c3aed;border-radius:8px;
                        padding:10px 12px;margin-bottom:8px;font-size:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
                    <strong style="color:#1e3a5f;">👤 {{ $b->nom }}</strong>
                    <span style="font-size:11px;color:#7c3aed;font-weight:700;">
                        📐 {{ number_format($superficieTotale, 0, ',', ' ') }} m²
                    </span>
                </div>
                <div style="font-size:10px;color:#64748b;margin-top:4px;">
                    📂 {{ $d->nom_dossier }}
                    @if($b->telephone) · 📞 {{ $b->telephone }} @endif
                    · 📦 {{ $benefAffs->count() }} lot(s)
                </div>
            </div>
        @empty
            <div style="text-align:center;color:#94a3b8;font-size:12px;padding:30px;">
                Aucun bénéficiaire
            </div>
        @endforelse
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════
     TAB 4 : VISITES
     ═══════════════════════════════════════════════════════════════ --}}
<div id="tab-visites" class="tab-content">
    <div class="section-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">🚶 Historique des visites ({{ $client->visites->count() }})</h5>
            <a href="{{ route('visites.index', ['nom' => $client->name]) }}"
               class="btn btn-outline-primary btn-sm" style="font-size:11px;">
                Voir tout →
            </a>
        </div>

        @php
            $typeColors = ['client'=>'#1d4ed8','proprietaire'=>'#15803d','autre'=>'#475569'];
            $typeLabels = ['client'=>'Client','proprietaire'=>'Propriétaire','autre'=>'Autre'];
        @endphp

        @forelse($client->visites->sortByDesc('date_visite') as $visite)
            @php $color = $typeColors[$visite->type_personne] ?? '#475569'; @endphp
            <div style="border-left:3px solid {{ $color }};
                        padding:10px 12px;margin-bottom:8px;
                        background:#f8fafc;border-radius:6px;font-size:12px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <strong style="color:#1e3a5f;">
                        {{ $visite->visiteur?->nom ?? $visite->nom ?? 'Inconnu' }}
                    </strong>
                    <span style="background:{{ $color }}22;color:{{ $color }};
                                 font-size:9px;padding:1px 6px;border-radius:4px;
                                 font-weight:600;">
                        {{ $typeLabels[$visite->type_personne] ?? $visite->type_personne }}
                    </span>
                </div>
                <div style="color:#64748b;margin-top:4px;">
                    📅 {{ \Carbon\Carbon::parse($visite->date_visite)->format('d/m/Y') }}
                    @if($visite->heure_arrivee)
                        · 🕐 {{ substr($visite->heure_arrivee, 0, 5) }}
                    @endif
                    @if($visite->heure_depart)
                        · 🚪 {{ substr($visite->heure_depart, 0, 5) }}
                    @endif
                </div>
                @if($visite->note)
                    <div style="color:#475569;margin-top:4px;font-style:italic;">
                        📝 {{ $visite->note }}
                    </div>
                @endif
            </div>
        @empty
            <div style="text-align:center;color:#94a3b8;font-size:12px;padding:30px;">
                Aucune visite enregistrée
            </div>
        @endforelse
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════
     MODALS
     ═══════════════════════════════════════════════════════════════ --}}

{{-- MODAL ÉTAPE DOSSIER --}}
<div class="modal-overlay" id="modalEtapeOverlay" onclick="fermerModalEtape()"></div>
<div class="modal-box" id="modalEtape">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;" id="modalEtapeTitre">📅 Définir la date</h5>
        <button onclick="fermerModalEtape()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <div class="mb-3">
        <p id="modalEtapeLabel" style="font-size:13px;color:#64748b;margin-bottom:10px;"></p>
        <input type="date" id="modalEtapeDate" class="form-control">
    </div>
    <div style="display:flex;justify-content:space-between;gap:10px;margin-top:10px;">
        <button onclick="supprimerEtape()" class="btn btn-danger btn-sm" id="btnSupprimerEtape" style="display:none;">
            🗑 Supprimer
        </button>
        <div style="display:flex;gap:10px;margin-left:auto;">
            <button onclick="fermerModalEtape()" class="btn btn-light">Annuler</button>
            <button onclick="validerEtape()" class="btn btn-primary">✅ Valider</button>
        </div>
    </div>
</div>

{{-- MODAL ÉTAPE BÉNÉFICIAIRE --}}
<div class="modal-overlay" id="modalEtapeBenefOverlay" onclick="fermerModalEtapeBenef()"></div>
<div class="modal-box" id="modalEtapeBenef">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;" id="modalEtapeBenefTitre">📅 Définir la date</h5>
        <button onclick="fermerModalEtapeBenef()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <div class="mb-3">
        <p id="modalEtapeBenefLabel" style="font-size:13px;color:#64748b;margin-bottom:10px;"></p>
        <input type="date" id="modalEtapeBenefDate" class="form-control">
    </div>
    <div style="display:flex;justify-content:space-between;gap:10px;margin-top:10px;">
        <button onclick="supprimerEtapeBenef()" class="btn btn-danger btn-sm" id="btnSupprimerEtapeBenef" style="display:none;">
            🗑 Supprimer
        </button>
        <div style="display:flex;gap:10px;margin-left:auto;">
            <button onclick="fermerModalEtapeBenef()" class="btn btn-light">Annuler</button>
            <button onclick="validerEtapeBenef()" class="btn btn-primary">✅ Valider</button>
        </div>
    </div>
</div>

{{-- MODAL MODIFIER GROUPE --}}
<div class="modal-overlay" id="modalModifAffectOverlay" onclick="fermerModalModifAffect()"></div>
<div class="modal-box" id="modalModifAffect" style="width:800px; max-width:95%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#f59e0b;font-weight:800;margin:0;">✏️ Modifier les affectations</h5>
        <button onclick="fermerModalModifAffect()"
                style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>

    <input type="hidden" id="modifAffectBenefId">
    <input type="hidden" id="modifAffectDossierId">

    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;
                padding:14px;margin-bottom:16px;">
        <div style="font-size:12px;font-weight:700;color:#16a34a;
                    text-transform:uppercase;margin-bottom:10px;
                    display:flex;justify-content:space-between;align-items:center;">
            <span>📦 Lots actuellement affectés</span>
            <span style="font-size:10px;color:#94a3b8;font-weight:normal;text-transform:none;">
                Décochez pour retirer
            </span>
        </div>
        <div id="modifAffectLotsActuels"
             style="display:flex;flex-wrap:wrap;gap:8px;min-height:40px;">
        </div>
    </div>

    <div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:8px;
                padding:14px;margin-bottom:16px;">
        <div style="font-size:12px;font-weight:700;color:#1d4ed8;
                    text-transform:uppercase;margin-bottom:10px;">
            ➕ Ajouter des lots (n'importe quel site)
        </div>

        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size:10px;font-weight:600;color:#374151;">Grand Site</label>
                <select class="form-control form-control-sm" id="modifAffectGs"
                        onchange="modifAffChargerSites(this.value)">
                    <option value="">-- Choisir --</option>
                    @foreach(\App\Models\GrandSite::orderBy('nom')->get() as $gs)
                        <option value="{{ $gs->id }}">{{ $gs->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label style="font-size:10px;font-weight:600;color:#374151;">Site</label>
                <select class="form-control form-control-sm" id="modifAffectSite"
                        onchange="modifAffChargerTfs(this.value)">
                    <option value="">-- Choisir --</option>
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size:10px;font-weight:600;color:#374151;">TF</label>
                <select class="form-control form-control-sm" id="modifAffectTf"
                        onchange="modifAffChargerBlocs(this.value)">
                    <option value="">-- Choisir --</option>
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size:10px;font-weight:600;color:#374151;">Bloc</label>
                <select class="form-control form-control-sm" id="modifAffectBloc"
                        onchange="modifAffChargerLots(this.value)">
                    <option value="">-- Choisir --</option>
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size:10px;font-weight:600;color:#374151;">&nbsp;</label>
                <button onclick="modifAffReset()" class="btn btn-outline-secondary btn-sm w-100"
                        style="font-size:10px;">
                    🔄 Reset
                </button>
            </div>
        </div>

        <div id="modifAffectLotsDisponibles" style="margin-top:10px;">
            <div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">
                ℹ️ Sélectionnez Grand Site → Site → TF → Bloc pour voir les lots
            </div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:16px;">
        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Date *</label>
            <input type="date" id="modifAffectDate" class="form-control form-control-sm">
        </div>
        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">📝 Notes</label>
            <input type="text" id="modifAffectNotes" class="form-control form-control-sm"
                   placeholder="Optionnel">
        </div>
    </div>

    <div id="modifAffectResume"
         style="background:#fef3c7;border:1px solid #fcd34d;border-radius:8px;
                padding:12px;margin-bottom:14px;font-size:12px;
                color:#78350f;display:none;">
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button onclick="fermerModalModifAffect()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="sauvegarderModifAffect()" class="btn btn-warning btn-sm" style="font-weight:700;">
            💾 Enregistrer les modifications
        </button>
    </div>
</div>

{{-- MODAL HISTORIQUE AFFECTATIONS --}}
<div class="modal-overlay" id="modalHistoAffectOverlay" onclick="fermerHistoAffect()"></div>
<div class="modal-box" id="modalHistoAffect" style="width:800px; max-width:95%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#0d6efd;font-weight:800;margin:0;">
            📜 Historique des affectations
        </h5>
        <button onclick="fermerHistoAffect()"
                style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>

    <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:10px;">
        <button onclick="filtrerHistoAffect('tous')" class="btn btn-sm btn-primary"
                id="btn-histo-tous" style="font-size:11px;">Tous</button>
        <button onclick="filtrerHistoAffect('affectation_lot')" class="btn btn-sm btn-outline-primary"
                id="btn-histo-affect" style="font-size:11px;">📦 Affectations</button>
        <button onclick="filtrerHistoAffect('modification_affectation')" class="btn btn-sm btn-outline-warning"
                id="btn-histo-modif" style="font-size:11px;">✏️ Modifications</button>
        <button onclick="filtrerHistoAffect('annulation_affectation')" class="btn btn-sm btn-outline-danger"
                id="btn-histo-annul" style="font-size:11px;">↩️ Annulations</button>
    </div>

    <div id="histoAffectContent" style="max-height:550px;overflow-y:auto;
                                          padding:10px;background:#f8fafc;border-radius:8px;">
        <div style="text-align:center;color:#94a3b8;padding:20px;">⏳ Chargement...</div>
    </div>
</div>

{{-- MODAL BÉNÉFICIAIRE --}}
<div class="modal-overlay" id="benefOverlay" onclick="fermerModalBenef()"></div>
<div class="modal-box" id="benefModal" style="width:750px; max-width:95%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;" id="benefTitre">👥 Ajouter un bénéficiaire</h5>
        <button onclick="fermerModalBenef()"
                style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>

    <input type="hidden" id="benefId">
    <input type="hidden" id="benefDossierId">

    <div style="display:grid;gap:10px;">
        <div id="benefClientWrapper">
            <label style="font-size:11px;font-weight:700;color:#64748b;">
                🔗 Utiliser le client du dossier comme bénéficiaire
            </label>
            <select id="benefClientId" class="form-control form-control-sm"
                    onchange="prefillBenefFromClient(this)">
                <option value="">-- Nouveau bénéficiaire --</option>
                @if($client)
                    <option value="{{ $client->id }}"
                            data-nom="{{ $client->name }}"
                            data-phone="{{ $client->phone }}"
                            data-sexe="{{ $client->sexe }}">
                        👤 {{ $client->name }} ({{ $client->phone }})
                    </option>
                @endif
            </select>
            <div id="benefClientInfo" style="font-size:10px;color:#7c3aed;margin-top:4px;"></div>
        </div>

        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">Nom et prénom(s) *</label>
            <input type="text" id="benefNom" class="form-control form-control-sm">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
                <label style="font-size:11px;font-weight:700;color:#64748b;">Téléphone</label>
                <input type="text" id="benefTelephone" class="form-control form-control-sm">
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#64748b;">
                    CNI (obligatoire) <span id="benefCniOblig">*</span>
                </label>
                <input type="file" id="benefCni" class="form-control form-control-sm"
                       accept="image/*,application/pdf">
                <div id="benefCniActuelle" style="font-size:10px;margin-top:4px;"></div>
            </div>
        </div>

        <div id="benefLotsSection" style="background:#f0fdf4;border:1px solid #bbf7d0;
                                            border-radius:8px;padding:12px;">
            <div style="font-size:11px;font-weight:700;color:#16a34a;
                        text-transform:uppercase;margin-bottom:10px;">
                📦 Sélection des lots *
            </div>

            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label style="font-size:10px;font-weight:600;color:#374151;">Grand Site *</label>
                    <select class="form-control form-control-sm" id="benefLotGs"
                            onchange="benefLotChargerSites(this.value)">
                        <option value="">-- Choisir --</option>
                        @foreach(\App\Models\GrandSite::orderBy('nom')->get() as $gs)
                            <option value="{{ $gs->id }}">{{ $gs->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label style="font-size:10px;font-weight:600;color:#374151;">Site *</label>
                    <select class="form-control form-control-sm" id="benefLotSite"
                            onchange="benefLotChargerTfs(this.value)">
                        <option value="">-- Choisir --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label style="font-size:10px;font-weight:600;color:#374151;">TF *</label>
                    <select class="form-control form-control-sm" id="benefLotTf"
                            onchange="benefLotChargerBlocs(this.value)">
                        <option value="">-- Choisir --</option>
                    </select>
                </div>
            </div>

            <div class="row g-2 align-items-end" style="margin-top:8px;">
                <div class="col-md-4">
                    <label style="font-size:10px;font-weight:600;color:#374151;">Bloc *</label>
                    <select class="form-control form-control-sm" id="benefLotBloc"
                            onchange="benefLotChargerLots(this.value)">
                        <option value="">-- Choisir --</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label style="font-size:10px;font-weight:600;color:#374151;">Date d'affectation</label>
                    <input type="date" class="form-control form-control-sm" id="benefLotDate"
                           value="{{ now()->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label style="font-size:10px;font-weight:600;color:#374151;">Notes</label>
                    <input type="text" class="form-control form-control-sm" id="benefNotes"
                           placeholder="Optionnel">
                </div>
            </div>

            <div id="benefLotListe" style="margin-top:10px;max-height:200px;overflow-y:auto;
                                            background:white;border-radius:6px;padding:8px;
                                            border:1px solid #e2e8f0;">
                <div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">
                    ℹ️ Sélectionnez un Bloc pour voir les lots disponibles
                </div>
            </div>

            <div id="benefLotResume" style="margin-top:10px;background:#dcfce7;
                                             border:1px solid #86efac;border-radius:6px;
                                             padding:8px;font-size:11px;color:#166534;
                                             display:none;">
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button onclick="fermerModalBenef()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="sauvegarderBenef()" class="btn btn-sm"
                style="background:#7c3aed;color:white;font-weight:600;">
            💾 Enregistrer
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF = window.CSRF || '{{ csrf_token() }}';

// VARIABLES GLOBALES
let modalDossierId = null;
let modalEtapeKey = null;
let modalBenefId = null;
let modalEtapeBenefKey = null;
let modifAffectGroupe = {
    benefId: null, dossierId: null,
    lotsActuels: [],
    lotsSelectionnes: new Set(),
    lotsAAjouter: new Set(),
};
let histoAffectData = [];
let histoAffectFiltre = 'tous';
let benefEditId = null;
let benefLotsSelectionnes = new Set();

// ════════════════════════════════════════════════════════════════
// NAVIGATION PAR ONGLETS PRINCIPAUX
// ════════════════════════════════════════════════════════════════
function switchTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.main-tab').forEach(t => t.classList.remove('active'));
    const tab = document.getElementById(tabId);
    if (tab) tab.classList.add('active');
    const btn = document.querySelector(`.main-tab[data-tab="${tabId}"]`);
    if (btn) btn.classList.add('active');
}

// ════════════════════════════════════════════════════════════════
// ✅ NAVIGATION ENTRE DOSSIERS (ONGLETS DOSSIERS)
// ════════════════════════════════════════════════════════════════
function showDossier(id, tab, event) {
    if (event) event.preventDefault();

    // Cacher tous les dossiers
    document.querySelectorAll('.dossier-panel').forEach(p => {
        p.style.display = 'none';
    });

    // Retirer active de tous les onglets dossier
    document.querySelectorAll('.dossier-tab').forEach(t => {
        t.classList.remove('active');
    });

    // Afficher le dossier ciblé
    const panel = document.getElementById(id);
    if (panel) {
        panel.style.display = 'block';
        // Scroll smooth vers le haut du panneau
        setTimeout(() => {
            panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 100);
    }

    // Activer l'onglet cliqué
    if (tab) tab.classList.add('active');
}

// ════════════════════════════════════════════════════════════════
// ACCORDÉONS
// ════════════════════════════════════════════════════════════════
function toggleAccordion(header) {
    const content = header.nextElementSibling;
    header.classList.toggle('open');
    content.classList.toggle('open');
}

// ════════════════════════════════════════════════════════════════
// TOAST
// ════════════════════════════════════════════════════════════════
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

function afficherNotification(message, couleur = '#16a34a') {
    const anciennes = document.querySelectorAll('.toast-notification');
    anciennes.forEach(el => el.remove());
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.style.background = couleur;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        setTimeout(() => toast.remove(), 400);
    }, 3000);
}

// ════════════════════════════════════════════════════════════════
// TOGGLE NEW
// ════════════════════════════════════════════════════════════════
function toggleNew(clientId) {
    const btn = document.getElementById('btn-new-detail-' + clientId);
    const badge = document.getElementById('badge-detail-' + clientId);
    if (!btn) return;
    btn.disabled = true;
    btn.textContent = '⏳ ...';
    fetch(`/admin/suivi-client/toggle-new/${clientId}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
        body: JSON.stringify({})
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            if (data.is_new) {
                btn.className = 'btn-toggle-new is-new';
                btn.innerHTML = '✅ Nouveau';
                badge.className = 'badge-new';
                badge.textContent = '🆕 Nouveau';
                showToast('✅ Client marqué comme nouveau');
            } else {
                btn.className = 'btn-toggle-new';
                btn.innerHTML = '🔄 Marquer nouveau';
                badge.className = 'badge-old';
                badge.textContent = 'Ancien';
                showToast('✅ Statut retiré');
            }
        } else showToast('❌ ' + data.message);
    })
    .catch(e => showToast('❌ Erreur réseau'))
    .finally(() => btn.disabled = false);
}

// ════════════════════════════════════════════════════════════════
// WHATSAPP DOSSIER
// ════════════════════════════════════════════════════════════════
function envoyerWhatsAppDossier(dossierId) {
    if (window.EdenLoader) window.EdenLoader.show();
    fetch(`/admin/dossiers/${dossierId}/whatsapp`, {
        headers: { 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) window.open(data.whatsapp_url, '_blank');
        else showToast('❌ ' + data.message);
    })
    .catch(e => { if (window.EdenLoader) window.EdenLoader.hide(); showToast('❌ Erreur réseau'); });
}

// ════════════════════════════════════════════════════════════════
// ÉTAPES DOSSIER
// ════════════════════════════════════════════════════════════════
function ouvrirModalEtape(dossierId, etape, label, estFait, dateActuelle) {
    modalDossierId = dossierId;
    modalEtapeKey = etape;
    document.getElementById('modalEtapeTitre').textContent = '📅 ' + label;
    document.getElementById('modalEtapeLabel').textContent = 'Sélectionnez la date pour "' + label + '"';
    document.getElementById('modalEtapeDate').value = dateActuelle || new Date().toISOString().split('T')[0];
    document.getElementById('btnSupprimerEtape').style.display = estFait ? 'inline-block' : 'none';
    document.getElementById('modalEtapeOverlay').style.display = 'block';
    document.getElementById('modalEtape').style.display = 'block';
}

function fermerModalEtape() {
    document.getElementById('modalEtapeOverlay').style.display = 'none';
    document.getElementById('modalEtape').style.display = 'none';
}

function supprimerEtape() {
    if (!confirm('Supprimer cette étape ?')) return;
    fermerModalEtape();
    if (window.EdenLoader) window.EdenLoader.show();
    fetch(`/admin/dossiers/${modalDossierId}/maj-etape`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ etape: modalEtapeKey, date: null, active: false }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) { afficherNotification('🗑️ Étape supprimée !', '#dc2626'); setTimeout(() => location.reload(), 800); }
        else afficherNotification(data.message || 'Erreur', '#dc2626');
    })
    .catch(e => { if (window.EdenLoader) window.EdenLoader.hide(); afficherNotification('Erreur réseau', '#dc2626'); });
}

function validerEtape() {
    const date = document.getElementById('modalEtapeDate').value;
    if (!date) { afficherNotification('⚠️ Date requise', '#dc2626'); return; }
    fermerModalEtape();
    if (window.EdenLoader) window.EdenLoader.show();
    fetch(`/admin/dossiers/${modalDossierId}/maj-etape`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ etape: modalEtapeKey, date: date, active: true }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) { afficherNotification('✅ Étape mise à jour !', '#16a34a'); setTimeout(() => location.reload(), 800); }
        else afficherNotification(data.message || 'Erreur', '#dc2626');
    })
    .catch(e => { if (window.EdenLoader) window.EdenLoader.hide(); afficherNotification('Erreur réseau', '#dc2626'); });
}

// ════════════════════════════════════════════════════════════════
// ÉTAPES BÉNÉFICIAIRE
// ════════════════════════════════════════════════════════════════
function ouvrirModalEtapeBenef(benefId, etape, label, estFait, dateActuelle) {
    modalBenefId = benefId;
    modalEtapeBenefKey = etape;
    document.getElementById('modalEtapeBenefTitre').textContent = '📅 ' + label;
    document.getElementById('modalEtapeBenefLabel').textContent = 'Sélectionnez la date pour "' + label + '"';
    document.getElementById('modalEtapeBenefDate').value = dateActuelle || new Date().toISOString().split('T')[0];
    document.getElementById('btnSupprimerEtapeBenef').style.display = estFait ? 'inline-block' : 'none';
    document.getElementById('modalEtapeBenefOverlay').style.display = 'block';
    document.getElementById('modalEtapeBenef').style.display = 'block';
}

function fermerModalEtapeBenef() {
    document.getElementById('modalEtapeBenefOverlay').style.display = 'none';
    document.getElementById('modalEtapeBenef').style.display = 'none';
}

function validerEtapeBenef() {
    const date = document.getElementById('modalEtapeBenefDate').value;
    if (!date) { afficherNotification('⚠️ Date requise', '#dc2626'); return; }
    fermerModalEtapeBenef();
    if (window.EdenLoader) window.EdenLoader.show();
    fetch(`/admin/beneficiaires/${modalBenefId}/maj-etape`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ etape: modalEtapeBenefKey, date: date, active: true }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) { afficherNotification('✅ Étape mise à jour !', '#16a34a'); setTimeout(() => location.reload(), 800); }
        else afficherNotification(data.message || 'Erreur', '#dc2626');
    })
    .catch(e => { if (window.EdenLoader) window.EdenLoader.hide(); afficherNotification('Erreur réseau', '#dc2626'); });
}

function supprimerEtapeBenef() {
    if (!confirm('Supprimer cette étape ?')) return;
    fermerModalEtapeBenef();
    if (window.EdenLoader) window.EdenLoader.show();
    fetch(`/admin/beneficiaires/${modalBenefId}/maj-etape`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ etape: modalEtapeBenefKey, date: null, active: false }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) { afficherNotification('🗑️ Étape supprimée !', '#dc2626'); setTimeout(() => location.reload(), 800); }
        else afficherNotification(data.message || 'Erreur', '#dc2626');
    })
    .catch(e => { if (window.EdenLoader) window.EdenLoader.hide(); afficherNotification('Erreur réseau', '#dc2626'); });
}

// ════════════════════════════════════════════════════════════════
// TOGGLE HISTORIQUE BÉNÉFICIAIRE
// ════════════════════════════════════════════════════════════════
function toggleBenefHistorique(benefId) {
    const content = document.getElementById('benef-histo-content-' + benefId);
    const icon    = document.getElementById('benef-histo-icon-' + benefId);
    if (!content) return;
    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        if (icon) icon.style.transform = 'rotate(90deg)';
    } else {
        content.style.display = 'none';
        if (icon) icon.style.transform = 'rotate(0deg)';
    }
}

// ════════════════════════════════════════════════════════════════
// MODAL MODIFIER GROUPE
// ════════════════════════════════════════════════════════════════
function ouvrirModalModifierGroupe(benefId, dossierId, blocId, blocCode, grandSiteNom,
                                    lotsActuels, dateAffectation, notes) {
    modifAffectGroupe = {
        benefId, dossierId,
        lotsActuels: lotsActuels || [],
        lotsSelectionnes: new Set((lotsActuels || []).map(l => l.id)),
        lotsAAjouter: new Set(),
    };

    document.getElementById('modifAffectBenefId').value   = benefId;
    document.getElementById('modifAffectDossierId').value = dossierId;

    modifAffReset();

    document.getElementById('modifAffectDate').value = dateAffectation || new Date().toISOString().split('T')[0];
    document.getElementById('modifAffectNotes').value = notes || '';

    afficherLotsActuels();
    document.getElementById('modifAffectResume').style.display = 'none';

    document.getElementById('modalModifAffectOverlay').style.display = 'block';
    document.getElementById('modalModifAffect').style.display = 'block';
}

function modifAffReset() {
    document.getElementById('modifAffectGs').value = '';
    document.getElementById('modifAffectSite').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('modifAffectTf').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('modifAffectBloc').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('modifAffectLotsDisponibles').innerHTML =
        '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">' +
        'ℹ️ Sélectionnez Grand Site → Site → TF → Bloc pour voir les lots</div>';
}

function fermerModalModifAffect() {
    document.getElementById('modalModifAffectOverlay').style.display = 'none';
    document.getElementById('modalModifAffect').style.display = 'none';
}

function afficherLotsActuels() {
    const container = document.getElementById('modifAffectLotsActuels');
    const lots = modifAffectGroupe.lotsActuels;

    if (!lots.length) {
        container.innerHTML = `<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;width:100%;">
            Aucun lot actuellement affecté
        </div>`;
        return;
    }

    container.innerHTML = lots.map(lot => {
        const checked = modifAffectGroupe.lotsSelectionnes.has(lot.id);
        return `
            <label style="display:inline-flex;align-items:center;gap:6px;
                          background:${checked ? '#dcfce7' : '#fee2e2'};
                          border:2px solid ${checked ? '#16a34a' : '#dc2626'};
                          border-radius:8px;padding:6px 12px;cursor:pointer;
                          font-size:11px;font-weight:700;
                          color:${checked ? '#16a34a' : '#dc2626'};
                          transition:all 0.2s;"
                   id="modif-lot-actuel-${lot.id}">
                <input type="checkbox" ${checked ? 'checked' : ''}
                       style="width:14px;height:14px;"
                       onchange="toggleLotActuel(${lot.id}, this.checked)">
                📦 Lot ${lot.numero}
                ${lot.superficie
                    ? '<span style="color:#64748b;font-size:9px;font-weight:normal;">'
                      + parseInt(lot.superficie).toLocaleString('fr-FR') + ' m²</span>'
                    : ''}
                ${checked
                    ? '<span style="color:#16a34a;font-size:10px;">✓ Gardé</span>'
                    : '<span style="color:#dc2626;font-size:10px;">✕ Retiré</span>'}
            </label>
        `;
    }).join('');
}

function toggleLotActuel(affectationId, checked) {
    if (checked) modifAffectGroupe.lotsSelectionnes.add(affectationId);
    else modifAffectGroupe.lotsSelectionnes.delete(affectationId);
    afficherLotsActuels();
    updateResume();
}

function modifAffChargerSites(gsId) {
    const sel = document.getElementById('modifAffectSite');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('modifAffectTf').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('modifAffectBloc').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('modifAffectLotsDisponibles').innerHTML = '';
    if (!gsId) return;
    fetch(`/admin/affectations/api/sites/${gsId}`)
        .then(r => r.json())
        .then(sites => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                sites.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        });
}

function modifAffChargerTfs(siteId) {
    const sel = document.getElementById('modifAffectTf');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('modifAffectBloc').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('modifAffectLotsDisponibles').innerHTML = '';
    if (!siteId) return;
    fetch(`/admin/affectations/api/tfs/${siteId}`)
        .then(r => r.json())
        .then(tfs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                tfs.map(t => `<option value="${t.id}">${t.title}</option>`).join('');
        });
}

function modifAffChargerBlocs(tfId) {
    const sel = document.getElementById('modifAffectBloc');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('modifAffectLotsDisponibles').innerHTML = '';
    if (!tfId) return;
    fetch(`/admin/affectations/api/blocs/${tfId}`)
        .then(r => r.json())
        .then(blocs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                blocs.map(b => `<option value="${b.id}">Bloc ${b.code}</option>`).join('');
        });
}

function modifAffChargerLots(blocId) {
    const container = document.getElementById('modifAffectLotsDisponibles');
    if (!blocId) { container.innerHTML = ''; return; }

    container.innerHTML = '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">⏳ Chargement...</div>';

    fetch(`/admin/affectations/api/lots/${blocId}`)
        .then(r => r.json())
        .then(lots => {
            const lotsActuelsIds = modifAffectGroupe.lotsActuels.map(l => l.lot_id);
            const lotsFiltres = lots.filter(l => !lotsActuelsIds.includes(l.id));

            if (!lotsFiltres.length) {
                container.innerHTML = '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">Aucun lot disponible dans ce bloc</div>';
                return;
            }

            container.innerHTML = `
                <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:6px;">
                    Cochez les lots à ajouter :
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    ${lotsFiltres.map(lot => {
                        const checked = modifAffectGroupe.lotsAAjouter.has(lot.id);
                        return `
                            <label style="display:inline-flex;align-items:center;gap:4px;
                                          background:${checked ? '#dbeafe' : 'white'};
                                          border:2px solid ${checked ? '#1d4ed8' : '#e2e8f0'};
                                          border-radius:6px;padding:4px 10px;cursor:pointer;
                                          font-size:11px;font-weight:600;
                                          color:${checked ? '#1d4ed8' : '#1e3a5f'};"
                                   id="modif-lot-ajout-${lot.id}">
                                <input type="checkbox" ${checked ? 'checked' : ''}
                                       style="width:14px;height:14px;"
                                       onchange="toggleLotAjouter(${lot.id}, this.checked)">
                                📦 Lot ${lot.numero}
                                ${lot.superficie
                                    ? '<span style="color:#64748b;font-size:9px;font-weight:normal;">'
                                      + parseInt(lot.superficie).toLocaleString('fr-FR') + ' m²</span>'
                                    : ''}
                            </label>
                        `;
                    }).join('')}
                </div>
            `;
        });
}

function toggleLotAjouter(lotId, checked) {
    if (checked) modifAffectGroupe.lotsAAjouter.add(lotId);
    else modifAffectGroupe.lotsAAjouter.delete(lotId);

    const label = document.getElementById('modif-lot-ajout-' + lotId);
    if (label) {
        if (checked) {
            label.style.background = '#dbeafe';
            label.style.borderColor = '#1d4ed8';
            label.style.color = '#1d4ed8';
        } else {
            label.style.background = 'white';
            label.style.borderColor = '#e2e8f0';
            label.style.color = '#1e3a5f';
        }
    }
    updateResume();
}

function updateResume() {
    const resume = document.getElementById('modifAffectResume');
    const totalActuels = modifAffectGroupe.lotsActuels.length;
    const totalGardes  = modifAffectGroupe.lotsSelectionnes.size;
    const totalRetires = totalActuels - totalGardes;
    const totalAjoutes = modifAffectGroupe.lotsAAjouter.size;

    if (totalRetires === 0 && totalAjoutes === 0) {
        resume.style.display = 'none';
        return;
    }

    resume.style.display = 'block';
    resume.innerHTML = `
        <div style="font-weight:700;margin-bottom:6px;">📊 Résumé :</div>
        <div style="display:flex;gap:12px;flex-wrap:wrap;">
            ${totalGardes > 0 ? `<span style="background:#dcfce7;color:#16a34a;padding:3px 10px;border-radius:6px;font-weight:700;">
                ✓ ${totalGardes} conservé(s)
            </span>` : ''}
            ${totalRetires > 0 ? `<span style="background:#fee2e2;color:#dc2626;padding:3px 10px;border-radius:6px;font-weight:700;">
                ✕ ${totalRetires} retiré(s)
            </span>` : ''}
            ${totalAjoutes > 0 ? `<span style="background:#dbeafe;color:#1d4ed8;padding:3px 10px;border-radius:6px;font-weight:700;">
                ➕ ${totalAjoutes} à ajouter
            </span>` : ''}
        </div>
    `;
}

function sauvegarderModifAffect() {
    const benefId = modifAffectGroupe.benefId;
    const date    = document.getElementById('modifAffectDate').value;
    const notes   = document.getElementById('modifAffectNotes').value;

    if (!date) { alert('⚠️ La date est obligatoire.'); return; }

    const lotsARetirer = modifAffectGroupe.lotsActuels
        .filter(l => !modifAffectGroupe.lotsSelectionnes.has(l.id))
        .map(l => l.id);
    const lotsAAjouter = Array.from(modifAffectGroupe.lotsAAjouter);

    if (window.EdenLoader) window.EdenLoader.show();

    fetch(`/admin/beneficiaires/${benefId}/modifier-lots`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            date_affectation: date,
            notes: notes,
            lots_a_retirer: lotsARetirer,
            lots_a_ajouter: lotsAAjouter,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('✅ ' + (data.message || 'Modifications enregistrées'));
            fermerModalModifAffect();
            setTimeout(() => location.reload(), 900);
        } else {
            let msg = data.message || 'Erreur';
            if (data.errors) msg = Object.values(data.errors).flat().join('\n');
            alert('❌ ' + msg);
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        alert('Erreur réseau : ' + e.message);
    });
}

// ════════════════════════════════════════════════════════════════
// HISTORIQUE AFFECTATIONS
// ════════════════════════════════════════════════════════════════
function ouvrirHistoriqueAffectations(dossierId) {
    document.getElementById('modalHistoAffectOverlay').style.display = 'block';
    document.getElementById('modalHistoAffect').style.display = 'block';
    document.getElementById('histoAffectContent').innerHTML =
        '<div style="text-align:center;color:#94a3b8;padding:20px;">⏳ Chargement...</div>';

    filtrerHistoAffect('tous');

    fetch(`/admin/dossiers/${dossierId}/historique`, {
        headers: { 'X-CSRF-TOKEN': CSRF }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            histoAffectData = (data.historiques || []).filter(h =>
                ['affectation_lot', 'modification_affectation', 'annulation_affectation'].includes(h.type_action)
            );
            afficherHistoriqueAffectations();
        } else {
            document.getElementById('histoAffectContent').innerHTML =
                '<div style="text-align:center;color:#dc2626;padding:20px;">Erreur de chargement</div>';
        }
    })
    .catch(e => {
        document.getElementById('histoAffectContent').innerHTML =
            '<div style="text-align:center;color:#dc2626;padding:20px;">Erreur réseau</div>';
    });
}

function fermerHistoAffect() {
    document.getElementById('modalHistoAffectOverlay').style.display = 'none';
    document.getElementById('modalHistoAffect').style.display = 'none';
    histoAffectData = [];
}

function filtrerHistoAffect(type) {
    histoAffectFiltre = type;
    const buttons = {
        'tous': 'btn-histo-tous',
        'affectation_lot': 'btn-histo-affect',
        'modification_affectation': 'btn-histo-modif',
        'annulation_affectation': 'btn-histo-annul',
    };
    Object.entries(buttons).forEach(([key, id]) => {
        const btn = document.getElementById(id);
        if (!btn) return;
        if (key === 'tous') btn.className = 'btn btn-sm ' + (key === histoAffectFiltre ? 'btn-primary' : 'btn-outline-primary');
        else if (key === 'affectation_lot') btn.className = 'btn btn-sm ' + (key === histoAffectFiltre ? 'btn-primary' : 'btn-outline-primary');
        else if (key === 'modification_affectation') btn.className = 'btn btn-sm ' + (key === histoAffectFiltre ? 'btn-warning' : 'btn-outline-warning');
        else if (key === 'annulation_affectation') btn.className = 'btn btn-sm ' + (key === histoAffectFiltre ? 'btn-danger' : 'btn-outline-danger');
        btn.style.fontSize = '11px';
    });
    afficherHistoriqueAffectations();
}

function formatLotsBadges(val, colorClass) {
    if (!val) return '<span style="color:#94a3b8;">—</span>';
    const parts = String(val).split(',').map(s => s.trim()).filter(Boolean);
    if (parts.length === 0) return '<span style="color:#94a3b8;">—</span>';
    const styles = {
        rouge: 'background:#fee2e2;color:#991b1b;padding:3px 10px;border-radius:6px;font-weight:700;font-size:11px;text-decoration:line-through;border:1px solid #fca5a5;',
        vert:  'background:#dcfce7;color:#166534;padding:3px 10px;border-radius:6px;font-weight:700;font-size:11px;border:1px solid #86efac;',
    };
    return parts.map(n => `<span style="${styles[colorClass]}">${colorClass === 'vert' ? '✅ ' : ''}Lot ${n}</span>`).join(' ');
}

function afficherHistoriqueAffectations() {
    const container = document.getElementById('histoAffectContent');
    let filtered = histoAffectData;
    if (histoAffectFiltre !== 'tous') {
        filtered = histoAffectData.filter(h => h.type_action === histoAffectFiltre);
    }

    if (!filtered.length) {
        container.innerHTML = `
            <div style="text-align:center;color:#94a3b8;padding:30px;">
                <div style="font-size:40px;">📭</div>
                <div style="font-weight:700;margin-top:10px;">Aucun historique trouvé</div>
            </div>`;
        return;
    }

    const icones = {'affectation_lot':'📦','modification_affectation':'✏️','annulation_affectation':'↩️'};
    const couleurs = {'affectation_lot':'#0d6efd','modification_affectation':'#f59e0b','annulation_affectation':'#dc2626'};

    container.innerHTML = filtered.map(h => {
        const icone = icones[h.type_action] || '📌';
        const couleur = couleurs[h.type_action] || '#64748b';

        let benefNom = null;
        if (h.avant && h.avant.beneficiaire_nom) benefNom = h.avant.beneficiaire_nom;
        if (h.apres && h.apres.beneficiaire_nom) benefNom = h.apres.beneficiaire_nom;
        if (h.resume) {
            const m1 = h.resume.match(/Bénéficiaire\s*:\s*«\s*([^»]+)\s*»/);
            if (m1) benefNom = m1[1];
            const m2 = h.resume.match(/bénéficiaire\s*«\s*([^»]+)\s*»/);
            if (m2) benefNom = m2[1];
        }

        let detailsHtml = '';

        if (h.type_action === 'modification_affectation' && h.avant && h.apres) {
            const depart = h.apres?.point_depart || {lot:h.avant.lot_num,bloc:h.avant.bloc,date:h.avant.date,notes:h.avant.notes};
            const arrivee = h.apres?.point_arrivee || {lot:h.apres.lot_num,bloc:h.apres.bloc,date:h.apres.date,notes:h.apres.notes};
            const dateAvantFmt = depart.date ? new Date(depart.date).toLocaleDateString('fr-FR') : '—';
            const dateApresFmt = arrivee.date ? new Date(arrivee.date).toLocaleDateString('fr-FR') : '—';

            detailsHtml = `
                <details style="margin-top:8px;font-size:11px;" open>
                    <summary style="cursor:pointer;color:#f59e0b;font-weight:700;padding:4px 0;list-style:none;">
                        🔍 <strong>Détail des changements</strong>
                    </summary>
                    <div style="background:white;border-radius:8px;padding:12px;margin-top:8px;border:1px solid #e2e8f0;">
                        <div style="background:#fef2f2;border-left:3px solid #dc2626;padding:8px 12px;border-radius:6px;margin-bottom:10px;">
                            <div style="font-size:10px;font-weight:700;color:#991b1b;text-transform:uppercase;margin-bottom:6px;">📍 Point de départ</div>
                            <div style="display:flex;flex-wrap:wrap;gap:4px;align-items:center;margin-bottom:4px;">
                                ${formatLotsBadges(depart.lot, 'rouge')}
                            </div>
                            <div style="font-size:11px;color:#7f1d1d;">📅 ${dateAvantFmt}</div>
                        </div>
                        <div style="background:#f8fafc;border-radius:6px;padding:8px 12px;margin-bottom:10px;text-align:center;">
                            <span style="color:#94a3b8;font-weight:900;font-size:20px;">⬇</span>
                        </div>
                        <div style="background:#f0fdf4;border-left:3px solid #16a34a;padding:8px 12px;border-radius:6px;">
                            <div style="font-size:10px;font-weight:700;color:#166534;text-transform:uppercase;margin-bottom:6px;">🎯 Point d'arrivée</div>
                            <div style="display:flex;flex-wrap:wrap;gap:4px;align-items:center;margin-bottom:4px;">
                                ${formatLotsBadges(arrivee.lot, 'vert')}
                            </div>
                            <div style="font-size:11px;color:#14532d;">📅 ${dateApresFmt}</div>
                        </div>
                    </div>
                </details>
            `;
        }

        if (h.type_action === 'affectation_lot' && h.apres) {
            let lotsData = h.apres.lots || [];
            let dateAff = h.apres.date_affectation || null;
            detailsHtml = `
                <details style="margin-top:8px;font-size:11px;" open>
                    <summary style="cursor:pointer;color:#0d6efd;font-weight:700;padding:4px 0;list-style:none;">
                        🔍 <strong>Lots attribués</strong>
                    </summary>
                    <div style="background:white;border-radius:8px;padding:12px;margin-top:8px;border:1px solid #e2e8f0;">
                        <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:8px;">
                            ${lotsData.map(l => `
                                <span style="background:#dcfce7;color:#16a34a;padding:4px 12px;border-radius:6px;font-size:11px;font-weight:700;border:1px solid #86efac;">
                                    ✅ Lot ${l.numero ?? '?'}
                                    ${l.bloc ? '(Bloc ' + l.bloc + ')' : ''}
                                </span>
                            `).join('')}
                        </div>
                        ${dateAff ? `<div style="font-size:10px;color:#64748b;padding-top:6px;border-top:1px dashed #e2e8f0;">📅 Date : <strong>${new Date(dateAff).toLocaleDateString('fr-FR')}</strong></div>` : ''}
                    </div>
                </details>
            `;
        }

        return `
            <div style="background:white;border-left:4px solid ${couleur};border-radius:8px;padding:12px 14px;margin-bottom:10px;box-shadow:0 1px 4px rgba(0,0,0,0.06);">
                ${benefNom ? `<div style="display:inline-flex;align-items:center;gap:4px;background:#faf5ff;color:#7c3aed;padding:3px 12px;border-radius:12px;font-size:11px;font-weight:700;margin-bottom:6px;">👤 ${benefNom}</div>` : ''}
                <div style="font-size:13px;color:#1e3a5f;font-weight:700;">${icone} ${h.resume}</div>
                <div style="font-size:10px;color:#94a3b8;margin-top:4px;">📅 ${h.date}${h.user ? ' · 👤 ' + h.user : ''}</div>
                ${detailsHtml}
            </div>
        `;
    }).join('');
}

// ════════════════════════════════════════════════════════════════
// AFFECTATION RAPIDE DANS CARTE BÉNÉFICIAIRE
// ════════════════════════════════════════════════════════════════
function benefAffChargerSites(gsId, benefId) {
    const sel = document.getElementById('benef-aff-site-' + benefId);
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-tf-' + benefId).innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-bloc-' + benefId).innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-lots-container-' + benefId).innerHTML = '';
    if (!gsId) return;
    fetch(`/admin/affectations/api/sites/${gsId}`)
        .then(r => r.json()).then(sites => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                sites.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        });
}

function benefAffChargerTfs(siteId, benefId) {
    const sel = document.getElementById('benef-aff-tf-' + benefId);
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-bloc-' + benefId).innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-lots-container-' + benefId).innerHTML = '';
    if (!siteId) return;
    fetch(`/admin/affectations/api/tfs/${siteId}`)
        .then(r => r.json()).then(tfs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                tfs.map(t => `<option value="${t.id}">${t.title}</option>`).join('');
        });
}

function benefAffChargerBlocs(tfId, benefId) {
    const sel = document.getElementById('benef-aff-bloc-' + benefId);
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-lots-container-' + benefId).innerHTML = '';
    if (!tfId) return;
    fetch(`/admin/affectations/api/blocs/${tfId}`)
        .then(r => r.json()).then(blocs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                blocs.map(b => `<option value="${b.id}">Bloc ${b.code}</option>`).join('');
        });
}

function benefAffChargerLots(blocId, benefId) {
    const container = document.getElementById('benef-aff-lots-container-' + benefId);
    const btn       = document.getElementById('benef-aff-btn-' + benefId);
    const compteur  = document.getElementById('benef-aff-compteur-' + benefId);
    container.innerHTML = '';
    btn.disabled = true;
    compteur.textContent = '0 lot(s) sélectionné(s)';
    if (!blocId) return;

    fetch(`/admin/affectations/api/lots/${blocId}`)
        .then(r => r.json()).then(lots => {
            if (!lots.length) {
                container.innerHTML = '<div style="color:#94a3b8;font-size:11px;padding:4px 0;">Aucun lot disponible.</div>';
                return;
            }
            container.innerHTML = `
                <div style="font-size:10px;font-weight:700;color:#64748b;margin-bottom:6px;">Lots disponibles :</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    ${lots.map(l => `
                        <label style="display:inline-flex;align-items:center;gap:4px;background:white;border:2px solid #e2e8f0;border-radius:6px;padding:3px 8px;cursor:pointer;font-size:10px;font-weight:600;"
                               id="benef-label-lot-${benefId}-${l.id}">
                            <input type="checkbox" value="${l.id}" class="benef-aff-lot-cb-${benefId}"
                                   style="width:13px;height:13px;"
                                   onchange="benefMajBoutonAff(${benefId}); benefToggleLotLabel(${benefId}, ${l.id})">
                            Lot ${l.numero}
                            ${l.superficie ? '<span style="color:#64748b;font-size:9px;">' + parseInt(l.superficie).toLocaleString('fr-FR') + ' m²</span>' : ''}
                        </label>
                    `).join('')}
                </div>
            `;
        });
}

function benefToggleLotLabel(benefId, lotId) {
    const label = document.getElementById('benef-label-lot-' + benefId + '-' + lotId);
    const cb = label.querySelector('input[type=checkbox]');
    if (cb.checked) {
        label.style.background = '#dcfce7';
        label.style.borderColor = '#16a34a';
        label.style.color = '#16a34a';
    } else {
        label.style.background = 'white';
        label.style.borderColor = '#e2e8f0';
        label.style.color = '';
    }
}

function benefMajBoutonAff(benefId) {
    const cbs = document.querySelectorAll('.benef-aff-lot-cb-' + benefId + ':checked');
    const compteur = document.getElementById('benef-aff-compteur-' + benefId);
    document.getElementById('benef-aff-btn-' + benefId).disabled = cbs.length === 0;
    compteur.textContent = cbs.length + ' lot(s) sélectionné(s)';
}

function validerBenefAffectation(benefId) {
    const cbs = document.querySelectorAll('.benef-aff-lot-cb-' + benefId + ':checked');
    const lotIds = Array.from(cbs).map(cb => cb.value);
    const date = document.getElementById('benef-aff-date-' + benefId).value;
    const notes = document.getElementById('benef-aff-notes-' + benefId).value;

    if (!lotIds.length) { alert('Sélectionnez au moins un lot.'); return; }
    if (!date) { alert('Date obligatoire.'); return; }

    if (window.EdenLoader) window.EdenLoader.show();
    fetch(`/admin/beneficiaires/${benefId}/affecter-lots`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ lot_ids: lotIds, date_affectation: date, notes }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) { alert(data.message); location.reload(); }
        else alert(data.message || 'Erreur');
    })
    .catch(e => { if (window.EdenLoader) window.EdenLoader.hide(); alert('Erreur réseau : ' + e.message); });
}

function annulerGroupeBenefAffectation(ids, btn) {
    if (!confirm('Supprimer toutes ces affectations ? Les lots redeviendront disponibles.')) return;
    const idArray = ids.split(',');
    const promises = idArray.map(id =>
        fetch(`/admin/affectations/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type':'application/json' },
        }).then(r => r.json())
    );
    Promise.all(promises)
        .then(results => {
            if (results.every(r => r.success)) {
                showToast('✅ Affectations annulées');
                setTimeout(() => location.reload(), 800);
            } else alert('Erreur lors de la suppression');
        })
        .catch(e => alert('Erreur réseau : ' + e.message));
}

// ════════════════════════════════════════════════════════════════
// BÉNÉFICIAIRES CRUD
// ════════════════════════════════════════════════════════════════
function ouvrirModalBenef(dossierId, benef) {
    benefEditId = benef?.id ?? null;
    benefLotsSelectionnes = new Set();

    document.getElementById('benefId').value = benef?.id ?? '';
    document.getElementById('benefDossierId').value = dossierId;
    document.getElementById('benefNom').value = benef?.nom ?? '';
    document.getElementById('benefTelephone').value = benef?.telephone ?? '';
    document.getElementById('benefNotes').value = benef?.notes ?? '';
    document.getElementById('benefTitre').textContent = benef ? '✏️ Modifier un bénéficiaire' : '👥 Ajouter un bénéficiaire';

    document.getElementById('benefCniOblig').style.display = benef ? 'none' : 'inline';
    document.getElementById('benefCni').required = !benef;

    const clientWrapper = document.getElementById('benefClientWrapper');
    if (benef) clientWrapper.style.display = 'none';
    else {
        clientWrapper.style.display = 'block';
        document.getElementById('benefClientId').value = '';
        document.getElementById('benefClientInfo').textContent = '';
    }

    const cniActuelle = document.getElementById('benefCniActuelle');
    cniActuelle.innerHTML = benef?.cni_url
        ? `📎 <a href="${benef.cni_url}" target="_blank">Voir CNI actuelle</a>`
        : '';

    const lotsSection = document.getElementById('benefLotsSection');
    if (benef) {
        lotsSection.style.display = 'none';
    } else {
        lotsSection.style.display = 'block';
        document.getElementById('benefLotGs').value = '';
        document.getElementById('benefLotSite').innerHTML = '<option value="">-- Choisir --</option>';
        document.getElementById('benefLotTf').innerHTML = '<option value="">-- Choisir --</option>';
        document.getElementById('benefLotBloc').innerHTML = '<option value="">-- Choisir --</option>';
        document.getElementById('benefLotDate').value = new Date().toISOString().split('T')[0];
        document.getElementById('benefLotListe').innerHTML =
            '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">' +
            'ℹ️ Sélectionnez un Bloc pour voir les lots disponibles</div>';
        document.getElementById('benefLotResume').style.display = 'none';
        benefLotsSelectionnes.clear();
    }

    document.getElementById('benefOverlay').style.display = 'block';
    document.getElementById('benefModal').style.display = 'block';
}

function fermerModalBenef() {
    document.getElementById('benefOverlay').style.display = 'none';
    document.getElementById('benefModal').style.display = 'none';
    benefEditId = null;
    benefLotsSelectionnes.clear();
}

function prefillBenefFromClient(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt.value) {
        document.getElementById('benefClientInfo').textContent = '';
        return;
    }
    document.getElementById('benefNom').value = opt.dataset.nom || '';
    document.getElementById('benefTelephone').value = opt.dataset.phone || '';
    const sexe = opt.dataset.sexe;
    document.getElementById('benefClientInfo').textContent =
        sexe ? '👤 Sexe : ' + (sexe === 'masculin' ? 'Masculin' : 'Féminin') : '⚠️ Sexe non renseigné';
}

// SÉLECTION LOTS DANS MODAL BÉNÉFICIAIRE
function benefLotChargerSites(gsId) {
    const sel = document.getElementById('benefLotSite');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benefLotTf').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benefLotBloc').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benefLotListe').innerHTML =
        '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">' +
        'ℹ️ Sélectionnez un Bloc pour voir les lots disponibles</div>';
    if (!gsId) return;
    fetch(`/admin/affectations/api/sites/${gsId}`)
        .then(r => r.json())
        .then(sites => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                sites.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        });
}

function benefLotChargerTfs(siteId) {
    const sel = document.getElementById('benefLotTf');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benefLotBloc').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benefLotListe').innerHTML =
        '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">' +
        'ℹ️ Sélectionnez un Bloc pour voir les lots disponibles</div>';
    if (!siteId) return;
    fetch(`/admin/affectations/api/tfs/${siteId}`)
        .then(r => r.json())
        .then(tfs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                tfs.map(t => `<option value="${t.id}">${t.title}</option>`).join('');
        });
}

function benefLotChargerBlocs(tfId) {
    const sel = document.getElementById('benefLotBloc');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benefLotListe').innerHTML =
        '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">' +
        'ℹ️ Sélectionnez un Bloc pour voir les lots disponibles</div>';
    if (!tfId) return;
    fetch(`/admin/affectations/api/blocs/${tfId}`)
        .then(r => r.json())
        .then(blocs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                blocs.map(b => `<option value="${b.id}">Bloc ${b.code}</option>`).join('');
        });
}

function benefLotChargerLots(blocId) {
    const container = document.getElementById('benefLotListe');
    if (!blocId) {
        container.innerHTML = '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">' +
            'ℹ️ Sélectionnez un Bloc pour voir les lots disponibles</div>';
        return;
    }
    container.innerHTML = '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">⏳ Chargement...</div>';

    fetch(`/admin/affectations/api/lots/${blocId}`)
        .then(r => r.json())
        .then(lots => {
            if (!lots.length) {
                container.innerHTML = '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">Aucun lot disponible dans ce bloc</div>';
                return;
            }
            container.innerHTML = `
                <div style="font-size:10px;font-weight:700;color:#64748b;margin-bottom:6px;">Cochez les lots à attribuer :</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    ${lots.map(l => `
                        <label style="display:inline-flex;align-items:center;gap:4px;background:white;border:2px solid #e2e8f0;border-radius:6px;padding:4px 10px;cursor:pointer;font-size:11px;font-weight:600;transition:0.15s;"
                               id="benef-lot-label-${l.id}">
                            <input type="checkbox" value="${l.id}" class="benef-lot-cb"
                                   data-superficie="${l.superficie ?? 0}"
                                   data-numero="${l.numero}"
                                   style="width:14px;height:14px;"
                                   onchange="benefToggleLot(${l.id}, this)">
                            Lot ${l.numero}
                            ${l.superficie ? '<span style="color:#64748b;font-size:9px;">' + parseInt(l.superficie).toLocaleString('fr-FR') + ' m²</span>' : ''}
                        </label>
                    `).join('')}
                </div>
            `;
        })
        .catch(e => {
            container.innerHTML = `<div style="color:#dc2626;font-size:11px;text-align:center;padding:10px;">❌ Erreur : ${e.message}</div>`;
        });
}

function benefToggleLot(lotId, checkbox) {
    const label = document.getElementById('benef-lot-label-' + lotId);
    if (checkbox.checked) {
        benefLotsSelectionnes.add(lotId);
        label.style.background = '#dcfce7';
        label.style.borderColor = '#16a34a';
        label.style.color = '#16a34a';
    } else {
        benefLotsSelectionnes.delete(lotId);
        label.style.background = 'white';
        label.style.borderColor = '#e2e8f0';
        label.style.color = '';
    }
    benefMajResume();
}

function benefMajResume() {
    const resume = document.getElementById('benefLotResume');
    if (benefLotsSelectionnes.size === 0) {
        resume.style.display = 'none';
        return;
    }
    let totalSuperficie = 0;
    const numeros = [];
    document.querySelectorAll('.benef-lot-cb:checked').forEach(cb => {
        totalSuperficie += parseFloat(cb.dataset.superficie) || 0;
        numeros.push(cb.dataset.numero);
    });
    resume.style.display = 'block';
    resume.innerHTML = `
        <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:6px;">
            <span>✅ <strong>${benefLotsSelectionnes.size} lot(s) sélectionné(s)</strong> : ${numeros.join(', ')}</span>
            <span style="background:#166534;color:white;padding:3px 10px;border-radius:6px;font-weight:700;">
                📐 Superficie totale : ${totalSuperficie.toLocaleString('fr-FR')} m²
            </span>
        </div>
    `;
}

function sauvegarderBenef() {
    const dossierId = document.getElementById('benefDossierId').value;
    const nom = document.getElementById('benefNom').value.trim();
    const cniFile = document.getElementById('benefCni').files[0];
    const clientId = document.getElementById('benefClientId')?.value || '';

    if (!nom) { showToast('⚠️ Le nom est obligatoire'); return; }

    const formData = new FormData();
    formData.append('nom', nom);
    formData.append('telephone', document.getElementById('benefTelephone').value);
    formData.append('notes', document.getElementById('benefNotes').value);
    if (cniFile) formData.append('cni', cniFile);
    if (clientId) formData.append('client_id', clientId);

    if (!benefEditId) {
        if (!cniFile) { showToast('⚠️ La CNI est obligatoire'); return; }
        if (benefLotsSelectionnes.size === 0) { showToast('⚠️ Sélectionnez au moins un lot'); return; }
        benefLotsSelectionnes.forEach(id => { formData.append('lot_ids[]', id); });
        formData.append('date_affectation', document.getElementById('benefLotDate').value);
    }

    const url = benefEditId
        ? `/admin/beneficiaires/${benefEditId}`
        : `/admin/dossiers/${dossierId}/beneficiaires`;

    if (benefEditId) formData.append('_method', 'PUT');

    if (window.EdenLoader) window.EdenLoader.show();

    fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            fermerModalBenef();
            showToast(data.message);
            setTimeout(() => location.reload(), 800);
        } else {
            let msg = data.message || 'Erreur';
            if (data.errors) msg = Object.values(data.errors).flat().join('\n');
            showToast('❌ ' + msg);
        }
    })
    .catch(err => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau');
    });
}

function supprimerBenef(benefId, dossierId) {
    if (!confirm('Supprimer ce bénéficiaire ? Les lots associés seront libérés.')) return;
    if (window.EdenLoader) window.EdenLoader.show();
    fetch(`/admin/beneficiaires/${benefId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast(data.message);
            setTimeout(() => location.reload(), 800);
        } else showToast(data.message || 'Erreur');
    })
    .catch(err => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau');
    });
}

// ════════════════════════════════════════════════════════════════
// UTILITAIRES
// ════════════════════════════════════════════════════════════════
function demanderReferenceCreate(dossierId) {
    const reference = prompt('🔑 Entrez votre numéro de référence :');
    if (reference === null) return false;
    const ref = reference.trim();
    if (ref === '') { alert('⚠️ La référence ne peut pas être vide.'); return false; }
    fetch('/admin/verifier-reference', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ reference: ref })
    })
    .then(r => r.json())
    .then(data => {
        if (data.existe) {
            window.location.href = '/admin/bons/' + dossierId + '/creer?reference=' + encodeURIComponent(ref);
        } else alert('❌ La référence n\'existe pas.');
    })
    .catch(e => alert('⚠️ Erreur de vérification.'));
}

function majPrix(dossierId) {
    const superficie = document.getElementById('prix-superficie-' + dossierId)?.value ?? '';
    const technique = document.getElementById('prix-technique-' + dossierId)?.value ?? '';
    const morcellement = document.getElementById('prix-morcellement-' + dossierId)?.value ?? '';
    const logistique = document.getElementById('prix-logistique-' + dossierId)?.value;

    if (window.EdenLoader) window.EdenLoader.show();
    fetch(`/admin/dossiers/${dossierId}/maj-prix`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            prix_superficie: superficie !== '' ? parseFloat(superficie) : null,
            prix_technique: technique !== '' ? parseFloat(technique) : null,
            prix_morcellement: morcellement !== '' ? parseFloat(morcellement) : null,
            prix_logistique: logistique,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) location.reload();
        else alert(data.message || 'Erreur');
    })
    .catch(e => { if (window.EdenLoader) window.EdenLoader.hide(); alert('Erreur réseau'); });
}

// ════════════════════════════════════════════════════════════════
// INITIALISATION AU CHARGEMENT
// ════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    // S'assurer que le premier dossier est visible
    const panels = document.querySelectorAll('.dossier-panel');
    if (panels.length > 0) {
        panels.forEach((p, i) => {
            p.style.display = i === 0 ? 'block' : 'none';
        });
    }

    // S'assurer que le premier onglet dossier est actif
    const tabs = document.querySelectorAll('.dossier-tab');
    if (tabs.length > 0) {
        tabs.forEach((t, i) => {
            if (i === 0) t.classList.add('active');
            else t.classList.remove('active');
        });
    }
});
</script>
@endsection