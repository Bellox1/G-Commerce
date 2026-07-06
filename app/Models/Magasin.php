<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Magasin extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'nom', 'adresse', 'ville', 'loyer',
    ];

    public function tenant()     { return $this->belongsTo(Tenant::class); }
    public function users()      { return $this->hasMany(User::class); }
    public function arrivages()  { return $this->hasMany(Arrivage::class); }
    public function ventes()     { return $this->hasMany(Vente::class); }
    public function mouvements() { return $this->hasMany(StockMouvement::class); }

    /**
     * Calcule le stock d'un produit dans ce magasin via les mouvements
     */
    public function stockProduit(int $produitId): int
    {
        return (int) $this->mouvements()
            ->where('produit_id', $produitId)
            ->selectRaw("SUM(CASE
                WHEN type IN ('entree_arrivage','transfert_entree','ajustement_positif') THEN quantite
                WHEN type IN ('sortie_vente','transfert_sortie','ajustement_negatif') THEN -quantite
                ELSE 0
            END) as total")
            ->value('total');
    }
}
