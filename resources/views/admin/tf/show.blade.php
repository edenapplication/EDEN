@extends('admin.layout')
@section('content')

<style>
.btn-outline-secondary:hover { background-color:#e5e7eb; color:#111; }
#legend-fixe {
    position:fixed; bottom:20px; right:20px;
    background:white; border:1px solid #e2e8f0; border-radius:12px;
    padding:12px 16px; box-shadow:0 4px 16px rgba(0,0,0,0.12);
    z-index:999; min-width:175px; font-size:12px;
}
#legend-fixe h6 { font-weight:700; margin-bottom:8px; font-size:12px; color:#1e3a5f; }
.leg-item { display:flex; align-items:center; gap:8px; margin-bottom:5px; }
.leg-dot  { width:14px; height:14px; border-radius:3px; flex-shrink:0; border:2px solid transparent; }
#toolbar { display:flex; align-items:center; gap:8px; margin-bottom:8px; font-size:13px; background:#f8fafc; border-radius:8px; padding:8px 12px; border:1px solid #e2e8f0; }
.tool-btn { padding:5px 12px; border-radius:6px; border:none; font-size:12px; font-weight:600; cursor:pointer; transition:0.15s; }
.tool-btn.active { background:#f59e0b; color:white; }
.tool-btn:not(.active) { background:#e2e8f0; color:#374151; }
.tool-btn:hover:not(.active) { background:#d1d5db; }
#tf-map-container { overflow:auto; position:relative; max-height:calc(100vh - 220px); min-height:500px; cursor:default; user-select:none; background:#f9fafb; }
#tf-map-container.zone-drawing { cursor:crosshair; }
#map-inner { display:inline-block; transform-origin:0 0; position:relative; }
.client-search-wrap { position:relative; }
.client-dropdown { position:absolute; top:100%; left:0; right:0; background:white; border:1px solid #ddd; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); z-index:999999; max-height:200px; overflow-y:auto; display:none; }
.client-option { padding:8px 12px; cursor:pointer; font-size:13px; border-bottom:1px solid #f8fafc; display:flex; justify-content:space-between; align-items:center; }
.client-option:hover { background:#eff6ff; }
#clientPanel { display:none; position:fixed; top:70px; z-index:99999; width:300px; max-height:calc(100vh - 90px); overflow-y:auto; background:white; border-radius:14px; box-shadow:0 8px 28px rgba(0,0,0,0.18); padding:16px; font-size:12px; border-top:4px solid #0d6efd; pointer-events:auto; }
#clientPanel h6 { font-weight:800; color:#1e3a5f; margin-bottom:2px; font-size:14px; }
.cp-dossier-section { border-radius:8px; padding:10px; margin-bottom:8px; background:#f8fafc; border-left:3px solid #0d6efd; }
.cp-pay-row { display:flex; justify-content:space-between; font-size:11px; padding:3px 0; border-bottom:1px solid #f1f5f9; }
.cp-pay-amt { font-weight:700; color:#28a745; }
.cp-prog-wrap { height:5px; background:#e2e8f0; border-radius:3px; margin-top:4px; }
.cp-prog-fill { height:100%; border-radius:3px; }
.btn-pay-small { background:#28a745; color:white; border:none; border-radius:6px; padding:4px 10px; font-size:11px; font-weight:600; cursor:pointer; transition:0.15s; margin-top:6px; width:100%; }
.btn-pay-small:hover { background:#1e7e34; }
.cp-lot-card { border-left:3px solid #ccc; padding:6px 8px; margin-bottom:8px; background:#f8fafc; border-radius:6px; }
#lotModal { pointer-events:auto; z-index:99998; }
#zoneGroupeModal { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.3); z-index:99999; width:450px; max-height:92vh; overflow-y:auto; pointer-events:auto; }
#paiementDossierModal { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.25); z-index:1000000; width:380px; pointer-events:auto; }
#modalOverlay { pointer-events:none; }
.badge-next-step { background:linear-gradient(135deg,#4D96FF,#0d6efd); color:white; font-size:11px; padding:3px 10px; border-radius:20px; font-weight:600; margin-left:8px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div style="flex:1;">
        <a href="{{ route('tf.show', $tf->id) }}"
           style="display:inline-block;width:70%;background:linear-gradient(135deg,#1e3a5f,#2d6cdf);color:white;border-radius:12px;padding:14px 24px;font-size:18px;font-weight:800;text-decoration:none;box-shadow:0 4px 14px rgba(30,58,95,0.3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"
           onmouseover="this.style.opacity='0.88'" onmouseout="this.style.opacity='1'">
            🗺️ {{ $tf->title }}
        </a>
    </div>
    <div class="d-flex gap-2">
        <button onclick="imprimerCarte()" class="btn btn-outline-secondary btn-sm">🖨️ Imprimer</button>
        <a href="{{ route('sites.show', $tf->site_id) }}" class="btn btn-outline-secondary btn-sm">← Retour au site</a>
    </div>
</div>

@if($tf->file_path)
<div style="margin-top:8px;border:1px solid #ddd;border-radius:10px;padding:12px;">
    <div id="toolbar">
        <span style="font-weight:700;color:#1e3a5f;">🛠️ Outil :</span>
        <button id="toolSelect"     class="tool-btn active" onclick="setTool('select')">🖱️ Sélection</button>
        <button id="toolZoneDessin" class="tool-btn"        onclick="setTool('zone')">✏️ Dessiner zone</button>
        <span id="toolHint" style="font-size:11px;color:#64748b;margin-left:6px;"></span>
        <div style="margin-left:auto;display:flex;align-items:center;gap:8px;">
            <span style="font-size:12px;">🔍 Zoom :</span>
            <input type="range" id="svg-zoom" min="20" max="400" value="100" style="width:130px;">
            <span id="svg-zoom-val">100%</span>
            <button onclick="resetZoom()" class="tool-btn" style="padding:3px 8px;font-size:11px;">↺ Reset</button>
        </div>
    </div>
    @php
        $svgPath = storage_path('app/public/' . $tf->file_path);
        $ext     = pathinfo($tf->file_path, PATHINFO_EXTENSION);
    @endphp
    @if($ext === 'svg')
        <div id="tf-map-container">
            <div id="map-inner">
                {!! file_get_contents($svgPath) !!}
                <canvas id="draw-canvas" style="position:absolute;top:0;left:0;pointer-events:none;"></canvas>
            </div>
        </div>
    @endif
</div>
@endif

<div id="modalOverlay" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.35);z-index:99996;pointer-events:none;"></div>

<div id="originModal" style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:24px;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,0.3);z-index:99999;width:320px;text-align:center;pointer-events:auto;">
    <h4 style="color:#1e3a5f;margin-bottom:4px;">📍 Origine du lot</h4>
    <p style="color:#64748b;font-size:13px;margin-bottom:16px;">Zone : <strong id="originZoneLabel">-</strong></p>
    <div class="mb-3 text-start">
        <label style="font-size:13px;font-weight:600;color:#374151;">Superficie (m²)</label>
        <input type="number" id="origine_superficie" class="form-control mt-1" placeholder="Ex: 500" min="1">
    </div>
    <div class="d-flex gap-2 justify-content-center mt-3">
        <button class="btn btn-primary px-4" onclick="setOrigin('eden')">🔵 EDEN</button>
        <button onclick="setOrigin('famille')" style="background:#c8a882;color:#4a2e0a;border:none;padding:8px 20px;border-radius:8px;font-weight:600;cursor:pointer;">🏡 FAMILLE</button>
    </div>
    <button onclick="closeAllModals()" class="btn btn-light btn-sm mt-3 w-100">Annuler</button>
</div>

<div id="lotModal" style="display:none;position:fixed;top:50%;transform:translateY(-50%);background:white;padding:20px;border-radius:12px;box-shadow:0 10px 30px rgba(0,0,0,0.3);z-index:99998;width:420px;max-height:92vh;overflow-y:auto;pointer-events:auto;">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div><h4 class="mb-0">📦 Lot</h4><div id="lotEtapeInfo" style="font-size:12px;color:#64748b;margin-top:2px;"></div></div>
        <button onclick="closeModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <p>Zone : <strong id="zoneLabel">-</strong></p>
    <label>Type</label>
    <select id="lotType" class="form-control">
        <option value="">-- Choisir --</option>
        <option value="implantation_prevue">Implantation prévue</option>
        <option value="deja_implante">Déjà implanté</option>
        <option value="dossier_technique">Dossier technique</option>
        <option value="morcellement">Morcellement</option>
    </select>
    <hr>
    <div id="clientExistantInfo" style="display:none;padding:8px 12px;background:#f0f7ff;border-radius:8px;font-size:12px;color:#1e3a5f;margin-bottom:10px;border-left:3px solid #0d6efd;">
        👤 <strong id="clientExistantNom"></strong><span id="clientExistantPhone" style="color:#64748b;"></span>
    </div>
    <div id="clientSearchField" style="display:none;" class="mt-2">
        <label style="font-weight:600;color:#374151;">👤 Client</label>
        <div class="client-search-wrap mt-1">
            <input type="text" id="client_search" class="form-control" placeholder="🔍 Rechercher client..." autocomplete="off">
            <div class="client-dropdown" id="client_dropdown"></div>
        </div>
        <input type="hidden" id="selected_client_id">
        <div id="client_selected_info" style="display:none;margin-top:6px;padding:6px 10px;background:#ecfdf5;border-radius:6px;font-size:12px;color:#065f46;"></div>
    </div>
    <div id="dossierSelectField" style="display:none;" class="mt-2">
        <label style="font-weight:600;color:#374151;">📂 Lier au dossier client</label>
        <select id="selected_dossier_id" class="form-control form-control-sm mt-1"><option value="">-- Choisir un dossier --</option></select>
        <div id="dossier_selected_info" style="display:none;margin-top:6px;padding:6px 10px;background:#eff6ff;border-radius:6px;font-size:12px;color:#1e3a5f;"></div>
    </div>
    <div id="datePrevueGroup" style="display:none;" class="mt-2"><label>Date prévue</label><input type="date" id="date_prevue" class="form-control"></div>
    <div id="dateConfirmeeGroup" class="mt-2" style="display:none;"><label>Date confirmée</label><input type="date" id="date_confirmee" class="form-control"></div>
    <div id="dateMorcellementGroup" class="mt-2" style="display:none;"><label>Date morcellement</label><input type="date" id="date_morcellement" class="form-control"></div>
    <div id="superficieField" class="mt-2" style="display:none;"><label>Superficie (m²)</label><input type="number" id="superficie" class="form-control"></div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <div id="supprimerLotBtn" style="display:none;margin-bottom:10px;">
    <button onclick="supprimerLot()" class="btn btn-outline-danger btn-sm w-100">🗑 Supprimer ce lot</button>
</div>
        <button onclick="closeModal()" class="btn btn-light">Annuler</button>
        <button onclick="saveLot()" class="btn btn-success">Enregistrer</button>
    </div>
</div>

<div id="zoneGroupeModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h4 class="mb-0">🟡 Zone groupée</h4><div id="zgEtapeInfo" style="font-size:12px;color:#64748b;margin-top:2px;"></div></div>
        <button onclick="closeZoneGroupeModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div class="mb-3">
        <label style="font-weight:600;font-size:13px;">🏷️ Nom ou client</label>
        <div class="client-search-wrap mt-1">
            <input type="text" id="zg_client_search" class="form-control" placeholder="🔍 Chercher client ou saisir un nom..." autocomplete="off">
            <div class="client-dropdown" id="zg_client_dropdown"></div>
        </div>
        <input type="hidden" id="zg_client_id">
        <div id="zg_client_info" style="display:none;margin-top:6px;padding:6px 10px;background:#ecfdf5;border-radius:6px;font-size:12px;color:#065f46;"></div>
    </div>
    <div id="zg_clientExistantInfo" style="display:none;padding:8px 12px;background:#f0f7ff;border-radius:8px;font-size:12px;color:#1e3a5f;margin-bottom:10px;border-left:3px solid #0d6efd;">
        👤 <strong id="zg_clientExistantNom"></strong>
    </div>
    <div id="zg_dossierSelectField" style="display:none;" class="mb-3">
        <label style="font-weight:600;font-size:13px;">📂 Dossier client</label>
        <select id="zg_dossier_id" class="form-control form-control-sm mt-1"><option value="">-- Choisir --</option></select>
    </div>
    <div class="mb-3">
        <label style="font-weight:600;font-size:13px;">Type</label>
        <select id="zg_type" class="form-control" onchange="zgTypeChange()">
            <option value="">-- Choisir --</option>
            <option value="implantation_prevue">Implantation prévue</option>
            <option value="deja_implante">Déjà implanté</option>
            <option value="dossier_technique">Dossier technique</option>
            <option value="morcellement">Morcellement</option>
        </select>
    </div>
    <div id="zg_datePrevueGroup" style="display:none;" class="mb-3"><label style="font-weight:600;font-size:13px;">Date prévue</label><input type="date" id="zg_date_prevue" class="form-control"></div>
    <div id="zg_dateConfirmeeGroup" style="display:none;" class="mb-3"><label style="font-weight:600;font-size:13px;">Date confirmée</label><input type="date" id="zg_date_confirmee" class="form-control"></div>
    <div id="zg_dateMorcellementGroup" style="display:none;" class="mb-3"><label style="font-weight:600;font-size:13px;">Date morcellement</label><input type="date" id="zg_date_morcellement" class="form-control"></div>
    <div id="zg_superficie_info" style="background:#fef9c3;border-radius:8px;padding:8px 12px;font-size:12px;color:#92400e;margin-bottom:12px;display:none;">
        📐 Superficie totale : <strong id="zg_superficie_val">0</strong> m²
    </div>
    <div id="zg_deleteBtn" style="display:none;margin-bottom:10px;">
        <button onclick="supprimerZoneGroupe()" class="btn btn-outline-danger btn-sm w-100">🗑 Supprimer cette zone</button>
    </div>
    <div class="d-flex justify-content-end gap-2">
        <button onclick="closeZoneGroupeModal()" class="btn btn-light">Annuler</button>
        <button onclick="saveZoneGroupe()" class="btn btn-warning" style="color:white;">💾 Enregistrer</button>
    </div>
</div>

<div id="clientPanel">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div><h6 id="cp-name">-</h6><div id="cp-phone" style="color:#64748b;font-size:11px;"></div></div>
        <button onclick="document.getElementById('clientPanel').style.display='none'" style="background:none;border:none;font-size:16px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:8px;border-top:1px solid #f1f5f9;padding-top:8px;">📂 Dossiers client</div>
    <div id="cp-dossiers"></div>
    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:8px;border-top:1px solid #f1f5f9;padding-top:8px;margin-top:8px;">📦 Lots attribués</div>
    <div id="cp-lots"></div>
    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:8px;border-top:1px solid #f1f5f9;padding-top:8px;margin-top:8px;">🚶 Historique visites</div>
    <div id="cp-visites"></div>
</div>

<div id="paiementDossierModal">
    <h5 style="font-weight:700;color:#1e3a5f;margin-bottom:14px;">💰 Paiement — <span id="pay-dossier-nom"></span></h5>
    <div class="mb-2"><label style="font-size:12px;font-weight:600;">Montant (FCFA)</label><input type="number" id="pay-montant" class="form-control form-control-sm"></div>
    <div class="mb-2"><label style="font-size:12px;font-weight:600;">Date</label><input type="date" id="pay-date" class="form-control form-control-sm"></div>
    <div class="mb-3"><label style="font-size:12px;font-weight:600;">Note (optionnel)</label><input type="text" id="pay-note" class="form-control form-control-sm" placeholder="Ex: Versement initial"></div>
    <div class="d-flex justify-content-between">
        <button onclick="closePaiementDossier()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="savePaiementDossier()" class="btn btn-success btn-sm">💾 Enregistrer</button>
    </div>
</div>

<div id="legend-fixe">
    <h6>📌 Légende</h6>
    <div class="leg-item"><div class="leg-dot" style="background:transparent;border:2px dashed #aaa;"></div> Non défini</div>
    <div class="leg-item"><div class="leg-dot" style="background:#faf5ee;border:2px solid #c8a882;"></div> Famille</div>
    <div class="leg-item"><div class="leg-dot" style="background:#0d6efd;"></div> EDEN</div>
    <div class="leg-item"><div class="leg-dot" style="background:#7c3aed;"></div> Implant. prévue</div>
    <div class="leg-item"><div class="leg-dot" style="background:#16a34a;"></div> Déjà implanté</div>
    <div class="leg-item"><div class="leg-dot" style="background:#dc2626;"></div> Dossier technique</div>
    <div class="leg-item"><div class="leg-dot" style="background:#ea580c;"></div> Morcellement</div>
    <div class="leg-item"><div class="leg-dot" style="background:transparent;border:3px solid #f59e0b;"></div> Zone groupée</div>
</div>

@endsection
@section('scripts')
<script>
// ============================================================
// DONNÉES
// ============================================================
const lots         = @json(\App\Models\Lot::with('client')->where('tf_id', $tf->id)->get());
const zonesGroupes = @json(\App\Models\ZoneGroupe::where('tf_id', $tf->id)->get());
const tfId         = "{{ $tf->id }}";
const CSRF         = "{{ csrf_token() }}";

// ============================================================
// COULEURS ALÉATOIRES STABLES PAR ZONE (basées sur l'id)
// ============================================================
const PALETTE_BORDURES = [
    '#e11d48','#7c3aed','#0284c7','#059669','#d97706',
    '#db2777','#4f46e5','#0891b2','#16a34a','#dc2626',
    '#9333ea','#2563eb','#0d9488','#ca8a04','#c026d3',
];
function couleurZone(zgId) {
    return PALETTE_BORDURES[zgId % PALETTE_BORDURES.length];
}

// ============================================================
// ÉTAT
// ============================================================
let currentZone             = null;
let originZone              = null;
let searchTimer             = null;
let zgSearchTimer           = null;
let currentDossierId        = null;
let cachedDossiers          = [];
let zgCachedDossiers        = [];
let currentZoneGroupeId     = null;
let currentZoneGroupePoints = [];
let currentZoneGroupeLotIds = [];
let currentTool             = 'select';
let drawingPoints           = [];
let isDrawing               = false;

// ============================================================
// REFS DOM
// ============================================================
const lotModal              = document.getElementById("lotModal");
const zoneLabel             = document.getElementById("zoneLabel");
const lotType               = document.getElementById("lotType");
const superficie            = document.getElementById("superficie");
const date_prevue           = document.getElementById("date_prevue");
const date_confirmee        = document.getElementById("date_confirmee");
const date_morcellement     = document.getElementById("date_morcellement");
const superficieField       = document.getElementById("superficieField");
const datePrevueGroup       = document.getElementById("datePrevueGroup");
const dateConfirmeeGroup    = document.getElementById("dateConfirmeeGroup");
const dateMorcellementGroup = document.getElementById("dateMorcellementGroup");
const modalOverlay          = document.getElementById("modalOverlay");
const clientSearchField     = document.getElementById("clientSearchField");
const clientSearch          = document.getElementById("client_search");
const clientDropdown        = document.getElementById("client_dropdown");
const selectedClientId      = document.getElementById("selected_client_id");
const dossierSelectField    = document.getElementById("dossierSelectField");
const selectedDossierId     = document.getElementById("selected_dossier_id");
const container             = document.getElementById('tf-map-container');
const mapInner              = document.getElementById('map-inner');
const canvas                = document.getElementById('draw-canvas');
const ctx                   = canvas.getContext('2d');
const ZOOM_KEY              = 'tf_zoom_{{ $tf->id }}';

function getSVG() { return mapInner?.querySelector('svg'); }

// ============================================================
// ZOOM
// ============================================================
function appliquerZoom(val) {
    const scale = val / 100;
    mapInner.style.transform       = `scale(${scale})`;
    mapInner.style.transformOrigin = '0 0';
    document.getElementById('svg-zoom-val').innerText = val + '%';
    redessinerCanvas();
    sessionStorage.setItem(ZOOM_KEY, val);
}
function resetZoom() { document.getElementById('svg-zoom').value = 100; appliquerZoom(100); }
document.getElementById('svg-zoom').addEventListener('input', function() { appliquerZoom(parseInt(this.value)); });

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    const savedZoom = sessionStorage.getItem(ZOOM_KEY);
    const zoomVal   = savedZoom ? parseInt(savedZoom) : 100;
    document.getElementById('svg-zoom').value = zoomVal;
    appliquerZoom(zoomVal);
    syncCanvas();
    initSVG();
    dessinerZonesGroupes();
});

function syncCanvas() {
    const svg = getSVG();
    if (!svg) return;
    const w = svg.getAttribute('width')  || svg.viewBox?.baseVal?.width  || 800;
    const h = svg.getAttribute('height') || svg.viewBox?.baseVal?.height || 600;
    canvas.width        = parseFloat(w);
    canvas.height       = parseFloat(h);
    canvas.style.width  = parseFloat(w) + 'px';
    canvas.style.height = parseFloat(h) + 'px';
}

// ============================================================
// OUTIL
// ============================================================
function setTool(tool) {
    currentTool = tool;
    document.getElementById('toolSelect').classList.toggle('active',     tool === 'select');
    document.getElementById('toolZoneDessin').classList.toggle('active', tool === 'zone');
    container.classList.toggle('zone-drawing', tool === 'zone');
    const hint = document.getElementById('toolHint');
    if (tool === 'zone') {
        hint.innerText = '💡 Cliquez pour placer des points • Cliquez sur le 1er point 🟡 pour fermer';
        canvas.style.pointerEvents = 'auto';
        canvas.style.cursor        = 'crosshair';
    } else {
        hint.innerText             = '';
        canvas.style.pointerEvents = 'none';
        canvas.style.cursor        = 'default';
        annulerDessin();
    }
}

// ============================================================
// COORDONNÉES
// ============================================================
function ecranVersSVG(e) {
    const rect  = canvas.getBoundingClientRect();
    const scale = parseInt(document.getElementById('svg-zoom').value) / 100;
    return { x: (e.clientX - rect.left) / scale, y: (e.clientY - rect.top) / scale };
}

// ============================================================
// DESSIN ZONE
// ============================================================
canvas.addEventListener('click', function(e) {
    if (currentTool !== 'zone') return;
    const pt = ecranVersSVG(e);
    if (drawingPoints.length >= 3) {
        const premier = drawingPoints[0];
        const dist    = Math.hypot(pt.x - premier.x, pt.y - premier.y);
        const seuil   = 15 / (parseInt(document.getElementById('svg-zoom').value) / 100);
        if (dist < seuil) { finaliserDessin(); return; }
    }
    drawingPoints.push(pt);
    isDrawing = true;
    redessinerCanvas();
});

canvas.addEventListener('mousemove', function(e) {
    if (currentTool !== 'zone' || drawingPoints.length === 0) return;
    redessinerCanvas(ecranVersSVG(e));
});

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && currentTool === 'zone') { annulerDessin(); setTool('select'); }
});

function redessinerCanvas(cursorPt) {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    if (drawingPoints.length === 0) return;
    const scale = parseInt(document.getElementById('svg-zoom').value) / 100;
    ctx.save();
    ctx.beginPath();
    ctx.moveTo(drawingPoints[0].x, drawingPoints[0].y);
    drawingPoints.forEach((p, i) => { if (i > 0) ctx.lineTo(p.x, p.y); });
    if (cursorPt) ctx.lineTo(cursorPt.x, cursorPt.y);
    ctx.closePath();
    ctx.fillStyle   = 'rgba(245,158,11,0.15)';
    ctx.fill();
    ctx.strokeStyle = '#f59e0b';
    ctx.lineWidth   = 3 / scale;
    ctx.setLineDash([8 / scale, 4 / scale]);
    ctx.stroke();
    ctx.setLineDash([]);
    drawingPoints.forEach((p, i) => {
        ctx.beginPath();
        ctx.arc(p.x, p.y, i === 0 ? 8 / scale : 5 / scale, 0, Math.PI * 2);
        ctx.fillStyle   = i === 0 ? '#f59e0b' : '#fff';
        ctx.strokeStyle = '#f59e0b';
        ctx.lineWidth   = 2 / scale;
        ctx.fill(); ctx.stroke();
        if (i === 0 && drawingPoints.length >= 3) {
            ctx.fillStyle = '#1e3a5f';
            ctx.font      = `bold ${12/scale}px sans-serif`;
            ctx.textAlign = 'center';
            ctx.fillText('✕', p.x, p.y + 1);
        }
    });
    ctx.restore();
}

function annulerDessin() {
    drawingPoints = []; isDrawing = false;
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

function finaliserDessin() {
    if (drawingPoints.length < 3) { alert('Il faut au moins 3 points.'); return; }
    const lotIdsInclus = trouverLotsInclus(drawingPoints);
    const superfTotale = lots.filter(l => lotIdsInclus.includes(l.id)).reduce((s, l) => s + (parseFloat(l.superficie) || 0), 0);
    currentZoneGroupeId     = null;
    currentZoneGroupePoints = drawingPoints.map(p => ({ x: Math.round(p.x*100)/100, y: Math.round(p.y*100)/100 }));
    currentZoneGroupeLotIds = lotIdsInclus;
    document.getElementById('zg_superficie_info').style.display     = 'block';
    document.getElementById('zg_superficie_val').innerText           = superfTotale.toLocaleString('fr-FR');
    document.getElementById('zg_deleteBtn').style.display            = 'none';
    document.getElementById('zgEtapeInfo').innerText                  = '';
    document.getElementById('zg_type').value                          = '';
    document.getElementById('zg_type').disabled                       = false;
    Array.from(document.getElementById('zg_type').options).forEach(o => o.disabled = false);
    document.getElementById('zg_client_id').value                     = '';
    document.getElementById('zg_client_search').value                 = '';
    document.getElementById('zg_client_search').style.display         = 'block';
    document.getElementById('zg_client_info').style.display           = 'none';
    document.getElementById('zg_clientExistantInfo').style.display    = 'none';
    document.getElementById('zg_dossierSelectField').style.display    = 'none';
    document.getElementById('zg_date_prevue').value                   = '';
    document.getElementById('zg_date_confirmee').value                = '';
    document.getElementById('zg_date_morcellement').value             = '';
    zgTypeChange();
    modalOverlay.style.display = 'block';
    document.getElementById('zoneGroupeModal').style.display = 'block';
    setTool('select');
}

// ============================================================
// POINT DANS POLYGONE
// ============================================================
function pointInPolygon(pt, poly) {
    let inside = false;
    for (let i = 0, j = poly.length - 1; i < poly.length; j = i++) {
        const xi = poly[i].x, yi = poly[i].y, xj = poly[j].x, yj = poly[j].y;
        if (((yi > pt.y) !== (yj > pt.y)) && (pt.x < (xj - xi) * (pt.y - yi) / (yj - yi) + xi)) inside = !inside;
    }
    return inside;
}

function trouverLotsInclus(poly) {
    const svg = getSVG(); if (!svg) return [];
    const inclus = [];
    svg.querySelectorAll('path').forEach(el => {
        try {
            const bbox = el.getBBox();
            const cx = bbox.x + bbox.width/2, cy = bbox.y + bbox.height/2;
            if (pointInPolygon({x:cx,y:cy}, poly)) {
                const zoneId = (el.getAttribute('id')||'').trim().toLowerCase();
                const lot = lots.find(l => (l.code||'').trim().toLowerCase() === zoneId);
                if (lot) inclus.push(lot.id);
            }
        } catch(e) {}
    });
    return inclus;
}

// ============================================================
// UTILITAIRES SVG TEXTE
// ============================================================
function mkText(x, y, txt, fs, fw, fill, sw) {
    const t = document.createElementNS('http://www.w3.org/2000/svg','text');
    t.setAttribute('x', x); t.setAttribute('y', y);
    t.setAttribute('text-anchor','middle'); t.setAttribute('dominant-baseline','central');
    t.setAttribute('font-size', fs); t.setAttribute('font-weight', fw);
    t.setAttribute('fill', fill); t.setAttribute('stroke','#fff');
    t.setAttribute('stroke-width', sw); t.setAttribute('paint-order','stroke');
    t.textContent = txt;
    return t;
}

// Crée des éléments <text> avec retour à la ligne automatique dans un bbox
function mkTextMultiline(svg, cx, cyStart, txt, fontSize, fontWeight, fill, strokeW, maxWidth) {
    const mots = txt.split(' ');
    const lignes = [];
    let ligne = '';
    // Estimation grossière : 0.6 * fontSize par caractère
    const charW = parseFloat(fontSize) * 0.6;
    mots.forEach(mot => {
        const test = ligne ? ligne + ' ' + mot : mot;
        if (test.length * charW > maxWidth && ligne) {
            lignes.push(ligne);
            ligne = mot;
        } else {
            ligne = test;
        }
    });
    if (ligne) lignes.push(ligne);
    const lineH = parseFloat(fontSize) * 1.3;
    const totalH = lignes.length * lineH;
    let y = cyStart - totalH / 2 + lineH / 2;
    const g = document.createElementNS('http://www.w3.org/2000/svg','g');
    g.style.pointerEvents = 'none';
    lignes.forEach(l => {
        g.appendChild(mkText(cx, y, l, fontSize, fontWeight, fill, strokeW));
        y += lineH;
    });
    return { g, hauteur: totalH };
}

// ============================================================
// DESSINER LES ZONES GROUPES
// ============================================================
function dessinerZonesGroupes() {
    const svg = getSVG(); if (!svg) return;
    svg.querySelectorAll('.zone-groupe-el').forEach(el => el.remove());

    zonesGroupes.forEach(zg => {
        if (!zg.points || zg.points.length < 3) return;

        const couleur   = couleurZone(zg.id);
        const ptsStr    = zg.points.map(p => `${p.x},${p.y}`).join(' ');

        // Fond : transparent si pas de type, couleur du type sinon
        const fillColor = zg.type ? hexToRgba(getColor(zg.type), 0.22) : 'transparent';

        const poly = document.createElementNS('http://www.w3.org/2000/svg','polygon');
        poly.setAttribute('points', ptsStr);
        poly.setAttribute('fill', fillColor);
        poly.setAttribute('stroke', couleur);
        poly.setAttribute('stroke-width', '5');
        poly.setAttribute('stroke-linejoin','round');
        poly.style.cursor = 'pointer';
        poly.classList.add('zone-groupe-el');
        poly.dataset.zgId = zg.id;
        poly.addEventListener('click', function(e) { e.stopPropagation(); ouvrirZoneGroupeExistante(zg); });
        svg.appendChild(poly);

        // Calculer le bbox du polygone
        const xs = zg.points.map(p => p.x), ys = zg.points.map(p => p.y);
        const minX = Math.min(...xs), maxX = Math.max(...xs);
        const minY = Math.min(...ys), maxY = Math.max(...ys);
        const cx   = (minX + maxX) / 2;
        const largeur = maxX - minX;
        const hauteurZone = maxY - minY;

        const nomTxt  = zg.owner_name || zg.nom || '';
        const supTxt  = zg.superficie_totale ? parseFloat(zg.superficie_totale).toLocaleString('fr-FR') + ' m²' : '';
        let   dateTxt = '';
        if (zg.type === 'implantation_prevue' && zg.date_prevue)       dateTxt = formatDate(zg.date_prevue);
        else if (zg.type === 'deja_implante'  && zg.date_confirmee)    dateTxt = formatDate(zg.date_confirmee);
        else if (zg.type === 'morcellement'   && zg.date_morcellement) dateTxt = formatDate(zg.date_morcellement);

        // Taille de police adaptée à la zone (min 10, max 18)
        const fontSize = Math.min(18, Math.max(10, Math.floor(largeur / 10)));

        // Calculer la hauteur totale des blocs de texte
        const nbLignesNom = nomTxt ? Math.ceil(nomTxt.length * fontSize * 0.6 / largeur) || 1 : 0;
        const ligneH      = fontSize * 1.3;
        const totalTxtH   = (nbLignesNom * ligneH) + (supTxt ? ligneH : 0) + (dateTxt ? ligneH : 0);
        let   yOff        = (minY + maxY) / 2 - totalTxtH / 2;

        const gAll = document.createElementNS('http://www.w3.org/2000/svg','g');
        gAll.style.pointerEvents = 'none';
        gAll.classList.add('zone-groupe-el');

        if (nomTxt) {
            const { g: gNom, hauteur: hNom } = mkTextMultiline(svg, cx, yOff + (nbLignesNom * ligneH) / 2, nomTxt, String(fontSize), '800', couleur, '3', largeur - 10);
            gAll.appendChild(gNom);
            yOff += hNom + 4;
        }
        if (supTxt) {
            gAll.appendChild(mkText(cx, yOff + ligneH/2, supTxt, String(Math.max(9, fontSize - 2)), '700', '#1d4ed8', '2.5'));
            yOff += ligneH + 2;
        }
        if (dateTxt) {
            gAll.appendChild(mkText(cx, yOff + ligneH/2, dateTxt, String(Math.max(8, fontSize - 3)), '500', '#374151', '2'));
        }

        svg.appendChild(gAll);
    });
}

// ============================================================
// COULEURS
// ============================================================
function getColor(type) {
    return { implantation_prevue:'#7c3aed', deja_implante:'#16a34a', dossier_technique:'#dc2626', morcellement:'#ea580c' }[type] || '#0d6efd';
}
function hexToRgba(hex, alpha) {
    if (!hex || !hex.startsWith('#')) return `rgba(245,158,11,${alpha})`;
    const r = parseInt(hex.slice(1,3),16), g = parseInt(hex.slice(3,5),16), b = parseInt(hex.slice(5,7),16);
    return `rgba(${r},${g},${b},${alpha})`;
}
function formatDate(s) {
    if (!s) return '';
    const d = new Date(s);
    return isNaN(d) ? s : d.toLocaleDateString('fr-FR',{day:'2-digit',month:'2-digit',year:'numeric'});
}

// ============================================================
// INIT SVG
// ============================================================
function initSVG() {
    const svg = getSVG(); if (!svg) return;

    // Lots masqués : encadrés par une zone AVEC type
    const lotIdsMasques = new Set();
    zonesGroupes.forEach(zg => {
        if (zg.type && zg.lot_ids && zg.lot_ids.length > 0)
            zg.lot_ids.forEach(id => lotIdsMasques.add(id));
    });

    svg.querySelectorAll('path').forEach(el => {
        const zoneId = (el.getAttribute('id')||'').trim().toLowerCase();
        const lot    = lots.find(l => (l.code||'').trim().toLowerCase() === zoneId);

        el.style.cursor      = 'pointer';
        el.style.strokeWidth = '2.5px';

        // Lot masqué par une zone typée → transparent
        if (lot && lotIdsMasques.has(lot.id)) {
            el.style.stroke          = 'transparent';
            el.style.fill            = 'transparent';
            el.style.strokeDasharray = '';
            el.addEventListener('click', function(e) {
                if (currentTool !== 'select') return;
                openModal(zoneId, lot);
            });
            return;
        }

        if (!lot || !lot.origine) {
            el.style.stroke = '#bbb'; el.style.fill = 'transparent'; el.style.strokeDasharray = '2,2';
        } else if (lot.origine === 'famille') {
            el.style.stroke = '#c8a882'; el.style.fill = 'rgba(250,245,238,0.35)'; el.style.strokeDasharray = '';
        } else if (lot.origine === 'eden') {
            el.style.strokeDasharray = '';
            if (!lot.type) {
                el.style.stroke = '#0d6efd'; el.style.fill = 'rgba(13,110,253,0.25)';
            } else {
                const color = getColor(lot.type);
                el.style.stroke = color; el.style.fill = hexToRgba(color, 0.40);
                afficherInfoSurLot(svg, el, lot);
            }
        }

        el.addEventListener('click', function(e) {
            if (currentTool !== 'select') return;
            if (e.ctrlKey)             { openOriginModal(zoneId, lot); return; }
            if (!lot || !lot.origine)  { openOriginModal(zoneId, lot); return; }
            if (lot.origine === 'famille') { openOriginModal(zoneId, lot); return; }
            if (lot.origine === 'eden')    { openModal(zoneId, lot); }
        });
    });
}

// Afficher nom + date sur un lot individuel avec texte adapté à la taille
function afficherInfoSurLot(svg, pathEl, lot) {
    try {
        const bbox = pathEl.getBBox();
        const cx = bbox.x + bbox.width/2, cy = bbox.y + bbox.height/2;
        const largeur = bbox.width;

        let dateTxt = '';
        if (lot.type === 'implantation_prevue' && lot.date_prevue)       dateTxt = formatDate(lot.date_prevue);
        else if (lot.type === 'deja_implante'  && lot.date_confirmee)    dateTxt = formatDate(lot.date_confirmee);
        else if (lot.type === 'morcellement'   && lot.date_morcellement) dateTxt = formatDate(lot.date_morcellement);
        const nomTxt = lot.owner_name || '';
        if (!nomTxt && !dateTxt) return;

        const fontSize = Math.min(13, Math.max(7, Math.floor(largeur / 8)));
        const g = document.createElementNS('http://www.w3.org/2000/svg','g');
        g.style.pointerEvents = 'none';

        let yOff = cy - (dateTxt ? fontSize * 0.7 : 0);

        if (nomTxt) {
            const { g: gNom } = mkTextMultiline(svg, cx, yOff, nomTxt, String(fontSize), '700', '#1e293b', '2.5', largeur - 4);
            g.appendChild(gNom);
            yOff += fontSize * 1.4;
        }
        if (dateTxt) {
            g.appendChild(mkText(cx, yOff, dateTxt, String(Math.max(6, fontSize - 1)), '500', '#374151', '2'));
        }
        svg.appendChild(g);
    } catch(e) {}
}

// ============================================================
// OUVRIR ZONE GROUPE EXISTANTE
// ============================================================
function ouvrirZoneGroupeExistante(zg) {
    currentZoneGroupeId     = zg.id;
    currentZoneGroupePoints = zg.points;
    currentZoneGroupeLotIds = zg.lot_ids || [];

    const etapeSuivante = getEtapeSuivante({ type: zg.type });
    const etapeInfoEl   = document.getElementById('zgEtapeInfo');

    if (etapeSuivante === null) {
        etapeInfoEl.innerHTML = '<span style="color:#28a745;">✅ Toutes les étapes complètes</span>';
    } else if (zg.type) {
        etapeInfoEl.innerHTML = `Étape : <strong>${etapeLabels[zg.type]}</strong> <span class="badge-next-step">→ ${etapeLabels[etapeSuivante]}</span>`;
    } else {
        etapeInfoEl.innerText = 'Aucune étape définie';
    }

    const superfTotale = lots.filter(l => (currentZoneGroupeLotIds||[]).includes(l.id))
        .reduce((s,l) => s + (parseFloat(l.superficie)||0), 0);
    document.getElementById('zg_superficie_info').style.display = 'block';
    document.getElementById('zg_superficie_val').innerText = (superfTotale || zg.superficie_totale || 0).toLocaleString('fr-FR');

    if (zg.client_id) {
        document.getElementById('zg_clientExistantInfo').style.display = 'block';
        document.getElementById('zg_clientExistantNom').innerText = zg.owner_name || '-';
        document.getElementById('zg_client_search').style.display  = 'none';
        document.getElementById('zg_client_id').value              = zg.client_id;
        chargerDossiersZG(zg.client_id, zg.dossier_client_id);
        loadClientPanel(zg.client_id);
    } else {
        document.getElementById('zg_clientExistantInfo').style.display = 'none';
        document.getElementById('zg_client_search').style.display       = 'block';
        document.getElementById('zg_client_search').value               = zg.nom || '';
        document.getElementById('zg_client_id').value                   = '';
        document.getElementById('zg_dossierSelectField').style.display  = 'none';
    }
    document.getElementById('zg_client_info').style.display = 'none';

    const zgTypeEl = document.getElementById('zg_type');
    zgTypeEl.disabled = false;
    Array.from(zgTypeEl.options).forEach(o => o.disabled = false);

    if (etapeSuivante === null) {
        zgTypeEl.disabled = true; zgTypeEl.value = zg.type || '';
    } else if (zg.type) {
        zgTypeEl.value = etapeSuivante;
        const ordre = ['implantation_prevue','deja_implante','dossier_technique','morcellement'];
        Array.from(zgTypeEl.options).forEach(opt => {
            if (opt.value === '') { opt.disabled = false; return; }
            opt.disabled = (ordre.indexOf(opt.value) !== ordre.indexOf(zg.type) + 1);
        });
    } else {
        zgTypeEl.value = 'implantation_prevue';
        Array.from(zgTypeEl.options).forEach(opt => {
            if (opt.value === '') { opt.disabled = false; return; }
            opt.disabled = opt.value !== 'implantation_prevue';
        });
    }

    document.getElementById('zg_date_prevue').value       = zg.date_prevue       ? zg.date_prevue.substring(0,10)       : '';
    document.getElementById('zg_date_confirmee').value    = zg.date_confirmee    ? zg.date_confirmee.substring(0,10)    : '';
    document.getElementById('zg_date_morcellement').value = zg.date_morcellement ? zg.date_morcellement.substring(0,10) : '';

    zgTypeChange();
    document.getElementById('zg_deleteBtn').style.display = 'block';
    modalOverlay.style.display = 'block';
    document.getElementById('zoneGroupeModal').style.display = 'block';
}

// ============================================================
// RECHERCHE CLIENT — zone groupe
// ============================================================
document.getElementById('zg_client_search').addEventListener('input', function() {
    const q = this.value.trim(), dd = document.getElementById('zg_client_dropdown');
    clearTimeout(zgSearchTimer);
    if (q.length < 1) { dd.style.display = 'none'; return; }
    zgSearchTimer = setTimeout(() => {
        fetch(`/admin/lots/client-search?q=${encodeURIComponent(q)}`).then(r => r.json()).then(clients => {
            if (!clients.length) { dd.style.display = 'none'; return; }
            dd.innerHTML = clients.map(c =>
                `<div class="client-option" data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone??''}">
                    <strong>${c.name}</strong><small>${c.phone??''}</small>
                </div>`).join('');
            dd.style.display = 'block';
            dd.querySelectorAll('.client-option').forEach(opt => {
                opt.addEventListener('click', function() {
                    document.getElementById('zg_client_id').value    = this.dataset.id;
                    document.getElementById('zg_client_search').value = this.dataset.name;
                    dd.style.display = 'none';
                    const info = document.getElementById('zg_client_info');
                    info.innerHTML = `✅ <strong>${this.dataset.name}</strong> ${this.dataset.phone}`;
                    info.style.display = 'block';
                    chargerDossiersZG(this.dataset.id, null);
                    loadClientPanel(this.dataset.id);
                });
            });
        });
    }, 250);
});

function chargerDossiersZG(clientId, selectedId) {
    fetch(`/admin/lots/client-panel/${clientId}`).then(r => r.json()).then(data => {
        zgCachedDossiers = data.dossiers || [];
        const sel = document.getElementById('zg_dossier_id');
        sel.innerHTML = '<option value="">-- Choisir un dossier --</option>';
        zgCachedDossiers.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.innerText = d.nom + (d.prix_ref !== '-' ? ' — ' + d.prix_ref + ' FCFA' : '');
            if (selectedId && d.id == selectedId) opt.selected = true;
            sel.appendChild(opt);
        });
        document.getElementById('zg_dossierSelectField').style.display = 'block';
    });
}

function zgTypeChange() {
    const type = document.getElementById('zg_type').value;
    document.getElementById('zg_datePrevueGroup').style.display       = type === 'implantation_prevue' ? 'block' : 'none';
    document.getElementById('zg_dateConfirmeeGroup').style.display    = (type === 'deja_implante' || type === 'dossier_technique') ? 'block' : 'none';
    document.getElementById('zg_dateMorcellementGroup').style.display = type === 'morcellement' ? 'block' : 'none';
}

// ============================================================
// SAVE / DELETE ZONE GROUPE
// ============================================================
function saveZoneGroupe() {
    if (!currentZoneGroupePoints || currentZoneGroupePoints.length < 3) {
        alert('Aucune zone dessinée (minimum 3 points requis).');
        return;
    }
    const clientId = document.getElementById('zg_client_id').value || null;
    const nom      = document.getElementById('zg_client_search').value.trim() || null;
    const type     = document.getElementById('zg_type').value || null;
    const payload  = {
        tf_id: tfId, client_id: clientId,
        dossier_client_id: document.getElementById('zg_dossier_id').value || null,
        nom, points: currentZoneGroupePoints, lot_ids: currentZoneGroupeLotIds, type,
        date_prevue:       document.getElementById('zg_date_prevue').value       || null,
        date_confirmee:    document.getElementById('zg_date_confirmee').value    || null,
        date_morcellement: document.getElementById('zg_date_morcellement').value || null,
    };
    const url    = currentZoneGroupeId ? `/admin/zone-groupes/${currentZoneGroupeId}` : '/admin/zone-groupes';
    const method = currentZoneGroupeId ? 'PUT' : 'POST';
    fetch(url, { method, headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF}, body:JSON.stringify(payload) })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.message || 'Erreur'); })
    .catch(e => alert('Erreur réseau : ' + e.message));
}

function supprimerZoneGroupe() {
    if (!confirm('Supprimer cette zone ?')) return;
    fetch(`/admin/zone-groupes/${currentZoneGroupeId}`, { method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF} })
    .then(r => r.json()).then(data => { if (data.success) location.reload(); });
}

function closeZoneGroupeModal() {
    document.getElementById('zoneGroupeModal').style.display = 'none';
    modalOverlay.style.display = 'none';
    document.getElementById('clientPanel').style.display = 'none';
    document.getElementById('zg_type').disabled = false;
    Array.from(document.getElementById('zg_type').options).forEach(o => o.disabled = false);
    annulerDessin();
}

// ============================================================
// AUTOCOMPLETE CLIENT — modal lot
// ============================================================
clientSearch.addEventListener('input', function() {
    const q = this.value.trim();
    selectedClientId.value = '';
    document.getElementById('client_selected_info').style.display = 'none';
    document.getElementById('clientPanel').style.display = 'none';
    resetDossierSelect();
    clearTimeout(searchTimer);
    if (q.length < 1) { clientDropdown.style.display = 'none'; return; }
    searchTimer = setTimeout(() => {
        fetch(`/admin/lots/client-search?q=${encodeURIComponent(q)}`).then(r => r.json()).then(clients => {
            if (!clients.length) { clientDropdown.style.display = 'none'; return; }
            clientDropdown.innerHTML = clients.map(c =>
                `<div class="client-option" data-id="${c.id}" data-name="${c.name}" data-phone="${c.phone??''}">
                    <strong>${c.name}</strong><small>${c.phone??''}</small>
                </div>`).join('');
            clientDropdown.style.display = 'block';
            clientDropdown.querySelectorAll('.client-option').forEach(opt => {
                opt.addEventListener('click', function() {
                    selectedClientId.value = this.dataset.id;
                    clientSearch.value     = this.dataset.name;
                    clientDropdown.style.display = 'none';
                    const info = document.getElementById('client_selected_info');
                    info.innerHTML = `✅ <strong>${this.dataset.name}</strong> ${this.dataset.phone}`;
                    info.style.display = 'block';
                    loadClientPanel(this.dataset.id);
                });
            });
        });
    }, 250);
});

document.addEventListener('click', e => {
    if (!e.target.closest('.client-search-wrap') && !e.target.closest('#zg_client_dropdown'))
        document.getElementById('zg_client_dropdown').style.display = 'none';
    if (!e.target.closest('.client-search-wrap') && !e.target.closest('#client_dropdown'))
        clientDropdown.style.display = 'none';
});

function resetDossierSelect() {
    cachedDossiers = [];
    selectedDossierId.innerHTML = '<option value="">-- Choisir un dossier --</option>';
    dossierSelectField.style.display = 'none';
    document.getElementById('dossier_selected_info').style.display = 'none';
}

// ============================================================
// PANNEAU CLIENT
// ============================================================
function loadClientPanel(clientId) {
    fetch(`/admin/lots/client-panel/${clientId}`).then(r => r.json()).then(data => {
        document.getElementById('cp-name').innerText  = data.name;
        document.getElementById('cp-phone').innerText = '📞 ' + (data.phone || '-');
        cachedDossiers = data.dossiers || [];

        selectedDossierId.innerHTML = '<option value="">-- Choisir un dossier --</option>';
        cachedDossiers.forEach(d => {
            const opt = document.createElement('option');
            opt.value = d.id;
            opt.innerText = d.nom + (d.prix_ref !== '-' ? ' — ' + d.prix_ref + ' FCFA' : '');
            selectedDossierId.appendChild(opt);
        });
        if (cachedDossiers.length > 0) {
            dossierSelectField.style.display = 'block';
            selectedDossierId.value = cachedDossiers[0].id;
            updateDossierInfo(cachedDossiers[0]);
        }

        document.getElementById('cp-dossiers').innerHTML = !cachedDossiers.length
            ? '<div style="color:#94a3b8;font-size:11px;">Aucun dossier</div>'
            : cachedDossiers.map(d => {
                const progColor = d.progression < 40 ? '#dc3545' : (d.progression < 75 ? '#fd7e14' : '#28a745');
                const pays = d.paiements.map(p =>
                    `<div class="cp-pay-row"><span>${p.date}${p.note?' — '+p.note:''}</span><span class="cp-pay-amt">${p.montant} FCFA</span></div>`
                ).join('');
                return `<div class="cp-dossier-section">
                    <div style="font-weight:700;font-size:12px;color:#1e3a5f;margin-bottom:4px;">📂 ${d.nom}</div>
                    <div style="font-size:11px;color:#64748b;line-height:1.8;">
                        🔗 ${d.facilitateur??'-'} | 🧑‍💼 ${d.commercial??'-'}<br>
                        🏢 ${d.grand_site??'-'} | 🧭 ${d.direction??'-'}<br>
                        📐 ${d.superficie??'-'} m² | 💰 ${d.prix_ref??'-'} FCFA
                    </div>
                    <div style="margin-top:8px;margin-bottom:4px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Paiements</div>
                    ${pays || '<div style="color:#94a3b8;font-size:11px;">Aucun paiement</div>'}
                    <div style="display:flex;justify-content:space-between;margin-top:6px;font-size:11px;">
                        <span>Total : <strong style="color:#28a745;">${d.total_paye} FCFA</strong></span>
                        <span>Reste : <strong style="color:#dc3545;">${d.reste} FCFA</strong></span>
                    </div>
                    <div class="cp-prog-wrap" style="margin-top:6px;"><div class="cp-prog-fill" style="width:${d.progression}%;background:${progColor};"></div></div>
                    <div style="font-size:10px;color:${progColor};font-weight:600;text-align:right;">${d.progression}%</div>
                    <button class="btn-pay-small" onclick="openPaiementDossier(${d.id},'${d.nom.replace(/'/g,"\\'")}')">+ Ajouter un paiement</button>
                </div>`;
            }).join('');

        document.getElementById('cp-lots').innerHTML = !data.lots.length
            ? '<div style="color:#94a3b8;font-size:11px;">Aucun lot</div>'
            : data.lots.map(l => {
                const progColor = l.prog < 40 ? '#dc3545' : (l.prog < 75 ? '#fd7e14' : '#28a745');
                return `<div class="cp-lot-card" style="border-left-color:${l.color};">
                    <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                        <strong style="font-size:12px;">${l.code}</strong>
                        <span style="font-size:10px;color:${l.color};font-weight:600;">${l.type??'Sans type'}</span>
                    </div>
                    ${l.date_prevue    ? `<div style="font-size:10px;color:#64748b;">📅 ${l.date_prevue}</div>`    : ''}
                    ${l.date_confirmee ? `<div style="font-size:10px;color:#64748b;">✅ ${l.date_confirmee}</div>` : ''}
                    ${l.prog > 0 ? `<div class="cp-prog-wrap mt-1"><div class="cp-prog-fill" style="width:${l.prog}%;background:${progColor};"></div></div>
                    <div style="font-size:10px;text-align:right;color:${progColor};font-weight:600;">${l.prog}%</div>` : ''}
                    ${l.dossier_url ? `<a href="${l.dossier_url}" style="display:block;margin-top:6px;background:#1d4ed8;color:white;border-radius:6px;padding:3px 8px;font-size:10px;font-weight:600;text-decoration:none;text-align:center;">📁 Voir le dossier technique</a>` : ''}
                </div>`;
            }).join('');

        // Visites
        fetch(`/admin/lots/client-visites/${clientId}`)
        .then(r => r.json())
        .then(visites => {
            if (!visites.length) {
                document.getElementById('cp-visites').innerHTML = '<div style="color:#94a3b8;font-size:11px;">Aucune visite enregistrée</div>';
                return;
            }
            document.getElementById('cp-visites').innerHTML = visites.slice(0,5).map(v =>
                `<div style="display:flex;justify-content:space-between;padding:4px 0;border-bottom:1px solid #f1f5f9;font-size:11px;">
                    <span>📅 ${v.date} ${v.type === 'client' ? '💳' : ''}</span>
                    <span style="color:#64748b;">${v.heure_arrivee??'--'} → ${v.heure_depart??'--'}</span>
                </div>`
            ).join('') + (visites.length > 5 ? `<div style="font-size:10px;color:#94a3b8;text-align:right;">${visites.length} visites au total</div>` : '');
        }).catch(() => {
            document.getElementById('cp-visites').innerHTML = '<div style="color:#94a3b8;font-size:11px;">—</div>';
        });

        positionClientPanel();
        document.getElementById('clientPanel').style.display = 'block';
    });
}

function updateDossierInfo(d) {
    if (!d) { document.getElementById('dossier_selected_info').style.display = 'none'; return; }
    const progColor = d.progression < 40 ? '#dc3545' : (d.progression < 75 ? '#fd7e14' : '#28a745');
    const info = document.getElementById('dossier_selected_info');
    info.innerHTML = `
        <div style="display:flex;justify-content:space-between;margin-bottom:3px;"><span>💰 Payé</span><strong style="color:#28a745;">${d.total_paye} FCFA</strong></div>
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;"><span>⏳ Reste</span><strong style="color:#dc3545;">${d.reste} FCFA</strong></div>
        <div style="height:6px;background:#e2e8f0;border-radius:3px;"><div style="width:${d.progression}%;height:100%;background:${progColor};border-radius:3px;"></div></div>
        <div style="font-size:10px;text-align:right;color:${progColor};font-weight:600;">${d.progression}%</div>`;
    info.style.display = 'block';
}

selectedDossierId.addEventListener('change', function() {
    updateDossierInfo(cachedDossiers.find(x => x.id == this.value) || null);
});

function positionClientPanel() {
    const modal = document.getElementById('lotModal'), panel = document.getElementById('clientPanel');
    if (modal.style.display === 'none') return;
    const rect = modal.getBoundingClientRect(), panelW = 310;
    let left = rect.right + 14;
    if (left + panelW > window.innerWidth) left = rect.left - panelW - 14;
    panel.style.left = Math.max(4, left) + 'px';
}

// ============================================================
// PAIEMENT
// ============================================================
function openPaiementDossier(dossierId, nom) {
    currentDossierId = dossierId;
    document.getElementById('pay-dossier-nom').innerText = nom;
    document.getElementById('pay-montant').value = '';
    document.getElementById('pay-date').value    = new Date().toISOString().split('T')[0];
    document.getElementById('pay-note').value    = '';
    document.getElementById('paiementDossierModal').style.display = 'block';
}
function closePaiementDossier() { document.getElementById('paiementDossierModal').style.display = 'none'; }
function savePaiementDossier() {
    const montant = document.getElementById('pay-montant').value;
    const date    = document.getElementById('pay-date').value;
    const note    = document.getElementById('pay-note').value;
    if (!montant || !date) { alert('Montant et date obligatoires.'); return; }
    fetch(`/admin/paiements-dossier/${currentDossierId}`, {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body:JSON.stringify({montant,date_paiement:date,note})
    }).then(r => r.json()).then(data => {
        if (data.success) {
            closePaiementDossier();
            const cid = selectedClientId.value || document.getElementById('zg_client_id').value;
            if (cid) loadClientPanel(cid); else location.reload();
        } else alert(data.message || 'Erreur');
    });
}

// ============================================================
// ÉTAPES
// ============================================================
function getEtapeSuivante(lot) {
    if (!lot || !lot.type) return 'implantation_prevue';
    if (lot.type === 'implantation_prevue') return 'deja_implante';
    if (lot.type === 'deja_implante')       return 'dossier_technique';
    if (lot.type === 'dossier_technique')   return 'morcellement';
    return null;
}
const etapeLabels = {
    implantation_prevue:'Implantation prévue', deja_implante:'Déjà implanté',
    dossier_technique:'Dossier technique',     morcellement:'Morcellement',
};

// ============================================================
// TYPE LISTENER (modal lot)
// ============================================================
lotType.addEventListener('change', function() {
    const type = this.value;
    [datePrevueGroup,dateConfirmeeGroup,dateMorcellementGroup,superficieField].forEach(el => el.style.display='none');
    if (type === 'implantation_prevue') { datePrevueGroup.style.display='block'; superficieField.style.display='block'; }
    if (type === 'deja_implante')       { dateConfirmeeGroup.style.display='block'; superficieField.style.display='block'; }
    if (type === 'dossier_technique')   { dateConfirmeeGroup.style.display='block'; }
    if (type === 'morcellement')        { dateMorcellementGroup.style.display='block'; superficieField.style.display='block'; }
});

// ============================================================
// MODALS LOT
// ============================================================
function openOriginModal(zoneId, lot) {
    originZone = { zoneId, lotId: lot?lot.id:null };
    document.getElementById('originZoneLabel').innerText = zoneId.toUpperCase();
    document.getElementById('origine_superficie').value  = lot?.superficie || '';
    modalOverlay.style.display = 'block';
    document.getElementById('originModal').style.display = 'block';
}

function openModal(zoneId, lot = null) {
    currentZone = { zoneId, tfId, lotId: lot?lot.id:null };
    lotModal.style.left = '30%'; lotModal.style.transform = 'translate(-50%,-50%)';
    zoneLabel.innerText     = zoneId.toUpperCase();
    superficie.value        = lot?.superficie        || '';
    date_prevue.value       = lot?.date_prevue       || '';
    date_confirmee.value    = lot?.date_confirmee    || '';
    date_morcellement.value = lot?.date_morcellement || '';
    resetDossierSelect();

    const etapeSuivante = getEtapeSuivante(lot);
    const aDejaClient   = !!lot?.client_id;
    const clientExistEl = document.getElementById('clientExistantInfo');
    clientSearchField.style.display = 'none';
    clientExistEl.style.display     = 'none';
    document.getElementById('client_selected_info').style.display = 'none';
    document.getElementById('clientPanel').style.display = 'none';
    // Afficher le bouton supprimer uniquement si le lot existe
document.getElementById('supprimerLotBtn').style.display = (lot && lot.id) ? 'block' : 'none';
    selectedClientId.value = ''; clientSearch.value = '';

    const etapeInfoEl = document.getElementById('lotEtapeInfo');
    if (etapeSuivante === null) {
        etapeInfoEl.innerHTML = '<span style="color:#28a745;">✅ Toutes les étapes sont complètes</span>';
        lotType.value = ''; lotType.disabled = true;
    } else {
        lotType.disabled = false;
        if (lot?.type) {
            etapeInfoEl.innerHTML = `Étape actuelle : <strong>${etapeLabels[lot.type]}</strong> <span class="badge-next-step">→ ${etapeLabels[etapeSuivante]}</span>`;
            lotType.value = etapeSuivante;
            const ordre = ['implantation_prevue','deja_implante','dossier_technique','morcellement'];
            Array.from(lotType.options).forEach(opt => {
                if (opt.value === '') { opt.disabled = false; return; }
                opt.disabled = (ordre.indexOf(opt.value) !== ordre.indexOf(lot.type) + 1);
            });
        } else {
            etapeInfoEl.innerHTML = `<span style="color:#6c757d;">Première étape : <strong>Implantation prévue</strong></span>`;
            lotType.value = 'implantation_prevue';
            Array.from(lotType.options).forEach(opt => {
                if (opt.value === '') { opt.disabled = false; return; }
                opt.disabled = opt.value !== 'implantation_prevue';
            });
        }
    }

    if (aDejaClient) {
        document.getElementById('clientExistantNom').innerText   = lot.client?.name ?? '';
        document.getElementById('clientExistantPhone').innerText = lot.client?.phone ? ` (${lot.client.phone})` : '';
        clientExistEl.style.display = 'block';
        selectedClientId.value      = lot.client_id;
        loadClientPanel(lot.client_id);
    } else if (!lot?.type) {
        clientSearchField.style.display = 'block';
    }

    modalOverlay.style.display = 'block';
    lotModal.style.display     = 'block';
    lotType.dispatchEvent(new Event('change'));
}

function closeModal() {
    lotModal.style.display = 'none'; modalOverlay.style.display = 'none';
    document.getElementById('clientPanel').style.display = 'none';
    resetDossierSelect(); closePaiementDossier();
    Array.from(lotType.options).forEach(opt => opt.disabled = false);
    lotType.disabled = false;
}
function closeAllModals() {
    document.getElementById('originModal').style.display = 'none';
    closeModal(); closeZoneGroupeModal();
}

// ============================================================
// SET ORIGINE
// ============================================================
function setOrigin(value) {
    const sup = document.getElementById('origine_superficie').value;
    if (!sup || parseFloat(sup) <= 0) { alert('Superficie obligatoire.'); return; }
    fetch('/admin/lots/set-origin', {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body:JSON.stringify({ svg_id:originZone.zoneId, tf_id:tfId, origine:value, superficie:sup })
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert(data.message||'Erreur'); });
}

// ============================================================
// SAVE LOT
// ============================================================
function saveLot() {
    const url = currentZone.lotId ? `/admin/lots/${currentZone.lotId}` : '/admin/lots/store';
    fetch(url, {
        method:'POST', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body:JSON.stringify({
            _method: currentZone.lotId ? 'PUT' : 'POST', tf_id:currentZone.tfId,
            svg_id:currentZone.zoneId, code:zoneLabel.innerText.toLowerCase(),
            type:lotType.value, client_id:selectedClientId.value||null,
            dossier_client_id:selectedDossierId.value||null,
            superficie:superficie.value, date_prevue:date_prevue.value,
            date_confirmee:date_confirmee.value, date_morcellement:date_morcellement.value,
        })
    }).then(r => r.json()).then(data => { if (data.success) location.reload(); else alert(data.message||'Erreur'); });
}

// ============================================================
// IMPRIMER
// ============================================================
function imprimerCarte() {
    const svg = getSVG(); if (!svg) { window.print(); return; }
    const clone = svg.cloneNode(true);
    svg.querySelectorAll('path').forEach((el, i) => {
        const clonePath = clone.querySelectorAll('path')[i];
        if (clonePath) {
            clonePath.setAttribute('fill',         el.style.fill   || 'transparent');
            clonePath.setAttribute('stroke',       el.style.stroke || '#bbb');
            clonePath.setAttribute('stroke-width', el.style.strokeWidth || '2px');
            if (el.style.strokeDasharray) clonePath.setAttribute('stroke-dasharray', el.style.strokeDasharray);
        }
    });
    const vb = svg.viewBox?.baseVal;
    if (vb && vb.width > 0) {
        clone.setAttribute('width', vb.width); clone.setAttribute('height', vb.height);
    } else {
        const rect  = svg.getBoundingClientRect();
        const scale = parseInt(document.getElementById('svg-zoom').value) / 100;
        clone.setAttribute('width', rect.width/scale); clone.setAttribute('height', rect.height/scale);
    }
    const svgData = new XMLSerializer().serializeToString(clone);
    const blob    = new Blob([svgData], { type:'image/svg+xml;charset=utf-8' });
    const url     = URL.createObjectURL(blob);
    const w = window.open('','_blank');
    w.document.write(`<!DOCTYPE html><html><head><title>Carte — {{ $tf->title }}</title>
    <style>*{margin:0;padding:0;box-sizing:border-box;}body{font-family:sans-serif;padding:20px;background:white;}
    h2{color:#1e3a5f;margin-bottom:12px;font-size:16px;}img{max-width:100%;height:auto;display:block;border:1px solid #e2e8f0;border-radius:6px;}
    .legend{margin-top:16px;display:flex;flex-wrap:wrap;gap:10px;font-size:11px;}.leg{display:flex;align-items:center;gap:5px;}
    .dot{width:14px;height:14px;border-radius:3px;flex-shrink:0;}@media print{body{padding:8px;}}</style>
    </head><body><h2>🗺️ {{ $tf->title }}</h2>
    <img src="${url}" onload="URL.revokeObjectURL('${url}')">
    <div class="legend">
        <div class="leg"><div class="dot" style="background:#faf5ee;border:2px solid #c8a882;"></div>Famille</div>
        <div class="leg"><div class="dot" style="background:#0d6efd;"></div>Eden</div>
        <div class="leg"><div class="dot" style="background:#7c3aed;"></div>Implantation prévue</div>
        <div class="leg"><div class="dot" style="background:#16a34a;"></div>Déjà implanté</div>
        <div class="leg"><div class="dot" style="background:#dc2626;"></div>Dossier technique</div>
        <div class="leg"><div class="dot" style="background:#ea580c;"></div>Morcellement</div>
        <div class="leg"><div class="dot" style="background:transparent;border:3px solid #f59e0b;"></div>Zone groupée</div>
    </div>
    <script>window.addEventListener('load',function(){setTimeout(function(){window.print();},800);});<\/script>
    </body></html>`);
    w.document.close();
}

function supprimerLot() {
    if (!confirm('Supprimer ce lot ? Cette action est irréversible.')) return;
    fetch(`/admin/lots/${currentZone.lotId}`, {
        method: 'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Erreur lors de la suppression');
    });
}

</script>
@endsection