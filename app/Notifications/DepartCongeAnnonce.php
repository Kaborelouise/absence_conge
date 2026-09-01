<?php

namespace App\Notifications;

use App\Models\DemandeJouissance;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class DepartCongeAnnonce extends Notification implements ShouldQueue
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
        $agent    = $this->demande->user;
        $dateFin  = \Carbon\Carbon::parse($this->demande->date_fin)->translatedFormat('d F Y');

        return (new MailMessage)
            ->subject("Départ en congé de {$agent->prenom} {$agent->nom}")
            ->greeting('Bonjour ' . $notifiable->prenom . ',')
            ->line("{$agent->prenom} {$agent->nom} est en congé à partir d'aujourd'hui, de retour prévu le {$dateFin}.");
    }

    public function toArray($notifiable): array
    {
        $agent = $this->demande->user;

        return [
            'demande_id' => $this->demande->id,
            'message'    => "{$agent->prenom} {$agent->nom} est parti(e) en congé aujourd'hui.",
        ];
    }
}