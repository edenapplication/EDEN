@extends('admin.layout')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<style>
.kpi-card { background:white; border-radius:12px; padding:14px; box-shadow:0 2px 10px rgba(0,0,0,0.06); text-align:center; height:100%; }
.kpi-val  { font-size:22px; font-weight:800; }
.kpi-lbl  { font-size:10px; color:#64748b; font-weight:600; text-transform:uppercase; margin-top:3px; }
.kpi-sup  { font-size:11px; color:#94a3b8; margin-top:2px; }
.tf-card  { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.06); transition:0.2s; }
.tf-card:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(0,0,0,0.1); }
.prog-bar  { height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden; margin-top:6px; }
.prog-fill { height:100%; border-radius:4px; background:linear-gradient(90deg,#1d4ed8,#16a34a); }
.section-lbl { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px; }

/* Tooltip survol TF sur la carte */
#tf-tooltip {
    display:none;
    position:fixed;
    z-index:99999;
    background:white;
    border-radius:12px;
    box-shadow:0 8px 28px rgba(0,0,0,0.18);
    padding:14px 16px;
    min-width:220px;
    max-width:280px;
    pointer-events:none;
    border-top:3px solid #1d4ed8;
    font-size:12px;
}
#tf-tooltip .tt-title  { font-weight:800; color:#1e3a5f; font-size:13px; margin-bottom:8px; }
#tf-tooltip .tt-row    { display:flex; justify-content:space-between; padding:3px 0; border-bottom:1px solid #f1f5f9; }
#tf-tooltip .tt-lbl    { color:#64748b; }
#tf-tooltip .tt-val    { font-weight:700; }
#tf-tooltip .tt-prog   { height:5px; background:#e2e8f0; border-radius:3px; margin-top:6px; overflow:hidden; }
#tf-tooltip .tt-prog-f { height:100%; border-radius:3px; background:linear-gradient(90deg,#1d4ed8,#16a34a); }
#tf-tooltip .tt-actions { display:flex; gap:6px; margin-top:10px; }
#tf-tooltip .tt-btn    { flex:1; padding:5px 8px; border-radius:6px; font-size:11px; font-weight:600; text-align:center; text-decoration:none; cursor:pointer; border:none; }

#tfModal input { border-radius:10px; padding:10px; }
#tfModal label { margin-bottom:5px; display:block; color:#333; }
#tfModal h4 { font-weight:700; }
#modalOverlay {
    display:none; position:fixed; top:0; left:0;
    width:100%; height:100%;
    background:rgba(0,0,0,0.4); z-index:9998;
}
#tfModal { animation: fadeInScale 0.2s ease; }
@keyframes fadeInScale {
    from { transform:translate(-50%,-60%) scale(0.9); opacity:0; }
    to   { transform:translate(-50%,-50%) scale(1);   opacity:1; }
}

/* ✅ Bouton TF dans la zone SVG — 70% de la zone, gros et cliquable */
.tf-zone-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 70%;
    margin: 0 auto;
    background: linear-gradient(135deg, #1e3a5f, #2d6cdf);
    color: white;
    border-radius: 10px;
    padding: 10px 16px;
    font-size: 13px;
    font-weight: 800;
    text-align: center;
    cursor: pointer;
    box-shadow: 0 3px 10px rgba(30,58,95,0.3);
    transition: opacity 0.2s;
    border: none;
    text-decoration: none;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    max-width: 70%;
}
.tf-zone-btn:hover { opacity: 0.85; color: white; }
</style>

