<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="manifest" href="/manifest.json?v=2">
    <link rel="apple-touch-icon" href="/images/icon-192.png">
    <title>Eden Group — Accueil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 60%, #1a4a8a 100%);
            font-family: 'Segoe UI', sans-serif;
            color: white;
        }

        /* TOPBAR */
        .topbar {
            background: rgba(255,255,255,0.07);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            padding: 14px 32px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .topbar .brand { font-size: 20px; font-weight: 800; display: flex; align-items: center; gap: 10px; }
        .topbar .brand .icon {
            width: 40px; height: 40px; background: linear-gradient(135deg, #2d6cdf, #7c3aed);
            border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 20px;
        }
        .topbar .user-info { display: flex; align-items: center; gap: 12px; font-size: 13px; }
        .role-badge {
            padding: 3px 12px; border-radius: 20px; font-size: 11px; font-weight: 700;
            background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25);
        }
        .btn-logout {
            background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.2);
            border-radius: 8px; padding: 6px 14px; font-size: 12px; cursor: pointer; transition: 0.2s;
            text-decoration: none;
        }
        .btn-logout:hover { background: rgba(255,0,0,0.2); color: white; }

        /* HERO */
        .hero {
            text-align: center; padding: 50px 20px 30px;
        }
        .hero h2 { font-size: 32px; font-weight: 900; margin-bottom: 8px; }
        .hero p { font-size: 15px; color: rgba(255,255,255,0.65); }

        /* MODULES */
        .modules { padding: 20px 40px 60px; max-width: 1100px; margin: 0 auto; }
        .modules-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; }

        .module-card {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 18px; padding: 28px 24px;
            text-decoration: none; color: white;
            transition: transform 0.2s, background 0.2s, box-shadow 0.2s;
            display: flex; flex-direction: column; gap: 12px;
        }
        .module-card:hover {
            transform: translateY(-4px);
            background: rgba(255,255,255,0.14);
            box-shadow: 0 20px 40px rgba(0,0,0,0.3);
            color: white;
        }
        .module-card .icon {
            width: 56px; height: 56px; border-radius: 14px;
            display: flex; align-items: center; justify-content: center; font-size: 26px;
        }
        .module-card h3 { font-size: 17px; font-weight: 800; margin: 0; }
        .module-card p  { font-size: 12px; color: rgba(255,255,255,0.6); margin: 0; line-height: 1.5; }
        .module-card .arrow {
            margin-top: auto; font-size: 12px; color: rgba(255,255,255,0.5);
            display: flex; align-items: center; gap: 4px;
        }
        .module-card:hover .arrow { color: rgba(255,255,255,0.9); }

        .module-disabled {
            opacity: 0.35; cursor: not-allowed; pointer-events: none;
        }

        .section-title {
            font-size: 11px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 0.1em; color: rgba(255,255,255,0.45);
            margin-bottom: 14px; margin-top: 10px;
        }
    </style>
</head>
<body>

{{-- TOPBAR --}}
<div class="topbar">
    <div class="brand">
        <div class="icon">ED</div>
        <span>EDEN GROUP</span>
    </div>
    <div class="user-info">
        <span>👤 {{ auth()->user()->name }}</span>
        <span class="role-badge">
            {{ ['admin' => '🔑 Admin', 'rh' => '👥 RH', 'commercial' => '💼 Commercial'][auth()->user()->role] }}
        </span>
        <form method="POST" action="{{ route('logout') }}" style="display:inline;">
            @csrf
            <button type="submit" class="btn-logout">Déconnexion →</button>
        </form>
    </div>
</div>

{{-- HERO --}}
<div class="hero">
    <h2>Bienvenue, {{ auth()->user()->name }} 👋</h2>
    <p>Choisissez un module pour commencer</p>
</div>

