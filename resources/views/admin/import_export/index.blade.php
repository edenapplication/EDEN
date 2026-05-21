@extends('admin.layout')
@section('content')

<style>
.ie-card { background:white; border-radius:14px; padding:24px; box-shadow:0 2px 12px rgba(0,0,0,0.07); margin-bottom:20px; }
.ie-card h5 { color:#1e3a5f; font-weight:800; margin-bottom:16px; padding-bottom:10px; border-bottom:2px solid #e2e8f0; }
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
.driver-badge { display:inline-block; padding:4px 12px; border-radius:8px; font-size:12px; font-weight:700; }
.driver-mysql  { background:#dcfce7; color:#15803d; }
.driver-sqlite { background:#fef9c3; color:#92400e; }
</style>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2 style="color:#1e3a5f;font-weight:800;">💾 Import / Export</h2>
    <span class="driver-badge driver-{{ $driver }}">
        🗄️ Base : {{ strtoupper($driver) }}
    </span>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('import_errors'))
    <div class="alert alert-warning alert-dismissible fade show">
        <strong>⚠️ Erreurs ignorées :</strong>
        <ul class="mb-0 mt-2" style="font-size:11px;max-height:150px;overflow-y:auto;">
            @foreach(session('import_errors') as $err)
                <li>{{ $err }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">

    {{-- ============ EXPORT ============ --}}
    <div class="col-lg-6">
        <div class="ie-card">
            <h5>📤 Exporter les données</h5>
            <p style="font-size:13px;color:#64748b;margin-bottom:20px;">
                Le fichier <strong>JSON</strong> généré est compatible <strong>MySQL et SQLite</strong>.
                Il peut être importé sur n'importe quelle instance Eden Group.
            </p>

            @php
                $foncierTables = ['grand_sites','sites','tfs','lots','clients','commerciaux','conducteurs','facilitateurs','agents_commerciaux','dossiers_clients','paiements_dossier','dossiers_techniques','zone_groupes','rapports','visites','visiteurs'];
                $rhTables      = ['rh_directions','rh_services','rh_postes','rh_employes','rh_employe_documents','rh_bulletins_paie','rh_absences','rh_prets','rh_heures_sup','rh_sanctions','rh_retards','rh_recapitulatifs'];
                $total         = array_sum(array_filter($tableStats));
                $foncierTotal  = array_sum(array_filter(array_intersect_key($tableStats, array_flip($foncierTables))));
                $rhTotal       = array_sum(array_filter(array_intersect_key($tableStats, array_flip($rhTables))));
            @endphp

            <form method="POST" action="{{ route('import-export.export') }}">
                @csrf
                <input type="hidden" name="module" value="all">
                <button type="submit" class="module-btn" style="border-color:#1d4ed8;background:#eff6ff;">
                    <span class="icon">🌐</span>
                    <div class="info">
                        <div class="title" style="color:#1d4ed8;">Export COMPLET</div>
                        <div class="desc">Toutes les tables : foncier + RH + utilisateurs</div>
                    </div>
                    <span class="badge-count">{{ number_format($total) }} lignes</span>
                </button>
            </form>

            <form method="POST" action="{{ route('import-export.export') }}">
                @csrf
                <input type="hidden" name="module" value="foncier">
                <button type="submit" class="module-btn">
                    <span class="icon">🏗️</span>
                    <div class="info">
                        <div class="title">Export Gestion Foncière</div>
                        <div class="desc">Sites, lots, clients, dossiers, paiements, visites</div>
                    </div>
                    <span class="badge-count">{{ number_format($foncierTotal) }} lignes</span>
                </button>
            </form>

            <form method="POST" action="{{ route('import-export.export') }}">
                @csrf
                <input type="hidden" name="module" value="rh">
                <button type="submit" class="module-btn">
                    <span class="icon">👔</span>
                    <div class="info">
                        <div class="title">Export Ressources Humaines</div>
                        <div class="desc">Employés, paie, absences, prêts, sanctions, retards</div>
                    </div>
                    <span class="badge-count">{{ number_format($rhTotal) }} lignes</span>
                </button>
            </form>

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
            <h5>📊 État des tables</h5>
            <div style="max-height:400px;overflow-y:auto;">
            <table class="table table-sm table-hover" style="font-size:11px;">
                <thead class="table-light">
                    <tr><th>Table</th><th>Lignes</th><th>Statut</th></tr>
                </thead>
                <tbody>
                @foreach($tableStats as $table => $count)
                    <tr>
                        <td style="font-family:monospace;">{{ $table }}</td>
                        <td><strong>{{ $count !== null ? number_format($count) : '-' }}</strong></td>
                        <td>
                            @if($count === null)
                                <span style="color:#dc2626;font-size:10px;">✗ absente</span>
                            @elseif($count === 0)
                                <span style="color:#94a3b8;font-size:10px;">vide</span>
                            @else
                                <span style="color:#16a34a;font-size:10px;">✓</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
            </div>
        </div>
    </div>

    {{-- ============ IMPORT ============ --}}
    <div class="col-lg-6">
        <div class="ie-card">
            <h5>📥 Importer des données</h5>

            <div style="background:#fff5f5;border:2px solid #fecaca;border-radius:10px;padding:14px;margin-bottom:16px;">
                <div style="font-weight:700;color:#dc2626;margin-bottom:6px;">⚠️ Attention</div>
                <div style="font-size:12px;color:#7f1d1d;line-height:1.6;">
                    Le mode <strong>Replace</strong> efface les données existantes avant import.<br>
                    Le mode <strong>Merge</strong> met à jour les enregistrements existants et ajoute les nouveaux.<br>
                    <strong>Formats acceptés :</strong> .json (recommandé) ou .sql (ancien format).
                </div>
            </div>

            <form method="POST" action="{{ route('import-export.import') }}" enctype="multipart/form-data">
                @csrf

                <div class="mb-3">
                    <label class="form-label fw-semibold">Fichier (.json ou .sql)</label>
                    <input type="file" name="fichier_sql" class="form-control" accept=".json,.sql,.txt" required>
                    <div style="font-size:11px;color:#64748b;margin-top:4px;">
                        Taille max : 100 MB. Format JSON recommandé pour la compatibilité MySQL ↔ SQLite.
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Mode d'import</label>
                    <div class="d-flex gap-3">
                        <label style="font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;">
                            <input type="radio" name="mode" value="merge" checked>
                            <div>
                                <span style="font-weight:600;">Merge</span>
                                <span style="font-size:11px;color:#64748b;display:block;">Met à jour + ajoute (recommandé)</span>
                            </div>
                        </label>
                        <label style="font-size:13px;cursor:pointer;display:flex;align-items:center;gap:6px;">
                            <input type="radio" name="mode" value="replace">
                            <div>
                                <span style="font-weight:600;">Replace</span>
                                <span style="font-size:11px;color:#64748b;display:block;">Efface tout puis importe</span>
                            </div>
                        </label>
                    </div>
                </div>

                <div class="mb-4 p-3" style="background:#fef9c3;border-radius:8px;border:1px solid #fde68a;">
                    <div class="form-check">
                        <input type="checkbox" name="confirmer" value="1" class="form-check-input" id="confirmerCheck" required>
                        <label class="form-check-label fw-semibold" for="confirmerCheck" style="font-size:13px;cursor:pointer;">
                            Je confirme vouloir importer et potentiellement modifier les données existantes
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-danger w-100 fw-bold" style="padding:12px;">
                    📥 Lancer l'import
                </button>
            </form>
        </div>

        {{-- GUIDE --}}
        <div class="ie-card" style="background:#f0f9ff;border:1px solid #bae6fd;">
            <h5 style="color:#0369a1;">📖 Guide de synchronisation</h5>
            <div style="font-size:13px;color:#0369a1;line-height:1.8;">

                <div style="font-weight:700;margin-bottom:6px;">🔄 Poste local (MySQL) → NAS (SQLite)</div>
                <ol style="padding-left:18px;color:#075985;margin-bottom:16px;">
                    <li>Sur le poste local : <strong>Exporter Complet</strong> → fichier .json</li>
                    <li>Copier le fichier sur le NAS</li>
                    <li>Sur le NAS : <strong>Importer</strong> le .json en mode <strong>Merge</strong></li>
                </ol>

                <div style="font-weight:700;margin-bottom:6px;">👔 Poste RH (MySQL) → NAS (SQLite)</div>
                <ol style="padding-left:18px;color:#075985;margin-bottom:16px;">
                    <li>Sur le poste RH : <strong>Exporter RH</strong></li>
                    <li>Sur le NAS : <strong>Importer</strong> — seules les tables RH sont affectées</li>
                </ol>

                <div style="font-weight:700;margin-bottom:6px;">📱 NAS → autre poste</div>
                <ol style="padding-left:18px;color:#075985;">
                    <li>Sur le NAS : <strong>Exporter Complet</strong></li>
                    <li>Sur le poste : <strong>Importer</strong> en mode Merge</li>
                </ol>

                <div style="margin-top:14px;padding:10px;background:#dcfce7;border-radius:8px;font-size:12px;color:#15803d;">
                    ✅ Le format <strong>JSON</strong> est 100% compatible MySQL et SQLite. Utilisez toujours ce format pour les échanges entre environnements différents.
                </div>
            </div>
        </div>
    </div>
</div>

@endsection