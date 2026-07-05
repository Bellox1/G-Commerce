@extends('layouts.app')
@section('title', 'Nouvel Arrivage (🇳🇬 Nigeria -> 🇧🇯 Bénin)')
@section('page-title', 'Enregistrer un Arrivage')

@push('styles')
<style>
    .autocomplete-wrap { position: relative; width: 100%; }
    .autocomplete-wrap input { width: 100%; }
    .autocomplete-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid var(--border); border-radius: 0 0 6px 6px; max-height: 220px; overflow-y: auto; z-index: 999; display: none; box-shadow: var(--shadow-card); }
    .autocomplete-dropdown.show { display: block; }
    .autocomplete-item { padding: 8px 14px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: .85rem; border-bottom: 1px solid var(--border); }
    .autocomplete-item:last-child { border-bottom: none; }
    .autocomplete-item:hover, .autocomplete-item.active { background: #f0fdf4; }
    .autocomplete-item .price-badge { font-size: .7rem; padding: 2px 8px; border-radius: 10px; font-weight: 600; background: #dbeafe; color: #1e40af; }
    .autocomplete-item .fourn-badge { font-size: .7rem; padding: 2px 8px; border-radius: 10px; font-weight: 600; background: #f3e8ff; color: #6b21a8; }
    .autocomplete-item .no-result { color: var(--text-muted); cursor: default; justify-content: center; font-size: .8rem; }
    .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.4); z-index: 9999; display: none; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal-box { background: #fff; border-radius: 12px; width: 420px; max-width: 94vw; max-height: 90vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
    .modal-box h4 { margin: 0 0 16px 0; font-size: 1.05rem; }
    .produit-row { transition: box-shadow .15s, border-color .15s; cursor: default; }
    .produit-row.selected { box-shadow: 0 0 0 2px var(--primary); border-radius: 6px; }
    .produit-row .fourn-tag { display: inline-flex; align-items: center; gap: 4px; font-size: .7rem; padding: 2px 8px; border-radius: 10px; font-weight: 600; margin-left: 6px; }
    .supplier-legend { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
    .supplier-legend-item { display: flex; align-items: center; gap: 6px; font-size: .8rem; padding: 4px 10px; border-radius: 20px; background: #f8fafc; border: 1px solid var(--border); }
    .supplier-legend-item .color-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
    .assign-bar { display: flex; gap: 8px; align-items: center; flex-wrap: wrap; }
    .assign-bar .autocomplete-wrap { flex: 1; min-width: 140px; }
    .assign-bar .autocomplete-wrap input { font-size: .85rem; }
</style>
@endpush

@section('content')
<form method="POST" action="{{ route('arrivages.store') }}" id="arrivageForm">
    @csrf

    <div class="page-grid page-grid-3">
        
        {{-- Section Principale : Choix des Produits --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-cart"></i> Articles Importés</h3>
                </div>
                <div class="card-body">
                    <p style="font-size: .8rem; color: var(--text-muted); margin-bottom: 16px;">
                        Ajoutez les produits contenus dans ce camion. Cliquez sur une ligne pour la sélectionner, puis attribuez-lui un fournisseur depuis le panneau de droite.
                    </p>

                    <div id="produits-container">
                        <div class="produit-row flex-row-mobile" style="margin-bottom: 12px; border-bottom: 1px dashed var(--border); padding-bottom: 12px;">
                            <div style="flex: 2;">
                                <label class="form-label" style="font-size: .7rem;">Article / Produit</label>
                                <div class="autocomplete-wrap">
                                    <input type="text" class="form-control produit-search" placeholder="Tapez le nom du produit..." data-row="0" autocomplete="off" required>
                                    <input type="hidden" name="produits[0][produit_id]" class="produit-id">
                                    <input type="hidden" name="produits[0][fournisseur_id]" class="fourn-id" value="">
                                    <div class="autocomplete-dropdown" data-row="0"></div>
                                </div>
                            </div>
                            <div style="flex: 1;">
                                <label class="form-label" style="font-size: .7rem;">Quantité</label>
                                <input type="number" name="produits[0][quantite]" class="form-control" min="1" placeholder="Ex: 100" required>
                            </div>
                            <div style="flex: 1.5;">
                                <label class="form-label" style="font-size: .7rem;">Prix U. Origine (Naira ₦)</label>
                                <input type="number" name="produits[0][prix_unitaire_origine]" class="form-control" min="0" placeholder="Ex: 5000" required>
                            </div>
                            <div>
                                <button type="button" class="btn btn-danger btn-sm remove-row-btn" style="padding: 9px 12px;"><i class="bi bi-trash"></i></button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary btn-sm" id="add-row-btn" style="margin-top: 10px;">
                        <i class="bi bi-plus-circle"></i> Ajouter un article
                    </button>
                </div>
            </div>
        </div>

        {{-- Section Droite --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">

            {{-- Carte : Attribution des fournisseurs --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-person-lines-fill"></i> Fournisseurs par produit</h3>
                </div>
                <div class="card-body">
                    <p style="font-size: .8rem; color: var(--text-muted); margin-bottom: 12px;">
                        1. Sélectionnez une ou plusieurs lignes produit (cliquez dessus).<br>
                        2. Choisissez un fournisseur ci-dessous.<br>
                        3. Cliquez sur <strong>Attribuer</strong>.
                    </p>

                    <div class="assign-bar">
                        <div class="autocomplete-wrap">
                            <input type="text" class="form-control assign-fourn-search" placeholder="Fournisseur..." autocomplete="off">
                            <input type="hidden" class="assign-fourn-id" value="">
                            <div class="autocomplete-dropdown assign-fourn-dropdown"></div>
                        </div>
                        <button type="button" class="btn btn-primary" id="btn-assign-fourn" style="white-space: nowrap; padding: 8px 16px;">
                            <i class="bi bi-check-lg"></i> Attribuer
                        </button>
                        <button type="button" class="btn btn-primary btn-new-fourn-assign" title="Créer un fournisseur" style="padding: 8px 14px;">
                            <i class="bi bi-plus"></i>
                        </button>
                    </div>

                    <div class="supplier-legend" id="supplier-legend"></div>
                </div>
            </div>

            {{-- Carte : Taux & Logistique --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-calculator"></i> Taux & Logistique</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Magasin de réception</label>
                        <div style="display: flex; gap: 6px; align-items: center;">
                            <select name="magasin_id" id="magasin-select" class="form-control" required style="flex: 1;">
                                @foreach($magasins as $m)
                                    <option value="{{ $m->id }}">{{ $m->nom }}</option>
                                @endforeach
                            </select>
                            <button type="button" class="btn btn-primary" id="btn-new-magasin" title="Créer un dépôt" style="white-space: nowrap; padding: 8px 16px;">
                                <i class="bi bi-plus"></i> Dépôt
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Taux Naira -> FCFA (ex: Taux marché)</label>
                        <input type="number" name="taux_change_naira_cfa" id="taux-input" class="form-control" value="0.65" step="0.0001" min="0.0001" required>
                        <div id="taux-resultat" style="font-size: .8rem; color: var(--primary); font-weight: 600; margin-top: 4px;">1 000 ₦ = 650 FCFA</div>
                        <small style="color: var(--text-muted); font-size: .7rem; display: block; margin-top: 2px;">Exemple : Taux de 0.65 signifie que 1000 ₦ = 650 FCFA.</small>
                    </div>
                </div>
            </div>

            {{-- Carte : Frais de Transport --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-cash-stack"></i> Frais de Transport (FCFA)</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label class="form-label">Frais de Transport</label>
                        <input type="number" name="frais_transport_cfa" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Douanes / Route</label>
                        <input type="number" name="frais_douane_cfa" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Manutention / Chargement</label>
                        <input type="number" name="frais_manutention_cfa" class="form-control" value="0" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Autres frais / Taxes</label>
                        <input type="number" name="autres_frais_cfa" class="form-control" value="0" min="0" required>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%; justify-content: center; margin-top: 10px;">
                        <i class="bi bi-save"></i> Enregistrer l'Arrivage
                    </button>
                    
                    <a href="{{ route('arrivages.index') }}" class="btn btn-secondary btn-sm" style="width: 100%; justify-content: center; margin-top: 8px;">
                        Annuler
                    </a>
                </div>
            </div>
        </div>

    </div>
</form>

<!-- Modal Créer Fournisseur -->
<div class="modal-overlay" id="fourn-modal">
    <div class="modal-box">
        <h4><i class="bi bi-building-add"></i> Nouveau Fournisseur</h4>
        <form id="fourn-form">
            <div class="form-group">
                <label class="form-label">Nom *</label>
                <input type="text" id="fourn-nom" class="form-control" required>
            </div>
            <div class="form-row form-row-2" style="margin-top: 12px;">
                <div class="form-group">
                    <label class="form-label">Pays</label>
                    <input type="text" id="fourn-pays" class="form-control" value="Nigeria" placeholder="Nigeria">
                </div>
                <div class="form-group">
                    <label class="form-label">Ville</label>
                    <input type="text" id="fourn-ville" class="form-control" placeholder="Lagos">
                </div>
            </div>
            <div class="form-row form-row-2" style="margin-top: 12px;">
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" id="fourn-tel" class="form-control" placeholder="+234...">
                </div>
                <div class="form-group">
                    <label class="form-label">Devise</label>
                    <select id="fourn-devise" class="form-control">
                        <option value="NGN">NGN (Naira)</option>
                        <option value="XOF">XOF (FCFA)</option>
                        <option value="USD">USD (Dollar)</option>
                        <option value="EUR">EUR (Euro)</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" id="fourn-modal-close">Annuler</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Créer</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Créer Dépôt -->
<div class="modal-overlay" id="magasin-modal">
    <div class="modal-box">
        <h4><i class="bi bi-shop"></i> Nouveau Dépôt / Magasin</h4>
        <form id="magasin-form">
            <div class="form-group">
                <label class="form-label">Nom *</label>
                <input type="text" id="magasin-nom" class="form-control" placeholder="Ex: Dépôt Zone Industrielle" required>
            </div>
            <div class="form-row form-row-2" style="margin-top: 12px;">
                <div class="form-group">
                    <label class="form-label">Adresse</label>
                    <input type="text" id="magasin-adresse" class="form-control" placeholder="Ex: Rue X, Quartier Y">
                </div>
                <div class="form-group">
                    <label class="form-label">Ville</label>
                    <input type="text" id="magasin-ville" class="form-control" placeholder="Cotonou">
                </div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" id="magasin-modal-close">Annuler</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Créer</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const produitsData = @json($produitsJson);
        const fournisseursData = @json($fournisseursJson);

        let rowCount = 1;
        const container = document.getElementById('produits-container');
        const addBtn = document.getElementById('add-row-btn');

        // ─── Couleurs pour les fournisseurs ───────────────────────────
        const FOURN_COLORS = ['#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899', '#14b8a6', '#f97316'];
        const supplierColorMap = {}; // id -> color
        let colorIndex = 0;

        function getColor(supplierId) {
            if (!supplierColorMap[supplierId]) {
                supplierColorMap[supplierId] = FOURN_COLORS[colorIndex % FOURN_COLORS.length];
                colorIndex++;
            }
            return supplierColorMap[supplierId];
        }

        // ─── Modal Fournisseur ────────────────────────────────────────
        const modal = document.getElementById('fourn-modal');
        const modalClose = document.getElementById('fourn-modal-close');
        const fournForm = document.getElementById('fourn-form');
        let pendingCallback = null;

        function openModal(callback) {
            pendingCallback = callback;
            document.getElementById('fourn-nom').value = '';
            document.getElementById('fourn-pays').value = 'Nigeria';
            document.getElementById('fourn-ville').value = '';
            document.getElementById('fourn-tel').value = '';
            document.getElementById('fourn-devise').value = 'NGN';
            modal.classList.add('open');
            setTimeout(() => document.getElementById('fourn-nom').focus(), 100);
        }

        function closeModal() {
            modal.classList.remove('open');
            pendingCallback = null;
        }

        modalClose.addEventListener('click', closeModal);
        modal.addEventListener('click', function(e) {
            if (e.target === modal) closeModal();
        });

        fournForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const nom = document.getElementById('fourn-nom').value.trim();
            if (!nom) { alert('Le nom est obligatoire.'); return; }

            fetch('{{ route("fournisseurs.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    nom: nom,
                    pays: document.getElementById('fourn-pays').value.trim(),
                    ville: document.getElementById('fourn-ville').value.trim(),
                    telephone: document.getElementById('fourn-tel').value.trim(),
                    devise: document.getElementById('fourn-devise').value,
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.id) {
                    fournisseursData.push({ id: data.id, nom: data.nom, ville: data.ville, pays: data.pays });
                    if (pendingCallback) pendingCallback(data);
                    closeModal();
                } else {
                    alert(data.error || 'Erreur lors de la création.');
                }
            })
            .catch(() => alert('Erreur réseau.'));
        });

        // ─── Modal Dépôt ──────────────────────────────────────────────
        const magasinModal = document.getElementById('magasin-modal');
        const magasinModalClose = document.getElementById('magasin-modal-close');
        const magasinForm = document.getElementById('magasin-form');
        const magasinSelect = document.getElementById('magasin-select');

        document.getElementById('btn-new-magasin').addEventListener('click', function() {
            document.getElementById('magasin-nom').value = '';
            document.getElementById('magasin-adresse').value = '';
            document.getElementById('magasin-ville').value = '';
            magasinModal.classList.add('open');
            setTimeout(() => document.getElementById('magasin-nom').focus(), 100);
        });

        magasinModalClose.addEventListener('click', () => magasinModal.classList.remove('open'));
        magasinModal.addEventListener('click', function(e) {
            if (e.target === magasinModal) magasinModal.classList.remove('open');
        });

        magasinForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const nom = document.getElementById('magasin-nom').value.trim();
            if (!nom) { alert('Le nom est obligatoire.'); return; }

            fetch('{{ route("magasins.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({
                    nom: nom,
                    adresse: document.getElementById('magasin-adresse').value.trim(),
                    ville: document.getElementById('magasin-ville').value.trim(),
                })
            })
            .then(r => r.json())
            .then(data => {
                if (data.id) {
                    const opt = document.createElement('option');
                    opt.value = data.id;
                    opt.textContent = data.nom;
                    magasinSelect.appendChild(opt);
                    magasinSelect.value = data.id;
                    magasinModal.classList.remove('open');
                } else {
                    alert(data.error || 'Erreur lors de la création.');
                }
            })
            .catch(() => alert('Erreur réseau.'));
        });

        // ─── Calcul taux en direct ────────────────────────────────────
        const tauxInput = document.getElementById('taux-input');
        const tauxResultat = document.getElementById('taux-resultat');
        function updateTaux() {
            const val = parseFloat(tauxInput.value) || 0;
            tauxResultat.textContent = val > 0
                ? `1 000 ₦ = ${(1000 * val).toLocaleString('fr-FR', {minimumFractionDigits: 0, maximumFractionDigits: 0})} FCFA`
                : 'Entrez un taux pour voir la conversion';
        }
        tauxInput.addEventListener('input', updateTaux);
        updateTaux();

        // ─── Autocomplete Produit ──────────────────────────────────────
        function initAutocomplete(input) {
            const row = input.closest('.produit-row');
            const dropdown = row.querySelector('.autocomplete-dropdown');
            const hiddenId = row.querySelector('.produit-id');

            function filterProduits(query) {
                const q = query.toLowerCase().trim();
                if (!q) return [];
                return produitsData.filter(p => p.nom.toLowerCase().includes(q));
            }

            function renderDropdown(results, query) {
                dropdown.innerHTML = '';
                if (results.length === 0) {
                    if (query.trim()) {
                        dropdown.innerHTML = '<div class="autocomplete-item no-result">Aucun produit trouvé</div>';
                        dropdown.classList.add('show');
                    } else {
                        dropdown.classList.remove('show');
                    }
                    return;
                }
                results.forEach((p, idx) => {
                    const item = document.createElement('div');
                    item.className = 'autocomplete-item' + (idx === 0 ? ' active' : '');
                    item.innerHTML = `<span>${p.nom}</span><span class="price-badge">${p.prix ?? '—'} FCFA</span>`;
                    item.dataset.id = p.id;
                    item.addEventListener('click', function() { selectProduct(this, row, input, dropdown, hiddenId); });
                    dropdown.appendChild(item);
                });
                dropdown.classList.add('show');
            }

            input.addEventListener('input', function() {
                renderDropdown(filterProduits(this.value), this.value);
                if (hiddenId.value) hiddenId.value = '';
            });

            input.addEventListener('keydown', function(e) {
                const items = dropdown.querySelectorAll('.autocomplete-item:not(.no-result)');
                const active = dropdown.querySelector('.autocomplete-item.active');
                let idx = Array.from(items).indexOf(active);
                if (e.key === 'ArrowDown') { e.preventDefault(); if (idx < items.length - 1) { if (active) active.classList.remove('active'); items[idx + 1].classList.add('active'); } }
                else if (e.key === 'ArrowUp') { e.preventDefault(); if (idx > 0) { if (active) active.classList.remove('active'); items[idx - 1].classList.add('active'); } }
                else if (e.key === 'Enter') { e.preventDefault(); if (active) { selectProduct(active, row, input, dropdown, hiddenId); } else if (items.length > 0) { selectProduct(items[0], row, input, dropdown, hiddenId); } }
                else if (e.key === 'Escape') { dropdown.classList.remove('show'); }
            });

            document.addEventListener('click', function(e) {
                if (!input.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.remove('show');
            });
        }

        function selectProduct(item, row, input, dropdown, hiddenId) {
            input.value = item.querySelector('span').innerText;
            hiddenId.value = item.dataset.id;
            dropdown.classList.remove('show');
        }

        // ─── Sélection de lignes produit (click) ──────────────────────
        container.addEventListener('click', function(e) {
            const row = e.target.closest('.produit-row');
            if (!row) return;
            if (e.target.closest('.autocomplete-dropdown') || e.target.closest('.remove-row-btn')) return;
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') return;
            row.classList.toggle('selected');
        });

        function getSelectedRows() {
            return container.querySelectorAll('.produit-row.selected');
        }

        // ─── Autocomplete Fournisseur (panneau d'attribution) ─────────
        const assignInput = document.querySelector('.assign-fourn-search');
        const assignDropdown = document.querySelector('.assign-fourn-dropdown');
        const assignHidden = document.querySelector('.assign-fourn-id');
        const btnAssign = document.getElementById('btn-assign-fourn');
        const legend = document.getElementById('supplier-legend');

        function initAssignFourn() {
            function filter(query) {
                const q = query.toLowerCase().trim();
                if (!q) return [];
                return fournisseursData.filter(f => f.nom.toLowerCase().includes(q));
            }

            function render(results, query) {
                assignDropdown.innerHTML = '';
                if (results.length === 0) {
                    if (query.trim()) {
                        assignDropdown.innerHTML = '<div class="autocomplete-item no-result">Aucun fournisseur</div>';
                        assignDropdown.classList.add('show');
                    } else {
                        assignDropdown.classList.remove('show');
                    }
                    return;
                }
                results.forEach((f, idx) => {
                    const item = document.createElement('div');
                    item.className = 'autocomplete-item' + (idx === 0 ? ' active' : '');
                    item.innerHTML = `<span>${f.nom}</span><span class="fourn-badge">${f.ville ?? f.pays ?? ''}</span>`;
                    item.dataset.id = f.id;
                    item.addEventListener('click', function() {
                        assignInput.value = f.nom;
                        assignHidden.value = f.id;
                        assignDropdown.classList.remove('show');
                    });
                    assignDropdown.appendChild(item);
                });
                assignDropdown.classList.add('show');
            }

            assignInput.addEventListener('input', function() {
                render(filter(this.value), this.value);
                if (assignHidden.value) assignHidden.value = '';
            });

            assignInput.addEventListener('keydown', function(e) {
                const items = assignDropdown.querySelectorAll('.autocomplete-item:not(.no-result)');
                const active = assignDropdown.querySelector('.autocomplete-item.active');
                let idx = Array.from(items).indexOf(active);
                if (e.key === 'ArrowDown') { e.preventDefault(); if (idx < items.length - 1) { if (active) active.classList.remove('active'); items[idx + 1].classList.add('active'); } }
                else if (e.key === 'ArrowUp') { e.preventDefault(); if (idx > 0) { if (active) active.classList.remove('active'); items[idx - 1].classList.add('active'); } }
                else if (e.key === 'Enter') { e.preventDefault(); if (active) { assignInput.value = active.querySelector('span').innerText; assignHidden.value = active.dataset.id; assignDropdown.classList.remove('show'); } else if (items.length > 0) { assignInput.value = items[0].querySelector('span').innerText; assignHidden.value = items[0].dataset.id; assignDropdown.classList.remove('show'); } }
                else if (e.key === 'Escape') { assignDropdown.classList.remove('show'); }
            });

            document.addEventListener('click', function(e) {
                if (!assignInput.contains(e.target) && !assignDropdown.contains(e.target)) assignDropdown.classList.remove('show');
            });
        }
        initAssignFourn();

        // ─── Bouton Créer fournisseur (depuis le panneau) ─────────────
        document.querySelector('.btn-new-fourn-assign').addEventListener('click', function() {
            openModal(function(data) {
                assignInput.value = data.nom;
                assignHidden.value = data.id;
            });
        });

        // ─── Attribuer le fournisseur aux lignes sélectionnées ────────
        btnAssign.addEventListener('click', function() {
            const fournId = assignHidden.value;
            const fournName = assignInput.value.trim();
            if (!fournId || !fournName) { alert('Veuillez choisir un fournisseur.'); return; }

            const selected = getSelectedRows();
            if (selected.length === 0) { alert('Veuillez sélectionner au moins une ligne produit (cliquez dessus).'); return; }

            const color = getColor(fournId);

            selected.forEach(row => {
                row.querySelector('.fourn-id').value = fournId;
                row.style.borderLeft = `4px solid ${color}`;
                row.classList.remove('selected');

                let tag = row.querySelector('.fourn-tag');
                if (!tag) {
                    const label = row.querySelector('.form-label');
                    if (label) {
                        tag = document.createElement('span');
                        tag.className = 'fourn-tag';
                        tag.style.background = color + '22';
                        tag.style.color = color;
                        label.after(tag);
                    }
                }
                if (tag) {
                    tag.innerHTML = `<span style="display:inline-block;width:8px;height:8px;border-radius:50%;background:${color};"></span> ${fournName}`;
                }
            });

            updateLegend();
            assignInput.value = '';
            assignHidden.value = '';
        });

        // ─── Légende des fournisseurs attribués ───────────────────────
        function updateLegend() {
            const assigned = {};
            container.querySelectorAll('.produit-row').forEach(row => {
                const id = row.querySelector('.fourn-id').value;
                if (id) assigned[id] = (assigned[id] || 0) + 1;
            });

            legend.innerHTML = '';
            Object.keys(assigned).forEach(id => {
                const f = fournisseursData.find(x => String(x.id) === id);
                if (!f) return;
                const color = getColor(id);
                const div = document.createElement('div');
                div.className = 'supplier-legend-item';
                div.innerHTML = `<span class="color-dot" style="background:${color}"></span> ${f.nom} <span style="color:var(--text-muted);font-size:.75rem;">(${assigned[id]} prod.)</span>`;
                legend.appendChild(div);
            });
        }

        // ─── Initialisation première ligne ────────────────────────────
        initAutocomplete(container.querySelector('.produit-search'));

        // ─── Ajouter une ligne ────────────────────────────────────────
        addBtn.addEventListener('click', function() {
            const idx = rowCount;
            const template = `
                <div class="produit-row flex-row-mobile" style="margin-bottom: 12px; border-bottom: 1px dashed var(--border); padding-bottom: 12px;">
                    <div style="flex: 2;">
                        <div class="autocomplete-wrap">
                            <input type="text" class="form-control produit-search" placeholder="Tapez le nom du produit..." data-row="${idx}" autocomplete="off" required>
                            <input type="hidden" name="produits[${idx}][produit_id]" class="produit-id">
                            <input type="hidden" name="produits[${idx}][fournisseur_id]" class="fourn-id" value="">
                            <div class="autocomplete-dropdown" data-row="${idx}"></div>
                        </div>
                    </div>
                    <div style="flex: 1;">
                        <input type="number" name="produits[${idx}][quantite]" class="form-control" min="1" placeholder="Ex: 100" required>
                    </div>
                    <div style="flex: 1.5;">
                        <input type="number" name="produits[${idx}][prix_unitaire_origine]" class="form-control" min="0" placeholder="Ex: 5000" required>
                    </div>
                    <div>
                        <button type="button" class="btn btn-danger btn-sm remove-row-btn" style="padding: 9px 12px;"><i class="bi bi-trash"></i></button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', template);
            initAutocomplete(container.querySelector(`.produit-search[data-row="${idx}"]`));
            rowCount++;
        });

        // ─── Supprimer une ligne ──────────────────────────────────────
        container.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-row-btn') || e.target.closest('.remove-row-btn')) {
                const row = e.target.closest('.produit-row');
                if (container.querySelectorAll('.produit-row').length > 1) {
                    row.remove();
                    updateLegend();
                } else {
                    alert('L\'arrivage doit contenir au moins un produit.');
                }
            }
        });

        // ─── Validation ───────────────────────────────────────────────
        document.getElementById('arrivageForm').addEventListener('submit', function(e) {
            const rows = container.querySelectorAll('.produit-row');
            let valid = true;
            rows.forEach(row => {
                if (!row.querySelector('.produit-id').value) {
                    valid = false;
                    row.querySelector('.produit-search').style.borderColor = 'var(--danger)';
                }
            });
            if (!valid) {
                e.preventDefault();
                alert('Veuillez sélectionner un produit pour chaque ligne.');
            }
        });
    });
</script>
@endpush
@endsection