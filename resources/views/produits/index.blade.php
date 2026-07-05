@extends('layouts.app')
@section('title', 'Catalogue des Produits')
@section('page-title', 'Catalogue des Produits')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
        <h3><i class="bi bi-tag"></i> Tous les Articles</h3>
        <div style="display: flex; gap: 8px; align-items: center;">
            <form method="GET" action="{{ route('produits.index') }}" id="filterForm">
                <select name="magasin_id" class="form-control" style="width: auto; display: inline-block;" onchange="document.getElementById('filterForm').submit()">
                    @foreach($magasins as $m)
                        <option value="{{ $m->id }}" {{ $selectedMagasinId == $m->id ? 'selected' : '' }}>
                            {{ $m->nom }}
                        </option>
                    @endforeach
                </select>
            </form>
            @if(Auth::user()->peutModifierCatalogues())
            <a href="{{ route('produits.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle"></i> Nouveau Produit
            </a>
            @endif
        </div>
    </div>
    
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Réf</th>
                    <th>Désignation</th>
                    <th>Prix Vente</th>
                    <th>Stock</th>
                    <th>Cartouche</th>
                    <th>Alerte</th>
                    <th style="width: 100px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($produits as $p)
                <tr>
                    <td>PRD-{{ $p->id }}</td>
                    <td style="font-weight: 600;">{{ $p->nom }}</td>
                    <td style="font-weight: 600; color: var(--primary);">{{ (int) $p->prix_vente_conseille }} FCFA</td>
                    <td>
                        @php $s = $stockParProduit[$p->id] ?? 0; @endphp
                        <span class="badge {{ $s > 0 ? 'badge-success' : 'badge-danger' }}">
                            {{ $s }}
                        </span>
                    </td>
                    <td>
                        @if($p->a_cartouche)
                            {{ $p->cartouche_par_carton }} /carton
                            <br><small style="color: var(--text-muted);">{{ (int) $p->prix_cartouche_effectif }} FCFA</small>
                        @else
                            <span class="badge badge-gray">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $p->seuil_alerte > 0 ? 'badge-warning' : 'badge-gray' }}">
                            {{ $p->seuil_alerte }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        @if(Auth::user()->peutModifierCatalogues())
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a href="{{ route('produits.edit', $p) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('produits.destroy', $p) }}" onsubmit="return confirm('Supprimer ce produit du catalogue ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        @else
                        <span style="color:var(--text-muted); font-size:.8rem;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucun produit dans le catalogue.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
