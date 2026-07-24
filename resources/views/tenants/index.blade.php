@extends('layouts.app')
@section('title', 'Gestion des Sociétés (Multi-Tenant)')
@section('page-title', 'Sociétés')

@section('actions')
<a href="{{ route('tenants.create') }}" class="btn btn-primary">
    <i class="bi bi-plus-circle-fill"></i> Nouvelle Société
</a>
@endsection

@section('content')
<div style="background:#fff7ed; border:1px solid #fed7aa; border-radius:10px; padding:14px 18px; margin-bottom:20px; display:flex; align-items:flex-start; gap:12px;">
    <i class="bi bi-exclamation-triangle-fill" style="color:#ea580c; font-size:1.3rem; margin-top:2px;"></i>
    <div>
        <strong style="color:#9a3412;">Attention :</strong>
        <span style="color:#9a3412;">Ne créez pas de société par ici si vous voulez des commissions. Toute société créée depuis cette page ne sera pas liée à un partenaire et ne générera aucune commission. Si vous êtes originaire de la plateforme et ne souhaitez pas en créer, c'est possible. Pour générer des commissions, utilisez l'espace <a href="{{ route('prestataire.tenants.create') }}" style="font-weight:700; text-decoration:underline;">prestataire</a>.</span>
    </div>
</div>

@if($stats)
<div class="stats-grid" style="grid-template-columns: repeat(6, 1fr); margin-bottom: 24px;">
    <div class="stat-card">
        <div class="stat-icon orange">
            <i class="bi bi-people"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['nbPartenaires'] }}</div>
            <div class="stat-lbl">Partenaires</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="bi bi-receipt"></i>
        </div>
        <div>
            <div class="stat-val">{{ $stats['nbVentes'] }}</div>
            <div class="stat-lbl">Offres Vendues</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="bi bi-cash-stack"></i>
        </div>
        <div>
            <div class="stat-val">{{ number_format($stats['totalPrixVente'], 0, ',', ' ') }} F</div>
            <div class="stat-lbl">Total Ventes (Offres)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red">
            <i class="bi bi-hourglass-split"></i>
        </div>
        <div>
            <div class="stat-val">{{ number_format($stats['totalAPayer'], 0, ',', ' ') }} F</div>
            <div class="stat-lbl">À payer aux Partenaires</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon blue">
            <i class="bi bi-wallet2"></i>
        </div>
        <div>
            <div class="stat-val">{{ number_format($stats['totalRegle'], 0, ',', ' ') }} F</div>
            <div class="stat-lbl">Déjà Réglé</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green">
            <i class="bi bi-piggy-bank"></i>
        </div>
        <div>
            <div class="stat-val">{{ number_format($stats['revenuNet'], 0, ',', ' ') }} F</div>
            <div class="stat-lbl">Revenu Net (Plateforme)</div>
        </div>
    </div>
</div>
@endif

@if($societesExpirees->isNotEmpty())
<div style="background:#fef2f2; border:1px solid #fecaca; border-radius:12px; padding:20px; margin-bottom:24px;">
    <div style="display:flex; align-items:center; gap:10px; margin-bottom:16px;">
        <i class="bi bi-exclamation-triangle-fill" style="color:#dc2626; font-size:1.4rem;"></i>
        <h3 style="margin:0; color:#991b1b; font-size:1.1rem; font-weight:700;">Offres expirées — Renouvellement requis</h3>
    </div>
    <p style="color:#991b1b; font-size:0.85rem; margin-bottom:16px;">Les sociétés suivantes ont une offre expirée. Leurs utilisateurs sont en lecture seule. Renouvelez l'offre pour rétablir l'accès complet.</p>

    <div style="display:flex; flex-direction:column; gap:12px;">
        @foreach($societesExpirees as $s)
        <div style="background:#fff; border:1px solid #fecaca; border-radius:10px; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; gap:16px; flex-wrap:wrap;">
            <div>
                <div style="font-weight:700; font-size:0.95rem; color:#1f2937;">{{ $s->nom }}</div>
                <div style="font-size:0.8rem; color:#6b7280;">
                    Offre : {{ $s->offre_code }} — Expirée le {{ $s->offre_expires_at ? $s->offre_expires_at->format('d/m/Y') : 'N/A' }}
                    @if($s->partenaire) — Partenaire : {{ $s->partenaire->name }} @endif
                </div>
            </div>
            <form action="{{ route('tenants.renew', $s) }}" method="POST" style="display:flex; gap:8px; align-items:center;">
                @csrf
                <select name="offre_code" class="form-control" style="width:auto; padding:6px 10px; font-size:0.85rem;" required>
                    @foreach($rules as $r)
                        <option value="{{ $r->code }}">{{ $r->nom }} — {{ number_format($r->prix, 0, ',', ' ') }} F</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary btn-sm" style="padding:6px 14px; white-space:nowrap;">
                    <i class="bi bi-arrow-repeat"></i> Renouveler
                </button>
            </form>
        </div>
        @endforeach
    </div>
</div>
@endif

