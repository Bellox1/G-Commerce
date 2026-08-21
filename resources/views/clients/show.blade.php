@extends('layouts.app')
@section('title', 'Fiche Client')
@section('page-title', 'Fiche Client')

@section('content')
@php
    $detteActuelle = $client->dettes
        ->whereIn('statut', ['en_cours', 'partiel', 'en_retard'])
        ->sum('montant_restant');
@endphp

<div style="display: flex; gap: 16px; flex-wrap: wrap; align-items: flex-start;">

    <!-- Colonne gauche : profil -->
    <div class="card" style="flex: 1 1 320px;">
        <div class="card-header">
            <h3 style="display:flex; align-items:center; gap:8px;">
                <i class="bi bi-person-fill"></i> {{ $client->nomComplet() }}
            </h3>
            <div style="display:flex; gap:8px;">
                <a href="{{ route('clients.index') }}" class="btn btn-secondary btn-sm">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
                <a href="{{ route('clients.edit', $client) }}" class="btn btn-primary btn-sm">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
            </div>
        </div>

        <div style="text-align:center; padding: 8px 0 4px;">
            <div style="width:64px; height:64px; border-radius:50%; background: color-mix(in srgb, var(--primary) 12%, transparent); color: var(--primary); display:flex; align-items:center; justify-content:center; font-size:24px; font-weight:800; margin: 0 auto 10px;">
                {{ strtoupper(substr($client->nom ?? 'C', 0, 2)) }}
            </div>
            <p style="font-weight:700; font-size:1.05rem; margin-bottom:4px;">{{ $client->nomComplet() }}</p>
            <p style="font-family:'Inter',sans-serif; font-size:0.9rem; color:var(--text-muted);">
                {{ $client->adresse ?: 'Adresse non renseignée' }}
            </p>

            @if(!Auth::user()->isControleur() && $client->telephone)
            <p style="font-family:'Inter',sans-serif; font-size:0.9rem; color:var(--text-muted); margin: 10px 0 8px;">
                <i class="bi bi-telephone-fill"></i>
                <a href="tel:{{ $client->telephone }}">{{ $client->telephone }}</a>
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $client->telephone) }}" target="_blank"
                   style="margin-left:12px; color:#25D366; text-decoration:none; font-weight:600;">
                    <i class="bi bi-whatsapp"></i> WhatsApp
                </a>
            </p>
            @endif
        </div>

        <hr style="border:none; border-top:1px solid var(--border); margin:12px 0;">

        <h3 style="margin-bottom:10px;"><i class="bi bi-graph-up"></i> Bilan Financier</h3>
        <div style="display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f1f5f9;">
            <span style="color:var(--text-muted); font-size:0.9rem;">Limite de crédit autorisée</span>
            <span style="font-weight:600;">{{ number_format($client->limite_credit, 0, ',', ' ') }} FCFA</span>
        </div>
        <div style="display:flex; justify-content:space-between; padding:8px 0;">
            <span style="color:var(--text-muted); font-size:0.9rem;">Dette actuelle en cours</span>
            <span style="font-weight:800; color: {{ $detteActuelle > 0 ? 'var(--danger)' : 'var(--success)' }};">
                {{ number_format($detteActuelle, 0, ',', ' ') }} FCFA
            </span>
        </div>
    </div>

    <!-- Colonne droite : dettes + ventes -->
    <div style="flex: 2 1 420px; display:flex; flex-direction:column; gap:16px;">

        @if($client->dettes && $client->dettes->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-receipt"></i> Dettes & Créances ({{ $client->dettes->count() }})</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Montant total</th>
                            <th>Reste à payer</th>
                            <th>Échéance</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($client->dettes as $d)
                        <tr>
                            <td style="font-weight:600;">#{{ $d->id }}</td>
                            <td>{{ number_format($d->montant_total ?? 0, 0, ',', ' ') }} FCFA</td>
                            <td style="font-weight:700; color:var(--danger);">{{ number_format($d->montant_restant ?? 0, 0, ',', ' ') }} FCFA</td>
                            <td>{{ $d->date_echeance ?: 'N/A' }}</td>
                            <td>
                                <span class="badge badge-{{ $d->statut == 'payee' ? 'success' : ($d->statut == 'en_retard' ? 'danger' : 'warning') }}">
                                    {{ ucfirst(str_replace('_', ' ', $d->statut)) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if($client->ventes && $client->ventes->count() > 0)
        <div class="card">
            <div class="card-header">
                <h3><i class="bi bi-bag-check"></i> Historique d'Achats ({{ $client->ventes->count() }})</h3>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Référence</th>
                            <th>Date</th>
                            <th style="text-align:right;">Montant</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($client->ventes as $v)
                        <tr>
                            <td style="font-weight:600;">{{ $v->reference ?: ('Vente #'.$v->id) }}</td>
                            <td>{{ $v->created_at ? $v->created_at->format('d/m/Y') : '-' }}</td>
                            <td style="text-align:right;">{{ number_format($v->montant_total ?? 0, 0, ',', ' ') }} FCFA</td>
                            <td>
                                <span class="badge badge-{{ $v->statut_paiement == 'paye' ? 'success' : ($v->statut_paiement == 'partiel' ? 'warning' : 'danger') }}">
                                    {{ ucfirst(str_replace('_', ' ', $v->statut_paiement ?? 'non_paye')) }}
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>
</div>
@endsection
