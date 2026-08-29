@extends('rh.layout')
@section('content')

<style>
.statut-badge { padding:3px 10px; border-radius:8px; font-size:11px; font-weight:600; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">📋 Déclarations CNPS</h2>
    <a href="{{ route('rh.cnps.declarations.create') }}" class="btn btn-primary btn-sm">+ Nouvelle déclaration</a>
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
        <div class="kpi-box" style="background:white;border-radius:10px;padding:12px;text-align:center;border-top:3px solid #1e3a5f;">
            <div class="v" style="font-size:20px;font-weight:800;color:#1e3a5f;">{{ $stats['total'] }}</div>
            <div class="l" style="font-size:9px;color:#64748b;font-weight:600;text-transform:uppercase;">Total déclarations</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-box" style="background:white;border-radius:10px;padding:12px;text-align:center;border-top:3px solid #f59e0b;">
            <div class="v" style="font-size:20px;font-weight:800;color:#f59e0b;">{{ $stats['a_declarer'] }}</div>
            <div class="l" style="font-size:9px;color:#64748b;font-weight:600;text-transform:uppercase;">À déclarer</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-box" style="background:white;border-radius:10px;padding:12px;text-align:center;border-top:3px solid #1d4ed8;">
            <div class="v" style="font-size:20px;font-weight:800;color:#1d4ed8;">{{ $stats['declare'] }}</div>
            <div class="l" style="font-size:9px;color:#64748b;font-weight:600;text-transform:uppercase;">Déclarées</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-box" style="background:white;border-radius:10px;padding:12px;text-align:center;border-top:3px solid #16a34a;">
            <div class="v" style="font-size:20px;font-weight:800;color:#16a34a;">{{ $stats['paye'] }}</div>
            <div class="l" style="font-size:9px;color:#64748b;font-weight:600;text-transform:uppercase;">Payées</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" class="d-flex gap-2 mb-4 flex-wrap align-items-end" style="background:white;border-radius:12px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Période</label>
        <input type="month" name="periode" class="form-control form-control-sm" value="{{ request('periode') }}" style="width:150px;">
    </div>
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Statut</label>
        <select name="statut" class="form-control form-control-sm" style="width:150px;">
            <option value="">Tous</option>
            @foreach(\App\Models\RH\CnpsDeclaration::STATUTS as $k => $v)
                <option value="{{ $k }}" {{ request('statut')==$k?'selected':'' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">🔍</button>
    <a href="{{ route('rh.cnps.declarations') }}" class="btn btn-outline-secondary btn-sm">✖</a>
</form>

{{-- TABLEAU --}}
<div style="overflow-x:auto;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);background:white;">
<table class="table table-hover table-bordered mb-0" style="font-size:12px;">
    <thead class="table-dark">
        <tr>
            <th>Référence</th>
            <th>Période</th>
            <th>Employés</th>
            <th>Salaire soumis</th>
            <th>Cotisation salariale</th>
            <th>Cotisation patronale</th>
            <th>Total CNPS</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($declarations as $d)
        <tr>
            <td style="font-weight:700;color:#1d4ed8;">{{ $d->reference }}</td>
            <td>{{ $d->mois_label }}</td>
            <td>{{ $d->lignes->count() }}</td>
            <td>{{ number_format($d->total_salaire_soumis, 0, ',', ' ') }}</td>
            <td>{{ number_format($d->total_cotisation_salariale, 0, ',', ' ') }}</td>
            <td>{{ number_format($d->total_cotisation_patronale, 0, ',', ' ') }}</td>
            <td style="font-weight:700;color:#1d4ed8;">{{ number_format($d->total_cnps, 0, ',', ' ') }}</td>
            <td>
                <span class="statut-badge" style="background:{{ $d->statut_color }};color:{{ $d->statut_text_color }};">
                    {{ $d->statut_label }}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('rh.cnps.declarations.show', $d->id) }}" class="btn btn-sm btn-primary" title="Voir">👁</a>
                    <a href="{{ route('rh.cnps.declarations.dipe', $d->id) }}" class="btn btn-sm btn-danger" title="DIPE">📄</a>
                    @if($d->statut === 'a_declarer')
                        <form action="{{ route('rh.cnps.declarations.statut', $d->id) }}" method="POST" style="display:inline;">
                            @csrf
                            <input type="hidden" name="statut" value="declare">
                            <button class="btn btn-sm btn-success" title="Marquer comme déclaré">✅</button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="9" class="text-center text-muted py-4">Aucune déclaration enregistrée</td></tr>
    @endforelse
    </tbody>
</table>
</div>

<div class="mt-4">
    {{ $declarations->links() }}
</div>

@endsection