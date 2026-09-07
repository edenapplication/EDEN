{{-- resources/views/admin/suivi_client/export_pdf.blade.php --}}

<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Export Clients</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 10px; }
        .header { text-align: center; padding: 20px 0; border-bottom: 2px solid #1d4ed8; margin-bottom: 20px; }
        .header h1 { color: #1e3a5f; font-size: 18px; }
        .header p { color: #64748b; font-size: 10px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { 
            background: #1d4ed8; 
            color: white; 
            padding: 8px 6px; 
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            text-align: left;
        }
        td { 
            padding: 5px 6px; 
            border-bottom: 1px solid #e2e8f0;
            font-size: 9px;
            vertical-align: top;
        }
        .even { background: #f8fafc; }
        
        .badge-new { 
            background: #10b981; 
            color: #fff; 
            padding: 1px 6px; 
            border-radius: 10px;
            font-size: 8px;
            font-weight: 700;
        }
        .badge-old { 
            background: #e2e8f0; 
            color: #64748b; 
            padding: 1px 6px; 
            border-radius: 10px;
            font-size: 8px;
        }
        
        .sexe-masculin {
            color: #1d4ed8;
            font-weight: 700;
        }
        .sexe-feminin {
            color: #dc2626;
            font-weight: 700;
        }
        
        .footer { text-align: center; padding-top: 20px; border-top: 1px solid #e2e8f0; font-size: 8px; color: #94a3b8; }
    </style>
</head>
<body>

<div class="header">
    <h1>📋 EDEN GROUP - Liste des clients</h1>
    <p>Exporté le {{ $date_export }} - {{ $total_clients }} client(s)</p>
</div>

<table>
    <thead>
        <tr>
            <th style="width:3%;">#</th>
            <th style="width:12%;">Nom Client</th>
            <th style="width:10%;">Téléphone</th>
            <th style="width:6%;">Sexe</th>  {{-- ✅ AJOUT --}}
            <th style="width:6%;">Statut</th>
            <th style="width:12%;">Dossier</th>
            <th style="width:8%;">Site</th>
            <th style="width:6%;">Superficie</th>
            <th style="width:6%;">Bloc</th>
            <th style="width:6%;">Lot</th>
            <th style="width:5%;">Tech. Payé</th>
            <th style="width:5%;">Morcel. Payé</th>
            <th style="width:5%;">Dossier Payé</th>
            <th style="width:5%;">Logis. Payé</th>
            <th style="width:3%;">%</th>
        </tr>
    </thead>
    <tbody>
        @php $i = 0; @endphp
        @foreach($clients as $client)
            @if($client->dossiers->count() > 0)
                @foreach($client->dossiers as $dossierIndex => $dossier)
                    @php
                        $i++;
                        $tD = $dossier->paiements->sum('montant');
                        $tT = $dossier->paiementsTechniques->sum('montant');
                        $tM = $dossier->paiementsMorcellements->sum('montant');
                        $tL = $dossier->paiementsLogistiques?->sum('montant') ?? 0;
                        $rD = $dossier->prix_superficie ?? 0;
                        $rT = $dossier->prix_technique ?? 0;
                        $rM = $dossier->prix_morcellement ?? 0;
                        $rL = $dossier->prix_logistique ?? 0;
                        
                        $pctD = $rD > 0 ? min(100, round(($tD / $rD) * 100)) : 0;
                        $pctT = $rT > 0 ? min(100, round(($tT / $rT) * 100)) : 0;
                        $pctM = $rM > 0 ? min(100, round(($tM / $rM) * 100)) : 0;
                        $pctL = $rL > 0 ? min(100, round(($tL / $rL) * 100)) : 0;
                        $totalPaye = $tD + $tT + $tL + $tM;
                        $totalRef = $rD + $rT + $rL + $rM;
                        $pctGlobal = $totalRef > 0 ? min(100, round(($totalPaye / $totalRef) * 100)) : 0;
                        
                        $affectations = $dossier->affectations;
                    @endphp
                    <tr class="{{ $dossierIndex % 2 == 0 ? 'even' : '' }}">
                        <td>{{ $i }}</td>
                        <td>
                            {{ $client->name }}
                            @if($client->is_new)
                                <span class="badge-new">NOUVEAU</span>
                            @endif
                        </td>
                        <td>{{ $client->phone ?? '-' }}</td>
                        <td>
                            @if($client->sexe == 'masculin')
                                <span class="sexe-masculin">👨 Masculin</span>
                            @elseif($client->sexe == 'feminin')
                                <span class="sexe-feminin">👩 Féminin</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($client->is_new)
                                <span class="badge-new">🆕</span>
                            @else
                                <span class="badge-old">Ancien</span>
                            @endif
                        </td>
                        <td>{{ $dossier->nom_dossier }}</td>
                        <td>{{ $dossier->grandSite?->nom ?? '-' }}</td>
                        <td>{{ $dossier->superficie_voulue ? number_format($dossier->superficie_voulue, 0, ',', ' ') . ' m²' : '-' }}</td>
                        <td>
                            @if($affectations->count() > 0)
                                {{ $affectations->pluck('bloc.code')->unique()->implode(', ') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($affectations->count() > 0)
                                {{ $affectations->pluck('lot.numero')->implode(', ') }}
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($rT > 0)
                                {{ number_format($tT, 0, ',', ' ') }} / {{ number_format($rT, 0, ',', ' ') }}
                                <br><small>{{ $pctT }}%</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($rM > 0)
                                {{ number_format($tM, 0, ',', ' ') }} / {{ number_format($rM, 0, ',', ' ') }}
                                <br><small>{{ $pctM }}%</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($rD > 0)
                                {{ number_format($tD, 0, ',', ' ') }} / {{ number_format($rD, 0, ',', ' ') }}
                                <br><small>{{ $pctD }}%</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($rL > 0)
                                {{ number_format($tL, 0, ',', ' ') }} / {{ number_format($rL, 0, ',', ' ') }}
                                <br><small>{{ $pctL }}%</small>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($totalRef > 0)
                                <strong>{{ $pctGlobal }}%</strong>
                            @else
                                -
                            @endif
                        </td>
                    </tr>
                @endforeach
            @else
                @php $i++; @endphp
                <tr class="even">
                    <td>{{ $i }}</td>
                    <td>
                        {{ $client->name }}
                        @if($client->is_new)
                            <span class="badge-new">NOUVEAU</span>
                        @endif
                    </td>
                    <td>{{ $client->phone ?? '-' }}</td>
                    <td>
                        @if($client->sexe == 'masculin')
                            <span class="sexe-masculin">👨 Masculin</span>
                        @elseif($client->sexe == 'feminin')
                            <span class="sexe-feminin">👩 Féminin</span>
                        @else
                            -
                        @endif
                    </td>
                    <td>
                        @if($client->is_new)
                            <span class="badge-new">🆕</span>
                        @else
                            <span class="badge-old">Ancien</span>
                        @endif
                    </td>
                    <td colspan="9" style="text-align:center;color:#94a3b8;">Aucun dossier</td>
                    <td>-</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>

<div class="footer">
    <p>EDEN GROUP - Export généré le {{ $date_export }}</p>
    <p>Total : {{ $i }} ligne(s) - {{ $total_clients }} client(s)</p>
</div>

</body>
</html>