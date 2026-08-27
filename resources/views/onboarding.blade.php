<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>PILOTIX — Bienvenue</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Space+Grotesk:wght@600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#105e49">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="PILOTIX">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <style>
        :root {
            --primary: #105e49;
            --primary-light: #167e65;
            --primary-dark: #0d4d3c;
            --secondary: #ea8d22;
            --bg: #f4f6f8;
            --text: #1f2937;
            --muted: #6b7280;
            --border: #e5e7eb;
            --white: #ffffff;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        html, body { height: 100%; overflow: hidden; font-family: 'Plus Jakarta Sans', sans-serif; background: var(--primary-dark); }

        /* ─── Splash Container ─── */
        .splash-container {
            height: 100%; width: 100%; display: flex; flex-direction: column;
            justify-content: space-between; position: relative;
        }

        /* Ambient Glows */
        .glow-1 {
            position: absolute; top: -120px; right: -100px; width: 320px; height: 320px;
            border-radius: 50%; background: radial-gradient(circle, rgba(234,141,34,.25) 0%, rgba(234,141,34,0) 70%);
            pointer-events: none;
        }
        .glow-2 {
            position: absolute; bottom: -80px; left: -80px; width: 280px; height: 280px;
            border-radius: 50%; background: radial-gradient(circle, rgba(22,126,101,.3) 0%, rgba(22,126,101,0) 70%);
            pointer-events: none;
        }

        /* ─── Top Header ─── */
        .splash-top {
            padding: 24px 28px; display: flex; justify-content: space-between;
            align-items: center; position: relative; z-index: 10;
        }
        .splash-logo { display: flex; align-items: center; gap: 10px; }
        .splash-logo img { height: 38px; width: 38px; border-radius: 10px; object-fit: contain; }
        .splash-logo span {
            font-family: 'Space Grotesk', sans-serif; font-weight: 900;
            font-size: 1.3rem; color: #fff; letter-spacing: -1px; text-transform: uppercase;
        }
        .badge-offline {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.12);
            padding: 4px 10px; border-radius: 20px; font-size: .7rem;
            color: rgba(255,255,255,.7); font-weight: 600;
        }
        .badge-offline i { color: #4ade80; font-size: .65rem; }

        /* ─── Slides Wrapper ─── */
        .slides-wrapper {
            flex: 1; display: flex; transition: transform .4s cubic-bezier(.25,1,.5,1);
            height: 100%;
        }
        .slide {
            min-width: 100%; width: 100%; padding: 0 28px;
            display: flex; flex-direction: column; justify-content: center;
            box-sizing: border-box; opacity: 0; transition: opacity .4s ease;
        }
        .slide.active { opacity: 1; }

        /* ─── Slide Content ─── */
        .slide-icon-box {
            width: 72px; height: 72px; border-radius: 22px;
            background: linear-gradient(135deg, rgba(255,255,255,.15) 0%, rgba(255,255,255,.05) 100%);
            border: 1px solid rgba(255,255,255,.18);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 24px; backdrop-filter: blur(10px);
        }
        .slide-icon-box i {
            font-size: 1.8rem; color: var(--secondary);
            text-shadow: 0 2px 10px rgba(234,141,34,.4);
        }
        .slide-icon-box.accent {
            background: linear-gradient(135deg, var(--secondary) 0%, #f97316 100%);
            border: none;
        }
        .slide-icon-box.accent i {
            font-size: 1.5rem; color: #fff;
            box-shadow: 0 8px 24px rgba(0,0,0,.2);
        }

        .slide-text { position: relative; z-index: 2; }
        .slide-title {
            font-family: 'Space Grotesk', sans-serif; font-weight: 900;
            font-size: 1.6rem; line-height: 1.2; color: #fff;
            margin-bottom: 10px; letter-spacing: -.5px;
        }
        .slide-title span { color: var(--secondary); }
        .slide-desc {
            font-size: .88rem; line-height: 1.55; color: rgba(255,255,255,.65);
            max-width: 340px; margin-bottom: 8px;
        }

        /* ─── Feature Pills ─── */
        .slide-pills {
            display: flex; flex-wrap: wrap; gap: 6px; margin-top: 10px;
        }
        .pill {
            display: inline-flex; align-items: center; gap: 4px;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.08);
            padding: 5px 10px; border-radius: 20px; font-size: .72rem;
            color: rgba(255,255,255,.7); font-weight: 500;
        }
        .pill i { color: var(--secondary); font-size: .75rem; }

        /* ─── Bottom Section ─── */
        .splash-bottom {
            padding: 28px; position: relative; z-index: 10;
        }

        /* ─── Dots ─── */
        .dots { display: flex; justify-content: center; gap: 8px; margin-bottom: 20px; }
        .dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: rgba(255,255,255,.2); transition: all .3s;
        }
        .dot.active { width: 28px; border-radius: 4px; background: var(--secondary); }

        /* ─── Nav Buttons ─── */
        .splash-nav { display: flex; gap: 10px; }
        .btn-skip {
            flex: 0 0 auto; padding: 14px 20px; border-radius: 14px;
            background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.1);
            color: rgba(255,255,255,.5); font-weight: 700; font-size: .9rem;
            cursor: pointer; font-family: 'Space Grotesk', sans-serif;
            transition: all .2s; display: flex; align-items: center; gap: 6px;
        }
        .btn-skip:hover { background: rgba(255,255,255,.15); color: #fff; }
        .btn-next {
            flex: 1; padding: 14px 24px; border-radius: 14px;
            background: var(--secondary); border: none; color: #fff;
            font-weight: 800; font-size: .95rem; cursor: pointer;
            font-family: 'Space Grotesk', sans-serif; display: flex;
            align-items: center; justify-content: center; gap: 8px;
            transition: all .2s; box-shadow: 0 6px 20px rgba(234,141,34,.3);
        }
        .btn-next:hover { transform: translateY(-1px); box-shadow: 0 8px 28px rgba(234,141,34,.4); }
        .btn-next:active { transform: scale(.98); }

        /* ─── Final Slide ─── */
        .btn-connect {
            flex: 1; padding: 14px 24px; border-radius: 14px;
            background: var(--white); border: none; color: var(--primary);
            font-weight: 800; font-size: .95rem; cursor: pointer;
            font-family: 'Space Grotesk', sans-serif; display: flex;
            align-items: center; justify-content: center; gap: 8px;
            text-decoration: none; transition: all .2s;
            box-shadow: 0 6px 20px rgba(0,0,0,.15);
        }
        .btn-connect:hover { transform: translateY(-1px); }

        /* ─── Floating Elements ─── */
        .float-circle {
            position: absolute; border-radius: 50%;
            background: rgba(255,255,255,.03);
        }
        .float-c1 { width: 300px; height: 300px; top: -80px; right: -60px; }
        .float-c2 { width: 200px; height: 200px; bottom: 100px; left: -60px; }

        /* ─── Responsive ─── */
        @media (min-width: 768px) {
            .splash { max-width: 420px; margin: 0 auto; border-radius: 20px; height: 95dvh; margin-top: 2.5dvh; }
        }
        @media (max-height: 680px) {
            .slide-visual { min-height: 140px; margin-bottom: 12px; }
            .slide-img { width: 160px; height: 160px; }
            .slide-title { font-size: 1.3rem; }
            .slide-desc { font-size: .82rem; }
        }
    </style>
</head>
<body>
    <div class="splash">
        <div class="float-circle float-c1"></div>
        <div class="float-circle float-c2"></div>

        {{-- Top Bar --}}
        <div class="splash-top">
            <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX" class="splash-logo">
            <a href="{{ route('login') }}" class="splash-login-btn">
                <i class="bi bi-box-arrow-in-right"></i> Se connecter
            </a>
        </div>

        {{-- Slides --}}
        <div class="slides-wrapper" id="slidesWrapper">
            <div class="slides-track" id="slidesTrack">

                {{-- Slide 1: Accueil --}}
                <div class="slide">
                    <div class="slide-visual">
                        <div class="slide-icon-badge" style="background: linear-gradient(135deg, #167e65, #0d4d3c);">
                            <i class="bi bi-rocket-takeoff-fill"></i>
                        </div>
                        <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX" class="slide-img">
                    </div>
                    <div class="slide-text">
                        <div class="slide-title">Bienvenue sur <span>PILOTIX</span></div>
                        <div class="slide-desc">La solution complète pour gérer votre activité commerciale en toute simplicité.</div>
                        <div class="slide-pills">
                            <span class="pill"><i class="bi bi-lightning-fill"></i> Rapide</span>
                            <span class="pill"><i class="bi bi-shield-check"></i> Sécurisé</span>
                            <span class="pill"><i class="bi bi-phone"></i> Mobile</span>
                        </div>
                    </div>
                    <div class="slide-progress">
                        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 20%;"></div></div>
                        <span class="progress-text">1 / 5</span>
                    </div>
                </div>

                {{-- Slide 2: Ventes --}}
                <div class="slide">
                    <div class="slide-visual">
                        <div class="slide-icon-badge" style="background: linear-gradient(135deg, #16a34a, #0d8a3a);">
                            <i class="bi bi-graph-up-arrow"></i>
                        </div>
                        <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX" class="slide-img">
                    </div>
                    <div class="slide-text">
                        <div class="slide-title">Suivi <span>Temps Réel</span> des Ventes</div>
                        <div class="slide-desc">Gérez et suivez toutes vos ventes en temps réel. Chaque transaction est enregistrée instantanément.</div>
                        <div class="slide-pills">
                            <span class="pill"><i class="bi bi-cart-check"></i> Ventes quotidiennes</span>
                            <span class="pill"><i class="bi bi-bar-chart"></i> Statistiques</span>
                            <span class="pill"><i class="bi bi-receipt"></i> Factures</span>
                        </div>
                    </div>
                    <div class="slide-progress">
                        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 40%;"></div></div>
                        <span class="progress-text">2 / 5</span>
                    </div>
                </div>

                {{-- Slide 3: Stocks --}}
                <div class="slide">
                    <div class="slide-visual">
                        <div class="slide-icon-badge" style="background: linear-gradient(135deg, #2563eb, #1d4ed8);">
                            <i class="bi bi-boxes"></i>
                        </div>
                        <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX" class="slide-img">
                    </div>
                    <div class="slide-text">
                        <div class="slide-title">Gestion <span>Multi-Dépôts</span> des Stocks</div>
                        <div class="slide-desc">Suivez vos stocks dépôt par dépôt. Alertes de rupture, transferts entre magasins, tout est centralisé.</div>
                        <div class="slide-pills">
                            <span class="pill"><i class="bi bi-box-seam"></i> Stock temps réel</span>
                            <span class="pill"><i class="bi bi-arrow-left-right"></i> Transferts</span>
                            <span class="pill"><i class="bi bi-exclamation-triangle"></i> Alertes</span>
                        </div>
                    </div>
                    <div class="slide-progress">
                        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 60%;"></div></div>
                        <span class="progress-text">3 / 5</span>
                    </div>
                </div>

                {{-- Slide 4: Livraisons & Arrivages --}}
                <div class="slide">
                    <div class="slide-visual">
                        <div class="slide-icon-badge" style="background: linear-gradient(135deg, var(--secondary), #d97706);">
                            <i class="bi bi-truck"></i>
                        </div>
                        <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX" class="slide-img">
                    </div>
                    <div class="slide-text">
                        <div class="slide-title">Livraisons & <span>Arrivages</span></div>
                        <div class="slide-desc">Planifiez vos livraisons et suivez vos arrivages de marchandises. Notifications en temps réel.</div>
                        <div class="slide-pills">
                            <span class="pill"><i class="bi bi-truck-flatbed"></i> Livraisons</span>
                            <span class="pill"><i class="bi bi-box-arrow-in-down"></i> Arrivages</span>
                            <span class="pill"><i class="bi bi-people"></i> Clients</span>
                        </div>
                    </div>
                    <div class="slide-progress">
                        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 80%;"></div></div>
                        <span class="progress-text">4 / 5</span>
                    </div>
                </div>

                {{-- Slide 5: Dettes & Finance --}}
                <div class="slide">
                    <div class="slide-visual">
                        <div class="slide-icon-badge" style="background: linear-gradient(135deg, #dc2626, #b91c1c);">
                            <i class="bi bi-wallet2"></i>
                        </div>
                        <img src="{{ asset('PILOTIX-logo.png') }}" alt="PILOTIX" class="slide-img">
                    </div>
                    <div class="slide-text">
                        <div class="slide-title">Dettes & <span>Finances</span></div>
                        <div class="slide-desc">Suivez les dettes clients et fournisseurs. Historique complet, paiements partiels, relevés automatisés.</div>
                        <div class="slide-pills">
                            <span class="pill"><i class="bi bi-credit-card"></i> Dettes clients</span>
                            <span class="pill"><i class="bi bi-cash-stack"></i> Paiements</span>
                            <span class="pill"><i class="bi bi-file-earmark-bar-graph"></i> Rapports</span>
                        </div>
                    </div>
                    <div class="slide-progress">
                        <div class="progress-bar-bg"><div class="progress-bar-fill" style="width: 100%;"></div></div>
                        <span class="progress-text">5 / 5</span>
                    </div>
                </div>

            </div>
        </div>

        {{-- Bottom --}}
        <div class="splash-bottom">
            <div class="dots" id="dots">
                <div class="dot active"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
                <div class="dot"></div>
            </div>
            <div class="splash-nav" id="splashNav">
                <button class="btn-skip" onclick="goToSlide(4)">Passer</button>
                <button class="btn-next" id="btnNext" onclick="nextSlide()">
                    Suivant <i class="bi bi-arrow-right"></i>
                </button>
            </div>
        </div>
    </div>

    <script>
    (function() {
        var currentSlide = 0;
        var totalSlides = 5;
        var track = document.getElementById('slidesTrack');
        var dots = document.querySelectorAll('.dot');
        var btnNext = document.getElementById('btnNext');
        var splashNav = document.getElementById('splashNav');
        var wrapper = document.getElementById('slidesWrapper');
        var startX = 0, deltaX = 0, isDragging = false;

        function updateSlide() {
            track.style.transform = 'translateX(-' + (currentSlide * 100) + '%)';
            dots.forEach(function(d, i) { d.classList.toggle('active', i === currentSlide); });

            if (currentSlide === totalSlides - 1) {
                splashNav.innerHTML = '<a href="{{ route('login') }}" class="btn-connect"><i class="bi bi-box-arrow-in-right"></i> Se connecter à PILOTIX</a>';
            } else {
                splashNav.innerHTML = '<button class="btn-skip" onclick="goToSlide(' + (totalSlides - 1) + ')">Passer</button><button class="btn-next" id="btnNext" onclick="nextSlide()">Suivant <i class="bi bi-arrow-right"></i></button>';
            }
        }

        window.nextSlide = function() {
            if (currentSlide < totalSlides - 1) { currentSlide++; updateSlide(); }
        };

        window.goToSlide = function(idx) {
            currentSlide = idx; updateSlide();
        };

        // Touch swipe
        wrapper.addEventListener('touchstart', function(e) { startX = e.touches[0].clientX; isDragging = true; }, { passive: true });
        wrapper.addEventListener('touchmove', function(e) { if (isDragging) deltaX = e.touches[0].clientX - startX; }, { passive: true });
        wrapper.addEventListener('touchend', function() {
            if (!isDragging) return; isDragging = false;
            if (deltaX < -50 && currentSlide < totalSlides - 1) { currentSlide++; updateSlide(); }
            else if (deltaX > 50 && currentSlide > 0) { currentSlide--; updateSlide(); }
            deltaX = 0;
        });

        // Keyboard
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' || e.key === ' ') { e.preventDefault(); nextSlide(); }
            else if (e.key === 'ArrowLeft' && currentSlide > 0) { currentSlide--; updateSlide(); }
        });
    })();
    </script>
</body>
</html>
