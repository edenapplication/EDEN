@extends('admin.layout')
@section('content')

<style>
.client-card {
    background:white; border-radius:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    padding:16px 20px; margin-bottom:10px;
    display:flex; justify-content:space-between; align-items:center;
    border-left:4px solid #0d6efd; transition:0.2s;
}
.client-card:hover { box-shadow:0 4px 18px rgba(0,0,0,0.1); transform:translateY(-1px); }
.client-card .name { font-weight:700; font-size:15px; color:#1e3a5f; }
.client-card .meta { font-size:12px; color:#64748b; margin-top:2px; }
.badge-dossier {
    background:#eff6ff; color:#1e3a5f; border:1px solid #bfdbfe;
    border-radius:6px; padding:2px 8px; font-size:11px; font-weight:600;
}
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>👤 Suivi Clients</h2>
    <a href="{{ route('suivi-client.create') }}" class="btn btn-primary">+ Nouveau client / dossier</a>
</div>

<form method="GET" class="mb-3 d-flex gap-2">
    <input type="text" name="search" class="form-control" style="max-width:300px;"
           placeholder="🔍 Nom ou téléphone..." value="{{ request('search') }}">
    <button class="btn btn-outline-primary">Rechercher</button>
    @if(request('search'))
        <a href="{{ route('suivi-client.index') }}" class="btn btn-outline-secondary">Reset</a>
    @endif
</form>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@forelse($clients as $client)
    <div class="client-card">
        <div>
            <div class="name">{{ $client->name }}</div>
            <div class="meta">
                📞 {{ $client->phone ?? '-' }}
                @if($client->dossiers->count())
                    &nbsp;|&nbsp;
                    @foreach($client->dossiers->take(3) as $d)
                        <span class="badge-dossier">{{ $d->nom_dossier }}</span>
                    @endforeach
                    @if($client->dossiers->count() > 3)
                        <small class="text-muted">+{{ $client->dossiers->count() - 3 }} autres</small>
                    @endif
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('suivi-client.show', $client->id) }}" class="btn btn-sm btn-primary">👁 Voir</a>
            <a href="{{ route('suivi-client.edit', $client->id) }}" class="btn btn-sm btn-warning">✏️ Modifier</a>
        </div>
    </div>
@empty
    <div class="alert alert-info">Aucun client trouvé.</div>
@endforelse

{{ $clients->links() }}

@endsection