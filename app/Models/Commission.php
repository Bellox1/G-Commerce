<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    protected $fillable = ['partenaire_id', 'tenant_id', 'montant', 'statut'];

    public function partenaire()
    {
        return $this->belongsTo(User::class, 'partenaire_id');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