{{-- TOOLTIP (fixe, invisible par défaut) --}}
<div id="tf-tooltip">
    <div class="tt-title" id="tt-title">-</div>
    <div class="tt-row"><span class="tt-lbl">Lots</span>        <span class="tt-val" id="tt-lots">-</span></div>
    <div class="tt-row">
        <span class="tt-lbl">🔤 Blocs</span>
        <span class="tt-val" style="color:#374151;" id="tt-blocs">-</span>
    </div>
    <div class="tt-row">
        <span class="tt-lbl" style="color:#1d4ed8;">Lots EDEN</span>
        <span class="tt-val" style="color:#1d4ed8;" id="tt-eden">-</span>
    </div>
    <div class="tt-row">
        <span class="tt-lbl" style="color:#92400e;">Lots Famille</span>
        <span class="tt-val" style="color:#92400e;" id="tt-famille">-</span>
    </div>
    <div class="tt-row"><span class="tt-lbl">Zones groupées</span><span class="tt-val" id="tt-zones">-</span></div>
    <div class="tt-row">
        <span class="tt-lbl">⏳ Implant. prévue</span>
        <span class="tt-val" style="color:#7c3aed;" id="tt-ip">-</span>
    </div>
    <div class="tt-row">
        <span class="tt-lbl">✅ Implanté</span>
        <span class="tt-val" style="color:#16a34a;" id="tt-di">-</span>
    </div>
    <div class="tt-row">
        <span class="tt-lbl">📁 Dossier tech.</span>
        <span class="tt-val" style="color:#dc2626;" id="tt-dt">-</span>
    </div>
    <div class="tt-row">
        <span class="tt-lbl">✂️ Morcellement</span>
        <span class="tt-val" style="color:#ea580c;" id="tt-mo">-</span>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-top:6px;margin-bottom:2px;">
        <span>Activité</span><strong id="tt-pct" style="color:#1e3a5f;">-</strong>
    </div>
    <div class="tt-prog"><div class="tt-prog-f" id="tt-prog-fill" style="width:0%;"></div></div>
    <div class="tt-actions">
        <a id="tt-btn-voir"     href="#" class="tt-btn" style="background:#1d4ed8;color:white;">👁 Ouvrir</a>
        <a id="tt-btn-modifier" href="#" class="tt-btn" style="background:#f59e0b;color:white;">✏️ Modifier</a>
    </div>
</div>

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('sites.index', $site->grand_site_id) }}" class="btn btn-outline-secondary btn-sm mb-2">← Sites</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">🗺️ {{ $site->name }}</h2>
        <div style="font-size:13px;color:#64748b;">{{ $site->grandSite?->nom }}</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('sites.edit', $site->id) }}" class="btn btn-warning btn-sm">✏️ Modifier ce site</a>
    </div>
</div>

<input type="hidden" id="site_id" value="{{ $site->id }}">

{{-- KPIs GLOBAUX --}}
<div class="section-lbl">📊 Vue globale du site</div>
<div class="row g-3 mb-3">
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:3px solid #1e3a5f;">
            <div class="kpi-val" style="color:#1e3a5f;">{{ $stats['nb_tfs'] }}</div>
            <div class="kpi-lbl">TF</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:3px solid #1d4ed8;">
            <div class="kpi-val" style="color:#1d4ed8;">{{ $stats['nb_lots'] }}</div>
            <div class="kpi-lbl">Lots</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:3px solid #f59e0b;">
            <div class="kpi-val" style="color:#f59e0b;">{{ $stats['nb_zones'] }}</div>
            <div class="kpi-lbl">Zones groupées</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:3px solid #16a34a;">
            <div class="kpi-val" style="color:#16a34a;">{{ $stats['activite_pct'] }}%</div>
            <div class="kpi-lbl">Activité</div>
            <div class="prog-bar"><div class="prog-fill" style="width:{{ $stats['activite_pct'] }}%;"></div></div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:3px solid #0891b2;">
            <div class="kpi-val" style="color:#0891b2;font-size:16px;">{{ number_format($stats['sup_totale'], 0, ',', ' ') }}</div>
            <div class="kpi-lbl">Superficie (m²)</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:3px solid #7c3aed;">
            <div class="kpi-val" style="color:#7c3aed;">{{ $stats['famille'] + $stats['eden'] }}</div>
            <div class="kpi-lbl">Lots attribués</div>
        </div>
    </div>
