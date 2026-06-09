@extends('layouts.app')
@section('title', 'Départements')
@section('page-title', 'Départements')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Gestion des départements</h5>
    <a href="{{ route('departements.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouveau département
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover">
            <thead class="table-dark">
                <tr>
                    <th>Libellé court</th>
                    <th>Libellé long</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($departements as $departement) 
                <tr>
                    <td><span class="badge bg-secondary">{{ $departement->libelle_court }}</span></td>
                    <td>{{ $departement->libelle_long }}</td>

                    <td>
                        <a href="{{ route('departements.edit', $departement->id) }}"
                           class="btn btn-sm btn-success btn-action">Modifier</a>
                        <form action="{{ route('departements.destroy', $departement->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer ce département ?')">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-6">Aucun département trouvé</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection