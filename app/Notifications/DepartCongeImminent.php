<?php

namespace App\Notifications;

use App\Models\DemandeJouissance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DepartCongeImminent extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DemandeJouissance $demande)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $dateDepart = \Carbon\Carbon::parse($this->demande->date_debut)->format('d/m/Y');

        return (new MailMessage)
            ->subject('Rappel : départ en congé dans 7 jours')
            ->greeting('Bonjour ' . $notifiable->prenom . ',')
            ->line("Votre départ en congé est prévu le {$dateDepart}, dans 7 jours.")
            ->line('Merci de bien préparer la passation avant votre départ.')
            ->action('Voir ma demande', route('demande_jouissances.show', $this->demande->id))
            ->salutation("Cordialement,\nPlateforme de gestion des autorisations d'absences et des congés");
    }

    public function toArray($notifiable): array
    {
        return [
            'demande_id' => $this->demande->id,
            'message'    => 'Votre départ en congé approche (dans 7 jours).',
            'url'        => route('demande_jouissances.show', $this->demande->id),
        ];
    }
}