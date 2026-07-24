<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Devenir Partenaire — PILOTRIX</title>
    <meta name="description" content="Rejoignez le réseau de partenaires PILOTRIX. Proposez la gestion commerciale à vos clients.">
    <meta name="robots" content="index, follow">
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

        /* ─── NAV (copié tel quel de welcome) ─── */
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
        .nav-links-pub a:hover { color: var(--primary); }
        .btn-nav { background: var(--primary); color: #fff !important; padding: 9px 22px; border-radius: 8px; font-weight: 700; font-size: 0.9rem; transition: all .2s; }
        .btn-nav:hover { background: var(--primary-light) !important; transform: translateY(-1px); box-shadow: 0 4px 14px rgba(16,94,73,.3); }

        /* ─── HERO COMPACT ─── */
        .sub-hero {
            background: linear-gradient(135deg, #0a3d2d 0%, #105e49 50%, #167e65 100%);
            padding: 60px 5% 50px; text-align: center; position: relative; overflow: hidden;
        }
        .sub-hero::before {
            content: ''; position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .sub-hero-inner { position: relative; z-index: 2; max-width: 700px; margin: 0 auto; }
        .sub-hero .back-link { display: inline-flex; align-items: center; gap: 6px; color: rgba(255,255,255,.6); font-weight: 600; font-size: 0.85rem; text-decoration: none; margin-bottom: 16px; transition: color .2s; }
        .sub-hero .back-link:hover { color: #fff; }
        .sub-hero h1 { font-family: 'Montserrat', sans-serif; font-size: 2.4rem; font-weight: 900; color: #fff; line-height: 1.3; letter-spacing: -1.5px; margin-bottom: 14px; }
        .sub-hero p { font-size: 1.05rem; color: rgba(255,255,255,0.75); line-height: 1.6; }

        /* ─── CONTENT ─── */
        .sub-content { padding: 60px 5% 80px; background: #fbfcfd; }

        .form-card {
            max-width: 600px; margin: 0 auto;
            background: #fff; border-radius: 16px; padding: 40px;
            border: 1px solid rgba(0,0,0,0.06); box-shadow: 0 10px 30px rgba(0,0,0,0.03);
        }
        .form-card h3 { font-family: 'Montserrat', sans-serif; font-size: 1.15rem; font-weight: 800; margin-bottom: 6px; }
        .form-card .sub-text { color: var(--muted); font-size: 0.92rem; margin-bottom: 28px; line-height: 1.5; }
        .fg { margin-bottom: 14px; }
        .fg label { display: block; font-size: 0.82rem; font-weight: 700; margin-bottom: 5px; color: var(--text); }
        .fg input, .fg textarea {
            width: 100%; padding: 11px 14px; border: 1px solid rgba(0,0,0,0.1); border-radius: 8px;
            font-size: 0.92rem; font-family: 'Inter', sans-serif; color: var(--text); background: #fff; transition: all .2s;
        }
        .fg input:focus, .fg textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(16,94,73,0.1); }
        .fg textarea { resize: vertical; min-height: 90px; }
        .fg-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
        @media(max-width: 500px) { .fg-row { grid-template-columns: 1fr; } }

        .btn-sub {
            width: 100%; padding: 14px; border: none; border-radius: 10px;
            background: var(--primary); color: #fff;
            font-weight: 800; font-size: 1rem; cursor: pointer;
            font-family: 'Inter', sans-serif; transition: all .2s; margin-top: 8px;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-sub:hover { background: var(--primary-light); }

        /* Alerts */
        .alert { padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.88rem; display: flex; align-items: center; gap: 8px; max-width: 600px; margin-left: auto; margin-right: auto; }
        .alert-s { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-d { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* Footer */
        footer { background: #0f172a; padding: 30px 5%; text-align: center; color: #6b7280; font-size: 0.85rem; }

        @media (max-width: 768px) {
            .nav-links-pub { display: none; }
            .sub-hero h1 { font-size: 1.8rem; }
        }
    </style>
</head>
<body>
    <!-- Nav -->
    <nav>
        <a href="/" class="nav-logo">
            <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix Logo">
        </a>
        <div class="nav-links-pub">
            <a href="/">Accueil</a>
            <a href="{{ route('partenaires') }}">Programme</a>
            @if (Auth::check())
                <a href="{{ route('dashboard') }}" class="btn-nav"><i class="bi bi-box-arrow-in-right"></i> Tableau de bord</a>
            @else
                <a href="{{ route('login') }}" class="btn-nav"><i class="bi bi-box-arrow-in-right"></i> Connexion</a>
            @endif
        </div>
    </nav>

    <!-- Hero -->
    <section class="sub-hero">
        <div class="sub-hero-inner">
            <a href="{{ route('partenaires') }}" class="back-link"><i class="bi bi-arrow-left"></i> Retour au programme</a>
            <h1>Devenir Partenaire Pilotix</h1>
            <p>Soumettez votre candidature. Notre équipe étudiera votre demande et créera votre espace partenaire dédié.</p>
        </div>
    </section>

    <!-- Content -->
    <div class="sub-content">
        @if(session('success'))
            <div class="alert alert-s"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-d"><i class="bi bi-exclamation-triangle-fill"></i> @foreach($errors->all() as $e) {{ $e }} @endforeach</div>
        @endif

        <div class="form-card">
            <h3>Informations personnelles</h3>
            <p class="sub-text">Remplissez ce formulaire pour soumettre votre candidature au programme de partenariat commercial Pilotix.</p>

            <form action="{{ route('prestataire.submit') }}" method="POST">
                @csrf
                <div class="fg-row">
                    <div class="fg">
                        <label>Nom *</label>
                        <input type="text" name="nom" placeholder="Votre nom" required value="{{ old('nom') }}">
                    </div>
                    <div class="fg">
                        <label>Prénom *</label>
                        <input type="text" name="prenom" placeholder="Votre prénom" required value="{{ old('prenom') }}">
                    </div>
                </div>
                <div class="fg">
                    <label>Adresse email *</label>
                    <input type="email" name="email" placeholder="votre@email.com" required value="{{ old('email') }}">
                </div>
                <div class="fg">
                    <label>Téléphone *</label>
                    <input type="text" name="telephone" placeholder="+229 XX XX XX XX" required value="{{ old('telephone') }}">
                </div>
                <div class="fg">
                    <label>Nom de votre entreprise (optionnel)</label>
                    <input type="text" name="entreprise" placeholder="Votre société" value="{{ old('entreprise') }}">
                </div>
                <div class="fg">
                    <label>Motivation / Pourquoi souhaitez-vous devenir partenaire ?</label>
                    <textarea name="motivation" placeholder="Parlez-nous de votre expérience, votre réseau, vos motivations...">{{ old('motivation') }}</textarea>
                </div>

                <button type="submit" class="btn-sub"><i class="bi bi-send"></i> Soumettre ma candidature</button>
            </form>
        </div>
    </div>

    <footer>&copy; {{ date('Y') }} Pilotix — Tous droits réservés.</footer>
</body>
</html>
