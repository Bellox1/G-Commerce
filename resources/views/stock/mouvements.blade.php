@extends('layouts.app')
@section('title', 'Historique des mouvements')
@section('page-title', 'Historique des Mouvements de Stock')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-clock-history"></i> Tous les mouvements
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ method_exists($mouvements, 'total') ? $mouvements->total() : $mouvements->count() }}</span>
        </h3>
        
        <form method="GET" action="{{ route('stock.mouvements') }}" style="display: flex; gap: 8px; flex-wrap: wrap;">
            <select name="magasin_id" class="form-control" style="width: auto;">
                <option value="">Tous les magasins</option>
                @foreach($magasins as $m)
                    <option value="{{ $m->id }}" {{ request('magasin_id') == $m->id ? 'selected' : '' }}>{{ $m->nom }}</option>
                @endforeach
            </select>

            <select name="produit_id" class="form-control" style="width: auto;">
                <option value="">Tous les produits</option>
                @foreach($produits as $p)
                    <option value="{{ $p->id }}" {{ request('produit_id') == $p->id ? 'selected' : '' }}>{{ $p->nom }}</option>
                @endforeach
            </select>

            <select name="type" class="form-control" style="width: auto;">
                <option value="">Tous les types</option>
                <option value="entree_arrivage" {{ request('type') == 'entree_arrivage' ? 'selected' : '' }}>Entrée arrivage</option>
                <option value="sortie_vente" {{ request('type') == 'sortie_vente' ? 'selected' : '' }}>Sortie vente</option>
                <option value="transfert_entrée" {{ request('type') == 'transfert_entree' ? 'selected' : '' }}>Transfert Entrée</option>
                <option value="transfert_sortie" {{ request('type') == 'transfert_sortie' ? 'selected' : '' }}>Transfert Sortie</option>
                <option value="ajustement_positif" {{ request('type') == 'ajustement_positif' ? 'selected' : '' }}>Ajustement positif</option>
                <option value="ajustement_negatif" {{ request('type') == 'ajustement_negatif' ? 'selected' : '' }}>Ajustement négatif</option>
            </select>

            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filtrer</button>
            <a href="{{ route('stock.mouvements') }}" class="btn btn-secondary btn-sm">Réinitialiser</a>
        </form>
    </div>
    
    <div class="table-search-wrap">
        <div class="table-search-field">
            <i class="bi bi-search table-search-icon"></i>
            <input type="text" class="table-search-input" placeholder="Rechercher un mouvement...">
        </div>
        <span class="table-search-count"></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date & Heure</th>
                    <th>Magasin</th>
                    <th>Produit</th>
                    <th>Type de mouvement</th>
                    <th style="text-align: right;">Quantité</th>
                    <th>Opérateur</th>
                    <th>Motif</th>
                </tr>
            </thead>
            <tbody>
                @forelse($mouvements as $m)
                <tr>
                    <td>{{ $m->date_mouvement->format('d/m/Y H:i:s') }}</td>
                    <td>{{ $m->magasin?->nom }}</td>
                    <td style="font-weight: 500;">{{ $m->produit?->nom }}</td>
                    <td>
                        @if($m->type === 'entree_arrivage')
                            <span class="badge badge-success">Entrée Arrivage</span>
                        @elseif($m->type === 'sortie_vente')
                            <span class="badge badge-danger">Sortie Vente</span>
                        @elseif($m->type === 'transfert_entree')
                            <span class="badge badge-info">Transfert Entrée</span>
                        @elseif($m->type === 'transfert_sortie')
                            <span class="badge badge-warning">Transfert Sortie</span>
                        @elseif($m->type === 'ajustement_positif')
                            <span class="badge badge-success">Ajustement (+)</span>
                        @elseif($m->type === 'ajustement_negatif')
                            <span class="badge badge-danger">Ajustement (-)</span>
                        @else
                            <span class="badge badge-gray">{{ $m->type }}</span>
                        @endif
                    </td>
                    <td style="text-align: right; font-weight: 700; color: {{ in_array($m->type, ['entree_arrivage', 'transfert_entree', 'ajustement_positif']) ? 'var(--success)' : 'var(--danger)' }};">
                        {{ in_array($m->type, ['entree_arrivage', 'transfert_entree', 'ajustement_positif']) ? '+' : '-' }}{{ $m->quantite }}
                    </td>
                    <td>{{ $m->user?->name ?: 'Système' }}</td>
                    <td style="color: var(--text-muted);">{{ $m->note ?: '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucun mouvement trouvé</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($mouvements->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: center;">
        {{ $mouvements->links() }}
    </div>
    @endif
</div>
@endsection
