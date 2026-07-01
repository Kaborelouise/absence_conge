<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'ANPTIC') - Gestion des demandes d'autorisation d'absence et de congé</title>

        <!-- Font -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

        <style>
            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                padding: 0;
                font-family: 'Segoe UI', Tahoma, sans-serif;
                background-color: #f0f2f5;
                min-height: 100vh;
            }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                width: 230px;
                height: 100vh;
                background-color: #1b384f;
                overflow-y: auto;
                z-index: 1000;
                display: flex;
                flex-direction: column;
            }

            .sidebar-brand {
                /* padding: 18px; */
                /* border-bottom: 3px solid rgba(255, 255, 255, 1); */
                /* display: flex; */
                /* align-items: center; */
                text-align: center;
                /* gap: 12px; */
                /* text-decoration: none; */
            }

            .sidebar-brand-text {
                color: white;
                line-height: 1.3;
            }

            .sidebar-brand-text .brand-name {
                font-size: 14px;
                font-weight: 700;
                display: block;
            }

            .sidebar-brand-text .brand-subtitle {
                font-size: 14px;
                color: rgba(255, 255, 255, 0.5);
            }

            .sidebar-section-title {
                padding: 16px 16px 6px;
                font-size: 10px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 1.2px;
                color: rgba(255, 255, 255, 0.3);
            }

            .sidebar-link {
                display: flex;
                align-items: center;
                gap: 10px;
                padding: 10px 16px;
                color: rgba(255, 255, 255, 0.6);
                text-decoration: none;
                font-size: 13px;
                transition: all 1.2s ease;
                border-left: 3px solid transparent;
            }

            .sidebar-link:hover {
                color: rgba(255, 255, 255, 0.9);
                background-color: rgba(255, 255, 255, 0.06);
            }

            .sidebar-link.active {
                color: white;
                background-color: rgba(255, 255, 255, 0.1);
                border-left-color: #42A5F5;
            }

            .sidebar-link i {
                font-size: 15px;
                width: 16px;
                text-align: center;
            }

            .sidebar-footer {
                margin-top: auto;
                padding: 16px;
                border-top: 1px solid rgba(255, 255, 255, 0.08);
            }

            .main-wrapper {
                margin-left: 230px;
                min-height: 100vh;
                display: flex;
                flex-direction: column;
            }

            .top-bar {
                background-color: white;
                padding: 0 24px;
                height: 56px;
                border-bottom: 1px solid #e8ecf0;
                display: flex;
                justify-content: space-between;
                align-items: center;
                position: sticky;
                top: 0;
                z-index: 999;
                box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            }

            .top-bar-title {
                font-size: 18px;
                font-weight: 600;
                color: #2c3e50;
            }

            .top-bar-user {
                display: flex;
                align-items: center;
                gap: 8px;
                color: #555;
                font-size: 13px;
                cursor: pointer;
            }

            .user-avatar {
                width: 34px;
                height: 34px;
                background: linear-gradient(135deg, #1976D2, #42A5F5);
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-weight: 700;
                font-size: 13px;
                flex-shrink: 0;
            }

            .page-body {
                padding: 24px;
                flex: 1;
            }

            .card {
                border: none;
                border-radius: 10px;
                box-shadow: 0 1px 6px rgba(0, 0, 0, 0.07);
            }

            .card-header-anptic {
                background-color: #1B384F !important;
                color: white !important;
            }

            .card-header {
                border-radius: 10px 10px 0 0 !important;
                padding: 14px 20px;
                border-bottom: 1px solid #1B384F !important;
                font-weight: 600;
            }

            .badge-statut {
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 11px;
                font-weight: 500;
                display: inline-block;
            }

            .badge-en_attente  { background-color: #fff3cd; color: #856404; }
            .badge-en_cours    { background-color: #cfe2ff; color: #084298; }
            .badge-validee     { background-color: #d1e7dd; color: #0a3622; }
            .badge-rejetee     { background-color: #f8d7da; color: #842029; }
            .badge-compilee    { background-color: #e2d9f3; color: #6f42c1; }

            .btn-action {
                padding: 4px 10px;
                font-size: 12px;
                border-radius: 5px;
            }

            .table-anptic-dark th {
                background-color: #1B384F !important;
                 color: white !important;
            }
        </style>

        @yield('styles')
    </head>
    <body>

        {{-- Sidebar --}}
        <div class="sidebar">
            <div class="text-center mt-3">
                <a href="{{ route('accueil') }}" class="sidebar-brand">
                    <img src="{{ asset('images/logo_anptic.png') }}" alt="Logo ANPTIC" style="width: 100px; height: 100px; object-fit: contain; flex-shrink: 0;">
                </a>
            </div>
            
            {{-- <div>
                <span>
                    Gestion des congés et<br>des autorisations d'absence
                </span>
            </div> --}}

            <div class="sidebar-section-title">Menu principal</div>

            <a href="{{ route('accueil') }}"
               class="sidebar-link {{ request()->routeIs('accueil') ? 'active' : '' }}">
                <i class="bi bi-house-door"></i>
                <span>Accueil</span>
            </a>

            <a href="{{ route('dashboard') }}"
               class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid-1x2"></i>
                <span>Tableau de bord</span>
            </a>

            <div class="sidebar-section-title">Demandes</div>

            <a href="{{ route('demande_absences.index') }}"
               class="sidebar-link {{ request()->routeIs('demande_absences.*') ? 'active' : '' }}">
                <i class="bi bi-person-x"></i>
                <span>Autorisation d'absence</span>
            </a>

            <a href="{{ route('demande_conges.index') }}"
               class="sidebar-link {{ request()->routeIs('demande_conges.*') ? 'active' : '' }}">
                <i class="bi bi-bookmark-plus"></i>
                <span>Demande de congé</span>
            </a>

            <a href="{{ route('demande_jouissances.index') }}"
               class="sidebar-link {{ request()->routeIs('demande_jouissances.*') ? 'active' : '' }}">
                <i class="bi bi-bookmark-check"></i>
                <span>Demande de jouissance</span>
            </a>

            <div class="sidebar-section-title">Administration</div>

            <a href="{{ route('utilisateurs.index') }}"
               class="sidebar-link {{ request()->routeIs('utilisateurs.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i>
                <span>Utilisateurs</span>
            </a>

            <a href="{{ route('roles.index') }}"
               class="sidebar-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                <i class="bi bi-person-badge"></i>
                <span>Rôles</span>
            </a>

            <a href="{{ route('directions.index') }}"
               class="sidebar-link {{ request()->routeIs('directions.*') ? 'active' : '' }}">
                <i class="bi bi-building"></i>
                <span>Directions</span>
            </a>

            <a href="{{ route('departements.index') }}"
               class="sidebar-link {{ request()->routeIs('departements.*') ? 'active' : '' }}">
                <i class="bi bi-diagram-3"></i>
                <span>Départements</span>
            </a>
           

        </div>

        {{-- Contenu principal --}}
        <div class="main-wrapper">

            <div class="top-bar">
                <div class="top-bar-title">
                    @yield('page-title', 'ANPTIC')
                </div>

                {{-- Avatar, nom et le dropdown de deconnexion --}}
                <div class="dropdown">
                    <div class="top-bar-user" data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->prenom, 0, 1)) }}{{ strtoupper(substr(auth()->user()->nom, 0, 1)) }}
                        </div>
                        <span>{{ auth()->user()->prenom }} {{ auth()->user()->nom }}</span>
                        <i class="bi bi-chevron-down" style="font-size:11px; color:#aaa;"></i>
                    </div>

                    <ul class="dropdown-menu dropdown-menu-end" style="min-width:200px; font-size:13px;">
                        <li class="px-3 py-2">
                            <div style="font-weight:600; color:#2c3e50;">
                                {{ auth()->user()->prenom }} {{ auth()->user()->nom }}
                            </div>
                            <div style="font-size:11px; color:#888;">
                                {{ auth()->user()->email }}
                            </div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Se déconnecter
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="page-body">
                @yield('content')
            </div>

        </div>


        <!-- Bootstrap JS pour la liste déroulante-->

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

        @yield('scripts')

    </body>
</html>