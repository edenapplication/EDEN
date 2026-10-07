<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport programmation + Date d'implantation</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1e3a5f; margin: 0; padding: 10px; }

        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #7c3aed; padding-bottom: 8px; margin-bottom: 12px; }
        .header-left h1 { margin: 0; font-size: 16px; font-weight: 900; }
        .header-left .subtitle { font-size: 10px; color: #64748b; margin-top: 2px; }
        .header-right { text-align: right; font-size: 8px; color: #64748b; }
        .header-right .date { font-weight: 700; color: #7c3aed; font-size: 10px; }

        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        thead { background: linear-gradient(135deg, #7c3aed, #6d28d9); color: white; }
        thead th { padding: 7px 5px; text-align: left; font-size: 7.5px; font-weight: 800; text-transform: uppercase; border-right: 1px solid #a78bfa; }
        thead th:last-child { border-right: none; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background: #faf5ff; }
        tbody td { padding: 6px 5px; font-size: 8.5px; vertical-align: middle; border-right: 1px solid #f1f5f9; }
        tbody td:last-child { border-right: none; }

        .num-cell { font-weight: 800; color: #7c3aed; text-align: center; width: 22px; }
        .nom-cell { font-weight: 700; }
        .sup-cell { font-weight: 700; color: #7c3aed; }
        .muted-cell { color: #64748b; font-size: 8px; }

        /* ✅ Colonne Date d'implantation mise en valeur */
        .date-impl-cell {
            font-weight: 700;
            color: #1e3a5f;
            background: #faf5ff;
            text-align: center;
            font-size: 8.5px;
        }

        .footer { margin-top: 14px; padding-top: 8px; border-top: 2px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 7.5px; color: #94a3b8; }
        .footer strong { color: #1e3a5f; }

        .stats-summary { margin-top: 10px; padding: 8px 12px; background: #faf5ff; border-radius: 6px; font-size: 8.5px; display: flex; justify-content: space-around; }
        .stats-summary .stat { text-align: center; }
        .stats-summary .stat .val { font-weight: 900; font-size: 12px; color: #7c3aed; display: block; }
        .stats-summary .stat .lbl { font-size: 7px; color: #64748b; text-transform: uppercase; font-weight: 700; }
    </style>
</head>
<body>

    {{-- EN-TÊTE --}}
    <div class="header">
        <div class="header-left">
            <h1>📋 RAPPORT DE PROGRAMMATION</h1>
            <div class="subtitle">EDEN GROUP — Affectations à programmer (avec date d'implantation)</div>
        </div>
        <div class="header-right">
            <div class="date">📅 Généré le {{ $dateGeneration }}</div>
            <div>{{ $lignes->count() }} ligne(s)</div>
        </div>
    </div>

    {{-- TABLEAU --}}
    @if($lignes->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width:22px;">N°</th>
                <th style="width:100px;">Bénéficiaire / Client</th>
                <th style="width:65px;">Grand Site</th>
                <th style="width:55px;">Site</th>
                <th style="width:50px;">TF</th>
                <th style="width:30px;">Bloc</th>
                <th style="width:70px;">Lots</th>
                <th style="width:50px;">Superficie</th>
                <th style="width:50px;">Date</th>
                <th style="width:60px;">📅 Implantation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lignes as $index => $ligne)
            <tr>
                <td class="num-cell">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td class="nom-cell">
                    {{ $ligne['beneficiaire'] }}
                    @if(!empty($ligne['telephone']))
                        <div style="font-size:7.5px;color:#64748b;">📞 {{ $ligne['telephone'] }}</div>
                    @endif
                </td>
                <td>{{ $ligne['grand_site'] }}</td>
                <td>{{ $ligne['site'] }}</td>
                <td>{{ $ligne['titre_foncier'] }}</td>
                <td>{{ $ligne['bloc'] ?? '—' }}</td>
                <td>{{ $ligne['lots'] ?: '—' }}</td>
                <td class="sup-cell">{{ number_format($ligne['superficie'], 0, ',', ' ') }} m²</td>
                <td style="font-size:8px;text-align:center;">
                    @if($ligne['date_affectation'])
                        {{ \Carbon\Carbon::parse($ligne['date_affectation'])->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
                <td class="date-impl-cell">
                    @if($ligne['date_implantation'])
                        {{ \Carbon\Carbon::parse($ligne['date_implantation'])->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="stats-summary">
        <div class="stat"><span class="val">{{ $lignes->count() }}</span><span class="lbl">Lignes</span></div>
        <div class="stat"><span class="val">{{ $stats['total_lots'] }}</span><span class="lbl">Lots</span></div>
        <div class="stat"><span class="val">{{ number_format($stats['total_superficie'], 0, ',', ' ') }}</span><span class="lbl">m² Total</span></div>
    </div>

    @else
    <div style="text-align:center; padding:60px; color:#94a3b8;">
        <div style="font-size:48px;">📭</div>
        <div style="font-weight:800; font-size:14px;">Aucune affectation</div>
    </div>
    @endif

    <div class="footer">
        <div>Document généré par <strong>EDEN GROUP</strong> — {{ $dateGeneration }}</div>
        <div>Page <strong>1</strong></div>
    </div>

</body>
</html>