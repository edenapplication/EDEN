@extends('admin.layout')
@section('content')

<style>
.ie-card { background:white; border-radius:14px; padding:24px; box-shadow:0 2px 12px rgba(0,0,0,0.07); margin-bottom:20px; }
.ie-card h4 { color:#1e3a5f; font-weight:800; margin-bottom:16px; }
.module-btn {
    display:flex; align-items:center; gap:12px; padding:14px 18px;
    border-radius:10px; border:2px solid #e2e8f0; background:#f8fafc;
    cursor:pointer; transition:0.2s; width:100%; text-align:left;
    margin-bottom:10px; text-decoration:none; color:#1e293b;
}
.module-btn:hover { border-color:#1d4ed8; background:#eff6ff; color:#1d4ed8; }
.module-btn .icon { font-size:24px; flex-shrink:0; }
.module-btn .info .title { font-weight:700; font-size:14px; }
.module-btn .info .desc  { font-size:12px; color:#64748b; margin-top:2px; }
.module-btn .badge-count { margin-left:auto; background:#dbeafe; color:#1d4ed8; padding:3px 10px; border-radius:8px; font-size:11px; font-weight:700; flex-shrink:0; }
.table-stat { font-size:12px; }
.table-stat td, .table-stat th { padding:5px 8px; }
.zone-danger { background:#fff5f5; border:2px solid #fecaca; border-radius:10px; padding:16px; margin-bottom:16px; }
.progress-mini { height:5px; background:#e2e8f0; border-radius:3px; overflow:hidden; display:inline-block; width:60px; vertical-align:middle; }
.progress-mini-fill { height:100%; background:#1d4ed8; border-radius:3px; }
</style>

<h2 class="mb-4" style="color:#1e3a5f;font-weight:800;">💾 Import / Export Base de Données</h2>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('import_errors'))
    <div class="alert alert-warning">
        <strong>Erreurs ignorées lors de l'import :</strong>
        <ul class="mb-0 mt-2" style="font-size:11px;">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">

    {{-- ============ EXPORT ============ --}}
    <div class="col-lg-6">
        <div class="ie-card">
            <h4>📤 Exporter les données</h4>
            <p style="font-size:13px;color:#64748b;margin-bottom:20px;">
                Choisissez ce que vous souhaitez exporter. Le fichier SQL généré peut être importé sur n'importe quelle instance Eden Group (NAS, autre poste, backup).
            </p>

            {{-- EXPORT COMPLET --}}
            <form method="POST" action="{{ route('import-export.export') }}">
                @csrf
                <input type="hidden" name="module" value="all">
                <button type="submit" class="module-btn" style="border-color:#1d4ed8;background:#eff6ff;">
                    <span class="icon">🌐</span>
                    <div class="info">
                        <div class="title" style="color:#1d4ed8;">Export COMPLET</div>
                        <div class="desc">Toutes les tables : foncier + RH + utilisateurs</div>
                    </div>
                    @php $total = array_sum(array_filter($tableStats)); @endphp
                    <span class="badge-count">{{ number_format($total) }} lignes</span>
                </button>
            </form>

            {{-- EXPORT FONCIER --}}
            <form method="POST" action="{{ route('import-export.export') }}">
                @csrf
                <input type="hidden" name="module" value="foncier">
                <button type="submit" class="module-btn">
                    <span class="icon">🏗️</span>
                    <div class="info">
                        <div class="title">Export Gestion Foncière</div>
                        <div class="desc">Sites, lots, clients, dossiers, paiements, visites</div>
                    </div>
                    @php
                        $foncierTables = ['grand_sites','sites','tfs','lots','clients','commerciaux','conducteurs','facilitateurs','agents_commerciaux','dossiers_clients','paiements_dossier','dossiers_techniques','zone_groupes','rapports','visites','visiteurs'];
                        $foncierTotal = array_sum(array_filter(array_intersect_key($tableStats, array_flip($foncierTables))));
                    @endphp
                    <span class="badge-count">{{ number_format($foncierTotal) }} lignes</span>
                </button>
            </form>

            {{-- EXPORT RH --}}
            <form method="POST" action="{{ route('import-export.export') }}">
                @csrf
                <input type="hidden" name="module" value="rh">
                <button type="submit" class="module-btn">
                    <span class="icon">👔</span>
                    <div class="info">
                        <div class="title">Export Ressources Humaines</div>
                        <div class="desc">Employés, paie, absences, prêts, sanctions, retards</div>
                    </div>
                    @php
                        $rhTables = ['rh_directions','rh_services','rh_postes','rh_employes','rh_employe_documents','rh_bulletins_paie','rh_absences','rh_prets','rh_heures_sup','rh_sanctions','rh_retards','rh_recapitulatifs'];
                        $rhTotal = array_sum(array_filter(array_intersect_key($tableStats, array_flip($rhTables))));
                    @endphp
                    <span class="badge-count">{{ number_format($rhTotal) }} lignes</span>
                </button>
            </form>

            {{-- EXPORT UTILISATEURS --}}
            <form method="POST" action="{{ route('import-export.export') }}">
                @csrf
                <input type="hidden" name="module" value="users">
                <button type="submit" class="module-btn">
                    <span class="icon">🔑</span>
                    <div class="info">
                        <div class="title">Export Utilisateurs</div>
                        <div class="desc">Comptes d'accès et rôles</div>
                    </div>
                    <span class="badge-count">{{ $tableStats['users'] ?? 0 }} lignes</span>
                </button>
            </form>
        </div>

        {{-- STATS TABLES --}}
        <div class="ie-card">
            <h4>📊 État des tables</h4>
            <table class="table table-sm table-hover table-stat">
                <thead class="table-light">
                    <tr><th>Table</th><th>Lignes</th><th></th></tr>
                </thead>
                <tbody>
                @foreach($tableStats as $table => $count)
                    <tr>
                        <td style="font-family:monospace;font-size:11px;">{{ $table }}</td>
                        <td>
                            @if($count === null)
                                <span class="text-muted" style="font-size:10px;">inexistante</span>
                            @else
                                <strong>{{ number_format($count) }}</strong>
                            @endif
                        </td>
                        <td>
                            @if($count !== null && $count > 0)
                                <div class="progress-mini">
                                    <div class="progress-mini-fill" style="width:{{ min(100, $count / max(1, $total) * 100 * 10) }}%;"></div>
                                </div>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- ============ IMPORT ============ --}}
    <div class="col-lg-6">
        <div class="ie-card">
            <h4>📥 Importer des données</h4>

            <div class="zone-danger">
                <div style="font-weight:700;color:#dc2626;margin-bottom:8px;">⚠️ Attention — Opération irréversible</div>
                <div style="font-size:12px;color:#7f1d1d;line-height:1.6;">
                    L'import <strong>remplace les données existantes</strong> pour les tables concernées (TRUNCATE puis INSERT).
                    <br>Faites un export de sauvegarde avant d'importer.
                    <br>Importez d'abord le module <strong>Foncier</strong>, puis le <strong>RH</strong> si besoin.
                </div>
            </div>

            <form method="POST" action="{{ route('import-export.import') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Fichier SQL (.sql ou .txt)</label>
                    <input type="file" name="fichier_sql" class="form-control" accept=".json" required>
                    <div style="font-size:11px;color:#64748b;margin-top:4px;">
                        Taille maximale : 100 MB. Fichier généré par Eden Group uniquement.
                    </div>
                </div>

                <div class="mb-3 p-3" style="background:#fef9c3;border-radius:8px;border:1px solid #fde68a;">
                    <div class="form-check">
                        <input type="checkbox" name="confirmer" value="1" class="form-check-input" id="confirmerCheck" required>
                        <label class="form-check-label fw-semibold" for="confirmerCheck" style="font-size:13px;cursor:pointer;">
                            Je confirme vouloir remplacer les données existantes par celles du fichier importé
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100" style="font-weight:700;padding:12px;">
                    📥 Lancer l'import
                </button>
            </form>
        </div>

        {{-- GUIDE --}}
        <div class="ie-card">
            <h4>📖 Guide de synchronisation NAS</h4>
            <div style="font-size:13px;color:#374151;line-height:1.8;">

                <div style="font-weight:700;color:#1e3a5f;margin-bottom:8px;">🔄 Scénario 1 — Poste local → NAS</div>
                <ol style="padding-left:18px;color:#475569;">
                    <li>Sur le poste local : <strong>Exporter → Complet</strong></li>
                    <li>Copier le fichier .sql sur le NAS</li>
                    <li>Sur le NAS : <strong>Importer</strong> le fichier</li>
                </ol>

                <div style="font-weight:700;color:#1e3a5f;margin-top:16px;margin-bottom:8px;">👔 Scénario 2 — Poste RH → NAS (fusion RH)</div>
                <ol style="padding-left:18px;color:#475569;">
                    <li>Sur le poste RH : <strong>Exporter → Ressources Humaines</strong></li>
                    <li>Copier le fichier .sql sur le NAS</li>
                    <li>Sur le NAS : <strong>Importer</strong> — seules les tables RH seront remplacées</li>
                </ol>

                <div style="font-weight:700;color:#1e3a5f;margin-top:16px;margin-bottom:8px;">📱 Scénario 3 — NAS → Poste local</div>
                <ol style="padding-left:18px;color:#475569;">
                    <li>Sur le NAS : <strong>Exporter → Complet</strong></li>
                    <li>Sur le poste local : <strong>Importer</strong> le fichier</li>
                </ol>

                <div style="margin-top:16px;padding:10px;background:#f0fdf4;border-radius:8px;border-left:3px solid #16a34a;font-size:12px;color:#15803d;">
                    ✅ <strong>Conseil :</strong> Faites toujours un export de sauvegarde avant chaque import.
                    Nommez vos fichiers avec la date pour vous y retrouver.
                </div>
            </div>
        </div>
    </div>
</div>

@endsection