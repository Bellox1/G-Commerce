@extends('layouts.app')
@section('title', 'Catalogue des Produits')
@section('page-title', 'Catalogue des Produits')

@section('content')
<div class="card">
    <form method="GET" action="{{ route('produits.index') }}" id="filterForm">
        <div class="card-header" style="flex-wrap: wrap; gap: 12px;">
            <h3 style="display:flex; align-items:center; gap:8px;">
                <i class="bi bi-tag"></i> Tous les Articles
                <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ method_exists($produits, 'total') ? $produits->total() : $produits->count() }}</span>
            </h3>
            <div style="display: flex; gap: 8px; align-items: center; flex-wrap: wrap;">
                <select name="magasin_id" class="form-control" style="width: auto; display: inline-block;" onchange="document.getElementById('filterForm').submit()">
                    <option value="all" {{ $selectedMagasinId == 'all' ? 'selected' : '' }}>Tous les produits</option>
                    @foreach($magasins as $m)
                        <option value="{{ $m->id }}" {{ $selectedMagasinId == $m->id ? 'selected' : '' }}>
                            {{ $m->nom }}
                        </option>
                    @endforeach
                </select>
                <select name="sort" class="form-control" style="width: auto; display: inline-block;" onchange="document.getElementById('filterForm').submit()">
                    <option value="az" {{ (request()->get('sort', 'az') == 'az') ? 'selected' : '' }}>Nom : A → Z</option>
                    <option value="za" {{ (request()->get('sort') == 'za') ? 'selected' : '' }}>Nom : Z → A</option>
                </select>
                @if(Auth::user()->peutModifierCatalogues())
                <a href="{{ route('produits.create') }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle"></i> Nouveau Produit
                </a>
                @endif
                <button type="button" class="btn btn-secondary btn-sm" onclick="openSim()">
                    <i class="bi bi-graph-up"></i> Stimulateur CA
                </button>
            </div>
        </div>

        <div class="table-search-wrap">
            <div class="table-search-field">
                <i class="bi bi-search table-search-icon"></i>
                <input type="text" name="q" value="{{ request('q') }}" class="table-search-input" placeholder="Rechercher par nom…"
                       oninput="clearTimeout(window.__qTimer); window.__qTimer = setTimeout(() => document.getElementById('filterForm').submit(), 400);"
                       onkeydown="if(event.key === 'Enter'){ event.preventDefault(); document.getElementById('filterForm').submit(); }">
            </div>
            <span class="table-search-count"></span>
        </div>
    </form>
    
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th style="width: 50px;">Image</th>
                    <th>Réf</th>
                    <th>Désignation</th>
                    <th>Prix Vente</th>
                    <th>Stock {{ $selectedMagasinId == 'all' ? '(total)' : '' }}</th>
                    <th>Cartouche</th>
                    <th>Alerte</th>
                    <th style="width: 100px; text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($produits as $p)
                @php
                    $s = $stockParProduit[$p->id] ?? 0;
                    $sc = $stockCartouchesParProduit[$p->id] ?? 0;
                    $seuil = (int) ($p->seuil_alerte ?? 0);
                    $enAlerte = $s <= 5 || ($seuil > 0 && $s <= $seuil);
                @endphp
                <tr class="{{ $enAlerte ? 'stock-alert-row' : '' }}">
                    <td>
                        <a href="{{ route('produits.show', $p) }}">
                        @if($p->image)
                            @php $imgSrc = str_starts_with($p->image, 'http') ? $p->image : asset('storage/' . $p->image); @endphp
                            <img src="{{ $imgSrc }}" alt="{{ $p->nom }}" style="width:36px; height:36px; border-radius:6px; object-fit:cover; border:1px solid var(--border);" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                            <span style="display:none; width:36px; height:36px; border-radius:6px; background:#f1f5f9; align-items:center; justify-content:center; color:#94a3b8; font-size:.8rem;">
                                <i class="bi bi-image"></i>
                            </span>
                        @else
                            <span style="display:inline-flex; width:36px; height:36px; border-radius:6px; background:#f1f5f9; align-items:center; justify-content:center; color:#94a3b8; font-size:.8rem;">
                                <i class="bi bi-image"></i>
                            </span>
                        @endif
                        </a>
                    </td>
                    <td><a href="{{ route('produits.show', $p) }}" style="color: var(--primary); text-decoration: none;">PRD-{{ $p->id }}</a></td>
                    <td style="font-weight: 600;"><a href="{{ route('produits.show', $p) }}" style="color: inherit; text-decoration: none;">{{ $p->nom }}</a></td>
                    <td style="font-weight: 600; color: var(--primary);">{{ (int) $p->prix_vente_conseille }} FCFA</td>
                    <td>
                        <span class="badge {{ ($s > 0 && !$enAlerte) ? 'badge-success' : 'badge-danger' }}">
                            {{ $s }} ctn{{ $p->a_cartouche && $sc > 0 ? ' +' . $sc . ' ctr' : '' }}
                        </span>
                    </td>
                    <td>
                        @if($p->a_cartouche)
                            {{ $p->cartouche_par_carton }} /carton
                            <br><small style="color: var(--text-muted);">{{ (int) $p->prix_cartouche_effectif }} FCFA</small>
                        @else
                            <span class="badge badge-gray">—</span>
                        @endif
                    </td>
                    <td>
                        <span class="badge {{ $enAlerte ? 'badge-danger' : ($p->seuil_alerte > 0 ? 'badge-warning' : 'badge-gray') }}">
                            {{ $p->seuil_alerte }}
                        </span>
                    </td>
                    <td style="text-align: center;">
                        @if(Auth::user()->peutModifierCatalogues())
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a href="{{ route('produits.edit', $p) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('produits.destroy', $p) }}" onsubmit="return confirm('Supprimer ce produit du catalogue ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                        @else
                        <span style="color:var(--text-muted); font-size:.8rem;">—</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucun produit dans le catalogue.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Stimulateur de Chiffre d'Affaires --}}
