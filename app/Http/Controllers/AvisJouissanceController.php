<?php

namespace App\Http\Controllers;

use App\Models\AvisJouissance;
use App\Models\DemandeJouissance;
use Illuminate\Http\Request;

class AvisJouissanceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $avis = AvisJouissance::with('demandeJouissance')->get();
        return view('avis_jouissances.index', compact('avis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $demande = DemandeJouissance::findOrFail($request->demande_jouissance_id);
        return view('avis_jouissances.create', compact('demande'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'avis' => 'required|in:favorable,defavorable,en_attente',
            'type' => 'required|in:chef_departement,agent_rh,responsable_direction,sg,dg,pca',

            'commentaire'           => 'nullable|string',
            'demande_jouissance_id' => 'required|exists:demande_jouissances,id',
        ]);

        AvisJouissance::create($request->only([
            'avis', 'type', 'commentaire', 'demande_jouissance_id'
        ]));

        return redirect()
            ->route('avis_jouissances.index')
            ->with('success', 'Avis enregistré');
    }

   
    public function edit($id)
    {
        $avis = AvisJouissance::findOrFail($id);
        $demande = DemandeJouissance::all();

        return view('avis_jouissances.edit', compact('avis', 'demande'));
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

        $avis = AvisJouissance::findOrFail($id);
        $avis->update($request->only(['avis', 'commentaire']));
        return redirect()
            ->route('avis_jouissances.index')
            ->with('success', 'Avis modifié');
   
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        AvisJouissance::findOrFail($id)->delete();
        return redirect()
            ->route('avis_jouissances.index')
            ->with('success', 'Avis supprimé');
    }
}
