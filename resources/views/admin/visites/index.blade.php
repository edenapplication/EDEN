@extends('admin.layout')
@section('content')

<style>
:root { --primary:#1e3a5f; --accent:#4D96FF; --border:#e2e8f0; }

.page-header {
    background:linear-gradient(135deg, #1d4ed8 0%, #7c3aed 55%, #dc2626 100%);
    color:white; padding:18px 24px; border-radius:14px; margin-bottom:20px;
}
.filter-card {
    background:white; border-radius:12px; padding:16px 20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px;
}

.v-table-wrap { overflow-x:auto; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); }
.v-table { width:100%; border-collapse:collapse; font-size:12px; background:white; }
.v-table thead tr { background:var(--primary); color:white; }
.v-table thead th { padding:10px 8px; font-weight:600; white-space:nowrap; text-align:left; }
.v-table tbody tr:nth-child(even) { background:#f8fafc; }
.v-table tbody tr:hover { background:#eff6ff; }
.v-table tbody td { padding:8px; border-bottom:1px solid var(--border); white-space:nowrap; }

.badge-type { font-size:10px; padding:2px 8px; border-radius:10px; font-weight:600; }
.bt-client       { background:#dbeafe; color:#1d4ed8; }
.bt-proprietaire { background:#dcfce7; color:#15803d; }
.bt-autre        { background:#f1f5f9; color:#475569; }
.bt-paiement     { background:#fef3c7; color:#92400e; font-size:9px; }

.visiteur-wrap { position:relative; }
.visiteur-dropdown {
    position:absolute; top:100%; left:0; right:0; z-index:9999;
    background:white; border:1px solid #ddd; border-radius:8px;
    box-shadow:0 4px 12px rgba(0,0,0,0.1);
    max-height:200px; overflow-y:auto; display:none;
}
.visiteur-option {
    padding:8px 12px; cursor:pointer; font-size:13px;
    border-bottom:1px solid #f8fafc;
    display:flex; justify-content:space-between;
}
.visiteur-option:hover { background:#eff6ff; }

.modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,0.4); z-index:9998;
}
.modal-box {
    display:none; position:fixed; top:50%; left:50%;
    transform:translate(-50%,-50%);
    background:white; padding:24px; border-radius:14px;
    box-shadow:0 10px 30px rgba(0,0,0,0.2);
    z-index:9999; width:500px; max-height:92vh; overflow-y:auto;
}
</style>

<div class="page-header">
    <h2 class="mb-1">🚶 Registre des visites</h2>
    <p class="mb-0" style="opacity:0.8;">Suivi des entrées et sorties — les paiements de dossier y sont automatiquement enregistrés</p>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

{{-- ACTIONS --}}
<div class="d-flex gap-2 mb-3 flex-wrap align-items-center">
    <button onclick="openModal()" class="btn btn-primary">+ Nouvelle visite</button>

    <form method="POST" action="{{ route('visites.import') }}" enctype="multipart/form-data"
          class="d-flex gap-2 align-items-center">
        @csrf
        <input type="file" name="fichier" class="form-control form-control-sm" style="width:220px;"
               accept=".csv,.xlsx,.xls,.txt">
        <button type="submit" class="btn btn-outline-success btn-sm">📥 Importer CSV</button>
    </form>

    <a href="{{ route('visites.export', array_merge(request()->all(), ['type'=>'pdf'])) }}"
       class="btn btn-danger btn-sm">📄 PDF</a>
    <a href="{{ route('visites.export', array_merge(request()->all(), ['type'=>'excel'])) }}"
       class="btn btn-success btn-sm">📊 Excel</a>
    <button onclick="imprimerTableau()" class="btn btn-outline-secondary btn-sm">🖨️ Imprimer</button>
</div>

{{-- GUIDE IMPORT --}}
<div style="background:#eff6ff; border:1px solid #bfdbfe; border-radius:8px; padding:10px 14px; font-size:11px; color:#1e3a5f; margin-bottom:14px;">
    📋 <strong>Format CSV d'import (séparateur ;) :</strong>
    Date ; Nom ; Numéro ; Type (client/proprietaire/autre) ; Heure arrivée ; Heure départ ; — ; — ; Note<br>
    Formats de date acceptés : <strong>dd/mm/yyyy</strong> ou <strong>yyyy-mm-dd</strong> —
    Heures acceptées : <strong>HH:MM</strong> ou <strong>HH:MM:SS</strong>
</div>

{{-- FILTRES --}}
<form method="GET" action="{{ route('visites.index') }}" class="filter-card">
    <div class="row g-2 align-items-end">

        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">🔍 Nom / Numéro</label>
            <input type="text" name="nom" class="form-control form-control-sm"
                   value="{{ request('nom') }}" placeholder="Rechercher...">
        </div>

        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">👤 Type</label>
            <select name="type_personne" class="form-control form-control-sm">
                <option value="">Tous</option>
                <option value="client"       {{ request('type_personne')==='client'       ? 'selected':'' }}>Client</option>
                <option value="proprietaire" {{ request('type_personne')==='proprietaire' ? 'selected':'' }}>Propriétaire</option>
                <option value="autre"        {{ request('type_personne')==='autre'        ? 'selected':'' }}>Autre</option>
            </select>
        </div>

        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">🏢 Grand Site</label>
            <select name="grand_site_id" class="form-control form-control-sm">
                <option value="">Tous</option>
                @foreach($grandsites as $gs)
                    <option value="{{ $gs->id }}" {{ request('grand_site_id')==$gs->id ? 'selected':'' }}>
                        {{ $gs->nom }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- ✅ Filtre site --}}
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">🗺️ Site</label>
            <select name="site_id" class="form-control form-control-sm">
                <option value="">Tous</option>
                @foreach($sites as $s)
                    <option value="{{ $s->id }}" {{ request('site_id')==$s->id ? 'selected':'' }}>
                        {{ $s->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">📅 Du</label>
            <input type="date" name="date_debut" class="form-control form-control-sm"
                   value="{{ request('date_debut') }}">
        </div>

        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">📅 Au</label>
            <input type="date" name="date_fin" class="form-control form-control-sm"
                   value="{{ request('date_fin') }}">
        </div>

        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">🗓️ Mois</label>
            <input type="month" name="mois" class="form-control form-control-sm"
                   value="{{ request('mois') }}">
        </div>

        {{-- ✅ Filtre nombre de visites --}}
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:600;color:#64748b;">🔢 Nb visites (entre)</label>
            <div class="d-flex gap-1 align-items-center">
                <input type="number" name="nb_min" class="form-control form-control-sm"
                       value="{{ request('nb_min') }}" placeholder="Min" min="1" style="width:70px;">
                <span style="font-size:12px;color:#64748b;">et</span>
                <input type="number" name="nb_max" class="form-control form-control-sm"
                       value="{{ request('nb_max') }}" placeholder="Max" min="1" style="width:70px;">
                <small class="text-muted" style="font-size:10px;">visites</small>
            </div>
        </div>

        {{-- COLONNES --}}
        <div class="col-12 mt-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">📋 Colonnes à afficher</label>
            <div class="d-flex flex-wrap gap-2 mt-1">
                @foreach($colonnesDisponibles as $key => $label)
                    <label style="font-size:11px;background:#f1f5f9;padding:3px 8px;border-radius:6px;cursor:pointer;">
                        <input type="checkbox" name="colonnes[]" value="{{ $key }}"
                            {{ in_array($key, $colonnesChoisies) ? 'checked' : '' }}>
                        {{ $label }}
                    </label>
                @endforeach
            </div>
        </div>

        <div class="col-12 d-flex gap-2 mt-2">
            <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrer</button>
            <a href="{{ route('visites.index') }}" class="btn btn-outline-secondary btn-sm">✖ Reset</a>
        </div>
    </div>
</form>

{{-- TABLEAU --}}
<div class="v-table-wrap" id="tableau-visites">
<table class="v-table">
    <thead>
        <tr>
            <th>#</th>
            @if(in_array('date_visite',   $colonnesChoisies)) <th>Date</th> @endif
            @if(in_array('nom',           $colonnesChoisies)) <th>Nom</th> @endif
            @if(in_array('numero',        $colonnesChoisies)) <th>Numéro</th> @endif
            @if(in_array('type_personne', $colonnesChoisies)) <th>Type</th> @endif
            @if(in_array('heure_arrivee', $colonnesChoisies)) <th>Arrivée</th> @endif
            @if(in_array('heure_depart',  $colonnesChoisies)) <th>Départ</th> @endif
            @if(in_array('grand_site',    $colonnesChoisies)) <th>Grand Site</th> @endif
            @if(in_array('site',          $colonnesChoisies)) <th>Site</th> @endif
            @if(in_array('nb_visites',    $colonnesChoisies)) <th>Nb visites</th> @endif
            @if(in_array('note',          $colonnesChoisies)) <th>Note</th> @endif
             @if(in_array('bon',           $colonnesChoisies)) <th>Bon N°</th> @endif
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($visites as $i => $v)
<tr>
    <td>{{ ($visites->currentPage() - 1) * $visites->perPage() + $i + 1 }}</td>

    @if(in_array('date_visite', $colonnesChoisies))
        <td>{{ \Carbon\Carbon::parse($v->date_visite)->format('d/m/Y') }}</td>
    @endif

    @if(in_array('nom', $colonnesChoisies))
        <td>
            <strong>{{ $v->visiteur?->nom ?? '-' }}</strong>
            @if($v->paiement_lie)
                <span class="badge-type bt-paiement ms-1">💳 paiement</span>
            @endif
        </td>
    @endif

    @if(in_array('numero', $colonnesChoisies))
        <td>{{ $v->visiteur?->numero ?? '-' }}</td>
    @endif

    @if(in_array('type_personne', $colonnesChoisies))
        <td>
            <span class="badge-type bt-{{ $v->type_personne }}">{{ ucfirst($v->type_personne) }}</span>
        </td>
    @endif

    @if(in_array('heure_arrivee', $colonnesChoisies))
        <td>{{ $v->heure_arrivee ? substr($v->heure_arrivee, 0, 5) : '-' }}</td>
    @endif

    @if(in_array('heure_depart', $colonnesChoisies))
        <td>
            @if($v->heure_depart)
                {{ substr($v->heure_depart, 0, 5) }}
            @elseif(!$v->paiement_lie)
                <button onclick="enregistrerDepart({{ $v->id }})"
                        style="background:#fef3c7;color:#92400e;font-size:10px;padding:2px 8px;border-radius:6px;border:none;cursor:pointer;">
                    ⏱ Départ
                </button>
            @else
                <span style="color:#94a3b8;font-size:10px;">—</span>
            @endif
        </td>
    @endif

    @if(in_array('grand_site', $colonnesChoisies))
        <td>{{ $v->grandSite?->nom ?? '-' }}</td>
    @endif

    @if(in_array('site', $colonnesChoisies))
        <td>{{ $v->site?->name ?? '-' }}</td>
    @endif

    @if(in_array('nb_visites', $colonnesChoisies))
        <td>
            <span style="background:#eff6ff;color:#1d4ed8;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;">
                {{ $comptageVisites[$v->visiteur_id] ?? 1 }}
            </span>
        </td>
    @endif

    @if(in_array('note', $colonnesChoisies))
        <td style="max-width:180px;overflow:hidden;text-overflow:ellipsis;" title="{{ $v->note }}">
            {{ $v->note ?? '-' }}
        </td>
    @endif

    @if(in_array('bon', $colonnesChoisies)) <!-- ✅ ICI - Ajouter la colonne Bon N° -->
        <td>
            @if($v->bon_id && $v->bon)
                <a href="{{ route('bons.show', $v->bon_id) }}" 
                   style="color:#1d4ed8;text-decoration:underline;font-size:11px;">
                    {{ $v->bon->numero_bon }}
                </a>
            @else
                <span style="color:#94a3b8;">—</span>
            @endif
        </td>
    @endif

    <td>
        <button onclick="supprimerVisite({{ $v->id }})"
                style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:14px;"
                title="Supprimer">🗑</button>
    </td>
</tr>
@empty
    <tr><td colspan="15" class="text-center text-muted py-4">Aucune visite enregistrée</td></tr>
@endforelse
    </tbody>
</table>
</div>

<div class="mt-3">{{ $visites->links() }}</div>

{{-- OVERLAY + MODAL --}}
<div class="modal-overlay" id="modalOverlay" onclick="closeModal()"></div>
<div class="modal-box" id="visiteModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0" style="color:#1e3a5f;font-weight:800;">🚶 Nouvelle visite</h5>
        <button onclick="closeModal()" style="background:none;border:none;font-size:18px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div class="mb-3">
        <label style="font-weight:600;font-size:13px;">👤 Visiteur</label>
        <div class="visiteur-wrap mt-1">
            <input type="text" id="visiteur_search" class="form-control"
                   placeholder="🔍 Rechercher par nom ou numéro..." autocomplete="off">
            <div class="visiteur-dropdown" id="visiteur_dropdown"></div>
        </div>
        <input type="hidden" id="visiteur_id">
        <div id="visiteur_info" style="display:none;margin-top:6px;padding:6px 10px;background:#ecfdf5;border-radius:6px;font-size:12px;color:#065f46;"></div>

        <div style="display:flex;align-items:center;gap:8px;margin:8px 0;color:#94a3b8;font-size:12px;">
            <hr style="flex:1;margin:0;"> nouveau visiteur <hr style="flex:1;margin:0;">
        </div>
        <div class="row g-2">
            <div class="col-7">
                <input type="text" id="visiteur_nom" class="form-control form-control-sm" placeholder="Nom complet">
            </div>
            <div class="col-5">
                <input type="text" id="visiteur_numero" class="form-control form-control-sm" placeholder="Numéro tél.">
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label style="font-weight:600;font-size:13px;">🏷️ Type</label>
        <select id="type_personne" class="form-control mt-1" onchange="toggleSiteFields(this.value)">
            <option value="autre">Autre</option>
            <option value="client">Client</option>
            <option value="proprietaire">Propriétaire</option>
        </select>
    </div>

    <div id="siteFields" style="display:none;">
        <div class="row g-2 mb-3">
            <div class="col-6">
                <label style="font-weight:600;font-size:13px;">🏢 Grand Site</label>
                <select id="grand_site_id" class="form-control form-control-sm mt-1">
                    <option value="">-- Choisir --</option>
                    @foreach($grandsites as $gs)
                        <option value="{{ $gs->id }}">{{ $gs->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-6">
                <label style="font-weight:600;font-size:13px;">🗺️ Site</label>
                <select id="site_id" class="form-control form-control-sm mt-1">
                    <option value="">-- Choisir --</option>
                    @foreach($sites as $s)
                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="row g-2 mb-3">
        <div class="col-4">
            <label style="font-weight:600;font-size:13px;">📅 Date</label>
            <input type="date" id="date_visite" class="form-control form-control-sm mt-1"
                   value="{{ now()->format('Y-m-d') }}">
        </div>
        <div class="col-4">
            <label style="font-weight:600;font-size:13px;">⏰ Arrivée</label>
            <input type="time" id="heure_arrivee" class="form-control form-control-sm mt-1">
        </div>
        <div class="col-4">
            <label style="font-weight:600;font-size:13px;">🚪 Départ</label>
            <input type="time" id="heure_depart" class="form-control form-control-sm mt-1">
        </div>
    </div>

    <div class="mb-3">
        <label style="font-weight:600;font-size:13px;">📝 Note</label>
        <textarea id="note" class="form-control form-control-sm mt-1" rows="2"
                  placeholder="Remarque optionnelle..."></textarea>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button onclick="closeModal()" class="btn btn-light">Annuler</button>
        <button onclick="sauvegarderVisite()" class="btn btn-primary">💾 Enregistrer</button>
    </div>
</div>

@endsection

@section('scripts')
<script>
let searchTimer = null;

function openModal() {
    document.getElementById('visiteur_id').value     = '';
    document.getElementById('visiteur_search').value = '';
    document.getElementById('visiteur_nom').value    = '';
    document.getElementById('visiteur_numero').value = '';
    document.getElementById('visiteur_info').style.display = 'none';
    document.getElementById('type_personne').value   = 'autre';
    document.getElementById('siteFields').style.display = 'none';
    document.getElementById('date_visite').value     = '{{ now()->format("Y-m-d") }}';
    document.getElementById('heure_arrivee').value   = new Date().toTimeString().slice(0,5);
    document.getElementById('heure_depart').value    = '';
    document.getElementById('note').value            = '';
    document.getElementById('modalOverlay').style.display = 'block';
    document.getElementById('visiteModal').style.display  = 'block';
}

function closeModal() {
    document.getElementById('modalOverlay').style.display = 'none';
    document.getElementById('visiteModal').style.display  = 'none';
}

function toggleSiteFields(type) {
    document.getElementById('siteFields').style.display =
        (type === 'client' || type === 'proprietaire') ? 'block' : 'none';
}

// AUTOCOMPLETE
document.getElementById('visiteur_search').addEventListener('input', function() {
    const q = this.value.trim();
    clearTimeout(searchTimer);
    const dd = document.getElementById('visiteur_dropdown');
    if (q.length < 1) { dd.style.display = 'none'; return; }

    searchTimer = setTimeout(() => {
        fetch(`/admin/visites/visiteur-search?q=${encodeURIComponent(q)}`)
        .then(r => r.json())
        .then(visiteurs => {
            if (!visiteurs.length) { dd.style.display = 'none'; return; }
            dd.innerHTML = visiteurs.map(v =>
                `<div class="visiteur-option"
                      data-id="${v.id}" data-nom="${v.nom}" data-numero="${v.numero ?? ''}">
                    <strong>${v.nom}</strong>
                    <small style="color:#64748b;">${v.numero ?? ''} — ${v.type}</small>
                </div>`
            ).join('');
            dd.style.display = 'block';
            dd.querySelectorAll('.visiteur-option').forEach(opt => {
                opt.addEventListener('click', function() {
                    document.getElementById('visiteur_id').value     = this.dataset.id;
                    document.getElementById('visiteur_search').value = this.dataset.nom;
                    document.getElementById('visiteur_nom').value    = this.dataset.nom;
                    document.getElementById('visiteur_numero').value = this.dataset.numero;
                    dd.style.display = 'none';
                    const info = document.getElementById('visiteur_info');
                    info.innerHTML     = `✅ <strong>${this.dataset.nom}</strong> ${this.dataset.numero}`;
                    info.style.display = 'block';
                });
            });
        });
    }, 200);
});

document.addEventListener('click', e => {
    if (!e.target.closest('.visiteur-wrap'))
        document.getElementById('visiteur_dropdown').style.display = 'none';
});

function sauvegarderVisite() {
    const visiteurId = document.getElementById('visiteur_id').value;
    const nom        = document.getElementById('visiteur_nom').value.trim();
    if (!visiteurId && !nom) { alert('Veuillez sélectionner ou saisir un visiteur.'); return; }

    fetch('/admin/visites', {
        method : 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body   : JSON.stringify({
            visiteur_id:   visiteurId || null,
            nom:           nom        || null,
            numero:        document.getElementById('visiteur_numero').value.trim() || null,
            type_personne: document.getElementById('type_personne').value,
            date_visite:   document.getElementById('date_visite').value,
            heure_arrivee: document.getElementById('heure_arrivee').value || null,
            heure_depart:  document.getElementById('heure_depart').value  || null,
            grand_site_id: document.getElementById('grand_site_id')?.value || null,
            site_id:       document.getElementById('site_id')?.value       || null,
            note:          document.getElementById('note').value           || null,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { closeModal(); location.reload(); }
        else alert(data.message || 'Erreur');
    });
}

function enregistrerDepart(id) {
    const heure = new Date().toTimeString().slice(0,5);
    fetch(`/admin/visites/${id}`, {
        method : 'PUT',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':'{{ csrf_token() }}' },
        body   : JSON.stringify({ heure_depart: heure })
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); });
}

function supprimerVisite(id) {
    if (!confirm('Supprimer cette visite ?')) return;
    fetch(`/admin/visites/${id}`, {
        method : 'DELETE',
        headers: { 'X-CSRF-TOKEN':'{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(data => { if (data.success) location.reload(); });
}

function imprimerTableau() {
    const contenu = document.getElementById('tableau-visites').outerHTML;
    const w = window.open('', '_blank');
    w.document.write(`
        <!DOCTYPE html><html><head>
        <title>Registre des visites</title>
        <style>
            body { font-family:sans-serif; font-size:11px; padding:20px; }
            table { width:100%; border-collapse:collapse; }
            th { background:#1e3a5f; color:white; padding:6px 8px; text-align:left; font-size:11px; }
            td { padding:5px 8px; border-bottom:1px solid #e2e8f0; font-size:11px; }
            tr:nth-child(even) { background:#f8fafc; }
            h2 { color:#1e3a5f; margin-bottom:12px; }
            .v-table-wrap { overflow:visible !important; }
        </style>
        </head><body>
        <h2>🚶 Registre des visites — {{ now()->format('d/m/Y') }}</h2>
        ${contenu}
        <script>window.onload=()=>{window.print();window.close()}<\/script>
        </body></html>
    `);
    w.document.close();
}
</script>
@endsection