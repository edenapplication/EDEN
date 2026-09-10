@extends('admin.layout')
@section('content')

<style>
    .rapport-header {
        background: linear-gradient(135deg, #4D96FF 0%, #a855f7 50%, #ef4444 100%);
        color: white;
        padding: 20px 24px;
        border-radius: 14px;
        margin-bottom: 20px;
    }
    .filter-badge {
        display: inline-block;
        background: #eff6ff;
        color: #1d4ed8;
        padding: 2px 8px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 600;
        margin: 2px 2px 2px 0;
        border: 1px solid #dbeafe;
    }
    .filters-cell {
        max-width: 300px;
    }
    .rapport-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    .rapport-table thead tr {
        background: #1e3a5f;
        color: white;
    }
    .rapport-table thead th {
        padding: 12px 10px;
        font-weight: 600;
        font-size: 12px;
        text-align: left;
        white-space: nowrap;
    }
    .rapport-table tbody tr {
        border-bottom: 1px solid #e2e8f0;
        transition: background 0.15s;
    }
    .rapport-table tbody tr:hover {
        background: #f8fafc;
    }
    .rapport-table tbody td {
        padding: 12px 10px;
        font-size: 13px;
        vertical-align: middle;
    }
    .btn-action {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 5px 10px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        white-space: nowrap;
    }
    .btn-pdf {
        background: #dc3545;
        color: white;
    }
    .btn-pdf:hover {
        background: #b91c1c;
        color: white;
    }
    .btn-reload {
        background: #1d4ed8;
        color: white;
    }
    .btn-reload:hover {
        background: #1e40af;
        color: white;
    }
    .btn-delete {
        background: transparent;
        color: #dc3545;
        border: 1px solid #fca5a5;
    }
    .btn-delete:hover {
        background: #fee2e2;
        color: #991b1b;
    }
    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #94a3b8;
    }
    .empty-state .ico {
        font-size: 3rem;
        margin-bottom: 12px;
    }
    .kpi-mini {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 11px;
        color: #64748b;
        background: #f1f5f9;
        padding: 2px 8px;
        border-radius: 6px;
    }
</style>

