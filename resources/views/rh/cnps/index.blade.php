@extends('rh.layout')
@section('content')

<style>
.kpi-card { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.06); text-align:center; height:100%; }
.kpi-card .v { font-size:24px; font-weight:800; }
.kpi-card .l { font-size:10px; color:#64748b; font-weight:600; text-transform:uppercase; margin-top:4px; }
.kpi-card .s { font-size:11px; color:#94a3b8; margin-top:4px; }
.statut-badge { padding:3px 10px; border-radius:8px; font-size:11px; font-weight:600; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🏛️ CNPS & Cotisations</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.cnps.declarations.create') }}" class="btn btn-primary btn-sm">+ Nouvelle déclaration</a>
        <a href="{{ route('rh.cnps.declarations') }}" class="btn btn-outline-secondary btn-sm">📋 Toutes les déclarations</a>
        <a href="{{ route('rh.cnps.affiliations') }}" class="btn btn-outline-info btn-sm">📋 Affiliations</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #1e3a5f;">
            <div class="v" style="color:#1e3a5f;">{{ $stats['total_employes'] }}</div>
            <div class="l">Total employés actifs</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #16a34a;">
            <div class="v" style="color:#16a34a;">{{ $stats['affilies'] }}</div>
            <div class="l">Affiliés CNPS</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #dc2626;">
            <div class="v" style="color:#dc2626;">{{ $stats['non_affilies'] }}</div>
            <div class="l">Non affiliés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:3px solid #f59e0b;">
            <div class="v" style="color:#f59e0b;">{{ $stats['sans_affiliation'] }}</div>
            <div class="l">Sans dossier CNPS</div>
            <div class="s"><a href="{{ route('rh.cnps.affiliations') }}" style="font-size:11px;">Gérer →</a></div>
        </div>
    </div>
</div>

{{-- DÉCLARATION DU MOIS --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:#1e3a5f;color:white;font-weight:700;">
                📅 Déclaration du mois : {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}
            </div>
            <div class="card-body">
                @if($declaration)
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div style="font-size:13px;font-weight:600;color:#1e3a5f;">
                                Réf : {{ $declaration->reference }}
                            </div>
                            <div style="font-size:12px;color:#64748b;">
                                Total CNPS : <strong style="color:#1d4ed8;">{{ number_format($declaration->total_cnps, 0, ',', ' ') }} FCFA</strong>
                            </div>
                            <div style="font-size:12px;color:#64748b;">
                                {{ $declaration->lignes->count() }} employés déclarés
                            </div>
                        </div>
                        <div class="text-end">
                            <span class="statut-badge" style="background:{{ $declaration->statut_color }};color:{{ $declaration->statut_text_color }};">
                                {{ $declaration->statut_label }}
                            </span>
                            <div class="mt-2">
                                <a href="{{ route('rh.cnps.declarations.show', $declaration->id) }}" class="btn btn-sm btn-primary">Voir</a>
                                <a href="{{ route('rh.cnps.declarations.dipe', $declaration->id) }}" class="btn btn-sm btn-danger">📄 DIPE</a>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center text-muted py-3">
                        <p>Aucune déclaration pour ce mois.</p>
                        <a href="{{ route('rh.cnps.declarations.create', ['periode' => $periode]) }}" class="btn btn-primary btn-sm">
                            + Créer la déclaration
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- DERNIÈRES DÉCLARATIONS --}}
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:#f1f5f9;font-weight:700;color:#1e3a5f;">
                📋 Dernières déclarations
            </div>
            <div class="card-body" style="max-height:200px;overflow-y:auto;">
                @forelse($dernieresDeclarations as $d)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <span style="font-weight:600;font-size:12px;">{{ $d->reference }}</span>
                            <span style="font-size:11px;color:#64748b;margin-left:8px;">{{ $d->mois_label }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <span class="statut-badge" style="background:{{ $d->statut_color }};color:{{ $d->statut_text_color }};font-size:9px;">
                                {{ $d->statut_label }}
                            </span>
                            <a href="{{ route('rh.cnps.declarations.show', $d->id) }}" class="btn btn-sm btn-outline-primary" style="font-size:9px;">👁</a>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">Aucune déclaration enregistrée</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- ÉVOLUTION DES COTISATIONS --}}
<div class="card">
    <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
        📈 Évolution des cotisations (6 mois)
    </div>
    <div class="card-body">
        <div style="display:flex;align-items:flex-end;gap:12px;height:180px;padding-top:10px;">
            @php $max = $evolution->max('total') ?: 1; @endphp
            @foreach($evolution as $e)
                @php $h = ($e['total'] / $max) * 150; @endphp
                <div style="flex:1;display:flex;flex-direction:column;align-items:center;gap:4px;">
                    <div style="font-size:9px;color:#64748b;font-weight:600;">
                        {{ $e['total'] > 0 ? number_format($e['total']/1000, 0) . 'k' : '-' }}
                    </div>
                    <div style="height:{{ max(4, $h) }}px;width:100%;background:linear-gradient(to top, #1d4ed8, #7c3aed);border-radius:4px 4px 0 0;position:relative;">
                        @if($e['statut'] !== 'Aucune')
                            <div style="position:absolute;top:-16px;right:-4px;font-size:7px;color:{{ $e['statut'] === 'Payé' ? '#16a34a' : '#f59e0b' }};">
                                {{ $e['statut'] === 'Payé' ? '✅' : '⏳' }}
                            </div>
                        @endif
                    </div>
                    <div style="font-size:8px;color:#64748b;text-align:center;max-width:50px;word-wrap:break-word;">{{ $e['mois'] }}</div>
                </div>
            @endforeach
        </div>
    </div>
</div>

{{-- ACTIONS RAPIDES --}}
<div class="row g-3 mt-3">
    <div class="col-md-12">
        <div class="card">
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('rh.cnps.declarations.create') }}" class="btn btn-primary btn-sm">📄 Nouvelle déclaration</a>
                    <a href="{{ route('rh.cnps.affiliations') }}" class="btn btn-info btn-sm text-white">📋 Gérer affiliations</a>
                    <a href="{{ route('rh.cnps.declarations', ['statut' => 'a_declarer']) }}" class="btn btn-warning btn-sm">⏳ Déclarations en attente</a>
                    <a href="{{ route('rh.cnps.declarations', ['statut' => 'paye']) }}" class="btn btn-success btn-sm">✅ Déclarations payées</a>
                    <a href="{{ route('rh.paie.recapitulatif') }}" class="btn btn-outline-secondary btn-sm">💰 Voir récapitulatif paie</a>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection