<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Section — {{ $section->titre }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f9; font-family:"Segoe UI",sans-serif; }
        .topbar { background:linear-gradient(135deg,#1d4ed8,#7c3aed); color:white; padding:0 28px; height:60px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; }
        .content { max-width:1200px; margin:24px auto; padding:0 16px; }
        .card { background:white; border-radius:14px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }

        /* Colonnes à cocher */
        .colonne-check { display:inline-flex; align-items:center; gap:6px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; padding:6px 12px; margin:4px; cursor:pointer; font-size:13px; transition:0.15s; }
        .colonne-check input { display:none; }
        .colonne-check.checked { background:#dbeafe; border-color:#1d4ed8; color:#1d4ed8; font-weight:700; }
        .colonne-check:hover { border-color:#1d4ed8; }

        /* Tableau dynamique */
        #tableau-preview { width:100%; border-collapse:collapse; margin-top:12px; }
        #tableau-preview th { background:#1e3a5f; color:white; padding:10px 12px; font-size:12px; font-weight:700; text-align:left; }
        #tableau-preview td { border-bottom:1px solid #e2e8f0; padding:6px 4px; }
        #tableau-preview td input { border:none; background:transparent; width:100%; font-size:13px; padding:4px 6px; outline:none; }
        #tableau-preview td input:focus { background:#eff6ff; border-radius:4px; }
        #tableau-preview tr:nth-child(even) td { background:#f8fafc; }
        #tableau-preview tr:nth-child(even) td input:focus { background:#dbeafe; }

        .btn-add-ligne { background:white; border:2px dashed #1d4ed8; color:#1d4ed8; border-radius:8px; padding:8px; width:100%; font-weight:700; cursor:pointer; margin-top:8px; }
        .btn-add-ligne:hover { background:#eff6ff; }
        .btn-rm-ligne { background:none; border:none; color:#dc2626; cursor:pointer; font-size:14px; padding:4px; }
        .btn-save { background:linear-gradient(135deg,#16a34a,#15803d); color:white; border:none; border-radius:12px; padding:12px 28px; font-weight:800; font-size:15px; cursor:pointer; }
    </style>
</head>
<body>
<div class="topbar">
    <div>
        <div style="font-weight:800;">📋 {{ $fiche->titre }}</div>
        <div style="font-size:11px;opacity:0.8;">Section : {{ $section->titre }}</div>
    </div>
    <a href="{{ route('feb.fiches.remplir', $fiche->id) }}" style="color:rgba(255,255,255,0.8);text-decoration:none;font-size:12px;">← Retour aux sections</a>
</div>

<div class="content">

    {{-- Sélection colonnes --}}
    <div class="card">
        <h6 style="font-weight:800;color:#1e3a5f;margin-bottom:12px;">1️⃣ Choisissez les colonnes de votre tableau</h6>
        <div id="colonnes-select">
            @foreach($colonnes as $col)
            <label class="colonne-check {{ $section->colonnes->contains($col->id) ? 'checked' : '' }}"
                   id="label-col-{{ $col->id }}"
                   onclick="toggleColonne({{ $col->id }}, '{{ addslashes($col->libelle) }}', this)">
                <input type="checkbox" value="{{ $col->id }}"
                       {{ $section->colonnes->contains($col->id) ? 'checked' : '' }}>
                {{ $col->libelle }}
            </label>
            @endforeach
        </div>
        @if($colonnes->isEmpty())
            <div style="color:#94a3b8;font-size:13px;">Aucune colonne disponible. L'administrateur doit en créer.</div>
        @endif
    </div>

    {{-- Tableau dynamique --}}
    <div class="card">
        <h6 style="font-weight:800;color:#1e3a5f;margin-bottom:12px;">2️⃣ Remplissez le tableau</h6>

        <div style="overflow-x:auto;">
            <table id="tableau-preview">
                <thead id="thead-preview">
                    <tr id="header-row">
                        <th style="width:40px;">#</th>
                        {{-- colonnes générées dynamiquement --}}
                        @foreach($section->colonnes as $col)
                        <th data-col-id="{{ $col->id }}">{{ $col->libelle }}</th>
                        @endforeach
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody id="tbody-preview">
                    @if($section->lignes->count())
                        @foreach($section->lignes as $ligne)
                        <tr data-ligne="{{ $ligne->numero_ligne }}">
                            <td style="color:#94a3b8;font-size:11px;text-align:center;">{{ $ligne->numero_ligne }}</td>
                            @foreach($section->colonnes as $col)
                            <td>
                                <input type="text"
                                       data-col="{{ $col->id }}"
                                       value="{{ $ligne->valeurs[$col->id] ?? '' }}"
                                       placeholder="{{ $col->libelle }}">
                            </td>
                            @endforeach
                            <td style="text-align:center;">
                                <button class="btn-rm-ligne" onclick="supprimerLigne(this)">🗑</button>
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
        </div>

        <button type="button" class="btn-add-ligne" onclick="ajouterLigne()">+ Ajouter une ligne</button>
    </div>

    {{-- Bouton sauvegarder --}}
    <div style="display:flex;justify-content:space-between;align-items:center;margin-top:8px;">
        <a href="{{ route('feb.fiches.remplir', $fiche->id) }}" class="btn btn-light">← Retour</a>
        <button class="btn-save" onclick="sauvegarder()">💾 Enregistrer cette section</button>
    </div>
</div>

<script>
const FICHE_ID   = {{ $fiche->id }};
const SECTION_ID = {{ $section->id }};
const CSRF       = '{{ csrf_token() }}';

// Colonnes sélectionnées : [ {id, libelle} ]
let colonnesSelectionnees = [
    @foreach($section->colonnes as $col)
    { id: {{ $col->id }}, libelle: '{{ addslashes($col->libelle) }}' },
    @endforeach
];

let ligneCount = {{ $section->lignes->count() ?: 0 }};

// ============================================================
// Cocher / décocher une colonne
// ============================================================
function toggleColonne(id, libelle, label) {
    const idx = colonnesSelectionnees.findIndex(c => c.id === id);
    if (idx === -1) {
        colonnesSelectionnees.push({ id, libelle });
        label.classList.add('checked');
    } else {
        colonnesSelectionnees.splice(idx, 1);
        label.classList.remove('checked');
    }
    reconstruireEntete();
    mettreAJourLignes();
}

// Reconstruire l'entête du tableau
function reconstruireEntete() {
    const row = document.getElementById('header-row');
    // Vider sauf # et actions
    row.innerHTML = '<th style="width:40px;">#</th>';
    colonnesSelectionnees.forEach(col => {
        const th = document.createElement('th');
        th.dataset.colId = col.id;
        th.innerText = col.libelle;
        row.appendChild(th);
    });
    const th = document.createElement('th');
    th.style.width = '40px';
    row.appendChild(th);
}

// Mettre à jour les colonnes dans chaque ligne existante
function mettreAJourLignes() {
    document.querySelectorAll('#tbody-preview tr').forEach(tr => {
        const num = tr.dataset.ligne;
        // Récupérer les valeurs existantes
        const vals = {};
        tr.querySelectorAll('td input[data-col]').forEach(inp => {
            vals[inp.dataset.col] = inp.value;
        });
        // Reconstruire la ligne
        tr.innerHTML = `<td style="color:#94a3b8;font-size:11px;text-align:center;">${num}</td>`;
        colonnesSelectionnees.forEach(col => {
            const td  = document.createElement('td');
            const inp = document.createElement('input');
            inp.type        = 'text';
            inp.dataset.col = col.id;
            inp.value       = vals[col.id] ?? '';
            inp.placeholder = col.libelle;
            td.appendChild(inp);
            tr.appendChild(td);
        });
        const tdRm = document.createElement('td');
        tdRm.style.textAlign = 'center';
        tdRm.innerHTML = '<button class="btn-rm-ligne" onclick="supprimerLigne(this)">🗑</button>';
        tr.appendChild(tdRm);
    });
}

// ============================================================
// Gestion des lignes
// ============================================================
function ajouterLigne() {
    if (colonnesSelectionnees.length === 0) {
        alert('Sélectionnez au moins une colonne avant d\'ajouter des lignes.');
        return;
    }
    ligneCount++;
    const tbody = document.getElementById('tbody-preview');
    const tr    = document.createElement('tr');
    tr.dataset.ligne = ligneCount;
    tr.innerHTML = `<td style="color:#94a3b8;font-size:11px;text-align:center;">${ligneCount}</td>`;
    colonnesSelectionnees.forEach(col => {
        const td  = document.createElement('td');
        const inp = document.createElement('input');
        inp.type        = 'text';
        inp.dataset.col = col.id;
        inp.placeholder = col.libelle;
        td.appendChild(inp);
        tr.appendChild(td);
    });
    const tdRm = document.createElement('td');
    tdRm.style.textAlign = 'center';
    tdRm.innerHTML = '<button class="btn-rm-ligne" onclick="supprimerLigne(this)">🗑</button>';
    tr.appendChild(tdRm);
    tbody.appendChild(tr);
    // Focus sur la première cellule
    tr.querySelector('input')?.focus();
}

function supprimerLigne(btn) {
    if (!confirm('Supprimer cette ligne ?')) return;
    btn.closest('tr').remove();
    // Renuméroter
    document.querySelectorAll('#tbody-preview tr').forEach((tr, i) => {
        tr.dataset.ligne = i + 1;
        tr.querySelector('td').innerText = i + 1;
    });
    ligneCount = document.querySelectorAll('#tbody-preview tr').length;
}

// ============================================================
// Sauvegarder via AJAX
// ============================================================
function sauvegarder() {
    const colonnes = colonnesSelectionnees.map(c => c.id);

    const lignes = [];
    document.querySelectorAll('#tbody-preview tr').forEach((tr, idx) => {
        const vals = {};
        tr.querySelectorAll('input[data-col]').forEach(inp => {
            vals[inp.dataset.col] = inp.value;
        });
        lignes.push(vals);
    });

    fetch(`/feb/fiches/${FICHE_ID}/section/${SECTION_ID}/sauvegarder`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ colonnes, lignes }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Notification succès sans reload
            const btn = document.querySelector('.btn-save');
            btn.innerText = '✅ Enregistré !';
            btn.style.background = '#16a34a';
            setTimeout(() => {
                btn.innerText = '💾 Enregistrer cette section';
                btn.style.background = '';
            }, 2000);
        } else alert('Erreur : ' + (data.message || 'inconnue'));
    })
    .catch(e => alert('Erreur réseau : ' + e.message));
}

// Init : si aucune ligne mais colonnes → ajouter une ligne vide
document.addEventListener('DOMContentLoaded', () => {
    if (colonnesSelectionnees.length > 0 && document.querySelectorAll('#tbody-preview tr').length === 0) {
        ajouterLigne();
    }
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>