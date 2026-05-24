<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès non autorisé — Eden Group</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { box-sizing:border-box; margin:0; padding:0; }
        body {
            min-height:100vh;
            background:linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #1a4a8a 100%);
            display:flex; align-items:center; justify-content:center;
            font-family:'Segoe UI', sans-serif;
        }
        .card-403 {
            background:white; border-radius:20px; padding:40px 36px;
            max-width:460px; width:100%; text-align:center;
            box-shadow:0 25px 60px rgba(0,0,0,0.35);
        }
        .icon-403 {
            width:80px; height:80px;
            background:linear-gradient(135deg,#fee2e2,#fecaca);
            border-radius:50%; display:inline-flex;
            align-items:center; justify-content:center;
            font-size:36px; margin-bottom:20px;
            border:3px solid #fca5a5;
        }
        h1 { font-size:22px; font-weight:800; color:#1e293b; margin-bottom:8px; }
        p  { font-size:14px; color:#64748b; line-height:1.6; margin-bottom:20px; }
        .role-badge {
            display:inline-block; background:#dbeafe; color:#1d4ed8;
            padding:4px 14px; border-radius:20px; font-size:12px;
            font-weight:700; margin-bottom:24px;
        }
        .btn-home {
            display:inline-block;
            background:linear-gradient(135deg,#1d4ed8,#7c3aed);
            color:white; border:none; border-radius:10px;
            padding:12px 28px; font-size:14px; font-weight:700;
            text-decoration:none; cursor:pointer; transition:opacity 0.2s;
            margin:4px;
        }
        .btn-home:hover { opacity:0.88; color:white; }
        .btn-back {
            display:inline-block;
            background:#f1f5f9; color:#374151; border:none; border-radius:10px;
            padding:12px 28px; font-size:14px; font-weight:600;
            text-decoration:none; cursor:pointer; transition:0.2s;
            margin:4px;
        }
        .btn-back:hover { background:#e2e8f0; color:#111; }
    </style>
</head>
<body>
<div class="card-403">
    <div class="icon-403">🔒</div>
    <h1>Accès non autorisé</h1>
    <p>Vous n'avez pas les droits nécessaires pour accéder à cette page.</p>
    <div class="role-badge">
        Votre rôle : {{ $roleLabel ?? 'Inconnu' }}
    </div>
    <br>
    <div>
        <a href="{{ route('home') }}" class="btn-home">🏠 Retour à l'accueil</a>
        <a href="javascript:history.back()" class="btn-back">← Retour</a>
    </div>
    <div style="margin-top:20px;padding-top:16px;border-top:1px solid #e2e8f0;font-size:11px;color:#94a3b8;">
        Si vous pensez que c'est une erreur, contactez un administrateur.
    </div>
</div>
</body>
</html>