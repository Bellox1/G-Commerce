@extends('layouts.app')

@section('title', 'Mes Sociétés')
@section('subtitle', 'Sociétés que vous avez créées en tant que partenaire')

@section('content')
<div class="card">
    <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
        <h3><i class="bi bi-building"></i> Sociétés ({{ $tenants->total() }})</h3>
        <a href="{{ route('prestataire.tenants.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Créer une société
        </a>
    </div>
    @if($tenants->isEmpty())
        <div style="padding:40px 20px; text-align:center; color:var(--text-muted);">
            <i class="bi bi-building" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
            <p>Vous n'avez pas encore créé de société.</p>
        </div>
    @else
        <div class="table-search-wrap">
            <div class="table-search-field">
                <i class="bi bi-search table-search-icon"></i>
                <input type="text" class="table-search-input" placeholder="Rechercher une société...">
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
                        <th>Magasins</th>
                        <th>Utilisateurs</th>
                        <th>Créée le</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tenants as $t)
                    <tr>
                        <td class="fw-bold">{{ $t->nom }}</td>
                        <td>{{ $t->email ?? '—' }}</td>
                        <td>{{ $t->telephone ?? '—' }}</td>
                        <td>{{ $t->magasins_count }}</td>
                        <td>{{ $t->users_count }}</td>
                        <td>{{ $t->created_at->format('d/m/Y') }}</td>
                        <td>
                            <a href="{{ route('prestataire.tenants.edit', $t) }}" class="btn btn-sm" style="background:var(--primary); color:#fff; padding:5px 12px; border-radius:6px; text-decoration:none; font-size:0.82rem;">
                                <i class="bi bi-pencil"></i> Modifier
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="padding:16px;">{{ $tenants->links() }}</div>
    @endif
</div>
@endsection
