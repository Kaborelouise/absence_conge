@extends('layouts.app')
@section('title', 'Rôles')
@section('page-title', 'Gestion des rôles')

@section('content')



<div class="d-flex justify-content-between align-items-center mb-5">
    <h5 class="mb-0 fw-bold">Liste des rôles</h5> 


    <div class="input-group w-25">
        <input type="text" id="recherche" class="form-control form-control-sm" placeholder="">
        <span class="input-group-text"><i class="bi bi-search"></i></span>
    </div>
    <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouveau rôle
    </a>
</div>
<div class="table-responsive">
<div class="card shadow-sm">
    <div class="card-body">
        <table class="table table-hover" id="tableRoles">
            <thead class="table-dark">
                <tr>
                    <th>id</th>
                    <th>Rôle</th>
                    <th>Nombre d'utilisateurs</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td>{{ $role->id }}</td>
                    <td>{{ $role->libelle }}</td>
                    <td>
                        {{-- utilisateurs_count vient de role::withCount('utilisateurs') dans le controller --}}
                        <span class="badge bg-primary">
                            {{ $role->utilisateurs_count }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('roles.edit', $role->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            <i class="bi bi-pencil me-1"></i> Modifier
                        </a>
                        <form action="{{ route('roles.destroy', $role->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer ce rôle ?')">
                                <i class="bi bi-trash me-1"></i> Supprimer
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">
                        <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                        Aucun role trouvé
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
    document.getElementById('recherche').addEventListener('input', function() {
        let valeur = this.value.toLowerCase();
        document.querySelectorAll('#tableRoles tbody tr').forEach(function(ligne) {
            ligne.style.display =
                ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
        });
    });
</script>
@endsection