@extends('rh.layout')
@section('content')

<style>
.form-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.form-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
.is-invalid { border-color: #dc2626 !important; background-color: #fef2f2; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">✏️ Modifier le contrat — {{ $contrat->numero_contrat }}</h2>
    <a href="{{ route('rh.contrats.show', $contrat->id) }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
</div>

{{-- ═══════════════════════════════════════════════════════════
     ✅ MESSAGES D'ERREURS (validation + session)
     ═══════════════════════════════════════════════════════════ --}}
@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" style="border-left:4px solid #dc2626;">
        <strong>⚠️ Erreur(s) de validation :</strong>
        <ul class="mb-0 mt-2" style="font-size:13px;">
            @foreach($errors->all() as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" style="border-left:4px solid #dc2626;">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" style="border-left:4px solid #16a34a;">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<form method="POST" action="{{ route('rh.contrats.update', $contrat->id) }}">
@csrf
@method('PUT')

{{-- ═══════════════════════════════════════════════════════
     EMPLOYÉ & TYPE DE CONTRAT
     ═══════════════════════════════════════════════════════ --}}
<div class="form-section">
    <h5>👤 Employé & Type de contrat</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Employé</label>
            <input type="text" class="form-control"
                   value="{{ $contrat->employe?->nom }} {{ $contrat->employe?->prenom }} ({{ $contrat->employe?->matricule }})"
                   readonly
                   style="background:#f1f5f9;cursor:not-allowed;">
            <input type="hidden" name="employe_id" value="{{ $contrat->employe_id }}">
            <small class="text-muted">🔒 L'employé ne peut pas être modifié après création.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Type de contrat <span class="text-danger">*</span></label>
            <select name="type_contrat_id" id="type_contrat_id"
                    class="form-control @error('type_contrat_id') is-invalid @enderror" required>
                @foreach($typesContrat as $t)
                    <option value="{{ $t->id }}"
                            data-duree-max="{{ $t->duree_maximale_mois ?? 0 }}"
                            {{ old('type_contrat_id', $contrat->type_contrat_id)==$t->id?'selected':'' }}>
                        {{ $t->nom }} @if($t->duree_maximale_mois) (max {{ $t->duree_maximale_mois }} mois) @endif
                    </option>
                @endforeach
            </select>
            @error('type_contrat_id')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     PÉRIODE DU CONTRAT
     ═══════════════════════════════════════════════════════ --}}
<div class="form-section">
    <h5>📅 Période du contrat</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de début <span class="text-danger">*</span></label>
            <input type="date" name="date_debut"
                   class="form-control @error('date_debut') is-invalid @enderror"
                   value="{{ old('date_debut', $contrat->date_debut?->format('Y-m-d')) }}" required>
            @error('date_debut')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4" id="bloc-date-fin">
            <label class="form-label fw-semibold">Date de fin</label>
            <input type="date" name="date_fin" id="date_fin"
                   class="form-control @error('date_fin') is-invalid @enderror"
                   value="{{ old('date_fin', $contrat->date_fin?->format('Y-m-d')) }}">
            <small class="text-muted" id="date-fin-hint">Laisser vide pour un CDI</small>
            @error('date_fin')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Période d'essai (jours)</label>
            <input type="number" name="periode_essai_jours"
                   class="form-control @error('periode_essai_jours') is-invalid @enderror"
                   value="{{ old('periode_essai_jours', $contrat->periode_essai_jours) }}"
                   min="0" max="365">
            <small class="text-muted">0 = pas de période d'essai</small>
            @error('periode_essai_jours')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     POSTE & AFFECTATION
     ═══════════════════════════════════════════════════════ --}}
