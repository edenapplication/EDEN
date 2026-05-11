@extends('rh.layout')
@section('content')

<style>
.param-card { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:20px; }
.param-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
.item-row { display:flex; justify-content:space-between; align-items:center; padding:8px 10px; border-radius:8px; margin-bottom:6px; background:#f8fafc; font-size:13px; }
.item-row:hover { background:#eff6ff; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:460px; }
</style>

<h2 style="color:#1e3a5f;font-weight:800;margin-bottom:20px;">⚙️ Directions, Services & Postes</h2>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

<div class="row g-3">

    {{-- ===================== DIRECTIONS ===================== --}}
    <div class="col-md-4">
        <div class="param-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">🏢 Directions</h5>
                <button onclick="openModal('dirModal')" class="btn btn-primary btn-sm">+ Ajouter</button>
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
                                style="background:#fef3c7;color:#92400e;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">✏️</button>
                        <form action="{{ route('rh.directions.destroy', $d->id) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Supprimer cette direction ?')">
                            @csrf @method('DELETE')
                            <button style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:13px;">🗑</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="text-muted text-center py-3" style="font-size:13px;">Aucune direction créée</div>
            @endforelse
        </div>
    </div>

    {{-- ===================== SERVICES ===================== --}}
    <div class="col-md-4">
        <div class="param-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">🗂️ Services</h5>
                <button onclick="openModal('svcModal')" class="btn btn-primary btn-sm">+ Ajouter</button>
            </div>

            @foreach($directions as $d)
                @if($d->services->count())
                    <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:10px 0 4px;">{{ $d->nom }}</div>
                    @foreach($d->services as $s)
                        <div class="item-row">
                            <span>{{ $s->nom }}</span>
                            <div class="d-flex gap-1">
                                <button onclick="editService({{ $s->id }}, {{ $s->direction_id }}, '{{ addslashes($s->nom) }}')"
                                        style="background:#fef3c7;color:#92400e;border:none;border-radius:4px;padding:2px 8px;font-size:11px;cursor:pointer;">✏️</button>
                                <form action="{{ route('rh.services.destroy', $s->id) }}" method="POST" style="display:inline"
                                      onsubmit="return confirm('Supprimer ce service ?')">
                                    @csrf @method('DELETE')
                                    <button style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:13px;">🗑</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach

            @if($directions->sum(fn($d) => $d->services->count()) === 0)
                <div class="text-muted text-center py-3" style="font-size:13px;">Aucun service créé</div>
            @endif
        </div>
    </div>

    {{-- ===================== POSTES ===================== --}}
    <div class="col-md-4">
        <div class="param-card">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">💼 Postes</h5>
                <button onclick="openModal('posteModal')" class="btn btn-primary btn-sm">+ Ajouter</button>
            </div>

            @foreach($directions as $d)
                @if($d->postes->count())
                    <div style="font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin:10px 0 4px;">{{ $d->nom }}</div>
                    @foreach($d->postes as $p)
                        <div class="item-row">
                            <div>
                                <span style="background:#f3e8ff;color:#7c3aed;padding:2px 6px;border-radius:4px;font-size:10px;font-weight:700;margin-right:4px;">{{ $p->code }}</span>
                                {{ $p->intitule }}
                            </div>
                            <form action="{{ route('rh.postes.destroy', $p->id) }}" method="POST" style="display:inline"
                                  onsubmit="return confirm('Supprimer ce poste ?')">
                                @csrf @method('DELETE')
                                <button style="background:none;border:none;color:#dc2626;cursor:pointer;font-size:13px;">🗑</button>
                            </form>
                        </div>
                    @endforeach
                @endif
            @endforeach

            @if($directions->sum(fn($d) => $d->postes->count()) === 0)
                <div class="text-muted text-center py-3" style="font-size:13px;">Aucun poste créé</div>
            @endif
        </div>
    </div>

</div>

{{-- OVERLAY --}}
<div class="modal-overlay" id="overlay" onclick="closeAll()"></div>

{{-- MODAL DIRECTION --}}
<div class="modal-box" id="dirModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 id="dirModalTitle" style="color:#1e3a5f;font-weight:800;">🏢 Nouvelle direction</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
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
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </div>
    </form>
</div>

{{-- MODAL SERVICE --}}
<div class="modal-box" id="svcModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 id="svcModalTitle" style="color:#1e3a5f;font-weight:800;">🗂️ Nouveau service</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
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
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </div>
    </form>
</div>

{{-- MODAL POSTE --}}
<div class="modal-box" id="posteModal">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;">💼 Nouveau poste</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
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
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
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
    document.getElementById('dirModalTitle').innerText = '✏️ Modifier direction';
    document.getElementById('dir_code').value = code;
    document.getElementById('dir_nom').value  = nom;
    document.getElementById('dir_desc').value = desc;
    const form = document.getElementById('dirForm');
    form.action = `/admin/rh/directions/${id}`;
    document.getElementById('dirMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    openModal('dirModal');
}

function editService(id, directionId, nom) {
    document.getElementById('svcModalTitle').innerText = '✏️ Modifier service';
    document.getElementById('svc_direction').value = directionId;
    document.getElementById('svc_nom').value = nom;
    const form = document.getElementById('svcForm');
    form.action = `/admin/rh/services/${id}`;
    document.getElementById('svcMethod').innerHTML = '<input type="hidden" name="_method" value="PUT">';
    openModal('svcModal');
}
</script>
@endsection