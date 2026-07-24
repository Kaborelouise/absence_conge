<?php

namespace App\Http\Controllers;

use App\Models\Direction;
use Illuminate\Http\Request;
use App\Helpers\LogActivity;


class DirectionController extends Controller
{
    // AJOUT : protection Administrateur sur toutes les méthodes
    public function __construct()
    {
        $this->middleware('Administrateur');
    }

    public function index()
    {
        $directions = Direction::with('departements')->withCount('departements')->get();
        return view('directions.index', compact('directions'));
    }

    public function create()
    {
        return view('directions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'libelle_court' => 'required|string|max:50',
            'libelle_long'  => 'required|string|max:255',
        ]);

        Direction::create($request->only(['libelle_court', 'libelle_long']));

        return redirect()
            ->route('directions.index')
            ->with('success', 'Direction créée avec succès');
    }

    public function edit($id)
    {
        $direction = Direction::findOrFail($id);
        return view('directions.edit', compact('direction'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'libelle_court' => 'required|string|max:50',
            'libelle_long'  => 'required|string|max:255',
        ]);

        $direction = Direction::findOrFail($id);
        $direction->update($request->only(['libelle_court', 'libelle_long']));

        return redirect()
            ->route('directions.index')
            ->with('success', 'Direction modifiée avec succès');
    }

    public function destroy($id)
    {
        $direction = Direction::withCount('departements')->findOrFail($id);

        if ($direction->departements_count > 0) {
            return redirect()
                ->route('directions.index')
                ->with('error', "Impossible de supprimer cette direction : {$direction->departements_count} département(s) y sont rattachés.");
        }

        $direction->delete();

        return redirect()
            ->route('directions.index')
            ->with('success', 'Direction supprimée');
    }
}