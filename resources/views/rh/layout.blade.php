<!DOCTYPE html>
<html>
<head>
    <title>RH — Eden Group</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background:#f4f6f9; font-family:"Segoe UI",sans-serif; }
        .sidebar {
            width:230px; height:100vh; position:fixed;
            background:#0f172a; display:flex; flex-direction:column;
            justify-content:space-between; overflow-y:auto;
        }
        .logo-box {
            padding:20px 16px 14px; border-bottom:1px solid rgba(255,255,255,0.08);
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
            text-transform:uppercase; letter-spacing:1px; cursor:pointer;
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
        .topbar {
            margin-left:230px; height:60px;
            background:linear-gradient(135deg,#1d4ed8 0%,#7c3aed 55%,#dc2626 100%);
            color:white; display:flex; align-items:center;
            justify-content:space-between; padding:0 28px;
        }
        .content { margin-left:230px; padding:24px; }
        .logout-btn {
            margin:12px; width:calc(100% - 24px);
            background:linear-gradient(135deg,#1d4ed8,#dc2626);
            color:white; border-radius:8px; padding:9px; border:none;
            font-weight:600; cursor:pointer; font-size:13px;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div>
        {{-- LOGO --}}
        <div class="logo-box">
            <div class="logo-circle">RH</div>
            <div class="logo-name">EDEN GROUP</div>
            <div class="logo-sub">Module Ressources Humaines</div>
        </div>

        {{-- Liens directs --}}
        <div style="padding:8px 0; border-bottom:1px solid rgba(255,255,255,0.06);">
            <a href="{{ route('admin.dashboard') }}">⬅ Admin</a>
            <a href="{{ route('rh.dashboard') }}">📊 Dashboard RH</a>
        </div>

        <div style="padding:8px 0;">

            {{-- Paramètres --}}
<button class="nav-group-btn" onclick="toggleGroup('g-param', this)">
    <span>⚙️ Paramètres</span><span class="chevron">›</span>
</button>
<div class="nav-group-items" id="g-param">
    <a href="{{ route('rh.directions.index') }}"><span>🏢</span> Directions & Services</a>
</div>

            {{-- Employés --}}
            <button class="nav-group-btn" onclick="toggleGroup('g-emp', this)">
                <span>👥 Employés</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-emp">
                <a href="{{ route('rh.employes.index') }}"><span>📋</span> Liste</a>
                <a href="{{ route('rh.employes.create') }}"><span>➕</span> Nouveau</a>
            </div>

            {{-- Paie --}}
            <button class="nav-group-btn" onclick="toggleGroup('g-paie', this)">
                <span>💰 Paie</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-paie">
                <a href="{{ route('rh.paie.index') }}"><span>📄</span> Bulletins</a>
                <a href="{{ route('rh.paie.create') }}"><span>➕</span> Nouveau bulletin</a>
                <a href="{{ route('rh.paie.recapitulatif') }}"><span>📊</span> Récapitulatif</a>
            </div>

            {{-- Absences --}}
            <button class="nav-group-btn" onclick="toggleGroup('g-abs', this)">
                <span>🗓️ Absences</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-abs">
                <a href="{{ route('rh.absences.index') }}"><span>📋</span> Suivi absences</a>
            </div>

            {{-- Prêts --}}
            <button class="nav-group-btn" onclick="toggleGroup('g-pret', this)">
                <span>🏦 Prêts & Acomptes</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-pret">
                <a href="{{ route('rh.prets.index') }}"><span>📋</span> Liste</a>
            </div>

            {{-- Sanctions --}}
            <button class="nav-group-btn" onclick="toggleGroup('g-sanc', this)">
                <span>⚠️ Sanctions</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-sanc">
                <a href="{{ route('rh.sanctions.index') }}"><span>📋</span> Liste</a>
            </div>

            {{-- Retards --}}
            <button class="nav-group-btn" onclick="toggleGroup('g-ret', this)">
                <span>⏱️ Retards</span><span class="chevron">›</span>
            </button>
            <div class="nav-group-items" id="g-ret">
                <a href="{{ route('rh.retards.index') }}"><span>📋</span> Liste</a>
            </div>

        </div>
    </div>

    <div>
        <button class="logout-btn">🚪 Déconnexion</button>
    </div>
</div>

<div class="topbar">
    <div style="font-weight:700;">🏢 RH — Eden Group</div>
    <div style="font-size:13px;background:rgba(255,255,255,0.15);padding:6px 14px;border-radius:20px;">
        👤 Admin RH
    </div>
</div>

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
    document.querySelectorAll('.nav-group-btn').forEach(el => el.classList.remove('open'));
    if (!isOpen) { items.classList.add('open'); btn.classList.add('open'); }
}

document.addEventListener('DOMContentLoaded', function() {
    const url = window.location.pathname;
    const map = {
        'g-emp' : '/rh/employes',
        'g-paie': '/rh/paie',
        'g-abs' : '/rh/absences',
        'g-pret': '/rh/prets',
        'g-sanc': '/rh/sanctions',
        'g-ret' : '/rh/retards',
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
        if (a.href && url === new URL(a.href).pathname) a.classList.add('active');
    });
});
</script>
@yield('scripts')
</body>
</html>