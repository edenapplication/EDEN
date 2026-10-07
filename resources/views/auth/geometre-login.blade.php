<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion Géomètre — EDEN</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #16a34a 0%, #15803d 50%, #065f46 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: -50%; right: -10%;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(255,255,255,0.12) 0%, transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }
        body::after {
            content: '📐';
            position: absolute;
            bottom: 30px; right: 40px;
            font-size: 220px;
            opacity: 0.06;
            pointer-events: none;
            transform: rotate(-15deg);
        }

        .login-wrapper {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }

        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 25px 70px rgba(0,0,0,0.25),
                        0 8px 20px rgba(0,0,0,0.15);
            animation: slideUp 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to   { transform: translateY(0);    opacity: 1; }
        }

        .login-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #16a34a, #15803d);
            border-radius: 50%;
            margin: 0 auto 20px;
            font-size: 40px;
            box-shadow: 0 10px 25px rgba(22,163,74,0.35);
        }

        .badge-role {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, #dcfce7, #bbf7d0);
            color: #14532d;
            padding: 6px 14px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.6px;
        }

        .login-title {
            text-align: center;
            font-size: 24px;
            font-weight: 900;
            color: #14532d;
            margin: 14px 0 6px;
            letter-spacing: -0.5px;
        }
        .login-sub {
            text-align: center;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 28px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #334155;
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }
        .input-wrapper .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 16px;
            color: #94a3b8;
            pointer-events: none;
        }
        .form-group input {
            width: 100%;
            padding: 13px 14px 13px 44px;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            font-size: 14px;
            transition: 0.2s;
            font-family: inherit;
            background: white;
            color: #1e293b;
        }
        .form-group input:focus {
            outline: none;
            border-color: #16a34a;
            box-shadow: 0 0 0 4px rgba(22,163,74,0.12);
            background: #f0fdf4;
        }
        .form-group input.is-invalid {
            border-color: #dc2626;
            background: #fef2f2;
        }

        .btn-toggle-pwd {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            background: transparent;
            border: none;
            width: 36px; height: 36px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 17px;
            color: #64748b;
            transition: 0.2s;
        }
        .btn-toggle-pwd:hover {
            background: rgba(22,163,74,0.1);
            color: #16a34a;
        }

        .remember-row {
            display: flex;
            align-items: center;
            margin-bottom: 22px;
            font-size: 13px;
        }
        .remember-row label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #475569;
            cursor: pointer;
            font-weight: 600;
        }
        .remember-row input[type="checkbox"] {
            width: 16px; height: 16px;
            accent-color: #16a34a;
            cursor: pointer;
        }

        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #16a34a, #15803d);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 900;
            cursor: pointer;
            transition: 0.2s;
            box-shadow: 0 6px 18px rgba(22,163,74,0.35);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-login:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(22,163,74,0.45);
        }
        .btn-login:disabled {
            opacity: 0.7;
            cursor: not-allowed;
        }

        .error-box {
            background: #fee2e2;
            border-left: 4px solid #dc2626;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 12.5px;
            color: #991b1b;
            font-weight: 600;
        }

        .success-box {
            background: #dcfce7;
            border-left: 4px solid #16a34a;
            border-radius: 10px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 12.5px;
            color: #166534;
            font-weight: 600;
        }

        .login-footer {
            text-align: center;
            margin-top: 26px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            font-size: 12px;
            color: #94a3b8;
        }
        .login-footer a {
            color: #16a34a;
            text-decoration: none;
            font-weight: 700;
        }
        .login-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="login-card">

        <div class="login-logo">📐</div>

        <div style="text-align:center;">
            <div class="badge-role">🏗️ Espace Géomètre</div>
        </div>

        <h1 class="login-title">Connexion Géomètre</h1>
        <p class="login-sub">Accédez à vos missions d'implantation</p>

        @if(session('success'))
            <div class="success-box">{{ session('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="error-box">
                @foreach ($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('geometre.login.post') }}" id="geometreLoginForm">
            @csrf

            <div class="form-group">
                <label for="email">📧 Adresse email</label>
                <div class="input-wrapper">
                    <span class="input-icon">✉️</span>
                    <input type="email"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="votre.email@exemple.com"
                           autocomplete="email"
                           required
                           autofocus
                           class="{{ $errors->has('email') ? 'is-invalid' : '' }}">
                </div>
            </div>

            <div class="form-group">
                <label for="password">🔒 Mot de passe</label>
                <div class="input-wrapper">
                    <span class="input-icon">🔐</span>
                    <input type="password"
                           id="password"
                           name="password"
                           placeholder="Votre mot de passe"
                           autocomplete="current-password"
                           required
                           style="padding-right: 48px;"
                           class="{{ $errors->has('password') ? 'is-invalid' : '' }}">
                    <button type="button" class="btn-toggle-pwd" id="btnTogglePwd" title="Afficher le mot de passe">👁</button>
                </div>
            </div>

            <div class="remember-row">
                <label>
                    <input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
                    Se souvenir de moi
                </label>
            </div>

            <button type="submit" class="btn-login" id="btnLogin">
                🔓 Se connecter
            </button>
        </form>
    </div>
</div>

<script>
// 👁 Toggle visibilité du mot de passe
const pwdInput  = document.getElementById('password');
const btnToggle = document.getElementById('btnTogglePwd');
const loginForm = document.getElementById('geometreLoginForm');
const btnLogin  = document.getElementById('btnLogin');

if (btnToggle && pwdInput) {
    btnToggle.addEventListener('click', () => {
        const isPassword = pwdInput.type === 'password';
        pwdInput.type = isPassword ? 'text' : 'password';
        btnToggle.textContent = isPassword ? '🙈' : '👁';
        btnToggle.title = isPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe';
        pwdInput.focus();
    });
}

// ⏳ Loading au submit
if (loginForm && btnLogin) {
    loginForm.addEventListener('submit', () => {
        btnLogin.disabled = true;
        btnLogin.innerHTML = '⏳ Connexion en cours...';
    });
}
</script>

</body>
</html>