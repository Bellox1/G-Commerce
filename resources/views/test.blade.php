<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comptes de Test — OdjaMi</title>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #105e49;
            --secondary: #ea8d22;
            --bg: #f8fafc;
            --card-bg: #ffffff;
            --text: #1e293b;
            --muted: #64748b;
            --border: #e2e8f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            padding: 40px 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .container {
            max-width: 900px;
            width: 100%;
        }
        .header {
            text-align: center;
            margin-bottom: 32px;
        }
        .header h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            color: var(--primary);
            font-size: 2.2rem;
            margin-bottom: 8px;
        }
        .header p {
            color: var(--muted);
            font-size: 1rem;
        }
        .grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }
        .card {
            background: var(--card-bg);
            border-radius: 16px;
            padding: 24px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: var(--border);
        }
        .card.superadmin::before { background: #3b82f6; }
        .card.admin::before { background: var(--primary); }
        .card.vendeur::before { background: var(--secondary); }
        .card.magasinier::before { background: #8b5cf6; }
        .card.livreur::before { background: #ec4899; }

        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }
        .superadmin .role-badge { background: #eff6ff; color: #1d4ed8; }
        .admin .role-badge { background: #ecfdf5; color: var(--primary); }
        .vendeur .role-badge { background: #fff7ed; color: var(--secondary); }
        .magasinier .role-badge { background: #f5f3ff; color: #6d28d9; }
        .livreur .role-badge { background: #fdf2f8; color: #be185d; }

        .card h3 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.2rem;
            margin-bottom: 16px;
        }
        .credential-box {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 16px;
            font-family: monospace;
            font-size: 0.9rem;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .credential-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .credential-val {
            font-weight: 750;
            color: #0f172a;
        }
        .copy-btn {
            background: none;
            border: none;
            color: var(--muted);
            cursor: pointer;
            padding: 2px 6px;
            border-radius: 4px;
            transition: all 0.2s;
        }
        .copy-btn:hover {
            color: var(--primary);
            background: rgba(16, 94, 73, 0.1);
        }
        .btn-connect {
            display: block;
            text-align: center;
            background: var(--primary);
            color: #fff;
            padding: 10px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            transition: background 0.2s;
        }
        .btn-connect:hover {
            background: #0d4c3b;
        }
        .footer-action {
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <h1>Comptes de Test OdjaMi</h1>
        <p>Utilisez ces identifiants pour tester les différents rôles de la plateforme.</p>
    </div>

    <div class="grid">
        <!-- Super Admin -->
        <div class="card superadmin">
            <div>
                <span class="role-badge">Super Admin</span>
                <h3>Super Admin Plateforme</h3>
                <div class="credential-box">
                    <div class="credential-item">
                        <span>Email:</span>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <span class="credential-val">superadmin@estock.com</span>
                            <button class="copy-btn" onclick="copyText('superadmin@estock.com', this)"><i class="bi bi-copy"></i></button>
                        </div>
                    </div>
                    <div class="credential-item">
                        <span>Mot de passe:</span>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <span class="credential-val">saimous2026</span>
                            <button class="copy-btn" onclick="copyText('saimous2026', this)"><i class="bi bi-copy"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{ route('login') }}" class="btn-connect">Se connecter</a>
        </div>

        <!-- Admin -->
        <div class="card admin">
            <div>
                <span class="role-badge">Admin</span>
                <h3>Admin SAÏMOUS (Matinou)</h3>
                <div class="credential-box">
                    <div class="credential-item">
                        <span>Email:</span>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <span class="credential-val">admin@saimous.bj</span>
                            <button class="copy-btn" onclick="copyText('admin@saimous.bj', this)"><i class="bi bi-copy"></i></button>
                        </div>
                    </div>
                    <div class="credential-item">
                        <span>Mot de passe:</span>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <span class="credential-val">saimous2026</span>
                            <button class="copy-btn" onclick="copyText('saimous2026', this)"><i class="bi bi-copy"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{ route('login') }}" class="btn-connect">Se connecter</a>
        </div>

        <!-- Vendeur -->
        <div class="card vendeur">
            <div>
                <span class="role-badge">Vendeur</span>
                <h3>Vendeur Principal</h3>
                <div class="credential-box">
                    <div class="credential-item">
                        <span>Email:</span>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <span class="credential-val">vendeur@saimous.bj</span>
                            <button class="copy-btn" onclick="copyText('vendeur@saimous.bj', this)"><i class="bi bi-copy"></i></button>
                        </div>
                    </div>
                    <div class="credential-item">
                        <span>Mot de passe:</span>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <span class="credential-val">saimous2026</span>
                            <button class="copy-btn" onclick="copyText('saimous2026', this)"><i class="bi bi-copy"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{ route('login') }}" class="btn-connect" style="background: var(--secondary);">Se connecter</a>
        </div>

        <!-- Magasinier -->
        <div class="card magasinier">
            <div>
                <span class="role-badge">Magasinier</span>
                <h3>Magasinier Dépôt</h3>
                <div class="credential-box">
                    <div class="credential-item">
                        <span>Email:</span>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <span class="credential-val">magasinier@saimous.bj</span>
                            <button class="copy-btn" onclick="copyText('magasinier@saimous.bj', this)"><i class="bi bi-copy"></i></button>
                        </div>
                    </div>
                    <div class="credential-item">
                        <span>Mot de passe:</span>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <span class="credential-val">saimous2026</span>
                            <button class="copy-btn" onclick="copyText('saimous2026', this)"><i class="bi bi-copy"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{ route('login') }}" class="btn-connect" style="background: #8b5cf6;">Se connecter</a>
        </div>

        <!-- Livreur -->
        <div class="card livreur">
            <div>
                <span class="role-badge">Livreur</span>
                <h3>Livreur RICCI</h3>
                <div class="credential-box">
                    <div class="credential-item">
                        <span>Email:</span>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <span class="credential-val">livreur@saimous.bj</span>
                            <button class="copy-btn" onclick="copyText('livreur@saimous.bj', this)"><i class="bi bi-copy"></i></button>
                        </div>
                    </div>
                    <div class="credential-item">
                        <span>Mot de passe:</span>
                        <div style="display:flex; align-items:center; gap:4px;">
                            <span class="credential-val">saimous2026</span>
                            <button class="copy-btn" onclick="copyText('saimous2026', this)"><i class="bi bi-copy"></i></button>
                        </div>
                    </div>
                </div>
            </div>
            <a href="{{ route('login') }}" class="btn-connect" style="background: #ec4899;">Se connecter</a>
        </div>
    </div>

    <div class="footer-action">
        <a href="/" style="color: var(--primary); text-decoration: none; font-weight: 600;"><i class="bi bi-arrow-left"></i> Retour à l'accueil</a>
    </div>
</div>

<script>
    function copyText(text, btn) {
        navigator.clipboard.writeText(text).then(() => {
            const icon = btn.querySelector('i');
            icon.className = 'bi bi-check-lg';
            icon.style.color = '#16a34a';
            setTimeout(() => {
                icon.className = 'bi bi-copy';
                icon.style.color = '';
            }, 1500);
        });
    }
</script>

</body>
</html>
