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
            ->when(in_array($role, ['agent_rh', 'sg', 'dg', 'pca', 'admin']), function ($q) {
                // Ces rôles voient toutes les demandes
            })
            ->latest()
            ->get();

        return view('demande_absences.index', compact('demandes'));
    }

    public function create()
    {
        $user = auth()->user();
        $agentsMemeDepartement = \App\Models\User::where('departement_id', $user->departement_id)
            ->where('id', '!=', $user->id)->get();
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

        $user = auth()->user();

        // Nombre de jours demandés, bornes incluses (ex: 1er au 6 janvier = 6 jours).
        // On ne peut pas encore utiliser $demande->nombreJours() ici car l'objet
        // DemandeAbsence n'existe pas encore : on calcule donc directement à partir
        // des dates de la requête, avec la même formule que dans le modèle.
        $jours = \Carbon\Carbon::parse($request->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($request->date_fin)) + 1;

        // Vérification du solde AVANT toute création : on bloque la demande si
        // l'agent n'a pas assez de jours restants. 
        if ($jours > $user->solde_absence) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Solde insuffisant : vous demandez {$jours} jour(s), il ne vous reste que {$user->solde_absence} jour(s).");
        }

        DemandeAbsence::create([
            'num_demande' => time(),
            'date_debut'  => $request->date_debut,
            'date_fin'    => $request->date_fin,
            'motif'       => $request->motif,
            'interimaire' => $request->interimaire,
            'user_id'     => $user->id,
            'statut'      => 'en_attente',
        ]);

        // Réservation immédiate des jours : le solde baisse dès la création, avant
        // même que la demande ait parcouru le circuit d'approbation. Si la demande
        // est refusée/abandonnée/supprimée plus tard, ces jours seront restitués.
        $user->decrement('solde_absence', $jours);

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

    public function edit($id)
    {
        $demande = DemandeAbsence::findOrFail($id);
        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()->route('demande_absences.show', $id)
                ->with('error', 'Cette demande ne peut plus être modifiée.');
        }
        return view('demande_absences.edit', compact('demande'));
    }

    public function update(Request $request, $id)
    {
        $demande = DemandeAbsence::findOrFail($id);
        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()->route('demande_absences.show', $id)
                ->with('error', 'Modification non autorisée.');
        }
        $request->validate([
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'motif'       => 'required|string|max:500',
            'interimaire' => 'nullable|string|max:255',
        ]);

        $user = $demande->user;

        // Jours actuellement réservés par cette demande (avant modification)
        $ancienJours = $demande->nombreJours();

        // Jours que la demande occuperait avec les nouvelles dates
        $nouveauxJours = \Carbon\Carbon::parse($request->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($request->date_fin)) + 1;

        // Solde "virtuellement" disponible si on remettait d'abord l'ancienne réservation
        $soldeDisponible = $user->solde_absence + $ancienJours;

        if ($nouveauxJours > $soldeDisponible) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Solde insuffisant : vous demandez {$nouveauxJours} jour(s), il ne vous reste que {$soldeDisponible} jour(s) disponible(s).");
        }

        $demande->update($request->only(['date_debut', 'date_fin', 'motif', 'interimaire']));

        // On applique la nouvelle réservation en une seule écriture
        $user->update(['solde_absence' => $soldeDisponible - $nouveauxJours]);

        return redirect()->route('demande_absences.index')
            ->with('success', 'Demande modifiée avec succès.');
    }

 
    public function destroy($id)
    {
        $demande = DemandeAbsence::findOrFail($id);
        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()->route('demande_absences.index')
                ->with('error', 'Suppression non autorisée.');
        }

        // Restitution des jours réservés à la création, puisque la demande est annulée
        $demande->user->increment('solde_absence', $demande->nombreJours());

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

        // Restitution des jours réservés, la demande n'ira pas au bout du circuit
        $demande->user->increment('solde_absence', $demande->nombreJours());

        $demande->update(['abandonnee' => true]);
        return redirect()->route('demande_absences.index')
            ->with('success', 'Demande abandonnée.');
    }


    //  Télécharger la demande d'absence validée au format PDF.
    //   Visible uniquement par l'auteur quand la demande est validée.
    public function telecharger($id)
    {
        $demande = DemandeAbsence::with(
            'user.departement.direction', 'avisAbsence'
        )->findOrFail($id);

        // Sécurité : seulement l'auteur et seulement si validée
        if ($demande->user_id !== auth()->id() || $demande->statut !== 'validee') {
            return redirect()->route('demande_absences.show', $id)
                ->with('error', 'Téléchargement non autorisé.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.absence',
            compact('demande')
        );

        return $pdf->download("autorisation_absence_{$demande->num_demande}.pdf");
    }
}