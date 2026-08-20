@extends('admin.layout')
@section('content')

<style>
.bon-card { background:white; border-radius:12px; padding:16px; margin-bottom:10px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #1d4ed8; }
.versement-chip { display:inline-block; padding:3px 10px; border-radius:8px; font-size:11px; font-weight:700; margin-right:4px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('suivi-client.show', $dossier->client_id) }}" class="btn btn-outline-secondary btn-sm mb-2">← Dossier</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">🧾 Bons de paiement</h2>
        <div style="font-size:13px;color:#64748b;">
            {{ $dossier->client?->name }} — {{ $dossier->grandSite?->nom ?? $dossier->nom_dossier }}
        </div>
    </div>
    <a href="{{ route('bons.creer', $dossier->id) }}" class="btn btn-primary">+ Nouveau bon</a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
@endif

{{-- RÉCAP DOSSIER --}}
@php
    $totD = $dossier->paiements->sum('montant');
    $totT = $dossier->paiementsTechniques->sum('montant');
    $totL = $dossier->paiementsLogistiques->sum('montant');
    $totM = $dossier->paiementsMorcellements->sum('montant');
    $totAll = $totD + $totT + $totL + $totM;
    $refAll = ($dossier->prix_superficie ?? 0) + ($dossier->prix_technique ?? 0) + ($dossier->prix_logistique ?? 0) + ($dossier->prix_morcellement ?? 0);
@endphp

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div style="background:white;border-radius:12px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #1d4ed8;text-align:center;">
            <div style="font-size:16px;font-weight:800;color:#1d4ed8;">{{ number_format($totD,0,',',' ') }}</div>
            <div style="font-size:10px;color:#64748b;text-transform:uppercase;">Dossier FCFA</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:white;border-radius:12px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #ea580c;text-align:center;">
            <div style="font-size:16px;font-weight:800;color:#ea580c;">{{ number_format($totT,0,',',' ') }}</div>
            <div style="font-size:10px;color:#64748b;text-transform:uppercase;">Technique FCFA</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:white;border-radius:12px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #7c3aed;text-align:center;">
            <div style="font-size:16px;font-weight:800;color:#7c3aed;">{{ number_format($totL,0,',',' ') }}</div>
            <div style="font-size:10px;color:#64748b;text-transform:uppercase;">Logistique FCFA</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:white;border-radius:12px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border-top:3px solid #ca8a04;text-align:center;">
            <div style="font-size:16px;font-weight:800;color:#ca8a04;">{{ number_format($totM,0,',',' ') }}</div>
            <div style="font-size:10px;color:#64748b;text-transform:uppercase;">Morcellement FCFA</div>
        </div>
    </div>
</div>

{{-- LISTE DES BONS --}}
@forelse($dossier->bons as $bon)
<div class="bon-card">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div style="font-weight:700;font-size:14px;color:#1e3a5f;">
                {{ $bon->numero_bon }}
                <span style="font-size:12px;color:#64748b;font-weight:400;margin-left:8px;">
                    📅 {{ $bon->date_bon->format('d/m/Y') }}
                </span>
            </div>
            <div style="margin-top:8px;">
                @if($bon->versement_dossier > 0)
                    <span class="versement-chip" style="background:#dbeafe;color:#1d4ed8;">
                        📁Terrain : {{ number_format($bon->versement_dossier,0,',',' ') }} FCFA
                    </span>
                @endif
                @if($bon->versement_technique > 0)
                    <span class="versement-chip" style="background:#ffedd5;color:#ea580c;">
                        🛠️Dossiet Tech : {{ number_format($bon->versement_technique,0,',',' ') }} FCFA
                    </span>
                @endif
                @if($bon->versement_logistique > 0)
                    <span class="versement-chip" style="background:#f3e8ff;color:#7c3aed;">
                        🚗 Logistique : {{ number_format($bon->versement_logistique,0,',',' ') }} FCFA
                    </span>
                @endif
                @if($bon->versement_morcellement > 0)
                    <span class="versement-chip" style="background:#fef9c3;color:#ca8a04;">
                        ✂️ Morcellement : {{ number_format($bon->versement_morcellement,0,',',' ') }} FCFA
                    </span>
                @endif
                <span style="font-size:12px;font-weight:800;color:#16a34a;margin-left:8px;">
                    = {{ number_format($bon->total_versement,0,',',' ') }} FCFA
                </span>
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('bons.show', $bon->id) }}" class="btn btn-primary btn-sm" style="font-size:11px;">👁 Voir</a>
            <a href="{{ route('bons.pdf', $bon->id) }}" class="btn btn-outline-danger btn-sm" style="font-size:11px;">🖨️</a>
            <form action="{{ route('bons.destroy', $bon->id) }}" method="POST" style="display:inline;"
                  onsubmit="return confirm('Supprimer ce bon ?')">
                @csrf @method('DELETE')
                <button class="btn btn-outline-danger btn-sm" style="font-size:11px;">🗑</button>
            </form>
        </div>
    </div>
</div>
@empty
<div style="text-align:center;padding:40px;color:#94a3b8;">
    <div style="font-size:40px;">🧾</div>
    <div style="font-weight:700;margin-top:10px;">Aucun bon de paiement</div>
    <a href="{{ route('bons.creer', $dossier->id) }}" class="btn btn-primary mt-3">+ Créer le premier bon</a>
</div>
@endforelse

@endsection