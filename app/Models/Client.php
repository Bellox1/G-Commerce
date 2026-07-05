<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id','nom','telephone','adresse','ville','limite_credit','actif','notes',
    ];

    protected $casts = ['actif' => 'boolean'];

    public function tenant()  { return $this->belongsTo(Tenant::class); }
    public function ventes()  { return $this->hasMany(Vente::class); }
    public function dettes()  { return $this->hasMany(Dette::class); }

    public function nomComplet(): string
    {
        return $this->nom;
    }

    public function totalDettesEnCours(): float
    {
        return (float) $this->dettes()
            ->whereIn('statut', ['en_cours', 'partiel', 'en_retard'])
            ->sum('montant_restant');
    }
}
