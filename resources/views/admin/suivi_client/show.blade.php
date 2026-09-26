@extends('admin.layout')
@section('content')

<style>
.section-card { background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:20px; margin-bottom:16px; }
.section-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px; }
.info-row { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
.info-row span:first-child { color:#64748b; }
.info-row span:last-child  { font-weight:600; color:#1e3a5f; }
.pay-row { display:flex; justify-content:space-between; font-size:12px; padding:4px 0; border-bottom:1px solid #f8fafc; }
.pay-amt { font-weight:700; }
.prog-wrap { height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden; margin-top:4px; }
.prog-fill  { height:100%; border-radius:4px; }
.dossier-tab { cursor:pointer; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600; background:#f1f5f9; color:#64748b; }
.dossier-tab.active { background:#0d6efd; color:white; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:400px; }

/* ✅ Blocs de paiement alignés */
.paiement-blocs { display:grid; grid-template-columns:repeat(3, 1fr); gap:14px; margin-top:16px; }
.paiement-bloc {
    border-radius:12px; padding:16px;
    border:1px solid #e2e8f0;
    display:flex; flex-direction:column;
}
.paiement-bloc.bloc-dossier   { background:#eff6ff; border-left:4px solid #0d6efd; }
.paiement-bloc.bloc-technique { background:#fff7ed; border-left:4px solid #ea580c; }
.paiement-bloc.bloc-morcel    { background:#fefce8; border-left:4px solid #ca8a04; }

.paiement-bloc h6 { font-weight:700; font-size:13px; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; }
.btn-add-pay {
    border:none; color:white; border-radius:8px;
    padding:5px 12px; font-size:11px; font-weight:600; cursor:pointer;
}
.btn-add-pay.dossier   { background:#0d6efd; }
.btn-add-pay.technique { background:#ea580c; }
.btn-add-pay.morcel    { background:#ca8a04; }

.pay-total-row { display:flex; justify-content:space-between; font-size:12px; padding:4px 0; }
.pay-historique { margin-top:10px; max-height:160px; overflow-y:auto; }

/* ═══ BADGE NOUVEAU ═══ */
.badge-new {
    background: #10b981;
    color: #fff;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    animation: pulse-new 2s ease-in-out infinite;
    display: inline-block;
    margin-left: 10px;
}

.badge-old {
    background: #e2e8f0;
    color: #64748b;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
    margin-left: 10px;
}

@keyframes pulse-new {
    0%, 100% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.05); opacity: 0.8; }
}

.btn-toggle-new {
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    cursor: pointer;
    transition: all 0.2s;
    font-weight: 600;
}

.btn-toggle-new:hover { background: #f1f5f9; }
.btn-toggle-new.is-new {
    background: #10b981;
    color: #fff;
    border-color: #10b981;
}
.btn-toggle-new.is-new:hover { background: #059669; }

/* Toast notification */
.toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #1f2937;
    color: #fff;
    padding: 12px 20px;
    border-radius: 8px;
    font-size: 14px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
    z-index: 9999;
    max-width: 400px;
    animation: slideInToast 0.3s ease;
}

@keyframes slideInToast {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

@media (max-width: 992px) {
    .paiement-blocs { grid-template-columns:1fr; }
}
</style>


<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('suivi-client.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
        <h2 class="d-inline ms-2">
            👤 {{ $client->name }}
            
            {{-- ═══ BADGE NOUVEAU ═══ --}}
            @if($client->is_new)
                <span class="badge-new" id="badge-detail-{{ $client->id }}">
                    🆕 Nouveau
                </span>
            @else
                <span class="badge-old" id="badge-detail-{{ $client->id }}">
                    Ancien
                </span>
            @endif
        </h2>
    </div>
    <div class="d-flex gap-2">
        {{-- ═══ BOUTON TOGGLE NEW ═══ --}}
        <button class="btn-toggle-new {{ $client->is_new ? 'is-new' : '' }}"
                onclick="toggleNew({{ $client->id }})"
                id="btn-new-detail-{{ $client->id }}">
            @if($client->is_new)
                ✅ Nouveau
            @else
                🔄 Marquer nouveau
            @endif
        </button>
        @if($dossier)
            <a href="{{ route('suivi-client.create') }}?client_id={{ $client->id }}"
               class="btn btn-primary btn-sm">📂 Nouveau dossier</a>
        @endif
        <a href="{{ route('suivi-client.edit', $client->id) }}" class="btn btn-warning btn-sm">✏️ Modifier</a>
    </div>
</div>

<div class="row g-3">

    {{-- INFOS CLIENT --}}
    <div class="col-md-4">
        <div class="section-card">
            <h5>📋 Informations client</h5>
            <div class="info-row"><span>Téléphone</span><span>{{ $client->phone ?? '-' }}</span></div>
            <div class="info-row"><span>Lots attribués</span><span>{{ $client->lots->count() }}</span></div>
            <div class="info-row"><span>Dossiers</span><span>{{ $client->dossiers->count() }}</span></div>
            <div class="info-row">
                <span>Statut</span>
                <span>
                    @if($client->is_new)
                        <span style="color:#10b981;font-weight:700;">🆕 Nouveau client</span>
                    @else
                        <span style="color:#64748b;">Client existant</span>
                    @endif
                </span>
            </div>
            <div class="info-row"><span>Date création</span><span>{{ $client->created_at?->format('d/m/Y H:i') ?? '-' }}</span></div>
        </div>

        {{-- ✅ LOTS AFFECTÉS AU DOSSIER (GROUPÉS PAR BLOC) --}}
@if($affectations->count() > 0)
<div style="background:white;border-radius:12px;padding:16px;margin-top:14px;border-left:4px solid #16a34a;">
    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:10px;">
        📦 Lots affectés au dossier ({{ $affectations->count() }})
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
        @php
            $groupes = $affectations->groupBy(function($aff) {
                return $aff->bloc_id . '-' . $aff->date_affectation?->format('Y-m-d');
            });
        @endphp

        @foreach($groupes as $groupe)
            @php
                $premier = $groupe->first();
                $nbLots = $groupe->count();
                $lotsList = $groupe->pluck('lot.numero')->implode(', ');
                $ids = $groupe->pluck('id')->implode(',');
            @endphp
            <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;padding:6px 12px;display:inline-flex;align-items:center;gap:8px;" id="groupe-{{ $premier->id }}">
                <span style="font-weight:600;font-size:12px;color:#1e3a5f;">
                    {{ $premier->grandSite?->nom }}
                    — Bloc <strong>{{ $premier->bloc?->code }}</strong>
                    — (Lot {{ $lotsList }})
                </span>
                <span style="font-size:10px;color:#64748b;">
                    📅 {{ $premier->date_affectation?->format('d/m/Y') }}
                </span>
                <span style="font-size:10px;color:#94a3b8;background:#f1f5f9;padding:0 6px;border-radius:4px;">
                    {{ $nbLots }} lot(s)
                </span>
                <button onclick="annulerGroupeAffectation('{{ $ids }}', this)"
                        style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:12px;padding:0 4px;"
                        title="Annuler toutes les affectations du groupe">✕</button>
            </div>
        @endforeach
    </div>
</div>
@endif

{{-- ✅ FORMULAIRE AFFECTATION LINÉAIRE AU DOSSIER --}}
@if($dossier)
<div style="background:#f8fafc;border-radius:12px;padding:16px;margin-top:14px;border:1px solid #e2e8f0;">
    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:12px;">
        ➕ Affecter des lots au dossier
    </div>
    
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label style="font-size:10px;font-weight:600;color:#374151;">Grand Site</label>
            <select class="form-control form-control-sm" id="aff-gs-{{ $dossier->id }}"
                    onchange="affChargerSites(this.value, {{ $dossier->id }})">
                <option value="">-- Choisir --</option>
                @foreach(\App\Models\GrandSite::orderBy('nom')->get() as $gs)
                    <option value="{{ $gs->id }}">{{ $gs->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:10px;font-weight:600;color:#374151;">Site</label>
            <select class="form-control form-control-sm" id="aff-site-{{ $dossier->id }}"
                    onchange="affChargerTfs(this.value, {{ $dossier->id }})">
                <option value="">-- Choisir --</option>
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:10px;font-weight:600;color:#374151;">TF</label>
            <select class="form-control form-control-sm" id="aff-tf-{{ $dossier->id }}"
                    onchange="affChargerBlocs(this.value, {{ $dossier->id }})">
                <option value="">-- Choisir --</option>
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:10px;font-weight:600;color:#374151;">Bloc</label>
            <select class="form-control form-control-sm" id="aff-bloc-{{ $dossier->id }}"
                    onchange="affChargerLots(this.value, {{ $dossier->id }})">
                <option value="">-- Choisir --</option>
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:10px;font-weight:600;color:#374151;">Date</label>
            <input type="date" class="form-control form-control-sm" id="aff-date-{{ $dossier->id }}"
                   value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-md-2">
            <label style="font-size:10px;font-weight:600;color:#374151;">Notes</label>
            <input type="text" class="form-control form-control-sm" id="aff-notes-{{ $dossier->id }}"
                   placeholder="Optionnel">
        </div>
    </div>

    <div style="margin-top:10px;" id="aff-lots-container-{{ $dossier->id }}">
        <div style="color:#94a3b8;font-size:12px;padding:4px 0;">
            ℹ️ Sélectionnez un Grand Site, Site, TF et Bloc pour voir les lots disponibles
        </div>
    </div>

    <div style="margin-top:8px;display:flex;gap:10px;align-items:center;">
        <button onclick="validerAffectation({{ $dossier->id }})"
                class="btn btn-success btn-sm" id="aff-btn-{{ $dossier->id }}" disabled>
            ✅ Affecter les lots sélectionnés
        </button>
        <span style="font-size:11px;color:#94a3b8;" id="aff-compteur-{{ $dossier->id }}">
            0 lot(s) sélectionné(s)
        </span>
    </div>
</div>
@endif

{{-- ============================================================
     BLOC ÉTAPES DU DOSSIER
     ============================================================ --}}
@if($dossier)
@php
    $etapesConfig = \App\Models\DossierClient::etapesConfig();
    $etapesOrdre  = \App\Models\DossierClient::etapesOrdre();
    $etapeActuelle= $dossier->etape_actuelle;
@endphp

<div style="background:#f8fafc;border-radius:12px;padding:16px;margin-top:14px;border:1px solid #e2e8f0;">
    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:10px;">
        📊 Étapes du dossier
    </div>

    <div style="display:flex;gap:8px;flex-wrap:wrap;">
        @foreach($etapesConfig as $cle => $cfg)
        @php
            $ordreEtape  = $etapesOrdre[$cle];
            $ordreActuel = $etapeActuelle ? ($etapesOrdre[$etapeActuelle] ?? 0) : 0;
            $estFait     = $ordreEtape <= $ordreActuel;
            $champ       = $cfg['champ'];
            $dateEtape   = $dossier->$champ;
        @endphp
        <div onclick="ouvrirModalEtape({{ $dossier->id }}, '{{ $cle }}', '{{ $cfg['label'] }}', {{ $estFait ? 'true' : 'false' }}, '{{ $dateEtape ? \Carbon\Carbon::parse($dateEtape)->format('Y-m-d') : '' }}')"
             class="etape-card-{{ $cle }}"
             style="
                display:flex;align-items:center;gap:6px;
                padding:8px 16px;border-radius:20px;
                border:2px solid {{ $estFait ? $cfg['color'] : '#e2e8f0' }};
                background:{{ $estFait ? $cfg['bg'] : 'white' }};
                font-size:12px;font-weight:700;color:{{ $estFait ? $cfg['color'] : '#94a3b8' }};
                cursor:pointer;transition:all 0.3s;
                {{ $estFait ? '' : 'opacity:0.7;' }}
                box-shadow: {{ $estFait ? '0 2px 8px rgba(0,0,0,0.06)' : 'none' }};
            "
            onmouseover="this.style.transform='scale(1.02)'"
            onmouseout="this.style.transform='scale(1)'"
            title="Cliquez pour {{ $estFait ? 'modifier' : 'définir' }} la date">
            <span style="font-size:16px;">{{ $cfg['icon'] }}</span>
            {{ $cfg['label'] }}
            @if($estFait && $dateEtape)
                <span style="font-size:10px;font-weight:400;color:#64748b;background:white;padding:0 10px;border-radius:10px;border:1px solid #e2e8f0;">
                    {{ \Carbon\Carbon::parse($dateEtape)->format('d/m/Y') }}
                </span>
                <span style="color:#16a34a;">✓</span>
            @else
                <span style="font-size:10px;font-weight:400;color:#94a3b8;">(à définir)</span>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

    </div>

    {{-- DOSSIERS CLIENT --}}
    <div class="col-md-8">
        @if($client->dossiers->count() > 1)
        <div class="d-flex gap-2 mb-3 flex-wrap">
            @foreach($client->dossiers as $i => $d)
                <div class="dossier-tab {{ $i === 0 ? 'active' : '' }}"
                     onclick="showDossier('dossier-{{ $d->id }}', this)">
                    {{ $d->nom_dossier }}
                </div>
            @endforeach
        </div>
        @endif

        @if($dossier)
            @foreach($client->dossiers as $i => $dossier)
            <div id="dossier-{{ $dossier->id }}" class="dossier-panel" style="{{ $i > 0 ? 'display:none;' : '' }}">

                <div class="section-card">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0">📂 {{ $dossier->nom_dossier }}</h5>
                        <div class="d-flex gap-1">
                            <a href="{{ route('bons.index', $dossier->id) }}"
                               class="btn btn-outline-primary btn-sm" style="font-size:11px;">
                                🧾 {{ $dossier->bons->count() }} bon(s)
                            </a>
                            <a href="#" 
                               class="btn btn-primary btn-sm" style="font-size:11px;"
                               onclick="demanderReferenceCreate({{ $dossier->id }})">
                                + Nouveau paiement
                            </a>
                            <form action="{{ route('suivi-client.dossiers.destroy', $dossier->id) }}"
                                  method="POST"
                                  onsubmit="return confirm('Supprimer ce dossier ?')"
                                  style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-danger btn-sm">🗑 Supprimer</button>
                            </form>
                        </div>
                    </div>

                    <div class="info-row"><span>🔗 Facilitateur</span>
                        <span>{{ $dossier->facilitateur?->nom ?? '-' }}
                            @if($dossier->facilitateur?->numero) ({{ $dossier->facilitateur->numero }}) @endif
                        </span>
                    </div>
                    <div class="info-row"><span>🧭 Direction</span>
                        <span>{{ match($dossier->direction) { 'baffoussam'=>'Baffoussam','bagante'=>'Bagante','direction_generale'=>'Direction Générale',default=>'-' } }}</span>
                    </div>
                    <div class="info-row">
                        <span>📎 CNI</span>
                        <span>
                            @php
                                $cnis = $dossier->cni_images ?? [];
                            @endphp
                            @if(count($cnis) > 0)
                                <div style="display:flex; gap:4px; flex-wrap:wrap; align-items:center;">
                                    @foreach($cnis as $img)
                                        <a href="{{ asset('storage/' . $img) }}" target="_blank" 
                                           style="display:inline-block; width:30px; height:30px; border-radius:4px; overflow:hidden; border:1px solid #e2e8f0;">
                                            <img src="{{ asset('storage/' . $img) }}" 
                                                 alt="CNI" 
                                                 style="width:100%; height:100%; object-fit:cover;">
                                        </a>
                                    @endforeach
                                    <span style="font-size:11px; color:#64748b; margin-left:4px;">
                                        ({{ count($cnis) }})
                                    </span>
                                </div>
                            @else
                                <span style="color:#94a3b8;">Aucune</span>
                            @endif
                        </span>
                    </div>
                    <div class="info-row"><span>🧑‍💼 Commercial</span>
                        <span>{{ $dossier->commercial?->name ?? '-' }}
                            @if($dossier->commercial?->phone) ({{ $dossier->commercial->phone }}) @endif
                        </span>
                    </div>
                    <div class="info-row"><span>🤝 Agent commercial</span>
                        <span>{{ $dossier->agentCommercial?->nom ?? '-' }}
                            @if($dossier->agentCommercial?->numero) ({{ $dossier->agentCommercial->numero }}) @endif
                        </span>
                    </div>
                    <div class="info-row"><span>🚗 Chauffeur</span>
                        <span>{{ $dossier->conducteur?->nom ?? '-' }}
                            @if($dossier->conducteur?->numero) ({{ $dossier->conducteur->numero }}) @endif
                        </span>
                    </div>
                    <div class="info-row"><span>🏢 Grand Site souhaité</span>
                        <span>{{ $dossier->grandSite?->nom ?? '-' }}</span>
                    </div>
                    <div class="info-row"><span>📐 Superficie voulue</span>
                        <span>{{ $dossier->superficie_voulue ? number_format($dossier->superficie_voulue, 0, ',', ' ') . ' m²' : '-' }}</span>
                    </div>
                    <div class="info-row"><span>💰 Prix superficie</span>
                        <span>{{ $dossier->prix_superficie ? number_format($dossier->prix_superficie, 0, ',', ' ') . ' FCFA' : '-' }}</span>
                    </div>
                    <div class="info-row"><span>💰 Prix technique</span>
                        <span>{{ $dossier->prix_technique ? number_format($dossier->prix_technique, 0, ',', ' ') . ' FCFA' : '-' }}</span>
                    </div>
                    <div class="info-row"><span>💰 Prix logistique</span>
                        <span>{{ $dossier->prix_logistique ? number_format($dossier->prix_logistique, 0, ',', ' ') . ' FCFA' : '-' }}</span>
                    </div>
                    <div class="info-row"><span>💰 Prix morcellement</span>
                        <span>{{ $dossier->prix_morcellement ? number_format($dossier->prix_morcellement, 0, ',', ' ') . ' FCFA' : '-' }}</span>
                    </div>

                    {{-- ============================================================
                         TROIS BLOCS DE PAIEMENT DISTINCTS
                         ============================================================ --}}

                    @php
                        $totalDossier   = $dossier->paiements->sum('montant');
                        $prixRef        = $dossier->prix_superficie    ?? 0;
                        $resteDossier   = max(0, $prixRef - $totalDossier);
                        $prixTech       = $dossier->prix_technique     ?? 0;
                        $prixMorcel     = $dossier->prix_morcellement  ?? 0;
                        $prixLogistique = $dossier->prix_logistique    ?? 0;
                        $totalTechnique = $dossier->paiementsTechniques->sum('montant');
                        $totalMorcel    = $dossier->paiementsMorcellements->sum('montant');
                        $totalLogistique= $dossier->paiementsLogistiques?->sum('montant') ?? 0;
                        $resteTech      = max(0, $prixTech - $totalTechnique);
                        $resteMorcel    = max(0, $prixMorcel - $totalMorcel);
                        $resteLogistique= max(0, $prixLogistique - $totalLogistique);
                        $affectations = $dossier->affectations ?? collect();
                    @endphp

                    {{-- Prix de référence configurables --}}
                    <div style="background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:14px;border:1px solid #e2e8f0;">
                        <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:10px;">
                            💰 Prix de référence du dossier
                        </div>
                        <div class="row g-2">

                            <div class="col-md-3">
                                <label style="font-size:11px;color:#64748b;">💰 Prix superficie (FCFA)</label>
                                <div style="display:flex;gap:6px;">
                                    <input type="number" id="prix-superficie-{{ $dossier->id }}"
                                           class="form-control form-control-sm"
                                           value="{{ $prixRef }}" placeholder="0">
                                    <button onclick="majPrix({{ $dossier->id }})"
                                            class="btn btn-primary btn-sm" style="font-size:11px;flex-shrink:0;">✓</button>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label style="font-size:11px;color:#ea580c;">🛠️ Prix technique (FCFA)</label>
                                <div style="display:flex;gap:6px;">
                                    <input type="number" id="prix-technique-{{ $dossier->id }}"
                                           class="form-control form-control-sm"
                                           value="{{ $prixTech }}" placeholder="0">
                                    <button onclick="majPrix({{ $dossier->id }})"
                                            class="btn btn-warning btn-sm" style="font-size:11px;flex-shrink:0;">✓</button>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label style="font-size:11px;color:#7c3aed;">🚗 Prix logistique (FCFA)</label>
                                <div style="display:flex;gap:6px;">
                                    <input type="number" id="prix-logistique-{{ $dossier->id }}"
                                           class="form-control form-control-sm"
                                           value="{{ $prixLogistique ?? $dossier->prix_logistique ?? 0 }}" placeholder="0">
                                    <button onclick="majPrix({{ $dossier->id }})"
                                            class="btn btn-sm" style="background:#7c3aed;color:white;font-size:11px;flex-shrink:0;">✓</button>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <label style="font-size:11px;color:#ca8a04;">Prix morcellement (FCFA)</label>
                                <div style="display:flex;gap:6px;">
                                    <input type="number" id="prix-morcellement-{{ $dossier->id }}"
                                           class="form-control form-control-sm"
                                           value="{{ $prixMorcel }}" placeholder="0">
                                    <button onclick="majPrix({{ $dossier->id }})"
                                            class="btn btn-sm" style="background:#ca8a04;color:white;font-size:11px;flex-shrink:0;">✓</button>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- 3 BLOCS PAIEMENTS --}}
                    <div class="paiement-blocs">

                        {{-- BLOC DOSSIER --}}
                        <div class="paiement-bloc bloc-dossier">
                            <h6 style="color:#0d6efd;">
                                📁 Paiement Parcelle
                            </h6>
                            @if($prixRef > 0)
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Référence</span>
                                <span style="font-weight:600;">{{ number_format($prixRef, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endif
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Payé</span>
                                <span class="pay-amt" style="color:#16a34a;">{{ number_format($totalDossier, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Reste</span>
                                <span style="font-weight:700;color:{{ $resteDossier > 0 ? '#dc2626' : '#16a34a' }};">
                                    {{ number_format($resteDossier, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                            @if($prixRef > 0)
                            <div style="height:6px;background:#e2e8f0;border-radius:3px;margin:8px 0;">
                                @php $pct = $prixRef > 0 ? min(100, round(($totalDossier/$prixRef)*100)) : 0; @endphp
                                <div style="width:{{ $pct }}%;height:100%;background:#0d6efd;border-radius:3px;"></div>
                            </div>
                            <div style="font-size:10px;text-align:right;color:#0d6efd;font-weight:700;">{{ $pct }}%</div>
                            @endif
                            <div class="pay-historique">
                                @forelse($dossier->paiements->sortByDesc('date_paiement') as $p)
                                <div class="pay-row" style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
                        
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;">
                                    {{ $p->date_paiement }}
                                </div>

                                @if($p->note)
                                    <div style="font-size:11px;color:#64748b;word-break:break-word;white-space:normal;">
                                        {{ $p->note }}
                                    </div>
                                @endif
                            </div>

                            <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                <span class="pay-amt" style="color:#16a34a;font-weight:700;">
                                    {{ number_format($p->montant,0,',',' ') }}
                                </span>

                                <form action="{{ route('paiements-dossiers.destroy', $p->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            onclick="return confirm('Supprimer ce paiement ?')"
                                            style="background:none;border:none;color:#dc2626;cursor:pointer;">
                                        🗑
                                    </button>
                                </form>
                            </div>

                        </div>
                                @empty
                                <div style="color:#94a3b8;font-size:11px;">Aucun paiement</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- BLOC TECHNIQUE --}}
                        <div class="paiement-bloc bloc-technique">
                            <h6 style="color:#ea580c;">
                                🛠️ Paiement Technique
                            </h6>
                            @if($prixTech > 0)
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Référence</span>
                                <span style="font-weight:600;">{{ number_format($prixTech, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endif
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Payé</span>
                                <span class="pay-amt" style="color:#ea580c;">{{ number_format($totalTechnique, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Reste</span>
                                <span style="font-weight:700;color:{{ $resteTech > 0 ? '#dc2626' : '#16a34a' }};">
                                    {{ number_format($resteTech, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                            @if($prixTech > 0)
                            <div style="height:6px;background:#e2e8f0;border-radius:3px;margin:8px 0;">
                                @php $pctT = $prixTech > 0 ? min(100, round(($totalTechnique/$prixTech)*100)) : 0; @endphp
                                <div style="width:{{ $pctT }}%;height:100%;background:#ea580c;border-radius:3px;"></div>
                            </div>
                            <div style="font-size:10px;text-align:right;color:#ea580c;font-weight:700;">{{ $pctT }}%</div>
                            @endif
                            <div class="pay-historique">
                                @forelse($dossier->paiementsTechniques->sortByDesc('date_paiement') as $p)
                                <div class="pay-row" style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
                        
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;">
                                    {{ $p->date_paiement }}
                                </div>

                                @if($p->note)
                                    <div style="font-size:11px;color:#64748b;word-break:break-word;white-space:normal;">
                                        {{ $p->note }}
                                    </div>
                                @endif
                            </div>

                            <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                <span class="pay-amt" style="color:#16a34a;font-weight:700;">
                                    {{ number_format($p->montant,0,',',' ') }}
                                </span>

                                <form action="{{ route('paiements-techniques.destroy', $p->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            onclick="return confirm('Supprimer ce paiement ?')"
                                            style="background:none;border:none;color:#dc2626;cursor:pointer;">
                                        🗑
                                    </button>
                                </form>
                            </div>

                        </div>
                                @empty
                                <div style="color:#94a3b8;font-size:11px;">Aucun paiement</div>
                                @endforelse
                            </div>
                        </div>

                        {{-- BLOC MORCELLEMENT --}}
                        <div class="paiement-bloc bloc-morcel">
                            <h6 style="color:#ca8a04;">
                            Paiement Morcellement
                            </h6>
                            @if($prixMorcel > 0)
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Référence</span>
                                <span style="font-weight:600;">{{ number_format($prixMorcel, 0, ',', ' ') }} FCFA</span>
                            </div>
                            @endif
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Payé</span>
                                <span class="pay-amt" style="color:#ca8a04;">{{ number_format($totalMorcel, 0, ',', ' ') }} FCFA</span>
                            </div>
                            <div class="pay-total-row">
                                <span style="color:#64748b;">Reste</span>
                                <span style="font-weight:700;color:{{ $resteMorcel > 0 ? '#dc2626' : '#16a34a' }};">
                                    {{ number_format($resteMorcel, 0, ',', ' ') }} FCFA
                                </span>
                            </div>
                            @if($prixMorcel > 0)
                            <div style="height:6px;background:#e2e8f0;border-radius:3px;margin:8px 0;">
                                @php $pctM = $prixMorcel > 0 ? min(100, round(($totalMorcel/$prixMorcel)*100)) : 0; @endphp
                                <div style="width:{{ $pctM }}%;height:100%;background:#ca8a04;border-radius:3px;"></div>
                            </div>
                            <div style="font-size:10px;text-align:right;color:#ca8a04;font-weight:700;">{{ $pctM }}%</div>
                            @endif
                            <div class="pay-historique">
                                @forelse($dossier->paiementsMorcellements->sortByDesc('date_paiement') as $p)
                                <div class="pay-row" style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;">
                        
                            <div style="flex:1;min-width:0;">
                                <div style="font-size:12px;">
                                    {{ $p->date_paiement }}
                                </div>

                                @if($p->note)
                                    <div style="font-size:11px;color:#64748b;word-break:break-word;white-space:normal;">
                                        {{ $p->note }}
                                    </div>
                                @endif
                            </div>

                            <div style="display:flex;align-items:center;gap:6px;flex-shrink:0;">
                                <span class="pay-amt" style="color:#16a34a;font-weight:700;">
                                    {{ number_format($p->montant,0,',',' ') }}
                                </span>

                                <form action="{{ route('paiements-morcellements.destroy', $p->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit"
                                            onclick="return confirm('Supprimer ce paiement ?')"
                                            style="background:none;border:none;color:#dc2626;cursor:pointer;">
                                        🗑
                                    </button>
                                </form>
                            </div>

                        </div>
                                @empty
                                <div style="color:#94a3b8;font-size:11px;">Aucun paiement</div>
                                @endforelse
                            </div>
                        </div>

                    </div>

                    {{-- ============================================================
                         👥 BÉNÉFICIAIRES / RÉPARTITION
                         ============================================================ --}}
                    @php
                        $supDossier    = $dossier->superficie_dossier;
                        $supAttribuee  = $dossier->superficie_attribuee;
                        $supRestante   = $dossier->superficie_restante;
                        $pctAttribue   = $dossier->pourcentage_attribue;
                        $beneficiaires = $dossier->beneficiaires->sortBy('nom');
                    @endphp

                    <div style="background:white;border-radius:12px;padding:16px;margin-top:14px;
                                border-left:4px solid #7c3aed;" id="bloc-beneficiaires-{{ $dossier->id }}">

                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;">
                            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">
                                👥 Bénéficiaires / Répartition ({{ $beneficiaires->count() }})
                            </div>
                            <button onclick="ouvrirModalBenef({{ $dossier->id }}, null)"
                                    class="btn btn-sm"
                                    style="background:#7c3aed;color:white;font-size:11px;font-weight:600;">
                                + Ajouter un bénéficiaire
                            </button>
                        </div>

                        {{-- INDICATEUR DE RÉPARTITION --}}
                        <div style="background:#faf5ff;border:1px solid #e9d5ff;border-radius:10px;
                                    padding:12px;margin-bottom:14px;">
                            <div style="font-size:11px;font-weight:700;color:#7c3aed;margin-bottom:8px;">
                                📐 Répartition de la superficie
                            </div>
                            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;">
                                <div>
                                    <div style="font-size:10px;color:#64748b;">Superficie du dossier</div>
                                    <div style="font-size:14px;font-weight:800;color:#1e3a5f;">
                                        {{ number_format($supDossier, 0, ',', ' ') }} m²
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size:10px;color:#64748b;">Superficie attribuée</div>
                                    <div style="font-size:14px;font-weight:800;color:#7c3aed;">
                                        {{ number_format($supAttribuee, 0, ',', ' ') }} m²
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size:10px;color:#64748b;">Superficie restante</div>
                                    <div style="font-size:14px;font-weight:800;
                                                color:{{ $supRestante > 0 ? '#16a34a' : '#dc2626' }};">
                                        {{ number_format($supRestante, 0, ',', ' ') }} m²
                                    </div>
                                </div>
                            </div>

                            <div style="height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden;margin-top:10px;">
                                <div id="bar-benef-{{ $dossier->id }}"
                                     style="width:{{ $pctAttribue }}%;height:100%;
                                            background:{{ $pctAttribue >= 100 ? '#dc2626' : '#7c3aed' }};
                                            border-radius:4px;transition:width 0.3s;"></div>
                            </div>
                            <div style="font-size:10px;text-align:right;color:#7c3aed;font-weight:700;margin-top:4px;">
                                <span id="pct-benef-{{ $dossier->id }}">{{ $pctAttribue }}</span>% attribué
                            </div>
                        </div>

                        {{-- LISTE DES BÉNÉFICIAIRES --}}
                        <div id="liste-benef-{{ $dossier->id }}">
                            @forelse($beneficiaires as $b)

                                @php
                                    $benefAffectations = $b->affectations()->with(['lot', 'bloc', 'grandSite'])->get();
                                    $benefEtapesConfig = \App\Models\Beneficiaire::etapesConfig();
                                    $benefEtapesOrdre  = \App\Models\Beneficiaire::etapesOrdre();
                                    $benefEtapeActuelle = $b->etape_actuelle;
                                @endphp

                                <div style="background:white;border:1px solid #e2e8f0;border-radius:10px;
                                            padding:14px;margin-bottom:14px;"
                                     id="benef-{{ $b->id }}">

                                    {{-- En-tête --}}
                                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:10px;margin-bottom:10px;">
                                        <div style="flex:1;min-width:0;">
                                            <div style="font-weight:700;font-size:14px;color:#1e3a5f;">
                                                👤 {{ $b->nom }}
                                                @if($b->telephone)
                                                    <span style="font-weight:400;color:#64748b;font-size:11px;">
                                                        · 📞 {{ $b->telephone }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div style="display:flex;flex-wrap:wrap;gap:6px;margin-top:6px;">
                                                @if($b->lots_texte)
                                                    <span style="background:#f5f3ff;color:#7c3aed;font-size:10px;
                                                                 padding:3px 8px;border-radius:6px;font-weight:600;">
                                                        🏷️ {{ $b->lots_texte }}
                                                    </span>
                                                @endif
                                                <span style="background:#f0fdf4;color:#16a34a;font-size:10px;
                                                             padding:3px 8px;border-radius:6px;font-weight:700;">
                                                    📐 {{ number_format($b->superficie_attribuee, 0, ',', ' ') }} m²
                                                </span>
                                                @if($b->cni_url)
                                                    <a href="{{ $b->cni_url }}" target="_blank"
                                                       style="background:#eff6ff;color:#1d4ed8;font-size:10px;
                                                              padding:3px 8px;border-radius:6px;font-weight:600;
                                                              text-decoration:none;">
                                                        📎 CNI
                                                    </a>
                                                @endif
                                            </div>

                                            @if($b->notes)
                                                <div style="font-size:10px;color:#64748b;margin-top:6px;
                                                            background:#f1f5f9;padding:4px 8px;border-radius:4px;">
                                                    📝 {{ $b->notes }}
                                                </div>
                                            @endif
                                        </div>

                                        <div style="display:flex;gap:4px;flex-shrink:0;">
                                            <button onclick='ouvrirModalBenef({{ $dossier->id }}, @json($b))'
                                                    style="background:none;border:none;color:#f59e0b;cursor:pointer;font-size:13px;"
                                                    title="Modifier">✏️</button>
                                            <button onclick="supprimerBenef({{ $b->id }}, {{ $dossier->id }})"
                                                    style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:13px;"
                                                    title="Supprimer">🗑</button>
                                        </div>
                                    </div>

                                    {{-- ═══════════════════════════════════════════════════════
                                         📦 LOTS AFFECTÉS AU BÉNÉFICIAIRE
                                         ═══════════════════════════════════════════════════════ --}}
                                    @if($benefAffectations->count() > 0)
                                    <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:8px;
                                                padding:10px;margin-bottom:10px;">
                                        <div style="font-size:10px;font-weight:700;color:#16a34a;
                                                    text-transform:uppercase;margin-bottom:8px;">
                                            📦 Lots affectés ({{ $benefAffectations->count() }})
                                        </div>

                                        @php
                                            $benefGroupes = $benefAffectations->groupBy(function($aff) {
                                                return $aff->bloc_id . '-' . $aff->date_affectation?->format('Y-m-d');
                                            });
                                        @endphp

                                        <div style="display:flex;flex-wrap:wrap;gap:6px;">
                                            @foreach($benefGroupes as $grp)
                                                @php
                                                    $premier  = $grp->first();
                                                    $nbLots   = $grp->count();
                                                    $lotsList = $grp->pluck('lot.numero')->implode(', ');
                                                    $ids      = $grp->pluck('id')->implode(',');
                                                @endphp

                                                <div style="background:white;border:1px solid #86efac;border-radius:6px;
                                                            padding:5px 10px;display:inline-flex;align-items:center;gap:6px;"
                                                     id="benef-groupe-{{ $premier->id }}">

                                                    <span style="font-weight:600;font-size:11px;color:#1e3a5f;">
                                                        {{ $premier->grandSite?->nom ?? '-' }}
                                                        — Bloc <strong>{{ $premier->bloc?->code ?? '-' }}</strong>
                                                        — (Lot {{ $lotsList }})
                                                    </span>

                                                    <span style="font-size:9px;color:#64748b;">
                                                        📅 {{ $premier->date_affectation?->format('d/m/Y') }}
                                                    </span>

                                                    <span style="font-size:9px;color:#94a3b8;background:#f1f5f9;
                                                                 padding:0 6px;border-radius:4px;">
                                                        {{ $nbLots }} lot(s)
                                                    </span>

                                                    <button onclick="annulerGroupeBenefAffectation('{{ $ids }}', this)"
                                                            style="background:none;border:none;color:#dc2626;
                                                                   cursor:pointer;font-size:11px;padding:0 4px;"
                                                            title="Annuler toutes les affectations du groupe">✕</button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    @endif

                                    {{-- ═══════════════════════════════════════════════════════
                                         ➕ FORMULAIRE AFFECTATION LOTS AU BÉNÉFICIAIRE
                                         ═══════════════════════════════════════════════════════ --}}
                                    <div style="background:#f8fafc;border-radius:8px;padding:10px;margin-bottom:10px;
                                                border:1px solid #e2e8f0;">
                                        <div style="font-size:10px;font-weight:700;color:#64748b;
                                                    text-transform:uppercase;margin-bottom:8px;">
                                            ➕ Affecter des lots à ce bénéficiaire
                                        </div>

                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-3">
                                                <label style="font-size:9px;font-weight:600;color:#374151;">Grand Site</label>
                                                <select class="form-control form-control-sm"
                                                        id="benef-aff-gs-{{ $b->id }}"
                                                        onchange="benefAffChargerSites(this.value, {{ $b->id }})">
                                                    <option value="">-- Choisir --</option>
                                                    @foreach(\App\Models\GrandSite::orderBy('nom')->get() as $gs)
                                                        <option value="{{ $gs->id }}">{{ $gs->nom }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label style="font-size:9px;font-weight:600;color:#374151;">Site</label>
                                                <select class="form-control form-control-sm"
                                                        id="benef-aff-site-{{ $b->id }}"
                                                        onchange="benefAffChargerTfs(this.value, {{ $b->id }})">
                                                    <option value="">-- Choisir --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label style="font-size:9px;font-weight:600;color:#374151;">TF</label>
                                                <select class="form-control form-control-sm"
                                                        id="benef-aff-tf-{{ $b->id }}"
                                                        onchange="benefAffChargerBlocs(this.value, {{ $b->id }})">
                                                    <option value="">-- Choisir --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label style="font-size:9px;font-weight:600;color:#374151;">Bloc</label>
                                                <select class="form-control form-control-sm"
                                                        id="benef-aff-bloc-{{ $b->id }}"
                                                        onchange="benefAffChargerLots(this.value, {{ $b->id }})">
                                                    <option value="">-- Choisir --</option>
                                                </select>
                                            </div>
                                            <div class="col-md-1">
                                                <label style="font-size:9px;font-weight:600;color:#374151;">Date</label>
                                                <input type="date" class="form-control form-control-sm"
                                                       id="benef-aff-date-{{ $b->id }}"
                                                       value="{{ now()->format('Y-m-d') }}">
                                            </div>
                                            <div class="col-md-2">
                                                <label style="font-size:9px;font-weight:600;color:#374151;">Notes</label>
                                                <input type="text" class="form-control form-control-sm"
                                                       id="benef-aff-notes-{{ $b->id }}"
                                                       placeholder="Optionnel">
                                            </div>
                                        </div>

                                        <div style="margin-top:8px;" id="benef-aff-lots-container-{{ $b->id }}">
                                            <div style="color:#94a3b8;font-size:11px;padding:4px 0;">
                                                ℹ️ Sélectionnez un Grand Site, Site, TF et Bloc pour voir les lots disponibles
                                            </div>
                                        </div>

                                        <div style="margin-top:8px;display:flex;gap:10px;align-items:center;">
                                            <button onclick="validerBenefAffectation({{ $b->id }})"
                                                    class="btn btn-success btn-sm" style="font-size:11px;"
                                                    id="benef-aff-btn-{{ $b->id }}" disabled>
                                                ✅ Affecter les lots sélectionnés
                                            </button>
                                            <span style="font-size:10px;color:#94a3b8;"
                                                  id="benef-aff-compteur-{{ $b->id }}">
                                                0 lot(s) sélectionné(s)
                                            </span>
                                        </div>
                                    </div>

                                    {{-- ═══════════════════════════════════════════════════════
                                         📊 ÉTAPES DU BÉNÉFICIAIRE
                                         ═══════════════════════════════════════════════════════ --}}
                                    <div style="background:#f8fafc;border-radius:8px;padding:10px;
                                                border:1px solid #e2e8f0;">
                                        <div style="font-size:10px;font-weight:700;color:#64748b;
                                                    text-transform:uppercase;margin-bottom:8px;">
                                            📊 Étapes du bénéficiaire
                                        </div>

                                        <div style="display:flex;gap:6px;flex-wrap:wrap;">
                                            @foreach($benefEtapesConfig as $cle => $cfg)
                                                @php
                                                    $ordreEtape  = $benefEtapesOrdre[$cle];
                                                    $ordreActuel = $benefEtapeActuelle ? ($benefEtapesOrdre[$benefEtapeActuelle] ?? 0) : 0;
                                                    $estFait     = $ordreEtape <= $ordreActuel;
                                                    $champ       = $cfg['champ'];
                                                    $dateEtape   = $b->$champ;
                                                @endphp

                                                <div onclick="ouvrirModalEtapeBenef({{ $b->id }}, '{{ $cle }}', '{{ $cfg['label'] }}', {{ $estFait ? 'true' : 'false' }}, '{{ $dateEtape ? \Carbon\Carbon::parse($dateEtape)->format('Y-m-d') : '' }}')"
                                                     class="benef-etape-card-{{ $b->id }}-{{ $cle }}"
                                                     style="
                                                        display:flex;align-items:center;gap:5px;
                                                        padding:6px 12px;border-radius:16px;
                                                        border:2px solid {{ $estFait ? $cfg['color'] : '#e2e8f0' }};
                                                        background:{{ $estFait ? $cfg['bg'] : 'white' }};
                                                        font-size:11px;font-weight:700;
                                                        color:{{ $estFait ? $cfg['color'] : '#94a3b8' }};
                                                        cursor:pointer;transition:all 0.2s;
                                                        {{ $estFait ? '' : 'opacity:0.7;' }}
                                                     "
                                                     onmouseover="this.style.transform='scale(1.02)'"
                                                     onmouseout="this.style.transform='scale(1)'"
                                                     title="Cliquez pour {{ $estFait ? 'modifier' : 'définir' }} la date">
                                                    <span style="font-size:13px;">{{ $cfg['icon'] }}</span>
                                                    {{ $cfg['label'] }}
                                                    @if($estFait && $dateEtape)
                                                        <span style="font-size:9px;font-weight:400;color:#64748b;
                                                                     background:white;padding:0 6px;border-radius:8px;
                                                                     border:1px solid #e2e8f0;">
                                                            {{ \Carbon\Carbon::parse($dateEtape)->format('d/m/Y') }}
                                                        </span>
                                                        <span style="color:#16a34a;">✓</span>
                                                    @else
                                                        <span style="font-size:9px;font-weight:400;color:#94a3b8;">
                                                            (à définir)
                                                        </span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                </div>
                            @empty
                            <div style="text-align:center;color:#94a3b8;font-size:12px;padding:16px;
                                        background:#f8fafc;border-radius:8px;">
                                Aucun bénéficiaire pour ce dossier.
                            </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- ============================================================
                         📜 HISTORIQUE DES AFFECTATIONS & BÉNÉFICIAIRES
                         ============================================================ --}}
                    @php
                        $historiques = $dossier->historiques ?? collect();
                    @endphp

                    <div style="background:white;border-radius:12px;padding:16px;margin-top:14px;
                                border-left:4px solid #64748b;" id="bloc-historique-{{ $dossier->id }}">

                        <div style="display:flex;justify-content:space-between;align-items:center;
                                    margin-bottom:12px;">
                            <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">
                                📜 Historique ({{ $historiques->count() }})
                            </div>
                            <button onclick="toggleHistorique({{ $dossier->id }})"
                                    class="btn btn-sm"
                                    style="font-size:10px;background:#f1f5f9;color:#475569;font-weight:600;">
                                <span id="btn-histo-texte-{{ $dossier->id }}">Afficher</span>
                            </button>
                        </div>

                        <div id="historique-content-{{ $dossier->id }}" style="display:none;">

                            @forelse($historiques as $h)
                                <div style="background:#f8fafc;border-left:3px solid {{ $h->couleur }};
                                            border-radius:6px;padding:10px 12px;margin-bottom:8px;">

                                    <div style="display:flex;justify-content:space-between;
                                                align-items:flex-start;gap:10px;">

                                        <div style="flex:1;min-width:0;">
                                            <div style="font-size:12px;color:#1e3a5f;font-weight:600;">
                                                {{ $h->icone }} {{ $h->resume }}
                                            </div>

                                            <div style="font-size:10px;color:#94a3b8;margin-top:4px;">
                                                📅 {{ $h->created_at->format('d/m/Y H:i') }}
                                                @if($h->user)
                                                    &nbsp;·&nbsp; 👤 {{ $h->user->name }}
                                                @endif
                                            </div>

                                            {{-- Détails du diff --}}
                                            @if($h->type_action === 'modification_beneficiaire'
                                                && $h->donnees_avant && $h->donnees_apres)
                                                <details style="margin-top:6px;font-size:10px;color:#64748b;">
                                                    <summary style="cursor:pointer;color:#7c3aed;font-weight:600;">
                                                        Voir le détail des changements
                                                    </summary>
                                                    <div style="background:white;border-radius:6px;
                                                                padding:6px 10px;margin-top:6px;">

                                                        @php
                                                            $av = $h->donnees_avant;
                                                            $ap = $h->donnees_apres;
                                                            $champs = [
                                                                'nom'                  => 'Nom',
                                                                'telephone'            => 'Téléphone',
                                                                'lots_texte'           => 'Lots',
                                                                'superficie_attribuee' => 'Superficie',
                                                                'notes'                => 'Notes',
                                                            ];
                                                        @endphp

                                                        @foreach($champs as $champ => $label)
                                                            @php
                                                                $old = $av[$champ] ?? null;
                                                                $new = $ap[$champ] ?? null;
                                                            @endphp
                                                            @if($champ === 'superficie_attribuee'
                                                                ? (float)$old !== (float)$new
                                                                : $old !== $new)
                                                                <div style="padding:2px 0;">
                                                                    <strong>{{ $label }} :</strong>
                                                                    <span style="color:#dc2626;
                                                                                 text-decoration:line-through;">
                                                                        {{ $old ?? '—' }}
                                                                    </span>
                                                                    <span style="color:#16a34a;">
                                                                        → {{ $new ?? '—' }}
                                                                    </span>
                                                                </div>
                                                            @endif
                                                        @endforeach

                                                        @if(($av['cni_path'] ?? null) !== ($ap['cni_path'] ?? null))
                                                            <div style="padding:2px 0;color:#f59e0b;">
                                                                📎 CNI remplacée
                                                            </div>
                                                        @endif
                                                    </div>
                                                </details>
                                            @endif

                                            {{-- Détails ajout bénéficiaire --}}
                                            @if($h->type_action === 'ajout_beneficiaire' && $h->donnees_apres)
                                                <details style="margin-top:6px;font-size:10px;color:#64748b;">
                                                    <summary style="cursor:pointer;color:#16a34a;font-weight:600;">
                                                        Voir le détail
                                                    </summary>
                                                    <div style="background:white;border-radius:6px;
                                                                padding:6px 10px;margin-top:6px;">
                                                        <div><strong>Nom :</strong> {{ $h->donnees_apres['nom'] ?? '—' }}</div>
                                                        <div><strong>Superficie :</strong>
                                                            {{ number_format($h->donnees_apres['superficie_attribuee'] ?? 0, 0, ',', ' ') }} m²
                                                        </div>
                                                        @if(!empty($h->donnees_apres['lots_texte']))
                                                            <div><strong>Lots :</strong> {{ $h->donnees_apres['lots_texte'] }}</div>
                                                        @endif
                                                    </div>
                                                </details>
                                            @endif

                                            {{-- Détails affectation de lots --}}
                                            @if($h->type_action === 'affectation_lot' && $h->donnees_apres)
                                                <details style="margin-top:6px;font-size:10px;color:#64748b;">
                                                    <summary style="cursor:pointer;color:#0d6efd;font-weight:600;">
                                                        Voir le détail
                                                    </summary>
                                                    <div style="background:white;border-radius:6px;
                                                                padding:6px 10px;margin-top:6px;">
                                                        @if(!empty($h->donnees_apres['beneficiaire_nom']))
                                                            <div><strong>Bénéficiaire :</strong> {{ $h->donnees_apres['beneficiaire_nom'] }}</div>
                                                        @endif
                                                        @if(!empty($h->donnees_apres['date_affectation']))
                                                            <div><strong>Date :</strong> {{ $h->donnees_apres['date_affectation'] }}</div>
                                                        @endif
                                                        @if(!empty($h->donnees_apres['notes']))
                                                            <div><strong>Notes :</strong> {{ $h->donnees_apres['notes'] }}</div>
                                                        @endif
                                                        @if(!empty($h->donnees_apres['lots']))
                                                            <div style="margin-top:4px;">
                                                                <strong>Lots affectés :</strong>
                                                                <ul style="margin:4px 0 0 16px;padding:0;">
                                                                    @foreach($h->donnees_apres['lots'] as $lot)
                                                                        <li>Lot {{ $lot['numero'] ?? '?' }}
                                                                            @if(!empty($lot['bloc'])) (Bloc {{ $lot['bloc'] }}) @endif
                                                                        </li>
                                                                    @endforeach
                                                                </ul>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </details>
                                            @endif

                                            {{-- Détails suppression --}}
                                            @if(in_array($h->type_action, ['suppression_beneficiaire', 'annulation_affectation']) && $h->donnees_avant)
                                                <details style="margin-top:6px;font-size:10px;color:#64748b;">
                                                    <summary style="cursor:pointer;color:#dc2626;font-weight:600;">
                                                        Voir le détail
                                                    </summary>
                                                    <div style="background:white;border-radius:6px;
                                                                padding:6px 10px;margin-top:6px;">
                                                        @if(!empty($h->donnees_avant['nom']))
                                                            <div><strong>Nom :</strong> {{ $h->donnees_avant['nom'] }}</div>
                                                        @endif
                                                        @if(!empty($h->donnees_avant['superficie_attribuee']))
                                                            <div><strong>Superficie :</strong>
                                                                {{ number_format($h->donnees_avant['superficie_attribuee'], 0, ',', ' ') }} m²
                                                            </div>
                                                        @endif
                                                        @if(!empty($h->donnees_avant['lot_num']))
                                                            <div><strong>Lot :</strong> {{ $h->donnees_avant['lot_num'] }}
                                                                @if(!empty($h->donnees_avant['bloc'])) (Bloc {{ $h->donnees_avant['bloc'] }}) @endif
                                                            </div>
                                                        @endif
                                                        @if(!empty($h->donnees_avant['date']))
                                                            <div><strong>Date affectation :</strong> {{ $h->donnees_avant['date'] }}</div>
                                                        @endif
                                                    </div>
                                                </details>
                                            @endif

                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div style="text-align:center;color:#94a3b8;font-size:12px;padding:16px;
                                            background:#f8fafc;border-radius:8px;">
                                    Aucun historique pour ce dossier.
                                </div>
                            @endforelse

                        </div>
                    </div>

                </div>
            </div>
            @endforeach
        @else
            {{-- ✅ AUCUN DOSSIER : Message + bouton pour en créer --}}
            <div class="section-card" style="text-align:center;padding:40px;">
                <div style="font-size:48px;margin-bottom:16px;">📂</div>
                <h4 style="color:#1e3a5f;">Aucun dossier pour ce client</h4>
                <p style="color:#64748b;margin-bottom:16px;">
                    Ce client n'a pas encore de dossier. Créez-lui un dossier pour commencer le suivi.
                </p>
                <a href="{{ route('suivi-client.create') }}?client_id={{ $client->id }}" 
                   class="btn btn-primary">
                    📂 Créer un dossier
                </a>
            </div>
        @endif
    </div>

    {{-- VISITES --}}
    <div class="col-12">
    <div class="section-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">🚶 Registre des visites ({{ $client->visites->count() }})</h5>
            <a href="{{ route('visites.index', ['nom' => $client->name]) }}" 
               class="btn btn-outline-primary btn-sm">
                Voir toutes les visites →
            </a>
        </div>

        @forelse($client->visites->sortByDesc('date_visite')->take(10) as $visite)
            @php
                $typeColors = [
                    'client' => '#1d4ed8',
                    'proprietaire' => '#15803d',
                    'autre' => '#475569'
                ];
                $color = $typeColors[$visite->type_personne] ?? '#475569';
                $typeLabels = [
                    'client' => 'Client',
                    'proprietaire' => 'Propriétaire',
                    'autre' => 'Autre'
                ];
            @endphp
            <div style="border-left:4px solid {{ $color }}; padding:12px; margin-bottom:10px; background:#f8fafc; border-radius:8px;">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <strong style="color:#1e3a5f;">{{ $visite->visiteur?->nom ?? $visite->nom ?? 'Inconnu' }}</strong>
                            <span style="background:{{ $color }}22; color:{{ $color }}; font-size:10px; padding:2px 8px; border-radius:6px; font-weight:600;">
                                {{ $typeLabels[$visite->type_personne] ?? $visite->type_personne }}
                            </span>
                            @if($visite->paiement_lie)
                                <span style="background:#fef3c7; color:#92400e; font-size:9px; padding:2px 8px; border-radius:4px; font-weight:600;">
                                    💳 Paiement lié
                                </span>
                            @endif
                        </div>
                        <div style="font-size:11px; color:#64748b; margin-top:4px;">
                            📅 {{ \Carbon\Carbon::parse($visite->date_visite)->format('d/m/Y') }}
                            @if($visite->heure_arrivee)
                                &nbsp;🕐 Arrivée : {{ substr($visite->heure_arrivee, 0, 5) }}
                            @endif
                            @if($visite->heure_depart)
                                &nbsp;🚪 Départ : {{ substr($visite->heure_depart, 0, 5) }}
                            @endif
                            @if($visite->grandSite?->nom || $visite->site?->name)
                                &nbsp;📍 
                                @if($visite->grandSite?->nom)
                                    {{ $visite->grandSite->nom }}
                                @endif
                                @if($visite->site?->name)
                                    - {{ $visite->site->name }}
                                @endif
                            @endif
                        </div>
                        @if($visite->note)
                            <div style="font-size:11px; color:#475569; margin-top:3px; background:#f1f5f9; padding:3px 8px; border-radius:4px;">
                                📝 {{ $visite->note }}
                            </div>
                        @endif
                        @if($visite->visiteur?->numero || $visite->numero)
                            <div style="font-size:10px; color:#94a3b8; margin-top:2px;">
                                📞 {{ $visite->visiteur?->numero ?? $visite->numero }}
                            </div>
                        @endif
                    </div>
                    <div style="display:flex;gap:4px;flex-shrink:0;">
                        <span style="font-size:10px; color:#94a3b8;">
                            {{ $visite->created_at ? $visite->created_at->format('d/m/Y H:i') : '' }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="alert alert-info mb-0">
                🚶 Aucune visite enregistrée pour ce client.
                <a href="{{ route('visites.index') }}" class="alert-link">Enregistrer une visite</a>
            </div>
        @endforelse

        @if($client->visites->count() > 10)
            <div style="text-align:center;margin-top:10px;">
                <a href="{{ route('visites.index', ['nom' => $client->name]) }}" 
                   class="btn btn-outline-secondary btn-sm">
                    Voir les {{ $client->visites->count() - 10 }} autres visites →
                </a>
            </div>
        @endif
    </div>
</div>
</div>

{{-- ============================================================
     MODAL PAIEMENT
     ============================================================ --}}
<div class="modal-overlay" id="paiementOverlay" onclick="closePaiement()"></div>
<div class="modal-box" id="paiementModal">
    <h5 id="paiementTitre">💰 Ajouter un paiement — <span id="dossierNom"></span></h5>
    <input type="number" id="montant"  class="form-control mt-3" placeholder="Montant (FCFA)">
    <input type="date"   id="datePaie" class="form-control mt-2">
    <input type="text"   id="note"     class="form-control mt-2" placeholder="Note (optionnel)">
    <div class="d-flex justify-content-between mt-3">
        <button class="btn btn-secondary" onclick="closePaiement()">Annuler</button>
        <button class="btn btn-success"   onclick="savePaiement()">Ajouter</button>
    </div>
</div>

{{-- ============================================================
     MODAL ÉTAPE DOSSIER
     ============================================================ --}}
<div class="modal-overlay" id="modalEtapeOverlay" onclick="fermerModalEtape()"></div>
<div class="modal-box" id="modalEtape">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;" id="modalEtapeTitre">📅 Définir la date</h5>
        <button onclick="fermerModalEtape()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    
    <div class="mb-3">
        <p id="modalEtapeLabel" style="font-size:13px;color:#64748b;margin-bottom:10px;"></p>
        <input type="date" id="modalEtapeDate" class="form-control" style="font-size:14px;padding:10px;">
        <div style="margin-top:10px;font-size:11px;color:#94a3b8;">
            💡 Sélectionnez la date de l'étape
        </div>
    </div>
    
    <div id="modalEtapeActions" style="display:flex;justify-content:space-between;gap:10px;margin-top:10px;">
        <button onclick="supprimerEtape()" class="btn btn-danger btn-sm" id="btnSupprimerEtape" style="display:none;">
            🗑 Supprimer l'étape
        </button>
        <div style="display:flex;gap:10px;margin-left:auto;">
            <button onclick="fermerModalEtape()" class="btn btn-light">Annuler</button>
            <button onclick="validerEtape()" class="btn btn-primary" id="btnValiderEtape">✅ Valider</button>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL ÉTAPE BÉNÉFICIAIRE
     ============================================================ --}}
<div class="modal-overlay" id="modalEtapeBenefOverlay" onclick="fermerModalEtapeBenef()"></div>
<div class="modal-box" id="modalEtapeBenef">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;" id="modalEtapeBenefTitre">
            📅 Définir la date
        </h5>
        <button onclick="fermerModalEtapeBenef()"
                style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>

    <div class="mb-3">
        <p id="modalEtapeBenefLabel" style="font-size:13px;color:#64748b;margin-bottom:10px;"></p>
        <input type="date" id="modalEtapeBenefDate" class="form-control"
               style="font-size:14px;padding:10px;">
        <div style="margin-top:10px;font-size:11px;color:#94a3b8;">
            💡 Sélectionnez la date de l'étape
        </div>
    </div>

    <div style="display:flex;justify-content:space-between;gap:10px;margin-top:10px;">
        <button onclick="supprimerEtapeBenef()" class="btn btn-danger btn-sm"
                id="btnSupprimerEtapeBenef" style="display:none;">
            🗑 Supprimer l'étape
        </button>
        <div style="display:flex;gap:10px;margin-left:auto;">
            <button onclick="fermerModalEtapeBenef()" class="btn btn-light">Annuler</button>
            <button onclick="validerEtapeBenef()" class="btn btn-primary">✅ Valider</button>
        </div>
    </div>
</div>

{{-- ============================================================
     MODAL BÉNÉFICIAIRE (ajout / modification)
     ============================================================ --}}
<div class="modal-overlay" id="benefOverlay" onclick="fermerModalBenef()"></div>
<div class="modal-box" id="benefModal" style="width:520px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;" id="benefTitre">
            👥 Ajouter un bénéficiaire
        </h5>
        <button onclick="fermerModalBenef()"
                style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>

    <input type="hidden" id="benefId">
    <input type="hidden" id="benefDossierId">

    <div style="display:grid;gap:10px;">
        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">
                Nom et prénom(s) *
            </label>
            <input type="text" id="benefNom" class="form-control form-control-sm"
                   placeholder="Ex : Paul Dupont">
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
            <div>
                <label style="font-size:11px;font-weight:700;color:#64748b;">
                    Téléphone
                </label>
                <input type="text" id="benefTelephone" class="form-control form-control-sm"
                       placeholder="Ex : 6XX XXX XXX">
            </div>
            <div>
                <label style="font-size:11px;font-weight:700;color:#64748b;">
                    Lot(s)
                </label>
                <input type="text" id="benefLots" class="form-control form-control-sm"
                       placeholder="Ex : LOT 125, LOT 126">
            </div>
        </div>

        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">
                Superficie attribuée (m²) *
            </label>
            <input type="number" id="benefSuperficie" class="form-control form-control-sm"
                   placeholder="Ex : 300" min="0.01" step="0.01">
            <div id="benefSuperficieHint"
                 style="font-size:10px;color:#7c3aed;margin-top:4px;"></div>
        </div>

        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">
                CNI (obligatoire) <span id="benefCniOblig">*</span>
            </label>
            <input type="file" id="benefCni" class="form-control form-control-sm"
                   accept="image/*,application/pdf">
            <div id="benefCniActuelle" style="font-size:10px;margin-top:4px;"></div>
        </div>

        <div>
            <label style="font-size:11px;font-weight:700;color:#64748b;">Notes</label>
            <textarea id="benefNotes" class="form-control form-control-sm" rows="2"
                      placeholder="Optionnel"></textarea>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-3">
        <button onclick="fermerModalBenef()" class="btn btn-light btn-sm">Annuler</button>
        <button onclick="sauvegarderBenef()" class="btn btn-sm"
                style="background:#7c3aed;color:white;font-weight:600;">
            💾 Enregistrer
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
let currentDossierId = null;
let currentType       = 'dossier';

// ════════════════════════════════════════════════════════════════
// TOGGLE NEW STATUS
// ════════════════════════════════════════════════════════════════
function toggleNew(clientId) {
    const btn = document.getElementById('btn-new-detail-' + clientId);
    const badge = document.getElementById('badge-detail-' + clientId);

    if (!btn) return;

    btn.disabled = true;
    btn.textContent = '⏳ ...';

    fetch(`/admin/suivi-client/toggle-new/${clientId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (data.is_new) {
                btn.className = 'btn-toggle-new is-new';
                btn.innerHTML = '✅ Nouveau';
                badge.className = 'badge-new';
                badge.textContent = '🆕 Nouveau';
                showToast('✅ Client marqué comme nouveau');
            } else {
                btn.className = 'btn-toggle-new';
                btn.innerHTML = '🔄 Marquer nouveau';
                badge.className = 'badge-old';
                badge.textContent = 'Ancien';
                showToast('✅ Statut "nouveau" retiré');
            }
        } else {
            showToast('❌ ' + data.message);
        }
    })
    .catch(error => {
        showToast('❌ Erreur réseau');
        console.error('Erreur:', error);
    })
    .finally(() => {
        btn.disabled = false;
    });
}

// ════════════════════════════════════════════════════════════════
// TOAST NOTIFICATION
// ════════════════════════════════════════════════════════════════
function showToast(message) {
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// ════════════════════════════════════════════════════════════════
// NOTIFICATION TOAST (afficherNotification)
// ════════════════════════════════════════════════════════════════
function afficherNotification(message, couleur = '#16a34a') {
    const anciennes = document.querySelectorAll('.toast-notification');
    anciennes.forEach(el => el.remove());
    
    const toast = document.createElement('div');
    toast.className = 'toast-notification';
    toast.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${couleur};
        color: white;
        padding: 14px 24px;
        border-radius: 12px;
        z-index: 999999;
        font-weight: 700;
        font-size: 14px;
        box-shadow: 0 6px 24px rgba(0,0,0,0.2);
        transition: all 0.4s ease;
        max-width: 400px;
        font-family: system-ui, -apple-system, sans-serif;
    `;
    toast.textContent = message;
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    }, 50);
    
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(50px)';
        setTimeout(() => {
            if (toast.parentNode) toast.remove();
        }, 400);
    }, 3000);
}

// ════════════════════════════════════════════════════════════════
// ÉTAPES DOSSIER
// ════════════════════════════════════════════════════════════════
const ETAPES_CONFIG = {
    implantation_prevue : { color:'#7c3aed', bg:'#f5f3ff', border:'#c4b5fd', label:'Implantation prévue', icon:'📍' },
    deja_implante       : { color:'#16a34a', bg:'#f0fdf4', border:'#86efac', label:'Déjà implanté',       icon:'✅' },
    dossier_technique   : { color:'#dc2626', bg:'#fff1f2', border:'#fca5a5', label:'Dossier technique',   icon:'📁' },
    morcellement        : { color:'#ca8a04', bg:'#fefce8', border:'#fde68a', label:'Morcellement',        icon:'✂️' },
};
const ETAPES_ORDRE = {
    implantation_prevue:1, deja_implante:2, dossier_technique:3, morcellement:4
};

let modalDossierId = null;
let modalEtapeKey = null;
let modalEstFait = false;

function ouvrirModalEtape(dossierId, etape, label, estFait, dateActuelle) {
    modalDossierId = dossierId;
    modalEtapeKey = etape;
    modalEstFait = estFait;
    
    document.getElementById('modalEtapeTitre').textContent = '📅 ' + label;
    document.getElementById('modalEtapeLabel').textContent = 'Sélectionnez la date pour l\'étape "' + label + '"';
    
    const dateInput = document.getElementById('modalEtapeDate');
    if (dateActuelle) {
        dateInput.value = dateActuelle;
    } else {
        dateInput.value = new Date().toISOString().split('T')[0];
    }
    
    const btnSuppr = document.getElementById('btnSupprimerEtape');
    if (estFait) {
        btnSuppr.style.display = 'inline-block';
    } else {
        btnSuppr.style.display = 'none';
    }
    
    document.getElementById('modalEtapeOverlay').style.display = 'block';
    document.getElementById('modalEtape').style.display = 'block';
}

function fermerModalEtape() {
    document.getElementById('modalEtapeOverlay').style.display = 'none';
    document.getElementById('modalEtape').style.display = 'none';
}

function supprimerEtape() {
    if (!confirm('Supprimer cette étape ?')) return;
    
    fermerModalEtape();
    
    if (window.EdenLoader) window.EdenLoader.show();
    
    fetch(`/admin/dossiers/${modalDossierId}/maj-etape`, {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({ 
            etape: modalEtapeKey, 
            date: null,
            active: false
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            mettreAJourVisuEtapesSimple(modalDossierId, data);
            afficherNotification('🗑️ Étape supprimée avec succès !', '#dc2626');
        } else {
            afficherNotification(data.message || 'Erreur', '#dc2626');
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        afficherNotification('Erreur réseau : ' + e.message, '#dc2626');
    });
}

function validerEtape() {
    const date = document.getElementById('modalEtapeDate').value;
    
    if (!date) {
        afficherNotification('⚠️ Veuillez sélectionner une date.', '#dc2626');
        return;
    }
    
    fermerModalEtape();
    
    if (window.EdenLoader) window.EdenLoader.show();
    
    fetch(`/admin/dossiers/${modalDossierId}/maj-etape`, {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({ 
            etape: modalEtapeKey, 
            date: date, 
            active: true 
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            mettreAJourVisuEtapesSimple(modalDossierId, data);
            afficherNotification('✅ Étape mise à jour avec succès !', '#16a34a');
        } else {
            afficherNotification(data.message || 'Erreur', '#dc2626');
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        afficherNotification('Erreur réseau : ' + e.message, '#dc2626');
    });
}

function mettreAJourVisuEtapesSimple(dossierId, data) {
    const etapeActuelle = data.etape_actuelle;
    const ordreActuel = etapeActuelle ? ETAPES_ORDRE[etapeActuelle] : 0;

    Object.entries(ETAPES_CONFIG).forEach(([cle, cfg]) => {
        const ordreEtape = ETAPES_ORDRE[cle];
        const estFait = ordreEtape <= ordreActuel;
        
        const card = document.querySelector(`.etape-card-${cle}`);
        
        if (card) {
            card.innerHTML = '';
            
            const iconSpan = document.createElement('span');
            iconSpan.style.cssText = 'font-size:16px;';
            iconSpan.textContent = cfg['icon'];
            card.appendChild(iconSpan);
            
            const labelSpan = document.createElement('span');
            labelSpan.textContent = ' ' + cfg['label'];
            card.appendChild(labelSpan);
            
            card.style.borderColor = estFait ? cfg.color : '#e2e8f0';
            card.style.background = estFait ? cfg.bg : 'white';
            card.style.color = estFait ? cfg.color : '#94a3b8';
            card.style.opacity = estFait ? '1' : '0.7';
            
            if (estFait && data.dates[cle]) {
                const dateSpan = document.createElement('span');
                dateSpan.style.cssText = 'font-size:10px;font-weight:400;color:#64748b;background:white;padding:0 10px;border-radius:10px;border:1px solid #e2e8f0;margin-left:4px;';
                dateSpan.textContent = data.dates[cle];
                card.appendChild(dateSpan);
                
                const check = document.createElement('span');
                check.style.cssText = 'color:#16a34a;margin-left:2px;';
                check.textContent = ' ✓';
                card.appendChild(check);
            } else {
                const text = document.createElement('span');
                text.style.cssText = 'font-size:10px;font-weight:400;color:#94a3b8;margin-left:2px;';
                text.textContent = '(à définir)';
                card.appendChild(text);
            }
            
            card.onclick = function() {
                ouvrirModalEtape(dossierId, cle, cfg['label'], estFait, data.dates[cle] ? data.dates[cle].split('/').reverse().join('-') : '');
            };
        }
    });
}

// ════════════════════════════════════════════════════════════════
// ÉTAPES BÉNÉFICIAIRE
// ════════════════════════════════════════════════════════════════
let modalBenefId = null;
let modalEtapeBenefKey = null;
let modalEtapeBenefEstFait = false;

function ouvrirModalEtapeBenef(benefId, etape, label, estFait, dateActuelle) {
    modalBenefId = benefId;
    modalEtapeBenefKey = etape;
    modalEtapeBenefEstFait = estFait;

    document.getElementById('modalEtapeBenefTitre').textContent = '📅 ' + label;
    document.getElementById('modalEtapeBenefLabel').textContent =
        'Sélectionnez la date pour l\'étape "' + label + '" du bénéficiaire';

    const dateInput = document.getElementById('modalEtapeBenefDate');
    if (dateActuelle) {
        dateInput.value = dateActuelle;
    } else {
        dateInput.value = new Date().toISOString().split('T')[0];
    }

    const btnSuppr = document.getElementById('btnSupprimerEtapeBenef');
    btnSuppr.style.display = estFait ? 'inline-block' : 'none';

    document.getElementById('modalEtapeBenefOverlay').style.display = 'block';
    document.getElementById('modalEtapeBenef').style.display = 'block';
}

function fermerModalEtapeBenef() {
    document.getElementById('modalEtapeBenefOverlay').style.display = 'none';
    document.getElementById('modalEtapeBenef').style.display = 'none';
}

function validerEtapeBenef() {
    const date = document.getElementById('modalEtapeBenefDate').value;

    if (!date) {
        afficherNotification('⚠️ Veuillez sélectionner une date.', '#dc2626');
        return;
    }

    fermerModalEtapeBenef();

    if (window.EdenLoader) window.EdenLoader.show();

    fetch(`/admin/beneficiaires/${modalBenefId}/maj-etape`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({
            etape: modalEtapeBenefKey,
            date: date,
            active: true
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            afficherNotification('✅ Étape du bénéficiaire mise à jour !', '#16a34a');
            setTimeout(() => location.reload(), 800);
        } else {
            afficherNotification(data.message || 'Erreur', '#dc2626');
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        afficherNotification('Erreur réseau : ' + e.message, '#dc2626');
    });
}

function supprimerEtapeBenef() {
    if (!confirm('Supprimer cette étape ?')) return;

    fermerModalEtapeBenef();

    if (window.EdenLoader) window.EdenLoader.show();

    fetch(`/admin/beneficiaires/${modalBenefId}/maj-etape`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({
            etape: modalEtapeBenefKey,
            date: null,
            active: false
        }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            afficherNotification('🗑️ Étape supprimée !', '#dc2626');
            setTimeout(() => location.reload(), 800);
        } else {
            afficherNotification(data.message || 'Erreur', '#dc2626');
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        afficherNotification('Erreur réseau : ' + e.message, '#dc2626');
    });
}

// ════════════════════════════════════════════════════════════════
// DEMANDER RÉFÉRENCE
// ════════════════════════════════════════════════════════════════
function demanderReferenceCreate(dossierId) {
    const reference = prompt('🔑 Entrez votre numéro de référence (signature) :');
    
    if (reference === null) {
        return false;
    }
    
    const ref = reference.trim();
    
    if (ref === '') {
        alert('⚠️ La référence ne peut pas être vide.');
        return false;
    }
    
    fetch('/admin/verifier-reference', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({ reference: ref })
    })
    .then(response => response.json())
    .then(data => {
        if (data.existe) {
            window.location.href = '/admin/bons/' + dossierId + '/creer?reference=' + encodeURIComponent(ref);
        } else {
            alert('❌ La référence "' + ref + '" n\'existe pas.');
        }
    })
    .catch(error => {
        alert('⚠️ Erreur de vérification. Réessayez.');
    });
}

function showDossier(id, tab) {
    document.querySelectorAll('.dossier-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.dossier-tab').forEach(t => t.classList.remove('active'));
    document.getElementById(id).style.display = 'block';
    tab.classList.add('active');
}

// ════════════════════════════════════════════════════════════════
// PRIX (superficie, technique, logistique, morcellement)
// ════════════════════════════════════════════════════════════════
function majPrix(dossierId) {
    const superficie   = document.getElementById('prix-superficie-'   + dossierId)?.value ?? '';
    const technique    = document.getElementById('prix-technique-'    + dossierId)?.value ?? '';
    const morcellement = document.getElementById('prix-morcellement-' + dossierId)?.value ?? '';
    const logistique   = document.getElementById('prix-logistique-'   + dossierId)?.value;

    if (window.EdenLoader) window.EdenLoader.show();

    fetch(`/admin/dossiers/${dossierId}/maj-prix`, {
        method:  'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
        body: JSON.stringify({
            prix_superficie:   superficie   !== '' ? parseFloat(superficie)   : null,
            prix_technique:    technique    !== '' ? parseFloat(technique)    : null,
            prix_morcellement: morcellement !== '' ? parseFloat(morcellement) : null,
            prix_logistique:   logistique,
        }),
    })
    .then(r => {
        if (!r.ok) throw new Error('Erreur HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();

        if (data.success) {
            location.reload();
        } else {
            alert(data.message || 'Erreur lors de la mise à jour des prix');
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        alert('Erreur réseau : ' + e.message);
    });
}

// ============================================================
// AFFECTATION DES LOTS AU DOSSIER
// ============================================================
function affChargerSites(gsId, dossierId) {
    const sel = document.getElementById('aff-site-' + dossierId);
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('aff-tf-'   + dossierId).innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('aff-bloc-' + dossierId).innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('aff-lots-container-' + dossierId).innerHTML = '';
    if (!gsId) return;
    fetch(`/admin/affectations/api/sites/${gsId}`)
        .then(r=>r.json()).then(sites => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                sites.map(s=>`<option value="${s.id}">${s.name}</option>`).join('');
        });
}

function affChargerTfs(siteId, dossierId) {
    const sel = document.getElementById('aff-tf-' + dossierId);
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('aff-bloc-' + dossierId).innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('aff-lots-container-' + dossierId).innerHTML = '';
    if (!siteId) return;
    fetch(`/admin/affectations/api/tfs/${siteId}`)
        .then(r=>r.json()).then(tfs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                tfs.map(t=>`<option value="${t.id}">${t.title}</option>`).join('');
        });
}

function affChargerBlocs(tfId, dossierId) {
    const sel = document.getElementById('aff-bloc-' + dossierId);
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('aff-lots-container-' + dossierId).innerHTML = '';
    if (!tfId) return;
    fetch(`/admin/affectations/api/blocs/${tfId}`)
        .then(r=>r.json()).then(blocs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                blocs.map(b=>`<option value="${b.id}">Bloc ${b.code}</option>`).join('');
        });
}

function affChargerLots(blocId, dossierId) {
    const container = document.getElementById('aff-lots-container-' + dossierId);
    const btn       = document.getElementById('aff-btn-' + dossierId);
    const compteur  = document.getElementById('aff-compteur-' + dossierId);
    container.innerHTML = '';
    btn.disabled = true;
    compteur.textContent = '0 lot(s) sélectionné(s)';
    if (!blocId) {
        container.innerHTML = '<div style="color:#94a3b8;font-size:12px;padding:4px 0;">ℹ️ Sélectionnez un Grand Site, Site, TF et Bloc pour voir les lots disponibles</div>';
        return;
    }

    fetch(`/admin/affectations/api/lots/${blocId}`)
        .then(r=>r.json()).then(lots => {
            if (!lots.length) {
                container.innerHTML = '<div style="color:#94a3b8;font-size:12px;padding:4px 0;">Aucun lot disponible dans ce bloc.</div>';
                return;
            }
            container.innerHTML = `
                <div style="font-size:11px;font-weight:700;color:#64748b;margin-bottom:6px;">
                    Lots disponibles — cochez ceux à affecter :
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;" id="aff-checkboxes-${dossierId}">
                    ${lots.map(l => `
                        <label style="display:inline-flex;align-items:center;gap:4px;background:white;border:2px solid #e2e8f0;border-radius:6px;padding:4px 10px;cursor:pointer;font-size:11px;font-weight:600;transition:0.15s;"
                               id="label-lot-${l.id}">
                            <input type="checkbox" value="${l.id}" class="aff-lot-cb-${dossierId}"
                                   style="width:14px;height:14px;"
                                   onchange="majBoutonAff(${dossierId}); toggleLotLabel(${l.id})">
                            Lot ${l.numero}
                            ${l.superficie ? '<span style="color:#64748b;font-size:9px;">' + parseInt(l.superficie).toLocaleString('fr-FR') + ' m²</span>' : ''}
                        </label>
                    `).join('')}
                </div>
            `;
        });
}

function toggleLotLabel(lotId) {
    const label = document.getElementById('label-lot-' + lotId);
    const cb    = label.querySelector('input[type=checkbox]');
    if (cb.checked) {
        label.style.background     = '#dcfce7';
        label.style.borderColor    = '#16a34a';
        label.style.color          = '#16a34a';
    } else {
        label.style.background     = 'white';
        label.style.borderColor    = '#e2e8f0';
        label.style.color          = '';
    }
}

function majBoutonAff(dossierId) {
    const cbs = document.querySelectorAll('.aff-lot-cb-' + dossierId + ':checked');
    const compteur = document.getElementById('aff-compteur-' + dossierId);
    document.getElementById('aff-btn-' + dossierId).disabled = cbs.length === 0;
    compteur.textContent = cbs.length + ' lot(s) sélectionné(s)';
}

function validerAffectation(dossierId) {
    const cbs   = document.querySelectorAll('.aff-lot-cb-' + dossierId + ':checked');
    const lotIds= Array.from(cbs).map(cb => cb.value);
    const date  = document.getElementById('aff-date-'  + dossierId).value;
    const notes = document.getElementById('aff-notes-' + dossierId).value;

    if (!lotIds.length) { alert('Sélectionnez au moins un lot.'); return; }
    if (!date) { alert('Date obligatoire.'); return; }

    if (window.EdenLoader) window.EdenLoader.show();

    fetch(`/admin/affectations/affecter/${dossierId}`, {
        method:  'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    JSON.stringify({ lot_ids: lotIds, date_affectation: date, notes }),
    })
    .then(r=>r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Erreur');
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        alert('Erreur réseau : ' + e.message);
    });
}

function annulerGroupeAffectation(ids, btn) {
    if (!confirm('Supprimer toutes ces affectations ? Les lots redeviendront disponibles.')) return;
    
    const idArray = ids.split(',');
    const promises = idArray.map(id => {
        return fetch(`/admin/affectations/${id}`, {
            method: 'DELETE',
            headers: { 
                'X-CSRF-TOKEN': CSRF, 
                'Content-Type':'application/json' 
            },
        }).then(r => r.json());
    });
    
    Promise.all(promises)
        .then(results => {
            if (results.every(r => r.success)) {
                btn.closest('[style*="background:#f0fdf4"]').remove();
                showToast('✅ Affectations annulées');
            } else {
                alert('Erreur lors de la suppression');
            }
        })
        .catch(e => alert('Erreur réseau : ' + e.message));
}

// ============================================================
// AFFECTATION DES LOTS AU BÉNÉFICIAIRE
// ============================================================
function benefAffChargerSites(gsId, benefId) {
    const sel = document.getElementById('benef-aff-site-' + benefId);
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-tf-'   + benefId).innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-bloc-' + benefId).innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-lots-container-' + benefId).innerHTML = '';
    if (!gsId) return;
    fetch(`/admin/affectations/api/sites/${gsId}`)
        .then(r => r.json()).then(sites => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                sites.map(s => `<option value="${s.id}">${s.name}</option>`).join('');
        });
}

function benefAffChargerTfs(siteId, benefId) {
    const sel = document.getElementById('benef-aff-tf-' + benefId);
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-bloc-' + benefId).innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-lots-container-' + benefId).innerHTML = '';
    if (!siteId) return;
    fetch(`/admin/affectations/api/tfs/${siteId}`)
        .then(r => r.json()).then(tfs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                tfs.map(t => `<option value="${t.id}">${t.title}</option>`).join('');
        });
}

function benefAffChargerBlocs(tfId, benefId) {
    const sel = document.getElementById('benef-aff-bloc-' + benefId);
    sel.innerHTML = '<option value="">-- Choisir --</option>';
    document.getElementById('benef-aff-lots-container-' + benefId).innerHTML = '';
    if (!tfId) return;
    fetch(`/admin/affectations/api/blocs/${tfId}`)
        .then(r => r.json()).then(blocs => {
            sel.innerHTML = '<option value="">-- Choisir --</option>' +
                blocs.map(b => `<option value="${b.id}">Bloc ${b.code}</option>`).join('');
        });
}

function benefAffChargerLots(blocId, benefId) {
    const container = document.getElementById('benef-aff-lots-container-' + benefId);
    const btn       = document.getElementById('benef-aff-btn-' + benefId);
    const compteur  = document.getElementById('benef-aff-compteur-' + benefId);
    container.innerHTML = '';
    btn.disabled = true;
    compteur.textContent = '0 lot(s) sélectionné(s)';
    if (!blocId) return;

    fetch(`/admin/affectations/api/lots/${blocId}`)
        .then(r => r.json()).then(lots => {
            if (!lots.length) {
                container.innerHTML = '<div style="color:#94a3b8;font-size:11px;padding:4px 0;">Aucun lot disponible dans ce bloc.</div>';
                return;
            }
            container.innerHTML = `
                <div style="font-size:10px;font-weight:700;color:#64748b;margin-bottom:6px;">
                    Lots disponibles — cochez ceux à affecter :
                </div>
                <div style="display:flex;flex-wrap:wrap;gap:6px;">
                    ${lots.map(l => `
                        <label style="display:inline-flex;align-items:center;gap:4px;background:white;border:2px solid #e2e8f0;border-radius:6px;padding:3px 8px;cursor:pointer;font-size:10px;font-weight:600;"
                               id="benef-label-lot-${benefId}-${l.id}">
                            <input type="checkbox" value="${l.id}" class="benef-aff-lot-cb-${benefId}"
                                   style="width:13px;height:13px;"
                                   onchange="benefMajBoutonAff(${benefId}); benefToggleLotLabel(${benefId}, ${l.id})">
                            Lot ${l.numero}
                            ${l.superficie ? '<span style="color:#64748b;font-size:9px;">' + parseInt(l.superficie).toLocaleString('fr-FR') + ' m²</span>' : ''}
                        </label>
                    `).join('')}
                </div>
            `;
        });
}

function benefToggleLotLabel(benefId, lotId) {
    const label = document.getElementById('benef-label-lot-' + benefId + '-' + lotId);
    const cb    = label.querySelector('input[type=checkbox]');
    if (cb.checked) {
        label.style.background  = '#dcfce7';
        label.style.borderColor = '#16a34a';
        label.style.color       = '#16a34a';
    } else {
        label.style.background  = 'white';
        label.style.borderColor = '#e2e8f0';
        label.style.color       = '';
    }
}

function benefMajBoutonAff(benefId) {
    const cbs = document.querySelectorAll('.benef-aff-lot-cb-' + benefId + ':checked');
    const compteur = document.getElementById('benef-aff-compteur-' + benefId);
    document.getElementById('benef-aff-btn-' + benefId).disabled = cbs.length === 0;
    compteur.textContent = cbs.length + ' lot(s) sélectionné(s)';
}

function validerBenefAffectation(benefId) {
    const cbs   = document.querySelectorAll('.benef-aff-lot-cb-' + benefId + ':checked');
    const lotIds= Array.from(cbs).map(cb => cb.value);
    const date  = document.getElementById('benef-aff-date-'  + benefId).value;
    const notes = document.getElementById('benef-aff-notes-' + benefId).value;

    if (!lotIds.length) { alert('Sélectionnez au moins un lot.'); return; }
    if (!date) { alert('Date obligatoire.'); return; }

    if (window.EdenLoader) window.EdenLoader.show();

    fetch(`/admin/beneficiaires/${benefId}/affecter-lots`, {
        method:  'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
        body:    JSON.stringify({ lot_ids: lotIds, date_affectation: date, notes }),
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            alert(data.message);
            location.reload();
        } else {
            alert(data.message || 'Erreur');
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        alert('Erreur réseau : ' + e.message);
    });
}

function annulerGroupeBenefAffectation(ids, btn) {
    if (!confirm('Supprimer toutes ces affectations ? Les lots redeviendront disponibles.')) return;

    const idArray = ids.split(',');
    const promises = idArray.map(id => {
        return fetch(`/admin/affectations/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Content-Type':'application/json'
            },
        }).then(r => r.json());
    });

    Promise.all(promises)
        .then(results => {
            if (results.every(r => r.success)) {
                btn.closest('[style*="background:#f0fdf4"]').remove();
                showToast('✅ Affectations annulées');
            } else {
                alert('Erreur lors de la suppression');
            }
        })
        .catch(e => alert('Erreur réseau : ' + e.message));
}

// ============================================================
// CNI
// ============================================================
function previewCni(input) {
    const preview = document.getElementById('cni-preview');
    preview.innerHTML = '';
    Array.from(input.files).forEach(file => {
        const div = document.createElement('div');
        div.style.cssText = 'position:relative;';
        if (file.type.startsWith('image/')) {
            const img   = document.createElement('img');
            img.src     = URL.createObjectURL(file);
            img.style.cssText = 'width:80px;height:80px;object-fit:cover;border-radius:8px;border:2px solid #e2e8f0;';
            div.appendChild(img);
        } else {
            div.innerHTML = `<div style="width:80px;height:80px;background:#fee2e2;border-radius:8px;border:2px solid #fca5a5;display:flex;align-items:center;justify-content:center;font-size:24px;">📄</div>`;
        }
        const name = document.createElement('div');
        name.innerText = file.name.substring(0,15) + '...';
        name.style.cssText = 'font-size:9px;color:#64748b;text-align:center;margin-top:3px;max-width:80px;overflow:hidden;';
        div.appendChild(name);
        preview.appendChild(div);
    });
}

function voirCni(dossierId) {
    fetch(`/admin/dossiers/${dossierId}/cni`)
        .then(response => response.json())
        .then(data => {
            if (!data.cni || data.cni.length === 0) {
                alert('Aucune CNI disponible pour ce dossier.');
                return;
            }
            
            const overlay = document.createElement('div');
            overlay.style.cssText = `
                position:fixed; inset:0; background:rgba(0,0,0,0.6); 
                z-index:99999; display:flex; align-items:center; 
                justify-content:center; padding:20px;
            `;
            
            const modal = document.createElement('div');
            modal.style.cssText = `
                background:white; border-radius:16px; padding:24px; 
                max-width:800px; max-height:90vh; overflow-y:auto;
                box-shadow:0 20px 60px rgba(0,0,0,0.3);
                width:100%;
            `;
            
            modal.innerHTML = `
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                    <h4 style="margin:0; color:#1e3a5f;">📎 CNI du dossier</h4>
                    <button onclick="this.closest('[style*=\\"position:fixed\\"]').remove()" 
                            style="background:none;border:none;font-size:24px;cursor:pointer;color:#94a3b8;">
                        ✕
                    </button>
                </div>
                <div style="display:flex; flex-wrap:wrap; gap:12px; justify-content:center;">
                    ${data.cni.map(img => `
                        <div style="width:200px; border-radius:8px; overflow:hidden; border:1px solid #e2e8f0; box-shadow:0 2px 8px rgba(0,0,0,0.06);">
                            <img src="/storage/${img}" 
                                 alt="CNI" 
                                 style="width:100%; height:auto; display:block; cursor:pointer;"
                                 onclick="window.open('/storage/${img}', '_blank')"
                                 title="Cliquez pour agrandir">
                            <div style="padding:6px 10px; font-size:10px; color:#64748b; background:#f8fafc; text-overflow:ellipsis; overflow:hidden; white-space:nowrap;">
                                ${img.split('/').pop()}
                            </div>
                        </div>
                    `).join('')}
                </div>
                <div style="text-align:center; margin-top:16px; font-size:11px; color:#94a3b8;">
                    Cliquez sur une image pour l'agrandir
                </div>
            `;
            
            overlay.appendChild(modal);
            document.body.appendChild(overlay);
            
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    overlay.remove();
                }
            });
            
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    const overlayEl = document.querySelector('[style*="position:fixed; inset:0; background:rgba(0,0,0,0.6);"]');
                    if (overlayEl) overlayEl.remove();
                }
            });
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors du chargement des CNI.');
        });
}

// ════════════════════════════════════════════════════════════════
// BÉNÉFICIAIRES — CRUD
// ════════════════════════════════════════════════════════════════
let benefEditId = null;

function ouvrirModalBenef(dossierId, benef) {
    benefEditId = benef?.id ?? null;

    document.getElementById('benefId').value         = benef?.id ?? '';
    document.getElementById('benefDossierId').value  = dossierId;
    document.getElementById('benefNom').value        = benef?.nom ?? '';
    document.getElementById('benefTelephone').value  = benef?.telephone ?? '';
    document.getElementById('benefLots').value       = benef?.lots_texte ?? '';
    document.getElementById('benefSuperficie').value = benef?.superficie_attribuee ?? '';
    document.getElementById('benefNotes').value      = benef?.notes ?? '';

    document.getElementById('benefTitre').textContent =
        benef ? '✏️ Modifier un bénéficiaire' : '👥 Ajouter un bénéficiaire';

    document.getElementById('benefCniOblig').style.display =
        benef ? 'none' : 'inline';
    document.getElementById('benefCni').required = !benef;

    const cniActuelle = document.getElementById('benefCniActuelle');
    if (benef?.cni_url) {
        cniActuelle.innerHTML =
            `📎 <a href="${benef.cni_url}" target="_blank">Voir la CNI actuelle</a>
             <span style="color:#94a3b8;"> (laisser vide pour conserver)</span>`;
    } else {
        cniActuelle.innerHTML = '';
    }

    const card = document.querySelector(`#bloc-beneficiaires-${dossierId}`);
    const hint = card?.querySelector('div[style*="color:#16a34a"], div[style*="color:#dc2626"]')
                    ?.textContent.trim() ?? '';
    document.getElementById('benefSuperficieHint').textContent =
        'Superficie restante : ' + hint;

    document.getElementById('benefOverlay').style.display = 'block';
    document.getElementById('benefModal').style.display   = 'block';
}

function fermerModalBenef() {
    document.getElementById('benefOverlay').style.display = 'none';
    document.getElementById('benefModal').style.display   = 'none';
    benefEditId = null;
}

function sauvegarderBenef() {
    const dossierId  = document.getElementById('benefDossierId').value;
    const nom        = document.getElementById('benefNom').value.trim();
    const superficie = document.getElementById('benefSuperficie').value;
    const cniFile    = document.getElementById('benefCni').files[0];

    if (!nom)        { showToast('⚠️ Le nom est obligatoire'); return; }
    if (!superficie) { showToast('⚠️ La superficie est obligatoire'); return; }
    if (!benefEditId && !cniFile) {
        showToast('⚠️ La CNI est obligatoire'); return;
    }

    const formData = new FormData();
    formData.append('nom', nom);
    formData.append('telephone', document.getElementById('benefTelephone').value);
    formData.append('lots_texte', document.getElementById('benefLots').value);
    formData.append('superficie_attribuee', superficie);
    formData.append('notes', document.getElementById('benefNotes').value);
    if (cniFile) formData.append('cni', cniFile);

    const url = benefEditId
        ? `/admin/beneficiaires/${benefEditId}`
        : `/admin/dossiers/${dossierId}/beneficiaires`;

    if (benefEditId) formData.append('_method', 'PUT');

    if (window.EdenLoader) window.EdenLoader.show();

    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
        body: formData,
    })
    .then(r => r.json().then(data => ({ status: r.status, data })))
    .then(({ status, data }) => {
        if (window.EdenLoader) window.EdenLoader.hide();

        if (data.success) {
            fermerModalBenef();
            showToast(data.message);
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || 'Erreur');
        }
    })
    .catch(err => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau : ' + err.message);
    });
}

function supprimerBenef(benefId, dossierId) {
    if (!confirm('Supprimer ce bénéficiaire ?')) return;

    if (window.EdenLoader) window.EdenLoader.show();

    fetch(`/admin/beneficiaires/${benefId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        if (data.success) {
            showToast(data.message);
            document.getElementById('benef-' + benefId)?.remove();
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message || 'Erreur');
        }
    })
    .catch(err => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau : ' + err.message);
    });
}

// ════════════════════════════════════════════════════════════════
// HISTORIQUE
// ════════════════════════════════════════════════════════════════
function toggleHistorique(dossierId) {
    const content = document.getElementById('historique-content-' + dossierId);
    const btnText = document.getElementById('btn-histo-texte-' + dossierId);

    if (!content) return;

    if (content.style.display === 'none' || content.style.display === '') {
        content.style.display = 'block';
        btnText.textContent   = 'Masquer';
    } else {
        content.style.display = 'none';
        btnText.textContent   = 'Afficher';
    }
}

</script>
@endsection