@extends('rh.layout')
@section('content')

<style>
.contrat-card { background:white; border-radius:12px; padding:16px; margin-bottom:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); }
.contrat-card .badge-statut { padding:3px 12px; border-radius:10px; font-size:11px; font-weight:600; }
.kpi-box { background:white; border-radius:10px; padding:12px; text-align:center; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
.kpi-box .v { font-size:20px; font-weight:800; }
.kpi-box .l { font-size:9px; color:#64748b; font-weight:600; text-transform:uppercase; margin-top:2px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">📄 Contrats</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.contrats.create') }}" class="btn btn-primary">+ Nouveau contrat</a>
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
            <div class="l">Total contrats</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #16a34a;">
            <div class="v" style="color:#16a34a;">{{ $stats['actifs'] }}</div>
            <div class="l">Actifs</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #f59e0b;">
            <div class="v" style="color:#f59e0b;">{{ $stats['en_attente'] }}</div>
            <div class="l">En attente</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #dc2626;">
            <div class="v" style="color:#dc2626;">{{ $stats['termines'] }}</div>
            <div class="l">Terminés</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #ef4444;background:#fef2f2;">
            <div class="v" style="color:#ef4444;">{{ $stats['alerte_essai'] }}</div>
            <div class="l">⚠️ Fin période essai</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-box" style="border-top:3px solid #ef4444;background:#fef2f2;">
            <div class="v" style="color:#ef4444;">{{ $stats['alerte_fin'] }}</div>
            <div class="l">⚠️ Fin contrat (15j)</div>
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
        <label style="font-size:11px;font-weight:600;color:#64748b;">Type</label>
        <select name="type_contrat_id" class="form-control form-control-sm" style="min-width:150px;">
            <option value="">Tous</option>
            @foreach($typesContrat as $t)
                <option value="{{ $t->id }}" {{ request('type_contrat_id')==$t->id?'selected':'' }}>
                    {{ $t->nom }}
                </option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Statut</label>
        <select name="statut" class="form-control form-control-sm" style="min-width:130px;">
            <option value="">Tous</option>
            @foreach(\App\Models\RH\Contrat::STATUTS_LABELS as $k => $v)
                <option value="{{ $k }}" {{ request('statut')==$k?'selected':'' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>
    <div>
        <label style="font-size:11px;font-weight:600;color:#64748b;">Mois début</label>
        <input type="month" name="mois" class="form-control form-control-sm" style="width:150px;" value="{{ request('mois') }}">
    </div>
    <button type="submit" class="btn btn-primary btn-sm">🔍</button>
    <a href="{{ route('rh.contrats.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
</form>

{{-- LISTE --}}
@forelse($contrats as $c)
<div class="contrat-card">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div style="font-weight:700;font-size:14px;color:#1e3a5f;">
                {{ $c->employe?->nom }} {{ $c->employe?->prenom }}
                <span style="font-size:11px;color:#64748b;margin-left:8px;">{{ $c->employe?->matricule }}</span>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:2px;">
                📄 {{ $c->numero_contrat }}
                @if($c->typeContrat)
                    <span style="background:#dbeafe;color:#1d4ed8;padding:1px 8px;border-radius:6px;font-size:10px;font-weight:600;margin-left:6px;">
                        {{ $c->typeContrat->nom }}
                    </span>
                @endif
            </div>
            <div style="font-size:11px;color:#64748b;margin-top:2px;">
                📅 {{ $c->date_debut?->format('d/m/Y') }}
                @if($c->date_fin)
                    → {{ $c->date_fin?->format('d/m/Y') }}
                    <span style="font-size:10px;color:#94a3b8;">({{ $c->duree }})</span>
                @else
                    <span style="color:#16a34a;font-weight:600;">• CDI</span>
                @endif
            </div>
            @if($c->periode_essai_jours)
                <div style="font-size:11px;color:#64748b;margin-top:2px;">
                    🕐 Période d'essai : {{ $c->periode_essai_jours }} jours
                    @if($c->est_en_periode_essai)
                        <span style="color:#f59e0b;font-weight:600;">({{ $c->jours_restants_periode_essai }} jours restants)</span>
                    @endif
                </div>
            @endif
            <div style="font-size:12px;font-weight:600;color:#1d4ed8;margin-top:2px;">
                💰 {{ number_format($c->salaire_base, 0, ',', ' ') }} FCFA
            </div>
        </div>
        <div class="text-end">
            <span class="badge-statut" style="background:{{ $c->statut_color }};color:{{ $c->statut_text_color }};">
                {{ $c->statut_label }}
            </span>
            <div class="mt-2 d-flex gap-1">
                <a href="{{ route('rh.contrats.show', $c->id) }}" class="btn btn-sm btn-primary" title="Voir">👁</a>
                <a href="{{ route('rh.contrats.edit', $c->id) }}" class="btn btn-sm btn-warning" title="Modifier">✏️</a>
                <a href="{{ route('rh.contrats.pdf', $c->id) }}" class="btn btn-sm btn-danger" title="PDF">📄</a>
                @if($c->statut === 'en_attente')
                    <form action="{{ route('rh.contrats.valider', $c->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="btn btn-sm btn-success" title="Valider">✅</button>
                    </form>
                @endif
                @if($c->statut === 'valide')
                    <form action="{{ route('rh.contrats.activer', $c->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="btn btn-sm btn-success" title="Activer">▶️</button>
                    </form>
                @endif
                @if(in_array($c->statut, ['actif', 'suspendu']))
                    <button onclick="openResilierModal({{ $c->id }}, '{{ $c->numero_contrat }}', '{{ $c->employe?->nom }} {{ $c->employe?->prenom }}')"
                            class="btn btn-sm btn-outline-danger" title="Résilier">🚫</button>
                @endif
                @if($c->peutEtreRenouvele())
                    <button onclick="openRenouvelerModal({{ $c->id }}, '{{ $c->numero_contrat }}', '{{ $c->employe?->nom }} {{ $c->employe?->prenom }}')"
                            class="btn btn-sm btn-outline-primary" title="Renouveler">🔄</button>
                @endif
            </div>
        </div>
    </div>
</div>
@empty
    <div class="text-center text-muted py-5">Aucun contrat enregistré</div>
@endforelse

{{-- Pagination --}}
<div class="mt-4">
    {{ $contrats->links() }}
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
                    <p>Confirmez la résiliation du contrat <strong id="resilierContratNum"></strong> pour <strong id="resilierEmployeNom"></strong>.</p>
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
                    <p>Renouvellement du contrat <strong id="renouvelerContratNum"></strong> pour <strong id="renouvelerEmployeNom"></strong>.</p>
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Date de début <span class="text-danger">*</span></label>
                            <input type="date" name="date_debut" class="form-control" required>
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
                            <input type="number" name="salaire_base" class="form-control" min="0" required>
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
function openResilierModal(id, num, nom) {
    document.getElementById('resilierForm').action = '/rh/contrats/' + id + '/resilier';
    document.getElementById('resilierContratNum').textContent = num;
    document.getElementById('resilierEmployeNom').textContent = nom;
    new bootstrap.Modal(document.getElementById('resilierModal')).show();
}

function openRenouvelerModal(id, num, nom) {
    document.getElementById('renouvelerForm').action = '/rh/contrats/' + id + '/renouveler';
    document.getElementById('renouvelerContratNum').textContent = num;
    document.getElementById('renouvelerEmployeNom').textContent = nom;
    // Date de début par défaut = lendemain de la date de fin
    new bootstrap.Modal(document.getElementById('renouvelerModal')).show();
}
</script>
@endsection