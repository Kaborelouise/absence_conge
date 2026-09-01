<?php

namespace App\Notifications;

// On importe le modèle DemandeAbsence, car cette notification
// a besoin de connaître la demande concernée pour construire son contenu
use App\Models\DemandeAbsence;

// Queueable permet d'envoyer la notification en tâche de fond c'est à dire en file d'attente
// au lieu de bloquer la page pendant l'envoi du mail
use Illuminate\Bus\Queueable;

// ShouldQueue est une interface qui indique à Laravel que
// cette notification doit être mise en file d'attente asynchrone
use Illuminate\Contracts\Queue\ShouldQueue;

// Classe de base fournie par Laravel pour créer une notification
use Illuminate\Notifications\Notification;

// MailMessage permet de construire facilement le contenu d'un email

use Illuminate\Notifications\Messages\MailMessage;

class DemandeAbsenceATraiter extends Notification implements ShouldQueue
{
    // Ajoute les fonctionnalités nécessaires à la mise en file d'attente
    use Queueable;


    //   Le constructeur reçoit la demande d'absence concernée.
    //   Elle est stockée dans une propriété publique ($this->demande)
    //   accessible ensuite dans toMail().
    //   "public DemandeAbsence $demande" est une syntaxe raccourcie PHP :
    //  elle déclare ET assigne automatiquement la propriété en une seule ligne.
   
    public function __construct(public DemandeAbsence $demande)
    {
    }


    //  via() indique à Laravel PAR QUEL CANAL envoyer cette notification.
    //  Ici, uniquement 'mail' (un email).
    //   On pourrait ajouter 'database' plus tard pour aussi avoir
    //   une notification affichée dans l'application (cloche).

    //  $notifiable = la personne qui va recevoir la notification
    //  (Laravel la fournit automatiquement, on n'a pas à la définir nous-même)

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }


//  toArray() définit ce qui est stocké en base pour la cloche.
//  C'est différent du mail : ici on stocke juste les infos essentielles
//  pour afficher une ligne de notification dans l'application.

        public function toArray($notifiable): array
        {
            return [
                'demande_id'  => $this->demande->id,
                'num_demande' => $this->demande->num_demande,
                'agent'       => $this->demande->user->prenom . ' ' . $this->demande->user->nom,
                'message'     => "Nouvelle demande d'absence à traiter",
                'url'         => route('demande_absences.show', $this->demande->id),
            ];
        }


    //  toMail() construit le contenu réel de l'email envoyé.
    //  Elle est appelée automatiquement par Laravel car 'mail'
    //  est présent dans via().

        public function toMail($notifiable): MailMessage
    {
        $agent = $this->demande->user;
        $url = route('demande_absences.show', $this->demande->id);

        $dateDebut = \Carbon\Carbon::parse($this->demande->date_debut)->format('d/m/Y');
        $dateFin   = \Carbon\Carbon::parse($this->demande->date_fin)->format('d/m/Y');

        return (new MailMessage)
            ->subject('Demande d\'absence à traiter')
            ->greeting('Bonjour ' . $notifiable->prenom . ',')
            ->line("{$agent->prenom} {$agent->nom} a soumis une demande d'absence qui attend votre avis.")
            ->line("Période : du {$dateDebut} au {$dateFin}")
            ->action('Consulter la demande', $url)
            ->line('Merci de la traiter dans les meilleurs délais.')
            ->salutation("Cordialement,\nPlateforme de gestion des autorisations d'absences et des congés");
    }
}