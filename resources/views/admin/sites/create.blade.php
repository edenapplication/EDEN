@extends('admin.layout')
@section('content')

<h2>➕ Ajouter un Site</h2>
<a href="{{ route('sites.index', $grandsite->id) }}" class="btn btn-outline-secondary mb-3">
    ← {{ $grandsite->nom }}
</a>

<div class="card p-4">
    <form method="POST" action="{{ route('sites.store', $grandsite->id) }}"
          enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label>Nom du site</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <div class="mb-3">
            <label>Fichier SVG</label>
            <input type="file" name="svg" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Enregistrer</button>
    </form>
</div>
@endsection