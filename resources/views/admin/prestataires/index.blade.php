@extends('layouts.app')

@section('title', 'Demandes de Partenaires')
@section('subtitle', 'Validez ou rejetez les demandes de prestataires commerciaux')

@section('content')
<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-person-plus"></i> Demandes reçues ({{ $demandes->count() }})</h3>
    </div>

    @if($demandes->isEmpty())
        <div style="text-align:center; padding:40px 20px; color:var(--text-muted);">
            <i class="bi bi-inbox" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
            <p>Aucune demande de partenariat reçue pour l'instant.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Nom</th>
                        <th>Email</th>
                        <th>Téléphone</th>
                        <th>Entreprise</th>
                        <th>Statut</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($demandes as $d)
                    <tr>
                        <td>{{ $d->created_at->format('d/m/Y') }}</td>
                        <td class="fw-bold">{{ $d->nom }} {{ $d->prenom }}</td>
                        <td>{{ $d->email }}</td>
                        <td>{{ $d->telephone }}</td>
                        <td>{{ $d->entreprise ?? '—' }}</td>
                        <td>
                            @if($d->statut === 'en_attente')
                                <span class="badge badge-warning">En attente</span>
                            @elseif($d->statut === 'approuve')
                                <span class="badge badge-success">Approuvé</span>
                            @else
                                <span class="badge badge-danger">Rejeté</span>
                            @endif
                        </td>
                        <td>
                            @if($d->statut === 'en_attente')
                                <form action="{{ route('admin.prestataires.valider', $d->id) }}" method="POST" style="display:inline;" data-no-api="true">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Valider cette demande ? Un compte prestataire sera créé automatiquement.')">
                                        <i class="bi bi-check-lg"></i> Valider
                                    </button>
                                </form>
                            @else
                                <span class="text-muted">Traité</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
