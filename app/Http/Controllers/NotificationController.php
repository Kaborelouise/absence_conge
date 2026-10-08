<?php

namespace App\Http\Controllers;

class NotificationController extends Controller
{
    // Marque une notification comme lue puis redirige
    // vers la page concernée, en gardant uniquement le chemin de l'URL
    public function lire(string $id)
    {
        $notification = auth()->user()->notifications()->findOrFail($id);

        $notification->markAsRead();

        $url = $notification->data['url'] ?? null;

        if ($url) {
            // On ignore l'hôte et le port enregistrés 
            // pour rester sur le serveur actuel
            $chemin = parse_url($url, PHP_URL_PATH) ?: '/';
            $requete = parse_url($url, PHP_URL_QUERY);

            if ($requete) {
                $chemin .= '?' . $requete;
            }

            return redirect($chemin);
        }

        return redirect()->route('dashboard');
    }
}