@extends('layouts.app')
@section('title', 'Tableau de bord')
@section('page-title', '  de bord')

@push('styles')
<style>
    @keyframes pulse-dot { 0%,100% { opacity: 1; } 50% { opacity: 0.3; } }

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
<div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap; white-space:nowrap;">
<form method="GET" action="{{ route('dashboard') }}" class="dash-date-form" style="display:flex; align-items:center; gap:8px;">
    <label style="font-size:.85rem; font-weight:600; color:var(--text); white-space:nowrap;">Période :</label>
    <select name="periode" onchange="this.form.submit()" style="padding:5px 10px; border:1px solid var(--border); border-radius:6px; font-size:.85rem;">
        <option value="jour" {{ $periode==='jour' ? 'selected' : '' }}>Jour</option>
        <option value="semaine" {{ $periode==='semaine' ? 'selected' : '' }}>Semaine</option>
        <option value="mois" {{ $periode==='mois' ? 'selected' : '' }}>Mois</option>
        <option value="annee" {{ $periode==='annee' ? 'selected' : '' }}>Année</option>
    </select>
    <input type="date" name="date" id="dashDate" value="{{ $date }}" onchange="this.form.submit()" style="padding:5px 10px; border:1px solid var(--border); border-radius:6px; font-size:.85rem; max-width:150px;">
    <button type="button" onclick="shiftDash(-1)" title="Précédent" style="border:1px solid var(--border); background:#fff; border-radius:6px; padding:5px 8px; cursor:pointer; color:var(--primary);"><i class="bi bi-chevron-left"></i></button>
    <button type="button" onclick="shiftDash(1)" title="Suivant" style="border:1px solid var(--border); background:#fff; border-radius:6px; padding:5px 8px; cursor:pointer; color:var(--primary);"><i class="bi bi-chevron-right"></i></button>
    @if($date !== today()->format('Y-m-d') || $periode !== 'jour')
        <a href="{{ route('dashboard') }}" style="font-size:.8rem; color:var(--primary); text-decoration:none; white-space:nowrap;">Aujourd'hui</a>
    @endif
</form>
@if(auth()->user()->aAccesAdmin())
    <a href="{{ route('analytique') }}" class="btn btn-primary btn-sm" style="display:inline-flex; align-items:center; gap:6px; padding:8px 18px; font-size:.85rem; white-space:nowrap;">
        <i class="bi bi-bar-chart-line"></i> Analyse avancée
    </a>
@endif
</div>
<script>
function shiftDash(delta){
    const sel = document.querySelector('select[name=periode]');
    const periode = sel ? sel.value : 'jour';
    const inp = document.getElementById('dashDate');
    if(!inp) return;
    let d = new Date(inp.value);
    if(isNaN(d.getTime())) d = new Date();
    if(periode==='jour') d.setDate(d.getDate()+delta);
    else if(periode==='semaine') d.setDate(d.getDate()+delta*7);
    else if(periode==='mois') d.setMonth(d.getMonth()+delta);
    else if(periode==='annee') d.setFullYear(d.getFullYear()+delta);
    const pad=n=>String(n).padStart(2,'0');
    inp.value = d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate());
    inp.form.submit();
}
</script>
@endsection

@section('content')

