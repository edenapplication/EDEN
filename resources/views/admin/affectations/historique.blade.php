{{-- APRÈS --}}
@extends('admin.affectations.layout')
@section('content')

<style>
/* ═══ STATS ═══ */
.histo-stats { display:grid; grid-template-columns:repeat(auto-fit, minmax(180px, 1fr)); gap:14px; margin-bottom:20px; }
.histo-stat {
    background:white; border-radius:12px; padding:16px;
    box-shadow:0 2px 10px rgba(0,0,0,0.06);
    border-left:4px solid #1d4ed8;
    display:flex; align-items:center; gap:14px;
}
.histo-stat .ico { width:44px; height:44px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.histo-stat .lbl { font-size:10px; color:#64748b; text-transform:uppercase; font-weight:700; }
.histo-stat .val { font-size:20px; font-weight:800; color:#1e3a5f; line-height:1.2; }

/* ═══ FILTRES ═══ */
.histo-filters { background:white; border-radius:12px; padding:16px; box-shadow:0 2px 10px rgba(0,0,0,0.05); margin-bottom:16px; }

/* ═══ CONTENEUR PRINCIPAL ═══ */
.histo-wrap {
    background:white; border-radius:12px; padding:16px;
    box-shadow:0 2px 10px rgba(0,0,0,0.05);
}

/* ═══ SCROLL PERSONNALISÉ ═══ */
.histo-scroll {
    max-height: 700px;
    overflow-y: auto;
    padding-right: 6px;
}
.histo-scroll::-webkit-scrollbar { width: 8px; }
.histo-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
.histo-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
.histo-scroll::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

/* ═══ ENTRÉE HISTORIQUE ═══ */
.histo-entry {
    padding: 10px 12px;
    margin-bottom: 8px;
    background: #f8fafc;
    border-radius: 8px;
    font-size: 11px;
    transition: all 0.2s;
}
.histo-entry:hover {
    background: #f1f5f9;
    transform: translateX(2px);
}

.histo-entry .resume {
    color: #1e3a5f;
    font-weight: 600;
    line-height: 1.4;
    font-size: 11.5px;
}

.histo-entry .meta {
    color: #94a3b8;
    font-size: 9.5px;
    margin-top: 6px;
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    align-items: center;
}

.histo-entry .meta .user {
    background: #e0e7ff;
    color: #4f46e5;
    padding: 1px 7px;
    border-radius: 8px;
    font-weight: 700;
    font-size: 9px;
}

/* ═══ PILLS LOTS ═══ */
.lots-container {
    margin-top: 7px;
    display: flex;
    flex-wrap: wrap;
    gap: 4px;
    align-items: center;
}

/* Lot ACTUEL (vert) */
.lot-pill-actuel {
    display:inline-flex; align-items:center; gap:3px;
    background:#dcfce7; color:#166534;
    border:1px solid #86efac;
    padding:2px 8px; border-radius:6px;
    font-size:9.5px; font-weight:700;
}

/* Lot AVANT (rouge barré) */
.lot-pill-avant {
    display:inline-flex; align-items:center; gap:3px;
    background:#fee2e2; color:#991b1b;
    border:1px solid #fca5a5;
    padding:2px 8px; border-radius:6px;
    font-size:9.5px; font-weight:700;
    text-decoration: line-through;
}

/* Lot RETIRÉ / REFUSÉ (rouge barré) */
.lot-pill-retire {
    display:inline-flex; align-items:center; gap:3px;
    background:#fee2e2; color:#991b1b;
    border:1px solid #fca5a5;
    padding:2px 8px; border-radius:6px;
    font-size:9.5px; font-weight:700;
    text-decoration: line-through;
    opacity: 0.9;
}

.lot-pill-actuel .bloc-info,
.lot-pill-avant .bloc-info,
.lot-pill-retire .bloc-info {
    color:#64748b; font-weight:400; font-size:8.5px;
    text-decoration: none;
}

.lot-pill-avant .bloc-info,
.lot-pill-retire .bloc-info {
    color:#991b1b; opacity: 0.7;
}

/* Séparateur flèche */
.lot-arrow {
    color: #94a3b8;
    font-size: 10px;
    font-weight: 700;
    margin: 0 2px;
}

/* Badge aucune info */
.no-lot-badge {
    display:inline-flex; align-items:center; gap:3px;
    background:#fef3c7; color:#92400e;
    border:1px dashed #f59e0b;
    padding:2px 8px; border-radius:6px;
    font-size:9px; font-weight:700;
}

/* Label de section avant/après */
.lot-section-label {
    font-size: 8.5px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-right: 4px;
}
.lot-section-label.avant  { color: #991b1b; }
.lot-section-label.apres  { color: #166534; }
.lot-section-label.retire { color: #991b1b; }

/* ═══ FILTRES PAR TYPE ═══ */
.filtres-type {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
    margin-bottom: 14px;
    padding-bottom: 14px;
    border-bottom: 1px solid #e2e8f0;
}
.btn-filtre-type {
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    cursor: pointer;
    transition: all 0.2s;
}
.btn-filtre-type:hover { border-color: #4f46e5; color: #4f46e5; }
.btn-filtre-type.active {
    background: #4f46e5;
    color: white;
    border-color: #4f46e5;
}

/* Couleurs spécifiques pour Accepté / Refusé */
.btn-filtre-type[data-type="accepte"].active {
    background: #16a34a;
    border-color: #16a34a;
}
.btn-filtre-type[data-type="accepte"]:hover {
    border-color: #16a34a;
    color: #16a34a;
}
.btn-filtre-type[data-type="refuse"].active {
    background: #dc2626;
    border-color: #dc2626;
}
.btn-filtre-type[data-type="refuse"]:hover {
    border-color: #dc2626;
    color: #dc2626;
}

.empty-state { text-align:center; padding:60px 20px; color:#94a3b8; }
.empty-state .ico { font-size:56px; margin-bottom:14px; }
</style>

{{-- EN-TÊTE --}}
<div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
    <div>
        <h2 style="color:#1e3a5f;font-weight:800;margin:0;">📜 Historique des affectations</h2>
        <div style="font-size:13px;color:#64748b;">
            {{ number_format($stats['total_entrees'] ?? ($historiques->total() ?? 0), 0, ',', ' ') }} entrée(s) —
            toutes les actions sur les affectations
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('affectations.programmation') }}" class="btn btn-primary btn-sm">
            📅 Programmation
        </a>
        <a href="{{ route('affectations.liste') }}" class="btn btn-outline-secondary btn-sm">
            📋 Liste des affectations
        </a>
    </div>
</div>

{{-- STATISTIQUES --}}
<div class="histo-stats">
    <div class="histo-stat" style="border-left-color:#1d4ed8;">
        <div class="ico" style="background:#dbeafe;color:#1d4ed8;">📋</div>
        <div>
            <div class="lbl">Total actions</div>
            <div class="val">{{ number_format($stats['total_entrees'] ?? 0, 0, ',', ' ') }}</div>
        </div>
    </div>
    <div class="histo-stat" style="border-left-color:#16a34a;">
        <div class="ico" style="background:#dcfce7;color:#16a34a;">📦</div>
        <div>
            <div class="lbl">Affectations</div>
            <div class="val">{{ number_format($stats['affectation_lot'] ?? 0, 0, ',', ' ') }}</div>
        </div>
    </div>
    <div class="histo-stat" style="border-left-color:#f59e0b;">
        <div class="ico" style="background:#fef3c7;color:#f59e0b;">✏️</div>
        <div>
            <div class="lbl">Modifications</div>
            <div class="val">{{ number_format($stats['modification_affectation'] ?? 0, 0, ',', ' ') }}</div>
        </div>
    </div>
    <div class="histo-stat" style="border-left-color:#dc2626;">
        <div class="ico" style="background:#fee2e2;color:#dc2626;">↩️</div>
        <div>
            <div class="lbl">Annulations</div>
            <div class="val">{{ number_format($stats['annulation_affectation'] ?? 0, 0, ',', ' ') }}</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<div class="histo-filters">
    <form method="GET" action="{{ route('affectations.historique') }}">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;">🔍 Recherche</label>
                <input type="text" name="q" class="form-control form-control-sm"
                       placeholder="Nom, téléphone, lot..."
                       value="{{ request('q') }}">
            </div>
            <div class="col-md-2">
                <label style="font-size:11px;font-weight:700;color:#64748b;">🏢 Grand Site</label>
                <select name="grand_site_id" class="form-control form-control-sm">
                    <option value="">Tous</option>
                    @foreach($grandSites as $gs)
                        <option value="{{ $gs->id }}" {{ request('grand_site_id') == $gs->id ? 'selected' : '' }}>{{ $gs->nom }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label style="font-size:11px;font-weight:700;color:#64748b;">👷 Géomètre</label>
                <select name="geometre_id" class="form-control form-control-sm">
                    <option value="">Tous</option>
                    @foreach($geometres as $geo)
                        <option value="{{ $geo->id }}" {{ request('geometre_id') == $geo->id ? 'selected' : '' }}>{{ $geo->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Du</label>
                <input type="date" name="du" class="form-control form-control-sm" value="{{ request('du') }}">
            </div>
            <div class="col-md-1">
                <label style="font-size:11px;font-weight:700;color:#64748b;">📅 Au</label>
                <input type="date" name="au" class="form-control form-control-sm" value="{{ request('au') }}">
            </div>
            <div class="col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-fill">🔍 Filtrer</button>
                <a href="{{ route('affectations.historique') }}" class="btn btn-outline-secondary btn-sm">✖</a>
            </div>
        </div>
    </form>
</div>

{{-- CONTENU PRINCIPAL --}}
<div class="histo-wrap">
    @if($historiques->count() > 0)

        {{-- Filtres par type d'action --}}
        <div class="filtres-type">
            <button type="button" class="btn-filtre-type active" data-type="tous" onclick="filtrerType('tous', this)">
                📌 Tous
            </button>
            <button type="button" class="btn-filtre-type" data-type="accepte" onclick="filtrerType('accepte', this)">
                ✅ Acceptées
            </button>
            <button type="button" class="btn-filtre-type" data-type="refuse" onclick="filtrerType('refuse', this)">
                ❌ Refusées
            </button>
            <button type="button" class="btn-filtre-type" data-type="affectation_lot" onclick="filtrerType('affectation_lot', this)">
                📦 Affectations
            </button>
            <button type="button" class="btn-filtre-type" data-type="modification_affectation" onclick="filtrerType('modification_affectation', this)">
                ✏️ Modifications
            </button>
            <button type="button" class="btn-filtre-type" data-type="annulation_affectation" onclick="filtrerType('annulation_affectation', this)">
                ↩️ Annulations
            </button>
        </div>

        {{-- Liste des entrées --}}
        <div class="histo-scroll" id="histoScroll">
            @foreach($historiques as $h)
                @php
                    $iconeH = match($h->type_action) {
                        'ajout_beneficiaire'         => '➕',
                        'modification_beneficiaire'  => '✏️',
                        'suppression_beneficiaire'   => '🗑️',
                        'affectation_lot'            => '📦',
                        'modification_affectation'   => '✏️',
                        'annulation_affectation'     => '↩️',
                        'etape_beneficiaire'         => '📊',
                        default                      => '📌',
                    };
                    $couleurH = match($h->type_action) {
                        'ajout_beneficiaire'         => '#16a34a',
                        'modification_beneficiaire'  => '#f59e0b',
                        'suppression_beneficiaire'   => '#dc2626',
                        'affectation_lot'            => '#0d6efd',
                        'modification_affectation'   => '#f59e0b',
                        'annulation_affectation'     => '#7c3aed',
                        'etape_beneficiaire'         => '#0891b2',
                        default                      => '#64748b',
                    };

                    // ✅ Extraction des données JSON
                    $avantJson = $h->donnees_avant ?? [];
                    $apresJson = $h->donnees_apres ?? [];
                    if (is_string($avantJson)) $avantJson = json_decode($avantJson, true) ?: [];
                    if (is_string($apresJson)) $apresJson = json_decode($apresJson, true) ?: [];
                    if (!is_array($avantJson)) $avantJson = [];
                    if (!is_array($apresJson)) $apresJson = [];

                    // Extraire les lots AVANT et APRÈS
                    $lotsAvant = [];
                    $lotsApres = [];

                    // ── Source 1 : structure multi-lots ──
                    if (!empty($avantJson['lots']) && is_array($avantJson['lots'])) {
                        foreach ($avantJson['lots'] as $l) {
                            if (!is_array($l)) continue;
                            $lotsAvant[] = [
                                'numero' => $l['numero'] ?? '?',
                                'bloc'   => $l['bloc']   ?? null,
                            ];
                        }
                    }
                    if (!empty($apresJson['lots']) && is_array($apresJson['lots'])) {
                        foreach ($apresJson['lots'] as $l) {
                            if (!is_array($l)) continue;
                            $lotsApres[] = [
                                'numero' => $l['numero'] ?? '?',
                                'bloc'   => $l['bloc']   ?? null,
                            ];
                        }
                    }

                    // ── Source 2 : structure single-lot ──
                    if (empty($lotsAvant) && !empty($avantJson['lot_num'])) {
                        $lotsAvant[] = [
                            'numero' => $avantJson['lot_num'],
                            'bloc'   => $avantJson['bloc'] ?? null,
                        ];
                    }
                    if (empty($lotsApres) && !empty($apresJson['lot_num'])) {
                        $lotsApres[] = [
                            'numero' => $apresJson['lot_num'],
                            'bloc'   => $apresJson['bloc'] ?? null,
                        ];
                    }

                    // ── Source 3 : point_depart / point_arrivee ──
                    if (empty($lotsAvant) && !empty($avantJson['point_depart']['lot'])) {
                        $nums = preg_split('/\s*,\s*/', $avantJson['point_depart']['lot']);
                        foreach ($nums as $n) {
                            if (trim($n) !== '') $lotsAvant[] = ['numero' => trim($n), 'bloc' => null];
                        }
                    }
                    if (empty($lotsApres) && !empty($apresJson['point_arrivee']['lot'])) {
                        $nums = preg_split('/\s*,\s*/', $apresJson['point_arrivee']['lot']);
                        foreach ($nums as $n) {
                            if (trim($n) !== '') $lotsApres[] = ['numero' => trim($n), 'bloc' => null];
                        }
                    }

                    // ── Fallback regex sur le résumé ──
                    if (empty($lotsAvant) && empty($lotsApres) && $h->resume) {
                        if (preg_match_all('/[Ll]ot\s+([A-Za-z0-9\-]+)/u', $h->resume, $m)) {
                            foreach ($m[1] as $num) {
                                $lotsApres[] = ['numero' => $num, 'bloc' => null];
                            }
                        }
                    }

                    // ═══════════════════════════════════════════════════════════════
                    // ✅ DÉTECTION DU TYPE D'AFFICHAGE
                    // ═══════════════════════════════════════════════════════════════
                    $isModification = $h->type_action === 'modification_affectation';
                    $isAnnulation   = $h->type_action === 'annulation_affectation';
                    $isAffectation  = $h->type_action === 'affectation_lot';

                    // Refus = annulation_affectation avec statut_acceptation='refuse'
                    $isRefus = $isAnnulation
                        && (
                            ($apresJson['statut_acceptation'] ?? null) === 'refuse'
                            || ($apresJson['statut'] ?? null) === 'annule'
                            || str_contains($h->resume ?? '', 'REFUSÉE')
                            || str_contains($h->resume ?? '', 'Refusée')
                            || str_contains($h->resume ?? '', 'refusée')
                        );

                    // ✅ Sous-type pour les filtres
                    $sousType = 'autre';
                    if ($isRefus) {
                        $sousType = 'refuse';
                    } elseif ($isAnnulation) {
                        $sousType = 'annule';
                    } elseif ($isModification) {
                        $sousType = 'modifie';
                    } elseif ($isAffectation) {
                        // Distinguer acceptation vs simple affectation
                        $resumeUp = strtoupper($h->resume ?? '');
                        if (str_contains($resumeUp, 'ACCEPTÉE') || str_contains($resumeUp, 'ACCEPTEE')) {
                            $sousType = 'accepte';
                        } else {
                            $sousType = 'affecte';
                        }
                    }

                    // ✅ RÈGLE MÉTIER : affecter les lots aux bons slots
                    $lotsAAfficherVert  = [];
                    $lotsAAfficherRouge = [];

                    if ($isModification) {
                        $lotsAAfficherRouge = $lotsAvant;
                        $lotsAAfficherVert  = $lotsApres;
                    } elseif ($isRefus) {
                        $lotsAAfficherRouge = !empty($lotsApres) ? $lotsApres : $lotsAvant;
                    } elseif ($isAnnulation) {
                        $lotsAAfficherRouge = !empty($lotsApres) ? $lotsApres : $lotsAvant;
                    } elseif ($isAffectation) {
                        $lotsAAfficherVert = !empty($lotsApres) ? $lotsApres : $lotsAvant;
                    } else {
                        if (!empty($lotsApres))      $lotsAAfficherVert  = $lotsApres;
                        elseif (!empty($lotsAvant))  $lotsAAfficherRouge = $lotsAvant;
                    }
                @endphp

                <div class="histo-entry"
                     data-type="{{ $h->type_action }}"
                     data-sous-type="{{ $sousType }}"
                     style="border-left:4px solid {{ $couleurH }};">

                    <div class="resume">
                        {{ $iconeH }} {{ $h->resume }}
                    </div>

                    {{-- ═══════════════════════════════════════════════ --}}
                    {{-- AFFICHAGE DES LOTS                                --}}
                    {{-- ═══════════════════════════════════════════════ --}}

                    @if($isModification && (!empty($lotsAAfficherRouge) || !empty($lotsAAfficherVert)))
                        {{-- ✏️ MODIFICATION : Avant (rouge barré) → Après (vert) --}}
                        <div class="lots-container">
                            @if(!empty($lotsAAfficherRouge))
                                <span class="lot-section-label avant">AVANT :</span>
                                @foreach($lotsAAfficherRouge as $lot)
                                    <span class="lot-pill-avant">
                                        📦 Lot {{ $lot['numero'] }}
                                        @if(!empty($lot['bloc']))
                                            <span class="bloc-info">({{ $lot['bloc'] }})</span>
                                        @endif
                                    </span>
                                @endforeach
                            @endif

                            @if(!empty($lotsAAfficherRouge) && !empty($lotsAAfficherVert))
                                <span class="lot-arrow">→</span>
                            @endif

                            @if(!empty($lotsAAfficherVert))
                                <span class="lot-section-label apres">APRÈS :</span>
                                @foreach($lotsAAfficherVert as $lot)
                                    <span class="lot-pill-actuel">
                                        📦 Lot {{ $lot['numero'] }}
                                        @if(!empty($lot['bloc']))
                                            <span class="bloc-info">({{ $lot['bloc'] }})</span>
                                        @endif
                                    </span>
                                @endforeach
                            @endif
                        </div>

                    @elseif(($isRefus || $isAnnulation) && !empty($lotsAAfficherRouge))
                        {{-- ❌ REFUS / ANNULATION : lots en rouge barré --}}
                        <div class="lots-container">
                            <span class="lot-section-label retire">
                                {{ $isRefus ? 'REFUSÉ :' : 'RETIRÉ :' }}
                            </span>
                            @foreach($lotsAAfficherRouge as $lot)
                                <span class="lot-pill-retire">
                                    📦 Lot {{ $lot['numero'] }}
                                    @if(!empty($lot['bloc']))
                                        <span class="bloc-info">({{ $lot['bloc'] }})</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>

                    @elseif(!empty($lotsAAfficherVert))
                        {{-- 📦 AFFECTATION / ACCEPTATION : lots en vert --}}
                        <div class="lots-container">
                            @foreach($lotsAAfficherVert as $lot)
                                <span class="lot-pill-actuel">
                                    📦 Lot {{ $lot['numero'] }}
                                    @if(!empty($lot['bloc']))
                                        <span class="bloc-info">({{ $lot['bloc'] }})</span>
                                    @endif
                                </span>
                            @endforeach
                        </div>

                    @else
                        <div class="lots-container">
                            <span class="no-lot-badge">⚠️ Aucun lot précisé</span>
                        </div>
                    @endif

                    {{-- ═══════════════════════════════════════════════ --}}
                    {{-- MÉTADONNÉES                                       --}}
                    {{-- ═══════════════════════════════════════════════ --}}
                    <div class="meta">
                        <span>📅 {{ $h->created_at->format('d/m/Y H:i') }}</span>
                        @if($h->user)
                            <span class="user">👤 {{ $h->user->name }}</span>
                        @endif
                        @if($h->dossier?->client)
                            <span style="background:#eff6ff;color:#1d4ed8;padding:1px 7px;border-radius:8px;font-weight:700;font-size:8.5px;">
                                📂 {{ $h->dossier->client->name }}
                            </span>
                        @endif
                        @if($h->beneficiaire)
                            <span style="background:#faf5ff;color:#7c3aed;padding:1px 7px;border-radius:8px;font-weight:700;font-size:8.5px;">
                                👥 {{ $h->beneficiaire->nom }}
                            </span>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        @if($historiques->hasPages())
        <div style="padding:14px 0;border-top:1px solid #f1f5f9;margin-top:14px;">
            {{ $historiques->links() }}
        </div>
        @endif

    @else
    <div class="empty-state">
        <div class="ico">📭</div>
        <div style="font-weight:700;font-size:15px;color:#475569;">Aucun historique d'affectation</div>
        <div style="font-size:12px;margin-top:6px;">Les actions sur les affectations apparaîtront ici.</div>
    </div>
    @endif
</div>

@endsection

@section('scripts')
<script>
function filtrerType(type, btn) {
    document.querySelectorAll('.btn-filtre-type').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');

    const entries = document.querySelectorAll('.histo-entry');
    entries.forEach(entry => {
        const dataType     = entry.dataset.type;
        const dataSousType = entry.dataset.sousType;

        let visible = false;

        if (type === 'tous') {
            visible = true;
        } else if (type === 'accepte') {
            // ✅ Acceptations uniquement
            visible = dataSousType === 'accepte';
        } else if (type === 'refuse') {
            // ❌ Refus uniquement
            visible = dataSousType === 'refuse';
        } else if (type === 'affectation_lot') {
            // 📦 Toutes les affectations (affectation simple + acceptation)
            visible = dataType === 'affectation_lot';
        } else if (type === 'modification_affectation') {
            // ✏️ Modifications
            visible = dataType === 'modification_affectation';
        } else if (type === 'annulation_affectation') {
            // ↩️ Toutes les annulations (annulation + refus)
            visible = dataType === 'annulation_affectation';
        } else {
            visible = dataType === type;
        }

        entry.style.display = visible ? '' : 'none';
    });
}
</script>
@endsection