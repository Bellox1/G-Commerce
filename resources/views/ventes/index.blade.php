@extends('layouts.app')
@section('title', 'Suivi des Ventes')
@section('page-title', 'Facturation & Ventes')

@section('content')

<div class="card">
    <div class="card-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-receipt"></i> Historique des ventes
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ method_exists($ventes, 'total') ? $ventes->total() : $ventes->count() }}</span>
        </h3>
        <a href="{{ route('ventes.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Nouvelle Vente
        </a>
    </div>

    @php
        $periodeActive = request('periode', 'aujourd_hui');
        $periodeDefs = [
            'avant_hier' => 'Avant-hier',
            'hier' => 'Hier',
            'aujourd_hui' => "Aujourd'hui",
            'tous' => 'Tous',
        ];
    @endphp
    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
        @foreach($periodeDefs as $p => $label)
            <a href="{{ route('ventes.index', array_filter(['periode' => $p])) }}"
               class="btn btn-sm {{ $periodeActive === $p ? 'btn-primary' : 'btn-secondary' }}">{{ $label }}</a>
        @endforeach
        <a href="{{ route('ventes.index', array_filter(['periode' => 'perso', 'date_debut' => $dateDebut, 'date_fin' => $dateFin])) }}"
           class="btn btn-sm {{ $periodeActive === 'perso' ? 'btn-primary' : 'btn-secondary' }}">Personnaliser</a>
    </div>

    @if($periodeActive === 'perso')
    <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 12px;">
        <input type="hidden" name="periode" value="perso">
        <div>
            <label class="form-label" style="margin-bottom: 4px;">Du</label>
            <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut }}">
        </div>
        <div>
            <label class="form-label" style="margin-bottom: 4px;">Au</label>
            <input type="date" name="date_fin" class="form-control" value="{{ $dateFin }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
    </form>
    @endif

    <div class="table-search-wrap">
        <div class="table-search-field">
            <i class="bi bi-search table-search-icon"></i>
            <input type="text" class="table-search-input" placeholder="Rechercher une vente (référence, client, magasin...)">
        </div>
        <span class="table-search-count"></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Facture N°</th>
                    <th>Date</th>
                    <th>Établi par</th>
                    <th>Magasin</th>
                    <th>Client</th>
                    <th style="text-align: right;">Montant Total</th>
                    <th style="text-align: right;">Montant Payé</th>
                    <th style="text-align: right;">Reste à payer</th>
                    <th style="text-align: center;">Statut règlement</th>
                    <th style="text-align: center; width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventes as $v)
                <tr>
                    <td style="font-weight: 600;">
                        <a href="{{ route('ventes.show', $v) }}" style="color: var(--primary); text-decoration: none;">
                            {{ $v->reference }}
                        </a>
                    </td>
                    <td>{{ $v->date_vente->fr('d F Y H:i') }}</td>
                    <td>{{ $v->user?->name }}</td>
                    <td><span class="badge badge-gray">{{ $v->magasin?->nom }}</span></td>
                    <td>{{ $v->client?->nomComplet() ?? 'Vente Directe (Anonyme)' }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ number_format($v->montant_total, 0, ',', ' ') }} FCFA</td>
                    <td style="text-align: right; color: var(--success); font-weight: 500;">
                        {{ number_format($v->montant_paye, 0, ',', ' ') }} FCFA
                    </td>
                    <td style="text-align: right; color: {{ $v->montant_reste > 0 ? 'var(--danger)' : 'var(--text-muted)' }}; font-weight: 600;">
                        {{ number_format($v->montant_reste, 0, ',', ' ') }} FCFA
                    </td>
                    <td style="text-align: center;">
                        @if($v->statut_paiement === 'paye')
                            <span class="badge badge-success"><i class="bi bi-check-lg"></i> Reglé</span>
                        @elseif($v->statut_paiement === 'partiel')
                            <span class="badge badge-warning"><i class="bi bi-clock"></i> Partiel</span>
                        @else
                            <span class="badge badge-danger"><i class="bi bi-exclamation-triangle"></i> Non reglé</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 4px; justify-content: center;">
                            <a href="{{ route('ventes.show', $v) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('ventes.edit', $v) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucune vente enregistrée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($ventes->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: center;">
        {{ $ventes->links() }}
    </div>
    @endif
</div>
@endsection
