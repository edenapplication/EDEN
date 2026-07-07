<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>FEB — Mes Fiches</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f9; font-family:"Segoe UI",sans-serif; }
        .topbar { background:linear-gradient(135deg,#1d4ed8,#7c3aed); color:white; padding:0 28px; height:60px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 12px rgba(0,0,0,0.15); position:sticky; top:0; z-index:100; }
        .content { max-width:1100px; margin:30px auto; padding:0 16px; }
        .fiche-card { background:white; border-radius:14px; padding:18px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #1d4ed8; margin-bottom:12px; transition:0.2s; }
        .fiche-card:hover { box-shadow:0 6px 20px rgba(0,0,0,0.1); transform:translateY(-2px); }
        .fiche-card.brouillon { border-left-color:#f59e0b; }
        .fiche-card.soumise   { border-left-color:#16a34a; }
        .badge-statut { font-size:10px; padding:3px 10px; border-radius:20px; font-weight:700; }
        .badge-brouillon { background:#fef3c7; color:#92400e; }
        .badge-soumise   { background:#dcfce7; color:#15803d; }
        .filtre-box { background:white; border-radius:12px; padding:14px; margin-bottom:20px; box-shadow:0 2px 8px rgba(0,0,0,0.05); }
        .btn-nouvelle { background:linear-gradient(135deg,#1d4ed8,#7c3aed); color:white; border:none; border-radius:12px; padding:10px 20px; font-weight:700; font-size:14px; text-decoration:none; }
        .empty-box { text-align:center; padding:60px 20px; color:#94a3b8; }
    </style>
</head>
<body>

<div class="topbar">
    <div style="font-weight:800;font-size:16px;">📋 Fiches d'Expression des Besoins</div>
    <div style="display:flex;align-items:center;gap:14px;">
        <div style="font-size:13px;background:rgba(255,255,255,0.18);padding:6px 14px;border-radius:20px;">
            👤 {{ $user->nom_complet }}
            <span style="font-size:10px;background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:10px;margin-left:6px;">
                {{ $user->agence?->nom ?? 'Aucune agence' }}
            </span>
        </div>
        <form method="POST" action="{{ route('feb.logout') }}" style="display:inline;">
            @csrf
            <button style="background:rgba(255,255,255,0.1);color:white;border:1px solid rgba(255,255,255,0.2);border-radius:8px;padding:5px 12px;font-size:12px;cursor:pointer;">
                Déconnexion
            </button>
        </form>
    </div>
</div>

<div class="content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 style="font-weight:800;color:#1e3a5f;margin:0;">Mes Fiches</h3>
            <div style="font-size:13px;color:#64748b;">{{ $fiches->count() }} fiche(s)</div>
        </div>
        {{-- Remplacer le bouton nouvelle fiche --}}
<a href="{{ route('feb.fiches.creer') }}" class="btn-nouvelle">+ Nouvelle fiche</a>
    </div>

    {{-- Filtres --}}
    <form method="GET" class="filtre-box">
        <div class="row g-2 align-items-end">
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;">Du</label>
                <input type="date" name="du" class="form-control form-control-sm" value="{{ request('du') }}">
            </div>
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;">Au</label>
                <input type="date" name="au" class="form-control form-control-sm" value="{{ request('au') }}">
            </div>
            <div class="col-md-3">
                <label style="font-size:11px;font-weight:700;color:#64748b;">Statut</label>
                <select name="statut" class="form-control form-control-sm">
                    <option value="">Tous</option>
                    <option value="brouillon" {{ request('statut')==='brouillon'?'selected':'' }}>Brouillon</option>
                    <option value="soumise"   {{ request('statut')==='soumise'  ?'selected':'' }}>Soumise</option>
                </select>
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button class="btn btn-primary btn-sm">🔍</button>
                <a href="{{ route('feb.fiches.index') }}" class="btn btn-outline-secondary btn-sm">✖</a>
            </div>
        </div>
    </form>

    {{-- Liste --}}
   @forelse($fiches as $fiche)
<div class="fiche-card {{ $fiche->statut }}">
    <div class="d-flex justify-content-between align-items-start">
        <div>
            <div style="font-weight:700;font-size:15px;color:#1e3a5f;">{{ $fiche->titre }}</div>
            <div style="font-size:12px;color:#64748b;margin-top:3px;">
                📅 {{ $fiche->created_at->format('d/m/Y à H:i') }}
                &nbsp;·&nbsp; {{ $fiche->sections->count() }} section(s)
                @if($fiche->description) &nbsp;·&nbsp; {{ Str::limit($fiche->description, 60) }} @endif
            </div>
        </div>
        <span class="badge-statut badge-{{ $fiche->statut }}">
            {{ $fiche->statut === 'soumise' ? '✅ Soumise' : '⏳ Brouillon' }}
        </span>
    </div>
    <div class="d-flex gap-2 mt-3 flex-wrap">
        {{-- ✅ Continuer un brouillon --}}
        @if($fiche->statut === 'brouillon')
            <a href="{{ route('feb.fiches.continuer', $fiche->id) }}"
               class="btn btn-primary btn-sm" style="font-size:12px;">
                ✏️ Continuer
            </a>
        @endif
        {{-- Utiliser comme modèle --}}
        <a href="{{ route('feb.fiches.utiliser', $fiche->id) }}"
           class="btn btn-outline-secondary btn-sm" style="font-size:12px;">
            📋 Modifier
        </a>
        {{-- PDF --}}
        @if($fiche->statut === 'soumise')
            <a href="{{ route('feb.fiches.pdf', $fiche->id) }}"
               class="btn btn-outline-danger btn-sm" style="font-size:12px;">
                🖨️ Télécharger PDF
            </a>
        @endif
    </div>
</div>
@empty
<div class="empty-box">
    <div style="font-size:48px;">📭</div>
    <div style="font-size:16px;font-weight:700;margin-top:12px;">Aucune fiche créée</div>
    <a href="{{ route('feb.fiches.creer') }}" class="btn-nouvelle mt-3" style="display:inline-block;">
        + Créer ma première fiche
    </a>
</div>
@endforelse
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>