<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:DejaVu Sans, sans-serif; font-size:8px; color:#1e293b; }

.header {
    background:#7c3aed; color:white;
    padding:16px 20px; margin-bottom:14px;
    border-left:6px solid #5b21b6;
}
.header h1 { font-size:17px; font-weight:bold; margin-bottom:3px; }
.header p  { font-size:8.5px; opacity:0.85; }

.kpi-grid {
    width:100%; margin-bottom:14px;
    border-collapse:separate; border-spacing:6px 0;
}
.kpi-grid td {
    width:20%; padding:10px 12px;
    background:#f8fafc; border-radius:8px;
    text-align:center; vertical-align:middle;
    border-top:3px solid #7c3aed;
}
.kpi-grid .kpi-val {
    font-size:16px; font-weight:900; color:#1e3a5f;
}
.kpi-grid .kpi-lbl {
    font-size:7.5px; color:#64748b; text-transform:uppercase;
    font-weight:700; letter-spacing:0.3px; margin-top:2px;
}

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

table.data { width:100%; border-collapse:collapse; }
thead tr { background:#7c3aed; color:white; }
thead th {
    padding:6px 5px; font-size:7pt; font-weight:bold;
    text-align:left; white-space:nowrap;
}
tbody tr:nth-child(even) { background:#f8fafc; }
tbody td {
    padding:5px 4px; border-bottom:1px solid #e2e8f0;
    font-size:7pt; vertical-align:top;
}
tfoot td {
    background:#1e3a5f; color:white; font-weight:bold;
    padding:7px 5px; font-size:8px;
}

.statut {
    padding:2px 6px; border-radius:8px;
    font-size:6.5pt; font-weight:bold;
    display:inline-block;
    white-space:nowrap;
}
.statut-paye     { background:#dcfce7; color:#15803d; }
.statut-partiel  { background:#fef3c7; color:#b45309; }
.statut-nonpaye  { background:#fee2e2; color:#b91c1c; }

.benef-nom {
    font-weight:bold;
    color:#6d28d9;
    font-size:7.5pt;
}

.site-badge {
    background:#ecfdf5; color:#15803d;
    padding:1px 5px; border-radius:5px;
    font-size:6.5pt; font-weight:bold;
    border:1px solid #86efac;
    display:inline-block;
    white-space:nowrap;
}

.tf-badge {
    background:#fef3c7; color:#92400e;
    padding:1px 5px; border-radius:5px;
    font-size:6.5pt; font-weight:bold;
    border:1px solid #fcd34d;
    display:inline-block;
    white-space:nowrap;
}

.date-badge {
    background:#eff6ff; color:#1e3a5f;
    padding:1px 5px; border-radius:5px;
    font-size:6.5pt; font-weight:bold;
    border:1px solid #bfdbfe;
    display:inline-block;
    white-space:nowrap;
}
.date-badge.vide {
    background:#f8fafc; color:#94a3b8;
    border:1px dashed #cbd5e1;
    font-weight:500;
}

.progress-wrap {
    width:40px; height:5px; background:#e2e8f0;
    border-radius:3px; display:inline-block;
    vertical-align:middle; overflow:hidden;
}
.progress-bar { height:100%; border-radius:3px; }

.val-violet { color:#7c3aed; font-weight:bold; }
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

{{-- EN-TÊTE --}}
<div class="header">
    <h1>📊 Rapport Logistique — Paiements</h1>
    <p>
        Suivi des encaissements logistiques par bénéficiaire —
        Généré le {{ now()->format('d/m/Y à H:i') }} —
        {{ $stats['total_dossiers'] }} dossier(s)
    </p>
</div>

{{-- KPI --}}
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
            <div class="kpi-val" style="color:#7c3aed;">
                {{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">📊 Réf. FCFA</div>
        </td>
        <td>
            <div class="kpi-val" style="color:#16a34a;">
                {{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">💵 Encaissé FCFA</div>
        </td>
        <td>
            <div class="kpi-val" style="color:#dc2626;">
                {{ number_format($stats['reste_logistique_total'], 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">🔻 Reste FCFA</div>
        </td>
    </tr>
</table>

{{-- RÉCAP --}}
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
                    {{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }} FCFA
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
                    {{ number_format($stats['paye_partiels'] ?? 0, 0, ',', ' ') }} FCFA
                </span>
            </div>
            <div class="recap-row">
                <span class="lbl">Reste attendu</span>
                <span class="val" style="color:#dc2626;">
                    {{ number_format($stats['reste_partiels'] ?? 0, 0, ',', ' ') }} FCFA
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
                    {{ number_format($stats['prix_non_payes'] ?? 0, 0, ',', ' ') }} FCFA
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

{{-- TABLEAU — une ligne par bénéficiaire --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:20px;">#</th>
            <th>Bénéficiaire</th>
            <th>Téléphone</th>
            <th>Grand Site</th>
            <th>Site</th>
            <th>TF</th>
            <th>Implant. prévue</th>
            <th style="text-align:right;">Réf.</th>
            <th style="text-align:right;">Payé</th>
            <th style="text-align:right;">Reste</th>
            <th style="text-align:center;">Prog.</th>
            <th style="text-align:center;">Statut</th>
        </tr>
    </thead>
    <tbody>
    @php $row = 0; @endphp

    @foreach($dossiers as $d)
        @php
            $payeLogi  = $d->paiementsLogistiques->sum('montant');
            $prixLogi  = $d->prix_logistique ?? 0;

            $benefs   = $d->beneficiaires ?? collect();
            $nbBenefs = max(1, $benefs->count());

            // ✅ Site & TF
            $premiereAffectation = $d->affectations->first();
            $siteNom = $premiereAffectation?->site?->name;
            $tfNom   = $premiereAffectation?->tf?->title;

            if (!$siteNom || !$tfNom) {
                $premiereAffBenef = \App\Models\Affectation::where('dossier_client_id', $d->id)
                    ->with(['site', 'tf'])
                    ->first();
                $siteNom = $siteNom ?? $premiereAffBenef?->site?->name;
                $tfNom   = $tfNom   ?? $premiereAffBenef?->tf?->title;
            }
        @endphp

        @forelse($benefs as $b)
            @php
                $row++;

                // ✅ Paiement SPÉCIFIQUE
                $payeBenef = \App\Models\BeneficiairePaiement::where('dossier_client_id', $d->id)
                    ->where('beneficiaire_id', $b->id)
                    ->where('paiement_type', 'logistique')
                    ->sum('montant');

                // ✅ FALLBACK : si 0, répartir le total du dossier
                if ($payeBenef == 0 && $payeLogi > 0) {
                    $payeBenef = $payeLogi / $nbBenefs;
                }

                $prixBenef  = $prixLogi / $nbBenefs;
                $resteBenef = max(0, $prixBenef - $payeBenef);
                $pctBenef   = $prixBenef > 0 ? min(100, round(($payeBenef / $prixBenef) * 100)) : 0;

                if ($payeBenef >= $prixBenef && $prixBenef > 0) {
                    $statutB = 'paye';     $labelB = '✅ Payé';      $couleurB = '#16a34a';
                } elseif ($payeBenef > 0) {
                    $statutB = 'partiel';  $labelB = '⏳ Partiel';   $couleurB = '#f59e0b';
                } else {
                    $statutB = 'nonpaye';  $labelB = '❌ Non payé';  $couleurB = '#dc2626';
                }
            @endphp
            <tr>
                <td>{{ $row }}</td>

                <td>
                    <span class="benef-nom">👤 {{ $b->nom }}</span>
                </td>

                <td>
                    {{ $b->telephone ?? '—' }}
                </td>

                <td>
                    {{ $d->grandSite?->nom ?? '—' }}
                </td>

                <td>
                    @if($siteNom)
                        <span class="site-badge">🗺️ {{ $siteNom }}</span>
                    @else
                        <span class="val-gris">—</span>
                    @endif
                </td>

                <td>
                    @if($tfNom)
                        <span class="tf-badge">📄 {{ $tfNom }}</span>
                    @else
                        <span class="val-gris">—</span>
                    @endif
                </td>

                <td>
                    @if($b->implantation_prevue)
                        <span class="date-badge">
                            📅 {{ \Carbon\Carbon::parse($b->implantation_prevue)->format('d/m/Y') }}
                        </span>
                    @else
                        <span class="date-badge vide">— non planifiée —</span>
                    @endif
                </td>

                <td style="text-align:right;" class="val-violet">
                    {{ number_format($prixBenef, 0, ',', ' ') }}
                </td>
                <td style="text-align:right;" class="val-vert">
                    {{ number_format($payeBenef, 0, ',', ' ') }}
                </td>
                <td style="text-align:right;" class="val-rouge">
                    {{ number_format($resteBenef, 0, ',', ' ') }}
                </td>
                <td style="text-align:center;">
                    <span class="progress-wrap">
                        <span class="progress-bar" style="width:{{ $pctBenef }}%;background:{{ $couleurB }};"></span>
                    </span>
                    <span style="font-size:6.5pt;color:{{ $couleurB }};font-weight:bold;margin-left:3px;">
                        {{ $pctBenef }}%
                    </span>
                </td>
                <td style="text-align:center;">
                    <span class="statut statut-{{ $statutB }}">{{ $labelB }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="12" style="text-align:center;color:#94a3b8;padding:10px;">
                    Aucun bénéficiaire pour ce dossier
                </td>
            </tr>
        @endforelse
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7" style="text-align:right;">TOTAUX</td>
            <td style="text-align:right;">
                {{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }}
            </td>
            <td style="text-align:right;color:#4ade80;">
                {{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }}
            </td>
            <td style="text-align:right;color:#fca5a5;">
                {{ number_format($stats['reste_logistique_total'], 0, ',', ' ') }}
            </td>
            <td colspan="2" style="text-align:center;">
                {{ $stats['progression_moyenne'] }}% moy.
            </td>
        </tr>
    </tfoot>
</table>

<div class="footer">
    EDEN GROUP — Rapport logistique généré automatiquement — {{ now()->format('d/m/Y à H:i') }}
</div>

</body>
</html>