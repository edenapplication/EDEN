<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family: DejaVu Sans, sans-serif; font-size:8.5px; color:#1e293b; padding:14px; }
    h2 { font-size:14px; color:#1e3a5f; margin-bottom:4px; }
    .sub { font-size:9px; color:#64748b; margin-bottom:12px; }
    table { width:100%; border-collapse:collapse; margin-top:6px; }
    thead tr { background:#1e3a5f; color:white; }
    thead th { padding:5px 4px; font-size:7.5px; text-align:left; white-space:nowrap; }
    tbody tr:nth-child(even) { background:#f8fafc; }
    tbody td { padding:4px; border-bottom:1px solid #e2e8f0; vertical-align:middle; }
    tfoot td { background:#1e3a5f; color:white; font-weight:700; padding:5px 4px; font-size:8px; }
    .badge { display:inline-block; padding:1px 6px; border-radius:8px; font-size:7px; font-weight:700; }
    .badge-v1 { background:#dbeafe; color:#1d4ed8; }
    .badge-v2 { background:#f3e8ff; color:#7c3aed; }
    .badge-paye { background:#dcfce7; color:#15803d; }
    .badge-valide { background:#fef3c7; color:#92400e; }
    .badge-brouillon { background:#f1f5f9; color:#475569; }
    .red { color:#dc2626; }
    .green { color:#16a34a; }
    .purple { color:#7c3aed; }
    .blue { color:#1d4ed8; }
    .orange { color:#f59e0b; }
    .text-end { text-align:right; }
    .text-center { text-align:center; }
    .fw-bold { font-weight:700; }
    .fs-small { font-size:7.5px; }
</style>
</head>
<body>

<h2>📋 Liste des bulletins de paie</h2>
<div class="sub">
    Période : {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}
    @if($vague) — Vague : {{ $vague }} @endif
    — Généré le {{ now()->format('d/m/Y à H:i') }}
</div>

<table>
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Nom</th>
            <th>Direction</th>
            <th>Vague</th>
            <th class="text-end">Brut</th>
            <th class="text-end">HS</th>
            <th class="text-end">Retards</th>
            <th class="text-end">Absences</th>
            {{-- COLONNES CNPS COMMENTÉES --}}
            {{-- <th class="text-end">CNPS Sal.</th> --}}
            {{-- <th class="text-end">CNPS Pat.</th> --}}
            {{-- <th class="text-end">Total CNPS</th> --}}
            <th class="text-end">Sanction</th>
            <th class="text-end">Acompte</th>
            <th class="text-end">Prêt</th>
            <th class="text-end">Net</th>
            <th class="text-end">Prêt restant</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
    @foreach($bulletins->sortBy('employe.nom') as $b)
        <tr>
            <td style="color:#1d4ed8;font-weight:700;">{{ $b->employe?->matricule }}</td>
            <td style="font-weight:600;">{{ $b->employe?->nom }} {{ $b->employe?->prenom }}</td>
            <td>{{ $b->employe?->direction?->nom ?? '-' }}</td>
            <td>
                <span class="badge {{ $b->vague === 'VAGUE 1' ? 'badge-v1' : 'badge-v2' }}">{{ $b->vague }}</span>
            </td>
            <td class="text-end">{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
            <td class="text-end green">{{ $b->montant_heures_sup > 0 ? number_format($b->montant_heures_sup, 0, ',', ' ') : '-' }}</td>
            <td class="text-end red">{{ $b->montant_retard > 0 ? number_format($b->montant_retard, 0, ',', ' ') : '-' }}</td>
            <td class="text-end red">{{ $b->montant_absence > 0 ? number_format($b->montant_absence, 0, ',', ' ') : '-' }}</td>
            {{-- DONNÉES CNPS COMMENTÉES --}}
            {{-- <td class="text-end red">{{ $b->cnps_salariale > 0 ? number_format($b->cnps_salariale, 0, ',', ' ') : '-' }}</td> --}}
            {{-- <td class="text-end purple">{{ $b->cnps_patronale > 0 ? number_format($b->cnps_patronale, 0, ',', ' ') : '-' }}</td> --}}
            {{-- <td class="text-end blue fw-bold">{{ $b->cnps > 0 ? number_format($b->cnps, 0, ',', ' ') : '-' }}</td> --}}
            <td class="text-end red">{{ $b->montant_sanction > 0 ? number_format($b->montant_sanction, 0, ',', ' ') : '-' }}</td>
            <td class="text-end purple">{{ $b->acompte > 0 ? number_format($b->acompte, 0, ',', ' ') : '-' }}</td>
            <td class="text-end purple">{{ $b->pret > 0 ? number_format($b->pret, 0, ',', ' ') : '-' }}</td>
            <td class="text-end" style="font-weight:800;color:#16a34a;font-size:9px;">{{ number_format($b->net_a_payer, 0, ',', ' ') }}</td>
            <td class="text-end red" style="font-weight:700;">
                {{ isset($b->pret_restant) && $b->pret_restant > 0 ? number_format($b->pret_restant, 0, ',', ' ') : '-' }}
            </td>
            <td>
                <span class="badge {{ $b->statut === 'payé' ? 'badge-paye' : ($b->statut === 'validé' ? 'badge-valide' : 'badge-brouillon') }}">
                    {{ $b->statut }}
                </span>
            </td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4">TOTAUX — {{ $bulletins->count() }} bulletins</td>
            <td class="text-end">{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('montant_heures_sup'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('montant_retard'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('montant_absence'), 0, ',', ' ') }}</td>
            {{-- TOTAUX CNPS COMMENTÉS --}}
            {{-- <td class="text-end red">{{ number_format($bulletins->sum('cnps_salariale'), 0, ',', ' ') }}</td> --}}
            {{-- <td class="text-end purple">{{ number_format($bulletins->sum('cnps_patronale'), 0, ',', ' ') }}</td> --}}
            {{-- <td class="text-end blue fw-bold">{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</td> --}}
            <td class="text-end">{{ number_format($bulletins->sum('montant_sanction'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('acompte'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('pret'), 0, ',', ' ') }}</td>
            <td class="text-end" style="color:#16a34a;font-size:10px;font-weight:800;">{{ number_format($bulletins->sum('net_a_payer'), 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($bulletins->sum('pret_restant'), 0, ',', ' ') }}</td>
            <td>—</td>
        </tr>
    </tfoot>
</table>

{{-- RÉCAPITULATIF CNPS COMMENTÉ --}}
{{-- <div style="margin-top:12px;border-top:1px solid #e2e8f0;padding-top:8px;display:flex;justify-content:space-between;font-size:8px;color:#94a3b8;">
    <div>
        <span style="font-weight:600;">📊 Récapitulatif CNPS :</span>
        <span style="color:#dc2626;">Salariale {{ number_format($bulletins->sum('cnps_salariale'), 0, ',', ' ') }} FCFA</span>
        <span style="margin-left:8px;color:#7c3aed;">Patronale {{ number_format($bulletins->sum('cnps_patronale'), 0, ',', ' ') }} FCFA</span>
        <span style="margin-left:8px;color:#1d4ed8;font-weight:700;">Total {{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }} FCFA</span>
    </div>
    <div>
        Taux CNPS : Salariale 2.52% · Patronale 4.20% · Total 6.72%
    </div>
</div> --}}

</body>
</html>