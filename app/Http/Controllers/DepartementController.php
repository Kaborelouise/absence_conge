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
        return view('departemnts.index', compact('departements'));
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
        Departement::create($request->all());
        
        return redirect()->route('departemets.index')
                         ->with('success', 'Département créé avec succès');
    }

    /**
     * Display the specified resource.
     */
    public function show(Departement $departement)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $departement = Departement::findOrFail($id);
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
        $departement->update($request->all());

       return redirect()->route('departements.index')->with('success', 'Département modifié avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Departement $departement)
    {
        Departement::findOrFail($id)->delete();
        return redirect()->route('departements.index')->with('success', 'Département supprimé');
    }
}
