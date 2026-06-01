<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>@yield('title', 'ANPTIC') - Gestion des demandes d'autorisation d'absence et de congé</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">
          <i class="bi bi-house"></i> 
         

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"
          rel="stylesheet">
    <style>
         {
            box-sizing: border-box;

          }
         body {
            margin: O; 
            padding: 0;
            font-family:'Segoe UI', Tahoma, sans-serif;
            background-color: #f0f2f5;
            min-height: 100vh;
          }

          .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 230px;
            height: 100vh;
            background-color: #1e2a3a;
            overflow-y: auto;
            Z-index: 1000;
            display: flex;
            flex-direction: column;
        
          }

          .sidebar-brand {
            padding: 18px;
            border-bottom: 1px solid rgba(255, 255, 0.88);
            display: flex;
            align-items: center;
            gap:12px;
            /*espace entre l'icone et le texte*/ 
            text-decoration: none;
        }

        .sidebar-brand-icon{
            width: 38px;
            height: 38px;
            background: linear-gradient(135deg, #1976D2, #42A5F5);
            border-radius: 8px; 
            display: flex;
            align-items: center;
            color: white;
            font-weight: 700; 
            font-size:16px;
            flex-shrink: 0;
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
            font-size: 10px;
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
            transition: all 0.2s ease;
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
            /* Hauteur fixe pour le header */
            border-bottom: 1px solid #e8ecf0;
            display: flex;
            justify-content: space-between;
            /* Titre à gauche, l'utilisateur droite */
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 999;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .top-bar-title {
            font-size: 14px;
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

        .top-bar-user i {
            font-size: 22px;
            color: #1976D2;
            /* Icône bleue pour l'utilisateur */
        }

        /* Corps de la page */
        .page-body {
            padding: 24px;
            flex: 1;
            /* flex 1 : prend tout l'espace vertical restant */
        }

        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.07);
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
            padding: 14px 20px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.06);
            font-weight: 600;
        }

        .badge-statut {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            display: inline-block;
        }

        .badge-en_attente {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-en_cours {
            background-color: #cfe2ff;
            color: #084298;
        }

        .badge-validee {
            background-color: #d1e7dd;
            color: #0a3622;
        }
        .badge-rejetee {
            background-color: #f8d7da;
            color: #842029;
        }

        .badge-compilee {
            background-color: #e2d9f3;
            color: #6f42c1;
        }

        /* Boutons d'action dans les tableaux */
        .btn-action {
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 5px;
        }
        </style>

        {{-- Styles propre à chaque page
         Chaque vue peut ajouter ses propres styles ici
         Ex: @section('styles') ... @endsection --}}
    @yield('styles')
</head>
<body>
    <div class="sidebar">
        <a href="{{ route('accueil') }}" class="sidebar-brand">
        {{-- C'est un lien vers l'accueil
             Cliquer sur le logo ramène à l'accueil --}}
        <div class="sidebar-brand-icon">A</div>
        <div class="sidebar-brand-text">
            <span class="brand-name">ANPTIC</span>
            <span class="brand-subtitle">
                Gestion des congés<br>et des autorisations absences
            </span>
        </div>
    </a>

    <div class="sidebar-section-title">Menu principal</div>*
    {{-- Accueil
         request()->routeIs('accueil') :
         va Vérifier si on est actuellement sur la route 'accueil'
         Si oui on ajoute la classe CSS 'active' (lien surligné)
         Si non on a pas de classe (lien normal) --}}
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
        <i class="bi bi-calendar2-check"></i>
        <span>Demande de congé</span>
    </a>

    <a href="{{ route('demande_jouissances.index') }}"
       class="sidebar-link {{ request()->routeIs('demande_jouissances.*') ? 'active' : '' }}">
        <i class="bi bi-calendar2-check"></i>
        <span>Demande de jouissance</span>
    </a>

     <div class="sidebar-section-title">Administration</div>

     <a href="{{ route('utilisateurs.index') }}"
       class="sidebar-link {{ request()->routeIs('utilisateurss.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span>Utilisateurs</span>
    </a>

    <a href="{{ route('roles.index') }}"
       class="sidebar-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
        <i class="bi bi-people"></i>
        <span>Rôles</span>
    </a>

     <a href="{{ route('directions.index') }}"
       class="sidebar-link {{ request()->routeIs('directions.*') ? 'active' : '' }}">
        <i class="bi bi-building"></i>
        <span>Directions</span>
    </a>

     <a href="{{ route('departements.index') }}"
       class="sidebar-link {{ request()->routeIs('departements.*') ? 'active' : '' }}">
        <i class="bi bi-building"></i>
        <span>Departements</span>
    </a>

       <div style="margin-top: auto; padding: 16px; border-top: 1px solid rgba(255,255,255,0.08)">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    style="background:none; border:none; cursor:pointer;
                           color:rgba(255,255,255,0.5); font-size:13px;
                           display:flex; align-items:center; gap:8px; padding:0">
                <i class="bi bi-box-arrow-left"></i>
                <span>Se déconnecter</span>
            </button>
        </form>
    </div>

    </body>
</html>




        {{-- <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html> --}}
