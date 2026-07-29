<?php

namespace App\Http\Controllers;

use App\Models\DemandeConge;
use App\Models\CompilationConge;
use App\Models\SessionAdministrative;
use Illuminate\Http\Request;
use App\Helpers\LogActivity;

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

        $session           = SessionAdministrative::courante();
        $compilationActive = $session ? CompilationConge::activeParSession($session->id) : null;
        $peutCompiler      = $role === 'Agent RH';

        return view('demande_conges.index', compact(
            'demandes', 'compilationActive', 'peutCompiler', 'session'
        ));
    }

    public function create()
    {
        $user = auth()->user();
        return view('demande_conges.create', compact('user'));
    }

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

        $user    = auth()->user();

        // ====================================================================
        // AJOUTÉ : même règle que pour les absences — un agent ne peut pas
        // avoir 2 demandes de congé actives en même temps. Ici, "en cours"
        // signifie : pas encore compilée par l'Agent RH (estCompilee() ===
        // false, cf. DemandeConge — pas de statut "rejetée" pour ce type de
        // demande, juste "compilée" ou non) ET pas abandonnée.
        // ====================================================================
        $demandeEnCours = DemandeConge::where('user_id', $user->id)
            ->where('abandonnee', false)
            ->whereDoesntHave('avisConge')
            ->exists();

        if ($demandeEnCours) {
            return redirect()->back()->withInput()
                ->with('error', 'Vous avez déjà une demande de congé en cours de traitement. '
                    . 'Vous devez attendre qu\'elle soit compilée ou l\'abandonner avant d\'en soumettre une nouvelle.');
        }

        $session = SessionAdministrative::courante();

        if ($session === null || !$session->estOuvertePour('conge')) {
            return redirect()->back()->withInput()
                ->with('error', 'Aucune session n\'est actuellement ouverte pour les demandes de congé.');
        }

        if (!$user->estEligibleAuConge()) {
            $periode         = $user->periodeOuvrantDroit();
            $dateEligibilite = $periode
                ? $periode['fin']->copy()->addDay()->format('d/m/Y')
                : 'inconnue';

            return redirect()->back()->withInput()
                ->with('error', "Vous n'êtes pas encore éligible au congé administratif. Vous le serez à partir du {$dateEligibilite}.");
        }

        // CORRECTION : capture dans $demande pour avoir l'id
        $demande = DemandeConge::create([
            'num_demande'                => time(),
            'lieu_jouissance'            => $request->lieu_jouissance,
            'user_id'                    => $user->id,
            'session_administrative_id'  => $session->id,
        ]);

        // LOG : soumission demande congé
        LogActivity::log(
            'create',
            'DemandeConge',
            $demande->id,
            "Soumission demande congé #{$demande->num_demande}"
        );

        return redirect()->route('demande_conges.index')
            ->with('success', 'Demande de congé soumise avec succès.');
    }

    public function show($id)
    {
        $demande = DemandeConge::with('user.departement.direction', 'avisConge')
            ->findOrFail($id);

        $user           = auth()->user();
        $peutCompiler   = $demande->peutEtreCompileePar($user);
        $peutAbandonner = $demande->peutEtreAbandonneePar($user);

        return view('demande_conges.show', compact('demande', 'peutCompiler', 'peutAbandonner'));
    }

    public function edit($id)
    {
        $demande = DemandeConge::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->estCompilee()) {
            return redirect()->route('demande_conges.show', $id)
                ->with('error', 'Cette demande ne peut plus être modifiée.');
        }

        return view('demande_conges.edit', compact('demande'));
    }

    public function update(Request $request, $id)
    {
        $demande = DemandeConge::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->estCompilee()) {
            return redirect()->route('demande_conges.show', $id)
                ->with('error', 'Modification non autorisée.');
        }

        $request->validate([
            'lieu_jouissance'   => 'required|array|min:1',
            'lieu_jouissance.*' => 'in:Afrique,Burkina,Canada,Europe,Asie,USA',
        ]);

        $demande->update($request->only(['lieu_jouissance']));

        // LOG : modification demande congé
        LogActivity::log(
            'update',
            'DemandeConge',
            $demande->id,
            "Modification demande congé #{$demande->num_demande}"
        );

        return redirect()->route('demande_conges.index')
            ->with('success', 'Demande modifiée avec succès.');
    }

    public function destroy($id)
    {
        $demande = DemandeConge::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->estCompilee()) {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Suppression non autorisée.');
        }

        // LOG : suppression AVANT delete()
        LogActivity::log(
            'delete',
            'DemandeConge',
            $demande->id,
            "Suppression demande congé #{$demande->num_demande}"
        );

        $demande->delete();

        return redirect()->route('demande_conges.index')
            ->with('success', 'Demande supprimée.');
    }

    public function abandonner($id)
    {
        $demande = DemandeConge::findOrFail($id);

        if (!$demande->peutEtreAbandonneePar(auth()->user())) {
            return redirect()->route('demande_conges.show', $id)
                ->with('error', 'Vous ne pouvez pas abandonner cette demande.');
        }

        $demande->update(['abandonnee' => true]);

        // LOG : abandon demande congé
        LogActivity::log(
            'update',
            'DemandeConge',
            $demande->id,
            "Abandon demande congé #{$demande->num_demande}"
        );

        return redirect()->route('demande_conges.index')
            ->with('success', 'Demande abandonnée.');
    }

    public function compiler()
    {
        if (auth()->user()->role->libelle !== 'Agent RH') {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Action non autorisée.');
        }

        $session = SessionAdministrative::courante();

        if ($session === null) {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Aucune session administrative n\'est actuellement ouverte.');
        }

        if (CompilationConge::activeParSession($session->id)) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Les demandes de la session « {$session->libelle} » sont déjà compilées.");
        }

        $demandes = DemandeConge::where('session_administrative_id', $session->id)
            ->where('statut', 'en_attente')
            ->where('abandonnee', false)
            ->get();

        if ($demandes->isEmpty()) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Aucune demande en attente pour la session « {$session->libelle} ».");
        }

        CompilationConge::create([
            'annee'                     => $session->annee,
            'session_administrative_id' => $session->id,
            'compiled_by'               => auth()->id(),
            'compiled_at'               => now(),
        ]);

        foreach ($demandes as $demande) {
            \App\Models\AvisConge::create([
                'demande_conge_id' => $demande->id,
                'avis'             => 'favorable',
                'type'             => 'agent_rh',
            ]);
            $demande->update(['statut' => 'compilee']);
        }

        $session->update(['active_conge' => false]);

        // LOG : compilation congés
        LogActivity::log(
            'update',
            'DemandeConge',
            null,
            "Compilation de {$demandes->count()} demande(s) de congé — session {$session->libelle}"
        );

        return redirect()->route('demande_conges.index')
            ->with('success', "{$demandes->count()} demande(s) compilée(s) avec succès.");
    }

    public function decompiler()
    {
        if (auth()->user()->role->libelle !== 'Agent RH') {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Action non autorisée.');
        }

        $session     = SessionAdministrative::courante();
        $compilation = $session ? CompilationConge::activeParSession($session->id) : null;

        if (!$compilation) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Aucune compilation active.");
        }

        $demandes = DemandeConge::where('session_administrative_id', $session->id)
            ->where('statut', 'compilee')
            ->get();

        foreach ($demandes as $demande) {
            $demande->avisConge()->delete();
            $demande->update(['statut' => 'en_attente']);
        }

        $compilation->update(['decompilee_at' => now()]);
        $session->update(['active_conge' => true]);

        // LOG : décompilation
        LogActivity::log(
            'update',
            'DemandeConge',
            null,
            "Décompilation de {$demandes->count()} demande(s) — session {$session->libelle}"
        );

        return redirect()->route('demande_conges.index')
            ->with('success', "Compilation annulée. {$demandes->count()} demande(s) repassée(s) en attente.");
    }

    public function telechargerDecision()
    {
        if (auth()->user()->role->libelle !== 'Agent RH') {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Action non autorisée.');
        }

        $session     = SessionAdministrative::courante();
        $compilation = $session ? CompilationConge::activeParSession($session->id) : null;

        if (!$compilation) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Aucune compilation active.");
        }

        $demandes = DemandeConge::with('user.departement.direction')
            ->where('session_administrative_id', $session->id)
            ->where('statut', 'compilee')
            ->get();

        // LOG : téléchargement décision congé
        LogActivity::log(
            'read',
            'DemandeConge',
            null,
            "Téléchargement décision congé — session {$session->libelle}"
        );

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.decision_conge',
            compact('demandes', 'session', 'compilation')
        )->setPaper('A4', 'portrait');

        return $pdf->download("decision_conge_{$session->annee}.pdf");
    }
}