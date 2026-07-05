<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fournisseur extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id','nom','pays','ville','telephone','email','devise','notes','actif',
    ];

    protected $casts = ['actif' => 'boolean'];

    public function tenant()    { return $this->belongsTo(Tenant::class); }
    public function arrivages() { return $this->hasMany(Arrivage::class); }
}
