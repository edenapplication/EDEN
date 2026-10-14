@extends('layouts.comptable')

@section('title', 'Historique du dossier')

@section('styles')
<style>
    .recap-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 16px;
        margin-bottom: 20px;
    }
    .recap-stat {
        background: white;
        border-radius: 12px;
        padding: 18px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06);
        border-left: 4px solid #1d4ed8;
    }
    .recap-stat.green  { border-left-color: #16a34a; }
    .recap-stat.red    { border-left-color: #dc2626; }
    .recap-stat .label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 700;
    }
    .recap-stat .value {
        font-size: 22px;
        font-weight: 900;
        color: #1e3a5f;
        margin-top: 4px;
    }
    .recap-stat.green .value { color: #16a34a; }
    .recap-stat.red .value   { color: #dc2626; }

    .histo-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    .histo-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px;
        border-bottom: 1px solid #f1f5f9;
    }
    .histo-item:last-child { border-bottom: none; }
</style>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('comptable.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← Retour</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">📜 Historique des paiements techniques</h2>
        <div style="font-size:13px;color:#64748b;">
            {{ $dossier->client?->name }} — {{ $dossier->nom_dossier }}
        </div>
    </div>
</div>

@php
    $totalPaye = $paiements->sum('montant');
    $prixRef   = $dossier->prix_technique ?? 0;
    $reste     = max(0, $prixRef - $totalPaye);
@endphp

<div class="recap-stats">
    <div class="recap-stat">
        <div class="label">Référence</div>
        <div class="value">{{ number_format($prixRef, 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="recap-stat green">
        <div class="label">Total payé</div>
        <div class="value">{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</div>
    </div>
    <div class="recap-stat red">
        <div class="label">Reste</div>
        <div class="value">{{ number_format($reste, 0, ',', ' ') }} FCFA</div>
    </div>
</div>

<div class="histo-card">
    <h6 style="font-weight:700;color:#1e3a5f;margin-bottom:14px;">📅 Détail des paiements</h6>

    @forelse($paiements as $p)
        <div class="histo-item">
            <div>
                <div style="font-weight:700;color:#1e3a5f;font-size:15px;">
                    {{ number_format($p->montant, 0, ',', ' ') }} FCFA
                </div>
                <div style="font-size:12px;color:#64748b;">
                    📅 {{ \Carbon\Carbon::parse($p->date_paiement)->format('d/m/Y') }}
                    @if($p->user_reference)
                        · 🔑 {{ $p->user_reference }}
                    @endif
                </div>
                @if($p->note)
                    <div style="font-size:11px;color:#94a3b8;margin-top:2px;">📝 {{ $p->note }}</div>
                @endif
            </div>
            <div style="font-size:11px;color:#94a3b8;">#{{ $p->id }}</div>
        </div>
    @empty
        <div style="text-align:center;padding:40px;color:#94a3b8;">
            <div style="font-size:40px;">📭</div>
            <div style="font-weight:700;margin-top:10px;">Aucun paiement enregistré</div>
        </div>
    @endforelse
</div>

@endsection