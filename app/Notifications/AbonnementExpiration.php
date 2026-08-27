<?php

namespace App\Notifications;

use App\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class AbonnementExpiration extends Notification
{
    use Queueable;

    public Tenant $tenant;
    public string $alertType;

    /**
     * @param Tenant $tenant
     * @param string $alertType  j7 | j3 | j1 | expire
     */
    public function __construct(Tenant $tenant, string $alertType)
    {
        $this->tenant = $tenant;
        $this->alertType = $alertType;
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        $labels = [
            'j7'     => 'Expire dans 7 jours',
            'j3'     => 'Expire dans 3 jours',
            'j1'     => 'Expire dans 1 jour',
            'expire' => 'Abonnement expiré',
        ];

        $messages = [
            'j7'     => "Votre abonnement **{$this->offreNom()}** expire dans 7 jours (le {$this->dateExpiration()}). Pensez à le renouveler pour ne pas interrompre le service.",
            'j3'     => "Votre abonnement **{$this->offreNom()}** expire dans 3 jours (le {$this->dateExpiration()}). Renouvelez dès maintenant via nos contacts.",
            'j1'     => "Votre abonnement **{$this->offreNom()}** expire demain ({$this->dateExpiration()}). Dernière chance pour renouveler sans coupure.",
            'expire' => "Votre abonnement **{$this->offreNom()}** a expiré le {$this->dateExpiration()}. Certaines fonctionnalités sont suspendues — contactez-nous pour le renouveler.",
        ];

        return [
            'categorie'     => 'abonnement',
            'alert_type'    => $this->alertType,
            'titre'         => $labels[$this->alertType] ?? 'Abonnement',
            'message'       => $messages[$this->alertType] ?? '',
            'offre_code'    => $this->tenant->offre_code,
            'offre_nom'     => $this->offreNom(),
            'expires_at'    => $this->tenant->offre_expires_at?->toDateString(),
            'tenant_id'     => $this->tenant->id,
            'action_url'    => '/offre',
            'action_label'  => 'Voir mon offre',
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage())
            ->subject('[PILOTIX] ' . ($this->toArray($notifiable)['titre']))
            ->line($this->toArray($notifiable)['message'])
            ->action('Voir mon offre', url('/offre'));
    }

    protected function offreNom(): string
    {
        return match ($this->tenant->offre_code) {
            'essentiel'     => 'Essentiel',
            'professionnel' => 'Professionnel',
            'entreprise'    => 'Entreprise',
            'locale'        => 'Locale',
            default         => (string) $this->tenant->offre_code,
        };
    }

    protected function dateExpiration(): string
    {
        return $this->tenant->offre_expires_at?->format('d/m/Y') ?? '—';
    }
}
