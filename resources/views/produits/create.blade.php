@extends('layouts.app')
@section('title', 'Nouveau Produit')
@section('page-title', 'Nouveau Produit')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-plus-circle"></i> Ajouter un nouvel article</h3>
        <a href="{{ route('produits.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
    
    <div class="card-body">
        <form method="POST" action="{{ route('produits.store') }}" id="produitForm" enctype="multipart/form-data">
            @csrf
            
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Nom du produit <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="nom" class="form-control" placeholder="Ex: Ricci Premium" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Prix de Vente (FCFA)</label>
                    <input type="number" name="prix_vente_conseille" class="form-control" placeholder="0" min="0" step="1" oninput="this.value = parseInt(this.value) || ''">
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Magasin de départ <span style="color:var(--danger);">*</span></label>
<select name="magasin_id" class="form-control" required>
    @foreach($magasins as $m)
        <option value="{{ $m->id }}" {{ $loop->first ? 'selected' : '' }}>{{ $m->nom }}</option>
    @endforeach
</select>
                    <small style="color:var(--text-muted); font-size:.75rem;">Le stock initial sera enregistré dans ce magasin.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Stock initial</label>
                    <input type="number" name="stock_initial" id="stockInitial" class="form-control" value="0" min="0">
                    <small style="color:var(--text-muted); font-size:.75rem;">Quantité de départ dans ce magasin.</small>
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Seuil d'alerte stock</label>
                    <input type="number" name="seuil_alerte" id="seuilAlerte" class="form-control" value="5" min="0" required>
                    <small style="color: var(--text-muted); font-size: .75rem;">Auto : ¼ du stock initial</small>
                </div>
            </div>

            <div class="form-group" style="margin-top: 8px;">
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="hidden" name="a_cartouche" value="0">
                        <input type="checkbox" name="a_cartouche" value="1" id="hasCartouche">
                        <span class="checkbox-custom"></span>
                        Ce produit a des <strong>cartouches</strong>
                    </label>
                </div>
            </div>

            <div id="cartoucheFields" style="display: none;" class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Nombre de cartouches <span style="color:var(--danger);">*</span></label>
                    <input type="number" name="cartouche_par_carton" id="cartoucheParCarton" class="form-control" placeholder="Ex: 9" min="1">
                </div>
                <div class="form-group">
                    <label class="form-label">Prix cartouche (FCFA)</label>
                    <input type="number" name="prix_cartouche" id="prixCartouche" class="form-control" placeholder="Auto" min="0">
                    <small style="color: var(--text-muted); font-size: .75rem;">Laissez vide pour calcul automatique</small>
                </div>
            </div>

            <div class="form-row form-row-2" style="margin-top: 16px;">
                <div class="form-group">
                    <label class="form-label">Image du produit <small style="color:var(--text-muted);">— optionnel</small></label>
                    <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                    <small style="color:var(--text-muted); font-size:.75rem;">Formats : JPEG, PNG, GIF, WebP — max 2 Mo</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Description / Remarques</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Notes (optionnel)..."></textarea>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <a href="{{ route('produits.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer le Produit</button>
            </div>
        </form>
    </div>
</div>

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

const seuilInput = document.getElementById('seuilAlerte');
const stockInput = document.getElementById('stockInitial');
stockInput.addEventListener('input', function() {
    const s = parseInt(this.value) || 0;
    seuilInput.value = Math.ceil(s / 4);
});
seuilInput.addEventListener('focus', function() {
    this.select();
});
</script>
@endpush
@endsection
