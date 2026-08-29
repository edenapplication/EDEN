@extends('rh.layout')
@section('content')

<style>
.param-card { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:20px; }
.param-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
.item-row { display:flex; justify-content:space-between; align-items:center; padding:8px 10px; border-radius:8px; margin-bottom:6px; background:#f8fafc; font-size:13px; }
.item-row:hover { background:#eff6ff; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:500px; max-height:90vh; overflow-y:auto; }

.badge-horaire { padding:2px 8px; border-radius:4px; font-size:9px; font-weight:600; }
.badge-horaire.travaille { background:#dcfce7; color:#15803d; }
.badge-horaire.ferie { background:#fee2e2; color:#b91c1c; }
.badge-horaire.repos { background:#f1f5f9; color:#475569; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;margin:0;">
        <i class="bi bi-gear"></i> Paramètres RH
    </h2>
    <span style="font-size:13px;color:#94a3b8;">Directions, Services, Postes & Horaires</span>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">

    {{-- ===================== DIRECTIONS ===================== --}}
    <div class="col-md-4">
        <div class="param-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-building"></i> Directions</h5>
                <button onclick="openModal('dirModal')" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Ajouter
                </button>
            </div>

            @forelse($directions as $d)
                <div class="item-row">
                    <div>
                        <span style="background:#dbeafe;color:#1d4ed8;padding:2px 8px;border-radius:6px;font-size:10px;font-weight:700;margin-right:6px;">
                            {{ $d->code }}
                        </span>
                        <strong>{{ $d->nom }}</strong>
                        <div style="font-size:11px;color:#64748b;margin-top:2px;">
                            {{ $d->services->count() }} service(s) — {{ $d->employes->count() }} employé(s)
                        </div>
                    </div>
                    <div class="d-flex gap-1">
                        <button onclick="editDirection({{ $d->id }}, '{{ $d->code }}', '{{ addslashes($d->nom) }}', '{{ addslashes($d->description) }}')"
                                class="btn btn-sm btn-outline-warning" title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <form action="{{ route('rh.directions.destroy', $d->id) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Supprimer cette direction ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-muted text-center py-3" style="font-size:13px;">
                    <i class="bi bi-building"></i> Aucune direction créée
                </div>
            @endforelse
        </div>
    </div>

    {{-- ===================== SERVICES ===================== --}}
    <div class="col-md-4">
        <div class="param-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-folder"></i> Services</h5>
                <button onclick="openModal('svcModal')" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Ajouter
                </button>
            </div>

            @foreach($directions as $d)
                @if($d->services->count())
                    <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:10px 0 4px;">
                        {{ $d->nom }}
                    </div>
                    @foreach($d->services as $s)
                        <div class="item-row">
                            <div>
                                <span>{{ $s->nom }}</span>
                                @if($s->relationLoaded('horaires') && $s->horaires->count())
                                    <span style="font-size:9px;color:#16a34a;display:block;">
                                        <i class="bi bi-clock"></i> {{ $s->horaires->where('est_travaille', true)->count() }} jours travaillés
                                    </span>
                                @endif
                            </div>
                            <div class="d-flex gap-1">
                                <button onclick="editService({{ $s->id }}, {{ $s->direction_id }}, '{{ addslashes($s->nom) }}')"
                                        class="btn btn-sm btn-outline-warning" title="Modifier">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button onclick="openHorairesModal({{ $s->id }}, '{{ addslashes($s->nom) }}')" 
                                        class="btn btn-sm btn-outline-info" title="Horaires">
                                    <i class="bi bi-clock"></i>
                                </button>
                                <form action="{{ route('rh.services.destroy', $s->id) }}" method="POST" style="display:inline"
                                      onsubmit="return confirm('Supprimer ce service ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach

            @if($directions->sum(fn($d) => $d->services->count()) === 0)
                <div class="text-muted text-center py-3" style="font-size:13px;">
                    <i class="bi bi-folder"></i> Aucun service créé
                </div>
            @endif
        </div>
    </div>

    {{-- ===================== POSTES ===================== --}}
    <div class="col-md-4">
        <div class="param-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0"><i class="bi bi-briefcase"></i> Postes</h5>
                <button onclick="openModal('posteModal')" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Ajouter
                </button>
            </div>

            @foreach($directions as $d)
                @if($d->postes->count())
                    <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:10px 0 4px;">
                        {{ $d->nom }}
                    </div>
                    @foreach($d->postes as $p)
                        <div class="item-row">
                            <div>
                                <span style="background:#f3e8ff;color:#7c3aed;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;margin-right:4px;">
                                    {{ $p->code }}
                                </span>
                                {{ $p->intitule }}
                            </div>
                            <form action="{{ route('rh.postes.destroy', $p->id) }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer ce poste ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                @endif
            @endforeach

            @if($directions->sum(fn($d) => $d->postes->count()) === 0)
                <div class="text-muted text-center py-3" style="font-size:13px;">
                    <i class="bi bi-briefcase"></i> Aucun poste créé
                </div>
            @endif
        </div>
    </div>

</div>

{{-- OVERLAY --}}
<div class="modal-overlay" id="overlay" onclick="closeAll()"></div>

{{-- ===================== MODAL DIRECTION ===================== --}}
<div class="modal-box" id="dirModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 id="dirModalTitle" style="color:#1e3a5f;font-weight:800;">
            <i class="bi bi-building"></i> Nouvelle direction
        </h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form id="dirForm" method="POST" action="{{ route('rh.directions.store') }}">
        @csrf
        <span id="dirMethod"></span>
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" id="dir_code" class="form-control" placeholder="Ex: DG" maxlength="20" required>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">Nom <span class="text-danger">*</span></label>
                <input type="text" name="nom" id="dir_nom" class="form-control" placeholder="Ex: Direction Générale" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" id="dir_desc" class="form-control" rows="2"></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
        </div>
    </form>
</div>

{{-- ===================== MODAL SERVICE ===================== --}}
<div class="modal-box" id="svcModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 id="svcModalTitle" style="color:#1e3a5f;font-weight:800;">
            <i class="bi bi-folder"></i> Nouveau service
        </h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form id="svcForm" method="POST" action="{{ route('rh.services.store') }}">
        @csrf
        <span id="svcMethod"></span>
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Direction <span class="text-danger">*</span></label>
                <select name="direction_id" id="svc_direction" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($directions as $d)
                        <option value="{{ $d->id }}">{{ $d->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Nom du service <span class="text-danger">*</span></label>
                <input type="text" name="nom" id="svc_nom" class="form-control" placeholder="Ex: Service Comptabilité" required>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" id="svc_desc" class="form-control" rows="2"></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
        </div>
    </form>
</div>

{{-- ===================== MODAL POSTE ===================== --}}
<div class="modal-box" id="posteModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;">
            <i class="bi bi-briefcase"></i> Nouveau poste
        </h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form method="POST" action="{{ route('rh.postes.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label fw-semibold">Direction</label>
                <select name="direction_id" class="form-control">
                    <option value="">-- Optionnel --</option>
                    @foreach($directions as $d)
                        <option value="{{ $d->id }}">{{ $d->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Service</label>
                <select name="service_id" class="form-control">
                    <option value="">-- Optionnel --</option>
                    @foreach($directions as $d)
                        @foreach($d->services as $s)
                            <option value="{{ $s->id }}">{{ $d->nom }} → {{ $s->nom }}</option>
                        @endforeach
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label fw-semibold">Code <span class="text-danger">*</span></label>
                <input type="text" name="code" class="form-control" placeholder="Ex: PDG" maxlength="30" required>
            </div>
            <div class="col-md-8">
                <label class="form-label fw-semibold">Intitulé <span class="text-danger">*</span></label>
                <input type="text" name="intitule" class="form-control" placeholder="Ex: Président Directeur Général" required>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
        </div>
    </form>
</div>

{{-- ===================== MODAL HORAIRES ===================== --}}
<div class="modal-box" id="horairesModal" style="width:550px;">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;">
            <i class="bi bi-clock"></i> Gestion des horaires
        </h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form id="horairesForm" method="POST" action="{{ route('rh.horaires.store') }}">
        @csrf
        <input type="hidden" name="service_id" id="horaire_service_id">
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Service</label>
                <input type="text" id="horaire_service_nom" class="form-control" readonly style="background:#f8fafc;font-weight:600;color:#1e3a5f;">
            </div>
            <div class="col-12">
                <div style="font-size:11px;font-weight:600;color:#64748b;margin-bottom:6px;">Définir les horaires par jour</div>
                <div class="row g-2">
                    @foreach(['lundi','mardi','mercredi','jeudi','vendredi','samedi','dimanche'] as $jour)
                        <div class="col-md-6">
                            <div style="display:flex;align-items:center;gap:6px;padding:4px 0;border-bottom:1px solid #f1f5f9;">
                                <span style="font-weight:600;font-size:12px;min-width:75px;">{{ ucfirst($jour) }}</span>
                                <input type="time" name="horaires[{{ $jour }}][debut]" class="form-control form-control-sm" placeholder="Début" style="width:90px;">
                                <span style="color:#94a3b8;">→</span>
                                <input type="time" name="horaires[{{ $jour }}][fin]" class="form-control form-control-sm" placeholder="Fin" style="width:90px;">
                                <label style="font-size:10px;color:#64748b;display:flex;align-items:center;gap:3px;margin-left:4px;">
                                    <input type="checkbox" name="horaires[{{ $jour }}][est_travaille]" value="1" checked>
                                    Travaillé
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="col-12">
                <div style="background:#fef9c3;border-radius:8px;padding:8px 12px;font-size:11px;color:#92400e;">
                    <i class="bi bi-info-circle"></i> 
                    Les horaires définis ici serviront de référence pour le calcul des retards et des heures supplémentaires.
                    <br>
                    <span style="font-weight:600;">Par défaut :</span> Lundi-Vendredi 08:00-17:30, Samedi 08:00-13:00, Dimanche non travaillé.
                </div>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer les horaires</button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
function openModal(id) {
    document.getElementById('overlay').style.display = 'block';
    document.getElementById(id).style.display = 'block';
}

function closeAll() {
    document.getElementById('overlay').style.display = 'none';
    document.querySelectorAll('.modal-box').forEach(m => m.style.display = 'none');
}

function editDirection(id, code, nom, desc) {
    document.getElementById('dirModalTitle').innerHTML = '<i class="bi bi-pencil"></i> Modifier direction';
    document.getElementById('dir_code').value = code;
    document.getElementById('dir_nom').value  = nom;
    document.getElementById('dir_desc').value = desc;
    const form = document.getElementById('dirForm');
    form.action = '/rh/directions/' + id;
    document.getElementById('dirMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    openModal('dirModal');
}

function editService(id, directionId, nom) {
    document.getElementById('svcModalTitle').innerHTML = '<i class="bi bi-pencil"></i> Modifier service';
    document.getElementById('svc_direction').value = directionId;
    document.getElementById('svc_nom').value = nom;
    const form = document.getElementById('svcForm');
    form.action = '/rh/services/' + id;
    document.getElementById('svcMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    openModal('svcModal');
}

function openHorairesModal(id, nom) {
    document.getElementById('horaire_service_id').value = id;
    document.getElementById('horaire_service_nom').value = nom || 'Service #' + id;
    openModal('horairesModal');
}

// Initialisation des horaires par défaut (08:00 - 17:30 pour les jours ouvrés)
document.addEventListener('DOMContentLoaded', function() {
    const joursOuvres = ['lundi', 'mardi', 'mercredi', 'jeudi', 'vendredi'];
    const samedi = ['samedi'];
    
    joursOuvres.forEach(jour => {
        const debut = document.querySelector(`input[name="horaires[${jour}][debut]"]`);
        const fin = document.querySelector(`input[name="horaires[${jour}][fin]"]`);
        if (debut) debut.value = '08:00';
        if (fin) fin.value = '17:30';
    });
    
    samedi.forEach(jour => {
        const debut = document.querySelector(`input[name="horaires[${jour}][debut]"]`);
        const fin = document.querySelector(`input[name="horaires[${jour}][fin]"]`);
        if (debut) debut.value = '08:00';
        if (fin) fin.value = '13:00';
    });
    
    // Dimanche : non travaillé par défaut
    const dimanche = document.querySelector(`input[name="horaires[dimanche][est_travaille]"]`);
    if (dimanche) dimanche.checked = false;
});
</script>
@endsection