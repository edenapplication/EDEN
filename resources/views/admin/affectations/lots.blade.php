@extends('admin.layout')
@section('content')

<style>
.modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9998; }
.modal-box { display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:24px;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,0.2);z-index:9999;width:540px;max-height:90vh;overflow-y:auto; }
.badge-dispo  { background:#dcfce7;color:#16a34a; }
.badge-occupe { background:#fee2e2;color:#dc2626; }
.lot-checkbox { width:18px;height:18px;cursor:pointer;accent-color:#1d4ed8; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('affectations.blocs') }}" class="btn btn-outline-secondary btn-sm mb-2">← Blocs</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">📦 Gestion des Lots</h2>
        <div style="font-size:13px;color:#64748b;">
            <span id="compteur-lots">{{ $lots->count() }}</span> lot(s)
            <span id="selection-info" style="margin-left:12px;display:none;color:#1d4ed8;font-weight:600;">
                <span id="nb-selectionnes">0</span> sélectionné(s)
            </span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="ouvrirModifSuperficie()" class="btn btn-outline-primary btn-sm" id="btnModifSuperficie" style="display:none;">
            📐 Modifier superficie
        </button>
        <button onclick="openModal('addModal')" class="btn btn-primary">+ Ajouter des lots</button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- FILTRES --}}
<form method="GET" style="background:white;border-radius:12px;padding:14px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Grand Site</label>
            <select name="grand_site_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tous</option>
                @foreach($grandSites as $gs)
                    <option value="{{ $gs->id }}" {{ request('grand_site_id')==$gs->id?'selected':'' }}>{{ $gs->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Disponibilité</label>
            <select name="disponible" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tous</option>
                <option value="1" {{ request('disponible')==='1'?'selected':'' }}>Disponibles</option>
                <option value="0" {{ request('disponible')==='0'?'selected':'' }}>Affectés</option>
            </select>
        </div>
        <div class="col-md-2">
            <a href="{{ route('affectations.lots') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
    </div>
</form>

<div style="background:white;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,0.06);overflow:hidden;">
<table class="table table-hover mb-0" style="font-size:13px;">
    <thead style="background:#1e3a5f;color:white;">
        <tr>
            <th style="width:40px;" class="px-3 py-3">
                <input type="checkbox" id="select-all" class="lot-checkbox" onchange="toggleTousLesLots(this)">
            </th>
            <th class="px-3 py-3">Lot</th>
            <th>Bloc</th>
            <th>TF / Site</th>
            <th>Superficie</th>
            <th>Statut</th>
            <th>Client affecté</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($lots as $lot)
    <tr id="lot-row-{{ $lot->id }}">
        <td class="px-3">
            <input type="checkbox" class="lot-checkbox lot-select" 
                   data-id="{{ $lot->id }}"
                   data-superficie="{{ $lot->superficie }}"
                   onchange="majSelection()"
                   {{ $lot->disponible ? '' : 'disabled' }}>
        </td>
        <td class="px-3">
            <span style="background:#0f172a;color:white;padding:2px 10px;border-radius:6px;font-weight:700;font-family:monospace;font-size:12px;">
                {{ $lot->bloc?->code ?? '?' }}-{{ $lot->numero }}
            </span>
        </td>
        <td style="font-weight:700;">{{ $lot->bloc?->code ?? '-' }}</td>
        <td style="font-size:11px;color:#64748b;">
            {{ $lot->tf?->title ?? '-' }}<br>
            <span style="font-size:10px;">{{ $lot->site?->name ?? '-' }}</span>
        </td>
        <td id="sup-{{ $lot->id }}">{{ $lot->superficie ? number_format($lot->superficie,0,',','') . ' m²' : '-' }}</td>
        <td>
            <span class="badge-{{ $lot->disponible ? 'dispo' : 'occupe' }}"
                  style="padding:3px 10px;border-radius:10px;font-size:11px;font-weight:700;">
                {{ $lot->disponible ? '✅ Disponible' : '🔴 Affecté' }}
            </span>
        </td>
        <td style="font-size:12px;">
            @if(!$lot->disponible && $lot->affectation)
                <div style="font-weight:700;">{{ $lot->affectation->client?->name }}</div>
                <div style="font-size:10px;color:#64748b;">
                    {{ $lot->affectation->date_affectation?->format('d/m/Y') }}
                </div>
            @else
                <span style="color:#94a3b8;">—</span>
            @endif
        </td>
        <td>
            <div class="d-flex gap-1">
                <button onclick="openEditLot({{ $lot->id }}, '{{ $lot->numero }}', {{ $lot->superficie ?? 'null' }}, {{ $lot->actif ? 1:0 }})"
                        class="btn btn-warning btn-sm" style="font-size:11px;">✏️</button>
                @if($lot->disponible)
                <button onclick="supprimerLot({{ $lot->id }})"
                        class="btn btn-outline-danger btn-sm" style="font-size:11px;">🗑</button>
                @endif
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center text-muted py-4">Aucun lot</td></tr>
    @endforelse
    </tbody>
</table>
</div>

{{-- MODAL AJOUT LOTS --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeAll()"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">📦 Ajouter des lots</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <div style="background:#dbeafe;border-radius:10px;padding:12px;margin-bottom:16px;font-size:12px;color:#1d4ed8;">
        ℹ️ Séparez les numéros de lots par des <strong>point-virgules</strong>. Ex: <strong>01;02;03;15A</strong>
    </div>
    <form method="POST" action="{{ route('affectations.lots.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Grand Site <span class="text-danger">*</span></label>
            <select name="grand_site_id" class="form-control" required onchange="chargerSites(this.value,'lot')">
                <option value="">-- Choisir --</option>
                @foreach($grandSites as $gs)
                    <option value="{{ $gs->id }}">{{ $gs->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Site</label>
            <select name="site_id" id="site-lot" class="form-control" onchange="chargerTfs(this.value,'lot')">
                <option value="">-- Choisir --</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">TF</label>
            <select name="tf_id" id="tf-lot" class="form-control" onchange="chargerBlocs(this.value,'lot')">
                <option value="">-- Choisir --</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Bloc <span class="text-danger">*</span></label>
            <select name="bloc_id" id="bloc-lot" class="form-control" required>
                <option value="">-- Choisir d'abord un TF --</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Numéros des lots <span class="text-danger">*</span></label>
            <input type="text" name="numeros" class="form-control" placeholder="Ex: 01;02;03;15A" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Superficie par défaut (m²)</label>
            <input type="number" name="superficie" class="form-control" placeholder="Optionnel — même superficie pour tous">
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary">💾 Créer</button>
        </div>
    </form>
</div>

{{-- MODAL MODIFICATION SUPERFICIE MULTIPLE --}}
<div class="modal-overlay" id="overlaySuperficie" onclick="fermerModalSuperficie()"></div>
<div class="modal-box" id="modalSuperficie">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">📐 Modifier la superficie</h5>
        <button onclick="fermerModalSuperficie()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <div style="background:#f0fdf4;border-radius:10px;padding:12px;margin-bottom:16px;font-size:12px;color:#16a34a;">
        <span id="modal-nb-lots">0</span> lot(s) sélectionné(s)
    </div>
    <form onsubmit="return false;">
        <div class="mb-3">
            <label class="form-label fw-semibold">Nouvelle superficie (m²)</label>
            <input type="number" id="nouvelle-superficie" class="form-control" placeholder="Entrez la superficie" required>
        </div>
        <div class="mb-3" style="background:#fef3c7;border-radius:8px;padding:10px;font-size:12px;color:#92400e;">
            ⚠️ Cette action modifiera la superficie de <strong id="modal-nb-lots2">0</strong> lot(s)
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" onclick="fermerModalSuperficie()" class="btn btn-light">Annuler</button>
            <button type="button" onclick="appliquerSuperficie()" class="btn btn-primary">💾 Appliquer</button>
        </div>
    </form>
</div>

{{-- MODAL EDIT LOT --}}
<div class="modal-overlay" id="overlayEdit" onclick="closeAll()"></div>
<div class="modal-box" id="editModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">✏️ Modifier le lot</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <div id="editLotContent"></div>
</div>

@endsection
@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
let lotsSelectionnes = [];

function openModal(id) {
    document.getElementById('overlayAdd').style.display = 'block';
    document.getElementById(id).style.display = 'block';
}

function openEditLot(id, numero, superficie, actif) {
    document.getElementById('editLotContent').innerHTML = `
        <form onsubmit="sauvegarderLot(event,${id})">
            <div class="mb-3">
                <label class="form-label fw-semibold">Numéro</label>
                <input type="text" id="el-num" class="form-control" value="${numero}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Superficie (m²)</label>
                <input type="number" id="el-sup" class="form-control" value="${superficie || ''}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Statut</label>
                <select id="el-actif" class="form-control">
                    <option value="1" ${actif?'selected':''}>Actif</option>
                    <option value="0" ${!actif?'selected':''}>Inactif</option>
                </select>
            </div>
            <div class="d-flex justify-content-end gap-2">
                <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
                <button type="submit" class="btn btn-warning">💾 Mettre à jour</button>
            </div>
        </form>
    `;
    document.getElementById('overlayEdit').style.display = 'block';
    document.getElementById('editModal').style.display   = 'block';
}

function sauvegarderLot(e, id) {
    e.preventDefault();
    fetch(`/admin/affectations/lots/${id}`, {
        method:'PUT', headers:{'Content-Type':'application/json','X-CSRF-TOKEN':CSRF},
        body: JSON.stringify({ 
            numero: document.getElementById('el-num').value, 
            superficie: document.getElementById('el-sup').value, 
            actif: document.getElementById('el-actif').value 
        }),
    }).then(r=>r.json()).then(d=>{ 
        if(d.success) location.reload(); 
        else alert(d.message); 
    });
}

function supprimerLot(id) {
    if (!confirm('Supprimer ce lot ?')) return;
    fetch(`/admin/affectations/lots/${id}`, {
        method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'}
    }).then(r=>r.json()).then(d=>{ 
        if(d.success) location.reload(); 
        else alert(d.message); 
    });
}

function closeAll() {
    ['overlayAdd','overlayEdit','addModal','editModal'].forEach(id=>{
        const el=document.getElementById(id);
        if(el) el.style.display='none';
    });
}

function chargerSites(gsId,suf){
    const s=document.getElementById('site-'+suf);
    if(!gsId){s.innerHTML='<option value="">-- Choisir --</option>';return;}
    fetch(`/admin/affectations/api/sites/${gsId}`).then(r=>r.json()).then(d=>{
        s.innerHTML='<option value="">-- Choisir --</option>'+d.map(x=>`<option value="${x.id}">${x.name}</option>`).join('');
    });
}

function chargerTfs(sId,suf){
    const t=document.getElementById('tf-'+suf);
    if(!sId){t.innerHTML='<option value="">-- Choisir --</option>';return;}
    fetch(`/admin/affectations/api/tfs/${sId}`).then(r=>r.json()).then(d=>{
        t.innerHTML='<option value="">-- Choisir --</option>'+d.map(x=>`<option value="${x.id}">${x.title}</option>`).join('');
    });
}

function chargerBlocs(tfId,suf){
    const b=document.getElementById('bloc-'+suf);
    if(!tfId){b.innerHTML='<option value="">-- Choisir d\'abord un TF --</option>';return;}
    fetch(`/admin/affectations/api/blocs/${tfId}`).then(r=>r.json()).then(d=>{
        b.innerHTML='<option value="">-- Choisir --</option>'+d.map(x=>`<option value="${x.id}">Bloc ${x.code}</option>`).join('');
    });
}

// ============================================================
// SÉLECTION MULTIPLE DES LOTS
// ============================================================
function majSelection() {
    const checkboxes = document.querySelectorAll('.lot-select:checked');
    const nb = checkboxes.length;
    const info = document.getElementById('selection-info');
    const nbEl = document.getElementById('nb-selectionnes');
    const btnModif = document.getElementById('btnModifSuperficie');
    
    lotsSelectionnes = Array.from(checkboxes).map(cb => parseInt(cb.dataset.id));
    
    if (nb > 0) {
        info.style.display = 'inline';
        nbEl.textContent = nb;
        btnModif.style.display = 'inline-block';
    } else {
        info.style.display = 'none';
        btnModif.style.display = 'none';
    }
    
    // Mettre à jour le select all
    const total = document.querySelectorAll('.lot-select:not(:disabled)').length;
    const checked = document.querySelectorAll('.lot-select:checked').length;
    document.getElementById('select-all').checked = total > 0 && checked === total;
}

function toggleTousLesLots(checkbox) {
    const checkboxes = document.querySelectorAll('.lot-select:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    majSelection();
}

function ouvrirModifSuperficie() {
    const nb = lotsSelectionnes.length;
    if (nb === 0) {
        alert('Veuillez sélectionner au moins un lot.');
        return;
    }
    document.getElementById('modal-nb-lots').textContent = nb;
    document.getElementById('modal-nb-lots2').textContent = nb;
    document.getElementById('nouvelle-superficie').value = '';
    document.getElementById('overlaySuperficie').style.display = 'block';
    document.getElementById('modalSuperficie').style.display = 'block';
}

function fermerModalSuperficie() {
    document.getElementById('overlaySuperficie').style.display = 'none';
    document.getElementById('modalSuperficie').style.display = 'none';
}

function appliquerSuperficie() {
    const superficie = document.getElementById('nouvelle-superficie').value;
    if (!superficie || parseFloat(superficie) <= 0) {
        alert('Veuillez entrer une superficie valide.');
        return;
    }
    
    if (lotsSelectionnes.length === 0) {
        alert('Aucun lot sélectionné.');
        return;
    }
    
    if (!confirm(`Appliquer ${superficie} m² à ${lotsSelectionnes.length} lot(s) ?`)) return;
    
    fetch('/admin/affectations/lots/superficie-multiple', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({
            lot_ids: lotsSelectionnes,
            superficie: parseFloat(superficie)
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            fermerModalSuperficie();
            // Mettre à jour l'affichage
            lotsSelectionnes.forEach(id => {
                const td = document.getElementById('sup-' + id);
                if (td) {
                    td.textContent = parseFloat(superficie).toLocaleString('fr-FR') + ' m²';
                }
                // Décocher les cases
                const cb = document.querySelector(`.lot-select[data-id="${id}"]`);
                if (cb) cb.checked = false;
            });
            lotsSelectionnes = [];
            majSelection();
            // Recharger la page pour mettre à jour
            setTimeout(() => location.reload(), 500);
        } else {
            alert(data.message || 'Erreur lors de la mise à jour');
        }
    })
    .catch(e => {
        alert('Erreur réseau : ' + e.message);
    });
}
</script>
@endsection