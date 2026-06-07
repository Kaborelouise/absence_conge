@extends('layouts.app')
@section('title', 'Utilisateurs')
@section('page-title', 'Gestion des utilisateurs')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Gestion des utilisateurs</h5>
    <a href="{{ route('utilisateurs.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouveau
    </a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="mb-3">
            <input type="text" id="recherche" class="form-control w-25"
                   placeholder="Rechercher...">
        </div>
        <table class="table table-hover" id="tableUsers">
            <thead class="table-dark">
                <tr>
                    <th>Nom</th>
                    <th>Prénom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Direction</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($utilisateurs as $user)
                <tr>
                    <td>{{ $user->nom }}</td>
                    <td>{{ $user->prenom }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->role->libelle ?? '—' }}</td>
                    <td>{{ $user->departement->direction->libelle_long ?? '—' }}</td>
                    <td>
                        <a href="{{ route('utilisateurs.edit', $user->id) }}"
                           class="btn btn-sm btn-success btn-action">Modifier</a>
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
                    <td colspan="6" class="text-center text-muted py-4">Aucun utilisateur</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('recherche').addEventListener('input', function() {
        const valeur = this.value.toLowerCase();
        document.querySelectorAll('#tableUsers tbody tr').forEach(function(ligne) {
            ligne.style.display =
                ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
        });
    });
</script>
@endsection