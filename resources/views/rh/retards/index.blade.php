@extends('rh.layout')
@section('content')

<style>
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:480px; max-height:90vh; overflow-y:auto; }
.stat-card { background:white; border-radius:10px; padding:14px; box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:10px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">⏰ Retards</h2>
    <button onclick="openModal('addModal')" class="btn btn-primary">+ Enregistrer un retard</button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- FILTRES --}}
<form method="GET" class="d-flex gap-2 mb-4 flex-wrap">
    <select name="employe_id" class="form-control form-control-sm" style="max-width:220px;" onchange="this.form.submit()">
        <option value="">👤 Tous les employés</option>
        @foreach($employes as $e)
            <option value="{{ $e->id }}" {{ request('employe_id') == $e->id ? 'selected':'' }}>{{ $e->nom }} {{ $e->prenom }}</option>
        @endforeach
    </select>
    <select name="direction_id" class="form-control form-control-sm" style="max-width:200px;" onchange="this.form.submit()">
        <option value="">🏢 Toutes les directions</option>
        @foreach($directions as $d)
            <option value="{{ $d->id }}" {{ request('direction_id') == $d->id ? 'selected':'' }}>{{ $d->nom }}</option>
        @endforeach
    </select>
    <input type="month" name="mois" class="form-control form-control-sm" style="max-width:160px;" value="{{ request('mois') }}" onchange="this.form.submit()">
    <a href="{{ route('rh.retards.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
</form>

<div class="row g-3 mb-4">

    {{-- STATS PAR DIRECTION --}}
    <div class="col-md-5">
        <div class="stat-card">
            <div style="font-weight:700;font-size:13px;color:#1e3a5f;margin-bottom:12px;">📊 Retards par direction</div>
            @foreach($parDirection as $dir => $data)
                <div style="margin-bottom:12px;padding-bottom:10px;border-bottom:1px solid #f1f5f9;">
                    <div style="display:flex;justify-content:space-between;margin-bottom:4px;">
                        <span style="font-weight:700;font-size:12px;">{{ $dir ?? 'Non défini' }}</span>
                        <span style="background:#fee2e2;color:#dc2626;padding:2px 8px;border-radius:8px;font-size:11px;font-weight:700;">{{ $data['nb'] }} retard(s)</span>
                    </div>
                    @foreach($data['employes'] as $emp)
                        <div style="font-size:11px;color:#64748b;padding-left:10px;line-height:1.8;">
                            👤 {{ $emp['nom'] }}
                            @if($emp['service'] !== '-') <span style="color:#94a3b8;">— {{ $emp['service'] }}</span>@endif
                            : <strong style="color:#dc2626;">{{ $emp['nb'] }}</strong>
                        </div>
                    @endforeach
                </div>
            @endforeach
            @if($parDirection->isEmpty())
                <div class="text-muted text-center py-2" style="font-size:12px;">Aucun retard</div>
            @endif
        </div>
    </div>

    {{-- STATS PAR SERVICE --}}
    <div class="col-md-3">
        <div class="stat-card">
            <div style="font-weight:700;font-size:13px;color:#1e3a5f;margin-bottom:12px;">🗂️ Par service</div>
            @foreach($parService as $svc => $data)
                <div style="display:flex;justify-content:space-between;margin-bottom:6px;font-size:12px;">
                    <span>{{ $svc ?? 'Non défini' }}</span>
                    <span style="background:#fee2e2;color:#dc2626;padding:1px 8px;border-radius:8px;font-size:11px;font-weight:700;">{{ $data['nb'] }}</span>
                </div>
            @endforeach
            @if($parService->isEmpty())
                <div class="text-muted text-center py-2" style="font-size:12px;">Aucun retard</div>
            @endif
        </div>
    </div>

    {{-- TOTAL --}}
    <div class="col-md-4">
        <div class="stat-card" style="border-top:3px solid #dc2626;">
            <div style="font-size:32px;font-weight:900;color:#dc2626;text-align:center;">{{ $retards->count() }}</div>
            <div style="font-size:11px;color:#64748b;text-align:center;text-transform:uppercase;font-weight:600;">Retards au total</div>
        </div>
        <div class="stat-card" style="border-top:3px solid #f59e0b;margin-top:10px;">
            <div style="font-size:24px;font-weight:900;color:#f59e0b;text-align:center;">{{ $retards->sum('duree_min') ?? 0 }} min</div>
            <div style="font-size:11px;color:#64748b;text-align:center;text-transform:uppercase;font-weight:600;">Durée totale</div>
        </div>
    </div>
