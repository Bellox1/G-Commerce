<?php

namespace App\Notifications;

use App\Models\Produit;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class StockBasNotification extends Notification
{
    use Queueable;

    public Produit $produit;
    public int $stock;

    public function __construct(Produit $produit, int $stock)
    {
        $this->produit = $produit;
        $this->stock   = $stock;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $seuil = (int) $this->produit->seuil_alerte;

        return [
            'categorie'    => 'stock',
            'produit_id'   => $this->produit->id,
            'titre'        => 'Stock bas : ' . $this->produit->nom,
            'message'      => "Le stock de **{$this->produit->nom}** est de {$this->stock} carton(s) (seuil d'alerte : {$seuil}). Pensez à commander un arrivage.",
            'seuil_alerte' => $seuil,
            'stock'        => $this->stock,
            'action_url'   => '/produits/' . $this->produit->id,
            'action_label' => 'Voir le produit',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('[PILOTIX] ' . $this->toArray($notifiable)['titre'])
            ->line($this->toArray($notifiable)['message'])
            ->action('Voir le produit', url('/produits/' . $this->produit->id));
    }
}
