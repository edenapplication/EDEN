@extends('admin.layout')
@section('content')

<style>
/* ===== CARDS ===== */
.site-card {
    background: white;
    border-radius: 18px;
    padding: 20px;
    box-shadow: 0 2px 14px rgba(0,0,0,0.06);
    border: 1px solid #eef2f7;
    transition: 0.22s;
    height: 100%;
    display: flex;
    flex-direction: column;
}
.site-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 36px rgba(0,0,0,0.11);
    border-color: #bfdbfe;
}
.site-icon {
    width: 46px; height: 46px; border-radius: 13px;
    background: linear-gradient(135deg,#1d4ed8,#3b82f6);
    display: flex; align-items: center; justify-content: center;
    font-size: 20px; color: white; flex-shrink: 0;
}
.site-title { font-size: 15px; font-weight: 800; color: #1e3a5f; }
.site-sub   { font-size: 11px; color: #94a3b8; }
.badge-tf   {
    background: #dbeafe; color: #1d4ed8;
    font-size: 10px; font-weight: 700;
    padding: 4px 10px; border-radius: 999px;
    white-space: nowrap;
}
.site-desc {
    font-size: 12px; color: #64748b; line-height: 1.55;
    margin: 10px 0; flex-grow: 1;
    display: -webkit-box; -webkit-line-clamp: 2;
    -webkit-box-orient: vertical; overflow: hidden;
}

/* Stats grid 3 colonnes */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 6px;
    margin-bottom: 12px;
}
.stat-box {
    background: #f8fafc;
    border-radius: 10px;
    padding: 7px 6px;
    text-align: center;
}
.stat-box .sv { font-size: 15px; font-weight: 800; }
.stat-box .sl { font-size: 8px; text-transform: uppercase; color: #94a3b8; font-weight: 700; margin-top: 2px; }

/* Barre activité */
.prog-bar { height: 6px; background: #e2e8f0; border-radius: 3px; overflow: hidden; margin: 8px 0 14px; }
.prog-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg,#1d4ed8,#16a34a); }

/* Actions */
.site-actions { display: flex; gap: 6px; }
.site-btn {
    flex: 1; border: none; border-radius: 10px;
    padding: 8px 6px; font-size: 11px; font-weight: 700;
    text-decoration: none; text-align: center; transition: 0.18s;
}
.btn-open   { background: #1d4ed8; color: white; }
.btn-open:hover  { background: #1e40af; color: white; }
.btn-edit   { background: #f59e0b; color: white; }
.btn-edit:hover  { background: #d97706; color: white; }
.btn-delete { background: #ef4444; color: white; }
.btn-delete:hover{ background: #dc2626; color: white; }

/* Tooltip */
#siteTooltip {
    position: fixed; display: none; width: 270px;
    background: white; border-radius: 14px;
    box-shadow: 0 16px 40px rgba(0,0,0,0.18);
    padding: 16px; z-index: 99999;
    border-top: 4px solid #1d4ed8;
    pointer-events: none;
}
.tt-title { font-size: 14px; font-weight: 800; color: #1e3a5f; margin-bottom: 10px; }
.tt-row   { display: flex; justify-content: space-between; padding: 4px 0; border-bottom: 1px solid #f1f5f9; font-size: 11px; }
.tt-lbl   { color: #64748b; }
.tt-val   { font-weight: 700; color: #0f172a; }
.tt-prog  { height: 4px; background: #e2e8f0; border-radius: 2px; margin-top: 8px; overflow: hidden; }
.tt-prog-f{ height: 100%; background: linear-gradient(90deg,#1d4ed8,#16a34a); border-radius: 2px; }

.empty-box { background: white; border-radius: 14px; padding: 50px 20px; text-align: center; color: #94a3b8; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
.section-lbl { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.07em; color: #94a3b8; margin-bottom: 14px; }
</style>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- TOOLTIP --}}
<div id="siteTooltip">
    <div class="tt-title" id="tt-name">-</div>
    <div class="tt-row"><span class="tt-lbl">📄 TF</span>              <span class="tt-val" id="tt-tf">-</span></div>
    <div class="tt-row"><span class="tt-lbl">📦 Lots</span>            <span class="tt-val" id="tt-lots">-</span></div>
    <div class="tt-row"><span class="tt-lbl">🟡 Zones groupées</span>  <span class="tt-val" id="tt-zones">-</span></div>
    <div class="tt-row"><span class="tt-lbl">🔵 EDEN</span>            <span class="tt-val" id="tt-eden" style="color:#1d4ed8">-</span></div>
    <div class="tt-row"><span class="tt-lbl">🏡 Famille</span>         <span class="tt-val" id="tt-famille" style="color:#92400e">-</span></div>
    <div class="tt-row"><span class="tt-lbl">⏳ Implant. prévue</span> <span class="tt-val" id="tt-ip" style="color:#7c3aed">-</span></div>
    <div class="tt-row"><span class="tt-lbl">✅ Implanté</span>        <span class="tt-val" id="tt-di" style="color:#16a34a">-</span></div>
    <div class="tt-row"><span class="tt-lbl">📁 Dossier tech.</span>   <span class="tt-val" id="tt-dt" style="color:#dc2626">-</span></div>
    <div class="tt-row"><span class="tt-lbl">✂️ Morcellement</span>    <span class="tt-val" id="tt-mo" style="color:#ca8a04">-</span></div>
    <div class="tt-row"><span class="tt-lbl">📐 Superficie</span>      <span class="tt-val" id="tt-sup">-</span></div>
    <div style="display:flex;justify-content:space-between;font-size:11px;color:#64748b;margin-top:8px;margin-bottom:3px;">
        <span>Activité</span><strong id="tt-pct" style="color:#1e3a5f;">-</strong>
    </div>
    <div class="tt-prog"><div class="tt-prog-f" id="tt-prog-f" style="width:0%"></div></div>
</div>

{{-- HEADER --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('grand-sites.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← Grand Sites</a>
        <h2 style="font-weight:800;color:#1e3a5f;margin:0;">📍 {{ $grandsite->nom }}</h2>
        <div style="font-size:13px;color:#64748b;">{{ $sites->count() }} site(s) enregistré(s)</div>
    </div>
    <a href="{{ route('sites.create', $grandsite->id) }}" class="btn btn-primary">+ Ajouter un site</a>
</div>

{{-- KPIs globaux --}}
@php
    $totalTfs    = $sites->sum('stat_tfs');
    $totalLots   = $sites->sum('stat_lots');
    $totalZones  = $sites->sum('stat_zones');
    $totalSup    = $sites->sum('stat_superficie');
    $totalEden   = $sites->sum('stat_eden');
    $totalFamille= $sites->sum('stat_famille');
@endphp
<div class="row g-3 mb-4">
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #1e3a5f;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#1e3a5f;">{{ $sites->count() }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Sites</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #1d4ed8;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#1d4ed8;">{{ $totalTfs }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">TF</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #0891b2;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#0891b2;">{{ $totalLots }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Lots</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #f59e0b;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#f59e0b;">{{ $totalZones }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Zones groupées</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #1d4ed8;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#1d4ed8;">{{ $totalEden }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Lots EDEN</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #92400e;text-align:center;">
        <div style="font-size:20px;font-weight:800;color:#92400e;">{{ $totalFamille }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Lots Famille</div>
    </div></div>
    <div class="col"><div style="background:white;border-radius:12px;padding:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #0891b2;text-align:center;">
        <div style="font-size:16px;font-weight:800;color:#0891b2;">{{ number_format($totalSup,0,',',' ') }}</div>
        <div style="font-size:9px;color:#94a3b8;font-weight:700;text-transform:uppercase;">Superficie (m²)</div>
    </div></div>
</div>

<div class="section-lbl">🏢 Sites disponibles</div>

<div class="row g-3">
@forelse($sites as $site)
<div class="col-xl-3 col-lg-4 col-md-6">
    <div class="site-card site-hover"
         data-name="{{ $site->name }}"
         data-tf="{{ $site->stat_tfs }}"
         data-lots="{{ $site->stat_lots }}"
         data-zones="{{ $site->stat_zones }}"
         data-eden="{{ $site->stat_eden }}"
         data-famille="{{ $site->stat_famille }}"
         data-ip="{{ $site->stat_implant }}"
         data-di="{{ $site->stat_implante }}"
         data-dt="{{ $site->stat_dossier }}"
         data-mo="{{ $site->stat_morcel }}"
         data-sup="{{ number_format($site->stat_superficie,0,',',' ') }}"
         data-pct="{{ $site->stat_activite }}"
         data-desc="{{ $site->description ?? '' }}">

        <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:10px;">
            <div class="d-flex gap-3 align-items-center">
                <div class="site-icon">🗺️</div>
                <div>
                    <div class="site-title">{{ $site->name }}</div>
                    <div class="site-sub">{{ $site->stat_tfs }} TF · {{ $site->stat_lots }} lot(s) · {{ $site->stat_zones }} zone(s)</div>
                </div>
            </div>
            <span class="badge-tf">{{ $site->stat_activite }}%</span>
        </div>


        {{-- Stats grid --}}
        

        {{-- Actions --}}
        <div class="site-actions">
            <a href="{{ route('sites.show', $site->id) }}" class="site-btn btn-open">👁 Ouvrir</a>
            <a href="{{ route('sites.edit', $site->id) }}" class="site-btn btn-edit">✏️Modifier</a>
            <form action="{{ route('sites.destroy', $site->id) }}" method="POST"
                  style="display:inline;" onsubmit="return confirm('Supprimer ce site ?')">
                @csrf @method('DELETE')
                <button class="site-btn btn-delete">🗑 Supprimer</button>
            </form>
        </div>
    </div>
</div>
@empty
<div class="col-12">
    <div class="empty-box">
        <div style="font-size:40px;">📭</div>
        <div style="font-size:16px;font-weight:700;margin-top:10px;">Aucun site enregistré</div>
        <div style="font-size:13px;margin-top:6px;">Commencez par ajouter un nouveau site</div>
    </div>
</div>
@endforelse
</div>

<script>
const tooltip = document.getElementById('siteTooltip');

document.querySelectorAll('.site-hover').forEach(card => {
    card.addEventListener('mouseenter', function() {
        const d = this.dataset;
        document.getElementById('tt-name').innerText    = d.name;
        document.getElementById('tt-tf').innerText      = d.tf;
        document.getElementById('tt-lots').innerText    = d.lots;
        document.getElementById('tt-zones').innerText   = d.zones;
        document.getElementById('tt-eden').innerText    = d.eden;
        document.getElementById('tt-famille').innerText = d.famille;
        document.getElementById('tt-ip').innerText      = d.ip;
        document.getElementById('tt-di').innerText      = d.di;
        document.getElementById('tt-dt').innerText      = d.dt;
        document.getElementById('tt-mo').innerText      = d.mo;
        document.getElementById('tt-sup').innerText     = d.sup + ' m²';
        document.getElementById('tt-pct').innerText     = d.pct + '%';
        document.getElementById('tt-prog-f').style.width = d.pct + '%';
        tooltip.style.display = 'block';
    });
    card.addEventListener('mousemove', function(e) {
        let left = e.clientX + 18, top = e.clientY + 18;
        if (left + 290 > window.innerWidth)  left = e.clientX - 290;
        if (top  + 360 > window.innerHeight) top  = e.clientY - 370;
        tooltip.style.left = Math.max(4, left) + 'px';
        tooltip.style.top  = Math.max(4, top)  + 'px';
    });
    card.addEventListener('mouseleave', () => { tooltip.style.display = 'none'; });
});
</script>
@endsection