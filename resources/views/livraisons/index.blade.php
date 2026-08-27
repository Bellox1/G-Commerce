@extends('layouts.app')
@section('title', 'Suivi des Livraisons')
@section('page-title', 'Livraisons')

@section('content')
@php
    $nbEnAttente = $nbParStatut['en_attente'] ?? 0;
    $nbLivre     = $nbParStatut['livre'] ?? 0;
    $nbProbleme  = $nbParStatut['probleme'] ?? 0;
@endphp
{{-- Stats livraisons (compteurs) --}}
<div class="stats-grid" style="margin-bottom:20px;">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-truck-flatbed"></i></div>
        <div>
            <div class="stat-val">{{ $nbLivraisons }}</div>
            <div class="stat-lbl">Total livraisons</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-clock-history"></i></div>
        <div>
            <div class="stat-val">{{ $nbEnAttente }}</div>
            <div class="stat-lbl">En attente</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-patch-check-fill"></i></div>
        <div>
            <div class="stat-val" style="color:var(--success);">{{ $nbLivre }}</div>
            <div class="stat-lbl">Livrées</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <div>
            <div class="stat-val" style="color:var(--danger);">{{ $nbProbleme }}</div>
            <div class="stat-lbl">Problème</div>
        </div>
    </div>
</div>

<div class="card" style="margin-bottom: 20px;">
    <div style="display: flex; gap: 10px; flex-wrap: wrap; align-items: center; justify-content: space-between; margin-bottom: 12px;">
        <h3 style="margin: 0; display:flex; align-items:center; gap:8px;">
            <i class="bi bi-truck-flatbed"></i> Livraisons
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ $nbLivraisons }}</span>
        </h3>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
            @php
                $periodeActive = request('periode', 'aujourd_hui');
                $statutActive = request('statut');
                $periodeDefs = [
                    'avant_hier' => 'Avant-hier',
                    'hier' => 'Hier',
                    'aujourd_hui' => "Aujourd'hui",
                    'tous' => 'Tous',
                ];
            @endphp
            @foreach($periodeDefs as $p => $label)
                <a href="{{ route('livraisons.index', array_filter(['periode' => $p, 'statut' => $statutActive])) }}"
                   class="btn btn-sm {{ $periodeActive === $p ? 'btn-primary' : 'btn-secondary' }}">{{ $label }}</a>
            @endforeach
            <a href="{{ route('livraisons.index', array_filter(['periode' => 'perso', 'statut' => $statutActive, 'date_debut' => $dateDebut, 'date_fin' => $dateFin])) }}"
               class="btn btn-sm {{ $periodeActive === 'perso' ? 'btn-primary' : 'btn-secondary' }}">Personnaliser</a>
        </div>
    </div>

    @if($periodeActive === 'perso')
    <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 12px;">
        <input type="hidden" name="periode" value="perso">
        @if($statutActive)
            <input type="hidden" name="statut" value="{{ $statutActive }}">
        @endif
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

    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
        <a href="{{ route('livraisons.index', array_filter(['periode' => $periodeActive === 'perso' ? 'perso' : $periodeActive, 'date_debut' => $periodeActive === 'perso' ? $dateDebut : null, 'date_fin' => $periodeActive === 'perso' ? $dateFin : null])) }}"
           class="btn btn-sm {{ !request()->filled('statut') ? 'btn-primary' : 'btn-secondary' }}">Tous</a>
        <a href="{{ route('livraisons.index', array_filter(['periode' => $periodeActive === 'perso' ? 'perso' : $periodeActive, 'statut' => 'en_attente', 'date_debut' => $periodeActive === 'perso' ? $dateDebut : null, 'date_fin' => $periodeActive === 'perso' ? $dateFin : null])) }}"
           class="btn btn-sm {{ request('statut') === 'en_attente' ? 'btn-primary' : 'btn-secondary' }}">En attente</a>
        <a href="{{ route('livraisons.index', array_filter(['periode' => $periodeActive === 'perso' ? 'perso' : $periodeActive, 'statut' => 'livre', 'date_debut' => $periodeActive === 'perso' ? $dateDebut : null, 'date_fin' => $periodeActive === 'perso' ? $dateFin : null])) }}"
           class="btn btn-sm {{ request('statut') === 'livre' ? 'btn-primary' : 'btn-secondary' }}">Livré</a>
        <a href="{{ route('livraisons.index', array_filter(['periode' => $periodeActive === 'perso' ? 'perso' : $periodeActive, 'statut' => 'probleme', 'date_debut' => $periodeActive === 'perso' ? $dateDebut : null, 'date_fin' => $periodeActive === 'perso' ? $dateFin : null])) }}"
           class="btn btn-sm {{ request('statut') === 'probleme' ? 'btn-primary' : 'btn-secondary' }}">Problème</a>
    </div>
