<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Nouveau mot de passe - ANPTIC</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: 'Figtree', 'Segoe UI', Tahoma, sans-serif;
            background-color: #f0f2f5;
            min-height: 100vh;
        }

        .login-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* ---------- PANNEAU GAUCHE ---------- */
        .login-side {
            display: none;
            width: 50%;
            background-color: #1B384F;
            color: white;
            position: relative;
            overflow: hidden;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 4rem;
        }

        @media (min-width: 992px) {
            .login-side { display: flex; }
        }

        .login-side::before {
            content: "";
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(255, 255, 255, 0.06) 1.4px, transparent 1.4px);
            background-size: 24px 24px;
        }

        .login-side-content {
            position: relative;
            z-index: 2;
            text-align: center;
            max-width: 380px;
        }

        .login-illustration {
            margin-bottom: 2.5rem;
        }

        .login-side h2 {
            font-size: 1.6rem;
            font-weight: 700;
            line-height: 1.4;
            margin-bottom: 0.85rem;
            letter-spacing: -0.01em;
        }

        .login-side p {
            color: rgba(255, 255, 255, 0.55);
            font-size: 0.95rem;
            line-height: 1.6;
        }

        .login-side-footer {
            position: absolute;
            bottom: 2.5rem;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 0.78rem;
            color: rgba(255, 255, 255, 0.3);
            z-index: 2;
        }

        /* ---------- PANNEAU DROIT ---------- */
        .login-main {
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
        }

        .login-logo-wrap {
            display: flex;
            justify-content: center;
            margin-bottom: 1.75rem;
        }

        .login-logo {
            width: 68px;
            height: 68px;
            object-fit: contain;
        }

        .login-title {
            text-align: center;
            font-size: 1.6rem;
            font-weight: 700;
            color: #1B384F;
            margin-bottom: 0.4rem;
            letter-spacing: -0.01em;
        }

        .login-subtitle {
            text-align: center;
            color: #7A8A99;
            font-size: 0.92rem;
            margin-bottom: 2.25rem;
            line-height: 1.5;
        }

        .form-label {
            font-size: 0.83rem;
            font-weight: 600;
            color: #1B384F;
            margin-bottom: 0.4rem;
        }

        .form-control {
            border-radius: 11px;
            border: 1.5px solid #e2e6ea;
            padding: 0.7rem 0.9rem;
            font-size: 0.92rem;
            background-color: #FAFBFC;
        }

        .form-control:focus {
            border-color: #42A5F5;
            background-color: white;
            box-shadow: 0 0 0 4px rgba(66, 165, 245, 0.13);
        }

        .input-icon-wrap {
            position: relative;
        }

        .input-icon-wrap i {
            position: absolute;
            top: 50%;
            left: 0.95rem;
            transform: translateY(-50%);
            color: #a0aab5;
            font-size: 1rem;
        }

        .input-icon-wrap .form-control {
            padding-left: 2.5rem;
        }

        .field-hint {
            font-size: 0.78rem;
            color: #a0aab5;
            margin-top: 0.35rem;
        }

        .field-error {
            font-size: 0.8rem;
            color: #c0392b;
            margin-top: 0.35rem;
        }

        .status-banner {
            background-color: #d1e7dd;
            color: #0a3622;
            font-size: 0.85rem;
            padding: 0.6rem 0.9rem;
            border-radius: 10px;
            margin-bottom: 1.25rem;
        }

        .btn-login {
            background-color: #1B384F;
            border: none;
            color: white;
            font-weight: 600;
            font-size: 0.95rem;
            padding: 0.75rem;
            border-radius: 11px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            transition: background-color 0.2s ease, transform 0.1s ease;
        }

        .btn-login:hover {
            background-color: #234863;
            color: white;
        }

        .btn-login:active {
            transform: scale(0.99);
        }
    </style>
</head>
<body>

    <div class="login-wrapper">

        {{-- Panneau gauche --}}
        <div class="login-side">
            <div class="login-side-content">

                <div class="login-illustration">
                    <svg width="140" height="140" viewBox="0 0 140 140" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <circle cx="70" cy="70" r="68" stroke="rgba(255,255,255,0.1)" stroke-width="1"/>
                        <path d="M70 30 c-16 0 -29 13 -29 29 c0 20 29 51 29 51 s29 -31 29 -51 c0 -16 -13 -29 -29 -29 z"
                              stroke="#42A5F5" stroke-width="2" fill="none" stroke-linejoin="round"/>
                        <circle cx="70" cy="59" r="9" stroke="#42A5F5" stroke-width="2" fill="none"/>
                        <path d="M62 66 l-4 6 M78 66 l4 6" stroke="#42A5F5" stroke-width="2" stroke-linecap="round"/>
                    </svg>
                </div>

                <h2>Un dernier pas avant d'accéder à votre espace</h2>
                <p>Pour la sécurité de votre compte, définissez un mot de passe personnel avant de continuer.</p>

            </div>

            <div class="login-side-footer">
                ANPTIC — Agence Nationale de Promotion des TIC
            </div>
        </div>

        {{-- Panneau droit --}}
        <div class="login-main">
            <div class="login-card">

                <div class="login-logo-wrap">
                    <img src="{{ asset('images/logo_anptic.png') }}" alt="Logo ANPTIC" class="login-logo">
                </div>

                <div class="login-title">Définir votre mot de passe</div>
                <div class="login-subtitle">Pour des raisons de sécurité, vous devez définir un nouveau mot de passe avant de continuer.</div>

                @if (session('status'))
                    <div class="status-banner">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('mot_de_passe.changer_obligatoire.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="password" class="form-label">Nouveau mot de passe</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-lock"></i>
                            <input type="password" name="password" id="password" class="form-control"
                                   placeholder="••••••••" required autofocus autocomplete="new-password">
                        </div>
                        @error('password')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                        <div class="input-icon-wrap">
                            <i class="bi bi-lock-fill"></i>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control"
                                   placeholder="••••••••" required autocomplete="new-password">
                        </div>
                        @error('password_confirmation')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn-login">
                        Définir le mot de passe
                        <i class="bi bi-arrow-right"></i>
                    </button>

                </form>

            </div>
        </div>

    </div>

</body>
</html>