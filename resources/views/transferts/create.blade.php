@extends('layouts.app')
@section('title', 'Nouveau Transfert')
@section('page-title', 'Effectuer un Transfert')

@push('styles')
<style>
    .autocomplete-wrap { position: relative; width: 100%; }
    .autocomplete-wrap input { width: 100%; }
    .autocomplete-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid var(--border); border-radius: 0 0 6px 6px; max-height: 220px; overflow-y: auto; z-index: 999; display: none; box-shadow: var(--shadow-card); }
    .autocomplete-dropdown.show { display: block; }
    .autocomplete-item { padding: 8px 14px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: .85rem; border-bottom: 1px solid var(--border); }
    .autocomplete-item:last-child { border-bottom: none; }
    .autocomplete-item:hover, .autocomplete-item.active { background: #f0fdf4; }
    .autocomplete-item .no-result { color: var(--text-muted); cursor: default; justify-content: center; font-size: .8rem; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('transferts.store') }}" id="transfertForm">
    @csrf

    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-arrow-left-right"></i> Détails du Transfert</h3>
        </div>
        <div class="card-body">
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Magasin Source <span style="color:var(--danger);">*</span></label>
                    <select name="magasin_source_id" id="magasinSource" class="form-control" required>
                        <option value="" disabled selected>Choisir le magasin source...</option>
                        @foreach($magasins as $m)
                            <option value="{{ $m->id }}" {{ old('magasin_source_id') == $m->id ? 'selected' : '' }}>{{ $m->nom }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Magasin Destination <span style="color:var(--danger);">*</span></label>
                    <select name="magasin_destination_id" class="form-control" required>
                        <option value="" disabled selected>Choisir le magasin destination...</option>
                        @foreach($magasins as $m)
                            <option value="{{ $m->id }}" {{ old('magasin_destination_id') == $m->id ? 'selected' : '' }}>{{ $m->nom }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Notes (optionnel)</label>
                <textarea name="notes" class="form-control" rows="2" placeholder="Motif ou commentaire...">{{ old('notes') }}</textarea>
            </div>
        </div>
    </div>

    {{-- Produits --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-box"></i> Produits à transférer</h3>
        </div>
        <div class="card-body">
            <div id="produitsContainer"></div>

            <button type="button" id="addLigneBtn" class="btn btn-secondary btn-sm" style="margin-top:12px;">
                <i class="bi bi-plus-circle"></i> Ajouter une ligne
            </button>

            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:20px;">
                <i class="bi bi-send"></i> Effectuer le Transfert
            </button>
            <a href="{{ route('transferts.index') }}" class="btn btn-secondary btn-sm" style="width:100%; justify-content:center; margin-top:8px;">
                Annuler
            </a>
        </div>
    </div>
</form>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const produitsData = @json($produitsJson);
        const container = document.getElementById('produitsContainer');
        const addBtn = document.getElementById('addLigneBtn');
        const sourceSelect = document.getElementById('magasinSource');

        let rowIndex = 0;

        function getSourceId() {
            return parseInt(sourceSelect.value);
        }

        function stockDispo(produitId) {
            const magId = getSourceId();
            if (!magId) return 0;
            const p = produitsData.find(x => x.id === produitId);
            return p ? (p.stockParMagasin[magId] || 0) : 0;
        }

        function produitsDisponibles(excludeIds) {
            const magId = getSourceId();
            if (!magId) return [];
            return produitsData.filter(p => {
                const stock = p.stockParMagasin[magId] || 0;
                if (stock <= 0) return false;
                if (excludeIds && excludeIds.includes(p.id)) return false;
                return true;
            });
        }

        function ajouterLigne(data) {
            const index = rowIndex++;
            const div = document.createElement('div');
            div.className = 'ligne-row';
            div.style.cssText = 'display:flex; gap:10px; margin-bottom:8px; align-items:flex-start;';

            div.innerHTML = `
                <div style="flex:2; min-width:0;">
                    <div class="autocomplete-wrap">
                        <input type="text" class="form-control produit-search" placeholder="Tapez le nom du produit..." autocomplete="off" value="${data ? data.nom : ''}">
                        <input type="hidden" name="produits[${index}][produit_id]" class="produit-id-input" value="${data ? data.produit_id : ''}">
                        <div class="autocomplete-dropdown"></div>
                    </div>
                    <small class="stock-info" style="color:var(--text-muted); font-size:.75rem; display:block; margin-top:4px;"></small>
                </div>
                <div style="flex:0.5;">
                    <input type="number" name="produits[${index}][quantite]" class="form-control quantite-input" min="1" value="${data ? data.quantite : ''}" placeholder="Qte" required style="text-align:right;" ${data ? '' : 'disabled'}>
                </div>
                <div style="flex:0 0 auto;">
                    <button type="button" class="btn btn-danger btn-sm" style="padding:8px 10px;"><i class="bi bi-x"></i></button>
                </div>
            `;

            const searchInput = div.querySelector('.produit-search');
            const hiddenInput = div.querySelector('.produit-id-input');
            const dropdown = div.querySelector('.autocomplete-dropdown');
            const stockInfo = div.querySelector('.stock-info');
            const qteInput = div.querySelector('.quantite-input');
            const delBtn = div.querySelector('.btn-danger');

            let selectedProduit = data ? { id: data.produit_id, nom: data.nom, stock: stockDispo(data.produit_id) } : null;

            function getExcludeIds() {
                const ids = [];
                container.querySelectorAll('.produit-id-input').forEach(inp => {
                    const val = parseInt(inp.value);
                    if (val && inp !== hiddenInput) ids.push(val);
                });
                return ids;
            }

            function filterProduits(query) {
                const q = query.toLowerCase().trim();
                const magId = getSourceId();
                if (!magId) return [];
                const exclude = getExcludeIds();
                return produitsDisponibles(exclude).filter(p => {
                    if (!q) return true;
                    return p.nom.toLowerCase().includes(q);
                });
            }

            function renderDropdown(results) {
                dropdown.innerHTML = '';
                if (results.length === 0) {
                    dropdown.innerHTML = '<div class="autocomplete-item no-result">Aucun produit disponible</div>';
                    dropdown.classList.add('show');
                    return;
                }
                results.forEach((p, idx) => {
                    const item = document.createElement('div');
                    item.className = 'autocomplete-item' + (idx === 0 ? ' active' : '');
                    const stock = p.stockParMagasin[getSourceId()] || 0;
                    item.innerHTML = `<span>${p.nom} <small style="color:var(--text-muted);">(${stock} dispo)</small></span>`;
                    item.dataset.id = p.id;
                    item.dataset.nom = p.nom;
                    item.dataset.stock = stock;
                    item.addEventListener('click', function() { selectProduct(this); });
                    dropdown.appendChild(item);
                });
                dropdown.classList.add('show');
            }

            function selectProduct(item) {
                selectedProduit = {
                    id: parseInt(item.dataset.id),
                    nom: item.dataset.nom,
                    stock: parseInt(item.dataset.stock),
                };
                searchInput.value = selectedProduit.nom + ' (' + selectedProduit.stock + ' dispo)';
                hiddenInput.value = selectedProduit.id;
                stockInfo.textContent = 'Stock disponible : ' + selectedProduit.stock;
                qteInput.disabled = false;
                qteInput.max = selectedProduit.stock;
                qteInput.value = selectedProduit.stock;
                qteInput.focus();
                dropdown.classList.remove('show');
            }

            searchInput.addEventListener('focus', function() {
                const magId = getSourceId();
                if (!magId) {
                    dropdown.innerHTML = '<div class="autocomplete-item no-result">Sélectionnez d\'abord un magasin source</div>';
                    dropdown.classList.add('show');
                    return;
                }
                const results = filterProduits(this.value);
                renderDropdown(results);
            });

            searchInput.addEventListener('input', function() {
                selectedProduit = null;
                hiddenInput.value = '';
                stockInfo.textContent = '';
                qteInput.disabled = true;
                qteInput.value = '';
                const magId = getSourceId();
                if (!magId) {
                    dropdown.innerHTML = '<div class="autocomplete-item no-result">Sélectionnez d\'abord un magasin source</div>';
                    dropdown.classList.add('show');
                    return;
                }
                const results = filterProduits(this.value);
                renderDropdown(results);
            });

            searchInput.addEventListener('keydown', function(e) {
                const items = dropdown.querySelectorAll('.autocomplete-item:not(.no-result)');
                const active = dropdown.querySelector('.autocomplete-item.active');
                let idx = Array.from(items).indexOf(active);
                if (e.key === 'ArrowDown') { e.preventDefault(); if (idx < items.length - 1) { if (active) active.classList.remove('active'); items[idx + 1].classList.add('active'); } }
                else if (e.key === 'ArrowUp') { e.preventDefault(); if (idx > 0) { if (active) active.classList.remove('active'); items[idx - 1].classList.add('active'); } }
                else if (e.key === 'Enter') { e.preventDefault(); if (active) selectProduct(active); else if (items.length > 0) selectProduct(items[0]); }
                else if (e.key === 'Escape') { dropdown.classList.remove('show'); }
            });

            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                    dropdown.classList.remove('show');
                }
            });

            delBtn.addEventListener('click', function() {
                div.remove();
            });

            container.appendChild(div);

            if (!data) {
                setTimeout(() => searchInput.focus(), 50);
            }
        }

        addBtn.addEventListener('click', function() {
            if (!getSourceId()) {
                alert('Veuillez d\'abord sélectionner un magasin source.');
                return;
            }
            ajouterLigne();
        });

        sourceSelect.addEventListener('change', function() {
            container.innerHTML = '';
            rowIndex = 0;
            if (getSourceId()) {
                ajouterLigne();
            }
        });

        document.getElementById('transfertForm').addEventListener('submit', function(e) {
            const lignes = container.querySelectorAll('.ligne-row');
            if (lignes.length === 0) {
                e.preventDefault();
                alert('Ajoutez au moins un produit à transférer.');
                return;
            }
            let valid = true;
            lignes.forEach(function(div) {
                const hidden = div.querySelector('.produit-id-input');
                const qte = div.querySelector('.quantite-input');
                if (!hidden.value) {
                    div.querySelector('.produit-search').style.borderColor = 'var(--danger)';
                    valid = false;
                }
                if (!qte.value || parseInt(qte.value) < 1) {
                    qte.style.borderColor = 'var(--danger)';
                    valid = false;
                }
            });
            if (!valid) {
                e.preventDefault();
                alert('Veuillez remplir toutes les lignes correctement.');
            }
        });
    });
</script>
@endpush
@endsection