@if($stats && $stats['offresVendues']->isNotEmpty())
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:20px; margin-bottom:24px;">
    <!-- Classement des offres -->
    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-trophy"></i> Performance des Offres</h3>
        </div>
        <div style="padding:16px;">
            <div style="display:flex; gap:12px; margin-bottom:20px;">
                @if($stats['topVendu'])
                <div style="flex:1; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:14px; text-align:center;">
                    <i class="bi bi-fire" style="color:#16a34a; font-size:1.5rem;"></i>
                    <div style="font-weight:800; font-size:1.1rem; color:#16a34a; margin-top:4px;">{{ $stats['topVendu']->nom_offre }}</div>
                    <div style="font-size:0.8rem; color:#15803d;">Plus vendue ({{ $stats['topVendu']->nb_ventes }} ventes)</div>
                </div>
                @endif
                @if($stats['topRevenue'])
                <div style="flex:1; background:#eff6ff; border:1px solid #bfdbfe; border-radius:10px; padding:14px; text-align:center;">
                    <i class="bi bi-cash-coin" style="color:#2563eb; font-size:1.5rem;"></i>
                    <div style="font-weight:800; font-size:1.1rem; color:#2563eb; margin-top:4px;">{{ $stats['topRevenue']->nom_offre }}</div>
                    <div style="font-size:0.8rem; color:#1d4ed8;">Meilleur revenu ({{ number_format($stats['topRevenue']->total_ventes, 0, ',', ' ') }} F)</div>
                </div>
                @endif
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Offre</th>
                            <th style="text-align:center;">Prix</th>
                            <th style="text-align:center;">Vendues</th>
                            <th style="text-align:right;">Revenu Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stats['offresVendues'] as $o)
                        <tr>
                            <td style="font-weight:600;">{{ $o->nom_offre }}</td>
                            <td style="text-align:center;">{{ number_format($o->prix, 0, ',', ' ') }} F</td>
                            <td style="text-align:center;">
                                <span class="badge badge-gray" style="font-size:0.9rem;">{{ $o->nb_ventes }}</span>
                            </td>
                            <td style="text-align:right; font-weight:700; color:var(--primary);">{{ number_format($o->total_ventes, 0, ',', ' ') }} F</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Résumé rapide -->
    <div class="card">
        <div class="card-header">
            <h3><i class="bi bi-bar-chart-line"></i> Résumé</h3>
        </div>
        <div style="padding:20px; display:flex; flex-direction:column; gap:16px;">
            @foreach($stats['offresVendues'] as $o)
            <div>
                <div style="display:flex; justify-content:space-between; margin-bottom:4px;">
                    <span style="font-weight:600; font-size:0.875rem;">{{ $o->nom_offre }}</span>
                    <span style="font-size:0.8rem; color:var(--text-muted);">{{ $o->nb_ventes }} vente(s)</span>
                </div>
                <div style="background:#e2e8f0; border-radius:20px; height:8px; overflow:hidden;">
                    <div style="height:100%; border-radius:20px; background:var(--primary); width:{{ $stats['nbVentes'] > 0 ? round(($o->nb_ventes / $stats['nbVentes']) * 100) : 0 }}%;"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endif

<div class="card">
    <div class="card-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-building"></i> Liste des Sociétés clientes
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ method_exists($tenants, 'total') ? $tenants->total() : $tenants->count() }}</span>
        </h3>
    </div>

    <div class="table-search-wrap">
        <div class="table-search-field">
            <i class="bi bi-search table-search-icon"></i>
            <input type="text" class="table-search-input" placeholder="Rechercher une société...">
        </div>
        <span class="table-search-count"></span>
    </div>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nom de la Société</th>
                    <th>Marque</th>
                    <th>Activité</th>
                    <th>Ville/Pays</th>
                    <th>Téléphone</th>
                    <th>Email principal</th>
                    <th style="text-align: center;">Magasins</th>
                    <th style="text-align: center;">Utilisateurs</th>
                    <th style="text-align: center;">Statut</th>
                    <th style="text-align: center; width: 150px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tenants as $t)
                <tr>
                    <td style="font-weight: 700; color: var(--primary);">
                        <a href="{{ route('tenants.show', $t) }}" style="text-decoration: none; color: inherit;">
                            {{ $t->nom }}
                        </a>
                    </td>
                    <td>{{ $t->marque ?? '-' }}</td>
                    <td>{{ $t->activite ?? '-' }}</td>
                    <td>{{ $t->ville ?? '-' }}, {{ $t->pays }}</td>
                    <td>{{ $t->telephone ?? '-' }}</td>
                    <td>{{ $t->email ?? '-' }}</td>
                    <td style="text-align: center;">
                        <span class="badge badge-gray font-weight-bold" style="font-size: 0.9rem;">
                            {{ $t->magasins_count }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        <span class="badge badge-gray font-weight-bold" style="font-size: 0.9rem;">
                            {{ $t->users_count }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        @if($t->actif)
                            <span class="badge badge-success"><i class="bi bi-shield-check"></i> Actif</span>
                        @else
                            <span class="badge badge-danger"><i class="bi bi-shield-slash"></i> Inactif</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a href="{{ route('tenants.show', $t) }}" class="btn btn-secondary btn-sm" style="padding: 6px 10px;" title="Gérer">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('tenants.edit', $t) }}" class="btn btn-secondary btn-sm" style="padding: 6px 10px;" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="{{ route('tenants.destroy', $t) }}" method="POST" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette société ? Tous ses magasins et données associés seront indisponibles.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 6px 10px;" title="Supprimer">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucune société enregistrée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($tenants->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: center;">
        {{ $tenants->links() }}
    </div>
    @endif
</div>
@endsection
