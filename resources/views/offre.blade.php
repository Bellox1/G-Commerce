@extends('layouts.app')

@section('title', 'Mon offre')
@section('subtitle', 'Détails de votre abonnement Pilotix')

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
        <div class="offre-card">
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
</style>
@endsection