{{-- MODULES --}}
<div class="modules">

    @php $role = auth()->user()->role; @endphp

    {{-- FONCIER --}}
    @if(in_array($role, ['admin', 'commercial']))
    <div class="section-title">🏗️ Gestion Foncière</div>
    <div class="modules-grid" style="margin-bottom:32px;">

        @if($role === 'admin')
        <a href="{{ route('admin.dashboard') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);">📊</div>
            <h3>Dashboard</h3>
            <p>Vue globale de l'état des sites, lots et activités</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('grand-sites.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#1e3a5f,#2d6cdf);">🏢</div>
            <h3>Grands Sites</h3>
            <p>Gérer les grands sites, sites, TF et cartes SVG</p>
            <div class="arrow">Accéder →</div>
        </a>
        @endif

        <a href="{{ route('lots.vendus') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#7c3aed,#a855f7);">📦</div>
            <h3>Suivi des Dossiers</h3>
            <p>Lots et zones affectés à des clients, avancement</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('suivi-client.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#0891b2,#06b6d4);">👥</div>
            <h3>Clients & Dossiers</h3>
            <p>Enregistrer les clients, créer et suivre les dossiers</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('visites.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#059669,#10b981);">🚶</div>
            <h3>Registre des Visites</h3>
            <p>Gérer les entrées et sorties du site</p>
            <div class="arrow">Accéder →</div>
        </a>

        @if($role === 'admin')
        <a href="{{ route('rapport.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#d97706,#f59e0b);">📝</div>
            <h3>Rapports</h3>
            <p>Créer, consulter et exporter les rapports d'activité</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('commerciaux.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#dc2626,#ef4444);">🤝</div>
            <h3>Équipes Commerciales</h3>
            <p>Commerciaux, agents, facilitateurs, conducteurs</p>
            <div class="arrow">Accéder →</div>
        </a>
        @endif
    </div>
    @endif

    {{-- RH --}}
    @if(in_array($role, ['admin', 'rh']))
    <div class="section-title">👔 Ressources Humaines</div>
    <div class="modules-grid" style="margin-bottom:32px;">

        <a href="{{ route('rh.dashboard') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#7c3aed,#9333ea);">📊</div>
            <h3>Dashboard RH</h3>
            <p>Vue globale des effectifs, paie et mouvements</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('rh.employes.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#1d4ed8,#3b82f6);">👤</div>
            <h3>Employés</h3>
            <p>Fiches employés, documents, suivi individuel</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('rh.paie.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#16a34a,#22c55e);">💰</div>
            <h3>Bulletins de Paie</h3>
            <p>Génération, validation et export des bulletins</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('rh.absences.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#0891b2,#06b6d4);">🗓️</div>
            <h3>Absences & Permissions</h3>
            <p>Demandes, approbations et historique</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('rh.prets.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#9333ea,#a855f7);">🏦</div>
            <h3>Prêts & Acomptes</h3>
            <p>Gestion des prêts et suivi des remboursements</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('rh.sanctions.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#dc2626,#ef4444);">⚠️</div>
            <h3>Sanctions</h3>
            <p>Enregistrement et suivi des sanctions disciplinaires</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('rh.retards.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#d97706,#f59e0b);">⏰</div>
            <h3>Retards</h3>
            <p>Suivi des retards par employé, direction et service</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('rh.directions.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#0f172a,#1e3a5f);">⚙️</div>
            <h3>Directions & Postes</h3>
            <p>Paramétrer les directions, services et postes</p>
            <div class="arrow">Accéder →</div>
        </a>
    </div>
    @endif

    {{-- ADMINISTRATION --}}
    @if($role === 'admin')
    <div class="section-title">🔑 Administration système</div>
    <div class="modules-grid">
        <a href="{{ route('admin.users.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#dc2626,#b91c1c);">🔑</div>
            <h3>Gestion des Accès</h3>
            <p>Créer les utilisateurs et définir leurs rôles</p>
            <div class="arrow">Accéder →</div>
        </a>

        <a href="{{ route('import-export.index') }}" class="module-card">
            <div class="icon" style="background:linear-gradient(135deg,#374151,#6b7280);">💾</div>
            <h3>Import / Export</h3>
            <p>Sauvegarder et restaurer la base de données</p>
            <div class="arrow">Accéder →</div>
        </a>
    </div>
    @endif

</div>

</body>
</html>