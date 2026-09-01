<?php

namespace App\Notifications;

use App\Models\DemandeJouissance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DemandeJouissanceRejetee extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DemandeJouissance $demande,
        public ?string $motif = null
    ) {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $dateDebut = \Carbon\Carbon::parse($this->demande->date_debut)->format('d/m/Y');
        $dateFin   = \Carbon\Carbon::parse($this->demande->date_fin)->format('d/m/Y');

        $mail = (new MailMessage)
            ->subject('Votre demande de jouissance a été rejetée')
            ->greeting('Bonjour ' . $notifiable->prenom . ',')
            ->line("Votre demande de jouissance du {$dateDebut} au {$dateFin} a été rejetée.");

        if ($this->motif) {
            $mail->line("Motif : {$this->motif}");
        }

        return $mail
            ->action('Voir ma demande', route('demande_jouissances.show', $this->demande->id))
            ->salutation("Cordialement,\nPlateforme de gestion des autorisations d'absences et des congés");
    }

    public function toArray($notifiable): array
    {
        return [
            'demande_id' => $this->demande->id,
            'message'    => 'Votre demande de jouissance a été rejetée.' . ($this->motif ? " Motif : {$this->motif}" : ''),
            'url'        => route('demande_jouissances.show', $this->demande->id),
        ];
    }
}