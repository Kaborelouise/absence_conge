<?php

namespace App\Http\Controllers;

use App\Models\AvisJouissance;
use App\Models\DemandeJouissance;
use Illuminate\Http\Request;

class AvisJouissanceController extends Controller
{

    public function store(Request $request)
    {
        $request->validate([
            'demande_jouissance_id' => 'required|exists:demande_jouissances,id',
            'avis'                  => 'required|in:favorable,defavorable',
            'commentaire'           => 'nullable|string|max:500',
        ]);

        // On charge la demande avec ses avis et les relations nécessaires
        // pour que peutDonnerAvis() et prochainActeur() fonctionnent correctement
        $demande = DemandeJouissance::with(
            'avis',
            'user.role',
            'user.departement'
        )->findOrFail($request->demande_jouissance_id);

        $user = auth()->user();

        // Sécurité : vérifier que c'est bien le tour de cet utilisateur
        if (!$demande->peutDonnerAvis($user)) {
            return redirect()
                ->route('demande_jouissances.show', $demande->id)
                ->with('error', 'Vous n\'êtes pas autorisé à donner un avis à cette étape.');
        }

        $role = $user->role->libelle;

        // Le type d'avis est déduit du rôle connecté — jamais du formulaire
        // pour éviter qu'un utilisateur se fasse passer pour un autre acteur
        $typeAvis = match(true) {
            $role === 'chef_departement' || $user->est_responsable_departement => 'chef_departement',
            $role === 'agent_rh'                                                => 'agent_rh',
            $role === 'responsable_direction'                                   => 'responsable_direction',
            $role === 'sg'                                                      => 'sg',
            $role === 'dg'                                                      => 'dg',
            $role === 'pca'                                                     => 'pca',
            default                                                             => $role,
        };

        // Enregistrement de l'avis
        AvisJouissance::create([
            'demande_jouissance_id' => $demande->id,
            'avis'                  => $request->avis,
            'type'                  => $typeAvis,
            'commentaire'           => $request->commentaire,
        ]);

        // Avis défavorable → arrêt immédiat du circuit
        if ($request->avis === 'defavorable') {
            $demande->update(['statut' => 'rejetee']);

            return redirect()
                ->route('demande_jouissances.show', $demande->id)
                ->with('success', 'Avis défavorable enregistré. La demande est rejetée.');
        }

        // Avis favorable : on recharge les avis pour recalculer
        // le prochain acteur avec les nouvelles données
        $demande->load('avis');
        $prochainActeur = $demande->prochainActeur();

        if ($prochainActeur === null) {
            // Plus d'acteur → demande validée
            $demande->update(['statut' => 'validee']);

            // Décrémenter le solde congé de l'agent
            $agent        = $demande->user;
            $nouveauSolde = max(0, $agent->solde_conge - $demande->nombre_jour);
            $agent->update(['solde_conge' => $nouveauSolde]);

            return redirect()
                ->route('demande_jouissances.show', $demande->id)
                ->with('success', "Demande validée. Solde mis à jour ({$nouveauSolde} jours restants).");
        }

        // Il reste des étapes → en_cours
        $demande->update(['statut' => 'en_cours']);

        return redirect()
            ->route('demande_jouissances.show', $demande->id)
            ->with('success', 'Avis favorable enregistré. Circuit en cours.');
    }

    // =========================================================
    // UPDATE / DESTROY — réservés à l'admin
    // =========================================================
    public function update(Request $request, $id)
    {
        $request->validate([
            'avis'        => 'required|in:favorable,defavorable',
            'commentaire' => 'nullable|string|max:500',
        ]);

        $avis = AvisJouissance::findOrFail($id);
        $avis->update($request->only(['avis', 'commentaire']));

        return redirect()
            ->route('demande_jouissances.show', $avis->demande_jouissance_id)
            ->with('success', 'Avis modifié.');
    }

    public function destroy($id)
    {
        $avis        = AvisJouissance::findOrFail($id);
        $demandeId   = $avis->demande_jouissance_id;
        $avis->delete();

        return redirect()
            ->route('demande_jouissances.show', $demandeId)
            ->with('success', 'Avis supprimé.');
    }
}