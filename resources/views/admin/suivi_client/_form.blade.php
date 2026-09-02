@if($errors->any())
    <div class="alert alert-danger alert-dismissible fade show">
        <strong>❌ Erreurs de validation :</strong>
        <ul class="mb-0 mt-2">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

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
.cni-preview-container {
    display:flex; flex-wrap:wrap; gap:10px; margin-top:10px;
    align-items:flex-start;
}
.cni-item {
    position:relative;
    width:100px; height:100px;
    border-radius:8px;
    border:2px solid #e2e8f0;
    overflow:hidden;
    background:#f8fafc;
}
.cni-item img {
    width:100%; height:100%;
    object-fit:cover;
}
.cni-item .cni-remove {
    position:absolute;
    top:-6px; right:-6px;
    width:22px; height:22px;
    border-radius:50%;
    background:#dc2626;
    color:white;
    border:none;
    font-size:12px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
}
.cni-item .cni-filename {
    position:absolute;
    bottom:0; left:0; right:0;
    background:rgba(0,0,0,0.6);
    color:white;
    font-size:8px;
    padding:2px 4px;
    text-overflow:ellipsis;
    overflow:hidden;
    white-space:nowrap;
}
.cni-placeholder {
    color:#94a3b8;
    font-size:12px;
    padding:8px 0;
    width:100%;
}
.cni-message-success {
    font-size:11px;
    color:#16a34a;
    font-weight:600;
    margin-top:4px;
    width:100%;
}
.cni-message-error {
    font-size:11px;
    color:#dc2626;
    font-weight:600;
    margin-top:4px;
    width:100%;
}
.cni-message-warning {
    font-size:11px;
    color:#92400e;
    background:#fef3c7;
    padding:4px 10px;
    border-radius:6px;
    margin-top:4px;
    width:100%;
}
.required-star {
    color: #dc2626;
    font-weight: 700;
}
</style>

@if(!isset($insideForm) || !$insideForm)
    <h2>{{ $title }}</h2>
    <a href="{{ route('suivi-client.index') }}" class="btn btn-outline-secondary btn-sm mb-3">← Retour</a>
    <form method="POST" action="{{ $action }}" enctype="multipart/form-data" id="mainForm">
        @csrf
        @if(isset($method) && $method === 'PUT') @method('PUT') @endif
@endif

{{-- CLIENT --}}
<div class="form-section">
    <h5>👤 Informations client</h5>
    <div class="row g-3">
        <div class="col-md-4">
            <label>Nom <span class="required-star">*</span></label>
            <input type="text" name="name" class="form-control"
                   value="{{ old('name', $clientPre?->name ?? $client?->name) }}"
                   {{ ($clientPre || $client) ? 'readonly' : '' }}
                   style="{{ ($clientPre || $client) ? 'background:#f1f5f9;' : '' }}"
                   required>
            @if($clientPre || $client)
                <small class="text-muted">Le nom du client ne peut pas être modifié.</small>
            @endif
        </div>

        <div class="col-md-4">
            <label>Téléphone <span class="required-star">*</span></label>
            <input type="text" name="phone" class="form-control"
                   value="{{ old('phone', $clientPre?->phone ?? $client?->phone) }}"
                   required>
            @if($clientPre || $client)
                <small class="text-muted">Le numéro peut être mis à jour.</small>
            @endif
        </div>

        <div class="col-md-4">
            <label>Nom du dossier <span class="required-star">*</span></label>
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

