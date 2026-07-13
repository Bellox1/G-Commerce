@extends('layouts.app')
@section('title', 'Créer une Nouvelle Société')

@section('content')
<style>
    .required { color: #ef4444; }
    .pw-wrap { position: relative; }
    .pw-wrap input { padding-right: 42px; }
    .pw-toggle {
        position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
        background: none; border: none; cursor: pointer; color: var(--muted); font-size: 1.1rem;
    }
</style>

<div style="margin-bottom: 20px;">
    <a href="{{ route('tenants.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<form action="{{ route('tenants.store') }}" method="POST" data-no-api="true">
    @csrf
    
    <div class="page-grid page-grid-3">
        <!-- Informations Société -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-building"></i> Informations Société</h3>
                </div>

                <div class="form-group">
                    <label class="form-label">Raison Sociale / Nom <span class="required">*</span></label>
                    <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}" required placeholder="Ex : SAÏMOUS">
                </div>

                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Marque Commerciale</label>
                        <input type="text" name="marque" class="form-control" value="{{ old('marque') }}" placeholder="Ex : RICCI">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Activité principale</label>
                        <input type="text" name="activite" class="form-control" value="{{ old('activite') }}" placeholder="Ex : Import / Export alimentaire">
                    </div>
                </div>

                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Pays <span class="required">*</span></label>
                        <select name="pays" class="form-control" required>
                            <option value="BJ" {{ old('pays') == 'BJ' ? 'selected' : '' }}>Bénin (BJ)</option>
                            <option value="NG" {{ old('pays') == 'NG' ? 'selected' : '' }}>Nigeria (NG)</option>
                            <option value="TG" {{ old('pays') == 'TG' ? 'selected' : '' }}>Togo (TG)</option>
                            <option value="CI" {{ old('pays') == 'CI' ? 'selected' : '' }}>Côte d'Ivoire (CI)</option>
                            <option value="GH" {{ old('pays') == 'GH' ? 'selected' : '' }}>Ghana (GH)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Ville</label>
                        <input type="text" name="ville" class="form-control" value="{{ old('ville') }}" placeholder="Ex : Cotonou">
                    </div>
                </div>

                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Téléphone</label>
                        <input type="text" name="telephone" class="form-control" value="{{ old('telephone') }}" placeholder="Ex : +229 97 00 00 00">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email société</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Ex : contact@saimous.com">
                    </div>
                </div>

                <div class="form-group" style="margin-top: 15px;">
                    <label class="form-label">Type d'offre / Plan <span class="required">*</span></label>
                    <select name="offre_code" class="form-control @error('offre_code') is-invalid @enderror" required>
                        <option value="">-- Sélectionnez une offre --</option>
                        @foreach($rules as $rule)
                            <option value="{{ $rule->code }}" {{ old('offre_code') == $rule->code ? 'selected' : '' }}>
                                {{ $rule->nom }} — {{ number_format($rule->prix, 0, ',', ' ') }} FCFA (Commission : {{ number_format($rule->commission, 0, ',', ' ') }} FCFA)
                            </option>
                        @endforeach
                    </select>
                    @error('offre_code')
                        <span class="invalid-feedback" role="alert" style="display:block;"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>

                @if(auth()->user()->isSuperAdmin() && !empty($partners))
                <div class="form-group" style="margin-top: 15px;">
                    <label class="form-label">Partenaire affilié (optionnel)</label>
                    <select name="partenaire_id" class="form-control @error('partenaire_id') is-invalid @enderror">
                        <option value="">-- Aucun partenaire --</option>
                        @foreach($partners as $partner)
                            <option value="{{ $partner->id }}" {{ old('partenaire_id') == $partner->id ? 'selected' : '' }}>
                                {{ $partner->name }} ({{ $partner->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('partenaire_id')
                        <span class="invalid-feedback" role="alert" style="display:block;"><strong>{{ $message }}</strong></span>
                    @enderror
                </div>
                @endif
            </div>
        </div>

        <!-- Administrateur de la société -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-person-badge"></i> Administrateur principal</h3>
                </div>

                <div class="form-group">
                    <label class="form-label">Nom complet <span class="required">*</span></label>
                    <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required placeholder="Ex : Matinou BELLO">
                </div>

                <div class="form-group">
                    <label class="form-label">Email de connexion <span class="required">*</span></label>
                    <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror" value="{{ old('admin_email') }}" required placeholder="Ex : admin@saimous.com">
                </div>

                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="admin_telephone" class="form-control" value="{{ old('admin_telephone') }}" placeholder="Ex : +229 65 00 00 00">
                </div>

                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Mot de passe <span class="required">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" name="admin_password" class="form-control @error('admin_password') is-invalid @enderror" required placeholder="Min. 6 caractères">
                            <button type="button" class="pw-toggle" onclick="togglePw(this)"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmer <span class="required">*</span></label>
                        <div class="pw-wrap">
                            <input type="password" name="admin_password_confirmation" class="form-control" required placeholder="Confirmer">
                            <button type="button" class="pw-toggle" onclick="togglePw(this)"><i class="bi bi-eye"></i></button>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        <i class="bi bi-check-circle-fill"></i> Créer la Société & son Admin
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
function togglePw(btn) {
    const input = btn.parentElement.querySelector('input');
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye', 'bi-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('bi-eye-slash', 'bi-eye');
    }
}
</script>
@endsection
