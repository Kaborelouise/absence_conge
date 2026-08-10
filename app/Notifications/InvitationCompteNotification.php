<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvitationCompteNotification extends Notification implements ShouldQueue
{
    use Queueable;
    // creer une nouvelle notification pour inviter un utilisateur à créer son compte et définir son mot de passe

    /**
     * @param string $token Le token de réinitialisation généré par Password::createToken()
     */
    //constructeur pour initialiser le token de réinitialisation
    public function __construct(public string $token)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    //toMail methode spécial laravel, notififiable pour l'utilisateur qui va recevoir le mail
    public function toMail($notifiable): MailMessage
    {
        $url = url(route('password.setup', [
            'token' => $this->token,
            'email' => $notifiable->email,
        ], false));

        return (new MailMessage)
            ->subject('Bienvenue — Activez votre compte')
            ->greeting('Bonjour ' . $notifiable->prenom . ',')
            ->line("Un compte a été créé pour vous sur l'application de gestion des congés et des autorisations d'absence.")
            ->line("Cliquez sur le bouton ci-dessous pour définir votre mot de passe et activer votre compte.")
            ->action('Définir mon mot de passe', $url)
            ->line('Ce lien expirera dans 60 minutes.')
            ->line("Si vous n'êtes pas à l'origine de cette demande, ignorez simplement cet email.");
    }
}