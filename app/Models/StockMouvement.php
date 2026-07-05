<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockMouvement extends Model
{
    protected $fillable = [
        'tenant_id','magasin_id','produit_id','user_id',
        'type','quantite','cout_unitaire',
        'reference_type','reference_id','note','date_mouvement',
    ];

    protected $casts = ['date_mouvement' => 'datetime'];

    public function tenant()  { return $this->belongsTo(Tenant::class); }
    public function magasin() { return $this->belongsTo(Magasin::class); }
    public function produit() { return $this->belongsTo(Produit::class); }
    public function user()    { return $this->belongsTo(User::class); }

    /**
     * Signe du mouvement pour calcul du stock : +1 pour entrée, -1 pour sortie
     */
    public function signe(): int
    {
        return in_array($this->type, [
            'entree_arrivage', 'transfert_entree', 'ajustement_positif'
        ]) ? 1 : -1;
    }
}
