@extends('admin.layout')
@section('content')

<style>
.feb-kpi { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,0.06); text-align:center; }
.feb-kpi .v { font-size:26px; font-weight:800; }
.feb-kpi .l { font-size:10px; color:#64748b; font-weight:700; text-transform:uppercase; margin-top:2px; }
.fiche-nouvelle { background:white; border-radius:12px; padding:14px 16px; margin-bottom:8px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #dc2626; animation:pulseNew 2s infinite; }
.fiche-normale  { background:white; border-radius:12px; padding:14px 16px; margin-bottom:8px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #16a34a; }
@keyframes pulseNew { 0%,100%{border-left-color:#dc2626} 50%{border-left-color:#f87171} }
.badge-new { background:#fee2e2; color:#b91c1c; font-size:10px; padding:3px 10px; border-radius:20px; font-weight:700; animation:bgPulse 1.5s infinite; }
@keyframes bgPulse { 0%,100%{background:#fee2e2} 50%{background:#fca5a5} }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;">📋 Module FEB — Fiches d'Expression</h2>
        <div style="font-size:13px;color:#64748b;">Gestion des fiches d'expression des besoins</div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.feb.fiches.index') }}" class="btn btn-primary btn-sm">📋 Toutes les fiches</a>
        <a href="{{ route('admin.feb.agences.index') }}" class="btn btn-outline-secondary btn-sm">🏢 Agences</a>
        <a href="{{ route('admin.feb.colonnes.index') }}" class="btn btn-outline-secondary btn-sm">📊 Colonnes</a>
        <a href="{{ route('admin.feb.utilisateurs.index') }}" class="btn btn-outline-secondary btn-sm">👤 Utilisateurs</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="feb-kpi" style="border-top:3px solid #dc2626;">
            <div class="v" style="color:#dc2626;">{{ $stats['fiches_new'] }}</div>
            <div class="l">🔴 Nouvelles non vues</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="feb-kpi" style="border-top:3px solid #1d4ed8;">
            {{-- ✅ Uniquement les soumises --}}
            <div class="v" style="color:#1d4ed8;">{{ $stats['fiches_total'] }}</div>
            <div class="l">Fiches soumises</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="feb-kpi" style="border-top:3px solid #16a34a;">
            <div class="v" style="color:#16a34a;">{{ $stats['utilisateurs'] }}</div>
            <div class="l">Utilisateurs actifs</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="feb-kpi" style="border-top:3px solid #f59e0b;">
            <div class="v" style="color:#f59e0b;">{{ $stats['agences'] }}</div>
            <div class="l">Agences</div>
        </div>
    </div>
</div>

{{-- ✅ NOUVELLES FICHES — attirent l'attention --}}
@if($nouvelles->count())
<div style="background:#fff1f2;border:2px solid #fca5a5;border-radius:14px;padding:16px;margin-bottom:24px;">
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px;">
        <div style="font-size:22px;">🔴</div>
        <div>
            <div style="font-weight:800;font-size:15px;color:#b91c1c;">{{ $nouvelles->count() }} fiche(s) nouvelle(s) non lue(s)</div>
            <div style="font-size:12px;color:#64748b;">Ces fiches n'ont pas encore été consultées</div>
        </div>
        <span class="badge-new ms-auto">NOUVEAU</span>
    </div>
    @foreach($nouvelles as $f)
    <div class="fiche-nouvelle">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div style="font-weight:700;font-size:14px;color:#1e3a5f;">{{ $f->titre }}</div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">
                    👤 {{ $f->utilisateur?->nom_complet }} — {{ $f->utilisateur?->agence?->nom ?? '-' }}
                    &nbsp;·&nbsp; 📅 {{ $f->soumise_at?->format('d/m/Y à H:i') }}
                </div>
            </div>
            <a href="{{ route('admin.feb.fiches.show', $f->id) }}" class="btn btn-danger btn-sm">
                👁 Consulter
            </a>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- Fiches récentes --}}
<div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:10px;">📋 Fiches récentes</div>
@foreach($recentes as $f)
<div class="fiche-normale">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <div style="font-weight:700;font-size:13px;color:#1e3a5f;">{{ $f->titre }}</div>
            <div style="font-size:11px;color:#64748b;margin-top:2px;">
                👤 {{ $f->utilisateur?->nom_complet }} — {{ $f->utilisateur?->agence?->nom ?? '-' }}
                &nbsp;·&nbsp; {{ $f->soumise_at?->format('d/m/Y') }}
                @if($f->vue_admin)
                    &nbsp;<span style="color:#16a34a;font-size:10px;">✅ Vue</span>
                @endif
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.feb.fiches.show', $f->id) }}" class="btn btn-primary btn-sm" style="font-size:11px;">👁 Voir</a>
            <a href="{{ route('admin.feb.fiches.pdf', $f->id) }}" class="btn btn-outline-danger btn-sm" style="font-size:11px;">🖨️</a>
        </div>
    </div>
</div>
@endforeach

@endsection