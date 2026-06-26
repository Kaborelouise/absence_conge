<?php

namespace App\Http\Controllers;

use App\Models\DemandeConge;
use Illuminate\Http\Request;

class DemandeCongeController extends Controller
{
    //la liste est filtrer selon le rôle
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->libelle;

        // L'agent ne voit que ses propres demandes et si il est un responsable ceux de sa juridiction également
        $demandes = DemandeConge::with('user.departement.direction', 'avisConge')
            ->when(!in_array($role, ['agent_rh', 'admin']), function ($q) use ($user) { 
                //tout utilisateur qui n'est pas admin ou rh ne voit que ses demandes
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->get();

        return view('demande_conges.index', compact('demandes'));
    }

    public function create()
    {
        $user = auth()->user();
        return view('demande_conges.create', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'lieu_jouissance' => 'required|in:Afrique,Asie,Amerique,Europe',
        ]);

        DemandeConge::create([
            'lieu_jouissance' => $request->lieu_jouissance,
            // user_id vient de l'utilisateur connecté, jamais du formulaire
            'user_id' => auth()->id(),
        ]);

        return redirect()
            ->route('demande_conges.index')
            ->with('success', 'Demande de congé soumise avec succès.');
    }

    public function show($id)
    {
        $demande = DemandeConge::with(
            'user.departement.direction',
            'avisConge'
        )->findOrFail($id);

        $user = auth()->user();

        // Permet d'afficher ou pas le bouton "Compiler" dans la vue
        $peutCompiler = $demande->peutEtreCompileePar($user);

        return view('demande_conges.show', compact('demande', 'peutCompiler'));
    }

    public function edit($id)
    {
        $demande = DemandeConge::findOrFail($id);

        // Modifiable seulement par l'auteur, et seulement si
        // pas encore compilée
        if ($demande->user_id !== auth()->id() || $demande->estCompilee()) {
            return redirect()
                ->route('demande_conges.show', $id)
                ->with('error', 'Cette demande ne peut plus être modifiée.');
        }

        return view('demande_conges.edit', compact('demande'));
    }

    public function update(Request $request, $id)
    {
        $demande = DemandeConge::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->estCompilee()) {
            return redirect()
                ->route('demande_conges.show', $id)
                ->with('error', 'Modification non autorisée.');
        }

        $request->validate([
            'lieu_jouissance' => 'required|in:Afrique,Asie,Amerique,Europe',
        ]);

        $demande->update($request->only(['lieu_jouissance']));

        return redirect()
            ->route('demande_conges.index')
            ->with('success', 'Demande modifiée avec succès.');
    }

    public function destroy($id)
    {
        $demande = DemandeConge::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->estCompilee()) {
            return redirect()
                ->route('demande_conges.index')
                ->with('error', 'Suppression non autorisée.');
        }

        $demande->delete();

        return redirect()
            ->route('demande_conges.index')
            ->with('success', 'Demande supprimée.');
    }
}