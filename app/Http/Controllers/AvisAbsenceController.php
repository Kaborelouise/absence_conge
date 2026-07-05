<?php

namespace App\Http\Controllers;

use App\Models\AvisAbsence;
use App\Models\DemandeAbsence;
use Illuminate\Http\Request;

class AvisAbsenceController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'demande_absence_id' => 'required|exists:demande_absences,id',
            'avis'               => 'required|in:favorable,defavorable',
            'commentaire'        => 'nullable|string|max:500',
            'retenue_salaire'    => 'nullable|boolean',
        ]);

        $demande = DemandeAbsence::with('avisAbsence', 'user.role', 'user.departement')
                                 ->findOrFail($request->demande_absence_id);

        $user = auth()->user();

        if (!$demande->peutDonnerAvis($user)) {
            return redirect()
                ->route('demande_absences.show', $demande->id)
                ->with('error', 'Vous n\'êtes pas autorisé à donner un avis à cette étape.');
        }

        $role = $user->role->libelle;

        $typeAvis = match(true) {
            $role === 'chef_departement' || $user->est_responsable_departement => 'chef_departement',
            $role === 'responsable_direction'                                   => 'responsable_direction',
            $role === 'agent_rh'                                                => 'agent_rh',
            $role === 'sg'                                                      => 'sg',
            $role === 'dg'                                                      => 'dg',
            $role === 'pca'                                                     => 'pca',
            default     
            
                => $role,
        };

        if ($role === 'agent_rh') {
            $demande->update([
                'retenue_salaire' => $request->boolean('retenue_salaire'),
            ]);
        }

        AvisAbsence::create([
            'demande_absence_id' => $demande->id,
            'avis'               => $request->avis,
            'type'               => $typeAvis,
            'commentaire'        => $request->commentaire,
            'user_id'            => $user->id, // On enregistre l'utilisateur qui a donné l'avis
        ]);

        /**
         * MODIFIÉ : le solde a été réservé (décrémenté) dès la CRÉATION de la demande
         * (voir DemandeAbsenceController::store). Si un avis défavorable est donné,
         * la demande n'aboutira jamais : il faut donc RESTITUER les jours réservés,
         * sinon l'agent perdrait définitivement des jours pour une demande refusée —
         * ce qui contredit la règle métier ("si une demande est refusée, pourquoi
         * extraire ça du solde ?").
         */
        if ($request->avis === 'defavorable') {
            $demande->update(['statut' => 'rejetee']);

            // Restitution des jours réservés à la création, la demande est rejetée
            $demande->user->increment('solde_absence', $demande->nombreJours());

            return redirect()
                ->route('demande_absences.show', $demande->id)
                ->with('success', 'Avis défavorable enregistré. La demande est rejetée.');
        }

        $demande->load('avisAbsence');
        $prochainActeur = $demande->prochainActeur();

        /**
         * MODIFIÉ : avant, ce bloc recalculait les jours et décrémentait le solde
         * ICI, à la validation finale, avec un "max(0, ...)" qui camouflait un
         * éventuel dépassement au lieu de le bloquer. Désormais, le solde a déjà
         * été décrémenté dès la création de la demande (réservation immédiate) :
         * il n'y a donc PLUS RIEN À FAIRE sur le solde à ce stade, on se contente
         * de passer le statut à "validee".
         */
        if ($prochainActeur === null) {
            $demande->update(['statut' => 'validee']);

            return redirect()
                ->route('demande_absences.show', $demande->id)
                ->with('success', 'Demande validée avec succès.');
        }

        $demande->update(['statut' => 'en_cours']);

        return redirect()
            ->route('demande_absences.show', $demande->id)
            ->with('success', 'Avis favorable enregistré. Circuit en cours.');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'avis'        => 'required|in:favorable,defavorable',
            'commentaire' => 'nullable|string|max:500',
        ]);

        $avis = AvisAbsence::findOrFail($id);
        $avis->update($request->only(['avis', 'commentaire']));

        return redirect()
            ->route('demande_absences.show', $avis->demande_absence_id)
            ->with('success', 'Avis modifié.');
    }

    public function destroy($id)
    {
        $avis = AvisAbsence::findOrFail($id);
        $demandeId = $avis->demande_absence_id;
        $avis->delete();

        return redirect()
            ->route('demande_absences.show', $demandeId)
            ->with('success', 'Avis supprimé.');
    }
}