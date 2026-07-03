<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"><link rel="manifest" href="/manifest.json?v=1">
<meta name="theme-color" content="#1d4ed8">
<link rel="manifest" href="/manifest.json">
<link rel="apple-touch-icon" href="/images/icon-192.png">
    <title>Connexion — EDEN GROUP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 50%, #2d6cdf 100%);
            display: flex; align-items: center; justify-content: center;
            font-family: 'Segoe UI', sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px 36px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.35);
        }
        .logo-zone {
            text-align: center;
            margin-bottom: 32px;
        }
    .logo-icon {
    width: 80px;
    height: 80px;
    border-radius: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;

    background: transparent;   /* ❌ on enlève le bleu */
    box-shadow: none;          /* optionnel: enlève l’ombre */
    margin: 0 auto 12px auto;
}

.logo-icon img {
    width: 100%;
    height: 100%;
    object-fit: contain; /* ou cover si tu veux remplir totalement */
    display: block;
    object-fit: contain;
}
        .logo-zone h1 { font-size: 24px; font-weight: 800; color: #1e3a5f; }
        .logo-zone p  { font-size: 13px; color: #64748b; margin-top: 3px; }
        .form-label { font-weight: 600; font-size: 13px; color: #374151; }
        .form-control {
            border-radius: 10px; padding: 11px 14px;
            border: 1.5px solid #e2e8f0; font-size: 14px;
            transition: border-color 0.2s;
        }
        .form-control:focus { border-color: #2d6cdf; box-shadow: 0 0 0 3px rgba(45,108,223,0.12); }
        .btn-login {
            background: linear-gradient(135deg, #1e3a5f, #2d6cdf);
            color: white; border: none; border-radius: 10px;
            padding: 12px; font-size: 15px; font-weight: 700;
            width: 100%; cursor: pointer; transition: opacity 0.2s;
            margin-top: 8px;
        }
        .btn-login:hover { opacity: 0.9; }
        .alert-danger { border-radius: 10px; font-size: 13px; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="logo-zone">
        <div class="logo-icon"><img src="/images/eden_login.webp" alt="EDEN GROUP"></div>
        <h1>EDEN GROUP</h1>
        <p>Système de gestion EDEN GROUP</p>
    </div>

    @if($errors->any())
        <div class="alert alert-danger mb-4">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('login.post') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Adresse e-mail</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email') }}" placeholder="votre@email.cm" required autofocus>
        </div>
        <div class="mb-4">
            <label class="form-label">Mot de passe</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="mb-4 d-flex align-items-center gap-2">
            <input type="checkbox" name="remember" id="remember" class="form-check-input" style="width:16px;height:16px;">
            <label for="remember" style="font-size:13px;color:#64748b;cursor:pointer;">Se souvenir de moi</label>
        </div>
        <button type="submit" class="btn-login">Se connecter →</button>
        <div style="text-align:center;margin-top:20px;padding-top:16px;border-top:1px solid #e2e8f0;">
    <a href="{{ route('feb.login') }}"
       style="display:inline-flex;align-items:center;gap:8px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:10px 18px;color:#1d4ed8;text-decoration:none;font-size:13px;font-weight:600;transition:0.2s;">
        📋 Accès Fiches d'Expression des Besoins
    </a>
</div>
    </form>
</div>
</body>
</html>