@extends('rh.layout')
@section('content')

<style>
.ret-table { width:100%; border-collapse:collapse; font-size:12px; background:white; }
.ret-table thead tr { background:#1e3a5f; color:white; }
.ret-table thead th { padding:10px 8px; font-weight:600; text-align:left; white-space:nowrap; }
.ret-table tbody tr:nth-child(even) { background:#f8fafc; }
.ret-table tbody tr:hover { background:#fffbeb; }
.ret-table tbody td { padding:8px; border-bottom:1px solid #e2e8f0; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:480px; max-height:90vh; overflow-y:auto; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">⏱️ Retards</h2>
    <button onclick="openModal()" class="btn btn-warning">+ Nouveau retard</button>
</div>

{{-- KPI --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #f59e0b;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:#f59e0b;">{{ $retards->count() }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Retards enregistrés</div>
        </div>
    </div>
    <div class="col-md-4">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #dc2626;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:#dc2626;">{{ number_format($totalDeductions, 0, ',', ' ') }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Total déductions ce mois (FCFA)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #16a34a;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:#16a34a;">{{ $retards->where('justifie', true)->count() }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Justifiés</div>
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
                    <option value="{{ $e->id }}" {{ request('employe_id')==$e->id?'selected':'' }}>
                        {{ $e->nom }} {{ $e->prenom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <input type="month" name="mois" class="form-control form-control-sm" value="{{ request('mois') }}">
        </div>
        <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrer</button>
            <a href="{{ route('rh.retards.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
    </div>
</form>

{{-- TABLEAU --}}
<div style="overflow-x:auto;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
<table class="ret-table">
    <thead>
        <tr>
            <th>Employé</th><th>Date</th><th>Heure arrivée</th>
            <th>Minutes retard</th><th>Déduction (FCFA)</th><th>Justification</th><th>Justifié</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($retards as $r)
        <tr>
            <td style="font-weight:600;">
                {{ $r->employe?->nom }} {{ $r->employe?->prenom }}<br>
                <small style="color:#64748b;">{{ $r->employe?->matricule }}</small>
            </td>
            <td>{{ $r->date?->format('d/m/Y') }}</td>
            <td>{{ $r->heure_arrivee ? substr($r->heure_arrivee, 0, 5) : '-' }}</td>
            <td>
                <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:8px;font-size:10px;font-weight:700;">
                    {{ $r->minutes_retard }} min
                </span>
            </td>
            <td style="color:#dc2626;font-weight:700;">{{ number_format($r->montant_deduction, 0, ',', ' ') }}</td>
            <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;" title="{{ $r->justification }}">
                {{ $r->justification ?? '-' }}
            </td>
            <td>
                @if($r->justifie)
                    <span style="background:#dcfce7;color:#15803d;padding:2px 8px;border-radius:8px;font-size:10px;font-weight:600;">✅ Oui</span>
                @else
                    <span style="background:#fee2e2;color:#b91c1c;padding:2px 8px;border-radius:8px;font-size:10px;font-weight:600;">❌ Non</span>
                @endif
            </td>
            <td>
                <form action="{{ route('rh.retards.destroy', $r->id) }}" method="POST" style="display:inline"
                      onsubmit="return confirm('Supprimer ce retard ?')">
                    @csrf @method('DELETE')
                    <button style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:14px;">🗑</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="8" class="text-center text-muted py-4">Aucun retard enregistré</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $retards->links() }}</div>

{{-- MODAL --}}
<div class="modal-overlay" id="overlay" onclick="closeModal()"></div>
<div class="modal-box" id="retModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;">⏱️ Nouveau retard</h5>
        <button onclick="closeModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label fw-semibold">Employé</label>
            <select id="r_employe" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($employes as $e)
                    <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Date</label>
            <input type="date" id="r_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Heure d'arrivée</label>
            <input type="time" id="r_heure" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Minutes de retard</label>
            <input type="number" id="r_minutes" class="form-control" min="1">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Déduction (FCFA)</label>
            <input type="number" id="r_montant" class="form-control" value="0" min="0">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Justification</label>
            <input type="text" id="r_justif" class="form-control" placeholder="Optionnel">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold d-flex align-items-center gap-2">
                <input type="checkbox" id="r_justifie"> Retard justifié
            </label>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="closeModal()" class="btn btn-light">Annuler</button>
        <button onclick="sauvegarderRetard()" class="btn btn-warning">💾 Enregistrer</button>
    </div>
</div>

@endsection
@section('scripts')
<script>
const csrf = '{{ csrf_token() }}';

function openModal() {
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('retModal').style.display = 'block';
}
function closeModal() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('retModal').style.display = 'none';
}

function sauvegarderRetard() {
    fetch('{{ route("rh.retards.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({
            employe_id:       document.getElementById('r_employe').value,
            date:             document.getElementById('r_date').value,
            heure_arrivee:    document.getElementById('r_heure').value || null,
            minutes_retard:   document.getElementById('r_minutes').value,
            montant_deduction:document.getElementById('r_montant').value,
            justification:    document.getElementById('r_justif').value || null,
            justifie:         document.getElementById('r_justifie').checked ? 1 : 0,
        })
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.message || 'Erreur'); });
}
</script>
@endsection