<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $titreDocument ?? 'Rapport' }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }

        html, body {
            height: auto !important;
            min-height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            background: white;
            line-height: 1.4;
        }

        @page {
            size: A4 landscape;
            margin: 5mm 8mm 15mm 8mm;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* 🏢 EN-TÊTE IMAGE                                        */
        /* ═══════════════════════════════════════════════════════ */
        .pdf-header {
    width: 100%;
    margin-bottom: 8px;
    text-align: left;
    border: none;
    background: transparent;
}
.pdf-header img {
    height: 55px;
    width: auto;
    border: none;
    outline: none;
}

        /* ═══════════════════════════════════════════════════════ */
        /* 📋 TITRE DU DOCUMENT                                    */
        /* ═══════════════════════════════════════════════════════ */
        .doc-titre {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 2px solid #1d4ed8;
        }
        .doc-titre .titre-principal {
            font-size: 13pt;
            font-weight: 900;
            color: #1e3a5f;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 3px;
        }
        .doc-titre .sous-titre {
            font-size: 9pt;
            color: #64748b;
            font-weight: 600;
        }
        .doc-titre .meta {
            display: flex;
            justify-content: space-between;
            font-size: 8pt;
            color: #475569;
            margin-top: 6px;
            padding-top: 4px;
            border-top: 1px dashed #cbd5e1;
        }
        .doc-titre .meta strong { color: #1e3a5f; }

        /* ═══════════════════════════════════════════════════════ */
        /* 📊 BANDEAU STATISTIQUES                                 */
        /* ═══════════════════════════════════════════════════════ */
        .stats-banner {
            display: table;
            width: 100%;
            margin-bottom: 10px;
            background: #eff6ff;
            border: 1px solid #93c5fd;
            border-radius: 5px;
            overflow: hidden;
        }
        .stats-banner .stat-item {
            display: table-cell;
            width: 25%;
            text-align: center;
            padding: 6px 3px;
            border-right: 1px solid #93c5fd;
        }
        .stats-banner .stat-item:last-child { border-right: none; }
        .stats-banner .stat-value {
            font-size: 12pt;
            font-weight: 900;
            color: #1d4ed8;
            display: block;
            line-height: 1.1;
        }
        .stats-banner .stat-label {
            font-size: 6pt;
            color: #1e40af;
            text-transform: uppercase;
            font-weight: 800;
            letter-spacing: 0.3px;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* 📋 TABLEAU PRINCIPAL                                    */
        /* ═══════════════════════════════════════════════════════ */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 7pt;
            border: 1px solid #bfdbfe;
        }
        table.main-table thead {
            display: table-header-group;
        }
        table.main-table thead tr { background: #dbeafe; }
        table.main-table thead th {
            padding: 5px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 6.5pt;
            font-weight: 800;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            text-transform: uppercase;
            letter-spacing: 0.2px;
            word-wrap: break-word;
        }
        table.main-table tbody td {
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
            font-size: 7pt;
            border: 1px solid #e2e8f0;
            word-wrap: break-word;
            overflow-wrap: break-word;
        }
        table.main-table tbody tr:nth-child(even) td { background: #f8fafc; }

        /* ═══ Cellules spéciales ═══ */
        .num-cell {
            font-weight: 900;
            color: #1d4ed8;
            text-align: center;
            font-size: 6.5pt;
        }
        .nom-cell {
            font-weight: 700;
            color: #0f172a;
            text-align: left !important;
        }
        .nom-cell small {
            display: block;
            font-weight: 400;
            color: #64748b;
            font-size: 5.5pt;
            margin-top: 1px;
        }
        .sup-cell {
            font-weight: 800;
            color: #7c3aed;
        }
        .muted-cell {
            color: #64748b;
            font-size: 6.5pt;
        }
        .date-impl-cell {
            font-weight: 800;
            color: #15803d;
            background: #f0fdf4;
        }
        .date-aff-cell {
            font-weight: 700;
            color: #5b21b6;
            background: #faf5ff;
        }
        .geo-cell {
            font-weight: 700;
            color: #1e3a5f;
            background: #eff6ff;
            font-size: 6.5pt;
        }
        .heure-cell {
            font-weight: 700;
            color: #1e3a5f;
            background: #eff6ff;
        }
        .frais-cell { text-align: center; }

        .frais-badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 4px;
            font-weight: 900;
            font-size: 5.5pt;
            white-space: nowrap;
        }
        .frais-paye {
            background: #dcfce7;
            color: #166534;
            border: 1px solid #86efac;
        }
        .frais-impaye {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        /* ═══ Badge Statut ═══ */
        .statut-badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 5px;
            font-size: 5.5pt;
            font-weight: 900;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .statut-accepte { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
        .statut-refuse  { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }
        .statut-attente { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .statut-present { background: #dbeafe; color: #1e40af; border: 1px solid #93c5fd; }
        .statut-absent  { background: #f3e8ff; color: #6b21a8; border: 1px solid #c4b5fd; }
        .statut-retard  { background: #ffedd5; color: #9a3412; border: 1px solid #fdba74; }

        .motif-refus {
            font-size: 5.5pt;
            color: #991b1b;
            font-style: italic;
            margin-top: 2px;
            line-height: 1.2;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* 🏁 PIED DE PAGE                                        */
        /* ═══════════════════════════════════════════════════════ */
        .footer {
            position: fixed;
            bottom: -10mm;
            left: 0;
            right: 0;
            padding: 4px 8px 0 8px;
            border-top: 1px solid #e2e8f0;
            font-size: 6.5pt;
            color: #94a3b8;
            display: flex;
            justify-content: space-between;
        }
        .footer strong { color: #1e3a5f; }

        /* ═══════════════════════════════════════════════════════ */
        /* 📭 ÉTAT VIDE                                           */
        /* ═══════════════════════════════════════════════════════ */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #94a3b8;
        }
        .empty-state .ico { font-size: 36px; margin-bottom: 8px; }
        .empty-state .msg { font-weight: 800; font-size: 11pt; color: #475569; }

        /* ═══════════════════════════════════════════════════════ */
        /* 🖨️ IMPRESSION PDF                                      */
        /* ═══════════════════════════════════════════════════════ */
        tr { page-break-inside: avoid; }
    </style>
</head>
<body>

@php
    // ═══════════════════════════════════════════════════════
    // 🎯 DATE EXTRAITE DES LIGNES (jamais now())
    // ═══════════════════════════════════════════════════════
    $dateAffichee = null;
    $premiereLigne = collect($lignes ?? [])->first();

    if ($premiereLigne && is_array($premiereLigne)) {
        // Attribution → date_affectation
        // Tous les autres → date_implantation
        if ($type === 'rapport_programmation') {
            $dateAffichee = $premiereLigne['date_affectation'] ?? null;
        } else {
            $dateAffichee = $premiereLigne['date_implantation'] ?? null;
        }
    }

    // Fallback : si pas de date dans les lignes, utiliser $dateSemaine
    if (empty($dateAffichee)) {
        $dateAffichee = $dateSemaine;
    }

    // Formater la date
    try {
        $dateSemaineFmt = $dateAffichee instanceof \Carbon\Carbon
            ? $dateAffichee->format('d/m/Y')
            : ($dateAffichee ? \Carbon\Carbon::parse($dateAffichee)->format('d/m/Y') : '');
    } catch (\Exception $e) {
        $dateSemaineFmt = '';
    }

    // ═══════════════════════════════════════════════════════
    // 🎯 TITRES DYNAMIQUES
    // ═══════════════════════════════════════════════════════
    $titresParType = [
        'rapport_programmation'      => "Rapport des attributions du {$dateSemaineFmt}",
        'rapport_programmation_date' => "Rapport de planification des implantations du {$dateSemaineFmt}",
        'programmation_finale'       => "Rapport de clôture de planification des implantations du {$dateSemaineFmt}",
        'programmation_initiale'     => "Rapport d'implantation du {$dateSemaineFmt}",
        'programmation_active'       => "Rapport des appréciations du {$dateSemaineFmt}",
    ];

    $titreAffiche = $titreDocument ?? ($titresParType[$type] ?? 'Rapport de programmation');

    // ═══════════════════════════════════════════════════════
    // 🎯 SOUS-TITRES DYNAMIQUES
    // ═══════════════════════════════════════════════════════
    $sousTitresParType = [
        'rapport_programmation'      => 'Basé sur la date d\'attribution des lots',
        'rapport_programmation_date' => 'Avec dates d\'implantation',
        'programmation_finale'       => 'Clôture de la journée d\'implantation — tous statuts',
        'programmation_initiale'     => 'Affectations programmées avec géomètre et heure',
        'programmation_active'       => 'Statuts d\'acceptation / refus / présence',
    ];

    $sousTitreAffiche = $sousTitresParType[$type] ?? 'Affectations programmées';

    // ═══════════════════════════════════════════════════════
    // ✅ FLAGS D'AFFICHAGE DES COLONNES
    // ═══════════════════════════════════════════════════════
    $showFrais = true;

    $showGeometre = in_array($type, [
        'programmation_initiale',
        'programmation_active',
    ]);

    $showHeure = in_array($type, [
        'programmation_initiale',
        'programmation_active',
    ]);

    $showStatut = in_array($type, [
        'programmation_active',
    ]);

    $showDateImplantation = in_array($type, [
        'rapport_programmation_date',
        'programmation_initiale',
        'programmation_active',
        'programmation_finale',
    ]);

    // ═══════════════════════════════════════════════════════
    // ✅ STAGIAIRES
    // ═══════════════════════════════════════════════════════
    $showStagiaire = !empty($lignes->first()['stagiaire'] ?? null);

    // ═══════════════════════════════════════════════════════
    // ✅ DÉTECTION APPRÉCIATIONS
    // ═══════════════════════════════════════════════════════
    $isAppreciation = ($type === 'programmation_active');
@endphp

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- 🏢 EN-TÊTE IMAGE                                        --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="pdf-header">
    <img src="{{ public_path('images/eden-bon.jpeg') }}"
         alt="Logo EDEN Group"
         border="0"
         style="border:none;">
</div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- 📋 TITRE DU DOCUMENT                                    --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="doc-titre">
        <div class="titre-principal">{{ $titreAffiche }}</div>
        <div class="sous-titre">{{ $sousTitreAffiche }}</div>
        <div class="meta">
            <div><strong>📅 Généré le :</strong> {{ $dateGeneration }}</div>
            <div><strong>📊 {{ $lignes->count() }} Affectation(s)</strong></div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- 📊 BANDEAU STATISTIQUES                                --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if($lignes->count() > 0)
    <div class="stats-banner">
        <div class="stat-item">
            <span class="stat-value">{{ $lignes->count() }}</span>
            <span class="stat-label">Affectattion(s)</span>
        </div>
        <div class="stat-item">
            <span class="stat-value">{{ $stats['total_lots'] ?? 0 }}</span>
            <span class="stat-label">Lots</span>
        </div>
        <div class="stat-item">
            <span class="stat-value">{{ number_format($stats['total_superficie'] ?? 0, 0, ',', ' ') }}</span>
            <span class="stat-label">m² Total</span>
        </div>
        @if(isset($stats['acceptes']) && $stats['acceptes'] > 0)
        <div class="stat-item">
            <span class="stat-value" style="color:#16a34a;">{{ $stats['acceptes'] }}</span>
            <span class="stat-label">Acceptés</span>
        </div>
        @elseif(isset($stats['refuses']) && $stats['refuses'] > 0)
        <div class="stat-item">
            <span class="stat-value" style="color:#dc2626;">{{ $stats['refuses'] }}</span>
            <span class="stat-label">Refusés</span>
        </div>
        @endif
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- 📋 TABLEAU PRINCIPAL                                   --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    @if($lignes->count() > 0)
    <table class="main-table">
        <thead>
            <tr>
                <th style="width:22px;">N°</th>
                <th style="width:85px;">Bénéficiaire / Client</th>
                <th style="width:60px;">Facilitateur</th>
                <th style="width:60px;">Grand Site</th>
                <th style="width:45px;">Site</th>
                <th style="width:42px;">TF</th>
                <th style="width:32px;">Bloc</th>
                <th style="width:60px;">Lots</th>
                <th style="width:48px;">Superficie</th>
                <th style="width:48px;">Date d'aff.</th>

                @if($showFrais)
                    <th style="width:48px;">💰 Frais</th>
                @endif

                @if($showGeometre)
                    <th style="width:60px;">👷 Géomètre</th>
                @endif

                @if($showHeure)
                    <th style="width:45px;">⏰ Heure</th>
                @endif

                @if($showStatut)
                    <th style="width:60px;">Statut</th>
                @endif

                @if($showDateImplantation)
                    <th style="width:52px;">Date d'impl.</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach($lignes as $index => $ligne)
            @php
                $statutKey  = $ligne['statut_acceptation'] ?? 'en_attente';
                $classeStat = $ligne['classe_statut'] ?? 'en_attente';
                $statutLabel = $ligne['statut_final'] ?? null;

                if (!$statutLabel) {
                    $statutLabel = match($statutKey) {
                        'accepte' => 'Accepté',
                        'refuse'  => 'Refusé',
                        default   => 'En attente',
                    };
                }

                $badgeIcon = match($classeStat) {
                    'accepte' => '✅',
                    'refuse'  => '❌',
                    'present' => '🟢',
                    'absent'  => '⚫',
                    'retard'  => '🟠',
                    default   => '⏳',
                };

                $badgeClass = match($classeStat) {
                    'accepte' => 'statut-accepte',
                    'refuse'  => 'statut-refuse',
                    'present' => 'statut-present',
                    'absent'  => 'statut-absent',
                    'retard'  => 'statut-retard',
                    default   => 'statut-attente',
                };
            @endphp
            <tr>
                <td class="num-cell">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>

                <td class="nom-cell">
                    {{ $ligne['beneficiaire'] ?? '—' }}
                    @if(!empty($ligne['telephone']))
                        <small>📞 {{ $ligne['telephone'] }}</small>
                    @endif
                </td>

                <td class="muted-cell">{{ $ligne['facilitateur'] ?? '—' }}</td>
                <td>{{ $ligne['grand_site'] ?? '—' }}</td>
                <td>{{ $ligne['site'] ?? '—' }}</td>
                <td>{{ $ligne['titre_foncier'] ?? '—' }}</td>
                <td>{{ $ligne['bloc'] ?? '—' }}</td>

                <td style="font-weight:700; color:#0f172a;">{{ $ligne['lots'] ?: '—' }}</td>

                <td class="sup-cell">
                    {{ number_format($ligne['superficie'] ?? 0, 0, ',', ' ') }} m²
                </td>

                <td class="date-aff-cell">
                    @if(!empty($ligne['date_affectation']))
                        {{ \Carbon\Carbon::parse($ligne['date_affectation'])->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>

                @if($showFrais)
                    <td class="frais-cell">
                        @if($ligne['frais_paye'] ?? false)
                            <span class="frais-badge frais-paye">✅ Payé</span>
                        @else
                            <span class="frais-badge frais-impaye">❌ Non payé</span>
                        @endif
                    </td>
                @endif

                @if($showGeometre)
                    <td class="geo-cell">
                        {{ !empty($ligne['geometre_nom']) ? $ligne['geometre_nom'] : '—' }}
                    </td>
                @endif

                @if($showHeure)
                    <td class="heure-cell">
                        {{ !empty($ligne['heure_implantation']) ? substr($ligne['heure_implantation'], 0, 5) : '—' }}
                    </td>
                @endif

                @if($showStatut)
                    <td style="text-align:center;">
                        <span class="statut-badge {{ $badgeClass }}">
                            {{ $badgeIcon }} {{ $statutLabel }}
                        </span>
                        @if($classeStat === 'refuse' && !empty($ligne['motif_refus']))
                            <div class="motif-refus">
                                💬 {{ \Illuminate\Support\Str::limit($ligne['motif_refus'], 40) }}
                            </div>
                        @endif
                    </td>
                @endif

                @if($showDateImplantation)
                    <td class="date-impl-cell">
                        @if(!empty($ligne['date_implantation']))
                            {{ \Carbon\Carbon::parse($ligne['date_implantation'])->format('d/m/Y') }}
                        @else
                            —
                        @endif
                    </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    @else
    <div class="empty-state">
        <div class="ico">📭</div>
        <div class="msg">Aucune affectation à afficher</div>
    </div>
    @endif

    {{-- ═══════════════════════════════════════════════════════ --}}
    {{-- 🏁 PIED DE PAGE                                        --}}
    {{-- ═══════════════════════════════════════════════════════ --}}
    <div class="footer">
        <div>Document généré par <strong>EDEN GROUP</strong> — {{ $dateGeneration }}</div>
        <div>Page <strong>1</strong></div>
    </div>

<script type="text/php">
    if (isset($pdf)) {
        $pdf->page_text(780, 560, "Page {PAGE_NUM}/{PAGE_COUNT}", null, 7, array(0.58, 0.64, 0.72));
    }
</script>

</body>
</html>