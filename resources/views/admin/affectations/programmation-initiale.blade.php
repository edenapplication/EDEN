@extends('admin.affectations.layout')
@section('content')

<style>
.prog-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(160px, 1fr)); gap:12px; margin-bottom:20px; }
.prog-stat {
    background:white; border-radius:12px; padding:14px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    border-left:4px solid #1d4ed8;
    display:flex; align-items:center; gap:12px;
}
.prog-stat .ico { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:18px; flex-shrink:0; }
.prog-stat .lbl { font-size:10px; color:#64748b; text-transform:uppercase; font-weight:700; }
.prog-stat .val { font-size:18px; font-weight:800; color:#1e3a5f; line-height:1.2; }

.prog-table-wrap { background:white; border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05); }

.prog-table { width:100%; border-collapse:collapse; font-size:12px; }
.prog-table thead { background:linear-gradient(135deg,#7c3aed,#6d28d9); color:white; }
.prog-table thead th { padding:10px 8px; text-align:left; font-weight:700; font-size:10px; text-transform:uppercase; letter-spacing:0.3px; white-space:nowrap; }
.prog-table tbody tr { border-bottom:1px solid #f1f5f9; transition:0.15s; }
.prog-table tbody tr:hover { background:#faf5ff; }
.prog-table tbody td { padding:10px 8px; vertical-align:middle; }

.prog-table tbody tr.incomplet { background:#fffbeb; border-left:4px solid #f59e0b; }
.prog-table tbody tr.sans-geo  { background:#fef2f2; border-left:4px solid #dc2626; }
.prog-table tbody tr.validee-row { background:#f0fdf4; border-left:4px solid #16a34a; opacity:0.85; }

.prog-num { display:inline-flex; align-items:center; justify-content:center; width:26px; height:26px; border-radius:50%; background:#7c3aed; color:white; font-size:11px; font-weight:800; }
.prog-nom { font-weight:700; color:#1e3a5f; font-size:12px; line-height:1.3; }
.prog-cell-bold { font-weight:700; color:#1e3a5f; }
.prog-cell-muted { color:#64748b; font-size:11px; }

.heure-input { border:1px solid #e2e8f0; border-radius:6px; padding:4px 6px; font-size:11px; font-weight:700; width:80px; transition:all 0.2s; }
.heure-input:hover, .heure-input:focus { border-color:#7c3aed; outline:none; box-shadow:0 0 0 3px rgba(124,58,237,0.1); }
.heure-input:disabled { background:#f1f5f9; cursor:not-allowed; opacity:0.7; }

.geo-select { border:1px solid #e2e8f0; border-radius:6px; padding:4px 6px; font-size:11px; min-width:130px; transition:all 0.2s; background:white; }
.geo-select:hover:not(:disabled), .geo-select:focus { border-color:#7c3aed; outline:none; box-shadow:0 0 0 3px rgba(124,58,237,0.1); }
.geo-select:disabled { background:#f1f5f9; cursor:not-allowed; opacity:0.7; }

.frais-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 4px 10px; border-radius: 12px;
    font-size: 10px; font-weight: 800; user-select: none;
}
.frais-badge.paye { background: #dcfce7; color: #166534; border: 2px solid #86efac; }
.frais-badge.impaye { background: #fee2e2; color: #991b1b; border: 2px solid #fca5a5; }

.toast-notification { position:fixed; bottom:20px; right:20px; background:#1f2937; color:#fff; padding:12px 20px; border-radius:8px; font-size:14px; box-shadow:0 4px 12px rgba(0,0,0,0.3); z-index:99999; max-width:400px; animation:slideInToast 0.3s ease; }
.toast-notification.success { background:#16a34a; }
.toast-notification.error   { background:#dc2626; }
.toast-notification.warning { background:#f59e0b; }
@keyframes slideInToast { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }

.empty-state { text-align:center; padding:60px 20px; color:#94a3b8; }
.empty-state .ico { font-size:56px; margin-bottom:14px; }

.badge-validee {
    display:inline-flex; align-items:center; gap:3px;
    background:#dcfce7; color:#166534;
    padding:2px 8px; border-radius:8px;
    font-size:9px; font-weight:800;
    margin-left:6px;
}

/* ═══ MODALE TITRE ═══ */
#titreModalOverlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,0.5);
    z-index: 9998;
}
#titreModalOverlay.visible { display: block; }

#titreModal {
    display: none;
    position: fixed; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 24px;
    border-radius: 14px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.25);
    z-index: 9999;
    width: 520px; max-width: 95%; max-height: 90vh;
    overflow-y: auto;
}
#titreModal.visible { display: block; }
</style>

{{-- EN-TÊTE --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">
            📝 Étape 1 — Programmation des implantations
        </h2>
        <div style="font-size:13px;color:#64748b;">
            Définissez le <strong>géomètre</strong> et l'<strong>heure d'implantation</strong>.
            Une fois complétées, validez en bas de page pour passer à l'étape 2.
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('affectations.programmation-active') }}" class="btn btn-warning btn-sm">
            ➡️ Paser a l'appreciation des implantation
        </a>
        <a href="{{ route('affectations.documents.initiales') }}"
           class="btn btn-sm"
           style="background:linear-gradient(135deg,#5b21b6,#7c3aed);
                  color:white;border:none;font-weight:800;
                  box-shadow:0 3px 10px rgba(91,33,182,0.3);">
            📅 Mes rapports d'implantation
        </a>
        <button onclick="window.location.reload()" class="btn btn-outline-primary btn-sm">
            🔄 Actualiser
        </button>
    </div>
</div>

{{-- 📅 BANDEAU DATE ACTIVE --}}
<div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);
            border:2px solid #1d4ed8;border-radius:14px;
            padding:14px 20px;margin-bottom:16px;
            display:flex;justify-content:space-between;
            align-items:center;gap:14px;flex-wrap:wrap;">
    <div>
        @if($dateActive)
            <div style="font-weight:800;color:#1e3a5f;font-size:15px;">
                📅 Date sélectionnée :
                <strong>{{ $dateActive->translatedFormat('l d F Y') }}</strong>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">
                {{ $dateActive->format('d/m/Y') }} — Les affectations affichées correspondent à cette date.
            </div>
        @else
            <div style="font-weight:800;color:#dc2626;font-size:15px;">
                ⚠️ Aucune date sélectionnée
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;">
                Cliquez sur le bouton ci-contre pour choisir une date.
            </div>
        @endif
    </div>
    <a href="{{ route('affectations.programmation-choix') }}"
       style="background:linear-gradient(135deg,#1d4ed8,#1e40af);color:white;
              border:none;border-radius:10px;padding:10px 20px;
              font-size:13px;font-weight:800;text-decoration:none;
              display:inline-flex;align-items:center;gap:6px;
              box-shadow:0 4px 12px rgba(29,78,216,0.3);">
        🔄 Changer de date
    </a>
</div>

{{-- STATISTIQUES --}}
<div class="prog-stats">
    <div class="prog-stat" style="border-left-color:#7c3aed;">
        <div class="ico" style="background:#ede9fe;color:#7c3aed;">📝</div>
        <div>
            <div class="lbl">Total</div>
            <div class="val">{{ $stats['total'] }}</div>
        </div>
    </div>
    <div class="prog-stat" style="border-left-color:#dc2626;">
        <div class="ico" style="background:#fee2e2;color:#dc2626;">👷</div>
        <div>
            <div class="lbl">Sans géomètre</div>
            <div class="val">{{ $stats['sans_geo'] }}</div>
        </div>
    </div>
    <div class="prog-stat" style="border-left-color:#f59e0b;">
        <div class="ico" style="background:#fef3c7;color:#f59e0b;">⏰</div>
        <div>
            <div class="lbl">Sans heure</div>
            <div class="val">{{ $stats['sans_heure'] }}</div>
        </div>
    </div>
    <div class="prog-stat" style="border-left-color:#16a34a;">
        <div class="ico" style="background:#dcfce7;color:#16a34a;">✅</div>
        <div>
            <div class="lbl">Validées</div>
            <div class="val">{{ $stats['validees'] }}</div>
        </div>
    </div>
    <div class="prog-stat" style="border-left-color:#7c3aed;">
        <div class="ico" style="background:#ede9fe;color:#7c3aed;">📐</div>
        <div>
            <div class="lbl">Superficie</div>
            <div class="val">{{ number_format($stats['superficie'], 0, ',', ' ') }} <small style="font-size:11px;color:#64748b;">m²</small></div>
        </div>
    </div>
</div>

{{-- TABLEAU --}}
<div class="prog-table-wrap">
    @if($lignes->count() > 0)
    <div style="overflow-x:auto;">
        <table class="prog-table">
            <thead>
                <tr>
                    <th style="width:50px;">N°</th>
                    <th>Noms et Prénoms</th>
                    <th>Titre Foncier</th>
                    <th>Bloc</th>
                    <th>Lots</th>
                    <th>Superficie</th>
                    <th>Facilitateur</th>
                    <th>Téléphone</th>
                    <th>📅 Date</th>
                    <th>👷 Géomètre</th>
                    <th>⏰ Heure</th>
                    <th style="text-align:center;">💰 Frais</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lignes as $index => $ligne)
                @php
                    $hasGeo    = !empty($ligne['geometre_id']);
                    $hasHeure  = !empty($ligne['heure_implantation']);
                    $isValidee = $ligne['validee'] ?? false;

                    if ($isValidee) {
                        $rowClass = 'validee-row';
                    } elseif (!$hasGeo) {
                        $rowClass = 'sans-geo';
                    } elseif (!$hasHeure) {
                        $rowClass = 'incomplet';
                    } else {
                        $rowClass = '';
                    }
                @endphp
                <tr class="{{ $rowClass }}"
                    id="row-{{ $ligne['affectation_ids'][0] ?? $index }}">
                    <td style="text-align:center;">
                        <span class="prog-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    </td>
                    <td>
                        <div class="prog-nom">
                            {{ $ligne['beneficiaire'] }}
                            @if($isValidee)
                                <span class="badge-validee">✅ Validée</span>
                            @endif
                        </div>
                    </td>
                    <td><span class="prog-cell-bold">{{ $ligne['titre_foncier'] }}</span></td>
                    <td><span class="prog-cell-bold">{{ $ligne['bloc'] }}</span></td>
                    <td><span class="prog-cell-bold">{{ $ligne['lots'] }}</span></td>
                    <td>
                        <span class="prog-cell-bold" style="color:#7c3aed;">
                            {{ number_format($ligne['superficie'], 0, ',', ' ') }} m²
                        </span>
                    </td>
                    <td><span class="prog-cell-muted">{{ $ligne['facilitateur'] }}</span></td>
                    <td><span class="prog-cell-muted">📞 {{ $ligne['telephone'] }}</span></td>

                    <td>
                        @if($ligne['date_implantation'])
                            <span style="font-size:11px;font-weight:700;color:#1e3a5f;background:#eff6ff;padding:3px 8px;border-radius:6px;display:inline-block;">
                                📅 {{ \Carbon\Carbon::parse($ligne['date_implantation'])->format('d/m/Y') }}
                            </span>
                        @else
                            <span style="font-size:11px;color:#94a3b8;">—</span>
                        @endif
                    </td>

                    <td>
                        <select class="geo-select"
                                data-affectation-ids='@json($ligne["affectation_ids"])'
                                onchange="majProgrammationInitiale(this, 'geometre_id')"
                                {{ $isValidee ? 'disabled' : '' }}>
                            <option value="">— Choisir —</option>
                            @foreach($geometres as $geo)
                                <option value="{{ $geo->id }}"
                                    {{ $ligne['geometre_id'] == $geo->id ? 'selected' : '' }}>
                                    {{ $geo->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>

                    <td>
                        <input type="time"
                               class="heure-input"
                               value="{{ $ligne['heure_implantation'] ? substr($ligne['heure_implantation'], 0, 5) : '' }}"
                               data-affectation-ids='@json($ligne["affectation_ids"])'
                               onchange="majProgrammationInitiale(this, 'heure_implantation')"
                               {{ $isValidee ? 'disabled' : '' }}>
                    </td>

                    <td style="text-align:center;">
                        <span class="frais-badge {{ $ligne['frais_paye'] ? 'paye' : 'impaye' }}">
                            @if($ligne['frais_paye'])
                                ✅ Payé
                            @else
                                ❌ Non payé
                            @endif
                        </span>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="ico">📭</div>
        <div style="font-weight:700;font-size:15px;color:#475569;">
            @if($dateActive)
                Aucune affectation pour le {{ $dateActive->format('d/m/Y') }}
            @else
                Aucune date sélectionnée
            @endif
        </div>
        <div style="font-size:12px;margin-top:6px;">
            @if($dateActive)
                Aucune affectation n'a été clôturée pour cette date.
            @else
                Choisissez d'abord une date dans le calendrier.
            @endif
        </div>
        <a href="{{ route('affectations.programmation-choix') }}" class="btn btn-primary btn-sm" style="margin-top:14px;">
            📅 Choisir une date
        </a>
    </div>
    @endif
</div>

{{-- ✅ BOUTON VALIDER L'ÉTAPE 1 --}}
@php
    $lignesNonValidees = $lignes->where('validee', false);

    // ✅ Vérifier que géomètre + heure sont remplis
    $lignesIncompletes = $lignesNonValidees->filter(fn($l) =>
        empty($l['geometre_id']) || empty($l['heure_implantation'])
    )->count();

    $peutValider = $lignesNonValidees->count() > 0 && $lignesIncompletes === 0;
@endphp

@if($lignesNonValidees->count() > 0)
<div style="margin-top:20px;padding:20px;background:white;border-radius:12px;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);
            border-left:4px solid {{ $peutValider ? '#7c3aed' : '#dc2626' }};
            display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
    <div>
        <div style="font-weight:800;color:#1e3a5f;font-size:15px;">
            @if($peutValider)
                ✅ Valider la programmation initiale
            @else
                ⚠️ Complétez tous les géomètres et heures
            @endif
        </div>
        <div style="font-size:12px;color:#64748b;margin-top:4px;">
            @if($peutValider)
                Les <strong>{{ $lignesNonValidees->count() }}</strong> ligne(s) non validées passeront à l'Étape 2 et
                un <strong>PDF sera généré automatiquement</strong>.
            @else
                <strong style="color:#dc2626;">{{ $lignesIncompletes }} ligne(s)</strong> ont encore un géomètre ou une heure manquante.
                Complétez-les pour pouvoir valider.
            @endif
        </div>
    </div>
    <button onclick="validerEtape1()" id="btn-valider-1"
            {{ $peutValider ? '' : 'disabled' }}
            style="background:{{ $peutValider ? 'linear-gradient(135deg,#7c3aed,#6d28d9)' : '#cbd5e1' }};
                   color:white;border:none;border-radius:10px;padding:14px 28px;
                   font-size:14px;font-weight:800;
                   cursor:{{ $peutValider ? 'pointer' : 'not-allowed' }};">
        @if($peutValider)
            ✅ Valider et passer à l'Étape 2
        @else
            ⚠️ Complétez géomètre + heure
        @endif
    </button>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODALE TITRE DU DOCUMENT                                     --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div id="titreModalOverlay" onclick="fermerModalTitre()"></div>
<div id="titreModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1d4ed8;font-weight:800;margin:0;">📄 Titre du document</h5>
        <button onclick="fermerModalTitre()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div style="background:#eff6ff;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:12.5px;color:#1e40af;">
        ℹ️ Donnez un titre à ce document PDF. Il sera utilisé comme <strong>nom du fichier</strong> et stocké en base.
    </div>

    <label style="font-size:12px;font-weight:700;color:#64748b;">
        📝 Titre du document <span style="color:#dc2626;">*</span>
    </label>
    <input type="text"
           id="titreDocumentInput"
           class="form-control form-control-sm"
           style="margin-top:6px;"
           maxlength="150"
           placeholder="Ex : Rapport d'implantation">

    <div style="font-size:11px;color:#94a3b8;margin-top:10px;">
        💡 Fichier :<strong id="apercuNomFichier">rapport-implantation-{{ now()->format('Y-m-d') }}.pdf</strong>
    </div>

    <div id="titreError" style="display:none;margin-top:10px;padding:8px 12px;background:#fee2e2;border-radius:6px;font-size:11px;color:#991b1b;">
        ⚠️ Le titre est obligatoire (min. 3 caractères).
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerModalTitre()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="confirmerTitreEtValider()" class="btn btn-primary btn-sm" style="font-weight:700;">
            ✅ Confirmer et télécharger
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF = window.CSRF || '{{ csrf_token() }}';

let actionEnAttente = null;

// ════════════════════════════════════════════════════════════════
// 🎯 MODALE TITRE
// ════════════════════════════════════════════════════════════════
function demanderTitre(callback, titreParDefaut = '') {
    actionEnAttente = callback;

    const dateStr = '{{ now()->format("Y-m-d") }}';
    const input = document.getElementById('titreDocumentInput');

    input.value = titreParDefaut;
    document.getElementById('titreError').style.display = 'none';

    const updateApercu = () => {
        const val = input.value.trim() || 'rapport';
        const slug = val.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
        document.getElementById('apercuNomFichier').textContent =
            (slug || 'rapport') + '-' + dateStr + '.pdf';
    };

    input.oninput = updateApercu;
    updateApercu();

    document.getElementById('titreModalOverlay').classList.add('visible');
    document.getElementById('titreModal').classList.add('visible');

    setTimeout(() => {
        input.focus();
        input.select();
    }, 100);
}

function fermerModalTitre() {
    document.getElementById('titreModalOverlay').classList.remove('visible');
    document.getElementById('titreModal').classList.remove('visible');
}

function confirmerTitreEtValider() {
    const titre = document.getElementById('titreDocumentInput').value.trim();

    if (!titre || titre.length < 3) {
        document.getElementById('titreError').style.display = 'block';
        return;
    }

    document.getElementById('titreError').style.display = 'none';

    const callback = actionEnAttente;

    document.getElementById('titreModalOverlay').classList.remove('visible');
    document.getElementById('titreModal').classList.remove('visible');
    actionEnAttente = null;

    if (typeof callback === 'function') {
        callback(titre);
    } else {
        console.error('❌ Aucune action en attente');
    }
}

// ════════════════════════════════════════════════════════════════
// ✅ VALIDER L'ÉTAPE 1
//    → Génère le PDF d'implantation
//    → ET passe les affectations à 'programmee' (Étape 2)
// ════════════════════════════════════════════════════════════════
function validerEtape1() {
    // Vérifier que tous les champs sont remplis
    let manquants = 0;
    document.querySelectorAll('.geo-select:not([disabled])').forEach(sel => {
        if (!sel.value) manquants++;
    });
    document.querySelectorAll('.heure-input:not([disabled])').forEach(inp => {
        if (!inp.value) manquants++;
    });

    if (manquants > 0) {
        alert(`❌ ${manquants} champ(s) vide(s). Complétez TOUTES les lignes.`);
        return;
    }

    // Récupérer les IDs
    const affectationIds = [];
    document.querySelectorAll('.geo-select:not([disabled]), .heure-input:not([disabled])').forEach(el => {
        try {
            const ids = JSON.parse(el.dataset.affectationIds || '[]');
            ids.forEach(id => {
                if (!affectationIds.includes(id)) affectationIds.push(id);
            });
        } catch (e) {}
    });

    if (affectationIds.length === 0) {
        alert('⚠️ Aucune nouvelle ligne à valider.');
        return;
    }

    // ✅ Titre automatique
    @if($dateActive)
        const dateLabel = '{{ $dateActive->format("d/m/Y") }}';
    @else
        const dateLabel = '{{ now()->format("d/m/Y") }}';
    @endif

    const titreAuto = 'Rapport d\'implantation du ' + dateLabel;

    demanderTitre(function(titre) {
        envoyerValidationEtape1(affectationIds, titre);
    }, titreAuto);
}

function envoyerValidationEtape1(affectationIds, titreDocument) {
    const btn = document.getElementById('btn-valider-1');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '⏳ Traitement...';

    if (window.EdenLoader) window.EdenLoader.show();

    // ✅ UN SEUL appel : validerEtape1 avec type='initiale'
    fetch('{{ route("affectations.valider-etape-1") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            affectation_ids: affectationIds,
            titre_document:  titreDocument,
            type_rapport:    'initiale',
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        btn.disabled = false;
        btn.innerHTML = originalText;

        if (data.success) {
            showToastWithLink(
                '✅ Étape 1 validée — Redirection vers Étape 2.',
                data.doc?.url || null,
                'success'
            );
            // ✅ Rediriger vers Étape 2
            setTimeout(() => {
                window.location.href = '{{ route("affectations.programmation-active") }}';
            }, 1500);
        } else {
            alert('❌ ' + (data.message || 'Erreur'));
        }
    })
    .catch(err => {
        if (window.EdenLoader) window.EdenLoader.hide();
        btn.disabled = false;
        btn.innerHTML = originalText;
        console.error(err);
        alert('❌ Erreur réseau');
    });
}

// ════════════════════════════════════════════════════════════════
// MISE À JOUR (géomètre / heure) — autosave
// ════════════════════════════════════════════════════════════════
function majProgrammationInitiale(el, champ) {
    let affectationIds;
    try { affectationIds = JSON.parse(el.dataset.affectationIds || '[]'); } catch (e) { return; }
    if (!affectationIds || affectationIds.length === 0) return;

    const payload = { affectation_ids: affectationIds };

    if (champ === 'geometre_id') {
        payload.geometre_id = el.value || null;
    } else if (champ === 'heure_implantation') {
        payload.heure_implantation = el.value || null;
    }

    el.disabled = true;
    el.style.opacity = '0.5';

    fetch('{{ route("affectations.groupe.programmation") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        el.disabled = false;
        el.style.opacity = '1';
        if (data.success) {
            showToast('✅ ' + (data.message || 'Enregistré'), 'success');
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('❌ ' + (data.message || 'Erreur'), 'error');
        }
    })
    .catch(err => {
        el.disabled = false;
        el.style.opacity = '1';
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// 🔔 TOASTS
// ════════════════════════════════════════════════════════════════
function showToast(message, type = 'info') {
    document.querySelectorAll('.toast-notification').forEach(el => el.remove());
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

function showToastWithLink(message, url, type = 'success') {
    document.querySelectorAll('.toast-notification').forEach(el => el.remove());

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.style.cssText += 'display:flex; flex-direction:column; gap:8px;';

    const text = document.createElement('div');
    text.textContent = message;
    toast.appendChild(text);

    if (url) {
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.style.cssText = `
            display:inline-flex; align-items:center; gap:6px;
            background:white; color:#16a34a;
            padding:6px 12px; border-radius:6px;
            font-weight:800; font-size:12px;
            text-decoration:none; align-self:flex-start;
            box-shadow:0 2px 6px rgba(0,0,0,0.15);
        `;
        link.innerHTML = '📄 Ouvrir le PDF';
        toast.appendChild(link);
    }

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 6000);
}
</script>
@endsection