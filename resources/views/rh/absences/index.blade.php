@extends('rh.layout')
@section('content')

<style>
.abs-table { width:100%; border-collapse:collapse; font-size:12px; background:white; }
.abs-table thead tr { background:#1e3a5f; color:white; }
.abs-table thead th { padding:10px 8px; font-weight:600; text-align:left; white-space:nowrap; }
.abs-table tbody tr:nth-child(even) { background:#f8fafc; }
.abs-table tbody tr:hover { background:#eff6ff; }
.abs-table tbody td { padding:8px; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:540px; max-height:90vh; overflow-y:auto; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🗓️ Suivi des absences</h2>
    <button onclick="openModal()" class="btn btn-primary">+ Nouvelle absence</button>
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
            <select name="type_absence" class="form-control form-control-sm">
                <option value="">Tous types</option>
                @foreach(['Congés','Maladie','Permission personnelle','Mission','Maternité','Paternité','Décès','Sans solde','Autre'] as $t)
                    <option value="{{ $t }}" {{ request('type_absence')===$t?'selected':'' }}>{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="statut" class="form-control form-control-sm">
                <option value="">Tous statuts</option>
                <option value="en_attente"  {{ request('statut')==='en_attente' ?'selected':'' }}>En attente</option>
                <option value="approuvé"    {{ request('statut')==='approuvé'   ?'selected':'' }}>Approuvé</option>
                <option value="refusé"      {{ request('statut')==='refusé'     ?'selected':'' }}>Refusé</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="month" name="mois" class="form-control form-control-sm" value="{{ request('mois') }}">
        </div>
        <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrer</button>
            <a href="{{ route('rh.absences.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
    </div>
</form>

{{-- TABLEAU --}}
<div style="overflow-x:auto;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
<table class="abs-table">
    <thead>
        <tr>
            <th>Référence</th><th>Employé</th><th>Direction</th><th>Type</th>
            <th>Début</th><th>Fin</th><th>Reprise</th><th>Jours</th>
            <th>Motif</th><th>Statut</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($absences as $a)
        <tr>
            <td style="color:#1d4ed8;font-weight:700;">{{ $a->reference ?? '-' }}</td>
            <td style="font-weight:600;">{{ $a->employe?->nom }} {{ $a->employe?->prenom }}</td>
            <td>{{ $a->employe?->direction?->nom ?? '-' }}</td>
            <td>
                <span style="font-size:10px;padding:2px 8px;border-radius:8px;background:#f1f5f9;color:#475569;font-weight:600;">
                    {{ $a->type_absence }}
                </span>
            </td>
            <td>{{ $a->date_debut?->format('d/m/Y') }}</td>
            <td>{{ $a->date_fin?->format('d/m/Y') }}</td>
            <td>{{ $a->date_reprise?->format('d/m/Y') ?? '-' }}</td>
            <td style="font-weight:700;color:#1d4ed8;">{{ $a->nombre_jours }}</td>
            <td style="max-width:120px;overflow:hidden;text-overflow:ellipsis;" title="{{ $a->motif }}">{{ $a->motif ?? '-' }}</td>
            <td>
                <span style="font-size:10px;padding:2px 8px;border-radius:8px;font-weight:600;background:{{ $a->statut==='approuvé'?'#dcfce7':($a->statut==='refusé'?'#fee2e2':'#fef9c3')}};color:{{ $a->statut==='approuvé'?'#15803d':($a->statut==='refusé'?'#b91c1c':'#92400e')}};">
                    {{ $a->statut }}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    @if($a->statut === 'en_attente')
                        <button onclick="approuver({{ $a->id }})" style="background:#dcfce7;color:#15803d;border:none;border-radius:4px;padding:2px 6px;font-size:10px;cursor:pointer;">✅</button>
                        <button onclick="refuser({{ $a->id }})" style="background:#fee2e2;color:#b91c1c;border:none;border-radius:4px;padding:2px 6px;font-size:10px;cursor:pointer;">❌</button>
                    @endif
                    <button onclick="supprimerAbs({{ $a->id }})" style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:12px;">🗑</button>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="11" class="text-center text-muted py-4">Aucune absence</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $absences->links() }}</div>

{{-- MODAL AJOUT ABSENCE --}}
<div class="modal-overlay" id="overlay" onclick="closeModal()"></div>
<div class="modal-box" id="absModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;">🗓️ Nouvelle absence</h5>
        <button onclick="closeModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label fw-semibold">Employé</label>
            <select id="abs_employe" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($employes as $e)
                    <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Type d'absence</label>
            <select id="abs_type" class="form-control">
                @foreach(['Congés','Maladie','Permission personnelle','Mission','Maternité','Paternité','Décès','Sans solde','Autre'] as $t)
                    <option value="{{ $t }}">{{ $t }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Type de journée</label>
            <select id="abs_journee" class="form-control">
                <option value="journée complète">Journée complète</option>
                <option value="demi-journée">Demi-journée</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Date début</label>
            <input type="date" id="abs_debut" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Date fin</label>
            <input type="date" id="abs_fin" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Date reprise</label>
            <input type="date" id="abs_reprise" class="form-control">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Motif</label>
            <textarea id="abs_motif" class="form-control" rows="2"></textarea>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Observations</label>
            <input type="text" id="abs_obs" class="form-control" placeholder="Visa RH, remarques...">
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="closeModal()" class="btn btn-light">Annuler</button>
        <button onclick="sauvegarderAbs()" class="btn btn-primary">💾 Enregistrer</button>
    </div>
</div>

@endsection
@section('scripts')
<script>
const csrf = '{{ csrf_token() }}';

function openModal() {
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('absModal').style.display = 'block';
}
function closeModal() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('absModal').style.display = 'none';
}

function sauvegarderAbs() {
    fetch('{{ route("rh.absences.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({
            employe_id:   document.getElementById('abs_employe').value,
            type_absence: document.getElementById('abs_type').value,
            type_journee: document.getElementById('abs_journee').value,
            date_debut:   document.getElementById('abs_debut').value,
            date_fin:     document.getElementById('abs_fin').value,
            date_reprise: document.getElementById('abs_reprise').value || null,
            motif:        document.getElementById('abs_motif').value,
            observations: document.getElementById('abs_obs').value,
        })
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.message || 'Erreur'); });
}

function approuver(id) {
    if (!confirm('Approuver cette absence ?')) return;
    fetch(`/admin/rh/absences/${id}/approuver`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }
    }).then(() => location.reload());
}

function refuser(id) {
    if (!confirm('Refuser cette absence ?')) return;
    fetch(`/admin/rh/absences/${id}/refuser`, {
        method: 'POST', headers: { 'X-CSRF-TOKEN': csrf }
    }).then(() => location.reload());
}

function supprimerAbs(id) {
    if (!confirm('Supprimer ?')) return;
    fetch(`/admin/rh/absences/${id}`, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf }
    }).then(() => location.reload());
}
</script>
@endsection