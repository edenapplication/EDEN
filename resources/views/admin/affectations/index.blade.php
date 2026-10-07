@extends('admin.affectations.layout')
@section('content')

<style>
.stat-card {
    background:white; border-radius:14px; padding:20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06); text-align:center;
    transition:0.2s;
}
.stat-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,0.1); }
.stat-card .v { font-size:28px; font-weight:900; margin-bottom:4px; }
.stat-card .l { font-size:10px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px; }
.action-card {
    background:white; border-radius:14px; padding:22px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    border-left:4px solid #1d4ed8;
    display:flex; justify-content:space-between; align-items:center;
    transition:0.2s;
}
.action-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.1); transform:translateY(-2px); }
.action-card .ac-titre { font-weight:700; font-size:15px; color:#1e3a5f; }
.action-card .ac-desc  { font-size:12px; color:#64748b; margin-top:3px; }

/* ═══════════════════════════════════════════════════════════════ */
/* 🌳 ARBRE GRAND SITE → SITES → TFS                                */
/* ═══════════════════════════════════════════════════════════════ */
.gs-tree {
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    overflow: hidden;
    border: 1px solid #e2e8f0;
}

.gs-header {
    background: linear-gradient(135deg, #1e3a5f, #1d4ed8);
    color: white;
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    transition: 0.2s;
    user-select: none;
}
.gs-header:hover { filter: brightness(1.08); }
.gs-header .gs-name {
    font-weight: 800;
    font-size: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.gs-header .gs-count {
    display: inline-flex;
    gap: 8px;
    font-size: 11px;
    font-weight: 700;
}
.gs-header .gs-badge {
    background: rgba(255,255,255,0.2);
    padding: 3px 10px;
    border-radius: 12px;
    backdrop-filter: blur(4px);
}
.gs-header .gs-arrow {
    font-size: 14px;
    transition: transform 0.25s;
}
.gs-header.collapsed .gs-arrow { transform: rotate(-90deg); }

.gs-body {
    padding: 12px 16px 16px 16px;
    background: #f8fafc;
    display: block;
}
.gs-body.collapsed { display: none; }

.site-block {
    background: white;
    border-radius: 10px;
    margin-bottom: 10px;
    overflow: hidden;
    border-left: 4px solid #16a34a;
    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
}
.site-block:last-child { margin-bottom: 0; }

.site-header {
    padding: 10px 14px;
    background: #f0fdf4;
    display: flex;
    justify-content: space-between;
    align-items: center;
    cursor: pointer;
    border-bottom: 1px solid #dcfce7;
}
.site-header:hover { background: #dcfce7; }
.site-header .site-name {
    font-weight: 700;
    font-size: 13px;
    color: #166534;
    display: flex;
    align-items: center;
    gap: 8px;
}
.site-header .site-count {
    font-size: 10px;
    font-weight: 700;
    background: #16a34a;
    color: white;
    padding: 2px 9px;
    border-radius: 10px;
}
.site-header .site-arrow {
    font-size: 12px;
    transition: transform 0.25s;
    color: #16a34a;
}
.site-header.collapsed .site-arrow { transform: rotate(-90deg); }

.tf-list {
    padding: 10px 14px;
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    background: white;
}
.tf-list.collapsed { display: none; }

.tf-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    border-radius: 8px;
    background: #fef3c7;
    border: 1px solid #fcd34d;
    font-size: 11px;
    font-weight: 700;
    color: #92400e;
    text-decoration: none;
    transition: 0.2s;
}
.tf-chip:hover {
    background: #f59e0b;
    color: white;
    border-color: #d97706;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(217,119,6,0.3);
}

.tf-empty {
    font-size: 11px;
    color: #94a3b8;
    font-style: italic;
    padding: 6px 0;
}
</style>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- EN-TÊTE AVEC 3 BOUTONS INDÉPENDANTS                              --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">🗺️ Blocs & Lots — Affectations</h2>
        <div style="font-size:13px;color:#64748b;">
            Gérez les blocs, les lots et leurs affectations aux dossiers clients
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">

        <a href="{{ route('admin.creation.grands-sites') }}"
           class="btn btn-sm"
           style="background:linear-gradient(135deg,#1e3a5f,#1d4ed8);
                  color:white;border:none;font-weight:800;
                  padding:10px 18px;border-radius:10px;
                  box-shadow:0 4px 14px rgba(29,78,216,0.3);">
            🏢 Créer Grands Sites
        </a>

        <a href="{{ route('admin.creation.sites') }}"
           class="btn btn-sm"
           style="background:linear-gradient(135deg,#16a34a,#15803d);
                  color:white;border:none;font-weight:800;
                  padding:10px 18px;border-radius:10px;
                  box-shadow:0 4px 14px rgba(22,163,74,0.3);">
            📍 Créer Sites
        </a>

        <a href="{{ route('admin.creation.tfs') }}"
           class="btn btn-sm"
           style="background:linear-gradient(135deg,#d97706,#f59e0b);
                  color:white;border:none;font-weight:800;
                  padding:10px 18px;border-radius:10px;
                  box-shadow:0 4px 14px rgba(217,119,6,0.3);">
            📄 Créer TFs
        </a>

    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- KPIs                                                              --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #1e3a5f;">
            <div class="v" style="color:#1e3a5f;">{{ $stats['blocs'] }}</div>
            <div class="l">Blocs enregistrés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #1d4ed8;">
            <div class="v" style="color:#1d4ed8;">{{ $stats['lots'] }}</div>
            <div class="l">Lots enregistrés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #16a34a;">
            <div class="v" style="color:#16a34a;">{{ $stats['disponibles'] }}</div>
            <div class="l">Lots disponibles</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #dc2626;">
            <div class="v" style="color:#dc2626;">{{ $stats['affectes'] }}</div>
            <div class="l">Lots affectés</div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- BARRE DE PROGRESSION DISPONIBILITÉ                                --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@if($stats['lots'] > 0)
@php
    $pctDispo = round(($stats['disponibles'] / $stats['lots']) * 100);
    $pctAff   = 100 - $pctDispo;
@endphp
<div style="background:white;border-radius:14px;padding:18px 22px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:24px;">
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-bottom:8px;">
        <span>✅ Disponibles : <strong style="color:#16a34a;">{{ $pctDispo }}%</strong></span>
        <span>🔴 Affectés : <strong style="color:#dc2626;">{{ $pctAff }}%</strong></span>
    </div>
    <div style="height:12px;background:#f1f5f9;border-radius:6px;overflow:hidden;">
        <div style="display:flex;height:100%;">
            <div style="width:{{ $pctDispo }}%;background:#16a34a;border-radius:6px 0 0 6px;"></div>
            <div style="width:{{ $pctAff }}%;background:#dc2626;border-radius:0 6px 6px 0;"></div>
        </div>
    </div>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ACTIONS PRINCIPALES (3 cartes)                                    --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;">
    Actions
</div>

<div class="row g-3 mb-4">

    {{-- 🗂️ Gestion des Blocs --}}
    <div class="col-md-4">
        <div class="action-card" style="border-left-color:#1e3a5f;">
            <div>
                <div class="ac-titre">🗂️ Gestion des Blocs</div>
                <div class="ac-desc">
                    Créer, modifier et supprimer des blocs.
                    Chaque bloc est lié à un Grand Site, un Site et un TF.
                </div>
                <div style="margin-top:10px;font-size:12px;color:#64748b;">
                    <strong style="color:#1e3a5f;">{{ $stats['blocs'] }}</strong> bloc(s) enregistré(s)
                </div>
            </div>
            <a href="{{ route('affectations.blocs') }}"
               class="btn btn-primary" style="white-space:nowrap;margin-left:16px;">
                Gérer →
            </a>
        </div>
    </div>

    {{-- 📦 Gestion des Lots --}}
    <div class="col-md-4">
        <div class="action-card" style="border-left-color:#1d4ed8;">
            <div>
                <div class="ac-titre">📦 Gestion des Lots</div>
                <div class="ac-desc">
                    Créer, modifier et supprimer des lots dans les blocs.
                    Visualisez la disponibilité de chaque lot.
                </div>
                <div style="margin-top:10px;font-size:12px;color:#64748b;">
                    <strong style="color:#16a34a;">{{ $stats['disponibles'] }}</strong> disponible(s)
                    sur <strong style="color:#1d4ed8;">{{ $stats['lots'] }}</strong> total
                </div>
            </div>
            <a href="{{ route('affectations.lots') }}"
               class="btn btn-primary" style="white-space:nowrap;margin-left:16px;">
                Gérer →
            </a>
        </div>
    </div>

    {{-- ⚡ Création rapide (3 boutons) --}}
    <div class="col-md-4">
        <div class="action-card" style="border-left-color:#7c3aed;flex-direction:column;align-items:flex-start;">
            <div style="width:100%;">
                <div class="ac-titre">⚡ Création rapide</div>
                <div class="ac-desc">
                    Créez <strong>plusieurs à la fois</strong>, indépendamment.
                </div>
            </div>

            <div style="display:flex;flex-direction:column;gap:6px;width:100%;margin-top:14px;">
                <a href="{{ route('admin.creation.grands-sites') }}"
                   class="btn btn-sm"
                   style="background:linear-gradient(135deg,#1e3a5f,#1d4ed8);
                          color:white;border:none;font-weight:800;
                          border-radius:8px;font-size:12px;padding:8px 12px;">
                    🏢 Créer Grands Sites
                </a>
                <a href="{{ route('admin.creation.sites') }}"
                   class="btn btn-sm"
                   style="background:linear-gradient(135deg,#16a34a,#15803d);
                          color:white;border:none;font-weight:800;
                          border-radius:8px;font-size:12px;padding:8px 12px;">
                    📍 Créer Sites
                </a>
                <a href="{{ route('admin.creation.tfs') }}"
                   class="btn btn-sm"
                   style="background:linear-gradient(135deg,#d97706,#f59e0b);
                          color:white;border:none;font-weight:800;
                          border-radius:8px;font-size:12px;padding:8px 12px;">
                    📄 Créer TFs
                </a>
            </div>
        </div>
    </div>

</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- 🌳 ARBORESCENCE GRAND SITE → SITES → TFS                          --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@if($grandSites->count() > 0)
<div style="margin-top:24px;">
    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:12px;">
        🌳 Hiérarchie complète — Grand Site / Site / TF
    </div>

    @foreach($grandSites as $gs)
    @php
        $sites = $gs->sites ?? collect();
        $nbSites = $sites->count();
        $nbTfs   = $sites->sum(fn($s) => ($s->tfs ?? collect())->count());
    @endphp

    <div class="gs-tree" style="margin-bottom:14px;">
        {{-- En-tête Grand Site --}}
        <div class="gs-header" onclick="toggleGs({{ $gs->id }})">
            <div class="gs-name">
                <span class="gs-arrow" id="arrow-gs-{{ $gs->id }}">▼</span>
                🏢 {{ $gs->nom }}
            </div>
            <div class="gs-count">
                <span class="gs-badge">📍 {{ $nbSites }} site(s)</span>
                <span class="gs-badge">📄 {{ $nbTfs }} TF(s)</span>
            </div>
        </div>

        {{-- Corps : liste des sites --}}
        <div class="gs-body" id="body-gs-{{ $gs->id }}">

            @forelse($sites as $site)
            @php
                $tfs = $site->tfs ?? collect();
                $nbTfsSite = $tfs->count();
            @endphp

            <div class="site-block">
                {{-- En-tête Site --}}
                <div class="site-header" onclick="toggleSite({{ $site->id }})">
                    <div class="site-name">
                        <span class="site-arrow" id="arrow-site-{{ $site->id }}">▼</span>
                        📍 {{ $site->name }}
                    </div>
                    <span class="site-count">{{ $nbTfsSite }} TF(s)</span>
                </div>

                {{-- Liste des TFs du site --}}
                <div class="tf-list" id="body-site-{{ $site->id }}">
                    @forelse($tfs as $tf)
                        <a href="{{ route('affectations.blocs', ['grand_site_id' => $gs->id, 'tf_id' => $tf->id]) }}"
                           class="tf-chip"
                           title="Voir les blocs de ce TF">
                            📄 {{ $tf->title }}
                        </a>
                    @empty
                        <span class="tf-empty">Aucun TF enregistré pour ce site</span>
                    @endforelse
                </div>
            </div>
            @empty
                <div style="padding:14px;font-size:12px;color:#94a3b8;font-style:italic;text-align:center;">
                    Aucun site enregistré pour ce grand site
                </div>
            @endforelse

        </div>
    </div>
    @endforeach
</div>
@endif

@endsection

@section('scripts')
<script>
// ════════════════════════════════════════════════════════════════
// 🌳 TOGGLE GRAND SITE
// ════════════════════════════════════════════════════════════════
function toggleGs(id) {
    const body  = document.getElementById('body-gs-' + id);
    const arrow = document.getElementById('arrow-gs-' + id);
    const header = arrow.closest('.gs-header');

    if (!body) return;

    const isCollapsed = body.classList.toggle('collapsed');
    header.classList.toggle('collapsed', isCollapsed);
}

// ════════════════════════════════════════════════════════════════
// 🌳 TOGGLE SITE
// ════════════════════════════════════════════════════════════════
function toggleSite(id) {
    const body  = document.getElementById('body-site-' + id);
    const arrow = document.getElementById('arrow-site-' + id);
    const header = arrow.closest('.site-header');

    if (!body) return;

    const isCollapsed = body.classList.toggle('collapsed');
    header.classList.toggle('collapsed', isCollapsed);
}

// ════════════════════════════════════════════════════════════════
// 🔄 Optionnel : tout déplier / tout replier
// ════════════════════════════════════════════════════════════════
function toutDeplier() {
    document.querySelectorAll('.gs-body, .tf-list').forEach(el => el.classList.remove('collapsed'));
    document.querySelectorAll('.gs-header, .site-header').forEach(el => el.classList.remove('collapsed'));
}

function toutReplier() {
    document.querySelectorAll('.gs-body, .tf-list').forEach(el => el.classList.add('collapsed'));
    document.querySelectorAll('.gs-header, .site-header').forEach(el => el.classList.add('collapsed'));
}
</script>
@endsection