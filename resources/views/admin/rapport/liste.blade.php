@extends('admin.layout')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>🗂️ Rapports sauvegardés</h2>
    <a href="{{ route('rapport.index') }}" class="btn btn-primary">+ Nouveau rapport</a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card p-0 overflow-hidden">
<table class="table table-hover mb-0" style="font-size:13px;">
    <thead class="table-dark">
        <tr>
            <th>Titre</th>
            <th>Description</th>
            <th>Date</th>
            <th>Filtres</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($rapports as $rapport)
        <tr>
            <td><strong>{{ $rapport->titre }}</strong></td>
            <td>{{ $rapport->description ?? '-' }}</td>
            <td>{{ $rapport->created_at->format('d/m/Y H:i') }}</td>
            <td>
                @if($rapport->filtres)
                    <small class="text-muted">
                        {{ collect($rapport->filtres)->filter()->count() }} filtre(s)
                    </small>
                @else -
                @endif
            </td>
            <td>
                @if($rapport->fichier_pdf)
                    <a href="{{ asset('storage/' . $rapport->fichier_pdf) }}" target="_blank"
                       class="btn btn-sm btn-danger">📄 PDF</a>
                @endif
                @if($rapport->filtres)
                    <a href="{{ route('rapport.index', $rapport->filtres) }}"
                       class="btn btn-sm btn-primary">🔍 Recharger</a>
                @endif
                <form action="{{ route('rapport.destroy', $rapport->id) }}" method="POST" style="display:inline"
                      onsubmit="return confirm('Supprimer ?')">
                    @csrf @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger">🗑</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted py-4">Aucun rapport sauvegardé</td></tr>
    @endforelse
    </tbody>
</table>
</div>

{{ $rapports->links() }}

@endsection