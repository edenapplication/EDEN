@extends('admin.layout')

@section('content')

<style>
.audit-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:14px; margin-bottom:22px; }
.audit-stat {
    background:white; border-radius:12px; padding:18px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    border-left:4px solid #1d4ed8;
    display:flex; align-items:center; gap:14px;
}
.audit-stat .ico { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:22px; flex-shrink:0; }
.audit-stat .lbl { font-size:11px; color:#64748b; text-transform:uppercase; font-weight:700; letter-spacing:0.3px; }
.audit-stat .val { font-size:24px; font-weight:800; color:#1e3a5f; line-height:1.2; margin-top:2px; }

.audit-filters {
    background:white; border-radius:12px; padding:16px 18px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
    margin-bottom:20px;
    display:grid; grid-template-columns:repeat(auto-fit, minmax(170px, 1fr));
    gap:12px; align-items:end;
}

.audit-table-wrap { background:white; border-radius:12px; overflow:hidden; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
.audit-table { width:100%; border-collapse:collapse; font-size:13px; }
.audit-table thead { background:linear-gradient(135deg,#1e3a5f,#1d4ed8); color:white; }
.audit-table thead th {
    padding:12px 10px; text-align:left; font-weight:700;
    font-size:11px; text-transform:uppercase; letter-spacing:0.4px;
    white-space:nowrap;
}
.audit-table tbody tr { border-bottom:1px solid #f1f5f9; transition:0.15s; }
.audit-table tbody tr:hover { background:#f8fafc; }
.audit-table tbody td { padding:10px; vertical-align:middle; }

.audit-event-badge {
    display:inline-flex; align-items:center; gap:4px;
    padding:3px 10px; border-radius:8px;
    font-size:10px; font-weight:800;
    text-transform:uppercase;
    white-space:nowrap;
}

.audit-module-badge {
    display:inline-block;
    padding:2px 8px; border-radius:6px;
    font-size:10px; font-weight:800;
    background:#f3e8ff; color:#6b21a8;
    text-transform:lowercase;
}

.audit-severity {
    display:inline-block;
    width:8px; height:8px; border-radius:50%;
    margin-right:6px;
}

.audit-btn-detail {
    padding:4px 12px; border-radius:6px;
    background:#eff6ff; color:#1d4ed8;
    border:1px solid #93c5fd;
    font-size:11px; font-weight:700;
    text-decoration:none; transition:0.2s;
    display:inline-flex; align-items:center; gap:4px;
}
.audit-btn-detail:hover { background:#1d4ed8; color:white; border-color:#1d4ed8; }

.audit-empty {
    text-align:center; padding:60px 20px; color:#94a3b8;
}
.audit-empty .ico { font-size:56px; margin-bottom:14px; }
</style>

{{-- EN-TÊTE --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">
            🔍 Journal d'audit
        </h2>
        <div style="font-size:13px;color:#64748b;margin-top:4px;">
            Historique complet des actions effectuées sur la plateforme
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <button onclick="window.location.reload()" class="btn btn-outline-primary btn-sm">
            🔄 Actualiser
        </button>
    </div>
</div>

{{-- STATS --}}
<div class="audit-stats">
    <div class="audit-stat" style="border-left-color:#1d4ed8;">
        <div class="ico" style="background:#dbeafe;color:#1d4ed8;">📊</div>
        <div>
            <div class="lbl">Total événements</div>
            <div class="val">{{ number_format($stats['total']) }}</div>
        </div>
    </div>
    <div class="audit-stat" style="border-left-color:#16a34a;">
        <div class="ico" style="background:#dcfce7;color:#16a34a;">📅</div>
        <div>
            <div class="lbl">Aujourd'hui</div>
            <div class="val">{{ number_format($stats['aujourdhui']) }}</div>
        </div>
    </div>
    <div class="audit-stat" style="border-left-color:#dc2626;">
        <div class="ico" style="background:#fee2e2;color:#dc2626;">⚠️</div>
        <div>
            <div class="lbl">Critiques</div>
            <div class="val">{{ number_format($stats['critiques']) }}</div>
        </div>
    </div>
    <div class="audit-stat" style="border-left-color:#7c3aed;">
        <div class="ico" style="background:#f3e8ff;color:#7c3aed;">👥</div>
        <div>
            <div class="lbl">Utilisateurs actifs</div>
            <div class="val">{{ $stats['utilisateurs'] }}</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" class="audit-filters">
    <div>
        <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">🔍 Recherche</label>
        <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Description, nom, URL...">
    </div>

    <div>
        <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">👤 Utilisateur</label>
        <select name="user_id" class="form-control form-control-sm">
            <option value="">— Tous —</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected(request('user_id') == $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">🎯 Événement</label>
        <select name="event" class="form-control form-control-sm">
            <option value="">— Tous —</option>
            @foreach($events as $ev)
                <option value="{{ $ev }}" @selected(request('event') === $ev)>{{ $ev }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">📁 Module</label>
        <select name="module" class="form-control form-control-sm">
            <option value="">— Tous —</option>
            @foreach($modules as $mod)
                <option value="{{ $mod }}" @selected(request('module') === $mod)>{{ $mod }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">⚠️ Sévérité</label>
        <select name="severity" class="form-control form-control-sm">
            <option value="">— Toutes —</option>
            <option value="info"     @selected(request('severity') === 'info')>Info</option>
            <option value="warning"  @selected(request('severity') === 'warning')>Warning</option>
            <option value="critical" @selected(request('severity') === 'critical')>Critical</option>
        </select>
    </div>

    <div>
        <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">📅 Du</label>
        <input type="date" name="du" value="{{ request('du') }}" class="form-control form-control-sm">
    </div>

    <div>
        <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">📅 Au</label>
        <input type="date" name="au" value="{{ request('au') }}" class="form-control form-control-sm">
    </div>

    <div style="display:flex;gap:6px;">
        <button type="submit" class="btn btn-primary btn-sm" style="font-weight:700;">🔍 Filtrer</button>
        <a href="{{ route('audits.index') }}" class="btn btn-outline-secondary btn-sm">↻</a>
    </div>
</form>

{{-- TABLEAU --}}
<div class="audit-table-wrap">
    @if($audits->count() > 0)
    <div style="overflow-x:auto;">
        <table class="audit-table">
            <thead>
                <tr>
                    <th style="width:140px;">Date</th>
                    <th style="width:140px;">Utilisateur</th>
                    <th style="width:130px;">Événement</th>
                    <th style="width:100px;">Module</th>
                    <th>Description</th>
                    <th style="width:120px;">IP</th>
                    <th style="width:100px;text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($audits as $audit)
                <tr>
                    <td style="font-size:12px;color:#64748b;white-space:nowrap;">
                        {{ $audit->created_at->format('d/m/Y') }}<br>
                        <span style="font-weight:700;color:#1e3a5f;">{{ $audit->created_at->format('H:i:s') }}</span>
                    </td>
                    <td>
                        <div style="display:flex;align-items:center;gap:6px;">
                            <span class="audit-severity" style="background:{{ $audit->couleur }};"></span>
                            <div>
                                <div style="font-weight:700;color:#1e3a5f;font-size:12px;">
                                    {{ $audit->user_name ?? 'Système' }}
                                </div>
                                @if($audit->user_role)
                                    <div style="font-size:10px;color:#94a3b8;text-transform:uppercase;">
                                        {{ $audit->user_role }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td>
                        <span class="audit-event-badge" style="background:{{ $audit->couleur }}20;color:{{ $audit->couleur }};">
                            {{ $audit->icone }} {{ $audit->event }}
                        </span>
                    </td>
                    <td>
                        @if($audit->module)
                            <span class="audit-module-badge">{{ $audit->module }}</span>
                        @else
                            <span style="color:#cbd5e1;">—</span>
                        @endif
                    </td>
                    <td style="font-size:12.5px;color:#1e293b;line-height:1.4;">
                        {{ $audit->description ?? '—' }}
                        @if($audit->auditable_label)
                            <div style="font-size:11px;color:#64748b;margin-top:2px;font-style:italic;">
                                <strong>{{ class_basename($audit->auditable_type) }}</strong> : {{ $audit->auditable_label }}
                            </div>
                        @endif
                    </td>
                    <td style="font-size:11px;color:#94a3b8;font-family:monospace;">
                        {{ $audit->ip }}
                    </td>
                    <td style="text-align:right;">
                        <a href="{{ route('audits.show', $audit) }}" class="audit-btn-detail">
                            👁️ Détails
                        </a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="audit-empty">
        <div class="ico">📭</div>
        <div style="font-weight:700;font-size:15px;color:#475569;">
            Aucun événement enregistré
        </div>
        <div style="font-size:13px;margin-top:6px;">
            Les actions effectuées dans l'application apparaîtront ici.
        </div>
    </div>
    @endif
</div>

{{-- PAGINATION --}}
@if($audits->count() > 0)
<div style="margin-top:20px;">
    {{ $audits->links() }}
</div>
@endif

@endsection