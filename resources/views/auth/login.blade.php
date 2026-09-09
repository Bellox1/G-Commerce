<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Connexion — PILOTIX</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #105e49;
            --primary-light: #167e65;
            --bg: #ffffff;
            --border: #e5e7eb;
            --text: #1f2937;
            --muted: #6b7280;
            --danger: #dc2626;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body {
            height: 100%;
            width: 100%;
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: #ffffff;
            overflow-x: hidden;
        }

        .login-page {
            display: flex;
            min-height: 100vh;
            width: 100vw;
        }

        /* Gauche : 60% plein écran, image couvre tout */
        .login-left {
            width: 60%;
            background: #ffffff;
            position: relative;
            display: flex;
            align-items: stretch;
            justify-content: center;
            overflow: hidden;
            padding: 0;
            margin: 0;
            min-height: 100vh;
        }
        .login-left-img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center center;
            display: block;
            position: absolute;
            top: 0;
            left: 0;
        }

        /* Droite : 40% Formulaire */
        .login-right {
            width: 40%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 40px 50px;
            background: #ffffff;
        }
        .login-box {
            width: 100%;
            max-width: 420px;
        }
        .login-title {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 800;
            font-size: 1.6rem;
            color: var(--text);
            margin-bottom: 6px;
        }
        .login-subtitle {
            font-size: 0.9rem;
            color: var(--muted);
            margin-bottom: 28px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            display: block;
            font-weight: 700;
            font-size: 0.8rem;
            color: var(--text);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }
        .input-wrapper {
            position: relative;
            display: flex;
            align-items: center;
        }
        .form-control {
            width: 100%;
            padding: 13px 16px;
            font-size: 16px;
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: inherit;
            outline: none;
            background: #f8fafc;
            transition: all 0.2s;
        }
        .form-control:focus {
            border-color: var(--primary);
            background: #fff;
            box-shadow: 0 0 0 3px rgba(16, 94, 73, 0.08);
        }
        .password-toggle {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            cursor: pointer;
            color: var(--muted);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            padding: 4px;
        }
        .password-toggle:hover {
            color: var(--text);
        }
        .form-checkbox {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 24px;
        }
        .form-checkbox input {
            width: 17px;
            height: 17px;
            cursor: pointer;
        }
        .form-checkbox label {
            font-size: 0.9rem;
            color: var(--muted);
            cursor: pointer;
            user-select: none;
        }
        .btn-submit {
            width: 100%;
            background: var(--primary);
            color: white;
            padding: 14px;
            border: none;
            border-radius: 10px;
            font-weight: 700;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background: var(--primary-light);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(16, 94, 73, 0.25);
        }
        .alert {
            background: #fee2e2;
            color: var(--danger);
            border: 1px solid #fecaca;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-success {
            background: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }
        .back-link {
            text-align: center;
            margin-top: 24px;
        }
        .back-link a {
            color: var(--primary);
            font-weight: 700;
            font-size: 0.92rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .back-link a:hover {
            color: var(--primary-light);
        }

        @media (max-width: 900px) {
            .login-page {
                flex-direction: column;
            }
            .login-left {
                display: none;
            }
            .login-right {
                width: 100%;
                padding: 32px 20px;
                min-height: 100vh;
            }
        }
    </style>
</head>
<body>

    <div class="login-page">

        <!-- Gauche 60% : Image Arrivage PC -->
        <div class="login-left">
            <img src="{{ asset('Visuel/pc-arrivage.png') }}" alt="PILOTIX Arrivages & Dépôts PC" class="login-left-img">
        </div>

        <!-- Droite 40% : Formulaire -->
        <div class="login-right">
            <div class="login-box">
                <div style="margin-bottom: 28px;">
                    <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX" style="height: 65px; width: auto; object-fit: contain; margin-bottom: 14px;">
                    <h1 class="login-title">Connexion à votre Espace</h1>
                    <p class="login-subtitle">Connectez-vous pour piloter vos magasins, arrivages et stocks.</p>
                </div>

                @if($errors->any())
                    <div class="alert">
                        <i class="bi bi-exclamation-circle-fill"></i>
                        <span>{{ $errors->first() }}</span>
                    </div>
                @endif

                @if(session('success'))
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('status'))
                    <div class="alert alert-success">
                        <i class="bi bi-info-circle-fill"></i>
                        <span>{{ session('status') }}</span>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.post') }}">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Adresse e-mail</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="votre Email" required autofocus>
                    </div>

                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <label class="form-label" style="margin-bottom: 0;">Mot de passe</label>
                            <a href="{{ route('password.request') }}" style="color: var(--primary); font-size: 0.8rem; font-weight: 600; text-decoration: none;">Mot de passe oublié ?</a>
                        </div>
                        <div class="input-wrapper">
                            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required style="padding-right: 48px;">
                            <button type="button" class="password-toggle" onclick="togglePasswordVisibility()">
                                <i id="toggleIcon" class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-checkbox">
                        <input type="checkbox" name="remember" id="remember">
                        <label for="remember">Rester connecté</label>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="bi bi-box-arrow-in-right"></i> Se connecter
                    </button>
                </form>

                <!-- Retour accueil public -->
                <div class="back-link">
                    <a href="/" id="backLink">
                        <i class="bi bi-arrow-left"></i> Retour à l'accueil
                    </a>
                </div>

                <!-- Télécharger l'app PWA -->
                <div style="text-align:center; margin-top:20px;">
                    <button onclick="installPWA()" id="loginInstallBtn" style="display:none; background:var(--primary); color:#fff; border:none; padding:12px 24px; border-radius:10px; font-weight:700; font-size:.9rem; cursor:pointer; gap:8px; box-shadow:0 4px 14px rgba(16,94,73,.25);">
                        <i class="bi bi-download"></i> Télécharger l'app
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Script pour afficher/cacher le mot de passe -->
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            }
        }
    </script>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#105e49">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <script>
    var deferredPrompt = null;
    if (window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone) {
        document.getElementById('backLink').href = '{{ route("onboarding") }}';
    }
    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;
        var btn = document.getElementById('loginInstallBtn');
        if (btn) btn.style.display = 'inline-flex';
    });
    function installPWA() {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function() { deferredPrompt = null; });
    }
    </script>
</body>
</html>
