<?php

namespace App\Http\Controllers;

use App\Models\JustificatifAbsence;
use Illuminate\Http\Request;

class JustificatifAbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $justificatifabsence = JustificatifAbsence::all();
        return view('justificatif.index', compact('justificatifs'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $demandes = DemandeAbsence::all();
        return view('justificatifs.create', compact('demandes'));
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $path = $request->file('fichier')->store('justificatifabsence');

        JustificatifAbsence::create([
            'fichier_path' => $path,
            'type' => $request->type,
             'demande_absence_id' => 'required|exists:demande_absences,id',
        ]);
        JustificatifAbsence::create($request->all());
        return redirect()->route('justificatifs.index')
        ->with('success', 'Justificatif ajouté');
    }

        

    // /**
    //  * Display the specified resource.
    //  */
    public function show(JustificatifAbsence $justificatifAbsence)
    {
     //
    }

    // /**
    //  * Show the form for editing the specified resource.
    //  */
    public function edit(JustificatifAbsence $justificatifAbsence)
    {
      $justificatif = JustificatifAbsence::findOrFail($id);
        $demandes = DemandeAbsence::all();
        return view('justificatifabsence.edit', compact('justificatifabsence', 'demandes'));
    
    }

    // /**
    //  * Update the specified resource in storage.
    //  */
    public function update(Request $request, $id)
    { 
        $request->validate([
            'fichier_path'       => 'required|string',
            'type'               => 'required|string',
            'demande_absence_id' => 'required|exists:demande_absences,id',
        ]);

         $justificatif = JustificatifAbsence::findOrFail($id);
        $justificatif->update($request->all());
        return redirect()->route('justificatifs.index')->with('success', 'Justificatif modifié');

         
    }

    // /**
    //  * Remove the specified resource from storage.
    //  */
    public function destroy(JustificatifAbsence $justificatifAbsence)
    {
     JustificatifAbsence::findOrFail($id)->delete();
        return redirect()->route('justificatifabsence.index')->with('success', 'Justificatif supprimé');
    }
}