</div>

<div class="card">
    <div class="table-search-wrap">
        <div class="table-search-field">
            <i class="bi bi-search table-search-icon"></i>
            <input type="text" class="table-search-input" placeholder="Rechercher une livraison...">
        </div>
        <span class="table-search-count"></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Facture N°</th>
                    <th>Date vente</th>
                    <th>Magasin</th>
                    <th>Client</th>
                    <th style="text-align: right;">Montant Vendu</th>
                    <th style="text-align: center;">Statut Règlement</th>
                    <th style="text-align: center;">Statut Livraison</th>
                    <th>Contrôleur</th>
                    <th style="text-align: center; width: 100px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventes as $v)
                <tr>
                    <td style="font-weight: 600;">
                        <a href="{{ route('livraisons.show', $v) }}" style="color: var(--primary); text-decoration: none;">
                            {{ $v->reference }}
                        </a>
                    </td>
                    <td>{{ $v->date_vente->fr('d F Y H:i') }}</td>
                    <td><span class="badge badge-gray">{{ $v->magasin?->nom }}</span></td>
                    <td>{{ $v->client?->nomComplet() ?? 'Vente Directe (Anonyme)' }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ number_format($v->montant_total, 0, ',', ' ') }} FCFA</td>
                    <td style="text-align: center;">
                        @if($v->statut_paiement === 'paye')
                            <span class="badge badge-success">Réglé</span>
                        @elseif($v->statut_paiement === 'partiel')
                            <span class="badge badge-warning">Partiel</span>
                        @else
                            <span class="badge badge-danger">Non réglé</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($v->statut_livraison === 'livre')
                            <span class="badge badge-success"><i class="bi bi-patch-check-fill"></i> Livré</span>
                        @elseif($v->statut_livraison === 'probleme')
                            <span class="badge badge-danger"><i class="bi bi-exclamation-triangle-fill"></i> Problème</span>
                        @else
                            <span class="badge badge-warning"><i class="bi bi-clock-history"></i> En attente</span>
                        @endif
                    </td>
                    <td>{{ $v->livreur?->name ?? '-' }}</td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 6px; justify-content: center; flex-wrap: wrap;">
                            @if($v->statut_livraison !== 'livre')
                            <form method="POST" action="{{ route('livraisons.update-statut', $v) }}" style="display:inline;">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="statut_livraison" value="livre">
                                <button type="submit" class="btn btn-success btn-sm" style="padding: 6px 12px; font-weight:700; display:inline-flex; align-items:center; gap:5px;" title="Cocher et marquer rapidement comme livré">
                                    <i class="bi bi-check-square-fill" style="font-size:1rem;"></i> Marquer livré
                                </button>
                            </form>
                            @else
                            <span class="badge badge-success" style="display:inline-flex; align-items:center; gap:4px; padding:6px 10px;">
                                <i class="bi bi-check-circle-fill"></i> Livré
                            </span>
                            @endif
                            <a href="{{ route('livraisons.show', $v) }}" class="btn btn-secondary btn-sm" style="padding: 6px 10px;" title="Gérer">
                                <i class="bi bi-gear-fill"></i> Gérer
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucune livraison correspondante ou trouvée.</td>
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
