<?php

namespace App\Http\Controllers;

use App\Models\DemandeConge;
use App\Models\CompilationConge;
use App\Models\SessionAdministrative;
use Illuminate\Http\Request;
use App\Helpers\LogActivity;
use DateTime;

class DemandeCongeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->libelle;

        $sessions            = SessionAdministrative::orderByDesc('annee')->get();
        $session             = SessionAdministrative::courante();
        $sessionSelectionnee = request('session_id', $session?->id);
        $estEligibleAuConge = $user->estEligible();
        // dd($estEligibleAuConge);

    $demandes = DemandeConge::with('user.departement.direction', 'avisConge')
        ->when($sessionSelectionnee, function ($q) use ($sessionSelectionnee) {
            $q->where('session_administrative_id', $sessionSelectionnee);
        })
        ->when(!in_array($role, ['Agent RH', 'Administrateur']), function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
        ->latest()
        ->get();

        $compilationActive = $session ? CompilationConge::activeParSession($session->id) : null;
        $peutCompiler       = $role === 'Agent RH';
        $peutSoumettre      = $session !== null && $session->estOuvertePour('conge');

        
        return view('demande_conges.index', compact(
            'demandes', 'compilationActive', 'peutCompiler', 'session',
            'sessions', 'sessionSelectionnee',
            'peutSoumettre', 'estEligibleAuConge' // pour afficher le bouton Soumettre une demande
        ));
    }

    public function create()
    {
        // $user = auth()->user();
        $session = SessionAdministrative::courante();
        return view('demande_conges.create', compact('session'));
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

        $periode_travail = $user->periodeTravail();
        $periode_travail_date_debut = $periode_travail['debut'] ?? null;
        
        $date_debut = $periode_travail['debut'] ?? null;
        $annee_en_cours = sessionAdministrative::courante()->annee;

        $periode_travail_date_debut = null;
        $periode_travail_date_fin = null;
        $periode_travail_date_effet = null;

        if ($date_debut) {
            $date = new DateTime($date_debut);
            
            $date->setDate($annee_en_cours, $date->format('m'), $date->format('d'));
            
            $periode_travail_date_debut = $date->format('d-m-Y');

            $date_fin = clone $date;
            
            $date_fin->modify('+11 months -1 day'); // Ajoute 11 mois et retire 1 jour
            $periode_travail_date_fin = $date_fin->format('d-m-Y');

            $date_effet = clone $date_fin;
            $date_effet->modify('+1 day');
            $periode_travail_date_effet = $date_effet->format('d-m-Y');
        }

        // dd($periode_travail_date_debut, $periode_travail_date_fin, $periode_travail_date_effet);

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

        

        $demande = DemandeConge::create([
            'num_demande'                => time(),
            'lieu_jouissance'            => $request->lieu_jouissance,
            'user_id'                    => $user->id,
            'session_administrative_id'  => $session->id,
            'date_debut'                 => $periode_travail_date_debut,
            'date_fin'                   => $periode_travail_date_fin,
            'date_effet'                 => $periode_travail_date_effet,
        ]);

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
        $session = SessionAdministrative::find($demande->session_administrative_id);

        return view('demande_conges.show', compact('demande', 'peutCompiler', 'peutAbandonner', 'session'));
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

        $date_debut = null;
        $date_fin   = null;
        $date_effet = null;

        $date_debut = $demandes->first()?->date_debut;
        $date_fin   = $demandes->first()?->date_fin;
        $date_effet = $demandes->first()?->date_effet;

        $date_debut_format = new DateTime($date_debut);
        $date_fin_format   = new DateTime($date_fin);
        $date_effet_format = new DateTime($date_effet);

        $date_debut = $date_debut_format->format('d/m/Y');
        $date_fin   = $date_fin_format->format('d/m/Y');
        $date_effet = $date_effet_format->format('d/m/Y');

        LogActivity::log(
            'read',
            'DemandeConge',
            null,
            "Téléchargement décision congé — session {$session->libelle}"
        );

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.decision_conge',
            compact('demandes', 'session', 'compilation', 'date_debut', 'date_fin', 'date_effet')
        )->setPaper('A4', 'portrait');

        return $pdf->download("decision_conge_{$session->annee}.pdf");
    }
}