<?php

namespace App\Http\Controllers;

use App\Models\DemandeAbsence;
use App\Models\SessionAdministrative;
use Illuminate\Http\Request;
use App\Helpers\LogActivity;

class DemandeAbsenceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->libelle;

        $demandes = DemandeAbsence::with('user.departement.direction', 'avisAbsence')
            ->when($role === 'Agent', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->when($role === 'Responsable Département' || $user->est_responsable_departement, function ($q) use ($user) {
                $q->whereHas('user', function ($q2) use ($user) {
                    $q2->where('departement_id', $user->departement_id);
                });
            })
            ->when($role === 'Responsable Direction', function ($q) use ($user) {
                $directionId = $user->departement->direction_id;
                $q->whereHas('user.departement', function ($q2) use ($directionId) {
                    $q2->where('direction_id', $directionId);
                });
            })
            ->when(in_array($role, ['Administrateur', 'Agent RH', 'SG', 'DG', 'PCA']), function ($q) {})
            ->latest()
            ->get();

        return view('demande_absences.index', compact('demandes'));
    }

    public function create()
    {
        $user = auth()->user();
        $AgentsMemeDepartement = \App\Models\User::where('departement_id', $user->departement_id)
            ->where('id', '!=', $user->id)->get();
        return view('demande_absences.create', compact('user', 'AgentsMemeDepartement'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'motif'       => 'required|string|max:500',
            'interimaire' => 'nullable|string|max:255',
        ]);

        $user = auth()->user();

        $session = SessionAdministrative::courante();

        if ($session === null || !$session->estOuvertePour('absence')) {
            return redirect()->back()->withInput()
                ->with('error', 'Aucune session n\'est actuellement ouverte pour les demandes d\'absence.');
        }

        $jours = \Carbon\Carbon::parse($request->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($request->date_fin)) + 1;

        if ($jours > $user->solde_absence) {
            return redirect()->back()->withInput()
                ->with('error', "Solde insuffisant : vous demandez {$jours} jour(s), il ne vous reste que {$user->solde_absence} jour(s).");
        }

        // CORRECTION : on capture le résultat dans $demande pour avoir l'id
        $demande = DemandeAbsence::create([
            'num_demande'                    => time(),
            'date_debut'                     => $request->date_debut,
            'date_fin'                       => $request->date_fin,
            'motif'                          => $request->motif,
            'interimaire'                    => $request->interimaire,
            'user_id'                        => $user->id,
            'statut'                         => 'en_attente',
            'session_administrative_id'      => $session->id,
        ]);

        $user->decrement('solde_absence', $jours);

        // LOG : soumission demande absence — placé AVANT le return
        LogActivity::log(
            'create',
            'DemandeAbsence',
            $demande->id,
            "Soumission demande absence du {$request->date_debut} au {$request->date_fin} ({$jours} jour(s))"
        );

        return redirect()->route('demande_absences.index')
            ->with('success', "Demande soumise avec succès. {$jours} jour(s) réservé(s) sur votre solde.");
    }

    public function show($id)
    {
        $demande = DemandeAbsence::with(
            'user.departement.direction', 'justificatifAbsence', 'avisAbsence'
        )->findOrFail($id);

        $user           = auth()->user();
        $peutAgir       = $demande->peutDonnerAvis($user);
        $prochainActeur = $demande->prochainActeur();
        $derniereEtape  = $demande->avisAbsence->last()?->type;
        $peutAbandonner = $demande->peutEtreAbandonneePar($user);

        $agentsMemeDepartement = \App\Models\User::where('departement_id', $demande->user->departement_id)
            ->where('id', '!=', $demande->user_id)->get();

        return view('demande_absences.show', compact(
            'demande', 'peutAgir', 'prochainActeur',
            'derniereEtape', 'peutAbandonner', 'agentsMemeDepartement'
        ));
    }

    public function update(Request $request, $id)
    {
        $demande = DemandeAbsence::findOrFail($id);

        if ($demande->user_id !== auth()->id()
            || $demande->statut !== 'en_attente'
            || $demande->avisAbsence()->exists()) {
            return redirect()->route('demande_absences.show', $id)
                ->with('error', 'Modification non autorisée.');
        }

        $request->validate([
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'motif'       => 'required|string|max:500',
            'interimaire' => 'nullable|string|max:255',
        ]);

        $user          = $demande->user;
        $ancienJours   = $demande->nombreJours();
        $nouveauxJours = \Carbon\Carbon::parse($request->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($request->date_fin)) + 1;
        $soldeDisponible = $user->solde_absence + $ancienJours;

        if ($nouveauxJours > $soldeDisponible) {
            return redirect()->back()->withInput()
                ->with('error', "Solde insuffisant : vous demandez {$nouveauxJours} jour(s), il ne vous reste que {$soldeDisponible} jour(s).");
        }

        $demande->update($request->only(['date_debut', 'date_fin', 'motif', 'interimaire']));
        $user->update(['solde_absence' => $soldeDisponible - $nouveauxJours]);

        // LOG : modification demande absence
        LogActivity::log(
            'update',
            'DemandeAbsence',
            $demande->id,
            "Modification demande absence du {$request->date_debut} au {$request->date_fin}"
        );

        return redirect()->route('demande_absences.index')
            ->with('success', 'Demande modifiée avec succès.');
    }

    public function destroy($id)
    {
        $demande = DemandeAbsence::findOrFail($id);

        if ($demande->user_id !== auth()->id()
            || $demande->statut !== 'en_attente'
            || $demande->avisAbsence()->exists()) {
            return redirect()->route('demande_absences.index')
                ->with('error', 'Suppression non autorisée.');
        }

        $demande->user->increment('solde_absence', $demande->nombreJours());

        // LOG : suppression — AVANT delete() car après l'id n'existe plus
        LogActivity::log(
            'delete',
            'DemandeAbsence',
            $demande->id,
            "Suppression demande absence #{$demande->num_demande}"
        );

        $demande->delete();

        return redirect()->route('demande_absences.index')
            ->with('success', 'Demande supprimée.');
    }

    public function abandonner($id)
    {
        $demande = DemandeAbsence::findOrFail($id);

        if (!$demande->peutEtreAbandonneePar(auth()->user())) {
            return redirect()->route('demande_absences.show', $id)
                ->with('error', 'Vous ne pouvez pas abandonner cette demande.');
        }

        $demande->user->increment('solde_absence', $demande->nombreJours());
        $demande->update(['statut' => 'abandonnee']);

        // LOG : abandon demande absence
        LogActivity::log(
            'update',
            'DemandeAbsence',
            $demande->id,
            "Abandon demande absence #{$demande->num_demande}"
        );

        return redirect()->route('demande_absences.index')
            ->with('success', 'Demande abandonnée.');
    }

    public function telecharger($id)
    {
        $demande = DemandeAbsence::with('user.departement.direction', 'avisAbsence')
            ->findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->statut !== 'validee') {
            return redirect()->route('demande_absences.show', $id)
                ->with('error', 'Téléchargement non autorisé.');
        }

        // LOG : téléchargement PDF absence
        LogActivity::log(
            'read',
            'DemandeAbsence',
            $demande->id,
            "Téléchargement autorisation absence #{$demande->num_demande}"
        );

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.absence', compact('demande'));
        return $pdf->download("autorisation_absence_{$demande->num_demande}.pdf");
    }
}