@extends('layouts.app')

@section('title', 'Mon Business')
@section('subtitle', 'Pilotez votre activité de partenaire et suivez vos commissions')

@section('actions')
    <a href="{{ route('prestataire.tenants.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill"></i> Créer une Société
    </a>
@endsection

@section('content')
<!-- Stats Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 30px;">
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="bi bi-building"></i>
        </div>
        <div>
            <div class="stat-val">{{ $totalSocietes }}</div>
            <div class="stat-lbl">Sociétés Créées</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="bi bi-wallet2"></i>
        </div>
        <div>
            <div class="stat-val">{{ number_format($montantTotal, 0, ',', ' ') }} F</div>
            <div class="stat-lbl">Total Commissions</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="bi bi-cash-coin"></i>
        </div>
        <div>
            <div class="stat-val">{{ number_format($montantRegle, 0, ',', ' ') }} F</div>
            <div class="stat-lbl">Déjà Réglé</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
            <div class="stat-val">{{ number_format($montantRestant, 0, ',', ' ') }} F</div>
            <div class="stat-lbl">Restant à payer</div>
        </div>
    </div>
</div>

@if($societesExpirees->isNotEmpty())
<div style="background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:20px; margin-bottom:24px;">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
        <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626; font-size:1.4rem;"></i>
        <h3 style="margin:0; color:#991b1b; font-size:1.1rem; font-weight:700;">Offres expirées — Renouvellement requis</h3>
    </div>
    <p style="color:#991b1b; font-size:0.85rem; margin-bottom:16px;">Les sociétés suivantes ont une offre expirée. Leurs utilisateurs sont en lecture seule. Renouvelez l'offre pour rétablir l'accès complet.</p>

    <div style="display:flex; flex-direction:column; gap:12px;">
        @foreach($societesExpirees as $s)
        <div style="background:#fff; border:1px solid #fecaca; border-radius:10px; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
            <div>
                <div style="font-weight:700; font-size:0.95rem; color:#1f2937;">{{ $s->nom }}</div>
                <div style="font-size:0.8rem; color:#6b7280;">
                    Offre : {{ $s->offre_code }} — Expirée le {{ $s->offre_expires_at ? $s->offre_expires_at->format('d/m/Y') : 'N/A' }}
                </div>
            </div>
            <form action="{{ route('prestataire.tenants.renew', $s) }}" method="POST" style="display:flex; gap:8px; align-items:center;">
                @csrf
                <select name="offre_code" class="form-control" style="width:auto; padding:6px 10px; font-size:0.85rem;" required>
                    @foreach($rules as $r)
                        <option value="{{ $r->code }}">{{ $r->nom }} — {{ number_format($r->prix, 0, ',', ' ') }} F</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm" style="padding:6px 14px; white-space:nowrap;">
                    <i class="bi bi-arrow-repeat"></i> Renouveler
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- Sociétés + Historique paiements -->
<div class="page-grid page-grid-3">
    <!-- Liste des sociétés -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-list-stars"></i> Vos Sociétés</h3>
            </div>

            @if($societes->isEmpty())
                <div style="text-align:center; padding:40px 20px; color:var(--text-muted);">
                    <i class="bi bi-building-exclamation" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                    <p>Vous n'avez pas encore créé de société.</p>
                    <a href="{{ route('prestataire.tenants.create') }}" class="btn btn-secondary btn-sm" style="margin-top:15px;">
                        <i class="bi bi-plus-circle"></i> Créer ma première société
                    </a>
                </div>
            @else
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>Créé le</th>
                                <th>Raison Sociale</th>
                                <th>Pays / Ville</th>
                                <th>Dépôts</th>
                                <th>Collaborateurs</th>
                                <th>Offre</th>
                                <th>Statut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($societes as $s)
                            <tr>
                                <td>{{ $s->created_at->format('d/m/Y') }}</td>
                                <td class="fw-bold">
                                    {{ $s->nom }}
                                    @if($s->marque)
                                        <span class="text-muted" style="font-size:0.75rem; display:block;">({{ $s->marque }})</span>
                                    @endif
                                </td>
                                <td>{{ $s->pays }} / {{ $s->ville ?? '—' }}</td>
                                <td>
                                    <span class="badge badge-gray">{{ $s->magasins_count }} dépôt(s)</span>
                                </td>
                                <td>
                                    <span class="badge badge-gray">{{ $s->users_count }} collaborateur(s)</span>
                                </td>
                                <td>
                                    @if($s->isOffreExpiree())
                                        <span class="badge badge-danger"><i class="bi bi-clock-history"></i> Offre expirée</span>
                                    @elseif($s->offre_code)
                                        <span class="badge badge-success"><i class="bi bi-check-circle"></i> {{ $s->offre_code }}</span>
                                    @else
                                        <span class="badge badge-gray">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($s->actif)
                                        <span class="badge badge-success">Actif</span>
                                    @else
                                        <span class="badge badge-danger">Inactif</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    <!-- Historique des Paiements -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-clock-history"></i> Historique des Paiements</h3>
            </div>

            @if($historiquePaiements->isEmpty())
                <div style="text-align:center; padding:30px 10px; color:var(--text-muted);">
                    <i class="bi bi-wallet" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
                    <p style="font-size:0.85rem;">Aucun paiement enregistré pour le moment.</p>
                </div>
            @else
                <div style="display:flex; flex-direction:column; gap:12px;">
                    @foreach($historiquePaiements as $h)
                    <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid var(--border); padding-bottom:8px;">
                        <div>
                            <div style="font-weight:600; font-size:0.875rem;">
                                {{ $h->tenant ? $h->tenant->nom : 'Société Supprimée' }}
                            </div>
                            <div class="text-muted" style="font-size:0.75rem;">
                                Réglé le {{ $h->updated_at->format('d/m/Y') }}
                            </div>
                        </div>
                        <div style="font-weight:700; color:var(--success); font-size:0.9rem;">
                            +{{ number_format($h->montant, 0, ',', ' ') }} F
                        </div>
                    </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Dernières commissions -->
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-receipt"></i> Dernières Commissions</h3>
    </div>
    @if($commissions->isEmpty())
        <div style="padding:40px 20px; text-align:center; color:var(--text-muted);">
            <i class="bi bi-inbox" style="font-size:2rem; display:block; margin-bottom:8px;"></i>
            Aucune commission pour le moment.
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Société</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commissions as $c)
                    <tr>
                        <td>{{ $c->created_at->format('d/m/Y') }}</td>
                        <td>{{ $c->tenant->nom ?? '—' }}</td>
                        <td class="fw-bold">{{ number_format($c->montant, 0, ',', ' ') }} F</td>
                        <td>
                            @if($c->statut === 'reglee')
                                <span class="badge badge-success">Réglée</span>
                            @else
                                <span class="badge badge-warning">En attente</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:16px;">{{ $commissions->links() }}</div>
    @endif
</div>
@endsection
