@extends('admin.affectations.layout')
@section('content')

<style>
/* ═══════════════════════════════════════════════════════════════ */
/* PAGE CHOIX DE LA DATE — CALENDRIER COMPACT & CENTRÉ             */
/* ═══════════════════════════════════════════════════════════════ */

.choix-wrap { max-width: 780px; margin: 0 auto; }

/* ─── EN-TÊTE ─── */
.choix-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #1d4ed8 50%, #7c3aed 100%);
    border-radius: 16px;
    padding: 22px 26px;
    color: white;
    margin-bottom: 18px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 6px 20px rgba(29,78,216,0.22);
}
.choix-header::before {
    content: '';
    position: absolute;
    top: -50%; right: -10%;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius: 50%;
}
.choix-header::after {
    content: '📅';
    position: absolute;
    right: 24px; top: 50%;
    transform: translateY(-50%);
    font-size: 78px;
    opacity: 0.12;
}
.choix-header h2 {
    margin: 0 0 6px 0;
    font-weight: 900;
    font-size: 21px;
    letter-spacing: -0.4px;
    position: relative; z-index: 1;
}
.choix-header .sub {
    font-size: 12.5px;
    opacity: 0.92;
    line-height: 1.45;
    max-width: 500px;
    position: relative; z-index: 1;
}

/* ─── STATS ─── */
.global-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
    gap: 10px;
    margin-bottom: 14px;
}
.stat-card {
    background: white;
    border-radius: 10px;
    padding: 10px 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    border-left: 3px solid #1d4ed8;
    display: flex; align-items: center; gap: 10px;
    transition: transform 0.2s;
}
.stat-card:hover { transform: translateY(-2px); }
.stat-card .ico {
    width: 34px; height: 34px; border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    font-size: 15px; flex-shrink: 0;
}
.stat-card .lbl {
    font-size: 9px; color: #64748b;
    text-transform: uppercase; font-weight: 700;
    letter-spacing: 0.3px;
}
.stat-card .val {
    font-size: 16px; font-weight: 900;
    color: #1e3a5f; line-height: 1.1; margin-top: 1px;
}

