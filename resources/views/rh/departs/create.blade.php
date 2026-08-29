@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🚪 Nouveau départ</h2>
    <a href="{{ route('rh.departs.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
</div>

<form method="POST" action="{{ route('rh.departs.store') }}">
@csrf

{{-- EMPLOYÉ & MOTIF --}}
<div class="form-section">
    <h5>👤 Employé & Motif</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Employé <span class="text-danger">*</span></label>
            <select name="employe_id" class="form-control" required>
                <option value="">-- Choisir --</option>
                @foreach($employes as $e)
                    <option value="{{ $e->id }}" {{ old('employe_id', $employe?->id)==$e->id?'selected':'' }}>
                        {{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Motif du départ</label>
            <select name="motif_depart_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($motifs as $m)
                    <option value="{{ $m->id }}" {{ old('motif_depart_id')==$m->id?'selected':'' }}>
                        {{ $m->nom }} @if($m->necessite_preavis) (préavis: {{ $m->preavis_jours }}j) @endif
                    </option>
                @endforeach
            </select>
            <small class="text-muted">Ou saisissez un motif personnalisé ci-dessous</small>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Motif personnalisé</label>
            <input type="text" name="motif_libre" class="form-control" value="{{ old('motif_libre') }}" placeholder="Ex: Départ volontaire pour raisons personnelles...">
        </div>
    </div>
</div>

{{-- DATES --}}
<div class="form-section">
    <h5>📅 Dates</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de départ <span class="text-danger">*</span></label>
            <input type="date" name="date_depart" class="form-control" value="{{ old('date_depart', now()->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de notification</label>
            <input type="date" name="date_notification" class="form-control" value="{{ old('date_notification') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de préavis</label>
            <input type="date" name="date_preavis" class="form-control" value="{{ old('date_preavis') }}">
            <small class="text-muted">Doit être avant ou égale à la date de départ</small>
        </div>
    </div>
</div>

{{-- OBSERVATIONS --}}
<div class="form-section">
    <h5>📝 Observations</h5>
    <textarea name="observations" class="form-control" rows="3" placeholder="Remarques sur ce départ...">{{ old('observations') }}</textarea>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('rh.departs.index') }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-primary px-5">💾 Enregistrer</button>
</div>

</form>
@endsection