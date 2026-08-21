<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produit extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id','nom',
        'prix_vente_conseille','prix_marche','seuil_alerte','image','description','actif',
        'stock','a_cartouche','cartouche_par_carton','prix_cartouche','stock_cartouches',
    ];

    protected $appends = [
        'prix_cartouche_effectif',
    ];

    protected $casts = [
        'actif'                  => 'boolean',
        'a_cartouche'            => 'boolean',
        'prix_vente_conseille'   => 'decimal:2',
        'prix_marche'            => 'decimal:2',
        'prix_cartouche'         => 'decimal:2',
        'stock_cartouches'       => 'integer',
        'alerte_notifiee'        => 'boolean',
    ];

    public function tenant()          { return $this->belongsTo(Tenant::class); }
    public function arrivageProduits(){ return $this->hasMany(ArrivageProduit::class); }
    public function mouvements()      { return $this->hasMany(StockMouvement::class); }
    public function venteLignes()     { return $this->hasMany(VenteLigne::class); }

    /**
     * Cartouches par carton effectif (1 si le produit n'est pas en cartouches).
     */
    public function cartouchesParCartonEffectif(): int
    {
        return ($this->a_cartouche && $this->cartouche_par_carton)
            ? (int) $this->cartouche_par_carton
            : 1;
    }

    /**
     * Recalcule le stock canonique en cartouches (unités) à partir des mouvements.
     * total = Σ (quantité en cartouches × cpc + quantité en cartouches isolées),
     * signé par le type de mouvement.
     */
    public function stockCartouchesTotal(): int
    {
        $cpc = $this->cartouchesParCartonEffectif();
        return (int) \App\Models\StockMouvement::where('produit_id', $this->id)
            ->selectRaw("GREATEST(0, SUM(CASE
                WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN (quantite * ? + quantite_cartouche)
                WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -(quantite * ? + quantite_cartouche)
                ELSE 0
            END)) as total", [$cpc, $cpc])
            ->value('total') ?? 0;
    }

    /**
     * Nombre de cartons complets (floor du stock en cartouches / cpc).
     */
    public function stockGlobal(): int
    {
        $cpc = $this->cartouchesParCartonEffectif();
        $total = $this->stockCartouchesTotal();
        return intdiv($total, $cpc);
    }

    /**
     * Cartouches restantes (ne formant pas un carton complet).
     */
    public function stockCartouchesRestantes(): int
    {
        $cpc = $this->cartouchesParCartonEffectif();
        $total = $this->stockCartouchesTotal();
        return $total % $cpc;
    }

    /**
     * Synchronise les colonnes cached stock + stock_cartouches depuis les mouvements.
     */
    public function syncStock(): void
    {
        $cpc = $this->cartouchesParCartonEffectif();
        $total = $this->stockCartouchesTotal();
        $this->update([
            'stock'            => intdiv($total, $cpc),
            'stock_cartouches' => $total % $cpc,
        ]);
    }

    public function getPrixCartoucheEffectifAttribute(): ?float
    {
        if (!$this->a_cartouche || !$this->cartouche_par_carton) return null;
        if ($this->prix_cartouche) return (float) $this->prix_cartouche;
        if ($this->prix_vente_conseille) return round($this->prix_vente_conseille / $this->cartouche_par_carton);
        return null;
    }

    /**
     * Arrondi du prix selon les règles du marché béninois
     * Évite les terminaisons 50 et 75
     */
    public static function arrondir(float $prix): float
    {
        if ($prix < 30000) return ceil($prix / 100) * 100;
        if ($prix < 50000) return ceil($prix / 500) * 500;
        return ceil($prix / 1000) * 1000;
    }
}
