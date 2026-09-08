@extends('layouts.app')
@section('title', 'Analyse avancée')
@section('page-title', 'Analyse avancée')
@section('subtitle', 'Graphiques et indicateurs de performance — ' . $tenant->nom)

@push('styles')
<style>
    .chart-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }
    @media (min-width: 992px) {
        .chart-grid-2 { grid-template-columns: 1fr 1fr; }
    }
    .chart-card {
        background: #fff;
        border-radius: var(--radius-card);
        border: 1px solid var(--border);
        padding: 20px;
        box-shadow: var(--shadow-sm);
    }
    .chart-card h3 {
        font-size: .95rem;
        font-weight: 700;
        margin: 0 0 4px;
    }
    .chart-card .chart-sub {
        font-size: .75rem;
        color: var(--text-muted);
        margin-bottom: 16px;
    }
    .chart-card .chart-wrap {
        position: relative;
        width: 100%;
        height: 280px;
    }
    .chart-card .chart-wrap canvas {
        width: 100% !important;
        height: 100% !important;
    }
    .alerte-list {
        display: flex;
        flex-direction: column;
        gap: 8px;
        margin-top: 12px;
    }
    .alerte-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 12px;
        background: #fff7ed;
        border-radius: 6px;
        font-size: .85rem;
    }
    .alerte-item .badge { font-size: .75rem; }

    /* Filter Bar Styles */
    .analytique-filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    .filter-select {
        padding: 6px 12px;
        border-radius: 50px;
        border: 1px solid var(--border);
        background: #fff;
        color: #0f172a;
        font-size: .8rem;
        font-weight: 700;
        cursor: pointer;
        outline: none;
        box-shadow: var(--shadow-sm);
    }
    .filter-pills-wrap {
        display: flex;
        align-items: center;
        gap: 4px;
        overflow-x: auto;
        max-width: 100%;
        padding: 4px 0;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
    }
    .filter-pills-wrap::-webkit-scrollbar { display: none; }
    .filter-pill {
        padding: 5px 12px;
        border-radius: 50px;
        border: 1px solid #cbd5e1;
        background: #fff;
        color: #475569;
        font-size: .78rem;
        font-weight: 700;
        text-decoration: none;
        white-space: nowrap;
        transition: all .2s;
    }
    .filter-pill:hover {
        border-color: #0f172a;
        color: #0f172a;
    }
    .filter-pill.active {
        background: #0f172a;
        color: #fff;
        border-color: #0f172a;
    }
    .filter-sep {
        color: #cbd5e1;
        margin: 0 4px;
    }
</style>
@endpush

@section('actions')
@php
    $yearList = range(date('Y') - 3, date('Y'));
    $moisListFull = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai','06'=>'Juin',
                     '07'=>'Juillet','08'=>'Août','09'=>'Septembre','10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
    $moisShort = ['01'=>'Jan','02'=>'Fév','03'=>'Mar','04'=>'Avr','05'=>'Mai','06'=>'Jui',
                  '07'=>'Jul','08'=>'Aoû','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Déc'];
@endphp

<div class="analytique-filter-bar">
    {{-- Selects rapid pour Mobile / Desktop --}}
    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
        <select onchange="location.href=this.value" class="filter-select">
            @foreach($yearList as $y)
                <option value="{{ route('analytique', array_filter(['annee' => $y, 'mois' => $mois])) }}" {{ $annee == $y ? 'selected' : '' }}>
                    Année {{ $y }}
                </option>
            @endforeach
        </select>

        <select onchange="location.href=this.value" class="filter-select">
            <option value="{{ route('analytique', ['annee' => $annee]) }}" {{ empty($mois) ? 'selected' : '' }}>
                Toute l'année {{ $annee }}
            </option>
            @foreach($moisListFull as $mv => $ml)
                <option value="{{ route('analytique', ['annee' => $annee, 'mois' => $mv]) }}" {{ $mois == $mv ? 'selected' : '' }}>
                    {{ $ml }}
                </option>
            @endforeach
        </select>
    </div>

    {{-- Chips / Pill buttons --}}
    <div class="filter-pills-wrap">
        @foreach($yearList as $y)
            <a href="{{ route('analytique', array_filter(['annee' => $y, 'mois' => $mois])) }}"
               class="filter-pill {{ $annee == $y ? 'active' : '' }}">
                {{ $y }}
            </a>
        @endforeach

        <span class="filter-sep">|</span>

        <a href="{{ route('analytique', ['annee' => $annee]) }}"
           class="filter-pill {{ empty($mois) ? 'active' : '' }}">
            Tout l'an
        </a>

        @foreach($moisShort as $mv => $ml)
            <a href="{{ route('analytique', ['annee' => $annee, 'mois' => $mv]) }}"
               class="filter-pill {{ $mois == $mv ? 'active' : '' }}">
                {{ $ml }}
            </a>
        @endforeach
    </div>
