@extends('admin.affectations.layout')
@section('content')

<style>
.aff-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin-bottom:20px; }
.aff-stat {
    background:white; border-radius:12px; padding:16px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    border-left:4px solid #1d4ed8;
    display:flex; align-items:center; gap:14px;
}
.aff-stat .ico { width:44px; height:44px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.aff-stat .lbl { font-size:10px; color:#64748b; text-transform:uppercase; font-weight:700; }
.aff-stat .val { font-size:20px; font-weight:800; color:#1e3a5f; line-height:1.2; }
.aff-stat .sub { font-size:10px;color:#94a3b8;font-weight:500;margin-top:2px; }

.aff-filters { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:16px; }

.aff-table-wrap { background:white; border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05); }

.aff-table { width:100%; border-collapse:collapse; font-size:13px; }
.aff-table thead { background:linear-gradient(135deg,#1e3a5f,#1d4ed8); color:white; }
.aff-table thead th { padding:12px 14px; text-align:left; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; white-space:nowrap; }
.aff-table tbody tr { border-bottom:1px solid #f1f5f9; transition:0.15s; }
.aff-table tbody tr:hover { background:#f8fafc; }
.aff-table tbody td { padding:12px 14px; vertical-align:middle; }

.aff-table tbody tr.row-attente { border-left:4px solid #f59e0b; }
.aff-table tbody tr.selected { background:#dbeafe !important; box-shadow:inset 0 0 0 2px #1d4ed8; }

.pill { display:inline-flex; align-items:center; gap:4px; padding:3px 9px; border-radius:12px; font-size:10px; font-weight:700; white-space:nowrap; }
.pill-site  { background:#dbeafe; color:#1d4ed8; }
.pill-tf    { background:#fef3c7; color:#92400e; }
.pill-bloc  { background:#fce7f3; color:#9d174d; }
.pill-lot   { background:#dcfce7; color:#166534; margin:2px 2px 2px 0; display:inline-block; }
.pill-sup   { background:#ede9fe; color:#7c3aed; }
.pill-attente { background:#fef3c7; color:#92400e; }

.pill-count { display:inline-block; background:#1d4ed8; color:white; padding:1px 7px; border-radius:10px; font-size:10px; font-weight:800; margin-left:4px; }

.aff-nom { font-weight:700; color:#1e3a5f; }
.aff-nom small { font-weight:400; color:#64748b; font-size:10px; display:block; }

.aff-actions { display:flex; gap:4px; justify-content:flex-end; flex-wrap:wrap; }
.aff-btn { width:28px; height:28px; border-radius:8px; border:none; cursor:pointer; display:inline-flex; align-items:center; justify-content:center; font-size:12px; transition:all 0.2s; }
.aff-btn.edit   { background:#fef3c7; color:#92400e; }
.aff-btn.edit:hover   { background:#f59e0b; color:white; transform:scale(1.1); }
.aff-btn.delete { background:#f1f5f9; color:#64748b; }
.aff-btn.delete:hover { background:#dc2626; color:white; transform:scale(1.1); }

.checkbox-ligne { width:18px; height:18px; cursor:pointer; accent-color:#1d4ed8; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 40px rgba(0,0,0,0.25); z-index:9999; width:720px; max-width:95%; max-height:90vh; overflow-y:auto; }

.lot-chip { display:inline-flex; align-items:center; gap:6px; padding:6px 10px; border-radius:8px; background:#f0fdf4; border:1px solid #86efac; font-size:11px; font-weight:700; color:#166534; margin:0 4px 4px 0; }
.lot-check-label { display:inline-flex; align-items:center; gap:4px; padding:5px 10px; border:2px solid #e2e8f0; border-radius:8px; cursor:pointer; font-size:11px; font-weight:600; background:white; transition:all 0.2s; }
.lot-check-label:hover { border-color:#1d4ed8; }
.lot-check-label.checked { background:#dcfce7; border-color:#16a34a; color:#166534; }
.lot-check-label input[type="checkbox"] { width:13px; height:13px; accent-color:#16a34a; }

.impl-input { border: 1px solid #e2e8f0; border-radius: 6px; transition: all 0.2s; }
.impl-input:hover { border-color: #1d4ed8; background: #eff6ff; }
.impl-input:focus { border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29,78,216,0.1); outline: none; }
.impl-input:disabled { opacity: 0.5; }

.selection-bar {
    display:none;
    background:linear-gradient(135deg,#eff6ff,#dbeafe);
    border:2px solid #1d4ed8;
    border-radius:14px;
    padding:16px 20px;
    margin-bottom:16px;
    box-shadow:0 4px 14px rgba(29,78,216,0.15);
    position:sticky; top:10px; z-index:100;
}
.selection-bar.visible { display:block; }
.selection-bar-header { display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px; }
.selection-bar-title { font-weight:800;color:#1d4ed8;font-size:14px;display:flex;align-items:center;gap:8px; }
.selection-bar-count { background:#1d4ed8;color:white;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:800; }
.selection-bar-actions { display:flex;gap:8px;flex-wrap:wrap;align-items:center; }
.date-input-multiple { padding:8px 12px;border:2px solid #93c5fd;border-radius:8px;font-size:13px;font-weight:600;background:white;transition:all 0.2s; }
.date-input-multiple:focus { outline:none; border-color:#1d4ed8; box-shadow:0 0 0 3px rgba(29,78,216,0.1); }
.btn-apply-date { background:linear-gradient(135deg,#1d4ed8,#1e40af);color:white;border:none;border-radius:8px;padding:10px 18px;font-size:13px;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s; }
.btn-apply-date:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 4px 12px rgba(29,78,216,0.4); }
.btn-apply-date:disabled { background:#cbd5e1; cursor:not-allowed; }
.btn-clear-selection { background:white;border:2px solid #e2e8f0;border-radius:8px;padding:8px 14px;font-size:12px;font-weight:700;color:#64748b;cursor:pointer;transition:all 0.2s; }
.btn-clear-selection:hover { border-color:#dc2626; color:#dc2626; }

.toast-notification { position:fixed; bottom:20px; right:20px; background:#1f2937; color:#fff; padding:12px 20px; border-radius:8px; font-size:14px; box-shadow:0 4px 12px rgba(0,0,0,0.3); z-index:99999; max-width:400px; animation:slideInToast 0.3s ease; }
.toast-notification.success { background:#16a34a; }
.toast-notification.error   { background:#dc2626; }
.toast-notification.warning { background:#f59e0b; }
@keyframes slideInToast { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }

.empty-state { text-align:center; padding:60px 20px; color:#94a3b8; }
.empty-state .ico { font-size:56px; margin-bottom:14px; }

/* ═══════════════════════════════════════════════════════════════ */
/* 📄 BARRE DE RAPPORTS + FILTRES                                   */
/* ═══════════════════════════════════════════════════════════════ */
.prog-filter-row td {
    background: #f8fafc !important;
    border-bottom: 2px solid #e2e8f0 !important;
    padding: 8px 14px !important;
}
.prog-filter-bar {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border: 2px solid #1d4ed8;
    border-radius: 10px;
    padding: 10px 14px;
    margin: 0;
}
.prog-filter-bar .label {
    font-size: 8px;
    font-weight: 800;
    color: #1e40af;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.prog-filter-bar input[type="date"] {
    font-size: 11px;
    padding: 5px 8px;
    border: 1px solid #c4b5fd;
    border-radius: 6px;
    background: white;
    color: #5b21b6;
    font-weight: 700;
    cursor: pointer;
}
.prog-filter-bar input[type="date"]:focus {
    outline: none;
    border-color: #7c3aed;
    box-shadow: 0 0 0 2px rgba(124,58,237,0.15);
}
.btn-rapport {
    border: none;
    border-radius: 8px;
    padding: 8px 14px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.2s;
    color: white;
    text-decoration: none;
}
.btn-rapport.prog      { background: linear-gradient(135deg, #5b21b6, #7c3aed); }
.btn-rapport.prog-date { background: linear-gradient(135deg, #d97706, #f59e0b); }
.btn-rapport.cloture   { background: linear-gradient(135deg, #16a34a, #15803d); }
.btn-rapport:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(29,78,216,0.35);
}
.btn-rapport.prog:hover      { box-shadow: 0 4px 12px rgba(91,33,182,0.4); }
.btn-rapport.prog-date:hover { box-shadow: 0 4px 12px rgba(217,119,6,0.4); }
.btn-rapport.cloture:hover   { box-shadow: 0 4px 12px rgba(22,163,74,0.4); }

/* ═══ Bouton "Effacer filtres" ═══ */
.btn-clear-filters {
    margin-left: auto;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    background: #fee2e2;
    color: #dc2626;
    border: 1.5px solid #fca5a5;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 11px;
    font-weight: 800;
    text-decoration: none;
    transition: 0.2s;
}
.btn-clear-filters:hover {
    background: #dc2626;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(220,38,38,0.35);
}

/* ═══ MODALE TITRE ═══ */
#titreModalOverlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9998;
}
#titreModalOverlay.visible { display: block; }

#titreModal {
    display: none;
    position: fixed; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 24px;
    border-radius: 14px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.25);
    z-index: 9999;
    width: 520px; max-width: 95%; max-height: 90vh;
    overflow-y: auto;
}
#titreModal.visible { display: block; }
</style>

{{-- EN-TÊTE --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">📋 Affectations à programmer</h2>
        <div style="font-size:13px;color:#64748b;">
            {{ $affectations->total() }} ligne(s) — attribuez les dates d'implantation puis validez
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">

        <a href="{{ route('affectation-rapide.index') }}" class="btn btn-primary btn-sm">
            🎯 Nouvelle affectation
        </a>

        <a href="{{ route('affectations.historique') }}" class="btn btn-outline-primary btn-sm">
            📜 Historique
        </a>

        <div class="d-flex gap-2 flex-wrap">

            <a href="{{ route('affectations.documents.avant-date') }}"
               class="btn btn-sm"
               style="background:linear-gradient(135deg,#5b21b6,#7c3aed);
                      color:white;border:none;font-weight:800;
                      box-shadow:0 3px 10px rgba(91,33,182,0.3);
                      display:inline-flex;align-items:center;gap:6px;">
                📄 Rapport des attributions
            </a>

            <a href="{{ route('affectations.documents.avec-date') }}"
               class="btn btn-sm"
               style="background:linear-gradient(135deg,#d97706,#f59e0b);
                      color:white;border:none;font-weight:800;
                      box-shadow:0 3px 10px rgba(217,119,6,0.3);
                      display:inline-flex;align-items:center;gap:6px;">
                📅 Rapport de planification
            </a>

            <a href="{{ route('affectations.documents.finales') }}"
               class="btn btn-sm"
               style="background:linear-gradient(135deg,#15803d,#16a34a);
                      color:white;border:none;font-weight:800;
                      box-shadow:0 3px 10px rgba(22,163,74,0.3);
                      display:inline-flex;align-items:center;gap:6px;">
                🔒 Rapport de clôture
            </a>

        </div>
    </div>
</div>

{{-- 🎯 BARRE DE SÉLECTION MULTIPLE --}}
<div class="selection-bar" id="selectionBar">
    <div class="selection-bar-header">
        <div class="selection-bar-title">
            🎯 Sélection multiple
            <span class="selection-bar-count" id="selectionCount">0</span>
        </div>
        <div class="selection-bar-actions">
            <label style="font-size:12px;font-weight:700;color:#1e3a5f;">📅 Date d'implantation :</label>
            <input type="datetime-local" id="dateImplantationMultiple" class="date-input-multiple">
            <button onclick="appliquerDateMultiple()" class="btn-apply-date" id="btnApplyDate" disabled>
                ✓ Appliquer aux lignes sélectionnées
            </button>
            <button onclick="effacerSelection()" class="btn-clear-selection">
                ✖ Effacer
            </button>
        </div>
    </div>
</div>

{{-- STATISTIQUES --}}
<div class="aff-stats">
    <div class="aff-stat" style="border-left-color:#1d4ed8;">
        <div class="ico" style="background:#dbeafe;color:#1d4ed8;">📋</div>
        <div>
            <div class="lbl">Lignes à programmer</div>
            <div class="val">{{ number_format($stats['total_lignes'], 0, ',', ' ') }}</div>
        </div>
    </div>
    <div class="aff-stat" style="border-left-color:#7c3aed;">
        <div class="ico" style="background:#ede9fe;color:#7c3aed;">📦</div>
        <div>
            <div class="lbl">Lots</div>
            <div class="val">{{ number_format($stats['total_lots'], 0, ',', ' ') }}</div>
            <div class="sub">cumul des lots</div>
        </div>
    </div>
    <div class="aff-stat" style="border-left-color:#f59e0b;">
        <div class="ico" style="background:#fef3c7;color:#f59e0b;">📐</div>
        <div>
            <div class="lbl">Superficie</div>
            <div class="val">{{ number_format($stats['total_superficie'], 0, ',', ' ') }} <small style="font-size:11px;color:#64748b;">m²</small></div>
            <div class="sub">cumul superficie</div>
        </div>
    </div>
</div>

{{-- FILTRES PRINCIPAUX --}}
<div class="aff-filters">
    <form method="GET" action="{{ route('affectations.liste') }}">
        {{-- ✅ Préserver les filtres de date dans la query string --}}
        @if(request('jour_attribution'))
            <input type="hidden" name="jour_attribution" value="{{ request('jour_attribution') }}">
        @endif
        @if(request('jour'))
            <input type="hidden" name="jour" value="{{ request('jour') }}">
        @endif

        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;">🔍 Recherche</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Nom, téléphone, lot..."
                       value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <label style="font-size:11px;font-weight:700;color:#64748b;">🏢 Grand Site</label>
                <select name="grand_site_id" class="form-control form-control-sm">
                    <option value="">Tous</option>
                    @foreach($grandSites as $gs)
                        <option value="{{ $gs->id }}" {{ request('grand_site_id') == $gs->id ? 'selected' : '' }}>{{ $gs->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size:11px;font-weight:700;color:#64748b;">🏗️ Bloc</label>
                <select name="bloc_id" class="form-control form-control-sm">
                    <option value="">Tous</option>
                    @foreach($blocs as $bloc)
                        <option value="{{ $bloc->id }}" {{ request('bloc_id') == $bloc->id ? 'selected' : '' }}>
                            Bloc {{ $bloc->code }}@if($bloc->tf) ({{ $bloc->tf->title }})@endif
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Du</label>
                <input type="date" name="du" class="form-control form-control-sm" value="{{ request('du') }}">
            </div>
            <div class="col-md-2">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Au</label>
                <input type="date" name="au" class="form-control form-control-sm" value="{{ request('au') }}">
            </div>
            <div class="col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">🔍</button>
                <a href="{{ route('affectations.liste') }}" class="btn btn-outline-secondary btn-sm">✖</a>
            </div>
        </div>
    </form>
</div>

{{-- TABLEAU --}}
<div class="aff-table-wrap">
    @if($affectations->count() > 0)
    <div style="overflow-x:auto;">
        <table class="aff-table">
            <thead>
                <tr>
                    <th style="width:40px;">
                        <input type="checkbox" class="checkbox-ligne" id="checkboxSelectAll"
                               onchange="toggleSelectAll(this)">
                    </th>
                    <th>Bénéficiaire / Client</th>
                    <th>Grand Site</th>
                    <th>Site</th>
                    <th>TF</th>
                    <th>Bloc</th>
                    <th>Lots</th>
                    <th>Superficie</th>
                    <th>Date</th>
                    <th style="min-width:230px;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span>📅 Implantation</span>
                        </div>
                    </th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                {{-- ═══════════════════════════════════════════════════ --}}
                {{-- 📄 BARRE DE RAPPORTS + 2 FILTRES CUMULABLES         --}}
                {{-- ═══════════════════════════════════════════════════ --}}
                <tr class="prog-filter-row">
                    <td colspan="11">
                        <div class="prog-filter-bar">

                            @php
                                // ✅ Dates des filtres avec fallback
                                $attrActive    = request('jour_attribution', now()->format('Y-m-d'));
                                $attrAffichee  = \Carbon\Carbon::parse($attrActive)->format('d/m/Y');

                                $jourActif    = request('jour', now()->format('Y-m-d'));
                                $jourAffiche  = \Carbon\Carbon::parse($jourActif)->format('d/m/Y');

                                $aFiltreAttribution = request()->filled('jour_attribution');
                                $aFiltreJour        = request()->filled('jour');
                            @endphp

                            {{-- ═══════════════════════════════════════════════ --}}
                            {{-- 📄 ATTRIBUTIONS : sa PROPRE date (affectation)  --}}
                            {{-- ═══════════════════════════════════════════════ --}}
                            <span class="label" style="color:#5b21b6;">📄 Attributions</span>

                            <button onclick="genererRapportAttribution()" class="btn-rapport prog">
                                📄 Rapport des attributions du {{ $attrAffichee }}
                            </button>

                            <input type="date"
                                   id="filtreDateAttribution"
                                   name="jour_attribution"
                                   value="{{ $attrActive }}"
                                   title="Filtrer par date d'attribution"
                                   onchange="appliquerFiltreAttribution(this.value)"
                                   style="border:1px solid rgba(252, 244, 244, 0.205);
                                          background:rgb(255, 255, 255);
                                          color: #5b21b6;
                                          border-radius:6px;
                                          padding:3px 6px;
                                          font-size:11px;
                                          font-weight:700;
                                          cursor:pointer;
                                          width:125px;
                                          outline:none;">

                            {{-- ═══ SÉPARATEUR VISUEL ═══ --}}
                            <span style="width:2px;height:32px;background:#93c5fd;margin:0 8px;border-radius:2px;"></span>

                            {{-- ═══════════════════════════════════════════════ --}}
                            {{-- 📅 PLANIFICATION + 🔒 CLÔTURE : filtre `jour`    --}}
                            {{-- ═══════════════════════════════════════════════ --}}
                            <span class="label" style="color:#92400e;">📅 Planification</span>

                            <button onclick="genererRapportPlanification()" class="btn-rapport prog-date">
                                📅 Rapport de planification des implantations du {{ $jourAffiche }}
                            </button>

                            <button onclick="genererRapportCloture()" class="btn-rapport cloture">
                                🔒 Rapport de clôture de planification des implantations du {{ $jourAffiche }}
                            </button>

                            <input type="date"
                                   id="filtreJour"
                                   name="jour"
                                   value="{{ $jourActif }}"
                                   title="Filtrer par jour d'implantation"
                                   onchange="appliquerFiltreJour(this.value)"
                                   style="border:1px solid rgba(252, 244, 244, 0.205);
                                          background:rgb(255, 255, 255);
                                          color: #15803d;
                                          border-radius:6px;
                                          padding:3px 6px;
                                          font-size:11px;
                                          font-weight:700;
                                          cursor:pointer;
                                          width:125px;
                                          outline:none;">

                            {{-- ═══════════════════════════════════════════════ --}}
                            {{-- 🧹 BOUTON "EFFACER FILTRES" (si au moins 1 filtre actif) --}}
                            {{-- ═══════════════════════════════════════════════ --}}
                            @if($aFiltreAttribution || $aFiltreJour)
                                <a href="{{ route('affectations.liste') }}" class="btn-clear-filters">
                                    ✖ Effacer filtres
                                </a>
                            @endif

                        </div>
                    </td>
                </tr>

                @foreach($affectations as $ligne)
                @php
                    $cleLigne = $ligne['cle'];
                @endphp
                <tr class="row-attente" data-cle="{{ $cleLigne }}">
                    <td>
                        <input type="checkbox" class="checkbox-ligne checkbox-ligne-item"
                               data-cle="{{ $cleLigne }}"
                               data-affectation-ids='@json($ligne["affectation_ids"])'
                               onchange="toggleSelectionLigne(this)">
                    </td>
                    <td>
                        <div class="aff-nom">
                            {{ $ligne['beneficiaire'] }}
                            @if($ligne['telephone'])
                                <small>📞 {{ $ligne['telephone'] }}</small>
                            @endif
                        </div>
                    </td>
                    <td>@if($ligne['grand_site'])<span class="pill pill-site">🏢 {{ $ligne['grand_site'] }}</span>@endif</td>
                    <td>@if($ligne['site'])<span class="pill pill-site">📍 {{ $ligne['site'] }}</span>@endif</td>
                    <td>@if($ligne['tf'])<span class="pill pill-tf">📄 {{ $ligne['tf'] }}</span>@endif</td>
                    <td>@if($ligne['bloc'])<span class="pill pill-bloc">🏗️ {{ $ligne['bloc'] }}</span>@endif</td>
                    <td style="max-width:240px;">
                        @if($ligne['lots'])
                            <span class="pill pill-lot">
                                📦 {{ $ligne['lots'] }}
                                <span class="pill-count">{{ $ligne['lots_count'] }}</span>
                            </span>
                        @endif
                    </td>
                    <td>
                        <span class="pill pill-sup">
                            📐 {{ number_format($ligne['superficie_totale'], 0, ',', ' ') }} m²
                        </span>
                    </td>
                    <td style="font-size:11px;color:#64748b;">
                        {{ $ligne['date_affectation']?->format('d/m/Y') ?? '—' }}
                    </td>

                    {{-- 📅 Date + heure d'implantation --}}
                    <td>
                        <input type="datetime-local"
                               class="form-control form-control-sm impl-input"
                               style="font-size:11px;padding:4px 6px;"
                               value="{{ $ligne['date_implantation']?->format('Y-m-d\TH:i') ?? '' }}"
                               data-affectation-ids='@json($ligne["affectation_ids"])'
                               onchange="majImplantationGroupe(this)">
                    </td>

                    {{-- Actions --}}
                    <td>
                        <div class="aff-actions">
                            <button class="aff-btn edit"
                                    onclick='ouvrirEditGroupe(@json($ligne["affectation_ids"]))'
                                    title="Modifier">✏️</button>
                            <button class="aff-btn delete"
                                    onclick='supprimerGroupe(@json($ligne["affectation_ids"]), @json($ligne["beneficiaire"]))'
                                    title="Supprimer">🗑</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($affectations->hasPages())
    <div style="padding:14px 20px;background:#f8fafc;border-top:1px solid #f1f5f9;">
        {{ $affectations->links() }}
    </div>
    @endif

    @else
    <div class="empty-state">
        @if(request('jour') || request('jour_attribution'))
            <div class="ico">📭</div>
            <div style="font-weight:700;font-size:15px;color:#475569;">
                Aucune affectation ne correspond aux filtres
            </div>
            <div style="font-size:12px;margin-top:6px;">
                @if(request('jour_attribution'))
                    📄 Attribution : {{ \Carbon\Carbon::parse(request('jour_attribution'))->format('d/m/Y') }}<br>
                @endif
                @if(request('jour'))
                    📅 Planification : {{ \Carbon\Carbon::parse(request('jour'))->format('d/m/Y') }}
                @endif
            </div>
            <a href="{{ route('affectations.liste') }}" class="btn btn-primary btn-sm" style="margin-top:14px;">
                ✖ Retirer les filtres
            </a>
        @else
            <div class="ico">✅</div>
            <div style="font-weight:700;font-size:15px;color:#475569;">Aucune affectation à programmer</div>
            <div style="font-size:12px;margin-top:6px;">Toutes les affectations ont été traitées.</div>
            <a href="{{ route('affectations.historique') }}" class="btn btn-primary btn-sm" style="margin-top:14px;">
                📜 Voir l'historique
            </a>
        @endif
    </div>
    @endif
</div>

{{-- MODAL MODIFICATION GROUPÉE --}}
<div class="modal-overlay" id="editOverlay" onclick="fermerEdit()"></div>
<div class="modal-box" id="editModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;">✏️ Modifier les affectations</h5>
        <button onclick="fermerEdit()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div id="editInfo" style="background:#eff6ff;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#1e3a5f;"></div>
    <input type="hidden" id="editIds">

    <div style="display:grid;grid-template-columns:1fr 2fr;gap:10px;margin-bottom:14px;">
        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Date d'affectation</label>
            <input type="date" id="editDate" class="form-control form-control-sm">
        </div>
        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">📝 Notes</label>
            <input type="text" id="editNotes" class="form-control form-control-sm" placeholder="Optionnel" maxlength="1000">
        </div>
    </div>

    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:12px;margin-bottom:14px;">
        <div style="font-size:11px;font-weight:700;color:#16a34a;text-transform:uppercase;margin-bottom:8px;">📦 Lots actuellement affectés</div>
        <div id="editLotsActuels" style="display:flex;flex-wrap:wrap;gap:6px;min-height:36px;"></div>
    </div>

    <div style="background:#eff6ff;border:1px solid #93c5fd;border-radius:10px;padding:12px;margin-bottom:14px;">
        <div style="font-size:11px;font-weight:700;color:#1d4ed8;text-transform:uppercase;margin-bottom:10px;">➕ Ajouter des lots (n'importe quel grand site)</div>

        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size:10px;font-weight:700;color:#64748b;">🏢 Grand Site</label>
                <select id="editGs" class="form-control form-control-sm" onchange="editChargerSites(this.value)"><option value="">-- Choisir --</option></select>
            </div>
            <div class="col-md-3">
                <label style="font-size:10px;font-weight:700;color:#64748b;">📍 Site</label>
                <select id="editSite" class="form-control form-control-sm" onchange="editChargerTfs(this.value)"><option value="">-- Choisir --</option></select>
            </div>
            <div class="col-md-3">
                <label style="font-size:10px;font-weight:700;color:#64748b;">📄 TF</label>
                <select id="editTf" class="form-control form-control-sm" onchange="editChargerBlocs(this.value)"><option value="">-- Choisir --</option></select>
            </div>
            <div class="col-md-3">
                <label style="font-size:10px;font-weight:700;color:#64748b;">🏗️ Bloc</label>
                <select id="editBloc" class="form-control form-control-sm" onchange="editChargerLots(this.value)"><option value="">-- Choisir --</option></select>
            </div>
        </div>

        <div id="editLotsDisponibles" style="margin-top:10px;">
            <div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">ℹ️ Sélectionnez GS → Site → TF → Bloc</div>
        </div>

        <div id="editLotsAjouterResume" style="display:none;margin-top:10px;padding:8px 12px;background:#dcfce7;border:2px solid #86efac;border-radius:8px;font-size:12px;color:#166534;font-weight:700;"></div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerEdit()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="sauvegarderEditGroupe()" class="btn btn-warning btn-sm" style="font-weight:700;">💾 Enregistrer</button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODALE TITRE DU DOCUMENT                                     --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div id="titreModalOverlay" onclick="fermerModalTitre()"></div>
<div id="titreModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1d4ed8;font-weight:800;margin:0;">📄 Titre du document</h5>
        <button onclick="fermerModalTitre()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div style="background:#eff6ff;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:12.5px;color:#1e40af;">
        ℹ️ Donnez un titre à ce document PDF. Il sera utilisé comme <strong>nom du fichier</strong> et stocké en base.
    </div>

    <label style="font-size:12px;font-weight:700;color:#64748b;">
        📝 Titre du document <span style="color:#dc2626;">*</span>
    </label>
    <input type="text"
           id="titreDocumentInput"
           class="form-control form-control-sm"
           style="margin-top:6px;"
           maxlength="150"
           placeholder="Ex : Rapport du 11/10/2026">

    <div style="font-size:11px;color:#94a3b8;margin-top:10px;">
        💡 Fichier : <strong id="apercuNomFichier">rapport-2026-10-05.pdf</strong>
    </div>

    <div id="titreError" style="display:none;margin-top:10px;padding:8px 12px;background:#fee2e2;border-radius:6px;font-size:11px;color:#991b1b;">
        ⚠️ Le titre est obligatoire (min. 3 caractères).
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerModalTitre()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="confirmerTitreEtValider()" class="btn btn-primary btn-sm" style="font-weight:700;">
            ✅ Confirmer et télécharger
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

let lotsAAjouter = new Set();
let lignesSelectionnees = new Map();
let actionEnAttente = null;

// ════════════════════════════════════════════════════════════════
// ✅ MODALE TITRE
// ════════════════════════════════════════════════════════════════
function demanderTitre(callback, titreParDefaut = '') {
    actionEnAttente = callback;

    const dateStr = '{{ now()->format("Y-m-d") }}';
    const input = document.getElementById('titreDocumentInput');

    input.value = titreParDefaut;

    document.getElementById('titreError').style.display = 'none';
    document.getElementById('apercuNomFichier').textContent = 'rapport-' + dateStr + '.pdf';

    input.oninput = function() {
        const val = this.value.trim() || 'rapport';
        const slug = val.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        document.getElementById('apercuNomFichier').textContent =
            (slug || 'rapport') + '-' + dateStr + '.pdf';
    };

    if (titreParDefaut) {
        input.dispatchEvent(new Event('input'));
    }

    document.getElementById('titreModalOverlay').classList.add('visible');
    document.getElementById('titreModal').classList.add('visible');

    setTimeout(() => {
        input.focus();
        input.select();
    }, 100);
}

function fermerModalTitre() {
    document.getElementById('titreModalOverlay').classList.remove('visible');
    document.getElementById('titreModal').classList.remove('visible');
}

function confirmerTitreEtValider() {
    const titre = document.getElementById('titreDocumentInput').value.trim();

    if (!titre || titre.length < 3) {
        document.getElementById('titreError').style.display = 'block';
        return;
    }

    document.getElementById('titreError').style.display = 'none';

    const callback = actionEnAttente;

    document.getElementById('titreModalOverlay').classList.remove('visible');
    document.getElementById('titreModal').classList.remove('visible');
    actionEnAttente = null;

    if (typeof callback === 'function') {
        callback(titre);
    }
}

// ════════════════════════════════════════════════════════════════
// 📄 FILTRE PAR DATE D'ATTRIBUTION (NOUVEAU)
// ════════════════════════════════════════════════════════════════
function appliquerFiltreAttribution(date) {
    const url = new URL(window.location.href);

    if (!date) {
        url.searchParams.delete('jour_attribution');
    } else {
        url.searchParams.set('jour_attribution', date);
    }

    window.location.href = url.toString();
}

// ════════════════════════════════════════════════════════════════
// 📅 FILTRE PAR JOUR D'IMPLANTATION (existant)
// ════════════════════════════════════════════════════════════════
function appliquerFiltreJour(date) {
    const url = new URL(window.location.href);

    if (!date) {
        url.searchParams.delete('jour');
    } else {
        url.searchParams.set('jour', date);
    }

    window.location.href = url.toString();
}

// ════════════════════════════════════════════════════════════════
// 📄 RAPPORT DES ATTRIBUTIONS — utilise la date du FILTRE
// ════════════════════════════════════════════════════════════════
function genererRapportAttribution() {
    const dateAttr = document.getElementById('filtreDateAttribution')?.value
                  || '{{ request("jour_attribution", now()->format("Y-m-d")) }}';

    if (!dateAttr) {
        showToast('⚠️ Veuillez choisir une date d\'attribution', 'warning');
        return;
    }

    const dateLabel = new Date(dateAttr).toLocaleDateString('fr-FR');
    const titreAuto = 'Rapport des attributions du ' + dateLabel;

    demanderTitre(function(titre) {
        envoyerRapport(
            '{{ route("affectations.rapport-programmation-pdf") }}',
            titre || titreAuto,
            { du: dateAttr, au: dateAttr }
        );
    }, titreAuto);
}

// ════════════════════════════════════════════════════════════════
// 📅 RAPPORT DE PLANIFICATION — utilise le filtre `jour`
// ════════════════════════════════════════════════════════════════
function genererRapportPlanification() {
    const jour = document.getElementById('filtreJour')?.value
              || '{{ request("jour", now()->format("Y-m-d")) }}';

    if (!jour) {
        showToast('⚠️ Veuillez choisir une date d\'implantation', 'warning');
        return;
    }

    const dateLabel = new Date(jour).toLocaleDateString('fr-FR');
    const titreAuto = 'Rapport de planification des implantations du ' + dateLabel;

    demanderTitre(function(titre) {
        envoyerRapport(
            '{{ route("affectations.rapport-programmation-geometre-pdf") }}',
            titre || titreAuto,
            { jour: jour }
        );
    }, titreAuto);
}

function genererRapportCloture() {
    const jour = document.getElementById('filtreJour')?.value;

    if (!jour) {
        showToast('⚠️ Veuillez choisir une date d\'implantation', 'warning');
        return;
    }

    const affectationIds = [];
    document.querySelectorAll('[data-affectation-ids]').forEach(el => {
        try {
            const ids = JSON.parse(el.dataset.affectationIds || '[]');
            ids.forEach(id => {
                if (!affectationIds.includes(id)) affectationIds.push(id);
            });
        } catch (e) {}
    });

    if (affectationIds.length === 0) {
        showToast('⚠️ Aucune affectation à clôturer.', 'warning');
        return;
    }

    const dateLabel = new Date(jour).toLocaleDateString('fr-FR');
    const titreAuto = 'Rapport de clôture de planification des implantations du ' + dateLabel;

    demanderTitre(function(titre) {
        envoyerCloture(affectationIds, titre || titreAuto, jour);
    }, titreAuto);
}

// ════════════════════════════════════════════════════════════════
// 🔒 CLÔTURE → PDF + passage à 'cloturee' (Étape 1)
// ════════════════════════════════════════════════════════════════
function envoyerCloture(affectationIds, titreDocument, jour) {
    if (window.EdenLoader) window.EdenLoader.show();

    fetch('{{ route("affectations.valider-etape-1") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            affectation_ids: affectationIds,
            titre_document:  titreDocument,
            type_rapport:    'cloture',
            jour:            jour,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();

        if (data.success) {
            showToastWithLink(
                '✅ Rapport de clôture généré — Redirection vers Étape 1 pour attribuer géomètre + heure.',
                data.doc?.url || null,
                'success'
            );

            setTimeout(() => {
                window.location.href = '{{ route("affectations.programmation") }}';
            }, 1500);
        } else {
            alert('❌ ' + (data.message || 'Erreur'));
        }
    })
    .catch(err => {
        if (window.EdenLoader) window.EdenLoader.hide();
        console.error(err);
        alert('❌ Erreur réseau');
    });
}

// ════════════════════════════════════════════════════════════════
// Helper — Envoyer la requête POST pour générer le rapport
// ════════════════════════════════════════════════════════════════
function envoyerRapport(url, titreDocument, filtres) {
    if (window.EdenLoader) window.EdenLoader.show();

    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            titre_document: titreDocument,
            ...filtres,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();

        if (data.success) {
            showToast('✅ ' + data.message, 'success');
            if (data.doc && data.doc.url) {
                window.open(data.doc.url, '_blank');
            }
        } else {
            alert('❌ ' + (data.message || 'Erreur'));
        }
    })
    .catch(err => {
        if (window.EdenLoader) window.EdenLoader.hide();
        console.error(err);
        alert('❌ Erreur réseau');
    });
}

// ════════════════════════════════════════════════════════════════
// 🎯 SÉLECTION MULTIPLE
// ════════════════════════════════════════════════════════════════
function toggleSelectionLigne(checkbox) {
    const cle = checkbox.dataset.cle;
    let ids = [];
    try { ids = JSON.parse(checkbox.dataset.affectationIds || '[]'); } catch (e) { return; }

    const row = checkbox.closest('tr');
    if (checkbox.checked) {
        lignesSelectionnees.set(cle, ids);
        row.classList.add('selected');
    } else {
        lignesSelectionnees.delete(cle);
        row.classList.remove('selected');
    }
    mettreAJourBarreSelection();
}

function toggleSelectAll(masterCheckbox) {
    document.querySelectorAll('.checkbox-ligne-item').forEach(cb => {
        cb.checked = masterCheckbox.checked;
        toggleSelectionLigne(cb);
    });
}

function mettreAJourBarreSelection() {
    const bar   = document.getElementById('selectionBar');
    const count = document.getElementById('selectionCount');
    const btn   = document.getElementById('btnApplyDate');
    const input = document.getElementById('dateImplantationMultiple');

    const total = lignesSelectionnees.size;
    count.textContent = total;

    if (total > 0) bar.classList.add('visible');
    else bar.classList.remove('visible');

    btn.disabled = !(total > 0 && input.value);
}

document.addEventListener('DOMContentLoaded', () => {
    const inputMulti = document.getElementById('dateImplantationMultiple');
    if (inputMulti) {
        inputMulti.addEventListener('input', mettreAJourBarreSelection);
        inputMulti.addEventListener('change', mettreAJourBarreSelection);
    }
});

function effacerSelection() {
    document.querySelectorAll('.checkbox-ligne-item').forEach(cb => {
        cb.checked = false;
        cb.closest('tr').classList.remove('selected');
    });
    document.getElementById('checkboxSelectAll').checked = false;
    lignesSelectionnees.clear();
    document.getElementById('dateImplantationMultiple').value = '';
    mettreAJourBarreSelection();
}

function appliquerDateMultiple() {
    const date = document.getElementById('dateImplantationMultiple').value;
    if (!date) { showToast('⚠️ Veuillez saisir une date', 'warning'); return; }
    if (lignesSelectionnees.size === 0) { showToast('⚠️ Aucune ligne sélectionnée', 'warning'); return; }

    const lignesPayload = [];
    lignesSelectionnees.forEach((ids, cle) => lignesPayload.push({ cle, ids }));

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('/admin/affectations/groupe/implantation-multiple', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ lignes: lignesPayload, date_implantation: date }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('✅ ' + data.message, 'success');
            effacerSelection();
            setTimeout(() => location.reload(), 1000);
        } else {
            let msg = data.message || 'Erreur';
            if (data.errors) msg = Object.values(data.errors).flat().join('\n');
            showToast('❌ ' + msg, 'error');
        }
    })
    .catch(err => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau', 'error');
    });
}

function majImplantationGroupe(el) {
    let affectationIds;
    try { affectationIds = JSON.parse(el.dataset.affectationIds || '[]'); } catch (e) { return; }
    if (!affectationIds || affectationIds.length === 0) return;

    const row = el.closest('tr');
    const inputDate = row.querySelector('input[type="datetime-local"]');
    const dateImplantation = inputDate?.value || null;

    const inputs = row.querySelectorAll('.impl-input');
    inputs.forEach(i => { i.disabled = true; i.style.opacity = '0.5'; });

    fetch('/admin/affectations/groupe/implantation', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ affectation_ids: affectationIds, date_implantation: dateImplantation }),
    })
    .then(r => r.json())
    .then(data => {
        inputs.forEach(i => { i.disabled = false; i.style.opacity = '1'; });
        if (data.success) showToast('✅ ' + (data.message || 'Enregistré'), 'success');
        else showToast('❌ ' + (data.message || 'Erreur'), 'error');
    })
    .catch(err => {
        inputs.forEach(i => { i.disabled = false; i.style.opacity = '1'; });
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// MODIFIER UN GROUPE
// ════════════════════════════════════════════════════════════════
function ouvrirEditGroupe(ids) {
    lotsAAjouter.clear();

    fetch(`/admin/affectations/groupe/details?ids[]=${ids.join('&ids[]=')}`, {
        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) { showToast('❌ ' + data.message, 'error'); return; }
        const g = data.groupe;

        document.getElementById('editIds').value   = JSON.stringify(ids);
        document.getElementById('editDate').value  = g.date_affectation;
        document.getElementById('editNotes').value = g.notes || '';

        document.getElementById('editInfo').innerHTML = `
            <div style="font-weight:700;">👤 ${g.beneficiaire}</div>
            <div style="font-size:11px;color:#475569;margin-top:4px;">
                ${g.grand_site ? `🏢 ${g.grand_site}` : ''}
                ${g.site ? ` · 📍 ${g.site}` : ''}
                ${g.tf ? ` · 📄 ${g.tf}` : ''}
                ${g.bloc ? ` · 🏗️ Bloc ${g.bloc}` : ''}
            </div>
            <div style="font-size:11px;color:#475569;margin-top:4px;">
                📦 <strong>${g.lots.length} lot(s)</strong> — 📐 ${parseInt(g.superficie_totale).toLocaleString('fr-FR')} m²
            </div>
        `;

        const contLots = document.getElementById('editLotsActuels');
        contLots.innerHTML = g.lots.length === 0
            ? '<div style="color:#94a3b8;font-size:11px;">Aucun lot</div>'
            : g.lots.map(l => `<span class="lot-chip">📦 Lot ${l.numero}${l.superficie ? ` <span style="color:#64748b;font-weight:400;">(${parseInt(l.superficie).toLocaleString('fr-FR')} m²)</span>` : ''}</span>`).join('');

        const selGs = document.getElementById('editGs');
        selGs.innerHTML = '<option value="">-- Choisir --</option>';
        data.grandSites.forEach(gs => {
            selGs.innerHTML += `<option value="${gs.id}">${gs.nom}</option>`;
        });

        document.getElementById('editSite').innerHTML = '<option value="">-- Choisir --</option>';
        document.getElementById('editTf').innerHTML   = '<option value="">-- Choisir --</option>';
        document.getElementById('editBloc').innerHTML = '<option value="">-- Choisir --</option>';
        document.getElementById('editLotsDisponibles').innerHTML = '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">ℹ️ Sélectionnez GS → Site → TF → Bloc</div>';
        document.getElementById('editLotsAjouterResume').style.display = 'none';

        document.getElementById('editOverlay').style.display = 'block';
        document.getElementById('editModal').style.display   = 'block';
    })
    .catch(() => showToast('❌ Erreur de chargement', 'error'));
}

function fermerEdit() {
    document.getElementById('editOverlay').style.display = 'none';
    document.getElementById('editModal').style.display   = 'none';
    lotsAAjouter.clear();
}

function editChargerSites(gsId) {
    const sel = document.getElementById('editSite');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('editTf').innerHTML   = '<option value="">-- Choisir --</option>';
    document.getElementById('editBloc').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('editLotsDisponibles').innerHTML = '';
    if (!gsId) return;
    fetch(`/admin/affectations/api/sites/${gsId}`, { headers: { 'X-CSRF-TOKEN': CSRF } })
        .then(r => r.json()).then(sites => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' + sites.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        });
}

function editChargerTfs(siteId) {
    const sel = document.getElementById('editTf');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('editBloc').innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('editLotsDisponibles').innerHTML = '';
    if (!siteId) return;
    fetch(`/admin/affectations/api/tfs/${siteId}`, { headers: { 'X-CSRF-TOKEN': CSRF } })
        .then(r => r.json()).then(tfs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' + tfs.map(t => `<option value="${t.id}">${t.title}</option>`).join('');
        });
}

function editChargerBlocs(tfId) {
    const sel = document.getElementById('editBloc');
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('editLotsDisponibles').innerHTML = '';
    if (!tfId) return;
    fetch(`/admin/affectations/api/blocs/${tfId}`, { headers: { 'X-CSRF-TOKEN': CSRF } })
        .then(r => r.json()).then(blocs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' + blocs.map(b => `<option value="${b.id}">Bloc ${b.code}</option>`).join('');
        });
}

function editChargerLots(blocId) {
    const container = document.getElementById('editLotsDisponibles');
    if (!blocId) { container.innerHTML = ''; return; }
    container.innerHTML = '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">⏳ Chargement...</div>';
    fetch(`/admin/affectations/api/lots/${blocId}`, { headers: { 'X-CSRF-TOKEN': CSRF } })
        .then(r => r.json()).then(lots => {
            if (!lots.length) {
                container.innerHTML = '<div style="color:#94a3b8;font-size:11px;text-align:center;padding:10px;">Aucun lot disponible</div>';
                return;
            }
            container.innerHTML = `
                <div style="font-size:10px;font-weight:700;color:#64748b;margin-bottom:6px;">Cochez les lots à ajouter :</div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    ${lots.map(l => `
                        <label class="lot-check-label" id="lot-label-${l.id}">
                            <input type="checkbox" value="${l.id}"
                                   data-superficie="${l.superficie ?? 0}" data-numero="${l.numero}"
                                   onchange="toggleLotAjouter(${l.id}, this)">
                            📦 Lot ${l.numero}
                            ${l.superficie ? `<span style="color:#64748b;font-size:9px;">(${parseInt(l.superficie).toLocaleString('fr-FR')} m²)</span>` : ''}
                        </label>
                    `).join('')}
                </div>
            `;
        });
}

function toggleLotAjouter(lotId, cb) {
    const label = document.getElementById('lot-label-' + lotId);
    if (cb.checked) { lotsAAjouter.add(lotId); label.classList.add('checked'); }
    else            { lotsAAjouter.delete(lotId); label.classList.remove('checked'); }
    mettreAJourResumeAjout();
}

function mettreAJourResumeAjout() {
    const resume = document.getElementById('editLotsAjouterResume');
    if (lotsAAjouter.size === 0) { resume.style.display = 'none'; return; }
    let sup = 0; const nums = [];
    document.querySelectorAll('#editLotsDisponibles input:checked').forEach(cb => {
        sup += parseFloat(cb.dataset.superficie) || 0;
        nums.push(cb.dataset.numero);
    });
    resume.style.display = 'block';
    resume.innerHTML = `✅ <strong>${lotsAAjouter.size} lot(s)</strong> à ajouter : ${nums.join(', ')} — 📐 ${sup.toLocaleString('fr-FR')} m²`;
}

function sauvegarderEditGroupe() {
    const ids   = JSON.parse(document.getElementById('editIds').value || '[]');
    const date  = document.getElementById('editDate').value;
    const notes = document.getElementById('editNotes').value;

    if (!date) { showToast('⚠️ La date est obligatoire', 'warning'); return; }

    if (window.EdenLoader) window.EdenLoader.show();
    const promises = [];

    ids.forEach(id => {
        promises.push(
            fetch(`/admin/affectations/${id}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({ date_affectation: date, notes: notes || null, _method: 'PUT' }),
            }).then(r => r.json())
        );
    });

    if (lotsAAjouter.size > 0) {
        promises.push(
            fetch('/admin/affectations/groupe/ajouter-lots', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                body: JSON.stringify({
                    affectation_ids: ids,
                    lot_ids: Array.from(lotsAAjouter),
                    date_affectation: date,
                    notes: notes || null,
                }),
            }).then(r => r.json())
        );
    }

    Promise.all(promises).then(results => {
        if (window.EdenLoader) window.EdenLoader.hide();
        const ok = results.filter(r => r.success).length;
        const ko = results.length - ok;
        if (ok > 0) {
            showToast(`✅ ${ok} opération(s) réussie(s)${ko > 0 ? ' — ' + ko + ' erreur(s)' : ''}`, 'success');
            fermerEdit();
            setTimeout(() => location.reload(), 1000);
        } else showToast('❌ Aucune modification appliquée', 'error');
    }).catch(() => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau', 'error');
    });
}

function supprimerGroupe(ids, nom) {
    if (!confirm(`⚠️ Supprimer les ${ids.length} affectation(s) de « ${nom} » ?\n\nLes lots seront libérés.`)) return;

    if (window.EdenLoader) window.EdenLoader.show();
    const promises = ids.map(id =>
        fetch(`/admin/affectations/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        }).then(r => r.json())
    );
    Promise.all(promises).then(results => {
        if (window.EdenLoader) window.EdenLoader.hide();
        const ok = results.filter(r => r.success).length;
        const ko = results.length - ok;
        if (ok > 0) {
            showToast(`✅ ${ok} affectation(s) supprimée(s)${ko > 0 ? ' — ' + ko + ' erreur(s)' : ''}`, 'success');
            setTimeout(() => location.reload(), 900);
        } else showToast('❌ Aucune suppression effectuée', 'error');
    }).catch(() => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau', 'error');
    });
}

function showToast(message, type = 'info') {
    document.querySelectorAll('.toast-notification').forEach(el => el.remove());
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
// 🔔 TOAST AVEC LIEN CLIQUABLE (pour les PDF générés)
// ════════════════════════════════════════════════════════════════
function showToastWithLink(message, url, type = 'success') {
    document.querySelectorAll('.toast-notification').forEach(el => el.remove());

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.style.cssText += 'display:flex; flex-direction:column; gap:8px;';

    const text = document.createElement('div');
    text.textContent = message;
    toast.appendChild(text);

    if (url) {
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.style.cssText = `
            display:inline-flex; align-items:center; gap:6px;
            background:white; color:#16a34a;
            padding:6px 12px; border-radius:6px;
            font-weight:800; font-size:12px;
            text-decoration:none; align-self:flex-start;
            box-shadow:0 2px 6px rgba(0,0,0,0.15);
        `;
        link.innerHTML = '📄 Ouvrir le PDF';
        toast.appendChild(link);
    }

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 6000);
}

</script>
@endsection