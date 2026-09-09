<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    <meta name="format-detection" content="telephone=no, date=no, address=no, email=no" />
    <title>PILOTIX — Logiciel de Gestion Commerciale Multi-Magasins</title>
    <meta name="description" content="PILOTIX : logiciel de gestion commerciale multi-magasins. Gérez vos ventes, stocks, clients, livraisons, arrivages et dettes en temps réel." />
    <link rel="shortcut icon" href="/PILOTIX-logo.png" type="image/png" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,400;14..32,500;14..32,600;14..32,700;14..32,800;14..32,900&family=Space+Grotesk:wght@600;700;800;900&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" />
    <style>
        /* ===== RESET & BASE ===== */
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        :root {
            --primary: #0d6b4e;
            --primary-dark: #08503a;
            --primary-light: #1a8b68;
            --secondary: #e68a2e;
            --secondary-light: #f5a94a;
            --bg: #f6f9f8;
            --surface: #ffffff;
            --text: #0b1a17;
            --text-secondary: #3d5a54;
            --text-muted: #7a9a93;
            --border: #dce9e5;
            --shadow-sm: 0 4px 12px rgba(11, 26, 23, 0.06);
            --shadow-md: 0 12px 40px rgba(11, 26, 23, 0.08);
            --shadow-lg: 0 24px 60px rgba(11, 26, 23, 0.12);
            --radius: 16px;
            --radius-lg: 24px;
            --transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        html {
            scroll-behavior: smooth;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            line-height: 1.6;
            -webkit-font-smoothing: antialiased;
        }
        a {
            text-decoration: none;
            color: inherit;
        }
        img {
            max-width: 100%;
            display: block;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ===== TOP BAR ===== */
        .top-bar {
            background: var(--primary-dark);
            color: #fff;
            padding: 6px 0;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 24px;
            flex-wrap: wrap;
        }
        .top-bar a {
            color: rgba(255, 255, 255, 0.85);
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: color 0.2s;
        }
        .top-bar a:hover {
            color: #fff;
        }
        .top-bar .divider {
            opacity: 0.25;
        }
        @media (max-width: 600px) {
            .top-bar {
                font-size: 0.7rem;
                gap: 12px;
                padding: 4px 12px;
            }
        }

        /* ===== NAVIGATION ===== */
        nav {
            position: sticky;
            top: 0;
            z-index: 100;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid var(--border);
            padding: 0 24px;
            height: 72px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .nav-logo {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 800;
            font-size: 1.2rem;
            color: var(--primary-dark);
        }
        .nav-logo img {
            height: 44px;
            width: 44px;
            border-radius: 10px;
            object-fit: contain;
        }
        .nav-links {
            display: flex;
            align-items: center;
            gap: 28px;
        }
        .nav-links a {
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-secondary);
            transition: color 0.2s;
            position: relative;
        }
        .nav-links a:hover {
            color: var(--primary);
        }
        .nav-links a.active {
            color: var(--primary);
        }
        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: -4px;
            left: 0;
            right: 0;
            height: 2.5px;
            background: var(--primary);
            border-radius: 4px;
        }
        .nav-cta {
            background: var(--primary);
            color: #fff !important;
            padding: 8px 20px;
            border-radius: 10px;
            font-weight: 700;
            transition: background 0.2s, transform 0.2s;
        }
        .nav-cta:hover {
            background: var(--primary-dark) !important;
            transform: translateY(-1px);
        }
        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            transition: background 0.2s;
        }
        .hamburger:hover {
            background: rgba(11, 26, 23, 0.04);
        }
        .hamburger span {
            display: block;
            width: 24px;
            height: 2.5px;
            background: var(--text);
            border-radius: 99px;
            transition: 0.3s;
        }
        .hamburger.open span:nth-child(1) {
            transform: translateY(7.5px) rotate(45deg);
        }
        .hamburger.open span:nth-child(2) {
            opacity: 0;
            width: 0;
        }
        .hamburger.open span:nth-child(3) {
            transform: translateY(-7.5px) rotate(-45deg);
        }
        .mobile-menu {
            display: none;
            position: fixed;
            top: 72px;
            left: 0;
            right: 0;
            background: rgba(255, 255, 255, 0.98);
            backdrop-filter: blur(20px);
            padding: 20px 24px 32px;
            border-bottom: 1px solid var(--border);
            flex-direction: column;
            gap: 4px;
            z-index: 99;
            transform: translateY(-12px);
            opacity: 0;
            transition: 0.35s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }
        .mobile-menu.active {
            display: flex;
            transform: translateY(0);
            opacity: 1;
            pointer-events: auto;
        }
        .mobile-menu a {
            padding: 12px 16px;
            border-radius: 10px;
            font-weight: 600;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.2s, color 0.2s;
        }
        .mobile-menu a:hover {
            background: rgba(11, 26, 23, 0.04);
            color: var(--primary);
        }
        .mobile-menu .mobile-cta {
            background: var(--primary);
            color: #fff !important;
            justify-content: center;
            margin-top: 8px;
        }
        .mobile-menu .mobile-cta:hover {
            background: var(--primary-dark);
        }
        .mobile-divider {
            height: 1px;
            background: var(--border);
            margin: 8px 0;
        }
        @media (max-width: 1180px) {
            .nav-links {
                gap: 16px;
            }
            .nav-links a {
                font-size: 0.82rem;
            }
        }
        @media (max-width: 1024px) {
            .nav-links {
                display: none;
            }
            .hamburger {
                display: flex;
            }
        }
        @media (max-width: 600px) {
            nav {
                padding: 0 16px;
                height: 64px;
            }
            .nav-logo {
                font-size: 1.05rem;
                gap: 8px;
            }
            .nav-logo img {
                height: 36px;
                width: 36px;
            }
            .mobile-menu {
                top: 64px;
                padding: 16px 20px 24px;
            }
        }

        /* ===== HERO ===== */
        .hero {
            padding: 80px 0 64px;
            background: linear-gradient(165deg, #eaf5f1 0%, #d9ede6 100%);
            position: relative;
            overflow: hidden;
        }
        .hero::after {
            content: '';
            position: absolute;
            right: -120px;
            top: -120px;
            width: 600px;
            height: 600px;
            border-radius: 50%;
            background: rgba(13, 107, 78, 0.06);
            pointer-events: none;
        }
        .hero .container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(13, 107, 78, 0.1);
            color: var(--primary);
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 6px 16px;
            border-radius: 100px;
            margin-bottom: 20px;
        }
        .hero h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 900;
            font-size: 3.2rem;
            line-height: 1.15;
            letter-spacing: -2px;
            color: var(--text);
            margin-bottom: 16px;
        }
        .hero h1 span {
            color: var(--primary);
        }
        .hero p {
            font-size: 1.05rem;
            color: var(--text-secondary);
            max-width: 480px;
            line-height: 1.7;
            margin-bottom: 32px;
        }
        .hero-actions {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
        }
        .btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: var(--primary);
            color: #fff;
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(13, 107, 78, 0.25);
        }
        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(13, 107, 78, 0.3);
        }
        .btn-outline {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: transparent;
            color: var(--text);
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            border: 2px solid var(--border);
            transition: border-color 0.2s, background 0.2s;
        }
        .btn-outline:hover {
            border-color: var(--primary);
            background: rgba(13, 107, 78, 0.04);
        }
        .hero-stats {
            display: flex;
            gap: 40px;
            margin-top: 40px;
            padding-top: 32px;
            border-top: 1px solid var(--border);
        }
        .hero-stats .stat-value {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 900;
            font-size: 1.8rem;
            color: var(--primary);
        }
        .hero-stats .stat-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            font-weight: 500;
        }
        .hero-visual {
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .phone-mockup {
            width: 280px;
            background: #0b1a17;
            border-radius: 40px;
            padding: 12px;
            border: 3px solid #2d4a43;
            box-shadow: 0 30px 60px rgba(11, 26, 23, 0.2);
            transition: transform 0.4s;
        }
        .phone-mockup:hover {
            transform: translateY(-6px);
        }
        .phone-mockup .notch {
            width: 80px;
            height: 18px;
            background: #0b1a17;
            border-radius: 0 0 14px 14px;
            margin: -8px auto 8px;
        }
        .phone-mockup .screen {
            background: #fff;
            border-radius: 28px;
            overflow: hidden;
            aspect-ratio: 9 / 19;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px 12px;
            text-align: center;
        }
        .phone-mockup .screen img {
            width: 80%;
            margin-bottom: 12px;
        }
        .phone-mockup .screen .tagline {
            font-weight: 800;
            font-size: 0.8rem;
            color: var(--primary-dark);
            letter-spacing: 0.5px;
        }
        @media (max-width: 900px) {
            .hero .container {
                grid-template-columns: 1fr;
                text-align: center;
            }
            .hero p {
                max-width: 100%;
                margin-left: auto;
                margin-right: auto;
            }
            .hero-actions {
                justify-content: center;
            }
            .hero-stats {
                justify-content: center;
            }
            .hero h1 {
                font-size: 2.4rem;
            }
            .phone-mockup {
                width: 200px;
            }
        }
        @media (max-width: 480px) {
            .hero h1 {
                font-size: 1.8rem;
            }
            .hero {
                padding: 48px 0 40px;
            }
            .phone-mockup {
                width: 160px;
            }
        }

        /* ===== SECTION HEADERS ===== */
        .section-header {
            text-align: center;
            max-width: 640px;
            margin: 0 auto 56px;
        }
        .section-header .label {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--primary);
            background: none;
            padding: 0 0 4px;
            border-radius: 0;
            margin-bottom: 14px;
            border-bottom: 2.5px solid var(--secondary);
        }
        .section-header h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 900;
            font-size: 2.4rem;
            letter-spacing: -1px;
            color: var(--text);
            margin-bottom: 12px;
        }
        .section-header h2 span {
            color: var(--primary);
        }
        .section-header p {
            color: var(--text-secondary);
            font-size: 1.05rem;
            line-height: 1.7;
        }

        /* ===== FEATURES ===== */
        .features {
            padding: 80px 0;
            background: var(--surface);
        }
        .features-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }
        .feature-card {
            background: var(--bg);
            border-radius: var(--radius-lg);
            padding: 32px 28px;
            transition: box-shadow 0.3s, transform 0.3s;
            border: 1px solid transparent;
        }
        .feature-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
            border-color: var(--border);
        }
        .feature-card .icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            border-radius: 14px;
            background: rgba(13, 107, 78, 0.08);
            color: var(--primary);
            font-size: 1.5rem;
            margin-bottom: 18px;
        }
        .feature-card h3 {
            font-weight: 800;
            font-size: 1.15rem;
            margin-bottom: 8px;
            color: var(--text);
        }
        .feature-card p {
            color: var(--text-secondary);
            font-size: 0.92rem;
            line-height: 1.6;
            margin-bottom: 16px;
        }
        .feature-card ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .feature-card ul li {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            font-size: 0.88rem;
            color: var(--text-secondary);
        }
        .feature-card ul li i {
            color: var(--primary);
            font-size: 1rem;
            margin-top: 2px;
            flex-shrink: 0;
        }
        @media (max-width: 900px) {
            .features-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        @media (max-width: 600px) {
            .features-grid {
                grid-template-columns: 1fr;
            }
            .section-header h2 {
                font-size: 1.8rem;
            }
        }

        /* ===== DETAILED FEATURES (alternating) ===== */
        .detailed-features {
            padding: 80px 0;
            background: var(--bg);
        }
        .detail-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            align-items: center;
            margin-bottom: 72px;
        }
        .detail-row:last-child {
            margin-bottom: 0;
        }
        .detail-row.reverse {
            direction: rtl;
        }
        .detail-row.reverse .detail-text {
            direction: ltr;
        }
        .detail-text .badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            font-size: 0.78rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: var(--primary);
            background: none;
            padding: 0 0 4px;
            border-radius: 0;
            margin-bottom: 14px;
            border-bottom: 2.5px solid var(--secondary);
        }
        .detail-text h3 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 900;
            font-size: 1.8rem;
            letter-spacing: -0.5px;
            margin-bottom: 12px;
            color: var(--text);
        }
        .detail-text p {
            color: var(--text-secondary);
            font-size: 1rem;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        .detail-text .check-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .detail-text .check-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            font-weight: 500;
            color: var(--text-secondary);
        }
        .detail-text .check-list li i {
            color: var(--primary);
            font-size: 1.1rem;
            margin-top: 2px;
            flex-shrink: 0;
        }
        .detail-image {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 10px;
            background: transparent;
        }
        .phone-frame {
            position: relative;
            width: 100%;
            max-width: 275px;
            background: #0f172a;
            border-radius: 44px;
            padding: 12px;
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.28), 0 0 0 2px #334155, inset 0 0 0 1px rgba(255, 255, 255, 0.15);
            transition: transform 0.4s ease, box-shadow 0.4s ease;
        }
        .phone-frame:hover {
            transform: translateY(-8px);
            box-shadow: 0 35px 60px -12px rgba(13, 107, 78, 0.25), 0 0 0 2.5px var(--primary);
        }
        .phone-frame::before {
            content: '';
            position: absolute;
            top: 20px;
            left: 50%;
            transform: translateX(-50%);
            width: 75px;
            height: 16px;
            background: #0f172a;
            border-radius: 12px;
            z-index: 10;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.5);
        }
        .phone-frame .phone-screen {
            position: relative;
            width: 100%;
            border-radius: 34px;
            overflow: hidden;
            background: #000;
            line-height: 0;
        }
        .phone-frame .phone-screen img {
            width: 100% !important;
            max-width: 100% !important;
            height: auto !important;
            display: block;
            border-radius: 34px !important;
            border: none !important;
            box-shadow: none !important;
        }
        @media (max-width: 820px) {
            .detail-row {
                grid-template-columns: 1fr;
                gap: 32px;
            }
            .detail-row.reverse {
                direction: ltr;
            }
            .detail-text h3 {
                font-size: 1.5rem;
            }
            .phone-frame {
                max-width: 240px;
                padding: 10px;
                border-radius: 38px;
            }
            .phone-frame .phone-screen {
                border-radius: 28px;
            }
            .phone-frame .phone-screen img {
                border-radius: 28px !important;
            }
        }

        /* ===== PRICING ===== */
        .pricing {
            padding: 80px 0;
            background: var(--surface);
        }
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
            margin-top: 8px;
        }
        .pricing-card {
            background: var(--bg);
            border-radius: var(--radius-lg);
            padding: 36px 28px;
            border: 1px solid var(--border);
            transition: box-shadow 0.3s, transform 0.3s, border-color 0.3s;
            display: flex;
            flex-direction: column;
            position: relative;
        }
        .pricing-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-md);
        }
        .pricing-card.featured {
            border-color: var(--secondary);
            background: #fffcf7;
            box-shadow: 0 8px 30px rgba(230, 138, 46, 0.1);
        }
        .pricing-card.featured .badge-top {
            position: absolute;
            top: -12px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--secondary);
            color: #fff;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px 16px;
            border-radius: 100px;
            white-space: nowrap;
            box-shadow: 0 2px 8px rgba(230, 138, 46, 0.3);
        }
        .pricing-card .plan-name {
            font-weight: 800;
            font-size: 1.3rem;
            color: var(--text);
            margin-bottom: 4px;
        }
        .pricing-card .plan-desc {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        .pricing-card .price {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 900;
            font-size: 2.2rem;
            color: var(--text);
            margin-bottom: 4px;
            word-break: break-word;
        }
        .pricing-card .price small {
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-muted);
        }
        .pricing-card .price-note {
            font-size: 0.8rem;
            color: var(--text-muted);
            margin-bottom: 20px;
        }
        .pricing-card .features-list {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1;
            margin-bottom: 28px;
        }
        .pricing-card .features-list li {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--text-secondary);
        }
        .pricing-card .features-list li i {
            color: var(--primary);
            font-size: 1rem;
            flex-shrink: 0;
        }
        .pricing-card .btn-plan {
            display: block;
            text-align: center;
            padding: 14px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 0.95rem;
            background: var(--border);
            color: var(--text-secondary);
            transition: background 0.2s, color 0.2s;
        }
        .pricing-card .btn-plan:hover {
            background: var(--primary);
            color: #fff;
        }
        .pricing-card.featured .btn-plan {
            background: var(--secondary);
            color: #fff;
        }
        .pricing-card.featured .btn-plan:hover {
            background: #d67a20;
        }
        @media (max-width: 1024px) {
            .pricing-grid {
                grid-template-columns: 1fr;
                max-width: 480px;
                margin-left: auto;
                margin-right: auto;
                gap: 32px;
            }
        }
        @media (max-width: 600px) {
            .pricing-card {
                padding: 26px 18px;
            }
            .pricing-card .price {
                font-size: 1.65rem;
            }
            .pricing-card .price small {
                font-size: 0.85rem;
            }
            .pricing-card .features-list li {
                font-size: 0.85rem;
                align-items: flex-start;
            }
            .pricing-card .features-list li i {
                margin-top: 3px;
            }
        }

        /* ===== DEVICES SHOWCASE ===== */
        .devices {
            padding: 80px 0;
            background: var(--bg);
        }
        .devices-grid {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 32px;
            flex-wrap: wrap;
            margin-top: 12px;
        }
        .device-item {
            background: var(--surface);
            border-radius: var(--radius-lg);
            padding: 20px 16px 24px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow-sm);
            transition: box-shadow 0.3s, transform 0.3s;
            text-align: center;
            flex: 0 1 200px;
        }
        .device-item:hover {
            box-shadow: var(--shadow-md);
            transform: translateY(-4px);
        }
        .device-item .icon-wrap {
            font-size: 2.8rem;
            color: var(--primary);
            margin-bottom: 10px;
        }
        .device-item .device-img-wrap {
            width: 100%;
            height: 140px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            overflow: hidden;
            border-radius: var(--radius-md);
            background: rgba(13, 107, 78, 0.04);
            padding: 8px;
        }
        .device-item .device-img-wrap img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        .device-item h4 {
            font-weight: 700;
            font-size: 1rem;
            color: var(--text);
        }
        .device-item p {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        @media (max-width: 768px) {
            .devices-grid {
                display: grid !important;
                grid-template-columns: repeat(2, 1fr) !important;
                gap: 12px !important;
            }
            .device-item {
                flex: unset !important;
                width: 100% !important;
                padding: 16px 10px !important;
            }
            .device-item .icon-wrap {
                font-size: 2.2rem !important;
                margin-bottom: 6px !important;
            }
            .device-item h4 {
                font-size: 0.9rem !important;
            }
            .device-item p {
                font-size: 0.78rem !important;
            }
        }

        /* ===== CONTACT / CTA ===== */
        .contact {
            padding: 80px 0;
            background: var(--surface);
            scroll-margin-top: 100px;
        }
        .contact-wrapper {
            display: grid;
            grid-template-columns: 1fr 1.2fr;
            gap: 48px;
            align-items: start;
            background: #ffffff;
            border-radius: var(--radius-lg);
            padding: 48px;
            border: 2px solid var(--primary);
            box-shadow: 0 16px 40px rgba(13, 107, 78, 0.1);
        }
        .contact-info h2 {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 900;
            font-size: 2rem;
            letter-spacing: -0.5px;
            margin-bottom: 12px;
            color: var(--text);
        }
        .contact-info h2 span {
            color: var(--primary);
        }
        .contact-info p {
            color: var(--text-secondary);
            line-height: 1.7;
            margin-bottom: 24px;
        }
        .contact-info .contact-detail {
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            margin-bottom: 10px;
        }
        .contact-info .contact-detail i {
            color: var(--primary);
            font-size: 1.2rem;
            width: 24px;
        }
        .contact-form {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .contact-form .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }
        .contact-form label {
            font-weight: 600;
            font-size: 0.85rem;
            color: var(--text-secondary);
            display: block;
            margin-bottom: 4px;
        }
        .contact-form input,
        .contact-form select {
            width: 100%;
            padding: 12px 16px;
            border-radius: 12px;
            border: 1.5px solid var(--border);
            background: var(--surface);
            font-family: inherit;
            font-size: 16px;
            transition: border-color 0.2s, box-shadow 0.2s;
            color: var(--text);
        }
        .contact-form input:focus,
        .contact-form select:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(13, 107, 78, 0.08);
        }
        .contact-form .price-preview {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: var(--surface);
            border-radius: 12px;
            padding: 12px 18px;
            border: 1.5px solid var(--border);
            flex-wrap: wrap;
            gap: 8px;
        }
        .contact-form .price-preview .amount {
            font-family: 'Space Grotesk', sans-serif;
            font-weight: 900;
            font-size: 1.3rem;
            color: var(--primary);
        }
        .contact-form .price-preview .installment {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--text-muted);
        }
        .contact-form .checkbox-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 0.9rem;
            color: var(--text-secondary);
            cursor: pointer;
        }
        .contact-form .checkbox-wrap input {
            width: 18px;
            height: 18px;
            accent-color: var(--primary);
            cursor: pointer;
        }
        .btn-submit {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 16px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.2s, transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 16px rgba(13, 107, 78, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }
        .btn-submit:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 8px 28px rgba(13, 107, 78, 0.3);
        }
        @media (max-width: 900px) {
            .contact-wrapper {
                grid-template-columns: 1fr;
                padding: 32px 24px;
            }
            .contact-form .row {
                grid-template-columns: 1fr;
            }
        }
        @media (max-width: 480px) {
            .contact-wrapper {
                padding: 24px 16px;
            }
            .contact-info h2 {
                font-size: 1.6rem;
            }
        }

        /* ===== FOOTER ===== */
        footer {
            background: #0f172a;
            color: #f8fafc;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding: 60px 5% 28px;
        }
        .footer-top {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 40px;
            flex-wrap: wrap;
            margin-bottom: 48px;
        }
        .footer-brand {
            flex: 1;
            min-width: 240px;
        }
        .footer-brand .logo-wrap {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 14px;
        }
        .footer-brand .logo-wrap img {
            height: 48px;
            width: 48px;
            object-fit: contain;
            border-radius: 12px;
        }
        .footer-brand p {
            color: #94a3b8;
            font-size: 0.9rem;
            line-height: 1.7;
            max-width: 320px;
        }
        .footer-links {
            min-width: 160px;
        }
        .footer-links h5 {
            font-weight: 700;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #f8fafc;
            margin-bottom: 18px;
        }
        .footer-links ul {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .footer-links ul li a {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all .2s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .footer-links ul li a:hover {
            color: var(--secondary);
            transform: translateX(3px);
        }
        .footer-bottom {
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
            color: #64748b;
            font-size: 0.85rem;
        }
        @media (max-width: 600px) {
            .footer-top { flex-direction: column; gap: 32px; }
        }

        /* ===== UTILITIES ===== */
        .text-center {
            text-align: center;
        }
        .mt-8 {
            margin-top: 8px;
        }
        .mt-16 {
            margin-top: 16px;
        }
        .mb-8 {
            margin-bottom: 8px;
        }
        .gap-8 {
            gap: 8px;
        }
        .flex-center {
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* ===== REVEAL ANIMATION ===== */
        .reveal {
            opacity: 1 !important;
            transform: translateY(0) !important;
            transition: opacity 0.5s ease, transform 0.5s ease;
        }
        .contact, .contact-wrapper, .contact-form {
            opacity: 1 !important;
            visibility: visible !important;
            display: block;
        }
        .reveal-delay-1 {
            transition-delay: 0.1s;
        }
        .reveal-delay-2 {
            transition-delay: 0.2s;
        }
        .reveal-delay-3 {
            transition-delay: 0.3s;
        }
        .reveal-delay-4 {
            transition-delay: 0.4s;
        }

        /* ===== CREATION MODAL ===== */
        .modal-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(11, 26, 23, 0.75);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }
        .modal-overlay.active {
            opacity: 1;
            visibility: visible;
        }
        .modal-card {
            background: #ffffff;
            border-radius: var(--radius-lg);
            max-width: 600px;
            width: 100%;
            max-height: 90vh;
            overflow-y: auto;
            padding: 32px 28px;
            position: relative;
            box-shadow: 0 25px 60px rgba(0, 0, 0, 0.3);
            transform: translateY(20px) scale(0.95);
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            border: 2px solid var(--primary);
        }
        .modal-overlay.active .modal-card {
            transform: translateY(0) scale(1);
        }
        .modal-close {
            position: absolute;
            top: 16px;
            right: 18px;
            background: var(--bg);
            border: none;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: var(--text-secondary);
            cursor: pointer;
            transition: all 0.2s;
        }
        .modal-close:hover {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
        }

        /* ===== FLOATING ACTION BUTTON ===== */
        .floating-cta {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 990;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: #ffffff;
            border: none;
            padding: 14px 22px;
            border-radius: 50px;
            font-family: inherit;
            font-weight: 800;
            font-size: 0.9rem;
            cursor: pointer;
            box-shadow: 0 8px 24px rgba(13, 107, 78, 0.4);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .floating-cta:hover {
            transform: translateY(-3px) scale(1.03);
            box-shadow: 0 12px 32px rgba(13, 107, 78, 0.5);
        }
    </style>
</head>
<body>

    <!-- ===== TOP BAR ===== -->
    <div class="top-bar">
        <a href="tel:+2290146862536"><i class="bi bi-telephone"></i> +229 01 46 86 25 36</a>
        <span class="divider">|</span>
        <a href="mailto:pilotixcontact@gmail.com"><i class="bi bi-envelope"></i> pilotixcontact@gmail.com</a>
    </div>

    <!-- ===== NAV ===== -->
    <nav>
        <a href="/" class="nav-logo">
            <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX" />
            PILOTIX
        </a>
        <div class="nav-links">
            <a href="#features">Fonctionnalités</a>
            <a href="#pricing">Tarifs</a>
            <a href="#devices">Écrans</a>
            <a href="{{ route('partenaires') }}"><i class="bi bi-people"></i> Partenariat</a>
            <a href="{{ route('download') }}"><i class="bi bi-download"></i> Télécharger</a>
            <a href="#contact" onclick="openCreationModal(event)"><i class="bi bi-chat-dots"></i> Demande de création</a>
            @if (Auth::check())
                <a href="{{ route('dashboard') }}" class="nav-cta"><i class="bi bi-box-arrow-in-right"></i> Tableau de bord</a>
            @else
                <a href="{{ route('login') }}" class="nav-cta"><i class="bi bi-box-arrow-in-right"></i> Connexion</a>
            @endif
        </div>
        <button class="hamburger" id="hamburger" onclick="toggleMenu()" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <!-- ===== MOBILE MENU ===== -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="#features" onclick="closeMenu()"><i class="bi bi-grid-1x2"></i> Fonctionnalités</a>
        <a href="#pricing" onclick="closeMenu()"><i class="bi bi-tag"></i> Tarifs</a>
        <a href="#devices" onclick="closeMenu()"><i class="bi bi-display"></i> Écrans</a>
        <a href="{{ route('partenaires') }}" onclick="closeMenu()"><i class="bi bi-people"></i> Partenariat</a>
        <a href="{{ route('download') }}" onclick="closeMenu()"><i class="bi bi-download"></i> Télécharger</a>
        <a href="#contact" onclick="openCreationModal(event);closeMenu()"><i class="bi bi-chat-dots"></i> Demande de création</a>
        <a href="tel:+2290146862536" onclick="closeMenu()"><i class="bi bi-telephone"></i> +229 01 46 86 25 36</a>
        <div class="mobile-divider"></div>
        @if (Auth::check())
            <a href="{{ route('dashboard') }}" class="mobile-cta" onclick="closeMenu()"><i class="bi bi-box-arrow-in-right"></i> Tableau de bord</a>
        @else
            <a href="{{ route('login') }}" class="mobile-cta" onclick="closeMenu()"><i class="bi bi-box-arrow-in-right"></i> Se connecter</a>
        @endif
    </div>

    <!-- ===== HERO ===== -->
    <section class="hero">
        <div class="container">
            <div class="hero-content reveal">
                <h1>Pilotez votre commerce avec <span>confiance</span></h1>
                <p>Centralisez vos ventes, stocks, arrivages, factures, dettes clients et dépenses. Une vue d'ensemble en temps réel pour des décisions éclairées.</p>
                <div class="hero-actions">
                    <a href="#contact" onclick="openCreationModal(event)" class="btn-primary"><i class="bi bi-rocket-takeoff-fill"></i> Demande de création de compte</a>
                    <a href="{{ route('login') }}" class="btn-outline"><i class="bi bi-box-arrow-in-right"></i> Se connecter</a>
                </div>
                <div class="hero-stats">
                    <div><span class="stat-value">∞</span> <span class="stat-label">Produits</span></div>
                    <div><span class="stat-value">100%</span> <span class="stat-label">Multi-magasins</span></div>
                </div>
            </div>
            <div class="hero-visual reveal reveal-delay-1">
                <div class="phone-mockup">
                    <div class="notch"></div>
                    <div class="screen" style="padding: 0; background: #000;">
                        <img src="{{ asset('Visuel/mobile-dashboard.jpeg') }}" alt="Écran Tableau de bord PILOTIX" style="width: 100%; height: 100%; object-fit: cover; border-radius: 28px; margin: 0;" />
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FEATURES GRID ===== -->
    <section class="features" id="features">
        <div class="container">
            <div class="section-header reveal">
                <span class="label"><i class="bi bi-stars"></i> Ce que PILOTIX vous apporte</span>
                <h2>Un écosystème commercial <span>complet & interconnecté</span></h2>
                <p>Une suite globale pensée pour les commerçants, grossistes, importateurs et gérants de multi-magasins.</p>
            </div>
            <div class="features-grid">
                <!-- Card 1 -->
                <div class="feature-card reveal reveal-delay-1">
                    <div class="icon"><i class="bi bi-box-seam"></i></div>
                    <h3>Stock multi-dépôts & arrivages</h3>
                    <p>Gérez vos stocks physiques par magasin, enregistrez vos arrivages fournisseurs et recevez des alertes avant rupture.</p>
                    <ul>
                        <li><i class="bi bi-check-lg"></i> Multi-magasins en temps réel</li>
                        <li><i class="bi bi-check-lg"></i> Coût de revient automatique</li>
                        <li><i class="bi bi-check-lg"></i> Alertes seuil critique</li>
                    </ul>
                </div>
                <!-- Card 2 -->
                <div class="feature-card reveal reveal-delay-2">
                    <div class="icon"><i class="bi bi-tags"></i></div>
                    <h3>Ventes gros & détail, factures</h3>
                    <p>Adaptez vos prix selon la quantité (carton / unité) et éditez factures proforma, définitives et tickets de caisse.</p>
                    <ul>
                        <li><i class="bi bi-check-lg"></i> Bascule automatique gros/détail</li>
                        <li><i class="bi bi-check-lg"></i> Impression tickets & factures</li>
                        <li><i class="bi bi-check-lg"></i> Historique par vendeur</li>
                    </ul>
                </div>
                <!-- Card 3 -->
                <div class="feature-card reveal reveal-delay-3">
                    <div class="icon"><i class="bi bi-wallet2"></i></div>
                    <h3>Trésorerie & rapprochement</h3>
                    <p>Suivez la caisse espèces, les virements bancaires et les portefeuilles Mobile Money (MTN, Moov, Wave).</p>
                    <ul>
                        <li><i class="bi bi-check-lg"></i> Caisse magasin au franc près</li>
                        <li><i class="bi bi-check-lg"></i> Rapprochement MoMo & banques</li>
                        <li><i class="bi bi-check-lg"></i> Clôture quotidienne</li>
                    </ul>
                </div>
                <!-- Card 4 -->
                <div class="feature-card reveal reveal-delay-1">
                    <div class="icon"><i class="bi bi-credit-card-2-back"></i></div>
                    <h3>Crédits & créances clients</h3>
                    <p>Accordez des facilités de paiement en toute sécurité. Suivez le solde dû et chaque acompte versé.</p>
                    <ul>
                        <li><i class="bi bi-check-lg"></i> Suivi du portefeuille créances</li>
                        <li><i class="bi bi-check-lg"></i> Reçu d'acompte instantané</li>
                        <li><i class="bi bi-check-lg"></i> Plafond paramétrable</li>
                    </ul>
                </div>
                <!-- Card 5 -->
                <div class="feature-card reveal reveal-delay-2">
                    <div class="icon"><i class="bi bi-box-arrow-up-right"></i></div>
                    <h3>Retrait magasin & validation</h3>
                    <p>Le magasinier valide la remise physique des colis pour certifier le bon de sortie et déduire le stock.</p>
                    <ul>
                        <li><i class="bi bi-check-lg"></i> Validation physique magasinier</li>
                        <li><i class="bi bi-check-lg"></i> Bon de sortie certifié</li>
                        <li><i class="bi bi-check-lg"></i> Déduction stock instantanée</li>
                    </ul>
                </div>
                <!-- Card 6 -->
                <div class="feature-card reveal reveal-delay-3">
                    <div class="icon"><i class="bi bi-calculator"></i></div>
                    <h3>Dépenses & bénéfice net réel</h3>
                    <p>Enregistrez vos frais d'exploitation (loyer, électricité, salaires) et calculez votre vrai bénéfice net.</p>
                    <ul>
                        <li><i class="bi bi-check-lg"></i> Catégorisation des charges</li>
                        <li><i class="bi bi-check-lg"></i> Bénéfice net automatique</li>
                        <li><i class="bi bi-check-lg"></i> Comparatifs mensuels</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== DETAILED FEATURES (alternating) ===== -->
    <section class="detailed-features">
        <div class="container">
            <div class="section-header reveal">
                <span class="label"><i class="bi bi-layers"></i> Fonctionnalités avancées</span>
                <h2>Une vision <span>360°</span> de votre activité</h2>
            </div>

            <!-- Row 1 -->
            <div class="detail-row reveal">
                <div class="detail-text">
                    <span class="badge"><i class="bi bi-box-seam-fill"></i> Logistique</span>
                    <h3>Gestion des stocks par dépôt & arrivages</h3>
                    <p>Ne soyez plus jamais en rupture. PILOTIX comptabilise vos stocks physiques réels par magasin et dépôt, enregistre vos arrivages fournisseurs avec frais (douane, transport) et alerte avant tout seuil critique.</p>
                    <ul class="check-list">
                        <li><i class="bi bi-check-lg"></i> Multi-magasins & dépôts autonomes en temps réel</li>
                        <li><i class="bi bi-check-lg"></i> Calcul automatique du coût de revient d'achat</li>
                        <li><i class="bi bi-check-lg"></i> Alertes dynamiques de stock bas et réapprovisionnement</li>
                        <li><i class="bi bi-check-lg"></i> Transferts inter-dépôts sécurisés</li>
                    </ul>
                </div>
                <div class="detail-image">
                    <div class="phone-frame">
                        <div class="phone-screen">
                            <img src="{{ asset('Visuel/mobile-depot.jpeg') }}" alt="Écran Stock & Arrivages PILOTIX">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2 (reverse) -->
            <div class="detail-row reverse reveal">
                <div class="detail-text">
                    <span class="badge"><i class="bi bi-tags-fill"></i> Tarification</span>
                    <h3>Ventes gros & détail, factures et tickets</h3>
                    <p>Adaptez vos prix automatiquement selon la quantité vendue (au carton ou à l'unité). Éditez des factures proforma, factures définitives et tickets de caisse avec suivi individuel par vendeur.</p>
                    <ul class="check-list">
                        <li><i class="bi bi-check-lg"></i> Bascule automatique des tarifs (Gros vs Détail)</li>
                        <li><i class="bi bi-check-lg"></i> Impression instantanée de tickets de caisse & factures</li>
                        <li><i class="bi bi-check-lg"></i> Historique détaillé des ventes par vendeur</li>
                        <li><i class="bi bi-check-lg"></i> Gestion des remises et rabais autorisés</li>
                    </ul>
                </div>
                <div class="detail-image">
                    <div class="phone-frame">
                        <div class="phone-screen">
                            <img src="{{ asset('Visuel/mobile-ventes.jpeg') }}" alt="Écran Ventes & Facturation PILOTIX">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3 -->
            <div class="detail-row reveal">
                <div class="detail-text">
                    <span class="badge"><i class="bi bi-wallet2"></i> Trésorerie</span>
                    <h3>Trésorerie globale, caisses espèces & Mobile Money</h3>
                    <p>Comptabilisez l'argent au franc près. PILOTIX isole et suit les encaissements de la caisse espèces en boutique, des virements bancaires et des portefeuilles Mobile Money.</p>
                    <ul class="check-list">
                        <li><i class="bi bi-check-lg"></i> Suivi de la caisse espèces en magasin au franc près</li>
                        <li><i class="bi bi-check-lg"></i> Rapprochement des paiements Mobile Money & Banques</li>
                        <li><i class="bi bi-check-lg"></i> Historique des entrées/sorties de trésorerie</li>
                        <li><i class="bi bi-check-lg"></i> Clôture quotidienne de caisse sans erreur</li>
                    </ul>
                </div>
                <div class="detail-image">
                    <div class="phone-frame">
                        <div class="phone-screen">
                            <img src="{{ asset('Visuel/mobile-tresorerie.jpeg') }}" alt="Écran Trésorerie & Caisses PILOTIX">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 4 (reverse) -->
            <div class="detail-row reverse reveal">
                <div class="detail-text">
                    <span class="badge"><i class="bi bi-exclamation-triangle-fill"></i> Alertes Stock</span>
                    <h3>Alertes de stock bas & alerte rupture</h3>
                    <p>Recevez des notifications automatiques dès qu'un produit atteint son seuil d'alerte critique afin d'éviter tout arrêt des ventes.</p>
                    <ul class="check-list">
                        <li><i class="bi bi-check-lg"></i> Alertes dynamiques de stock bas et rupture imminente</li>
                        <li><i class="bi bi-check-lg"></i> Catalogue produits complet avec prix de gros & détail</li>
                        <li><i class="bi bi-check-lg"></i> Niveau de stock physique en temps réel par dépôt</li>
                        <li><i class="bi bi-check-lg"></i> Réapprovisionnement rapide guidé par le système</li>
                    </ul>
                </div>
                <div class="detail-image">
                    <div class="phone-frame">
                        <div class="phone-screen">
                            <img src="{{ asset('Visuel/mobile-alert-stock.jpeg') }}" alt="Écran Alertes Stock PILOTIX">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 5 -->
            <div class="detail-row reveal">
                <div class="detail-text">
                    <span class="badge"><i class="bi bi-credit-card-2-back-fill"></i> Créances</span>
                    <h3>Gestion des crédits clients & acomptes</h3>
                    <p>Accordez des facilités de paiement en toute sécurité. Suivez le solde dû par client, enregistrez chaque acompte versé et rééditez instantanément le reçu de versement.</p>
                    <ul class="check-list">
                        <li><i class="bi bi-check-lg"></i> Suivi rigoureux du portefeuille de créances clients</li>
                        <li><i class="bi bi-check-lg"></i> Enregistrement des acomptes avec génération de reçu</li>
                        <li><i class="bi bi-check-lg"></i> Historique chronologique des règlements effectués</li>
                        <li><i class="bi bi-check-lg"></i> Plafond de crédit paramétrable par client</li>
                    </ul>
                </div>
                <div class="detail-image">
                    <div class="phone-frame">
                        <div class="phone-screen">
                            <img src="{{ asset('Visuel/mobile-dettes-clients.jpeg') }}" alt="Écran Crédits & Créances PILOTIX">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 5 -->
            <div class="detail-row reveal">
                <div class="detail-text">
                    <span class="badge"><i class="bi bi-box-arrow-up-right"></i> Logistics</span>
                    <h3>Retrait en magasin & validation magasinier</h3>
                    <p>Vos clients récupèrent leurs marchandises directement au dépôt ou en boutique. Le magasinier valide la remise physique des colis pour certifier le bon de sortie et déduire le stock.</p>
                    <ul class="check-list">
                        <li><i class="bi bi-check-lg"></i> Validation physique de la remise du colis par le magasinier</li>
                        <li><i class="bi bi-check-lg"></i> Certification du bon de sortie du magasin ou dépôt</li>
                        <li><i class="bi bi-check-lg"></i> Suivi précis des commandes en attente de retrait</li>
                        <li><i class="bi bi-check-lg"></i> Déduction instantanée du stock physique réel</li>
                    </ul>
                </div>
                <div class="detail-image">
                    <div class="phone-frame">
                        <div class="phone-screen">
                            <img src="{{ asset('Visuel/mobile-transfert.jpeg') }}" alt="Écran Retrait Magasin & Transferts PILOTIX">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 6 (reverse) -->
            <div class="detail-row reverse reveal">
                <div class="detail-text">
                    <span class="badge"><i class="bi bi-calculator-fill"></i> Rentabilité</span>
                    <h3>Dépenses d'exploitation & bénéfice net réel</h3>
                    <p>Enregistrez vos frais d'exploitation (loyers de magasins, électricité, salaires, transport) pour calculer automatiquement votre vrai bénéfice net.</p>
                    <ul class="check-list">
                        <li><i class="bi bi-check-lg"></i> Catégorisation des charges fixes et variables</li>
                        <li><i class="bi bi-check-lg"></i> Calcul automatique du bénéfice net réel de l'entreprise</li>
                        <li><i class="bi bi-check-lg"></i> Statistiques financières comparatives mensuelles</li>
                        <li><i class="bi bi-check-lg"></i> Visibilité claire sur les marges bénéficiaires</li>
                    </ul>
                </div>
                <div class="detail-image">
                    <div class="phone-frame">
                        <div class="phone-screen">
                            <img src="{{ asset('Visuel/mobile-nos-dettes.jpeg') }}" alt="Écran Dépenses & Résultat Net PILOTIX">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 7: Facturation & Reçus -->
            <div class="detail-row reveal">
                <div class="detail-text">
                    <span class="badge"><i class="bi bi-receipt"></i> Facturation</span>
                    <h3>Factures d'acompte, proforma & reçus</h3>
                    <p>Éditez des factures professionnelles en quelques secondes, générez des reçus d'acompte et partagez-les directement avec vos clients.</p>
                    <ul class="check-list">
                        <li><i class="bi bi-check-lg"></i> Édition instantanée de factures proforma & définitives</li>
                        <li><i class="bi bi-check-lg"></i> Reçus de paiement d'acompte avec récapitulatif du reste à payer</li>
                        <li><i class="bi bi-check-lg"></i> Exportation et partage facile par WhatsApp et email</li>
                        <li><i class="bi bi-check-lg"></i> Numérotation légale & traçabilité automatique des pièces</li>
                    </ul>
                </div>
                <div class="detail-image">
                    <div class="phone-frame">
                        <div class="phone-screen">
                            <img src="{{ asset('Visuel/mobile-facture.jpeg') }}" alt="Écran Facture & Reçus PILOTIX">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 8 (reverse): Analytique & Graphiques -->
            <div class="detail-row reverse reveal">
                <div class="detail-text">
                    <span class="badge"><i class="bi bi-graph-up-arrow"></i> Analytique</span>
                    <h3>Analyse avancée & tableau de bord</h3>
                    <p>Suivez les performances de votre entreprise grâce à des graphiques dynamiques et des métriques détaillées de chiffre d'affaires et marge brute.</p>
                    <ul class="check-list">
                        <li><i class="bi bi-check-lg"></i> Graphiques en temps réel du chiffre d'affaires et bénéfices</li>
                        <li><i class="bi bi-check-lg"></i> Suivi d'évolution mensuel et comparatifs de croissance</li>
                        <li><i class="bi bi-check-lg"></i> Palmarès des produits les plus vendus et les plus rentables</li>
                        <li><i class="bi bi-check-lg"></i> Analyse complète de la rentabilité globale</li>
                    </ul>
                </div>
                <div class="detail-image">
                    <div class="phone-frame">
                        <div class="phone-screen">
                            <img src="{{ asset('Visuel/mobile-analyse-avancee.jpeg') }}" alt="Écran Analytique & Graphiques PILOTIX">
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- ===== PRICING ===== -->
    <section class="pricing" id="pricing">
        <div class="container">
            <div class="section-header reveal">
                <span class="label"><i class="bi bi-tag"></i> Nos offres</span>
                <h2>Choisissez votre <span>version</span></h2>
                <p>Deux options simples. Zéro engagement.</p>
            </div>
            <div class="pricing-grid">
                <!-- Essentiel -->
                <div class="pricing-card reveal reveal-delay-1">
                    <div class="plan-name">Essentiel</div>
                    <div class="plan-desc">Idéal pour démarrer une boutique unique.</div>
                    <div class="price">{{ number_format($rules['essentiel']->prix ?? 0, 0, ' ', ' ') }}<small> FCFA / an</small></div>
                    @php
                        $essentielTranche = ($rules['essentiel']->prix_tranche_3x ?? 0) > 0 
                            ? $rules['essentiel']->prix_tranche_3x 
                            : round(($rules['essentiel']->prix ?? 0) / 3);
                    @endphp
                    <div class="price-note"><i class="bi bi-wallet2"></i> Paiement 3x possible (3 × {{ number_format($essentielTranche, 0, ' ', ' ') }} FCFA)</div>
                    <ul class="features-list">
                        <li><i class="bi bi-check-lg"></i> 1 Poste de travail</li>
                        <li><i class="bi bi-check-lg"></i> Gestion des produits & stocks</li>
                        <li><i class="bi bi-check-lg"></i> Ventes gros & détail + Factures</li>
                        <li><i class="bi bi-check-lg"></i> Dépenses, clients & inventaires</li>
                        <li><i class="bi bi-check-lg"></i> Mode Hors-Connexion & Sync auto</li>
                    </ul>
                    <a href="#contact" onclick="openCreationModal(event, 'essentiel')" class="btn-plan">Choisir l'offre</a>
                </div>

                <!-- Professionnel (featured) -->
                <div class="pricing-card featured reveal reveal-delay-2">
                    <div class="badge-top">Recommandé</div>
                    <div class="plan-name">Professionnel</div>
                    <div class="plan-desc">Pour grossistes, importateurs et multi-magasins.</div>
                    <div class="price">{{ number_format($rules['professionnel']->prix ?? 0, 0, ' ', ' ') }}<small> FCFA / an</small></div>
                    @php
                        $profTrancheCard = ($rules['professionnel']->prix_tranche_3x ?? 0) > 0 
                            ? $rules['professionnel']->prix_tranche_3x 
                            : round(($rules['professionnel']->prix ?? 0) / 3);
                    @endphp
                    <div class="price-note"><i class="bi bi-wallet2"></i> Paiement 3x possible (3 × {{ number_format($profTrancheCard, 0, ' ', ' ') }} FCFA)</div>
                    <ul class="features-list">
                        <li><i class="bi bi-check-lg"></i> <strong>Plusieurs postes</strong> & utilisateurs</li>
                        <li><i class="bi bi-check-lg"></i> <strong>Gestion des Importations</strong> & arrivages</li>
                        <li><i class="bi bi-check-lg"></i> Calculs des <strong>coûts de revient & marges</strong></li>
                        <li><i class="bi bi-check-lg"></i> <strong>Multi-magasins</strong> & dépôts</li>
                        <li><i class="bi bi-check-lg"></i> Mode Hors-Connexion & Sync auto</li>
                        <li><i class="bi bi-check-lg"></i> Statistiques avancées & suivi d'activité</li>
                    </ul>
                    <a href="#contact" onclick="openCreationModal(event, 'professionnel')" class="btn-plan">Choisir l'offre</a>
                </div>

                <!-- Entreprise -->
                <div class="pricing-card reveal reveal-delay-3">
                    <div class="plan-name">Entreprise</div>
                    <div class="plan-desc">Pour les structures souhaitant une infrastructure dédiée.</div>
                    <div class="price">{{ number_format($rules['entreprise']->prix ?? 0, 0, ' ', ' ') }}<small> FCFA</small></div>
                    <div class="price-note">Licence à vie (Domaine client après 1 an)</div>
                    <ul class="features-list">
                        <li><i class="bi bi-check-lg"></i> Installation & configuration personnalisées</li>
                        <li><i class="bi bi-check-lg"></i> Toutes les fonctionnalités incluses</li>
                        <li><i class="bi bi-check-lg"></i> Domaine & hébergement dédiés du client</li>
                        <li><i class="bi bi-check-lg"></i> Base de données 100% isolée & dédiée</li>
                        <li><i class="bi bi-check-lg"></i> Support prioritaire & formation sur site</li>
                    </ul>
                    <a href="#contact" onclick="openCreationModal(event, 'entreprise')" class="btn-plan">Demander un devis</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FORMULAIRE DE SOUSCRIPTION ===== -->
    <section class="contact" id="contact">
        <div class="container">
            <div class="section-header reveal" style="margin-bottom: 40px; text-align: center;">
                <span class="label"><i class="bi bi-rocket-takeoff-fill"></i> Souscription & Création de compte</span>
                <h2>Formulaire de <span>Demande de Création</span></h2>
                <p>Remplissez les informations ci-dessous. Notre équipe prépare vos accès et vous recontacte sous 24h.</p>
            </div>
            <div class="contact-wrapper">
                <div class="contact-info">
                    <span style="display:inline-block; font-size:0.75rem; font-weight:700; text-transform:uppercase; letter-spacing:1.5px; color:var(--primary); border-bottom:2.5px solid var(--secondary); padding-bottom:3px; margin-bottom:12px;">
                        <i class="bi bi-shield-check"></i> Assistance & Support
                    </span>
                    <h2>Prêt à propulser <span>votre commerce ?</span></h2>
                    <p>Pour toute question ou assistance immédiate, nos conseillers sont joignables directement.</p>
                    <div class="contact-detail"><i class="bi bi-telephone"></i> +229 01 46 86 25 36</div>
                    <div class="contact-detail"><i class="bi bi-envelope"></i> pilotixcontact@gmail.com</div>
                    <div class="contact-detail"><i class="bi bi-geo-alt"></i> Bénin — Disponible dans toute l'Afrique de l'Ouest</div>
                </div>

                <div>
                    @if(session('success'))
                        <div style="background:#f0fdf4; border:1px solid #86efac; padding:14px; border-radius:12px; color:#166534; font-weight:600; margin-bottom:20px; display:flex; align-items:center; gap:8px;">
                            <i class="bi bi-check-circle-fill" style="color:#16a34a;"></i> {{ session('success') }}
                        </div>
                    @endif

                    <form action="{{ route('contact.submit') }}" method="POST" class="contact-form">
                        @csrf
                        <div class="row">
                            <div>
                                <label for="nom_societe">Nom de la société *</label>
                                <input type="text" name="nom_societe" id="nom_societe" placeholder="Ex: Mon Entreprise SARL" required />
                            </div>
                            <div>
                                <label for="email">Email de contact *</label>
                                <input type="email" name="email" id="email" placeholder="Ex: contact@entreprise.com" required />
                            </div>
                        </div>
                        <div class="row">
                            <div>
                                <label for="localisation">Pays / Localisation *</label>
                                <input type="text" name="localisation" id="localisation" placeholder="Ex: Bénin" required />
                            </div>
                            <div>
                                <label for="ville">Ville *</label>
                                <input type="text" name="ville" id="ville" placeholder="Ex: Cotonou" required />
                            </div>
                        </div>
                        <div class="row">
                            <div>
                                <label for="telephone">Téléphone *</label>
                                <input type="text" name="telephone" id="telephone" placeholder="Ex: +229 97 00 00 00" required />
                            </div>
                            <div>
                                <label for="secteurs_activite">Secteurs d'activité *</label>
                                <input type="text" name="secteurs_activite" id="secteurs_activite" placeholder="Ex: Import/Export, Distribution" required />
                            </div>
                        </div>

                        <div>
                            <label for="type_souscription">Offre de souscription *</label>
                            <select name="type_souscription" id="type_souscription" onchange="updatePricing()">
                                <option value="essentiel">Essentiel — {{ number_format($rules['essentiel']->prix ?? 0, 0, ' ', ' ') }} FCFA / an</option>
                                <option value="professionnel" selected>Professionnel — {{ number_format($rules['professionnel']->prix ?? 0, 0, ' ', ' ') }} FCFA / an</option>
                                <option value="entreprise">Entreprise — À partir de {{ number_format($rules['entreprise']->prix ?? 0, 0, ' ', ' ') }} FCFA</option>
                            </select>
                        </div>

                        <div class="price-preview">
                            <div>
                                <span style="font-size:0.8rem; color:var(--text-muted); font-weight:600;">Montant estimé</span>
                                <div><span class="amount" id="priceTotal">{{ number_format($rules['professionnel']->prix ?? 0, 0, ' ', ' ') }} FCFA</span> <span style="font-size:0.9rem; color:var(--text-muted);">/ an</span></div>
                            </div>
                            <div>
                                <span style="font-size:0.75rem; font-weight:700; color:var(--secondary); background:rgba(230,138,46,0.12); padding:4px 12px; border-radius:100px;" id="installmentLabel">Paiement 3x disponible</span>
                            </div>
                        </div>

                        <label class="checkbox-wrap" id="wrap_paiement_3x">
                            <input type="checkbox" name="paiement_3x" id="paiement_3x" value="1" onchange="updatePricing()" />
                            Option : Répartir en 3 tranches
                            <span style="font-weight:400; color:var(--text-muted); font-size:0.85rem;" id="installmentDetail">
                                @php
                                    $initialTranche = ($rules['professionnel']->prix_tranche_3x ?? 0) > 0 
                                        ? $rules['professionnel']->prix_tranche_3x 
                                        : round(($rules['professionnel']->prix ?? 0) / 3);
                                @endphp
                                3 × {{ number_format($initialTranche, 0, ' ', ' ') }} FCFA
                            </span>
                        </label>

                        <button type="submit" class="btn-submit"><i class="bi bi-send-fill"></i> Soumettre ma demande</button>
                        <div style="display:flex; align-items:center; gap:6px; font-size:0.8rem; color:var(--text-muted); margin-top:4px;">
                            <i class="bi bi-shield-check" style="color:var(--primary);"></i> Vos données sont protégées & traitées confidentiellement
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== DEVICES ===== -->
    <section class="devices" id="devices">
        <div class="container">
            <div class="section-header reveal">
                <span class="label"><i class="bi bi-display"></i> Multi-écrans</span>
                <h2>Disponible sur <span>tous vos appareils</span></h2>
                <p>Optimisée pour les smartphones vendeurs & livreurs, avec extensions sur Tablette POS et PC Bureau.</p>
            </div>
            <div class="devices-grid">
                <div class="device-item reveal reveal-delay-1">
                    <div class="icon-wrap"><i class="bi bi-phone"></i></div>
                    <h4>Mobile App (Android)</h4>
                    <p>Interface principale vendeurs & livreurs</p>
                </div>
                <div class="device-item reveal reveal-delay-2">
                    <div class="icon-wrap"><i class="bi bi-apple"></i></div>
                    <h4>iPhone & Web (iOS)</h4>
                    <p>Accès 100% Web optimisé mobile</p>
                </div>
                <div class="device-item reveal reveal-delay-3">
                    <div class="icon-wrap"><i class="bi bi-laptop"></i></div>
                    <h4>PC, Mac & Tablette</h4>
                    <p>Supervision & gestion des dépôts</p>
                </div>
                <div class="device-item reveal reveal-delay-4">
                    <div class="icon-wrap"><i class="bi bi-wifi-off"></i></div>
                    <h4>Mode Hors-Ligne</h4>
                    <p>Synchronisation automatique</p>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER ===== -->
    <footer>
        <div class="footer-top">
            <div class="footer-brand">
                <div class="logo-wrap">
                    <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX Logo">
                    <span style="font-family:'Space Grotesk',sans-serif; font-size:1.3rem; font-weight:800; color:#f8fafc;">PILOTIX</span>
                </div>
                <p>Solution de gestion commerciale multi-Dépôt ou Magasin pour les PME d'Afrique de l'Ouest.</p>
            </div>

            <div class="footer-links">
                <h5>Navigation</h5>
                <ul>
                    <li><a href="{{ url('/') }}"><i class="bi bi-house"></i> Accueil</a></li>
                    <li><a href="#features"><i class="bi bi-stars"></i> Fonctionnalités</a></li>
                    <li><a href="#pricing"><i class="bi bi-tags"></i> Tarifs</a></li>
                    <li><a href="{{ route('download') }}"><i class="bi bi-download"></i> Télécharger</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h5>Contact</h5>
                <ul>
                    <li><a href="tel:+2290146862536"><i class="bi bi-telephone"></i> +229 01 46 86 25 36</a></li>
                    <li><a href="mailto:pilotixcontact@gmail.com"><i class="bi bi-envelope"></i> pilotixcontact@gmail.com</a></li>
                    <li><a href="{{ route('partenaires') }}"><i class="bi bi-people"></i> Partenariat</a></li>
                    @if(Auth::check())
                        <li><a href="{{ route('dashboard') }}"><i class="bi bi-speedometer2"></i> Tableau de bord</a></li>
                    @endif
                </ul>
            </div>
        </div>
        <div class="footer-bottom">
            <span>&copy; {{ date('Y') }} PILOTIX — Gestion commerciale multi-Dépôt ou Magasin</span>
            <span>
                <a href="{{ route('conditions') }}" style="color:inherit;">Conditions</a> ·
                <a href="{{ route('confidentialite') }}" style="color:inherit;">Confidentialité</a> ·
                <a href="{{ route('partenaires') }}" style="color:inherit;">Partenariat</a>
            </span>
        </div>
    </footer>

    <!-- ===== SCRIPTS ===== -->
    <script>
        // ---- Pricing updater ----
        const OFFRES = {
            essentiel: {
                prix: {{ $rules['essentiel']?->prix ?? 0 }},
                tranche3x: {{ $rules['essentiel']?->prix_tranche_3x ?? 0 }}
            },
            professionnel: {
                prix: {{ $rules['professionnel']?->prix ?? 0 }},
                tranche3x: {{ $rules['professionnel']?->prix_tranche_3x ?? 0 }}
            },
            entreprise: {
                prix: {{ $rules['entreprise']?->prix ?? 0 }},
                tranche3x: {{ $rules['entreprise']?->prix_tranche_3x ?? 0 }}
            }
        };

        function formatFcfa(n) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' FCFA';
        }

        function updatePricing() {
            const type = document.getElementById('type_souscription')?.value || 'professionnel';
            const checkboxWrap = document.getElementById('wrap_paiement_3x');
            const checkboxInput = document.getElementById('paiement_3x');
            const is3x = checkboxInput ? checkboxInput.checked : false;

            const item = OFFRES[type] || { prix: 0, tranche3x: 0 };
            const cashPrix = item.prix;
            const tranchePrice = item.tranche3x > 0 ? item.tranche3x : Math.round(cashPrix / 3);
            const total3x = item.tranche3x > 0 ? (item.tranche3x * 3) : cashPrix;

            const label = document.getElementById('installmentLabel');
            const detail = document.getElementById('installmentDetail');
            const priceTotal = document.getElementById('priceTotal');

            if (type === 'entreprise') {
                if (checkboxWrap) checkboxWrap.style.display = 'none';
                if (checkboxInput) checkboxInput.checked = false;
                if (label) label.textContent = 'Licence à vie / Devis';
                if (detail) detail.textContent = 'Sur-mesure';
                if (priceTotal) priceTotal.textContent = formatFcfa(cashPrix);
            } else {
                if (checkboxWrap) checkboxWrap.style.display = 'flex';
                if (is3x) {
                    if (priceTotal) priceTotal.textContent = formatFcfa(total3x);
                    if (label) label.textContent = '3 × ' + formatFcfa(tranchePrice);
                    if (detail) detail.textContent = '3 × ' + formatFcfa(tranchePrice) + ' (soit ' + formatFcfa(total3x) + ')';
                } else {
                    if (priceTotal) priceTotal.textContent = formatFcfa(cashPrix);
                    if (label) label.textContent = 'Paiement 3x disponible';
                    if (detail) detail.textContent = '3 × ' + formatFcfa(tranchePrice) + (item.tranche3x > 0 ? ' (soit ' + formatFcfa(total3x) + ')' : '');
                }
            }
        }

        document.addEventListener('DOMContentLoaded', updatePricing);
        updatePricing();

        // ---- Mobile menu ----
        function toggleMenu() {
            const menu = document.getElementById('mobileMenu');
            const btn = document.getElementById('hamburger');
            const isOpen = menu.classList.contains('active');
            if (isOpen) {
                closeMenu();
            } else {
                menu.classList.add('active');
                btn.classList.add('open');
            }
        }

        function closeMenu() {
            document.getElementById('mobileMenu').classList.remove('active');
            document.getElementById('hamburger').classList.remove('open');
        }

        document.addEventListener('click', function(e) {
            const menu = document.getElementById('mobileMenu');
            const btn = document.getElementById('hamburger');
            if (!menu.contains(e.target) && !btn.contains(e.target)) {
                closeMenu();
            }
        });

        // ---- Scroll reveal ----
        const revealObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    revealObserver.unobserve(entry.target);
                }
            });
        }, { threshold: 0.05, rootMargin: '0px 0px 40px 0px' });

        document.querySelectorAll('.reveal').forEach(el => {
            if (el.getBoundingClientRect().top < window.innerHeight) {
                el.classList.add('visible');
            } else {
                revealObserver.observe(el);
            }
        });

        // ---- Active nav link on scroll ----
        const sections = document.querySelectorAll('section[id]');
        const navLinks = document.querySelectorAll('.nav-links a:not(.nav-cta)');

        function setActiveLink() {
            let current = '';
            sections.forEach(section => {
                const top = section.getBoundingClientRect().top;
                if (top <= 120) current = section.getAttribute('id');
            });
            navLinks.forEach(link => {
                link.classList.remove('active');
                if (link.getAttribute('href') === '#' + current) {
                    link.classList.add('active');
                }
            });
        }

        window.addEventListener('scroll', setActiveLink);
        setActiveLink();

        // ---- PWA install ----
        let deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
        });

        function installPWA() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function() { deferredPrompt = null; });
        }

        window.addEventListener('appinstalled', function() {
            deferredPrompt = null;
        });
    </script>



    <script>
        function openCreationModal(e, plan) {
            if (e && e.preventDefault) e.preventDefault();

            const sel = document.getElementById('type_souscription');
            if (plan && sel) {
                sel.value = plan;
                if (typeof updatePricing === 'function') updatePricing();

                // Highlight visuel : bordure colorée + fond léger sur le select
                sel.style.transition = 'box-shadow 0.3s, border-color 0.3s, background 0.3s';
                sel.style.boxShadow = '0 0 0 3px rgba(13, 107, 78, 0.35)';
                sel.style.borderColor = 'var(--primary, #0d6b4e)';
                sel.style.background = 'rgba(13, 107, 78, 0.06)';
                setTimeout(() => {
                    sel.style.boxShadow = '';
                    sel.style.borderColor = '';
                    sel.style.background = '';
                }, 2000);
            }

            // Scroll directement vers le champ select (pas juste la section)
            const target = sel || document.getElementById('contact');
            if (target) {
                setTimeout(() => {
                    target.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 80);
            }
        }
    </script>

    <link rel="manifest" href="/manifest.json" />
    <meta name="theme-color" content="#0d6b4e" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png" />
</body>
</html>