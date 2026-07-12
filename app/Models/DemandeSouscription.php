<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandeSouscription extends Model
{
    protected $fillable = [
        'nom_entreprise',
        'nom_contact',
        'email_contact',
        'telephone_contact',
        'type_offre',
        'duree',
        'options',
        'montant_total',
        'statut',
        'prestataire_id',
        'tenant_id'
    ];

    protected $casts = [
        'options' => 'array',
    ];

    public function prestataire()
    {
        return $this->belongsTo(User::class, 'prestataire_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
