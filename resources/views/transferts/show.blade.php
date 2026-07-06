@extends('layouts.app')
@section('title', 'Détails du Transfert ' . $transfert->reference)
@section('page-title', 'Transfert : ' . $transfert->reference)

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
    <div class="card" style="background: white; border-left: 4px solid {{ $transfert->statut === 'livre' ? 'var(--success)' : 'var(--warning)' }};">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <span class="badge {{ $transfert->statut === 'livre' ? 'badge-success' : 'badge-warning' }}" style="margin-bottom: 6px;">
                    {{ $transfert->statut === 'livre' ? 'Livré' : 'En transit' }}
                </span>
                <h2 style="font-size: 1.4rem; font-weight: 700;">Transfert {{ $transfert->reference }}</h2>
                <div style="font-size: .8rem; color: var(--text-muted); margin-top: 4px;">
                    <i class="bi bi-calendar"></i> Créé le {{ $transfert->created_at->format('d/m/Y à H:i') }} par <strong>{{ $transfert->user?->name }}</strong>
                </div>
            </div>
            <a href="{{ route('transferts.index') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Liste des transferts
            </a>
        </div>
    </div>

    <div class="page-grid page-grid-3">
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-info-circle"></i> Détails du Transfert</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 500; font-size: .85rem;">Magasin Source :</span>
                        <span style="font-weight: 600; font-size: .85rem; color: var(--danger);">{{ $transfert->magasinSource?->nom }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 500; font-size: .85rem;">Magasin Destination :</span>
                        <span style="font-weight: 600; font-size: .85rem; color: var(--success);">{{ $transfert->magasinDestination?->nom }}</span>
                    </div>
                    @if($transfert->notes)
                    <div style="display: flex; flex-direction: column; gap: 4px; padding: 12px 16px;">
                        <span style="font-weight: 500; font-size: .85rem;">Notes :</span>
                        <span style="font-size: .85rem; color: var(--text-muted);">{{ $transfert->notes }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-box"></i> Produits transférés</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    @if($transfert->produits && $transfert->produits->count())
                    <div class="table-wrap" style="padding: 8px 16px; border: none; margin-bottom: 0;">
                        <table style="width:100%; border-collapse:collapse; font-size:.85rem;">
                            <thead>
                                <tr>
                                    <th style="text-align:left; padding:8px 4px; border-bottom:1px solid var(--border);">Produit</th>
                                    <th style="text-align:right; padding:8px 4px; border-bottom:1px solid var(--border);">Quantité</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfert->produits as $tp)
                                <tr>
                                    <td style="padding:8px 4px; border-bottom:1px solid var(--border); font-weight:500;">{{ $tp->produit?->nom }}</td>
                                    <td style="padding:8px 4px; border-bottom:1px solid var(--border); text-align:right; font-weight:600;">{{ $tp->quantite }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @elseif($transfert->produit)
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px;">
                        <span style="font-weight: 500; font-size: .85rem;">Produit :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ $transfert->produit?->nom }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px;">
                        <span style="font-weight: 500; font-size: .85rem;">Quantité :</span>
                        <span style="font-weight: 700; font-size: .85rem;">{{ $transfert->quantite }} Carton</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($transfert->statut === 'livre' && $transfert->date_livraison)
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-check-circle"></i> Livraison</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 500; font-size: .85rem;">Date de livraison :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ $transfert->date_livraison->format('d/m/Y à H:i') }}</span>
                    </div>
                    @if($transfert->livreur)
                    <div style="display: flex; justify-content: space-between; padding: 8px 0;">
                        <span style="font-weight: 500; font-size: .85rem;">Livreur :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ $transfert->livreur?->name }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
