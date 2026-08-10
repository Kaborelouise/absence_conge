<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class PasswordSetupController extends Controller
{
   
    //  affiche le formulaire pour la definition du mot de pase
    public function create(string $token, Request $request)
    {
        return view('auth.password-setup', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    //  traite la soumission du mot de passe
    public function store(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')
                ->with('status', 'Votre mot de passe a été défini avec succès. Vous pouvez maintenant vous connecter.');
        }

        return back()->withErrors(['email' => __($status)]);
    }
}