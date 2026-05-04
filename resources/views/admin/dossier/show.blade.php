@extends('admin.layout')
@section('content')

<style>
.etape-card {
    border:2px solid #e5e7eb; border-radius:12px;
    padding:14px 18px; margin-bottom:10px;
    display:flex; align-items:center; justify-content:space-between;
    transition:0.2s; cursor:pointer; user-select:none;
}
.etape-card:hover { border-color:#4D96FF; background:#f0f6ff; }
.etape-card.coche { border-color:#28a745; background:#f0fff4; }
.badge-poids { font-size:12px; background:#e5e7eb; color:#333; padding:3px 10px; border-radius:20px; font-weight:600; }
.etape-card.coche .badge-poids { background:#28a745; color:white; }
.check-icon { font-size:20px; margin-right:12px; }
.progress { height:16px; border-radius:8px; }
.progress-bar { border-radius:8px; transition:width 0.5s ease; font-weight:600; font-size:12px; }

/* PAIEMENTS */
.pay-section { background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:20px; margin-top:16px; }
.pay-section h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px; }
.pay-row { display:flex; justify-content:space-between; align-items:center; padding:8px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
.pay-amt { font-weight:700; color:#28a745; }
.pay-reste { font-weight:600; color:#dc3545; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:24px; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:400px; }
</style>

{{-- BREADCRUMB --}}
<div class="mb-3">
    <a href="{{ route('lots.vendus') }}" class="text-muted text-decoration-none">Suivi parcelles</a>
    <span class="text-muted mx-1">›</span>
    <strong>Dossier — {{ strtoupper($lot->code) }}</strong>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h2>📁 Dossier Technique — <span class="text-muted fs-5">{{ strtoupper($lot->code) }}</span></h2>
    <div class="d-flex gap-2">
        <button onclick="openPaiement()" class="btn btn-success">💰 Ajouter un paiement</button>
        <button onclick="window.location.reload()" class="btn btn-outline-secondary">🔄 Actualiser</button>
    </div>
</div>

<div class="row g-3">

    {{-- COLONNE GAUCHE : PROGRESSION --}}
    <div class="col-md-7">

        {{-- BARRE PROGRESSION --}}
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

        {{-- CHECKLIST --}}
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
                        <span class="check-icon" id="check-{{ $champ }}">
                            {{ $dossier->$champ ? '✅' : '⬜' }}
                        </span>
                        <strong>{{ $info['icon'] }} {{ $info['label'] }}</strong>
                    </div>
                    <span class="badge-poids">{{ $info['poids'] }}%</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- COLONNE DROITE : PAIEMENTS --}}
    <div class="col-md-5">

        {{-- INFO LOT + CLIENT --}}
        <div class="card p-3 mb-3">
            <h6 style="font-weight:700; color:#1e3a5f; margin-bottom:10px;">📦 Lot & Client</h6>
            <div style="font-size:13px; line-height:2;">
                <div><span class="text-muted">Lot :</span> <strong>{{ strtoupper($lot->code) }}</strong></div>
                <div><span class="text-muted">TF :</span> {{ $lot->tf?->title ?? '-' }}</div>
                <div><span class="text-muted">Site :</span> {{ $lot->tf?->site?->name ?? '-' }}</div>
                <div><span class="text-muted">Client :</span> <strong>{{ $lot->client?->name ?? $lot->owner_name ?? '-' }}</strong></div>
                <div><span class="text-muted">Téléphone :</span> {{ $lot->client?->phone ?? '-' }}</div>
            </div>
        </div>

        {{-- PAIEMENTS SUR LE DOSSIER CLIENT --}}
        <div class="pay-section">
            <h5>💳 Paiements du dossier client</h5>

            @php
                // Paiements via le dossier client lié
                $dossierClient = $lot->client?->dossiers->first();
                $paiementsDossier = $dossierClient?->paiements ?? collect();
                $prixRef    = $dossierClient?->prix_superficie ?? $lot->prix ?? 0;
                $totalPaye  = $paiementsDossier->sum('montant');
                $reste      = max(0, $prixRef - $totalPaye);
                $pctPaye    = $prixRef > 0 ? round(($totalPaye / $prixRef) * 100) : 0;
            @endphp

            {{-- RÉSUMÉ --}}
            <div style="background:#f8fafc; border-radius:10px; padding:12px; margin-bottom:14px;">
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:12px; color:#64748b;">Prix de référence</span>
                    <strong>{{ number_format($prixRef, 0, ',', ' ') }} FCFA</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span style="font-size:12px; color:#64748b;">Total payé</span>
                    <strong class="pay-amt">{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span style="font-size:12px; color:#64748b;">Reste à payer</span>
                    <strong class="pay-reste">{{ number_format($reste, 0, ',', ' ') }} FCFA</strong>
                </div>
                {{-- Barre de paiement --}}
                <div style="height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden;">
                    <div style="width:{{ $pctPaye }}%; height:100%; background:{{ $pctPaye >= 100 ? '#28a745' : ($pctPaye >= 50 ? '#4D96FF' : '#fd7e14') }}; border-radius:4px; transition:0.5s;"></div>
                </div>
                <div style="font-size:11px; text-align:right; color:#64748b; margin-top:2px;">{{ $pctPaye }}% payé</div>
            </div>

            {{-- HISTORIQUE --}}
            @if($dossierClient)
                @forelse($paiementsDossier->sortByDesc('date_paiement') as $p)
                    <div class="pay-row">
                        <div>
                            <div style="font-weight:600; font-size:13px;">{{ number_format($p->montant, 0, ',', ' ') }} FCFA</div>
                            <div style="font-size:11px; color:#64748b;">
                                📅 {{ $p->date_paiement }}
                                @if($p->note) — {{ $p->note }} @endif
                            </div>
                        </div>
                        <div style="font-size:11px; color:#dc3545;">
                            Reste : {{ number_format($p->reste, 0, ',', ' ') }} FCFA
                        </div>
                    </div>
                @empty
                    <p class="text-muted" style="font-size:13px;">Aucun paiement enregistré</p>
                @endforelse
            @else
                <div class="alert alert-warning" style="font-size:12px;">
                    ⚠️ Aucun dossier client lié. Créez un dossier depuis <a href="{{ route('suivi-client.create') }}">Suivi clients</a>.
                </div>
            @endif
        </div>
    </div>
</div>

{{-- MODAL PAIEMENT --}}
<div class="modal-overlay" id="paiementOverlay" onclick="closePaiement()"></div>
<div class="modal-box" id="paiementModal">
    <h5 style="font-weight:700; color:#1e3a5f; margin-bottom:16px;">
        💰 Ajouter un paiement
        @if($dossierClient) — {{ $dossierClient->nom_dossier }} @endif
    </h5>

    @if($dossierClient)
    <div class="mb-3">
        <label class="form-label fw-semibold">Montant (FCFA)</label>
        <input type="number" id="montant" class="form-control" placeholder="Ex: 500000">
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Date du paiement</label>
        <input type="date" id="datePaie" class="form-control">
    </div>
    <div class="mb-3">
        <label class="form-label fw-semibold">Note (optionnel)</label>
        <input type="text" id="note" class="form-control" placeholder="Ex: Versement initial">
    </div>
    <div class="d-flex justify-content-between mt-4">
        <button onclick="closePaiement()" class="btn btn-light">Annuler</button>
        <button onclick="savePaiement()" class="btn btn-success">💾 Enregistrer</button>
    </div>
    @else
    <p class="text-muted">Aucun dossier client lié à ce lot.</p>
    <button onclick="closePaiement()" class="btn btn-secondary mt-2">Fermer</button>
    @endif
</div>

@endsection

@section('scripts')
<script>
const lotId        = {{ $lot->id }};
const dossierId    = {{ $dossierClient?->id ?? 'null' }};
const csrf         = "{{ csrf_token() }}";

// =============================================
// TOGGLE ÉTAPE DOSSIER TECHNIQUE
// =============================================
function toggleEtape(etape) {
    fetch(`/admin/dossier/toggle/${lotId}`, {
        method: "POST",
        headers: { "Content-Type":"application/json", "X-CSRF-TOKEN":csrf },
        body: JSON.stringify({ etape })
    })
    .then(r => r.json())
    .then(data => {
        if (!data.success) return;

        const card  = document.getElementById(`card-${etape}`);
        const check = document.getElementById(`check-${etape}`);

        if (data.valeur) {
            card.classList.add('coche');
            check.innerText = '✅';
        } else {
            card.classList.remove('coche');
            check.innerText = '⬜';
        }

        // Mettre à jour la barre
        const barre = document.getElementById('barre-progression');
        barre.style.width = data.progression + '%';
        barre.innerText   = data.progression + '%';

        // Mettre à jour les couleurs de la barre
        barre.className = 'progress-bar ' + (
            data.progression < 40 ? 'bg-danger' :
            data.progression < 75 ? 'bg-warning' : 'bg-success'
        );

        // Mettre à jour le badge statut
        const badge = document.getElementById('statut-badge');
        badge.innerText   = data.statut === 'none' ? 'Non commencé' : (data.statut === 'en_cours' ? 'En cours' : 'Complet');
        badge.className   = 'badge fs-6 ' + (data.statut === 'complet' ? 'bg-success' : data.statut === 'en_cours' ? 'bg-warning text-dark' : 'bg-secondary');
    });
}

// =============================================
// PAIEMENT SUR DOSSIER CLIENT
// =============================================
function openPaiement() {
    if (!dossierId) { alert('Aucun dossier client lié à ce lot.'); return; }
    document.getElementById('montant').value  = '';
    document.getElementById('datePaie').value = new Date().toISOString().split('T')[0];
    document.getElementById('note').value     = '';
    document.getElementById('paiementOverlay').style.display = 'block';
    document.getElementById('paiementModal').style.display   = 'block';
}

function closePaiement() {
    document.getElementById('paiementOverlay').style.display = 'none';
    document.getElementById('paiementModal').style.display   = 'none';
}

function savePaiement() {
    const montant = document.getElementById('montant').value;
    const date    = document.getElementById('datePaie').value;
    const note    = document.getElementById('note').value;

    if (!montant || !date) { alert('Montant et date obligatoires.'); return; }

    fetch(`/admin/paiements-dossier/${dossierId}`, {
        method: 'POST',
        headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN':csrf },
        body: JSON.stringify({ montant, date_paiement: date, note })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Erreur');
    });
}
</script>
@endsection