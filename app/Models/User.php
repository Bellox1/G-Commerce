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
    public function isControleur(): bool { return $this->role === 'controleur'; }
    public function isMagasinier(): bool { return $this->role === 'magasinier'; }
    public function isPrestataire(): bool { return $this->role === 'prestataire'; }
    public function isSuperviseur(): bool { return $this->role === 'superviseur'; }

    /**
     * Accès complet type "admin". Le rôle admin est réservé au propriétaire de
     * la société ; les autres bénéficient des mêmes fonctionnalités via
     * le rôle "superviseur" (principal ou secondaire).
     */
    public function aAccesAdmin(): bool
    {
        return $this->isSuperAdmin()
            || $this->isAdmin()
            || $this->isSuperviseur()
            || $this->hasRole('superviseur');
    }

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
        return $this->aAccesAdmin();
    }

    /**
     * Vérifie si l'utilisateur peut gérer les arrivages
     */
    public function peutGererArrivages(): bool
    {
        return $this->aAccesAdmin() || $this->hasRole('magasinier');
    }

    // ─── Permissions par module ─────────────────────────
 
    public function peutGererProduits(): bool
    {
        return $this->aAccesAdmin() || $this->hasRole('magasinier') || $this->hasRole('vendeur');
    }

    public function peutModifierCatalogues(): bool
    {
        return $this->aAccesAdmin() || $this->hasRole('magasinier');
    }
 
    public function peutGererMagasins(): bool
    {
        return $this->aAccesAdmin();
    }
 
    public function peutGererVentes(): bool
    {
        return $this->aAccesAdmin() || $this->hasRole('vendeur');
    }
 
    public function peutGererClients(): bool
    {
        return $this->aAccesAdmin() || $this->hasRole('vendeur') || $this->hasRole('magasinier');
    }
 
    public function peutGererDettes(): bool
    {
        return $this->aAccesAdmin() || $this->hasRole('vendeur');
    }
 
    public function peutGererStock(): bool
    {
        return $this->aAccesAdmin() || $this->hasRole('magasinier') || $this->hasRole('vendeur');
    }
 
    public function peutGererTransferts(): bool
    {
        return $this->aAccesAdmin() || $this->hasRole('magasinier') || $this->hasRole('vendeur') || $this->hasRole('livreur');
    }

    public function peutGererLivraisons(): bool
    {
        return $this->aAccesAdmin() || $this->hasRole('controleur') || $this->hasRole('magasinier') || $this->hasRole('livreur');
    }

    /**
     * Accès en lecture seule (dashboard, consultation)
     */
    public function peutVoirDashboard(): bool { return true; }
}
