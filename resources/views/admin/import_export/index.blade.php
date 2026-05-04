@extends('admin.layout')
@section('content')

<style>
.ie-card { background:white; border-radius:12px; padding:24px; box-shadow:0 2px 10px rgba(0,0,0,0.06); margin-bottom:20px; }
.ie-card h5 { font-weight:700; color:#1e3a5f; border-bottom:2px solid #e2e8f0; padding-bottom:8px; margin-bottom:16px; }
.table-checkbox { display:flex; flex-wrap:wrap; gap:8px; }
.table-checkbox label { font-size:12px; background:#f1f5f9; padding:4px 10px; border-radius:6px; cursor:pointer; }
.table-checkbox input { margin-right:4px; }
</style>

<h2 class="mb-4">🔄 Import / Export de données</h2>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('warning'))
    <div class="alert alert-warning">{{ session('warning') }}</div>
@endif
@if(session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

{{-- EXPORT --}}
<div class="ie-card">
    <h5>📤 Exporter la base de données</h5>
    <p class="text-muted" style="font-size:13px;">
        Sélectionnez les tables à exporter. Le fichier JSON téléchargé peut être réimporté sur un autre environnement.
    </p>

    <form method="POST" action="{{ route('import-export.export') }}">
        @csrf

        <div class="table-checkbox mb-4">
            @foreach(['grand_sites','sites','commerciaux','clients','conducteurs','facilitateurs','agents_commerciaux','tfs','lots','dossiers_clients','paiements_dossier','dossiers_techniques'] as $table)
                <label>
                    <input type="checkbox" name="tables[]" value="{{ $table }}" checked>
                    {{ $table }}
                </label>
            @endforeach
        </div>

        <button type="submit" class="btn btn-primary">📥 Télécharger l'export JSON</button>
    </form>
</div>

{{-- IMPORT --}}
<div class="ie-card">
    <h5>📥 Importer des données</h5>

    <div class="alert alert-warning" style="font-size:12px;">
        ⚠️ <strong>Mode Replace :</strong> efface les données existantes avant import.<br>
        ✅ <strong>Mode Merge :</strong> met à jour les enregistrements existants et ajoute les nouveaux (recommandé).
    </div>

    <form method="POST" action="{{ route('import-export.import') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-3">
            <label class="form-label fw-bold">Fichier JSON d'export</label>
            <input type="file" name="fichier" class="form-control" accept=".json" required>
        </div>

        <div class="mb-3">
            <label class="form-label fw-bold">Mode d'import</label>
            <div class="d-flex gap-3">
                <label style="font-size:13px; cursor:pointer;">
                    <input type="radio" name="mode" value="merge" checked> Merge (recommandé)
                </label>
                <label style="font-size:13px; cursor:pointer;">
                    <input type="radio" name="mode" value="replace"> Replace (écrase tout)
                </label>
            </div>
        </div>

        <button type="submit" class="btn btn-success"
                onclick="return confirm('Confirmer l\'import ? Cette action peut modifier vos données.')">
            🚀 Lancer l'import
        </button>
    </form>
</div>

{{-- GUIDE --}}
<div class="ie-card" style="background:#f0f9ff; border:1px solid #bae6fd;">
    <h5 style="color:#0369a1;">📖 Guide d'utilisation</h5>
    <ol style="font-size:13px; color:#0369a1; line-height:1.8;">
        <li>Sur l'environnement <strong>source</strong> : cliquer sur "Exporter" pour télécharger le fichier JSON</li>
        <li>Sur l'environnement <strong>cible</strong> : cliquer sur "Importer" et sélectionner le fichier JSON</li>
        <li>Choisir le mode <strong>Merge</strong> pour une synchronisation sans perte de données</li>
        <li>Vérifier les données après import depuis le Dashboard</li>
    </ol>
</div>

@endsection