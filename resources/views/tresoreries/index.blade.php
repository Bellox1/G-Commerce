@extends('layouts.app')
@section('title', 'Trésorerie')
@section('page-title', 'Trésorerie')

@push('styles')
<style>
    .treso-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
    @media (max-width: 768px) { .treso-grid { grid-template-columns: 1fr; } }
    .treso-card { background: #fff; border-radius: var(--radius-card); padding: 20px; border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
    .treso-card .treso-val { font-size: 1.6rem; font-weight: 800; line-height: 1.1; }
    .treso-card .treso-lbl { font-size: .78rem; color: var(--text-muted); text-transform: uppercase; font-weight: 600; margin-top: 4px; }
    .treso-pos { color: var(--success); }
    .treso-neg { color: var(--danger); }
    .treso-neu { color: var(--primary); }

    .filter-bar { display: flex; gap: 12px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 20px; }
    .filter-bar .form-group { margin-bottom: 0; }
    .sens-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: .75rem; font-weight: 600; }
    .sens-entree { background: #dcfce7; color: #166534; }
    .sens-sortie { background: #fee2e2; color: #991b1b; }
    .sens-ca_jour { background: #dbeafe; color: #1d4ed8; }
</style>
@endpush

@section('actions')
    <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-sm"><i class="bi bi-arrow-left"></i> Retour</a>
@endsection

@section('content')

{{-- Cartes récapitulatives --}}
<div class="treso-grid">
    <div class="treso-card">
        <div class="treso-val treso-pos">{{ number_format($totalEntrees, 0, ',', ' ') }} F</div>
        <div class="treso-lbl"><i class="bi bi-arrow-down-left"></i> Total entrées</div>
    </div>
    <div class="treso-card">
        <div class="treso-val treso-neg">-{{ number_format($totalSorties, 0, ',', ' ') }} F</div>
        <div class="treso-lbl"><i class="bi bi-arrow-up-right"></i> Total sorties</div>
    </div>
    <div class="treso-card">
        <div class="treso-val treso-neu">{{ number_format($totalCaJour, 0, ',', ' ') }} F</div>
        <div class="treso-lbl"><i class="bi bi-graph-up-arrow"></i> CA du jour</div>
    </div>
</div>

{{-- Filtre rapide --}}
<div style="display:flex; gap:8px; margin-bottom:16px;">
    <a href="{{ route('tresoreries.index') }}"
       class="btn btn-sm {{ !request('date') && !request('date_debut') && !request('date_fin') ? 'btn-primary' : 'btn-secondary' }}">Tout</a>
    <a href="{{ route('tresoreries.index', ['date' => \Carbon\Carbon::today()->format('Y-m-d')]) }}"
       class="btn btn-sm {{ request('date') ? 'btn-primary' : 'btn-secondary' }}">Aujourd'hui</a>
</div>

{{-- Formulaire d'ajout --}}
<div class="card" style="margin-bottom: 24px;">
    <div class="card-header">
        <h3><i class="bi bi-plus-circle"></i> Enregistrer un mouvement</h3>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('tresoreries.store') }}" style="display: grid; gap: 16px; grid-template-columns: repeat(3, 1fr);">
            @csrf
            <div class="form-group">
                <label class="form-label" style="font-size: .8rem;">Date *</label>
                <input type="date" name="date" class="form-control" value="{{ old('date', date('Y-m-d')) }}" required>
            </div>
            <div class="form-group">
                <label class="form-label" style="font-size: .8rem;">Sens *</label>
                <select name="sens" class="form-control" required>
                    <option value="entree">Entrée (argent en caisse)</option>
                    <option value="sortie">Sortie (dépense / retrait)</option>
                    <option value="ca_jour">CA du jour (encaissement vente)</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label" style="font-size: .8rem;">Montant (FCFA) *</label>
                <input type="number" name="montant" class="form-control" min="1" required placeholder="0">
            </div>
            <div class="form-group">
                <label class="form-label" style="font-size: .8rem;">Libellé</label>
                <input type="text" name="libelle" class="form-control" maxlength="255" placeholder="Ex : Versement, achat, etc.">
            </div>
            <div class="form-group">
                <label class="form-label" style="font-size: .8rem;">Mode de paiement</label>
                <select name="mode_paiement" class="form-control">
                    <option value="Espèces" selected>Espèces</option>
                    <option value="Mobile Money">Mobile Money</option>
                    <option value="Chèque">Chèque</option>
                </select>
            </div>
            <div style="grid-column: 1 / -1;">
                <button type="submit" class="btn btn-primary"><i class="bi bi-check-circle"></i> Enregistrer</button>
            </div>
        </form>
    </div>
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('tresoreries.index') }}" class="filter-bar" style="background:#fff; border:1px solid var(--border); border-radius:var(--radius-card); padding:16px;">
    <div class="form-group">
        <label class="form-label" style="font-size: .8rem;">Du</label>
        <input type="date" name="date_debut" class="form-control" value="{{ request('date_debut') }}">
    </div>
    <div class="form-group">
        <label class="form-label" style="font-size: .8rem;">Au</label>
        <input type="date" name="date_fin" class="form-control" value="{{ request('date_fin') }}">
    </div>
    <div class="form-group">
        <label class="form-label" style="font-size: .8rem;">Sens</label>
        <select name="sens" class="form-control">
            <option value="">Tous</option>
            <option value="entree" {{ request('sens')=='entree' ? 'selected' : '' }}>Entrée</option>
            <option value="sortie" {{ request('sens')=='sortie' ? 'selected' : '' }}>Sortie</option>
            <option value="ca_jour" {{ request('sens')=='ca_jour' ? 'selected' : '' }}>CA du jour</option>
        </select>
    </div>
    <button type="submit" class="btn btn-secondary"><i class="bi bi-funnel"></i> Filtrer</button>
    <a href="{{ route('tresoreries.index') }}" class="btn btn-sm" style="border:1px solid var(--border);">Réinitialiser</a>
</form>

{{-- Tableau des mouvements --}}
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-list-ul"></i> Mouvements de trésorerie</h3>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Sens</th>
                    <th>Libellé</th>
                    <th>Mode</th>
                    <th style="text-align:right;">Montant (FCFA)</th>
                    <th>Enregistré par</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $item)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($item->date)->fr('d F Y') }}</td>
                    <td>
                        @if($item->sens === 'entree')
                            <span class="sens-badge sens-entree">Entrée</span>
                        @elseif($item->sens === 'sortie')
                            <span class="sens-badge sens-sortie">Sortie</span>
                        @else
                            <span class="sens-badge sens-ca_jour">CA du jour</span>
                        @endif
                    </td>
                    <td>{{ $item->libelle ?: '—' }}</td>
                    <td>{{ $item->mode_paiement ?: 'Espèces' }}</td>
                    <td style="text-align:right; font-weight:600; {{ $item->sens === 'sortie' ? 'color:var(--danger);' : 'color:var(--success);' }}">
                        {{ $item->sens === 'sortie' ? '-' : '+' }}{{ number_format($item->montant, 0, ',', ' ') }}
                    </td>
                    <td>{{ $item->user?->name ?? 'N/A' }}</td>
                    <td style="text-align:right;">
                        <form method="POST" action="{{ route('tresoreries.destroy', $item) }}" onsubmit="return confirm('Supprimer ce mouvement ?');" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" style="text-align:center; color:var(--text-muted); padding:24px;">Aucun mouvement enregistré</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($items->hasPages())
    <div style="padding: 12px 16px;">
        {{ $items->links() }}
    </div>
    @endif
</div>

@endsection
