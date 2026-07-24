<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Tableau de bord') — PILOTRIX</title>
    <meta name="description" content="@yield('meta_description', 'PILOTRIX — Gestion de stock, ventes, clients, livraisons et dettes. Application de gestion commerciale multi-magasins.')">
    <meta name="keywords" content="gestion stock, ventes, clients, livraisons, dettes, multi-magasins, applicaition gestion, pilotrix">
    <meta name="robots" content="index, follow">
    <meta property="og:title" content="@yield('title', 'Tableau de bord') — PILOTRIX">
    <meta property="og:description" content="Gestion de stock, ventes, clients, livraisons et dettes. Application de gestion commerciale multi-magasins.">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="PILOTRIX">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- PWA Meta -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#105e49">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="PILOTRIX">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192x192.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon.ico">

    <style>
        :root {
            --primary: #105e49;
            --primary-light: #167e65;
            --secondary: #ea8d22;
            --success: #16a34a;
            --danger: #dc2626;
            --warning: #d97706;
            --bg: #f4f6f8;
            --bg-card: #ffffff;
            --text: #333333;
            --text-muted: #6b7280;
            --border: #e5e7eb;
            --shadow-sm: 0 1px 2px rgba(0,0,0,0.05);
            --shadow-card: 0 4px 6px -1px rgba(0,0,0,.05), 0 2px 4px -1px rgba(0,0,0,.03);
            --radius-card: 8px;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            font-family: 'Montserrat', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            margin: 0;
            padding: 0;
            width: 100%;
            overflow-x: hidden;
        }

        /* ─── Typography & Utilities ─── */
        h1, h2, h3 { color: var(--text); margin-bottom: 16px; font-weight: 700; }
        a { color: var(--primary); text-decoration: none; }
        a.text-danger { color: var(--danger); }
        .text-muted { color: var(--text-muted); font-size: 0.85rem; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: 700; }
        
        .d-flex { display: flex; }
        .align-items-center { align-items: center; }
        .justify-content-between { justify-content: space-between; }
        .mb-2 { margin-bottom: 8px; }
        .mb-3 { margin-bottom: 16px; }
        .mb-4 { margin-bottom: 24px; }
        .mt-2 { margin-top: 8px; }
        .mt-4 { margin-top: 24px; }

        /* ─── Top Header ─── */
        .app-header {
            background: #ffffff;
            border-bottom: 2px solid var(--secondary);
            padding: 0 24px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 10px rgba(0,0,0,0.03);
        }
        @media (max-width: 640px) {
            .app-header { padding: 0 12px; height: 56px; }
            .header-brand .logo-text { font-size: 1.3rem; }
            .header-user { gap: 8px; font-size: 0.8rem; }
            .header-user span { display: none; }
        }

        .header-brand {
            display: flex;
            align-items: flex-start;
            text-decoration: none;
            gap: 0;
        }

        .header-brand .logo-text {
            font-family: 'Montserrat', sans-serif;
            color: var(--primary);
            font-weight: 900;
            font-size: 1.6rem;
            letter-spacing: -1.5px;
            text-transform: uppercase;
        }

        .header-user {
            display: flex;
            align-items: center;
            gap: 15px;
            font-weight: 500;
            font-size: 0.9rem;
            color: var(--text);
        }

        /* ─── Breadcrumb Bar ─── */
        .breadcrumb-bar {
            background: #ffffff;
            border-bottom: 1px solid var(--border);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 0.9rem;
            color: var(--text-muted);
            font-weight: 500;
            margin-bottom: 30px;
            position: relative;
        }
        @media (max-width: 1100px) {
            .breadcrumb-bar { padding: 10px 16px; flex-wrap: wrap; gap: 6px; }
        }

        /* ─── Main Content ─── */
        .main-container {
            padding: 0 24px 40px;
            width: 100%;
        }
        @media (max-width: 640px) {
            .main-container { padding: 0 12px 24px; }
            .main-container > .d-flex { flex-wrap: wrap; gap: 8px; }
        }

        /* ─── Global Forms & UI ─── */
        .card { background: var(--bg-card); border-radius: var(--radius-card); box-shadow: var(--shadow-card); padding: 24px; margin-bottom: 24px; border: 1px solid var(--border); }
        .card-header { border-bottom: 1px solid var(--border); padding-bottom: 16px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center; }
        .card-header h3 { margin: 0; font-size: 1.1rem; }
        @media (max-width: 640px) {
            .card { padding: 16px; margin-bottom: 16px; }
            .card-header { flex-wrap: wrap; gap: 8px; }
            .card-header h3 { font-size: 1rem; }
            h1 { font-size: 1.3rem !important; }
        }

        .alert { padding: 12px 16px; border-radius: 6px; margin-bottom: 20px; font-size: 0.9rem; font-family: 'Inter', sans-serif; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        .form-group { margin-bottom: 16px; font-family: 'Inter', sans-serif; }
        .form-label { display: block; margin-bottom: 6px; font-size: 0.85rem; font-weight: 600; color: var(--text); }
        .form-control { width: 100%; padding: 10px 14px; border: 1px solid var(--border); border-radius: 6px; font-size: 0.9rem; transition: border-color 0.2s; background: #fff; color: var(--text); }
        .form-control:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(16, 94, 73, 0.1); }
        
        .form-row { display: grid; gap: 16px; grid-template-columns: 1fr; }
        @media(min-width: 768px) {
            .form-row-2 { grid-template-columns: 1fr 1fr; }
            .form-row-3 { grid-template-columns: 1fr 1fr 1fr; }
        }

        /* ─── Page Grid (responsive 2 columns) ─── */
        .page-grid { display: grid; gap: 24px; grid-template-columns: 1fr; }
        .page-grid > * { min-width: 0; }
        @media (max-width: 640px) { .page-grid { gap: 12px; } }
        @media (min-width: 1058px) {
            .page-grid-3 { grid-template-columns: 2fr 1fr; }
        }

        /* ─── Flex row mobile (stack on small screens) ─── */
        @media (max-width: 640px) {
            .flex-row-mobile { flex-direction: column; }
            .flex-row-mobile > div { width: 100% !important; flex: none !important; }
        }

        /* ─── Breadcrumb Navigation Links ─── */
        .breadcrumb-nav-links { margin-left: auto; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }

        /* ─── Mobile nav toggle ─── */
        .nav-toggle { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text); padding: 4px; }
        @media (max-width: 1100px) {
            .nav-toggle { display: flex; align-items: center; margin-left: auto; }
            .breadcrumb-nav-links { display: none; }
            .breadcrumb-nav-links.open { display: flex; flex-direction: column; position: absolute; top: 100%; left: 0; right: 0; background: #fff; padding: 12px; border: 1px solid var(--border); box-shadow: var(--shadow-card); z-index: 999; gap: 4px; }
            .breadcrumb-nav-links.open .nav-link { width: 100%; }
            .nav-dropdown { width: 100%; }
            .nav-dropdown-btn { width: 100%; justify-content: center; }
            .nav-dropdown-menu { position: static; box-shadow: none; border: none; background: var(--bg); margin-top: 4px; }
        }

        .btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; padding: 10px 20px; border-radius: 6px; font-weight: 600; font-size: 0.9rem; cursor: pointer; border: none; text-decoration: none; font-family: 'Inter', sans-serif; transition: all 0.2s; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-primary:hover { background: var(--primary-light); }
        .btn-secondary { background: #f1f5f9; color: var(--text); border: 1px solid var(--border); }
        .btn-secondary:hover { background: #e2e8f0; }
        .btn-danger { background: #fee2e2; color: var(--danger); border: 1px solid #fecaca; }
        .btn-danger:hover { background: #fca5a5; color: #7f1d1d; }
        .btn-sm { padding: 6px 12px; font-size: 0.85rem; }

        /* Generic table fix for new layout */
        .table-wrap { overflow-x: auto; background: var(--bg-card); border-radius: var(--radius-card); border: 1px solid var(--border); margin-bottom: 24px; -webkit-overflow-scrolling: touch; }
        table { width: 100%; border-collapse: collapse; font-family: 'Inter', sans-serif; font-size: 0.9rem; }
        th, td { padding: 12px 16px; text-align: left; border-bottom: 1px solid var(--border); white-space: nowrap; }
        th { background: #f9fafb; font-weight: 600; color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        tr:hover td { background: #f8fafc; }
        tr:last-child td { border-bottom: none; }
        th.wrap-text, td.wrap-text { white-space: normal !important; min-width: 150px; }
        @media (max-width: 640px) {
            th, td { padding: 8px 10px; font-size: 0.8rem; }
        }

        /* Badges */
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-gray { background: #f1f5f9; color: #475569; }

        /* Pagination */
        .pagination { display: flex; align-items: center; gap: 4px; list-style: none; padding: 12px 0; margin: 0; flex-wrap: wrap; justify-content: center; }
        .pagination li { margin: 0; }
        .pagination li a, .pagination li span { display: inline-flex; align-items: center; justify-content: center; min-width: 32px; height: 32px; padding: 0 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 500; text-decoration: none; color: var(--text); border: 1px solid var(--border); background: #fff; transition: all 0.15s; }
        .pagination li a:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
        .pagination li.active span { background: var(--primary); color: #fff; border-color: var(--primary); font-weight: 700; }
        .pagination li.disabled span { color: #cbd5e1; border-color: #e2e8f0; background: #f8fafc; cursor: not-allowed; }
        .pagination svg { width: 14px; height: 14px; flex-shrink: 0; }
        @media (max-width: 640px) { .pagination { gap: 2px; } .pagination li a, .pagination li span { min-width: 28px; height: 28px; font-size: 0.75rem; padding: 0 6px; } .pagination svg { width: 12px; height: 12px; } }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: var(--radius-card); padding: 20px; border: 1px solid var(--border); display: flex; align-items: flex-start; gap: 14px; box-shadow: var(--shadow-sm); min-width: 0; overflow: hidden; }
        .stat-card > div:last-child { min-width: 0; overflow: hidden; flex: 1; }
        @media (max-width: 1100px) {
            .stats-grid { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 640px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 8px; }
            .stat-card { padding: 10px; gap: 8px; }
            .stat-icon { width: 32px !important; height: 32px !important; font-size: 0.9rem !important; flex-shrink: 0; }
            .stat-val { font-size: clamp(0.8rem, 3.5vw, 1.15rem) !important; }
            .stat-lbl { font-size: 0.58rem !important; }
        }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .stat-icon.blue { background: #dbeafe; color: #1d4ed8; }
        .stat-icon.green { background: #dcfce7; color: #15803d; }
        .stat-icon.orange { background: #ffedd5; color: #c2410c; }
        .stat-icon.red { background: #fee2e2; color: #b91c1c; }
        .stat-val { font-size: clamp(0.85rem, 2vw, 1.5rem); font-weight: 700; line-height: 1; margin-bottom: 4px; overflow: hidden; text-overflow: ellipsis; }
        .stat-lbl { font-size: clamp(0.6rem, 1.1vw, 0.75rem); color: var(--text-muted); font-weight: 500; text-transform: uppercase; overflow: hidden; text-overflow: ellipsis; line-height: 1.3; }

        /* ─── Breadcrumb Nav Links ─── */
        .nav-link { color: var(--text-muted); text-decoration: none; font-weight: 600; font-size: 0.82rem; padding: 5px 10px; border-radius: 6px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 5px; }
        .nav-link:hover { background: rgba(0,0,0,0.03); color: var(--text); }
        .nav-link.active { color: #fff; background: var(--primary); }
        .nav-link i { font-size: 0.95rem; line-height: 1; display: inline-flex; align-items: center; }
        /* ─── Nav Dropdown (Plus menu) ─── */
        .nav-dropdown { position: relative; display: inline-flex; }
        .nav-dropdown-btn { color: var(--text-muted); background: none; border: 1px solid var(--border); font-weight: 600; font-size: 0.82rem; padding: 5px 10px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 5px; transition: all 0.2s; }
        .nav-dropdown-btn:hover { background: rgba(0,0,0,0.03); color: var(--text); border-color: #cbd5e1; }
        .nav-dropdown-btn i { font-size: 0.85rem; transition: transform 0.2s; }
        .nav-dropdown-btn.open i { transform: rotate(180deg); }
        .nav-dropdown-menu { display: none; position: absolute; top: calc(100% + 6px); right: 0; background: #fff; border: 1px solid var(--border); border-radius: 10px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); min-width: 200px; z-index: 1000; padding: 6px; }
        .nav-dropdown-menu.show { display: block; }
        .nav-dropdown-menu .nav-link { width: 100%; padding: 8px 12px; font-size: 0.82rem; border-radius: 6px; }
        .nav-dropdown-menu .nav-link:hover { background: var(--bg); }
        .nav-dropdown-menu .nav-link.active { color: #fff; background: var(--primary); }
        @media (max-width: 640px) {
            .nav-dropdown-menu { right: auto; left: 0; }
        }

        /* ─── Page Grid 2 columns ─── */
        .page-grid-2 { grid-template-columns: 1fr; }
        @media(min-width: 992px) { .page-grid-2 { grid-template-columns: 1fr 1fr; } }

        /* ─── Stock Sidebar ─── */
        .stock-sidebar { display: flex; flex-direction: column; gap: 20px; }
        .stock-mouvements-card { padding: 16px; display: flex; flex-direction: column; gap: 8px; justify-content: center; align-items: center; text-align: center; }
        .stock-mouvements-card i { font-size: 2rem; color: var(--primary); }
        .stock-mouvements-card h4 { font-size: .85rem; font-weight: 600; margin: 0; }
        .stock-mouvements-card p { font-size: .75rem; color: var(--text-muted); margin: 0; }
        .btn-full { width: 100%; justify-content: center; }
        .stock-header { flex-wrap: wrap; gap: 12px; }
        .stock-header select { width: auto; display: inline-block; }
        .prix-cell { white-space: nowrap; }
        .stock-table { margin-bottom: 0; border: none; }
        .td-ref { font-size: 0.8rem; color: var(--text-muted); }
        @media (max-width: 768px) {
            .stock-sidebar { gap: 16px; }
            .stock-header { flex-direction: column; align-items: stretch; }
            .stock-header select { width: 100%; }
        }
        @media (max-width: 640px) {
            .td-ref { font-size: 0.7rem; }
            .th-prix, td.prix-cell { display: none; }
        }
        @media (max-width: 480px) {
            td:first-child, th:first-child { padding-left: 8px; }
            td:last-child, th:last-child { padding-right: 8px; }
        }

        /* ─── Flex row mobile base ─── */
        .flex-row-mobile { display: flex; gap: 8px; flex-wrap: wrap; }
        .flex-row-mobile > div { min-width: 120px; flex: 1; }

        /* ─── Responsive: generic ─── */
        .is-invalid { border-color: var(--danger) !important; }
        .grid-2 { display: grid; grid-template-columns: 1fr; gap: 20px; }
        @media(min-width: 768px) { .grid-2 { grid-template-columns: 1fr 1fr; } }
        @media(max-width: 576px) { .invoice-card { padding: 16px !important; } }
        @media(max-width: 768px) {
            .form-row:not(.flex-row-mobile) { grid-template-columns: 1fr !important; }
        }

        /* ─── Custom Checkbox ─── */
        .checkbox-group { padding: 4px 0; }
        .checkbox-label { display: inline-flex; align-items: center; gap: 10px; cursor: pointer; font-size: 0.95rem; user-select: none; }
        .checkbox-label input[type="checkbox"] { display: none; }
        .checkbox-custom { width: 22px; height: 22px; border: 2px solid var(--border); border-radius: 5px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s; flex-shrink: 0; }
        .checkbox-label input:checked + .checkbox-custom { background: var(--primary); border-color: var(--primary); }
        .checkbox-label input:checked + .checkbox-custom::after { content: "✓"; color: #fff; font-size: 14px; font-weight: 700; }
        .checkbox-label:hover .checkbox-custom { border-color: var(--primary); }

    </style>
    <style>
        .table-search-wrap {
            display: flex; align-items: center; gap: 8px; padding: 12px 20px;
            border-bottom: 1px solid var(--border);
        }
        .table-search-field {
            position: relative; flex: 1; max-width: 360px;
        }
        .table-search-wrap .table-search-input {
            width: 100%; padding: 8px 12px 8px 36px;
            border: 1px solid var(--border); border-radius: 8px;
            font-size: .85rem; background: var(--bg); color: var(--text);
            transition: border-color .2s;
        }
        .table-search-wrap .table-search-input:focus {
            outline: none; border-color: var(--primary);
        }
        .table-search-field .table-search-icon {
            position: absolute; left: 12px; top: 50%; transform: translateY(-50%);
            color: var(--text-muted); font-size: .9rem; pointer-events: none;
        }
        .table-search-wrap .table-search-count {
            font-size: .75rem; color: var(--text-muted); white-space: nowrap;
        }
        @media (max-width: 640px) {
            .table-search-wrap { padding: 10px 12px; }
            .table-search-wrap .table-search-input { max-width: 100%; font-size: .8rem; }
        }
    </style>
    @stack('styles')
</head>
<body>

@auth
    <!-- Top Header -->
    <header class="app-header">
        <a href="{{ url('/') }}" class="header-brand" id="headerBrand">
            <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix" style="height: 56px; width: 56px; object-fit: contain; border-radius: 12px;">
        </a>

        <div class="header-user">
            <a href="{{ route('download') }}" title="Télécharger l'app" id="downloadLink" style="display:flex; align-items:center; text-decoration:none; color:var(--primary); background:rgba(16,94,73,.08); width:40px; height:40px; border-radius:10px; justify-content:center; flex-shrink:0;">
                <i class="bi bi-download" style="font-size:1.2rem;"></i>
            </a>
            <a href="{{ route('profile') }}" style="display:flex; align-items:center; gap:8px; text-decoration:none; color:inherit;">
                <i class="bi bi-person-circle" style="font-size: 1.8rem; color: var(--primary);"></i>
                <span>{{ auth()->user()->name }}</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" data-no-api="true" style="margin-left:8px;">
                @csrf
                <button type="submit" style="background:none; border:none; cursor:pointer; display:flex; align-items:center;">
                    <i class="bi bi-box-arrow-right" style="font-size: 1.5rem; color: var(--text-muted);"></i>
                </button>
            </form>
        </div>
    </header>

    <!-- Breadcrumb (Visible only if authenticated) -->
    <div class="breadcrumb-bar">
        <a href="{{ route('dashboard') }}" style="color: {{ request()->routeIs('dashboard') ? 'var(--primary)' : 'var(--text-muted)' }}; text-decoration: none;">
            <i class="bi bi-house-door-fill" style="font-size: 1.3rem;"></i>
        </a>
        <i class="bi bi-chevron-right" style="font-size: 0.7rem; color: #cbd5e1;"></i>
        <span>@yield('title', 'Tableau de bord')</span>
        
        <button class="nav-toggle" id="navToggle" aria-label="Menu navigation">
            <i class="bi bi-list"></i>
        </button>

        <div class="breadcrumb-nav-links">
            @if(Auth::user()->isSuperAdmin())
                {{-- Le Super Admin ne voit que la gestion des sociétés --}}
                <a href="{{ route('tenants.index') }}" class="nav-link {{ request()->routeIs('tenants.*') ? 'active' : '' }}">
                    <i class="bi bi-building-fill-check"></i> Sociétés
                </a>
                <a href="{{ route('admin.prestataires') }}" class="nav-link {{ request()->routeIs('admin.prestataires') ? 'active' : '' }}">
                    <i class="bi bi-person-plus"></i> Partenaires
                </a>
                <a href="{{ route('admin.commissions') }}" class="nav-link {{ request()->routeIs('admin.commissions') ? 'active' : '' }}">
                    <i class="bi bi-wallet2"></i> Commissions
                </a>
            @else
                {{-- Liens métier seulement si l'utilisateur a un tenant associé --}}
                @if(Auth::user()->tenant)
                @if(Auth::user()->peutGererProduits())
                <a href="{{ route('produits.index') }}" class="nav-link {{ request()->routeIs('produits.*') ? 'active' : '' }}">
                    <i class="bi bi-box2"></i> Produits
                </a>
                @endif
                @if(Auth::user()->peutGererArrivages())
                <a href="{{ route('arrivages.index') }}" class="nav-link {{ request()->routeIs('arrivages.*') ? 'active' : '' }}">
                    <i class="bi bi-truck"></i> Arrivages
                </a>
                @endif
                @if(Auth::user()->peutGererVentes())
                <a href="{{ route('ventes.index') }}" class="nav-link {{ request()->routeIs('ventes.*') ? 'active' : '' }}">
                    <i class="bi bi-cart"></i> Ventes
                </a>
                @endif
                @if(Auth::user()->peutGererLivraisons())
                <a href="{{ route('livraisons.index') }}" class="nav-link {{ request()->routeIs('livraisons.*') ? 'active' : '' }}">
                    <i class="bi bi-truck-flatbed"></i> Livraisons
                </a>
                @endif
                @if(Auth::user()->peutGererClients())
                <a href="{{ route('clients.index') }}" class="nav-link {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                    <i class="bi bi-people"></i> Clients
                </a>
                @endif
                @if(Auth::user()->peutGererDettes())
                <a href="{{ route('dettes.index') }}" class="nav-link {{ request()->routeIs('dettes.*') ? 'active' : '' }}" style="{{ !request()->routeIs('dettes.*') ? 'color: var(--danger);' : '' }}">
                    <i class="bi bi-credit-card-2-back"></i> Dettes
                </a>
                @endif
                @if(Auth::user()->peutGererTransferts())
                <a href="{{ route('transferts.index') }}" class="nav-link {{ request()->routeIs('transferts.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i> Transferts
                </a>
                @endif

                {{-- Menu déroulant : liens secondaires --}}
                @php
                    $hasSecondary = Auth::user()->peutGererDettes() || Auth::user()->peutGererStock() || Auth::user()->peutGererMagasins() || Auth::user()->peutGererUtilisateurs();
                    $isSecondaryActive = request()->routeIs('dettes-societe.*') || request()->routeIs('stock.*') || request()->routeIs('magasins.*') || request()->routeIs('employes.*') || request()->routeIs('faq');
                @endphp
                @if($hasSecondary)
                <div class="nav-dropdown" id="navDropdown">
                    <button class="nav-dropdown-btn {{ $isSecondaryActive ? 'open' : '' }}" onclick="document.getElementById('navDropdown').querySelector('.nav-dropdown-menu').classList.toggle('show'); this.classList.toggle('open');">
                        <i class="bi bi-three-dots"></i> Plus <i class="bi bi-chevron-down"></i>
                    </button>
                    <div class="nav-dropdown-menu">
                        @if(Auth::user()->peutGererDettes())
                        <a href="{{ route('dettes-societe.index') }}" class="nav-link {{ request()->routeIs('dettes-societe.*') ? 'active' : '' }}" style="{{ !request()->routeIs('dettes-societe.*') ? 'color: var(--danger);' : '' }}">
                            <i class="bi bi-building"></i> Dettes Société
                        </a>
                        @endif
                        @if(Auth::user()->peutGererStock())
                        <a href="{{ route('stock.index') }}" class="nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                            <i class="bi bi-boxes"></i> Stock
                        </a>
                        @endif
                        @if(Auth::user()->peutGererMagasins())
                        <a href="{{ route('magasins.index') }}" class="nav-link {{ request()->routeIs('magasins.*') ? 'active' : '' }}">
                            <i class="bi bi-shop"></i> Dépôts
                        </a>
                        @endif
                        @if(Auth::user()->peutGererUtilisateurs())
                        <a href="{{ route('employes.index') }}" class="nav-link {{ request()->routeIs('employes.*') ? 'active' : '' }}">
                            <i class="bi bi-person-badge"></i> Employés
                        </a>
                        @endif
                        <a href="{{ route('faq') }}" class="nav-link {{ request()->routeIs('faq') ? 'active' : '' }}">
                            <i class="bi bi-question-circle"></i> FAQ
                        </a>
                    </div>
                </div>
                @endif
                @endif

                @if(!Auth::user()->tenant)
                <a href="{{ route('faq') }}" class="nav-link {{ request()->routeIs('faq') ? 'active' : '' }}">
                    <i class="bi bi-question-circle"></i> FAQ
                </a>
                @endif
            @endif

            {{-- Onglets prestataire - visible pour tous les utilisateurs avec le rôle prestataire --}}
            @if(Auth::user()->hasRole('prestataire'))
            <a href="{{ route('prestataire.dashboard') }}" class="nav-link {{ request()->routeIs('prestataire.dashboard') ? 'active' : '' }}">
                <i class="bi bi-speedometer2"></i> Mon Business
            </a>
            <a href="{{ route('prestataire.mes-societes') }}" class="nav-link {{ request()->routeIs('prestataire.mes-societes') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Mes Sociétés
            </a>
            @endif
        </div>
    </div>

    <!-- Main Content Container -->
    <main class="main-container">
        <!-- Header for pages -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 style="margin:0; font-size:1.5rem; font-weight:800; color:var(--text); letter-spacing:-0.5px;">@yield('title')</h1>
                @hasSection('subtitle')
                    <p style="margin:4px 0 0; color:var(--text-muted); font-size:0.9rem;">@yield('subtitle')</p>
                @endif
            </div>
            @yield('actions')
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="bi bi-check-circle-fill"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {{ session('error') }}
            </div>
        @endif
        @if(session('warning'))
            <div class="alert" style="background:#fff3cd; color:#856404; border:1px solid #ffecb5;">
                <i class="bi bi-exclamation-triangle-fill"></i>
                {!! nl2br(e(session('warning'))) !!}
            </div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger" style="display: block;">
                <div style="margin-bottom: 8px;"><i class="bi bi-exclamation-triangle"></i> <strong>Erreurs :</strong></div>
                <ul style="margin:0 0 0 16px; font-size: 0.85rem;">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    {{-- Mini calculatrice flottante --}}
    @unless(request()->routeIs('ventes.show', 'dettes.show'))
    <div id="calcFab" class="calc-fab" title="Calculatrice">
        <i class="bi bi-calculator"></i>
    </div>

    <div id="calcModal" class="calc-modal">
        <div class="calc-header">
            <span><i class="bi bi-calculator"></i> Calculatrice</span>
            <div class="calc-header-actions">
                <button class="calc-btn-icon" id="calcHistoryBtn" title="Historique"><i class="bi bi-clock-history"></i></button>
                <button class="calc-btn-icon" id="calcClose" title="Fermer"><i class="bi bi-x-lg"></i></button>
            </div>
        </div>
        <div id="calcHistory" class="calc-history" style="display:none;"></div>
        <div class="calc-display">
            <div class="calc-expr" id="calcExpr"></div>
            <div class="calc-result" id="calcResult">0</div>
        </div>
        <div class="calc-buttons">
            <button class="calc-btn calc-btn-fn" data-action="clear">C</button>
            <button class="calc-btn calc-btn-fn" data-action="backspace"><i class="bi bi-backspace"></i></button>
            <button class="calc-btn calc-btn-fn" data-action="percent">%</button>
            <button class="calc-btn calc-btn-op" data-action="divide"><i class="bi bi-slash-lg"></i></button>

            <button class="calc-btn" data-action="digit" data-value="7">7</button>
            <button class="calc-btn" data-action="digit" data-value="8">8</button>
            <button class="calc-btn" data-action="digit" data-value="9">9</button>
            <button class="calc-btn calc-btn-op" data-action="multiply"><i class="bi bi-x-lg"></i></button>

            <button class="calc-btn" data-action="digit" data-value="4">4</button>
            <button class="calc-btn" data-action="digit" data-value="5">5</button>
            <button class="calc-btn" data-action="digit" data-value="6">6</button>
            <button class="calc-btn calc-btn-op" data-action="subtract"><i class="bi bi-dash-lg"></i></button>

            <button class="calc-btn" data-action="digit" data-value="1">1</button>
            <button class="calc-btn" data-action="digit" data-value="2">2</button>
            <button class="calc-btn" data-action="digit" data-value="3">3</button>
            <button class="calc-btn calc-btn-op" data-action="add"><i class="bi bi-plus-lg"></i></button>

            <button class="calc-btn" data-action="negate">±</button>
            <button class="calc-btn" data-action="digit" data-value="0">0</button>
            <button class="calc-btn" data-action="decimal">,</button>
            <button class="calc-btn calc-btn-eq" data-action="equals">=</button>

            <button class="calc-btn calc-btn-copy" data-action="copy-expr" style="grid-column:span 2;"><i class="bi bi-file-text"></i> Copier expression</button>
            <button class="calc-btn calc-btn-copy" data-action="copy-result" style="grid-column:span 2;"><i class="bi bi-clipboard"></i> Copier résultat</button>
        </div>
    </div>

    <div id="calcOverlay" class="calc-overlay"></div>

    <style>
        .calc-fab {
            position: fixed; bottom: 24px; right: 24px; z-index: 9999;
            width: 56px; height: 56px; border-radius: 50%;
            background: var(--primary); color: #fff;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.5rem; cursor: pointer;
            box-shadow: 0 4px 16px rgba(16,94,73,.35);
            transition: transform .2s, box-shadow .2s;
            border: none;
        }
        .calc-fab:hover { transform: scale(1.08); box-shadow: 0 6px 24px rgba(16,94,73,.45); }
        .calc-fab:active { transform: scale(.95); }

        .calc-overlay {
            position: fixed; inset: 0; z-index: 9998;
            background: rgba(0,0,0,.3); display: none;
        }
        .calc-overlay.open { display: block; }

        .calc-modal {
            position: fixed; bottom: 90px; right: 24px; z-index: 10000;
            width: 340px; background: #fff; border-radius: 16px;
            box-shadow: 0 16px 48px rgba(0,0,0,.2);
            display: none; flex-direction: column;
            overflow: hidden; border: 1px solid var(--border);
        }
        .calc-modal.open { display: flex; }

        .calc-header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 12px 16px; background: var(--primary); color: #fff;
            font-weight: 700; font-size: .9rem;
        }
        .calc-header-actions { display: flex; gap: 6px; }
        .calc-btn-icon {
            background: rgba(255,255,255,.15); border: none; color: #fff;
            width: 30px; height: 30px; border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; font-size: .85rem; transition: background .15s;
        }
        .calc-btn-icon:hover { background: rgba(255,255,255,.25); }

        .calc-history {
            background: #f8fafc; border-bottom: 1px solid var(--border);
            max-height: 160px; overflow-y: auto; padding: 8px 12px;
            font-size: .8rem; font-family: 'Inter', monospace;
        }
        .calc-history-item {
            padding: 4px 0; border-bottom: 1px solid #e2e8f0;
            display: flex; justify-content: space-between; align-items: center;
        }
        .calc-history-item:last-child { border-bottom: none; }
        .calc-history-item .h-expr { color: var(--text-muted); }
        .calc-history-item .h-result { font-weight: 700; color: var(--primary); }
        .calc-history-clear {
            background: none; border: none; color: var(--danger);
            cursor: pointer; font-size: .7rem; padding: 4px 0;
            text-align: left; display: block;
        }

        .calc-display {
            padding: 16px; text-align: right; background: #fafafa;
            border-bottom: 1px solid var(--border);
            min-height: 80px; display: flex; flex-direction: column; justify-content: flex-end;
        }
        .calc-expr {
            font-size: .85rem; color: var(--text-muted); min-height: 1.2em;
            word-break: break-all; font-family: 'Inter', monospace;
        }
        .calc-result {
            font-size: 1.8rem; font-weight: 800; color: var(--text);
            word-break: break-all; font-family: 'Inter', monospace; line-height: 1.2;
        }

        .calc-buttons {
            display: grid; grid-template-columns: repeat(4, 1fr); gap: 6px;
            padding: 12px;
        }
        .calc-btn {
            padding: 14px 0; border: none; border-radius: 8px;
            font-size: 1.05rem; font-weight: 600; cursor: pointer;
            background: #f1f5f9; color: var(--text);
            transition: background .1s, transform .1s;
            font-family: 'Inter', sans-serif;
        }
        .calc-btn:hover { background: #e2e8f0; }
        .calc-btn:active { transform: scale(.95); }
        .calc-btn-fn { background: #e2e8f0; color: #475569; }
        .calc-btn-fn:hover { background: #cbd5e1; }
        .calc-btn-op { background: #dbeafe; color: #1d4ed8; }
        .calc-btn-op:hover { background: #bfdbfe; }
        .calc-btn-eq { background: var(--primary); color: #fff; }
        .calc-btn-eq:hover { background: var(--primary-light); }
        .calc-btn-copy { background: #f0fdf4; color: #16a34a; font-size: .8rem; padding: 10px 0; }
        .calc-btn-copy:hover { background: #dcfce7; }

        @media (max-width: 640px) {
            .calc-modal { right: 12px; width: calc(100% - 24px); bottom: 80px; }
            .calc-fab { bottom: 16px; right: 16px; width: 50px; height: 50px; font-size: 1.3rem; }
        }
    </style>

    <script>
        (function() {
            const fab = document.getElementById('calcFab');
            const modal = document.getElementById('calcModal');
            const overlay = document.getElementById('calcOverlay');
            const closeBtn = document.getElementById('calcClose');
            const exprEl = document.getElementById('calcExpr');
            const resultEl = document.getElementById('calcResult');
            const historyEl = document.getElementById('calcHistory');
            const historyBtn = document.getElementById('calcHistoryBtn');
            const STORAGE_KEY = 'calc_history';
            const MAX_HISTORY = 50;

            let state = { expr: '', result: '0', memory: null, op: null, reset: false, history: [] };

            function loadHistory() {
                try {
                    const d = localStorage.getItem(STORAGE_KEY);
                    state.history = d ? JSON.parse(d) : [];
                } catch(e) { state.history = []; }
            }
            function saveHistory() {
                try { localStorage.setItem(STORAGE_KEY, JSON.stringify(state.history.slice(0, MAX_HISTORY))); } catch(e) {}
            }
            function pushHistory(expr, result) {
                state.history.unshift({ expr: expr.replace(/\*/g, '×').replace(/\//g, '÷'), result: result, ts: Date.now() });
                if (state.history.length > MAX_HISTORY) state.history = state.history.slice(0, MAX_HISTORY);
                saveHistory();
                renderHistory();
            }
            function renderHistory() {
                if (!historyEl) return;
                if (state.history.length === 0) {
                    historyEl.innerHTML = '<div style="padding:8px 0;color:var(--text-muted);font-size:.8rem;">Aucun historique</div>';
                    return;
                }
                let html = '<button class="calc-history-clear" id="clearHistoryBtn"><i class="bi bi-trash3"></i> Effacer l\'historique</button>';
                state.history.forEach(function(h) {
                    html += '<div class="calc-history-item">' +
                        '<div><div class="h-expr">' + h.expr + '</div><div style="font-size:.65rem;color:#94a3b8;">' + new Date(h.ts).toLocaleTimeString('fr-FR') + '</div></div>' +
                        '<div class="h-result">' + h.result + '</div>' +
                    '</div>';
                });
                historyEl.innerHTML = html;
                var clearBtn = document.getElementById('clearHistoryBtn');
                if (clearBtn) clearBtn.addEventListener('click', function() {
                    state.history = [];
                    saveHistory();
                    renderHistory();
                });
            }
            function toggleHistory() {
                historyEl.style.display = historyEl.style.display === 'none' ? 'block' : 'none';
            }

            function updateDisplay() {
                exprEl.textContent = state.expr || '\u00A0';
                resultEl.textContent = state.result;
            }

            function formatNum(n) {
                if (!isFinite(n)) return 'Erreur';
                if (Math.abs(n) >= 1e15) return n.toExponential(2).replace('.', ',');
                var s = n.toFixed(10).replace(/\.?0+$/, '');
                return s.replace('.', ',');
            }

            function evalExpr(expr) {
                try {
                    var s = expr
                        .replace(/\s/g, '')
                        .replace(/×/g, '*')
                        .replace(/÷/g, '/')
                        .replace(/,/g, '.')
                        .replace(/=$/, '')
                        .replace(/[+\-*/.]$/, '');
                    if (!s || s === '-' || /^[+\-*/]+$/.test(s)) return '0';
                    var result = Function('"use strict"; return (' + s + ')')();
                    if (typeof result === 'number' && isFinite(result)) {
                        return result < 1e-10 ? '0' : formatNum(result);
                    }
                    return 'Erreur';
                } catch(e) {
                    return 'Erreur';
                }
            }

            function handleAction(el) {
                var action = el.dataset.action;
                var value = el.dataset.value;

                if (action === 'digit') {
                    if (state.reset) {
                        state.expr = '';
                        state.result = '0';
                        state.reset = false;
                    }
                    state.expr += value;
                    state.result = evalExpr(state.expr);
                    updateDisplay();
                    return;
                }

                if (action === 'decimal') {
                    if (state.reset) {
                        state.expr = '0,';
                        state.result = '0';
                        state.reset = false;
                    } else {
                        var parts = state.expr.split(/[\+\-\*\/×÷]/);
                        var last = parts[parts.length - 1] || '0';
                        if (last.indexOf(',') === -1 && last.indexOf('.') === -1) {
                            state.expr += ',';
                        }
                    }
                    state.result = evalExpr(state.expr);
                    updateDisplay();
                    return;
                }

                if (action === 'add' || action === 'subtract' || action === 'multiply' || action === 'divide') {
                    var opMap = { add: '+', subtract: '-', multiply: '×', divide: '÷' };
                    if (state.reset) {
                        state.expr = state.result.replace('.', ',') + opMap[action];
                        state.reset = false;
                    } else {
                        state.expr += opMap[action];
                    }
                    updateDisplay();
                    return;
                }

                if (action === 'equals') {
                    var expr = state.expr;
                    var result = evalExpr(expr);
                    if (result !== 'Erreur') {
                        pushHistory(expr, result);
                    }
                    state.expr = expr + '=';
                    state.result = result;
                    state.reset = true;
                    updateDisplay();
                    return;
                }

                if (action === 'clear') {
                    state.expr = '';
                    state.result = '0';
                    state.reset = false;
                    updateDisplay();
                    return;
                }

                if (action === 'backspace') {
                    if (state.reset) {
                        state.expr = '';
                        state.result = '0';
                        state.reset = false;
                    } else {
                        state.expr = state.expr.slice(0, -1);
                        state.result = state.expr ? evalExpr(state.expr) : '0';
                    }
                    updateDisplay();
                    return;
                }

                if (action === 'percent') {
                    if (state.reset) {
                        var r = parseFloat(state.result.replace(',', '.').replace(/[^0-9.,\-eE+]/g, '')) || 0;
                        state.expr = (r / 100).toString().replace('.', ',');
                        state.result = state.expr;
                        state.reset = false;
                    } else {
                        var val = parseFloat(state.expr.replace(/,/g, '.')) || 0;
                        state.expr = (val / 100).toString().replace('.', ',');
                        state.result = evalExpr(state.expr);
                    }
                    updateDisplay();
                    return;
                }

                if (action === 'negate') {
                    if (state.reset) {
                        var r = state.result.replace(',', '.');
                        var n = parseFloat(r) || 0;
                        state.expr = (-n).toString().replace('.', ',');
                        state.result = state.expr;
                        state.reset = false;
                    } else if (state.expr && state.expr !== '0') {
                        state.expr = state.expr.startsWith('-') ? state.expr.slice(1) : '-' + state.expr;
                        state.result = evalExpr(state.expr);
                    } else {
                        state.expr = '-';
                        state.result = '0';
                    }
                    updateDisplay();
                    return;
                }

                if (action === 'copy-expr') {
                    var txt = state.expr.replace(/\u00A0/g, '').replace(/=$/, '').replace(/×/g, '*').replace(/÷/g, '/');
                    if (txt) { navigator.clipboard.writeText(txt).catch(function() {}); }
                    return;
                }

                if (action === 'copy-result') {
                    var txt = state.result;
                    if (txt && txt !== '0') { navigator.clipboard.writeText(txt).catch(function() {}); }
                    return;
                }
            }

            function openCalc() {
                modal.classList.add('open');
                overlay.classList.add('open');
                renderHistory();
            }
            function closeCalc() {
                modal.classList.remove('open');
                overlay.classList.remove('open');
                historyEl.style.display = 'none';
            }

            fab.addEventListener('click', function(e) {
                e.stopPropagation();
                if (modal.classList.contains('open')) { closeCalc(); }
                else { openCalc(); }
            });
            closeBtn.addEventListener('click', closeCalc);
            overlay.addEventListener('click', closeCalc);
            historyBtn.addEventListener('click', function(e) { e.stopPropagation(); toggleHistory(); });

            modal.addEventListener('click', function(e) { e.stopPropagation(); });

            document.querySelectorAll('.calc-btn').forEach(function(btn) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    handleAction(this);
                });
            });

            document.addEventListener('keydown', function(e) {
                if (!modal.classList.contains('open')) return;
                var key = e.key;
                if (key >= '0' && key <= '9') {
                    var btn = document.querySelector('.calc-btn[data-action="digit"][data-value="' + key + '"]');
                    if (btn) { btn.click(); e.preventDefault(); }
                }
                if (key === '.') { document.querySelector('[data-action="decimal"]')?.click(); e.preventDefault(); }
                if (key === ',') { document.querySelector('[data-action="decimal"]')?.click(); e.preventDefault(); }
                if (key === '+') { document.querySelector('[data-action="add"]')?.click(); e.preventDefault(); }
                if (key === '-') { document.querySelector('[data-action="subtract"]')?.click(); e.preventDefault(); }
                if (key === '*') { document.querySelector('[data-action="multiply"]')?.click(); e.preventDefault(); }
                if (key === '/') { document.querySelector('[data-action="divide"]')?.click(); e.preventDefault(); }
                if (key === 'Enter' || key === '=') { document.querySelector('[data-action="equals"]')?.click(); e.preventDefault(); }
                if (key === 'Backspace') { document.querySelector('[data-action="backspace"]')?.click(); e.preventDefault(); }
                if (key === 'Escape') { closeCalc(); }
                if (key === 'c') { document.querySelector('[data-action="clear"]')?.click(); e.preventDefault(); }
                if (key === '%') { document.querySelector('[data-action="percent"]')?.click(); e.preventDefault(); }
            });

            loadHistory();
            updateDisplay();
        })();
    </script>
    @endunless

@endauth

@guest
    <!-- ─── Login Background modern clean light grey ─── -->
    <div style="background: #f4f6f8; min-height: 100vh; display:flex; align-items:center; justify-content:center; position:relative;">
        <div style="position:relative; z-index:2; width:100%;">
            @yield('content')
        </div>
    </div>
@endguest

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var toggle = document.getElementById('navToggle');
        if (toggle) {
            toggle.addEventListener('click', function() {
                document.querySelector('.breadcrumb-nav-links').classList.toggle('open');
                var icon = this.querySelector('i');
                icon.classList.toggle('bi-list');
                icon.classList.toggle('bi-x-lg');
            });
        }
        // Fermer le dropdown "Plus" en cliquant à l'extérieur
        document.addEventListener('click', function(e) {
            var dd = document.getElementById('navDropdown');
            if (dd && !dd.contains(e.target)) {
                dd.querySelector('.nav-dropdown-menu')?.classList.remove('show');
                dd.querySelector('.nav-dropdown-btn')?.classList.remove('open');
            }
        });
    });
</script>
<script>
/**
 * OdjaMi — Intercepteur AJAX Global
 * Intercepte tous les formulaires non-GET et les soumet via l'API JSON.
 * Pour désactiver sur un formulaire spécifique : <form data-no-api="true">
 */
(function () {
    'use strict';

    // Supprime les messages d'erreur injectés précédemment
    function clearErrors(form) {
        form.querySelectorAll('.api-error-msg').forEach(el => el.remove());
        form.querySelectorAll('.api-field-error').forEach(el => {
            el.classList.remove('api-field-error');
            el.style.borderColor = '';
        });
    }

    // Modale de confirmation personnalisée
    function showConfirmModal(message, onConfirm, onCancel) {
        const existing = document.querySelector('.custom-confirm-modal');
        if (existing) existing.remove();
        const overlay = document.createElement('div');
        overlay.className = 'custom-confirm-modal';
        overlay.style.cssText = 'position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.4);display:flex;align-items:center;justify-content:center;';
        overlay.innerHTML = `<div style="background:#fff;border-radius:12px;padding:28px 32px;max-width:440px;width:90%;box-shadow:0 16px 48px rgba(0,0,0,0.2);text-align:center;">
            <div style="font-size:2.5rem;margin-bottom:12px;color:var(--warning);"><i class="bi bi-exclamation-triangle-fill"></i></div>
            <div style="font-size:.95rem;color:var(--text);line-height:1.5;margin-bottom:24px;white-space:pre-line;text-align:left;">${message}</div>
            <div style="display:flex;gap:12px;justify-content:center;">
                <button class="btn btn-primary" id="confirmYes" style="min-width:100px;">Oui, continuer</button>
                <button class="btn btn-secondary" id="confirmNo" style="min-width:100px;">Non, annuler</button>
            </div>
        </div>`;
        document.body.appendChild(overlay);
        document.getElementById('confirmYes').addEventListener('click', function() {
            overlay.remove();
            if (onConfirm) onConfirm();
        });
        document.getElementById('confirmNo').addEventListener('click', function() {
            overlay.remove();
            if (onCancel) onCancel();
        });
        overlay.addEventListener('click', function(e) {
            if (e.target === overlay) {
                overlay.remove();
                if (onCancel) onCancel();
            }
        });
    }

    // Affiche une erreur globale (toast ou alerte)
    function showGlobalError(message) {
        // Cherche un conteneur d'alerte existant ou en crée un
        let alert = document.getElementById('api-global-alert');
        if (!alert) {
            alert = document.createElement('div');
            alert.id = 'api-global-alert';
            alert.style.cssText = 'position:fixed;top:80px;right:20px;z-index:9999;max-width:400px;';
            document.body.appendChild(alert);
        }
        alert.innerHTML = `<div class="alert alert-danger alert-dismissible fade show shadow" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>`;
        // Auto-fermeture après 6 secondes
        setTimeout(() => { if (alert.firstChild) alert.firstChild.remove(); }, 6000);
    }

    // Affiche les erreurs de validation champ par champ
    function showValidationErrors(form, errors) {
        Object.entries(errors).forEach(([field, messages]) => {
            // Normalise le nom (ex: produits.0.nom -> produits[0][nom])
            const inputName = field.replace(/\.(\d+)\./g, '[$1][').replace(/\./g, '[') + (field.includes('.') ? ']' : '');
            const input = form.querySelector(`[name="${field}"], [name="${inputName}"]`);
            const errorText = Array.isArray(messages) ? messages[0] : messages;
            if (input) {
                input.style.borderColor = '#dc3545';
                input.classList.add('api-field-error');
                const errEl = document.createElement('div');
                errEl.className = 'api-error-msg text-danger small mt-1';
                errEl.textContent = errorText;
                input.parentNode.insertBefore(errEl, input.nextSibling);
            } else {
                showGlobalError(errorText);
            }
        });
    }

    // Convertit une URL web en URL API
    function toApiUrl(action, method) {
        try {
            const url = new URL(action, window.location.origin);
            let path = url.pathname;
            // Si c'est déjà une route /api/, on la laisse telle quelle
            if (path.startsWith('/api/')) return action;
            // On rajoute /api devant le chemin
            return window.location.origin + '/api' + path + url.search;
        } catch (e) {
            return '/api' + action;
        }
    }

    // Gère la soumission d'un formulaire
    async function handleSubmit(e) {
        const form = e.currentTarget;

        // Ignorer si data-no-api="true"
        if (form.dataset.noApi === 'true') return;

        // Ignorer les formulaires GET (recherche, filtres...)
        const method = (form.dataset.method || form.method || 'GET').toUpperCase();
        if (method === 'GET') return;

        e.preventDefault();

        clearErrors(form);

        // Construire l'URL API
        const apiUrl = toApiUrl(form.action, method);

        // Construire le body (FormData ou JSON)
        const formData = new FormData(form);

        // Le "_method" de Laravel pour PUT/DELETE/PATCH
        const realMethod = formData.get('_method') ? formData.get('_method').toUpperCase() : method;

        // Bouton de soumission — désactivation pendant la requête
        const submitBtn = form.querySelector('[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Chargement...';
        }

        try {
            const response = await fetch(apiUrl, {
                method: ['GET', 'HEAD'].includes(realMethod) ? 'GET' : 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: ['GET', 'HEAD'].includes(realMethod) ? undefined : formData,
            });

            const contentType = response.headers.get('Content-Type') || '';

            if (contentType.includes('application/json')) {
                const data = await response.json();

                if (response.ok && data.success !== false) {
                    // Avertissement crédit — demande confirmation
                    if (data.credit_warning) {
                        showConfirmModal(data.message,
                            function() {
                                formData.append('ignore_credit_warning', '1');
                                fetch(apiUrl, {
                                    method: ['GET', 'HEAD'].includes(realMethod) ? 'GET' : 'POST',
                                    headers: {
                                        'Accept': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                        'X-Requested-With': 'XMLHttpRequest',
                                    },
                                    body: formData,
                                }).then(r => r.json()).then(d => {
                                    if (d.redirect) {
                                        window.location.href = d.redirect;
                                    } else if (d.errors) {
                                        showValidationErrors(form, d.errors);
                                        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalBtnText; }
                                    } else {
                                        showGlobalError(d.message || 'Erreur lors de la validation.');
                                        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalBtnText; }
                                    }
                                }).catch(() => {
                                    showGlobalError('Erreur lors de la validation.');
                                    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalBtnText; }
                                });
                            },
                            function() {
                                if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalBtnText; }
                            }
                        );
                        return;
                    }
                    // Succès — on redirige
                    if (data.redirect) {
                        setTimeout(() => { window.location.href = data.redirect; }, 300);
                    } else {
                        if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalBtnText; }
                    }
                } else if (response.status === 422) {
                    // Erreurs de validation
                    if (data.errors) {
                        showValidationErrors(form, data.errors);
                    } else {
                        showGlobalError(data.message || 'Erreur de validation.');
                    }
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalBtnText; }
                } else {
                    // Autre erreur JSON — si 401/419, session expirée
                    if (response.status === 401 || response.status === 419) {
                        window.location.href = '/login';
                        return;
                    }
                    showGlobalError(data.message || 'Une erreur est survenue.');
                    if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalBtnText; }
                }
            } else {
                // Réponse non-JSON — session expirée ou redirection classique
                if (response.status === 401 || response.status === 419) {
                    window.location.href = '/login';
                    return;
                }
                // Rediriger vers la page finale (après 302 de Laravel)
                window.location.href = response.url;
            }
        } catch (err) {
            console.error('OdjaMi API Error:', err);
            showGlobalError('Impossible de contacter le serveur. Vérifiez votre connexion.');
            if (submitBtn) { submitBtn.disabled = false; submitBtn.innerHTML = originalBtnText; }
        }
    }

    // Attacher l'intercepteur — DÉSACTIVÉ (les formulaires web fonctionnent normalement)
    // Si besoin, activer uniquement sur les formulaires avec data-api="true"
    function attachInterceptors() {
        document.querySelectorAll('form[data-api="true"]:not([data-no-api="true"])').forEach(form => {
            const method = (form.dataset.method || form.method || 'GET').toUpperCase();
            if (method !== 'GET') {
                form.removeEventListener('submit', handleSubmit);
                form.addEventListener('submit', handleSubmit);
            }
        });
    }

    // Initialisation au chargement
    document.addEventListener('DOMContentLoaded', attachInterceptors);

    // Réattacher si du contenu dynamique est ajouté (modals, etc.)
    const observer = new MutationObserver(attachInterceptors);
    observer.observe(document.body, { childList: true, subtree: true });

})();
</script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.table-search-input').forEach(function(input) {
        var wrap = input.closest('.table-search-wrap');
        var tableWrap = wrap ? wrap.parentElement : null;
        var table = tableWrap ? tableWrap.querySelector('table') : null;
        var countEl = wrap ? wrap.querySelector('.table-search-count') : null;
        if (!table) return;
        var tbody = table.querySelector('tbody');
        if (!tbody) return;
        var rows = Array.from(tbody.querySelectorAll('tr'));

        function doSearch() {
            var q = input.value.trim().toLowerCase();
            var visible = 0;
            rows.forEach(function(row) {
                if (row.querySelector('td[colspan]')) { row.style.display = ''; return; }
                var text = row.textContent.toLowerCase();
                var match = !q || text.indexOf(q) !== -1;
                row.style.display = match ? '' : 'none';
                if (match) visible++;
            });
            if (countEl) {
                countEl.textContent = visible + ' sur ' + rows.filter(function(r){ return !r.querySelector('td[colspan]'); }).length;
            }
        }
        input.addEventListener('input', doSearch);
        doSearch();
    });
});
</script>
<script>
// ─── PWA : masquer Télécharger + corriger lien logo ───
(function() {
    var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;
    if (isStandalone) {
        var dl = document.getElementById('downloadLink');
        if (dl) dl.style.display = 'none';
        var brand = document.getElementById('headerBrand');
        if (brand) brand.href = '{{ route("onboarding") }}';
    }
})();
// ─── PWA Service Worker + Install Banner ───
(function() {
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/sw.js').then(function(reg) {
                reg.addEventListener('updatefound', function() {
                    var newWorker = reg.installing;
                    if (newWorker) {
                        newWorker.addEventListener('statechange', function() {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                newWorker.postMessage({ type: 'SKIP_WAITING' });
                            }
                        });
                    }
                });
            }).catch(function() {});
        });
    }

    var deferredPrompt = null;
    var installBanner = null;
    var dismissed = localStorage.getItem('pwa_install_dismissed');

    window.addEventListener('beforeinstallprompt', function(e) {
        e.preventDefault();
        deferredPrompt = e;
        if (!dismissed) showInstallBanner();
    });

    function showInstallBanner() {
        if (installBanner || document.getElementById('pwa-install-banner')) return;
        installBanner = document.createElement('div');
        installBanner.id = 'pwa-install-banner';
        installBanner.style.cssText = 'position:fixed;bottom:0;left:0;right:0;background:#105e49;color:#fff;padding:12px 16px;display:flex;align-items:center;justify-content:center;gap:12px;z-index:10000;font-size:.85rem;box-shadow:0 -4px 20px rgba(0,0,0,.15);flex-wrap:wrap;';
        installBanner.innerHTML = '<span style="flex:1;min-width:200px;">📲 Installer <strong>PILOTRIX</strong> sur votre écran d\'accueil</span>' +
            '<button id="pwa-install-btn" style="background:#fff;color:#105e49;border:none;padding:8px 18px;border-radius:8px;font-weight:700;cursor:pointer;font-size:.85rem;">Installer</button>' +
            '<button id="pwa-dismiss-btn" style="background:transparent;color:rgba(255,255,255,.7);border:1px solid rgba(255,255,255,.3);padding:8px 14px;border-radius:8px;cursor:pointer;font-size:.8rem;">Plus tard</button>';
        document.body.appendChild(installBanner);

        document.getElementById('pwa-install-btn').addEventListener('click', function() {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function() { deferredPrompt = null; });
            installBanner.remove();
            installBanner = null;
        });
        document.getElementById('pwa-dismiss-btn').addEventListener('click', function() {
            localStorage.setItem('pwa_install_dismissed', '1');
            dismissed = '1';
            installBanner.remove();
            installBanner = null;
        });
    }

    window.addEventListener('appinstalled', function() {
        if (installBanner) { installBanner.remove(); installBanner = null; }
        localStorage.removeItem('pwa_install_dismissed');
    });
})();
</script>
@stack('scripts')
</body>
</html>