{{-- HEADER --}}
<div class="rapport-header">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="mb-1">📁 Rapports sauvegardés</h2>
            <p class="mb-0 opacity-75">Historique de tous vos rapports générés</p>
        </div>
        <a href="{{ route('rapport.index') }}" 
           style="background:white;color:#1e3a5f;padding:10px 20px;border-radius:10px;text-decoration:none;font-weight:700;font-size:13px;">
            ✨ Nouveau rapport
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        ✅ {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        ⚠️ {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- STATS --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card p-3 text-center" style="border-radius:10px;border-left:4px solid #1d4ed8;">
            <div style="font-size:24px;font-weight:900;color:#1e3a5f;">{{ $rapports->total() }}</div>
            <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Rapports totaux</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center" style="border-radius:10px;border-left:4px solid #16a34a;">
            <div style="font-size:24px;font-weight:900;color:#16a34a;">
                {{ $rapports->filter(fn($r) => $r->created_at->isToday())->count() }}
            </div>
            <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Aujourd'hui</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center" style="border-radius:10px;border-left:4px solid #f59e0b;">
            <div style="font-size:24px;font-weight:900;color:#f59e0b;">
                {{ $rapports->filter(fn($r) => $r->created_at->isCurrentWeek())->count() }}
            </div>
            <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Cette semaine</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card p-3 text-center" style="border-radius:10px;border-left:4px solid #7c3aed;">
            <div style="font-size:24px;font-weight:900;color:#7c3aed;">
                {{ $rapports->filter(fn($r) => $r->fichier_pdf)->count() }}
            </div>
            <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:600;">Avec PDF</div>
        </div>
    </div>
</div>

{{-- TABLEAU --}}
@if($rapports->count() > 0)
<div class="card p-0 overflow-hidden" style="border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
    <div class="table-responsive">
        <table class="rapport-table">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    <th style="width:25%;">📋 Titre</th>
                    <th style="width:20%;">📝 Description</th>
                    <th style="width:15%;">📅 Date</th>
                    <th style="width:20%;">🔍 Filtres appliqués</th>
                    <th style="width:15%;">⚙️ Actions</th>
                </tr>
            </thead>
            <tbody>
            @foreach($rapports as $i => $rapport)
                <tr>
                    <td>
                        <span style="display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;background:#eff6ff;color:#1d4ed8;border-radius:50%;font-size:11px;font-weight:700;">
                            {{ $i + 1 }}
                        </span>
                    </td>
                    <td>
                        <div style="font-weight:700;color:#1e3a5f;margin-bottom:2px;">
                            {{ $rapport->titre }}
                        </div>
                        @if($rapport->fichier_pdf)
                            <span style="font-size:10px;color:#16a34a;">📄 PDF disponible</span>
                        @else
                            <span style="font-size:10px;color:#94a3b8;">⚠️ Pas de PDF</span>
                        @endif
                    </td>
                    <td>
                        @if($rapport->description)
                            <span style="font-size:12px;color:#64748b;">
                                {{ Str::limit($rapport->description, 60) }}
                            </span>
                        @else
                            <span style="font-size:11px;color:#cbd5e1;font-style:italic;">Aucune description</span>
                        @endif
                    </td>
                    <td>
                        <div style="font-size:12px;font-weight:600;color:#1e3a5f;">
                            {{ $rapport->created_at->format('d/m/Y') }}
                        </div>
                        <div style="font-size:10px;color:#94a3b8;">
                            🕐 {{ $rapport->created_at->format('H:i') }}
                        </div>
                    </td>
                    <td class="filters-cell">
                        @php
                            $filtres = collect($rapport->filtres ?? [])->filter(function($v) {
                                return !empty($v) && $v !== 'null';
                            });
                        @endphp
                        @if($filtres->count() > 0)
                            <div style="display:flex;flex-wrap:wrap;gap:2px;">
                                @foreach($filtres as $key => $value)
                                    @php
                                        $labels = [
                                            'grand_sites' => '🏢 GS souhaité',
                                            'sites' => '🗺️ GS affectation',
                                            'tfs' => '🧭 TF',
                                            'clients_ids' => '👤 Clients',
                                            'types' => '🏷️ Types',
                                            'statuts_dossier' => '📁 Statut',
                                            'date_debut' => '📅 Du',
                                            'date_fin' => '📅 Au',
                                            'colonnes' => '📋 Colonnes',
                                        ];
                                        $label = $labels[$key] ?? $key;
                                        $count = is_array($value) ? count($value) : 1;
                                    @endphp
                                    <span class="filter-badge">
                                        {{ $label }} : {{ $count }}
                                    </span>
                                @endforeach
                            </div>
                        @else
                            <span style="font-size:11px;color:#cbd5e1;font-style:italic;">Aucun filtre</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap;">
                            @if($rapport->fichier_pdf)
                                <a href="{{ asset('storage/' . $rapport->fichier_pdf) }}" 
                                   target="_blank"
                                   class="btn-action btn-pdf"
                                   title="Ouvrir le PDF">
                                    📄 PDF
                                </a>
                            @endif
                            @if($rapport->filtres)
                                <a href="{{ route('rapport.index', $rapport->filtres) }}"
                                   class="btn-action btn-reload"
                                   title="Recharger les filtres">
                                    🔍 Recharger
                                </a>
                            @endif
                            <form action="{{ route('rapport.destroy', $rapport->id) }}" 
                                  method="POST" 
                                  style="display:inline"
                                  onsubmit="return confirm('⚠️ Supprimer ce rapport ?\n\nCette action est irréversible.')">
                                @csrf 
                                @method('DELETE')
                                <button class="btn-action btn-delete" title="Supprimer">
                                    🗑
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- PAGINATION --}}
<div class="d-flex justify-content-center mt-4">
    {{ $rapports->links() }}
</div>

@else
{{-- EMPTY STATE --}}
<div class="card p-0">
    <div class="empty-state">
        <div class="ico">📁</div>
        <h4 style="color:#1e3a5f;font-weight:800;">Aucun rapport sauvegardé</h4>
        <p style="color:#64748b;font-size:13px;margin-bottom:20px;">
            Commencez par générer votre premier rapport d'activité avec les filtres de votre choix.
        </p>
        <a href="{{ route('rapport.index') }}" 
           style="background:linear-gradient(135deg,#1d4ed8,#7c3aed);color:white;padding:12px 24px;border-radius:10px;text-decoration:none;font-weight:700;font-size:13px;display:inline-flex;align-items:center;gap:8px;">
            ✨ Créer un rapport
        </a>
    </div>
</div>
@endif

@endsection