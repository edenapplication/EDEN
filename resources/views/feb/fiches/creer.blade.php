<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ isset($fiche) ? 'Continuer la fiche' : 'Nouvelle fiche' }} — FEB</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { box-sizing:border-box; }
        body { background:#f4f6f9; font-family:"Segoe UI",sans-serif; margin:0; }

        .topbar {
            background:linear-gradient(135deg,#1d4ed8,#7c3aed);
            color:white; padding:0 28px; height:60px;
            display:flex; align-items:center; justify-content:space-between;
            position:sticky; top:0; z-index:200;
            box-shadow:0 2px 12px rgba(0,0,0,0.15);
        }
        .page-wrap { display:flex; min-height:calc(100vh - 60px); }

        /* SIDEBAR */
        .sidebar-resume {
            width:260px; flex-shrink:0; background:#0f172a; color:#cbd5e1;
            position:sticky; top:60px; height:calc(100vh - 60px);
            overflow-y:auto; padding:16px; scrollbar-width:thin;
        }
        .sidebar-resume h6 { color:#60a5fa; font-size:11px; text-transform:uppercase; letter-spacing:1px; font-weight:700; margin-bottom:10px; }
        .resume-fiche-titre { font-weight:800; color:white; font-size:14px; margin-bottom:12px; word-break:break-word; min-height:20px; }
        .resume-section { background:rgba(255,255,255,0.06); border-radius:8px; padding:10px; margin-bottom:8px; border-left:3px solid #475569; cursor:pointer; transition:0.15s; }
        .resume-section:hover { background:rgba(255,255,255,0.1); }
        .resume-section.ok { border-left-color:#16a34a; }
        .resume-section.partiel { border-left-color:#f59e0b; }
        .resume-section .sec-titre { font-size:12px; font-weight:700; color:white; }
        .resume-section .sec-stats { font-size:10px; color:#64748b; margin-top:3px; }
        .resume-total { background:rgba(22,163,74,0.15); border:1px solid #16a34a; border-radius:8px; padding:10px; margin-top:10px; }

        /* CONTENU */
        .main-content { flex:1; padding:24px; overflow-y:auto; }
        .card-section { background:white; border-radius:14px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:20px; overflow:hidden; }
        .card-header-sec { background:#1e3a5f; color:white; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; }
        .card-body-sec { padding:18px; }

        /* DESTINATAIRES */
        .destinataires-container {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            overflow: hidden;
        }
        .destinataires-header {
            background: #f8fafc;
            padding: 12px 16px;
            border-bottom: 1px solid #e2e8f0;
            font-weight: 600;
            color: #1e3a5f;
        }
        .destinataires-list {
            padding: 12px 16px;
            min-height: 50px;
        }
        .destinataire-tag {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #dbeafe;
            color: #1d4ed8;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            margin: 4px;
        }
        .destinataire-tag .remove-btn {
            background: none;
            border: none;
            color: #60a5fa;
            cursor: pointer;
            font-size: 14px;
            padding: 0 4px;
            line-height: 1;
        }
        .destinataire-tag .remove-btn:hover {
            color: #dc2626;
        }
        .destinataire-empty {
            color: #94a3b8;
            font-style: italic;
            font-size: 13px;
        }
        .destinataire-actions {
            padding: 12px 16px;
            border-top: 1px solid #e2e8f0;
            background: #fafbfc;
        }
        .destinataire-select-group {
            display: flex;
            gap: 8px;
            align-items: flex-end;
        }
        .destinataire-select-group select {
            flex: 1;
        }
        .destinataire-create-group {
            display: flex;
            gap: 8px;
            margin-top: 8px;
        }
        .destinataire-create-group input {
            flex: 1;
        }

        /* COLONNES */
        .colonnes-grid { display:flex; flex-wrap:wrap; gap:6px; margin-bottom:14px; }
        .col-chip {
            display:inline-flex; align-items:center; gap:6px;
            border:2px solid #e2e8f0; border-radius:8px;
            padding:6px 14px; cursor:pointer; font-size:12px;
            transition:all 0.15s; user-select:none; background:#f8fafc;
            font-weight:600;
        }
        .col-chip:hover { border-color:#1d4ed8; background:#eff6ff; color:#1d4ed8; }
        .col-chip.active { background:#dbeafe; border-color:#1d4ed8; color:#1d4ed8; font-weight:700; }
        .col-chip .chip-check { width:14px; height:14px; border-radius:3px; border:2px solid #cbd5e1; display:inline-flex; align-items:center; justify-content:center; font-size:10px; flex-shrink:0; }
        .col-chip.active .chip-check { background:#1d4ed8; border-color:#1d4ed8; color:white; }

        /* TABLEAU */
        .tableau-wrap { overflow-x:auto; margin-bottom:8px; border-radius:8px; border:1px solid #e2e8f0; }
        .tableau-fiche { width:100%; border-collapse:collapse; min-width:400px; }
        .tableau-fiche thead tr { background:#1e3a5f; color:white; }
        .tableau-fiche thead th { padding:10px 12px; font-size:12px; font-weight:700; text-align:left; white-space:nowrap; }
        .tableau-fiche thead th.th-num { width:36px; text-align:center; }
        .tableau-fiche thead th.th-act { width:36px; }
        .tableau-fiche tbody td { border-bottom:1px solid #e2e8f0; padding:1px 2px; }
        .tableau-fiche tbody tr:nth-child(even) td { background:#f8fafc; }
        .tableau-fiche tfoot td { background:#f0fdf4; border-top:2px solid #16a34a; padding:10px 12px; font-weight:700; }

        .cell-input { border:none; background:transparent; width:100%; font-size:13px; padding:8px 10px; outline:none; border-radius:4px; transition:0.1s; }
        .cell-input:focus { background:#eff6ff; }
        .cell-input.num { text-align:right; font-family:monospace; }
        .cell-input.readonly { color:#16a34a; font-weight:700; text-align:right; cursor:default; font-family:monospace; }

        .btn-rm-row { background:none; border:none; color:#dc2626; cursor:pointer; font-size:13px; padding:4px 8px; border-radius:4px; }
        .btn-rm-row:hover { background:#fee2e2; }
        .btn-add-row { background:white; border:2px dashed #1d4ed8; color:#1d4ed8; border-radius:8px; padding:9px; width:100%; font-weight:700; font-size:13px; cursor:pointer; margin-top:6px; transition:0.15s; }
        .btn-add-row:hover { background:#eff6ff; }

        /* TOTAUX */
        .total-section-row td { background:#f0fdf4 !important; font-weight:800; color:#16a34a; border-top:2px solid #bbf7d0; }
        .total-global-bar { background:linear-gradient(135deg,#1e3a5f,#1d4ed8); color:white; border-radius:14px; padding:18px 24px; text-align:right; margin-bottom:20px; display:none; }
        .total-global-bar .label { font-size:12px; opacity:0.75; }
        .total-global-bar .montant { font-size:28px; font-weight:900; margin-top:4px; }

        /* ACTIONS */
        .btn-add-section { background:white; border:2px dashed #7c3aed; color:#7c3aed; border-radius:12px; padding:14px; width:100%; font-weight:700; font-size:14px; cursor:pointer; margin-bottom:20px; transition:0.15s; }
        .btn-add-section:hover { background:#faf5ff; }
        .footer-actions { background:white; border-radius:14px; padding:18px 22px; box-shadow:0 2px 10px rgba(0,0,0,0.06); display:flex; justify-content:space-between; align-items:center; gap:12px; }
        .btn-soumettre { background:linear-gradient(135deg,#16a34a,#15803d); color:white; border:none; border-radius:12px; padding:14px 30px; font-weight:800; font-size:15px; cursor:pointer; transition:0.15s; }
        .btn-soumettre:disabled { opacity:0.4; cursor:not-allowed; }
        .btn-brouillon { background:#f1f5f9; color:#374151; border:none; border-radius:12px; padding:12px 20px; font-weight:700; font-size:13px; cursor:pointer; transition:0.15s; }
        .btn-brouillon:hover { background:#e2e8f0; }
        .btn-rm-section { background:none; border:none; color:rgba(255,255,255,0.5); font-size:16px; cursor:pointer; padding:0 4px; line-height:1; }
        .btn-rm-section:hover { color:white; }
        .info-bar { background:#dbeafe; border-radius:10px; padding:12px 16px; font-size:13px; color:#1d4ed8; margin-bottom:16px; }

        .sec-label-titre { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px; }
    </style>
</head>
<body>

<div class="topbar">
    <div>
        <div style="font-weight:800;font-size:15px;">
            📋 {{ isset($fiche) ? 'Continuer la fiche' : 'Nouvelle Fiche d\'Expression des Besoins' }}
        </div>
        <div style="font-size:11px;opacity:0.7;">
            {{ isset($fiche) ? 'Brouillon en cours — ' . $fiche->titre : 'Tout se fait sur cette page' }}
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:12px;">
        <div style="font-size:12px;background:rgba(255,255,255,0.18);padding:5px 12px;border-radius:16px;">
            👤 {{ $user->nom_complet }}
            @if($user->agence) — {{ $user->agence->nom }} @endif
        </div>
        <a href="{{ route('feb.fiches.index') }}"
           style="color:rgba(255,255,255,0.8);text-decoration:none;font-size:12px;background:rgba(255,255,255,0.1);padding:5px 12px;border-radius:8px;">
            ← Mes fiches
        </a>
    </div>
</div>

<div class="page-wrap">

    {{-- SIDEBAR RÉSUMÉ --}}
    <div class="sidebar-resume">
        <h6>📋 Aperçu</h6>
        <div class="resume-fiche-titre" id="resume-titre">Sans titre</div>

        <div id="resume-sections">
            <div style="color:#475569;font-size:12px;">Aucune section</div>
        </div>

        <div class="resume-total mt-3">
            <div style="font-size:10px;color:#64748b;text-transform:uppercase;margin-bottom:4px;">Total général</div>
            <div style="font-size:20px;font-weight:800;color:#16a34a;" id="resume-total-global">0 FCFA</div>
        </div>

        @if($colonnes->count() > 0)
        <div style="margin-top:16px;padding-top:12px;border-top:1px solid rgba(255,255,255,0.08);">
            <div style="font-size:10px;color:#475569;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.05em;">Colonnes disponibles</div>
            @foreach($colonnes as $col)
            <div style="font-size:11px;color:#64748b;padding:2px 0;">• {{ $col->libelle }}</div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- CONTENU PRINCIPAL --}}
    <div class="main-content">

        <div class="info-bar">
            💡 Remplissez le titre, ajoutez vos sections, cochez les colonnes et saisissez vos données. Le calcul est automatique.
            Vous pouvez <strong>sauvegarder en brouillon</strong> pour continuer plus tard.
        </div>

        {{-- TITRE + DESCRIPTION --}}
        <div class="card-section">
            <div class="card-body-sec">
                <div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label fw-bold" style="color:#1e3a5f;">
                            Titre de la fiche <span class="text-danger">*</span>
                        </label>
                        <input type="text" id="fiche-titre" class="form-control form-control-lg"
                               placeholder="Ex : Expression des besoins — Janvier 2025"
                               value="{{ $fiche->titre ?? '' }}"
                               oninput="mettreAJourTitre(); mettreAJourBoutonSoumettre();">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold" style="color:#1e3a5f;">
                            Description <span class="text-muted" style="font-size:11px;">(optionnel)</span>
                        </label>
                        <textarea id="fiche-desc" class="form-control" rows="2"
                                  placeholder="Courte description...">{{ $fiche->description ?? '' }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- ============================================================ --}}
        {{-- SECTION DESTINATAIRES --}}
        {{-- ============================================================ --}}
        <div class="card-section">
            <div class="card-header-sec" style="background: #0f172a;">
                <div style="font-weight:700; font-size:14px;">👥 DOIT(S)</div>
                <span style="font-size:11px; opacity:0.6;">DOIT de la fiche</span>
            </div>
            <div class="card-body-sec">
                <div class="destinataires-container">
                    <!-- Liste des destinataires sélectionnés -->
                    <div class="destinataires-list" id="destinataires-list">
                        <span class="destinataire-empty">Aucun destinataire sélectionné</span>
                    </div>

                    <!-- Actions -->
                    <div class="destinataire-actions">
                        <!-- Sélectionner un destinataire existant -->
                        <div class="destinataire-select-group">
                            <select id="dest-select" class="form-select form-select-sm">
                                <option value="">Choisir un destinataire...</option>
                            </select>
                            <button type="button" class="btn btn-primary btn-sm" onclick="addSelectedDestinataire()">
                                + Ajouter
                            </button>
                        </div>

                        <!-- Créer un nouveau destinataire -->
                        <div class="destinataire-create-group">
                            <input type="text" id="new-dest-name" class="form-control form-control-sm"
                                   placeholder="Nouveau destinataire..." 
                                   onkeydown="if(event.key==='Enter'){event.preventDefault();createAndAddDestinataire();}">
                            <button type="button" class="btn btn-success btn-sm" onclick="createAndAddDestinataire()">
                                + Créer et ajouter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TOTAL GLOBAL --}}
        <div class="total-global-bar" id="total-global-bar">
            <div class="label">TOTAL GÉNÉRAL DE LA FICHE</div>
            <div class="montant" id="total-global-val">0 FCFA</div>
        </div>

        {{-- SECTIONS --}}
        <div id="sections-container"></div>

        <button type="button" class="btn-add-section" onclick="ajouterSection()">
            ＋ Ajouter une section
        </button>

        {{-- FOOTER --}}
        <div class="footer-actions">
            <a href="{{ route('feb.fiches.index') }}" class="btn btn-light">← Annuler</a>
            <div style="display:flex;align-items:center;gap:12px;">
                <div>
                    <div style="font-size:12px;color:#64748b;text-align:right;margin-bottom:4px;" id="hint-soumettre">
                        Remplissez au moins une section.
                    </div>
                    <div style="display:flex;gap:10px;">
                        <button class="btn-brouillon" id="btn-brouillon" onclick="soumettre('brouillon')" disabled>
                            💾 Sauvegarder brouillon
                        </button>
                        <button class="btn-soumettre" id="btn-soumettre" onclick="soumettre('soumettre')" disabled>
                            ✅ Soumettre la fiche
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

<script>
// ============================================================
// CONFIG
// ============================================================
const CSRF          = '{{ csrf_token() }}';
const URL_SOUMETTRE = '{{ route("feb.fiches.creer-soumettre") }}';
const URL_RETOUR    = '{{ route("feb.fiches.index") }}';
const URL_DESTINATAIRES = '{{ route("feb.destinataires.index") }}';
const URL_DESTINATAIRES_STORE = '{{ route("feb.destinataires.store") }}';
const FICHE_ID      = {{ isset($fiche) ? $fiche->id : 'null' }};
const MODELE_ID     = {{ $modele?->id ?? 'null' }};

const COLONNES_DISPO = @json($colonnes->values());

// Détection type de colonne
const estQte  = l => /quantit/i.test(l);
const estPU   = l => /prix.?u|p\.u|unitaire/i.test(l);
const estPT   = l => /prix.?total|p\.t|montant.?total/i.test(l);
const estNum  = l => estQte(l) || /prix|montant/i.test(l);

// ============================================================
// STATE
// ============================================================
let sections     = [];
let sectionCount = 0;
let lignesCount  = {};

// Destinataires
let selectedDestinataires = [];
let availableDestinataires = [];

// ============================================================
// INIT
// ============================================================
document.addEventListener('DOMContentLoaded', function() {
    // Charger les destinataires
    chargerDestinataires();

    // Charger les destinataires existants si on est en édition
    @if(isset($fiche) && $fiche)
        @foreach($fiche->destinataires as $dest)
            selectedDestinataires.push({ id: {{ $dest->id }}, nom: '{{ addslashes($dest->nom) }}' });
        @endforeach
        afficherDestinataires();
    @endif

    document.getElementById('fiche-titre')?.addEventListener('input', function() {
        mettreAJourTitre();
        mettreAJourBoutonSoumettre();
    });

    @if(isset($fiche) && $fiche)
        document.getElementById('fiche-titre').value = @json($fiche->titre ?? '');
        document.getElementById('fiche-desc').value  = @json($fiche->description ?? '');
        mettreAJourTitre();

        @foreach($fiche->sections->sortBy('ordre') as $sec)
        (function() {
            const secId = ajouterSection(@json($sec->titre), false);
            const secObj = sections.find(s => s.id === secId);
            if (!secObj) return;

            secObj.colonnesIds = [];
            @foreach($sec->colonnes->sortBy('pivot.ordre') as $col)
                secObj.colonnesIds.push({{ $col->id }});
            @endforeach

            COLONNES_DISPO.forEach(function(col) {
                const chip = document.getElementById('chip-' + secId + '-' + col.id);
                if (!chip) return;
                if (secObj.colonnesIds.includes(col.id)) {
                    chip.classList.add('active');
                } else {
                    chip.classList.remove('active');
                }
            });

            reconstruireEntete(secId);

            @foreach($sec->lignes->sortBy('numero_ligne') as $ligne)
                ajouterLigne(secId, @json($ligne->valeurs));
            @endforeach

            calculerTotaux(secId);
        })();
        @endforeach

    @elseif(isset($modele) && $modele)
        document.getElementById('fiche-titre').value = '';
        mettreAJourTitre();

        @foreach($modele->sections->sortBy('ordre') as $sec)
        (function() {
            const secId  = ajouterSection(@json($sec->titre), false);
            const secObj = sections.find(s => s.id === secId);
            if (!secObj) return;

            secObj.colonnesIds = [];
            @foreach($sec->colonnes->sortBy('pivot.ordre') as $col)
                secObj.colonnesIds.push({{ $col->id }});
            @endforeach

            COLONNES_DISPO.forEach(function(col) {
                const chip = document.getElementById('chip-' + secId + '-' + col.id);
                if (chip) {
                    if (secObj.colonnesIds.includes(col.id)) chip.classList.add('active');
                    else chip.classList.remove('active');
                }
            });

            reconstruireEntete(secId);

            @foreach($sec->lignes->sortBy('numero_ligne') as $ligne)
                ajouterLigne(secId, @json($ligne->valeurs));
            @endforeach

            calculerTotaux(secId);
        })();
        @endforeach

    @else
        ajouterSection();
    @endif

    mettreAJourResume();
    mettreAJourBoutonSoumettre();
});

// ============================================================
// GESTION DES DESTINATAIRES
// ============================================================
function chargerDestinataires() {
    fetch(URL_DESTINATAIRES)
        .then(function(response) {
            return response.json();
        })
        .then(function(data) {
            availableDestinataires = data;
            mettreAJourSelectDestinataires();
        })
        .catch(function(error) {
            console.error('Erreur lors du chargement des destinataires:', error);
        });
}

function mettreAJourSelectDestinataires() {
    const select = document.getElementById('dest-select');
    if (!select) return;

    // Garder l'option "Choisir..."
    select.innerHTML = '<option value="">Choisir un destinataire...</option>';

    availableDestinataires.forEach(function(dest) {
        // Ne pas afficher ceux déjà sélectionnés
        if (!selectedDestinataires.some(function(s) { return s.id === dest.id; })) {
            const option = document.createElement('option');
            option.value = dest.id;
            option.textContent = dest.nom;
            select.appendChild(option);
        }
    });
}

function addSelectedDestinataire() {
    const select = document.getElementById('dest-select');
    const id = parseInt(select.value);
    if (!id) {
        alert('Veuillez sélectionner un destinataire.');
        return;
    }

    const destinataire = availableDestinataires.find(function(d) { return d.id === id; });
    if (!destinataire) return;

    if (selectedDestinataires.some(function(d) { return d.id === id; })) {
        alert('Ce destinataire est déjà sélectionné.');
        return;
    }

    selectedDestinataires.push({ ...destinataire });
    afficherDestinataires();
    mettreAJourSelectDestinataires();
    select.value = '';
}

function createAndAddDestinataire() {
    const input = document.getElementById('new-dest-name');
    const nom = input.value.trim();
    if (!nom) {
        alert('Veuillez saisir un nom.');
        return;
    }

    // Vérifier si déjà sélectionné
    if (selectedDestinataires.some(function(d) { return d.nom.toLowerCase() === nom.toLowerCase(); })) {
        alert('Ce destinataire est déjà sélectionné.');
        return;
    }

    fetch(URL_DESTINATAIRES_STORE, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({ nom: nom })
    })
    .then(function(response) {
        return response.json().then(function(data) {
            if (!response.ok) {
                throw new Error(data.errors?.nom?.[0] || 'Erreur lors de la création');
            }
            return data;
        });
    })
    .then(function(data) {
        // Ajouter à la liste disponible
        availableDestinataires.push(data.destinataire);

        // Ajouter aux sélectionnés
        selectedDestinataires.push(data.destinataire);

        // Réinitialiser le champ
        input.value = '';

        // Mettre à jour l'affichage
        afficherDestinataires();
        mettreAJourSelectDestinataires();
    })
    .catch(function(error) {
        alert(error.message);
    });
}

function removeDestinataire(index) {
    selectedDestinataires.splice(index, 1);
    afficherDestinataires();
    mettreAJourSelectDestinataires();
}

function afficherDestinataires() {
    const container = document.getElementById('destinataires-list');
    if (!container) return;

    if (selectedDestinataires.length === 0) {
        container.innerHTML = '<span class="destinataire-empty">Aucun destinataire sélectionné</span>';
        return;
    }

    let html = '';
    selectedDestinataires.forEach(function(dest, index) {
        html += `
            <span class="destinataire-tag">
                <span>${escHtml(dest.nom)}</span>
                <button type="button" class="remove-btn" onclick="removeDestinataire(${index})">✕</button>
            </span>
        `;
    });
    container.innerHTML = html;
}

// ============================================================
// SECTION
// ============================================================
function ajouterSection(titrePre, avecLigneVide) {
    if (titrePre === undefined) titrePre = '';
    if (avecLigneVide === undefined) avecLigneVide = true;
    
    sectionCount++;
    const secId = 'sec-' + sectionCount;

    const defaut = COLONNES_DISPO
        .filter(function(c) { return /d[eé]sign/i.test(c.libelle) || estQte(c.libelle) || estPU(c.libelle) || estPT(c.libelle); })
        .map(function(c) { return c.id; });

    sections.push({ id: secId, titre: titrePre, colonnesIds: [...defaut], totalCalcule: 0 });
    lignesCount[secId] = 0;

    const div = document.createElement('div');
    div.className     = 'card-section';
    div.id            = 'card-' + secId;
    div.dataset.secId = secId;

    let colonnesHtml = '';
    COLONNES_DISPO.forEach(function(col) {
        const active = defaut.includes(col.id) ? 'active' : '';
        const check = defaut.includes(col.id) ? '✓' : '';
        colonnesHtml += `
            <label class="col-chip ${active}"
                   id="chip-${secId}-${col.id}"
                   onclick="toggleColonne('${secId}', ${col.id}); return false;">
                <span class="chip-check">${check}</span>
                ${escHtml(col.libelle)}
            </label>
        `;
    });

    let enteteColonnes = '';
    defaut.forEach(function(cid) {
        const col = COLONNES_DISPO.find(function(c) { return c.id === cid; });
        if (col) {
            enteteColonnes += `<th data-col-id="${cid}">${escHtml(col.libelle)}</th>`;
        }
    });

    div.innerHTML = `
        <div class="card-header-sec" id="header-${secId}">
            <div style="flex:1;margin-right:10px;">
                <div style="font-size:11px;opacity:0.6;">Section ${sectionCount}</div>
                <input type="text"
                       id="titre-${secId}"
                       class="form-control form-control-sm mt-1"
                       style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.3);color:white;font-weight:700;font-size:14px;"
                       placeholder="Titre de la section (ex: Fournitures, Matériel...)"
                       value="${escHtml(titrePre)}"
                       oninput="onTitreSectionChange('${secId}')">
            </div>
            <button class="btn-rm-section" onclick="supprimerSection('${secId}')" title="Supprimer">✕</button>
        </div>
        <div class="card-body-sec">

            <div class="sec-label-titre">① Colonnes du tableau</div>
            <div class="colonnes-grid" id="colonnes-grid-${secId}">
                ${colonnesHtml}
                ${COLONNES_DISPO.length === 0
                    ? '<div style="color:#f87171;font-size:12px;">⚠️ Aucune colonne disponible — contactez l\'administrateur.</div>'
                    : ''}
            </div>

            <div class="sec-label-titre" style="margin-top:14px;">② Données</div>
            <div class="tableau-wrap">
                <table class="tableau-fiche" id="table-${secId}">
                    <thead>
                        <tr id="thead-${secId}">
                            <th class="th-num">#</th>
                            ${enteteColonnes}
                            <th class="th-act"></th>
                        </tr>
                    </thead>
                    <tbody id="tbody-${secId}"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="999" style="padding:0;">
                                <div style="display:flex;justify-content:flex-end;align-items:center;padding:10px 14px;gap:12px;background:#f0fdf4;border-top:2px solid #bbf7d0;">
                                    <span style="font-size:12px;color:#64748b;font-weight:600;">Total section :</span>
                                    <span style="font-size:17px;font-weight:800;color:#16a34a;" id="total-sec-${secId}">0 FCFA</span>
                                </div>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <button type="button" class="btn-add-row" onclick="ajouterLigne('${secId}')">
                ＋ Ajouter une ligne
            </button>
        </div>
    `;

    document.getElementById('sections-container').appendChild(div);
    mettreAJourVisibiliteTitresSections();

    if (avecLigneVide) ajouterLigne(secId);

    mettreAJourResume();
    mettreAJourBoutonSoumettre();
    setTimeout(function() { div.scrollIntoView({ behavior:'smooth', block:'start' }); }, 100);

    return secId;
}

function supprimerSection(secId) {
    if (sections.length <= 1) { alert('Il faut au moins une section.'); return; }
    if (!confirm('Supprimer cette section ?')) return;
    sections = sections.filter(function(s) { return s.id !== secId; });
    document.getElementById('card-' + secId)?.remove();
    mettreAJourVisibiliteTitresSections();
    mettreAJourTotalGlobal();
    mettreAJourResume();
    mettreAJourBoutonSoumettre();
}

function mettreAJourVisibiliteTitresSections() {
    const nb = sections.length;
    sections.forEach(function(sec) {
        const header = document.getElementById('header-' + sec.id);
        if (header) header.style.display = nb <= 1 ? 'none' : 'flex';
    });
}

function onTitreSectionChange(secId) {
    const sec = sections.find(function(s) { return s.id === secId; });
    if (sec) sec.titre = document.getElementById('titre-' + secId)?.value ?? '';
    mettreAJourResume();
}

// ============================================================
// COLONNES
// ============================================================
function toggleColonne(secId, colonneId) {
    event?.stopPropagation?.();
    event?.preventDefault?.();

    const sec = sections.find(function(s) { return s.id === secId; });
    if (!sec) return;

    const chip = document.getElementById('chip-' + secId + '-' + colonneId);
    const idx  = sec.colonnesIds.indexOf(colonneId);

    if (idx === -1) {
        sec.colonnesIds.push(colonneId);
        if (chip) {
            chip.classList.add('active');
            const checkEl = chip.querySelector('.chip-check');
            if (checkEl) checkEl.innerText = '✓';
        }
    } else {
        sec.colonnesIds.splice(idx, 1);
        if (chip) {
            chip.classList.remove('active');
            const checkEl = chip.querySelector('.chip-check');
            if (checkEl) checkEl.innerText = '';
        }
    }

    reconstruireEntete(secId);
    reconstruireLignes(secId);
    mettreAJourResume();
    mettreAJourBoutonSoumettre();
}

function reconstruireEntete(secId) {
    const sec      = sections.find(function(s) { return s.id === secId; });
    const theadRow = document.getElementById('thead-' + secId);
    if (!sec || !theadRow) return;

    let html = '<th class="th-num">#</th>';
    sec.colonnesIds.forEach(function(cid) {
        const col = COLONNES_DISPO.find(function(c) { return c.id === cid; });
        if (!col) return;
        html += `<th data-col-id="${cid}">${escHtml(col.libelle)}</th>`;
    });
    html += '<th class="th-act"></th>';
    theadRow.innerHTML = html;
}

function reconstruireLignes(secId) {
    const sec   = sections.find(function(s) { return s.id === secId; });
    const tbody = document.getElementById('tbody-' + secId);
    if (!sec || !tbody) return;

    const valsSaved = {};
    tbody.querySelectorAll('tr[data-ligne]').forEach(function(tr) {
        const n = tr.dataset.ligne;
        valsSaved[n] = {};
        tr.querySelectorAll('input[data-col]').forEach(function(inp) {
            valsSaved[n][inp.dataset.col] = inp.value;
        });
    });

    tbody.querySelectorAll('tr[data-ligne]').forEach(function(tr) {
        construireCellulesLigne(tr, secId, sec, tr.dataset.ligne, valsSaved[tr.dataset.ligne] || {});
    });

    calculerTotaux(secId);
}

// ============================================================
// LIGNES
// ============================================================
function ajouterLigne(secId, valeursPre) {
    if (valeursPre === undefined) valeursPre = {};
    const sec   = sections.find(function(s) { return s.id === secId; });
    const tbody = document.getElementById('tbody-' + secId);
    if (!sec || !tbody) return;

    lignesCount[secId] = (lignesCount[secId] || 0) + 1;
    const numLigne = lignesCount[secId];

    const tr = document.createElement('tr');
    tr.dataset.ligne = numLigne;
    construireCellulesLigne(tr, secId, sec, numLigne, valeursPre);
    tbody.appendChild(tr);

    setTimeout(function() { tr.querySelector('.cell-input:not(.readonly)')?.focus(); }, 50);

    calculerTotaux(secId);
    mettreAJourResume();
    mettreAJourBoutonSoumettre();
}

function construireCellulesLigne(tr, secId, sec, numLigne, valeurs) {
    if (valeurs === undefined) valeurs = {};
    let html = `<td style="text-align:center;color:#94a3b8;font-size:11px;padding:4px 6px;min-width:32px;">${numLigne}</td>`;

    sec.colonnesIds.forEach(function(cid) {
        const col = COLONNES_DISPO.find(function(c) { return c.id === cid; });
        if (!col) return;
        const lib   = col.libelle;
        const isPT  = estPT(lib);
        const isNum = estNum(lib);

        const val = valeurs[cid] || '';
        const className = 'cell-input' + (isNum ? ' num' : '') + (isPT ? ' readonly' : '');
        const readonly = isPT ? 'readonly' : '';
        const oninput = isPT ? '' : 'oninput="calculerTotaux(\'' + secId + '\')"';
        
        html += `
            <td>
                <input class="${className}" 
                       data-col="${cid}" 
                       placeholder="${escHtml(lib)}" 
                       value="${escHtml(val)}" 
                       autocomplete="off"
                       ${readonly}
                       ${oninput}>
            </td>
        `;
    });

    html += `
        <td style="text-align:center;">
            <button class="btn-rm-row" onclick="supprimerLigne(this,'${secId}')" title="Supprimer">✕</button>
        </td>
    `;
    tr.innerHTML = html;
}

function supprimerLigne(btn, secId) {
    const tbody = document.getElementById('tbody-' + secId);
    if (tbody.querySelectorAll('tr[data-ligne]').length <= 1) {
        alert('Il faut au moins une ligne.'); return;
    }
    btn.closest('tr').remove();
    tbody.querySelectorAll('tr[data-ligne]').forEach(function(tr, i) {
        tr.dataset.ligne = i + 1;
        tr.querySelector('td').innerText = i + 1;
    });
    lignesCount[secId] = tbody.querySelectorAll('tr[data-ligne]').length;
    calculerTotaux(secId);
    mettreAJourResume();
    mettreAJourBoutonSoumettre();
}

// ============================================================
// CALCULS
// ============================================================
function calculerTotaux(secId) {
    const sec   = sections.find(function(s) { return s.id === secId; });
    const tbody = document.getElementById('tbody-' + secId);
    if (!sec || !tbody) return;

    const colQte = sec.colonnesIds.find(function(cid) { const c = COLONNES_DISPO.find(function(x) { return x.id === cid; }); return c && estQte(c.libelle); });
    const colPU  = sec.colonnesIds.find(function(cid) { const c = COLONNES_DISPO.find(function(x) { return x.id === cid; }); return c && estPU(c.libelle); });
    const colPT  = sec.colonnesIds.find(function(cid) { const c = COLONNES_DISPO.find(function(x) { return x.id === cid; }); return c && estPT(c.libelle); });

    let totalSection = 0;

    tbody.querySelectorAll('tr[data-ligne]').forEach(function(tr) {
        if (colQte && colPU && colPT) {
            const qte = parseFloat((tr.querySelector('[data-col="' + colQte + '"]')?.value ?? '0').replace(/\s/g,'').replace(',','.')) || 0;
            const pu  = parseFloat((tr.querySelector('[data-col="' + colPU + '"]')?.value ?? '0').replace(/\s/g,'').replace(',','.')) || 0;
            const pt  = qte * pu;
            const inp = tr.querySelector('[data-col="' + colPT + '"]');
            if (inp) inp.value = pt > 0 ? fmt(pt) : '';
            totalSection += pt;
        } else if (colPT) {
            const ptStr = tr.querySelector('[data-col="' + colPT + '"]')?.value ?? '0';
            totalSection += parseFloat(ptStr.replace(/\s/g,'').replace(',','.')) || 0;
        }
    });

    sec.totalCalcule = totalSection;
    const el = document.getElementById('total-sec-' + secId);
    if (el) el.innerText = fmt(totalSection) + ' FCFA';

    mettreAJourTotalGlobal();
    mettreAJourResume();
}

function mettreAJourTotalGlobal() {
    const total = sections.reduce(function(s, sec) { return s + (sec.totalCalcule || 0); }, 0);
    const el    = document.getElementById('total-global-val');
    const bar   = document.getElementById('total-global-bar');
    const res   = document.getElementById('resume-total-global');
    if (el)  el.innerText  = fmt(total) + ' FCFA';
    if (res) res.innerText = fmt(total) + ' FCFA';
    if (bar) bar.style.display = total > 0 ? 'block' : 'none';
}

// ============================================================
// RÉSUMÉ SIDEBAR
// ============================================================
function mettreAJourTitre() {
    const t  = document.getElementById('fiche-titre')?.value?.trim() || 'Sans titre';
    const el = document.getElementById('resume-titre');
    if (el) el.innerText = t;
}

function mettreAJourResume() {
    const c = document.getElementById('resume-sections');
    if (!c) return;

    if (sections.length === 0) {
        c.innerHTML = '<div style="color:#475569;font-size:12px;">Aucune section</div>';
        return;
    }

    let html = '';
    sections.forEach(function(sec) {
        const titre  = document.getElementById('titre-' + sec.id)?.value?.trim() || 'Section sans titre';
        const tbody  = document.getElementById('tbody-' + sec.id);
        const nbL    = tbody ? tbody.querySelectorAll('tr[data-ligne]').length : 0;
        const nbC    = sec.colonnesIds.length;
        const total  = sec.totalCalcule || 0;
        const ok     = nbC > 0 && nbL > 0 && total > 0;
        const partiel= nbC > 0 && nbL > 0 && total === 0;

        html += `
            <div class="resume-section ${ok ? 'ok' : (partiel ? 'partiel' : '')}"
                 onclick="document.getElementById('card-${sec.id}')?.scrollIntoView({behavior:'smooth'})">
                <div class="sec-titre">${ok ? '✅' : (partiel ? '⏳' : '○')} ${escHtml(titre)}</div>
                <div class="sec-stats">
                    ${nbC} col. · ${nbL} ligne(s)
                    ${total > 0 ? '· <strong style="color:#16a34a">' + fmt(total) + ' FCFA</strong>' : ''}
                </div>
            </div>
        `;
    });
    c.innerHTML = html;
}

// ============================================================
// BOUTON
// ============================================================
function mettreAJourBoutonSoumettre() {
    const btnS  = document.getElementById('btn-soumettre');
    const btnB  = document.getElementById('btn-brouillon');
    const hint  = document.getElementById('hint-soumettre');
    const titre = document.getElementById('fiche-titre')?.value?.trim();

    const hasTitre = !!titre;
    const hasDonnees = sections.some(function(sec) {
        const tbody = document.getElementById('tbody-' + sec.id);
        return sec.colonnesIds.length > 0 && tbody && tbody.querySelectorAll('tr[data-ligne]').length > 0;
    });

    if (btnB) btnB.disabled = !hasTitre;
    if (btnS) btnS.disabled = !(hasTitre && hasDonnees);

    if (hint) {
        if (!hasTitre) {
            hint.innerText   = 'Saisissez un titre pour continuer.';
            hint.style.color = '#64748b';
        } else if (!hasDonnees) {
            hint.innerText   = 'Remplissez au moins une section.';
            hint.style.color = '#64748b';
        } else {
            hint.innerText   = '✅ Prêt à soumettre.';
            hint.style.color = '#16a34a';
        }
    }
}

// ============================================================
// SOUMETTRE / BROUILLON
// ============================================================
function soumettre(action) {
    if (action === undefined) action = 'soumettre';
    const titre = document.getElementById('fiche-titre')?.value?.trim();
    const desc  = document.getElementById('fiche-desc')?.value?.trim();

    if (!titre) { alert('Saisissez un titre.'); return; }

    if (action === 'soumettre') {
        if (!confirm('Soumettre définitivement cette fiche ? Elle sera transmise à l\'administration.')) return;
    }

    // Récupérer les IDs des destinataires sélectionnés
    const destinataireIds = selectedDestinataires.map(function(d) { return d.id; });

    const payload = {
        titre: titre,
        description: desc,
        action: action,
        fiche_id: FICHE_ID,
        modele_id: MODELE_ID,
        destinataires: destinataireIds,
        sections: sections.map(function(sec, i) {
            const tbody  = document.getElementById('tbody-' + sec.id);
            const lignes = [];
            if (tbody) {
                tbody.querySelectorAll('tr[data-ligne]').forEach(function(tr) {
                    const vals = {};
                    tr.querySelectorAll('input[data-col]').forEach(function(inp) {
                        vals[inp.dataset.col] = inp.value;
                    });
                    lignes.push(vals);
                });
            }
            return {
                titre:    document.getElementById('titre-' + sec.id)?.value?.trim() || ('Section ' + (i+1)),
                colonnes: sec.colonnesIds,
                lignes: lignes,
            };
        }),
    };

    const btnS = document.getElementById('btn-soumettre');
    const btnB = document.getElementById('btn-brouillon');
    btnS.disabled = true;
    btnB.disabled = true;
    btnS.innerText = action === 'soumettre' ? '⏳ Envoi...' : '✅ Soumettre la fiche';
    btnB.innerText = action === 'brouillon' ? '⏳ Sauvegarde...' : '💾 Sauvegarder brouillon';

    fetch(URL_SOUMETTRE, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(payload),
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        if (data.success) {
            if (action === 'soumettre' && data.pdf_url) {
                window.open(data.pdf_url, '_blank');
                setTimeout(function() { window.location.href = URL_RETOUR; }, 600);
            } else {
                window.location.href = URL_RETOUR;
            }
        } else {
            alert('Erreur : ' + (data.message || 'inconnue'));
            mettreAJourBoutonSoumettre();
            btnS.innerText = '✅ Soumettre la fiche';
            btnB.innerText = '💾 Sauvegarder brouillon';
        }
    })
    .catch(function(e) {
        alert('Erreur réseau : ' + e.message);
        mettreAJourBoutonSoumettre();
        btnS.innerText = '✅ Soumettre la fiche';
        btnB.innerText = '💾 Sauvegarder brouillon';
    });
}

// ============================================================
// UTILITAIRES
// ============================================================
function fmt(n) {
    if (!n && n !== 0) return '0';
    return Math.round(n).toLocaleString('fr-FR');
}
function escHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g,'&amp;').replace(/</g,'&lt;')
        .replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>