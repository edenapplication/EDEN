<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $titreDocument ?? 'Rapport des appréciations' }}</title>
    <style>
        * { margin: 0; padding: 0; }
        body {
            font-family: 'dejavusans', sans-serif;
            font-size: 8pt;
            color: #1e293b;
            line-height: 1.3;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* EN-TÊTE IMAGE                                           */
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
        /* TITRE + SOUS-TITRE + DATE                               */
        /* ═══════════════════════════════════════════════════════ */
        .rapport-titre {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .rapport-titre small {
            font-size: 8pt;
            font-weight: normal;
            color: #64748b;
            display: block;
            margin-top: 3px;
            text-transform: none;
        }

        .rapport-date-impl {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            color: #1d4ed8;
            margin-bottom: 10px;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* BANDEAU STATISTIQUES (vrai tableau HTML)                */
        /* ═══════════════════════════════════════════════════════ */
        table.stats-banner {
            width: 100%;
            margin-bottom: 10px;
            background-color: #eff6ff;
            border: 1px solid #93c5fd;
            border-collapse: collapse;
        }
        table.stats-banner td {
            text-align: center;
            padding: 6px 3px;
            border-right: 1px solid #93c5fd;
            vertical-align: middle;
        }
        table.stats-banner td:last-child {
            border-right: none;
        }
        .stat-value {
            font-size: 11pt;
            font-weight: bold;
            display: block;
            margin-bottom: 2px;
        }
        .stat-label {
            font-size: 6pt;
            color: #1e40af;
            text-transform: uppercase;
            font-weight: bold;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* SECTIONS                                                */
        /* ═══════════════════════════════════════════════════════ */
        .section {
            margin-bottom: 8px;
        }
        .section-titre {
            text-align: center;
            font-size: 10pt;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        .section-contenu {
            font-size: 8pt;
            text-align: justify;
            line-height: 1.4;
            margin-bottom: 4px;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* TABLEAU PRINCIPAL                                       */
        /* ═══════════════════════════════════════════════════════ */
        table.main-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.5pt;
        }
        table.main-table th {
            padding: 4px 2px;
            text-align: center;
            font-size: 6pt;
            font-weight: bold;
            color: #1d4ed8;
            background-color: #dbeafe;
            border: 1px solid #bfdbfe;
            text-transform: uppercase;
        }
        table.main-table td {
            padding: 3px 2px;
            text-align: center;
            font-size: 6.5pt;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }
        table.main-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .num-cell {
            font-weight: bold;
            color: #1d4ed8;
            font-size: 6pt;
        }
        .nom-cell {
            font-weight: bold;
            color: #0f172a;
            text-align: left;
        }
        .nom-cell small {
            display: block;
            font-weight: normal;
            color: #64748b;
            font-size: 5.5pt;
        }
        .sup-cell {
            font-weight: bold;
            color: #7c3aed;
        }
        .muted-cell {
            color: #64748b;
            font-size: 6pt;
        }
        .date-impl-cell {
            font-weight: bold;
            color: #15803d;
        }
        .date-aff-cell {
            font-weight: bold;
            color: #5b21b6;
        }
        .geo-cell {
            font-weight: bold;
            color: #1e3a5f;
            font-size: 6pt;
        }
        .heure-cell {
            font-weight: bold;
            color: #1e3a5f;
        }
        .frais-cell {
            text-align: center;
        }
        .frais-paye {
            color: #166534;
            font-weight: bold;
        }
        .frais-impaye {
            color: #991b1b;
            font-weight: bold;
        }
        .statut-badge {
            font-size: 6pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #1e3a5f;
        }
        .motif-refus {
            font-size: 5.5pt;
            color: #991b1b;
            font-style: italic;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* TABLEAU CHAUFFEURS                                      */
        /* ═══════════════════════════════════════════════════════ */
        table.chauffeurs-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
            margin-top: 5px;
        }
        table.chauffeurs-table th {
            padding: 4px 8px;
            text-align: left;
            font-weight: bold;
            color: #1e3a5f;
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            font-size: 7pt;
        }
        table.chauffeurs-table td {
            padding: 4px 8px;
            border: 1px solid #e2e8f0;
            font-size: 8pt;
        }
        table.chauffeurs-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* LISTES                                                  */
        /* ═══════════════════════════════════════════════════════ */
        .totaux-liste {
            margin-top: 4px;
            font-size: 8pt;
            padding-left: 20px;
        }
        .totaux-liste li {
            margin-bottom: 2px;
            font-weight: bold;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* SIGNATURE                                               */
        /* ═══════════════════════════════════════════════════════ */
        .signature {
            margin-top: 20px;
            text-align: right;
            font-size: 9pt;
        }
        .signature .nom {
            font-weight: bold;
            font-size: 10pt;
            color: #1e3a5f;
        }
        .signature .poste {
            font-style: italic;
            color: #475569;
            margin-top: 2px;
            font-size: 8pt;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* PIED DE PAGE                                            */
        /* ═══════════════════════════════════════════════════════ */
        .footer {
            font-size: 6pt;
            color: #94a3b8;
            text-align: center;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            margin-top: 20px;
        }

        /* ═══════════════════════════════════════════════════════ */
        /* PAGE 2 - TITRES                                         */
        /* ═══════════════════════════════════════════════════════ */
        .paysage-titre {
            text-align: center;
            font-size: 12pt;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .paysage-sous-titre {
            text-align: center;
            font-size: 9pt;
            font-weight: bold;
            color: #64748b;
            margin-bottom: 10px;
        }
        .paysage-recap {
            font-size: 8pt;
            text-align: justify;
            line-height: 1.4;
            margin-bottom: 10px;
            padding: 8px 12px;
            background-color: #f8fafc;
            border-left: 3px solid #1d4ed8;
        }
    </style>
</head>
<body>

@php
    // ═══════════════════════════════════════════════════════
    // SÉCURISATION DES VARIABLES
    // ═══════════════════════════════════════════════════════
    $dateSemaineFmt = $dateSemaine instanceof \Carbon\Carbon
        ? $dateSemaine->format('d/m/Y')
        : ($dateSemaine ? \Carbon\Carbon::parse($dateSemaine)->format('d/m/Y') : now()->format('d/m/Y'));

    $titreAffiche = $titreDocument ?? "Rapport des appréciations du {$dateSemaineFmt}";

    $lignesCollection = collect($lignes ?? []);

    $nbAffectations = $lignesCollection->sum(fn($l) => count($l['affectation_ids'] ?? []));
    $nbGeometres    = $lignesCollection->pluck('geometre_id')->filter()->unique()->count();
    $nbAcceptes     = $lignesCollection->where('statut_acceptation', 'accepte')->count();
    $nbRefuses      = $lignesCollection->where('statut_acceptation', 'refuse')->count();
    $nbAbsents      = $lignesCollection->where('statut_presence', 'absent')->count();
    $nbRetards      = $lignesCollection->where('statut_presence', 'retard')->count();

    $showStagiaire = $lignesCollection->first()['stagiaire'] ?? null;
    $showStagiaire = !empty($showStagiaire);

    $narratif = $narratif ?? [];
@endphp

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- PAGE 1 : PORTRAIT                                           --}}
{{-- ═══════════════════════════════════════════════════════════ --}}

    <div class="pdf-header">
    <img src="{{ public_path('images/eden-bon.jpeg') }}"
         alt="Logo EDEN Group"
         border="0"
         style="border:none;">
</div>

    <div class="rapport-titre">
        {{ $titreAffiche }}
    </div>

    @if($dateSemaine)
        <div class="rapport-date-impl">
            Date d'implantation : {{ $dateSemaineFmt }}
        </div>
    @endif

    @if($lignesCollection->count() > 0)
    <table class="stats-banner" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width:16.66%;">
                <span class="stat-value" style="color:#1d4ed8;">{{ $nbAffectations }}</span>
                <span class="stat-label">Affectations</span>
            </td>
            <td style="width:16.66%;">
                <span class="stat-value" style="color:#7c3aed;">{{ $nbGeometres }}</span>
                <span class="stat-label">Geometres</span>
            </td>
            <td style="width:16.66%;">
                <span class="stat-value" style="color:#16a34a;">{{ $nbAcceptes }}</span>
                <span class="stat-label">Acceptes</span>
            </td>
            <td style="width:16.66%;">
                <span class="stat-value" style="color:#dc2626;">{{ $nbRefuses }}</span>
                <span class="stat-label">Refuses</span>
            </td>
            <td style="width:16.66%;">
                <span class="stat-value" style="color:#991b1b;">{{ $nbAbsents }}</span>
                <span class="stat-label">Absents</span>
            </td>
            <td style="width:16.66%;">
                <span class="stat-value" style="color:#f59e0b;">{{ $nbRetards }}</span>
                <span class="stat-label">Retards</span>
            </td>
        </tr>
    </table>
    @endif

    @if(!empty($narratif['introduction']))
    <div class="section">
        <div class="section-titre">Introduction</div>
        <div class="section-contenu">{!! nl2br(e($narratif['introduction'])) !!}</div>
    </div>
    @endif

    @if(!empty($narratif['difficultes']))
    <div class="section">
        <div class="section-titre">Difficultes Rencontrees</div>
        <div class="section-contenu">{!! nl2br(e($narratif['difficultes'])) !!}</div>
    </div>
    @endif

    @if(!empty($narratif['securite']) || !empty($narratif['stagiaires_avis']))
    <div class="section">
        <div class="section-titre">
            Avis sur les Agents de Securite, Stagiaires, Chauffeurs, Photographe et Autres
        </div>

        @if(!empty($narratif['securite']))
        <div style="margin-bottom:4px;">
            <strong style="color:#1e3a5f; font-size:8pt;">Agents de securite</strong>
            <div class="section-contenu" style="margin-top:2px;">{!! nl2br(e($narratif['securite'])) !!}</div>
        </div>
        @endif

        @if(!empty($narratif['stagiaires_avis']))
        <div style="margin-bottom:4px;">
            <strong style="color:#1e3a5f; font-size:8pt;">Stagiaires</strong>
            <div class="section-contenu" style="margin-top:2px;">{!! nl2br(e($narratif['stagiaires_avis'])) !!}</div>
        </div>
        @endif
    </div>
    @endif

    @if(!empty($narratif['autres']) && is_array($narratif['autres']))
    <div class="section">
        <div class="section-titre">Chauffeurs et Autres</div>

        <table class="chauffeurs-table">
            <thead>
                <tr>
                    <th style="width:60%;">NOMS</th>
                    <th style="width:40%;">ROLE</th>
                </tr>
            </thead>
            <tbody>
                @foreach($narratif['autres'] as $agent)
                <tr>
                    <td style="font-weight:bold;">{{ $agent['nom'] ?? '—' }}</td>
                    <td>{{ $agent['role'] ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif

    <div class="section">
        <div class="section-titre">Conclusion</div>
        <div class="section-contenu">
            {!! nl2br(e($narratif['conclusion'] ?? 'Au terme de cette mission, la brigade des implantations du groupe EDEN a mene avec succes l\'ensemble des travaux topographiques prevus.')) !!}
        </div>
    </div>

    @if(!empty($narratif['pieces_jointes']) && is_array($narratif['pieces_jointes']) && count($narratif['pieces_jointes']) > 0)
    <div class="section">
        <div class="section-titre">Pieces Jointes</div>
        <ul class="totaux-liste">
            @foreach($narratif['pieces_jointes'] as $pj)
                <li>{{ $pj }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="signature">
        <div class="nom">{{ $narratif['signataire_nom'] ?? 'Josiane Morelle NGOLO AYISSI' }}</div>
        <div class="poste">{{ $narratif['signataire_poste'] ?? 'Chef de bureau des installations et du suivi des implantations' }}</div>
    </div>

    <div class="footer">
        B.P 35531 - Yaounde - Cameroun · Tel.: 699 16 41 43 / 652 224 680 · NIU: M042217297350S<br>
        Situe a Texaco Omnisport · Agrement N°: 0001/MINHDU/SG/DHSPI/SDPIAC du 07 Fevrier 2025
    </div>


{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- PAGE 2 : PAYSAGE                                            --}}
{{-- ═══════════════════════════════════════════════════════════ --}}

<pagebreak orientation="landscape" />

<div>

    <div class="paysage-titre">Tableau Recapitulatif</div>
    <div class="paysage-sous-titre">
        Rapport des appreciations du {{ $dateSemaineFmt }} — {{ $lignesCollection->count() }} ligne(s)
    </div>

    @if(!empty($narratif['recapitulatif']))
    <div class="paysage-recap">
        {!! nl2br(e($narratif['recapitulatif'])) !!}
    </div>
    @endif

    @if($lignesCollection->count() > 0)
    <table class="main-table">
        <thead>
            <tr>
                <th style="width:20px;">N°</th>
                <th style="width:90px;">Beneficiaire / Client</th>
                <th style="width:60px;">Facilitateur</th>
                <th style="width:60px;">Grand Site</th>
                <th style="width:45px;">Site</th>
                <th style="width:50px;">Titre Foncier</th>
                <th style="width:35px;">Bloc</th>
                <th style="width:50px;">Lots</th>
                <th style="width:50px;">Superficie</th>
                <th style="width:50px;">Date d'aff.</th>
                <th style="width:45px;">Frais</th>
                <th style="width:60px;">Geometre</th>
                @if($showStagiaire)
                    <th style="width:55px;">Stagiaire</th>
                @endif
                <th style="width:45px;">Heure</th>
                <th style="width:60px;">Statut</th>
                <th style="width:50px;">Date d'impl.</th>
            </tr>
        </thead>
        <tbody>
            @foreach($lignesCollection as $index => $ligne)
            @php
                $statutKey   = $ligne['statut_acceptation'] ?? 'en_attente';
                $statutLabel = $ligne['statut_final'] ?? null;

                if (!$statutLabel) {
                    $statutLabel = match($statutKey) {
                        'accepte' => 'Accepte',
                        'refuse'  => 'Refuse',
                        default   => 'En attente',
                    };
                }

                $beneficiaire = $ligne['beneficiaire'] ?? '—';
                $telephone    = $ligne['telephone'] ?? null;
                $facilitateur = $ligne['facilitateur'] ?? '—';
                $grandSite    = $ligne['grand_site'] ?? '—';
                $site         = $ligne['site'] ?? '—';
                $titreFoncier = $ligne['titre_foncier'] ?? '—';
                $bloc         = $ligne['bloc'] ?? '—';
                $lots         = $ligne['lots'] ?? null;
                $superficie   = $ligne['superficie'] ?? 0;
                $dateAff      = $ligne['date_affectation'] ?? null;
                $fraisPaye    = $ligne['frais_paye'] ?? false;
                $geometreNom  = $ligne['geometre_nom'] ?? null;
                $stagiaire    = $ligne['stagiaire'] ?? null;
                $heureImpl    = $ligne['heure_implantation'] ?? null;
                $dateImpl     = $ligne['date_implantation'] ?? null;
                $motifRefus   = $ligne['motif_refus'] ?? null;
            @endphp
            <tr>
                <td class="num-cell">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</td>
                <td class="nom-cell">
                    {{ $beneficiaire }}
                    @if(!empty($telephone))
                        <small>{{ $telephone }}</small>
                    @endif
                </td>
                <td class="muted-cell">{{ $facilitateur }}</td>
                <td>{{ $grandSite }}</td>
                <td>{{ $site }}</td>
                <td>{{ $titreFoncier }}</td>
                <td>{{ $bloc }}</td>
                <td style="font-weight:bold; color:#0f172a;">{{ $lots ?: '—' }}</td>
                <td class="sup-cell">{{ number_format($superficie, 0, ',', ' ') }} m²</td>
                <td class="date-aff-cell">
                    @if(!empty($dateAff))
                        {{ \Carbon\Carbon::parse($dateAff)->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
                <td class="frais-cell">
                    @if($fraisPaye)
                        <span class="frais-paye">Paye</span>
                    @else
                        <span class="frais-impaye">Non paye</span>
                    @endif
                </td>
                <td class="geo-cell">{{ !empty($geometreNom) ? $geometreNom : '—' }}</td>
                @if($showStagiaire)
                    <td class="muted-cell">{{ !empty($stagiaire) ? $stagiaire : '—' }}</td>
                @endif
                <td class="heure-cell">{{ !empty($heureImpl) ? substr($heureImpl, 0, 5) : '—' }}</td>
                <td style="text-align:center;">
                    <span class="statut-badge">{{ $statutLabel }}</span>
                    @if(!empty($motifRefus))
                        <div class="motif-refus">{{ \Illuminate\Support\Str::limit($motifRefus, 40) }}</div>
                    @endif
                </td>
                <td class="date-impl-cell">
                    @if(!empty($dateImpl))
                        {{ \Carbon\Carbon::parse($dateImpl)->format('d/m/Y') }}
                    @else
                        —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <div style="text-align:center;padding:40px;color:#94a3b8;">
        <div style="font-weight:bold;font-size:11pt;color:#475569;">
            Aucune affectation a afficher
        </div>
    </div>
    @endif

</div>

</body>
</html>