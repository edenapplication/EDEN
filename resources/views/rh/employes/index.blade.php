@extends('rh.layout')
@section('content')

<style>
.emp-table { width:100%; border-collapse:collapse; font-size:12px; background:white; }
.emp-table thead tr { background:#1e3a5f; color:white; }
.emp-table thead th { padding:10px 8px; font-weight:600; text-align:left; white-space:nowrap; }
.emp-table tbody tr:nth-child(even) { background:#f8fafc; }
.emp-table tbody tr:hover { background:#eff6ff; }
.emp-table tbody td { padding:8px; border-bottom:1px solid #e2e8f0; white-space:nowrap; }

/* Lignes archivées */
.emp-table tbody tr.archive-row { background:#fff1f2 !important; }
.emp-table tbody tr.archive-row:hover { background:#ffe4e6 !important; }
.emp-table tbody tr.archive-row td { color:#9f1239; }

.badge-contrat { font-size:10px; padding:2px 8px; border-radius:10px; font-weight:600; }
.bc-cdi  { background:#dcfce7; color:#15803d; }
.bc-cdd  { background:#fef3c7; color:#92400e; }
.bc-pre  { background:#e0e7ff; color:#3730a3; }
.bc-stage{ background:#f3f4f6; color:#374151; }
.filter-bar { background:white; border-radius:12px; padding:14px 18px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.kpi-box { background:white; border-radius:10px; padding:14px; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
.kpi-val { font-size:22px; font-weight:800; }
.kpi-lbl { font-size:10px; color:#64748b; font-weight:600; text-transform:uppercase; margin-top:2px; }
.kpi-sub { font-size:11px; color:#94a3b8; margin-top:3px; }
.filtre-badge { display:inline-block; background:#fef9c3; color:#92400e; padding:2px 8px; border-radius:6px; font-size:10px; font-weight:600; margin-left:6px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">👥 Employés</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.employes.export', array_merge(request()->all(), ['type'=>'csv'])) }}" class="btn btn-success btn-sm">📊 CSV</a>
        <a href="{{ route('rh.employes.export', array_merge(request()->all(), ['type'=>'pdf'])) }}" class="btn btn-danger btn-sm">📄 PDF</a>
        <a href="{{ route('rh.employes.create') }}" class="btn btn-primary">+ Nouvel employé</a>
    </div>
</div>

{{-- ✅ KPIs GLOBAUX --}}
<div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:8px;">
    📊 Statistiques globales
</div>
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div class="kpi-box" style="border-top:3px solid #1d4ed8;">
            <div class="kpi-val" style="color:#1d4ed8;">{{ $stats['total'] }}</div>
            <div class="kpi-lbl">Total actifs</div>
            @if($filtreActif)
                <div class="kpi-sub">Filtrés : <strong style="color:#1d4ed8;">{{ $statsFiltre['total'] }}</strong><span class="filtre-badge">filtre</span></div>
            @endif
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-box" style="border-top:3px solid #0891b2;">
            <div class="kpi-val" style="color:#0891b2;">{{ $stats['hommes'] }}</div>
            <div class="kpi-lbl">Hommes</div>
            @if($filtreActif)
                <div class="kpi-sub">Filtrés : <strong style="color:#0891b2;">{{ $statsFiltre['hommes'] }}</strong><span class="filtre-badge">filtre</span></div>
            @endif
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-box" style="border-top:3px solid #db2777;">
            <div class="kpi-val" style="color:#db2777;">{{ $stats['femmes'] }}</div>
            <div class="kpi-lbl">Femmes</div>
            @if($filtreActif)
                <div class="kpi-sub">Filtrés : <strong style="color:#db2777;">{{ $statsFiltre['femmes'] }}</strong><span class="filtre-badge">filtre</span></div>
            @endif
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-box" style="border-top:3px solid #16a34a;">
            <div class="kpi-val" style="color:#16a34a;">{{ $stats['cdi'] }}</div>
            <div class="kpi-lbl">CDI</div>
            @if($filtreActif)
                <div class="kpi-sub">Filtrés : <strong style="color:#16a34a;">{{ $statsFiltre['cdi'] }}</strong><span class="filtre-badge">filtre</span></div>
            @endif
        </div>
    </div>
</div>

{{-- ✅ KPI ARCHIVÉS --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-box" style="border-top:3px solid #dc2626;background:#fff5f5;">
            <div class="kpi-val" style="color:#dc2626;">{{ $stats['archives'] }}</div>
            <div class="kpi-lbl" style="color:#b91c1c;">Employés archivés</div>
            <div class="kpi-sub">
                <a href="{{ route('rh.employes.index', ['statut' => 'inactif']) }}"
                   style="color:#dc2626;font-size:11px;font-weight:600;">Voir les archivés →</a>
            </div>
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
                <option value="actif"   {{ !$showInactif ? 'selected':'' }}>Actifs</option>
                <option value="inactif" {{ $showInactif  ? 'selected':'' }}>Archivés</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-primary btn-sm">🔍</button>
            <a href="{{ route('rh.employes.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
    </div>
</form>

{{-- INDICATEUR FILTRE ACTIF --}}
@if($filtreActif)
    <div style="background:#fef9c3;border-radius:8px;padding:8px 14px;font-size:12px;color:#92400e;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
        🔍 <strong>Filtre actif</strong> — {{ $statsFiltre['total'] }} résultat(s) sur {{ $stats['total'] }} employés actifs
        <a href="{{ route('rh.employes.index') }}" style="margin-left:auto;color:#92400e;font-weight:600;">✖ Effacer</a>
    </div>
@endif

{{-- INDICATEUR MODE ARCHIVÉS --}}
@if($showInactif)
    <div style="background:#fee2e2;border-radius:8px;padding:8px 14px;font-size:12px;color:#b91c1c;margin-bottom:12px;display:flex;align-items:center;gap:8px;">
        🚫 <strong>Mode archivés</strong> — Affichage des employés archivés uniquement
        <a href="{{ route('rh.employes.index') }}" style="margin-left:auto;color:#b91c1c;font-weight:600;">← Retour aux actifs</a>
    </div>
@endif

{{-- TABLEAU --}}
<div style="overflow-x:auto;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
<table class="emp-table">
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Photo</th>
            <th>Nom & Prénom</th>
            <th>Sexe</th>
            <th>Direction</th>
            <th>Service</th>
            <th>Poste</th>
            <th>Contrat</th>
            <th>Catégorie</th>
            <th>Vague</th>
            <th>Salaire base</th>
            <th>Intégration</th>
            @if($showInactif)<th>Date sortie</th>@endif
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($employes as $e)
        <tr class="{{ !$e->actif ? 'archive-row' : '' }}">
            <td>
                <strong style="color:{{ $e->actif ? '#1d4ed8' : '#dc2626' }};">{{ $e->matricule }}</strong>
                @if(!$e->actif)
                    <span style="background:#fee2e2;color:#b91c1c;font-size:9px;padding:1px 5px;border-radius:4px;margin-left:3px;">archivé</span>
                @endif
            </td>
            <td>
                @if($e->photo_path)
                    <img src="{{ asset('storage/' . $e->photo_path) }}"
                         style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid {{ $e->actif ? '#1d4ed8' : '#dc2626' }};"
                         alt="Photo">
                @else
                    <div style="width:32px;height:32px;border-radius:50%;background:{{ $e->actif ? 'linear-gradient(135deg,#1d4ed8,#7c3aed)' : '#fca5a5'}};display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:900;color:white;">
                        {{ strtoupper(substr($e->prenom,0,1)) }}{{ strtoupper(substr($e->nom,0,1)) }}
                    </div>
                @endif
            </td>
            <td>
                <a href="{{ route('rh.employes.show', $e->id) }}"
                   style="font-weight:600;color:{{ $e->actif ? '#1e3a5f' : '#9f1239' }};text-decoration:none;">
                    {{ $e->nom }} {{ $e->prenom }}
                </a>
            </td>
            <td>
                <span style="background:{{ $e->sexe==='M'?'#dbeafe':'#fce7f3'}};color:{{ $e->sexe==='M'?'#1d4ed8':'#be185d'}};padding:2px 8px;border-radius:10px;font-size:10px;font-weight:600;">
                    {{ $e->sexe === 'M' ? '👨 M' : '👩 F' }}
                </span>
            </td>
            <td>{{ $e->direction?->nom ?? '-' }}</td>
            <td>{{ $e->service?->nom ?? '-' }}</td>
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
            @if($showInactif)
                <td style="color:#dc2626;font-size:11px;">{{ $e->date_sortie?->format('d/m/Y') ?? '-' }}</td>
            @endif
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('rh.employes.show', $e->id) }}" class="btn btn-sm btn-primary" style="font-size:10px;" title="Voir">👁</a>
                    <a href="{{ route('rh.employes.edit', $e->id) }}" class="btn btn-sm btn-warning" style="font-size:10px;" title="Modifier">✏️</a>
                    @if($e->actif)
                        <form action="{{ route('rh.employes.destroy', $e->id) }}" method="POST" style="display:inline"
                              onsubmit="return confirm('Archiver cet employé ?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" style="font-size:10px;" title="Archiver">🗑</button>
                        </form>
                    @else
                        {{-- Réactiver un archivé --}}
                        <form action="{{ route('rh.employes.update', $e->id) }}" method="POST" style="display:inline">
                            @csrf @method('PUT')
                            <input type="hidden" name="actif" value="1">
                            <input type="hidden" name="nom"              value="{{ $e->nom }}">
                            <input type="hidden" name="prenom"           value="{{ $e->prenom }}">
                            <input type="hidden" name="sexe"             value="{{ $e->sexe }}">
                            <input type="hidden" name="date_integration" value="{{ $e->date_integration?->format('Y-m-d') }}">
                            <input type="hidden" name="type_contrat"     value="{{ $e->type_contrat }}">
                            <input type="hidden" name="salaire_base"     value="{{ $e->salaire_base }}">
                            <button class="btn btn-sm btn-outline-success" style="font-size:10px;" title="Réactiver">♻️</button>
                        </form>
                    @endif
                </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="{{ $showInactif ? 14 : 13 }}" class="text-center text-muted py-4">
                @if($showInactif) Aucun employé archivé @else Aucun employé trouvé @endif
            </td>
        </tr>
    @endforelse
    </tbody>
</table>
</div>

{{-- ✅ Pagination propre sans bug --}}
<div class="mt-4 d-flex justify-content-between align-items-center">
    <div style="font-size:12px;color:#64748b;">
        Affichage de {{ $employes->firstItem() ?? 0 }} à {{ $employes->lastItem() ?? 0 }}
        sur {{ $employes->total() }} employé(s)
    </div>
    <nav>
        <ul class="pagination pagination-sm mb-0">
            {{-- Précédent --}}
            @if($employes->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">‹ Précédent</span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $employes->previousPageUrl() }}">‹ Précédent</a>
                </li>
            @endif

            {{-- Pages --}}
            @for($p = 1; $p <= $employes->lastPage(); $p++)
                @if($p === $employes->currentPage())
                    <li class="page-item active">
                        <span class="page-link" style="background:#1e3a5f;border-color:#1e3a5f;">{{ $p }}</span>
                    </li>
                @elseif($p === 1 || $p === $employes->lastPage() || abs($p - $employes->currentPage()) <= 2)
                    <li class="page-item">
                        <a class="page-link" href="{{ $employes->url($p) }}">{{ $p }}</a>
                    </li>
                @elseif(abs($p - $employes->currentPage()) === 3)
                    <li class="page-item disabled"><span class="page-link">…</span></li>
                @endif
            @endfor

            {{-- Suivant --}}
            @if($employes->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $employes->nextPageUrl() }}">Suivant ›</a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">Suivant ›</span>
                </li>
            @endif
        </ul>
    </nav>
</div>

@endsection