<div class="form-section">
    <h5>🏢 Poste & Affectation</h5>
    <div class="row g-3">
        <div class="col-md-3">
            <label class="form-label fw-semibold">Direction</label>
            <select name="direction_id" id="direction_id"
                    class="form-control @error('direction_id') is-invalid @enderror">
                <option value="">-- Choisir --</option>
                @foreach($directions as $d)
                    <option value="{{ $d->id }}"
                            {{ old('direction_id', $contrat->direction_id)==$d->id?'selected':'' }}>
                        {{ $d->nom }}
                    </option>
                @endforeach
            </select>
            @error('direction_id')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Service</label>
            <select name="service_id" id="service_id"
                    class="form-control @error('service_id') is-invalid @enderror">
                <option value="">-- Choisir --</option>
                @foreach($services as $s)
                    <option value="{{ $s->id }}"
                            data-direction-id="{{ $s->direction_id }}"
                            {{ old('service_id', $contrat->service_id)==$s->id?'selected':'' }}>
                        {{ $s->direction?->nom }} - {{ $s->nom }}
                    </option>
                @endforeach
            </select>
            @error('service_id')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Poste</label>
            <select name="poste_id" class="form-control @error('poste_id') is-invalid @enderror">
                <option value="">-- Choisir --</option>
                @foreach($postes as $p)
                    <option value="{{ $p->id }}"
                            {{ old('poste_id', $contrat->poste_id)==$p->id?'selected':'' }}>
                        {{ $p->code }} - {{ $p->intitule }}
                    </option>
                @endforeach
            </select>
            @error('poste_id')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-3">
            <label class="form-label fw-semibold">Agence / Site</label>
            <select name="agence_site_id" class="form-control @error('agence_site_id') is-invalid @enderror">
                <option value="">-- Choisir --</option>
                @foreach($agences as $a)
                    <option value="{{ $a->id }}"
                            {{ old('agence_site_id', $contrat->agence_site_id)==$a->id?'selected':'' }}>
                        {{ $a->nom }} @if($a->ville) - {{ $a->ville }} @endif
                    </option>
                @endforeach
            </select>
            @error('agence_site_id')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     RÉMUNÉRATION
     ═══════════════════════════════════════════════════════ --}}
<div class="form-section">
    <h5>💰 Rémunération</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Salaire de base (FCFA) <span class="text-danger">*</span></label>
            <input type="number" name="salaire_base"
                   class="form-control @error('salaire_base') is-invalid @enderror"
                   value="{{ old('salaire_base', $contrat->salaire_base) }}" min="0" required>
            @error('salaire_base')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Mode de paiement</label>
            <select name="mode_paiement" class="form-control @error('mode_paiement') is-invalid @enderror">
                <option value="">-- Choisir --</option>
                <option value="VIREMENT" {{ old('mode_paiement', $contrat->mode_paiement)=='VIREMENT'?'selected':'' }}>Virement bancaire</option>
                <option value="CHEQUE"   {{ old('mode_paiement', $contrat->mode_paiement)=='CHEQUE'?'selected':'' }}>Chèque</option>
                <option value="ESPECES"  {{ old('mode_paiement', $contrat->mode_paiement)=='ESPECES'?'selected':'' }}>Espèces</option>
            </select>
            @error('mode_paiement')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Responsable hiérarchique</label>
            <input type="text" name="responsable_hierarchique"
                   class="form-control @error('responsable_hierarchique') is-invalid @enderror"
                   value="{{ old('responsable_hierarchique', $contrat->responsable_hierarchique) }}"
                   placeholder="Ex: M. DUPONT Jean">
            @error('responsable_hierarchique')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     INFORMATIONS COMPLÉMENTAIRES
     ═══════════════════════════════════════════════════════ --}}
<div class="form-section">
    <h5>📝 Informations complémentaires</h5>
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold">Lieu de travail</label>
            <input type="text" name="lieu_travail"
                   class="form-control @error('lieu_travail') is-invalid @enderror"
                   value="{{ old('lieu_travail', $contrat->lieu_travail) }}"
                   placeholder="Ex: Yaoundé, Douala...">
            @error('lieu_travail')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold">Horaires</label>
            <input type="text" name="horaires"
                   class="form-control @error('horaires') is-invalid @enderror"
                   value="{{ old('horaires', $contrat->horaires) }}"
                   placeholder="Ex: 08h00 - 17h30">
            @error('horaires')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Conditions particulières</label>
            <textarea name="conditions_particulieres"
                      class="form-control @error('conditions_particulieres') is-invalid @enderror"
                      rows="3"
                      placeholder="Clauses particulières, avantages...">{{ old('conditions_particulieres', $contrat->conditions_particulieres) }}</textarea>
            @error('conditions_particulieres')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-12">
            <label class="form-label fw-semibold">Notes</label>
            <textarea name="notes"
                      class="form-control @error('notes') is-invalid @enderror"
                      rows="2"
                      placeholder="Remarques internes...">{{ old('notes', $contrat->notes) }}</textarea>
            @error('notes')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     STATUT DU CONTRAT
     ═══════════════════════════════════════════════════════ --}}
