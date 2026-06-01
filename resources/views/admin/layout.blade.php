<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Eden Admin</title>

    {{-- ✅ PWA Meta --}}
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Eden Admin">
    <meta name="theme-color" content="#1d4ed8">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }
        body { background:#f4f6f9; font-family:"Segoe UI",sans-serif; margin:0; }

        /* ========== SPLASH SCREEN ========== */
        #splash {
            position:fixed; inset:0;
            background:linear-gradient(135deg,#1d4ed8 0%,#7c3aed 55%,#dc2626 100%);
            display:flex; flex-direction:column;
            justify-content:center; align-items:center;
            color:white; z-index:999999;
            transition:opacity 0.4s ease;
        }
        #splash .splash-logo {
            width:110px; height:110px; border-radius:24px;
            background:rgba(255,255,255,0.15);
            display:flex; align-items:center; justify-content:center;
            font-size:52px; margin-bottom:20px;
            box-shadow:0 8px 32px rgba(0,0,0,0.2);
        }
        #splash .splash-logo img {
            width:100%; height:100%; object-fit:contain; border-radius:20px;
        }
        #splash .splash-title {
            font-size:26px; font-weight:900; letter-spacing:1px; margin-bottom:6px;
        }
        #splash .splash-sub {
            font-size:13px; opacity:0.7;
        }
        #splash .splash-loader {
            width:48px; height:4px; background:rgba(255,255,255,0.25);
            border-radius:2px; margin-top:32px; overflow:hidden;
        }
        #splash .splash-loader-bar {
            height:100%; background:white; border-radius:2px;
            animation:splashLoad 0.9s ease forwards;
        }
        @keyframes splashLoad {
            from { width:0% }
            to   { width:100% }
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width:230px; height:100vh; position:fixed; top:0; left:0;
            background:#0f172a;
            display:flex; flex-direction:column;
            overflow-y:auto; overflow-x:hidden;
            z-index:200;
            scrollbar-width:thin; scrollbar-color:rgba(255,255,255,0.1) transparent;
        }
        .sidebar::-webkit-scrollbar { width:4px; }
        .sidebar::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:2px; }

        .logo-box {
            padding:20px 16px 14px;
            border-bottom:1px solid rgba(255,255,255,0.08);
            display:flex; flex-direction:column; align-items:center; gap:10px;
        }
        .logo-img-wrap {
            width:180px; height:150px; border-radius:14px;
            overflow:hidden; display:flex;
            align-items:center; justify-content:center;
        }
        .logo-img-wrap img {
            width:100%; height:100%; object-fit:contain;
        }
        .logo-placeholder-letter {
            width:80px; height:80px;
            background:linear-gradient(135deg,#1d4ed8 0%,#7c3aed 50%,#dc2626 100%);
            border-radius:14px; display:flex; align-items:center;
            justify-content:center; font-size:28px; font-weight:900; color:white;
            box-shadow:0 4px 18px rgba(0,0,0,0.4);
        }
        .logo-name {
            font-size:14px; font-weight:800; letter-spacing:0.5px;
            background:linear-gradient(90deg,#60a5fa,#a78bfa,#f87171);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            background-clip:text;
        }
        .logo-sub { font-size:10px; color:#475569; margin-top:-4px; }

        .nav-section { padding:10px 0; }
        .nav-group-btn {
            display:flex; align-items:center; justify-content:space-between;
            width:100%; padding:9px 16px; background:none; border:none;
            color:#94a3b8; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:1px;
            cursor:pointer; transition:0.15s;
        }
        .nav-group-btn:hover { color:#cbd5e1; }
        .nav-group-btn .chevron { font-size:10px; transition:transform 0.2s; }
        .nav-group-btn.open .chevron { transform:rotate(90deg); }
        .nav-group-items { display:none; padding-bottom:4px; }
        .nav-group-items.open { display:block; }

        .sidebar a {
            display:flex; align-items:center; gap:8px;
            color:#cbd5e1; padding:8px 20px; text-decoration:none;
            font-size:13px; transition:0.15s;
            border-left:3px solid transparent;
        }
        .sidebar a:hover {
            background:rgba(255,255,255,0.06); color:white;
            border-left-color:#7c3aed;
        }
        .sidebar a.active {
            background:rgba(255,255,255,0.08); color:white;
            border-left-color:#60a5fa;
        }
        .nav-direct { padding:4px 0 8px; border-bottom:1px solid rgba(255,255,255,0.06); }

        /* ========== TOPBAR FIXE ========== */
        .topbar {
            position:fixed; top:0; left:230px; right:0; height:60px; z-index:100;
            background:linear-gradient(135deg,#1d4ed8 0%,#7c3aed 55%,#dc2626 100%);
            color:white; display:flex; align-items:center;
            justify-content:space-between; padding:0 28px;
            box-shadow:0 2px 12px rgba(29,78,216,0.35);
        }
        .topbar-title { font-weight:700; font-size:15px; letter-spacing:0.3px; }
        .topbar-right  { display:flex; align-items:center; gap:10px; flex-wrap:nowrap; }
        .topbar-user {
            display:flex; align-items:center; gap:8px; font-size:13px;
            background:rgba(255,255,255,0.18); padding:6px 14px; border-radius:20px;
        }
        .topbar-link {
            background:rgba(255,255,255,0.15); color:white;
            border:1px solid rgba(255,255,255,0.25); border-radius:8px;
            padding:5px 12px; font-size:11px; font-weight:600;
            text-decoration:none; transition:0.2s; white-space:nowrap;
        }
        .topbar-link:hover { background:rgba(255,255,255,0.25); color:white; }

        /* ========== CONTENU ========== */
        .content { margin-left:230px; padding:24px; padding-top:84px; }

        /* ========== PWA — Bannière mise à jour ========== */
        #pwa-update-banner {
            position:fixed; bottom:20px; left:50%; transform:translateX(-50%);
            background:#1d4ed8; color:white; border-radius:12px;
            padding:12px 20px; z-index:999999;
            display:flex; align-items:center; gap:12px;
            box-shadow:0 8px 24px rgba(0,0,0,0.25);
            font-size:13px; font-weight:600;
            max-width:90vw; display:none;
        }
        #pwa-install-btn {
            position:fixed; bottom:20px; right:20px; z-index:99998; display:none;
        }
        #pwa-install-btn button {
            background:linear-gradient(135deg,#1d4ed8,#7c3aed);
            color:white; border:none; border-radius:14px;
            padding:12px 18px; font-size:13px; font-weight:700;
            cursor:pointer; box-shadow:0 8px 24px rgba(29,78,216,0.4);
            display:flex; align-items:center; gap:8px;
        }

        /* ========== RESPONSIVE MOBILE ========== */
        @media (max-width: 768px) {
            .sidebar {
                transform:translateX(-100%);
                transition:transform 0.3s ease;
                z-index:300;
            }
            .sidebar.open { transform:translateX(0); }
            .topbar { left:0; padding:0 16px; }
            .content { margin-left:0; padding:16px; padding-top:76px; }

            /* Overlay pour fermer la sidebar sur mobile */
            #sidebar-overlay {
                display:none; position:fixed; inset:0;
                background:rgba(0,0,0,0.5); z-index:299;
            }
            #sidebar-overlay.open { display:block; }

            /* Bouton hamburger */
            #menu-toggle {
                background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25);
                color:white; border-radius:8px; padding:5px 10px;
                font-size:16px; cursor:pointer; margin-right:8px;
            }

            /* Masquer les textes longs sur mobile */
            .topbar-title { font-size:13px; }
            .topbar-user .role-badge { display:none; }
        }
    </style>
