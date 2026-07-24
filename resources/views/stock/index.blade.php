@extends('layouts.app')
@section('title', 'Gestion des stocks')
@section('page-title', 'État des Stocks')

@section('content')
<div class="page-grid page-grid-3">
    
    {{-- Tableau des stocks --}}
    <div class="card stock-table">
        <div class="card-header stock-header">
            <h3 style="display:flex; align-items:center; gap:8px;">
                <i class="bi bi-boxes"></i> Quantités disponibles
                <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ count($produits) }}</span>
            </h3>
            <form method="GET" action="{{ route('stock.index') }}" id="filterForm">
                <select name="magasin_id" class="form-control" onchange="document.getElementById('filterForm').submit()">
                    @foreach($magasins as $m)
                        <option value="{{ $m->id }}" {{ $selectedMagasinId == $m->id ? 'selected' : '' }}>
                            {{ $m->nom }}
                        </option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="table-search-wrap">
            <div class="table-search-field">
                <i class="bi bi-search table-search-icon"></i>
                <input type="text" class="table-search-input" placeholder="Rechercher un produit...">
            </div>
            <span class="table-search-count"></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Désignation</th>
                        <th class="th-prix">Prix Conseillé</th>
                        <th class="th-prix">Prix Marché</th>
                        <th style="text-align: right;">En Stock</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($produits as $p)
                    @php 
                        $stockVal = $stockParProduit[$p->id] ?? 0;
                    @endphp
                    <tr>
                        <td class="td-ref">PRD-{{ $p->id }}</td>
                        <td style="font-weight: 600;">{{ $p->nom }}</td>
                        <td class="prix-cell">{{ number_format($p->prix_vente_conseille, 0, ',', ' ') }} FCFA</td>
                        <td class="prix-cell">{{ number_format($p->prix_marche, 0, ',', ' ') }} FCFA</td>
                        <td style="text-align: right; font-weight: 700;">
                            <span class="badge {{ $stockVal <= $p->seuil_alerte ? 'badge-danger' : 'badge-success' }}">
                                {{ $stockVal }} Carton
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucun produit dans le catalogue</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Ajustement de stock si admin/super_admin/magasinier --}}
    @if(in_array(auth()->user()->role, ['super_admin', 'admin', 'magasinier']))
    <div class="stock-sidebar">
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-sliders"></i> Ajustement / Inventaire</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('stock.ajuster') }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Magasin</label>
                        <select name="magasin_id" class="form-control" required>
                            @foreach($magasins as $m)
                                <option value="{{ $m->id }}" {{ $selectedMagasinId == $m->id ? 'selected' : '' }}>
                                    {{ $m->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Produit</label>
                        <select name="produit_id" class="form-control" required>
                            @foreach($produits as $p)
                                <option value="{{ $p->id }}">{{ $p->nom }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Nouvelle quantité réelle</label>
                        <input type="number" name="quantite" class="form-control" min="0" placeholder="Entrez la quantité constatée" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Motif de l'ajustement</label>
                        <input type="text" name="note" class="form-control" placeholder="Ex: Perte, écart inventaire, abîmé...">
                    </div>

                    <button type="submit" class="btn btn-primary btn-full">
                        <i class="bi bi-check-circle"></i> Enregistrer l'ajustement
                    </button>
                </form>
            </div>
        </div>

        <div class="card stock-mouvements-card">
            <i class="bi bi-journal-text" style="font-size: 2rem; color: var(--primary);"></i>
            <h4>Historique des Mouvements</h4>
            <p>Consultez la traçabilité complète de vos stocks</p>
            <a href="{{ route('stock.mouvements') }}" class="btn btn-secondary btn-sm" style="margin-top: 8px;">
                <i class="bi bi-clock-history"></i> Voir les mouvements
            </a>
        </div>
    </div>
    @endif
</div>
@endsection
