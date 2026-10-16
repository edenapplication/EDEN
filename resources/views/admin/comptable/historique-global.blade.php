@extends('layouts.comptable')

@section('title', 'Historique global')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">🕐 Historique global logistique</h2>
        <p style="color:#64748b;font-size:13px;margin:0;">Tous les paiements logistiques enregistrés</p>
    </div>
    <a href="{{ route('comptable.index') }}" class="btn-payer">🚗 Retour</a>
</div>

<div style="background:white;border-radius:12px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:20px;">
    <form method="GET" action="{{ route('comptable.historique-global') }}" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:700;color:#64748b;">🔍 Recherche</label>
            <input type="text" name="q" class="form-control form-control-sm"
                   placeholder="Client, dossier, téléphone..." value="{{ request('q') }}">
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Du</label>
            <input type="date" name="du" class="form-control form-control-sm" value="{{ request('du') }}">
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Au</label>
            <input type="date" name="au" class="form-control form-control-sm" value="{{ request('au') }}">
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn-payer btn-sm">🔍 Filtrer</button>
            <a href="{{ route('comptable.historique-global') }}" class="btn btn-outline-secondary btn-sm">✖</a>
        </div>
        <div class="col-md-2 text-end">
            <div style="font-size:11px;color:#64748b;">Total filtré</div>
            <div style="font-size:18px;font-weight:900;color:#7c3aed;">
                {{ number_format($totalFiltre, 0, ',', ' ') }} FCFA
            </div>
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
                        <br><small style="color:#94a3b8;">{{ $p->dossier?->client?->phone ?? '-' }}</small>
                    </td>
                    <td style="padding:12px 16px;">{{ $p->dossier?->nom_dossier ?? '-' }}</td>
                    <td style="padding:12px 16px;">{{ $p->beneficiaire?->nom ?? '-' }}</td>
                    <td style="padding:12px 16px;text-align:right;">
                        <strong style="color:#7c3aed;">{{ number_format($p->montant, 0, ',', ' ') }} FCFA</strong>
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" style="text-align:center;padding:60px;color:#94a3b8;">
                    <div style="font-size:48px;">📭</div>
                    <div style="font-weight:700;margin-top:12px;">Aucun paiement logistique trouvé</div>
                </td></tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $paiements->links() }}
</div>

@endsection