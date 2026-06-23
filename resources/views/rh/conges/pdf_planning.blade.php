<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:DejaVu Sans, sans-serif; font-size:9pt; color:#1e293b; padding:20px; }
    .entete { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; border-bottom:3px solid #1d4ed8; padding-bottom:12px; }
    .titre-doc { font-size:15pt; font-weight:bold; color:#1e3a5f; }
    .sous-titre { font-size:9pt; color:#64748b; }
    table { width:100%; border-collapse:collapse; margin-top:10px; }
    thead tr { background:#1e3a5f; color:white; }
    thead th { padding:8px 6px; text-align:left; font-size:8pt; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody tr:hover { background:#eff6ff; }
    tbody td { padding:7px 6px; border-bottom:1px solid #e2e8f0; font-size:8.5pt; }
    .badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:7.5pt; font-weight:bold; }
    .planifie { background:#fef3c7; color:#92400e; }
    .en_cours { background:#dbeafe; color:#1d4ed8; }
    .termine  { background:#dcfce7; color:#15803d; }
    .annule   { background:#fee2e2; color:#b91c1c; }

    /* Mini calendrier horizontal */
    .mini-cal { display:flex; gap:1px; }
    .mc-j { width:14px; height:14px; border-radius:2px; background:#bfdbfe; font-size:6pt; color:#1d4ed8; text-align:center; line-height:14px; }
    .mc-j.w { background:#e2e8f0; color:#94a3b8; }

    .pied { margin-top:20px; text-align:center; font-size:7.5pt; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:8px; }
    .resume { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:10px 14px; margin-bottom:14px; display:flex; gap:24px; }
    .res-item .v { font-size:14pt; font-weight:bold; }
    .res-item .l { font-size:7.5pt; color:#64748b; }
</style>
</head>
<body>

<div class="entete">
    <div>
        <div class="titre-doc">🏖️ Planning des Congés</div>
        <div class="sous-titre">Eden Group — Direction des Ressources Humaines</div>
        @if($mois)
        <div class="sous-titre">Période : {{ \Carbon\Carbon::parse($mois)->format('F Y') }}</div>
        @endif
    </div>
    <div style="text-align:right;font-size:8pt;color:#64748b;">
        Généré le {{ now()->format('d/m/Y à H:i') }}<br>
        {{ $conges->count() }} congé(s)
    </div>
</div>

{{-- Résumé --}}
<div class="resume">
    <div class="res-item">
        <div class="v">{{ $conges->count() }}</div>
        <div class="l">Total</div>
    </div>
    <div class="res-item">
        <div class="v" style="color:#f59e0b;">{{ $conges->where('statut','planifie')->count() }}</div>
        <div class="l">Planifiés</div>
    </div>
    <div class="res-item">
        <div class="v" style="color:#1d4ed8;">{{ $conges->where('statut','en_cours')->count() }}</div>
        <div class="l">En cours</div>
    </div>
    <div class="res-item">
        <div class="v" style="color:#16a34a;">{{ $conges->where('statut','termine')->count() }}</div>
        <div class="l">Terminés</div>
    </div>
    <div class="res-item">
        <div class="v" style="color:#dc2626;">{{ $conges->where('statut','annule')->count() }}</div>
        <div class="l">Annulés</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Employé</th>
            <th>Matricule</th>
            <th>Direction</th>
            <th>Début</th>
            <th>Fin</th>
            <th>Jours</th>
            <th>Motif</th>
            <th>Statut</th>
            <th>Calendrier</th>
        </tr>
    </thead>
    <tbody>
    @forelse($conges as $c)
    <tr>
        <td><strong>{{ $c->employe?->nom }} {{ $c->employe?->prenom }}</strong></td>
        <td style="color:#64748b;">{{ $c->employe?->matricule }}</td>
        <td>{{ $c->employe?->direction?->nom ?? '-' }}</td>
        <td><strong>{{ $c->date_debut->format('d/m/Y') }}</strong></td>
        <td><strong>{{ $c->date_fin->format('d/m/Y') }}</strong></td>
        <td style="text-align:center;font-weight:bold;">{{ $c->nb_jours }}</td>
        <td style="color:#64748b;">{{ $c->motif ?? '-' }}</td>
        <td>
            <span class="badge {{ $c->statut }}">{{ $c->statut_label }}</span>
        </td>
        <td>
            {{-- Mini calendrier 15 cases --}}
            <div class="mini-cal">
                @for($i = 0; $i < 15; $i++)
                    @php $j = $c->date_debut->copy()->addDays($i); @endphp
                    <div class="mc-j {{ $j->isWeekend() ? 'w' : '' }}" title="{{ $j->format('d/m') }}">
                        {{ $j->format('d') }}
                    </div>
                @endfor
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:20px;">Aucun congé</td></tr>
    @endforelse
    </tbody>
</table>

<div class="pied">
    Eden Group · Direction des Ressources Humaines · Planning généré automatiquement
</div>
</body>
</html>