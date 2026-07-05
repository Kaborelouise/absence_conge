<?php

namespace App\Http\Controllers;

use App\Models\DemandeConge;
use Illuminate\Http\Request;

class DemandeCongeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->libelle;

        $demandes = DemandeConge::with('user.departement.direction', 'avisConge')
            ->when(!in_array($role, ['agent_rh', 'admin']), function ($q) use ($user) {
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
            // CORRECTION : tableau au lieu d'une valeur unique
            'lieu_jouissance'   => 'required|array|min:1',
            'lieu_jouissance.*' => 'in:Afrique,Burkina,Canada,Europe,Asie,USA',
        ], [
            // Messages d'erreur en français
            'lieu_jouissance.required' => 'Veuillez sélectionner au moins un lieu.',
            'lieu_jouissance.min'      => 'Veuillez sélectionner au moins un lieu.',
            'lieu_jouissance.*.in'     => 'Lieu de jouissance invalide.',
        ]);

        DemandeConge::create([
            // Le cast 'array' dans le modèle convertit automatiquement en JSON
            'lieu_jouissance' => $request->lieu_jouissance,
            'user_id'         => auth()->id(),
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

        $user           = auth()->user();
        $peutCompiler   = $demande->peutEtreCompileePar($user);
        $peutAbandonner = $demande->peutEtreAbandonneePar($user);

        return view('demande_conges.show', compact('demande', 'peutCompiler', 'peutAbandonner'));
    }

    public function edit($id)
    {
        $demande = DemandeConge::findOrFail($id);

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
            'lieu_jouissance'   => 'required|array|min:1',
            'lieu_jouissance.*' => 'in:Afrique,Burkina,Canada,Europe,Asie,USA',
        ], [
            'lieu_jouissance.required' => 'Veuillez sélectionner au moins un lieu.',
            'lieu_jouissance.min'      => 'Veuillez sélectionner au moins un lieu.',
            'lieu_jouissance.*.in'     => 'Lieu de jouissance invalide.',
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

    public function abandonner($id)
    {
        $demande = DemandeConge::findOrFail($id);

        if (!$demande->peutEtreAbandonneePar(auth()->user())) {
            return redirect()
                ->route('demande_conges.show', $id)
                ->with('error', 'Vous ne pouvez pas abandonner cette demande.');
        }

        $demande->update(['abandonnee' => true]);

        return redirect()
            ->route('demande_conges.index')
            ->with('success', 'Demande abandonnée.');
    }
}