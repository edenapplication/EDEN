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

        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('suivi-client.show', $client->id) }}" class="btn btn-sm btn-primary">👁 Voir</a>
            <a href="{{ route('suivi-client.edit', $client->id) }}" class="btn btn-sm btn-warning">✏️ Modifier</a>

            <form action="{{ route('suivi-client.destroy', $client->id) }}" method="POST"
                  onsubmit="return confirm('Supprimer ce client et tous ses dossiers ?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-sm btn-danger">🗑 Supprimer</button>
            </form>
        </div>
    </div>
@empty
    <div class="alert alert-info">Aucun client trouvé.</div>
@endforelse

{{-- PAGINATION --}}
<div class="d-flex justify-content-center mt-3">
    {{ $clients->onEachSide(1)->links('pagination::bootstrap-5') }}
</div>