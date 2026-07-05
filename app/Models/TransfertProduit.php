<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransfertProduit extends Model
{
    protected $table = 'transfert_produits';

    protected $fillable = [
        'transfert_id', 'produit_id', 'quantite',
    ];

    public function transfert() { return $this->belongsTo(Transfert::class); }
    public function produit()   { return $this->belongsTo(Produit::class); }
}
