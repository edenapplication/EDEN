@extends('rh.layout')
@section('content')

<style>
.info-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
.info-row span:first-child { color:#64748b; }
.info-row span:last-child { font-weight:600; color:#1e3a5f; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('rh.sante.accidents') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <span class="ms-3" style="font-size:18px;font-weight:800;color:#1e3a5f;">
            ⚠️ Accident de travail
        </span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.sante.accidents.edit', $accident->id) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil"></i> Modifier
        </a>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                👤 Informations
            </div>
            <div class="card-body">
                <div class="info-row">
                    <span>Employé</span>
                    <span>
                        <a href="{{ route('rh.employes.show', $accident->employe_id) }}" style="color:#1d4ed8;text-decoration:none;">
                            {{ $accident->employe?->nom }} {{ $accident->employe?->prenom }}
                        </a>
                    </span>
                </div>
                <div class="info-row"><span>Matricule</span><span>{{ $accident->employe?->matricule }}</span></div>
                <div class="info-row"><span>Date</span><span>{{ $accident->date_accident->format('d/m/Y') }}</span></div>
                <div class="info-row"><span>Heure</span><span>{{ $accident->heure ?? '-' }}</span></div>
                <div class="info-row"><span>Lieu</span><span>{{ $accident->lieu }}</span></div>
                <div class="info-row"><span>Statut</span>
                    <span class="badge-status" style="background:{{ $accident->statut_color }};color:#1e293b;">
                        {{ $accident->statut_label }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                📋 Détails
            </div>
            <div class="card-body">
                <div class="info-row"><span>Nature blessures</span><span>{{ $accident->nature_blessures ?? '-' }}</span></div>
                <div class="info-row"><span>Circonstances</span><span>{{ $accident->circonstances ?? '-' }}</span></div>
                <div class="info-row"><span>Date retour</span><span>{{ $accident->date_retour?->format('d/m/Y') ?? '-' }}</span></div>
                @if($accident->duree_arret)
                    <div class="info-row"><span>Durée d'arrêt</span><span>{{ $accident->duree_arret }} jours</span></div>
                @endif
                <div class="info-row"><span>Déclaré par</span><span>{{ $accident->declarePar?->name ?? '-' }}</span></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                📝 Description
            </div>
            <div class="card-body">
                <div style="white-space:pre-wrap;font-size:13px;line-height:1.8;">{{ $accident->description }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                👀 Témoins
            </div>
            <div class="card-body">
                @if($accident->temoin1_nom || $accident->temoin2_nom)
                    <div class="info-row"><span>Témoin 1</span><span>{{ $accident->temoin1_nom ?? '-' }} {{ $accident->temoin1_tel ? ' - ' . $accident->temoin1_tel : '' }}</span></div>
                    <div class="info-row"><span>Témoin 2</span><span>{{ $accident->temoin2_nom ?? '-' }} {{ $accident->temoin2_tel ? ' - ' . $accident->temoin2_tel : '' }}</span></div>
                @else
                    <div class="text-muted">Aucun témoin</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                📎 Documents
            </div>
            <div class="card-body">
                <div class="d-flex gap-2 flex-wrap">
                    @if($accident->rapport_path)
                        <a href="{{ asset('storage/' . $accident->rapport_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-file-earmark-pdf"></i> Rapport
                        </a>
                    @endif
                    @if($accident->constat_path)
                        <a href="{{ asset('storage/' . $accident->constat_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-file-earmark-image"></i> Constat
                        </a>
                    @endif
                    @if(!$accident->rapport_path && !$accident->constat_path)
                        <div class="text-muted">Aucun document</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@if($accident->suivi)
<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                📋 Suivi
            </div>
            <div class="card-body">
                <div style="white-space:pre-wrap;font-size:13px;line-height:1.8;">{{ $accident->suivi }}</div>
            </div>
        </div>
    </div>
</div>
@endif

@if($accident->prise_en_charge)
<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                💊 Prise en charge
            </div>
            <div class="card-body">
                <div style="white-space:pre-wrap;font-size:13px;line-height:1.8;">{{ $accident->prise_en_charge }}</div>
            </div>
        </div>
    </div>
</div>
@endif

<div class="mt-3">
    <form action="{{ route('rh.sante.accidents.destroy', $accident->id) }}" method="POST" style="display:inline;"
          onsubmit="return confirm('Supprimer cet accident ?')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Supprimer</button>
    </form>
</div>

@endsection