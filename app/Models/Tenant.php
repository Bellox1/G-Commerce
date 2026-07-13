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
        'partenaire_id', 'offre_code', 'offre_expires_at',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'offre_expires_at' => 'datetime',
    ];

    public function proprietaire()   { return $this->belongsTo(User::class, 'proprietaire_id'); }
    public function partenaire()     { return $this->belongsTo(User::class, 'partenaire_id'); }
    public function magasins()       { return $this->hasMany(Magasin::class); }
    public function users()          { return $this->hasMany(User::class); }
    public function produits()       { return $this->hasMany(Produit::class); }
    public function fournisseurs()   { return $this->hasMany(Fournisseur::class); }
    public function arrivages()      { return $this->hasMany(Arrivage::class); }
    public function clients()        { return $this->hasMany(Client::class); }
    public function ventes()         { return $this->hasMany(Vente::class); }
    public function dettes()         { return $this->hasMany(Dette::class); }
    public function transferts()     { return $this->hasMany(Transfert::class); }
    public function commissions()    { return $this->hasMany(Commission::class); }

    /**
     * L'offre est-elle active ? (offre à vie OU pas encore expirée)
     */
    public function isOffreActive(): bool
    {
        if (!$this->offre_code) return false;
        if ($this->offre_code === 'locale') return true;
        if (!$this->offre_expires_at) return false;
        return $this->offre_expires_at->isFuture();
    }

    /**
     * L'offre est-elle expirée ?
     */
    public function isOffreExpiree(): bool
    {
        return !$this->isOffreActive();
    }
}
