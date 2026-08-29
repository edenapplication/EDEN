@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">📄 Nouvelle déclaration CNPS</h2>
    <a href="{{ route('rh.cnps.declarations') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
</div>

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

@if($existe)
    <div class="alert alert-warning">
        ⚠️ Une déclaration existe déjà pour la période <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</strong>.
        <a href="{{ route('rh.cnps.declarations.show', $existe->id ?? 0) }}" class="alert-link">Voir la déclaration existante</a>
    </div>
@endif

<form method="POST" action="{{ route('rh.cnps.declarations.store') }}">
@csrf

<div class="form-section">
    <h5>📅 Période de déclaration</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Période <span class="text-danger">*</span></label>
            <input type="month" name="periode" class="form-control" value="{{ old('periode', $periode) }}" required>
            <small class="text-muted">Mois et année de la déclaration</small>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de déclaration</label>
            <input type="date" name="date_declaration" class="form-control" value="{{ old('date_declaration', now()->format('Y-m-d')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date d'échéance</label>
            <input type="date" name="date_echeance" class="form-control" value="{{ old('date_echeance', now()->addDays(15)->format('Y-m-d')) }}">
        </div>
    </div>
</div>

<div class="form-section">
    <h5>📝 Observations</h5>
    <textarea name="observations" class="form-control" rows="3" placeholder="Remarques sur cette déclaration...">{{ old('observations') }}</textarea>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('rh.cnps.declarations') }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-primary px-5">💾 Créer la déclaration</button>
</div>

</form>
@endsection