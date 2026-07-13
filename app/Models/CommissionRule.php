<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionRule extends Model
{
    protected $fillable = ['nom', 'code', 'prix', 'commission'];

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
