<?php

namespace App\Http\Controllers;

use App\Models\JustificatifAbsence;
use App\Models\DemandeAbsence;
use Illuminate\Http\Request;

class JustificatifAbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $justificatifabsence = JustificatifAbsence::with('DemandeAbsence');
        return view('justificatifabsence.index', compact('justificatifabsences'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $demandes = DemandeAbsence::all();
        return view('justificatifabsence.create', compact('demandeabsence'));
    }
    

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
         $request->validate([
            'fichier'            => 'required|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'type'               => 'required|string',
            'demande_absence_id' => 'required|exists:demande_absences,id',
        ]);
        $fichierPath = $request->file('fichier')->store('justificatifabsence', 'public');

        JustificatifAbsence::create([
            'fichier_path' => $fichierPath,
            'type' => $request->type,
             'demande_absence_id' => 'required|exists:demande_absences,id',
        ]);
        
        return redirect()
            ->route('justificatifs.index')
            ->with('success', 'Justificatif ajouté');
    }

    public function show(JustificatifAbsence $justificatifAbsence)
    {
     
    }

    
    public function edit($id)
    {
      $justificatif = JustificatifAbsence::findOrFail($id);
        $demandes = DemandeAbsence::all();
        return view('justificatifabsence.edit', compact('justificatifabsence', 'demandes'));
    
    }
    public function update(Request $request, $id)
    { 
        $request->validate([
            'fichier'       => 'sometimes|file|mimes:pdf,jpg,jpeg,png|max:2048',
            'type'               => 'required|string',
            'demande_absence_id' => 'required|exists:demande_absences,id',
        ]);

         $justificatifabsence = JustificatifAbsence::findOrFail($id);

         //si un nouveau fichier est envoyé
         if($request->hasFile('fichier')) {
            $fichierPath = $request->file('fichier')
            ->store('justificatifabsence', 'public');

            $justificatifabsence->fichier_path = $fichierPath;
        }

        $justificatifabsence->type = $request->type;
        $justificatifabsence->save();
        return redirect()
        ->route('justificatifs.index')
        ->with('success', 'Justificatif modifié');

         
    }
    public function destroy(JustificatifAbsence $justificatifAbsence)
    {
     JustificatifAbsence::findOrFail($id)->delete();
        return redirect()
        ->route('justificatifabsence.index')
        ->with('success', 'Justificatif supprimé');
    }
}
