<?php

namespace App\Http\Controllers;

use App\Models\DemandeAbsence;
use App\Models\SessionAdministrative;
use Illuminate\Http\Request;
use App\Helpers\LogActivity;
use App\Models\User;

class DemandeAbsenceController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->libelle;
        $sessions = SessionAdministrative::orderByDesc('annee')->get();
        $sessionCourante = SessionAdministrative::courante();
        $sessionSelectionnee = request(
        'session_id',
        $sessionCourante?->id
        );

            $demandes = DemandeAbsence::with('user.departement.direction', 'avisAbsence')

            ->when($sessionSelectionnee, function ($q) use ($sessionSelectionnee) {
                $q->where('session_administrative_id', $sessionSelectionnee);
            })

            ->when($role === 'Agent', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })

            ->when($role === 'Chef de Département' || $user->est_responsable_departement, function ($q) use ($user) {
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

        return view('demande_absences.index', compact('demandes', 'sessions', 'sessionSelectionnee'));
    }

    public function create()
    {
        $user = auth()->user();

        $AgentsMemeDepartement = $this->agentsPourInterimaire($user);
        $estResponsable        = $this->estResponsable($user);
        return view('demande_absences.create', compact('user', 'AgentsMemeDepartement', 'estResponsable'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after_or_equal:date_debut',
            'motif'   => 'required|string|in:evenement_familliaux,jouissance_de_reliquat_de_congé_paye,convenances_personnelles,autre',
            'motif_autre' => 'required_if:motif,autre|nullable|string|max:255',
            'interimaire' => 'nullable|exists:users,id',
            ], [
            'motif_autre.required_if' => 'Veuillez préciser le motif.',
    ]);


        $user = auth()->user();


        // un agent ne peut pas avoir 2 demandes d'absence en même temps

        $demandeEnCours = DemandeAbsence::where('user_id', $user->id)
            ->whereNotIn('statut', ['validee', 'rejetee', 'abandonnee'])
            ->exists();

        if ($demandeEnCours) {
            return redirect()->back()->withInput()
                ->with('error', 'Vous avez déjà une demande d\'absence en cours de traitement. '
                    . 'Vous devez attendre qu\'elle soit validée, rejetée ou abandonnée avant d\'en soumettre une nouvelle.');
        }

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


        $demande = DemandeAbsence::create([
            'num_demande'                    => time(),
            'date_debut'                     => $request->date_debut,
            'date_fin'                       => $request->date_fin,
            'motif'                          => $request->motif,
            'interimaire_id'  => $request->interimaire,
            'motif_autre'                   => $request->motif === 'autre' ? $request->motif_autre : null,
            'user_id'                        => $user->id,
            'statut'                         => 'en_attente',
            'session_administrative_id'      => $session->id,
        ]);

        $user->decrement('solde_absence', $jours);
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
            'user.departement.direction', 'interimaire', 'justificatifAbsence', 'avisAbsence'
        )->findOrFail($id);

        $user           = auth()->user();
        $peutAgir       = $demande->peutDonnerAvis($user);
        $prochainActeur = $demande->prochainActeur();
        $derniereEtape  = $demande->avisAbsence->last()?->type;
        $peutAbandonner = $demande->peutEtreAbandonneePar($user);

        //liste les intérimaires possible pour l'agent de la demande
        $agentsMemeDepartement = $this->agentsPourInterimaire($demande->user);
        // est-ce ce que la personne qui donne l'avis est un responsable et doit donc voir le champ intérimaire dans le popup 
        $donneurAvisEstResponsable = $this->estResponsable($user);

        $peutTelechargerAutorisation = $demande->statut === 'validee'
             && $demande->peutTelechargerDocuments($user);
    
        $peutTelechargerNoteInterim = $demande->statut === 'validee'
            && $demande->necessiteNoteInterim()
            && $demande->peutTelechargerDocuments($user);

        return view('demande_absences.show', compact(
            'demande', 'peutAgir', 'prochainActeur',
            'derniereEtape', 'peutAbandonner', 'agentsMemeDepartement',
            'donneurAvisEstResponsable', 'peutTelechargerNoteInterim',
            'peutTelechargerAutorisation'
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
        'motif'       => 'required|string|in:evenement_familliaux,jouissance_de_reliquat_de_congé_paye,convenances_personnelles,autre',
        'motif_autre' => 'required_if:motif,autre|nullable|string|max:255',
        'interimaire' => 'nullable|exists:users,id',
    ], [
        'motif_autre.required_if' => 'Veuillez préciser le motif.',
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

        $demande->update([
            'date_debut'  => $request->date_debut,
            'date_fin'    => $request->date_fin,
            'motif'       => $request->motif,
            'motif_autre' => $request->motif === 'autre' ? $request->motif_autre : null,
           'interimaire_id' => $request->interimaire,
        ]);
        $user->update(['solde_absence' => $soldeDisponible - $nouveauxJours]);


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
            $demande = DemandeAbsence::with('user.departement.direction', 'avisAbsence.user', 'interimaire')
                ->findOrFail($id);

           $user = auth()->user();
            $autorise = $demande->peutTelechargerDocuments($user);

            if (!$autorise || $demande->statut !== 'validee') {
                return redirect()->route('demande_absences.show', $id)
                    ->with('error', 'Téléchargement non autorisé.');
            }

            if (!$demande->estCloturee()) {
                $demande->update(['cloturee_at' => now()]);

                LogActivity::log(
                    'update',
                    'DemandeAbsence',
                    $demande->id,
                    "Clôture demande absence #{$demande->num_demande}"
                );
            }

            LogActivity::log(
                'read',
                'DemandeAbsence',
                $demande->id,
                "Téléchargement autorisation absence #{$demande->num_demande}"
            );

            // Jours cumulés validés sur l'année en cours
            $joursCumules = DemandeAbsence::where('user_id', $demande->user_id)
                ->where('statut', 'validee')
                ->whereYear('date_debut', now()->year)
                ->get()
                ->sum(fn ($d) => $d->nombreJours());

            $circuit        = $demande->circuitAttendu();
            $telechargePar  = $user;
            $telechargeLe   = now();

            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.absence', compact(
                'demande', 'joursCumules', 'circuit', 'telechargePar', 'telechargeLe'
            ));

            return $pdf->download("autorisation_absence_{$demande->num_demande}.pdf");
}

    public function edit($id)
    {
        $demande = DemandeAbsence::findOrFail($id);

        // seul le propriétaire peut modifier
        if ($demande->user_id !== auth()->id()
            || $demande->statut !== 'en_attente'
            || $demande->avisAbsence()->exists()) {

            return redirect()->route('demande_absences.show', $id)
                ->with('error', 'Modification non autorisée.');
        }


        $user = auth()->user();
        $AgentsMemeDepartement = $this->agentsPourInterimaire($user);
        $estResponsable        = $this->estResponsable($user);
        return view('demande_absences.edit', compact('demande', 'AgentsMemeDepartement', 'estResponsable'));
    
        //  // //récupération des agents du meme département
        // $AgentsMemeDepartement = User::where('departement_id',
        // auth()->user()->departement_id)
        // ->where('id', "!=", auth()->id())
        // ->get();
        // return view('demande_absences.edit', compact('demande', 'AgentsMemeDepartement'));

        
    }

    //

    private function estResponsable(User $user): bool
    {
        $role = $user->role->libelle;

        return $role=== 'Responsable Direction'
            || $role === 'Chef de Département'
            || $user->est_responsable_departement
            || $user->est_responsable_direction
            ||in_array($role, ['Agent RH', 'SG', 'DG', 'PCA']);
    }

    private function agentsPourInterimaire(User $user)
    {
        return User::where('id', '!=', $user->id)->get();
       
     }

       


            public function telechargerNoteInterim($id)
            {
                $demande = DemandeAbsence::with('user.departement.direction', 'interimaire')
                    ->findOrFail($id);

                $user = auth()->user();
                $autorise = $demande->peutTelechargerDocuments($user);

                if (!$autorise || $demande->statut !== 'validee' || !$demande->necessiteNoteInterim()) {
                    return redirect()->route('demande_absences.show', $id)
                        ->with('error', "Téléchargement de la note d'intérim non autorisé.");
                }

                $signataire = $demande->signataireUser();

                if (!$signataire) {
                    return redirect()->route('demande_absences.show', $id)
                        ->with('error', "Impossible de déterminer le signataire de la note d'intérim.");
                }

                // Le numéro de note n'est généré qu'une seule fois, à la première
               
                if (!$demande->num_note_interim) {
                    $annee   = now()->year;
                    $nbNotes = DemandeAbsence::whereYear('note_interim_generee_at', $annee)
                        ->whereNotNull('num_note_interim')
                        ->count();
                    $numero  = str_pad($nbNotes + 1, 3, '0', STR_PAD_LEFT);

                    $demande->update([
                        'num_note_interim'        => "N°{$annee}-{$numero}MDENP/SG/ANPTIC/SG/DRH",
                        'note_interim_generee_at' => now(),
                    ]);
                }

                LogActivity::log(
                    'read',
                    'DemandeAbsence',
                    $demande->id,
                    "Téléchargement note d'intérim #{$demande->num_demande}"
                );

                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.note_interim', compact('demande', 'signataire'));

                return $pdf->download("note_interim_{$demande->num_demande}.pdf");
            }


}