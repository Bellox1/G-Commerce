@extends('layouts.app')
@section('title', $produit->nom)
@section('page-title', $produit->nom)

@section('content')
<div style="display: flex; flex-direction: column; gap: 20px;">
    {{-- En-tête --}}
    <div class="card">
        <div class="card-body" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
            <div style="display: flex; align-items: center; gap: 16px;">
                @if($produit->image)
                    @php $imgSrc = str_starts_with($produit->image, 'http') ? $produit->image : asset('storage/' . $produit->image); @endphp
                    <img src="{{ $imgSrc }}" alt="{{ $produit->nom }}" onclick="openImageModal('{{ $imgSrc }}')"
                         style="width: 64px; height: 64px; border-radius: 8px; object-fit: cover; border: 1px solid var(--border); cursor: pointer;" title="Cliquer pour agrandir" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                    <span style="display:none; width:64px; height:64px; border-radius:8px; background:#f1f5f9; align-items:center; justify-content:center; color:#94a3b8; font-size:1.5rem;">
                        <i class="bi bi-image"></i>
                    </span>
                @else
                    <span style="display: inline-flex; width: 64px; height: 64px; border-radius: 8px; background: #f1f5f9; align-items: center; justify-content: center; color: #94a3b8; font-size: 1.5rem;">
                        <i class="bi bi-image"></i>
                    </span>
                @endif
                <div>
                    <h2 style="font-size: 1.4rem; font-weight: 700; margin: 0;">{{ $produit->nom }}</h2>
                    <div style="font-size: .8rem; color: var(--text-muted); margin-top: 4px;">
                        Réf: PRD-{{ $produit->id }}
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 8px;">
                <a href="{{ route('produits.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Liste
                </a>
                @if(Auth::user()->peutModifierCatalogues())
                <a href="{{ route('produits.edit', $produit) }}" class="btn btn-primary">
                    <i class="bi bi-pencil"></i> Modifier
                </a>
                @endif
            </div>
        </div>
    </div>

    <div class="page-grid page-grid-3cols">
        {{-- Colonne gauche : Image + Infos --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            {{-- Image --}}
            <div class="card">
                <div class="card-body" style="padding: 16px; display: flex; justify-content: center; position: relative;">
                    @if($produit->image)
                        @php $imgSrc = str_starts_with($produit->image, 'http') ? $produit->image : asset('storage/' . $produit->image); @endphp
                        <div style="position: relative; cursor: pointer; display: inline-block;" onclick="openImageModal('{{ $imgSrc }}')" title="Cliquer pour agrandir en grand écran">
                            <img src="{{ $imgSrc }}" alt="{{ $produit->nom }}"
                                 style="max-width: 100%; max-height: 300px; border-radius: 8px; object-fit: contain; display: block;" onerror="this.parentElement.style.display='none'; this.parentElement.nextElementSibling.style.display='inline-flex';">
                            <div style="position: absolute; bottom: 8px; right: 8px; background: rgba(15,23,42,0.7); color: #fff; border-radius: 6px; padding: 4px 8px; font-size: 0.75rem; display: flex; align-items: center; gap: 4px;">
                                <i class="bi bi-arrows-angle-expand"></i> Agrandir
                            </div>
                        </div>
                        <span style="display:none; width:200px; height:200px; border-radius:8px; background:#f1f5f9; align-items:center; justify-content:center; color:#94a3b8; font-size:3rem;">
                            <i class="bi bi-image"></i>
                        </span>
                    @else
                        <span style="display: inline-flex; width: 200px; height: 200px; border-radius: 8px; background: #f1f5f9; align-items: center; justify-content: center; color: #94a3b8; font-size: 3rem;">
                            <i class="bi bi-image"></i>
                        </span>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            @if($produit->description)
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-card-text"></i> Description</h3>
                </div>
                <div class="card-body">
                    <p style="margin: 0; font-size: .9rem; line-height: 1.6; color: var(--text); white-space: pre-wrap;">{{ $produit->description }}</p>
                </div>
            </div>
            @endif
        </div>

        {{-- Colonne milieu : Infos prix & stock --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-coin"></i> Prix & Stock</h3>
                </div>
                <div class="card-body" style="padding: 0;">
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Prix de vente conseillé :</span>
                        <span style="font-weight: 700; font-size: .9rem; color: var(--primary);">{{ number_format($produit->prix_vente_conseille, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Prix marché :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ number_format($produit->prix_marche, 0, ',', ' ') }} FCFA</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Seuil d'alerte :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ $produit->seuil_alerte }}</span>
                    </div>
                    @if($produit->a_cartouche)
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: 1px solid var(--border);">
                        <span style="font-size: .85rem;">Cartouches par carton :</span>
                        <span style="font-weight: 600; font-size: .85rem;">{{ $produit->cartouche_par_carton }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px;">
                        <span style="font-size: .85rem;">Prix cartouche :</span>
                        <span style="font-weight: 600; font-size: .85rem; color: var(--primary);">{{ number_format($produit->prix_cartouche_effectif, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Stock par magasin --}}
            <div class="card">
                <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                    <h3><i class="bi bi-boxes"></i> Stock par Magasin</h3>
                    <a href="{{ route('produits.stocks.edit', $produit) }}" class="btn btn-secondary btn-sm">
                        <i class="bi bi-pencil"></i> Gérer les stocks
                    </a>
                </div>
                <div class="card-body" style="padding: 0;">
                    @forelse($magasins as $m)
                    @php $stock = $stockParMagasin[$m->id] ?? 0; @endphp
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; border-bottom: {{ !$loop->last ? '1px solid var(--border)' : 'none' }};">
                        <span style="font-size: .85rem;">{{ $m->nom }}</span>
                        <span class="badge {{ $stock > 0 ? 'badge-success' : 'badge-danger' }}">
                            {{ $stock }} Carton
                        </span>
                    </div>
                    @empty
                    <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: .85rem;">Aucun magasin</div>
                    @endforelse
                    @if($magasins->isNotEmpty())
                    <div style="display: flex; justify-content: space-between; padding: 12px 16px; background: var(--bg-light); font-weight: 700;">
                        <span style="font-size: .85rem;">Total centralisé</span>
                        <span>{{ $produit->stockGlobal() }} Carton</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Colonne droite : Mouvements récents --}}
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <div class="card-header">
                    <h3><i class="bi bi-clock-history"></i> Mouvements Récents</h3>
                </div>
                <div class="card-body" style="padding: 0; max-height: 400px; overflow-y: auto;">
                    @forelse($mouvements as $mvt)
                    @php
                        $typeConfig = [
                            'entree_arrivage'     => ['label'=>'Arrivage',          'icon'=>'bi-box-arrow-in-down',    'badge'=>'badge-success'],
                            'sortie_vente'        => ['label'=>'Sortie Vente',       'icon'=>'bi-cart-dash',             'badge'=>'badge-danger'],
                            'transfert_entree'    => ['label'=>'Transfert Entrée',  'icon'=>'bi-arrow-down-circle',    'badge'=>'badge-success'],
                            'transfert_sortie'    => ['label'=>'Transfert Sortie',  'icon'=>'bi-arrow-up-circle',      'badge'=>'badge-warning'],
                            'ajustement_positif'  => ['label'=>'Ajust. +',          'icon'=>'bi-plus-circle',          'badge'=>'badge-success'],
                            'ajustement_negatif'  => ['label'=>'Ajust. -',          'icon'=>'bi-dash-circle',          'badge'=>'badge-danger'],
                        ];
                        $cfg = $typeConfig[$mvt->type] ?? ['label'=>ucfirst($mvt->type), 'icon'=>'bi-arrow-repeat', 'badge'=>'badge-gray'];
                        $isEntree = $mvt->signe() > 0;

                        $refLibelle = match ($mvt->type) {
                            'sortie_vente'     => 'Vente',
                            'entree_arrivage'  => 'Arrivage',
                            'transfert_entree' => 'Transfert entrée',
                            'transfert_sortie' => 'Transfert sortie',
                            default            => null,
                        };
                        $refText = $mvt->reference?->reference
                            ? ($refLibelle ? $refLibelle.' '.$mvt->reference->reference : $mvt->reference->reference)
                            : ($mvt->note ?: $cfg['label']);
                        $desc = ($mvt->magasin?->nom ?? '');
                        if ($refText) $desc .= ' · '.$refText;
                        $desc .= ' ('.$mvt->quantite.' Carton'.($mvt->quantite > 1 ? 's' : '').')';
                    @endphp
                    <div style="display: flex; align-items: center; gap: 10px; padding: 10px 16px; border-bottom: 1px solid var(--border); font-size: .8rem;">
                        <div style="width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:.9rem;"
                             class="{{ $cfg['badge'] }}">
                            <i class="bi {{ $cfg['icon'] }}"></i>
                        </div>
                        <div style="flex: 1; min-width:0;">
                            <div style="font-weight: 600;">{{ $cfg['label'] }}</div>
                            <div style="color: var(--text-muted); font-size: .75rem; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                {{ $desc }}
                            </div>
                        </div>
                        <div style="text-align:right; flex-shrink:0;">
                            <div style="font-weight:700; font-size:.9rem; color:{{ $isEntree ? 'var(--success)' : 'var(--danger)' }};">
                                {{ $isEntree ? '+' : '-' }}{{ $mvt->quantite }}
                            </div>
                            <div style="color: var(--text-muted); font-size: .72rem;">{{ $mvt->date_mouvement?->fr('d F Y') }}</div>
                        </div>
                    </div>
                    @empty
                    <div style="padding: 16px; text-align: center; color: var(--text-muted); font-size: .85rem;">
                        Aucun mouvement enregistré.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal d'agrandissement d'image --}}
<div id="imageModal" style="display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(15,23,42,0.85); z-index:99999; justify-content:center; align-items:center; backdrop-filter:blur(4px);" onclick="closeImageModal()">
    <div style="position:relative; max-width:92vw; max-height:92vh; display:flex; flex-direction:column; align-items:center;" onclick="event.stopPropagation()">
        <button type="button" onclick="closeImageModal()" style="position:absolute; top:-40px; right:0; background:rgba(255,255,255,0.2); border:none; color:#fff; width:36px; height:36px; border-radius:50%; font-size:1.2rem; cursor:pointer; display:flex; align-items:center; justify-content:center;">
            <i class="bi bi-x-lg"></i>
        </button>
        <img id="imageModalSrc" src="" alt="Agrandissement" style="max-width:90vw; max-height:85vh; border-radius:12px; box-shadow:0 20px 40px rgba(0,0,0,0.6); object-fit:contain; background:#fff;">
    </div>
</div>

<script>
function openImageModal(src) {
    const modal = document.getElementById('imageModal');
    const img = document.getElementById('imageModalSrc');
    img.src = src;
    modal.style.display = 'flex';
}
function closeImageModal() {
    document.getElementById('imageModal').style.display = 'none';
}
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeImageModal();
});
</script>
@endsection