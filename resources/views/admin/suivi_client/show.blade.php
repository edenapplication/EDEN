@extends('admin.layout')
@section('content')

<style>
.section-card { background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:20px; margin-bottom:16px; }
.section-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px; }
.info-row { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
.info-row span:first-child { color:#64748b; }
.info-row span:last-child  { font-weight:600; color:#1e3a5f; }
.pay-row { display:flex; justify-content:space-between; font-size:12px; padding:4px 0; border-bottom:1px solid #f8fafc; }
.pay-amt { font-weight:700; color:#28a745; }
.prog-wrap { height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden; margin-top:4px; }
.prog-fill  { height:100%; border-radius:4px; }
.dossier-tab { cursor:pointer; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600; background:#f1f5f9; color:#64748b; }
.dossier-tab.active { background:#0d6efd; color:white; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:400px; }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <a href="{{ route('suivi-client.index') }}" class="btn btn-outline-secondary btn-sm">← Retour</a>
        <h2 class="d-inline ms-2">👤 {{ $client->name }}</h2>
    </div>
    <div class="d-flex gap-2">
    <a href="{{ route('suivi-client.create') }}?client_id={{ $client->id }}"
       class="btn btn-primary btn-sm">📂 Nouveau dossier</a>
    <a href="{{ route('suivi-client.edit', $client->id) }}" class="btn btn-warning btn-sm">✏️ Modifier</a>
</div>
</div>

<div class="row g-3">

    {{-- INFOS CLIENT --}}
    <div class="col-md-4">
        <div class="section-card">
            <h5>📋 Informations client</h5>
            <div class="info-row"><span>Téléphone</span><span>{{ $client->phone ?? '-' }}</span></div>
            <div class="info-row"><span>Lots attribués</span><span>{{ $client->lots->count() }}</span></div>
            <div class="info-row"><span>Dossiers</span><span>{{ $client->dossiers->count() }}</span></div>
        </div>
    </div>

    {{-- DOSSIERS CLIENT --}}
    <div class="col-md-8">
        {{-- ONGLETS DOSSIERS --}}
        @if($client->dossiers->count() > 1)
        <div class="d-flex gap-2 mb-3 flex-wrap">
            @foreach($client->dossiers as $i => $d)
                <div class="dossier-tab {{ $i === 0 ? 'active' : '' }}"
                     onclick="showDossier('dossier-{{ $d->id }}', this)">
                    {{ $d->nom_dossier }}
                </div>
            @endforeach
        </div>
        @endif

        @foreach($client->dossiers as $i => $dossier)
        <div id="dossier-{{ $dossier->id }}" class="dossier-panel" style="{{ $i > 0 ? 'display:none;' : '' }}">

            <div class="section-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">📂 {{ $dossier->nom_dossier }}</h5>
                    <button class="btn btn-sm btn-success"
                            onclick="openPaiement({{ $dossier->id }}, '{{ $dossier->nom_dossier }}')">
                        + Paiement
                    </button>
                </div>

                <div class="info-row"><span>🔗 Facilitateur</span>
                    <span>{{ $dossier->facilitateur?->nom ?? '-' }}
                        @if($dossier->facilitateur?->numero) ({{ $dossier->facilitateur->numero }}) @endif
                    </span>
                </div>
                <div class="info-row"><span>🧭 Direction</span>
                    <span>{{ match($dossier->direction) { 'baffoussam'=>'Baffoussam','bagante'=>'Bagante','direction_generale'=>'Direction Générale',default=>'-' } }}</span>
                </div>
                <div class="info-row"><span>🧑‍💼 Commercial</span>
                    <span>{{ $dossier->commercial?->name ?? '-' }}
                        @if($dossier->commercial?->phone) ({{ $dossier->commercial->phone }}) @endif
                    </span>
                </div>
                <div class="info-row"><span>🤝 Agent commercial</span>
                    <span>{{ $dossier->agentCommercial?->nom ?? '-' }}
                        @if($dossier->agentCommercial?->numero) ({{ $dossier->agentCommercial->numero }}) @endif
                    </span>
                </div>
                <div class="info-row"><span>🚗 Chauffeur</span>
                    <span>{{ $dossier->conducteur?->nom ?? '-' }}
                        @if($dossier->conducteur?->numero) ({{ $dossier->conducteur->numero }}) @endif
                    </span>
                </div>
                <div class="info-row"><span>🏢 Grand Site souhaité</span>
                    <span>{{ $dossier->grandSite?->nom ?? '-' }}</span>
                </div>
                <div class="info-row"><span>📐 Superficie voulue</span>
                    <span>{{ $dossier->superficie_voulue ? number_format($dossier->superficie_voulue, 0, ',', ' ') . ' m²' : '-' }}</span>
                </div>
                <div class="info-row"><span>💰 Prix superficie</span>
                    <span>{{ $dossier->prix_superficie ? number_format($dossier->prix_superficie, 0, ',', ' ') . ' FCFA' : '-' }}</span>
                </div>

                @php
                    $totalPaye = $dossier->paiements->sum('montant');
                    $reste     = max(0, ($dossier->prix_superficie ?? 0) - $totalPaye);
                @endphp

                <div class="info-row" style="border-top:2px solid #e2e8f0; margin-top:6px; padding-top:6px;">
                    <span>Total payé</span>
                    <span style="color:#28a745; font-weight:700;">{{ number_format($totalPaye, 0, ',', ' ') }} FCFA</span>
                </div>
                <div class="info-row">
                    <span>Reste à payer</span>
                    <span style="color:{{ $reste > 0 ? '#dc3545' : '#28a745' }}; font-weight:700;">
                        {{ number_format($reste, 0, ',', ' ') }} FCFA
                    </span>
                </div>

                {{-- HISTORIQUE PAIEMENTS --}}
                @if($dossier->paiements->count())
                <div style="margin-top:12px;">
                    <strong style="font-size:12px; color:#64748b;">Historique paiements :</strong>
                    @foreach($dossier->paiements->sortByDesc('date_paiement') as $p)
                        <div class="pay-row mt-1">
                            <span>{{ $p->date_paiement }} {{ $p->note ? '— '.$p->note : '' }}</span>
                            <span class="pay-amt">{{ number_format($p->montant, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>

    {{-- LOTS ATTRIBUÉS --}}
    <div class="col-12">
        <div class="section-card">
            <h5>📦 Lots attribués ({{ $client->lots->count() }})</h5>

            @forelse($client->lots as $lot)
                @php
                    $typeColors = ['implantation_prevue'=>'#6c757d','deja_implante'=>'#28a745','dossier_technique'=>'#dc3545','morcellement'=>'#fd7e14'];
                    $color      = $typeColors[$lot->type] ?? '#0d6efd';
                    $prog       = $lot->dossier?->progression ?? 0;
                    $progColor  = $prog < 40 ? '#dc3545' : ($prog < 75 ? '#fd7e14' : '#28a745');
                @endphp
                <div style="border-left:4px solid {{ $color }}; padding:12px; margin-bottom:10px; background:#f8fafc; border-radius:8px;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong>{{ strtoupper($lot->code) }}</strong>
                            @if($lot->type)
                                <span style="background:{{ $color }}22; color:{{ $color }}; font-size:10px; padding:2px 8px; border-radius:6px; font-weight:600; margin-left:6px;">
                                    {{ str_replace('_', ' ', $lot->type) }}
                                </span>
                            @endif
                        </div>
                        <small class="text-muted">{{ $lot->tf?->title }} · {{ $lot->tf?->site?->name }}</small>
                    </div>
                    <div style="font-size:11px; color:#64748b; margin-top:4px;">
                        @if($lot->date_prevue)    📅 Prévue : {{ $lot->date_prevue->format('d/m/Y') }} @endif
                        @if($lot->date_confirmee) &nbsp;✅ Confirmée : {{ $lot->date_confirmee->format('d/m/Y') }} @endif
                        @if($lot->dossier?->date_sortie) &nbsp;📁 Sortie : {{ $lot->dossier->date_sortie }} @endif
                        @if($lot->date_morcellement) &nbsp;✂️ Morcel. : {{ $lot->date_morcellement->format('d/m/Y') }} @endif
                    </div>
                    @if($lot->dossier)
                        <div class="prog-wrap mt-2">
                            <div class="prog-fill" style="width:{{ $prog }}%; background:{{ $progColor }};"></div>
                        </div>
                        <small style="color:{{ $progColor }}; font-weight:600;">{{ $prog }}%</small>
                    @endif
                </div>
            @empty
                <div class="alert alert-info mb-0">Aucun lot attribué. L'attribution se fait depuis la carte TF.</div>
            @endforelse
        </div>
    </div>
</div>

{{-- MODAL PAIEMENT --}}
<div class="modal-overlay" id="paiementOverlay" onclick="closePaiement()"></div>
<div class="modal-box" id="paiementModal">
    <h5>💰 Ajouter un paiement — <span id="dossierNom"></span></h5>
    <input type="number" id="montant" class="form-control mt-3" placeholder="Montant (FCFA)">
    <input type="date"   id="datePaie" class="form-control mt-2">
    <input type="text"   id="note"    class="form-control mt-2" placeholder="Note (optionnel)">
    <div class="d-flex justify-content-between mt-3">
        <button class="btn btn-secondary" onclick="closePaiement()">Annuler</button>
        <button class="btn btn-success"   onclick="savePaiement()">Ajouter</button>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentDossierId = null;

function showDossier(id, tab) {
    document.querySelectorAll('.dossier-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.dossier-tab').forEach(t => t.classList.remove('active'));
    document.getElementById(id).style.display = 'block';
    tab.classList.add('active');
}

function openPaiement(dossierId, nom) {
    currentDossierId = dossierId;
    document.getElementById('dossierNom').innerText = nom;
    document.getElementById('montant').value  = '';
    document.getElementById('datePaie').value = '';
    document.getElementById('note').value     = '';
    document.getElementById('paiementOverlay').style.display = 'block';
    document.getElementById('paiementModal').style.display   = 'block';
}

function closePaiement() {
    document.getElementById('paiementOverlay').style.display = 'none';
    document.getElementById('paiementModal').style.display   = 'none';
}

function savePaiement() {
    fetch(`/admin/paiements-dossier/${currentDossierId}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
        body: JSON.stringify({
            montant:       document.getElementById('montant').value,
            date_paiement: document.getElementById('datePaie').value,
            note:          document.getElementById('note').value,
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) location.reload();
        else alert(data.message || 'Erreur');
    });
}
</script>
@endsection