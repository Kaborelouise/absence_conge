<!DOCTYPE html>

{{-- ce fichier est le squelette de toutes les pages apres la connexion, il contient le sidebar, le navbar, le footer --}}
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
         {{-- viewport:permet a l'application de s'adapter a taille de l'éccran utilisé --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">
        {{-- csrf_token est utilisé pour la sécurité des requêtes JavaScript --}}

        <title>@yield('title', 'ANPTIC') - Gestion des demandes d'autorisation d'absence et de congé</title>
         {{-- @yield('title'): permet a chaque vue de definir son titre avec @section('title', 'mon titre'), si aucun titre n'est defini on prend ANPTIC par defaut--}}

        <!-- Polices -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
         {{-- Boostrap 5 css pour les composants visuels(cartes, boutons, tableaux)--}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
          rel="stylesheet">
          <i class="bi bi-house"></i> 
         
     {{-- Boostrap icons: biblithèque d'icons--}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css"
          rel="stylesheet">

     {{-- styles qui s'appliquent a toutes les pages --}}
    <style>
         * {
            box-sizing: border-box;

          }
         body {
            margin: 0; 
            padding: 0;
            font-family:'Segoe UI', Tahoma, sans-serif;
            background-color: #f0f2f5;
            min-height: 100vh;
          }
          

           /* sidebar gauche*/
          .sidebar {
            position: fixed; /*fixed pour dire que le sidebar reste statique quand on scrolle */
            top: 0; /*top et left: collé a qauche */
            left: 0;
            width: 230px;
            height: 100vh;
            background-color: #1e2a3a;
            overflow-y: auto;
            Z-index: 1000;
            display: flex;
            flex-direction: column;
        
          }
         
          /*Logo de l'ANPTIC */
          .sidebar-brand {
            padding: 18px 16px;
            /*ligne de séparation*/
            border-bottom: 1px solid rgba(255, 255,255, 0.88);

            display: flex;
            align-items: center;
            gap:12px;
            /*espace entre l'icone et le texte*/ 
            text-decoration: none;
            /* Pas de soulignement car c'est un lien */
        }
        
        /* Icône du logo */
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
            /* L'icône ne rétrécit pas*/
        }

        .sidebar-brand-text {
            color: white;
            line-height: 1.3;
        }

        /* nom ANPTIC */
        .sidebar-brand-text .brand-name {
            font-size: 14px;
            font-weight: 700;
            display: block;
        }

         .sidebar-brand-text .brand-subtitle {
            font-size: 10px;
            color: rgba(255, 255, 255, 0.5);
        }

            /* titre des sections, Menu principal, Demandes, Administration */
        .sidebar-section-title {
            padding: 16px 16px 6px;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
             /* Texte en MAJUSCULES */
            letter-spacing: 1.2px;
            /* Espace entre les lettres */
            color: rgba(255, 255, 255, 0.3);
            /* Gris clair discret */
        }

        /* les liens de navigation*/
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 16px;
            color: rgba(255, 255, 255, 0.6);
            /* Blanc semi-transparent par défaut */
            text-decoration: none;
            font-size: 13px;
            transition: all 0.2s ease;
            /* Animation fluide 0.2 seconde au survol */
            border-left: 3px solid transparent;
            /* Bordure gauche transparente devient bleu sur le lien actif */
           
        }

        .sidebar-link:hover {
            color: rgba(255, 255, 255, 0.9);
            background-color: rgba(255, 255, 255, 0.06);
            /* Fond légèrement plus clair au survol au survol */
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
            /* Largeur fixe pour aligner les textes des liens */
        }

        /* contenu principal a droite de sidebar */
        .main-wrapper {
            margin-left: 230px;
            min-height: 100vh;
            
            display: flex;
            flex-direction: column;
            width: calc(100% - 230px); /*pour forcer la largeur */
            /* header, contenu et footer empilés verticalement */
        }

        /* Navbar en haut*/
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

        /* titre de la page dans la navbar */
        .top-bar-title {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
        }

        .top-bar-user {
            /* zone utilisateur a droite de la navbar  */
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
            /* flex 1 : prend tout l'espace vertical restant ainsi le footer reste toujours en bas */
        }

            /* Cartes utilisées pour les détails des demandes et les formulaires */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 1px 6px rgba(146, 39, 39, 0.07);
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important; 
             /* !important force bosstrap a rescpecter le border radius */
            padding: 14px 20px;
            border-bottom:  rgba(0, 0, 0, 0.06);
            font-weight: 600;
        }

        .badge-statut {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 500;
            display: inline-block;
        }

        /* couleurs des badges selon le statut */
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

         .footer {
            background-color: white;
            border-top: 1px solid #e8ecf0;
            padding: 14px 24px;
            text-align: center;
            font-size: 12px;
            color: #888;
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
            
            <div class="sidebar-brand-text">
                <span class="brand-name">ANPTIC</span>
                <span class="brand-subtitle">
                    Gestion des congés<br>
                    et des autorisations absences
                </span>
            </div>
        </a>

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

        {{-- <div style="margin-top:auto;padding:16px;border-top:1px solid rgba(255,255,255,.08)">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        style="background:none;border:none;cursor:pointer;
                               color:rgba(255,255,255,.6);
                               display:flex;align-items:center;gap:8px;">
                    <i class="bi bi-box-arrow-left"></i>
                    <span>Se déconnecter</span>
                </button>
            </form>
        </div> --}}

    </div>

    <!-- Contenu principal -->
    <div class="main-wrapper">

        <div class="top-bar">
            <div class="top-bar-title">
                @yield('page-title', 'ANPTIC')
            </div>
        </div>

        <div class="page-body">
            @yield('content')
        </div>

    </div>

    @yield('scripts')

</body>
</html>
