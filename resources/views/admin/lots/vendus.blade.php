@extends('admin.layout')
@section('content')

<style>
.dropdown-menu { z-index: 9999; }

.modal-box {
    display:none; position:fixed;
    top:50%; left:50%; transform:translate(-50%,-50%);
    background:white; padding:20px; width:450px;
    border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.2);
    z-index:9999;
}

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

{{-- ===== FILTRES ===== --}}
<form method="GET" class="filter-bar">
    <select name="grand_site" onchange="this.form.submit()">
        <option value="">🏢 Tous les grands sites</option>
        @foreach($grandsites as $gs)
            <option value="{{ $gs->id }}" {{ request('grand_site') == $gs->id ? 'selected' : '' }}>
                {{ $gs->nom }}
            </option>
        @endforeach
    </select>

    <select name="site" onchange="this.form.submit()">
        <option value="">🗺️ Tous les sites</option>
        @foreach($sites as $s)
            <option value="{{ $s->id }}" {{ request('site') == $s->id ? 'selected' : '' }}>
                {{ $s->name }}
            </option>
        @endforeach
    </select>

    <select name="tf" onchange="this.form.submit()">
        <option value="">🧭 Tous les TF</option>
        @foreach($tfs as $tf)
            <option value="{{ $tf->id }}" {{ request('tf') == $tf->id ? 'selected' : '' }}>
                {{ $tf->title }}
            </option>
        @endforeach
    </select>

    <select name="bloc" onchange="this.form.submit()">
        <option value="">🔤 Tous les blocs</option>
        @foreach($blocs as $b)
            <option value="{{ $b }}" {{ request('bloc') == $b ? 'selected' : '' }}>
                Bloc {{ $b }}
            </option>
        @endforeach
    </select>

    <input type="text" name="search" placeholder="🔍 Client (nom ou tél.)"
           value="{{ request('search') }}">

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
            <th>CLIENT</th>
            <th>TÉLÉPHONE</th>
            <th>PRIX</th>
            <th>PAYÉ</th>
            <th>RESTE</th>
            <th>DOSSIER</th>
            <th>ACTIONS</th>
        </tr>
    </thead>
    <tbody>
    @forelse($lots as $lot)
        @php
    // ✅ Paiements depuis le dossier client, pas le lot
    $dossierClient = $lot->client?->dossiers->first();
    $total_paye    = $dossierClient?->paiements->sum('montant') ?? 0;
    $prixRef       = $dossierClient?->prix_superficie ?? $lot->prix ?? 0;
    $reste         = max(0, $prixRef - $total_paye);
    $progress      = $lot->dossier->progression ?? 0;
    $progColor     = $progress < 40 ? '#dc3545' : ($progress < 75 ? '#fd7e14' : '#28a745');
@endphp
        <tr>
            
            <td>{{ $lot->tf?->site?->grandSite?->nom ?? '---' }}</td>
            <td>{{ $lot->tf?->site?->name ?? '---' }}</td>
            <td style="max-width:100px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"
                title="{{ $lot->tf?->title }}">
                {{ $lot->tf?->title ?? '---' }}
            </td>
            <td><strong>{{ $lot->client?->name ?? $lot->owner_name ?? '---' }}</strong></td>
            <td>{{ $lot->client?->phone ?? '---' }}</td>
            <td>{{ number_format($lot->prix ?? 0, 0, ',', ' ') }} FCFA</td>
            <td style="color:#28a745; font-weight:600;">{{ number_format($total_paye, 0, ',', ' ') }} FCFA</td>
            <td style="color:#dc3545; font-weight:600;">{{ number_format($reste, 0, ',', ' ') }} FCFA</td>
            <td>
                @if($lot->dossier)
                    <div class="d-flex align-items-center gap-1">
                        <div class="prog-wrap">
                            <div class="prog-fill" style="width:{{ $progress }}%; background:{{ $progColor }};"></div>
                        </div>
                        <small style="color:{{ $progColor }}; font-weight:600;">{{ $progress }}%</small>
                    </div>
                @else
                    <span class="badge bg-warning text-dark">⚠ Non créé</span>
                @endif
            </td>
            <td>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light" data-bs-toggle="dropdown">⋮</button>
                    <ul class="dropdown-menu">
                        
                        <li>
                            <a class="dropdown-item"
                               href="{{ route('dossier.show', $lot->id) }}">
                                📁 Dossier technique
                            </a>
                        </li>
                    </ul>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="12" class="text-center text-muted py-4">Aucun lot trouvé</td></tr>
    @endforelse
    </tbody>
</table>
</div>


@endsection

@section('scripts')
