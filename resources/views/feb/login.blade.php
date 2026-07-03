<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>FEB — Connexion</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:linear-gradient(135deg,#1e3a5f 0%,#1d4ed8 50%,#7c3aed 100%); min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .login-box { background:white; border-radius:20px; padding:36px 32px; width:420px; box-shadow:0 20px 60px rgba(0,0,0,0.25); }
        .login-title { font-size:22px; font-weight:800; color:#1e3a5f; margin-bottom:4px; }
        .login-sub { font-size:13px; color:#64748b; margin-bottom:28px; }
        .form-control { border-radius:10px; padding:12px 14px; }
        .btn-connexion { background:linear-gradient(135deg,#1d4ed8,#7c3aed); border:none; border-radius:10px; padding:12px; font-weight:700; font-size:15px; width:100%; color:white; }
        .btn-connexion:hover { opacity:0.9; color:white; }
        .divider { border-top:1px solid #e2e8f0; margin:20px 0; text-align:center; }
        .divider span { background:white; padding:0 12px; color:#94a3b8; font-size:12px; position:relative; top:-10px; }
        .btn-admin-link { display:block; text-align:center; font-size:12px; color:#64748b; margin-top:12px; text-decoration:none; }
        .btn-admin-link:hover { color:#1d4ed8; }
    </style>
</head>
<body>
<div class="login-box">
    <div style="text-align:center;margin-bottom:20px;">
        <div style="width:60px;height:60px;background:linear-gradient(135deg,#1d4ed8,#ed3a3a);border-radius:14px;display:inline-flex;align-items:center;justify-content:center;font-size:28px;margin-bottom:10px;">📋</div>
        <div class="login-title">Fiches d'Expression</div>
        <div class="login-sub">Connectez-vous à votre espace</div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger py-2" style="font-size:13px;">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('feb.login.post') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold" style="font-size:13px;">Identifiant</label>
            <input type="text" name="identifiant" class="form-control" placeholder="Votre identifiant" required autofocus value="{{ old('identifiant') }}">
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold" style="font-size:13px;">Mot de passe</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-connexion">Connexion →</button>
    </form>

    <div class="divider"><span>ou</span></div>

    {{-- Lien vers le login admin existant --}}
    <a href="{{ route('login') }}" class="btn-admin-link">⚙️ Accès Administration EDEN GROUP</a>
</div>
</body>
</html>