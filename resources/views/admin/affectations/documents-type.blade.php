@extends('admin.affectations.layout')
@section('content')

<style>
.doc-hero {
    background: linear-gradient(135deg, {{ $couleurPrincipale }} 0%, {{ $couleurSecondaire }} 100%);
    border-radius: 18px; padding: 28px 32px; color: white;
    margin-bottom: 20px; position: relative; overflow: hidden;
    box-shadow: 0 8px 24px {{ $couleurPrincipale }}55;
}
.doc-hero::before {
    content: ''; position: absolute; top: -50%; right: -10%;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius: 50%;
}
.doc-hero::after {
    content: '📄'; position: absolute; right: 30px; top: 50%;
    transform: translateY(-50%); font-size: 110px; opacity: 0.12;
}
.doc-hero h2 { margin: 0 0 6px 0; font-weight: 900; font-size: 26px; letter-spacing: -0.5px; position: relative; z-index: 1; }
.doc-hero .sub { font-size: 13.5px; opacity: 0.95; position: relative; z-index: 1; }
.doc-hero .count-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.2); padding: 6px 14px;
    border-radius: 20px; font-size: 12px; font-weight: 800;
    margin-top: 10px; position: relative; z-index: 1;
}

.doc-wrap { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
.doc-filters { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:16px; }
.doc-table { width:100%; border-collapse:collapse; font-size:13px; }
.doc-table thead { background: linear-gradient(135deg, {{ $couleurPrincipale }}, {{ $couleurSecondaire }}); color:white; }
.doc-table thead th { padding:12px 14px; text-align:left; font-weight:700; font-size:11px; text-transform:uppercase; }
.doc-table tbody tr { border-bottom:1px solid #f1f5f9; }
.doc-table tbody tr:hover { background: {{ $couleurFond }}; }
.doc-table tbody td { padding:12px 14px; vertical-align:middle; }
.btn-icon { background:none; border:none; cursor:pointer; font-size:16px; padding:4px 8px; border-radius:6px; text-decoration:none; display:inline-block; }
.btn-icon:hover { background: {{ $couleurFond }}; transform:scale(1.1); }

/* ─── BOUTONS D'ACTION ─── */
.btn-action {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 8px;
    border: none;
    cursor: pointer;
    font-size: 14px;
    transition: all 0.2s;
    text-decoration: none;
    padding: 0;
}
.btn-action.view {
    background: {{ $couleurFond }};
    color: {{ $couleurPrincipale }};
}
.btn-action.view:hover {
    background: {{ $couleurPrincipale }};
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px {{ $couleurPrincipale }}55;
}
.btn-action.download {
    background: #dbeafe;
    color: #1d4ed8;
}
.btn-action.download:hover {
    background: #1d4ed8;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(29,78,216,0.3);
}
.btn-action.delete {
    background: #fee2e2;
    color: #dc2626;
}
.btn-action.delete:hover {
    background: #dc2626;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 10px rgba(220,38,38,0.3);
}

.doc-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 12px; margin-bottom: 16px; }
.doc-stat { background: white; border-radius: 12px; padding: 14px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); border-left: 4px solid {{ $couleurPrincipale }}; display: flex; align-items: center; gap: 12px; }
.doc-stat .ico { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; background: {{ $couleurFond }}; color: {{ $couleurPrincipale }}; }
.doc-stat .lbl { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; }
.doc-stat .val { font-size: 18px; font-weight: 800; color: #1e3a5f; line-height: 1.2; }

.filtres-actifs { background: {{ $couleurFond }}; border-left: 4px solid {{ $couleurPrincipale }}; border-radius: 10px; padding: 12px 18px; margin-bottom: 16px; font-size: 12.5px; color: {{ $couleurPrincipale }}; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.filtres-actifs .badge { background: white; color: {{ $couleurPrincipale }}; padding: 3px 10px; border-radius: 8px; font-size: 11px; font-weight: 800; }
.filtres-actifs .btn-retirer { margin-left: auto; color: #dc2626; font-weight: 800; text-decoration: none; padding: 4px 10px; border-radius: 6px; border: 1px solid #fca5a5; background: #fee2e2; font-size: 11px; }
.filtres-actifs .btn-retirer:hover { background: #dc2626; color: white; }

/* ═══════════════════════════════════════════════════════════════ */
/* 🗑️ MODALE SUPPRESSION DOCUMENT                                  */
/* ═══════════════════════════════════════════════════════════════ */
#delDocOverlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(15, 23, 42, 0.55);
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
    z-index: 99998;
    animation: overlayFadeIn 0.25s ease;
}
#delDocOverlay.visible { display: block; }

#delDocModal {
    display: none;
    position: fixed; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    background: white;
    padding: 0;
    border-radius: 16px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.35);
    z-index: 99999;
    width: 500px; max-width: 92%;
    max-height: 90vh;
    overflow: hidden;
    animation: popInModal 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
}
#delDocModal.visible { display: block; }

.del-doc-header {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    padding: 18px 22px;
    display: flex;
    align-items: center;
    gap: 14px;
}
.del-doc-header .icon {
    font-size: 32px;
    line-height: 1;
    animation: shakeLock 0.6s ease;
}
.del-doc-header .title {
    font-weight: 900;
    font-size: 17px;
    letter-spacing: 0.3px;
}
.del-doc-header .sub {
    font-size: 11.5px;
    opacity: 0.92;
    margin-top: 3px;
    word-break: break-all;
}

.del-doc-body {
    padding: 22px;
}
.del-doc-warning {
    background: #fef3c7;
    border-left: 4px solid #f59e0b;
    border-radius: 10px;
    padding: 12px 14px;
    margin-bottom: 16px;
    font-size: 12.5px;
    color: #78350f;
    line-height: 1.55;
}
.del-doc-warning strong {
    color: #92400e;
    font-weight: 900;
}

.del-doc-field {
    margin-bottom: 14px;
}
.del-doc-field label {
    display: block;
    font-size: 12px;
    font-weight: 700;
    color: #475569;
    margin-bottom: 6px;
}

/* ═══════════════════════════════════════════════════════════════ */
/* 🔐 CHAMP MOT DE PASSE PERSONNALISÉ (anti-autofill + œil)        */
/* ═══════════════════════════════════════════════════════════════ */
.custom-pwd-wrapper {
    position: relative;
    width: 100%;
}

/* Le vrai input caché */
.custom-pwd-real {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    opacity: 0;
    pointer-events: none;
    border: none;
    outline: none;
    background: transparent;
    font-size: 1px;
}

/* Le faux input visible qui montre les points */
.custom-pwd-display {
    width: 100%;
    padding: 11px 48px 11px 14px; /* padding-right pour le bouton œil */
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    transition: 0.2s;
    font-family: inherit;
    box-sizing: border-box;
    background: white;
    color: #1e293b;
    cursor: text;
    min-height: 44px;
    display: flex;
    align-items: center;
    letter-spacing: 3px;
    user-select: none;
    position: relative;
}
.custom-pwd-display.focused {
    outline: none;
    border-color: #dc2626;
    box-shadow: 0 0 0 3px rgba(220,38,38,0.12);
}
.custom-pwd-display.error {
    border-color: #dc2626;
    background: #fef2f2;
    animation: shakeInput 0.4s ease;
}
.custom-pwd-display .placeholder {
    color: #94a3b8;
    letter-spacing: 0;
}
.custom-pwd-display .dots {
    color: #1e293b;
    letter-spacing: 3px;
    font-weight: 900;
    font-size: 16px;
}
.custom-pwd-display .plain-text {
    color: #1e293b;
    font-family: 'Courier New', monospace;
    font-size: 13.5px;
    letter-spacing: 0.5px;
    word-break: break-all;
    padding-right: 4px;
}
.custom-pwd-display .caret {
    display: inline-block;
    width: 1px;
    height: 18px;
    background: #1e293b;
    margin-left: 2px;
    animation: caretBlink 1s step-end infinite;
}
@keyframes caretBlink {
    50% { opacity: 0; }
}

/* ─── BOUTON ŒIL ─── */
.btn-toggle-pwd {
    position: absolute;
    top: 50%;
    right: 8px;
    transform: translateY(-50%);
    background: transparent;
    border: none;
    width: 34px;
    height: 34px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 17px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s;
    color: #64748b;
    z-index: 2;
    padding: 0;
}
.btn-toggle-pwd:hover {
    background: rgba(220,38,38,0.1);
    color: #dc2626;
    transform: translateY(-50%) scale(1.12);
}
.btn-toggle-pwd:active {
    transform: translateY(-50%) scale(0.95);
}

@keyframes shakeInput {
    0%, 100% { transform: translateX(0); }
    25%      { transform: translateX(-6px); }
    75%      { transform: translateX(6px); }
}

.del-doc-error {
    display: none;
    background: #fee2e2;
    border-left: 3px solid #dc2626;
    border-radius: 8px;
    padding: 10px 14px;
    font-size: 12.5px;
    color: #991b1b;
    margin-bottom: 14px;
    font-weight: 700;
}
.del-doc-error.visible { display: block; }

.del-doc-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    padding: 0 22px 22px 22px;
}
.btn-modal-cancel {
    background: #f1f5f9;
    color: #475569;
    border: none;
    border-radius: 10px;
    padding: 10px 18px;
    font-size: 13px;
    font-weight: 800;
    cursor: pointer;
    transition: 0.2s;
}
.btn-modal-cancel:hover {
    background: #e2e8f0;
    color: #1e293b;
}
.btn-modal-delete {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 10px 20px;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
    transition: 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}
