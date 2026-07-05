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
        'stock','a_cartouche','cartouche_par_carton','prix_cartouche',
    ];

    protected $casts = [
        'actif'                  => 'boolean',
        'a_cartouche'            => 'boolean',
        'prix_vente_conseille'   => 'decimal:2',
        'prix_marche'            => 'decimal:2',
        'prix_cartouche'         => 'decimal:2',
    ];

    public function tenant()          { return $this->belongsTo(Tenant::class); }
    public function arrivageProduits(){ return $this->hasMany(ArrivageProduit::class); }
    public function mouvements()      { return $this->hasMany(StockMouvement::class); }
    public function venteLignes()     { return $this->hasMany(VenteLigne::class); }

    public function stockGlobal(): int
    {
        return (int) $this->stock;
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
