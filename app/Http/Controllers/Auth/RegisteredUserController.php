<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'nom' => ['required', 'string', 'min:2', 'max:255'],
             'prenom' => ['required', 'string', 'min:2','max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'matricule' => ['required', 'integer', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::min(6)],
        ]);

        $user = User::create([
        'nom'           => $request->nom,
        'prenom'        => $request->prenom,
        'email'         => $request->email,
        'matricule'     => $request->matricule,
        'password'      => Hash::make($request->password),
        'role_id'       => 1,
        'departement_id'=> 1,
        // role_id et departement_id par défaut
        // l'admin les changera après
    ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashbord', absolute: false));
    }
}
