@extends('layouts.app')
@section('title', 'Détails de l\'Arrivage ' . $arrivage->reference)
@section('page-title', 'Arrivage : ' . $arrivage->reference)

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
    
    {{-- Entête & Statut --}}
    <div class="card" style="background: white;">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div>
                <span class="badge {{ $arrivage->statut === 'receptionne' ? 'badge-success' : 'badge-warning' }}" style="margin-bottom: 6px;">
                    {{ $arrivage->statut === 'receptionne' ? 'Receptionné & En Stock' : 'En attente de validation' }}
                </span>
                <h2 style="font-size: 1.4rem; font-weight: 700;">Arrivage {{ $arrivage->reference }}</h2>
                <div style="font-size: .8rem; color: var(--text-muted); margin-top: 4px;">
                    <i class="bi bi-calendar"></i> Créé le {{ $arrivage->created_at->format('d/m/Y à H:i') }} par <strong>{{ $arrivage->user?->name }}</strong>
                </div>
            </div>
            
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <a href="{{ route('arrivages.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Liste des arrivages
                </a>
                <a href="{{ route('arrivages.edit', $arrivage) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                
                @if($arrivage->statut !== 'receptionne')
                <form method="POST" action="{{ route('arrivages.valider', $arrivage) }}" onsubmit="return confirm('Valider cet arrivage ? Cette action injectera le stock dans le magasin & ajustera les prix conseillés.')">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-check-circle"></i> Valider & Réceptionner stock
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Grille Informations Logistiques / Financières --}}
    <div class="page-grid page-grid-3">
        
        {{-- Tableau des frais et infos --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-info-circle"></i> Informations Générales</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    @php
                        $fournisseurs = $arrivage->produits->pluck('fournisseur.nom')->filter()->unique()->values();
                    @endphp
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 500; font-size: .85rem;">Fournisseur(s) :</span>
                        <span style="font-weight: 600; font-size: .85rem; text-align: right;">
                            @if($fournisseurs->isNotEmpty())
                                {{ $fournisseurs->implode(', ') }}
                            @else
                                {{ $arrivage->fournisseur?->nom ?? '—' }}
                            @endif
                        </span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 500; font-size: .85rem;">Magasin de stockage :</span>
                        <span style="font-weight: 600; font-size: .85rem; color: var(--primary);">{{ $arrivage->magasin?->nom }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 500; font-size: .85rem;">Taux Naira -> FCFA :</span>
                        <span style="font-weight: 600; font-size: .85rem;">1 ₦ = {{ number_format($arrivage->taux_change, 4, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>

            {{-- Tableau détaillé des articles --}}
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-box"></i> Liste des Articles & Répartition du Coût</h3>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th class="wrap-text">Produit</th>
                                <th>Fournisseur</th>
                                <th style="text-align: right;">Quantité</th>
                                <th style="text-align: right;">Prix Achat (₦)</th>
                                <th style="text-align: right;">Achat (FCFA)</th>
                                <th style="text-align: right;">Frais prorata (FCFA)</th>
                                <th style="text-align: right;">Coût Revient U. (CFA)</th>
                                <th style="text-align: right; color: var(--primary);">Prix Vente Suggéré</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($arrivage->produits as $ligne)
                            <tr>
                                <td class="wrap-text" style="font-weight: 600;">{{ $ligne->produit?->nom }}</td>
                                <td style="font-size: .8rem;">{{ $ligne->fournisseur?->nom ?? '—' }}</td>
                                <td style="text-align: right;">{{ $ligne->quantite }} Carton</td>
                                <td style="text-align: right;">{{ number_format($ligne->prix_unitaire_origine, 0, ',', ' ') }} ₦</td>
                                <td style="text-align: right;">
                                    {{ number_format($ligne->prix_unitaire_origine * $arrivage->taux_change, 0, ',', ' ') }} FCFA
                                </td>
                                <td style="text-align: right; color: var(--warning);">
                                    {{ number_format($ligne->part_frais / $ligne->quantite, 0, ',', ' ') }} / u.
                                </td>
                                <td style="text-align: right; font-weight: 700; color: var(--success);">
                                    {{ number_format($ligne->cout_unitaire_reel, 0, ',', ' ') }} FCFA
                                </td>
                                <td style="text-align: right;">
                                    <form method="POST" action="{{ route('arrivages.produit.prix-suggere', $ligne) }}" style="display: flex; gap: 4px; align-items: center; justify-content: flex-end;">
                                        @csrf
                                        @method('PUT')
                                        <input type="number" name="prix_vente_suggere" value="{{ $ligne->prix_vente_suggere }}" min="0" style="width: 100px; text-align: right; font-weight: 700; color: var(--primary); border: 1px solid var(--border); border-radius: 4px; padding: 2px 6px; font-size: .85rem;">
                                        <button type="submit" style="background: none; border: none; color: var(--primary); cursor: pointer; padding: 2px;" title="Enregistrer"><i class="bi bi-check-circle-fill"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card" style="background: #f8fafc;">
                <div class="card-body" style="padding: 12px 16px;">
                    <h4 style="font-size: .85rem; font-weight: 700; margin: 0 0 8px 0;"><i class="bi bi-info-circle"></i> Règle d'arrondi du prix suggéré</h4>
                    <div style="font-size: .8rem; color: var(--text-muted); line-height: 1.6;">
                        <p style="margin: 0 0 4px 0;">Le coût de revient est arrondi à la hausse selon ces paliers :</p>
                        <ul style="margin: 0; padding-left: 20px;">
                            <li><strong>Moins de 30 000 FCFA</strong> → arrondi aux <strong>100 FCFA</strong> près (pas de 25, 50, 75)</li>
                            <li><strong>30 000 à 49 999 FCFA</strong> → arrondi aux <strong>500 FCFA</strong> près</li>
                            <li><strong>50 000 FCFA et plus</strong> → arrondi aux <strong>1 000 FCFA</strong> près</li>
                        </ul>
                        <p style="margin: 6px 0 0 0; font-style: italic;">Le prix suggéré peut être modifié manuellement à tout moment.</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Détails des Frais logistiques & Bénéfice --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            @php
                $benefice = $arrivage->beneficePrevisionnel();
            @endphp
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-graph-up-arrow"></i> Bénéfice Prévisionnel</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Revenu attendu (vente au prix suggéré) :</span>
                        <span style="font-weight: 650; font-size: .85rem;">{{ number_format($arrivage->revenuAttendu(), 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Coût total réel :</span>
                        <span style="font-weight: 650; font-size: .85rem;">{{ number_format($arrivage->total_cout_reel, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 16px; background: {{ $benefice >= 0 ? '#f0fdf4' : '#fef2f2' }};">
                        <span style="font-weight: 700; font-size: .95rem; color: {{ $benefice >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                            {{ $benefice >= 0 ? 'BÉNÉFICE' : 'PERTE' }}
                        </span>
                        <span style="font-weight: 700; font-size: 1.1rem; color: {{ $benefice >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                            {{ number_format($benefice, 0, ',', ' ') }} FCFA
                        </span>
                    </div>
                </div>
            </div>
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-wallet2"></i> Charges Importation</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Valeur marchandise (Naira) :</span>
                        <span style="font-weight: 650; font-size: .85rem;">{{ number_format($arrivage->total_valeur_origine, 0, ',', ' ') }} ₦</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Valeur marchandise (CFA) :</span>
                        <span style="font-weight: 650; font-size: .85rem;">{{ number_format($arrivage->total_valeur_origine * $arrivage->taux_change, 0, ',', ' ') }} FCFA</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Frais de transport :</span>
                        <span style="font-weight: 600; font-size: .85rem; color: var(--warning);">{{ number_format($arrivage->frais_transport, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Droits douaniers :</span>
                        <span style="font-weight: 600; font-size: .85rem; color: var(--warning);">{{ number_format($arrivage->frais_douane, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Frais de manutention :</span>
                        <span style="font-weight: 600; font-size: .85rem; color: var(--warning);">{{ number_format($arrivage->frais_manutention, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Autres frais divers :</span>
                        <span style="font-weight: 600; font-size: .85rem; color: var(--warning);">{{ number_format($arrivage->frais_divers, 0, ',', ' ') }} FCFA</span>
                    </div>
                    
                    @php 
                        $totalFrais = $arrivage->frais_transport + $arrivage->frais_douane + $arrivage->frais_manutention + $arrivage->frais_divers;
                    @endphp
                    <div style="display: flex; justify-content: space-between; padding: 16px; background: #f8fafc; border-bottom: 1px solid var(--border);">
                        <span style="font-weight: 600; font-size: .85rem;">Total des charges annexes :</span>
                        <span style="font-weight: 700; font-size: .85rem; color: var(--warning);">{{ number_format($totalFrais, 0, ',', ' ') }} FCFA</span>
                    </div>

                    <div style="display: flex; justify-content: space-between; padding: 16px; background: var(--sidebar-bg); color: white;">
                        <span style="font-weight: 600; font-size: .9rem;">COÛT TOTAL CAMION :</span>
                        <span style="font-weight: 700; font-size: .95rem; color: var(--secondary);">{{ number_format($arrivage->total_cout_reel, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
