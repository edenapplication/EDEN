@extends('admin.layout')
@section('content')

<style>
    .section-bloc { 
        background:white; 
        border-radius:12px; 
        margin-bottom:16px; 
        box-shadow:0 2px 8px rgba(0,0,0,0.06); 
        overflow:hidden; 
    }
    .section-header { 
        background:#1e3a5f; 
        color:white; 
        padding:12px 18px; 
        font-weight:700; 
        font-size:14px; 
    }
    .section-body { padding:16px; overflow-x:auto; }

    .tab-fiche { 
        width:100%; 
        border-collapse:collapse; 
        font-size:13px; 
    }
    .tab-fiche thead tr { background:#dbeafe; }
    .tab-fiche thead th { 
        padding:10px 12px; 
        font-weight:700; 
        color:#1d4ed8; 
        text-align:left; 
        border:1px solid #bfdbfe; 
    }
    .tab-fiche tbody td { 
        padding:8px 12px; 
        border:1px solid #e2e8f0; 
    }
    .tab-fiche tbody tr:nth-child(even) td { background:#f8fafc; }

    .info-pill { 
        display:inline-block; 
        background:#f1f5f9; 
        border-radius:8px; 
        padding:4px 12px; 
        font-size:12px; 
        color:#1e3a5f; 
        font-weight:600; 
        margin-right:6px; 
        margin-bottom:4px; 
    }

    .total-row td {
        background:#eef6ff;
        font-weight:bold;
        color:#1e3a5f;
    }
    .total-general {
        margin-top:20px;
        background:#1e3a5f;
        color:white;
        padding:14px 18px;
        font-size:16px;
        font-weight:700;
        text-align:right;
        border-radius:8px;
    }

    /* ================= STYLE EN-TÊTE (comme l'image) ================= */
    .fiche-entete {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 25px;
        border-bottom: 3px solid #1d4ed8;
        padding-bottom: 18px;
    }
    .fiche-logo {
        width: 70px;
        height: 70px;
        background: white;
        border: 3px solid #dc2626;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        font-weight: 900;
        color: #1d4ed8;
        flex-shrink: 0;
    }
    .fiche-centre {
        flex: 1;
        text-align: center;
        padding: 0 20px;
    }
    .fiche-societe {
        font-size: 19px;
        font-weight: 900;
        color: #1e3a5f;
        text-transform: uppercase;
    }
    .fiche-slogan {
        font-size: 9.5px;
        color: #dc2626;
        font-style: italic;
        margin-top: 4px;
    }
    .fiche-titre {
        font-size: 16px;
        font-weight: 900;
        color: #1e3a5f;
        text-transform: uppercase;
        margin-top: 16px;
        letter-spacing: 0.5px;
    }
    .fiche-droite {
        text-align: right;
        font-size: 13px;
        min-width: 180px;
    }
    .fiche-info {
        margin-bottom: 6px;
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('admin.feb.fiches.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← Fiches</a>
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

<!-- ================= EN-TÊTE STYLE IMAGE ================= -->
<div class="fiche-entete">
    <!-- Logo -->
    <div class="fiche-logo">E</div>

    <!-- Centre -->
    <div class="fiche-centre">
        <div class="fiche-societe">EDEN GROUP SARL</div>
        <div class="fiche-slogan">Réservez votre parcelle et garantissez votre futur</div>
        <div class="fiche-titre">FICHE D'EXPRESSION DES BESOINS</div>
    </div>

    <!-- Droite -->
    <div class="fiche-droite">
        @if($fiche->numero_fiche)
            <div class="fiche-info"><strong>N° :</strong> {{ $fiche->numero_fiche }}</div>
        @endif
        <div class="fiche-info"><strong>Date :</strong> {{ now()->format('d/m/Y') }}</div>
        @if($fiche->soumise_at)
            <div class="fiche-info"><strong>Soumission :</strong> {{ $fiche->soumise_at->format('d/m/Y H:i') }}</div>
        @endif
    </div>
</div>

<!-- Infos demandeur -->
<div style="background:white;border-radius:12px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,0.06);margin-bottom:20px;">
    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:10px;">Informations du demandeur</div>
    
    <span class="info-pill">👤 {{ $fiche->utilisateur?->nom_complet }}</span>
    <span class="info-pill">💼 {{ $fiche->utilisateur?->poste ?? '-' }}</span>
    <span class="info-pill">🏢 {{ $fiche->utilisateur?->agence?->nom ?? '-' }}</span>
    <span class="info-pill">📍 {{ $fiche->utilisateur?->direction ?? '-' }}</span>
    <span class="info-pill">📅 {{ $fiche->soumise_at?->format('d/m/Y à H:i') ?? '-' }}</span>
</div>

@php
    $totalGeneral = 0;
@endphp

{{-- Sections --}}
@forelse($fiche->sections as $section)
@php
    $totalSection = 0;
    $colPrix = $section->colonnes->first(function ($col) {
        return in_array(strtolower($col->libelle), ['prix total','total','montant','prix']);
    });
@endphp

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
                    {{-- Titre de la section --}}
                    <tr>
                        <td colspan="{{ $section->colonnes->count() + 1 }}">
                            <div class="section-header">
                                {{ $loop->iteration }}. {{ $section->titre }}
                            </div>
                        </td>
                    </tr>

                    {{-- Lignes --}}
                    @foreach($section->lignes as $ligne)
                    @php
                        $montant = 0;
                        if ($colPrix) {
                            $montant = (float) str_replace([' ', ','], ['',''], $ligne->valeurs[$colPrix->id] ?? 0);
                        }
                        $totalSection += $montant;
                    @endphp
                    <tr>
                        <td style="color:#94a3b8;text-align:center;">{{ $ligne->numero_ligne }}</td>
                        @foreach($section->colonnes as $col)
                            <td>{{ $ligne->valeurs[$col->id] ?? '' }}</td>
                        @endforeach
                    </tr>
                    @endforeach

                    {{-- Total Section --}}
                    <tr class="total-row">
                        <td colspan="{{ $section->colonnes->count() }}">Total section</td>
                        <td style="text-align:right;">
                            {{ number_format($totalSection, 0, ',', ' ') }} FCFA
                        </td>
                    </tr>
                </tbody>
            </table>

            @php $totalGeneral += $totalSection; @endphp
        @else
            <div style="color:#94a3b8;font-size:13px;padding:10px;">Section vide.</div>
        @endif
    </div>
</div>

@empty
    <div style="color:#94a3b8;text-align:center;padding:30px;">Aucune section.</div>
@endforelse

{{-- TOTAL GÉNÉRAL --}}
@if($totalGeneral > 0)
<div class="total-general">
    TOTAL GÉNÉRAL : {{ number_format($totalGeneral, 0, ',', ' ') }} FCFA
</div>
@endif

@endsection