<?php

namespace App\Http\Controllers;

use App\Models\AvisAbsence;
use App\Models\DemandeAbsence;
use Illuminate\Http\Request;

class AvisAbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $avis = AvisAbsence::with('demandeAbsence')->get();
        return view('avis_absences.index', compact('avis'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $demandes = DemandeAbsence::all();
        return view('avis_absences.create', compact('demandes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'avis' => 'required|in:favorable,defavorable,en_attente',
            'type' => 'required|in:chef_departement,responsable_direction,agent_rh,sg,dg,pca',
            'commentaire' => 'nullable|string',
            'demande_absence_id' => 'required|exists:demande_absences,id',

        ]);

        AvisAbsence::create($request->only([
            'avis', 'type', 'commentaire', 'demande_absence_id'
        ]));
        return redirect()
            ->route('avis_absences.index')
            ->with('success', 'Avis enregistré');
    }

    
    
    public function edit($id)
    {
         $avis = AvisAbsence::findOrFail($id);
         $demandes = DemandeAbsence::all();
         return view('avis_absences.edit', compact('avis', 'demandes'));
    
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

        $avis = AvisAbsence::findOrFail($id);
        $avis->update($request->only(['avis', 'commentaire']));

        return redirect()
        ->route('avis_absences.index')
        ->with('success', 'Avis modifié');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        AvisAbsence::findOrFail($id)->delete();
        return redirect()
        ->route('avis_absences.index')
        ->with('success', 'Avis supprimé');
    }
}
