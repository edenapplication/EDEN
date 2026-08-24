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

@media (max-width: 992px) {
    .paiement-blocs { grid-template-columns:1fr; }
}
</style>


<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('suivi-client.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
        <h2 class="d-inline ms-2">👤 {{ $client->name }}</h2>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('suivi-client.create') }}?client_id={{ $client->id }}"
           class="btn btn-primary btn-sm">📂 Nouveau dossier</a>
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
        </div>

        {{-- ✅ LOTS AFFECTÉS (GROUPÉS PAR BLOC) --}}
@if($affectations->count() > 0)
<div style="background:white;border-radius:12px;padding:16px;margin-top:14px;border-left:4px solid #16a34a;">
    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:10px;">
        📦 Lots affectés ({{ $affectations->count() }})
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

{{-- ✅ FORMULAIRE AFFECTATION LINÉAIRE --}}
<div style="background:#f8fafc;border-radius:12px;padding:16px;margin-top:14px;border:1px solid #e2e8f0;">
    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:12px;">
        ➕ Affecter des lots à ce dossier
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

{{-- ============================================================
     BLOC ÉTAPES DU DOSSIER - VERSION 4 (AVEC MODAL)
     ============================================================ --}}
@php
    $etapesConfig = \App\Models\DossierClient::etapesConfig();
    $etapesOrdre  = \App\Models\DossierClient::etapesOrdre();
    $etapeActuelle= $dossier->etape_actuelle;
@endphp

<div style="background:#f8fafc;border-radius:12px;padding:16px;margin-top:14px;border:1px solid #e2e8f0;">
    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:10px;">
        📊 Étapes du dossier
    </div>

    {{-- LIGNE DES 4 ÉTAPES (cliquables) --}}
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

                {{-- ============================================================
                     ✅ TROIS BLOCS DE PAIEMENT DISTINCTS
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
            </div>
        </div>
        @endforeach
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
     MODAL PAIEMENT — réutilisé pour les 3 types
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
     MODAL POUR LA DATE DES ÉTAPES
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

@endsection

@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
let currentDossierId = null;
let currentType       = 'dossier';

const TYPE_CONFIG = {
    dossier:      { url: '/admin/paiements-dossier',      titre: '📁 Paiement Parcelle — ',      btnColor: 'btn-success' },
    technique:    { url: '/admin/paiements-technique',    titre: '🛠️ Paiement Dossier Technique — ',    btnColor: 'btn-success' },
    morcellement: { url: '/admin/paiements-morcellement', titre: '✂️ Paiement Morcellement — ',  btnColor: 'btn-success' },
};
// ============================================================
// GESTION DES ÉTAPES - VERSION 4 (AVEC MODAL + TOAST)
// ============================================================
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
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content 
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
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content 
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
            // Reconstruire le contenu
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
            
            // Réattacher l'événement onclick
            card.onclick = function() {
                ouvrirModalEtape(dossierId, cle, cfg['label'], estFait, data.dates[cle] ? data.dates[cle].split('/').reverse().join('-') : '');
            };
        }
    });
}

// ============================================================
// NOTIFICATION TOAST
// ============================================================
function afficherNotification(message, couleur = '#16a34a') {
    // Supprimer les notifications existantes
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
    
    // Animation d'entrée
    setTimeout(() => {
        toast.style.transform = 'translateX(0)';
        toast.style.opacity = '1';
    }, 50);
    
    // Disparition automatique après 3 secondes
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(50px)';
        setTimeout(() => {
            if (toast.parentNode) toast.remove();
        }, 400);
    }, 3000);
}

// ============================================================
// DEMANDER RÉFÉRENCE
// ============================================================
function demanderReferenceCreate(dossierId) {
    const reference = prompt('🔑 Entrez votre numéro de référence (signature) :');
    
    if (reference === null) {
        return false;
    }
    
    const ref = reference.trim();
    
    console.log('🔍 Référence saisie:', ref);
    console.log('🔍 Longueur:', ref.length);
    console.log('🔍 Caractères:', ref.split('').map(c => c.charCodeAt(0)));

    if (ref === '') {
        alert('⚠️ La référence ne peut pas être vide.');
        return false;
    }
    
    const token = document.querySelector('meta[name="csrf-token"]')?.content;
    
    fetch('/admin/verifier-reference', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token
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

function openPaiement(type, dossierId, nom) {
    currentDossierId = dossierId;
    currentType       = type;
    const cfg = TYPE_CONFIG[type];

    document.getElementById('paiementTitre').innerHTML = cfg.titre + '<span id="dossierNom">' + nom + '</span>';
    document.getElementById('montant').value  = '';
    document.getElementById('datePaie').value = '';
    document.getElementById('note').value     = '';
    document.getElementById('paiementOverlay').style.display = 'block';
    document.getElementById('paiementModal').style.display   = 'block';
}

function closePaiement() {
    document.getElementById('paiementOverlay').style.display = 'none';
    document.getElementById('paiementModal').style.display   = 'none';
}

function savePaiement() {
    const cfg     = TYPE_CONFIG[currentType];
    const montant = document.getElementById('montant').value;
    const date    = document.getElementById('datePaie').value;

    if (!montant || !date) { alert('Montant et date obligatoires.'); return; }

    closePaiement();

    if (window.EdenLoader) window.EdenLoader.show();

    fetch(`${cfg.url}/${currentDossierId}`, {
        method:  'POST',
        headers: {
            'Content-Type':  'application/json',
            'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]')?.content
                             || '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            montant:       parseFloat(montant),
            date_paiement: date,
            note:          document.getElementById('note').value,
        }),
    })
    .then(r => {
        if (!r.ok) throw new Error('Erreur HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            if (window.EdenLoader) window.EdenLoader.hide();
            alert(data.message || 'Erreur lors du paiement');
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        alert('Erreur réseau : ' + e.message);
    });
}

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
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            || CSRF,
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
// AFFECTATION DES LOTS
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

function annulerAffectation(affId, btn) {
    if (!confirm('Annuler cette affectation ? Le lot redeviendra disponible.')) return;
    fetch(`/admin/affectations/${affId}`, {
        method:  'DELETE',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type':'application/json' },
    })
    .then(r=>r.json())
    .then(data => {
        if (data.success) btn.closest('[style*="background:#f0fdf4"]').remove();
        else alert(data.message || 'Erreur');
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
            } else {
                alert('Erreur lors de la suppression');
            }
        })
        .catch(e => alert('Erreur réseau : ' + e.message));
}

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

// ============================================================
// VOIR LES CNI DU DOSSIER
// ============================================================
function voirCni(dossierId) {
    // Récupérer les CNI depuis le serveur
    fetch(`/admin/dossiers/${dossierId}/cni`)
        .then(response => response.json())
        .then(data => {
            if (!data.cni || data.cni.length === 0) {
                alert('Aucune CNI disponible pour ce dossier.');
                return;
            }
            
            // Créer la modal
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
            
            // Fermer en cliquant à l'extérieur
            overlay.addEventListener('click', function(e) {
                if (e.target === overlay) {
                    overlay.remove();
                }
            });
            
            // Fermer avec la touche Echap
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

</script>
@endsection