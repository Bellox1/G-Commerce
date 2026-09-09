@extends('layouts.app')
@section('title', 'Suivi des Ventes')
@section('page-title', 'Facturation & Ventes')

@section('content')

{{-- ── Ventes hors ligne en attente de synchronisation ── --}}
<div id="offline-ventes-wrap" style="display:none; margin-bottom:16px;"></div>

@push('scripts')
<script>
// Afficher les ventes offline en attente dans la liste
(async function() {
    if (!window.PilotixOffline) return;
    const pending = await PilotixOffline.getPendingVentes();
    if (!pending.length) return;

    const wrap = document.getElementById('offline-ventes-wrap');
    if (!wrap) return;
    wrap.style.display = 'block';

    const fmt = function(n) { return Number(n).toLocaleString('fr-FR') + ' FCFA'; };
    const fmtDate = function(iso) {
        const d = new Date(iso);
        return d.toLocaleDateString('fr-FR', { day:'2-digit', month:'short', year:'numeric' }) + ' ' + d.toLocaleTimeString('fr-FR', { hour:'2-digit', minute:'2-digit' });
    };

    let html = `
    <div style="background:#fff;border-radius:8px;border:2px solid #f59e0b;box-shadow:0 2px 8px rgba(245,158,11,.15);overflow:hidden;">
        <div style="background:#fef3c7;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <span style="font-weight:700;font-size:.9rem;color:#92400e;display:flex;align-items:center;gap:8px;">
                <i class="bi bi-wifi-off"></i>
                ${pending.length} vente${pending.length>1?'s':''} hors ligne en attente de synchronisation
            </span>
            <button id="manualSyncBtn" style="background:#f59e0b;color:#fff;border:none;padding:6px 14px;border-radius:6px;font-weight:700;font-size:.82rem;cursor:pointer;">
                <i class="bi bi-arrow-repeat"></i> Synchroniser maintenant
            </button>
        </div>
        <div style="overflow-x:auto;">
        <table style="width:100%;border-collapse:collapse;font-size:.85rem;">
            <thead><tr style="background:#fffbeb;">
                <th style="padding:8px 14px;text-align:left;color:#92400e;font-size:.72rem;text-transform:uppercase;font-weight:700;">Statut</th>
                <th style="padding:8px 14px;text-align:left;color:#92400e;font-size:.72rem;text-transform:uppercase;font-weight:700;">Date</th>
                <th style="padding:8px 14px;text-align:left;color:#92400e;font-size:.72rem;text-transform:uppercase;font-weight:700;">Magasin</th>
                <th style="padding:8px 14px;text-align:left;color:#92400e;font-size:.72rem;text-transform:uppercase;font-weight:700;">Client(s)</th>
                <th style="padding:8px 14px;text-align:right;color:#92400e;font-size:.72rem;text-transform:uppercase;font-weight:700;">Total</th>
            </tr></thead>
            <tbody>`;

    pending.forEach(function(item) {
        const d = item._display || {};
        const ventes = d.ventes || [];
        const total  = ventes.reduce(function(s, v) { return s + (v.total || 0); }, 0);
        const clients = ventes.map(function(v) { return v.client || 'Anonyme'; }).join(', ');
        html += `<tr style="border-top:1px solid #fde68a;">
            <td style="padding:10px 14px;"><span style="background:#fef3c7;color:#b45309;border-radius:20px;padding:3px 10px;font-size:.75rem;font-weight:700;white-space:nowrap;"><i class="bi bi-clock"></i> En attente</span></td>
            <td style="padding:10px 14px;color:#64748b;white-space:nowrap;">${fmtDate(item._created_at)}</td>
            <td style="padding:10px 14px;">${d.magasin || '—'}</td>
            <td style="padding:10px 14px;">${clients}</td>
            <td style="padding:10px 14px;text-align:right;font-weight:700;">${fmt(total)}</td>
        </tr>`;
    });

    html += `</tbody></table></div></div>`;
    wrap.innerHTML = html;

    document.getElementById('manualSyncBtn').addEventListener('click', async function() {
        if (!navigator.onLine) {
            alert('Vous êtes hors ligne. Connectez-vous pour synchroniser.');
            return;
        }
        this.disabled = true;
        this.innerHTML = '<i class="bi bi-arrow-repeat" style="animation:spin .8s linear infinite;display:inline-block"></i> Sync…';
        const result = await PilotixOffline.syncAll();
        if (result.ventes > 0) {
            alert('✅ ' + result.ventes + ' vente(s) synchronisée(s).');
            window.location.reload();
        } else if (result.errors > 0) {
            alert('⚠ ' + result.errors + ' erreur(s). Vérifiez la connexion.');
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-arrow-repeat"></i> Réessayer';
        }
    });
})();
</script>
@endpush

