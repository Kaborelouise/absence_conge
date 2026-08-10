<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\Departement;
use App\Helpers\LogActivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Notifications\InvitationCompteNotification;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('Administrateur');
    }

    public function index()
    {
        $users = User::with('role', 'departement.direction')->get();
        return view('utilisateurs.index', compact('users'));
    }

    public function create()
    {
        $roles        = Role::all();
        $departements = Departement::with('direction')->get();
        return view('utilisateurs.create', compact('roles', 'departements'));
    }

 public function store(Request $request)
    {
        
        $validated = $request->validate([
            'matricule'                     => 'required|numeric|unique:users,matricule',
            'nom'                           => 'required|string|max:255',
            'prenom'                        => 'required|string|max:255',
            'poste'                         => 'required|string|max:255',
            'email'                         => 'required|email|unique:users,email',
            'role_id'                       => 'required|exists:roles,id',
            'departement_id'                => 'required|exists:departements,id',
            'date_prise_service'            => 'required|date|before_or_equal:today',
            'certificat_prise_service'      => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'solde_conge'                   => 'nullable|numeric',
            'solde_absence'                 => 'nullable|numeric',
            'est_responsable_departement'   => 'nullable|boolean',
            'est_responsable_direction'     => 'nullable|boolean',
        ]);

        if ($request->password) {
            $validated['password'] = Hash::make($request->password);
        }
        else {
            // Mot de passe temporaire et inutilisable  l'utilisateur définira
            $validated['password'] = Hash::make(Str::random(32));
        }
        

        if ($request->hasFile('certificat_prise_service')) {
            $validated['certificat_prise_service'] = $request->file('certificat_prise_service')
                ->store('certificats', 'public');
        }

        $user = User::create($validated);

        // Génère un token de réinitialisation 
        // et envoie l'email d'invitation contenant le lien
        $token = Password::createToken($user);
        $user->notify(new InvitationCompteNotification($token));

        return redirect()->route('utilisateurs.index')
            ->with('success', "Utilisateur créé. Un email d'invitation a été envoyé à {$user->email} pour qu'il définisse son mot de passe.");
    }

    public function show($id)
    {
        $user = User::with(
            'role', 'departement.direction',
            'demandeAbsences', 'demandeJouissances'
        )->findOrFail($id);

        return view('utilisateurs.show', compact('user'));
    }

    public function edit($id)
    {
        $user         = User::findOrFail($id);
        $roles        = Role::all();
        $departements = Departement::with('direction')->get();
        return view('utilisateurs.edit', compact('user', 'roles', 'departements'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'matricule'                   => 'required|integer|unique:users,matricule,'.$id,
            'nom'                         => 'required|string|max:255',
            'prenom'                      => 'required|string|max:255',
            'poste'                       => 'required|string|max:255',
            'email'                       => 'required|email|unique:users,email,'.$id,
            
            // 'password'                    => 'nullable|string|min:8|confirmed',
            'role_id'                     => 'required|exists:roles,id',
            'departement_id'              => 'required|exists:departements,id',
            'est_responsable_departement' => 'nullable|boolean',
            'est_responsable_direction'   => 'nullable|boolean',
            'solde_conge'                 => 'nullable|integer',
            'solde_absence'               => 'nullable|integer',
            'date_prise_service'          => 'required|date|before_or_equal:today',
            'certificat_prise_service'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            // 'password.min'                       => 'Le mot de passe doit contenir au moins 8 caractères.',
            // 'password.confirmed'                 => 'Les mots de passe ne correspondent pas.',
            'date_prise_service.required'        => 'La date de prise de service est obligatoire.',
            'date_prise_service.before_or_equal' => 'La date de prise de service ne peut pas être dans le futur.',
        ]);

        $user = User::findOrFail($id);

        $data = [
            'matricule'                   => $request->matricule,
            'nom'                         => strtoupper($request->nom),
            'prenom'                      => $request->prenom,
            'poste'                       => $request->poste,
            'email'                       => $request->email,
            'est_responsable_departement' => $request->boolean('est_responsable_departement'),
            'est_responsable_direction'   => $request->boolean('est_responsable_direction'),
            'solde_conge'                 => $request->solde_conge,
            'solde_absence'               => $request->solde_absence,
            'role_id'                     => $request->role_id,
            'departement_id'              => $request->departement_id,
            'date_prise_service'          => $request->date_prise_service,
        ];

        // Mot de passe seulement si renseigné
        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        // Nouveau certificat
        if ($request->hasFile('certificat_prise_service')) {
            if ($user->certificat_prise_service) {
                Storage::disk('public')->delete($user->certificat_prise_service);
            }
            $data['certificat_prise_service'] = Storage::disk('public')->putFile(
                'certificats_prise_service',
                $request->file('certificat_prise_service')
            );
        }

        $user->update($data);

        LogActivity::log(
            'update', 'User', $user->id,
            "Modification utilisateur {$user->nom} {$user->prenom}"
        );

        return redirect()
            ->route('utilisateurs.index')
            ->with('success', 'Utilisateur modifié avec succès.');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->certificat_prise_service) {
            Storage::disk('public')->delete($user->certificat_prise_service);
        }

        $nomComplet = "{$user->nom} {$user->prenom}";
        $user->delete();

        LogActivity::log('delete', 'User', $id, "Suppression utilisateur {$nomComplet}");

        return redirect()
            ->route('utilisateurs.index')
            ->with('success', 'Utilisateur supprimé.');
    }

    public function renvoyerInvitation(User $utilisateur)
    {
        $token = Password::createToken($utilisateur);
        $utilisateur->notify(new InvitationCompteNotification($token));

        return back()->with('success', "Email d'invitation renvoyé à {$utilisateur->email}.");
    }
}