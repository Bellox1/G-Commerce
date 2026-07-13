@extends('layouts.app')

@section('title', 'Tableau de bord Partenaire')
@section('subtitle', 'Pilotez votre activité de parrainage et suivez vos commissions')

@section('actions')
    <a href="{{ route('prestataire.tenants.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle-fill"></i> Créer une Société Cliente
    </a>
@endsection

@section('content')
<!-- Stats Grid -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 30px;">
    <!-- Total Sociétés -->
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="bi bi-building"></i>
        </div>
        <div>
            <div class="stat-val">{{ $totalSocietes }}</div>
            <div class="stat-lbl">Sociétés Créées</div>
        </div>
    </div>

    <!-- Total Commissions -->
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="bi bi-wallet2"></i>
        </div>
        <div>
            <div class="stat-val">{{ number_format($montantTotal, 0, ',', ' ') }} F</div>
            <div class="stat-lbl">Total Commissions</div>
        </div>
    </div>

    <!-- Commissions Réglées -->
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="bi bi-cash-coin"></i>
        </div>
        <div>
            <div class="stat-val">{{ number_format($montantRegle, 0, ',', ' ') }} F</div>
            <div class="stat-lbl">Déjà Réglé</div>
        </div>
    </div>

    <!-- Reste à Payer -->
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

<div class="page-grid page-grid-3">
    <!-- Liste des sociétés -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-list-stars"></i> Vos Sociétés Clientes</h3>
            </div>

            @if($societes->isEmpty())
                <div style="text-align:center; padding:40px 20px; color:var(--text-muted);">
                    <i class="bi bi-building-exclamation" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
                    <p>Vous n'avez pas encore créé de société cliente.</p>
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
                                <th>Magasins</th>
                                <th>Utilisateurs</th>
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
@endsection
