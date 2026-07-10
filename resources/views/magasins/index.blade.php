@extends('layouts.app')
@section('title', 'Dépôts / Magasins')
@section('page-title', 'Gestion des Dépôts')

@push('styles')
<style>
    @media (max-width: 640px) {
        .depot-table td { font-size: .8rem; padding: 8px 8px; }
        .depot-table th { font-size: .7rem; padding: 8px 8px; }
    }
</style>
@endpush

@section('content')
<div class="card">
    <div class="card-header">
        <h3 style="display:flex; align-items:center; gap:8px;">
            <i class="bi bi-shop"></i> Liste des dépôts
            <span style="font-size:0.7rem; background:#f1f5f9; color:#64748b; border-radius:20px; padding:2px 8px; font-weight:600;">{{ count($magasins) }}</span>
        </h3>
        <button type="button" class="btn btn-primary btn-sm" id="btnNewDepot">
            <i class="bi bi-plus-circle"></i> Nouveau Dépôt
        </button>
    </div>

    <div class="table-wrap">
        <table class="depot-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Adresse</th>
                    <th>Ville</th>
                    <th style="text-align: right;">Loyer (FCFA/mois)</th>
                    <th style="text-align: center; width: 80px;">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($magasins as $m)
                <tr>
                    <td style="font-weight: 600;">{{ $m->nom }}</td>
                    <td>{{ $m->adresse ?? '—' }}</td>
                    <td>{{ $m->ville ?? '—' }}</td>
                    <td style="text-align: right;">{{ $m->loyer ? number_format($m->loyer, 0, ',', ' ') . ' F' : '—' }}</td>
                    <td style="text-align: center;">
                        <button type="button" class="btn btn-secondary btn-sm edit-btn"
                            data-id="{{ $m->id }}"
                            data-nom="{{ $m->nom }}"
                            data-adresse="{{ $m->adresse }}"
                            data-ville="{{ $m->ville }}"
                            data-loyer="{{ $m->loyer }}"
                            title="Modifier">
                            <i class="bi bi-pencil"></i>
                        </button>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: var(--text-muted); padding: 32px;">
                        Aucun dépôt enregistré.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Créer / Modifier -->
<div id="depotModal" class="modal-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.4); z-index: 9999; align-items: center; justify-content: center;">
    <div style="background: #fff; border-radius: 12px; padding: 24px; max-width: 480px; width: 90%; box-shadow: 0 20px 60px rgba(0,0,0,0.15);">
        <h4 style="margin: 0 0 4px;" id="modalTitle"><i class="bi bi-shop"></i> Nouveau Dépôt</h4>
        <p style="color: var(--text-muted); font-size: .85rem; margin-bottom: 16px;" id="modalSubtitle">Ajouter un nouveau dépôt à votre société.</p>

        <form method="POST" action="{{ route('magasins.store') }}" id="depotForm">
            @csrf
            <input type="hidden" name="_method" id="formMethod" value="POST">
            <input type="hidden" name="magasin_id" id="magasinId" value="">

            <div class="form-group">
                <label class="form-label">Nom du dépôt <span style="color:#dc2626;">*</span></label>
                <input type="text" name="nom" id="inputNom" class="form-control" placeholder="Dépôt principal" required>
            </div>

            <div class="form-group">
                <label class="form-label">Ville</label>
                <input type="text" name="ville" id="inputVille" class="form-control" placeholder="Yaoundé">
            </div>

            <div class="form-group">
                <label class="form-label">Adresse</label>
                <input type="text" name="adresse" id="inputAdresse" class="form-control" placeholder="123 Rue du Marché">
            </div>

            <div class="form-group">
                <label class="form-label">Loyer mensuel (FCFA) <small style="color:var(--text-muted);">— optionnel</small></label>
                <input type="number" name="loyer" id="inputLoyer" class="form-control" placeholder="Ex: 150000" min="0">
            </div>

            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 20px;">
                <button type="button" class="btn btn-secondary" id="btnAnnuler">Annuler</button>
                <button type="submit" class="btn btn-primary" id="btnSave">
                    <i class="bi bi-check-circle"></i> <span id="btnSaveText">Créer</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('depotModal');
    const form = document.getElementById('depotForm');
    const methodInput = document.getElementById('formMethod');
    const magasinId = document.getElementById('magasinId');
    const inputNom = document.getElementById('inputNom');
    const inputVille = document.getElementById('inputVille');
    const inputAdresse = document.getElementById('inputAdresse');
    const inputLoyer = document.getElementById('inputLoyer');
    const modalTitle = document.getElementById('modalTitle');
    const modalSubtitle = document.getElementById('modalSubtitle');
    const btnSaveText = document.getElementById('btnSaveText');

    // ── Ouvrir modal création ──
    document.getElementById('btnNewDepot').addEventListener('click', function() {
        form.action = '{{ route('magasins.store') }}';
        methodInput.value = 'POST';
        magasinId.value = '';
        form.reset();
        modalTitle.innerHTML = '<i class="bi bi-shop"></i> Nouveau Dépôt';
        modalSubtitle.textContent = 'Ajouter un nouveau dépôt à votre société.';
        btnSaveText.textContent = 'Créer';
        modal.style.display = 'flex';
    });

    // ── Ouvrir modal modification ──
    document.querySelectorAll('.edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            form.action = '/magasins/' + id;
            methodInput.value = 'PUT';
            magasinId.value = id;
            inputNom.value = this.dataset.nom;
            inputVille.value = this.dataset.ville;
            inputAdresse.value = this.dataset.adresse;
            inputLoyer.value = this.dataset.loyer || '';
            modalTitle.innerHTML = '<i class="bi bi-pencil"></i> Modifier le Dépôt';
            modalSubtitle.textContent = 'Modifier les informations du dépôt.';
            btnSaveText.textContent = 'Enregistrer';
            modal.style.display = 'flex';
        });
    });

    // ── Fermer modal ──
    function closeModal() { modal.style.display = 'none'; }

    document.getElementById('btnAnnuler').addEventListener('click', closeModal);
    modal.addEventListener('click', function(e) { if (e.target === this) closeModal(); });
});
</script>
@endpush
