@extends('admin.layout')
@section('content')

<style>
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:440px; }
.col-card { background:white; border-radius:12px; padding:14px 16px; margin-bottom:8px; box-shadow:0 2px 8px rgba(0,0,0,0.05); display:flex; justify-content:space-between; align-items:center; border-left:4px solid #1d4ed8; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.feb.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← FEB</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">📊 Colonnes disponibles</h2>
        <div style="font-size:13px;color:#64748b;">Ces colonnes apparaîtront dans les fiches.</div>
    </div>
    <button onclick="openModal('addModal')" class="btn btn-primary">+ Nouvelle colonne</button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div style="background:#dbeafe;border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#1d4ed8;">
    ℹ️ Les colonnes créées ici seront proposées aux utilisateurs lorsqu'ils rempliront une section de fiche.
</div>

@forelse($colonnes as $col)
<div class="col-card {{ !$col->actif ? 'opacity-50' : '' }}">
    <div>
        <div style="font-weight:700;font-size:14px;color:#1e3a5f;">{{ $col->libelle }}</div>
        @if($col->description)
        <div style="font-size:12px;color:#64748b;margin-top:2px;">{{ $col->description }}</div>
        @endif
        <div style="font-size:11px;color:#94a3b8;margin-top:2px;">Ordre : {{ $col->ordre }}</div>
    </div>
    <div class="d-flex gap-2 align-items-center">
        <span style="background:{{ $col->actif ? '#dcfce7' : '#f1f5f9' }};color:{{ $col->actif ? '#15803d' : '#64748b' }};padding:2px 10px;border-radius:12px;font-size:11px;font-weight:700;">
            {{ $col->actif ? 'Active' : 'Inactive' }}
        </span>
        <button onclick="openEditModal({{ $col->id }}, '{{ addslashes($col->libelle) }}', '{{ addslashes($col->description ?? '') }}', {{ $col->ordre }}, {{ $col->actif ? 1 : 0 }})"
                class="btn btn-warning btn-sm" style="font-size:11px;">✏️</button>
        <form action="{{ route('admin.feb.colonnes.destroy', $col->id) }}" method="POST" style="display:inline;"
              onsubmit="return confirm('Supprimer cette colonne ?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm" style="font-size:11px;">🗑</button>
        </form>
    </div>
</div>
@empty
<div style="background:white;border-radius:12px;padding:40px;text-align:center;color:#94a3b8;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <div style="font-size:40px;">📊</div>
    <div style="font-size:15px;font-weight:700;margin-top:10px;">Aucune colonne créée</div>
    <div style="font-size:13px;margin-top:6px;">Créez des colonnes pour que les utilisateurs puissent remplir leurs fiches.</div>
</div>
@endforelse

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeAll()"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">📊 Nouvelle colonne</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form method="POST" action="{{ route('admin.feb.colonnes.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Libellé <span class="text-danger">*</span></label>
            <input type="text" name="libelle" class="form-control" placeholder="Ex: Désignation, Quantité, Prix unitaire..." required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Description <span class="text-muted" style="font-size:11px;">(optionnel)</span></label>
            <input type="text" name="description" class="form-control" placeholder="Explication de cette colonne">
        </div>
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
        <h5 style="color:#1e3a5f;font-weight:800;">✏️ Modifier colonne</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form id="editForm" method="POST" action="">
        @csrf @method('PUT')
        <div class="mb-3">
            <label class="form-label fw-semibold">Libellé</label>
            <input type="text" name="libelle" id="edit_libelle" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Description</label>
            <input type="text" name="description" id="edit_desc" class="form-control">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Ordre</label>
            <input type="number" name="ordre" id="edit_ordre" class="form-control" min="0">
        </div>
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
function openEditModal(id, libelle, desc, ordre, actif) {
    document.getElementById('editForm').action = `/admin/feb/colonnes/${id}`;
    document.getElementById('edit_libelle').value = libelle;
    document.getElementById('edit_desc').value    = desc;
    document.getElementById('edit_ordre').value   = ordre;
    document.getElementById('edit_actif').value   = actif;
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