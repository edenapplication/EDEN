<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:9px; color:#1e293b; padding:16px; }
    h2 { font-size:14px; color:#1e3a5f; margin-bottom:4px; }
    .sub { font-size:10px; color:#64748b; margin-bottom:14px; }
    table { width:100%; border-collapse:collapse; }
    thead tr { background:#1e3a5f; color:white; }
    thead th { padding:6px 5px; font-size:8.5px; text-align:left; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody td { padding:5px; border-bottom:1px solid #e2e8f0; vertical-align:top; }
    tfoot td { background:#1e3a5f; color:white; font-weight:700; padding:6px 5px; }
    .badge { display:inline-block; padding:1px 6px; border-radius:8px; font-size:7px; font-weight:700; }
    .note-cell { max-width:150px; word-break:break-word; white-space:pre-wrap; }
</style>
</head>
<body>
<h2>📋 Liste des absences & permissions</h2>
<div class="sub">Généré le {{ now()->format('d/m/Y à H:i') }}</div>
<table>
    <thead>
        <tr>
            <th>Réf.</th>
            <th>Employé</th>
            <th>Direction</th>
            <th>Type</th>
            <th>Début</th>
            <th>Fin</th>
            <th>Jours</th>
            <th>Statut</th>
            <th>Motif</th>
            <th>Note</th>
        </tr>
    </thead>
    <tbody>
    @foreach($absences as $a)
        <tr>
            <td style="color:#1d4ed8;font-weight:700;">{{ $a->reference }}</td>
            <td style="font-weight:600;">{{ $a->employe?->nom }} {{ $a->employe?->prenom }}</td>
            <td>{{ $a->employe?->direction?->nom ?? '-' }}</td>
            <td>{{ $a->type_absence }}</td>
            <td>{{ $a->date_debut?->format('d/m/Y') }}</td>
            <td>{{ $a->date_fin?->format('d/m/Y') }}</td>
            <td style="font-weight:700;text-align:center;">{{ $a->nombre_jours }}</td>
            <td>
                <span class="badge" style="background:{{ $a->statut==='approuvé'?'#dcfce7':($a->statut==='refusé'?'#fee2e2':'#fef3c7')}};color:{{ $a->statut==='approuvé'?'#15803d':($a->statut==='refusé'?'#b91c1c':'#92400e')}};">
                    {{ $a->statut }}
                </span>
            </td>
            <td>{{ $a->motif ?? '-' }}</td>
            <td class="note-cell">{{ $a->note ?? '-' }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6">TOTAL</td>
            <td>{{ $absences->sum('nombre_jours') }} jours</td>
            <td colspan="3">{{ $absences->count() }} demandes</td>
        </tr>
    </tfoot>
</table>
</body>
</html>