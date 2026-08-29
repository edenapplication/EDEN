@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">📄 Nouveau contrat</h2>
    <a href="{{ route('rh.contrats.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
</div>

<form method="POST" action="{{ route('rh.contrats.store') }}">
@csrf

{{-- EMPLOYÉ & TYPE --}}
<div class="form-section">
    <h5>👤 Employé & Type de contrat</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Employé <span class="text-danger">*</span></label>
            <select name="employe_id" class="form-control" required>
                <option value="">-- Choisir --</option>
                @foreach($employes as $e)
                    <option value="{{ $e->id }}" {{ old('employe_id')==$e->id?'selected':'' }}>
                        {{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Type de contrat <span class="text-danger">*</span></label>
            <select name="type_contrat_id" class="form-control" required>
                <option value="">-- Choisir --</option>
                @foreach($typesContrat as $t)
                    <option value="{{ $t->id }}" {{ old('type_contrat_id')==$t->id?'selected':'' }}>
                        {{ $t->nom }} @if($t->duree_maximale_mois) (max {{ $t->duree_maximale_mois }} mois) @endif
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
            <input type="date" name="date_debut" class="form-control" value="{{ old('date_debut', now()->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de fin</label>
            <input type="date" name="date_fin" class="form-control" value="{{ old('date_fin') }}">
            <small class="text-muted">Laisser vide pour un CDI</small>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Période d'essai (jours)</label>
            <input type="number" name="periode_essai_jours" class="form-control" value="{{ old('periode_essai_jours', 0) }}" min="0">
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
                    <option value="{{ $d->id }}" {{ old('direction_id')==$d->id?'selected':'' }}>{{ $d->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Service</label>
            <select name="service_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}" {{ old('service_id')==$s->id?'selected':'' }}>
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
                    <option value="{{ $p->id }}" {{ old('poste_id')==$p->id?'selected':'' }}>
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
                    <option value="{{ $a->id }}" {{ old('agence_site_id')==$a->id?'selected':'' }}>
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
            <input type="number" name="salaire_base" class="form-control" value="{{ old('salaire_base', 0) }}" min="0" required>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Mode de paiement</label>
            <select name="mode_paiement" class="form-control">
                <option value="">-- Choisir --</option>
                <option value="VIREMENT" {{ old('mode_paiement')=='VIREMENT'?'selected':'' }}>Virement bancaire</option>
                <option value="CHEQUE" {{ old('mode_paiement')=='CHEQUE'?'selected':'' }}>Chèque</option>
                <option value="ESPECES" {{ old('mode_paiement')=='ESPECES'?'selected':'' }}>Espèces</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Responsable hiérarchique</label>
            <input type="text" name="responsable_hierarchique" class="form-control" value="{{ old('responsable_hierarchique') }}" placeholder="Ex: M. DUPONT Jean">
        </div>
    </div>
</div>

{{-- COMPLÉMENTS --}}
<div class="form-section">
    <h5>📝 Informations complémentaires</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Lieu de travail</label>
            <input type="text" name="lieu_travail" class="form-control" value="{{ old('lieu_travail') }}" placeholder="Ex: Yaoundé, Douala...">
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Horaires</label>
            <input type="text" name="horaires" class="form-control" value="{{ old('horaires') }}" placeholder="Ex: 08h00 - 17h30">
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Conditions particulières</label>
            <textarea name="conditions_particulieres" class="form-control" rows="3" placeholder="Clauses particulières, avantages...">{{ old('conditions_particulieres') }}</textarea>
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Notes</label>
            <textarea name="notes" class="form-control" rows="2" placeholder="Remarques internes...">{{ old('notes') }}</textarea>
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
                <option value="en_attente" {{ old('statut')=='en_attente'?'selected':'' }}>En attente</option>
                <option value="valide" {{ old('statut')=='valide'?'selected':'' }}>Validé (en attente d'activation)</option>
                <option value="actif" {{ old('statut')=='actif'?'selected':'' }}>Actif</option>
            </select>
            <small class="text-muted">"Actif" mettra automatiquement à jour les informations de l'employé.</small>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de signature</label>
            <input type="date" name="date_signature" class="form-control" value="{{ old('date_signature') }}">
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Est renouvelable</label>
            <select name="est_renouvelable" class="form-control">
                <option value="1" {{ old('est_renouvelable', 1)==1?'selected':'' }}>Oui</option>
                <option value="0" {{ old('est_renouvelable', 1)==0?'selected':'' }}>Non</option>
            </select>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('rh.contrats.index') }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-primary px-5">💾 Enregistrer</button>
</div>

</form>
@endsection