@extends('layouts.app')
@section('title', 'Gestion des Clients')
@section('page-title', 'Liste des Clients')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-people"></i> Gestion des Clients
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ method_exists($clients, 'total') ? $clients->total() : $clients->count() }}</span>
        </h3>
        <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Nouveau Client
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Téléphone</th>
                    <th>Nom Complet</th>
                    <th>Adresse</th>
                    <th style="text-align: right;">Limite Crédit (CFA)</th>
                    <th style="text-align: right;">Dette Actuelle (CFA)</th>
                    <th style="text-align: center; width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($clients as $c)
                @php
                    $detteEnCours = \App\Models\Dette::where('client_id', $c->id)
                        ->whereIn('statut', ['en_cours', 'partiel', 'en_retard'])
                        ->sum('montant_restant');
                @endphp
                <tr>
                    <td style="font-weight: 600; color: var(--primary);">{{ $c->telephone }}</td>
                    <td style="font-weight: 500;">{{ $c->nomComplet() }}</td>
                    <td>{{ $c->adresse ?: '-' }}</td>
                    <td style="text-align: right; color: var(--text-muted);">
                        {{ number_format($c->limite_credit, 0, ',', ' ') }} FCFA
                    </td>
                    <td style="text-align: right; font-weight: 700; color: {{ $detteEnCours > 0 ? 'var(--danger)' : 'var(--success)' }};">
                        {{ number_format($detteEnCours, 0, ',', ' ') }} FCFA
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a href="{{ route('clients.edit', $c) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('clients.destroy', $c) }}" onsubmit="return confirm('Retirer ce client ? Cela conservera ses factures.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucun client dans la base.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
