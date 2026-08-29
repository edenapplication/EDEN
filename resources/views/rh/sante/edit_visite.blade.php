@extends('rh.layout')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">✏️ Modifier la visite médicale</h2>
    <a href="{{ route('rh.sante.visites.show', $visite->id) }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<form method="POST" action="{{ route('rh.sante.visites.update', $visite->id) }}" enctype="multipart/form-data">
@csrf
@method('PUT')

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                👤 Employé
            </div>
            <div class="card-body">
                <select name="employe_id" class="form-control" required>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}" {{ $visite->employe_id==$e->id?'selected':'' }}>
                            {{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})
                        </option>
                    @endforeach
                </select>
                <div class="mt-2">
                    <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-control" required>
                        <option value="embauche" {{ $visite->type=='embauche'?'selected':'' }}>Visite d'embauche</option>
                        <option value="periodique" {{ $visite->type=='periodique'?'selected':'' }}>Visite périodique</option>
                        <option value="reprise" {{ $visite->type=='reprise'?'selected':'' }}>Visite de reprise</option>
                        <option value="accident" {{ $visite->type=='accident'?'selected':'' }}>Visite suite à accident</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                📅 Détails
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date_visite" class="form-control" value="{{ $visite->date_visite->format('Y-m-d') }}" required>
                </div>
                <div class="row g-2">
                    <div class="col-6">
                        <label class="form-label fw-semibold">Médecin</label>
                        <input type="text" name="medecin_nom" class="form-control" value="{{ $visite->medecin_nom }}">
                    </div>
                    <div class="col-6">
                        <label class="form-label fw-semibold">Téléphone</label>
                        <input type="text" name="medecin_tel" class="form-control" value="{{ $visite->medecin_tel }}">
                    </div>
                </div>
                <div class="mt-2">
                    <label class="form-label fw-semibold">Établissement</label>
                    <input type="text" name="etablissement" class="form-control" value="{{ $visite->etablissement }}">
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                📋 Résultats
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label fw-semibold">Aptitude <span class="text-danger">*</span></label>
                    <select name="aptitude" class="form-control" required>
                        <option value="apte" {{ $visite->aptitude=='apte'?'selected':'' }}>Apte</option>
                        <option value="apte_avec_restriction" {{ $visite->aptitude=='apte_avec_restriction'?'selected':'' }}>Apte avec restrictions</option>
                        <option value="inapte" {{ $visite->aptitude=='inapte'?'selected':'' }}>Inapte</option>
                    </select>
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Prochaine visite</label>
                    <input type="date" name="prochaine_visite" class="form-control" value="{{ $visite->prochaine_visite?->format('Y-m-d') }}">
                </div>
                <div class="mb-2">
                    <label class="form-label fw-semibold">Restrictions</label>
                    <textarea name="restrictions" class="form-control" rows="2">{{ $visite->restrictions }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                📎 Certificat
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <label class="form-label fw-semibold">Certificat</label>
                    <input type="file" name="certificat" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                    @if($visite->certificat_path)
                        <small class="text-success">✓ Certificat actuel présent</small>
                    @endif
                </div>
                <div>
                    <label class="form-label fw-semibold">Observations</label>
                    <textarea name="observations" class="form-control" rows="3">{{ $visite->observations }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <a href="{{ route('rh.sante.visites.show', $visite->id) }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Mettre à jour</button>
</div>

</form>
@endsection