{{-- GRAND SITE + DIRECTION + SUPERFICIE + PRIX --}}
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

        <div class="col-12">
            <label class="form-label fw-semibold" id="cni-label">
                📎 Pièces CNI <span class="required-star">*</span>
                <span style="font-size:11px;font-weight:400;color:#64748b;">
                    (Recto, verso — obligatoire pour un nouveau dossier)
                </span>
            </label>
            <input type="file" name="cni_images[]" class="form-control"
                   multiple accept="image/*,.pdf"
                   id="cni-input"
                   {{ (!isset($dossier) || !$dossier) ? 'required' : '' }}
                   onchange="previewCni(this)">
            <div id="cni-preview" class="cni-preview-container">
                @if(isset($dossier) && $dossier && $dossier->cni_images)
                    @foreach($dossier->cni_images as $img)
                        <div class="cni-item">
                            <img src="{{ asset('storage/' . $img) }}" alt="CNI">
                            <div class="cni-filename">{{ basename($img) }}</div>
                        </div>
                    @endforeach
                    <div class="cni-message-success">
                        ✅ {{ count($dossier->cni_images) }} fichier(s) existant(s)
                    </div>
                @else
                    <div class="cni-placeholder">Aucun fichier sélectionné</div>
                @endif
            </div>
            <div style="font-size:11px;color:#64748b;margin-top:4px;">
                Formats acceptés : JPG, PNG, PDF · Plusieurs fichiers possibles
            </div>
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

        {{-- ✅ PRIX SUPERFICIE --}}
        <div class="col-md-3">
            <label>Prix de la superficie (FCFA) <span class="required-star">*</span></label>
            <input type="number" name="prix_superficie" class="form-control"
                   value="{{ old('prix_superficie', $dossier?->prix_superficie) }}"
                   required>
        </div>

        {{-- ✅ PRIX TECHNIQUE --}}
        <div class="col-md-3">
            <label style="color:#ea580c;">🛠️ Prix technique (FCFA) <span class="required-star">*</span></label>
            <input type="number" name="prix_technique" class="form-control"
                   value="{{ old('prix_technique', $dossier?->prix_technique) }}"
                   required>
        </div>

        {{-- ✅ PRIX LOGISTIQUE --}}
        <div class="col-md-3">
            <label style="color:#7c3aed;">🚗 Prix logistique (FCFA) <span class="required-star">*</span></label>
            <input type="number" name="prix_logistique" class="form-control"
                   value="{{ old('prix_logistique', $dossier?->prix_logistique) }}"
                   required>
        </div>

        {{-- ✅ PRIX MORCELLEMENT --}}
        <div class="col-md-3">
            <label style="color:#ca8a04;">✂️ Prix morcellement (FCFA)</label>
            <input type="number" name="prix_morcellement" class="form-control"
                   value="{{ old('prix_morcellement', $dossier?->prix_morcellement) }}"
                   placeholder="0">
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-2">
    <a href="{{ route('suivi-client.index') }}" class="btn btn-light">Annuler</a>
    <button type="submit" class="btn btn-success px-4" id="btnSubmit" 
            {{ (!isset($dossier) || !$dossier) ? 'disabled' : '' }}
            style="{{ (!isset($dossier) || !$dossier) ? 'opacity:0.5;cursor:not-allowed;' : '' }}">
        💾 Enregistrer
    </button>
</div>

@if(!isset($insideForm) || !$insideForm)
    </form>
@endif

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Direction
    const sel = document.getElementById('directionSelect');
    if (sel) {
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
    }

    // CNI - Vérification initiale
    const input = document.getElementById('cni-input');
    const btnSubmit = document.getElementById('btnSubmit');
    const isNew = !{{ isset($dossier) && $dossier ? 'true' : 'false' }};
    
    if (isNew && input) {
        if (!input.files || input.files.length === 0) {
            if (btnSubmit) {
                btnSubmit.disabled = true;
                btnSubmit.style.opacity = '0.5';
                btnSubmit.style.cursor = 'not-allowed';
            }
            const preview = document.getElementById('cni-preview');
            if (preview && !preview.querySelector('.cni-item')) {
                preview.innerHTML = '<span class="cni-message-error">⚠️ Veuillez sélectionner au moins un fichier (recto CNI)</span>';
            }
        }
    }
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

