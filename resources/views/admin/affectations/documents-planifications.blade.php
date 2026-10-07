@extends('admin.affectations.layout')
@section('content')

<style>
.doc-wrap { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
.doc-filters { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:16px; }
.doc-table { width:100%; border-collapse:collapse; font-size:13px; }
.doc-table thead { background:linear-gradient(135deg,#d97706,#f59e0b); color:white; }
.doc-table thead th { padding:12px 14px; text-align:left; font-weight:700; font-size:11px; text-transform:uppercase; letter-spacing:0.5px; white-space:nowrap; }
.doc-table tbody tr { border-bottom:1px solid #f1f5f9; transition:0.15s; }
.doc-table tbody tr:hover { background:#fffbeb; }
.doc-table tbody td { padding:12px 14px; vertical-align:middle; }

.btn-icon { background:none; border:none; cursor:pointer; font-size:16px; padding:4px 8px; border-radius:6px; transition:all 0.2s; text-decoration:none; display:inline-block; }
.btn-icon:hover { background:#fef3c7; transform:scale(1.1); }

/* ═══ HERO ORANGE ═══ */
.doc-hero {
    background: linear-gradient(135deg, #d97706 0%, #f59e0b 50%, #fbbf24 100%);
    border-radius: 18px;
    padding: 28px 32px;
    color: white;
    margin-bottom: 20px;
    position: relative;
    overflow: hidden;
    box-shadow: 0 8px 24px rgba(245,158,11,0.35);
}
.doc-hero::before {
    content: '';
    position: absolute;
    top: -50%; right: -10%;
    width: 300px; height: 300px;
    background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
    border-radius: 50%;
}
.doc-hero::after {
    content: '📅';
    position: absolute;
    right: 30px; top: 50%;
    transform: translateY(-50%);
    font-size: 110px;
    opacity: 0.12;
}
.doc-hero h2 { margin: 0 0 6px 0; font-weight: 900; font-size: 26px; letter-spacing: -0.5px; position: relative; z-index: 1; }
.doc-hero .sub { font-size: 13.5px; opacity: 0.95; position: relative; z-index: 1; }
.doc-hero .count-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,255,255,0.2);
    padding: 6px 14px; border-radius: 20px;
    font-size: 12px; font-weight: 800; margin-top: 10px;
    position: relative; z-index: 1;
    backdrop-filter: blur(10px);
}

/* ═══ BOUTONS ONGLETS ═══ */
.onglets-bar { display: flex; gap: 8px; margin-bottom: 20px; flex-wrap: wrap; }
.btn-onglet {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 12px 22px; border-radius: 12px;
    font-size: 13px; font-weight: 800;
    text-decoration: none;
    transition: all 0.25s;
    border: 2px solid #e2e8f0;
    background: white; color: #64748b;
}
.btn-onglet:hover { transform: translateY(-3px); box-shadow: 0 6px 16px rgba(0,0,0,0.08); }
.btn-onglet.active { color: white; border-color: transparent; box-shadow: 0 6px 18px rgba(0,0,0,0.15); }
.btn-onglet.active.attr { background: linear-gradient(135deg, #5b21b6, #7c3aed); }
.btn-onglet.active.plan { background: linear-gradient(135deg, #f59e0b, #d97706); }
.btn-onglet.active.clot { background: linear-gradient(135deg, #16a34a, #15803d); }

/* ═══ STATS ORANGE ═══ */
.doc-stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 16px; }
.doc-stat {
    background: white; border-radius: 12px; padding: 14px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    border-left: 4px solid #f59e0b;
    display: flex; align-items: center; gap: 12px;
}
.doc-stat .ico { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
.doc-stat .lbl { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 700; letter-spacing: 0.3px; }
.doc-stat .val { font-size: 18px; font-weight: 800; color: #1e3a5f; line-height: 1.2; }

.filtres-actifs {
    background: linear-gradient(135deg,#fef3c7,#fde68a);
    border-left: 4px solid #f59e0b;
    border-radius: 10px; padding: 12px 18px; margin-bottom: 16px;
    font-size: 12.5px; color: #92400e;
    display: flex; align-items: center; gap: 10px; flex-wrap: wrap;
}
.filtres-actifs .badge { background: white; color: #92400e; padding: 3px 10px; border-radius: 8px; font-size: 11px; font-weight: 800; border: 1px solid #fcd34d; }
.filtres-actifs .btn-retirer { margin-left: auto; color: #dc2626; font-weight: 800; text-decoration: none; padding: 4px 10px; border-radius: 6px; border: 1px solid #fca5a5; background: #fee2e2; font-size: 11px; transition: all 0.2s; }
.filtres-actifs .btn-retirer:hover { background: #dc2626; color: white; }
</style>

{{-- ═══ HERO ORANGE ═══ --}}
<div class="doc-hero">
    <h2>📅 Documents des planifications</h2>
    <div class="sub">
        Tous les rapports de planification d'implantation enregistrés. Chaque PDF correspond à une journée d'implantation.
    </div>
    <div class="count-badge">
        📊 {{ $documents->total() }} document(s)
    </div>
</div>

{{-- ═══ ONGLETS ═══ --}}
<div class="onglets-bar">
    <a href="{{ route('affectations.documents.attributions') }}" class="btn-onglet">
        📄 Attributions
    </a>
    <a href="{{ route('affectations.documents.planifications') }}" class="btn-onglet plan active">
        📅 Planifications
    </a>
    <a href="{{ route('affectations.documents.clotures') }}" class="btn-onglet">
        🔒 Clôtures
    </a>
</div>

{{-- ═══ FILTRES ACTIFS ═══ --}}
@if(request('jour') || request('q'))
<div class="filtres-actifs">
    <strong>🔍 Filtres actifs :</strong>
    @if(request('jour'))
        <span class="badge">📅 {{ \Carbon\Carbon::parse(request('jour'))->format('d/m/Y') }}</span>
    @endif
    @if(request('q'))
        <span class="badge">🔍 "{{ request('q') }}"</span>
    @endif
    <a href="{{ route('affectations.documents.planifications') }}" class="btn-retirer">
        ✖ Retirer
    </a>
</div>
@endif

{{-- ═══ STATS ═══ --}}
@php
    $totalLignes = $documents->sum('nb_lignes');
    $totalLots   = $documents->sum('nb_lots');
    $totalSup    = $documents->sum('superficie_totale');
@endphp

<div class="doc-stats">
    <div class="doc-stat" style="border-left-color:#f59e0b;">
        <div class="ico" style="background:#fef3c7;color:#f59e0b;">📅</div>
        <div>
            <div class="lbl">Rapports</div>
            <div class="val">{{ $documents->total() }}</div>
        </div>
    </div>
    <div class="doc-stat" style="border-left-color:#f59e0b;">
        <div class="ico" style="background:#fef3c7;color:#f59e0b;">📋</div>
        <div>
            <div class="lbl">Total lignes</div>
            <div class="val">{{ number_format($totalLignes, 0, ',', ' ') }}</div>
        </div>
    </div>
    <div class="doc-stat" style="border-left-color:#f59e0b;">
        <div class="ico" style="background:#fef3c7;color:#f59e0b;">📦</div>
        <div>
            <div class="lbl">Total lots</div>
            <div class="val">{{ number_format($totalLots, 0, ',', ' ') }}</div>
        </div>
    </div>
    <div class="doc-stat" style="border-left-color:#f59e0b;">
        <div class="ico" style="background:#fef3c7;color:#f59e0b;">📐</div>
        <div>
            <div class="lbl">Superficie</div>
            <div class="val">{{ number_format($totalSup, 0, ',', ' ') }} <small style="font-size:11px;color:#64748b;">m²</small></div>
        </div>
    </div>
</div>

{{-- ═══ FILTRES ═══ --}}
<div class="doc-filters">
    <form method="GET" action="{{ route('affectations.documents.planifications') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-4">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Date d'implantation</label>
                <input type="date" name="jour" class="form-control form-control-sm"
                       value="{{ request('jour') }}">
            </div>
            <div class="col-md-4">
                <label style="font-size:11px;font-weight:700;color:#64748b;">🔍 Fichier</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Nom du fichier..."
                       value="{{ request('q') }}">
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm flex-fill"
                        style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;font-weight:700;">
                    🔍 Filtrer
                </button>
                <a href="{{ route('affectations.documents.planifications') }}"
                   class="btn btn-outline-secondary btn-sm">✖</a>
            </div>
        </div>
    </form>
</div>

{{-- ═══ TABLEAU ═══ --}}
<div class="doc-wrap">
    @if($documents->count() > 0)
    <div style="overflow-x:auto;">
        <table class="doc-table">
            <thead>
                <tr>
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
                <tr>
                    <td><strong style="color:#d97706;">{{ $doc->date_semaine?->format('d/m/Y') ?? '—' }}</strong></td>
                    <td style="font-size:11px;color:#64748b;max-width:280px;word-break:break-all;">{{ $doc->nom_fichier }}</td>
                    <td><strong style="color:#f59e0b;">{{ $doc->nb_lignes }}</strong></td>
                    <td><strong style="color:#f59e0b;">{{ $doc->nb_lots }}</strong></td>
                    <td><span style="font-weight:700;color:#d97706;">{{ number_format($doc->superficie_totale ?? 0, 0, ',', ' ') }} m²</span></td>
                    <td><span style="font-size:12px;color:#475569;">👤 {{ $doc->user?->name ?? '—' }}</span></td>
                    <td style="font-size:11px;color:#64748b;">{{ $doc->created_at->format('d/m/Y H:i') }}</td>
                    <td style="text-align:right;white-space:nowrap;">
                        <a href="{{ $doc->url }}" target="_blank" class="btn-icon" title="Voir">📄</a>
                        <a href="{{ $doc->url }}" download class="btn-icon" title="Télécharger">⬇</a>
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
        <div style="font-weight:700;font-size:15px;color:#475569;">Aucun rapport de planification</div>
        <div style="font-size:12px;margin-top:6px;">
            Les rapports de planification apparaîtront ici après génération depuis la liste des affectations.
        </div>
        <a href="{{ route('affectations.liste') }}" class="btn btn-sm" style="margin-top:14px;background:linear-gradient(135deg,#f59e0b,#d97706);color:white;font-weight:700;">
            📋 Aller à la liste des affectations
        </a>
    </div>
    @endif
</div>

@endsection