@extends('layouts.app')
@section('title', 'Facture')
@section('page-title', 'Facture : ' . $vente->reference)

@push('styles')
<style>
    @media print {
        @page { size: 80mm auto; margin: 0; }
        html, body { width: 80mm !important; }
        body { background: #fff !important; }
        .no-print { display: none !important; }
        .print-full { width: 80mm !important; max-width: 80mm !important; margin: 0 !important; }
        .invoice-card { box-shadow: none !important; border: none !important; width: 80mm !important; padding: 6px 8px !important; font-size: 11px !important; }
        .invoice-header { padding-bottom: 10px !important; margin-bottom: 12px !important; }
        .invoice-header h1 { font-size: 14px !important; }
        .invoice-header h2 { font-size: 11px !important; }
        .invoice-header p { font-size: 10px !important; }
        header, .breadcrumb-bar, .main-container > .d-flex:first-child, .alert { display: none !important; }
        .main-container { padding: 0 !important; max-width: 100% !important; }
        .table-wrap table { table-layout: fixed !important; width: 100% !important; font-size: 10px !important; }
        .table-wrap th, .table-wrap td { padding: 3px 4px !important; overflow-wrap: anywhere !important; word-break: break-word !important; }
        .table-wrap th:nth-child(1), .table-wrap td:nth-child(1) { width: 42% !important; }
        .table-wrap th:nth-child(2), .table-wrap td:nth-child(2) { width: 20% !important; }
        .table-wrap th:nth-child(3), .table-wrap td:nth-child(3) { width: 18% !important; }
        .table-wrap th:nth-child(4), .table-wrap td:nth-child(4) { width: 20% !important; }
    }
    .invoice-card { background: white; padding: 40px; box-shadow: var(--shadow-md); }
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
    <div class="no-print" style="display: flex; justify-content: space-between; align-items: center;">
        <a href="{{ route('ventes.index') }}" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Retour
        </a>
        <div style="display: flex; gap: 8px;">
            <a href="{{ route('ventes.edit', $vente) }}" class="btn btn-secondary">
                <i class="bi bi-pencil"></i> Modifier
            </a>
            <button onclick="var t=document.title;document.title='';window.print();setTimeout(function(){document.title=t},100)" class="btn btn-primary">
                <i class="bi bi-printer"></i> Imprimer
            </button>
        </div>
    </div>

    {{-- Corps de la facture --}}
    <div class="invoice-card" style="border-radius: 8px;">
        
        {{-- En-tête Facture --}}
        <div class="invoice-header" style="display: flex; justify-content: space-between; border-bottom: 2px solid var(--border); padding-bottom: 20px; margin-bottom: 24px;">
            <div>
                <h1 style="font-size: 1.6rem; font-weight: 800; color: #1f2937;">{{ auth()->user()->tenant->nom ?? 'SAÏMOUS' }}</h1>
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
                <p style="color: var(--text-muted); margin-top: 4px;">Établi par: {{ $vente->user->name }}</p>
            @endif
        </div>

        {{-- Tableau des Lignes --}}
        <div class="table-wrap" style="border: 1px solid var(--border); border-radius: 6px; margin-bottom: 24px;">
            <table style="width: 100%; border-collapse: collapse; font-size: .85rem;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <th class="wrap-text" style="padding: 10px 14px; text-align: left;">Article</th>
                        <th style="padding: 10px 14px; text-align: right;">Prix</th>
                        <th style="padding: 10px 14px; text-align: right;">Qté</th>
                        <th style="padding: 10px 14px; text-align: right;">Total</th>
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
                    <span style="font-weight: 600;">{{ number_format($vente->montant_total, 0, ',', ' ') }}</span>
                </div>
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); font-weight: 600;">
                    <span>Payé</span>
                    <span>{{ number_format($vente->montant_paye, 0, ',', ' ') }}</span>
                </div>
                @if($vente->montant_remis)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); font-weight: 600;">
                    <span>Montant remis</span>
                    <span>{{ number_format($vente->montant_remis, 0, ',', ' ') }}</span>
                </div>
                @endif
                @if($vente->montant_reste > 0)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); font-weight: 600;">
                    <span>Reste</span>
                    <span>{{ number_format($vente->montant_reste, 0, ',', ' ') }}</span>
                </div>
                @endif
                @if($vente->du && $vente->du > 0)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); font-weight: 600; color: #374151;">
                    <span>Du client (Monnaie à rendre)</span>
                    <span>{{ number_format($vente->du, 0, ',', ' ') }}</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; font-size: 1rem; font-weight: 700; color: #1f2937; border-top: 2px solid #1f2937; padding: 8px 0; margin-top: 8px;">
                    <span>NET À PAYER</span>
                    <span>{{ number_format($vente->montant_total, 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
