<?php

namespace App\Http\Controllers;

use App\Models\ParametreDocument;
use Illuminate\Http\Request;

class ParametreDocumentController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $role = auth()->user()->role->libelle;

            if (!in_array($role, ['Administrateur', 'Agent RH'])) {
                abort(403, "Accès non autorisé.");
            }

            return $next($request);
        });
    }

    public function edit()
    {
        $parametres = ParametreDocument::actuel();

        return view('parametres_documents.edit', compact('parametres'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'ministere_libelle'          => 'required|string|max:255',
            'sigle_ministere'            => 'required|string|max:20',
            'sigle_secretariat'          => 'required|string|max:20',
            'sigle_agence'               => 'required|string|max:20',
            'nb_chiffres_decision'       => 'required|integer|min:1|max:10',
            'nb_chiffres_cessation'      => 'required|integer|min:1|max:10',
            'nb_chiffres_prise_service'  => 'required|integer|min:1|max:10',
            'nb_chiffres_interim'        => 'required|integer|min:1|max:10',
        ]);

        $parametres = ParametreDocument::actuel();
        $parametres->update($request->only([
            'ministere_libelle',
            'sigle_ministere',
            'sigle_secretariat',
            'sigle_agence',
            'nb_chiffres_decision',
            'nb_chiffres_cessation',
            'nb_chiffres_prise_service',
            'nb_chiffres_interim',
        ]));

        return redirect()
            ->route('parametres_documents.edit')
            ->with('success', 'Paramètres des documents mis à jour avec succès.');
    }
}