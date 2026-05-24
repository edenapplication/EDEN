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
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- FILTRES --}}
<form method="GET" class="d-flex gap-2 mb-4 flex-wrap">
    <select name="employe_id" class="form-control form-control-sm" style="max-width:220px;" onchange="this.form.submit()">
        <option value="">👤 Tous les employés</option>
        @foreach($employes as $e)
            <option value="{{ $e->id }}" {{ request('employe_id') == $e->id ? 'selected':'' }}>{{ $e->nom }} {{ $e->prenom }}</option>
        @endforeach
    </select>
    <select name="type" class="form-control form-control-sm" style="max-width:160px;" onchange="this.form.submit()">
        <option value="">📋 Tous types</option>
        <option value="pret"    {{ request('type') === 'pret'    ? 'selected':'' }}>Prêt</option>
        <option value="acompte" {{ request('type') === 'acompte' ? 'selected':'' }}>Acompte</option>
    </select>
    <select name="statut" class="form-control form-control-sm" style="max-width:160px;" onchange="this.form.submit()">
        <option value="">🔄 Tous statuts</option>
        <option value="en_cours"   {{ request('statut') === 'en_cours'   ? 'selected':'' }}>En cours</option>
        <option value="rembourse"  {{ request('statut') === 'rembourse'  ? 'selected':'' }}>Remboursé</option>
        <option value="annule"     {{ request('statut') === 'annule'     ? 'selected':'' }}>Annulé</option>
    </select>
    <a href="{{ route('rh.prets.index') }}" class="btn btn-outline-secondary btn-sm">Reset</a>
</form>

{{-- LISTE --}}
@forelse($prets as $p)
    @php
        $pct    = $p->montant > 0 ? round(($p->montant_rembourse / $p->montant) * 100) : 0;
        $reste  = max(0, $p->montant - $p->montant_rembourse);
        $color  = $pct >= 100 ? '#16a34a' : ($pct >= 50 ? '#f59e0b' : '#dc2626');
    @endphp
    <div class="pret-card {{ $p->type === 'acompte' ? 'acompte' : '' }} {{ $p->statut === 'rembourse' ? 'rembourse' : '' }}">
        <div class="d-flex justify-content-between align-items-start">
            <div>
                <div style="font-weight:700;font-size:14px;color:#1e3a5f;">
                    {{ $p->employe?->nom }} {{ $p->employe?->prenom }}
                    <span style="font-size:11px;color:#64748b;margin-left:6px;">{{ $p->employe?->matricule }}</span>
                </div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">
                    <span style="background:{{ $p->type==='acompte'?'#f3e8ff':'#dbeafe'}};color:{{ $p->type==='acompte'?'#7c3aed':'#1d4ed8'}};padding:2px 8px;border-radius:6px;font-weight:600;font-size:11px;">
                        {{ $p->type === 'acompte' ? 'Acompte' : 'Prêt' }}
                    </span>
                    &nbsp;📅 {{ $p->date_demande }}
                    @if($p->motif) &nbsp;— {{ $p->motif }} @endif
                </div>
            </div>
            <div class="text-end">
                <div style="font-size:16px;font-weight:800;color:#1e3a5f;">{{ number_format($p->montant, 0, ',', ' ') }} FCFA</div>
                <span style="font-size:11px;padding:2px 8px;border-radius:6px;font-weight:600;background:{{ $p->statut==='rembourse'?'#dcfce7':($p->statut==='annule'?'#fee2e2':'#fef3c7')}};color:{{ $p->statut==='rembourse'?'#15803d':($p->statut==='annule'?'#b91c1c':'#92400e')}};">
                    {{ $p->statut === 'rembourse' ? '✅ Remboursé' : ($p->statut === 'annule' ? '🚫 Annulé' : '🔄 En cours') }}
                </span>
            </div>
        </div>

        <div class="prog-bar">
            <div class="prog-fill" style="width:{{ $pct }}%;background:{{ $color }};"></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-top:4px;">
            <span>Remboursé : <strong style="color:#16a34a;">{{ number_format($p->montant_rembourse, 0, ',', ' ') }} FCFA</strong></span>
            <span>Reste : <strong style="color:#dc2626;">{{ number_format($reste, 0, ',', ' ') }} FCFA</strong></span>
            <span style="color:{{ $color }};font-weight:700;">{{ $pct }}%</span>
        </div>
        @if($p->mensualite && $p->duree_mois)
            <div style="font-size:11px;color:#64748b;margin-top:4px;">
                📆 {{ $p->duree_mois }} mois — mensualité : <strong>{{ number_format($p->mensualite, 0, ',', ' ') }} FCFA</strong>
            </div>
        @endif

        @if($p->statut === 'en_cours')
        <div class="d-flex gap-2 mt-3">
            {{-- ✅ FORMULAIRE REMBOURSEMENT --}}
            <form action="{{ route('rh.prets.update', $p->id) }}" method="POST" class="d-flex gap-2 flex-grow-1">
                @csrf @method('PUT')
                <input type="number" name="montant_rembourse_ajout"
                       class="form-control form-control-sm"
                       placeholder="Montant remboursé (FCFA)"
                       min="1" max="{{ $reste }}" step="1" required>
                <button type="submit" class="btn btn-success btn-sm" style="white-space:nowrap;">
                    ✅ Enregistrer
                </button>
            </form>
            {{-- Annuler --}}
            <form action="{{ route('rh.prets.update', $p->id) }}" method="POST">
                @csrf @method('PUT')
                <input type="hidden" name="statut" value="annule">
                <button type="submit" class="btn btn-outline-danger btn-sm"
                        onclick="return confirm('Annuler ce prêt ?')">🚫</button>
            </form>
        </div>
        @endif

        {{-- Supprimer --}}
        @if($p->statut !== 'en_cours')
        <form action="{{ route('rh.prets.destroy', $p->id) }}" method="POST" class="mt-2"
              onsubmit="return confirm('Supprimer ce prêt ?')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm" style="font-size:11px;">🗑 Supprimer</button>
        </form>
        @endif
    </div>
@empty
    <div class="text-muted text-center py-5" style="font-size:14px;">Aucun prêt enregistré</div>
@endforelse

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeModal('addModal')"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">🏦 Nouveau prêt / acompte</h5>
        <button onclick="closeModal('addModal')" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <form method="POST" action="{{ route('rh.prets.store') }}">
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
                <select name="type" class="form-control" required id="pret_type_select" onchange="toggleDuree()">
                    <option value="pret">Prêt</option>
                    <option value="acompte">Acompte</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Montant (FCFA) <span class="text-danger">*</span></label>
                <input type="number" name="montant" class="form-control" min="1" required>
            </div>
            <div class="col-md-6" id="duree_field">
                <label class="form-label fw-semibold">Durée (mois)</label>
                <input type="number" name="duree_mois" class="form-control" min="1" max="60">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date demande <span class="text-danger">*</span></label>
                <input type="date" name="date_debut" class="form-control" value="{{ date('Y-m-d') }}" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" class="form-control" placeholder="Ex: Frais médicaux">
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeModal('addModal')" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
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
function closeModal(id) {
    document.getElementById('overlayAdd').style.display = 'none';
    document.getElementById(id).style.display = 'none';
}
function toggleDuree() {
    const type = document.getElementById('pret_type_select').value;
    document.getElementById('duree_field').style.display = type === 'pret' ? 'block' : 'none';
}
toggleDuree();
</script>
@endsection