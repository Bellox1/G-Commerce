<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilotix — Réinitialiser le mot de passe</title>
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
        .login-card { animation: fadeSlideUp 0.7s ease-out 0.15s both; }
        .back-link  { animation: fadeIn 0.6s ease-out 0.5s both; }
        @media (max-width: 480px) {
            body { padding: 12px; align-items: flex-start; padding-top: 80px; }
            .login-card { padding: 24px 20px; }
            .login-title { font-size: 1.2rem; }
            .btn-submit { font-size: 0.9rem; padding: 12px; }
            .back-link a { font-size: 0.82rem; }
        }
        .login-container {
            width: 100%;
            max-width: 480px; 
        }
        .brand-name {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 2.2rem;
            color: var(--primary);
            letter-spacing: -2px;
            text-transform: uppercase;
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
            margin-bottom: 24px;
            line-height: 1.5;
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
            padding: 12px;
            border-radius: 8px;
            font-size: 0.85rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
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
    </style>
</head>
<body>

    <a href="{{ url('/') }}" style="position: absolute; top: 30px; left: 40px; text-decoration: none; display: flex; align-items: flex-start; gap: 0; z-index: 10;">
        <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix Logo" style="height: 72px; width: 72px; object-fit: contain; border-radius: 16px;">
    </a>

    <div class="login-container">
        <div class="login-card">
            <h2 class="login-title">Réinitialiser le mot de passe</h2>
            <p class="login-subtitle">Renseignez le code OTP reçu par e-mail et votre nouveau mot de passe.</p>

            @if($errors->any())
                <div class="alert">
                    <i class="bi bi-exclamation-circle-fill"></i>
                    <span>{{ $errors->first() }}</span>
                </div>
            @endif

            @if(session('status'))
                <div class="alert alert-success">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}">
                @csrf
                
                <div class="form-group">
                    <label class="form-label">Adresse e-mail</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $email) }}" placeholder="votreemail@Pilotix.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Code OTP de validation</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code', $code) }}" placeholder="Ex: 582910" required maxlength="6" style="letter-spacing: 2px; font-weight: 700; font-size: 1.1rem; text-align: center;">
                </div>

                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <div class="input-wrapper">
                        <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required style="padding-right: 48px;">
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password', 'toggleIcon1')">
                            <i id="toggleIcon1" class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmer le nouveau mot de passe</label>
                    <div class="input-wrapper">
                        <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" placeholder="••••••••" required style="padding-right: 48px;">
                        <button type="button" class="password-toggle" onclick="togglePasswordVisibility('password_confirmation', 'toggleIcon2')">
                            <i id="toggleIcon2" class="bi bi-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-check-circle-fill"></i> Enregistrer le nouveau mot de passe
                </button>
            </form>
        </div>

        <div class="back-link">
            <a href="{{ route('login') }}">
                <i class="bi bi-arrow-left"></i> Retour à la connexion
            </a>
        </div>
    </div>

    <script>
        function togglePasswordVisibility(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const toggleIcon = document.getElementById(iconId);
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('bi-eye');
                toggleIcon.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('bi-eye-slash');
                toggleIcon.classList.add('bi-eye');
            }
        }
    </script>
</body>
</html>
