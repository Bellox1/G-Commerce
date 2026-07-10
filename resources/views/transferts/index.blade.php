@extends('layouts.app')
@section('title', 'Gestion des Transferts')
@section('page-title', 'Transferts entre Magasins')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-arrow-left-right"></i> Historique des Transferts
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ method_exists($transferts, 'total') ? $transferts->total() : $transferts->count() }}</span>
        </h3>
        @if(Auth::user()->peutGererTransferts())
        <a href="{{ route('transferts.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Nouveau Transfert
        </a>
        @endif
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Produits</th>
                    <th>Magasin Source</th>
                    <th>Magasin Destination</th>
                    <th style="text-align: center;">Statut</th>
                    <th style="text-align: center; width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transferts as $t)
                <tr>
                    <td style="font-weight: 600;">
                        <a href="{{ route('transferts.show', $t) }}" style="color: var(--primary); text-decoration: none;">
                            {{ $t->reference }}
                        </a>
                    </td>
                    <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                    <td>
                        @if($t->produits && $t->produits->count())
                            @foreach($t->produits as $tp)
                                <span class="badge badge-gray" style="margin-right:2px;">{{ $tp->produit?->nom }} ({{ $tp->quantite }})</span>
                            @endforeach
                        @elseif($t->produit)
                            <span class="badge badge-gray">{{ $t->produit?->nom }} ({{ $t->quantite }})</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td>{{ $t->magasinSource?->nom }}</td>
                    <td>{{ $t->magasinDestination?->nom }}</td>
                    <td style="text-align: center;">
                        @if($t->statut === 'livre')
                            <span class="badge badge-success"><i class="bi bi-check-lg"></i> Livré</span>
                        @else
                            <span class="badge badge-warning"><i class="bi bi-clock"></i> En transit</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('transferts.show', $t) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">
                            <i class="bi bi-eye"></i>
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucun transfert enregistré.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($transferts->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: center;">
        {{ $transferts->links() }}
    </div>
    @endif
</div>
@endsection
