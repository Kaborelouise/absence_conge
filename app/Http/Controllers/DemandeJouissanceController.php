<?php

namespace App\Http\Controllers;

use App\Models\DemandeJouissance;
use App\Models\SessionAdministrative;
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
            ->when(in_array($role, ['agent_rh', 'sg', 'dg', 'pca', 'admin']), function ($q) {
                // Ces rôles voient toutes les demandes 
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

    /**
     * MODIFIÉ : même principe de "réservation" du solde que DemandeAbsenceController.
     *
     * Deux corrections par rapport à la version précédente :
     *
     * 1. On ne fait plus confiance à $request->nombre_jour (un simple champ texte
     *    rempli par l'utilisateur). On le RECALCULE côté serveur à partir des
     *    dates, exactement comme le fait DemandeJouissance::nombreJours(). Sans ça,
     *    un agent pourrait saisir des dates couvrant 20 jours tout en indiquant
     *    manuellement "nombre_jour = 1", ce qui contournerait complètement la
     *    vérification du plafond de 30 jours ci-dessous.
     *
     * 2. Vérification du plafond de 30 jours (solde_conge) AVANT création, puis
     *    réservation immédiate (décrémentation) du solde — pas de notion de durée
     *    ici (contrairement à DemandeAbsence), juste un plafond simple à respecter.
     */
    public function store(Request $request)
    {
        $request->validate([
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
        ]);

        $user = auth()->user();

        /**
         * AJOUTÉ : deux conditions cumulatives avant de pouvoir créer une
         * demande de jouissance :
         * 1. Session active pour la jouissance (comme pour absence/congé).
         * 2. Une DemandeConge COMPILÉE doit exister pour cet agent sur cette
         *    même session (règle validée : "on ne peut pas faire de
         *    jouissance si on n'a pas de demande de congé [compilée]").
         *    On vérifie directement via le statut de DemandeConge plutôt que
         *    via CompilationConge, car c'est le statut par AGENT qui compte
         *    ici (une compilation peut être active pour l'année sans que CET
         *    agent particulier ait sa demande dedans, si elle a été créée
         *    après coup ou hors circuit).
         */
        $session = SessionAdministrative::courante();

        if ($session === null || !$session->estOuvertePour('jouissance')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Aucune session n\'est actuellement ouverte pour les demandes de jouissance. Contactez l\'administration.');
        }

        $congeCompile = $user->demandeConges()
            ->where('session_administrative_id', $session->id)
            ->where('statut', 'compilee')
            ->exists();

        if (!$congeCompile) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Vous devez avoir une demande de congé compilée par le service RH avant de pouvoir soumettre une demande de jouissance.');
        }

        // Calcul serveur du nombre de jours, bornes incluses. On ignore
        // volontairement $request->nombre_jour : voir le commentaire ci-dessus.
        $jours = \Carbon\Carbon::parse($request->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($request->date_fin)) + 1;

        if ($jours > $user->solde_conge) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Solde insuffisant : vous demandez {$jours} jour(s), il ne vous reste que {$user->solde_conge} jour(s) de congé.");
        }

        DemandeJouissance::create([
            'num_demande' => time(),
            'date_debut'  => $request->date_debut,
            'date_fin'    => $request->date_fin,
            'nombre_jour' => $jours,
            'user_id'     => $user->id,
            'statut'      => 'en_attente',
            // AJOUTÉ : rattachement à la session courante
            'session_administrative_id' => $session->id,
        ]);

        // Réservation immédiate des jours sur le solde congé
        $user->decrement('solde_conge', $jours);

        return redirect()
            ->route('demande_jouissances.index')
            ->with('success', "Demande de jouissance soumise avec succès. {$jours} jour(s) réservé(s) sur votre solde.");
    }

    public function show($id)
    {
        $demande = DemandeJouissance::with(
            'user.departement.direction',
            'avis'
        )->findOrFail($id);

        $user           = auth()->user();
        $peutAgir       = $demande->peutDonnerAvis($user);
        $prochainActeur = $demande->prochainActeur();
        $derniereEtape  = $demande->avis->last()?->type;
        $peutAbandonner = $demande->peutEtreAbandonneePar($user);

        $agentsMemeDepartement = \App\Models\User::where('departement_id', $demande->user->departement_id)
            ->where('id', '!=', $demande->user_id)
            ->get();

        return view('demande_jouissances.show', compact(
            'demande', 'peutAgir', 'prochainActeur',
            'derniereEtape', 'peutAbandonner', 'agentsMemeDepartement'
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

    /**
     * MODIFIÉ : même ajustement de réservation que DemandeAbsenceController::update.
     * On calcule le solde "disponible" en restituant virtuellement l'ancienne
     * réservation, on vérifie que les nouveaux jours (calculés depuis les nouvelles
     * dates, pas depuis un champ formulaire) rentrent dedans, puis on applique.
     */
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
        ]);

        $user = $demande->user;

        $ancienJours = $demande->nombreJours();

        $nouveauxJours = \Carbon\Carbon::parse($request->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($request->date_fin)) + 1;

        $soldeDisponible = $user->solde_conge + $ancienJours;

        if ($nouveauxJours > $soldeDisponible) {
            return redirect()->back()
                ->withInput()
                ->with('error', "Solde insuffisant : vous demandez {$nouveauxJours} jour(s), il ne vous reste que {$soldeDisponible} jour(s) disponible(s).");
        }

        $demande->update([
            'date_debut'  => $request->date_debut,
            'date_fin'    => $request->date_fin,
            'nombre_jour' => $nouveauxJours,
        ]);

        $user->update(['solde_conge' => $soldeDisponible - $nouveauxJours]);

        return redirect()
            ->route('demande_jouissances.index')
            ->with('success', 'Demande modifiée avec succès.');
    }

    /**
     * MODIFIÉ : restitution des jours réservés puisque la demande est supprimée
     * avant validation.
     */
    public function destroy($id)
    {
        $demande = DemandeJouissance::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()
                ->route('demande_jouissances.index')
                ->with('error', 'Suppression non autorisée.');
        }

        $demande->user->increment('solde_conge', $demande->nombreJours());

        $demande->delete();

        return redirect()
            ->route('demande_jouissances.index')
            ->with('success', 'Demande supprimée.');
    }

    /**
     * MODIFIÉ : même logique, restitution des jours réservés.
     */
    public function abandonner($id)
    {
        $demande = DemandeJouissance::findOrFail($id);

        if (!$demande->peutEtreAbandonneePar(auth()->user())) {
            return redirect()
                ->route('demande_jouissances.show', $id)
                ->with('error', 'Vous ne pouvez pas abandonner cette demande.');
        }

        $demande->user->increment('solde_conge', $demande->nombreJours());

        $demande->update(['abandonnee' => true]);

        return redirect()
            ->route('demande_jouissances.index')
            ->with('success', 'Demande abandonnée.');
    }

    //Télécharger le certificat de cessation de service. Disponible dès que la demande est validée.
     
    public function telechargerCessation($id)
    {
        $demande = DemandeJouissance::with('user.departement.direction', 'avis')
            ->findOrFail($id);

        // Sécurité : seulement l'auteur et seulement si validée
        if ($demande->user_id !== auth()->id() || $demande->statut !== 'validee') {
            return redirect()
                ->route('demande_jouissances.show', $id)
                ->with('error', 'Téléchargement non autorisé.');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.jouissance_cessation',
            compact('demande')
        );

        return $pdf->download("cessation_service_{$demande->num_demande}.pdf");
    }

    // Télécharger le certificat de prise de service. Disponible 2 jours avant la date de fin.

    public function telechargerReprise($id)
    {
        $demande = DemandeJouissance::with('user.departement.direction', 'avis')
            ->findOrFail($id);

        $dateFin    = \Carbon\Carbon::parse($demande->date_fin);
        $aujourdhui = \Carbon\Carbon::today();

        // Sécurité : auteur, validée, et 2 jours avant la fin
        if ($demande->user_id !== auth()->id()
            || $demande->statut !== 'validee'
            || $aujourdhui->lt($dateFin->copy()->subDays(2))) {
            return redirect()
                ->route('demande_jouissances.show', $id)
                ->with('error', 'Le certificat de reprise sera disponible 2 jours avant votre retour ('
                    . $dateFin->copy()->subDays(2)->format('d/m/Y') . ').');
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.jouissance_reprise',
            compact('demande')
        );

        return $pdf->download("reprise_service_{$demande->num_demande}.pdf");
    }

    // Clôturer la demande après le retour de l'agent Disponible uniquement après la date de fin.
    
     
    public function cloturer($id)
    {
        $demande    = DemandeJouissance::findOrFail($id);
        $aujourdhui = \Carbon\Carbon::today();
        $dateFin    = \Carbon\Carbon::parse($demande->date_fin);

        // Sécurité auteur, validée, après la date de fin, pas déjà clôturée
        if ($demande->user_id !== auth()->id()
            || $demande->statut !== 'validee'
            || $aujourdhui->lte($dateFin)
            || $demande->estCloturee()) {
            return redirect()
                ->route('demande_jouissances.show', $id)
                ->with('error', 'Clôture non autorisée. Vérifiez que votre congé est terminé.');
        }

        $demande->update(['cloturee_at' => now()]);

        return redirect()
            ->route('demande_jouissances.show', $id)
            ->with('success', 'Demande clôturée avec succès.');
    }
}