<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class MotDePasseObligatoireController extends Controller
{

    //  Affiche le formulaire de changement dU mot de passe obligatoire.
  
    public function create(): View|RedirectResponse
    {
        $user = Auth::user();

        // Si l'utilisateur n'a pas (ou plus) de mot de passe temporaire actif,
        // on ne le laisse pas accéder à cette page 
        if (! $user->mot_de_passe_temporaire) {
            return redirect()->route('dashboard');
        }

        return view('auth.changer_mot_de_passe_obligatoire');
    }


    //  Traite le changement de mot de passe obligatoire.

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ], [
            'password.required'  => 'Le nouveau mot de passe est obligatoire.',
            'password.confirmed' => 'La confirmation ne correspond pas au mot de passe saisi.',
            'password.min'       => 'Le mot de passe doit contenir au moins 8 caractères.',
        ]);

        $user = Auth::user();

        $user->update([
            'password'                => Hash::make($request->password),
            'mot_de_passe_temporaire' => false,
            'mot_de_passe_expire_at'  => null,
            'last_login_at'           => now(),
        ]);

        \App\Helpers\LogActivity::log(
            'connexion',
            'User',
            $user->id,
            'Connexion de ' . $user->prenom . ' ' . $user->nom . ' (après changement du mot de passe temporaire)'
        );

        return redirect()->route('dashboard')
            ->with('success', 'Votre mot de passe a été mis à jour avec succès.');
    }
}