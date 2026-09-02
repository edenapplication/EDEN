@extends('admin.layout')
@section('content')

<style>
.client-card {
    background:white; border-radius:12px; padding:16px; margin-bottom:10px;
    box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #1d4ed8; transition:0.2s;
}
.client-card:hover { box-shadow:0 6px 20px rgba(0,0,0,0.1); transform:translateY(-2px); }
.client-card.selected {
    border-left-color: #16a34a;
    background: #f0fdf4;
}

.filtre-box { background:white; border-radius:12px; padding:14px; margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
.dossier-pill {
    display:inline-flex; align-items:center; gap:6px;
    background:#f1f5f9; border-radius:8px; padding:4px 10px;
    font-size:11px; color:#374151; margin-right:6px; margin-top:4px;
}
#search-live { font-size:15px; padding:10px 16px; border-radius:10px; border:2px solid #e2e8f0; }
#search-live:focus { border-color:#1d4ed8; outline:none; }
.highlight { background:#fef9c3; border-radius:3px; padding:0 2px; }

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
    margin-left: 8px;
}

.badge-old {
    background: #e2e8f0;
    color: #64748b;
    padding: 4px 12px;
    border-radius: 50px;
    font-size: 11px;
    font-weight: 600;
    display: inline-block;
    margin-left: 8px;
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

/* ═══ BARRE D'ACTIONS GROUPÉES ═══ */
.action-bar {
    background: white;
    border-radius: 12px;
    padding: 12px 16px;
    margin-bottom: 16px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    display: none;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
    border: 2px solid #16a34a;
    position: sticky;
    top: 0;
    z-index: 100;
}
.action-bar.visible {
    display: flex;
}
.action-bar .count {
    font-weight: 700;
    color: #16a34a;
    font-size: 14px;
}
.action-bar .btn-group {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}
.action-bar .btn {
    font-size: 12px;
    padding: 6px 14px;
    border-radius: 6px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: all 0.2s;
}
.action-bar .btn-primary { background: #1d4ed8; color: #fff; }
.action-bar .btn-primary:hover { background: #1e40af; }
.action-bar .btn-success { background: #16a34a; color: #fff; }
.action-bar .btn-success:hover { background: #15803d; }
.action-bar .btn-danger { background: #dc2626; color: #fff; }
.action-bar .btn-danger:hover { background: #b91c1c; }
.action-bar .btn-warning { background: #f59e0b; color: #fff; }
.action-bar .btn-warning:hover { background: #d97706; }
.action-bar .btn-outline { background: transparent; border: 1.5px solid #e2e8f0; color: #64748b; }
.action-bar .btn-outline:hover { background: #f1f5f9; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:500px; max-width:95%; }

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
.toast-notification.success { background: #16a34a; }
.toast-notification.error { background: #dc2626; }
.toast-notification.warning { background: #f59e0b; }

@keyframes slideInToast {
    from { transform: translateY(20px); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}

/* ═══ CHECKBOX PERSONNALISÉ ═══ */
.client-checkbox {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: #1d4ed8;
    margin-right: 8px;
    flex-shrink: 0;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">👤 Suivi Clients</h2>
        <div style="font-size:13px;color:#64748b;" id="compteur-clients">
            {{ $clients->count() }} client(s)
            <span style="margin-left:10px;color:#10b981;">
                🆕 {{ $clients->where('is_new', true)->count() }} nouveau(x)
            </span>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button onclick="selectionnerTout()" class="btn btn-outline-secondary btn-sm">
            ☑ Sélectionner tout
        </button>
        <button onclick="deselectionnerTout()" class="btn btn-outline-secondary btn-sm">
            ☐ Désélectionner
        </button>
        <a href="{{ route('dossiers.export-excel', request()->all()) }}" class="btn btn-success btn-sm">
            📥 Export Excel
        </a>
        <button onclick="exporterPdf()" class="btn btn-danger btn-sm" title="Exporter en PDF">
            📄 PDF
        </button>
        <a href="{{ route('suivi-client.create') }}" class="btn btn-primary btn-sm">+ Nouveau client</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ═══ BARRE D'ACTIONS GROUPÉES ═══ --}}
<div class="action-bar" id="actionBar">
    <span class="count" id="selectedCount">0</span>
    <span style="font-size:13px;color:#64748b;">client(s) sélectionné(s)</span>
    <div class="btn-group">
        <button class="btn btn-success" onclick="actionGroupee('mark_as_new')">
            🆕 Marquer nouveaux
        </button>
        <button class="btn btn-warning" onclick="actionGroupee('mark_as_old')">
            📌 Marquer anciens
        </button>
        <button class="btn btn-primary" onclick="actionGroupee('export_whatsapp')">
            💬 WhatsApp
        </button>
        <button class="btn btn-danger" onclick="actionGroupee('delete')">
            🗑 Supprimer
        </button>
        <button class="btn btn-outline" onclick="deselectionnerTout()">
            ✖ Annuler
        </button>
    </div>
</div>

{{-- ✅ FILTRES avec recherche dynamique --}}
<div class="filtre-box">
    <div class="row g-2 align-items-end">
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;">🔍 Recherche</label>
            <input type="text" id="search-live" class="form-control form-control-sm"
                   placeholder="Nom ou téléphone..."
                   value="{{ request('q') }}"
                   oninput="filtrerClients(this.value)">
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Statut</label>
            <select id="filtre-statut" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="new" {{ request('status') == 'new' ? 'selected' : '' }}>🆕 Nouveaux</option>
                <option value="old" {{ request('status') == 'old' ? 'selected' : '' }}>Anciens</option>
            </select>
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Du</label>
            <input type="date" name="du" id="filtre-du" class="form-control form-control-sm" value="{{ request('du') }}">
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Au</label>
            <input type="date" name="au" id="filtre-au" class="form-control form-control-sm" value="{{ request('au') }}">
        </div>
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Grand Site</label>
            <select id="filtre-site" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                @foreach($grandSites as $gs)
                    <option value="{{ $gs->id }}" {{ request('grand_site_id')==$gs->id?'selected':'' }}>
                        {{ $gs->nom }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ✅ FILTRE PAIEMENT TECHNIQUE SOLDÉ --}}
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">🛠️ Technique</label>
            <select id="filtre-technique" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="solde" {{ request('technique_solde') == 'solde' ? 'selected' : '' }}>✅ Soldé</option>
                <option value="non_solde" {{ request('technique_solde') == 'non_solde' ? 'selected' : '' }}>⏳ Non soldé</option>
            </select>
        </div>

        {{-- ✅ FILTRE PAIEMENT MORCELLEMENT SOLDÉ --}}
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">✂️ Morcellement</label>
            <select id="filtre-morcellement" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="solde" {{ request('morcellement_solde') == 'solde' ? 'selected' : '' }}>✅ Soldé</option>
                <option value="non_solde" {{ request('morcellement_solde') == 'non_solde' ? 'selected' : '' }}>⏳ Non soldé</option>
            </select>
        </div>

        {{-- ✅ FILTRE PAIEMENT DOSSIER SOLDÉ --}}
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">📁 Dossier</label>
            <select id="filtre-dossier" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="solde" {{ request('dossier_solde') == 'solde' ? 'selected' : '' }}>✅ Soldé</option>
                <option value="non_solde" {{ request('dossier_solde') == 'non_solde' ? 'selected' : '' }}>⏳ Non soldé</option>
            </select>
        </div>

        {{-- ✅ FILTRE PAIEMENT LOGISTIQUE SOLDÉ --}}
        <div class="col-md-1">
            <label style="font-size:11px;font-weight:700;color:#64748b;">🚗 Logistique</label>
            <select id="filtre-logistique" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous</option>
                <option value="solde" {{ request('logistique_solde') == 'solde' ? 'selected' : '' }}>✅ Soldé</option>
                <option value="non_solde" {{ request('logistique_solde') == 'non_solde' ? 'selected' : '' }}>⏳ Non soldé</option>
            </select>
        </div>

        <div class="col-md-1 d-flex gap-1">
            <button onclick="appliquerFiltresServeur()" class="btn btn-primary btn-sm">🔍</button>
            <a href="{{ route('suivi-client.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
    </div>
</div>

{{-- LISTE --}}
<div id="liste-clients">
    @forelse($clients as $client)
    <div class="client-card"
         data-nom="{{ strtolower($client->name) }}"
         data-phone="{{ $client->phone }}"
         data-is-new="{{ $client->is_new ? 'true' : 'false' }}"
         data-id="{{ $client->id }}">
        <div class="d-flex justify-content-between align-items-start">
            <div style="flex:1;display:flex;align-items:flex-start;gap:8px;">
                {{-- ═══ CHECKBOX DE SÉLECTION ═══ --}}
                <input type="checkbox" class="client-checkbox" 
                       onchange="toggleSelection(this, {{ $client->id }})"
                       data-client-id="{{ $client->id }}">
                       
                <div style="flex:1;">
                    {{-- Nom modifiable + Badge "Nouveau" --}}
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                        <div style="font-weight:700;font-size:15px;color:#1e3a5f;"
                             id="nom-{{ $client->id }}" class="client-nom">
                            {{ $client->name }}
                        </div>
                        
                        @if($client->is_new)
                            <span class="badge-new" id="badge-{{ $client->id }}">
                                🆕 Nouveau
                            </span>
                        @else
                            <span class="badge-old" id="badge-{{ $client->id }}">
                                Ancien
                            </span>
                        @endif
                        
                        <button onclick="ouvrirEditNom({{ $client->id }}, '{{ addslashes($client->name) }}')"
                                style="background:none;border:none;color:#f59e0b;cursor:pointer;font-size:13px;padding:2px 6px;"
                                title="Modifier le nom">✏️</button>
                        
                        <button class="btn-toggle-new {{ $client->is_new ? 'is-new' : '' }}"
                                onclick="toggleNew({{ $client->id }})"
                                id="btn-new-{{ $client->id }}">
                            @if($client->is_new)
                                ✅ Nouveau
                            @else
                                🔄 Marquer nouveau
                            @endif
                        </button>
                    </div>
                    
                    <div style="font-size:12px;color:#64748b;margin-top:2px;">
                        📞 {{ $client->phone ?? '-' }}
                        &nbsp;·&nbsp; 📂 <span id="nb-dossiers-{{ $client->id }}">{{ $client->dossiers->count() }}</span> dossier(s)
                        &nbsp;·&nbsp; 📅 {{ $client->created_at?->format('d/m/Y') ?? '-' }}
                    </div>

                    {{-- Dossiers sous forme de pills --}}
                    <div style="margin-top:6px;" id="dossiers-pills-{{ $client->id }}">
                        @foreach($client->dossiers as $d)
                        @php
                            $tD = $d->paiements->sum('montant');
                            $tT = $d->paiementsTechniques->sum('montant');
                            $tM = $d->paiementsMorcellements->sum('montant');
                            $tL = $d->paiementsLogistiques?->sum('montant') ?? 0;

                            $rD = $d->prix_superficie   ?? 0;
                            $rT = $d->prix_technique    ?? 0;
                            $rM = $d->prix_morcellement ?? 0;
                            $rL = $d->prix_logistique   ?? 0;

                            $statutD = $rD > 0 ? ($tD >= $rD ? 'solde' : ($tD > 0 ? 'en_cours' : 'vide')) : ($tD > 0 ? 'en_cours' : 'vide');
                            $statutT = $rT > 0 ? ($tT >= $rT ? 'solde' : ($tT > 0 ? 'en_cours' : 'vide')) : ($tT > 0 ? 'en_cours' : 'vide');
                            $statutM = $rM > 0 ? ($tM >= $rM ? 'solde' : ($tM > 0 ? 'en_cours' : 'vide')) : ($tM > 0 ? 'en_cours' : 'vide');
                            $statutL = $rL > 0 ? ($tL >= $rL ? 'solde' : ($tL > 0 ? 'en_cours' : 'vide')) : ($tL > 0 ? 'en_cours' : 'vide');

                            $couleurs = [
                                'solde' => ['bg' => '#dcfce7', 'border' => '#86efac', 'text' => '#15803d', 'icone' => '✅'],
                                'en_cours' => ['bg' => '#fef3c7', 'border' => '#fcd34d', 'text' => '#b45309', 'icone' => '⏳'],
                                'vide' => ['bg' => '#f1f5f9', 'border' => '#cbd5e1', 'text' => '#64748b', 'icone' => '⭕']
                            ];

                            $totalPaye = $tD + $tT + $tL + $tM;
                            $totalRef = $rD + $rT + $rL + $rM;
                            $tousSoldes = ($statutD === 'solde' || $tD == 0) && 
                                          ($statutT === 'solde' || $tT == 0) && 
                                          ($statutL === 'solde' || $tL == 0) && 
                                          ($statutM === 'solde' || $tM == 0);
                            $pctGlobal = $totalRef > 0 ? round(($totalPaye / $totalRef) * 100) : 0;
                        @endphp

                        <div class="dossier-pill" id="pill-dossier-{{ $d->id }}" style="display:inline-block;margin-bottom:8px;">
                            <div style="background:#f8fafc;border-radius:8px;padding:8px 12px;border:1px solid #e2e8f0;">
                                <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                                    <span class="site" style="font-weight:700;color:#1e3a5f;">
                                        {{ $d->grandSite?->nom ?? $d->nom_dossier }}
                                        <small style="color:#6b7280;font-weight:normal;">
                                            ({{ $d->created_at?->format('d/m/Y') ?? '-' }})
                                        </small>
                                    </span>
                                    <span style="color:#64748b;font-size:11px;">
                                        {{ $d->superficie_voulue ? number_format($d->superficie_voulue, 0, ',', ' ') . ' m²' : '-' }}
                                    </span>
                                </div>

                                <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:6px;">
                                    @if($tD > 0 || $rD > 0)
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:12px;font-size:9px;font-weight:700;background:{{ $couleurs[$statutD]['bg'] }};color:{{ $couleurs[$statutD]['text'] }};border:1.5px solid {{ $couleurs[$statutD]['border'] }};">
                                        <span style="font-size:10px;">{{ $couleurs[$statutD]['icone'] }}</span>
                                        📁 Dossier
                                        @if($statutD === 'solde')
                                            <span style="background:#15803d22;padding:0 6px;border-radius:8px;">SOLDÉ</span>
                                        @elseif($statutD === 'en_cours')
                                            <span>{{ $rD > 0 ? number_format(round(($tD/$rD)*100)) . '%' : 'payé' }}</span>
                                        @else
                                            <span style="color:#94a3b8;">non payé</span>
                                        @endif
                                    </span>
                                    @endif

                                    @if($tT > 0 || $rT > 0)
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:12px;font-size:9px;font-weight:700;background:{{ $couleurs[$statutT]['bg'] }};color:{{ $couleurs[$statutT]['text'] }};border:1.5px solid {{ $couleurs[$statutT]['border'] }};">
                                        <span style="font-size:10px;">{{ $couleurs[$statutT]['icone'] }}</span>
                                        🛠️ Tech.
                                        @if($statutT === 'solde')
                                            <span style="background:#15803d22;padding:0 6px;border-radius:8px;">SOLDÉ</span>
                                        @elseif($statutT === 'en_cours')
                                            <span>{{ $rT > 0 ? number_format(round(($tT/$rT)*100)) . '%' : 'payé' }}</span>
                                        @else
                                            <span style="color:#94a3b8;">non payé</span>
                                        @endif
                                    </span>
                                    @endif

                                    @if($tL > 0 || $rL > 0)
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:12px;font-size:9px;font-weight:700;background:{{ $couleurs[$statutL]['bg'] }};color:{{ $couleurs[$statutL]['text'] }};border:1.5px solid {{ $couleurs[$statutL]['border'] }};">
                                        <span style="font-size:10px;">{{ $couleurs[$statutL]['icone'] }}</span>
                                        🚗 Logi.
                                        @if($statutL === 'solde')
                                            <span style="background:#15803d22;padding:0 6px;border-radius:8px;">SOLDÉ</span>
                                        @elseif($statutL === 'en_cours')
                                            <span>{{ $rL > 0 ? number_format(round(($tL/$rL)*100)) . '%' : 'payé' }}</span>
                                        @else
                                            <span style="color:#94a3b8;">non payé</span>
                                        @endif
                                    </span>
                                    @endif

                                    @if($tM > 0 || $rM > 0)
                                    <span style="display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:12px;font-size:9px;font-weight:700;background:{{ $couleurs[$statutM]['bg'] }};color:{{ $couleurs[$statutM]['text'] }};border:1.5px solid {{ $couleurs[$statutM]['border'] }};">
                                        <span style="font-size:10px;">{{ $couleurs[$statutM]['icone'] }}</span>
                                        ✂️ Morcel.
                                        @if($statutM === 'solde')
                                            <span style="background:#15803d22;padding:0 6px;border-radius:8px;">SOLDÉ</span>
                                        @elseif($statutM === 'en_cours')
                                            <span>{{ $rM > 0 ? number_format(round(($tM/$rM)*100)) . '%' : 'payé' }}</span>
                                        @else
                                            <span style="color:#94a3b8;">non payé</span>
                                        @endif
                                    </span>
                                    @endif
                                </div>

                                @if($totalRef > 0)
                                <div style="margin-top:6px;padding:4px 10px;border-radius:6px;background: {{ $tousSoldes ? '#dcfce7' : ($totalPaye > 0 ? '#fef3c7' : '#f1f5f9') }};border: 1.5px solid {{ $tousSoldes ? '#86efac' : ($totalPaye > 0 ? '#fcd34d' : '#cbd5e1') }};display:flex;justify-content:space-between;align-items:center;font-size:10px;">
                                    <span style="font-weight:700;color:{{ $tousSoldes ? '#15803d' : ($totalPaye > 0 ? '#b45309' : '#64748b') }};">
                                        {{ $tousSoldes ? '✅ SOLDÉ ' : ($totalPaye > 0 ? '⏳ EN COURS ' : '⭕ NON PAYÉ ') }}
                                    </span>
                                    <span style="font-weight:900;color:{{ $tousSoldes ? '#15803d' : ($totalPaye > 0 ? '#b45309' : '#64748b') }};">
                                        @if($totalPaye > 0)
                                            {{ number_format($totalPaye, 0, ',', ' ') }} FCFA/ {{ number_format($totalRef, 0, ',', ' ') }} FCFA
                                            <span style="font-size:9px;">({{ $pctGlobal }}%)</span>
                                        @else
                                            0 FCFA
                                        @endif
                                    </span>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <a href="{{ route('suivi-client.show', $client->id) }}"
               class="btn btn-primary btn-sm ms-2" style="white-space:nowrap;">Voir →</a>
        </div>
    </div>
    @empty
    <div style="text-align:center;padding:40px;color:#94a3b8;">
        <div style="font-size:40px;">📭</div>
        <div style="font-weight:700;margin-top:10px;">Aucun client trouvé</div>
    </div>
    @endforelse
</div>

{{-- MODAL MODIFIER NOM --}}
<div class="modal-overlay" id="overlayNom" onclick="fermerEditNom()"></div>
<div class="modal-box" id="modalNom">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;">✏️ Modifier le nom</h5>
        <button onclick="fermerEditNom()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <input type="text" id="input-nouveau-nom" class="form-control mb-3" placeholder="Nouveau nom du client">
    <div class="d-flex justify-content-end gap-2">
        <button onclick="fermerEditNom()" class="btn btn-light">Annuler</button>
        <button onclick="sauvegarderNom()" class="btn btn-warning">💾 Enregistrer</button>
    </div>
</div>

{{-- MODAL CONFIRMATION ACTION --}}
<div class="modal-overlay" id="modalConfirmationOverlay" onclick="fermerModalConfirmation()"></div>
<div class="modal-box" id="modalConfirmation">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;" id="modalConfirmationTitre">⚠️ Confirmation</h5>
        <button onclick="fermerModalConfirmation()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    <p id="modalConfirmationMessage" style="font-size:14px;color:#64748b;"></p>
    <div id="modalConfirmationListe" style="max-height:200px;overflow-y:auto;margin:12px 0;"></div>
    <div class="d-flex justify-content-end gap-2">
        <button onclick="fermerModalConfirmation()" class="btn btn-light">Annuler</button>
        <button onclick="executerActionConfirmee()" class="btn btn-danger" id="modalConfirmationBtn">Confirmer</button>
    </div>
</div>

@endsection

@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
let clientIdCourant = null;
let selectedClients = new Set();

// ════════════════════════════════════════════════════════════════
// ✅ EXPORT PDF
// ════════════════════════════════════════════════════════════════

function exporterPdf() {
    const params = new URLSearchParams(window.location.search);
    const q = document.getElementById('search-live')?.value || '';
    const du = document.getElementById('filtre-du')?.value || '';
    const au = document.getElementById('filtre-au')?.value || '';
    const site = document.getElementById('filtre-site')?.value || '';
    const statut = document.getElementById('filtre-statut')?.value || '';
    const technique = document.getElementById('filtre-technique')?.value || '';
    const morcellement = document.getElementById('filtre-morcellement')?.value || '';
    const dossier = document.getElementById('filtre-dossier')?.value || '';
    const logistique = document.getElementById('filtre-logistique')?.value || '';
    
    let url = '{{ route("suivi-client.export-pdf") }}?';
    if (q) url += 'q=' + encodeURIComponent(q) + '&';
    if (du) url += 'du=' + du + '&';
    if (au) url += 'au=' + au + '&';
    if (site) url += 'grand_site_id=' + site + '&';
    if (statut) url += 'status=' + statut + '&';
    if (technique) url += 'technique_solde=' + technique + '&';
    if (morcellement) url += 'morcellement_solde=' + morcellement + '&';
    if (dossier) url += 'dossier_solde=' + dossier + '&';
    if (logistique) url += 'logistique_solde=' + logistique + '&';
    
    window.open(url, '_blank');
}

// ════════════════════════════════════════════════════════════════
// ✅ GESTION DES SÉLECTIONS
// ════════════════════════════════════════════════════════════════

function toggleSelection(checkbox, clientId) {
    if (checkbox.checked) {
        selectedClients.add(clientId);
        document.querySelector(`.client-card[data-id="${clientId}"]`)?.classList.add('selected');
    } else {
        selectedClients.delete(clientId);
        document.querySelector(`.client-card[data-id="${clientId}"]`)?.classList.remove('selected');
    }
    mettreAJourActionBar();
}

function selectionnerTout() {
    document.querySelectorAll('.client-card:not([style*="display: none"])').forEach(card => {
        const cb = card.querySelector('.client-checkbox');
        if (cb) {
            cb.checked = true;
            const id = parseInt(cb.dataset.clientId);
            selectedClients.add(id);
            card.classList.add('selected');
        }
    });
    mettreAJourActionBar();
}

function deselectionnerTout() {
    document.querySelectorAll('.client-checkbox').forEach(cb => {
        cb.checked = false;
        const id = parseInt(cb.dataset.clientId);
        selectedClients.delete(id);
        document.querySelector(`.client-card[data-id="${id}"]`)?.classList.remove('selected');
    });
    mettreAJourActionBar();
}

function mettreAJourActionBar() {
    const bar = document.getElementById('actionBar');
    const count = selectedClients.size;
    document.getElementById('selectedCount').textContent = count;
    
    if (count > 0) {
        bar.classList.add('visible');
    } else {
        bar.classList.remove('visible');
    }
}

// ════════════════════════════════════════════════════════════════
// ✅ ACTIONS GROUPÉES
// ════════════════════════════════════════════════════════════════

function actionGroupee(action) {
    const ids = Array.from(selectedClients);
    if (ids.length === 0) {
        showToast('⚠️ Aucun client sélectionné', 'warning');
        return;
    }

    const actionsMessages = {
        'mark_as_new': { 
            title: '🆕 Marquer comme nouveaux', 
            message: `Êtes-vous sûr de vouloir marquer ${ids.length} client(s) comme NOUVEAUX ?`,
            btnText: 'Marquer',
            btnClass: 'btn-success'
        },
        'mark_as_old': { 
            title: '📌 Marquer comme anciens', 
            message: `Êtes-vous sûr de vouloir marquer ${ids.length} client(s) comme ANCIENS ?`,
            btnText: 'Marquer',
            btnClass: 'btn-warning'
        },
        'delete': { 
            title: '🗑 Supprimer', 
            message: `Êtes-vous sûr de vouloir supprimer ${ids.length} client(s) ? Cette action est irréversible.`,
            btnText: 'Supprimer',
            btnClass: 'btn-danger'
        },
        'export_whatsapp': { 
            title: '💬 Envoyer sur WhatsApp', 
            message: `Envoyer les informations de ${ids.length} client(s) sur WhatsApp au numéro +237 653 350 503 ?`,
            btnText: 'Envoyer',
            btnClass: 'btn-primary'
        }
    };

    const config = actionsMessages[action];
    if (!config) return;

    document.getElementById('modalConfirmationTitre').textContent = config.title;
    document.getElementById('modalConfirmationMessage').textContent = config.message;
    document.getElementById('modalConfirmationBtn').textContent = config.btnText;
    document.getElementById('modalConfirmationBtn').className = `btn ${config.btnClass}`;
    
    const liste = document.getElementById('modalConfirmationListe');
    let html = '<div style="font-size:12px;color:#64748b;margin-bottom:6px;">Clients sélectionnés :</div>';
    ids.forEach(id => {
        const card = document.querySelector(`.client-card[data-id="${id}"]`);
        if (card) {
            const nom = card.querySelector('.client-nom')?.textContent || 'Inconnu';
            const phone = card.dataset.phone || '';
            html += `<div style="padding:3px 0;font-size:12px;border-bottom:1px solid #f1f5f9;">• ${nom} ${phone ? '- ' + phone : ''}</div>`;
        }
    });
    liste.innerHTML = html;
    
    document.getElementById('modalConfirmationOverlay').style.display = 'block';
    document.getElementById('modalConfirmation').style.display = 'block';
    document.getElementById('modalConfirmationBtn').dataset.action = action;
}

function fermerModalConfirmation() {
    document.getElementById('modalConfirmationOverlay').style.display = 'none';
    document.getElementById('modalConfirmation').style.display = 'none';
}

function executerActionConfirmee() {
    const action = document.getElementById('modalConfirmationBtn').dataset.action;
    const ids = Array.from(selectedClients);
    
    fermerModalConfirmation();
    
    if (window.EdenLoader) window.EdenLoader.show();
    
    // ✅ URL RELATIVE - CORRIGÉE
    fetch('/admin/suivi-client/actions-group', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF
        },
        body: JSON.stringify({ ids: ids, action: action })
    })
    .then(response => response.json())
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();
        
        if (data.success) {
            if (action === 'export_whatsapp' && data.whatsapp_url) {
                window.open(data.whatsapp_url, '_blank');
                showToast(`✅ ${data.count} client(s) envoyé(s) sur WhatsApp !`, 'success');
            } else if (action === 'delete') {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1500);
            } else {
                showToast(data.message, 'success');
                setTimeout(() => location.reload(), 1000);
            }
        } else {
            showToast('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        if (window.EdenLoader) window.EdenLoader.hide();
        showToast('❌ Erreur réseau : ' + error.message, 'error');
        console.error('Erreur:', error);
    });
}

// ════════════════════════════════════════════════════════════════
// ✅ TOGGLE NEW STATUS
// ════════════════════════════════════════════════════════════════

function toggleNew(clientId) {
    const btn = document.getElementById('btn-new-' + clientId);
    const badge = document.getElementById('badge-' + clientId);
    const card = document.querySelector(`.client-card[data-id="${clientId}"]`);
    
    if (!btn) return;
    
    btn.disabled = true;
    btn.textContent = '⏳ ...';

    // ✅ URL RELATIVE - CORRIGÉE
    fetch('/admin/suivi-client/toggle-new/' + clientId, {
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
                if (card) card.dataset.isNew = 'true';
                showToast('✅ Client marqué comme nouveau', 'success');
            } else {
                btn.className = 'btn-toggle-new';
                btn.innerHTML = '🔄 Marquer nouveau';
                badge.className = 'badge-old';
                badge.textContent = 'Ancien';
                if (card) card.dataset.isNew = 'false';
                showToast('✅ Statut "nouveau" retiré', 'success');
            }
            mettreAJourCompteur();
        } else {
            showToast('❌ ' + data.message, 'error');
        }
    })
    .catch(error => {
        showToast('❌ Erreur réseau', 'error');
        console.error('Erreur:', error);
    })
    .finally(() => {
        btn.disabled = false;
    });
}

function mettreAJourCompteur() {
    const cards = document.querySelectorAll('#liste-clients .client-card');
    let total = cards.length;
    let nouveaux = 0;
    cards.forEach(c => {
        if (c.dataset.isNew === 'true') nouveaux++;
    });
    const compteur = document.getElementById('compteur-clients');
    if (compteur) {
        compteur.innerHTML = `${total} client(s) <span style="margin-left:10px;color:#10b981;">🆕 ${nouveaux} nouveau(x)</span>`;
    }
}

// ════════════════════════════════════════════════════════════════
// ✅ TOAST NOTIFICATION
// ════════════════════════════════════════════════════════════════

function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// ════════════════════════════════════════════════════════════════
// ✅ FILTRAGE DYNAMIQUE
// ════════════════════════════════════════════════════════════════

function filtrerClients(terme) {
    const t = terme.trim().toLowerCase();
    const cards = document.querySelectorAll('#liste-clients .client-card');
    let visible = 0;
    let nouveaux = 0;

    cards.forEach(card => {
        const nom = card.dataset.nom || '';
        const phone = card.dataset.phone || '';
        const isNew = card.dataset.isNew === 'true';
        const match = !t || nom.includes(t) || phone.includes(t);
        card.style.display = match ? '' : 'none';
        if (match) {
            visible++;
            if (isNew) nouveaux++;
        }

        if (match && t) {
            const nomEl = card.querySelector('.client-nom');
            if (nomEl) {
                const texte = nomEl.dataset.original || nomEl.innerText;
                nomEl.dataset.original = texte;
                const regex = new RegExp(`(${t})`, 'gi');
                nomEl.innerHTML = texte.replace(regex, '<span class="highlight">$1</span>');
            }
        } else {
            const nomEl = card.querySelector('.client-nom');
            if (nomEl && nomEl.dataset.original) {
                nomEl.innerText = nomEl.dataset.original;
            }
        }
    });

    const compteur = document.getElementById('compteur-clients');
    if (compteur) {
        compteur.innerHTML = `${visible} client(s) <span style="margin-left:10px;color:#10b981;">🆕 ${nouveaux} nouveau(x)</span>`;
    }
}

// Filtres serveur
function appliquerFiltresServeur() {
    const du = document.getElementById('filtre-du')?.value;
    const au = document.getElementById('filtre-au')?.value;
    const site = document.getElementById('filtre-site')?.value;
    const statut = document.getElementById('filtre-statut')?.value;
    const technique = document.getElementById('filtre-technique')?.value;
    const morcellement = document.getElementById('filtre-morcellement')?.value;
    const dossier = document.getElementById('filtre-dossier')?.value;
    const logistique = document.getElementById('filtre-logistique')?.value;
    const q = document.getElementById('search-live')?.value;
    const url = new URL(window.location.href);
    du ? url.searchParams.set('du', du) : url.searchParams.delete('du');
    au ? url.searchParams.set('au', au) : url.searchParams.delete('au');
    site ? url.searchParams.set('grand_site_id', site) : url.searchParams.delete('grand_site_id');
    statut ? url.searchParams.set('status', statut) : url.searchParams.delete('status');
    technique ? url.searchParams.set('technique_solde', technique) : url.searchParams.delete('technique_solde');
    morcellement ? url.searchParams.set('morcellement_solde', morcellement) : url.searchParams.delete('morcellement_solde');
    dossier ? url.searchParams.set('dossier_solde', dossier) : url.searchParams.delete('dossier_solde');
    logistique ? url.searchParams.set('logistique_solde', logistique) : url.searchParams.delete('logistique_solde');
    q ? url.searchParams.set('q', q) : url.searchParams.delete('q');
    window.location.href = url.toString();
}

// ════════════════════════════════════════════════════════════════
// MODIFIER NOM CLIENT
// ════════════════════════════════════════════════════════════════

function ouvrirEditNom(clientId, nomActuel) {
    clientIdCourant = clientId;
    document.getElementById('input-nouveau-nom').value = nomActuel;
    document.getElementById('overlayNom').style.display = 'block';
    document.getElementById('modalNom').style.display = 'block';
    setTimeout(() => document.getElementById('input-nouveau-nom').focus(), 100);
}

function fermerEditNom() {
    document.getElementById('overlayNom').style.display = 'none';
    document.getElementById('modalNom').style.display = 'none';
    clientIdCourant = null;
}

function sauvegarderNom() {
    const nom = document.getElementById('input-nouveau-nom').value.trim();
    if (!nom) { alert('Le nom ne peut pas être vide.'); return; }
    // ✅ URL RELATIVE - CORRIGÉE
    fetch('/admin/clients/' + clientIdCourant + '/modifier-nom', {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify({ nom }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const el = document.getElementById('nom-' + clientIdCourant);
            if (el) {
                el.innerText = data.nom;
                el.dataset.original = data.nom;
                const card = el.closest('.client-card');
                if (card) card.dataset.nom = data.nom.toLowerCase();
            }
            fermerEditNom();
            showToast('✅ Nom mis à jour', 'success');
        } else alert(data.message || 'Erreur');
    });
}

// Appliquer le filtre live au chargement
document.addEventListener('DOMContentLoaded', () => {
    const q = new URLSearchParams(window.location.search).get('q');
    if (q) filtrerClients(q);
});
</script>
@endsection