</div>

{{-- ÉTAPES --}}
<div class="section-lbl">📈 Avancement par étape (lots + zones)</div>
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #7c3aed;">
            <div class="kpi-val" style="color:#7c3aed;">{{ $stats['implantation_prevue'] }}</div>
            <div class="kpi-lbl">Implantation prévue</div>
            <div class="kpi-sup">{{ number_format($stats['sup_implantation'], 0, ',', ' ') }} m²</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #16a34a;">
            <div class="kpi-val" style="color:#16a34a;">{{ $stats['deja_implante'] }}</div>
            <div class="kpi-lbl">Déjà implanté</div>
            <div class="kpi-sup">{{ number_format($stats['sup_implante'], 0, ',', ' ') }} m²</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #dc2626;">
            <div class="kpi-val" style="color:#dc2626;">{{ $stats['dossier_technique'] }}</div>
            <div class="kpi-lbl">Dossier technique</div>
            <div class="kpi-sup">{{ number_format($stats['sup_dossier'], 0, ',', ' ') }} m²</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #ea580c;">
            <div class="kpi-val" style="color:#ea580c;">{{ $stats['morcellement'] }}</div>
            <div class="kpi-lbl">Morcellement</div>
            <div class="kpi-sup">{{ number_format($stats['sup_morcellement'], 0, ',', ' ') }} m²</div>
        </div>
    </div>
</div>