/* ─── INFO ─── */
.info-box {
    background: linear-gradient(135deg, #eff6ff, #dbeafe);
    border-left: 3px solid #1d4ed8;
    border-radius: 10px;
    padding: 11px 16px;
    font-size: 12px;
    color: #1e40af;
    margin-bottom: 14px;
    display: flex; align-items: center; gap: 10px;
    line-height: 1.45;
}
.info-box .ico { font-size: 18px; flex-shrink: 0; }

/* ─── ALERTE VERROUILLAGE ─── */
.lock-alert {
    background: linear-gradient(135deg, #fef3c7, #fde68a);
    border-left: 4px solid #f59e0b;
    border-radius: 12px;
    padding: 14px 20px;
    margin-bottom: 14px;
    box-shadow: 0 3px 12px rgba(245,158,11,0.15);
}
.lock-alert .content {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}
.lock-alert .icon { font-size: 28px; }
.lock-alert .title {
    font-weight: 900;
    color: #78350f;
    font-size: 14px;
}
.lock-alert .desc {
    font-size: 12px;
    color: #92400e;
    margin-top: 3px;
    line-height: 1.5;
}
.lock-alert .hint {
    font-size: 11px;
    color: #a16207;
    margin-top: 5px;
    font-style: italic;
}

/* ─── CALENDRIER ─── */
.calendar-wrap {
    background: white;
    border-radius: 14px;
    box-shadow: 0 3px 18px rgba(0,0,0,0.07);
    padding: 18px;
    margin-bottom: 18px;
    max-width: 700px;
    margin-left: auto;
    margin-right: auto;
}

.calendar-nav {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 14px;
    padding-bottom: 12px;
    border-bottom: 2px solid #e2e8f0;
}
.calendar-nav .month-title {
    font-size: 16px;
    font-weight: 900;
    color: #1e3a5f;
    letter-spacing: -0.4px;
    display: flex; align-items: center; gap: 8px;
}
.calendar-nav .nav-btns {
    display: flex; gap: 6px;
}
.btn-nav {
    background: #f1f5f9;
    color: #1e3a5f;
    border: none;
    border-radius: 8px;
    padding: 7px 12px;
    font-size: 12px;
    font-weight: 800;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex; align-items: center; gap: 4px;
    text-decoration: none;
}
.btn-nav:hover {
    background: #1d4ed8;
    color: white;
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(29,78,216,0.3);
}
.btn-nav.today {
    background: linear-gradient(135deg, #7c3aed, #6d28d9);
    color: white;
}

/* Grille du calendrier */
.calendar-grid {
    display: grid;
    grid-template-columns: repeat(7, 1fr);
    gap: 5px;
}
.calendar-head {
    padding: 6px 4px;
    text-align: center;
    font-size: 10px;
    font-weight: 900;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.calendar-head.weekend { color: #dc2626; }

.calendar-cell {
    aspect-ratio: 1 / 0.9;
    border-radius: 8px;
    padding: 5px 6px;
    background: #f8fafc;
    border: 2px solid transparent;
    cursor: pointer;
    transition: all 0.15s;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    position: relative;
    text-decoration: none;
    color: inherit;
}
.calendar-cell:hover:not(.empty):not(.locked) {
    transform: translateY(-2px);
    box-shadow: 0 5px 14px rgba(0,0,0,0.12);
    border-color: #1d4ed8;
    background: #eff6ff;
}
.calendar-cell.empty {
    background: transparent;
    cursor: default;
}

.calendar-cell .day-num {
    font-size: 13px;
    font-weight: 900;
    color: #1e3a5f;
    line-height: 1;
    display: flex;
    align-items: center;
    gap: 4px;
}
.calendar-cell.today .day-num {
    color: #7c3aed;
}
.calendar-cell.today::before {
    content: '•';
    position: absolute;
    top: 3px; right: 6px;
    font-size: 15px;
    color: #7c3aed;
    font-weight: 900;
    line-height: 1;
}

/* Badge activité */
.calendar-cell .activity {
    display: flex;
    flex-wrap: wrap;
    gap: 2px;
    margin-top: auto;
}
.act-pill {
    font-size: 8px;
    font-weight: 800;
    padding: 1px 4px;
    border-radius: 4px;
    line-height: 1.3;
}
.act-pill.attente { background: #fef3c7; color: #92400e; }
.act-pill.accepte { background: #dcfce7; color: #166534; }
.act-pill.refuse  { background: #fee2e2; color: #991b1b; }

/* Cellule sélectionnée */
.calendar-cell.selected {
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
    border-color: #1e40af;
    box-shadow: 0 6px 16px rgba(29,78,216,0.4);
}
.calendar-cell.selected .day-num { color: white; }
.calendar-cell.selected .act-pill { background: rgba(255,255,255,0.25); color: white; }
.calendar-cell.selected::after {
    content: '✓';
    position: absolute;
    top: 3px; right: 4px;
    width: 15px; height: 15px;
    background: white;
    color: #1d4ed8;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 9px; font-weight: 900;
}

/* Badge "vide" */
.calendar-cell.vide {
    opacity: 0.6;
}
.calendar-cell.vide .day-num { color: #94a3b8; }

/* Weekend */
.calendar-cell.weekend {
    background: #fef2f2;
}
.calendar-cell.weekend .day-num { color: #dc2626; }

/* ─── 🔒 CELLULE VERROUILLÉE ─── */
.calendar-cell.locked {
    background: repeating-linear-gradient(
        45deg,
        #f1f5f9,
        #f1f5f9 6px,
        #e2e8f0 6px,
        #e2e8f0 12px
    );
    border: 2px dashed #cbd5e1;
    cursor: not-allowed;
    opacity: 0.7;
    position: relative;
}
.calendar-cell.locked:hover {
    opacity: 0.9;
    border-color: #f59e0b;
    background: repeating-linear-gradient(
        45deg,
        #fef3c7,
        #fef3c7 6px,
        #fde68a 6px,
        #fde68a 12px
    );
    transform: none;
    box-shadow: none;
}
.calendar-cell.locked .day-num {
    color: #94a3b8;
    text-decoration: line-through;
}
.calendar-cell.locked .lock-icon-small {
    font-size: 10px;
    text-decoration: none;
    opacity: 0.75;
    filter: grayscale(0.4);
}
.calendar-cell.locked .act-pill {
    filter: grayscale(0.7);
    opacity: 0.75;
    border: 1px dashed rgba(0,0,0,0.15);
}
.calendar-cell.locked:hover .lock-icon-small {
    animation: shakeLock 0.6s ease;
}

@keyframes shakeLock {
    0%, 100% { transform: rotate(0); }
    20%      { transform: rotate(-12deg); }
    40%      { transform: rotate(12deg); }
    60%      { transform: rotate(-8deg); }
    80%      { transform: rotate(8deg); }
}

/* ─── RÉCAP SÉLECTION ─── */
.selection-card {
    background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border-left: 4px solid #16a34a;
    border-radius: 12px;
    padding: 14px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
    box-shadow: 0 3px 12px rgba(22,163,74,0.13);
}
.selection-card .label {
    font-size: 10px;
    color: #166534;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    margin-bottom: 2px;
}
.selection-card .date-val {
    font-size: 17px;
    font-weight: 900;
    color: #14532d;
    letter-spacing: -0.3px;
}
.selection-card .stats-mini {
    display: flex; gap: 6px; flex-wrap: wrap;
    margin-top: 5px;
}
.selection-card .mini-pill {
    font-size: 10px;
    font-weight: 800;
    padding: 2px 8px;
    border-radius: 8px;
    background: white;
    color: #166534;
    border: 1px solid #86efac;
}
.btn-valider {
    background: linear-gradient(135deg, #16a34a, #15803d);
    color: white;
    border: none;
    border-radius: 10px;
    padding: 11px 20px;
    font-size: 13px;
    font-weight: 900;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 5px 14px rgba(22,163,74,0.35);
    text-decoration: none;
}
.btn-valider:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(22,163,74,0.5);
    color: white;
}

.empty-selection {
    background: #f8fafc;
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    padding: 18px;
    text-align: center;
    color: #64748b;
    font-size: 12.5px;
    font-weight: 600;
}

/* ═══════════════════════════════════════════════════════════════ */
/* 🎯 TOAST CENTRÉ AU MILIEU (verrouillage)                        */
/* ═══════════════════════════════════════════════════════════════ */
.toast-locked-overlay {
    position: fixed;
    inset: 0;
    background: rgba(15, 23, 42, 0.45);
    backdrop-filter: blur(3px);
    -webkit-backdrop-filter: blur(3px);
    z-index: 99998;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: overlayFadeIn 0.25s ease;
}
.toast-locked-overlay.fade-out {
    animation: overlayFadeOut 0.3s ease forwards;
}

.toast-locked {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    color: white;
    padding: 24px 28px;
    border-radius: 16px;
    box-shadow: 0 25px 60px rgba(220,38,38,0.5),
                0 10px 25px rgba(0,0,0,0.3);
    max-width: 440px;
    width: calc(100% - 40px);
    display: flex;
    align-items: flex-start;
    gap: 16px;
    animation: popInLocked 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    border-left: 6px solid #fbbf24;
    position: relative;
    overflow: hidden;
    text-align: left;
}
.toast-locked.fade-out {
    animation: popOutLocked 0.3s ease forwards;
}
.toast-locked .toast-icon {
    font-size: 38px;
    line-height: 1;
    flex-shrink: 0;
    filter: drop-shadow(0 3px 6px rgba(0,0,0,0.3));
    animation: shakeLock 0.6s ease;
}
.toast-locked .toast-body {
    flex: 1;
    min-width: 0;
}
.toast-locked .toast-title {
    font-weight: 900;
    font-size: 17px;
    letter-spacing: 0.3px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.toast-locked .toast-message {
    font-size: 13.5px;
    line-height: 1.6;
    opacity: 0.96;
}
.toast-locked .toast-message strong {
    background: rgba(255,255,255,0.22);
    padding: 2px 8px;
    border-radius: 6px;
    font-weight: 900;
    white-space: nowrap;
}
.toast-locked .toast-stats {
    margin-top: 12px;
    padding-top: 12px;
    border-top: 1px solid rgba(255,255,255,0.2);
    font-size: 12px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: 6px;
}
.toast-locked .toast-stats .label {
    font-weight: 800;
    opacity: 0.9;
    margin-right: 4px;
}
.toast-locked .toast-stats .stat-pill {
    padding: 3px 10px;
    border-radius: 8px;
    font-weight: 900;
    font-size: 11.5px;
}
.toast-locked .toast-stats .stat-pill.attente {
    background: rgba(251,191,36,0.35);
    color: #fef3c7;
}
.toast-locked .toast-stats .stat-pill.accepte {
    background: rgba(34,197,94,0.35);
    color: #dcfce7;
}
.toast-locked .toast-stats .stat-pill.refuse {
    background: rgba(239,68,68,0.4);
    color: #fee2e2;
}
.toast-locked .toast-close {
    background: rgba(255,255,255,0.18);
    border: none;
    color: white;
    width: 26px;
    height: 26px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 13px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: 0.2s;
}
.toast-locked .toast-close:hover {
    background: rgba(255,255,255,0.35);
    transform: rotate(90deg);
}
.toast-locked .toast-progress {
    position: absolute;
    bottom: 0;
    left: 0;
    height: 4px;
    background: #fbbf24;
    border-radius: 0 0 16px 16px;
    animation: progressBar 3s linear forwards;
}

@keyframes overlayFadeIn {
    from { opacity: 0; }
    to   { opacity: 1; }
}
@keyframes overlayFadeOut {
    from { opacity: 1; }
    to   { opacity: 0; }
}
@keyframes popInLocked {
    from {
        transform: scale(0.7) translateY(30px);
        opacity: 0;
    }
    to {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
}
@keyframes popOutLocked {
    from {
        transform: scale(1) translateY(0);
        opacity: 1;
    }
    to {
        transform: scale(0.85) translateY(-20px);
        opacity: 0;
    }
}
@keyframes progressBar {
    from { width: 100%; }
    to   { width: 0%; }
}

/* ─── RESPONSIVE ─── */
@media (max-width: 768px) {
    .choix-header { padding: 16px; }
    .choix-header h2 { font-size: 17px; }
    .choix-header::after { font-size: 55px; right: 12px; }
    .calendar-wrap { padding: 10px; max-width: 100%; }
    .calendar-grid { gap: 3px; }
    .calendar-cell { padding: 3px; border-radius: 6px; }
    .calendar-cell .day-num { font-size: 11px; }
    .calendar-nav .month-title { font-size: 13px; }
    .btn-nav { padding: 5px 8px; font-size: 10px; }
    .selection-card { padding: 10px 14px; }
    .selection-card .date-val { font-size: 14px; }
    .btn-valider { padding: 9px 14px; font-size: 11px; }
    .act-pill { font-size: 7px; padding: 1px 3px; }
    .lock-alert .title { font-size: 12px; }
    .lock-alert .desc { font-size: 11px; }
}

@media (max-width: 480px) {
    .toast-locked {
        padding: 20px 22px;
        max-width: calc(100% - 32px);
    }
    .toast-locked .toast-title { font-size: 15px; }
    .toast-locked .toast-message { font-size: 12.5px; }
    .toast-locked .toast-icon { font-size: 32px; }
}
</style>

<div class="choix-wrap">

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- EN-TÊTE                                                  --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="choix-header">
        <h2>📅 Choisir une date d'implantation</h2>
        <div class="sub">
            Sélectionnez directement une date dans le calendrier. Vous pourrez ensuite programmer les affectations correspondantes.
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- 🔒 ALERTE VERROUILLAGE                                   --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if(isset($dateVerrouillage) && $dateVerrouillage)
    <div class="lock-alert">
        <div class="content">
            <div class="icon">🔒</div>
            <div style="flex:1;min-width:200px;">
                <div class="title">
                    Dates futures verrouillées
                </div>
                <div class="desc">
                    <strong>{{ $nbEnAttente }} affectation(s)</strong> non terminée(s) sur
                    {{ count($datesEnAttente) }} date(s) antérieure(s) :
                    <strong>{{ implode(', ', $datesEnAttente) }}</strong>
                </div>
                <div class="hint">
                    💡 Traitez d'abord ces affectations pour débloquer les dates suivantes.
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- STATS GLOBALES                                           --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @php
        $totalDates   = collect($calendrier)->flatten(1)->filter()->count();
        $totalAffect  = collect($calendrier)->flatten(1)->filter()->sum('total');
        $totalAttente = collect($calendrier)->flatten(1)->filter()->sum('en_attente');
        $totalAccepte = collect($calendrier)->flatten(1)->filter()->sum('acceptees');
        $totalRefuse  = collect($calendrier)->flatten(1)->filter()->sum('refusees');
    @endphp

    <div class="global-stats">
        <div class="stat-card" style="border-left-color:#1d4ed8;">
            <div class="ico" style="background:#dbeafe;color:#1d4ed8;">📅</div>
            <div>
                <div class="lbl">Jours actifs</div>
                <div class="val">{{ $totalDates }}</div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#7c3aed;">
            <div class="ico" style="background:#ede9fe;color:#7c3aed;">📊</div>
            <div>
                <div class="lbl">Affectations</div>
                <div class="val">{{ $totalAffect }}</div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#f59e0b;">
            <div class="ico" style="background:#fef3c7;color:#f59e0b;">🟡</div>
            <div>
                <div class="lbl">En attente</div>
                <div class="val">{{ $totalAttente }}</div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#16a34a;">
            <div class="ico" style="background:#dcfce7;color:#16a34a;">✅</div>
            <div>
                <div class="lbl">Acceptées</div>
                <div class="val">{{ $totalAccepte }}</div>
            </div>
        </div>
        <div class="stat-card" style="border-left-color:#dc2626;">
            <div class="ico" style="background:#fee2e2;color:#dc2626;">❌</div>
            <div>
                <div class="lbl">Refusées</div>
                <div class="val">{{ $totalRefuse }}</div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- INFO                                                     --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="info-box">
        <div class="ico">💡</div>
        <div>
            <strong>Astuce :</strong> cliquez sur une date pour la sélectionner.
            Les pastilles <span style="background:#fef3c7;color:#92400e;padding:1px 6px;border-radius:6px;font-size:10px;font-weight:800;">⏳</span>,
            <span style="background:#dcfce7;color:#166534;padding:1px 6px;border-radius:6px;font-size:10px;font-weight:800;">✅</span> et
            <span style="background:#fee2e2;color:#991b1b;padding:1px 6px;border-radius:6px;font-size:10px;font-weight:800;">❌</span>
            indiquent le nombre d'affectations par statut. Les dates 🔒 sont verrouillées jusqu'à traitement des antérieures.
        </div>
    </div>

    {{-- Alertes --}}
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- CALENDRIER                                               --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="calendar-wrap">

        {{-- Navigation mois --}}
        <div class="calendar-nav">
            <div class="month-title">
                🗓️ {{ $moisLabel }}
            </div>
            <div class="nav-btns">
                <a href="{{ route('affectations.programmation-choix', ['mois' => $moisPrecedent]) }}"
                   class="btn-nav">
                    ← Précédent
                </a>
                <a href="{{ route('affectations.programmation-choix', ['mois' => now()->format('Y-m')]) }}"
                   class="btn-nav today">
                    📍 Aujourd'hui
                </a>
                <a href="{{ route('affectations.programmation-choix', ['mois' => $moisSuivant]) }}"
                   class="btn-nav">
                    Suivant →
                </a>
            </div>
        </div>

        {{-- En-têtes jours --}}
        <div class="calendar-grid">
            @foreach(['Lun','Mar','Mer','Jeu','Ven','Sam','Dim'] as $i => $jour)
                <div class="calendar-head {{ $i >= 5 ? 'weekend' : '' }}">{{ $jour }}</div>
            @endforeach

            {{-- Cellules --}}
            @foreach($calendrier as $semaine)
                @foreach($semaine as $cellule)
                    @if($cellule === null)
                        <div class="calendar-cell empty"></div>
                    @else
                        @php
                            $dateStr    = $cellule['date_str'];
                            $isToday    = $cellule['is_today'];
                            $isSelected = $cellule['is_selected'];
                            $isWeekend  = $cellule['is_weekend'];
                            $total      = $cellule['total'];
                            $enAttente  = $cellule['en_attente'];
                            $acceptees  = $cellule['acceptees'];
                            $refusees   = $cellule['refusees'];
                            $vide       = $total === 0;
                            $verrouillee = $cellule['verrouillee'] ?? false;
                        @endphp

                        @if($verrouillee)
                            {{-- 🔒 Cellule VERROUILLÉE (avec compteurs grisés) --}}
                            <div class="calendar-cell locked"
                                 title="🔒 Verrouillée — Terminez d'abord les affectations antérieures"
                                 onclick="showLockedWarning(
                                     '{{ \Carbon\Carbon::parse($dateStr)->format('d/m/Y') }}',
                                     {
                                         total: {{ $total }},
                                         en_attente: {{ $enAttente }},
                                         acceptees: {{ $acceptees }},
                                         refusees: {{ $refusees }}
                                     }
                                 )">
                                <div class="day-num">
                                    {{ $cellule['jour'] }}
                                    <span class="lock-icon-small">🔒</span>
                                </div>

                                @if($total > 0)
                                    <div class="activity">
                                        @if($enAttente > 0)
                                            <span class="act-pill attente">⏳ {{ $enAttente }}</span>
                                        @endif
                                        @if($acceptees > 0)
                                            <span class="act-pill accepte">✅ {{ $acceptees }}</span>
                                        @endif
                                        @if($refusees > 0)
                                            <span class="act-pill refuse">❌ {{ $refusees }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        @else
                            {{-- ✅ Cellule NORMALE --}}
                            <a href="{{ route('affectations.programmation-set-dimanche', ['dimanche' => $dateStr]) }}"
                               class="calendar-cell
                                      {{ $isToday ? 'today' : '' }}
                                      {{ $isSelected ? 'selected' : '' }}
                                      {{ $isWeekend ? 'weekend' : '' }}
                                      {{ $vide ? 'vide' : '' }}"
                               onclick="event.preventDefault(); document.getElementById('form-set-date-{{ $dateStr }}').submit();">

                                <div class="day-num">{{ $cellule['jour'] }}</div>

                                @if($total > 0)
                                    <div class="activity">
                                        @if($enAttente > 0)
                                            <span class="act-pill attente">⏳ {{ $enAttente }}</span>
                                        @endif
                                        @if($acceptees > 0)
                                            <span class="act-pill accepte">✅ {{ $acceptees }}</span>
                                        @endif
                                        @if($refusees > 0)
                                            <span class="act-pill refuse">❌ {{ $refusees }}</span>
                                        @endif
                                    </div>
                                @endif
                            </a>

                            {{-- Form caché pour la soumission --}}
                            <form id="form-set-date-{{ $dateStr }}"
                                  method="POST"
                                  action="{{ route('affectations.programmation-set-dimanche') }}"
                                  style="display:none;">
                                @csrf
                                <input type="hidden" name="dimanche" value="{{ $dateStr }}">
                            </form>
                        @endif
                    @endif
                @endforeach
            @endforeach
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- RÉCAP SÉLECTION                                          --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if($dateSelectionnee)
        @php
            $selDate  = \Carbon\Carbon::parse($dateSelectionnee);
            $infoSel  = collect($calendrier)->flatten(1)->filter()->firstWhere('date_str', $dateSelectionnee);
        @endphp

        <div class="selection-card">
            <div>
                <div class="label">📌 Date sélectionnée</div>
                <div class="date-val">
                    {{ $selDate->translatedFormat('l d F Y') }}
                </div>
                @if($infoSel && $infoSel['total'] > 0)
                    <div class="stats-mini">
                        <span class="mini-pill">📊 {{ $infoSel['total'] }} affectation(s)</span>
                        @if($infoSel['en_attente'] > 0)
                            <span class="mini-pill">⏳ {{ $infoSel['en_attente'] }} en attente</span>
                        @endif
                        @if($infoSel['acceptees'] > 0)
                            <span class="mini-pill">✅ {{ $infoSel['acceptees'] }} acceptée(s)</span>
                        @endif
                        @if($infoSel['refusees'] > 0)
                            <span class="mini-pill">❌ {{ $infoSel['refusees'] }} refusée(s)</span>
                        @endif
                    </div>
                @endif
            </div>

            <a href="{{ route('affectations.programmation') }}" class="btn-valider">
                🚀 Continuer vers la programmation
            </a>
        </div>
    @else
        <div class="empty-selection">
            👆 Cliquez sur une date dans le calendrier pour commencer
        </div>
    @endif

</div>

@endsection

@section('scripts')
<script>
// ════════════════════════════════════════════════════════════════
// 🔒 TOAST CENTRÉ POUR DATE VERROUILLÉE (3 secondes)
// ════════════════════════════════════════════════════════════════
function showLockedWarning(dateStr, stats = null) {
    // Supprimer tout overlay existant
    document.querySelectorAll('.toast-locked-overlay').forEach(el => el.remove());

    // Construction du bloc stats (si données fournies)
    let statsHTML = '';
    if (stats && stats.total > 0) {
        statsHTML = `
            <div class="toast-stats">
                <span class="label">📊 Activité en cours :</span>
                ${stats.en_attente > 0 ? `<span class="stat-pill attente">⏳ ${stats.en_attente}</span>` : ''}
                ${stats.acceptees > 0 ? `<span class="stat-pill accepte">✅ ${stats.acceptees}</span>` : ''}
                ${stats.refusees > 0 ? `<span class="stat-pill refuse">❌ ${stats.refusees}</span>` : ''}
            </div>
        `;
    }

    // Créer l'overlay qui contient la carte
    const overlay = document.createElement('div');
    overlay.className = 'toast-locked-overlay';

    const card = document.createElement('div');
    card.className = 'toast-locked';
    card.innerHTML = `
        <div class="toast-icon">🔒</div>
        <div class="toast-body">
            <div class="toast-title">
                Date verrouillée
            </div>
            <div class="toast-message">
                La date du <strong>${dateStr}</strong> ne peut pas encore être sélectionnée.
                Terminez d'abord les affectations antérieures.
            </div>
            ${statsHTML}
        </div>
        <button class="toast-close" title="Fermer">✕</button>
        <div class="toast-progress"></div>
    `;

    overlay.appendChild(card);
    document.body.appendChild(overlay);

    // Fonction de fermeture
    const close = () => {
        card.classList.add('fade-out');
        overlay.classList.add('fade-out');
        setTimeout(() => overlay.remove(), 300);
    };

    // Bouton fermer
    card.querySelector('.toast-close').addEventListener('click', close);

    // Clic sur l'overlay (hors carte) → ferme
    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) close();
    });

    // Touche Échap → ferme
    const onEsc = (e) => {
        if (e.key === 'Escape') {
            close();
            document.removeEventListener('keydown', onEsc);
        }
    };
    document.addEventListener('keydown', onEsc);

    // Auto-fermeture après 3 secondes
    setTimeout(() => {
        if (document.body.contains(overlay)) {
            close();
        }
    }, 3000);
}
</script>
@endsection