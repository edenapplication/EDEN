@extends('admin.layout')
@section('content')

<style>
.btn-outline-secondary:hover { background-color:#e5e7eb; color:#111; }

#tf-map-container path:hover {
    fill: rgba(138,43,226,0.18) !important;
    transition: 0.2s;
}

/* LÉGENDE FIXE */
#legend-fixe {
    position:fixed; bottom:20px; right:20px;
    background:white; border:1px solid #e2e8f0;
    border-radius:12px; padding:12px 16px;
    box-shadow:0 4px 16px rgba(0,0,0,0.12);
    z-index:999; min-width:170px; font-size:12px;
}
#legend-fixe h6 { font-weight:700; margin-bottom:8px; font-size:12px; color:#1e3a5f; }
.leg-item { display:flex; align-items:center; gap:8px; margin-bottom:5px; }
.leg-dot  { width:14px; height:14px; border-radius:3px; flex-shrink:0; border:2px solid transparent; }

#svg-resize-bar {
    display:flex; align-items:center; gap:10px;
    margin-bottom:8px; font-size:13px; color:#64748b;
}
#svg-zoom { width:150px; }

/* CARTE */
#tf-map-container {
    overflow: auto;
    position: relative;
    max-height: calc(100vh - 200px);
    min-height: 500px;
}

