@extends('admin.layout')
@section('content')

<h2>➕ Nouvelle Zone</h2>
<a href="{{ route('grand-sites.index') }}" class="btn btn-outline-secondary mb-3">← Retour</a>

<div class="card p-4">
    <form action="{{ route('grand-sites.store') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label>Nom</label>
            <input type="text" name="nom" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>
        <button class="btn btn-success">Créer</button>
    </form>
</div>
@endsection