{{-- Bannière d'introduction de la société --}}
@php
    $offreActive = $tenant->isOffreActive();
    $joursRestants = $tenant->offre_expires_at ? max(0, (int) now()->diffInDays($tenant->offre_expires_at, false)) : null;
    $nomOffre = $tenant->offre_code ? (\App\Models\CommissionRule::where('code', $tenant->offre_code)->value('nom') ?? $tenant->offre_code) : 'Aucune';
@endphp
<div style="display:flex; align-items:center; gap:10px; padding:10px 0; margin-bottom:16px; font-size:.85rem; border-bottom:1px solid var(--border);">
    <span style="font-weight:800; font-size:1.1rem; color:var(--text); font-family:'Space Grotesk',sans-serif;">{{ $tenant->nom }}.</span>
    <span style="display:inline-flex; align-items:center; gap:6px; color:var(--text-muted);">
        @if($offreActive)
            <span style="width:8px; height:8px; border-radius:50%; background:var(--success); animation:pulse-dot 1.5s infinite; flex-shrink:0;"></span>
            Offre {{ $nomOffre }}
        @else
            <span style="width:8px; height:8px; border-radius:50%; background:var(--danger); animation:pulse-dot 1.5s infinite; flex-shrink:0;"></span>
            Offre expirée
        @endif
    </span>
</div>

{{-- ─── Stats de période ─── --}}
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon blue"><i class="bi bi-currency-exchange"></i></div>
        <div>
            <div class="stat-val">@prix($encaissePeriode)</div>
            <div class="stat-lbl">Encaissé · {{ $periodeLabel }} (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-graph-up-arrow"></i></div>
        <div>
            <div class="stat-val">@prix($caPeriode)</div>
            <div class="stat-lbl">C.A. · {{ $periodeLabel }} (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-receipt"></i></div>
        <div>
            <div class="stat-val" style="color:var(--danger);">@prix($depensePeriode)</div>
            <div class="stat-lbl">Dépenses · {{ $periodeLabel }} (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon orange"><i class="bi bi-exclamation-triangle"></i></div>
        <div>
            <div class="stat-val" style="color:var(--warning);">@prix($creancesPeriode)</div>
            <div class="stat-lbl">Créances · {{ $periodeLabel }} (FCFA)</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon green"><i class="bi bi-cash-stack"></i></div>
        <div>
            <div class="stat-val">@prix($dettePaiementsJour)</div>
            <div class="stat-lbl">Dettes encaissées le {{ \Carbon\Carbon::parse($date)->fr('d F') }}</div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-credit-card-2-back"></i></div>
        <div>
            <div class="stat-val">@prix($totalDettes)</div>
            <div class="stat-lbl">Mes dettes clients (FCFA)
                @if($dettesEnRetard > 0)
                    <span class="badge badge-danger" style="margin-left:4px;">{{ $dettesEnRetard }} en retard</span>
                @endif
            </div>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon red"><i class="bi bi-building"></i></div>
        <div>
            <div class="stat-val" style="color:var(--danger);">@prix($totalDettesSociete)</div>
            <div class="stat-lbl">Mes dettes société (FCFA)
                <a href="{{ route('dettes-societe.index') }}" style="font-size:.7rem; color:var(--primary);">Voir</a>
            </div>
        </div>
    </div>
</div>

{{-- ─── Grille principale ─── --}}
<div class="page-grid page-grid-3">

    {{-- Colonne gauche : Ventes, Vendeurs, Dépenses --}}
    <div style="display:flex; flex-direction:column; gap:20px;">
        {{-- Dernières ventes --}}
        <div class="card" style="margin-bottom:0;">
            <div class="card-header">
                <h3><i class="bi bi-receipt"></i> Ventes du {{ \Carbon\Carbon::parse($date)->fr('d F Y') }} ({{ $nbVentesJour }})</h3>
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
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dernieresVentes as $v)
                        <tr>
                            <td>
                                <a href="{{ route('ventes.show', $v) }}" style="color:var(--primary); font-weight:500; text-decoration:none;">
                                    {{ $v->reference }}
                                </a>
                                <div class="ref-date" style="font-size:.7rem; color:var(--text-muted);">{{ $v->date_vente->fr('d F Y H:i') }}</div>
                            </td>
                            <td>@if($v->client){{ $v->client->nomComplet() }}@else<i style="color:#94a3b8">Anonyme</i>@endif</td>
                            <td style="font-weight:600;">@prix($v->montant_total)</td>
                            <td>
                                @if($v->statut_paiement === 'paye')
                                    <span class="badge badge-success">Payé</span>
                                @elseif($v->statut_paiement === 'partiel')
                                    <span class="badge badge-warning">Partiel</span>
                                @else
                                    <span class="badge badge-danger">Impayé</span>
                                @endif
                            </td>
                            <td style="text-align:right; white-space:nowrap;">
                                <a href="{{ route('ventes.show', $v) }}" class="btn btn-sm btn-primary" style="padding:3px 8px;" title="Imprimer la facture" onclick="window.open(this.href,'_blank');return false;">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" style="text-align:center; color:var(--text-muted); padding:24px;">Aucune vente ce jour</td></tr>
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

        {{-- Stimulateur CA + Trésorerie --}}
        <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
            <button type="button" onclick="openSim()" class="btn btn-primary" style="display:inline-flex; align-items:center; gap:8px;">
                <i class="bi bi-calculator"></i> Stimulateur CA
            </button>
            <a href="{{ route('tresoreries.index') }}" class="btn btn-dark" style="display:inline-flex; align-items:center; gap:8px; background:#111; color:#fff;">
                <i class="bi bi-cash"></i> Trésorerie
            </a>
        </div>

        {{-- Ventes par vendeur --}}
        @if(count($statsParPersonne) > 0)
        <div class="card" style="margin-bottom:0;">
            <div class="card-header">
                <h3><i class="bi bi-people"></i> Ventes par vendeur le {{ \Carbon\Carbon::parse($date)->fr('d F Y') }}</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Vendeur</th>
                            <th style="text-align: right;">Ventes (FCFA)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($statsParPersonne as $stat)
                        <tr>
                            <td style="font-weight: 600;">{{ $stat->user?->name ?? 'N/A' }}</td>
                            <td style="text-align: right;">@prix($stat->total_ca)</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Dépenses du jour --}}
        <div class="card" style="margin-bottom:0;">
            <div class="card-header">
                <h3><i class="bi bi-cash"></i> Enregistrer une dépense</h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('dashboard.depense.store') }}" enctype="multipart/form-data" style="display: flex; gap: 12px; align-items: flex-end; flex-wrap: wrap;">
                    @csrf
                    <input type="hidden" name="date" value="{{ $date }}">
                    <div style="flex: 1; min-width: 150px;">
                        <label class="form-label" style="font-size: .8rem;">Montant (FCFA) *</label>
                        <input type="number" name="montant" class="form-control" min="1" required placeholder="0">
                    </div>
                    <div style="flex: 2; min-width: 200px;" id="desc-group">
                        <label class="form-label" style="font-size: .8rem;">Description</label>
                        <input type="text" name="description" id="depense-desc" class="form-control" placeholder="Ex: Eau, transport, réparation..." maxlength="255">
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Ajouter</button>
                </form>

                <div style="margin-top: 16px;">
                    <h4 style="font-size: .9rem; font-weight: 600; margin-bottom: 8px; color: #475569; display: flex; justify-content: space-between; align-items: center;">
                        <span><i class="bi bi-list-ul"></i> Dépenses du {{ \Carbon\Carbon::parse($date)->fr('d F Y') }}</span>
                        <a href="{{ route('depenses.index') }}" style="font-size: .72rem; color: var(--primary); text-decoration: none;">Voir tout <i class="bi bi-arrow-right"></i></a>
                    </h4>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th style="text-align: right;">Montant (FCFA)</th>
                                    <th>Enregistré par</th>
                                    <th>Heure</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($depensesDuJour as $d)
                                <tr>
                                    <td>
                                    {{ $d->description ?: '-' }}
                                    </td>
                                    <td style="text-align: right; font-weight: 600; color: #dc2626;">@prix($d->montant)</td>
                                    <td>{{ $d->user?->name ?? 'N/A' }}</td>
                                    <td>{{ $d->created_at->format('H:i') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" style="text-align:center; color:var(--text-muted); padding:16px;">Aucune dépense ce jour</td>
                                </tr>
                                @endforelse
                            </tbody>
                            @if($depensesDuJour->count() > 0)
                            <tfoot>
                                <tr style="background: #f1f5f9; font-weight: 700;">
                                    <td>Total</td>
                                    <td style="text-align: right; color: #dc2626;">@prix($depenseJour)</td>
                                    <td colspan="2"></td>
                                </tr>
                            </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Colonne droite --}}
    <div style="display:flex; flex-direction:column; gap:20px;">

        {{-- Dettes en retard --}}
        @if(count($dettesEnRetardListe) > 0)
        <div class="card" style="margin-bottom:0;">
            <div class="card-header">
                <h3 style="color:var(--danger);"><i class="bi bi-exclamation-triangle-fill"></i> Créances en retard ({{ $dettesEnRetard }})</h3>
                <a href="{{ route('dettes.index') }}" style="font-size:.8rem; color:var(--primary); text-decoration:none;">
                    Gérer <i class="bi bi-arrow-right"></i>
                </a>
            </div>
            <div class="card-body" style="padding:0;">
                @foreach($dettesEnRetardListe as $dette)
                <a href="/dettes/{{ $dette->id }}" style="display:flex; align-items:center; gap:12px; padding:10px 16px; border-bottom:1px solid var(--border); text-decoration:none; color:inherit; transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <div style="flex:1; min-width:0;">
                        <span style="font-size:.85rem; font-weight:600;">{{ $dette->client?->nomComplet() ?: 'Anonyme' }}</span>
                        <div style="font-size:.7rem; color:var(--text-muted);">
                            Échéance : {{ $dette->date_echeance?->fr('d F Y') ?: 'N/A' }}
                            @if($dette->vente)
                                · {{ $dette->vente->reference }}
                            @endif
                        </div>
                    </div>
                    <span style="font-weight:700; color:var(--danger); white-space:nowrap;">
                        @prix($dette->montant_restant)
                    </span>
                    <span style="font-size:.75rem; color:var(--primary); white-space:nowrap;">
                        <i class="bi bi-wallet2"></i>
                    </span>
                </a>
                @endforeach
                <div style="padding:12px 16px;">
                    <a href="{{ count($dettesEnRetardListe) === 1 ? url('/dettes/' . $dettesEnRetardListe->first()->id) : route('dettes.index', ['statut' => 'en_retard']) }}" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-cash-stack"></i> {{ count($dettesEnRetardListe) === 1 ? 'Encaisser cette créance' : 'Voir toutes les créances en retard' }}
                    </a>
                </div>
            </div>
        </div>
        @endif

        {{-- Stock alertes --}}
        @if(count($stockAlertes) > 0)
        <div class="card" style="margin-bottom:0;">
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
        <div class="card" style="margin-bottom:0;">
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
    </div>
