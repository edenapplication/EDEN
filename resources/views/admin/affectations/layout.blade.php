<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Eden Admin — Affectations</title>
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#1d4ed8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        window.CSRF = '{{ csrf_token() }}';
    </script>

    <style>
        * { box-sizing:border-box; }
        body { background:#f4f6f9; font-family:"Segoe UI",sans-serif; margin:0; }

        /* ═══ SIDEBAR ═══ */
        .sidebar {
            width:230px; height:100vh; position:fixed; top:0; left:0;
            background:#0f172a; display:flex; flex-direction:column;
            overflow-y:auto; z-index:200;
        }
        .logo-box { padding:20px 16px 14px; border-bottom:1px solid rgba(255,255,255,0.08); text-align:center; }
        .logo-placeholder-letter {
            width:80px; height:80px; margin:0 auto;
            background:linear-gradient(135deg,#1d4ed8,#7c3aed,#dc2626);
            border-radius:14px; display:flex; align-items:center;
            justify-content:center; font-size:28px; font-weight:900; color:white;
        }
        .logo-name { font-size:14px; font-weight:800; color:#60a5fa; margin-top:10px; }
        .logo-sub { font-size:10px; color:#475569; }

        .sidebar a {
            display:flex; align-items:center; gap:8px;
            color:#cbd5e1; padding:10px 20px; text-decoration:none;
            font-size:13px; transition:0.15s; border-left:3px solid transparent;
        }
        .sidebar a:hover { background:rgba(255,255,255,0.06); color:white; border-left-color:#7c3aed; }
        .sidebar a.active { background:rgba(255,255,255,0.08); color:white; border-left-color:#60a5fa; }

        /* ═══ TOPBAR ═══ */
        .topbar {
            position:fixed; top:0; left:230px; right:0; height:60px; z-index:100;
            background:linear-gradient(135deg,#1d4ed8,#7c3aed,#dc2626);
            color:white; display:flex; align-items:center;
            justify-content:space-between; padding:0 28px;
            box-shadow:0 2px 12px rgba(29,78,216,0.35);
        }
        .topbar-title { font-weight:700; font-size:15px; }
        .topbar-user { display:flex; align-items:center; gap:10px; font-size:13px; }

        /* ═══════════════════════════════════════════ */
        /* 🔥 BARRE PRINCIPALE DES GRANDS BOUTONS      */
        /* ═══════════════════════════════════════════ */
        .main-nav {
            position:fixed; top:60px; left:230px; right:0;
            height:60px; z-index:99;
            background:white;
            border-bottom:3px solid #e2e8f0;
            display:flex; align-items:center;
            padding:0 20px; gap:6px;
            overflow-x:auto;
            box-shadow:0 2px 8px rgba(0,0,0,0.04);
            scrollbar-width:thin;
        }
        .main-nav::-webkit-scrollbar { height:3px; }
        .main-nav::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:2px; }

        .main-nav-btn {
            display:inline-flex; align-items:center; gap:8px;
            padding:10px 18px; border-radius:10px;
            font-size:13px; font-weight:800;
            color:#64748b;
            white-space:nowrap;
            transition:all 0.2s;
            border:2px solid #e2e8f0;
            background:white;
            cursor:pointer;
            flex-shrink:0;
        }
        .main-nav-btn:hover {
            background:#f1f5f9;
            color:#1e3a5f;
            transform:translateY(-2px);
        }
        .main-nav-btn.active {
            background:linear-gradient(135deg,#1d4ed8,#1e40af);
            color:white;
            border-color:#1d4ed8;
            box-shadow:0 4px 12px rgba(29,78,216,0.35);
        }

        .main-nav-btn.group-accueil.active      { background:linear-gradient(135deg,#0891b2,#0e7490); border-color:#0891b2; box-shadow:0 4px 12px rgba(8,145,178,0.35); }
        .main-nav-btn.group-implantation.active { background:linear-gradient(135deg,#f59e0b,#d97706); border-color:#f59e0b; box-shadow:0 4px 12px rgba(245,158,11,0.35); }
        .main-nav-btn.group-planification.active{ background:linear-gradient(135deg,#7c3aed,#6d28d9); border-color:#7c3aed; box-shadow:0 4px 12px rgba(124,58,237,0.35); }
        .main-nav-btn.group-historique.active   { background:linear-gradient(135deg,#1d4ed8,#1e40af); border-color:#1d4ed8; box-shadow:0 4px 12px rgba(29,78,216,0.35); }

        /* ═══════════════════════════════════════════ */
        /* 🔥 BARRE SECONDAIRE (sous-boutons)          */
        /* ═══════════════════════════════════════════ */
        .sub-nav {
            position:fixed; top:120px; left:230px; right:0;
            height:52px; z-index:98;
            background:linear-gradient(90deg, #f8fafc, #f1f5f9);
            border-bottom:2px dashed #cbd5e1;
            display:none; align-items:center;
            padding:0 20px; gap:6px;
            overflow-x:auto;
            animation:slideDown 0.25s ease;
            scrollbar-width:thin;
        }
        .sub-nav::-webkit-scrollbar { height:3px; }
        .sub-nav::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:2px; }
        .sub-nav.visible { display:flex; }

        @keyframes slideDown {
            from { opacity:0; transform:translateY(-10px); }
            to   { opacity:1; transform:translateY(0); }
        }

        .sub-nav-btn {
            display:inline-flex; align-items:center; gap:6px;
            padding:8px 14px; border-radius:8px;
            font-size:12px; font-weight:700;
            color:#475569;
            text-decoration:none;
            white-space:nowrap;
            transition:all 0.2s;
            background:white;
            border:1.5px solid #e2e8f0;
            flex-shrink:0;
        }
        .sub-nav-btn:hover {
            background:#eff6ff;
            color:#1d4ed8;
            border-color:#93c5fd;
            transform:translateY(-2px);
        }
        .sub-nav-btn.active {
            background:linear-gradient(135deg,#1d4ed8,#1e40af);
            color:white;
            border-color:#1d4ed8;
            box-shadow:0 3px 10px rgba(29,78,216,0.3);
        }

        .sub-nav-sep {
            width:1px; height:20px; background:#cbd5e1; margin:0 6px; flex-shrink:0;
        }

        /* ═══ CONTENU ═══ */
        .content {
            margin-left:230px;
            padding:24px;
            transition: padding-top 0.25s ease;
        }
        .content.with-subnav { padding-top:186px; }
        .content.without-subnav { padding-top:136px; }

        /* ═══ TOASTS ═══ */
        .toast-notification {
            position:fixed; bottom:20px; right:20px;
            background:#1f2937; color:#fff;
            padding:12px 20px; border-radius:8px;
            font-size:14px; box-shadow:0 4px 12px rgba(0,0,0,0.3);
            z-index:99999; max-width:400px;
            animation:slideInToast 0.3s ease;
        }
        .toast-notification.success { background:#16a34a; }
        .toast-notification.error   { background:#dc2626; }
        .toast-notification.warning { background:#f59e0b; }
        @keyframes slideInToast { from { transform:translateY(20px); opacity:0; } to { transform:translateY(0); opacity:1; } }

        /* ═══ MODAL STANDARD ═══ */
        .modal-overlay {
            display: none;
            position: fixed; inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 9998;
        }
        .modal-overlay.visible { display: block; }
        .modal-box {
            display: none;
            position: fixed; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            background: white; padding: 24px; border-radius: 14px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.25);
            z-index: 9999; max-width: 95%; max-height: 90vh;
            overflow-y: auto;
        }
        .modal-box.visible { display: block; }

        /* ═══ MOBILE ═══ */
        @media (max-width:768px) {
            .sidebar { transform:translateX(-100%); transition:transform 0.3s ease; z-index:300; }
            .sidebar.open { transform:translateX(0); }
            .topbar { left:0; padding:0 16px; }
            .main-nav { left:0; padding:0 12px; }
            .sub-nav { left:0; padding:0 12px; }
            .content { margin-left:0; padding:16px; }
            .content.with-subnav { padding-top:200px; }
            .content.without-subnav { padding-top:150px; }
        }
    </style>

    @yield('styles')
</head>

<body>

@php
    $role  = auth()->user()?->role;
    $route = request()->route()->getName() ?? '';

    $groupes = [
        'accueil' => [
            'label' => '🏠 Accueil',
            'class' => 'group-accueil',
            'first_url' => route('affectations.index'),
            'patterns' => [
                'affectations.index',
                'affectations.blocs',
                'affectations.lots',
                'grand-sites.*',
                'sites.*',
                'tf.*',
                'lots.*',
            ],
        ],
        'implantation' => [
            'label' => '📅 Planifications',
            'class' => 'group-implantation',
            'first_url' => route('affectation-rapide.index'),
            'patterns' => [
                'affectation-rapide.*',
                'affectations.liste',
                'affectations.affecter*',
            ],
        ],
        'planification' => [
            'label' => '📦 Implantations',
            'class' => 'group-planification',
            'first_url' => route('affectations.programmation-choix'),
            'patterns' => [
                'affectations.programmation-choix',
                'affectations.programmation',
                'affectations.programmation-active',
            ],
        ],
        'historique' => [
            'label' => '📜 Historique',
            'class' => 'group-historique',
            'first_url' => route('affectations.historique'),
            'patterns' => [
                'affectations.historique',
            ],
        ],
    ];

    $groupeActif = null;
    foreach ($groupes as $key => $g) {
        foreach ($g['patterns'] as $pattern) {
            if (\Illuminate\Support\Str::is($pattern, $route)) {
                $groupeActif = $key;
                break 2;
            }
        }
    }
@endphp



{{-- ═══ TOPBAR ═══ --}}
<div class="topbar">
    <div class="topbar-title">🗺️ Module Affectations</div>
    <div class="topbar-user">
        <span>👤 {{ auth()->user()?->name }}</span>
        <span style="font-size:10px;background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:10px;">
            @if($role === 'admin') 🔴 Admin
            @elseif($role === 'rh') 🟣 RH
            @elseif($role === 'geometre') 🟡 Géomètre
            @else 🟢 Commercial
            @endif
        </span>

        {{-- ✅ Déconnexion adaptée au rôle --}}
        @php
            $logoutRoute = ($role === 'geometre')
                ? route('geometre.logout')
                : route('logout');
        @endphp

        <form method="POST" action="{{ $logoutRoute }}" style="display:inline;">
            @csrf
            <button type="submit" style="background:rgba(255,255,255,0.1);color:white;border:1px solid rgba(255,255,255,0.2);border-radius:6px;padding:4px 10px;font-size:11px;cursor:pointer;">
                Déconnexion
            </button>
        </form>
    </div>
</div>

{{-- ═══ BARRE PRINCIPALE ═══ --}}
<div class="main-nav">
    @foreach($groupes as $key => $g)
        <button type="button"
                class="main-nav-btn {{ $g['class'] }} {{ $groupeActif === $key ? 'active' : '' }}"
                onclick="toggleGroupe('{{ $key }}')"
                data-groupe="{{ $key }}"
                data-first-url="{{ $g['first_url'] ?? '' }}">
            {{ $g['label'] }}
        </button>
    @endforeach
</div>

{{-- ═══ SOUS-NAV : ACCUEIL ═══ --}}
<div class="sub-nav" id="subnav-accueil">
    <a href="{{ route('affectations.index') }}"
       class="sub-nav-btn {{ request()->routeIs('affectations.index') ? 'active' : '' }}">
        📊 Création des sites
    </a>
    <a href="{{ route('affectations.blocs') }}"
       class="sub-nav-btn {{ request()->routeIs('affectations.blocs') ? 'active' : '' }}">
        🧱 Blocs
    </a>
    <a href="{{ route('affectations.lots') }}"
       class="sub-nav-btn {{ request()->routeIs('affectations.lots') ? 'active' : '' }}">
        📦 Lots
    </a>
</div>

{{-- ═══ SOUS-NAV : IMPLANTATION ═══ --}}
<div class="sub-nav" id="subnav-implantation">
    <a href="{{ route('affectation-rapide.index') }}"
       class="sub-nav-btn {{ request()->routeIs('affectation-rapide.*') ? 'active' : '' }}">
        🎯 Attribution des parcelles
    </a>
    <a href="{{ route('affectations.liste') }}"
       class="sub-nav-btn {{ request()->routeIs('affectations.liste') ? 'active' : '' }}">
        📋 Planification des implntations
    </a>
</div>

{{-- ═══ SOUS-NAV : PLANIFICATION ═══ --}}
<div class="sub-nav" id="subnav-planification">
    <a href="{{ route('affectations.programmation-choix') }}"
       class="sub-nav-btn {{ request()->routeIs('affectations.programmation-choix') ? 'active' : '' }}">
        📅 Jour d'implantation
    </a>
    <span class="sub-nav-sep"></span>
    <a href="{{ route('affectations.programmation') }}"
       class="sub-nav-btn {{ request()->routeIs('affectations.programmation') && !request()->routeIs('affectations.programmation-choix') ? 'active' : '' }}">
        📝 Programmation des implantations
    </a>
    <a href="{{ route('affectations.programmation-active') }}"
       class="sub-nav-btn {{ request()->routeIs('affectations.programmation-active') ? 'active' : '' }}">
        🎯 Appréciation des implantations
    </a>
</div>

{{-- ═══ SOUS-NAV : HISTORIQUE ═══ --}}
<div class="sub-nav" id="subnav-historique">
    <a href="{{ route('affectations.historique') }}"
       class="sub-nav-btn {{ request()->routeIs('affectations.historique') ? 'active' : '' }}">
        📜 Historique
    </a>
</div>

{{-- ═══ CONTENU ═══ --}}
<div class="content" id="content-wrapper">
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const groupeActif = '{{ $groupeActif ?? "" }}';

function toggleGroupe(nom) {
    const btn = document.querySelector(`.main-nav-btn[data-groupe="${nom}"]`);
    if (!btn) return;

    const isActive = btn.classList.contains('active');

    if (isActive) {
        document.querySelectorAll('.main-nav-btn').forEach(b => b.classList.remove('active'));
        document.querySelectorAll('.sub-nav').forEach(s => s.classList.remove('visible'));

        const wrapper = document.getElementById('content-wrapper');
        wrapper.classList.remove('with-subnav');
        wrapper.classList.add('without-subnav');
        return;
    }

    const firstUrl = btn.dataset.firstUrl;

    if (firstUrl) {
        window.location.href = firstUrl;
        return;
    }

    document.querySelectorAll('.main-nav-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.sub-nav').forEach(s => s.classList.remove('visible'));

    btn.classList.add('active');
    const subnav = document.getElementById('subnav-' + nom);
    const wrapper = document.getElementById('content-wrapper');

    if (subnav && subnav.children.length > 0) {
        subnav.classList.add('visible');
        wrapper.classList.add('with-subnav');
        wrapper.classList.remove('without-subnav');
    } else {
        wrapper.classList.remove('with-subnav');
        wrapper.classList.add('without-subnav');
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const wrapper = document.getElementById('content-wrapper');

    if (groupeActif) {
        const subnav = document.getElementById('subnav-' + groupeActif);
        if (subnav && subnav.children.length > 0) {
            subnav.classList.add('visible');
            wrapper.classList.add('with-subnav');
            wrapper.classList.remove('without-subnav');
        } else {
            wrapper.classList.remove('with-subnav');
            wrapper.classList.add('without-subnav');
        }
    } else {
        wrapper.classList.add('without-subnav');
    }
});

function showToast(message, type = 'info') {
    document.querySelectorAll('.toast-notification').forEach(el => el.remove());
    const toast = document.createElement('div');
    toast.className = `toast-notification ${type}`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transition = 'opacity 0.3s ease';
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}
</script>

@yield('scripts')
</body>
</html>