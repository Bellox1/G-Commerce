<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable, SoftDeletes, HasApiTokens;

    protected $fillable = [
        'tenant_id','magasin_id','name','email','telephone','salaire',
        'role','roles_secondaires','actif','password',
    ];

    protected $hidden = ['password','remember_token'];
    protected $casts  = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'roles_secondaires' => 'array',
        'actif'             => 'boolean',
    ];

    // Relations
    public function tenant()  { return $this->belongsTo(Tenant::class); }
    public function magasin() { return $this->belongsTo(Magasin::class); }
    public function ventes()  { return $this->hasMany(Vente::class); }
    public function commissions() { return $this->hasMany(Commission::class, 'partenaire_id'); }
    public function tenantsCrees() { return $this->hasMany(Tenant::class, 'partenaire_id'); }

    // Vérifications de rôle principal
    public function isSuperAdmin(): bool { return $this->role === 'super_admin'; }
    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isVendeur(): bool    { return $this->role === 'vendeur'; }
    public function isLivreur(): bool    { return $this->role === 'livreur'; }
    public function isMagasinier(): bool { return $this->role === 'magasinier'; }
    public function isPrestataire(): bool { return $this->role === 'prestataire'; }

    /**
     * Vérifie si l'utilisateur a un rôle (principal ou secondaire)
     */
    public function hasRole(string $role): bool
    {
        if ($this->role === $role) return true;
        $secondaires = $this->roles_secondaires ?? [];
        return in_array($role, $secondaires);
    }

    /**
     * Vérifie si l'utilisateur peut gérer les utilisateurs (employés)
     */
    public function peutGererUtilisateurs(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }

    /**
     * Vérifie si l'utilisateur peut gérer les arrivages
     */
    public function peutGererArrivages(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->hasRole('magasinier');
    }

    // ─── Permissions par module ─────────────────────────
 
    public function peutGererProduits(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->hasRole('magasinier') || $this->hasRole('vendeur');
    }

    public function peutModifierCatalogues(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->hasRole('magasinier');
    }
 
    public function peutGererMagasins(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin();
    }
 
    public function peutGererVentes(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->hasRole('vendeur');
    }
 
    public function peutGererClients(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->hasRole('vendeur') || $this->hasRole('magasinier');
    }
 
    public function peutGererDettes(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->hasRole('vendeur');
    }
 
    public function peutGererStock(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->hasRole('magasinier') || $this->hasRole('vendeur');
    }
 
    public function peutGererTransferts(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->hasRole('magasinier');
    }

    public function peutGererLivraisons(): bool
    {
        return $this->isSuperAdmin() || $this->isAdmin() || $this->hasRole('livreur') || $this->hasRole('magasinier');
    }

    /**
     * Accès en lecture seule (dashboard, consultation)
     */
    public function peutVoirDashboard(): bool { return true; }
}
