<?php
namespace App\Http\Controllers;

use App\Models\User;
// On importe Role et Departement car on en a besoin pour les listes déroulantes du formulaire
use App\Models\Role;
use App\Models\Departement;
use Illuminate\Http\Request;

class UserController extends Controller
{
    
    public function index()
    {
        
        $users = User::with('role', 'departement.direction')->get();

        return view('users.index', compact('users'));
    }

    
    public function create()
    {
        // On récupère les rôles pour la liste déroulante

        $roles = Role::all();

        
        $departements = Departement::with('direction')->get();

        return view('users.create', compact(
            'roles',
            'departements'
        ));
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'matricule' => 'required|integer|unique:users,matricule',
            // unique:users,matricule : le matricule doit être unique dans la table users

            'nom'    => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'poste'  => 'required|string|max:255',

            'email' => 'required|email|unique:users,email',
            // email vérifie que s'est un format email valide
            

            'password' => 'required|string|min:8',


            'role_id'        => 'required|exists:roles,id',
            'departement_id' => 'required|exists:departements,id',

         
            // Ces champs sont des cases à cocher

            'est_responsable_departement' => 'boolean',
            'est_responsable_direction'   => 'boolean',

            
            'solde_conge'   => 'nullable|integer',
            'solde_absence' => 'nullable|integer',
        ]);

        User::create($request->only([
            'matricule', 'nom', 'prenom', 'poste',
            'email', 'password', 'signature',
            'est_responsable_departement',
            'est_responsable_direction',
            'solde_conge', 'solde_absence',
            'role_id', 'departement_id',
        ]));

        return redirect()
            ->route('users.index')
            ->with('success', 'Utilisateur créé avec succès');
    }

   
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $roles = Role::all();
        $departements = Departement::with('direction')->get();

        return view('users.edit', compact(
            'user',
            'roles',
            'departements'
        ));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            
            'matricule' => 'required|integer|unique:users,matricule,'.$id,
            'nom'       => 'required|string|max:255',
            'prenom'    => 'required|string|max:255',
            'poste'     => 'required|string|max:255',
            'email'     => 'required|email|unique:users,email,'.$id,

            // 'sometimes' valide si le champ est présent
            
            'password'       => 'sometimes|string|min:8',
            'role_id'        => 'required|exists:roles,id',
            'departement_id' => 'required|exists:departements,id',
            'est_responsable_departement' => 'boolean',
            'est_responsable_direction'   => 'boolean',
            'solde_conge'   => 'nullable|integer',
            'solde_absence' => 'nullable|integer',
        ]);

        $user = User::findOrFail($id);
        $user->update($request->only([
            'matricule', 'nom', 'prenom', 'poste',
            'email', 'password', 'signature',
            'est_responsable_departement',
            'est_responsable_direction',
            'solde_conge', 'solde_absence',
            'role_id', 'departement_id',
        ]));

        return redirect()
            ->route('users.index')
            ->with('success', 'Utilisateur modifié avec succès');
    }

   
    public function destroy($id)
    {
        User::findOrFail($id)->delete();

        return redirect()
            ->route('utilisateurs.index')
            ->with('success', 'Utilisateur supprimé');
    }
}