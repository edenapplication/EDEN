@extends('rh.layout')
@section('content')

<style>
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:500px; max-height:90vh; overflow-y:auto; }

.badge-status {
    padding:3px 12px;
    border-radius:10px;
    font-size:10px;
    font-weight:600;
    display:inline-block;
}
.badge-status.approuve { background:#dcfce7; color:#15803d; }
.badge-status.refuse { background:#fee2e2; color:#b91c1c; }
.badge-status.en_attente { background:#fef3c7; color:#92400e; }

.filter-bar { background:white; border-radius:12px; padding:14px 18px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }

.table-modern { font-size:12px; margin-bottom:0; }
.table-modern thead th { background:#1e3a5f; color:white; padding:10px 10px; font-weight:600; border-bottom:none; }
.table-modern tbody td { padding:10px; vertical-align:middle; border-bottom:1px solid #f1f5f9; }
.table-modern tbody tr:hover { background:#f8fafc; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🗓️ Absences & Permissions</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.absences.pdf-liste') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}"
           class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-pdf"></i> Imprimer liste
        </a>
        <button onclick="openModal('addModal')" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nouvelle demande
        </button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- FILTRES --}}
<form method="GET" class="filter-bar d-flex gap-2 flex-wrap align-items-end">
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Employé</label>
        <select name="employe_id" class="form-control form-control-sm" style="min-width:200px;" onchange="this.form.submit()">
            <option value="">👤 Tous les employés</option>
            @foreach($employes as $e)
                <option value="{{ $e->id }}" {{ request('employe_id') == $e->id ? 'selected':'' }}>
                    {{ $e->nom }} {{ $e->prenom }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Statut</label>
        <select name="statut" class="form-control form-control-sm" style="min-width:150px;" onchange="this.form.submit()">
            <option value="">🔄 Tous statuts</option>
            <option value="en_attente" {{ request('statut') === 'en_attente' ? 'selected':'' }}>En attente</option>
            <option value="approuvé"   {{ request('statut') === 'approuvé'   ? 'selected':'' }}>Approuvé</option>
            <option value="refusé"     {{ request('statut') === 'refusé'     ? 'selected':'' }}>Refusé</option>
        </select>
    </div>
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Mois</label>
        <input type="month" name="mois" class="form-control form-control-sm" style="width:150px;" value="{{ request('mois') }}" onchange="this.form.submit()">
    </div>
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Type</label>
        <select name="type_absence" class="form-control form-control-sm" style="min-width:150px;" onchange="this.form.submit()">
            <option value="">📋 Tous types</option>
            @foreach(['Congé annuel','Congé maladie','Permission','Congé maternité','Congé paternité','Absence injustifiée','Autre'] as $t)
                <option value="{{ $t }}" {{ request('type_absence') === $t ? 'selected':'' }}>{{ $t }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
    <a href="{{ route('rh.absences.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
</form>

{{-- TABLEAU --}}
<div style="overflow-x:auto;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);background:white;">
<table class="table table-modern">
    <thead>
        <tr>
            <th>Réf.</th>
            <th>Employé</th>
            <th>Direction</th>
            <th>Type</th>
            <th>Début</th>
            <th>Fin</th>
            <th style="text-align:center;">Jours</th>
            <th>Statut</th>
            <th>Motif</th>
            <th style="width:150px;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($absences as $a)
        <tr>
            <td style="color:#1d4ed8;font-weight:700;">{{ $a->reference }}</td>
            <td>
                <strong>{{ $a->employe?->nom }} {{ $a->employe?->prenom }}</strong>
                <br>
                <small style="color:#94a3b8;font-size:10px;">{{ $a->employe?->matricule }}</small>
            </td>
            <td>{{ $a->employe?->direction?->nom ?? '-' }}</td>
            <td>{{ $a->type_absence }}</td>
            <td>{{ $a->date_debut?->format('d/m/Y') }}</td>
            <td>{{ $a->date_fin?->format('d/m/Y') }}</td>
            <td style="text-align:center;font-weight:700;font-size:13px;color:#1d4ed8;">{{ $a->nombre_jours }}</td>
            <td>
                <span class="badge-status {{ $a->statut === 'approuvé' ? 'approuve' : ($a->statut === 'refusé' ? 'refuse' : 'en_attente') }}">
                    @if($a->statut === 'approuvé') <i class="bi bi-check-circle"></i> @endif
                    @if($a->statut === 'refusé') <i class="bi bi-x-circle"></i> @endif
                    @if($a->statut === 'en_attente') <i class="bi bi-clock"></i> @endif
                    {{ ucfirst($a->statut) }}
                </span>
            </td>
            <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $a->motif }}">
                {{ $a->motif ?? '-' }}
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button onclick="openEditModal({{ $a->id }}, {{ $a->employe_id }}, '{{ $a->type_absence }}', '{{ $a->date_debut?->format('Y-m-d') }}', '{{ $a->date_fin?->format('Y-m-d') }}', '{{ addslashes($a->motif) }}', '{{ addslashes($a->note) }}')"
                            class="btn btn-sm btn-outline-warning" title="Modifier">
                        <i class="bi bi-pencil"></i>
                    </button>
                    <a href="{{ route('rh.absences.pdf', $a->id) }}" class="btn btn-sm btn-outline-danger" title="PDF">
                        <i class="bi bi-file-pdf"></i>
                    </a>
                    @if($a->statut === 'en_attente')
                        <form action="{{ route('rh.absences.approuver', $a->id) }}" method="POST" style="display:inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-success" title="Approuver">
                                <i class="bi bi-check-lg"></i>
                            </button>
                        </form>
                        <form action="{{ route('rh.absences.refuser', $a->id) }}" method="POST" style="display:inline">
                            @csrf
                            <button class="btn btn-sm btn-outline-danger" title="Refuser">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </form>
                    @endif
                    <form action="{{ route('rh.absences.destroy', $a->id) }}" method="POST" style="display:inline"
                          onsubmit="return confirm('Supprimer cette absence ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-secondary" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="10" class="text-center text-muted py-5">
                <div style="font-size:40px;margin-bottom:12px;">📭</div>
                <p style="font-size:14px;">Aucune absence enregistrée</p>
                <p style="font-size:12px;color:#94a3b8;">Cliquez sur "Nouvelle demande" pour en créer une.</p>
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
</div>

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeModal('addModal')"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;"><i class="bi bi-plus-circle"></i> Nouvelle demande</h5>
        <button onclick="closeModal('addModal')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form method="POST" action="{{ route('rh.absences.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Employé <span class="text-danger">*</span></label>
                <select name="employe_id" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                <select name="type_absence" class="form-control" required>
                    @foreach(['Congé annuel','Congé maladie','Permission','Congé maternité','Congé paternité','Absence injustifiée','Autre'] as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Date début <span class="text-danger">*</span></label>
                <input type="date" name="date_debut" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Date fin <span class="text-danger">*</span></label>
                <input type="date" name="date_fin" class="form-control" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" class="form-control" placeholder="Raison de l'absence">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Note / Remarques</label>
                <textarea name="note" class="form-control" rows="5" placeholder="Informations complémentaires, précisions, pièces justificatives..."></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeModal('addModal')" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
        </div>
    </form>
</div>

{{-- MODAL MODIFICATION --}}
<div class="modal-overlay" id="overlayEdit" onclick="closeModal('editModal')"></div>
<div class="modal-box" id="editModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;"><i class="bi bi-pencil"></i> Modifier la demande</h5>
        <button onclick="closeModal('editModal')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form id="editForm" method="POST" action="">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Employé</label>
                <select name="employe_id" class="form-control" required id="edit_employe_id">
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Type</label>
                <select name="type_absence" class="form-control" id="edit_type">
                    @foreach(['Congé annuel','Congé maladie','Permission','Congé maternité','Congé paternité','Absence injustifiée','Autre'] as $t)
                        <option value="{{ $t }}">{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Date début</label>
                <input type="date" name="date_debut" class="form-control" id="edit_debut">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Date fin</label>
                <input type="date" name="date_fin" class="form-control" id="edit_fin">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" class="form-control" id="edit_motif">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Note / Remarques</label>
                <textarea name="note" class="form-control" rows="6" id="edit_note" placeholder="Informations complémentaires..."></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeModal('editModal')" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Mettre à jour</button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
function openModal(id) {
    const overlayId = id === 'addModal' ? 'overlayAdd' : 'overlayEdit';
    document.getElementById(overlayId).style.display = 'block';
    document.getElementById(id).style.display = 'block';
}

function closeModal(id) {
    document.getElementById('overlayAdd').style.display = 'none';
    document.getElementById('overlayEdit').style.display = 'none';
    document.getElementById(id).style.display = 'none';
}

function openEditModal(id, employeId, type, debut, fin, motif, note) {
    document.getElementById('editForm').action = `/rh/absences/${id}`;
    document.getElementById('edit_employe_id').value = employeId;
    document.getElementById('edit_type').value = type;
    document.getElementById('edit_debut').value = debut;
    document.getElementById('edit_fin').value = fin;
    document.getElementById('edit_motif').value = motif;
    document.getElementById('edit_note').value = note || '';
    document.getElementById('overlayEdit').style.display = 'block';
    document.getElementById('editModal').style.display = 'block';
}
</script>
@endsection