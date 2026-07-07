<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nom', 'marque', 'activite', 'pays', 'ville',
        'telephone', 'email', 'logo', 'actif', 'proprietaire_id',
    ];

    protected $casts = ['actif' => 'boolean'];

    public function proprietaire()   { return $this->belongsTo(User::class, 'proprietaire_id'); }
    public function magasins()       { return $this->hasMany(Magasin::class); }
    public function users()          { return $this->hasMany(User::class); }
    public function produits()       { return $this->hasMany(Produit::class); }
    public function fournisseurs()   { return $this->hasMany(Fournisseur::class); }
    public function arrivages()      { return $this->hasMany(Arrivage::class); }
    public function clients()        { return $this->hasMany(Client::class); }
    public function ventes()         { return $this->hasMany(Vente::class); }
    public function dettes()         { return $this->hasMany(Dette::class); }
    public function transferts()     { return $this->hasMany(Transfert::class); }
}
