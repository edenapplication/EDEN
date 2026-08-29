<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:8.5px; color:#1e293b; padding:14px; }
    h2 { font-size:14px; color:#1e3a5f; margin-bottom:4px; }
    .sub { font-size:9px; color:#64748b; margin-bottom:12px; }
    table { width:100%; border-collapse:collapse; margin-top:6px; }
    thead tr { background:#1e3a5f; color:white; }
    thead th { padding:5px 4px; font-size:7.5px; text-align:left; white-space:nowrap; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody td { padding:4px; border-bottom:1px solid #e2e8f0; font-size:8px; }
    tfoot td { background:#1e3a5f; color:white; font-weight:700; padding:5px 4px; font-size:8px; }
    .badge { display:inline-block; padding:1px 6px; border-radius:8px; font-size:7px; font-weight:700; }
</style>
</head>
<body>

<h2>🩺 Liste des visites médicales</h2>
<div class="sub">Généré le {{ now()->format('d/m/Y à H:i') }}</div>

<table>
    <thead>
        <tr>
            <th>N°</th>
            <th>Employé</th>
            <th>Matricule</th>
            <th>Type</th>
            <th>Date</th>
            <th>Aptitude</th>
            <th>Prochaine visite</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
    @forelse($visites as $index => $v)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td><strong>{{ $v->employe?->nom }} {{ $v->employe?->prenom }}</strong></td>
            <td>{{ $v->employe?->matricule }}</td>
            <td>{{ $v->type_label }}</td>
            <td>{{ $v->date_visite->format('d/m/Y') }}</td>
            <td>
                <span class="badge" style="background:{{ $v->aptitude === 'apte' ? '#dcfce7' : ($v->aptitude === 'apte_avec_restriction' ? '#fef3c7' : '#fee2e2') }};color:{{ $v->aptitude === 'apte' ? '#15803d' : ($v->aptitude === 'apte_avec_restriction' ? '#92400e' : '#b91c1c') }};">
                    {{ $v->aptitude_label }}
                </span>
            </td>
            <td>{{ $v->prochaine_visite?->format('d/m/Y') ?? '-' }}</td>
            <td>
                <span class="badge" style="background:{{ $v->statut_color }};color:#1e293b;">
                    {{ $v->statut_label }}
                </span>
            </td>
        </tr>
    @empty
        <tr><td colspan="8" style="text-align:center;color:#94a3b8;padding:20px;">Aucune visite enregistrée</td></tr>
    @endforelse
    </tbody>
    <tfoot>
        <tr>
            <td colspan="8">TOTAL : {{ $visites->count() }} visite(s)</td>
        </tr>
    </tfoot>
</table>

</body>
</html>