@extends('layouts.app')
@section('title', 'Modifier la Société : ' . $tenant->nom)

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Retour
    </a>
</div>

<form action="{{ route('tenants.update', $tenant) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="page-grid page-grid-2">
        <!-- Informations d'identité -->
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-building"></i> Fiche d'identité</h3>
            </div>
            
            <div class="form-group">
                <label class="form-label">Nom / Raison Sociale *</label>
                <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $tenant->nom) }}" required placeholder="Ex : SAIMOUS">
                @error('nom') <small style="color: var(--danger);">{{ $message }}</small> @enderror
            </div>

            <div class="form-group">
                <label class="form-label">Marque Commerciale</label>
                <input type="text" name="marque" class="form-control" value="{{ old('marque', $tenant->marque) }}" placeholder="Ex : RICCI">
            </div>

            <div class="form-group">
                <label class="form-label">Secteur d'activité</label>
                <input type="text" name="activite" class="form-control" value="{{ old('activite', $tenant->activite) }}" placeholder="Ex : Importation et vente de produits alimentaires">
            </div>
        </div>

        <!-- Localisation et Contacts -->
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-geo-alt"></i> Localisation & Statut</h3>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Pays *</label>
                    <select name="pays" class="form-control" required>
                        <option value="BJ" {{ old('pays', $tenant->pays) == 'BJ' ? 'selected' : '' }}>Bénin (BJ)</option>
                        <option value="NG" {{ old('pays', $tenant->pays) == 'NG' ? 'selected' : '' }}>Nigeria (NG)</option>
                        <option value="TG" {{ old('pays', $tenant->pays) == 'TG' ? 'selected' : '' }}>Togo (TG)</option>
                        <option value="CI" {{ old('pays', $tenant->pays) == 'CI' ? 'selected' : '' }}>Côte d'Ivoire (CI)</option>
                        <option value="GH" {{ old('pays', $tenant->pays) == 'GH' ? 'selected' : '' }}>Ghana (GH)</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Ville</label>
                    <input type="text" name="ville" class="form-control" value="{{ old('ville', $tenant->ville) }}" placeholder="Ex : Cotonou">
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $tenant->telephone) }}" placeholder="Ex : +229 97 00 00 00">
                </div>
                <div class="form-group">
                    <label class="form-label">Adresse email de la Société</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $tenant->email) }}" placeholder="Ex : email@societe.com">
                    @error('email') <small style="color: var(--danger);">{{ $message }}</small> @enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Statut du compte</label>
                <select name="actif" class="form-control" required>
                    <option value="1" {{ old('actif', $tenant->actif) ? 'selected' : '' }}>Actif (Autorisé à se connecter)</option>
                    <option value="0" {{ !old('actif', $tenant->actif) ? 'selected' : '' }}>Inactif (Connexion bloquée)</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: flex-end;">
        <a href="{{ route('tenants.show', $tenant) }}" class="btn btn-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> Enregistrer les modifications
        </button>
    </div>
</form>
@endsection
