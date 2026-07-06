<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id','magasin_id','user_id','client_id','reference',
        'date_vente','montant_total','montant_paye','montant_reste',
        'montant_remis','du',
        'statut_paiement','statut_livraison','livreur_id','date_livraison','note_livraison','mode_paiement','notes',
    ];

    protected $casts = [
        'date_vente' => 'datetime',
        'date_livraison' => 'datetime',
    ];

    public function tenant()   { return $this->belongsTo(Tenant::class); }
    public function magasin()  { return $this->belongsTo(Magasin::class); }
    public function user()     { return $this->belongsTo(User::class); }
    public function client()   { return $this->belongsTo(Client::class); }
    public function livreur()  { return $this->belongsTo(User::class, 'livreur_id'); }
    public function lignes()   { return $this->hasMany(VenteLigne::class); }
    public function dette()    { return $this->hasOne(Dette::class); }

    /**
     * Calcule le statut de paiement
     */
    public function calculerStatut(): string
    {
        if ($this->montant_reste <= 0) return 'paye';
        if ($this->montant_paye > 0)  return 'partiel';
        return 'impaye';
    }

    /**
     * Calcule la marge totale de la vente
     */
    public function marge(): float
    {
        return $this->lignes->sum(fn($l) => ($l->prix_vente - $l->cout_unitaire) * $l->quantite);
    }
}
