<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use Illuminate\Http\Request;

class DirectionController extends Controller
{
    //Affiche la liste des directions
    public function index()
    {
        $directions = Direction::with('departements')->get();
    //with('departements) charge les departements de chaque direction
        return view('directions.index', compact('directions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('directions.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'libelle_court' => 'required|string|max:50',
            'libelle_long'  => 'required|string|max:255',
        ]);
        Direction::create($request ->only([
            'libelle_court',
            'libelle_long'

            
        ]));

        return redirect()->route('directions.index')
        ->with('success', 'Direction créée avec succès');
    }

    // /**
    //  * Display the specified resource.
    //  */
    // public function show(Direction $direction)
    // {
    //     //
    // }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        
        $direction = Direction::findOrFail($id);
        return view('directions.edit', compact('direction'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'libelle_court' => 'required|string|max:50',
            'libelle_long'  => 'required|string|max:255',
        ]);
        $direction = Direction::findOrFail($id);
        $direction->update($request->all());

        return redirect()->route('directions.index')
                        ->with('success', 'Direction modifiée avec succès');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        Direction::findOrFail($id)->delete();
        return redirect()->route('directions.index')
                        ->with('success', 'Direction supprimée');
    }
    
}
