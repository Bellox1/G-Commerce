@extends('layouts.app')
@section('title', 'Suivi des Ventes')
@section('page-title', 'Facturation & Ventes')

@section('content')

{{-- Modal choisir client pour conversion dette --}}
<div id="detteModal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 12px; padding: 24px; max-width: 420px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
        <h4 style="margin: 0 0 4px;"><i class="bi bi-credit-card-2-back"></i> Convertir en dette</h4>
        <p style="color: var(--text-muted); font-size: .85rem; margin-bottom: 16px;">Sélectionnez le client pour la facture <strong id="detteRef"></strong></p>
        <form method="POST" action="" id="detteForm">
            @csrf
            <div class="form-group">
                <label class="form-label">Client *</label>
                <select name="client_id" id="detteClientSelect" class="form-control" required>
                    <option value="">-- Choisir un client --</option>
                    @foreach(App\Models\Client::where('tenant_id', auth()->user()->tenant_id)->get() as $c)
                        <option value="{{ $c->id }}">{{ $c->nomComplet() }} ({{ $c->telephone }})</option>
                    @endforeach
                </select>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" onclick="document.getElementById('detteModal').style.display='none'">Annuler</button>
                <button type="submit" class="btn btn-warning"><i class="bi bi-credit-card-2-back"></i> Confirmer la dette</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3><i class="bi bi-receipt"></i> Historique des ventes</h3>
        <a href="{{ route('ventes.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Nouvelle Vente
        </a>
    </div>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Facture N°</th>
                    <th>Date</th>
                    <th>Établi par</th>
                    <th>Magasin</th>
                    <th>Client</th>
                    <th style="text-align: right;">Montant Total</th>
                    <th style="text-align: right;">Montant Payé</th>
                    <th style="text-align: right;">Reste à payer</th>
                    <th style="text-align: center;">Statut règlement</th>
                    <th style="text-align: center; width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ventes as $v)
                <tr>
                    <td style="font-weight: 600;">
                        <a href="{{ route('ventes.show', $v) }}" style="color: var(--primary); text-decoration: none;">
                            {{ $v->reference }}
                        </a>
                    </td>
                    <td>{{ $v->date_vente->format('d/m/Y H:i') }}</td>
                    <td>{{ $v->user?->name }}</td>
                    <td><span class="badge badge-gray">{{ $v->magasin?->nom }}</span></td>
                    <td>{{ $v->client?->nomComplet() ?? 'Vente Directe (Anonyme)' }}</td>
                    <td style="text-align: right; font-weight: 600;">{{ number_format($v->montant_total, 0, ',', ' ') }} FCFA</td>
                    <td style="text-align: right; color: var(--success); font-weight: 500;">
                        {{ number_format($v->montant_paye, 0, ',', ' ') }} FCFA
                    </td>
                    <td style="text-align: right; color: {{ $v->montant_reste > 0 ? 'var(--danger)' : 'var(--text-muted)' }}; font-weight: 600;">
                        {{ number_format($v->montant_reste, 0, ',', ' ') }} FCFA
                    </td>
                    <td style="text-align: center;">
                        @if($v->statut_paiement === 'paye')
                            <span class="badge badge-success"><i class="bi bi-check-lg"></i> Reglé</span>
                        @elseif($v->statut_paiement === 'partiel')
                            <span class="badge badge-warning"><i class="bi bi-clock"></i> Partiel</span>
                        @else
                            <span class="badge badge-danger"><i class="bi bi-exclamation-triangle"></i> Non reglé</span>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        <div style="display: flex; gap: 4px; justify-content: center;">
                            <a href="{{ route('ventes.show', $v) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;" title="Voir">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('ventes.edit', $v) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if(!$v->dette && $v->statut_paiement === 'paye')
                            <button type="button" class="btn btn-warning btn-sm" style="padding: 4px 8px;"
                                onclick="convertirDette({{ $v->id }}, '{{ $v->reference }}')"
                                title="Marquer comme dette">
                                <i class="bi bi-credit-card-2-back"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" style="text-align: center; color: var(--text-muted); padding: 32px;">Aucune vente enregistrée.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($ventes->hasPages())
    <div style="padding: 16px 20px; border-top: 1px solid var(--border); display: flex; justify-content: center;">
        {{ $ventes->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function convertirDette(id, ref, hasClient) {
    const modal = document.getElementById('detteModal');
    document.getElementById('detteRef').textContent = ref;
    document.getElementById('detteForm').action = '/ventes/' + id + '/convertir-dette';
    modal.style.display = 'flex';
}
// Fermer le modal en cliquant à l'extérieur
document.getElementById('detteModal').addEventListener('click', function(e) {
    if (e.target === this) this.style.display = 'none';
});
</script>
@endpush
