@extends('rh.layout')
@section('content')

<style>
.emp-table { width:100%; border-collapse:collapse; font-size:12px; background:white; }
.emp-table thead tr { background:#1e3a5f; color:white; }
.emp-table thead th { padding:10px 8px; font-weight:600; text-align:left; white-space:nowrap; }
.emp-table tbody tr:nth-child(even) { background:#f8fafc; }
.emp-table tbody tr:hover { background:#eff6ff; }
.emp-table tbody td { padding:8px; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
.badge-contrat { font-size:10px; padding:2px 8px; border-radius:10px; font-weight:600; }
.bc-cdi  { background:#dcfce7; color:#15803d; }
.bc-cdd  { background:#fef3c7; color:#92400e; }
.bc-pre  { background:#e0e7ff; color:#3730a3; }
.bc-stage{ background:#f3f4f6; color:#374151; }
.filter-bar { background:white; border-radius:12px; padding:14px 18px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f; font-weight:800;">👥 Employés</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.employes.export', array_merge(request()->all(), ['type'=>'csv'])) }}" class="btn btn-success btn-sm">📊 CSV</a>
        <a href="{{ route('rh.employes.export', array_merge(request()->all(), ['type'=>'pdf'])) }}" class="btn btn-danger btn-sm">📄 PDF</a>
        <a href="{{ route('rh.employes.create') }}" class="btn btn-primary">+ Nouvel employé</a>
    </div>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #1d4ed8;">
            <div style="font-size:22px;font-weight:800;color:#1d4ed8;">{{ $stats['total'] }}</div>
            <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;">Total actifs</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #0891b2;">
            <div style="font-size:22px;font-weight:800;color:#0891b2;">{{ $stats['hommes'] }}</div>
            <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;">Hommes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #db2777;">
            <div style="font-size:22px;font-weight:800;color:#db2777;">{{ $stats['femmes'] }}</div>
            <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;">Femmes</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #16a34a;">
            <div style="font-size:22px;font-weight:800;color:#16a34a;">{{ $stats['cdi'] }}</div>
            <div style="font-size:11px;color:#64748b;font-weight:600;text-transform:uppercase;">CDI</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" class="filter-bar">
    <div class="row g-2 align-items-end">
        <div class="col-md-3">
            <input type="text" name="search" class="form-control form-control-sm"
                   placeholder="🔍 Nom, prénom, matricule..." value="{{ request('search') }}">
        </div>
        <div class="col-md-2">
            <select name="direction_id" class="form-control form-control-sm">
                <option value="">Toutes directions</option>
                @foreach($directions as $d)
                    <option value="{{ $d->id }}" {{ request('direction_id')==$d->id?'selected':'' }}>{{ $d->nom }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="type_contrat" class="form-control form-control-sm">
                <option value="">Tous contrats</option>
                @foreach(['CDI','CDD','PRE-EMPLOI','STAGE','PRESTATAIRE'] as $c)
                    <option value="{{ $c }}" {{ request('type_contrat')===$c?'selected':'' }}>{{ $c }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="vague" class="form-control form-control-sm">
                <option value="">Toutes vagues</option>
                <option value="VAGUE 1" {{ request('vague')==='VAGUE 1'?'selected':'' }}>VAGUE 1</option>
                <option value="VAGUE 2" {{ request('vague')==='VAGUE 2'?'selected':'' }}>VAGUE 2</option>
            </select>
        </div>
        <div class="col-md-1">
            <select name="statut" class="form-control form-control-sm">
                <option value="actif" {{ request('statut')!=='inactif'?'selected':'' }}>Actifs</option>
                <option value="inactif" {{ request('statut')==='inactif'?'selected':'' }}>Archivés</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm">🔍</button>
            <a href="{{ route('rh.employes.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
    </div>
</form>

{{-- TABLEAU --}}
<div style="overflow-x:auto; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06);">
<table class="emp-table">
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Nom & Prénom</th>
            <th>Sexe</th>
            <th>Direction</th>
            <th>Poste</th>
            <th>Contrat</th>
            <th>Catégorie</th>
            <th>Vague</th>
            <th>Salaire base</th>
            <th>Intégration</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($employes as $e)
        <tr>
            <td><strong style="color:#1d4ed8;">{{ $e->matricule }}</strong></td>
            <td>
                <a href="{{ route('rh.employes.show', $e->id) }}" style="font-weight:600;color:#1e3a5f;text-decoration:none;">
                    {{ $e->nom }} {{ $e->prenom }}
                </a>
            </td>
            <td>
                <span style="background:{{ $e->sexe==='M'?'#dbeafe':'#fce7f3'}};color:{{ $e->sexe==='M'?'#1d4ed8':'#be185d'}};padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;">
                    {{ $e->sexe === 'M' ? '👨 M' : '👩 F' }}
                </span>
            </td>
            <td>{{ $e->direction?->nom ?? '-' }}</td>
            <td style="max-width:150px;overflow:hidden;text-overflow:ellipsis;" title="{{ $e->intitule_poste }}">
                {{ $e->intitule_poste ?? '-' }}
            </td>
            <td>
                @php $cl = ['CDI'=>'bc-cdi','CDD'=>'bc-cdd','PRE-EMPLOI'=>'bc-pre','STAGE'=>'bc-stage'][$e->type_contrat] ?? ''; @endphp
                <span class="badge-contrat {{ $cl }}">{{ $e->type_contrat }}</span>
            </td>
            <td>{{ $e->categorie ?? '-' }}</td>
            <td>{{ $e->vague_paiement ?? '-' }}</td>
            <td style="font-weight:600;">{{ number_format($e->salaire_base, 0, ',', ' ') }} FCFA</td>
            <td>{{ $e->date_integration?->format('d/m/Y') }}</td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('rh.employes.show', $e->id) }}" class="btn btn-sm btn-primary" title="Voir">👁</a>
                    <a href="{{ route('rh.employes.edit', $e->id) }}" class="btn btn-sm btn-warning" title="Modifier">✏️</a>
                    <form action="{{ route('rh.employes.destroy', $e->id) }}" method="POST" style="display:inline"
                          onsubmit="return confirm('Archiver cet employé ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Archiver">🗑</button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="11" class="text-center text-muted py-4">Aucun employé trouvé</td></tr>
    @endforelse
    </tbody>
</table>
</div>

<div class="mt-3">{{ $employes->links() }}</div>
@endsection