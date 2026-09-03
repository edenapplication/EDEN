@extends('rh.layout')
@section('content')

<style>
.paie-card { background:white; border-radius:14px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
.paie-card .stat-value { font-size:24px; font-weight:800; }
.paie-card .stat-label { font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; }

.badge-status {
    padding:2px 10px;
    border-radius:10px;
    font-size:10px;
    font-weight:600;
    display:inline-block;
}
.badge-status.paye { background:#dcfce7; color:#15803d; }
.badge-status.valide { background:#dbeafe; color:#1d4ed8; }
.badge-status.brouillon { background:#f1f5f9; color:#475569; }

.filter-bar { background:white; border-radius:12px; padding:14px 18px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:16px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">💰 Bulletins de paie</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.paie.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nouveau bulletin
        </a>
        <a href="{{ route('rh.paie.pdf-liste', ['periode' => $periode, 'vague' => request('vague')]) }}" 
           class="btn btn-danger">
            <i class="bi bi-file-pdf"></i> PDF
        </a>
        <a href="{{ route('rh.paie.recapitulatif', ['periode' => $periode]) }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-bar-chart-fill"></i> Récapitulatif
        </a>
    </div>
</div>

{{-- FILTRES & GÉNÉRATION MASSE --}}
<div class="filter-bar">
    <div class="row g-3 align-items-end">
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Période</label>
            <input type="month" name="periode" id="periodeFilter" class="form-control form-control-sm"
                   value="{{ $periode }}" onchange="filtrer()">
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;">Vague</label>
            <select name="vague" id="vagueFilter" class="form-control form-control-sm" onchange="filtrer()">
                <option value="">Toutes</option>
                <option value="VAGUE 1" {{ $vague==='VAGUE 1'?'selected':'' }}>VAGUE 1</option>
                <option value="VAGUE 2" {{ $vague==='VAGUE 2'?'selected':'' }}>VAGUE 2</option>
            </select>
        </div>
        <div class="col-md-4">
            <form method="POST" action="{{ route('rh.paie.generer') }}" class="d-flex gap-2 align-items-end">
                @csrf
                <div>
                    <label style="font-size:11px;font-weight:600;color:#64748b;">Générer pour</label>
                    <select name="vague" class="form-control form-control-sm">
                        <option value="VAGUE 1">VAGUE 1</option>
                        <option value="VAGUE 2">VAGUE 2</option>
                    </select>
                </div>
                <input type="hidden" name="periode" value="{{ $periode }}">
                <input type="date" name="date_paiement" class="form-control form-control-sm" value="{{ now()->format('Y-m-d') }}">
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Générer les bulletins en masse ?')">
                    <i class="bi bi-lightning-fill"></i> Générer masse
                </button>
            </form>
        </div>
        <div class="col-md-3 text-end">
            <a href="{{ route('rh.paie.recapitulatif', ['periode' => $periode, 'vague' => request('vague')]) }}" 
               class="btn btn-outline-primary btn-sm">
                <i class="bi bi-bar-chart-fill"></i> Récapitulatif
            </a>
        </div>
    </div>
</div>

{{-- KPIs PÉRIODE --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="paie-card" style="border-top:4px solid #1d4ed8;">
            <div class="stat-value" style="color:#1d4ed8;">{{ number_format($totalNet, 0, ',', ' ') }}</div>
            <div class="stat-label">Masse salariale nette</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="paie-card" style="border-top:4px solid #7c3aed;">
            <div class="stat-value" style="color:#7c3aed;">{{ number_format($totalBrut, 0, ',', ' ') }}</div>
            <div class="stat-label">Masse salariale brute</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="paie-card" style="border-top:4px solid #dc2626;">
            <div class="stat-value" style="color:#dc2626;">{{ number_format($totalSanction, 0, ',', ' ') }}</div>
            <div class="stat-label">Total sanctions</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="paie-card" style="border-top:4px solid #f59e0b;">
            <div class="stat-value" style="color:#f59e0b;">{{ number_format($totalAcompte, 0, ',', ' ') }}</div>
            <div class="stat-label">Total acomptes</div>
        </div>
    </div>
</div>

{{-- TABLEAU --}}
<div style="overflow-x:auto;border-radius:14px;box-shadow:0 2px 12px rgba(0,0,0,0.06);background:white;">
<table class="table table-modern">
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Employé</th>
            <th>Direction</th>
            <th>Vague</th>
            <th class="text-end">Brut</th>
            <th class="text-end">HS</th>
            {{-- CNPS COLUMNS COMMENTED --}}
            {{-- <th class="text-end">CNPS Sal.</th> --}}
            {{-- <th class="text-end">CNPS Pat.</th> --}}
            {{-- <th class="text-end">Total CNPS</th> --}}
            <th class="text-end">Sanction</th>
            <th class="text-end">Acompte</th>
            <th class="text-end">Net</th>
            <th>Statut</th>
            <th style="width:100px;">Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($bulletins as $b)
        <tr>
            <td><span style="font-weight:600;color:#1d4ed8;">{{ $b->employe?->matricule }}</span></td>
            <td>
                <a href="{{ route('rh.employes.show', $b->employe_id) }}" style="font-weight:600;color:#1e293b;text-decoration:none;">
                    {{ $b->employe?->nom }} {{ $b->employe?->prenom }}
                </a>
            </td>
            <td>{{ $b->employe?->direction?->nom ?? '-' }}</td>
            <td>
                <span class="badge-status" style="background:{{ $b->vague==='VAGUE 1'?'#dbeafe':'#f3e8ff'}};color:{{ $b->vague==='VAGUE 1'?'#1d4ed8':'#7c3aed'}};">
                    {{ $b->vague }}
                </span>
            </td>
            <td class="text-end">{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
            <td class="text-end text-success">{{ $b->montant_heures_sup > 0 ? number_format($b->montant_heures_sup, 0, ',', ' ') : '-' }}</td>
            {{-- CNPS DATA COMMENTED --}}
            {{-- <td class="text-end" style="color:#dc2626;">{{ $b->cnps_salariale > 0 ? number_format($b->cnps_salariale, 0, ',', ' ') : '-' }}</td> --}}
            {{-- <td class="text-end" style="color:#7c3aed;">{{ $b->cnps_patronale > 0 ? number_format($b->cnps_patronale, 0, ',', ' ') : '-' }}</td> --}}
            {{-- <td class="text-end" style="font-weight:700;color:#1d4ed8;">{{ $b->cnps > 0 ? number_format($b->cnps, 0, ',', ' ') : '-' }}</td> --}}
            <td class="text-end text-danger">{{ $b->montant_sanction > 0 ? number_format($b->montant_sanction, 0, ',', ' ') : '-' }}</td>
            <td class="text-end text-purple">{{ $b->acompte > 0 ? number_format($b->acompte, 0, ',', ' ') : '-' }}</td>
            <td class="text-end" style="font-weight:700;color:#1d4ed8;font-size:14px;">{{ number_format($b->net_a_payer, 0, ',', ' ') }}</td>
            <td>
                <span class="badge-status {{ $b->statut === 'payé' ? 'paye' : ($b->statut === 'validé' ? 'valide' : 'brouillon') }}">
                    {{ $b->statut }}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('rh.paie.pdf', $b->id) }}" class="btn btn-sm btn-danger" title="PDF"><i class="bi bi-file-pdf"></i></a>
                    <form action="{{ route('rh.paie.valider', $b->id) }}" method="POST" style="display:inline;">
                        @csrf
                        <button class="btn btn-sm btn-success" title="Valider"><i class="bi bi-check-lg"></i></button>
                    </form>
                    <form action="{{ route('rh.paie.destroy', $b->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Supprimer ce bulletin ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" title="Supprimer"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </td>
        </tr>
    @empty
        <tr><td colspan="14" class="text-center text-muted py-4">Aucun bulletin pour cette période</td></tr>
    @endforelse
    </tbody>
    @if($bulletins->count() > 0)
    <tfoot>
        <tr style="background:#f8fafc;font-weight:700;">
            <td colspan="4">TOTAUX ({{ $bulletins->count() }} employés)</td>
            <td class="text-end">{{ number_format($totalBrut, 0, ',', ' ') }}</td>
            <td class="text-end">—</td>
            {{-- CNPS TOTALS COMMENTED --}}
            {{-- <td class="text-end" style="color:#dc2626;">{{ number_format($bulletins->sum('cnps_salariale'), 0, ',', ' ') }}</td> --}}
            {{-- <td class="text-end" style="color:#7c3aed;">{{ number_format($bulletins->sum('cnps_patronale'), 0, ',', ' ') }}</td> --}}
            {{-- <td class="text-end" style="color:#1d4ed8;font-weight:800;">{{ number_format($bulletins->sum('cnps'), 0, ',', ' ') }}</td> --}}
            <td class="text-end">{{ number_format($totalSanction, 0, ',', ' ') }}</td>
            <td class="text-end">{{ number_format($totalAcompte, 0, ',', ' ') }}</td>
            <td class="text-end" style="color:#1d4ed8;font-size:15px;">{{ number_format($totalNet, 0, ',', ' ') }}</td>
            <td colspan="2">—</td>
        </tr>
    </tfoot>
    @endif
</table>
</div>

@endsection

@section('scripts')
<script>
function filtrer() {
    const p = document.getElementById('periodeFilter').value;
    const v = document.getElementById('vagueFilter').value;
    let url = '{{ route("rh.paie.index") }}?periode=' + p;
    if (v) url += '&vague=' + encodeURIComponent(v);
    window.location.href = url;
}
</script>
@endsection