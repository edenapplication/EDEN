<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:9px; color:#1e293b; padding:16px; }
    .header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; border-bottom:3px solid #1d4ed8; padding-bottom:12px; }
    .company { font-size:16px; font-weight:900; color:#1e3a5f; }
    .company-sub { font-size:9px; color:#64748b; margin-top:2px; }
    .title { font-size:13px; font-weight:700; color:#1e3a5f; text-align:right; }
    .title-sub { font-size:9px; color:#64748b; margin-top:2px; }
    .section { margin-bottom:12px; }
    .section-title { font-size:10px; font-weight:700; color:white; background:#1e3a5f; padding:4px 8px; border-radius:3px; margin-bottom:6px; }
    table { width:100%; border-collapse:collapse; }
    thead tr { background:#1e3a5f; color:white; }
    thead th { padding:5px 4px; font-size:7.5px; text-align:left; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody td { padding:4px; border-bottom:1px solid #e2e8f0; font-size:8px; }
    tfoot td { background:#f1f5f9; font-weight:700; padding:5px 4px; font-size:8px; }
    .footer { margin-top:16px; text-align:center; font-size:7px; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:6px; }
</style>
</head>
<body>

<div class="header">
    <div>
        <div class="company">EDEN GROUP</div>
        <div class="company-sub">Direction des Ressources Humaines</div>
        <div class="company-sub">Yaoundé, Cameroun</div>
        <div class="company-sub" style="font-weight:600;margin-top:4px;">N° CONTRIBUABLE : __________</div>
    </div>
    <div>
        <div class="title">DÉCLARATION INDIVIDUELLE DE PAIE</div>
        <div class="title-sub">DIPE — {{ $declaration->reference }}</div>
        <div class="title-sub">Période : {{ $declaration->mois_label }}</div>
    </div>
</div>

<div class="section">
    <div class="section-title">📋 Informations de la déclaration</div>
    <table style="width:100%;border-collapse:collapse;font-size:8px;">
        <tr>
            <td style="padding:4px;border:1px solid #e2e8f0;width:50%;">
                <strong>Référence :</strong> {{ $declaration->reference }}
            </td>
            <td style="padding:4px;border:1px solid #e2e8f0;width:50%;">
                <strong>Date déclaration :</strong> {{ $declaration->date_declaration?->format('d/m/Y') ?? '-' }}
            </td>
        </tr>
        <tr>
            <td style="padding:4px;border:1px solid #e2e8f0;">
                <strong>Période :</strong> {{ $declaration->mois_label }}
            </td>
            <td style="padding:4px;border:1px solid #e2e8f0;">
                <strong>Date échéance :</strong> {{ $declaration->date_echeance?->format('d/m/Y') ?? '-' }}
            </td>
        </tr>
        <tr>
            <td style="padding:4px;border:1px solid #e2e8f0;">
                <strong>Nombre d'employés :</strong> {{ $declaration->lignes->count() }}
            </td>
            <td style="padding:4px;border:1px solid #e2e8f0;">
                <strong>Statut :</strong> {{ $declaration->statut_label }}
            </td>
        </tr>
    </table>
</div>

{{-- TABLEAU DES EMPLOYÉS --}}
<div class="section">
    <div class="section-title">👥 Liste des employés déclarés</div>
    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Matricule</th>
                <th>Nom & Prénom</th>
                <th>N° CNPS</th>
                <th style="text-align:right;">Salaire soumis</th>
                <th style="text-align:right;">Cotisation salariale</th>
                <th style="text-align:right;">Cotisation patronale</th>
                <th style="text-align:right;">Total CNPS</th>
            </tr>
        </thead>
        <tbody>
        @foreach($declaration->lignes as $index => $ligne)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td style="font-weight:600;">{{ $ligne->matricule }}</td>
                <td>{{ $ligne->nom }} {{ $ligne->prenom }}</td>
                <td>{{ $ligne->numero_cnps ?? '-' }}</td>
                <td style="text-align:right;">{{ number_format($ligne->salaire_soumis, 0, ',', ' ') }}</td>
                <td style="text-align:right;">{{ number_format($ligne->cotisation_salariale, 0, ',', ' ') }}</td>
                <td style="text-align:right;">{{ number_format($ligne->cotisation_patronale, 0, ',', ' ') }}</td>
                <td style="text-align:right;font-weight:700;">{{ number_format($ligne->total_cnps, 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align:right;font-weight:700;">TOTAUX</td>
                <td style="text-align:right;font-weight:700;">{{ number_format($declaration->total_salaire_soumis, 0, ',', ' ') }}</td>
                <td style="text-align:right;font-weight:700;">{{ number_format($declaration->total_cotisation_salariale, 0, ',', ' ') }}</td>
                <td style="text-align:right;font-weight:700;">{{ number_format($declaration->total_cotisation_patronale, 0, ',', ' ') }}</td>
                <td style="text-align:right;font-size:11px;font-weight:800;color:#1d4ed8;">
                    {{ number_format($declaration->total_cnps, 0, ',', ' ') }}
                </td>
            </tr>
            <tr style="background:#f0fdf4;">
                <td colspan="7" style="text-align:right;font-weight:700;font-size:10px;color:#15803d;">
                    TOTAL CNPS (Salariale + Patronale)
                </td>
                <td style="text-align:right;font-size:13px;font-weight:900;color:#15803d;">
                    {{ number_format($declaration->total_cnps, 0, ',', ' ') }}
                </td>
            </tr>
        </tfoot>
    </table>
</div>

{{-- RÉCAPITULATIF DES TAUX --}}
<div class="section">
    <div class="section-title">📊 Récapitulatif des taux CNPS</div>
    <table style="width:100%;border-collapse:collapse;font-size:8px;">
        <tr>
            <td style="padding:4px;border:1px solid #e2e8f0;width:33%;text-align:center;">
                <strong>Cotisation salariale</strong><br>
                2.52%
            </td>
            <td style="padding:4px;border:1px solid #e2e8f0;width:33%;text-align:center;">
                <strong>Cotisation patronale</strong><br>
                4.20%
            </td>
            <td style="padding:4px;border:1px solid #e2e8f0;width:33%;text-align:center;background:#dcfce7;">
                <strong>TOTAL</strong><br>
                <span style="font-size:11px;font-weight:800;color:#15803d;">6.72%</span>
            </td>
        </tr>
    </table>
</div>

<div class="footer">
    Document généré le {{ now()->format('d/m/Y à H:i') }} — EDEN GROUP · Direction des Ressources Humaines
    <br>
    Déclaration conforme aux règles CNPS du Cameroun
</div>

</body>
</html>