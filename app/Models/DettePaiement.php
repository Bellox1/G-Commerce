<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DettePaiement extends Model
{
    protected $fillable = [
        'dette_id','user_id','montant','mode_paiement','date_paiement','notes',
    ];

    protected $casts = ['date_paiement' => 'datetime'];

    public function dette() { return $this->belongsTo(Dette::class); }
    public function user()  { return $this->belongsTo(User::class); }
}
