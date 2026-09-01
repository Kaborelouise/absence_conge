<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Connexion - ANPTIC</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            min-height: 100vh;
        }

        .login-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ===== Panneau visuel gauche ===== */
        .login-visual {
            flex: 1;
            background: linear-gradient(160deg, #1B384F 0%, #12283a 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 60px 40px;
            position: relative;
            overflow: hidden;
        }

        /* Formes flottantes animées */
        .shape {
            position: absolute;
            border-radius: 50%;
            background: rgba(66, 165, 245, 0.08);
            animation: floatShape 10s ease-in-out infinite;
        }

        .shape-1 {
            width: 380px;
            height: 380px;
            top: -120px;
            right: -100px;
            animation-duration: 12s;
        }

        .shape-2 {
            width: 260px;
            height: 260px;
            bottom: -90px;
            left: -70px;
            background: rgba(66, 165, 245, 0.06);
            animation-duration: 9s;
            animation-delay: 1s;
        }

        .shape-3 {
            width: 140px;
            height: 140px;
            top: 20%;
            left: 8%;
            background: rgba(255, 255, 255, 0.04);
            animation-duration: 7s;
            animation-delay: 0.5s;
        }

        @keyframes floatShape {
            0%, 100% { transform: translate(0, 0) scale(1); }
            50% { transform: translate(20px, -25px) scale(1.05); }
        }

        .login-logo {
            width: 100px;
            height: 100px;
            object-fit: contain;
            margin-bottom: 20px;
            position: relative;
            z-index: 2;
            background: white;
            border-radius: 20px;
            padding: 10px;
        }

        /* ===== Carrousel ===== */
        .carousel-zone {
            position: relative;
            z-index: 2;
            width: 100%;
            max-width: 420px;
            min-height: 230px;
        }

        .carousel-slide {
            position: absolute;
            top: 0; left: 0; right: 0;
            opacity: 0;
            transform: translateY(14px);
            transition: opacity 0.6s ease, transform 0.6s ease;
            pointer-events: none;
            text-align: center;
        }

        .carousel-slide.active {
            opacity: 1;
            transform: translateY(0);
            pointer-events: auto;
        }

        .carousel-slide i {
            font-size: 40px;
            color: #42A5F5;
            margin-bottom: 16px;
            display: inline-block;
        }

        .carousel-slide h2 {
            font-weight: 700;
            font-size: 23px;
            margin-bottom: 10px;
        }

        .carousel-slide p {
            color: rgba(255,255,255,0.65);
            font-size: 14px;
            max-width: 360px;
            margin: 0 auto;
        }

        .carousel-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 26px;
            position: relative;
            z-index: 2;
        }

        .carousel-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.25);
            transition: all 0.3s ease;
        }

        .carousel-dot.active {
            background: #42A5F5;
            width: 22px;
            border-radius: 4px;
        }

        /* ===== Panneau formulaire droit ===== */
        .login-form-panel {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #f5f7fa;
            padding: 40px;
        }

        .login-card {
            width: 100%;
            max-width: 380px;
            animation: fadeInUp 0.6s ease both;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card h3 {
            font-weight: 700;
            color: #1B384F;
            margin-bottom: 4px;
        }

        .login-card .subtitle {
            color: #8a94a3;
            font-size: 14px;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            font-size: 13.5px;
            color: #2c3e50;
        }

        .form-control {
            border-radius: 8px;
            padding: 10px 14px;
            border: 1px solid #dfe4ea;
        }

        .form-control:focus {
            border-color: #1B384F;
            box-shadow: 0 0 0 0.2rem rgba(27, 56, 79, 0.1);
        }

        .btn-login {
            background-color: #1B384F;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 11px;
            font-weight: 600;
            width: 100%;
            transition: background-color 0.2s ease;
        }

        .btn-login:hover {
            background-color: #142a3b;
            color: white;
        }

        .forgot-link {
            font-size: 13px;
            color: #6c7a89;
            text-decoration: none;
        }

        .forgot-link:hover {
            color: #1B384F;
        }

        @media (max-width: 900px) {
            .login-visual { display: none; }
            .login-form-panel { flex: 1 1 100%; }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">

        {{-- Panneau visuel avec carrousel --}}
        <div class="login-visual">
            <div class="shape shape-1"></div>
            <div class="shape shape-2"></div>
            <div class="shape shape-3"></div>

            <img src="{{ asset('images/logo_anptic.png') }}" alt="ANPTIC" class="login-logo">

            <div class="carousel-zone" id="carouselZone">
                <div class="carousel-slide active">
                    <i class="bi bi-calendar-check"></i>
                    <h2>Gérez vos congés</h2>
                    <p>Soumettez vos demandes de congé administratif et suivez leur traitement en temps réel.</p>
                </div>
                <div class="carousel-slide">
                    <i class="bi bi-person-x"></i>
                    <h2>Autorisations d'absence</h2>
                    <p>Déclarez vos absences et obtenez rapidement l'avis de vos responsables hiérarchiques.</p>
                </div>
                <div class="carousel-slide">
                    <i class="bi bi-clock-history"></i>
                    <h2>Suivi en temps réel</h2>
                    <p>Consultez l'état d'avancement de chaque demande, du dépôt jusqu'à la validation finale.</p>
                </div>
                <div class="carousel-slide">
                    <i class="bi bi-bell"></i>
                    <h2>Notifications instantanées</h2>
                    <p>Recevez une alerte dès qu'une action est requise ou qu'une décision est prise.</p>
                </div>
            </div>

            <div class="carousel-dots" id="carouselDots"></div>
        </div>

        {{-- Formulaire --}}
        <div class="login-form-panel">
            <div class="login-card">
                <h3>Connexion</h3>
                <p class="subtitle">Accédez à votre espace personnel</p>

                @if (session('status'))
                    <div class="alert alert-success py-2 small">{{ session('status') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger py-2 small">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input id="email" type="email" name="email"
                               class="form-control"
                               value="{{ old('email') }}"
                               required autofocus autocomplete="username">
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input id="password" type="password" name="password"
                               class="form-control"
                               required autocomplete="current-password">
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                            <label class="form-check-label small" for="remember_me" style="color:#6c7a89;">
                                Se souvenir de moi
                            </label>
                        </div>

                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-link">
                                Mot de passe oublié ?
                            </a>
                        @endif
                    </div>

                    <button type="submit" class="btn btn-login">
                        Se connecter
                    </button>
                </form>
            </div>
        </div>

    </div>

    <script>
        const slides = document.querySelectorAll('#carouselZone .carousel-slide');
        const dotsContainer = document.getElementById('carouselDots');
        let current = 0;

        slides.forEach((_, i) => {
            const dot = document.createElement('div');
            dot.className = 'carousel-dot' + (i === 0 ? ' active' : '');
            dotsContainer.appendChild(dot);
        });
        const dots = document.querySelectorAll('.carousel-dot');

        function goToSlide(index) {
            slides[current].classList.remove('active');
            dots[current].classList.remove('active');
            current = index;
            slides[current].classList.add('active');
            dots[current].classList.add('active');
        }

        setInterval(() => {
            const next = (current + 1) % slides.length;
            goToSlide(next);
        }, 4000);
    </script>

</body>
</html>