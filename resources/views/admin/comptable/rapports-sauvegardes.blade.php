@extends('layouts.comptable')

@section('title', 'Rapports sauvegardés')

@section('styles')
<style>
    .header-box {
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        color: white; padding: 24px 28px; border-radius: 14px;
        margin-bottom: 20px; box-shadow: 0 4px 16px rgba(124,58,237,0.25);
    }
    .header-box h2 { font-weight: 800; margin: 0; }
    .header-box p  { opacity: 0.9; margin: 4px 0 0 0; font-size: 13px; }

    .filter-box {
        background: white; border-radius: 12px; padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 20px;
    }

    .rapport-card {
        background: white; border-radius: 12px; padding: 16px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 12px;
        border-left: 4px solid #7c3aed;
        transition: box-shadow 0.2s;
    }
    .rapport-card:hover { box-shadow: 0 6px 20px rgba(0,0,0,0.1); }

    .rapport-title {
        font-weight: 800; color: #1e3a5f; font-size: 15px;
    }
    .rapport-meta {
        font-size: 12px; color: #64748b; margin-top: 4px;
    }
    .rapport-note {
        background: #fffbeb; border-left: 3px solid #f59e0b;
        border-radius: 6px; padding: 8px 12px; margin-top: 10px;
        font-size: 12px; color: #92400e; font-style: italic;
    }
    .rapport-stats {
        display: flex; flex-wrap: wrap; gap: 8px; margin-top: 10px;
    }
    .rapport-stat {
        background: #f8fafc; border-radius: 6px; padding: 6px 12px;
        font-size: 11px;
    }
    .rapport-stat strong { color: #1e3a5f; }

    .btn-download {
        background: #16a34a; color: white; padding: 6px 14px;
        border-radius: 6px; font-weight: 700; font-size: 12px;
        text-decoration: none; display: inline-flex; align-items: center; gap: 4px;
    }
    .btn-download:hover { background: #15803d; color: white; }

    .btn-delete {
        background: #fee2e2; color: #dc2626; border: none;
        padding: 6px 14px; border-radius: 6px; font-weight: 700;
        font-size: 12px; cursor: pointer;
    }
    .btn-delete:hover { background: #dc2626; color: white; }
</style>
@endsection

@section('content')

<div class="header-box">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2>📚 Rapports sauvegardés</h2>
            <p>Historique de tous les rapports logistiques générés</p>
        </div>
        <a href="{{ route('comptable.rapport') }}" class="btn btn-light" style="font-weight:700;">
            ← Retour au rapport
        </a>
    </div>
</div>

{{-- FILTRES --}}
<div class="filter-box">
    <form method="GET" action="{{ route('comptable.rapports.sauvegardes') }}">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">
                    🔍 Recherche
                </label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Titre ou nom de fichier..."
                       value="{{ request('q') }}">
            </div>
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">
                    📅 Du
                </label>
                <input type="date" name="date_debut" class="form-control form-control-sm"
                       value="{{ request('date_debut') }}">
            </div>
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;">
                    📅 Au
                </label>
                <input type="date" name="date_fin" class="form-control form-control-sm"
                       value="{{ request('date_fin') }}">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill" style="font-weight:700;">
                    🔍 Filtrer
                </button>
                <a href="{{ route('comptable.rapports.sauvegardes') }}" class="btn btn-outline-secondary btn-sm">
                    ✖
                </a>
            </div>
        </div>
    </form>
</div>

{{-- LISTE DES RAPPORTS --}}
@forelse($rapports as $rapport)
    @php
        $activite = $rapport->calculerActiviteComptable();
    @endphp

    <div class="rapport-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div style="flex:1;">
                <div class="rapport-title">
                    📄 {{ $rapport->titre }}
                </div>
                <div class="rapport-meta">
                    📅 Créé le {{ $rapport->created_at->format('d/m/Y à H:i') }}
                    &nbsp;·&nbsp;
                    👤 {{ $rapport->user?->name ?? 'Inconnu' }}
                    &nbsp;·&nbsp;
                    <span style="background:#eff6ff;color:#1e3a5f;padding:2px 8px;border-radius:4px;font-weight:700;">
                        {{ strtoupper($rapport->type) }}
                    </span>
                </div>

                @if($rapport->note)
                    <div class="rapport-note">
                        📝 <strong>Note :</strong> {{ \Illuminate\Support\Str::limit($rapport->note, 200) }}
                    </div>
                @endif

                <div class="rapport-stats">
                    <div class="rapport-stat">
                        📂 <strong>{{ $rapport->nb_dossiers }}</strong> dossiers
                    </div>
                    <div class="rapport-stat">
                        👥 <strong>{{ $rapport->nb_beneficiaires }}</strong> bénéficiaires
                    </div>
                    <div class="rapport-stat">
                        💰 Réf. <strong style="color:#7c3aed;">{{ number_format($rapport->total_prix, 0, ',', ' ') }}</strong> FCFA
                    </div>
                    <div class="rapport-stat">
                        💵 Encaissé <strong style="color:#16a34a;">{{ number_format($rapport->total_paye, 0, ',', ' ') }}</strong> FCFA
                    </div>

                    {{-- ✅ Activité recalculée --}}
                    @if($activite['total_comptable'] > 0)
                        <div class="rapport-stat" style="background:#f0fdf4;">
                            💼 Comptables <strong style="color:#15803d;">{{ number_format($activite['total_comptable'], 0, ',', ' ') }}</strong> FCFA
                            <small style="color:#64748b;">({{ $activite['nb_comptable'] }})</small>
                        </div>
                    @endif

                    @if($activite['total_autres'] > 0)
                        <div class="rapport-stat" style="background:#f8fafc;">
                            👥 Autres <strong style="color:#475569;">{{ number_format($activite['total_autres'], 0, ',', ' ') }}</strong> FCFA
                            <small style="color:#64748b;">({{ $activite['nb_autres'] }})</small>
                        </div>
                    @endif

                    @if($rapport->frais_transport > 0)
                        <div class="rapport-stat" style="background:#fef3c7;">
                            🚗 Frais transport <strong style="color:#92400e;">{{ number_format($rapport->frais_transport, 0, ',', ' ') }}</strong> FCFA
                        </div>
                    @endif

                    @if($rapport->reste_a_reverser > 0)
                        <div class="rapport-stat" style="background:#dcfce7;">
                            📤 À reverser <strong style="color:#15803d;">{{ number_format($rapport->reste_a_reverser, 0, ',', ' ') }}</strong> FCFA
                        </div>
                    @endif
                </div>
            </div>

            <div class="d-flex flex-column gap-2">
                <a href="{{ $rapport->url }}" target="_blank" class="btn-download">
                    ⬇️ Télécharger
                </a>
                <form method="POST" action="{{ route('comptable.rapports.sauvegardes.destroy', $rapport->id) }}"
                      onsubmit="return confirm('Supprimer ce rapport ?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-delete w-100">
                        🗑 Supprimer
                    </button>
                </form>
            </div>
        </div>
    </div>
@empty
    <div class="text-center py-5" style="background:white;border-radius:12px;">
        <div style="font-size:48px;">📭</div>
        <h4 style="color:#1e3a5f;font-weight:800;margin-top:12px;">Aucun rapport sauvegardé</h4>
        <p style="color:#64748b;font-size:13px;">
            Générez votre premier rapport depuis la page
            <a href="{{ route('comptable.rapport') }}">Rapport Logistique</a>.
        </p>
    </div>
@endforelse

<div class="mt-3 d-flex justify-content-center">
    {{ $rapports->links() }}
</div>

@endsection