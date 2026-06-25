<?php

namespace App\Http\Controllers;

use App\Models\DemandeJouissance;
use Illuminate\Http\Request;

class DemandeJouissanceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->libelle;

        $demandes = DemandeJouissance::with('user.departement.direction', 'avis')
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

        return view('demande_jouissances.index', compact('demandes'));
    }

    public function create()
    {
        $user = auth()->user();
        return view('demande_jouissances.create', compact('user'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'nombre_jour' => 'required|integer|min:1',
        ]);

        DemandeJouissance::create([
            'num_demande'  => time(),
            'date_debut'   => $request->date_debut,
            'date_fin'     => $request->date_fin,
            'nombre_jour'  => $request->nombre_jour,
            'user_id'      => auth()->id(),
            'statut'       => 'en_attente',
        ]);

        return redirect()
            ->route('demande_jouissances.index')
            ->with('success', 'Demande de jouissance soumise avec succès.');
    }

     public function show($id)
     {
    $demande = DemandeJouissance::with(
        'user.departement.direction',
        'avis'
    )->findOrFail($id);

    $user = auth()->user();

    $peutAgir       = $demande->peutDonnerAvis($user);
    $prochainActeur = $demande->prochainActeur();

    // Ajout dernière étape traitée pour afficher "Étape actuelle"
  
    $derniereEtape = $demande->avis->last()?->type;

    // Agent du même département que l'agent
    $agentsMemeDepartement = \App\Models\User::where('departement_id', $demande->user->departement_id)
        ->where('id', '!=', $demande->user_id)
        ->get();

    return view('demande_jouissances.show', compact(
        'demande',
        'peutAgir',
        'prochainActeur',
        'derniereEtape',
        'agentsMemeDepartement'
    ));
}

    public function edit($id)
    {
        $demande = DemandeJouissance::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()
                ->route('demande_jouissances.show', $id)
                ->with('error', 'Cette demande ne peut plus être modifiée.');
        }

        return view('demande_jouissances.edit', compact('demande'));
    }

    public function update(Request $request, $id)
    {
        $demande = DemandeJouissance::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()
                ->route('demande_jouissances.show', $id)
                ->with('error', 'Modification non autorisée.');
        }

        $request->validate([
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'nombre_jour' => 'required|integer|min:1',
        ]);

        $demande->update($request->only([
            'date_debut', 'date_fin', 'nombre_jour',
        ]));

        return redirect()
            ->route('demande_jouissances.index')
            ->with('success', 'Demande modifiée avec succès.');
    }

    public function destroy($id)
    {
        $demande = DemandeJouissance::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()
                ->route('demande_jouissances.index')
                ->with('error', 'Suppression non autorisée.');
        }

        $demande->delete();

        return redirect()
            ->route('demande_jouissances.index')
            ->with('success', 'Demande supprimée.');
    }
}