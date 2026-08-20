@extends('admin.layout')
@section('content')

<style>
.client-card {
    background:white; border-radius:12px; padding:16px; margin-bottom:10px;
    box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #1d4ed8; transition:0.2s;
}
.client-card:hover { box-shadow:0 6px 20px rgba(0,0,0,0.1); transform:translateY(-2px); }
.filtre-box { background:white; border-radius:12px; padding:14px; margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
.dossier-pill {
    display:inline-flex; align-items:center; gap:6px;
    background:#f1f5f9; border-radius:8px; padding:4px 10px;
    font-size:11px; color:#374151; margin-right:6px; margin-top:4px;
}
.dossier-pill .site { font-weight:700; color:#1e3a5f; }
.dossier-pill .rm-dossier {
    background:none; border:none; color:#dc2626; cursor:pointer;
    font-size:12px; padding:0 2px; line-height:1;
}
.dossier-pill .rm-dossier:hover { color:#991b1b; }
#search-live { font-size:15px; padding:10px 16px; border-radius:10px; border:2px solid #e2e8f0; }
#search-live:focus { border-color:#1d4ed8; outline:none; }
.highlight { background:#fef9c3; border-radius:3px; padding:0 2px; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:400px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">👤 Suivi Clients</h2>
        <div style="font-size:13px;color:#64748b;" id="compteur-clients">
            {{ $clients->count() }} client(s)
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('dossiers.export-excel', request()->all()) }}" class="btn btn-success btn-sm">
            📥 Export Excel
        </a>
        <a href="{{ route('suivi-client.create') }}" class="btn btn-primary btn-sm">+ Nouveau client</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- ✅ FILTRES avec recherche dynamique --}}
<div class="filtre-box">
    <div class="row g-2 align-items-end">
        <div class="col-md-4">
            <label style="font-size:11px;font-weight:700;color:#64748b;">🔍 Recherche instantanée</label>
            <input type="text" id="search-live" class="form-control"
                   placeholder="Tapez un nom ou téléphone..."
                   value="{{ request('q') }}"
                   oninput="filtrerClients(this.value)">
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Créé du</label>
            <input type="date" name="du" id="filtre-du" class="form-control form-control-sm" value="{{ request('du') }}">
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Au</label>
            <input type="date" name="au" id="filtre-au" class="form-control form-control-sm" value="{{ request('au') }}">
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;">Grand Site</label>
            <select id="filtre-site" class="form-control form-control-sm" onchange="appliquerFiltresServeur()">
                <option value="">Tous les sites</option>
                @foreach($grandSites as $gs)
                    <option value="{{ $gs->id }}" {{ request('grand_site_id')==$gs->id?'selected':'' }}>
                        {{ $gs->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2 d-flex gap-2">
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
         data-phone="{{ $client->phone }}">
        <div class="d-flex justify-content-between align-items-start">
            <div style="flex:1;">
                {{-- Nom modifiable --}}
                <div style="display:flex;align-items:center;gap:8px;">
                    <div style="font-weight:700;font-size:15px;color:#1e3a5f;"
                         id="nom-{{ $client->id }}" class="client-nom">
                        {{ $client->name }}
                    </div>
                    <button onclick="ouvrirEditNom({{ $client->id }}, '{{ addslashes($client->name) }}')"
                            style="background:none;border:none;color:#f59e0b;cursor:pointer;font-size:13px;padding:2px 6px;"
                            title="Modifier le nom">✏️</button>
                </div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">
                    📞 {{ $client->phone ?? '-' }}
                    &nbsp;·&nbsp; 📂 <span id="nb-dossiers-{{ $client->id }}">{{ $client->dossiers->count() }}</span> dossier(s)
                </div>

                {{-- ✅ Dossiers sous forme de pills avec statuts de paiement --}}
                <div style="margin-top:6px;" id="dossiers-pills-{{ $client->id }}">
                    @foreach($client->dossiers as $d)
                    @php
                        // Totaux versés
                        $tD = $d->paiements->sum('montant');
                        $tT = $d->paiementsTechniques->sum('montant');
                        $tM = $d->paiementsMorcellements->sum('montant');
                        $tL = $d->paiementsLogistiques?->sum('montant') ?? 0;

                        // Prix de référence
                        $rD = $d->prix_superficie   ?? 0;
                        $rT = $d->prix_technique    ?? 0;
                        $rM = $d->prix_morcellement ?? 0;
                        $rL = $d->prix_logistique   ?? 0;

                        // Statut
                        $statutD = $rD > 0 ? ($tD >= $rD ? 'solde' : ($tD > 0 ? 'en_cours' : 'vide')) : ($tD > 0 ? 'en_cours' : 'vide');
                        $statutT = $rT > 0 ? ($tT >= $rT ? 'solde' : ($tT > 0 ? 'en_cours' : 'vide')) : ($tT > 0 ? 'en_cours' : 'vide');
                        $statutM = $rM > 0 ? ($tM >= $rM ? 'solde' : ($tM > 0 ? 'en_cours' : 'vide')) : ($tM > 0 ? 'en_cours' : 'vide');
                        $statutL = $rL > 0 ? ($tL >= $rL ? 'solde' : ($tL > 0 ? 'en_cours' : 'vide')) : ($tL > 0 ? 'en_cours' : 'vide');

                        // Couleurs
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
                            {{-- En-tête du dossier --}}
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

                            {{-- Badges de statut --}}
                            <div style="display:flex;flex-wrap:wrap;gap:4px;margin-top:6px;">
                                @if($tD > 0 || $rD > 0)
                                <span style="
                                    display:inline-flex;align-items:center;gap:4px;
                                    padding:2px 8px;border-radius:12px;font-size:9px;font-weight:700;
                                    background:{{ $couleurs[$statutD]['bg'] }};
                                    color:{{ $couleurs[$statutD]['text'] }};
                                    border:1.5px solid {{ $couleurs[$statutD]['border'] }};
                                ">
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
                                <span style="
                                    display:inline-flex;align-items:center;gap:4px;
                                    padding:2px 8px;border-radius:12px;font-size:9px;font-weight:700;
                                    background:{{ $couleurs[$statutT]['bg'] }};
                                    color:{{ $couleurs[$statutT]['text'] }};
                                    border:1.5px solid {{ $couleurs[$statutT]['border'] }};
                                ">
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
                                <span style="
                                    display:inline-flex;align-items:center;gap:4px;
                                    padding:2px 8px;border-radius:12px;font-size:9px;font-weight:700;
                                    background:{{ $couleurs[$statutL]['bg'] }};
                                    color:{{ $couleurs[$statutL]['text'] }};
                                    border:1.5px solid {{ $couleurs[$statutL]['border'] }};
                                ">
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
                                <span style="
                                    display:inline-flex;align-items:center;gap:4px;
                                    padding:2px 8px;border-radius:12px;font-size:9px;font-weight:700;
                                    background:{{ $couleurs[$statutM]['bg'] }};
                                    color:{{ $couleurs[$statutM]['text'] }};
                                    border:1.5px solid {{ $couleurs[$statutM]['border'] }};
                                ">
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

                            {{-- Statut global du dossier --}}
                            @if($totalRef > 0)
                            <div style="
                                margin-top:6px;
                                padding:4px 10px;
                                border-radius:6px;
                                background: {{ $tousSoldes ? '#dcfce7' : ($totalPaye > 0 ? '#fef3c7' : '#f1f5f9') }};
                                border: 1.5px solid {{ $tousSoldes ? '#86efac' : ($totalPaye > 0 ? '#fcd34d' : '#cbd5e1') }};
                                display:flex;
                                justify-content:space-between;
                                align-items:center;
                                font-size:10px;
                            ">
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

@endsection
@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';
let clientIdCourant = null;

// ============================================================
// ✅ FILTRAGE DYNAMIQUE CÔTÉ CLIENT
// ============================================================
function filtrerClients(terme) {
    const t       = terme.trim().toLowerCase();
    const cards   = document.querySelectorAll('#liste-clients .client-card');
    let   visible = 0;

    cards.forEach(card => {
        const nom   = card.dataset.nom   || '';
        const phone = card.dataset.phone || '';
        const match = !t || nom.includes(t) || phone.includes(t);
        card.style.display = match ? '' : 'none';
        if (match) visible++;

        // Surligner la correspondance
        if (match && t) {
            const nomEl = card.querySelector('.client-nom');
            if (nomEl) {
                const texte  = nomEl.dataset.original || nomEl.innerText;
                nomEl.dataset.original = texte;
                const regex  = new RegExp(`(${t})`, 'gi');
                nomEl.innerHTML = texte.replace(regex, '<span class="highlight">$1</span>');
            }
        } else {
            const nomEl = card.querySelector('.client-nom');
            if (nomEl && nomEl.dataset.original) {
                nomEl.innerText = nomEl.dataset.original;
            }
        }
    });

    // Mettre à jour compteur
    const compteur = document.getElementById('compteur-clients');
    if (compteur) compteur.innerText = visible + ' client(s)';
}

// Filtres serveur (date + site)
function appliquerFiltresServeur() {
    const du   = document.getElementById('filtre-du')?.value;
    const au   = document.getElementById('filtre-au')?.value;
    const site = document.getElementById('filtre-site')?.value;
    const q    = document.getElementById('search-live')?.value;
    const url  = new URL(window.location.href);
    du   ? url.searchParams.set('du',           du)   : url.searchParams.delete('du');
    au   ? url.searchParams.set('au',           au)   : url.searchParams.delete('au');
    site ? url.searchParams.set('grand_site_id',site) : url.searchParams.delete('grand_site_id');
    q    ? url.searchParams.set('q',            q)    : url.searchParams.delete('q');
    window.location.href = url.toString();
}

// ============================================================
// MODIFIER NOM CLIENT
// ============================================================
function ouvrirEditNom(clientId, nomActuel) {
    clientIdCourant = clientId;
    document.getElementById('input-nouveau-nom').value = nomActuel;
    document.getElementById('overlayNom').style.display = 'block';
    document.getElementById('modalNom').style.display   = 'block';
    setTimeout(() => document.getElementById('input-nouveau-nom').focus(), 100);
}
function fermerEditNom() {
    document.getElementById('overlayNom').style.display = 'none';
    document.getElementById('modalNom').style.display   = 'none';
    clientIdCourant = null;
}
function sauvegarderNom() {
    const nom = document.getElementById('input-nouveau-nom').value.trim();
    if (!nom) { alert('Le nom ne peut pas être vide.'); return; }
    fetch(`/admin/clients/${clientIdCourant}/modifier-nom`, {
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
                el.closest('.client-card').dataset.nom = data.nom.toLowerCase();
            }
            fermerEditNom();
        } else alert(data.message || 'Erreur');
    });
}

// Appliquer le filtre live au chargement si paramètre q dans l'URL
document.addEventListener('DOMContentLoaded', () => {
    const q = new URLSearchParams(window.location.search).get('q');
    if (q) filtrerClients(q);
});
</script>
@endsection