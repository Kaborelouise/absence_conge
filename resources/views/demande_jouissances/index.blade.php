@extends('layouts.app')

@section('title', 'Demandes de jouissance')
@section('page-title', 'Gestion des demandes de jouissance')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Liste des demandes de jouissance</h5>
    <a href="{{ route('demande_jouissances.create') }}" class="btn btn-primary btn-sm">
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

        <div class="table-responsive">
        <table class="table table-hover" id="tableJouissances">
            {{--table-anptic-dark pour cohérence avec les autres pages --}}
            <thead class="table-anptic-dark">
                <tr>
                    <th>N° Demande</th>
                    <th>Agent</th>
                    <th>Département</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                    <th>Nombre de Jours</th>
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

                @forelse($demandes as $demande)
                @php
                    $peutAgirIci = $demande->peutDonnerAvis(auth()->user());
                    $estAuteur   = $demande->user_id === auth()->id();
                    // Modifiable auteur, en attente, et pas encore abandonnée
                    $modifiable  = $estAuteur && $demande->statut === 'en_attente' && !$demande->abandonnee;
                @endphp

                <tr>
                    <td>{{ $demande->num_demande }}</td>
                    <td>{{ $demande->user->nom ?? '' }} {{ $demande->user->prenom ?? '' }}</td>
                    <td>{{ $demande->user->departement->libelle_court ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }}</td>
                    <td>{{ $demande->nombre_jour }}</td>
                    <td>
                        {{--cas abandonnée affiché en priorité --}}
                        @if($demande->abandonnee)
                            <span class="badge-statut badge-rejetee">Abandonnée</span>
                        @elseif($peutAgirIci)
                            <span class="badge bg-warning text-dark" style="font-size:11px;">
                                <i class="bi bi-clock me-1"></i> À traiter
                            </span>
                        @else
                            <span class="badge-statut badge-{{ $demande->statut }}">
                                {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                            </span>
                        @endif
                    </td>
                    <td>
                        {{-- Bouton Voir visible par tous --}}
                        <a href="{{ route('demande_jouissances.show', $demande->id) }}"
                           class="btn btn-sm btn-outline-primary btn-action">Voir
                        </a>

                        {{-- Boutons Modifier / Supprimer / Abandonner : auteur, en attente, pas abandonnée --}}
                        @if($modifiable)
                        <a href="{{ route('demande_jouissances.edit', $demande->id) }}"
                           class="btn btn-sm btn-success btn-action">
                            Modifier
                        </a>
                        <form action="{{ route('demande_jouissances.destroy', $demande->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer cette demande ?')">
                                Supprimer
                            </button>
                        </form>
                        {{--Bouton Abandonner --}}
                        <form action="{{ route('demande_jouissances.abandonner', $demande->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning btn-action"
                                    onclick="return confirm('Abandonner cette demande ?')">
                                Abandonner
                            </button>
                        </form>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">Aucune demande trouvée</td>
                </tr>
                @endforelse

            </tbody>
        </table>
    </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('recherche').addEventListener('input', function () {
    let valeur = this.value.toLowerCase();
    document.querySelectorAll('#tableJouissances tbody tr').forEach(function(ligne) {
        ligne.style.display = ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
    });
});
</script>
@endsection