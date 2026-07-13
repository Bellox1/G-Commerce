@extends('layouts.app')
@section('title', 'Dettes Société')
@section('page-title', 'Dettes que la société doit aux fournisseurs')

@section('actions')
<button onclick="document.getElementById('modalAjout').style.display='flex'" class="btn btn-primary">
    <i class="bi bi-plus-circle"></i> Nouvelle dette
</button>
@endsection

@section('content')
{{-- Résumé --}}
<div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(180px,1fr)); gap:16px; margin-bottom:20px;">
    <div class="card">
        <div style="padding:16px;">
            <div style="font-size:.75rem; color:var(--text-muted);">Total à payer</div>
            <div style="font-size:1.3rem; font-weight:800; color:var(--danger);">{{ number_format($totalDettes, 0, ',', ' ') }} F</div>
        </div>
    </div>
    <div class="card">
        <div style="padding:16px;">
            <div style="font-size:.75rem; color:var(--text-muted);">Déjà soldé</div>
            <div style="font-size:1.3rem; font-weight:800; color:var(--success);">{{ number_format($totalSolde, 0, ',', ' ') }} F</div>
        </div>
    </div>
    <div class="card">
        <div style="padding:16px;">
            <div style="font-size:.75rem; color:var(--text-muted);">Nb dettes actives</div>
            <div style="font-size:1.3rem; font-weight:800; color:var(--warning);">{{ $dettes->total() }}</div>
        </div>
    </div>
</div>

