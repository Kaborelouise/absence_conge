<?php

namespace App\Http\Controllers;

use App\Models\DemandeJouissance;
use Illuminate\Http\Request;

class DemandeJouissanceController extends Controller
{
    
    public function index()
    {
        $demandes = DemandeJouissance::with('user', 'avis')->get();
        return view('demande_jouissances.index', compact('demandes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
         $user = auth()->user();
        return view('demande_jouissances.create', compact('user'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'num_demande'    => 'required|integer|unique:demande_jouissances,num_demande',
            'date_debut'     => 'required|date',
            'date_fin'       => 'required|date|after_or_equal:date_debut',
            'nombre_jour'    => 'required|integer|min:1',
            'utilisateur_id' => 'required|exists:users,id',
        ]);

        DemandeJouissance::create($request->only([
            'num_demande', 'date_debut', 'date_fin',
            'nombre_jour', 'user_id',
        ]));

        return redirect()
            ->route('demande-jouissances.index')
            ->with('success', 'Demande de jouissance soumise');
    }

    /**
     * Display the specified resource.
     */
    public function show(DemandeJouissance $demandeJouissance)
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
    public function edit(DemandeJouissance $demandeJouissance)
    {
        $demande = DemandeJouissance::findOrFail($id);
        return view('demande-jouissances.edit', compact('demande'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DemandeJouissance $demandeJouissance)
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
    public function destroy(DemandeJouissance $demandeJouissance)
    {
        DemandeJouissance::findOrFail($id)->delete();
        return redirect()
            ->route('demande-jouissances.index')
            ->with('success', 'Demande supprimée');
    }
}
