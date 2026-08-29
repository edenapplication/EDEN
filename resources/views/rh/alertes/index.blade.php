@extends('rh.layout')
@section('content')

<style>
.alerte-card {
    background:white; border-radius:12px; padding:16px;
    margin-bottom:10px; box-shadow:0 2px 10px rgba(0,0,0,0.06);
    border-left:4px solid #94a3b8;
    transition:transform 0.2s;
}
.alerte-card:hover { transform:translateX(4px); }
.alerte-card .badge-priorite { padding:2px 10px; border-radius:10px; font-size:10px; font-weight:600; }
.alerte-card.non_lu { background:#f8fafc; border-left-color:#3b82f6; }
.alerte-card.non_lu .alerte-titre { font-weight:700; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">
        🔔 Centre de notifications
        @if($stats['non_lu'] > 0)
            <span style="background:#dc2626;color:white;font-size:12px;padding:2px 12px;border-radius:10px;margin-left:8px;">
                {{ $stats['non_lu'] }} non lues
            </span>
        @endif
    </h2>
    <div class="d-flex gap-2">
        @if($stats['non_lu'] > 0)
            <form action="{{ route('rh.alertes.tout-marquer-lu') }}" method="POST">
                @csrf
                <button class="btn btn-outline-primary btn-sm">✅ Tout marquer comme lu</button>
            </form>
        @endif
        <a href="{{ route('rh.dashboard') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
    </div>
</div>

{{-- STATS --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #3b82f6;">
            <div class="value" style="color:#3b82f6;font-size:24px;">{{ $stats['non_lu'] }}</div>
            <div class="label">Non lues</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #94a3b8;">
            <div class="value" style="color:#94a3b8;font-size:24px;">{{ $stats['total'] }}</div>
            <div class="label">Alertes actives</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #dc2626;">
            <div class="value" style="color:#dc2626;font-size:24px;">{{ $stats['critique'] }}</div>
            <div class="label">Critiques</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #f59e0b;">
            <div class="value" style="color:#f59e0b;font-size:24px;">{{ $alertes->total() }}</div>
            <div class="label">Total</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" class="filter-bar">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Type</label>
            <select name="type" class="form-control form-control-sm">
                <option value="">Tous</option>
                @foreach(\App\Models\RH\Alerte::TYPES as $k => $v)
                    <option value="{{ $k }}" {{ request('type')==$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Statut</label>
            <select name="statut" class="form-control form-control-sm">
                <option value="">Tous</option>
                @foreach(\App\Models\RH\Alerte::STATUTS as $k => $v)
                    <option value="{{ $k }}" {{ request('statut')==$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Priorité</label>
            <select name="priorite" class="form-control form-control-sm">
                <option value="">Toutes</option>
                @foreach(\App\Models\RH\Alerte::PRIORITES as $k => $v)
                    <option value="{{ $k }}" {{ request('priorite')==$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm">🔍 Filtrer</button>
        </div>
        <div class="col-md-3 text-end">
            <a href="{{ route('rh.alertes.index') }}" class="btn btn-outline-secondary btn-sm">✖ Reset</a>
        </div>
    </div>
</form>

{{-- LISTE DES ALERTES --}}
@forelse($alertes as $alerte)
<div class="alerte-card {{ $alerte->statut }}" style="border-left-color:{{ $alerte->priorite_color }};">
    <div class="d-flex justify-content-between align-items-start">
        <div style="flex:1;">
            <div class="alerte-titre" style="font-size:14px;color:#1e3a5f;">
                {{ $alerte->titre }}
                <span class="badge-priorite" style="background:{{ $alerte->priorite_color }}22;color:{{ $alerte->priorite_color }};">
                    {{ $alerte->priorite_label }}
                </span>
                <span style="font-size:11px;color:#94a3b8;font-weight:400;margin-left:6px;">
                    {{ $alerte->type_label }}
                </span>
            </div>
            <div style="font-size:13px;color:#475569;margin-top:4px;">
                {{ $alerte->message }}
            </div>
            <div style="font-size:11px;color:#94a3b8;margin-top:6px;">
                @if($alerte->employe)
                    👤 {{ $alerte->employe->nom }} {{ $alerte->employe->prenom }}
                @endif
                📅 {{ $alerte->created_at->format('d/m/Y à H:i') }}
                @if($alerte->statut === 'lu' && $alerte->date_lecture)
                    · Lu le {{ $alerte->date_lecture->format('d/m/Y H:i') }}
                @endif
                @if($alerte->statut === 'traite' && $alerte->date_traitement)
                    · Traité le {{ $alerte->date_traitement->format('d/m/Y H:i') }}
                @endif
            </div>
        </div>
        <div class="d-flex gap-1 ms-3 flex-shrink-0">
            @if($alerte->lien)
                <a href="{{ $alerte->lien }}" class="btn btn-sm btn-outline-primary" title="Voir le détail">
                    <i class="bi bi-eye"></i>
                </a>
            @endif
            @if($alerte->statut === 'non_lu')
                <form action="{{ route('rh.alertes.marquer-lu', $alerte->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button class="btn btn-sm btn-outline-success" title="Marquer comme lu">
                        <i class="bi bi-check-lg"></i>
                    </button>
                </form>
            @endif
            @if(in_array($alerte->statut, ['non_lu', 'lu']))
                <form action="{{ route('rh.alertes.marquer-traite', $alerte->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button class="btn btn-sm btn-outline-info" title="Marquer comme traité">
                        <i class="bi bi-check2-all"></i>
                    </button>
                </form>
            @endif
            @if($alerte->statut === 'non_lu')
                <form action="{{ route('rh.alertes.marquer-ignore', $alerte->id) }}" method="POST" style="display:inline;">
                    @csrf
                    <button class="btn btn-sm btn-outline-secondary" title="Ignorer" onclick="return confirm('Ignorer cette alerte ?')">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </form>
            @endif
            <form action="{{ route('rh.alertes.destroy', $alerte->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer cette alerte ?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        </div>
    </div>
</div>
@empty
    <div class="text-center text-muted py-5">
        <div style="font-size:48px;margin-bottom:16px;">🔔</div>
        <p style="font-size:16px;">Aucune alerte pour le moment</p>
        <p style="font-size:13px;">Toutes les notifications seront affichées ici.</p>
    </div>
@endforelse

{{-- PAGINATION --}}
<div class="mt-4">
    {{ $alertes->links() }}
</div>

@endsection