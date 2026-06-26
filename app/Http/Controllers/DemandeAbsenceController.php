<?php

namespace App\Http\Controllers;

use App\Models\DemandeAbsence;
use Illuminate\Http\Request;

class DemandeAbsenceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->libelle;

        $demandes = DemandeAbsence::with('user.departement.direction', 'avisAbsence')
            ->when($role === 'agent', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->when($role === 'chef_departement' || $user->est_responsable_departement, function ($q) use ($user) {
                $q->whereHas('user', function ($q2) use ($user) {
                    $q2->where('departement_id', $user->departement_id);
                });
            })
            ->when($role === 'responsable_direction', function ($q) use ($user) {
                $directionId = $user->departement->direction_id;
                $q->whereHas('user.departement', function ($q2) use ($directionId) {
                    $q2->where('direction_id', $directionId);
                });
            })
            ->when(in_array($role, ['agent_rh', 'sg', 'dg', 'pca']), function ($q) {
                // RH, SG, DG, PCA voient toutes les demandes
            })
            ->latest()
            ->get();

        return view('demande_absences.index', compact('demandes'));
    }

public function create()
{
    $user = auth()->user();

    $agentsMemeDepartement = \App\Models\User::where('departement_id', $user->departement_id)
        ->where('id', '!=', $user->id)
        ->get();

    return view('demande_absences.create', compact('user', 'agentsMemeDepartement'));
}

    public function store(Request $request)
    {
        $request->validate([
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'motif'       => 'required|string|max:500',
            'interimaire' => 'nullable|string|max:255',
        ]);

        DemandeAbsence::create([
            'num_demande'  => time(),
            'date_debut'   => $request->date_debut,
            'date_fin'     => $request->date_fin,
            'motif'        => $request->motif,
            'interimaire'  => $request->interimaire,
            'user_id'      => auth()->id(),
            'statut'       => 'en_attente',
        ]);

        return redirect()
            ->route('demande_absences.index')
            ->with('success', 'Demande soumise avec succès.');
    }

     public function show($id)
     {
    $demande = DemandeAbsence::with(
        'user.departement.direction',
        'justificatifAbsence',
        'avisAbsence'
    )->findOrFail($id);

    $user = auth()->user();

    $peutAgir       = $demande->peutDonnerAvis($user);
    $prochainActeur = $demande->prochainActeur();
    $derniereEtape = $demande->avisAbsence->last()?->type;

    $peutAbandonner = $demande->peutEtreAbandonneePar ($user);

    $agentsMemeDepartement = \App\Models\User::where('departement_id', $demande->user->departement_id)
        ->where('id', '!=', $demande->user_id)
        ->get();

    return view('demande_absences.show', compact(
        'demande', 
        'peutAgir', 
        'prochainActeur', 
        'derniereEtape',
        'peutAbandonner',
        'agentsMemeDepartement'
    ));
 }
    public function edit($id)
    {
        $demande = DemandeAbsence::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()
                ->route('demande_absences.show', $id)
                ->with('error', 'Cette demande ne peut plus être modifiée.');
        }

        return view('demande_absences.edit', compact('demande'));
    }

    public function update(Request $request, $id)
    {
        $demande = DemandeAbsence::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()
                ->route('demande_absences.show', $id)
                ->with('error', 'Modification non autorisée.');
        }

        $request->validate([
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'motif'       => 'required|string|max:500',
            'interimaire' => 'nullable|string|max:255',
        ]);

        $demande->update($request->only([
            'date_debut', 'date_fin', 'motif', 'interimaire',
        ]));

        return redirect()
            ->route('demande_absences.index')
            ->with('success', 'Demande modifiée avec succès.');
    }

    public function destroy($id)
    {
        $demande = DemandeAbsence::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()
                ->route('demande_absences.index')
                ->with('error', 'Suppression non autorisée.');
        }

        $demande->delete();

        return redirect()
            ->route('demande_absences.index')
            ->with('success', 'Demande supprimée.');
    }

        public function abandonner($id)
        {
        $demande = DemandeAbsence::findOrFail($id);

         if (!$demande->peutEtreAbandonneePar(auth()->user())) {
        return redirect() 
            ->route('demande_absences.show', $id)
            ->with('error', 'Vous ne pouvez pas abandonner cette demande.');
       }

        $demande->update(['abandonnee' => true]);

        return redirect()
          ->route('demande_absences.index')
          ->with('success', 'Demande abandonnée.');
        }
}