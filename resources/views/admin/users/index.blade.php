@extends('admin.layout')
@section('content')

<style>
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:460px; max-height:90vh; overflow-y:auto; }
.role-badge { display:inline-block; padding:3px 10px; border-radius:10px; font-size:11px; font-weight:700; }
.role-admin      { background:#fee2e2; color:#b91c1c; }
.role-rh         { background:#f3e8ff; color:#7c3aed; }
.role-commercial { background:#dbeafe; color:#1d4ed8; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🔑 Gestion des Accès</h2>
    <button onclick="openModal('addModal')" class="btn btn-primary">+ Nouvel utilisateur</button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="card p-0 overflow-hidden">
<table class="table table-hover table-bordered mb-0" style="font-size:13px;">
    <thead class="table-dark">
        <tr>
            <th>Nom</th>
            <th>Email</th>
            <th>Référence (Signature)</th>
            <th>Rôle</th>
            <th>Statut</th>
            <th>ACTIONS</th>
        </tr>
    </thead>
    <tbody>
    @forelse($users as $user)
        <tr>
            <td>
                <strong>{{ $user->name }}</strong>
                @if($user->id === auth()->id())
                    <span class="badge bg-secondary" style="font-size:9px;">Vous</span>
                @endif
            </td>
            <td>{{ $user->email }}</td>
            <td>
                @if($user->reference)
                    <span class="badge bg-primary" style="font-size:11px;padding:4px 10px;">{{ $user->reference }}</span>
                @else
                    <span class="text-muted" style="font-size:11px;">Non défini</span>
                @endif
            </td>
            <td>
                <span class="role-badge role-{{ $user->role }}">
                    {{ ['admin' => '🔑 Admin', 'rh' => '👥 RH', 'commercial' => '💼 Commercial'][$user->role] }}
                </span>
            </td>
            <td>
                <span style="font-size:11px;padding:2px 8px;border-radius:8px;font-weight:600;background:{{ $user->actif?'#dcfce7':'#fee2e2'}};color:{{ $user->actif?'#15803d':'#b91c1c'}};">
                    {{ $user->actif ? '✅ Actif' : '🚫 Inactif' }}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button onclick="openEditModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->email }}', '{{ $user->role }}', '{{ addslashes($user->reference) }}')"
                            class="btn btn-warning btn-sm" style="font-size:10px;">✏️</button>

                    @if($user->id !== auth()->id())
                        <form action="{{ route('admin.users.toggle', $user->id) }}" method="POST" style="display:inline">
                            @csrf
                            <button class="btn btn-sm {{ $user->actif ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                    style="font-size:10px;" title="{{ $user->actif ? 'Désactiver' : 'Activer' }}">
                                {{ $user->actif ? '🚫' : '✅' }}
                            </button>
                        </form>
                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Supprimer cet utilisateur ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm" style="font-size:10px;">🗑</button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Aucun utilisateur</td></tr>
    @endforelse
    </tbody>
</table>
</div>

<div class="mt-3" style="font-size:12px;color:#64748b;">
    <strong>Rôles disponibles :</strong>
    <span class="role-badge role-admin ms-2">🔑 Admin</span> — Accès complet à tous les modules
    &nbsp;|&nbsp;
    <span class="role-badge role-rh">👥 RH</span> — Module Ressources Humaines uniquement
    &nbsp;|&nbsp;
    <span class="role-badge role-commercial">💼 Commercial</span> — Clients, dossiers, paiements, visites
</div>

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeAll()"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">🔑 Nouvel utilisateur</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form method="POST" action="{{ route('admin.users.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Nom complet <span class="text-danger">*</span></label>
                <input type="text" name="name" class="form-control" required placeholder="Ex: Jean Dupont">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                <input type="email" name="email" class="form-control" required placeholder="email@exemple.cm">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Référence (Signature) <span class="text-danger">*</span></label>
                <input type="text" name="reference" class="form-control" required placeholder="Ex: EDG-2026-001" 
                       style="text-transform:uppercase;">
                <div style="font-size:11px;color:#64748b;margin-top:4px;">
                    💡 La référence sert de signature sur les documents (Bons de paiement, etc.)
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Mot de passe <span class="text-danger">*</span></label>
                <input type="password" name="password" class="form-control" required minlength="6">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Rôle <span class="text-danger">*</span></label>
                <select name="role" class="form-control" required>
                    <option value="commercial">💼 Commercial</option>
                    <option value="rh">👥 RH</option>
                    <option value="admin">🔑 Admin</option>
                </select>
            </div>
        </div>
        <div style="background:#f0f7ff;border-radius:8px;padding:10px;margin-top:14px;font-size:12px;color:#1e3a5f;">
            <strong>Droits par rôle :</strong><br>
            🔑 <strong>Admin</strong> : tout<br>
            👥 <strong>RH</strong> : employés, paie, absences, prêts, sanctions, retards<br>
            💼 <strong>Commercial</strong> : clients, dossiers, paiements, visites
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
        <h5 style="color:#1e3a5f;font-weight:800;">✏️ Modifier l'utilisateur</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form id="editForm" method="POST" action="">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Nom complet</label>
                <input type="text" name="name" class="form-control" id="edit_name" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Email</label>
                <input type="email" name="email" class="form-control" id="edit_email" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Référence (Signature)</label>
                <input type="text" name="reference" class="form-control" id="edit_reference" 
                       placeholder="Ex: EDG-2026-001" style="text-transform:uppercase;">
                <div style="font-size:11px;color:#64748b;margin-top:4px;">
                    💡 La référence sert de signature sur les documents
                </div>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nouveau mot de passe</label>
                <input type="password" name="password" class="form-control" placeholder="Laisser vide = inchangé" minlength="6">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Rôle</label>
                <select name="role" class="form-control" id="edit_role">
                    <option value="commercial">💼 Commercial</option>
                    <option value="rh">👥 RH</option>
                    <option value="admin">🔑 Admin</option>
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
function closeAll() {
    document.getElementById('overlayAdd').style.display = 'none';
    document.getElementById('overlayEdit').style.display = 'none';
    document.getElementById('addModal').style.display = 'none';
    document.getElementById('editModal').style.display = 'none';
}
function openEditModal(id, name, email, role, reference) {
    document.getElementById('editForm').action = `/admin/users/${id}`;
    document.getElementById('edit_name').value  = name;
    document.getElementById('edit_email').value = email;
    document.getElementById('edit_role').value  = role;
    document.getElementById('edit_reference').value = reference || '';
    document.getElementById('overlayEdit').style.display = 'block';
    document.getElementById('editModal').style.display   = 'block';
}
</script>
@endsection