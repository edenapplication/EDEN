<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:DejaVu Sans, sans-serif; font-size:8px; color:#1e293b; }

.header {
    background:#ea580c; color:white;
    padding:16px 20px; margin-bottom:14px;
    border-left:6px solid #7c2d12;
}
.header h1 { font-size:17px; font-weight:bold; margin-bottom:3px; }
.header p  { font-size:8.5px; opacity:0.85; }

/* ═══ BANDEAU PÉRIODE ═══ */
.periode-banner {
    background:linear-gradient(135deg, #dbeafe, #eff6ff);
    border-left:5px solid #0d6efd;
    padding:10px 14px;
    margin-bottom:12px;
    border-radius:6px;
}
.periode-banner .label {
    font-size:9px; color:#1e40af; font-weight:700;
    text-transform:uppercase; letter-spacing:0.5px;
}
.periode-banner .val {
    font-size:18px; color:#0d6efd; font-weight:900;
    margin-top:2px;
}
.periode-banner .dates {
    font-size:8.5px; color:#475569; margin-top:2px;
}

/* ═══ KPI GLOBAL ═══ */
.kpi-grid {
    width:100%; margin-bottom:14px;
    border-collapse:separate; border-spacing:6px 0;
}
.kpi-grid td {
    width:20%; padding:10px 12px;
    background:#f8fafc; border-radius:8px;
    text-align:center; vertical-align:middle;
    border-top:3px solid #ea580c;
}
.kpi-grid .kpi-val {
    font-size:16px; font-weight:900; color:#1e3a5f;
}
.kpi-grid .kpi-lbl {
    font-size:7.5px; color:#64748b; text-transform:uppercase;
    font-weight:700; letter-spacing:0.3px; margin-top:2px;
}

/* ═══ RECAP 3 CARTES ═══ */
.recap-grid {
    width:100%; margin-bottom:12px;
    border-collapse:separate; border-spacing:6px 0;
}
.recap-grid td {
    width:33.33%; padding:10px 12px;
    border-radius:8px; vertical-align:top;
    font-size:8px;
}
.recap-card-paye     { background:#f0fdf4; border-left:4px solid #16a34a; }
.recap-card-partiel  { background:#fffbeb; border-left:4px solid #f59e0b; }
.recap-card-nonpaye  { background:#fef2f2; border-left:4px solid #dc2626; }
.recap-card h6 { font-size:9px; font-weight:bold; margin-bottom:6px; }
.recap-row { display:flex; justify-content:space-between; padding:2px 0; }
.recap-row .lbl { color:#64748b; }
.recap-row .val { font-weight:bold; }

/* ═══ TABLE PRINCIPALE ═══ */
table.data { width:100%; border-collapse:collapse; }
thead tr { background:#1e3a5f; color:white; }
thead th {
    padding:6px 5px; font-size:7.5px; font-weight:bold;
    text-align:left; white-space:nowrap;
}
tbody tr:nth-child(even) { background:#f8fafc; }
tbody td {
    padding:5px; border-bottom:1px solid #e2e8f0;
    font-size:7.5px; white-space:nowrap;
}
tfoot td {
    background:#1e3a5f; color:white; font-weight:bold;
    padding:7px 5px; font-size:8px;
}

/* ═══ BADGES STATUT ═══ */
.statut {
    padding:2px 6px; border-radius:8px;
    font-size:7px; font-weight:bold;
    display:inline-block;
}
.statut-paye     { background:#dcfce7; color:#15803d; }
.statut-partiel  { background:#fef3c7; color:#b45309; }
.statut-nonpaye  { background:#fee2e2; color:#b91c1c; }

/* ═══ PROGRESSION ═══ */
.progress-wrap {
    width:45px; height:5px; background:#e2e8f0;
    border-radius:3px; display:inline-block;
    vertical-align:middle; overflow:hidden;
}
.progress-bar { height:100%; border-radius:3px; }

/* ═══ VALEURS ═══ */
.val-orange { color:#ea580c; font-weight:bold; }
.val-vert   { color:#16a34a; font-weight:bold; }
.val-rouge  { color:#dc2626; font-weight:bold; }
.val-gris   { color:#94a3b8; font-style:italic; }

.footer {
    text-align:right; font-size:7px; color:#94a3b8; margin-top:10px;
    border-top:1px solid #e2e8f0; padding-top:6px;
}
</style>
</head>
<body>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- EN-TÊTE --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="header">
    <h1>📊 Rapport Technique — Paiements</h1>
    <p>
        Suivi des encaissements du dossier technique —
        Généré le {{ now()->format('d/m/Y à H:i') }} —
        {{ $stats['total_dossiers'] }} dossier(s)
    </p>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- BANDEAU PÉRIODE (si sélectionnée) --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@if(request('date_debut') || request('date_fin'))
    <div class="periode-banner">
        <div class="label">💵 Paiements sur la période</div>
        <div class="val">{{ number_format($stats['paiement_periode_total'], 0, ',', ' ') }} FCFA</div>
        <div class="dates">
            @if(request('date_debut') && request('date_fin'))
                Du {{ \Carbon\Carbon::parse(request('date_debut'))->format('d/m/Y') }}
                au {{ \Carbon\Carbon::parse(request('date_fin'))->format('d/m/Y') }}
            @elseif(request('date_debut'))
                À partir du {{ \Carbon\Carbon::parse(request('date_debut'))->format('d/m/Y') }}
            @else
                Jusqu'au {{ \Carbon\Carbon::parse(request('date_fin'))->format('d/m/Y') }}
            @endif
        </div>
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- KPI GLOBAL --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<table class="kpi-grid">
    <tr>
        <td>
            <div class="kpi-val">{{ $stats['nb_payes'] }}</div>
            <div class="kpi-lbl">✅ Payés</div>
        </td>
        <td>
            <div class="kpi-val">{{ $stats['nb_partiels'] }}</div>
            <div class="kpi-lbl">⏳ Partiels</div>
        </td>
        <td>
            <div class="kpi-val">{{ $stats['nb_non_payes'] }}</div>
            <div class="kpi-lbl">❌ Non payés</div>
        </td>
        <td>
            <div class="kpi-val" style="color:#16a34a;">
                {{ number_format($stats['paye_technique_total'], 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">💵 Encaissé FCFA</div>
        </td>
        <td>
            <div class="kpi-val" style="color:#dc2626;">
                {{ number_format($stats['reste_technique_total'], 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">🔻 Reste FCFA</div>
        </td>
    </tr>
</table>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- RÉCAP 3 STATUTS --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<table class="recap-grid">
    <tr>
        <td class="recap-card-paye">
            <h6 style="color:#16a34a;">✅ Dossiers soldés</h6>
            <div class="recap-row">
                <span class="lbl">Nombre</span>
                <span class="val" style="color:#16a34a;">{{ $stats['nb_payes'] }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Encaissé</span>
                <span class="val" style="color:#16a34a;">
                    {{ number_format($stats['paye_payes'], 0, ',', ' ') }} FCFA
                </span>
            </div>
            <div class="recap-row">
                <span class="lbl">% du total</span>
                <span class="val">
                    {{ $stats['total_dossiers'] > 0 ? round(($stats['nb_payes'] / $stats['total_dossiers']) * 100) : 0 }}%
                </span>
            </div>
        </td>
        <td class="recap-card-partiel">
            <h6 style="color:#b45309;">⏳ En cours</h6>
            <div class="recap-row">
                <span class="lbl">Nombre</span>
                <span class="val" style="color:#b45309;">{{ $stats['nb_partiels'] }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Encaissé</span>
                <span class="val" style="color:#16a34a;">
                    {{ number_format($stats['paye_partiels'], 0, ',', ' ') }} FCFA
                </span>
            </div>
            <div class="recap-row">
                <span class="lbl">Reste attendu</span>
                <span class="val" style="color:#dc2626;">
                    {{ number_format($stats['reste_partiels'], 0, ',', ' ') }} FCFA
                </span>
            </div>
        </td>
        <td class="recap-card-nonpaye">
            <h6 style="color:#b91c1c;">❌ Non payés</h6>
            <div class="recap-row">
                <span class="lbl">Nombre</span>
                <span class="val" style="color:#b91c1c;">{{ $stats['nb_non_payes'] }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Attendu</span>
                <span class="val" style="color:#dc2626;">
                    {{ number_format($stats['prix_non_payes'], 0, ',', ' ') }} FCFA
                </span>
            </div>
            <div class="recap-row">
                <span class="lbl">% du total</span>
                <span class="val">
                    {{ $stats['total_dossiers'] > 0 ? round(($stats['nb_non_payes'] / $stats['total_dossiers']) * 100) : 0 }}%
                </span>
            </div>
        </td>
    </tr>
</table>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- TABLEAU PRINCIPAL --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:20px;">#</th>
            <th>Client</th>
            <th>Téléphone</th>
            <th>Dossier</th>
            <th>Grand Site</th>
            <th style="text-align:right;">Réf. Technique</th>
            <th style="text-align:right;">Payé</th>
            <th style="text-align:right;">Reste</th>
            <th style="text-align:center;">Progression</th>
            <th style="text-align:center;">Statut</th>
            <th>Dernier paiement</th>
        </tr>
    </thead>
    <tbody>
    @foreach($dossiers as $i => $d)
        @php
            $payeTech  = $d->paiementsTechniques->sum('montant');
            $prixTech  = $d->prix_technique ?? 0;
            $resteTech = max(0, $prixTech - $payeTech);
            $pct       = $prixTech > 0 ? min(100, round(($payeTech / $prixTech) * 100)) : 0;

            if ($payeTech >= $prixTech && $prixTech > 0) {
                $statut = 'paye';     $label = '✅ Payé';      $couleur = '#16a34a';
            } elseif ($payeTech > 0) {
                $statut = 'partiel';  $label = '⏳ Partiel';   $couleur = '#f59e0b';
            } else {
                $statut = 'nonpaye';  $label = '❌ Non payé';  $couleur = '#dc2626';
            }

            $dernier = $d->paiementsTechniques->sortByDesc('date_paiement')->first();
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td><strong>{{ $d->client?->name ?? '-' }}</strong></td>
            <td>{{ $d->client?->phone ?? '-' }}</td>
            <td>{{ $d->nom_dossier ?? '-' }}</td>
            <td>{{ $d->grandSite?->nom ?? '-' }}</td>
            <td style="text-align:right;" class="val-orange">
                {{ number_format($prixTech, 0, ',', ' ') }}
            </td>
            <td style="text-align:right;" class="val-vert">
                {{ number_format($payeTech, 0, ',', ' ') }}
            </td>
            <td style="text-align:right;" class="val-rouge">
                {{ number_format($resteTech, 0, ',', ' ') }}
            </td>
            <td style="text-align:center;">
                <span class="progress-wrap">
                    <span class="progress-bar" style="width:{{ $pct }}%;background:{{ $couleur }};"></span>
                </span>
                <span style="font-size:7px;color:{{ $couleur }};font-weight:bold;margin-left:3px;">
                    {{ $pct }}%
                </span>
            </td>
            <td style="text-align:center;">
                <span class="statut statut-{{ $statut }}">{{ $label }}</span>
            </td>
            <td>
                @if($dernier)
                    <span style="font-size:7.5px;color:#475569;">
                        📅 {{ \Carbon\Carbon::parse($dernier->date_paiement)->format('d/m/Y') }}
                    </span>
                    <br>
                    <span style="font-size:7px;color:#94a3b8;">
                        {{ number_format($dernier->montant, 0, ',', ' ') }} FCFA
                    </span>
                @else
                    <span class="val-gris">—</span>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" style="text-align:right;">TOTAUX</td>
            <td style="text-align:right;">
                {{ number_format($stats['prix_technique_total'], 0, ',', ' ') }}
            </td>
            <td style="text-align:right;color:#4ade80;">
                {{ number_format($stats['paye_technique_total'], 0, ',', ' ') }}
            </td>
            <td style="text-align:right;color:#fca5a5;">
                {{ number_format($stats['reste_technique_total'], 0, ',', ' ') }}
            </td>
            <td colspan="3" style="text-align:center;">
                {{ $stats['progression_moyenne'] }}% moy.
            </td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    EDEN GROUP — Rapport technique généré automatiquement — {{ now()->format('d/m/Y à H:i') }}
</div>

</body>
</html>