/* AUTOCOMPLETE */
.client-search-wrap { position:relative; }
.client-dropdown {
    position:absolute; top:100%; left:0; right:0;
    background:white; border:1px solid #ddd; border-radius:8px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    z-index:999999; max-height:200px; overflow-y:auto; display:none;
}
.client-option {
    padding:8px 12px; cursor:pointer; font-size:13px;
    border-bottom:1px solid #f8fafc;
    display:flex; justify-content:space-between; align-items:center;
}
.client-option:hover { background:#eff6ff; }

/* PANNEAU LATÉRAL CLIENT */
#clientPanel {
    display:none;
    position:fixed;
    top:70px;
    z-index:99999;
    width:300px;
    max-height:calc(100vh - 90px);
    overflow-y:auto;
    background:white;
    border-radius:14px;
    box-shadow:0 8px 28px rgba(0,0,0,0.18);
    padding:16px;
    font-size:12px;
    border-top:4px solid #0d6efd;
    pointer-events:auto;
}
#clientPanel h6 { font-weight:800; color:#1e3a5f; margin-bottom:2px; font-size:14px; }
.cp-dossier-section {
    border-radius:8px; padding:10px; margin-bottom:8px;
    background:#f8fafc; border-left:3px solid #0d6efd;
}
.cp-pay-row {
    display:flex; justify-content:space-between;
    font-size:11px; padding:3px 0; border-bottom:1px solid #f1f5f9;
}
.cp-pay-amt { font-weight:700; color:#28a745; }
.cp-prog-wrap { height:5px; background:#e2e8f0; border-radius:3px; margin-top:4px; }
.cp-prog-fill { height:100%; border-radius:3px; }
.cp-lot-card {
    border-left:3px solid #ccc; padding:6px 8px;
    margin-bottom:8px; background:#f8fafc; border-radius:6px;
}

.btn-pay-small {
    background:#28a745; color:white; border:none; border-radius:6px;
    padding:4px 10px; font-size:11px; font-weight:600; cursor:pointer;
    transition:0.15s; margin-top:6px; width:100%;
}
.btn-pay-small:hover { background:#1e7e34; }

#lotModal { pointer-events:auto; z-index:99998; }

#paiementDossierModal {
    display:none;
    position:fixed; top:50%; left:50%;
    transform:translate(-50%,-50%);
    background:white; padding:20px; border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,0.25);
    z-index:1000000;
    width:380px;
    pointer-events:auto;
}

/* Overlay transparent = panneau toujours cliquable */
#modalOverlay { pointer-events:none; }

/* ✅ Badge étape suivante */
.badge-next-step {
    background:linear-gradient(135deg,#4D96FF,#0d6efd);
    color:white; font-size:11px; padding:3px 10px;
    border-radius:20px; font-weight:600; margin-left:8px;
}
</style>

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-3">
    <div style="flex:1;">
        <a href="{{ route('tf.show', $tf->id) }}"
           style="
               display:inline-block; width:70%;
               background:linear-gradient(135deg,#1e3a5f,#2d6cdf);
               color:white; border-radius:12px; padding:14px 24px;
               font-size:18px; font-weight:800; text-decoration:none;
               box-shadow:0 4px 14px rgba(30,58,95,0.3);
               white-space:nowrap; overflow:hidden; text-overflow:ellipsis;
               transition:opacity 0.2s;
           "
           onmouseover="this.style.opacity='0.88'"
           onmouseout="this.style.opacity='1'">
            🗺️ {{ $tf->title }}
        </a>
    </div>
    <div class="d-flex gap-2">
        {{-- ✅ BOUTON IMPRIMER --}}
        <button onclick="imprimerCarte()"
                class="btn btn-outline-secondary btn-sm"
                title="Imprimer la carte">
            🖨️ Imprimer
        </button>
        <a href="{{ route('sites.show', $tf->site_id) }}" class="btn btn-outline-secondary btn-sm">
            ← Retour au site
        </a>
    </div>
</div>

@if($tf->file_path)
<div style="margin-top:8px; border:1px solid #ddd; border-radius:10px; padding:12px;">
    <div id="svg-resize-bar">
        <span>🔍 Zoom :</span>
        <input type="range" id="svg-zoom" min="30" max="200" value="100">
        <span id="svg-zoom-val">100%</span>
    </div>
    @php
        $svgPath = storage_path('app/public/' . $tf->file_path);
        $ext     = pathinfo($tf->file_path, PATHINFO_EXTENSION);
    @endphp
    @if($ext === 'svg')
        <div id="tf-map-container">
            {!! file_get_contents($svgPath) !!}
        </div>
    @endif
</div>
@endif

{{-- OVERLAY --}}
<div id="modalOverlay" style="
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,0.35); z-index:99996;
    pointer-events:none;
"></div>

{{-- MODAL ORIGINE --}}
<div id="originModal" style="
    display:none; position:fixed; top:50%; left:50%;
    transform:translate(-50%,-50%); background:white;
    padding:24px; border-radius:14px;
    box-shadow:0 10px 30px rgba(0,0,0,0.3);
    z-index:99999; width:320px; text-align:center; pointer-events:auto;
">
    <h4 style="color:#1e3a5f; margin-bottom:4px;">📍 Origine du lot</h4>
    <p style="color:#64748b; font-size:13px; margin-bottom:16px;">
        Zone : <strong id="originZoneLabel">-</strong>
    </p>
    <div class="mb-3 text-start">
        <label style="font-size:13px; font-weight:600; color:#374151;">Superficie (m²)</label>
        <input type="number" id="origine_superficie" class="form-control mt-1" placeholder="Ex: 500" min="1">
    </div>
    <div class="d-flex gap-2 justify-content-center mt-3">
        <button class="btn btn-primary px-4" onclick="setOrigin('eden')">🔵 EDEN</button>
        <button onclick="setOrigin('famille')"
                style="background:#c8a882;color:#4a2e0a;border:none;padding:8px 20px;border-radius:8px;font-weight:600;cursor:pointer;">
            🏡 FAMILLE
        </button>
    </div>
    <button onclick="closeAllModals()" class="btn btn-light btn-sm mt-3 w-100">Annuler</button>
</div>

{{-- MODAL LOT --}}
<div id="lotModal" style="
    display:none; position:fixed; top:50%;
    transform:translateY(-50%);
    background:white; padding:20px;
    border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.3);
    z-index:99998; width:420px; max-height:92vh; overflow-y:auto;
    pointer-events:auto;
">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <div>
            <h4 class="mb-0">📦 Lot</h4>
            <div id="lotEtapeInfo" style="font-size:12px; color:#64748b; margin-top:2px;"></div>
        </div>
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

    {{-- INFO CLIENT EXISTANT (si lot déjà attribué) --}}
    <div id="clientExistantInfo" style="display:none; padding:8px 12px; background:#f0f7ff;
         border-radius:8px; font-size:12px; color:#1e3a5f; margin-bottom:10px; border-left:3px solid #0d6efd;">
        👤 <strong id="clientExistantNom"></strong>
        <span id="clientExistantPhone" style="color:#64748b;"></span>
    </div>

    {{-- RECHERCHE CLIENT — seulement pour implantation_prevue sur lot sans client --}}
    <div id="clientSearchField" style="display:none;" class="mt-2">
        <label style="font-weight:600; color:#374151;">👤 Client</label>
        <div class="client-search-wrap mt-1">
            <input type="text" id="client_search" class="form-control"
                   placeholder="🔍 Rechercher client (nom ou tél.)..." autocomplete="off">
            <div class="client-dropdown" id="client_dropdown"></div>
        </div>
        <input type="hidden" id="selected_client_id">
        <div id="client_selected_info" style="display:none; margin-top:6px; padding:6px 10px;
             background:#ecfdf5; border-radius:6px; font-size:12px; color:#065f46;"></div>
    </div>

    {{-- SÉLECTION DU DOSSIER --}}
    <div id="dossierSelectField" style="display:none;" class="mt-2">
        <label style="font-weight:600; color:#374151;">📂 Lier au dossier client</label>
        <select id="selected_dossier_id" class="form-control form-control-sm mt-1">
            <option value="">-- Choisir un dossier --</option>
        </select>
        <div id="dossier_selected_info" style="display:none; margin-top:6px; padding:6px 10px;
             background:#eff6ff; border-radius:6px; font-size:12px; color:#1e3a5f;"></div>
    </div>

    {{-- DATES --}}
    <div id="datePrevueGroup" style="display:none;" class="mt-2">
        <label>Date prévue</label>
        <input type="date" id="date_prevue" class="form-control">
    </div>
    <div id="dateConfirmeeGroup" class="mt-2" style="display:none;">
        <label>Date confirmée</label>
        <input type="date" id="date_confirmee" class="form-control">
    </div>
    <div id="dateMorcellementGroup" class="mt-2" style="display:none;">
        <label>Date morcellement</label>
        <input type="date" id="date_morcellement" class="form-control">
    </div>

    {{-- SUPERFICIE --}}
    <div id="superficieField" class="mt-2" style="display:none;">
        <label>Superficie (m²)</label>
        <input type="number" id="superficie" class="form-control">
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="closeModal()" class="btn btn-light">Annuler</button>
        <button onclick="saveLot()" class="btn btn-success">Enregistrer</button>
    </div>
</div>

{{-- PANNEAU LATÉRAL CLIENT --}}
<div id="clientPanel">
    <div class="d-flex justify-content-between align-items-start mb-3">
        <div>
            <h6 id="cp-name">-</h6>
            <div id="cp-phone" style="color:#64748b; font-size:11px;"></div>
        </div>
        <button onclick="document.getElementById('clientPanel').style.display='none'"
                style="background:none;border:none;font-size:16px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;
                margin-bottom:8px; border-top:1px solid #f1f5f9; padding-top:8px;">
        📂 Dossiers client
    </div>
    <div id="cp-dossiers"></div>

    <div style="font-size:11px; font-weight:700; color:#64748b; text-transform:uppercase;
                margin-bottom:8px; border-top:1px solid #f1f5f9; padding-top:8px; margin-top:8px;">
        📦 Lots attribués
    </div>
    <div id="cp-lots"></div>
</div>

{{-- MODAL PAIEMENT DOSSIER --}}
<div id="paiementDossierModal">
    <h5 style="font-weight:700; color:#1e3a5f; margin-bottom:14px;">
        💰 Paiement — <span id="pay-dossier-nom"></span>
    </h5>
    <div class="mb-2">
        <label style="font-size:12px; font-weight:600;">Montant (FCFA)</label>
        <input type="number" id="pay-montant" class="form-control form-control-sm">
    </div>
    <div class="mb-2">
        <label style="font-size:12px; font-weight:600;">Date</label>
        <input type="date" id="pay-date" class="form-control form-control-sm">
    </div>
    <div class="mb-3">
        <label style="font-size:12px; font-weight:600;">Note (optionnel)</label>
        <input type="text" id="pay-note" class="form-control form-control-sm" placeholder="Ex: Versement initial">
    </div>
    <div class="d-flex justify-content-between">
        <button onclick="closePaiementDossier()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="savePaiementDossier()" class="btn btn-success btn-sm">💾 Enregistrer</button>
    </div>
</div>

{{-- LÉGENDE FIXE --}}
<div id="legend-fixe">
    <h6>📌 Légende</h6>
    <div class="leg-item">
        {{-- ✅ Famille : très clair, presque blanc --}}
        <div class="leg-dot" style="background:#f5ede3; border:2px solid #c8a882;"></div>
        Famille
    </div>
    <div class="leg-item">
        <div class="leg-dot" style="background:#0d6efd;"></div>
        EDEN
    </div>
    <div class="leg-item">
        <div class="leg-dot" style="background:#7c3aed;"></div>
        Implant. prévue
    </div>
    <div class="leg-item">
        <div class="leg-dot" style="background:#28a745;"></div>
        Déjà implanté
    </div>
    <div class="leg-item">
        <div class="leg-dot" style="background:#dc3545;"></div>
        Dossier technique
    </div>
    <div class="leg-item">
        <div class="leg-dot" style="background:#fd7e14;"></div>
        Morcellement
    </div>
</div>

@endsection

@section('scripts')
<script>

// ============================================================
// ÉTAT GLOBAL
// ============================================================
let currentZone      = null;
let originZone       = null;
let searchTimer      = null;
let currentDossierId = null;
let cachedDossiers   = [];

// REFS DOM
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

// ============================================================
// ZOOM SVG
// ============================================================
const zoomSlider   = document.getElementById('svg-zoom');
const zoomVal      = document.getElementById('svg-zoom-val');
const svgContainer = document.getElementById('tf-map-container');

if (zoomSlider && svgContainer) {
    zoomSlider.addEventListener('input', function() {
        zoomVal.innerText = this.value + '%';
        const svgEl = svgContainer.querySelector('svg');
        if (svgEl) { svgEl.style.width = this.value + '%'; svgEl.style.height = 'auto'; }
    });
}

// ============================================================
// ✅ IMPRIMER LA CARTE
// ============================================================
function imprimerCarte() {
    const container = document.getElementById('tf-map-container');
    if (!container) return;

    const svgEl = container.querySelector('svg');
    if (!svgEl) { window.print(); return; }

    // Sérialise le SVG avec son état actuel (couleurs incluses)
    const svgData   = new XMLSerializer().serializeToString(svgEl);
    const svgBlob   = new Blob([svgData], { type: 'image/svg+xml;charset=utf-8' });
    const svgUrl    = URL.createObjectURL(svgBlob);

    const printWin  = window.open('', '_blank');
    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Carte — {{ $tf->title }}</title>
            <style>
                * { margin:0; padding:0; box-sizing:border-box; }
                body { font-family: sans-serif; padding:20px; }
                h2  { font-size:18px; font-weight:bold; margin-bottom:12px; color:#1e3a5f; }
                img { max-width:100%; height:auto; display:block; }
                .legend { margin-top:20px; display:flex; flex-wrap:wrap; gap:12px; font-size:12px; }
                .leg    { display:flex; align-items:center; gap:6px; }
                .dot    { width:14px; height:14px; border-radius:3px; flex-shrink:0; }
                @media print { .no-print { display:none; } }
            </style>
        </head>
        <body>
            <h2>🗺️ {{ $tf->title }}</h2>
            <img src="${svgUrl}" onload="URL.revokeObjectURL('${svgUrl}')">
            <div class="legend">
                <div class="leg"><div class="dot" style="background:transparent;border:2px dashed #aaa;"></div>Non défini</div>
                <div class="leg"><div class="dot" style="background:#f5ede3;border:2px solid #c8a882;"></div>Famille</div>
                <div class="leg"><div class="dot" style="background:#0d6efd;"></div>Eden</div>
                <div class="leg"><div class="dot" style="background:#6c757d;"></div>Implantation prévue</div>
                <div class="leg"><div class="dot" style="background:#28a745;"></div>Déjà implanté</div>
                <div class="leg"><div class="dot" style="background:#dc3545;"></div>Dossier technique</div>
                <div class="leg"><div class="dot" style="background:#fd7e14;"></div>Morcellement</div>
            </div>
            <script>
                window.onload = function() { window.print(); window.close(); }
            <\/script>
        </body>
        </html>
    `);
    printWin.document.close();
}

// ============================================================
// AUTOCOMPLETE CLIENT
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
        fetch(`/admin/lots/client-search?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(clients => {
            if (!clients.length) { clientDropdown.style.display = 'none'; return; }
            clientDropdown.innerHTML = clients.map(c =>
                `<div class="client-option"
                      data-id="${c.id}"
                      data-name="${c.name}"
                      data-phone="${c.phone ?? ''}">
                    <strong>${c.name}</strong>
                    <small>${c.phone ?? ''}</small>
                </div>`
            ).join('');
            clientDropdown.style.display = 'block';

            clientDropdown.querySelectorAll('.client-option').forEach(opt => {
                opt.addEventListener('click', function() {
                    selectedClientId.value = this.dataset.id;
                    clientSearch.value     = this.dataset.name;
                    clientDropdown.style.display = 'none';
                    const info = document.getElementById('client_selected_info');
                    info.innerHTML     = `✅ <strong>${this.dataset.name}</strong> ${this.dataset.phone}`;
                    info.style.display = 'block';
                    loadClientPanel(this.dataset.id);
                });
            });
        });
    }, 250);
});