</head>

{{-- ========== SPLASH SCREEN ========== --}}
<script>
// Afficher le splash uniquement au premier chargement de la session PWA
(function() {
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
    const splashDone   = sessionStorage.getItem('edenSplashDone');

    if (isStandalone && !splashDone) {
        document.write(`
            <div id="splash">
                <div class="splash-logo">
                    <img src="/images/eden.webp" alt="Eden" onerror="this.style.display='none';this.parentNode.innerHTML='🏢'">
                </div>
                <div class="splash-title">EDEN GROUP</div>
                <div class="splash-sub">Administration · RH · Foncier</div>
                <div class="splash-loader"><div class="splash-loader-bar"></div></div>
            </div>
        `);
    }
})();
</script>

<body>

{{-- Overlay mobile --}}
<div id="sidebar-overlay" onclick="fermerSidebar()"></div>

{{-- ========== SIDEBAR ========== --}}
<div class="sidebar" id="sidebar">
    <div>
        {{-- LOGO --}}
        <div class="logo-box">
            @if(file_exists(public_path('images/eden.webp')) || file_exists(public_path('images/eden_group.png')))
                <div class="logo-img-wrap">
                    <img src="{{ file_exists(public_path('images/eden.webp')) ? asset('images/eden.webp') : asset('images/eden_group.png') }}"
                         alt="Logo Eden Group"
                         onerror="this.parentNode.innerHTML='<div class=\'logo-placeholder-letter\'>E</div>'">
                </div>
            @else
                <div class="logo-placeholder-letter">E</div>
            @endif
            <div>
                <div class="logo-name">EDEN GROUP</div>
                <div class="logo-sub">Administration</div>
            </div>
        </div>

        {{-- DASHBOARD --}}
        <div class="nav-direct">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span>📊</span> Dashboard
            </a>
        </div>

        {{-- Gestion Accès — admin seulement --}}
        @if(auth()->user()?->isAdmin())
        <div class="nav-direct" style="border-bottom:none;padding-top:0;">
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span>🔑</span> Gestion Accès
            </a>
        </div>
        @endif

        <div class="nav-section">

            {{-- GESTION FONCIÈRE --}}
            <button class="nav-group-btn" onclick="toggleGroup('group-foncier', this)">
                <span>🏢 Gestion foncière</span>
                <span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="group-foncier">
                <a href="{{ route('grand-sites.index') }}" onclick="fermerSidebar()"><span>🗂️</span> Sites & Zones</a>
                <a href="{{ route('lots.vendus') }}" onclick="fermerSidebar()"><span>📁</span> Suivi des parcelles</a>
            </div>

            {{-- CLIENTS & ÉQUIPES --}}
            <button class="nav-group-btn" onclick="toggleGroup('group-clients', this)">
                <span>👥 Clients & Équipes</span>
                <span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="group-clients">
                <a href="{{ route('suivi-client.index') }}" onclick="fermerSidebar()"><span>👤</span> Suivi clients</a>
                @if(auth()->user()?->isAdmin())
                    <a href="{{ route('commerciaux.index') }}" onclick="fermerSidebar()"><span>🧑‍💼</span> Commerciaux</a>
                    <a href="{{ route('agents.index') }}" onclick="fermerSidebar()"><span>🤝</span> Agents commerciaux</a>
                @endif
            </div>

            {{-- RAPPORTS & DONNÉES — admin seulement --}}
            @if(auth()->user()?->isAdmin())
            <button class="nav-group-btn" onclick="toggleGroup('group-rapports', this)">
                <span>📊 Rapports & Données</span>
                <span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="group-rapports">
                <a href="{{ route('rapport.index') }}" onclick="fermerSidebar()"><span>📈</span> Rapport d'activité</a>
                <a href="{{ route('rapport.liste') }}" onclick="fermerSidebar()"><span>🗂️</span> Rapports sauvegardés</a>
                <a href="{{ route('import-export.index') }}" onclick="fermerSidebar()"><span>🔄</span> Import / Export</a>
                <a href="{{ route('visites.index') }}" onclick="fermerSidebar()"><span>🚶</span> Registre des visites</a>
            </div>
            @endif

            {{-- VISITES — commercial --}}
            @if(!auth()->user()?->isAdmin())
            <div style="padding:4px 0;">
                <a href="{{ route('visites.index') }}" onclick="fermerSidebar()"><span>🚶</span> Registre des visites</a>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- ========== TOPBAR FIXE ========== --}}
