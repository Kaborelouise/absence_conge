@extends('layouts.app')

@section('title', 'Départements')
@section('page-title', 'Gestion des départements')

@section('content')

{{-- En-tête de page: titre et bouton de création, le bouton renvoie vers la route 'departements.create', générée automatiquement par Route::resource--}}
<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Liste des départements</h5>

    <a href="{{ route('departements.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouveau département
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

        <table class="table table-hover" id="tableDepartements">
            <thead class="table-anptic-dark">
                <tr>
                    <th>Libellé court</th>
                    <th>Libellé long</th>
                    <th>Direction</th>
                    <th>Utilisateurs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
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
                           class="btn btn-sm btn-warning btn-action">
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
document.getElementById('recherche').addEventListener('input', function () {
    let valeur = this.value.toLowerCase();
    document.querySelectorAll('#tableDepartements tbody tr').forEach(function(ligne) {
        ligne.style.display = ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
    });
});
</script>
@endsection