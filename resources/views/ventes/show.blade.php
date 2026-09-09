@extends('layouts.app')
@section('title', 'Facture')
@section('page-title', 'Facture : ' . $vente->reference)

@push('styles')
<style>
    @media print {
        @page { size: auto; margin: 5mm; }
        html, body { width: 100% !important; }
        body { background: #fff !important; font-size: 13px !important; color: #000 !important; font-weight: 600 !important; }
        .no-print { display: none !important; }
        .print-full { width: 100% !important; max-width: 100% !important; margin: 0 !important; }
        .invoice-card { box-shadow: none !important; border: none !important; width: 100% !important; padding: 10px !important; font-size: 13px !important; }
        .invoice-header { padding-bottom: 12px !important; margin-bottom: 14px !important; border-bottom: 2px solid #000 !important; }
        .invoice-header h1 { font-size: 20px !important; font-weight: 900 !important; color: #000 !important; }
        .invoice-header h2 { font-size: 16px !important; font-weight: 800 !important; color: #000 !important; }
        .invoice-header p { font-size: 13px !important; font-weight: 700 !important; color: #000 !important; }
        header, .breadcrumb-bar, .main-container > .d-flex:first-child, .alert { display: none !important; }
        .main-container { padding: 0 !important; max-width: 100% !important; }
        .table-wrap table { table-layout: fixed !important; width: 100% !important; font-size: 13px !important; color: #000 !important; }
        .table-wrap th, .table-wrap td { padding: 6px 6px !important; font-size: 13px !important; font-weight: 700 !important; color: #000 !important; overflow-wrap: anywhere !important; word-break: break-word !important; }
        .table-wrap th { border-bottom: 2px solid #000 !important; }
        .hide-company .invoice-company-info, .hide-company .invoice-company-name, .hide-company .invoice-company-phone { display: none !important; }
        .hide-vendeur .invoice-seller-name { display: none !important; }
    }
    .invoice-card { background: white; padding: 40px; box-shadow: var(--shadow-md); }
    .hide-company .invoice-company-info, .hide-company .invoice-company-name, .hide-company .invoice-company-phone { display: none; }
    .hide-vendeur .invoice-seller-name { display: none; }
    @media (max-width: 768px) {
        .invoice-card { padding: 16px; }
        .invoice-header { flex-direction: column; text-align: center; gap: 12px; }
        .invoice-header > div { text-align: center !important; }
    }
</style>
@endpush

@section('content')
<div class="print-full" style="max-width: 900px; margin: 0 auto; display: flex; flex-direction: column; gap: 20px;">
    
    {{-- Boutons d'actions --}}
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;">
        <a href="{{ route('ventes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
            <label style="font-size: .85rem; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; user-select: none; color: var(--text);">
                <input type="checkbox" onchange="togglePrintOption('company', this.checked)">
                Masquer la société
            </label>
            <label style="font-size: .85rem; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; user-select: none; color: var(--text);">
                <input type="checkbox" onchange="togglePrintOption('vendeur', this.checked)">
                Masquer le vendeur
            </label>
            <a href="{{ route('ventes.edit', $vente) }}" class="btn btn-secondary">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            <button onclick="var t=document.title;document.title='';window.print();setTimeout(function(){document.title=t},100)" class="btn btn-primary">
                <i class="bi bi-printer"></i> Imprimer
            </button>
            <form method="POST" action="{{ route('ventes.destroy', $vente) }}" style="display:inline;" onsubmit="return confirm('Supprimer cette vente #{{ $vente->reference }} ? Le stock sera automatiquement réajusté.');">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="bi bi-trash"></i> Supprimer
                </button>
            </form>
        </div>
    </div>

    {{-- Corps de la facture --}}
    <div class="invoice-card" style="border-radius: 8px;">
        
        @php
            $tenantObj = $vente->tenant ?? $vente->magasin?->tenant ?? auth()->user()->tenant ?? null;
            $companyPhone = $tenantObj?->telephone ?? auth()->user()->telephone ?? null;
        @endphp
        {{-- En-tête Facture --}}
        <div class="invoice-header" style="display: flex; justify-content: space-between; border-bottom: 2px solid var(--border); padding-bottom: 20px; margin-bottom: 24px;">
            <div class="invoice-company-info">
                <h1 class="invoice-company-name" style="font-size: 1.6rem; font-weight: 800; color: #1f2937; margin: 0;">{{ $tenantObj?->nom ?? auth()->user()->tenant->nom ?? 'SAÏMOUS' }}</h1>
                @if($companyPhone)
                    <p class="invoice-company-phone" style="font-size: .9rem; font-weight: 600; color: #4b5563; margin-top: 4px; margin-bottom: 0;">Tél: {{ $companyPhone }}</p>
                @endif
            </div>
            <div style="text-align: right;">
                <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 4px;">FACTURE</h2>
                <p style="font-size: .95rem; font-weight: 600; color: #1f2937;">N° {{ $vente->reference }}</p>
                @php
                    $frMois = ['Janvier','Février','Mars','Avril','Mai','Juin','Juillet','Août','Septembre','Octobre','Novembre','Décembre'];
                    $dt = $vente->date_vente;
                    $dateStr = $dt->format('d') . ' ' . $frMois[(int)$dt->format('m') - 1] . ' ' . $dt->format('Y') . ' · ' . $dt->format('H:i');
                @endphp
                <p style="font-size: .8rem; color: var(--text-muted); margin-top: 4px;">{{ $dateStr }}</p>
            </div>
        </div>

        {{-- Client --}}
        <div style="margin-bottom: 24px; font-size: .85rem;">
            <h4 style="font-size: .7rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px; font-weight: 600; letter-spacing: .05em;">Client</h4>
            @if($vente->client)
                <p style="font-weight: 600; color: #1f2937;">{{ $vente->client->nomComplet() }}</p>
                <p style="color: var(--text-muted);">Tél: {{ $vente->client->telephone }}</p>
            @else
                <p style="color: var(--text-muted); font-weight: 600;">Client Anonyme</p>
            @endif
            @if($vente->user)
                <p class="invoice-seller-name" style="color: var(--text-muted); margin-top: 4px;">Établi par: {{ $vente->user->name }}</p>
            @endif
        </div>

        {{-- Tableau des Lignes --}}
        <div class="table-wrap" style="border: 1px solid var(--border); border-radius: 6px; margin-bottom: 24px;">
            <table style="width: 100%; border-collapse: collapse; font-size: .85rem;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th class="wrap-text" style="padding: 10px 14px; text-align: left; vertical-align: middle;">Article</th>
                        <th style="padding: 10px 14px; text-align: right; vertical-align: middle;">Prix</th>
                        <th style="padding: 10px 14px; text-align: right; vertical-align: middle;">Qté</th>
                        <th style="padding: 10px 14px; text-align: right; vertical-align: middle;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vente->lignes as $ligne)
                    <tr>
                        <td class="wrap-text" style="padding: 10px 14px; font-weight: 600;">{{ $ligne->produit?->nom }}</td>
                        <td style="padding: 10px 14px; text-align: right;">{{ number_format($ligne->prix_vente, 0, ',', ' ') }}</td>
                        <td style="padding: 10px 14px; text-align: right;">
                            {{ $ligne->quantite }}
                            @if($ligne->unite)
                                {{ \Illuminate\Support\Str::contains($ligne->unite, ['cartouche', 'Cartouche']) ? 'ctc' : (\Illuminate\Support\Str::contains($ligne->unite, ['carton', 'Carton']) ? 'ctn' : (\Illuminate\Support\Str::contains($ligne->unite, ['piece', 'Pièce', 'pièce']) ? 'pc' : $ligne->unite)) }}
                            @endif
                        </td>
                        <td style="padding: 10px 14px; text-align: right; font-weight: 600;">{{ number_format($ligne->total_ligne, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Totaux --}}
        <div style="display: flex; justify-content: flex-end; font-size: .85rem;">
            <div style="width: 100%; max-width: 320px; display: flex; flex-direction: column; gap: 6px;">
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border);">
                    <span>Total</span>
                    <span style="font-weight: 600;">{{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); font-weight: 600;">
                    <span>Payé</span>
                    <span>{{ number_format($vente->montant_paye, 0, ',', ' ') }} FCFA</span>
                </div>
                @if($vente->montant_reste > 0)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); font-weight: 600; color: #dc2626;">
                    <span>Reste à payer</span>
                    <span>{{ number_format($vente->montant_reste, 0, ',', ' ') }} FCFA</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; font-size: 1rem; font-weight: 800; color: #1f2937; border-top: 2px solid #1f2937; padding: 8px 0; margin-top: 8px;">
                    <span>NET À PAYER</span>
                    <span>{{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA</span>
                </div>
            </div>
        </div>

    </div>
</div>

@push('scripts')
<script>
function togglePrintOption(option, checked) {
    const card = document.querySelector('.invoice-card');
    if (option === 'company') {
        card.classList.toggle('hide-company', checked);
    } else if (option === 'vendeur') {
        card.classList.toggle('hide-vendeur', checked);
    }
}

// ── Empêcher le retour vers le formulaire de création (bouton ← du navigateur) ──
// Quand l'utilisateur revient en arrière depuis la facture, on le redirige
// vers la liste des ventes au lieu du formulaire vide.
(function() {
    if (window.history && window.history.pushState) {
        window.history.pushState({ page: 'facture' }, '', window.location.href);
        window.addEventListener('popstate', function() {
            window.location.replace('{{ route("ventes.index") }}');
        });
    }
})();
</script>
@endpush
@endsection
