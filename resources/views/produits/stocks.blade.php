@extends('layouts.app')
@section('title', 'Gestion des stocks - ' . $produit->nom)
@section('page-title', 'Stocks par magasin : ' . $produit->nom)

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-boxes"></i> Stocks par magasin</h3>
        <a href="{{ route('produits.show', $produit) }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour au produit
        </a>
    </div>
    <div class="card-body">
        <p style="color: var(--text-muted); font-size: .85rem; margin-bottom: 16px;">
            Définissez la quantité réelle dans chaque magasin. Le stock total est centralisé (somme de tous les magasins).
            Une différence par rapport au stock actuel génère automatiquement un ajustement d'inventaire.
        </p>

        <form method="POST" action="{{ route('produits.stocks.update', $produit) }}">
            @csrf
            @method('PUT')

            <div style="display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:16px;">
                @foreach($magasins as $m)
                    <div style="display:flex; flex-direction:column; gap:4px;">
                        <label class="form-label" style="font-size:.8rem;">{{ $m->nom }}</label>
                        <div style="display:flex; align-items:center; gap:6px;">
                            <input type="number" name="stocks[{{ $m->id }}]" class="form-control" value="{{ $stockParMagasin[$m->id] ?? 0 }}" min="0" style="width:90px;">
                            <span style="font-size:.75rem; color:var(--text-muted);">ctn</span>
                            @if($produit->a_cartouche)
                                <span style="display:inline-flex; align-items:center; gap:4px;">
                                    <input type="number" name="stocks_cartouches[{{ $m->id }}]" class="form-control" value="{{ $stockCartouchesParMagasin[$m->id] ?? 0 }}" min="0" style="width:70px;">
                                    <span style="font-size:.75rem; color:var(--text-muted);">ctr</span>
                                </span>
                            @endif
                        </div>
                        <small style="color:var(--text-muted); font-size:.7rem;">Stock actuel : {{ $stockParMagasin[$m->id] ?? 0 }} ctn{{ $produit->a_cartouche ? ' + ' . ($stockCartouchesParMagasin[$m->id] ?? 0) . ' ctr' : '' }}</small>
                    </div>
                @endforeach
            </div>

            <div style="margin-top:20px; display:flex; gap:12px;">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg"></i> Enregistrer les stocks
                </button>
                <a href="{{ route('produits.show', $produit) }}" class="btn btn-secondary">Annuler</a>
            </div>
        </form>
    </div>
</div>
@endsection
