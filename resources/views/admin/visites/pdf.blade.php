<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:DejaVu Sans,sans-serif; font-size:8px; color:#1e293b; }

.header {
    background:#1e3a5f; color:white;
    padding:14px 18px; margin-bottom:14px;
    border-left:6px solid #dc2626;
}
.header h1 { font-size:16px; font-weight:bold; margin-bottom:3px; }
.header p  { font-size:8px; opacity:0.75; }

table { width:100%; border-collapse:collapse; }
thead tr { background:#1e3a5f; color:white; }
thead th { padding:5px 4px; font-size:7.5px; font-weight:bold; text-align:left; }
tbody tr:nth-child(even) { background:#f8fafc; }
tbody td { padding:4px; border-bottom:1px solid #e2e8f0; font-size:7.5px; }
.footer { text-align:right; font-size:6px; color:#94a3b8; margin-top:8px; }

.bt-client       { background:#dbeafe; color:#1d4ed8; padding:1px 5px; border-radius:3px; font-size:7px; }
.bt-proprietaire { background:#dcfce7; color:#15803d; padding:1px 5px; border-radius:3px; font-size:7px; }
.bt-autre        { background:#f1f5f9; color:#475569; padding:1px 5px; border-radius:3px; font-size:7px; }
</style>
</head>
<body>

<div class="header">
    <h1>🚶 Registre des visites — EDEN GROUP</h1>
    <p>Généré le {{ now()->format('d/m/Y à H:i') }} — {{ $visites->count() }} visite(s)</p>
</div>

<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Date</th>
            <th>Nom</th>
            <th>Numéro</th>
            <th>Type</th>
            <th>Arrivée</th>
            <th>Départ</th>
            <th>Grand Site</th>
            <th>Site</th>
            <th>Note</th>
        </tr>
    </thead>
    <tbody>
    @foreach($visites as $i => $v)
        <tr>
            <td>{{ $i+1 }}</td>
            <td>{{ $v->date_visite }}</td>
            <td><strong>{{ $v->visiteur?->nom ?? '-' }}</strong></td>
            <td>{{ $v->visiteur?->numero ?? '-' }}</td>
            <td>
                <span class="bt-{{ $v->type_personne }}">{{ ucfirst($v->type_personne) }}</span>
            </td>
            <td>{{ $v->heure_arrivee ?? '-' }}</td>
            <td>{{ $v->heure_depart  ?? '-' }}</td>
            <td>{{ $v->grandSite?->nom ?? '-' }}</td>
            <td>{{ $v->site?->name    ?? '-' }}</td>
            <td>{{ $v->note           ?? '' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="footer">EDEN GROUP — {{ now()->format('d/m/Y à H:i') }}</div>
</body>
</html>