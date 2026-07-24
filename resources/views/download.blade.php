<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Télécharger PILOTRIX</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#105e49">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="PILOTRIX">
    <link rel="apple-touch-icon" href="/icons/icon-192x192.png">
    <style>
        :root { --primary: #105e49; --primary-light: #167e65; --secondary: #ea8d22; --bg: #f4f6f8; --text: #1f2937; --muted: #6b7280; --border: #e5e7eb; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: var(--bg); color: var(--text); min-height: 100vh; display: flex; flex-direction: column; }

        .dl-hero {
            background: linear-gradient(135deg, #0d4d3c 0%, #105e49 40%, #167e65 100%);
            padding: 60px 24px 50px; text-align: center; color: #fff;
            position: relative; overflow: hidden;
        }
        .dl-hero::before {
            content: ''; position: absolute; top: -80px; right: -80px;
            width: 260px; height: 260px; border-radius: 50%;
            background: rgba(255,255,255,.04);
        }
        .dl-hero::after {
            content: ''; position: absolute; bottom: -60px; left: -40px;
            width: 200px; height: 200px; border-radius: 50%;
            background: rgba(255,255,255,.03);
        }
        .dl-logo { width: 90px; height: 90px; border-radius: 22px; object-fit: contain; margin: 0 auto 20px; box-shadow: 0 12px 40px rgba(0,0,0,.25); background: #fff; padding: 6px; }
        .dl-hero h1 { font-family: 'Montserrat', sans-serif; font-weight: 900; font-size: 2rem; letter-spacing: -1px; margin-bottom: 8px; position: relative; z-index: 1; }
        .dl-hero p { font-size: .95rem; color: rgba(255,255,255,.7); max-width: 400px; margin: 0 auto; position: relative; z-index: 1; }

        .dl-content { max-width: 680px; width: 100%; margin: 0 auto; padding: 32px 20px 60px; flex: 1; }

        .dl-card {
            background: #fff; border-radius: 16px; padding: 0;
            box-shadow: 0 4px 20px rgba(0,0,0,.04); border: 1px solid var(--border);
            overflow: hidden; margin-bottom: 20px;
            transition: transform .2s;
        }
        .dl-card:hover { transform: translateY(-2px); box-shadow: 0 8px 30px rgba(0,0,0,.08); }

        .dl-card-detected { border: 2px solid var(--primary); }

        .dl-card-header {
            display: flex; align-items: center; gap: 16px; padding: 20px 24px;
            cursor: pointer; user-select: none;
        }
        .dl-card-icon {
            width: 52px; height: 52px; border-radius: 14px; display: flex;
            align-items: center; justify-content: center; flex-shrink: 0;
        }
        .dl-card-icon.android { background: #dcfce7; color: #16a34a; }
        .dl-card-icon.ios { background: #eff6ff; color: #2563eb; }
        .dl-card-icon.desktop { background: #fef3c7; color: #d97706; }
        .dl-card-icon i { font-size: 1.5rem; }
        .dl-card-info { flex: 1; }
        .dl-card-info h3 { font-family: 'Montserrat', sans-serif; font-weight: 800; font-size: 1.05rem; margin-bottom: 2px; }
        .dl-card-info span { font-size: .8rem; color: var(--muted); }
        .dl-card-badge {
            font-size: .7rem; font-weight: 700; padding: 4px 10px;
            border-radius: 20px; white-space: nowrap;
        }
        .dl-card-detected .dl-card-badge { background: var(--primary); color: #fff; }
        .dl-card:not(.dl-card-detected) .dl-card-badge { background: #f1f5f9; color: #64748b; }

        .dl-card-body {
            display: none; padding: 0 24px 24px; border-top: 1px solid var(--border);
        }
        .dl-card.open .dl-card-body { display: block; padding-top: 20px; }

        .dl-btn {
            display: flex; align-items: center; justify-content: center; gap: 10px;
            width: 100%; padding: 14px; border-radius: 12px; font-weight: 800;
            font-size: 1rem; cursor: pointer; border: none; text-decoration: none;
            transition: all .2s; font-family: 'Montserrat', sans-serif;
        }
        .dl-btn-primary { background: var(--primary); color: #fff; }
        .dl-btn-primary:hover { background: var(--primary-light); transform: translateY(-1px); box-shadow: 0 6px 20px rgba(16,94,73,.25); }
        .dl-btn-google { background: #000; color: #fff; }
        .dl-btn-google:hover { background: #222; }
        .dl-btn-apple { background: #000; color: #fff; }
        .dl-btn-apple:hover { background: #222; }

        .dl-steps { margin-top: 16px; }
        .dl-step {
            display: flex; gap: 12px; padding: 10px 0;
            font-size: .85rem; color: var(--muted); line-height: 1.5;
        }
        .dl-step-num {
            width: 24px; height: 24px; border-radius: 50%; background: #f1f5f9;
            color: var(--primary); font-weight: 800; font-size: .75rem;
            display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 1px;
        }
        .dl-step strong { color: var(--text); }

        .dl-features {
            display: grid; grid-template-columns: 1fr 1fr; gap: 12px;
            margin-top: 24px; padding-top: 20px; border-top: 1px solid var(--border);
        }
        .dl-feature {
            display: flex; align-items: center; gap: 8px; font-size: .82rem; color: var(--muted);
        }
        .dl-feature i { color: var(--primary); font-size: 1rem; }

        .dl-footer {
            text-align: center; padding: 20px; font-size: .75rem; color: var(--muted);
        }

        @media (max-width: 480px) {
            .dl-hero { padding: 40px 16px 36px; }
            .dl-hero h1 { font-size: 1.5rem; }
            .dl-content { padding: 20px 16px 40px; }
            .dl-features { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

    <div class="dl-hero">
        <a href="{{ url('/') }}" style="text-decoration:none; display:inline-block; transition:transform .2s;">
            <img src="{{ asset('Pilotix.jpeg') }}" alt="PILOTRIX" class="dl-logo" style="cursor:pointer;">
        </a>
        <h1>Télécharger PILOTRIX</h1>
        <p>Installez l'application sur votre appareil et gérez votre business partout.</p>
    </div>

    <div class="dl-content">

        {{-- ─── Android ─── --}}
        <div class="dl-card" id="card-android">
            <div class="dl-card-header" onclick="toggleCard('android')">
                <div class="dl-card-icon android"><i class="bi bi-android2"></i></div>
                <div class="dl-card-info">
                    <h3>Android</h3>
                    <span>Smartphone & tablette</span>
                </div>
                <span class="dl-card-badge" id="badge-android">Détection...</span>
            </div>
            <div class="dl-card-body">
                <button class="dl-btn dl-btn-primary" onclick="installPWA()" id="btn-android-install" style="display:none;">
                    <i class="bi bi-download"></i> Installer PILOTRIX
                </button>
                <div id="btn-android-manual" style="display:none;">
                    <p style="font-size:.85rem; color:var(--muted); margin-bottom:12px;">L'installation automatique n'est pas disponible. Suivez ces étapes :</p>
                    <div class="dl-steps">
                        <div class="dl-step">
                            <span class="dl-step-num">1</span>
                            <span>Ouvrez ce site dans <strong>Google Chrome</strong></span>
                        </div>
                        <div class="dl-step">
                            <span class="dl-step-num">2</span>
                            <span>Appuyez sur le bouton <strong>⋮</strong> (menu) en haut à droite</span>
                        </div>
                        <div class="dl-step">
                            <span class="dl-step-num">3</span>
                            <span>Sélectionnez <strong>« Ajouter à l'écran d'accueil »</strong></span>
                        </div>
                        <div class="dl-step">
                            <span class="dl-step-num">4</span>
                            <span>Confirmez avec <strong>« Ajouter »</strong></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── iOS (iPhone / iPad) ─── --}}
        <div class="dl-card" id="card-ios">
            <div class="dl-card-header" onclick="toggleCard('ios')">
                <div class="dl-card-icon ios"><i class="bi bi-apple"></i></div>
                <div class="dl-card-info">
                    <h3>iPhone & iPad</h3>
                    <span>iOS 16.4 ou supérieur</span>
                </div>
                <span class="dl-card-badge" id="badge-ios">Détection...</span>
            </div>
            <div class="dl-card-body">
                <button class="dl-btn dl-btn-apple" onclick="installPWA()" id="btn-ios-install" style="display:none;">
                    <i class="bi bi-download"></i> Installer PILOTRIX
                </button>
                <div id="btn-ios-manual" style="display:none;">
                    <p style="font-size:.85rem; color:var(--muted); margin-bottom:12px;">Installation via Safari :</p>
                    <div class="dl-steps">
                        <div class="dl-step">
                            <span class="dl-step-num">1</span>
                            <span>Ouvrez cette page dans <strong>Safari</strong></span>
                        </div>
                        <div class="dl-step">
                            <span class="dl-step-num">2</span>
                            <span>Appuyez sur l'icône <strong>Partager</strong> <i class="bi bi-box-arrow-up" style="font-size:.9rem;"></i> en bas</span>
                        </div>
                        <div class="dl-step">
                            <span class="dl-step-num">3</span>
                            <span>Choisissez <strong>« Ajouter à l'écran d'accueil »</strong></span>
                        </div>
                        <div class="dl-step">
                            <span class="dl-step-num">4</span>
                            <span>Appuyez sur <strong>« Ajouter »</strong> en haut à droite</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Desktop (Windows / Mac / Linux) ─── --}}
        <div class="dl-card" id="card-desktop">
            <div class="dl-card-header" onclick="toggleCard('desktop')">
                <div class="dl-card-icon desktop"><i class="bi bi-laptop"></i></div>
                <div class="dl-card-info">
                    <h3>Ordinateur</h3>
                    <span>Windows, Mac & Linux</span>
                </div>
                <span class="dl-card-badge" id="badge-desktop">Détection...</span>
            </div>
            <div class="dl-card-body">
                <button class="dl-btn dl-btn-google" onclick="installPWA()" id="btn-desktop-install" style="display:none;">
                    <i class="bi bi-download"></i> Installer PILOTRIX
                </button>
                <div id="btn-desktop-manual" style="display:none;">
                    <p style="font-size:.85rem; color:var(--muted); margin-bottom:12px;">Installation via votre navigateur :</p>
                    <div class="dl-steps">
                        <div class="dl-step">
                            <span class="dl-step-num">1</span>
                            <span>Ouvrez ce site dans <strong>Chrome, Edge ou Brave</strong></span>
                        </div>
                        <div class="dl-step">
                            <span class="dl-step-num">2</span>
                            <span>Cliquez sur l'icône <strong>⬇️</strong> ou <strong>+</strong> dans la barre d'adresse</span>
                        </div>
                        <div class="dl-step">
                            <span class="dl-step-num">3</span>
                            <span>Sélectionnez <strong>« Installer PILOTRIX »</strong></span>
                        </div>
                        <div class="dl-step">
                            <span class="dl-step-num">4</span>
                            <span>L'app sera disponible dans votre menu démarrer / launches</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ─── Fonctionnalités ─── --}}
        <div style="background:#fff; border-radius:16px; padding:24px; border:1px solid var(--border); margin-top:8px;">
            <h3 style="font-family:'Montserrat',sans-serif; font-weight:800; font-size:1rem; margin-bottom:16px;">
                <i class="bi bi-stars" style="color:var(--secondary);"></i> Fonctionnalités de l'app
            </h3>
            <div class="dl-features" style="border-top:none; padding-top:0; margin-top:0;">
                <div class="dl-feature"><i class="bi bi-check-circle-fill"></i> Gestion des ventes</div>
                <div class="dl-feature"><i class="bi bi-check-circle-fill"></i> Suivi des stocks</div>
                <div class="dl-feature"><i class="bi bi-check-circle-fill"></i> Livraisons</div>
                <div class="dl-feature"><i class="bi bi-check-circle-fill"></i> Dettes & créances</div>
                <div class="dl-feature"><i class="bi bi-check-circle-fill"></i> Arrivages</div>
                <div class="dl-feature"><i class="bi bi-check-circle-fill"></i> Multi-dépôts</div>
                <div class="dl-feature"><i class="bi bi-check-circle-fill"></i> Rapports & analyses</div>
                <div class="dl-feature"><i class="bi bi-check-circle-fill"></i> Fonctionne hors ligne</div>
            </div>
        </div>

        {{-- Lien onboarding mobile --}}
        <a href="{{ route('onboarding') }}" style="display:flex; align-items:center; justify-content:center; gap:10px; background:#fff; border-radius:16px; padding:18px; border:1px solid var(--border); text-decoration:none; color:var(--primary); font-weight:700; font-size:.9rem; transition: all .2s; box-shadow: 0 2px 10px rgba(0,0,0,.04);">
            <i class="bi bi-phone" style="font-size:1.2rem;"></i>
            Découvrir PILOTRIX en images
            <i class="bi bi-arrow-right" style="font-size:1rem;"></i>
        </a>

    </div>

    <div class="dl-footer">
        <a href="{{ route('onboarding') }}" style="color:var(--muted); text-decoration:none;">PILOTRIX</a> &copy; {{ date('Y') }} &mdash; Application web progressive (PWA)
    </div>

    <script>
    // ─── Détection de plateforme ───
    (function() {
        var ua = navigator.userAgent || '';
        var isAndroid = /Android/i.test(ua);
        var isIOS = /iPhone|iPad|iPod/i.test(ua);
        var isMobile = isAndroid || isIOS || /webOS|BlackBerry|IEMobile|Opera Mini/i.test(ua);
        var isDesktop = !isMobile;
        var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone;

        // Android
        var androidCard = document.getElementById('card-android');
        var badgeAndroid = document.getElementById('badge-android');
        var btnAndroidInstall = document.getElementById('btn-android-install');
        var btnAndroidManual = document.getElementById('btn-android-manual');
        // iOS
        var iosCard = document.getElementById('card-ios');
        var badgeIOS = document.getElementById('badge-ios');
        var btnIOSInstall = document.getElementById('btn-ios-install');
        var btnIOSManual = document.getElementById('btn-ios-manual');
        // Desktop
        var desktopCard = document.getElementById('card-desktop');
        var badgeDesktop = document.getElementById('badge-desktop');
        var btnDesktopInstall = document.getElementById('btn-desktop-install');
        var btnDesktopManual = document.getElementById('btn-desktop-manual');

        function setup() {
            // Android
            if (isAndroid) {
                androidCard.classList.add('dl-card-detected');
                badgeAndroid.textContent = '⭐ Votre appareil';
                if (deferredPrompt) {
                    btnAndroidInstall.style.display = 'flex';
                } else {
                    btnAndroidManual.style.display = 'block';
                }
                androidCard.classList.add('open');
            } else {
                badgeAndroid.textContent = 'Autre appareil';
                btnAndroidManual.style.display = 'block';
            }

            // iOS
            if (isIOS) {
                iosCard.classList.add('dl-card-detected');
                badgeIOS.textContent = '⭐ Votre appareil';
                if (deferredPrompt) {
                    btnIOSInstall.style.display = 'flex';
                } else {
                    btnIOSManual.style.display = 'block';
                }
                iosCard.classList.add('open');
            } else {
                badgeIOS.textContent = 'Autre appareil';
                btnIOSManual.style.display = 'block';
            }

            // Desktop
            if (isDesktop) {
                desktopCard.classList.add('dl-card-detected');
                badgeDesktop.textContent = '⭐ Votre appareil';
                if (deferredPrompt) {
                    btnDesktopInstall.style.display = 'flex';
                } else {
                    btnDesktopManual.style.display = 'block';
                }
                desktopCard.classList.add('open');
            } else {
                badgeDesktop.textContent = 'Autre appareil';
                btnDesktopManual.style.display = 'block';
            }
        }

        // PWA install
        var deferredPrompt = null;
        window.addEventListener('beforeinstallprompt', function(e) {
            e.preventDefault();
            deferredPrompt = e;
            setup();
        });

        window.addEventListener('appinstalled', function() {
            deferredPrompt = null;
            setup();
        });

        setup();
    })();

    function installPWA() {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function() { deferredPrompt = null; });
    }

    function toggleCard(platform) {
        var card = document.getElementById('card-' + platform);
        card.classList.toggle('open');
    }
    </script>
</body>
</html>
