@extends('layouts.app')
@section('title', 'Détails Créance ' . $dette->client?->nomComplet())
@section('page-title', 'Créance Client')

@push('styles')
<style>
    .dette-totals-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 16px;
        margin-top: 20px;
        border-top: 1px solid var(--border);
        padding-top: 16px;
    }
    @media (max-width: 768px) {
        .dette-totals-grid {
            grid-template-columns: 1fr !important;
            gap: 12px !important;
        }
        #bdette-page .page-grid-3 { gap: 16px; }
    }
    @media (max-width: 640px) {
        #bdette-page table th, #bdette-page table td {
            padding: 8px 6px;
            font-size: 0.8rem;
        }
        #bdette-page table th:nth-child(3),
        #bdette-page table td:nth-child(3),
        #bdette-page table th:nth-child(4),
        #bdette-page table td:nth-child(4) {
            display: none;
        }
    }
</style>
@endpush

@section('content')
<div class="page-grid page-grid-3" id="bdette-page">
    
    {{-- Section gauche : Détails et Historique des versements --}}
    <div style="display: flex; flex-direction: column; gap: 20px;">
        
        {{-- Fiche Dette --}}
        <div class="card">
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

                <div class="dette-totals-grid">
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

        {{-- Date d'échéance --}}
        <div class="card">
            <div class="card-body">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:12px;">
                    <div>
                        <div style="font-size:.75rem; color:var(--text-muted);">Date d'échéance</div>
                        <div style="font-size:1.1rem; font-weight:700; margin-top:2px;">
                            @if($dette->date_echeance)
                                {{ $dette->date_echeance->format('d/m/Y') }}
                                @if($dette->estEnRetard())
                                    <span class="badge badge-danger">En retard</span>
                                @elseif($dette->date_echeance->isToday())
                                    <span class="badge badge-warning">Aujourd'hui</span>
                                @elseif($dette->date_echeance->isFuture())
                                    <span class="badge badge-success">Dans {{ max(1, (int) ceil(now()->diffInDays($dette->date_echeance))) }} jour(s)</span>
                                @endif
                            @else
                                <span style="color:var(--text-muted); font-weight:400;">Non définie</span>
                            @endif
                        </div>
                    </div>
                    <form method="POST" action="{{ route('dettes.echeance', $dette) }}" style="display:flex; gap:8px; align-items:center; flex-wrap:wrap;">
                        @csrf
                        @method('PUT')
                        <select name="echeance_option" class="form-control echeance-select" style="min-width:140px; font-size:.85rem;">
                            <option value="">-- Modifier --</option>
                            <option value="today">Aujourd'hui</option>
                            <option value="tomorrow">Demain</option>
                            <option value="after_tomorrow">Après-demain</option>
                            <option value="6_days">Dans 6 jours</option>
                            <option value="2_weeks">Dans 2 semaines</option>
                            <option value="1_month">Dans 1 mois</option>
                            <option value="custom">Personnalisé...</option>
                        </select>
                        <input type="date" name="date_echeance_custom" class="form-control echeance-custom" style="display:none; width:140px; font-size:.85rem;">
                        <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-calendar-check"></i></button>
                    </form>
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

@push('scripts')
<script>
    document.querySelectorAll('.echeance-select').forEach(function(select) {
        select.addEventListener('change', function() {
            const customInput = this.closest('form').querySelector('.echeance-custom');
            if (customInput) {
                customInput.style.display = this.value === 'custom' ? 'block' : 'none';
                if (this.value !== 'custom') {
                    const map = { 'today': 0, 'tomorrow': 1, 'after_tomorrow': 2, '6_days': 6, '2_weeks': 14, '1_month': 30 };
                    if (map[this.value] !== undefined) {
                        const d = new Date();
                        d.setDate(d.getDate() + map[this.value]);
                        customInput.value = d.toISOString().split('T')[0];
                    }
                }
            }
        });
    });
</script>
@endpush
@endsection
