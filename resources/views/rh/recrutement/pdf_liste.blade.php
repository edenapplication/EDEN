<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Liste des candidats</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; padding: 20px; }
        h1 { color: #1e3a5f; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background: #1e3a5f; color: white; padding: 6px; text-align: left; }
        td { padding: 5px; border-bottom: 1px solid #e2e8f0; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 8px; font-weight: bold; }
    </style>
</head>
<body>
    <h1>📋 Liste des candidats</h1>
    <p>Généré le {{ now()->format('d/m/Y à H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Nom & Prénom</th>
                <th>Email</th>
                <th>Poste</th>
                <th>Date</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @forelse($candidats as $index => $c)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $c->nom }} {{ $c->prenom }}</td>
                <td>{{ $c->email ?? '-' }}</td>
                <td>{{ $c->poste_demande ?? '-' }}</td>
                <td>{{ $c->date_candidature->format('d/m/Y') }}</td>
                <td>
                    <span class="badge" style="background:{{ $c->statut_color }};color:{{ $c->statut_text_color }};">
                        {{ $c->statut_label }}
                    </span>
                </td>
            </tr>
            @empty
            <tr><td colspan="6">Aucun candidat</td></tr>
            @endforelse
        </tbody>
    </table>

    <p style="margin-top:20px;font-size:8px;color:#94a3b8;">Total : {{ $candidats->count() }} candidat(s)</p>
</body>
</html>