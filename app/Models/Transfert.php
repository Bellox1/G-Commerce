<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transfert extends Model
{
    protected $fillable = [
        'tenant_id','magasin_source_id','magasin_destination_id',
        'produit_id','user_id','livreur_id','reference',
        'quantite','statut','date_transfert','date_livraison','notes',
    ];

    protected $casts = [
        'date_transfert' => 'datetime',
        'date_livraison' => 'datetime',
    ];

    public function tenant()              { return $this->belongsTo(Tenant::class); }
    public function magasinSource()       { return $this->belongsTo(Magasin::class, 'magasin_source_id'); }
    public function magasinDestination()  { return $this->belongsTo(Magasin::class, 'magasin_destination_id'); }
    public function produit()             { return $this->belongsTo(Produit::class); }
    public function produits()            { return $this->hasMany(TransfertProduit::class); }
    public function user()                { return $this->belongsTo(User::class, 'user_id'); }
    public function livreur()             { return $this->belongsTo(User::class, 'livreur_id'); }
}
