<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetteSocietePaiement extends Model
{
    protected $fillable = [
        'dette_societe_id', 'user_id',
        'montant', 'date_paiement', 'mode_paiement', 'notes',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date_paiement' => 'date',
    ];

    public function detteSociete() { return $this->belongsTo(DetteSociete::class); }
    public function user()         { return $this->belongsTo(User::class); }
}
