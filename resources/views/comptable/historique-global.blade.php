@extends('layouts.comptable')

@section('title', 'Historique global')

@section('styles')
<style>
    /* ═══ BARRE DE FILTRES ═══ */
    .filtre-box {
        background: white; border-radius: 12px; padding: 16px;
        margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.06);
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
    .filtre-box .form-control {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 9px 14px;
        font-size: 13px;
        font-weight: 600;
        transition: all 0.2s;
        background-color: #f8fafc;
    }
    .filtre-box .form-control:focus {
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

    .total-display {
        background: linear-gradient(135deg, #16a34a, #15803d);
        color: white;
        padding: 12px 20px;
        border-radius: 10px;
        text-align: right;
    }
    .total-display .label {
        font-size: 10px; opacity: 0.9; text-transform: uppercase;
        font-weight: 700; letter-spacing: 0.5px;
    }
    .total-display .value {
        font-size: 20px; font-weight: 900; margin-top: 2px;
    }

    /* ═══ TABLEAU ═══ */
    .historique-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
        background: white;
    }
    .historique-table thead {
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 700;
        position: sticky;
        top: 0;
        z-index: 10;
    }
    .historique-table th {
        padding: 12px 16px;
        text-align: left;
        cursor: pointer;
        user-select: none;
        transition: background 0.2s;
        white-space: nowrap;
    }
    .historique-table th:hover {
        background: #e2e8f0;
        color: #dc2626;
    }
    .historique-table th.sortable::after {
        content: ' ⇅';
        opacity: 0.4;
        font-size: 10px;
        margin-left: 4px;
    }
    .historique-table th.sort-asc::after {
        content: ' ▲';
        color: #dc2626;
        opacity: 1;
    }
    .historique-table th.sort-desc::after {
        content: ' ▼';
        color: #dc2626;
        opacity: 1;
    }
    .historique-table td {
        padding: 12px 16px;
        border-top: 1px solid #f1f5f9;
        vertical-align: middle;
    }
    .historique-table tbody tr {
        transition: background 0.15s;
    }
    .historique-table tbody tr:hover {
        background: #fef2f2;
    }

    /* ═══ BADGES ═══ */
    .badge-ref {
        background: #dbeafe; color: #1d4ed8;
        padding: 2px 8px; border-radius: 6px;
        font-size: 11px; font-weight: 700;
        display: inline-block;
    }
    .badge-montant {
        color: #16a34a;
        font-weight: 800;
    }

    /* ═══ HIGHLIGHT ═══ */
    .highlight {
        background: #fef08a;
        padding: 0 2px;
        border-radius: 3px;
        font-weight: 700;
    }

    /* ═══ ÉTAT VIDE ═══ */
    .empty-state {
        display: none;
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }
    .empty-state.visible { display: block; }

    /* ═══ ANIMATION LIGNE ═══ */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(4px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .historique-table tbody tr {
        animation: fadeIn 0.25s ease;
    }

    /* ═══ RESPONSIVE ═══ */
    @media (max-width: 768px) {
        .historique-table th,
        .historique-table td {
            padding: 10px 8px;
            font-size: 11px;
        }
    }
</style>
@endsection

@section('content')

{{-- ═══ EN-TÊTE ═══ --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">🕐 Historique global des paiements</h2>
        <p style="color:#64748b;font-size:13px;margin:0;">
            Tous les paiements techniques enregistrés
        </p>
    </div>
    <a href="{{ route('comptable.index') }}" class="btn-comptable">
        💰 Retour aux paiements
    </a>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- FILTRES DYNAMIQUES --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div class="filtre-box">
    <div class="row g-3 align-items-end">

        {{-- Recherche --}}
        <div class="col-md-4">
            <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">
                🔍 Recherche
            </label>
            <input type="text"
                   id="search-input"
                   class="search-input"
                   placeholder="Client, téléphone, dossier, référence..."
                   autocomplete="off">
        </div>

        {{-- Date début --}}
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">
                📅 Du
            </label>
            <input type="date" id="filtre-du" class="form-control">
        </div>

        {{-- Date fin --}}
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">
                📅 Au
            </label>
            <input type="date" id="filtre-au" class="form-control">
        </div>

        {{-- Montant min --}}
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">
                💰 Montant min
            </label>
            <input type="number" id="filtre-montant-min" class="form-control"
                   placeholder="0" min="0">
        </div>

        {{-- Compteur + Reset --}}
        <div class="col-md-2 d-flex gap-2 justify-content-end align-items-end">
            <span class="filtre-count">
                <span id="count-visible">{{ $paiements->count() }}</span>
                <span>/</span>
                <span>{{ $paiements->total() }}</span>
            </span>
            <button type="button" class="btn-reset" onclick="resetFiltres()" title="Réinitialiser">
                ✖
            </button>
        </div>

    </div>

    {{-- Ligne 2 : Total filtré dynamique + Tri --}}
    <div class="row g-3 align-items-center mt-3">
        <div class="col-md-3">
            <div class="total-display">
                <div class="label">💰 Total filtré</div>
                <div class="value" id="total-filtre">
                    {{ number_format($totalFiltre, 0, ',', ' ') }} FCFA
                </div>
            </div>
        </div>

        <div class="col-md-9 d-flex gap-2 justify-content-end flex-wrap">
            <div style="font-size:11px;color:#64748b;font-weight:700;
                        display:flex;align-items:center;gap:8px;">
                Trier par :
            </div>
            <select id="tri-select" class="form-control" style="width:auto;padding:8px 14px;">
                <option value="date-desc">📅 Date (récent → ancien)</option>
                <option value="date-asc">📅 Date (ancien → récent)</option>
                <option value="montant-desc">💰 Montant (élevé → faible)</option>
                <option value="montant-asc">💰 Montant (faible → élevé)</option>
                <option value="client-asc">👤 Client (A → Z)</option>
                <option value="client-desc">👤 Client (Z → A)</option>
            </select>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- TABLEAU --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div style="background:white;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.06);">

    <table class="historique-table" id="table-historique">
        <thead>
            <tr>
                <th class="sortable" data-sort="date">Date</th>
                <th class="sortable" data-sort="client">Client</th>
                <th>Dossier</th>
                <th>Grand Site</th>
                <th class="sortable" data-sort="montant" style="text-align:right;">Montant</th>
                <th>Référence</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody id="tbody-historique">

            @forelse($paiements as $p)
                @php
                    $datePaiement = \Carbon\Carbon::parse($p->date_paiement);
                    $clientNom    = $p->dossier?->client?->name ?? '-';
                    $clientPhone  = $p->dossier?->client?->phone ?? '';
                    $dossierNom   = $p->dossier?->nom_dossier ?? '-';
                    $siteNom      = $p->dossier?->grandSite?->nom ?? '-';
                    $reference    = $p->user_reference ?? '';
                @endphp

                <tr data-date="{{ $datePaiement->format('Y-m-d') }}"
                    data-montant="{{ $p->montant }}"
                    data-client="{{ strtolower($clientNom) }}"
                    data-search="{{ strtolower($clientNom . ' ' . $clientPhone . ' ' . $dossierNom . ' ' . $reference) }}">

                    <td>
                        <span style="font-weight:600;color:#475569;">
                            📅 {{ $datePaiement->format('d/m/Y') }}
                        </span>
                    </td>

                    <td>
                        <strong class="client-name" style="color:#1e3a5f;">{{ $clientNom }}</strong>
                        @if($clientPhone)
                            <br>
                            <small style="color:#94a3b8;">📞 {{ $clientPhone }}</small>
                        @endif
                    </td>

                    <td>
                        <span style="font-size:12px;color:#475569;">{{ $dossierNom }}</span>
                    </td>

                    <td>
                        <span style="background:#f1f5f9;color:#475569;padding:2px 8px;
                                     border-radius:6px;font-size:11px;font-weight:600;">
                            🏢 {{ $siteNom }}
                        </span>
                    </td>

                    <td style="text-align:right;">
                        <span class="badge-montant">
                            {{ number_format($p->montant, 0, ',', ' ') }} FCFA
                        </span>
                    </td>

                    <td>
                        @if($reference)
                            <span class="badge-ref">🔑 {{ $reference }}</span>
                        @else
                            <span style="color:#cbd5e1;font-size:11px;">—</span>
                        @endif
                    </td>

                    <td>
                        <small style="color:#64748b;">{{ $p->note ?? '-' }}</small>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align:center;padding:60px;color:#94a3b8;">
                        <div style="font-size:48px;">📭</div>
                        <div style="font-weight:700;margin-top:12px;">Aucun paiement trouvé</div>
                    </td>
                </tr>
            @endforelse

        </tbody>
    </table>

    {{-- ✅ État vide dynamique --}}
    <div class="empty-state" id="empty-state">
        <div style="font-size:64px;">🔍</div>
        <div style="font-weight:800;font-size:18px;color:#1e3a5f;margin-top:12px;">
            Aucun résultat
        </div>
        <div style="font-size:13px;margin-top:8px;">
            Aucun paiement ne correspond à vos critères de recherche.
        </div>
        <button type="button" class="btn-reset mt-3" onclick="resetFiltres()">
            ✖ Effacer les filtres
        </button>
    </div>
</div>

{{-- Pagination (utilisée uniquement quand on recharge la page) --}}
<div class="mt-3 d-flex justify-content-center">
    {{ $paiements->links() }}
</div>

@endsection

@section('scripts')
<script>
// ═══════════════════════════════════════════════════════════════════
// FILTRAGE DYNAMIQUE + TRI
// ═══════════════════════════════════════════════════════════════════

/**
 * Applique les filtres en temps réel sur la liste des paiements
 */
function appliquerFiltres() {
    const recherche  = (document.getElementById('search-input')?.value || '').toLowerCase().trim();
    const dateDu     = document.getElementById('filtre-du')?.value || '';
    const dateAu     = document.getElementById('filtre-au')?.value || '';
    const montantMin = parseFloat(document.getElementById('filtre-montant-min')?.value || 0) || 0;

    const rows = document.querySelectorAll('#tbody-historique tr[data-date]');
    let visibles = 0;
    let total = 0;

    rows.forEach(row => {
        const rowSearch  = row.dataset.search || '';
        const rowDate    = row.dataset.date || '';
        const rowMontant = parseFloat(row.dataset.montant) || 0;

        // Vérifier chaque critère
        const matchRecherche  = !recherche  || rowSearch.includes(recherche);
        const matchDateDebut  = !dateDu     || rowDate >= dateDu;
        const matchDateFin    = !dateAu     || rowDate <= dateAu;
        const matchMontantMin = !montantMin || rowMontant >= montantMin;

        const visible = matchRecherche && matchDateDebut && matchDateFin && matchMontantMin;

        if (visible) {
            row.style.display = '';
            visibles++;
            total += rowMontant;
        } else {
            row.style.display = 'none';
        }
    });

    // Mettre à jour les compteurs
    document.getElementById('count-visible').textContent = visibles;
    document.getElementById('total-filtre').textContent =
        total.toLocaleString('fr-FR') + ' FCFA';

    // Afficher l'état vide si nécessaire
    const emptyState = document.getElementById('empty-state');
    if (visibles === 0 && rows.length > 0) {
        emptyState.classList.add('visible');
    } else {
        emptyState.classList.remove('visible');
    }

    // Réappliquer le tri après filtrage
    appliquerTri();
}

/**
 * Applique le tri sur les lignes visibles
 */
function appliquerTri() {
    const tri = document.getElementById('tri-select')?.value || 'date-desc';
    const tbody = document.getElementById('tbody-historique');
    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr[data-date]'));

    rows.sort((a, b) => {
        switch (tri) {
            case 'date-desc':
                return b.dataset.date.localeCompare(a.dataset.date);
            case 'date-asc':
                return a.dataset.date.localeCompare(b.dataset.date);
            case 'montant-desc':
                return (parseFloat(b.dataset.montant) || 0) - (parseFloat(a.dataset.montant) || 0);
            case 'montant-asc':
                return (parseFloat(a.dataset.montant) || 0) - (parseFloat(b.dataset.montant) || 0);
            case 'client-asc':
                return (a.dataset.client || '').localeCompare(b.dataset.client || '');
            case 'client-desc':
                return (b.dataset.client || '').localeCompare(a.dataset.client || '');
            default:
                return 0;
        }
    });

    // Réordonner les lignes
    rows.forEach(row => tbody.appendChild(row));
}

/**
 * Réinitialise tous les filtres
 */
function resetFiltres() {
    const search   = document.getElementById('search-input');
    const du       = document.getElementById('filtre-du');
    const au       = document.getElementById('filtre-au');
    const montant  = document.getElementById('filtre-montant-min');
    const tri      = document.getElementById('tri-select');

    if (search)  search.value = '';
    if (du)      du.value = '';
    if (au)      au.value = '';
    if (montant) montant.value = '';
    if (tri)     tri.value = 'date-desc';

    appliquerFiltres();
}

// ═══════════════════════════════════════════════════════════════════
// ÉCOUTEURS D'ÉVÉNEMENTS
// ═══════════════════════════════════════════════════════════════════
document.addEventListener('DOMContentLoaded', () => {
    const searchInput  = document.getElementById('search-input');
    const filtreDu     = document.getElementById('filtre-du');
    const filtreAu     = document.getElementById('filtre-au');
    const filtreMontant = document.getElementById('filtre-montant-min');
    const triSelect    = document.getElementById('tri-select');

    // Recherche avec debounce
    let debounceTimer;
    searchInput?.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => appliquerFiltres(), 150);
    });

    // Filtres de date
    filtreDu?.addEventListener('change', appliquerFiltres);
    filtreAu?.addEventListener('change', appliquerFiltres);

    // Filtre montant (avec debounce)
    let debounceMontant;
    filtreMontant?.addEventListener('input', () => {
        clearTimeout(debounceMontant);
        debounceMontant = setTimeout(() => appliquerFiltres(), 200);
    });

    // Tri
    triSelect?.addEventListener('change', appliquerFiltres);

    // Raccourci Échap pour effacer la recherche
    searchInput?.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            searchInput.value = '';
            appliquerFiltres();
        }
    });

    // Tri par clic sur les en-têtes
    document.querySelectorAll('.historique-table th.sortable').forEach(th => {
        th.addEventListener('click', () => {
            const sortKey = th.dataset.sort;
            const current = document.getElementById('tri-select').value;

            let newSort;
            if (sortKey === 'date') {
                newSort = current === 'date-desc' ? 'date-asc' : 'date-desc';
            } else if (sortKey === 'montant') {
                newSort = current === 'montant-desc' ? 'montant-asc' : 'montant-desc';
            } else if (sortKey === 'client') {
                newSort = current === 'client-asc' ? 'client-desc' : 'client-asc';
            }

            document.getElementById('tri-select').value = newSort;
            appliquerTri();

            // Mettre à jour les flèches visuelles
            document.querySelectorAll('.historique-table th').forEach(h => {
                h.classList.remove('sort-asc', 'sort-desc');
            });
            th.classList.add(newSort.endsWith('asc') ? 'sort-asc' : 'sort-desc');
        });
    });

    // Initialiser
    appliquerFiltres();
});
</script>
@endsection