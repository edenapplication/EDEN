<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Programmation - {{ $dateSemaine->format('d/m/Y') }}</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #1e3a5f; margin: 0; padding: 10px; }

        .header { display: flex; justify-content: space-between; align-items: center;
                  border-bottom: 3px solid #1d4ed8; padding-bottom: 8px; margin-bottom: 12px; }
        .header-left h1 { margin: 0; font-size: 16px; font-weight: 900; color: #1e3a5f; }
        .header-left .subtitle { font-size: 10px; color: #64748b; margin-top: 2px; }
        .header-right { text-align: right; font-size: 8px; color: #64748b; }
        .header-right .date { font-weight: 700; color: #1d4ed8; font-size: 10px; }

        .type-box { padding: 6px 10px; margin-bottom: 10px; font-size: 10px; font-weight: 700; border-radius: 4px; }
        .type-box.initiale { background: #ede9fe; border-left: 4px solid #7c3aed; color: #5b21b6; }
        .type-box.active   { background: #fef3c7; border-left: 4px solid #f59e0b; color: #92400e; }
        .type-box.finale   { background: #dcfce7; border-left: 4px solid #16a34a; color: #166534; }

        .semaine-box { background: #eff6ff; border-left: 4px solid #1d4ed8; padding: 6px 10px;
                       margin-bottom: 10px; font-size: 10px; color: #1e40af; border-radius: 4px; font-weight: 700; }

        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        thead { background: linear-gradient(135deg, #1e3a5f, #1d4ed8); color: white; }
        thead th { padding: 7px 5px; text-align: left; font-size: 7.5px; font-weight: 800;
                   text-transform: uppercase; border-right: 1px solid #3b82f6; }
        thead th:last-child { border-right: none; }
        tbody tr { border-bottom: 1px solid #e2e8f0; }
        tbody tr:nth-child(even) { background: #f8fafc; }
        tbody td { padding: 6px 5px; font-size: 8.5px; color: #1e3a5f; vertical-align: middle; border-right: 1px solid #f1f5f9; }
        tbody td:last-child { border-right: none; }

        .num-cell { font-weight: 800; color: #1d4ed8; text-align: center; }
        .nom-cell { font-weight: 700; }
        .sup-cell { font-weight: 700; color: #7c3aed; }
        .muted-cell { color: #64748b; font-size: 8px; }

        .badge { display: inline-block; padding: 1px 6px; border-radius: 10px; font-size: 7px; font-weight: 800; }
        .badge.paye { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge.impaye { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge.accepte { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .badge.refuse  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .badge.absent  { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
        .badge.en_attente { background: #e2e8f0; color: #475569; border: 1px solid #cbd5e1; }

        .stats { margin-top: 10px; padding: 8px 12px; background: #f1f5f9; border-radius: 6px;
                 font-size: 8.5px; display: flex; justify-content: space-around; }
        .stats .stat { text-align: center; }
        .stats .stat .val { font-weight: 900; font-size: 12px; color: #1d4ed8; display: block; }
        .stats .stat .lbl { font-size: 7px; color: #64748b; text-transform: uppercase; font-weight: 700; }

        .footer { margin-top: 14px; padding-top: 8px; border-top: 2px solid #e2e8f0;
                  display: flex; justify-content: space-between; font-size: 7.5px; color: #94a3b8; }
        .footer strong { color: #1e3a5f; }
    </style>
</head>
<body>

    {{-- EN-TÊTE --}}
    <div class="header">
        <div class="header-left">
            <h1>📅 PROGRAMMATION IMPLANTATION</h1>
            <div class="subtitle">EDEN GROUP — Planification des implantations foncières</div>
        </div>
        <div class="header-right">
            <div class="date">📅 Généré le {{ $dateGeneration }}</div>
            <div>{{ $lignes->count() }} ligne(s)</div>
        </div>
    </div>

    {{-- TYPE --}}
    <div class="type-box {{ $type === 'programmation_finale' ? 'finale' : ($type === 'programmation_active' ? 'active' : 'initiale') }}">
        @if($type === 'programmation_finale')
            ✅ DOCUMENT — Programmation finalisée (accepté / refusé / absent)
        @elseif($type === 'programmation_active')
            🎯 DOCUMENT — Programmation active
        @else
            📝 DOCUMENT — Programmation initiale
        @endif
    </div>

    {{-- SEMAINE --}}
    <div class="semaine-box">
        📆 Semaine du <strong>{{ $dateSemaine->copy()->startOfWeek()->format('d/m/Y') }}</strong>
        au <strong>{{ $dateSemaine->copy()->endOfWeek()->format('d/m/Y') }}</strong>
        — Dimanche : <strong>{{ $dateSemaine->format('d/m/Y') }}</strong>
    </div>

    {{-- TABLEAU --}}
    @if($lignes->count() > 0)
    <table>
        <thead>
            <tr>
                <th style="width:22px;">N°</th>
                <th style="width:110px;">Noms et Prénoms</th>
                <th style="width:60px;">Titre Foncier</th>
                <th style="width:35px;">Bloc</th>
                <th style="width:70px;">Lots</th>
                <th style="width:55px;">Superficie</th>
                <th style="width:80px;">Facilitateur</th>
                <th style="width:75px;">Téléphone</th>
                <th style="width:80px;">Géomètres</th>
                <th style="width:45px;">Heures</th>
                <th style="width:75px;">Frais d'implantation</th>
                @if(in_array($type, ['programmation_active', 'programmation_finale']))
                    <th style="width:65px;">Statut</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($lignes as $index => $ligne)
            <tr>
                <td class="num-cell">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td class="nom-cell">{{ $ligne['beneficiaire'] }}</td>
                <td>{{ $ligne['titre_foncier'] }}</td>
                <td>{{ $ligne['bloc'] ?? '—' }}</td>
                <td>{{ $ligne['lots'] ?: '—' }}</td>
                <td class="sup-cell">{{ number_format($ligne['superficie'] ?? 0, 0, ',', ' ') }} m²</td>
                <td class="muted-cell">{{ $ligne['facilitateur'] }}</td>
                <td class="muted-cell">📞 {{ $ligne['telephone'] }}</td>
                <td>{{ $ligne['geometre_nom'] ?? '—' }}</td>
                <td style="text-align:center;">{{ $ligne['heure_implantation'] ? substr($ligne['heure_implantation'], 0, 5) : '—' }}</td>
                <td style="text-align:center;">
                    @if($ligne['frais_paye'] ?? false)
                        <span class="badge paye">✅ Payé</span>
                    @else
                        <span class="badge impaye">❌ Non payé</span>
                    @endif
                </td>
                @if(in_array($type, ['programmation_active', 'programmation_finale']))
                    <td style="text-align:center;">
                        @php
                            $statut = $ligne['statut_acceptation'] ?? 'en_attente';
                            $label = match($statut) {
                                'accepte'    => '✅ Accepté',
                                'refuse'     => '❌ Refusé',
                                'en_attente' => '⏳ En attente',
                                default      => $statut,
                            };
                            $classe = match($statut) {
                                'accepte' => 'accepte',
                                'refuse'  => 'refuse',
                                default   => 'en_attente',
                            };
                        @endphp
                        <span class="badge {{ $classe }}">{{ $label }}</span>
                    </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- STATS --}}
    <div class="stats">
        <div class="stat">
            <span class="val">{{ $lignes->count() }}</span>
            <span class="lbl">Lignes</span>
        </div>
        <div class="stat">
            <span class="val">{{ $stats['total_lots'] ?? 0 }}</span>
            <span class="lbl">Lots</span>
        </div>
        <div class="stat">
            <span class="val">{{ number_format($stats['total_superficie'] ?? 0, 0, ',', ' ') }}</span>
            <span class="lbl">m² Total</span>
        </div>
        @if(in_array($type, ['programmation_active', 'programmation_finale']))
        <div class="stat">
            <span class="val">{{ $stats['acceptes'] ?? 0 }}</span>
            <span class="lbl">Acceptés</span>
        </div>
        <div class="stat">
            <span class="val">{{ $stats['refuses'] ?? 0 }}</span>
            <span class="lbl">Refusés</span>
        </div>
        @endif
    </div>

    @else
    <div style="text-align:center; padding:60px; color:#94a3b8;">
        <div style="font-size:48px;">📭</div>
        <div style="font-weight:800; font-size:14px; color:#1e3a5f;">Aucune affectation</div>
    </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        <div>Document généré par <strong>EDEN GROUP</strong> — {{ $dateGeneration }}</div>
        <div>Page <strong>1</strong></div>
    </div>

</body>
</html>