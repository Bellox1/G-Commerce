@extends('layouts.app')
@section('title', 'Détails du Transfert ' . $transfert->reference)
@section('page-title', 'Transfert : ' . $transfert->reference)

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
    <div class="card" style="background: white;">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                @if($transfert->statut === 'receptionne' || $transfert->statut === 'livre')
                    <span class="badge badge-success" style="margin-bottom: 6px;"><i class="bi bi-check-circle-fill"></i> {{ $transfert->statut === 'livre' ? 'Livré' : 'Réceptionné' }}</span>
                @elseif($transfert->statut === 'en_transit')
                    <span class="badge badge-warning" style="margin-bottom: 6px;"><i class="bi bi-truck"></i> En transit</span>
                @else
                    <span class="badge badge-gray" style="margin-bottom: 6px;"><i class="bi bi-clock"></i> En attente</span>
                @endif
                <h2 style="font-size: 1.4rem; font-weight: 700;">Transfert {{ $transfert->reference }}</h2>
                <div style="font-size: .8rem; color: var(--text-muted); margin-top: 4px;">
                    <i class="bi bi-calendar"></i> Créé le {{ $transfert->created_at->fr('d F Y à H:i') }} par <strong>{{ $transfert->user?->name }}</strong>
                    @if($transfert->user?->role)
                        <span style="background:#f1f5f9; color:#475569; border-radius:20px; padding:1px 7px; font-size:.7rem; font-weight:600; margin-left:4px;">{{ ucfirst($transfert->user->role) }}</span>
                    @endif
                </div>
            </div>
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <a href="{{ route('transferts.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Liste
                </a>
                @if($transfert->statut === 'en_transit')
                    <a href="{{ route('transferts.edit', $transfert) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> Modifier
                    </a>
                    <form method="POST" action="{{ route('transferts.reception', $transfert) }}" onsubmit="return confirm('Confirmer la réception de ce transfert ? Le stock entrera dans le magasin destination.');">
                        @csrf
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Valider la réception
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="page-grid page-grid-3">
        {{-- Colonne gauche : Détails + Produits --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-info-circle"></i> Détails du Transfert</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 500; font-size: .85rem;">Magasin Source :</span>
                        <span style="font-weight: 600; font-size: .85rem; color: var(--danger);">{{ $transfert->magasinSource?->nom }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 500; font-size: .85rem;">Magasin Destination :</span>
                        <span style="font-weight: 600; font-size: .85rem; color: var(--success);">{{ $transfert->magasinDestination?->nom }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 500; font-size: .85rem;">Date du transfert :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ $transfert->date_transfert ? \Carbon\Carbon::parse($transfert->date_transfert)->fr('d F Y') : '—' }}</span>
                    </div>
                    @if($transfert->notes)
                    <div style="display: flex; flex-direction: column; gap: 4px; padding: 12px 16px;">
                        <span style="font-weight: 500; font-size: .85rem;">Notes :</span>
                        <span style="font-size: .85rem; color: var(--text-muted);">{{ $transfert->notes }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h3><i class="bi bi-box"></i> Produits transférés</h3>
                    <span class="badge badge-primary" style="font-size:.85rem; padding:6px 12px; background:var(--primary); color:#fff; border-radius:20px;">
                        Total: {{ number_format($transfert->produits->sum('quantite') ?: ($transfert->quantite ?? 0), 0, ',', ' ') }} unité(s)
                    </span>
                </div>
                <div class="card-body" style="padding: 0;">
                    @if($transfert->produits && $transfert->produits->count())
                        @if($transfert->statut === 'en_transit')
                        <form method="POST" action="{{ route('transferts.reception', $transfert) }}" onsubmit="return confirm('Confirmer la réception ? Le stock sera ajusté aux deux magasins selon les quantités reçues.');">
                            @csrf
                            <div class="table-wrap" style="padding: 8px 16px; border: none; margin-bottom: 0;">
                                <table style="width:100%; border-collapse:collapse; font-size:.85rem;">
                                    <thead>
                                        <tr>
                                            <th style="text-align:left; padding:8px 4px; border-bottom:1px solid var(--border);">Produit</th>
                                            <th style="text-align:right; padding:8px 4px; border-bottom:1px solid var(--border);">Déclarée</th>
                                            <th style="text-align:right; padding:8px 4px; border-bottom:1px solid var(--border);">Reçue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($transfert->produits as $tp)
                                        <tr>
                                            <td style="padding:8px 4px; border-bottom:1px solid var(--border); font-weight:500;">{{ $tp->produit?->nom }}</td>
                                            <td style="padding:8px 4px; border-bottom:1px solid var(--border); text-align:right; font-weight:600;">{{ $tp->quantite }}</td>
                                            <td style="padding:8px 4px; border-bottom:1px solid var(--border); text-align:right;">
                                                <input type="number" min="0" name="produits[{{ $tp->produit_id }}][quantite_recue]" value="{{ $tp->quantite }}" class="form-control" style="width:90px; display:inline-block; text-align:right; font-weight:700; color:var(--primary);">
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr style="font-weight:700; background:rgba(0,0,0,0.02);">
                                            <td style="padding:10px 4px;">TOTAL TRANSFÉRÉ</td>
                                            <td style="padding:10px 4px; text-align:right;">{{ number_format($transfert->produits->sum('quantite'), 0, ',', ' ') }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div style="padding: 12px 16px;">
                                <button type="submit" class="btn btn-success" style="width:100%; justify-content:center;">
                                    <i class="bi bi-check-circle"></i> Valider la réception
                                </button>
                            </div>
                        </form>
                        @else
                        <div class="table-wrap" style="padding: 8px 16px; border: none; margin-bottom: 0;">
                            <table style="width:100%; border-collapse:collapse; font-size:.85rem;">
                                <thead>
                                    <tr>
                                        <th style="text-align:left; padding:8px 4px; border-bottom:1px solid var(--border);">Produit</th>
                                        <th style="text-align:right; padding:8px 4px; border-bottom:1px solid var(--border);">Déclarée</th>
                                        <th style="text-align:right; padding:8px 4px; border-bottom:1px solid var(--border);">Reçue</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transfert->produits as $tp)
                                    <tr>
                                        <td style="padding:8px 4px; border-bottom:1px solid var(--border); font-weight:500;">{{ $tp->produit?->nom }}</td>
                                        <td style="padding:8px 4px; border-bottom:1px solid var(--border); text-align:right; font-weight:600;">{{ $tp->quantite }}</td>
                                        <td style="padding:8px 4px; border-bottom:1px solid var(--border); text-align:right; font-weight:600; color: {{ $tp->quantite_recue !== null && $tp->quantite_recue != $tp->quantite ? 'var(--warning)' : 'var(--success)' }};">{{ $tp->quantite_recue ?? $tp->quantite }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr style="font-weight:700; background:rgba(0,0,0,0.02);">
                                        <td style="padding:10px 4px;">TOTAL</td>
                                        <td style="padding:10px 4px; text-align:right;">{{ number_format($transfert->produits->sum('quantite'), 0, ',', ' ') }}</td>
                                        <td style="padding:10px 4px; text-align:right; color:var(--success);">{{ number_format($transfert->produits->sum('quantite_recue') ?: $transfert->produits->sum('quantite'), 0, ',', ' ') }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                        @endif
                    @elseif($transfert->produit)
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px;">
                        <span style="font-weight: 500; font-size: .85rem;">Produit :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ $transfert->produit?->nom }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px;">
                        <span style="font-weight: 500; font-size: .85rem;">Quantité :</span>
                        <span style="font-weight: 700; font-size: .85rem;">{{ $transfert->quantite }} Carton</span>
                    </div>
                    @else
                    <div style="padding: 24px; text-align: center; color: var(--text-muted); font-size: .85rem;">
                        <i class="bi bi-inbox" style="font-size: 2rem; display:block; margin-bottom:8px;"></i>
                        Aucun produit enregistré
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Colonne droite : Statut livraison + Livreur --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            {{-- Carte statut livraison --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-truck"></i> Statut de livraison</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div style="padding: 20px 16px; display: flex; align-items: center; gap: 14px; border-bottom: 1px solid var(--border);">
                        @if($transfert->statut === 'receptionne' || $transfert->statut === 'livre')
                            <div style="width:48px; height:48px; background:#dcfce7; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0;">
                                <i class="bi bi-patch-check-fill" style="color:var(--success);"></i>
                            </div>
                            <div>
                                <div style="font-size:1rem; font-weight:700; color:var(--success);">{{ $transfert->statut === 'livre' ? 'LIVRÉ' : 'RÉCEPTIONNÉ' }}</div>
                                <div style="font-size:.8rem; color:var(--text-muted);">Les marchandises ont été reçues</div>
                            </div>
                        @elseif($transfert->statut === 'en_transit')
                            <div style="width:48px; height:48px; background:#fef3c7; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0;">
                                <i class="bi bi-truck" style="color:var(--warning);"></i>
                            </div>
                            <div>
                                <div style="font-size:1rem; font-weight:700; color:var(--warning);">EN TRANSIT</div>
                                <div style="font-size:.8rem; color:var(--text-muted);">En cours d'acheminement</div>
                            </div>
                        @else
                            <div style="width:48px; height:48px; background:#f1f5f9; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0;">
                                <i class="bi bi-clock-history" style="color:#64748b;"></i>
                            </div>
                            <div>
                                <div style="font-size:1rem; font-weight:700; color:#64748b;">EN ATTENTE</div>
                                <div style="font-size:.8rem; color:var(--text-muted);">Pas encore expédié</div>
                            </div>
                        @endif
                    </div>

                    @if($transfert->date_livraison)
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 500; font-size: .85rem;">Date de livraison :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ $transfert->date_livraison->fr('d F Y') }}</span>
                    </div>
                    @endif

                    @if($transfert->livreur)
                    <div style="display: flex; justify-content: space-between; align-items: center; padding: 12px 16px;">
                        <span style="font-weight: 500; font-size: .85rem;">Contrôleur assigné :</span>
                        <span style="display:flex; align-items:center; gap:6px; font-weight: 600; font-size: .85rem;">
                            <span style="width:28px; height:28px; background:var(--primary); color:#fff; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:700;">
                                {{ strtoupper(substr($transfert->livreur->name, 0, 2)) }}
                            </span>
                            {{ $transfert->livreur->name }}
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Récapitulatif des magasins --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-arrow-left-right"></i> Trajet</h3>
                </div>
                <div class="card-body">
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; background: #fee2e2; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-shop" style="color: var(--danger); font-size: .9rem;"></i>
                            </div>
                            <div>
                                <div style="font-size: .7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; font-weight: 600;">Départ</div>
                                <div style="font-weight: 700; font-size: .95rem; color: var(--danger);">{{ $transfert->magasinSource?->nom ?? '—' }}</div>
                            </div>
                        </div>
                        <div style="display: flex; align-items: center; padding-left: 17px;">
                            <div style="width: 2px; height: 30px; background: var(--border);"></div>
                            <i class="bi bi-chevron-down" style="font-size: .7rem; color: var(--text-muted); margin-left: 4px;"></i>
                        </div>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; background: #dcfce7; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <i class="bi bi-shop" style="color: var(--success); font-size: .9rem;"></i>
                            </div>
                            <div>
                                <div style="font-size: .7rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: .5px; font-weight: 600;">Arrivée</div>
                                <div style="font-weight: 700; font-size: .95rem; color: var(--success);">{{ $transfert->magasinDestination?->nom ?? '—' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