.btn-modal-delete:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(220,38,38,0.4);
}
.btn-modal-delete:disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

@keyframes popInModal {
    from { transform: translate(-50%, -40%) scale(0.9); opacity: 0; }
    to   { transform: translate(-50%, -50%) scale(1); opacity: 1; }
}
@keyframes overlayFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes shakeLock {
    0%, 100% { transform: rotate(0); }
    20%      { transform: rotate(-12deg); }
    40%      { transform: rotate(12deg); }
    60%      { transform: rotate(-8deg); }
    80%      { transform: rotate(8deg); }
}

/* ─── TOAST ─── */
.toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #1f2937;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 999999;
    max-width: 400px;
    animation: slideInToast 0.3s ease;
}
.toast-notification.success { background: #16a34a; }
.toast-notification.error   { background: #dc2626; }
.toast-notification.warning { background: #f59e0b; }
@keyframes slideInToast {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0); opacity: 1; }
}
</style>

{{-- HERO --}}
<div class="doc-hero">
    <h2>{{ $titre }}</h2>
    <div class="sub">{{ $sousTitre }}</div>
    <div class="count-badge">📊 {{ $documents->total() }} document(s)</div>
</div>

{{-- FILTRES ACTIFS --}}
@if(request('jour') || request('q'))
<div class="filtres-actifs">
    <strong>🔍 Filtres actifs :</strong>
    @if(request('jour'))<span class="badge">📅 {{ \Carbon\Carbon::parse(request('jour'))->format('d/m/Y') }}</span>@endif
    @if(request('q'))<span class="badge">🔍 "{{ request('q') }}"</span>@endif
    <a href="{{ url()->current() }}" class="btn-retirer">✖ Retirer</a>
</div>
@endif

{{-- STATS --}}
<div class="doc-stats">
    <div class="doc-stat"><div class="ico">📄</div><div><div class="lbl">Total</div><div class="val">{{ $statsGlobales['total'] }}</div></div></div>
    <div class="doc-stat"><div class="ico">📋</div><div><div class="lbl">Lignes</div><div class="val">{{ number_format($statsGlobales['lignes'], 0, ',', ' ') }}</div></div></div>
    <div class="doc-stat"><div class="ico">📦</div><div><div class="lbl">Lots</div><div class="val">{{ number_format($statsGlobales['lots'], 0, ',', ' ') }}</div></div></div>
    <div class="doc-stat"><div class="ico">📐</div><div><div class="lbl">Superficie</div><div class="val">{{ number_format($statsGlobales['superficie'], 0, ',', ' ') }} m²</div></div></div>
</div>

{{-- FILTRES --}}
<div class="doc-filters">
    <form method="GET" action="{{ url()->current() }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Date</label>
                <input type="date" name="jour" class="form-control form-control-sm" value="{{ request('jour') }}">
            </div>
            <div class="col-md-4">
                <label style="font-size:11px;font-weight:700;color:#64748b;">🔍 Fichier</label>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Nom du fichier..." value="{{ request('q') }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm flex-fill" style="background:linear-gradient(135deg,{{ $couleurPrincipale }},{{ $couleurSecondaire }});color:white;font-weight:700;">🔍 Filtrer</button>
                <a href="{{ url()->current() }}" class="btn btn-outline-secondary btn-sm">✖</a>
            </div>
        </div>
    </form>
</div>

{{-- TABLEAU --}}
<div class="doc-wrap">
    @if($documents->count() > 0)
    <div style="overflow-x:auto;">
        <table class="doc-table">
            <thead>
                <tr>
                    <th>📅 Date</th><th>📄 Fichier</th><th>Lignes</th><th>Lots</th>
                    <th>Superficie</th><th>👤 Créé par</th><th>🕐 Créé le</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                <tr data-doc-id="{{ $doc->id }}">
                    <td><strong style="color:{{ $couleurPrincipale }};">{{ $doc->date_semaine?->format('d/m/Y') ?? '—' }}</strong></td>
                    <td style="font-size:11px;color:#64748b;max-width:280px;word-break:break-all;">{{ $doc->nom_fichier }}</td>
                    <td><strong style="color:{{ $couleurPrincipale }};">{{ $doc->nb_lignes }}</strong></td>
                    <td><strong style="color:{{ $couleurPrincipale }};">{{ $doc->nb_lots }}</strong></td>
                    <td><span style="font-weight:700;color:{{ $couleurPrincipale }};">{{ number_format($doc->superficie_totale ?? 0, 0, ',', ' ') }} m²</span></td>
                    <td><span style="font-size:12px;color:#475569;">👤 {{ $doc->user?->name ?? '—' }}</span></td>
                    <td style="font-size:11px;color:#64748b;">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                    <td style="text-align:right;white-space:nowrap;">
                        <div style="display:inline-flex;gap:6px;justify-content:flex-end;">
                            <a href="{{ $doc->url }}"
                               target="_blank"
                               class="btn-action view"
                               title="Voir le PDF">
                                👁
                            </a>

                            <a href="{{ $doc->url }}"
                               download
                               class="btn-action download"
                               title="Télécharger">
                                ⬇
                            </a>

                            <button type="button"
                                    class="btn-action delete"
                                    title="Supprimer"
                                    onclick="demanderSuppressionDocument(
                                        {{ $doc->id }},
                                        @js($doc->nom_fichier)
                                    )">
                                🗑️
                            </button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($documents->hasPages())
    <div style="padding:14px 0;border-top:1px solid #f1f5f9;margin-top:14px;">{{ $documents->links() }}</div>
    @endif
    @else
    <div style="text-align:center;padding:60px;color:#94a3b8;">
        <div style="font-size:56px;margin-bottom:14px;">📭</div>
        <div style="font-weight:700;font-size:15px;color:#475569;">Aucun document</div>
        <div style="font-size:12px;margin-top:6px;">Aucun rapport de ce type n'a encore été généré.</div>
        <a href="{{ route('affectations.liste') }}" class="btn btn-sm" style="margin-top:14px;background:linear-gradient(135deg,{{ $couleurPrincipale }},{{ $couleurSecondaire }});color:white;font-weight:700;">
            📋 Aller à la liste
        </a>
    </div>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════════ --}}
{{-- 🗑️ MODALE SUPPRESSION DOCUMENT                          --}}
{{-- ═══════════════════════════════════════════════════════ --}}
<div id="delDocOverlay" onclick="fermerModalSuppression()"></div>
<div id="delDocModal">
    <div class="del-doc-header">
        <div class="icon">🗑️</div>
        <div style="flex:1;min-width:0;">
            <div class="title">Supprimer ce document</div>
            <div class="sub" id="delDocNomFichier">—</div>
        </div>
    </div>

    <div class="del-doc-body">
        <div class="del-doc-warning">
            ⚠️ <strong>Attention :</strong> cette action est <strong>irréversible</strong>.
            Le fichier PDF sera définitivement supprimé du serveur et de la base de données.
        </div>

        <div id="delDocError" class="del-doc-error"></div>

        <div class="del-doc-field">
            <label>🔐 Mot de passe administrateur <span style="color:#dc2626;">*</span></label>

            <div class="custom-pwd-wrapper">
                {{-- Le vrai input caché --}}
                <input type="text"
                       id="delDocPassword"
                       class="custom-pwd-real"
                       autocomplete="off"
                       data-lpignore="true"
                       data-form-type="other"
                       aria-hidden="true"
                       tabindex="-1">

                {{-- Le faux input visible avec bouton œil --}}
                <div class="custom-pwd-display" id="delDocPwdDisplay" onclick="focusCustomPwd()">
                    <span class="placeholder">Saisissez le mot de passe pour confirmer</span>

                    <button type="button"
                            class="btn-toggle-pwd"
                            id="btnTogglePwd"
                            title="Afficher le mot de passe">
                        👁
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="del-doc-actions">
        <button class="btn-modal-cancel" onclick="fermerModalSuppression()">
            Annuler
        </button>
        <button class="btn-modal-delete" id="delDocConfirmBtn"
                onclick="confirmerSuppressionDocument()">
            🗑️ Supprimer définitivement
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