{{-- ORIGINES --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #1d4ed8;">
            <div class="kpi-val" style="color:#1d4ed8;">{{ $stats['eden'] }}</div>
            <div class="kpi-lbl">Lots EDEN</div>
            <div class="kpi-sup">{{ number_format($stats['sup_eden'], 0, ',', ' ') }} m²</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #92400e;">
            <div class="kpi-val" style="color:#92400e;">{{ $stats['famille'] }}</div>
            <div class="kpi-lbl">Lots Famille</div>
            <div class="kpi-sup">{{ number_format($stats['sup_famille'], 0, ',', ' ') }} m²</div>
        </div>
    </div>
</div>

{{-- CARTE SVG --}}
@if($site->svg_path && file_exists(storage_path('app/public/maps/' . basename($site->svg_path))))
<div class="section-lbl">🗺️ Carte du site <small style="color:#94a3b8;font-weight:400;">(survolez un TF pour ses statistiques — cliquez pour y accéder)</small></div>
<div class="card p-3 mb-4">
    <div id="map-container" style="border:1px solid #ddd; overflow:auto; position:relative; border-radius:8px;">
        {!! file_get_contents(storage_path('app/public/maps/' . basename($site->svg_path))) !!}
    </div>
</div>
@endif

{{-- TFs --}}
<div class="section-lbl">📋 Détail par TF</div>
<div class="row g-3">
    @forelse($statsTf as $item)
        <div class="col-md-4">
            <div class="tf-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <div style="font-weight:700;font-size:14px;color:#1e3a5f;">{{ $item['tf']->title }}</div>
                        <div style="font-size:11px;color:#94a3b8;">
                            {{ $item['lots'] }} lot(s) · {{ $item['zones'] }} zone(s)
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <a href="{{ route('tf.show', $item['tf']->id) }}"
                           class="btn btn-primary btn-sm" style="font-size:11px;" title="Ouvrir le TF">👁 Voir</a>
                        <button onclick="openEditTfModal({{ $item['tf']->id }}, '{{ addslashes($item['tf']->title) }}', '{{ $item['tf']->svg_zone_id }}')"
                                class="btn btn-warning btn-sm" style="font-size:11px;" title="Modifier le TF">✏️</button>
                    </div>
                </div>
                <div class="row g-1 mb-2">
                    <div class="col-6">
                        <div style="background:#f5f3ff;border-radius:6px;padding:6px;text-align:center;">
                            <div style="font-weight:700;color:#7c3aed;">{{ $item['implantation_prevue'] }}</div>
                            <div style="font-size:9px;color:#7c3aed;">Implant. prévue</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#f0fdf4;border-radius:6px;padding:6px;text-align:center;">
                            <div style="font-weight:700;color:#16a34a;">{{ $item['deja_implante'] }}</div>
                            <div style="font-size:9px;color:#16a34a;">Implanté</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#fff1f2;border-radius:6px;padding:6px;text-align:center;">
                            <div style="font-weight:700;color:#dc2626;">{{ $item['dossier_technique'] }}</div>
                            <div style="font-size:9px;color:#dc2626;">Dossier tech.</div>
                        </div>
                    </div>
                    <div class="col-6">
                        <div style="background:#fff7ed;border-radius:6px;padding:6px;text-align:center;">
                            <div style="font-weight:700;color:#ea580c;">{{ $item['morcellement'] }}</div>
                            <div style="font-size:9px;color:#ea580c;">Morcellement</div>
                        </div>
                    </div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-bottom:4px;">
                    <span>Activité</span>
                    <strong style="color:#1e3a5f;">{{ $item['actif_pct'] }}%</strong>
                </div>
                <div class="prog-bar">
                    <div class="prog-fill" style="width:{{ $item['actif_pct'] }}%;"></div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 text-center text-muted py-4">Aucun TF dans ce site</div>
    @endforelse
</div>

{{-- MODAL CRÉER TF --}}
<div id="modalOverlay" onclick="closeTfModal()"></div>
<div id="tfModal" style="
    display:none; position:fixed; top:50%; left:50%;
    transform:translate(-50%,-50%); background:white;
    padding:25px; border-radius:16px;
    box-shadow:0 20px 50px rgba(0,0,0,0.2);
    z-index:9999; width:400px; max-width:90%;
">
    <h4 id="modalTitle">Créer un TF</h4>
    <div class="mb-3">
        <label for="tfTitle">Nom du TF</label>
        <input type="text" id="tfTitle" class="form-control" placeholder="Ex: TF 102">
    </div>
    <div class="mb-3">
        <label for="tfFile">Fichier (optionnel)</label>
        <input type="file" id="tfFile" class="form-control">
    </div>
    <div class="d-flex justify-content-end gap-2 mt-3">
        <button onclick="closeTfModal()" class="btn btn-light">Annuler</button>
        <button onclick="saveTf()" class="btn btn-primary" id="saveBtn">Créer</button>
    </div>
</div>

{{-- MODAL ACTION TF --}}
<div id="tfActionModal" style="
    display:none; position:fixed; top:30%; left:50%;
    transform:translate(-50%,-50%); background:white;
    padding:20px; border-radius:10px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
    z-index:9999; width:300px; text-align:center;
">
    <h4>Que veux-tu faire ?</h4>
    <button class="btn btn-primary w-100 mt-2" onclick="goToTf()">👁 Voir le TF</button>
    <button class="btn btn-warning w-100 mt-2" onclick="editTf()">✏️ Modifier</button>
    <button class="btn btn-light w-100 mt-2" onclick="closeTfActionModal()">Annuler</button>
</div>

{{-- MODAL MODIFIER TF --}}
<div id="modalOverlayEdit" onclick="closeEditTfModal()"
     style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9998;"></div>
<div id="editTfModal"
     style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:24px;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,0.2);z-index:9999;width:400px;">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;">✏️ Modifier le TF</h5>
        <button onclick="closeEditTfModal()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Nom du TF</label>
        <input type="text" id="edit_tf_title" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Nouveau fichier SVG (optionnel)</label>
        <input type="file" id="edit_tf_file" class="form-control" accept=".svg">
    </div>
    <div class="d-flex justify-content-end gap-2">
        <button onclick="closeEditTfModal()" class="btn btn-light">Annuler</button>
        <button onclick="saveEditTf()" class="btn btn-warning">💾 Mettre à jour</button>
    </div>
</div>

{{-- LÉGENDE --}}
<div style="position:fixed; bottom:20px; right:20px; background:white;
            padding:10px; border:1px solid #ccc; max-width:200px; border-radius:8px;">
    <strong>Légende TF</strong>
    <div id="legend-items"></div>
</div>

@endsection

@section('scripts')
<script>
// ============================================================
// DONNÉES JS
// ============================================================
const CSRF    = '{{ csrf_token() }}';
const siteId  = {{ $site->id }};
const tfsData = @json($statsTf);
const tfsRaw  = @json($tfs);

// Construire un index rapide : svg_zone_id → stats complètes
const tfStatsByZone = {};
tfsData.forEach(item => {
    if (item.tf && item.tf.svg_zone_id) {
        tfStatsByZone[item.tf.svg_zone_id.toLowerCase()] = item;
    }
});

let selectedTf  = null;
let isEditMode  = false;
let currentZone = null;

function getRandomColor() {
    const colors = ["#FF6B6B","#4D96FF","#6BCB77","#FFD93D","#845EC2","#FF9671","#00C9A7","#C34A36","#3D5A80","#98C1D9"];
    return colors[Math.floor(Math.random() * colors.length)];
}

// ============================================================
// POSITIONNER LE TOOLTIP
// ============================================================
function positionnerTooltip(e) {
    const tooltip = document.getElementById('tf-tooltip');
    const margin  = 14;
    let   left    = e.clientX + margin;
    let   top     = e.clientY + margin;
    const tw = 280, th = 260;
    if (left + tw > window.innerWidth)  left = e.clientX - tw - margin;
    if (top  + th > window.innerHeight) top  = e.clientY - th - margin;
    tooltip.style.left = Math.max(4, left) + 'px';
    tooltip.style.top  = Math.max(4, top)  + 'px';
}

// ============================================================
// MODAL CRÉER TF (via bouton)
// ============================================================
function openCreateTfModal() {
    document.getElementById('tfTitle').value = '';
    document.getElementById('tfFile').value = '';
    document.getElementById('modalTitle').innerText = 'Créer un TF';
    document.getElementById('saveBtn').innerText = 'Créer';
    document.getElementById('modalOverlay').style.display = 'block';
    document.getElementById('tfModal').style.display = 'block';
}

function closeTfModal() {
    document.getElementById('tfModal').style.display = 'none';
    document.getElementById('modalOverlay').style.display = 'none';
    currentZone = null;
}

function saveTf() {
    const title = document.getElementById('tfTitle').value.trim();
    const file = document.getElementById('tfFile').files[0];
    
    if (!title) {
        alert('Veuillez saisir un nom pour le TF.');
        return;
    }

    const zoneId = currentZone ? currentZone.zoneId : '';

    const formData = new FormData();
    formData.append('title', title);
    formData.append('site_id', siteId);
    if (zoneId) formData.append('svg_zone_id', zoneId);
    if (file) formData.append('file', file);

    // ✅ Correction : utiliser les bonnes URLs
    const url = isEditMode ? `/admin/tf/update/${selectedTf?.id}` : '/admin/tf/store';
    if (isEditMode) formData.append('_method', 'PUT');

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            return response.text().then(text => {
                throw new Error(`Erreur ${response.status}: ${text.substring(0, 200)}`);
            });
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erreur lors de la création');
        }
    })
    .catch(e => {
        console.error('Erreur:', e);
        alert('Erreur réseau : ' + e.message);
    });
}

