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

        // Sécurité vérifier que c'est bien le tour de cet utilisateur
        if (!$demande->peutDonnerAvis($user)) {
            return redirect()
                ->route('demande_jouissances.show', $demande->id)
                ->with('error', 'Vous n\'êtes pas autorisé à donner un avis à cette étape.');
        }

        $role = $user->role->libelle;

        // Le type d'avis est déduit du rôle connecté — jamais du formulaire
        // pour éviter qu'un utilisateur se fasse passer pour un autre acteur
        $typeAvis = match(true) {
        $role === 'Chef de Département' || $user->est_responsable_departement 
            => 'Chef de Département',

        $role === 'Agent RH'
            => 'Agent RH',

        $role === 'Responsable Direction'
            => 'Responsable Direction',

        $role === 'SG'
            => 'SG',

        $role === 'DG'
            => 'DG',

        $role === 'PCA'
            => 'PCA',

        default => strtolower($role),
    };

        // Enregistrement de l'avis
        AvisJouissance::create([
            'demande_jouissance_id' => $demande->id,
            'avis'                  => $request->avis,
            'type'                  => $typeAvis,
            'commentaire'           => $request->commentaire,
        ]);

        /**
         * MODIFIÉ : le solde a déjà été réservé (décrémenté) à la CRÉATION de la
         * demande (voir DemandeJouissanceController::store). Si l'avis est
         * défavorable, la demande n'aboutira jamais : il faut donc RESTITUER les
         * jours réservés, sinon l'Agent perdrait définitivement des jours de congé
         * pour une demande refusée — même règle que pour DemandeAbsence.
         */
        if ($request->avis === 'defavorable') {
            $demande->update(['statut' => 'rejetee']);

            $demande->user->increment('solde_conge', $demande->nombreJours());

            return redirect()
                ->route('demande_jouissances.show', $demande->id)
                ->with('success', 'Avis défavorable enregistré. La demande est rejetée.');
        }

        // Avis favorable : on recharge les avis pour recalculer
        // le prochain acteur avec les nouvelles données
        $demande->load('avis');
        $prochainActeur = $demande->prochainActeur();

        /**
         * MODIFIÉ : avant, ce bloc décrémentait le solde_conge ICI, à la validation
         * finale, avec un "max(0, ...)" qui camouflait un éventuel dépassement du
         * plafond de 30 jours au lieu de le bloquer. Le solde ayant désormais déjà
         * été décrémenté dès la création (réservation immédiate), il n'y a plus
         * rien à faire sur le solde à ce stade : on se contente de valider.
         */
        if ($prochainActeur === null) {
            $demande->update(['statut' => 'validee']);

            return redirect()
                ->route('demande_jouissances.show', $demande->id)
                ->with('success', 'Demande validée avec succès.');
        }

        // Il reste des étapes → en_cours
        $demande->update(['statut' => 'en_cours']);

        return redirect()
            ->route('demande_jouissances.show', $demande->id)
            ->with('success', 'Avis favorable enregistré. Circuit en cours.');
    }

    // suppression et modification réservé a l'Administrateur

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