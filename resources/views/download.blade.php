<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Téléchargements PILOTIX — Application Mobile & Desktop</title>
    <meta name="description" content="Téléchargez PILOTIX pour Android (APK), iOS (Expo Go) et Ordinateur (Windows, Mac, Linux). Solution complète de gestion commerciale.">
    <meta name="keywords" content="télécharger PILOTIX, application mobile gestion, apk android PILOTIX, PILOTIX desktop, expo go PILOTIX">
    <meta name="robots" content="index, follow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #105e49;
            --primary-light: #167e65;
            --primary-dark: #0a3d2d;
            --secondary: #ea8d22;
            --bg: #f8fafc;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e2e8f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #fff; color: var(--text); overflow-x: hidden; }

        /* ─── TOP BANNER ─── */
        .top-banner {
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center; gap: 16px;
            padding: 8px 5%; font-size: 0.82rem; font-weight: 500;
            white-space: nowrap; overflow: hidden; flex-wrap: nowrap;
        }
        .top-banner a { color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; }
        .top-banner a:hover { opacity: .85; }
        .top-banner span { opacity: .3; }
        @media (max-width: 576px) {
            .top-banner { font-size: 0.72rem; gap: 8px; padding: 6px 10px; overflow-x: auto; justify-content: flex-start; }
            .top-banner a { font-size: 0.72rem; gap: 4px; }
        }

        /* ─── NAV ─── */
        nav {
            position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center;
            padding: 0 5%; min-height: 68px;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            justify-content: space-between;
        }
        .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; flex-shrink: 0; }
        .nav-logo img { height: 48px; width: 48px; object-fit: contain; border-radius: 12px; }
        .nav-logo-text { font-family: 'Space Grotesk', sans-serif; font-size: 1.4rem; font-weight: 800; color: var(--primary-dark); }
        
        .nav-links-pub { display: flex; align-items: center; gap: 28px; margin-left: auto; }
        .nav-links-pub a { color: var(--text); text-decoration: none; font-weight: 600; font-size: 0.92rem; white-space: nowrap; transition: color 0.2s; }
        .nav-links-pub a:hover, .nav-links-pub a.nav-active { color: var(--primary); }
        .btn-nav { background: var(--primary); color: #fff !important; padding: 9px 22px; border-radius: 8px; font-weight: 700; font-size: 0.9rem; transition: all .2s; text-decoration: none; }
        .btn-nav:hover { background: var(--primary-light) !important; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(16,94,73,.3); }

        /* ─── HAMBURGER BUTTON (MOBILE) ─── */
        .hamburger {
            display: none; flex-direction: column; justify-content: center; align-items: center;
            gap: 5px; width: 40px; height: 40px; background: none; border: none; cursor: pointer;
            padding: 4px; border-radius: 8px; transition: background 0.2s; z-index: 200;
        }
        .hamburger:hover { background: rgba(16,94,73,0.06); }
        .hamburger span {
            display: block; width: 24px; height: 2.5px; background: var(--text);
            border-radius: 99px; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .hamburger.open span:nth-child(1) { transform: translateY(7.5px) rotate(45deg); }
        .hamburger.open span:nth-child(2) { opacity: 0; width: 0; }
        .hamburger.open span:nth-child(3) { transform: translateY(-7.5px) rotate(-45deg); }

        /* ─── MOBILE DRAWER MENU ─── */
        .mobile-menu {
            display: none; flex-direction: column; position: fixed;
            top: 68px; left: 0; right: 0; background: rgba(255,255,255,0.98);
            backdrop-filter: blur(20px); border-bottom: 1px solid rgba(0,0,0,0.06);
            padding: 20px 5% 28px; gap: 4px; z-index: 99;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08); transform: translateY(-10px);
            opacity: 0; transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .mobile-menu.active { display: flex; transform: translateY(0); opacity: 1; }
        .mobile-menu a {
            color: var(--text); text-decoration: none; font-weight: 500; font-size: 1rem;
            padding: 12px 16px; border-radius: 10px; display: flex; align-items: center; gap: 12px;
            transition: background 0.2s, color 0.2s;
        }
        .mobile-menu a:hover { background: rgba(16,94,73,0.06); color: var(--primary); }
        .mobile-menu .btn-nav-mobile { background: var(--primary); color: #fff !important; margin-top: 8px; justify-content: center; font-weight: 700; border-radius: 10px; }
        .mobile-menu-divider { height: 1px; background: rgba(0,0,0,0.06); margin: 6px 0; }

        @media (max-width: 991px) {
            .nav-links-pub { display: none; }
            .hamburger { display: flex; }
        }

        /* ─── HERO SECTION ─── */
        .hero {
            background: linear-gradient(135deg, #0a3d2d 0%, #105e49 50%, #167e65 100%);
            padding: 90px 5% 75px; text-align: center; color: #fff; position: relative; overflow: hidden;
        }
        .hero::before {
            content: ''; position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .hero-inner { max-width: 860px; margin: 0 auto; position: relative; z-index: 1; }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(234,141,34,.15); border: 1px solid rgba(234,141,34,.4);
            color: var(--secondary); padding: 6px 18px; border-radius: 30px;
            font-weight: 700; font-size: 0.78rem; letter-spacing: 1px;
            text-transform: uppercase; margin-bottom: 24px;
        }
        .hero-title {
            font-family: 'Space Grotesk', sans-serif; font-size: 3.2rem; font-weight: 900;
            color: #fff; line-height: 1.25; letter-spacing: -1.5px; margin-bottom: 20px;
        }
        .arc-underline { position: relative; display: inline-block; color: var(--secondary); }
        .arc-underline svg { position: absolute; bottom: -8px; left: 0; width: 100%; height: 12px; pointer-events: none; }
        .hero-desc { font-size: 1.15rem; color: rgba(255,255,255,0.85); line-height: 1.7; max-width: 680px; margin: 0 auto 36px; }

        .hero-devices-pills { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; }
        .device-pill {
            background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.18);
            padding: 8px 18px; border-radius: 50px; color: #fff; font-size: 0.85rem; font-weight: 600;
            display: flex; align-items: center; gap: 8px; backdrop-filter: blur(10px);
        }

        /* ─── MAIN CONTENT SECTIONS ─── */
        .dl-section { padding: 90px 5%; background: #fbfcfd; position: relative; }
        .dl-container { max-width: 1200px; margin: 0 auto; }

        .section-label {
            display: inline-block; color: var(--primary); font-weight: 800; font-size: 0.85rem;
            text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px;
            background: rgba(16, 94, 73, 0.1); padding: 6px 16px; border-radius: 50px;
        }
        .section-title {
            font-family: 'Space Grotesk', sans-serif; font-size: 2.5rem; font-weight: 900;
            color: var(--text); letter-spacing: -1px; margin-bottom: 16px;
        }
        .section-sub { font-size: 1.05rem; color: var(--muted); max-width: 600px; line-height: 1.6; margin-bottom: 50px; }

        /* ─── DOWNLOAD CARDS GRID (ALIGN ITEMS START TO PREVENT STRETCHING OTHER CARDS) ─── */
        .dl-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 32px;
            align-items: start;
        }
        .dl-card {
            background: #ffffff; border: 1px solid rgba(0,0,0,0.06); border-radius: 24px;
            padding: 36px 32px; box-shadow: 0 10px 30px rgba(0,0,0,0.04);
            transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.35s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.35s ease;
            display: flex; flex-direction: column; position: relative; overflow: hidden;
        }
        .dl-card:hover {
            transform: translateY(-8px); box-shadow: 0 25px 50px rgba(16,94,73,0.12);
            border-color: rgba(16,94,73,0.25);
        }
        .dl-card.highlight {
            border: 2px solid var(--primary); box-shadow: 0 15px 40px rgba(16,94,73,0.1);
        }

        .dl-card-badge {
            align-self: flex-start; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;
            letter-spacing: 1px; padding: 4px 12px; border-radius: 50px; margin-bottom: 20px;
        }
        .badge-green { background: rgba(16, 94, 73, 0.1); color: var(--primary); }
        .badge-orange { background: rgba(234, 141, 34, 0.12); color: var(--secondary); }
        .badge-blue { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .badge-purple { background: rgba(124, 58, 237, 0.1); color: #7c3aed; }

        .dl-card-icon {
            width: 56px; height: 56px; border-radius: 16px; display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; margin-bottom: 20px; flex-shrink: 0;
        }
        .icon-green { background: rgba(16, 94, 73, 0.08); color: var(--primary); }
        .icon-orange { background: rgba(234, 141, 34, 0.1); color: var(--secondary); }
        .icon-blue { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
        .icon-purple { background: rgba(124, 58, 237, 0.1); color: #7c3aed; }

        .dl-card-title {
            font-family: 'Space Grotesk', sans-serif; font-size: 1.4rem; font-weight: 800;
            color: var(--text); margin-bottom: 10px; line-height: 1.3;
        }
        .dl-card-desc { font-size: 0.95rem; color: var(--muted); line-height: 1.6; margin-bottom: 24px; flex-grow: 1; }

        .dl-feature-list { list-style: none; margin-bottom: 30px; display: flex; flex-direction: column; gap: 12px; }
        .dl-feature-item { display: flex; align-items: flex-start; gap: 10px; font-size: 0.9rem; color: #475569; }
        .dl-feature-item i {
            display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px;
            border-radius: 50%; font-size: 0.75rem; flex-shrink: 0; margin-top: 1px;
        }

        .btn-card-action {
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 14px 24px; border-radius: 12px; font-weight: 800; font-size: 0.95rem;
            text-decoration: none; border: none; cursor: pointer; transition: all 0.3s ease;
            font-family: 'Space Grotesk', sans-serif;
        }
        .btn-action-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff;
            box-shadow: 0 8px 20px rgba(16, 94, 73, 0.25);
        }
        .btn-action-primary:hover {
            transform: translateY(-2px); box-shadow: 0 12px 28px rgba(16, 94, 73, 0.35); background: var(--primary-light);
        }
        .btn-action-secondary {
            background: #f1f5f9; color: var(--text); border: 1px solid var(--border);
        }
        .btn-action-secondary:hover { background: #e2e8f0; color: var(--primary); }

        .btn-action-purple {
            background: #7c3aed; color: #fff; box-shadow: 0 8px 20px rgba(124, 58, 237, 0.25);
        }
        .btn-action-purple:hover { background: #6d28d9; transform: translateY(-2px); }

        /* ─── EXPANDABLE PANELS IN CARDS ─── */
        .card-expand-content {
            display: none; margin-top: 24px; padding-top: 24px; border-top: 1px dashed var(--border);
            animation: fadeIn 0.3s ease;
        }
        .card-expand-content.active { display: block; }

        .os-tabs-nav { display: flex; gap: 6px; background: #f1f5f9; padding: 4px; border-radius: 10px; margin-bottom: 16px; }
        .os-tab-btn {
            flex: 1; padding: 8px 12px; border-radius: 8px; border: none; background: transparent;
            font-weight: 700; font-size: 0.8rem; color: var(--muted); cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .os-tab-btn.active { background: #fff; color: var(--primary); box-shadow: 0 2px 6px rgba(0,0,0,0.06); }

        .os-tab-panel { display: none; }
        .os-tab-panel.active { display: block; }

        .steps-mini-list { display: flex; flex-direction: column; gap: 10px; margin-top: 14px; }
        .step-mini-item { display: flex; gap: 10px; font-size: 0.82rem; color: #64748b; line-height: 1.5; }
        .step-mini-num {
            width: 20px; height: 20px; border-radius: 50%; background: rgba(16,94,73,0.1); color: var(--primary);
            font-weight: 800; font-size: 0.7rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;
        }

        .qr-box-wrap {
            display: flex; gap: 16px; align-items: center; background: #f8fafc; padding: 14px; border-radius: 14px; border: 1px solid var(--border); margin-top: 14px;
        }
        .qr-image-holder { width: 90px; height: 90px; border-radius: 10px; background: #fff; overflow: hidden; border: 1px solid var(--border); flex-shrink: 0; }
        .qr-image-holder img { width: 100%; height: 100%; object-fit: cover; }

        /* ─── FOOTER (CLEAN WHITE BACKGROUND LIKE WELCOME) ─── */
        footer { background: #ffffff; border-top: 1px solid #e5e7eb; color: var(--text); padding: 60px 5% 28px; }
        .footer-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 40px; flex-wrap: wrap; margin-bottom: 48px; }
        .footer-brand { flex: 1; min-width: 240px; }
        .footer-brand .logo-wrap { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; }
        .footer-brand .logo-wrap img { height: 48px; width: 48px; object-fit: contain; border-radius: 12px; }
        .footer-brand p { color: #6b7280; font-size: 0.9rem; line-height: 1.7; max-width: 320px; }
        .footer-links { min-width: 160px; }
        .footer-links h5 { font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #374151; margin-bottom: 18px; }
        .footer-links ul { list-style: none; display: flex; flex-direction: column; gap: 12px; }
        .footer-links ul li a { color: #374151; text-decoration: none; font-size: 0.9rem; transition: color .2s; display: inline-flex; align-items: center; gap: 8px; }
        .footer-links ul li a:hover { color: var(--primary); }
        .footer-bottom { border-top: 1px solid #e5e7eb; padding-top: 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; color: #6b7280; font-size: 0.85rem; }

        @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

        @media (max-width: 768px) {
            .hero-title { font-size: 2.2rem; }
            .section-title { font-size: 2rem; }
            .dl-grid { grid-template-columns: 1fr; }
            .footer-top { flex-direction: column; gap: 32px; }
        }
    </style>
</head>
<body>

    <!-- TOP BANNER -->
    <div class="top-banner">
        <span>⚡ Nouveau :</span> PILOTIX Mobile est disponible !
        <a href="#mobile-apps">Télécharger l'APK Android <i class="bi bi-arrow-right"></i></a>
    </div>

    <!-- NAV BAR -->
    <nav>
        <a href="{{ url('/') }}" class="nav-logo">
            <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX Logo">
            <span class="nav-logo-text">PILOTIX</span>
        </a>

        <div class="nav-links-pub">
            <a href="{{ url('/') }}">Accueil</a>
            <a href="{{ url('/') }}#features">Fonctionnalités</a>
            <a href="{{ url('/') }}#tarifs">Tarifs</a>
            <a href="{{ route('partenaires') }}">Partenaires</a>
            <a href="{{ route('download') }}" class="nav-active">Téléchargements</a>
            @if(Auth::check())
                <a href="{{ route('dashboard') }}" class="btn-nav">Tableau de bord</a>
            @else
                <a href="{{ route('login') }}" class="btn-nav">Se connecter</a>
            @endif
        </div>

        <button class="hamburger" id="hamburgerBtn" aria-label="Menu Mobile">
            <span></span>
            <span></span>
            <span></span>
        </button>
    </nav>

    <!-- MOBILE MENU DRAWER -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="{{ url('/') }}"><i class="bi bi-house"></i> Accueil</a>
        <a href="{{ url('/') }}#features"><i class="bi bi-stars"></i> Fonctionnalités</a>
        <a href="{{ url('/') }}#tarifs"><i class="bi bi-tags"></i> Tarifs</a>
        <a href="{{ route('partenaires') }}"><i class="bi bi-people"></i> Programme Partenaires</a>
        <a href="{{ route('download') }}"><i class="bi bi-download"></i> Téléchargements</a>
        <div class="mobile-menu-divider"></div>
        @if(Auth::check())
            <a href="{{ route('dashboard') }}" class="btn-nav-mobile"><i class="bi bi-speedometer2"></i> Mon Tableau de bord</a>
        @else
            <a href="{{ route('login') }}" class="btn-nav-mobile"><i class="bi bi-box-arrow-in-right"></i> Se connecter</a>
        @endif
    </div>

    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-inner">
            <div class="hero-badge">
                <i class="bi bi-download"></i> Espace Téléchargements
            </div>
            <h1 class="hero-title">
                PILOTIX sur tous vos <span class="arc-underline">appareils<svg viewBox="0 0 200 20" fill="none"><path d="M 0 12 Q 100 2 200 12" stroke="currentColor" stroke-width="4" fill="transparent"/></svg></span>
            </h1>
            <p class="hero-desc">
                Installez PILOTIX sur votre smartphone Android, iPhone ou ordinateur de bureau et gérez vos stocks, ventes et livraisons en temps réel partout où vous allez.
            </p>
            <div class="hero-devices-pills">
                <span class="device-pill"><i class="bi bi-android2" style="color:#4ade80;"></i> Android (APK)</span>
                <span class="device-pill"><i class="bi bi-phone" style="color:#a78bfa;"></i> iPhone (Expo Go)</span>
                <span class="device-pill"><i class="bi bi-windows" style="color:#38bdf8;"></i> Windows PC</span>
                <span class="device-pill"><i class="bi bi-apple" style="color:#e2e8f0;"></i> macOS</span>
                <span class="device-pill"><i class="bi bi-ubuntu" style="color:#fb923c;"></i> Linux</span>
            </div>
        </div>
    </section>

    <!-- SECTION MOBILES -->
    <section class="dl-section" id="mobile-apps">
        <div class="dl-container">
            <span class="section-label"><i class="bi bi-phone"></i> Applications Mobiles</span>
            <h2 class="section-title">PILOTIX dans votre poche</h2>
            <p class="section-sub">Accédez à votre gestion commerciale depuis votre smartphone avec nos solutions adaptées Android et iOS.</p>

            <div class="dl-grid">

                <!-- CARD 1: ANDROID APK -->
                <div class="dl-card highlight" id="card-android">
                    <span class="dl-card-badge badge-green"><i class="bi bi-star-fill"></i> Recommandé Android</span>
                    <div class="dl-card-icon icon-green">
                        <i class="bi bi-android2"></i>
                    </div>
                    <h3 class="dl-card-title">Android — App Native (.APK)</h3>
                    <p class="dl-card-desc">L'application mobile native complète pour smartphone et tablette Android (8.0+). Installation directe rapide.</p>
                    
                    <ul class="dl-feature-list">
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(16,94,73,0.1); color:var(--primary);"></i> Scan de photos et caméras fluide</li>
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(16,94,73,0.1); color:var(--primary);"></i> Fonctionne hors-ligne &amp; synchro auto</li>
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(16,94,73,0.1); color:var(--primary);"></i> Téléchargement direct sans Play Store</li>
                    </ul>

                    <a href="{{ asset('downloads/pilotix-latest.apk') }}" download class="btn-card-action btn-action-primary">
                        <i class="bi bi-download"></i> Télécharger l'APK (~115 Mo)
                    </a>
                </div>

                <!-- CARD 2: EXPO GO (UNIVERSEL) -->
                <div class="dl-card" id="card-expo">
                    <span class="dl-card-badge badge-purple"><i class="bi bi-stars"></i> iOS &amp; Test Direct</span>
                    <div class="dl-card-icon icon-purple">
                        <i class="bi bi-phone"></i>
                    </div>
                    <h3 class="dl-card-title">Expo Go — iPhone &amp; Android</h3>
                    <p class="dl-card-desc">Idéal pour utiliser PILOTIX sur iPhone (iOS) ou tester sur Android instantanément sans installer de fichier APK.</p>
                    
                    <ul class="dl-feature-list">
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(124,58,237,0.1); color:#7c3aed;"></i> Compatible 100% avec iPhone et iPad</li>
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(124,58,237,0.1); color:#7c3aed;"></i> Lancement via l'application officielle Expo</li>
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(124,58,237,0.1); color:#7c3aed;"></i> Simple scan de QR Code pour démarrer</li>
                    </ul>

                    <button class="btn-card-action btn-action-purple" onclick="toggleExpand('expand-expo')">
                        <i class="bi bi-qr-code-scan"></i> Afficher le QR Code &amp; Guide
                    </button>

                    <div class="card-expand-content" id="expand-expo">
                        <div class="os-tabs-nav">
                            <button class="os-tab-btn active" onclick="switchOsTab('expo-android', this)"><i class="bi bi-android2"></i> Android</button>
                            <button class="os-tab-btn" onclick="switchOsTab('expo-ios', this)"><i class="bi bi-apple"></i> iPhone</button>
                        </div>
                        
                        <div id="expo-android" class="os-tab-panel active">
                            <a href="https://play.google.com/store/apps/details?id=host.exp.exponent" target="_blank" class="btn-card-action btn-action-secondary" style="font-size:0.85rem; padding:10px;">
                                <i class="bi bi-google-play"></i> 1. Télécharger Expo Go (Play Store)
                            </a>
                        </div>
                        <div id="expo-ios" class="os-tab-panel">
                            <a href="https://apps.apple.com/app/expo-go/id982107779" target="_blank" class="btn-card-action btn-action-secondary" style="font-size:0.85rem; padding:10px;">
                                <i class="bi bi-apple"></i> 1. Télécharger Expo Go (App Store)
                            </a>
                        </div>

                        <div class="qr-box-wrap">
                            <div class="qr-image-holder">
                                <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=exp%3A%2F%2Fexp.host%2F%40PILOTIX%2FPILOTIX%3Frelease-channel%3Ddefault" alt="QR Code Expo PILOTIX">
                            </div>
                            <div style="flex:1;">
                                <p style="font-size:0.8rem; font-weight:700; color:var(--text); margin-bottom:4px;">2. Scannez le QR Code</p>
                                <p style="font-size:0.75rem; color:var(--muted); line-height:1.4;">Ouvrez l'appareil photo sur iPhone ou l'app Expo Go sur Android.</p>
                                <a href="exp://exp.host/@PILOTIX/PILOTIX?release-channel=default" style="display:inline-block; font-size:0.78rem; font-weight:700; color:#7c3aed; margin-top:6px; text-decoration:none;">Lancer directement →</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CARD 3: IOS APP STORE -->
                <div class="dl-card" id="card-ios">
                    <span class="dl-card-badge badge-orange"><i class="bi bi-clock-history"></i> Bientôt disponible</span>
                    <div class="dl-card-icon icon-orange">
                        <i class="bi bi-apple"></i>
                    </div>
                    <h3 class="dl-card-title">iPhone — App Store Native</h3>
                    <p class="dl-card-desc">L'application officielle PILOTIX iOS est actuellement en cours de préparation pour l'App Store Apple.</p>
                    
                    <ul class="dl-feature-list">
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(234,141,34,0.1); color:var(--secondary);"></i> Distribution officielle Apple</li>
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(234,141,34,0.1); color:var(--secondary);"></i> Mises à jour automatiques App Store</li>
                    </ul>

                    <button class="btn-card-action btn-action-secondary" disabled style="opacity:0.6; cursor:not-allowed;">
                        <i class="bi bi-clock-history"></i> Bientôt disponible
                    </button>
                </div>

            </div>
        </div>
    </section>

    <!-- SECTION DESKTOP -->
    <section class="dl-section" style="background:#fff;" id="desktop-apps">
        <div class="dl-container">
            <span class="section-label"><i class="bi bi-laptop"></i> Applications Ordinateur</span>
            <h2 class="section-title">PILOTIX sur votre PC / Mac</h2>
            <p class="section-sub">Installez l'application de bureau dédiée sur votre poste de travail ou caisse enregistreuse.</p>

            <div class="dl-grid">

                <!-- DESKTOP ELECTRON CARD -->
                <div class="dl-card" id="card-desktop">
                    <span class="dl-card-badge badge-orange"><i class="bi bi-clock-history"></i> Bientôt disponible</span>
                    <div class="dl-card-icon icon-blue">
                        <i class="bi bi-laptop"></i>
                    </div>
                    <h3 class="dl-card-title">Application Desktop (Windows / Mac / Linux)</h3>
                    <p class="dl-card-desc">Application de bureau complète basée sur Electron pour votre poste de gestion ou caisse enregistreuse.</p>
                    
                    <ul class="dl-feature-list">
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(37,99,235,0.1); color:#2563eb;"></i> Exécution autonome hors navigateur</li>
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(37,99,235,0.1); color:#2563eb;"></i> Support imprimantes de caisse &amp; scanners</li>
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(37,99,235,0.1); color:#2563eb;"></i> Windows (.exe), Mac (.dmg) &amp; Linux (.AppImage)</li>
                    </ul>

                    <button class="btn-card-action btn-action-secondary" disabled style="opacity:0.6; cursor:not-allowed;">
                        <i class="bi bi-clock-history"></i> Bientôt disponible
                    </button>
                </div>

                <!-- UPDATE CARD -->
                <div class="dl-card" id="card-update">
                    <span class="dl-card-badge badge-orange"><i class="bi bi-clock-history"></i> Bientôt disponible</span>
                    <div class="dl-card-icon icon-orange">
                        <i class="bi bi-arrow-down-circle"></i>
                    </div>
                    <h3 class="dl-card-title">Mise à jour PC Existante</h3>
                    <p class="dl-card-desc">Fichier de mise à jour rapide pour les installations de bureau existantes.</p>
                    
                    <ul class="dl-feature-list">
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(234,141,34,0.1); color:var(--secondary);"></i> Patch léger d'actualisation</li>
                        <li class="dl-feature-item"><i class="bi bi-check" style="background:rgba(234,141,34,0.1); color:var(--secondary);"></i> Conserve l'intégralité des données locales</li>
                    </ul>

                    <button class="btn-card-action btn-action-secondary" disabled style="opacity:0.6; cursor:not-allowed; margin-top:auto;">
                        <i class="bi bi-clock-history"></i> Bientôt disponible
                    </button>
                </div>

            </div>
        </div>
    </section>

    <!-- FOOTER (EXACT SAME WHITE FOOTER AS WELCOME.BLADE.PHP) -->
    <footer>
        <div class="footer-top">
            <div class="footer-brand">
                <div style="margin-bottom:10px;">
                    <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX Logo" style="height: 56px; width: 56px; object-fit: contain; border-radius: 12px;">
                </div>
                <p style="color: #6b7280;">Solution de gestion commerciale multi-Dépôt ou Magasin pour les PME d'Afrique de l'Ouest.</p>
            </div>
            <div class="footer-links">
                <h5 style="color: #374151;">Fonctionnalités</h5>
                <ul>
                    <li><a href="{{ url('/') }}#features" style="color: #374151;"><i class="bi bi-receipt"></i>Ventes &amp; Facturation</a></li>
                    <li><a href="{{ url('/') }}#features" style="color: #374151;"><i class="bi bi-box-seam"></i>Stock &amp; Arrivages</a></li>
                    <li><a href="{{ url('/') }}#features" style="color: #374151;"><i class="bi bi-truck"></i>Livraisons</a></li>
                    <li><a href="{{ url('/') }}#features" style="color: #374151;"><i class="bi bi-cash-stack"></i>Dettes Clients</a></li>
                </ul>
            </div>
            <div class="footer-links">
                <h5 style="color: #374151;">Accès</h5>
                <ul>
                    @if (Auth::check())
                        <li><a href="{{ route('dashboard') }}" style="color: #374151;"><i class="bi bi-speedometer2"></i>Tableau de bord</a></li>
                    @else
                        <li><a href="{{ route('login') }}" style="color: #374151;"><i class="bi bi-box-arrow-in-right"></i>Se connecter</a></li>
                    @endif
                </ul>
            </div>
            <div class="footer-links">
                <h5 style="color: #374151;">Contact</h5>
                <ul>
                    <li><a href="tel:+2290146862536" style="color: #374151;"><i class="bi bi-telephone"></i> +229 01 46 86 25 36</a></li>
                    <li><a href="mailto:pilotixcontact@gmail.com" style="color: #374151;"><i class="bi bi-envelope"></i> pilotixcontact@gmail.com</a></li>
                    <li><a href="{{ route('partenaires') }}" style="color: #374151;"><i class="bi bi-people"></i> Partenariat</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom" style="border-top: 1px solid #e5e7eb; color: #6b7280;">
            <span>&copy; {{ date('Y') }} PILOTIX — Gestion commerciale multi-Dépôt ou Magasin</span>
            <span><a href="{{ route('conditions') }}" style="color:inherit;">Conditions</a> · <a href="{{ route('confidentialite') }}" style="color:inherit;">Confidentialité</a> · <a href="{{ route('partenaires') }}" style="color:inherit;">Partenariat</a></span>
        </div>
    </footer>

    <script>
        // Hamburger Menu Toggle
        const hamburgerBtn = document.getElementById('hamburgerBtn');
        const mobileMenu = document.getElementById('mobileMenu');

        if(hamburgerBtn && mobileMenu) {
            hamburgerBtn.addEventListener('click', () => {
                hamburgerBtn.classList.toggle('open');
                mobileMenu.classList.toggle('active');
            });
        }

        // Expandable Panels
        function toggleExpand(id) {
            const content = document.getElementById(id);
            if(content) {
                content.classList.toggle('active');
            }
        }

        // OS Tab Switchers
        function switchOsTab(panelId, btnEl) {
            const parent = btnEl.closest('.card-expand-content');
            if(!parent) return;

            parent.querySelectorAll('.os-tab-btn').forEach(btn => btn.classList.remove('active'));
            parent.querySelectorAll('.os-tab-panel').forEach(panel => panel.classList.remove('active'));

            btnEl.classList.add('active');
            const targetPanel = parent.querySelector('#' + panelId);
            if(targetPanel) {
                targetPanel.classList.add('active');
            }
        }

        // Auto platform detection to highlight / open relevant content
        (function() {
            const ua = navigator.userAgent || '';
            const isAndroid = /Android/i.test(ua);
            const isIOS = /iPhone|iPad|iPod/i.test(ua);
            const isWin = /Windows/i.test(ua);
            const isMac = /Macintosh|Mac OS/i.test(ua);

            if(isAndroid) {
                const card = document.getElementById('card-android');
                if(card) card.classList.add('highlight');
            } else if(isIOS) {
                const card = document.getElementById('card-expo');
                if(card) {
                    card.classList.add('highlight');
                    toggleExpand('expand-expo');
                }
            } else if(isWin || isMac) {
                const card = document.getElementById('card-desktop');
                if(card) {
                    card.classList.add('highlight');
                    toggleExpand('expand-desktop');
                }
            }
        })();
    </script>
</body>
</html>
