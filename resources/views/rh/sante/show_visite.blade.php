@extends('rh.layout')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('rh.sante.visites') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <span class="ms-3" style="font-size:18px;font-weight:800;color:#1e3a5f;">
            🩺 Visite médicale
        </span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.sante.visites.edit', $visite->id) }}" class="btn btn-warning btn-sm">
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
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="color:#64748b;">Employé</span>
                    <span style="font-weight:600;">
                        <a href="{{ route('rh.employes.show', $visite->employe_id) }}" style="color:#1d4ed8;text-decoration:none;">
                            {{ $visite->employe?->nom }} {{ $visite->employe?->prenom }}
                        </a>
                    </span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="color:#64748b;">Matricule</span>
                    <span style="font-weight:600;">{{ $visite->employe?->matricule }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="color:#64748b;">Type</span>
                    <span style="font-weight:600;">{{ $visite->type_label }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="color:#64748b;">Date</span>
                    <span style="font-weight:600;">{{ $visite->date_visite->format('d/m/Y') }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="color:#64748b;">Statut</span>
                    <span>
                        <span class="badge-status" style="background:{{ $visite->statut_color }};color:#1e293b;">
                            {{ $visite->statut_label }}
                        </span>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                📋 Résultats
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="color:#64748b;">Aptitude</span>
                    <span style="font-weight:600;">
                        <span class="badge-status" style="background:{{ $visite->aptitude === 'apte' ? '#dcfce7' : ($visite->aptitude === 'apte_avec_restriction' ? '#fef3c7' : '#fee2e2') }};color:{{ $visite->aptitude === 'apte' ? '#15803d' : ($visite->aptitude === 'apte_avec_restriction' ? '#92400e' : '#b91c1c') }};">
                            {{ $visite->aptitude_label }}
                        </span>
                    </span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="color:#64748b;">Prochaine visite</span>
                    <span style="font-weight:600;">{{ $visite->prochaine_visite?->format('d/m/Y') ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="color:#64748b;">Médecin</span>
                    <span style="font-weight:600;">{{ $visite->medecin_nom ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between py-1 border-bottom">
                    <span style="color:#64748b;">Établissement</span>
                    <span style="font-weight:600;">{{ $visite->etablissement ?? '-' }}</span>
                </div>
                <div class="d-flex justify-content-between py-1">
                    <span style="color:#64748b;">Certificat</span>
                    <span>
                        @if($visite->certificat_path)
                            <a href="{{ asset('storage/' . $visite->certificat_path) }}" target="_blank" class="btn btn-sm btn-primary">
                                <i class="bi bi-file-earmark-pdf"></i> Télécharger
                            </a>
                        @else
                            <span style="color:#94a3b8;">Non fourni</span>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mt-2">
    <div class="col-12">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">
                📝 Observations
            </div>
            <div class="card-body">
                @if($visite->observations)
                    <div style="white-space:pre-wrap;font-size:13px;line-height:1.8;">{{ $visite->observations }}</div>
                @else
                    <div class="text-muted">Aucune observation</div>
                @endif
                @if($visite->restrictions)
                    <div style="margin-top:12px;background:#fef9c3;padding:10px;border-radius:8px;">
                        <strong style="color:#92400e;">⚠️ Restrictions :</strong>
                        <div style="margin-top:4px;">{{ $visite->restrictions }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <form action="{{ route('rh.sante.visites.destroy', $visite->id) }}" method="POST" style="display:inline;"
          onsubmit="return confirm('Supprimer cette visite ?')">
        @csrf @method('DELETE')
        <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Supprimer</button>
    </form>
</div>

@endsection