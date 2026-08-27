@extends('layouts.app')

@section('title', 'Mon offre')
@section('subtitle', 'Détails de votre abonnement PILOTIX')

@section('content')
<div class="container-offre">
    {{-- Bannière d'expiration --}}
    @if(!$data['actif'] && !$data['est_vie'])
        <div class="offre-banner expired">
            <i class="bi bi-x-circle-fill"></i>
            <div>
                <strong>Votre abonnement est expiré.</strong>
                Certaines fonctionnalités sont suspendues. Contactez-nous pour le renouveler et retrouver un accès complet.
            </div>
        </div>
    @elseif($data['jours_restants'] !== null && $data['jours_restants'] <= 7 && $data['jours_restants'] > 0)
        <div class="offre-banner warn">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <div>
                <strong>Attention :</strong> votre abonnement expire dans
                <strong>{{ $data['jours_restants'] }} jour(s)</strong> (le {{ $data['expires_at'] }}).
                Pensez à le renouveler.
            </div>
        </div>
    @endif

    <div class="offre-grid">
        {{-- Carte offre --}}
        <div class="offre-card offre-card-dark">
            <div class="offre-card-head">
                <span class="offre-badge">{{ $data['offre_nom'] }}</span>
                <span class="offre-status {{ $data['actif'] ? 'on' : 'off' }}">
                    {{ $data['actif'] ? 'Actif' : 'Expiré' }}
                </span>
            </div>

            @if($data['est_admin'])
                <div class="offre-price">
                    <div class="offre-price-val">Administrateur</div>
                    <div class="offre-price-sub">Espace de gestion — aucun abonnement client</div>
                </div>
                <div class="offre-meta">
                    <div><i class="bi bi-shield-check"></i> Vous êtes connecté en tant qu'administrateur. La gestion des offres s'applique aux espaces clients.</div>
                </div>
            @else
            <div class="offre-price">
                @if($data['est_vie'])
                    <div class="offre-price-val">À vie</div>
                    <div class="offre-price-sub">Licence dédiée</div>
                @else
                    <div class="offre-price-val">{{ number_format($data['prix'], 0, ' ', ' ') }} FCFA</div>
                    <div class="offre-price-sub">/ an</div>
                @endif
            </div>

            <div class="offre-meta">
                <div><i class="bi bi-calendar-event"></i> Expiration : <strong>{{ $data['expires_at'] ?? '—' }}</strong></div>
                @if($data['jours_restants'] !== null && !$data['est_vie'])
                    <div>
                        <i class="bi bi-hourglass-split"></i>
                        {{ $data['jours_restants'] > 0 ? "Reste $data[jours_restants] jour(s)" : 'A expiré' }}
                    </div>
                @endif
            </div>

            <div class="offre-section-title">Ce qui est inclus</div>
            <ul class="offre-features">
                @foreach($data['features'] as $f)
                    <li><i class="bi bi-check2-circle"></i> {{ $f }}</li>
                @endforeach
            </ul>
            @endif
        </div>

        {{-- Carte contact / renouvellement --}}
        <div class="offre-card contact">
            <div class="offre-section-title">Renouveler mon abonnement</div>
            <p class="offre-contact-intro">
                Pour prolonger ou changer d'offre, contactez notre équipe. Nous vous accompagnons
                pour le paiement (comptant ou en <strong>3 tranches</strong>).
            </p>

            <a class="contact-btn call" href="tel:{{ $data['contact']['telephone'] }}">
                <i class="bi bi-telephone-fill"></i> Appeler
                <span>{{ $data['contact']['telephone'] }}</span>
            </a>
            <a class="contact-btn whatsapp" href="https://wa.me/{{ $data['contact']['whatsapp'] }}" target="_blank">
                <i class="bi bi-whatsapp"></i> WhatsApp
                <span>{{ $data['contact']['telephone'] }}</span>
            </a>
            <a class="contact-btn mail" href="mailto:{{ $data['contact']['email'] }}">
                <i class="bi bi-envelope-fill"></i> Écrire un e-mail
                <span>{{ $data['contact']['email'] }}</span>
            </a>

            <a href="{{ route('faq') }}#abonnement" class="offre-faq-link">
                <i class="bi bi-question-circle"></i> Consulter la FAQ « Abonnement »
            </a>
        </div>
    </div>

    {{-- Bloc incitation montée en offre (visible uniquement pour les offres non-supérieures) --}}
    @if(!$data['est_admin'] && $data['offre_code'] === 'essentiel')
    <div class="offre-upgrade-block">
        <div class="offre-upgrade-inner">
            <div class="offre-upgrade-icon"><i class="bi bi-rocket-takeoff-fill"></i></div>
            <div class="offre-upgrade-body">
                <div class="offre-upgrade-tag">✨ Passez à l'offre supérieure</div>
                <h3 class="offre-upgrade-title">
                    Votre commerce grandit ? PILOTIX grandit avec vous.
                </h3>
                <p class="offre-upgrade-desc">
                    Vous êtes actuellement sur l'offre <strong>{{ $data['offre_nom'] }}</strong>.
                    Si votre activité implique des <strong>importations</strong>, la gestion de 
                    <strong>plusieurs magasins ou dépôts</strong>, ou une <strong>équipe de plusieurs 
                    collaborateurs</strong> — l'offre supérieure est faite pour vous.
                </p>

                <div class="offre-upgrade-reasons">
                    <div class="offre-upgrade-reason">
                        <i class="bi bi-box-seam-fill"></i>
                        <span><strong>Importation & arrivages</strong> — Suivez vos commandes fournisseurs, coûts de revient et marges à l'importation.</span>
                    </div>
                    <div class="offre-upgrade-reason">
                        <i class="bi bi-buildings-fill"></i>
                        <span><strong>Multi-magasins & dépôts</strong> — Gérez plusieurs points de vente ou entrepôts depuis un seul tableau de bord.</span>
                    </div>
                    <div class="offre-upgrade-reason">
                        <i class="bi bi-people-fill"></i>
                        <span><strong>Équipe & multi-postes</strong> — Ajoutez vendeurs, magasiniers, livreurs et superviseurs avec des accès distincts.</span>
                    </div>
                    <div class="offre-upgrade-reason">
                        <i class="bi bi-graph-up-arrow"></i>
                        <span><strong>Statistiques avancées</strong> — Analysez vos performances par magasin, par produit, par période.</span>
                    </div>
                </div>

                <div class="offre-upgrade-note">
                    <i class="bi bi-info-circle-fill"></i>
                    <span>Pas d'importation, pas de multi-magasins, une seule personne qui gère ? <strong>Votre offre actuelle est amplement suffisante</strong>, pas besoin de changer. Renouvelez simplement à l'échéance.</span>
                </div>

                <a href="https://wa.me/{{ $data['contact']['whatsapp'] }}" target="_blank" class="offre-upgrade-cta">
                    <i class="bi bi-whatsapp"></i> Discuter de la montée en offre
                </a>
            </div>
        </div>
    </div>
    @endif
</div>

<style>
    .container-offre { max-width: 980px; margin: 0 auto; padding: 24px 16px; }
    .offre-banner {
        display: flex; gap: 12px; align-items: flex-start;
        border-radius: 14px; padding: 14px 18px; margin-bottom: 20px; font-size: 0.92rem; line-height: 1.5;
    }
    .offre-banner i { font-size: 1.4rem; margin-top: 2px; flex-shrink: 0; }
    .offre-banner.expired { background: #F1F5F9; border: 1px solid #CBD5E1; color: #1E293B; }
    .offre-banner.warn { background: #F1F5F9; border: 1px solid #CBD5E1; color: #1E293B; }
    .offre-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 18px; }
    @media (max-width: 760px) { .offre-grid { grid-template-columns: 1fr; } }
    .offre-card { background: #fff; border: 1px solid var(--border); border-radius: 18px; padding: 24px; }
    /* Carte offre en cours — dark premium */
    .offre-card-dark {
        background: linear-gradient(135deg, #0F172A 0%, #1E3A5F 100%) !important;
        border-color: transparent !important;
        color: #fff;
        position: relative;
        overflow: hidden;
    }
    .offre-card-dark::before {
        content: '';
        position: absolute;
        top: -50px; right: -50px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
        pointer-events: none;
    }
    .offre-card-dark .offre-badge { background: rgba(255,255,255,0.15); color: #fff; }
    .offre-card-dark .offre-status.on { background: rgba(74,222,128,0.2); color: #4ADE80; }
    .offre-card-dark .offre-status.off { background: rgba(239,68,68,0.2); color: #F87171; }
    .offre-card-dark .offre-price-val { color: #fff; }
    .offre-card-dark .offre-price-sub { color: rgba(255,255,255,0.6); }
    .offre-card-dark .offre-meta { color: rgba(255,255,255,0.65); }
    .offre-card-dark .offre-meta i { color: #93C5FD; }
    .offre-card-dark .offre-meta strong { color: #fff; }
    .offre-card-dark .offre-section-title { color: #fff; border-bottom-color: rgba(255,255,255,0.3); }
    .offre-card-dark .offre-features li { color: rgba(255,255,255,0.8); }
    .offre-card-dark .offre-features i { color: #60A5FA; }
    .offre-card-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; }
    .offre-badge { background: #F1F5F9; color: #1E293B; font-weight: 800; padding: 6px 14px; border-radius: 50px; font-size: 0.9rem; }
    .offre-status { font-size: 0.78rem; font-weight: 700; padding: 4px 10px; border-radius: 50px; }
    .offre-status.on { background: #E2E8F0; color: #1E293B; }
    .offre-status.off { background: #CBD5E1; color: #1E293B; }
    .offre-price { margin-bottom: 16px; }
    .offre-price-val { font-size: 2rem; font-weight: 900; color: var(--text); letter-spacing: -1px; }
    .offre-price-sub { font-size: 0.9rem; font-weight: 700; color: var(--muted); }
    .offre-meta { display: flex; flex-direction: column; gap: 6px; font-size: 0.9rem; color: var(--text-muted); margin-bottom: 20px; }
    .offre-meta i { color: #64748B; margin-right: 6px; }
    .offre-section-title { font-size: 1.1rem; font-weight: 700; color: var(--text); margin-bottom: 12px; padding-bottom: 6px; border-bottom: 2px solid #1E293B; display: inline-block; }
    .offre-features { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 10px; }
    .offre-features li { display: flex; align-items: center; gap: 8px; font-size: 0.92rem; color: #475569; }
    .offre-features i { color: #1E293B; font-size: 1.1rem; }
    .offre-card.contact { background: #f8fafc; }
    .offre-contact-intro { font-size: 0.9rem; color: var(--text-muted); line-height: 1.6; margin-bottom: 16px; }
    .contact-btn { display: flex; align-items: center; gap: 10px; padding: 12px 16px; border-radius: 12px; text-decoration: none; font-weight: 700; margin-bottom: 10px; background: #fff; border: 1px solid #CBD5E1; color: var(--text); }
    .contact-btn i { font-size: 1.2rem; }
    .contact-btn span { font-weight: 500; opacity: 0.8; margin-left: auto; font-size: 0.85rem; }
    .contact-btn.call { color: #1E293B; border-color: #CBD5E1; }
    .contact-btn.whatsapp { color: #1E293B; border-color: #CBD5E1; }
    .contact-btn.mail { color: #1E293B; border-color: #CBD5E1; }
    .offre-faq-link { display: inline-flex; align-items: center; gap: 8px; margin-top: 8px; color: #1E293B; font-weight: 700; text-decoration: none; font-size: 0.9rem; }

    /* Bloc montée en offre */
    .offre-upgrade-block {
        margin-top: 24px;
        background: linear-gradient(135deg, #0F172A 0%, #1E3A5F 100%);
        border-radius: 20px;
        padding: 28px 28px;
        color: #fff;
        overflow: hidden;
        position: relative;
    }
    .offre-upgrade-block::before {
        content: '';
        position: absolute;
        top: -40px; right: -40px;
        width: 200px; height: 200px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }
    .offre-upgrade-inner { display: flex; gap: 24px; align-items: flex-start; position: relative; }
    .offre-upgrade-icon {
        font-size: 2.5rem;
        background: rgba(255,255,255,0.1);
        border-radius: 16px;
        width: 60px; height: 60px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .offre-upgrade-body { flex: 1; }
    .offre-upgrade-tag {
        display: inline-block;
        background: rgba(255,255,255,0.15);
        color: #fff;
        font-size: 0.78rem;
        font-weight: 700;
        padding: 4px 12px;
        border-radius: 50px;
        margin-bottom: 10px;
        letter-spacing: 0.5px;
    }
    .offre-upgrade-title { font-size: 1.3rem; font-weight: 800; color: #fff; margin: 0 0 10px; line-height: 1.3; }
    .offre-upgrade-desc { font-size: 0.92rem; color: rgba(255,255,255,0.75); line-height: 1.6; margin-bottom: 18px; }
    .offre-upgrade-desc strong { color: #fff; }
    .offre-upgrade-reasons { display: flex; flex-direction: column; gap: 12px; margin-bottom: 20px; }
    .offre-upgrade-reason { display: flex; gap: 12px; align-items: flex-start; }
    .offre-upgrade-reason i { font-size: 1.1rem; color: #60A5FA; margin-top: 2px; flex-shrink: 0; }
    .offre-upgrade-reason span { font-size: 0.9rem; color: rgba(255,255,255,0.8); line-height: 1.5; }
    .offre-upgrade-reason span strong { color: #fff; }
    .offre-upgrade-note {
        display: flex; gap: 10px; align-items: flex-start;
        background: rgba(255,255,255,0.08);
        border-radius: 12px;
        padding: 12px 16px;
        margin-bottom: 20px;
        font-size: 0.87rem;
        color: rgba(255,255,255,0.7);
        line-height: 1.5;
    }
    .offre-upgrade-note i { color: #93C5FD; font-size: 1rem; margin-top: 2px; flex-shrink: 0; }
    .offre-upgrade-note strong { color: #fff; }
    .offre-upgrade-cta {
        display: inline-flex; align-items: center; gap: 10px;
        background: #25D366;
        color: #fff;
        font-weight: 700;
        font-size: 0.92rem;
        padding: 12px 22px;
        border-radius: 12px;
        text-decoration: none;
        transition: opacity 0.2s;
    }
    .offre-upgrade-cta:hover { opacity: 0.9; color: #fff; }
    .offre-upgrade-cta i { font-size: 1.2rem; }
    @media (max-width: 640px) {
        .offre-upgrade-inner { flex-direction: column; gap: 16px; }
        .offre-upgrade-icon { width: 48px; height: 48px; font-size: 1.8rem; }
        .offre-upgrade-title { font-size: 1.1rem; }
    }
</style>
@endsection
