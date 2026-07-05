@extends('layouts.app')
@section('title', 'Nouveau Client')
@section('page-title', 'Nouveau Client')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-plus-circle"></i> Ajouter une fiche client</h3>
        <a href="{{ route('clients.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
    
    <div class="card-body">
        <form method="POST" action="{{ route('clients.store') }}">
            @csrf
            
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Nom complet <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="nom" class="form-control" placeholder="Ex: Sanni Adam" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Numéro de téléphone</label>
                    <input type="text" name="telephone" class="form-control" placeholder="Ex: +229 97000000">
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Limite de crédit autorisée (FCFA)</label>
                    <input type="number" name="limite_credit" class="form-control" value="500000" min="0">
                    <small style="color: var(--text-muted); font-size: .75rem;">Le système bloquera ou alertera si les dettes en cours dépassent cette limite.</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Adresse physique</label>
                    <input type="text" name="adresse" class="form-control" placeholder="Ex: Cotonou, Sainte Rita, Rue 125">
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <a href="{{ route('clients.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> Enregistrer le Client</button>
            </div>
        </form>
    </div>
</div>
@endsection
