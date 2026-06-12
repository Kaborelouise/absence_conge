<?php

namespace App\Http\Controllers;

use App\Models\DemandeConge;
use App\Models\User;
use Illuminate\Http\Request;

class DemandeCongeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $demandes = DemandeConge::with('user', 'avisConge')->get();
        return view('demande_conges.index', compact('demandes'));
    }
        
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = auth()->user();
        return view('demande_conges.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        
    $request->validate([
            'lieu_jouissance' => 'required|string',
            'user_id'  => 'required|exists:users,id',
        ]);
        DemandeConge::create($request->only([
            'lieu_jouissance',
            'user_id',
            
        ]));;
        return redirect()->route('demande_conges.index')->with('success', 'Demande de congé créée');
    }

   
    public function edit($id)
    {
         $demande = DemandeConge::findOrFail($id);
        return view('demande_conges.edit', compact('demande'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
         $request->validate([
            'lieu_jouissance' => 'required|string',
            'statut' => 'required|in:en_attente,compilee,validee',
        ]);
        $demande = DemandeConge::findOrFail($id);
        $demande->update($request->only([
            'lieu_jouissance', 
            'statut'
        ]));

        return redirect()
            ->route('demande_conges.index')
            ->with('success', 'Demande modifiée');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
         DemandeConge::findOrFail($id)->delete();
        return redirect()
            ->route('demande_conges.index')
            ->with('success', 'Demande supprimée');
    }

    public function show($id)
{
    $demande = DemandeConge::with('user', 'avisConge')
                ->findOrFail($id);
    return view('demande_conges.show', compact('demande'));
}
}
