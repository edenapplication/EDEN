<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:DejaVu Sans,sans-serif; font-size:8px; color:#1e293b; }

/* ✅ EN-TÊTE COLORÉ BLEU → VIOLET → ROUGE */
.page-header {
    background: #1e3a5f;
    color: white;
    padding: 16px 20px;
    margin-bottom: 14px;
    border-left: 8px solid #ef4444;
}
.rapport-titre {
    font-size: 18px;
    font-weight: bold;
    margin-bottom: 4px;
    letter-spacing: 0.3px;
}
.rapport-description {
    font-size: 11px;
    opacity: 0.88;
    font-style: italic;
    margin-bottom: 6px;
}
.rapport-meta { font-size: 8px; opacity: 0.65; }

table { width:100%; border-collapse:collapse; margin-bottom:8px; }
thead tr { background:#1e3a5f; color:white; }
thead th { padding:4px 3px; font-size:7px; font-weight:bold; text-align:left; white-space:nowrap; }
tbody tr:nth-child(even) { background:#f8fafc; }
tbody td { padding:3px; border-bottom:1px solid #e2e8f0; font-size:7px; }
tfoot tr { background:#1e3a5f; color:white; }
tfoot td { padding:4px 3px; font-size:7px; font-weight:bold; }
.footer { text-align:right; font-size:6px; color:#94a3b8; margin-top:6px; }
.prog-wrap { display:inline-block; width:40px; height:4px; background:#e2e8f0; border-radius:2px; vertical-align:middle; }
.prog-fill  { height:100%; border-radius:2px; }
</style>
</head>
<body>

{{-- ✅ TITRE ET DESCRIPTION EN GRAND --}}
<div class="page-header">
    <div class="rapport-titre">{{ $rapport_titre ?? 'Rapport EDEN GROUP' }}</div>
    @if(!empty($rapport_description))
        <div class="rapport-description">{{ $rapport_description }}</div>
    @endif
    <div class="rapport-meta">
        Généré le {{ now()->format('d/m/Y à H:i') }}
        — {{ $dossiers->count() }} dossiers
        ({{ $dossiers->filter(fn($d)=>$d->lots->count()>0)->count() }} avec lot,
        {{ $dossiers->filter(fn($d)=>$d->lots->count()===0)->count() }} sans lot)
    </div>
</div>

{{-- KPI --}}
<table style="margin-bottom:10px;">
    <tr style="background:#f0f7ff;">
        <td style="padding:6px 8px;text-align:center;border:1px solid #e2e8f0;">
            <div style="font-size:11px;font-weight:bold;color:#1e3a5f;">{{ $totaux['nb_dossiers'] }}</div>
            <div style="font-size:6px;color:#64748b;">DOSSIERS</div>
        </td>
        <td style="padding:6px 8px;text-align:center;border:1px solid #e2e8f0;">
            <div style="font-size:11px;font-weight:bold;color:#1e3a5f;">{{ $totaux['nb_clients'] }}</div>
            <div style="font-size:6px;color:#64748b;">CLIENTS</div>
        </td>
        <td style="padding:6px 8px;text-align:center;border:1px solid #e2e8f0;">
            <div style="font-size:11px;font-weight:bold;color:#15803d;">{{ $totaux['nb_avec_lot'] }}</div>
            <div style="font-size:6px;color:#64748b;">AVEC LOT</div>
        </td>
        <td style="padding:6px 8px;text-align:center;border:1px solid #e2e8f0;">
            <div style="font-size:11px;font-weight:bold;color:#b91c1c;">{{ $totaux['nb_sans_lot'] }}</div>
            <div style="font-size:6px;color:#64748b;">SANS LOT</div>
        </td>
        <td style="padding:6px 8px;text-align:center;border:1px solid #e2e8f0;">
            <div style="font-size:11px;font-weight:bold;color:#15803d;">{{ number_format($totaux['prix_total'],0,',',' ') }}</div>
            <div style="font-size:6px;color:#64748b;">PRIX TOTAL FCFA</div>
        </td>
        <td style="padding:6px 8px;text-align:center;border:1px solid #e2e8f0;">
            <div style="font-size:11px;font-weight:bold;color:#1d4ed8;">{{ number_format($totaux['total_paye'],0,',',' ') }}</div>
            <div style="font-size:6px;color:#64748b;">PAYÉ FCFA</div>
        </td>
        <td style="padding:6px 8px;text-align:center;border:1px solid #e2e8f0;">
            <div style="font-size:11px;font-weight:bold;color:#b91c1c;">{{ number_format($totaux['total_reste'],0,',',' ') }}</div>
            <div style="font-size:6px;color:#64748b;">RESTE FCFA</div>
        </td>
        <td style="padding:6px 8px;text-align:center;border:1px solid #e2e8f0;">
            <div style="font-size:11px;font-weight:bold;color:#1e3a5f;">{{ $totaux['avg_progression'] }}%</div>
            <div style="font-size:6px;color:#64748b;">MOY. PAIEMENT</div>
        </td>
    </tr>
</table>

{{-- TABLEAU --}}
<table>
    <thead>
        <tr>
            <th>#</th>
            @foreach($colonnesChoisies as $col)
                <th>{{ ['grand_site'=>'Grand Site','site'=>'Site','tf'=>'TF','lot'=>'Lot','client'=>'Client','telephone'=>'Tél.','commercial'=>'Comm.','facilitateur'=>'Facil.','chauffeur'=>'Chauff.','agent'=>'Agent','direction'=>'Direction','grand_site_dossier'=>'GS voulu','superficie'=>'Sup.','prix'=>'Prix','paye'=>'Payé','reste'=>'Reste','date_prevue'=>'D.Prév.','date_confirmee'=>'D.Conf.','date_morcel'=>'D.Morcel.','statut_dossier'=>'Statut','progression'=>'Prog.','nom_dossier'=>'Dossier'][$col] ?? $col }}</th>
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
            <td>{{ $i+1 }}</td>
            @foreach($colonnesChoisies as $col)
            <td>
                @switch($col)
                    @case('grand_site')    {{ $lot?->tf?->site?->grandSite?->nom ?? '—' }} @break
                    @case('site')          {{ $lot?->tf?->site?->name ?? '—' }} @break
                    @case('tf')            {{ $lot?->tf?->title ?? '—' }} @break
                    @case('lot')           {{ $lot ? strtoupper($lot->code) : 'Sans lot' }} @break
                    @case('client')        {{ $d->client?->name ?? '-' }} @break
                    @case('telephone')     {{ $d->client?->phone ?? '-' }} @break
                    @case('commercial')    {{ $d->commercial?->name ?? '-' }} @break
                    @case('facilitateur')  {{ $d->facilitateur?->nom ?? '-' }} @break
                    @case('chauffeur')     {{ $d->conducteur?->nom ?? '-' }} @break
                    @case('agent')         {{ $d->agentCommercial?->nom ?? '-' }} @break
                    @case('direction')     {{ match($d->direction) { 'baffoussam'=>'Baffoussam','bagante'=>'Bagante','direction_generale'=>'Dir. Gén.',default=>$d->direction??'-' } }} @break
                    @case('grand_site_dossier') {{ $d->grandSite?->nom ?? '-' }} @break
                    @case('superficie')    {{ $d->superficie_voulue ?? '-' }} @break
                    @case('prix')          {{ $d->prix_superficie ? number_format($d->prix_superficie,0,',',' ') : '-' }} @break
                    @case('paye')          {{ number_format($paye,0,',',' ') }} @break
                    @case('reste')         {{ number_format($reste,0,',',' ') }} @break
                    @case('date_prevue')   {{ $lot?->date_prevue?->format('d/m/Y') ?? '-' }} @break
                    @case('date_confirmee'){{ $lot?->date_confirmee?->format('d/m/Y') ?? '-' }} @break
                    @case('date_morcel')   {{ $lot?->date_morcellement?->format('d/m/Y') ?? '-' }} @break
                    @case('statut_dossier'){{ $statut === 'none' ? 'Non comm.' : ($statut === 'en_cours' ? 'En cours' : 'Complet') }} @break
                    @case('progression')
                        <div class="prog-wrap"><div class="prog-fill" style="width:{{ $prog }}%;background:{{ $progColor }};"></div></div> {{ $prog }}%
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
                    @case('lot')        {{ $totaux['nb_dossiers'] }} @break
                    @case('superficie') {{ number_format($totaux['superficie_totale'],0,',',' ') }} @break
                    @case('prix')       {{ number_format($totaux['prix_total'],0,',',' ') }} @break
                    @case('paye')       {{ number_format($totaux['total_paye'],0,',',' ') }} @break
                    @case('reste')      {{ number_format($totaux['total_reste'],0,',',' ') }} @break
                    @case('progression'){{ $totaux['avg_progression'] }}% @break
                    @default —
                @endswitch
            </td>
            @endforeach
        </tr>
    </tfoot>
</table>

<div class="footer">EDEN GROUP — {{ now()->format('d/m/Y à H:i') }}</div>
</body>
</html>