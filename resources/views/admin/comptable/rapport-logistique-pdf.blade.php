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

.note-box {
    background:#fffbeb; border-left:5px solid #f59e0b;
    border-radius:8px; padding:12px 16px; margin-bottom:14px;
    font-size:9px; color:#92400e; line-height:1.5;
}
.note-box .title {
    font-size:10px; font-weight:bold; color:#78350f; margin-bottom:4px;
}

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
.kpi-grid .kpi-val { font-size:16px; font-weight:900; color:#1e3a5f; }
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

{{-- EN-TETE (sans emoji) --}}
<div class="header">
    <h1>Rapport de la Logistique des implanations</h1>
    <p>
        Suivi des encaissements logistiques par beneficiaire -
        Genere le {{ now()->format('d/m/Y a H:i') }} -
        {{ $stats['total_dossiers'] }} dossier(s)
    </p>
</div>

{{-- NOTE DU COMPTABLE --}}
@if(!empty($noteRapport))
<div class="note-box">
    <div class="title">NOTE DU COMPTABLE</div>
    <div>{!! nl2br(e($noteRapport)) !!}</div>
</div>
@endif

{{-- KPI (sans emojis) --}}
<table class="kpi-grid">
    <tr>
        <td>
            <div class="kpi-val">{{ $stats['nb_payes'] }}</div>
            <div class="kpi-lbl">Payes</div>
        </td>
        <td>
            <div class="kpi-val">{{ $stats['nb_partiels'] }}</div>
            <div class="kpi-lbl">Partiels</div>
        </td>
        <td>
            <div class="kpi-val">{{ $stats['nb_non_payes'] }}</div>
            <div class="kpi-lbl">Non payes</div>
        </td>
        <td>
            <div class="kpi-val" style="color:#7c3aed;">
                {{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">Ref. FCFA</div>
        </td>
        <td>
            <div class="kpi-val" style="color:#16a34a;">
                {{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">Encaisse FCFA</div>
        </td>
        <td>
            <div class="kpi-val" style="color:#dc2626;">
                {{ number_format($stats['reste_logistique_total'], 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">Reste FCFA</div>
        </td>
    </tr>
</table>

{{-- RECAP --}}
<table class="recap-grid">
    <tr>
        <td class="recap-card-paye">
            <h6 style="color:#16a34a;">Dossiers soldes</h6>
            <div class="recap-row">
                <span class="lbl">Nombre</span>
                <span class="val" style="color:#16a34a;">{{ $stats['nb_payes'] }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Encaisse</span>
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
            <h6 style="color:#b45309;">En cours</h6>
            <div class="recap-row">
                <span class="lbl">Nombre</span>
                <span class="val" style="color:#b45309;">{{ $stats['nb_partiels'] }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Encaisse</span>
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
            <h6 style="color:#b91c1c;">Non payes</h6>
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

{{-- TABLEAU (sans statut, sans emojis) --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:20px;">#</th>
            <th>Beneficiaire</th>
            <th>Telephone</th>
            <th>Grand Site</th>
            <th>Site</th>
            <th>TF</th>
            <th>Implant. prevue</th>
            <th style="text-align:right;">Ref.</th>
            <th style="text-align:right;">Paye</th>
            <th style="text-align:right;">Reste</th>
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

                $payeBenef = \App\Models\BeneficiairePaiement::where('dossier_client_id', $d->id)
                    ->where('beneficiaire_id', $b->id)
                    ->where('paiement_type', 'logistique')
                    ->sum('montant');

                if ($payeBenef == 0 && $payeLogi > 0) {
                    $payeBenef = $payeLogi / $nbBenefs;
                }

                $prixBenef  = $prixLogi / $nbBenefs;
                $resteBenef = max(0, $prixBenef - $payeBenef);
            @endphp
            <tr>
                <td>{{ $row }}</td>
                <td>
                    <span class="benef-nom">{{ $b->nom }}</span>
                </td>
                <td>
                    {{ $b->telephone ?? '-' }}
                </td>
                <td>
                    {{ $d->grandSite?->nom ?? '-' }}
                </td>
                <td>
                    @if($siteNom)
                        <span class="site-badge">{{ $siteNom }}</span>
                    @else
                        <span class="val-gris">-</span>
                    @endif
                </td>
                <td>
                    @if($tfNom)
                        <span class="tf-badge">{{ $tfNom }}</span>
                    @else
                        <span class="val-gris">-</span>
                    @endif
                </td>
                <td>
                    @if($b->implantation_prevue)
                        <span class="date-badge">
                            {{ \Carbon\Carbon::parse($b->implantation_prevue)->format('d/m/Y') }}
                        </span>
                    @else
                        <span class="date-badge vide">non planifiee</span>
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
            </tr>
        @empty
            <tr>
                <td colspan="10" style="text-align:center;color:#94a3b8;padding:10px;">
                    Aucun beneficiaire pour ce dossier
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
        </tr>
    </tfoot>
</table>

<div class="footer">
    EDEN GROUP - Rapport logistique genere automatiquement - {{ now()->format('d/m/Y a H:i') }}
</div>

</body>
</html>