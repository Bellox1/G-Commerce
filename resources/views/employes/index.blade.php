@extends('layouts.app')
@section('title', 'Gestion du Personnel')
@section('page-title', 'Liste du Personnel')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-person-badge"></i> Gestion du Personnel
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ count($employes) }}</span>
        </h3>
        <a href="{{ route('employes.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Nouveau membre
        </a>
    </div>

    <div class="table-search-wrap">
        <div class="table-search-field">
            <i class="bi bi-search table-search-icon"></i>
            <input type="text" class="table-search-input" placeholder="Rechercher un membre...">
        </div>
        <span class="table-search-count"></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Téléphone</th>
                    <th>Rôle</th>
                    <th>Rôles secondaires</th>
                    <th style="text-align: center;">Statut</th>
                    <th style="text-align: center; width: 140px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employes as $e)
                <tr>
                    <td style="font-weight: 500;">{{ $e->name }}</td>
                    <td>{{ $e->email }}</td>
                    <td>{{ $e->telephone ?: '-' }}</td>
                    <td>
                        <span class="badge {{ $e->role === 'vendeur' ? 'badge-success' : ($e->role === 'magasinier' ? 'badge-warning' : 'badge-gray') }}">
                            {{ ucfirst($e->role) }}
                        </span>
                    </td>
                    <td>
                        @if($e->roles_secondaires && count($e->roles_secondaires))
                            @foreach($e->roles_secondaires as $sr)
                                <span class="badge badge-gray" style="margin-right:4px;">{{ ucfirst($sr) }}</span>
                            @endforeach
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($e->actif)
                            <span class="badge badge-success">Actif</span>
                        @else
                            <span class="badge badge-danger">Inactif</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a href="{{ route('employes.edit', $e) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('employes.toggle-active', $e) }}" onsubmit="return confirm('{{ $e->actif ? 'Désactiver ce compte ?' : 'Réactiver ce compte ?' }}')">
                                @csrf
                                <button type="submit" class="btn btn-warning btn-sm" style="padding: 4px 8px;" title="{{ $e->actif ? 'Désactiver' : 'Activer' }}">
                                    <i class="bi bi-{{ $e->actif ? 'pause-fill' : 'play-fill' }}"></i>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('employes.destroy', $e) }}" onsubmit="return confirm('Supprimer ce compte ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px;" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucun membre du personnel dans la base.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
