@extends('rh.layout')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">⚠️ Accidents de travail</h2>
    <a href="{{ route('rh.sante.accidents.create') }}" class="btn btn-danger btn-sm">
        <i class="bi bi-plus-circle"></i> Déclarer un accident
    </a>
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
        <div class="col-md-4">
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
            <label style="font-size:11px;font-weight:600;color:#64748b;">Statut</label>
            <select name="statut" class="form-control form-control-sm">
                <option value="">Tous</option>
                @foreach(\App\Models\RH\AccidentTravail::STATUTS as $k => $v)
                    <option value="{{ $k }}" {{ request('statut')==$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('rh.sante.accidents') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i> Reset</a>
        </div>
    </div>
</form>

{{-- TABLEAU --}}
<div style="overflow-x:auto;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);background:white;">
<table class="table table-modern">
    <thead>
        <tr>
            <th>Employé</th>
            <th>Date</th>
            <th>Lieu</th>
            <th>Nature blessures</th>
            <th>Statut</th>
            <th style="width:100px;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($accidents as $a)
        <tr>
            <td>
                <strong>{{ $a->employe?->nom }} {{ $a->employe?->prenom }}</strong>
                <br>
                <small style="color:#94a3b8;font-size:10px;">{{ $a->employe?->matricule }}</small>
            </td>
            <td>{{ $a->date_accident->format('d/m/Y') }}</td>
            <td>{{ $a->lieu }}</td>
            <td>{{ $a->nature_blessures ?? '-' }}</td>
            <td>
                <span class="badge-status" style="background:{{ $a->statut_color }};color:#1e293b;">
                    {{ $a->statut_label }}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('rh.sante.accidents.show', $a->id) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                        <i class="bi bi-eye"></i>
                    </a>
                    <a href="{{ route('rh.sante.accidents.edit', $a->id) }}" class="btn btn-sm btn-outline-warning" title="Modifier">
                        <i class="bi bi-pencil"></i>
                    </a>
                    <form action="{{ route('rh.sante.accidents.destroy', $a->id) }}" method="POST" style="display:inline;"
                          onsubmit="return confirm('Supprimer cet accident ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                            <i class="bi bi-trash"></i>
                        </button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="6" class="text-center text-muted py-4">Aucun accident enregistré</td></tr>
    @endforelse
    </tbody>
</table>
</div>

<div class="mt-4">
    {{ $accidents->links() }}
</div>

@endsection