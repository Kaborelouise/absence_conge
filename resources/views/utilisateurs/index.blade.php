@extends('layouts.app')

@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Liste des utilisateurs</h5>

    <a href="{{ route('utilisateurs.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouvel utilisateur
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm">
    <div class="card-body">

        <div class="mb-3">
            <input type="text" id="recherche" class="form-control w-25" placeholder="Rechercher...">
        </div>



      {{ auth()->user()->role->libelle ?? 'pas de role' }}

        <table class="table table-hover" id="tableUsers">
            <thead class="table-dark">
                <tr>
                    <th>Matricule</th>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Poste</th>
                    <th>Rôle</th>
                    <th>Département</th>
                    <th>Direction</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

                @forelse($users as $user)
                <tr>
                    <td>{{ $user->matricule }}</td>
                    <td>{{ $user->nom }}</td>
                    <td>{{ $user->prenom }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->poste }}</td>
                    <td>{{ ucfirst(str_replace('_', ' ', $user->role->libelle ?? '—')) }}</td>
                    <td>{{ $user->departement->libelle_court ?? '—' }}</td>
                    <td>{{ $user->departement->direction->libelle_court ?? '—' }}</td>
                    <td>
                        <a href="{{ route('utilisateurs.edit', $user->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            Modifier
                        </a>

                        <form action="{{ route('utilisateurs.destroy', $user->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer cet utilisateur ?')">
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center">Aucun utilisateur trouvé</td>
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
    document.querySelectorAll('#tableUsers tbody tr').forEach(function(ligne) {
        ligne.style.display = ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
    });
});
</script>
@endsection