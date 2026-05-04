@extends('admin.layout')
@section('content')

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('grand-sites.index') }}" class="btn btn-outline-secondary btn-sm">
            ← Grand Sites
        </a>
        <h2 class="d-inline ms-2">📍 {{ $grandsite->nom }}</h2>
    </div>
    <a href="{{ route('sites.create', $grandsite->id) }}" class="btn btn-primary">
        + Ajouter un site
    </a>
</div>

<div class="card p-3">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>TF</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        @forelse($sites as $site)
            <tr>
                <td>{{ $site->id }}</td>
                <td><strong>{{ $site->name }}</strong></td>
                <td><span class="badge bg-info">{{ $site->tfs->count() }}</span></td>
                <td>
                    <a href="{{ route('sites.show', $site->id) }}"
                       class="btn btn-sm btn-primary">👁️ Voir</a>
                    <a href="{{ route('sites.edit', $site->id) }}"
                       class="btn btn-sm btn-warning">✏️ Modifier</a>
                    <form action="{{ route('sites.destroy', $site->id) }}"
                          method="POST" style="display:inline"
                          onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-danger">🗑</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="text-center text-muted">Aucun site</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection