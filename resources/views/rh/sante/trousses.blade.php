@extends('rh.layout')
@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">🧰 Trousses de secours</h2>
    <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#trousseModal">
        <i class="bi bi-plus-circle"></i> Ajouter une trousse
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    @forelse($trousses as $t)
        <div class="col-md-4">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 style="color:#1e3a5f;">📍 {{ $t->localisation }}</h5>
                            <div style="font-size:12px;color:#64748b;">
                                Responsable : {{ $t->responsable ?? '-' }}
                            </div>
                            <div style="font-size:12px;color:#64748b;">
                                Vérifiée le : {{ $t->date_verification->format('d/m/Y') }}
                            </div>
                            @if($t->prochaine_verification)
                                <div style="font-size:12px;color:#64748b;">
                                    Prochaine : {{ $t->prochaine_verification->format('d/m/Y') }}
                                </div>
                            @endif
                            <div style="margin-top:6px;">
                                <span class="badge-status" style="background:{{ $t->statut_color }};color:#1e293b;font-size:10px;">
                                    {{ $t->statut_label }}
                                </span>
                            </div>
                            @if($t->contenu)
                                <div style="font-size:11px;color:#475569;margin-top:6px;">
                                    <strong>Contenu :</strong> {{ Str::limit($t->contenu, 100) }}
                                </div>
                            @endif
                        </div>
                        <div class="d-flex gap-1">
                            <button class="btn btn-sm btn-outline-warning" title="Modifier" 
                                    onclick="editTrousse({{ $t->id }}, '{{ $t->localisation }}', '{{ $t->responsable }}', '{{ $t->date_verification->format('Y-m-d') }}', '{{ $t->prochaine_verification?->format('Y-m-d') }}', '{{ addslashes($t->contenu) }}', '{{ $t->statut }}')">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <form action="{{ route('rh.sante.trousses.destroy', $t->id) }}" method="POST" style="display:inline;"
                                  onsubmit="return confirm('Supprimer cette trousse ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="text-center text-muted py-5">
                <div style="font-size:48px;margin-bottom:16px;">🧰</div>
                <p style="font-size:16px;">Aucune trousse de secours enregistrée</p>
                <p style="font-size:13px;color:#94a3b8;">Cliquez sur "Ajouter une trousse" pour commencer.</p>
            </div>
        </div>
    @endforelse
</div>

{{-- MODAL AJOUT / MODIFICATION --}}
<div class="modal fade" id="trousseModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="trousseForm" method="POST" action="{{ route('rh.sante.trousses.store') }}">
                @csrf
                <input type="hidden" name="trousse_id" id="trousse_id">
                <input type="hidden" name="_method" id="trousse_method" value="POST">
                <div class="modal-header">
                    <h5 class="modal-title" id="trousseModalTitle">🧰 Nouvelle trousse de secours</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Localisation <span class="text-danger">*</span></label>
                            <input type="text" name="localisation" id="trousse_localisation" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Responsable</label>
                            <input type="text" name="responsable" id="trousse_responsable" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Date de vérification <span class="text-danger">*</span></label>
                            <input type="date" name="date_verification" id="trousse_date_verification" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Prochaine vérification</label>
                            <input type="date" name="prochaine_verification" id="trousse_prochaine" class="form-control">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Contenu</label>
                            <textarea name="contenu" id="trousse_contenu" class="form-control" rows="3" placeholder="Liste du contenu..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Statut <span class="text-danger">*</span></label>
                            <select name="statut" id="trousse_statut" class="form-control" required>
                                <option value="ok">OK</option>
                                <option value="alerte">Alerte (réapprovisionnement)</option>
                                <option value="vide">Vide</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editTrousse(id, localisation, responsable, dateVerif, prochaine, contenu, statut) {
    document.getElementById('trousseModalTitle').textContent = '✏️ Modifier la trousse';
    document.getElementById('trousseForm').action = '/rh/sante/trousses/' + id;
    document.getElementById('trousse_method').value = 'PUT';
    document.getElementById('trousse_id').value = id;
    document.getElementById('trousse_localisation').value = localisation;
    document.getElementById('trousse_responsable').value = responsable || '';
    document.getElementById('trousse_date_verification').value = dateVerif;
    document.getElementById('trousse_prochaine').value = prochaine || '';
    document.getElementById('trousse_contenu').value = contenu || '';
    document.getElementById('trousse_statut').value = statut || 'ok';
    new bootstrap.Modal(document.getElementById('trousseModal')).show();
}

document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('trousse_date_verification').value = '{{ now()->format('Y-m-d') }}';
    document.getElementById('trousse_prochaine').value = '{{ now()->addMonths(3)->format('Y-m-d') }}';
    
    // Reset du formulaire à la fermeture
    document.getElementById('trousseModal').addEventListener('hidden.bs.modal', function() {
        document.getElementById('trousseForm').action = '{{ route('rh.sante.trousses.store') }}';
        document.getElementById('trousse_method').value = 'POST';
        document.getElementById('trousse_id').value = '';
        document.getElementById('trousseModalTitle').textContent = '🧰 Nouvelle trousse de secours';
    });
});
</script>

@endsection