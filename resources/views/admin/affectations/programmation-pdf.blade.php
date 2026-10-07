<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Programmation Implantation</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1e3a5f;
            margin: 0;
            padding: 10px;
        }

        /* ═══ EN-TÊTE ═══ */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 3px solid #1d4ed8;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-left h1 {
            margin: 0;
            font-size: 16px;
            font-weight: 900;
            color: #1e3a5f;
        }
        .header-left .subtitle {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .header-right {
            text-align: right;
            font-size: 8px;
            color: #64748b;
        }
        .header-right .date {
            font-weight: 700;
            color: #1d4ed8;
            font-size: 10px;
        }

        /* ═══ FILTRES ═══ */
        .filtres-box {
            background: #eff6ff;
            border-left: 4px solid #1d4ed8;
            padding: 6px 10px;
            margin-bottom: 12px;
            font-size: 8px;
            color: #1e40af;
            border-radius: 4px;
        }
        .filtres-box strong {
            color: #1e3a5f;
        }

        /* ═══ TABLEAU ═══ */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        thead {
            background: linear-gradient(135deg, #1e3a5f, #1d4ed8);
            color: white;
        }
        thead th {
            padding: 8px 6px;
            text-align: left;
            font-size: 8px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            border-right: 1px solid #3b82f6;
        }
        thead th:last-child {
            border-right: none;
        }
        tbody tr {
            border-bottom: 1px solid #e2e8f0;
        }
        tbody tr:nth-child(even) {
            background: #f8fafc;
        }
        tbody tr.paye {
            background: #f0fdf4;
        }
        tbody tr.impaye {
            background: #fef2f2;
        }
        tbody td {
            padding: 7px 6px;
            font-size: 8.5px;
            color: #1e3a5f;
            vertical-align: middle;
        }

        .num-cell {
            font-weight: 800;
            color: #1d4ed8;
            text-align: center;
            width: 24px;
        }
        .nom-cell {
            font-weight: 700;
            color: #1e3a5f;
        }
        .bold-cell {
            font-weight: 700;
        }
        .sup-cell {
            font-weight: 700;
            color: #7c3aed;
        }
        .muted-cell {
            color: #64748b;
            font-size: 8px;
        }

        .frais-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 7.5px;
            font-weight: 800;
        }
        .frais-badge.paye {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .frais-badge.impaye {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* ═══ PIED DE PAGE ═══ */
        .footer {
            margin-top: 14px;
            padding-top: 8px;
            border-top: 2px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            font-size: 7.5px;
            color: #94a3b8;
        }
        .footer strong {
            color: #1e3a5f;
        }

        .stats-summary {
            margin-top: 10px;
            padding: 8px 12px;
            background: #f1f5f9;
            border-radius: 6px;
            font-size: 8.5px;
            color: #1e3a5f;
            display: flex;
            justify-content: space-around;
        }
        .stats-summary .stat {
            text-align: center;
        }
        .stats-summary .stat .val {
            font-weight: 900;
            font-size: 12px;
            color: #1d4ed8;
            display: block;
        }
        .stats-summary .stat .lbl {
            font-size: 7.5px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 700;
        }
    </style>
</head>
<body>

    {{-- ═══ EN-TÊTE ═══ --}}
    <div class="header">
        <div class="header-left">
            <h1>📅 PROGRAMMATION IMPLANTATION</h1>
            <div class="subtitle">EDEN GROUP — Planification des implantations foncières</div>
        </div>
        <div class="header-right">
            <div class="date">📅 {{ $dateGeneration }}</div>
            <div style="margin-top:2px;">{{ $lignes->count() }} ligne(s)</div>
        </div>
    </div>

    {{-- ═══ FILTRES ═══ --}}
    @if($filtresTexte && $filtresTexte !== 'Aucun filtre appliqué')
    <div class="filtres-box">
        <strong>🔍 Filtres appliqués :</strong> {{ $filtresTexte }}
    </div>
    @endif

    {{-- ═══ TABLEAU ═══ --}}
    @if($lignes->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width:24px;">N°</th>
                <th style="width:110px;">Noms et Prénoms</th>
                <th style="width:60px;">Titre Foncier</th>
                <th style="width:35px;">Bloc</th>
                <th style="width:70px;">Lots</th>
                <th style="width:55px;">Superficie</th>
                <th style="width:80px;">Facilitateur</th>
                <th style="width:75px;">Téléphone</th>
                <th style="width:80px;">Géomètres</th>
                <th style="width:45px;">Heures</th>
                <th style="width:85px;">Frais d'implantation</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lignes as $index => $ligne)
            @php
                $rowClass = $ligne['frais_paye'] ? 'paye' : 'impaye';
            @endphp
            <tr class="{{ $rowClass }}">
                <td class="num-cell">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td class="nom-cell">{{ $ligne['beneficiaire'] }}</td>
                <td class="bold-cell">{{ $ligne['titre_foncier'] }}</td>
                <td class="bold-cell">{{ $ligne['bloc'] ?? '—' }}</td>
                <td class="bold-cell">{{ $ligne['lots'] ?: '—' }}</td>
                <td class="sup-cell">
                    {{ number_format($ligne['superficie'], 0, ',', ' ') }} m²
                </td>
                <td class="muted-cell">{{ $ligne['facilitateur'] }}</td>
                <td class="muted-cell">📞 {{ $ligne['telephone'] }}</td>
                <td class="bold-cell">{{ $ligne['geometre_nom'] ?? '—' }}</td>
                <td class="bold-cell" style="text-align:center;">
                    {{ $ligne['heure_implantation'] ? substr($ligne['heure_implantation'], 0, 5) : '—' }}
                </td>
                <td style="text-align:center;">
                    @if($ligne['frais_paye'])
                        <span class="frais-badge paye">✅ Payé</span>
                    @else
                        <span class="frais-badge impaye">❌ Non payé</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ═══ RÉSUMÉ ═══ --}}
    <div class="stats-summary">
        <div class="stat">
            <span class="val">{{ $lignes->count() }}</span>
            <span class="lbl">Lignes</span>
        </div>
        <div class="stat">
            <span class="val">{{ $lignes->sum(fn($l) => count(explode(', ', $l['lots']))) }}</span>
            <span class="lbl">Lots</span>
        </div>
        <div class="stat">
            <span class="val">{{ number_format($lignes->sum('superficie'), 0, ',', ' ') }}</span>
            <span class="lbl">m² Total</span>
        </div>
        <div class="stat">
            <span class="val">{{ $lignes->where('frais_paye', true)->count() }}</span>
            <span class="lbl">Payés</span>
        </div>
        <div class="stat">
            <span class="val">{{ $lignes->where('frais_paye', false)->count() }}</span>
            <span class="lbl">Non payés</span>
        </div>
    </div>

    @else
    <div style="text-align:center; padding:60px; color:#94a3b8;">
        <div style="font-size:48px; margin-bottom:12px;">✅</div>
        <div style="font-weight:800; font-size:14px; color:#1e3a5f;">Aucune affectation en attente</div>
        <div style="font-size:10px; margin-top:6px;">Toutes les programmations ont été traitées.</div>
    </div>
    @endif

    {{-- ═══ PIED DE PAGE ═══ --}}
    <div class="footer">
        <div>Document généré automatiquement par <strong>EDEN GROUP</strong> — {{ $dateGeneration }}</div>
        <div>Page <strong>1</strong></div>
    </div>

</body>
</html>