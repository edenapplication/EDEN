@extends('rh.layout')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🩺 Visites médicales</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.sante.visites.pdf') }}{{ request()->getQueryString() ? '?'.request()->getQueryString() : '' }}" 
           class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-pdf"></i> PDF
        </a>
        <a href="{{ route('rh.sante.visites.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Nouvelle visite
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- FILTRES --}}
<form method="GET" class="filter-bar">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Employé</label>
            <select name="employe_id" class="form-control form-control-sm">
                <option value="">Tous</option>
                @foreach($employes as $e)
                    <option value="{{ $e->id }}" {{ request('employe_id')==$e->id?'selected':'' }}>
                        {{ $e->nom }} {{ $e->prenom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Type</label>
            <select name="type" class="form-control form-control-sm">
                <option value="">Tous</option>
                @foreach(\App\Models\RH\VisiteMedicale::TYPES as $k => $v)
                    <option value="{{ $k }}" {{ request('type')==$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Statut</label>
            <select name="statut" class="form-control form-control-sm">
                <option value="">Tous</option>
                @foreach(\App\Models\RH\VisiteMedicale::STATUTS as $k => $v)
                    <option value="{{ $k }}" {{ request('statut')==$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
        </div>
        <div class="col-md-3 text-end">
            <a href="{{ route('rh.sante.visites') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i> Reset</a>
        </div>
    </div>
</form>

{{-- TABLEAU --}}
<div style="overflow-x:auto;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);background:white;">
<table class="table table-modern">
    <thead>
        <tr>
            <th>Employé</th>
            <th>Type</th>
            <th>Date</th>
            <th>Aptitude</th>
            <th>Prochaine visite</th>
            <th>Statut</th>
            <th style="width:100px;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($visites as $v)
        <tr>
            <td>
                <strong>{{ $v->employe?->nom }} {{ $v->employe?->prenom }}</strong>
                <br>
                <small style="color:#94a3b8;font-size:10px;">{{ $v->employe?->matricule }}</small>
            </td>
            <td>{{ $v->type_label }}</td>
            <td>{{ $v->date_visite->format('d/m/Y') }}</td>
            <td>
                <span class="badge-status" style="background:{{ $v->aptitude === 'apte' ? '#dcfce7' : ($v->aptitude === 'apte_avec_restriction' ? '#fef3c7' : '#fee2e2') }};color:{{ $v->aptitude === 'apte' ? '#15803d' : ($v->aptitude === 'apte_avec_restriction' ? '#92400e' : '#b91c1c') }};">
                    {{ $v->aptitude_label }}
                </span>
            </td>
            <td>
                @if($v->prochaine_visite)
                    {{ $v->prochaine_visite->format('d/m/Y') }}
                    @if($v->est_expiree)
                        <span class="text-danger" style="font-size:10px;">⚠️ Expirée</span>
                    @elseif($v->jours_restants && $v->jours_restants <= 30)
                        <span class="text-warning" style="font-size:10px;">{{ $v->jours_restants }}j</span>
                    @endif
                @else
                    -
                @endif
            </td>
            <td>
                <span class="badge-status" style="background:{{ $v->statut_color }};color:#1e293b;">
                    {{ $v->statut_label }}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('rh.sante.visites.show', $v->id) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('rh.sante.visites.edit', $v->id) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('rh.sante.visites.destroy', $v->id) }}" method="POST" style="display:inline;"
                          onsubmit="return confirm('Supprimer cette visite ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="7" class="text-center text-muted py-4">Aucune visite enregistrée</td></tr>
    @endforelse
    </tbody>
</table>
</div>

<div class="mt-4">
    {{ $visites->links() }}
</div>

@endsection