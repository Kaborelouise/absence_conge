@extends('layouts.app')

@section('title', 'Directions')
@section('page-title', 'Gestion des directions')

@section('content')

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

        <table class="table table-hover" id="tableDirections">
            <thead class="table-dark">
                <tr>
                    <th>Libellé court</th>
                    <th>Libellé long</th>
                    <th>Départements</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($directions as $direction)
                <tr>
                    <td>{{ $direction->libelle_court }}</td>
                    <td>{{ $direction->libelle_long }}</td>
                    <td>
                        <span class="badge bg-secondary">{{ $direction->departements_count }}</span>
                    </td>
                    <td>
                        <a href="{{ route('directions.edit', $direction->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            Modifier
                        </a>

                        <form action="{{ route('directions.destroy', $direction->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer cette direction ?')"
                                    {{ $direction->departements_count > 0 ? 'disabled title=Impossible : départements rattachés' : '' }}>
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center">Aucune direction trouvé</td>
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