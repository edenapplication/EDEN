@extends('rh.layout')
@section('content')

<style>
.depart-card { background:white; border-radius:12px; padding:16px; margin-bottom:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); border-left:4px solid #1e3a5f; }
.depart-card .badge-statut { padding:3px 12px; border-radius:10px; font-size:11px; font-weight:600; }
.kpi-box { background:white; border-radius:10px; padding:12px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
.kpi-box .v { font-size:20px; font-weight:800; }
.kpi-box .l { font-size:9px; color:#64748b; font-weight:600; text-transform:uppercase; margin-top:2px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🚪 Départs</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.departs.create') }}" class="btn btn-primary">+ Nouveau départ</a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #1e3a5f;">
            <div class="v" style="color:#1e3a5f;">{{ $stats['total'] }}</div>
            <div class="l">Total départs</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #f59e0b;">
            <div class="v" style="color:#f59e0b;">{{ $stats['en_attente'] }}</div>
            <div class="l">En attente</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #1d4ed8;">
            <div class="v" style="color:#1d4ed8;">{{ $stats['valide'] }}</div>
            <div class="l">Validés</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #16a34a;">
            <div class="v" style="color:#16a34a;">{{ $stats['termine'] }}</div>
            <div class="l">Terminés</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #94a3b8;">
            <div class="v" style="color:#94a3b8;">{{ $stats['annule'] }}</div>
            <div class="l">Annulés</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" class="d-flex gap-2 mb-4 flex-wrap align-items-end"
      style="background:white;border-radius:12px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.05);">
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Employé</label>
        <select name="employe_id" class="form-control form-control-sm" style="min-width:180px;">
            <option value="">Tous</option>
            @foreach($employes as $e)
                <option value="{{ $e->id }}" {{ request('employe_id')==$e->id?'selected':'' }}>
                    {{ $e->nom }} {{ $e->prenom }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Statut</label>
        <select name="statut" class="form-control form-control-sm" style="min-width:130px;">
            <option value="">Tous</option>
            @foreach(\App\Models\RH\Depart::STATUTS as $k => $v)
                <option value="{{ $k }}" {{ request('statut')==$k?'selected':'' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Mois</label>
        <input type="month" name="mois" class="form-control form-control-sm" style="width:150px;" value="{{ request('mois') }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">🔍</button>
    <a href="{{ route('rh.departs.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
</form>

{{-- LISTE --}}
@forelse($departs as $d)
<div class="depart-card" style="border-left-color: 
    @if($d->statut === 'termine') #16a34a
    @elseif($d->statut === 'en_attente') #f59e0b
    @elseif($d->statut === 'valide') #1d4ed8
    @elseif($d->statut === 'annule') #94a3b8
    @else #1e3a5f @endif;">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div style="font-weight:700;font-size:14px;color:#1e3a5f;">
                {{ $d->employe?->nom }} {{ $d->employe?->prenom }}
                <span style="font-size:11px;color:#64748b;margin-left:8px;">{{ $d->employe?->matricule }}</span>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">
                📅 Départ : <strong>{{ $d->date_depart?->format('d/m/Y') }}</strong>
                @if($d->motifDepart)
                    — {{ $d->motifDepart->nom }}
                @elseif($d->motif_libre)
                    — {{ $d->motif_libre }}
                @endif
            </div>
            @if($d->soldeToutCompte)
                <div style="font-size:12px;color:#1d4ed8;font-weight:600;margin-top:2px;">
                    💰 Solde : {{ number_format($d->soldeToutCompte->net_a_payer, 0, ',', ' ') }} FCFA
                    @if($d->soldeToutCompte->statut === 'paye')
                        <span style="color:#16a34a;">✅ Payé</span>
                    @else
                        <span style="color:#f59e0b;">⏳ À payer</span>
                    @endif
                </div>
            @endif
        </div>
        <div class="text-end">
            <span class="badge-statut" style="background:{{ $d->statut_color }};color:#1e293b;">
                {{ $d->statut_label }}
            </span>
            <div class="mt-2 d-flex gap-1">
                <a href="{{ route('rh.departs.show', $d->id) }}" class="btn btn-sm btn-primary" title="Voir">👁</a>
                @if($d->statut === 'en_attente')
                    <a href="{{ route('rh.departs.edit', $d->id) }}" class="btn btn-sm btn-warning" title="Modifier">✏️</a>
                    <form action="{{ route('rh.departs.valider', $d->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="btn btn-sm btn-success" title="Valider">✅</button>
                    </form>
                    <form action="{{ route('rh.departs.annuler', $d->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary" title="Annuler" onclick="return confirm('Annuler ce départ ?')">🚫</button>
                    </form>
                @endif
                @if(!$d->soldeToutCompte && in_array($d->statut, ['valide', 'en_cours']))
                    <form action="{{ route('rh.departs.generer-solde', $d->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="btn btn-sm btn-outline-success" title="Générer solde">💰</button>
                    </form>
                @endif
                @if(!$d->certificatCessation && $d->statut === 'termine')
                    <form action="{{ route('rh.departs.generer-certificat', $d->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="btn btn-sm btn-outline-info" title="Générer certificat">📄</button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@empty
    <div class="text-center text-muted py-5">Aucun départ enregistré</div>
@endforelse

{{-- Pagination --}}
<div class="mt-4">
    {{ $departs->links() }}
</div>

@endsection