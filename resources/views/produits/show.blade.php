@extends('layouts.app')
@section('title', $produit->nom)
@section('page-title', $produit->nom)

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
    {{-- En-tête --}}
    <div class="card" style="border-left: 4px solid {{ $produit->actif ? 'var(--success)' : 'var(--danger)' }};">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                @if($produit->image)
                    <img src="{{ asset('storage/' . $produit->image) }}" alt="{{ $produit->nom }}"
                         style="width: 64px; height: 64px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border);">
                @else
                    <span style="display: inline-flex; width: 64px; height: 64px; border-radius: 8px; background: #f1f5f9; align-items: center; justify-content: center; color: #94a3b8; font-size: 1.5rem;">
                        <i class="bi bi-image"></i>
                    </span>
                @endif
                <div>
                    <span class="badge {{ $produit->actif ? 'badge-success' : 'badge-danger' }}" style="margin-bottom: 4px;">
                        {{ $produit->actif ? 'Actif' : 'Inactif' }}
                    </span>
                    <h2 style="font-size: 1.4rem; font-weight: 700; margin: 0;">{{ $produit->nom }}</h2>
                    <div style="font-size: .8rem; color: var(--text-muted); margin-top: 4px;">
                        Réf: PRD-{{ $produit->id }}
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('produits.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Liste
                </a>
                @if(Auth::user()->peutModifierCatalogues())
                <a href="{{ route('produits.edit', $produit) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="page-grid page-grid-3">
        {{-- Colonne gauche : Image + Infos --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            {{-- Image --}}
            <div class="card">
                <div class="card-body" style="padding: 16px; display: flex; justify-content: center;">
                    @if($produit->image)
                        <img src="{{ asset('storage/' . $produit->image) }}" alt="{{ $produit->nom }}"
                             style="max-width: 100%; max-height: 300px; border-radius: 8px; object-fit: contain;">
                    @else
                        <span style="display: inline-flex; width: 200px; height: 200px; border-radius: 8px; background: #f1f5f9; align-items: center; justify-content: center; color: #94a3b8; font-size: 3rem;">
                            <i class="bi bi-image"></i>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            @if($produit->description)
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-card-text"></i> Description</h3>
                </div>
                <div class="card-body">
                    <p style="margin: 0; font-size: .9rem; line-height: 1.6; color: var(--text); white-space: pre-wrap;">{{ $produit->description }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Colonne milieu : Infos prix & stock --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-coin"></i> Prix & Stock</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Prix de vente conseillé :</span>
                        <span style="font-weight: 700; font-size: .9rem; color: var(--primary);">{{ number_format($produit->prix_vente_conseille, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Prix marché :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ number_format($produit->prix_marche, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Seuil d'alerte :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ $produit->seuil_alerte }}</span>
                    </div>
                    @if($produit->a_cartouche)
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Cartouches par carton :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ $produit->cartouche_par_carton }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px;">
                        <span style="font-size: .85rem;">Prix cartouche :</span>
                        <span style="font-weight: 600; font-size: .85rem; color: var(--primary);">{{ number_format($produit->prix_cartouche_effectif, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Stock par magasin --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-boxes"></i> Stock par Magasin</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    @forelse($magasins as $m)
                    @php $stock = $stockParMagasin[$m->id] ?? 0; @endphp
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: {{ !$loop->last ? '1px solid var(--border)' : 'none' }};">
                        <span style="font-size: .85rem;">{{ $m->nom }}</span>
                        <span class="badge {{ $stock > 0 ? 'badge-success' : 'badge-danger' }}">
                            {{ $stock }} Carton
                        </span>
                    </div>
                    @empty
                    <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: .85rem;">Aucun magasin</div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Colonne droite : Mouvements récents --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-clock-history"></i> Mouvements Récents</h3>
                </div>
                <div class="card-body" style="padding: 0; max-height: 400px; overflow-y: auto;">
                    @forelse($mouvements as $mvt)
                    <div style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; border-bottom: 1px solid var(--border); font-size: .8rem;">
                        <span class="badge {{ in_array($mvt->type, ['entree_arrivage','transfert_entree','ajustement_positif']) ? 'badge-success' : 'badge-danger' }}" style="min-width: 70px; text-align: center;">
                            {{ $mvt->signe() > 0 ? '+' : '-' }}{{ $mvt->quantite }}
                        </span>
                        <div style="flex: 1;">
                            <div style="font-weight: 600;">{{ ucfirst(str_replace('_', ' ', $mvt->type)) }}</div>
                            <div style="color: var(--text-muted); font-size: .75rem;">
                                {{ $mvt->magasin?->nom }} @if($mvt->user) · {{ $mvt->user->name }} @endif
                            </div>
                        </div>
                        <div style="color: var(--text-muted); font-size: .75rem; text-align: right; white-space: nowrap;">
                            {{ $mvt->date_mouvement?->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    @empty
                    <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: .85rem;">
                        Aucun mouvement enregistré.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection