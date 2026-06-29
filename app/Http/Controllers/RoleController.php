<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    // protection admin sur toutes les méthodes
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $roles = Role::withCount('utilisateurs')->get();
        return view('roles.index', compact('roles'));
    }

    public function create()
    {
        return view('roles.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'libelle' => 'required|string|max:255|unique:roles,libelle',
        ]);

        Role::create($request->only(['libelle']));

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rôle créé avec succès');
    }

    public function show($id)
    {
        $role = Role::withCount('utilisateurs')->findOrFail($id);
        return view('roles.show', compact('role'));
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return view('roles.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'libelle' => 'required|string|max:255|unique:roles,libelle,'.$id,
        ]);

        $role = Role::findOrFail($id);
        $role->update($request->only(['libelle']));

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rôle modifié avec succès');
    }

    public function destroy($id)
    {
        $role = Role::withCount('utilisateurs')->findOrFail($id);

        if ($role->utilisateurs_count > 0) {
            return redirect()
                ->route('roles.index')
                ->with('error', "Impossible de supprimer ce rôle : {$role->utilisateurs_count} utilisateur(s) l'utilisent encore.");
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Rôle supprimé avec succès');
    }
}