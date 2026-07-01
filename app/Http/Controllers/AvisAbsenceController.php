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

        if ($request->avis === 'defavorable') {
            $demande->update(['statut' => 'rejetee']);

            return redirect()
                ->route('demande_absences.show', $demande->id)
                ->with('success', 'Avis défavorable enregistré. La demande est rejetée.');
        }

        $demande->load('avisAbsence');
        $prochainActeur = $demande->prochainActeur();

        if ($prochainActeur === null) {
            $demande->update(['statut' => 'validee']);

            $jours = \Carbon\Carbon::parse($demande->date_debut)
                                   ->diffInDays($demande->date_fin);

            $agent = $demande->user;
            $nouveauSolde = max(0, $agent->solde_absence - $jours);
            $agent->update(['solde_absence' => $nouveauSolde]);

            return redirect()
                ->route('demande_absences.show', $demande->id)
                ->with('success', "Demande validée avec succès. Solde mis à jour ({$nouveauSolde} jours restants).");
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