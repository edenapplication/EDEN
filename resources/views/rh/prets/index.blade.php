@extends('rh.layout')
@section('content')

<style>
.pret-table { width:100%; border-collapse:collapse; font-size:12px; background:white; }
.pret-table thead tr { background:#1e3a5f; color:white; }
.pret-table thead th { padding:10px 8px; font-weight:600; text-align:left; white-space:nowrap; }
.pret-table tbody tr:nth-child(even) { background:#f8fafc; }
.pret-table tbody tr:hover { background:#eff6ff; }
.pret-table tbody td { padding:8px; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:480px; max-height:90vh; overflow-y:auto; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🏦 Prêts & Acomptes</h2>
    <button onclick="openModal()" class="btn btn-primary">+ Nouveau</button>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #7c3aed;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:#7c3aed;">{{ number_format($totalEnCours, 0, ',', ' ') }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Total prêts en cours (FCFA)</div>
        </div>
    </div>
    <div class="col-md-4">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #1d4ed8;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:#1d4ed8;">{{ $prets->where('statut','en_cours')->count() }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Dossiers en cours</div>
        </div>
    </div>
    <div class="col-md-4">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #16a34a;text-align:center;">
            <div style="font-size:20px;font-weight:800;color:#16a34a;">{{ $prets->where('statut','remboursé')->count() }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;">Remboursés</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" style="background:white;border-radius:12px;padding:14px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:16px;">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <input type="text" name="employe" class="form-control form-control-sm"
                   placeholder="🔍 Nom employé..." value="{{ request('employe') }}">
        </div>
        <div class="col-md-2">
            <select name="type" class="form-control form-control-sm">
                <option value="">Tous types</option>
                <option value="pret"    {{ request('type')==='pret'   ?'selected':'' }}>Prêt</option>
                <option value="acompte" {{ request('type')==='acompte'?'selected':'' }}>Acompte</option>
            </select>
        </div>
        <div class="col-md-2">
            <select name="statut" class="form-control form-control-sm">
                <option value="">Tous statuts</option>
                <option value="en_cours"   {{ request('statut')==='en_cours'  ?'selected':'' }}>En cours</option>
                <option value="remboursé"  {{ request('statut')==='remboursé' ?'selected':'' }}>Remboursé</option>
                <option value="annulé"     {{ request('statut')==='annulé'    ?'selected':'' }}>Annulé</option>
            </select>
        </div>
        <div class="col-md-3 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrer</button>
            <a href="{{ route('rh.prets.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
    </div>
</form>

{{-- TABLEAU --}}
<div style="overflow-x:auto;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
<table class="pret-table">
    <thead>
        <tr>
            <th>Employé</th><th>Type</th><th>Montant</th><th>Durée</th>
            <th>Mensualité</th><th>Remboursé</th><th>Restant</th>
            <th>Date début</th><th>Statut</th><th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($prets as $p)
        <tr>
            <td style="font-weight:600;">{{ $p->employe?->nom }} {{ $p->employe?->prenom }}<br>
                <small style="color:#64748b;">{{ $p->employe?->matricule }}</small>
            </td>
            <td>
                <span style="font-size:10px;padding:2px 8px;border-radius:8px;font-weight:600;background:{{ $p->type==='pret'?'#dbeafe':'#f3e8ff'}};color:{{ $p->type==='pret'?'#1d4ed8':'#7c3aed'}};">
                    {{ ucfirst($p->type) }}
                </span>
            </td>
            <td style="font-weight:700;">{{ number_format($p->montant, 0, ',', ' ') }}</td>
            <td>{{ $p->duree_mois ? $p->duree_mois.' mois' : '-' }}</td>
            <td>{{ $p->mensualite ? number_format($p->mensualite, 0, ',', ' ') : '-' }}</td>
            <td style="color:#16a34a;font-weight:600;">{{ number_format($p->montant_rembourse, 0, ',', ' ') }}</td>
            <td style="color:#dc2626;font-weight:600;">{{ number_format($p->montant - $p->montant_rembourse, 0, ',', ' ') }}</td>
            <td>{{ $p->date_debut?->format('d/m/Y') }}</td>
            <td>
                <span style="font-size:10px;padding:2px 8px;border-radius:8px;font-weight:600;background:{{ $p->statut==='remboursé'?'#dcfce7':($p->statut==='annulé'?'#fee2e2':'#fef9c3')}};color:{{ $p->statut==='remboursé'?'#15803d':($p->statut==='annulé'?'#b91c1c':'#92400e')}};">
                    {{ $p->statut }}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <button onclick="ouvrirRemboursement({{ $p->id }}, {{ $p->montant - $p->montant_rembourse }})"
                            style="background:#dcfce7;color:#15803d;border:none;border-radius:4px;padding:2px 8px;font-size:10px;cursor:pointer;">
                        💳 Rembourser
                    </button>
                    <form action="{{ route('rh.prets.destroy', $p->id) }}" method="POST" style="display:inline"
                          onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:12px;">🗑</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="10" class="text-center text-muted py-4">Aucun prêt enregistré</td></tr>
    @endforelse
    </tbody>
</table>
</div>
<div class="mt-3">{{ $prets->links() }}</div>

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlay" onclick="closeModal()"></div>
<div class="modal-box" id="pretModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;">🏦 Nouveau prêt / acompte</h5>
        <button onclick="closeModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label fw-semibold">Employé</label>
            <select id="p_employe" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($employes as $e)
                    <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Type</label>
            <select id="p_type" class="form-control">
                <option value="acompte">Acompte</option>
                <option value="pret">Prêt</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Montant (FCFA)</label>
            <input type="number" id="p_montant" class="form-control" min="1">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Durée (mois)</label>
            <input type="number" id="p_duree" class="form-control" min="1" placeholder="Laisser vide si acompte">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Date début</label>
            <input type="date" id="p_date" class="form-control" value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Notes</label>
            <textarea id="p_notes" class="form-control" rows="2"></textarea>
        </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="closeModal()" class="btn btn-light">Annuler</button>
        <button onclick="sauvegarderPret()" class="btn btn-primary">💾 Enregistrer</button>
    </div>
</div>

{{-- MODAL REMBOURSEMENT --}}
<div class="modal-box" id="rembModal" style="display:none;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;">💳 Enregistrer un remboursement</h5>
        <button onclick="closeRemb()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <input type="hidden" id="remb_id">
    <div class="mb-3">
        <label class="form-label fw-semibold">Montant remboursé (FCFA)</label>
        <input type="number" id="remb_montant" class="form-control" min="1">
        <small class="text-muted" id="remb_restant"></small>
    </div>
    <div class="d-flex justify-content-end gap-2">
        <button onclick="closeRemb()" class="btn btn-light">Annuler</button>
        <button onclick="sauvegarderRemboursement()" class="btn btn-success">💾 Enregistrer</button>
    </div>
</div>

@endsection
@section('scripts')
<script>
const csrf = '{{ csrf_token() }}';

function openModal() {
    document.getElementById('overlay').style.display = 'block';
    document.getElementById('pretModal').style.display = 'block';
}
function closeModal() {
    document.getElementById('overlay').style.display = 'none';
    document.getElementById('pretModal').style.display = 'none';
}

function sauvegarderPret() {
    fetch('{{ route("rh.prets.store") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({
            employe_id: document.getElementById('p_employe').value,
            type:       document.getElementById('p_type').value,
            montant:    document.getElementById('p_montant').value,
            duree_mois: document.getElementById('p_duree').value || null,
            date_debut: document.getElementById('p_date').value,
            notes:      document.getElementById('p_notes').value,
        })
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.message || 'Erreur'); });
}

function ouvrirRemboursement(id, restant) {
    document.getElementById('remb_id').value      = id;
    document.getElementById('remb_montant').value = '';
    document.getElementById('remb_restant').innerText = 'Restant : ' + new Intl.NumberFormat('fr-FR').format(restant) + ' FCFA';
    document.getElementById('overlay').style.display  = 'block';
    document.getElementById('rembModal').style.display = 'block';
}
function closeRemb() {
    document.getElementById('overlay').style.display   = 'none';
    document.getElementById('rembModal').style.display = 'none';
}

function sauvegarderRemboursement() {
    const id      = document.getElementById('remb_id').value;
    const montant = document.getElementById('remb_montant').value;
    fetch(`/admin/rh/prets/${id}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
        body: JSON.stringify({ montant_rembourse_ajout: montant })
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); else alert(data.message || 'Erreur'); });
}
</script>
@endsection