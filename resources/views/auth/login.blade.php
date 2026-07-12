<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PilotixaMi — Connexion</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #105e49;
            --primary-light: #167e65;
            --bg: #f4f6f8;
            --border: #e5e7eb;
            --text: #1f2937;
            --muted: #6b7280;
            --danger: #dc2626;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg);
            color: var(--text);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-x: hidden;
        }

        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(30px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-6px); }
        }
        .anim-brand   { animation: fadeSlideUp 0.7s ease-out both; }
        .anim-card    { animation: fadeSlideUp 0.7s ease-out 0.15s both; }
        .anim-back    { animation: fadeIn 0.6s ease-out 0.5s both; }
        .login-card { animation: fadeSlideUp 0.7s ease-out 0.15s both; }
        .back-link  { animation: fadeIn 0.6s ease-out 0.5s both; }
        .login-brand { animation: fadeSlideUp 0.7s ease-out both; }
        .login-container {
            width: 100%;
            max-width: 480px; 
        }
        .login-brand {
            text-align: center;
            margin-bottom: 28px;
        }
        .brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 60px;
            height: 60px;
            background: var(--primary);
            border-radius: 14.5px;
            margin-bottom: 16px;
            box-shadow: 0 8px 20px rgba(16, 94, 73, 0.15);
        }
        .brand-icon i {
            color: #fff;
            font-size: 2rem;
        }
        .brand-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 2.2rem;
            color: var(--primary);
            letter-spacing: -2px;
            text-transform: uppercase;
        }
        .brand-tagline {
            color: var(--muted);
            font-size: 0.95rem;
            margin-top: 4px;
            font-weight: 500;
        }
        .login-card {
            background: #ffffff;
            border-radius: 20px;
            padding: 44px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.03);
            border: 1px solid var(--border);
        }
        .login-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 1.4rem;
            margin-bottom: 8px;
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
            font-size: 0.95rem;
            border: 1px solid var(--border);
            border-radius: 8px;
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
            margin-bottom: 28px;
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
            padding: 13px;
            border: none;
            border-radius: 8px;
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
            margin-top: 28px;
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
        @media (max-width: 480px) {
            body { padding: 12px; align-items: flex-start; padding-top: 80px; }
            .login-card { padding: 24px 20px; }
            .login-title { font-size: 1.2rem; }
            .brand-name { font-size: 1.4rem !important; }
            .back-link a { font-size: 0.82rem; }
            .login-card .brand-name { font-size: 1.8rem; }
            .login-brand .brand-name { font-size: 2.2rem; }
        }
    </style>
</head>
<body>

    <!-- Logo Top Left -->
    <a href="{{ url('/') }}" style="position: absolute; top: 30px; left: 40px; text-decoration: none; display: flex; align-items: flex-start; gap: 0; z-index: 10;">
        <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix Logo" style="height: 72px; width: 72px; object-fit: contain; border-radius: 16px;">
    </a>

    <div class="login-container">

        <!-- Formulaire -->
        <div class="login-card">
            <h2 class="login-title">Connexion</h2>
            <p class="login-subtitle">Renseignez vos accès pour accéder à la plateforme.</p>

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
        </div>

        <!-- Retour accueil public -->
        <div class="back-link">
            <a href="{{ url('/') }}">
                <i class="bi bi-arrow-left"></i> Retour au site de présentation
            </a>
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
</body>
</html>
