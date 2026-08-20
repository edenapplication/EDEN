<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="csrf-token" content="{{ csrf_token() }}">
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
        position: running(header);
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

    .entete-centre {
        flex: 1;
        text-align: center;
        margin-top: -22px;
    }
    .document-title {
        font-size: 11pt;
        font-weight: 900;
        color: #1e3a5f;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-top: 4px;
    }

    .entete-droite {
        text-align: right;
        min-width: 150px;
        font-size: 8.5pt;
    }
    .entete-droite .info-line {
        margin-bottom: 3px;
    }
    .entete-droite strong {
        color: #1e3a5f;
    }

    .titre-bon {
        width: 100%;
        margin: 8px 0 10px 0;
        background-color: #f1f5f9;
        border-left: 4px solid #1d4ed8;
        padding: 8px 14px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .titre-bon .tb-titre {
        font-size: 12pt;
        font-weight: 900;
        color: #1e3a5f;
        text-transform: uppercase;
    }
    .titre-bon .tb-num {
        font-size: 9.5pt;
        font-weight: 700;
        color: #dc2626;
        background-color: #fee2e2;
        padding: 4px 14px;
        border-radius: 4px;
    }

    .date-ligne {
        font-size: 8pt;
        color: #64748b;
        margin-bottom: 10px;
        text-align: right;
    }

    .info-table {
        width: 48%;
        border-collapse: collapse;
        display: inline-table;
        vertical-align: top;
        margin-bottom: 8px;
        font-size: 8pt;
    }
    .info-table:last-child { margin-right: 0; }
    .info-table .bloc-titre {
        background-color: #1e3a5f;
        color: white;
        font-size: 7.5pt;
        font-weight: 700;
        padding: 4px 10px;
        text-transform: uppercase;
        text-align: left;
    }
    .info-table td.lbl {
        font-size: 7.5pt;
        color: #64748b;
        text-transform: uppercase;
        padding: 4px 10px;
        border: 1px solid #f1f5f9;
        width: 40%;
    }
    .info-table td.val {
        font-size: 8pt;
        font-weight: 700;
        color: #1e3a5f;
        padding: 4px 10px;
        border: 1px solid #f1f5f9;
    }

    .vers-table {
        width: 100%;
        border-collapse: collapse;
        margin: 8px 0;
        font-size: 8.5pt;
    }
    .vers-table thead tr { background-color: #1e3a5f; color: white; }
    .vers-table thead th {
        padding: 5px 10px;
        font-size: 8pt;
        text-align: left;
    }
    .vers-table thead th.tr { text-align: right; }
    .vers-table tbody td {
        padding: 5px 10px;
        border-bottom: 1px solid #e2e8f0;
    }
    .vers-table tbody td.tr {
        text-align: right;
        font-family: monospace;
        font-weight: 700;
    }
    .vers-table tfoot td {
        padding: 6px 10px;
        font-size: 9pt;
        font-weight: 900;
    }
    .vers-table tfoot .total-lbl { color: #1e3a5f; }
    .vers-table tfoot .total-val {
        text-align: right;
        color: #16a34a;
        font-family: monospace;
    }
    /* Suppression des ::before qui causaient les points d'interrogation */
    .v-dossier  { color: #1d4ed8; font-weight: 600; }
    .v-tech     { color: #ea580c; font-weight: 600; }
    .v-logi     { color: #7c3aed; font-weight: 600; }
    .v-morcel   { color: #ca8a04; font-weight: 600; }

    .cumul-table {
        width: 100%;
        border-collapse: collapse;
        margin: 6px 0;
        background-color: #f8fafc;
        font-size: 8pt;
    }
    .cumul-table td {
        padding: 5px 10px;
        border: 1px solid #e2e8f0;
    }
    .cumul-table .c-lbl { color: #64748b; }
    .cumul-table .c-val {
        font-weight: 700;
        text-align: right;
        font-family: monospace;
    }
    .cumul-table .c-total-lbl {
        font-weight: 800;
        color: #1e3a5f;
        background-color: #f0fdf4;
    }
    .cumul-table .c-total-val {
        font-weight: 900;
        color: #16a34a;
        text-align: right;
        background-color: #f0fdf4;
        font-family: monospace;
        font-size: 9pt;
    }

    .reste-row td {
        background-color: #fff1f2 !important;
        border-top: 2px solid #fecaca !important;
        padding: 6px 10px !important;
        font-size: 9pt !important;
    }
    .reste-row .r-lbl {
        color: #b91c1c !important;
        font-weight: 800 !important;
    }
    .reste-row .r-val {
        color: #b91c1c !important;
        font-weight: 900 !important;
        text-align: right !important;
        font-family: monospace !important;
        font-size: 10pt !important;
    }

    .notes-box {
        background: #f8fafc;
        border-left: 3px solid #1d4ed8;
        padding: 5px 12px;
        border-radius: 0 4px 4px 0;
        margin: 6px 0;
        font-size: 8pt;
        color: #374151;
    }

    .sig-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 18px;
    }
    .sig-table td {
        width: 50%;
        text-align: center;
        padding: 0 8px;
        vertical-align: bottom;
    }
    .sig-line {
        border-top: 1.5px solid #374151;
        margin-top: 25px;
        padding-top: 5px;
        font-size: 8pt;
        font-weight: 700;
        color: #374151;
    }
    .sig-sub {
        font-size: 7pt;
        font-weight: 400;
        color: #64748b;
        margin-top: 2px;
    }

    .pied {
        margin-top: 25px;
        padding-top: 10px;
        border-top: 1px solid #e2e8f0;
        display: flex;
        justify-content: space-between;
        font-size: 7pt;
        color: #94a3b8;
    }

    .clearfix { clear: both; }
</style>
</head>
<body>

    {{-- ===== EN-TÊTE IMAGE ===== --}}
    <header class="pdf-header">
        <img src="{{ public_path('images/entete2.png') }}" 
             class="header-image" 
             alt="En-tête" 
             style="width:100%; height:900px; object-fit:cover;">
    </header>

<div class="page">
    {{-- ===== PHP CALCULS ===== --}}
    @php
     $timezone = 'Africa/Douala';
    $now = now()->setTimezone($timezone);

    $dateBon = $bon->date_bon ? $bon->date_bon->setTimezone($timezone) : $now;
    $createdAt = $bon->created_at ? $bon->created_at->setTimezone($timezone) : $now;

        $dossier = $bon->dossier;
        $client  = $dossier?->client;
        $site    = $dossier?->grandSite;

        $refTotal = ($dossier?->prix_superficie   ?? 0)
                  + ($dossier?->prix_technique    ?? 0)
                  + ($dossier?->prix_logistique   ?? 0)
                  + ($dossier?->prix_morcellement ?? 0);

        $totalCumul = ($bon->total_dossier_cumul ?? 0)
                    + ($bon->total_technique_cumul ?? 0)
                    + ($bon->total_logistique_cumul ?? 0)
                    + ($bon->total_morcellement_cumul ?? 0);
        // Calcul du prix unitaire
$prixUnitaire = 0;
if (($dossier?->prix_superficie ?? 0) > 0 && ($dossier?->superficie_voulue ?? 0) > 0) {
    $prixUnitaire = $dossier->prix_superficie / $dossier->superficie_voulue;
}

        $resteTotal = max(0, $refTotal - $totalCumul);
    @endphp

    {{-- ===== ENTÊTE TEXTE ===== --}}
    <div class="entete">
        <div class="entete-centre">
    <div class="document-title" style="font-size: 25pt; font-weight: 900; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.8px;">BON DE PAIEMENT</div>
    <div style="font-size: 14pt; font-weight: 800; color: #475569; text-transform: uppercase; margin-top: 3px;">EDEN GROUP ENTREPRISE</div>
    <div style="font-size: 14pt; font-weight: 600; color: #475569; margin-top: 2px;">Direction des Opérations - Service Commerciale</div>
</div>

        <div class="entete-droite">
            <div class="info-line">
                <strong>N° :</strong> EDG-{{ $now->format('dmY') }}-{{ str_pad($bon->id, 4, '0', STR_PAD_LEFT) }}
            </div>
            <div class="info-line">
                <strong>Date :</strong> {{ $bon->date_bon?->format('d/m/Y') ?? now()->format('d/m/Y') }}
            </div>
        </div>
    </div>

    {{-- ===== DATE ===== --}}
<div class="date-ligne">
    Yaoundé, le {{ $dateBon->format('d/m/Y') }}
    | Émis le {{ $createdAt->format('d/m/Y à H:i') }}
</div>



    {{-- ===== INFOS CLIENT ===== --}}
    <table class="info-table">
        <tr><td colspan="2" class="bloc-titre">👤 Client</td></tr>
        <tr><td class="lbl">Nom</td><td class="val">{{ $client?->name ?? '-' }}</td></tr>
        <tr><td class="lbl">Téléphone</td><td class="val">{{ $client?->phone ?? '-' }}</td></tr>
    </table>

    {{-- ===== INFOS SITE ===== --}}
    <table class="info-table">
        <tr><td colspan="2" class="bloc-titre">🏢 Site</td></tr>
        <tr><td class="lbl">Site</td><td class="val">{{ $site?->nom ?? $dossier?->nom_dossier ?? '-' }}</td></tr>
        @if(($dossier?->superficie_voulue ?? 0) > 0)
        <tr><td class="lbl">Superficie</td><td class="val">{{ number_format($dossier->superficie_voulue, 0, ',', ' ') }} m²</td></tr>
        @endif
        @if($prixUnitaire > 0)
<tr><td class="lbl">Prix unitaire</td><td class="val">{{ number_format($prixUnitaire, 0, ',', ' ') }} FCFA/m²</td></tr>
@endif
        <tr><td class="lbl">Prix Terrain</td><td class="val">{{ number_format($dossier?->prix_superficie ?? 0, 0, ',', ' ') }} FCFA</td></tr>
    </table>

    <div class="clearfix"></div>

    {{-- ===== VERSEMENTS DU JOUR ===== --}}
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
                <td class="tr v-dossier">{{ number_format($bon->versement_dossier, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            @if(($bon->versement_technique ?? 0) > 0)
            <tr>
                <td><span class="v-tech">Paiement du Dossier Technique</span></td>
                <td class="tr v-tech">{{ number_format($bon->versement_technique, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            @if(($bon->versement_logistique ?? 0) > 0)
            <tr>
                <td><span class="v-logi">Frais de Logistique d'implantation</span></td>
                <td class="tr v-logi">{{ number_format($bon->versement_logistique, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endif
            @if(($bon->versement_morcellement ?? 0) > 0)
            <tr>
                <td><span class="v-morcel">Frais Morcellement</span></td>
                <td class="tr v-morcel">{{ number_format($bon->versement_morcellement, 0, ',', ' ') }} FCFA</td>
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

    {{-- ===== CUMUL + RESTE (fusionnés) ===== --}}
    <table class="cumul-table">
        <tr>
            <td class="c-lbl" style="font-weight:700; color:#1e3a5f;" colspan="2">
                📊 Cumul total versé
            </td>
        </tr>
        @if(($bon->total_dossier_cumul ?? 0) > 0)
        <tr>
            <td class="c-lbl">📁 paiement de la Parcelle</td>
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

    {{-- ===== NOTES ===== --}}
    @if($bon->notes)
    <div class="notes-box">
        <strong>Note :</strong> {{ $bon->notes }}
    </div>
    @endif

    {{-- ===== SIGNATURES ===== --}}
    <table class="sig-table">
        <tr>
            <td>
                <div class="sig-line">
                    Conseillère Commerciale
                    <div class="sig-sub">{{ '_________________' }}</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- ===== PIED ===== --}}
<div class="pied">
    <div>EDEN GROUP SARL — Bon de Paiement</div>
    <div>{{ $bon->numero_bon }} — {{ $now->format('d/m/Y H:i') }}</div>
</div>

</div>

</body>
</html>