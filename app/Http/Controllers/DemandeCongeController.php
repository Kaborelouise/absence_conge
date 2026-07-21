<?php

namespace App\Http\Controllers;

use App\Models\DemandeConge;
use App\Models\CompilationConge;
use App\Models\SessionAdministrateuristrative;
use Illuminate\Http\Request;

class DemandeCongeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->libelle;

        $demandes = DemandeConge::with('user.departement.direction', 'avisConge')
            ->when(!in_array($role, ['Agent RH', 'Administrateur']), function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->get();

        /**
         * MODIFIÉ : la compilation active se détermine désormais via la
         * SESSION courante (par date), plus fiable que whereYear('created_at')
         * qui mélangeait "année de création" et "campagne Administrateuristrative" —
         * deux notions différentes (une demande créée fin décembre pourrait
         * appartenir à la session de l'année suivante si l'Administrateur ouvre les
         * sessions à cheval sur le calendrier).
         */
        $session = SessionAdministrateuristrative::courante();
        $compilationActive = $session ? CompilationConge::activeParSession($session->id) : null;
        $peutCompiler = $role === 'Agent RH';

        return view('demande_conges.index', compact(
            'demandes',
            'compilationActive',
            'peutCompiler',
            'session'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        return view('demande_conges.create', compact('user'));
    }

    /**
     * MODIFIÉ : ajout du blocage à DEUX conditions cumulatives (règle validée) :
     * 1. Une session Administrateuristrative doit couvrir la date du jour ET avoir son
     *    flag active_conge à true (contrôle du RH/Administrateur).
     * 2. L'Agent doit avoir atteint ses 11 mois de travail effectif depuis sa
     *    date_prise_service (User::estEligibleAuConge()) — sinon on affiche la
     *    date à laquelle il deviendra éligible.
     */
    public function store(Request $request)
    {
        $request->validate([
            'lieu_jouissance'   => 'required|array|min:1',
            'lieu_jouissance.*' => 'in:Afrique,Burkina,Canada,Europe,Asie,USA',
        ], [
            'lieu_jouissance.required' => 'Veuillez sélectionner au moins un lieu.',
            'lieu_jouissance.min'      => 'Veuillez sélectionner au moins un lieu.',
            'lieu_jouissance.*.in'     => 'Lieu de jouissance invalide.',
        ]);

        $user = auth()->user();

        // Condition 1 : session active pour le congé
        $session = SessionAdministrateuristrative::courante();

        if ($session === null || !$session->estOuvertePour('conge')) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Aucune session n\'est actuellement ouverte pour les demandes de congé. Contactez l\'Administrateuristration.');
        }

        // Condition 2 : éligibilité de l'Agent (11 mois de travail effectif)
        if (!$user->estEligibleAuConge()) {
            $periode = $user->periodeOuvrantDroit();
            $dateEligibilite = $periode ? $periode['fin']->copy()->addDay()->format('d/m/Y') : 'inconnue (date de prise de service non renseignée)';

            return redirect()->back()
                ->withInput()
                ->with('error', "Vous n'êtes pas encore éligible au congé Administrateuristratif. Vous le serez à partir du {$dateEligibilite}.");
        }

        DemandeConge::create([
            'num_demande'     => time(),
            'lieu_jouissance' => $request->lieu_jouissance,
            'user_id'         => $user->id,
            // AJOUTÉ : rattachement à la session courante
            'session_Administrateuristrative_id' => $session->id,
        ]);

        return redirect()
            ->route('demande_conges.index')
            ->with('success', 'Demande de congé soumise avec succès.');
    }

    public function show($id)
    {
        $demande = DemandeConge::with(
            'user.departement.direction',
            'avisConge'
        )->findOrFail($id);

        $user           = auth()->user();
        $peutCompiler   = $demande->peutEtreCompileePar($user);
        $peutAbandonner = $demande->peutEtreAbandonneePar($user);

        return view('demande_conges.show', compact('demande', 'peutCompiler', 'peutAbandonner'));
    }

    public function edit($id)
    {
        $demande = DemandeConge::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->estCompilee()) {
            return redirect()
                ->route('demande_conges.show', $id)
                ->with('error', 'Cette demande ne peut plus être modifiée.');
        }

        return view('demande_conges.edit', compact('demande'));
    }

    public function update(Request $request, $id)
    {
        $demande = DemandeConge::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->estCompilee()) {
            return redirect()
                ->route('demande_conges.show', $id)
                ->with('error', 'Modification non autorisée.');
        }

        $request->validate([
            'lieu_jouissance'   => 'required|array|min:1',
            'lieu_jouissance.*' => 'in:Afrique,Burkina,Canada,Europe,Asie,USA',
        ], [
            'lieu_jouissance.required' => 'Veuillez sélectionner au moins un lieu.',
            'lieu_jouissance.min'      => 'Veuillez sélectionner au moins un lieu.',
            'lieu_jouissance.*.in'     => 'Lieu de jouissance invalide.',
        ]);

        $demande->update($request->only(['lieu_jouissance']));

        return redirect()
            ->route('demande_conges.index')
            ->with('success', 'Demande modifiée avec succès.');
    }

    public function destroy($id)
    {
        $demande = DemandeConge::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->estCompilee()) {
            return redirect()
                ->route('demande_conges.index')
                ->with('error', 'Suppression non autorisée.');
        }

        $demande->delete();

        return redirect()
            ->route('demande_conges.index')
            ->with('success', 'Demande supprimée.');
    }

    public function abandonner($id)
    {
        $demande = DemandeConge::findOrFail($id);

        if (!$demande->peutEtreAbandonneePar(auth()->user())) {
            return redirect()
                ->route('demande_conges.show', $id)
                ->with('error', 'Vous ne pouvez pas abandonner cette demande.');
        }

        $demande->update(['abandonnee' => true]);

        return redirect()
            ->route('demande_conges.index')
            ->with('success', 'Demande abandonnée.');
    }

    /**
     * MODIFIÉ EN PROFONDEUR par rapport à la version précédente :
     *
     * 1. On travaille sur la SESSION courante (pas whereYear('created_at')) :
     *    on ne compile que les demandes RATTACHÉES à cette session
     *    (session_Administrateuristrative_id), ce qui est plus juste qu'un simple
     *    filtre par année civile de création.
     * 2. Coexistence CompilationConge + AvisConge (règle confirmée) : en plus
     *    de créer la ligne globale dans compilations_conges, on crée aussi un
     *    AvisConge individuel pour CHAQUE demande compilée, pour garder une
     *    trace par demande (comme avant), en plus de la trace globale.
     * 3. On bascule active_conge à false sur la session : ça ferme les
     *    nouvelles soumissions de congé pour tout le monde (vu dans
     *    DemandeCongeController::store(), qui vérifie estOuvertePour('conge')).
     */
    public function compiler()
    {
        if (auth()->user()->role->libelle !== 'Agent RH') {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Action non autorisée.');
        }

        $session = SessionAdministrateuristrative::courante();

        if ($session === null) {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Aucune session Administrateuristrative n\'est actuellement ouverte.');
        }

        if (CompilationConge::activeParSession($session->id)) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Les demandes de la session « {$session->libelle} » sont déjà compilées.");
        }

        $demandes = DemandeConge::where('session_Administrateuristrative_id', $session->id)
            ->where('statut', 'en_attente')
            ->where('abandonnee', false)
            ->get();

        if ($demandes->isEmpty()) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Aucune demande en attente pour la session « {$session->libelle} ».");
        }

        // Création de la trace globale de compilation
        CompilationConge::create([
            'annee'                      => $session->annee,
            'session_Administrateuristrative_id'  => $session->id,
            'compiled_by'                => auth()->id(),
            'compiled_at'                => now(),
        ]);

        // Création de l'avis individuel + passage au statut "compilee" pour
        // chaque demande de la session
        foreach ($demandes as $demande) {
            \App\Models\AvisConge::create([
                'demande_conge_id' => $demande->id,
                'avis'             => 'favorable',
                'type'             => 'Agent RH',
            ]);

            $demande->update(['statut' => 'compilee']);
        }

        // Fermeture des nouvelles soumissions de congé pour cette session
        $session->update(['active_conge' => false]);

        // AJOUTÉ : on repart sur un état "compilé, pas encore téléchargé"
        session()->forget('decision_telechargee');

        return redirect()->route('demande_conges.index')
            ->with('success', "{$demandes->count()} demande(s) compilée(s) avec succès.");
    }

    /**
     * MODIFIÉ : suppression des AvisConge créés lors de la compilation (pour
     * que DemandeConge::estCompilee() redevienne false, cohérent avec le
     * retour du statut à "en_attente"), et réouverture d'active_conge pour
     * laisser les retardataires soumettre leur demande (règle confirmée).
     */
    public function decompiler()
    {
        if (auth()->user()->role->libelle !== 'Agent RH') {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Action non autorisée.');
        }

        $session = SessionAdministrateuristrative::courante();

        if ($session === null) {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Aucune session Administrateuristrative n\'est actuellement ouverte.');
        }

        $compilation = CompilationConge::activeParSession($session->id);

        if (!$compilation) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Aucune compilation active pour la session « {$session->libelle} ».");
        }

        $demandes = DemandeConge::where('session_Administrateuristrative_id', $session->id)
            ->where('statut', 'compilee')
            ->get();

        foreach ($demandes as $demande) {
            // On supprime l'avis créé à la compilation, pour que estCompilee()
            // (qui se base sur l'existence de cette relation) redevienne false.
            $demande->avisConge()->delete();
            $demande->update(['statut' => 'en_attente']);
        }

        $compilation->update(['decompilee_at' => now()]);

        // AJOUTÉ : on efface le flag, on repart de zéro pour le prochain cycle
        session()->forget('decision_telechargee');

        // Réouverture des soumissions de congé pour les retardataires
        $session->update(['active_conge' => true]);

        return redirect()->route('demande_conges.index')
            ->with('success', "Compilation annulée. {$demandes->count()} demande(s) repassée(s) en attente.");
    }

    /**
     * MODIFIÉ : filtrage par session plutôt que par année civile.
     */
    public function telechargerDecision()
    {
        if (auth()->user()->role->libelle !== 'Agent RH') {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Action non autorisée.');
        }

        $session = SessionAdministrateuristrative::courante();

        if ($session === null) {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Aucune session Administrateuristrative n\'est actuellement ouverte.');
        }

        $compilation = CompilationConge::activeParSession($session->id);

        if (!$compilation) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Aucune compilation active pour la session « {$session->libelle} ».");
        }

        $demandes = DemandeConge::with('user.departement.direction')
            ->where('session_Administrateuristrative_id', $session->id)
            ->where('statut', 'compilee')
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.decision_conge',
            compact('demandes', 'session', 'compilation')
        )->setPaper('A4', 'portrait');

        // AJOUTÉ : marque le passage à l'état "décision téléchargée" pour que
        // la vue index.blade.php n'affiche plus que "Décompiler" ensuite (voir
        // règle confirmée). session()->save() force l'écriture immédiate car
        // cette réponse est un téléchargement de fichier, pas une redirection
        // classique (qui aurait persisté la session automatiquement).
        session(['decision_telechargee' => true]);
        session()->save();

        return $pdf->download("decision_conge_{$session->annee}.pdf");
    }
}