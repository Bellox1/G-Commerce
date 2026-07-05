@extends('layouts.app')
@section('title', 'Modifier le Produit')
@section('page-title', 'Modifier le Produit')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-pencil"></i> Modifier {{ $produit->nom }}</h3>
        <a href="{{ route('produits.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
    
    <div class="card-body">
        <form method="POST" action="{{ route('produits.update', $produit) }}" id="produitForm">
            @csrf
            @method('PUT')
            
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Nom du produit <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="nom" class="form-control" value="{{ $produit->nom }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Seuil d'alerte stock <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="seuil_alerte" class="form-control" value="{{ $produit->seuil_alerte }}" min="0" required>
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Prix de Vente (FCFA)</label>
                    <input type="number" name="prix_vente_conseille" class="form-control" value="{{ $produit->prix_vente_conseille }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Stock (tous magasins)</label>
                    <input type="text" class="form-control" value="Géré via les mouvements" readonly style="background:#f5f5f5;">
                </div>
                <input type="hidden" name="stock" value="0">
            </div>

            <div class="form-group" style="margin-top: 8px;">
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="hidden" name="a_cartouche" value="0">
                        <input type="checkbox" name="a_cartouche" value="1" id="hasCartouche" {{ $produit->a_cartouche ? 'checked' : '' }}>
                        <span class="checkbox-custom"></span>
                        Ce produit a des <strong>cartouches</strong>
                    </label>
                </div>
            </div>

            <div id="cartoucheFields" style="display: {{ $produit->a_cartouche ? 'grid' : 'none' }};" class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Nombre de cartouches <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="cartouche_par_carton" id="cartoucheParCarton" class="form-control" value="{{ $produit->cartouche_par_carton }}" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Prix cartouche (FCFA)</label>
                    <input type="number" name="prix_cartouche" id="prixCartouche" class="form-control" value="{{ $produit->prix_cartouche }}" min="0">
                    <small style="color: var(--text-muted); font-size: .75rem;">Laissez vide pour calcul automatique</small>
                </div>
            </div>

            <div class="form-group" style="margin-top: 16px;">
                <label class="form-label">Description / Remarques</label>
                <textarea name="description" class="form-control" rows="2">{{ $produit->description }}</textarea>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <a href="{{ route('produits.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Enregistrer les Modifications</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('hasCartouche').addEventListener('change', function() {
    document.getElementById('cartoucheFields').style.display = this.checked ? 'grid' : 'none';
});

function calcPrixCartouche() {
    const prix = parseFloat(document.querySelector('[name="prix_vente_conseille"]').value) || 0;
    const nb = parseFloat(document.getElementById('cartoucheParCarton').value) || 0;
    const input = document.getElementById('prixCartouche');
    if (nb > 0 && prix > 0) {
        input.placeholder = Math.round(prix / nb) + ' FCFA';
    } else {
        input.placeholder = 'Auto';
    }
}
document.querySelector('[name="prix_vente_conseille"]').addEventListener('input', calcPrixCartouche);
document.getElementById('cartoucheParCarton').addEventListener('input', calcPrixCartouche);
calcPrixCartouche();
</script>
@endpush
