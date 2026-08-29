@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">✏️ Modifier le départ</h2>
    <a href="{{ route('rh.departs.show', $depart->id) }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
</div>

<form method="POST" action="{{ route('rh.departs.update', $depart->id) }}">
@csrf
@method('PUT')

{{-- EMPLOYÉ & MOTIF --}}
<div class="form-section">
    <h5>👤 Employé & Motif</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Employé</label>
            <input type="text" class="form-control" value="{{ $depart->employe?->nom }} {{ $depart->employe?->prenom }} ({{ $depart->employe?->matricule }})" readonly>
            <input type="hidden" name="employe_id" value="{{ $depart->employe_id }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Motif du départ</label>
            <select name="motif_depart_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($motifs as $m)
                    <option value="{{ $m->id }}" {{ $depart->motif_depart_id==$m->id?'selected':'' }}>
                        {{ $m->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Motif personnalisé</label>
            <input type="text" name="motif_libre" class="form-control" value="{{ old('motif_libre', $depart->motif_libre) }}" placeholder="Ex: Départ volontaire...">
        </div>
    </div>
</div>

{{-- DATES --}}
<div class="form-section">
    <h5>📅 Dates</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de départ <span class="text-danger">*</span></label>
            <input type="date" name="date_depart" class="form-control" value="{{ old('date_depart', $depart->date_depart?->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de notification</label>
            <input type="date" name="date_notification" class="form-control" value="{{ old('date_notification', $depart->date_notification?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de préavis</label>
            <input type="date" name="date_preavis" class="form-control" value="{{ old('date_preavis', $depart->date_preavis?->format('Y-m-d')) }}">
        </div>
    </div>
</div>

{{-- STATUT --}}
<div class="form-section">
    <h5>📌 Statut</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
            <select name="statut" class="form-control" required>
                @foreach(\App\Models\RH\Depart::STATUTS as $k => $v)
                    <option value="{{ $k }}" {{ $depart->statut==$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
            <small class="text-muted">"Terminé" archivra automatiquement l'employé</small>
        </div>
    </div>
</div>

{{-- OBSERVATIONS --}}
<div class="form-section">
    <h5>📝 Observations</h5>
    <textarea name="observations" class="form-control" rows="3">{{ old('observations', $depart->observations) }}</textarea>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('rh.departs.show', $depart->id) }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-primary px-5">💾 Mettre à jour</button>
</div>

</form>
@endsection