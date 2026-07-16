<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { 
            font-family: DejaVu Sans, sans-serif; 
            font-size: 9.2pt; 
            color: #1e293b; 
            background: white; 
            line-height: 1.45;
        }
        .page { padding: 25px 28px; }

        @page {
            size: A4 portrait;
            margin: 1.2cm 1cm 2.5cm 1cm;
        }

        .pdf-header {
            position: running(header);
            width: 100%;
            height: 155px;
            object-fit: cover;
        }

        .pdf-footer {
            position: running(footer);
            text-align: center;
            color: #4a2c1a;
            font-size: 8.7pt;
            font-weight: bold;
            line-height: 1.35;
            padding: 25px 0 15px 0;
            height: 22%;
            border-top: 2.5px solid #8b4513;
        }

        .entete {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 18px;
            gap: 20px;
        }

        .entete-centre {
            flex: 1;
            text-align: center;
            margin-top: -25px;
        }
        .document-title {
            font-size: 13pt;
            font-weight: 900;
            color: #1e3a5f;
            text-transform: uppercase;
            margin-top: 8px;
            letter-spacing: 0.8px;
        }

        .entete-droite {
            text-align: right;
            min-width: 190px;
            font-size: 9.2pt;
        }
        .entete-droite .info-line {
            margin-bottom: 6px;
        }
        .entete-droite strong {
            color: #1e3a5f;
        }

        table.tab-data { 
            width:100%; 
            border-collapse:collapse;  
            table-layout: fixed; 
            font-size: 7.8pt;
            border: 1px solid #bfdbfe;
        }

        table.tab-data thead tr { background:#dbeafe; }
        table.tab-data thead th {
            padding:5px 6px;
            text-align:center;
            vertical-align:middle;
            font-size:7.7pt;
            font-weight:700;
            color:#1d4ed8;
            border:1px solid #bfdbfe;
        }
        table.tab-data thead th.num-th { width:28px; text-align:center; color:#64748b; }

        table.tab-data tbody td {
            padding:4px 6px;
            text-align:center;
            vertical-align:middle;
            font-size:7.7pt;
            border:1px solid #e2e8f0;
            word-wrap:break-word;
            overflow-wrap:break-word;
        }
        table.tab-data tbody tr:nth-child(even) td { background:#f8fafc; }
        table.tab-data .num-td { text-align:center; color:#94a3b8; }
        table.tab-data .montant {
            text-align:center;
            font-family:monospace;
        }

        .section-title-row td {
            background-color:#eef2ff !important;
            border:1px solid #bfdbfe !important;
            padding:6px 12px !important;
            font-weight:700;
            font-size:9pt;
            color:#1e3a5f;
            text-align:left !important;
        }

        .total-section-row td {
            background:#f0fdf4 !important;
            border:1px solid #bbf7d0 !important;
            border-top:2px solid #86efac !important;
            padding:6px 12px !important;
            text-align:left !important;
            font-weight:600;
        }
        .total-section-row .ts-val {
            font-weight:900;
            color:#15803d;
            font-size:9.5pt;
            margin-left:8px;
        }

        .total-global-row td {
            background:#f8f8f8 !important;
            border:1px solid #f5f5f2 !important;
            border-top:3px solid #fcfbf9 !important;
            padding:10px 16px !important;
            text-align:left !important;
            font-weight:700;
            font-size:10pt;
        }
        .total-global-row .tg-val {
            font-weight:900;
            font-size:14pt;
            color:#000;
            margin-left:8px;
        }
        .total-global-row .tg-lettres {
            font-size: 8.5pt;
            font-weight: 600;
            color: #374151;
            display: block;
            margin-top: 4px;
        }

        .poste {
            text-decoration: underline;
        }

        .pied {
            margin-top:45px;
            padding-top:12px;
            border-top:1.5px solid #e2e8f0;
            display:flex;
            justify-content:space-between;
            font-size:7.6pt;
            color:#94a3b8;
        }
    </style>
</head>
<body>

    <header class="pdf-header">
        <img src="{{ public_path('images/entete2.png') }}" class="header-image" alt="En-tête" style="width:100%; height:900px; object-fit:cover;">
    </header>

<div class="page">

    <div class="entete">
        <div class="entete-centre">
            @if($fiche->utilisateur?->agence?->nom)
                <div class="document-title">{{ $fiche->utilisateur->agence->nom }}</div>
            @endif
            @if($fiche->utilisateur?->direction)
                <div class="document-title">{{ $fiche->utilisateur->direction }}</div>
            @endif
            @if($fiche->utilisateur?->service)
                <div class="document-title">{{ $fiche->utilisateur->service }}</div>
            @endif
        </div>

        <div class="entete-droite">
            <div class="info-line">
                <strong>N° Document :</strong> {{ $fiche->numero_fiche ?? 'EDG-' . str_pad($fiche->id, 6, '0', STR_PAD_LEFT) }}
            </div>
            @if($fiche->soumise_at)
            <div class="info-line"><strong>Date de soumission :</strong> {{ $fiche->soumise_at->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

    @if($fiche->titre)
        <div style="font-size:14pt; font-weight:bold; color:#ef0c0c; text-align:left; margin-bottom:10px;">
            {{ $fiche->titre }}
        </div>
    @endif

    {{-- ================= DESTINATAIRES (sur une seule ligne) ================= --}}
    @if($fiche->destinataires->isNotEmpty())
        <div style="margin-bottom:15px; font-size:9.5pt; color:#000;">
            <span style="font-weight:700;">DOIT :</span> 
           <strong> {{ $fiche->destinataires->pluck('nom')->implode(', ') }}</strong>
        </div>
    @endif

    {{-- ================= TABLEAU ================= --}}
    @php
        $totalGlobal = 0;
        $nbSections  = $fiche->sections->count();
        $colonnesGlobales = [];
        $firstSection = $fiche->sections->first();
        if ($firstSection) {
            $colonnesGlobales = $firstSection->colonnes;
        }
        $colspan = count($colonnesGlobales) + 1;
    @endphp

    <table class="tab-data">
        <thead>
            <tr>
                <th class="num-th">N</th>
                @foreach($colonnesGlobales as $col)
                    <th>{{ $col->libelle }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($fiche->sections as $section)
                @php
                    $colonnes     = $section->colonnes;
                    $lignes       = $section->lignes;
                    $totalSection = 0;
                    $colPT = $colonnes->first(fn($c) => preg_match('/prix.?total|montant.?total/i', $c->libelle));
                    
                    foreach ($lignes as $l) {
                        if ($colPT) {
                            $val = str_replace([' ',' ',','], ['', '', '.'], $l->valeurs[$colPT->id] ?? '0');
                            $totalSection += floatval($val);
                        }
                    }
                    $totalGlobal += $totalSection;

                    $hasCustomTitle = !empty(trim($section->titre)) && !str_starts_with(trim($section->titre), 'Section ');
                @endphp

                @if($hasCustomTitle)
                    <tr class="section-title-row">
                        <td colspan="{{ $colspan }}">
                            {{ $nbSections > 1 ? $loop->iteration . '. ' : '' }}{{ $section->titre }}
                        </td>
                    </tr>
                @endif

                @forelse($lignes as $ligne)
                    <tr>
                        <td class="num-td">{{ $ligne->numero_ligne }}</td>
                        @foreach($colonnesGlobales as $col)
                            @php 
                                $val = $ligne->valeurs[$col->id] ?? ''; 
                                $isNum = preg_match('/prix|montant|quantit/i', $col->libelle); 
                            @endphp
                            <td class="{{ $isNum ? 'montant' : '' }}">{{ $val }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $colspan }}" style="text-align:center; color:#94a3b8; font-style:italic; padding:12px;">
                            — Aucune ligne dans cette section —
                        </td>
                    </tr>
                @endforelse

                @if($lignes->count() > 1)
                    <tr class="total-section-row">
                        <td colspan="{{ $colspan }}">
                            <span style="font-weight:600;">Total {{ $hasCustomTitle ? $section->titre : 'Section' }} :</span>
                            <span class="ts-val">{{ number_format($totalSection, 0, ',', ' ') }} FCFA</span>
                        </td>
                    </tr>
                @endif

            @empty
                <tr>
                    <td colspan="{{ count($colonnesGlobales) + 1 }}" style="text-align:center; color:#94a3b8; padding:25px;">
                        Aucune section disponible.
                    </td>
                </tr>
            @endforelse

            @if($totalGlobal > 0)
                <tr class="total-global-row">
                    <td colspan="{{ count($colonnesGlobales) + 1 }}">
                        <span>TOTAL GÉNÉRAL :</span>
                        <span class="tg-val">{{ number_format($totalGlobal, 0, ',', ' ') }} FCFA</span>
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <br><span class="tg-lettres">Arrêté le présent dévis a la somme de : <strong>{{ $totalLettre ?? '' }}</strong></span>

    <div style="margin-top:30px; text-align:right;">
        @if($fiche->utilisateur?->poste)
            <div class="poste" style="font-weight:700; font-size:10pt;">{{ $fiche->utilisateur->poste }}</div>
            <br>
        @endif
        @if($fiche->utilisateur?->nom_complet)
            <div style="font-weight:600; font-size:10pt;">{{ $fiche->utilisateur->nom_complet }}</div>
        @endif
    </div>

    <div class="pied">
        <div>EDEN GROUP SARL — Fiches d'Expression des Besoins</div>
        <div>{{ $fiche->numero_fiche ?? '' }} — Imprimé le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>

</div>

</body>
</html>