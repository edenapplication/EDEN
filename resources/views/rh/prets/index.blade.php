@extends('rh.layout')
@section('content')

<style>
.pret-card { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:12px; border-left:4px solid #1d4ed8; }
.pret-card.acompte { border-left-color:#7c3aed; }
.pret-card.rembourse { border-left-color:#16a34a; opacity:0.75; }

.prog-bar { height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden; margin-top:6px; }
.prog-fill { height:100%; border-radius:4px; transition:width 0.4s; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:440px; max-height:90vh; overflow-y:auto; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🏦 Prêts & Acomptes</h2>
    <button onclick="openModal('addModal')" class="btn btn-primary">+ Nouveau prêt / acompte</button>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- FILTRES --}}
<form method="GET" class="d-flex gap-2 mb-4 flex-wrap">
    <select name="employe_id" class="form-control form-control-sm" onchange="this.form.submit()">
        <option value="">Tous les employés</option>
        @foreach($employes as $e)
            <option value="{{ $e->id }}" {{ request('employe_id') == $e->id ? 'selected':'' }}>
                {{ $e->nom }} {{ $e->prenom }}
            </option>
        @endforeach
    </select>

    <select name="type" class="form-control form-control-sm" onchange="this.form.submit()">
        <option value="">Tous types</option>
        <option value="pret" {{ request('type') === 'pret' ? 'selected':'' }}>pret</option>
        <option value="acompte" {{ request('type') === 'acompte' ? 'selected':'' }}>acompte</option>
    </select>

    <select name="statut" class="form-control form-control-sm" onchange="this.form.submit()">
        <option value="">Tous statuts</option>
        <option value="en_cours" {{ request('statut') === 'en_cours' ? 'selected':'' }}>en_cours</option>
        <option value="rembourse" {{ request('statut') === 'rembourse' ? 'selected':'' }}>rembourse</option>
        <option value="annule" {{ request('statut') === 'annule' ? 'selected':'' }}>annule</option>
    </select>

    <a href="{{ route('rh.prets.index') }}" class="btn btn-outline-secondary btn-sm">reset</a>
</form>

{{-- LISTE --}}
@forelse($prets as $p)
@php
    $pct = $p->montant > 0 ? round(($p->montant_rembourse / $p->montant) * 100) : 0;
    $reste = max(0, $p->montant - $p->montant_rembourse);
    $color = $pct >= 100 ? '#16a34a' : ($pct >= 50 ? '#f59e0b' : '#dc2626');
@endphp

<div class="pret-card {{ $p->type }} {{ $p->statut === 'rembourse' ? 'rembourse' : '' }}">

    <div class="d-flex justify-content-between">
        <div>
            <div style="font-weight:700;">
                {{ $p->employe?->nom }} {{ $p->employe?->prenom }}
            </div>
            <div style="font-size:12px;color:#64748b;">
                {{ $p->type }} — {{ $p->motif }}
            </div>
        </div>

        <div style="text-align:right;">
            <div style="font-weight:800;">
                {{ number_format($p->montant, 0, ',', ' ') }} FCFA
            </div>

            <span style="font-size:11px;padding:2px 8px;border-radius:6px;
                background:
                {{ $p->statut==='rembourse'?'#dcfce7':($p->statut==='annule'?'#fee2e2':'#fef3c7') }};
                color:
                {{ $p->statut==='rembourse'?'#15803d':($p->statut==='annule'?'#b91c1c':'#92400e') }};
            ">
                {{ $p->statut }}
            </span>
        </div>
    </div>

    <div class="prog-bar">
        <div class="prog-fill" style="width:{{ $pct }}%;background:{{ $color }}"></div>
    </div>

    <div style="display:flex;justify-content:space-between;font-size:11px;">
        <span>rembourse: {{ $p->montant_rembourse }}</span>
        <span>reste: {{ $reste }}</span>
        <span>{{ $pct }}%</span>
    </div>

    {{-- ACTIONS --}}
    @if($p->statut === 'en_cours')
    <div class="d-flex gap-2 mt-2">

        <form action="{{ route('rh.prets.update', $p->id) }}" method="POST" class="d-flex gap-2">
            @csrf @method('PUT')
            <input type="number" name="montant_rembourse_ajout" class="form-control form-control-sm" min="1" required>
            <button class="btn btn-success btn-sm">ok</button>
        </form>

        <form action="{{ route('rh.prets.update', $p->id) }}" method="POST">
            @csrf @method('PUT')
            <input type="hidden" name="statut" value="annule">
            <button class="btn btn-danger btn-sm">annule</button>
        </form>

    </div>
    @endif

</div>

@empty
<div class="text-center text-muted">aucun pret</div>
@endforelse


{{-- MODAL --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeModal('addModal')"></div>

<div class="modal-box" id="addModal">
<form method="POST" action="{{ route('rh.prets.store') }}">
@csrf

<select name="employe_id" class="form-control mb-2">
@foreach($employes as $e)
<option value="{{ $e->id }}">{{ $e->nom }}</option>
@endforeach
</select>

<select name="type" class="form-control mb-2">
<option value="pret">pret</option>
<option value="acompte">acompte</option>
</select>

<input type="number" name="montant" class="form-control mb-2" required>
<input type="number" name="duree_mois" class="form-control mb-2">
<input type="date" name="date_debut" class="form-control mb-2">

<button class="btn btn-primary w-100">enregistrer</button>
</form>
</div>

@endsection


@section('scripts')
<script>
function openModal(){ document.getElementById('overlayAdd').style.display='block'; document.getElementById('addModal').style.display='block'; }
function closeModal(){ document.getElementById('overlayAdd').style.display='none'; document.getElementById('addModal').style.display='none'; }
</script>
@endsection