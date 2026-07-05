@extends('layouts.app')
@section('title', 'Gestion des Sociétés (Multi-Tenant)')
@section('page-title', 'Sociétés')

@section('actions')
<a href="{{ route('tenants.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle-fill"></i> Nouvelle Société
</a>
@endsection

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-building"></i> Liste des Sociétés clientes</h3>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nom de la Société</th>
                    <th>Marque</th>
                    <th>Activité</th>
                    <th>Ville/Pays</th>
                    <th>Téléphone</th>
                    <th>Email principal</th>
                    <th style="text-align: center;">Magasins</th>
                    <th style="text-align: center;">Utilisateurs</th>
                    <th style="text-align: center;">Statut</th>
                    <th style="text-align: center; width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $t)
                <tr>
                    <td style="font-weight: 700; color: var(--primary);">
                        <a href="{{ route('tenants.show', $t) }}" style="text-decoration: none; color: inherit;">
                            {{ $t->nom }}
                        </a>
                    </td>
                    <td>{{ $t->marque ?? '-' }}</td>
                    <td>{{ $t->activite ?? '-' }}</td>
                    <td>{{ $t->ville ?? '-' }}, {{ $t->pays }}</td>
                    <td>{{ $t->telephone ?? '-' }}</td>
                    <td>{{ $t->email ?? '-' }}</td>
                    <td style="text-align: center;">
                        <span class="badge badge-gray font-weight-bold" style="font-size: 0.9rem;">
                            {{ $t->magasins_count }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-gray font-weight-bold" style="font-size: 0.9rem;">
                            {{ $t->users_count }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        @if($t->actif)
                            <span class="badge badge-success"><i class="bi bi-shield-check"></i> Actif</span>
                        @else
                            <span class="badge badge-danger"><i class="bi bi-shield-slash"></i> Inactif</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a href="{{ route('tenants.show', $t) }}" class="btn btn-secondary btn-sm" style="padding: 6px 10px;" title="Gérer">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('tenants.edit', $t) }}" class="btn btn-secondary btn-sm" style="padding: 6px 10px;" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('tenants.destroy', $t) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette société ? Tous ses magasins et données associés seront indisponibles.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 6px 10px;" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucune société enregistrée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tenants->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: center;">
        {{ $tenants->links() }}
    </div>
    @endif
</div>
@endsection
