<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Arrivage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id','magasin_id','fournisseur_id','user_id','reference',
        'date_arrivage','pays_origine','taux_change','devise_origine','devise_locale',
        'frais_transport','frais_douane','frais_manutention','frais_commission','frais_divers',
        'total_frais','total_valeur_origine','total_valeur_fcfa','total_cout_reel',
        'statut','commentaire',
    ];

    protected $casts = [
        'date_arrivage' => 'date',
        'taux_change'   => 'decimal:4',
    ];

    public function tenant()      { return $this->belongsTo(Tenant::class); }
    public function magasin()     { return $this->belongsTo(Magasin::class); }
    public function fournisseur() { return $this->belongsTo(Fournisseur::class); }
    public function user()        { return $this->belongsTo(User::class); }
    public function produits()    { return $this->hasMany(ArrivageProduit::class); }

    /**
     * Total des frais de l'arrivage
     */
    public function totalFrais(): float
    {
        return (float)($this->frais_transport + $this->frais_douane
             + $this->frais_manutention + $this->frais_commission + $this->frais_divers);
    }

    /**
     * Total des ventes attendues basé sur les prix suggérés
     */
    public function revenuAttendu(): float
    {
        return (float)$this->produits->sum(fn($l) => $l->prix_vente_suggere * $l->quantite);
    }

    /**
     * Bénéfice prévisionnel = revenu attendu - coût total réel
     */
    public function beneficePrevisionnel(): float
    {
        return $this->revenuAttendu() - (float)$this->total_cout_reel;
    }

    /**
     * Recalcule et sauvegarde tous les totaux
     */
    public function recalculer(): void
    {
        $this->total_frais = $this->totalFrais();
        $totalOrigine      = $this->produits()->sum('total_origine');
        $this->total_valeur_origine = $totalOrigine;
        $this->total_valeur_fcfa    = $totalOrigine * $this->taux_change;
        $this->total_cout_reel      = $this->total_valeur_fcfa + $this->total_frais;
        $this->save();

        // Répartir les frais sur chaque ligne produit proportionnellement
        if ($this->total_valeur_origine > 0) {
            foreach ($this->produits as $ligne) {
                $proportion           = $ligne->total_origine / $this->total_valeur_origine;
                $ligne->valeur_fcfa   = $ligne->total_origine * $this->taux_change;
                $ligne->part_frais    = $this->total_frais * $proportion;
                $coutTotal            = $ligne->valeur_fcfa + $ligne->part_frais;
                $ligne->cout_total_reel   = $coutTotal;
                $ligne->cout_unitaire_reel = $ligne->quantite > 0 ? $coutTotal / $ligne->quantite : 0;
                $ligne->prix_vente_suggere = Produit::arrondir($ligne->cout_unitaire_reel);
                $ligne->save();
            }
        }
    }
}