document.addEventListener('click', e => {
    if (!e.target.closest('.client-search-wrap')) clientDropdown.style.display = 'none';
});

// ============================================================
// RESET SELECT DOSSIER
// ============================================================
function resetDossierSelect() {
    cachedDossiers = [];
    selectedDossierId.innerHTML = '<option value="">-- Choisir un dossier --</option>';
    dossierSelectField.style.display = 'none';
    document.getElementById('dossier_selected_info').style.display = 'none';
}

// ============================================================
// CHARGER PANNEAU CLIENT
// ============================================================
function loadClientPanel(clientId) {
    fetch(`/admin/lots/client-panel/${clientId}`)
    .then(r => r.json())
    .then(data => {
        document.getElementById('cp-name').innerText  = data.name;
        document.getElementById('cp-phone').innerText = '📞 ' + (data.phone || '-');

        cachedDossiers = data.dossiers || [];

        // Peupler le select dossier dans le modal
        selectedDossierId.innerHTML = '<option value="">-- Choisir un dossier --</option>';
        cachedDossiers.forEach(d => {
            const opt    = document.createElement('option');
            opt.value    = d.id;
            opt.innerText= d.nom + (d.prix_ref !== '-' ? ' — ' + d.prix_ref + ' FCFA' : '');
            selectedDossierId.appendChild(opt);
        });

        if (cachedDossiers.length > 0) {
            dossierSelectField.style.display = 'block';
            selectedDossierId.value = cachedDossiers[0].id;
            updateDossierInfo(cachedDossiers[0]);
        }

        // Panneau dossiers
        const dossiers = cachedDossiers;
        document.getElementById('cp-dossiers').innerHTML = !dossiers.length
            ? '<div style="color:#94a3b8;font-size:11px;">Aucun dossier — <a href="/admin/suivi-client/create" style="color:#0d6efd;">Créer</a></div>'
            : dossiers.map(d => {
                const progColor = d.progression < 40 ? '#dc3545' : (d.progression < 75 ? '#fd7e14' : '#28a745');
                const pays = d.paiements.map(p =>
                    `<div class="cp-pay-row">
                        <span>${p.date}${p.note ? ' — ' + p.note : ''}</span>
                        <span class="cp-pay-amt">${p.montant} FCFA</span>
                    </div>`
                ).join('');
                return `
                <div class="cp-dossier-section">
                    <div style="font-weight:700;font-size:12px;color:#1e3a5f;margin-bottom:4px;">📂 ${d.nom}</div>
                    <div style="font-size:11px;color:#64748b;line-height:1.8;">
                        🔗 ${d.facilitateur ?? '-'} &nbsp;|&nbsp; 🧑‍💼 ${d.commercial ?? '-'}<br>
                        🤝 ${d.agent ?? '-'} &nbsp;|&nbsp; 🚗 ${d.conducteur ?? '-'}<br>
                        🏢 ${d.grand_site ?? '-'} &nbsp;|&nbsp; 🧭 ${d.direction ?? '-'}<br>
                        📐 ${d.superficie ?? '-'} m² &nbsp;|&nbsp; 💰 ${d.prix_ref ?? '-'} FCFA
                    </div>
                    <div style="margin-top:8px;margin-bottom:4px;font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">Paiements</div>
                    ${pays || '<div style="color:#94a3b8;font-size:11px;">Aucun paiement</div>'}
                    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:6px;font-size:11px;">
                        <span>Total : <strong style="color:#28a745;">${d.total_paye} FCFA</strong></span>
                        <span>Reste : <strong style="color:#dc3545;">${d.reste} FCFA</strong></span>
                    </div>
                    <div class="cp-prog-wrap" style="margin-top:6px;">
                        <div class="cp-prog-fill" style="width:${d.progression}%;background:${progColor};"></div>
                    </div>
                    <div style="font-size:10px;color:${progColor};font-weight:600;text-align:right;">${d.progression}%</div>
                    <button class="btn-pay-small" onclick="openPaiementDossier(${d.id}, '${d.nom.replace(/'/g, "\\'")}')">
                        + Ajouter un paiement
                    </button>
                </div>`;
            }).join('');

        // Panneau lots
        const lots = data.lots;
        document.getElementById('cp-lots').innerHTML = !lots.length
            ? '<div style="color:#94a3b8;font-size:11px;">Aucun lot</div>'
            : lots.map(l => {
                const progColor = l.prog < 40 ? '#dc3545' : (l.prog < 75 ? '#fd7e14' : '#28a745');
                return `
                <div class="cp-lot-card" style="border-left-color:${l.color};">
                    <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
                        <strong style="font-size:12px;">${l.code}</strong>
                        <span style="font-size:10px;color:${l.color};font-weight:600;">${l.type ?? 'Sans type'}</span>
                    </div>
                    ${l.date_prevue    ? `<div style="font-size:10px;color:#64748b;">📅 Prévue : ${l.date_prevue}</div>` : ''}
                    ${l.date_confirmee ? `<div style="font-size:10px;color:#64748b;">✅ Confirmée : ${l.date_confirmee}</div>` : ''}
                    ${l.date_sortie    ? `<div style="font-size:10px;color:#64748b;">📁 Sortie : ${l.date_sortie}</div>` : ''}
                    ${l.prog > 0 ? `<div class="cp-prog-wrap mt-1"><div class="cp-prog-fill" style="width:${l.prog}%;background:${progColor};"></div></div>` : ''}
                </div>`;
            }).join('');

        positionClientPanel();
        document.getElementById('clientPanel').style.display = 'block';
    });
}

