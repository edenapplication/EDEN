<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion Comptable — EDEN GROUP</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #4c1d95 0%, #7c3aed 50%, #a855f7 100%);
            position: relative;
            overflow: hidden;
            padding: 20px;
        }

        body::before {
            content: '';
            position: absolute;
            width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            top: -200px; right: -200px;
            border-radius: 50%;
        }
        body::after {
            content: '';
            position: absolute;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
            bottom: -150px; left: -150px;
            border-radius: 50%;
        }

        .login-wrapper {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 440px;
            overflow: hidden;
            position: relative;
            z-index: 1;
            animation: slideUp 0.4s ease;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(20px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .login-header {
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            padding: 32px 30px 26px;
            text-align: center;
            color: white;
            position: relative;
        }
        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0; right: 0;
            height: 20px;
            background: white;
            border-radius: 20px 20px 0 0;
        }

        .login-icon {
            width: 80px; height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 42px;
            margin: 0 auto 14px;
            border: 3px solid rgba(255,255,255,0.3);
        }

        .login-header h1 { font-size: 22px; font-weight: 800; margin-bottom: 4px; }
        .login-header p  { font-size: 13px; opacity: 0.9; }

        .login-body { padding: 30px; position: relative; z-index: 1; }

        .role-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #faf5ff;
            color: #6b21a8;
            padding: 6px 14px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 20px;
        }

        .form-group { margin-bottom: 16px; }

        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #475569;
            margin-bottom: 6px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .form-input {
            width: 100%;
            padding: 13px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.2s;
            background: #f8fafc;
            font-family: inherit;
        }
        .form-input:focus {
            outline: none;
            border-color: #7c3aed;
            background: white;
            box-shadow: 0 0 0 4px rgba(124,58,237,0.1);
        }
        .form-input.is-invalid {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .input-icon-wrapper { position: relative; }
        .input-icon-wrapper i {
            position: absolute;
            left: 14px; top: 50%;
            transform: translateY(-50%);
            font-size: 18px;
            color: #94a3b8;
            pointer-events: none;
        }
        .input-icon-wrapper .form-input { padding-left: 44px; }

        .error-box {
            background: #fee2e2;
            color: #b91c1c;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 18px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            border-left: 4px solid #dc2626;
        }
        .error-box i { font-size: 18px; flex-shrink: 0; }

        .success-box {
            background: #dcfce7;
            color: #15803d;
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 18px;
            display: flex;
            align-items: flex-start;
            gap: 8px;
            border-left: 4px solid #16a34a;
        }
        .success-box i { font-size: 18px; flex-shrink: 0; }

        .remember-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
            font-size: 12px;
        }
        .remember-row label {
            display: flex;
            align-items: center;
            gap: 6px;
            color: #64748b;
            cursor: pointer;
            font-weight: 600;
        }
        .remember-row input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: #7c3aed;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(135deg, #7c3aed, #a855f7);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            font-family: inherit;
            letter-spacing: 0.3px;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(124,58,237,0.4);
        }
        .btn-login:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

        .login-footer {
            padding: 18px 30px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            text-align: center;
            font-size: 12px;
            color: #64748b;
        }
        .login-footer a { color: #7c3aed; text-decoration: none; font-weight: 700; }
        .login-footer a:hover { text-decoration: underline; }

        .info-block {
            margin-top: 16px;
            padding: 12px 14px;
            background: #faf5ff;
            border-left: 4px solid #7c3aed;
            border-radius: 8px;
            font-size: 11px;
            color: #6b21a8;
            line-height: 1.5;
        }
        .info-block strong { font-weight: 800; }

        @media (max-width: 480px) {
            .login-header { padding: 26px 24px 22px; }
            .login-header h1 { font-size: 19px; }
            .login-body { padding: 24px; }
            .login-icon { width: 66px; height: 66px; font-size: 34px; }
        }
    </style>
</head>
<body>

<div class="login-wrapper">

    {{-- EN-TÊTE --}}
    <div class="login-header">
        <div class="login-icon">🚗</div>
        <h1>Espace Comptable Logistique</h1>
        <p>EDEN GROUP — Gestion des encaissements</p>
    </div>

    {{-- CORPS --}}
    <div class="login-body">

        <div class="role-badge">
            <i class="bi bi-shield-lock-fill"></i>
            Accès réservé au personnel comptable
        </div>

        @if($errors->any())
            <div class="error-box">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        @if(session('success'))
            <div class="success-box">
                <i class="bi bi-check-circle-fill"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if(session('error'))
            <div class="error-box">
                <i class="bi bi-x-circle-fill"></i>
                <div>{{ session('error') }}</div>
            </div>
        @endif

        {{-- FORMULAIRE --}}
        <form method="POST" action="{{ route('comptable.auth.login.post') }}" id="comptableLoginForm">
            @csrf

            <div class="form-group">
                <label for="email">
                    <i class="bi bi-envelope-fill"></i> Adresse email
                </label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-envelope"></i>
                    <input type="email"
                           id="email"
                           name="email"
                           class="form-input @error('email') is-invalid @enderror"
                           value="{{ old('email') }}"
                           placeholder="comptable@eden.cm"
                           required
                           autofocus
                           autocomplete="username">
                </div>
            </div>

            <div class="form-group">
                <label for="password">
                    <i class="bi bi-lock-fill"></i> Mot de passe
                </label>
                <div class="input-icon-wrapper">
                    <i class="bi bi-lock"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-input @error('password') is-invalid @enderror"
                           placeholder="••••••••"
                           required
                           autocomplete="current-password">
                </div>
            </div>

            <div class="remember-row">
                <label>
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    Se souvenir de moi
                </label>
                <span style="color:#94a3b8;font-size:11px;">
                    <i class="bi bi-shield-check"></i> Connexion sécurisée
                </span>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                <i class="bi bi-box-arrow-in-right"></i>
                <span>Se connecter</span>
            </button>

        </form>

        <div class="info-block">
            <strong>ℹ️ Information :</strong><br>
            Cette page est réservée aux <strong>comptables logistiques</strong> et aux <strong>administrateurs</strong>.<br>
            Pour tout autre accès, contactez votre administrateur.
        </div>

    </div>

    {{-- PIED --}}
    <div class="login-footer">
        🔒 Connexion sécurisée — <strong>EDEN GROUP</strong> © {{ date('Y') }}
    </div>

</div>

<script>
document.getElementById('comptableLoginForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('btnLogin');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Connexion en cours...';
    }
});
</script>

</body>
</html>