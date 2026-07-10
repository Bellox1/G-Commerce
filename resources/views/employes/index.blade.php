@extends('layouts.app')
@section('title', 'Gestion des Employés')
@section('page-title', 'Liste des Employés')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-person-badge"></i> Gestion des Employés
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ count($employes) }}</span>
        </h3>
        <a href="{{ route('employes.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Nouvel Employé
        </a>
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
                    <th style="text-align: center; width: 100px;">Actions</th>
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
                            <a href="{{ route('employes.edit', $e) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('employes.destroy', $e) }}" onsubmit="return confirm('Retirer cet employé ?')">
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
                    <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucun employé dans la base.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
