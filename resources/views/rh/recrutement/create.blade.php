@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🎯 Nouveau candidat</h2>
    <a href="{{ route('rh.recrutement.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<form method="POST" action="{{ route('rh.recrutement.store') }}" enctype="multipart/form-data">
@csrf

{{-- IDENTITÉ --}}
<div class="form-section">
    <h5>👤 Identité</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
            <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
            <input type="text" name="prenom" class="form-control" value="{{ old('prenom') }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Sexe</label>
            <select name="sexe" class="form-control">
                <option value="">--</option>
                <option value="M" {{ old('sexe')=='M'?'selected':'' }}>Masculin</option>
                <option value="F" {{ old('sexe')=='F'?'selected':'' }}>Féminin</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Date naissance</label>
            <input type="date" name="date_naissance" class="form-control" value="{{ old('date_naissance') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Lieu naissance</label>
            <input type="text" name="lieu_naissance" class="form-control" value="{{ old('lieu_naissance') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Nationalité</label>
            <input type="text" name="nationalite" class="form-control" value="{{ old('nationalite') }}">
        </div>
    </div>
</div>

{{-- CONTACT --}}
<div class="form-section">
    <h5>📞 Contact</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Téléphone</label>
            <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Adresse</label>
            <input type="text" name="adresse" class="form-control" value="{{ old('adresse') }}">
        </div>
    </div>
</div>

{{-- PROFESSIONNEL --}}
<div class="form-section">
    <h5>💼 Informations professionnelles</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Poste demandé</label>
            <input type="text" name="poste_demande" class="form-control" value="{{ old('poste_demande') }}" placeholder="Ex: Développeur PHP">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Niveau académique</label>
            <select name="niveau_academique" class="form-control">
                <option value="">--</option>
                @foreach(['BACC+5','BACC+4','BACC+3','BACC+2','BACC','BEP','CAP','BEPC','SANS'] as $n)
                    <option value="{{ $n }}" {{ old('niveau_academique')==$n?'selected':'' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Spécialité</label>
            <input type="text" name="specialite" class="form-control" value="{{ old('specialite') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Années expérience</label>
            <input type="number" name="annees_experience" class="form-control" value="{{ old('annees_experience', 0) }}" min="0">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Dernier poste occupé</label>
            <input type="text" name="dernier_poste" class="form-control" value="{{ old('dernier_poste') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Dernier employeur</label>
            <input type="text" name="dernier_employeur" class="form-control" value="{{ old('dernier_employeur') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Salaire souhaité (FCFA)</label>
            <input type="number" name="salaire_souhaite" class="form-control" value="{{ old('salaire_souhaite') }}" min="0" step="1000">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Compétences</label>
            <textarea name="competences" class="form-control" rows="3" placeholder="Ex: PHP, Laravel, JavaScript, gestion d'équipe...">{{ old('competences') }}</textarea>
        </div>
    </div>
</div>

{{-- SOURCE & DOCUMENTS --}}
<div class="form-section">
    <h5>📊 Source & Documents</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Source <span class="text-danger">*</span></label>
            <select name="source_id" class="form-control" required>
                <option value="">-- Choisir --</option>
                @foreach($sources as $s)
                    <option value="{{ $s->id }}" {{ old('source_id')==$s->id?'selected':'' }}>{{ $s->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de candidature <span class="text-danger">*</span></label>
            <input type="date" name="date_candidature" class="form-control" value="{{ old('date_candidature', now()->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Disponibilité</label>
            <input type="date" name="disponibilite" class="form-control" value="{{ old('disponibilite') }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">CV (PDF, DOC)</label>
            <input type="file" name="cv" class="form-control" accept=".pdf,.doc,.docx">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Lettre de motivation (PDF, DOC)</label>
            <input type="file" name="lettre_motivation" class="form-control" accept=".pdf,.doc,.docx">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Informations complémentaires...">{{ old('notes') }}</textarea>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('rh.recrutement.index') }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
</div>

</form>
@endsection