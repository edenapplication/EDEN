@extends('layouts.comptable')

@section('title', 'Mes paiements')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">👤 Mes paiements logistiques</h2>
        <p style="color:#64748b;font-size:13px;margin:0;">
            Réf. <strong>{{ auth()->user()->reference ?? 'N/A' }}</strong>
        </p>
    </div>
</div>

<div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:20px;">
    <div style="background:white;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #7c3aed;">
        <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;">📊 Nombre</div>
        <div style="font-size:26px;font-weight:900;color:#7c3aed;">{{ $stats['total_count'] }}</div>
    </div>
    <div style="background:white;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #16a34a;">
        <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;">💰 Total</div>
        <div style="font-size:22px;font-weight:900;color:#16a34a;">{{ number_format($stats['total_montant'], 0, ',', ' ') }}</div>
    </div>
    <div style="background:white;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #f59e0b;">
        <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;">📅 Aujourd'hui</div>
        <div style="font-size:22px;font-weight:900;color:#f59e0b;">{{ number_format($stats['aujourdhui'], 0, ',', ' ') }}</div>
    </div>
    <div style="background:white;border-radius:12px;padding:18px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-left:4px solid #0891b2;">
        <div style="font-size:11px;color:#64748b;font-weight:700;text-transform:uppercase;">🗓️ Ce mois</div>
        <div style="font-size:22px;font-weight:900;color:#0891b2;">{{ number_format($stats['ce_mois'], 0, ',', ' ') }}</div>
    </div>
</div>

<div style="background:white;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:20px;">
    <form method="GET" action="{{ route('comptable.mes-paiements') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Du</label>
            <input type="date" name="du" class="form-control form-control-sm" value="{{ request('du') }}">
        </div>
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Au</label>
            <input type="date" name="au" class="form-control form-control-sm" value="{{ request('au') }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn-payer btn-sm">🔍 Filtrer</button>
            <a href="{{ route('comptable.mes-paiements') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
    </form>
</div>

<div style="background:white;border-radius:14px;overflow:hidden;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
    <table style="width:100%;border-collapse:collapse;font-size:13px;">
        <thead style="background:#f8fafc;color:#64748b;font-size:11px;text-transform:uppercase;font-weight:700;">
            <tr>
                <th style="padding:12px 16px;text-align:left;">Date</th>
                <th style="padding:12px 16px;text-align:left;">Client</th>
                <th style="padding:12px 16px;text-align:left;">Dossier</th>
                <th style="padding:12px 16px;text-align:left;">Bénéficiaire</th>
                <th style="padding:12px 16px;text-align:right;">Montant</th>
            </tr>
        </thead>
        <tbody>
            @forelse($paiements as $p)
                <tr style="border-top:1px solid #f1f5f9;">
                    <td style="padding:12px 16px;">📅 {{ \Carbon\Carbon::parse($p->date_paiement)->format('d/m/Y') }}</td>
                    <td style="padding:12px 16px;">
                        <strong>{{ $p->dossier?->client?->name ?? '-' }}</strong>
                    </td>
                    <td style="padding:12px 16px;">{{ $p->dossier?->nom_dossier ?? '-' }}</td>
                    <td style="padding:12px 16px;">{{ $p->beneficiaire?->nom ?? '-' }}</td>
                    <td style="padding:12px 16px;text-align:right;">
                        <strong style="color:#7c3aed;">{{ number_format($p->montant, 0, ',', ' ') }} FCFA</strong>
                    </td>
                </tr>
            @empty
                <tr><td colspan="5" style="text-align:center;padding:60px;color:#94a3b8;">
                    <div style="font-size:48px;">📭</div>
                    <div style="font-weight:700;margin-top:12px;">Aucun paiement</div>
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $paiements->links() }}
</div>

@endsection