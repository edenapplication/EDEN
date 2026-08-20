@php
    $clientPre = $clientPre ?? null;
    $client    = $client    ?? null;
    $dossier   = $dossier   ?? null;
@endphp

<style>
.form-section {
    background:white; border-radius:12px; padding:20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px;
}
.form-section h5 {
    font-weight:700; color:#1e3a5f;
    border-bottom:2px solid #e2e8f0;
    padding-bottom:8px; margin-bottom:16px; font-size:14px;
}
.or-divider { display:flex; align-items:center; gap:8px; color:#94a3b8; font-size:12px; margin:8px 0; }
.or-divider hr { flex:1; margin:0; }
</style>

@if(!isset($insideForm) || !$insideForm)
    <h2>{{ $title }}</h2>
    <a href="{{ route('suivi-client.index') }}" class="btn btn-outline-secondary btn-sm mb-3">← Retour</a>
    <form method="POST" action="{{ $action }}">
        @csrf
        @if(isset($method) && $method === 'PUT') @method('PUT') @endif
@endif

{{-- CLIENT --}}
<div class="form-section">
    <h5>👤 Informations client</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label>Nom <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name', $clientPre?->name ?? $client?->name) }}"
                   {{ ($clientPre || $client) ? 'readonly' : '' }}
                   style="{{ ($clientPre || $client) ? 'background:#f1f5f9;' : '' }}"
                   required>
            @if($clientPre || $client)
                <small class="text-muted">Le nom du client ne peut pas être modifié.</small>
            @endif
        </div>

        {{-- ✅ Téléphone modifiable même si client existant --}}
        <div class="col-md-4">
            <label>Téléphone <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control"
                   value="{{ old('phone', $clientPre?->phone ?? $client?->phone) }}"
                   required>
            @if($clientPre || $client)
                <small class="text-muted">Le numéro peut être mis à jour.</small>
            @endif
        </div>

        <div class="col-md-4">
            <label>Nom du dossier <span class="text-danger">*</span></label>
            <input type="text" name="nom_dossier" class="form-control"
                   value="{{ old('nom_dossier', $dossier?->nom_dossier) }}"
                   placeholder="Ex: Dossier terrain Baffoussam" required>
        </div>
    </div>
</div>

{{-- COMMERCIAL --}}
<div class="form-section">
    <h5>🧑‍💼 Commercial qui l'a reçu</h5>
    <select name="commercial_id" class="form-control">
        <option value="">-- Choisir --</option>
        @foreach($options['commerciaux'] as $c)
            <option value="{{ $c->id }}"
                {{ old('commercial_id', $dossier?->commercial_id) == $c->id ? 'selected' : '' }}>
                {{ $c->name }} — {{ $c->phone ?? '-' }}
            </option>
        @endforeach
    </select>
</div>

{{-- AGENT COMMERCIAL --}}
<div class="form-section">
    <h5>🤝 Agent commercial</h5>
    <select name="agent_commercial_id" class="form-control">
        <option value="">-- Choisir un agent existant --</option>
        @foreach($options['agents'] as $a)
            <option value="{{ $a->id }}"
                {{ old('agent_commercial_id', $dossier?->agent_commercial_id) == $a->id ? 'selected' : '' }}>
                {{ $a->nom }} — {{ $a->numero ?? '-' }}
            </option>
        @endforeach
    </select>
    <div class="or-divider"><hr> ou nouveau <hr></div>
    <div class="row g-2">
        <div class="col-6">
            <input type="text" name="agent_nom" class="form-control" placeholder="Nom agent">
        </div>
        <div class="col-6">
            <input type="text" name="agent_numero" class="form-control" placeholder="Numéro">
        </div>
    </div>
</div>

{{-- CHAUFFEUR --}}
<div class="form-section">
    <h5>🚗 Chauffeur</h5>
    <select name="conducteur_id" class="form-control">
        <option value="">-- Choisir --</option>
        @foreach($options['conducteurs'] as $c)
            <option value="{{ $c->id }}"
                {{ old('conducteur_id', $dossier?->conducteur_id) == $c->id ? 'selected' : '' }}>
                {{ $c->nom }} — {{ $c->numero ?? '-' }}
            </option>
        @endforeach
    </select>
    <div class="or-divider"><hr> ou nouveau <hr></div>
    <div class="row g-2">
        <div class="col-6">
            <input type="text" name="conducteur_nom" class="form-control" placeholder="Nom chauffeur">
        </div>
        <div class="col-6">
            <input type="text" name="conducteur_numero" class="form-control" placeholder="Numéro">
        </div>
    </div>
</div>

{{-- FACILITATEUR --}}
<div class="form-section">
    <h5>🔗 Facilitateur</h5>
    <select name="facilitateur_id" class="form-control">
        <option value="">-- Choisir --</option>
        @foreach($options['facilitateurs'] as $f)
            <option value="{{ $f->id }}"
                {{ old('facilitateur_id', $dossier?->facilitateur_id) == $f->id ? 'selected' : '' }}>
                {{ $f->nom }} — {{ $f->numero ?? '-' }}
            </option>
        @endforeach
    </select>
    <div class="or-divider"><hr> ou nouveau <hr></div>
    <div class="row g-2">
        <div class="col-6">
            <input type="text" name="facilitateur_nom" class="form-control" placeholder="Nom facilitateur">
        </div>
        <div class="col-6">
            <input type="text" name="facilitateur_numero" class="form-control" placeholder="Numéro">
        </div>
    </div>
</div>

{{-- GRAND SITE + DIRECTION + SUPERFICIE --}}
<div class="form-section">
    <h5>📍 Intérêt foncier</h5>
    <div class="row g-3">
        <div class="col-md-3">
            <label>Grand site souhaité</label>
            <select name="grand_site_id" class="form-control">
                <option value="">-- Choisir --</option>
                @foreach($options['grandsites'] as $gs)
                    <option value="{{ $gs->id }}"
                        {{ old('grand_site_id', $dossier?->grand_site_id) == $gs->id ? 'selected' : '' }}>
                        {{ $gs->nom }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3">
            <label>Direction d'origine</label>
            <select name="directionSelect" id="directionSelect" class="form-control"
                    onchange="toggleAutreDirection(this.value)">
                <option value="">-- Choisir --</option>
                <option value="baffoussam"
                    {{ old('direction', $dossier?->direction) === 'baffoussam' ? 'selected' : '' }}>
                    Baffoussam
                </option>
                <option value="bagante"
                    {{ old('direction', $dossier?->direction) === 'bagante' ? 'selected' : '' }}>
                    Bagante
                </option>
                <option value="dschang"
                    {{ old('direction', $dossier?->direction) === 'dschang' ? 'selected' : '' }}>
                    Dschang
                </option>
                <option value="direction_generale"
                    {{ old('direction', $dossier?->direction) === 'direction_generale' ? 'selected' : '' }}>
                    Direction Générale
                </option>
                @if($dossier?->direction && !in_array($dossier->direction, ['baffoussam','bagante','dschang','direction_generale']))
                    <option value="{{ $dossier->direction }}" selected>{{ $dossier->direction }}</option>
                @endif
                <option value="__autre__" {{ old('direction') === '__autre__' ? 'selected' : '' }}>
                    ➕ Autre (saisir)
                </option>
            </select>

            <div id="autreDirectionWrap" style="display:none; margin-top:6px;">
                <input type="text" id="autreDirectionInput" class="form-control"
                       placeholder="Nom de la direction..." value="{{ old('direction_autre') }}">
                <small class="text-muted">Cette direction sera enregistrée telle quelle.</small>
            </div>

            <input type="hidden" name="direction" id="directionHidden"
                   value="{{ old('direction', $dossier?->direction) }}">
        </div>

        <div class="col-md-3">
            <label>Superficie voulue (m²)</label>
            <input type="number" name="superficie_voulue" class="form-control"
                   value="{{ old('superficie_voulue', $dossier?->superficie_voulue) }}">
        </div>

        <div class="col-md-3">
            <label>Prix de la superficie (FCFA)</label>
            <input type="number" name="prix_superficie" class="form-control"
                   value="{{ old('prix_superficie', $dossier?->prix_superficie) }}">
        </div>
        <div class="col-md-3">
    <label style="color:#7c3aed;">🚗 Prix logistique (FCFA)</label>
    <input type="number" name="prix_logistique" class="form-control"
           value="{{ old('prix_logistique', $dossier?->prix_logistique) }}"
           placeholder="0">
</div>
    </div>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('suivi-client.index') }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-success px-4">💾 Enregistrer</button>
</div>

@if(!isset($insideForm) || !$insideForm)
    </form>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('directionSelect');
    if (!sel) return;

    const connues = ['', 'baffoussam', 'bagante', 'dschang', 'direction_generale', '__autre__'];
    const valActuelle = document.getElementById('directionHidden').value;

    if (valActuelle && !connues.includes(valActuelle)) {
        sel.value = '__autre__';
        document.getElementById('autreDirectionWrap').style.display = 'block';
        document.getElementById('autreDirectionInput').value = valActuelle;
    } else if (valActuelle) {
        sel.value = valActuelle;
    }

    sel.addEventListener('change', function() {
        toggleAutreDirection(this.value);
    });
});

function toggleAutreDirection(val) {
    const wrap   = document.getElementById('autreDirectionWrap');
    const hidden = document.getElementById('directionHidden');
    const input  = document.getElementById('autreDirectionInput');

    if (val === '__autre__') {
        wrap.style.display = 'block';
        input.focus();
        input.oninput = function() { hidden.value = this.value.trim(); };
        hidden.value = input.value.trim();
    } else {
        wrap.style.display = 'none';
        hidden.value = val;
    }
}

document.querySelector('form')?.addEventListener('submit', function(e) {
    const sel    = document.getElementById('directionSelect');
    const hidden = document.getElementById('directionHidden');
    const input  = document.getElementById('autreDirectionInput');
    if (!sel) return;

    if (sel.value === '__autre__') {
        if (!input.value.trim()) {
            e.preventDefault();
            alert('Veuillez saisir le nom de la direction.');
            input.focus();
            return;
        }
        hidden.value = input.value.trim();
    } else {
        hidden.value = sel.value;
    }
    sel.disabled = true;
});
</script>