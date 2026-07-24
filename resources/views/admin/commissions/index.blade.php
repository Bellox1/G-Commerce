@extends('layouts.app')

@section('title', 'Gestion des Commissions')
@section('subtitle', 'Suivez et réglez les commissions générées par vos partenaires')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-wallet2"></i> Commissions Partenaires ({{ $commissions->count() }})</h3>
    </div>

    @if($commissions->isEmpty())
        <div style="text-align:center; padding:40px 20px; color:var(--text-muted);">
            <i class="bi bi-inbox" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
            <p>Aucune commission enregistrée pour le moment.</p>
        </div>
    @else
        <div class="table-search-wrap">
            <div class="table-search-field">
                <i class="bi bi-search table-search-icon"></i>
                <input type="text" class="table-search-input" placeholder="Rechercher une commission...">
            </div>
            <span class="table-search-count"></span>
        </div>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</td>
                        <th>Partenaire</th>
                        <th>Société Client</th>
                        <th>Montant</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($commissions as $c)
                    <tr>
                        <td>{{ $c->created_at->format('d/m/Y') }}</td>
                        <td>
                            @if($c->partenaire)
                                <span class="fw-bold">{{ $c->partenaire->name }}</span><br>
                                <span class="text-muted" style="font-size:0.75rem;">{{ $c->partenaire->email }}</span>
                            @else
                                <span class="text-muted">Partenaire supprimé</span>
                            @endif
                        </td>
                        <td>
                            @if($c->tenant)
                                <span class="fw-bold">{{ $c->tenant->nom }}</span>
                                @if($c->tenant->marque)
                                    <span class="text-muted" style="font-size:0.75rem;">({{ $c->tenant->marque }})</span>
                                @endif
                            @else
                                <span class="text-muted">Société supprimée</span>
                            @endif
                        </td>
                        <td class="fw-bold text-success">{{ number_format($c->montant, 0, ',', ' ') }} FCFA</td>
                        <td>
                            @if($c->statut === 'en_attente')
                                <span class="badge badge-warning">En attente</span>
                            @else
                                <span class="badge badge-success">Réglée</span>
                            @endif
                        </td>
                        <td>
                            @if($c->statut === 'en_attente')
                                <form action="{{ route('admin.commissions.statut', $c->id) }}" method="POST" style="display:inline;" data-no-api="true">
                                    @csrf
                                    <input type="hidden" name="statut" value="reglee">
                                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Marquer cette commission comme réglée ?')">
                                        <i class="bi bi-check-circle-fill"></i> Réglée
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('admin.commissions.statut', $c->id) }}" method="POST" style="display:inline;" data-no-api="true">
                                    @csrf
                                    <input type="hidden" name="statut" value="en_attente">
                                    <button type="submit" class="btn btn-secondary btn-sm" onclick="return confirm('Remettre cette commission en attente ?')">
                                        <i class="bi bi-arrow-counterclockwise"></i> Remettre en attente
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
