@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">✏️ Modifier le contrat — {{ $contrat->numero_contrat }}</h2>
    <a href="{{ route('rh.contrats.show', $contrat->id) }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
</div>

<form method="POST" action="{{ route('rh.contrats.update', $contrat->id) }}">
@csrf
@method('PUT')

{{-- EMPLOYÉ & TYPE --}}
<div class="form-section">
    <h5>👤 Employé & Type de contrat</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Employé</label>
            <input type="text" class="form-control" value="{{ $contrat->employe?->nom }} {{ $contrat->employe?->prenom }} ({{ $contrat->employe?->matricule }})" readonly>
            <input type="hidden" name="employe_id" value="{{ $contrat->employe_id }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Type de contrat <span class="text-danger">*</span></label>
            <select name="type_contrat_id" class="form-control" required>
                @foreach($typesContrat as $t)
                    <option value="{{ $t->id }}" {{ $contrat->type_contrat_id==$t->id?'selected':'' }}>
                        {{ $t->nom }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- PÉRIODE --}}
<div class="form-section">
    <h5>📅 Période du contrat</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de début <span class="text-danger">*</span></label>
            <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', $contrat->date_debut?->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de fin</label>
            <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin', $contrat->date_fin?->format('Y-m-d')) }}">
            <small class="text-muted">Laisser vide pour un CDI</small>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Période d'essai (jours)</label>
            <input type="number" name="periode_essai_jours" class="form-control" value="{{ old('periode_essai_jours', $contrat->periode_essai_jours) }}" min="0">
            <small class="text-muted">0 = pas de période d'essai</small>
        </div>
    </div>
</div>

{{-- POSTE & AFFECTATION --}}
<div class="form-section">
    <h5>🏢 Poste & Affectation</h5>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Direction</label>
            <select name="direction_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($directions as $d)
                    <option value="{{ $d->id }}" {{ $contrat->direction_id==$d->id?'selected':'' }}>{{ $d->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Service</label>
            <select name="service_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}" {{ $contrat->service_id==$s->id?'selected':'' }}>
                        {{ $s->direction?->nom }} - {{ $s->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Poste</label>
            <select name="poste_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($postes as $p)
                    <option value="{{ $p->id }}" {{ $contrat->poste_id==$p->id?'selected':'' }}>
                        {{ $p->code }} - {{ $p->intitule }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Agence / Site</label>
            <select name="agence_site_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($agences as $a)
                    <option value="{{ $a->id }}" {{ $contrat->agence_site_id==$a->id?'selected':'' }}>
                        {{ $a->nom }} @if($a->ville) - {{ $a->ville }} @endif
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- SALAIRE & MODE PAIEMENT --}}
<div class="form-section">
    <h5>💰 Rémunération</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Salaire de base (FCFA) <span class="text-danger">*</span></label>
            <input type="number" name="salaire_base" class="form-control" value="{{ old('salaire_base', $contrat->salaire_base) }}" min="0" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Mode de paiement</label>
            <select name="mode_paiement" class="form-control">
                <option value="">-- Choisir --</option>
                <option value="VIREMENT" {{ $contrat->mode_paiement=='VIREMENT'?'selected':'' }}>Virement bancaire</option>
                <option value="CHEQUE" {{ $contrat->mode_paiement=='CHEQUE'?'selected':'' }}>Chèque</option>
                <option value="ESPECES" {{ $contrat->mode_paiement=='ESPECES'?'selected':'' }}>Espèces</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Responsable hiérarchique</label>
            <input type="text" name="responsable_hierarchique" class="form-control" value="{{ old('responsable_hierarchique', $contrat->responsable_hierarchique) }}" placeholder="Ex: M. DUPONT Jean">
        </div>
    </div>
</div>

{{-- COMPLÉMENTS --}}
<div class="form-section">
    <h5>📝 Informations complémentaires</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Lieu de travail</label>
            <input type="text" name="lieu_travail" class="form-control" value="{{ old('lieu_travail', $contrat->lieu_travail) }}">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Horaires</label>
            <input type="text" name="horaires" class="form-control" value="{{ old('horaires', $contrat->horaires) }}">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Conditions particulières</label>
            <textarea name="conditions_particulieres" class="form-control" rows="3">{{ old('conditions_particulieres', $contrat->conditions_particulieres) }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Notes</label>
            <textarea name="notes" class="form-control" rows="2">{{ old('notes', $contrat->notes) }}</textarea>
        </div>
    </div>
</div>

{{-- STATUT --}}
<div class="form-section">
    <h5>📌 Statut du contrat</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
            <select name="statut" class="form-control" required>
                @foreach(\App\Models\RH\Contrat::STATUTS_LABELS as $k => $v)
                    <option value="{{ $k }}" {{ $contrat->statut==$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de signature</label>
            <input type="date" name="date_signature" class="form-control" value="{{ old('date_signature', $contrat->date_signature?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Est renouvelable</label>
            <select name="est_renouvelable" class="form-control">
                <option value="1" {{ $contrat->est_renouvelable?'selected':'' }}>Oui</option>
                <option value="0" {{ !$contrat->est_renouvelable?'selected':'' }}>Non</option>
            </select>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('rh.contrats.show', $contrat->id) }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-primary px-5">💾 Enregistrer</button>
</div>

</form>
@endsection