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
    @if($method === 'PUT') @method('PUT') @endif
@endif

    {{-- CLIENT --}}
{{-- CLIENT --}}
<div class="form-section">
    <h5>👤 Informations client</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label>Nom <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name', $clientPre?->name ?? $client?->name) }}"
                   {{ ($clientPre || ($client && isset($insideForm))) ? 'readonly style=background:#f1f5f9' : '' }}
                   required>
            @if($clientPre || ($client && isset($insideForm)))
                <small class="text-muted">Le nom du client ne peut pas être modifié.</small>
            @endif
        </div>
        <div class="col-md-4">
            <label>Téléphone <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control"
                   value="{{ old('phone', $clientPre?->phone ?? $client?->phone) }}"
                   {{ ($clientPre || ($client && isset($insideForm))) ? 'readonly style=background:#f1f5f9' : '' }}
                   required>
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
            <select name="direction" id="directionSelect" class="form-control"
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
                <option value="direction_generale"
                    {{ old('direction', $dossier?->direction) === 'direction_generale' ? 'selected' : '' }}>
                    Direction Générale
                </option>
                {{-- Si la direction actuelle ne correspond à aucune option connue, la proposer --}}
                @if($dossier?->direction &&
                    !in_array($dossier->direction, ['baffoussam','bagante','direction_generale']))
                    <option value="{{ $dossier->direction }}" selected>
                        {{ $dossier->direction }}
                    </option>
                @endif
                <option value="__autre__"
                    {{ old('direction') === '__autre__' ? 'selected' : '' }}>
                    ➕ Autre (saisir)
                </option>
            </select>

            {{-- Champ texte libre si "Autre" est sélectionné --}}
            <div id="autreDirectionWrap" style="display:none; margin-top:6px;">
                <input type="text"
                       id="autreDirectionInput"
                       class="form-control"
                       placeholder="Nom de la direction..."
                       value="{{ old('direction_autre') }}">
                <small class="text-muted">Cette direction sera enregistrée telle quelle.</small>
            </div>

            {{-- Champ caché qui envoie la vraie valeur --}}
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
    </div>
</div>

    <div class="d-flex gap-2 mt-2">
        <a href="{{ route('suivi-client.index') }}" class="btn btn-light">Annuler</a>
        <button type="submit" class="btn btn-success px-4">💾 Enregistrer</button>
    </div>

@if(!isset($insideForm) || !$insideForm)

<script>
// Initialiser à l'ouverture si une direction personnalisée est déjà enregistrée
document.addEventListener('DOMContentLoaded', function() {
    const sel = document.getElementById('directionSelect');
    if (sel) {
        const valActuelle = sel.value;
        // Si la valeur actuelle n'est pas dans les options standards, afficher le champ libre
        const connues = ['', 'baffoussam', 'bagante', 'direction_generale', '__autre__'];
        if (valActuelle && !connues.includes(valActuelle)) {
            document.getElementById('autreDirectionWrap').style.display = 'block';
            document.getElementById('autreDirectionInput').value = valActuelle;
            document.getElementById('directionHidden').value     = valActuelle;
        }
        toggleAutreDirection(valActuelle);
    }
});

function toggleAutreDirection(val) {
    const wrap   = document.getElementById('autreDirectionWrap');
    const hidden = document.getElementById('directionHidden');
    const input  = document.getElementById('autreDirectionInput');

    if (val === '__autre__') {
        wrap.style.display = 'block';
        input.focus();
        // Le champ hidden sera mis à jour en temps réel
        input.addEventListener('input', function() {
            hidden.value = this.value.trim();
        });
        hidden.value = input.value.trim();
    } else {
        wrap.style.display = 'none';
        hidden.value = val;
    }
}

// Intercepter la soumission pour valider le champ "autre"
document.querySelector('form')?.addEventListener('submit', function(e) {
    const sel    = document.getElementById('directionSelect');
    const hidden = document.getElementById('directionHidden');
    const input  = document.getElementById('autreDirectionInput');

    if (!sel || !hidden) return;

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

    // Désactiver le select pour qu'il n'envoie pas son propre champ
    // (le hidden envoie la vraie valeur)
    sel.disabled = true;
});
</script>

</form>
@endif