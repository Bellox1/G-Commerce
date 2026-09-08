@extends('layouts.app')

@section('title', 'Prix des offres')
@section('subtitle', 'Définissez les prix d\'abonnement, les commissions et les primes de performance des offres')

@section('content')
<div class="card" style="margin-bottom:24px;">
    <div class="card-header">
        <h3><i class="bi bi-bar-chart-fill"></i> Paliers de primes (nombre de ventes)</h3>
    </div>
    <form action="{{ route('admin.offres.paliers.update') }}" method="POST" data-no-api="true" style="padding:16px; display:flex; flex-wrap:wrap; gap:16px; align-items:flex-end;">
        @csrf
        <p style="width:100%; font-size:0.82rem; color:var(--text-muted); margin:0;">
            Définissez les seuils de ventes déclenchant les primes (ex. 5/10/15, 3/7/10 ou 2/5/10). Ces paliers s'appliquent à toutes les offres.
        </p>
        @foreach($paliers as $i => $p)
            <div>
                <label class="form-label" style="font-size:.8rem;">Palier {{ $i + 1 }} (ventes)</label>
                <input type="number" name="paliers[]" class="form-control" min="1" step="1" value="{{ $p }}" style="max-width:120px;" required>
            </div>
        @endforeach
        <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Enregistrer les paliers</button>
    </form>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-tag-fill"></i> Prix d'abonnement, Commissions &amp; Primes ({{ $regles->count() }})</h3>
    </div>

    @if($regles->isEmpty())
        <div style="text-align:center; padding:40px 20px; color:var(--text-muted);">
            <i class="bi bi-inbox" style="font-size:2.5rem; display:block; margin-bottom:12px;"></i>
            <p>Aucune offre configurée.</p>
        </div>
    @else
        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Offre</th>
                        <th>Code</th>
                        <th>Prix Cash (F)</th>
                        <th>Prix / Tranche 3x (F)</th>
                        <th>Commission (F)</th>
                        @foreach($paliers as $seuil)
                            <th>Prime {{ $seuil }} ventes (F)</th>
                        @endforeach
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($regles as $r)
                    <form action="{{ route('admin.offres.update', $r->code) }}" method="POST" data-no-api="true">
                        @csrf
                        <tr>
                            <td class="fw-bold">{{ $r->nom }}</td>
                            <td><span class="text-muted" style="font-size:0.8rem;">{{ $r->code }}</span></td>
                            <td><input type="number" name="prix" class="form-control" min="0" step="1" value="{{ $r->prix }}" style="max-width:130px;" required></td>
                            <td><input type="number" name="prix_tranche_3x" class="form-control" min="0" step="1" value="{{ $r->prix_tranche_3x }}" placeholder="ex: 12000" style="max-width:130px;"></td>
                            <td><input type="number" name="commission" class="form-control" min="0" step="1" value="{{ $r->commission }}" style="max-width:130px;" required></td>
                            @foreach($paliers as $seuil)
                                <td><input type="number" name="prime_{{ $seuil }}" class="form-control" min="0" step="1" value="{{ $r->primePour($seuil) }}" style="max-width:150px;" required></td>
                            @endforeach
                            <td><button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-check-lg"></i> Enregistrer</button></td>
                        </tr>
                    </form>
                    @endforeach
                </tbody>
            </table>
        </div>
        <p style="padding:12px 16px; font-size:0.8rem; color:var(--text-muted); margin:0;">
            Les primes de performance sont attribuées par offre selon le nombre de ventes de <strong>cette offre</strong> (paliers {{ implode(', ', $paliers) }} ventes).
        </p>
    @endif
</div>
@endsection
