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
    thead th { padding:6px 5px; font-size:8.5px; text-align:left; white-space:nowrap; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody td { padding:5px; border-bottom:1px solid #e2e8f0; vertical-align:middle; }
    tfoot td { background:#1e3a5f; color:white; font-weight:700; padding:6px 5px; }
    .badge { display:inline-block; padding:1px 6px; border-radius:8px; font-size:7.5px; font-weight:700; }
    .badge-v1 { background:#dbeafe; color:#1d4ed8; }
    .badge-v2 { background:#f3e8ff; color:#7c3aed; }
    .badge-paye { background:#dcfce7; color:#15803d; }
    .badge-valide { background:#fef3c7; color:#92400e; }
    .badge-brouillon { background:#f1f5f9; color:#475569; }
    .prog { height:5px; background:#e2e8f0; border-radius:3px; display:inline-block; width:50px; vertical-align:middle; }
    .prog-fill { height:100%; border-radius:3px; }
    .red { color:#dc2626; }
    .green { color:#16a34a; }
    .purple { color:#7c3aed; }
</style>
</head>
<body>

<h2>📋 Liste des bulletins de paie</h2>
<div class="sub">Période : {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }} — Généré le {{ now()->format('d/m/Y à H:i') }}</div>

<table>
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Nom</th>
            <th>Direction</th>
            <th>Vague</th>
            <th>Brut (FCFA)</th>
            <th>HS</th>
            <th>Retards</th>
            <th>Absences</th>
            <th>Sanction</th>
            <th>Acompte</th>
            <th>Prêt mensuel</th>
            <th>CNPS</th>
            <th>Net (FCFA)</th>
            <th>Prêt restant</th>
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
            <td>{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
            <td class="green">{{ $b->montant_heures_sup > 0 ? number_format($b->montant_heures_sup, 0, ',', ' ') : '-' }}</td>
            <td class="red">{{ $b->montant_retard > 0 ? number_format($b->montant_retard, 0, ',', ' ') : '-' }}</td>
            <td class="red">{{ $b->montant_absence > 0 ? number_format($b->montant_absence, 0, ',', ' ') : '-' }}</td>
            <td class="red">{{ $b->montant_sanction > 0 ? number_format($b->montant_sanction, 0, ',', ' ') : '-' }}</td>
            <td class="purple">{{ $b->acompte > 0 ? number_format($b->acompte, 0, ',', ' ') : '-' }}</td>
            <td class="purple">{{ $b->pret > 0 ? number_format($b->pret, 0, ',', ' ') : '-' }}</td>
            <td>{{ $b->cnps > 0 ? number_format($b->cnps, 0, ',', ' ') : '-' }}</td>
            <td style="font-weight:800;color:#16a34a;">{{ number_format($b->net_a_payer, 0, ',', ' ') }}</td>
            <td class="red" style="font-weight:700;">
                {{ $b->pret_restant > 0 ? number_format($b->pret_restant, 0, ',', ' ') : '-' }}
            </td>
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
            <td colspan="4">TOTAUX — {{ $bulletins->count() }} bulletins</td>
            <td>{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</td>
            <td>{{ number_format($bulletins->sum('montant_heures_sup'), 0, ',', ' ') }}</td>
            <td>{{ number_format($bulletins->sum('montant_retard'), 0, ',', ' ') }}</td>
            <td>{{ number_format($bulletins->sum('montant_absence'), 0, ',', ' ') }}</td>
            <td>{{ number_format($bulletins->sum('montant_sanction'), 0, ',', ' ') }}</td>
            <td>{{ number_format($bulletins->sum('acompte'), 0, ',', ' ') }}</td>
            <td>{{ number_format($bulletins->sum('pret'), 0, ',', ' ') }}</td>
            <td>{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</td>
            <td>{{ number_format($bulletins->sum('net_a_payer'), 0, ',', ' ') }}</td>
            <td>{{ number_format($bulletins->sum('pret_restant'), 0, ',', ' ') }}</td>
            <td>—</td>
        </tr>
    </tfoot>
</table>

</body>
</html>