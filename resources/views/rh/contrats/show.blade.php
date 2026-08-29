@extends('rh.layout')
@section('content')

<style>
.info-card { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.info-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px; }
.info-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
.info-row span:first-child { color:#64748b; }
.info-row span:last-child { font-weight:600; color:#1e3a5f; }
.badge-statut { padding:4px 14px; border-radius:10px; font-size:12px; font-weight:600; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('rh.contrats.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
        <span class="ms-3" style="font-size:18px;font-weight:800;color:#1e3a5f;">
            📄 Contrat {{ $contrat->numero_contrat }}
        </span>
        <span class="badge-statut" style="background:{{ $contrat->statut_color }};color:{{ $contrat->statut_text_color }};margin-left:10px;">
            {{ $contrat->statut_label }}
        </span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.contrats.pdf', $contrat->id) }}" class="btn btn-danger btn-sm">📄 PDF</a>
        <a href="{{ route('rh.contrats.edit', $contrat->id) }}" class="btn btn-warning btn-sm">✏️ Modifier</a>
        @if($contrat->statut === 'en_attente')
            <form action="{{ route('rh.contrats.valider', $contrat->id) }}" method="POST" style="display:inline;">
                @csrf
                <button class="btn btn-success btn-sm">✅ Valider</button>
            </form>
        @endif
        @if($contrat->statut === 'valide')
            <form action="{{ route('rh.contrats.activer', $contrat->id) }}" method="POST" style="display:inline;">
                @csrf
                <button class="btn btn-success btn-sm">▶️ Activer</button>
            </form>
        @endif
    </div>
</div>

<div class="row g-3">
    {{-- INFOS CONTRAT --}}
    <div class="col-md-7">
        <div class="info-card">
            <h5>📋 Informations générales</h5>
            <div class="info-row"><span>Numéro de contrat</span><span style="color:#1d4ed8;font-weight:700;">{{ $contrat->numero_contrat }}</span></div>
            <div class="info-row"><span>Type de contrat</span><span>{{ $contrat->typeContrat?->nom }}</span></div>
            <div class="info-row"><span>Date de début</span><span>{{ $contrat->date_debut?->format('d/m/Y') }}</span></div>
            <div class="info-row"><span>Date de fin</span><span>{{ $contrat->date_fin?->format('d/m/Y') ?? 'CDI (Indéterminé)' }}</span></div>
            <div class="info-row"><span>Durée</span><span>{{ $contrat->duree }}</span></div>
            <div class="info-row"><span>Période d'essai</span><span>
                @if($contrat->periode_essai_jours)
                    {{ $contrat->periode_essai_jours }} jours
                    @if($contrat->est_en_periode_essai)
                        <span style="color:#f59e0b;font-weight:700;">({{ $contrat->jours_restants_periode_essai }} jours restants)</span>
                    @else
                        <span style="color:#16a34a;font-weight:700;">(Terminée)</span>
                    @endif
                @else
                    Aucune
                @endif
            </span></div>
            <div class="info-row"><span>Renouvelable</span><span>{{ $contrat->est_renouvelable ? '✅ Oui' : '❌ Non' }}</span></div>
            @if($contrat->nb_renouvellements > 0)
                <div class="info-row"><span>Nombre de renouvellements</span><span>{{ $contrat->nb_renouvellements }}</span></div>
            @endif
            <div class="info-row"><span>Date de signature</span><span>{{ $contrat->date_signature?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="info-row"><span>Date de validation</span><span>{{ $contrat->date_validation?->format('d/m/Y') ?? '-' }}</span></div>
            @if($contrat->validePar)
                <div class="info-row"><span>Validé par</span><span>{{ $contrat->validePar?->name }}</span></div>
            @endif
        </div>

        {{-- POSTE & AFFECTATION --}}
        <div class="info-card">
            <h5>🏢 Poste & Affectation</h5>
            <div class="info-row"><span>Direction</span><span>{{ $contrat->direction?->nom ?? '-' }}</span></div>
            <div class="info-row"><span>Service</span><span>{{ $contrat->service?->nom ?? '-' }}</span></div>
            <div class="info-row"><span>Poste</span><span>{{ $contrat->poste?->intitule ?? '-' }}</span></div>
            <div class="info-row"><span>Agence / Site</span><span>{{ $contrat->agenceSite?->nom ?? '-' }}</span></div>
            <div class="info-row"><span>Responsable hiérarchique</span><span>{{ $contrat->responsable_hierarchique ?? '-' }}</span></div>
            <div class="info-row"><span>Lieu de travail</span><span>{{ $contrat->lieu_travail ?? '-' }}</span></div>
            <div class="info-row"><span>Horaires</span><span>{{ $contrat->horaires ?? '-' }}</span></div>
        </div>
    </div>

    {{-- SALAIRE & EMPLOYÉ --}}
    <div class="col-md-5">
        <div class="info-card">
            <h5>💰 Rémunération</h5>
            <div class="info-row" style="border-bottom:2px solid #e2e8f0;padding-bottom:10px;">
                <span style="font-weight:700;">Salaire de base</span>
                <span style="font-size:18px;font-weight:800;color:#1d4ed8;">{{ number_format($contrat->salaire_base, 0, ',', ' ') }} FCFA</span>
            </div>
            <div class="info-row"><span>Mode de paiement</span><span>{{ $contrat->mode_paiement ?? '-' }}</span></div>
            <div class="info-row"><span>Devise</span><span>{{ $contrat->devise ?? 'FCFA' }}</span></div>
        </div>

        <div class="info-card">
            <h5>👤 Employé</h5>
            <div class="info-row"><span>Matricule</span><span style="color:#1d4ed8;font-weight:700;">{{ $contrat->employe?->matricule }}</span></div>
            <div class="info-row"><span>Nom & Prénom</span><span>{{ $contrat->employe?->nom }} {{ $contrat->employe?->prenom }}</span></div>
            <div class="info-row"><span>Email</span><span>{{ $contrat->employe?->email ?? '-' }}</span></div>
            <div class="info-row"><span>Téléphone</span><span>{{ $contrat->employe?->telephone ?? '-' }}</span></div>
            <div class="info-row"><span>Statut</span><span>{{ $contrat->employe?->actif ? '✅ Actif' : '🚫 Archivé' }}</span></div>
            <div class="mt-2">
                <a href="{{ route('rh.employes.show', $contrat->employe_id) }}" class="btn btn-outline-primary btn-sm">Voir la fiche employé</a>
            </div>
        </div>
    </div>
</div>

{{-- CONDITIONS PARTICULIÈRES --}}
@if($contrat->conditions_particulieres)
<div class="info-card mt-3">
    <h5>📝 Conditions particulières</h5>
    <div style="white-space:pre-wrap;font-size:13px;line-height:1.8;color:#374151;">{{ $contrat->conditions_particulieres }}</div>
</div>
@endif

{{-- NOTES --}}
@if($contrat->notes)
<div class="info-card mt-3">
    <h5>📝 Notes</h5>
    <div style="white-space:pre-wrap;font-size:13px;line-height:1.8;color:#374151;">{{ $contrat->notes }}</div>
</div>
@endif

{{-- HISTORIQUE DES CONTRATS --}}
@if($historique->count() > 0)
<div class="info-card mt-3">
    <h5>📜 Historique des contrats</h5>
    <table class="table table-bordered table-sm" style="font-size:12px;">
        <thead>
            <tr style="background:#f1f5f9;">
                <th>Numéro</th>
                <th>Type</th>
                <th>Début</th>
                <th>Fin</th>
                <th>Salaire</th>
                <th>Statut</th>
            </tr>
        </thead>
        <tbody>
            @foreach($historique as $h)
            <tr>
                <td><a href="{{ route('rh.contrats.show', $h->id) }}" style="color:#1d4ed8;font-weight:600;">{{ $h->numero_contrat }}</a></td>
                <td>{{ $h->typeContrat?->nom }}</td>
                <td>{{ $h->date_debut?->format('d/m/Y') }}</td>
                <td>{{ $h->date_fin?->format('d/m/Y') ?? 'CDI' }}</td>
                <td>{{ number_format($h->salaire_base, 0, ',', ' ') }}</td>
                <td><span class="badge" style="background:{{ $h->statut_color }};color:{{ $h->statut_text_color }};">{{ $h->statut_label }}</span></td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

{{-- ACTIONS DANGEREUSES --}}
<div class="mt-3 d-flex gap-2">
    @if($contrat->peutEtreRenouvele())
        <button onclick="openRenouvelerModal({{ $contrat->id }})" class="btn btn-outline-primary">🔄 Renouveler</button>
    @endif
    @if(in_array($contrat->statut, ['actif', 'suspendu']))
        <button onclick="openResilierModal({{ $contrat->id }})" class="btn btn-outline-danger">🚫 Résilier</button>
    @endif
    @if(!in_array($contrat->statut, ['actif', 'termine', 'resilie']))
        <form action="{{ route('rh.contrats.destroy', $contrat->id) }}" method="POST" onsubmit="return confirm('Supprimer ce contrat ?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger">🗑 Supprimer</button>
        </form>
    @endif
</div>

{{-- MODAL RÉSILIATION --}}
<div class="modal fade" id="resilierModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="resilierForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">🚫 Résilier le contrat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Confirmez la résiliation du contrat <strong>{{ $contrat->numero_contrat }}</strong>.</p>
                    <div class="mb-3">
                        <label class="form-label">Date de résiliation <span class="text-danger">*</span></label>
                        <input type="date" name="date_resiliation" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Motif de résiliation</label>
                        <input type="text" name="motif_resiliation" class="form-control" placeholder="Ex: Fin de mission, Départ volontaire...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Confirmer la résiliation</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL RENOUVELLEMENT --}}
<div class="modal fade" id="renouvelerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="renouvelerForm" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">🔄 Renouveler le contrat</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Renouvellement du contrat <strong>{{ $contrat->numero_contrat }}</strong>.</p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Date de début <span class="text-danger">*</span></label>
                            <input type="date" name="date_debut" class="form-control" value="{{ now()->addDay()->format('Y-m-d') }}" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Date de fin</label>
                            <input type="date" name="date_fin" class="form-control">
                            <small class="text-muted">Laisser vide pour un CDI</small>
                        </div>
                        <div class="col-6">
                            <label class="form-label">Période d'essai (jours)</label>
                            <input type="number" name="periode_essai_jours" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Salaire base <span class="text-danger">*</span></label>
                            <input type="number" name="salaire_base" class="form-control" value="{{ $contrat->salaire_base }}" min="0" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Confirmer le renouvellement</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openResilierModal(id) {
    document.getElementById('resilierForm').action = '/rh/contrats/' + id + '/resilier';
    new bootstrap.Modal(document.getElementById('resilierModal')).show();
}

function openRenouvelerModal(id) {
    document.getElementById('renouvelerForm').action = '/rh/contrats/' + id + '/renouveler';
    new bootstrap.Modal(document.getElementById('renouvelerModal')).show();
}
</script>
@endsection