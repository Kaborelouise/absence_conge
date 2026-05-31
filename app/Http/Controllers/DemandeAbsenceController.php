<?php

namespace App\Http\Controllers;

use App\Models\DemandeAbsence;
use App\Models\User;
use Illuminate\Http\Request;

class DemandeAbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $demandes = DemandeAbsence::with('user', 'justificatifabsence', 'avis')
        ->get();
        return view('demandes_absence.index', compact('demandes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $user = Auth()->user();
        return view('demandes_absence.create', compact('users'));
    }
    

    
    public function store(Request $request)
    {
        $request->validate([
            'num_demande'    => 'required|integer|unique:demande_absences',
            'date_debut'     => 'required|date',
            'date_fin'       => 'required|date|after_or_equal:date_debut',
            'motif'          => 'required|string',
            'user_id' => 'required|exists:user,id',
        ]);

        DemandeAbsence::create($request->only([
             'num_demande', 'date_debut', 'date_fin',
            'motif', 'interimaire', 'retenue_salaire',
            'users_id',
        ]));

        return redirect()->route('demande-absences.index')->with('success', 'Demande créée avec succès');
    }

    

    public function show($id)
    {
        $demande = DemandeAbsence::with('user.departement.direction', 'justificatifabsence', 'avisabsence')->findOrFail($id);

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $demande = DemandeAbsence::findOrFail($id);
        
        return view('demande_absences.edit', compact('demande'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
            'motif'      => 'required|string',
            'interimaire' => 'nullable|string',
            'statut' => 'required|in:en_attente,en_cours,validee,rejetee',
            'retenue_salaire' => 'boolean',

        ]);
        $demande = DemandeAbsence::findOrFail($id);
        $demande->update($request->only([
            'date_debut',
            'date_fin',
            'motif',
            'interimaire', 'statut', 'retenu_salaire',

        ]));
        return redirect()->route('demande-absences.index')->with('success', 'Demande modifiée avec succès');
    }

    

   
    public function destroy($id)
    {
        DemandeAbsence::findOrFail($id)->delete();
        return redirect()->route('demande_absences.index')->with('success', 'Demande supprimée');
    }
    
}
