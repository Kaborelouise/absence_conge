<?php

namespace App\Http\Controllers;

use App\Models\DemandeAbsence;
use App\Models\SessionAdministrateuristrative;
use Illuminate\Http\Request;

class DemandeAbsenceController extends Controller
{
    public function index()
        {
            $user = auth()->user();
            $role = $user->role->libelle;

            $demandes = DemandeAbsence::with('user.departement.direction', 'avisAbsence')

                // L'Agent voit uniquement ses demandes
                ->when($role === 'Agent', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                })

                // Responsable de département
                ->when(
                    $role === 'Chef Département' || $user->est_responsable_departement,
                    function ($q) use ($user) {
                        $q->whereHas('user', function ($q2) use ($user) {
                            $q2->where('departement_id', $user->departement_id);
                        });
                    }
                )

                // Responsable de direction
                ->when(
                    $role === 'Responsable Direction' || $user->est_Responsable Direction,
                    function ($q) use ($user) {

                        $directionId = $user->departement->direction_id;

                        $q->whereHas('user.departement', function ($q2) use ($directionId) {
                            $q2->where('direction_id', $directionId);
                        });
                    }
                )

            ->when(
            in_array($role, [
                'Administrateuristrateur',
                'Agent RH',
                'SG',
                'DG',
                'PCA',
            ]),
            function ($q) {
                // aucune restriction : ils voient toutes les demandes
            }
        )

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

        /**
         * AJOUTÉ : rattachement à la session Administrateuristrative en cours.
         * Règle validée : les 3 types de demandes (absence/congé/jouissance)
         * sont bloqués si aucune session ne couvre la date du jour, OU si le
         * flag correspondant (ici active_absence) est à false.
         */
        $session = SessionAdministrateuristrative::courante();

        if ($session === null || !$session->estOuvertePour('absence')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Aucune session n\'est actuellement ouverte pour les demandes d\'absence. Contactez l\'Administrateuristration.');
        }

        // Nombre de jours demandés, bornes incluses (ex: 1er au 6 janvier = 6 jours).
        // On ne peut pas encore utiliser $demande->nombreJours() ici car l'objet
        // DemandeAbsence n'existe pas encore : on calcule donc directement à partir
        // des dates de la requête, avec la même formule que dans le modèle.
        $jours = \Carbon\Carbon::parse($request->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($request->date_fin)) + 1;

        // Vérification du solde AVANT toute création : on bloque la demande si
        // l'Agent n'a pas assez de jours restants. C'est le premier rempart contre
        // le dépassement du plafond de 10 jours/an.
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
            // AJOUTÉ : rattachement à la session courante
            'session_Administrateuristrative_id' => $session->id,
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

        $AgentsMemeDepartement = \App\Models\User::where('departement_id', $demande->user->departement_id)
            ->where('id', '!=', $demande->user_id)->get();

        return view('demande_absences.show', compact(
            'demande', 'peutAgir', 'prochainActeur',
            'derniereEtape', 'peutAbandonner', 'AgentsMemeDepartement'
        ));

    }

  

    /**
     * MODIFIÉ : la demande a déjà réservé X jours à sa création. Si l'Agent change
     * les dates, il faut ajuster la réservation :
     * 1. On calcule le solde qu'aurait l'Agent si on annulait l'ancienne réservation
     *    (solde_absence + ancienJours).
     * 2. On vérifie que ce solde disponible couvre le NOUVEAU nombre de jours.
     * 3. On enregistre le nouveau solde = solde disponible - nouveaux jours.
     *
     * Ça évite d'avoir à faire deux opérations séparées (restituer puis redécompter)
     * qui pourraient laisser le solde dans un état incohérent en cas d'erreur au milieu.
     */
    public function update(Request $request, $id)
    {
        $demande = DemandeAbsence::findOrFail($id);

        if (
            $demande->user_id !== auth()->id()
            || $demande->statut !== 'en_attente'
            || $demande->avisAbsence()->exists()
        ) {
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

        // Jours actuellement réservés
        $ancienJours = $demande->nombreJours();

        // Nouveaux jours demandés
        $nouveauxJours = \Carbon\Carbon::parse($request->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($request->date_fin)) + 1;

        // Solde disponible si on annule virtuellement l'ancienne réservation
        $soldeDisponible = $user->solde_absence + $ancienJours;

        if ($nouveauxJours > $soldeDisponible) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Solde insuffisant : vous demandez {$nouveauxJours} jour(s), il ne vous reste que {$soldeDisponible} jour(s) disponible(s).");
        }

        $demande->update($request->only([
            'date_debut',
            'date_fin',
            'motif',
            'interimaire'
        ]));

        // Mise à jour du solde
        $user->update([
            'solde_absence' => $soldeDisponible - $nouveauxJours
        ]);

        return redirect()->route('demande_absences.index')
            ->with('success', 'Demande modifiée avec succès.');
    }

    /**
     * MODIFIÉ : la suppression n'est possible que si la demande est encore
     * "en_attente" (donc jamais validée). Les jours réservés à la création doivent
     * être restitués à l'Agent puisque la demande n'ira jamais au bout.
     */
    public function destroy($id)

    {
        $demande = DemandeAbsence::findOrFail($id);

        if (
            $demande->user_id !== auth()->id()
            || $demande->statut !== 'en_attente'
            || $demande->avisAbsence()->exists()
        ) {
            return redirect()->route('demande_absences.index')
                ->with('error', 'Suppression non autorisée.');
        }

        $demande->user->increment('solde_absence', $demande->nombreJours());

        $demande->delete();

        return redirect()->route('demande_absences.index')
            ->with('success', 'Demande supprimée.');
    }
    /**
     * MODIFIÉ : même logique que destroy() — l'abandon annule la demande avant
     * qu'elle soit validée, donc on restitue les jours réservés.
     */
    public function abandonner($id)
    {
        $demande = DemandeAbsence::findOrFail($id);
        if (!$demande->peutEtreAbandonneePar(auth()->user())) {
            return redirect()->route('demande_absences.show', $id)
                ->with('error', 'Vous ne pouvez pas abandonner cette demande.');
        }

        // Restitution des jours réservés, la demande n'ira pas au bout du circuit
        $demande->user->increment('solde_absence', $demande->nombreJours());

        $demande->update(['statut' => 'abandonnee']);
        return redirect()->route('demande_absences.index')
            ->with('success', 'Demande abandonnée.');
    }

    /**
     * Télécharger la demande d'absence validée au format PDF.
     * Visible uniquement par l'auteur quand la demande est validée.
     */
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