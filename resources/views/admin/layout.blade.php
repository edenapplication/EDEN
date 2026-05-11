<!DOCTYPE html>
<html>
<head>
    <title>Eden Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f9; font-family:"Segoe UI",sans-serif; }

        /* ===================== SIDEBAR ===================== */
        .sidebar {
            width:230px; height:100vh; position:fixed;
            background:#0f172a;
            display:flex; flex-direction:column; justify-content:space-between;
            overflow-y:auto; overflow-x:hidden;
        }

        /* LOGO */
        .logo-box {
            padding:20px 16px 14px;
            border-bottom:1px solid rgba(255,255,255,0.08);
            display:flex; flex-direction:column; align-items:center; gap:10px;
        }

        .logo-img-wrap {
    width:80px;
    height:80px;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 4px 12px rgba(0,0,0,0.25); /* léger */
}

    .logo-img-wrap img {
    width: 120%;
    height: 120%;
    object-fit: contain;
    transform: scale(1.8); /* zoom propre */
}

        /* Placeholder si pas encore d'image */
        .logo-placeholder-letter {
            width:80px; height:80px;
            background:linear-gradient(135deg, #1d4ed8 0%, #7c3aed 50%, #dc2626 100%);
            border-radius:14px;
            display:flex; align-items:center; justify-content:center;
            font-size:28px; font-weight:900; color:white;
            box-shadow:0 4px 18px rgba(0,0,0,0.4);
        }
        .logo-name {
            font-size:14px; font-weight:800; letter-spacing:0.5px;
            background:linear-gradient(90deg, #60a5fa, #a78bfa, #f87171);
            -webkit-background-clip:text; -webkit-text-fill-color:transparent;
            background-clip:text;
        }
        .logo-sub { font-size:10px; color:#475569; margin-top:-4px; }

        /* NAV GROUPES */
        .nav-section { padding:10px 0; }

        /* Bouton groupe (déclencheur dropdown) */
        .nav-group-btn {
            display:flex; align-items:center; justify-content:space-between;
            width:100%; padding:9px 16px;
            background:none; border:none;
            color:#94a3b8; font-size:11px; font-weight:700;
            text-transform:uppercase; letter-spacing:1px;
            cursor:pointer; transition:0.15s;
        }
        .nav-group-btn:hover { color:#cbd5e1; }
        .nav-group-btn .chevron {
            font-size:10px; transition:transform 0.2s;
        }
        .nav-group-btn.open .chevron { transform:rotate(90deg); }

        /* Items du groupe */
        .nav-group-items {
            display:none; padding-bottom:4px;
        }
        .nav-group-items.open { display:block; }

        .sidebar a {
            display:flex; align-items:center; gap:8px;
            color:#cbd5e1; padding:8px 20px;
            text-decoration:none; border-radius:0;
            font-size:13px; transition:0.15s;
            border-left:3px solid transparent;
        }
        .sidebar a:hover {
            background:rgba(255,255,255,0.06);
            color:white;
            border-left-color:#7c3aed;
        }
        .sidebar a.active {
            background:rgba(255,255,255,0.08);
            color:white;
            border-left-color:#60a5fa;
        }

        /* Lien dashboard — toujours visible, pas dans un groupe */
        .nav-direct {
            padding:4px 0 8px;
            border-bottom:1px solid rgba(255,255,255,0.06);
        }

        /* ===================== TOPBAR ===================== */
        .topbar {
            margin-left:230px; height:60px;
            /* Bleu → violet → rouge */
            background:linear-gradient(135deg, #1d4ed8 0%, #7c3aed 55%, #dc2626 100%);
            color:white; display:flex; align-items:center;
            justify-content:space-between; padding:0 28px;
            box-shadow:0 2px 12px rgba(29,78,216,0.35);
        }
        .topbar-title { font-weight:700; font-size:15px; letter-spacing:0.3px; }
        .topbar-user {
            display:flex; align-items:center; gap:8px; font-size:13px;
            background:rgba(255,255,255,0.18); padding:6px 14px;
            border-radius:20px;
        }

        .content { margin-left:230px; padding:24px; }

        /* Bouton déconnexion */
        .logout-btn {
            margin:12px; width:calc(100% - 24px);
            background:linear-gradient(135deg, #1d4ed8, #dc2626);
            color:white; border-radius:8px;
            padding:9px 12px; border:none; font-weight:600;
            cursor:pointer; font-size:13px;
            transition:opacity 0.2s;
        }
        .logout-btn:hover { opacity:0.85; }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        {{-- LOGO --}}
        <div class="logo-box">
            {{-- ✅ Remplace le src par le chemin de ton image --}}
            @if(file_exists(public_path('images/eden_groups.png')))
                <div class="logo-img-wrap">
                    <img src="{{ asset('images/eden_group.png') }}" alt="Logo Eden Group">
                </div>
            @else
                {{-- Placeholder tant que l'image n'est pas uploadée --}}
                <div class="logo-placeholder-letter">E</div>
            @endif
            <div>
                <div class="logo-name">EDEN GROUP</div>
                <div class="logo-sub">Administration</div>
            </div>
        </div>

        {{-- DASHBOARD — lien direct, pas dans un groupe --}}
        <div class="nav-direct">
            <a href="{{ route('admin.dashboard') }}">
                <span>📊</span> Dashboard
            </a>
        </div>

        <div class="nav-section">

            {{-- GROUPE : GESTION FONCIÈRE --}}
            <button class="nav-group-btn" onclick="toggleGroup('group-foncier', this)">
                <span>🏢 Gestion foncière</span>
                <span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="group-foncier">
                <a href="{{ route('grand-sites.index') }}">
                    <span>🗂️</span> Sites & Zones
                </a>
                <a href="{{ route('lots.vendus') }}">
                    <span>📁</span> Suivi des dossiers techniques
                </a>
            </div>

            {{-- GROUPE : CLIENTS & ÉQUIPES --}}
            <button class="nav-group-btn" onclick="toggleGroup('group-clients', this)">
                <span>👥 Clients & Équipes</span>
                <span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="group-clients">
                <a href="{{ route('suivi-client.index') }}">
                    <span>👤</span> Suivi clients et nouveaux dossiers
                </a>
                <a href="{{ route('commerciaux.index') }}">
                    <span>🧑‍💼</span> Commerciaux
                </a>
                <a href="{{ route('agents.index') }}">
                    <span>🤝</span> Agents commerciaux
                </a>
            </div>

            {{-- GROUPE : RAPPORTS & DONNÉES --}}
            <button class="nav-group-btn" onclick="toggleGroup('group-rapports', this)">
                <span>📊 Rapports & Données</span>
                <span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="group-rapports">
                <a href="{{ route('rapport.index') }}">
                    <span>📈</span> Rapport d'activité
                </a>
                <a href="{{ route('rapport.liste') }}">
                    <span>🗂️</span> Rapports sauvegardés
                </a>
                <a href="{{ route('import-export.index') }}">
                    <span>🔄</span> Import / Export
                </a>
                <a href="{{ route('visites.index') }}">
                    <span>🚶</span> Registre des visites
                </a>
            </div>

        </div>
    </div>

    <div>
        <button class="logout-btn" onclick="alert('Déconnexion')">
            🚪 Déconnexion
        </button>
    </div>
</div>

<div class="topbar">
    <div class="topbar-title">🌍 EDEN Administration</div>
    <div class="topbar-user">
        <span>👤</span>
        <span>Admin connecté</span>
    </div>
</div>

<div class="content">
    @yield('content')
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// =============================================
// TOGGLE GROUPE DE NAVIGATION
// =============================================
function toggleGroup(id, btn) {
    const items = document.getElementById(id);
    const isOpen = items.classList.contains('open');

    // Fermer tous les groupes
    document.querySelectorAll('.nav-group-items').forEach(el => el.classList.remove('open'));
    document.querySelectorAll('.nav-group-btn').forEach(el => el.classList.remove('open'));

    // Ouvrir celui cliqué si pas déjà ouvert
    if (!isOpen) {
        items.classList.add('open');
        btn.classList.add('open');
    }
}

// =============================================
// OUVRIR AUTOMATIQUEMENT LE GROUPE ACTIF
// =============================================
document.addEventListener('DOMContentLoaded', function() {
    const currentUrl = window.location.pathname;

    const groupes = {
        'group-foncier' : ['/admin/grand-sites', '/admin/sites', '/admin/tf', '/admin/lots'],
        'group-clients' : ['/admin/suivi-client', '/admin/commerciaux', '/admin/agents'],
        'group-rapports': ['/admin/rapport', '/admin/import-export'],
    };

    Object.entries(groupes).forEach(([groupId, prefixes]) => {
        const actif = prefixes.some(p => currentUrl.startsWith(p));
        if (actif) {
            const items = document.getElementById(groupId);
            const btn   = items?.previousElementSibling;
            if (items) items.classList.add('open');
            if (btn)   btn.classList.add('open');
        }
    });

    // Marquer le lien actif
    document.querySelectorAll('.sidebar a').forEach(a => {
        if (a.getAttribute('href') && currentUrl.startsWith(a.getAttribute('href').replace(window.location.origin, ''))) {
            a.classList.add('active');
        }
    });
});
</script>

@yield('scripts')
</body>
</html>