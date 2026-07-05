@extends('layouts.app')
@section('title', 'Mon Profil')
@section('page-title', 'Mon Profil')

@section('content')
<div class="page-grid page-grid-2">
    {{-- Infos personnelles --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-person"></i> Informations personnelles</h3>
            <div style="display:flex; gap:6px; align-items:center;">
                <span class="badge {{ $user->role === 'super_admin' ? 'badge-danger' : ($user->role === 'admin' ? 'badge-warning' : ($user->role === 'vendeur' ? 'badge-success' : 'badge-gray')) }}" style="font-size:.8rem; padding:4px 12px;">
                    {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                </span>
                @if($user->roles_secondaires)
                    @foreach($user->roles_secondaires as $sr)
                        <span class="badge badge-gray" style="font-size:.8rem;">{{ ucfirst($sr) }}</span>
                    @endforeach
                @endif
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Nom</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    @error('name') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                    @error('email') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $user->telephone) }}">
                    @error('telephone') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Enregistrer</button>
            </form>
        </div>
    </div>

    {{-- Mot de passe --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-lock"></i> Modifier le mot de passe</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('profile.password') }}">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label class="form-label">Mot de passe actuel</label>
                    <input type="password" name="current_password" class="form-control" required>
                    @error('current_password') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-primary"><i class="bi bi-check"></i> Modifier</button>
            </form>
        </div>
    </div>

    {{-- Supprimer le compte --}}
    <div class="card" style="border-color: var(--danger); grid-column: 1 / -1;">
        <div class="card-header">
            <h3 style="color:var(--danger);"><i class="bi bi-trash"></i> Supprimer le compte</h3>
        </div>
        <div class="card-body">
            <p style="font-size:.85rem; color:var(--text-muted); margin-bottom:16px;">
                Cette action est irréversible. Toutes vos données seront supprimées.
            </p>
            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer votre compte ?');">
                @csrf
                @method('DELETE')

                <div class="form-group">
                    <label class="form-label">Entrez votre mot de passe pour confirmer</label>
                    <input type="password" name="delete_password" class="form-control" required>
                    @error('delete_password') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <button type="submit" class="btn btn-danger"><i class="bi bi-trash"></i> Supprimer mon compte</button>
            </form>
        </div>
    </div>
</div>
@endsection
