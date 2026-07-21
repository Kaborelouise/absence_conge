@extends('layouts.app')

@section('title', 'Rôles')
@section('page-title', 'Gestion des rôles')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Liste des rôles</h5>

    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouveau rôle
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

        <table class="table table-hover" id="tableRoles">
            <thead class="table-anptic-dark">
                <tr>
                    <th>Libellé</th>
                    <th>Nombre d'utilisateurs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

                @forelse($roles as $role)
                <tr>
                    <td>{{ ucfirst(str_replace('_', ' ', $role->libelle)) }}</td>
                    <td>
                        <span class="baDGe bg-secondary">{{ $role->utilisateurs_count }}</span>
                    </td>
                    <td>
                        <a href="{{ route('roles.edit', $role->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            Modifier
                        </a>

                        <form action="{{ route('roles.destroy', $role->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer ce rôle ?')"
                                    {{ $role->utilisateurs_count > 0 ? 'disabled title=Impossible : rôle encore utilisé' : '' }}>
                                Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="text-center">Aucun rôle trouvé</td>
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
    document.querySelectorAll('#tableRoles tbody tr').forEach(function(ligne) {
        ligne.style.display = ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
    });
});
</script>
@endsection