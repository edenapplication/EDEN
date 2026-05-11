@extends('admin.layout')
@section('content')

<style>
.etape-card {
    border:2px solid #e5e7eb; border-radius:12px;
    padding:14px 18px; margin-bottom:10px;
    display:flex; align-items:center; justify-content:space-between;
    transition:0.2s; cursor:pointer; user-select:none;
}
.etape-card:hover { border-color:#f59e0b; background:#fffbeb; }
.etape-card.coche { border-color:#28a745; background:#f0fff4; }
.badge-poids { font-size:12px; background:#e5e7eb; color:#333; padding:3px 10px; border-radius:20px; font-weight:600; }
.etape-card.coche .badge-poids { background:#28a745; color:white; }
.check-icon { font-size:20px; margin-right:12px; }
.progress { height:16px; border-radius:8px; }
.progress-bar { border-radius:8px; transition:width 0.5s ease; font-weight:600; font-size:12px; }
.info-section { background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:20px; }
.info-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px; }
.zone-header {
    background:linear-gradient(135deg,#f59e0b,#d97706);
    color:white; border-radius:12px; padding:16px 20px; margin-bottom:20px;
}
</style>

<div class="mb-3">
    <a href="{{ route('admin.dashboard') }}" class="text-muted text-decoration-none">Dashboard</a>
    <span class="text-muted mx-1">›</span>
    <strong>Dossier Zone — {{ $zone->owner_name ?? $zone->nom ?? 'Zone #'.$zone->id }}</strong>
</div>

<div class="zone-header">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <h2 class="mb-1">🟡 {{ $zone->owner_name ?? $zone->nom ?? 'Zone groupée' }}</h2>
            <div style="opacity:0.85;font-size:13px;">
                📐 {{ number_format($zone->superficie_totale ?? 0, 0, ',', ' ') }} m²
                @if($zone->type)
                    &nbsp;|&nbsp;
                    <span style="background:rgba(255,255,255,0.25);padding:2px 10px;border-radius:10px;">
                        {{ ['implantation_prevue'=>'Implantation prévue','deja_implante'=>'Déjà implanté','dossier_technique'=>'Dossier technique','morcellement'=>'Morcellement'][$zone->type] ?? $zone->type }}
                    </span>
                @endif
            </div>
        </div>
        <button onclick="window.history.back()" class="btn" style="background:rgba(255,255,255,0.2);color:white;border:none;">← Retour</button>
    </div>
</div>

<div class="row g-3">

    {{-- GAUCHE : PROGRESSION --}}
    <div class="col-md-7">
        <div class="card p-4 mb-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h5 class="mb-0">Progression du dossier</h5>
                <span id="statut-badge" class="badge fs-6
                    @if($dossier->statut === 'complet') bg-success
                    @elseif($dossier->statut === 'en_cours') bg-warning text-dark
                    @else bg-secondary @endif">
                    {{ $dossier->statut === 'none' ? 'Non commencé' : ($dossier->statut === 'en_cours' ? 'En cours' : 'Complet') }}
                </span>
            </div>
            <div class="progress">
                <div class="progress-bar
                    @if($dossier->progression < 40) bg-danger
                    @elseif($dossier->progression < 75) bg-warning
                    @else bg-success @endif"
                     id="barre-progression"
                     style="width:{{ $dossier->progression }}%">
                    {{ $dossier->progression }}%
                </div>
            </div>
        </div>

        <div class="card p-4">
            <h5 class="mb-4">✅ Étapes du dossier</h5>
            @php
                $etapes = [
                    'montage_dossier' => ['label' => 'Montage du dossier', 'poids' => 40, 'icon' => '📋'],
                    'bon_pour_ccp'    => ['label' => 'Bon pour CCP',       'poids' => 10, 'icon' => '✔️'],
                    'controle'        => ['label' => 'Contrôle',           'poids' => 15, 'icon' => '🔍'],
                    'mise_a_jour'     => ['label' => 'Mise à jour',        'poids' => 10, 'icon' => '🔄'],
                    'secretariat'     => ['label' => 'Secrétariat',        'poids' => 10, 'icon' => '🗂️'],
                    'signature'       => ['label' => 'Signature',          'poids' => 15, 'icon' => '✍️'],
                ];
            @endphp
            @foreach($etapes as $champ => $info)
                <div class="etape-card {{ $dossier->$champ ? 'coche' : '' }}"
                     id="card-{{ $champ }}"
                     onclick="toggleEtape('{{ $champ }}')">
                    <div class="d-flex align-items-center">
                        <span class="check-icon" id="check-{{ $champ }}">{{ $dossier->$champ ? '✅' : '⬜' }}</span>
                        <strong>{{ $info['icon'] }} {{ $info['label'] }}</strong>
                    </div>
                    <span class="badge-poids">{{ $info['poids'] }}%</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- DROITE : INFO ZONE --}}
    <div class="col-md-5">
        <div class="info-section">
            <h5>🟡 Zone & Client</h5>
            <div style="font-size:13px;line-height:2.2;">
                <div><span class="text-muted">Nom :</span> <strong>{{ $zone->owner_name ?? $zone->nom ?? '-' }}</strong></div>
                <div><span class="text-muted">Client :</span> <strong style="color:#1d4ed8;">{{ $zone->client?->name ?? '-' }}</strong></div>
                <div><span class="text-muted">Téléphone :</span> {{ $zone->client?->phone ?? '-' }}</div>
                <div><span class="text-muted">TF :</span> {{ $zone->tf?->title ?? '-' }}</div>
                <div><span class="text-muted">Superficie totale :</span> <strong>{{ number_format($zone->superficie_totale ?? 0, 0, ',', ' ') }} m²</strong></div>
                @if($zone->lot_ids && count($zone->lot_ids))
                    <div><span class="text-muted">Lots inclus :</span> {{ count($zone->lot_ids) }} lot(s)</div>
                @endif
                @if($dossierClient)
                    <div style="margin-top:8px;padding:8px 12px;background:#f0f7ff;border-radius:8px;border-left:3px solid #1d4ed8;">
                        <div style="font-weight:700;color:#1e3a5f;">📂 {{ $dossierClient->nom_dossier }}</div>
                        @php
                            $totalPaye = $dossierClient->paiements->sum('montant');
                            $reste     = max(0, ($dossierClient->prix_superficie ?? 0) - $totalPaye);
                            $pct       = ($dossierClient->prix_superficie ?? 0) > 0
                                        ? round(($totalPaye / $dossierClient->prix_superficie) * 100) : 0;
                        @endphp
                        <div style="font-size:11px;color:#64748b;margin-top:4px;">
                            💰 Payé : <strong style="color:#28a745;">{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</strong>
                            &nbsp;|&nbsp;
                            ⏳ Reste : <strong style="color:#dc3545;">{{ number_format($reste, 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div style="height:6px;background:#e2e8f0;border-radius:3px;margin-top:6px;">
                            <div style="width:{{ $pct }}%;height:100%;background:{{ $pct>=100?'#28a745':($pct>=50?'#4D96FF':'#fd7e14') }};border-radius:3px;"></div>
                        </div>
                        <div style="font-size:10px;text-align:right;color:#64748b;margin-top:2px;">{{ $pct }}%</div>
                    </div>
                @else
                    <div class="alert alert-warning mt-3" style="font-size:12px;">
                        ⚠️ Aucun dossier client lié à cette zone.
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
@section('scripts')
<script>
const zoneId = {{ $zone->id }};
const csrf   = "{{ csrf_token() }}";

function toggleEtape(etape) {
    fetch(`/admin/dossier-zone/toggle/${zoneId}`, {
        method: "POST",
        headers: { "Content-Type":"application/json", "X-CSRF-TOKEN":csrf },
        body: JSON.stringify({ etape })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;
        const card  = document.getElementById(`card-${etape}`);
        const check = document.getElementById(`check-${etape}`);
        if (data.valeur) { card.classList.add('coche'); check.innerText = '✅'; }
        else { card.classList.remove('coche'); check.innerText = '⬜'; }
        const barre = document.getElementById('barre-progression');
        barre.style.width = data.progression + '%';
        barre.innerText   = data.progression + '%';
        barre.className   = 'progress-bar ' + (data.progression < 40 ? 'bg-danger' : data.progression < 75 ? 'bg-warning' : 'bg-success');
        const badge = document.getElementById('statut-badge');
        badge.innerText = data.statut === 'none' ? 'Non commencé' : data.statut === 'en_cours' ? 'En cours' : 'Complet';
        badge.className = 'badge fs-6 ' + (data.statut === 'complet' ? 'bg-success' : data.statut === 'en_cours' ? 'bg-warning text-dark' : 'bg-secondary');
    });
}
</script>
@endsection