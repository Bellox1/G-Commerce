<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politique de confidentialité — Pilotix</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --primary: #105e49; --text: #1e293b; --muted: #64748b; --border: #e2e8f0; --bg: #f8fafc; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); line-height: 1.8; }
        nav { position: sticky; top: 0; z-index: 100; display: flex; align-items: center; padding: 0 5%; min-height: 64px; background: #fff; border-bottom: 1px solid var(--border); justify-content: space-between; }
        .nav-logo { display: flex; align-items: center; text-decoration: none; }
        .nav-logo img { height: 56px; width: 56px; object-fit: contain; border-radius: 12px; }
        .nav-links { display: flex; align-items: center; gap: 24px; margin-left: auto; }
        .nav-links a { color: var(--text); text-decoration: none; font-weight: 600; font-size: 0.9rem; }
        .nav-links a:hover { color: var(--primary); }
        .page-header { padding: 60px 5% 40px; max-width: 780px; margin: 0 auto; }
        .page-header .tag { display: inline-block; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: var(--primary); background: rgba(16,94,73,.08); padding: 5px 14px; border-radius: 50px; margin-bottom: 12px; }
        .page-header h1 { font-size: 1.9rem; font-weight: 800; margin-bottom: 8px; }
        .page-header .date { color: var(--muted); font-size: 0.85rem; }
        .page-body { max-width: 780px; margin: 0 auto; padding: 0 5% 80px; }
        .page-body h2 { font-size: 1.1rem; font-weight: 800; color: var(--primary); margin: 30px 0 10px; }
        .page-body p { margin-bottom: 14px; font-size: 0.92rem; color: #334155; }
        .page-body ul { margin: 0 0 14px 20px; }
        .page-body li { margin-bottom: 8px; font-size: 0.92rem; color: #334155; }
        .page-body strong { color: var(--text); }
        footer { background: #fff; border-top: 1px solid #e5e7eb; padding: 40px 5%; }
        .footer-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 40px; flex-wrap: wrap; margin-bottom: 40px; }
        .footer-brand { flex: 1; min-width: 200px; }
        .footer-brand .logo-name { font-weight: 800; font-size: 1.3rem; letter-spacing: -0.5px; text-transform: uppercase; color: var(--primary); }
        .footer-brand p { margin-top: 10px; color: #6b7280; font-size: 0.85rem; line-height: 1.6; max-width: 260px; }
        .footer-col { min-width: 130px; }
        .footer-col h5 { font-weight: 700; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #374151; margin-bottom: 12px; }
        .footer-col ul { list-style: none; display: flex; flex-direction: column; gap: 8px; }
        .footer-col ul li a { color: #374151; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 6px; }
        .footer-col ul li a:hover { color: var(--primary); }
        .footer-bottom { border-top: 1px solid #e5e7eb; padding-top: 20px; display: flex; justify-content: space-between; color: #6b7280; font-size: 0.8rem; flex-wrap: wrap; gap: 8px; }
        @media (max-width: 600px) { .nav-links { display: none; } .page-header h1 { font-size: 1.5rem; } .footer-top { flex-direction: column; } .footer-bottom { flex-direction: column; text-align: center; } }
    </style>
</head>
<body>

<nav>
    <a href="/" class="nav-logo">
        <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix">
    </a>
    <div class="nav-links">
        <a href="/">Accueil</a>
        <a href="{{ route('partenaires') }}">Partenaires</a>
    </div>
</nav>

<div class="page-header">
    <h1>Politique de confidentialité</h1>
    <p class="date">En vigueur depuis le {{ date('d/m/Y') }}</p>
</div>

<div class="page-body">

    <h2>1. Qui sommes-nous ?</h2>
    <p>Pilotix est un logiciel de gestion commerciale édité au Bénin. On développe cette solution pour aider les PME et commerçants d'Afrique de l'Ouest à mieux gérer leur activité.</p>
    <p>Si vous avez des questions sur vos données, contactez-nous directement :</p>
    <ul>
        <li>Email : <a href="mailto:pilotrix@gmail.com" style="color:var(--primary); font-weight:600;">pilotrix@gmail.com</a></li>
        <li>Téléphone : <a href="tel:+2290146862536" style="color:var(--primary); font-weight:600;">+229 01 46 86 25 36</a></li>
    </ul>

    <h2>2. Quelles données collecte-t-on ?</h2>
    <p>On collecte uniquement ce qui est nécessaire pour faire tourner Pilotix :</p>
    <ul>
        <li><strong>Vos informations personnelles :</strong> nom, prénom, email, téléphone — fournies lors de l'inscription.</li>
        <li><strong>Vos données commerciales :</strong> ventes, stocks, clients, livraisons — que vous saisissez vous-même dans l'application.</li>
    </ul>
    <p><strong>On ne collecte pas</strong> de données bancaires, de numéro CNI ou passeport, ni d'informations sur votre appareil ou votre navigation.</p>

    <h2>3. À quoi servent ces données ?</h2>
    <p>On utilise vos données pour :</p>
    <ul>
        <li>Vous fournir le service (gérer votre compte, vos ventes, vos stocks).</li>
        <li>Améliorer Pilotix en comprenant comment vous l'utilisez.</li>
        <li>Vous prévenir des mises à jour importantes.</li>
        <li>Assurer la sécurité de votre compte.</li>
    </ul>

    <h2>4. On partage vos données ?</h2>
    <p><strong>Non.</strong> On ne vend, ne loue et ne partage pas vos données avec des tiers.</p>

    <h2>5. Comment on protège vos données ?</h2>
    <ul>
        <li>Connexion sécurisée (chiffrement SSL).</li>
        <li>Mots de passe stockés de manière sécurisée (on ne les connaît pas).</li>
        <li>Accès limité aux données — seules les personnes autorisées peuvent y accéder.</li>
        <li>Sauvegardes régulières.</li>
    </ul>

    <h2>6. Combien de temps on garde vos données ?</h2>
    <ul>
        <li><strong>Tant que votre compte est actif :</strong> on garde tout.</li>
        <li><strong>Données de facturation :</strong> on les garde 10 ans (obligation légale).</li>
    </ul>

    <h2>7. Vos droits</h2>
    <p>Vous avez le droit de :</p>
    <ul>
        <li><strong>Savoir</strong> quelles données on a sur vous.</li>
        <li><strong>Corriger</strong> une information incorrecte.</li>
        <li><strong>Demander la suppression</strong> de vos données.</li>
        <li><strong>Récupérer vos données</strong> dans un format simple.</li>
    </ul>
    <p>Pour exercer ces droits, envoyez un email à <a href="mailto:pilotrix@gmail.com" style="color:var(--primary); font-weight:600;">pilotrix@gmail.com</a>.</p>

    <h2>8. Cookies</h2>
    <p>Pilotix utilise uniquement des cookies techniques (pour garder votre session ouverte). Pas de cookies publicitaires ni de suivi.</p>

    <h2>9. Où sont stockées vos données ?</h2>
    <p>Vos données sont hébergées en Afrique de l'Ouest. On ne les envoie pas à l'étranger.</p>

    <h2>10. Modifications</h2>
    <p>Si on change quelque chose dans cette politique, on vous envoie un email ou une notification au moins 15 jours avant.</p>

    <h2>Besoin d'aide ?</h2>
    <p>Pour toute question sur vos données ou cette politique :</p>
    <ul>
        <li>Email : <a href="mailto:pilotrix@gmail.com" style="color:var(--primary); font-weight:600;">pilotrix@gmail.com</a></li>
        <li>Téléphone : <a href="tel:+2290146862536" style="color:var(--primary); font-weight:600;">+229 01 46 86 25 36</a></li>
    </ul>

</div>

<footer>
    <div class="footer-top">
        <div class="footer-brand">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix" style="height:56px; width:56px; object-fit:contain; border-radius:12px;">
            </div>
            <p>Solution de gestion commerciale multi-Dépôt ou Magasin pour les PME d'Afrique de l'Ouest.</p>
        </div>
        <div class="footer-col">
            <h5>Fonctionnalités</h5>
            <ul>
                <li><a href="/#fonctionnalites"><i class="bi bi-receipt"></i> Ventes & Facturation</a></li>
                <li><a href="/#fonctionnalites"><i class="bi bi-box-seam"></i> Stock & Arrivages</a></li>
                <li><a href="/#fonctionnalites"><i class="bi bi-truck"></i> Livraisons</a></li>
                <li><a href="/#fonctionnalites"><i class="bi bi-cash-stack"></i> Dettes Clients</a></li>
            </ul>
        </div>
        <div class="footer-col">
            <h5>Contact</h5>
            <ul>
                <li><a href="tel:+2290146862536"><i class="bi bi-telephone"></i> +229 01 46 86 25 36</a></li>
                <li><a href="mailto:pilotrix@gmail.com"><i class="bi bi-envelope"></i> pilotrix@gmail.com</a></li>
            </ul>
        </div>
    </div>
    <div class="footer-bottom">
        <span>&copy; {{ date('Y') }} Pilotix — Gestion commerciale multi-Dépôt ou Magasin</span>
        <span><a href="{{ route('conditions') }}" style="color:inherit;">Conditions</a> · <a href="{{ route('confidentialite') }}" style="color:inherit;">Confidentialité</a> · <a href="{{ route('partenaires') }}" style="color:inherit;">Partenariat</a></span>
    </div>
</footer>

</body>
</html>
