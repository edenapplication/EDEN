<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 9.5pt;
        color: #1e293b;
        background: white;
        line-height: 1.4;
    }
    .page { padding: 18px 22px; }

    @page {
        size: A4 portrait;
        margin: 1cm 0.8cm 1.5cm 0.8cm;
    }

    .pdf-header {
        width: 100%;
        height: 135px;
        object-fit: cover;
    }

    .entete {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
        padding-bottom: 10px;
        gap: 15px;
    }

    .entete-centre { flex: 1; text-align: center; margin-top: -22px; }
    .document-title {
        font-size: 11pt; font-weight: 900; color: #1e3a5f;
        text-transform: uppercase; letter-spacing: 0.5px; margin-top: 4px;
    }

    .entete-droite {
        text-align: right; min-width: 150px; font-size: 8.5pt;
    }
    .entete-droite .info-line { margin-bottom: 3px; }
    .entete-droite strong { color: #1e3a5f; }

    .ant-badge {
        display: inline-block;
        background: #fef3c7; color: #92400e;
        border: 1.5px solid #fcd34d;
        padding: 2px 8px; border-radius: 4px;
        font-size: 7pt; font-weight: 800;
        margin-left: 6px; text-transform: uppercase;
    }

    .date-ligne {
        font-size: 8pt; color: #64748b;
        margin-bottom: 10px; text-align: right;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* ✅ NOUVEAU : Tableau 2 lignes (Client + Site)              */
    /* ═══════════════════════════════════════════════════════════ */
    table.section-2lignes {
        width: 100%; border-collapse: collapse;
        margin: 0 0 8px 0; font-size: 7.5pt;
        table-layout: auto;
    }
    table.section-2lignes thead { background: #1e3a5f; color: white; }
    table.section-2lignes thead th {
        padding: 5px 6px; font-size: 6.8pt; font-weight: 700;
        text-align: center; text-transform: uppercase;
        letter-spacing: 0.3px; border: 1px solid #1e3a5f;
        word-wrap: break-word; line-height: 1.2;
    }
    table.section-2lignes tbody td {
        padding: 7px 6px; text-align: center;
        font-family: monospace; font-weight: 700;
        font-size: 8pt; color: #1e3a5f;
        border: 1px solid #e2e8f0; background: #ffffff;
        word-wrap: break-word;
    }
    table.section-2lignes tbody td.val-left {
        text-align: center; font-family: DejaVu Sans, sans-serif;
        font-weight: 700; color: #1d4ed8; font-size: 8.2pt;
    }

    /* ═══════════════════════════════════════════════════════════ */
    /* ✅ NOUVEAU : Section bénéficiaires (1 ligne par bénéf)     */
    /* ═══════════════════════════════════════════════════════════ */
    table.section-benef {
        width: 100%; border-collapse: collapse;
        margin: 10px 0; font-size: 8pt;
        border: 1px solid #a855f7;
    }
    table.section-benef tbody td {
        padding: 6px 10px;
        border-bottom: 1px solid #e9d5ff;
        vertical-align: middle;
    }
    table.section-benef tbody tr:last-child td {
        border-bottom: none;
    }
    table.section-benef tbody td.td-benef-nom {
        width: 42%; background: #faf5ff;
        text-align: left; font-family: DejaVu Sans, sans-serif;
        font-size: 8.2pt; color: #1e3a5f;
    }
    table.section-benef tbody td.td-benef-paiements {
        width: 58%; background: white; text-align: left;
    }
    .num-badge {
        display: inline-block; background: #7c3aed; color: white;
        width: 18px; height: 18px; border-radius: 50%;
        font-size: 8pt; font-weight: 800; text-align: center;
        line-height: 18px; margin-right: 6px;
    }
    .paiement-chip {
        display: inline-block; padding: 2px 8px; border-radius: 5px;
        font-size: 7.2pt; font-weight: 700; margin: 2px 0;
        font-family: monospace;
    }
    .paiement-chip .chip-type {
        font-family: DejaVu Sans, sans-serif;
        font-size: 6.8pt; opacity: 0.85;
    }
    .paiement-technique    { background: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .paiement-logistique   { background: #faf5ff; color: #7c3aed; border: 1px solid #e9d5ff; }
    .paiement-morcellement { background: #fefce8; color: #ca8a04; border: 1px solid #fde68a; }
    .paiement-dossier      { background: #eff6ff; color: #1d4ed8; border: 1px solid #93c5fd; }

    /* ═══════════════════════════════════════════════════════════ */
    /* PRIX-LIGNE (conservé)                                       */
    /* ═══════════════════════════════════════════════════════════ */
    .prix-ligne {
        display: flex; flex-wrap: wrap;
        gap: 4px 12px; padding: 6px 10px;
        background: #f8fafc; border-radius: 6px;
        border: 1px solid #e2e8f0;
        margin-top: 4px; font-size: 7.5pt;
    }
    .prix-item {
        display: inline-flex; align-items: center;
        gap: 4px; white-space: nowrap;
    }
    .prix-item .p-libelle { color: #64748b; font-weight: 600; }
    .prix-item .p-valeur { font-weight: 700; }

    /* ═══════════════════════════════════════════════════════════ */
    /* VERSEMENTS DU JOUR (conservé)                               */
    /* ═══════════════════════════════════════════════════════════ */
    .vers-table {
        width: 100%; border-collapse: collapse;
        margin: 8px 0; font-size: 8.5pt;
    }
    .vers-table thead tr { background-color: #1e3a5f; color: white; }
    .vers-table thead th {
        padding: 5px 10px; font-size: 8pt; text-align: left;
    }
    .vers-table thead th.tr { text-align: right; }
    .vers-table tbody td {
        padding: 5px 10px; border-bottom: 1px solid #e2e8f0;
    }
    .vers-table tbody td.tr {
        text-align: right; font-family: monospace; font-weight: 700;
    }
    .vers-table tfoot td {
        padding: 6px 10px; font-size: 9pt; font-weight: 900;
    }
    .vers-table tfoot .total-lbl { color: #1e3a5f; }
    .vers-table tfoot .total-val {
        text-align: right; color: #16a34a; font-family: monospace;
    }
    .v-dossier  { color: #1d4ed8; font-weight: 600; }
    .v-tech     { color: #ea580c; font-weight: 600; }
    .v-logi     { color: #7c3aed; font-weight: 600; }
    .v-morcel   { color: #ca8a04; font-weight: 600; }

    /* ═══════════════════════════════════════════════════════════ */
    /* CUMUL + RESTE (conservé)                                    */
    /* ═══════════════════════════════════════════════════════════ */
    .cumul-table {
        width: 100%; border-collapse: collapse;
        margin: 6px 0; background-color: #f8fafc;
        font-size: 8pt;
    }
    .cumul-table td {
        padding: 5px 10px; border: 1px solid #e2e8f0;
    }
    .cumul-table .c-lbl { color: #64748b; }
    .cumul-table .c-val {
        font-weight: 700; text-align: right; font-family: monospace;
    }
    .cumul-table .c-total-lbl {
        font-weight: 800; color: #1e3a5f; background-color: #f0fdf4;
    }
    .cumul-table .c-total-val {
        font-weight: 900; color: #16a34a; text-align: right;
        background-color: #f0fdf4; font-family: monospace; font-size: 9pt;
    }

    .reste-row td {
        background-color: #fff1f2 !important;
        border-top: 2px solid #fecaca !important;
        padding: 6px 10px !important; font-size: 9pt !important;
    }
    .reste-row .r-lbl {
        color: #b91c1c !important; font-weight: 800 !important;
    }
    .reste-row .r-val {
        color: #b91c1c !important; font-weight: 900 !important;
        text-align: right !important; font-family: monospace !important;
        font-size: 10pt !important;
    }

    .notes-box {
        background: #f8fafc;
        border-left: 3px solid #1d4ed8;
        padding: 5px 12px; border-radius: 0 4px 4px 0;
        margin: 6px 0; font-size: 8pt; color: #374151;
    }

    .sig-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    .sig-table td {
        width: 50%; text-align: center;
        padding: 0 8px; vertical-align: bottom;
    }
    .sig-table td.droite { text-align: right; }
    .sig-line {
        border-top: 1.5px solid #374151;
        margin-top: 30px; padding-top: 5px;
        font-size: 8pt; font-weight: 700;
        color: #374151; display: inline-block;
        min-width: 180px; text-align: center;
    }

    .pied {
        margin-top: 25px; padding-top: 10px;
        display: flex; justify-content: space-between;
        font-size: 7pt; color: #94a3b8;
    }

    .clearfix { clear: both; }
</style>
</head>
<body>

    {{-- EN-TÊTE IMAGE --}}
    <header class="pdf-header">
        <img src="{{ public_path('images/entete2.png') }}"
             class="header-image"
             alt="En-tête"
             style="width:100%; height:900px; object-fit:cover;">
    </header>

<div class="page">
    @php
    $timezone = 'Africa/Douala';
    $now = now()->setTimezone($timezone);

    $dateBon = $bon->date_bon ? $bon->date_bon->setTimezone($timezone) : $now;
    $createdAt = $bon->created_at ? $bon->created_at->setTimezone($timezone) : $now;

    $dossier = $bon->dossier;
    $client  = $dossier?->client;
    $site    = $dossier?->grandSite;

    // ✅ Détection ANT
    $dateAujourdhui = $now->format('Y-m-d');
    $dateDuBon = $dateBon->format('Y-m-d');
    $estAntidate = ($dateDuBon !== $dateAujourdhui);

    $lettreP = ($bon->versement_dossier ?? 0) > 0 ? 'P' : '';
    $lettreT = ($bon->versement_technique ?? 0) > 0 ? 'T' : '';
    $lettreL = ($bon->versement_logistique ?? 0) > 0 ? 'L' : '';
    $lettreM = ($bon->versement_morcellement ?? 0) > 0 ? 'M' : '';

    $referenceUser = $bon->user_reference ?? 'XXXXXX';
    $an = $now->format('y');
    $mois = $now->format('m');
    $jour = $now->format('d');

    $prefixeANT = $estAntidate ? 'ANT-' : '';
    $numeroBon = $prefixeANT . 'EDG-' . $an . $lettreP . $mois . $lettreT . $jour . $lettreL . '-' . $referenceUser . $lettreM . '-' . str_pad($bon->id, 4, '0', STR_PAD_LEFT);

    $prixTerrain    = $dossier?->prix_superficie   ?? 0;
    $prixTechnique  = $dossier?->prix_technique    ?? 0;
    $prixLogistique = $dossier?->prix_logistique   ?? 0;
    $prixMorcel     = $dossier?->prix_morcellement ?? 0;
    $refTotal = $prixTerrain + $prixTechnique + $prixLogistique + $prixMorcel;

    $totalCumul = ($bon->total_dossier_cumul ?? 0)
                + ($bon->total_technique_cumul ?? 0)
                + ($bon->total_logistique_cumul ?? 0)
                + ($bon->total_morcellement_cumul ?? 0);

    $prixUnitaire = 0;
    if ($prixTerrain > 0 && ($dossier?->superficie_voulue ?? 0) > 0) {
        $prixUnitaire = $prixTerrain / $dossier->superficie_voulue;
    }

    $resteTotal = max(0, $refTotal - $totalCumul);

    // ✅ Récupérer UNIQUEMENT les bénéficiaires de CE bon
    $benefPaiements = \App\Models\BeneficiairePaiement::with('beneficiaire')
        ->where('bon_id', $bon->id)
        ->orderBy('id')
        ->get();
    @endphp

    {{-- ENTÊTE TEXTE --}}
    <div class="entete">
        <div class="entete-centre">
            <div class="document-title" style="font-size: 25pt; font-weight: 900; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.8px;">BON DE PAIEMENT</div>
            <div style="font-size: 14pt; font-weight: 800; color: #475569; text-transform: uppercase; margin-top: 3px;">EDEN GROUP ENTREPRISE</div>
            <div style="font-size: 14pt; font-weight: 600; color: #475569; margin-top: 2px;">Direction des Opérations - Service Commercial</div>
        </div>

        <div class="entete-droite">
            <div class="info-line">
                <strong>N° :</strong>
                {{ $numeroBon }}
            </div>
            <div class="info-line">
                <strong>Date :</strong> {{ $bon->date_bon?->format('d/m/Y') ?? now()->format('d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- DATE --}}
    <div class="date-ligne">
        Yaoundé, le {{ $dateBon->format('d/m/Y') }}
        | Émis le {{ $createdAt->format('d/m/Y à H:i') }}
    </div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ✅ MODIFICATION 1 : CLIENT + SITE (2 LIGNES)               --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    <table class="section-2lignes">
        <thead>
            <tr>
                <th>Nom</th>
                <th>Téléphone</th>
                <th>Site</th>
                <th>Superficie</th>
                <th>Prix unitaire</th>
                <th>Prix du Terrain</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="val-left">{{ $client?->name ?? '-' }}</td>
                <td>{{ $client?->phone ?? '-' }}</td>
                <td>{{ $site?->nom ?? $dossier?->nom_dossier ?? '-' }}</td>
                <td>
                    @if(($dossier?->superficie_voulue ?? 0) > 0)
                        {{ number_format($dossier->superficie_voulue, 0, ',', ' ') }} m²
                    @else
                        —
                    @endif
                </td>
                <td>
                    @if($prixUnitaire > 0)
                        {{ number_format($prixUnitaire, 0, ',', ' ') }} FCFA/m²
                    @else
                        —
                    @endif
                </td>
                <td>{{ number_format($prixTerrain, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tbody>
    </table>

    {{-- LIGNE DES PRIX (CONSERVÉE TELLE QUELLE) --}}
    <div style="margin-bottom: 8px; width: 100%;">
        <div style="background:#f8fafc; border-radius:6px; padding:6px 10px; border:1px solid #e2e8f0;">
            <div class="prix-ligne">
                @if($prixTerrain > 0)
                <span class="prix-item">
                    <span class="p-libelle">Prix du Terrain</span>
                    <span class="p-valeur" style="color:#1d4ed8;">{{ number_format($prixTerrain, 0, ',', ' ') }} FCFA -</span>
                </span>
                @endif
                @if($prixTechnique > 0)
                <span class="prix-item">
                    <span class="p-libelle" style="color:#ea580c;">Dossier Technique</span>
                    <span class="p-valeur" style="color:#ea580c;">{{ number_format($prixTechnique, 0, ',', ' ') }} FCFA -</span>
                </span>
                @endif
                @if($prixLogistique > 0)
                <span class="prix-item">
                    <span class="p-libelle" style="color:#7c3aed;">Logistique d'implantation</span>
                    <span class="p-valeur" style="color:#7c3aed;">{{ number_format($prixLogistique, 0, ',', ' ') }} FCFA -</span>
                </span>
                @endif
                @if($prixMorcel > 0)
                <span class="prix-item">
                    <span class="p-libelle" style="color:#ca8a04;">Frais de Morcellement</span>
                    <span class="p-valeur" style="color:#ca8a04;">{{ number_format($prixMorcel, 0, ',', ' ') }} FCFA</span>
                </span>
                @endif
                <span class="prix-item" style="border-left:2px solid #16a34a; padding-left:8px;">
                    <span class="p-libelle" style="font-weight:800; color:#1e3a5f;">TOTAL</span>
                    <span class="p-valeur" style="font-weight:900; color:#16a34a; font-size:9pt;">{{ number_format($refTotal, 0, ',', ' ') }} FCFA</span>
                </span>
            </div>
        </div>
    </div>

    <div class="clearfix"></div>

    {{-- ═══════════════════════════════════════════════════════════ --}}
    {{-- ✅ MODIFICATION 2 : BÉNÉFICIAIRES (1 LIGNE PAR BÉNÉF)       --}}
    {{-- ═══════════════════════════════════════════════════════════ --}}
    @php
        $benefGroupes = $benefPaiements->groupBy('beneficiaire_id')->map(function($items) {
            $benef = $items->first()->beneficiaire;
            $types = [];
            foreach ($items as $item) {
                $label = match($item->paiement_type) {
                    'technique'    => 'Technique',
                    'logistique'   => 'Logistique',
                    'morcellement' => 'Morcellement',
                    'dossier'      => 'Parcelle',
                    default        => ucfirst($item->paiement_type),
                };
                $types[] = [
                    'label'   => $label,
                    'montant' => $item->montant,
                    'class'   => 'paiement-' . strtolower($item->paiement_type),
                ];
            }
            return [
                'beneficiaire' => $benef,
                'types'        => $types,
            ];
        })->values();
    @endphp

    @if($benefGroupes->count() > 0)
    <table class="section-benef">
        <tbody>
            @foreach($benefGroupes as $i => $groupe)
                <tr>
                    <td class="td-benef-nom">
                        <span class="num-badge">{{ $i + 1 }}</span>
                        <strong>{{ $groupe['beneficiaire']?->nom_avec_civilite ?? $groupe['beneficiaire']?->nom ?? 'Inconnu' }}</strong>
                    </td>
                    <td class="td-benef-paiements">
                        @foreach($groupe['types'] as $j => $t)
                            <span class="paiement-chip {{ $t['class'] }}">
                                {{ number_format($t['montant'], 0, ',', ' ') }} FCFA
                                <span class="chip-type">({{ $t['label'] }})</span>
                            </span>
                            @if(!$loop->last)
                                <span style="color:#94a3b8;">, </span>
                            @endif
                        @endforeach
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- VERSEMENTS DU JOUR (CONSERVÉE TELLE QUELLE) --}}
    <table class="vers-table">
        <thead>
            <tr>
                <th>Type de paiement</th>
                <th class="tr">Versement (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @if(($bon->versement_dossier ?? 0) > 0)
            <tr>
                <td><span class="v-dossier">Paiement de la Parcelle</span></td>
                <td class="tr v-dossier">{{ number_format($bon->versement_dossier, 0, ',', ' ') }}</td>
            </tr>
            @endif
            @if(($bon->versement_technique ?? 0) > 0)
            <tr>
                <td><span class="v-tech">Frais de Dossier Technique</span></td>
                <td class="tr v-tech">{{ number_format($bon->versement_technique, 0, ',', ' ') }}</td>
            </tr>
            @endif
            @if(($bon->versement_logistique ?? 0) > 0)
            <tr>
                <td><span class="v-logi">Frais de la Logistique d'implantation</span></td>
                <td class="tr v-logi">{{ number_format($bon->versement_logistique, 0, ',', ' ') }}</td>
            </tr>
            @endif
            @if(($bon->versement_morcellement ?? 0) > 0)
            <tr>
                <td><span class="v-morcel">Frais de Morcellement</span></td>
                <td class="tr v-morcel">{{ number_format($bon->versement_morcellement, 0, ',', ' ') }}</td>
            </tr>
            @endif
        </tbody>
        <tfoot>
            <tr style="background-color:#f0fdf4; border-top: 2px solid #16a34a;">
                <td class="total-lbl">TOTAL DU JOUR</td>
                <td class="total-val">{{ number_format($bon->total_versement ?? 0, 0, ',', ' ') }} FCFA</td>
            </tr>
        </tfoot>
    </table>

    {{-- CUMUL + RESTE (CONSERVÉE TELLE QUELLE) --}}
    <table class="cumul-table">
        <tr>
            <td class="c-lbl" style="font-weight:700; color:#1e3a5f;" colspan="2">
                📊 Cumul total versé
            </td>
        </tr>
        @if(($bon->total_dossier_cumul ?? 0) > 0)
        <tr>
            <td class="c-lbl">📁 Paiement de la Parcelle</td>
            <td class="c-val" style="color:#1d4ed8;">{{ number_format($bon->total_dossier_cumul, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endif
        @if(($bon->total_technique_cumul ?? 0) > 0)
        <tr>
            <td class="c-lbl">🛠️ Paiement du Dossier Technique</td>
            <td class="c-val" style="color:#ea580c;">{{ number_format($bon->total_technique_cumul, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endif
        @if(($bon->total_logistique_cumul ?? 0) > 0)
        <tr>
            <td class="c-lbl">🚗 Frais de Logistique d'implantation</td>
            <td class="c-val" style="color:#7c3aed;">{{ number_format($bon->total_logistique_cumul, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endif
        @if(($bon->total_morcellement_cumul ?? 0) > 0)
        <tr>
            <td class="c-lbl">✂️ Paiement du Morcellement</td>
            <td class="c-val" style="color:#ca8a04;">{{ number_format($bon->total_morcellement_cumul, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endif
        <tr>
            <td class="c-total-lbl">TOTAL CUMULÉ</td>
            <td class="c-total-val">{{ number_format($totalCumul, 0, ',', ' ') }} FCFA</td>
        </tr>
        @if(($bon->afficher_reste ?? true) && $refTotal > 0)
        <tr class="reste-row">
            <td class="r-lbl">RESTE À PAYER</td>
            <td class="r-val">{{ number_format($resteTotal, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endif
    </table>

    {{-- NOTES --}}
    @if($bon->notes)
    <div class="notes-box">
        <strong>Note :</strong> {{ $bon->notes }}
    </div>
    @endif

    {{-- SIGNATURES --}}
    <table class="sig-table">
        <tr>
            <td style="text-align:left;">
            </td>
            <td class="droite" style="text-align:right;">
                <div class="sig-line" style="min-width:180px;">
                    Conseillère Commerciale
                </div>
            </td>
        </tr>
    </table>

    {{-- PIED --}}
    <div class="pied">
        <div>EDEN GROUP SARL — Bon de Paiement</div>
        <div>{{ $bon->numero_bon }} — {{ $now->format('d/m/Y H:i') }}</div>
    </div>

</div>

</body>
</html>