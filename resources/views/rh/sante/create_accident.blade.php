@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">⚠️ Déclaration d'accident de travail</h2>
    <a href="{{ route('rh.sante.accidents') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<form method="POST" action="{{ route('rh.sante.accidents.store') }}" enctype="multipart/form-data">
@csrf

<div class="form-section">
    <h5>👤 Informations générales</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Employé <span class="text-danger">*</span></label>
            <select name="employe_id" class="form-control" required>
                <option value="">-- Choisir --</option>
                @foreach($employes as $e)
                    <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
            <input type="date" name="date_accident" class="form-control" value="{{ old('date_accident', now()->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Heure</label>
            <input type="time" name="heure" class="form-control" value="{{ old('heure') }}">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Lieu <span class="text-danger">*</span></label>
            <input type="text" name="lieu" class="form-control" value="{{ old('lieu') }}" placeholder="Ex: Atelier de production" required>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Circonstances</label>
            <input type="text" name="circonstances" class="form-control" value="{{ old('circonstances') }}" placeholder="Circonstances de l'accident">
        </div>
    </div>
</div>

<div class="form-section">
    <h5>📋 Détails de l'accident</h5>
    <div class="row g-3">
        <div class="col-12">
            <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
            <textarea name="description" class="form-control" rows="4" placeholder="Décrire l'accident en détail..." required>{{ old('description') }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Nature des blessures</label>
            <input type="text" name="nature_blessures" class="form-control" value="{{ old('nature_blessures') }}" placeholder="Ex: Fracture, brûlure, etc.">
        </div>
    </div>
</div>

<div class="form-section">
    <h5>👀 Témoins</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Témoin 1 - Nom</label>
            <input type="text" name="temoin1_nom" class="form-control" value="{{ old('temoin1_nom') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Témoin 1 - Téléphone</label>
            <input type="text" name="temoin1_tel" class="form-control" value="{{ old('temoin1_tel') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Témoin 2 - Nom</label>
            <input type="text" name="temoin2_nom" class="form-control" value="{{ old('temoin2_nom') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Témoin 2 - Téléphone</label>
            <input type="text" name="temoin2_tel" class="form-control" value="{{ old('temoin2_tel') }}">
        </div>
    </div>
</div>

<div class="form-section">
    <h5>📎 Documents</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Rapport d'accident</label>
            <input type="file" name="rapport" class="form-control" accept=".pdf,.doc,.docx">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Constat</label>
            <input type="file" name="constat" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('rh.sante.accidents') }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-danger"><i class="bi bi-save"></i> Déclarer</button>
</div>

</form>
@endsection