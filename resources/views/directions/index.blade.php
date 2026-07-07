@extends('layouts.app')

@section('title', 'Directions')
@section('page-title', 'Gestion des directions')

@section('content')

{{-- En-tête de page: titre et bouton de création, le bouton renvoie vers la route 'directions.create', générée automatiquement par Route::resource--}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Liste des directions</h5>

    <a href="{{ route('directions.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouvelle direction
    </a>
</div>
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
        <div class="mb-3">
            <input type="text" id="recherche" class="form-control w-25" placeholder="Rechercher...">
        </div>

        <div class="table-responsive">
        <table class="table table-hover" id="tableDepartements">
            <thead class="table-anptic-dark">
                <tr>
                    <th>Libellé court</th>
                    <th>Libellé long</th>
                    <th>Nombre de départements</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
               @forelse($directions as $direction)
                <tr>
                    <td>
                        {{-- badge bg-secondary : pastille grise pour le code court --}}
                        <span class="badge bg-secondary">
                            {{ $direction->libelle_court }}
                        </span>
                    </td>

                    <td>{{ $direction->libelle_long }}</td>

                    <td>
                        {{-- $direction->departements vient de with('departements')
                             .count() : compte le nombre de départements
                             C'est plus efficace que de faire une requête séparée --}}
                        <span class="badge bg-info text-dark">
                            {{ $direction->departements->count() }} département(s)
                        </span>
                    </td>
                    <td>
                        {{-- bouton modifier qui redirige vers la page de modification --}}
                        <a href="{{ route('directions.edit', $direction->id) }}"
                           class="btn btn-sm btn-warning btn-action">
                            Modifier
                        </a>

                        {{-- formulaire pour la suppresion , On ne peut pas faire DELETE avec un <a>, Le navigateur ne supporte que GET et POST, Donc on utilise un formulaire POST avec @method('DELETE') --}}
                        <form action="{{ route('directions.destroy', $direction->id) }}"
                              method="POST"
                              class="d-inline">
                              {{-- d-inline dit que le formulaire reste sur la même ligne que le bouton Modifier --}}

                            {{-- @csrf pour protection de sécurité obligatoire , Génère un champ caché avec un token unique, Laravel vérifie ce token à chaque POST,  Sans @csrf → erreur 419 --}}
                            @csrf
                            @method('DELETE')

                            <button type="submit"
                                    class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer cette direction ? Ses départements seront aussi supprimés.')">
                                 Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        {{-- colspan="4" : cette cellule occupe les 4 colonnes --}}
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Aucune direction trouvée
                    </td>
                </tr>
                @endforelse

            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('recherche').addEventListener('input', function () {
    let valeur = this.value.toLowerCase();
    document.querySelectorAll('#tableDirections tbody tr').forEach(function(ligne) {
        ligne.style.display = ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
    });
});
</script>
@endsection



































