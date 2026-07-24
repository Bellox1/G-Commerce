<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page introuvable — PILOTRIX</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #105e49;
            --primary-light: #167e65;
            --text: #1f2937;
            --muted: #6b7280;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #fff; color: var(--text); min-height: 100vh; display: flex; flex-direction: column; }

        nav {
            display: flex; align-items: center;
            padding: 0 5%; min-height: 68px;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .nav-logo { display: flex; align-items: center; text-decoration: none; flex-shrink: 0; }
        .nav-logo img { height: 56px; width: 56px; object-fit: contain; border-radius: 12px; }

        .error-page {
            flex: 1; display: flex; align-items: center; justify-content: center;
            padding: 60px 5%; text-align: center;
        }
        .error-code {
            font-size: 7rem; font-weight: 900; color: var(--text);
            line-height: 1; letter-spacing: -3px; margin-bottom: 12px;
        }
        .error-title {
            font-size: 1.5rem; font-weight: 800;
            color: var(--text); margin-bottom: 10px;
        }
        .error-desc {
            font-size: 1rem; color: var(--muted); line-height: 1.6; margin-bottom: 32px;
        }
        .error-actions { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn-home {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 32px; background: var(--primary); color: #fff; border-radius: 12px;
            font-weight: 800; font-size: 0.95rem; text-decoration: none; transition: all .2s;
        }
        .btn-home:hover { background: var(--primary-light); transform: translateY(-2px); box-shadow: 0 8px 24px rgba(16,94,73,0.25); }
        .btn-back {
            display: inline-flex; align-items: center; gap: 8px;
            padding: 14px 32px; background: rgba(16,94,73,0.06); color: var(--primary);
            border-radius: 12px; font-weight: 800; font-size: 0.95rem; text-decoration: none;
            transition: all .2s; cursor: pointer; border: none;
        }
        .btn-back:hover { background: rgba(16,94,73,0.12); }

        @media (max-width: 768px) {
            .error-code { font-size: 4.5rem; }
            .error-title { font-size: 1.2rem; }
        }
    </style>
</head>
<body>
    <nav>
        <a href="/" class="nav-logo">
            <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix Logo">
        </a>
    </nav>

    <div class="error-page">
        <div class="error-inner">
            <div class="error-code">404</div>
            <h1 class="error-title">Page introuvable</h1>
            <p class="error-desc">La page que vous recherchez n'existe pas ou a été déplacée.</p>
            <div class="error-actions">
                <a href="/" class="btn-home">Retour à l'accueil</a>
                <button onclick="history.back()" class="btn-back">Page précédente</button>
            </div>
        </div>
    </div>
</body>
</html>
