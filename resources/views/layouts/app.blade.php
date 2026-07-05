<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'OdjaMi') — {{ Auth::check() ? (Auth::user()->tenant?->nom ?? 'OdjaMi') : 'OdjaMi' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

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
            align-items: center;
            text-decoration: none;
            gap: 10px;
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
        @media (max-width: 640px) {
            .breadcrumb-bar { padding: 10px 12px; margin-bottom: 16px; flex-wrap: wrap; }
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
        @media (max-width: 640px) { .page-grid { gap: 12px; } }
        @media (min-width: 1024px) {
            .page-grid-3 { grid-template-columns: 2fr 1fr; }
        }

        /* ─── Flex row mobile (stack on small screens) ─── */
        @media (max-width: 640px) {
            .flex-row-mobile { flex-direction: column; }
            .flex-row-mobile > div { width: 100% !important; flex: none !important; }
        }

        /* ─── Breadcrumb Navigation Links ─── */
        .breadcrumb-nav-links { margin-left: auto; display: flex; gap: 15px; }

        /* ─── Mobile nav toggle ─── */
        .nav-toggle { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text); padding: 4px; }
        @media (max-width: 768px) {
            .nav-toggle { display: flex; align-items: center; margin-left: auto; }
            .breadcrumb-nav-links { display: none; }
            .breadcrumb-nav-links.open { display: flex; flex-direction: column; position: absolute; top: 100%; left: 0; right: 0; background: #fff; padding: 12px; border: 1px solid var(--border); box-shadow: var(--shadow-card); z-index: 999; gap: 4px; }
            .breadcrumb-nav-links.open .nav-link { width: 100%; }
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
        @media (max-width: 640px) {
            th, td { padding: 8px 10px; font-size: 0.8rem; }
        }

        /* Badges */
        .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-success { background: #dcfce7; color: #166534; }
        .badge-warning { background: #fef3c7; color: #b45309; }
        .badge-danger { background: #fee2e2; color: #991b1b; }
        .badge-gray { background: #f1f5f9; color: #475569; }

        /* Stats Grid */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #fff; border-radius: var(--radius-card); padding: 20px; border: 1px solid var(--border); display: flex; align-items: flex-start; gap: 14px; box-shadow: var(--shadow-sm); }
        @media (max-width: 640px) {
            .stats-grid { grid-template-columns: repeat(2, 1fr); gap: 10px; }
            .stat-card { padding: 14px; gap: 10px; }
            .stat-icon { width: 36px; height: 36px; font-size: 1rem; }
            .stat-val { font-size: 1.2rem; }
            .stat-lbl { font-size: 0.65rem; }
        }
        .stat-icon { width: 44px; height: 44px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; }
        .stat-icon.blue { background: #dbeafe; color: #1d4ed8; }
        .stat-icon.green { background: #dcfce7; color: #15803d; }
        .stat-icon.orange { background: #ffedd5; color: #c2410c; }
        .stat-icon.red { background: #fee2e2; color: #b91c1c; }
        .stat-val { font-size: 1.5rem; font-weight: 700; line-height: 1; margin-bottom: 4px; }
        .stat-lbl { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; text-transform: uppercase; }

        /* ─── Breadcrumb Nav Links ─── */
        .nav-link { color: var(--text-muted); text-decoration: none; font-weight: 600; font-size: 0.9rem; padding: 6px 12px; border-radius: 6px; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; }
        .nav-link:hover { background: rgba(0,0,0,0.03); color: var(--text); }
        .nav-link.active { color: #fff; background: var(--primary); }
        .nav-link i { font-size: 1.1rem; line-height: 1; display: inline-flex; align-items: center; }

        /* ─── Page Grid 2 columns ─── */
        .page-grid-2 { grid-template-columns: 1fr; }
        @media(min-width: 992px) { .page-grid-2 { grid-template-columns: 1fr 1fr; } }

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
    @stack('styles')
</head>
<body>

@auth
    <!-- Top Header -->
    <header class="app-header">
        <a href="{{ url('/') }}" class="header-brand">
            <img src="{{ asset('logo.svg') }}" alt="OdjaMi" style="height: 34px; width: auto;">
            <div class="logo-text">OdjaMi</div>
        </a>

        <div class="header-user">
            <a href="{{ route('profile') }}" style="display:flex; align-items:center; gap:8px; text-decoration:none; color:inherit;">
                <i class="bi bi-person-circle" style="font-size: 1.8rem; color: var(--primary);"></i>
                <span>{{ auth()->user()->name }}</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" style="margin-left:8px;">
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
            @else
                {{-- Liens métier selon les droits du rôle --}}
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
                @if(Auth::user()->peutGererMagasins())
                <a href="{{ route('magasins.index') }}" class="nav-link {{ request()->routeIs('magasins.*') ? 'active' : '' }}">
                    <i class="bi bi-shop"></i> Dépôts
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
                <a href="{{ route('dettes.index') }}" class="nav-link {{ request()->routeIs('dettes.*') ? 'active' : '' }}">
                    <i class="bi bi-credit-card-2-back"></i> Dettes
                </a>
                @endif
                @if(Auth::user()->peutGererTransferts())
                <a href="{{ route('transferts.index') }}" class="nav-link {{ request()->routeIs('transferts.*') ? 'active' : '' }}">
                    <i class="bi bi-arrow-left-right"></i> Transferts
                </a>
                @endif
                @if(Auth::user()->peutGererStock())
                <a href="{{ route('stock.index') }}" class="nav-link {{ request()->routeIs('stock.*') ? 'active' : '' }}">
                    <i class="bi bi-boxes"></i> Stock
                </a>
                @endif
                @if(Auth::user()->peutGererUtilisateurs())
                <a href="{{ route('employes.index') }}" class="nav-link {{ request()->routeIs('employes.*') ? 'active' : '' }}">
                    <i class="bi bi-person-badge"></i> Employés
                </a>
                @endif
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
    });
</script>
@stack('scripts')
</body>
</html>
