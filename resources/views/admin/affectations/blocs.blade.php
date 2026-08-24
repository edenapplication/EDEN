@extends('admin.layout')
@section('content')

<style>
.modal-overlay { display:none;position:fixed;inset:0;background:rgba(0,0,0,0.4);z-index:9998; }
.modal-box { display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);background:white;padding:24px;border-radius:14px;box-shadow:0 10px 30px rgba(0,0,0,0.2);z-index:9999;width:520px;max-height:90vh;overflow-y:auto; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('affectations.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← Affectations</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">🗂️ Gestion des Blocs</h2>
        <div style="font-size:13px;color:#64748b;">{{ $blocs->count() }} bloc(s)</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('affectations.lots') }}" class="btn btn-outline-secondary btn-sm">📦 Lots</a>
        <button onclick="openModal('addModal')" class="btn btn-primary">+ Ajouter des blocs</button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- FILTRES --}}
<form method="GET" style="background:white;border-radius:12px;padding:14px;margin-bottom:16px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Grand Site</label>
            <select name="grand_site_id" class="form-control form-control-sm" onchange="this.form.submit()">
                <option value="">Tous</option>
                @foreach($grandSites as $gs)
                    <option value="{{ $gs->id }}" {{ request('grand_site_id')==$gs->id?'selected':'' }}>{{ $gs->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <a href="{{ route('affectations.blocs') }}" class="btn btn-outline-secondary btn-sm">✖ Reset</a>
        </div>
    </div>
</form>

{{-- TABLEAU BLOCS --}}
<div style="background:white;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,0.06);overflow:hidden;">
<table class="table table-hover mb-0" style="font-size:13px;">
    <thead style="background:#1e3a5f;color:white;">
        <tr>
            <th class="px-3 py-3">Code</th>
            <th>Grand Site</th>
            <th>Site</th>
            <th>TF</th>
            <th>Lots total</th>
            <th>Disponibles</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($blocs as $bloc)
    <tr>
        <td class="px-3">
            <span style="background:#1e3a5f;color:white;padding:2px 10px;border-radius:6px;font-weight:700;font-family:monospace;">
                {{ $bloc->code }}
            </span>
        </td>
        <td>{{ $bloc->grandSite?->nom ?? '-' }}</td>
        <td>{{ $bloc->site?->name ?? '-' }}</td>
        <td>{{ $bloc->tf?->title ?? '-' }}</td>
        <td style="text-align:center;font-weight:700;">{{ $bloc->lots_count }}</td>
        <td style="text-align:center;">
            <span style="background:#dcfce7;color:#16a34a;padding:2px 10px;border-radius:10px;font-weight:700;font-size:11px;">
                {{ $bloc->lots_disponibles_count }} dispo.
            </span>
        </td>
        <td>
            <span style="background:{{ $bloc->actif ? '#dcfce7' : '#f1f5f9' }};color:{{ $bloc->actif ? '#16a34a' : '#64748b' }};padding:2px 10px;border-radius:10px;font-size:11px;font-weight:700;">
                {{ $bloc->actif ? 'Actif' : 'Inactif' }}
            </span>
        </td>
        <td>
            <div class="d-flex gap-1">
                <a href="{{ route('affectations.lots', ['bloc_id' => $bloc->id]) }}"
                   class="btn btn-outline-primary btn-sm" style="font-size:11px;" title="Voir les lots">📦</a>
                <button onclick="openEditBloc({{ $bloc->id }}, '{{ addslashes($bloc->code) }}', '{{ addslashes($bloc->description ?? '') }}', {{ $bloc->actif ? 1:0 }})"
                        class="btn btn-warning btn-sm" style="font-size:11px;">✏️</button>
                <button onclick="supprimerBloc({{ $bloc->id }})"
                        class="btn btn-outline-danger btn-sm" style="font-size:11px;">🗑</button>
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="8" class="text-center text-muted py-4">Aucun bloc</td></tr>
    @endforelse
    </tbody>
</table>
</div>

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeAll()"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">🗂️ Ajouter des blocs</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <div style="background:#dbeafe;border-radius:10px;padding:12px;margin-bottom:16px;font-size:12px;color:#1d4ed8;">
        ℹ️ Séparez les codes de blocs par des <strong>point-virgules</strong>. Ex: <strong>A;B;C;D</strong>
        <br>Chaque code sera créé comme un bloc indépendant.
    </div>
    <form method="POST" action="{{ route('affectations.blocs.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Grand Site <span class="text-danger">*</span></label>
            <select name="grand_site_id" class="form-control" required onchange="chargerSites(this.value,'add')">
                <option value="">-- Choisir --</option>
                @foreach($grandSites as $gs)
                    <option value="{{ $gs->id }}">{{ $gs->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Site</label>
            <select name="site_id" id="site-add" class="form-control" onchange="chargerTfs(this.value,'add')">
                <option value="">-- Choisir d'abord un grand site --</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">TF</label>
            <select name="tf_id" id="tf-add" class="form-control">
                <option value="">-- Choisir d'abord un site --</option>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Codes des blocs <span class="text-danger">*</span></label>
            <input type="text" name="codes" class="form-control"
                   placeholder="Ex: A;B;C;D;E" required>
            <div style="font-size:11px;color:#64748b;margin-top:4px;">
                Séparés par des point-virgules ( ; )
            </div>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Description (optionnel)</label>
            <input type="text" name="description" class="form-control" placeholder="Ex: Zone nord">
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary">💾 Créer</button>
        </div>
    </form>
</div>

{{-- MODAL MODIFIER --}}
<div class="modal-overlay" id="overlayEdit" onclick="closeAll()"></div>
<div class="modal-box" id="editModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">✏️ Modifier le bloc</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <div id="editBlocContent"></div>
</div>

@endsection
@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

function openModal(id) {
    document.getElementById('overlayAdd').style.display = 'block';
    document.getElementById(id).style.display = 'block';
}
function openEditBloc(id, code, desc, actif) {
    document.getElementById('editBlocContent').innerHTML = `
        <form onsubmit="sauvegarderBloc(event, ${id})">
            <div class="mb-3">
                <label class="form-label fw-semibold">Code</label>
                <input type="text" id="edit-code" class="form-control" value="${code}" required>
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <input type="text" id="edit-desc" class="form-control" value="${desc}">
            </div>
            <div class="mb-3">
                <label class="form-label fw-semibold">Statut</label>
                <select id="edit-actif" class="form-control">
                    <option value="1" ${actif ? 'selected':''}>Actif</option>
                    <option value="0" ${!actif ? 'selected':''}>Inactif</option>
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
function sauvegarderBloc(e, id) {
    e.preventDefault();
    fetch(`/admin/affectations/blocs/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({
            code:  document.getElementById('edit-code').value,
            description: document.getElementById('edit-desc').value,
            actif: document.getElementById('edit-actif').value,
        }),
    }).then(r => r.json()).then(d => { if(d.success) location.reload(); else alert(d.message); });
}
function supprimerBloc(id) {
    if (!confirm('Supprimer ce bloc ? Tous ses lots sans affectation seront supprimés.')) return;
    fetch(`/admin/affectations/blocs/${id}`, {
        method:'DELETE', headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'}
    }).then(r=>r.json()).then(d=>{ if(d.success) location.reload(); else alert(d.message); });
}
function closeAll() {
    ['overlayAdd','overlayEdit','addModal','editModal'].forEach(id => {
        const el = document.getElementById(id); if(el) el.style.display='none';
    });
}

// Sélecteurs dynamiques
function chargerSites(grandSiteId, suffix) {
    const sel = document.getElementById('site-' + suffix);
    if (!grandSiteId) { sel.innerHTML = '<option value="">-- Choisir d\'abord un grand site --</option>'; return; }
    fetch(`/admin/affectations/api/sites/${grandSiteId}`)
        .then(r=>r.json()).then(sites => {
            sel.innerHTML = '<option value="">-- Choisir un site --</option>' +
                sites.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        });
}
function chargerTfs(siteId, suffix) {
    const sel = document.getElementById('tf-' + suffix);
    if (!siteId) { sel.innerHTML = '<option value="">-- Choisir d\'abord un site --</option>'; return; }
    fetch(`/admin/affectations/api/tfs/${siteId}`)
        .then(r=>r.json()).then(tfs => {
            sel.innerHTML = '<option value="">-- Choisir un TF --</option>' +
                tfs.map(t => `<option value="${t.id}">${t.title}</option>`).join('');
        });
}
</script>
@endsection