</div>
@endsection

@section('content')

{{-- Grille de graphiques --}}
<div class="chart-grid chart-grid-2">

    {{-- 1. Ventes mensuelles (courbe) --}}
    <div class="chart-card">
        <h3><i class="bi bi-graph-up" style="color:var(--primary);"></i> Ventes mensuelles</h3>
        <div class="chart-sub">Évolution des encaissements par mois (FCFA)</div>
        <div class="chart-wrap">
            <canvas id="chartVentesMensuelles"></canvas>
        </div>
    </div>

    {{-- 2. Revenu net vs Dépenses vs Loyers (barres groupées) --}}
    <div class="chart-card">
        <h3><i class="bi bi-bar-chart" style="color:var(--success);"></i> Résultat mensuel</h3>
        <div class="chart-sub">C.A. − Dépenses − Loyers = Résultat (FCFA)</div>
        <div class="chart-wrap">
            <canvas id="chartRevenuNet"></canvas>
        </div>
    </div>

    {{-- 3. Ventes quotidiennes du mois (courbe) --}}
    <div class="chart-card">
        <h3><i class="bi bi-calendar2-week" style="color:#2563eb;"></i> Ventes quotidiennes</h3>
        <div class="chart-sub">Encaissements par jour du mois selectionné (FCFA)</div>
        <div class="chart-wrap">
            <canvas id="chartVentesQuotidiennes"></canvas>
        </div>
    </div>

    {{-- 4. Tous les produits les plus vendus (barres horizontales) --}}
    <div class="chart-card">
        <h3><i class="bi bi-trophy" style="color:#d97706;"></i> Classement des produits les plus vendus</h3>
        <div class="chart-sub">Tous les produits vendus (quantité) — {{ count($topProduits) }} produit(s)</div>
        <div style="width:100%; {{ count($topProduits) > 8 ? 'max-height:550px; overflow-y:auto;' : '' }}">
            <div class="chart-wrap" style="height: {{ max(280, count($topProduits) * 34) }}px;">
                <canvas id="chartTopProduits"></canvas>
            </div>
        </div>
    </div>

    {{-- 5. Répartition statut paiement (donut) --}}
    <div class="chart-card">
        <h3><i class="bi bi-pie-chart" style="color:var(--primary);"></i> Répartition des ventes</h3>
        <div class="chart-sub">Selon le statut de paiement</div>
        <div class="chart-wrap" style="max-height:280px; display:flex; justify-content:center;">
            <canvas id="chartStatutPaiement" style="max-width:280px;"></canvas>
        </div>
    </div>

    {{-- 6. Ventes par vendeur (barres) --}}
    <div class="chart-card">
        <h3><i class="bi bi-people" style="color:#7c3aed;"></i> Ventes par vendeur</h3>
        <div class="chart-sub">Total encaissé par collaborateur (FCFA)</div>
        <div class="chart-wrap">
            <canvas id="chartVentesParVendeur"></canvas>
        </div>
    </div>

    {{-- 7. Nombre de ventes par mois (barres) --}}
    <div class="chart-card">
        <h3><i class="bi bi-receipt" style="color:#0891b2;"></i> Nombre de ventes par mois</h3>
        <div class="chart-sub">Volume de transactions mensuel</div>
        <div class="chart-wrap">
            <canvas id="chartNbVentes"></canvas>
        </div>
    </div>

    {{-- 8. Dettes créées par mois (courbe) --}}
    <div class="chart-card">
        <h3><i class="bi bi-credit-card-2-back" style="color:#dc2626;"></i> Dettes créées par mois</h3>
        <div class="chart-sub">Montant total des nouvelles dettes (FCFA)</div>
        <div class="chart-wrap">
            <canvas id="chartDettes"></canvas>
        </div>
    </div>

    {{-- 9. Alertes stock --}}
    <div class="chart-card">
        <h3><i class="bi bi-exclamation-triangle" style="color:#d97706;"></i> Produits en alerte stock</h3>
        <div class="chart-sub">Stock sous le seuil d'alerte</div>
        @if(count($stockAlertes) > 0)
            <div class="alerte-list">
                @foreach($stockAlertes as $a)
                    <div class="alerte-item">
                        <span>{{ $a['nom'] }}</span>
                        <span class="badge {{ $a['stock'] <= 0 ? 'badge-danger' : 'badge-warning' }}">{{ $a['stock'] }} carton(s)</span>
                    </div>
                @endforeach
            </div>
        @else
            <p style="color:var(--text-muted); font-size:.85rem; padding:24px 0; text-align:center;">Aucun produit en alerte</p>
        @endif
    </div>

    {{-- 10. Résumé chiffres clés --}}
    @php
        $totalVentesAn  = array_sum($moisData);
        $totalDepensesAn = array_sum($depensesData);
        $totalLoyerAn   = $loyersCumules;
        $totalNetAn     = $totalVentesAn - $totalDepensesAn - $totalLoyerAn;
        $nbVentesAn     = array_sum($nbVentesData);

        $moisNomsFull = ['01'=>'Janvier','02'=>'Février','03'=>'Mars','04'=>'Avril','05'=>'Mai',
                         '06'=>'Juin','07'=>'Juillet','08'=>'Août','09'=>'Septembre',
                         '10'=>'Octobre','11'=>'Novembre','12'=>'Décembre'];
        $moisNomActif = $mois ? ($moisNomsFull[$mois] ?? $mois) : null;

        $titreResume = $moisNomActif
            ? "Résumé — {$moisNomActif} {$annee}"
            : "Résumé {$annee} — {$moisEcoules} mois écoulé(s)";
        $sousTitreResume = $moisNomActif
            ? "Chiffres du mois de {$moisNomActif}"
            : "Cumul sur {$moisEcoules} mois (loyers = {$moisEcoules} × loyer mensuel)";
    @endphp
    <div class="chart-card" style="grid-column: 1 / -1;">
        <h3><i class="bi bi-calculator" style="color:var(--primary);"></i> {{ $titreResume }}</h3>
        <div class="chart-sub">{{ $sousTitreResume }}</div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-top:12px;">
            <div style="background:#f8f9fa; padding:14px; border-radius:8px; text-align:center;">
                <div style="font-size:1.3rem; font-weight:800; color:#1f2937;">{{ number_format($totalVentesAn, 0, ',', ' ') }} F</div>
                <div style="font-size:.7rem; color:var(--text-muted); text-transform:uppercase;">
                    {{ $moisNomActif ? 'Ventes du mois' : 'Total ventes' }}
                </div>
            </div>
            <div style="background:#fef2f2; padding:14px; border-radius:8px; text-align:center;">
                <div style="font-size:1.3rem; font-weight:800; color:#dc2626;">{{ number_format($totalDepensesAn, 0, ',', ' ') }} F</div>
                <div style="font-size:.7rem; color:var(--text-muted); text-transform:uppercase;">
                    {{ $moisNomActif ? 'Dépenses du mois' : 'Total dépenses' }}
                </div>
            </div>
            <div style="background:#fef2f2; padding:14px; border-radius:8px; text-align:center;">
                <div style="font-size:1.3rem; font-weight:800; color:#dc2626;">{{ number_format($totalLoyerAn, 0, ',', ' ') }} F</div>
                <div style="font-size:.7rem; color:var(--text-muted); text-transform:uppercase;">
                    {{ $moisNomActif ? 'Loyer du mois' : "Loyers ({$moisEcoules} mois)" }}
                </div>
            </div>
            <div style="background:#f8f9fa; padding:14px; border-radius:8px; text-align:center;">
                <div style="font-size:1.3rem; font-weight:800; color:#1f2937;">{{ $nbVentesAn }}</div>
                <div style="font-size:.7rem; color:var(--text-muted); text-transform:uppercase;">Nombre de ventes</div>
            </div>
            <div style="grid-column:1/-1; background:#f8f9fa; padding:18px; border-radius:8px; text-align:center; border:2px solid #1f2937;">
                <div style="font-size:1.6rem; font-weight:900; color:#000;">{{ number_format($totalNetAn, 0, ',', ' ') }} F</div>
                <div style="font-size:.75rem; color:var(--text-muted); text-transform:uppercase;">
                    {{ $moisNomActif ? 'Revenu net du mois' : "Revenu net ({$moisEcoules} mois)" }}
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Chart === 'undefined') return;

    Chart.defaults.font.family = "'Plus Jakarta Sans', sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#6b7280';
    Chart.defaults.responsive = true;
    Chart.defaults.maintainAspectRatio = false;

    const COLORS = {
        primary: '#105e49', success: '#16a34a', danger: '#dc2626',
        warning: '#d97706', blue: '#2563eb', purple: '#7c3aed',
    };

    function id(el) { return document.getElementById(el); }

    function yFmt() {
        return { beginAtZero: true, ticks: { callback: function(v) { return v >= 1000 ? (v/1000).toFixed(0) + 'k' : v; } } };
    }

    var CH = function(el, cfg) {
        if (!el) return;
        try { return new Chart(el, cfg); } catch(e) { console.warn('Chart error:', e); }
    };

    // 1. Ventes mensuelles
    CH(id('chartVentesMensuelles'), {
        type: 'line',
        data: {
            labels: {!! json_encode($moisLabels) !!},
            datasets: [{
                label: 'Ventes (FCFA)',
                data: {{ json_encode($moisData) }},
                borderColor: COLORS.primary,
                backgroundColor: COLORS.primary + '22',
                fill: true, tension: .3, pointRadius: 4,
                pointBackgroundColor: COLORS.primary,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: yFmt() } }
    });

    // 2. Revenu net
    CH(id('chartRevenuNet'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($moisLabels) !!},
            datasets: [
                { label: 'C.A.', data: {{ json_encode($moisData) }}, backgroundColor: '#16a34a88', borderColor: '#16a34a', borderWidth: 1 },
                { label: 'Dépenses', data: {{ json_encode($depensesData) }}, backgroundColor: '#dc262688', borderColor: '#dc2626', borderWidth: 1 },
                { label: 'Résultat', data: {{ json_encode($revenuNetData) }}, backgroundColor: '#105e4988', borderColor: '#105e49', borderWidth: 1 },
            ]
        },
        options: {
            plugins: { legend: { position: 'top', labels: { boxWidth: 12, font: { size: 10 } } } },
            scales: { y: yFmt() }
        }
    });

    // 3. Ventes quotidiennes
    CH(id('chartVentesQuotidiennes'), {
        type: 'line',
        data: {
            labels: {!! json_encode($joursLabels) !!},
            datasets: [{
                label: 'Encaissements (FCFA)',
                data: {{ json_encode($ventesJourData) }},
                borderColor: COLORS.blue, backgroundColor: COLORS.blue + '22',
                fill: true, tension: .3, pointRadius: 2, pointBackgroundColor: COLORS.blue,
            }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: yFmt() } }
    });

    // 4. Top 10 produits
    var tpL = {!! json_encode($topProduits->pluck('nom')) !!};
    var tpD = {!! json_encode($topProduits->pluck('total_vendu')) !!};
    CH(id('chartTopProduits'), {
        type: 'bar',
        data: { labels: tpL, datasets: [{ label: 'Quantité vendue', data: tpD, backgroundColor: '#d9770688', borderColor: '#d97706', borderWidth: 1 }] },
        options: { indexAxis: 'y', plugins: { legend: { display: false } }, scales: { x: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // 5. Statut paiement (donut)
    var sL = {!! json_encode($statutLabels) !!};
    var sD = {{ json_encode($statutData) }};
    var sC = {!! json_encode($statutColors) !!};
    if (sL.length > 0) {
        CH(id('chartStatutPaiement'), {
            type: 'doughnut',
            data: { labels: sL, datasets: [{ data: sD, backgroundColor: sC, borderWidth: 2, borderColor: '#fff' }] },
            options: { cutout: '65%', plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } } }
        });
    } else {
        var el = id('chartStatutPaiement');
        if (el) {
            el.parentNode.innerHTML = '<p style="text-align:center;color:var(--text-muted);padding:40px 0;">Aucune donnée de vente cette année</p>';
        }
    }

    // 6. Ventes par vendeur
    var vL = {!! json_encode($ventesParVendeur->map(fn($v) => $v->user?->name ?? 'N/A')) !!};
    var vD = {!! json_encode($ventesParVendeur->pluck('total')) !!};
    CH(id('chartVentesParVendeur'), {
        type: 'bar',
        data: { labels: vL, datasets: [{ label: 'Ventes (FCFA)', data: vD, backgroundColor: '#7c3aed88', borderColor: '#7c3aed', borderWidth: 1 }] },
        options: { plugins: { legend: { display: false } }, scales: { y: yFmt() } }
    });

    // 7. Nombre de ventes
    CH(id('chartNbVentes'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($moisLabels) !!},
            datasets: [{ label: 'Nombre de ventes', data: {{ json_encode($nbVentesData) }}, backgroundColor: '#0891b288', borderColor: '#0891b2', borderWidth: 1 }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
    });

    // 8. Dettes par mois
    CH(id('chartDettes'), {
        type: 'line',
        data: {
            labels: {!! json_encode($moisLabels) !!},
            datasets: [{ label: 'Dettes (FCFA)', data: {{ json_encode($dettesData) }}, borderColor: COLORS.danger, backgroundColor: COLORS.danger + '22', fill: true, tension: .3, pointRadius: 3, pointBackgroundColor: COLORS.danger }]
        },
        options: { plugins: { legend: { display: false } }, scales: { y: yFmt() } }
    });
});
</script>
@endpush
