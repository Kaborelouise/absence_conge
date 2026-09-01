<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{

    //  Marque une notification comme lu puis redirige
    //  vers la page de la demande concernée stockée dans 'url'

    public function lire(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        // markAsRead() est une méthode fournie automatiquement par Laravel
        // sur chaque notification, dès qu'on utilise le trait Notifiable
        $notification->markAsRead();

        // On récupère l'URL stockée dans toArray() de la notification
        $url = $notification->data['url'] ?? route('dashboard');

        return redirect($url);
    }
}