function updateDossierInfo(d) {
    if (!d) { document.getElementById('dossier_selected_info').style.display = 'none'; return; }
    const progColor = d.progression < 40 ? '#dc3545' : (d.progression < 75 ? '#fd7e14' : '#28a745');
    const info = document.getElementById('dossier_selected_info');
    info.innerHTML = `
        <div style="display:flex;justify-content:space-between;margin-bottom:3px;">
            <span>💰 Payé</span><strong style="color:#28a745;">${d.total_paye} FCFA</strong>
        </div>
        <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
            <span>⏳ Reste</span><strong style="color:#dc3545;">${d.reste} FCFA</strong>
        </div>
        <div style="height:6px;background:#e2e8f0;border-radius:3px;">
            <div style="width:${d.progression}%;height:100%;background:${progColor};border-radius:3px;"></div>
        </div>
        <div style="font-size:10px;text-align:right;color:${progColor};font-weight:600;">${d.progression}%</div>`;
    info.style.display = 'block';
}

selectedDossierId.addEventListener('change', function() {
    const d = cachedDossiers.find(x => x.id == this.value);
    updateDossierInfo(d || null);
});

// ============================================================
// POSITION DU PANNEAU LATÉRAL
// ============================================================
function positionClientPanel() {
    const modal  = document.getElementById('lotModal');
    const panel  = document.getElementById('clientPanel');
    if (modal.style.display === 'none') return;
    const rect   = modal.getBoundingClientRect();
    const panelW = 310;
    let   left   = rect.right + 14;
    if (left + panelW > window.innerWidth) left = rect.left - panelW - 14;
    panel.style.left = Math.max(4, left) + 'px';
}

