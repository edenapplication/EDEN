@extends('admin.layout')
@section('content')

<h2>✏️ Modifier La Zone</h2>
<a href="{{ route('grand-sites.index') }}" class="btn btn-outline-secondary mb-3">← Retour</a>

<div class="card p-4">
    <form action="{{ route('grand-sites.update', $grandsite->id) }}" method="POST">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control"
                   value="{{ $grandsite->nom }}" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control">{{ $grandsite->description }}</textarea>
        </div>
        <button class="btn btn-success">Mettre à jour</button>
    </form>
</div>
@endsection