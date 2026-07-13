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
                    <div style="display:flex; gap:0; margin-bottom:8px;">
                        <button type="button" class="btn btn-sm img-tab active" id="tabFile" onclick="switchImgMode('file')" style="border-radius:6px 0 0 6px; border:1px solid var(--border); background:var(--primary); color:#fff; padding:4px 12px; cursor:pointer; font-size:.8rem;">
                            <i class="bi bi-upload"></i> Fichier
                        </button>
                        <button type="button" class="btn btn-sm img-tab" id="tabUrl" onclick="switchImgMode('url')" style="border-radius:0 6px 6px 0; border:1px solid var(--border); border-left:0; background:#f1f5f9; color:#64748b; padding:4px 12px; cursor:pointer; font-size:.8rem;">
                            <i class="bi bi-link-45deg"></i> URL
                        </button>
                    </div>
                    <div id="imgFileInput">
                        <input type="file" name="image" class="form-control" accept="image/jpeg,image/png,image/jpg,image/gif,image/webp">
                        <small style="color:var(--text-muted); font-size:.75rem;">Formats : JPEG, PNG, GIF, WebP — max 2 Mo</small>
                    </div>
                    <div id="imgUrlInput" style="display:none;">
                        <input type="url" name="image_url" class="form-control" placeholder="https://exemple.com/image.jpg">
                        <small style="color:var(--text-muted); font-size:.75rem;">Collez un lien direct vers une image (JPG, PNG...)</small>
                    </div>
                    <div id="imgPreview" style="margin-top:8px; display:none;">
                        <img src="" alt="Aperçu" style="max-width:150px; max-height:150px; border-radius:6px; border:1px solid var(--border);" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                        <span style="display:none; width:120px; height:120px; border-radius:6px; background:#f1f5f9; align-items:center; justify-content:center; color:#94a3b8; font-size:2rem;">
                            <i class="bi bi-image"></i>
                        </span>
                    </div>
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
function switchImgMode(mode) {
    const fileInput = document.getElementById('imgFileInput');
    const urlInput = document.getElementById('imgUrlInput');
    const tabFile = document.getElementById('tabFile');
    const tabUrl = document.getElementById('tabUrl');
    const preview = document.getElementById('imgPreview');
    if (mode === 'url') {
        fileInput.style.display = 'none';
        urlInput.style.display = 'block';
        tabUrl.style.background = 'var(--primary)'; tabUrl.style.color = '#fff';
        tabFile.style.background = '#f1f5f9'; tabFile.style.color = '#64748b';
        document.querySelector('[name="image"]').value = '';
    } else {
        fileInput.style.display = 'block';
        urlInput.style.display = 'none';
        tabFile.style.background = 'var(--primary)'; tabFile.style.color = '#fff';
        tabUrl.style.background = '#f1f5f9'; tabUrl.style.color = '#64748b';
        document.querySelector('[name="image_url"]').value = '';
        preview.style.display = 'none';
    }
}
document.querySelector('[name="image_url"]').addEventListener('input', function() {
    const preview = document.getElementById('imgPreview');
    const img = preview.querySelector('img');
    const placeholder = preview.querySelector('span');
    if (this.value) {
        img.style.display = 'none'; img.src = '';
        placeholder.style.display = 'none';
        preview.style.display = 'block';
        img.src = this.value;
    } else {
        preview.style.display = 'none';
    }
});
document.querySelector('[name="image"]').addEventListener('change', function() {
    const preview = document.getElementById('imgPreview');
    if (this.files && this.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) { preview.style.display = 'block'; preview.querySelector('img').src = e.target.result; };
        reader.readAsDataURL(this.files[0]);
    } else {
        preview.style.display = 'none';
    }
});

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