// ============================================================
// PAIEMENT DOSSIER
// ============================================================
function openPaiementDossier(dossierId, nom) {
    currentDossierId = dossierId;
    document.getElementById('pay-dossier-nom').innerText = nom;
    document.getElementById('pay-montant').value = '';
    document.getElementById('pay-date').value    = new Date().toISOString().split('T')[0];
    document.getElementById('pay-note').value    = '';
    document.getElementById('paiementDossierModal').style.display = 'block';
}

function closePaiementDossier() {
    document.getElementById('paiementDossierModal').style.display = 'none';
}

function savePaiementDossier() {
    const montant = document.getElementById('pay-montant').value;
    const date    = document.getElementById('pay-date').value;
    const note    = document.getElementById('pay-note').value;
    if (!montant || !date) { alert('Montant et date obligatoires.'); return; }

    fetch(`/admin/paiements-dossier/${currentDossierId}`, {
        method : 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body   : JSON.stringify({ montant, date_paiement: date, note })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closePaiementDossier();
            const clientId = selectedClientId.value;
            if (clientId) loadClientPanel(clientId);
            else location.reload();
        } else {
            alert(data.message || 'Erreur');
        }
    });
}

// ============================================================
// COULEURS
// ============================================================
function getColor(type) {
    return {
        implantation_prevue: "#7c3aed",   // violet vif
        deja_implante:       "#16a34a",   // vert vif
        dossier_technique:   "#dc2626",   // rouge vif
        morcellement:        "#ea580c",   // orange vif
    }[type] || "#0d6efd";
}

