@extends('admin.layout')
@section('content')

<style>
.client-card {
    background:white; border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    padding:16px 20px; margin-bottom:10px;
    display:flex; justify-content:space-between; align-items:center;
    border-left:4px solid #0d6efd; transition:0.2s;
}
.client-card:hover { box-shadow:0 4px 18px rgba(0,0,0,0.1); transform:translateY(-1px); }
.client-card .name { font-weight:700; font-size:15px; color:#1e3a5f; }
.client-card .meta { font-size:12px; color:#64748b; margin-top:2px; }
.badge-dossier {
    background:#eff6ff; color:#1e3a5f;
    border:1px solid #bfdbfe; border-radius:6px;
    padding:2px 8px; font-size:11px; font-weight:600;
}
.filter-bar {
    background:white; border-radius:12px; padding:14px 18px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px;
}
.pagination { justify-content:center; margin-top:20px; }
.pagination .page-link {
    border-radius:8px !important; margin:0 3px;
    color:#1e3a5f; border:1px solid #e2e8f0; transition:0.2s;
}
.pagination .page-link:hover { background:#0d6efd; color:white; border-color:#0d6efd; }
.pagination .active .page-link {
    background:linear-gradient(135deg,#1d4ed8,#7c3aed); border:none; color:white; font-weight:600;
}
.pagination .disabled .page-link { opacity:0.4; cursor:not-allowed; }

/* Zone résultats AJAX */
#liste-clients { min-height:100px; }
.spinner {
    display:none; text-align:center; padding:20px;
    color:#64748b; font-size:13px;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>👤 Suivi Clients</h2>
    <a href="{{ route('suivi-client.create') }}" class="btn btn-primary">+ Nouveau client / dossier</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- FILTRES --}}
<div class="filter-bar">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label style="font-size:11px;font-weight:600;color:#64748b;">🔍 Nom ou téléphone</label>
            <input type="text" id="searchInput" class="form-control form-control-sm"
                   placeholder="Tapez pour filtrer..." value="{{ request('search') }}"
                   autocomplete="off">
        </div>
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:600;color:#64748b;">🏢 Grand site</label>
            <select id="grandSiteFilter" class="form-control form-control-sm">
                <option value="">Tous les sites</option>
                @foreach($grandsites as $gs)
                    <option value="{{ $gs->id }}" {{ request('grand_site_id')==$gs->id ? 'selected' : '' }}>
                        {{ $gs->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:600;color:#64748b;">🧭 Direction</label>
            <select id="directionFilter" class="form-control form-control-sm">
                <option value="">Toutes directions</option>
                <option value="baffoussam"         {{ request('direction')==='baffoussam'         ? 'selected':'' }}>Baffoussam</option>
                <option value="bagante"            {{ request('direction')==='bagante'            ? 'selected':'' }}>Bagante</option>
                <option value="dschang"            {{ request('direction')==='dschang'            ? 'selected':'' }}>Dschang</option>
                <option value="direction_generale" {{ request('direction')==='direction_generale' ? 'selected':'' }}>Direction Générale</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1 align-items-end">
            <button onclick="resetFiltres()" class="btn btn-outline-secondary btn-sm">✖ Reset</button>
        </div>
    </div>
</div>

{{-- SPINNER --}}
<div class="spinner" id="spinner">⏳ Chargement...</div>

{{-- LISTE — mise à jour par AJAX --}}
<div id="liste-clients">
    @include('admin.suivi_client._liste', ['clients' => $clients])
</div>

@endsection

@section('scripts')
<script>
let searchTimeout = null;

const searchInput    = document.getElementById('searchInput');
const grandSiteFilter = document.getElementById('grandSiteFilter');
const directionFilter = document.getElementById('directionFilter');
const listeEl        = document.getElementById('liste-clients');
const spinner        = document.getElementById('spinner');

// Lancer la recherche avec délai sur la saisie texte
searchInput.addEventListener('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => charger(), 350);
});

// Lancer immédiatement sur changement des selects
grandSiteFilter.addEventListener('change', () => charger());
directionFilter.addEventListener('change', () => charger());

function resetFiltres() {
    searchInput.value     = '';
    grandSiteFilter.value = '';
    directionFilter.value = '';
    charger();
}

function charger() {
    const params = new URLSearchParams({
        search:        searchInput.value,
        grand_site_id: grandSiteFilter.value,
        direction:     directionFilter.value,
        ajax:          '1',
    });

    spinner.style.display = 'block';
    listeEl.style.opacity = '0.4';

    fetch(`{{ route('suivi-client.index') }}?${params.toString()}`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.text())
    .then(html => {
        listeEl.innerHTML    = html;
        listeEl.style.opacity = '1';
        spinner.style.display = 'none';

        // Remettre le focus sur le champ de recherche sans perdre la position du curseur
        const pos = searchInput.selectionStart;
        searchInput.focus();
        searchInput.setSelectionRange(pos, pos);
    })
    .catch(() => {
        spinner.style.display = 'none';
        listeEl.style.opacity = '1';
    });
}
</script>
@endsection