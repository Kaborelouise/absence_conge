@extends('layouts.app')

@section('title', 'Départements')
@section('page-title', 'Gestion des départements')

@section('content')

{{-- En-tête de page : titre et bouton de création, le bouton renvoie vers la route 'departements.create', générée automatiquement par Route::resource--}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Liste des départements</h5>

    <a href="{{ route('departements.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouveau département
    </a>
</div>

{{-- Messages flash laravel stocke un message temporaire en session via with('success',etc) ou with('error', '...') dans le controller On l'affiche ici une seule fois, puis il disparaît à la prochaine demande
--}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">

        {{-- barre de recherche côté client (pas de requête au serveur).Le script JS en bas filtre les lignes du tableau en JS pur,
            sans recharger la page.
        --}}
        <div class="mb-3">
            <input type="text" id="recherche" class="form-control w-25" placeholder="Rechercher...">
        </div>

        <table class="table table-hover" id="tableDepartements">
            <thead class="table-dark">
                <tr>
                    <th>Libellé court</th>
                    <th>Libellé long</th>
                    <th>Direction</th>
                    <th>Utilisateurs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

                {{--  $departements vient du controller departement::with('direction')->withCount('user')->get()
                  withCount('user') : ajoute automatiquement un attribut
                    virtuel $departement->user_count, qui contient le nombre
                    d'utilisateurs liés, sans avoir à les charger tous.--}}
                @forelse($departements as $departement)
                <tr>
                    <td>{{ $departement->libelle_court }}</td>
                    <td>{{ $departement->libelle_long }}</td>
                    <td>
                        {{ $departement->direction->libelle_court ?? '—' }}
                    </td>
                    <td>
                        <span class="badge bg-secondary">{{ $departement->user_count }}</span>
                    </td>
                    <td>
                        <a href="{{ route('departements.edit', $departement->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            Modifier
                        </a>

                        <form action="{{ route('departements.destroy', $departement->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer ce département ?')"
                                    {{ $departement->user_count > 0 ? 'disabled title=Impossible : utilisateurs rattachés' : '' }}>
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Aucun département trouvé</td>
                </tr>
                @endforelse

            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Filtre en temps réel  à chaque saisie dans le champ de recherche,
// ca veut dire on filtre en fonction de la recherche
document.getElementById('recherche').addEventListener('input', function () {
    let valeur = this.value.toLowerCase();
    document.querySelectorAll('#tableDepartements tbody tr').forEach(function(ligne) {
        ligne.style.display = ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
    });
});
</script>
@endsection