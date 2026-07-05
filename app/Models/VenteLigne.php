<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VenteLigne extends Model
{
    protected $fillable = [
        'vente_id','produit_id','quantite',
        'prix_conseille','prix_vente','cout_unitaire','total_ligne','unite'
    ];

    public function vente()   { return $this->belongsTo(Vente::class); }
    public function produit() { return $this->belongsTo(Produit::class); }
}
