<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Eden Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="apple-mobile-web-app-title" content="Eden Admin">
    <meta name="theme-color" content="#1d4ed8">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { box-sizing:border-box; }
        body { background:#f4f6f9; font-family:"Segoe UI",sans-serif; margin:0; }

        /* SPLASH */
        #splash {
            position:fixed; inset:0;
            background:linear-gradient(135deg,#1d4ed8 0%,#7c3aed 55%,#dc2626 100%);
            display:flex; flex-direction:column;
            justify-content:center; align-items:center;
            color:white; z-index:999999; transition:opacity 0.4s ease;
        }
        #splash .splash-logo {
            width:110px; height:110px; border-radius:24px;
            background:rgba(255,255,255,0.15);
            display:flex; align-items:center; justify-content:center;
            font-size:52px; margin-bottom:20px;
            box-shadow:0 8px 32px rgba(0,0,0,0.2);
        }
        #splash .splash-logo img { width:100%; height:100%; object-fit:contain; border-radius:20px; }
        #splash .splash-title  { font-size:26px; font-weight:900; letter-spacing:1px; margin-bottom:6px; }
        #splash .splash-sub    { font-size:13px; opacity:0.7; }
        #splash .splash-loader { width:48px; height:4px; background:rgba(255,255,255,0.25); border-radius:2px; margin-top:32px; overflow:hidden; }
        #splash .splash-loader-bar { height:100%; background:white; border-radius:2px; animation:splashLoad 0.9s ease forwards; }
        @keyframes splashLoad { from{width:0%} to{width:100%} }

        /* SIDEBAR */
        .sidebar {
            width:230px; height:100vh; position:fixed; top:0; left:0;
            background:#0f172a; display:flex; flex-direction:column;
            overflow-y:auto; overflow-x:hidden; z-index:200;
            scrollbar-width:thin; scrollbar-color:rgba(255,255,255,0.1) transparent;
        }
        .sidebar::-webkit-scrollbar { width:4px; }
        .sidebar::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:2px; }

        .logo-box {
            padding:20px 16px 14px;
            border-bottom:1px solid rgba(255,255,255,0.08);
            display:flex; flex-direction:column; align-items:center; gap:10px;
        }
        .logo-img-wrap { width:180px; height:150px; border-radius:14px; overflow:hidden; display:flex; align-items:center; justify-content:center; }
        .logo-img-wrap img { width:100%; height:100%; object-fit:contain; }
        .logo-placeholder-letter {
            width:80px; height:80px;
            background:linear-gradient(135deg,#1d4ed8 0%,#7c3aed 50%,#dc2626 100%);
            border-radius:14px; display:flex; align-items:center;
            justify-content:center; font-size:28px; font-weight:900; color:white;
        }
        .logo-name {
            font-size:14px; font-weight:800; letter-spacing:0.5px;
            background:linear-gradient(90deg,#60a5fa,#a78bfa,#f87171);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
        }
        .logo-sub { font-size:10px; color:#475569; margin-top:-4px; }

        .nav-section { padding:10px 0; }
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
        .nav-direct { padding:4px 0 8px; border-bottom:1px solid rgba(255,255,255,0.06); }

        /* Badge rôle dans sidebar */
        .role-tag {
            display:inline-block; font-size:9px; font-weight:700;
            padding:2px 7px; border-radius:10px; margin-left:auto;
        }
        .role-tag.admin    { background:rgba(220,38,38,0.2); color:#fca5a5; }
        .role-tag.rh       { background:rgba(124,58,237,0.2); color:#c4b5fd; }
        .role-tag.commercial { background:rgba(16,185,129,0.2); color:#6ee7b7; }
        @keyframes badgePulse {
    0%,100% { background:#dc2626; }
    50%      { background:#f87171; }
}

        /* TOPBAR */
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

        .content { margin-left:230px; padding:24px; padding-top:84px; }

        /* PWA */
        #pwa-update-banner {
            position:fixed; bottom:20px; left:50%; transform:translateX(-50%);
            background:#1d4ed8; color:white; border-radius:12px;
            padding:12px 20px; z-index:999999;
            align-items:center; gap:12px;
            box-shadow:0 8px 24px rgba(0,0,0,0.25);
            font-size:13px; font-weight:600; max-width:90vw; display:none;
        }
        #pwa-install-btn { position:fixed; bottom:20px; right:20px; z-index:99998; display:none; }
        #pwa-install-btn button {
            background:linear-gradient(135deg,#1d4ed8,#7c3aed); color:white;
            border:none; border-radius:14px; padding:12px 18px;
            font-size:13px; font-weight:700; cursor:pointer;
            box-shadow:0 8px 24px rgba(29,78,216,0.4);
            display:flex; align-items:center; gap:8px;
        }

        /* MOBILE */
        @media (max-width:768px) {
            .sidebar { transform:translateX(-100%); transition:transform 0.3s ease; z-index:300; }
            .sidebar.open { transform:translateX(0); }
            .topbar { left:0; padding:0 16px; }
            .content { margin-left:0; padding:16px; padding-top:76px; }
            #sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:299; }
            #sidebar-overlay.open { display:block; }
            #menu-toggle {
                background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25);
                color:white; border-radius:8px; padding:5px 10px; font-size:16px; cursor:pointer; margin-right:8px;
            }
            .topbar-user .role-badge { display:none; }
        }
    </style>
</head>

<script>
(function() {
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (isStandalone && !sessionStorage.getItem('edenSplashDone')) {
        document.write(`<div id="splash">
            <div class="splash-logo">
                <img src="/images/eden.webp" alt="Eden" onerror="this.style.display='none';this.parentNode.innerHTML='🏢'">
            </div>
            <div class="splash-title">EDEN GROUP</div>
            <div class="splash-sub">Administration · RH · Foncier</div>
            <div class="splash-loader"><div class="splash-loader-bar"></div></div>
        </div>`);
    }
})();
</script>

<body>
    {{-- ============ PAGE DE CHARGEMENT ============ --}}
<div id="eden-loader" style="
    display:none;
    position:fixed;
    inset:0;
    z-index:999999;
    background:rgba(15,23,42,0.82);
    align-items:center;
    justify-content:center;
    flex-direction:column;
    gap:20px;
">
    {{-- Carte centrale --}}
    <div style="
        background:white;
        border-radius:20px;
        padding:36px 44px;
        text-align:center;
        box-shadow:0 20px 60px rgba(0,0,0,0.35);
        min-width:260px;
        position:relative;
        overflow:hidden;
    ">
        {{-- Barre de couleur en haut --}}
        <div style="
            position:absolute;top:0;left:0;right:0;height:5px;
            background:linear-gradient(90deg,#1d4ed8 0%,#7c3aed 50%,#dc2626 100%);
        "></div>

        {{-- Logo / Initiale --}}
        <div style="
            width:64px;height:64px;
            background:linear-gradient(135deg,#1d4ed8,#7c3aed);
            border-radius:16px;
            display:flex;align-items:center;justify-content:center;
            margin:0 auto 16px auto;
            font-size:26px;font-weight:900;color:white;
            letter-spacing:-1px;
        ">E</div>

        {{-- Nom société --}}
        <div style="
            font-size:13px;font-weight:800;
            background:linear-gradient(90deg,#1d4ed8,#dc2626);
            -webkit-background-clip:text;-webkit-text-fill-color:transparent;
            background-clip:text;
            letter-spacing:1px;margin-bottom:20px;
        ">EDEN GROUP</div>

        {{-- Spinner SVG animé --}}
        <div style="margin:0 auto 18px auto;width:52px;height:52px;">
            <svg viewBox="0 0 52 52" style="width:52px;height:52px;animation:edenSpin 1s linear infinite;">
                <circle cx="26" cy="26" r="22"
                    fill="none"
                    stroke="#e2e8f0"
                    stroke-width="4"/>
                <circle cx="26" cy="26" r="22"
                    fill="none"
                    stroke="url(#edenGrad)"
                    stroke-width="4"
                    stroke-linecap="round"
                    stroke-dasharray="100 38"
                    stroke-dashoffset="0"/>
                <defs>
                    <linearGradient id="edenGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%"   stop-color="#1d4ed8"/>
                        <stop offset="50%"  stop-color="#7c3aed"/>
                        <stop offset="100%" stop-color="#dc2626"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>

        {{-- Texte chargement animé --}}
        <div id="eden-loader-msg" style="
            font-size:13px;font-weight:600;color:#475569;
        ">Chargement en cours<span id="eden-dots"></span></div>

        {{-- Barre de progression --}}
        <div style="
            height:3px;background:#f1f5f9;border-radius:2px;
            margin-top:16px;overflow:hidden;
        ">
            <div id="eden-prog-bar" style="
                height:100%;width:0%;border-radius:2px;
                background:linear-gradient(90deg,#1d4ed8,#7c3aed,#dc2626);
                transition:width 0.3s ease;
            "></div>
        </div>
    </div>
</div>

<style>
@keyframes edenSpin {
    0%   { transform: rotate(0deg);   }
    100% { transform: rotate(360deg); }
}
#eden-loader.actif {
    display: flex !important;
}
</style>

<script>
(function() {
    const loader  = document.getElementById('eden-loader');
    const dots    = document.getElementById('eden-dots');
    const progBar = document.getElementById('eden-prog-bar');

    // ============================================================
    // ANIMATION DES POINTS
    // ============================================================
    let dotsCount = 0;
    let dotsTimer = null;
    function animerPoints() {
        dotsTimer = setInterval(() => {
            dotsCount = (dotsCount + 1) % 4;
            dots.innerText = '.'.repeat(dotsCount);
        }, 400);
    }

    // ============================================================
    // BARRE DE PROGRESSION SIMULÉE
    // ============================================================
    let progValue  = 0;
    let progTimer  = null;
    function demarrerProgression() {
        progValue = 0;
        progBar.style.width = '0%';
        progTimer = setInterval(() => {
            // Avancer rapidement jusqu'à 85%, puis ralentir
            if (progValue < 30)       progValue += 4;
            else if (progValue < 60)  progValue += 2.5;
            else if (progValue < 80)  progValue += 1;
            else if (progValue < 88)  progValue += 0.3;
            // Bloquer à 88% jusqu'à vraie fin
            if (progValue >= 88) {
                progValue = 88;
                clearInterval(progTimer);
            }
            progBar.style.width = progValue + '%';
        }, 100);
    }
    function terminerProgression(callback) {
        clearInterval(progTimer);
        progValue = 100;
        progBar.style.width = '100%';
        setTimeout(() => {
            if (callback) callback();
        }, 300);
    }

    // ============================================================
    // AFFICHER / MASQUER
    // ============================================================
    function afficher() {
        loader.classList.add('actif');
        demarrerProgression();
        animerPoints();
    }

    function masquer() {
        terminerProgression(() => {
            loader.classList.remove('actif');
            clearInterval(dotsTimer);
            dotsCount = 0;
            if (dots) dots.innerText = '';
        });
    }

    // ============================================================
    // DÉCLENCHEURS
    // ============================================================

    // 1. Tous les liens qui causent une navigation (sauf ancres, modals, js)
    document.addEventListener('click', function(e) {
        const a = e.target.closest('a');
        if (!a) return;
        const href = a.getAttribute('href');
        if (!href) return;
        // Ignorer : ancres, javascript:, target _blank, téléchargements
        if (href.startsWith('#'))         return;
        if (href.startsWith('javascript'))return;
        if (a.target === '_blank')        return;
        if (a.download)                   return;
        // Ignorer : boutons qui ouvrent des modals (onclick sans navigation)
        if (a.dataset.bsToggle)           return;
        afficher();
    });

    // 2. Tous les formulaires soumis (sauf fetch/ajax)
    document.addEventListener('submit', function(e) {
        const form = e.target;
        // Ignorer les formulaires sans action réelle
        if (form.dataset.ajax === 'true') return;
        afficher();
    });

    // 3. Boutons submit classiques (dans forms)
    document.addEventListener('click', function(e) {
        const btn = e.target.closest('button[type="submit"]');
        if (!btn) return;
        const form = btn.closest('form');
        if (form && form.dataset.ajax !== 'true') {
            afficher();
        }
    });

    // 4. Masquer dès que la page est chargée (retour arrière, etc.)
    window.addEventListener('pageshow', function(e) {
        masquer();
    });

    // 5. Masquer si la page est déjà chargée
    if (document.readyState === 'complete') {
        masquer();
    } else {
        window.addEventListener('load', masquer);
    }

    // ============================================================
    // API GLOBALE — pour appeler manuellement depuis n'importe où
    // ============================================================
    window.EdenLoader = {
        show: afficher,
        hide: masquer,
    };
})();
</script>

<div id="sidebar-overlay" onclick="fermerSidebar()"></div>

{{-- ============================================================
     SIDEBAR — visibilité stricte par rôle
     ============================================================ --}}
<div class="sidebar" id="sidebar">
    <div>

        {{-- LOGO --}}
        <div class="logo-box">
            @if(file_exists(public_path('images/eden.png')))
                <div class="logo-img-wrap">
                    <img src="{{ asset('images/eden.png') }}" alt="Logo EDEN GROUP"
                         onerror="this.parentNode.innerHTML='<div class=\'logo-placeholder-letter\'>E</div>'">
                </div>
            @elseif(file_exists(public_path('images/eden_group.png')))
                <div class="logo-img-wrap">
                    <img src="{{ asset('images/eden_group.png') }}" alt="Logo EDEN GROUP">
                </div>
            @else
                <div class="logo-placeholder-letter">E</div>
            @endif
            <div>
                <div class="logo-name">EDEN GROUP</div>
                <div class="logo-sub">
                    @php $role = auth()->user()?->role; @endphp
                    @if($role === 'admin') Administration
                    @elseif($role === 'rh') Ressources Humaines
                    @else Commercial
                    @endif
                </div>
            </div>
        </div>

        {{-- ✅ DASHBOARD — visible pour tous --}}
        <div class="nav-direct">
            <a href="{{ route('admin.dashboard') }}" class="{{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <span>📊</span> Dashboard
                <span class="role-tag {{ $role }}">
                    {{ ['admin'=>'Admin','rh'=>'RH','commercial'=>'Commercial'][$role] ?? '' }}
                </span>
            </a>
        </div>

        {{-- ✅ GESTION ACCÈS — admin uniquement --}}
        @if($role === 'admin')
        <div class="nav-direct" style="border-bottom:none;padding-top:0;">
            <a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <span>🔑</span> Gestion Accès
            </a>
        </div>
        @endif

        <div class="nav-section">

            {{-- ✅ GESTION FONCIÈRE — admin + commercial (lecture) --}}
            @if(in_array($role, ['admin', 'commercial']))
            <button class="nav-group-btn" onclick="toggleGroup('group-foncier', this)">
                <span>🏢 Gestion foncière</span>
                <span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="group-foncier">
                {{-- Sites & Zones : admin peut modifier, commercial voit seulement --}}
                <a href="{{ route('grand-sites.index') }}" onclick="fermerSidebar()">
                    <span>🗂️</span> Sites & Zones
                    @if($role !== 'admin')
                        <span style="font-size:9px;color:#475569;margin-left:auto;">lecture</span>
                    @endif
                </a>
                {{-- Suivi parcelles : admin + commercial --}}
                <a href="{{ route('lots.vendus') }}" onclick="fermerSidebar()">
                    <span>📁</span> Suivi des parcelles
                </a>
            </div>
            @endif

            {{-- ✅ CLIENTS & ÉQUIPES — admin + commercial --}}
            @if(in_array($role, ['admin', 'commercial']))
            <button class="nav-group-btn" onclick="toggleGroup('group-clients', this)">
                <span>👥 Clients & Équipes</span>
                <span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="group-clients">
                <a href="{{ route('suivi-client.index') }}" onclick="fermerSidebar()">
                    <span>👤</span> Suivi clients
                </a>
                {{-- Commerciaux & Agents — admin seulement --}}
                @if($role === 'admin')
                    <a href="{{ route('commerciaux.index') }}" onclick="fermerSidebar()">
                        <span>🧑‍💼</span> Commerciaux
                    </a>
                    <a href="{{ route('agents.index') }}" onclick="fermerSidebar()">
                        <span>🤝</span> Agents commerciaux
                    </a>
                @endif
            </div>
            @endif

          {{-- MODULE FEB — admin seulement --}}
@if($role === 'admin')
@php
    $nbFebNew = \App\Models\Feb\Fiche::where('vue_admin', false)
                    ->where('statut', 'soumise')
                    ->count();
@endphp
<div style="padding:4px 0;border-top:1px solid rgba(255,255,255,0.06);margin-top:4px;">
    <a href="{{ route('admin.feb.index') }}" onclick="fermerSidebar()"
       class="{{ request()->is('admin/feb*') ? 'active' : '' }}"
       style="position:relative;">
        <span>📋</span> Fiches d'Expression
        @if($nbFebNew > 0)
            <span style="
                background:#dc2626; color:white; border-radius:10px;
                font-size:9px; padding:2px 7px; font-weight:800;
                margin-left:auto; flex-shrink:0;
                animation:badgePulse 1.5s infinite;
            ">{{ $nbFebNew }}</span>
        @endif
    </a>
</div>
@endif

            {{-- ✅ RAPPORTS & DONNÉES — admin uniquement --}}
            @if($role === 'admin')
            <button class="nav-group-btn" onclick="toggleGroup('group-rapports', this)">
                <span>📊 Rapports & Données</span>
                <span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="group-rapports">
                <a href="{{ route('rapport.index') }}" onclick="fermerSidebar()">
                    <span>📈</span> Rapport d'activité
                </a>
                <a href="{{ route('rapport.liste') }}" onclick="fermerSidebar()">
                    <span>🗂️</span> Rapports sauvegardés
                </a>
                <a href="{{ route('import-export.index') }}" onclick="fermerSidebar()">
                    <span>🔄</span> Import / Export
                </a>
                <a href="{{ route('visites.index') }}" onclick="fermerSidebar()">
                    <span>🚶</span> Registre des visites
                </a>
            </div>
            @endif

            {{-- ✅ VISITES — commercial (sans les rapports) --}}
            @if($role === 'commercial')
            <div style="padding:4px 0;border-top:1px solid rgba(255,255,255,0.06);">
                <a href="{{ route('visites.index') }}" onclick="fermerSidebar()">
                    <span>🚶</span> Registre des visites
                </a>
            </div>
            @endif

            {{-- ✅ MODULE RH — admin peut y accéder aussi --}}
            @if($role === 'admin')
            <div style="padding:4px 0;border-top:1px solid rgba(255,255,255,0.06);">
                <a href="{{ route('rh.dashboard') }}" onclick="fermerSidebar()">
                    <span>🏢</span> Module RH
                </a>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- ============================================================
     TOPBAR
     ============================================================ --}}
<div class="topbar">
    <div style="display:flex;align-items:center;gap:8px;">
        <button id="menu-toggle" onclick="toggleSidebar()" class="d-md-none">☰</button>
        <div class="topbar-title">🌍 EDEN Administration</div>
    </div>
    <div class="topbar-right">

        {{-- Accueil modules --}}
        <a href="{{ route('home') }}" class="topbar-link">🏠 Accueil</a>

        {{-- Raccourci RH — admin uniquement --}}
        @if($role === 'admin')
        <a href="{{ route('rh.dashboard') }}" class="topbar-link">🏢 RH</a>
        @endif

        {{-- Utilisateur --}}
        <div class="topbar-user">
            <span>👤</span>
            <span style="font-size:12px;">{{ auth()->user()?->name }}</span>
            <span class="role-badge" style="font-size:10px;background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:10px;">
                @if($role === 'admin') 🔴 Admin
                @elseif($role === 'rh') 🟣 RH
                @else 🟢 Commercial
                @endif
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

{{-- CONTENU --}}
<div class="content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @yield('content')
</div>

{{-- PWA --}}
<div id="pwa-update-banner">
    🔄 Mise à jour disponible
    <button onclick="appliquerMAJ()"
            style="background:white;color:#1d4ed8;border:none;border-radius:8px;padding:5px 12px;font-weight:700;cursor:pointer;font-size:12px;">
        Mettre à jour
    </button>
    <button onclick="document.getElementById('pwa-update-banner').style.display='none'"
            style="background:none;border:none;color:rgba(255,255,255,0.7);cursor:pointer;font-size:18px;padding:0;line-height:1;">✕</button>
</div>

<div id="pwa-install-btn">
    <button onclick="installerPWA()">📲 Installer l'app</button>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// SPLASH
window.addEventListener('load', function() {
    const splash = document.getElementById('splash');
    if (!splash) return;
    setTimeout(() => {
        splash.style.opacity = '0';
        setTimeout(() => { splash.remove(); sessionStorage.setItem('edenSplashDone','1'); }, 400);
    }, 1000);
});

// SIDEBAR NAVIGATION
function toggleGroup(id, btn) {
    const items  = document.getElementById(id);
    const isOpen = items.classList.contains('open');
    document.querySelectorAll('.nav-group-items').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.nav-group-btn').forEach(el   => el.classList.remove('open'));
    if (!isOpen) { items.classList.add('open'); btn.classList.add('open'); }
}

document.addEventListener('DOMContentLoaded', function() {
    const url = window.location.pathname;
    const groupes = {
        'group-foncier' : ['/admin/grand-sites','/admin/sites','/admin/tf','/admin/lots'],
        'group-clients' : ['/admin/suivi-client','/admin/commerciaux','/admin/agents'],
        'group-rapports': ['/admin/rapport','/admin/import-export','/admin/visites'],
    };
    Object.entries(groupes).forEach(([groupId, prefixes]) => {
        const el = document.getElementById(groupId); if (!el) return;
        if (prefixes.some(p => url.startsWith(p))) {
            el.classList.add('open');
            const btn = el.previousElementSibling;
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

// MOBILE SIDEBAR
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('sidebar-overlay').classList.toggle('open');
}
function fermerSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebar-overlay').classList.remove('open');
}

// PWA SERVICE WORKER
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').then(reg => {
            reg.addEventListener('updatefound', () => {
                const nw = reg.installing;
                nw.addEventListener('statechange', () => {
                    if (nw.state === 'installed' && navigator.serviceWorker.controller)
                        document.getElementById('pwa-update-banner').style.display = 'flex';
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

// PWA INSTALLATION
let deferredPrompt = null;
window.addEventListener('beforeinstallprompt', e => {
    e.preventDefault();
    deferredPrompt = e;
    if (!window.matchMedia('(display-mode: standalone)').matches)
        document.getElementById('pwa-install-btn').style.display = 'block';
});
function installerPWA() {
    if (!deferredPrompt) return;
    deferredPrompt.prompt();
    deferredPrompt.userChoice.then(r => {
        if (r.outcome === 'accepted')
            document.getElementById('pwa-install-btn').style.display = 'none';
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