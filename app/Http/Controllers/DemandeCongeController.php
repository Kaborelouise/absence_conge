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
        $demandes = DemandeConge::with('user', 'avisconge')->get();
        return view('demande-conges.index', compact('demandes'));
    }
        
    

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $utilisateurs = User::all();
        return view('demande-conges.create', compact('utilisateurs'));
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
        DemandeConge::create($request->all());
        return redirect()->route('demande-conges.index')->with('success', 'Demande de congé créée');
    }

    /**
     * Display the specified resource.
     */
    public function show(DemandeConge $demandeConge)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DemandeConge $demandeConge)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DemandeConge $demandeConge)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DemandeConge $demandeConge)
    {
        //
    }
}
