<?php

namespace App\Http\Controllers;

use App\Models\DemandeJouissance;
use Illuminate\Http\Request;

class DemandeJouissanceController extends Controller
{
    
    public function index()
    {
        $demande = DemandeJouissance::with('user', 'avis')->get();
        return view('demande_jouissances.index', compact('demande'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         $user = auth()->user();
        return view('demande_jouissances.create', compact('user'));
    }

    public function store(Request $request)
{
    $request->validate([
        'date_debut' => 'required|date',
        'date_fin'   => 'required|date|after_or_equal:date_debut',
    ]);

    // calcul nombre de jours
    $dateDebut = new \DateTime($request->date_debut);
    $dateFin   = new \DateTime($request->date_fin);
    $nombreJour = $dateDebut->diff($dateFin)->days + 1;

    // génération du numéro (OBLIGATOIRE AVANT INSERT)
    $last = DemandeJouissance::orderBy('id', 'desc')->first();
    $nextNumber = $last ? $last->id + 1 : 1;

    $numDemande = $nextNumber; // IMPORTANT (INTEGER)

    // INSERT complet
    DemandeJouissance::create([
        'num_demande' => $numDemande,   // 🔥 IMPORTANT
        'date_debut'  => $request->date_debut,
        'date_fin'    => $request->date_fin,
        'nombre_jour' => $nombreJour,
        'user_id'     => auth()->id(),
    ]);

    return redirect()
        ->route('demande_jouissances.index')
        ->with('success', 'Demande créée avec succès.');
}



    /**
     * Store a newly created resource in storage.
     */
    // public function store(Request $request)
    // {
        
    //     $request->validate([
        
    //         'date_debut'     => 'required|date',
    //         'date_fin'       => 'required|date|after_or_equal:date_debut',  
    //     ]);
        
    //     $dateDebut = new \DateTime($request->date_debut);
    //     $dateFin = new \DateTime($request->date_fin);
    //     $nombreJour = $dateDebut->diff($dateFin)->days + 1;

    //   DemandeJouissance::create([
    //   'num_demande'  => $request->num_demande,
    //   'date_debut'  => $request->date_debut,
    //   'date_fin'    => $request->date_fin,
    //   'nombre_jour' => $nombreJour,
    //   'user_id'     => auth()->id(),
    //  ]);
    //  return redirect()
    //         ->route('demande_jouissances.index')
    //         ->with('success', 'Demande créée avec succès.');
    // }




    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $demande = DemandeJouissance::with(
            'user.departement.direction',
            'avis'
        )->findOrFail($id);

        return view('demande_jouissances.show', compact('demande'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $demande = DemandeJouissance::findOrFail($id);
        return view('demande_jouissances.edit', compact('demande'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'nombre_jour' => 'required|integer|min:1',
            'statut'      => 'required|in:en_attente,en_cours,validee,rejetee',
        ]);

        $demande = DemandeJouissance::findOrFail($id);
        $demande->update($request->only([
            'date_debut', 'date_fin', 'nombre_jour', 'statut'
        ]));

        return redirect()
            ->route('demande_jouissances.index')
            ->with('success', 'Demande modifiée');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        DemandeJouissance::findOrFail($id)->delete();
        return redirect()
            ->route('demande_jouissances.index') 
            ->with('success', 'Demande supprimée');
    }
}