{{-- Filtres --}}
<div class="card">
    <div class="card-header" style="flex-wrap:wrap; gap:12px;">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-building"></i> Dettes de la société
        </h3>
        <form method="GET" style="display:flex; gap:8px; flex-wrap:wrap; align-items:center;">
            <select name="statut" class="form-control" style="width:auto;">
                <option value="" disabled {{ !request()->has('statut') ? 'selected' : '' }}>Tous les statuts</option>
                <option value="en_cours" {{ request('statut') == 'en_cours' ? 'selected' : '' }}>En cours</option>
                <option value="partiel" {{ request('statut') == 'partiel' ? 'selected' : '' }}>Partiel</option>
                <option value="solde" {{ request('statut') == 'solde' ? 'selected' : '' }}>Soldé</option>
            </select>
            <select name="fournisseur_id" class="form-control" style="width:auto;">
                <option value="">Tous les fournisseurs</option>
                @foreach($fournisseurs as $f)
                    <option value="{{ $f->id }}" {{ request('fournisseur_id') == $f->id ? 'selected' : '' }}>{{ $f->nom }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="bi bi-funnel"></i> Filtrer</button>
            <a href="{{ route('dettes-societe.index') }}" class="btn btn-secondary btn-sm">Réinitialiser</a>
        </form>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Fournisseur</th>
                    <th>Arrivage</th>
                    <th>Description</th>
                    <th style="text-align:right;">Montant</th>
                    <th style="text-align:right;">Payé</th>
                    <th style="text-align:right;">Reste</th>
                    <th style="text-align:center;">Statut</th>
                    <th style="text-align:center;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dettes as $d)
                @php $reste = $d->montant - $d->montant_paye; @endphp
                <tr>
                    <td>{{ $d->date_dette->format('d/m/Y') }}</td>
                    <td style="font-weight:600;">{{ $d->fournisseur?->nom ?? '—' }}</td>
                    <td>
                        @if($d->arrivage)
                            <span class="badge badge-gray">{{ $d->arrivage->reference }}</span>
                        @else
                            <span style="color:var(--text-muted);">—</span>
                        @endif
                    </td>
                    <td>{{ $d->description ?? '—' }}</td>
                    <td style="text-align:right;">{{ number_format($d->montant, 0, ',', ' ') }} F</td>
                    <td style="text-align:right; color:var(--success);">{{ number_format($d->montant_paye, 0, ',', ' ') }} F</td>
                    <td style="text-align:right; font-weight:700; color:{{ $reste > 0 ? 'var(--danger)' : 'var(--success)' }};">
                        {{ number_format($reste, 0, ',', ' ') }} F
                    </td>
                    <td style="text-align:center;">
                        @if($d->statut === 'solde')
                            <span class="badge badge-success">Soldé</span>
                        @elseif($d->statut === 'partiel')
                            <span class="badge badge-warning">Partiel</span>
                        @else
                            <span class="badge badge-danger">En cours</span>
                        @endif
                    </td>
                    <td style="text-align:center; white-space:nowrap;">
                        <a href="{{ route('dettes-societe.show', $d) }}" class="btn btn-secondary btn-sm">
                            <i class="bi bi-eye"></i>
                        </a>
                        @if($reste > 0)
                        <button onclick="openPaiement({{ $d->id }}, {{ $reste }})" class="btn btn-success btn-sm">
                            <i class="bi bi-cash"></i>
                        </button>
                        @endif
                        <form method="POST" action="{{ route('dettes-societe.destroy', $d) }}" style="display:inline;" onsubmit="return confirm('Supprimer cette dette ?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"><i class="bi bi-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" style="text-align:center; color:var(--text-muted); padding:32px;">Aucune dette société enregistrée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($dettes->hasPages())
    <div style="padding:16px 20px; border-top:1px solid var(--border); display:flex; justify-content:center;">
        {{ $dettes->links() }}
    </div>
    @endif
</div>

{{-- Modal Ajout --}}
<div id="modalAjout" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width:90%; max-width:500px; max-height:90vh; overflow-y:auto;">
        <div class="card-header">
            <h3><i class="bi bi-plus-circle"></i> Nouvelle dette société</h3>
            <button onclick="this.closest('#modalAjout').style.display='none'" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('dettes-societe.store') }}">
                @csrf
                <div class="form-group">
                    <label class="form-label">Fournisseur (optionnel)</label>
                    <select name="fournisseur_id" id="modalFournisseur" class="form-control">
                        <option value="">— Aucun —</option>
                        @foreach($fournisseurs as $f)
                            <option value="{{ $f->id }}">{{ $f->nom }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Arrivage associé (optionnel)</label>
                    <select name="arrivage_id" id="modalArrivage" class="form-control" onchange="changeArrivageDette(this)">
                        <option value="">— Aucun arrivage —</option>
                        @foreach($arrivages as $a)
                            <option value="{{ $a->id }}" data-fournisseur="{{ $a->fournisseur_id }}" data-montant="{{ (int)$a->total_cout_reel }}" data-reference="{{ $a->reference }}">
                                {{ $a->reference }} ({{ number_format($a->total_cout_reel, 0, ',', ' ') }} F)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Montant (FCFA) *</label>
                    <input type="number" name="montant" id="modalMontant" class="form-control" min="1" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <input type="text" name="description" id="modalDescription" class="form-control" placeholder="Ex: Livraison stock, Achat matériel...">
                </div>
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" name="date_dette" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:12px;">
                    <i class="bi bi-check-circle"></i> Enregistrer
                </button>
            </form>
        </div>
    </div>
</div>

{{-- Modal Paiement --}}
<div id="modalPaiement" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1000; align-items:center; justify-content:center;">
    <div class="card" style="width:90%; max-width:420px;">
        <div class="card-header">
            <h3><i class="bi bi-cash-stack"></i> Enregistrer un paiement</h3>
            <button onclick="this.closest('#modalPaiement').style.display='none'" style="background:none; border:none; font-size:1.5rem; cursor:pointer;">&times;</button>
        </div>
        <div class="card-body">
            <form id="formPaiement" method="POST">
                @csrf
                <div class="form-group">
                    <label class="form-label">Reste à payer</label>
                    <div id="paiementReste" style="font-size:1.2rem; font-weight:700; color:var(--danger);"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Montant du paiement (FCFA) *</label>
                    <input type="number" name="montant" id="paiementMontant" class="form-control" min="1" required style="font-size:1.1rem; font-weight:700;">
                </div>
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" name="date_paiement" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Mode de paiement *</label>
                    <select name="mode_paiement" class="form-control" required>
                        <option value="especes">Espèces</option>
                        <option value="virement">Virement</option>
                        <option value="mobile_money">Mobile Money</option>
                        <option value="cheque">Chèque</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Notes</label>
                    <input type="text" name="notes" class="form-control" placeholder="Facultatif">
                </div>
                <button type="submit" class="btn btn-success" style="width:100%; justify-content:center; margin-top:12px;">
                    <i class="bi bi-check-circle"></i> Confirmer le paiement
                </button>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function changeArrivageDette(select) {
    const option = select.options[select.selectedIndex];
    if (!option || !option.value) return;

    const fournisseurId = option.getAttribute('data-fournisseur');
    const montant = option.getAttribute('data-montant');
    const reference = option.getAttribute('data-reference');

    // Mettre à jour le fournisseur
    const fournisseurSelect = document.getElementById('modalFournisseur');
    if (fournisseurSelect && fournisseurId) {
        fournisseurSelect.value = fournisseurId;
    }

    // Mettre à jour le montant
    const montantInput = document.getElementById('modalMontant');
    if (montantInput && montant) {
        montantInput.value = montant;
    }

    // Mettre à jour la description
    const descInput = document.getElementById('modalDescription');
    if (descInput && reference) {
        descInput.value = "Arrivage " + reference;
    }
}

function openPaiement(id, reste) {
    document.getElementById('formPaiement').action = '/dettes-societe/' + id + '/payer';
    document.getElementById('paiementReste').textContent = numberFormat(reste) + ' F';
    document.getElementById('paiementMontant').max = reste;
    document.getElementById('paiementMontant').value = reste;
    document.getElementById('modalPaiement').style.display = 'flex';
}
function numberFormat(n) {
    return n.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
}
</script>
@endpush
@endsection
