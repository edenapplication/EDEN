@extends('rh.layout')
@section('content')

<style>
.statut-badge { padding:3px 10px; border-radius:8px; font-size:11px; font-weight:600; }
.workflow-step { display:flex; align-items:center; gap:8px; padding:8px 12px; border-radius:8px; }
.workflow-step.active { background:#dbeafe; font-weight:600; }
.workflow-step.done { background:#dcfce7; }
.workflow-step .num { width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700; }
.workflow-step.active .num { background:#1d4ed8;color:white; }
.workflow-step.done .num { background:#16a34a;color:white; }
.workflow-step.pending .num { background:#e2e8f0;color:#94a3b8; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('rh.cnps.declarations') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
        <span class="ms-3" style="font-size:18px;font-weight:800;color:#1e3a5f;">
            📄 {{ $declaration->reference }}
        </span>
        <span class="statut-badge ms-2" style="background:{{ $declaration->statut_color }};color:{{ $declaration->statut_text_color }};">
            {{ $declaration->statut_label }}
        </span>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.cnps.declarations.dipe', $declaration->id) }}" class="btn btn-danger btn-sm">📄 DIPE</a>
        @if($declaration->peutEtreModifiee())
            <button onclick="openStatutModal()" class="btn btn-warning btn-sm">🔄 Changer statut</button>
        @endif
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- WORKFLOW --}}
<div class="card mb-4">
    <div class="card-body">
        <div class="d-flex justify-content-between">
            @php
                $steps = [
                    'a_declarer' => 'À déclarer',
                    'declare' => 'Déclaré',
                    'facture_recue' => 'Facture reçue',
                    'paye' => 'Payé',
                    'justifie' => 'Justifié'
                ];
                $current = $declaration->statut;
                $found = false;
            @endphp
            @foreach($steps as $key => $label)
                @php
                    $status = 'pending';
                    if ($key === $current) { $status = 'active'; $found = true; }
                    elseif ($found) { $status = 'pending'; }
                    else { $status = 'done'; }
                @endphp
                <div class="workflow-step {{ $status }}" style="flex:1;">
                    <div class="num">{{ $loop->iteration }}</div>
                    <span style="font-size:12px;">{{ $label }}</span>
                    @if($status === 'done') <span style="font-size:10px;color:#16a34a;">✅</span> @endif
                    @if($status === 'active') <span style="font-size:10px;color:#1d4ed8;">◀</span> @endif
                </div>
                @if(!$loop->last)
                    <div style="flex:0.2;border-top:2px dashed #e2e8f0;height:0;margin-top:16px;"></div>
                @endif
            @endforeach
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- INFOS DÉCLARATION --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">📋 Informations</div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-1 border-bottom"><span style="color:#64748b;">Référence</span><span style="font-weight:600;">{{ $declaration->reference }}</span></div>
                <div class="d-flex justify-content-between py-1 border-bottom"><span style="color:#64748b;">Période</span><span style="font-weight:600;">{{ $declaration->mois_label }}</span></div>
                <div class="d-flex justify-content-between py-1 border-bottom"><span style="color:#64748b;">Date déclaration</span><span style="font-weight:600;">{{ $declaration->date_declaration?->format('d/m/Y') ?? '-' }}</span></div>
                <div class="d-flex justify-content-between py-1 border-bottom"><span style="color:#64748b;">Date échéance</span><span style="font-weight:600;">{{ $declaration->date_echeance?->format('d/m/Y') ?? '-' }}</span></div>
                <div class="d-flex justify-content-between py-1 border-bottom"><span style="color:#64748b;">Date paiement</span><span style="font-weight:600;">{{ $declaration->date_paiement?->format('d/m/Y') ?? '-' }}</span></div>
                <div class="d-flex justify-content-between py-1"><span style="color:#64748b;">Validé par</span><span style="font-weight:600;">{{ $declaration->validePar?->name ?? '-' }}</span></div>
                @if($declaration->observations)
                    <div class="mt-2 p-2" style="background:#f8fafc;border-radius:6px;font-size:12px;">
                        <strong style="color:#64748b;">Observations :</strong>
                        <div style="margin-top:4px;white-space:pre-wrap;">{{ $declaration->observations }}</div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- TOTAUX --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">💰 Totaux</div>
            <div class="card-body">
                <div class="d-flex justify-content-between py-1 border-bottom"><span style="color:#64748b;">Employés déclarés</span><span style="font-weight:600;font-size:16px;">{{ $declaration->lignes->count() }}</span></div>
                <div class="d-flex justify-content-between py-1 border-bottom"><span style="color:#64748b;">Salaire soumis</span><span style="font-weight:600;">{{ number_format($declaration->total_salaire_soumis, 0, ',', ' ') }} FCFA</span></div>
                <div class="d-flex justify-content-between py-1 border-bottom"><span style="color:#64748b;">Cotisation salariale (2.52%)</span><span style="font-weight:600;color:#dc2626;">{{ number_format($declaration->total_cotisation_salariale, 0, ',', ' ') }} FCFA</span></div>
                <div class="d-flex justify-content-between py-1 border-bottom"><span style="color:#64748b;">Cotisation patronale (4.20%)</span><span style="font-weight:600;color:#7c3aed;">{{ number_format($declaration->total_cotisation_patronale, 0, ',', ' ') }} FCFA</span></div>
                <div class="d-flex justify-content-between py-1" style="border-top:2px solid #1e3a5f;padding-top:8px;margin-top:4px;">
                    <span style="font-weight:700;">TOTAL CNPS</span>
                    <span style="font-size:18px;font-weight:800;color:#1d4ed8;">{{ number_format($declaration->total_cnps, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>
    </div>

    {{-- AJOUTER EMPLOYÉS --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">➕ Ajouter des employés</div>
            <div class="card-body">
                @if($declaration->peutEtreModifiee())
                    <form action="{{ route('rh.cnps.declarations.ajouter-employes', $declaration->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <select name="employes[]" class="form-control" multiple style="height:150px;" required>
                                @php
                                    $existants = $declaration->lignes->pluck('employe_id')->toArray();
                                @endphp
                                @foreach(\App\Models\RH\Employe::where('actif', true)->orderBy('nom')->get() as $e)
                                    @if(!in_array($e->id, $existants))
                                        <option value="{{ $e->id }}">
                                            {{ $e->nom }} {{ $e->prenom }} ({{ $e->matricule }})
                                            @if($e->cnpsAffiliation?->numero_cnps) - CNPS: {{ $e->cnpsAffiliation->numero_cnps }} @endif
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            <small class="text-muted">Maintenez Ctrl/Cmd pour sélectionner plusieurs employés</small>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm w-100">➕ Ajouter à la déclaration</button>
                    </form>
                @else
                    <div class="text-center text-muted py-3">
                        <p>Cette déclaration ne peut plus être modifiée.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- LISTE DES EMPLOYÉS --}}
<div class="card mt-3">
    <div class="card-header" style="background:#f8fafc;font-weight:700;color:#1e3a5f;">👥 Employés déclarés</div>
    <div class="card-body p-0">
        <div style="overflow-x:auto;">
        <table class="table table-bordered table-hover mb-0" style="font-size:12px;">
            <thead class="table-dark">
                <tr>
                    <th>Matricule</th>
                    <th>Nom & Prénom</th>
                    <th>N° CNPS</th>
                    <th>Salaire soumis</th>
                    <th>Cotisation salariale</th>
                    <th>Cotisation patronale</th>
                    <th>Total CNPS</th>
                    @if($declaration->peutEtreModifiee())
                        <th>Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody>
            @forelse($declaration->lignes as $ligne)
                <tr>
                    <td style="font-weight:600;color:#1d4ed8;">{{ $ligne->matricule }}</td>
                    <td>{{ $ligne->nom }} {{ $ligne->prenom }}</td>
                    <td>{{ $ligne->numero_cnps ?? '-' }}</td>
                    <td>{{ number_format($ligne->salaire_soumis, 0, ',', ' ') }}</td>
                    <td>{{ number_format($ligne->cotisation_salariale, 0, ',', ' ') }}</td>
                    <td>{{ number_format($ligne->cotisation_patronale, 0, ',', ' ') }}</td>
                    <td style="font-weight:700;color:#1d4ed8;">{{ number_format($ligne->total_cnps, 0, ',', ' ') }}</td>
                    @if($declaration->peutEtreModifiee())
                        <td>
                            <form action="{{ route('rh.cnps.declarations.supprimer-ligne', [$declaration->id, $ligne->id]) }}" method="POST" onsubmit="return confirm('Retirer cet employé de la déclaration ?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Retirer">✖</button>
                            </form>
                        </td>
                    @endif
                </tr>
            @empty
                <tr><td colspan="{{ $declaration->peutEtreModifiee() ? 8 : 7 }}" class="text-center text-muted py-4">Aucun employé dans cette déclaration</td></tr>
            @endforelse
            </tbody>
            @if($declaration->lignes->count() > 0)
                <tfoot style="background:#f1f5f9;font-weight:700;">
                    <tr>
                        <td colspan="3">TOTAUX</td>
                        <td>{{ number_format($declaration->total_salaire_soumis, 0, ',', ' ') }}</td>
                        <td>{{ number_format($declaration->total_cotisation_salariale, 0, ',', ' ') }}</td>
                        <td>{{ number_format($declaration->total_cotisation_patronale, 0, ',', ' ') }}</td>
                        <td>{{ number_format($declaration->total_cnps, 0, ',', ' ') }}</td>
                        @if($declaration->peutEtreModifiee())
                            <td></td>
                        @endif
                    </tr>
                </tfoot>
            @endif
        </table>
        </div>
    </div>
</div>

{{-- MODAL CHANGER STATUT --}}
<div class="modal fade" id="statutModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('rh.cnps.declarations.statut', $declaration->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">🔄 Changer le statut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nouveau statut <span class="text-danger">*</span></label>
                        <select name="statut" class="form-control" required>
                            @foreach(\App\Models\RH\CnpsDeclaration::STATUTS as $k => $v)
                                <option value="{{ $k }}" {{ $declaration->statut==$k?'selected':'' }}>
                                    {{ $v }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="datePaiementField">
                        <label class="form-label">Date de paiement</label>
                        <input type="date" name="date_paiement" class="form-control" value="{{ now()->format('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Observations</label>
                        <textarea name="observations" class="form-control" rows="2">{{ $declaration->observations }}</textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">💾 Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openStatutModal() {
    new bootstrap.Modal(document.getElementById('statutModal')).show();
}

// Afficher/cacher le champ date paiement selon le statut
document.addEventListener('DOMContentLoaded', function() {
    const statutSelect = document.querySelector('select[name="statut"]');
    const dateField = document.getElementById('datePaiementField');

    function toggleDateField() {
        if (statutSelect.value === 'paye') {
            dateField.style.display = 'block';
        } else {
            dateField.style.display = 'none';
        }
    }

    statutSelect.addEventListener('change', toggleDateField);
    toggleDateField();
});
</script>

@endsection