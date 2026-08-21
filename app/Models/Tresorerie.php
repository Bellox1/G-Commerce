<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tresorerie extends Model
{
    protected $table = 'tresoreries';

    protected $fillable = [
        'tenant_id', 'user_id', 'date', 'sens',
        'montant', 'libelle', 'mode_paiement', 'note',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'date' => 'date',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