let currentDocId = null;

// ════════════════════════════════════════════════════════════════
// 🔐 CHAMP MOT DE PASSE PERSONNALISÉ (anti-autofill + œil)
// ════════════════════════════════════════════════════════════════
const realPwdInput = document.getElementById('delDocPassword');
const pwdDisplay   = document.getElementById('delDocPwdDisplay');

let passwordVisible = false;

// Focus programmatique sur le vrai input
function focusCustomPwd() {
    if (realPwdInput) realPwdInput.focus();
}

// Bascule visibilité mot de passe
function togglePasswordVisibility() {
    passwordVisible = !passwordVisible;
    updatePwdDisplay();
    if (realPwdInput) realPwdInput.focus();
}

// Échappe le HTML pour éviter les injections XSS
function escapeHtml(str) {
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

// Retourne le HTML du bouton œil
function getToggleBtnHTML() {
    const icon  = passwordVisible ? '🙈' : '👁';
    const title = passwordVisible ? 'Masquer le mot de passe' : 'Afficher le mot de passe';
    return `<button type="button" class="btn-toggle-pwd" title="${title}">${icon}</button>`;
}

// Synchronise l'affichage avec la valeur du vrai input
function updatePwdDisplay() {
    if (!realPwdInput || !pwdDisplay) return;

    const val     = realPwdInput.value;
    const btnHTML = getToggleBtnHTML();

    if (!val) {
        pwdDisplay.innerHTML = `
            <span class="placeholder">Saisissez le mot de passe pour confirmer</span>
            ${btnHTML}
        `;
    } else if (passwordVisible) {
        pwdDisplay.innerHTML = `
            <span class="plain-text">${escapeHtml(val)}</span>
            <span class="caret"></span>
            ${btnHTML}
        `;
    } else {
        const dots = '•'.repeat(val.length);
        pwdDisplay.innerHTML = `
            <span class="dots">${dots}</span>
            <span class="caret"></span>
            ${btnHTML}
        `;
    }

    // Réattache l'événement au bouton (car innerHTML l'a recréé)
    const newBtn = pwdDisplay.querySelector('.btn-toggle-pwd');
    if (newBtn) {
        newBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            togglePasswordVisibility();
        });
    }
}

