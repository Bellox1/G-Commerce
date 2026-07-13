<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Conditions d'utilisation — Pilotix</title>
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
    <h1>Conditions d'utilisation</h1>
    <p class="date">En vigueur depuis le {{ date('d/m/Y') }}</p>
</div>

<div class="page-body">

    <h2>1. Qu'est-ce que Pilotix ?</h2>
    <p>Pilotix est un logiciel de gestion commerciale en ligne. Il permet aux commerçants et PME de gérer leurs ventes, stocks, clients, livraisons et dettes depuis leur téléphone ou ordinateur.</p>
    <p>En utilisant Pilotix, vous acceptez ces conditions. On a tout écrit simplement pour que ce soit clair.</p>

    <h2>2. Votre compte</h2>
    <ul>
        <li>Lors de votre inscription, donnez vos vraies informations (nom, email, téléphone).</li>
        <li>Votre compte vous est personnel — ne le partagez pas avec quelqu'un d'autre.</li>
        <li>Vous êtes responsable de garder votre mot de passe secret.</li>
        <li>Si quelqu'un utilise votre compte sans votre accord, prévenez-nous tout de suite.</li>
    </ul>

    <h2>3. Les offres et paiements</h2>
    <p>Pilotix propose deux types d'offres :</p>
    <ul>
        <li><strong>Locale :</strong> vous payez une seule fois (79 900 FCFA) et c'est pour la vie. Vous l'utilisez sur un seul ordinateur, sans connexion internet.</li>
        <li><strong>Cloud Sync :</strong> un abonnement mensuel (à partir de 3 500 FCFA). Vous pouvez y accéder depuis n'importe quel appareil, avec synchronisation des données.</li>
    </ul>
    <p>Les paiements se font via les moyens proposés sur la plateforme. Si on change un tarif, on vous prévient au moins 30 jours avant.</p>

    <h2>4. Ce qu'on vous demande</h2>
    <p>En utilisant Pilotix, vous vous engagez à :</p>
    <ul>
        <li>Utiliser le logiciel de manière honnête et légale.</li>
        <li>Ne pas essayer de pirater ou contourner le système.</li>
        <li>Ne pas copier, revendre ou partager le logiciel.</li>
        <li>Respecter les autres utilisateurs et leurs données.</li>
    </ul>

    <h2>5. Vos données</h2>
    <p>Vos données (ventes, stocks, clients) vous appartiennent. On ne les utilise pas pour autre chose, on ne les vend pas, et on ne les partage pas avec des tiers. Pour plus de détails, consultez notre <a href="{{ route('confidentialite') }}" style="color:var(--primary); font-weight:600;">Politique de confidentialité</a>.</p>

    <h2>6. Disponibilité du service</h2>
    <ul>
        <li>On fait tout pour que Pilotix soit disponible en permanois, mais il peut y avoir des coupures ponctuelles pour maintenance.</li>
        <li>On vous prévient au moins 48h à l'avance quand c'est planifié.</li>
        <li>On ne peut pas être tenus responsables si vous perdez des données à cause d'une mauvaise manipulation ou d'une panne de votre appareil.</li>
    </ul>

    <h2>7. Propriété</h2>
    <p>Tout ce qui compose Pilotix (design, code, logos) nous appartient. Vous ne pouvez pas le copier ni le revendre.</p>

    <h2>8. Résiliation</h2>
    <ul>
        <li>Vous pouvez arrêter d'utiliser Pilotix quand vous voulez.</li>
        <li>Si vous avez un abonnement Cloud, vous pouvez le résilier depuis votre compte.</li>
        <li>On garde vos données 90 jours après la résiliation, puis on les supprime définitivement.</li>
    </ul>

    <h2>9. Ce qu'on peut changer</h2>
    <p>On se réserve le droit de modifier ces conditions. Si on le fait, on vous envoie un email ou une notification dans l'application au moins 15 jours avant.</p>

    <h2>10. Loi applicable</h2>
    <p>Ces conditions sont régies par les lois de la République du Bénin. En cas de litige, les tribunaux de Cotonou sont compétents.</p>

    <h2>Besoin d'aide ?</h2>
    <p>Pour toute question, écrivez-nous :</p>
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
