@extends('admin.layout')
@section('content')

<style>
.agent-card {
    background:white; border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    padding:16px 20px; margin-bottom:10px;
    display:flex; justify-content:space-between; align-items:center;
    border-left:4px solid #a855f7; transition:0.2s;
}
.agent-card:hover { box-shadow:0 4px 18px rgba(0,0,0,0.1); transform:translateY(-1px); }
.agent-card .name { font-weight:700; font-size:14px; color:#1e3a5f; }
.agent-card .meta { font-size:12px; color:#64748b; margin-top:2px; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:400px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🤝 Agents Commerciaux</h2>
    <button class="btn btn-primary" onclick="openCreate()">+ Nouvel agent</button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@forelse($agents as $agent)
    <div class="agent-card">
        <div>
            <div class="name">{{ $agent->nom }}</div>
            <div class="meta">📞 {{ $agent->numero ?? 'Pas de numéro' }}</div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-warning"
                    onclick="openEdit({{ $agent->id }}, '{{ addslashes($agent->nom) }}', '{{ $agent->numero ?? '' }}')">
                ✏️ Modifier
            </button>
            <form action="{{ route('agents.destroy', $agent->id) }}" method="POST" style="display:inline"
                  onsubmit="return confirm('Supprimer cet agent ?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger">🗑</button>
            </form>
        </div>
    </div>
@empty
    <div class="alert alert-info">Aucun agent commercial enregistré.</div>
@endforelse

{{-- MODAL CRÉER / MODIFIER --}}
<div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>
<div class="modal-box" id="agentModal">
    <h5 id="modalTitle" style="font-weight:700; color:#1e3a5f; margin-bottom:16px;">Nouvel agent</h5>
    <form method="POST" id="agentForm">
        @csrf
        <span id="methodField"></span>

        <div class="mb-3">
            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
            <input type="text" name="nom" id="agentNom" class="form-control" required placeholder="Nom complet">
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Numéro de téléphone</label>
            <input type="text" name="numero" id="agentNumero" class="form-control" placeholder="Ex: 6XXXXXXXX">
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeModal()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-success">💾 Enregistrer</button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
function openCreate() {
    document.getElementById('modalTitle').innerText  = 'Nouvel agent commercial';
    document.getElementById('agentNom').value        = '';
    document.getElementById('agentNumero').value     = '';
    document.getElementById('agentForm').action      = '{{ route('agents.store') }}';
    document.getElementById('methodField').innerHTML = '';
    document.getElementById('modalOverlay').style.display = 'block';
    document.getElementById('agentModal').style.display   = 'block';
}

function openEdit(id, nom, numero) {
    document.getElementById('modalTitle').innerText  = 'Modifier l\'agent';
    document.getElementById('agentNom').value        = nom;
    document.getElementById('agentNumero').value     = numero;
    document.getElementById('agentForm').action      = `/admin/agents/${id}`;
    document.getElementById('methodField').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    document.getElementById('modalOverlay').style.display = 'block';
    document.getElementById('agentModal').style.display   = 'block';
}

function closeModal() {
    document.getElementById('modalOverlay').style.display = 'none';
    document.getElementById('agentModal').style.display   = 'none';
}
</script>
@endsection