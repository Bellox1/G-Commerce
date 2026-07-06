<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>G-STOCK — Logiciel de Gestion Commerciale Multi-Magasins</title>
    <meta name="description" content="G-STOCK est une solution SaaS de gestion commerciale multi-tenant pour les PME africaines : ventes, stock, livraisons, dettes, arrivages et plus.">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #105e49;
            --primary-light: #167e65;
            --secondary: #ea8d22;
            --bg: #f4f7f6;
            --text: #1f2937;
            --muted: #6b7280;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #fff; color: var(--text); overflow-x: hidden; }

        /* ─── NAV ─── */
        nav {
            position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center;
            padding: 0 5%; min-height: 68px;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            flex-wrap: wrap; gap: 6px 0; justify-content: space-between;
        }
        .nav-logo { display: flex; align-items: center; gap: 0; text-decoration: none; flex-shrink: 0; }
        .nav-logo img { margin-top: -10px; }
        .nav-logo .icon-box { width: 36px; height: 36px; background: var(--primary); border-radius: 9px; display: flex; align-items: center; justify-content: center; }
        .nav-logo .icon-box i { color: #fff; font-size: 1.1rem; }
        .nav-logo .logo-name { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 1.4rem; color: var(--primary); letter-spacing: -1.5px; text-transform: uppercase; margin-left: -2px; }
        .nav-links-pub { display: flex; align-items: center; gap: 32px; margin-left: auto; flex-wrap: wrap; }
        .nav-links-pub a { color: var(--text); text-decoration: none; font-weight: 600; font-size: 0.92rem; white-space: nowrap; }
        .nav-links-pub a:hover, .nav-links-pub a.nav-active { color: var(--primary); }
        .btn-nav { background: var(--primary); color: #fff !important; padding: 9px 22px; border-radius: 8px; font-weight: 700; font-size: 0.9rem; transition: all .2s; }
        .btn-nav:hover { background: var(--primary-light) !important; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(16,94,73,.3); }

        /* ─── HAMBURGER BUTTON ─── */
        .hamburger {
            display: none;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 5px;
            width: 40px; height: 40px;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: 8px;
            transition: background 0.2s;
            z-index: 200;
        }
        .hamburger:hover { background: rgba(16,94,73,0.06); }
        .hamburger span {
            display: block;
            width: 24px; height: 2.5px;
            background: var(--text);
            border-radius: 99px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        /* Croix quand ouvert */
        .hamburger.open span:nth-child(1) { transform: translateY(7.5px) rotate(45deg); }
        .hamburger.open span:nth-child(2) { opacity: 0; width: 0; }
        .hamburger.open span:nth-child(3) { transform: translateY(-7.5px) rotate(-45deg); }

        /* ─── MOBILE DRAWER ─── */
        .mobile-menu {
            display: none;
            flex-direction: column;
            position: fixed;
            top: 68px; left: 0; right: 0;
            background: rgba(255,255,255,0.98);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            padding: 20px 5% 28px;
            gap: 4px;
            z-index: 99;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transform: translateY(-10px);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
        }
        .mobile-menu.active {
            display: flex;
            transform: translateY(0);
            opacity: 1;
        }
        .mobile-menu a {
            color: var(--text);
            text-decoration: none;
            font-weight: 500;
            font-size: 1rem;
            padding: 14px 16px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: background 0.2s, color 0.2s;
        }
        .mobile-menu a:hover { background: rgba(16,94,73,0.06); color: var(--primary); }
        .mobile-menu .btn-nav-mobile {
            background: var(--primary);
            color: #fff !important;
            margin-top: 8px;
            justify-content: center;
            font-weight: 700;
            border-radius: 10px;
        }
        .mobile-menu .btn-nav-mobile:hover { background: var(--primary-light); }
        .mobile-menu-divider { height: 1px; background: rgba(0,0,0,0.06); margin: 8px 0; }

        @media (max-width: 768px) {
            .nav-links-pub { display: none; }
            .hamburger { display: flex; }
        }

        /* ─── HERO ─── */
        .hero {
            min-height: 92vh;
            display: flex; align-items: center;
            background: linear-gradient(135deg, #0a3d2d 0%, #105e49 50%, #167e65 100%);
            padding: 80px 5% 60px;
            position: relative; overflow: hidden;
        }
        .hero::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .hero-inner { max-width: 1200px; margin: 0 auto; width: 100%; display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; position: relative; z-index: 1; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(234,141,34,.15); border: 1px solid rgba(234,141,34,.4); color: var(--secondary); padding: 6px 16px; border-radius: 30px; font-weight: 700; font-size: 0.78rem; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 24px; }
        .hero-title { font-family: 'Montserrat', sans-serif; font-size: 3.6rem; font-weight: 900; color: #fff; line-height: 1.25; letter-spacing: -2px; margin-bottom: 24px; }
        .arc-underline {
            position: relative;
            display: inline-block;
            color: var(--secondary);
            z-index: 1;
        }
        .arc-underline svg {
            position: absolute;
            bottom: -10px;
            left: 0;
            width: 100%;
            height: 15px;
            pointer-events: none;
        }
        .hero-desc { font-size: 1.1rem; color: rgba(255,255,255,0.8); line-height: 1.7; margin-bottom: 40px; max-width: 480px; }
        .hero-actions { display: flex; gap: 14px; flex-wrap: wrap; }
        .btn-hero-primary { display: inline-flex; align-items: center; gap: 10px; background: #fff; color: var(--primary); padding: 14px 30px; border-radius: 10px; font-weight: 800; font-size: 1rem; text-decoration: none; transition: all .3s; box-shadow: 0 8px 25px rgba(0,0,0,.15); }
        .btn-hero-primary:hover { background: var(--secondary); color: #fff; transform: translateY(-3px); box-shadow: 0 16px 35px rgba(0,0,0,.2); }
        .btn-hero-sec { display: inline-flex; align-items: center; gap: 10px; border: 2px solid rgba(255,255,255,.3); color: #fff; padding: 14px 28px; border-radius: 10px; font-weight: 700; font-size: 1rem; text-decoration: none; transition: all .3s; }
        .btn-hero-sec:hover { border-color: #fff; background: rgba(255,255,255,.1); }
        .hero-stats { display: flex; gap: 36px; margin-top: 50px; padding-top: 40px; border-top: 1px solid rgba(255,255,255,.12); }
        .hero-stat .stat-val { font-family: 'Montserrat', sans-serif; font-size: 2rem; font-weight: 900; color: var(--secondary); }
        .hero-stat .stat-lbl { font-size: 0.8rem; color: rgba(255,255,255,.6); margin-top: 2px; }

        /* Hero visual mock */
        .hero-visual { position: relative; }
        .hero-card-mockup {
            background: rgba(255,255,255,.07); border: 1px solid rgba(255,255,255,.1);
            border-radius: 20px; padding: 28px; backdrop-filter: blur(10px);
        }
        .mock-header { display: flex; align-items: center; gap: 10px; margin-bottom: 24px; }
        .mock-dot { width: 10px; height: 10px; border-radius: 50%; }
        .mock-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 16px; }
        .mock-stat { background: rgba(255,255,255,.08); border-radius: 12px; padding: 16px; }
        .mock-stat .ms-val { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1.4rem; color: #fff; }
        .mock-stat .ms-lbl { font-size: 0.72rem; color: rgba(255,255,255,.5); margin-top: 3px; }
        .mock-stat .ms-trend { font-size: 0.72rem; color: #4ade80; margin-top: 4px; font-weight: 700; }
        .mock-bar-row { display: flex; flex-direction: column; gap: 10px; }
        .mock-bar { display: flex; align-items: center; gap: 10px; }
        .mock-bar-label { font-size: 0.72rem; color: rgba(255,255,255,.55); width: 80px; flex-shrink: 0; }
        .mock-bar-track { flex: 1; height: 8px; background: rgba(255,255,255,.1); border-radius: 99px; overflow: hidden; }
        .mock-bar-fill { height: 100%; border-radius: 99px; background: linear-gradient(90deg, var(--secondary), #f97316); }

        /* ─── ENTRY OVERLAY ─── */
        #entry-overlay {
            position: fixed; inset: 0; z-index: 9999;
            background: #fff;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            transition: opacity 0.8s ease;
        }
        #entry-overlay.hide { opacity: 0; pointer-events: none; }
        .entry-svg-wrap { width: 160px; }
        .entry-svg-wrap svg { width: 100%; height: auto; display: block; }
        .entry-piece {
            opacity: 0;
            transform-origin: center bottom;
            animation: bottomToTopReveal 0.9s cubic-bezier(0.25, 1, 0.5, 1) forwards;
            animation-delay: calc(var(--i) * 0.12s);
        }
        @keyframes bottomToTopReveal {
            0% {
                opacity: 0;
                transform: translateY(120px) scale(0.85);
            }
            70% {
                opacity: 0.8;
                transform: translateY(-10px) scale(1.02);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        .entry-text {
            margin-top: 28px; text-align: center;
            opacity: 0;
            animation: textReveal 0.8s ease-out 2.5s forwards;
        }
        .entry-text-main {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900; font-size: 2.6rem;
            color: #105e49; letter-spacing: 6px; display: block;
        }
        .entry-text-sub {
            font-family: 'Inter', sans-serif;
            font-weight: 500; font-size: 0.85rem;
            color: rgba(255,255,255,.5);
            letter-spacing: 3px; text-transform: uppercase; display: block; margin-top: 6px;
        }
        @keyframes textReveal {
            0% { opacity: 0; transform: translateY(20px) scale(0.95); letter-spacing: 20px; }
            100% { opacity: 1; transform: translateY(0) scale(1); letter-spacing: 6px; }
        }

        /* ─── NOUVEAU WEB DESIGN ASYMÉTRIQUE ALTERNÉ ─── */
        .features-section {
            padding: 120px 5%;
            background: #fbfcfd;
            position: relative;
            overflow: hidden;
            width: 100%;
            box-sizing: border-box;
        }
        
        .features-header { text-align: center; margin-bottom: 80px; position: relative; z-index: 2; }
        .section-label { display: inline-block; color: var(--primary); font-weight: 800; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 16px; background: rgba(16, 94, 73, 0.1); padding: 6px 16px; border-radius: 50px; }
        .section-title { font-family: 'Montserrat', sans-serif; font-size: 2.8rem; font-weight: 900; color: var(--text); letter-spacing: -1px; margin-bottom: 20px; }
        .section-sub { font-size: 1.1rem; color: var(--muted); max-width: 600px; margin: 0 auto; line-height: 1.6; }

        /* Arcs de couleurs géants pro en background */
        .decor-arc {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            z-index: 1;
            opacity: 0.45;
        }
        .decor-arc-1 {
            width: 700px;
            height: 700px;
            border: 45px solid rgba(16, 94, 73, 0.03);
            border-top-color: rgba(16, 94, 73, 0.08);
            border-right-color: rgba(16, 94, 73, 0.05);
            top: -200px;
            right: -150px;
            transform: rotate(45deg);
        }
        .decor-arc-2 {
            width: 800px;
            height: 800px;
            border: 55px solid rgba(234, 141, 34, 0.02);
            border-bottom-color: rgba(234, 141, 34, 0.05);
            border-left-color: rgba(234, 141, 34, 0.03);
            bottom: 10%;
            left: -350px;
            transform: rotate(-15deg);
        }

        .feat-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 80px;
            max-width: 1300px;
            margin: 0 auto 120px auto;
            position: relative;
            z-index: 2;
        }
        .feat-row:last-child {
            margin-bottom: 0;
        }
        .feat-row:nth-child(even) {
            flex-direction: row-reverse;
        }
        .feat-col-text {
            flex: 1;
            max-width: 550px;
        }
        .feat-col-image {
            flex: 1.1;
            position: relative;
        }

        /* Cadres d'images modernes avec bordure courbée pro */
        .feat-image-card {
            position: relative;
            background: #ffffff;
            border-radius: 90px 24px 90px 24px;
            padding: 12px;
            box-shadow: 0 25px 50px rgba(15, 25, 35, 0.06);
            border: 4px solid var(--primary); /* Trait de couleur verte en arc */
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .feat-image-card img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-radius: 78px 18px 78px 18px;
            display: block;
        }
        .feat-row:nth-child(even) .feat-image-card {
            border-radius: 24px 90px 24px 90px;
            border-color: var(--secondary); /* Trait de couleur orange en arc */
        }
        .feat-row:nth-child(even) .feat-image-card img {
            border-radius: 18px 78px 18px 78px;
        }
        .feat-image-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 30px 60px rgba(16, 94, 73, 0.12);
        }

        /* Arc de cercle de fond stylisé décoratif */
        .feat-ring-decoration {
            position: absolute;
            width: 180px;
            height: 180px;
            border: 2px dashed rgba(16, 94, 73, 0.2);
            border-radius: 50%;
            z-index: -1;
            pointer-events: none;
        }
        .ring-tr { top: -40px; right: -40px; }
        .ring-bl { bottom: -40px; left: -40px; border-color: rgba(234, 141, 34, 0.2); }

        .feat-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(16, 94, 73, 0.07);
            color: var(--primary);
            font-weight: 800;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 8px 18px;
            border-radius: 100px;
            margin-bottom: 24px;
        }
        .feat-row:nth-child(even) .feat-badge {
            background: rgba(234, 141, 34, 0.1);
            color: var(--secondary);
        }
        .feat-title {
            font-family: 'Montserrat', sans-serif;
            font-weight: 900;
            font-size: 2.3rem;
            line-height: 1.2;
            letter-spacing: -1px;
            color: var(--text);
            margin-bottom: 20px;
        }
        .feat-desc {
            font-size: 1.05rem;
            color: var(--muted);
            line-height: 1.75;
            margin-bottom: 28px;
        }
        
        .bullet-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        .bullet-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            font-size: 0.95rem;
            color: var(--text);
            font-weight: 500;
        }
        .bullet-item i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 24px;
            height: 24px;
            background: rgba(16, 94, 73, 0.08);
            color: var(--primary);
            border-radius: 50%;
            font-size: 0.8rem;
            margin-top: 2px;
            flex-shrink: 0;
        }
        .feat-row:nth-child(even) .bullet-item i {
            background: rgba(234, 141, 34, 0.1);
            color: var(--secondary);
        }

        @media (max-width: 991px) {
            .nav-links-pub { gap: 10px; }
            .nav-links-pub a { font-size: 0.82rem; }
            .btn-nav { padding: 6px 14px; font-size: 0.8rem; }
            .feat-row {
                flex-direction: column !important;
                gap: 48px;
                margin-bottom: 80px;
            }
            .feat-col-text {
                max-width: 100%;
            }
            .feat-image-card img {
                height: 280px;
            }
        }

        /* ─── ROLES ACCORDION ─── */
        .roles { padding: 60px 5% 120px; background: #fbfcfd; position: relative; }
        .roles-inner { max-width: 1200px; margin: 0 auto; }
        .roles-accordion { margin-top: 50px; display: flex; flex-direction: column; gap: 10px; }

        .role-accordion-item {
            background: #ffffff;
            border: 1px solid rgba(0,0,0,0.06);
            border-radius: 16px;
            overflow: hidden;
            transition: box-shadow 0.3s;
        }
        .role-accordion-item:hover {
            box-shadow: 0 4px 16px rgba(16,94,73,0.06);
        }
        .role-accordion-item.active {
            box-shadow: 0 8px 24px rgba(16,94,73,0.1);
            border-color: rgba(16,94,73,0.15);
        }
        .role-accordion-header {
            display: flex;
            align-items: center;
            gap: 16px;
            width: 100%;
            padding: 18px 24px;
            background: none;
            border: none;
            cursor: pointer;
            font-family: inherit;
            font-size: 1rem;
            text-align: left;
            color: var(--text);
            transition: background 0.2s;
        }
        .role-accordion-header:hover { background: rgba(16,94,73,0.03); }
        .role-accordion-icon {
            width: 28px; height: 28px;
            background: rgba(16,94,73,0.07);
            color: var(--primary);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.8rem;
            flex-shrink: 0;
            transition: all 0.3s;
        }
        .role-accordion-item.active .role-accordion-icon {
            background: var(--primary);
            color: #fff;
        }
        .role-accordion-header h4 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            color: var(--text);
            flex: 1;
        }
        .role-accordion-chevron {
            color: var(--muted);
            font-size: 1.1rem;
            transition: transform 0.3s ease;
            flex-shrink: 0;
        }
        .role-accordion-item.active .role-accordion-chevron {
            transform: rotate(180deg);
            color: var(--primary);
        }
        .role-accordion-body {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.4s ease, padding 0.4s ease;
        }
        .role-accordion-item.active .role-accordion-body {
            max-height: 600px;
        }
        .role-accordion-content {
            padding: 0 24px 24px 68px;
            font-size: 0.95rem;
            color: var(--muted);
            line-height: 1.8;
        }
        .role-accordion-content ul {
            list-style: none;
            padding: 0; margin: 0;
            display: flex; flex-direction: column; gap: 8px;
        }
        .role-accordion-content ul li {
            position: relative;
            padding-left: 20px;
        }
        .role-accordion-content ul li::before {
            content: '';
            position: absolute; left: 0; top: 11px;
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--primary);
            opacity: 0.3;
        }
        .role-accordion-content ul li strong {
            color: var(--text);
            font-weight: 700;
        }

        /* ─── CTA ─── */
        .cta { padding: 100px 5%; background: linear-gradient(135deg, #0a3d2d, #105e49); text-align: center; position: relative; overflow: hidden; }
        .cta h2 { font-family: 'Montserrat', sans-serif; font-size: 2.8rem; font-weight: 900; color: #fff; letter-spacing: -1px; margin-bottom: 16px; }
        .cta p { font-size: 1.15rem; color: rgba(255,255,255,.75); max-width: 520px; margin: 0 auto 40px; line-height: 1.7; }
        .cta-container { max-width: 1200px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 60px; text-align: left; }
        .cta-info { flex: 1; }
        .cta-form-box { flex: 1; background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 20px; padding: 32px; backdrop-filter: blur(12px); box-shadow: 0 20px 40px rgba(0,0,0,0.1); width: 100%; max-width: 500px; }
        .cta-form-box h3 { color: #fff; font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1.4rem; margin-bottom: 24px; text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .cta-form-box .form-group { margin-bottom: 20px; text-align: left; }
        .cta-form-box .form-label { color: rgba(255, 255, 255, 0.9); font-weight: 600; font-size: 0.85rem; margin-bottom: 8px; display: block; }
        .cta-form-box .form-control { background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2); color: #fff; padding: 12px 16px; border-radius: 10px; font-size: 0.95rem; width: 100%; transition: all 0.3s; }
        .cta-form-box .form-control:focus { outline: none; border-color: var(--secondary); background: rgba(255, 255, 255, 0.18); box-shadow: 0 0 0 4px rgba(234, 141, 34, 0.15); }
        .cta-form-box .form-control::placeholder { color: rgba(255, 255, 255, 0.4); }
        .btn-cta-submit { background: var(--secondary); color: #fff; border: none; padding: 14px 28px; border-radius: 10px; font-weight: 800; font-size: 0.95rem; cursor: pointer; transition: all 0.3s; width: 100%; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 10px 20px rgba(234, 141, 34, 0.2); }
        .btn-cta-submit:hover { background: #f97316; transform: translateY(-2px); box-shadow: 0 15px 30px rgba(234, 141, 34, 0.3); }
        @media (max-width: 991px) {
            .cta-container { flex-direction: column; text-align: left; gap: 40px; }
            .cta-info { text-align: left; }
            .cta-form-box { margin: 0 auto; }
        }
        .form-row-contact { display: flex; gap: 16px; }
        @media (max-width: 640px) {
            .form-row-contact { flex-direction: column; gap: 0; }
        }

        /* ─── FOOTER ─── */
        footer { background: #0f1923; color: #fff; padding: 60px 5% 28px; }
        .footer-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 40px; flex-wrap: wrap; margin-bottom: 48px; }
        .footer-brand { flex: 1; min-width: 200px; }
        .footer-brand .logo-name { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 1.6rem; letter-spacing: -1px; text-transform: uppercase; color: #fff; }
        .footer-brand p { margin-top: 12px; color: #6b7280; font-size: 0.9rem; line-height: 1.7; max-width: 280px; }
        .footer-links { min-width: 140px; }
        .footer-links h5 { font-weight: 700; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: #9ca3af; margin-bottom: 16px; }
        .footer-links ul { list-style: none; display: flex; flex-direction: column; gap: 10px; }
        .footer-links ul li a { color: #9ca3af; text-decoration: none; font-size: 0.9rem; transition: color .2s; display: inline-flex; align-items: center; gap: 7px; max-width: 100%; }
        .footer-links ul li a:hover { color: #fff; }
        .footer-links ul li a i { flex-shrink: 0; }
        .footer-bottom { border-top: 1px solid rgba(255,255,255,.07); padding-top: 24px; display: flex; justify-content: space-between; align-items: center; gap: 16px; flex-wrap: wrap; color: #4b5563; font-size: 0.85rem; }

        /* Responsive */
        @media (max-width: 1024px) { .hero-inner { grid-template-columns: 1fr; } .hero-visual { display: none; } .hero-title { font-size: 2.8rem; } }

        @media (max-width: 768px) {
            .nav-links-pub { display: none; }
            .hamburger { display: flex; }
            .hero-title { font-size: 2.2rem; }
            .cta h2 { font-size: 2rem; }
            .hero-stats { gap: 16px; flex-wrap: wrap; }
            .hero-stat { width: calc(50% - 8px); }
            .section-title { font-size: 2rem; }
            .feat-image-card img { height: 200px; }
            .role-accordion-header { padding: 14px 16px; gap: 12px; }
            .role-accordion-content { padding: 0 16px 20px 60px; font-size: 0.9rem; }
            .role-accordion-header h4 { font-size: 1rem; }
            .footer-top { gap: 24px; }
        }
        @media (max-width: 480px) {
            .hero-title { font-size: 1.8rem; letter-spacing: -1px; }
            .hero-desc { font-size: 0.95rem; }
            .hero-stats { gap: 12px; }
            .hero-stat .stat-val { font-size: 1.5rem; }
            .section-title { font-size: 1.6rem; }
            .feat-title { font-size: 1.5rem; }
            .feat-image-card { border-radius: 40px 12px 40px 12px; padding: 8px; }
            .feat-image-card img { height: 160px; border-radius: 32px 8px 32px 8px; }
            .feat-row:nth-child(even) .feat-image-card { border-radius: 12px 40px 12px 40px; }
            .feat-row:nth-child(even) .feat-image-card img { border-radius: 8px 32px 8px 32px; }
            .role-accordion-content { padding: 0 16px 16px 52px; font-size: 0.85rem; }
            .role-accordion-header { padding: 12px 14px; gap: 10px; }
            .role-accordion-icon { width: 24px; height: 24px; font-size: 0.7rem; border-radius: 6px; }
            .role-accordion-header h4 { font-size: 0.9rem; }
            .role-accordion-chevron { font-size: 0.9rem; }
            .cta-form-box { padding: 20px; }
            .footer-top { flex-direction: column; gap: 24px; }
            .footer-bottom { flex-direction: column; text-align: center; }
        }
        @keyframes fadeSlideUp {
            from { opacity: 0; transform: translateY(40px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }
        @keyframes pulseGlow {
            0%, 100% { box-shadow: 0 0 0 0 rgba(16, 94, 73, 0.2); }
            50%      { box-shadow: 0 0 0 20px rgba(16, 94, 73, 0); }
        }
        @keyframes floatSlow {
            0%, 100% { transform: translateY(0); }
            50%      { transform: translateY(-12px); }
        }
        .anim-hero-content { animation: fadeSlideUp 0.9s ease-out both; }
        .anim-hero-visual  { animation: fadeSlideUp 0.9s ease-out 0.3s both; }
        .anim-hero-stats   { animation: fadeIn 0.8s ease-out 0.6s both; }

        .reveal {
            opacity: 0;
            transform: translateY(40px);
            transition: opacity 0.7s ease-out, transform 0.7s ease-out;
        }
        .reveal.visible {
            opacity: 1;
            transform: translateY(0);
        }
        .reveal-delay-1 { transition-delay: 0.1s; }
        .reveal-delay-2 { transition-delay: 0.2s; }
        .reveal-delay-3 { transition-delay: 0.3s; }
        .reveal-delay-4 { transition-delay: 0.4s; }

        .feat-image-card { animation: floatSlow 6s ease-in-out infinite; }
        .feat-row:nth-child(even) .feat-image-card { animation-delay: -3s; }
    </style>
</head>
<body>

    <!-- Entry Overlay -->
    <div id="entry-overlay">
        <div class="entry-svg-wrap" style="width: 170px;">
            <svg viewBox="0 0 431 775" fill="none" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <linearGradient id="paint0_linear_921_6071" x1="-0.215805" y1="0.0726477" x2="375.488" y2="375.777" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#020d0a"/>
                        <stop offset="0.99696" stop-color="#052c22"/>
                    </linearGradient>
                    <linearGradient id="paint1_linear_921_6071" x1="187.834" y1="0" x2="187.834" y2="375.668" gradientUnits="userSpaceOnUse">
                        <stop stop-color="#052c22"/>
                        <stop offset="1" stop-color="#105e49"/>
                    </linearGradient>
                </defs>

                <!-- Concentric Isometric Cubes -->
                <g class="entry-piece" style="--i: 2">
                    <rect width="375.668" height="375.668" transform="matrix(0.866025 0.5 0 1 50 210.745)" fill="url(#paint1_linear_921_6071)" stroke="#105e49" stroke-width="2"/>
                    <rect width="375.668" height="375.668" transform="matrix(0.866025 -0.5 0 1 375.34 398.579)" stroke="#105e49" stroke-width="2"/>
                    <rect width="375.668" height="375.668" transform="matrix(0.866025 0.5 -0.866025 0.5 375.34 22.9108)" fill="url(#paint0_linear_921_6071)" stroke="#105e49" stroke-width="2"/>
                </g>

                <g class="entry-piece" style="--i: 3">
                    <rect width="300.357" height="300.357" transform="matrix(0.866025 0.5 -0.866025 0.5 375.34 98.2216)" stroke="#167e65" stroke-width="1.5"/>
                    <rect width="300.357" height="300.357" transform="matrix(0.866025 0.5 0 1 115.221 248.4)" stroke="#167e65" stroke-width="1.5"/>
                    <rect width="300.357" height="300.357" transform="matrix(0.866025 -0.5 0 1 375.34 398.579)" stroke="#167e65" stroke-width="1.5"/>

                    <rect width="227.214" height="227.214" transform="matrix(0.866025 0.5 -0.866025 0.5 375.339 171.365)" stroke="#1a9e7a" stroke-width="1.5"/>
                    <rect width="227.214" height="227.214" transform="matrix(0.866025 0.5 0 1 178.566 284.972)" stroke="#1a9e7a" stroke-width="1.5"/>
                    <rect width="227.214" height="227.214" transform="matrix(0.866025 -0.5 0 1 375.339 398.579)" stroke="#1a9e7a" stroke-width="1.5"/>
                </g>

                <g class="entry-piece" style="--i: 4">
                    <rect width="159.01" height="159.01" transform="matrix(0.866025 0.5 -0.866025 0.5 375.339 239.569)" stroke="#2ec4a0"/>
                    <rect width="159.01" height="159.01" transform="matrix(0.866025 0.5 0 1 237.632 319.074)" stroke="#2ec4a0"/>
                    <rect width="159.01" height="159.01" transform="matrix(0.866025 -0.5 0 1 375.339 398.579)" stroke="#2ec4a0"/>
                </g>

                <g class="entry-piece" style="--i: 3">
                    <rect width="94.294" height="94.294" transform="matrix(0.866025 0.5 -0.866025 0.5 375.339 304.285)" fill="#167e65" stroke="#ea8d22" stroke-width="1.5"/>
                    <rect width="94.294" height="94.294" transform="matrix(0.866025 0.5 0 1 293.678 351.432)" fill="#0a3d2d" stroke="#ea8d22" stroke-width="1.5"/>
                    <rect width="94.294" height="94.294" transform="matrix(0.866025 -0.5 0 1 375.339 398.579)" fill="#105e49" stroke="#ea8d22" stroke-width="1.5"/>
                </g>

                <!-- MAGASIN (Shop storefront) at the top -->
                <g class="entry-piece" style="--i: 5">
                    <g transform="translate(111, 10) scale(1.6)">
                        <path d="M 20 20 L 120 20 L 135 40 L 5 40 Z" fill="#ea8d22" />
                        <path d="M 23 20 L 39 20 L 32 40 L 16 40 Z" fill="#ffffff" opacity="0.35"/>
                        <path d="M 60 20 L 76 20 L 71 40 L 55 40 Z" fill="#ffffff" opacity="0.35"/>
                        <path d="M 97 20 L 113 20 L 110 40 L 94 40 Z" fill="#ffffff" opacity="0.35"/>
                        <path d="M 20 20 L 120 20 L 135 40 L 5 40 Z" fill="none" stroke="#d97706" stroke-width="2"/>
                        
                        <rect x="15" y="40" width="110" height="60" rx="3" fill="#031611" stroke="#167e65" stroke-width="2.5"/>
                        <rect x="25" y="50" width="40" height="35" rx="2" fill="none" stroke="#1a9e7a" stroke-width="1.5"/>
                        <line x1="27" y1="67" x2="63" y2="67" stroke="#1a9e7a" stroke-width="1"/>
                        <rect x="30" y="54" width="8" height="11" rx="1" fill="#ea8d22" opacity="0.95"/>
                        <rect x="45" y="56" width="12" height="9" rx="1" fill="#f59e0b" opacity="0.95"/>
                        
                        <rect x="75" y="50" width="35" height="48" rx="2" fill="none" stroke="#1a9e7a" stroke-width="1.5"/>
                        <rect x="80" y="58" width="10" height="40" rx="1" fill="#167e65"/>
                        <circle cx="83" cy="78" r="1.5" fill="#ea8d22"/>
                    </g>
                </g>

                <!-- STOCK (Isometric packages stack) at the bottom-left -->
                <g class="entry-piece" style="--i: 1">
                    <g transform="translate(10, 560) scale(1.6)">
                        <path d="M 55 20 L 75 30 L 55 40 L 35 30 Z" fill="#f59e0b" stroke="#ea8d22" stroke-width="1.5"/>
                        <line x1="35" y1="30" x2="75" y2="30" stroke="#d97706" stroke-width="2" opacity="0.8"/>
                        <path d="M 35 30 L 55 40 L 55 60 L 35 50 Z" fill="#105e49" stroke="#0a3d2d" stroke-width="1.5"/>
                        <path d="M 55 40 L 75 30 L 75 50 L 55 60 Z" fill="#167e65" stroke="#105e49" stroke-width="1.5"/>

                        <path d="M 35 48 L 55 58 L 35 68 L 15 58 Z" fill="#f59e0b" stroke="#ea8d22" stroke-width="1.5"/>
                        <line x1="15" y1="58" x2="55" y2="58" stroke="#d97706" stroke-width="2" opacity="0.8"/>
                        <path d="M 15 58 L 35 68 L 35 88 L 15 78 Z" fill="#105e49" stroke="#0a3d2d" stroke-width="1.5"/>
                        <path d="M 35 68 L 55 58 L 55 78 L 35 88 Z" fill="#167e65" stroke="#105e49" stroke-width="1.5"/>

                        <path d="M 55 58 L 75 48 L 95 58 L 75 68 Z" fill="#fcd34d" stroke="#f59e0b" stroke-width="1.5"/>
                        <line x1="55" y1="58" x2="95" y2="58" stroke="#d97706" stroke-width="2" opacity="0.8"/>
                        <path d="M 75 68 L 95 58 L 95 78 L 75 88 Z" fill="#167e65" stroke="#105e49" stroke-width="1.5"/>
                    </g>
                </g>

                <!-- CHIFFRE D'AFFAIRES (Isometric growth chart) at the bottom-right -->
                <g class="entry-piece" style="--i: 2">
                    <g transform="translate(250, 565) scale(1.6)">
                        <path d="M 28 55 L 38 60 L 28 65 L 18 60 Z" fill="#f59e0b" stroke="#ea8d22" stroke-width="1"/>
                        <path d="M 18 60 L 28 65 L 28 80 L 18 75 Z" fill="#ea8d22" stroke="#d97706" stroke-width="1"/>
                        <path d="M 28 65 L 38 60 L 38 75 L 28 80 Z" fill="#d97706" stroke="#b45309" stroke-width="1"/>

                        <path d="M 48 40 L 58 45 L 48 50 L 38 45 Z" fill="#f59e0b" stroke="#ea8d22" stroke-width="1"/>
                        <path d="M 38 45 L 48 50 L 48 80 L 38 75 Z" fill="#ea8d22" stroke="#d97706" stroke-width="1"/>
                        <path d="M 48 50 L 58 45 L 58 75 L 48 80 Z" fill="#d97706" stroke="#b45309" stroke-width="1"/>

                        <path d="M 68 25 L 78 30 L 68 35 L 58 30 Z" fill="#fcd34d" stroke="#f59e0b" stroke-width="1"/>
                        <path d="M 58 30 L 68 35 L 68 80 L 58 75 Z" fill="#f59e0b" stroke="#ea8d22" stroke-width="1"/>
                        <path d="M 68 35 L 78 30 L 78 75 L 68 80 Z" fill="#ea8d22" stroke="#d97706" stroke-width="1"/>

                        <path d="M 15 65 Q 40 45 74 19" fill="none" stroke="#167e65" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <polygon points="69,18 76,17 74,24" fill="#167e65"/>
                    </g>
                </g>
            </svg>
        </div>
        <div class="entry-text">
            <span class="entry-text-main">G-STOCK</span>
            <span class="entry-text-sub">Gestion Commerciale &amp; Stock</span>
        </div>
    </div>
    <script>
        setTimeout(() => {
            document.getElementById('entry-overlay').classList.add('hide');
        }, 3600);
    </script>

    <!-- Nav -->
    <nav>
        <a href="/" class="nav-logo">
            <img src="{{ asset('logo.svg') }}" alt="G-STOCK Logo" style="height: 40px; width: 40px; object-fit: contain; border-radius: 9px;">
            <span class="logo-name">G-STOCK</span>
        </a>
        <div class="nav-links-pub">
            <a href="#fonctionnalites">Fonctionnalités</a>
            <a href="#roles">Rôles & Accès</a>
            <a href="#contact">Contact</a>
            @if (Auth::check())
                <a href="{{ route('dashboard') }}" class="btn-nav"><i class="bi bi-box-arrow-in-right"></i> Tableau de bord</a>
            @else
                <a href="{{ route('login') }}" class="btn-nav"><i class="bi bi-box-arrow-in-right"></i> Connexion</a>
            @endif
        </div>
        <button class="hamburger" id="hamburger" onclick="toggleMenu()" aria-label="Menu">
            <span></span><span></span><span></span>
        </button>
    </nav>

    <!-- Menu Mobile (Tiroir) -->
    <div class="mobile-menu" id="mobileMenu">
        <a href="#fonctionnalites" onclick="closeMenu()"><i class="bi bi-grid-1x2"></i> Fonctionnalités</a>
        <a href="#roles" onclick="closeMenu()"><i class="bi bi-people"></i> Rôles & Accès</a>
        <a href="#contact" onclick="closeMenu()"><i class="bi bi-chat-dots"></i> Contact</a>
        <div class="mobile-menu-divider"></div>
        @if (Auth::check())
            <a href="{{ route('dashboard') }}" class="btn-nav-mobile"><i class="bi bi-box-arrow-in-right"></i> Tableau de bord</a>
        @else
            <a href="{{ route('login') }}" class="btn-nav-mobile"><i class="bi bi-box-arrow-in-right"></i> Se connecter</a>
        @endif
    </div>

    <!-- Hero -->
    <section class="hero">
        <div class="hero-inner">
            <div class="hero-content anim-hero-content">
                 <h1 class="hero-title">Gérez factures, ventes, livraisons et <span class="arc-underline">stock<svg viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0,5 Q50,10 100,5" stroke="var(--secondary)" stroke-width="4" fill="none" stroke-linecap="round"/></svg></span> en temps réel</h1>
                <p class="hero-desc">L'outil indispensable pour votre commerce : G-STOCK gère intelligemment votre stock, déclenche des alertes avant rupture, centralise la facturation et offre un suivi commercial complet pour propulser vos activités.</p>
                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn-hero-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Accéder à mon espace
                    </a>
                    <a href="#fonctionnalites" class="btn-hero-sec">
                        Voir les fonctionnalités <i class="bi bi-arrow-down"></i>
                    </a>
                </div>
                <div class="hero-stats anim-hero-stats">
                    <div class="hero-stat"><div class="stat-val">4</div><div class="stat-lbl">Rôles métier</div></div>
                    <div class="hero-stat"><div class="stat-val">∞</div><div class="stat-lbl">Sociétés</div></div>
                    <div class="hero-stat"><div class="stat-val">100%</div><div class="stat-lbl">Multi-magasins</div></div>
                </div>
            </div>
            <div class="hero-visual anim-hero-visual">
                <div class="hero-card-mockup">
                    <div class="mock-header">
                        <div class="mock-dot" style="background:#ef4444;"></div>
                        <div class="mock-dot" style="background:#f59e0b;"></div>
                        <div class="mock-dot" style="background:#22c55e;"></div>
                        <span style="margin-left:8px; font-size:.75rem; color:rgba(255,255,255,.4);">Dashboard G-STOCK</span>
                    </div>
                    <div class="mock-stat-grid">
                        <div class="mock-stat">
                            <div class="ms-val">32 000 000</div>
                            <div class="ms-lbl">Ventes du jour (FCFA)</div>
                            <div class="ms-trend">↑ +12% vs hier</div>
                        </div>
                        <div class="mock-stat">
                            <div class="ms-val">47</div>
                            <div class="ms-lbl">Transactions</div>
                            <div class="ms-trend">↑ +5 aujourd'hui</div>
                        </div>
                        <div class="mock-stat">
                            <div class="ms-val">3</div>
                            <div class="ms-lbl">Alertes stock bas</div>
                            <div class="ms-trend" style="color:#f97316;">⚠ À réapprovisionner</div>
                        </div>
                        <div class="mock-stat">
                            <div class="ms-val">12</div>
                            <div class="ms-lbl">Livraisons en attente</div>
                            <div class="ms-trend">🚚 En cours</div>
                        </div>
                    </div>
                    <div class="mock-bar-row">
                        <div class="mock-bar">
                            <span class="mock-bar-label">Savon GLIC</span>
                            <div class="mock-bar-track"><div class="mock-bar-fill" style="width:82%;"></div></div>
                        </div>
                        <div class="mock-bar">
                            <span class="mock-bar-label">Riz 5kg</span>
                            <div class="mock-bar-track"><div class="mock-bar-fill" style="width:65%;"></div></div>
                        </div>
                        <div class="mock-bar">
                            <span class="mock-bar-label">Huile Coco</span>
                            <div class="mock-bar-track"><div class="mock-bar-fill" style="width:47%;"></div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section Asymétrique Alternée -->
    <section id="fonctionnalites" class="features-section">
        
        <!-- Arcs géants stylisés en arrière-plan -->
        <div class="decor-arc decor-arc-1"></div>
        <div class="decor-arc decor-arc-2"></div>

        <div class="features-header reveal">
            <div class="section-label reveal reveal-delay-1">Ce qu'G-STOCK fait pour vous</div>
            <h2 class="section-title reveal reveal-delay-2">Un outil complet, pensé pour le <span class="arc-underline" style="color: var(--primary);">Marché local et Magasins<svg viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0,5 Q50,10 100,5" stroke="var(--secondary)" stroke-width="4" fill="none" stroke-linecap="round"/></svg></span></h2>
            <p class="section-sub reveal reveal-delay-3">Découvrez comment chaque module structure et sécurise vos processus commerciaux quotidiens.</p>
        </div>

        <!-- Rangée 1 : Gestion de Stock (Image à droite, Texte à gauche) -->
        <div class="feat-row reveal">
            <div class="feat-col-text">
                <span class="feat-badge"><i class="bi bi-box-seam"></i> Logistique</span>
                <h3 class="feat-title">Suivi des stocks en temps réel par dépôt</h3>
                <p class="feat-desc">Ne soyez plus jamais pris au dépourvu. Suivez l’état de vos produits de façon globale et par point de stockage physique. G-STOCK calcule automatiquement les niveaux critiques pour vous alerter en cas de stock bas.</p>
                <div class="bullet-list">
                    <div class="bullet-item"><i class="bi bi-check2"></i> Multi-dépôts : gestion indépendante par magasin physique.</div>
                    <div class="bullet-item"><i class="bi bi-check2"></i> Seuils d'alerte : notification dynamique avant rupture.</div>
                    <div class="bullet-item"><i class="bi bi-check2"></i> Historique : traçabilité complète de chaque entrée/sortie.</div>
                </div>
            </div>
            <div class="feat-col-image">
                <div class="feat-ring-decoration ring-tr"></div>
                <div class="feat-image-card">
                    <img src="https://images.unsplash.com/photo-1553413077-190dd305871c?w=800&auto=format&fit=crop&q=80" alt="Gestion de Stock">
                </div>
            </div>
        </div>

        <!-- Rangée 2 : Ventes (Image à gauche, Texte à droite) -->
        <div class="feat-row reveal">
            <div class="feat-col-text">
                <span class="feat-badge"><i class="bi bi-receipt"></i> Facturation & Résultats</span>
                <h3 class="feat-title">Savoir exactement combien vous vendez</h3>
                <p class="feat-desc">Enregistrez vos ventes en un clin d'œil et laissez le système analyser vos performances. Sachez combien vous vendez par jour, et suivez l'évolution précise de votre chiffre d'affaires toutes les semaines, chaque mois ou à l'année.</p>
                <div class="bullet-list">
                    <div class="bullet-item"><i class="bi bi-check2"></i> Tableaux de bord : votre chiffre d'affaires quotidien en un clin d'œil.</div>
                    <div class="bullet-item"><i class="bi bi-check2"></i> Règlements fluides : encaissement d'acomptes et calcul des restes à payer.</div>
                    <div class="bullet-item"><i class="bi bi-check2"></i> Historique complet : analysez vos performances hebdos, mensuelles ou annuelles.</div>
                </div>
            </div>
            <div class="feat-col-image">
                <div class="feat-ring-decoration ring-bl"></div>
                <div class="feat-image-card">
                    <img src="https://images.unsplash.com/photo-1556742049-0cfed4f6a45d?w=800&auto=format&fit=crop&q=80" alt="Ventes & Facturation">
                </div>
            </div>
        </div>

        <!-- Rangée 3 : Livraisons (Image à droite, Texte à gauche) -->
        <div class="feat-row reveal">
            <div class="feat-col-text">
                <span class="feat-badge"><i class="bi bi-truck"></i> Expédition</span>
                <h3 class="feat-title">Suivi de livraison et interface livreur</h3>
                <p class="feat-desc">Pilotez vos flux d'expéditions depuis votre console centrale. Vos agents livreurs accèdent à une interface épurée sur le terrain pour repérer les commandes à livrer, appeler le client en un clic et mettre à jour le statut.</p>
                <div class="bullet-list">
                    <div class="bullet-item"><i class="bi bi-check2"></i> Bons de livraison détaillés avec contenu exact et contacts.</div>
                    <div class="bullet-item"><i class="bi bi-check2"></i> Statut en temps réel : livré, en cours, ou problème signalé.</div>
                    <div class="bullet-item"><i class="bi bi-check2"></i> Responsabilité : historique nominatif de l'expédition.</div>
                </div>
            </div>
            <div class="feat-col-image">
                <div class="feat-ring-decoration ring-tr"></div>
                <div class="feat-image-card">
                    <img src="https://images.unsplash.com/photo-1566576721346-d4a3b4eaeb55?w=800&auto=format&fit=crop&q=80" alt="Suivi des Livraisons">
                </div>
            </div>
        </div>

        <!-- Rangée 4 : Dettes & SaaS (Image à gauche, Texte à droite) -->
        <div class="feat-row reveal">
            <div class="feat-col-text">
                <span class="feat-badge"><i class="bi bi-cash-stack"></i> Finance & SaaS</span>
                <h3 class="feat-title">Régularisation des dettes et Multi-Sociétés</h3>
                <p class="feat-desc">Contrôlez les encours financiers de votre clientèle et prévenez les impayés. Au niveau global, l’infrastructure SaaS robuste isole strictement les données de chaque société pour un déploiement fluide en marque blanche.</p>
                <div class="bullet-list">
                    <div class="bullet-item"><i class="bi bi-check2"></i> Dettes clients : enregistrement de chaque versement de solde.</div>
                    <div class="bullet-item"><i class="bi bi-check2"></i> Échéanciers : suivi précis des retards par client.</div>
                    <div class="bullet-item"><i class="bi bi-check2"></i> Multi-Tenant : étanchéité complète des données par société.</div>
                </div>
            </div>
            <div class="feat-col-image">
                <div class="feat-ring-decoration ring-bl"></div>
                <div class="feat-image-card">
                    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&auto=format&fit=crop&q=80" alt="Gestion des Dettes & SaaS">
                </div>
            </div>
        </div>
    </section>

    <!-- Roles -->
    <section id="roles" class="roles">
        <div class="roles-inner">
            <div style="text-align:center;" class="reveal">
                <div class="section-label reveal reveal-delay-1">Organisation des accès</div>
                <h2 class="section-title reveal reveal-delay-2">Un rôle précis pour chaque acteur</h2>
                <p class="section-sub reveal reveal-delay-3" style="margin:0 auto; max-width:650px;">Chaque utilisateur n'accède qu'aux fonctionnalités de son rôle. Les rôles secondaires permettent plus de flexibilité sans compromettre la sécurité de votre base de données.</p>
            </div>
            
            <div class="roles-accordion">
                <div class="role-accordion-item active reveal reveal-delay-1" onclick="toggleRole(this)">
                    <button class="role-accordion-header">
                        <span class="role-accordion-icon"><i class="bi bi-person-badge-fill"></i></span>
                        <h4>DG de la société</h4>
                        <span class="role-accordion-chevron"><i class="bi bi-chevron-down"></i></span>
                    </button>
                    <div class="role-accordion-body">
                        <div class="role-accordion-content">
                            <ul>
                                <li><strong>Pilotez</strong> votre entreprise avec une vue consolidée : chiffre d'affaires, marges, tendances et rentabilité par dépôt</li>
                                <li><strong>Déléguez</strong> en toute confiance grâce aux permissions granulaires — chaque employé n'accède qu'à son périmètre</li>
                                <li><strong>Décidez</strong> en temps réel avec des rapports précis : stocks, ventes, dettes, dépenses du jour et du mois</li>
                                <li><strong>Maîtrisez</strong> votre catalogue et votre politique tarifaire avec une vision stratégique</li>
                                <li><strong>Supervisez</strong> l'ensemble des opérations sensibles : validation des transactions et suivi des créances clients</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="role-accordion-item reveal reveal-delay-2" onclick="toggleRole(this)">
                    <button class="role-accordion-header">
                        <span class="role-accordion-icon"><i class="bi bi-cart-check-fill"></i></span>
                        <h4>Vendeur</h4>
                        <span class="role-accordion-chevron"><i class="bi bi-chevron-down"></i></span>
                    </button>
                    <div class="role-accordion-body">
                        <div class="role-accordion-content">
                            <ul>
                                <li><strong>Concentrez-vous</strong> sur vos clients : encaissez, facturez et rendez service sans vous perdre dans la paperasse</li>
                                <li><strong>Fidélisez</strong> votre clientèle grâce au crédit client intégré et au suivi des versements</li>
                                <li><strong>Gagnez</strong> du temps : sélection rapide des produits, calcul automatique du total et de la monnaie</li>
                                <li><strong>Suivez</strong> votre propre performance : historique de vos ventes, objectifs et évolution jour par jour</li>
                                <li><strong>Rassurez</strong> vos clients avec des factures et reçus professionnels imprimables</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="role-accordion-item reveal reveal-delay-3" onclick="toggleRole(this)">
                    <button class="role-accordion-header">
                        <span class="role-accordion-icon"><i class="bi bi-box-seam-fill"></i></span>
                        <h4>Magasinier</h4>
                        <span class="role-accordion-chevron"><i class="bi bi-chevron-down"></i></span>
                    </button>
                    <div class="role-accordion-body">
                        <div class="role-accordion-content">
                            <ul>
                                <li><strong>Garantissez</strong> la disponibilité des produits grâce aux alertes de stock bas et au réapprovisionnement automatique</li>
                                <li><strong>Maîtrisez</strong> les entrées et sorties : à chaque mouvement son historique pour une traçabilité sans faille</li>
                                <li><strong>Organisez</strong> vos stocks entre plusieurs dépôts avec les transferts inter-magasins en un clic</li>
                                <li><strong>Anticipez</strong> les ruptures avec des inventaires physiques et des ajustements précis</li>
                                <li><strong>Collaborez</strong> efficacement : peut cumuler le rôle secondaire de livreur pour les urgences terrain</li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="role-accordion-item reveal reveal-delay-4" onclick="toggleRole(this)">
                    <button class="role-accordion-header">
                        <span class="role-accordion-icon"><i class="bi bi-truck-front-fill"></i></span>
                        <h4>Livreur</h4>
                        <span class="role-accordion-chevron"><i class="bi bi-chevron-down"></i></span>
                    </button>
                    <div class="role-accordion-body">
                        <div class="role-accordion-content">
                            <ul>
                                <li><strong>Optimisez</strong> votre tournée : repérez les livraisons du jour groupées par zone avec les coordonnées complètes</li>
                                <li><strong>Gagnez</strong> un temps précieux : validez une livraison en un clic depuis votre mobile</li>
                                <li><strong>Communiquez</strong> directement avec le client depuis l'application pour confirmer les rendez-vous</li>
                                <li><strong>Signalez</strong> les incidents en temps réel : client absent, adresse erronée, colis endommagé</li>
                                <li><strong>Suivez</strong> votre historique de tournées et votre productivité jour par jour</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA & Formulaire de contact -->
    <section class="cta" id="contact">
        <div class="cta-container">
            <div class="cta-info reveal">
                <h2>Prêt à propulser votre commerce ?</h2>
                <p style="margin-bottom: 24px; max-width: 100%;">Faites une demande de création de société dès aujourd'hui. Remplissez ce formulaire et configurez votre espace de vente multi-magasins en quelques minutes.</p>
                <div style="display:flex; flex-direction:column; gap:16px; margin-bottom:30px; color:rgba(255,255,255,0.9); font-size:0.95rem;">
                    <div style="display:flex; align-items:flex-start; gap:12px;"><i class="bi bi-patch-check-fill" style="color:var(--secondary); font-size:1.2rem; flex-shrink:0; margin-top:2px;"></i> <span style="word-break:break-word;">Étancheité complète de vos données</span></div>
                    <div style="display:flex; align-items:flex-start; gap:12px;"><i class="bi bi-patch-check-fill" style="color:var(--secondary); font-size:1.2rem; flex-shrink:0; margin-top:2px;"></i> <span style="word-break:break-word;">Accompagnement personnalisé pour l'intégration</span></div>
                    <div style="display:flex; align-items:flex-start; gap:12px;"><i class="bi bi-patch-check-fill" style="color:var(--secondary); font-size:1.2rem; flex-shrink:0; margin-top:2px;"></i> <span style="word-break:break-word;">Suivi des stocks en temps réel et facturation pro</span></div>
                </div>
                <a href="{{ route('login') }}" class="btn-hero-sec" style="display:inline-flex;">
                    <i class="bi bi-box-arrow-in-right"></i> J'ai déjà un compte
                </a>
            </div>

            <div class="cta-form-box reveal reveal-delay-2">
                <h3>Créer ma société</h3>

                @if(session('success'))
                    <div style="background: rgba(22, 163, 74, 0.2); border: 1px solid #16a34a; padding: 14px; border-radius: 10px; color: #4ade80; font-size: 0.9rem; margin-bottom: 20px; font-weight: 500;">
                        <i class="bi bi-check-circle-fill" style="margin-right: 6px;"></i> {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="nom_societe">Nom de la société *</label>
                        <input type="text" name="nom_societe" id="nom_societe" class="form-control" placeholder="Ex: Mon Entreprise SARL" required value="{{ old('nom_societe') }}">
                    </div>

                    <div class="form-row-contact">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="localisation">Pays / Localisation *</label>
                            <input type="text" name="localisation" id="localisation" class="form-control" placeholder="Ex: Bénin" required value="{{ old('localisation') }}">
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="ville">Ville *</label>
                            <input type="text" name="ville" id="ville" class="form-control" placeholder="Ex: Cotonou" required value="{{ old('ville') }}">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="telephone">Numéro de téléphone *</label>
                        <input type="text" name="telephone" id="telephone" class="form-control" placeholder="Ex: +229 97 00 00 00" required value="{{ old('telephone') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="secteurs_activite">Secteurs d'activité *</label>
                        <input type="text" name="secteurs_activite" id="secteurs_activite" class="form-control" placeholder="Ex: Import/Export, Distribution, Vente au détail" required value="{{ old('secteurs_activite') }}">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Adresse email de contact *</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Ex: contact@entreprise.com" required value="{{ old('email') }}">
                    </div>

                    <button type="submit" class="btn-cta-submit">
                        <i class="bi bi-send-fill"></i> Envoyer ma demande
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer style="background:#ffffff; border-top: 1px solid #e5e7eb;">
        <div class="footer-top">
            <div class="footer-brand">
                <div style="display:flex; align-items:flex-start; gap:0; margin-bottom:10px;">
                    <img src="{{ asset('logo.svg') }}" alt="G-STOCK Logo" style="height: 40px; width: 40px; object-fit: contain; border-radius: 9px;">
                    <span class="logo-name" style="color: var(--primary); letter-spacing:-1.5px; margin-left:-2px;">G-STOCK</span>
                </div>
                <p style="color: #6b7280;">Solution de gestion commerciale multi-tenant pour les PME d'Afrique de l'Ouest.</p>
            </div>
            <div class="footer-links">
                <h5 style="color: #374151;">Fonctionnalités</h5>
                <ul>
                    <li><a href="#fonctionnalites" style="color: #374151;"><i class="bi bi-receipt"></i>Ventes & Facturation</a></li>
                    <li><a href="#fonctionnalites" style="color: #374151;"><i class="bi bi-box-seam"></i>Stock & Arrivages</a></li>
                    <li><a href="#fonctionnalites" style="color: #374151;"><i class="bi bi-truck"></i>Livraisons</a></li>
                    <li><a href="#fonctionnalites" style="color: #374151;"><i class="bi bi-cash-stack"></i>Dettes Clients</a></li>
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
                    <li><a href="#roles" style="color: #374151;"><i class="bi bi-shield-lock"></i>Rôles & Permissions</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom" style="border-top: 1px solid #e5e7eb; color: #6b7280;">
            <span>© {{ date('Y') }} G-STOCK — Gestion commerciale multi-tenant</span>
            <span>Tous droits réservés</span>
        </div>
    </footer>
<script>
    // ─── Roles accordion toggle ───
    function toggleRole(el) {
        const isActive = el.classList.contains('active');
        document.querySelectorAll('.role-accordion-item').forEach(item => item.classList.remove('active'));
        if (!isActive) el.classList.add('active');
    }

    // ─── Intersection Observer for scroll reveals ───
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                revealObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.15, rootMargin: '0px 0px -40px 0px' });

    document.querySelectorAll('.reveal').forEach(el => revealObserver.observe(el));

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
    // Fermer si on clique en dehors
    document.addEventListener('click', function(e) {
        const menu = document.getElementById('mobileMenu');
        const btn = document.getElementById('hamburger');
        if (!menu.contains(e.target) && !btn.contains(e.target)) {
            closeMenu();
        }
    });

    // ─── Lien actif selon section visible ───
    const sections = document.querySelectorAll('section[id]');
    const navLinks = document.querySelectorAll('.nav-links-pub a:not(.btn-nav)');

    function setActiveLink() {
        let current = '';
        sections.forEach(section => {
            const top = section.getBoundingClientRect().top;
            if (top <= 120) {
                current = section.getAttribute('id');
            }
        });
        navLinks.forEach(link => {
            link.classList.remove('nav-active');
            if (link.getAttribute('href') === '#' + current) {
                link.classList.add('nav-active');
            }
        });
    }

    window.addEventListener('scroll', setActiveLink);
    setActiveLink();
</script>
</body>
</html>