// Attache les événements au vrai input
if (realPwdInput && pwdDisplay) {

    realPwdInput.addEventListener('input', updatePwdDisplay);

    realPwdInput.addEventListener('focus', () => {
        pwdDisplay.classList.add('focused');
        updatePwdDisplay();
    });

    realPwdInput.addEventListener('blur', () => {
        pwdDisplay.classList.remove('focused');
        updatePwdDisplay();
    });

    realPwdInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
            e.preventDefault();
            confirmerSuppressionDocument();
        }
    });

    // Attache le premier click sur le bouton œil
    const initBtn = pwdDisplay.querySelector('.btn-toggle-pwd');
    if (initBtn) {
        initBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            togglePasswordVisibility();
        });
    }
}

// ════════════════════════════════════════════════════════════════
// 🗑️ OUVRIR LA MODALE DE SUPPRESSION
// ════════════════════════════════════════════════════════════════
function demanderSuppressionDocument(docId, nomFichier) {
    currentDocId = docId;

    document.getElementById('delDocNomFichier').textContent = nomFichier;

    // ✅ Reset du champ
    realPwdInput.value = '';
    realPwdInput.classList.remove('error');
    pwdDisplay.classList.remove('error');
    pwdDisplay.classList.remove('focused');
    passwordVisible = false;

    updatePwdDisplay();  // Remet le placeholder + bouton 👁

    document.getElementById('delDocError').classList.remove('visible');
    document.getElementById('delDocError').textContent = '';

    const btn = document.getElementById('delDocConfirmBtn');
    btn.disabled = false;
    btn.innerHTML = '🗑️ Supprimer définitivement';

    document.getElementById('delDocOverlay').classList.add('visible');
    document.getElementById('delDocModal').classList.add('visible');

    setTimeout(() => {
        if (realPwdInput) realPwdInput.focus();
    }, 150);
}

