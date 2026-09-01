<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class MotDePasseTemporaireNotification extends Notification implements ShouldQueue
{
    use Queueable;


    //  On reçoit directement le mot de passe en clair (généré juste avant,
    //  jamais stocké en clair nulle part ailleurs que dans ce mail).

    public function __construct(public string $motDePasseTemporaire)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Bienvenue — Votre accès à la plateforme ANPTIC')
            ->greeting('Bonjour ' . $notifiable->prenom . ' ' . strtoupper($notifiable->nom) . ',')
            ->line('Un compte a été créé pour vous sur la plateforme de gestion des congés et absences ANPTIC.')
            ->line('Voici vos informations de connexion :')
            ->line('**Identifiant / Email :** ' . $notifiable->email)
            ->line('**Votre mot de passe temporaire :** ' . $this->motDePasseTemporaire)
            ->line('Ce mot de passe est valable **7 jours**. Connectez-vous avec ces identifiants, vous serez ensuite invité à définir votre mot de passe personnel.')
            ->action('Se connecter à la plateforme', route('login'))
            ->line('Passé ce délai, contactez votre administrateur pour recevoir un nouveau mot de passe temporaire.')
            ->salutation("Cordialement,\nPlateforme de gestion des autorisations d'absences et des congés");
    }
}