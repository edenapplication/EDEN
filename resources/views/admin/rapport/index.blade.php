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

/* ═══ RECAP PAIEMENTS ═══ */
.recap-section { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:20px; }
.recap-grid { display:grid; grid-template-columns:repeat(4, 1fr); gap:14px; }
.recap-card { border-radius:12px; padding:16px; border-left:4px solid; }
.recap-card.superficie   { background:#eff6ff; border-left-color:#0d6efd; }
.recap-card.technique    { background:#fff7ed; border-left-color:#ea580c; }
.recap-card.logistique   { background:#f5f3ff; border-left-color:#7c3aed; }
.recap-card.morcellement { background:#fefce8; border-left-color:#ca8a04; }
.recap-card h6 { font-size:13px; font-weight:700; margin-bottom:10px; }
.recap-row { display:flex; justify-content:space-between; font-size:12px; padding:4px 0; border-bottom:1px solid rgba(0,0,0,0.05); }
.recap-row:last-child { border-bottom:none; }
.recap-row .lbl { color:#64748b; }
.recap-row .val { font-weight:700; }
.recap-progress { height:6px; background:#e2e8f0; border-radius:3px; margin:8px 0; overflow:hidden; }
.recap-progress-bar { height:100%; border-radius:3px; transition:width 0.3s; }
.recap-total { font-size:22px; font-weight:900; text-align:center; margin-top:20px; padding:16px; background:linear-gradient(135deg,#1e3a5f,#1d4ed8); color:white; border-radius:12px; }
@media (max-width:992px) { .recap-grid { grid-template-columns:repeat(2, 1fr); } }
@media (max-width:576px) { .recap-grid { grid-template-columns:1fr; } }

/* ═══ BADGES LOTS ═══ */
.lot-badge { display:inline-block; background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:4px; font-size:10px; font-weight:700; margin:1px; }
.lot-count { display:inline-block; background:#1d4ed8; color:white; padding:1px 6px; border-radius:10px; font-size:9px; font-weight:700; margin-left:4px; }

/* ✅ Badge période + détail dépliable */
.periode-badge { display:inline-block; background:#dbeafe; color:#1d4ed8; padding:2px 8px; border-radius:6px; font-weight:700; font-size:11px; }
.periode-details summary { cursor:pointer; color:#0d6efd; font-size:10px; user-select:none; }
.periode-details ul { margin:4px 0; padding-left:16px; font-size:10px; }
.periode-details li { color:#475569; }
</style>

<div class="rapport-header">
    <h2 class="mb-1">📊 Rapport d'Activité</h2>
    <p class="mb-0 opacity-75">Données réelles des dossiers, affectations de lots et paiements</p>
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
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">🗺️ Grand Site (affectation)</label>
            <select name="sites[]" multiple class="select-multi">
                @foreach($options['grandsites'] as $gs)
                    <option value="{{ $gs->id }}" {{ in_array($gs->id, request('sites',[])) ? 'selected' : '' }}>{{ $gs->nom }}</option>
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
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">📁 Statut dossier</label>
            <select name="statuts_dossier[]" multiple class="select-multi">
                @foreach($options['statuts'] as $s)
                    <option value="{{ $s }}" {{ in_array($s, request('statuts_dossier',[])) ? 'selected' : '' }}>
                        {{ $s === 'none' ? 'Non commencé' : ($s === 'en_cours' ? 'En cours' : 'Complet') }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">🏷️ Disponibilité</label>
            <select name="types[]" multiple class="select-multi">
                @foreach($options['types'] as $t)
                    <option value="{{ $t }}" {{ in_array($t, request('types',[])) ? 'selected' : '' }}>
                        @if($t === 'disponible') 🟢 Disponible
                        @elseif($t === 'indisponible') 🔴 Indisponible
                        @elseif($t === 'actif') ✅ Actif
                        @else ⛔ Inactif
                        @endif
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

                {{-- 👤 Infos client --}}
                <div style="width:100%;font-size:10px;font-weight:700;color:#1d4ed8;margin-top:6px;">👤 Informations client</div>
                @foreach(['client','telephone','sexe'] as $key)
                    @if(isset($colonnes[$key]))
                        <label style="font-size:11px;background:#f1f5f9;padding:3px 8px;border-radius:6px;cursor:pointer;">
                            <input type="checkbox" name="colonnes[]" value="{{ $key }}"
                                {{ in_array($key, $colonnesChoisies) ? 'checked' : '' }}>
                            {{ $colonnes[$key] }}
                        </label>
                    @endif
                @endforeach

                {{-- 📁 Dossier --}}
                <div style="width:100%;font-size:10px;font-weight:700;color:#0d6efd;margin-top:6px;">📁 Dossier</div>
                @foreach(['nom_dossier','grand_site_dossier','superficie','direction','statut_dossier'] as $key)
                    @if(isset($colonnes[$key]))
                        <label style="font-size:11px;background:#f1f5f9;padding:3px 8px;border-radius:6px;cursor:pointer;">
                            <input type="checkbox" name="colonnes[]" value="{{ $key }}"
                                {{ in_array($key, $colonnesChoisies) ? 'checked' : '' }}>
                            {{ $colonnes[$key] }}
                        </label>
                    @endif
                @endforeach

                {{-- 👥 Acteurs --}}
                <div style="width:100%;font-size:10px;font-weight:700;color:#7c3aed;margin-top:6px;">👥 Acteurs</div>
                @foreach(['commercial','facilitateur','chauffeur','agent'] as $key)
                    @if(isset($colonnes[$key]))
                        <label style="font-size:11px;background:#f1f5f9;padding:3px 8px;border-radius:6px;cursor:pointer;">
                            <input type="checkbox" name="colonnes[]" value="{{ $key }}"
                                {{ in_array($key, $colonnesChoisies) ? 'checked' : '' }}>
                            {{ $colonnes[$key] }}
                        </label>
                    @endif
                @endforeach

                {{-- 🗺️ Affectations --}}
                <div style="width:100%;font-size:10px;font-weight:700;color:#16a34a;margin-top:6px;">🗺️ Affectations</div>
                @foreach(['grand_site','site','tf','bloc','lot'] as $key)
                    @if(isset($colonnes[$key]))
                        <label style="font-size:11px;background:#f1f5f9;padding:3px 8px;border-radius:6px;cursor:pointer;">
                            <input type="checkbox" name="colonnes[]" value="{{ $key }}"
                                {{ in_array($key, $colonnesChoisies) ? 'checked' : '' }}>
                            {{ $colonnes[$key] }}
                        </label>
                    @endif
                @endforeach

                {{-- 💰 Paiements --}}
                <div style="width:100%;font-size:10px;font-weight:700;color:#ca8a04;margin-top:6px;">💰 Paiements</div>
                @foreach(['prix_superficie','prix_technique','prix_logistique','prix_morcellement','paye_superficie','paye_technique','paye_logistique','paye_morcellement','total_paye','paiement_periode','total_reste','progression'] as $key)
                    @if(isset($colonnes[$key]))
                        <label style="font-size:11px;background:#f1f5f9;padding:3px 8px;border-radius:6px;cursor:pointer;{{ $key==='paiement_periode' ? 'border:1px solid #0d6efd;' : '' }}">
                            <input type="checkbox" name="colonnes[]" value="{{ $key }}"
                                {{ in_array($key, $colonnesChoisies) ? 'checked' : '' }}>
                            {{ $colonnes[$key] }}
                        </label>
                    @endif
                @endforeach

                {{-- 📅 Dates --}}
                <div style="width:100%;font-size:10px;font-weight:700;color:#dc2626;margin-top:6px;">📅 Dates</div>
                @foreach(['date_implantation','date_dossier_tech','date_morcellement'] as $key)
                    @if(isset($colonnes[$key]))
                        <label style="font-size:11px;background:#f1f5f9;padding:3px 8px;border-radius:6px;cursor:pointer;">
                            <input type="checkbox" name="colonnes[]" value="{{ $key }}"
                                {{ in_array($key, $colonnesChoisies) ? 'checked' : '' }}>
                            {{ $colonnes[$key] }}
                        </label>
                    @endif
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

    {{-- ✅ KPI période (affiché uniquement si période sélectionnée) --}}
    @if(request('date_debut') || request('date_fin'))
        <div class="kpi-box" style="border-top-color:#0d6efd;">
            <div class="val" style="color:#0d6efd;">{{ number_format($totaux['paiement_periode_total'],0,',',' ') }}</div>
            <div class="lbl">Payé période FCFA</div>
        </div>
    @endif

    <div class="kpi-box"><div class="val" style="color:var(--danger)">{{ number_format($totaux['total_reste'],0,',',' ') }}</div><div class="lbl">Reste FCFA</div></div>
    <div class="kpi-box"><div class="val">{{ $totaux['avg_progression'] }}%</div><div class="lbl">Moy. paiement</div></div>
</div>

{{-- ═══ RECAP PAIEMENTS ═══ --}}
<div class="recap-section">
    <h5 style="font-weight:800;color:#1e3a5f;margin-bottom:16px;">💰 Récapitulatif des paiements</h5>
    <div class="recap-grid">

        <div class="recap-card superficie">
            <h6 style="color:#0d6efd;">📁 Paiement Parcelle</h6>
            <div class="recap-row"><span class="lbl">Référence</span><span class="val">{{ number_format($totaux['prix_superficie_total'],0,',',' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">Payé</span><span class="val" style="color:#16a34a;">{{ number_format($totaux['paye_superficie_total'],0,',',' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">Reste</span><span class="val" style="color:#dc2626;">{{ number_format(max(0, $totaux['prix_superficie_total'] - $totaux['paye_superficie_total']),0,',',' ') }} FCFA</span></div>
            @php $pctSup = $totaux['prix_superficie_total'] > 0 ? min(100, round(($totaux['paye_superficie_total']/$totaux['prix_superficie_total'])*100)) : 0; @endphp
            <div class="recap-progress"><div class="recap-progress-bar" style="width:{{ $pctSup }}%;background:#0d6efd;"></div></div>
            <div style="font-size:11px;text-align:right;color:#0d6efd;font-weight:700;">{{ $pctSup }}%</div>
        </div>

        <div class="recap-card technique">
            <h6 style="color:#ea580c;">🛠️ Paiement Technique</h6>
            <div class="recap-row"><span class="lbl">Référence</span><span class="val">{{ number_format($totaux['prix_technique_total'],0,',',' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">Payé</span><span class="val" style="color:#16a34a;">{{ number_format($totaux['paye_technique_total'],0,',',' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">Reste</span><span class="val" style="color:#dc2626;">{{ number_format(max(0, $totaux['prix_technique_total'] - $totaux['paye_technique_total']),0,',',' ') }} FCFA</span></div>
            @php $pctTech = $totaux['prix_technique_total'] > 0 ? min(100, round(($totaux['paye_technique_total']/$totaux['prix_technique_total'])*100)) : 0; @endphp
            <div class="recap-progress"><div class="recap-progress-bar" style="width:{{ $pctTech }}%;background:#ea580c;"></div></div>
            <div style="font-size:11px;text-align:right;color:#ea580c;font-weight:700;">{{ $pctTech }}%</div>
        </div>

        <div class="recap-card logistique">
            <h6 style="color:#7c3aed;">🚗 Paiement Logistique</h6>
            <div class="recap-row"><span class="lbl">Référence</span><span class="val">{{ number_format($totaux['prix_logistique_total'],0,',',' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">Payé</span><span class="val" style="color:#16a34a;">{{ number_format($totaux['paye_logistique_total'],0,',',' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">Reste</span><span class="val" style="color:#dc2626;">{{ number_format(max(0, $totaux['prix_logistique_total'] - $totaux['paye_logistique_total']),0,',',' ') }} FCFA</span></div>
            @php $pctLog = $totaux['prix_logistique_total'] > 0 ? min(100, round(($totaux['paye_logistique_total']/$totaux['prix_logistique_total'])*100)) : 0; @endphp
            <div class="recap-progress"><div class="recap-progress-bar" style="width:{{ $pctLog }}%;background:#7c3aed;"></div></div>
            <div style="font-size:11px;text-align:right;color:#7c3aed;font-weight:700;">{{ $pctLog }}%</div>
        </div>

        <div class="recap-card morcellement">
            <h6 style="color:#ca8a04;">✂️ Paiement Morcellement</h6>
            <div class="recap-row"><span class="lbl">Référence</span><span class="val">{{ number_format($totaux['prix_morcellement_total'],0,',',' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">Payé</span><span class="val" style="color:#16a34a;">{{ number_format($totaux['paye_morcellement_total'],0,',',' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">Reste</span><span class="val" style="color:#dc2626;">{{ number_format(max(0, $totaux['prix_morcellement_total'] - $totaux['paye_morcellement_total']),0,',',' ') }} FCFA</span></div>
            @php $pctMor = $totaux['prix_morcellement_total'] > 0 ? min(100, round(($totaux['paye_morcellement_total']/$totaux['prix_morcellement_total'])*100)) : 0; @endphp
            <div class="recap-progress"><div class="recap-progress-bar" style="width:{{ $pctMor }}%;background:#ca8a04;"></div></div>
            <div style="font-size:11px;text-align:right;color:#ca8a04;font-weight:700;">{{ $pctMor }}%</div>
        </div>

    </div>

    <div class="recap-total">
        <div style="font-size:12px;opacity:0.8;margin-bottom:6px;">TOTAL GÉNÉRAL</div>
        <div>{{ number_format($totaux['total_paye'],0,',',' ') }} FCFA payé / {{ number_format($totaux['prix_total'],0,',',' ') }} FCFA</div>
        <div style="font-size:14px;margin-top:6px;">
            Reste à payer : {{ number_format($totaux['total_reste'],0,',',' ') }} FCFA
            <span style="margin-left:12px;background:rgba(255,255,255,0.2);padding:2px 10px;border-radius:10px;">
                {{ $totaux['avg_progression'] }}%
            </span>
        </div>

        {{-- ✅ Bandeau période --}}
        @if(request('date_debut') || request('date_fin'))
            <div style="margin-top:12px;background:rgba(255,255,255,0.15);padding:10px;border-radius:8px;">
                <div style="font-size:12px;opacity:0.9;">
                    💵 Payé sur la période
                    @if(request('date_debut') && request('date_fin'))
                        (du {{ \Carbon\Carbon::parse(request('date_debut'))->format('d/m/Y') }} au {{ \Carbon\Carbon::parse(request('date_fin'))->format('d/m/Y') }})
                    @elseif(request('date_debut'))
                        (à partir du {{ \Carbon\Carbon::parse(request('date_debut'))->format('d/m/Y') }})
                    @elseif(request('date_fin'))
                        (jusqu'au {{ \Carbon\Carbon::parse(request('date_fin'))->format('d/m/Y') }})
                    @endif
                </div>
                <div style="font-size:20px;font-weight:900;margin-top:4px;">
                    {{ number_format($totaux['paiement_periode_total'],0,',',' ') }} FCFA
                </div>
            </div>
        @endif
    </div>
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
        <button type="submit" style="background:#dc3545;color:white;padding:8px 16px;border-radius:8px;border:none;font-weight:600;font-size:13px;cursor:pointer;">📄 PDF</button>
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
        <button type="submit" style="background:#28a745;color:white;padding:8px 16px;border-radius:8px;border:none;font-weight:600;font-size:13px;cursor:pointer;">📊 Excel (CSV)</button>
    </form>
</div>

{{-- ═══ TABLEAU ═══ --}}
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
            $payeSuperficie   = $d->paiements->sum('montant');
            $payeTechnique    = $d->paiementsTechniques->sum('montant');
            $payeLogistique   = $d->paiementsLogistiques?->sum('montant') ?? 0;
            $payeMorcellement = $d->paiementsMorcellements->sum('montant');
            $totalPaye        = $payeSuperficie + $payeTechnique + $payeLogistique + $payeMorcellement;

            $prixSuperficie   = $d->prix_superficie ?? 0;
            $prixTechnique    = $d->prix_technique ?? 0;
            $prixLogistique   = $d->prix_logistique ?? 0;
            $prixMorcellement = $d->prix_morcellement ?? 0;
            $totalPrix        = $prixSuperficie + $prixTechnique + $prixLogistique + $prixMorcellement;
            $totalReste       = max(0, $totalPrix - $totalPaye);
            $progression      = $totalPrix > 0 ? round(($totalPaye / $totalPrix) * 100) : 0;
            $progColor        = $progression < 40 ? '#dc3545' : ($progression < 75 ? '#fd7e14' : '#28a745');

            $affectations = $d->affectations;
            $nbLots = $affectations->count();

            // ✅ Paiement période + détails
            $payePeriode = $paiementsPeriode[$d->id] ?? 0;
            $detailsPeriode = $detailsPaiementsPeriode[$d->id] ?? [];
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            @foreach($colonnesChoisies as $col)
            <td>
                @switch($col)

                    {{-- 👤 CLIENT --}}
                    @case('client') <strong>{{ $d->client?->name ?? '-' }}</strong> @break
                    @case('telephone') {{ $d->client?->phone ?? '-' }} @break
                    @case('sexe')
                        @if($d->client?->sexe === 'masculin') 👨 Masculin
                        @elseif($d->client?->sexe === 'feminin') 👩 Féminin
                        @else —
                        @endif
                        @break

                    {{-- 📁 DOSSIER --}}
                    @case('nom_dossier')        {{ $d->nom_dossier ?? '-' }} @break
                    @case('grand_site_dossier') {{ $d->grandSite?->nom ?? '-' }} @break
                    @case('superficie')         {{ $d->superficie_voulue ? number_format($d->superficie_voulue,0,',',' ') . ' m²' : '-' }} @break
                    @case('direction')
                        {{ match($d->direction) {
                            'baffoussam' => 'Baffoussam',
                            'bagante'    => 'Bagante',
                            'direction_generale' => 'Dir. Générale',
                            default => $d->direction ?? '-'
                        } }}
                        @break
                    @case('statut_dossier')
                        @php
                            $statut = $d->etape_actuelle ?? 'none';
                            $statutLabel = match($statut) {
                                'implantation_prevue' => 'Implantation prévue',
                                'deja_implante'       => 'Déjà implanté',
                                'dossier_technique'   => 'Dossier technique',
                                'morcellement'        => 'Morcellement',
                                default               => 'Non commencé',
                            };
                            $statutColor = match($statut) {
                                'morcellement'        => ['bg' => '#dcfce7', 'text' => '#15803d'],
                                'dossier_technique'   => ['bg' => '#fef9c3', 'text' => '#854d0e'],
                                'deja_implante'       => ['bg' => '#dbeafe', 'text' => '#1d4ed8'],
                                'implantation_prevue' => ['bg' => '#ede9fe', 'text' => '#7c3aed'],
                                default               => ['bg' => '#f1f5f9', 'text' => '#475569'],
                            };
                        @endphp
                        <span style="font-size:10px;padding:2px 8px;border-radius:4px;background:{{ $statutColor['bg'] }};color:{{ $statutColor['text'] }};font-weight:600;">
                            {{ $statutLabel }}
                        </span>
                        @break

                    {{-- 👥 ACTEURS --}}
                    @case('commercial')   {{ $d->commercial?->name ?? '-' }} @break
                    @case('facilitateur') {{ $d->facilitateur?->nom ?? '-' }} @break
                    @case('chauffeur')    {{ $d->conducteur?->nom ?? '-' }} @break
                    @case('agent')        {{ $d->agentCommercial?->nom ?? '-' }} @break

                    {{-- 🗺️ AFFECTATIONS --}}
                    @case('grand_site')
                        @if($nbLots > 0) {{ $affectations->pluck('grandSite.nom')->filter()->unique()->implode(', ') }} @else — @endif
                        @break
                    @case('site')
                        @if($nbLots > 0) {{ $affectations->pluck('grandSite.nom')->filter()->unique()->implode(', ') }} @else — @endif
                        @break
                    @case('tf')
                        @if($nbLots > 0) {{ $affectations->pluck('bloc.tf.title')->filter()->unique()->implode(', ') }} @else — @endif
                        @break
                    @case('bloc')
                        @if($nbLots > 0) {{ $affectations->pluck('bloc.code')->filter()->unique()->implode(', ') }} @else — @endif
                        @break
                    @case('lot')
                        @if($nbLots > 0)
                            @foreach($affectations as $aff)
                                @if($aff->lot) <span class="lot-badge">{{ strtoupper($aff->lot->numero) }}</span> @endif
                            @endforeach
                            @if($nbLots > 1) <span class="lot-count">{{ $nbLots }} lots</span> @endif
                        @else
                            <span style="color:#94a3b8;font-style:italic;">Sans lot</span>
                        @endif
                        @break

                    {{-- 💰 PAIEMENTS --}}
                    @case('prix_superficie')    <span style="color:#0d6efd;">{{ number_format($prixSuperficie,0,',',' ') }}</span> @break
                    @case('prix_technique')     <span style="color:#ea580c;">{{ number_format($prixTechnique,0,',',' ') }}</span> @break
                    @case('prix_logistique')    <span style="color:#7c3aed;">{{ number_format($prixLogistique,0,',',' ') }}</span> @break
                    @case('prix_morcellement')  <span style="color:#ca8a04;">{{ number_format($prixMorcellement,0,',',' ') }}</span> @break
                    @case('paye_superficie')    <span style="color:#16a34a;font-weight:600;">{{ number_format($payeSuperficie,0,',',' ') }}</span> @break
                    @case('paye_technique')     <span style="color:#16a34a;font-weight:600;">{{ number_format($payeTechnique,0,',',' ') }}</span> @break
                    @case('paye_logistique')    <span style="color:#16a34a;font-weight:600;">{{ number_format($payeLogistique,0,',',' ') }}</span> @break
                    @case('paye_morcellement')  <span style="color:#16a34a;font-weight:600;">{{ number_format($payeMorcellement,0,',',' ') }}</span> @break
                    @case('total_paye')         <span style="color:#16a34a;font-weight:700;">{{ number_format($totalPaye,0,',',' ') }}</span> @break

                    {{-- ✅ Paiement période --}}
                    @case('paiement_periode')
                        @if($payePeriode > 0)
                            <span class="periode-badge">{{ number_format($payePeriode,0,',',' ') }} FCFA</span>
                            @if(!empty($detailsPeriode))
                                <details class="periode-details" style="margin-top:2px;">
                                    <summary>{{ count($detailsPeriode) }} paiement(s)</summary>
                                    <ul>
                                        @foreach($detailsPeriode as $p)
                                            <li>
                                                {{ \Carbon\Carbon::parse($p['date'])->format('d/m/Y') }} —
                                                {{ number_format($p['montant'],0,',',' ') }} FCFA
                                                <em style="color:#94a3b8;">({{ $p['type'] }})</em>
                                            </li>
                                        @endforeach
                                    </ul>
                                </details>
                            @endif
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                        @break

                    @case('total_reste') <span style="color:#dc3545;font-weight:700;">{{ number_format($totalReste,0,',',' ') }}</span> @break
                    @case('progression')
                        <div style="display:flex;align-items:center;gap:4px;">
                            <div style="width:50px;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                                <div style="width:{{ $progression }}%;height:100%;background:{{ $progColor }};border-radius:3px;"></div>
                            </div>
                            <span style="font-size:10px;color:{{ $progColor }};font-weight:600;">{{ $progression }}%</span>
                        </div>
                        @break

                    {{-- 📅 DATES --}}
                    @case('date_implantation') {{ $d->date_implantation_prevue?->format('d/m/Y') ?? '-' }} @break
                    @case('date_dossier_tech') {{ $d->date_dossier_technique?->format('d/m/Y') ?? '-' }} @break
                    @case('date_morcellement') {{ $d->date_morcellement?->format('d/m/Y') ?? '-' }} @break

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
                    @case('lot')                {{ $totaux['nb_dossiers'] }} dossiers @break
                    @case('superficie')         {{ number_format($totaux['superficie_totale'],0,',',' ') }} m² @break
                    @case('prix_superficie')    {{ number_format($totaux['prix_superficie_total'],0,',',' ') }} @break
                    @case('prix_technique')     {{ number_format($totaux['prix_technique_total'],0,',',' ') }} @break
                    @case('prix_logistique')    {{ number_format($totaux['prix_logistique_total'],0,',',' ') }} @break
                    @case('prix_morcellement')  {{ number_format($totaux['prix_morcellement_total'],0,',',' ') }} @break
                    @case('paye_superficie')    {{ number_format($totaux['paye_superficie_total'],0,',',' ') }} @break
                    @case('paye_technique')     {{ number_format($totaux['paye_technique_total'],0,',',' ') }} @break
                    @case('paye_logistique')    {{ number_format($totaux['paye_logistique_total'],0,',',' ') }} @break
                    @case('paye_morcellement')  {{ number_format($totaux['paye_morcellement_total'],0,',',' ') }} @break
                    @case('total_paye')         {{ number_format($totaux['total_paye'],0,',',' ') }} @break
                    @case('paiement_periode')
                        @if($totaux['paiement_periode_total'] > 0)
                            {{ number_format($totaux['paiement_periode_total'],0,',',' ') }} FCFA
                        @else
                            —
                        @endif
                        @break
                    @case('total_reste')        {{ number_format($totaux['total_reste'],0,',',' ') }} @break
                    @case('progression')        {{ $totaux['avg_progression'] }}% moy. @break
                    @default —
                @endswitch
            </td>
            @endforeach
        </tr>
    </tfoot>
</table>
</div>

@else
    <div class="alert alert-info" style="text-align:center;padding:40px;background:white;border-radius:12px;">
        <div style="font-size:3rem;margin-bottom:12px;">📭</div>
        <h4 style="color:#1e3a5f;font-weight:800;margin-bottom:8px;">Aucun dossier trouvé</h4>
        <p style="color:#64748b;font-size:13px;margin:0;">
            Aucun dossier ne correspond à vos critères. Modifiez ou réinitialisez les filtres.
        </p>
    </div>
@endif

@endsection

@section('scripts')
<script>
document.getElementById('form-pdf')?.addEventListener('submit', function() {
    document.getElementById('pdf-titre').value = document.getElementById('input-titre')?.value ?? '';
    document.getElementById('pdf-desc').value  = document.getElementById('input-desc')?.value  ?? '';
});
</script>
@endsection