<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>RH — Eden Group</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Eden RH">
    <meta name="theme-color" content="#7c3aed">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/icons/icon-192.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        /* ===== STYLES GLOBAUX ===== */
        * { box-sizing:border-box; }
        body { background:#f1f5f9; font-family:"Segoe UI", system-ui, -apple-system, sans-serif; margin:0; }

        /* ===== SPLASH SCREEN ===== */
        #splash {
            position:fixed; inset:0;
            background:linear-gradient(135deg,#7c3aed 0%,#1d4ed8 55%,#dc2626 100%);
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
        #splash .splash-title  { font-size:24px; font-weight:900; margin-bottom:5px; }
        #splash .splash-sub    { font-size:12px; opacity:0.7; }
        #splash .splash-loader { width:44px; height:4px; background:rgba(255,255,255,0.2); border-radius:2px; margin-top:28px; overflow:hidden; }
        #splash .splash-loader-bar { height:100%; background:white; border-radius:2px; animation:splashLoad 0.9s ease forwards; }
        @keyframes splashLoad { from{width:0%} to{width:100%} }

        /* ===== SIDEBAR ===== */
        .sidebar {
            width:250px; height:100vh; position:fixed; top:0; left:0;
            background:#0f172a; display:flex; flex-direction:column;
            overflow-y:auto; z-index:200; transition:transform 0.3s ease;
            scrollbar-width:thin; scrollbar-color:rgba(255,255,255,0.1) transparent;
        }
        .sidebar::-webkit-scrollbar { width:4px; }
        .sidebar::-webkit-scrollbar-thumb { background:rgba(255,255,255,0.1); border-radius:2px; }

        .logo-box {
            padding:20px 16px 16px; border-bottom:1px solid rgba(255,255,255,0.06);
            display:flex; flex-direction:column; align-items:center; gap:8px;
        }
        .logo-circle {
            width:64px; height:64px;
            background:linear-gradient(135deg,#7c3aed,#1d4ed8,#dc2626);
            border-radius:14px; display:flex; align-items:center;
            justify-content:center; font-size:24px; font-weight:900; color:white;
            box-shadow:0 4px 12px rgba(124,58,237,0.4);
        }
        .logo-name { font-size:15px; font-weight:800; color:white; letter-spacing:0.5px; }
        .logo-sub  { font-size:10px; color:#64748b; }

        .nav-group-btn {
            display:flex; align-items:center; justify-content:space-between;
            width:100%; padding:10px 20px; background:none; border:none;
            color:#94a3b8; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:0.5px; cursor:pointer; transition:0.15s;
        }
        .nav-group-btn:hover { color:#e2e8f0; background:rgba(255,255,255,0.04); }
        .nav-group-btn .chevron { font-size:10px; transition:transform 0.3s; }
        .nav-group-btn.open .chevron { transform:rotate(90deg); }
        .nav-group-items { display:none; padding-bottom:4px; }
        .nav-group-items.open { display:block; }

        .sidebar a {
            display:flex; align-items:center; gap:10px;
            color:#cbd5e1; padding:8px 20px 8px 40px; text-decoration:none;
            font-size:13px; transition:0.15s; border-left:3px solid transparent;
            position:relative;
        }
        .sidebar a:hover { background:rgba(255,255,255,0.06); color:white; border-left-color:#7c3aed; }
        .sidebar a.active { background:rgba(255,255,255,0.08); color:white; border-left-color:#60a5fa; }
        .sidebar a .badge-sidebar {
            position:absolute; right:16px;
            background:#dc2626; color:white; font-size:9px;
            padding:1px 8px; border-radius:10px; font-weight:600;
        }

        /* ===== TOPBAR ===== */
        .topbar {
            position:fixed; top:0; left:250px; right:0; height:64px; z-index:100;
            background:linear-gradient(135deg,#7c3aed 0%,#1d4ed8 55%,#dc2626 100%);
            color:white; display:flex; align-items:center;
            justify-content:space-between; padding:0 28px;
            box-shadow:0 2px 16px rgba(124,58,237,0.35);
        }
        .topbar .page-title { font-weight:700; font-size:15px; display:flex; align-items:center; gap:8px; }
        .topbar-right { display:flex; align-items:center; gap:12px; }
        .topbar-user {
            display:flex; align-items:center; gap:10px; font-size:13px;
            background:rgba(255,255,255,0.15); padding:6px 16px; border-radius:24px;
            backdrop-filter:blur(4px);
        }
        .topbar-user .avatar {
            width:32px; height:32px; border-radius:50%;
            background:rgba(255,255,255,0.2); display:flex;
            align-items:center; justify-content:center; font-weight:700; font-size:14px;
        }
        .topbar-link {
            background:rgba(255,255,255,0.12); color:white;
            border:1px solid rgba(255,255,255,0.2); border-radius:8px;
            padding:6px 14px; font-size:12px; font-weight:600;
            text-decoration:none; transition:0.2s;
        }
        .topbar-link:hover { background:rgba(255,255,255,0.25); color:white; }

        /* ===== CONTENT ===== */
        .content { margin-left:250px; padding:24px; padding-top:84px; }
        .content .card { border-radius:14px; border:none; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
        .content .card-header { border-radius:14px 14px 0 0; background:white; border-bottom:1px solid #f1f5f9; }

        /* ===== STATS CARDS ===== */
        .stat-card {
            background:white; border-radius:14px; padding:20px;
            box-shadow:0 2px 12px rgba(0,0,0,0.06);
            transition:transform 0.2s, box-shadow 0.2s;
            position:relative; overflow:hidden;
        }
        .stat-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.1); }
        .stat-card .icon { font-size:28px; opacity:0.12; position:absolute; right:16px; top:16px; }
        .stat-card .value { font-size:28px; font-weight:800; }
        .stat-card .label { font-size:11px; color:#64748b; font-weight:600; text-transform:uppercase; margin-top:2px; }
        .stat-card .trend { font-size:11px; font-weight:600; padding:2px 10px; border-radius:10px; display:inline-block; margin-top:4px; }
        .trend-up { background:#dcfce7; color:#15803d; }
        .trend-down { background:#fee2e2; color:#b91c1c; }

        /* ===== TABLE ===== */
        .table-modern {
            font-size:12px; margin-bottom:0;
        }
        .table-modern thead th {
            background:#f8fafc; color:#1e293b; font-weight:700;
            padding:12px 12px; border-bottom:2px solid #e2e8f0;
            text-transform:uppercase; font-size:10px; letter-spacing:0.3px;
        }
        .table-modern tbody td {
            padding:10px 12px; border-bottom:1px solid #f1f5f9;
            vertical-align:middle;
        }
        .table-modern tbody tr:hover { background:#f8fafc; }

        /* ===== BADGES ===== */
        .badge-status {
            padding:3px 12px; border-radius:10px; font-size:10px; font-weight:600; display:inline-block;
        }
        .badge-status.approuve { background:#dcfce7; color:#15803d; }
        .badge-status.refuse { background:#fee2e2; color:#b91c1c; }
        .badge-status.en_attente { background:#fef3c7; color:#92400e; }
        .badge-status.actif { background:#dcfce7; color:#15803d; }
        .badge-status.inactif { background:#fee2e2; color:#b91c1c; }
        .badge-status.paye { background:#dcfce7; color:#15803d; }
        .badge-status.brouillon { background:#f1f5f9; color:#475569; }
        .badge-status.valide { background:#dbeafe; color:#1d4ed8; }

        /* ===== FILTRES ===== */
        .filter-bar {
            background:white; border-radius:14px; padding:16px 20px;
            box-shadow:0 2px 12px rgba(0,0,0,0.06);
            margin-bottom:20px;
        }

        /* ===== MODALS ===== */
        .modal-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.45); z-index:9998; }
        .modal-box { display:none; position:fixed; top:50%; left:50%; transform:translate(-50%,-50%); background:white; padding:28px; border-radius:16px; box-shadow:0 20px 60px rgba(0,0,0,0.3); z-index:9999; width:520px; max-height:90vh; overflow-y:auto; }

        /* ===== MOBILE ===== */
        @media (max-width:768px) {
            .sidebar { transform:translateX(-100%); }
            .sidebar.open { transform:translateX(0); }
            .topbar { left:0; padding:0 16px; height:60px; }
            .topbar .page-title { font-size:13px; }
            .content { margin-left:0; padding:16px; padding-top:76px; }
            .topbar-user .user-name { display:none; }
            #sidebar-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:299; }
            #sidebar-overlay.open { display:block; }
            #menu-toggle {
                background:rgba(255,255,255,0.15); border:1px solid rgba(255,255,255,0.25);
                color:white; border-radius:8px; padding:5px 12px; font-size:18px; cursor:pointer;
            }
        }
    </style>
</head>

<script>
(function() {
    const isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (isStandalone && !sessionStorage.getItem('edenSplashDone')) {
        document.write(`<div id="splash">
            <div class="splash-logo">🏢</div>
            <div class="splash-title">EDEN RH</div>
            <div class="splash-sub">Ressources Humaines</div>
            <div class="splash-loader"><div class="splash-loader-bar"></div></div>
        </div>`);
    }
})();
</script>

<body>

@php $role = auth()->user()?->role; @endphp
<div id="sidebar-overlay" onclick="fermerSidebar()"></div>

{{-- ============================================================
     SIDEBAR
     ============================================================ --}}
<div class="sidebar" id="sidebar">
    <div class="logo-box">
        <div class="logo-circle">RH</div>
        <div class="logo-name">EDEN GROUP</div>
        <div class="logo-sub">
            @if($role === 'admin') Administration @else Ressources Humaines @endif
        </div>
    </div>

    {{-- Dashboard --}}
    <div style="padding:6px 0;border-bottom:1px solid rgba(255,255,255,0.06);">
        <a href="{{ route('rh.dashboard') }}" onclick="fermerSidebar()"
           class="{{ request()->routeIs('rh.dashboard') ? 'active' : '' }}">
            <span>📊</span> Dashboard RH
        </a>
    </div>

    {{-- ALERTES --}}
<a href="{{ route('rh.alertes.index') }}" onclick="fermerSidebar()"
   class="{{ request()->routeIs('rh.alertes.*') ? 'active' : '' }}">
    <span>🔔</span> Notifications
    @php
        $nonLu = \App\Models\RH\Alerte::nonLu()->count();
    @endphp
    @if($nonLu > 0)
        <span class="badge-sidebar">{{ $nonLu }}</span>
    @endif
</a>

    <div style="padding:6px 0;">

        {{-- RECRUTEMENT --}}
<button class="nav-group-btn" onclick="toggleGroup('g-recrutement', this)">
    <span>🎯 Recrutement</span><span class="chevron">›</span>
</button>
<div class="nav-group-items" id="g-recrutement">
    <a href="{{ route('rh.recrutement.index') }}" onclick="fermerSidebar()">
        <span>📋</span> Candidats
    </a>
    <a href="{{ route('rh.recrutement.create') }}" onclick="fermerSidebar()">
        <span>➕</span> Nouveau candidat
    </a>
    <a href="{{ route('rh.recrutement.index', ['statut' => 'recu']) }}" onclick="fermerSidebar()">
        <span>📥</span> Nouvelles candidatures
    </a>
    <a href="{{ route('rh.recrutement.index', ['statut' => 'embauche']) }}" onclick="fermerSidebar()">
        <span>✅</span> Embauchés
    </a>
</div>

        {{-- PARAMÈTRES --}}
        @if(in_array($role, ['admin','rh']))
        <button class="nav-group-btn" onclick="toggleGroup('g-param', this)">
            <span>⚙️ Paramètres</span><span class="chevron">›</span>
        </button>
        <div class="nav-group-items" id="g-param">
            <a href="{{ route('rh.directions.index') }}" onclick="fermerSidebar()">🏢 Directions & Services</a>
        </div>
        @endif

        {{-- EMPLOYÉS --}}
        @if(in_array($role, ['admin','rh']))
        <button class="nav-group-btn" onclick="toggleGroup('g-emp', this)">
            <span>👥 Employés</span><span class="chevron">›</span>
        </button>
        <div class="nav-group-items" id="g-emp">
            <a href="{{ route('rh.employes.index') }}" onclick="fermerSidebar()">📋 Liste</a>
            <a href="{{ route('rh.employes.create') }}" onclick="fermerSidebar()">➕ Nouveau</a>
            <a href="{{ route('rh.employes.index', ['statut' => 'inactif']) }}" onclick="fermerSidebar()">📦 Archivés</a>
        </div>
        @endif

        {{-- CONTRATS --}}
        @if(in_array($role, ['admin','rh']))
        <button class="nav-group-btn" onclick="toggleGroup('g-contrat', this)">
            <span>📄 Contrats</span><span class="chevron">›</span>
        </button>
        <div class="nav-group-items" id="g-contrat">
            <a href="{{ route('rh.contrats.index') }}" onclick="fermerSidebar()">📋 Liste</a>
            <a href="{{ route('rh.contrats.create') }}" onclick="fermerSidebar()">➕ Nouveau contrat</a>
            <a href="{{ route('rh.contrats.index', ['statut' => 'actif']) }}" onclick="fermerSidebar()">✅ Contrats actifs</a>
        </div>
        @endif

        {{-- PAIE --}}
        @if(in_array($role, ['admin','rh']))
        <button class="nav-group-btn" onclick="toggleGroup('g-paie', this)">
            <span>💰 Paie</span><span class="chevron">›</span>
        </button>
        <div class="nav-group-items" id="g-paie">
            <a href="{{ route('rh.paie.index') }}" onclick="fermerSidebar()">📄 Bulletins</a>
            <a href="{{ route('rh.paie.create') }}" onclick="fermerSidebar()">➕ Nouveau bulletin</a>
            <a href="{{ route('rh.paie.recapitulatif') }}" onclick="fermerSidebar()">📊 Récapitulatif</a>
        </div>
        @endif

        {{-- CNPS --}}
        @if(in_array($role, ['admin','rh']))
        <button class="nav-group-btn" onclick="toggleGroup('g-cnps', this)">
            <span>🏛️ CNPS</span><span class="chevron">›</span>
        </button>
        <div class="nav-group-items" id="g-cnps">
            <a href="{{ route('rh.cnps.index') }}" onclick="fermerSidebar()">📊 Dashboard CNPS</a>
            <a href="{{ route('rh.cnps.declarations') }}" onclick="fermerSidebar()">📋 Déclarations</a>
            <a href="{{ route('rh.cnps.declarations.create') }}" onclick="fermerSidebar()">➕ Nouvelle déclaration</a>
            <a href="{{ route('rh.cnps.affiliations') }}" onclick="fermerSidebar()">📇 Affiliations</a>
            <a href="{{ route('rh.cnps.declarations', ['statut' => 'a_declarer']) }}" onclick="fermerSidebar()">⏳ À déclarer</a>
        </div>
        @endif

        {{-- SANTÉ & SÉCURITÉ --}}
<button class="nav-group-btn" onclick="toggleGroup('g-sante', this)">
    <span>🏥 Santé & Sécurité</span><span class="chevron">›</span>
</button>
<div class="nav-group-items" id="g-sante">
    <a href="{{ route('rh.sante.index') }}" onclick="fermerSidebar()">
        <span>📊</span> Dashboard Santé
    </a>
    <a href="{{ route('rh.sante.visites') }}" onclick="fermerSidebar()">
        <span>🩺</span> Visites médicales
    </a>
    <a href="{{ route('rh.sante.visites.create') }}" onclick="fermerSidebar()">
        <span>➕</span> Nouvelle visite
    </a>
    <a href="{{ route('rh.sante.accidents') }}" onclick="fermerSidebar()">
        <span>⚠️</span> Accidents
    </a>
    <a href="{{ route('rh.sante.trousses') }}" onclick="fermerSidebar()">
        <span>🧰</span> Trousse de secours
    </a>
</div>

        {{-- ABSENCES --}}
        @if(in_array($role, ['admin','rh']))
        <button class="nav-group-btn" onclick="toggleGroup('g-abs', this)">
            <span>🗓️ Absences</span><span class="chevron">›</span>
        </button>
        <div class="nav-group-items" id="g-abs">
            <a href="{{ route('rh.absences.index') }}" onclick="fermerSidebar()">📋 Suivi absences</a>
        </div>
        @endif

        {{-- CONGÉS --}}
        @if(in_array($role, ['admin','rh']))
        <button class="nav-group-btn" onclick="toggleGroup('g-conge', this)">
            <span>🏖️ Congés</span><span class="chevron">›</span>
        </button>
        <div class="nav-group-items" id="g-conge">
            <a href="{{ route('rh.conges.index') }}" onclick="fermerSidebar()">📋 Planning congés</a>
        </div>
        @endif

        {{-- RETARDS --}}
        @if(in_array($role, ['admin','rh']))
        <button class="nav-group-btn" onclick="toggleGroup('g-ret', this)">
            <span>⏱️ Retards</span><span class="chevron">›</span>
        </button>
        <div class="nav-group-items" id="g-ret">
            <a href="{{ route('rh.retards.index') }}" onclick="fermerSidebar()">📋 Liste</a>
        </div>
        @endif

        {{-- PRÊTS --}}
        @if(in_array($role, ['admin','rh']))
        <button class="nav-group-btn" onclick="toggleGroup('g-pret', this)">
            <span>🏦 Prêts</span><span class="chevron">›</span>
        </button>
        <div class="nav-group-items" id="g-pret">
            <a href="{{ route('rh.prets.index') }}" onclick="fermerSidebar()">📋 Liste</a>
        </div>
        @endif

        {{-- SANCTIONS --}}
        @if(in_array($role, ['admin','rh']))
        <button class="nav-group-btn" onclick="toggleGroup('g-sanc', this)">
            <span>⚠️ Sanctions</span><span class="chevron">›</span>
        </button>
        <div class="nav-group-items" id="g-sanc">
            <a href="{{ route('rh.sanctions.index') }}" onclick="fermerSidebar()">📋 Liste</a>
        </div>
        @endif

        {{-- DÉPARTS --}}
        @if(in_array($role, ['admin','rh']))
        <button class="nav-group-btn" onclick="toggleGroup('g-depart', this)">
            <span>🚪 Départs</span><span class="chevron">›</span>
        </button>
        <div class="nav-group-items" id="g-depart">
            <a href="{{ route('rh.departs.index') }}" onclick="fermerSidebar()">📋 Liste</a>
            <a href="{{ route('rh.departs.create') }}" onclick="fermerSidebar()">➕ Nouveau départ</a>
            <a href="{{ route('rh.departs.index', ['statut' => 'en_attente']) }}" onclick="fermerSidebar()">⏳ En attente</a>
        </div>
        @endif

        {{-- RETOUR MODULE ADMIN --}}
        @if($role === 'admin')
        <div style="padding:8px 0;border-top:1px solid rgba(255,255,255,0.06);margin-top:8px;">
            <a href="{{ route('admin.dashboard') }}" onclick="fermerSidebar()">
                <span>⚙️</span> Module Admin
            </a>
        </div>
        @endif

    </div>
</div>

{{-- ============================================================
     TOPBAR
     ============================================================ --}}
<div class="topbar">
    <div style="display:flex;align-items:center;gap:12px;">
        <button id="menu-toggle" onclick="toggleSidebar()" class="d-md-none">☰</button>
        <span class="page-title">
            <span>🏢</span>
            <span>EDEN GROUP — RH</span>
        </span>
    </div>
    <div class="topbar-right">
        <a href="{{ route('home') }}" class="topbar-link">🏠 Accueil</a>
        {{-- Après le bouton Accueil --}}
<a href="{{ route('rh.alertes.index') }}" class="topbar-link" style="position:relative;">
    🔔
    @php
        $nonLu = \App\Models\RH\Alerte::nonLu()->count();
    @endphp
    @if($nonLu > 0)
        <span style="position:absolute;top:-6px;right:-6px;background:#dc2626;color:white;font-size:9px;padding:0 6px;border-radius:50%;min-width:18px;text-align:center;font-weight:700;">
            {{ $nonLu > 99 ? '99+' : $nonLu }}
        </span>
    @endif
</a>
        @if($role === 'admin')
            <a href="{{ route('admin.dashboard') }}" class="topbar-link">⚙️ Admin</a>
        @endif
        <div class="topbar-user">
            <div class="avatar">{{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 2)) }}</div>
            <span class="user-name" style="font-size:12px;">{{ auth()->user()?->name }}</span>
            <span style="font-size:9px;background:rgba(255,255,255,0.2);padding:2px 10px;border-radius:10px;">
                @if($role === 'admin') 🔴 Admin
                @elseif($role === 'rh') 🟣 RH
                @else 🟢 Commercial
                @endif
            </span>
            <form method="POST" action="{{ route('logout') }}" style="display:inline;">
                @csrf
                <button type="submit" style="background:rgba(255,255,255,0.1);color:white;border:1px solid rgba(255,255,255,0.2);border-radius:6px;padding:4px 12px;font-size:11px;cursor:pointer;transition:0.2s;">
                    Déconnexion
                </button>
            </form>
        </div>
    </div>
</div>

{{-- ============================================================
     CONTENU
     ============================================================ --}}
<div class="content">
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" style="border-radius:12px;">
            <i class="bi bi-check-circle-fill me-2" style="font-size:18px;"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" style="border-radius:12px;">
            <i class="bi bi-exclamation-triangle-fill me-2" style="font-size:18px;"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @yield('content')
</div>

{{-- ============================================================
     SCRIPTS
     ============================================================ --}}
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ===== SPLASH =====
window.addEventListener('load', function() {
    const splash = document.getElementById('splash');
    if (!splash) return;
    setTimeout(() => {
        splash.style.opacity = '0';
        setTimeout(() => { splash.remove(); sessionStorage.setItem('edenSplashDone','1'); }, 400);
    }, 1000);
});

// ===== SIDEBAR =====
function toggleGroup(id, btn) {
    const items = document.getElementById(id);
    const isOpen = items.classList.contains('open');
    document.querySelectorAll('.nav-group-items').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.nav-group-btn').forEach(el => el.classList.remove('open'));
    if (!isOpen) { items.classList.add('open'); btn.classList.add('open'); }
}

document.addEventListener('DOMContentLoaded', function() {
    const url = window.location.pathname;
    const map = {
        'g-emp'   : '/rh/employes',
        'g-contrat': '/rh/contrats',
        'g-paie'  : '/rh/paie',
        'g-cnps'  : '/rh/cnps',
        'g-abs'   : '/rh/absences',
        'g-conge' : '/rh/conges',
        'g-ret'   : '/rh/retards',
        'g-pret'  : '/rh/prets',
        'g-sanc'  : '/rh/sanctions',
        'g-depart': '/rh/departs',
        'g-param' : '/rh/directions',
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

// ===== MODALS =====
function openModal(id) {
    const overlay = document.getElementById('overlay' + id.charAt(0).toUpperCase() + id.slice(1));
    if (overlay) overlay.style.display = 'block';
    document.getElementById(id).style.display = 'block';
}
function closeModal(id) {
    const overlay = document.getElementById('overlay' + id.charAt(0).toUpperCase() + id.slice(1));
    if (overlay) overlay.style.display = 'none';
    document.getElementById(id).style.display = 'none';
}
function closeAll() {
    document.querySelectorAll('.modal-overlay').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.modal-box').forEach(el => el.style.display = 'none');
}

// ===== PWA UPDATE =====
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
</script>

@yield('scripts')
</body>
</html>