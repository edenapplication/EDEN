<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:9px; color:#1e293b; padding:16px; }
    h2 { font-size:14px; color:#1e3a5f; margin-bottom:4px; }
    .sub { font-size:10px; color:#64748b; margin-bottom:16px; }
    table { width:100%; border-collapse:collapse; margin-bottom:16px; }
    thead tr { background:#1e3a5f; color:white; }
    thead th { padding:6px 5px; font-size:8.5px; text-align:left; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody td { padding:5px; border-bottom:1px solid #e2e8f0; }
    tfoot td { background:#1e3a5f; color:white; font-weight:700; padding:6px 5px; }
    .rouge { color:#dc2626; font-weight:700; }
    .vert  { color:#16a34a; font-weight:700; }
    .kpi { display:inline-block; background:#f8fafc; border-radius:6px; padding:8px 14px; text-align:center; margin-right:10px; border-top:2px solid #1e3a5f; }
    .kpi .v { font-size:16px; font-weight:800; color:#1e3a5f; }
    .kpi .l { font-size:8px; color:#64748b; text-transform:uppercase; font-weight:600; }
</style>
</head>
<body>

<h2>⏰ Liste des retards</h2>
<div class="sub">
    Mois : {{ \Carbon\Carbon::createFromFormat('Y-m', $mois)->translatedFormat('F Y') }}
    — Généré le {{ now()->format('d/m/Y à H:i') }}
    &nbsp;|&nbsp; Horaire référence : 08h00 – 18h00
</div>

<div style="margin-bottom:16px;">
    <div class="kpi">
        <div class="v">{{ $retards->count() }}</div>
        <div class="l">Total retards</div>
    </div>
    <div class="kpi">
        <div class="v rouge">{{ $retards->sum('minutes_retard') ?? 0 }} min</div>
        <div class="l">Minutes de retard</div>
    </div>
    <div class="kpi">
        <div class="v vert">{{ $retards->sum('minutes_sup') ?? 0 }} min</div>
        <div class="l">Heures supplémentaires</div>
    </div>
    <div class="kpi">
        <div class="v">{{ $retards->groupBy('employe_id')->count() }}</div>
        <div class="l">Employés concernés</div>
    </div>
</div>

{{-- STATS PAR DIRECTION --}}
@if($parDirection->count())
<div style="margin-bottom:14px;">
    <div style="font-weight:700;font-size:10px;color:#1e3a5f;margin-bottom:6px;">📊 Par direction</div>
    <table style="width:auto;">
        <thead><tr>
            <th style="padding:4px 10px;">Direction</th>
            <th style="padding:4px 10px;">Nb retards</th>
        </tr></thead>
        <tbody>
        @foreach($parDirection as $dir => $data)
            <tr><td style="padding:4px 10px;">{{ $dir ?? 'Non défini' }}</td>
                <td style="padding:4px 10px;color:#dc2626;font-weight:700;">{{ $data['nb'] }}</td></tr>
        @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- TABLEAU PRINCIPAL --}}
<table>
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Employé</th>
            <th>Direction</th>
            <th>Service</th>
            <th>Date</th>
            <th>H. Arrivée</th>
            <th>H. Départ</th>
            <th>Retard (min)</th>
            <th>H. Sup (min)</th>
            <th>Motif</th>
        </tr>
    </thead>
    <tbody>
    @foreach($retards as $r)
        <tr>
            <td style="color:#1d4ed8;font-weight:700;">{{ $r->employe?->matricule }}</td>
            <td style="font-weight:600;">{{ $r->employe?->nom }} {{ $r->employe?->prenom }}</td>
            <td>{{ $r->employe?->direction?->nom ?? '-' }}</td>
            <td>{{ $r->employe?->service?->nom ?? '-' }}</td>
            <td>{{ $r->date instanceof \Carbon\Carbon ? $r->date->format('d/m/Y') : $r->date }}</td>
            <td>{{ $r->heure_arrivee ?? '-' }}</td>
            <td>{{ $r->heure_depart  ?? '-' }}</td>
            <td class="rouge">{{ $r->minutes_retard > 0 ? $r->minutes_retard : '-' }}</td>
            <td class="vert">{{ $r->minutes_sup    > 0 ? $r->minutes_sup    : '-' }}</td>
            <td>{{ $r->motif ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7">TOTAL — {{ $retards->count() }} retard(s)</td>
            <td>{{ $retards->sum('minutes_retard') }} min</td>
            <td>{{ $retards->sum('minutes_sup') }} min</td>
            <td>—</td>
        </tr>
    </tfoot>
</table>

</body>
</html>