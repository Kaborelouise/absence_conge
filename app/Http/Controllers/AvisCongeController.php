<?php

namespace App\Http\Controllers;

use App\Models\AvisConge;
use App\Models\DemandeConge;
use Illuminate\Http\Request;

class AvisCongeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $avis = AvisConge::with('demandeConge')->get();
        return view('avis_conges.index', compact('avis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $demandes = DemandeConge::all();
        return view('avis_conges.create', compact('demandes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'avis'             => 'required|in:favorable,defavorable,en_attente',
            
            // Seul l'agent_RH intervient sur les demandes de congés
            'type'             => 'required|in:agent_rh',
            'commentaire'      => 'nullable|string',
            'demande_conge_id' => 'required|exists:demande_conges,id',
        ]);

        AvisConge::create($request->only([
            'avis', 'type', 'commentaire', 'demande_conge_id'
        ]));

        return redirect()
            ->route('avis_conges.index')
            ->with('success', 'Avis enregistré');
    }

    /**
     * Display the specified resource.
     */
    
    public function edit($id)
    {
        $avis = AvisConge::findOrFail($id);
        $demandes = DemandeConge::all();
        return view('avis_conges.edit', compact('avis', 'demandes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'avis'        => 'required|in:favorable,defavorable,en_attente',
            'commentaire' => 'nullable|string',
        ]);
        $avis = AvisConge::findOrFail($id);
        $avis->update($request->only(['avis', 'commentaire']));

        return redirect()
            ->route('avis_conges.index')
            ->with('success', 'Avis modifié');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        AvisConge::findOrFail($id)->delete();
        
        return redirect()
            ->route('avis_conges.index')
            ->with('success', 'Avis supprimé');
    }
}
