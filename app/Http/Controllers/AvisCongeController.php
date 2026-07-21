<?php

namespace App\Http\Controllers;

use App\Models\AvisConge;
use App\Models\DemandeConge;
use Illuminate\Http\Request;

class AvisCongeController extends Controller
{
// pas de refus pour une demande de congé juste un accusé de traitement par l'Agent RH
    public function store(Request $request)
    {
        $request->validate([
            'demande_conge_id' => 'required|exists:demande_conges,id',
            'commentaire'      => 'nullable|string|max:500',
        ]);

        $demande = DemandeConge::with('avisConge')->findOrFail($request->demande_conge_id);
        $user = auth()->user();

        if (!$demande->peutEtreCompileePar($user)) {
            return redirect()
                ->route('demande_conges.show', $demande->id)
                ->with('error', 'Vous n\'êtes pas autorisé à compiler cette demande.');
        }

        AvisConge::create([
            'demande_conge_id' => $demande->id,
            'avis'             => 'favorable', // toujours favorable : juste un accusé de traitement
            'type'             => 'Agent RH',
            'commentaire'      => $request->commentaire,
        ]);

        return redirect()
            ->route('demande_conges.show', $demande->id)
            ->with('success', 'Demande compilée avec succès.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'commentaire' => 'nullable|string|max:500',
        ]);

        $avis = AvisConge::findOrFail($id);
        $avis->update($request->only(['commentaire']));

        return redirect()
            ->route('demande_conges.show', $avis->demande_conge_id)
            ->with('success', 'Avis modifié.');
    }

    public function destroy($id)
    {
        $avis = AvisConge::findOrFail($id);
        $demandeId = $avis->demande_conge_id;
        $avis->delete();

        return redirect()
            ->route('demande_conges.show', $demandeId)
            ->with('success', 'Compilation annulée.');
    }
}