// ============================================================
// MODAL MODIFIER TF
// ============================================================
let editTfId = null;
let editTfZoneId = null;

function openEditTfModal(id, title, zoneId) {
    editTfId = id;
    editTfZoneId = zoneId;
    document.getElementById('edit_tf_title').value = title;
    document.getElementById('edit_tf_file').value = '';
    document.getElementById('modalOverlayEdit').style.display = 'block';
    document.getElementById('editTfModal').style.display = 'block';
}

function closeEditTfModal() {
    document.getElementById('modalOverlayEdit').style.display = 'none';
    document.getElementById('editTfModal').style.display = 'none';
    editTfId = null;
}

function saveEditTf() {
    const title = document.getElementById('edit_tf_title').value.trim();
    const file = document.getElementById('edit_tf_file').files[0];
    
    if (!title || !editTfId) return;

    const formData = new FormData();
    formData.append('title', title);
    formData.append('site_id', siteId);
    formData.append('_method', 'PUT');
    if (editTfZoneId) formData.append('svg_zone_id', editTfZoneId);
    if (file) formData.append('file', file);

    fetch(`/admin/tf/update/${editTfId}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': CSRF },
        body: formData,
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Erreur lors de la mise à jour');
    })
    .catch(e => alert('Erreur réseau : ' + e.message));
}

function openTfModal(zoneId, siteId) {
    isEditMode  = false;
    currentZone = { zoneId, siteId };
    document.getElementById("tfTitle").value        = "";
    document.getElementById("tfFile").value         = "";
    document.getElementById("modalTitle").innerText = "Créer un TF";
    document.getElementById("saveBtn").innerText    = "Créer";
    document.getElementById("modalOverlay").style.display = "block";
    document.getElementById("tfModal").style.display      = "block";
}

function openTfActionModal()  { 
    document.getElementById("tfActionModal").style.display = "block"; 
}

function closeTfActionModal() { 
    document.getElementById("tfActionModal").style.display = "none"; 
}

function goToTf() { 
    if (selectedTf) {
        window.location.href = "/admin/tf/" + selectedTf.id; 
    }
}

function editTf() {
    if (!selectedTf) return;
    isEditMode  = true;
    currentZone = { zoneId: selectedTf.svg_zone_id, siteId: selectedTf.site_id };
    document.getElementById("tfTitle").value        = selectedTf.title;
    document.getElementById("modalTitle").innerText = "Modifier le TF";
    document.getElementById("saveBtn").innerText    = "Mettre à jour";
    document.getElementById("modalOverlay").style.display = "block";
    document.getElementById("tfModal").style.display      = "block";
    closeTfActionModal();
}

// ============================================================
// CARTE SVG — survol + clic
// ============================================================
document.addEventListener("DOMContentLoaded", function () {
    const siteId = document.getElementById("site_id").value;
    const tfs    = @json(\App\Models\Tf::where('site_id', $site->id)->get());
    const svg    = document.querySelector("#map-container svg");
    const tooltip = document.getElementById('tf-tooltip');
    if (!svg) return;

    svg.querySelectorAll("text").forEach(t => t.remove());

    let usedColors = {};
    let tooltipTimer = null;

    svg.querySelectorAll("path").forEach(el => {
        const tf = tfs.find(t => t.svg_zone_id === el.id);

        el.style.cursor      = "pointer";
        el.style.transition  = "0.2s";
        el.style.fill        = "transparent";
        el.style.strokeWidth = "2px";

        if (tf) {
            const color = tf.color || getRandomColor();
            usedColors[tf.title] = color;
            el.style.stroke = color;
            el.style.fill   = color + "33";

            const bbox = el.getBBox();
            const cx   = bbox.x + bbox.width  / 2;
            const cy   = bbox.y + bbox.height / 2;

            // Bouton SVG étranger
            const fo = document.createElementNS("http://www.w3.org/2000/svg", "foreignObject");
            const btnW = bbox.width * 0.70;
            const btnH = Math.min(bbox.height * 0.55, 38);

            fo.setAttribute("x",      cx - btnW / 2);
            fo.setAttribute("y",      cy - btnH / 2);
            fo.setAttribute("width",  btnW);
            fo.setAttribute("height", btnH);
            fo.style.pointerEvents = "none";

            const btn = document.createElement("a");
            btn.href      = `/admin/tf/${tf.id}`;
            btn.className = "tf-zone-btn";
            btn.style.width    = "100%";
            btn.style.maxWidth = "100%";
            btn.style.fontSize = Math.max(9, Math.min(13, btnW / 8)) + "px";
            btn.title     = tf.title;
            btn.innerText = tf.title;
            btn.style.pointerEvents = "auto";

            fo.appendChild(btn);
            svg.appendChild(fo);

            // ✅ SURVOL → afficher tooltip avec stats
            el.addEventListener("mouseenter", function(e) {
                el.style.strokeWidth = '4px';
                el.style.fill        = color + '55';

                const stats = tfStatsByZone[tf.svg_zone_id.toLowerCase()];
                if (!stats) return;

                document.getElementById('tt-title').innerText      = stats.tf.title || tf.title;
                document.getElementById('tt-lots').innerText       = stats.lots || '-';
                document.getElementById('tt-zones').innerText      = stats.zones || '-';
                document.getElementById('tt-blocs').innerText      = stats.blocs   ?? '-';
                document.getElementById('tt-eden').innerText       = stats.eden    ?? '-';
                document.getElementById('tt-famille').innerText    = stats.famille ?? '-';
                document.getElementById('tt-ip').innerText         = stats.implantation_prevue || '-';
                document.getElementById('tt-di').innerText         = stats.deja_implante || '-';
                document.getElementById('tt-dt').innerText         = stats.dossier_technique || '-';
                document.getElementById('tt-mo').innerText         = stats.morcellement || '-';
                document.getElementById('tt-pct').innerText        = stats.actif_pct + '%' || '0%';
                document.getElementById('tt-prog-fill').style.width= stats.actif_pct + '%' || '0%';
                document.getElementById('tt-btn-voir').href        = `/admin/tf/${tf.id}`;
                document.getElementById('tt-btn-modifier').onclick = function(ev) {
                    ev.preventDefault();
                    tooltip.style.display = 'none';
                    openEditTfModal(tf.id, tf.title, tf.svg_zone_id);
                };

                positionnerTooltip(e);
                clearTimeout(tooltipTimer);
                tooltip.style.display = 'block';
            });

            el.addEventListener("mousemove", function(e) {
                positionnerTooltip(e);
            });

            el.addEventListener("mouseleave", function() {
                el.style.strokeWidth = '2px';
                el.style.fill        = color + '33';
                tooltipTimer = setTimeout(() => { tooltip.style.display = 'none'; }, 200);
            });

            // Clic sur le path → action modal
            el.addEventListener("click", function (e) {
                if (e.target.tagName === 'A') return;
                selectedTf = tf;
                openTfActionModal();
            });

        } else {
            // Zone sans TF
            el.style.stroke          = "#999";
            el.style.strokeDasharray = "2,2";
            
            el.addEventListener("mouseenter", function() {
                this.style.fill = "rgba(0,150,255,0.25)";
                this.style.stroke = "#1d4ed8";
                this.style.strokeWidth = "3px";
            });
            el.addEventListener("mouseleave", function() {
                this.style.fill = "transparent";
                this.style.stroke = "#999";
                this.style.strokeWidth = "2px";
            });
            
            el.addEventListener("click", function () { 
                currentZone = { zoneId: el.id, siteId: siteId };
                openTfModal(el.id, siteId); 
            });
        }
    });

    // Masquer tooltip si on déplace sur le tooltip lui-même
    tooltip.addEventListener('mouseenter', () => clearTimeout(tooltipTimer));
    tooltip.addEventListener('mouseleave', () => { tooltip.style.display = 'none'; });

    Object.entries(usedColors).forEach(([name, color]) => {
        const div = document.createElement("div");
        div.style.cssText = "display:flex;align-items:center;margin-bottom:5px;";
        div.innerHTML = `<div style="width:12px;height:12px;background:${color};margin-right:5px;border-radius:2px;"></div><small>${name}</small>`;
        document.getElementById("legend-items").appendChild(div);
    });
});
</script>
@endsection