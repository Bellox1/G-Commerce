<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetteSociete extends Model
{
    protected $table = 'dettes_societe';

    protected $fillable = [
        'tenant_id', 'fournisseur_id', 'arrivage_id',
        'montant', 'montant_paye', 'description',
        'date_dette', 'statut',
    ];

    protected $casts = [
        'montant' => 'decimal:2',
        'montant_paye' => 'decimal:2',
        'date_dette' => 'date',
    ];

    public function tenant()       { return $this->belongsTo(Tenant::class); }
    public function fournisseur()  { return $this->belongsTo(Fournisseur::class); }
    public function arrivage()     { return $this->belongsTo(Arrivage::class); }
    public function paiements()    { return $this->hasMany(DetteSocietePaiement::class); }

    public function montantRestant(): float
    {
        return (float) $this->montant - (float) $this->montant_paye;
    }

    public function updateStatut(): void
    {
        $reste = $this->montantRestant();
        if ($reste <= 0) {
            $this->update(['statut' => 'solde', 'montant_paye' => $this->montant]);
        } elseif ($this->montant_paye > 0) {
            $this->update(['statut' => 'partiel']);
        }
    }
}
