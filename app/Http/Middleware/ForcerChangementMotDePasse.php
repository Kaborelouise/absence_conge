<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcerChangementMotDePasse
{
   
    private array $routesAutorisees = [
        'mot_de_passe.changer_obligatoire',
        'mot_de_passe.changer_obligatoire.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->mot_de_passe_temporaire) {

            // Si le mot de passe temporaire a expiré
            if ($user->mot_de_passe_expire_at && $user->mot_de_passe_expire_at->isPast()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => "Votre mot de passe temporaire a expiré. Contactez votre administrateur pour en recevoir un nouveau."]);
            }

            // Sinon, on bloque tout sauf les routes autorisées
            if (! $request->routeIs($this->routesAutorisees)) {
                return redirect()->route('mot_de_passe.changer_obligatoire');
            }
        }

        return $next($request);
    }
}