function formatDate(s) {
    if (!s) return "";
    const d = new Date(s);
    return isNaN(d) ? s : d.toLocaleDateString("fr-FR", { day:"2-digit", month:"2-digit", year:"numeric" });
}

// ============================================================
// AFFICHER NOM + DATE SUR LE LOT
// ============================================================
function afficherInfoSurLot(svg, pathEl, lot) {
    const bbox  = pathEl.getBBox();
    const cx    = bbox.x + bbox.width  / 2;
    const cy    = bbox.y + bbox.height / 2;
    let dateTxt = "";

    if (lot.type === "implantation_prevue" && lot.date_prevue)       dateTxt = formatDate(lot.date_prevue);
    else if (lot.type === "deja_implante"  && lot.date_confirmee)    dateTxt = formatDate(lot.date_confirmee);
    else if (lot.type === "morcellement"   && lot.date_morcellement) dateTxt = formatDate(lot.date_morcellement);

    const nomTxt = lot.owner_name || "";
    if (!nomTxt && !dateTxt) return;

    const g = document.createElementNS("http://www.w3.org/2000/svg", "g");
    g.style.pointerEvents = "none";

    if (nomTxt) {
        const t = document.createElementNS("http://www.w3.org/2000/svg", "text");
        t.setAttribute("x", cx);
        t.setAttribute("y", dateTxt ? cy - 6 : cy);
        t.setAttribute("text-anchor", "middle");
        t.setAttribute("dominant-baseline", "central");
        t.setAttribute("font-size", "10");
        t.setAttribute("font-weight", "700");
        t.setAttribute("fill", "#1e293b");
        t.setAttribute("stroke", "#fff");
        t.setAttribute("stroke-width", "2.5");
        t.setAttribute("paint-order", "stroke");
        t.textContent = nomTxt;
        g.appendChild(t);
    }
    if (dateTxt) {
        const t = document.createElementNS("http://www.w3.org/2000/svg", "text");
        t.setAttribute("x", cx);
        t.setAttribute("y", nomTxt ? cy + 9 : cy);
        t.setAttribute("text-anchor", "middle");
        t.setAttribute("dominant-baseline", "central");
        t.setAttribute("font-size", "9");
        t.setAttribute("fill", "#374151");
        t.setAttribute("stroke", "#fff");
        t.setAttribute("stroke-width", "2");
        t.setAttribute("paint-order", "stroke");
        t.textContent = dateTxt;
        g.appendChild(t);
    }
    svg.appendChild(g);
}

// ============================================================
// ✅ DÉTERMINER L'ÉTAPE SUIVANTE DU LOT
// ============================================================
function getEtapeSuivante(lot) {
    if (!lot || !lot.type) return 'implantation_prevue';
    if (lot.type === 'implantation_prevue') return 'deja_implante';
    if (lot.type === 'deja_implante')       return 'dossier_technique';
    if (lot.type === 'dossier_technique')   return 'morcellement';
    return null; // morcellement = fin
}

