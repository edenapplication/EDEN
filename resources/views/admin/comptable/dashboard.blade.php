@extends('layouts.comptable')

@section('title', 'Accueil Comptable')

@section('styles')
<style>
    .welcome-card {
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        color: white;
        padding: 28px;
        border-radius: 16px;
        margin-bottom: 24px;
        box-shadow: 0 8px 24px rgba(124,58,237,0.25);
    }
    .welcome-card h1 { font-size: 24px; font-weight: 800; margin: 0 0 6px 0; }
    .welcome-card p  { opacity: 0.9; font-size: 14px; margin: 0; }

    .kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
        margin-bottom: 24px;
    }
    .kpi-card {
        background: white;
        border-radius: 14px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        border-left: 5px solid #7c3aed;
        transition: all 0.25s;
    }
    .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }
    .kpi-card.success { border-left-color: #16a34a; }
    .kpi-card.warning { border-left-color: #f59e0b; }
    .kpi-card.danger  { border-left-color: #dc2626; }
    .kpi-card.info    { border-left-color: #0d6efd; }

    .kpi-card .kpi-label {
        font-size: 11px; color: #64748b; text-transform: uppercase;
        font-weight: 700; letter-spacing: 0.5px; margin-bottom: 6px;
    }
    .kpi-card .kpi-value {
        font-size: 28px; font-weight: 900; color: #1e3a5f; line-height: 1;
    }
    .kpi-card .kpi-sub { font-size: 12px; color: #94a3b8; margin-top: 6px; }

    .section-title {
        font-size: 16px; font-weight: 800; color: #1e3a5f;
        margin: 28px 0 14px 0; display: flex; align-items: center; gap: 8px;
    }

    .progress-global {
        background: white; border-radius: 14px; padding: 22px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 24px;
    }
    .progress-global .big-value {
        font-size: 32px; font-weight: 900; color: #7c3aed; text-align: center;
    }
    .progress-global .big-label {
        text-align: center; font-size: 12px; color: #64748b;
        text-transform: uppercase; font-weight: 700; margin-bottom: 12px;
    }
    .progress-bar-big {
        height: 12px; background: #e2e8f0; border-radius: 6px;
        overflow: hidden; margin: 16px 0;
    }
    .progress-bar-big > div {
        height: 100%; background: linear-gradient(90deg, #7c3aed, #a855f7);
        border-radius: 6px; transition: width 0.5s;
    }

    .quick-actions {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 14px; margin-bottom: 24px;
    }
    .quick-action {
        background: white; border-radius: 12px; padding: 18px;
        text-decoration: none; color: #1e3a5f;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        transition: all 0.25s; display: flex; align-items: center; gap: 14px;
        border: 2px solid transparent;
    }
    .quick-action:hover {
        border-color: #7c3aed; transform: translateY(-3px);
        color: #7c3aed; box-shadow: 0 8px 24px rgba(124,58,237,0.15);
    }
    .quick-action .icon {
        font-size: 28px; width: 50px; height: 50px;
        border-radius: 12px; background: #faf5ff;
        display: flex; align-items: center; justify-content: center;
    }
    .quick-action .title { font-weight: 800; font-size: 14px; }
    .quick-action .desc  { font-size: 11px; color: #94a3b8; margin-top: 2px; }

    .table-recent {
        background: white; border-radius: 14px; overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06);
    }
    .table-recent table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .table-recent thead {
        background: #f8fafc; color: #64748b; font-size: 11px;
        text-transform: uppercase; font-weight: 700;
    }
    .table-recent th { padding: 12px 16px; text-align: left; }
    .table-recent td { padding: 12px 16px; border-top: 1px solid #f1f5f9; }
    .table-recent tbody tr:hover { background: #faf5ff; }

    @media (max-width: 768px) {
        .welcome-card { padding: 20px; }
        .welcome-card h1 { font-size: 19px; }
        .kpi-card .kpi-value { font-size: 22px; }
    }
</style>
@endsection

@section('content')

{{-- BIENVENUE --}}
<div class="welcome-card">
    <h1>👋 Bonjour, {{ auth()->user()->name }} !</h1>
    <p>
        Bienvenue dans votre espace <strong>comptable logistique</strong>. Voici un résumé des encaissements logistiques.
        <br>
        📅 {{ now()->isoFormat('dddd D MMMM YYYY') }}
    </p>
</div>

{{-- KPI --}}
<div class="kpi-grid">
    <div class="kpi-card info">
        <div class="kpi-label">📂 Total dossiers logistiques</div>
        <div class="kpi-value">{{ $stats['total_dossiers'] }}</div>
        <div class="kpi-sub">Avec un prix logistique défini</div>
    </div>
    <div class="kpi-card success">
        <div class="kpi-label">✅ Dossiers payés</div>
        <div class="kpi-value" style="color:#16a34a;">{{ $stats['nb_payes'] }}</div>
        <div class="kpi-sub">{{ $stats['pct_payes'] }}% du total</div>
    </div>
    <div class="kpi-card warning">
        <div class="kpi-label">⏳ En cours de paiement</div>
        <div class="kpi-value" style="color:#f59e0b;">{{ $stats['nb_partiels'] }}</div>
        <div class="kpi-sub">{{ $stats['pct_partiels'] }}% du total</div>
    </div>
    <div class="kpi-card danger">
        <div class="kpi-label">❌ Non payés</div>
        <div class="kpi-value" style="color:#dc2626;">{{ $stats['nb_non_payes'] }}</div>
        <div class="kpi-sub">{{ $stats['pct_non_payes'] }}% du total</div>
    </div>
</div>

<div class="kpi-grid">
    <div class="kpi-card">
        <div class="kpi-label">🚗 Référence logistique totale</div>
        <div class="kpi-value" style="font-size:22px;">
            {{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }}
        </div>
        <div class="kpi-sub">FCFA attendus</div>
    </div>
    <div class="kpi-card success">
        <div class="kpi-label">💵 Encaissé</div>
        <div class="kpi-value" style="font-size:22px;color:#16a34a;">
            {{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }}
        </div>
        <div class="kpi-sub">FCFA déjà payés</div>
    </div>
    <div class="kpi-card danger">
        <div class="kpi-label">🔻 Reste à encaisser</div>
        <div class="kpi-value" style="font-size:22px;color:#dc2626;">
            {{ number_format($stats['reste_logistique_total'], 0, ',', ' ') }}
        </div>
        <div class="kpi-sub">FCFA à recouvrer</div>
    </div>
    <div class="kpi-card info">
        <div class="kpi-label">📈 Progression globale</div>
        <div class="kpi-value">{{ $stats['progression_moyenne'] }}%</div>
        <div class="kpi-sub">Moyenne des encaissements</div>
    </div>
</div>

{{-- PROGRESSION --}}
<div class="progress-global">
    <div class="big-label">Progression globale des encaissements logistiques</div>
    <div class="big-value">{{ $stats['progression_moyenne'] }}%</div>
    <div class="progress-bar-big">
        <div style="width:{{ $stats['progression_moyenne'] }}%;"></div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:12px;color:#64748b;margin-top:8px;flex-wrap:wrap;gap:8px;">
        <span>💵 Payé : <strong style="color:#16a34a;">
            {{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }} FCFA
        </strong></span>
        <span>🎯 Objectif : <strong style="color:#1e3a5f;">
            {{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }} FCFA
        </strong></span>
    </div>
</div>

{{-- ACTIONS --}}
<h2 class="section-title">⚡ Actions rapides</h2>

<div class="quick-actions">
    <a href="{{ route('comptable.index') }}" class="quick-action">
        <div class="icon">🚗</div>
        <div>
            <div class="title">Effectuer un paiement</div>
            <div class="desc">Enregistrer un paiement logistique</div>
        </div>
    </a>
    <a href="{{ route('comptable.index', ['statut' => 'non_paye']) }}" class="quick-action">
        <div class="icon">❌</div>
        <div>
            <div class="title">Dossiers non payés</div>
            <div class="desc">À relancer rapidement</div>
        </div>
    </a>
    <a href="{{ route('comptable.index', ['statut' => 'partiel']) }}" class="quick-action">
        <div class="icon">⏳</div>
        <div>
            <div class="title">Dossiers partiels</div>
            <div class="desc">En cours de paiement</div>
        </div>
    </a>
    <a href="{{ route('comptable.rapport') }}" class="quick-action">
        <div class="icon">📊</div>
        <div>
            <div class="title">Rapport logistique</div>
            <div class="desc">Statistiques détaillées</div>
        </div>
    </a>
</div>

{{-- DERNIERS PAIEMENTS --}}
<h2 class="section-title">
    🕐 Vos 10 derniers paiements logistiques
    <span style="font-size:12px;color:#64748b;font-weight:500;">
        ({{ $mesPaiements->count() }} aujourd'hui)
    </span>
</h2>

<div class="table-recent">
    <table>
        <thead>
            <tr>
                <th>Client</th>
                <th>Dossier</th>
                <th>Bénéficiaire</th>
                <th>Montant</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($derniersPaiements as $p)
                <tr>
                    <td>
                        <strong>{{ $p->dossier?->client?->name ?? '-' }}</strong>
                        <br><small style="color:#94a3b8;">{{ $p->dossier?->client?->phone ?? '-' }}</small>
                    </td>
                    <td>{{ $p->dossier?->nom_dossier ?? '-' }}</td>
                    <td>
                        @if($p->beneficiaire)
                            <span style="background:#faf5ff;color:#7c3aed;padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;">
                                👤 {{ $p->beneficiaire->nom }}
                            </span>
                        @else
                            <span style="color:#94a3b8;">—</span>
                        @endif
                    </td>
                    <td>
                        <strong style="color:#7c3aed;">
                            {{ number_format($p->montant, 0, ',', ' ') }} FCFA
                        </strong>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($p->date_paiement)->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;padding:40px;color:#94a3b8;">
                        <div style="font-size:36px;">📭</div>
                        <div style="font-weight:700;margin-top:8px;">Aucun paiement récent</div>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@endsection