<div class="card">
    <div class="card-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-receipt"></i> Historique des ventes
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ method_exists($ventes, 'total') ? $ventes->total() : $ventes->count() }}</span>
        </h3>
        <a href="{{ route('ventes.create') }}" class="btn btn-primary btn-sm">
            <i class="bi bi-plus-circle"></i> Nouvelle Vente
        </a>
    </div>

    @php
        $periodeActive = request('periode', 'aujourd_hui');
        $periodeDefs = [
            'avant_hier' => 'Avant-hier',
            'hier' => 'Hier',
            'aujourd_hui' => "Aujourd'hui",
            'tous' => 'Toutes les ventes',
        ];
    @endphp
    <div style="display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 12px;">
        @foreach($periodeDefs as $p => $label)
            <a href="{{ route('ventes.index', array_filter(['periode' => $p])) }}"
               class="btn btn-sm {{ $periodeActive === $p ? 'btn-primary' : 'btn-secondary' }}">{{ $label }}</a>
        @endforeach
        <a href="{{ route('ventes.index', array_filter(['periode' => 'perso', 'date_debut' => $dateDebut, 'date_fin' => $dateFin])) }}"
           class="btn btn-sm {{ $periodeActive === 'perso' ? 'btn-primary' : 'btn-secondary' }}">Personnaliser</a>
    </div>

    @if($periodeActive === 'perso')
    <form method="GET" style="display: flex; gap: 10px; flex-wrap: wrap; align-items: flex-end; margin-bottom: 12px;">
        <input type="hidden" name="periode" value="perso">
        <div>
            <label class="form-label" style="margin-bottom: 4px;">Du</label>
            <input type="date" name="date_debut" class="form-control" value="{{ $dateDebut }}">
        </div>
        <div>
            <label class="form-label" style="margin-bottom: 4px;">Au</label>
            <input type="date" name="date_fin" class="form-control" value="{{ $dateFin }}">
        </div>
        <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
    </form>
    @endif

    <div class="table-search-wrap">
        <div class="table-search-field">
            <i class="bi bi-search table-search-icon"></i>
            <input type="text" class="table-search-input" placeholder="Rechercher une vente (référence, client, magasin...)">
        </div>
        <span class="table-search-count"></span>
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
                    <td>{{ $v->date_vente->fr('d F Y H:i') }}</td>
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
                            <a href="{{ route('ventes.show', $v) }}" class="btn btn-primary btn-sm" style="padding: 4px 8px;" title="Imprimer la facture" onclick="window.open(this.href,'_blank');setTimeout(function(){var w=window.open(this.href,'_blank');w&&w.addEventListener('load',function(){w.print();});},100);return false;">
                                <i class="bi bi-printer"></i>
                            </a>
                            <a href="{{ route('ventes.edit', $v) }}" class="btn btn-secondary btn-sm" style="padding: 4px 8px;" title="Modifier">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" action="{{ route('ventes.destroy', $v) }}" style="display:inline;" onsubmit="return confirm('Attention : Êtes-vous sûr de vouloir supprimer la vente #{{ $v->reference }} ? Le stock sera automatiquement réajusté.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" style="padding: 4px 8px;" title="Supprimer la vente">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
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
