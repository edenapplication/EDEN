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

/* ═══ RÉCAP PAIEMENTS ═══ */
.recap-section { margin-bottom:14px; }
.recap-title {
    font-size:11px;
    font-weight:bold;
    color:#1e3a5f;
    margin-bottom:8px;
    padding-bottom:4px;
    border-bottom:2px solid #e2e8f0;
}
.recap-table {
    width:100%;
    border-collapse:collapse;
    margin-bottom:10px;
}
.recap-table th {
    padding:6px 8px;
    font-size:7px;
    font-weight:bold;
    text-align:left;
    border:1px solid #e2e8f0;
    background:#f8fafc;
    color:#1e3a5f;
}
.recap-table td {
    padding:6px 8px;
    font-size:7px;
    border:1px solid #e2e8f0;
}
.recap-table .row-total {
    background:#1e3a5f;
    color:white;
    font-weight:bold;
}
.recap-table .row-total td {
    border-color:#1e3a5f;
    font-size:8px;
}
.recap-table .montant { text-align:right; font-family:DejaVu Sans Mono,monospace; }
.recap-table .paye { color:#15803d; font-weight:bold; }
.recap-table .reste { color:#b91c1c; font-weight:bold; }
.recap-table .pct { text-align:center; font-weight:bold; }
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
        ({{ $dossiers->filter(fn($d)=>$d->affectations->count()>0)->count() }} avec lot,
        {{ $dossiers->filter(fn($d)=>$d->affectations->count()===0)->count() }} sans lot)
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

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- ✅ RÉCAPITULATIF DES PAIEMENTS --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="recap-section">
    <div class="recap-title">💰 RÉCAPITULATIF DES PAIEMENTS</div>

    <table class="recap-table">
        <thead>
            <tr>
                <th style="width:22%;">Type de paiement</th>
                <th style="width:16%;text-align:right;">Référence (FCFA)</th>
                <th style="width:16%;text-align:right;">Payé (FCFA)</th>
                <th style="width:16%;text-align:right;">Reste (FCFA)</th>
                <th style="width:10%;text-align:center;">Progression</th>
                <th style="width:20%;text-align:center;">Barre</th>
            </tr>
        </thead>
        <tbody>
            {{-- Paiement Parcelle --}}
            @php
                $resteSup = max(0, $totaux['prix_superficie_total'] - $totaux['paye_superficie_total']);
                $pctSup = $totaux['prix_superficie_total'] > 0 ? min(100, round(($totaux['paye_superficie_total']/$totaux['prix_superficie_total'])*100)) : 0;
            @endphp
            <tr>
                <td style="font-weight:bold;color:#0d6efd;">📁 Paiement Parcelle</td>
                <td class="montant">{{ number_format($totaux['prix_superficie_total'],0,',',' ') }}</td>
                <td class="montant paye">{{ number_format($totaux['paye_superficie_total'],0,',',' ') }}</td>
                <td class="montant reste">{{ number_format($resteSup,0,',',' ') }}</td>
                <td class="pct" style="color:#0d6efd;">{{ $pctSup }}%</td>
                <td>
                    <div style="width:100%;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                        <div style="width:{{ $pctSup }}%;height:100%;background:#0d6efd;border-radius:3px;"></div>
                    </div>
                </td>
            </tr>

            {{-- Paiement Technique --}}
            @php
                $resteTech = max(0, $totaux['prix_technique_total'] - $totaux['paye_technique_total']);
                $pctTech = $totaux['prix_technique_total'] > 0 ? min(100, round(($totaux['paye_technique_total']/$totaux['prix_technique_total'])*100)) : 0;
            @endphp
            <tr>
                <td style="font-weight:bold;color:#ea580c;">🛠️ Paiement Technique</td>
                <td class="montant">{{ number_format($totaux['prix_technique_total'],0,',',' ') }}</td>
                <td class="montant paye">{{ number_format($totaux['paye_technique_total'],0,',',' ') }}</td>
                <td class="montant reste">{{ number_format($resteTech,0,',',' ') }}</td>
                <td class="pct" style="color:#ea580c;">{{ $pctTech }}%</td>
                <td>
                    <div style="width:100%;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                        <div style="width:{{ $pctTech }}%;height:100%;background:#ea580c;border-radius:3px;"></div>
                    </div>
                </td>
            </tr>

            {{-- Paiement Logistique --}}
            @php
                $resteLog = max(0, $totaux['prix_logistique_total'] - $totaux['paye_logistique_total']);
                $pctLog = $totaux['prix_logistique_total'] > 0 ? min(100, round(($totaux['paye_logistique_total']/$totaux['prix_logistique_total'])*100)) : 0;
            @endphp
            <tr>
                <td style="font-weight:bold;color:#7c3aed;">🚗 Paiement Logistique</td>
                <td class="montant">{{ number_format($totaux['prix_logistique_total'],0,',',' ') }}</td>
                <td class="montant paye">{{ number_format($totaux['paye_logistique_total'],0,',',' ') }}</td>
                <td class="montant reste">{{ number_format($resteLog,0,',',' ') }}</td>
                <td class="pct" style="color:#7c3aed;">{{ $pctLog }}%</td>
                <td>
                    <div style="width:100%;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                        <div style="width:{{ $pctLog }}%;height:100%;background:#7c3aed;border-radius:3px;"></div>
                    </div>
                </td>
            </tr>

            {{-- Paiement Morcellement --}}
            @php
                $resteMor = max(0, $totaux['prix_morcellement_total'] - $totaux['paye_morcellement_total']);
                $pctMor = $totaux['prix_morcellement_total'] > 0 ? min(100, round(($totaux['paye_morcellement_total']/$totaux['prix_morcellement_total'])*100)) : 0;
            @endphp
            <tr>
                <td style="font-weight:bold;color:#ca8a04;">✂️ Paiement Morcellement</td>
                <td class="montant">{{ number_format($totaux['prix_morcellement_total'],0,',',' ') }}</td>
                <td class="montant paye">{{ number_format($totaux['paye_morcellement_total'],0,',',' ') }}</td>
                <td class="montant reste">{{ number_format($resteMor,0,',',' ') }}</td>
                <td class="pct" style="color:#ca8a04;">{{ $pctMor }}%</td>
                <td>
                    <div style="width:100%;height:6px;background:#e2e8f0;border-radius:3px;overflow:hidden;">
                        <div style="width:{{ $pctMor }}%;height:100%;background:#ca8a04;border-radius:3px;"></div>
                    </div>
                </td>
            </tr>

            {{-- TOTAL GÉNÉRAL --}}
            <tr class="row-total">
                <td>📊 TOTAL GÉNÉRAL</td>
                <td class="montant">{{ number_format($totaux['prix_total'],0,',',' ') }}</td>
                <td class="montant">{{ number_format($totaux['total_paye'],0,',',' ') }}</td>
                <td class="montant">{{ number_format($totaux['total_reste'],0,',',' ') }}</td>
                <td class="pct">{{ $totaux['avg_progression'] }}%</td>
                <td>
                    <div style="width:100%;height:6px;background:rgba(255,255,255,0.3);border-radius:3px;overflow:hidden;">
                        <div style="width:{{ $totaux['avg_progression'] }}%;height:100%;background:#ffffff;border-radius:3px;"></div>
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{-- ═══════════════════════════════════════════════════════════════════ --}}
{{-- ✅ TABLEAU DÉTAILLÉ DES DOSSIERS --}}
{{-- ═══════════════════════════════════════════════════════════════════ --}}
<div class="recap-title" style="margin-top:16px;">📋 DÉTAIL DES DOSSIERS</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            @foreach($colonnesChoisies as $col)
                <th>{{ [
                    'grand_site'=>'Grand Site',
                    'site'=>'Site',
                    'tf'=>'TF',
                    'bloc'=>'Bloc',
                    'lot'=>'Lot',
                    'client'=>'Client',
                    'telephone'=>'Tél.',
                    'sexe'=>'Sexe',
                    'commercial'=>'Comm.',
                    'facilitateur'=>'Facil.',
                    'chauffeur'=>'Chauff.',
                    'agent'=>'Agent',
                    'direction'=>'Direction',
                    'grand_site_dossier'=>'GS voulu',
                    'superficie'=>'Sup.',
                    'prix_superficie'=>'Prix Sup.',
                    'prix_technique'=>'Prix Tech.',
                    'prix_logistique'=>'Prix Log.',
                    'prix_morcellement'=>'Prix Mor.',
                    'paye_superficie'=>'Payé Sup.',
                    'paye_technique'=>'Payé Tech.',
                    'paye_logistique'=>'Payé Log.',
                    'paye_morcellement'=>'Payé Mor.',
                    'total_paye'=>'Total Payé',
                    'total_reste'=>'Total Reste',
                    'progression'=>'Prog.',
                    'date_implantation'=>'D.Implant.',
                    'date_dossier_tech'=>'D.Tech.',
                    'date_morcellement'=>'D.Morcel.',
                    'statut_dossier'=>'Statut',
                    'nom_dossier'=>'Dossier'
                ][$col] ?? $col }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
    @foreach($dossiers as $i => $d)
        @php
            // ✅ Calculs réels
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

            // ✅ Affectation réelle
            $affectation = $d->affectations->first();
            $lot         = $affectation?->lot;
            $bloc        = $affectation?->bloc;
        @endphp
        <tr>
            <td>{{ $i+1 }}</td>
            @foreach($colonnesChoisies as $col)
            <td>
                @switch($col)
                    @case('grand_site')         {{ $affectation?->grandSite?->nom ?? '—' }} @break
                    @case('site')               {{ $affectation?->grandSite?->nom ?? '—' }} @break
                    @case('tf')                 {{ $bloc?->tf?->title ?? '—' }} @break
                    @case('bloc')               {{ $bloc?->code ?? '—' }} @break
                    @case('lot')                {{ $lot ? strtoupper($lot->numero) : 'Sans lot' }} @break
                    @case('client')             {{ $d->client?->name ?? '-' }} @break
                    @case('telephone')          {{ $d->client?->phone ?? '-' }} @break
                    @case('sexe')
                        @if($d->client?->sexe === 'masculin') Masculin
                        @elseif($d->client?->sexe === 'feminin') Féminin
                        @else —
                        @endif
                        @break
                    @case('commercial')         {{ $d->commercial?->name ?? '-' }} @break
                    @case('facilitateur')       {{ $d->facilitateur?->nom ?? '-' }} @break
                    @case('chauffeur')          {{ $d->conducteur?->nom ?? '-' }} @break
                    @case('agent')              {{ $d->agentCommercial?->nom ?? '-' }} @break
                    @case('direction')          {{ match($d->direction) { 'baffoussam'=>'Baffoussam','bagante'=>'Bagante','direction_generale'=>'Dir. Gén.',default=>$d->direction??'-' } }} @break
                    @case('grand_site_dossier') {{ $d->grandSite?->nom ?? '-' }} @break
                    @case('superficie')         {{ $d->superficie_voulue ?? '-' }} @break
                    @case('prix_superficie')    {{ number_format($prixSuperficie,0,',',' ') }} @break
                    @case('prix_technique')     {{ number_format($prixTechnique,0,',',' ') }} @break
                    @case('prix_logistique')    {{ number_format($prixLogistique,0,',',' ') }} @break
                    @case('prix_morcellement')  {{ number_format($prixMorcellement,0,',',' ') }} @break
                    @case('paye_superficie')    {{ number_format($payeSuperficie,0,',',' ') }} @break
                    @case('paye_technique')     {{ number_format($payeTechnique,0,',',' ') }} @break
                    @case('paye_logistique')    {{ number_format($payeLogistique,0,',',' ') }} @break
                    @case('paye_morcellement')  {{ number_format($payeMorcellement,0,',',' ') }} @break
                    @case('total_paye')         {{ number_format($totalPaye,0,',',' ') }} @break
                    @case('total_reste')        {{ number_format($totalReste,0,',',' ') }} @break
                    @case('progression')
                        <div class="prog-wrap"><div class="prog-fill" style="width:{{ $progression }}%;background:{{ $progColor }};"></div></div> {{ $progression }}%
                        @break
                    @case('date_implantation')  {{ $d->date_implantation_prevue?->format('d/m/Y') ?? '-' }} @break
                    @case('date_dossier_tech')  {{ $d->date_dossier_technique?->format('d/m/Y') ?? '-' }} @break
                    @case('date_morcellement')  {{ $d->date_morcellement?->format('d/m/Y') ?? '-' }} @break
                    @case('statut_dossier')
                        @php
                            $statut = $d->etape_actuelle ?? 'none';
                            echo match($statut) {
                                'implantation_prevue' => 'Implant. prévue',
                                'deja_implante'       => 'Déjà implanté',
                                'dossier_technique'   => 'Dossier tech.',
                                'morcellement'        => 'Morcellement',
                                default               => 'Non commencé',
                            };
                        @endphp
                        @break
                    @case('nom_dossier')        {{ $d->nom_dossier ?? '-' }} @break
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
                    @case('lot')                {{ $totaux['nb_dossiers'] }} @break
                    @case('superficie')         {{ number_format($totaux['superficie_totale'],0,',',' ') }} @break
                    @case('prix_superficie')    {{ number_format($totaux['prix_superficie_total'],0,',',' ') }} @break
                    @case('prix_technique')     {{ number_format($totaux['prix_technique_total'],0,',',' ') }} @break
                    @case('prix_logistique')    {{ number_format($totaux['prix_logistique_total'],0,',',' ') }} @break
                    @case('prix_morcellement')  {{ number_format($totaux['prix_morcellement_total'],0,',',' ') }} @break
                    @case('paye_superficie')    {{ number_format($totaux['paye_superficie_total'],0,',',' ') }} @break
                    @case('paye_technique')     {{ number_format($totaux['paye_technique_total'],0,',',' ') }} @break
                    @case('paye_logistique')    {{ number_format($totaux['paye_logistique_total'],0,',',' ') }} @break
                    @case('paye_morcellement')  {{ number_format($totaux['paye_morcellement_total'],0,',',' ') }} @break
                    @case('total_paye')         {{ number_format($totaux['total_paye'],0,',',' ') }} @break
                    @case('total_reste')        {{ number_format($totaux['total_reste'],0,',',' ') }} @break
                    @case('progression')        {{ $totaux['avg_progression'] }}% @break
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