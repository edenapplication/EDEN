@extends('admin.affectations.layout')
@section('content')

<style>
.doc-wrap { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
.doc-filters { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:16px; }
.doc-table { width:100%; border-collapse:collapse; font-size:13px; }
.doc-table thead { background:linear-gradient(135deg,#1e3a5f,#1d4ed8); color:white; }
.doc-table thead th { padding:12px 14px; text-align:left; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; white-space:nowrap; }
.doc-table tbody tr { border-bottom:1px solid #f1f5f9; transition:0.15s; }
.doc-table tbody tr:hover { background:#f8fafc; }
.doc-table tbody td { padding:12px 14px; vertical-align:middle; }

/* ═══════════════════════════════════════════════════════════════ */
/* BADGES DE TYPE DE RAPPORT                                        */
/* ═══════════════════════════════════════════════════════════════ */
.type-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 10px;
    font-weight: 800;
    white-space: nowrap;
}
.type-badge.attribution   { background:#ede9fe; color:#5b21b6; }
.type-badge.planification { background:#fef3c7; color:#92400e; }
.type-badge.cloture       { background:#dcfce7; color:#166534; }
.type-badge.autre         { background:#e2e8f0; color:#475569; }

.btn-icon { background:none; border:none; cursor:pointer; font-size:16px; padding:4px 8px; border-radius:6px; transition:all 0.2s; text-decoration:none; }
.btn-icon:hover { background:#eff6ff; transform:scale(1.1); }

/* ═══════════════════════════════════════════════════════════════ */
/* BANDEAU DES FILTRES ACTIFS                                        */
/* ═══════════════════════════════════════════════════════════════ */
.filtres-actifs {
    background: linear-gradient(135deg,#eff6ff,#dbeafe);
    border-left: 4px solid #1d4ed8;
    border-radius: 10px;
    padding: 12px 18px;
    margin-bottom: 16px;
    font-size: 12.5px;
    color: #1e40af;
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
    box-shadow: 0 2px 8px rgba(29,78,216,0.08);
}
.filtres-actifs .badge {
    background: white;
    color: #1d4ed8;
    padding: 3px 10px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
    border: 1px solid #93c5fd;
}
.filtres-actifs .btn-retirer {
    margin-left: auto;
    color: #dc2626;
    font-weight: 800;
    text-decoration: none;
    padding: 4px 10px;
    border-radius: 6px;
    border: 1px solid #fca5a5;
    background: #fee2e2;
    font-size: 11px;
    transition: all 0.2s;
}
.filtres-actifs .btn-retirer:hover {
    background: #dc2626;
    color: white;
    border-color: #dc2626;
}

/* ═══════════════════════════════════════════════════════════════ */
/* STATS                                                                 */
/* ═══════════════════════════════════════════════════════════════ */
.doc-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 12px;
    margin-bottom: 16px;
}
.doc-stat {
    background: white;
    border-radius: 12px;
    padding: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    border-left: 4px solid #1d4ed8;
    display: flex;
    align-items: center;
    gap: 12px;
}
.doc-stat .ico {
    width: 40px; height: 40px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 18px;
    flex-shrink: 0;
}
.doc-stat .lbl {
    font-size: 10px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 700;
    letter-spacing: 0.3px;
}
.doc-stat .val {
    font-size: 18px;
    font-weight: 800;
    color: #1e3a5f;
    line-height: 1.2;
}
</style>

{{-- EN-TÊTE --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">📄 Documents PDF enregistrés</h2>
        <div style="font-size:13px;color:#64748b;">
            {{ $documents->total() }} document(s) — archivage
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('affectations.liste') }}" class="btn btn-outline-secondary btn-sm">
            📋 Liste des affectations
        </a>
        <a href="{{ route('affectations.programmation') }}" class="btn btn-outline-primary btn-sm">
            📝 Étape 1
        </a>
        <a href="{{ route('affectations.programmation-active') }}" class="btn btn-outline-warning btn-sm">
            🎯 Étape 2
        </a>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- BANDEAU DES FILTRES ACTIFS                                   --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@if(request('jour') || request('type') || request('grand_site_id') || request('bloc_id'))
<div class="filtres-actifs">
    <strong>🔍 Filtres actifs :</strong>

    @if(request('jour'))
        <span class="badge">📅 {{ \Carbon\Carbon::parse(request('jour'))->format('d/m/Y') }}</span>
    @endif

    @if(request('type'))
        @php
            $typeLabel = match(request('type')) {
                'programmation_initiale'         => '📄 Attribution',
                'rapport_programmation'          => '📄 Attribution',
                'programmation_active'           => '📅 Planification',
                'rapport_programmation_date'     => '📅 Planification',
                'programmation_finale'           => '🔒 Clôture',
                default                          => '📄 ' . request('type'),
            };
        @endphp
        <span class="badge">{{ $typeLabel }}</span>
    @endif

    @if(request('grand_site_id'))
        <span class="badge">🏢 GS #{{ request('grand_site_id') }}</span>
    @endif

    @if(request('bloc_id'))
        <span class="badge">🏗️ Bloc #{{ request('bloc_id') }}</span>
    @endif

    <a href="{{ route('affectations.documents-pdf') }}" class="btn-retirer">
        ✖ Retirer les filtres
    </a>
</div>
@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- STATS GLOBALES                                               --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
@php
    $statsAttribution   = $documents->whereIn('type', ['programmation_initiale', 'rapport_programmation'])->count();
    $statsPlanification = $documents->whereIn('type', ['programmation_active', 'rapport_programmation_date'])->count();
    $statsCloture       = $documents->where('type', 'programmation_finale')->count();
@endphp

<div class="doc-stats">
    <div class="doc-stat" style="border-left-color:#1d4ed8;">
        <div class="ico" style="background:#dbeafe;color:#1d4ed8;">📄</div>
        <div>
            <div class="lbl">Total documents</div>
            <div class="val">{{ $documents->total() }}</div>
        </div>
    </div>
    <div class="doc-stat" style="border-left-color:#7c3aed;">
        <div class="ico" style="background:#ede9fe;color:#7c3aed;">📄</div>
        <div>
            <div class="lbl">Attributions</div>
            <div class="val">{{ $statsAttribution }}</div>
        </div>
    </div>
    <div class="doc-stat" style="border-left-color:#f59e0b;">
        <div class="ico" style="background:#fef3c7;color:#f59e0b;">📅</div>
        <div>
            <div class="lbl">Planifications</div>
            <div class="val">{{ $statsPlanification }}</div>
        </div>
    </div>
    <div class="doc-stat" style="border-left-color:#16a34a;">
        <div class="ico" style="background:#dcfce7;color:#16a34a;">🔒</div>
        <div>
            <div class="lbl">Clôtures</div>
            <div class="val">{{ $statsCloture }}</div>
        </div>
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- FILTRES                                                     --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="doc-filters">
    <form method="GET" action="{{ route('affectations.documents-pdf') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Date (jour)</label>
                <input type="date" name="jour" class="form-control form-control-sm"
                       value="{{ request('jour') }}">
            </div>
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📋 Type de rapport</label>
                <select name="type" class="form-control form-control-sm">
                    <option value="">Tous les types</option>
                    <optgroup label="📄 Attribution">
                        <option value="programmation_initiale"
                            {{ request('type') == 'programmation_initiale' ? 'selected' : '' }}>
                            📄 Attribution (initiale)
                        </option>
                        <option value="rapport_programmation"
                            {{ request('type') == 'rapport_programmation' ? 'selected' : '' }}>
                            📄 Attribution (avant date)
                        </option>
                    </optgroup>
                    <optgroup label="📅 Planification">
                        <option value="programmation_active"
                            {{ request('type') == 'programmation_active' ? 'selected' : '' }}>
                            📅 Planification (active)
                        </option>
                        <option value="rapport_programmation_date"
                            {{ request('type') == 'rapport_programmation_date' ? 'selected' : '' }}>
                            📅 Planification (avec date)
                        </option>
                    </optgroup>
                    <optgroup label="🔒 Clôture">
                        <option value="programmation_finale"
                            {{ request('type') == 'programmation_finale' ? 'selected' : '' }}>
                            🔒 Clôture (finale)
                        </option>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;">🔍 Fichier</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Nom du fichier..."
                       value="{{ request('q') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">🔍 Filtrer</button>
                <a href="{{ route('affectations.documents-pdf') }}" class="btn btn-outline-secondary btn-sm">
                    ✖
                </a>
            </div>
        </div>
    </form>
</div>

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- TABLEAU                                                     --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div class="doc-wrap">
    @if($documents->count() > 0)
    <div style="overflow-x:auto;">
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Type de rapport</th>
                    <th>📅 Date</th>
                    <th>Fichier</th>
                    <th>Lignes</th>
                    <th>Lots</th>
                    <th>Superficie</th>
                    <th>👤 Créé par</th>
                    <th>🕐 Créé le</th>
                    <th style="text-align:right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @foreach($documents as $doc)
                @php
                    // ✅ Mapping des types → libellés + classes CSS
                    $typeInfo = match($doc->type) {
                        'programmation_initiale', 'rapport_programmation' => [
                            'label' => '📄 Rapport des attributions',
                            'class' => 'attribution',
                        ],
                        'programmation_active', 'rapport_programmation_date' => [
                            'label' => '📅 Rapport de planification',
                            'class' => 'planification',
                        ],
                        'programmation_finale' => [
                            'label' => '🔒 Rapport de clôture',
                            'class' => 'cloture',
                        ],
                        default => [
                            'label' => '📄 ' . $doc->type,
                            'class' => 'autre',
                        ],
                    };
                @endphp
                <tr>
                    <td>
                        <span class="type-badge {{ $typeInfo['class'] }}">
                            {{ $typeInfo['label'] }}
                        </span>
                    </td>
                    <td>
                        <strong style="color:#1e3a5f;">
                            {{ $doc->date_semaine?->format('d/m/Y') ?? '—' }}
                        </strong>
                    </td>
                    <td style="font-size:11px;color:#64748b;max-width:280px;word-break:break-all;">
                        {{ $doc->nom_fichier }}
                    </td>
                    <td>
                        <strong style="color:#7c3aed;">{{ $doc->nb_lignes }}</strong>
                    </td>
                    <td>
                        <strong style="color:#7c3aed;">{{ $doc->nb_lots }}</strong>
                    </td>
                    <td>
                        <span style="font-weight:700;color:#1e3a5f;">
                            {{ number_format($doc->superficie_totale ?? 0, 0, ',', ' ') }} m²
                        </span>
                    </td>
                    <td>
                        <span style="font-size:12px;color:#475569;">
                            👤 {{ $doc->user?->name ?? '—' }}
                        </span>
                    </td>
                    <td style="font-size:11px;color:#64748b;">
                        {{ $doc->created_at->format('d/m/Y H:i') }}
                    </td>
                    <td style="text-align:right;white-space:nowrap;">
                        <a href="{{ $doc->url }}" target="_blank"
                           class="btn-icon" title="Voir le PDF">📄</a>
                        <a href="{{ $doc->url }}" download
                           class="btn-icon" title="Télécharger">⬇</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($documents->hasPages())
    <div style="padding:14px 0;border-top:1px solid #f1f5f9;margin-top:14px;">
        {{ $documents->links() }}
    </div>
    @endif

    @else
    {{-- Empty state --}}
    <div style="text-align:center;padding:60px;color:#94a3b8;">
        <div style="font-size:56px;margin-bottom:14px;">📭</div>

        @if(request('jour') || request('type'))
            <div style="font-weight:700;font-size:15px;color:#475569;">
                Aucun document pour ces filtres
            </div>
            <div style="font-size:12px;margin-top:6px;">
                Essayez de retirer certains filtres pour élargir la recherche.
            </div>
            <a href="{{ route('affectations.documents-pdf') }}"
               class="btn btn-primary btn-sm" style="margin-top:14px;">
                ✖ Retirer les filtres
            </a>
        @else
            <div style="font-weight:700;font-size:15px;color:#475569;">Aucun document PDF</div>
            <div style="font-size:12px;margin-top:6px;">
                Les PDF enregistrés apparaîtront ici.
            </div>
        @endif
    </div>
    @endif
</div>

@endsection