<div id="simModal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,.45); z-index:1050; align-items:flex-start; justify-content:center; padding:24px; overflow:auto;">
    <div style="background:#fff; border-radius:14px; width:100%; max-width:640px; max-height:90vh; display:flex; flex-direction:column; box-shadow:0 20px 50px rgba(0,0,0,.25);">
        <div style="display:flex; align-items:center; justify-content:space-between; padding:16px 18px; border-bottom:1px solid var(--border);">
            <div>
                <h3 style="margin:0; font-size:1.05rem;"><i class="bi bi-graph-up"></i> Stimulateur de CA</h3>
                <small style="color:var(--text-muted);">Les produits sans stock sont ignorés. Total = Σ (stock × prix).</small>
            </div>
            <button type="button" onclick="closeSim()" style="background:none; border:none; font-size:1.5rem; line-height:1; cursor:pointer; color:var(--text-muted);">&times;</button>
        </div>

        <div style="display:flex; gap:8px; padding:10px 18px; border-bottom:1px solid var(--border);">
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
    tr.stock-alert-row { background-color: #fef2f2; box-shadow: inset 3px 0 0 #ef4444; }
    tr.stock-alert-row:hover { background-color: #fee2e2; }
</style>
<script>
function openSim() { document.getElementById('simModal').style.display = 'flex'; simRefresh(); }
function closeSim() { document.getElementById('simModal').style.display = 'none'; }
function formatFCFA(n) { return new Intl.NumberFormat('fr-FR').format(Math.round(n)) + ' F'; }

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
            lineEl.textContent = '—';
            return;
        }
        const v = simLineVal(row);
        total += v;
        lineEl.textContent = formatFCFA(v);
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
        b.textContent = 'Ajouter';
        b.classList.remove('btn-danger');
        b.classList.add('btn-secondary');
    });
    simRefresh();
}

function simAjouterTout() {
    document.querySelectorAll('#simList .sim-row').forEach(r => {
        r.classList.remove('excluded');
        const b = r.querySelector('button');
        b.textContent = 'Retirer';
        b.classList.add('btn-danger');
        b.classList.remove('btn-secondary');
    });
    simRefresh();
}
</script>
@endpush
@endsection