// ════════════════════════════════════════════════════════════════
// ✖ FERMER LA MODALE
// ════════════════════════════════════════════════════════════════
function fermerModalSuppression() {
    document.getElementById('delDocOverlay').classList.remove('visible');
    document.getElementById('delDocModal').classList.remove('visible');
    currentDocId = null;
}

// ════════════════════════════════════════════════════════════════
// ✅ CONFIRMER LA SUPPRESSION
// ════════════════════════════════════════════════════════════════
function confirmerSuppressionDocument() {
    if (!currentDocId) return;

    const pwd   = realPwdInput.value.trim();
    const errEl = document.getElementById('delDocError');
    const btn   = document.getElementById('delDocConfirmBtn');

    errEl.classList.remove('visible');
    realPwdInput.classList.remove('error');
    pwdDisplay.classList.remove('error');

    if (!pwd) {
        errEl.textContent = '⚠️ Veuillez saisir le mot de passe.';
        errEl.classList.add('visible');
        realPwdInput.classList.add('error');
        pwdDisplay.classList.add('error');
        realPwdInput.focus();
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '⏳ Suppression...';

    fetch(`/admin/affectations/documents/${currentDocId}`, {
        method: 'DELETE',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ password: pwd }),
    })
    .then(r => r.json().then(data => ({ status: r.status, data })))
    .then(({ status, data }) => {
        btn.disabled = false;
        btn.innerHTML = '🗑️ Supprimer définitivement';

        if (data.success) {
            showToast('✅ ' + data.message, 'success');

            // Retirer la ligne du tableau
            const row = document.querySelector(`tr[data-doc-id="${currentDocId}"]`);
            if (row) {
                row.style.transition = 'opacity 0.3s, transform 0.3s';
                row.style.opacity = '0';
                row.style.transform = 'translateX(-20px)';
                setTimeout(() => row.remove(), 350);
            }

            fermerModalSuppression();

            setTimeout(() => location.reload(), 1200);
        } else {
            errEl.textContent = data.message || '❌ Erreur lors de la suppression.';
            errEl.classList.add('visible');
            realPwdInput.classList.add('error');
            pwdDisplay.classList.add('error');
            realPwdInput.focus();
            realPwdInput.select();
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '🗑️ Supprimer définitivement';
        errEl.textContent = '❌ Erreur réseau. Réessayez.';
        errEl.classList.add('visible');
        console.error(err);
    });
}

// ════════════════════════════════════════════════════════════════
// 🔔 TOAST
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
    }, 3500);
}

// Échap ferme la modale
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') fermerModalSuppression();
});
</script>
@endsection