<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>RH — Eden Group</title>

    {{-- ✅ PWA Meta --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Eden RH">
    <meta name="theme-color" content="#1d4ed8">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * { box-sizing:border-box; }
        body { background:#f4f6f9; font-family:"Segoe UI",sans-serif; margin:0; }

        /* ========== SPLASH ========== */
        #splash {
            position:fixed; inset:0;
            background:linear-gradient(135deg,#1d4ed8 0%,#7c3aed 55%,#dc2626 100%);
            display:flex; flex-direction:column;
            justify-content:center; align-items:center;
            color:white; z-index:999999; transition:opacity 0.4s ease;
        }
        #splash .splash-logo {
            width:90px; height:90px; border-radius:20px;
            background:rgba(255,255,255,0.18); font-size:40px;
            display:flex; align-items:center; justify-content:center;
            margin-bottom:18px; box-shadow:0 8px 32px rgba(0,0,0,0.2);
        }
        #splash .splash-title { font-size:24px; font-weight:900; margin-bottom:5px; }
        #splash .splash-sub { font-size:12px; opacity:0.7; }
        #splash .splash-loader { width:44px; height:4px; background:rgba(255,255,255,0.2); border-radius:2px; margin-top:28px; overflow:hidden; }
        #splash .splash-loader-bar { height:100%; background:white; border-radius:2px; animation:splashLoad 0.9s ease forwards; }
        @keyframes splashLoad { from{width:0%} to{width:100%} }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width:230px; height:100vh; position:fixed; top:0; left:0;
            background:#0f172a; display:flex; flex-direction:column;
            overflow-y:auto; z-index:200;
            scrollbar-width:thin; scrollbar-color:rgba(255,255,255,0.1) transparent;
        }
        .sidebar::-webkit-scrollbar { width:4px; }
        .sidebar::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:2px; }

        .logo-box {
            padding:18px 16px 14px; border-bottom:1px solid rgba(255,255,255,0.08);
            display:flex; flex-direction:column; align-items:center; gap:8px;
        }
        .logo-circle {
            width:60px; height:60px;
            background:linear-gradient(135deg,#1d4ed8,#7c3aed,#dc2626);
            border-radius:12px; display:flex; align-items:center;
            justify-content:center; font-size:22px; font-weight:900; color:white;
        }
        .logo-name { font-size:14px; font-weight:800; color:white; }
        .logo-sub  { font-size:10px; color:#475569; }

        .nav-group-btn {
            display:flex; align-items:center; justify-content:space-between;
            width:100%; padding:9px 16px; background:none; border:none;
            color:#94a3b8; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:1px; cursor:pointer; transition:0.15s;
        }
        .nav-group-btn:hover { color:#cbd5e1; }
        .nav-group-btn .chevron { font-size:10px; transition:transform 0.2s; }
        .nav-group-btn.open .chevron { transform:rotate(90deg); }
        .nav-group-items { display:none; padding-bottom:4px; }
        .nav-group-items.open { display:block; }

        .sidebar a {
            display:flex; align-items:center; gap:8px;
            color:#cbd5e1; padding:8px 20px; text-decoration:none;
            font-size:13px; transition:0.15s; border-left:3px solid transparent;
        }
        .sidebar a:hover { background:rgba(255,255,255,0.06); color:white; border-left-color:#7c3aed; }
        .sidebar a.active { background:rgba(255,255,255,0.08); color:white; border-left-color:#60a5fa; }

        /* ========== TOPBAR ========== */
        .topbar {
            position:fixed; top:0; left:230px; right:0; height:60px; z-index:100;
            background:linear-gradient(135deg,#1d4ed8 0%,#7c3aed 55%,#dc2626 100%);
            color:white; display:flex; align-items:center;
            justify-content:space-between; padding:0 28px;
            box-shadow:0 2px 12px rgba(29,78,216,0.35);
        }
        .topbar-right { display:flex; align-items:center; gap:10px; }
        .topbar-user {
            display:flex; align-items:center; gap:8px; font-size:13px;
            background:rgba(255,255,255,0.18); padding:6px 14px; border-radius:20px;
        }
        .topbar-link {
            background:rgba(255,255,255,0.15); color:white;
            border:1px solid rgba(255,255,255,0.25); border-radius:8px;
            padding:5px 12px; font-size:11px; font-weight:600;
            text-decoration:none; transition:0.2s;
        }
        .topbar-link:hover { background:rgba(255,255,255,0.25); color:white; }

        /* ========== CONTENU ========== */
        .content { margin-left:230px; padding:24px; padding-top:84px; }

        /* ========== PWA ========== */
        #pwa-update-banner {
            position:fixed; bottom:20px; left:50%; transform:translateX(-50%);
            background:#1d4ed8; color:white; border-radius:12px;
            padding:12px 20px; z-index:999999;
            align-items:center; gap:12px;
            box-shadow:0 8px 24px rgba(0,0,0,0.25);
            font-size:13px; font-weight:600;
            max-width:90vw; display:none;
        }

        /* ========== RESPONSIVE MOBILE ========== */
        @media (max-width: 768px) {
            .sidebar {
                transform:translateX(-100%); transition:transform 0.3s ease; z-index:300;
            }
            .sidebar.open { transform:translateX(0); }
            .topbar { left:0; padding:0 16px; }
            .content { margin-left:0; padding:16px; padding-top:76px; }
            #sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:299; }
            #sidebar-overlay.open { display:block; }
            #menu-toggle {
                background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25);
                color:white; border-radius:8px; padding:5px 10px;
                font-size:16px; cursor:pointer; margin-right:8px;
            }
            .topbar-user .role-badge { display:none; }
        }
    </style>
</head>

<script>
(function() {
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
    if (isStandalone && !sessionStorage.getItem('edenSplashDone')) {
        document.write(`
            <div id="splash">
                <div class="splash-logo">🏢</div>
                <div class="splash-title">EDEN RH</div>
                <div class="splash-sub">Ressources Humaines</div>
                <div class="splash-loader"><div class="splash-loader-bar"></div></div>
            </div>
        `);
    }
})();
</script>

<body>

<div id="sidebar-overlay" onclick="fermerSidebar()"></div>

{{-- ========== SIDEBAR ========== --}}
<div class="sidebar" id="sidebar">
    <div>
        <div class="logo-box">
            <div class="logo-circle">RH</div>
            <div class="logo-name">EDEN GROUP</div>
            <div class="logo-sub">Module Ressources Humaines</div>
        </div>

        <div style="padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
            <a href="{{ route('rh.dashboard') }}" onclick="fermerSidebar()"
               class="{{ request()->routeIs('rh.dashboard') ? 'active' : '' }}">
                <span>📊</span> Dashboard RH
            </a>
        </div>

        <div style="padding:6px 0;">

            <button class="nav-group-btn" onclick="toggleGroup('g-param', this)">
                <span>⚙️ Paramètres</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-param">
                <a href="{{ route('rh.directions.index') }}" onclick="fermerSidebar()"><span>🏢</span> Directions & Services</a>
            </div>

            <button class="nav-group-btn" onclick="toggleGroup('g-emp', this)">
                <span>👥 Employés</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-emp">
                <a href="{{ route('rh.employes.index') }}" onclick="fermerSidebar()"><span>📋</span> Liste</a>
                <a href="{{ route('rh.employes.create') }}" onclick="fermerSidebar()"><span>➕</span> Nouveau</a>
            </div>

            <button class="nav-group-btn" onclick="toggleGroup('g-paie', this)">
                <span>💰 Paie</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-paie">
                <a href="{{ route('rh.paie.index') }}" onclick="fermerSidebar()"><span>📄</span> Bulletins</a>
                <a href="{{ route('rh.paie.create') }}" onclick="fermerSidebar()"><span>➕</span> Nouveau bulletin</a>
                <a href="{{ route('rh.paie.recapitulatif') }}" onclick="fermerSidebar()"><span>📊</span> Récapitulatif</a>
            </div>

            <button class="nav-group-btn" onclick="toggleGroup('g-abs', this)">
                <span>🗓️ Absences</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-abs">
                <a href="{{ route('rh.absences.index') }}" onclick="fermerSidebar()"><span>📋</span> Suivi absences</a>
            </div>

            <button class="nav-group-btn" onclick="toggleGroup('g-pret', this)">
                <span>🏦 Prêts & Acomptes</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-pret">
                <a href="{{ route('rh.prets.index') }}" onclick="fermerSidebar()"><span>📋</span> Liste</a>
            </div>

            <button class="nav-group-btn" onclick="toggleGroup('g-sanc', this)">
                <span>⚠️ Sanctions</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-sanc">
                <a href="{{ route('rh.sanctions.index') }}" onclick="fermerSidebar()"><span>📋</span> Liste</a>
            </div>

            <button class="nav-group-btn" onclick="toggleGroup('g-ret', this)">
                <span>⏱️ Retards</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-ret">
                <a href="{{ route('rh.retards.index') }}" onclick="fermerSidebar()"><span>📋</span> Liste</a>
            </div>

        </div>
    </div>
</div>

{{-- ========== TOPBAR ========== --}}
<div class="topbar">
    <div style="display:flex;align-items:center;gap:8px;">
        <button id="menu-toggle" onclick="toggleSidebar()" class="d-md-none">☰</button>
        <div style="font-weight:700;font-size:15px;">🏢 RH — EDEN GROUP</div>
    </div>
    <div class="topbar-right">
        <a href="{{ route('home') }}" class="topbar-link">🏠 Accueil</a>
        @if(auth()->user()?->isAdmin())
        <a href="{{ route('admin.dashboard') }}" class="topbar-link">⚙️ Admin</a>
        @endif
        <div class="topbar-user">
            <span>👤</span>
            <span style="font-size:12px;">{{ auth()->user()?->name }}</span>
            <span class="role-badge" style="font-size:10px;background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:10px;">
                {{ ['admin'=>'Admin','rh'=>'RH','commercial'=>'Commercial'][auth()->user()?->role] ?? '' }}
            </span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit"
                        style="background:rgba(255,255,255,0.1);color:white;border:1px solid rgba(255,255,255,0.2);border-radius:6px;padding:4px 10px;font-size:11px;cursor:pointer;">
                    Déconnexion
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ========== CONTENU ========== --}}
<div class="content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @yield('content')
</div>

<div id="pwa-update-banner">
    🔄 Mise à jour disponible
    <button onclick="appliquerMAJ()"
            style="background:white;color:#1d4ed8;border:none;border-radius:8px;padding:5px 12px;font-weight:700;cursor:pointer;font-size:12px;">
        Mettre à jour
    </button>
    <button onclick="document.getElementById('pwa-update-banner').style.display='none'"
            style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;font-size:18px;padding:0;">✕</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ============================================================
// SPLASH
// ============================================================
window.addEventListener('load', function() {
    const splash = document.getElementById('splash');
    if (!splash) return;
    setTimeout(() => {
        splash.style.opacity = '0';
        setTimeout(() => { splash.remove(); sessionStorage.setItem('edenSplashDone','1'); }, 400);
    }, 1000);
});

// ============================================================
// SIDEBAR
// ============================================================
function toggleGroup(id, btn) {
    const items = document.getElementById(id);
    const isOpen = items.classList.contains('open');
    document.querySelectorAll('.nav-group-items').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.nav-group-btn').forEach(el   => el.classList.remove('open'));
    if (!isOpen) { items.classList.add('open'); btn.classList.add('open'); }
}

document.addEventListener('DOMContentLoaded', function() {
    const url = window.location.pathname;
    const map = {
        'g-emp'  : '/rh/employes',
        'g-paie' : '/rh/paie',
        'g-abs'  : '/rh/absences',
        'g-pret' : '/rh/prets',
        'g-sanc' : '/rh/sanctions',
        'g-ret'  : '/rh/retards',
        'g-param': '/rh/directions',
    };
    Object.entries(map).forEach(([id, prefix]) => {
        if (url.includes(prefix)) {
            const el  = document.getElementById(id);
            const btn = el?.previousElementSibling;
            if (el)  el.classList.add('open');
            if (btn) btn.classList.add('open');
        }
    });
    document.querySelectorAll('.sidebar a').forEach(a => {
        try { if (a.href && url === new URL(a.href).pathname) a.classList.add('active'); } catch(e) {}
    });
});

function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebar-overlay').classList.toggle('open');
}
function fermerSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('open');
}

// ============================================================
// PWA
// ============================================================
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(reg => {
            reg.addEventListener('updatefound', () => {
                const nw = reg.installing;
                nw.addEventListener('statechange', () => {
                    if (nw.state === 'installed' && navigator.serviceWorker.controller) {
                        document.getElementById('pwa-update-banner').style.display = 'flex';
                    }
                });
            });
        }).catch(e => console.warn('SW:', e));
    });
}

function appliquerMAJ() {
    navigator.serviceWorker.getRegistration().then(reg => {
        if (reg?.waiting) reg.waiting.postMessage('skipWaiting');
    });
    window.location.reload();
}
</script>

@yield('scripts')
</body>
</html>