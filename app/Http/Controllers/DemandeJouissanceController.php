<?php

namespace App\Http\Controllers;

use App\Models\DemandeJouissance;
use App\Models\SessionAdministrative;
use Illuminate\Http\Request;
use App\Helpers\LogActivity;

class DemandeJouissanceController extends Controller
{
   


    public function index()
    {
        $user = auth()->user();
        $role = $user->role->libelle;

       
        $sessions            = SessionAdministrative::orderByDesc('annee')->get();
        $sessionCourante     = SessionAdministrative::courante();
        $sessionSelectionnee = request('session_id', $sessionCourante?->id);

        $demandes = DemandeJouissance::with('user.departement.direction', 'avis')
            ->when($sessionSelectionnee, function ($q) use ($sessionSelectionnee) {
                $q->where('session_administrative_id', $sessionSelectionnee);
            })
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
            ->latest()
            ->get();

        return view('demande_jouissances.index', compact('demandes', 'sessions', 'sessionSelectionnee'));
    }



    public function create()
    {
        $user = auth()->user();
        return view('demande_jouissances.create', compact('user'));
    }

    public function store(Request $request)
    {
       
        $request->validate([
            'date_debut'  => 'required|date|after_or_equal:today',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
        ], [
            'date_debut.required'     => 'La date de début est obligatoire.',
            'date_debut.after_or_equal' => 'La date de début ne peut pas être inférieue à aujourd\'hui.',//ajouté pour ne pas permettre la soumission d'une demande dont la date de debut est derrière
            'date_fin.required'       => 'La date de fin est obligatoire.', 
            'date_fin.after_or_equal' => 'La date de fin doit être supérieure ou égale à la date de début.', // after_or_equal est une regle native de laravel 
        ]);


        $user    = auth()->user();


        // un agent ne peut pas avoir 2 demandes de jouissance  en même temps

        $demandeEnCours = DemandeJouissance::where('user_id', $user->id)
            ->where('abandonnee', false)
            ->whereNotIn('statut', ['validee', 'rejetee'])
            ->exists();

        if ($demandeEnCours) {
            return redirect()->back()->withInput()
                ->with('error', 'Vous avez déjà une demande de jouissance en cours de traitement. '
                    . 'Vous devez attendre qu\'elle soit validée, rejetée ou l\'abandonner avant d\'en soumettre une nouvelle.');
        }

        $session = SessionAdministrative::courante();

        if ($session === null || !$session->estOuvertePour('jouissance')) {
            return redirect()->back()->withInput()
                ->with('error', 'Aucune session n\'est actuellement ouverte pour les demandes de jouissance.');
        }

        $congeCompile = $user->demandeConges()
            ->where('session_administrative_id', $session->id)
            ->where('statut', 'compilee')
            ->exists();

        if (!$congeCompile) {
            return redirect()->back()->withInput()
                ->with('error', 'Vous devez avoir une demande de congé compilée avant de soumettre une demande de jouissance.');
        }

        $jours = \Carbon\Carbon::parse($request->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($request->date_fin)) + 1;

        if ($jours > $user->solde_conge) {
            return redirect()->back()->withInput()
                ->with('error', "Solde insuffisant : vous demandez {$jours} jour(s), il ne vous reste que {$user->solde_conge} jour(s).");
        }


        $demande = DemandeJouissance::create([
            'num_demande'               => time(),
            'date_debut'                => $request->date_debut,
            'date_fin'                  => $request->date_fin,
            'nombre_jour'               => $jours,
            'user_id'                   => $user->id,
            'statut'                    => 'en_attente',
            'session_administrative_id' => $session->id,
        ]);

        $user->decrement('solde_conge', $jours);

        // Log pour la soumission demande jouissance 
        LogActivity::log(
            'create',
            'DemandeJouissance',
            $demande->id,
            "Soumission demande jouissance du {$request->date_debut} au {$request->date_fin} ({$jours} jour(s))"
        );

        return redirect()->route('demande_jouissances.index')
            ->with('success', "Demande de jouissance soumise avec succès. {$jours} jour(s) réservé(s).");
    }

    public function show($id)
    {
        $demande = DemandeJouissance::with('user.departement.direction', 'avis')
            ->findOrFail($id);

        $user           = auth()->user();
        $peutAgir       = $demande->peutDonnerAvis($user);
        $prochainActeur = $demande->prochainActeur();
        $derniereEtape  = $demande->avis->last()?->type;
        $peutAbandonner = $demande->peutEtreAbandonneePar($user);

        $agentsMemeDepartement = \App\Models\User::where('departement_id', $demande->user->departement_id)
            ->where('id', '!=', $demande->user_id)->get();

        return view('demande_jouissances.show', compact(
            'demande', 'peutAgir', 'prochainActeur',
            'derniereEtape', 'peutAbandonner', 'agentsMemeDepartement'
        ));
    }

    public function edit($id)
    {
        $demande = DemandeJouissance::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()->route('demande_jouissances.show', $id)
                ->with('error', 'Cette demande ne peut plus être modifiée.');
        }

