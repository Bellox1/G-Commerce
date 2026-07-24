@extends('layouts.app')
@section('title', 'Nouvelle Vente')
@section('page-title', 'Nouvelle Vente')

@push('styles')
<style>
    .autocomplete-wrap { position: relative; width: 100%; }
    .autocomplete-wrap input { width: 100%; }
    .autocomplete-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid var(--border); border-radius: 0 0 6px 6px; max-height: 220px; overflow-y: auto; z-index: 999; display: none; box-shadow: var(--shadow-card); }
    .autocomplete-dropdown.show { display: block; }
    .autocomplete-item { padding: 8px 14px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: .85rem; border-bottom: 1px solid var(--border); }
    .autocomplete-item:last-child { border-bottom: none; }
    .autocomplete-item:hover, .autocomplete-item.active { background: #f0fdf4; }
    .autocomplete-item .stock-badge { font-size: .7rem; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
    .autocomplete-item .stock-ok { background: #dcfce7; color: #166534; }
    .autocomplete-item .stock-low { background: #fef3c7; color: #b45309; }

    .vente-card { border: 1px solid var(--border); border-radius: 8px; background: #fff; margin-bottom: 16px; overflow: hidden; }
    .vente-card-header { display: flex; justify-content: space-between; align-items: center; padding: 14px 20px; background: #f8fafc; border-bottom: 2px solid var(--primary); }
    .vente-card-header h5 { margin: 0; font-size: .95rem; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 8px; }
    .vente-card-body { padding: 20px; }

    .total-badge { background: #f1f5f9; padding: 4px 14px; border-radius: 20px; font-size: .85rem; font-weight: 700; color: var(--primary); }
    .btn-remove-vente { color: var(--danger); background: none; border: none; cursor: pointer; font-size: 1.3rem; padding: 4px; line-height: 1; }
    .btn-remove-vente:hover { opacity: .7; }
    .form-label-sm { font-size: .75rem; font-weight: 600; color: #64748b; margin-bottom: 4px; display: block; }
    .checkbox-credit { display: flex; align-items: center; gap: 8px; padding: 8px 14px; background: #fef9c3; border: 1px solid #facc15; border-radius: 6px; cursor: pointer; }
    .checkbox-credit input { width: 18px; height: 18px; accent-color: var(--danger); cursor: pointer; }
    .checkbox-credit label { font-size: .85rem; font-weight: 700; color: #854d0e; cursor: pointer; margin: 0; }
    .alert-credit { background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; padding: 10px 14px; display: none; margin-top: 8px; font-size: .8rem; color: #991b1b; }
    .alert-credit.show { display: flex; align-items: center; gap: 8px; }
    .qte-overstock { border-color: #dc2626 !important; background: #fef2f2; }
    .input-invalid { border-color: #dc2626 !important; background: #fff5f5; }
    .paye-over { border-color: #dc2626 !important; background: #fef2f2; }
    .input-error-msg { display: none; font-size: .7rem; color: #dc2626; margin-top: 2px; }
    .input-error-msg.show { display: flex; align-items: center; gap: 4px; }

    @media (max-width: 640px) {
        .vente-card-body { padding: 12px; }
        .vente-card-header { padding: 10px 12px; }
        .vente-card-header h5 { font-size: .85rem; }
        .client-paiement-grid { grid-template-columns: 1fr !important; }
        .remis-row { display: grid; gap: 12px; grid-template-columns: 1fr 1fr; margin-bottom: 8px; }
        .du-display { padding: 8px 12px; background: #fef3c7; border: 1px solid #f59e0b; border-radius: 6px; font-size: .9rem; font-weight: 700; color: #92400e; min-height: 38px; display: flex; align-items: center; }
        .du-display.zero { background: transparent; border-color: transparent; color: var(--text-muted); font-weight: 400; }
        .ligne-row { flex-wrap: wrap; gap: 6px !important; }
        .ligne-row > div { flex: 1 1 calc(50% - 6px) !important; min-width: 0 !important; }
        .ligne-row > div:first-child { flex: 1 1 100% !important; }
        .ligne-row .remove-row-btn { padding: 6px 8px !important; }
        .ligne-row .btn-remove { flex: 0 0 auto !important; }
        .total-badge { font-size: .75rem; padding: 2px 10px; }
    }
</style>
@endpush

@section('content')
{{-- Action rapide : Nouveau client --}}
<div style="display: flex; justify-content: flex-end; margin-bottom: 16px;">
    <a href="{{ route('clients.create') }}" class="btn btn-success" target="_blank">
        <i class="bi bi-person-plus-fill"></i> Nouveau client
    </a>
</div>

<form method="POST" action="{{ route('ventes.store') }}" id="venteForm">
    @csrf
    <input type="hidden" name="magasin_id" value="{{ $selectedMagasin->id }}">

    <div id="ventes-container">
        {{-- Première vente --}}
        <div class="vente-card" data-index="0">
            <div class="vente-card-header">
                <h5><i class="bi bi-person-badge"></i> Client <span class="client-label">1</span></h5>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <span class="total-badge" id="totaux-0">0 FCFA</span>
                </div>
            </div>
            <div class="vente-card-body">
                {{-- Client & Paiement --}}
                <div class="client-paiement-grid" style="display: grid; gap: 12px; grid-template-columns: 1fr 1fr; align-items: end; margin-bottom: 8px;">
                    <div>
                        <label class="form-label-sm">Client</label>
                        <div class="autocomplete-wrap">
                            <input type="text" class="form-control client-search" placeholder="Client..." autocomplete="off">
                            <input type="hidden" name="ventes[0][client_id]" class="client-id" value="">
                            <div class="autocomplete-dropdown"></div>
                        </div>
                        
                    </div>
                    <div class="paye-col" id="payeCol-0" style="display:none;">
                        <label class="form-label-sm">Montant payé (FCFA)</label>
                        <input type="number" name="ventes[0][montant_paye]" class="form-control paye-input" value="0" min="0" readonly>
                        <span class="input-error-msg paye-error"><i class="bi bi-exclamation-circle"></i> <span class="paye-error-text"></span></span>
                    </div>
                </div>
                <div class="remis-row" id="remisRow-0">
                    <div>
                        <label class="form-label-sm">Montant remis (FCFA)</label>
                        <input type="number" name="ventes[0][montant_remis]" class="form-control remis-input" placeholder="Montant remis" min="0">
                    </div>
                    <div>
                        <label class="form-label-sm">Du client (FCFA)</label>
                        <div class="du-display zero" id="du-0">0 FCFA</div>
                    </div>
                </div>
                <div style="margin-bottom: 16px;">
                    <div class="checkbox-credit" style="margin-top: 6px;">
                        <input type="checkbox" name="ventes[0][a_credit]" value="1" class="credit-checkbox" id="credit-0">
                        <label for="credit-0">À crédit</label>
                    </div>
                </div>
                <div class="alert-credit" id="creditAlert-0">
                    <i class="bi bi-info-circle"></i>
                    <span>Cette vente sera enregistrée comme une dette.</span>
                </div>

                <div class="echeance-row" id="echeanceRow-0" style="display:none; margin-bottom:12px;">
                    <div class="form-group">
                        <label class="form-label-sm">Date de règlement souhaitée</label>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <select name="ventes[0][echeance_option]" class="form-control echeance-select" style="flex:1; min-width:140px;" data-vente="0">
                                <option value="">-- Optionnelle --</option>
                                <option value="today">Aujourd'hui</option>
                                <option value="tomorrow">Demain</option>
                                <option value="after_tomorrow">Après-demain</option>
                                <option value="6_days">Dans 6 jours (1 semaine)</option>
                                <option value="2_weeks">Dans 2 semaines</option>
                                <option value="1_month">Dans 1 mois</option>
                                <option value="custom">Personnalisé...</option>
                            </select>
                            <input type="date" name="ventes[0][date_echeance_custom]" class="form-control echeance-custom" style="display:none; min-width:140px;">
                            <input type="hidden" name="ventes[0][date_echeance]" class="echeance-value">
                        </div>
                    </div>
                </div>

                {{-- Articles --}}
                <div class="lignes-container" data-vente="0">
                    <div class="ligne-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: flex-end; flex-wrap: wrap;">
                        <div style="flex: 2; min-width: 180px;">
                            <label class="form-label-sm" style="font-size: 0.75rem; color: var(--text-muted);">Produit</label>
                            <div class="autocomplete-wrap">
                                <input type="text" class="form-control produit-search" placeholder="Rechercher un produit..." autocomplete="off" required>
                                <input type="hidden" name="ventes[0][lignes][0][produit_id]" class="produit-id">
                                <div class="autocomplete-dropdown"></div>
                            </div>
                        </div>
                        
                        <!-- Carton -->
                        <div style="flex: 0.8; min-width: 100px; display: flex; gap: 4px;">
                            <div style="flex: 1;">
                                <label class="form-label-sm" style="font-size: 0.75rem; color: var(--text-muted);">Qté Ctn</label>
                                <input type="number" name="ventes[0][lignes][0][quantite]" class="form-control qte-input" placeholder="Cartons" min="0" value="0">
                            </div>
                            <div style="flex: 1.5;">
                                <label class="form-label-sm" style="font-size: 0.75rem; color: var(--text-muted);">Prix Ctn</label>
                                <input type="number" name="ventes[0][lignes][0][prix_vente]" class="form-control prix-input" placeholder="Prix" min="0">
                            </div>
                        </div>

                        <!-- Cartouche -->
                        <div class="cartouche-col" style="flex: 1.2; min-width: 140px; display: none; gap: 4px;">
                            <div style="flex: 1;">
                                <label class="form-label-sm" style="font-size: 0.75rem; color: var(--text-muted);">Qté Cart.</label>
                                <input type="number" name="ventes[0][lignes][0][quantite_cartouche]" class="form-control qte-cartouche-input" placeholder="Cartouches" min="0" value="0">
                            </div>
                            <div style="flex: 1.5;">
                                <label class="form-label-sm" style="font-size: 0.75rem; color: var(--text-muted);">Prix Cart.</label>
                                <input type="number" name="ventes[0][lignes][0][prix_cartouche]" class="form-control prix-cartouche-input" placeholder="Prix" min="0">
                            </div>
                        </div>

                        <div style="flex: 0.6; min-width: 80px;">
                            <label class="form-label-sm" style="font-size: 0.75rem; color: var(--text-muted);">Total</label>
                            <input type="text" class="form-control stotal-input" style="background: #f8fafc; font-weight: 700;" readonly value="0">
                            <span class="input-error-msg qte-error" style="position: absolute; margin-top: 4px;"><i class="bi bi-exclamation-circle"></i> Stock: <span class="qte-error-text"></span> ctn</span>
                        </div>
                        <div style="display: flex; align-items: flex-end; padding-bottom: 2px;">
                            <button type="button" class="btn btn-danger btn-sm remove-row-btn" style="padding: 8px 10px;"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="reste-stock-info" style="display:none; font-size: 0.78rem; margin-top: 4px; margin-bottom: 6px; padding: 4px 8px; background: #f8fafc; border-radius: 6px; border: 1px solid var(--border);"></div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm add-row-btn" style="margin-top: 2px;">
                    <i class="bi bi-plus-circle"></i> Article
                </button>

                <div style="margin-top: 16px; padding-top: 12px; border-top: 1px solid var(--border); text-align: right;">
                    <button type="button" class="btn btn-sm btn-outline-primary save-card-btn">
                        <i class="bi bi-floppy"></i> Sauvegarder cette vente
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div style="display: flex; gap: 10px; margin-top: 4px; flex-wrap: wrap;">
        <button type="button" class="btn btn-secondary" id="addVenteBtn">
            <i class="bi bi-person-plus"></i> Nouvelle vente
        </button>
    </div>
    

    <input type="hidden" name="save_one" id="save_one" value="">

    <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
        <a href="{{ route('ventes.index') }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary" id="saveAllBtn">
            <i class="bi bi-receipt-cutoff"></i> Tout enregistrer
        </button>
    </div>
</form>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const produitsData = @json($produitsJson);
    const clientsData = @json($clientsJson);
    const createClientUrl = '{{ route('clients.create') }}';
    let venteCount = 1;
    const container = document.getElementById('ventes-container');

    // ── Client autocomplete (full list on focus, filter on type) ──
    function initClientAutocomplete(input) {
        const wrap = input.closest('.autocomplete-wrap');
        const dropdown = wrap.querySelector('.autocomplete-dropdown');
        const hiddenId = wrap.querySelector('.client-id');

        function filterClients(q) {
            q = q.toLowerCase().trim();
            if (!q) return clientsData;
            return clientsData.filter(c =>
                c.nom.toLowerCase().includes(q) ||
                c.telephone.toLowerCase().includes(q)
            );
        }

        function renderDropdown(results) {
            dropdown.innerHTML = '';
            if (!results.length) {
                dropdown.innerHTML = '<div class="autocomplete-item" style="color:var(--text-muted);cursor:default;justify-content:center;font-size:.8rem;">Aucun client trouvé</div>';
                dropdown.classList.add('show');
                return;
            }
            results.forEach((c, i) => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item' + (i === 0 ? ' active' : '');
                item.innerHTML = `<span>${c.nom}</span><span style="color:#94a3b8;font-size:.8rem;">${c.telephone}</span>`;
                item.dataset.id = c.id;
                item.addEventListener('click', () => {
                    hiddenId.value = c.id;
                    input.value = c.nom + ' (' + c.telephone + ')';
                    dropdown.classList.remove('show');
                });
                dropdown.appendChild(item);
            });
            dropdown.classList.add('show');
        }

        input.addEventListener('focus', function() {
            renderDropdown(filterClients(this.value));
        });

        input.addEventListener('input', function() {
            renderDropdown(filterClients(this.value));
            if (!this.value.trim()) hiddenId.value = '';
        });

        input.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.autocomplete-item:not([style*="cursor:default"])');
            const active = dropdown.querySelector('.autocomplete-item.active');
            let idx = Array.from(items).indexOf(active);
            if (e.key === 'ArrowDown') { e.preventDefault(); if (idx < items.length - 1) { active?.classList.remove('active'); items[idx + 1].classList.add('active'); } }
            if (e.key === 'ArrowUp') { e.preventDefault(); if (idx > 0) { active?.classList.remove('active'); items[idx - 1].classList.add('active'); } }
            if (e.key === 'Enter') { e.preventDefault(); const target = active || items[0]; if (target) target.click(); }
            if (e.key === 'Escape') dropdown.classList.remove('show');
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.remove('show');
        });
    }

    // ── Product autocomplete ──
    function initAutocomplete(input) {
        const row = input.closest('.ligne-row');
        const dropdown = row.querySelector('.autocomplete-dropdown');
        const hiddenId = row.querySelector('.produit-id');
        const prixInput = row.querySelector('.prix-input');

        function filterProduits(q) {
            q = q.toLowerCase().trim();
            if (!q) return [];
            return produitsData.filter(p => p.nom.toLowerCase().includes(q));
        }

        function renderDropdown(results, query) {
            dropdown.innerHTML = '';
            if (!results.length) {
                dropdown.innerHTML = query.trim()
                    ? '<div class="autocomplete-item" style="color:var(--text-muted);cursor:default;justify-content:center;font-size:.8rem;">Aucun produit trouvé</div>'
                    : '';
                dropdown.classList.toggle('show', !!query.trim());
                return;
            }
            results.forEach((p, i) => {
                const item = document.createElement('div');
                item.className = 'autocomplete-item' + (i === 0 ? ' active' : '');
                item.innerHTML = `<span>${p.nom}</span><span class="stock-badge ${p.stock <= 5 ? 'stock-low' : 'stock-ok'}">Stock: ${p.stock}</span>`;
                item.dataset.id = p.id;
                item.addEventListener('click', () => {
                    hiddenId.value = p.id;
                    input.value = p.nom;
                    input.classList.remove('input-invalid');
                    prixInput.value = p.prix;
                    
                    row.dataset.stock = p.stock;
                    row.dataset.cartoucheParCarton = p.cartouche_par_carton || '1';

                    const cartoucheCol = row.querySelector('.cartouche-col');
                    const qteCartoucheInput = row.querySelector('.qte-cartouche-input');
                    const prixCartoucheInput = row.querySelector('.prix-cartouche-input');

                    if (p.a_cartouche && p.cartouche_par_carton) {
                        if (cartoucheCol) cartoucheCol.style.display = 'flex';
                        if (prixCartoucheInput) prixCartoucheInput.value = p.prix_cartouche || '';
                        if (qteCartoucheInput) qteCartoucheInput.value = 0;
                    } else {
                        if (cartoucheCol) cartoucheCol.style.display = 'none';
                        if (qteCartoucheInput) qteCartoucheInput.value = 0;
                        if (prixCartoucheInput) prixCartoucheInput.value = 0;
                        const qteInput = row.querySelector('.qte-input');
                        if (qteInput && (parseInt(qteInput.value) || 0) === 0) qteInput.value = 1;
                    }

                    dropdown.classList.remove('show');
                    recalcRow(row);
                    recalcVente(row.closest('.vente-card'));
                    validateQte(row);
                });
                dropdown.appendChild(item);
            });
            dropdown.classList.add('show');
        }

        input.addEventListener('input', function() {
            renderDropdown(filterProduits(this.value), this.value);
            // Si le texte change, on invalide la sélection précédente
            const selected = hiddenId.value;
            if (selected) {
                const match = produitsData.find(p => p.id == selected && p.nom.toLowerCase() === this.value.toLowerCase().trim());
                if (!match) {
                    hiddenId.value = '';
                    input.classList.add('input-invalid');
                }
            } else if (this.value.trim()) {
                input.classList.add('input-invalid');
            } else {
                input.classList.remove('input-invalid');
            }
        });

        input.addEventListener('blur', function() {
            // Si rien de valide sélectionné et du texte tapé, marquer rouge
            if (this.value.trim() && !hiddenId.value) {
                input.classList.add('input-invalid');
            }
        });

        // Quand on sélectionne un item, valider
        // Dans les item click listeners, ajouter: input.classList.remove('input-invalid');

        input.addEventListener('keydown', function(e) {
            const items = dropdown.querySelectorAll('.autocomplete-item:not([style*="cursor:default"])');
            const active = dropdown.querySelector('.autocomplete-item.active');
            let idx = Array.from(items).indexOf(active);
            if (e.key === 'ArrowDown') { e.preventDefault(); if (idx < items.length - 1) { active?.classList.remove('active'); items[idx + 1].classList.add('active'); } }
            if (e.key === 'ArrowUp') { e.preventDefault(); if (idx > 0) { active?.classList.remove('active'); items[idx - 1].classList.add('active'); } }
            if (e.key === 'Enter') { e.preventDefault(); const target = active || items[0]; if (target) target.click(); }
            if (e.key === 'Escape') dropdown.classList.remove('show');
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.remove('show');
        });
    }
    function validateQte(row) {
        const qteInput = row.querySelector('.qte-input');
        const qteCartoucheInput = row.querySelector('.qte-cartouche-input');
        const qte = parseInt(qteInput.value) || 0;
        const qteCartouche = parseInt(qteCartoucheInput?.value) || 0;
        const stock = parseInt(row.dataset.stock); // en cartons
        const cartoucheParCarton = parseInt(row.dataset.cartoucheParCarton) || 1;
        const hasCartouche = row.querySelector('.cartouche-col')?.style.display !== 'none';

        const cartonsNecessaires = qte + Math.ceil(qteCartouche / cartoucheParCarton);
        const errSpan = row.querySelector('.qte-error');
        const errText = row.querySelector('.qte-error-text');

        if (stock !== undefined && !isNaN(stock) && cartonsNecessaires > stock) {
            qteInput.classList.add('qte-overstock');
            if (qteCartoucheInput) qteCartoucheInput.classList.add('qte-overstock');
            if (errSpan && errText) {
                errText.textContent = stock;
                errSpan.classList.add('show');
            }
        } else {
            qteInput.classList.remove('qte-overstock');
            if (qteCartoucheInput) qteCartoucheInput.classList.remove('qte-overstock');
            if (errSpan) errSpan.classList.remove('show');
        }

        // ── Affichage du reste ──
        const resteInfo = row.querySelector('.reste-stock-info')
            || row.parentElement?.querySelector('.reste-stock-info')
            || (row.nextElementSibling?.classList.contains('reste-stock-info') ? row.nextElementSibling : null);
        if (resteInfo && !isNaN(stock)) {
            const cartonRestant = stock - cartonsNecessaires;

            // Cartouches restantes dans les cartons entamés uniquement
            // Si on vend N cartouches, on entame ceil(N/cpp) cartons.
            // Les cartouches qui restent dans ces cartons = ceil(N/cpp)*cpp - N
            const cartoucheResiduelle = qteCartouche > 0
                ? (Math.ceil(qteCartouche / cartoucheParCarton) * cartoucheParCarton - qteCartouche)
                : 0;

            if (cartonRestant < 0) {
                resteInfo.innerHTML = `<span style="color:var(--danger);font-weight:600;">⚠ Stock insuffisant</span>`;
            } else if (cartonRestant === 0 && !hasCartouche) {
                resteInfo.innerHTML = `<span style="color:var(--danger);font-weight:600;">⚠ Stock épuisé</span>`;
            } else {
                const couleurCtn = cartonRestant === 0 ? 'var(--danger)' : (cartonRestant < 5 ? '#f59e0b' : '#16a34a');
                let html = `<span style="color:${couleurCtn};font-weight:600;">Reste: ${cartonRestant} carton${cartonRestant > 1 ? 's' : ''}</span>`;

                if (hasCartouche) {
                    if (cartoucheResiduelle === 0) {
                        html += `&nbsp;·&nbsp;<span style="color:var(--text-muted);">Pas de cartouche restante</span>`;
                    } else {
                        html += `&nbsp;·&nbsp;<span style="color:#7c3aed;">${cartoucheResiduelle} cartouche${cartoucheResiduelle > 1 ? 's' : ''} restante${cartoucheResiduelle > 1 ? 's' : ''}</span>`;
                    }
                }
                resteInfo.innerHTML = html;
            }
            resteInfo.style.display = 'block';
        }
    }

    // ── Payment validation ──
    function validatePaye(card) {
        let total = 0;
        card.querySelectorAll('.ligne-row').forEach(row => {
            const qte = parseFloat(row.querySelector('.qte-input').value) || 0;
            const prix = parseFloat(row.querySelector('.prix-input').value) || 0;
            const qteCartouche = parseFloat(row.querySelector('.qte-cartouche-input')?.value) || 0;
            const prixCartouche = parseFloat(row.querySelector('.prix-cartouche-input')?.value) || 0;
            total += (qte * prix) + (qteCartouche * prixCartouche);
        });
        const payeInput = card.querySelector('.paye-input');
        const paye = parseFloat(payeInput.value) || 0;
        const errSpan = card.querySelector('.paye-error');
        const errText = card.querySelector('.paye-error-text');
        if (paye > total) {
            payeInput.classList.add('paye-over');
            if (errSpan && errText) {
                errText.textContent = 'Max: ' + total.toLocaleString('fr-FR') + ' FCFA';
                errSpan.classList.add('show');
            }
        } else {
            payeInput.classList.remove('paye-over');
            if (errSpan) errSpan.classList.remove('show');
        }
    }

    // ── Échéance ──
    function initEcheance(card) {
        const idx = card.dataset.index;
        const select = card.querySelector('.echeance-select');
        const customInput = card.querySelector('.echeance-custom');
        const hidden = card.querySelector('.echeance-value');
        if (!select) return;

        function computeDate(option, customVal) {
            const map = {
                'today': 0, 'tomorrow': 1, 'after_tomorrow': 2,
                '6_days': 6, '2_weeks': 14, '1_month': 30
            };
            if (option === 'custom') return customVal || '';
            if (map[option] !== undefined) {
                const d = new Date();
                d.setDate(d.getDate() + map[option]);
                return d.toISOString().split('T')[0];
            }
            return '';
        }

        select.addEventListener('change', function() {
            const isCustom = this.value === 'custom';
            customInput.style.display = isCustom ? 'block' : 'none';
            if (!isCustom) {
                hidden.value = computeDate(this.value, '');
            } else {
                hidden.value = customInput.value || '';
            }
        });

        customInput.addEventListener('change', function() {
            hidden.value = this.value;
        });
    }

    function recalcRow(row) {
        const qte = parseFloat(row.querySelector('.qte-input').value) || 0;
        const prix = parseFloat(row.querySelector('.prix-input').value) || 0;
        const qteCartouche = parseFloat(row.querySelector('.qte-cartouche-input')?.value) || 0;
        const prixCartouche = parseFloat(row.querySelector('.prix-cartouche-input')?.value) || 0;
        const t = (qte * prix) + (qteCartouche * prixCartouche);
        row.querySelector('.stotal-input').value = t.toLocaleString('fr-FR') + ' F';
    }

    function recalcVente(card) {
        let total = 0;
        card.querySelectorAll('.ligne-row').forEach(row => {
            const qte = parseFloat(row.querySelector('.qte-input').value) || 0;
            const prix = parseFloat(row.querySelector('.prix-input').value) || 0;
            const qteCartouche = parseFloat(row.querySelector('.qte-cartouche-input')?.value) || 0;
            const prixCartouche = parseFloat(row.querySelector('.prix-cartouche-input')?.value) || 0;
            total += (qte * prix) + (qteCartouche * prixCartouche);
        });
        const idx = card.dataset.index;
        const badge = document.getElementById('totaux-' + idx);
        if (badge) badge.textContent = total.toLocaleString('fr-FR') + ' FCFA';
        calcDu(card, total);
    }

    function calcDu(card, total) {
        const remisInput = card.querySelector('.remis-input');
        if (!remisInput) return;
        const idx = card.dataset.index;
        const duDisplay = document.getElementById('du-' + idx);
        if (!duDisplay) return;
        const remis = parseFloat(remisInput.value) || 0;
        if (remis > total) {
            const du = remis - total;
            duDisplay.textContent = du.toLocaleString('fr-FR') + ' FCFA';
            duDisplay.classList.remove('zero');
        } else {
            duDisplay.textContent = '0 FCFA';
            duDisplay.classList.add('zero');
        }
    }

    function toggleRemisVisibility(card) {
        const idx = card.dataset.index;
        const remisRow = document.getElementById('remisRow-' + idx);
        const payeCol = document.getElementById('payeCol-' + idx);
        if (!remisRow || !payeCol) return;
        const creditChecked = card.querySelector('.credit-checkbox').checked;
        remisRow.style.display = creditChecked ? 'none' : 'grid';
        payeCol.style.display = creditChecked ? 'block' : 'none';
    }

    function addLigne(venteCard) {
        const idx = parseInt(venteCard.dataset.index);
        const lignesContainer = venteCard.querySelector('.lignes-container');
        const rowCount = lignesContainer.querySelectorAll('.ligne-row').length;
        const wrapper = document.createElement('div');
        const tmpl = document.createElement('div');
        tmpl.className = 'ligne-row';
        tmpl.style.cssText = 'display: flex; gap: 10px; margin-bottom: 4px; align-items: flex-end; flex-wrap: wrap;';
        tmpl.innerHTML = `
            <div style="flex: 2; min-width: 180px;">
                <div class="autocomplete-wrap">
                    <input type="text" class="form-control produit-search" placeholder="Rechercher un produit..." autocomplete="off" required>
                    <input type="hidden" name="ventes[${idx}][lignes][${rowCount}][produit_id]" class="produit-id">
                    <div class="autocomplete-dropdown"></div>
                </div>
            </div>
            <!-- Carton -->
            <div style="flex: 0.8; min-width: 100px; display: flex; gap: 4px;">
                <div style="flex: 1;">
                    <input type="number" name="ventes[${idx}][lignes][${rowCount}][quantite]" class="form-control qte-input" placeholder="Cartons" min="0" value="0">
                </div>
                <div style="flex: 1.5;">
                    <input type="number" name="ventes[${idx}][lignes][${rowCount}][prix_vente]" class="form-control prix-input" placeholder="Prix" min="0">
                </div>
            </div>
            <!-- Cartouche -->
            <div class="cartouche-col" style="flex: 1.2; min-width: 140px; display: none; gap: 4px;">
                <div style="flex: 1;">
                    <input type="number" name="ventes[${idx}][lignes][${rowCount}][quantite_cartouche]" class="form-control qte-cartouche-input" placeholder="Cartouches" min="0" value="0">
                </div>
                <div style="flex: 1.5;">
                    <input type="number" name="ventes[${idx}][lignes][${rowCount}][prix_cartouche]" class="form-control prix-cartouche-input" placeholder="Prix" min="0">
                </div>
            </div>
            <div style="flex: 0.6; min-width: 80px;">
                <input type="text" class="form-control stotal-input" style="background: #f8fafc; font-weight: 700;" readonly value="0">
                <span class="input-error-msg qte-error" style="position: absolute; margin-top: 4px;"><i class="bi bi-exclamation-circle"></i> Stock: <span class="qte-error-text"></span> ctn</span>
            </div>
            <div style="display: flex; align-items: flex-end; padding-bottom: 2px;">
                <button type="button" class="btn btn-danger btn-sm remove-row-btn" style="padding: 8px 10px;"><i class="bi bi-trash"></i></button>
            </div>
        `;
        const resteDiv = document.createElement('div');
        resteDiv.className = 'reste-stock-info';
        resteDiv.style.cssText = 'display:none; font-size: 0.78rem; margin-top: 0; margin-bottom: 8px; padding: 4px 8px; background: #f8fafc; border-radius: 6px; border: 1px solid var(--border);';
        wrapper.appendChild(tmpl);
        wrapper.appendChild(resteDiv);
        lignesContainer.appendChild(wrapper);
        initAutocomplete(tmpl.querySelector('.produit-search'));
        
        tmpl.querySelector('.qte-input').addEventListener('input', function() { recalcRow(tmpl); recalcVente(venteCard); validateQte(tmpl); validatePaye(venteCard); });
        tmpl.querySelector('.prix-input').addEventListener('input', function() { recalcRow(tmpl); recalcVente(venteCard); validatePaye(venteCard); });
        
        const qteCart = tmpl.querySelector('.qte-cartouche-input');
        const prixCart = tmpl.querySelector('.prix-cartouche-input');
        if (qteCart) qteCart.addEventListener('input', function() { recalcRow(tmpl); recalcVente(venteCard); validateQte(tmpl); validatePaye(venteCard); });
        if (prixCart) prixCart.addEventListener('input', function() { recalcRow(tmpl); recalcVente(venteCard); validatePaye(venteCard); });
    }

    // ── Add vente card ──
    document.getElementById('addVenteBtn').addEventListener('click', function() {
        const idx = venteCount;
        const card = document.createElement('div');
        card.className = 'vente-card';
        card.dataset.index = idx;
        card.innerHTML = `
            <div class="vente-card-header">
                <h5><i class="bi bi-person-badge"></i> Client <span class="client-label">${idx + 1}</span></h5>
                <div style="display:flex;gap:10px;align-items:center;">
                    <span class="total-badge" id="totaux-${idx}">0 FCFA</span>
                    <button type="button" class="btn-remove-vente" title="Supprimer">&times;</button>
                </div>
            </div>
            <div class="vente-card-body">
                <div class="client-paiement-grid" style="display:grid;gap:12px;grid-template-columns:1fr 1fr;align-items:end;margin-bottom:8px;">
                    <div>
                        <label class="form-label-sm">Client</label>
                        <div class="autocomplete-wrap">
                            <input type="text" class="form-control client-search" placeholder="Client..." autocomplete="off">
                            <input type="hidden" name="ventes[${idx}][client_id]" class="client-id" value="">
                            <div class="autocomplete-dropdown"></div>
                        </div>
                    </div>
                    <div class="paye-col" id="payeCol-${idx}" style="display:none;">
                        <label class="form-label-sm">Montant payé (FCFA)</label>
                        <input type="number" name="ventes[${idx}][montant_paye]" class="form-control paye-input" value="0" min="0" readonly>
                        <span class="input-error-msg paye-error"><i class="bi bi-exclamation-circle"></i> <span class="paye-error-text"></span></span>
                    </div>
                </div>
                <div class="remis-row" id="remisRow-${idx}">
                    <div>
                        <label class="form-label-sm">Montant remis (FCFA)</label>
                        <input type="number" name="ventes[${idx}][montant_remis]" class="form-control remis-input" placeholder="Montant remis" min="0">
                    </div>
                    <div>
                        <label class="form-label-sm">Du client (FCFA)</label>
                        <div class="du-display zero" id="du-${idx}">0 FCFA</div>
                    </div>
                </div>
                <div style="margin-bottom:16px;">
                    <div class="checkbox-credit" style="margin-top:6px;">
                        <input type="checkbox" name="ventes[${idx}][a_credit]" value="1" class="credit-checkbox" id="credit-${idx}">
                        <label for="credit-${idx}">À crédit</label>
                    </div>
                </div>
                <div class="alert-credit" id="creditAlert-${idx}">
                    <i class="bi bi-info-circle"></i>
                    <span>Cette vente sera enregistrée comme une dette.</span>
                </div>
                <div class="echeance-row" id="echeanceRow-${idx}" style="display:none; margin-bottom:12px;">
                    <div class="form-group">
                        <label class="form-label-sm">Date de règlement souhaitée</label>
                        <div style="display:flex; gap:8px; flex-wrap:wrap;">
                            <select name="ventes[${idx}][echeance_option]" class="form-control echeance-select" style="flex:1; min-width:140px;" data-vente="${idx}">
                                <option value="">-- Optionnelle --</option>
                                <option value="today">Aujourd'hui</option>
                                <option value="tomorrow">Demain</option>
                                <option value="after_tomorrow">Après-demain</option>
                                <option value="6_days">Dans 6 jours (1 semaine)</option>
                                <option value="2_weeks">Dans 2 semaines</option>
                                <option value="1_month">Dans 1 mois</option>
                                <option value="custom">Personnalisé...</option>
                            </select>
                            <input type="date" name="ventes[${idx}][date_echeance_custom]" class="form-control echeance-custom" style="display:none; min-width:140px;">
                            <input type="hidden" name="ventes[${idx}][date_echeance]" class="echeance-value">
                        </div>
                    </div>
                </div>
                <label class="form-label-sm" style="margin-bottom:8px;">Articles <span style="color:#dc2626;">*</span></label>
                <div class="lignes-container" data-vente="${idx}">
                    <div class="ligne-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: flex-end; flex-wrap: wrap;">
                        <div style="flex: 2; min-width: 180px;">
                            <div class="autocomplete-wrap">
                                <input type="text" class="form-control produit-search" placeholder="Rechercher un produit..." autocomplete="off" required>
                                <input type="hidden" name="ventes[${idx}][lignes][0][produit_id]" class="produit-id">
                                <div class="autocomplete-dropdown"></div>
                            </div>
                        </div>
                        
                        <!-- Carton -->
                        <div style="flex: 0.8; min-width: 100px; display: flex; gap: 4px;">
                            <div style="flex: 1;">
                                <input type="number" name="ventes[${idx}][lignes][0][quantite]" class="form-control qte-input" placeholder="Cartons" min="0" value="0">
                            </div>
                            <div style="flex: 1.5;">
                                <input type="number" name="ventes[${idx}][lignes][0][prix_vente]" class="form-control prix-input" placeholder="Prix" min="0">
                            </div>
                        </div>

                        <!-- Cartouche -->
                        <div class="cartouche-col" style="flex: 1.2; min-width: 140px; display: none; gap: 4px;">
                            <div style="flex: 1;">
                                <input type="number" name="ventes[${idx}][lignes][0][quantite_cartouche]" class="form-control qte-cartouche-input" placeholder="Cartouches" min="0" value="0">
                            </div>
                            <div style="flex: 1.5;">
                                <input type="number" name="ventes[${idx}][lignes][0][prix_cartouche]" class="form-control prix-cartouche-input" placeholder="Prix" min="0">
                            </div>
                        </div>

                        <div style="flex: 0.6; min-width: 80px;">
                            <input type="text" class="form-control stotal-input" style="background: #f8fafc; font-weight: 700;" readonly value="0">
                            <span class="input-error-msg qte-error" style="position: absolute; margin-top: 4px;"><i class="bi bi-exclamation-circle"></i> Stock: <span class="qte-error-text"></span> ctn</span>
                        </div>
                        <div style="display: flex; align-items: flex-end; padding-bottom: 2px;">
                            <button type="button" class="btn btn-danger btn-sm remove-row-btn" style="padding: 8px 10px;"><i class="bi bi-trash"></i></button>
                        </div>
                    </div>
                    <div class="reste-stock-info" style="display:none; font-size: 0.78rem; margin-top: 4px; margin-bottom: 6px; padding: 4px 8px; background: #f8fafc; border-radius: 6px; border: 1px solid var(--border);"></div>
                </div>
                <button type="button" class="btn btn-secondary btn-sm add-row-btn" style="margin-top:2px;"><i class="bi bi-plus-circle"></i> Article</button>
                <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border);text-align:right;">
                    <button type="button" class="btn btn-sm btn-outline-primary save-card-btn"><i class="bi bi-floppy"></i> Sauvegarder cette vente</button>
                </div>
            </div>
        `;
        container.appendChild(card);
        initAutocomplete(card.querySelector('.produit-search'));
        initClientAutocomplete(card.querySelector('.client-search'));
        
        const firstRow = card.querySelector('.ligne-row');
        firstRow.querySelector('.qte-input').addEventListener('input', function() { recalcRow(firstRow); recalcVente(card); validateQte(firstRow); validatePaye(card); });
        firstRow.querySelector('.prix-input').addEventListener('input', function() { recalcRow(firstRow); recalcVente(card); validatePaye(card); });
        const qteCart = firstRow.querySelector('.qte-cartouche-input');
        const prixCart = firstRow.querySelector('.prix-cartouche-input');
        if (qteCart) qteCart.addEventListener('input', function() { recalcRow(firstRow); recalcVente(card); validateQte(firstRow); validatePaye(card); });
        if (prixCart) prixCart.addEventListener('input', function() { recalcRow(firstRow); recalcVente(card); validatePaye(card); });

        card.querySelector('.paye-input').addEventListener('input', function() { validatePaye(card); });
        card.querySelector('.credit-checkbox').addEventListener('change', function() {
            const idx = card.dataset.index;
            const alert = document.getElementById('creditAlert-' + idx);
            alert.classList.toggle('show', this.checked);
            const echeanceRow = document.getElementById('echeanceRow-' + idx);
            echeanceRow.style.display = this.checked ? 'block' : 'none';
            const paye = card.querySelector('.paye-input');
            paye.readOnly = !this.checked;
            if (!this.checked) paye.value = 0;
            toggleRemisVisibility(card);
        });

        // Échéance
        initEcheance(card);
        card.querySelector('.btn-remove-vente').addEventListener('click', function() {
            if (container.querySelectorAll('.vente-card').length > 1) card.remove();
            else alert('Au moins un client.');
        });
        card.querySelector('.save-card-btn').addEventListener('click', function() {
            if (hasOverstock()) { alert('Certains articles dépassent le stock disponible.'); return; }
            document.getElementById('save_one').value = card.dataset.index;
            document.getElementById('venteForm').requestSubmit();
        });
        // Init remis visibility (credit default = unchecked, so remis visible by default)
        venteCount++;
    });

    // ── Check overstock ──
    function hasOverstock() {
        let over = false;
        container.querySelectorAll('.qte-overstock').forEach(function(el) {
            if (el.closest('.ligne-row').closest('.vente-card').style.display !== 'none') over = true;
        });
        container.querySelectorAll('.paye-over').forEach(function(el) {
            if (el.closest('.vente-card').style.display !== 'none') over = true;
        });
        return over;
    }

    // ── Delegated events ──
    container.addEventListener('click', function(e) {
        const removeBtn = e.target.closest('.remove-row-btn');
        if (removeBtn) {
            const row = removeBtn.closest('.ligne-row');
            const card = row.closest('.vente-card');
            if (card.querySelectorAll('.ligne-row').length > 1) { row.remove(); recalcVente(card); }
            else alert('Au moins un article.');
        }
    });

    container.addEventListener('click', function(e) {
        const addBtn = e.target.closest('.add-row-btn');
        if (addBtn) addLigne(addBtn.closest('.vente-card'));
    });

    container.addEventListener('input', function(e) {
        if (e.target.classList.contains('qte-input') || e.target.classList.contains('qte-cartouche-input')) {
            const row = e.target.closest('.ligne-row');
            recalcRow(row);
            recalcVente(row.closest('.vente-card'));
            validateQte(row);
            validatePaye(row.closest('.vente-card'));
        }
        if (e.target.classList.contains('prix-input') || e.target.classList.contains('prix-cartouche-input')) {
            const row = e.target.closest('.ligne-row');
            recalcRow(row);
            recalcVente(row.closest('.vente-card'));
            validatePaye(row.closest('.vente-card'));
        }
        if (e.target.classList.contains('paye-input')) {
            validatePaye(e.target.closest('.vente-card'));
        }
        if (e.target.classList.contains('remis-input')) {
            const card = e.target.closest('.vente-card');
            let total = 0;
            card.querySelectorAll('.ligne-row').forEach(row => {
                const qte = parseFloat(row.querySelector('.qte-input').value) || 0;
                const prix = parseFloat(row.querySelector('.prix-input').value) || 0;
                const qteCartouche = parseFloat(row.querySelector('.qte-cartouche-input')?.value) || 0;
                const prixCartouche = parseFloat(row.querySelector('.prix-cartouche-input')?.value) || 0;
                total += (qte * prix) + (qteCartouche * prixCartouche);
            });
            calcDu(card, total);
        }
    });

    // ── Init first card ──
    const firstCard = document.querySelector('.vente-card');
    initAutocomplete(firstCard.querySelector('.produit-search'));
    initClientAutocomplete(firstCard.querySelector('.client-search'));
    firstCard.querySelectorAll('.ligne-row').forEach(row => {
        row.querySelector('.qte-input').addEventListener('input', function() { recalcRow(row); recalcVente(firstCard); validateQte(row); validatePaye(firstCard); });
        row.querySelector('.prix-input').addEventListener('input', function() { recalcRow(row); recalcVente(firstCard); validatePaye(firstCard); });
        const qteCart = row.querySelector('.qte-cartouche-input');
        const prixCart = row.querySelector('.prix-cartouche-input');
        if (qteCart) qteCart.addEventListener('input', function() { recalcRow(row); recalcVente(firstCard); validateQte(row); validatePaye(firstCard); });
        if (prixCart) prixCart.addEventListener('input', function() { recalcRow(row); recalcVente(firstCard); validateQte(row); validatePaye(firstCard); });
    });
    firstCard.querySelector('.paye-input').addEventListener('input', function() { validatePaye(firstCard); });
    firstCard.querySelector('.credit-checkbox').addEventListener('change', function() {
        const alert = document.getElementById('creditAlert-0');
        alert.classList.toggle('show', this.checked);
        const echeanceRow = document.getElementById('echeanceRow-0');
        echeanceRow.style.display = this.checked ? 'block' : 'none';
        const paye = firstCard.querySelector('.paye-input');
        paye.readOnly = !this.checked;
        if (!this.checked) paye.value = 0;
        toggleRemisVisibility(firstCard);
    });
    initEcheance(firstCard);
    firstCard.querySelector('.save-card-btn').addEventListener('click', function() {
        if (hasOverstock()) { alert('Certains articles dépassent le stock disponible.'); return; }
        document.getElementById('save_one').value = firstCard.dataset.index;
        document.getElementById('venteForm').requestSubmit();
    });

    // Init remis visibility for first card
    toggleRemisVisibility(firstCard);

    // ── Submit validation ──
    document.getElementById('venteForm').addEventListener('submit', function(e) {
        if (hasOverstock()) {
            e.preventDefault();
            alert('Certains articles dépassent le stock disponible. Corrigez les quantités en rouge.');
            return;
        }
        let valid = true;
        container.querySelectorAll('.ligne-row').forEach(row => {
            if (!row.querySelector('.produit-id').value) {
                valid = false;
                row.querySelector('.produit-search').style.borderColor = 'var(--danger)';
            }
        });
        if (!valid) {
            e.preventDefault();
            alert('Veuillez sélectionner un produit pour chaque ligne.');
            return;
        }
        // Vérifier que montant_payé ≤ total par carte
        container.querySelectorAll('.vente-card').forEach(card => {
            let total = 0;
            card.querySelectorAll('.ligne-row').forEach(r => {
                const qte = parseFloat(r.querySelector('.qte-input').value) || 0;
                const prix = parseFloat(r.querySelector('.prix-input').value) || 0;
                const qteCartouche = parseFloat(r.querySelector('.qte-cartouche-input')?.value) || 0;
                const prixCartouche = parseFloat(r.querySelector('.prix-cartouche-input')?.value) || 0;
                total += (qte * prix) + (qteCartouche * prixCartouche);
            });
            const clientId = card.querySelector('.client-id').value;
            const paye = parseFloat(card.querySelector('.paye-input').value) || 0;
            if (!clientId && paye > 0) {
                valid = false;
                card.querySelector('.paye-input').classList.add('paye-over');
            }
            if (paye > total) {
                valid = false;
                card.querySelector('.paye-input').classList.add('qte-overstock');
            }
        });
        if (!valid) {
            e.preventDefault();
            alert('Le montant payé ne peut pas dépasser le total ou un client doit être sélectionné si un paiement est saisi.');
            return;
        }
    });

    // ── "Tout enregistrer" clears save_one ──
    document.getElementById('saveAllBtn').addEventListener('click', function() {
        document.getElementById('save_one').value = '';
    });

    // ── Restore unsaved cards after partial save ──
    const unsavedVentes = @json(session('unsaved_ventes'));
    if (unsavedVentes && Object.keys(unsavedVentes).length > 0) {
        Object.entries(unsavedVentes).forEach(([idx, vData]) => {
            const cardIndex = parseInt(idx);
            if (cardIndex < 0) return;
            if (container.querySelector(`.vente-card[data-index="${cardIndex}"]`)) return;
            
            const card = document.createElement('div');
            card.className = 'vente-card';
            card.dataset.index = cardIndex;
            
            const lignesHtml = vData.lignes.map((l, li) => {
                const prod = produitsData.find(p => p.id == l.produit_id);
                const hasCartouche = prod && prod.a_cartouche && prod.cartouche_par_carton;
                return `
                <div class="ligne-row" style="display: flex; gap: 10px; margin-bottom: 8px; align-items: flex-end; flex-wrap: wrap;" 
                     data-stock="${prod ? prod.stock : 0}" data-cartouche-par-carton="${prod ? prod.cartouche_par_carton : 1}">
                    <div style="flex: 2; min-width: 180px;">
                        <div class="autocomplete-wrap">
                            <input type="text" class="form-control produit-search" value="${prod ? prod.nom : ''}" placeholder="Rechercher un produit..." autocomplete="off" required>
                            <input type="hidden" name="ventes[${cardIndex}][lignes][${li}][produit_id]" class="produit-id" value="${l.produit_id}">
                            <div class="autocomplete-dropdown"></div>
                        </div>
                    </div>
                    
                    <!-- Carton -->
                    <div style="flex: 0.8; min-width: 100px; display: flex; gap: 4px;">
                        <div style="flex: 1;">
                            <input type="number" name="ventes[${cardIndex}][lignes][${li}][quantite]" class="form-control qte-input" value="${l.quantite || (hasCartouche ? 0 : 1)}" placeholder="Cartons" min="0">
                        </div>
                        <div style="flex: 1.5;">
                            <input type="number" name="ventes[${cardIndex}][lignes][${li}][prix_vente]" class="form-control prix-input" value="${l.prix_vente || (prod ? prod.prix : 0)}" placeholder="Prix" min="0">
                        </div>
                    </div>

                    <!-- Cartouche -->
                    <div class="cartouche-col" style="flex: 1.2; min-width: 140px; display: ${hasCartouche ? 'flex' : 'none'}; gap: 4px;">
                        <div style="flex: 1;">
                            <input type="number" name="ventes[${cardIndex}][lignes][${li}][quantite_cartouche]" class="form-control qte-cartouche-input" value="${l.quantite_cartouche || 0}" placeholder="Cartouches" min="0">
                        </div>
                        <div style="flex: 1.5;">
                            <input type="number" name="ventes[${cardIndex}][lignes][${li}][prix_cartouche]" class="form-control prix-cartouche-input" value="${l.prix_cartouche || (prod ? prod.prix_cartouche : 0)}" placeholder="Prix" min="0">
                        </div>
                    </div>

                    <div style="flex: 0.6; min-width: 80px;">
                        <input type="text" class="form-control stotal-input" style="background:#f8fafc;font-weight:700;" readonly value="0">
                        <span class="input-error-msg qte-error" style="position: absolute; margin-top: 4px;"><i class="bi bi-exclamation-circle"></i> Stock: <span class="qte-error-text"></span> ctn</span>
                    </div>
                    <div>
                        <button type="button" class="btn btn-danger btn-sm remove-row-btn" style="padding:8px 10px;"><i class="bi bi-trash"></i></button>
                    </div>
                </div>`;
            }).join('');

            const clientName = vData.client_id 
                ? (() => { const c = clientsData.find(c => c.id == vData.client_id); return c ? c.nom + ' (' + c.telephone + ')' : ''; })()
                : '';

            card.innerHTML = `
                <div class="vente-card-header">
                    <h5><i class="bi bi-person-badge"></i> Client <span class="client-label">${cardIndex + 1}</span></h5>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <span class="total-badge" id="totaux-${cardIndex}">0 FCFA</span>
                        <button type="button" class="btn-remove-vente" title="Supprimer">&times;</button>
                    </div>
                </div>
                <div class="vente-card-body">
                    <div class="client-paiement-grid" style="display:grid;gap:12px;grid-template-columns:1fr 1fr;align-items:end;margin-bottom:8px;">
                        <div>
                            <label class="form-label-sm">Client</label>
                            <div class="autocomplete-wrap">
                                <input type="text" class="form-control client-search" value="${clientName}" placeholder="Client..." autocomplete="off">
                                <input type="hidden" name="ventes[${cardIndex}][client_id]" class="client-id" value="${vData.client_id || ''}">
                                <div class="autocomplete-dropdown"></div>
                            </div>
                        </div>
                        <div class="paye-col" id="payeCol-${cardIndex}" style="display:${vData.a_credit ? 'block' : 'none'};">
                            <label class="form-label-sm">Montant payé (FCFA)</label>
                            <input type="number" name="ventes[${cardIndex}][montant_paye]" class="form-control paye-input" value="${vData.montant_paye || 0}" min="0" \${vData.a_credit ? '' : 'readonly'}>
                            <span class="input-error-msg paye-error"><i class="bi bi-exclamation-circle"></i> <span class="paye-error-text"></span></span>
                        </div>
                    </div>
                    <div class="remis-row ${vData.a_credit ? '' : ''}" id="remisRow-${cardIndex}">
                        <div>
                            <label class="form-label-sm">Montant remis (FCFA)</label>
                            <input type="number" name="ventes[${cardIndex}][montant_remis]" class="form-control remis-input" placeholder="Montant remis" value="${vData.montant_remis || ''}" min="0">
                        </div>
                        <div>
                            <label class="form-label-sm">Du client (FCFA)</label>
                            <div class="du-display ${(vData.du && vData.du > 0) ? '' : 'zero'}" id="du-${cardIndex}">${vData.du ? Number(vData.du).toLocaleString() : 0} FCFA</div>
                        </div>
                    </div>
                    <div style="margin-bottom:16px;">
                        <div class="checkbox-credit" style="margin-top:6px;">
                            <input type="checkbox" name="ventes[${cardIndex}][a_credit]" value="1" class="credit-checkbox" id="credit-${cardIndex}" ${vData.a_credit ? 'checked' : ''}>
                            <label for="credit-${cardIndex}">À crédit</label>
                        </div>
                    </div>
                    <div class="alert-credit" id="creditAlert-${cardIndex}">
                        <i class="bi bi-info-circle"></i>
                        <span>Cette vente sera enregistrée comme une dette.</span>
                    </div>
                    <label class="form-label-sm" style="margin-bottom:8px;">Articles <span style="color:#dc2626;">*</span></label>
                    <div class="lignes-container" data-vente="${cardIndex}">
                        ${lignesHtml}
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm add-row-btn" style="margin-top:2px;"><i class="bi bi-plus-circle"></i> Article</button>
                    <div style="margin-top:16px;padding-top:12px;border-top:1px solid var(--border);text-align:right;">
                        <button type="button" class="btn btn-sm btn-outline-primary save-card-btn"><i class="bi bi-floppy"></i> Sauvegarder cette vente</button>
                    </div>
                </div>
            `;
            container.appendChild(card);
            
            initAutocomplete(card.querySelector('.produit-search'));
            initClientAutocomplete(card.querySelector('.client-search'));
            
            card.querySelectorAll('.ligne-row').forEach(row => {
                row.querySelector('.qte-input').addEventListener('input', function() { recalcRow(row); recalcVente(card); validateQte(row); validatePaye(card); });
                row.querySelector('.prix-input').addEventListener('input', function() { recalcRow(row); recalcVente(card); validatePaye(card); });
                const qteCart = row.querySelector('.qte-cartouche-input');
                const prixCart = row.querySelector('.prix-cartouche-input');
                if (qteCart) qteCart.addEventListener('input', function() { recalcRow(row); recalcVente(card); validateQte(row); validatePaye(card); });
                if (prixCart) prixCart.addEventListener('input', function() { recalcRow(row); recalcVente(card); validatePaye(card); });
                
                recalcRow(row);
                validateQte(row);
            });
            recalcVente(card);
            
            if (vData.a_credit) {
                card.querySelector('.paye-input').readOnly = false;
                card.querySelector('#creditAlert-' + cardIndex).classList.add('show');
            }
            
            card.querySelector('.credit-checkbox').addEventListener('change', function() {
                const alert = document.getElementById('creditAlert-' + card.dataset.index);
                alert.classList.toggle('show', this.checked);
                const paye = card.querySelector('.paye-input');
                paye.readOnly = !this.checked;
                if (!this.checked) paye.value = 0;
                toggleRemisVisibility(card);
            });
            card.querySelector('.btn-remove-vente').addEventListener('click', function() {
                if (container.querySelectorAll('.vente-card').length > 1) card.remove();
                else alert('Au moins un client.');
            });
            card.querySelector('.save-card-btn').addEventListener('click', function() {
                if (hasOverstock()) { alert('Certains articles dépassent le stock disponible.'); return; }
                document.getElementById('save_one').value = card.dataset.index;
                document.getElementById('venteForm').requestSubmit();
            });
            
            if (cardIndex >= venteCount) venteCount = cardIndex + 1;
        });
    }
});
</script>
@endpush
