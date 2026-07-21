@extends('layouts.app')

@section('title', 'Demandes d\'absence')
@section('page-title', 'Autorisation d\'absence ')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Liste des demandes d'absence</h5>

     <a href="{{ route('demande_absences.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg me-1"></i> Nouvelle demande</a>
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
            <input type="text" id="recherche" class="form-control w-25"
                   placeholder="Rechercher...">
        </div>

        <div class="table-responsive">
        <table class="table table-hover" id="tableAbsences">
            <thead class="table-anptic-dark">
                <tr>
                    <th>N° Demande</th>
                    <th>Agent</th>
                    <th>Département</th>
                    <th>Date début</th>
                    <th>Date fin</th>
                    {{-- <th>Motif</th> --}}
                    <th>Statut</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>

                @forelse($demandes as $demande)
                @php
                    $peutAgirIci = $demande->peutDonnerAvis(auth()->user());

                    $estAuteur = $demande->user_id === auth()->id();

                    $peutSupprimer =
                        $estAuteur
                        && $demande->statut === 'en_attente'
                        && $demande->avisAbsence->isEmpty();

                    $peutAbandonner =
                        $estAuteur
                        && $demande->statut === 'en_attente'
                        && $demande->avisAbsence->isNotEmpty();
                @endphp

                <tr>
                    <td>{{ $demande->num_demande }}</td>
                    <td>{{ $demande->user->nom ?? '' }} {{ $demande->user->prenom ?? '' }}</td>
                    <td>{{ $demande->user->departement->libelle_court ?? '—' }}</td>
                    <td>{{ \Carbon\Carbon::parse($demande->date_debut)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($demande->date_fin)->format('d/m/Y') }}</td>
                    {{-- <td>{{ $demande->motif }}</td> --}}
                    <td>
                        @if($peutAgirIci)
                            <span class="baDGe bg-warning text-dark" style="font-size:11px;">
                                <i class="bi bi-clock me-1"></i> À traiter
                            </span>
                        @else
                            <span class="baDGe-statut baDGe-{{ $demande->statut }}">
                                {{ ucfirst(str_replace('_',' ',$demande->statut)) }}
                            </span>
                        @endif
                    </td>
                    <td>
                        {{-- Bouton Voir : visible par tous --}}
                        <a href="{{ route('demande_absences.show', $demande->id) }}"
                           class="btn btn-sm btn-outline-primary btn-action">Voir
                        </a>

                        {{-- Bouton Modifier : auteur uniquement, demande en attente --}}
                        @if($peutSupprimer || $peutAbandonner)
                        <a href="{{ route('demande_absences.edit', $demande->id) }}"
                            class="btn btn-sm btn-warning btn-action">
                            Modifier
                        </a>
                        @endif

                        {{-- Bouton Supprimer : auteur uniquement, demande en attente --}}
                        @if($peutSupprimer)
                        <form action="{{ route('demande_absences.destroy', $demande->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger btn-action"
                                    onclick="return confirm('Supprimer cette demande ?')">
                                Supprimer
                            </button>
                        </form>
                        @endif

                        @if($peutAbandonner)
                        <form action="{{ route('demande_absences.abandonner', $demande->id) }}"
                              method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-dark btn-action"
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
    document.querySelectorAll('#tableAbsences tbody tr').forEach(function(ligne) {
        ligne.style.display = ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
    });
});
</script>
@endsection