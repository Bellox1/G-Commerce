<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>G-STOCK — Partenaires</title>
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
        body { font-family: 'Inter', sans-serif; background: #fff; color: var(--text); }

        /* ─── NAV ─── */
        nav {
            position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center;
            padding: 0 5%; min-height: 68px;
            background: rgba(255,255,255,0.95); backdrop-filter: blur(12px);
            border-bottom: 1px solid rgba(0,0,0,0.06);
            justify-content: space-between;
        }
        .nav-logo { display: flex; align-items: center; text-decoration: none; }
        .nav-logo img { margin-top: -10px; }
        .nav-logo .icon-box { width: 36px; height: 36px; background: var(--primary); border-radius: 9px; display: flex; align-items: center; justify-content: center; }
        .nav-logo .icon-box i { color: #fff; font-size: 1.1rem; }
        .nav-logo .logo-name { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 1.4rem; color: var(--primary); letter-spacing: -1.5px; text-transform: uppercase; margin-left: -2px; }
        .nav-links { display: flex; align-items: center; gap: 24px; margin-left: auto; }
        .nav-links a { color: var(--text); text-decoration: none; font-weight: 600; font-size: .9rem; }
        .nav-links a:hover { color: var(--primary); }
        .btn-nav { background: var(--primary); color: #fff !important; padding: 9px 22px; border-radius: 8px; font-weight: 700; font-size: .9rem; transition: all .2s; }
        .btn-nav:hover { background: var(--primary-light); transform: translateY(-1px); box-shadow: 0 4px 14px rgba(16,94,73,.3); }

        /* ─── HERO ─── */
        .hero-part {
            background: linear-gradient(135deg, #0a3d2d 0%, #105e49 50%, #167e65 100%);
            padding: 80px 5% 70px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .hero-part::before {
            content: '';
            position: absolute; inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .hero-part > * { position: relative; z-index: 1; }
        .hero-part .badge-top { display: inline-flex; align-items: center; gap: 8px; background: rgba(234,141,34,.15); border: 1px solid rgba(234,141,34,.4); color: var(--secondary); padding: 6px 16px; border-radius: 30px; font-weight: 700; font-size: .78rem; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 24px; }
        .hero-part h1 { font-family: 'Montserrat', sans-serif; font-size: 2.8rem; font-weight: 900; color: #fff; line-height: 1.3; letter-spacing: -2px; margin-bottom: 18px; }
        .hero-part h1 span { color: var(--secondary); }
        .hero-part p { font-size: 1.1rem; color: rgba(255,255,255,.75); max-width: 620px; margin: 0 auto; line-height: 1.7; }
        .hero-part .hero-actions { display: flex; gap: 14px; justify-content: center; margin-top: 32px; flex-wrap: wrap; }
        .hero-part .btn-hero-primary { display: inline-flex; align-items: center; gap: 10px; background: #fff; color: var(--primary); padding: 14px 30px; border-radius: 10px; font-weight: 800; font-size: 1rem; text-decoration: none; transition: all .3s; box-shadow: 0 8px 25px rgba(0,0,0,.15); }
        .hero-part .btn-hero-primary:hover { background: var(--secondary); color: #fff; transform: translateY(-3px); }
        .hero-part .btn-hero-sec { display: inline-flex; align-items: center; gap: 10px; border: 2px solid rgba(255,255,255,.3); color: #fff; padding: 14px 28px; border-radius: 10px; font-weight: 700; font-size: 1rem; text-decoration: none; transition: all .3s; }
        .hero-part .btn-hero-sec:hover { border-color: #fff; background: rgba(255,255,255,.1); }

        /* ─── SECTION COMMON ─── */
        .section-part { padding: 80px 5%; }
        .section-part.alt { background: var(--bg); }
        .section-part .section-header { text-align: center; margin-bottom: 50px; }
        .section-part .section-header h2 { font-family: 'Montserrat', sans-serif; font-size: 2rem; font-weight: 900; color: var(--primary); margin-bottom: 12px; letter-spacing: -1px; }
        .section-part .section-header p { color: var(--muted); font-size: 1rem; max-width: 580px; margin: 0 auto; line-height: 1.6; }

        /* ─── HOW IT WORKS ─── */
        .steps-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 28px; max-width: 900px; margin: 0 auto; }
        .step-card { text-align: center; padding: 30px 24px; background: #fff; border-radius: 16px; border: 1px solid #e5e7eb; transition: all .3s; }
        .step-card:hover { transform: translateY(-4px); box-shadow: 0 12px 30px rgba(16,94,73,.08); }
        .step-num { width: 48px; height: 48px; border-radius: 12px; background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; display: flex; align-items: center; justify-content: center; font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 1.2rem; margin: 0 auto 16px; }
        .step-card h3 { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1rem; color: var(--text); margin-bottom: 8px; }
        .step-card p { color: var(--muted); font-size: .88rem; line-height: 1.5; }

        /* ─── PRICING CARDS ─── */
        .pricing-cards { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; max-width: 1100px; margin: 0 auto; }
        .pricing-card {
            background: #fff;
            border-radius: 18px;
            padding: 32px 24px 28px;
            text-align: center;
            border: 2px solid #e5e7eb;
            transition: all .3s;
            position: relative;
            overflow: hidden;
        }
        .pricing-card:hover { transform: translateY(-6px); box-shadow: 0 20px 50px rgba(16,94,73,.12); border-color: var(--primary); }
        .pricing-card.popular { border-color: var(--secondary); background: linear-gradient(180deg, #fffbf5 0%, #fff 40%); }
        .pricing-card.popular:hover { box-shadow: 0 20px 50px rgba(234,141,34,.15); }
        .popular-badge { position: absolute; top: 0; left: 0; right: 0; background: var(--secondary); color: #fff; font-size: .72rem; font-weight: 800; text-transform: uppercase; letter-spacing: 1.5px; padding: 6px 0; }
        .plan-icon { width: 52px; height: 52px; border-radius: 14px; display: flex; align-items: center; justify-content: center; margin: 0 auto 14px; font-size: 1.4rem; color: #fff; background: linear-gradient(135deg, var(--primary), var(--primary-light)); }
        .pricing-card.popular .plan-icon { background: linear-gradient(135deg, var(--secondary), #f9a825); }
        .plan-name { font-family: 'Montserrat', sans-serif; font-size: 1.15rem; font-weight: 800; color: var(--text); margin-bottom: 4px; }
        .plan-price { font-family: 'Montserrat', sans-serif; font-size: 2rem; font-weight: 900; color: var(--primary); margin: 10px 0 2px; letter-spacing: -1.5px; }
        .pricing-card.popular .plan-price { color: var(--secondary); }
        .plan-price span { font-size: .8rem; font-weight: 600; color: var(--muted); letter-spacing: 0; }
        .plan-desc { color: var(--muted); font-size: .82rem; margin-bottom: 18px; line-height: 1.5; }
        .plan-features { list-style: none; padding: 0; margin: 0 0 20px; text-align: left; }
        .plan-features li { padding: 7px 0; font-size: .82rem; color: var(--text); display: flex; align-items: flex-start; gap: 8px; border-bottom: 1px solid #f3f4f6; }
        .plan-features li:last-child { border-bottom: none; }
        .plan-features li i { color: #16a34a; font-size: .85rem; margin-top: 2px; flex-shrink: 0; }

        /* ─── COMMISSION TABLE ─── */
        .commission-wrap { max-width: 800px; margin: 50px auto 0; background: #fff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,.06); border: 1px solid #e5e7eb; }
        .commission-wrap h3 { font-family: 'Montserrat', sans-serif; font-size: 1.1rem; font-weight: 800; color: var(--primary); padding: 22px 28px 0; margin: 0; }
        .commission-wrap > p { padding: 4px 28px 16px; color: var(--muted); font-size: .85rem; margin: 0; }
        .commission-table { width: 100%; border-collapse: collapse; font-size: .88rem; }
        .commission-table thead th { background: var(--primary); color: #fff; font-weight: 700; padding: 12px 16px; text-align: center; font-size: .82rem; letter-spacing: .5px; }
        .commission-table thead th:first-child { text-align: left; padding-left: 28px; }
        .commission-table tbody td { padding: 14px 16px; text-align: center; border-bottom: 1px solid #f3f4f6; font-weight: 500; }
        .commission-table tbody td:first-child { text-align: left; padding-left: 28px; font-weight: 700; color: var(--text); }
        .commission-table tbody tr:last-child td { border-bottom: none; }
        .commission-table tbody tr:hover { background: #f9fafb; }
        .badge-dur { background: #e0f2f1; color: #105e49; padding: 4px 12px; border-radius: 6px; font-weight: 700; font-size: .8rem; }
        .badge-dur.pop { background: #fff3e0; color: #ea8d22; }
        .pct { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1.1rem; color: var(--primary); }

        /* ─── ADVANTAGES ─── */
        .adv-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; max-width: 900px; margin: 0 auto; }
        .adv-card { background: #fff; border-radius: 14px; padding: 28px 22px; border: 1px solid #e5e7eb; text-align: center; transition: all .3s; }
        .adv-card:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(16,94,73,.08); }
        .adv-card i { font-size: 2rem; color: var(--primary); margin-bottom: 12px; display: block; }
        .adv-card h4 { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: .95rem; color: var(--text); margin-bottom: 6px; }
        .adv-card p { color: var(--muted); font-size: .83rem; line-height: 1.5; }

        /* ─── CTA FINAL ─── */
        .cta-part { background: linear-gradient(135deg, #0a3d2d 0%, #105e49 50%, #167e65 100%); padding: 70px 5%; text-align: center; }
        .cta-part h2 { font-family: 'Montserrat', sans-serif; font-size: 2rem; font-weight: 900; color: #fff; margin-bottom: 14px; letter-spacing: -1px; }
        .cta-part p { color: rgba(255,255,255,.7); font-size: 1rem; max-width: 500px; margin: 0 auto 30px; line-height: 1.6; }
        .cta-part .cta-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
        .cta-part .btn-cta-primary { display: inline-flex; align-items: center; gap: 10px; background: var(--secondary); color: #fff; padding: 14px 32px; border-radius: 10px; font-weight: 800; font-size: 1rem; text-decoration: none; transition: all .3s; box-shadow: 0 8px 25px rgba(234,141,34,.3); }
        .cta-part .btn-cta-primary:hover { background: #d67d18; transform: translateY(-3px); }
        .cta-part .btn-cta-sec { display: inline-flex; align-items: center; gap: 10px; border: 2px solid rgba(255,255,255,.3); color: #fff; padding: 14px 28px; border-radius: 10px; font-weight: 700; font-size: 1rem; text-decoration: none; transition: all .3s; }
        .cta-part .btn-cta-sec:hover { border-color: #fff; background: rgba(255,255,255,.1); }

        /* ─── FOOTER ─── */
        footer { background: #fff; border-top: 1px solid #e5e7eb; padding: 28px 5%; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; color: var(--muted); font-size: .85rem; }
        footer a { color: var(--primary); text-decoration: none; font-weight: 600; }
        footer a:hover { text-decoration: underline; }

        /* ─── RESPONSIVE ─── */
        @media (max-width: 900px) {
            .hero-part h1 { font-size: 2rem; }
            .steps-grid, .adv-grid { grid-template-columns: 1fr; max-width: 400px; margin-left: auto; margin-right: auto; }
            .pricing-cards { grid-template-columns: repeat(2, 1fr); max-width: 500px; margin-left: auto; margin-right: auto; }
            .commission-wrap { margin: 50px 10px 0; overflow-x: auto; }
            .commission-table { min-width: 480px; }
            .nav-links { gap: 14px; }
            .nav-links a { font-size: .82rem; }
        }
        @media (max-width: 560px) {
            .pricing-cards { grid-template-columns: 1fr; max-width: 360px; }
        }
        @media (max-width: 500px) {
            .nav-links a:not(.btn-nav) { display: none; }
        }
    </style>
</head>
<body>
    <nav>
        <a href="/" class="nav-logo">
            <img src="{{ asset('logo.svg') }}" alt="G-STOCK" style="height: 40px; width: 40px; object-fit: contain; border-radius: 9px;">
            <span class="logo-name">G-STOCK</span>
        </a>
        <div class="nav-links">
            <a href="/">Accueil</a>
            <a href="/#fonctionnalites">Fonctionnalités</a>
            @if (Auth::check())
                <a href="{{ route('dashboard') }}" class="btn-nav"><i class="bi bi-box-arrow-in-right"></i> Tableau de bord</a>
            @else
                <a href="{{ route('login') }}" class="btn-nav"><i class="bi bi-box-arrow-in-right"></i> Connexion</a>
            @endif
        </div>
    </nav>

    <!-- ═══ HERO ═══ -->
    <section class="hero-part">
        <div class="badge-top"><i class="bi bi-handshake"></i> Programme Partenaires</div>
        <h1>Devenez <span>Partenaire</span><br>G-STOCK</h1>
        <p>Recommandez G-STOCK à vos contacts et gagnez une commission sur chaque client actif. Plus vous parrainez, plus vos revenus augmentent.</p>
        <div class="hero-actions">
            <a href="#forfaits" class="btn-hero-primary"><i class="bi bi-graph-up-arrow"></i> Voir les tarifs</a>
            <a href="#comment" class="btn-hero-sec"><i class="bi bi-play-circle"></i> Comment ça marche</a>
        </div>
    </section>

    <!-- ═══ COMMENT ÇA MARCHE ═══ -->
    <section class="section-part alt" id="comment">
        <div class="section-header">
            <h2>Comment ça marche ?</h2>
            <p>Devenez partenaire en 3 étapes simples et commencez à générer des revenus récurrents.</p>
        </div>
        <div class="steps-grid">
            <div class="step-card">
                <div class="step-num">1</div>
                <h3>Choisissez votre durée</h3>
                <p>Sélectionnez la durée qui vous convient : 1 mois, 3 mois, 6 mois ou 1 an.</p>
            </div>
            <div class="step-card">
                <div class="step-num">2</div>
                <h3>Parrainez des clients</h3>
                <p>Partagez votre lien ou recommandez G-STOCK à votre réseau. Chaque client actif vous rapporte.</p>
            </div>
            <div class="step-card">
                <div class="step-num">3</div>
                <h3>Gagnez des commissions</h3>
                <p>Recevez un pourcentage sur les ventes de vos clients. Plus ils sont nombreux, plus votre taux augmente.</p>
            </div>
        </div>
    </section>

    <!-- ═══ FORFAITS ═══ -->
    <section class="section-part" id="forfaits">
        <div class="section-header">
            <h2>Nos forfaits partenaires</h2>
            <p>Plus la durée est longue, plus votre taux de commission est élevé.</p>
        </div>

        <div class="pricing-cards">
            <!-- 1 MOIS -->
            <div class="pricing-card">
                <div class="plan-icon"><i class="bi bi-calendar3"></i></div>
                <div class="plan-name">1 Mois</div>
                <div class="plan-price">5 000 <span>FCFA</span></div>
                <div class="plan-desc">Idéal pour tester le programme partenaire sans engagement.</div>
                <ul class="plan-features">
                    <li><i class="bi bi-check-circle-fill"></i> Accès au tableau de bord partenaire</li>
                    <li><i class="bi bi-check-circle-fill"></i> Suivi de vos commissions</li>
                    <li><i class="bi bi-check-circle-fill"></i> Commission de 5%</li>
                </ul>
            </div>

            <!-- 3 MOIS -->
            <div class="pricing-card">
                <div class="plan-icon"><i class="bi bi-calendar-week"></i></div>
                <div class="plan-name">3 Mois</div>
                <div class="plan-price">13 500 <span>FCFA</span></div>
                <div class="plan-desc">L'équivalent de 4 500 FCFA/mois. Économisez 10%.</div>
                <ul class="plan-features">
                    <li><i class="bi bi-check-circle-fill"></i> Tout le forfait 1 Mois</li>
                    <li><i class="bi bi-check-circle-fill"></i> Commission de 8%</li>
                    <li><i class="bi bi-check-circle-fill"></i> Support prioritaire</li>
                </ul>
            </div>

            <!-- 6 MOIS -->
            <div class="pricing-card popular">
                <div class="popular-badge">Populaire</div>
                <div class="plan-icon"><i class="bi bi-calendar-month"></i></div>
                <div class="plan-name">6 Mois</div>
                <div class="plan-price">24 000 <span>FCFA</span></div>
                <div class="plan-desc">L'équivalent de 4 000 FCFA/mois. Économisez 20%.</div>
                <ul class="plan-features">
                    <li><i class="bi bi-check-circle-fill"></i> Tout le forfait 3 Mois</li>
                    <li><i class="bi bi-check-circle-fill"></i> Commission de 12%</li>
                    <li><i class="bi bi-check-circle-fill"></i> Gestion de plusieurs clients</li>
                </ul>
            </div>

            <!-- 1 AN -->
            <div class="pricing-card">
                <div class="plan-icon"><i class="bi bi-calendar2-event"></i></div>
                <div class="plan-name">1 An</div>
                <div class="plan-price">42 000 <span>FCFA</span></div>
                <div class="plan-desc">L'équivalent de 3 500 FCFA/mois. Économisez 30%.</div>
                <ul class="plan-features">
                    <li><i class="bi bi-check-circle-fill"></i> Tout le forfait 6 Mois</li>
                    <li><i class="bi bi-check-circle-fill"></i> Commission de 15%</li>
                    <li><i class="bi bi-check-circle-fill"></i> Support dédié & accompagnement</li>
                </ul>
            </div>
        </div>

        <!-- Tableau des commissions -->
        <div class="commission-wrap">
            <h3><i class="bi bi-graph-up-arrow" style="margin-right:8px;"></i>Grille des commissions</h3>
            <p>Votre pourcentage de commission est fixe et dépend de la durée de votre forfait.</p>
            <table class="commission-table">
                <thead>
                    <tr>
                        <th>Durée</th>
                        <th>Prix</th>
                        <th>Prix / mois</th>
                        <th>Commission</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="badge-dur">1 Mois</span></td>
                        <td>5 000 FCFA</td>
                        <td>5 000 FCFA</td>
                        <td><span class="pct">5%</span></td>
                    </tr>
                    <tr>
                        <td><span class="badge-dur">3 Mois</span></td>
                        <td>13 500 FCFA</td>
                        <td>4 500 FCFA</td>
                        <td><span class="pct">8%</span></td>
                    </tr>
                    <tr>
                        <td><span class="badge-dur pop">6 Mois</span></td>
                        <td>24 000 FCFA</td>
                        <td>4 000 FCFA</td>
                        <td><span class="pct">12%</span></td>
                    </tr>
                    <tr>
                        <td><span class="badge-dur">1 An</span></td>
                        <td>42 000 FCFA</td>
                        <td>3 500 FCFA</td>
                        <td><span class="pct">15%</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- ═══ AVANTAGES ═══ -->
    <section class="section-part alt">
        <div class="section-header">
            <h2>Pourquoi devenir partenaire ?</h2>
            <p>Rejoignez un programme conçu pour vous aider à réussir.</p>
        </div>
        <div class="adv-grid">
            <div class="adv-card">
                <i class="bi bi-cash-coin"></i>
                <h4>Revenus récurrents</h4>
                <p>Gagnez chaque mois tant que vos clients restent actifs sur la plateforme.</p>
            </div>
            <div class="adv-card">
                <i class="bi bi-graph-up-arrow"></i>
                <h4>Commissions plus élevées</h4>
                <p>Plus votre durée est longue, plus votre taux de commission est important.</p>
            </div>
            <div class="adv-card">
                <i class="bi bi-speedometer2"></i>
                <h4>Suivi en temps réel</h4>
                <p>Tableau de bord dédié pour suivre vos commissions et vos clients actifs.</p>
            </div>
            <div class="adv-card">
                <i class="bi bi-headset"></i>
                <h4>Support dédié</h4>
                <p>Une équipe à votre disposition pour vous accompagner dans votre croissance.</p>
            </div>
            <div class="adv-card">
                <i class="bi bi-shield-check"></i>
                <h4>Paiements sécurisés</h4>
                <p>Vos commissions sont calculées automatiquement et payées à terme fixe.</p>
            </div>
            <div class="adv-card">
                <i class="bi bi-people-fill"></i>
                <h4>Réseau illimité</h4>
                <p>Parrainez autant de clients que vous le souhaitez, sans limite.</p>
            </div>
        </div>
    </section>

    <!-- ═══ CTA FINAL ═══ -->
    <section class="cta-part">
        <h2>Prêt à gagner avec G-STOCK ?</h2>
        <p>Rejoignez notre réseau de partenaires et transformez votre recommandation en source de revenus.</p>
        <div class="cta-actions">
            <a href="{{ route('login') }}" class="btn-cta-primary"><i class="bi bi-box-arrow-in-right"></i> Commencer maintenant</a>
            <a href="/" class="btn-cta-sec"><i class="bi bi-arrow-left"></i> Retour à l'accueil</a>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <span>&copy; {{ date('Y') }} G-STOCK — Partenaires</span>
        <span><a href="/">Accueil</a> &middot; <a href="/#contact">Contact</a></span>
    </footer>
</body>
</html>
