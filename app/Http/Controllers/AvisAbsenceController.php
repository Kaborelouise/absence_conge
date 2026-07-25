<?php

namespace App\Http\Controllers;

use App\Models\AvisAbsence;
use App\Models\DemandeAbsence;
use Illuminate\Http\Request;
use App\Helpers\LogActivity;

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

        $typeAvis = match (true) {
            $role === 'Responsable Département' || $user->est_responsable_departement => 'chef_departement',
            $role === 'Responsable Direction'                                          => 'responsable_direction',
            $role === 'Agent RH'                                                       => 'agent_rh',
            $role === 'SG'                                                             => 'sg',
            $role === 'DG'                                                             => 'dg',
            $role === 'PCA'                                                            => 'pca',
            default                                                                    => strtolower($role),
        };

        if ($role === 'Agent RH') {
            $demande->update(['retenue_salaire' => $request->boolean('retenue_salaire')]);
        }

        AvisAbsence::create([
            'demande_absence_id' => $demande->id,
            'avis'               => $request->avis,
            'type'               => $typeAvis,
            'commentaire'        => $request->commentaire,
            'user_id'            => $user->id,
        ]);

        if ($request->avis === 'defavorable') {
            $demande->update(['statut' => 'rejetee']);
            $demande->user->increment('solde_absence', $demande->nombreJours());

            // LOG : avis défavorable — rejet demande
            LogActivity::log(
                'update',
                'DemandeAbsence',
                $demande->id,
                "Avis défavorable ({$role}) sur demande absence #{$demande->num_demande} — demande rejetée"
            );

            return redirect()
                ->route('demande_absences.show', $demande->id)
                ->with('success', 'Avis défavorable enregistré. La demande est rejetée.');
        }

        $demande->load('avisAbsence');
        $prochainActeur = $demande->prochainActeur();

        if ($prochainActeur === null) {
            $demande->update(['statut' => 'validee']);

            // LOG : validation finale
            LogActivity::log(
                'update',
                'DemandeAbsence',
                $demande->id,
                "Validation finale ({$role}) demande absence #{$demande->num_demande}"
            );

            return redirect()
                ->route('demande_absences.show', $demande->id)
                ->with('success', 'Demande validée avec succès.');
        }

        $demande->update(['statut' => 'en_cours']);

        // LOG : avis favorable intermédiaire
        LogActivity::log(
            'update',
            'DemandeAbsence',
            $demande->id,
            "Avis favorable ({$role}) sur demande absence #{$demande->num_demande}"
        );

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
        $avis      = AvisAbsence::findOrFail($id);
        $demandeId = $avis->demande_absence_id;
        $avis->delete();

        return redirect()
            ->route('demande_absences.show', $demandeId)
            ->with('success', 'Avis supprimé.');
    }
}