<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Dette extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id','client_id','vente_id',
        'montant_initial','montant_paye','montant_restant',
        'date_echeance','statut','notes',
    ];

    protected $casts = ['date_echeance' => 'date'];

    public function tenant()    { return $this->belongsTo(Tenant::class); }
    public function client()    { return $this->belongsTo(Client::class)->withDefault(['nom' => 'Anonyme']); }
    public function vente()     { return $this->belongsTo(Vente::class); }
    public function paiements() { return $this->hasMany(DettePaiement::class); }

    /**
     * Enregistre un paiement partiel et met à jour le statut automatiquement
     */
    public function enregistrerPaiement(float $montant, string $mode = 'especes', ?int $userId = null): DettePaiement
    {
        $paiement = $this->paiements()->create([
            'user_id'       => $userId,
            'montant'       => $montant,
            'mode_paiement' => $mode,
        ]);

        $this->montant_paye    += $montant;
        $this->montant_restant -= $montant;

        if ($this->montant_restant <= 0) {
            $this->montant_restant = 0;
            $this->statut = 'solde';
        } elseif ($this->montant_paye > 0) {
            $this->statut = 'partiel';
        }

        $this->save();

        return $paiement;
    }

    public function estEnRetard(): bool
    {
        return $this->date_echeance
            && \Carbon\Carbon::parse($this->date_echeance)->isPast()
            && $this->statut !== 'solde';
    }
}
