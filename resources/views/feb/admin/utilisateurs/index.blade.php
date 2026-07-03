@extends('admin.layout')
@section('content')

<style>
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:480px; max-height:90vh; overflow-y:auto; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.feb.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← FEB</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">👤 Utilisateurs FEB</h2>
        <div style="font-size:13px;color:#64748b;">{{ $utilisateurs->count() }} utilisateur(s)</div>
    </div>
    <button onclick="openModal('addModal')" class="btn btn-primary">+ Nouvel utilisateur</button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div style="background:white;border-radius:14px;box-shadow:0 2px 10px rgba(0,0,0,0.06);overflow:hidden;">
<table class="table table-hover mb-0" style="font-size:13px;">
    <thead style="background:#1e3a5f;color:white;">
        <tr>
            <th class="px-3 py-3">Identifiant</th>
            <th>Nom</th>
            <th>Poste</th>
            <th>Agence</th>
            <th>Fiches</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($utilisateurs as $u)
    <tr>
        <td class="px-3">
            <code style="background:#f1f5f9;padding:2px 8px;border-radius:6px;font-size:12px;">{{ $u->identifiant }}</code>
        </td>
        <td class="fw-bold">{{ $u->nom }} {{ $u->prenom }}</td>
        <td style="color:#64748b;">{{ $u->poste ?? '-' }}</td>
        <td>{{ $u->agence?->nom ?? '-' }}</td>
        <td>
            <span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:700;">
                {{ $u->fiches->count() }}
            </span>
        </td>
        <td>
            <span style="background:{{ $u->actif ? '#dcfce7' : '#fee2e2' }};color:{{ $u->actif ? '#15803d' : '#b91c1c' }};padding:2px 10px;border-radius:12px;font-size:11px;font-weight:700;">
                {{ $u->actif ? 'Actif' : 'Inactif' }}
            </span>
        </td>
        <td>
            <div class="d-flex gap-1">
                <button onclick="openEditModal(
                    {{ $u->id }},
                    '{{ addslashes($u->identifiant) }}',
                    '{{ addslashes($u->nom) }}',
                    '{{ addslashes($u->prenom ?? '') }}',
                    '{{ addslashes($u->poste ?? '') }}',
                    {{ $u->agence_id ?? 'null' }},
                    {{ $u->actif ? 1 : 0 }}
                )" class="btn btn-warning btn-sm" style="font-size:11px;">✏️</button>

                <form action="{{ route('admin.feb.utilisateurs.toggle', $u->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button class="btn btn-sm {{ $u->actif ? 'btn-outline-danger' : 'btn-outline-success' }}" style="font-size:11px;">
                        {{ $u->actif ? '🔒' : '🔓' }}
                    </button>
                </form>

                <form action="{{ route('admin.feb.utilisateurs.destroy', $u->id) }}" method="POST" style="display:inline;"
                      onsubmit="return confirm('Supprimer cet utilisateur ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-outline-danger btn-sm" style="font-size:11px;">🗑</button>
                </form>
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="7" class="text-center text-muted py-4">Aucun utilisateur</td></tr>
    @endforelse
    </tbody>
</table>
</div>

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeAll()"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">👤 Nouvel utilisateur FEB</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form method="POST" action="{{ route('admin.feb.utilisateurs.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Identifiant <span class="text-danger">*</span></label>
                <input type="text" name="identifiant" class="form-control" placeholder="Ex: jean.dupont" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" placeholder="Min 4 caractères" required minlength="4">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                <input type="text" name="nom" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Prénom</label>
                <input type="text" name="prenom" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Poste</label>
                <input type="text" name="poste" class="form-control" placeholder="Ex: Responsable achats">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Agence</label>
                <select name="agence_id" class="form-control">
                    <option value="">-- Aucune --</option>
                    @foreach($agences as $a)
                        <option value="{{ $a->id }}">{{ $a->nom }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary">💾 Créer</button>
        </div>
    </form>
</div>

{{-- MODAL MODIFICATION --}}
<div class="modal-overlay" id="overlayEdit" onclick="closeAll()"></div>
<div class="modal-box" id="editModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">✏️ Modifier utilisateur</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form id="editForm" method="POST" action="">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Identifiant</label>
                <input type="text" id="edit_ident" class="form-control" readonly style="background:#f1f5f9;">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nom</label>
                <input type="text" name="nom" id="edit_nom" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Prénom</label>
                <input type="text" name="prenom" id="edit_prenom" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Poste</label>
                <input type="text" name="poste" id="edit_poste" class="form-control">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Agence</label>
                <select name="agence_id" id="edit_agence" class="form-control">
                    <option value="">-- Aucune --</option>
                    @foreach($agences as $a)
                        <option value="{{ $a->id }}">{{ $a->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nouveau mot de passe <span class="text-muted" style="font-size:11px;">(laisser vide = inchangé)</span></label>
                <input type="password" name="password" class="form-control" placeholder="Nouveau mot de passe">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Statut</label>
                <select name="actif" id="edit_actif" class="form-control">
                    <option value="1">Actif</option>
                    <option value="0">Inactif</option>
                </select>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
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
function openEditModal(id, ident, nom, prenom, poste, agenceId, actif) {
    document.getElementById('editForm').action = `/admin/feb/utilisateurs/${id}`;
    document.getElementById('edit_ident').value  = ident;
    document.getElementById('edit_nom').value    = nom;
    document.getElementById('edit_prenom').value = prenom;
    document.getElementById('edit_poste').value  = poste;
    document.getElementById('edit_agence').value = agenceId ?? '';
    document.getElementById('edit_actif').value  = actif;
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