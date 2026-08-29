@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🩺 Nouvelle visite médicale</h2>
    <a href="{{ route('rh.sante.visites') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<form method="POST" action="{{ route('rh.sante.visites.store') }}" enctype="multipart/form-data">
@csrf

<div class="form-section">
    <h5>👤 Employé & Type</h5>
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
        <div class="col-md-6">
            <label class="form-label fw-semibold">Type de visite <span class="text-danger">*</span></label>
            <select name="type" class="form-control" required>
                <option value="embauche">Visite d'embauche</option>
                <option value="periodique">Visite périodique</option>
                <option value="reprise">Visite de reprise</option>
                <option value="accident">Visite suite à accident</option>
            </select>
        </div>
    </div>
</div>

<div class="form-section">
    <h5>📅 Détails de la visite</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de visite <span class="text-danger">*</span></label>
            <input type="date" name="date_visite" class="form-control" value="{{ old('date_visite', now()->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Médecin</label>
            <input type="text" name="medecin_nom" class="form-control" value="{{ old('medecin_nom') }}" placeholder="Nom du médecin">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Téléphone médecin</label>
            <input type="text" name="medecin_tel" class="form-control" value="{{ old('medecin_tel') }}" placeholder="Téléphone">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Établissement</label>
            <input type="text" name="etablissement" class="form-control" value="{{ old('etablissement') }}" placeholder="Nom de l'établissement">
        </div>
    </div>
</div>

<div class="form-section">
    <h5>📋 Résultats</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Aptitude <span class="text-danger">*</span></label>
            <select name="aptitude" class="form-control" required>
                <option value="apte">Apte</option>
                <option value="apte_avec_restriction">Apte avec restrictions</option>
                <option value="inapte">Inapte</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Prochaine visite</label>
            <input type="date" name="prochaine_visite" class="form-control" value="{{ old('prochaine_visite') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Certificat</label>
            <input type="file" name="certificat" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Restrictions</label>
            <textarea name="restrictions" class="form-control" rows="2" placeholder="Restrictions éventuelles...">{{ old('restrictions') }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Observations</label>
            <textarea name="observations" class="form-control" rows="3" placeholder="Observations médicales...">{{ old('observations') }}</textarea>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('rh.sante.visites') }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
</div>

</form>
@endsection