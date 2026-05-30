<?php

namespace App\Http\Controllers;

use App\Models\DemandeAbsence;
use Illuminate\Http\Request;

class DemandeAbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $demandes = DemandeAbsence::with('user', 'justificatif', 'avis')->get();
        return view('demandes_absence.index', compact('demandes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $utilisateurs= User::all();
        return view('demandes_absence.create', compact('utilisateurs'));
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'num_demande'    => 'required|integer|unique:demande_absences',
            'date_debut'     => 'required|date',
            'date_fin'       => 'required|date|after_or_equal:date_debut',
            'motif'          => 'required|string',
            'user_id' => 'required|exists:user,id',
        ]);
        DemandeAbsence::create($request->all());
        return redirect()->route('demande-absences.index')->with('success', 'Demande créée avec succès');
    }

    public function edit($id)
    

    /**
     * Display the specified resource.
     */
    public function show(DemandeAbsence $demandeAbsence)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DemandeAbsence $demandeAbsence)
    {
        $demande = DemandeAbsence::findOrFail($id);
        $utilisateurs = User::all();
        return view('demande_absences.edit', compact('demande', 'users'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DemandeAbsence $demandeAbsence)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'motif'      => 'required|string',
        ]);
        $demande = DemandeAbsence::findOrFail($id);
        $demande->update($request->all());
        return redirect()->route('demande-absences.index')->with('success', 'Demande modifiée avec succès');
    }

    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DemandeAbsence::findOrFail($id)->delete();
        return redirect()->route('demande_absences.index')->with('success', 'Demande supprimée');
    }
    
}
