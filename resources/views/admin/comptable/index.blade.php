@extends('layouts.comptable')

@section('title', 'Paiement Logistique')

@section('styles')
<style>
    .comptable-header {
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        color: white; border-radius: 14px; padding: 24px 28px;
        margin-bottom: 20px; box-shadow: 0 4px 16px rgba(124,58,237,0.25);
    }
    .comptable-header h2 { font-weight: 800; margin: 0; font-size: 22px; }
    .comptable-header .sub { font-size: 13px; opacity: 0.9; margin-top: 4px; }

    .stats-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 14px; margin-bottom: 20px;
    }
    .stat-card {
        background: white; border-radius: 12px; padding: 16px 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-left: 4px solid #7c3aed;
    }
    .stat-card.paye     { border-left-color: #16a34a; }
    .stat-card.non_paye { border-left-color: #dc2626; }
    .stat-card.partiel  { border-left-color: #f59e0b; }
    .stat-card.aucun    { border-left-color: #94a3b8; }
    .stat-card .label {
        font-size: 11px; color: #64748b; text-transform: uppercase;
        font-weight: 700; letter-spacing: 0.5px;
    }
    .stat-card .value { font-size: 24px; font-weight: 900; color: #1e3a5f; margin-top: 4px; }
    .stat-card.paye .value     { color: #16a34a; }
    .stat-card.non_paye .value { color: #dc2626; }
    .stat-card.partiel .value  { color: #f59e0b; }
    .stat-card.aucun .value    { color: #94a3b8; }

    .filtre-box {
        background: white; border-radius: 12px; padding: 16px;
        margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .filtre-box .search-input {
        font-size: 15px; padding: 10px 16px 10px 44px;
        border: 2px solid #e2e8f0; border-radius: 10px;
        width: 100%; transition: all 0.2s;
        background: #f8fafc url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='20' height='20' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cpath d='m21 21-4.35-4.35'/%3E%3C/svg%3E") no-repeat 14px center;
    }
    .filtre-box .search-input:focus {
        outline: none; border-color: #7c3aed;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(124,58,237,0.1);
    }
    .filtre-box .form-select {
        border: 2px solid #e2e8f0; border-radius: 10px;
        padding: 10px 14px; font-size: 13px; font-weight: 600;
        color: #1e3a5f; background-color: #f8fafc;
    }
    .filtre-box .form-select:focus {
        outline: none; border-color: #7c3aed;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(124,58,237,0.1);
    }

    .client-card {
        background: white; border-radius: 14px; margin-bottom: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06); overflow: hidden;
        transition: box-shadow 0.25s;
    }
    .client-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
    .client-header {
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        color: white; padding: 14px 20px;
        display: flex; justify-content: space-between; align-items: center;
        flex-wrap: wrap; gap: 10px;
    }
    .client-header .client-name { font-size: 15px; font-weight: 800; }
    .client-header .client-meta { font-size: 12px; opacity: 0.9; margin-top: 2px; }
    .client-header .client-badge {
        background: rgba(255,255,255,0.2); padding: 6px 14px;
        border-radius: 20px; font-size: 11px; font-weight: 700;
        white-space: nowrap;
    }

    .client-body { padding: 12px; }

    .btn-payer {
        background: #7c3aed; color: white; border: none;
        padding: 8px 20px; border-radius: 8px; font-weight: 700;
        font-size: 13px; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
        transition: all 0.2s; white-space: nowrap;
    }
    .btn-payer:hover { background: #6d28d9; color: white; transform: scale(1.03); }
    .btn-payer.disabled { background: #cbd5e1; cursor: not-allowed; transform: none; }

    .btn-multi {
        background: linear-gradient(135deg, #7c3aed, #a855f7); color: white; border: none;
        padding: 8px 20px; border-radius: 8px; font-weight: 700;
        font-size: 13px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: all 0.2s; white-space: nowrap;
    }
    .btn-multi:hover { background: linear-gradient(135deg, #6d28d9, #9333ea); transform: scale(1.03); }

    .btn-prix {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white; border: none;
        padding: 8px 20px; border-radius: 8px; font-weight: 700;
        font-size: 13px; cursor: pointer;
        display: inline-flex; align-items: center; gap: 6px;
        transition: all 0.2s; white-space: nowrap;
    }
    .btn-prix:hover { background: linear-gradient(135deg, #d97706, #b45309); transform: scale(1.03); }

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
</style>
@endsection

@section('content')

{{-- EN-TÊTE --}}
<div class="comptable-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2>🚗 Paiement Logistique</h2>
            <div class="sub">Gérez les paiements logistiques par bénéficiaire</div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;opacity:0.8;">Total à encaisser</div>
            <div style="font-size:20px;font-weight:900;">
                {{ number_format($stats['prix_logistique_total'] ?? 0, 0, ',', ' ') }} FCFA
            </div>
        </div>
    </div>
</div>

{{-- STATISTIQUES --}}
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
    @if(($stats['nb_aucun_frais'] ?? 0) > 0)
        <div class="stat-card aucun">
            <div class="label">⚪ Aucun frais</div>
            <div class="value">{{ $stats['nb_aucun_frais'] }}</div>
        </div>
    @endif
    <div class="stat-card paye">
        <div class="label">💵 Encaissé</div>
        <div class="value" style="font-size:18px;">
            {{ number_format($stats['paye_logistique_total'] ?? 0, 0, ',', ' ') }} FCFA
        </div>
    </div>
</div>

{{-- FILTRES --}}
<div class="filtre-box">
    <form method="GET" action="{{ route('comptable.index') }}" id="form-filtres">
        <div class="row g-2 align-items-end">
            <div class="col-md-5">
                <label style="font-size:11px;font-weight:700;color:#64748b;">
                    🔍 Recherche <small style="color:#94a3b8;font-weight:400;">(Entrée pour valider)</small>
                </label>
                <input type="text" name="q" id="search-input" class="search-input"
                       placeholder="Nom client, bénéficiaire, téléphone, dossier..."
                       value="{{ request('q') }}" autocomplete="off">
            </div>
            <div class="col-md-2">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📊 Statut</label>
                <select name="statut" id="filtre-statut" class="form-select"
                        onchange="document.getElementById('form-filtres').submit()">
                    <option value="">Tous</option>
                    <option value="paye"      {{ request('statut') == 'paye' ? 'selected' : '' }}>✅ Payés</option>
                    <option value="partiel"   {{ request('statut') == 'partiel' ? 'selected' : '' }}>⏳ Partiels</option>
                    <option value="non_paye"  {{ request('statut') == 'non_paye' ? 'selected' : '' }}>❌ Non payés</option>
                    <option value="sans_prix" {{ request('statut') == 'sans_prix' ? 'selected' : '' }}>⚪ Sans prix</option>
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

{{-- COMPTEUR RÉSULTATS --}}
<div style="font-size:12px;color:#64748b;font-weight:600;
            padding:8px 14px;background:#f1f5f9;border-radius:8px;
            display:inline-block;margin-bottom:12px;">
    📊 <strong>{{ $dossiers->count() }}</strong> dossier(s) trouvé(s)
</div>

{{-- ALERTE SANS PRIX --}}
@if(($dossiersSansPrix ?? 0) > 0)
    <div style="background:linear-gradient(135deg,#fef3c7,#fde68a);
                border-left:5px solid #f59e0b;padding:16px 20px;border-radius:12px;
                margin-bottom:16px;display:flex;justify-content:space-between;
                align-items:center;gap:16px;flex-wrap:wrap;">
        <div style="font-size:14px;color:#92400e;">
            <strong>⚠️ {{ $dossiersSansPrix }} dossier(s) sans prix logistique</strong>
            <br>
            <span style="font-size:12px;">
                Vous devez <strong>définir le prix</strong> avant de pouvoir enregistrer un paiement.
            </span>
        </div>
        <a href="{{ route('comptable.index', ['statut' => 'sans_prix']) }}" class="btn-prix">
            ⚙️ Voir ces dossiers
        </a>
    </div>
@endif

{{-- LISTE GROUPÉE PAR CLIENT --}}
@forelse($dossiersGroupes as $clientId => $dossiersDuClient)
    @php
        $client = $dossiersDuClient->first()->client;
        $totalDossiers = $dossiersDuClient->count();
    @endphp

    <div class="client-card" data-client-id="{{ $clientId }}">
        <div class="client-header">
            <div>
                <div class="client-name">👤 {{ $client?->name ?? 'Client inconnu' }}</div>
                <div class="client-meta">
                    📞 {{ $client?->phone ?? '-' }}
                    &nbsp;·&nbsp;
                    📂 {{ $totalDossiers }} dossier(s)
                </div>
            </div>
            <div class="client-badge">{{ $totalDossiers }} dossier(s)</div>
        </div>

        <div class="client-body">
            @foreach($dossiersDuClient as $dossier)
                @php
                    $totalLogi = $dossier->paiementsLogistiques->sum('montant');
                    $prixLogi  = $dossier->prix_logistique ?? 0;

                    if ($prixLogi == 0 && $totalLogi == 0) {
                        $statutLogi = 'sans_prix';
                    } elseif ($prixLogi > 0 && $totalLogi >= $prixLogi) {
                        $statutLogi = 'paye';
                    } elseif ($totalLogi > 0) {
                        $statutLogi = 'partiel';
                    } else {
                        $statutLogi = 'non_paye';
                    }

                    $hasBeneficiaires = $dossier->beneficiaires->count() > 0;
                @endphp

                <div class="dossier-card" style="background:#f8fafc;border-radius:12px;
                            padding:16px;margin-bottom:12px;border-left:4px solid #7c3aed;">

                    <div style="display:flex;justify-content:space-between;
                                align-items:center;flex-wrap:wrap;gap:10px;
                                margin-bottom:12px;">
                        <div>
                            <div style="font-weight:800;color:#1e3a5f;font-size:15px;">
                                📂 {{ $dossier->nom_dossier }}
                            </div>
                            <div style="font-size:12px;color:#64748b;">
                                🏢 {{ $dossier->grandSite?->nom ?? '-' }}
                                &nbsp;·&nbsp;
                                📅 {{ $dossier->created_at?->format('d/m/Y') }}
                            </div>
                        </div>
                    </div>

                    {{-- Total LOGISTIQUE --}}
                    <div style="background:white;border-radius:8px;padding:14px;
                                margin-bottom:12px;
                                border-left:4px solid {{ 
                                    $statutLogi === 'paye' ? '#16a34a' : 
                                    ($statutLogi === 'partiel' ? '#f59e0b' : 
                                    ($statutLogi === 'sans_prix' ? '#94a3b8' : '#dc2626')) 
                                }};">
                        <div style="font-size:11px;color:#64748b;font-weight:700;
                                    text-transform:uppercase;margin-bottom:6px;">
                            🚗 Logistique
                        </div>
                        @if($prixLogi > 0)
                            <div style="font-size:18px;font-weight:900;
                                        color:{{ 
                                            $statutLogi === 'paye' ? '#16a34a' : 
                                            ($statutLogi === 'partiel' ? '#f59e0b' : '#dc2626') 
                                        }};">
                                {{ number_format($totalLogi, 0, ',', ' ') }} /
                                {{ number_format($prixLogi, 0, ',', ' ') }} FCFA
                            </div>
                            <div style="font-size:11px;font-weight:700;
                                        color:{{ 
                                            $statutLogi === 'paye' ? '#16a34a' : 
                                            ($statutLogi === 'partiel' ? '#f59e0b' : '#dc2626') 
                                        }};margin-top:4px;">
                                @if($statutLogi === 'paye') ✅ SOLDÉ
                                @elseif($statutLogi === 'partiel')
                                    ⏳ Reste {{ number_format($prixLogi - $totalLogi, 0, ',', ' ') }} FCFA
                                @else ❌ NON PAYÉ
                                @endif
                            </div>
                        @else
                            <div style="font-size:13px;color:#94a3b8;font-weight:700;">
                                ⚪ Prix non défini
                            </div>
                            @if($totalLogi > 0)
                                <div style="font-size:14px;color:#f59e0b;font-weight:900;margin-top:4px;">
                                    💵 {{ number_format($totalLogi, 0, ',', ' ') }} FCFA versés
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Bénéficiaires --}}
                    @if($hasBeneficiaires)
                        <div style="margin-bottom:12px;">
                            <div style="font-size:11px;color:#64748b;font-weight:700;
                                        text-transform:uppercase;margin-bottom:8px;">
                                👥 Bénéficiaires ({{ $dossier->beneficiaires->count() }})
                            </div>

                            @foreach($dossier->beneficiaires as $benef)
                                @php
                                    $payeLogiB = \App\Models\BeneficiairePaiement::where('beneficiaire_id', $benef->id)
                                        ->where('paiement_type', 'logistique')->sum('montant');
                                @endphp

                                <div style="background:white;border-radius:8px;
                                            padding:10px 14px;margin-bottom:6px;
                                            display:flex;justify-content:space-between;
                                            align-items:center;flex-wrap:wrap;gap:8px;
                                            border-left:3px solid #a855f7;">
                                    <div>
                                        <div style="font-weight:700;color:#1e3a5f;font-size:13px;">
                                            👤 {{ $benef->nom }}
                                        </div>
                                        <div style="font-size:11px;color:#64748b;margin-top:2px;">
                                            @if($payeLogiB > 0)
                                                🚗 Logi : <strong style="color:#16a34a;">{{ number_format($payeLogiB, 0, ',', ' ') }}</strong> FCFA
                                            @else
                                                <span style="color:#94a3b8;">Aucun paiement</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    {{-- Actions --}}
                    <div style="display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap;">
                        @if(!$hasBeneficiaires)
                            <span style="background:#fef3c7;color:#92400e;
                                         padding:8px 14px;border-radius:8px;
                                         font-size:12px;font-weight:600;">
                                ⚠️ Aucun bénéficiaire
                            </span>
                        @elseif($prixLogi <= 0)
                            {{-- ✅ Prix non défini → OBLIGER à le définir --}}
                            <span style="background:#fee2e2;color:#b91c1c;
                                         padding:8px 14px;border-radius:8px;
                                         font-size:12px;font-weight:700;
                                         display:inline-flex;align-items:center;gap:6px;">
                                ❌ Prix non défini
                            </span>
                            <button type="button" class="btn-prix"
                                    onclick="ouvrirModalPrix({{ $dossier->id }}, '{{ addslashes($dossier->nom_dossier) }}')">
                                ⚙️ Définir le prix
                            </button>
                        @else
                            {{-- ✅ Prix défini → autoriser le paiement --}}
                            <button type="button" class="btn-multi"
                                    onclick="ouvrirModalMulti({{ $dossier->id }}, '{{ addslashes($client?->name) }}', '{{ addslashes($dossier->nom_dossier) }}')">
                                💳 Paiement logistique
                            </button>
                        @endif

                        <a href="{{ route('comptable.historique', $dossier->id) }}"
                           class="btn btn-outline-secondary btn-sm"
                           style="padding:8px 14px;font-weight:600;">
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

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODALE PAIEMENT LOGISTIQUE                               --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalMultiOverlay" onclick="fermerModalMulti()"></div>
<div class="modal-box" id="modalMulti" style="width:700px; max-width:95%; max-height:90vh; overflow-y:auto;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#7c3aed;font-weight:800;margin:0;">🚗 Paiement Logistique</h5>
        <button onclick="fermerModalMulti()"
                style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div id="multiRecap"
         style="background:linear-gradient(135deg,#faf5ff,#f3e8ff);border-radius:10px;
                padding:14px;margin-bottom:16px;border-left:4px solid #7c3aed;">
        <div style="font-weight:800;color:#1e3a5f;font-size:14px;" id="multiClient">—</div>
        <div style="font-size:12px;color:#64748b;margin-top:2px;" id="multiDossier">—</div>
        <div style="margin-top:10px;">
            <div style="font-size:10px;color:#64748b;text-transform:uppercase;font-weight:700;">🚗 Logistique</div>
            <div style="font-size:16px;font-weight:900;color:#7c3aed;" id="multiRecapLogi">—</div>
        </div>
    </div>

    <div id="versementsList" style="margin-bottom:16px;"></div>

    <button type="button" onclick="ajouterVersement()"
            style="background:#faf5ff;color:#7c3aed;border:2px dashed #c4b5fd;
                   padding:10px 16px;border-radius:8px;font-weight:700;
                   font-size:13px;cursor:pointer;width:100%;margin-bottom:16px;">
        ➕ Ajouter un versement
    </button>

    <div style="margin-bottom:14px;">
        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:6px;">
            📅 Date du paiement <span style="color:#dc2626;">*</span>
        </label>
        <input type="date" id="multi_date" class="form-control" required>
    </div>

    <div style="margin-bottom:16px;">
        <label style="font-size:12px;font-weight:700;color:#64748b;display:block;margin-bottom:6px;">
            📝 Note (optionnel)
        </label>
        <textarea id="multi_note" class="form-control" rows="2"
                  placeholder="Remarque..." style="resize:none;"></textarea>
    </div>

    <div style="background:#faf5ff;border-radius:10px;padding:14px;
                margin-bottom:16px;display:flex;justify-content:space-between;
                align-items:center;border-left:4px solid #7c3aed;">
        <span style="font-weight:700;color:#6b21a8;">💰 TOTAL À VERSER :</span>
        <span style="font-size:20px;font-weight:900;color:#7c3aed;" id="multiTotal">0 FCFA</span>
    </div>

    <div id="multiMessage"
         style="display:none;padding:10px 14px;border-radius:8px;
                font-size:13px;margin-bottom:12px;font-weight:600;"></div>

    <div class="d-flex justify-content-end gap-2">
        <button type="button" onclick="fermerModalMulti()" class="btn btn-light">Annuler</button>
        <button type="button" id="btnMultiSubmit" onclick="soumettreMulti()"
                class="btn px-4" style="background:#7c3aed;color:white;font-weight:700;">
            💾 Enregistrer les paiements
        </button>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODALE DÉFINIR LE PRIX LOGISTIQUE                        --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modalPrixOverlay" onclick="fermerModalPrix()"></div>
<div class="modal-box" id="modalPrix" style="width:500px; max-width:95%;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#f59e0b;font-weight:800;margin:0;">⚙️ Définir le prix logistique</h5>
        <button onclick="fermerModalPrix()"
                style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div style="background:#fef3c7;border-radius:8px;padding:12px 14px;margin-bottom:16px;
                font-size:12px;color:#92400e;border-left:3px solid #f59e0b;">
        ℹ️ Ce dossier n'a pas encore de prix logistique. Vous devez le définir
        <strong>avant de pouvoir enregistrer un paiement</strong>.
    </div>

    <div style="background:#faf5ff;border-radius:10px;padding:12px 14px;margin-bottom:14px;">
        <div style="font-weight:800;color:#1e3a5f;font-size:14px;" id="prixDossier">—</div>
    </div>

    <label style="font-size:12px;font-weight:700;color:#64748b;">
        💰 Prix logistique (FCFA) <span style="color:#dc2626;">*</span>
    </label>
    <input type="number" id="prix_logistique_input" class="form-control"
           placeholder="Ex: 50000" min="1" step="500"
           style="margin-top:6px;font-size:16px;font-weight:700;padding:12px;">

    <div id="prixMessage"
         style="display:none;padding:10px 14px;border-radius:8px;
                font-size:13px;margin-top:12px;font-weight:600;"></div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" onclick="fermerModalPrix()" class="btn btn-light">Annuler</button>
        <button type="button" onclick="soumettrePrix()" id="btnPrixSubmit"
                class="btn px-4" style="background:#f59e0b;color:white;font-weight:700;">
            💾 Enregistrer le prix
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF_TOKEN = '{{ csrf_token() }}';

// ═══════════════════════════════════════════════════════════
// RECHERCHE — soumettre uniquement sur Entrée
// ═══════════════════════════════════════════════════════════
const searchInput = document.getElementById('search-input');
if (searchInput) {
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('form-filtres').submit();
        }
        if (e.key === 'Escape') {
            this.value = '';
            document.getElementById('form-filtres').submit();
        }
    });

    if (searchInput.value) {
        setTimeout(() => {
            searchInput.focus();
            searchInput.setSelectionRange(searchInput.value.length, searchInput.value.length);
        }, 100);
    }
}

// ═══════════════════════════════════════════════════════════
// MODALE PAIEMENT LOGISTIQUE
// ═══════════════════════════════════════════════════════════
let multiDossierId = null;
let multiBeneficiaires = [];
let multiDossierInfo = {};
let versementCounter = 0;

function ouvrirModalMulti(dossierId, clientNom, dossierNom) {
    multiDossierId = dossierId;
    document.getElementById('multiClient').textContent = '👤 ' + clientNom;
    document.getElementById('multiDossier').textContent = '📂 ' + dossierNom;
    document.getElementById('multi_date').value = new Date().toISOString().split('T')[0];
    document.getElementById('multi_note').value = '';
    document.getElementById('versementsList').innerHTML = '';
    document.getElementById('multiMessage').style.display = 'none';
    versementCounter = 0;

    fetch(`/admin/comptable/dossier/${dossierId}/beneficiaires`, {
        headers: { 'Accept': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // ✅ Double vérification client : prix défini ?
            if ((data.dossier.prix_logistique ?? 0) <= 0) {
                alert('❌ Le prix logistique doit être défini avant de pouvoir payer.');
                fermerModalMulti();
                return;
            }

            multiBeneficiaires = data.beneficiaires;
            multiDossierInfo = data.dossier;

            const recapLogi = data.dossier.total_logi.toLocaleString('fr-FR') + ' / ' +
                              data.dossier.prix_logistique.toLocaleString('fr-FR') + ' FCFA';

            document.getElementById('multiRecapLogi').textContent = recapLogi;
            ajouterVersement();
        } else {
            alert('❌ Erreur : ' + data.message);
        }
    })
    .catch(err => alert('❌ Erreur réseau : ' + err.message));

    document.getElementById('modalMultiOverlay').classList.add('active');
    document.getElementById('modalMulti').classList.add('active');
}

function fermerModalMulti() {
    document.getElementById('modalMultiOverlay').classList.remove('active');
    document.getElementById('modalMulti').classList.remove('active');
    multiDossierId = null;
}

function ajouterVersement() {
    if (multiBeneficiaires.length === 0) {
        alert('⚠️ Aucun bénéficiaire pour ce dossier');
        return;
    }

    versementCounter++;
    const id = versementCounter;

    const benefOptions = multiBeneficiaires.map(b =>
        `<option value="${b.id}">${b.nom}${b.telephone ? ' — ' + b.telephone : ''}</option>`
    ).join('');

    const html = `
        <div class="versement-item" data-id="${id}"
             style="background:#faf5ff;border:2px solid #e9d5ff;
                    border-radius:10px;padding:14px;margin-bottom:10px;">
            <div style="display:flex;justify-content:space-between;
                        align-items:center;margin-bottom:10px;">
                <span style="font-weight:800;color:#7c3aed;font-size:12px;">VERSEMENT #${id}</span>
                <button type="button" onclick="supprimerVersement(${id})"
                        style="background:#fee2e2;color:#dc2626;border:none;
                               padding:4px 10px;border-radius:6px;font-weight:700;
                               font-size:11px;cursor:pointer;">✖</button>
            </div>

            <div style="margin-bottom:10px;">
                <label style="font-size:11px;font-weight:700;color:#64748b;">
                    👤 Bénéficiaire <span style="color:#dc2626;">*</span>
                </label>
                <select class="form-control form-control-sm benef-select"
                        data-id="${id}" style="margin-top:4px;"
                        onchange="majTotalMulti()">
                    <option value="">— Choisir un bénéficiaire —</option>
                    ${benefOptions}
                </select>
            </div>

            <div>
                <label style="font-size:11px;font-weight:700;color:#7c3aed;">
                    💵 Montant Logistique (FCFA) <span style="color:#dc2626;">*</span>
                </label>
                <input type="number" class="form-control form-control-sm montant-input"
                       data-id="${id}" min="1" placeholder="Ex: 50000"
                       oninput="majTotalMulti()">
            </div>
        </div>
    `;

    document.getElementById('versementsList').insertAdjacentHTML('beforeend', html);
    majTotalMulti();
}

function supprimerVersement(id) {
    const el = document.querySelector(`.versement-item[data-id="${id}"]`);
    if (el) { el.remove(); majTotalMulti(); }
}

function majTotalMulti() {
    let total = 0;
    document.querySelectorAll('.versement-item').forEach(item => {
        total += parseFloat(item.querySelector('.montant-input')?.value || 0) || 0;
    });
    document.getElementById('multiTotal').textContent = total.toLocaleString('fr-FR') + ' FCFA';
}

function soumettreMulti() {
    const btn = document.getElementById('btnMultiSubmit');
    const date = document.getElementById('multi_date').value;
    const note = document.getElementById('multi_note').value;

    if (!date) { afficherMessageMulti('⚠️ La date est obligatoire', 'error'); return; }

    const versements = [];
    for (const item of document.querySelectorAll('.versement-item')) {
        const benefId = item.querySelector('.benef-select')?.value;
        const montant = parseFloat(item.querySelector('.montant-input')?.value || 0) || 0;

        if (!benefId) { afficherMessageMulti('⚠️ Sélectionnez un bénéficiaire', 'error'); return; }
        if (montant <= 0) { afficherMessageMulti('⚠️ Montant > 0 obligatoire', 'error'); return; }

        versements.push({
            beneficiaire_id: parseInt(benefId),
            type: 'logistique',
            montant: montant,
        });
    }

    if (versements.length === 0) { afficherMessageMulti('⚠️ Ajoutez un versement', 'error'); return; }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Enregistrement...';

    fetch(`/admin/comptable/dossier/${multiDossierId}/payer-multi`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ versements, date_paiement: date, note: note }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            afficherMessageMulti('✅ ' + data.message, 'success');
            setTimeout(() => {
                fermerModalMulti();
                location.reload();
            }, 1500);
        } else {
            afficherMessageMulti('❌ ' + data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '💾 Enregistrer les paiements';
        }
    })
    .catch(err => {
        afficherMessageMulti('❌ Erreur : ' + err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '💾 Enregistrer les paiements';
    });
}

function afficherMessageMulti(message, type) {
    const box = document.getElementById('multiMessage');
    box.style.display = 'block';
    box.textContent = message;
    box.style.background = type === 'success' ? '#dcfce7' : '#fee2e2';
    box.style.color      = type === 'success' ? '#15803d' : '#b91c1c';
    box.style.border     = type === 'success' ? '1px solid #86efac' : '1px solid #fecaca';
}

// ═══════════════════════════════════════════════════════════
// MODALE DÉFINIR LE PRIX
// ═══════════════════════════════════════════════════════════
let prixDossierId = null;

function ouvrirModalPrix(dossierId, dossierNom) {
    prixDossierId = dossierId;
    document.getElementById('prixDossier').textContent = '📂 ' + dossierNom;
    document.getElementById('prix_logistique_input').value = '';
    document.getElementById('prixMessage').style.display = 'none';

    document.getElementById('modalPrixOverlay').classList.add('active');
    document.getElementById('modalPrix').classList.add('active');

    setTimeout(() => document.getElementById('prix_logistique_input').focus(), 100);
}

function fermerModalPrix() {
    document.getElementById('modalPrixOverlay').classList.remove('active');
    document.getElementById('modalPrix').classList.remove('active');
    prixDossierId = null;
}

function soumettrePrix() {
    const prix = parseFloat(document.getElementById('prix_logistique_input').value);

    if (!prix || prix <= 0) {
        afficherMessagePrix('⚠️ Entrez un prix valide (> 0)', 'error');
        return;
    }

    const btn = document.getElementById('btnPrixSubmit');
    btn.disabled = true;
    btn.innerHTML = '⏳ Enregistrement...';

    fetch(`/admin/comptable/dossier/${prixDossierId}/definir-prix`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF_TOKEN,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ prix_logistique: prix }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            afficherMessagePrix('✅ ' + data.message, 'success');
            setTimeout(() => {
                fermerModalPrix();
                location.reload();
            }, 1200);
        } else {
            afficherMessagePrix('❌ ' + data.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '💾 Enregistrer le prix';
        }
    })
    .catch(err => {
        afficherMessagePrix('❌ Erreur réseau : ' + err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '💾 Enregistrer le prix';
    });
}

function afficherMessagePrix(message, type) {
    const box = document.getElementById('prixMessage');
    box.style.display = 'block';
    box.textContent = message;
    box.style.background = type === 'success' ? '#dcfce7' : '#fee2e2';
    box.style.color      = type === 'success' ? '#15803d' : '#b91c1c';
    box.style.border     = type === 'success' ? '1px solid #86efac' : '1px solid #fecaca';
}

// ═══════════════════════════════════════════════════════════
// FERMER LES MODALES AVEC ESC
// ═══════════════════════════════════════════════════════════
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        fermerModalMulti();
        fermerModalPrix();
    }
});
</script>
@endsection