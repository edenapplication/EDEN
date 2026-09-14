<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
* { margin:0; padding:0; box-sizing:border-box; }
body { font-family:DejaVu Sans, sans-serif; font-size:7.5px; color:#1e293b; }

.header {
    background:#1e3a5f; color:white;
    padding:14px 18px; margin-bottom:14px;
    border-left:6px solid #dc2626;
}
.header h1 { font-size:16px; font-weight:bold; margin-bottom:3px; }
.header p  { font-size:8px; opacity:0.75; }

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
    font-size:16px; color:#0d6efd; font-weight:900;
    margin-top:2px;
}
.periode-banner .dates {
    font-size:8px; color:#475569; margin-top:2px;
}

/* ═══ RECAP ═══ */
.recap-grid {
    width:100%;
    margin-bottom:12px;
    border-collapse:separate;
    border-spacing:6px 0;
}
.recap-grid td {
    width:25%;
    padding:8px 10px;
    border-radius:6px;
    vertical-align:top;
    font-size:7.5px;
}
.recap-card-superficie   { background:#eff6ff; border-left:3px solid #0d6efd; }
.recap-card-technique    { background:#fff7ed; border-left:3px solid #ea580c; }
.recap-card-logistique   { background:#f5f3ff; border-left:3px solid #7c3aed; }
.recap-card-morcellement { background:#fefce8; border-left:3px solid #ca8a04; }
.recap-card h6 { font-size:8px; font-weight:bold; margin-bottom:4px; }
.recap-row { display:flex; justify-content:space-between; padding:1px 0; }
.recap-row .lbl { color:#64748b; }
.recap-row .val { font-weight:bold; }

/* ═══ TABLE ═══ */
table.data { width:100%; border-collapse:collapse; }
thead tr { background:#1e3a5f; color:white; }
thead th { padding:5px 4px; font-size:7px; font-weight:bold; text-align:left; white-space:nowrap; }
tbody tr:nth-child(even) { background:#f8fafc; }
tbody td { padding:4px; border-bottom:1px solid #e2e8f0; font-size:7px; white-space:nowrap; }
tfoot td { background:#1e3a5f; color:white; font-weight:bold; padding:6px 4px; font-size:7.5px; }

.footer { text-align:right; font-size:6px; color:#94a3b8; margin-top:8px; }

.badge-lot { background:#dbeafe; color:#1d4ed8; padding:1px 4px; border-radius:3px; font-size:6.5px; font-weight:bold; }

.statut { padding:1px 4px; border-radius:3px; font-size:6.5px; font-weight:bold; }
.statut-none                { background:#f1f5f9; color:#475569; }
.statut-implantation_prevue { background:#ede9fe; color:#7c3aed; }
.statut-deja_implante       { background:#dbeafe; color:#1d4ed8; }
.statut-dossier_technique   { background:#fef9c3; color:#854d0e; }
.statut-morcellement        { background:#dcfce7; color:#15803d; }

.periode-cell { color:#0d6efd; font-weight:bold; background:#dbeafe; padding:1px 4px; border-radius:3px; }
.periode-vide { color:#94a3b8; }

.progress-wrap { width:36px; height:4px; background:#e2e8f0; border-radius:2px; display:inline-block; vertical-align:middle; }
.progress-bar  { height:100%; border-radius:2px; }
</style>
</head>
<body>

<div class="header">
    <h1>📊 {{ $rapport_titre ?? 'Rapport EDEN GROUP' }}</h1>
    <p>
        @if(!empty($rapport_description))
            {{ $rapport_description }} —
        @endif
        Généré le {{ now()->format('d/m/Y à H:i') }} — {{ $dossiers->count() }} dossier(s)
    </p>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ✅ BANDEAU PÉRIODE (affiché uniquement si période sélectionnée) --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
@if(request('date_debut') || request('date_fin'))
    @php
        $totalPeriode = array_sum($paiementsPeriode ?? []);
    @endphp
    <div class="periode-banner">
        <div class="label">💵 Paiements sur la période</div>
        <div class="val">{{ number_format($totalPeriode, 0, ',', ' ') }} FCFA</div>
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
{{-- ✅ RÉCAP PAIEMENTS (4 catégories) --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<table class="recap-grid">
    <tr>
        <td class="recap-card-superficie">
            <h6 style="color:#0d6efd;">📁 Parcelle</h6>
            <div class="recap-row">
                <span class="lbl">Réf.</span>
                <span class="val">{{ number_format($totaux['prix_superficie_total'],0,',',' ') }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Payé</span>
                <span class="val" style="color:#16a34a;">{{ number_format($totaux['paye_superficie_total'],0,',',' ') }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Reste</span>
                <span class="val" style="color:#dc2626;">{{ number_format(max(0, $totaux['prix_superficie_total'] - $totaux['paye_superficie_total']),0,',',' ') }}</span>
            </div>
        </td>
        <td class="recap-card-technique">
            <h6 style="color:#ea580c;">🛠️ Technique</h6>
            <div class="recap-row">
                <span class="lbl">Réf.</span>
                <span class="val">{{ number_format($totaux['prix_technique_total'],0,',',' ') }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Payé</span>
                <span class="val" style="color:#16a34a;">{{ number_format($totaux['paye_technique_total'],0,',',' ') }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Reste</span>
                <span class="val" style="color:#dc2626;">{{ number_format(max(0, $totaux['prix_technique_total'] - $totaux['paye_technique_total']),0,',',' ') }}</span>
            </div>
        </td>
        <td class="recap-card-logistique">
            <h6 style="color:#7c3aed;">🚗 Logistique</h6>
            <div class="recap-row">
                <span class="lbl">Réf.</span>
                <span class="val">{{ number_format($totaux['prix_logistique_total'],0,',',' ') }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Payé</span>
                <span class="val" style="color:#16a34a;">{{ number_format($totaux['paye_logistique_total'],0,',',' ') }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Reste</span>
                <span class="val" style="color:#dc2626;">{{ number_format(max(0, $totaux['prix_logistique_total'] - $totaux['paye_logistique_total']),0,',',' ') }}</span>
            </div>
        </td>
        <td class="recap-card-morcellement">
            <h6 style="color:#ca8a04;">✂️ Morcellement</h6>
            <div class="recap-row">
                <span class="lbl">Réf.</span>
                <span class="val">{{ number_format($totaux['prix_morcellement_total'],0,',',' ') }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Payé</span>
                <span class="val" style="color:#16a34a;">{{ number_format($totaux['paye_morcellement_total'],0,',',' ') }}</span>
            </div>
            <div class="recap-row">
                <span class="lbl">Reste</span>
                <span class="val" style="color:#dc2626;">{{ number_format(max(0, $totaux['prix_morcellement_total'] - $totaux['paye_morcellement_total']),0,',',' ') }}</span>
            </div>
        </td>
    </tr>
</table>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- ✅ TABLEAU PRINCIPAL --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<table class="data">
    <thead>
        <tr>
            <th>#</th>
            @foreach($colonnesChoisies as $col)
                <th>{{ $colonnes[$col] ?? $col }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
    @foreach($dossiers as $i => $d)
        @php
            $payeSuperficie   = $d->paiements->sum('montant');
            $payeTechnique    = $d->paiementsTechniques->sum('montant');
            $payeLogistique   = $d->paiementsLogistiques?->sum('montant') ?? 0;
            $payeMorcellement = $d->paiementsMorcellements->sum('montant');
            $totalPaye        = $payeSuperficie + $payeTechnique + $payeLogistique + $payeMorcellement;

            $prixSuperficie   = $d->prix_superficie ?? 0;
            $prixTechnique    = $d->prix_technique ?? 0;
            $prixLogistique   = $d->prix_logistique ?? 0;
            $prixMorcellement = $d->prix_morcellement ?? 0;
            $totalPrix        = $prixSuperficie + $prixTechnique + $prixLogistique + $prixMorcellement;
            $totalReste       = max(0, $totalPrix - $totalPaye);
            $progression      = $totalPrix > 0 ? round(($totalPaye / $totalPrix) * 100) : 0;
            $progColor        = $progression < 40 ? '#dc3545' : ($progression < 75 ? '#fd7e14' : '#28a745');

            $affectations = $d->affectations;
            $nbLots       = $affectations->count();

            // ✅ Paiement période
            $payePeriode = ($paiementsPeriode ?? [])[$d->id] ?? 0;
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            @foreach($colonnesChoisies as $col)
            <td>
                @switch($col)

                    {{-- 👤 CLIENT --}}
                    @case('client')    <strong>{{ $d->client?->name ?? '-' }}</strong> @break
                    @case('telephone') {{ $d->client?->phone ?? '-' }} @break
                    @case('sexe')
                        {{ $d->client?->sexe === 'masculin' ? 'Masculin' : ($d->client?->sexe === 'feminin' ? 'Féminin' : '-') }}
                        @break

                    {{-- 📁 DOSSIER --}}
                    @case('nom_dossier')        {{ $d->nom_dossier ?? '-' }} @break
                    @case('grand_site_dossier') {{ $d->grandSite?->nom ?? '-' }} @break
                    @case('superficie')         {{ $d->superficie_voulue ? number_format($d->superficie_voulue,0,',',' ') : '-' }} @break
                    @case('direction')
                        {{ match($d->direction) {
                            'baffoussam' => 'Baffoussam',
                            'bagante'    => 'Bagante',
                            'direction_generale' => 'Dir. Générale',
                            default => $d->direction ?? '-'
                        } }}
                        @break
                    @case('statut_dossier')
                        @php
                            $statut = $d->etape_actuelle ?? 'none';
                            $label = match($statut) {
                                'implantation_prevue' => 'Implantation prévue',
                                'deja_implante'       => 'Déjà implanté',
                                'dossier_technique'   => 'Dossier technique',
                                'morcellement'        => 'Morcellement',
                                default               => 'Non commencé',
                            };
                        @endphp
                        <span class="statut statut-{{ $statut }}">{{ $label }}</span>
                        @break

                    {{-- 👥 ACTEURS --}}
                    @case('commercial')   {{ $d->commercial?->name ?? '-' }} @break
                    @case('facilitateur') {{ $d->facilitateur?->nom ?? '-' }} @break
                    @case('chauffeur')    {{ $d->conducteur?->nom ?? '-' }} @break
                    @case('agent')        {{ $d->agentCommercial?->nom ?? '-' }} @break

                    {{-- 🗺️ AFFECTATIONS --}}
                    @case('grand_site')
                        {{ $nbLots > 0 ? $affectations->pluck('grandSite.nom')->filter()->unique()->implode(', ') : '—' }}
                        @break
                    @case('site')
                        {{ $nbLots > 0 ? $affectations->pluck('grandSite.nom')->filter()->unique()->implode(', ') : '—' }}
                        @break
                    @case('tf')
                        {{ $nbLots > 0 ? $affectations->pluck('bloc.tf.title')->filter()->unique()->implode(', ') : '—' }}
                        @break
                    @case('bloc')
                        {{ $nbLots > 0 ? $affectations->pluck('bloc.code')->filter()->unique()->implode(', ') : '—' }}
                        @break
                    @case('lot')
                        @if($nbLots > 0)
                            @foreach($affectations as $aff)
                                @if($aff->lot)
                                    <span class="badge-lot">{{ strtoupper($aff->lot->numero) }}</span>
                                @endif
                            @endforeach
                        @else
                            <em style="color:#94a3b8;">Sans lot</em>
                        @endif
                        @break

                    {{-- 💰 PAIEMENTS --}}
                    @case('prix_superficie')   {{ number_format($prixSuperficie,0,',',' ') }} @break
                    @case('prix_technique')    {{ number_format($prixTechnique,0,',',' ') }} @break
                    @case('prix_logistique')   {{ number_format($prixLogistique,0,',',' ') }} @break
                    @case('prix_morcellement') {{ number_format($prixMorcellement,0,',',' ') }} @break
                    @case('paye_superficie')   {{ number_format($payeSuperficie,0,',',' ') }} @break
                    @case('paye_technique')    {{ number_format($payeTechnique,0,',',' ') }} @break
                    @case('paye_logistique')   {{ number_format($payeLogistique,0,',',' ') }} @break
                    @case('paye_morcellement') {{ number_format($payeMorcellement,0,',',' ') }} @break
                    @case('total_paye')        <strong style="color:#16a34a;">{{ number_format($totalPaye,0,',',' ') }}</strong> @break

                    {{-- ✅ Paiement période --}}
                    @case('paiement_periode')
                        @if($payePeriode > 0)
                            <span class="periode-cell">{{ number_format($payePeriode,0,',',' ') }}</span>
                        @else
                            <span class="periode-vide">—</span>
                        @endif
                        @break

                    @case('total_reste')       <strong style="color:#dc3545;">{{ number_format($totalReste,0,',',' ') }}</strong> @break
                    @case('progression')
                        <span class="progress-wrap">
                            <span class="progress-bar" style="width:{{ $progression }}%;background:{{ $progColor }};"></span>
                        </span>
                        <span style="font-size:6.5px;color:{{ $progColor }};font-weight:bold;">{{ $progression }}%</span>
                        @break

                    {{-- 📅 DATES --}}
                    @case('date_implantation') {{ $d->date_implantation_prevue?->format('d/m/Y') ?? '-' }} @break
                    @case('date_dossier_tech') {{ $d->date_dossier_technique?->format('d/m/Y') ?? '-' }} @break
                    @case('date_morcellement') {{ $d->date_morcellement?->format('d/m/Y') ?? '-' }} @break

                    @default —
                @endswitch
            </td>
            @endforeach
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td>TOTAUX</td>
            @foreach($colonnesChoisies as $col)
            <td>
                @switch($col)
                    @case('lot')               {{ $totaux['nb_dossiers'] }} dossiers @break
                    @case('superficie')        {{ number_format($totaux['superficie_totale'],0,',',' ') }} m² @break
                    @case('prix_superficie')   {{ number_format($totaux['prix_superficie_total'],0,',',' ') }} @break
                    @case('prix_technique')    {{ number_format($totaux['prix_technique_total'],0,',',' ') }} @break
                    @case('prix_logistique')   {{ number_format($totaux['prix_logistique_total'],0,',',' ') }} @break
                    @case('prix_morcellement') {{ number_format($totaux['prix_morcellement_total'],0,',',' ') }} @break
                    @case('paye_superficie')   {{ number_format($totaux['paye_superficie_total'],0,',',' ') }} @break
                    @case('paye_technique')    {{ number_format($totaux['paye_technique_total'],0,',',' ') }} @break
                    @case('paye_logistique')   {{ number_format($totaux['paye_logistique_total'],0,',',' ') }} @break
                    @case('paye_morcellement') {{ number_format($totaux['paye_morcellement_total'],0,',',' ') }} @break
                    @case('total_paye')        {{ number_format($totaux['total_paye'],0,',',' ') }} @break
                    @case('paiement_periode')
                        @if(($totaux['paiement_periode_total'] ?? 0) > 0)
                            {{ number_format($totaux['paiement_periode_total'],0,',',' ') }}
                        @else
                            —
                        @endif
                        @break
                    @case('total_reste')       {{ number_format($totaux['total_reste'],0,',',' ') }} @break
                    @case('progression')       {{ $totaux['avg_progression'] }}% moy. @break
                    @default —
                @endswitch
            </td>
            @endforeach
        </tr>
    </tfoot>
</table>

<div class="footer">EDEN GROUP — {{ now()->format('d/m/Y à H:i') }}</div>
</body>
</html>