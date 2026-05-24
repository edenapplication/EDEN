<!DOCTYPE html>
<html>
<head>
    <title>Eden Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f9; font-family:"Segoe UI",sans-serif; }

        /* ===================== SIDEBAR ===================== */
        .sidebar {
            width:230px; height:100vh; position:fixed; top:0; left:0;
            background:#0f172a;
            display:flex; flex-direction:column;
            overflow-y:auto; overflow-x:hidden;
            z-index:200;
        }
        .logo-box {
            padding:20px 16px 14px;
            border-bottom:1px solid rgba(255,255,255,0.08);
            display:flex; flex-direction:column; align-items:center; gap:10px;
        }
        .logo-img-wrap {
            width:80px; height:80px; border-radius:14px;
            overflow:hidden; box-shadow:0 4px 12px rgba(0,0,0,0.25);
        }
        .logo-img-wrap img {
            width:120%; height:120%; object-fit:contain; transform:scale(1.8);
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
        .sidebar a:hover { background:rgba(255,255,255,0.06); color:white; border-left-color:#7c3aed; }
        .sidebar a.active { background:rgba(255,255,255,0.08); color:white; border-left-color:#60a5fa; }
        .nav-direct { padding:4px 0 8px; border-bottom:1px solid rgba(255,255,255,0.06); }

        /* ===================== TOPBAR FIXE ===================== */
        .topbar {
            position:fixed; top:0; left:230px; right:0; height:60px; z-index:100;
            background:linear-gradient(135deg,#1d4ed8 0%,#7c3aed 55%,#dc2626 100%);
            color:white; display:flex; align-items:center;
            justify-content:space-between; padding:0 28px;
            box-shadow:0 2px 12px rgba(29,78,216,0.35);
        }
        .topbar-title { font-weight:700; font-size:15px; letter-spacing:0.3px; }
        .topbar-right  { display:flex; align-items:center; gap:10px; }
        .topbar-user {
            display:flex; align-items:center; gap:8px; font-size:13px;
            background:rgba(255,255,255,0.18); padding:6px 14px; border-radius:20px;
        }

        /* ✅ Contenu décalé pour topbar fixe */
        .content { margin-left:230px; padding:24px; padding-top:84px; }
    </style>
</head>
<body>

{{-- ===================== SIDEBAR ===================== --}}
<div class="sidebar">
    <div>
        {{-- LOGO --}}
        <div class="logo-box">
            @if(file_exists(public_path('images/eden_group.png')))
                <div class="logo-img-wrap">
                    <img src="{{ asset('images/eden_group.png') }}" alt="Logo Eden Group">
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

        {{-- ✅ Gestion Accès — admin seulement --}}
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
                <a href="{{ route('grand-sites.index') }}"><span>🗂️</span> Sites & Zones</a>
                <a href="{{ route('lots.vendus') }}"><span>📁</span> Suivi des parcelles</a>
            </div>

            {{-- CLIENTS & ÉQUIPES --}}
            <button class="nav-group-btn" onclick="toggleGroup('group-clients', this)">
                <span>👥 Clients & Équipes</span>
                <span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="group-clients">
                <a href="{{ route('suivi-client.index') }}"><span>👤</span> Suivi clients</a>
                {{-- ✅ Commerciaux — admin seulement --}}
                @if(auth()->user()?->isAdmin())
                    <a href="{{ route('commerciaux.index') }}"><span>🧑‍💼</span> Commerciaux</a>
                    <a href="{{ route('agents.index') }}"><span>🤝</span> Agents commerciaux</a>
                @endif
            </div>

            {{-- RAPPORTS & DONNÉES — admin seulement --}}
            @if(auth()->user()?->isAdmin())
            <button class="nav-group-btn" onclick="toggleGroup('group-rapports', this)">
                <span>📊 Rapports & Données</span>
                <span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="group-rapports">
                <a href="{{ route('rapport.index') }}"><span>📈</span> Rapport d'activité</a>
                <a href="{{ route('rapport.liste') }}"><span>🗂️</span> Rapports sauvegardés</a>
                <a href="{{ route('import-export.index') }}"><span>🔄</span> Import / Export</a>
                <a href="{{ route('visites.index') }}"><span>🚶</span> Registre des visites</a>
            </div>
            @endif

            {{-- VISITES — commercial aussi --}}
            @if(!auth()->user()?->isAdmin())
            <div style="padding:4px 0;">
                <a href="{{ route('visites.index') }}"><span>🚶</span> Registre des visites</a>
            </div>
            @endif

        </div>
    </div>
</div>

{{-- ===================== TOPBAR FIXE ===================== --}}
<div class="topbar">
    <div class="topbar-title">🌍 EDEN Administration</div>
    <div class="topbar-right">
        {{-- ✅ Bouton retour à l'accueil modules --}}
        <a href="{{ route('home') }}"
           style="background:rgba(255,255,255,0.15);color:white;border:1px solid rgba(255,255,255,0.25);border-radius:8px;padding:5px 12px;font-size:11px;font-weight:600;text-decoration:none;transition:0.2s;"
           onmouseover="this.style.background='rgba(255,255,255,0.25)'"
           onmouseout="this.style.background='rgba(255,255,255,0.15)'">
            🏠 Accueil
        </a>
        <div class="topbar-user">
            <span>👤</span>
            <span style="font-size:12px;">{{ auth()->user()?->name }}</span>
            <span style="font-size:10px;background:rgba(255,255,255,0.2);padding:2px 8px;border-radius:10px;">
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

{{-- ===================== CONTENU ===================== --}}
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleGroup(id, btn) {
    const items = document.getElementById(id);
    const isOpen = items.classList.contains('open');
    document.querySelectorAll('.nav-group-items').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.nav-group-btn').forEach(el   => el.classList.remove('open'));
    if (!isOpen) { items.classList.add('open'); btn.classList.add('open'); }
}

document.addEventListener('DOMContentLoaded', function() {
    const currentUrl = window.location.pathname;
    const groupes = {
        'group-foncier' : ['/admin/grand-sites', '/admin/sites', '/admin/tf', '/admin/lots'],
        'group-clients' : ['/admin/suivi-client', '/admin/commerciaux', '/admin/agents'],
        'group-rapports': ['/admin/rapport', '/admin/import-export', '/admin/visites'],
    };
    Object.entries(groupes).forEach(([groupId, prefixes]) => {
        if (!document.getElementById(groupId)) return;
        const actif = prefixes.some(p => currentUrl.startsWith(p));
        if (actif) {
            const items = document.getElementById(groupId);
            const btn   = items?.previousElementSibling;
            if (items) items.classList.add('open');
            if (btn)   btn.classList.add('open');
        }
    });
    document.querySelectorAll('.sidebar a').forEach(a => {
        try {
            if (a.getAttribute('href') && currentUrl.startsWith(new URL(a.href).pathname)) {
                a.classList.add('active');
            }
        } catch(e) {}
    });
});
</script>
@yield('scripts')
</body>
</html>