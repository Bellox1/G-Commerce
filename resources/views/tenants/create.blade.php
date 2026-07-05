@extends('layouts.app')
@section('title', 'Créer une Nouvelle Société')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('tenants.index') }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<form action="{{ route('tenants.store') }}" method="POST">
    @csrf
    
    <div class="page-grid page-grid-3">
        <!-- Informations Société -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-building"></i> Informations Société</h3>
                </div>

                <div class="form-group">
                    <label class="form-label">Raison Sociale/Nom de la société *</label>
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
                        <label class="form-label">Pays *</label>
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
                        <label class="form-label">Adresse email de la Société</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Ex : contact@saimous.com">
                    </div>
                </div>
            </div>
        </div>

        <!-- Informations Administrateur par défaut -->
        <div>
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-person-badge"></i> Administrateur principal</h3>
                </div>

                <div class="form-group">
                    <label class="form-label">Nom complet de l'Administrateur *</label>
                    <input type="text" name="admin_name" class="form-control" value="{{ old('admin_name') }}" required placeholder="Ex : Matinou BELLO">
                </div>

                <div class="form-group">
                    <label class="form-label">Adresse email de connexion *</label>
                    <input type="email" name="admin_email" class="form-control @error('admin_email') is-invalid @enderror" value="{{ old('admin_email') }}" required placeholder="Ex : admin@saimous.com">
                </div>

                <div class="form-group">
                    <label class="form-label">N° de Téléphone</label>
                    <input type="text" name="admin_telephone" class="form-control" value="{{ old('admin_telephone') }}" placeholder="Ex : +229 65 00 00 00">
                </div>

                <div class="form-row form-row-2">
                    <div class="form-group">
                        <label class="form-label">Mot de Passe *</label>
                        <input type="password" name="admin_password" class="form-control" required placeholder="Min. 6 caractères">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirmer le Mot de Passe *</label>
                        <input type="password" name="admin_password_confirmation" class="form-control" required placeholder="Confirmer">
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
@endsection
