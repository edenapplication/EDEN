<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:DejaVu Sans, sans-serif; font-size:8px; color:#1e293b; }

.header {
    background:#7c3aed; color:white;
    padding:14px 18px; margin-bottom:10px;
    border-left:5px solid #5b21b6;
}
.header h1 { font-size:15px; font-weight:bold; margin-bottom:2px; }
.header p  { font-size:8px; opacity:0.85; }

.note-box {
    background:#fffbeb; border-left:4px solid #f59e0b;
    border-radius:6px; padding:8px 12px; margin-bottom:10px;
    font-size:8.5px; color:#92400e; line-height:1.4;
}
.note-box .title {
    font-size:9px; font-weight:bold; color:#78350f; margin-bottom:3px;
}

/* ✅ KPI — 9 cellules sur UNE SEULE LIGNE */
.kpi-grid {
    width:100%; margin-bottom:10px;
    border-collapse:separate; border-spacing:3px 0;
    table-layout:fixed;
}
.kpi-grid td {
    padding:6px 4px;
    background:#f8fafc; border-radius:6px;
    text-align:center; vertical-align:middle;
    border-top:2px solid #7c3aed;
    width:11.11%;
}
.kpi-grid .kpi-val {
    font-size:11px; font-weight:900; color:#1e3a5f;
    line-height:1.1;
}
.kpi-grid .kpi-lbl {
    font-size:5.5px; color:#64748b; text-transform:uppercase;
    font-weight:700; letter-spacing:0.1px; margin-top:2px;
}

/* Couleurs spécifiques aux KPI comptables */
.kpi-comptable { border-top-color:#16a34a !important; background:#f0fdf4 !important; }
.kpi-comptable .kpi-val { color:#15803d; }

.kpi-transport { border-top-color:#f59e0b !important; background:#fffbeb !important; }
.kpi-transport .kpi-val { color:#b45309; }

.kpi-versement { border-top-color:#7c3aed !important; background:#f3e8ff !important; }
.kpi-versement .kpi-val { color:#6b21a8; }

/* TABLEAU */
table.data { width:100%; border-collapse:collapse; }
thead tr { background:#7c3aed; color:white; }
thead th {
    padding:5px 4px; font-size:6.5pt; font-weight:bold;
    text-align:left; white-space:nowrap;
}
tbody tr:nth-child(even) { background:#f8fafc; }
tbody td {
    padding:4px 3px; border-bottom:1px solid #e2e8f0;
    font-size:6.5pt; vertical-align:top;
}
tfoot td {
    background:#1e3a5f; color:white; font-weight:bold;
    padding:6px 4px; font-size:7.5px;
}

.benef-nom {
    font-weight:bold;
    color:#6d28d9;
    font-size:7pt;
}

.site-badge {
    background:#ecfdf5; color:#15803d;
    padding:1px 4px; border-radius:4px;
    font-size:6pt; font-weight:bold;
    border:1px solid #86efac;
    display:inline-block;
    white-space:nowrap;
}

.tf-badge {
    background:#fef3c7; color:#92400e;
    padding:1px 4px; border-radius:4px;
    font-size:6pt; font-weight:bold;
    border:1px solid #fcd34d;
    display:inline-block;
    white-space:nowrap;
}

.date-badge {
    background:#eff6ff; color:#1e3a5f;
    padding:1px 4px; border-radius:4px;
    font-size:6pt; font-weight:bold;
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
    text-align:right; font-size:6.5px; color:#94a3b8; margin-top:8px;
    border-top:1px solid #e2e8f0; padding-top:5px;
}
</style>
</head>
<body>

@php
    $dateUnique      = request('date_implantation');
    $dateDebutFiltre = request('date_implantation_debut');
    $dateFinFiltre   = request('date_implantation_fin');
    $filtreDateActif = $dateUnique || $dateDebutFiltre || $dateFinFiltre;

    // Valeurs comptables
    $totalComptable = $activite['total_comptable'] ?? 0;
    $nbComptable    = $activite['nb_comptable'] ?? 0;
    $totalAutres    = $activite['total_autres'] ?? 0;
    $nbAutres       = $activite['nb_autres'] ?? 0;
    $totalGlobal    = $activite['total_global'] ?? 0;
    $fraisT         = $fraisTransport ?? 0;
    $resteAR        = $resteAReverser ?? 0;
@endphp

{{-- EN-TETE --}}
<div class="header">
    <h1>{{ $titreRapport ?? 'Rapport des versement pour la Logistique des implantations' }}</h1>
    <p>
        Suivi des encaissements logistiques par beneficiaire -
        Genere le {{ now()->format('d/m/Y a H:i') }} -
        {{ $stats['total_dossiers'] }} dossier(s)
    </p>
</div>

{{-- NOTE --}}
@if(!empty($noteRapport))
<div class="note-box">
    <div class="title">NOTE DU COMPTABLE</div>
    <div>{!! nl2br(e($noteRapport)) !!}</div>
</div>
@endif

{{-- ✅ KPI — 9 cellules sur UNE SEULE LIGNE --}}
<table class="kpi-grid">
    <tr>
        {{-- 6 KPI standard --}}
        <td>
            <div class="kpi-val">{{ $stats['nb_payes'] }}</div>
            <div class="kpi-lbl">Paiements complets</div>
        </td>
        <td>
            <div class="kpi-val">{{ $stats['nb_partiels'] }}</div>
            <div class="kpi-lbl">Paiements Partiels</div>
        </td>
        <td>
            <div class="kpi-val">{{ $stats['nb_non_payes'] }}</div>
            <div class="kpi-lbl">Non payes</div>
        </td>
        <td>
            <div class="kpi-val" style="color:#7c3aed;">
                {{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">Total à encaisser</div>
        </td>
        <td>
            <div class="kpi-val" style="color:#16a34a;">
                {{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">Total encaisser</div>
        </td>
        <td>
            <div class="kpi-val" style="color:#dc2626;">
                {{ number_format($stats['reste_logistique_total'], 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">Reste à encaisser</div>
        </td>

        {{-- ✅ 3 KPI comptables --}}
        <td class="kpi-comptable">
            <div class="kpi-val">
                {{ number_format($totalComptable, 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">Encaissement de la comptable</div>
        </td>
        <td class="kpi-transport">
            <div class="kpi-val">
                {{ number_format($fraisT, 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">Transport de la comptable</div>
        </td>
        <td class="kpi-versement">
            <div class="kpi-val">
                {{ number_format($resteAR, 0, ',', ' ') }}
            </div>
            <div class="kpi-lbl">Versement de la comptable</div>
        </td>
    </tr>
</table>

{{-- TABLEAU --}}
<table class="data">
    <thead>
        <tr>
            <th style="width:18px;">#</th>
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

            $benefs = $d->beneficiaires ?? collect();

            if ($filtreDateActif) {
                $benefs = $benefs->filter(function ($b) use ($dateUnique, $dateDebutFiltre, $dateFinFiltre) {
                    if (!$b->implantation_prevue) return false;
                    $dateBenef = \Carbon\Carbon::parse($b->implantation_prevue)->format('Y-m-d');

                    if ($dateUnique) return $dateBenef === $dateUnique;

                    if ($dateDebutFiltre && $dateBenef < $dateDebutFiltre) return false;
                    if ($dateFinFiltre && $dateBenef > $dateFinFiltre) return false;
                    return true;
                })->values();
            }

            if ($benefs->count() === 0) continue;
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

        @foreach($benefs as $b)
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
                <td><span class="benef-nom">{{ $b->nom }}</span></td>
                <td>{{ $b->telephone ?? '-' }}</td>
                <td>{{ $d->grandSite?->nom ?? '-' }}</td>
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
        @endforeach
    @endforeach

    @if($row === 0)
        <tr>
            <td colspan="10" style="text-align:center;color:#94a3b8;padding:10px;">
                Aucun beneficiaire ne correspond aux criteres
            </td>
        </tr>
    @endif
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