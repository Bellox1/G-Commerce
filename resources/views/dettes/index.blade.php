@extends('layouts.app')
@section('title', 'Gestion des Créances Clients')
@section('page-title', 'Suivi des Dettes & Créances')

@section('content')
<div class="card">
    <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
        <h3><i class="bi bi-credit-card-2-back"></i> Suivi des impayés</h3>
        
        <form method="GET" action="{{ route('dettes.index') }}" style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <select name="client_id" class="form-control" style="width: auto;">
                <option value="">Tous les clients</option>
                @foreach($clients as $c)
                    <option value="{{ $c->id }}" {{ request('client_id') == $c->id ? 'selected' : '' }}>{{ $c->nomComplet() }}</option>
                @endforeach
            </select>

            <select name="statut" class="form-control" style="width: auto;">
                <option value="" disabled {{ !request()->has('statut') ? 'selected' : '' }}>Filtrer par statut</option>
                <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                <option value="partiel" {{ request('statut') == 'partiel' ? 'selected' : '' }}>Partiel</option>
                <option value="en_retard" {{ request('statut') == 'en_retard' ? 'selected' : '' }}>En retard</option>
                <option value="solde" {{ request('statut') == 'solde' ? 'selected' : '' }}>Soldé</option>
            </select>

            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filtrer</button>
            <a href="{{ route('dettes.index') }}" class="btn btn-secondary btn-sm">Réinitialiser</a>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date d'origine</th>
                    <th>Client</th>
                    <th>Facture associée</th>
                    <th style="text-align: right;">Montant Initial</th>
                    <th style="text-align: right;">Montant Remboursé</th>
                    <th style="text-align: right;">Solde Restant</th>
                    <th>Échéance</th>
                    <th style="text-align: center;">Statut</th>
                    <th style="text-align: center; width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dettes as $d)
                @php
                    $retard = $d->estEnRetard();
                @endphp
                <tr style="{{ $retard ? 'background: #fff5f5;' : '' }}">
                    <td>{{ $d->created_at->format('d/m/Y') }}</td>
                    <td style="font-weight: 600;">{{ $d->client?->nomComplet() }}</td>
                    <td>
                        <a href="{{ route('ventes.show', $d->vente_id) }}" style="color: var(--primary); text-decoration: none; font-weight: 500;">
                            {{ $d->vente?->reference }}
                        </a>
                    </td>
                    <td style="text-align: right;">{{ number_format($d->montant_initial, 0, ',', ' ') }} F</td>
                    <td style="text-align: right; color: var(--success);">{{ number_format($d->montant_paye, 0, ',', ' ') }} F</td>
                    <td style="text-align: right; font-weight: 700; color: {{ $d->montant_restant > 0 ? 'var(--danger)' : 'var(--success)' }};">
                        {{ number_format($d->montant_restant, 0, ',', ' ') }} F
                    </td>
                    <td>
                        @if($d->date_echeance)
                            <span style="{{ $retard ? 'color: var(--danger); font-weight: 700;' : '' }}">
                                {{ $d->date_echeance->format('d/m/Y') }}
                            </span>
                        @else
                            <i style="color: #94a3b8;">Non définie</i>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($d->statut === 'solde')
                            <span class="badge badge-success">Soldé</span>
                        @elseif($retard || $d->statut === 'en_retard')
                            <span class="badge badge-danger">En retard</span>
                        @elseif($d->statut === 'partiel')
                            <span class="badge badge-warning">Partiel</span>
                        @else
                            <span class="badge badge-info">En cours</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <a href="{{ route('dettes.show', $d) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-wallet2"></i> Encaisser
                        </a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucune créance ou dette active trouvée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dettes->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: center;">
        {{ $dettes->links() }}
    </div>
    @endif
</div>
@endsection
