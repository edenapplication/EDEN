@extends('admin.layout')
@section('content')

<style>
:root { --primary:#1e3a5f; --accent:#4D96FF; --success:#28a745; --danger:#dc3545; --border:#e2e8f0; }
.rapport-header { background:linear-gradient(135deg, #4D96FF 0%, #a855f7 50%, #ef4444 100%); color:white; padding:20px 24px; border-radius:14px; margin-bottom:20px; }
.filter-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:20px; }
.filter-section h6 { font-weight:700; color:var(--primary); border-bottom:2px solid var(--border); padding-bottom:6px; margin-bottom:12px; }
.select-multi { border:1.5px solid var(--border); border-radius:8px; padding:6px; width:100%; font-size:12px; background:#f8fafc; min-height:90px; }
.select-multi option:checked { background:var(--accent); color:white; }
.kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(140px,1fr)); gap:10px; margin-bottom:20px; }
.kpi-box { background:white; border-radius:10px; padding:14px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-top:3px solid var(--accent); }
.kpi-box .val { font-size:20px; font-weight:800; color:var(--primary); }
.kpi-box .lbl { font-size:10px; color:#64748b; text-transform:uppercase; font-weight:600; }
.rapport-table-wrap { overflow-x:auto; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,0.06); }
.rapport-table { width:100%; border-collapse:collapse; font-size:11px; background:white; }
.rapport-table thead tr { background:var(--primary); color:white; }
.rapport-table thead th { padding:8px 6px; font-weight:600; white-space:nowrap; text-align:left; }
.rapport-table tbody tr:nth-child(even) { background:#f8fafc; }
.rapport-table tbody tr:hover { background:#eff6ff; }
.rapport-table tbody td { padding:6px; border-bottom:1px solid var(--border); white-space:nowrap; }
.rapport-table tfoot td { background:var(--primary); color:white; font-weight:700; padding:8px 6px; }
.save-section { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:16px; margin-bottom:16px; }
</style>

<div class="rapport-header">
    <h2 class="mb-1">📊 Rapport d'Activité</h2>
    <p class="mb-0 opacity-75">Données tirées des dossiers clients — inclut les dossiers sans lot</p>
</div>

<form method="GET" action="{{ route('rapport.index') }}" id="form-rapport">
<div class="filter-section">
    <h6>🔍 Filtres</h6>
    <div class="row g-3">
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">🏢 Grand Site souhaité</label>
            <select name="grand_sites[]" multiple class="select-multi">
                @foreach($options['grandsites'] as $gs)
                    <option value="{{ $gs->id }}" {{ in_array($gs->id, request('grand_sites',[])) ? 'selected' : '' }}>{{ $gs->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">🗺️ Sites (lots)</label>
            <select name="sites[]" multiple class="select-multi">
                @foreach($options['sites'] as $s)
                    <option value="{{ $s->id }}" {{ in_array($s->id, request('sites',[])) ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">🧭 TFs</label>
            <select name="tfs[]" multiple class="select-multi">
                @foreach($options['tfs'] as $tf)
                    <option value="{{ $tf->id }}" {{ in_array($tf->id, request('tfs',[])) ? 'selected' : '' }}>{{ $tf->title }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">👤 Clients</label>
            <select name="clients_ids[]" multiple class="select-multi">
                @foreach($options['clients'] as $c)
                    <option value="{{ $c->id }}" {{ in_array($c->id, request('clients_ids',[])) ? 'selected' : '' }}>{{ $c->name }} ({{ $c->phone }})</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">🏷️ Types lot</label>
            <select name="types[]" multiple class="select-multi">
                @foreach($options['types'] as $t)
                    <option value="{{ $t }}" {{ in_array($t, request('types',[])) ? 'selected' : '' }}>{{ str_replace('_',' ',ucfirst($t)) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">📁 Statut dossier tech.</label>
            <select name="statuts_dossier[]" multiple class="select-multi">
                @foreach($options['statuts'] as $s)
                    <option value="{{ $s }}" {{ in_array($s, request('statuts_dossier',[])) ? 'selected' : '' }}>
                        {{ $s === 'none' ? 'Non commencé' : ($s === 'en_cours' ? 'En cours' : 'Complet') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">📅 Période paiements</label>
            <div class="d-flex gap-2">
                <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}">
                <input type="date" name="date_fin"   class="form-control form-control-sm" value="{{ request('date_fin') }}">
            </div>
        </div>
        <div class="col-md-9">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">📋 Colonnes à afficher</label>
            <div style="display:flex; flex-wrap:wrap; gap:6px; margin-top:4px;">
                @foreach($colonnes as $key => $label)
                    <label style="font-size:11px; background:#f1f5f9; padding:3px 8px; border-radius:6px; cursor:pointer;">
                        <input type="checkbox" name="colonnes[]" value="{{ $key }}"
                            {{ in_array($key, $colonnesChoisies) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>
    </div>
    <div class="d-flex gap-2 mt-4 flex-wrap">
        <button type="submit" class="btn btn-primary">🔍 Générer</button>
        <a href="{{ route('rapport.index') }}" class="btn btn-outline-secondary">✖ Reset</a>
    </div>
</div>
</form>

@if($dossiers->count() > 0)

{{-- KPI --}}
<div class="kpi-grid mt-3">
    <div class="kpi-box"><div class="val">{{ $totaux['nb_dossiers'] }}</div><div class="lbl">Dossiers</div></div>
    <div class="kpi-box"><div class="val">{{ $totaux['nb_clients'] }}</div><div class="lbl">Clients</div></div>
    <div class="kpi-box"><div class="val">{{ $totaux['nb_avec_lot'] }}</div><div class="lbl">Avec lot</div></div>
    <div class="kpi-box"><div class="val" style="color:var(--danger)">{{ $totaux['nb_sans_lot'] }}</div><div class="lbl">Sans lot</div></div>
    <div class="kpi-box"><div class="val" style="color:var(--success)">{{ number_format($totaux['prix_total'],0,',',' ') }}</div><div class="lbl">Prix total FCFA</div></div>
    <div class="kpi-box"><div class="val" style="color:var(--accent)">{{ number_format($totaux['total_paye'],0,',',' ') }}</div><div class="lbl">Payé FCFA</div></div>
    <div class="kpi-box"><div class="val" style="color:var(--danger)">{{ number_format($totaux['total_reste'],0,',',' ') }}</div><div class="lbl">Reste FCFA</div></div>
    <div class="kpi-box"><div class="val">{{ $totaux['avg_progression'] }}%</div><div class="lbl">Moy. paiement</div></div>
</div>

{{-- SAUVEGARDER --}}
<div class="save-section">
    <form method="POST" action="{{ route('rapport.sauvegarder') }}" id="form-save" class="d-flex gap-2 align-items-end flex-wrap">
        @csrf
        @foreach(request()->all() as $k => $v)
            @if(is_array($v))
                @foreach($v as $vi) <input type="hidden" name="{{ $k }}[]" value="{{ $vi }}"> @endforeach
            @else
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endif
        @endforeach
        <div>
            <label style="font-size:12px;font-weight:600;">Titre du rapport</label>
            <input type="text" name="titre" id="input-titre" class="form-control form-control-sm" placeholder="Ex: Rapport Q1 2025" required>
        </div>
        <div style="flex:1;">
            <label style="font-size:12px;font-weight:600;">Description</label>
            <input type="text" name="description" id="input-desc" class="form-control form-control-sm" placeholder="Description optionnelle">
        </div>
        <button type="submit" class="btn btn-success btn-sm">💾 Sauvegarder</button>
    </form>
</div>

{{-- EXPORT --}}
<div class="d-flex gap-3 mb-3">
    <form method="POST" action="{{ route('rapport.export') }}" id="form-pdf">
        @csrf
        @foreach(request()->all() as $k => $v)
            @if(is_array($v))
                @foreach($v as $vi) <input type="hidden" name="{{ $k }}[]" value="{{ $vi }}"> @endforeach
            @else
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endif
        @endforeach
        <input type="hidden" name="type" value="pdf">
        <input type="hidden" name="titre" id="pdf-titre">
        <input type="hidden" name="description" id="pdf-desc">
        <button type="submit" style="background:#dc3545;color:white;padding:8px 16px;border-radius:8px;border:none;font-weight:600;font-size:13px;cursor:pointer;">
            📄 PDF
        </button>
    </form>
    <form method="POST" action="{{ route('rapport.export') }}">
        @csrf
        @foreach(request()->all() as $k => $v)
            @if(is_array($v))
                @foreach($v as $vi) <input type="hidden" name="{{ $k }}[]" value="{{ $vi }}"> @endforeach
            @else
                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
            @endif
        @endforeach
        <input type="hidden" name="type" value="excel">
        <button type="submit" style="background:#28a745;color:white;padding:8px 16px;border-radius:8px;border:none;font-weight:600;font-size:13px;cursor:pointer;">
            📊 Excel (CSV)
        </button>
    </form>
</div>

{{-- TABLEAU --}}
<div class="rapport-table-wrap">
<table class="rapport-table">
    <thead>
        <tr>
            <th>#</th>
            @foreach($colonnesChoisies as $col)
                <th>{{ $colonnes[$col] ?? $col }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
    @foreach($dossiers as $i => $d)
        @php
            $paye      = $d->paiements->sum('montant');
            $reste     = max(0, ($d->prix_superficie ?? 0) - $paye);
            $prog      = $d->prix_superficie > 0 ? round(($paye / $d->prix_superficie) * 100) : 0;
            $progColor = $prog < 40 ? '#dc3545' : ($prog < 75 ? '#fd7e14' : '#28a745');
            $lot       = $d->lots->first();
            $statut    = $lot?->dossier?->statut ?? 'none';
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            @foreach($colonnesChoisies as $col)
            <td>
                @switch($col)
                    @case('grand_site')    {{ $lot?->tf?->site?->grandSite?->nom ?? '—' }} @break
                    @case('site')          {{ $lot?->tf?->site?->name ?? '—' }} @break
                    @case('tf')            {{ $lot?->tf?->title ?? '—' }} @break
                    @case('lot')
                        @if($lot) <strong>{{ strtoupper($lot->code) }}</strong>
                        @else <span style="color:#94a3b8;font-style:italic;">Sans lot</span>
                        @endif
                        @break
                    @case('client')        {{ $d->client?->name ?? '-' }} @break
                    @case('telephone')     {{ $d->client?->phone ?? '-' }} @break
                    @case('commercial')    {{ $d->commercial?->name ?? '-' }} @break
                    @case('facilitateur')  {{ $d->facilitateur?->nom ?? '-' }} @break
                    @case('chauffeur')     {{ $d->conducteur?->nom ?? '-' }} @break
                    @case('agent')         {{ $d->agentCommercial?->nom ?? '-' }} @break
                    @case('direction')
                        {{ match($d->direction) { 'baffoussam'=>'Baffoussam','bagante'=>'Bagante','direction_generale'=>'Dir. Générale',default=>$d->direction??'-' } }}
                        @break
                    @case('grand_site_dossier') {{ $d->grandSite?->nom ?? '-' }} @break
                    @case('superficie')    {{ $d->superficie_voulue ? number_format($d->superficie_voulue,0,',',' ') : '-' }} @break
                    @case('prix')          {{ $d->prix_superficie ? number_format($d->prix_superficie,0,',',' ') : '-' }} @break
                    @case('paye')          <span style="color:#28a745;font-weight:600;">{{ number_format($paye,0,',',' ') }}</span> @break
                    @case('reste')         <span style="color:#dc3545;font-weight:600;">{{ number_format($reste,0,',',' ') }}</span> @break
                    @case('date_prevue')   {{ $lot?->date_prevue?->format('d/m/Y') ?? '-' }} @break
                    @case('date_confirmee'){{ $lot?->date_confirmee?->format('d/m/Y') ?? '-' }} @break
                    @case('date_morcel')   {{ $lot?->date_morcellement?->format('d/m/Y') ?? '-' }} @break
                    @case('statut_dossier')
                        <span style="font-size:10px;padding:2px 6px;border-radius:4px;background:{{ $statut==='complet'?'#dcfce7':($statut==='en_cours'?'#fef9c3':'#f1f5f9')}};color:{{ $statut==='complet'?'#15803d':($statut==='en_cours'?'#854d0e':'#475569')}};">
                            {{ $statut === 'none' ? 'Non commencé' : ($statut === 'en_cours' ? 'En cours' : 'Complet') }}
                        </span>
                        @break
                    @case('progression')
                        <div style="display:flex;align-items:center;gap:4px;">
                            <div style="width:50px;height:6px;background:#e2e8f0;border-radius:3px;">
                                <div style="width:{{ $prog }}%;height:100%;background:{{ $progColor }};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:10px;color:{{ $progColor }};font-weight:600;">{{ $prog }}%</span>
                        </div>
                        @break
                    @case('nom_dossier')   {{ $d->nom_dossier ?? '-' }} @break
                @endswitch
            </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAUX</td>
            @foreach($colonnesChoisies as $col)
            <td>
                @switch($col)
                    @case('lot')        {{ $totaux['nb_dossiers'] }} dossiers @break
                    @case('superficie') {{ number_format($totaux['superficie_totale'],0,',',' ') }} m² @break
                    @case('prix')       {{ number_format($totaux['prix_total'],0,',',' ') }} @break
                    @case('paye')       {{ number_format($totaux['total_paye'],0,',',' ') }} @break
                    @case('reste')      {{ number_format($totaux['total_reste'],0,',',' ') }} @break
                    @case('progression'){{ $totaux['avg_progression'] }}% moy. @break
                    @default —
                @endswitch
            </td>
            @endforeach
        </tr>
    </tfoot>
</table>
</div>

@elseif(request()->anyFilled(['grand_sites','sites','tfs','types','statuts_dossier','date_debut','date_fin','clients_ids']))
    <div class="alert alert-info">Aucun dossier trouvé avec ces filtres.</div>
@endif

@endsection

@section('scripts')
<script>
// Synchronise le titre/description vers le PDF avant soumission
document.getElementById('form-pdf')?.addEventListener('submit', function() {
    document.getElementById('pdf-titre').value = document.getElementById('input-titre')?.value ?? '';
    document.getElementById('pdf-desc').value  = document.getElementById('input-desc')?.value  ?? '';
});
</script>
@endsection