// ============================================================
// PREVIEW CNI
// ============================================================
function previewCni(input) {
    const preview = document.getElementById('cni-preview');
    preview.innerHTML = '';
    
    const files = input.files;
    const btnSubmit = document.getElementById('btnSubmit');
    const isNew = !{{ isset($dossier) && $dossier ? 'true' : 'false' }};
    
    // ✅ Pour un nouveau dossier, CNI obligatoire
    if (isNew && (!files || files.length === 0)) {
        preview.innerHTML = '<span class="cni-message-error">⚠️ Veuillez sélectionner au moins un fichier (recto CNI)</span>';
        if (btnSubmit) {
            btnSubmit.disabled = true;
            btnSubmit.style.opacity = '0.5';
            btnSubmit.style.cursor = 'not-allowed';
        }
        return;
    }
    
    // ✅ Activer le bouton si au moins un fichier
    if (btnSubmit) {
        btnSubmit.disabled = false;
        btnSubmit.style.opacity = '1';
        btnSubmit.style.cursor = 'pointer';
    }
    
    if (!files || files.length === 0) {
        preview.innerHTML = '<span class="cni-placeholder">Aucun fichier sélectionné</span>';
        return;
    }
    
    // Affichage des miniatures
    const maxFiles = 5;
    const filesToShow = Math.min(files.length, maxFiles);
    
    for (let i = 0; i < filesToShow; i++) {
        const file = files[i];
        const div = document.createElement('div');
        div.className = 'cni-item';
        
        if (file.type.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.alt = file.name;
            img.onload = function() {
                URL.revokeObjectURL(this.src);
            };
            div.appendChild(img);
        } else {
            const iconDiv = document.createElement('div');
            iconDiv.style.cssText = 'display:flex;align-items:center;justify-content:center;height:100%;font-size:32px;';
            iconDiv.textContent = '📄';
            div.appendChild(iconDiv);
        }
        
        const nameDiv = document.createElement('div');
        nameDiv.className = 'cni-filename';
        nameDiv.textContent = file.name.length > 15 ? file.name.substring(0, 15) + '...' : file.name;
        div.appendChild(nameDiv);
        
        preview.appendChild(div);
    }
    
    if (files.length > maxFiles) {
        const msg = document.createElement('div');
        msg.className = 'cni-message-warning';
        msg.textContent = `⚠️ ${files.length - maxFiles} fichier(s) supplémentaire(s) sélectionné(s)`;
        preview.appendChild(msg);
    }
    
    const validMsg = document.createElement('div');
    validMsg.className = 'cni-message-success';
    validMsg.textContent = `✅ ${files.length} fichier(s) sélectionné(s)`;
    preview.appendChild(validMsg);
}

// ============================================================
// VALIDATION DU FORMULAIRE
// ============================================================
function validerFormulaire() {
    const input = document.getElementById('cni-input');
    const files = input?.files;
    const isNew = !{{ isset($dossier) && $dossier ? 'true' : 'false' }};
    
    // ✅ Pour un nouveau dossier, CNI obligatoire
    if (isNew && (!files || files.length === 0)) {
        alert('⚠️ Veuillez sélectionner au moins un fichier CNI (recto).');
        input.focus();
        return false;
    }
    
    return true;
}

// ============================================================
// SOUMISSION DU FORMULAIRE
// ============================================================
document.querySelector('form')?.addEventListener('submit', function(e) {
    // Direction
    const sel    = document.getElementById('directionSelect');
    const hidden = document.getElementById('directionHidden');
    const input  = document.getElementById('autreDirectionInput');
    if (sel) {
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
    }
    
    // ✅ CNI - Validation
    const cniInput = document.getElementById('cni-input');
    const files = cniInput?.files;
    const isNew = !{{ isset($dossier) && $dossier ? 'true' : 'false' }};
    
    if (isNew && (!files || files.length === 0)) {
        e.preventDefault();
        alert('⚠️ Veuillez sélectionner au moins un fichier CNI (recto).');
        cniInput.focus();
        return;
    }
});
</script>