const etapeLabels = {
    implantation_prevue: 'Implantation prévue',
    deja_implante:       'Déjà implanté',
    dossier_technique:   'Dossier technique',
    morcellement:        'Morcellement',
};

// ============================================================
// LISTENER TYPE → CHAMPS
// ============================================================
lotType.addEventListener("change", function () {
    const type = this.value;

    [datePrevueGroup, dateConfirmeeGroup,
     dateMorcellementGroup, superficieField]
        .forEach(el => el.style.display = "none");

    if (type === "implantation_prevue") {
        datePrevueGroup.style.display = "block";
        superficieField.style.display = "block";
    }
    if (type === "deja_implante") {
        dateConfirmeeGroup.style.display = "block";
        superficieField.style.display    = "block";
    }
    if (type === "dossier_technique") {
        dateConfirmeeGroup.style.display = "block";
    }
    if (type === "morcellement") {
        dateMorcellementGroup.style.display = "block";
        superficieField.style.display       = "block";
    }
});

// ============================================================
// INIT SVG
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
    const container = document.querySelector("#tf-map-container");
    if (!container) return;
    const svg = container.querySelector("svg");
    if (!svg) return;

    const lots = @json(\App\Models\Lot::with('client')->where('tf_id', $tf->id)->get());

    svg.querySelectorAll("path").forEach(el => {
        const zoneId = (el.getAttribute("id") || "").trim().toLowerCase();
        const lot    = lots.find(l => (l.code || "").trim().toLowerCase() === zoneId);

        el.style.cursor      = "pointer";
        el.style.strokeWidth = "2.5px";

        if (!lot || !lot.origine) {
            el.style.stroke          = "#bbb";
            el.style.fill            = "transparent";
            el.style.strokeDasharray = "2,2";

        } else if (lot.origine === "famille") {
            // ✅ Famille : très clair, presque blanc avec contour discret
            el.style.stroke          = "#c8a882";
            el.style.fill            = "rgba(250,245,238,0.35)";   // beige très clair
            el.style.strokeDasharray = "";

        } else if (lot.origine === "eden") {
            el.style.strokeDasharray = "";
            if (!lot.type) {
                el.style.stroke = "#0d6efd";
                el.style.fill   = "rgba(13,110,253,0.25)";  // bleu pâle renforcé
            } else {
                const color     = getColor(lot.type);
                el.style.stroke = color;
                // Remplissage renforcé (plus opaque)
                el.style.fill   = hexToRgba(color, 0.50);
                afficherInfoSurLot(svg, el, lot);
            }
        }

        el.addEventListener("click", function (e) {
            if (e.ctrlKey)             { openOriginModal(zoneId, lot); return; }
            if (!lot || !lot.origine)  { openOriginModal(zoneId, lot); return; }
            if (lot.origine === "famille") { openOriginModal(zoneId, lot); return; }
            if (lot.origine === "eden")    { openModal(zoneId, lot); }
        });
    });
});

// Convertit un hex en rgba avec opacité
function hexToRgba(hex, alpha) {
    const r = parseInt(hex.slice(1, 3), 16);
    const g = parseInt(hex.slice(3, 5), 16);
    const b = parseInt(hex.slice(5, 7), 16);
    return `rgba(${r},${g},${b},${alpha})`;
}

// ============================================================
// OUVRIR MODAL ORIGINE
// ============================================================
function openOriginModal(zoneId, lot) {
    originZone = { zoneId, lotId: lot ? lot.id : null };
    document.getElementById("originZoneLabel").innerText = zoneId.toUpperCase();
    document.getElementById("origine_superficie").value  = lot?.superficie || "";
    modalOverlay.style.display                            = "block";
    document.getElementById("originModal").style.display = "block";
}

