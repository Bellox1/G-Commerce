@extends('layouts.app')
@section('title', 'Dette Société #' . $dette->id)
@section('page-title', 'Détails de la dette société')

@section('content')
<div class="page-grid page-grid-3" style="direction: ltr;">
    {{-- Colonne Gauche (1/3) : Formulaire paiement --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        @if($dette->montantRestant() > 0)
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-cash-stack"></i> Enregistrer un paiement</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('dettes-societe.payer', $dette) }}">
                    @csrf
                    <div class="form-group">
                        <label class="form-label">Montant à régler (FCFA)</label>
                        <input type="number" name="montant" class="form-control" style="font-size:1.15rem; font-weight:bold; color:var(--success);"
                               min="1" max="{{ $dette->montantRestant() }}" value="{{ $dette->montantRestant() }}" required>
                        <small style="color:var(--text-muted); display:block; margin-top:4px;">
                            Max : {{ number_format($dette->montantRestant(), 0, ',', ' ') }} F
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date</label>
                        <input type="date" name="date_paiement" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Mode de paiement</label>
                        <select name="mode_paiement" class="form-control" required>
                            <option value="especes">Espèces</option>
                            <option value="virement">Virement</option>
                            <option value="mobile_money">Mobile Money</option>
                            <option value="cheque">Chèque</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Notes</label>
                        <input type="text" name="notes" class="form-control" placeholder="Facultatif">
                    </div>
                    <button type="submit" class="btn btn-success" style="width:100%; justify-content:center; margin-top:10px;">
                        <i class="bi bi-check-circle"></i> Confirmer le paiement
                    </button>
                </form>
            </div>
        </div>
        @else
        <div class="card" style="background:#f8fafc; border:1px dashed var(--border); padding:24px; text-align:center; display:flex; flex-direction:column; align-items:center; gap:8px;">
            <i class="bi bi-patch-check-fill" style="font-size:2.5rem; color:var(--success);"></i>
            <h4 style="font-weight:700; margin:0;">Dette soldée</h4>
            <p style="font-size:.8rem; color:var(--text-muted); margin:0;">Cette dette est entièrement réglée.</p>
        </div>
        @endif

        <a href="{{ route('dettes-societe.index') }}" class="btn btn-secondary" style="justify-content:center;">
            <i class="bi bi-arrow-left"></i> Liste des dettes société
        </a>
    </div>

    {{-- Colonne Droite (2/3) : Fiche dette + Historique des paiements --}}
    <div style="grid-column: span 1; display: flex; flex-direction: column; gap: 20px;">
        {{-- Fiche dette --}}
        <div class="card">
            <div class="card-body">
                <span class="badge {{ $dette->montantRestant() <= 0 ? 'badge-success' : 'badge-danger' }}" style="margin-bottom: 8px;">
                    {{ $dette->statut === 'solde' ? 'Soldée' : ($dette->statut === 'partiel' ? 'Partiellement payée' : 'Active') }}
                </span>
                <h2 style="font-size:1.3rem; font-weight:700; margin-top:8px;">Dette société #{{ $dette->id }}</h2>
                <p style="font-size:.85rem; color:var(--text-muted); margin-top:4px;">
                    Créée le {{ $dette->date_dette->format('d/m/Y') }}
                </p>

                <div style="display:grid; grid-template-columns:1fr 1fr 1fr; gap:16px; margin-top:20px; border-top:1px solid var(--border); padding-top:16px;">
                    <div>
                        <div style="font-size:.75rem; color:var(--text-muted);">Montant total</div>
                        <div style="font-size:1.2rem; font-weight:750;">{{ number_format($dette->montant, 0, ',', ' ') }} F</div>
                    </div>
                    <div>
                        <div style="font-size:.75rem; color:var(--success);">Déjà payé</div>
                        <div style="font-size:1.2rem; font-weight:750; color:var(--success);">{{ number_format($dette->montant_paye, 0, ',', ' ') }} F</div>
                    </div>
                    <div>
                        <div style="font-size:.75rem; color:var(--danger);">Reste à payer</div>
                        <div style="font-size:1.2rem; font-weight:770; color:var(--danger);">{{ number_format($dette->montantRestant(), 0, ',', ' ') }} F</div>
                    </div>
                </div>

                <div style="margin-top:16px; border-top:1px solid var(--border); padding-top:12px;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; font-size:.85rem;">
                        <div>
                            <span style="color:var(--text-muted);">Fournisseur :</span>
                            <strong>{{ $dette->fournisseur?->nom ?? '—' }}</strong>
                        </div>
                        <div>
                            <span style="color:var(--text-muted);">Arrivage :</span>
                            <strong>{{ $dette->arrivage?->reference ?? '—' }}</strong>
                        </div>
                    </div>
                    @if($dette->description)
                    <div style="margin-top:8px; font-size:.85rem; color:var(--text-muted);">
                        <strong>Description :</strong> {{ $dette->description }}
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Historique paiements --}}
        @if($dette->paiements->count())
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-clock-history"></i> Historique des paiements</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th style="text-align:right;">Montant</th>
                            <th>Mode</th>
                            <th>Notes</th>
                            <th>Opérateur</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dette->paiements as $p)
                        <tr>
                            <td>{{ $p->date_paiement->format('d/m/Y') }}</td>
                            <td style="text-align:right; font-weight:700; color:var(--success);">+{{ number_format($p->montant, 0, ',', ' ') }} F</td>
                            <td><span class="badge badge-gray">{{ $p->mode_paiement }}</span></td>
                            <td>{{ $p->notes ?? '—' }}</td>
                            <td>{{ $p->user?->name ?? 'Système' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
