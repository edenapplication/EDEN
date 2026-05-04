@extends('admin.layout')
@section('content')

<h2>✏️ Modifier Client</h2>
<a href="{{ route('suivi-client.show', $client->id) }}" class="btn btn-outline-secondary btn-sm mb-3">← Retour</a>

<form method="POST" action="{{ route('suivi-client.update', $client->id) }}">
    @csrf @method('PUT')

    {{-- Choisir quel dossier modifier --}}
    @if($client->dossiers->count() > 1)
    <div class="card p-3 mb-3">
        <label class="fw-bold">Dossier à modifier</label>
        <select name="dossier_id" class="form-control mt-1">
            @foreach($client->dossiers as $d)
                <option value="{{ $d->id }}">{{ $d->nom_dossier }}</option>
            @endforeach
        </select>
    </div>
    @else
        <input type="hidden" name="dossier_id" value="{{ $client->dossiers->first()?->id }}">
    @endif

    @include('admin.suivi_client._form', [
        'title'   => '',
        'action'  => '',
        'method'  => 'PUT',
        'client'  => $client,
        'dossier' => $client->dossiers->first(),
        'insideForm' => true,
    ])
</form>
@endsection