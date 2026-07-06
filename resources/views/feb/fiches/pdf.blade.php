<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
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

        /* ================= FORMAT A4 PORTRAIT ================= */
        @page {
            size: A4 portrait;
            margin: 1.2cm 1cm 1.8cm 1cm;
        }

        /* Header Image répété */
        .pdf-header {
            position: running(header);
            width: 100%;
            height: 110px;
            object-fit: cover;
        }

        /* Footer répété */
        .pdf-footer {
            position: running(footer);
            text-align: center;
            color: #4a2c1a;
            font-size: 9pt;
            font-weight: bold;
            padding-top: 8px;
        }
        .footer-line {
            height: 2.5px;
            background: #8b4513;
            margin: 8px auto;
            width: 70%;
        }

        /* ================= EN-TÊTE ================= */
        .entete {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            border-bottom: 3px solid #1d4ed8;
            padding-bottom: 18px;
            gap: 20px;
        }

        .entete-gauche {
            flex-shrink: 0;
        }
        .logo-image {
            width: 85px;
            height: 85px;
            object-fit: contain;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            background: white;
            padding: 5px;
            border: 2px solid #1d4ed8;
        }

        .entete-centre {
            flex: 1;
            text-align: center;
        }
        .societe-name {
            font-size: 18pt;
            font-weight: 900;
            color: #1e3a5f;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .slogan {
            font-size: 9pt;
            color: #dc2626;
            font-style: italic;
            margin-top: 4px;
        }
        .document-title {
            font-size: 15pt;
            font-weight: 900;
            color: #1e3a5f;
            text-transform: uppercase;
            margin-top: 12px;
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

        /* Infos demandeur */
        .infos-demandeur {
            display: flex;
            justify-content: space-between;
            margin-bottom: 22px;
            gap: 30px;
        }
        .infos-gauche, .infos-droite {
            flex: 1;
        }
        .info-label {
            font-size: 8.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-value {
            font-size: 9.8pt;
            font-weight: 700;
            color: #1e3a5f;
        }

        /* Autres styles */
        .section-bloc { margin-bottom:20px; }
        .section-titre {
            background:#1e3a5f;
            color:white;
            padding:8px 16px;
            font-size:10.5pt;
            font-weight:700;
            border-radius:4px 4px 0 0;
            display:flex;
            justify-content:space-between;
        }

        table.tab-data { width:100%; border-collapse:collapse; }
        table.tab-data thead tr { background:#dbeafe; }
        table.tab-data thead th {
            padding:8px 10px; text-align:left;
            font-size:8.7pt; font-weight:700; color:#1d4ed8;
            border:1px solid #bfdbfe;
        }
        table.tab-data thead th.num-th { width:32px; text-align:center; color:#64748b; }
        table.tab-data tbody td {
            padding:7px 10px; font-size:8.7pt;
            border:1px solid #e2e8f0;
        }
        table.tab-data tbody tr:nth-child(even) td { background:#f8fafc; }
        table.tab-data .num-td { text-align:center; color:#94a3b8; }
        table.tab-data .montant { text-align:right; font-family:monospace; }

        .total-section {
            background:#f0fdf4;
            border:1px solid #bbf7d0;
            border-top:none;
            padding:10px 16px;
            display:flex;
            justify-content:flex-end;
            gap:16px;
        }
        .total-section .ts-val { font-weight:900; color:#15803d; font-size:11.3pt; }

        .total-global {
            background:#1e3a5f;
            color:white;
            border-radius:8px;
            padding:14px 24px;
            display:flex;
            justify-content:space-between;
            align-items:center;
            margin:22px 0;
        }
        .total-global .tg-val { font-size:16pt; font-weight:900; }

        .signatures {
            display:flex;
            justify-content:space-between;
            margin-top:40px;
            gap:20px;
        }
        .sig-box { flex:1; text-align:center; }
        .sig-line {
            border-top:1.5px solid #374151;
            margin-top:50px;
            padding-top:6px;
            font-size:8.8pt;
            color:#374151;
            font-weight:600;
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

    <!-- Image En-tête (répétée sur toutes les pages) -->
    <div style="position: running(header);">
        <img src="{{ asset('images/entete2.png') }}" style="width:100%; height:110px; object-fit:cover;" alt="En-tête">
    </div>

<div class="page">

    <!-- ================= EN-TÊTE ================= -->
    <div class="entete">
        <!-- GAUCHE : Logo Image -->
        <div class="entete-gauche">
            <img src="{{ asset('images/eden.webp') }}" alt="EDEN GROUP" class="logo-image">
        </div>

        <!-- CENTRE -->
        <div class="entete-centre">
            <div class="societe-name">EDEN GROUP SARL</div>
            <div class="document-title">FICHE D'EXPRESSION DES BESOINS</div>
             @if($fiche->utilisateur?->agence?->nom)
                <div class="document-title">{{ $fiche->utilisateur->agence->nom }}</div>
            @endif
            @if($fiche->utilisateur?->direction)
                <div style="margin-top:12px;">
                    <div class="info-value">{{ $fiche->utilisateur->direction }}</div>
                </div>
            @endif
             @if($fiche->utilisateur?->service)
                <div style="margin-top:8px;">
                    <div class="info-value">{{ $fiche->utilisateur->service }}</div>
                </div>
            @endif
        </div>

        <!-- DROITE -->
        <div class="entete-droite">
            @if($fiche->numero_fiche)
            <div class="info-line"><strong>N° Document :</strong> {{ $fiche->numero_fiche }}</div>
            @endif
            @if($fiche->soumise_at)
            <div class="info-line"><strong>Date de soumission :</strong> {{ $fiche->soumise_at->format('d/m/Y') }}</div>
            @endif
        </div>
    </div>

@if($fiche->titre)
                <div style="margin-top:12px;">
                    <div class="info-label">Motfif</div>
                    <div class="info-value">{{ $fiche->titre }}</div>
                </div>
            @endif
    {{-- ================= SECTIONS & RESTE DU DOCUMENT (inchangé) ================= --}}
    @php
        $totalGlobal = 0;
        $nbSections  = $fiche->sections->count();
    @endphp

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
    @endphp

    <div class="section-bloc">
        <div class="section-titre">
            <span>{{ $nbSections > 1 ? $loop->iteration . '. ' : '' }}{{ $section->titre }}</span>
            @if($totalSection > 0)
                <span>Total : {{ number_format($totalSection, 0, ',', ' ') }} FCFA</span>
            @endif
        </div>

        @if($colonnes->count() > 0 && $lignes->count() > 0)
        <table class="tab-data">
            <thead>
                <tr>
                    <th class="num-th">#</th>
                    @foreach($colonnes as $col)
                    <th>{{ $col->libelle }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($lignes as $ligne)
                <tr>
                    <td class="num-td">{{ $ligne->numero_ligne }}</td>
                    @foreach($colonnes as $col)
                        @php 
                            $val = $ligne->valeurs[$col->id] ?? ''; 
                            $isNum = preg_match('/prix|montant|quantit/i', $col->libelle); 
                        @endphp
                        <td class="{{ $isNum ? 'montant' : '' }}">{{ $val }}</td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
        </table>

        <div class="total-section">
            <span class="ts-lbl">Sous-total {{ $section->titre }} :</span>
            <span class="ts-val">{{ number_format($totalSection, 0, ',', ' ') }} FCFA</span>
        </div>
        @else
        <div style="color:#94a3b8; font-style:italic; padding:16px; text-align:center; border:1px solid #e2e8f0;">
            — Section vide —
        </div>
        @endif
    </div>
    @empty
    <div style="text-align:center; color:#94a3b8; padding:25px;">Aucune section.</div>
    @endforelse

    @if($totalGlobal > 0)
    <div class="total-global">
        <div>💰 ARRETER LE PRESENT DEVIS AU MONTANT DE : </div>
        <div class="tg-val">{{ number_format($totalGlobal, 0, ',', ' ') }} FCFA</div>
    </div>
    @endif

    <div class="entete-droite">
            @if($fiche->utilisateur?->poste)
            <div class="info-line"><strong>{{ $fiche->utilisateur->poste }}</strong></div><br><br>
            @endif

            @if($fiche->utilisateur?->nom_complet)
            <div class="info-line"><strong>{{ $fiche->utilisateur->nom_complet }}</strong></div>
            @endif
        </div>

    <div class="pied">
        <div>EDEN GROUP SARL — Fiches d'Expression des Besoins</div>
        <div>{{ $fiche->numero_fiche ?? '' }} — Imprimé le {{ now()->format('d/m/Y à H:i') }}</div>
    </div>

</div>

    <!-- Pied de page répété -->
    <div class="pdf-footer">
        <div class="footer-line"></div>
        B.P 35531 - Yaounde - Cameroun Tel : 694 123 784 / 675 538 022<br>
        NIU : M042217297350S    Situe a Texaco Omnisport<br>
        Agrement No : 0001/MINHDU/SG/DHSPI/SDPIAC du 07 Fevrier 2025
    </div>

</body>
</html>