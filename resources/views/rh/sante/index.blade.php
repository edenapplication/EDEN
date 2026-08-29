@extends('rh.layout')
@section('content')

<style>
.kpi-card { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.06); text-align:center; height:100%; }
.kpi-card .v { font-size:24px; font-weight:800; }
.kpi-card .l { font-size:10px; color:#64748b; font-weight:600; text-transform:uppercase; margin-top:4px; }
.kpi-card .s { font-size:11px; color:#94a3b8; margin-top:4px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🏥 Santé & Sécurité</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.sante.visites.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Nouvelle visite
        </a>
        <a href="{{ route('rh.sante.accidents.create') }}" class="btn btn-danger btn-sm">
            <i class="bi bi-plus-circle"></i> Déclarer accident
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #1d4ed8;">
            <div class="v" style="color:#1d4ed8;">{{ $stats['visites_total'] }}</div>
            <div class="l">Total visites</div>
            <div class="s">{{ $stats['visites_effectuees'] }} effectuées</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #f59e0b;">
            <div class="v" style="color:#f59e0b;">{{ $stats['visites_planifiees'] }}</div>
            <div class="l">Visites planifiées</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #dc2626;">
            <div class="v" style="color:#dc2626;">{{ $stats['visites_expirees'] }}</div>
            <div class="l">Visites expirées</div>
            <div class="s">{{ $stats['visites_a_expirer'] }} à expirer (30j)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #7c3aed;">
            <div class="v" style="color:#7c3aed;">{{ $stats['accidents_en_cours'] }}</div>
            <div class="l">Accidents en cours</div>
            <div class="s">{{ $stats['accidents_total'] }} au total</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="kpi-card" style="border-top:3px solid #16a34a;text-align:left;padding:16px;">
            <div style="font-weight:700;color:#1e3a5f;margin-bottom:8px;">🧰 Trousses de secours</div>
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <div><span style="color:#16a34a;">✅ {{ $stats['trousses_ok'] }}</span> OK</div>
                <div><span style="color:#f59e0b;">⚠️ {{ $stats['trousses_alerte'] }}</span> Alerte</div>
                <div><span style="color:#dc2626;">❌ {{ $stats['trousses_ok'] - $stats['trousses_ok'] }}</span> Vide</div>
            </div>
            <div class="mt-2">
                <a href="{{ route('rh.sante.trousses') }}" class="btn btn-sm btn-outline-primary">Gérer les trousses</a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="kpi-card" style="border-top:3px solid #f59e0b;text-align:left;padding:16px;">
            <div style="font-weight:700;color:#1e3a5f;margin-bottom:8px;">📋 Actions rapides</div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="{{ route('rh.sante.visites') }}" class="btn btn-outline-primary btn-sm">🩺 Voir les visites</a>
                <a href="{{ route('rh.sante.accidents') }}" class="btn btn-outline-danger btn-sm">⚠️ Voir les accidents</a>
                <a href="{{ route('rh.sante.visites.create') }}" class="btn btn-outline-success btn-sm">➕ Nouvelle visite</a>
            </div>
        </div>
    </div>
</div>

{{-- DERNIÈRES VISITES --}}
<div class="card mb-3">
    <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
        🩺 Dernières visites médicales
    </div>
    <div class="card-body">
        @forelse($dernieresVisites as $v)
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <span style="font-weight:600;">{{ $v->employe?->nom }} {{ $v->employe?->prenom }}</span>
                    <span style="font-size:12px;color:#64748b;margin-left:8px;">{{ $v->type_label }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:12px;color:#64748b;">{{ $v->date_visite->format('d/m/Y') }}</span>
                    <span class="badge-status" style="background:{{ $v->statut_color }};color:#1e293b;font-size:9px;padding:2px 10px;border-radius:10px;">
                        {{ $v->statut_label }}
                    </span>
                    <a href="{{ route('rh.sante.visites.show', $v->id) }}" class="btn btn-sm btn-outline-primary">👁</a>
                </div>
            </div>
        @empty
            <div class="text-muted text-center py-3">Aucune visite enregistrée</div>
        @endforelse
    </div>
</div>

{{-- DERNIERS ACCIDENTS --}}
<div class="card">
    <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
        ⚠️ Derniers accidents de travail
    </div>
    <div class="card-body">
        @forelse($derniersAccidents as $a)
            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                <div>
                    <span style="font-weight:600;">{{ $a->employe?->nom }} {{ $a->employe?->prenom }}</span>
                    <span style="font-size:12px;color:#64748b;margin-left:8px;">{{ $a->lieu }}</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span style="font-size:12px;color:#64748b;">{{ $a->date_accident->format('d/m/Y') }}</span>
                    <span class="badge-status" style="background:{{ $a->statut_color }};color:#1e293b;font-size:9px;padding:2px 10px;border-radius:10px;">
                        {{ $a->statut_label }}
                    </span>
                    <a href="{{ route('rh.sante.accidents.show', $a->id) }}" class="btn btn-sm btn-outline-primary">👁</a>
                </div>
            </div>
        @empty
            <div class="text-muted text-center py-3">Aucun accident enregistré</div>
        @endforelse
    </div>
</div>

@endsection