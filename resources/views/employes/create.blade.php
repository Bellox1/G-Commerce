@extends('layouts.app')
@section('title', 'Nouveau membre du personnel')
@section('page-title', 'Nouveau membre du personnel')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-plus-circle"></i> Ajouter un membre du personnel</h3>
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
                    <label class="form-label">Salaire mensuel (FCFA)</label>
                    <input type="number" name="salaire" class="form-control" value="{{ old('salaire') }}" min="0" placeholder="Ex : 150000">
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Rôle <span style="color:var(--danger);">*</span></label>
                    <select name="role" class="form-control" required>
                        <option value="">Sélectionner un rôle</option>
                        <option value="superviseur" @selected(old('role') === 'superviseur')>Superviseur</option>
                        <option value="vendeur" @selected(old('role', 'vendeur') === 'vendeur')>Vendeur</option>
                        <option value="livreur" @selected(old('role') === 'livreur')>Livreur</option>
                        <option value="magasinier" @selected(old('role') === 'magasinier')>Magasinier</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" style="margin-bottom: 8px;">Rôles secondaires</label>
                    <small style="color:var(--text-muted); font-size:.75rem; display:block; margin-bottom:8px;">Le membre du personnel peut cumuler plusieurs rôles (ex: magasinier qui peut aussi vendre).</small>
                    <div style="display:flex; gap:20px; flex-wrap:wrap;" id="rolesSecondaires">
                            @php $rolesDisponibles = ['superviseur', 'vendeur', 'livreur', 'magasinier']; @endphp
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
                var secContainer = document.getElementById('rolesSecondaires');
                var secCheckboxes = document.querySelectorAll('#rolesSecondaires .checkbox-group');
                function filterSecondaires() {
                    var selected = roleSelect.value;
                    if (selected === 'superviseur') {
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
                    <label class="form-label">Mot de passe <span style="color:var(--danger);">*</span></label>
                    <div style="position:relative;">
                        <input type="password" name="password" id="inputPass" class="form-control" style="padding-right:40px;" required>
                        <button type="button" onclick="togglePassVisibility('inputPass', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    @error('password') <small style="color:var(--danger);">{{ $message }}</small> @enderror
                </div>

                <div class="form-group">
                    <label class="form-label">Confirmer le mot de passe <span style="color:var(--danger);">*</span></label>
                    <div style="position:relative;">
                        <input type="password" name="password_confirmation" id="inputPassConf" class="form-control" style="padding-right:40px;" required>
                        <button type="button" onclick="togglePassVisibility('inputPassConf', this)" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--text-muted); cursor:pointer; padding:4px;">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>
            </div>

            <script>
            function togglePassVisibility(inputId, btn) {
                var input = document.getElementById(inputId);
                var icon = btn.querySelector('i');
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.className = 'bi bi-eye';
                } else {
                    input.type = 'password';
                    icon.className = 'bi bi-eye-slash';
                }
            }
            </script>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <a href="{{ route('employes.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection
