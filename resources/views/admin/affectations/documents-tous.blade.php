@extends('admin.affectations.layout')
@section('content')

<style>
.doc-hero {
    background: linear-gradient(135deg, #1d4ed8 0%, #3b82f6 50%, #60a5fa 100%);
    border-radius: 18px; padding: 28px 32px; color: white;
    margin-bottom: 20px; position: relative; overflow: hidden;
    box-shadow: 0 8px 24px rgba(29,78,216,0.35);
}
.doc-hero::after {
    content: '🗂️'; position: absolute; right: 30px; top: 50%;
    transform: translateY(-50%); font-size: 110px; opacity: 0.12;
}
.doc-hero h2 { margin: 0 0 6px 0; font-weight: 900; font-size: 26px; position: relative; z-index: 1; }
.doc-hero .sub { font-size: 13.5px; opacity: 0.95; position: relative; z-index: 1; }
.doc-hero .count-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.2); padding: 6px 14px;
    border-radius: 20px; font-size: 12px; font-weight: 800;
    margin-top: 10px; position: relative; z-index: 1;
}

.doc-wrap { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
.doc-filters { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:16px; }
.doc-table { width:100%; border-collapse:collapse; font-size:13px; }
.doc-table thead { background: linear-gradient(135deg,#1e3a5f,#1d4ed8); color:white; }
.doc-table thead th { padding:12px 14px; text-align:left; font-weight:700; font-size:11px; text-transform:uppercase; }
.doc-table tbody tr { border-bottom:1px solid #f1f5f9; }
.doc-table tbody tr:hover { background:#f8fafc; }
.doc-table tbody td { padding:12px 14px; vertical-align:middle; }
.btn-icon { background:none; border:none; cursor:pointer; font-size:16px; padding:4px 8px; border-radius:6px; text-decoration:none; display:inline-block; }
.btn-icon:hover { background:#eff6ff; transform:scale(1.1); }

.type-badge { display:inline-flex; padding:4px 10px; border-radius:12px; font-size:10px; font-weight:800; }
.type-badge.initiales  { background:#ede9fe; color:#5b21b6; }
.type-badge.avant-date { background:#ede9fe; color:#7c3aed; }
.type-badge.actives    { background:#fef3c7; color:#92400e; }
.type-badge.avec-date  { background:#fef3c7; color:#d97706; }
.type-badge.finales    { background:#dcfce7; color:#166534; }

.onglets-bar { display: flex; gap: 6px; margin-bottom: 18px; flex-wrap: wrap; }
.btn-onglet {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 10px 16px; border-radius: 10px;
    font-size: 12px; font-weight: 800;
    text-decoration: none; transition: all 0.2s;
    border: 2px solid #e2e8f0; background: white; color: #64748b;
}
.btn-onglet:hover { transform: translateY(-2px); }
.btn-onglet.active { color: white; border-color: transparent; box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
.btn-onglet.a-initiales.active  { background: linear-gradient(135deg, #5b21b6, #7c3aed); }
.btn-onglet.a-avant-date.active { background: linear-gradient(135deg, #7c3aed, #a855f7); }
.btn-onglet.a-actives.active    { background: linear-gradient(135deg, #d97706, #f59e0b); }
.btn-onglet.a-avec-date.active  { background: linear-gradient(135deg, #f59e0b, #fbbf24); }
.btn-onglet.a-finales.active    { background: linear-gradient(135deg, #15803d, #16a34a); }
.btn-onglet.a-tous.active       { background: linear-gradient(135deg, #1d4ed8, #3b82f6); }
</style>

{{-- HERO --}}
<div class="doc-hero">
    <h2>🗂️ Tous les documents PDF</h2>
    <div class="sub">Vue d'ensemble de tous les rapports générés, tous types confondus.</div>
    <div class="count-badge">📊 {{ $documents->total() }} document(s)</div>
</div>

{{-- ONGLETS --}}
<div class="onglets-bar">
    <a href="{{ route('affectations.documents.initiales') }}" class="btn-onglet a-initiales">📄 Initiales</a>
    <a href="{{ route('affectations.documents.avant-date') }}" class="btn-onglet a-avant-date">📄 Avant date</a>
    <a href="{{ route('affectations.documents.actives') }}" class="btn-onglet a-actives">📅 Actives</a>
    <a href="{{ route('affectations.documents.avec-date') }}" class="btn-onglet a-avec-date">📅 Avec date</a>
    <a href="{{ route('affectations.documents.finales') }}" class="btn-onglet a-finales">🔒 Finales</a>
    <a href="{{ route('affectations.documents.tous') }}" class="btn-onglet a-tous active">🗂️ Tous</a>
</div>

{{-- FILTRES --}}
<div class="doc-filters">
    <form method="GET" action="{{ route('affectations.documents.tous') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Date</label>
                <input type="date" name="jour" class="form-control form-control-sm" value="{{ request('jour') }}">
            </div>
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📋 Type</label>
                <select name="type" class="form-control form-control-sm">
                    <option value="">Tous les types</option>
                    <optgroup label="📄 Attribution">
                        <option value="programmation_initiale" {{ request('type') == 'programmation_initiale' ? 'selected' : '' }}>📄 Initiale</option>
                        <option value="rapport_programmation" {{ request('type') == 'rapport_programmation' ? 'selected' : '' }}>📄 Avant date</option>
                    </optgroup>
                    <optgroup label="📅 Planification">
                        <option value="programmation_active" {{ request('type') == 'programmation_active' ? 'selected' : '' }}>📅 Active</option>
                        <option value="rapport_programmation_date" {{ request('type') == 'rapport_programmation_date' ? 'selected' : '' }}>📅 Avec date</option>
                    </optgroup>
                    <optgroup label="🔒 Clôture">
                        <option value="programmation_finale" {{ request('type') == 'programmation_finale' ? 'selected' : '' }}>🔒 Finale</option>
                    </optgroup>
                </select>
            </div>
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;">🔍 Fichier</label>
                <input type="text" name="q" class="form-control form-control-sm" placeholder="Nom du fichier..." value="{{ request('q') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">🔍 Filtrer</button>
                <a href="{{ route('affectations.documents.tous') }}" class="btn btn-outline-secondary btn-sm">✖</a>
            </div>
        </div>
    </form>
</div>

{{-- TABLEAU --}}
<div class="doc-wrap">
    @if($documents->count() > 0)
    <div style="overflow-x:auto;">
        <table class="doc-table">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>📅 Date</th>
                    <th>📄 Fichier</th>
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
                    $badge = match($doc->type) {
                        'programmation_initiale'      => ['📄 Initiale',   'initiales'],
                        'rapport_programmation'       => ['📄 Avant date', 'avant-date'],
                        'programmation_active'        => ['📅 Active',     'actives'],
                        'rapport_programmation_date'  => ['📅 Avec date',  'avec-date'],
                        'programmation_finale'        => ['🔒 Finale',     'finales'],
                        default                       => ['📄 ' . $doc->type, 'initiales'],
                    };
                @endphp
                <tr>
                    <td><span class="type-badge {{ $badge[1] }}">{{ $badge[0] }}</span></td>
                    <td><strong style="color:#1e3a5f;">{{ $doc->date_semaine?->format('d/m/Y') ?? '—' }}</strong></td>
                    <td style="font-size:11px;color:#64748b;max-width:240px;word-break:break-all;">{{ $doc->nom_fichier }}</td>
                    <td><strong style="color:#1d4ed8;">{{ $doc->nb_lignes }}</strong></td>
                    <td><strong style="color:#1d4ed8;">{{ $doc->nb_lots }}</strong></td>
                    <td><span style="font-weight:700;color:#1e3a5f;">{{ number_format($doc->superficie_totale ?? 0, 0, ',', ' ') }} m²</span></td>
                    <td><span style="font-size:12px;color:#475569;">👤 {{ $doc->user?->name ?? '—' }}</span></td>
                    <td style="font-size:11px;color:#64748b;">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                    <td style="text-align:right;white-space:nowrap;">
                        <a href="{{ $doc->url }}" target="_blank" class="btn-icon">📄</a>
                        <a href="{{ $doc->url }}" download class="btn-icon">⬇</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($documents->hasPages())
    <div style="padding:14px 0;border-top:1px solid #f1f5f9;margin-top:14px;">
        {{ $documents->links() }}
    </div>
    @endif

    @else
    <div style="text-align:center;padding:60px;color:#94a3b8;">
        <div style="font-size:56px;margin-bottom:14px;">📭</div>
        <div style="font-weight:700;font-size:15px;color:#475569;">Aucun document</div>
    </div>
    @endif
</div>

@endsection