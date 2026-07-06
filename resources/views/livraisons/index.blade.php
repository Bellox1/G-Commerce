@extends('layouts.app')
@section('title', 'Suivi des Livraisons')
@section('page-title', 'Livraisons')

@section('content')
{{-- Stats chiffre d'affaire --}}
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-currency-exchange"></i></div>
        <div>
            <div class="stat-val">{{ number_format($totalMontant, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Chiffre d'affaire (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
        <div>
            <div class="stat-val">{{ number_format($totalPaye, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Montant encaissé (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-truck-flatbed"></i></div>
        <div>
            <div class="stat-val">{{ $nbLivraisons }}</div>
            <div class="stat-lbl">{{ $nbLivraisons > 1 ? 'Livraisons' : 'Livraison' }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-clock-history"></i></div>
        <div>
            <div class="stat-val">{{ number_format(($totalParStatut['en_attente'] ?? 0), 0, ',', ' ') }}</div>
            <div class="stat-lbl">En attente (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-patch-check-fill"></i></div>
        <div>
            <div class="stat-val" style="color:var(--success);">{{ number_format(($totalParStatut['livre'] ?? 0), 0, ',', ' ') }}</div>
            <div class="stat-lbl">Livré (FCFA)</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; justify-content: space-between;">
        <h3 style="margin: 0;"><i class="bi bi-truck-flatbed"></i> Livraisons</h3>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('livraisons.index') }}" class="btn btn-sm {{ !request()->filled('statut') ? 'btn-primary' : 'btn-secondary' }}">Tous</a>
            <a href="{{ route('livraisons.index', ['statut' => 'en_attente']) }}" class="btn btn-sm {{ request('statut') === 'en_attente' ? 'btn-primary' : 'btn-secondary' }}">En attente</a>
            <a href="{{ route('livraisons.index', ['statut' => 'livre']) }}" class="btn btn-sm {{ request('statut') === 'livre' ? 'btn-primary' : 'btn-secondary' }}">Livré</a>
            <a href="{{ route('livraisons.index', ['statut' => 'probleme']) }}" class="btn btn-sm {{ request('statut') === 'probleme' ? 'btn-primary' : 'btn-secondary' }}">Problème</a>
        </div>
    </div>
</div>

<div class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Facture N°</th>
                    <th>Date vente</th>
                    <th>Magasin</th>
                    <th>Client</th>
                    <th style="text-align: right;">Montant Vendu</th>
                    <th style="text-align: center;">Statut Règlement</th>
                    <th style="text-align: center;">Statut Livraison</th>
                    <th>Livreur</th>
                    <th style="text-align: center; width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventes as $v)
                <tr>
                    <td style="font-weight: 600;">
                        <a href="{{ route('livraisons.show', $v) }}" style="color: var(--primary); text-decoration: none;">
                            {{ $v->reference }}
                        </a>
                    </td>
                    <td>{{ $v->date_vente->format('d/m/Y H:i') }}</td>
                    <td><span class="badge badge-gray">{{ $v->magasin?->nom }}</span></td>
                    <td>{{ $v->client?->nomComplet() ?? 'Vente Directe (Anonyme)' }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ number_format($v->montant_total, 0, ',', ' ') }} FCFA</td>
                    <td style="text-align: center;">
                        @if($v->statut_paiement === 'paye')
                            <span class="badge badge-success">Réglé</span>
                        @elseif($v->statut_paiement === 'partiel')
                            <span class="badge badge-warning">Partiel</span>
                        @else
                            <span class="badge badge-danger">Non réglé</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($v->statut_livraison === 'livre')
                            <span class="badge badge-success"><i class="bi bi-patch-check-fill"></i> Livré</span>
                        @elseif($v->statut_livraison === 'probleme')
                            <span class="badge badge-danger"><i class="bi bi-exclamation-triangle-fill"></i> Problème</span>
                        @else
                            <span class="badge badge-warning"><i class="bi bi-clock-history"></i> En attente</span>
                        @endif
                    </td>
                    <td>{{ $v->livreur?->name ?? '-' }}</td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a href="{{ route('livraisons.show', $v) }}" class="btn btn-secondary btn-sm" style="padding: 6px 10px;" title="Gérer">
                                <i class="bi bi-gear-fill"></i> Gérer
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucune livraison correspondante ou trouvée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($ventes->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: center;">
        {{ $ventes->links() }}
    </div>
    @endif
</div>
@endsection
