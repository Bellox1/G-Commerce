<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programme Partenaires & Affiliation — PILOTIX</title>
    <meta name="description" content="Devenez partenaire PILOTIX. Touchez jusqu'à {{ number_format($maxCommission ?? 0, 0, ' ', ' ') }} FCFA de commission unique par vente et débloquez jusqu'à {{ number_format($maxPrime ?? 0, 0, ' ', ' ') }} FCFA de prime de performance !">
    <meta name="keywords" content="partenaire, affiliation, commission, PILOTIX, souscription, parrainage, revendeur">
    <meta name="robots" content="index, follow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #0f5c47;
            --primary-dark: #0a3d2e;
            --primary-light: #168567;
            --secondary: #f59e0b;
            --secondary-dark: #d97706;
            --accent: #3b82f6;
            --bg-dark: #071e17;
            --bg-light: #f8fafc;
            --card-bg: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --radius-lg: 20px;
            --radius-md: 14px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        html { scroll-behavior: smooth; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: var(--bg-light);
            color: var(--text-main);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Header / Nav */
        nav {
            position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center; justify-content: space-between;
            padding: 14px 6%;
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }
        .nav-logo { display: flex; align-items: center; gap: 10px; text-decoration: none; }
        .nav-logo img { height: 48px; width: 48px; object-fit: contain; border-radius: 12px; }
        .nav-logo-text { font-family: 'Space Grotesk', sans-serif; font-size: 1.4rem; font-weight: 700; color: var(--primary-dark); }
        .nav-links { display: flex; align-items: center; gap: 24px; }
        .nav-links a { color: var(--text-main); text-decoration: none; font-weight: 600; font-size: 0.92rem; transition: color 0.2s; }
        .nav-links a:hover { color: var(--primary); }
        .btn-nav {
            background: linear-gradient(135deg, var(--primary), var(--primary-light));
            color: #fff !important; padding: 10px 22px; border-radius: 10px;
            font-weight: 700; font-size: 0.9rem; text-decoration: none;
            box-shadow: 0 4px 14px rgba(15, 92, 71, 0.25); transition: all 0.3s;
        }
        .btn-nav:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(15, 92, 71, 0.35); }

        /* ─── HAMBURGER BUTTON (MOBILE) ─── */
        .hamburger {
            display: none; flex-direction: column; justify-content: center; align-items: center;
            gap: 5px; width: 40px; height: 40px; background: none; border: none; cursor: pointer;
            padding: 4px; border-radius: 8px; transition: background 0.2s; z-index: 200;
        }
        .hamburger:hover { background: rgba(15,92,71,0.06); }
        .hamburger span {
            display: block; width: 24px; height: 2.5px; background: var(--text-main);
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
            color: var(--text-main); text-decoration: none; font-weight: 500; font-size: 1rem;
            padding: 12px 16px; border-radius: 10px; display: flex; align-items: center; gap: 12px;
            transition: background 0.2s, color 0.2s;
        }
        .mobile-menu a:hover { background: rgba(15,92,71,0.06); color: var(--primary); }
        .mobile-menu .btn-nav-mobile { background: var(--primary); color: #fff !important; margin-top: 8px; justify-content: center; font-weight: 700; border-radius: 10px; }
        .mobile-menu-divider { height: 1px; background: rgba(0,0,0,0.06); margin: 6px 0; }

        /* HERO SECTION */
        .hero {
            background: linear-gradient(135deg, #051812 0%, #0c4233 50%, #0f5c47 100%);
            color: #fff;
            padding: 90px 6% 110px;
            position: relative;
            overflow: hidden;
            text-align: center;
        }
        .hero::before {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(circle at 50% 20%, rgba(245, 158, 11, 0.15), transparent 60%);
            pointer-events: none;
        }
        .hero-badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(245, 158, 11, 0.16); border: 1px solid rgba(245, 158, 11, 0.35);
            color: #fbbf24; padding: 6px 18px; border-radius: 50px;
            font-size: 0.82rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 24px; backdrop-filter: blur(8px);
        }
        .hero h1 {
            font-family: 'Space Grotesk', sans-serif;
            font-size: clamp(2.2rem, 5vw, 3.6rem);
            font-weight: 700; line-height: 1.2;
            margin: 0 auto 20px; max-width: 860px;
        }
        .hero h1 span {
            background: linear-gradient(135deg, #fef08a, #f59e0b);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero-sub {
            font-size: 1.15rem; color: rgba(255,255,255,0.85);
            max-width: 680px; margin: 0 auto 36px; font-weight: 400; line-height: 1.7;
        }
        .hero-actions { display: flex; gap: 16px; justify-content: center; flex-wrap: wrap; margin-bottom: 50px; }
        .btn-hero-primary {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff; padding: 15px 34px; border-radius: 12px;
            font-weight: 800; font-size: 1rem; text-decoration: none;
            box-shadow: 0 10px 25px rgba(245, 158, 11, 0.35); transition: all 0.3s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-hero-primary:hover { transform: translateY(-3px); box-shadow: 0 14px 30px rgba(245, 158, 11, 0.45); }
        .btn-hero-secondary {
            background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.25);
            color: #fff; padding: 15px 28px; border-radius: 12px;
            font-weight: 700; font-size: 1rem; text-decoration: none;
            backdrop-filter: blur(10px); transition: all 0.3s;
            display: inline-flex; align-items: center; gap: 8px;
        }
        .btn-hero-secondary:hover { background: rgba(255,255,255,0.18); transform: translateY(-2px); }

        /* Pill Stats Header */
        .hero-pills {
            display: flex; justify-content: center; gap: 20px; flex-wrap: wrap;
            max-width: 900px; margin: 0 auto;
        }
        .pill-stat {
            background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.12);
            padding: 12px 20px; border-radius: 14px; backdrop-filter: blur(12px);
            display: flex; align-items: center; gap: 12px; text-align: left;
        }
        .pill-icon {
            width: 40px; height: 40px; border-radius: 10px;
            background: rgba(245, 158, 11, 0.2); color: #fbbf24;
            display: flex; align-items: center; justify-content: center; font-size: 1.2rem;
        }
        .pill-text { font-size: 0.82rem; color: rgba(255,255,255,0.7); }
        .pill-val { font-size: 0.95rem; font-weight: 800; color: #fff; }

        /* SECTION STYLING */
        .section { padding: 80px 6%; }
        .section-header { text-align: center; max-width: 700px; margin: 0 auto 50px; }
        .section-tag {
            display: inline-block; font-size: 0.78rem; font-weight: 800;
            text-transform: uppercase; letter-spacing: 1px; color: var(--primary);
            background: rgba(15, 92, 71, 0.08); padding: 5px 16px; border-radius: 50px;
            margin-bottom: 12px;
        }
        .section-title {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 12px;
        }
        .section-sub { font-size: 1rem; color: var(--text-muted); line-height: 1.6; }

        /* CALCULATOR WIDGET (INTERACTIVE WOW FACTOR) */
        .calc-wrapper {
            background: linear-gradient(135deg, #09261e, #0f5c47);
            border-radius: 24px; padding: 44px; color: #fff;
            max-width: 1000px; margin: 0 auto 70px;
            box-shadow: 0 20px 50px rgba(15, 92, 71, 0.2);
            position: relative; overflow: hidden;
        }
        .calc-header { text-align: center; margin-bottom: 36px; }
        .calc-header h3 { font-family: 'Space Grotesk', sans-serif; font-size: 1.8rem; font-weight: 700; }
        .calc-header p { color: rgba(255,255,255,0.8); font-size: 0.95rem; }

        .calc-grid { display: grid; grid-template-columns: 1.2fr 1fr; gap: 40px; align-items: center; }
        @media(max-width: 850px) { .calc-grid { grid-template-columns: 1fr; } }

        .calc-controls { display: flex; flex-direction: column; gap: 24px; }
        .calc-group label { display: block; font-size: 0.9rem; font-weight: 700; margin-bottom: 10px; color: rgba(255,255,255,0.9); }
        .plan-selector { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; }
        .plan-btn {
            background: rgba(255, 255, 255, 0.1); border: 2px solid rgba(255, 255, 255, 0.15);
            color: #fff; padding: 12px 8px; border-radius: 12px; font-weight: 700;
            font-size: 0.85rem; cursor: pointer; text-align: center; transition: all 0.2s;
        }
        .plan-btn:hover, .plan-btn.active {
            background: #fff; color: var(--primary-dark); border-color: #fff;
            box-shadow: 0 6px 18px rgba(0,0,0,0.15);
        }
        .plan-input {
            background: rgba(255,255,255,0.1); border: 2px solid rgba(255,255,255,0.15);
            color: #fff; padding: 12px 10px; border-radius: 12px; text-align: center;
            display: flex; flex-direction: column; gap: 6px;
        }
        .plan-input span { font-weight: 700; font-size: 0.95rem; }
        .plan-input small { font-weight: 400; opacity: 0.85; font-size: 0.72rem; }
        .plan-input input { text-align: center; font-weight: 700; }

        .range-slider-wrap { background: rgba(255,255,255,0.08); padding: 18px 20px; border-radius: 14px; border: 1px solid rgba(255,255,255,0.12); }
        .range-header { display: flex; justify-content: space-between; margin-bottom: 10px; font-weight: 700; font-size: 0.9rem; }
        .range-input {
            width: 100%; accent-color: var(--secondary); cursor: pointer; height: 8px; border-radius: 4px;
        }

        .calc-result-box {
            background: rgba(255, 255, 255, 0.08); border: 1px solid rgba(255, 255, 255, 0.18);
            border-radius: 20px; padding: 28px; backdrop-filter: blur(10px);
            text-align: center; position: relative;
        }
        .res-line { display: flex; justify-content: space-between; font-size: 0.9rem; padding: 10px 0; border-bottom: 1px dashed rgba(255,255,255,0.15); }
        .res-line:last-of-type { border-bottom: none; }
        .res-val { font-weight: 800; color: #fef08a; }
        .res-total-wrap {
            margin-top: 20px; padding-top: 16px; border-top: 2px solid rgba(255,255,255,0.2);
        }
        .res-total-label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 1px; color: rgba(255,255,255,0.7); }
        .res-total-num { font-family: 'Space Grotesk', sans-serif; font-size: 2.3rem; font-weight: 800; color: #fbbf24; margin-top: 4px; }

        /* CARDS GRID FOR COMMISSIONS (NO UGLY TABLES) */
        .commisions-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 28px; max-width: 1100px; margin: 0 auto;
        }
        .commission-card {
            background: var(--card-bg); border-radius: var(--radius-lg); padding: 32px;
            border: 1px solid var(--border-color); box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            transition: all 0.3s; position: relative; display: flex; flex-direction: column;
        }
        .commission-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,0.08); }
        .commission-card.featured {
            border: 2px solid var(--secondary);
            box-shadow: 0 15px 40px rgba(245, 158, 11, 0.15);
        }
        .card-badge {
            position: absolute; top: -14px; right: 24px;
            background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
            color: #fff; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;
            padding: 4px 14px; border-radius: 50px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .plan-title { font-family: 'Space Grotesk', sans-serif; font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
        .plan-price-tag { font-size: 0.88rem; color: var(--text-muted); margin-bottom: 20px; font-weight: 600; }
        .cash-box {
            background: rgba(15, 92, 71, 0.06); border: 1px solid rgba(15, 92, 71, 0.12);
            border-radius: 14px; padding: 18px; text-align: center; margin-bottom: 20px;
        }
        .cash-box.featured-box {
            background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.2);
        }
        .cash-lbl { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.5px; }
        .cash-amount { font-family: 'Space Grotesk', sans-serif; font-size: 1.9rem; font-weight: 800; color: var(--primary-dark); margin-top: 2px; }
        .cash-box.featured-box .cash-amount { color: var(--secondary-dark); }
        .payout-type { font-size: 0.8rem; font-weight: 700; color: var(--primary); margin-top: 4px; }

        .card-features-list { list-style: none; display: flex; flex-direction: column; gap: 12px; margin-bottom: 24px; flex-grow: 1; }
        .card-features-list li { display: flex; align-items: flex-start; gap: 10px; font-size: 0.9rem; color: #475569; line-height: 1.5; }
        .card-features-list li i { color: var(--primary); font-weight: 800; font-size: 1.05rem; margin-top: 2px; flex-shrink: 0; }
        .commission-card.featured .card-features-list li i { color: var(--secondary); }

        /* TIERED BONUSES — stylé comme les cartes Offres ci-dessus */
        .tiers-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 28px; max-width: 1100px; margin: 0 auto; }
        .tier-card {
            background: var(--card-bg); border-radius: var(--radius-lg); padding: 32px;
            border: 1px solid var(--border-color); position: relative;
            transition: all 0.3s; display: flex; flex-direction: column;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }
        .tier-card:hover { transform: translateY(-6px); box-shadow: 0 16px 40px rgba(0,0,0,0.08); }
        .tier-card.double {
            border: 2px solid var(--secondary);
            box-shadow: 0 15px 40px rgba(245, 158, 11, 0.15);
        }
        .tier-card .card-badge {
            position: absolute; top: -14px; right: 24px;
            background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
            color: #fff; font-size: 0.72rem; font-weight: 800; text-transform: uppercase;
            padding: 4px 14px; border-radius: 50px; box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        }
        .tier-card .plan-title { font-family: 'Space Grotesk', sans-serif; font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
        .tier-card.double .plan-title { color: var(--secondary-dark); }
        .tier-card .plan-price-tag { font-size: 0.88rem; color: var(--text-muted); margin-bottom: 20px; font-weight: 600; }

        .prime-list { display: flex; flex-direction: column; gap: 14px; flex-grow: 1; }
        .prime-item {
            background: rgba(15, 92, 71, 0.06); border: 1px solid rgba(15, 92, 71, 0.12);
            border-radius: 14px; padding: 14px 18px; display: flex; flex-direction: column; gap: 4px;
        }
        .tier-card.double .prime-item {
            background: rgba(245, 158, 11, 0.08); border-color: rgba(245, 158, 11, 0.2);
        }
        .prime-lbl { font-size: 0.78rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: var(--text-muted); }
        .prime-amt { font-family: 'Space Grotesk', sans-serif; font-size: 1.35rem; font-weight: 800; color: var(--primary-dark); }
        .tier-card.double .prime-amt { color: var(--secondary-dark); }

        /* FORM SECTION (CLEAN & MODERN) */
        .form-container { max-width: 780px; margin: 0 auto; }
        .form-card {
            background: #fff; border-radius: 24px; padding: 40px;
            border: 1px solid var(--border-color); box-shadow: 0 15px 45px rgba(0,0,0,0.04);
        }

        /* Stepper Header */
        .stepper-header { display: flex; justify-content: space-between; margin-bottom: 30px; position: relative; }
        .stepper-header::before {
            content: ''; position: absolute; top: 18px; left: 30px; right: 30px; height: 3px; background: #e2e8f0; z-index: 0;
        }
        .step-item { display: flex; flex-direction: column; align-items: center; gap: 6px; z-index: 1; flex: 1; }
        .step-item .circle {
            width: 38px; height: 38px; border-radius: 50%; background: #e2e8f0; color: #64748b;
            display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.9rem;
            transition: all 0.3s;
        }
        .step-item.active .circle { background: var(--primary); color: #fff; box-shadow: 0 0 0 4px rgba(15, 92, 71, 0.2); }
        .step-item.done .circle { background: var(--primary); color: #fff; }
        .step-item .label { font-size: 0.72rem; font-weight: 700; color: var(--text-muted); text-align: center; }
        .step-item.active .label, .step-item.done .label { color: var(--primary); }

        /* Questions Panels */
        .step-panel { display: none; }
        .step-panel.active { display: block; animation: fadeInUp 0.35s ease; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

        .panel-title { font-family: 'Space Grotesk', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--primary-dark); margin-bottom: 4px; }
        .panel-sub { font-size: 0.88rem; color: var(--text-muted); margin-bottom: 24px; }

        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 8px; color: var(--text-main); }
        .form-control {
            width: 100%; padding: 12px 16px; border: 1.5px solid var(--border-color); border-radius: 10px;
            font-size: 0.92rem; font-family: inherit; transition: all 0.2s; background: #fff;
        }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(15, 92, 71, 0.12); }
        textarea.form-control { resize: vertical; min-height: 90px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media(max-width: 600px) { .form-row { grid-template-columns: 1fr; } }

        .step-buttons { display: flex; gap: 12px; margin-top: 30px; }
        .btn-step-next, .btn-step-prev {
            flex: 1; padding: 14px; border-radius: 10px; border: none; font-weight: 700;
            font-size: 0.95rem; cursor: pointer; transition: all 0.2s; font-family: inherit;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-step-next { background: var(--primary); color: #fff; }
        .btn-step-next:hover { background: var(--primary-light); }
        .btn-step-prev { background: #f1f5f9; color: var(--text-main); border: 1px solid var(--border-color); }
        .btn-step-prev:hover { background: #e2e8f0; }
        .btn-step-submit {
            flex: 1; padding: 15px; border-radius: 10px; border: none; background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
            color: #fff; font-weight: 800; font-size: 1rem; cursor: pointer; transition: all 0.2s; font-family: inherit;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 6px 20px rgba(245, 158, 11, 0.3);
        }
        .btn-step-submit:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(245, 158, 11, 0.4); }

        /* FOOTER (CLEAN WHITE BACKGROUND LIKE WELCOME) */
        footer { background: #ffffff; border-top: 1px solid #e5e7eb; color: var(--text-main); padding: 60px 6% 28px; margin-top: 80px; }
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

        /* RESPONSIVE MEDIA QUERIES (PARTENAIRES) */
        @media (max-width: 991px) {
            nav { padding: 12px 4%; }
            .nav-links { display: none !important; }
            .hamburger { display: flex !important; }
            .hero { padding: 60px 5% 80px; }
            .hero h1 { font-size: 2.2rem; }
            .hero-sub { font-size: 1rem; }
            .calc-wrapper { padding: 24px 18px; border-radius: 18px; }
            .commisions-grid, .tiers-grid { grid-template-columns: 1fr; }
            .form-card { padding: 24px 18px; border-radius: 18px; }
            .stepper-header { flex-wrap: wrap; gap: 12px; }
            .step-item .label { font-size: 0.65rem; }
            .footer-top { flex-direction: column; gap: 32px; }
        }

        @media (max-width: 600px) {
            nav { justify-content: space-between; }
            .nav-logo-text { font-size: 1.1rem; }
            .nav-logo img { height: 38px; width: 38px; }
            .hero { padding: 44px 16px 60px; }
            .hero h1 { font-size: 1.75rem; line-height: 1.25; }
            .hero-sub { font-size: 0.92rem; margin-bottom: 24px; }
            .hero-actions { flex-direction: column; width: 100%; gap: 10px; }
            .btn-hero-primary, .btn-hero-secondary { width: 100%; justify-content: center; padding: 13px 20px; font-size: 0.92rem; }
            .hero-pills { flex-direction: column; gap: 10px; }
            .pill-stat { width: 100%; }
            .calc-wrapper { padding: 20px 14px; }
            .plan-selector { grid-template-columns: 1fr; }
            .res-total-num { font-size: 1.8rem; }
            .form-card { padding: 20px 14px; }
            .step-buttons { flex-direction: column; }
            .btn-step-next, .btn-step-prev, .btn-step-submit { width: 100%; }
            .footer-bottom { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<nav>
    <a href="/" class="nav-logo">
        <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX">
        <span class="nav-logo-text">PILOTIX</span>
    </a>
    <div class="nav-links">
        <a href="/">Accueil</a>
        <a href="#simulateur">Simulateur</a>
        <a href="#commissions">Offres</a>
        <a href="#primes">Primes</a>
        <a href="{{ route('download') }}">Téléchargements</a>
        @if (Auth::check())
            <a href="{{ route('dashboard') }}" class="btn-nav">Mon Espace</a>
        @else
            <a href="#candidature" class="btn-nav">Postuler Maintenant</a>
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
    <a href="#simulateur"><i class="bi bi-calculator"></i> Simulateur</a>
    <a href="#commissions"><i class="bi bi-gift"></i> Offres</a>
    <a href="#primes"><i class="bi bi-trophy"></i> Primes</a>
    <a href="{{ route('download') }}"><i class="bi bi-download"></i> Téléchargements</a>
    <div class="mobile-menu-divider"></div>
    @if(Auth::check())
        <a href="{{ route('dashboard') }}" class="btn-nav-mobile"><i class="bi bi-speedometer2"></i> Mon Espace</a>
    @else
        <a href="#candidature" class="btn-nav-mobile"><i class="bi bi-pencil-square"></i> Postuler Maintenant</a>
    @endif
</div>

<!-- HERO SECTION -->
<section class="hero">
    <div class="hero-badge"><i class="bi bi-shield-check"></i> Programme d'Affiliation Officiel</div>
    <h1>Devenez Partenaire <span>PILOTIX</span> & Monétisez Votre Réseau</h1>
    <p class="hero-sub">Recommandez la solution n°1 de gestion commerciale. Encaissez jusqu'à <strong>{{ number_format($rules['entreprise']->commission, 0, ' ', ' ') }} FCFA</strong> de commission cash par vente et débloquez jusqu'à <strong>{{ number_format($maxPrime, 0, ' ', ' ') }} FCFA</strong> de super-primes de performance !</p>
    
    <div class="hero-actions">
        <a href="#simulateur" class="btn-hero-primary"><i class="bi bi-calculator"></i> Simuler Mes Gains</a>
        <a href="#candidature" class="btn-hero-secondary"><i class="bi bi-send"></i> Soumettre Ma Candidature</a>
    </div>

    <div class="hero-pills">
        <div class="pill-stat">
            <div class="pill-icon"><i class="bi bi-cash"></i></div>
            <div>
                <div class="pill-text">Commission Unique Par Vente</div>
                <div class="pill-val">{{ number_format($rules['essentiel']->commission, 0, ' ', ' ') }} à {{ number_format($rules['entreprise']->commission, 0, ' ', ' ') }} FCFA</div>
            </div>
        </div>
        <div class="pill-stat">
            <div class="pill-icon"><i class="bi bi-award"></i></div>
            <div>
                <div class="pill-text">Super-Prime Dès {{ $paliers[0] }} Clients</div>
                <div class="pill-val">Jusqu'à +{{ number_format($primesByCode['entreprise'][$paliers[0]] ?? 0, 0, ' ', ' ') }} FCFA</div>
            </div>
        </div>
        <div class="pill-stat">
            <div class="pill-icon"><i class="bi bi-graph-up-arrow"></i></div>
            <div>
                <div class="pill-text">Palier {{ $paliers[1] ?? ($paliers[0] * 2) }} Clients (Le Double !)</div>
                <div class="pill-val">Jusqu'à +{{ number_format($primesByCode['entreprise'][$paliers[1] ?? ($paliers[0] * 2)] ?? 0, 0, ' ', ' ') }} FCFA</div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 1 : SIMULATEUR INTERACTIF (ELEGANT & SOBER) -->
<section class="section" id="simulateur">
    <div class="section-header">
        <div class="section-tag">Calculateur En Direct</div>
        <h2 class="section-title">Combien allez-vous gagner ?</h2>
        <p class="section-sub">Indiquez le nombre de clients apportés pour chaque offre pour calculer vos commissions uniques + vos primes de performance en temps réel.</p>
    </div>

    <div class="calc-wrapper">
        <div class="calc-header">
            <h3><i class="bi bi-calculator"></i> Simulateur de Gains Partenaire</h3>
            <p>Calcul automatique : Commissions cash uniques + Primes de palier</p>
        </div>

        <div class="calc-grid">
            <div class="calc-controls">
                <div class="calc-group">
                    <label>1. Nombre de clients apportés par offre :</label>
                    <div class="plan-selector">
                        <div class="plan-input">
                            <span>Essentiel</span>
                            <small style="font-weight:400; opacity:0.8;">{{ number_format($rules['essentiel']->prix/1000, 0, '', '') }}k F · {{ number_format($rules['essentiel']->commission, 0, ' ', ' ') }} F/vente</small>
                            <input type="number" min="0" value="0" id="nb_essentiel" class="form-control" oninput="updateCalc()">
                        </div>
                        <div class="plan-input">
                            <span>Professionnel</span>
                            <small style="font-weight:400; opacity:0.8;">{{ number_format($rules['professionnel']->prix/1000, 0, '', '') }}k F · {{ number_format($rules['professionnel']->commission, 0, ' ', ' ') }} F/vente</small>
                            <input type="number" min="0" value="5" id="nb_professionnel" class="form-control" oninput="updateCalc()">
                        </div>
                        <div class="plan-input">
                            <span>Entreprise</span>
                            <small style="font-weight:400; opacity:0.8;">{{ number_format($rules['entreprise']->prix/1000, 0, '', '') }}k F · {{ number_format($rules['entreprise']->commission, 0, ' ', ' ') }} F/vente</small>
                            <input type="number" min="0" value="0" id="nb_entreprise" class="form-control" oninput="updateCalc()">
                        </div>
                    </div>
                    <p style="font-size:0.78rem; color:var(--muted); margin-top:8px;">Les primes de performance sont calculées <strong>par offre</strong> selon le nombre de ventes de cette offre (paliers {{ implode(', ', $paliers) }} ventes).</p>
                </div>
            </div>

            <div class="calc-result-box">
                <div class="res-line">
                    <span>Commissions Cash Uniques :</span>
                    <span class="res-val" id="resDirect">{{ number_format($rules['professionnel']->commission * 5, 0, ' ', ' ') }} FCFA</span>
                </div>
                <div class="res-line">
                    <span>Super-Prime de Performance :</span>
                    <span class="res-val" id="resBonus">+ {{ number_format($primesByCode['professionnel'][$paliers[0]] ?? 0, 0, ' ', ' ') }} FCFA</span>
                </div>
                <div class="res-total-wrap">
                    <div class="res-total-label">Gains Totaux Encaissés</div>
                    <div class="res-total-num" id="resTotal">{{ number_format($rules['professionnel']->commission * 5 + ($primesByCode['professionnel'][$paliers[0]] ?? 0), 0, ' ', ' ') }} FCFA</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 2 : GRILLE DES COMMISSIONS UNIQUES -->
<section class="section" id="commissions" style="background:#fff;">
    <div class="section-header">
        <div class="section-tag">Commissions Uniques Cash</div>
        <h2 class="section-title">Rémunération directe par vente</h2>
        <p class="section-sub">Pas de tracas ni d'attente complexe. Chaque client signé vous donne droit à une commission forfaitaire unique versée dès la souscription.</p>
    </div>

    <div class="commisions-grid">
        <!-- Card 1 -->
        <div class="commission-card">
            <h3 class="plan-title">Offre 1 — Essentiel</h3>
            <div class="plan-price-tag">Prix Client : {{ number_format($rules['essentiel']->prix, 0, ' ', ' ') }} FCFA / an (1 Poste)</div>

            <div class="cash-box">
                <div class="cash-lbl">Votre Commission Directe</div>
                <div class="cash-amount">{{ number_format($rules['essentiel']->commission, 0, ' ', ' ') }} FCFA</div>
                <div class="payout-type"><i class="bi bi-check-circle-fill"></i> Versement unique cash</div>
            </div>

            <ul class="card-features-list">
                <li><i class="bi bi-check2"></i> <span>1 Poste de travail pour boutique unique</span></li>
                <li><i class="bi bi-check2"></i> <span>Ventes, stocks, factures & dépenses</span></li>
                <li><i class="bi bi-check2"></i> <span>Mode Hors-connexion & Sync auto</span></li>
                <li><i class="bi bi-check2"></i> <span>Facilité de paiement en 3x possible</span></li>
            </ul>
        </div>

        <!-- Card 2 (Featured) -->
        <div class="commission-card featured" style="border: 2px solid var(--primary); box-shadow: 0 15px 40px rgba(15, 92, 71, 0.12);">
            <div class="card-badge" style="background: linear-gradient(135deg, var(--primary), var(--primary-light));">Le Plus Populaire</div>
            <h3 class="plan-title">Offre 2 — Professionnel</h3>
            <div class="plan-price-tag">Prix Client : {{ number_format($rules['professionnel']->prix, 0, ' ', ' ') }} FCFA / an (Multi-Magasins)</div>

            <div class="cash-box featured-box" style="background: rgba(15, 92, 71, 0.06); border-color: rgba(15, 92, 71, 0.15);">
                <div class="cash-lbl">Votre Commission Directe</div>
                <div class="cash-amount" style="color: var(--primary-dark);">{{ number_format($rules['professionnel']->commission, 0, ' ', ' ') }} FCFA</div>
                <div class="payout-type" style="color:var(--primary);"><i class="bi bi-check-circle-fill"></i> Versement unique cash</div>
            </div>

            <ul class="card-features-list">
                <li><i class="bi bi-check2"></i> <span>Multi-postes & utilisateurs illimités</span></li>
                <li><i class="bi bi-check2"></i> <span>Gestion des arrivages & importations</span></li>
                <li><i class="bi bi-check2"></i> <span>Calcul des coûts de revient & marges</span></li>
                <li><i class="bi bi-check2"></i> <span>Multi-magasins & dépôts d'entreprise</span></li>
            </ul>
        </div>

        <!-- Card 3 -->
        <div class="commission-card">
            <h3 class="plan-title">Offre 3 — Entreprise</h3>
            <div class="plan-price-tag">Prix Client : À partir de {{ number_format($rules['entreprise']->prix, 0, ' ', ' ') }} FCFA (À vie)</div>

            <div class="cash-box">
                <div class="cash-lbl">Votre Commission Directe</div>
                <div class="cash-amount">{{ number_format($rules['entreprise']->commission, 0, ' ', ' ') }} FCFA</div>
                <div class="payout-type"><i class="bi bi-check-circle-fill"></i> Versement unique cash</div>
            </div>

            <ul class="card-features-list">
                <li><i class="bi bi-check2"></i> <span>Licence à vie sur-mesure</span></li>
                <li><i class="bi bi-check2"></i> <span>Domaine & hébergement client dédiés</span></li>
                <li><i class="bi bi-check2"></i> <span>Base de données 100% isolée</span></li>
                <li><i class="bi bi-check2"></i> <span>Support VIP & installation personnalisée</span></li>
            </ul>
        </div>
    </div>
</section>

<!-- SECTION 3 : PRIMES DE PERFORMANCE PAR PALIERS -->
<section class="section" id="primes">
    <div class="section-header">
        <div class="section-tag" style="background:rgba(15, 92, 71, 0.08); color:var(--primary);">Super-Primes De Palier</div>
        <h2 class="section-title">Boostez vos gains avec les paliers de clients</h2>
        <p class="section-sub">Atteignez des objectifs de vente et recevez des primes de performance massives en bonus de vos commissions uniques.</p>
    </div>

    <div class="tiers-grid">
        @foreach($paliers as $idx => $seuil)
            @php
                $isDouble = $idx === 1;
                $isLast   = $idx === count($paliers) - 1;
                $title    = $isLast ? "Palier {$seuil} Clients & Plus" : "Palier {$seuil} Clients";
                $sub      = $isLast ? "Et ainsi de suite par tranche" : "Dès {$seuil} ventes validées";
                if ($isDouble) { $title .= ' (Le Double !)'; $sub = "Le doublement de vos primes"; }
            @endphp
            <div class="tier-card @if($isDouble) double @endif">
                @if($isDouble)
                    <div class="card-badge">Le Double !</div>
                @endif
                <h3 class="plan-title">{{ $title }}</h3>
                <div class="plan-price-tag">{{ $sub }}</div>

                <div class="prime-list">
                    <div class="prime-item">
                        <span class="prime-lbl">Prime Offre Essentiel</span>
                        <span class="prime-amt">+ {{ number_format($primesByCode['essentiel'][$seuil] ?? 0, 0, ' ', ' ') }} FCFA</span>
                    </div>
                    <div class="prime-item">
                        <span class="prime-lbl">Prime Offre Professionnel</span>
                        <span class="prime-amt">+ {{ number_format($primesByCode['professionnel'][$seuil] ?? 0, 0, ' ', ' ') }} FCFA</span>
                    </div>
                    <div class="prime-item">
                        <span class="prime-lbl">Prime Offre Entreprise</span>
                        <span class="prime-amt">+ {{ number_format($primesByCode['entreprise'][$seuil] ?? 0, 0, ' ', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</section>

<!-- SECTION 4 : FORMULAIRE DE CANDIDATURE MULTI-ÉTAPES -->
<section class="section" id="candidature" style="background:#fff;">
    <div class="section-header">
        <div class="section-tag">Inscription Rapide</div>
        <h2 class="section-title">Rejoignez l'équipe de Partenaires</h2>
        <p class="section-sub">Remplissez ce formulaire en 5 étapes pour valider votre compte partenaire et recevoir vos liens & codes de parrainage.</p>
    </div>

    <div class="form-container">
        <div class="form-card">
            @if(session('success'))
                <div style="background:#dcfce7; color:#166534; padding:16px; border-radius:12px; margin-bottom:24px; font-weight:700; display:flex; align-items:center; gap:10px;">
                    <i class="bi bi-check-circle-fill" style="font-size:1.3rem;"></i> {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div style="background:#fee2e2; color:#991b1b; padding:16px; border-radius:12px; margin-bottom:24px; font-weight:700;">
                    <i class="bi bi-exclamation-triangle-fill"></i> @foreach($errors->all() as $e) <div>{{ $e }}</div> @endforeach
                </div>
            @endif

            <!-- Stepper -->
            <div class="stepper-header">
                <div class="step-item active" data-step="1"><div class="circle">1</div><div class="label">Infos</div></div>
                <div class="step-item" data-step="2"><div class="circle">2</div><div class="label">Réseau</div></div>
                <div class="step-item" data-step="3"><div class="circle">3</div><div class="label">Expérience</div></div>
                <div class="step-item" data-step="4"><div class="circle">4</div><div class="label">Terrain</div></div>
                <div class="step-item" data-step="5"><div class="circle">5</div><div class="label">Motivation</div></div>
            </div>

            <form action="{{ route('prestataire.submit') }}" method="POST" id="candidatureForm">
                @csrf

                <!-- ÉTAPE 1 -->
                <div class="step-panel active" data-panel="1">
                    <div class="panel-title"><i class="bi bi-person-badge-fill" style="color:var(--primary);"></i> Vos Informations Personnelles</div>
                    <div class="panel-sub">Comment pouvons-nous vous contacter et valider votre profil ?</div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Nom *</label>
                            <input type="text" name="nom" class="form-control" placeholder="Votre nom" required>
                        </div>
                        <div class="form-group">
                            <label>Prénom *</label>
                            <input type="text" name="prenom" class="form-control" placeholder="Votre prénom" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Email *</label>
                            <input type="email" name="email" class="form-control" placeholder="votre@email.com" required>
                        </div>
                        <div class="form-group">
                            <label>Téléphone / WhatsApp *</label>
                            <input type="text" name="telephone" class="form-control" placeholder="+229 XX XX XX XX" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Ville / Zone d'intervention principale *</label>
                        <input type="text" name="q5" class="form-control" placeholder="Ex: Cotonou, Porto-Novo, Parakou..." required>
                    </div>
                    <div class="form-group">
                        <label>Activité principale actuelle *</label>
                        <input type="text" name="q6" class="form-control" placeholder="Ex: Agent commercial, Commerçant, Indépendant..." required>
                    </div>

                    <div class="step-buttons">
                        <button type="button" class="btn-step-next" onclick="goStep(2)">Continuer <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- ÉTAPE 2 -->
                <div class="step-panel" data-panel="2">
                    <div class="panel-title"><i class="bi bi-people-fill" style="color:var(--primary);"></i> Votre Réseau Commercial</div>
                    <div class="panel-sub">Évaluation du potentiel de votre réseau d'affaires.</div>

                    <div class="form-group">
                        <label>Nombre approximatif de commerces dans votre entourage ? *</label>
                        <select name="q7" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="1-5">1 à 5 commerces</option>
                            <option value="6-15">6 à 15 commerces</option>
                            <option value="16-30">16 à 30 commerces</option>
                            <option value="30+">Plus de 30 commerces</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Secteurs d'activité prédominants ? *</label>
                        <input type="text" name="q8" class="form-control" placeholder="Ex: Alimentation, Mode, Électronique, Dépôts..." required>
                    </div>

                    <div class="form-group">
                        <label>Profils cibles prioritaires ? *</label>
                        <select name="q9" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Détaillants">Boutiques & Détaillants</option>
                            <option value="Grossistes">Grossistes & Importateurs</option>
                            <option value="Marchés">Commerçants de marché</option>
                            <option value="Tous">Tous types de commerces</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Combien de prospects comptez-vous contacter par mois ? *</label>
                        <select name="q10" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="1-5">1 à 5 prospects</option>
                            <option value="6-15">6 à 15 prospects</option>
                            <option value="15+">Plus de 15 prospects</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Canaux de prospection privilégiés ? *</label>
                        <input type="text" name="q11" class="form-control" placeholder="Ex: Visites terrain, WhatsApp, Réseau direct..." required>
                    </div>

                    <div class="step-buttons">
                        <button type="button" class="btn-step-prev" onclick="goStep(1)"><i class="bi bi-arrow-left"></i> Retour</button>
                        <button type="button" class="btn-step-next" onclick="goStep(3)">Continuer <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- ÉTAPE 3 -->
                <div class="step-panel" data-panel="3">
                    <div class="panel-title"><i class="bi bi-award-fill" style="color:var(--primary);"></i> Expérience & Compétences</div>
                    <div class="panel-sub">Votre expérience en vente de solutions informatiques ou de services.</div>

                    <div class="form-group">
                        <label>Avez-vous déjà vendu un logiciel commercial ? *</label>
                        <select name="q12" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui">Oui, régulièrement</option>
                            <option value="Un peu">Un peu d'expérience</option>
                            <option value="Non">Non, première fois</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quel est votre principal atout commercial ? *</label>
                        <textarea name="q13" class="form-control" placeholder="Ex: Très bon relationnel avec les commerçants de ma ville..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Vos contacts utilisent-ils déjà un logiciel de gestion ? *</label>
                        <select name="q14" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Non, aucun">Non, gestion sur papier/cahier</option>
                            <option value="Oui, basique">Oui, logiciel basique</option>
                            <option value="Je ne sais pas">Je ne sais pas</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Quel est leur principal problème de gestion ? *</label>
                        <textarea name="q15" class="form-control" placeholder="Ex: Perte de stock, oubli des dettes clients, manque de calcul des marges d'importation..." required></textarea>
                    </div>

                    <div class="form-group">
                        <label>Savez-vous faire une démonstration produit ? *</label>
                        <select name="q16" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui, facilement">Oui, facilement</option>
                            <option value="Avec formation">Oui, après une formation rapide</option>
                            <option value="Besoin d'aide">J'aurai besoin d'accompagnement</option>
                        </select>
                    </div>

                    <div class="step-buttons">
                        <button type="button" class="btn-step-prev" onclick="goStep(2)"><i class="bi bi-arrow-left"></i> Retour</button>
                        <button type="button" class="btn-step-next" onclick="goStep(4)">Continuer <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- ÉTAPE 4 -->
                <div class="step-panel" data-panel="4">
                    <div class="panel-title"><i class="bi bi-geo-alt-fill" style="color:var(--primary);"></i> Disponibilité & Action Terrain</div>
                    <div class="panel-sub">Votre organisation pratique sur le terrain.</div>

                    <div class="form-group">
                        <label>Êtes-vous prêt à vous déplacer chez les commerçants ? *</label>
                        <select name="q17" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui">Oui, tout à fait</option>
                            <option value="Occasionnellement">Occasionnellement</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Pouvez-vous aider à l'installation de départ ? *</label>
                        <select name="q18" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui">Oui, sans problème</option>
                            <option value="Avec support">Avec un petit support de l'équipe</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Temps disponible par semaine ? *</label>
                        <select name="q19" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="1-3h">1 à 3 heures</option>
                            <option value="4-8h">4 à 8 heures</option>
                            <option value="8-15h">8 à 15 heures</option>
                            <option value="Temps plein">Temps plein (+15h)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Moyen de déplacement ? *</label>
                        <select name="q20" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Moto">Moto</option>
                            <option value="Voiture">Voiture</option>
                            <option value="Transport">Transport en commun</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Avez-vous des contacts dans plusieurs villes ? *</label>
                        <select name="q21" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui, plusieurs villes">Oui, plusieurs villes</option>
                            <option value="Non, ma ville">Non, uniquement ma ville</option>
                        </select>
                    </div>

                    <div class="step-buttons">
                        <button type="button" class="btn-step-prev" onclick="goStep(3)"><i class="bi bi-arrow-left"></i> Retour</button>
                        <button type="button" class="btn-step-next" onclick="goStep(5)">Continuer <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- ÉTAPE 5 -->
                <div class="step-panel" data-panel="5">
                    <div class="panel-title"><i class="bi bi-rocket-takeoff-fill" style="color:var(--primary);"></i> Objectifs & Finalisation</div>
                    <div class="panel-sub">Dernière étape pour finaliser votre dossier.</div>

                    <div class="form-group">
                        <label>Souhaitez-vous suivre une formation rapide offerte par PILOTIX ? *</label>
                        <select name="q22" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui, avec plaisir">Oui, avec plaisir</option>
                            <option value="Non">Non merci</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Objectif de ventes mensuelles estimé ? *</label>
                        <select name="q23" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="1-2 clients">1 à 2 clients / mois</option>
                            <option value="3-5 clients">3 à 5 clients / mois (Bonus 50k - 250k F)</option>
                            <option value="10+ clients">10+ clients / mois (Super Bonus Double !)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Type de rémunération souhaité ? *</label>
                        <select name="q24" class="form-control" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Commissions directes + Primes">Commissions cash uniques + Primes de performance (5, 10+ clients)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Motif de votre candidature & message *</label>
                        <textarea name="q25" class="form-control" placeholder="Dites-nous ce qui vous motive à rejoindre PILOTIX..." required></textarea>
                    </div>

                    <div class="step-buttons">
                        <button type="button" class="btn-step-prev" onclick="goStep(4)"><i class="bi bi-arrow-left"></i> Retour</button>
                        <button type="submit" class="btn-step-submit"><i class="bi bi-send-fill"></i> Valider Ma Candidature</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</section>

<!-- FOOTER -->
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
        <span>&copy; {{ date('Y') }} PILOTIX — Programme d'Affiliation &amp; Réseau Partenaire</span>
        <span><a href="{{ route('conditions') }}" style="color:inherit;">Conditions</a> · <a href="{{ route('confidentialite') }}" style="color:inherit;">Confidentialité</a> · <a href="{{ route('partenaires') }}" style="color:inherit;">Partenariat</a></span>
    </div>
</footer>

    <script>
        // Logic for Interactive Calculator (calcul par offre)
        const planPrices = @json($calcData);
        const planCodes = ['essentiel', 'professionnel', 'entreprise'];
        const paliers = @json($paliers);

        function formatFcfa(val) {
            return new Intl.NumberFormat('fr-FR').format(Math.round(val)) + ' FCFA';
        }

        function updateCalc() {
            let directTotal = 0;
            let bonusTotal = 0;

            planCodes.forEach(function (plan) {
                const nb = parseInt(document.getElementById('nb_' + plan).value) || 0;
                const rates = planPrices[plan];
                if (!rates) return;

                directTotal += nb * rates.direct;

                // Prime du palier le plus élevé atteint.
                let prime = 0;
                paliers.forEach(function (seuil) {
                    if (nb >= seuil) {
                        prime = rates.primes[seuil] || 0;
                    }
                });
                bonusTotal += prime;
            });

            const grandTotal = directTotal + bonusTotal;

            document.getElementById('resDirect').textContent = formatFcfa(directTotal);
            document.getElementById('resBonus').textContent = bonusTotal > 0 ? '+ ' + formatFcfa(bonusTotal) : '0 FCFA';
            document.getElementById('resTotal').textContent = formatFcfa(grandTotal);
        }

    // Wizard Step Navigation
    function goStep(n) {
        const current = document.querySelector('.step-panel.active');
        const currentNum = parseInt(current.dataset.panel);

        if (n > currentNum) {
            const inputs = current.querySelectorAll('input[required], select[required], textarea[required]');
            let valid = true;
            inputs.forEach(inp => {
                if (!inp.value.trim()) { inp.style.borderColor = '#ef4444'; valid = false; }
                else { inp.style.borderColor = ''; }
            });
            if (!valid) { inputs[0].focus(); return; }
        }

        document.querySelectorAll('.step-panel').forEach(p => p.classList.remove('active'));
        document.querySelector(`[data-panel="${n}"]`).classList.add('active');

        document.querySelectorAll('.step-item').forEach(d => {
            const s = parseInt(d.dataset.step);
            d.classList.remove('active', 'done');
            if (s === n) d.classList.add('active');
            else if (s < n) d.classList.add('done');
        });

        current.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Hamburger Menu Toggle
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mobileMenu = document.getElementById('mobileMenu');

    if(hamburgerBtn && mobileMenu) {
        hamburgerBtn.addEventListener('click', () => {
            hamburgerBtn.classList.toggle('open');
            mobileMenu.classList.toggle('active');
        });
    }

    // Init Calc
    updateCalc();
</script>

</body>
</html>
