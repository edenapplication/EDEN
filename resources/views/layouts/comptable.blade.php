<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Comptable') — EDEN GROUP</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        :root {
            --comptable-primary: #7c3aed;
            --comptable-dark:    #6d28d9;
            --comptable-light:   #a855f7;
            --comptable-bg:      #faf5ff;
            --comptable-border:  #e9d5ff;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            padding-bottom: 100px;
        }

        /* ═══ BARRE SUPÉRIEURE ═══ */
        .comptable-topbar {
            background: linear-gradient(135deg, var(--comptable-primary), var(--comptable-light));
            color: white;
            padding: 16px 24px;
            box-shadow: 0 4px 16px rgba(124,58,237,0.25);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .comptable-topbar .brand {
            display: flex; align-items: center; gap: 12px;
            font-size: 18px; font-weight: 800;
        }
        .comptable-topbar .brand-icon {
            font-size: 26px;
            background: rgba(255,255,255,0.15);
            width: 44px; height: 44px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
        }
        .comptable-topbar .user-info {
            display: flex; align-items: center; gap: 12px;
            background: rgba(255,255,255,0.12);
            padding: 8px 16px;
            border-radius: 50px;
            font-size: 13px;
        }
        .comptable-topbar .user-badge {
            background: rgba(255,255,255,0.25);
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 700;
        }
        .btn-logout {
            background: rgba(255,255,255,0.15);
            border: none;
            color: white;
            padding: 8px 14px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            font-size: 12px;
            transition: all 0.2s;
        }
        .btn-logout:hover { background: rgba(255,255,255,0.25); }

        /* ═══ NAVIGATION ═══ */
        .comptable-nav {
            background: white;
            border-bottom: 2px solid var(--comptable-border);
            padding: 0 24px;
            position: sticky;
            top: 76px;
            z-index: 99;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            overflow-x: auto;
        }
        .comptable-nav-inner {
            display: flex;
            gap: 4px;
            min-width: max-content;
        }
        .comptable-nav-link {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 14px 20px;
            color: #64748b;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            border-bottom: 3px solid transparent;
            transition: all 0.25s;
            white-space: nowrap;
        }
        .comptable-nav-link:hover {
            color: var(--comptable-primary);
            background: var(--comptable-bg);
        }
        .comptable-nav-link.active {
            color: var(--comptable-primary);
            border-bottom-color: var(--comptable-primary);
            background: var(--comptable-bg);
        }
        .comptable-nav-link i { font-size: 18px; }

        /* ═══ CONTENU ═══ */
        .comptable-content {
            padding: 24px;
            max-width: 1600px;
            margin: 0 auto;
        }

        /* ═══ ALERTES ═══ */
        .alert {
            border-radius: 10px;
            border: none;
            padding: 14px 18px;
            margin-bottom: 16px;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .alert-success { background: #dcfce7; color: #15803d; }
        .alert-danger  { background: #fee2e2; color: #b91c1c; }
        .alert-warning { background: #fef3c7; color: #b45309; }
        .alert-info    { background: #f3e8ff; color: #7c3aed; }

        .btn-comptable {
            background: var(--comptable-primary);
            color: white;
            border: none;
            padding: 8px 18px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            transition: all 0.2s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .btn-comptable:hover {
            background: var(--comptable-dark);
            color: white;
            transform: translateY(-1px);
        }

        ::-webkit-scrollbar { width: 10px; height: 10px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb {
            background: var(--comptable-primary);
            border-radius: 5px;
            border: 2px solid #f1f5f9;
        }

        @media (max-width: 768px) {
            .comptable-topbar { padding: 12px 16px; }
            .comptable-topbar .brand { font-size: 15px; }
            .comptable-nav { padding: 0 12px; }
            .comptable-nav-link { padding: 12px 14px; font-size: 13px; }
            .comptable-content { padding: 16px; }
        }
    </style>

    @yield('styles')
</head>
<body>

{{-- ═══ BARRE SUPÉRIEURE ═══ --}}
<header class="comptable-topbar">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div class="brand">
            <div class="brand-icon">🚗</div>
            <div>
                <div>Espace Comptable Logistique</div>
                <div style="font-size:11px;opacity:0.85;font-weight:500;">
                    EDEN GROUP — Gestion des encaissements logistiques
                </div>
            </div>
        </div>

        <div class="d-flex align-items-center gap-3">
            <div class="user-info">
                <span class="user-badge">🚗 COMPTABLE</span>
                <span style="font-weight:700;">{{ auth()->user()->name ?? 'Utilisateur' }}</span>
                <span style="opacity:0.8;font-size:11px;">
                    🔑 {{ auth()->user()->reference ?? 'N/A' }}
                </span>
            </div>

            <form method="POST" action="{{ route('comptable.auth.logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="btn-logout" title="Se déconnecter">
                    <i class="bi bi-box-arrow-right"></i> Déconnexion
                </button>
            </form>
        </div>
    </div>
</header>

{{-- ═══ NAVIGATION ═══ --}}
<nav class="comptable-nav">
    <div class="comptable-nav-inner">

        <a href="{{ route('comptable.dashboard') }}"
           class="comptable-nav-link {{ request()->routeIs('comptable.dashboard') ? 'active' : '' }}">
            <i class="bi bi-house-door-fill"></i>
            <span>Accueil</span>
        </a>

        <a href="{{ route('comptable.index') }}"
           class="comptable-nav-link {{ request()->routeIs('comptable.index') ? 'active' : '' }}">
            <i class="bi bi-truck"></i>
            <span>Paiement Logistique</span>
        </a>

        <a href="{{ route('comptable.rapport') }}"
           class="comptable-nav-link {{ request()->routeIs('comptable.rapport*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-fill"></i>
            <span>Rapport Logistique</span>
        </a>

        <a href="{{ route('comptable.historique-global') }}"
           class="comptable-nav-link {{ request()->routeIs('comptable.historique-global') ? 'active' : '' }}">
            <i class="bi bi-clock-history"></i>
            <span>Historique global</span>
        </a>

        <a href="{{ route('comptable.mes-paiements') }}"
           class="comptable-nav-link {{ request()->routeIs('comptable.mes-paiements') ? 'active' : '' }}">
            <i class="bi bi-person-check-fill"></i>
            <span>Mes paiements</span>
        </a>

    </div>
</nav>

{{-- ═══ CONTENU ═══ --}}
<main class="comptable-content">

    @if(session('success'))
        <div class="alert alert-success">
            <span>✅ {{ session('success') }}</span>
            <button type="button" onclick="this.parentElement.remove()"
                    style="background:none;border:none;cursor:pointer;font-size:18px;color:#15803d;">×</button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">
            <span>❌ {{ session('error') }}</span>
            <button type="button" onclick="this.parentElement.remove()"
                    style="background:none;border:none;cursor:pointer;font-size:18px;color:#b91c1c;">×</button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning">
            <span>⚠️ {{ session('warning') }}</span>
            <button type="button" onclick="this.parentElement.remove()"
                    style="background:none;border:none;cursor:pointer;font-size:18px;color:#b45309;">×</button>
        </div>
    @endif

    @yield('content')

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

{{-- Toast container --}}
<div id="comptable-toast-container"
     style="position:fixed;bottom:20px;right:20px;z-index:99999;
            display:flex;flex-direction:column;gap:10px;">
</div>

<style>
    @keyframes toastSlideIn {
        from { transform: translateY(20px); opacity: 0; }
        to   { transform: translateY(0); opacity: 1; }
    }
</style>

<script>
function showToastGlobal(message, type = 'success') {
    const container = document.getElementById('comptable-toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.style.cssText = `
        background: ${type === 'success' ? '#16a34a' : (type === 'error' ? '#dc2626' : '#1f2937')};
        color: white; padding: 14px 22px; border-radius: 10px;
        font-size: 14px; font-weight: 600;
        box-shadow: 0 10px 30px rgba(0,0,0,0.25);
        max-width: 400px;
        animation: toastSlideIn 0.3s ease;
    `;
    toast.textContent = message;
    container.appendChild(toast);

    setTimeout(() => {
        toast.style.transition = 'all 0.3s ease';
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(20px)';
        setTimeout(() => toast.remove(), 300);
    }, 3500);
}
</script>

@yield('scripts')
</body>
</html>