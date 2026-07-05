@extends('layouts.app')
@section('title', 'Nouvel Employé')
@section('page-title', 'Nouvel Employé')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-plus-circle"></i> Ajouter un employé</h3>
        <a href="{{ route('employes.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('employes.store') }}">
            @csrf

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Nom complet <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Email <span style="color:var(--danger);">*</span></label>
                    <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                    @error('email') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}">
                </div>

                <div class="form-group">
                    <label class="form-label">Rôle <span style="color:var(--danger);">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="">Sélectionner un rôle</option>
                        <option value="vendeur" @selected(old('role') === 'vendeur')>Vendeur</option>
                        <option value="livreur" @selected(old('role') === 'livreur')>Livreur</option>
                        <option value="magasinier" @selected(old('role') === 'magasinier')>Magasinier</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" style="margin-bottom: 8px;">Rôles secondaires</label>
                    <small style="color:var(--text-muted); font-size:.75rem; display:block; margin-bottom:8px;">L'employé peut cumuler plusieurs rôles (ex: magasinier qui peut aussi vendre).</small>
                    <div style="display:flex; gap:20px; flex-wrap:wrap;" id="rolesSecondaires">
                        @php $rolesDisponibles = ['vendeur', 'livreur', 'magasinier']; @endphp
                        @foreach($rolesDisponibles as $r)
                        <div class="checkbox-group" data-role="{{ $r }}">
                            <label class="checkbox-label">
                                <input type="checkbox" name="roles_secondaires[]" value="{{ $r }}"
                                    @checked(is_array(old('roles_secondaires')) && in_array($r, old('roles_secondaires')))>
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
                var secCheckboxes = document.querySelectorAll('#rolesSecondaires .checkbox-group');
                function filterSecondaires() {
                    var selected = roleSelect.value;
                    secCheckboxes.forEach(function(group) {
                        if (group.dataset.role === selected) {
                            group.style.display = 'none';
                            group.querySelector('input').checked = false;
                        } else {
                            group.style.display = '';
                        }
                    });
                }
                roleSelect.addEventListener('change', filterSecondaires);
                filterSecondaires();
            });
            </script>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Mot de passe <span style="color:var(--danger);">*</span></label>
                    <input type="password" name="password" class="form-control" required>
                    @error('password') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe <span style="color:var(--danger);">*</span></label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <a href="{{ route('employes.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