<div class="topbar">
    <div style="display:flex;align-items:center;gap:8px;">
        {{-- Bouton hamburger mobile --}}
        <button id="menu-toggle" onclick="toggleSidebar()" class="d-md-none">☰</button>
        <div class="topbar-title">🌍 EDEN Administration</div>
    </div>
    <div class="topbar-right">
        {{-- Accueil --}}
        <a href="{{ route('home') }}" class="topbar-link">🏠 Accueil</a>

        {{-- Utilisateur + déconnexion --}}
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

{{-- ========== PWA — Bannière mise à jour ========== --}}
<div id="pwa-update-banner">
    🔄 Mise à jour disponible
    <button onclick="appliquerMAJ()"
            style="background:white;color:#1d4ed8;border:none;border-radius:8px;padding:5px 12px;font-weight:700;cursor:pointer;font-size:12px;">
        Mettre à jour
    </button>
    <button onclick="document.getElementById('pwa-update-banner').style.display='none'"
            style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;font-size:18px;padding:0;line-height:1;">
        ✕
    </button>
</div>

{{-- ========== PWA — Bouton installation ========== --}}
<div id="pwa-install-btn">
    <button onclick="installerPWA()">
        📲 Installer l'app
    </button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// ============================================================
// SPLASH SCREEN
// ============================================================
window.addEventListener('load', function() {
    const splash = document.getElementById('splash');
    if (!splash) return;
    setTimeout(() => {
        splash.style.opacity = '0';
        setTimeout(() => {
            splash.remove();
            sessionStorage.setItem('edenSplashDone', '1');
        }, 400);
    }, 1000);
});

