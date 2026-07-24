<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programme Partenaires — PILOTRIX</title>
    <meta name="description" content="Devenez partenaire PILOTRIX. Programme de partenariat pour revendeurs et intégrateurs.">
    <meta name="keywords" content="partenaire, programme partenariat, revendeur, intégrateur, pilotrix">
    <meta name="robots" content="index, follow">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --primary: #105e49;
            --primary-light: #167e65;
            --secondary: #ea8d22;
            --bg: #f8fafc;
            --text: #1e293b;
            --muted: #64748b;
            --border: #e2e8f0;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); }

        nav {
            position: sticky; top: 0; z-index: 100;
            display: flex; align-items: center;
            padding: 0 5%; min-height: 64px;
            background: #fff; border-bottom: 1px solid var(--border);
            justify-content: space-between;
        }
        .nav-logo { display: flex; align-items: center; text-decoration: none; }
        .nav-logo img { height: 56px; width: 56px; object-fit: contain; border-radius: 12px; }
        .nav-links { display: flex; align-items: center; gap: 24px; margin-left: auto; }
        .nav-links a { color: var(--text); text-decoration: none; font-weight: 600; font-size: 0.9rem; }
        .nav-links a:hover { color: var(--primary); }
        .btn-nav { background: var(--primary); color: #fff !important; padding: 10px 22px; border-radius: 8px; font-weight: 700; }
        .btn-nav:hover { background: var(--primary-light); }

        .hero {
            background: linear-gradient(135deg, #0a3d2d, #105e49, #167e65);
            padding: 100px 5% 80px; text-align: center;
            display: flex; flex-direction: column; align-items: center;
        }
        .hero .badge {
            display: inline-block; background: rgba(234,141,34,.15);
            border: 1px solid rgba(234,141,34,.3); color: var(--secondary);
            padding: 6px 18px; border-radius: 50px; font-size: 0.75rem;
            font-weight: 700; text-transform: uppercase; letter-spacing: 1px;
            margin-bottom: 20px;
        }
        .hero h1 { font-size: 2.8rem; font-weight: 800; color: #fff; line-height: 1.2; margin-bottom: 16px; max-width: 700px; }
        .hero h1 span { color: var(--secondary); }
        .hero p { font-size: 1.05rem; color: rgba(255,255,255,.75); max-width: 550px; line-height: 1.6; margin-bottom: 32px; }
        .hero-btns { display: flex; gap: 12px; justify-content: center; flex-wrap: wrap; }
        .btn-primary { background: var(--primary); color: #fff; padding: 14px 32px; border-radius: 10px; font-weight: 700; font-size: 0.95rem; text-decoration: none; border: none; cursor: pointer; }
        .btn-primary:hover { background: var(--primary-light); color: #fff; }

        .section { padding: 80px 5%; }
        .section.alt { background: #fff; }
        .section-title { text-align: center; margin-bottom: 48px; }
        .section-title .tag {
            display: inline-block; font-size: 0.75rem; font-weight: 700;
            text-transform: uppercase; letter-spacing: 1px; color: var(--primary);
            background: rgba(16,94,73,.08); padding: 6px 16px; border-radius: 50px;
            margin-bottom: 12px;
        }
        .section-title h2 { font-size: 1.8rem; font-weight: 800; margin-bottom: 8px; }
        .section-title p { color: var(--muted); font-size: 0.95rem; }

        .table-wrap {
            max-width: 850px; margin: 0 auto;
            border: 1px solid var(--border); border-radius: 12px;
            overflow: hidden; background: #fff;
        }
        table { width: 100%; border-collapse: collapse; }
        thead { background: #f1f5f9; }
        thead th { padding: 14px 20px; text-align: left; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; color: #475569; border-bottom: 2px solid var(--border); }
        tbody td { padding: 14px 20px; text-align: left; font-size: 0.9rem; border-bottom: 1px solid #f1f5f9; color: var(--text); }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #f8fafc; }

        /* ─── VIDEO ─── */
        .video-section { max-width: 800px; margin: 0 auto 60px; }
        .video-wrapper {
            position: relative; padding-bottom: 56.25%; height: 0;
            border-radius: 16px; overflow: hidden;
            box-shadow: 0 20px 50px rgba(0,0,0,0.12);
        }
        .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; }

        /* ─── FORMULAIRE MULTI-ÉTAPES ─── */
        .form-section { max-width: 750px; margin: 0 auto; }
        .form-card {
            background: #fff; border-radius: 16px; padding: 40px;
            border: 1px solid var(--border);
            box-shadow: 0 10px 30px rgba(0,0,0,0.04);
        }
        .form-card h3 { font-size: 1.3rem; font-weight: 800; margin-bottom: 8px; }
        .form-card .sub-text { color: var(--muted); font-size: 0.92rem; margin-bottom: 32px; line-height: 1.5; }

        /* Stepper */
        .stepper { display: flex; justify-content: space-between; margin-bottom: 36px; position: relative; }
        .stepper::before { content: ''; position: absolute; top: 18px; left: 40px; right: 40px; height: 3px; background: #e5e7eb; z-index: 0; }
        .step-dot {
            display: flex; flex-direction: column; align-items: center; gap: 6px;
            z-index: 1; flex: 1;
        }
        .step-dot .dot {
            width: 36px; height: 36px; border-radius: 50%;
            background: #e5e7eb; color: #9ca3af;
            display: flex; align-items: center; justify-content: center;
            font-weight: 800; font-size: 0.85rem;
            transition: all .3s;
        }
        .step-dot.active .dot { background: var(--primary); color: #fff; box-shadow: 0 0 0 4px rgba(16,94,73,.15); }
        .step-dot.done .dot { background: var(--primary); color: #fff; }
        .step-dot .dot-label {
            font-size: 0.68rem; font-weight: 600; color: var(--muted);
            text-align: center; white-space: nowrap;
        }
        .step-dot.active .dot-label, .step-dot.done .dot-label { color: var(--primary); }

        /* Progress bar */
        .progress-bar { height: 4px; background: #e5e7eb; border-radius: 4px; margin-bottom: 32px; overflow: hidden; }
        .progress-bar .fill { height: 100%; background: var(--primary); border-radius: 4px; transition: width .4s ease; }

        /* Step panels */
        .step-panel { display: none; }
        .step-panel.active { display: block; animation: fadeStep .35s ease; }
        @keyframes fadeStep { from { opacity: 0; transform: translateY(12px); } to { opacity: 1; transform: translateY(0); } }

        .step-title { font-size: 1.1rem; font-weight: 800; margin-bottom: 4px; color: var(--primary); }
        .step-subtitle { font-size: 0.85rem; color: var(--muted); margin-bottom: 24px; }

        /* Questions */
        .q-group { margin-bottom: 18px; }
        .q-group label { display: block; font-size: 0.88rem; font-weight: 700; margin-bottom: 8px; color: var(--text); }
        .q-group label .q-num { display: inline-block; width: 24px; height: 24px; border-radius: 50%; background: rgba(16,94,73,.08); color: var(--primary); font-size: 0.72rem; font-weight: 800; text-align: center; line-height: 24px; margin-right: 6px; }
        .q-group input, .q-group select, .q-group textarea {
            width: 100%; padding: 11px 14px; border: 1px solid var(--border); border-radius: 8px;
            font-size: 0.92rem; font-family: 'Inter', sans-serif; color: var(--text); background: #fff; transition: all .2s;
        }
        .q-group input:focus, .q-group select:focus, .q-group textarea:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(16,94,73,0.1); }
        .q-group textarea { resize: vertical; min-height: 80px; }
        .q-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        @media(max-width: 600px) { .q-row { grid-template-columns: 1fr; } }

        /* Nav buttons */
        .step-nav { display: flex; gap: 12px; margin-top: 28px; }
        .btn-next, .btn-prev {
            flex: 1; padding: 13px; border: none; border-radius: 10px;
            font-weight: 700; font-size: 0.95rem; cursor: pointer;
            font-family: 'Inter', sans-serif; transition: all .2s;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-next { background: var(--primary); color: #fff; }
        .btn-next:hover { background: var(--primary-light); }
        .btn-prev { background: #f1f5f9; color: var(--text); border: 1px solid var(--border); }
        .btn-prev:hover { background: #e2e8f0; }
        .btn-submit {
            flex: 1; padding: 16px; border: none; border-radius: 10px;
            background: var(--secondary); color: #fff;
            font-weight: 800; font-size: 1rem; cursor: pointer;
            font-family: 'Inter', sans-serif; transition: all .2s;
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover { background: #d47d1e; transform: translateY(-1px); box-shadow: 0 8px 24px rgba(234,141,34,0.3); }

        .alert { padding: 14px 18px; border-radius: 10px; margin-bottom: 24px; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; }
        .alert-s { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        .alert-d { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

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

        @media (max-width: 768px) {
            .hero h1 { font-size: 2rem; }
            .section-title h2 { font-size: 1.5rem; }
            .section { padding: 60px 5%; }
            thead th, tbody td { padding: 10px 12px; font-size: 0.8rem; }
            .table-wrap { overflow-x: auto; }
            table { min-width: 600px; }
        }
        @media (max-width: 600px) {
            .nav-links { display: none; }
            .hero-btns { flex-direction: column; align-items: center; }
            .btn-primary { width: 100%; max-width: 280px; text-align: center; }
            .footer-top { flex-direction: column; }
            .footer-bottom { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>

<nav>
    <a href="/" class="nav-logo">
        <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix">
    </a>
    <div class="nav-links">
        <a href="/">Accueil</a>
        <a href="/#offres">Offres</a>
        @if (Auth::check())
            <a href="{{ route('dashboard') }}" class="btn-nav">Tableau de bord</a>
        @else
            <a href="{{ route('prestataire.form') }}" class="btn-nav">Devenir Partenaire</a>
        @endif
    </div>
</nav>

<!-- HERO -->
<section class="hero">
    <h1>Monétisez votre <span>réseau d'affaires</span></h1>
    <p>Gagnez jusqu'à <span style="background:#facc15; color:#000; padding:2px 10px; border-radius:4px; font-weight:800; font-size:1.1em;">110 000 FCFA</span> pour 10 clients avec l'offre Max.</p>
    <div class="hero-btns">
        <a href="#gains" class="btn-primary" style="background:var(--secondary);">Voir vos commissions</a>
        <a href="#video" class="btn-primary" style="background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.3);">Voir la vidéo</a>
    </div>
</section>

<!-- VIDÉO -->
<section class="section alt" id="video">
    <div class="section-title">
        <div class="tag">Découvrez Pilotix</div>
        <h2>Comment ça marche</h2>
        <p>Regardez cette courte vidéo pour comprendre ce que Pilotix offre à vos futurs clients.</p>
    </div>
    <div class="video-section">
        <div class="video-wrapper">
            <iframe src="https://www.youtube.com/embed/dQw4w9WgXcQ" title="Pilotix — Présentation" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        </div>
    </div>
</section>

<!-- FORMULAIRE QUESTIONNAIRE -->
<section class="section" id="candidature">
    <div class="section-title">
        <div class="tag">Candidature</div>
        <h2>Rejoignez le programme</h2>
        <p>Répondez à ces questions pour que nous puissions mieux vous accompagner.</p>
    </div>
    <div class="form-section">
        <div class="form-card">
            @if(session('success'))
                <div class="alert alert-s"><i class="bi bi-check-circle-fill"></i> {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-d"><i class="bi bi-exclamation-triangle-fill"></i> @foreach($errors->all() as $e) {{ $e }} @endforeach</div>
            @endif

            <h3>Formulaire de candidature</h3>
            <p class="sub-text">5 étapes rapides — vos réponses nous aident à vous accompagner.</p>

            <!-- Stepper -->
            <div class="stepper">
                <div class="step-dot active" data-step="1"><div class="dot">1</div><div class="dot-label">Infos</div></div>
                <div class="step-dot" data-step="2"><div class="dot">2</div><div class="dot-label">Réseau</div></div>
                <div class="step-dot" data-step="3"><div class="dot">3</div><div class="dot-label">Expérience</div></div>
                <div class="step-dot" data-step="4"><div class="dot">4</div><div class="dot-label">Terrain</div></div>
                <div class="step-dot" data-step="5"><div class="dot">5</div><div class="dot-label">Motivation</div></div>
            </div>
            <div class="progress-bar"><div class="fill" id="progressFill" style="width:20%"></div></div>

            <form action="{{ route('prestataire.submit') }}" method="POST" id="candidatureForm">
                @csrf

                <!-- ═══ ÉTAPE 1 — Infos personnelles ═══ -->
                <div class="step-panel active" data-panel="1">
                    <div class="step-title"><i class="bi bi-person-fill"></i> Vos informations</div>
                    <div class="step-subtitle">Comment pouvons-nous vous contacter ?</div>

                    <div class="q-row">
                        <div class="q-group">
                            <label><span class="q-num">1</span> Nom *</label>
                            <input type="text" name="nom" placeholder="Votre nom" required>
                        </div>
                        <div class="q-group">
                            <label><span class="q-num">2</span> Prénom *</label>
                            <input type="text" name="prenom" placeholder="Votre prénom" required>
                        </div>
                    </div>
                    <div class="q-row">
                        <div class="q-group">
                            <label><span class="q-num">3</span> Email *</label>
                            <input type="email" name="email" placeholder="votre@email.com" required>
                        </div>
                        <div class="q-group">
                            <label><span class="q-num">4</span> Téléphone *</label>
                            <input type="text" name="telephone" placeholder="+229 XX XX XX XX" required>
                        </div>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">5</span> Ville / Zone d'intervention *</label>
                        <input type="text" name="q5" placeholder="Ex: Cotonou, Porto-Novo" required>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">6</span> Quelle est votre activité principale ? *</label>
                        <input type="text" name="q6" placeholder="Ex: Vendeur, Commerçant, Agent commercial" required>
                    </div>

                    <div class="step-nav">
                        <button type="button" class="btn-next" onclick="goStep(2)">Suivant <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- ═══ ÉTAPE 2 — Réseau ═══ -->
                <div class="step-panel" data-panel="2">
                    <div class="step-title"><i class="bi bi-people-fill"></i> Votre réseau</div>
                    <div class="step-subtitle">Parlez-nous de votre entourage professionnel.</div>

                    <div class="q-group">
                        <label><span class="q-num">7</span> Nombre approximatif de commerces/boutiques dans votre réseau ? *</label>
                        <select name="q7" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="1-5">1 à 5</option>
                            <option value="6-15">6 à 15</option>
                            <option value="16-30">16 à 30</option>
                            <option value="31-50">31 à 50</option>
                            <option value="50+">Plus de 50</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">8</span> Quels secteurs d'activité représentent vos clients ? *</label>
                        <input type="text" name="q8" placeholder="Ex: Alimentation, Mode, Pharmacie, Électronique" required>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">9</span> Quel type de commerçants ciblez-vous en priorité ? *</label>
                        <select name="q9" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Petits commerces">Petits commerces / Boutiques</option>
                            <option value="Grossistes">Grossistes / Grossistes-détaillants</option>
                            <option value="Supermarchés">Supermarchés / Supérettes</option>
                            <option value="Marchés">Commerçants de marché</option>
                            <option value="Tous">Tous types de commerces</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">10</span> Combien de commerçants comptez-vous contacter par mois ? *</label>
                        <select name="q10" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="1-5">1 à 5</option>
                            <option value="6-15">6 à 15</option>
                            <option value="16-30">16 à 30</option>
                            <option value="30+">Plus de 30</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">11</span> Quels canaux utilisez-vous pour vendre / contacter vos clients ? *</label>
                        <input type="text" name="q11" placeholder="Ex: Visites terrain, Appels, WhatsApp, Réseaux sociaux" required>
                    </div>

                    <div class="step-nav">
                        <button type="button" class="btn-prev" onclick="goStep(1)"><i class="bi bi-arrow-left"></i> Retour</button>
                        <button type="button" class="btn-next" onclick="goStep(3)">Suivant <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- ═══ ÉTAPE 3 — Expérience ═══ -->
                <div class="step-panel" data-panel="3">
                    <div class="step-title"><i class="bi bi-lightbulb-fill"></i> Votre expérience</div>
                    <div class="step-subtitle">Avez-vous déjà vendu un logiciel ou un service digital ?</div>

                    <div class="q-group">
                        <label><span class="q-num">12</span> Avez-vous déjà vendu un logiciel ou un service digital ? *</label>
                        <select name="q12" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui">Oui</option>
                            <option value="Non">Non</option>
                            <option value="Un peu">Un peu d'expérience</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">13</span> Quel est votre principal atout pour convaincre un commerçant ? *</label>
                        <textarea name="q13" placeholder="Ex: Je connais bien les commerçants de ma zone, je suis très persuasif..." required></textarea>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">14</span> Les commerçants que vous connaissez gèrent-ils déjà un logiciel ? *</label>
                        <select name="q14" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Non, aucun">Non, aucun</option>
                            <option value="Oui, basique">Oui, un logiciel basique (caisse, cahier)</option>
                            <option value="Oui, avancé">Oui, un logiciel plus avancé</option>
                            <option value="Je ne sais pas">Je ne sais pas</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">15</span> Quel est le principal problème des commerçants que vous côtoyez ? *</label>
                        <textarea name="q15" placeholder="Ex: Perdre le suivi des stocks, les dettes clients, la fraude des employés..." required></textarea>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">16</span> Savez-vous expliquer les avantages d'un logiciel de gestion ? *</label>
                        <select name="q16" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui, facilement">Oui, facilement</option>
                            <option value="Oui, avec un peu de formation">Oui, avec un peu de formation</option>
                            <option value="Non, j'aurai besoin d'aide">Non, j'aurai besoin d'aide</option>
                        </select>
                    </div>

                    <div class="step-nav">
                        <button type="button" class="btn-prev" onclick="goStep(2)"><i class="bi bi-arrow-left"></i> Retour</button>
                        <button type="button" class="btn-next" onclick="goStep(4)">Suivant <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- ═══ ÉTAPE 4 — Terrain ═══ -->
                <div class="step-panel" data-panel="4">
                    <div class="step-title"><i class="bi bi-geo-alt-fill"></i> Engagement terrain</div>
                    <div class="step-subtitle">Êtes-vous prêt à vendre sur le terrain ?</div>

                    <div class="q-group">
                        <label><span class="q-num">17</span> Êtes-vous à l'aise pour faire une démonstration devant un client ? *</label>
                        <select name="q17" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui">Oui, aucun problème</option>
                            <option value="Avec un peu de pratique">Avec un peu de pratique</option>
                            <option value="Non">Non, pas du tout</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">18</span> Pouvez-vous assurer un suivi après la vente (installation, formation) ? *</label>
                        <select name="q18" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui">Oui, je peux</option>
                            <option value="Avec un petit support">Avec un petit support technique</option>
                            <option value="Non">Non, pas possible</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">19</span> Combien de temps par semaine pouvez-vous dédier à la vente Pilotix ? *</label>
                        <select name="q19" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="1-3h">1 à 3 heures</option>
                            <option value="4-8h">4 à 8 heures</option>
                            <option value="8-15h">8 à 15 heures</option>
                            <option value="15h+">Plus de 15 heures (temps plein)</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">20</span> Avez-vous un véhicule pour les visites terrain ? *</label>
                        <select name="q20" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui, moto">Oui, moto</option>
                            <option value="Oui, voiture">Oui, voiture</option>
                            <option value="Non">Non</option>
                            <option value="Transport en commun">Transport en commun</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">21</span> Connaissez-vous des commerçants dans d'autres villes ? *</label>
                        <select name="q21" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui, plusieurs">Oui, plusieurs villes</option>
                            <option value="Oui, une ou deux">Oui, une ou deux autres villes</option>
                            <option value="Non, uniquement ma ville">Non, uniquement ma ville</option>
                        </select>
                    </div>

                    <div class="step-nav">
                        <button type="button" class="btn-prev" onclick="goStep(3)"><i class="bi bi-arrow-left"></i> Retour</button>
                        <button type="button" class="btn-next" onclick="goStep(5)">Suivant <i class="bi bi-arrow-right"></i></button>
                    </div>
                </div>

                <!-- ═══ ÉTAPE 5 — Motivation ═══ -->
                <div class="step-panel" data-panel="5">
                    <div class="step-title"><i class="bi bi-rocket-takeoff-fill"></i> Votre motivation</div>
                    <div class="step-subtitle">Dernière ligne droite — à vous de jouer !</div>

                    <div class="q-group">
                        <label><span class="q-num">22</span> Accepteriez-vous une formation gratuite sur le produit Pilotix ? *</label>
                        <select name="q22" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Oui, avec plaisir">Oui, avec plaisir</option>
                            <option value="Oui, si nécessaire">Oui, si nécessaire</option>
                            <option value="Non">Non merci</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">23</span> Quel objectif de vente vous semblerait réaliste par mois ? *</label>
                        <select name="q23" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="1-2 clients">1 à 2 clients</option>
                            <option value="3-5 clients">3 à 5 clients</option>
                            <option value="6-10 clients">6 à 10 clients</option>
                            <option value="10+ clients">Plus de 10 clients</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">24</span> Souhaitez-vous des revenus récurrents ou ponctuels ? *</label>
                        <select name="q24" required>
                            <option value="">-- Sélectionnez --</option>
                            <option value="Récurrents">Récurrents (commissions mensuelles sur Cloud)</option>
                            <option value="Ponctuels">Ponctuels (commission unique sur Licence Locale)</option>
                            <option value="Les deux">Les deux m'intéressent</option>
                        </select>
                    </div>
                    <div class="q-group">
                        <label><span class="q-num">25</span> Pourquoi Pilotix vous intéresse-t-il ? *</label>
                        <textarea name="q25" placeholder="Partagez votre motivation..." required></textarea>
                    </div>

                    <div class="step-nav">
                        <button type="button" class="btn-prev" onclick="goStep(4)"><i class="bi bi-arrow-left"></i> Retour</button>
                        <button type="submit" class="btn-submit"><i class="bi bi-send-fill"></i> Soumettre ma candidature</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</section>

<!-- GRILLE DES COMMISSIONS -->
<section class="section alt" id="gains">
    <div class="section-title">
        <div class="tag">Vos gains</div>
        <h2>Grille des commissions</h2>
        <p>Des taux qui augmentent avec l'engagement.</p>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Version</th>
                    <th>Durée</th>
                    <th>Prix client</th>
                    <th>Taux</th>
                    <th>Commission</th>
                    <th>Sur 1 an</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Locale</td>
                    <td>Licence à vie</td>
                    <td>79 900 FCFA</td>
                    <td>5%</td>
                    <td>3 995 FCFA</td>
                    <td>3 995 FCFA</td>
                </tr>
                <tr>
                    <td rowspan="4">Cloud Sync</td>
                    <td>1 mois</td>
                    <td>3 500 FCFA</td>
                    <td>7%</td>
                    <td>245 FCFA</td>
                    <td>2 940 FCFA</td>
                </tr>
                <tr>
                    <td>3 mois</td>
                    <td>9 000 FCFA</td>
                    <td>10%</td>
                    <td>900 FCFA</td>
                    <td>3 600 FCFA</td>
                </tr>
                <tr>
                    <td>6 mois</td>
                    <td>16 800 FCFA</td>
                    <td>15%</td>
                    <td>2 520 FCFA</td>
                    <td>5 040 FCFA</td>
                </tr>
                <tr>
                    <td>12 mois</td>
                    <td>30 000 FCFA</td>
                    <td>20%</td>
                    <td>6 000 FCFA</td>
                    <td>6 000 FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>
</section>

<!-- BONUS -->
<section class="section" id="primes">
    <div class="section-title">
        <div class="tag">Bonus</div>
        <h2>Plus vous amenez, plus vous gagnez</h2>
        <p>Des primes cumulées selon le nombre de sociétés que vous référez.</p>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Clients référencés</th>
                    <th>Bonus</th>
                    <th>Prime estimée</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1 client</td>
                    <td>Commissions standard</td>
                    <td>Selon l'offre souscrite</td>
                </tr>
                <tr>
                    <td>3 clients et plus</td>
                    <td>+ Accès prioritaire au support</td>
                    <td>Bonus de fidélité</td>
                </tr>
                <tr>
                    <td>5 clients et plus</td>
                    <td>+ Récompense exclusive</td>
                    <td>À partir de 25 000 FCFA</td>
                </tr>
                <tr>
                    <td>10 clients et plus</td>
                    <td>+ Statut Partenaire Premium</td>
                    <td>À partir de 50 000 FCFA</td>
                </tr>
            </tbody>
        </table>
    </div>
    <p style="text-align:center; font-size:0.82rem; color:#64748b; margin-top:16px; max-width:700px; margin-left:auto; margin-right:auto;">
        Les primes et bonus sont versés <strong>tant que le client paie</strong>. Tant que l'abonnement est actif et à jour, le partenaire continue de toucher ses gains.
    </p>
</section>

<!-- Footer -->
<footer style="background:#fff; border-top:1px solid #e5e7eb; padding:40px 5%;">
    <div class="footer-top">
        <div class="footer-brand">
            <div style="display:flex; align-items:center; gap:8px; margin-bottom:8px;">
                <img src="{{ asset('Pilotix.jpeg') }}" alt="Pilotix" style="height:56px; width:56px; object-fit:contain; border-radius:12px;">
            </div>
            <p>Solution de gestion commerciale multi-Dépôt ou Magasin pour les PME d'Afrique de l'Ouest.</p>
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
        <span>&copy; {{ date('Y') }} Pilotix — Gestion commerciale multi-tenant</span>
        <span><a href="{{ route('conditions') }}" style="color:inherit;">Conditions</a> · <a href="{{ route('confidentialite') }}" style="color:inherit;">Confidentialité</a></span>
    </div>
</footer>

<script>
function goStep(n) {
    const form = document.getElementById('candidatureForm');
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

    document.querySelectorAll('.step-dot').forEach(d => {
        const s = parseInt(d.dataset.step);
        d.classList.remove('active', 'done');
        if (s === n) d.classList.add('active');
        else if (s < n) d.classList.add('done');
    });

    document.getElementById('progressFill').style.width = (n * 20) + '%';

    if (n === 5) {
        document.querySelector('.step-subtitle:last-of-type').textContent = 'Dernière ligne droite — à vous de jouer !';
    }

    current.scrollIntoView({ behavior: 'smooth', block: 'center' });
}
</script>

</body>
</html>
