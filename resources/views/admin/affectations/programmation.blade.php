{{-- APRÈS --}}
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
.prog-table thead { background:linear-gradient(135deg,#1e3a5f,#1d4ed8); color:white; }
.prog-table thead th {
    padding:10px 8px; text-align:left; font-weight:700;
    font-size:10px; text-transform:uppercase; letter-spacing:0.3px;
    white-space:nowrap;
}
.prog-table tbody tr { border-bottom:1px solid #f1f5f9; transition:0.15s; }
.prog-table tbody tr:hover { background:#f8fafc; }
.prog-table tbody td { padding:10px 8px; vertical-align:middle; }

.prog-table tbody tr.presence-present { background:#f0fdf4; border-left:4px solid #16a34a; }
.prog-table tbody tr.presence-retard  { background:#fffbeb; border-left:4px solid #f59e0b; }
.prog-table tbody tr.presence-absent  { background:#fef2f2; border-left:4px solid #dc2626; }
.prog-table tbody tr.presence-attente { background:#f8fafc; border-left:4px solid #64748b; }

.prog-table tbody tr.selected { background:#dbeafe !important; box-shadow:inset 0 0 0 2px #1d4ed8; }

.prog-num {
    display:inline-flex; align-items:center; justify-content:center;
    width:26px; height:26px; border-radius:50%;
    background:#1d4ed8; color:white;
    font-size:11px; font-weight:800;
}

.prog-nom { font-weight:700; color:#1e3a5f; font-size:12px; line-height:1.3; }

.prog-cell-bold { font-weight:700; color:#1e3a5f; }
.prog-cell-muted { color:#64748b; font-size:11px; }

.frais-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:4px 10px; border-radius:12px;
    font-size:10px; font-weight:800;
    user-select:none;
}
.frais-badge.paye { background:#dcfce7; color:#166534; border:2px solid #86efac; }
.frais-badge.impaye { background:#fee2e2; color:#991b1b; border:2px solid #fca5a5; }

.heure-input {
    border:1px solid #e2e8f0; border-radius:6px;
    padding:4px 6px; font-size:11px; font-weight:700;
    width:80px; transition:all 0.2s;
}
.heure-input:hover, .heure-input:focus {
    border-color:#1d4ed8; outline:none;
    box-shadow:0 0 0 3px rgba(29,78,216,0.1);
}

.geo-select {
    border:1px solid #e2e8f0; border-radius:6px;
    padding:4px 6px; font-size:11px;
    min-width:130px; transition:all 0.2s;
    background:white;
}
.geo-select:hover, .geo-select:focus {
    border-color:#1d4ed8; outline:none;
    box-shadow:0 0 0 3px rgba(29,78,216,0.1);
}

/* Boutons d'action */
.prog-action-btn {
    width:30px; height:30px; border-radius:6px; border:none;
    cursor:pointer; display:inline-flex; align-items:center;
    justify-content:center; font-size:13px; transition:all 0.2s;
    position:relative;
}
.prog-action-btn:hover { transform:scale(1.12); }
.prog-action-btn.accept  { background:#dcfce7; color:#16a34a; }
.prog-action-btn.accept:hover  { background:#16a34a; color:white; }
.prog-action-btn.refuse  { background:#fee2e2; color:#dc2626; }
.prog-action-btn.refuse:hover  { background:#dc2626; color:white; }
.prog-action-btn.retard  { background:#fef3c7; color:#92400e; }
.prog-action-btn.retard:hover  { background:#f59e0b; color:white; }
.prog-action-btn.absent  { background:#fecaca; color:#991b1b; }
.prog-action-btn.absent:hover  { background:#dc2626; color:white; }

/* Badge "à programmer" */
.badge-a-programmer {
    display:inline-flex;align-items:center;gap:4px;
    background:#fef3c7;color:#92400e;border:1px dashed #f59e0b;
    padding:5px 10px;border-radius:8px;font-size:10px;font-weight:800;
    white-space:nowrap;
}

/* Tooltip */
.prog-action-btn::after {
    content: attr(title);
    position: absolute;
    bottom: -22px;
    left: 50%;
    transform: translateX(-50%);
    background: #1f2937;
    color: white;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 9px;
    font-weight: 600;
    white-space: nowrap;
    opacity: 0;
    pointer-events: none;
    transition: opacity 0.2s;
    z-index: 10;
}
.prog-action-btn:hover::after { opacity: 1; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 40px rgba(0,0,0,0.25); z-index:9999; width:520px; max-width:95%; max-height:90vh; overflow-y:auto; }

.toast-notification { position:fixed; bottom:20px; right:20px; background:#1f2937; color:#fff; padding:12px 20px; border-radius:8px; font-size:14px; box-shadow:0 4px 12px rgba(0,0,0,0.3); z-index:99999; max-width:400px; animation:slideInToast 0.3s ease; }
.toast-notification.success { background:#16a34a; }
.toast-notification.error   { background:#dc2626; }
.toast-notification.warning { background:#f59e0b; }
@keyframes slideInToast { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }

.empty-state { text-align:center; padding:60px 20px; color:#94a3b8; }
.empty-state .ico { font-size:56px; margin-bottom:14px; }

/* 🎯 BARRE DE SÉLECTION MULTIPLE */
.selection-bar {
    display:none;
    background:linear-gradient(135deg,#eff6ff,#dbeafe);
    border:2px solid #1d4ed8;
    border-radius:14px;
    padding:16px 20px;
    margin-bottom:16px;
    box-shadow:0 4px 14px rgba(29,78,216,0.15);
    position:sticky; top:10px; z-index:100;
}
.selection-bar.visible { display:block; }
.selection-bar-header {
    display:flex;justify-content:space-between;align-items:center;
    flex-wrap:wrap;gap:14px;
}
.selection-bar-title {
    font-weight:800;color:#1d4ed8;font-size:14px;
    display:flex;align-items:center;gap:8px;
}
.selection-bar-count {
    background:#1d4ed8;color:white;padding:2px 10px;
    border-radius:20px;font-size:12px;font-weight:800;
}
.selection-bar-actions { display:flex;gap:8px;flex-wrap:wrap; }
.btn-action-multiple {
    border:none;border-radius:8px;padding:10px 18px;
    font-size:13px;font-weight:800;cursor:pointer;
    display:inline-flex;align-items:center;gap:6px;
    transition:all 0.2s;
}
.btn-action-multiple.accept { background:linear-gradient(135deg,#16a34a,#15803d);color:white; }
.btn-action-multiple.accept:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 4px 12px rgba(22,163,74,0.4); }
.btn-action-multiple.refuse { background:linear-gradient(135deg,#dc2626,#991b1b);color:white; }
.btn-action-multiple.refuse:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 4px 12px rgba(220,38,38,0.4); }
.btn-action-multiple.edit { background:linear-gradient(135deg,#f59e0b,#d97706);color:white; }
.btn-action-multiple.edit:hover:not(:disabled) { transform:translateY(-2px); box-shadow:0 4px 12px rgba(245,158,11,0.4); }
.btn-action-multiple:disabled { background:#cbd5e1; cursor:not-allowed; opacity:0.7; }

.btn-clear-selection {
    background:white;border:2px solid #e2e8f0;border-radius:8px;
    padding:8px 14px;font-size:12px;font-weight:700;color:#64748b;
    cursor:pointer;transition:all 0.2s;
}
.btn-clear-selection:hover { border-color:#dc2626; color:#dc2626; }

.checkbox-ligne {
    width:18px; height:18px; cursor:pointer;
    accent-color:#1d4ed8;
}
.checkbox-ligne:disabled {
    cursor:not-allowed; opacity:0.4;
}
</style>

{{-- EN-TÊTE --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">📅 Programmation Implantation</h2>
        <div style="font-size:13px;color:#64748b;">
            <strong style="color:#1e3a5f;font-size:15px;">{{ $lignes->count() }}</strong> ligne(s) —
            <strong style="color:#7c3aed;">{{ collect($lignes)->sum(fn($l) => count($l['affectation_ids'])) }}</strong> affectation(s)
            en attente
            @if(!empty($filtrerParSemaine) && $filtrerParSemaine)
                <span style="background:#fef3c7;color:#92400e;padding:2px 8px;
                             border-radius:8px;font-size:10px;font-weight:800;margin-left:6px;">
                    🔒 SEMAINE PRIORITAIRE
                </span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button onclick="exporterPdf()" class="btn btn-danger btn-sm">
            📄 Exporter PDF
        </button>
        <a href="{{ route('affectations.liste') }}" class="btn btn-outline-secondary btn-sm">
            📋 Liste des affectations
        </a>
        <button onclick="window.location.reload()" class="btn btn-outline-primary btn-sm">
            🔄 Actualiser
        </button>
    </div>
</div>

{{-- INFO : mode d'affichage --}}
@if(!empty($filtrerParSemaine) && $filtrerParSemaine && $debutSemaine && $finSemaine)
    {{-- 🔒 MODE BLOQUÉ : semaine antérieure prioritaire --}}
    <div style="background:linear-gradient(135deg,#fef3c7,#fef9c3);border:2px solid #fcd34d;
                border-radius:14px;padding:16px 20px;margin-bottom:20px;
                color:#92400e;font-weight:600;font-size:13px;
                display:flex;align-items:flex-start;gap:12px;">
        <div style="font-size:26px;">🔒</div>
        <div style="flex:1;">
            <div style="font-weight:800;font-size:15px;">
                Semaine prioritaire à traiter
            </div>
            <div style="margin-top:6px;font-size:12.5px;">
                📆 Semaine du
                <strong>{{ $debutSemaine->format('d/m/Y') }}</strong>
                au
                <strong>{{ $finSemaine->format('d/m/Y') }}</strong>
                @if(isset($autresSemaines) && $autresSemaines > 0)
                    — il reste <strong>{{ $autresSemaines }} affectation(s)</strong>
                    @if(isset($semainesEnAttente) && $semainesEnAttente > 0)
                        sur <strong>{{ $semainesEnAttente }} semaine(s)</strong>
                    @endif
                    antérieure(s) à celle-ci.
                @endif
            </div>
            <div style="margin-top:8px;font-size:11.5px;font-style:italic;opacity:0.9;">
                Vous devez terminer cette semaine avant de pouvoir traiter les suivantes.
            </div>
        </div>
    </div>
@else
    {{-- ✅ MODE LIBRE : toutes les affectations en attente --}}
    <div style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border-left:4px solid #1d4ed8;
                border-radius:12px;padding:14px 18px;margin-bottom:16px;
                font-size:13px;color:#1e40af;
                display:flex;align-items:center;gap:12px;">
        <div style="font-size:24px;">📋</div>
        <div>
            <div style="font-weight:800;font-size:14px;">
                Toutes les affectations en attente
            </div>
            <div style="margin-top:4px;font-size:12px;">
                Aucune semaine antérieure n'est en attente.
                Vous pouvez traiter librement toutes les affectations programmées.
            </div>
        </div>
    </div>
@endif

{{-- 🎯 BARRE DE SÉLECTION MULTIPLE --}}
<div class="selection-bar" id="selectionBar">
    <div class="selection-bar-header">
        <div class="selection-bar-title">
            🎯 Actions groupées
            <span class="selection-bar-count" id="selectionCount">0</span>
        </div>
        <div class="selection-bar-actions">
            <button onclick="ouvrirAcceptMultiple()" class="btn-action-multiple accept" id="btnAcceptMultiple" disabled>
                ✅ Accepter
            </button>
            <button onclick="ouvrirRefusMultiple()" class="btn-action-multiple refuse" id="btnRefusMultiple" disabled>
                ❌ Refuser
            </button>
            <button onclick="ouvrirModifMultiple()" class="btn-action-multiple edit" id="btnModifMultiple" disabled>
                ✏️ Modifier
            </button>
            <button onclick="effacerSelection()" class="btn-clear-selection">✖ Effacer</button>
        </div>
    </div>
</div>

{{-- STATISTIQUES --}}
<div class="prog-stats">
    <div class="prog-stat" style="border-left-color:#1d4ed8;">
        <div class="ico" style="background:#dbeafe;color:#1d4ed8;">📅</div>
        <div><div class="lbl">Total en attente</div><div class="val">{{ $stats['total'] }}</div></div>
    </div>
    <div class="prog-stat" style="border-left-color:#16a34a;">
        <div class="ico" style="background:#dcfce7;color:#16a34a;">✅</div>
        <div><div class="lbl">Présents</div><div class="val">{{ $stats['presents'] }}</div></div>
    </div>
    <div class="prog-stat" style="border-left-color:#f59e0b;">
        <div class="ico" style="background:#fef3c7;color:#f59e0b;">⏰</div>
        <div><div class="lbl">Retards</div><div class="val">{{ $stats['retards'] }}</div></div>
    </div>
    <div class="prog-stat" style="border-left-color:#dc2626;">
        <div class="ico" style="background:#fee2e2;color:#dc2626;">❌</div>
        <div><div class="lbl">Absents</div><div class="val">{{ $stats['absents'] }}</div></div>
    </div>
    <div class="prog-stat" style="border-left-color:#7c3aed;">
        <div class="ico" style="background:#ede9fe;color:#7c3aed;">📐</div>
        <div><div class="lbl">Superficie</div><div class="val">{{ number_format($stats['superficie'], 0, ',', ' ') }} <small style="font-size:11px;color:#64748b;">m²</small></div></div>
    </div>
</div>

{{-- TABLEAU --}}
<div class="prog-table-wrap">
    @if($lignes->count() > 0)
    <div style="overflow-x:auto;">
        <table class="prog-table">
            <thead>
                <tr>
                    <th style="width:60px;">
                        <input type="checkbox" class="checkbox-ligne" id="checkboxSelectAll"
                               onchange="toggleSelectAll(this)">
                    </th>
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
                    <th>💰 Frais</th>
                    <th style="text-align:right;min-width:150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lignes as $index => $ligne)
                @php
                    $presenceClass = 'presence-' . str_replace('en_attente', 'attente', $ligne['statut_presence']);
                    $estProgramme  = !empty($ligne['geometre_id']) && !empty($ligne['heure_implantation']);
                @endphp
                <tr class="{{ $presenceClass }}"
                    data-cle="{{ $ligne['affectation_ids'][0] ?? '' }}"
                    id="row-{{ $ligne['affectation_ids'][0] ?? $index }}">
                    <td style="text-align:center;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="checkbox" class="checkbox-ligne checkbox-ligne-item"
                                   data-cle="{{ $ligne['affectation_ids'][0] ?? '' }}"
                                   data-affectation-ids='@json($ligne["affectation_ids"])'
                                   onchange="toggleSelectionLigne(this)"
                                   @disabled(!$estProgramme)
                                   title="{{ $estProgramme ? 'Sélectionner cette ligne' : 'Ligne non programmable (géomètre + heure requis)' }}">
                            <span class="prog-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="prog-nom">{{ $ligne['beneficiaire'] }}</div>
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

                    {{-- Date d'implantation (lecture seule) --}}
                    <td>
                        @if($ligne['date_implantation'])
                            <span style="font-size:11px;font-weight:700;color:#1e3a5f;background:#eff6ff;padding:3px 8px;border-radius:6px;display:inline-block;">
                                📅 {{ \Carbon\Carbon::parse($ligne['date_implantation'])->format('d/m/Y') }}
                            </span>
                        @else
                            <span style="font-size:11px;color:#94a3b8;">—</span>
                        @endif
                    </td>

                    {{-- Géomètre (éditable) --}}
                    <td>
                        <select class="geo-select"
                                data-affectation-ids='@json($ligne["affectation_ids"])'
                                onchange="majProgrammation(this, 'geometre_id')">
                            <option value="">— Choisir —</option>
                            @foreach($geometres as $geo)
                                <option value="{{ $geo->id }}"
                                    {{ $ligne['geometre_id'] == $geo->id ? 'selected' : '' }}>
                                    {{ $geo->name }}
                                </option>
                            @endforeach
                        </select>
                    </td>

                    {{-- Heure (éditable) --}}
                    <td>
                        <input type="time"
                               class="heure-input"
                               value="{{ $ligne['heure_implantation'] ? substr($ligne['heure_implantation'], 0, 5) : '' }}"
                               data-affectation-ids='@json($ligne["affectation_ids"])'
                               onchange="majProgrammation(this, 'heure_implantation')">
                    </td>

                    {{-- Frais logistique (LECTURE SEULE) --}}
                    <td>
                        <span class="frais-badge {{ $ligne['frais_paye'] ? 'paye' : 'impaye' }}"
                              title="Frais en lecture seule">
                            @if($ligne['frais_paye'])
                                ✅ Payé
                            @else
                                ❌ Non payé
                            @endif
                        </span>
                    </td>

                    {{-- Actions --}}
                    <td>
                        <div style="display:flex;gap:4px;justify-content:flex-end;flex-wrap:wrap;align-items:center;">

                            @if($estProgramme)
                                {{-- ✅ Programmé : géomètre + heure définis → accepter / refuser disponibles --}}
                                <button class="prog-action-btn accept"
                                        onclick='ouvrirAccept(@json($ligne["affectation_ids"]), @json($ligne["beneficiaire"]))'
                                        title="Accepter">✅</button>
                                <button class="prog-action-btn refuse"
                                        onclick='ouvrirRefus(@json($ligne["affectation_ids"]), @json($ligne["beneficiaire"]))'
                                        title="Refuser">❌</button>
                            @else
                                {{-- ⚠️ Non programmé : il manque géomètre et/ou heure --}}
                                <span class="badge-a-programmer"
                                      title="Renseignez le géomètre et l'heure pour pouvoir accepter ou refuser">
                                    ⚠️ À programmer
                                </span>
                            @endif

                            {{-- Boutons présence toujours disponibles --}}
                            <button class="prog-action-btn retard"
                                    onclick='marquerPresence(@json($ligne["affectation_ids"]), "retard", {{ $ligne["affectation_ids"][0] ?? 0 }})'
                                    title="Marquer en retard">⏰</button>
                            <button class="prog-action-btn absent"
                                    onclick='marquerPresence(@json($ligne["affectation_ids"]), "absent", {{ $ligne["affectation_ids"][0] ?? 0 }})'
                                    title="Marquer absent">🚫</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="ico">✅</div>
        <div style="font-weight:700;font-size:15px;color:#475569;">Aucune affectation en attente</div>
        <div style="font-size:12px;margin-top:6px;">Toutes les programmations ont été traitées. Bravo !</div>
        <a href="{{ route('affectations.liste') }}" class="btn btn-primary btn-sm" style="margin-top:14px;">📋 Voir toutes les affectations</a>
    </div>
    @endif
</div>

{{-- MODAL ACCEPTATION (individuelle) --}}
<div class="modal-overlay" id="acceptOverlay" onclick="fermerAccept()"></div>
<div class="modal-box" id="acceptModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#16a34a;font-weight:800;margin:0;">✅ Accepter l'affectation</h5>
        <button onclick="fermerAccept()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div id="acceptInfo" style="background:#f0fdf4;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#166534;"></div>

    <label style="font-size:12px;font-weight:700;color:#64748b;">
        📅 Date d'acceptation <span style="color:#16a34a;">*</span>
    </label>
    <input type="date" id="acceptDate" class="form-control form-control-sm" style="margin-top:6px;"
           value="{{ now()->format('Y-m-d') }}">

    <div style="font-size:11px;color:#64748b;margin-top:8px;background:#f0fdf4;padding:8px 12px;border-radius:6px;">
        ℹ️ L'étape du bénéficiaire sera automatiquement mise à <strong>« Déjà implanté »</strong> avec cette date.
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerAccept()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="validerAccept()" class="btn btn-success btn-sm" style="font-weight:700;">
            ✅ Confirmer
        </button>
    </div>
</div>

{{-- MODAL REFUS (individuel) --}}
<div class="modal-overlay" id="refusOverlay" onclick="fermerRefus()"></div>
<div class="modal-box" id="refusModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#dc2626;font-weight:800;margin:0;">❌ Refuser l'affectation</h5>
        <button onclick="fermerRefus()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div id="refusInfo" style="background:#fef2f2;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#991b1b;"></div>

    <div style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:700;color:#64748b;">
            📅 Date du refus <span style="color:#dc2626;">*</span>
        </label>
        <input type="date" id="refusDate" class="form-control form-control-sm" style="margin-top:6px;"
               value="{{ now()->format('Y-m-d') }}">
    </div>

    <div>
        <label style="font-size:12px;font-weight:700;color:#64748b;">
            📝 Motif du refus <span style="color:#dc2626;">*</span>
        </label>
        <textarea id="refusMotif" class="form-control" rows="4"
                  placeholder="Expliquez pourquoi cette affectation est refusée (min. 5 caractères)..."
                  maxlength="1000" style="margin-top:6px;font-size:13px;"></textarea>
        <div style="font-size:11px;color:#94a3b8;margin-top:6px;">
            <span id="refusCompteur">0</span> / 1000 caractères
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerRefus()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="validerRefus()" class="btn btn-danger btn-sm" style="font-weight:700;">
            ❌ Confirmer
        </button>
    </div>
</div>

{{-- MODAL ACCEPTATION MULTIPLE --}}
<div class="modal-overlay" id="acceptMultiOverlay" onclick="fermerAcceptMultiple()"></div>
<div class="modal-box" id="acceptMultiModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#16a34a;font-weight:800;margin:0;">✅ Accepter les lignes sélectionnées</h5>
        <button onclick="fermerAcceptMultiple()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div id="acceptMultiInfo" style="background:#f0fdf4;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#166534;"></div>

    <label style="font-size:12px;font-weight:700;color:#64748b;">
        📅 Date d'acceptation <span style="color:#16a34a;">*</span>
    </label>
    <input type="date" id="acceptMultiDate" class="form-control form-control-sm" style="margin-top:6px;"
           value="{{ now()->format('Y-m-d') }}">

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerAcceptMultiple()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="validerAcceptMultiple()" class="btn btn-success btn-sm" style="font-weight:700;">
            ✅ Confirmer l'acceptation
        </button>
    </div>
</div>

{{-- MODAL REFUS MULTIPLE --}}
<div class="modal-overlay" id="refusMultiOverlay" onclick="fermerRefusMultiple()"></div>
<div class="modal-box" id="refusMultiModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#dc2626;font-weight:800;margin:0;">❌ Refuser les lignes sélectionnées</h5>
        <button onclick="fermerRefusMultiple()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div id="refusMultiInfo" style="background:#fef2f2;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#991b1b;"></div>

    <div style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:700;color:#64748b;">
            📅 Date du refus <span style="color:#dc2626;">*</span>
        </label>
        <input type="date" id="refusMultiDate" class="form-control form-control-sm" style="margin-top:6px;"
               value="{{ now()->format('Y-m-d') }}">
    </div>

    <div>
        <label style="font-size:12px;font-weight:700;color:#64748b;">
            📝 Motif du refus <span style="color:#dc2626;">*</span>
        </label>
        <textarea id="refusMultiMotif" class="form-control" rows="4"
                  placeholder="Expliquez pourquoi ces affectations sont refusées (min. 5 caractères)..."
                  maxlength="1000" style="margin-top:6px;font-size:13px;"></textarea>
        <div style="font-size:11px;color:#94a3b8;margin-top:6px;">
            <span id="refusMultiCompteur">0</span> / 1000 caractères
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerRefusMultiple()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="validerRefusMultiple()" class="btn btn-danger btn-sm" style="font-weight:700;">
            ❌ Confirmer le refus
        </button>
    </div>
</div>

{{-- MODAL MODIFICATION MULTIPLE --}}
<div class="modal-overlay" id="modifMultiOverlay" onclick="fermerModifMultiple()"></div>
<div class="modal-box" id="modifMultiModal" style="width:640px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#f59e0b;font-weight:800;margin:0;">✏️ Modifier les lignes sélectionnées</h5>
        <button onclick="fermerModifMultiple()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div id="modifMultiInfo" style="background:#fef3c7;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#92400e;"></div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Date d'implantation</label>
            <input type="date" id="modifMultiDate" class="form-control form-control-sm">
            <div style="font-size:9px;color:#94a3b8;margin-top:3px;">Laisser vide = ne pas changer</div>
        </div>
        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">⏰ Heure d'implantation</label>
            <input type="time" id="modifMultiHeure" class="form-control form-control-sm">
            <div style="font-size:9px;color:#94a3b8;margin-top:3px;">Laisser vide = ne pas changer</div>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px;">
        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">👷 Géomètre</label>
            <select id="modifMultiGeometre" class="form-control form-control-sm">
                <option value="">— Ne pas changer —</option>
                @foreach($geometres as $geo)
                    <option value="{{ $geo->id }}">{{ $geo->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">🎯 Statut présence</label>
            <select id="modifMultiPresence" class="form-control form-control-sm">
                <option value="">— Ne pas changer —</option>
                <option value="en_attente">⏳ En attente</option>
                <option value="present">✅ Présent</option>
                <option value="retard">⏰ Retard</option>
                <option value="absent">❌ Absent</option>
            </select>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerModifMultiple()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="validerModifMultiple()" class="btn btn-warning btn-sm" style="font-weight:700;">
            💾 Appliquer
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

let acceptIds = [];
let refusIds = [];
let lignesSelectionnees = new Map();

// ════════════════════════════════════════════════════════════════
// ⏰ / ❌ MARQUER PRÉSENCE (retard / absent) — action individuelle
// ════════════════════════════════════════════════════════════════
function marquerPresence(affectationIds, statut, rowId) {
    const labels = {
        'retard': '⏰ Retard',
        'absent': '❌ Absent',
        'present': '✅ Présent',
        'en_attente': '⏳ En attente',
    };

    const label = labels[statut] || statut;

    if (!confirm(`Marquer cette ligne comme "${label}" ?`)) return;

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('/admin/affectations/groupe/programmation', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            affectation_ids: affectationIds,
            statut_presence: statut,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('✅ ' + (data.message || 'Mis à jour'), 'success');

            const row = document.getElementById('row-' + rowId);
            if (row) {
                const oldClasses = row.className.split(' ').filter(c => !c.startsWith('presence-'));
                row.className = oldClasses.join(' ') + ' presence-' + (statut === 'en_attente' ? 'attente' : statut);
            }

            setTimeout(() => location.reload(), 900);
        } else {
            showToast('❌ ' + (data.message || 'Erreur'), 'error');
        }
    })
    .catch(err => {
        if (window.EdenLoader) window.EdenLoader.hide();
        console.error(err);
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// 🎯 SÉLECTION MULTIPLE
// ════════════════════════════════════════════════════════════════
function toggleSelectionLigne(checkbox) {
    if (checkbox.disabled) return;

    const cle = checkbox.dataset.cle;
    let ids = [];
    try {
        ids = JSON.parse(checkbox.dataset.affectationIds || '[]');
    } catch (e) {
        console.error('IDs invalides :', e);
        return;
    }

    const row = checkbox.closest('tr');

    if (checkbox.checked) {
        lignesSelectionnees.set(cle, ids);
        row.classList.add('selected');
    } else {
        lignesSelectionnees.delete(cle);
        row.classList.remove('selected');
    }

    mettreAJourBarreSelection();
}

function toggleSelectAll(masterCheckbox) {
    document.querySelectorAll('.checkbox-ligne-item:not(:disabled)').forEach(cb => {
        cb.checked = masterCheckbox.checked;
        toggleSelectionLigne(cb);
    });
}

function mettreAJourBarreSelection() {
    const bar    = document.getElementById('selectionBar');
    const count  = document.getElementById('selectionCount');
    const btnAcc = document.getElementById('btnAcceptMultiple');
    const btnRef = document.getElementById('btnRefusMultiple');
    const btnMod = document.getElementById('btnModifMultiple');

    const total = lignesSelectionnees.size;
    count.textContent = total;

    if (total > 0) {
        bar.classList.add('visible');
        btnAcc.disabled = false;
        btnRef.disabled = false;
        btnMod.disabled = false;
    } else {
        bar.classList.remove('visible');
        btnAcc.disabled = true;
        btnRef.disabled = true;
        btnMod.disabled = true;
    }
}

function effacerSelection() {
    document.querySelectorAll('.checkbox-ligne-item').forEach(cb => {
        cb.checked = false;
        cb.closest('tr').classList.remove('selected');
    });
    const master = document.getElementById('checkboxSelectAll');
    if (master) master.checked = false;
    lignesSelectionnees.clear();
    mettreAJourBarreSelection();
}

function tousLesIdsSelectionnes() {
    const ids = [];
    lignesSelectionnees.forEach(arr => arr.forEach(id => ids.push(id)));
    return ids;
}

// ════════════════════════════════════════════════════════════════
// ✅ ACCEPTER PLUSIEURS LIGNES
// ════════════════════════════════════════════════════════════════
function ouvrirAcceptMultiple() {
    if (lignesSelectionnees.size === 0) {
        showToast('⚠️ Aucune ligne sélectionnée', 'warning');
        return;
    }

    document.getElementById('acceptMultiInfo').innerHTML = `
        <div style="font-weight:700;">⚙️ Action groupée</div>
        <div style="font-size:12px;margin-top:6px;">
            <strong>${lignesSelectionnees.size} ligne(s)</strong>
            — soit <strong>${tousLesIdsSelectionnes().length} affectation(s)</strong>
            seront acceptées.
        </div>
    `;
    document.getElementById('acceptMultiDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('acceptMultiOverlay').style.display = 'block';
    document.getElementById('acceptMultiModal').style.display   = 'block';
}

function fermerAcceptMultiple() {
    document.getElementById('acceptMultiOverlay').style.display = 'none';
    document.getElementById('acceptMultiModal').style.display   = 'none';
}

function validerAcceptMultiple() {
    const date = document.getElementById('acceptMultiDate').value;
    if (!date) { showToast('⚠️ La date est obligatoire', 'warning'); return; }

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('/admin/affectations/groupe/programmation-accepter-multiple', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({
            affectation_ids: tousLesIdsSelectionnes(),
            date_acceptation: date,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('✅ ' + data.message, 'success');
            fermerAcceptMultiple();
            setTimeout(() => location.reload(), 1000);
        } else showToast('❌ ' + (data.message || 'Erreur'), 'error');
    })
    .catch(() => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// ❌ REFUSER PLUSIEURS LIGNES
// ════════════════════════════════════════════════════════════════
function ouvrirRefusMultiple() {
    if (lignesSelectionnees.size === 0) {
        showToast('⚠️ Aucune ligne sélectionnée', 'warning');
        return;
    }

    document.getElementById('refusMultiInfo').innerHTML = `
        <div style="font-weight:700;">⚙️ Action groupée</div>
        <div style="font-size:12px;margin-top:6px;">
            <strong>${lignesSelectionnees.size} ligne(s)</strong>
            — soit <strong>${tousLesIdsSelectionnes().length} affectation(s)</strong>
            seront refusées avec le même motif.
        </div>
    `;
    document.getElementById('refusMultiDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('refusMultiMotif').value = '';
    document.getElementById('refusMultiCompteur').textContent = '0';
    document.getElementById('refusMultiOverlay').style.display = 'block';
    document.getElementById('refusMultiModal').style.display   = 'block';
    setTimeout(() => document.getElementById('refusMultiMotif').focus(), 100);
}

function fermerRefusMultiple() {
    document.getElementById('refusMultiOverlay').style.display = 'none';
    document.getElementById('refusMultiModal').style.display   = 'none';
}

function validerRefusMultiple() {
    const date  = document.getElementById('refusMultiDate').value;
    const motif = document.getElementById('refusMultiMotif').value.trim();

    if (!date) { showToast('⚠️ La date est obligatoire', 'warning'); return; }
    if (!motif || motif.length < 5) { showToast('⚠️ Le motif est obligatoire', 'warning'); return; }

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('/admin/affectations/groupe/programmation-refuser-multiple', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({
            affectation_ids: tousLesIdsSelectionnes(),
            date_refus: date,
            motif_refus: motif,
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('❌ ' + data.message, 'success');
            fermerRefusMultiple();
            setTimeout(() => location.reload(), 1000);
        } else showToast('❌ ' + (data.message || 'Erreur'), 'error');
    })
    .catch(() => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// ✏️ MODIFIER PLUSIEURS LIGNES
// ════════════════════════════════════════════════════════════════
function ouvrirModifMultiple() {
    if (lignesSelectionnees.size === 0) {
        showToast('⚠️ Aucune ligne sélectionnée', 'warning');
        return;
    }

    document.getElementById('modifMultiInfo').innerHTML = `
        <div style="font-weight:700;">⚙️ Action groupée</div>
        <div style="font-size:12px;margin-top:6px;">
            <strong>${lignesSelectionnees.size} ligne(s)</strong>
            — soit <strong>${tousLesIdsSelectionnes().length} affectation(s)</strong>
            <br>Remplissez uniquement les champs à modifier.
        </div>
    `;

    document.getElementById('modifMultiDate').value    = '';
    document.getElementById('modifMultiHeure').value   = '';
    document.getElementById('modifMultiGeometre').value = '';
    document.getElementById('modifMultiPresence').value = '';

    document.getElementById('modifMultiOverlay').style.display = 'block';
    document.getElementById('modifMultiModal').style.display   = 'block';
}

function fermerModifMultiple() {
    document.getElementById('modifMultiOverlay').style.display = 'none';
    document.getElementById('modifMultiModal').style.display   = 'none';
}

function validerModifMultiple() {
    const date     = document.getElementById('modifMultiDate').value;
    const heure    = document.getElementById('modifMultiHeure').value;
    const geo      = document.getElementById('modifMultiGeometre').value;
    const presence = document.getElementById('modifMultiPresence').value;

    if (!date && !heure && !geo && !presence) {
        showToast('⚠️ Remplissez au moins un champ', 'warning');
        return;
    }

    const lignes = [];
    lignesSelectionnees.forEach((ids, cle) => {
        lignes.push({ cle: cle, ids: ids });
    });

    const payload = {
        lignes: lignes,
        date_implantation: date || null,
        heure_implantation: heure || null,
        geometre_id: geo || null,
        statut_presence: presence || null,
    };

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('/admin/affectations/groupe/programmation-multiple', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('✅ ' + data.message, 'success');
            fermerModifMultiple();
            setTimeout(() => location.reload(), 1000);
        } else showToast('❌ ' + (data.message || 'Erreur'), 'error');
    })
    .catch(() => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// MISE À JOUR INDIVIDUELLE (autosave)
// ════════════════════════════════════════════════════════════════
function majProgrammation(el, champ) {
    let affectationIds;
    try {
        affectationIds = JSON.parse(el.dataset.affectationIds || '[]');
    } catch (e) {
        console.error('IDs invalides :', e);
        return;
    }
    if (!affectationIds || affectationIds.length === 0) return;

    const payload = { affectation_ids: affectationIds };

    if (champ === 'geometre_id') {
        payload.geometre_id = el.value || null;
    } else if (champ === 'heure_implantation') {
        payload.heure_implantation = el.value || null;
    }

    el.disabled = true;
    el.style.opacity = '0.5';

    fetch('/admin/affectations/groupe/programmation', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        el.disabled = false;
        el.style.opacity = '1';
        if (data.success) {
            showToast('✅ ' + (data.message || 'Enregistré'), 'success');
            // 🔄 Recharger pour mettre à jour l'affichage des boutons (À programmer ↔ Accept/Refuser)
            setTimeout(() => location.reload(), 500);
        } else {
            showToast('❌ ' + (data.message || 'Erreur'), 'error');
        }
    })
    .catch(err => {
        el.disabled = false;
        el.style.opacity = '1';
        console.error(err);
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// ACCEPTER INDIVIDUEL
// ════════════════════════════════════════════════════════════════
function ouvrirAccept(ids, nom) {
    acceptIds = ids;
    document.getElementById('acceptInfo').innerHTML = `
        <div style="font-weight:700;">👤 ${nom}</div>
        <div style="font-size:11px;margin-top:4px;">${ids.length} affectation(s) — seront acceptées</div>
    `;
    document.getElementById('acceptDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('acceptOverlay').style.display = 'block';
    document.getElementById('acceptModal').style.display   = 'block';
}

function fermerAccept() {
    document.getElementById('acceptOverlay').style.display = 'none';
    document.getElementById('acceptModal').style.display   = 'none';
    acceptIds = [];
}

function validerAccept() {
    const date = document.getElementById('acceptDate').value;
    if (!date) { showToast('⚠️ La date est obligatoire', 'warning'); return; }

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('/admin/affectations/groupe/accepter', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ affectation_ids: acceptIds, date_acceptation: date }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('✅ ' + data.message, 'success');
            fermerAccept();
            setTimeout(() => location.reload(), 1000);
        } else showToast('❌ ' + (data.message || 'Erreur'), 'error');
    })
    .catch(() => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// REFUSER INDIVIDUEL
// ════════════════════════════════════════════════════════════════
function ouvrirRefus(ids, nom) {
    refusIds = ids;
    document.getElementById('refusInfo').innerHTML = `
        <div style="font-weight:700;">👤 ${nom}</div>
        <div style="font-size:11px;margin-top:4px;">${ids.length} affectation(s) — motif et date obligatoires</div>
    `;
    document.getElementById('refusDate').value = new Date().toISOString().split('T')[0];
    document.getElementById('refusMotif').value = '';
    document.getElementById('refusCompteur').textContent = '0';
    document.getElementById('refusOverlay').style.display = 'block';
    document.getElementById('refusModal').style.display   = 'block';
    setTimeout(() => document.getElementById('refusMotif').focus(), 100);
}

function fermerRefus() {
    document.getElementById('refusOverlay').style.display = 'none';
    document.getElementById('refusModal').style.display   = 'none';
    refusIds = [];
}

function validerRefus() {
    const date  = document.getElementById('refusDate').value;
    const motif = document.getElementById('refusMotif').value.trim();

    if (!date) { showToast('⚠️ La date est obligatoire', 'warning'); return; }
    if (!motif || motif.length < 5) { showToast('⚠️ Le motif est obligatoire', 'warning'); return; }

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('/admin/affectations/groupe/refuser', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ affectation_ids: refusIds, date_refus: date, motif_refus: motif }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('❌ ' + data.message, 'success');
            fermerRefus();
            setTimeout(() => location.reload(), 1000);
        } else showToast('❌ ' + (data.message || 'Erreur'), 'error');
    })
    .catch(() => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// EXPORT PDF
// ════════════════════════════════════════════════════════════════
function exporterPdf() {
    window.location.href = '{{ route("affectations.programmation.pdf") }}';
}

// ════════════════════════════════════════════════════════════════
// COMPTEURS DE CARACTÈRES (motifs)
// ════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    const txt1 = document.getElementById('refusMotif');
    if (txt1) txt1.addEventListener('input', () => {
        document.getElementById('refusCompteur').textContent = txt1.value.length;
    });

    const txt2 = document.getElementById('refusMultiMotif');
    if (txt2) txt2.addEventListener('input', () => {
        document.getElementById('refusMultiCompteur').textContent = txt2.value.length;
    });
});

// ════════════════════════════════════════════════════════════════
// TOAST
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
</script>
@endsection