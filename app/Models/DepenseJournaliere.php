<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepenseJournaliere extends Model
{
    protected $table = 'depenses_journalieres';

    protected $fillable = [
        'tenant_id', 'user_id', 'montant', 'description', 'audio_path', 'date_depense',
    ];

    protected $casts = [
        'date_depense' => 'date',
    ];

    public function tenant() { return $this->belongsTo(Tenant::class); }
    public function user()   { return $this->belongsTo(User::class); }
}
