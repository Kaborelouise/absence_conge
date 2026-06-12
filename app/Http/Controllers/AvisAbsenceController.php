<?php

namespace App\Http\Controllers;

use App\Models\AvisAbsence;
use App\Models\DemandeAbsence;
use Illuminate\Http\Request;

class AvisAbsenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $avis = AvisAbsence::with('demandeAbsence')->get();
        return view('avis_absences.index', compact('avis'));
    }

    public function create(Request $request)
{
    // On récupère la demande concernée
    // depuis l'URL : /avis_absences/create?demande_absence_id=3
    // $request->demande_absence_id : l'id passé en paramètre
    $demande = DemandeAbsence::findOrFail($request->demande_absence_id);

    return view('avis_absences.create', compact('demande'));
}

public function store(Request $request)
{
    $request->validate([
        'avis'               => 'required|in:favorable,defavorable,en_attente',
        'type'               => 'required|in:chef_departement,responsable_direction,agent_rh,sg,dg,pca',
        'commentaire'        => 'nullable|string',
        'demande_absence_id' => 'required|exists:demande_absences,id',
    ]);

    AvisAbsence::create($request->only([
        'avis', 'type', 'commentaire', 'demande_absence_id'
    ]));

    // On retourne vers le DÉTAIL de la demande
    // et pas vers une liste d'avis
    return redirect()
        ->route('demande_absences.show', $request->demande_absence_id)
        ->with('success', 'Avis enregistré avec succès');
}

public function edit($id)
{
    $avis = AvisAbsence::findOrFail($id);
    $demande = $avis->demandeAbsence;
    // On charge aussi la demande pour afficher le contexte

    return view('avis_absences.edit', compact('avis', 'demande'));
}

public function update(Request $request, $id)
{
    $request->validate([
        'avis'        => 'required|in:favorable,defavorable,en_attente',
        'commentaire' => 'nullable|string',
    ]);

    $avis = AvisAbsence::findOrFail($id);
    $avis->update($request->only(['avis', 'commentaire']));

    // Retour vers le détail de la demande concernée
    return redirect()
        ->route('demande_absences.show', $avis->demande_absence_id)
        ->with('success', 'Avis modifié');
}

public function destroy($id)
{
    $avis = AvisAbsence::findOrFail($id);
    $demande_id = $avis->demande_absence_id;
    // On sauvegarde l'id AVANT de supprimer
    // car après delete() on ne peut plus y accéder
    $avis->delete();

    return redirect()
        ->route('demande_absences.show', $demande_id)
        ->with('success', 'Avis supprimé');
}
}