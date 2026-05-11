@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">✏️ Modifier — {{ $employe->nom }} {{ $employe->prenom }}</h2>
    <a href="{{ route('rh.employes.show', $employe->id) }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
</div>

<form method="POST" action="{{ route('rh.employes.update', $employe->id) }}">
@csrf
@method('PUT')

{{-- IDENTITÉ --}}
<div class="form-section">
    <h5>👤 Identité</h5>
    <div class="row g-3">
        <div class="col-md-2">
            <label class="form-label fw-semibold">Matricule</label>
            <input type="text" class="form-control" value="{{ $employe->matricule }}" readonly
                   style="background:#f8fafc;font-weight:700;color:#1d4ed8;">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
            <input type="text" name="nom" class="form-control" value="{{ old('nom', $employe->nom) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
            <input type="text" name="prenom" class="form-control" value="{{ old('prenom', $employe->prenom) }}" required>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Sexe <span class="text-danger">*</span></label>
            <select name="sexe" class="form-control" required>
                <option value="M" {{ $employe->sexe==='M'?'selected':'' }}>Masculin</option>
                <option value="F" {{ $employe->sexe==='F'?'selected':'' }}>Féminin</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Date naissance</label>
            <input type="date" name="date_naissance" class="form-control" value="{{ old('date_naissance', $employe->date_naissance?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Lieu de naissance</label>
            <input type="text" name="lieu_naissance" class="form-control" value="{{ old('lieu_naissance', $employe->lieu_naissance) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">N° CNI</label>
            <input type="text" name="numero_cni" class="form-control" value="{{ old('numero_cni', $employe->numero_cni) }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">NIU</label>
            <input type="text" name="niu" class="form-control" value="{{ old('niu', $employe->niu) }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Origines</label>
            <input type="text" name="origines" class="form-control" value="{{ old('origines', $employe->origines) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Situation matrimoniale</label>
            <select name="situation_matrimoniale" class="form-control">
                @foreach($options['situations'] as $s)
                    <option value="{{ $s }}" {{ $employe->situation_matrimoniale===$s?'selected':'' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Nb enfants</label>
            <input type="number" name="nb_enfants" class="form-control" value="{{ old('nb_enfants', $employe->nb_enfants) }}" min="0">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">État de santé</label>
            <select name="etat_sante" class="form-control">
                @foreach(['BON','ASTHMATIQUE','DIABETIQUE','AUTRE'] as $e)
                    <option value="{{ $e }}" {{ $employe->etat_sante===$e?'selected':'' }}>{{ $e }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>

{{-- CONTACT --}}
<div class="form-section">
    <h5>📞 Contact</h5>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Téléphone</label>
            <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $employe->telephone) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Adresse</label>
            <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $employe->adresse) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Personne à contacter</label>
            <input type="text" name="personne_a_contacter" class="form-control" value="{{ old('personne_a_contacter', $employe->personne_a_contacter) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Tél. urgence</label>
            <input type="text" name="tel_urgence" class="form-control" value="{{ old('tel_urgence', $employe->tel_urgence) }}">
        </div>
    </div>
</div>

{{-- POSTE & CONTRAT --}}
<div class="form-section">
    <h5>🏢 Poste & Contrat</h5>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Direction</label>
            <select name="direction_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($options['directions'] as $d)
                    <option value="{{ $d->id }}" {{ $employe->direction_id==$d->id?'selected':'' }}>{{ $d->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Service</label>
            <select name="service_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($options['services'] as $s)
                    <option value="{{ $s->id }}" {{ $employe->service_id==$s->id?'selected':'' }}>{{ $s->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Intitulé du poste</label>
            <input type="text" name="intitule_poste" class="form-control" value="{{ old('intitule_poste', $employe->intitule_poste) }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Type contrat <span class="text-danger">*</span></label>
            <select name="type_contrat" class="form-control" required>
                @foreach($options['contrats'] as $c)
                    <option value="{{ $c }}" {{ $employe->type_contrat===$c?'selected':'' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Catégorie</label>
            <select name="categorie" class="form-control">
                <option value="">--</option>
                @foreach($options['categories'] as $c)
                    <option value="{{ $c }}" {{ $employe->categorie===$c?'selected':'' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Date d'intégration <span class="text-danger">*</span></label>
            <input type="date" name="date_integration" class="form-control"
                   value="{{ old('date_integration', $employe->date_integration?->format('Y-m-d')) }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Vague de paiement</label>
            <select name="vague_paiement" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($options['vagues'] as $v)
                    <option value="{{ $v }}" {{ $employe->vague_paiement===$v?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Salaire de base (FCFA) <span class="text-danger">*</span></label>
            <input type="number" name="salaire_base" class="form-control"
                   value="{{ old('salaire_base', $employe->salaire_base) }}" min="0" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Date de sortie</label>
            <input type="date" name="date_sortie" class="form-control"
                   value="{{ old('date_sortie', $employe->date_sortie?->format('Y-m-d')) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Cause de départ</label>
            <input type="text" name="cause_depart" class="form-control" value="{{ old('cause_depart', $employe->cause_depart) }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Solde congés</label>
            <input type="number" name="solde_conges" class="form-control" value="{{ old('solde_conges', $employe->solde_conges) }}" min="0">
        </div>
    </div>
</div>

{{-- FORMATION --}}
<div class="form-section">
    <h5>🎓 Formation académique</h5>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Niveau académique</label>
            <select name="niveau_academique" class="form-control">
                <option value="">--</option>
                @foreach($options['niveaux'] as $n)
                    <option value="{{ $n }}" {{ $employe->niveau_academique===$n?'selected':'' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Spécialité</label>
            <input type="text" name="specialite_academique" class="form-control" value="{{ old('specialite_academique', $employe->specialite_academique) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Diplôme de recrutement</label>
            <input type="text" name="diplome_recrutement" class="form-control" value="{{ old('diplome_recrutement', $employe->diplome_recrutement) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Poste précédent</label>
            <input type="text" name="exp_poste_precedent" class="form-control" value="{{ old('exp_poste_precedent', $employe->exp_poste_precedent) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Entreprise précédente</label>
            <input type="text" name="entreprise_precedente" class="form-control" value="{{ old('entreprise_precedente', $employe->entreprise_precedente) }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Durée expérience</label>
            <input type="text" name="duree_exp_precedente" class="form-control" value="{{ old('duree_exp_precedente', $employe->duree_exp_precedente) }}" placeholder="Ex: 2 ans">
        </div>
    </div>
</div>

{{-- NOTES --}}
<div class="form-section">
    <h5>📝 Notes</h5>
    <textarea name="notes" class="form-control" rows="3">{{ old('notes', $employe->notes) }}</textarea>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('rh.employes.show', $employe->id) }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-primary px-5">💾 Enregistrer les modifications</button>
</div>

</form>
@endsection