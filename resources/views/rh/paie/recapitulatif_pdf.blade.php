<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:9px; color:#1e293b; padding:16px; }
    h2 { font-size:14px; color:#1e3a5f; margin-bottom:4px; }
    .sub { font-size:10px; color:#64748b; margin-bottom:16px; }
    table { width:100%; border-collapse:collapse; margin-bottom:20px; }
    thead tr { background:#1e3a5f; color:white; }
    thead th { padding:6px 5px; font-size:8.5px; text-align:left; white-space:nowrap; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody td { padding:5px; border-bottom:1px solid #e2e8f0; }
    tfoot td { background:#1e3a5f; color:white; font-weight:700; padding:6px 5px; }
    .section-title { font-size:11px; font-weight:700; color:#1e3a5f; background:#f1f5f9; padding:5px 8px; border-radius:4px; margin-bottom:8px; margin-top:16px; }
    .kpi-row { display:flex; gap:12px; margin-bottom:16px; }
    .kpi { background:#f8fafc; border-radius:8px; padding:8px 12px; border-top:2px solid #1d4ed8; text-align:center; flex:1; }
    .kpi .v { font-size:13px; font-weight:800; color:#1d4ed8; }
    .kpi .l { font-size:7.5px; color:#64748b; text-transform:uppercase; font-weight:600; }
</style>
</head>
<body>

<h2>📊 Récapitulatif de paie — {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</h2>
<div class="sub">Généré le {{ now()->format('d/m/Y à H:i') }}</div>

<div class="kpi-row">
    <div class="kpi" style="border-color:#1d4ed8;">
        <div class="v">{{ $bulletins->count() }}</div>
        <div class="l">Bulletins</div>
    </div>
    <div class="kpi" style="border-color:#7c3aed;">
        <div class="v" style="color:#7c3aed;">{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</div>
        <div class="l">Masse brute (FCFA)</div>
    </div>
    <div class="kpi" style="border-color:#16a34a;">
        <div class="v" style="color:#16a34a;">{{ number_format($bulletins->sum('net_a_payer'), 0, ',', ' ') }}</div>
        <div class="l">Masse nette (FCFA)</div>
    </div>
    <div class="kpi" style="border-color:#dc2626;">
        <div class="v" style="color:#dc2626;">{{ number_format($bulletins->sum('montant_sanction') + $bulletins->sum('acompte'), 0, ',', ' ') }}</div>
        <div class="l">Déductions (FCFA)</div>
    </div>
</div>

<div class="section-title">🏢 Par direction</div>
<table>
    <thead><tr>
        <th>Direction</th><th>Nb employés</th><th>Masse brute</th>
        <th>Heures sup.</th><th>Sanctions</th><th>Acomptes</th><th>Masse nette</th>
    </tr></thead>
    <tbody>
    @foreach($parDirection as $dir => $data)
        <tr>
            <td style="font-weight:600;">{{ $dir ?? 'Non défini' }}</td>
            <td>{{ $data['nb'] }}</td>
            <td>{{ number_format($data['brut'], 0, ',', ' ') }}</td>
            <td>{{ number_format($data['hs'], 0, ',', ' ') }}</td>
            <td style="color:#dc2626;">{{ number_format($data['sanction'], 0, ',', ' ') }}</td>
            <td style="color:#7c3aed;">{{ number_format($data['acomptes'], 0, ',', ' ') }}</td>
            <td style="font-weight:700;color:#16a34a;">{{ number_format($data['net'], 0, ',', ' ') }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot><tr>
        <td>TOTAL</td>
        <td>{{ $bulletins->count() }}</td>
        <td>{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('montant_heures_sup'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('montant_sanction'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('acompte'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('net_a_payer'), 0, ',', ' ') }}</td>
    </tr></tfoot>
</table>

<div class="section-title">👤 Détail par employé</div>
<table>
    <thead><tr>
        <th>Matricule</th><th>Nom</th><th>Direction</th><th>Vague</th>
        <th>Brut</th><th>HS</th><th>Retards</th><th>Absences</th>
        <th>Sanction</th><th>Acompte</th><th>Prêt</th><th>CNPS</th><th>Net</th><th>Statut</th>
    </tr></thead>
    <tbody>
    @foreach($bulletins->sortBy('employe.nom') as $b)
        <tr>
            <td style="color:#1d4ed8;font-weight:700;">{{ $b->employe?->matricule }}</td>
            <td style="font-weight:600;">{{ $b->employe?->nom }} {{ $b->employe?->prenom }}</td>
            <td>{{ $b->employe?->direction?->nom ?? '-' }}</td>
            <td>{{ $b->vague }}</td>
            <td>{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
            <td style="color:#16a34a;">{{ number_format($b->montant_heures_sup, 0, ',', ' ') }}</td>
            <td style="color:#f59e0b;">{{ number_format($b->montant_retard, 0, ',', ' ') }}</td>
            <td style="color:#f59e0b;">{{ number_format($b->montant_absence, 0, ',', ' ') }}</td>
            <td style="color:#dc2626;">{{ number_format($b->montant_sanction, 0, ',', ' ') }}</td>
            <td style="color:#7c3aed;">{{ number_format($b->acompte, 0, ',', ' ') }}</td>
            <td>{{ number_format($b->pret, 0, ',', ' ') }}</td>
            <td>{{ number_format($b->cnps, 0, ',', ' ') }}</td>
            <td style="font-weight:800;color:#16a34a;">{{ number_format($b->net_a_payer, 0, ',', ' ') }}</td>
            <td>{{ $b->statut }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot><tr>
        <td colspan="4">TOTAUX</td>
        <td>{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('montant_heures_sup'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('montant_retard'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('montant_absence'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('montant_sanction'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('acompte'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('pret'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</td>
        <td>{{ number_format($bulletins->sum('net_a_payer'), 0, ',', ' ') }}</td>
        <td>—</td>
    </tr></tfoot>
</table>

</body>
</html>