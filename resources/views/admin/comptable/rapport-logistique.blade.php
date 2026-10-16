@extends('layouts.comptable')

@section('title', 'Rapport Logistique')

@section('styles')
<style>
    .rapport-header {
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        color: white; padding: 24px 28px; border-radius: 14px;
        margin-bottom: 20px; box-shadow: 0 4px 16px rgba(124,58,237,0.25);
    }
    .rapport-header h2 { font-weight: 800; margin: 0; }
    .rapport-header p { opacity: 0.9; margin: 4px 0 0 0; font-size: 13px; }

    .filter-section {
        background: white; border-radius: 12px; padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 20px;
    }
    .filter-section h6 {
        font-weight: 700; color: #1e3a5f;
        border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 12px;
    }

    .kpi-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px; margin-bottom: 20px;
    }
    .kpi-box {
        background: white; border-radius: 10px; padding: 14px; text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-top: 3px solid #7c3aed;
    }
    .kpi-box .val { font-size: 22px; font-weight: 800; color: #1e3a5f; }
    .kpi-box .lbl { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-top: 2px; }

    .rapport-table-wrap { overflow-x: auto; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
    .rapport-table { width: 100%; border-collapse: collapse; font-size: 12px; background: white; }
    .rapport-table thead tr { background: #7c3aed; color: white; }
    .rapport-table thead th { padding: 10px 8px; font-weight: 600; white-space: nowrap; text-align: left; }
    .rapport-table tbody tr:nth-child(even) { background: #f8fafc; }
    .rapport-table tbody tr:hover { background: #faf5ff; }
    .rapport-table tbody td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
    .rapport-table tfoot td { background: #1e3a5f; color: white; font-weight: 700; padding: 10px 8px; }

    .recap-section {
        background: white; border-radius: 12px; padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 20px;
    }
    .recap-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .recap-card { border-radius: 12px; padding: 16px; border-left: 4px solid; }
    .recap-card.paye     { background: #f0fdf4; border-left-color: #16a34a; }
    .recap-card.partiel  { background: #fffbeb; border-left-color: #f59e0b; }
    .recap-card.nonpaye  { background: #fef2f2; border-left-color: #dc2626; }
    .recap-card h6 { font-size: 13px; font-weight: 700; margin-bottom: 10px; }
    .recap-row {
        display: flex; justify-content: space-between; font-size: 12px;
        padding: 4px 0; border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .recap-row:last-child { border-bottom: none; }
    .recap-row .lbl { color: #64748b; }
    .recap-row .val { font-weight: 700; }

    .recap-total {
        font-size: 22px; font-weight: 900; text-align: center;
        margin-top: 20px; padding: 16px;
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        color: white; border-radius: 12px;
    }

    .badge-statut {
        display: inline-block; padding: 3px 10px; border-radius: 50px;
        font-size: 10px; font-weight: 800; text-transform: uppercase;
    }
    .badge-statut.paye     { background: #dcfce7; color: #15803d; border: 1.5px solid #86efac; }
    .badge-statut.partiel  { background: #fef3c7; color: #b45309; border: 1.5px solid #fcd34d; }
    .badge-statut.nonpaye  { background: #fee2e2; color: #b91c1c; border: 1.5px solid #fecaca; }

    .progress-bar-mini {
        width: 60px; height: 6px; background: #e2e8f0;
        border-radius: 3px; overflow: hidden; display: inline-block;
        vertical-align: middle;
    }
    .progress-bar-mini > div { height: 100%; border-radius: 3px; }

    /* ═══ Bénéficiaires avec date prévue ═══ */
    .benef-line {
        display: flex; align-items: center; gap: 6px;
        padding: 3px 6px; border-radius: 6px;
        font-size: 11px; margin-bottom: 3px;
        background: #faf5ff; border-left: 3px solid #7c3aed;
    }
    .benef-line .nom {
        font-weight: 700; color: #6d28d9;
        flex: 1; white-space: nowrap;
    }
    .benef-line .date {
        background: #eff6ff; color: #1e3a5f;
        padding: 2px 6px; border-radius: 4px;
        font-weight: 700; font-size: 10px;
        border: 1px solid #bfdbfe;
        white-space: nowrap;
    }
    .benef-line .date.vide {
        background: #f8fafc; color: #94a3b8;
        border: 1px dashed #cbd5e1;
        font-weight: 500;
    }
    .benef-line .montant {
        color: #16a34a; font-weight: 700;
        font-size: 10px;
    }
    .benef-line .montant.zero {
        color: #94a3b8; font-weight: 500;
    }

    .btn-payer {
        background: #7c3aed; color: white; border: none;
        padding: 10px 18px; border-radius: 8px; font-weight: 700;
        font-size: 13px; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-payer:hover { background: #6d28d9; color: white; }

    @media (max-width: 992px) { .recap-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

{{-- EN-TÊTE --}}
<div class="rapport-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2>📊 Rapport Logistique</h2>
            <p>Suivi des encaissements logistiques par bénéficiaire</p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;opacity:0.8;">Généré le</div>
            <div style="font-size:14px;font-weight:700;">{{ now()->format('d/m/Y à H:i') }}</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" action="{{ route('comptable.rapport') }}">
<div class="filter-section">
    <h6>🔍 Filtres</h6>
    <div class="row g-3">
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">🔍 Recherche</label>
            <input type="text" name="q" class="form-control form-control-sm"
                   placeholder="Nom, téléphone, dossier..." value="{{ request('q') }}">
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">🏢 Grand Site</label>
            <select name="grand_site_id" class="form-control form-control-sm">
                <option value="">Tous</option>
                @foreach($grandSites as $gs)
                    <option value="{{ $gs->id }}" {{ request('grand_site_id') == $gs->id ? 'selected' : '' }}>
                        {{ $gs->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">📊 Statut</label>
            <select name="statut" class="form-control form-control-sm">
                <option value="">Tous</option>
                <option value="paye"     {{ request('statut') == 'paye' ? 'selected' : '' }}>✅ Payés</option>
                <option value="partiel"  {{ request('statut') == 'partiel' ? 'selected' : '' }}>⏳ Partiels</option>
                <option value="non_paye" {{ request('statut') == 'non_paye' ? 'selected' : '' }}>❌ Non payés</option>
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">📅 Implantation prévue</label>
            <input type="date" name="date_implantation_prevue" class="form-control form-control-sm"
                   value="{{ request('date_implantation_prevue') }}">
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn-payer btn-sm">🔍 Générer</button>
            <a href="{{ route('comptable.rapport') }}" class="btn btn-outline-secondary btn-sm">✖ Reset</a>
        </div>
    </div>

    @if(request('date_implantation_prevue'))
        <div style="margin-top:12px;padding:8px 14px;background:#eff6ff;border-radius:8px;
                    font-size:12px;color:#1e40af;border-left:3px solid #1d4ed8;">
            📅 Filtre actif :
            <strong>Implantation prévue = {{ \Carbon\Carbon::parse(request('date_implantation_prevue'))->format('d/m/Y') }}</strong>
        </div>
    @endif
</div>
</form>

@if($dossiers->count() > 0)

{{-- KPI --}}
<div class="kpi-grid">
    <div class="kpi-box" style="border-top-color:#7c3aed;">
        <div class="val">{{ $stats['total_dossiers'] }}</div>
        <div class="lbl">Dossiers</div>
    </div>
    <div class="kpi-box" style="border-top-color:#16a34a;">
        <div class="val" style="color:#16a34a;">{{ $stats['nb_payes'] }}</div>
        <div class="lbl">✅ Payés</div>
    </div>
    <div class="kpi-box" style="border-top-color:#f59e0b;">
        <div class="val" style="color:#f59e0b;">{{ $stats['nb_partiels'] }}</div>
        <div class="lbl">⏳ Partiels</div>
    </div>
    <div class="kpi-box" style="border-top-color:#dc2626;">
        <div class="val" style="color:#dc2626;">{{ $stats['nb_non_payes'] }}</div>
        <div class="lbl">❌ Non payés</div>
    </div>
    <div class="kpi-box" style="border-top-color:#7c3aed;">
        <div class="val" style="font-size:18px;">{{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }}</div>
        <div class="lbl">Réf. FCFA</div>
    </div>
    <div class="kpi-box" style="border-top-color:#16a34a;">
        <div class="val" style="font-size:18px;color:#16a34a;">{{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }}</div>
        <div class="lbl">Encaissé FCFA</div>
    </div>
    <div class="kpi-box" style="border-top-color:#dc2626;">
        <div class="val" style="font-size:18px;color:#dc2626;">{{ number_format($stats['reste_logistique_total'], 0, ',', ' ') }}</div>
        <div class="lbl">Reste FCFA</div>
    </div>
    <div class="kpi-box" style="border-top-color:#7c3aed;">
        <div class="val" style="color:#7c3aed;">{{ $stats['progression_moyenne'] }}%</div>
        <div class="lbl">Progression</div>
    </div>
</div>

{{-- RÉCAP --}}
<div class="recap-section">
    <h5 style="font-weight:800;color:#1e3a5f;margin-bottom:16px;">🚗 Récapitulatif Logistique</h5>
    <div class="recap-grid">
        <div class="recap-card paye">
            <h6 style="color:#16a34a;">✅ Dossiers soldés</h6>
            <div class="recap-row"><span class="lbl">Nombre</span><span class="val" style="color:#16a34a;">{{ $stats['nb_payes'] }}</span></div>
            <div class="recap-row"><span class="lbl">Encaissé</span><span class="val" style="color:#16a34a;">{{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">% du total</span><span class="val">{{ $stats['total_dossiers'] > 0 ? round(($stats['nb_payes'] / $stats['total_dossiers']) * 100) : 0 }}%</span></div>
        </div>
        <div class="recap-card partiel">
            <h6 style="color:#b45309;">⏳ En cours</h6>
            <div class="recap-row"><span class="lbl">Nombre</span><span class="val" style="color:#b45309;">{{ $stats['nb_partiels'] }}</span></div>
            <div class="recap-row"><span class="lbl">Encaissé</span><span class="val" style="color:#16a34a;">{{ number_format($stats['paye_partiels'] ?? 0, 0, ',', ' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">Reste attendu</span><span class="val" style="color:#dc2626;">{{ number_format($stats['reste_partiels'] ?? 0, 0, ',', ' ') }} FCFA</span></div>
        </div>
        <div class="recap-card nonpaye">
            <h6 style="color:#b91c1c;">❌ Non payés</h6>
            <div class="recap-row"><span class="lbl">Nombre</span><span class="val" style="color:#b91c1c;">{{ $stats['nb_non_payes'] }}</span></div>
            <div class="recap-row"><span class="lbl">Attendu</span><span class="val" style="color:#dc2626;">{{ number_format($stats['prix_non_payes'] ?? 0, 0, ',', ' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">% du total</span><span class="val">{{ $stats['total_dossiers'] > 0 ? round(($stats['nb_non_payes'] / $stats['total_dossiers']) * 100) : 0 }}%</span></div>
        </div>
    </div>

    <div class="recap-total">
        <div style="font-size:12px;opacity:0.8;margin-bottom:6px;">TOTAL ENCAISSÉ</div>
        <div>{{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }} FCFA / {{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }} FCFA</div>
        <div style="font-size:14px;margin-top:6px;">
            Reste à encaisser : {{ number_format($stats['reste_logistique_total'], 0, ',', ' ') }} FCFA
            <span style="margin-left:12px;background:rgba(255,255,255,0.2);padding:2px 10px;border-radius:10px;">
                {{ $stats['progression_moyenne'] }}%
            </span>
        </div>
    </div>
</div>

{{-- EXPORTS --}}
<div class="d-flex gap-2 mb-3">
    <a href="{{ route('comptable.rapport.export', array_merge(request()->all(), ['type' => 'pdf'])) }}"
       class="btn btn-danger btn-sm" style="font-weight:700;">
        📄 Export PDF
    </a>
    <a href="{{ route('comptable.rapport.export', array_merge(request()->all(), ['type' => 'excel'])) }}"
       class="btn btn-success btn-sm" style="font-weight:700;">
        📊 Export Excel
    </a>
</div>

{{-- TABLEAU --}}
<div class="rapport-table-wrap">
<table class="rapport-table">
    <thead>
        <tr>
            <th>#</th>
            <th>Client</th>
            <th>Téléphone</th>
            <th>Dossier</th>
            <th>Grand Site</th>
            <th>👥 Bénéficiaire(s) & Date prévue</th>
            <th style="text-align:right;">Réf. Logistique</th>
            <th style="text-align:right;">Payé</th>
            <th style="text-align:right;">Reste</th>
            <th>Progression</th>
            <th>Statut</th>
            <th>Dernier paiement</th>
        </tr>
    </thead>
    <tbody>
    @foreach($dossiers as $i => $d)
        @php
            $payeLogi  = $d->paiementsLogistiques->sum('montant');
            $prixLogi  = $d->prix_logistique ?? 0;
            $resteLogi = max(0, $prixLogi - $payeLogi);
            $pct       = $prixLogi > 0 ? min(100, round(($payeLogi / $prixLogi) * 100)) : 0;

            if ($payeLogi >= $prixLogi && $prixLogi > 0) {
                $statut = 'paye';     $label = '✅ Payé';      $couleur = '#16a34a';
            } elseif ($payeLogi > 0) {
                $statut = 'partiel';  $label = '⏳ Partiel';   $couleur = '#f59e0b';
            } else {
                $statut = 'nonpaye';  $label = '❌ Non payé';  $couleur = '#dc2626';
            }

            $dernier = $d->paiementsLogistiques->sortByDesc('date_paiement')->first();

            // ✅ Récupérer les paiements logistiques par bénéficiaire
            $benefsPaiement = \App\Models\BeneficiairePaiement::with('beneficiaire')
                ->where('dossier_client_id', $d->id)
                ->where('paiement_type', 'logistique')
                ->get()
                ->groupBy('beneficiaire_id');

            // ✅ Construire la liste des bénéficiaires avec leur date prévue + montant
            $benefsListe = [];

            // 1. Bénéficiaires du dossier (source principale)
            foreach (($d->beneficiaires ?? collect()) as $b) {
                $montant = isset($benefsPaiement[$b->id])
                    ? $benefsPaiement[$b->id]->sum('montant')
                    : 0;

                $benefsListe[] = [
                    'nom'            => $b->nom,
                    'date_prevue'    => $b->implantation_prevue,
                    'montant'        => $montant,
                ];
            }

            // 2. Paiements orphelins (bénéficiaire non listé sur le dossier)
            foreach ($benefsPaiement as $benefId => $paiements) {
                if (($d->beneficiaires ?? collect())->contains('id', $benefId)) continue;

                $benefsListe[] = [
                    'nom'         => $paiements->first()->beneficiaire?->nom ?? 'Inconnu',
                    'date_prevue' => $paiements->first()->beneficiaire?->implantation_prevue,
                    'montant'     => $paiements->sum('montant'),
                ];
            }

            // Trier par date prévue (les plus proches en premier)
            usort($benefsListe, function ($a, $b) {
                if (!$a['date_prevue'] && !$b['date_prevue']) return 0;
                if (!$a['date_prevue']) return 1;
                if (!$b['date_prevue']) return -1;
                return strcmp($a['date_prevue'], $b['date_prevue']);
            });
        @endphp
        <tr>
            <td>{{ $i + 1 }}</td>
            <td><strong>{{ $d->client?->name ?? '-' }}</strong></td>
            <td>{{ $d->client?->phone ?? '-' }}</td>
            <td>{{ $d->nom_dossier ?? '-' }}</td>
            <td>{{ $d->grandSite?->nom ?? '-' }}</td>

            {{-- ✅ Colonne BÉNÉFICIAIRES + DATE PRÉVUE + MONTANT --}}
            <td style="min-width:280px;">
                @if(count($benefsListe) > 0)
                    @foreach($benefsListe as $b)
                        <div class="benef-line">
                            <span class="nom">👤 {{ $b['nom'] }}</span>

                            @if($b['date_prevue'])
                                <span class="date">
                                    📅 {{ \Carbon\Carbon::parse($b['date_prevue'])->format('d/m/Y') }}
                                </span>
                            @else
                                <span class="date vide">
                                    — non planifiée —
                                </span>
                            @endif

                            <span class="montant {{ $b['montant'] > 0 ? '' : 'zero' }}">
                                {{ number_format($b['montant'], 0, ',', ' ') }} F
                            </span>
                        </div>
                    @endforeach
                @else
                    <span style="color:#94a3b8;font-size:11px;">Aucun bénéficiaire</span>
                @endif
            </td>

            <td style="text-align:right;color:#7c3aed;font-weight:700;">{{ number_format($prixLogi, 0, ',', ' ') }}</td>
            <td style="text-align:right;color:#16a34a;font-weight:700;">{{ number_format($payeLogi, 0, ',', ' ') }}</td>
            <td style="text-align:right;color:#dc2626;font-weight:700;">{{ number_format($resteLogi, 0, ',', ' ') }}</td>
            <td>
                <div style="display:flex;align-items:center;gap:6px;">
                    <div class="progress-bar-mini">
                        <div style="width:{{ $pct }}%;background:{{ $couleur }};"></div>
                    </div>
                    <span style="font-size:11px;font-weight:700;color:{{ $couleur }};">{{ $pct }}%</span>
                </div>
            </td>
            <td><span class="badge-statut {{ $statut }}">{{ $label }}</span></td>
            <td>
                @if($dernier)
                    <span style="font-size:11px;color:#64748b;">📅 {{ \Carbon\Carbon::parse($dernier->date_paiement)->format('d/m/Y') }}</span>
                    <br>
                    <span style="font-size:10px;color:#94a3b8;">{{ number_format($dernier->montant, 0, ',', ' ') }} FCFA</span>
                @else
                    <span style="color:#94a3b8;">—</span>
                @endif
            </td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="6">TOTAUX</td>
            <td style="text-align:right;">{{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }}</td>
            <td style="text-align:right;color:#4ade80;">{{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }}</td>
            <td style="text-align:right;color:#fca5a5;">{{ number_format($stats['reste_logistique_total'], 0, ',', ' ') }}</td>
            <td colspan="3" style="text-align:center;">{{ $stats['progression_moyenne'] }}% moy.</td>
        </tr>
    </tfoot>
</table>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $dossiers->links() }}
</div>

@else
    <div class="alert alert-info" style="text-align:center;padding:60px 20px;background:white;border-radius:12px;">
        <div style="font-size:48px;">📭</div>
        <h4 style="color:#1e3a5f;font-weight:800;margin-top:12px;">Aucun dossier trouvé</h4>
        <p style="color:#64748b;font-size:13px;margin:0;">Aucun dossier logistique ne correspond à vos critères.</p>
        @if(request('date_implantation_prevue'))
            <a href="{{ route('comptable.rapport') }}" class="btn btn-outline-primary btn-sm" style="margin-top:12px;">
                ✖ Effacer le filtre
            </a>
        @endif
    </div>
@endif

@endsection