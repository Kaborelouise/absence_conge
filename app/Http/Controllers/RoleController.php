<?php

namespace App\Http\Controllers;

use App\http\controllers;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::all();
        //passer $roles a la vue
        return view('roles.index', compact('roles'));
    }

    //Afficher le formulaire de création 
    public function create()
    {
        return view ('roles.create');
    }

    //enregistrer un nouveau role
    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:255|unique:roles,libelle'

        ]);
        Role::create($request->all());

        //redirect() : redirige vers la liste des role apres création
        //with('success') envoie un message de succès
        return redirect()->route('roles.index')->with('success', 'Role a été creer avec succès');    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        return response()->json(Role::with('users')->findOrFail($id));
    }

   // Affiche le formulaire de modification
    public function edit($id)
    {
        return view('roles.edit', compact('role'));
        
    }

   //Mettre a jour un role
    public function update(Request $request, $id)
    {
        $request->validate([
            'libelle' => 'required|string|max:255|unique:roles, libelle,'.$id,
        ]);
        $role = Role::findOrFail($id);
        $role->update($request->all());

        return response()->route('$roles.index')
                         ->with('success', 'Role modifié avec succès');
    }
    

    //supprimer un role
    public function destroy($id)
    {
        Role::findOrFail($id)->delete();
        return redirect()->route('roles.index')->with('success', 'Role supprimé avec succès');
    }
}