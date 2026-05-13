@extends('admin.layout')
@section('content')

<style>
.dashboard-card {
    border-radius:12px; padding:14px; background:white;
    box-shadow:0 4px 16px rgba(0,0,0,0.06); transition:0.2s; height:100%;
}
.dashboard-card:hover { transform:translateY(-2px); }
.kpi h5 { font-size:12px; font-weight:600; opacity:0.85; margin-bottom:4px; }
.kpi h2 { font-size:20px; font-weight:800; margin:0; }
.kpi small { font-size:11px; opacity:0.75; margin-top:3px; display:block; }
.filter-bar {
    background:white; padding:12px 16px; border-radius:12px;
    margin-bottom:20px; display:flex; gap:8px; flex-wrap:wrap; align-items:center;
}
.filter-bar select { padding:6px 10px; border-radius:8px; border:1px solid #ddd; font-size:13px; }
.tf-title-short { max-width:90px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; display:inline-block; vertical-align:bottom; }
.activite-bar-wrap { height:10px; background:rgba(255,255,255,0.3); border-radius:5px; overflow:hidden; }
.activite-bar-fill { height:100%; background:rgba(255,255,255,0.9); border-radius:5px; }
.section-label { font-size:11px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px; padding-left:4px; }
</style>

<h2 class="mb-3">🌍 État général du site</h2>

{{-- ===== ÉTAT GLOBAL ===== --}}
<div class="section-label">📊 Vue globale (tous sites + lots + zones groupées)</div>
<div class="row g-2 mb-2 flex-nowrap overflow-auto">
    <div class="col">
        <div class="dashboard-card text-center bg-dark text-white kpi">
            <h5>🏡 Famille</h5>
            <h2>{{ $global_famille }}</h2>
            <small>{{ number_format($global_superficie_famille, 0, ',', ' ') }} m²</small>
        </div>
    </div>
    <div class="col">
        <div class="dashboard-card text-center bg-primary text-white kpi">
            <h5>🔵 EDEN</h5>
            <h2>{{ $global_eden }}</h2>
            <small>{{ number_format($global_superficie_eden, 0, ',', ' ') }} m²</small>
        </div>
    </div>
    <div class="col">
        <div class="dashboard-card text-center kpi" style="background:#1e3a5f;color:white;">
            <h5>📊 Activité globale</h5>
            <h2>{{ $global_activite_pct }}%</h2>
            <div class="activite-bar-wrap mt-1">
                <div class="activite-bar-fill" style="width:{{ $global_activite_pct }}%;"></div>
            </div>
        </div>
    </div>
</div>
<div class="row g-2 mb-4 flex-nowrap overflow-auto">
    <div class="col">
        <div class="dashboard-card text-center bg-secondary text-white kpi">
            <h5>⏳ Implant. prévue</h5>
            <h2>{{ $global_implantation_prevue }}</h2>
            <small>{{ number_format($global_superficie_implantation, 0, ',', ' ') }} m²</small>
        </div>
    </div>
    <div class="col">
        <div class="dashboard-card text-center bg-success text-white kpi">
            <h5>✅ Déjà implanté</h5>
            <h2>{{ $global_deja_implante }}</h2>
            <small>{{ number_format($global_superficie_deja, 0, ',', ' ') }} m²</small>
        </div>
    </div>
    <div class="col">
        <div class="dashboard-card text-center bg-danger text-white kpi">
            <h5>📁 Dossier tech.</h5>
            <h2>{{ $global_dossier_technique }}</h2>
            <small>{{ number_format($global_superficie_dossier, 0, ',', ' ') }} m²</small>
        </div>
    </div>
    <div class="col">
        <div class="dashboard-card text-center kpi" style="background:#fd7e14;color:white;">
            <h5>✂️ Morcellement</h5>
            <h2>{{ $global_morcellement }}</h2>
            <small>{{ number_format($global_superficie_morcellement, 0, ',', ' ') }} m²</small>
        </div>
    </div>
</div>

{{-- ===== FILTRES ===== --}}
<div class="filter-bar">
    <select onchange="applyFilter('grand_site', this.value)">
        <option value="">🏢 Tous les grands sites</option>
        @foreach($grandsites as $gs)
            <option value="{{ $gs->id }}" {{ $grandSiteId == $gs->id ? 'selected' : '' }}>{{ $gs->nom }}</option>
        @endforeach
    </select>

    <select onchange="applyFilter('site', this.value)">
        <option value="">🗺️ Tous les sites</option>
        @foreach($sites as $s)
            <option value="{{ $s->id }}" {{ $siteId == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
    </select>

    <select onchange="applyFilter('tf', this.value)">
        <option value="">🧭 Tous les TF</option>
        @foreach($tfs as $tf)
            <option value="{{ $tf->id }}" {{ $tfId == $tf->id ? 'selected' : '' }}>{{ Str::limit($tf->title, 25) }}</option>
        @endforeach
    </select>

    <select onchange="applyFilter('block', this.value)">
        <option value="">🔤 Tous les blocs</option>
        @foreach($blocksList as $letter)
            <option value="{{ $letter }}" {{ $block == $letter ? 'selected' : '' }}>Bloc {{ $letter }}</option>
        @endforeach
    </select>

    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-dark">Reset</a>
</div>

<div class="section-label">📍 Résultat filtré (lots + zones groupées)</div>

{{-- ===== RÉSULTATS FILTRÉS ===== --}}
<div class="row g-2 mb-2 flex-nowrap overflow-auto">
    <div class="col">
        <div class="dashboard-card text-center bg-dark text-white kpi">
            <h5>🏡 Famille</h5>
            <h2>{{ $lots->where('origine','famille')->count() }}</h2>
            <small>{{ number_format($superficie_famille, 0, ',', ' ') }} m²</small>
        </div>
    </div>
    <div class="col">
        <div class="dashboard-card text-center bg-primary text-white kpi">
            <h5>🔵 EDEN</h5>
            <h2>{{ $lots_eden_sans_type }}</h2>
            <small>{{ number_format($superficie_eden, 0, ',', ' ') }} m²</small>
        </div>
    </div>
    <div class="col">
        <div class="dashboard-card kpi text-center">
            <h5>🧱 Total</h5>
            <h2>{{ $lots_count }}</h2>
            <small style="color:#64748b;">lots</small>
        </div>
    </div>
    <div class="col">
        <div class="dashboard-card text-center kpi" style="background:#1e3a5f;color:white;">
            <h5>📊 Activité</h5>
            <h2>{{ $activite_filtree_pct }}%</h2>
            <div class="activite-bar-wrap mt-1">
                <div class="activite-bar-fill" style="width:{{ $activite_filtree_pct }}%;"></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-2 mb-4 flex-nowrap overflow-auto">
    <div class="col">
        <div class="dashboard-card text-center bg-secondary text-white kpi">
            <h5>⏳ Implant. prévue</h5>
            <h2>{{ $lots_implantation_prevue }}</h2>
            <small>{{ number_format($superficie_implantation_prevue, 0, ',', ' ') }} m²</small>
        </div>
    </div>
    <div class="col">
        <div class="dashboard-card text-center bg-success text-white kpi">
            <h5>✅ Déjà implanté</h5>
            <h2>{{ $lots_deja_implante }}</h2>
            <small>{{ number_format($superficie_deja_implante, 0, ',', ' ') }} m²</small>
        </div>
    </div>
    <div class="col">
        <div class="dashboard-card text-center bg-danger text-white kpi">
            <h5>📁 Dossier tech.</h5>
            <h2>{{ $lots_dossier_technique }}</h2>
            <small>{{ number_format($superficie_dossier, 0, ',', ' ') }} m²</small>
        </div>
    </div>
    <div class="col">
        <div class="dashboard-card text-center kpi" style="background:#fd7e14;color:white;">
            <h5>✂️ Morcellement</h5>
            <h2>{{ $lots_morcellement }}</h2>
            <small>{{ number_format($superficie_morcellement, 0, ',', ' ') }} m²</small>
        </div>
    </div>
</div>

{{-- ===== TABLEAU LOTS ===== --}}
<div class="dashboard-card mt-2">
    <h5>📌 Lots</h5>
    <table class="table table-hover table-sm mt-3" style="font-size:13px;">
        <thead class="table-dark">
            <tr>
                <th>CODE</th>
                <th>GRAND SITE</th>
                <th>SITE</th>
                <th>TF</th>
                <th>ORIGINE</th>
                <th>TYPE</th>
                <th>CLIENT</th>
                <th>SUPERFICIE</th>
            </tr>
        </thead>
        <tbody>
        @foreach($lots as $lot)
            <tr>
                <td><strong>{{ strtoupper($lot->code) }}</strong></td>
                <td>{{ $lot->tf?->site?->grandSite?->nom ?? '-' }}</td>
                <td>{{ $lot->tf?->site?->name ?? '-' }}</td>
                <td>
                    <span class="tf-title-short" title="{{ $lot->tf?->title }}">{{ $lot->tf?->title ?? '-' }}</span>
                </td>
                <td>
                    <span class="badge {{ $lot->origine === 'eden' ? 'bg-primary' : 'bg-dark' }}">
                        {{ $lot->origine ?? '-' }}
                    </span>
                </td>
                <td>
                    @if($lot->type)
                        <span class="badge
                            @if($lot->type === 'deja_implante') bg-success
                            @elseif($lot->type === 'implantation_prevue') bg-secondary
                            @elseif($lot->type === 'dossier_technique') bg-danger
                            @elseif($lot->type === 'morcellement') bg-warning text-dark
                            @endif">
                            {{ str_replace('_', ' ', $lot->type) }}
                        </span>
                    @else
                        <span class="text-muted">-</span>
                    @endif
                </td>
                <td>{{ $lot->owner_name ?? '-' }}</td>
                <td>{{ $lot->superficie ? number_format($lot->superficie, 0, ',', ' ') . ' m²' : '-' }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

@endsection
@section('scripts')
<script>
function applyFilter(type, value) {
    const url = new URL(window.location.href);
    if (value) url.searchParams.set(type, value);
    else url.searchParams.delete(type);
    if (type === 'grand_site') { url.searchParams.delete('site'); url.searchParams.delete('tf'); url.searchParams.delete('block'); }
    if (type === 'site')       { url.searchParams.delete('tf'); url.searchParams.delete('block'); }
    if (type === 'tf')         { url.searchParams.delete('block'); }
    window.location.href = url.toString();
}
</script>
@endsection