@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">➕ Nouvel employé</h2>
    <a href="{{ route('rh.employes.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
</div>

<form method="POST" action="{{ route('rh.employes.store') }}">
@csrf

{{-- IDENTITÉ --}}
<div class="form-section">
    <h5>👤 Identité</h5>
    <div class="row g-3">
        <div class="col-md-2">
            <label class="form-label fw-semibold">Matricule</label>
            <input type="text" name="matricule" class="form-control" value="{{ $matricule }}" readonly
                   style="background:#f8fafc;font-weight:700;color:#1d4ed8;">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
            <input type="text" name="nom" class="form-control" value="{{ old('nom') }}" required placeholder="Nom de famille">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Prénom <span class="text-danger">*</span></label>
            <input type="text" name="prenom" class="form-control" value="{{ old('prenom') }}" required placeholder="Prénom(s)">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Sexe <span class="text-danger">*</span></label>
            <select name="sexe" class="form-control" required>
                <option value="M" {{ old('sexe')==='M'?'selected':'' }}>Masculin</option>
                <option value="F" {{ old('sexe')==='F'?'selected':'' }}>Féminin</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Date naissance</label>
            <input type="date" name="date_naissance" class="form-control" value="{{ old('date_naissance') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Lieu de naissance</label>
            <input type="text" name="lieu_naissance" class="form-control" value="{{ old('lieu_naissance') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Nationalité</label>
            <input type="text" name="nationalite" class="form-control" value="{{ old('nationalite') }}" placeholder="Ex: Camerounaise">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">N° CNI</label>
            <input type="text" name="numero_cni" class="form-control" value="{{ old('numero_cni') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">NIU</label>
            <input type="text" name="niu" class="form-control" value="{{ old('niu') }}">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Origines</label>
            <input type="text" name="origines" class="form-control" value="{{ old('origines') }}" placeholder="Ex: BAMILEKE">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Situation matrimoniale</label>
            <select name="situation_matrimoniale" class="form-control">
                @foreach($options['situations'] as $s)
                    <option value="{{ $s }}" {{ old('situation_matrimoniale')===$s?'selected':'' }}>{{ $s }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">Nb enfants</label>
            <input type="number" name="nb_enfants" class="form-control" value="{{ old('nb_enfants', 0) }}" min="0">
        </div>
        <div class="col-md-2">
            <label class="form-label fw-semibold">État de santé</label>
            <select name="etat_sante" class="form-control">
                @foreach(['BON','ASTHMATIQUE','DIABETIQUE','AUTRE'] as $e)
                    <option value="{{ $e }}" {{ old('etat_sante')===$e?'selected':'' }}>{{ $e }}</option>
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
            <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="exemple@domaine.com">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Adresse</label>
            <input type="text" name="adresse" class="form-control" value="{{ old('adresse') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Personne à contacter</label>
            <input type="text" name="personne_a_contacter" class="form-control" value="{{ old('personne_a_contacter') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Tél. urgence</label>
            <input type="text" name="tel_urgence" class="form-control" value="{{ old('tel_urgence') }}">
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
                    <option value="{{ $d->id }}" {{ old('direction_id')==$d->id?'selected':'' }}>{{ $d->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Service</label>
            <select name="service_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($options['services'] as $s)
                    <option value="{{ $s->id }}" {{ old('service_id')==$s->id?'selected':'' }}>{{ $s->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Agence / Site</label>
            <select name="agence_site_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($options['agences'] ?? [] as $a)
                    <option value="{{ $a->id }}" {{ old('agence_site_id')==$a->id?'selected':'' }}>
                        {{ $a->nom }} @if($a->ville) - {{ $a->ville }} @endif
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Intitulé du poste</label>
            <input type="text" name="intitule_poste" class="form-control" value="{{ old('intitule_poste') }}" placeholder="Ex: DIRECTEUR GENERAL">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Type contrat <span class="text-danger">*</span></label>
            <select name="type_contrat" class="form-control" required>
                @foreach($options['contrats'] as $c)
                    <option value="{{ $c }}" {{ old('type_contrat')===$c?'selected':'' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Catégorie</label>
            <select name="categorie" class="form-control">
                <option value="">--</option>
                @foreach($options['categories'] as $c)
                    <option value="{{ $c }}" {{ old('categorie')===$c?'selected':'' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Niveau / Échelon</label>
            <select name="niveau_chelon_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($options['niveaux_chelons'] ?? [] as $n)
                    <option value="{{ $n->id }}" {{ old('niveau_chelon_id')==$n->id?'selected':'' }}>
                        {{ $n->code }} - {{ $n->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Date d'intégration <span class="text-danger">*</span></label>
            <input type="date" name="date_integration" class="form-control" value="{{ old('date_integration') }}" required>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Date de prise de fonction</label>
            <input type="date" name="date_prise_fonction" class="form-control" value="{{ old('date_prise_fonction') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Fin période d'essai</label>
            <input type="date" name="date_fin_periode_essai" class="form-control" value="{{ old('date_fin_periode_essai') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Vague de paiement</label>
            <select name="vague_paiement" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($options['vagues'] as $v)
                    <option value="{{ $v }}" {{ old('vague_paiement')===$v?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Mode de paiement</label>
            <select name="mode_paiement" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($options['modes_paiement'] ?? ['VIREMENT', 'CHEQUE', 'ESPECES'] as $m)
                    <option value="{{ $m }}" {{ old('mode_paiement')===$m?'selected':'' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Responsable hiérarchique</label>
            <select name="responsable_hierarchique_id" class="form-control">
                <option value="">-- Aucun --</option>
                @foreach($options['responsables'] ?? [] as $r)
                    <option value="{{ $r->id }}" {{ old('responsable_hierarchique_id')==$r->id?'selected':'' }}>
                        {{ $r->nom }} {{ $r->prenom }} ({{ $r->matricule }})
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Salaire de base (FCFA) <span class="text-danger">*</span></label>
            <input type="number" name="salaire_base" class="form-control" value="{{ old('salaire_base', 0) }}" min="0" required>
        </div>
    </div>
</div>

{{-- INFORMATIONS CNPS --}}
<div class="form-section">
    <h5>🏛️ CNPS & Cotisations</h5>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label fw-semibold">N° CNPS</label>
            <input type="text" name="numero_cnps" class="form-control" value="{{ old('numero_cnps') }}" placeholder="Ex: 123456789">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Date d'affiliation</label>
            <input type="date" name="date_affiliation_cnps" class="form-control" value="{{ old('date_affiliation_cnps') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Centre CNPS</label>
            <input type="text" name="centre_cnps" class="form-control" value="{{ old('centre_cnps') }}" placeholder="Ex: Yaoundé">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Situation affiliation</label>
            <select name="situation_affiliation_cnps" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($options['situations_cnps'] ?? ['affilie', 'non_affilie', 'en_cours', 'radie'] as $s)
                    <option value="{{ $s }}" {{ old('situation_affiliation_cnps')===$s?'selected':'' }}>
                        {{ ucfirst(str_replace('_', ' ', $s)) }}
                    </option>
                @endforeach
            </select>
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
                    <option value="{{ $n }}" {{ old('niveau_academique')===$n?'selected':'' }}>{{ $n }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Spécialité</label>
            <input type="text" name="specialite_academique" class="form-control" value="{{ old('specialite_academique') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Diplôme de recrutement</label>
            <input type="text" name="diplome_recrutement" class="form-control" value="{{ old('diplome_recrutement') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Poste précédent</label>
            <input type="text" name="exp_poste_precedent" class="form-control" value="{{ old('exp_poste_precedent') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Entreprise précédente</label>
            <input type="text" name="entreprise_precedente" class="form-control" value="{{ old('entreprise_precedente') }}">
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Durée expérience</label>
            <input type="text" name="duree_exp_precedente" class="form-control" value="{{ old('duree_exp_precedente') }}" placeholder="Ex: 2 ans">
        </div>
    </div>
</div>

{{-- NOTES --}}
<div class="form-section">
    <h5>📝 Notes</h5>
    <textarea name="notes" class="form-control" rows="3" placeholder="Remarques ou informations supplémentaires...">{{ old('notes') }}</textarea>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('rh.employes.index') }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-primary px-5">💾 Enregistrer</button>
</div>

</form>
@endsection