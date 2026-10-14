@extends('layouts.comptable')

@section('title', 'Paiement Technique')

@section('styles')
<style>
    .comptable-header {
        background: linear-gradient(135deg, #1e3a5f 0%, #2d5a8c 100%);
        color: white; border-radius: 14px; padding: 24px 28px;
        margin-bottom: 20px; box-shadow: 0 4px 16px rgba(30,58,95,0.2);
    }
    .comptable-header h2 { font-weight: 800; margin: 0; font-size: 22px; }
    .comptable-header .sub { font-size: 13px; opacity: 0.8; margin-top: 4px; }

    .stats-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px; margin-bottom: 20px;
    }
    .stat-card {
        background: white; border-radius: 12px; padding: 16px 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-left: 4px solid #1d4ed8;
    }
    .stat-card.paye     { border-left-color: #16a34a; }
    .stat-card.non_paye { border-left-color: #dc2626; }
    .stat-card.partiel  { border-left-color: #f59e0b; }
    .stat-card.sans_prix{ border-left-color: #7c3aed; }
    .stat-card .label {
        font-size: 11px; color: #64748b; text-transform: uppercase;
        font-weight: 700; letter-spacing: 0.5px;
    }
    .stat-card .value { font-size: 24px; font-weight: 900; color: #1e3a5f; margin-top: 4px; }
    .stat-card.paye .value      { color: #16a34a; }
    .stat-card.non_paye .value  { color: #dc2626; }
    .stat-card.partiel .value   { color: #f59e0b; }
    .stat-card.sans_prix .value { color: #7c3aed; }

    /* ALERTE SANS PRIX */
    .alert-sans-prix {
        background: linear-gradient(135deg, #fef3c7, #fde68a);
        border-left: 5px solid #f59e0b;
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }
    .alert-sans-prix .info {
        font-size: 14px;
        color: #92400e;
    }
    .alert-sans-prix .info strong { font-weight: 800; }

    .filtre-box {
        background: white; border-radius: 12px; padding: 16px;
        margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .filtre-box .search-input {
        font-size: 15px;
        padding: 10px 16px 10px 44px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        width: 100%;
        transition: all 0.2s;
        background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") no-repeat 14px center;
    }
    .filtre-box .search-input:focus {
        outline: none;
        border-color: #dc2626;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(220,38,38,0.1);
    }
    .filtre-box .form-select {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 14px;
        font-size: 13px;
        font-weight: 600;
        color: #1e3a5f;
        background-color: #f8fafc;
    }
    .filtre-box .form-select:focus {
        outline: none;
        border-color: #dc2626;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(220,38,38,0.1);
    }

    /* CARTE CLIENT */
    .client-card {
        background: white; border-radius: 14px; margin-bottom: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06); overflow: hidden;
        transition: box-shadow 0.25s;
    }
    .client-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
    .client-header {
        background: linear-gradient(135deg, #1e3a5f, #2d5a8c);
        color: white; padding: 14px 20px;
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 10px;
    }
    .client-header .client-name { font-size: 15px; font-weight: 800; }
    .client-header .client-meta { font-size: 12px; opacity: 0.85; margin-top: 2px; }
    .client-header .client-badge {
        background: rgba(255,255,255,0.15); padding: 6px 14px;
        border-radius: 20px; font-size: 11px; font-weight: 700;
        white-space: nowrap;
    }
    .client-header .client-badge.paid    { background: rgba(34,197,94,0.3); }
    .client-header .client-badge.partial { background: rgba(245,158,11,0.3); }
    .client-header .client-badge.none    { background: rgba(220,38,38,0.3); }

    .client-body { padding: 12px; }

    /* LIGNE DOSSIER */
    .dossier-row {
        background: white; border-radius: 12px; padding: 16px 18px;
        margin-bottom: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-left: 4px solid #e2e8f0; transition: all 0.3s;
        display: flex; justify-content: space-between; align-items: center;
        gap: 16px; flex-wrap: wrap;
    }
    .dossier-row:last-child { margin-bottom: 0; }
    .dossier-row:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); transform: translateX(3px); }
    .dossier-row.paye      { border-left-color: #16a34a; background: #f0fdf4; }
    .dossier-row.partiel   { border-left-color: #f59e0b; background: #fffbeb; }
    .dossier-row.non_paye  { border-left-color: #dc2626; background: #fef2f2; }
    .dossier-row.sans_prix { border-left-color: #7c3aed; background: #faf5ff; }

    .badge-statut {
        padding: 6px 14px; border-radius: 50px; font-size: 11px;
        font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .badge-statut.paye      { background: #dcfce7; color: #15803d; border: 1.5px solid #86efac; }
    .badge-statut.partiel   { background: #fef3c7; color: #b45309; border: 1.5px solid #fcd34d; }
    .badge-statut.non_paye  { background: #fee2e2; color: #b91c1c; border: 1.5px solid #fecaca; }
    .badge-statut.sans_prix { background: #f3e8ff; color: #7c3aed; border: 1.5px solid #c4b5fd; }

    .progress-bar-mini {
        height: 6px; background: #e2e8f0; border-radius: 3px;
        overflow: hidden; margin-top: 6px; width: 100%;
    }
    .progress-bar-mini > div { height: 100%; border-radius: 3px; transition: width 0.3s; }

    .btn-payer {
        background: #1d4ed8; color: white; border: none;
        padding: 8px 20px; border-radius: 8px; font-weight: 700;
        font-size: 13px; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
        transition: all 0.2s; white-space: nowrap;
    }
    .btn-payer:hover { background: #1e40af; color: white; transform: scale(1.03); }
    .btn-payer.disabled { background: #cbd5e1; cursor: not-allowed; transform: none; }

    .btn-prix {
        background: #7c3aed; color: white; border: none;
        padding: 8px 20px; border-radius: 8px; font-weight: 700;
        font-size: 13px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: all 0.2s; white-space: nowrap;
    }
    .btn-prix:hover { background: #6d28d9; color: white; transform: scale(1.03); }

    .dossier-info { flex: 1; min-width: 250px; }
    .dossier-nom { font-weight: 800; color: #1e3a5f; font-size: 15px; }
    .dossier-meta { font-size: 12px; color: #64748b; margin-top: 2px; }
    .dossier-montants { text-align: right; margin-right: 16px; min-width: 180px; }
    .montant-ref { font-size: 11px; color: #94a3b8; }
    .montant-paye { font-size: 14px; font-weight: 900; }
    .montant-reste { font-size: 11px; font-weight: 700; }

    /* MODAL */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(15,23,42,0.6); z-index: 9998;
        backdrop-filter: blur(2px);
    }
    .modal-overlay.active { display: block; }
    .modal-box {
        display: none; position: fixed; top: 50%; left: 50%;
        transform: translate(-50%, -50%); background: white;
        padding: 24px; border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        z-index: 9999; animation: modalSlideIn 0.2s ease;
    }
    .modal-box.active { display: block; }
    @keyframes modalSlideIn {
        from { opacity: 0; transform: translate(-50%, -45%); }
        to   { opacity: 1; transform: translate(-50%, -50%); }
    }

    .empty-state {
        text-align: center; padding: 60px 20px; color: #94a3b8;
        background: white; border-radius: 12px;
    }

    @media (max-width: 768px) {
        .dossier-row { flex-direction: column; align-items: stretch; }
        .dossier-montants { text-align: left; margin-right: 0; }
        .action-buttons { min-width: auto !important; }
    }
</style>
@endsection

@section('content')

{{-- ═══ EN-TÊTE ═══ --}}
<div class="comptable-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2>💰 Paiement Technique</h2>
            <div class="sub">
                Gérez les paiements et les prix techniques
            </div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;opacity:0.7;">Total à encaisser</div>
            <div style="font-size:20px;font-weight:900;">
                {{ number_format($stats['prix_technique_total'], 0, ',', ' ') }} FCFA
            </div>
        </div>
    </div>
</div>

{{-- ═══ ALERTE SANS PRIX ═══ --}}
@if(($dossiersSansPrix ?? 0) > 0)
    <div class="alert-sans-prix">
        <div class="info">
            <strong>⚠️ {{ $dossiersSansPrix }} dossier(s) sans prix technique</strong>
            <br>
            Ces dossiers ne sont pas payables tant que leur prix n'est pas défini.
        </div>
        <a href="{{ route('comptable.index', ['statut' => 'sans_prix']) }}"
           class="btn-prix">
            ⚙️ Voir et définir
        </a>
    </div>
@endif

{{-- ═══ STATISTIQUES ═══ --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="label">📂 Total dossiers</div>
        <div class="value">{{ $stats['total_dossiers'] }}</div>
    </div>
    <div class="stat-card paye">
        <div class="label">✅ Payés</div>
        <div class="value">{{ $stats['nb_payes'] }}</div>
    </div>
    <div class="stat-card non_paye">
        <div class="label">❌ Non payés</div>
        <div class="value">{{ $stats['nb_non_payes'] }}</div>
    </div>
    <div class="stat-card partiel">
        <div class="label">⏳ Partiels</div>
        <div class="value">{{ $stats['nb_partiels'] }}</div>
    </div>
    <div class="stat-card paye">
        <div class="label">💵 Encaissé</div>
        <div class="value" style="font-size:18px;">
            {{ number_format($stats['paye_technique_total'], 0, ',', ' ') }} FCFA
        </div>
    </div>
</div>

{{-- ═══ FILTRES ═══ --}}
<div class="filtre-box">
    <form method="GET" action="{{ route('comptable.index') }}" id="form-filtres">
        <div class="row g-2 align-items-end">

            <div class="col-md-5">
                <label style="font-size:11px;font-weight:700;color:#64748b;">🔍 Recherche</label>
                <input type="text" name="q" id="search-input" class="search-input"
                       placeholder="Nom client, téléphone, dossier..."
                       value="{{ request('q') }}"
                       autocomplete="off">
            </div>

            <div class="col-md-2">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📊 Statut</label>
                <select name="statut" id="filtre-statut" class="form-select"
                        onchange="document.getElementById('form-filtres').submit()">
                    <option value="">Tous</option>
                    <option value="paye"      {{ request('statut') == 'paye' ? 'selected' : '' }}>✅ Payés</option>
                    <option value="partiel"   {{ request('statut') == 'partiel' ? 'selected' : '' }}>⏳ Partiels</option>
                    <option value="non_paye"  {{ request('statut') == 'non_paye' ? 'selected' : '' }}>❌ Non payés</option>
                    <option value="sans_prix" {{ request('statut') == 'sans_prix' ? 'selected' : '' }}>⚙️ Sans prix ({{ $dossiersSansPrix ?? 0 }})</option>
                </select>
            </div>

            <div class="col-md-2">
                <label style="font-size:11px;font-weight:700;color:#64748b;">🏢 Grand Site</label>
                <select name="grand_site_id" class="form-select"
                        onchange="document.getElementById('form-filtres').submit()">
                    <option value="">Tous</option>
                    @foreach($grandSites as $gs)
                        <option value="{{ $gs->id }}" {{ request('grand_site_id') == $gs->id ? 'selected' : '' }}>
                            {{ $gs->nom }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3 d-flex gap-2 justify-content-end">
                <button type="submit" class="btn-payer">🔍 Filtrer</button>
                <a href="{{ route('comptable.index') }}" class="btn btn-outline-secondary"
                   style="padding:8px 14px;">✖</a>
            </div>

        </div>
    </form>
</div>

{{-- ═══ LISTE GROUPÉE PAR CLIENT ═══ --}}
@forelse($dossiersGroupes as $clientId => $dossiersDuClient)
    @php
        $client = $dossiersDuClient->first()->client;
        $totalDossiers = $dossiersDuClient->count();

        $nbPayes = 0; $nbPartiels = 0; $nbNonPayes = 0; $nbSansPrix = 0;

        foreach ($dossiersDuClient as $d) {
            $paye = $d->paiementsTechniques->sum('montant');
            $prix = $d->prix_technique ?? 0;

            if ($prix <= 0) {
                $nbSansPrix++;
            } elseif ($paye >= $prix) {
                $nbPayes++;
            } elseif ($paye > 0) {
                $nbPartiels++;
            } else {
                $nbNonPayes++;
            }
        }

        if ($nbSansPrix > 0) {
            $badgeClass = 'none';
        } elseif ($nbPayes === $totalDossiers) {
            $badgeClass = 'paid';
        } elseif ($nbNonPayes === $totalDossiers) {
            $badgeClass = 'none';
        } else {
            $badgeClass = 'partial';
        }
    @endphp

    <div class="client-card" data-client-id="{{ $clientId }}">

        <div class="client-header">
            <div>
                <div class="client-name">👤 {{ $client?->name ?? 'Client inconnu' }}</div>
                <div class="client-meta">
                    📞 {{ $client?->phone ?? '-' }}
                    &nbsp;·&nbsp;
                    📂 {{ $totalDossiers }} dossier(s)
                    @if($nbPayes > 0)      &nbsp;·&nbsp; ✅ {{ $nbPayes }} payé(s)      @endif
                    @if($nbPartiels > 0)   &nbsp;·&nbsp; ⏳ {{ $nbPartiels }} partiel(s) @endif
                    @if($nbNonPayes > 0)   &nbsp;·&nbsp; ❌ {{ $nbNonPayes }} non payé(s) @endif
                    @if($nbSansPrix > 0)   &nbsp;·&nbsp; ⚙️ {{ $nbSansPrix }} sans prix @endif
                </div>
            </div>
            <div class="client-badge {{ $badgeClass }}">{{ $totalDossiers }} dossier(s)</div>
        </div>

        <div class="client-body">

            @foreach($dossiersDuClient as $dossier)
                @php
                    $dejaPaye = $dossier->paiementsTechniques->sum('montant');
                    $prixRef  = $dossier->prix_technique ?? 0;
                    $reste    = max(0, $prixRef - $dejaPaye);
                    $pct      = $prixRef > 0 ? min(100, round(($dejaPaye / $prixRef) * 100)) : 0;

                    // ✅ Statut incluant 'sans_prix'
                    if ($prixRef <= 0) {
                        $statut = 'sans_prix'; $label = '⚙️ Prix non défini'; $couleur = '#7c3aed';
                    } elseif ($dejaPaye >= $prixRef) {
                        $statut = 'paye';      $label = '✅ Payé';            $couleur = '#16a34a';
                    } elseif ($dejaPaye > 0) {
                        $statut = 'partiel';   $label = '⏳ Partiel (' . $pct . '%)'; $couleur = '#f59e0b';
                    } else {
                        $statut = 'non_paye';  $label = '❌ Non payé';        $couleur = '#dc2626';
                    }
                @endphp

                <div class="dossier-row {{ $statut }}"
                     data-dossier-id="{{ $dossier->id }}"
                     data-search="{{ strtolower(
                         ($client?->name ?? '') . ' ' .
                         ($client?->phone ?? '') . ' ' .
                         ($dossier->nom_dossier ?? '')
                     ) }}"
                     data-statut="{{ $statut }}">

                    <div class="dossier-info">
                        <div class="dossier-nom">
                            📂 {{ $dossier->nom_dossier }}
                            <span class="badge-statut {{ $statut }}">{{ $label }}</span>
                        </div>
                        <div class="dossier-meta">
                            🏢 {{ $dossier->grandSite?->nom ?? '-' }}
                            &nbsp;·&nbsp;
                            📅 {{ $dossier->created_at?->format('d/m/Y') }}
                        </div>

                        @if($prixRef > 0)
                        <div class="progress-bar-mini" style="max-width:300px;">
                            <div style="width:{{ $pct }}%; background:{{ $couleur }};"></div>
                        </div>
                        @endif
                    </div>

                    <div class="dossier-montants">
                        @if($prixRef > 0)
                            <div class="montant-ref">Réf. technique</div>
                            <div class="montant-paye" style="color:{{ $couleur }};">
                                {{ number_format($dejaPaye, 0, ',', ' ') }} /
                                {{ number_format($prixRef, 0, ',', ' ') }} FCFA
                            </div>
                            @if($reste > 0)
                                <div class="montant-reste" style="color:#dc2626;">
                                    Reste : {{ number_format($reste, 0, ',', ' ') }} FCFA
                                </div>
                            @else
                                <div class="montant-reste" style="color:#16a34a;">✅ Soldé</div>
                            @endif
                        @else
                            <div class="montant-ref">Prix technique</div>
                            <div class="montant-paye" style="color:#7c3aed;">
                                Non défini
                            </div>
                        @endif
                    </div>

                    <div class="action-buttons" style="min-width:150px;">
                        @if($statut === 'sans_prix')
                            <button type="button"
                                    class="btn-prix"
                                    data-dossier-id="{{ $dossier->id }}"
                                    data-client-nom="{{ addslashes($client?->name ?? 'Client') }}"
                                    data-dossier-nom="{{ addslashes($dossier->nom_dossier ?? '') }}"
                                    onclick="ouvrirModalPrix(this)">
                                ⚙️ Définir le prix
                            </button>
                        @elseif($statut !== 'paye')
                            <button type="button"
                                    class="btn-payer"
                                    data-dossier-id="{{ $dossier->id }}"
                                    data-client-nom="{{ addslashes($client?->name ?? 'Client') }}"
                                    data-dossier-nom="{{ addslashes($dossier->nom_dossier ?? '') }}"
                                    data-prix-ref="{{ $prixRef }}"
                                    data-deja-paye="{{ $dejaPaye }}"
                                    data-reste="{{ $reste }}"
                                    onclick="ouvrirModalDepuisBouton(this)">
                                💳 Payer
                            </button>
                        @else
                            <span class="btn-payer disabled">✅ Payé</span>
                        @endif

                        <a href="{{ route('comptable.historique', $dossier->id) }}"
                           class="btn btn-outline-secondary btn-sm mt-2"
                           style="font-size:11px;width:100%;text-align:center;display:block;">
                            📜 Historique
                        </a>
                    </div>
                </div>
            @endforeach

        </div>
    </div>
@empty
    <div class="empty-state">
        <div style="font-size:48px;">📭</div>
        <div style="font-weight:700;margin-top:12px;font-size:16px;">Aucun dossier trouvé</div>
        <div style="font-size:13px;margin-top:6px;">Essayez de modifier vos filtres</div>
    </div>
@endforelse

<div class="mt-3 d-flex justify-content-center">
    {{ $dossiers->links() }}
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODAL PAIEMENT ═══════════════════════════════════════════ --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalPaiementOverlay" onclick="fermerModalPaiement()"></div>
<div class="modal-box" id="modalPaiement" style="width:520px; max-width:95%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;">💳 Paiement Technique</h5>
        <button onclick="fermerModalPaiement()"
                style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div id="modalRecap"
         style="background:linear-gradient(135deg,#eff6ff,#dbeafe);border-radius:10px;
                padding:14px;margin-bottom:16px;border-left:4px solid #1d4ed8;">
        <div style="font-weight:800;color:#1e3a5f;font-size:14px;" id="recapClient">—</div>
        <div style="font-size:12px;color:#64748b;margin-top:2px;" id="recapDossier">—</div>
        <div style="display:flex;gap:16px;margin-top:10px;flex-wrap:wrap;">
            <div>
                <div style="font-size:10px;color:#64748b;text-transform:uppercase;font-weight:700;">Référence</div>
                <div style="font-size:14px;font-weight:900;color:#1e3a5f;" id="recapRef">0</div>
            </div>
            <div>
                <div style="font-size:10px;color:#64748b;text-transform:uppercase;font-weight:700;">Déjà payé</div>
                <div style="font-size:14px;font-weight:900;color:#b45309;" id="recapPaye">0</div>
            </div>
            <div>
                <div style="font-size:10px;color:#64748b;text-transform:uppercase;font-weight:700;">Reste</div>
                <div style="font-size:14px;font-weight:900;color:#b91c1c;" id="recapReste">0</div>
            </div>
        </div>
    </div>

    <form id="formPaiementAjax" onsubmit="return soumettrePaiementAjax(event)">
        @csrf
        <input type="hidden" id="paiement_dossier_id" name="dossier_id">

        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:6px;">
                💰 Montant à payer (FCFA) <span style="color:#dc2626;">*</span>
            </label>
            <input type="number" id="paiement_montant" name="montant"
                   class="form-control form-control-lg"
                   min="1" required
                   style="font-weight:800;font-size:18px;color:#16a34a;"
                   oninput="verifierMontantModal()">
            <div style="display:flex;justify-content:space-between;margin-top:4px;">
                <span style="font-size:11px;color:#64748b;" id="montantMaxLabel"></span>
                <button type="button" onclick="remplirMontantMax()"
                        style="background:none;border:none;color:#1d4ed8;
                               font-size:11px;font-weight:700;cursor:pointer;text-decoration:underline;">
                    ⚡ Remplir le reste
                </button>
            </div>
            <div id="erreurMontantModal"
                 style="display:none;color:#dc2626;font-size:12px;font-weight:700;margin-top:4px;"></div>
        </div>

        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:6px;">
                📅 Date du paiement <span style="color:#dc2626;">*</span>
            </label>
            <input type="date" id="paiement_date" name="date_paiement"
                   class="form-control" required>
        </div>

        <div style="margin-bottom:16px;">
            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:6px;">
                📝 Note (optionnel)
            </label>
            <textarea id="paiement_note" name="note" class="form-control" rows="2"
                      placeholder="Remarque sur ce paiement..."
                      style="resize:none;font-size:13px;"></textarea>
        </div>

        <div id="messageModal"
             style="display:none;padding:10px 14px;border-radius:8px;font-size:13px;
                    margin-bottom:12px;font-weight:600;"></div>

        <div class="d-flex justify-content-end gap-2">
            <button type="button" onclick="fermerModalPaiement()"
                    class="btn btn-light btn-sm" style="font-weight:600;">Annuler</button>
            <button type="submit" id="btnSubmitModal"
                    class="btn btn-primary btn-sm px-4" style="font-weight:700;">
                💾 Enregistrer
            </button>
        </div>
    </form>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODAL DÉFINIR PRIX ═══════════════════════════════════════ --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalPrixOverlay" onclick="fermerModalPrix()"></div>
<div class="modal-box" id="modalPrix" style="width:480px; max-width:95%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#7c3aed;font-weight:800;margin:0;">⚙️ Définir le prix technique</h5>
        <button onclick="fermerModalPrix()"
                style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div style="background:linear-gradient(135deg,#faf5ff,#f3e8ff);border-radius:10px;
                padding:14px;margin-bottom:16px;border-left:4px solid #7c3aed;">
        <div style="font-weight:800;color:#1e3a5f;font-size:14px;" id="prixClient">—</div>
        <div style="font-size:12px;color:#64748b;margin-top:2px;" id="prixDossier">—</div>
    </div>

    <form id="formPrixAjax" onsubmit="return soumettrePrix(event)">
        @csrf
        <input type="hidden" id="prix_dossier_id">

        <div style="margin-bottom:14px;">
            <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:6px;">
                💰 Prix technique (FCFA) <span style="color:#dc2626;">*</span>
            </label>
            <input type="number" id="prix_montant" name="prix_technique"
                   class="form-control form-control-lg"
                   min="1" required
                   style="font-weight:800;font-size:18px;color:#7c3aed;"
                   placeholder="Ex: 350000"
                   oninput="verifierPrixModal()">
            <div style="font-size:11px;color:#64748b;margin-top:4px;">
                💡 Ce prix deviendra la référence pour tous les paiements de ce dossier.
            </div>
            <div id="erreurPrixModal"
                 style="display:none;color:#dc2626;font-size:12px;font-weight:700;margin-top:4px;"></div>
        </div>

        <div id="messagePrixModal"
             style="display:none;padding:10px 14px;border-radius:8px;font-size:13px;
                    margin-bottom:12px;font-weight:600;"></div>

        <div class="d-flex justify-content-end gap-2">
            <button type="button" onclick="fermerModalPrix()"
                    class="btn btn-light btn-sm" style="font-weight:600;">Annuler</button>
            <button type="submit" id="btnPrixSubmit"
                    class="btn btn-sm px-4"
                    style="background:#7c3aed;color:white;font-weight:700;">
                💾 Enregistrer
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
// ═══════════════════════════════════════════════════════════
// ÉTAT GLOBAL
// ═══════════════════════════════════════════════════════════
let modalDossierId  = null;
let modalMaxMontant = 0;
let prixDossierId   = null;
const CSRF_TOKEN    = '{{ csrf_token() }}';

// ═══════════════════════════════════════════════════════════
// FILTRAGE DYNAMIQUE
// ═══════════════════════════════════════════════════════════
function filtrerDossiers() {
    const recherche = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
    const rows = document.querySelectorAll('.dossier-row');

    rows.forEach(row => {
        const dataSearch = row.dataset.search || '';
        const visible = !recherche || dataSearch.includes(recherche);
        row.style.display = visible ? '' : 'none';
    });

    // Masquer les cartes vides
    document.querySelectorAll('.client-card').forEach(card => {
        const visibles = card.querySelectorAll('.dossier-row:not([style*="display: none"])').length;
        card.style.display = visibles > 0 ? '' : 'none';
    });
}

let debounceTimer;
document.getElementById('search-input')?.addEventListener('input', () => {
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(filtrerDossiers, 150);
});

// ═══════════════════════════════════════════════════════════
// MODAL PAIEMENT
// ═══════════════════════════════════════════════════════════
function ouvrirModalDepuisBouton(btn) {
    ouvrirModalPaiement(
        parseInt(btn.dataset.dossierId),
        btn.dataset.clientNom,
        btn.dataset.dossierNom,
        parseFloat(btn.dataset.prixRef) || 0,
        parseFloat(btn.dataset.dejaPaye) || 0
    );
}

function ouvrirModalPaiement(dossierId, clientNom, dossierNom, prixRef, dejaPaye) {
    modalDossierId  = dossierId;
    modalMaxMontant = Math.max(0, prixRef - dejaPaye);

    document.getElementById('recapClient').textContent  = '👤 ' + clientNom;
    document.getElementById('recapDossier').textContent = '📂 ' + dossierNom;
    document.getElementById('recapRef').textContent     = prixRef.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('recapPaye').textContent    = dejaPaye.toLocaleString('fr-FR') + ' FCFA';
    document.getElementById('recapReste').textContent   = modalMaxMontant.toLocaleString('fr-FR') + ' FCFA';

    document.getElementById('paiement_dossier_id').value = dossierId;
    document.getElementById('paiement_montant').value    = modalMaxMontant;
    document.getElementById('paiement_date').value       = new Date().toISOString().split('T')[0];
    document.getElementById('paiement_note').value       = '';
    document.getElementById('montantMaxLabel').textContent =
        'Max autorisé : ' + modalMaxMontant.toLocaleString('fr-FR') + ' FCFA';

    document.getElementById('messageModal').style.display = 'none';
    document.getElementById('erreurMontantModal').style.display = 'none';
    document.getElementById('btnSubmitModal').disabled = false;
    document.getElementById('btnSubmitModal').innerHTML = '💾 Enregistrer';

    document.getElementById('modalPaiementOverlay').classList.add('active');
    document.getElementById('modalPaiement').classList.add('active');

    setTimeout(() => document.getElementById('paiement_montant').focus(), 100);
    verifierMontantModal();
}

function fermerModalPaiement() {
    document.getElementById('modalPaiementOverlay').classList.remove('active');
    document.getElementById('modalPaiement').classList.remove('active');
    modalDossierId = null;
}

function verifierMontantModal() {
    const input  = document.getElementById('paiement_montant');
    const erreur = document.getElementById('erreurMontantModal');
    const btn    = document.getElementById('btnSubmitModal');
    const val    = parseFloat(input.value) || 0;

    if (val <= 0) {
        erreur.style.display = 'block';
        erreur.textContent = '⚠️ Le montant doit être supérieur à 0';
        input.style.borderColor = '#dc2626';
        btn.disabled = true; btn.style.opacity = '0.5';
    } else if (val > modalMaxMontant) {
        erreur.style.display = 'block';
        erreur.textContent = '⚠️ Maximum : ' + modalMaxMontant.toLocaleString('fr-FR') + ' FCFA';
        input.style.borderColor = '#dc2626';
        btn.disabled = true; btn.style.opacity = '0.5';
    } else {
        erreur.style.display = 'none';
        input.style.borderColor = '#16a34a';
        btn.disabled = false; btn.style.opacity = '1';
    }
}

function remplirMontantMax() {
    document.getElementById('paiement_montant').value = modalMaxMontant;
    verifierMontantModal();
}

function soumettrePaiementAjax(event) {
    event.preventDefault();
    const btn = document.getElementById('btnSubmitModal');
    const montant = parseFloat(document.getElementById('paiement_montant').value) || 0;
    const date    = document.getElementById('paiement_date').value;
    const note    = document.getElementById('paiement_note').value;

    if (montant <= 0 || montant > modalMaxMontant) {
        afficherMessageModal('⚠️ Montant invalide', 'error'); return false;
    }
    if (!date) {
        afficherMessageModal('⚠️ La date est obligatoire', 'error'); return false;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';

    fetch(`/admin/comptable/dossier/${modalDossierId}/payer-ajax`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ montant, date_paiement: date, note }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            afficherMessageModal('✅ ' + data.message, 'success');
            mettreAJourLigneDossier(data.data);
            setTimeout(() => {
                fermerModalPaiement();
                showToastGlobal('✅ Paiement enregistré — Bon ' + data.bon_numero, 'success');
            }, 1200);
        } else {
            afficherMessageModal('❌ ' + data.message, 'error');
            btn.disabled = false; btn.innerHTML = '💾 Enregistrer';
        }
    })
    .catch(err => {
        afficherMessageModal('❌ Erreur réseau : ' + err.message, 'error');
        btn.disabled = false; btn.innerHTML = '💾 Enregistrer';
    });

    return false;
}

function afficherMessageModal(message, type) {
    const box = document.getElementById('messageModal');
    box.style.display = 'block';
    box.textContent = message;
    box.style.background = type === 'success' ? '#dcfce7' : '#fee2e2';
    box.style.color      = type === 'success' ? '#15803d' : '#b91c1c';
    box.style.border     = type === 'success' ? '1px solid #86efac' : '1px solid #fecaca';
}

function mettreAJourLigneDossier(data) {
    const row = document.querySelector(`.dossier-row[data-dossier-id="${data.dossier_id}"]`);
    if (!row) return;

    row.classList.remove('paye', 'partiel', 'non_paye', 'sans_prix');
    row.classList.add(data.statut);
    row.dataset.statut = data.statut;

    const badge = row.querySelector('.badge-statut');
    if (badge) {
        badge.className = 'badge-statut ' + data.statut;
        badge.textContent = data.label;
    }

    const montantPaye = row.querySelector('.montant-paye');
    if (montantPaye) {
        montantPaye.textContent = data.deja_paye_fmt + ' / ' + data.prix_ref_fmt + ' FCFA';
        montantPaye.style.color = data.couleur;
    }

    const montantReste = row.querySelector('.montant-reste');
    if (montantReste) {
        if (data.reste > 0) {
            montantReste.textContent = 'Reste : ' + data.reste_fmt + ' FCFA';
            montantReste.style.color = '#dc2626';
        } else {
            montantReste.textContent = '✅ Soldé';
            montantReste.style.color = '#16a34a';
        }
    }

    const progressBar = row.querySelector('.progress-bar-mini > div');
    if (progressBar) {
        progressBar.style.width = data.pourcentage + '%';
        progressBar.style.background = data.couleur;
    }

    const actionDiv = row.querySelector('.action-buttons');
    if (actionDiv && data.statut === 'paye') {
        actionDiv.innerHTML = `
            <span class="btn-payer disabled">✅ Payé</span>
            <a href="/admin/comptable/dossier/${data.dossier_id}/historique"
               class="btn btn-outline-secondary btn-sm mt-2"
               style="font-size:11px;width:100%;text-align:center;display:block;">
                📜 Historique
            </a>`;
    }

    row.style.transition = 'background 0.5s';
    row.style.background = '#dcfce7';
    setTimeout(() => { row.style.background = ''; }, 1500);
}

// ═══════════════════════════════════════════════════════════
// MODAL DÉFINIR PRIX
// ═══════════════════════════════════════════════════════════
function ouvrirModalPrix(btn) {
    prixDossierId = parseInt(btn.dataset.dossierId);

    document.getElementById('prix_dossier_id').value   = prixDossierId;
    document.getElementById('prixClient').textContent   = '👤 ' + btn.dataset.clientNom;
    document.getElementById('prixDossier').textContent  = '📂 ' + btn.dataset.dossierNom;
    document.getElementById('prix_montant').value       = '';
    document.getElementById('messagePrixModal').style.display = 'none';
    document.getElementById('erreurPrixModal').style.display = 'none';
    document.getElementById('btnPrixSubmit').disabled = true;
    document.getElementById('btnPrixSubmit').innerHTML = '💾 Enregistrer';

    document.getElementById('modalPrixOverlay').classList.add('active');
    document.getElementById('modalPrix').classList.add('active');

    setTimeout(() => document.getElementById('prix_montant').focus(), 100);
}

function fermerModalPrix() {
    document.getElementById('modalPrixOverlay').classList.remove('active');
    document.getElementById('modalPrix').classList.remove('active');
    prixDossierId = null;
}

function verifierPrixModal() {
    const input  = document.getElementById('prix_montant');
    const erreur = document.getElementById('erreurPrixModal');
    const btn    = document.getElementById('btnPrixSubmit');
    const val    = parseFloat(input.value) || 0;

    if (val <= 0) {
        erreur.style.display = 'block';
        erreur.textContent = '⚠️ Le prix doit être supérieur à 0';
        input.style.borderColor = '#dc2626';
        btn.disabled = true; btn.style.opacity = '0.5';
    } else {
        erreur.style.display = 'none';
        input.style.borderColor = '#16a34a';
        btn.disabled = false; btn.style.opacity = '1';
    }
}

function soumettrePrix(event) {
    event.preventDefault();
    const btn = document.getElementById('btnPrixSubmit');
    const prix = parseFloat(document.getElementById('prix_montant').value) || 0;

    if (prix <= 0) {
        afficherMessagePrix('⚠️ Prix invalide', 'error'); return false;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';

    fetch(`/admin/comptable/dossier/${prixDossierId}/definir-prix`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ prix_technique: prix }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            afficherMessagePrix('✅ ' + data.message, 'success');
            setTimeout(() => {
                fermerModalPrix();
                showToastGlobal('✅ Prix technique défini', 'success');
                setTimeout(() => location.reload(), 800);
            }, 1200);
        } else {
            afficherMessagePrix('❌ ' + data.message, 'error');
            btn.disabled = false; btn.innerHTML = '💾 Enregistrer';
        }
    })
    .catch(err => {
        afficherMessagePrix('❌ Erreur : ' + err.message, 'error');
        btn.disabled = false; btn.innerHTML = '💾 Enregistrer';
    });

    return false;
}

function afficherMessagePrix(message, type) {
    const box = document.getElementById('messagePrixModal');
    box.style.display = 'block';
    box.textContent = message;
    box.style.background = type === 'success' ? '#dcfce7' : '#fee2e2';
    box.style.color      = type === 'success' ? '#15803d' : '#b91c1c';
    box.style.border     = type === 'success' ? '1px solid #86efac' : '1px solid #fecaca';
}

// ═══════════════════════════════════════════════════════════
// RACCOURCIS CLAVIER
// ═══════════════════════════════════════════════════════════
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        fermerModalPaiement();
        fermerModalPrix();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('paiement_note')?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('formPaiementAjax').dispatchEvent(new Event('submit'));
        }
    });
    document.getElementById('search-input')?.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            e.target.value = '';
            filtrerDossiers();
        }
    });
});
</script>
@endsection