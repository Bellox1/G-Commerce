<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    protected $fillable = ['nom', 'code', 'prix', 'prix_tranche_3x', 'commission', 'primes'];

    protected $casts = [
        'primes' => 'array',
    ];

    /**
     * Retourne le montant de la prime pour un seuil (palier) donné.
     */
    public function primePour(int $seuil): float
    {
        return (float) ($this->primes[$seuil] ?? 0);
    }

    /**
     * Retourne la durée en mois de l'offre (null = à vie)
     */
    public function dureeEnMois(): ?int
    {
        return match ($this->code) {
            'locale'  => null,
            'cloud_1' => 1,
            'cloud_3' => 3,
            'cloud_6' => 6,
            'cloud_12'=> 12,
            default   => null,
        };
    }
}
