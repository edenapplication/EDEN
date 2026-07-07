@extends('admin.layout')
@section('content')

<style>
.section-card { background:white; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,0.06); padding:20px; margin-bottom:16px; }
.section-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:14px; }
.info-row { display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #f8fafc; font-size:13px; }
.info-row span:first-child { color:#64748b; }
.info-row span:last-child  { font-weight:600; color:#1e3a5f; }
.pay-row { display:flex; justify-content:space-between; font-size:12px; padding:4px 0; border-bottom:1px solid #f8fafc; }
.pay-amt { font-weight:700; }
.prog-wrap { height:8px; background:#e2e8f0; border-radius:4px; overflow:hidden; margin-top:4px; }
.prog-fill  { height:100%; border-radius:4px; }
.dossier-tab { cursor:pointer; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600; background:#f1f5f9; color:#64748b; }
.dossier-tab.active { background:#0d6efd; color:white; }
.modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:9998; }
.modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:20px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,0.2); z-index:9999; width:400px; }

/* ✅ Blocs de paiement alignés */
.paiement-blocs { display:grid; grid-template-columns:repeat(3, 1fr); gap:14px; margin-top:16px; }
.paiement-bloc {
    border-radius:12px; padding:16px;
    border:1px solid #e2e8f0;
    display:flex; flex-direction:column;
}
.paiement-bloc.bloc-dossier   { background:#eff6ff; border-left:4px solid #0d6efd; }
.paiement-bloc.bloc-technique { background:#fff7ed; border-left:4px solid #ea580c; }
.paiement-bloc.bloc-morcel    { background:#fefce8; border-left:4px solid #ca8a04; }

.paiement-bloc h6 { font-weight:700; font-size:13px; margin-bottom:10px; display:flex; align-items:center; justify-content:space-between; }
.btn-add-pay {
    border:none; color:white; border-radius:8px;
    padding:5px 12px; font-size:11px; font-weight:600; cursor:pointer;
}
.btn-add-pay.dossier   { background:#0d6efd; }
.btn-add-pay.technique { background:#ea580c; }
.btn-add-pay.morcel    { background:#ca8a04; }

.pay-total-row { display:flex; justify-content:space-between; font-size:12px; padding:4px 0; }
.pay-historique { margin-top:10px; max-height:160px; overflow-y:auto; }

@media (max-width: 992px) {
    .paiement-blocs { grid-template-columns:1fr; }
}
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

    <form action="{{ route('suivi-client.dossiers.destroy', $dossier->id) }}"
      method="POST"
      onsubmit="return confirm('Supprimer ce dossier ?')"
      style="display:inline;">
    @csrf
    @method('DELETE')

    <button class="btn btn-danger btn-sm">
        🗑 Supprimer
    </button>
</form>
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

                {{-- ============================================================
                     ✅ TROIS BLOCS DE PAIEMENT DISTINCTS
                     ============================================================ --}}
                {{-- Dans le show.blade.php, section blocs paiements --}}

@php
    $totalDossier   = $dossier->paiements->sum('montant');
    $prixRef        = $dossier->prix_superficie ?? 0;
    $resteDossier   = max(0, $prixRef - $totalDossier);
    $prixTech       = $dossier->prix_technique ?? 0;
    $prixMorcel     = $dossier->prix_morcellement ?? 0;
    $totalTechnique = $dossier->paiementsTechniques->sum('montant');
    $totalMorcel    = $dossier->paiementsMorcellements->sum('montant');
    $resteTech      = max(0, $prixTech - $totalTechnique);
    $resteMorcel    = max(0, $prixMorcel - $totalMorcel);
@endphp

{{-- Prix de référence configurables --}}
<div style="background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:14px;border:1px solid #e2e8f0;">
    <div style="font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;margin-bottom:10px;">
        💰 Prix de référence du dossier
    </div>
    <div class="row g-2">
        <div class="col-md-4">
            <label style="font-size:11px;color:#64748b;">Prix superficie (FCFA)</label>
            <div style="display:flex;gap:6px;">
                <input type="number" id="prix-superficie-{{ $dossier->id }}"
                       class="form-control form-control-sm"
                       value="{{ $prixRef }}" placeholder="0">
                <button onclick="majPrix({{ $dossier->id }})"
                        class="btn btn-primary btn-sm" style="font-size:11px;">✓</button>
            </div>
        </div>
        <div class="col-md-4">
            <label style="font-size:11px;color:#ea580c;">Prix technique (FCFA)</label>
            <div style="display:flex;gap:6px;">
                <input type="number" id="prix-technique-{{ $dossier->id }}"
                       class="form-control form-control-sm"
                       value="{{ $prixTech }}" placeholder="0">
                <button onclick="majPrix({{ $dossier->id }})"
                        class="btn btn-warning btn-sm" style="font-size:11px;">✓</button>
            </div>
        </div>
        <div class="col-md-4">
            <label style="font-size:11px;color:#ca8a04;">Prix morcellement (FCFA)</label>
            <div style="display:flex;gap:6px;">
                <input type="number" id="prix-morcellement-{{ $dossier->id }}"
                       class="form-control form-control-sm"
                       value="{{ $prixMorcel }}" placeholder="0">
                <button onclick="majPrix({{ $dossier->id }})"
                        class="btn btn-sm" style="background:#ca8a04;color:white;font-size:11px;">✓</button>
            </div>
        </div>
    </div>
</div>

{{-- 3 BLOCS PAIEMENTS --}}
<div class="paiement-blocs">

    {{-- BLOC DOSSIER --}}
    <div class="paiement-bloc bloc-dossier">
        <h6 style="color:#0d6efd;">
            📁 Paiement Dossier
            <button class="btn-add-pay dossier"
                    onclick="openPaiement('dossier', {{ $dossier->id }}, '{{ addslashes($dossier->nom_dossier) }}')">
                + Ajouter
            </button>
        </h6>
        @if($prixRef > 0)
        <div class="pay-total-row">
            <span style="color:#64748b;">Référence</span>
            <span style="font-weight:600;">{{ number_format($prixRef, 0, ',', ' ') }} FCFA</span>
        </div>
        @endif
        <div class="pay-total-row">
            <span style="color:#64748b;">Payé</span>
            <span class="pay-amt" style="color:#16a34a;">{{ number_format($totalDossier, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="pay-total-row">
            <span style="color:#64748b;">Reste</span>
            <span style="font-weight:700;color:{{ $resteDossier > 0 ? '#dc2626' : '#16a34a' }};">
                {{ number_format($resteDossier, 0, ',', ' ') }} FCFA
            </span>
        </div>
        @if($prixRef > 0)
        <div style="height:6px;background:#e2e8f0;border-radius:3px;margin:8px 0;">
            @php $pct = $prixRef > 0 ? min(100, round(($totalDossier/$prixRef)*100)) : 0; @endphp
            <div style="width:{{ $pct }}%;height:100%;background:#0d6efd;border-radius:3px;"></div>
        </div>
        <div style="font-size:10px;text-align:right;color:#0d6efd;font-weight:700;">{{ $pct }}%</div>
        @endif
        <div class="pay-historique">
            @forelse($dossier->paiements->sortByDesc('date_paiement') as $p)
            <div class="pay-row">
                <span>{{ $p->date_paiement }} {{ $p->note ? '— '.$p->note : '' }}</span>
                <div style="display:flex;align-items:center;gap:6px;">
                    <span class="pay-amt" style="color:#16a34a;">{{ number_format($p->montant, 0, ',', ' ') }}</span>
                    {{-- ✅ Supprimer paiement --}}
                    <form action="{{ route('paiements-dossiers.destroy', $p->id) }}" method="POST" style="display:inline;">
    @csrf
    @method('DELETE')

    <button type="submit"
        onclick="return confirm('Supprimer ce paiement ?')"
        style="background:none;border:none;color:#dc2626;cursor:pointer;">
        🗑
    </button>
</form>
                        </div>
            </div>
            @empty
            <div style="color:#94a3b8;font-size:11px;">Aucun paiement</div>
            @endforelse
        </div>
    </div>

    {{-- BLOC TECHNIQUE --}}
    <div class="paiement-bloc bloc-technique">
        <h6 style="color:#ea580c;">
            🛠️ Paiement Technique
            <button class="btn-add-pay technique"
                    onclick="openPaiement('technique', {{ $dossier->id }}, '{{ addslashes($dossier->nom_dossier) }}')">
                + Ajouter
            </button>
        </h6>
        @if($prixTech > 0)
        <div class="pay-total-row">
            <span style="color:#64748b;">Référence</span>
            <span style="font-weight:600;">{{ number_format($prixTech, 0, ',', ' ') }} FCFA</span>
        </div>
        @endif
        <div class="pay-total-row">
            <span style="color:#64748b;">Payé</span>
            <span class="pay-amt" style="color:#ea580c;">{{ number_format($totalTechnique, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="pay-total-row">
            <span style="color:#64748b;">Reste</span>
            <span style="font-weight:700;color:{{ $resteTech > 0 ? '#dc2626' : '#16a34a' }};">
                {{ number_format($resteTech, 0, ',', ' ') }} FCFA
            </span>
        </div>
        @if($prixTech > 0)
        <div style="height:6px;background:#e2e8f0;border-radius:3px;margin:8px 0;">
            @php $pctT = $prixTech > 0 ? min(100, round(($totalTechnique/$prixTech)*100)) : 0; @endphp
            <div style="width:{{ $pctT }}%;height:100%;background:#ea580c;border-radius:3px;"></div>
        </div>
        <div style="font-size:10px;text-align:right;color:#ea580c;font-weight:700;">{{ $pctT }}%</div>
        @endif
        <div class="pay-historique">
            @forelse($dossier->paiementsTechniques->sortByDesc('date_paiement') as $p)
            <div class="pay-row">
                <span>{{ $p->date_paiement }} {{ $p->note ? '— '.$p->note : '' }}</span>
                <div style="display:flex;align-items:center;gap:6px;">
                    <span class="pay-amt" style="color:#ea580c;">{{ number_format($p->montant, 0, ',', ' ') }}</span>
                    <form action="{{ route('paiements-techniques.destroy', $p->id) }}" method="POST" style="display:inline;">
    @csrf
    @method('DELETE')

    <button type="submit"
        onclick="return confirm('Supprimer ce paiement ?')"
        style="background:none;border:none;color:#dc2626;cursor:pointer;">
        🗑
    </button>
</form>
                        </div>
            </div>
            @empty
            <div style="color:#94a3b8;font-size:11px;">Aucun paiement</div>
            @endforelse
        </div>
    </div>

    {{-- BLOC MORCELLEMENT --}}
    <div class="paiement-bloc bloc-morcel">
        <h6 style="color:#ca8a04;">
            ✂️ Paiement Morcellement
            <button class="btn-add-pay morcel"
                    onclick="openPaiement('morcellement', {{ $dossier->id }}, '{{ addslashes($dossier->nom_dossier) }}')">
                + Ajouter
            </button>
        </h6>
        @if($prixMorcel > 0)
        <div class="pay-total-row">
            <span style="color:#64748b;">Référence</span>
            <span style="font-weight:600;">{{ number_format($prixMorcel, 0, ',', ' ') }} FCFA</span>
        </div>
        @endif
        <div class="pay-total-row">
            <span style="color:#64748b;">Payé</span>
            <span class="pay-amt" style="color:#ca8a04;">{{ number_format($totalMorcel, 0, ',', ' ') }} FCFA</span>
        </div>
        <div class="pay-total-row">
            <span style="color:#64748b;">Reste</span>
            <span style="font-weight:700;color:{{ $resteMorcel > 0 ? '#dc2626' : '#16a34a' }};">
                {{ number_format($resteMorcel, 0, ',', ' ') }} FCFA
            </span>
        </div>
        @if($prixMorcel > 0)
        <div style="height:6px;background:#e2e8f0;border-radius:3px;margin:8px 0;">
            @php $pctM = $prixMorcel > 0 ? min(100, round(($totalMorcel/$prixMorcel)*100)) : 0; @endphp
            <div style="width:{{ $pctM }}%;height:100%;background:#ca8a04;border-radius:3px;"></div>
        </div>
        <div style="font-size:10px;text-align:right;color:#ca8a04;font-weight:700;">{{ $pctM }}%</div>
        @endif
        <div class="pay-historique">
            @forelse($dossier->paiementsMorcellements->sortByDesc('date_paiement') as $p)
            <div class="pay-row">
                <span>{{ $p->date_paiement }} {{ $p->note ? '— '.$p->note : '' }}</span>
                <div style="display:flex;align-items:center;gap:6px;">
                    <span class="pay-amt" style="color:#ca8a04;">{{ number_format($p->montant, 0, ',', ' ') }}</span>
                    <form action="{{ route('paiements-morcellements.destroy', $p->id) }}" method="POST" style="display:inline;">
    @csrf
    @method('DELETE')

    <button type="submit"
        onclick="return confirm('Supprimer ce paiement ?')"
        style="background:none;border:none;color:#dc2626;cursor:pointer;">
        🗑
    </button>
</form>
                        </div>
            </div>
            @empty
            <div style="color:#94a3b8;font-size:11px;">Aucun paiement</div>
            @endforelse
        </div>
    </div>

</div>
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
                    $typeColors = ['implantation_prevue'=>'#7c3aed','deja_implante'=>'#16a34a','dossier_technique'=>'#dc2626','morcellement'=>'#ca8a04'];
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

{{-- ============================================================
     MODAL PAIEMENT — réutilisé pour les 3 types
     ============================================================ --}}
<div class="modal-overlay" id="paiementOverlay" onclick="closePaiement()"></div>
<div class="modal-box" id="paiementModal">
    <h5 id="paiementTitre">💰 Ajouter un paiement — <span id="dossierNom"></span></h5>
    <input type="number" id="montant"  class="form-control mt-3" placeholder="Montant (FCFA)">
    <input type="date"   id="datePaie" class="form-control mt-2">
    <input type="text"   id="note"     class="form-control mt-2" placeholder="Note (optionnel)">
    <div class="d-flex justify-content-between mt-3">
        <button class="btn btn-secondary" onclick="closePaiement()">Annuler</button>
        <button class="btn btn-success"   onclick="savePaiement()">Ajouter</button>
    </div>
</div>

@endsection

@section('scripts')
<script>
let currentDossierId = null;
let currentType       = 'dossier'; // dossier | technique | morcellement

const TYPE_CONFIG = {
    dossier:      { url: '/admin/paiements-dossier',      titre: '📁 Paiement Parcelle — ',      btnColor: 'btn-success' },
    technique:    { url: '/admin/paiements-technique',    titre: '🛠️ Paiement Dossier Technique — ',    btnColor: 'btn-success' },
    morcellement: { url: '/admin/paiements-morcellement', titre: '✂️ Paiement Morcellement — ',  btnColor: 'btn-success' },
};

function showDossier(id, tab) {
    document.querySelectorAll('.dossier-panel').forEach(p => p.style.display = 'none');
    document.querySelectorAll('.dossier-tab').forEach(t => t.classList.remove('active'));
    document.getElementById(id).style.display = 'block';
    tab.classList.add('active');
}

function openPaiement(type, dossierId, nom) {
    currentDossierId = dossierId;
    currentType       = type;
    const cfg = TYPE_CONFIG[type];

    document.getElementById('paiementTitre').innerHTML = cfg.titre + '<span id="dossierNom">' + nom + '</span>';
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
    const cfg     = TYPE_CONFIG[currentType];
    const montant = document.getElementById('montant').value;
    const date    = document.getElementById('datePaie').value;

    if (!montant || !date) { alert('Montant et date obligatoires.'); return; }

    // ✅ Fermer le modal AVANT le loader
    closePaiement();

    // ✅ Afficher loader
    if (window.EdenLoader) window.EdenLoader.show();

    fetch(`${cfg.url}/${currentDossierId}`, {
        method:  'POST',
        headers: {
            'Content-Type':  'application/json',
            'X-CSRF-TOKEN':  document.querySelector('meta[name="csrf-token"]')?.content
                             || '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            montant:       parseFloat(montant),
            date_paiement: date,
            note:          document.getElementById('note').value,
        }),
    })
    .then(r => {
        if (!r.ok) throw new Error('Erreur HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            if (window.EdenLoader) window.EdenLoader.hide();
            alert(data.message || 'Erreur lors du paiement');
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        alert('Erreur réseau : ' + e.message);
    });
}

function majPrix(dossierId) {
    const superficie   = document.getElementById('prix-superficie-'   + dossierId)?.value ?? '';
    const technique    = document.getElementById('prix-technique-'    + dossierId)?.value ?? '';
    const morcellement = document.getElementById('prix-morcellement-' + dossierId)?.value ?? '';

    // ✅ Afficher loader
    if (window.EdenLoader) window.EdenLoader.show();

    fetch(`/admin/dossiers/${dossierId}/maj-prix`, {
        method:  'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                            || CSRF,
        },
        body: JSON.stringify({
            prix_superficie:   superficie   !== '' ? parseFloat(superficie)   : null,
            prix_technique:    technique    !== '' ? parseFloat(technique)    : null,
            prix_morcellement: morcellement !== '' ? parseFloat(morcellement) : null,
        }),
    })
    .then(r => {
        if (!r.ok) throw new Error('Erreur HTTP ' + r.status);
        return r.json();
    })
    .then(data => {
        if (window.EdenLoader) window.EdenLoader.hide();

        if (data.success) {
            const notif = document.createElement('div');
            notif.innerText = '✅ Prix mis à jour';
            notif.style.cssText = [
                'position:fixed','top:80px','right:20px',
                'background:#16a34a','color:white',
                'padding:10px 18px','border-radius:10px',
                'z-index:999999','font-weight:700','font-size:13px',
                'box-shadow:0 4px 16px rgba(0,0,0,0.15)',
                'transition:opacity 0.3s',
            ].join(';');
            document.body.appendChild(notif);
            setTimeout(() => {
                notif.style.opacity = '0';
                setTimeout(() => notif.remove(), 300);
            }, 2200);
        } else {
            alert(data.message || 'Erreur lors de la mise à jour des prix');
        }
    })
    .catch(e => {
        if (window.EdenLoader) window.EdenLoader.hide();
        alert('Erreur réseau : ' + e.message);
    });
}

// ✅ Supprimer un paiement
function supprimerPaiement(type, paiementId, btn) {

    alert("STEP 1 OK");

    console.log("TYPE RAW =", type);
    console.log("PAIEMENT ID =", paiementId);

    if (!confirm("Supprimer ?")) {
        alert("STOP CONFIRM");
        return;
    }

    alert("STEP 2 OK (après confirm)");

    const urls = {
        dossier: "OK dossier",
        technique: "OK technique",
        morcellement: "OK morcellement",
    };

    alert("TYPE VALUE = " + type);

    const url = urls[type];

    alert("URL = " + url);
}


</script>
@endsection