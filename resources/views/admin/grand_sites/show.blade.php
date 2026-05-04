@extends('admin.layout')

@section('content')

<h2>🏢 {{ $grandsite->nom }}</h2>

<p class="text-muted">{{ $grandsite->description }}</p>

<a href="{{ route('grand-sites.index') }}" class="btn btn-outline-secondary mb-3">
    ← Retour
</a>

<hr>

<h4>📍 Sites de ce grand site</h4>

@if($sites->isEmpty())
    <div class="alert alert-info">Aucun site trouvé.</div>
@else
<div class="row">
    @foreach($sites as $site)
    <div class="col-md-4 mb-4">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h5>{{ $site->name }}</h5>
                <p class="text-muted">{{ $site->description ?? 'Pas de description' }}</p>
                <a href="/admin/sites/{{ $site->id }}" class="btn btn-outline-primary w-100">
                    Entrer
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

@endsection