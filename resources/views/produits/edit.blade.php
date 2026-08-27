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
        <form method="POST" action="{{ route('produits.update', $produit) }}" id="produitForm" enctype="multipart/form-data">
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
                    <input type="number" name="prix_vente_conseille" class="form-control" value="{{ $produit->prix_vente_conseille ? intval($produit->prix_vente_conseille) : '' }}" min="0" step="1" oninput="this.value = parseInt(this.value) || ''">
                </div>

                <div class="form-group">
                    <label class="form-label">Stock par magasin</label>
                    <small style="color:var(--text-muted); font-size:.75rem; display:block; margin-bottom:8px;">Quantité actuelle dans chaque magasin (ajustement automatique à la sauvegarde). Le stock total est centralisé.</small>
                    <div style="display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px;">
                        @foreach($magasins as $m)
                            <div style="display:flex; flex-direction:column; gap:4px;">
                                <span style="font-size:.75rem; font-weight:600; color:var(--text-muted);">{{ $m->nom }}</span>
                                <div style="display:flex; align-items:center; gap:6px;">
                                    <input type="number" name="stocks[{{ $m->id }}]" class="form-control" value="{{ $stockParMagasin[$m->id] ?? 0 }}" min="0" style="width:90px;">
                                    <span style="font-size:.75rem; color:var(--text-muted);">ctn</span>
                                    <span class="cartouche-stock-field" style="display:{{ $produit->a_cartouche ? 'inline-flex' : 'none' }}; align-items:center; gap:4px;">
                                        <input type="number" name="stocks_cartouches[{{ $m->id }}]" class="form-control" value="{{ $stockCartouchesParMagasin[$m->id] ?? 0 }}" min="0" style="width:70px;">
                                        <span style="font-size:.75rem; color:var(--text-muted);">ctr</span>
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
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
                    <input type="number" name="prix_cartouche" id="prixCartouche" class="form-control" value="{{ $produit->prix_cartouche ? intval($produit->prix_cartouche) : '' }}" min="0" step="1">
                    <small style="color: var(--text-muted); font-size: .75rem;">Laissez vide pour calcul automatique</small>
                </div>
            </div>

            <div class="form-row form-row-2" style="margin-top: 16px;">
                <div class="form-group">
                    <label class="form-label">Image du produit <small style="color:var(--text-muted);">— optionnel</small></label>
                    <div>
                        <input type="file" name="image" id="imgInput" class="form-control" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                        <small style="color:var(--text-muted); font-size:.75rem;">Formats : JPEG, PNG, GIF, WebP — max 10 Mo</small>
                    </div>
                    <div id="imgPreview" style="margin-top:8px; display:{{ $produit->image ? 'block' : 'none' }};">
                        @if($produit->image)
                            @php $imgSrc = str_starts_with($produit->image, 'http') ? $produit->image : asset('storage/' . $produit->image); @endphp
                            <img src="{{ $imgSrc }}" alt="{{ $produit->nom }}" style="max-width:150px; max-height:150px; border-radius:6px; border:1px solid var(--border);" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                            <span style="display:none; width:120px; height:120px; border-radius:6px; background:#f1f5f9; align-items:center; justify-content:center; color:#94a3b8; font-size:2rem;">
                                <i class="bi bi-image"></i>
                            </span>
                        @else
                            <img src="" alt="Aperçu" style="max-width:150px; max-height:150px; border-radius:6px; border:1px solid var(--border);" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                            <span style="display:none; width:120px; height:120px; border-radius:6px; background:#f1f5f9; align-items:center; justify-content:center; color:#94a3b8; font-size:2rem;">
                                <i class="bi bi-image"></i>
                            </span>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Description / Remarques</label>
                    <textarea name="description" class="form-control" rows="2">{{ $produit->description }}</textarea>
                </div>
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
document.getElementById('imgInput').addEventListener('change', function() {
    const preview = document.getElementById('imgPreview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.style.display = 'block';
            const img = preview.querySelector('img');
            if (img) {
                img.style.display = 'block';
                img.src = e.target.result;
            }
        };
        reader.readAsDataURL(this.files[0]);
    }
});

document.getElementById('hasCartouche').addEventListener('change', function() {
    document.getElementById('cartoucheFields').style.display = this.checked ? 'grid' : 'none';
    document.querySelectorAll('.cartouche-stock-field').forEach(function(el) {
        el.style.display = this.checked ? 'inline-flex' : 'none';
    }, this);
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
