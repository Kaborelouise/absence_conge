<?php

namespace App\Http\Controllers;

use App\Models\Departement;
use App\Models\Direction;
use Illuminate\Http\Request;

class DepartementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departements = Departement::with('direction')->get();
        return view('departements.index', compact('departements'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()

    {
        $directions = Direction::all();

        return view('departements.create', compact('directions'));
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'libelle_court' => 'required|string|max:50',
            'libelle_long'  => 'required|string|max:255',
            'direction_id'  => 'required|exists:directions,id',
        ]);
        Departement::create($request->only([
            'libelle_court',
            'libelle_long',
            'direction_id'

        ]));
        
        return redirect()->route('departements.index')
                         ->with('success', 'Département créé avec succès');
    }

    public function edit($id)
    {
        $departement = Departement::findOrFail($id);
        $directions = Direction::all();
        return view('departements.edit', compact('departement', 'directions'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'libelle_court' => 'required|string|max:50',
            'libelle_long'  => 'required|string|max:255',
            'direction_id'  => 'required|exists:directions,id',
        ]);
        $departement = Departement::findOrFail($id);
        $departement->update($request->only([
            'libelle_court',
            'libelle_long',
            'direction_id'

        ]));

       return redirect()->route('departements.index')->with('success', 'Département modifié avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
   public function destroy($id)
   {
    $departement = Departement::findOrFail($id);

    if ($departement->users()->count() > 0) {
        return redirect()
            ->route('departements.index')
            ->with('error', 'Impossible de supprimer ce département car il contient des utilisateurs.');
    }

      $departement->delete();

         return redirect()
        ->route('departements.index')
        ->with('success', 'Département supprimé avec succès.');
}
}
