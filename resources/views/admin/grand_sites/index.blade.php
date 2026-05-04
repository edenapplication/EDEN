@extends('admin.layout')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🏢 Zones/Sites</h2>
    <a href="{{ route('grand-sites.create') }}" class="btn btn-primary">+ Nouveau</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-bordered table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Description</th>
                    <th>Sites</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            @forelse($grandsites as $gs)
                <tr>
                    <td>{{ $gs->id }}</td>
                    <td><strong>{{ $gs->nom }}</strong></td>
                    <td>{{ $gs->description ?? '-' }}</td>
                    <td><span class="badge bg-info">{{ $gs->sites_count }}</span></td>
                    <td>
                        <a href="{{ route('sites.index', $gs->id) }}"
                           class="btn btn-sm btn-primary">Entrer</a>
                        <a href="{{ route('grand-sites.edit', $gs->id) }}"
                           class="btn btn-sm btn-warning">Modifier</a>
                        <form action="{{ route('grand-sites.destroy', $gs->id) }}"
                              method="POST" style="display:inline"
                              onsubmit="return confirm('Supprimer ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger">Supprimer</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" class="text-center text-muted">Aucune zone enregistrer</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection