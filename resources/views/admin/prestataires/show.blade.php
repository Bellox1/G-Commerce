@extends('layouts.app')

@section('title', "Demande de {$demande->nom} {$demande->prenom}")
@section('subtitle', 'Détail de la candidature partenaire')

@section('content')
<div style="margin-bottom: 16px;">
    <a href="{{ route('admin.prestataires') }}" style="color:var(--primary); text-decoration:none;">
        <i class="bi bi-arrow-left"></i> Retour à la liste
    </a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h3><i class="bi bi-person"></i> Informations du candidat</h3>
    </div>
    <div style="padding: 20px; display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
        <div>
            <strong>Nom :</strong> {{ $demande->nom }} {{ $demande->prenom }}
        </div>
        <div>
            <strong>Email :</strong> {{ $demande->email }}
        </div>
        <div>
            <strong>Téléphone :</strong> {{ $demande->telephone }}
        </div>
        <div>
            <strong>Entreprise :</strong> {{ $demande->entreprise ?? '—' }}
        </div>
        <div>
            <strong>Date :</strong> {{ $demande->created_at->format('d/m/Y à H:i') }}
        </div>
        <div>
            <strong>Statut :</strong>
            @if($demande->statut === 'en_attente')
                <span class="badge badge-warning">En attente</span>
            @elseif($demande->statut === 'approuve')
                <span class="badge badge-success">Approuvé</span>
            @else
                <span class="badge badge-danger">Rejeté</span>
            @endif
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div class="card-header">
        <h3><i class="bi bi-chat-left-text"></i> Motivation</h3>
    </div>
    <div style="padding: 20px;">
        {{ $demande->motivation ?? 'Aucune motivation renseignée.' }}
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-clipboard-check"></i> Questionnaire (21 questions)</h3>
    </div>
    @php
        $labels = [
            'Ville / Zone',
            'Activité principale',
            'Nombre de commerces dans le réseau',
            'Secteurs ciblés',
            'Type de commerçants ciblés',
            'Nb commerçants contactés / mois',
            'Canaux de vente',
            'Expérience vente logiciel',
            'Atout principal pour convaincre',
            'Les commerçants utilisent-ils un logiciel',
            'Problème principal des commerçants',
            'Aisance à expliquer les avantages',
            'Confort démonstration client',
            'Suivi post-vente',
            'Temps dédié / semaine',
            'Véhicule pour terrain',
            'Commerçants dans d\'autres villes',
            'Formation acceptée ?',
            'Objectif vente / mois',
            'Préférence commissions (récurrentes / ponctuelles)',
            'Motivation',
        ];
        $answers = $demande->questionnaire ?? [];
    @endphp
    @if(empty($answers))
        <div style="padding: 20px; color:var(--text-muted);">
            Aucun questionnaire rempli.
        </div>
    @else
        <div style="padding: 20px;">
            @foreach($labels as $i => $label)
                <div style="padding: 10px 0; {{ $i > 0 ? 'border-top: 1px solid var(--border);' : '' }}">
                    <strong style="color:var(--primary);">{{ $i + 1 }}. {{ $label }}</strong>
                    <div style="margin-top: 4px; color: var(--text);">
                        {{ $answers[$i] ?? '—' }}
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@if($demande->statut === 'en_attente')
<div style="margin-top: 20px; display: flex; gap: 10px;">
    <form action="{{ route('admin.prestataires.valider', $demande->id) }}" method="POST" data-no-api="true">
        @csrf
        <button type="submit" class="btn btn-success" onclick="return confirm('Approuver cette demande ? Le compte partenaire sera créé automatiquement.')">
            <i class="bi bi-check-lg"></i> Approuver
        </button>
    </form>
    <form action="{{ route('admin.prestataires.rejeter', $demande->id) }}" method="POST" data-no-api="true">
        @csrf
        <button type="submit" class="btn btn-danger" onclick="return confirm('Rejeter cette demande ?')">
            <i class="bi bi-x-lg"></i> Rejeter
        </button>
    </form>
</div>
@endif
@endsection
