<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminMiddleware
{
    //On vérifie que l'utilisateur connecté a le rôle d'admin Sinon redirige vers l'accueil avec un message d'erreur.
     
    public function handle(Request $request, Closure $next)
    {
        if (auth()->user()->role->libelle !== 'admin') {
            return redirect()
                ->route('accueil')
                ->with('error', 'Accès réservé aux administrateurs.');
        }

        return $next($request);
    }
}