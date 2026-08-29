@extends('rh.layout')
@section('content')

<style>
.conge-card {
    background:white;
    border-radius:14px;
    padding:18px 20px;
    margin-bottom:12px;
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    border-left:4px solid #f59e0b;
    transition:transform 0.2s, box-shadow 0.2s;
}
.conge-card:hover { transform:translateX(4px); box-shadow:0 4px 16px rgba(0,0,0,0.08); }
.conge-card.en_cours { border-left-color:#1d4ed8; }
.conge-card.termine  { border-left-color:#16a34a; opacity:0.75; }
.conge-card.annule   { border-left-color:#94a3b8; opacity:0.6; }

.badge-status {
    padding:3px 12px;
    border-radius:10px;
    font-size:10px;
    font-weight:600;
    display:inline-block;
}
.badge-status.planifie { background:#fef3c7; color:#92400e; }
.badge-status.en_cours { background:#dbeafe; color:#1d4ed8; }
.badge-status.termine { background:#dcfce7; color:#15803d; }
.badge-status.annule { background:#f1f5f9; color:#475569; }

.kpi-box { background:white; border-radius:10px; padding:12px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
.kpi-box .v { font-size:20px; font-weight:800; }
.kpi-box .l { font-size:9px; color:#64748b; font-weight:600; text-transform:uppercase; margin-top:2px; }

.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:480px; max-height:90vh; overflow-y:auto; }

.filter-bar { background:white; border-radius:12px; padding:14px 18px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🏖️ Planning des Congés</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.conges.planning-pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-pdf"></i> PDF Planning
        </a>
        <button onclick="openModal('addModal')" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nouveau congé
        </button>
    </div>
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

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #1e3a5f;">
            <div class="v" style="color:#1e3a5f;">{{ $stats['total'] }}</div>
            <div class="l">Total</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #f59e0b;">
            <div class="v" style="color:#f59e0b;">{{ $stats['planifie'] }}</div>
            <div class="l">Planifiés</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #1d4ed8;">
            <div class="v" style="color:#1d4ed8;">{{ $stats['en_cours'] }}</div>
            <div class="l">En cours</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #16a34a;">
            <div class="v" style="color:#16a34a;">{{ $stats['termine'] }}</div>
            <div class="l">Terminés</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #94a3b8;">
            <div class="v" style="color:#94a3b8;">{{ $stats['annule'] }}</div>
            <div class="l">Annulés</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" class="filter-bar">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Employé</label>
            <select name="employe_id" class="form-control form-control-sm">
                <option value="">Tous les employés</option>
                @foreach($employes as $e)
                    <option value="{{ $e->id }}" {{ request('employe_id')==$e->id?'selected':'' }}>
                        {{ $e->nom }} {{ $e->prenom }}
                        ({{ $e->jours_restants }} restants)
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Statut</label>
            <select name="statut" class="form-control form-control-sm">
                <option value="">Tous</option>
                <option value="planifie" {{ request('statut')==='planifie'?'selected':'' }}>Planifié</option>
                <option value="en_cours" {{ request('statut')==='en_cours'?'selected':'' }}>En cours</option>
                <option value="termine"  {{ request('statut')==='termine' ?'selected':'' }}>Terminé</option>
                <option value="annule"   {{ request('statut')==='annule'  ?'selected':'' }}>Annulé</option>
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Mois</label>
            <input type="month" name="mois" class="form-control form-control-sm" value="{{ request('mois') }}">
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i> Filtrer</button>
        </div>
        <div class="col-md-3 text-end">
            <a href="{{ route('rh.conges.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i> Reset</a>
        </div>
    </div>
</form>

{{-- LISTE DES CONGÉS --}}
@forelse($conges as $c)
<div class="conge-card {{ $c->statut }}">
    <div class="d-flex justify-content-between align-items-start">
        <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <div style="font-weight:700;font-size:15px;color:#1e3a5f;">
                    {{ $c->employe?->nom }} {{ $c->employe?->prenom }}
                    <span style="font-size:11px;color:#64748b;font-weight:400;margin-left:6px;">{{ $c->employe?->matricule }}</span>
                </div>
                <span style="font-size:11px;color:#64748b;">
                    {{ $c->employe?->direction?->nom ?? '-' }}
                </span>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;display:flex;gap:16px;flex-wrap:wrap;">
                <span>📅 {{ $c->date_debut->format('d/m/Y') }} → {{ $c->date_fin->format('d/m/Y') }}</span>
                <span>📊 <strong style="color:#1d4ed8;">{{ $c->nb_jours }}</strong> jours ouvrés</span>
                @if($c->motif)
                    <span>📌 {{ $c->motif }}</span>
                @endif
            </div>
            <div style="margin-top:6px;font-size:11px;color:#0f766e;font-weight:600;">
                📊 Pris : {{ $c->employe->jours_pris ?? 0 }}/15
                | Restants : <strong style="color:#16a34a;">{{ $c->employe->jours_restants ?? 15 }}</strong>
            </div>
        </div>
        <div class="text-end" style="flex-shrink:0;margin-left:16px;">
            <span class="badge-status {{ $c->statut }}">
                @if($c->statut === 'planifie') <i class="bi bi-calendar-event"></i> @endif
                @if($c->statut === 'en_cours') <i class="bi bi-play-circle"></i> @endif
                @if($c->statut === 'termine') <i class="bi bi-check-circle"></i> @endif
                @if($c->statut === 'annule') <i class="bi bi-x-circle"></i> @endif
                {{ $c->statut_label }}
            </span>
        </div>
    </div>

    {{-- BARRE VISUELLE DES JOURS (excluant les dimanches) --}}
    <div style="margin:12px 0;">
        <div style="display:flex;gap:2px;flex-wrap:wrap;">
            @php
                $date = $c->date_debut->copy();
                $joursAffiches = 0;
                $maxJours = min($c->nb_jours + 7, 22); // Marge pour les week-ends
            @endphp
            @for($i = 0; $i < $maxJours && $joursAffiches < $c->nb_jours; $i++)
                @php
                    $estWeek = $date->isSunday();
                    $estAuj = $date->isToday();
                    $passe = $date->isPast() && !$date->isToday();
                    $estDansPeriode = $date->between($c->date_debut, $c->date_fin);
                    
                    if (!$estWeek) {
                        $joursAffiches++;
                    }
                    
                    $bg = $estWeek ? '#f1f5f9' : ($estAuj ? '#1d4ed8' : ($passe ? '#16a34a' : '#bfdbfe'));
                    $txt = $estWeek ? '#94a3b8' : ($estAuj ? 'white' : ($passe ? 'white' : '#1d4ed8'));
                    $border = $estAuj ? '2px solid #1d4ed8' : '1px solid #e2e8f0';
                    $opacity = $estWeek ? '0.5' : '1';
                    $title = $date->format('D d/m') . ($estWeek ? ' (Dimanche non compté)' : '');
                @endphp
                <div title="{{ $title }}"
                     style="flex:1;min-width:28px;height:28px;border-radius:4px;background:{{ $bg }};
                            display:flex;align-items:center;justify-content:center;
                            font-size:8px;font-weight:700;color:{{ $txt }};
                            border:{{ $border }};opacity:{{ $opacity }};
                            cursor:default;">
                    {{ $date->format('d') }}
                    @if($estWeek)
                        <span style="font-size:6px;color:#94a3b8;margin-left:1px;">✕</span>
                    @endif
                </div>
                @php $date->addDay(); @endphp
            @endfor
        </div>
        <div style="display:flex;gap:14px;font-size:9px;color:#94a3b8;margin-top:6px;flex-wrap:wrap;">
            <span><span style="display:inline-block;width:12px;height:12px;background:#bfdbfe;border-radius:2px;margin-right:4px;vertical-align:middle;"></span>À venir</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#16a34a;border-radius:2px;margin-right:4px;vertical-align:middle;"></span>Passé</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#1d4ed8;border-radius:2px;margin-right:4px;vertical-align:middle;"></span>Aujourd'hui</span>
            <span><span style="display:inline-block;width:12px;height:12px;background:#f1f5f9;border-radius:2px;border:1px solid #e2e8f0;margin-right:4px;vertical-align:middle;"></span>Dimanche</span>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="d-flex gap-2 mt-2 flex-wrap">
        <a href="{{ route('rh.conges.pdf', $c->id) }}" class="btn btn-sm btn-outline-danger">
            <i class="bi bi-file-pdf"></i> Attestation
        </a>

        @if($c->statut !== 'annule' && $c->statut !== 'termine')
            <form action="{{ route('rh.conges.update', $c->id) }}" method="POST" style="display:inline;">
                @csrf @method('PUT')
                @if($c->statut === 'planifie')
                    <input type="hidden" name="statut" value="en_cours">
                    <button class="btn btn-sm btn-primary"><i class="bi bi-play-fill"></i> Démarrer</button>
                @elseif($c->statut === 'en_cours')
                    <input type="hidden" name="statut" value="termine">
                    <button class="btn btn-sm btn-success"><i class="bi bi-check-lg"></i> Terminer</button>
                @endif
            </form>
        @endif

        <button onclick="openEditModal(
            {{ $c->id }},
            {{ $c->employe_id }},
            '{{ $c->date_debut->format('Y-m-d') }}',
            {{ $c->nb_jours }},
            '{{ addslashes($c->motif ?? '') }}',
            '{{ $c->statut }}',
            '{{ addslashes($c->notes ?? '') }}'
        )" class="btn btn-sm btn-outline-warning">
            <i class="bi bi-pencil"></i> Modifier
        </button>

        @if($c->statut === 'planifie')
            <form action="{{ route('rh.conges.update', $c->id) }}" method="POST" style="display:inline;">
                @csrf @method('PUT')
                <input type="hidden" name="statut" value="annule">
                <button class="btn btn-sm btn-outline-secondary" onclick="return confirm('Annuler ce congé ?')">
                    <i class="bi bi-x-lg"></i> Annuler
                </button>
            </form>
        @endif

        @if(in_array($c->statut, ['annule','termine']))
            <form action="{{ route('rh.conges.destroy', $c->id) }}" method="POST" style="display:inline;"
                  onsubmit="return confirm('Supprimer définitivement ce congé ?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Supprimer</button>
            </form>
        @endif
    </div>
</div>
@empty
    <div class="text-center text-muted py-5">
        <div style="font-size:48px;margin-bottom:16px;">🏖️</div>
        <p style="font-size:16px;">Aucun congé enregistré</p>
        <p style="font-size:13px;color:#94a3b8;">Cliquez sur "Nouveau congé" pour en planifier un.</p>
    </div>
@endforelse

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeModal('addModal')"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;"><i class="bi bi-plus-circle"></i> Planifier un congé</h5>
        <button onclick="closeModal('addModal')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <div style="background:#fef9c3;border-radius:8px;padding:10px 14px;font-size:12px;color:#92400e;margin-bottom:16px;">
        <i class="bi bi-info-circle"></i> Vous pouvez attribuer de 1 à 15 jours de congé.
        <strong>Le dimanche n'est pas comptabilisé</strong> dans le calcul.
    </div>
    <form method="POST" action="{{ route('rh.conges.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Employé <span class="text-danger">*</span></label>
                <select name="employe_id" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}">
                            {{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})
                            - Pris : {{ $e->jours_pris }}/15 - Restants : {{ $e->jours_restants }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date de début <span class="text-danger">*</span></label>
                <input type="date" name="date_debut" id="add_date_debut" class="form-control"
                       value="{{ now()->format('Y-m-d') }}" required oninput="calculerFinAdd()">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nombre de jours <span class="text-danger">*</span></label>
                <input type="number" name="nb_jours" id="add_nb_jours" min="1" max="15" value="1"
                       class="form-control" required oninput="calculerFinAdd()">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date de fin <span class="text-muted">(auto)</span></label>
                <input type="text" id="add_date_fin_display" class="form-control" readonly
                       style="background:#f1f5f9;font-weight:700;color:#1e3a5f;">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" class="form-control" placeholder="Ex: Congé annuel">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Notes internes</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeModal('addModal')" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
        </div>
    </form>
</div>

{{-- MODAL MODIFICATION --}}
<div class="modal-overlay" id="overlayEdit" onclick="closeModal('editModal')"></div>
<div class="modal-box" id="editModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;"><i class="bi bi-pencil"></i> Modifier le congé</h5>
        <button onclick="closeModal('editModal')" style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <form id="editForm" method="POST" action="">
        @csrf @method('PUT')
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Employé</label>
                <select name="employe_id" id="edit_employe" class="form-control" required>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}">{{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date de début</label>
                <input type="date" name="date_debut" id="edit_date_debut" class="form-control"
                       oninput="calculerFinEdit()">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Nombre de jours</label>
                <input type="number" name="nb_jours" id="edit_nb_jours" min="1" max="15"
                       class="form-control" oninput="calculerFinEdit()">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date de fin (auto)</label>
                <input type="text" id="edit_date_fin_display" class="form-control" readonly
                       style="background:#f1f5f9;font-weight:700;color:#1e3a5f;">
            </div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Statut</label>
                <select name="statut" id="edit_statut" class="form-control">
                    <option value="planifie">Planifié</option>
                    <option value="en_cours">En cours</option>
                    <option value="termine">Terminé</option>
                    <option value="annule">Annulé</option>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" id="edit_motif" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Notes</label>
                <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeModal('editModal')" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-warning"><i class="bi bi-save"></i> Mettre à jour</button>
        </div>
    </form>
</div>

@endsection

@section('scripts')
<script>
// ============================================================
// CALCUL DATE DE FIN (exclut les dimanches)
// ============================================================
function calculerFinAdd() {
    const debut = document.getElementById('add_date_debut').value;
    const jours = parseInt(document.getElementById('add_nb_jours').value || 1);

    if (!debut) return;

    let date = new Date(debut);
    let compteur = 1;

    while (compteur < jours) {
        date.setDate(date.getDate() + 1);
        if (date.getDay() !== 0) { // 0 = Dimanche
            compteur++;
        }
    }

    document.getElementById('add_date_fin_display').value =
        date.toLocaleDateString('fr-FR', {day:'2-digit', month:'2-digit', year:'numeric'});
}

function calculerFinEdit() {
    const debut = document.getElementById('edit_date_debut').value;
    const jours = parseInt(document.getElementById('edit_nb_jours').value || 1);

    if (!debut) return;

    let date = new Date(debut);
    let compteur = 1;

    while (compteur < jours) {
        date.setDate(date.getDate() + 1);
        if (date.getDay() !== 0) {
            compteur++;
        }
    }

    document.getElementById('edit_date_fin_display').value =
        date.toLocaleDateString('fr-FR', {day:'2-digit', month:'2-digit', year:'numeric'});
}

// ============================================================
// MODALS
// ============================================================
function openModal(id) {
    const overlayId = id === 'addModal' ? 'overlayAdd' : 'overlayEdit';
    document.getElementById(overlayId).style.display = 'block';
    document.getElementById(id).style.display = 'block';
    if (id === 'addModal') calculerFinAdd();
}

function closeModal(id) {
    document.getElementById('overlayAdd').style.display = 'none';
    document.getElementById('overlayEdit').style.display = 'none';
    document.getElementById(id).style.display = 'none';
}

function closeAll() {
    document.querySelectorAll('.modal-overlay').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.modal-box').forEach(el => el.style.display = 'none');
}

function openEditModal(id, empId, dateDebut, nbJours, motif, statut, notes) {
    document.getElementById('editForm').action = `/rh/conges/${id}`;
    document.getElementById('edit_employe').value = empId;
    document.getElementById('edit_date_debut').value = dateDebut;
    document.getElementById('edit_nb_jours').value = nbJours;
    document.getElementById('edit_motif').value = motif || '';
    document.getElementById('edit_statut').value = statut;
    document.getElementById('edit_notes').value = notes || '';
    document.getElementById('overlayEdit').style.display = 'block';
    document.getElementById('editModal').style.display = 'block';
    calculerFinEdit();
}

document.addEventListener('DOMContentLoaded', function() {
    calculerFinAdd();
});
</script>
@endsection