// ============================================================
// ✅ OUVRIR MODAL LOT — logique d'étapes progressives
// ============================================================
function openModal(zoneId, lot = null) {
    currentZone = { zoneId, tfId: "{{ $tf->id }}", lotId: lot ? lot.id : null };

    lotModal.style.left      = "30%";
    lotModal.style.transform = "translate(-50%, -50%)";

    zoneLabel.innerText      = zoneId.toUpperCase();
    superficie.value         = lot?.superficie        || "";
    date_prevue.value        = lot?.date_prevue       || "";
    date_confirmee.value     = lot?.date_confirmee    || "";
    date_morcellement.value  = lot?.date_morcellement || "";

    resetDossierSelect();

    // ✅ LOGIQUE PROGRESSIVE : on propose l'étape suivante
    const etapeSuivante = getEtapeSuivante(lot);
    const aDejaClient   = !!lot?.client_id;
    const clientExistEl = document.getElementById('clientExistantInfo');

    // Masquer tous les champs client
    clientSearchField.style.display = 'none';
    clientExistEl.style.display     = 'none';
    document.getElementById('client_selected_info').style.display = 'none';
    document.getElementById('clientPanel').style.display = 'none';
    selectedClientId.value = '';
    clientSearch.value     = '';

    // Afficher info étape dans le header
    const etapeInfoEl = document.getElementById('lotEtapeInfo');

    if (etapeSuivante === null) {
        // Lot complet (morcellement fait)
        etapeInfoEl.innerHTML = '<span style="color:#28a745;">✅ Toutes les étapes sont complètes</span>';
        lotType.value = '';
        lotType.disabled = true;
    } else {
        lotType.disabled = false;

        if (lot?.type) {
            // Étape actuelle + prochaine étape proposée
            etapeInfoEl.innerHTML = `
                Étape actuelle : <strong>${etapeLabels[lot.type]}</strong>
                <span class="badge-next-step">→ ${etapeLabels[etapeSuivante]}</span>`;

            // ✅ Pré-sélectionner l'étape suivante dans le select
            lotType.value = etapeSuivante;

            // Verrouiller le select sur l'étape suivante (on ne peut qu'avancer)
            Array.from(lotType.options).forEach(opt => {
                if (opt.value === '') { opt.disabled = false; return; }
                const ordre = ['implantation_prevue','deja_implante','dossier_technique','morcellement'];
                const idxActuel  = ordre.indexOf(lot.type);
                const idxOpt     = ordre.indexOf(opt.value);
                // Autoriser uniquement l'étape suivante
                opt.disabled = (idxOpt !== idxActuel + 1);
            });

        } else {
            // Pas encore de type → première étape obligatoire
            etapeInfoEl.innerHTML = `<span style="color:#6c757d;">Première étape : <strong>Implantation prévue</strong></span>`;
            lotType.value = 'implantation_prevue';

            Array.from(lotType.options).forEach(opt => {
                if (opt.value === '') { opt.disabled = false; return; }
                opt.disabled = (opt.value !== 'implantation_prevue');
            });
        }
    }

    // ✅ Afficher le client existant (nom figé, pas de recherche)
    if (aDejaClient) {
        document.getElementById('clientExistantNom').innerText   = lot.client?.name   ?? '';
        document.getElementById('clientExistantPhone').innerText = lot.client?.phone ? ` (${lot.client.phone})` : '';
        clientExistEl.style.display = 'block';
        selectedClientId.value      = lot.client_id;
        loadClientPanel(lot.client_id);

    } else if (etapeSuivante === 'implantation_prevue' || lot?.type === null) {
        // Seulement à l'étape implantation_prevue sans client → on peut chercher
        clientSearchField.style.display = 'block';
    }

    modalOverlay.style.display = "block";
    lotModal.style.display     = "block";
    lotType.dispatchEvent(new Event("change"));
}

// ============================================================
// FERMER MODALS
// ============================================================
function closeModal() {
    lotModal.style.display     = "none";
    modalOverlay.style.display = "none";
    document.getElementById('clientPanel').style.display = 'none';
    resetDossierSelect();
    closePaiementDossier();
    // Réactiver le select
    Array.from(lotType.options).forEach(opt => { opt.disabled = false; });
    lotType.disabled = false;
}

function closeAllModals() {
    document.getElementById("originModal").style.display = "none";
    lotModal.style.display     = "none";
    modalOverlay.style.display = "none";
    document.getElementById('clientPanel').style.display = 'none';
    resetDossierSelect();
    closePaiementDossier();
    Array.from(lotType.options).forEach(opt => { opt.disabled = false; });
    lotType.disabled = false;
}

// ============================================================
// SET ORIGINE
// ============================================================
function setOrigin(value) {
    const sup = document.getElementById('origine_superficie').value;
    if (!sup || parseFloat(sup) <= 0) { alert("Superficie obligatoire."); return; }

    fetch("/admin/lots/set-origin", {
        method : "POST",
        headers: { "Content-Type":"application/json", "X-CSRF-TOKEN":"{{ csrf_token() }}" },
        body   : JSON.stringify({
            svg_id:     originZone.zoneId,
            tf_id:      "{{ $tf->id }}",
            origine:    value,
            superficie: sup
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || "Erreur");
    });
}

// ============================================================
// SAVE LOT
// ============================================================
function saveLot() {
    const url = currentZone.lotId
        ? `/admin/lots/${currentZone.lotId}`
        : "/admin/lots/store";

    fetch(url, {
        method : "POST",
        headers: { "Content-Type":"application/json", "X-CSRF-TOKEN":"{{ csrf_token() }}" },
        body   : JSON.stringify({
            _method:           currentZone.lotId ? "PUT" : "POST",
            tf_id:             currentZone.tfId,
            svg_id:            currentZone.zoneId,
            code:              zoneLabel.innerText.toLowerCase(),
            type:              lotType.value,
            client_id:         selectedClientId.value || null,
            dossier_client_id: selectedDossierId.value || null,
            superficie:        superficie.value,
            date_prevue:       date_prevue.value,
            date_confirmee:    date_confirmee.value,
            date_morcellement: date_morcellement.value,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || "Erreur");
    });
}
</script>
@endsection