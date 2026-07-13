@extends('layouts.app')
@section('title', "Livraison : " . $vente->reference)



@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('livraisons.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour au suivi
    </a>
</div>

<div class="page-grid page-grid-3">
    <!-- Détails de la vente et des produits -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-cart-fill"></i> Articles de cette commande</h3>
            </div>
            <div class="table-wrap" style="margin-bottom: 0;">
                <table>
                    <thead>
                        <tr>
                            <th class="wrap-text">Désignation du Produit</th>
                            <th style="text-align: center;">Quantité vendue</th>
                            <th style="text-align: right;">Prix Unitaire</th>
                            <th style="text-align: right;">Total Ligne</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vente->lignes as $l)
                        <tr>
                            <td class="wrap-text" style="font-weight: 500;">{{ $l->produit?->nom }}</td>
                            <td style="text-align: center; font-weight: 700; font-size: 1.1rem; color: var(--primary);">
                                {{ $l->quantite }}
                            </td>
                            <td style="text-align: right;">{{ number_format($l->prix_vente, 0, ',', ' ') }} FCFA</td>
                            <td style="text-align: right; font-weight: 600;">{{ number_format($l->total_ligne, 0, ',', ' ') }} FCFA</td>
                        </tr>
                        @endforeach
                        <tr style="background: #f8fafc; font-weight: 700;">
                            <td colspan="3" style="text-align: right;">Montant Vente Total :</td>
                            <td style="text-align: right; color: var(--primary); font-size: 1.1rem;">
                                {{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        @if($vente->notes)
        <div class="card">
            <h4 style="margin-bottom: 10px;"><i class="bi bi-sticky"></i> Note client ou vendeur :</h4>
            <div style="padding: 12px; background: #fffbeb; border-radius: 4px; font-family: 'Inter', sans-serif;">
                {{ $vente->notes }}
            </div>
        </div>
        @endif
    </div>

    <!-- Statut et informations client -->
    <div>
        <!-- Statut de livraison actuel -->
        <div class="card">
            <h3 style="margin-bottom:12px;"><i class="bi bi-info-circle"></i> Info Livraison</h3>
            
            <div style="margin-bottom: 20px;">
                <label class="form-label" style="margin-bottom: 4px;">Statut actuel :</label>
                <div style="font-size: 1.1rem; font-weight: 700;">
                    @if($vente->statut_livraison === 'livre')
                        <span style="color: var(--success);"><i class="bi bi-patch-check-fill"></i> LIVRÉ</span>
                    @elseif($vente->statut_livraison === 'probleme')
                        <span style="color: var(--danger);"><i class="bi bi-exclamation-triangle-fill"></i> PROBLÈME SIGNALÉ</span>
                    @else
                        <span style="color: var(--warning);"><i class="bi bi-clock-history"></i> EN ATTENTE DE LIVRAISON</span>
                    @endif
                </div>
            </div>

            @if($vente->date_livraison)
            <div style="margin-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 0.9rem;">
                <strong>Date livraison :</strong> {{ $vente->date_livraison->format('d/m/Y \à H\hi') }}
            </div>
            @endif

            @if($vente->livreur)
            <div style="margin-bottom: 10px; font-family: 'Inter', sans-serif; font-size: 0.9rem;">
                <strong>Livreur assigné :</strong> {{ $vente->livreur->name }}
            </div>
            @endif

            @if($vente->note_livraison)
            <div style="margin-bottom: 20px; font-family: 'Inter', sans-serif; font-size: 0.9rem;">
                <strong>Commentaire de livraison :</strong>
                <div style="padding: 10px; background: var(--bg); border: 1px solid var(--border); border-radius: 6px; margin-top: 6px; white-space: pre-wrap;">{{ $vente->note_livraison }}</div>
            </div>
            @endif
        </div>

        <!-- Mettre à jour le statut -->
        <div class="card">
            <h3 style="margin-bottom: 16px;"><i class="bi bi-pencil-square"></i> Mettre à jour le statut</h3>
            <form action="{{ route('livraisons.update-statut', $vente) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group">
                    <label class="form-label">Nouveau Statut</label>
                    <select name="statut_livraison" class="form-control" required>
                        <option value="en_attente" {{ $vente->statut_livraison === 'en_attente' ? 'selected' : '' }}>En attente de livraison</option>
                        <option value="livre" {{ $vente->statut_livraison === 'livre' ? 'selected' : '' }}>Livré</option>
                        <option value="probleme" {{ $vente->statut_livraison === 'probleme' ? 'selected' : '' }}>Problème (erreur de quantité, retour, etc.)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Note ou observation</label>
                    <textarea name="note_livraison" rows="3" class="form-control" placeholder="Ajouter une note facultative (ex : reçu par le frère, carton légèrement ouvert, etc.)">{{ $vente->note_livraison }}</textarea>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 10px;">
                    <i class="bi bi-save"></i> Enregistrer
                </button>
            </form>
        </div>

        <!-- Client et Magasin -->
        <div class="card">
            <h3 style="margin-bottom: 12px;"><i class="bi bi-person-fill"></i> Client</h3>
            <p style="font-weight: 700; margin-bottom: 4px;">{{ $vente->client?->nomComplet() ?? 'Vente Directe (Anonyme)' }}</p>
            @if($vente->client?->telephone)
            <p style="font-family: 'Inter', sans-serif; font-size: 0.9rem; color: var(--text-muted);">
                <i class="bi bi-telephone-fill"></i> <a href="tel:{{ $vente->client->telephone }}">{{ $vente->client->telephone }}</a>
            </p>
            @endif

            <hr style="border: none; border-top: 1px solid var(--border); margin: 16px 0;">

            <h3 style="margin-bottom: 8px;"><i class="bi bi-shop"></i> Magasin d'expédition</h3>
            <span class="badge badge-gray" style="font-size: 0.85rem; padding: 6px 12px;">{{ $vente->magasin?->nom }}</span>
        </div>
    </div>
</div>
@endsection
