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

.prog-table tbody tr.row-accepte { background:#f0fdf4 !important; border-left:4px solid #16a34a !important; }
.prog-table tbody tr.row-refuse { background:#fef2f2 !important; border-left:4px solid #dc2626 !important; }
.prog-table tbody tr.row-refus-attente { background:#fef3c7 !important; border-left:4px solid #f59e0b !important; }
.prog-table tbody tr.row-refus-attente td { opacity: 0.92; }

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

.prog-action-btn {
    width:30px; height:30px; border-radius:6px; border:none;
    cursor:pointer; display:inline-flex; align-items:center;
    justify-content:center; font-size:13px; transition:all 0.2s;
    position:relative;
}
.prog-action-btn:hover:not(:disabled) { transform:scale(1.12); }
.prog-action-btn:disabled { opacity:0.4; cursor:not-allowed; }
.prog-action-btn.accept  { background:#dcfce7; color:#16a34a; }
.prog-action-btn.accept:hover:not(:disabled)  { background:#16a34a; color:white; }
.prog-action-btn.refuse  { background:#fee2e2; color:#dc2626; }
.prog-action-btn.refuse:hover:not(:disabled)  { background:#dc2626; color:white; }
.prog-action-btn.retard  { background:#fef3c7; color:#92400e; }
.prog-action-btn.retard:hover:not(:disabled)  { background:#f59e0b; color:white; }
.prog-action-btn.absent  { background:#fecaca; color:#991b1b; }
.prog-action-btn.absent:hover:not(:disabled)  { background:#dc2626; color:white; }
.prog-action-btn.cancel-refus { background:#fef3c7; color:#92400e; border:1px dashed #f59e0b; }
.prog-action-btn.cancel-refus:hover:not(:disabled) { background:#f59e0b; color:white; }

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

/* ═══ 🎓 INPUT STAGIAIRE INLINE ═══ */
.stagiaire-inline-input {
    transition: all 0.2s;
    font-family: inherit;
}
.stagiaire-inline-input:focus {
    outline: none;
    border-color: #7c3aed !important;
    box-shadow: 0 0 0 3px rgba(124,58,237,0.15);
}

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 40px rgba(0,0,0,0.25); z-index:9999; width:520px; max-width:95%; max-height:90vh; overflow-y:auto; }

.toast-notification { position:fixed; bottom:20px; right:20px; background:#1f2937; color:#fff; padding:12px 20px; border-radius:8px; font-size:14px; box-shadow:0 4px 12px rgba(0,0,0,0.3); z-index:99999; max-width:400px; animation:slideInToast 0.3s ease; }
.toast-notification.success { background:#16a34a; }
.toast-notification.error   { background:#dc2626; }
.toast-notification.warning { background:#f59e0b; }
@keyframes slideInToast { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }

.empty-state { text-align:center; padding:60px 20px; color:#94a3b8; }
.empty-state .ico { font-size:56px; margin-bottom:14px; }

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
.selection-bar-header { display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:14px; }
.selection-bar-title { font-weight:800;color:#1d4ed8;font-size:14px;display:flex;align-items:center;gap:8px; }
.selection-bar-count { background:#1d4ed8;color:white;padding:2px 10px;border-radius:20px;font-size:12px;font-weight:800; }
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

.checkbox-ligne { width:18px; height:18px; cursor:pointer; accent-color:#1d4ed8; }
.checkbox-ligne:disabled { cursor:not-allowed; opacity:0.4; }

.badge-statut {
    display:inline-flex; align-items:center; gap:3px;
    padding:2px 8px; border-radius:8px;
    font-size:9px; font-weight:800;
    margin-left:6px;
}
.badge-statut.accepte  { background:#dcfce7; color:#166534; }
.badge-statut.refuse   { background:#fee2e2; color:#991b1b; }
.badge-statut.present  { background:#dcfce7; color:#166534; }
.badge-statut.retard   { background:#fef3c7; color:#92400e; }
.badge-statut.absent   { background:#fee2e2; color:#991b1b; }

.badge-refus-attente {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 10px; border-radius:8px;
    font-size:10px; font-weight:900;
    background:linear-gradient(135deg,#fef3c7,#fde68a);
    color:#92400e;
    border:1.5px dashed #f59e0b;
    margin-left:6px;
    animation:pulseBadge 2s ease-in-out infinite;
}
@keyframes pulseBadge {
    0%, 100% { opacity: 1; }
    50%      { opacity: 0.75; }
}

.motif-refus-preview {
    font-size:10px;
    color:#92400e;
    font-style:italic;
    margin-top:3px;
    line-height:1.3;
    padding:3px 6px;
    background:#fffbeb;
    border-left:2px solid #f59e0b;
    border-radius:3px;
}

/* ═══ MODALE TITRE ═══ */
#titreModalOverlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9998; }
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

/* ═══════════════════════════════════════════════════════ */
/* 🎯 WIZARD                                               */
/* ═══════════════════════════════════════════════════════ */
.wizard-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.6); backdrop-filter:blur(3px); z-index:99998; }
.wizard-overlay.visible { display:block; }

.wizard-modal {
    display:none; position:fixed; top:50%; left:50%;
    transform:translate(-50%,-50%);
    background:white; border-radius:16px;
    box-shadow:0 25px 70px rgba(0,0,0,0.4);
    z-index:99999; width:750px; max-width:95%; max-height:90vh;
    overflow:hidden; flex-direction:column;
}
.wizard-modal.visible { display:flex; }

.wizard-header {
    background:linear-gradient(135deg,#7c3aed,#a855f7);
    color:white; padding:20px 24px;
    display:flex; justify-content:space-between; align-items:flex-start;
}
.wizard-title { font-weight:900; font-size:17px; letter-spacing:0.3px; }
.wizard-subtitle { font-size:12px; opacity:0.9; margin-top:3px; }
.wizard-close {
    background:rgba(255,255,255,0.2); border:none; color:white;
    width:30px; height:30px; border-radius:50%; cursor:pointer;
    font-size:14px; display:flex; align-items:center;
    justify-content:center; transition:0.2s;
}
.wizard-close:hover { background:rgba(255,255,255,0.35); transform:rotate(90deg); }

.wizard-progress { height:4px; background:#e2e8f0; }
.wizard-progress-bar {
    height:100%; background:linear-gradient(90deg,#7c3aed,#a855f7);
    width:9.09%; transition:width 0.3s ease; border-radius:0 2px 2px 0;
}

.wizard-body { padding:24px; overflow-y:auto; flex:1; max-height:55vh; }
.wizard-step { display:none; }
.wizard-step.active { display:block; }

.step-title { font-weight:800; font-size:15px; color:#1e3a5f; margin-bottom:6px; }
.step-desc { font-size:12.5px; color:#64748b; margin-bottom:16px; line-height:1.5; }

.wizard-input, .wizard-textarea {
    width:100%; padding:11px 14px;
    border:2px solid #e2e8f0; border-radius:10px;
    font-size:13.5px; font-family:inherit; transition:0.2s;
    box-sizing:border-box;
}
.wizard-input:focus, .wizard-textarea:focus {
    outline:none; border-color:#7c3aed;
    box-shadow:0 0 0 3px rgba(124,58,237,0.1);
}
.wizard-textarea { resize:vertical; min-height:100px; line-height:1.5; }
.wizard-label { display:block; font-size:12px; font-weight:700; color:#475569; margin-bottom:6px; }
.wizard-hint { font-size:11px; color:#94a3b8; margin-top:6px; }

.wizard-btn-add {
    margin-top:12px; background:#eff6ff; color:#1d4ed8;
    border:1.5px dashed #93c5fd; border-radius:8px;
    padding:8px 16px; font-size:12px; font-weight:700;
    cursor:pointer; transition:0.2s;
}
.wizard-btn-add:hover { background:#dbeafe; border-color:#1d4ed8; }

.autre-row { display:flex; gap:8px; margin-bottom:8px; align-items:center; }
.autre-row input { flex:1; padding:8px 12px; border:1.5px solid #e2e8f0; border-radius:8px; font-size:13px; }
.autre-row button {
    background:#fee2e2; color:#dc2626; border:none;
    width:32px; height:32px; border-radius:8px;
    cursor:pointer; font-weight:900; transition:0.2s;
}
.autre-row button:hover { background:#dc2626; color:white; }

.stagiaire-table { width:100%; border-collapse:collapse; margin-top:10px; font-size:12px; }
.stagiaire-table thead { background:#f3e8ff; }
.stagiaire-table thead th {
    padding:8px; text-align:left; font-weight:800;
    color:#6b21a8; font-size:11px;
}
.stagiaire-table tbody td { padding:6px 8px; border-bottom:1px solid #e9d5ff; }
.stagiaire-table tbody input {
    width:100%; padding:6px 10px;
    border:1.5px solid #e9d5ff; border-radius:6px; font-size:12px;
}
.stagiaire-table tbody input:focus { outline:none; border-color:#7c3aed; }

.wizard-footer {
    padding:16px 24px; background:#f8fafc;
    border-top:1px solid #e2e8f0;
    display:flex; justify-content:space-between; gap:10px;
}
.wizard-btn {
    padding:10px 20px; border-radius:10px;
    font-size:13px; font-weight:800;
    cursor:pointer; border:none; transition:0.2s;
}
.wizard-btn-prev { background:white; color:#64748b; border:1.5px solid #e2e8f0; }
.wizard-btn-prev:hover { border-color:#94a3b8; }
.wizard-btn-prev:disabled { opacity:0.4; cursor:not-allowed; }
.wizard-btn-next { background:linear-gradient(135deg,#7c3aed,#6d28d9); color:white; margin-left:auto; }
.wizard-btn-next:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(124,58,237,0.4); }
.wizard-btn-finish { background:linear-gradient(135deg,#15803d,#16a34a); color:white; margin-left:auto; }
.wizard-btn-finish:hover { transform:translateY(-2px); box-shadow:0 4px 12px rgba(22,163,74,0.4); }
</style>

{{-- EN-TÊTE --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">
            🎯 Étape 2 — Appréciation des implantations
        </h2>
        <div style="font-size:13px;color:#64748b;">
            <strong style="color:#1e3a5f;font-size:15px;">{{ $lignes->count() }}</strong> ligne(s) —
            <strong style="color:#7c3aed;">{{ collect($lignes)->sum(fn($l) => count($l['affectation_ids'])) }}</strong> affectation(s)
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('affectations.programmation') }}" class="btn btn-outline-primary btn-sm">
            ⬅️ Retour étape 1
        </a>
        <a href="{{ route('affectations.documents.actives') }}"
           class="btn btn-sm"
           style="background:linear-gradient(135deg,#d97706,#f59e0b);
                  color:white;border:none;font-weight:800;
                  box-shadow:0 3px 10px rgba(217,119,6,0.3);">
            🎯 Mes rapports d'appréciations
        </a>
        <a href="{{ route('affectations.liste') }}" class="btn btn-outline-secondary btn-sm">
            📋 Liste des affectations
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
        <div><div class="lbl">Total</div><div class="val">{{ $stats['total'] }}</div></div>
    </div>
    <div class="prog-stat" style="border-left-color:#16a34a;">
        <div class="ico" style="background:#dcfce7;color:#16a34a;">✅</div>
        <div><div class="lbl">Acceptés</div><div class="val">{{ $stats['acceptes'] ?? 0 }}</div></div>
    </div>
    <div class="prog-stat" style="border-left-color:#dc2626;">
        <div class="ico" style="background:#fee2e2;color:#dc2626;">❌</div>
        <div><div class="lbl">Refusés</div><div class="val">{{ $stats['refuses'] ?? 0 }}</div></div>
    </div>
    <div class="prog-stat" style="border-left-color:#16a34a;">
    <div class="ico" style="background:#dcfce7;color:#16a34a;">👤</div>
    <div><div class="lbl">Présents</div><div class="val">{{ $stats['presents'] ?? 0 }}</div></div>
</div>
<div class="prog-stat" style="border-left-color:#f59e0b;">
    <div class="ico" style="background:#fef3c7;color:#f59e0b;">⏰</div>
    <div><div class="lbl">Retards</div><div class="val">{{ $stats['retards'] ?? 0 }}</div></div>
</div>
<div class="prog-stat" style="border-left-color:#dc2626;">
    <div class="ico" style="background:#fee2e2;color:#dc2626;">🚫</div>
    <div><div class="lbl">Absents</div><div class="val">{{ $stats['absents'] ?? 0 }}</div></div>
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
                    <th style="text-align:right;min-width:180px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($lignes as $index => $ligne)
                @php
                    $statutAccept      = $ligne['statut_acceptation'] ?? 'en_attente';
                    $statutPresence    = $ligne['statut_presence'] ?? 'en_attente';
                    $refusEnAttente    = $ligne['refus_en_attente'] ?? false;
                    $motifRefusPrevu   = $ligne['motif_refus_prevu'] ?? null;

                    if ($refusEnAttente) {
                        $rowClass = 'row-refus-attente';
                        $badge = '<span class="badge-refus-attente">⚠️ Refus en attente</span>';
                    } elseif ($statutAccept === 'accepte') {
                        $rowClass = 'row-accepte';
                        $badge = '<span class="badge-statut accepte">✅ Accepté</span>';
                    } elseif ($statutAccept === 'refuse') {
                        $rowClass = 'row-refuse';
                        $badge = '<span class="badge-statut refuse">❌ Refusé</span>';
                    } elseif ($statutPresence === 'present') {
                        $rowClass = 'presence-present';
                        $badge = '<span class="badge-statut present">👤 Présent</span>';
                    } elseif ($statutPresence === 'retard') {
                        $rowClass = 'presence-retard';
                        $badge = '<span class="badge-statut retard">⏰ Retard</span>';
                    } elseif ($statutPresence === 'absent') {
                        $rowClass = 'presence-absent';
                        $badge = '<span class="badge-statut absent">🚫 Absent</span>';
                    } else {
                        $rowClass = 'presence-attente';
                        $badge = '';
                    }

                    $estFigee = in_array($statutAccept, ['accepte', 'refuse']);
                @endphp
                <tr class="{{ $rowClass }}"
                    data-cle="{{ $ligne['affectation_ids'][0] ?? '' }}"
                    id="row-{{ $ligne['affectation_ids'][0] ?? $index }}">
                    <td style="text-align:center;">
                        <div style="display:flex;align-items:center;gap:6px;">
                            <input type="checkbox" class="checkbox-ligne checkbox-ligne-item"
                                   data-cle="{{ $ligne['affectation_ids'][0] ?? '' }}"
                                   data-affectation-ids='@json($ligne["affectation_ids"])'
                                   onchange="toggleSelectionLigne(this)"
                                   {{ $estFigee ? 'disabled' : '' }}>
                            <span class="prog-num">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="prog-nom">
                            {{ $ligne['beneficiaire'] }}
                            {!! $badge !!}
                        </div>
                        @if($refusEnAttente && $motifRefusPrevu)
                            <div class="motif-refus-preview">
                                💬 {{ \Illuminate\Support\Str::limit($motifRefusPrevu, 60) }}
                            </div>
                        @endif
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
                        @if($ligne['geometre_id'])
                            <span style="font-size:11px;font-weight:700;color:#1e3a5f;background:#eff6ff;padding:3px 8px;border-radius:6px;display:inline-block;">
                                👷 {{ $ligne['geometre_nom'] ?? '—' }}
                            </span>
                        @else
                            <span style="font-size:11px;color:#94a3b8;">— Non attribué —</span>
                        @endif
                    </td>

                    <td>
                        @if($ligne['heure_implantation'])
                            <span style="font-size:11px;font-weight:700;color:#1e3a5f;background:#eff6ff;padding:3px 8px;border-radius:6px;display:inline-block;">
                                ⏰ {{ substr($ligne['heure_implantation'], 0, 5) }}
                            </span>
                        @else
                            <span style="font-size:11px;color:#94a3b8;">—</span>
                        @endif
                    </td>

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

                    <td>
                        <div style="display:flex;gap:4px;justify-content:flex-end;flex-wrap:wrap;">
                            @if($refusEnAttente)
                                <button class="prog-action-btn cancel-refus"
                                        onclick='annulerRefusEnAttente(@json($ligne["affectation_ids"]), @json($ligne["beneficiaire"]))'
                                        title="Annuler le refus en attente">
                                    ↩️
                                </button>
                            @else
                                <button class="prog-action-btn accept"
                                        onclick='ouvrirAccept(@json($ligne["affectation_ids"]), @json($ligne["beneficiaire"]))'
                                        title="Accepter"
                                        {{ $estFigee ? 'disabled' : '' }}>✅</button>
                                <button class="prog-action-btn refuse"
                                        onclick='ouvrirRefus(@json($ligne["affectation_ids"]), @json($ligne["beneficiaire"]))'
                                        title="Refuser"
                                        {{ $estFigee ? 'disabled' : '' }}>❌</button>
                                <button class="prog-action-btn retard"
                                        onclick='marquerPresence(@json($ligne["affectation_ids"]), "retard", {{ $ligne["affectation_ids"][0] ?? 0 }})'
                                        title="Marquer en retard"
                                        {{ $estFigee ? 'disabled' : '' }}>⏰</button>
                                <button class="prog-action-btn absent"
                                        onclick='marquerPresence(@json($ligne["affectation_ids"]), "absent", {{ $ligne["affectation_ids"][0] ?? 0 }})'
                                        title="Marquer absent"
                                        {{ $estFigee ? 'disabled' : '' }}>🚫</button>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty-state">
        <div class="ico">⏳</div>
        <div style="font-weight:700;font-size:15px;color:#475569;">
            @if($dateActive)
                Aucune ligne prête pour le {{ $dateActive->format('d/m/Y') }}
            @else
                Aucune date sélectionnée
            @endif
        </div>
        <div style="font-size:12px;margin-top:6px;">
            @if($dateActive)
                Toutes les lignes de cette date doivent avoir un <strong>géomètre + une heure</strong> pour apparaître ici.<br>
                Complétez-les dans l'Étape 1 puis cliquez sur "Valider".
            @else
                Choisissez d'abord une date dans le calendrier.
            @endif
        </div>
        <a href="{{ route('affectations.programmation') }}" class="btn btn-primary btn-sm" style="margin-top:14px;">
            ⬅️ Retour Étape 1
        </a>
    </div>
    @endif
</div>

{{-- ✅ BOUTON ENREGISTRER LE PDF FINAL --}}
@if($lignes->count() > 0)
<div style="margin-top:20px;padding:20px;background:white;border-radius:12px;
            box-shadow:0 2px 10px rgba(0,0,0,0.05);border-left:4px solid #f59e0b;
            display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap;">
    <div>
        <div style="font-weight:800;color:#1e3a5f;font-size:15px;">
            💾 Enregistrer la programmation finalisée
        </div>
        <div style="font-size:12px;color:#64748b;margin-top:4px;">
            Générez le <strong>PDF final avec tous les statuts</strong> de la journée.<br>
            Vous pouvez regénérer ce PDF à tout moment.
        </div>
    </div>
    <button onclick="validerEtape3()" id="btn-valider-3"
            style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;
                   border:none;border-radius:10px;padding:14px 28px;
                   font-size:14px;font-weight:800;cursor:pointer;
                   box-shadow:0 4px 14px rgba(245,158,11,0.3);
                   display:inline-flex;align-items:center;gap:8px;
                   transition:all 0.2s;"
            onmouseover="this.style.transform='translateY(-2px)'"
            onmouseout="this.style.transform='translateY(0)'">
        💾 Enregistrer le PDF final
    </button>
</div>
@endif

{{-- MODALES ACCEPT/REFUS/MODIF --}}
<div class="modal-overlay" id="acceptOverlay" onclick="fermerAccept()"></div>
<div class="modal-box" id="acceptModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#16a34a;font-weight:800;margin:0;">✅ Accepter l'affectation</h5>
        <button onclick="fermerAccept()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div id="acceptInfo" style="background:#f0fdf4;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#166534;"></div>
    <label style="font-size:12px;font-weight:700;color:#64748b;">📅 Date d'acceptation <span style="color:#16a34a;">*</span></label>
    <input type="date" id="acceptDate" class="form-control form-control-sm" style="margin-top:6px;" value="{{ now()->format('Y-m-d') }}">
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerAccept()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="validerAccept()" class="btn btn-success btn-sm" style="font-weight:700;">✅ Confirmer</button>
    </div>
</div>

<div class="modal-overlay" id="refusOverlay" onclick="fermerRefus()"></div>
<div class="modal-box" id="refusModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#dc2626;font-weight:800;margin:0;">❌ Refuser l'affectation</h5>
        <button onclick="fermerRefus()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div id="refusInfo" style="background:#fef2f2;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#991b1b;"></div>

    <div style="background:#fef3c7;border:1.5px dashed #f59e0b;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:11.5px;color:#78350f;line-height:1.5;">
        ⚠️ <strong>Attention :</strong> le refus sera <strong>enregistré en attente</strong>.
        Il ne sera réellement appliqué qu'à la <strong>génération du rapport PDF final</strong>.
    </div>

    <div style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:700;color:#64748b;">📅 Date du refus <span style="color:#dc2626;">*</span></label>
        <input type="date" id="refusDate" class="form-control form-control-sm" style="margin-top:6px;" value="{{ now()->format('Y-m-d') }}">
    </div>
    <div>
        <label style="font-size:12px;font-weight:700;color:#64748b;">📝 Motif du refus <span style="color:#dc2626;">*</span></label>
        <textarea id="refusMotif" class="form-control" rows="4" placeholder="Expliquez pourquoi (min. 5 caractères)..." maxlength="1000" style="margin-top:6px;font-size:13px;"></textarea>
        <div style="font-size:11px;color:#94a3b8;margin-top:6px;"><span id="refusCompteur">0</span> / 1000</div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerRefus()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="validerRefus()" class="btn btn-warning btn-sm" style="font-weight:700;">
            ⚠️ Enregistrer le refus en attente
        </button>
    </div>
</div>

<div class="modal-overlay" id="acceptMultiOverlay" onclick="fermerAcceptMultiple()"></div>
<div class="modal-box" id="acceptMultiModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#16a34a;font-weight:800;margin:0;">✅ Accepter les lignes sélectionnées</h5>
        <button onclick="fermerAcceptMultiple()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div id="acceptMultiInfo" style="background:#f0fdf4;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#166534;"></div>
    <label style="font-size:12px;font-weight:700;color:#64748b;">📅 Date d'acceptation <span style="color:#16a34a;">*</span></label>
    <input type="date" id="acceptMultiDate" class="form-control form-control-sm" style="margin-top:6px;" value="{{ now()->format('Y-m-d') }}">
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerAcceptMultiple()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="validerAcceptMultiple()" class="btn btn-success btn-sm" style="font-weight:700;">✅ Confirmer</button>
    </div>
</div>

<div class="modal-overlay" id="refusMultiOverlay" onclick="fermerRefusMultiple()"></div>
<div class="modal-box" id="refusMultiModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#dc2626;font-weight:800;margin:0;">❌ Refuser les lignes sélectionnées</h5>
        <button onclick="fermerRefusMultiple()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div id="refusMultiInfo" style="background:#fef2f2;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:13px;color:#991b1b;"></div>

    <div style="background:#fef3c7;border:1.5px dashed #f59e0b;border-radius:8px;padding:10px 14px;margin-bottom:12px;font-size:11.5px;color:#78350f;line-height:1.5;">
        ⚠️ <strong>Attention :</strong> les refus seront <strong>enregistrés en attente</strong>.
        Ils ne seront réellement appliqués qu'à la <strong>génération du rapport PDF final</strong>.
    </div>

    <div style="margin-bottom:12px;">
        <label style="font-size:12px;font-weight:700;color:#64748b;">📅 Date du refus <span style="color:#dc2626;">*</span></label>
        <input type="date" id="refusMultiDate" class="form-control form-control-sm" style="margin-top:6px;" value="{{ now()->format('Y-m-d') }}">
    </div>
    <div>
        <label style="font-size:12px;font-weight:700;color:#64748b;">📝 Motif du refus <span style="color:#dc2626;">*</span></label>
        <textarea id="refusMultiMotif" class="form-control" rows="4" placeholder="Expliquez pourquoi (min. 5 caractères)..." maxlength="1000" style="margin-top:6px;font-size:13px;"></textarea>
        <div style="font-size:11px;color:#94a3b8;margin-top:6px;"><span id="refusMultiCompteur">0</span> / 1000</div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-4">
        <button onclick="fermerRefusMultiple()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="validerRefusMultiple()" class="btn btn-warning btn-sm" style="font-weight:700;">
            ⚠️ Enregistrer les refus en attente
        </button>
    </div>
</div>

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
            <div style="font-size:9px;color:#94a3b8;margin-top:3px;">Vide = ne pas changer</div>
        </div>
        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">⏰ Heure d'implantation</label>
            <input type="time" id="modifMultiHeure" class="form-control form-control-sm">
            <div style="font-size:9px;color:#94a3b8;margin-top:3px;">Vide = ne pas changer</div>
        </div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:14px;">
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
            <label style="font-size:11px;font-weight:700;color:#64748b;">🎓 Stagiaire</label>
            <input type="text"
                   id="modifMultiStagiaire"
                   class="form-control form-control-sm"
                   list="liste-stagiaires"
                   placeholder="Choisir ou saisir...">
            <div style="font-size:9px;color:#94a3b8;margin-top:3px;">Vide = ne pas changer</div>
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
        <button onclick="validerModifMultiple()" class="btn btn-warning btn-sm" style="font-weight:700;">💾 Appliquer</button>
    </div>
</div>

{{-- ═══ MODALE TITRE ═══ --}}
<div id="titreModalOverlay" onclick="fermerModalTitre()"></div>
<div id="titreModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1d4ed8;font-weight:800;margin:0;">📄 Titre du document</h5>
        <button onclick="fermerModalTitre()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>
    <div style="background:#eff6ff;border-radius:10px;padding:12px 16px;margin-bottom:14px;font-size:12.5px;color:#1e40af;">
        ℹ️ Donnez un nom à ce document. Il sera utilisé comme <strong>nom du fichier PDF</strong>.<br>
        <span style="font-size:11px;color:#64748b;display:block;margin-top:6px;">
            Le <strong>titre affiché dans le PDF</strong> sera automatiquement : <em>🎯 RAPPORT DES APPRÉCIATIONS</em>
        </span>
    </div>
    <label style="font-size:12px;font-weight:700;color:#64748b;">
        📝 Nom du fichier <span style="color:#dc2626;">*</span>
    </label>
    <input type="text" id="titreDocumentInput" class="form-control form-control-sm"
           style="margin-top:6px;" maxlength="150" placeholder="Ex : Appréciations du 08/10/2026">
    <div style="font-size:11px;color:#94a3b8;margin-top:10px;">
        💡 Fichier : <strong id="apercuNomFichier">titre-2026-10-08.pdf</strong>
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

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- 🎯 MODALE WIZARD RAPPORT D'APPRÉCIATIONS (11 étapes)        --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div id="wizardOverlay" class="wizard-overlay" onclick="fermerWizard()"></div>

<div id="wizard" class="wizard-modal">
    <div class="wizard-header">
        <div>
            <div class="wizard-title">📄 Rapport des appréciations</div>
            <div class="wizard-subtitle">Étape <span id="wizardStepNum">1</span> sur 11</div>
        </div>
        <button onclick="fermerWizard()" class="wizard-close">✕</button>
    </div>

    <div class="wizard-progress">
        <div class="wizard-progress-bar" id="wizardProgressBar"></div>
    </div>

    <div class="wizard-body">

        <div class="wizard-step active" data-step="1">
            <div class="step-title">📄 Titre du document</div>
            <div class="step-desc">Ce titre sera utilisé comme <strong>nom du fichier PDF</strong>.</div>
            <input type="text" id="wz_titre" class="wizard-input" placeholder="Ex: Rapport du 04/10/2026" maxlength="150">
            <div class="wizard-hint">💡 Minimum 3 caractères</div>
        </div>

        <div class="wizard-step" data-step="2">
            <div class="step-title">📝 Introduction</div>
            <div class="step-desc">Décrivez le contexte de la mission.</div>
            <textarea id="wz_introduction" class="wizard-textarea" rows="6" placeholder="La brigade des implantations du groupe EDEN a été conviée à..."></textarea>
        </div>

        <div class="wizard-step" data-step="3">
            <div class="step-title">📊 Texte récapitulatif</div>
            <div class="step-desc">Résumé global des travaux (précède le tableau).</div>
            <textarea id="wz_recapitulatif" class="wizard-textarea" rows="5" placeholder="Les travaux topographiques avaient été prévus pour..."></textarea>
        </div>

        <div class="wizard-step" data-step="4">
            <div class="step-title">⚠️ Difficultés rencontrées</div>
            <div class="step-desc">Listez les problèmes (optionnel).</div>
            <textarea id="wz_difficultes" class="wizard-textarea" rows="5" placeholder="Ex: Le refus de la cliente X justifié par..."></textarea>
        </div>

        <div class="wizard-step" data-step="5">
            <div class="step-title">👮 Avis — Agents de sécurité</div>
            <textarea id="wz_securite" class="wizard-textarea" rows="6" placeholder="À notre arrivée sur le site..."></textarea>
        </div>

        <div class="wizard-step" data-step="6">
            <div class="step-title">🎓 Avis — Stagiaires</div>
            <textarea id="wz_stagiaires_avis" class="wizard-textarea" rows="6" placeholder="Grâce à leur engagement..."></textarea>
        </div>

        <div class="wizard-step" data-step="7">
            <div class="step-title">👥 Stagiaires par ligne</div>
            <div class="step-desc">Saisissez le nom du stagiaire pour chaque bénéficiaire.</div>

            <table class="stagiaire-table">
                <thead>
                    <tr>
                        <th style="width: 60%;">Bénéficiaire</th>
                        <th style="width: 40%;">Stagiaire</th>
                    </tr>
                </thead>
                <tbody id="wz_stagiaires_container"></tbody>
            </table>
        </div>

        <div class="wizard-step" data-step="8">
            <div class="step-title">🚗 Chauffeurs et Autres</div>
            <div class="step-desc">Ajoutez les personnes (chauffeurs, photographes, superviseurs...).</div>

            <div id="wz_autres_container"></div>
            <button type="button" onclick="ajouterAutre()" class="wizard-btn-add">➕ Ajouter une personne</button>
        </div>

        <div class="wizard-step" data-step="9">
            <div class="step-title">🎯 Conclusion</div>
            <div class="step-desc">Texte de clôture du rapport d'appréciations.</div>
            <textarea id="wz_conclusion" class="wizard-textarea" rows="6"
                      placeholder="Au terme de cette mission, la brigade des implantations du groupe EDEN a mené avec succès l'ensemble des travaux topographiques prévus."></textarea>
        </div>

        <div class="wizard-step" data-step="10">
            <div class="step-title">📎 Pièces jointes</div>
            <div class="step-desc">Listez les documents joints à ce rapport.</div>

            <div id="wz_pieces_jointes_container"></div>
            <button type="button" onclick="ajouterPieceJointe()" class="wizard-btn-add">➕ Ajouter une pièce jointe</button>
        </div>

        <div class="wizard-step" data-step="11">
            <div class="step-title">✍️ Signataire</div>
            <div class="step-desc">Nom et poste du signataire du rapport.</div>

            <label class="wizard-label">Nom complet</label>
            <input type="text" id="wz_signataire_nom" class="wizard-input" value="Josiane Morelle NGOLO AYISSI" maxlength="100">

            <label class="wizard-label" style="margin-top: 12px;">Poste</label>
            <input type="text" id="wz_signataire_poste" class="wizard-input" value="Chef de bureau des installations et du suivi des implantations" maxlength="150">
        </div>

    </div>

    <div class="wizard-footer">
        <button type="button" onclick="wizardPrecedent()" class="wizard-btn wizard-btn-prev" id="wzBtnPrev">← Précédent</button>
        <button type="button" onclick="wizardSuivant()" class="wizard-btn wizard-btn-next" id="wzBtnNext">Suivant →</button>
        <button type="button" onclick="wizardFinaliser()" class="wizard-btn wizard-btn-finish" id="wzBtnFinish" style="display:none;">✅ Générer le rapport</button>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF = window.CSRF || '{{ csrf_token() }}';

let acceptIds = [];
let refusIds = [];
let lignesSelectionnees = new Map();
let actionEnAttente = null;

// ════════════════════════════════════════════════════════════════
// 🎯 WIZARD (11 étapes)
// ════════════════════════════════════════════════════════════════
let wizardStep = 1;
const WIZARD_TOTAL_STEPS = 11;
let wizardAffectationIds = [];

// ════════════════════════════════════════════════════════════════
// 🎯 MODALE TITRE (conservée pour compatibilité)
// ════════════════════════════════════════════════════════════════
function demanderTitre(callback) {
    actionEnAttente = callback;
    const dateStr = '{{ now()->format("Y-m-d") }}';

    document.getElementById('titreDocumentInput').value = '';
    document.getElementById('titreError').style.display = 'none';
    document.getElementById('apercuNomFichier').textContent = 'appreciations-' + dateStr + '.pdf';

    document.getElementById('titreDocumentInput').oninput = function() {
        const val = this.value.trim() || 'appreciations';
        const slug = val.toLowerCase()
            .normalize('NFD').replace(/[\u0300-\u036f]/g, '')
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-|-$/g, '');
        document.getElementById('apercuNomFichier').textContent =
            (slug || 'appreciations') + '-' + dateStr + '.pdf';
    };

    document.getElementById('titreModalOverlay').classList.add('visible');
    document.getElementById('titreModal').classList.add('visible');
    setTimeout(() => document.getElementById('titreDocumentInput').focus(), 100);
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
    if (typeof callback === 'function') callback(titre);
}

// ════════════════════════════════════════════════════════════════
// ✅ VALIDER L'ÉTAPE 3 — Ouvre le WIZARD
// ════════════════════════════════════════════════════════════════
function validerEtape3() {
    const affectationIds = [];
    document.querySelectorAll('.checkbox-ligne-item').forEach(cb => {
        try {
            const ids = JSON.parse(cb.dataset.affectationIds || '[]');
            ids.forEach(id => {
                if (!affectationIds.includes(id)) affectationIds.push(id);
            });
        } catch (e) {}
    });

    if (affectationIds.length === 0) {
        alert('⚠️ Aucune affectation à valider.');
        return;
    }

    ouvrirWizard(affectationIds);
}

function ouvrirWizard(affectationIds) {
    wizardAffectationIds = affectationIds;

    const today = new Date().toLocaleDateString('fr-FR');
    document.getElementById('wz_titre').value = 'Rapport des appréciations du ' + today;

    document.getElementById('wz_conclusion').value =
        "Au terme de cette mission, la brigade des implantations du groupe EDEN a mené avec succès l'ensemble des travaux topographiques prévus.";

    document.getElementById('wz_pieces_jointes_container').innerHTML = '';
    ajouterPieceJointe('Fichier de mise à jour des différents sites');

    const container = document.getElementById('wz_stagiaires_container');
    container.innerHTML = '';

    document.querySelectorAll('.checkbox-ligne-item').forEach(cb => {
        try {
            const ids = JSON.parse(cb.dataset.affectationIds || '[]');
            if (!ids.length) return;

            const row = cb.closest('tr');
            let nom = row.querySelector('.prog-nom')?.textContent.trim() || '—';
            nom = nom.replace('✅ Accepté', '').replace('❌ Refusé', '')
                     .replace('👤 Présent', '').replace('⏰ Retard', '')
                     .replace('🚫 Absent', '').replace('⚠️ Refus en attente', '').trim();

            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td><strong>${nom}</strong></td>
                <td><input type="text" class="wz-stagiaire-par-ligne" data-ids='${JSON.stringify(ids)}' list="liste-stagiaires" placeholder="Nom du stagiaire"></td>
            `;
            container.appendChild(tr);
        } catch (e) {}
    });

    document.getElementById('wz_autres_container').innerHTML = '';
    ajouterAutre();

    wizardStep = 1;
    mettreAJourWizard();
    document.getElementById('wizardOverlay').classList.add('visible');
    document.getElementById('wizard').classList.add('visible');
}

function fermerWizard() {
    document.getElementById('wizardOverlay').classList.remove('visible');
    document.getElementById('wizard').classList.remove('visible');
}

function mettreAJourWizard() {
    document.querySelectorAll('.wizard-step').forEach(el => el.classList.remove('active'));
    document.querySelector(`.wizard-step[data-step="${wizardStep}"]`).classList.add('active');
    document.getElementById('wizardStepNum').textContent = wizardStep;
    document.getElementById('wizardProgressBar').style.width = (wizardStep / WIZARD_TOTAL_STEPS * 100) + '%';
    document.getElementById('wzBtnPrev').disabled = (wizardStep === 1);
    document.getElementById('wzBtnNext').style.display = (wizardStep === WIZARD_TOTAL_STEPS) ? 'none' : 'inline-block';
    document.getElementById('wzBtnFinish').style.display = (wizardStep === WIZARD_TOTAL_STEPS) ? 'inline-block' : 'none';
}

function wizardSuivant() {
    if (wizardStep === 1) {
        const titre = document.getElementById('wz_titre').value.trim();
        if (titre.length < 3) { showToast('⚠️ Titre obligatoire (min. 3 caractères).', 'warning'); return; }
    }
    if (wizardStep < WIZARD_TOTAL_STEPS) { wizardStep++; mettreAJourWizard(); }
}

function wizardPrecedent() {
    if (wizardStep > 1) { wizardStep--; mettreAJourWizard(); }
}

function ajouterAutre() {
    const container = document.getElementById('wz_autres_container');
    const row = document.createElement('div');
    row.className = 'autre-row';
    row.innerHTML = `
        <input type="text" class="wz-autre-nom" placeholder="Nom (ex: M. JOHN)">
        <input type="text" class="wz-autre-role" placeholder="Rôle (ex: Conduite)">
        <button type="button" onclick="this.parentElement.remove()">✕</button>
    `;
    container.appendChild(row);
}

function ajouterPieceJointe(valeur = '') {
    const container = document.getElementById('wz_pieces_jointes_container');
    const row = document.createElement('div');
    row.className = 'autre-row';
    row.innerHTML = `
        <input type="text" class="wz-piece-jointe" placeholder="Ex: Fichier de mise à jour des différents sites" value="${valeur.replace(/"/g, '&quot;')}">
        <button type="button" onclick="this.parentElement.remove()">✕</button>
    `;
    container.appendChild(row);
}

function wizardFinaliser() {
    const titre = document.getElementById('wz_titre').value.trim();
    if (titre.length < 3) { showToast('⚠️ Titre obligatoire.', 'warning'); return; }

    const stagiairesLignes = [];
    document.querySelectorAll('.wz-stagiaire-par-ligne').forEach(input => {
        try {
            const ids = JSON.parse(input.dataset.ids || '[]');
            const stagiaire = input.value.trim();
            if (stagiaire) stagiairesLignes.push({ affectation_ids: ids, stagiaire: stagiaire });
        } catch (e) {}
    });

    const autres = [];
    document.querySelectorAll('#wz_autres_container .autre-row').forEach(row => {
        const nom  = row.querySelector('.wz-autre-nom')?.value.trim();
        const role = row.querySelector('.wz-autre-role')?.value.trim();
        if (nom && role) autres.push({ nom, role });
    });

    const piecesJointes = [];
    document.querySelectorAll('.wz-piece-jointe').forEach(input => {
        const val = input.value.trim();
        if (val) piecesJointes.push(val);
    });

    const payload = {
        affectation_ids: wizardAffectationIds,
        titre_document:  titre,
        narratif: {
            introduction:      document.getElementById('wz_introduction').value.trim(),
            recapitulatif:     document.getElementById('wz_recapitulatif').value.trim(),
            difficultes:       document.getElementById('wz_difficultes').value.trim(),
            securite:          document.getElementById('wz_securite').value.trim(),
            stagiaires_avis:   document.getElementById('wz_stagiaires_avis').value.trim(),
            stagiaires_lignes: stagiairesLignes,
            autres:            autres,
            conclusion:        document.getElementById('wz_conclusion').value.trim(),
            pieces_jointes:    piecesJointes,
            signataire_nom:    document.getElementById('wz_signataire_nom').value.trim(),
            signataire_poste:  document.getElementById('wz_signataire_poste').value.trim(),
        },
    };

    const btn = document.getElementById('wzBtnFinish');
    btn.disabled = true;
    btn.innerHTML = '⏳ Génération...';

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('{{ route("affectations.valider-etape-3") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify(payload),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        btn.disabled = false;
        btn.innerHTML = '✅ Générer le rapport';

        if (data.success) {
            fermerWizard();

            showToastAvecLien(
                '✅ ' + data.message,
                data.doc?.url,
                'success'
            );

            setTimeout(() => location.reload(), 3000);
        } else {
            alert('❌ ' + (data.message || 'Erreur'));
        }
    })
    .catch(err => {
        if (window.EdenLoader) window.EdenLoader.hide();
        btn.disabled = false;
        btn.innerHTML = '✅ Générer le rapport';
        console.error(err);
        alert('❌ Erreur réseau');
    });
}

// ════════════════════════════════════════════════════════════════
// SÉLECTION MULTIPLE
// ════════════════════════════════════════════════════════════════
function toggleSelectionLigne(checkbox) {
    if (checkbox.disabled) return;
    const cle = checkbox.dataset.cle;
    let ids = [];
    try { ids = JSON.parse(checkbox.dataset.affectationIds || '[]'); } catch (e) { return; }

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
    document.querySelectorAll('.checkbox-ligne-item:not([disabled])').forEach(cb => {
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
// MARQUER PRÉSENCE
// ════════════════════════════════════════════════════════════════
function marquerPresence(affectationIds, statut, rowId) {
    const labels = { 'retard': '⏰ Retard', 'absent': '❌ Absent', 'present': '✅ Présent', 'en_attente': '⏳ En attente' };
    const label = labels[statut] || statut;

    if (!confirm(`Marquer cette ligne comme "${label}" ?`)) return;

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('{{ route("affectations.groupe.programmation") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ affectation_ids: affectationIds, statut_presence: statut }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('✅ ' + (data.message || 'Mis à jour'), 'success');
            setTimeout(() => location.reload(), 900);
        } else showToast('❌ ' + (data.message || 'Erreur'), 'error');
    })
    .catch(err => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// ✅ ACCEPTER / ❌ REFUSER (individuel & multiple)
// ════════════════════════════════════════════════════════════════
function ouvrirAcceptMultiple() {
    if (lignesSelectionnees.size === 0) { showToast('⚠️ Aucune ligne sélectionnée', 'warning'); return; }
    document.getElementById('acceptMultiInfo').innerHTML = `
        <div style="font-weight:700;">⚙️ Action groupée</div>
        <div style="font-size:12px;margin-top:6px;">
            <strong>${lignesSelectionnees.size} ligne(s)</strong>
            — soit <strong>${tousLesIdsSelectionnes().length} affectation(s)</strong> seront acceptées.
        </div>`;
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

    fetch('{{ route("affectations.groupe.programmation-accepter-multiple") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ affectation_ids: tousLesIdsSelectionnes(), date_acceptation: date }),
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

function ouvrirRefusMultiple() {
    if (lignesSelectionnees.size === 0) { showToast('⚠️ Aucune ligne sélectionnée', 'warning'); return; }
    document.getElementById('refusMultiInfo').innerHTML = `
        <div style="font-weight:700;">⚙️ Action groupée</div>
        <div style="font-size:12px;margin-top:6px;">
            <strong>${lignesSelectionnees.size} ligne(s)</strong>
            — soit <strong>${tousLesIdsSelectionnes().length} affectation(s)</strong> seront marquées en attente de refus.
        </div>`;
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

    fetch('{{ route("affectations.groupe.programmation-refuser-multiple") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ affectation_ids: tousLesIdsSelectionnes(), date_refus: date, motif_refus: motif }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('⚠️ ' + data.message, 'success');
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
// MODIFIER MULTIPLE
// ════════════════════════════════════════════════════════════════
function ouvrirModifMultiple() {
    if (lignesSelectionnees.size === 0) { showToast('⚠️ Aucune ligne sélectionnée', 'warning'); return; }
    document.getElementById('modifMultiInfo').innerHTML = `
        <div style="font-weight:700;">⚙️ Action groupée</div>
        <div style="font-size:12px;margin-top:6px;">
            <strong>${lignesSelectionnees.size} ligne(s)</strong>
            — soit <strong>${tousLesIdsSelectionnes().length} affectation(s)</strong>
            <br>Remplissez uniquement les champs à modifier.
        </div>`;
    document.getElementById('modifMultiDate').value       = '';
    document.getElementById('modifMultiHeure').value      = '';
    document.getElementById('modifMultiGeometre').value   = '';
    document.getElementById('modifMultiStagiaire').value  = '';
    document.getElementById('modifMultiPresence').value   = '';
    document.getElementById('modifMultiOverlay').style.display = 'block';
    document.getElementById('modifMultiModal').style.display   = 'block';
}

function fermerModifMultiple() {
    document.getElementById('modifMultiOverlay').style.display = 'none';
    document.getElementById('modifMultiModal').style.display   = 'none';
}

function validerModifMultiple() {
    const date       = document.getElementById('modifMultiDate').value;
    const heure      = document.getElementById('modifMultiHeure').value;
    const geo        = document.getElementById('modifMultiGeometre').value;
    const stagiaire  = document.getElementById('modifMultiStagiaire').value.trim();
    const presence   = document.getElementById('modifMultiPresence').value;

    if (!date && !heure && !geo && !stagiaire && !presence) {
        showToast('⚠️ Remplissez au moins un champ', 'warning');
        return;
    }

    const lignes = [];
    lignesSelectionnees.forEach((ids, cle) => lignes.push({ cle, ids }));

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('{{ route("affectations.groupe.programmation-multiple") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({
            lignes: lignes,
            date_implantation: date || null,
            heure_implantation: heure || null,
            geometre_id: geo || null,
            stagiaire: stagiaire || null,
            statut_presence: presence || null,
        }),
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
// ✅ ACCEPTER INDIVIDUEL
// ════════════════════════════════════════════════════════════════
function ouvrirAccept(ids, nom) {
    acceptIds = ids;
    document.getElementById('acceptInfo').innerHTML = `
        <div style="font-weight:700;">👤 ${nom}</div>
        <div style="font-size:11px;margin-top:4px;">${ids.length} affectation(s) seront acceptées</div>`;
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

    fetch('{{ route("affectations.groupe.accepter") }}', {
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

function ouvrirRefus(ids, nom) {
    refusIds = ids;
    document.getElementById('refusInfo').innerHTML = `
        <div style="font-weight:700;">👤 ${nom}</div>
        <div style="font-size:11px;margin-top:4px;">${ids.length} affectation(s) — motif et date obligatoires</div>`;
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

    fetch('{{ route("affectations.groupe.refuser") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ affectation_ids: refusIds, date_refus: date, motif_refus: motif }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('⚠️ ' + data.message, 'success');
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
// ↩️ ANNULER UN REFUS EN ATTENTE
// ════════════════════════════════════════════════════════════════
function annulerRefusEnAttente(ids, nom) {
    if (!confirm(`↩️ Annuler le refus en attente pour « ${nom} » ?\n\nLa ligne redeviendra modifiable.`)) return;

    if (window.EdenLoader) window.EdenLoader.show();

    fetch('{{ route("affectations.groupe.annuler-refus") }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        body: JSON.stringify({ affectation_ids: ids }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast('✅ ' + data.message, 'success');
            setTimeout(() => location.reload(), 900);
        } else showToast('❌ ' + (data.message || 'Erreur'), 'error');
    })
    .catch(() => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// 🎓 SAUVEGARDER LE STAGIAIRE (inline dans le tableau)
// ════════════════════════════════════════════════════════════════
function sauvegarderStagiaireInline(input) {
    const affectationIds = JSON.parse(input.dataset.affectationIds || '[]');
    const stagiaire = input.value.trim();

    if (!affectationIds.length) {
        showToast('⚠️ Aucune affectation à modifier', 'warning');
        return;
    }

    if (input.dataset.lastValue === stagiaire) {
        return;
    }

    input.style.opacity = '0.6';
    input.style.borderColor = '#7c3aed';

    fetch('{{ route("affectations.update-stagiaire-groupe") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: JSON.stringify({
            affectation_ids: affectationIds,
            stagiaire: stagiaire || null,
        }),
    })
    .then(r => r.json())
    .then(data => {
        input.style.opacity = '1';

        if (data.success) {
            input.style.borderColor = '#16a34a';
            input.style.background = '#f0fdf4';
            input.dataset.lastValue = stagiaire;
            setTimeout(() => {
                input.style.background = '';
                input.style.borderColor = '#e2e8f0';
            }, 1500);
            showToast('✅ Stagiaire enregistré', 'success');
        } else {
            input.style.borderColor = '#dc2626';
            input.style.background = '#fef2f2';
            showToast('❌ ' + (data.message || 'Erreur'), 'error');
        }
    })
    .catch(err => {
        input.style.opacity = '1';
        input.style.borderColor = '#dc2626';
        input.style.background = '#fef2f2';
        console.error(err);
        showToast('❌ Erreur réseau', 'error');
    });
}

// ════════════════════════════════════════════════════════════════
// COMPTEURS
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

    // Mémoriser les valeurs initiales des champs stagiaires
    document.querySelectorAll('.stagiaire-inline-input').forEach(input => {
        input.dataset.lastValue = input.value.trim();
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

function showToastAvecLien(message, url, type = 'info') {
    document.querySelectorAll('.toast-notification').forEach(el => el.remove());

    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.style.display = 'flex';
    toast.style.alignItems = 'center';
    toast.style.gap = '12px';
    toast.style.padding = '14px 18px';

    const msg = document.createElement('span');
    msg.textContent = message;
    msg.style.flex = '1';

    if (url) {
        const link = document.createElement('a');
        link.href = url;
        link.target = '_blank';
        link.textContent = '📄 Voir le PDF';
        link.style.cssText = `
            background: white;
            color: #16a34a;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 12px;
            text-decoration: none;
            white-space: nowrap;
            transition: all 0.2s;
        `;
        link.onmouseover = () => link.style.transform = 'scale(1.05)';
        link.onmouseout  = () => link.style.transform = 'scale(1)';
        toast.appendChild(msg);
        toast.appendChild(link);
    } else {
        toast.appendChild(msg);
    }

    document.body.appendChild(toast);

    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 8000);
}

</script>
@endsection

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- 🎓 DATALIST GLOBAL DES STAGIAIRES                                --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<datalist id="liste-stagiaires">
    @foreach($stagiaires ?? [] as $stagiaire)
        <option value="{{ $stagiaire }}">
    @endforeach
</datalist>

{{-- ═══ PDFs de la date active ═══ --}}
@if(isset($documentsPdf))
    @include('admin.affectations._documents-pdf')
@endif