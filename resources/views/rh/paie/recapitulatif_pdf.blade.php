<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:8.5px; color:#1e293b; padding:14px; }
    h2 { font-size:14px; color:#1e3a5f; margin-bottom:4px; }
    .sub { font-size:9px; color:#64748b; margin-bottom:12px; }
    table { width:100%; border-collapse:collapse; margin-bottom:16px; }
    thead tr { background:#1e3a5f; color:white; }
    thead th { padding:5px 4px; font-size:7.5px; text-align:left; white-space:nowrap; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody td { padding:4px; border-bottom:1px solid #e2e8f0; }
    tfoot td { background:#1e3a5f; color:white; font-weight:700; padding:5px 4px; font-size:8px; }
    .section-title { font-size:10px; font-weight:700; color:#1e3a5f; background:#f1f5f9; padding:4px 8px; border-radius:4px; margin-bottom:6px; margin-top:12px; }
    .kpi-row { display:flex; gap:8px; margin-bottom:12px; }
    .kpi { background:#f8fafc; border-radius:6px; padding:6px 10px; border-top:2px solid #1d4ed8; text-align:center; flex:1; }
    .kpi .v { font-size:11px; font-weight:800; color:#1d4ed8; }
    .kpi .l { font-size:7px; color:#64748b; text-transform:uppercase; font-weight:600; }
    .text-end { text-align:right; }
    .text-center { text-align:center; }
    .fw-bold { font-weight:700; }
    .red { color:#dc2626; }
    .green { color:#16a34a; }
    .purple { color:#7c3aed; }
    .blue { color:#1d4ed8; }
    .orange { color:#f59e0b; }
    .badge { display:inline-block; padding:1px 6px; border-radius:8px; font-size:7px; font-weight:700; }
    .badge-v1 { background:#dbeafe; color:#1d4ed8; }
    .badge-v2 { background:#f3e8ff; color:#7c3aed; }
    .badge-paye { background:#dcfce7; color:#15803d; }
    .badge-valide { background:#fef3c7; color:#92400e; }
    .badge-brouillon { background:#f1f5f9; color:#475569; }
    .cnps-synth { background:#fef9c3; border:1px solid #fcd34d; border-radius:4px; padding:8px 12px; margin-top:10px; text-align:center; font-size:8px; color:#92400e; }
    .cnps-synth strong { font-size:10px; color:#1d4ed8; }
</style>
</head>
<body>

<h2>📊 Récapitulatif de paie</h2>
<div class="sub">
    Période : {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}
    @if($vague) — Vague : {{ $vague }} @endif
    — Généré le {{ now()->format('d/m/Y à H:i') }}
</div>

{{-- KPIs --}}
<div class="kpi-row">
    <div class="kpi" style="border-color:#1d4ed8;">
        <div class="v">{{ $bulletins->count() }}</div>
        <div class="l">Bulletins</div>
    </div>
    <div class="kpi" style="border-color:#7c3aed;">
        <div class="v" style="color:#7c3aed;">{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</div>
        <div class="l">Masse brute</div>
    </div>
    <div class="kpi" style="border-color:#16a34a;">
        <div class="v" style="color:#16a34a;">{{ number_format($bulletins->sum('net_a_payer'), 0, ',', ' ') }}</div>
        <div class="l">Masse nette</div>
    </div>
    {{-- KPI CNPS COMMENTÉ --}}
    {{-- <div class="kpi" style="border-color:#f59e0b;">
        <div class="v" style="color:#f59e0b;font-size:10px;">{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</div>
        <div class="l">Total CNPS</div>
    </div> --}}
    <div class="kpi" style="border-color:#dc2626;">
        <div class="v" style="color:#dc2626;font-size:10px;">{{ number_format($bulletins->sum('montant_sanction') + $bulletins->sum('acompte'), 0, ',', ' ') }}</div>
        <div class="l">Déductions</div>
    </div>
</div>

{{-- PAR DIRECTION --}}
<div class="section-title">🏢 Par direction</div>
<table>
    <thead>
        <tr>
            <th>Direction</th>
            <th>Nb</th>
            <th class="text-end">Brut</th>
            <th class="text-end">HS</th>
            {{-- COLONNES CNPS COMMENTÉES --}}
            {{-- <th class="text-end">CNPS Sal.</th> --}}
            {{-- <th class="text-end">CNPS Pat.</th> --}}
            {{-- <th class="text-end">Total CNPS</th> --}}
            <th class="text-end">Sanctions</th>
            <th class="text-end">Acomptes</th>
            <th class="text-end">Net</th>
        </tr>
    </thead>
    <tbody>
    @foreach($parDirection as $dir => $data)
        <tr>
            <td style="font-weight:600;">{{ $dir ?? 'Non défini' }}</td>
            <td class="text-center">{{ $data['nb'] }}</td>
            <td class="text-end">{{ number_format($data['brut'], 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($data['hs'], 0, ',', ' ') }}</td>
            {{-- DONNÉES CNPS COMMENTÉES --}}
            {{-- <td class="text-end red">{{ number_format($data['cnps_salariale'] ?? 0, 0, ',', ' ') }}</td> --}}
            {{-- <td class="text-end purple">{{ number_format($data['cnps_patronale'] ?? 0, 0, ',', ' ') }}</td> --}}
            {{-- <td class="text-end blue fw-bold">{{ number_format($data['cnps'] ?? 0, 0, ',', ' ') }}</td> --}}
            <td class="text-end red">{{ number_format($data['sanction'], 0, ',', ' ') }}</td>
            <td class="text-end purple">{{ number_format($data['acomptes'] ?? 0, 0, ',', ' ') }}</td>
            <td class="text-end green fw-bold">{{ number_format($data['net'], 0, ',', ' ') }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAL</td>
            <td class="text-center">{{ $bulletins->count() }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('montant_heures_sup'), 0, ',', ' ') }}</td>
            {{-- TOTAUX CNPS COMMENTÉS --}}
            {{-- <td class="text-end red">{{ number_format($bulletins->sum('cnps_salariale'), 0, ',', ' ') }}</td> --}}
            {{-- <td class="text-end purple">{{ number_format($bulletins->sum('cnps_patronale'), 0, ',', ' ') }}</td> --}}
            {{-- <td class="text-end blue fw-bold">{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</td> --}}
            <td class="text-end">{{ number_format($bulletins->sum('montant_sanction'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('acompte'), 0, ',', ' ') }}</td>
            <td class="text-end green fw-bold">{{ number_format($bulletins->sum('net_a_payer'), 0, ',', ' ') }}</td>
        </tr>
    </tfoot>
</table>

{{-- DÉTAIL PAR EMPLOYÉ --}}
<div class="section-title">👤 Détail par employé</div>
<table>
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Nom</th>
            <th>Direction</th>
            <th>Vague</th>
            <th class="text-end">Brut</th>
            <th class="text-end">HS</th>
            {{-- COLONNES CNPS COMMENTÉES --}}
            {{-- <th class="text-end">CNPS Sal.</th> --}}
            {{-- <th class="text-end">CNPS Pat.</th> --}}
            {{-- <th class="text-end">Total CNPS</th> --}}
            <th class="text-end">Retards</th>
            <th class="text-end">Absences</th>
            <th class="text-end">Sanction</th>
            <th class="text-end">Acompte</th>
            <th class="text-end">Prêt</th>
            <th class="text-end">Net</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
    @foreach($bulletins->sortBy('employe.nom') as $b)
        <tr>
            <td style="color:#1d4ed8;font-weight:700;">{{ $b->employe?->matricule }}</td>
            <td style="font-weight:600;">{{ $b->employe?->nom }} {{ $b->employe?->prenom }}</td>
            <td>{{ $b->employe?->direction?->nom ?? '-' }}</td>
            <td>
                <span class="badge {{ $b->vague === 'VAGUE 1' ? 'badge-v1' : 'badge-v2' }}">{{ $b->vague }}</span>
            </td>
            <td class="text-end">{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
            <td class="text-end green">{{ $b->montant_heures_sup > 0 ? number_format($b->montant_heures_sup, 0, ',', ' ') : '-' }}</td>
            {{-- DONNÉES CNPS COMMENTÉES --}}
            {{-- <td class="text-end red">{{ $b->cnps_salariale > 0 ? number_format($b->cnps_salariale, 0, ',', ' ') : '-' }}</td> --}}
            {{-- <td class="text-end purple">{{ $b->cnps_patronale > 0 ? number_format($b->cnps_patronale, 0, ',', ' ') : '-' }}</td> --}}
            {{-- <td class="text-end blue fw-bold">{{ $b->cnps > 0 ? number_format($b->cnps, 0, ',', ' ') : '-' }}</td> --}}
            <td class="text-end orange">{{ $b->montant_retard > 0 ? number_format($b->montant_retard, 0, ',', ' ') : '-' }}</td>
            <td class="text-end orange">{{ $b->montant_absence > 0 ? number_format($b->montant_absence, 0, ',', ' ') : '-' }}</td>
            <td class="text-end red">{{ $b->montant_sanction > 0 ? number_format($b->montant_sanction, 0, ',', ' ') : '-' }}</td>
            <td class="text-end purple">{{ $b->acompte > 0 ? number_format($b->acompte, 0, ',', ' ') : '-' }}</td>
            <td class="text-end purple">{{ $b->pret > 0 ? number_format($b->pret, 0, ',', ' ') : '-' }}</td>
            <td class="text-end" style="font-weight:800;color:#16a34a;font-size:9px;">{{ number_format($b->net_a_payer, 0, ',', ' ') }}</td>
            <td>
                <span class="badge {{ $b->statut === 'payé' ? 'badge-paye' : ($b->statut === 'validé' ? 'badge-valide' : 'badge-brouillon') }}">
                    {{ $b->statut }}
                </span>
            </td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">TOTAUX</td>
            <td class="text-end">{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('montant_heures_sup'), 0, ',', ' ') }}</td>
            {{-- TOTAUX CNPS COMMENTÉS --}}
            {{-- <td class="text-end red">{{ number_format($bulletins->sum('cnps_salariale'), 0, ',', ' ') }}</td> --}}
            {{-- <td class="text-end purple">{{ number_format($bulletins->sum('cnps_patronale'), 0, ',', ' ') }}</td> --}}
            {{-- <td class="text-end blue fw-bold">{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</td> --}}
            <td class="text-end">{{ number_format($bulletins->sum('montant_retard'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('montant_absence'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('montant_sanction'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('acompte'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('pret'), 0, ',', ' ') }}</td>
            <td class="text-end" style="color:#16a34a;font-size:10px;font-weight:800;">{{ number_format($bulletins->sum('net_a_payer'), 0, ',', ' ') }}</td>
            <td>—</td>
        </tr>
    </tfoot>
</table>

{{-- SYNTHÈSE CNPS COMMENTÉE --}}
{{-- <div class="cnps-synth">
    <span style="font-weight:700;">📊 Synthèse CNPS :</span>
    Masse salariale brute <strong>{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</strong> FCFA × 6.72% = 
    <strong style="font-size:11px;color:#1d4ed8;">{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</strong> FCFA
    <br>
    <span style="font-size:7px;color:#94a3b8;">
        Détail : Salariale (2.52%) <span style="color:#dc2626;">{{ number_format($bulletins->sum('cnps_salariale'), 0, ',', ' ') }}</span> FCFA · 
        Patronale (4.20%) <span style="color:#7c3aed;">{{ number_format($bulletins->sum('cnps_patronale'), 0, ',', ' ') }}</span> FCFA
    </span>
</div> --}}

<div style="text-align:center;margin-top:14px;font-size:7px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:6px;">
    EDEN GROUP — Document généré le {{ now()->format('d/m/Y à H:i') }} — Confidentiel
</div>

</body>
</html>