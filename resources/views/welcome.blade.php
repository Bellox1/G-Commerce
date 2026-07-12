<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilotix — Logiciel de Gestion Commerciale Multi-Magasins</title>
    <meta name="description" content="Pilotix est une solution SaaS de gestion commerciale multi-tenant pour les PME africaines : ventes, stock, livraisons, dettes, arrivages et plus.">
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

        /* ─── TOP BANNER ─── */
        .top-banner {
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center; gap: 16px;
            padding: 8px 5%; font-size: 0.82rem; font-weight: 500;
        }
        .top-banner a { color: #fff; text-decoration: none; display: inline-flex; align-items: center; gap: 5px; }
        .top-banner a:hover { opacity: .85; }
        .top-banner span { opacity: .3; }

        /* ─── NAV ─── */
        nav {
            position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center;
            padding: 0 5%; min-height: 68px;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            flex-wrap: wrap; gap: 6px 0; justify-content: space-between;
        }
        .nav-logo { display: flex; align-items: center; text-decoration: none; flex-shrink: 0; }
        .nav-logo img { height: 56px; width: 56px; object-fit: contain; border-radius: 12px; }
        .nav-links-pub { display: flex; align-items: center; gap: 32px; margin-left: auto; flex-wrap: wrap; }
        .nav-links-pub a { color: var(--text); text-decoration: none; font-weight: 600; font-size: 0.92rem; white-space: nowrap; }
        .nav-links-pub a:hover, .nav-links-pub a.nav-active { color: var(--primary); }
        .btn-nav-phone { background: var(--primary); color: #fff !important; padding: 10px 18px; border-radius: 10px; font-weight: 600; font-size: 0.88rem; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: all 0.3s; white-space: nowrap; }
        .btn-nav-phone:hover { background: var(--primary-light); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(16,94,73,.3); }
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
        .hero-inner { width: 100%; display: grid; grid-template-columns: 2fr 1fr; gap: 60px; align-items: center; position: relative; z-index: 1; }
        .hero-badge { display: inline-flex; align-items: center; gap: 8px; background: rgba(234,141,34,.15); border: 1px solid rgba(234,141,34,.4); color: var(--secondary); padding: 6px 16px; border-radius: 30px; font-weight: 700; font-size: 0.78rem; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 24px; }
        .hero-title { font-family: 'Montserrat', sans-serif; font-size: 3rem; font-weight: 900; color: #fff; line-height: 1.45; letter-spacing: -2px; margin-bottom: 24px; }
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
        .hero-desc { font-size: 1.1rem; color: rgba(255,255,255,0.8); line-height: 1.7; margin-bottom: 40px; }
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

        .phone-frame {
            position: relative;
            width: 280px;
            margin: 0 auto;
            background: #1a1a2e;
            border-radius: 36px;
            padding: 12px;
            box-shadow: 0 25px 60px rgba(0,0,0,.4), inset 0 0 0 2px rgba(255,255,255,.1);
        }
        .phone-notch {
            position: absolute;
            top: 12px;
            left: 50%;
            transform: translateX(-50%);
            width: 100px;
            height: 24px;
            background: #1a1a2e;
            border-radius: 0 0 16px 16px;
            z-index: 2;
        }
        .phone-notch::after {
            content: '';
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 40px;
            height: 4px;
            background: rgba(255,255,255,.15);
            border-radius: 99px;
        }
        .phone-screen {
            border-radius: 28px;
            overflow: hidden;
            background: #0a3d2d;
            aspect-ratio: 9/16;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .phone-screen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .tablet-frame {
            position: relative;
            width: 360px;
            background: #1a1a2e;
            border-radius: 24px;
            padding: 14px;
            box-shadow: 0 25px 60px rgba(0,0,0,.4), inset 0 0 0 2px rgba(255,255,255,.1);
        }
        .tablet-camera {
            position: absolute;
            top: 14px;
            left: 50%;
            transform: translateX(-50%);
            width: 8px;
            height: 8px;
            background: rgba(255,255,255,.12);
            border-radius: 50%;
            z-index: 2;
        }
        .tablet-screen {
            border-radius: 16px;
            overflow: hidden;
            background: #0a3d2d;
            aspect-ratio: 4/3;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tablet-screen img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ─── MULTI-DEVICE SECTION ─── */
        .multi-device-section {
            padding: 80px 5%;
            background: transparent;
            text-align: center;
        }
        .multi-device-inner { max-width: 1100px; margin: 0 auto; }
        .devices-showcase {
            display: flex;
            align-items: flex-end;
            justify-content: center;
            gap: 30px;
            max-width: 100%;
            overflow: hidden;
        }

        /* PC */
        .pc-frame { text-align: center; flex-shrink: 0; }
        .pc-screen {
            width: 520px;
            max-width: 520px;
            height: 320px;
            background: #1a1a2e;
            border-radius: 12px 12px 0 0;
            border: 4px solid #2a2a3e;
            border-bottom: none;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .pc-screen img { width: 100%; height: 100%; object-fit: cover; }
        .pc-stand {
            width: 100px;
            height: 40px;
            margin: 0 auto;
            background: #2a2a3e;
            clip-path: polygon(15% 0%, 85% 0%, 100% 100%, 0% 100%);
        }

        /* Tablette large */
        .tablet-frame-lg {
            position: relative;
            width: 200px;
            background: #1a1a2e;
            border-radius: 20px;
            padding: 10px;
            box-shadow: 0 15px 40px rgba(0,0,0,.3), inset 0 0 0 2px rgba(255,255,255,.08);
        }
        .tablet-screen-lg {
            border-radius: 14px;
            overflow: hidden;
            background: #0a3d2d;
            aspect-ratio: 3/4;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .tablet-screen-lg img { width: 100%; height: 100%; object-fit: cover; }

        /* Téléphone petit */
        .phone-frame-sm {
            position: relative;
            width: 120px;
            background: #1a1a2e;
            border-radius: 22px;
            padding: 8px;
            box-shadow: 0 15px 40px rgba(0,0,0,.3), inset 0 0 0 2px rgba(255,255,255,.08);
        }
        .phone-notch-sm {
            position: absolute;
            top: 8px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 14px;
            background: #1a1a2e;
            border-radius: 0 0 10px 10px;
            z-index: 2;
        }
        .phone-screen-sm {
            border-radius: 18px;
            overflow: hidden;
            background: #0a3d2d;
            aspect-ratio: 9/16;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .phone-screen-sm img { width: 100%; height: 100%; object-fit: cover; }

        /* ─── ENTRY OVERLAY ─── */
        #entry-overlay {
            position: fixed; inset: 0; z-index: 9999;
            background: #fff;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
            transition: opacity 0.8s ease;
        }
        #entry-overlay.hide { opacity: 0; pointer-events: none; }
        .entry-logo-wrap {
            width: 150px; height: 150px;
            border-radius: 32px; overflow: hidden;
            animation: logoEntryAnim 1s cubic-bezier(0.25,1,0.5,1) both;
            box-shadow: 0 8px 40px rgba(16,94,73,.3);
        }
        .entry-logo-wrap img { width: 100%; height: 100%; object-fit: cover; }
        @keyframes logoEntryAnim {
            0%   { opacity: 0; transform: scale(0.5) translateY(40px); }
            70%  { opacity: 1; transform: scale(1.06) translateY(-5px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
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
            gap: 40px;
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
            border: none;
            background: transparent;
            padding: 0;
            border-radius: 0;
            box-shadow: none;
            max-width: 100%;
        }
        .feat-row:nth-child(even) .feat-image-card img {
            border-radius: 0;
            aspect-ratio: 1 / 1;
            width: 100%;
            display: block;
        }
        .feat-row:nth-child(even) .feat-col-image {
            flex: 1.3;
            margin: 0 0 0 40px;
            padding: 0;
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
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-radius: 14px;
            display: block;
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
        .cta { padding: 100px 5%; background: url('https://i.pinimg.com/736x/6a/cd/1b/6acd1b1405369a5b0457877eed1dc42d.jpg') center/cover no-repeat; text-align: center; position: relative; overflow: hidden; }
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
        .cta-form-box select.form-control option { background: #1f2937; color: #fff; padding: 8px; }
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
        @media (max-width: 1024px) {
            .hero-inner { grid-template-columns: 1fr; }
            .hero-visual { display: none; }
            .hero-title { font-size: 2.8rem; }
            .devices-showcase { flex-direction: column; align-items: center; gap: 20px; }
            .pc-frame { width: 80%; max-width: 480px; }
            .pc-screen { width: 100%; height: auto; aspect-ratio: 16/10; border-width: 3px; }
            .pc-stand { width: 80px; height: 28px; }
            .tablet-frame-lg { width: 160px; }
            .phone-frame-sm { width: 100px; }
        }

        @media (max-width: 768px) {
            .nav-links-pub { display: none; }
            .hamburger { display: flex; }
            .hero-title { font-size: 2.2rem; }
            .cta h2 { font-size: 2rem; }
            .hero-stats { gap: 16px; flex-wrap: wrap; }
            .hero-stat { width: calc(50% - 8px); }
            .section-title { font-size: 2rem; }
            .feat-image-card img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-radius: 14px;
            display: block;
        }
            .feat-row:nth-child(even) .feat-image-card img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-radius: 14px;
            display: block;
        }
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
            .feat-image-card {
            position: relative;
            background: #ffffff;
            border-radius: 20px;
            padding: 8px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(16, 94, 73, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
            .feat-image-card img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-radius: 14px;
            display: block;
        }
            .feat-row:nth-child(even) .feat-image-card {
            position: relative;
            background: #ffffff;
            border-radius: 20px;
            padding: 8px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(16, 94, 73, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
            .feat-row:nth-child(even) .feat-image-card img {
            width: 100%;
            height: 380px;
            object-fit: cover;
            border-radius: 14px;
            display: block;
        }
            .pc-screen { width: 320px; height: auto; aspect-ratio: 16/10; }
            .pc-stand { width: 70px; height: 28px; }
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
        @keyframes logoEntry {
            0%   { opacity: 0; transform: scale(0.6) translateY(30px); }
            60%  { opacity: 1; transform: scale(1.05) translateY(-4px); }
            100% { opacity: 1; transform: scale(1) translateY(0); }
        }
        @keyframes logoGlow {
            0%, 100% { box-shadow: 0 8px 32px rgba(16,94,73,.25), 0 0 60px rgba(234,141,34,.08); }
            50%      { box-shadow: 0 12px 40px rgba(16,94,73,.35), 0 0 80px rgba(234,141,34,.15); }
        }
        .anim-hero-content { animation: fadeSlideUp 0.9s ease-out both; }
        .anim-hero-visual  { animation: fadeSlideUp 0.9s ease-out 0.3s both; }
        .anim-hero-stats   { animation: fadeIn 0.8s ease-out 0.6s both; }
        .hero-logo-wrap {
            display: flex; align-items: center; justify-content: center;
            width: 180px; height: 180px; margin: 0 auto;
            border-radius: 40px; overflow: hidden;
            animation: logoEntry 1s ease-out 0.5s both, logoGlow 3s ease-in-out infinite 1.5s;
            background: #fff; border: 3px solid rgba(255,255,255,.15);
        }
        .hero-logo-wrap img { width: 100%; height: 100%; object-fit: cover; }

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

        .feat-image-card {
            position: relative;
            background: #ffffff;
            border-radius: 20px;
            padding: 8px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(16, 94, 73, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .feat-row:nth-child(even) .feat-image-card {
            position: relative;
            background: #ffffff;
            border-radius: 20px;
            padding: 8px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(16, 94, 73, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
    </style>
</head>
<body>

    <!-- Entry Overlay -->
    <div id="entry-overlay">
        <div class="entry-logo-wrap">
            <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix">
        </div>
        <div class="entry-text">
            <span class="entry-text-main">Pilotix</span>
            <span class="entry-text-sub">Gestion Commerciale &amp; Stock</span>
        </div>
    </div>
    <script>
        setTimeout(() => {
            document.getElementById('entry-overlay').classList.add('hide');
        }, 3600);
    </script>

    <!-- Top Banner -->
    <div class="top-banner">
        <a href="tel:+2290146862536"><i class="bi bi-telephone"></i> +229 01 46 86 25 36</a>
        <span>|</span>
        <a href="mailto:pilotrix@gmail.com"><i class="bi bi-envelope"></i> pilotrix@gmail.com</a>
    </div>

    <!-- Nav -->
    <nav>
        <a href="/" class="nav-logo">
            <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix Logo">
        </a>
        <div class="nav-links-pub">
            <a href="#fonctionnalites">Fonctionnalités</a>
            <a href="#roles">Rôles & Accès</a>
            <a href="#offres">Tarif</a>
            <a href="{{ route('partenaires') }}">Partenariat</a>
            <a href="#contact">Demande</a>
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
        <a href="#offres" onclick="closeMenu()"><i class="bi bi-tag"></i> Tarif</a>
        <a href="{{ route('partenaires') }}" onclick="closeMenu()"><i class="bi bi-handshake"></i> Partenariat</a>
        <a href="#contact" onclick="closeMenu()"><i class="bi bi-chat-dots"></i> Demande</a>
        <a href="tel:+2290146862536" onclick="closeMenu()"><i class="bi bi-telephone"></i> +229 01 46 86 25 36</a>
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
                 <h1 class="hero-title">Gérer <span class="arc-underline">stocks<svg viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0,5 Q50,10 100,5" stroke="var(--secondary)" stroke-width="4" fill="none" stroke-linecap="round"/></svg></span>, factures, ventes, livraisons, dettes et <span class="arc-underline" style="white-space:nowrap;">chiffre d'affaires<svg viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0,5 Q50,10 100,5" stroke="var(--secondary)" stroke-width="4" fill="none" stroke-linecap="round"/></svg></span> en temps réel</h1>
                <p class="hero-desc">L'outil indispensable pour votre commerce : Pilotix gère intelligemment votre stock, déclenche des alertes avant rupture, centralise la facturation et offre un suivi commercial complet pour propulser vos activités.</p>
                <div class="hero-actions">
                    <a href="{{ route('login') }}" class="btn-hero-primary">
                        <i class="bi bi-box-arrow-in-right"></i> Accéder à mon espace
                    </a>
                    <a href="#fonctionnalites" class="btn-hero-sec">
                        Voir les fonctionnalités <i class="bi bi-arrow-down"></i>
                    </a>
                </div>
                <div class="hero-stats anim-hero-stats">
                    <div class="hero-stat"><div class="stat-val">∞</div><div class="stat-lbl">Sociétés</div></div>
                    <div class="hero-stat"><div class="stat-val">100%</div><div class="stat-lbl">Multi-magasins</div></div>
                </div>
            </div>
            <div class="hero-visual anim-hero-visual">
                <div class="phone-frame">
                    <div class="phone-notch"></div>
                    <div class="phone-screen">
                        <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix" style="width:100%; height:100%; object-fit:cover;">
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
            <div class="section-label reveal reveal-delay-1">Ce qu'Pilotix fait pour vous</div>
            <h2 class="section-title reveal reveal-delay-2">Un outil complet, pensé pour le <span class="arc-underline" style="color: var(--primary);">Marché local et Magasins<svg viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0,5 Q50,10 100,5" stroke="var(--secondary)" stroke-width="4" fill="none" stroke-linecap="round"/></svg></span></h2>
            <p class="section-sub reveal reveal-delay-3">Découvrez comment chaque module structure et sécurise vos processus commerciaux quotidiens.</p>
        </div>

        <!-- Rangée 1 : Gestion de Stock (Image à droite, Texte à gauche) -->
        <div class="feat-row reveal">
            <div class="feat-col-text">
                <span class="feat-badge"><i class="bi bi-box-seam"></i> Logistique</span>
                <h3 class="feat-title">Suivi des stocks en temps réel par dépôt</h3>
                <p class="feat-desc">Ne soyez plus jamais pris au dépourvu. Suivez l’état de vos produits de façon globale et par point de stockage physique. Pilotix calcule automatiquement les niveaux critiques pour vous alerter en cas de stock bas.</p>
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
                <div class="feat-image-card">
                    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=800&auto=format&fit=crop&q=80" alt="Gestion des Dettes & SaaS">
                </div>
            </div>
        </div>
    </section>

    <!-- Multi-Device Section -->
    <section class="multi-device-section">
        <div class="multi-device-inner">
            <span class="feat-badge reveal">Disponible partout</span>
            <h2 class="section-title reveal" style="margin-bottom: 50px;">Sur <span class="arc-underline" style="color: var(--primary);">tous vos écrans<svg viewBox="0 0 100 10" preserveAspectRatio="none"><path d="M0,5 Q50,10 100,5" stroke="var(--secondary)" stroke-width="4" fill="none" stroke-linecap="round"/></svg></span></h2>
            <div class="devices-showcase reveal">
                <div class="device pc-frame">
                    <div class="pc-screen">
                        <!-- Capture PC ici -->
                    </div>
                    <div class="pc-stand"></div>
                </div>
                <div class="device tablet-frame-lg">
                    <div class="tablet-camera"></div>
                    <div class="tablet-screen-lg">
                        <!-- Capture tablette ici -->
                    </div>
                </div>
                <div class="device phone-frame-sm">
                    <div class="phone-notch-sm"></div>
                    <div class="phone-screen-sm">
                        <!-- Capture téléphone ici -->
                    </div>
                </div>
            </div>
        </div>
    </section>


    <!-- Offres -->
    <section id="offres" class="features-section" style="padding-top:40px; padding-bottom:80px;">
        <div class="features-header reveal">
            <div class="section-label">Nos Offres</div>
            <h2 class="section-title">Choisissez votre version</h2>
            <p class="section-sub" style="font-size:1.1rem; color:var(--muted); max-width:600px; margin:0 auto; line-height:1.6;">Deux options simples. Zéro engagement.</p>
        </div>

        <div style="display:flex; justify-content:center; gap:40px; flex-wrap:wrap; max-width:1100px; margin:0 auto;" class="reveal">
            <!-- Offre Locale -->
            <div style="flex:1; min-width:320px; max-width:450px; background:#fff; border-radius:24px; padding:48px; border:1px solid rgba(0,0,0,0.06); box-shadow:0 10px 30px rgba(0,0,0,0.03); transition:all 0.3s; position:relative; display:flex; flex-direction:column;">
                <h3 style="font-family:'Montserrat',sans-serif; font-size:1.8rem; font-weight:900; color:var(--text); margin-bottom:6px;">Version Locale</h3>
                
                <div style="margin-bottom:24px;">
                    <div style="font-family:'Montserrat', sans-serif; font-size:2.5rem; font-weight:900; color:var(--text); letter-spacing:-1px;">79 900 <span style="font-size:1rem; font-weight:700; color:var(--muted);">FCFA</span></div>
                    <div style="font-size:0.85rem; font-weight:700; color:var(--primary); margin-top:4px;">Paiement unique, licence à vie</div>
                </div>

                <ul style="list-style:none; padding:0; margin:0 0 30px; display:flex; flex-direction:column; gap:12px; border-top:1px solid #f1f5f9; padding-top:24px; flex-grow:1;">
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--primary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Gestion complète des stocks, ventes, achats & fournisseurs</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--primary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Suivi des créances clients & dettes fournisseurs</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--primary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Gestion multi-utilisateurs avec droits d'accès</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--primary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Rapports & statistiques de vente en temps réel</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--primary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Gestion des catégories, dépôts & transferts</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--primary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Impression de reçus, factures & états de stock</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-x-circle" style="color:#ef4444; font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Accessible uniquement depuis un seul ordinateur</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-x-circle" style="color:#ef4444; font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Pas de synchronisation de données à distance</span></li>
                </ul>

                <a href="#contact" style="display:block; text-align:center; padding:16px; background:rgba(16,94,73,0.06); color:var(--primary); border-radius:12px; font-weight:800; font-size:1.05rem; text-decoration:none; transition:all .2s;">Souscrire maintenant</a>
            </div>

            <!-- Offre Cloud -->
            <div style="flex:1; min-width:320px; max-width:450px; background:#fff; border-radius:24px; padding:48px; border:2px solid var(--secondary); box-shadow:0 15px 40px rgba(234,141,34,0.1); transition:all 0.3s; position:relative; display:flex; flex-direction:column;">
                <div style="position:absolute; top:0; left:0; right:0; background:linear-gradient(90deg, var(--secondary), #f59e0b); color:#fff; font-size:0.75rem; font-weight:800; text-transform:uppercase; letter-spacing:2px; padding:8px 0; text-align:center; border-radius:22px 22px 0 0;">Le plus populaire</div>
                
                <h3 style="font-family:'Montserrat',sans-serif; font-size:1.8rem; font-weight:900; color:var(--text); margin-bottom:6px; margin-top:10px;">Version Cloud Sync</h3>
                
                <div style="margin-bottom:24px;">
                    <div style="font-family:'Montserrat', sans-serif; font-size:2.5rem; font-weight:900; color:var(--text); letter-spacing:-1px;">3 500 <span style="font-size:1rem; font-weight:700; color:var(--muted);">FCFA / mois</span></div>
                    <div style="font-size:0.85rem; font-weight:700; color:var(--secondary); margin-top:4px;">Jusqu'à -30% sur l'engagement</div>
                </div>

                <ul style="list-style:none; padding:0; margin:0 0 30px; display:flex; flex-direction:column; gap:12px; border-top:1px solid #f1f5f9; padding-top:24px; flex-grow:1;">
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--secondary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Gestion complète des stocks, ventes, achats & fournisseurs</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--secondary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Suivi des créances clients & dettes fournisseurs</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--secondary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Gestion multi-utilisateurs avec droits d'accès</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--secondary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Rapports & statistiques de vente en temps réel</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--secondary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Gestion des catégories, dépôts & transferts</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--secondary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Impression de reçus, factures & états de stock</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--secondary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Accessible partout (PC, Mobile, Tablette)</span></li>
                    <li style="display:flex; align-items:center; gap:10px;"><i class="bi bi-check2" style="color:var(--secondary); font-size:1.1rem; font-weight:800;"></i> <span style="font-size:0.93rem; color:#475569;">Synchronisation des données à distance</span></li>
                </ul>

                <a href="#contact" style="display:block; text-align:center; padding:16px; background:var(--secondary); color:#fff; border-radius:12px; font-weight:800; font-size:1.05rem; text-decoration:none; transition:all .2s; box-shadow:0 8px 20px rgba(234,141,34,0.3);">Souscrire maintenant</a>
            </div>
        </div>
    </section>

    <!-- Roles -->
    <section id="roles" class="roles">
        <div class="roles-inner">
            <div style="text-align:center;" class="reveal">
                <div class="section-label reveal reveal-delay-1">Organisation des accès</div>
                <h2 class="section-title reveal reveal-delay-2">Organisez l'accès de chaque collaborateur</h2>
                <p class="section-sub reveal reveal-delay-3" style="margin:0 auto; max-width:650px;">Vous pouvez séparer les droits d'accès de votre équipe : qui voit les ventes, qui gère le stock, qui livre. Chaque collaborateur accède uniquement à ce qui le concerne, sans confusion.</p>
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
                                <li><strong>Collaborez</strong> efficacement : peut aussi assister la livraison en cas d'urgence terrain</li>
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
                    <div style="background: rgba(22, 163, 74, 0.9); border: 1px solid #4ade80; padding: 16px; border-radius: 10px; color: #fff; font-size: 0.95rem; margin-bottom: 20px; font-weight: 600; text-align:center;">
                        <i class="bi bi-check-circle-fill" style="margin-right: 6px;"></i> {{ session('success') }}
                    </div>
                @endif

                <form action="{{ route('contact.submit') }}" method="POST">
                    @csrf
                    <div class="form-group">
                        <label class="form-label" for="nom_societe">Nom de la société *</label>
                        <input type="text" name="nom_societe" id="nom_societe" class="form-control" placeholder="Ex: Mon Entreprise SARL" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Adresse email de contact *</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Ex: contact@entreprise.com" required>
                    </div>

                    <div class="form-row-contact">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="localisation">Pays / Localisation *</label>
                            <input type="text" name="localisation" id="localisation" class="form-control" placeholder="Ex: Bénin" required>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="ville">Ville *</label>
                            <input type="text" name="ville" id="ville" class="form-control" placeholder="Ex: Cotonou" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="telephone">Numéro de téléphone *</label>
                        <input type="text" name="telephone" id="telephone" class="form-control" placeholder="Ex: +229 97 00 00 00" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="secteurs_activite">Secteurs d'activité *</label>
                        <input type="text" name="secteurs_activite" id="secteurs_activite" class="form-control" placeholder="Ex: Import/Export, Distribution, Vente au détail" required>
                    </div>

                    <div class="form-row-contact">
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="type_souscription">Type de souscription *</label>
                            <select name="type_souscription" id="type_souscription" class="form-control" required onchange="updateDuree()">
                                <option value="cloud">Cloud Sync (3 500 FCFA/mois)</option>
                                <option value="local">Locale (79 900 FCFA)</option>
                            </select>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label class="form-label" for="duree">Durée</label>
                            <select name="duree" id="duree" class="form-control" onchange="updateDuree()">
                                <option value="vie" id="opt-vie">À vie</option>
                                <option value="1" id="opt-1" selected>1 mois</option>
                                <option value="3" id="opt-3">3 mois</option>
                                <option value="6" id="opt-6">6 mois</option>
                                <option value="12" id="opt-12">1 an</option>
                            </select>
                        </div>
                    </div>

                    <div id="price-display" style="display:flex; align-items:center; gap:10px; padding:12px 16px; background:rgba(255,255,255,0.1); border-radius:10px; margin-bottom:16px;">
                        <span style="font-weight:800; font-size:1.3rem; color:#fff;" id="price-total">3 500 FCFA</span>
                        <span style="font-size:0.8rem; color:rgba(255,255,255,0.5); font-weight:600;" id="price-period">/mois</span>
                        <span id="price-eco" style="display:none; font-size:0.75rem; font-weight:700; color:#4ade80; background:rgba(74,222,128,0.15); padding:2px 10px; border-radius:8px; margin-left:auto;"></span>
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
                <div style="margin-bottom:10px;">
                    <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix Logo" style="height: 56px; width: 56px; object-fit: contain; border-radius: 12px;">
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
                </ul>
            </div>
            <div class="footer-links">
                <h5 style="color: #374151;">Contact</h5>
                <ul>
                    <li><a href="tel:+2290146862536" style="color: #374151;"><i class="bi bi-telephone"></i> +229 01 46 86 25 36</a></li>
                    <li><a href="mailto:pilotrix@gmail.com" style="color: #374151;"><i class="bi bi-envelope"></i> pilotrix@gmail.com</a></li>
                    <li><a href="mailto:belloxdigital@gmail.com" style="color: #374151;"><i class="bi bi-envelope"></i> belloxdigital@gmail.com</a></li>
                    <li><a href="{{ route('partenaires') }}" style="color: #374151;"><i class="bi bi-handshake"></i> Partenariat</a></li>
                </ul>
            </div>
        </div>
        <div class="footer-bottom" style="border-top: 1px solid #e5e7eb; color: #6b7280;">
            <span>&copy; {{ date('Y') }} Pilotix — Gestion commerciale multi-tenant</span>
            <span><a href="{{ route('conditions') }}" style="color:inherit;">Conditions</a> · <a href="{{ route('confidentialite') }}" style="color:inherit;">Confidentialité</a></span>
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

    function updateDuree() {
        var type = document.getElementById('type_souscription').value;
        var duree = document.getElementById('duree').value;
        var optVie = document.getElementById('opt-vie');
        var opt1 = document.getElementById('opt-1');
        var opt3 = document.getElementById('opt-3');
        var opt6 = document.getElementById('opt-6');
        var opt12 = document.getElementById('opt-12');
        var sel = document.getElementById('duree');
        var priceTotal = document.getElementById('price-total');
        var pricePeriod = document.getElementById('price-period');
        var priceEco = document.getElementById('price-eco');

        if (type === 'local') {
            optVie.style.display = '';
            opt1.style.display = 'none'; opt3.style.display = 'none';
            opt6.style.display = 'none'; opt12.style.display = 'none';
            sel.value = 'vie';
            priceTotal.textContent = '79 900 FCFA';
            pricePeriod.textContent = 'Paiement unique';
            priceEco.style.display = 'none';
        } else {
            optVie.style.display = 'none';
            opt1.style.display = ''; opt3.style.display = '';
            opt6.style.display = ''; opt12.style.display = '';
            if (sel.value === 'vie') sel.value = '1';
            updateCloudPrice();
        }
    }

    function updateCloudPrice() {
        var duree = document.getElementById('duree').value;
        var priceTotal = document.getElementById('price-total');
        var pricePeriod = document.getElementById('price-period');
        var priceEco = document.getElementById('price-eco');
        var tarif = 3500;
        var prix, label, economie, taux;

        if (duree === '1') {
            prix = tarif; label = '/mois';
            priceEco.style.display = 'none';
        } else if (duree === '3') {
            prix = 9000; label = 'pour 3 mois';
            economie = (tarif * 3) - prix; taux = Math.round((economie / (tarif * 3)) * 100);
            priceEco.style.display = ''; priceEco.textContent = '-' + economie.toLocaleString() + ' FCFA (' + taux + '%)';
        } else if (duree === '6') {
            prix = 16800; label = 'pour 6 mois';
            economie = (tarif * 6) - prix; taux = Math.round((economie / (tarif * 6)) * 100);
            priceEco.style.display = ''; priceEco.textContent = '-' + economie.toLocaleString() + ' FCFA (' + taux + '%)';
        } else if (duree === '12') {
            prix = 30000; label = 'pour 1 an';
            economie = (tarif * 12) - prix; taux = Math.round((economie / (tarif * 12)) * 100);
            priceEco.style.display = ''; priceEco.textContent = '-' + economie.toLocaleString() + ' FCFA (' + taux + '%)';
        }
        priceTotal.textContent = prix.toLocaleString() + ' FCFA';
        pricePeriod.textContent = label;
    }

    updateDuree();
</script>
</body>
</html>
