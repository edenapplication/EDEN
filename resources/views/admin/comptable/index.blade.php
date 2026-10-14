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
        transition: all 0.25s;
    }
    .stat-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,0.1); }
    .stat-card.paye     { border-left-color: #16a34a; }
    .stat-card.non_paye { border-left-color: #dc2626; }
    .stat-card.partiel  { border-left-color: #f59e0b; }
    .stat-card .label {
        font-size: 11px; color: #64748b; text-transform: uppercase;
        font-weight: 700; letter-spacing: 0.5px;
    }
    .stat-card .value { font-size: 24px; font-weight: 900; color: #1e3a5f; margin-top: 4px; }
    .stat-card.paye .value     { color: #16a34a; }
    .stat-card.non_paye .value { color: #dc2626; }
    .stat-card.partiel .value  { color: #f59e0b; }

    /* ═══ FILTRE DYNAMIQUE ═══ */
    .filtre-box {
        background: white; border-radius: 12px; padding: 16px;
        margin-bottom: 16px; box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border-left: 4px solid #dc2626;
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
        transition: all 0.2s;
        background-color: #f8fafc;
    }
    .filtre-box .form-select:focus {
        outline: none;
        border-color: #dc2626;
        background-color: white;
        box-shadow: 0 0 0 4px rgba(220,38,38,0.1);
    }
    .btn-reset {
        background: #f1f5f9;
        color: #64748b;
        border: none;
        padding: 10px 18px;
        border-radius: 10px;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        transition: all 0.2s;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .btn-reset:hover { background: #e2e8f0; color: #334155; }

    .filtre-count {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
        padding: 6px 12px;
        background: #f1f5f9;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .filtre-count strong { color: #dc2626; font-size: 14px; }

    /* ═══ LIGNES DOSSIERS ═══ */
    .dossier-row {
        background: white; border-radius: 12px; padding: 16px 18px;
        margin-bottom: 10px; box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-left: 4px solid #e2e8f0; transition: all 0.3s;
        display: flex; justify-content: space-between; align-items: center; gap: 16px;
        flex-wrap: wrap;
    }
    .dossier-row:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); transform: translateY(-2px); }
    .dossier-row.paye     { border-left-color: #16a34a; background: #f0fdf4; }
    .dossier-row.partiel  { border-left-color: #f59e0b; background: #fffbeb; }
    .dossier-row.non_paye { border-left-color: #dc2626; background: #fef2f2; }

    .badge-statut {
        padding: 6px 14px; border-radius: 50px; font-size: 11px;
        font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;
        display: inline-flex; align-items: center; gap: 5px;
    }
    .badge-statut.paye     { background: #dcfce7; color: #15803d; border: 1.5px solid #86efac; }
    .badge-statut.partiel  { background: #fef3c7; color: #b45309; border: 1.5px solid #fcd34d; }
    .badge-statut.non_paye { background: #fee2e2; color: #b91c1c; border: 1.5px solid #fecaca; }

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

    .dossier-info { flex: 1; min-width: 250px; }
    .dossier-nom { font-weight: 800; color: #1e3a5f; font-size: 15px; }
    .dossier-meta { font-size: 12px; color: #64748b; margin-top: 2px; }
    .dossier-montants { text-align: right; margin-right: 16px; min-width: 180px; }
    .montant-ref { font-size: 11px; color: #94a3b8; }
    .montant-paye { font-size: 14px; font-weight: 900; }
    .montant-reste { font-size: 11px; font-weight: 700; }

    /* ═══ HIGHLIGHT RECHERCHE ═══ */
    .highlight { background: #fef08a; padding: 0 2px; border-radius: 3px; }

    /* ═══ ETAT VIDE DYNAMIQUE ═══ */
    .empty-state {
        display: none;
        text-align: center; padding: 60px 20px;
        background: white; border-radius: 12px;
        color: #94a3b8;
    }
    .empty-state.visible { display: block; }

    /* ═══ MODAL ═══ */
    .modal-overlay {
        display: none; position: fixed; inset: 0;
        background: rgba(15,23,42,0.6); z-index: 9998;
        backdrop-filter: blur(2px);
    }
    .modal-box {
        display: none; position: fixed; top: 50%; left: 50%;
        transform: translate(-50%, -50%); background: white;
        padding: 24px; border-radius: 16px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        z-index: 9999; animation: modalSlideIn 0.2s ease;
    }
    @keyframes modalSlideIn {
        from { opacity: 0; transform: translate(-50%, -45%); }
        to   { opacity: 1; transform: translate(-50%, -50%); }
    }
    .modal-overlay.active, .modal-box.active { display: block; }

    /* ═══ ANIMATION LIGNE ═══ */
    @keyframes rowHighlight {
        0% { background: #dcfce7; }
        100% { background: transparent; }
    }
</style>
@endsection

@section('content')

{{-- EN-TÊTE --}}
<div class="comptable-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2>💰 Paiement Technique</h2>
            <div class="sub">
                Gérez uniquement les paiements du <strong>dossier technique</strong>
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
    <div class="stat-card paye">
        <div class="label">💵 Montant encaissé</div>
        <div class="value" style="font-size:18px;">
            {{ number_format($stats['paye_technique_total'], 0, ',', ' ') }} FCFA
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- FILTRAGE DYNAMIQUE (aucun rechargement) --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="filtre-box">
    <div class="row g-2 align-items-center">

        {{-- Recherche --}}
        <div class="col-md-5">
            <input type="text"
                   id="search-input"
                   class="search-input"
                   placeholder="Rechercher par nom, téléphone ou dossier..."
                   autocomplete="off">
        </div>

        {{-- Statut --}}
        <div class="col-md-2">
            <select id="filtre-statut" class="form-select">
                <option value="">📊 Tous les statuts</option>
                <option value="paye">✅ Payés</option>
                <option value="partiel">⏳ Partiels</option>
                <option value="non_paye">❌ Non payés</option>
            </select>
        </div>

        {{-- Grand Site --}}
        <div class="col-md-2">
            <select id="filtre-site" class="form-select">
                <option value="">🏢 Tous les sites</option>
                @foreach($grandSites as $gs)
                    <option value="{{ strtolower($gs->nom) }}">{{ $gs->nom }}</option>
                @endforeach
            </select>
        </div>

        {{-- Reset + Compteur --}}
        <div class="col-md-3 d-flex gap-2 justify-content-end">
            <span class="filtre-count">
                <span id="count-visible">{{ $dossiers->count() }}</span>
                <span>/</span>
                <span>{{ $dossiers->total() }}</span>
                dossier(s)
            </span>
            <button type="button" class="btn-reset" onclick="resetFiltres()">
                ✖ Réinitialiser
            </button>
        </div>

    </div>
</div>

{{-- LISTE --}}
<div id="liste-dossiers">

    @forelse($dossiers as $dossier)
        @php
            $dejaPaye = $dossier->paiementsTechniques->sum('montant');
            $prixRef  = $dossier->prix_technique ?? 0;
            $reste    = max(0, $prixRef - $dejaPaye);
            $pct      = $prixRef > 0 ? min(100, round(($dejaPaye / $prixRef) * 100)) : 0;

            if ($dejaPaye >= $prixRef && $prixRef > 0) {
                $statut  = 'paye';     $label = '✅ Payé';       $couleur = '#16a34a';
            } elseif ($dejaPaye > 0) {
                $statut  = 'partiel';  $label = '⏳ Partiel (' . $pct . '%)';  $couleur = '#f59e0b';
            } else {
                $statut  = 'non_paye'; $label = '❌ Non payé';    $couleur = '#dc2626';
            }
        @endphp

        {{-- 
            ═══════════════════════════════════════════════════════
            ✅ data-attributes pour le filtrage côté client
            ═══════════════════════════════════════════════════════
        --}}
        <div class="dossier-row {{ $statut }}"
             data-dossier-id="{{ $dossier->id }}"
             data-search="{{ strtolower(
                 ($dossier->client?->name ?? '') . ' ' .
                 ($dossier->client?->phone ?? '') . ' ' .
                 ($dossier->nom_dossier ?? '')
             ) }}"
             data-statut="{{ $statut }}"
             data-site="{{ strtolower($dossier->grandSite?->nom ?? '') }}">

            <div class="dossier-info">
                <div class="dossier-nom">
                    👤 <span class="client-name">{{ $dossier->client?->name ?? 'Client inconnu' }}</span>
                    <span class="badge-statut {{ $statut }}">{{ $label }}</span>
                </div>
                <div class="dossier-meta">
                    📞 {{ $dossier->client?->phone ?? '-' }}
                    &nbsp;·&nbsp;
                    📂 {{ $dossier->nom_dossier }}
                    &nbsp;·&nbsp;
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
                <div class="montant-ref">Réf. technique</div>
                <div class="montant-paye" style="color:{{ $couleur }};">
                    {{ number_format($dejaPaye, 0, ',', ' ') }} /
                    {{ number_format($prixRef, 0, ',', ' ') }} FCFA
                </div>
                @if($reste > 0)
                    <div class="montant-reste" style="color:#dc2626;">
                        Reste : {{ number_format($reste, 0, ',', ' ') }} FCFA
                    </div>
                @elseif($prixRef > 0)
                    <div class="montant-reste" style="color:#16a34a;">✅ Soldé</div>
                @endif
            </div>

            <div class="action-buttons" style="min-width:140px;">
                @if($statut !== 'paye')
                    <button type="button"
                            class="btn-payer"
                            data-dossier-id="{{ $dossier->id }}"
                            data-client-nom="{{ addslashes($dossier->client?->name ?? 'Client') }}"
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
    @empty
        <div style="text-align:center;padding:60px 20px;color:#94a3b8;background:white;border-radius:12px;">
            <div style="font-size:48px;">📭</div>
            <div style="font-weight:700;margin-top:12px;font-size:16px;">Aucun dossier technique trouvé</div>
        </div>
    @endforelse

</div>

{{-- État vide dynamique (affiché quand le filtrage ne retourne rien) --}}
<div class="empty-state" id="empty-state">
    <div style="font-size:64px;">🔍</div>
    <div style="font-weight:800;font-size:18px;color:#1e3a5f;margin-top:12px;">
        Aucun résultat
    </div>
    <div style="font-size:13px;margin-top:8px;">
        Aucun dossier ne correspond à vos critères de recherche.
    </div>
    <button type="button" class="btn-reset mt-3" onclick="resetFiltres()">
        ✖ Effacer les filtres
    </button>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $dossiers->links() }}
</div>

{{-- MODAL PAIEMENT AJAX --}}
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
                 style="display:none;color:#dc2626;font-size:12px;font-weight:700;margin-top:4px;">
            </div>
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
                    margin-bottom:12px;font-weight:600;">
        </div>

        <div class="d-flex justify-content-end gap-2">
            <button type="button" onclick="fermerModalPaiement()"
                    class="btn btn-light btn-sm" style="font-weight:600;">
                Annuler
            </button>
            <button type="submit" id="btnSubmitModal"
                    class="btn btn-primary btn-sm px-4"
                    style="font-weight:700;">
                💾 Enregistrer
            </button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
// ═══════════════════════════════════════════════════════════════════
// ÉTAT GLOBAL
// ═══════════════════════════════════════════════════════════════════
let modalDossierId  = null;
let modalMaxMontant = 0;
const CSRF_TOKEN    = '{{ csrf_token() }}';

// ═══════════════════════════════════════════════════════════════════
// ✅ FILTRAGE DYNAMIQUE (INSTANTANÉ)
// ═══════════════════════════════════════════════════════════════════

/**
 * Applique les filtres en temps réel sur la liste des dossiers
 */
function appliquerFiltres() {
    const recherche = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
    const statut    = document.getElementById('filtre-statut')?.value || '';
    const site      = document.getElementById('filtre-site')?.value || '';

    const rows = document.querySelectorAll('.dossier-row');
    let visibles = 0;

    rows.forEach(row => {
        const dataSearch = row.dataset.search || '';
        const dataStatut = row.dataset.statut || '';
        const dataSite   = row.dataset.site   || '';

        // Vérifier chaque critère
        const matchRecherche = !recherche || dataSearch.includes(recherche);
        const matchStatut    = !statut    || dataStatut === statut;
        const matchSite      = !site      || dataSite === site;

        const visible = matchRecherche && matchStatut && matchSite;

        if (visible) {
            row.style.display = '';
            visibles++;
        } else {
            row.style.display = 'none';
        }
    });

    // Mettre à jour le compteur
    document.getElementById('count-visible').textContent = visibles;

    // Afficher l'état vide si rien ne correspond
    const emptyState = document.getElementById('empty-state');
    if (visibles === 0 && rows.length > 0) {
        emptyState.classList.add('visible');
    } else {
        emptyState.classList.remove('visible');
    }
}

/**
 * Réinitialise tous les filtres
 */
function resetFiltres() {
    const search = document.getElementById('search-input');
    const statut = document.getElementById('filtre-statut');
    const site   = document.getElementById('filtre-site');

    if (search) search.value = '';
    if (statut) statut.value = '';
    if (site)   site.value   = '';

    appliquerFiltres();
}

// Écouteurs d'événements
document.addEventListener('DOMContentLoaded', () => {
    const searchInput  = document.getElementById('search-input');
    const statutSelect = document.getElementById('filtre-statut');
    const siteSelect   = document.getElementById('filtre-site');

    // Recherche en temps réel (avec debounce pour la performance)
    let debounceTimer;
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => appliquerFiltres(), 150);
    });

    // Filtres statut et site (instantané)
    statutSelect?.addEventListener('change', appliquerFiltres);
    siteSelect?.addEventListener('change', appliquerFiltres);

    // Raccourci clavier : Échap pour effacer la recherche
    searchInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            searchInput.value = '';
            appliquerFiltres();
        }
    });
});

// ═══════════════════════════════════════════════════════════════════
// MODAL PAIEMENT (existant)
// ═══════════════════════════════════════════════════════════════════

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
        btn.disabled = true;
        btn.style.opacity = '0.5';
    } else if (val > modalMaxMontant) {
        erreur.style.display = 'block';
        erreur.textContent = '⚠️ Maximum : ' + modalMaxMontant.toLocaleString('fr-FR') + ' FCFA';
        input.style.borderColor = '#dc2626';
        btn.disabled = true;
        btn.style.opacity = '0.5';
    } else {
        erreur.style.display = 'none';
        input.style.borderColor = '#16a34a';
        btn.disabled = false;
        btn.style.opacity = '1';
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
        afficherMessageModal('⚠️ Montant invalide', 'error');
        return false;
    }
    if (!date) {
        afficherMessageModal('⚠️ La date est obligatoire', 'error');
        return false;
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
            btn.disabled = false;
            btn.innerHTML = '💾 Enregistrer';
        }
    })
    .catch(err => {
        afficherMessageModal('❌ Erreur réseau : ' + err.message, 'error');
        btn.disabled = false;
        btn.innerHTML = '💾 Enregistrer';
    });

    return false;
}

function afficherMessageModal(message, type) {
    const box = document.getElementById('messageModal');
    box.style.display = 'block';
    box.textContent = message;

    if (type === 'success') {
        box.style.background = '#dcfce7';
        box.style.color = '#15803d';
        box.style.border = '1px solid #86efac';
    } else {
        box.style.background = '#fee2e2';
        box.style.color = '#b91c1c';
        box.style.border = '1px solid #fecaca';
    }
}

function mettreAJourLigneDossier(data) {
    const row = document.querySelector(`.dossier-row[data-dossier-id="${data.dossier_id}"]`);
    if (!row) return;

    // Mettre à jour la classe de statut
    row.classList.remove('paye', 'partiel', 'non_paye');
    row.classList.add(data.statut);

    // ✅ Mettre à jour les data-attributes pour le filtrage
    row.dataset.statut = data.statut;

    // Mettre à jour le badge
    const badge = row.querySelector('.badge-statut');
    if (badge) {
        badge.className = 'badge-statut ' + data.statut;
        badge.textContent = data.label;
    }

    // Montants
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

    // Barre de progression
    const progressBar = row.querySelector('.progress-bar-mini > div');
    if (progressBar) {
        progressBar.style.width = data.pourcentage + '%';
        progressBar.style.background = data.couleur;
    }

    // Boutons d'action
    const actionDiv = row.querySelector('.action-buttons');
    if (actionDiv && data.statut === 'paye') {
        actionDiv.innerHTML = `
            <span class="btn-payer disabled">✅ Payé</span>
            <a href="/admin/comptable/dossier/${data.dossier_id}/historique"
               class="btn btn-outline-secondary btn-sm mt-2"
               style="font-size:11px;width:100%;text-align:center;display:block;">
                📜 Historique
            </a>`;
    } else if (actionDiv) {
        const btn = actionDiv.querySelector('.btn-payer');
        if (btn) {
            btn.dataset.prixRef  = data.prix_ref;
            btn.dataset.dejaPaye = data.deja_paye;
            btn.dataset.reste    = data.reste;
        }
    }

    // Animation visuelle
    row.style.transition = 'background 0.5s';
    row.style.background = '#dcfce7';
    setTimeout(() => { row.style.background = ''; }, 1500);

    // ✅ Réappliquer les filtres (au cas où le statut a changé)
    setTimeout(() => appliquerFiltres(), 100);
}

// ═══════════════════════════════════════════════════════════════════
// RACCOURCIS CLAVIER
// ═══════════════════════════════════════════════════════════════════
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        fermerModalPaiement();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('paiement_note')?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            document.getElementById('formPaiementAjax').dispatchEvent(new Event('submit'));
        }
    });
});
</script>
@endsection