@extends('admin.layout')
@section('content')

<style>
.info-bloc { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,0.06); margin-bottom:14px; }
.info-bloc h6 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:12px; }
.info-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
.info-row span:first-child { color:#64748b; }
.info-row span:last-child  { font-weight:600; color:#1e3a5f; }
.vers-bloc { border-radius:10px; padding:14px; margin-bottom:8px; }
.vers-total { background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:12px 16px; display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; }
.cumul-row { display:flex; justify-content:space-between; padding:6px 0; border-bottom:1px solid #f1f5f9; font-size:13px; }
.reste-bloc { background:#fff1f2; border:1px solid #fecaca; border-radius:10px; padding:14px 18px; display:flex; justify-content:space-between; align-items:center; }
</style>

@php
    $dossier  = $bon->dossier;
    $client   = $dossier->client;
    $site     = $dossier->grandSite;

    $refTotal = ($dossier->prix_superficie   ?? 0)
              + ($dossier->prix_technique    ?? 0)
              + ($dossier->prix_logistique   ?? 0)
              + ($dossier->prix_morcellement ?? 0);

    $totalCumul = $bon->total_dossier_cumul
                + $bon->total_technique_cumul
                + $bon->total_logistique_cumul
                + $bon->total_morcellement_cumul;

    $resteTotal = max(0, $refTotal - $totalCumul);
@endphp

{{-- EN-TÊTE PAGE --}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <a href="{{ route('bons.index', $dossier->id) }}"
           class="btn btn-outline-secondary btn-sm mb-2">← Bons du dossier</a>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">🧾 {{ $bon->numero_bon }}</h2>
        <div style="font-size:13px;color:#64748b;">
            📅 {{ $bon->date_bon->format('d/m/Y') }}
            &nbsp;·&nbsp; Créé le {{ $bon->created_at->format('d/m/Y à H:i') }}
        </div>
    </div>
    <div class="d-flex gap-2">
        {{-- Toggle afficher reste --}}
        <button onclick="toggleReste({{ $bon->id }})"
                id="btn-toggle-reste"
                class="btn btn-sm {{ $bon->afficher_reste ? 'btn-warning' : 'btn-outline-secondary' }}"
                style="font-size:12px;">
            {{ $bon->afficher_reste ? '🙈 Masquer le reste' : '👁 Afficher le reste' }}
        </button>
        <a href="{{ route('bons.pdf', $bon->id) }}"
           class="btn btn-danger btn-sm">🖨️ PDF</a>
        <form action="{{ route('bons.destroy', $bon->id) }}" method="POST"
              onsubmit="return confirm('Supprimer ce bon définitivement ?')">
            @csrf @method('DELETE')
            <button class="btn btn-outline-danger btn-sm">🗑 Supprimer</button>
        </form>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">

    {{-- COLONNE GAUCHE --}}
    <div class="col-md-7">

        {{-- INFOS CLIENT --}}
        <div class="info-bloc">
            <h6>👤 Informations Client</h6>
            <div class="info-row">
                <span>Nom</span>
                <span>{{ $client?->name ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span>Téléphone</span>
                <span>{{ $client?->phone ?? '-' }}</span>
            </div>
            <div class="info-row">
                <span>Site</span>
                <span>{{ $site?->nom ?? $dossier->nom_dossier ?? '-' }}</span>
            </div>
            @if($dossier->superficie_voulue)
            <div class="info-row">
                <span>Superficie</span>
                <span>{{ number_format($dossier->superficie_voulue, 0, ',', ' ') }} m²</span>
            </div>
            @endif
            @if($dossier->prix_superficie > 0 && $dossier->superficie_voulue > 0)
            <div class="info-row">
                <span>Prix unitaire</span>
                <span>{{ number_format($dossier->prix_unitaire, 0, ',', ' ') }} FCFA/m²</span>
            </div>
            @endif
        </div>

        {{-- VERSEMENTS DU JOUR --}}
        <div class="info-bloc">
            <h6>💰 Versements du jour</h6>

            @if($bon->versement_dossier > 0)
            <div class="vers-bloc" style="background:#eff6ff;border:1px solid #bfdbfe;margin-bottom:8px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-weight:700;color:#1d4ed8;">📁 Paiement Dossier</span>
                    <span style="font-size:16px;font-weight:800;color:#1d4ed8;">
                        {{ number_format($bon->versement_dossier, 0, ',', ' ') }} FCFA
                    </span>
                </div>
            </div>
            @endif

            @if($bon->versement_technique > 0)
            <div class="vers-bloc" style="background:#fff7ed;border:1px solid #fed7aa;margin-bottom:8px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-weight:700;color:#ea580c;">🛠️ Paiement Technique</span>
                    <span style="font-size:16px;font-weight:800;color:#ea580c;">
                        {{ number_format($bon->versement_technique, 0, ',', ' ') }} FCFA
                    </span>
                </div>
            </div>
            @endif

            @if($bon->versement_logistique > 0)
            <div class="vers-bloc" style="background:#faf5ff;border:1px solid #e9d5ff;margin-bottom:8px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-weight:700;color:#7c3aed;">🚗 Frais Logistique</span>
                    <span style="font-size:16px;font-weight:800;color:#7c3aed;">
                        {{ number_format($bon->versement_logistique, 0, ',', ' ') }} FCFA
                    </span>
                </div>
            </div>
            @endif

            @if($bon->versement_morcellement > 0)
            <div class="vers-bloc" style="background:#fefce8;border:1px solid #fde68a;margin-bottom:8px;">
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <span style="font-weight:700;color:#ca8a04;">✂️ Paiement Morcellement</span>
                    <span style="font-size:16px;font-weight:800;color:#ca8a04;">
                        {{ number_format($bon->versement_morcellement, 0, ',', ' ') }} FCFA
                    </span>
                </div>
            </div>
            @endif

            {{-- TOTAL DU JOUR --}}
            <div class="vers-total">
                <span style="font-weight:700;color:#64748b;font-size:14px;">
                    Total versement du jour
                </span>
                <span style="font-size:22px;font-weight:900;color:#16a34a;">
                    {{ number_format($bon->total_versement, 0, ',', ' ') }} FCFA
                </span>
            </div>

            {{-- NOTES --}}
            @if($bon->notes)
            <div style="background:#f8fafc;border-left:3px solid #1d4ed8;padding:10px 14px;border-radius:0 8px 8px 0;font-size:13px;color:#374151;">
                <strong>Notes :</strong> {{ $bon->notes }}
            </div>
            @endif
        </div>

    </div>

    {{-- COLONNE DROITE --}}
    <div class="col-md-5">

        {{-- CUMUL TOTAL --}}
        <div class="info-bloc">
            <h6>📊 Cumul total versé à ce jour</h6>

            @if($bon->total_dossier_cumul > 0)
            <div class="cumul-row">
                <span>📁 Dossier</span>
                <span style="color:#1d4ed8;font-weight:700;">
                    {{ number_format($bon->total_dossier_cumul, 0, ',', ' ') }} FCFA
                </span>
            </div>
            @endif

            @if($bon->total_technique_cumul > 0)
            <div class="cumul-row">
                <span>🛠️ Technique</span>
                <span style="color:#ea580c;font-weight:700;">
                    {{ number_format($bon->total_technique_cumul, 0, ',', ' ') }} FCFA
                </span>
            </div>
            @endif

            @if($bon->total_logistique_cumul > 0)
            <div class="cumul-row">
                <span>🚗 Logistique</span>
                <span style="color:#7c3aed;font-weight:700;">
                    {{ number_format($bon->total_logistique_cumul, 0, ',', ' ') }} FCFA
                </span>
            </div>
            @endif

            @if($bon->total_morcellement_cumul > 0)
            <div class="cumul-row">
                <span>✂️ Morcellement</span>
                <span style="color:#ca8a04;font-weight:700;">
                    {{ number_format($bon->total_morcellement_cumul, 0, ',', ' ') }} FCFA
                </span>
            </div>
            @endif

            <div style="display:flex;justify-content:space-between;padding:10px 0;margin-top:4px;">
                <span style="font-weight:800;color:#1e3a5f;font-size:14px;">Total cumulé</span>
                <span style="font-weight:900;color:#16a34a;font-size:18px;">
                    {{ number_format($totalCumul, 0, ',', ' ') }} FCFA
                </span>
            </div>
        </div>

        {{-- RESTE À PAYER --}}
        <div id="bloc-reste" style="{{ $bon->afficher_reste ? '' : 'display:none;' }}">
            @if($refTotal > 0)
            <div class="reste-bloc">
                <div>
                    <div style="font-weight:700;color:#b91c1c;font-size:13px;">⏳ Reste à payer</div>
                    <div style="font-size:11px;color:#64748b;margin-top:2px;">
                        Référence totale : {{ number_format($refTotal, 0, ',', ' ') }} FCFA
                    </div>
                </div>
                <div style="font-size:22px;font-weight:900;color:#b91c1c;">
                    {{ number_format($resteTotal, 0, ',', ' ') }} FCFA
                </div>
            </div>
            @else
            <div style="background:#f1f5f9;border-radius:10px;padding:14px;text-align:center;color:#64748b;font-size:12px;">
                ℹ️ Définissez les prix de référence dans le dossier pour afficher le reste à payer.
            </div>
            @endif
        </div>

        {{-- LIENS RAPIDES --}}
        <div class="info-bloc mt-3">
            <h6>🔗 Actions rapides</h6>
            <div class="d-grid gap-2">
                <a href="{{ route('bons.creer', $dossier->id) }}"
                   class="btn btn-primary btn-sm">+ Nouveau bon pour ce dossier</a>
                <a href="{{ route('bons.index', $dossier->id) }}"
                   class="btn btn-outline-secondary btn-sm">📋 Tous les bons</a>
                <a href="{{ route('suivi-client.show', $dossier->client_id) }}"
                   class="btn btn-outline-secondary btn-sm">👤 Fiche client</a>
                <a href="{{ route('bons.pdf', $bon->id) }}"
                   class="btn btn-danger btn-sm">🖨️ Télécharger le PDF</a>
            </div>
        </div>

    </div>
</div>

@endsection
@section('scripts')
<script>
const CSRF = '{{ csrf_token() }}';

function toggleReste(bonId) {
    fetch(`/admin/bons/detail/${bonId}/toggle-reste`, {
        method:  'POST',
        headers: { 'X-CSRF-TOKEN': CSRF, 'Content-Type': 'application/json' },
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const bloc = document.getElementById('bloc-reste');
            const btn  = document.getElementById('btn-toggle-reste');
            if (data.afficher_reste) {
                bloc.style.display = '';
                btn.innerText      = '🙈 Masquer le reste';
                btn.classList.remove('btn-outline-secondary');
                btn.classList.add('btn-warning');
            } else {
                bloc.style.display = 'none';
                btn.innerText      = '👁 Afficher le reste';
                btn.classList.remove('btn-warning');
                btn.classList.add('btn-outline-secondary');
            }
        }
    })
    .catch(e => alert('Erreur : ' + e.message));
}
</script>
@endsection