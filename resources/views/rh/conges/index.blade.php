@extends('rh.layout')
@section('content')

<style>
.conge-card { background:white; border-radius:12px; padding:14px 16px; margin-bottom:10px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #f59e0b; }
.conge-card.en_cours { border-left-color:#1d4ed8; }
.conge-card.termine  { border-left-color:#16a34a; opacity:0.8; }
.conge-card.annule   { border-left-color:#dc2626; opacity:0.7; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:480px; max-height:90vh; overflow-y:auto; }
.kpi-box { background:white; border-radius:10px; padding:12px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
.kpi-box .v { font-size:22px; font-weight:800; }
.kpi-box .l { font-size:9px; color:#64748b; font-weight:700; text-transform:uppercase; margin-top:2px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🏖️ Planning des Congés</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.conges.planning-pdf', request()->all()) }}" class="btn btn-outline-danger btn-sm">🖨️ PDF Planning</a>
        <button onclick="openModal('addModal')" class="btn btn-primary">+ Nouveau congé</button>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col"><div class="kpi-box" style="border-top:3px solid #1e3a5f;">
        <div class="v" style="color:#1e3a5f;">{{ $stats['total'] }}</div>
        <div class="l">Total</div>
    </div></div>
    <div class="col"><div class="kpi-box" style="border-top:3px solid #f59e0b;">
        <div class="v" style="color:#f59e0b;">{{ $stats['planifie'] }}</div>
        <div class="l">Planifiés</div>
    </div></div>
    <div class="col"><div class="kpi-box" style="border-top:3px solid #1d4ed8;">
        <div class="v" style="color:#1d4ed8;">{{ $stats['en_cours'] }}</div>
        <div class="l">En cours</div>
    </div></div>
    <div class="col"><div class="kpi-box" style="border-top:3px solid #16a34a;">
        <div class="v" style="color:#16a34a;">{{ $stats['termine'] }}</div>
        <div class="l">Terminés</div>
    </div></div>
    <div class="col"><div class="kpi-box" style="border-top:3px solid #dc2626;">
        <div class="v" style="color:#dc2626;">{{ $stats['annule'] }}</div>
        <div class="l">Annulés</div>
    </div></div>
</div>

{{-- FILTRES --}}
<form method="GET" class="d-flex gap-2 mb-4 flex-wrap align-items-end"
      style="background:white;border-radius:12px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <div>
        <label class="form-label fw-semibold" style="font-size:11px;">Employé</label>
        <select name="employe_id" class="form-control form-control-sm" style="min-width:200px;">
            <option value="">Tous les employés</option>
            @foreach($employes as $e)
                <option value="{{ $e->id }}" {{ request('employe_id')==$e->id?'selected':'' }}>
                    {{ $e->nom }} {{ $e->prenom }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label class="form-label fw-semibold" style="font-size:11px;">Statut</label>
        <select name="statut" class="form-control form-control-sm">
            <option value="">Tous</option>
            <option value="planifie" {{ request('statut')==='planifie'?'selected':'' }}>Planifié</option>
            <option value="en_cours" {{ request('statut')==='en_cours'?'selected':'' }}>En cours</option>
            <option value="termine"  {{ request('statut')==='termine' ?'selected':'' }}>Terminé</option>
            <option value="annule"   {{ request('statut')==='annule'  ?'selected':'' }}>Annulé</option>
        </select>
    </div>
    <div>
        <label class="form-label fw-semibold" style="font-size:11px;">Mois</label>
        <input type="month" name="mois" class="form-control form-control-sm" value="{{ request('mois') }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrer</button>
    <a href="{{ route('rh.conges.index') }}" class="btn btn-outline-secondary btn-sm">✖ Reset</a>
</form>

{{-- LISTE --}}
@forelse($conges as $c)
<div class="conge-card {{ $c->statut }}">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div style="font-weight:700;font-size:14px;color:#1e3a5f;">
                {{ $c->employe?->nom }} {{ $c->employe?->prenom }}
                <span style="font-size:11px;color:#64748b;margin-left:6px;">{{ $c->employe?->matricule }}</span>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">
                {{ $c->employe?->direction?->nom ?? '-' }}
            </div>
            <div style="font-size:11px;color:#0f766e;font-weight:700;">
    📊 Pris : {{ $c->employe->jours_pris ?? 0 }}/15
    | Restants : {{ $c->employe->jours_restants ?? 15 }}
</div>
        </div>
        <div class="text-end">
            <span style="background:{{ $c->statut_color }}22;color:{{ $c->statut_color }};padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;">
                {{ $c->statut_label }}
            </span>
        </div>
    </div>

    {{-- PLANNING VISUEL 15 jours --}}
    <div style="margin:12px 0;">
        <div style="font-size:11px;color:#64748b;margin-bottom:6px;">
            📅 Du <strong>{{ $c->date_debut->format('d/m/Y') }}</strong>
            au <strong>{{ $c->date_fin->format('d/m/Y') }}</strong>
            — <strong>{{ $c->nb_jours }} jours</strong>
            @if($c->motif) — {{ $c->motif }} @endif
        </div>
        {{-- Barre des 15 jours --}}
        <div style="display:flex;gap:2px;margin-bottom:4px;">
            @for($i = 0; $i < $c->nb_jours; $i++)
                @php
                    $jour     = $c->date_debut->copy()->addDays($i);
                    $estWeek  = $jour->isWeekend();
                    $estAuj   = $jour->isToday();
                    $passe    = $jour->isPast();
                    $bg       = $estWeek ? '#e2e8f0' : ($estAuj ? '#1d4ed8' : ($passe ? '#16a34a' : '#bfdbfe'));
                    $txt      = $estWeek ? '#94a3b8' : ($estAuj ? 'white' : ($passe ? 'white' : '#1d4ed8'));
                @endphp
                <div title="{{ $jour->format('D d/m') }}"
                     style="flex:1;height:28px;border-radius:4px;background:{{ $bg }};
                            display:flex;align-items:center;justify-content:center;
                            font-size:9px;font-weight:700;color:{{ $txt }};
                            border:{{ $estAuj ? '2px solid #1d4ed8' : '1px solid #e2e8f0' }};">
                    {{ $jour->format('d') }}
                </div>
            @endfor
        </div>
        <div style="display:flex;gap:12px;font-size:9px;color:#94a3b8;">
            <span><span style="display:inline-block;width:10px;height:10px;background:#bfdbfe;border-radius:2px;margin-right:3px;"></span>À venir</span>
            <span><span style="display:inline-block;width:10px;height:10px;background:#16a34a;border-radius:2px;margin-right:3px;"></span>Passé</span>
            <span><span style="display:inline-block;width:10px;height:10px;background:#1d4ed8;border-radius:2px;margin-right:3px;"></span>Aujourd'hui</span>
            <span><span style="display:inline-block;width:10px;height:10px;background:#e2e8f0;border-radius:2px;margin-right:3px;"></span>Weekend</span>
        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex gap-2 mt-2">
        <a href="{{ route('rh.conges.pdf', $c->id) }}"
           class="btn btn-outline-danger btn-sm" style="font-size:11px;">
            🖨️ Attestation
        </a>

        {{-- Changer statut --}}
        @if($c->statut !== 'annule' && $c->statut !== 'termine')
        <form action="{{ route('rh.conges.update', $c->id) }}" method="POST" style="display:inline;">
            @csrf @method('PUT')
            @if($c->statut === 'planifie')
                <input type="hidden" name="statut" value="en_cours">
                <button class="btn btn-primary btn-sm" style="font-size:11px;">▶ Démarrer</button>
            @elseif($c->statut === 'en_cours')
                <input type="hidden" name="statut" value="termine">
                <button class="btn btn-success btn-sm" style="font-size:11px;">✅ Terminer</button>
            @endif
        </form>
        @endif

        {{-- Modifier --}}
        <button onclick="openEditModal(
    {{ $c->id }},
    {{ $c->employe_id }},
    '{{ $c->date_debut->format('Y-m-d') }}',
    {{ $c->nb_jours }},
    '{{ addslashes($c->motif ?? '') }}',
    '{{ $c->statut }}',
    '{{ addslashes($c->notes ?? '') }}'
)" class="btn btn-warning btn-sm" style="font-size:11px;">
    ✏️ Modifier
</button>

        {{-- Annuler / Supprimer --}}
        @if($c->statut === 'planifie')
        <form action="{{ route('rh.conges.update', $c->id) }}" method="POST" style="display:inline;">
            @csrf @method('PUT')
            <input type="hidden" name="statut" value="annule">
            <button class="btn btn-outline-danger btn-sm" style="font-size:11px;"
                    onclick="return confirm('Annuler ce congé ?')">🚫 Annuler</button>
        </form>
        @endif

        @if(in_array($c->statut, ['annule','termine']))
        <form action="{{ route('rh.conges.destroy', $c->id) }}" method="POST" style="display:inline;"
              onsubmit="return confirm('Supprimer définitivement ?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm" style="font-size:11px;">🗑 Supprimer</button>
        </form>
        @endif
    </div>
</div>
@empty
    <div class="text-center text-muted py-5">Aucun congé enregistré</div>
@endforelse

{{-- MODAL AJOUT --}}
<div class="modal-overlay" id="overlayAdd" onclick="closeAll()"></div>
<div class="modal-box" id="addModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">🏖️ Planifier un congé</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
    </div>
    ℹ️ Vous pouvez attribuer de 1 à 15 jours de congé.
Le dimanche n'est pas comptabilisé dans le calcul.
    <form method="POST" action="{{ route('rh.conges.store') }}">
        @csrf
        <div class="row g-3">
            <div class="col-12">
                <label class="form-label fw-semibold">Employé <span class="text-danger">*</span></label>
                <select name="employe_id" class="form-control" required>
                    <option value="">-- Choisir --</option>
                    @foreach($employes as $e)
                        <option value="{{ $e->id }}">
    {{ $e->nom }} {{ $e->prenom }}
    ({{ $e->matricule }})
    - Pris : {{ $e->jours_pris }}/15
    - Restants : {{ $e->jours_restants }}
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
    <label class="form-label fw-semibold">
        Nombre de jours
    </label>
    <input type="number"
           name="nb_jours"
           id="add_nb_jours"
           min="1"
           max="15"
           value="1"
           class="form-control"
           oninput="calculerFinAdd()">
</div>
            <div class="col-md-6">
                <label class="form-label fw-semibold">Date de fin <span class="text-muted">(auto)</span></label>
                <input type="text" id="add_date_fin_display" class="form-control" readonly
                       style="background:#f1f5f9;font-weight:700;color:#1e3a5f;">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" class="form-control" placeholder="Ex: Congé annuel">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Notes internes</label>
                <textarea name="notes" class="form-control" rows="2"></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
        </div>
    </form>
</div>

{{-- MODAL MODIFICATION --}}
<div class="modal-overlay" id="overlayEdit" onclick="closeAll()"></div>
<div class="modal-box" id="editModal">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 style="color:#1e3a5f;font-weight:800;">✏️ Modifier le congé</h5>
        <button onclick="closeAll()" style="background:none;border:none;font-size:18px;cursor:pointer;">✕</button>
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
    <label class="form-label fw-semibold">
        Nombre de jours
    </label>

    <input type="number"
           name="nb_jours"
           id="edit_nb_jours"
           min="1"
           max="15"
           class="form-control"
           oninput="calculerFinEdit()">
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
            <div class="col-md-6">
                <label class="form-label fw-semibold">Motif</label>
                <input type="text" name="motif" id="edit_motif" class="form-control">
            </div>
            <div class="col-12">
                <label class="form-label fw-semibold">Notes</label>
                <textarea name="notes" id="edit_notes" class="form-control" rows="2"></textarea>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <button type="button" onclick="closeAll()" class="btn btn-light">Annuler</button>
            <button type="submit" class="btn btn-warning">💾 Mettre à jour</button>
        </div>
    </form>
</div>

@endsection
@section('scripts')
<script>
function openModal(id) {
    document.getElementById('overlayAdd').style.display = 'block';
    document.getElementById(id).style.display = 'block';
    calculerFinAdd();
}
function openEditModal(
    id,
    empId,
    dateDebut,
    nbJours,
    motif,
    statut,
    notes
)
{
    document.getElementById('editForm').action = `/rh/conges/${id}`;

    document.getElementById('edit_employe').value = empId;
    document.getElementById('edit_date_debut').value = dateDebut;
    document.getElementById('edit_nb_jours').value = nbJours;

    document.getElementById('edit_motif').value = motif;
    document.getElementById('edit_statut').value = statut;
    document.getElementById('edit_notes').value = notes;

    calculerFinEdit();

    document.getElementById('overlayEdit').style.display = 'block';
    document.getElementById('editModal').style.display = 'block';
}

function closeAll() {
    ['overlayAdd','overlayEdit','addModal','editModal']
        .forEach(id => { const el = document.getElementById(id); if(el) el.style.display='none'; });
}

// Calcul automatique date de fin (+14 jours = 15 jours)
function ajouterJours(dateStr, nb) {
    const d = new Date(dateStr);
    d.setDate(d.getDate() + nb);
    return d.toLocaleDateString('fr-FR', {day:'2-digit',month:'2-digit',year:'numeric'});
}
function calculerFinAdd()
{
    const debut = document.getElementById('add_date_debut').value;
    const jours = parseInt(
        document.getElementById('add_nb_jours').value || 1
    );

    if (!debut) return;

    let date = new Date(debut);
    let compteur = 1;

    while (compteur < jours) {

        date.setDate(date.getDate() + 1);

        if (date.getDay() !== 0) {
            compteur++;
        }
    }

    document.getElementById('add_date_fin_display').value =
        date.toLocaleDateString('fr-FR');
}

function calculerFinEdit()
{
    const debut = document.getElementById('edit_date_debut').value;
    const jours = parseInt(
        document.getElementById('edit_nb_jours').value || 1
    );

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
        date.toLocaleDateString('fr-FR');
}

document.addEventListener('DOMContentLoaded', calculerFinAdd);
</script>
@endsection