</div>

{{-- ─── Stimulateur de Chiffre d'Affaires Modal ─── --}}
<div id="simModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.45); z-index:1050; align-items:flex-start; justify-content:center; padding:24px; overflow:auto;">
    <div style="background:#fff; border-radius:14px; width:100%; max-width:640px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,.25);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 18px; border-bottom:1px solid var(--border);">
            <div>
                <h3 style="margin:0; font-size:1.05rem;"><i class="bi bi-graph-up"></i> Stimulateur de CA</h3>
                <small style="color:var(--text-muted);">Les produits sans stock sont ignorés. Total = Σ (stock × prix).</small>
            </div>
            <button type="button" onclick="closeSim()" style="background:none; border:none; font-size:1.5rem; line-height:1; cursor:pointer; color:var(--text-muted);">&times;</button>
        </div>

        <div style="display:flex; align-items:center; gap:10px; padding:10px 18px; border-bottom:1px solid var(--border); flex-wrap:wrap;">
            {{-- Sélecteur de dépôt --}}
            <div style="display:flex; align-items:center; gap:8px; flex:1; min-width:180px;">
                <i class="bi bi-storefront" style="color:var(--text-muted);"></i>
                <select id="simMagasinSelect" class="form-select form-select-sm" onchange="simChangeMagasin(this.value)" style="max-width:240px;">
                    <option value="all">Tous les dépôts</option>
                    @foreach($magasins as $mag)
                        <option value="{{ $mag->id }}">{{ $mag->nom }}</option>
                    @endforeach
                </select>
                <span id="simLoadingSpinner" style="display:none;" class="spinner-border spinner-border-sm text-primary" role="status"></span>
            </div>
            <button type="button" class="btn btn-sm btn-secondary" onclick="simRetirerTout()">Tout retirer</button>
            <button type="button" class="btn btn-sm btn-secondary" onclick="simAjouterTout()">Tout ajouter</button>
        </div>

        <div id="simList" style="flex:1; overflow:auto; padding:6px 18px;">
            @php $hasSim = false; @endphp
            @foreach($produits as $p)
                @php
                    $s  = $stockParProduit[$p->id] ?? 0;
                    $sc = $stockCartouchesParProduit[$p->id] ?? 0;
                @endphp
                @if($s > 0 || $sc > 0)
                    @php $hasSim = true; @endphp
                    <div class="sim-row" data-id="{{ $p->id }}" data-stock="{{ $s }}" data-cartouches="{{ $sc }}" data-prix="{{ (int) $p->prix_vente_conseille }}" data-acartouche="{{ $p->a_cartouche ? 1 : 0 }}" data-prixcartouche="{{ (int) $p->prix_cartouche_effectif }}">
                        <div style="flex:1; min-width:0;">
                            <div style="font-weight:600;">{{ $p->nom }}</div>
                            <div style="font-size:.8rem; color:var(--text-muted);">
                                {{ $s }} carton(s) × {{ (int) $p->prix_vente_conseille }} F
                                @if($p->a_cartouche && $sc > 0) • {{ $sc }} cartouche(s) × {{ (int) $p->prix_cartouche_effectif }} F @endif
                            </div>
                        </div>
                        <div class="sim-line" style="font-weight:700; min-width:120px; text-align:right;"></div>
                        <button type="button" class="btn btn-sm btn-danger" style="margin-left:10px;" onclick="simToggle(this)">Retirer</button>
                    </div>
                @endif
            @endforeach
            @if(!$hasSim)
                <div id="simEmpty" style="text-align:center; color:var(--text-muted); padding:40px;">Aucun produit en stock.</div>
            @endif
        </div>

        <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 18px; border-top:1px solid var(--border); background:#f8fafc;">
            <span style="font-weight:600; color:var(--text-muted);">Chiffre d'affaires estimé</span>
            <span id="simTotal" style="font-size:1.2rem; font-weight:800; color:var(--primary);">0 F</span>
        </div>
    </div>
</div>

@push('scripts')
<style>
    .sim-row {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid var(--border);
    }
    .sim-row.excluded { opacity: 0.45; }
</style>
<script>
// ─── Données initiales : tous les produits du Blade (tous dépôts) ───────────
const SIM_ALL_DATA = [
    @foreach($produits as $p)
        @php
            $s  = $stockParProduit[$p->id] ?? 0;
            $sc = $stockCartouchesParProduit[$p->id] ?? 0;
        @endphp
        @if($s > 0 || $sc > 0)
        {
            id: {{ $p->id }},
            nom: @json($p->nom),
            stock: {{ $s }},
            cartouches: {{ $sc }},
            prix: {{ (int)$p->prix_vente_conseille }},
            acartouche: {{ $p->a_cartouche ? 1 : 0 }},
            prixcartouche: {{ (int)$p->prix_cartouche_effectif }}
        },
        @endif
    @endforeach
];

let simCurrentMagasin = 'all';

function openSim() {
    document.getElementById('simMagasinSelect').value = 'all';
    simCurrentMagasin = 'all';
    simRenderRows(SIM_ALL_DATA);
    document.getElementById('simModal').style.display = 'flex';
    simRefresh();
}
function closeSim() { document.getElementById('simModal').style.display = 'none'; }
function formatFCFA(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' F'; }

function simChangeMagasin(val) {
    simCurrentMagasin = val;
    if (val === 'all') {
        simRenderRows(SIM_ALL_DATA);
        simRefresh();
        return;
    }
    // Charger via AJAX le stock du magasin sélectionné
    const spinner = document.getElementById('simLoadingSpinner');
    spinner.style.display = 'inline-block';
    fetch(`/api/produits?magasin_id=${val}&per_page=200`, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(resp => {
        const raw = resp.data?.data ?? resp.data ?? [];
        const list = Array.isArray(raw) ? raw : (Array.isArray(raw.data) ? raw.data : []);
        const rows = list
            .filter(p => (Number(p.stock) || 0) > 0 || (Number(p.stock_cartouches) || 0) > 0)
            .map(p => ({
                id: p.id,
                nom: p.nom,
                stock: Number(p.stock ?? p.stock_actuel ?? 0),
                cartouches: Number(p.stock_cartouches ?? 0),
                prix: Number(p.prix_vente_conseille || 0),
                acartouche: p.a_cartouche ? 1 : 0,
                prixcartouche: Number(p.prix_cartouche_effectif || 0),
            }));
        simRenderRows(rows);
        simRefresh();
    })
    .catch(() => {
        simRenderRows([]);
    })
    .finally(() => { spinner.style.display = 'none'; });
}

function simRenderRows(data) {
    const container = document.getElementById('simList');
    const emptyEl = document.getElementById('simEmpty');
    if (!data || data.length === 0) {
        container.innerHTML = '<div id="simEmpty" style="text-align:center; color:var(--text-muted); padding:40px;">Aucun produit en stock dans ce dépôt.</div>';
        const t = document.getElementById('simTotal');
        if (t) t.textContent = '0 F';
        return;
    }
    container.innerHTML = data.map(p => `
        <div class="sim-row" data-id="${p.id}" data-stock="${p.stock}" data-cartouches="${p.cartouches}" data-prix="${p.prix}" data-acartouche="${p.acartouche}" data-prixcartouche="${p.prixcartouche}">
            <div style="flex:1; min-width:0;">
                <div style="font-weight:600;">${p.nom}</div>
                <div style="font-size:.8rem; color:var(--text-muted);">
                    ${p.stock} carton(s) × ${p.prix} F
                    ${p.acartouche && p.cartouches > 0 ? ` • ${p.cartouches} cartouche(s) × ${p.prixcartouche} F` : ''}
                </div>
            </div>
            <div class="sim-line" style="font-weight:700; min-width:120px; text-align:right;"></div>
            <button type="button" class="btn btn-sm btn-danger" style="margin-left:10px;" onclick="simToggle(this)">Retirer</button>
        </div>
    `).join('');
}

function simLineVal(row) {
    const stock = +row.dataset.stock;
    const cart  = +row.dataset.cartouches;
    const prix  = +row.dataset.prix;
    const ac    = +row.dataset.acartouche;
    const pc    = +row.dataset.prixcartouche;
    let v = stock * prix;
    if (ac) v += cart * pc;
    return v;
}

function simRefresh() {
    let total = 0;
    const rows = document.querySelectorAll('#simList .sim-row');
    rows.forEach(row => {
        const lineEl = row.querySelector('.sim-line');
        if (row.classList.contains('excluded')) {
            if (lineEl) lineEl.textContent = '—';
            return;
        }
        const v = simLineVal(row);
        total += v;
        if (lineEl) lineEl.textContent = formatFCFA(v);
    });
    const t = document.getElementById('simTotal');
    if (t) t.textContent = formatFCFA(total);
}

function simToggle(btn) {
    const row = btn.closest('.sim-row');
    if (row.classList.contains('excluded')) {
        row.classList.remove('excluded');
        btn.textContent = 'Retirer';
        btn.classList.add('btn-danger');
        btn.classList.remove('btn-secondary');
    } else {
        row.classList.add('excluded');
        btn.textContent = 'Ajouter';
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-secondary');
    }
    simRefresh();
}

function simRetirerTout() {
    document.querySelectorAll('#simList .sim-row').forEach(r => {
        r.classList.add('excluded');
        const b = r.querySelector('button');
        if (b) { b.textContent = 'Ajouter'; b.classList.remove('btn-danger'); b.classList.add('btn-secondary'); }
    });
    simRefresh();
}

function simAjouterTout() {
    document.querySelectorAll('#simList .sim-row').forEach(r => {
        r.classList.remove('excluded');
        const b = r.querySelector('button');
        if (b) { b.textContent = 'Retirer'; b.classList.add('btn-danger'); b.classList.remove('btn-secondary'); }
    });
    simRefresh();
}
</script>
@endpush

@endsection
