@extends('layouts.app')
@section('title', 'Gestion des Arrivages (🇧🇯->🇳🇬, 🇨🇳->🇧🇯)')
@section('page-title', 'Liste des Arrivages')

@section('content')
<div class="card">
    <div class="card-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-truck"></i> Suivi des Arrivages
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ method_exists($arrivages, 'total') ? $arrivages->total() : $arrivages->count() }}</span>
        </h3>
        @if(Auth::user()->peutGererArrivages())
        <a href="{{ route('arrivages.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Créer un Arrivage
        </a>
        @endif
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Référence</th>
                    <th>Date</th>
                    <th>Fournisseur</th>
                    <th>Destination</th>
                    <th>Taux (Naira / FCFA)</th>
                    <th style="text-align: right;">Valeur Origine</th>
                    <th style="text-align: right;">Total Frais (FCFA)</th>
                    <th style="text-align: right;">Coût Total Réel (FCFA)</th>
                    <th style="text-align: right;">Bénéfice (FCFA)</th>
                    <th style="text-align: center;">Statut</th>
                    <th style="text-align: center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($arrivages as $arr)
                <tr>
                    <td style="font-weight: 600;">
                        <a href="{{ route('arrivages.show', $arr) }}" style="color: var(--primary); text-decoration: none;">
                            {{ $arr->reference }}
                        </a>
                    </td>
                    <td>{{ $arr->created_at->format('d/m/Y') }}</td>
                    <td>{{ $arr->fournisseur?->nom ?? '—' }}</td>
                    <td><span class="badge badge-gray">{{ $arr->magasin?->nom }}</span></td>
                    <td>1 ₦ = {{ number_format($arr->taux_change, 4, ',', ' ') }} FCFA</td>
                    <td style="text-align: right;">{{ number_format($arr->total_valeur_origine, 0, ',', ' ') }} ₦</td>
                    <td style="text-align: right; color: var(--warning); font-weight: 500;">
                        {{ number_format($arr->frais_transport + $arr->frais_douane + $arr->frais_manutention + $arr->frais_divers, 0, ',', ' ') }}
                    </td>
                    <td style="text-align: right; font-weight: 700; color: var(--success);">
                        {{ number_format($arr->total_cout_reel, 0, ',', ' ') }}
                    </td>
                    <td style="text-align: right; font-weight: 700; color: {{ $arr->beneficePrevisionnel() >= 0 ? 'var(--success)' : 'var(--danger)' }};">
                        {{ number_format($arr->beneficePrevisionnel(), 0, ',', ' ') }}
                    </td>
                    <td style="text-align: center;">
                        @if($arr->statut === 'receptionne')
                            <span class="badge badge-success"><i class="bi bi-patch-check"></i> Réceptionné</span>
                        @else
                            <span class="badge badge-warning"><i class="bi bi-hourglass-split"></i> En attente</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 6px; justify-content: center;">
                            <a href="{{ route('arrivages.show', $arr) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;">
                                <i class="bi bi-eye"></i> Voir
                            </a>
                            @if($arr->statut !== 'receptionne' && Auth::user()->peutGererArrivages())
                            <form method="POST" action="{{ route('arrivages.destroy', $arr) }}" onsubmit="return confirm('Confirmer la suppression de cet arrivage ?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px;">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="11" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucun arrivage enregistré.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($arrivages->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: center;">
        {{ $arrivages->links() }}
    </div>
    @endif
</div>
@endsection
