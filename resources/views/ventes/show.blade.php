@extends('layouts.app')
@section('title', 'Facture')
@section('page-title', 'Facture : ' . $vente->reference)

@push('styles')
<style>
    @media print {
        body { background: #fff !important; }
        .no-print { display: none !important; }
        .invoice-card { box-shadow: none !important; border: none !important; padding: 20px !important; }
        .print-full { width: 100% !important; max-width: 100% !important; }
        header, .breadcrumb-bar, .main-container > .d-flex:first-child, .alert { display: none !important; }
        .main-container { padding: 0 !important; max-width: 100% !important; }
        @page { margin: 0; }
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
                <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--sidebar-bg);">SAÏMOUS</h1>
            </div>
            <div style="text-align: right;">
                <h2 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 4px;">FACTURE</h2>
                <p style="font-size: .95rem; font-weight: 600; color: var(--primary);">N° {{ $vente->reference }}</p>
                <p style="font-size: .8rem; color: var(--text-muted); margin-top: 4px;">{{ $vente->date_vente->format('d/m/Y H:i') }}</p>
            </div>
        </div>

        {{-- Client --}}
        <div style="margin-bottom: 24px; font-size: .85rem;">
            <h4 style="font-size: .7rem; text-transform: uppercase; color: var(--text-muted); margin-bottom: 4px; font-weight: 600; letter-spacing: .05em;">Client</h4>
            @if($vente->client)
                <p style="font-weight: 600; color: var(--primary);">{{ $vente->client->nomComplet() }}</p>
                <p style="color: var(--text-muted);">Tél: {{ $vente->client->telephone }}</p>
            @else
                <p style="color: var(--text-muted); font-weight: 600;">Client Anonyme</p>
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
                        <td style="padding: 10px 14px; text-align: right;">{{ $ligne->quantite }}</td>
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
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); color: var(--success); font-weight: 600;">
                    <span>Payé</span>
                    <span>{{ number_format($vente->montant_paye, 0, ',', ' ') }}</span>
                </div>
                @if($vente->montant_reste > 0)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); color: var(--danger); font-weight: 600;">
                    <span>Reste</span>
                    <span>{{ number_format($vente->montant_reste, 0, ',', ' ') }}</span>
                </div>
                @endif
                @if($vente->du && $vente->du > 0)
                <div style="display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid var(--border); color: #92400e; font-weight: 600; background: #fef3c7; border-radius: 4px; padding: 8px 12px;">
                    <span>Du client</span>
                    <span>{{ number_format($vente->du, 0, ',', ' ') }}</span>
                </div>
                @endif
                <div style="display: flex; justify-content: space-between; font-size: 1rem; font-weight: 700; background: var(--sidebar-bg); color: white; padding: 8px 12px; border-radius: 4px; margin-top: 4px;">
                    <span>NET À PAYER</span>
                    <span>{{ number_format($vente->montant_total, 0, ',', ' ') }}</span>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