// ============================================================
// SIDEBAR — navigation groupée
// ============================================================
function toggleGroup(id, btn) {
    const items  = document.getElementById(id);
    const isOpen = items.classList.contains('open');
    document.querySelectorAll('.nav-group-items').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.nav-group-btn').forEach(el   => el.classList.remove('open'));
    if (!isOpen) { items.classList.add('open'); btn.classList.add('open'); }
}

// Ouvrir automatiquement le groupe actif
document.addEventListener('DOMContentLoaded', function() {
    const url = window.location.pathname;
    const groupes = {
        'group-foncier' : ['/admin/grand-sites', '/admin/sites', '/admin/tf', '/admin/lots'],
        'group-clients' : ['/admin/suivi-client', '/admin/commerciaux', '/admin/agents'],
        'group-rapports': ['/admin/rapport', '/admin/import-export', '/admin/visites'],
    };
    Object.entries(groupes).forEach(([groupId, prefixes]) => {
        if (!document.getElementById(groupId)) return;
        if (prefixes.some(p => url.startsWith(p))) {
            const el  = document.getElementById(groupId);
            const btn = el?.previousElementSibling;
            if (el)  el.classList.add('open');
            if (btn) btn.classList.add('open');
        }
    });
    document.querySelectorAll('.sidebar a').forEach(a => {
        try {
            if (a.getAttribute('href') && url.startsWith(new URL(a.href).pathname))
                a.classList.add('active');
        } catch(e) {}
    });
});

// ============================================================
// SIDEBAR MOBILE — hamburger
// ============================================================
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebar-overlay').classList.toggle('open');
}
function fermerSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('open');
}

// ============================================================
// PWA — Service Worker
// ============================================================
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(reg => {

            // Détecter mise à jour
            reg.addEventListener('updatefound', () => {
                const newWorker = reg.installing;
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        document.getElementById('pwa-update-banner').style.display = 'flex';
                    }
                });
            });

        }).catch(err => console.warn('SW:', err));
    });
}

function appliquerMAJ() {
    navigator.serviceWorker.getRegistration().then(reg => {
        if (reg?.waiting) reg.waiting.postMessage('skipWaiting');
    });
    window.location.reload();
}

// ============================================================
// PWA — Bouton installation Android
// ============================================================
let deferredPrompt = null;

window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredPrompt = e;
    // N'afficher que si pas déjà installée
    if (!window.matchMedia('(display-mode: standalone)').matches) {
        document.getElementById('pwa-install-btn').style.display = 'block';
    }
});

function installerPWA() {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(result => {
        if (result.outcome === 'accepted') {
            document.getElementById('pwa-install-btn').style.display = 'none';
        }
        deferredPrompt = null;
    });
}

window.addEventListener('appinstalled', () => {
    document.getElementById('pwa-install-btn').style.display = 'none';
    deferredPrompt = null;
});
</script>

@yield('scripts')
</body>
</html>