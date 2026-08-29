<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:DejaVu Sans, sans-serif; font-size:8.5pt; color:#1e293b; padding:16px; }
    .entete { display:flex; justify-content:space-between; align-items:center; margin-bottom:16px; border-bottom:3px solid #1d4ed8; padding-bottom:10px; }
    .titre-doc { font-size:14pt; font-weight:bold; color:#1e3a5f; }
    .sous-titre { font-size:8.5pt; color:#64748b; }
    table { width:100%; border-collapse:collapse; margin-top:8px; }
    thead tr { background:#1e3a5f; color:white; }
    thead th { padding:6px 5px; text-align:left; font-size:7.5pt; white-space:nowrap; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody td { padding:6px 5px; border-bottom:1px solid #e2e8f0; font-size:8pt; vertical-align:middle; }
    .badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:7pt; font-weight:bold; }
    .badge.planifie { background:#fef3c7; color:#92400e; }
    .badge.en_cours { background:#dbeafe; color:#1d4ed8; }
    .badge.termine  { background:#dcfce7; color:#15803d; }
    .badge.annule   { background:#f1f5f9; color:#475569; }

    /* Mini calendrier horizontal - affiche uniquement les jours ouvrés */
    .mini-cal { display:flex; gap:1px; flex-wrap:wrap; max-width:210px; }
    .mc-j { 
        width:13px; height:13px; border-radius:2px; 
        background:#bfdbfe; font-size:5.5pt; color:#1d4ed8; 
        text-align:center; line-height:13px; 
        font-weight:600;
    }
    .mc-j.dimanche { 
        background:#f1f5f9; 
        color:#cbd5e1; 
        border:1px solid #e2e8f0;
        font-size:5pt;
    }
    .mc-j.passe { background:#16a34a; color:white; }
    .mc-j.aujourdhui { background:#1d4ed8; color:white; border:1px solid #1d4ed8; }

    .pied { margin-top:16px; text-align:center; font-size:7pt; color:#94a3b8; border-top:1px solid #e2e8f0; padding-top:6px; }
    .resume { background:#f8fafc; border:1px solid #e2e8f0; border-radius:6px; padding:8px 12px; margin-bottom:12px; display:flex; gap:16px; flex-wrap:wrap; }
    .res-item .v { font-size:13pt; font-weight:bold; }
    .res-item .l { font-size:7pt; color:#64748b; text-transform:uppercase; }

    .legende { display:flex; gap:12px; font-size:7pt; color:#94a3b8; margin-top:4px; flex-wrap:wrap; }
    .legende span { display:flex; align-items:center; gap:4px; }
    .legende .c { display:inline-block; width:10px; height:10px; border-radius:2px; }
    .legende .c.ouvre { background:#bfdbfe; }
    .legende .c.dimanche { background:#f1f5f9; border:1px solid #e2e8f0; }
    .legende .c.passe { background:#16a34a; }
    .legende .c.aujourdhui { background:#1d4ed8; }
</style>
</head>
<body>

<div class="entete">
    <div>
        <div class="titre-doc">🏖️ Planning des Congés</div>
        <div class="sous-titre">Eden Group — Direction des Ressources Humaines</div>
        @if($mois)
        <div class="sous-titre">Période : {{ \Carbon\Carbon::parse($mois)->translatedFormat('F Y') }}</div>
        @endif
        <div class="sous-titre" style="font-size:7.5pt;color:#94a3b8;margin-top:2px;">
            ⚠️ Les dimanches ne sont pas comptabilisés dans la durée des congés
        </div>
    </div>
    <div style="text-align:right;font-size:8pt;color:#64748b;">
        Généré le {{ now()->format('d/m/Y à H:i') }}<br>
        <span style="font-weight:700;color:#1d4ed8;font-size:10pt;">{{ $conges->count() }}</span> congé(s)
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
        <div class="v" style="color:#94a3b8;">{{ $conges->where('statut','annule')->count() }}</div>
        <div class="l">Annulés</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th>Employé</th>
            <th>Matricule</th>
            <th>Direction</th>
            <th style="text-align:center;">Début</th>
            <th style="text-align:center;">Fin</th>
            <th style="text-align:center;">Jours</th>
            <th>Motif</th>
            <th>Statut</th>
            <th style="text-align:center;">Calendrier</th>
        </tr>
    </thead>
    <tbody>
    @forelse($conges as $c)
    <tr>
        <td><strong>{{ $c->employe?->nom }} {{ $c->employe?->prenom }}</strong></td>
        <td style="color:#64748b;font-weight:600;">{{ $c->employe?->matricule }}</td>
        <td>{{ $c->employe?->direction?->nom ?? '-' }}</td>
        <td style="text-align:center;font-weight:600;">{{ $c->date_debut->format('d/m/Y') }}</td>
        <td style="text-align:center;font-weight:600;">{{ $c->date_fin->format('d/m/Y') }}</td>
        <td style="text-align:center;font-weight:bold;color:#1d4ed8;font-size:9pt;">{{ $c->nb_jours }}</td>
        <td style="color:#64748b;font-size:7.5pt;">{{ $c->motif ?? '-' }}</td>
        <td>
            <span class="badge {{ $c->statut }}">{{ $c->statut_label }}</span>
        </td>
        <td>
            {{-- Mini calendrier des jours ouvrés (excluant dimanches) --}}
            <div style="display:flex;flex-direction:column;align-items:center;gap:2px;">
                <div class="mini-cal">
                    @php
                        $date = $c->date_debut->copy();
                        $joursAffiches = 0;
                        $maxJours = min($c->nb_jours + 7, 20);
                        $aujourdhui = \Carbon\Carbon::now()->startOfDay();
                    @endphp
                    @for($i = 0; $i < $maxJours && $joursAffiches < $c->nb_jours; $i++)
                        @php
                            $estDimanche = $date->isSunday();
                            $estAujourdhui = $date->isToday();
                            $estPasse = $date->isPast() && !$date->isToday();
                            
                            if (!$estDimanche) {
                                $joursAffiches++;
                            }
                            
                            $classe = 'mc-j';
                            if ($estDimanche) {
                                $classe .= ' dimanche';
                                $display = '✕';
                            } elseif ($estAujourdhui) {
                                $classe .= ' aujourdhui';
                                $display = $date->format('d');
                            } elseif ($estPasse) {
                                $classe .= ' passe';
                                $display = $date->format('d');
                            } else {
                                $display = $date->format('d');
                            }
                        @endphp
                        <div class="{{ $classe }}" title="{{ $date->format('d/m/Y') }}{{ $estDimanche ? ' (Dimanche non compté)' : '' }}">
                            {{ $display }}
                        </div>
                        @php $date->addDay(); @endphp
                    @endfor
                </div>
            </div>
        </td>
    </tr>
    @empty
    <tr><td colspan="9" style="text-align:center;color:#94a3b8;padding:20px;">Aucun congé enregistré pour cette période</td></tr>
    @endforelse
    </tbody>
</table>

{{-- Légende --}}
<div class="legende">
    <span><span class="c ouvre"></span> Jour ouvré</span>
    <span><span class="c dimanche"></span> Dimanche (non compté)</span>
    <span><span class="c passe"></span> Passé</span>
    <span><span class="c aujourdhui"></span> Aujourd'hui</span>
    <span style="font-weight:600;color:#1d4ed8;margin-left:8px;">
        Total jours ouvrés : {{ $conges->sum('nb_jours') }}
    </span>
</div>

<div class="pied">
    Eden Group · Direction des Ressources Humaines · Planning généré automatiquement
    <br>
    <span style="font-size:6.5pt;color:#cbd5e1;">
        Les dimanches ne sont pas comptabilisés dans la durée des congés conformément à la législation.
    </span>
</div>

</body>
</html>