@extends('layouts.app')

@section('title', 'Demandes de congé')
@section('page-title', 'Gestion des demandes de congé administratif')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Liste des demandes de congé</h5>

    <a href="{{ route('demande_conges.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouvelle demande
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

        <table class="table table-hover" id="tableConges">
            <thead class="table-dark">
                <tr>
                    <th>Agent</th>
                    <th>Département</th>
                    <th>Lieu de jouissance</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

                @forelse($demandes as $demande)
                @php
                    $estAuteur  = $demande->user_id === auth()->id();
                    $modifiable = $estAuteur && !$demande->estCompilee();
                    $peutAgirIci = $demande->peutEtreCompileePar(auth()->user());
                @endphp

                <tr>
                    <td>{{ $demande->user->nom ?? '' }} {{ $demande->user->prenom ?? '' }}</td>
                    <td>{{ $demande->user->departement->libelle_court ?? '—' }}</td>
                    <td>{{ $demande->lieu_jouissance }}</td>
                    <td>
                        @if($demande->estCompilee())
                            <span class="badge-statut badge-compilee">Compilée</span>
                        @else
                            <span class="badge-statut badge-en_attente">En attente</span>
                        @endif
                        @if($peutAgirIci)
                            <span class="badge bg-warning text-dark ms-1" style="font-size:10px;">
                                À traiter
                            </span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('demande_conges.show', $demande->id) }}"
                           class="btn btn-sm btn-outline-primary btn-action">
                            <i class="bi bi-eye"></i>
                        </a>

                        @if($modifiable)
                        <a href="{{ route('demande_conges.edit', $demande->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            Modifier
                        </a>
                        @endif

                        @if($modifiable)
                        <form action="{{ route('demande_conges.destroy', $demande->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer cette demande ?')">
                                Supprimer
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="text-center">Aucune demande trouvée</td>
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
    document.querySelectorAll('#tableConges tbody tr').forEach(function(ligne) {
        ligne.style.display = ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
    });
});
</script>
@endsection