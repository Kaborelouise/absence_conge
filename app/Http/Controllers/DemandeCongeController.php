<?php

namespace App\Http\Controllers;

use App\Models\DemandeConge;
use App\Models\CompilationConge;
use Illuminate\Http\Request;

class DemandeCongeController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $role = $user->role->libelle;

        $demandes = DemandeConge::with('user.departement.direction', 'avisConge')
            ->when(!in_array($role, ['agent_rh', 'admin']), function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->latest()
            ->get();

        // AJOUT : vérifie si une compilation est active pour l'année en cours
        $annee            = now()->year;
        $compilationActive = CompilationConge::activeParAnnee($annee);

        // AJOUT : indique si le RH peut compiler (role agent_rh uniquement)
        $peutCompiler = $role === 'agent_rh';

        return view('demande_conges.index', compact(
            'demandes',
            'compilationActive', // null si pas compilé, objet si compilé
            'peutCompiler',
            'annee'
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

        DemandeConge::create([
            'num_demande'    => time(),
            'lieu_jouissance' => $request->lieu_jouissance,
            'user_id'         => auth()->id(),
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

    // ================================================================
    // AJOUT : compiler toutes les demandes en_attente de l'année en cours
    // Accessible uniquement par l'agent RH
    // ================================================================
    public function compiler()
    {
        // Sécurité : seul le RH peut compiler
        if (auth()->user()->role->libelle !== 'agent_rh') {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Action non autorisée.');
        }

        $annee = now()->year;

        // Vérifie qu'il n'y a pas déjà une compilation active pour cette année
        if (CompilationConge::activeParAnnee($annee)) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Les demandes de {$annee} sont déjà compilées.");
        }

        // Récupère toutes les demandes en_attente de l'année en cours
        $demandes = DemandeConge::where('statut', 'en_attente')
            ->whereYear('created_at', $annee)
            ->get();

        if ($demandes->isEmpty()) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Aucune demande en attente pour {$annee}.");
        }

        // Passe toutes les demandes à "compilee"
        DemandeConge::where('statut', 'en_attente')
            ->whereYear('created_at', $annee)
            ->update(['statut' => 'compilee']);

        // Enregistre la compilation
        CompilationConge::create([
            'annee'       => $annee,
            'compiled_by' => auth()->id(),
            'compiled_at' => now(),
        ]);

        return redirect()->route('demande_conges.index')
            ->with('success', "{$demandes->count()} demande(s) compilée(s) avec succès.");
    }

    // ================================================================
    // AJOUT : décompiler — remet les demandes en en_attente
    // ================================================================
    public function decompiler()
    {
        if (auth()->user()->role->libelle !== 'agent_rh') {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Action non autorisée.');
        }

        $annee      = now()->year;
        $compilation = CompilationConge::activeParAnnee($annee);

        if (!$compilation) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Aucune compilation active pour {$annee}.");
        }

        // Remet les demandes compilées de cette année en en_attente
        DemandeConge::where('statut', 'compilee')
            ->whereYear('created_at', $annee)
            ->update(['statut' => 'en_attente']);

        // Marque la compilation comme décompilée
        $compilation->update(['decompilee_at' => now()]);

        return redirect()->route('demande_conges.index')
            ->with('success', "Compilation annulée. Les demandes sont de nouveau en attente.");
    }

    // ================================================================
    // AJOUT : télécharger la décision au format PDF
    // Génère le document officiel ANPTIC avec tous les agents compilés
    // ================================================================
    public function telechargerDecision()
    {
        if (auth()->user()->role->libelle !== 'agent_rh') {
            return redirect()->route('demande_conges.index')
                ->with('error', 'Action non autorisée.');
        }

        $annee      = now()->year;
        $compilation = CompilationConge::activeParAnnee($annee);

        if (!$compilation) {
            return redirect()->route('demande_conges.index')
                ->with('error', "Aucune compilation active pour {$annee}.");
        }

        // Récupère toutes les demandes compilées de l'année avec les infos agents
        $demandes = DemandeConge::with('user.departement.direction')
            ->where('statut', 'compilee')
            ->whereYear('created_at', $annee)
            ->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView(
            'pdf.decision_conge',
            compact('demandes', 'annee', 'compilation')
        )->setPaper('A4', 'portrait');

        return $pdf->download("decision_conge_{$annee}.pdf");
    }
}