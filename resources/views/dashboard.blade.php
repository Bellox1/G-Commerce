@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('page-title', 'Tableau de bord')
@section('subtitle', $tenant->nom . ($tenant->marque ? ' - ' . $tenant->marque : ''))

@push('styles')
<style>
    @media (max-width: 640px) {
        .dash-date-form { flex-wrap: wrap; width: 100%; }
        .dash-date-form label { font-size: .75rem; }
        .dash-date-form input { max-width: 100%; flex: 1; min-width: 100px; }
        .dash-table td { font-size: .75rem; padding: 6px 6px; }
        .dash-table th { font-size: .65rem; padding: 6px 6px; }
        .dash-table .ref-date { font-size: .6rem; }
        .dash-footer { padding: 10px 12px !important; }
        .page-grid { gap: 12px; }
    }
</style>
@endpush

@section('actions')
<form method="GET" action="{{ route('dashboard') }}" class="dash-date-form" style="display:flex; align-items:center; gap:8px; margin-right:40px;">
    <label style="font-size:.85rem; font-weight:600; color:var(--text); white-space:nowrap;">Date :</label>
    <input type="date" name="date" value="{{ $date }}" onchange="this.form.submit()" style="padding:5px 10px; border:1px solid var(--border); border-radius:6px; font-size:.85rem; max-width:150px;">
    @if($date !== today()->format('Y-m-d'))
        <a href="{{ route('dashboard') }}" style="font-size:.8rem; color:var(--primary); text-decoration:none; white-space:nowrap;">Réinitialiser</a>
    @endif
</form>
@endsection

@section('content')

