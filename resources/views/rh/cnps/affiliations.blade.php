@extends('rh.layout')
@section('content')

<style>
.statut-badge { padding:3px 10px; border-radius:8px; font-size:11px; font-weight:600; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">📋 Affiliations CNPS</h2>
    <a href="{{ route('rh.cnps.index') }}" class="btn btn-outline-secondary btn-sm">← Dashboard</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- FILTRES --}}
<form method="GET" class="d-flex gap-2 mb-4 flex-wrap align-items-end" style="background:white;border-radius:12px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Rechercher</label>
        <input type="text" name="search" class="form-control form-control-sm" placeholder="Nom, matricule..." value="{{ request('search') }}" style="width:200px;">
    </div>
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Situation</label>
        <select name="situation" class="form-control form-control-sm" style="width:150px;">
            <option value="">Tous</option>
            @foreach(\App\Models\RH\CnpsAffiliation::SITUATIONS as $k => $v)
                <option value="{{ $k }}" {{ request('situation')==$k?'selected':'' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <button type="submit" class="btn btn-primary btn-sm">🔍</button>
    <a href="{{ route('rh.cnps.affiliations') }}" class="btn btn-outline-secondary btn-sm">✖</a>
</form>

<div class="row g-3">
    {{-- LISTE DES AFFILIATIONS --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">👥 Employés affiliés</div>
            <div class="card-body p-0">
                <div style="overflow-x:auto;">
                <table class="table table-bordered table-hover mb-0" style="font-size:12px;">
                    <thead class="table-dark">
                        <tr>
                            <th>Matricule</th>
                            <th>Nom & Prénom</th>
                            <th>N° CNPS</th>
                            <th>Centre</th>
                            <th>Situation</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse($affiliations as $a)
                        <tr>
                            <td style="font-weight:600;color:#1d4ed8;">{{ $a->employe?->matricule }}</td>
                            <td>{{ $a->employe?->nom }} {{ $a->employe?->prenom }}</td>
                            <td>{{ $a->numero_cnps ?? '-' }}</td>
                            <td>{{ $a->centre_cnps ?? '-' }}</td>
                            <td>
                                <span class="statut-badge" style="background:{{ $a->situation_color }};color:#1e293b;">
                                    {{ $a->situation_label }}
                                </span>
                            </td>
                            <td>
                                <button onclick="openEditModal({{ $a->id }}, {{ $a->employe_id }}, '{{ $a->numero_cnps }}', '{{ $a->date_affiliation?->format('Y-m-d') }}', '{{ $a->centre_cnps }}', '{{ $a->situation_affiliation }}', '{{ $a->categorie_cnps }}', '{{ $a->salaire_soumis }}')"
                                        class="btn btn-sm btn-warning" title="Modifier">✏️</button>
                                <form action="{{ route('rh.cnps.affiliations.destroy', $a->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette affiliation ?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Supprimer">🗑</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Aucune affiliation enregistrée</td></tr>
                    @endforelse
                    </tbody>
                </table>
                </div>
            </div>
            <div class="card-footer">
                {{ $affiliations->links() }}
            </div>
        </div>
    </div>

    {{-- EMPLOYÉS SANS AFFILIATION --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header" style="background:#fef2f2;font-weight:700;color:#dc2626;">⚠️ Sans affiliation</div>
            <div class="card-body" style="max-height:400px;overflow-y:auto;">
                @forelse($sansAffiliation as $e)
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <div>
                            <span style="font-weight:600;font-size:12px;">{{ $e->nom }} {{ $e->prenom }}</span>
                            <span style="font-size:11px;color:#64748b;display:block;">{{ $e->matricule }}</span>
                        </div>
                        <button onclick="openCreateModal({{ $e->id }}, '{{ $e->nom }} {{ $e->prenom }}')"
                                class="btn btn-sm btn-primary">+</button>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">Tous les employés ont une affiliation</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

{{-- MODAL CRÉATION/MODIFICATION --}}
<div class="modal fade" id="affiliationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="affiliationForm" method="POST" action="{{ route('rh.cnps.affiliations.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="affiliationModalTitle">📋 Affiliation CNPS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="employe_id" id="aff_employe_id">
                    <div class="mb-3">
                        <label class="form-label">Employé</label>
                        <input type="text" id="aff_employe_nom" class="form-control" readonly style="background:#f8fafc;font-weight:600;">
                    </div>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">N° CNPS</label>
                            <input type="text" name="numero_cnps" id="aff_numero_cnps" class="form-control" placeholder="Ex: 123456789">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Date d'affiliation</label>
                            <input type="date" name="date_affiliation" id="aff_date_affiliation" class="form-control">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Centre CNPS</label>
                            <input type="text" name="centre_cnps" id="aff_centre_cnps" class="form-control" placeholder="Ex: Yaoundé">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Situation <span class="text-danger">*</span></label>
                            <select name="situation_affiliation" id="aff_situation" class="form-control" required>
                                @foreach(\App\Models\RH\CnpsAffiliation::SITUATIONS as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Catégorie CNPS</label>
                            <input type="text" name="categorie_cnps" id="aff_categorie_cnps" class="form-control" placeholder="Ex: AGENT">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Salaire soumis</label>
                            <input type="number" name="salaire_soumis" id="aff_salaire_soumis" class="form-control" min="0" step="1000">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" id="aff_notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreateModal(id, nom) {
    document.getElementById('affiliationModalTitle').textContent = '➕ Nouvelle affiliation';
    document.getElementById('aff_employe_id').value = id;
    document.getElementById('aff_employe_nom').value = nom;
    document.getElementById('aff_numero_cnps').value = '';
    document.getElementById('aff_date_affiliation').value = '';
    document.getElementById('aff_centre_cnps').value = '';
    document.getElementById('aff_situation').value = 'en_cours';
    document.getElementById('aff_categorie_cnps').value = '';
    document.getElementById('aff_salaire_soumis').value = '';
    document.getElementById('aff_notes').value = '';
    new bootstrap.Modal(document.getElementById('affiliationModal')).show();
}

function openEditModal(id, empId, numero, date, centre, situation, categorie, salaire) {
    document.getElementById('affiliationModalTitle').textContent = '✏️ Modifier l\'affiliation';
    document.getElementById('aff_employe_id').value = empId;
    document.getElementById('aff_employe_nom').value = document.querySelector(`tr:has([value="${empId}"])`)?.textContent || '';
    document.getElementById('aff_numero_cnps').value = numero || '';
    document.getElementById('aff_date_affiliation').value = date || '';
    document.getElementById('aff_centre_cnps').value = centre || '';
    document.getElementById('aff_situation').value = situation || 'en_cours';
    document.getElementById('aff_categorie_cnps').value = categorie || '';
    document.getElementById('aff_salaire_soumis').value = salaire || '';
    document.getElementById('aff_notes').value = '';
    new bootstrap.Modal(document.getElementById('affiliationModal')).show();
}
</script>

@endsection