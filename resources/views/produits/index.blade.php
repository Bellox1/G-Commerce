@extends('layouts.app')
@section('title', 'Catalogue des Produits')
@section('page-title', 'Catalogue des Produits')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-tag"></i> Tous les Articles
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ method_exists($produits, 'total') ? $produits->total() : $produits->count() }}</span>
        </h3>
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
                    <th style="width: 50px;">Image</th>
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
                    <td>
                        <a href="{{ route('produits.show', $p) }}">
                        @if($p->image)
                            @php $imgSrc = str_starts_with($p->image, 'http') ? $p->image : asset('storage/' . $p->image); @endphp
                            <img src="{{ $imgSrc }}" alt="{{ $p->nom }}" style="width:36px; height:36px; border-radius:6px; object-fit:cover; border:1px solid var(--border);" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                            <span style="display:none; width:36px; height:36px; border-radius:6px; background:#f1f5f9; align-items:center; justify-content:center; color:#94a3b8; font-size:.8rem;">
                                <i class="bi bi-image"></i>
                            </span>
                        @else
                            <span style="display:inline-flex; width:36px; height:36px; border-radius:6px; background:#f1f5f9; align-items:center; justify-content:center; color:#94a3b8; font-size:.8rem;">
                                <i class="bi bi-image"></i>
                            </span>
                        @endif
                        </a>
                    </td>
                    <td><a href="{{ route('produits.show', $p) }}" style="color: var(--primary); text-decoration: none;">PRD-{{ $p->id }}</a></td>
                    <td style="font-weight: 600;"><a href="{{ route('produits.show', $p) }}" style="color: inherit; text-decoration: none;">{{ $p->nom }}</a></td>
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
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucun produit dans le catalogue.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