{{-- Bannière d'introduction de la société --}}
<div class="card" style="background: linear-gradient(135deg, var(--primary), var(--primary-light)); color: #fff; border: none; padding: 20px 24px; margin-bottom: 24px; display: flex; flex-direction: row; align-items: center; gap: 16px; border-radius: var(--radius-card); box-shadow: var(--shadow-card);">
    <div style="width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; flex-shrink: 0;">
        <i class="bi bi-building" style="color: #fff;"></i>
    </div>
    <div>
        <h2 style="margin: 0; font-size: 1.35rem; font-weight: 800; color: #fff; font-family: 'Montserrat', sans-serif;">{{ $tenant->nom }}</h2>
        <p style="margin: 2px 0 0; font-size: 0.85rem; color: rgba(255,255,255,0.85); font-family: 'Inter', sans-serif;">Espace commercial connecté &bull; Marque : {{ $tenant->marque ?? 'N/A' }}</p>
    </div>
</div>

{{-- ─── Stats ─── --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-currency-exchange"></i></div>
        <div>
            <div class="stat-val">{{ number_format($ventesJour, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Encaissé le {{ \Carbon\Carbon::parse($date)->format('d/m') }} (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <div class="stat-val">{{ number_format($ventesMois, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Ventes du mois (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-receipt"></i></div>
        <div>
            <div class="stat-val">{{ $nbVentesJour }}</div>
            <div class="stat-lbl">Ventes du {{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
        <div>
            <div class="stat-val">{{ number_format($dettePaiementsJour, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Dettes encaissées le {{ \Carbon\Carbon::parse($date)->format('d/m') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-credit-card-2-back"></i></div>
        <div>
            <div class="stat-val">{{ number_format($totalDettes, 0, ',', ' ') }}</div>
            <div class="stat-lbl">Dettes actives (FCFA)
                @if($dettesEnRetard > 0)
                    <span class="badge badge-danger" style="margin-left:4px;">{{ $dettesEnRetard }} en retard</span>
                @endif
            </div>
        </div>
    </div>
    @if(count($stockAlertes) > 0)
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
            <div class="stat-val">{{ count($stockAlertes) }}</div>
            <div class="stat-lbl">Produits en alerte stock</div>
        </div>
    </div>
    @endif
</div>

{{-- ─── Grille principale ─── --}}
<div class="page-grid page-grid-3">

    {{-- Dernières ventes --}}
    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-receipt"></i> Ventes du {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</h3>
            <a href="{{ route('ventes.create') }}" class="btn btn-primary btn-sm">
                <i class="bi bi-plus"></i> Nouvelle vente
            </a>
        </div>
        <div class="table-wrap">
            <table class="dash-table">
                <thead>
                    <tr>
                        <th>Référence</th>
                        <th>Client</th>
                        <th>Montant</th>
                        <th>Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dernieresVentes as $v)
                    <tr>
                        <td>
                            <a href="{{ route('ventes.show', $v) }}" style="color:var(--primary); font-weight:500; text-decoration:none;">
                                {{ $v->reference }}
                            </a>
                            <div class="ref-date" style="font-size:.7rem; color:var(--text-muted);">{{ $v->date_vente->format('d/m H:i') }}</div>
                        </td>
                        <td>@if($v->client){{ $v->client->nomComplet() }}@else<i style="color:#94a3b8">Anonyme</i>@endif</td>
                        <td style="font-weight:600;">{{ number_format($v->montant_total, 0, ',', ' ') }}</td>
                        <td>
                            @if($v->statut_paiement === 'paye')
                                <span class="badge badge-success">Payé</span>
                            @elseif($v->statut_paiement === 'partiel')
                                <span class="badge badge-warning">Partiel</span>
                            @else
                                <span class="badge badge-danger">Impayé</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" style="text-align:center; color:var(--text-muted); padding:24px;">Aucune vente ce jour</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="dash-footer" style="padding:12px 20px; border-top:1px solid var(--border);">
            <a href="{{ route('ventes.index') }}" style="font-size:.8rem; color:var(--primary); text-decoration:none;">
                Voir toutes les ventes <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>

    {{-- Colonne droite --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Stock alertes --}}
        @if(count($stockAlertes) > 0)
        <div class="card">
            <div class="card-header">
                <h3 style="color:var(--warning);"><i class="bi bi-exclamation-triangle"></i> Stock critique</h3>
            </div>
            <div class="card-body" style="padding:0;">
                @foreach($stockAlertes as $alerte)
                <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 16px; border-bottom:1px solid var(--border);">
                    <span style="font-size:.85rem; font-weight:500;">{{ $alerte['produit']->nom }}</span>
                    <span class="badge {{ $alerte['stock'] <= 0 ? 'badge-danger' : 'badge-warning' }}">
                        {{ $alerte['stock'] }} Carton
                    </span>
                </div>
                @endforeach
                <div style="padding:12px 16px;">
                    <a href="{{ route('arrivages.create') }}" class="btn btn-sm btn-primary">
                        <i class="bi bi-truck"></i> Commander un arrivage
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Top produits --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-bar-chart"></i> Top produits (mois)</h3>
            </div>
            <div class="card-body" style="padding:0;">
                @forelse($topProduits as $i => $p)
                <div style="display:flex; align-items:center; gap:10px; padding:10px 16px; border-bottom:1px solid var(--border);">
                    <span style="width:20px; height:20px; background:var(--primary); color:#fff; border-radius:50%;
                                 display:flex; align-items:center; justify-content:center; font-size:.7rem; font-weight:700; flex-shrink:0;">
                        {{ $i + 1 }}
                    </span>
                    <span style="font-size:.85rem; flex:1;">{{ $p->nom }}</span>
                    <span style="font-size:.8rem; font-weight:600; color:var(--success);">{{ $p->total_vendu }}</span>
                </div>
                @empty
                <div style="padding:24px; text-align:center; color:var(--text-muted); font-size:.85rem;">
                    Aucune vente ce mois
                </div>
                @endforelse
            </div>
        </div>

        {{-- Collaborateurs & Activité --}}
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-people"></i> Activité des employés</h3>
            </div>
            <div class="card-body" style="padding:0; max-height: 250px; overflow-y: auto;">
                @forelse($employes as $emp)
                    @php
                        $isOnline = false;
                        if ($emp->last_seen) {
                            $isOnline = \Carbon\Carbon::parse($emp->last_seen)->diffInMinutes(now()) < 5;
                        }
                    @endphp
                    <div style="display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid var(--border);">
                        {{-- Indicateur de statut en ligne/hors ligne --}}
                        <div style="position:relative;">
                            <div style="width:36px; height:36px; background:#e2e8f0; color:#475569; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:700; font-size:.85rem;">
                                {{ strtoupper(substr($emp->name, 0, 2)) }}
                            </div>
                            <span style="position:absolute; bottom:-1px; right:-1px; width:11px; height:11px; border-radius:50%; border:2px solid #fff;
                                         background: {{ $isOnline ? '#22c55e' : '#94a3b8' }};"></span>
                        </div>
                        <div style="flex:1; min-width:0;">
                            <div style="font-size:.85rem; font-weight:600; color:var(--text); text-overflow:ellipsis; overflow:hidden; white-space:nowrap; margin-bottom: 2px;">
                                {{ $emp->name }}
                            </div>
                            <div style="font-size:.72rem; color:var(--text-muted); text-transform:capitalize;">
                                {{ $emp->role }}
                            </div>
                        </div>
                        <div style="text-align:right; font-size:.75rem; color:var(--text-muted);">
                            @if($isOnline)
                                <span style="color:#22c55e; font-weight:650;">En ligne</span>
                            @elseif($emp->last_seen)
                                {{ \Carbon\Carbon::parse($emp->last_seen)->diffForHumans() }}
                            @else
                                <span style="color:#94a3b8; font-style:italic;">Jamais connecté</span>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="padding:24px; text-align:center; color:var(--text-muted); font-size:.85rem;">
                        Aucun autre employé enregistré
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection
