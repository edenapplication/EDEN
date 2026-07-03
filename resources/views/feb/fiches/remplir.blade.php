<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Remplir la fiche</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f9; font-family:"Segoe UI",sans-serif; }
        .topbar { background:linear-gradient(135deg,#1d4ed8,#7c3aed); color:white; padding:0 28px; height:60px; display:flex; align-items:center; justify-content:space-between; position:sticky; top:0; z-index:100; }
        .content { max-width:800px; margin:30px auto; padding:0 16px; }
        .section-bloc { background:white; border-radius:14px; padding:20px; margin-bottom:14px; box-shadow:0 2px 8px rgba(0,0,0,0.06); border-left:4px solid #1d4ed8; }
        .section-ok { border-left-color:#16a34a; }
        .btn-entrer { background:#1d4ed8; color:white; border:none; border-radius:10px; padding:10px 20px; font-weight:700; text-decoration:none; font-size:13px; }
        .btn-entrer:hover { background:#1e40af; color:white; }
        .btn-soumettre { background:linear-gradient(135deg,#16a34a,#15803d); color:white; border:none; border-radius:12px; padding:12px 28px; font-weight:800; font-size:15px; cursor:pointer; }
    </style>
</head>
<body>
<div class="topbar">
    <div>
        <div style="font-weight:800;">📋 {{ $fiche->titre }}</div>
        <div style="font-size:11px;opacity:0.8;">Étape 2 — Remplissage des sections</div>
    </div>
    <a href="{{ route('feb.fiches.index') }}" style="color:rgba(255,255,255,0.8);text-decoration:none;font-size:12px;">← Mes fiches</a>
</div>

<div class="content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div style="background:#dbeafe;border-radius:12px;padding:14px 18px;margin-bottom:20px;font-size:13px;color:#1d4ed8;">
        ℹ️ Remplissez chaque section puis soumettez votre fiche. Vous pouvez revenir sur une section tant que la fiche n'est pas soumise.
    </div>

    @foreach($fiche->sections as $section)
    @php $aDesLignes = $section->lignes->count() > 0; @endphp
    <div class="section-bloc {{ $aDesLignes ? 'section-ok' : '' }}">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <div style="font-weight:700;font-size:15px;color:#1e3a5f;">
                    {{ $aDesLignes ? '✅' : '⏳' }} {{ $section->titre }}
                </div>
                <div style="font-size:12px;color:#64748b;margin-top:2px;">
                    @if($aDesLignes)
                        {{ $section->colonnes->count() }} colonne(s) · {{ $section->lignes->count() }} ligne(s)
                    @else
                        Non remplie
                    @endif
                </div>
            </div>
            <a href="{{ route('feb.fiches.section', [$fiche->id, $section->id]) }}" class="btn-entrer">
                {{ $aDesLignes ? '✏️ Modifier' : '→ Remplir' }}
            </a>
        </div>
    </div>
    @endforeach

    {{-- Bouton soumettre --}}
    <div style="text-align:center;margin-top:28px;">
        <form method="POST" action="{{ route('feb.fiches.soumettre', $fiche->id) }}"
              onsubmit="return confirm('Soumettre définitivement cette fiche ? Elle ne pourra plus être modifiée.')">
            @csrf
            <button type="submit" class="btn-soumettre">
                ✅ Soumettre la fiche
            </button>
        </form>
        <div style="font-size:12px;color:#94a3b8;margin-top:8px;">La fiche sera envoyée à l'administration.</div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>