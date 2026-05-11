@extends('rh.layout')
@section('content')

<style>
.kpi-card { background:white; border-radius:14px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,0.06); height:100%; }
.kpi-card .val { font-size:28px; font-weight:800; }
.kpi-card .lbl { font-size:12px; color:#64748b; font-weight:600; text-transform:uppercase; }
.kpi-card .icon { font-size:32px; opacity:0.15; position:absolute; right:16px; top:16px; }
.section-card { background:white; border-radius:14px; padding:20px; box-shadow:0 2px 12px rgba(0,0,0,0.06); margin-bottom:20px; }
.section-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
.dir-bar { height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden; }
.dir-fill { height:100%; border-radius:4px; background:linear-gradient(90deg,#1d4ed8,#7c3aed); }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f; font-weight:800;">📊 Dashboard RH</h2>
    <span style="font-size:13px; color:#64748b;">{{ now()->translatedFormat('l d F Y') }}</span>
</div>

{{-- LIGNE 1 — EFFECTIFS --}}
<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:4px solid #1d4ed8; position:relative;">
            <div class="icon">👥</div>
            <div class="val" style="color:#1d4ed8;">{{ $totalEmployes }}</div>
            <div class="lbl">Employés actifs</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:4px solid #0891b2; position:relative;">
            <div class="icon">👨</div>
            <div class="val" style="color:#0891b2;">{{ $totalHommes }}</div>
            <div class="lbl">Hommes</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:4px solid #db2777; position:relative;">
            <div class="icon">👩</div>
            <div class="val" style="color:#db2777;">{{ $totalFemmes }}</div>
            <div class="lbl">Femmes</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:4px solid #7c3aed; position:relative;">
            <div class="icon">📋</div>
            <div class="val" style="color:#7c3aed;">{{ $totalCDI }}</div>
            <div class="lbl">CDI</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:4px solid #16a34a; position:relative;">
            <div class="icon">🆕</div>
            <div class="val" style="color:#16a34a;">{{ $nouveauxCeMois }}</div>
            <div class="lbl">Entrées ce mois</div>
        </div>
    </div>
    <div class="col-md-2">
        <div class="kpi-card" style="border-top:4px solid #dc2626; position:relative;">
            <div class="icon">🚪</div>
            <div class="val" style="color:#dc2626;">{{ $departs }}</div>
            <div class="lbl">Départs ce mois</div>
        </div>
    </div>
</div>

{{-- LIGNE 2 — PAIE & ALERTES --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:4px solid #0d6efd; position:relative;">
            <div class="icon">💰</div>
            <div class="val" style="color:#0d6efd; font-size:20px;">{{ number_format($masseSalariale, 0, ',', ' ') }}</div>
            <div class="lbl">Masse salariale nette (FCFA)</div>
            <div style="font-size:11px; color:#94a3b8; margin-top:4px;">{{ $bulletinsValides }}/{{ $bulletinsTotal }} bulletins validés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:4px solid #f59e0b; position:relative;">
            <div class="icon">🗓️</div>
            <div class="val" style="color:#f59e0b;">{{ $absencesMois }}</div>
            <div class="lbl">Absences ce mois</div>
            @if($absencesEnAttente > 0)
                <div style="font-size:11px; background:#fef9c3; color:#92400e; padding:3px 8px; border-radius:6px; margin-top:6px; display:inline-block;">
                    ⚠ {{ $absencesEnAttente }} en attente
                </div>
            @endif
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:4px solid #8b5cf6; position:relative;">
            <div class="icon">🏦</div>
            <div class="val" style="color:#8b5cf6; font-size:20px;">{{ number_format($pretsEnCours, 0, ',', ' ') }}</div>
            <div class="lbl">Prêts en cours (FCFA)</div>
            <div style="font-size:11px; color:#94a3b8; margin-top:4px;">{{ $pretsNb }} dossier(s)</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="kpi-card" style="border-top:4px solid #dc2626; position:relative;">
            <div class="icon">⚠️</div>
            <div class="val" style="color:#dc2626; font-size:20px;">{{ number_format($sanctionsMois, 0, ',', ' ') }}</div>
            <div class="lbl">Sanctions ce mois (FCFA)</div>
            <div style="font-size:11px; color:#94a3b8; margin-top:4px;">Ancienneté moy. : {{ $ancMoyenneAns }} ans</div>
        </div>
    </div>
</div>

<div class="row g-3">
    {{-- Répartition par direction --}}
    <div class="col-md-5">
        <div class="section-card">
            <h5>🏢 Répartition par direction</h5>
            @php $maxEmp = $parDirection->max('employes_count') ?: 1; @endphp
            @foreach($parDirection as $dir)
                @if($dir->employes_count > 0)
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span style="font-size:13px; font-weight:600;">{{ $dir->nom }}</span>
                        <span style="font-size:12px; color:#1d4ed8; font-weight:700;">{{ $dir->employes_count }}</span>
                    </div>
                    <div class="dir-bar">
                        <div class="dir-fill" style="width:{{ ($dir->employes_count / $maxEmp) * 100 }}%;"></div>
                    </div>
                </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Évolution masse salariale --}}
    <div class="col-md-7">
        <div class="section-card">
            <h5>📈 Évolution masse salariale (6 mois)</h5>
            @php $maxPaie = $evolutionPaie->max('montant') ?: 1; @endphp
            <div style="display:flex; align-items:flex-end; gap:8px; height:160px; padding-bottom:4px;">
                @foreach($evolutionPaie as $p)
                    @php $h = ($p['montant'] / $maxPaie) * 150; @endphp
                    <div style="flex:1; display:flex; flex-direction:column; align-items:center; gap:4px;">
                        <div style="font-size:9px; color:#64748b;">{{ number_format($p['montant']/1000000, 1) }}M</div>
                        <div style="height:{{ max(4, $h) }}px; width:100%;
                                    background:linear-gradient(to top,#1d4ed8,#7c3aed);
                                    border-radius:4px 4px 0 0; transition:0.3s;"></div>
                        <div style="font-size:9px; color:#64748b; text-align:center;">{{ $p['mois'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- Actions rapides --}}
<div class="section-card mt-3">
    <h5>⚡ Actions rapides</h5>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('rh.employes.create') }}" class="btn btn-primary btn-sm">👤 Nouvel employé</a>
        <a href="{{ route('rh.paie.create') }}" class="btn btn-success btn-sm">💰 Nouveau bulletin</a>
        <a href="{{ route('rh.absences.index') }}" class="btn btn-warning btn-sm">🗓️ Gérer absences</a>
        <a href="{{ route('rh.prets.index') }}" class="btn btn-info btn-sm text-white">🏦 Prêts & acomptes</a>
        <a href="{{ route('rh.sanctions.index') }}" class="btn btn-danger btn-sm">⚠️ Sanctions</a>
        <a href="{{ route('rh.paie.recapitulatif') }}" class="btn btn-outline-primary btn-sm">📊 Récapitulatif paie</a>
    </div>
</div>

@endsection