<div class="form-section">
    <h5>📌 Statut du contrat</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
            <select name="statut" class="form-control @error('statut') is-invalid @enderror" required>
                @foreach(\App\Models\RH\Contrat::STATUTS_LABELS as $k => $v)
                    <option value="{{ $k }}" {{ old('statut', $contrat->statut)==$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
            @error('statut')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Date de signature</label>
            <input type="date" name="date_signature"
                   class="form-control @error('date_signature') is-invalid @enderror"
                   value="{{ old('date_signature', $contrat->date_signature?->format('Y-m-d')) }}">
            @error('date_signature')
                <div class="text-danger" style="font-size:12px;margin-top:4px;">{{ $message }}</div>
            @enderror
        </div>
        <div class="col-md-4">
            <label class="form-label fw-semibold">Est renouvelable</label>
            <select name="est_renouvelable" class="form-control">
                <option value="1" {{ old('est_renouvelable', $contrat->est_renouvelable)==1?'selected':'' }}>Oui</option>
                <option value="0" {{ old('est_renouvelable', $contrat->est_renouvelable)==0?'selected':'' }}>Non</option>
            </select>
        </div>
    </div>
</div>

{{-- BOUTONS --}}
<div class="d-flex gap-2 mt-2">
    <a href="{{ route('rh.contrats.show', $contrat->id) }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-primary px-5">💾 Enregistrer</button>
</div>

</form>

@endsection

@section('scripts')
<script>
// ════════════════════════════════════════════════════════════════
// ✅ MASQUER / DÉSACTIVER LA DATE DE FIN SI CDI
// ════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function () {
    const typeSelect = document.getElementById('type_contrat_id');
    const dateFin    = document.getElementById('date_fin');
    const blocFin    = document.getElementById('bloc-date-fin');
    const hintFin    = document.getElementById('date-fin-hint');

    if (!typeSelect || !dateFin || !blocFin) return;

    function toggleDateFin() {
        const opt = typeSelect.options[typeSelect.selectedIndex];
        const dureeMax = parseInt(opt?.dataset?.dureeMax || '0', 10);
        const estCDI = dureeMax === 0;

        if (estCDI) {
            // Garder la valeur si déjà saisie pour un CDI → mais on laisse l'utilisateur la vider
            if (!dateFin.value) {
                dateFin.disabled = true;
                blocFin.style.opacity = '0.5';
                if (hintFin) hintFin.textContent = '🔒 Non applicable pour un CDI';
            }
        } else {
            dateFin.disabled = false;
            blocFin.style.opacity = '1';
            if (hintFin) hintFin.textContent = 'Laisser vide pour un CDI';
        }
    }

    typeSelect.addEventListener('change', toggleDateFin);
    toggleDateFin();
});

// ════════════════════════════════════════════════════════════════
// ✅ FILTRER LES SERVICES SELON LA DIRECTION
// ════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', function () {
    const directionSelect = document.getElementById('direction_id');
    const serviceSelect   = document.getElementById('service_id');

    if (!directionSelect || !serviceSelect) return;

    // Sauvegarder toutes les options de service
    const toutesOptions = Array.from(serviceSelect.options).map(opt => ({
        value:       opt.value,
        text:        opt.textContent,
        directionId: opt.dataset.directionId || '',
        selected:    opt.selected,
    }));

    function filtrerServices() {
        const dirId = directionSelect.value;
        serviceSelect.innerHTML = '';

        toutesOptions.forEach(opt => {
            if (!opt.value) {
                const optVide = document.createElement('option');
                optVide.value = '';
                optVide.textContent = '-- Choisir --';
                serviceSelect.appendChild(optVide);
                return;
            }

            if (!dirId || opt.directionId === dirId) {
                const nouvelleOpt = document.createElement('option');
                nouvelleOpt.value = opt.value;
                nouvelleOpt.textContent = opt.text;
                nouvelleOpt.dataset.directionId = opt.directionId;
                if (opt.selected && dirId) nouvelleOpt.selected = true;
                serviceSelect.appendChild(nouvelleOpt);
            }
        });
    }

    directionSelect.addEventListener('change', filtrerServices);
    filtrerServices();
});
</script>
@endsection