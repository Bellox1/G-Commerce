@extends('layouts.app')
@section('title', 'Détails Créance ' . $dette->client?->nomComplet())
@section('page-title', 'Créance Client')

@section('content')
<div class="page-grid page-grid-3">
    
    {{-- Section gauche : Détails et Historique des versements --}}
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        {{-- Fiche Dette --}}
        <div class="card" style="border-left: 4px solid {{ $dette->montant_restant <= 0 ? 'var(--success)' : 'var(--danger)' }};">
            <div class="card-body">
                <span class="badge {{ $dette->montant_restant <= 0 ? 'badge-success' : 'badge-danger' }}" style="margin-bottom: 8px;">
                    {{ $dette->montant_restant <= 0 ? 'Soldée' : 'Impayée / Active' }}
                </span>
                <h2 style="font-size: 1.4rem; font-weight: 700;">Créance de {{ $dette->client?->nomComplet() }}</h2>
                <p style="font-size: .8rem; color: var(--text-muted); margin-top: 4px;">
                    Générée le {{ $dette->created_at->format('d/m/Y') }} lors de la facture 
                    <a href="{{ route('ventes.show', $dette->vente_id) }}" style="color: var(--primary); font-weight: 600; text-decoration: none;">
                        {{ $dette->vente?->reference }}
                    </a>
                </p>

                <div style="display:grid; grid-template-columns: 1fr 1fr 1fr; gap:16px; margin-top:20px; border-top: 1px solid var(--border); padding-top: 16px;">
                    <div>
                        <div style="font-size: .75rem; color: var(--text-muted);">Montant Initial</div>
                        <div style="font-size: 1.2rem; font-weight: 750;">{{ number_format($dette->montant_initial, 0, ',', ' ') }} F</div>
                    </div>
                    <div>
                        <div style="font-size: .75rem; color: var(--text-muted); color: var(--success);">Remboursé</div>
                        <div style="font-size: 1.2rem; font-weight: 750; color: var(--success);">{{ number_format($dette->montant_paye, 0, ',', ' ') }} F</div>
                    </div>
                    <div>
                        <div style="font-size: .75rem; color: var(--text-muted); color: var(--danger);">Reste à payer</div>
                        <div style="font-size: 1.2rem; font-weight: 770; color: var(--danger);">{{ number_format($dette->montant_restant, 0, ',', ' ') }} F</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Historique des versements --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-clock-history"></i> Historique des Versements</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date & Heure</th>
                            <th style="text-align: right;">Montant Versé</th>
                            <th>Moyen de Paiement</th>
                            <th>Opérateur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dette->paiements as $p)
                        <tr>
                            <td>{{ $p->created_at->format('d/m/Y H:i:s') }}</td>
                            <td style="text-align: right; font-weight: 700; color: var(--success);">
                                +{{ number_format($p->montant, 0, ',', ' ') }} FCFA
                            </td>
                            <td>
                                <span class="badge badge-gray"><i class="bi bi-cash"></i> Espèces</span>
                            </td>
                            <td>{{ $p->user?->name ?: 'Système' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" style="text-align: center; color: var(--text-muted); padding: 24px;">Aucun versement enregistré sur cette dette.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    {{-- Section droite : Formulaire d'encaissement --}}
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        @if($dette->montant_restant > 0)
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-cash-stack"></i> Enregistrer un versement</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('dettes.payer', $dette) }}">
                    @csrf
                    
                    <div class="form-group">
                        <label class="form-label">Montant à encaisser (CFA)</label>
                        <input type="number" name="montant" class="form-control" style="font-size: 1.15rem; font-weight: bold; color: var(--success);" 
                               min="1" max="{{ $dette->montant_restant }}" value="{{ $dette->montant_restant }}" required>
                        <small style="color: var(--text-muted); display: block; margin-top: 4px;">Le montant maximum est de {{ number_format($dette->montant_restant, 0, ',', ' ') }} FCFA.</small>
                    </div>

                    <button type="submit" class="btn btn-success" style="width: 100%; justify-content: center; margin-top: 10px;">
                        <i class="bi bi-check-circle"></i> Confirmer l'encaissement
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="card" style="background: #f8fafc; border: 1px dashed var(--border); padding: 32px; text-align: center; display: flex; flex-direction: column; align-items: center; gap: 10px;">
            <i class="bi bi-patch-check-fill" style="font-size: 3rem; color: var(--success);"></i>
            <h4 style="font-weight: 700;">Créance Soldée</h4>
            <p style="font-size: .8rem; color: var(--text-muted);">Ce client a complètement régularisé le paiement de cette facture.</p>
        </div>
        @endif

        <a href="{{ route('dettes.index') }}" class="btn btn-secondary" style="justify-content: center;">
            <i class="bi bi-arrow-left"></i> Liste des dettes
        </a>
    </div>

</div>
@endsection
