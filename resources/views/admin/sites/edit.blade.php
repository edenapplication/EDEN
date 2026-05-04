@extends('admin.layout')
@section('content')

<h2>✏️ Modifier le site</h2>
<a href="{{ route('sites.index', $site->grand_site_id) }}"
   class="btn btn-outline-secondary mb-3">← Retour</a>

<div class="card p-4">
    <form method="POST" action="{{ route('sites.update', $site->id) }}"
          enctype="multipart/form-data">
        @csrf @method('PUT')
        <div class="mb-3">
            <label>Nom du site</label>
            <input type="text" name="name" class="form-control"
                   value="{{ $site->name }}" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control">{{ $site->description }}</textarea>
        </div>
        <div class="mb-3">
            <label>Fichier SVG (optionnel)</label>
            @if($site->svg_path)
                <div class="mb-2">
                    <small>Fichier actuel :</small>
                    <a href="{{ asset($site->svg_path) }}" target="_blank">Voir</a>
                </div>
            @endif
            <input type="file" name="svg" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">💾 Mettre à jour</button>
    </form>
</div>
@endsection