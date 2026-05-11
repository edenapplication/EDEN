@extends('admin.layout')
@section('content')

<style>
.dropdown-menu { z-index:9999; }
.prog-wrap { width:80px; height:8px; background:#eee; border-radius:10px; overflow:hidden; display:inline-block; vertical-align:middle; }
.prog-fill  { height:100%; background:#4D96FF; border-radius:10px; }
.filter-bar {
    background:white; padding:12px; border-radius:12px;
    margin-bottom:16px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;
}
.filter-bar select, .filter-bar input {
    padding:6px 10px; border-radius:8px; border:1px solid #ddd; font-size:13px;
}
</style>

<h2 class="mb-3">💰 Suivi des Dossiers</h2>

{{-- FILTRES --}}
<form method="GET" class="filter-bar">
    <select name="grand_site" onchange="this.form.submit()">
        <option value="">🏢 Tous les grands sites</option>
        @foreach($grandsites as $gs)
            <option value="{{ $gs->id }}" {{ request('grand_site') == $gs->id ? 'selected' : '' }}>{{ $gs->nom }}</option>
        @endforeach
    </select>

    <select name="site" onchange="this.form.submit()">
        <option value="">🗺️ Tous les sites</option>
        @foreach($sites as $s)
            <option value="{{ $s->id }}" {{ request('site') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
        @endforeach
    </select>

    <select name="tf" onchange="this.form.submit()">
        <option value="">🧭 Tous les TF</option>
        @foreach($tfs as $tf)
            <option value="{{ $tf->id }}" {{ request('tf') == $tf->id ? 'selected' : '' }}>{{ $tf->title }}</option>
        @endforeach
    </select>

    <select name="bloc" onchange="this.form.submit()">
        <option value="">🔤 Tous les blocs</option>
        @foreach($blocs as $b)
            <option value="{{ $b }}" {{ request('bloc') == $b ? 'selected' : '' }}>Bloc {{ $b }}</option>
        @endforeach
    </select>

    <input type="text" name="search" placeholder="🔍 Client (nom ou tél.)" value="{{ request('search') }}">

    <button type="submit" class="btn btn-sm btn-primary">Filtrer</button>
    <a href="{{ route('lots.vendus') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
</form>

<div class="card p-0 overflow-hidden">
<table class="table table-bordered table-hover mb-0" style="font-size:13px;">
    <thead class="table-dark">
        <tr>
            <th>GRAND SITE</th>
            <th>SITE</th>
            <th>TF</th>
            <th>LOT / ZONE</th>
            <th>CLIENT</th>
            <th>TÉLÉPHONE</th>
            <th>DOSSIER CLIENT</th>
            <th>LOTS DE LA ZONE</th>
            <th>AVANCEMENT</th>
            <th>ACTIONS</th>
        </tr>
    </thead>
    <tbody>

    {{-- ✅ LOTS avec client existant --}}
    @foreach($lots as $lot)
        @if($lot->client_id)
        @php
            $dossierClient = $lot->dossierClient ?? $lot->client?->dossiers->first();
            $progress      = $lot->dossierTechnique?->progression ?? 0;
            $progColor     = $progress < 40 ? '#dc3545' : ($progress < 75 ? '#fd7e14' : '#28a745');
        @endphp
        <tr>
            <td>{{ $lot->tf?->site?->grandSite?->nom ?? '---' }}</td>
            <td>{{ $lot->tf?->site?->name ?? '---' }}</td>
            <td style="max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $lot->tf?->title }}">
                {{ $lot->tf?->title ?? '---' }}
            </td>
            <td>
                <span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;">
                    📦 {{ strtoupper($lot->code) }}
                </span>
            </td>
            <td><strong>{{ $lot->client?->name ?? $lot->owner_name ?? '---' }}</strong></td>
            <td>{{ $lot->client?->phone ?? '---' }}</td>
            <td>
                @if($dossierClient)
                    <span style="font-size:12px;font-weight:600;color:#1e3a5f;">{{ $dossierClient->nom_dossier }}</span>
                @else
                    <span class="badge bg-warning text-dark" style="font-size:10px;">Aucun dossier</span>
                @endif
            </td>
            <td style="color:#64748b;font-size:11px;">—</td>
            <td>
                @if($lot->dossierTechnique)
                    <div class="d-flex align-items-center gap-1">
                        <div class="prog-wrap">
                            <div class="prog-fill" style="width:{{ $progress }}%;background:{{ $progColor }};"></div>
                        </div>
                        <small style="color:{{ $progColor }};font-weight:600;">{{ $progress }}%</small>
                    </div>
                @elseif($lot->client_id)
                    <span class="badge bg-secondary" style="font-size:10px;">Non démarré</span>
                @else
                    <span class="badge bg-light text-muted" style="font-size:10px;">—</span>
                @endif
            </td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">⋮</button>
                    <ul class="dropdown-menu">
                        @if($lot->dossierTechnique)
                            <li>
                                <a class="dropdown-item" href="{{ route('dossier.show', $lot->id) }}">
                                    📁 Dossier technique
                                </a>
                            </li>
                        @endif
                        @if($dossierClient)
                            <li>
                                <a class="dropdown-item" href="{{ route('suivi-client.show', $lot->client_id) }}">
                                    👤 Fiche client
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </td>
        </tr>
        @endif
    @endforeach

    {{-- ✅ ZONES GROUPES avec client existant --}}
    @foreach($zonesGroupes as $zg)
        @if($zg->client_id)
        @php
            $dossierClientZG = $zg->dossierClient ?? $zg->client?->dossiers->first();
            $progressZG      = $zg->dossierTechnique?->progression ?? 0;
            $progColorZG     = $progressZG < 40 ? '#dc3545' : ($progressZG < 75 ? '#fd7e14' : '#28a745');
            $lotsZone        = $lots->whereIn('id', $zg->lot_ids ?? []);
        @endphp
        <tr style="background:#fffbeb;">
            <td>{{ $zg->tf?->site?->grandSite?->nom ?? '---' }}</td>
            <td>{{ $zg->tf?->site?->name ?? '---' }}</td>
            <td style="max-width:100px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="{{ $zg->tf?->title }}">
                {{ $zg->tf?->title ?? '---' }}
            </td>
            <td>
                <span style="background:#fef9c3;color:#92400e;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;border:1px solid #f59e0b;">
                    🟡 {{ $zg->owner_name ?? $zg->nom ?? 'Zone #'.$zg->id }}
                </span>
                <div style="font-size:10px;color:#64748b;margin-top:2px;">
                    📐 {{ number_format($zg->superficie_totale ?? 0, 0, ',', ' ') }} m²
                </div>
            </td>
            <td><strong>{{ $zg->client?->name ?? '---' }}</strong></td>
            <td>{{ $zg->client?->phone ?? '---' }}</td>
            <td>
                @if($dossierClientZG)
                    <span style="font-size:12px;font-weight:600;color:#1e3a5f;">{{ $dossierClientZG->nom_dossier }}</span>
                @else
                    <span class="badge bg-warning text-dark" style="font-size:10px;">Aucun dossier</span>
                @endif
            </td>
            <td>
                @if($lotsZone->count())
                    <div style="font-size:11px;color:#374151;">
                        @foreach($lotsZone as $lz)
                            <span style="background:#f1f5f9;padding:1px 6px;border-radius:4px;margin:1px;display:inline-block;">
                                {{ strtoupper($lz->code) }}
                            </span>
                        @endforeach
                    </div>
                @else
                    <span style="color:#94a3b8;font-size:11px;">—</span>
                @endif
            </td>
            <td>
                @if($zg->dossierTechnique)
                    <div class="d-flex align-items-center gap-1">
                        <div class="prog-wrap">
                            <div class="prog-fill" style="width:{{ $progressZG }}%;background:{{ $progColorZG }};"></div>
                        </div>
                        <small style="color:{{ $progColorZG }};font-weight:600;">{{ $progressZG }}%</small>
                    </div>
                @else
                    <span class="badge bg-secondary" style="font-size:10px;">Non démarré</span>
                @endif
            </td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">⋮</button>
                    <ul class="dropdown-menu">
                        @if($zg->dossierTechnique)
                            <li>
                                <a class="dropdown-item" href="{{ route('dossier.zone.show', $zg->id) }}">
                                    📁 Dossier technique
                                </a>
                            </li>
                        @endif
                        @if($zg->client_id)
                            <li>
                                <a class="dropdown-item" href="{{ route('suivi-client.show', $zg->client_id) }}">
                                    👤 Fiche client
                                </a>
                            </li>
                        @endif
                    </ul>
                </div>
            </td>
        </tr>
        @endif
    @endforeach

    @if($lots->where('client_id', '!=', null)->count() === 0 && $zonesGroupes->where('client_id', '!=', null)->count() === 0)
        <tr><td colspan="10" class="text-center text-muted py-4">Aucun lot ou zone avec client trouvé</td></tr>
    @endif

    </tbody>
</table>
</div>

@endsection