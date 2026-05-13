<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:9px; color:#1e293b; padding:16px; }
    h2 { font-size:14px; color:#1e3a5f; margin-bottom:4px; }
    .sub { font-size:10px; color:#64748b; margin-bottom:14px; }
    table { width:100%; border-collapse:collapse; margin-top:8px; }
    thead tr { background:#1e3a5f; color:white; }
    thead th { padding:6px 5px; font-size:8.5px; text-align:left; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody td { padding:5px; border-bottom:1px solid #e2e8f0; }
    tfoot td { background:#1e3a5f; color:white; font-weight:700; padding:6px 5px; }
    .stats { display:flex; gap:16px; margin-bottom:16px; flex-wrap:wrap; }
    .stat-box { background:#fee2e2; border-radius:8px; padding:8px 14px; text-align:center; min-width:100px; }
    .stat-box .val { font-size:18px; font-weight:900; color:#dc2626; }
    .stat-box .lbl { font-size:8px; color:#b91c1c; text-transform:uppercase; font-weight:700; }
</style>
</head>
<body>

<h2>⏰ Liste des retards</h2>
<div class="sub">Généré le {{ now()->format('d/m/Y à H:i') }}</div>

<div class="stats">
    <div class="stat-box">
        <div class="val">{{ $retards->count() }}</div>
        <div class="lbl">Total retards</div>
    </div>
    <div class="stat-box" style="background:#fef9c3;">
        <div class="val" style="color:#92400e;">{{ $retards->sum('duree_min') ?? 0 }} min</div>
        <div class="lbl" style="color:#92400e;">Durée totale</div>
    </div>
    @foreach($parDirection as $dir => $data)
        <div class="stat-box" style="background:#f1f5f9;">
            <div class="val" style="color:#1e3a5f;">{{ $data['nb'] }}</div>
            <div class="lbl" style="color:#64748b;">{{ $dir ?? 'N/A' }}</div>
        </div>
    @endforeach
</div>

<table>
    <thead>
        <tr>
            <th>Employé</th>
            <th>Matricule</th>
            <th>Direction</th>
            <th>Service</th>
            <th>Date</th>
            <th>Durée (min)</th>
            <th>Motif</th>
        </tr>
    </thead>
    <tbody>
    @foreach($retards as $r)
        <tr>
            <td style="font-weight:600;">{{ $r->employe?->nom }} {{ $r->employe?->prenom }}</td>
            <td style="color:#1d4ed8;">{{ $r->employe?->matricule }}</td>
            <td>{{ $r->employe?->direction?->nom ?? '-' }}</td>
            <td>{{ $r->employe?->service?->nom ?? '-' }}</td>
            <td>{{ $r->date }}</td>
            <td style="text-align:center;font-weight:700;color:#dc2626;">{{ $r->duree_min ?? '-' }}</td>
            <td>{{ $r->motif ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5">TOTAL — {{ $retards->count() }} retard(s)</td>
            <td>{{ $retards->sum('duree_min') ?? 0 }} min</td>
            <td>—</td>
        </tr>
    </tfoot>
</table>

</body>
</html>