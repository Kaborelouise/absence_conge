<?php

namespace App\Notifications;

use App\Models\DemandeJouissance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DemandeJouissanceATraiter extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DemandeJouissance $demande)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'demande_id'  => $this->demande->id,
            'num_demande' => $this->demande->num_demande,
            'agent'       => $this->demande->user->prenom . ' ' . $this->demande->user->nom,
            'message'     => "Nouvelle demande de jouissance à traiter",
            'url'         => route('demande_jouissances.show', $this->demande->id),
        ];
    }

    public function toMail($notifiable): MailMessage
    {
        $agent = $this->demande->user;
        $url   = route('demande_jouissances.show', $this->demande->id);

        return (new MailMessage)
            ->subject('Demande de jouissance à traiter')
            ->greeting('Bonjour ' . $notifiable->prenom . ',')
            ->line("{$agent->prenom} {$agent->nom} a soumis une demande de jouissance qui attend votre avis.")
            ->action('Consulter la demande', $url)
            ->line('Merci de la traiter dans les meilleurs délais.')
            ->salutation("Cordialement,\nPlateforme de gestion des autorisations d'absences et des congés");
    }
}