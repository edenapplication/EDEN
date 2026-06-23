@extends('rh.layout')
@section('content')

<style>
.paie-table { width:100%; border-collapse:collapse; font-size:12px; background:white; }
.paie-table thead tr { background:#1e3a5f; color:white; }
.paie-table thead th { padding:10px 8px; font-weight:600; text-align:left; white-space:nowrap; }
.paie-table tbody tr:nth-child(even) { background:#f8fafc; }
.paie-table tbody tr:hover { background:#eff6ff; }
.paie-table tbody td { padding:8px; border-bottom:1px solid #e2e8f0; white-space:nowrap; }
.paie-table tfoot td { background:#1e3a5f; color:white; font-weight:700; padding:8px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">💰 Bulletins de paie</h2>
    <div class="d-flex gap-2">
        <a href="{{ route('rh.paie.create') }}" class="btn btn-primary btn-sm">+ Nouveau bulletin</a>
        <a href="{{ route('rh.paie.pdf-liste', [
    'periode' => $periode,
    'vague'   => request('vague')
]) }}"
class="btn btn-danger btn-sm">
    📄 Télécharger PDF
</a>
    </div>
</div>

{{-- FILTRES + GÉNÉRATION MASSE --}}
<div style="background:white;border-radius:12px;padding:16px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:16px;">
    <div class="row g-2 align-items-end">
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

        {{-- Génération en masse --}}
        <div class="col-md-5">
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
                    ⚡ Générer masse
                </button>
            </form>
        </div>
    </div>
</div>

{{-- KPIs PÉRIODE --}}
<div class="row g-3 mb-3">
    <div class="col-md-3">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #1d4ed8;">
            <div style="font-size:20px;font-weight:800;color:#1d4ed8;">{{ number_format($totalNet, 0, ',', ' ') }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;">MASSE SALARIALE NETTE (FCFA)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #7c3aed;">
            <div style="font-size:20px;font-weight:800;color:#7c3aed;">{{ number_format($totalBrut, 0, ',', ' ') }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;">MASSE SALARIALE BRUTE (FCFA)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #dc2626;">
            <div style="font-size:20px;font-weight:800;color:#dc2626;">{{ number_format($totalSanction, 0, ',', ' ') }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;">TOTAL SANCTIONS (FCFA)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div style="background:white;border-radius:10px;padding:14px;box-shadow:0 2px 8px rgba(0,0,0,0.06);border-top:3px solid #f59e0b;">
            <div style="font-size:20px;font-weight:800;color:#f59e0b;">{{ number_format($totalAcompte, 0, ',', ' ') }}</div>
            <div style="font-size:10px;color:#64748b;font-weight:600;">TOTAL ACOMPTES (FCFA)</div>
        </div>
    </div>
</div>

{{-- TABLEAU --}}
<div style="overflow-x:auto;border-radius:12px;box-shadow:0 2px 10px rgba(0,0,0,0.06);">
<table class="paie-table">
    <thead>
        <tr>
            <th>Matricule</th>
            <th>Employé</th>
            <th>Direction</th>
            <th>Vague</th>
            <th>Salaire brut</th>
            <th>Hres sup.</th>
            <th>Retards</th>
            <th>Absences</th>
            <th>Sanction</th>
            <th>Acompte</th>
            <th>CNPS</th>
            <th>Net à payer</th>
            <th>Statut</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    @forelse($bulletins as $b)
        <tr>
            <td style="color:#1d4ed8;font-weight:700;">{{ $b->employe?->matricule }}</td>
            <td><a href="{{ route('rh.employes.show', $b->employe_id) }}" style="font-weight:600;color:#1e3a5f;text-decoration:none;">{{ $b->employe?->nom }} {{ $b->employe?->prenom }}</a></td>
            <td>{{ $b->employe?->direction?->nom ?? '-' }}</td>
            <td>
                <span style="font-size:10px;padding:2px 8px;border-radius:8px;background:{{ $b->vague==='VAGUE 1'?'#dbeafe':'#f3e8ff'}};color:{{ $b->vague==='VAGUE 1'?'#1d4ed8':'#7c3aed'}};font-weight:600;">
                    {{ $b->vague }}
                </span>
            </td>
            <td>{{ number_format($b->salaire_brut, 0, ',', ' ') }}</td>
            <td>{{ $b->nb_heures_sup > 0 ? $b->nb_heures_sup.'h — '.number_format($b->montant_heures_sup, 0, ',', ' ') : '-' }}</td>
            <td style="color:#f59e0b;font-weight:600;">{{ $b->montant_retard > 0 ? number_format($b->montant_retard, 0, ',', ' ') : '-' }}</td>
            <td style="color:#f59e0b;font-weight:600;">{{ $b->montant_absence > 0 ? number_format($b->montant_absence, 0, ',', ' ') : '-' }}</td>
            <td style="color:#dc2626;font-weight:600;">{{ $b->montant_sanction > 0 ? number_format($b->montant_sanction, 0, ',', ' ') : '-' }}</td>
            <td style="color:#7c3aed;font-weight:600;">{{ $b->acompte > 0 ? number_format($b->acompte, 0, ',', ' ') : '-' }}</td>
            <td>{{ $b->cnps > 0 ? number_format($b->cnps, 0, ',', ' ') : '-' }}</td>
            <td style="font-weight:800;color:#1d4ed8;font-size:13px;">{{ number_format($b->net_a_payer, 0, ',', ' ') }}</td>
            <td>
                <span style="font-size:10px;padding:2px 8px;border-radius:8px;font-weight:600;background:{{ $b->statut==='payé'?'#dcfce7':($b->statut==='validé'?'#fef3c7':'#f1f5f9')}};color:{{ $b->statut==='payé'?'#15803d':($b->statut==='validé'?'#92400e':'#475569')}};">
                    {{ $b->statut }}
                </span>
            </td>
            <td>
                <div class="d-flex gap-1">
                    <a href="{{ route('rh.paie.pdf', $b->id) }}" class="btn btn-sm btn-danger" style="font-size:10px;padding:2px 6px;" title="PDF">📄</a>
                    <form action="{{ route('rh.paie.valider', $b->id) }}" method="POST" style="display:inline">
                        @csrf
                        <button class="btn btn-sm btn-success" style="font-size:10px;padding:2px 6px;" title="Valider">✅</button>
                    </form>
                    <form action="{{ route('rh.paie.destroy', $b->id) }}" method="POST" style="display:inline" onsubmit="return confirm('Supprimer ?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger" style="font-size:10px;padding:2px 6px;" title="Supprimer">🗑</button>
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
        <tr>
            <td colspan="4">TOTAUX ({{ $bulletins->count() }} employés)</td>
            <td>{{ number_format($totalBrut, 0, ',', ' ') }}</td>
            <td colspan="4">—</td>
            <td>{{ number_format($totalAcompte, 0, ',', ' ') }}</td>
            <td>—</td>
            <td>{{ number_format($totalNet, 0, ',', ' ') }}</td>
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