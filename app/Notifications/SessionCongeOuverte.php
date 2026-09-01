<?php

namespace App\Notifications;

use App\Models\SessionAdministrative;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SessionCongeOuverte extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public SessionAdministrative $session)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ouverture de la session de congé ' . $this->session->annee)
            ->greeting('Bonjour ' . $notifiable->prenom . ',')
            ->line("La session administrative {$this->session->annee} est maintenant ouverte pour les demandes de congé.")
            ->action('Faire ma demande de congé', route('demande_conges.create'))
            ->line('Merci de soumettre votre demande dans les meilleurs délais.')
            ->salutation("Cordialement,\nPlateforme de gestion des autorisations d'absences et des congés");
    }

    public function toArray($notifiable): array
    {
        return [
            'session_id' => $this->session->id,
            'message'    => "La session de congé {$this->session->annee} est ouverte.",
            'url'        => route('demande_conges.create'),
        ];
    }
}