</div>

{{-- TABLE --}}
<div class="card p-0 overflow-hidden">
<table class="table table-hover table-bordered mb-0" style="font-size:12px;">
    <thead class="table-dark">
        <tr>
            <th>Employé</th>
            <th>Direction</th>
            <th>Date</th>
            <th>Durée (min)</th>
            <th>Motif</th>
            <th>ACTIONS</th>
        </tr>
    </thead>
    <tbody>
    @forelse($retards as $r)
        <tr>
            <td><strong>{{ $r->employe?->nom }} {{ $r->employe?->prenom }}</strong><br><span style="font-size:10px;color:#94a3b8;">{{ $r->employe?->matricule }}</span></td>
            <td>{{ $r->employe?->direction?->nom ?? '-' }}</td>
            <td>{{ $r->date }}</td>
            <td style="text-align:center;font-weight:700;color:#dc2626;">{{ $r->duree_min ?? '-' }}</td>
            <td>{{ $r->motif ?? '-' }}</td>
            <td>
                <div class="d-flex gap-1">
                    <button onclick="openEditRetard({{ $r->id }}, {{ $r->employe_id }}, '{{ $r->date }}', {{ $r->duree_min ?? 0 }}, '{{ addslashes($r->motif) }}')"
                            class="btn btn-warning btn-sm" style="font-size:10px;">✏️</button>
                    <form action="{{ route('rh.retards.destroy', $r->id) }}" method="POST" style="display:inline"
                          onsubmit="return confirm('Supprimer ce retard ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-outline-danger btn-sm" style="font-size:10px;">🗑</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Aucun retard enregistré</td></tr>
    @endforelse
    </tbody>
</table>
</div>

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeAllModals()"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">⏰ Enregistrer un retard</h5>
        <button onclick="closeAllModals()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form method="POST" action="{{ route('rh.retards.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Employé <span class="text-danger">*</span></label>
                <select name="employe_id" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                <input type="date" name="date" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Durée (minutes)</label>
                <input type="number" name="duree_min" class="form-control" min="1" max="480" placeholder="Ex: 30">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" class="form-control" placeholder="Ex: Embouteillages">
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAllModals()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </div>
    </form>
</div>

{{-- MODAL MODIFICATION --}}
<div class="modal-overlay" id="overlayEdit" onclick="closeAllModals()"></div>
<div class="modal-box" id="editModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">✏️ Modifier le retard</h5>
        <button onclick="closeAllModals()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form id="editRetardForm" method="POST" action="">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Employé</label>
                <select name="employe_id" class="form-control" id="edit_r_employe" required>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date</label>
                <input type="date" name="date" class="form-control" id="edit_r_date" required>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Durée (minutes)</label>
                <input type="number" name="duree_min" class="form-control" id="edit_r_duree" min="1">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" class="form-control" id="edit_r_motif">
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAllModals()" class="btn btn-light">Annuler</button>
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
function closeAllModals() {
    document.getElementById('overlayAdd').style.display = 'none';
    document.getElementById('overlayEdit').style.display = 'none';
    document.getElementById('addModal').style.display = 'none';
    document.getElementById('editModal').style.display = 'none';
}
function openEditRetard(id, employeId, date, duree, motif) {
    document.getElementById('editRetardForm').action = `/rh/retards/${id}`;
    document.getElementById('edit_r_employe').value  = employeId;
    document.getElementById('edit_r_date').value     = date;
    document.getElementById('edit_r_duree').value    = duree;
    document.getElementById('edit_r_motif').value    = motif;
    document.getElementById('overlayEdit').style.display = 'block';
    document.getElementById('editModal').style.display   = 'block';
}
</script>
@endsection