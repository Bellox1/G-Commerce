<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DemandePrestataire extends Model
{
    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'entreprise',
        'motivation',
        'questionnaire',
        'statut',
        'user_id',
        'password'
    ];

    protected $casts = [
        'questionnaire' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
