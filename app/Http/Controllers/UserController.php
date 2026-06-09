<?php
namespace App\Http\Controllers;

use App\Models\User;
// On importe Role et Departement car on en a besoin pour les listes déroulantes du formulaire
use App\Models\Role;
use App\Models\Departement;
use Illuminate\Http\Request;

class UserController extends Controller
{
    // on récupère tous les utilisateurs avec leurs rôles, les départements et directions associé
    
    public function index()
    {
        // on rcupere tous les utilisateurs avec leurs rôles, départements et directions associés
        // et leur departement et direction associé 
        // with() évite les problème de N+1 (trop de requêtes à la base de données)
        
        $utilisateurs = User::with('role', 'departement.direction')->get();
        return view('utilisateurs.index', compact('utilisateurs'));
        // compact('utilisateurs') : permet de passer la variable $utilisateurs à la vue
    }

    
    public function create()
    {
        // on récupère  tous les roles et les departements

        $roles = Role::all();

        
        $departements = Departement::with('direction')->get();

        return view('utilisateurs.create', compact(
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
            ->route('utilisateurs.index')
            ->with('success', 'Utilisateur créé avec succès');
    }

   
    public function edit($id)
    {
        $utilisateur= User::findOrFail($id);
        $roles = Role::all();
        $departements = Departement::with('direction')->get();

        return view('utilisateurs.edit', compact(
            'utilisateur',
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

            // 'sometimes' valide si le champ est présent dans la requête, sinon il l'ignore
            
            'password'       => 'sometimes|string|min:8',
            'role_id'        => 'required|exists:roles,id',
            'departement_id' => 'required|exists:departements,id',
            'est_responsable_departement' => 'boolean',
            'est_responsable_direction'   => 'boolean',
            'solde_conge'   => 'nullable|integer',
            'solde_absence' => 'nullable|integer',
        ]);

        $utilisateur = User::findOrFail($id);
        $utilisateur->update($request->only([
            'matricule', 'nom', 'prenom', 'poste',
            'email', 'password', 'signature',
            'est_responsable_departement',
            'est_responsable_direction',
            'solde_conge', 'solde_absence',
            'role_id', 'departement_id',
        ]));

        return redirect()
            ->route('utilisateurs.index')
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