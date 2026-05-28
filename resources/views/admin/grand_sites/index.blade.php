@extends('admin.layout')
@section('content')

<style>
.gs-card {
    background: white;
    border-radius: 20px;
    padding: 22px;
    box-shadow: 0 4px 16px rgba(0,0,0,0.07);
    border: 1px solid #eef2f7;
    transition: 0.22s;
    height: 100%;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
}
.gs-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 4px;
    background: linear-gradient(90deg, #1d4ed8, #7c3aed, #dc2626);
    border-radius: 20px 20px 0 0;
}
.gs-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 18px 40px rgba(0,0,0,0.12);
    border-color: #bfdbfe;
}
.gs-icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: linear-gradient(135deg,#1e3a5f,#2d6cdf);
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; color: white; flex-shrink: 0;
    box-shadow: 0 6px 18px rgba(30,58,95,0.25);
}
.gs-name { font-size: 17px; font-weight: 800; color: #1e3a5f; margin-bottom: 2px; }
.gs-sub  { font-size: 11px; color: #94a3b8; }
.gs-desc { font-size: 12px; color: #64748b; line-height: 1.55; margin: 10px 0; min-height: 36px; }

/* Stats 3×2 */
.gs-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    margin-bottom: 12px;
}
.gs-stat { background: #f8fafc; border-radius: 10px; padding: 8px 6px; text-align: center; }
.gs-stat .sv { font-size: 15px; font-weight: 800; }
.gs-stat .sl { font-size: 8px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-top: 2px; }

.prog-bar  { height: 5px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin: 6px 0 14px; }
.prog-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg,#1d4ed8,#16a34a); }

.gs-actions { display: flex; gap: 8px; margin-top: auto; }
.gs-btn {
    flex: 1; border: none; border-radius: 10px;
    padding: 10px 8px; font-size: 12px; font-weight: 700;
    text-decoration: none; text-align: center; transition: 0.18s;
}
.btn-enter  { background: linear-gradient(135deg,#1d4ed8,#3b82f6); color: white; }
.btn-enter:hover  { color: white; transform: translateY(-1px); }
.btn-edit   { background: #f59e0b; color: white; }
.btn-edit:hover   { background: #d97706; color: white; }
.btn-delete { background: #ef4444; color: white; }
.btn-delete:hover { background: #dc2626; color: white; }

.empty-box {
    background: white; border-radius: 16px; padding: 60px 20px;
    text-align: center; color: #94a3b8;
    box-shadow: 0 4px 16px rgba(0,0,0,0.05);
}
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="font-weight:800;color:#1e3a5f;margin:0;">🏢 Zones & Grand Sites</h2>
        <div style="font-size:13px;color:#64748b;">{{ $grandsites->count() }} grand(s) site(s) enregistré(s)</div>
    </div>
    <a href="{{ route('grand-sites.create') }}"
       style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);color:white;border:none;border-radius:12px;padding:10px 18px;font-weight:700;text-decoration:none;font-size:13px;">
        + Nouveau Grand Site
    </a>
</div>

{{-- KPIs globaux --}}
@php
    $gTotalSites = $grandsites->sum('sites_count');
    $gTotalTfs   = $grandsites->sum('stat_tfs');
    $gTotalLots  = $grandsites->sum('stat_lots');
    $gTotalZones = $grandsites->sum('stat_zones');
    $gTotalEden  = $grandsites->sum('stat_eden');
    $gTotalFam   = $grandsites->sum('stat_famille');
    $gTotalSup   = $grandsites->sum('stat_superficie');
@endphp
<div class="row g-3 mb-4">
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #1e3a5f;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#1e3a5f;">{{ $grandsites->count() }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Zones</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #1d4ed8;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#1d4ed8;">{{ $gTotalSites }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Sites</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #0891b2;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#0891b2;">{{ $gTotalTfs }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">TF</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #374151;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#374151;">{{ $gTotalLots }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Lots</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #f59e0b;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#f59e0b;">{{ $gTotalZones }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Zones groupées</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #1d4ed8;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#1d4ed8;">{{ $gTotalEden }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Lots EDEN</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #92400e;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#92400e;">{{ $gTotalFam }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Lots Famille</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #0891b2;text-align:center;">
        <div style="font-size:15px;font-weight:800;color:#0891b2;">{{ number_format($gTotalSup,0,',',' ') }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Superficie (m²)</div>
    </div></div>
</div>

@if($grandsites->count())
<div class="row g-3">
    @foreach($grandsites as $gs)
    <div class="col-xl-3 col-lg-4 col-md-6">
        <div class="gs-card">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:12px;">
                <div class="d-flex gap-3 align-items-center">
                    <div class="gs-icon">🏢</div>
                    <div>
                        <div class="gs-name">{{ $gs->nom }}</div>
                        <div class="gs-sub">{{ $gs->sites_count }} site(s) · {{ $gs->stat_tfs }} TF</div>
                    </div>
                </div>
                <span style="background:#dbeafe;color:#1d4ed8;font-size:10px;font-weight:700;padding:4px 10px;border-radius:999px;white-space:nowrap;">
                    {{ $gs->stat_activite }}%
                </span>
            </div>

            <div class="gs-desc">{{ $gs->description ?? 'Aucune description renseignée.' }}</div>

            <div class="gs-stats">
                <div class="gs-stat">
                    <div class="sv" style="color:#1d4ed8;">{{ $gs->stat_lots }}</div>
                    <div class="sl">Lots</div>
                </div>
                <div class="gs-stat">
                    <div class="sv" style="color:#f59e0b;">{{ $gs->stat_zones }}</div>
                    <div class="sl">Zones</div>
                </div>
                <div class="gs-stat">
                    <div class="sv" style="color:#0891b2;">{{ $gs->stat_tfs }}</div>
                    <div class="sl">TF</div>
                </div>
                <div class="gs-stat">
                    <div class="sv" style="color:#1d4ed8;">{{ $gs->stat_eden }}</div>
                    <div class="sl">EDEN</div>
                </div>
                <div class="gs-stat">
                    <div class="sv" style="color:#92400e;">{{ $gs->stat_famille }}</div>
                    <div class="sl">Famille</div>
                </div>
                <div class="gs-stat">
                    <div class="sv" style="color:#0891b2;font-size:11px;">{{ number_format($gs->stat_superficie/1000,0,',',' ') }}k</div>
                    <div class="sl">m²</div>
                </div>
            </div>

            {{-- Origines chips --}}
            <div style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:8px;font-size:10px;">
                <span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:6px;font-weight:600;">🔵 EDEN : {{ $gs->stat_eden }}</span>
                <span style="background:#fef3c7;color:#92400e;padding:2px 8px;border-radius:6px;font-weight:600;">🏡 Famille : {{ $gs->stat_famille }}</span>
            </div>

            {{-- Barre activité --}}
            <div style="display:flex;justify-content:space-between;font-size:10px;color:#64748b;margin-bottom:3px;">
                <span>Activité globale</span>
                <strong style="color:#1e3a5f;">{{ $gs->stat_activite }}%</strong>
            </div>
            <div class="prog-bar">
                <div class="prog-fill" style="width:{{ $gs->stat_activite }}%;"></div>
            </div>

            <div class="gs-actions">
                <a href="{{ route('sites.index', $gs->id) }}" class="gs-btn btn-enter">Entrer →</a>
                <a href="{{ route('grand-sites.edit', $gs->id) }}" class="gs-btn btn-edit">✏️</a>
                <form action="{{ route('grand-sites.destroy', $gs->id) }}" method="POST"
                      style="display:inline;" onsubmit="return confirm('Supprimer cette zone ?')">
                    @csrf @method('DELETE')
                    <button class="gs-btn btn-delete">🗑</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="empty-box">
    <div style="font-size:44px;">📭</div>
    <div style="font-size:16px;font-weight:700;margin-top:12px;">Aucune zone enregistrée</div>
    <div style="font-size:13px;margin-top:6px;">Commencez par créer votre premier grand site foncier.</div>
</div>
@endif

@endsection