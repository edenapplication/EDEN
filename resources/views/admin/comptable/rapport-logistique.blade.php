@extends('layouts.comptable')

@section('title', 'Rapport Logistique')

@section('styles')
<style>
    .rapport-header {
        background: linear-gradient(135deg, #7c3aed 0%, #a855f7 100%);
        color: white; padding: 24px 28px; border-radius: 14px;
        margin-bottom: 20px; box-shadow: 0 4px 16px rgba(124,58,237,0.25);
    }
    .rapport-header h2 { font-weight: 800; margin: 0; }
    .rapport-header p { opacity: 0.9; margin: 4px 0 0 0; font-size: 13px; }

    .filter-section {
        background: white; border-radius: 12px; padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 20px;
    }
    .filter-section h6 {
        font-weight: 700; color: #1e3a5f;
        border-bottom: 2px solid #e2e8f0; padding-bottom: 6px; margin-bottom: 12px;
    }

    .kpi-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 10px; margin-bottom: 20px;
    }
    .kpi-box {
        background: white; border-radius: 10px; padding: 14px; text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.06); border-top: 3px solid #7c3aed;
    }
    .kpi-box .val { font-size: 22px; font-weight: 800; color: #1e3a5f; }
    .kpi-box .lbl { font-size: 10px; color: #64748b; text-transform: uppercase; font-weight: 600; margin-top: 2px; }

    .rapport-table-wrap { overflow-x: auto; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.06); }
    .rapport-table { width: 100%; border-collapse: collapse; font-size: 12px; background: white; }
    .rapport-table thead tr { background: #7c3aed; color: white; }
    .rapport-table thead th { padding: 10px 8px; font-weight: 600; white-space: nowrap; text-align: left; }
    .rapport-table tbody tr:nth-child(even) { background: #f8fafc; }
    .rapport-table tbody tr:hover { background: #faf5ff; }
    .rapport-table tbody td { padding: 8px; border-bottom: 1px solid #e2e8f0; }
    .rapport-table tfoot td { background: #1e3a5f; color: white; font-weight: 700; padding: 10px 8px; }

    .recap-section {
        background: white; border-radius: 12px; padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06); margin-bottom: 20px;
    }
    .recap-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; }
    .recap-card { border-radius: 12px; padding: 16px; border-left: 4px solid; }
    .recap-card.paye     { background: #f0fdf4; border-left-color: #16a34a; }
    .recap-card.partiel  { background: #fffbeb; border-left-color: #f59e0b; }
    .recap-card.nonpaye  { background: #fef2f2; border-left-color: #dc2626; }
    .recap-card h6 { font-size: 13px; font-weight: 700; margin-bottom: 10px; }
    .recap-row {
        display: flex; justify-content: space-between; font-size: 12px;
        padding: 4px 0; border-bottom: 1px solid rgba(0,0,0,0.05);
    }
    .recap-row:last-child { border-bottom: none; }
    .recap-row .lbl { color: #64748b; }
    .recap-row .val { font-weight: 700; }

    .recap-total {
        font-size: 22px; font-weight: 900; text-align: center;
        margin-top: 20px; padding: 16px;
        background: linear-gradient(135deg, #7c3aed, #a855f7);
        color: white; border-radius: 12px;
    }

    .badge-statut {
        display: inline-block; padding: 3px 10px; border-radius: 50px;
        font-size: 10px; font-weight: 800; text-transform: uppercase;
    }
    .badge-statut.paye     { background: #dcfce7; color: #15803d; border: 1.5px solid #86efac; }
    .badge-statut.partiel  { background: #fef3c7; color: #b45309; border: 1.5px solid #fcd34d; }
    .badge-statut.nonpaye  { background: #fee2e2; color: #b91c1c; border: 1.5px solid #fecaca; }
    .badge-statut.aucun    { background: #f1f5f9; color: #64748b; border: 1.5px solid #cbd5e1; }

    .benef-nom { font-weight: 800; color: #6d28d9; font-size: 12px; }
    .benef-date {
        display: inline-block;
        background: #eff6ff; color: #1e3a5f;
        padding: 2px 8px; border-radius: 6px;
        font-weight: 700; font-size: 10px;
        border: 1px solid #bfdbfe;
        white-space: nowrap;
    }
    .benef-date.vide {
        background: #f8fafc; color: #94a3b8;
        border: 1px dashed #cbd5e1;
        font-weight: 500;
    }

    .site-badge {
        display: inline-block;
        background: #ecfdf5; color: #15803d;
        padding: 2px 8px; border-radius: 6px;
        font-weight: 700; font-size: 10px;
        border: 1px solid #86efac;
        white-space: nowrap;
    }
    .tf-badge {
        display: inline-block;
        background: #fef3c7; color: #92400e;
        padding: 2px 8px; border-radius: 6px;
        font-weight: 700; font-size: 10px;
        border: 1px solid #fcd34d;
        white-space: nowrap;
    }

    .btn-payer {
        background: #7c3aed; color: white; border: none;
        padding: 10px 18px; border-radius: 8px; font-weight: 700;
        font-size: 13px; cursor: pointer; text-decoration: none;
        display: inline-flex; align-items: center; gap: 6px;
    }
    .btn-payer:hover { background: #6d28d9; color: white; }

    @media (max-width: 992px) { .recap-grid { grid-template-columns: 1fr; } }
</style>
@endsection

@section('content')

{{-- EN-TÊTE --}}
<div class="rapport-header">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
        <div>
            <h2>📊 Rapport Logistique</h2>
            <p>Suivi des encaissements logistiques par bénéficiaire</p>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;opacity:0.8;">Généré le</div>
            <div style="font-size:14px;font-weight:700;">{{ now()->format('d/m/Y à H:i') }}</div>
        </div>
    </div>
</div>

{{-- FILTRES --}}
<form method="GET" action="{{ route('comptable.rapport') }}">
<div class="filter-section">
    <h6>🔍 Filtres</h6>
    <div class="row g-3">
        <div class="col-md-3">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">🔍 Recherche</label>
            <input type="text" name="q" class="form-control form-control-sm"
                   placeholder="Nom bénéficiaire, téléphone..." value="{{ request('q') }}">
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">🏢 Grand Site</label>
            <select name="grand_site_id" class="form-control form-control-sm">
                <option value="">Tous</option>
                @foreach($grandSites as $gs)
                    <option value="{{ $gs->id }}" {{ request('grand_site_id') == $gs->id ? 'selected' : '' }}>
                        {{ $gs->nom }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">📊 Statut</label>
            <select name="statut" class="form-control form-control-sm">
                <option value="">Tous</option>
                <option value="paye"     {{ request('statut') == 'paye' ? 'selected' : '' }}>✅ Payés</option>
                <option value="partiel"  {{ request('statut') == 'partiel' ? 'selected' : '' }}>⏳ Partiels</option>
                <option value="non_paye" {{ request('statut') == 'non_paye' ? 'selected' : '' }}>❌ Non payés</option>
                <option value="sans_prix" {{ request('statut') == 'sans_prix' ? 'selected' : '' }}>⚪ Sans prix</option>
            </select>
        </div>
        <div class="col-md-2">
            <label style="font-size:11px;font-weight:600;color:#64748b;text-transform:uppercase;">📅 Implantation prévue</label>
            <input type="date" name="date_implantation_prevue" class="form-control form-control-sm"
                   value="{{ request('date_implantation_prevue') }}">
        </div>
        <div class="col-md-3 d-flex align-items-end gap-2">
            <button type="submit" class="btn-payer btn-sm">🔍 Générer</button>
            <a href="{{ route('comptable.rapport') }}" class="btn btn-outline-secondary btn-sm">✖ Reset</a>
        </div>
    </div>

    @if(request('date_implantation_prevue'))
        <div style="margin-top:12px;padding:8px 14px;background:#eff6ff;border-radius:8px;
                    font-size:12px;color:#1e40af;border-left:3px solid #1d4ed8;">
            📅 Filtre actif :
            <strong>Implantation prévue = {{ \Carbon\Carbon::parse(request('date_implantation_prevue'))->format('d/m/Y') }}</strong>
        </div>
    @endif
</div>
</form>

@if($dossiers->count() > 0)

{{-- KPI --}}
<div class="kpi-grid">
    <div class="kpi-box" style="border-top-color:#7c3aed;">
        <div class="val">{{ $stats['total_dossiers'] }}</div>
        <div class="lbl">Dossiers</div>
    </div>
    <div class="kpi-box" style="border-top-color:#16a34a;">
        <div class="val" style="color:#16a34a;">{{ $stats['nb_payes'] }}</div>
        <div class="lbl">✅ Payés</div>
    </div>
    <div class="kpi-box" style="border-top-color:#f59e0b;">
        <div class="val" style="color:#f59e0b;">{{ $stats['nb_partiels'] }}</div>
        <div class="lbl">⏳ Partiels</div>
    </div>
    <div class="kpi-box" style="border-top-color:#dc2626;">
        <div class="val" style="color:#dc2626;">{{ $stats['nb_non_payes'] }}</div>
        <div class="lbl">❌ Non payés</div>
    </div>
    @if(($stats['nb_aucun_frais'] ?? 0) > 0)
        <div class="kpi-box" style="border-top-color:#94a3b8;">
            <div class="val" style="color:#64748b;">{{ $stats['nb_aucun_frais'] }}</div>
            <div class="lbl">⚪ Aucun frais</div>
        </div>
    @endif
    <div class="kpi-box" style="border-top-color:#7c3aed;">
        <div class="val" style="font-size:18px;">{{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }}</div>
        <div class="lbl">Réf. FCFA</div>
    </div>
    <div class="kpi-box" style="border-top-color:#16a34a;">
        <div class="val" style="font-size:18px;color:#16a34a;">{{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }}</div>
        <div class="lbl">Encaissé FCFA</div>
    </div>
    <div class="kpi-box" style="border-top-color:#dc2626;">
        <div class="val" style="font-size:18px;color:#dc2626;">{{ number_format($stats['reste_logistique_total'], 0, ',', ' ') }}</div>
        <div class="lbl">Reste FCFA</div>
    </div>
</div>

{{-- RÉCAP --}}
<div class="recap-section">
    <h5 style="font-weight:800;color:#1e3a5f;margin-bottom:16px;">🚗 Récapitulatif Logistique</h5>
    <div class="recap-grid">
        <div class="recap-card paye">
            <h6 style="color:#16a34a;">✅ Dossiers soldés</h6>
            <div class="recap-row"><span class="lbl">Nombre</span><span class="val" style="color:#16a34a;">{{ $stats['nb_payes'] }}</span></div>
            <div class="recap-row"><span class="lbl">Encaissé</span><span class="val" style="color:#16a34a;">{{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">% du total</span><span class="val">{{ $stats['total_dossiers'] > 0 ? round(($stats['nb_payes'] / $stats['total_dossiers']) * 100) : 0 }}%</span></div>
        </div>
        <div class="recap-card partiel">
            <h6 style="color:#b45309;">⏳ En cours</h6>
            <div class="recap-row"><span class="lbl">Nombre</span><span class="val" style="color:#b45309;">{{ $stats['nb_partiels'] }}</span></div>
            <div class="recap-row"><span class="lbl">Encaissé</span><span class="val" style="color:#16a34a;">{{ number_format($stats['paye_partiels'] ?? 0, 0, ',', ' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">Reste attendu</span><span class="val" style="color:#dc2626;">{{ number_format($stats['reste_partiels'] ?? 0, 0, ',', ' ') }} FCFA</span></div>
        </div>
        <div class="recap-card nonpaye">
            <h6 style="color:#b91c1c;">❌ Non payés</h6>
            <div class="recap-row"><span class="lbl">Nombre</span><span class="val" style="color:#b91c1c;">{{ $stats['nb_non_payes'] }}</span></div>
            <div class="recap-row"><span class="lbl">Attendu</span><span class="val" style="color:#dc2626;">{{ number_format($stats['prix_non_payes'] ?? 0, 0, ',', ' ') }} FCFA</span></div>
            <div class="recap-row"><span class="lbl">% du total</span><span class="val">{{ $stats['total_dossiers'] > 0 ? round(($stats['nb_non_payes'] / $stats['total_dossiers']) * 100) : 0 }}%</span></div>
        </div>
    </div>

    <div class="recap-total">
        <div style="font-size:12px;opacity:0.8;margin-bottom:6px;">TOTAL ENCAISSÉ</div>
        <div>{{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }} FCFA / {{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }} FCFA</div>
        <div style="font-size:14px;margin-top:6px;">
            Reste à encaisser : {{ number_format($stats['reste_logistique_total'], 0, ',', ' ') }} FCFA
        </div>
    </div>
</div>

{{-- EXPORTS --}}
<div class="d-flex gap-2 mb-3 align-items-center flex-wrap">
    <button type="button" class="btn btn-danger btn-sm" style="font-weight:700;"
            onclick="ouvrirModalExport('pdf')">
        📄 Export PDF
    </button>
    <button type="button" class="btn btn-success btn-sm" style="font-weight:700;"
            onclick="ouvrirModalExport('excel')">
        📊 Export Excel
    </button>
    <a href="{{ route('comptable.rapports.sauvegardes') }}"
   class="btn btn-secondary btn-sm" style="font-weight:700;">
    📚 Rapports sauvegardés
</a>
</div>

{{-- TABLEAU --}}
<div class="rapport-table-wrap">
<table class="rapport-table">
    <thead>
        <tr>
            <th>#</th>
            <th>👤 Bénéficiaire</th>
            <th>📞 Téléphone</th>
            <th>🏢 Grand Site</th>
            <th>🗺️ Site</th>
            <th>📄 TF</th>
            <th>📅 Implantation prévue</th>
            <th style="text-align:right;">Réf. Logistique</th>
            <th style="text-align:right;">Payé</th>
            <th style="text-align:right;">Reste</th>
            <th>Statut</th>
        </tr>
    </thead>
    <tbody>
    @php $row = 0; @endphp

    @foreach($dossiers as $d)
        @php
            $payeLogi  = $d->paiementsLogistiques->sum('montant');
            $prixLogi  = $d->prix_logistique ?? 0;

            $benefs = $d->beneficiaires ?? collect();
            $nbBenefs = max(1, $benefs->count());

            $premiereAffectation = $d->affectations->first();
            $siteNom = $premiereAffectation?->site?->name;
            $tfNom   = $premiereAffectation?->tf?->title;

            if (!$siteNom || !$tfNom) {
                $premiereAffBenef = \App\Models\Affectation::where('dossier_client_id', $d->id)
                    ->with(['site', 'tf'])
                    ->first();
                $siteNom = $siteNom ?? $premiereAffBenef?->site?->name;
                $tfNom   = $tfNom   ?? $premiereAffBenef?->tf?->title;
            }
        @endphp

        @forelse($benefs as $b)
            @php
                $row++;

                $payeBenef = \App\Models\BeneficiairePaiement::where('dossier_client_id', $d->id)
                    ->where('beneficiaire_id', $b->id)
                    ->where('paiement_type', 'logistique')
                    ->sum('montant');

                if ($payeBenef == 0 && $payeLogi > 0) {
                    $payeBenef = $payeLogi / $nbBenefs;
                }

                $prixBenef  = $prixLogi / $nbBenefs;
                $resteBenef = max(0, $prixBenef - $payeBenef);

                if ($prixBenef == 0 && $payeBenef == 0) {
                    $statutB = 'aucun';    $labelB = '⚪ Aucun frais';
                } elseif ($prixBenef == 0 && $payeBenef > 0) {
                    $statutB = 'partiel';  $labelB = '⚠️ Payé sans prix';
                } elseif ($payeBenef >= $prixBenef && $prixBenef > 0) {
                    $statutB = 'paye';     $labelB = '✅ Payé';
                } elseif ($payeBenef > 0) {
                    $statutB = 'partiel';  $labelB = '⏳ Partiel';
                } else {
                    $statutB = 'nonpaye';  $labelB = '❌ Non payé';
                }
            @endphp
            <tr>
                <td>{{ $row }}</td>
                <td><span class="benef-nom">👤 {{ $b->nom }}</span></td>
                <td>
                    <span style="font-size:11px;color:#1e40af;font-weight:600;">
                        📞 {{ $b->telephone ?? '—' }}
                    </span>
                </td>
                <td>
                    <span style="font-size:11px;color:#64748b;">
                        {{ $d->grandSite?->nom ?? '—' }}
                    </span>
                </td>
                <td>
                    @if($siteNom)
                        <span class="site-badge">🗺️ {{ $siteNom }}</span>
                    @else
                        <span style="color:#94a3b8;font-size:11px;">—</span>
                    @endif
                </td>
                <td>
                    @if($tfNom)
                        <span class="tf-badge">📄 {{ $tfNom }}</span>
                    @else
                        <span style="color:#94a3b8;font-size:11px;">—</span>
                    @endif
                </td>
                <td>
                    @if($b->implantation_prevue)
                        <span class="benef-date">
                            📅 {{ \Carbon\Carbon::parse($b->implantation_prevue)->format('d/m/Y') }}
                        </span>
                    @else
                        <span class="benef-date vide">— non planifiée —</span>
                    @endif
                </td>
                <td style="text-align:right;color:#7c3aed;font-weight:700;">
                    {{ number_format($prixBenef, 0, ',', ' ') }}
                </td>
                <td style="text-align:right;color:#16a34a;font-weight:700;">
                    {{ number_format($payeBenef, 0, ',', ' ') }}
                </td>
                <td style="text-align:right;color:#dc2626;font-weight:700;">
                    {{ number_format($resteBenef, 0, ',', ' ') }}
                </td>
                <td>
                    <span class="badge-statut {{ $statutB }}">{{ $labelB }}</span>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="11" style="text-align:center;color:#94a3b8;padding:20px;">
                    Aucun bénéficiaire pour ce dossier
                </td>
            </tr>
        @endforelse
    @endforeach
    </tbody>
    <tfoot>
        <tr>
            <td colspan="7">TOTAUX</td>
            <td style="text-align:right;">{{ number_format($stats['prix_logistique_total'], 0, ',', ' ') }}</td>
            <td style="text-align:right;color:#4ade80;">{{ number_format($stats['paye_logistique_total'], 0, ',', ' ') }}</td>
            <td style="text-align:right;color:#fca5a5;">{{ number_format($stats['reste_logistique_total'], 0, ',', ' ') }}</td>
            <td></td>
        </tr>
    </tfoot>
</table>
</div>

<div class="mt-3 d-flex justify-content-center">
    {{ $dossiers->links() }}
</div>

@else
    <div class="alert alert-info" style="text-align:center;padding:60px 20px;background:white;border-radius:12px;">
        <div style="font-size:48px;">📭</div>
        <h4 style="color:#1e3a5f;font-weight:800;margin-top:12px;">Aucun bénéficiaire trouvé</h4>
        <p style="color:#64748b;font-size:13px;margin:0;">Aucun bénéficiaire logistique ne correspond à vos critères.</p>
    </div>
@endif

{{-- ═══════════════════════════════════════════════════════════ --}}
{{-- MODALE DE SAISIE DE NOTE POUR EXPORT                     --}}
{{-- ═══════════════════════════════════════════════════════════ --}}
<div id="exportNoteOverlay" onclick="fermerModalExport()"
     style="display:none;position:fixed;inset:0;background:rgba(15,23,42,0.6);
            z-index:9998;backdrop-filter:blur(2px);"></div>

<div id="exportNoteModal"
     style="display:none;position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
            background:white;padding:24px;border-radius:16px;
            box-shadow:0 20px 60px rgba(0,0,0,0.3);z-index:9999;
            width:520px;max-width:95%;">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 style="color:#1e3a5f;font-weight:800;margin:0;">📝 Ajouter une note au rapport</h5>
        <button type="button" onclick="fermerModalExport()"
                style="background:none;border:none;font-size:20px;cursor:pointer;color:#94a3b8;">✕</button>
    </div>

    <div style="background:#eff6ff;border-radius:8px;padding:10px 14px;margin-bottom:14px;
                font-size:12px;color:#1e40af;border-left:3px solid #1d4ed8;">
        ℹ️ Cette note sera affichée dans le rapport exporté. Elle est <strong>optionnelle</strong>.
    </div>

    <label style="font-size:12px;font-weight:700;color:#64748b;">
        📝 Note pour le rapport
    </label>
    <textarea id="export_note" class="form-control" rows="4"
              placeholder="Ex : Rapport établi à la demande du responsable financier..."
              maxlength="1000" style="margin-top:6px;font-size:13px;"></textarea>
    <div style="font-size:11px;color:#94a3b8;margin-top:4px;">
        <span id="export_note_counter">0</span> / 1000 caractères
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <button type="button" onclick="fermerModalExport()" class="btn btn-light">Annuler</button>
        <button type="button" onclick="confirmerExport()" id="btnConfirmExport"
                class="btn px-4" style="background:#7c3aed;color:white;font-weight:700;">
            ✅ Générer le rapport
        </button>
    </div>
</div>

@endsection

@section('scripts')
<script>
let exportTypeEnCours = null;

function ouvrirModalExport(type) {
    exportTypeEnCours = type;
    document.getElementById('export_note').value = '';
    document.getElementById('export_note_counter').textContent = '0';
    document.getElementById('exportNoteOverlay').style.display = 'block';
    document.getElementById('exportNoteModal').style.display = 'block';
    setTimeout(() => document.getElementById('export_note').focus(), 100);
}

function fermerModalExport() {
    document.getElementById('exportNoteOverlay').style.display = 'none';
    document.getElementById('exportNoteModal').style.display = 'none';
    exportTypeEnCours = null;
}

document.getElementById('export_note')?.addEventListener('input', function() {
    document.getElementById('export_note_counter').textContent = this.value.length;
});

function confirmerExport() {
    if (!exportTypeEnCours) return;

    const note = document.getElementById('export_note').value.trim();

    const params = new URLSearchParams(window.location.search);
    params.set('type', exportTypeEnCours);
    if (note) params.set('note', note);

    const url = '{{ route("comptable.rapport.export") }}?' + params.toString();

    fermerModalExport();
    window.location.href = url;
}
</script>
@endsection