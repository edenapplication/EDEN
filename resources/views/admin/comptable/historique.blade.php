@extends('layouts.comptable')

@section('title', 'Historique du dossier')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <a href="{{ route('comptable.index') }}" class="btn btn-outline-secondary btn-sm mb-2">← Retour</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">📜 Historique Logistique</h2>
        <div style="font-size:13px;color:#64748b;">
            {{ $dossier->client?->name }} — {{ $dossier->nom_dossier }}
        </div>
    </div>
</div>

@php
    $totalPaye = $paiements->sum('montant');
    $prixRef   = $dossier->prix_logistique ?? 0;
    $reste     = max(0, $prixRef - $totalPaye);
@endphp

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:20px;">
    <div style="background:white;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #7c3aed;">
        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;">Référence logistique</div>
        <div style="font-size:20px;font-weight:900;color:#7c3aed;">{{ number_format($prixRef, 0, ',', ' ') }} FCFA</div>
    </div>
    <div style="background:white;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #16a34a;">
        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;">Total payé</div>
        <div style="font-size:20px;font-weight:900;color:#16a34a;">{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</div>
    </div>
    <div style="background:white;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid {{ $reste > 0 ? '#dc2626' : '#16a34a' }};">
        <div style="font-size:11px;color:#64748b;text-transform:uppercase;font-weight:700;">Reste</div>
        <div style="font-size:20px;font-weight:900;color:{{ $reste > 0 ? '#dc2626' : '#16a34a' }};">{{ number_format($reste, 0, ',', ' ') }} FCFA</div>
    </div>
</div>

<div style="background:white;border-radius:14px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
    <h6 style="font-weight:700;color:#1e3a5f;margin-bottom:14px;">📅 Détail des paiements logistiques</h6>

    @forelse($paiements as $p)
        <div style="display:flex;justify-content:space-between;align-items:center;padding:12px;border-bottom:1px solid #f1f5f9;">
            <div>
                <div style="font-weight:700;color:#1e3a5f;font-size:15px;">
                    {{ number_format($p->montant, 0, ',', ' ') }} FCFA
                </div>
                <div style="font-size:12px;color:#64748b;">
                    📅 {{ \Carbon\Carbon::parse($p->date_paiement)->format('d/m/Y') }}
                    @if($p->beneficiaire)
                        · 👤 {{ $p->beneficiaire->nom }}
                    @endif
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
            <div style="font-weight:700;margin-top:10px;">Aucun paiement logistique enregistré</div>
        </div>
    @endforelse
</div>

@endsection