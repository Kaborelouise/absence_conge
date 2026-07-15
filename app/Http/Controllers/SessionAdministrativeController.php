<?php

namespace App\Http\Controllers;

use App\Models\SessionAdministrative;
use App\Models\User;
use Illuminate\Http\Request;

class SessionAdministrativeController extends Controller
{
    public function index()
    {
        $sessions = SessionAdministrative::with('creePar')->latest()->get();
        return view('sessions_administratives.index', compact('sessions'));
    }

    public function create()
    {
        return view('sessions_administratives.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'libelle'     => 'required|string|max:255',
            'annee'       => 'required|integer|min:2000',
            'date_debut'  => 'required|date',
            'date_fin'    => 'required|date|after:date_debut',
        ]);

        $dateDebut = \Carbon\Carbon::parse($request->date_debut);
        $dateFin   = \Carbon\Carbon::parse($request->date_fin);

        if (SessionAdministrative::chevaucheUneSessionExistante($dateDebut, $dateFin)) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'Cette période chevauche une session déjà existante. Une seule session peut couvrir une date donnée.');
        }

        $session = SessionAdministrative::create([
            'libelle'     => $request->libelle,
            'annee'       => $request->annee,
            'date_debut'  => $dateDebut,
            'date_fin'    => $dateFin,
            // Les 3 flags par défaut (true en base) laissent tout ouvert à la
            // création : c'est au RH de fermer active_conge ponctuellement via
            // "Compiler" plus tard, pas à la création de la session.
            'created_by'  => auth()->id(),
        ]);

        // Réinitialisation du quota annuel pour tous les agents. On fait ça en
        // une seule requête UPDATE (pas de boucle sur chaque User) pour rester
        // performant même avec beaucoup d'agents.
        User::query()->update([
            'solde_absence' => 10,
            'solde_conge'   => 30,
        ]);

        return redirect()
            ->route('sessions_administratives.index')
            ->with('success', "Session « {$session->libelle} » créée avec succès. Les soldes de tous les agents ont été réinitialisés (10 jours d'absence, 30 jours de congé).");
    }

    public function show($id)
    {
        $session = SessionAdministrative::with('creePar')->findOrFail($id);
        return view('sessions_administratives.show', compact('session'));
    }
}