@extends('rh.layout')
@section('content')

<style>
.recap-card { background:white; border-radius:12px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
.recap-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
.recap-table { width:100%; border-collapse:collapse; font-size:12px; }
.recap-table thead tr { background:#1e3a5f; color:white; }
.recap-table thead th { padding:10px 8px; font-weight:600; text-align:left; white-space:nowrap; }
.recap-table tbody tr:nth-child(even) { background:#f8fafc; }
.recap-table tbody tr:hover { background:#eff6ff; }
.recap-table tbody td { padding:8px; border-bottom:1px solid #e2e8f0; }
.recap-table tfoot td { background:#1e3a5f; color:white; font-weight:700; padding:10px 8px; }
.kpi { background:white; border-radius:10px; padding:14px; box-shadow:0 2px 8px rgba(0,0,0,0.06); text-align:center; }
.kpi .val { font-size:18px; font-weight:800; }
.kpi .lbl { font-size:10px; color:#64748b; font-weight:600; text-transform:uppercase; margin-top:2px; }
.kpi .sub { font-size:11px; color:#94a3b8; margin-top:4px; }

.badge-cnps { padding:2px 8px; border-radius:4px; font-size:9px; font-weight:600; }
.badge-cnps.salariale { background:#fee2e2; color:#dc2626; }
.badge-cnps.patronale { background:#ede9fe; color:#7c3aed; }
.badge-cnps.total { background:#dbeafe; color:#1d4ed8; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">📊 Récapitulatif de paie</h2>
    <div class="d-flex gap-2 align-items-center">
        <form method="GET" class="d-flex gap-2">
            <input type="month" name="periode" class="form-control form-control-sm"
                   value="{{ $periode }}" onchange="this.form.submit()">
        </form>
        <select name="vague" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="">Toutes les vagues</option>
            <option value="VAGUE 1" {{ request('vague')=='VAGUE 1' ? 'selected' : '' }}>VAGUE 1</option>
            <option value="VAGUE 2" {{ request('vague')=='VAGUE 2' ? 'selected' : '' }}>VAGUE 2</option>
        </select>
        <a href="{{ route('rh.paie.recapitulatif', [
            'periode' => $periode,
            'vague'   => request('vague'),
            'pdf'     => 1
        ]) }}" class="btn btn-outline-danger btn-sm">
            <i class="bi bi-file-pdf"></i> PDF
        </a>
    </div>
</div>

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi" style="border-top:3px solid #1d4ed8;">
            <div class="val" style="color:#1d4ed8;">{{ $bulletins->count() }}</div>
            <div class="lbl">Bulletins générés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi" style="border-top:3px solid #7c3aed;">
            <div class="val" style="color:#7c3aed; font-size:14px;">
                {{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}
            </div>
            <div class="lbl">Masse brute (FCFA)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi" style="border-top:3px solid #16a34a;">
            <div class="val" style="color:#16a34a; font-size:14px;">
                {{ number_format($bulletins->sum('net_a_payer'), 0, ',', ' ') }}
            </div>
            <div class="lbl">Masse nette (FCFA)</div>
        </div>
    </div>
    {{-- KPI CNPS COMMENTÉ --}}
    {{-- <div class="col-md-3">
        <div class="kpi" style="border-top:3px solid #f59e0b;">
            <div class="val" style="color:#f59e0b; font-size:14px;">
                {{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}
            </div>
            <div class="lbl">Total CNPS (FCFA)</div>
            <div class="sub">
                <span class="badge-cnps salariale">Sal. {{ number_format($bulletins->sum('cnps_salariale'), 0, ',', ' ') }}</span>
                <span class="badge-cnps patronale">Pat. {{ number_format($bulletins->sum('cnps_patronale'), 0, ',', ' ') }}</span>
            </div>
        </div>
    </div> --}}
</div>

{{-- PAR DIRECTION --}}
<div class="recap-card">
    <h5>🏢 Récapitulatif par direction — {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y') }}</h5>
    <div style="overflow-x:auto;">
    <table class="recap-table">
        <thead>
            <tr>
                <th>Direction</th>
                <th>Nb</th>
                <th>Brut</th>
                <th>HS</th>
                {{-- COLONNES CNPS COMMENTÉES --}}
                {{-- <th>CNPS Sal.</th> --}}
                {{-- <th>CNPS Pat.</th> --}}
                {{-- <th>Total CNPS</th> --}}
                <th>Sanctions</th>
                <th>Acomptes</th>
                <th>Net</th>
            </tr>
        </thead>
        <tbody>
        @foreach($parDirection as $dir => $data)
            <tr>
                <td style="font-weight:600;">{{ $dir ?? 'Non défini' }}</td>
                <td>{{ $data['nb'] }}</td>
                <td>{{ number_format($data['brut'], 0, ',', ' ') }}</td>
                <td>{{ number_format($data['hs'], 0, ',', ' ') }}</td>
                {{-- DONNÉES CNPS COMMENTÉES --}}
                {{-- <td style="color:#dc2626;">{{ number_format($data['cnps_salariale'] ?? 0, 0, ',', ' ') }}</td> --}}
                {{-- <td style="color:#7c3aed;">{{ number_format($data['cnps_patronale'] ?? 0, 0, ',', ' ') }}</td> --}}
                {{-- <td style="color:#1d4ed8;font-weight:700;">{{ number_format($data['cnps'] ?? 0, 0, ',', ' ') }}</td> --}}
                <td style="color:#dc2626;">{{ number_format($data['sanction'], 0, ',', ' ') }}</td>
                <td style="color:#7c3aed;">{{ number_format($data['acomptes'] ?? 0, 0, ',', ' ') }}</td>
                <td style="font-weight:700; color:#16a34a;">{{ number_format($data['net'], 0, ',', ' ') }}</td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td>TOTAL</td>
                <td>{{ $bulletins->count() }}</td>
                <td>{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('montant_heures_sup'), 0, ',', ' ') }}</td>
                {{-- TOTAUX CNPS COMMENTÉS --}}
                {{-- <td style="color:#dc2626;">{{ number_format($bulletins->sum('cnps_salariale'), 0, ',', ' ') }}</td> --}}
                {{-- <td style="color:#7c3aed;">{{ number_format($bulletins->sum('cnps_patronale'), 0, ',', ' ') }}</td> --}}
                {{-- <td style="color:#1d4ed8;font-weight:700;">{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</td> --}}
                <td>{{ number_format($bulletins->sum('montant_sanction'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('acompte'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('net_a_payer'), 0, ',', ' ') }}</td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>

{{-- DÉTAIL PAR EMPLOYÉ --}}
<div class="recap-card">
    <h5>👤 Détail par employé</h5>
    <div style="overflow-x:auto;">
    <table class="recap-table">
        <thead>
            <tr>
                <th>Matricule</th>
                <th>Nom</th>
                <th>Direction</th>
                <th>Vague</th>
                <th>Brut</th>
                <th>HS</th>
                {{-- COLONNES CNPS COMMENTÉES --}}
                {{-- <th>CNPS Sal.</th> --}}
                {{-- <th>CNPS Pat.</th> --}}
                {{-- <th>Total CNPS</th> --}}
                <th>Retards</th>
                <th>Absences</th>
                <th>Sanction</th>
                <th>Acompte</th>
                <th>Prêt</th>
                <th>Net</th>
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
                    <span style="font-size:10px;padding:2px 8px;border-radius:8px;font-weight:600;background:{{ $b->vague==='VAGUE 1'?'#dbeafe':'#f3e8ff'}};color:{{ $b->vague==='VAGUE 1'?'#1d4ed8':'#7c3aed'}};">
                        {{ $b->vague }}
                    </span>
                </td>
                <td>{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
                <td>{{ $b->montant_heures_sup > 0 ? number_format($b->montant_heures_sup, 0, ',', ' ') : '-' }}</td>
                {{-- DONNÉES CNPS COMMENTÉES --}}
                {{-- <td style="color:#dc2626;">{{ $b->cnps_salariale > 0 ? number_format($b->cnps_salariale, 0, ',', ' ') : '-' }}</td> --}}
                {{-- <td style="color:#7c3aed;">{{ $b->cnps_patronale > 0 ? number_format($b->cnps_patronale, 0, ',', ' ') : '-' }}</td> --}}
                {{-- <td style="color:#1d4ed8;font-weight:700;">{{ $b->cnps > 0 ? number_format($b->cnps, 0, ',', ' ') : '-' }}</td> --}}
                <td style="color:#f59e0b;">{{ $b->montant_retard > 0 ? number_format($b->montant_retard, 0, ',', ' ') : '-' }}</td>
                <td style="color:#f59e0b;">{{ $b->montant_absence > 0 ? number_format($b->montant_absence, 0, ',', ' ') : '-' }}</td>
                <td style="color:#dc2626;">{{ $b->montant_sanction > 0 ? number_format($b->montant_sanction, 0, ',', ' ') : '-' }}</td>
                <td style="color:#7c3aed;">{{ $b->acompte > 0 ? number_format($b->acompte, 0, ',', ' ') : '-' }}</td>
                <td>{{ $b->pret > 0 ? number_format($b->pret, 0, ',', ' ') : '-' }}</td>
                <td style="font-weight:800;color:#16a34a;font-size:13px;">{{ number_format($b->net_a_payer, 0, ',', ' ') }}</td>
                <td>
                    <span style="font-size:10px;padding:2px 8px;border-radius:8px;font-weight:600;background:{{ $b->statut==='payé'?'#dcfce7':($b->statut==='validé'?'#fef3c7':'#f1f5f9')}};color:{{ $b->statut==='payé'?'#15803d':($b->statut==='validé'?'#92400e':'#475569')}};">
                        {{ $b->statut }}
                    </span>
                </td>
            </tr>
        @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4">TOTAUX</td>
                <td>{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('montant_heures_sup'), 0, ',', ' ') }}</td>
                {{-- TOTAUX CNPS COMMENTÉS --}}
                {{-- <td style="color:#dc2626;">{{ number_format($bulletins->sum('cnps_salariale'), 0, ',', ' ') }}</td> --}}
                {{-- <td style="color:#7c3aed;">{{ number_format($bulletins->sum('cnps_patronale'), 0, ',', ' ') }}</td> --}}
                {{-- <td style="color:#1d4ed8;font-weight:700;">{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</td> --}}
                <td>{{ number_format($bulletins->sum('montant_retard'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('montant_absence'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('montant_sanction'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('acompte'), 0, ',', ' ') }}</td>
                <td>{{ number_format($bulletins->sum('pret'), 0, ',', ' ') }}</td>
                <td style="color:#16a34a;font-size:14px;font-weight:800;">{{ number_format($bulletins->sum('net_a_payer'), 0, ',', ' ') }}</td>
                <td>—</td>
            </tr>
        </tfoot>
    </table>
    </div>
</div>

{{-- RÉCAPITULATIF GLOBAL CNPS COMMENTÉ --}}
{{-- <div class="recap-card">
    <h5>🏛️ Récapitulatif CNPS</h5>
    <div class="row g-3">
        <div class="col-md-3">
            <div class="kpi" style="border-top:3px solid #1d4ed8;">
                <div class="val" style="color:#1d4ed8;font-size:16px;">{{ number_format($bulletins->sum('base_cnps'), 0, ',', ' ') }}</div>
                <div class="lbl">Base de calcul CNPS</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi" style="border-top:3px solid #dc2626;">
                <div class="val" style="color:#dc2626;font-size:16px;">{{ number_format($bulletins->sum('cnps_salariale'), 0, ',', ' ') }}</div>
                <div class="lbl">Cotisation salariale (2.52%)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi" style="border-top:3px solid #7c3aed;">
                <div class="val" style="color:#7c3aed;font-size:16px;">{{ number_format($bulletins->sum('cnps_patronale'), 0, ',', ' ') }}</div>
                <div class="lbl">Cotisation patronale (4.20%)</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="kpi" style="border-top:3px solid #f59e0b;">
                <div class="val" style="color:#f59e0b;font-size:20px;">{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</div>
                <div class="lbl">Total CNPS (6.72%)</div>
            </div>
        </div>
    </div>
    <div style="margin-top:12px;padding:12px;background:#fef9c3;border-radius:8px;border:1px solid #fcd34d;text-align:center;font-size:12px;color:#92400e;">
        <span style="font-weight:700;">📊 Synthèse CNPS :</span>
        Masse salariale brute <strong>{{ number_format($bulletins->sum('salaire_brut'), 0, ',', ' ') }}</strong> FCFA × 6.72% = 
        <strong style="font-size:14px;color:#1d4ed8;">{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</strong> FCFA
        <span style="margin-left:12px;font-size:11px;color:#94a3b8;">
            (Salariale {{ number_format($bulletins->sum('cnps_salariale'), 0, ',', ' ') }} FCFA + Patronale {{ number_format($bulletins->sum('cnps_patronale'), 0, ',', ' ') }} FCFA)
        </span>
    </div>
</div> --}}

@endsection