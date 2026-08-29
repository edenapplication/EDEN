@extends('rh.layout')
@section('content')

<style>
.dashboard-stat {
    background:white; border-radius:14px; padding:20px;
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    transition:transform 0.2s, box-shadow 0.2s;
    position:relative; overflow:hidden;
    height:100%;
}
.dashboard-stat:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,0.1); }
.dashboard-stat .icon { font-size:28px; opacity:0.12; position:absolute; right:16px; top:16px; }
.dashboard-stat .value { font-size:26px; font-weight:800; }
.dashboard-stat .label { font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; margin-top:2px; }
.dashboard-stat .trend { font-size:11px; font-weight:600; padding:2px 10px; border-radius:10px; display:inline-block; margin-top:4px; }
.trend-up { background:#dcfce7; color:#15803d; }
.trend-down { background:#fee2e2; color:#b91c1c; }

.section-card { background:white; border-radius:14px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,0.06); margin-bottom:20px; }
.section-card .section-title { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; display:flex; justify-content:space-between; align-items:center; }
.section-card .section-title .badge-count { background:#f1f5f9; color:#475569; font-size:11px; padding:2px 12px; border-radius:10px; }

.activity-item { display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:1px solid #f1f5f9; }
.activity-item:last-child { border-bottom:none; }
.activity-item .avatar { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:13px; color:white; flex-shrink:0; }
.activity-item .content { flex:1; font-size:13px; }
.activity-item .content .title { font-weight:600; color:#1e293b; }
.activity-item .content .sub { font-size:11px; color:#94a3b8; }
.activity-item .time { font-size:11px; color:#94a3b8; white-space:nowrap; }

/* ===== BADGES DE STATUT ===== */
.badge-status {
    padding:2px 10px;
    border-radius:10px;
    font-size:10px;
    font-weight:600;
    display:inline-block;
}
.badge-status.approuve { background:#dcfce7; color:#15803d; }
.badge-status.refuse { background:#fee2e2; color:#b91c1c; }
.badge-status.en_attente { background:#fef3c7; color:#92400e; }
.badge-status.actif { background:#dcfce7; color:#15803d; }
.badge-status.inactif { background:#fee2e2; color:#b91c1c; }
.badge-status.paye { background:#dcfce7; color:#15803d; }
.badge-status.brouillon { background:#f1f5f9; color:#475569; }
.badge-status.valide { background:#dbeafe; color:#1d4ed8; }

.progress-bar-custom { height:6px; background:#e2e8f0; border-radius:3px; overflow:hidden; margin-top:4px; }
.progress-bar-custom .fill { height:100%; border-radius:3px; transition:width 0.6s ease; }

.grid-3 { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
.grid-2 { display:grid; grid-template-columns:repeat(2,1fr); gap:16px; }

@media (max-width:768px) {
    .grid-3 { grid-template-columns:1fr; }
    .grid-2 { grid-template-columns:1fr; }
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="color:#1e3a5f; font-weight:800; margin:0;">📊 Dashboard RH</h2>
        <p style="color:#94a3b8; font-size:13px; margin:4px 0 0;">
            {{ now()->translatedFormat('l d F Y') }} — Vue d'ensemble des ressources humaines
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.employes.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus"></i> Nouvel employé
        </a>
        <a href="{{ route('rh.alertes.index') }}" class="btn btn-outline-secondary btn-sm position-relative">
            <i class="bi bi-bell"></i>
            @if($alertesNonLues > 0)
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size:9px;">
                    {{ $alertesNonLues > 99 ? '99+' : $alertesNonLues }}
                </span>
            @endif
        </a>
    </div>
</div>

{{-- ============================================================
     LIGNE 1 — EFFECTIFS
     ============================================================ --}}
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="dashboard-stat" style="border-top:4px solid #1d4ed8;">
            <div class="icon">👥</div>
            <div class="value" style="color:#1d4ed8;">{{ $totalEmployes }}</div>
            <div class="label">Employés actifs</div>
            <span class="trend trend-up">+{{ $nouveauxCeMois }} ce mois</span>
        </div>
    </div>
    <div class="col-md-2">
        <div class="dashboard-stat" style="border-top:4px solid #0891b2;">
            <div class="icon">👨</div>
            <div class="value" style="color:#0891b2;">{{ $totalHommes }}</div>
            <div class="label">Hommes</div>
            <div style="font-size:11px;color:#94a3b8;">{{ $totalEmployes > 0 ? round(($totalHommes / $totalEmployes) * 100) : 0 }}%</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="dashboard-stat" style="border-top:4px solid #db2777;">
            <div class="icon">👩</div>
            <div class="value" style="color:#db2777;">{{ $totalFemmes }}</div>
            <div class="label">Femmes</div>
            <div style="font-size:11px;color:#94a3b8;">{{ $totalEmployes > 0 ? round(($totalFemmes / $totalEmployes) * 100) : 0 }}%</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="dashboard-stat" style="border-top:4px solid #16a34a;">
            <div class="icon">📋</div>
            <div class="value" style="color:#16a34a;">{{ $totalCDI }}</div>
            <div class="label">CDI</div>
            <div style="font-size:11px;color:#94a3b8;">{{ $totalCDD }} CDD · {{ $totalStages }} Stages</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="dashboard-stat" style="border-top:4px solid #f59e0b;">
            <div class="icon">🆕</div>
            <div class="value" style="color:#f59e0b;">{{ $nouveauxCeMois }}</div>
            <div class="label">Entrées ce mois</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="dashboard-stat" style="border-top:4px solid #dc2626;">
            <div class="icon">🚪</div>
            <div class="value" style="color:#dc2626;">{{ $departs }}</div>
            <div class="label">Départs ce mois</div>
            <div style="font-size:11px;color:#94a3b8;">{{ $archives }} archivés au total</div>
        </div>
    </div>
</div>

{{-- ============================================================
     LIGNE 2 — PAIE & FINANCES
     ============================================================ --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="dashboard-stat" style="border-top:4px solid #0d6efd;">
            <div class="icon">💰</div>
            <div class="value" style="color:#0d6efd;font-size:20px;">{{ number_format($masseSalariale, 0, ',', ' ') }}</div>
            <div class="label">Masse salariale nette</div>
            <div style="font-size:11px;color:#94a3b8;">
                Brut: {{ number_format($masseBrute, 0, ',', ' ') }}
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-stat" style="border-top:4px solid #7c3aed;">
            <div class="icon">📄</div>
            <div class="value" style="color:#7c3aed;font-size:20px;">{{ $bulletinsTotal }}</div>
            <div class="label">Bulletins ce mois</div>
            <div style="font-size:11px;color:#94a3b8;">
                {{ $bulletinsValides }} validés · {{ $bulletinsPayes }} payés
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-stat" style="border-top:4px solid #f59e0b;">
            <div class="icon">🏦</div>
            <div class="value" style="color:#f59e0b;font-size:18px;">{{ number_format($pretsEnCours, 0, ',', ' ') }}</div>
            <div class="label">Prêts en cours</div>
            <div style="font-size:11px;color:#94a3b8;">
                {{ $pretsNb }} actifs · {{ $pretsRembourses }} remboursés
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-stat" style="border-top:4px solid #dc2626;">
            <div class="icon">⚠️</div>
            <div class="value" style="color:#dc2626;font-size:20px;">{{ number_format($sanctionsMois, 0, ',', ' ') }}</div>
            <div class="label">Sanctions ce mois</div>
            <div style="font-size:11px;color:#94a3b8;">
                {{ $sanctionsEnAttente }} en attente · {{ $sanctionsValides }} validées
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     LIGNE 3 — ABSENCES, RETARDS, CONGÉS
     ============================================================ --}}
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="dashboard-stat" style="border-top:4px solid #f59e0b;">
            <div class="icon">🗓️</div>
            <div class="value" style="color:#f59e0b;">{{ $absencesMois }}</div>
            <div class="label">Absences ce mois</div>
            <div style="font-size:11px;color:#94a3b8;display:flex;gap:12px;margin-top:4px;">
                <span style="color:#16a34a;">✅ {{ $absencesApprouvees }}</span>
                <span style="color:#f59e0b;">⏳ {{ $absencesEnAttente }}</span>
                <span style="color:#dc2626;">❌ {{ $absencesRefusees }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-stat" style="border-top:4px solid #dc2626;">
            <div class="icon">⏰</div>
            <div class="value" style="color:#dc2626;">{{ $retardsMois }}</div>
            <div class="label">Retards ce mois</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">
                {{ $totalMinutesRetard }} min de retard · {{ $totalMinutesSup }} min d'heures sup
            </div>
            <div style="font-size:11px;color:#94a3b8;">
                {{ $employesAvecRetards }} employés concernés
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="dashboard-stat" style="border-top:4px solid #1d4ed8;">
            <div class="icon">🏖️</div>
            <div class="value" style="color:#1d4ed8;">{{ $congesPlanifies + $congesEnCours }}</div>
            <div class="label">Congés en cours</div>
            <div style="font-size:11px;color:#94a3b8;display:flex;gap:12px;margin-top:4px;flex-wrap:wrap;">
                <span style="color:#f59e0b;">📅 {{ $congesPlanifies }}</span>
                <span style="color:#1d4ed8;">▶️ {{ $congesEnCours }}</span>
                <span style="color:#16a34a;">✅ {{ $congesTermines }}</span>
                <span style="color:#94a3b8;">🚫 {{ $congesAnnules }}</span>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     LIGNE 4 — CNPS, CONTRATS & ALERTES
     ============================================================ --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="dashboard-stat" style="border-top:4px solid #1d4ed8;">
            <div class="icon">🏛️</div>
            <div class="value" style="color:#1d4ed8;font-size:20px;">{{ number_format($cotisationsMois, 0, ',', ' ') }}</div>
            <div class="label">Cotisations CNPS</div>
            <div style="font-size:11px;color:#94a3b8;">
                {{ $affilies }} affiliés · {{ $nonAffilies }} non affiliés
            </div>
            <div style="font-size:11px;color:#f59e0b;">
                <a href="{{ route('rh.cnps.declarations', ['statut' => 'a_declarer']) }}" style="color:#f59e0b;">
                    {{ $declarationsEnAttente }} déclaration(s) en attente
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-stat" style="border-top:4px solid #16a34a;">
            <div class="icon">📄</div>
            <div class="value" style="color:#16a34a;">{{ $contratsActifs }}</div>
            <div class="label">Contrats actifs</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">
                {{ $contratsEnAttente }} en attente · {{ $contratsTermines }} terminés
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-stat" style="border-top:4px solid #ef4444;background:#fef2f2;">
            <div class="icon">⚠️</div>
            <div class="value" style="color:#ef4444;font-size:20px;">{{ $alertePeriodeEssai + $alerteFinCDD }}</div>
            <div class="label">Alertes contrats</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">
                <span style="color:#ef4444;">🔴 {{ $alertePeriodeEssai }} fin période d'essai</span><br>
                <span style="color:#f59e0b;">🟡 {{ $alerteFinCDD }} fin CDD (15j)</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="dashboard-stat" style="border-top:4px solid #7c3aed;">
            <div class="icon">🔔</div>
            <div class="value" style="color:#7c3aed;">{{ $alertesNonLues }}</div>
            <div class="label">Notifications non lues</div>
            <div style="font-size:11px;color:#94a3b8;margin-top:4px;">
                @if($alertesCritiques > 0)
                    <span style="color:#dc2626;">🔴 {{ $alertesCritiques }} critiques</span>
                @else
                    <span style="color:#16a34a;">✅ Aucune alerte critique</span>
                @endif
                <br>
                <a href="{{ route('rh.alertes.index') }}" style="font-size:11px;">Voir toutes les notifications →</a>
            </div>
        </div>
    </div>
</div>

{{-- ============================================================
     LIGNE 5 — GRAPHIQUES
     ============================================================ --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="section-card">
            <div class="section-title">
                <span>📈 Évolution masse salariale (12 mois)</span>
            </div>
            <canvas id="chartEvolutionPaie" height="180"></canvas>
        </div>
    </div>
    <div class="col-md-6">
        <div class="section-card">
            <div class="section-title">
                <span>📊 Répartition par direction</span>
            </div>
            <canvas id="chartRepartitionDirection" height="180"></canvas>
        </div>
    </div>
</div>

{{-- ============================================================
     LIGNE 6 — DERNIÈRES ACTIVITÉS
     ============================================================ --}}
<div class="row g-3">
    <div class="col-md-6">
        <div class="section-card">
            <div class="section-title">
                <span>🆕 Derniers employés</span>
                <a href="{{ route('rh.employes.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            @if($derniersEmployes->count() > 0)
                @foreach($derniersEmployes as $e)
                    <div class="activity-item">
                        <div class="avatar" style="background:linear-gradient(135deg,{{ $e->sexe === 'M' ? '#1d4ed8' : '#db2777' }},#7c3aed);">
                            {{ strtoupper(substr($e->prenom ?? '', 0, 1)) }}{{ strtoupper(substr($e->nom ?? '', 0, 1)) }}
                        </div>
                        <div class="content">
                            <div class="title">{{ $e->nom ?? '' }} {{ $e->prenom ?? '' }}</div>
                            <div class="sub">{{ $e->matricule ?? '-' }} · {{ $e->direction->nom ?? '-' }}</div>
                        </div>
                        <div class="time">{{ $e->created_at ? $e->created_at->diffForHumans() : '-' }}</div>
                    </div>
                @endforeach
            @else
                <div class="text-muted text-center py-3">Aucun employé récent</div>
            @endif
        </div>
    </div>

    <div class="col-md-6">
        <div class="section-card">
            <div class="section-title">
                <span>💰 Derniers bulletins</span>
                <a href="{{ route('rh.paie.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            @if($derniersBulletins->count() > 0)
                @foreach($derniersBulletins as $b)
                    <div class="activity-item">
                        <div class="avatar" style="background:linear-gradient(135deg,#16a34a,#0d9488);">
                            💰
                        </div>
                        <div class="content">
                            <div class="title">{{ $b->employe->nom ?? '' }} {{ $b->employe->prenom ?? '' }}</div>
                            <div class="sub">
                                {{ $b->employe->matricule ?? '' }} · 
                                {{ $b->periode ?? '' }} · 
                                <span style="font-weight:600;color:#1d4ed8;">
                                    {{ number_format($b->net_a_payer ?? 0, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                        </div>
                        <div class="time">
                            <span class="badge-status {{ $b->statut === 'payé' ? 'paye' : ($b->statut === 'validé' ? 'valide' : 'brouillon') }}">
                                {{ $b->statut ?? 'brouillon' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-muted text-center py-3">Aucun bulletin récent</div>
            @endif
        </div>
    </div>
</div>

{{-- LIGNE 7 — DERNIÈRES ABSENCES & ALERTES --}}
<div class="row g-3 mt-2">
    <div class="col-md-6">
        <div class="section-card">
            <div class="section-title">
                <span>🗓️ Dernières absences</span>
                <a href="{{ route('rh.absences.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            @if($dernieresAbsences->count() > 0)
                @foreach($dernieresAbsences as $a)
                    <div class="activity-item">
                        <div class="avatar" style="background:linear-gradient(135deg,#f59e0b,#d97706);">
                            📅
                        </div>
                        <div class="content">
                            <div class="title">{{ $a->employe->nom ?? '' }} {{ $a->employe->prenom ?? '' }}</div>
                            <div class="sub">{{ $a->type_absence ?? '-' }}</div>
                        </div>
                        <div class="time">
                            <span class="badge-status {{ $a->statut === 'approuvé' ? 'approuve' : ($a->statut === 'refusé' ? 'refuse' : 'en_attente') }}">
                                {{ $a->statut ?? 'en_attente' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-muted text-center py-3">Aucune absence récente</div>
            @endif
        </div>
    </div>

    <div class="col-md-6">
        <div class="section-card">
            <div class="section-title">
                <span>🔔 Dernières alertes</span>
                <a href="{{ route('rh.alertes.index') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            @if($dernieresAlertes->count() > 0)
                @foreach($dernieresAlertes as $a)
                    <div class="activity-item">
                        <div class="avatar" style="background:{{ $a->priorite_color ?? '#94a3b8' }};">
                            {{ substr($a->type ?? 'A', 0, 1) }}
                        </div>
                        <div class="content">
                            <div class="title">{{ $a->titre ?? 'Alerte' }}</div>
                            <div class="sub">
                                @if($a->employe)
                                    {{ $a->employe->nom ?? '' }} {{ $a->employe->prenom ?? '' }} · 
                                @endif
                                {{ Str::limit($a->message ?? '', 50) }}
                            </div>
                        </div>
                        <div class="time">
                            <span class="badge-status" style="background:{{ $a->priorite_color ?? '#94a3b8' }}22;color:{{ $a->priorite_color ?? '#94a3b8' }};font-size:9px;">
                                {{ $a->priorite_label ?? 'Normale' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            @else
                <div class="text-muted text-center py-3">Aucune alerte récente</div>
            @endif
        </div>
    </div>
</div>

{{-- ============================================================
     LIGNE 8 — ACTIONS RAPIDES
     ============================================================ --}}
<div class="section-card mt-3">
    <div class="section-title">
        <span>⚡ Actions rapides</span>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('rh.employes.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-person-plus"></i> Nouvel employé
        </a>
        <a href="{{ route('rh.paie.create') }}" class="btn btn-success btn-sm">
            <i class="bi bi-plus-circle"></i> Nouveau bulletin
        </a>
        <a href="{{ route('rh.absences.index') }}" class="btn btn-warning btn-sm">
            <i class="bi bi-calendar-plus"></i> Gérer absences
        </a>
        <a href="{{ route('rh.conges.index') }}" class="btn btn-info btn-sm text-white">
            <i class="bi bi-umbrella"></i> Planifier congé
        </a>
        <a href="{{ route('rh.prets.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-bank"></i> Prêts & acomptes
        </a>
        <a href="{{ route('rh.sanctions.index') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-exclamation-triangle"></i> Sanctions
        </a>
        <a href="{{ route('rh.cnps.declarations.create') }}" class="btn btn-sm text-white" style="background:#7c3aed;">
            <i class="bi bi-file-earmark-text"></i> Déclaration CNPS
        </a>
        <a href="{{ route('rh.contrats.create') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-plus"></i> Nouveau contrat
        </a>
        <a href="{{ route('rh.departs.create') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-box-arrow-right"></i> Nouveau départ
        </a>
        <a href="{{ route('rh.paie.recapitulatif') }}" class="btn btn-outline-info btn-sm">
            <i class="bi bi-bar-chart-fill"></i> Récapitulatif paie
        </a>
        <a href="{{ route('rh.retards.index') }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-clock"></i> Retards
        </a>
    </div>
</div>

{{-- ============================================================
     SCRIPTS
     ============================================================ --}}
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // ===== GRAPHIQUE ÉVOLUTION PAIE =====
    const ctx1 = document.getElementById('chartEvolutionPaie').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: @json($evolutionPaie->pluck('mois')),
            datasets: [
                {
                    label: 'Masse brute',
                    data: @json($evolutionPaie->pluck('brut')),
                    borderColor: '#7c3aed',
                    backgroundColor: 'rgba(124,58,237,0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                },
                {
                    label: 'Masse nette',
                    data: @json($evolutionPaie->pluck('net')),
                    borderColor: '#1d4ed8',
                    backgroundColor: 'rgba(29,78,216,0.1)',
                    fill: true,
                    tension: 0.4,
                    borderWidth: 2
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { font: { size: 10 } }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000) + 'M';
                            if (value >= 1000) return (value / 1000) + 'k';
                            return value;
                        }
                    }
                }
            }
        }
    });

    // ===== GRAPHIQUE RÉPARTITION PAR DIRECTION =====
    const ctx2 = document.getElementById('chartRepartitionDirection').getContext('2d');
    const colors = ['#1d4ed8', '#7c3aed', '#0891b2', '#16a34a', '#f59e0b', '#dc2626', '#8b5cf6', '#0d9488', '#e11d48', '#2563eb'];
    
    new Chart(ctx2, {
        type: 'doughnut',
        data: {
            labels: @json($parDirection->pluck('nom')),
            datasets: [{
                data: @json($parDirection->pluck('employes_count')),
                backgroundColor: colors.slice(0, {{ $parDirection->count() }}),
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { 
                        font: { size: 10 },
                        padding: 10
                    }
                }
            },
            cutout: '65%'
        }
    });

    // ===== AUTO-REFRESH DES NOTIFICATIONS =====
    setInterval(function() {
        fetch('{{ route("rh.alertes.count-non-lu") }}')
            .then(response => response.json())
            .then(data => {
                const badge = document.querySelector('.position-relative .badge');
                if (badge) {
                    if (data.count > 0) {
                        badge.textContent = data.count > 99 ? '99+' : data.count;
                        badge.style.display = 'block';
                    } else {
                        badge.style.display = 'none';
                    }
                }
            })
            .catch(() => {});
    }, 30000);
});
</script>
@endsection