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
</div>

{{-- Zone d'action sensible / suppression discrète --}}
<div style="margin-top: 40px; border-top: 1px solid #e2e8f0; padding-top: 20px; text-align: center;">
    <button type="button" onclick="document.getElementById('danger-zone-box').style.display = document.getElementById('danger-zone-box').style.display === 'none' ? 'block' : 'none';" style="background:transparent; border:none; color:#94a3b8; font-size:.8rem; cursor:pointer;">
        <i class="bi bi-shield-exclamation"></i> Options de compte avancées...
    </button>

    <div id="danger-zone-box" style="display:none; max-width: 480px; margin: 16px auto 0 auto; background: #fff5f5; border: 1px dashed #feb2b2; border-radius: 12px; padding: 20px; text-align: left;">
        <h4 style="font-size: .9rem; color: #c53030; font-weight: 700; margin-bottom: 8px;">
            <i class="bi bi-exclamation-triangle"></i> Supprimer définitivement mon compte
        </h4>
        <p style="font-size: .8rem; color: #742a2a; margin-bottom: 14px;">
            Cette action supprime l'accès au compte de manière irréversible.
        </p>
        <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('Attention : Êtes-vous ABSOLUMENT certain de vouloir supprimer définitivement votre compte ?');">
            @csrf
            @method('DELETE')

            <div class="form-group" style="margin-bottom: 12px;">
                <label class="form-label" style="font-size: .78rem; color: #742a2a;">Entrez votre mot de passe pour confirmer</label>
                <input type="password" name="delete_password" class="form-control form-control-sm" required placeholder="Mot de passe actuel">
                @error('delete_password') <small style="color:var(--danger);">{{ $message }}</small> @enderror
            </div>

            <button type="submit" class="btn btn-sm btn-outline-danger" style="border-radius: 8px; font-weight: 700;">
                <i class="bi bi-trash"></i> Confirmer la suppression
            </button>
        </form>
    </div>
</div>
@endsection
