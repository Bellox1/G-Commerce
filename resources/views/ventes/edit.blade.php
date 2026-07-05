@extends('layouts.app')
@section('title', 'Modifier la Vente ' . $vente->reference)
@section('page-title', 'Modifier : ' . $vente->reference)

@push('styles')
<style>
    .autocomplete-wrap { position: relative; }
    .autocomplete-wrap input { width: 100%; }
    .autocomplete-dropdown { position: absolute; top: 100%; left: 0; right: 0; background: #fff; border: 1px solid var(--border); border-radius: 0 0 6px 6px; max-height: 220px; overflow-y: auto; z-index: 999; display: none; box-shadow: var(--shadow-card); }
    .autocomplete-dropdown.show { display: block; }
    .autocomplete-item { padding: 8px 14px; cursor: pointer; display: flex; justify-content: space-between; align-items: center; font-size: .85rem; border-bottom: 1px solid var(--border); }
    .autocomplete-item:last-child { border-bottom: none; }
    .autocomplete-item:hover, .autocomplete-item.active { background: #f0fdf4; }
    .autocomplete-item .stock-badge { font-size: .7rem; padding: 2px 8px; border-radius: 10px; font-weight: 600; }
    .autocomplete-item .stock-ok { background: #dcfce7; color: #166534; }
    .autocomplete-item .stock-low { background: #fef3c7; color: #b45309; }
    .reste-card { background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center; }
    .reste-card .label { font-size: .85rem; font-weight: 600; color: #0369a1; text-transform: uppercase; letter-spacing: .5px; }
    .reste-card .value { font-size: 1.5rem; font-weight: 800; color: #0369a1; }
    .input-error-msg { display: none; font-size: .7rem; color: #dc2626; margin-top: 2px; }
    .input-error-msg.show { display: flex; align-items: center; gap: 4px; }
    .paye-over { border-color: #dc2626 !important; background: #fef2f2; }

    @media (max-width: 640px) {
        .edit-grid { grid-template-columns: 1fr !important; }
        .ligne-existante { flex-wrap: wrap; gap: 6px; }
        .ligne-existante > span:first-child { width: 100%; min-width: 0 !important; margin-bottom: 4px; }
        .ligne-existante > div { flex-wrap: wrap; gap: 6px; width: 100%; }
        .ligne-existante .edit-qte { width: 60px !important; }
        .ligne-existante .edit-prix { width: 90px !important; }
        .add-article-row { flex-wrap: wrap; }
        .add-article-row > div:first-child { flex: 1 1 100% !important; }
        .add-article-row > div { flex: 1 1 calc(50% - 8px) !important; min-width: 0 !important; }
        .reste-card { padding: 12px; }
        .reste-card .value { font-size: 1.2rem; }
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-pencil"></i> Modifier la vente</h3>
        <a href="{{ route('ventes.show', $vente) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('ventes.update', $vente) }}" id="editForm">
            @csrf
            @method('PUT')

            <div class="edit-grid" style="display: grid; gap: 16px; grid-template-columns: 1fr 1fr;">
                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Client</label>
                    <select name="client_id" class="form-control">
                        <option value="">-- Client Anonyme --</option>
                        @foreach($clients as $c)
                            <option value="{{ $c->id }}" {{ $vente->client_id == $c->id ? 'selected' : '' }}>
                                {{ $c->nomComplet() }} ({{ $c->telephone }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group" style="margin-bottom: 0;">
                    <label class="form-label">Montant payé (FCFA) *</label>
                    <input type="number" name="montant_paye" id="montant_paye" class="form-control" value="{{ (int) $vente->montant_paye }}" min="0" required>
                    <span class="input-error-msg paye-error"><i class="bi bi-exclamation-circle"></i> <span class="paye-error-text"></span></span>
                </div>
            </div>

            <div class="reste-card" style="margin: 16px 0;">
                <span class="label">Reste à payer</span>
                <span class="value" id="resteAffichage">{{ (int) $vente->montant_reste }} FCFA</span>
            </div>

            @if($vente->dette)
            <div style="background: #fff5f5; border: 1px solid #fee2e2; border-radius: 8px; padding: 12px; margin-bottom: 16px; font-size: .85rem;">
                <i class="bi bi-info-circle" style="color: var(--danger);"></i>
                Cette vente est liée à une dette. La modification du montant payé ajustera la dette automatiquement.
            </div>
            @endif

            {{-- Articles existants --}}
            <div class="form-group">
                <label class="form-label">Articles <span style="color:#dc2626;">*</span></label>
                <div id="lignes-existantes">
                    @foreach($vente->lignes as $l)
                        <div class="ligne-existante" data-produit-id="{{ $l->produit_id }}" style="display: flex; justify-content: space-between; align-items: center; padding: 8px 10px; background: #f8fafc; border: 1px solid var(--border); border-radius: 6px; margin-bottom: 6px;">
                            <span style="font-weight: 600; min-width: 180px;">{{ $l->produit?->nom }}</span>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <span style="color:#94a3b8;">×</span>
                                <input type="number" name="lignes_existantes[{{ $l->id }}][quantite]" value="{{ $l->quantite }}" min="1" class="form-control form-control-sm edit-qte" style="width: 70px;">
                                <input type="number" name="lignes_existantes[{{ $l->id }}][prix_vente]" value="{{ (int) $l->prix_vente }}" min="0" class="form-control form-control-sm edit-prix" style="width: 110px;">
                                <span class="edit-total" style="color: #94a3b8; min-width: 80px; text-align: right;">={{ (int) ($l->quantite * $l->prix_vente) }} FCFA</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Ajouter un article --}}
            <div class="form-group" style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border);">
                <label class="form-label">Ajouter un article</label>
                <div class="add-article-row" style="display: flex; gap: 10px; align-items: flex-end;">
                    <div style="flex: 2;">
                        <div class="autocomplete-wrap">
                            <input type="text" class="form-control" id="produitSearch" placeholder="Rechercher un produit..." autocomplete="off">
                            <input type="hidden" id="newProduitId" value="">
                            <div class="autocomplete-dropdown" id="produitDropdown"></div>
                        </div>
                    </div>
                    <div style="flex: 0.5;">
                        <input type="number" id="newQte" class="form-control" placeholder="Qté" min="1" value="1">
                    </div>
                    <div style="flex: 1;">
                        <input type="number" id="newPrix" class="form-control" placeholder="Prix unitaire" min="0">
                    </div>
                    <div>
                        <button type="button" class="btn btn-primary btn-sm" id="addArticleBtn" style="padding: 8px 16px;">
                            <i class="bi bi-plus-lg"></i> Ajouter
                        </button>
                    </div>
                </div>
                <div id="newLignesContainer" style="margin-top: 10px;"></div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--border);">
                <a href="{{ route('ventes.show', $vente) }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const produitsData = @json($produitsJson ?? []);
    const montantInitial = {{ (int) $vente->montant_total }};
    let newCount = 0;
    let addedLines = [];

    // ── Recalculer le reste ──
    function recalcReste() {
        let total = 0;
        // Lignes existantes modifiées
        document.querySelectorAll('#lignes-existantes .ligne-existante').forEach(div => {
            const qte = parseInt(div.querySelector('.edit-qte').value) || 0;
            const prix = parseFloat(div.querySelector('.edit-prix').value) || 0;
            total += qte * prix;
            const totSpan = div.querySelector('.edit-total');
            if (totSpan) totSpan.textContent = '=' + (qte * prix).toLocaleString('fr-FR') + ' FCFA';
        });
        // Nouvelles lignes
        total += addedLines.reduce((sum, l) => sum + l.quantite * l.prix_vente, 0);
        const paye = parseFloat(document.getElementById('montant_paye').value) || 0;
        const reste = Math.max(0, total - paye);
        document.getElementById('resteAffichage').textContent = reste.toLocaleString('fr-FR') + ' FCFA';
        return { total, paye, reste };
    }

    // Recalc on existing line changes
    document.querySelectorAll('#lignes-existantes .edit-qte, #lignes-existantes .edit-prix').forEach(el => {
        el.addEventListener('input', recalcReste);
    });

    // ── Autocomplete produit ──
    const searchInput = document.getElementById('produitSearch');
    const dropdown = document.getElementById('produitDropdown');
    const hiddenId = document.getElementById('newProduitId');
    const prixInput = document.getElementById('newPrix');

    function filterProduits(q) {
        q = q.toLowerCase().trim();
        if (!q) return [];
        return produitsData.filter(p => p.nom.toLowerCase().includes(q));
    }

    function renderDropdown(results, query) {
        dropdown.innerHTML = '';
        if (!results.length) {
            dropdown.innerHTML = query.trim()
                ? '<div class="autocomplete-item" style="color:var(--text-muted);cursor:default;justify-content:center;">Aucun produit trouvé</div>'
                : '';
            dropdown.classList.toggle('show', !!query.trim());
            return;
        }
        results.forEach((p, i) => {
            const item = document.createElement('div');
            item.className = 'autocomplete-item' + (i === 0 ? ' active' : '');
            item.innerHTML = `<span>${p.nom}</span><span class="stock-badge ${p.stock <= 5 ? 'stock-low' : 'stock-ok'}">Stock: ${p.stock}</span>`;
            item.dataset.id = p.id;
            item.dataset.prix = p.prix;
            item.addEventListener('click', () => {
                hiddenId.value = p.id;
                searchInput.value = p.nom;
                prixInput.value = p.prix;
                dropdown.classList.remove('show');
            });
            dropdown.appendChild(item);
        });
        dropdown.classList.add('show');
    }

    searchInput.addEventListener('input', function() {
        renderDropdown(filterProduits(this.value), this.value);
        hiddenId.value = '';
    });
    searchInput.addEventListener('keydown', function(e) {
        const items = dropdown.querySelectorAll('.autocomplete-item:not([style*="cursor:default"])');
        const active = dropdown.querySelector('.autocomplete-item.active');
        let idx = Array.from(items).indexOf(active);
        if (e.key === 'ArrowDown') { e.preventDefault(); if (idx < items.length - 1) { active?.classList.remove('active'); items[idx + 1].classList.add('active'); } }
        if (e.key === 'ArrowUp') { e.preventDefault(); if (idx > 0) { active?.classList.remove('active'); items[idx - 1].classList.add('active'); } }
        if (e.key === 'Enter') { e.preventDefault(); const target = active || items[0]; if (target) target.click(); }
        if (e.key === 'Escape') dropdown.classList.remove('show');
    });
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) dropdown.classList.remove('show');
    });

    // ── Ajouter article ──
    document.getElementById('addArticleBtn').addEventListener('click', function() {
        const prodId = hiddenId.value;
        const qte = parseInt(document.getElementById('newQte').value) || 1;
        const prix = parseFloat(document.getElementById('newPrix').value) || 0;
        if (!prodId) { alert('Sélectionnez un produit.'); return; }
        if (qte < 1) { alert('Quantité invalide.'); return; }

        // Vérifier si déjà dans les lignes existantes
        const existing = document.querySelector(`#lignes-existantes .ligne-existante[data-produit-id="${prodId}"]`);
        if (existing) {
            existing.querySelector('.edit-qte').value = parseInt(existing.querySelector('.edit-qte').value) + qte;
            existing.querySelector('.edit-prix').value = prix;
            recalcReste();
            resetAddForm();
            return;
        }

        // Vérifier si déjà dans les nouvelles lignes ajoutées
        const addedIdx = addedLines.findIndex(l => l.produit_id == prodId);
        if (addedIdx >= 0) {
            addedLines[addedIdx].quantite += qte;
            addedLines[addedIdx].prix_vente = prix;
            // Update the displayed line
            const container = document.getElementById('newLignesContainer');
            const divs = container.querySelectorAll('.ligne-existante');
            if (divs[addedIdx]) {
                divs[addedIdx].querySelector('input[name$="[quantite]"]').value = addedLines[addedIdx].quantite;
                divs[addedIdx].querySelector('input[name$="[prix_vente]"]').value = prix;
                const spans = divs[addedIdx].querySelectorAll('span');
                // Update display text
                const prod = produitsData.find(p => p.id == prodId);
                const qteSpan = divs[addedIdx].querySelector('.new-ligne-qte');
                const prixSpan = divs[addedIdx].querySelector('.new-ligne-prix');
                const totalSpan = divs[addedIdx].querySelector('.new-ligne-total');
                if (qteSpan) qteSpan.textContent = '×' + addedLines[addedIdx].quantite;
                if (prixSpan) prixSpan.textContent = prix.toLocaleString('fr-FR') + ' FCFA';
                if (totalSpan) totalSpan.textContent = '=' + (addedLines[addedIdx].quantite * prix).toLocaleString('fr-FR') + ' FCFA';
            }
            recalcReste();
            resetAddForm();
            return;
        }

        // Nouveau produit
        const prod = produitsData.find(p => p.id == prodId);
        addedLines.push({ produit_id: prodId, quantite: qte, prix_vente: prix });
        const idx = newCount++;
        const container = document.getElementById('newLignesContainer');
        const div = document.createElement('div');
        div.className = 'ligne-existante';
        div.style.cssText = 'display:flex;justify-content:space-between;align-items:center;padding:8px 10px;background:#fefce8;border:1px solid #facc15;border-radius:6px;margin-bottom:6px;';
        div.innerHTML = `
            <input type="hidden" name="new_lignes[${idx}][produit_id]" value="${prodId}">
            <input type="hidden" name="new_lignes[${idx}][quantite]" value="${qte}">
            <input type="hidden" name="new_lignes[${idx}][prix_vente]" value="${prix}">
            <span style="font-weight:600;">${prod ? prod.nom : 'Produit #' + prodId}</span>
            <div style="display:flex;gap:16px;align-items:center;">
                <span class="new-ligne-qte">×${qte}</span>
                <span class="new-ligne-prix" style="font-weight:700;">${prix.toLocaleString('fr-FR')} FCFA</span>
                <span class="new-ligne-total" style="color:#94a3b8;">=${(qte * prix).toLocaleString('fr-FR')} FCFA</span>
                <button type="button" class="btn btn-sm btn-danger remove-new-line" style="padding:2px 8px;">&times;</button>
            </div>
        `;
        container.appendChild(div);
        recalcReste();
        resetAddForm();
    });

    function resetAddForm() {
        searchInput.value = '';
        hiddenId.value = '';
        prixInput.value = '';
        document.getElementById('newQte').value = 1;
    }

    document.getElementById('newLignesContainer').addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-new-line')) {
            const div = e.target.closest('.ligne-existante');
            const idx = Array.from(this.children).indexOf(div);
            if (idx >= 0) addedLines.splice(idx, 1);
            div.remove();
            recalcReste();
        }
    });

    // ── Validation montant payé ──
    document.getElementById('montant_paye').addEventListener('input', function() {
        const { total, paye, reste } = recalcReste();
        const errSpan = document.querySelector('.paye-error');
        const errText = document.querySelector('.paye-error-text');
        if (paye > total) {
            this.classList.add('paye-over');
            if (errSpan && errText) {
                errText.textContent = 'Max: ' + total.toLocaleString('fr-FR') + ' FCFA';
                errSpan.classList.add('show');
            }
        } else {
            this.classList.remove('paye-over');
            if (errSpan) errSpan.classList.remove('show');
        }
    });

    recalcReste();
});
</script>
@endpush
