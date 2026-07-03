@extends('admin.layout')
@section('content')

<style>
.section-bloc { background:white; border-radius:12px; margin-bottom:16px; box-shadow:0 2px 8px rgba(0,0,0,0.06); overflow:hidden; }
.section-header { background:#1e3a5f; color:white; padding:12px 18px; font-weight:700; font-size:14px; }
.section-body { padding:16px; overflow-x:auto; }
.tab-fiche { width:100%; border-collapse:collapse; font-size:13px; }
.tab-fiche thead tr { background:#dbeafe; }
.tab-fiche thead th { padding:10px 12px; font-weight:700; color:#1d4ed8; text-align:left; border:1px solid #bfdbfe; }
.tab-fiche tbody td { padding:8px 12px; border:1px solid #e2e8f0; }
.tab-fiche tbody tr:nth-child(even) td { background:#f8fafc; }
.info-pill { display:inline-block; background:#f1f5f9; border-radius:8px; padding:4px 12px; font-size:12px; color:#1e3a5f; font-weight:600; margin-right:6px; margin-bottom:4px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.feb.fiches.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← Fiches</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">📋 {{ $fiche->titre }}</h2>
    </div>
    <div class="d-flex gap-2">
        @if(!$fiche->vue_admin)
        <form method="POST" action="{{ route('admin.feb.fiches.marquer', $fiche->id) }}">
            @csrf
            <button class="btn btn-success btn-sm">✅ Marquer comme vue</button>
        </form>
        @endif
        <a href="{{ route('admin.feb.fiches.pdf', $fiche->id) }}" class="btn btn-outline-danger btn-sm">🖨️ PDF</a>
    </div>
</div>

{{-- Infos utilisateur --}}
<div style="background:white;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-bottom:16px;">
    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:10px;">Informations</div>
    <span class="info-pill">👤 {{ $fiche->utilisateur?->nom_complet }}</span>
    <span class="info-pill">💼 {{ $fiche->utilisateur?->poste ?? '-' }}</span>
    <span class="info-pill">🏢 Agence : {{ $fiche->utilisateur?->agence?->nom ?? '-' }}</span>
    <span class="info-pill">📅 Soumise le {{ $fiche->soumise_at?->format('d/m/Y à H:i') }}</span>
    <span class="info-pill">📁 {{ $fiche->sections->count() }} section(s)</span>
    @if($fiche->description)
    <div style="margin-top:10px;font-size:13px;color:#64748b;">{{ $fiche->description }}</div>
    @endif
</div>

{{-- Sections --}}
@forelse($fiche->sections as $section)
<div class="section-bloc">
    
    <div class="section-body">
        @if($section->colonnes->count() > 0 && $section->lignes->count() > 0)
        <table class="tab-fiche">
            <thead>
                <tr>
                    <th style="width:40px;">#</th>
                    @foreach($section->colonnes as $col)
                    <th>{{ $col->libelle }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
    <tr>
        <td colspan="{{ $section->colonnes->count() + 1 }}" style="padding:0;border:none;">
            <div class="section-header" style="background:#1e3a5f;color:#fff;padding:14px 18px;font-size:16px;font-weight:700;display:flex;justify-content:space-between;align-items:center;">
                <span>{{ $loop->iteration }}. {{ $section->titre }}</span>

                <span style="font-size:13px;opacity:.85;">
                    {{ $section->colonnes->count() }} colonne(s) ·
                    {{ $section->lignes->count() }} ligne(s)
                </span>
            </div>
        </td>
    </tr>

    @foreach($section->lignes as $ligne)
    <tr>
        <td style="color:#94a3b8;text-align:center;">{{ $ligne->numero_ligne }}</td>
        @foreach($section->colonnes as $col)
        <td>{{ $ligne->valeurs[$col->id] ?? '' }}</td>
        @endforeach
    </tr>
    @endforeach
</tbody>
        </table>
        @else
        <div style="color:#94a3b8;font-size:13px;padding:10px;">Section vide.</div>
        @endif
    </div>
</div>
@empty
<div style="color:#94a3b8;text-align:center;padding:30px;">Aucune section.</div>
@endforelse

@endsection