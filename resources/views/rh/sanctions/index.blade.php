@extends('rh.layout')
@section('content')

<style>
.sanc-table { width:100%; border-collapse:collapse; font-size:12px; background:white; }
.sanc-table thead tr { background:#1e3a5f; color:white; }
.sanc-table thead th { padding:10px 8px; font-weight:600; text-align:left; white-space:nowrap; }
.sanc-table tbody tr:nth-child(even) { background:#f8fafc; }
.sanc-table tbody tr:hover { background:#fff1f2; }
.sanc-table tbody td { padding:8px; border-bottom:1px solid #e2e8f0; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:500px; max-height:90vh; overflow-y:auto; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">⚠️ Sanctions</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.sanctions.pdf-liste') }}" class="btn btn-outline-danger btn-sm">🖨️ PDF</a>
        <button onclick="openModal()" class="btn btn-danger">+ Nouvelle sanction</button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- KPI --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #dc2626;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:#dc2626;">{{ number_format($totalMois, 0, ',', ' ') }} FCFA</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Total sanctions ce mois</div>
        </div>
    </div>
    <div class="col-md-4">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #f59e0b;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:#f59e0b;">{{ $sanctions->where('statut','en_attente')->count() }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">En attente</div>
        </div>
    </div>
    <div class="col-md-4">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #16a34a;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:#16a34a;">{{ $sanctions->where('statut','validé')->count() }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Validées</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" style="background:white;border-radius:12px;padding:14px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:16px;">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <select name="employe_id" class="form-control form-control-sm">
                <option value="">Tous les employés</option>
                @foreach($employes as $e)
                    <option value="{{ $e->id }}" {{ request('employe_id')==$e->id?'selected':'' }}>{{ $e->nom }} {{ $e->prenom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="type" class="form-control form-control-sm">
                <option value="">Tous types</option>
                @foreach(['amende','avertissement','mise_a_pied','autre'] as $t)
                    <option value="{{ $t }}" {{ request('type')===$t?'selected':'' }}>{{ ucfirst(str_replace('_',' ',$t)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="statut" class="form-control form-control-sm">
                <option value="">Tous statuts</option>
                <option value="notifié"  {{ request('statut')==='en_attente' ?'selected':'' }}>En attente</option>
                <option value="validé"   {{ request('statut')==='validé'  ?'selected':'' }}>Validé</option>
                <option value="annulé"   {{ request('statut')==='annulé'  ?'selected':'' }}>Annulé</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="month" name="mois" class="form-control form-control-sm" value="{{ request('mois') }}">
        </div>
        <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm">🔍</button>
            <a href="{{ route('rh.sanctions.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
    </div>
</form>

{{-- TABLEAU --}}
<div style="overflow-x:auto;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
<table class="sanc-table">
    <thead>
        <tr>
            <th>Employé</th>
            <th>Direction</th>
            <th>Date</th>
            <th>Type</th>
            <th>Motif</th>
            <th>Durée</th>
            <th>Montant</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($sanctions as $s)
        <tr>
            <td style="font-weight:600;">
                {{ $s->employe?->nom }} {{ $s->employe?->prenom }}<br>
                <small style="color:#64748b;">{{ $s->employe?->matricule }}</small>
            </td>
            <td>{{ $s->employe?->direction?->nom ?? '-' }}</td>
            <td>{{ $s->date instanceof \Carbon\Carbon ? $s->date->format('d/m/Y') : $s->date }}</td>
            <td>
                <span style="font-size:10px;padding:2px 8px;border-radius:8px;font-weight:600;background:#fee2e2;color:#b91c1c;">
                    {{ ucfirst(str_replace('_',' ',$s->type)) }}
                </span>
            </td>
            <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $s->motif }}">
                {{ $s->motif }}
            </td>
            <td>{{ $s->duree_jours ? $s->duree_jours.' j' : '-' }}</td>
            <td style="font-weight:700;color:#dc2626;">{{ number_format($s->montant, 0, ',', ' ') }} FCFA</td>
            <td>
                <span style="font-size:10px;padding:2px 8px;border-radius:8px;font-weight:600;
                    background:{{ $s->statut==='validé'?'#dcfce7':($s->statut==='annulé'?'#f1f5f9':'#fef9c3')}};
                    color:{{ $s->statut==='validé'?'#15803d':($s->statut==='annulé'?'#475569':'#92400e')}};
                ">{{ $s->statut }}</span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    {{-- ✅ Modifier --}}
                    <button onclick="openEditModal({{ $s->id }}, {{ $s->employe_id }}, '{{ $s->date instanceof \Carbon\Carbon ? $s->date->format('Y-m-d') : $s->date }}', '{{ $s->type }}', '{{ addslashes($s->motif) }}', {{ $s->duree_jours ?? 0 }}, {{ $s->montant ?? 0 }}, '{{ addslashes($s->description ?? '') }}')"
                            class="btn btn-warning btn-sm" style="font-size:10px;">✏️</button>
                    {{-- ✅ Valider --}}
                    @if($s->statut === 'en_attente')
                        <form action="{{ route('rh.sanctions.update', $s->id) }}" method="POST" style="display:inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="statut" value="validé">
                            <button class="btn btn-success btn-sm" style="font-size:10px;" title="Valider">✅</button>
                        </form>
                    @endif
                    {{-- ✅ Supprimer --}}
                    <form action="{{ route('rh.sanctions.destroy', $s->id) }}" method="POST" style="display:inline"
                          onsubmit="return confirm('Supprimer cette sanction ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm" style="font-size:10px;">🗑</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="9" class="text-center text-muted py-4">Aucune sanction enregistrée</td></tr>
    @endforelse
    </tbody>
</table>
</div>

{{-- ✅ MODAL AJOUT — formulaire HTML standard --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeAll()"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">⚠️ Nouvelle sanction</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form method="POST" action="{{ route('rh.sanctions.store') }}">
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
                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                <select name="type" class="form-control" required>
                    <option value="amende">Amende</option>
                    <option value="avertissement">Avertissement</option>
                    <option value="mise_a_pied">Mise à pied</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Motif <span class="text-danger">*</span></label>
                <input type="text" name="motif" class="form-control" placeholder="Ex: Absence injustifiée" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Durée (jours)</label>
                <input type="number" name="duree_jours" class="form-control" min="0" placeholder="Optionnel">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Montant (FCFA)</label>
                <input type="number" name="montant" class="form-control" value="0" min="0">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="2" placeholder="Détails supplémentaires..."></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-danger">💾 Enregistrer</button>
        </div>
    </form>
</div>

{{-- MODAL MODIFICATION --}}
<div class="modal-overlay" id="overlayEdit" onclick="closeAll()"></div>
<div class="modal-box" id="editModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">✏️ Modifier la sanction</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form id="editForm" method="POST" action="">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Employé</label>
                <select name="employe_id" class="form-control" id="edit_employe" required>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date</label>
                <input type="date" name="date" class="form-control" id="edit_date" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Type</label>
                <select name="type" class="form-control" id="edit_type">
                    <option value="amende">Amende</option>
                    <option value="avertissement">Avertissement</option>
                    <option value="mise_a_pied">Mise à pied</option>
                    <option value="autre">Autre</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" class="form-control" id="edit_motif" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Durée (jours)</label>
                <input type="number" name="duree_jours" class="form-control" id="edit_duree" min="0">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Montant (FCFA)</label>
                <input type="number" name="montant" class="form-control" id="edit_montant" min="0">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" id="edit_description" rows="2"></textarea>
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
function openModal() {
    document.getElementById('overlayAdd').style.display = 'block';
    document.getElementById('addModal').style.display   = 'block';
}
function closeAll() {
    ['overlayAdd','overlayEdit','addModal','editModal']
        .forEach(id => { const el = document.getElementById(id); if(el) el.style.display='none'; });
}
function openEditModal(id, empId, date, type, motif, duree, montant, desc) {
    document.getElementById('editForm').action           = `/rh/sanctions/${id}`;
    document.getElementById('edit_employe').value        = empId;
    document.getElementById('edit_date').value           = date;
    document.getElementById('edit_type').value           = type;
    document.getElementById('edit_motif').value          = motif;
    document.getElementById('edit_duree').value          = duree;
    document.getElementById('edit_montant').value        = montant;
    document.getElementById('edit_description').value    = desc;
    document.getElementById('overlayEdit').style.display = 'block';
    document.getElementById('editModal').style.display   = 'block';
}
</script>
@endsection