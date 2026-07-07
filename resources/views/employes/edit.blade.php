@extends('layouts.app')
@section('title', 'Modifier l\'Employé')
@section('page-title', 'Modifier l\'Employé')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-pencil"></i> Modifier {{ $employe->name }}</h3>
        <a href="{{ route('employes.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('employes.update', $employe) }}">
            @csrf
            @method('PUT')

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Nom complet <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $employe->name) }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email <span style="color:var(--danger);">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $employe->email) }}" required>
                    @error('email') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $employe->telephone) }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Rôle <span style="color:var(--danger);">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="admin" @selected(old('role', $employe->role) === 'admin')>Admin</option>
                        <option value="vendeur" @selected(old('role', $employe->role) === 'vendeur')>Vendeur</option>
                        <option value="livreur" @selected(old('role', $employe->role) === 'livreur')>Livreur</option>
                        <option value="magasinier" @selected(old('role', $employe->role) === 'magasinier')>Magasinier</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" style="margin-bottom: 8px;">Rôles secondaires</label>
                    <small style="color:var(--text-muted); font-size:.75rem; display:block; margin-bottom:8px;">L'employé peut cumuler plusieurs rôles (ex: magasinier qui peut aussi vendre).</small>
                    <div style="display:flex; gap:20px; flex-wrap:wrap;" id="rolesSecondaires">
                        @php
                            $rolesDisponibles = ['vendeur', 'livreur', 'magasinier'];
                            $secondaires = old('roles_secondaires', $employe->roles_secondaires ?? []);
                        @endphp
                        @foreach($rolesDisponibles as $r)
                        <div class="checkbox-group" data-role="{{ $r }}">
                            <label class="checkbox-label">
                                <input type="checkbox" name="roles_secondaires[]" value="{{ $r }}"
                                    @checked(in_array($r, $secondaires))>
                                <span class="checkbox-custom"></span>
                                {{ ucfirst($r) }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function() {
                var roleSelect = document.querySelector('select[name="role"]');
                var secContainer = document.getElementById('rolesSecondaires');
                var secCheckboxes = document.querySelectorAll('#rolesSecondaires .checkbox-group');
                function filterSecondaires() {
                    var selected = roleSelect.value;
                    if (selected === 'admin') {
                        secContainer.style.display = 'none';
                        secCheckboxes.forEach(function(group) {
                            group.querySelector('input').checked = false;
                        });
                    } else {
                        secContainer.style.display = '';
                        secCheckboxes.forEach(function(group) {
                            if (group.dataset.role === selected) {
                                group.style.display = 'none';
                                group.querySelector('input').checked = false;
                            } else {
                                group.style.display = '';
                            }
                        });
                    }
                }
                roleSelect.addEventListener('change', filterSecondaires);
                filterSecondaires();
            });
            </script>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Nouveau mot de passe</label>
                    <input type="password" name="password" class="form-control">
                    <small style="color:var(--text-muted); font-size:.75rem;">Laissez vide pour conserver le mot de passe actuel.</small>
                    @error('password') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe</label>
                    <input type="password" name="password_confirmation" class="form-control">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <div class="checkbox-group">
                        <label class="checkbox-label">
                            <input type="hidden" name="actif" value="0">
                            <input type="checkbox" name="actif" value="1" @checked(old('actif', $employe->actif))>
                            <span class="checkbox-custom"></span>
                            Compte actif
                        </label>
                    </div>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <a href="{{ route('employes.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Enregistrer les Modifications</button>
            </div>
        </form>
    </div>
</div>
@endsection
