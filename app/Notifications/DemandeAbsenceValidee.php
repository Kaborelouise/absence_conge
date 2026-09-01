<?php

namespace App\Notifications;

use App\Models\DemandeAbsence;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DemandeAbsenceValidee extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public DemandeAbsence $demande)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $dateDebut = \Carbon\Carbon::parse($this->demande->date_debut)->format('d/m/Y');
        $dateFin   = \Carbon\Carbon::parse($this->demande->date_fin)->format('d/m/Y');

        return (new MailMessage)
            ->subject('Votre demande d\'absence a été validée')
            ->greeting('Bonjour ' . $notifiable->prenom . ',')
            ->line("Votre demande d'absence du {$dateDebut} au {$dateFin} a été validée.")
            ->action('Voir ma demande', route('demande_absences.show', $this->demande->id))
            ->salutation("Cordialement,\nPlateforme de gestion des autorisations d'absences et des congés");
    }

    public function toArray($notifiable): array
    {
        return [
            'demande_id' => $this->demande->id,
            'message'    => 'Votre demande d\'absence a été validée.',
            'url'        => route('demande_absences.show', $this->demande->id),
        ];
    }
}