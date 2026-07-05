<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArrivageProduit extends Model
{
    protected $fillable = [
        'arrivage_id','produit_id','fournisseur_id','quantite',
        'prix_unitaire_origine','total_origine',
        'valeur_fcfa','part_frais',
        'cout_unitaire_reel','cout_total_reel','prix_vente_suggere',
    ];

    public function arrivage()   { return $this->belongsTo(Arrivage::class); }
    public function produit()    { return $this->belongsTo(Produit::class); }
    public function fournisseur(){ return $this->belongsTo(Fournisseur::class); }
}
