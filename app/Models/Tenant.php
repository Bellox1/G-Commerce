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
        'partenaire_id', 'offre_code', 'offre_expires_at', 'alertes_envoyees',
    ];

    protected $casts = [
        'actif' => 'boolean',
        'offre_expires_at' => 'datetime',
        'alertes_envoyees' => 'array',
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

    public const PLAN_ESSENTIEL   = 'essentiel';
    public const PLAN_PRO         = 'professionnel';
    public const PLAN_ENTREPRISE  = 'entreprise';
    public const PLAN_LOCALE       = 'locale';

    /**
     * Niveau de l'offre : 1 = Essentiel, 2 = Professionnel, 3 = Entreprise / Locale (à vie)
     */
    public function planLevel(): int
    {
        return match ($this->offre_code) {
            self::PLAN_PRO          => 2,
            self::PLAN_ENTREPRISE,
            self::PLAN_LOCALE       => 3,
            default                 => 1, // essentiel et inconnu
        };
    }

    /**
     * Capacités par offre (cf. page tarifs).
     * Essentiel : produits/stocks, ventes, dépenses, clients, inventaires, hors-connexion.
     * Professionnel+ : importations/arrivages, multi-magasins, stats avancées, multi-utilisateurs, coûts/marges.
     */
    public function hasCapability(string $capability): bool
    {
        $level = $this->planLevel();

        return match ($capability) {
            'import', 'multi_magasin', 'advanced_stats', 'multi_user', 'cost_margin' => $level >= 2,
            default                                                                  => true,
        };
    }

    /**
     * Limite de dépôts/magasins (null = illimité).
     */
    public function maxMagasins(): ?int
    {
        return $this->planLevel() >= 2 ? null : 1;
    }

    /**
     * Limite d'utilisateurs totaux (null = illimité).
     */
    public function maxUsers(): ?int
    {
        return $this->planLevel() >= 2 ? null : 1;
    }

    public function magasinLimitReached(): bool
    {
        $max = $this->maxMagasins();
        return $max !== null && $this->magasins()->count() >= $max;
    }

    public function userLimitReached(): bool
    {
        $max = $this->maxUsers();
        return $max !== null && $this->users()->count() >= $max;
    }
}
