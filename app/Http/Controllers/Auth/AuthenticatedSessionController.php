<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;


class AuthenticatedSessionController extends Controller
{
   
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = auth()->user();

        // Vérifie si l'utilisateur a un mot de passe temporaire
        if ($user->mot_de_passe_temporaire) {

            // Si le mot de passe temporaire a expiré, on refuse et déconnecte
            if ($user->mot_de_passe_expire_at && $user->mot_de_passe_expire_at->isPast()) {
                Auth::guard('web')->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => "Votre mot de passe temporaire a expiré. Contactez votre administrateur pour en recevoir un nouveau."]);
            }

            // Mot de passe temporaire encore valide : on force le changement
            return redirect()->route('mot_de_passe.changer_obligatoire');
        }

        // enregistre la date de dernière connexion
        $user->update(['last_login_at' => now()]);

        // log de connexion
        \App\Helpers\LogActivity::log(
            'connexion',
            'User',
            auth()->id(),
            'Connexion de ' . $user->prenom . ' ' . $user->nom
        );

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}

