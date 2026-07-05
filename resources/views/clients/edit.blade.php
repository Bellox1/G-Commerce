@extends('layouts.app')
@section('title', 'Modifier le Client')
@section('page-title', 'Modifier le Client')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-pencil"></i> Modifier {{ $client->nomComplet() }}</h3>
        <a href="{{ route('clients.index') }}" class="btn btn-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
    </div>
    
    <div class="card-body">
        <form method="POST" action="{{ route('clients.update', $client) }}">
            @csrf
            @method('PUT')
            
            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Nom complet <span style="color:var(--danger);">*</span></label>
                    <input type="text" name="nom" class="form-control" value="{{ $client->nom }}" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Numéro de téléphone</label>
                    <input type="text" name="telephone" class="form-control" value="{{ $client->telephone }}">
                </div>
            </div>

            <div class="form-row form-row-2">
                <div class="form-group">
                    <label class="form-label">Limite de crédit autorisée (FCFA)</label>
                    <input type="number" name="limite_credit" class="form-control" value="{{ $client->limite_credit }}" min="0">
                </div>

                <div class="form-group">
                    <label class="form-label">Adresse physique</label>
                    <input type="text" name="adresse" class="form-control" value="{{ $client->adresse }}">
                </div>
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <a href="{{ route('clients.index') }}" class="btn btn-secondary">Annuler</a>
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Enregistrer les Modifications</button>
            </div>
        </form>
    </div>
</div>
@endsection
