@extends('rh.layout')
@section('content')

<style>
.info-card { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.info-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px; }
.info-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
.info-row span:first-child { color:#64748b; }
.info-row span:last-child { font-weight:600; color:#1e3a5f; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('rh.recrutement.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <span class="ms-3" style="font-size:18px;font-weight:800;color:#1e3a5f;">
            {{ $candidat->nom }} {{ $candidat->prenom }}
        </span>
        <span class="badge-statut ms-2" style="background:{{ $candidat->statut_color }};color:{{ $candidat->statut_text_color }};padding:3px 12px;border-radius:10px;font-size:12px;font-weight:600;">
            <i class="{{ $candidat->statut_icon }}"></i> {{ $candidat->statut_label }}
        </span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.recrutement.edit', $candidat->id) }}" class="btn btn-warning btn-sm">
            <i class="bi bi-pencil"></i> Modifier
        </a>
    </div>
</div>

<div class="row g-3">
    {{-- INFOS CANDIDAT --}}
    <div class="col-md-4">
        <div class="info-card">
            <h5>👤 Identité</h5>
            <div class="info-row"><span>Nom & Prénom</span><span>{{ $candidat->nom }} {{ $candidat->prenom }}</span></div>
            <div class="info-row"><span>Sexe</span><span>{{ $candidat->sexe === 'M' ? 'Masculin' : ($candidat->sexe === 'F' ? 'Féminin' : '-') }}</span></div>
            <div class="info-row"><span>Date naissance</span><span>{{ $candidat->date_naissance?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="info-row"><span>Lieu naissance</span><span>{{ $candidat->lieu_naissance ?? '-' }}</span></div>
            <div class="info-row"><span>Nationalité</span><span>{{ $candidat->nationalite ?? '-' }}</span></div>
        </div>

        <div class="info-card">
            <h5>📞 Contact</h5>
            <div class="info-row"><span>Email</span><span>{{ $candidat->email ?? '-' }}</span></div>
            <div class="info-row"><span>Téléphone</span><span>{{ $candidat->telephone ?? '-' }}</span></div>
            <div class="info-row"><span>Adresse</span><span>{{ $candidat->adresse ?? '-' }}</span></div>
        </div>

        <div class="info-card">
            <h5>📊 Informations</h5>
            <div class="info-row"><span>Poste demandé</span><span>{{ $candidat->poste_demande ?? '-' }}</span></div>
            <div class="info-row"><span>Source</span><span>{{ $candidat->source?->nom ?? '-' }}</span></div>
            <div class="info-row"><span>Date candidature</span><span>{{ $candidat->date_candidature->format('d/m/Y') }}</span></div>
            <div class="info-row"><span>Disponibilité</span><span>{{ $candidat->disponibilite?->format('d/m/Y') ?? '-' }}</span></div>
            <div class="info-row"><span>Salaire souhaité</span><span>{{ $candidat->salaire_souhaite ? number_format($candidat->salaire_souhaite, 0, ',', ' ') . ' FCFA' : '-' }}</span></div>
        </div>
    </div>

    {{-- PROFESSIONNEL --}}
    <div class="col-md-8">
        <div class="info-card">
            <h5>💼 Expérience professionnelle</h5>
            <div class="info-row"><span>Années d'expérience</span><span>{{ $candidat->annees_experience }} ans</span></div>
            <div class="info-row"><span>Dernier poste</span><span>{{ $candidat->dernier_poste ?? '-' }}</span></div>
            <div class="info-row"><span>Dernier employeur</span><span>{{ $candidat->dernier_employeur ?? '-' }}</span></div>
            <div class="info-row"><span>Niveau académique</span><span>{{ $candidat->niveau_academique ?? '-' }}</span></div>
            <div class="info-row"><span>Spécialité</span><span>{{ $candidat->specialite ?? '-' }}</span></div>
            <div class="mt-2 p-3" style="background:#f8fafc;border-radius:8px;">
                <strong style="color:#64748b;">🏆 Compétences</strong>
                <div style="margin-top:4px;white-space:pre-wrap;">{{ $candidat->competences ?? 'Aucune compétence renseignée' }}</div>
            </div>
            @if($candidat->notes)
                <div class="mt-2 p-3" style="background:#fef9c3;border-radius:8px;">
                    <strong style="color:#92400e;">📝 Notes</strong>
                    <div style="margin-top:4px;white-space:pre-wrap;">{{ $candidat->notes }}</div>
                </div>
            @endif
        </div>

        {{-- DOCUMENTS --}}
        <div class="info-card">
            <h5>📎 Documents</h5>
            <div class="d-flex gap-2">
                @if($candidat->cv_path)
                    <a href="{{ route('rh.recrutement.download', [$candidat->id, 'cv']) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-pdf"></i> CV
                    </a>
                @else
                    <span class="text-muted">CV non fourni</span>
                @endif
                @if($candidat->lettre_motivation_path)
                    <a href="{{ route('rh.recrutement.download', [$candidat->id, 'lettre']) }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-file-pdf"></i> Lettre de motivation
                    </a>
                @else
                    <span class="text-muted">Lettre non fournie</span>
                @endif
            </div>
        </div>

        {{-- ENTRETIENS --}}
        <div class="info-card">
            <h5>📋 Entretiens</h5>
            <button class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#entretienModal">
                <i class="bi bi-plus-circle"></i> Ajouter un entretien
            </button>
            @forelse($candidat->entretiens as $e)
                <div style="background:#f8fafc;border-radius:8px;padding:12px;margin-bottom:8px;border-left:3px solid {{ $e->decision_color }};">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>{{ $e->type_label }}</strong>
                            <span style="font-size:11px;color:#64748b;margin-left:8px;">{{ $e->date_entretien->format('d/m/Y') }}</span>
                        </div>
                        <span style="font-size:11px;color:{{ $e->decision === 'positif' ? '#16a34a' : ($e->decision === 'negatif' ? '#dc2626' : '#f59e0b') }}">
                            {{ $e->decision_label }}
                        </span>
                    </div>
                    <div style="font-size:12px;color:#64748b;margin-top:4px;">
                        <span>👤 {{ $e->evaluateur ?? '-' }}</span>
                        @if($e->note)
                            <span class="ms-3">⭐ {{ $e->note }}/5</span>
                        @endif
                    </div>
                    @if($e->remarques)
                        <div style="font-size:11px;color:#475569;margin-top:4px;">{{ $e->remarques }}</div>
                    @endif
                </div>
            @empty
                <div class="text-muted text-center py-2">Aucun entretien enregistré</div>
            @endforelse
        </div>

        {{-- TESTS --}}
        <div class="info-card">
            <h5>📝 Tests</h5>
            <button class="btn btn-primary btn-sm mb-3" data-bs-toggle="modal" data-bs-target="#testModal">
                <i class="bi bi-plus-circle"></i> Ajouter un test
            </button>
            @forelse($candidat->tests as $t)
                <div style="background:#f8fafc;border-radius:8px;padding:12px;margin-bottom:8px;">
                    <div class="d-flex justify-content-between">
                        <div>
                            <strong>{{ $t->type_test }}</strong>
                            <span style="font-size:11px;color:#64748b;margin-left:8px;">{{ $t->date_test->format('d/m/Y') }}</span>
                        </div>
                        <span style="font-size:11px;font-weight:600;color:{{ $t->est_reussi ? '#16a34a' : '#dc2626' }}">
                            {{ $t->note_label }}
                        </span>
                    </div>
                    @if($t->resultats)
                        <div style="font-size:11px;color:#475569;margin-top:4px;">{{ $t->resultats }}</div>
                    @endif
                    @if($t->fichier_path)
                        <div class="mt-1">
                            <a href="{{ route('rh.recrutement.download', [$candidat->id, 'test']) }}?fichier_id={{ $t->id }}" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-file-earmark"></i> Télécharger
                            </a>
                        </div>
                    @endif
                </div>
            @empty
                <div class="text-muted text-center py-2">Aucun test enregistré</div>
            @endforelse
        </div>
    </div>
</div>

{{-- MODAL ENTRETIEN --}}
<div class="modal fade" id="entretienModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('rh.recrutement.entretien.store', $candidat->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">📋 Ajouter un entretien</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Type d'entretien <span class="text-danger">*</span></label>
                            <select name="type" class="form-control" required>
                                <option value="rh">Entretien RH</option>
                                <option value="hierarchique">Entretien hiérarchique</option>
                                <option value="technique">Entretien technique</option>
                                <option value="final">Entretien final</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date_entretien" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Heure</label>
                            <input type="time" name="heure" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Lieu</label>
                            <input type="text" name="lieu" class="form-control" placeholder="Ex: Salle de réunion">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Évaluateur</label>
                            <input type="text" name="evaluateur" class="form-control" placeholder="Nom de l'évaluateur">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Poste de l'évaluateur</label>
                            <input type="text" name="evaluateur_poste" class="form-control" placeholder="Ex: Directeur RH">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Note (1-5)</label>
                            <input type="number" name="note" class="form-control" min="1" max="5">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Décision</label>
                            <select name="decision" class="form-control">
                                <option value="en_attente">En attente</option>
                                <option value="positif">Positif</option>
                                <option value="negatif">Négatif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Points forts</label>
                            <textarea name="points_forts" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Points faibles</label>
                            <textarea name="points_faibles" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Remarques</label>
                            <textarea name="remarques" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL TEST --}}
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form action="{{ route('rh.recrutement.test.store', $candidat->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">📝 Ajouter un test</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Type de test <span class="text-danger">*</span></label>
                            <input type="text" name="type_test" class="form-control" placeholder="Ex: Test technique" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date <span class="text-danger">*</span></label>
                            <input type="date" name="date_test" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Note (/20)</label>
                            <input type="number" name="note" class="form-control" min="0" max="20" step="0.5">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Fichier</label>
                            <input type="file" name="fichier" class="form-control" accept=".pdf,.doc,.docx,.xlsx">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Résultats</label>
                            <textarea name="resultats" class="form-control" rows="2" placeholder="Détails des résultats..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Appréciation</label>
                            <textarea name="appreciation" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">💾 Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection