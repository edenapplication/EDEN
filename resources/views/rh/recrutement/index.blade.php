@extends('rh.layout')
@section('content')

<style>
.candidat-card {
    background:white;
    border-radius:12px;
    padding:16px;
    margin-bottom:12px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    border-left:4px solid #94a3b8;
    transition:transform 0.2s, box-shadow 0.2s;
}
.candidat-card:hover { transform:translateX(4px); box-shadow:0 4px 16px rgba(0,0,0,0.08); }

.badge-statut {
    padding:3px 12px;
    border-radius:10px;
    font-size:10px;
    font-weight:600;
    display:inline-block;
}
.kpi-box { background:white; border-radius:10px; padding:12px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
.kpi-box .v { font-size:20px; font-weight:800; }
.kpi-box .l { font-size:9px; color:#64748b; font-weight:600; text-transform:uppercase; margin-top:2px; }

.filter-bar { background:white; border-radius:12px; padding:14px 18px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🎯 Recrutement</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.recrutement.export-pdf') }}" class="btn btn-outline-danger btn-sm">
    <i class="bi bi-file-pdf"></i> PDF
</a>
        <a href="{{ route('rh.recrutement.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nouveau candidat
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #1e3a5f;">
            <div class="v" style="color:#1e3a5f;">{{ $stats['total'] }}</div>
            <div class="l">Total</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #f59e0b;">
            <div class="v" style="color:#f59e0b;">{{ $stats['recu'] }}</div>
            <div class="l">Reçues</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #1d4ed8;">
            <div class="v" style="color:#1d4ed8;">{{ $stats['preselectionne'] + $stats['entretien_rh'] }}</div>
            <div class="l">En cours</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #16a34a;">
            <div class="v" style="color:#16a34a;">{{ $stats['embauche'] }}</div>
            <div class="l">Embauchés</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #dc2626;">
            <div class="v" style="color:#dc2626;">{{ $stats['rejete'] }}</div>
            <div class="l">Rejetés</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" class="filter-bar">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="🔍 Rechercher..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="statut" class="form-control form-control-sm">
                <option value="">Tous statuts</option>
                @foreach(\App\Models\RH\Candidat::STATUTS as $k => $v)
                    <option value="{{ $k }}" {{ request('statut')==$k?'selected':'' }}>{{ $v }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="source_id" class="form-control form-control-sm">
                <option value="">Toutes sources</option>
                @foreach($sources as $s)
                    <option value="{{ $s->id }}" {{ request('source_id')==$s->id?'selected':'' }}>{{ $s->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-search"></i></button>
        </div>
        <div class="col-md-3 text-end">
            <a href="{{ route('rh.recrutement.index') }}" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i> Reset</a>
        </div>
    </div>
</form>

{{-- LISTE --}}
@forelse($candidats as $c)
<div class="candidat-card" style="border-left-color:{{ $c->statut_color }};">
    <div class="d-flex justify-content-between align-items-start">
        <div style="flex:1;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <div style="font-weight:700;font-size:15px;color:#1e3a5f;">
                    {{ $c->nom }} {{ $c->prenom }}
                </div>
                <span style="font-size:11px;color:#64748b;">
                    📧 {{ $c->email ?? 'Email non fourni' }}
                </span>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:4px;display:flex;gap:16px;flex-wrap:wrap;">
                <span>📌 {{ $c->poste_demande ?? 'Poste non spécifié' }}</span>
                <span>📅 {{ $c->date_candidature->format('d/m/Y') }}</span>
                @if($c->source)
                    <span>📊 {{ $c->source->nom }}</span>
                @endif
                @if($c->annees_experience > 0)
                    <span>💼 {{ $c->annees_experience }} ans d'exp.</span>
                @endif
            </div>
            @if($c->competences)
                <div style="font-size:11px;color:#64748b;margin-top:4px;">
                    🏆 {{ Str::limit($c->competences, 100) }}
                </div>
            @endif
        </div>
        <div class="text-end" style="flex-shrink:0;margin-left:16px;">
            <span class="badge-statut" style="background:{{ $c->statut_color }};color:{{ $c->statut_text_color }};">
                <i class="{{ $c->statut_icon }}"></i> {{ $c->statut_label }}
            </span>
            <div style="margin-top:8px;display:flex;gap:1px;">
                @php
                    $progression = $c->progression;
                @endphp
                @for($i = 0; $i < 10; $i++)
                    <div style="width:10px;height:4px;background:{{ $i * 10 < $progression ? '#1d4ed8' : '#e2e8f0' }};border-radius:1px;"></div>
                @endfor
            </div>
            <div style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $progression }}%</div>
        </div>
    </div>

    {{-- ACTIONS --}}
    <div class="d-flex gap-2 mt-3 flex-wrap">
        <a href="{{ route('rh.recrutement.show', $c->id) }}" class="btn btn-sm btn-primary">
            <i class="bi bi-eye"></i> Voir
        </a>
        <a href="{{ route('rh.recrutement.edit', $c->id) }}" class="btn btn-sm btn-outline-warning">
            <i class="bi bi-pencil"></i>
        </a>
        
        {{-- Boutons de changement de statut --}}
        @if($c->statut !== 'embauche' && $c->statut !== 'rejete')
            <div class="btn-group btn-group-sm">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-arrow-right"></i> Avancer
                </button>
                <ul class="dropdown-menu">
                    @php
                        $steps = array_keys(\App\Models\RH\Candidat::STATUTS);
                        $current = array_search($c->statut, $steps);
                    @endphp
                    @foreach($steps as $index => $step)
                        @if($index > $current && $step !== 'embauche' && $step !== 'rejete')
                            <li>
                                <form action="{{ route('rh.recrutement.statut', $c->id) }}" method="POST" style="display:inline;">
                                    @csrf
                                    <input type="hidden" name="statut" value="{{ $step }}">
                                    <button type="submit" class="dropdown-item">
                                        {{ \App\Models\RH\Candidat::STATUTS[$step] }}
                                    </button>
                                </form>
                            </li>
                        @endif
                    @endforeach
                    @if($c->statut === 'valide')
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('rh.recrutement.statut', $c->id) }}" method="POST" style="display:inline;">
                                @csrf
                                <input type="hidden" name="statut" value="embauche">
                                <button type="submit" class="dropdown-item text-success">
                                    <i class="bi bi-person-plus"></i> Embaucher
                                </button>
                            </form>
                        </li>
                    @endif
                </ul>
            </div>
            
            <form action="{{ route('rh.recrutement.statut', $c->id) }}" method="POST" style="display:inline;">
                @csrf
                <input type="hidden" name="statut" value="rejete">
                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Rejeter ce candidat ?')">
                    <i class="bi bi-x-lg"></i> Rejeter
                </button>
            </form>
        @endif

        @if($c->statut === 'embauche' && $c->employe)
            <a href="{{ route('rh.employes.show', $c->employe->id) }}" class="btn btn-sm btn-success">
                <i class="bi bi-person"></i> Voir employé
            </a>
        @endif

        @if($c->statut !== 'embauche')
            <form action="{{ route('rh.recrutement.destroy', $c->id) }}" method="POST" style="display:inline;"
                  onsubmit="return confirm('Supprimer cette candidature ?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
            </form>
        @endif
    </div>
</div>
@empty
    <div class="text-center text-muted py-5">
        <div style="font-size:48px;margin-bottom:16px;">🎯</div>
        <p style="font-size:16px;">Aucun candidat enregistré</p>
        <p style="font-size:13px;color:#94a3b8;">Cliquez sur "Nouveau candidat" pour commencer.</p>
    </div>
@endforelse

{{-- Pagination --}}
<div class="mt-4">
    {{ $candidats->links() }}
</div>

@endsection