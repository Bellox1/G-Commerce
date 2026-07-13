@extends('layouts.app')

@section('title', 'Modifier ' . $tenant->nom)
@section('subtitle', 'Modifier les informations de la société')

@section('content')
<style>
    .required { color: #ef4444; }
</style>

<div style="margin-bottom: 20px;">
    <a href="{{ route('prestataire.mes-societes') }}" style="color:var(--primary); text-decoration:none;">
        <i class="bi bi-arrow-left"></i> Retour à mes sociétés
    </a>
</div>

<form action="{{ route('prestataire.tenants.update', $tenant) }}" method="POST" data-no-api="true">
    @csrf
    @method('PUT')

    <div class="card" style="width: 100%;">
        <div class="card-header">
            <h3><i class="bi bi-building"></i> Informations Société</h3>
        </div>

        <div class="form-group">
            <label class="form-label">Raison Sociale / Nom <span class="required">*</span></label>
            <input type="text" name="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom', $tenant->nom) }}" required placeholder="Ex : SAÏMOUS">
        </div>

        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Marque Commerciale</label>
                <input type="text" name="marque" class="form-control" value="{{ old('marque', $tenant->marque) }}" placeholder="Ex : RICCI">
            </div>
            <div class="form-group">
                <label class="form-label">Activité principale</label>
                <input type="text" name="activite" class="form-control" value="{{ old('activite', $tenant->activite) }}" placeholder="Ex : Import / Export">
            </div>
        </div>

        <div class="form-row form-row-2">
            <div class="form-group">
                <label class="form-label">Pays <span class="required">*</span></label>
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
                <label class="form-label">Email société</label>
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $tenant->email) }}" placeholder="Ex : contact@saimous.com">
            </div>
        </div>

        <div style="margin-top: 20px;">
            <button type="submit" class="btn btn-primary" style="width: 100%;">
                <i class="bi bi-check-circle-fill"></i> Enregistrer les modifications
            </button>
        </div>
    </div>
</form>
@endsection
