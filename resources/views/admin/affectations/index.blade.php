@extends('admin.layout')
@section('content')

<style>
.stat-card {
    background:white; border-radius:14px; padding:20px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06); text-align:center;
    transition:0.2s;
}
.stat-card:hover { transform:translateY(-3px); box-shadow:0 8px 24px rgba(0,0,0,0.1); }
.stat-card .v { font-size:28px; font-weight:900; margin-bottom:4px; }
.stat-card .l { font-size:10px; font-weight:700; text-transform:uppercase; color:#64748b; letter-spacing:0.5px; }
.action-card {
    background:white; border-radius:14px; padding:22px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    border-left:4px solid #1d4ed8;
    display:flex; justify-content:space-between; align-items:center;
    transition:0.2s;
}
.action-card:hover { box-shadow:0 8px 24px rgba(0,0,0,0.1); transform:translateY(-2px); }
.action-card .ac-titre { font-weight:700; font-size:15px; color:#1e3a5f; }
.action-card .ac-desc  { font-size:12px; color:#64748b; margin-top:3px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">🗺️ Blocs & Lots — Affectations</h2>
        <div style="font-size:13px;color:#64748b;">
            Gérez les blocs, les lots et leurs affectations aux dossiers clients
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- KPIs --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #1e3a5f;">
            <div class="v" style="color:#1e3a5f;">{{ $stats['blocs'] }}</div>
            <div class="l">Blocs enregistrés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #1d4ed8;">
            <div class="v" style="color:#1d4ed8;">{{ $stats['lots'] }}</div>
            <div class="l">Lots enregistrés</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #16a34a;">
            <div class="v" style="color:#16a34a;">{{ $stats['disponibles'] }}</div>
            <div class="l">Lots disponibles</div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stat-card" style="border-top:3px solid #dc2626;">
            <div class="v" style="color:#dc2626;">{{ $stats['affectes'] }}</div>
            <div class="l">Lots affectés</div>
        </div>
    </div>
</div>

{{-- BARRE DE PROGRESSION DISPONIBILITÉ --}}
@if($stats['lots'] > 0)
@php
    $pctDispo = round(($stats['disponibles'] / $stats['lots']) * 100);
    $pctAff   = 100 - $pctDispo;
@endphp
<div style="background:white;border-radius:14px;padding:18px 22px;box-shadow:0 2px 10px rgba(0,0,0,0.06);margin-bottom:24px;">
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-bottom:8px;">
        <span>✅ Disponibles : <strong style="color:#16a34a;">{{ $pctDispo }}%</strong></span>
        <span>🔴 Affectés : <strong style="color:#dc2626;">{{ $pctAff }}%</strong></span>
    </div>
    <div style="height:12px;background:#f1f5f9;border-radius:6px;overflow:hidden;">
        <div style="display:flex;height:100%;">
            <div style="width:{{ $pctDispo }}%;background:#16a34a;border-radius:6px 0 0 6px;"></div>
            <div style="width:{{ $pctAff }}%;background:#dc2626;border-radius:0 6px 6px 0;"></div>
        </div>
    </div>
</div>
@endif

{{-- ACTIONS PRINCIPALES --}}
<div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;">
    Actions
</div>

<div class="row g-3 mb-4">

    <div class="col-md-6">
        <div class="action-card" style="border-left-color:#1e3a5f;">
            <div>
                <div class="ac-titre">🗂️ Gestion des Blocs</div>
                <div class="ac-desc">
                    Créer, modifier et supprimer des blocs.
                    Chaque bloc est lié à un Grand Site, un Site et un TF.
                </div>
                <div style="margin-top:10px;font-size:12px;color:#64748b;">
                    <strong style="color:#1e3a5f;">{{ $stats['blocs'] }}</strong> bloc(s) enregistré(s)
                </div>
            </div>
            <a href="{{ route('affectations.blocs') }}"
               class="btn btn-primary" style="white-space:nowrap;margin-left:16px;">
                Gérer les blocs →
            </a>
        </div>
    </div>

    <div class="col-md-6">
        <div class="action-card" style="border-left-color:#1d4ed8;">
            <div>
                <div class="ac-titre">📦 Gestion des Lots</div>
                <div class="ac-desc">
                    Créer, modifier et supprimer des lots dans les blocs.
                    Visualisez la disponibilité de chaque lot.
                </div>
                <div style="margin-top:10px;font-size:12px;color:#64748b;">
                    <strong style="color:#16a34a;">{{ $stats['disponibles'] }}</strong> disponible(s)
                    sur <strong style="color:#1d4ed8;">{{ $stats['lots'] }}</strong> total
                </div>
            </div>
            <a href="{{ route('affectations.lots') }}"
               class="btn btn-primary" style="white-space:nowrap;margin-left:16px;">
                Gérer les lots →
            </a>
        </div>
    </div>

</div>

{{-- INFO AFFECTATIONS --}}
<div style="background:#eff6ff;border-radius:14px;padding:18px 22px;border:1px solid #bfdbfe;">
    <div style="font-weight:700;color:#1d4ed8;font-size:14px;margin-bottom:8px;">
        ℹ️ Comment affecter un lot à un dossier client ?
    </div>
    <ol style="font-size:13px;color:#374151;margin:0;padding-left:20px;line-height:2;">
        <li>Allez dans <strong>Suivi clients</strong> → ouvrez la fiche d'un client</li>
        <li>Ouvrez un dossier</li>
        <li>Dans la section <strong>"Affecter des lots"</strong>, choisissez le Grand Site, Site, TF et Bloc</li>
        <li>Cochez les lots à affecter et validez</li>
    </ol>
    <div style="margin-top:12px;">
        <a href="{{ route('suivi-client.index') }}" class="btn btn-outline-primary btn-sm">
            👤 Aller au suivi clients →
        </a>
    </div>
</div>

{{-- FILTRE PAR GRAND SITE --}}
@if($grandSites->count() > 0)
<div style="margin-top:24px;">
    <div style="font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;margin-bottom:12px;">
        Accès rapide par grand site
    </div>
    <div style="display:flex;flex-wrap:wrap;gap:8px;">
        @foreach($grandSites as $gs)
        <a href="{{ route('affectations.blocs', ['grand_site_id' => $gs->id]) }}"
           style="display:inline-flex;align-items:center;gap:6px;background:white;border:1px solid #e2e8f0;border-radius:10px;padding:8px 16px;font-size:13px;font-weight:600;color:#1e3a5f;text-decoration:none;transition:0.2s;"
           onmouseover="this.style.background='#eff6ff';this.style.borderColor='#1d4ed8'"
           onmouseout="this.style.background='white';this.style.borderColor='#e2e8f0'">
            🏢 {{ $gs->nom }}
        </a>
        @endforeach
    </div>
</div>
@endif

@endsection