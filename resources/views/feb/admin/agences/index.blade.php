@extends('admin.layout')
@section('content')

<style>
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:440px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.feb.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← FEB</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">🏢 Agences FEB</h2>
        <div style="font-size:13px;color:#64748b;">{{ $agences->count() }} agence(s)</div>
    </div>
    <button onclick="openModal('addModal')" class="btn btn-primary">+ Nouvelle agence</button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div style="background:white;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,0.06);overflow:hidden;">
<table class="table table-hover mb-0" style="font-size:13px;">
    <thead style="background:#1e3a5f;color:white;">
        <tr>
            <th class="px-3 py-3">Nom</th>
            <th>Code</th>
            <th>Localité</th>
            <th>Utilisateurs</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($agences as $a)
    <tr>
        <td class="px-3 fw-bold">{{ $a->nom }}</td>
        <td>{{ $a->code ?? '-' }}</td>
        <td>{{ $a->localite ?? '-' }}</td>
        <td>
            <span style="background:#dbeafe;color:#1d4ed8;padding:2px 10px;border-radius:12px;font-size:11px;font-weight:700;">
                {{ $a->utilisateurs_count }} utilisateur(s)
            </span>
        </td>
        <td>
            <span style="background:{{ $a->actif ? '#dcfce7' : '#fee2e2' }};color:{{ $a->actif ? '#15803d' : '#b91c1c' }};padding:2px 10px;border-radius:12px;font-size:11px;font-weight:700;">
                {{ $a->actif ? 'Active' : 'Inactive' }}
            </span>
        </td>
        <td>
            <div class="d-flex gap-1">
                <button onclick="openEditModal({{ $a->id }}, '{{ addslashes($a->nom) }}', '{{ $a->code }}', '{{ $a->localite }}', {{ $a->actif ? 1 : 0 }})"
                        class="btn btn-warning btn-sm" style="font-size:11px;">✏️</button>
                <form action="{{ route('admin.feb.agences.destroy', $a->id) }}" method="POST" style="display:inline;"
                      onsubmit="return confirm('Supprimer cette agence ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" style="font-size:11px;">🗑</button>
                </form>
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="6" class="text-center text-muted py-4">Aucune agence</td></tr>
    @endforelse
    </tbody>
</table>
</div>

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeAll()"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">🏢 Nouvelle agence</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form method="POST" action="{{ route('admin.feb.agences.store') }}">
        @csrf
        <div class="mb-3"><label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
            <input type="text" name="nom" class="form-control" required></div>
        <div class="mb-3"><label class="form-label fw-semibold">Code</label>
            <input type="text" name="code" class="form-control" placeholder="Ex: AG01"></div>
        <div class="mb-3"><label class="form-label fw-semibold">Localité</label>
            <input type="text" name="localite" class="form-control"></div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </div>
    </form>
</div>

{{-- MODAL MODIFICATION --}}
<div class="modal-overlay" id="overlayEdit" onclick="closeAll()"></div>
<div class="modal-box" id="editModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">✏️ Modifier agence</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form id="editForm" method="POST" action="">
        @csrf @method('PUT')
        <div class="mb-3"><label class="form-label fw-semibold">Nom</label>
            <input type="text" name="nom" id="edit_nom" class="form-control" required></div>
        <div class="mb-3"><label class="form-label fw-semibold">Code</label>
            <input type="text" name="code" id="edit_code" class="form-control"></div>
        <div class="mb-3"><label class="form-label fw-semibold">Localité</label>
            <input type="text" name="localite" id="edit_localite" class="form-control"></div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Statut</label>
            <select name="actif" id="edit_actif" class="form-control">
                <option value="1">Active</option>
                <option value="0">Inactive</option>
            </select>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-warning">💾 Mettre à jour</button>
        </div>
    </form>
</div>

@endsection
@section('scripts')
<script>
function openModal(id) {
    document.getElementById('overlayAdd').style.display = 'block';
    document.getElementById(id).style.display = 'block';
}
function openEditModal(id, nom, code, localite, actif) {
    document.getElementById('editForm').action = `/admin/feb/agences/${id}`;
    document.getElementById('edit_nom').value      = nom;
    document.getElementById('edit_code').value     = code || '';
    document.getElementById('edit_localite').value = localite || '';
    document.getElementById('edit_actif').value    = actif;
    document.getElementById('overlayEdit').style.display = 'block';
    document.getElementById('editModal').style.display   = 'block';
}
function closeAll() {
    ['overlayAdd','overlayEdit','addModal','editModal'].forEach(id => {
        const el = document.getElementById(id); if(el) el.style.display='none';
    });
}
</script>
@endsection