        return view('demande_jouissances.edit', compact('demande'));
    }

    public function update(Request $request, $id)
    {
        $demande = DemandeJouissance::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()->route('demande_jouissances.show', $id)
                ->with('error', 'Modification non autorisée.');
        }

            $request->validate([
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after_or_equal:date_debut',
        ], [
            'date_debut.required'     => 'La date de début est obligatoire.',
            'date_debut.after_or_equal' => 'La date de début ne peut pas être inférieure à aujourd\'hui.',
            'date_fin.required'       => 'La date de fin est obligatoire.',
            'date_fin.after_or_equal' => 'La date de fin doit être postérieure ou égale à la date de début.',
        ]);

        $user            = $demande->user;
        $ancienJours     = $demande->nombreJours();
        $nouveauxJours   = \Carbon\Carbon::parse($request->date_debut)
            ->diffInDays(\Carbon\Carbon::parse($request->date_fin)) + 1;
        $soldeDisponible = $user->solde_conge + $ancienJours;

        if ($nouveauxJours > $soldeDisponible) {
            return redirect()->back()->withInput()
                ->with('error', "Solde insuffisant : vous demandez {$nouveauxJours} jour(s), il ne vous reste que {$soldeDisponible} jour(s).");
        }

        $demande->update([
            'date_debut'  => $request->date_debut,
            'date_fin'    => $request->date_fin,
            'nombre_jour' => $nouveauxJours,
        ]);

        $user->update(['solde_conge' => $soldeDisponible - $nouveauxJours]);

        // Log pour la modification demande jouissance
        LogActivity::log(
            'update',
            'DemandeJouissance',
            $demande->id,
            "Modification demande jouissance #{$demande->num_demande}"
        );

        return redirect()->route('demande_jouissances.index')
            ->with('success', 'Demande modifiée avec succès.');
    }

    public function destroy($id)
    {
        $demande = DemandeJouissance::findOrFail($id);

        if ($demande->user_id !== auth()->id() || $demande->statut !== 'en_attente') {
            return redirect()->route('demande_jouissances.index')
                ->with('error', 'Suppression non autorisée.');
        }

        $demande->user->increment('solde_conge', $demande->nombreJours());

        LogActivity::log(
            'delete',
            'DemandeJouissance',
            $demande->id,
            "Suppression demande jouissance #{$demande->num_demande}"
        );

        $demande->delete();

        return redirect()->route('demande_jouissances.index')
            ->with('success', 'Demande supprimée.');
    }

    public function abandonner($id)
    {
        $demande = DemandeJouissance::findOrFail($id);

        if (!$demande->peutEtreAbandonneePar(auth()->user())) {
            return redirect()->route('demande_jouissances.show', $id)
                ->with('error', 'Vous ne pouvez pas abandonner cette demande.');
        }

        $demande->user->increment('solde_conge', $demande->nombreJours());
        $demande->update(['abandonnee' => true]);

        // LOG abandon demande jouissance
        LogActivity::log(
            'update',
            'DemandeJouissance',
            $demande->id,
            "Abandon demande jouissance #{$demande->num_demande}"
        );

        return redirect()->route('demande_jouissances.index')
            ->with('success', 'Demande abandonnée.');
    }

    public function telechargerCessation($id)
        {
            $demande = DemandeJouissance::with('user.departement.direction', 'avis')->findOrFail($id);

            $user       = auth()->user();
            $estAuteur  = $demande->user_id === $user->id;
            $estAgentRH = $user->role->libelle === 'Agent RH';

            if ((!$estAuteur && !$estAgentRH) || $demande->statut !== 'validee') {
                return redirect()->route('demande_jouissances.show', $id)
                    ->with('error', 'Téléchargement non autorisé.');
            }

            // LOG de téléchargement pour le certificat de cessation
            LogActivity::log(
                'read',
                'DemandeJouissance',
                $demande->id,
                "Téléchargement certificat cessation #{$demande->num_demande}"
            );

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.jouissance_cessation', compact('demande'));
            return $pdf->download("cessation_service_{$demande->num_demande}.pdf");
        }

    public function telechargerReprise($id)
        {
            $demande    = DemandeJouissance::with('user.departement.direction', 'avis')->findOrFail($id);
            $dateFin    = \Carbon\Carbon::parse($demande->date_fin);
            $aujourdhui = \Carbon\Carbon::today();
            $user       = auth()->user();                                
            $estAuteur  = $demande->user_id === $user->id;                
            $estAgentRH = $user->role->libelle === 'Agent RH';            

            // $bloqueParDelai = !$estAgentRH && $aujourdhui->lt($dateFin->copy()->subDays(2)); 

            if ((!$estAuteur && !$estAgentRH)                              
                || $demande->statut !== 'validee'
                || $bloqueParDelai) {                                     
                return redirect()->route('demande_jouissances.show', $id)
                    ->with('error', 'Le certificat de reprise sera disponible 2 jours avant le retour');
            }

            LogActivity::log('read', 'DemandeJouissance', $demande->id,
                "Téléchargement certificat reprise #{$demande->num_demande}");   

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.jouissance_reprise', compact('demande'));
            return $pdf->download("reprise_service_{$demande->num_demande}.pdf");  
        }

    public function cloturer($id)
    {
        $demande    = DemandeJouissance::findOrFail($id);
        $aujourdhui = \Carbon\Carbon::today();
        $dateFin    = \Carbon\Carbon::parse($demande->date_fin);

        if ($demande->user_id !== auth()->id()
            || $demande->statut !== 'validee'
            || $aujourdhui->lte($dateFin)
            || $demande->estCloturee()) {
            return redirect()->route('demande_jouissances.show', $id)
                ->with('error', 'Clôture non autorisée.');
        }

        $demande->update(['cloturee_at' => now()]);

        // Log pour la clôture demande jouissance
        LogActivity::log(
            'update',
            'DemandeJouissance',
            $demande->id,
            "Clôture demande jouissance #{$demande->num_demande}"
        );

        return redirect()->route('demande_jouissances.show', $id)
            ->with('success', 'Demande clôturée avec succès.');
    }
}