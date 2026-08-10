@extends('layouts.app')
@section('title', 'Sessions Administratives')
@section('page-title', 'Sessions Administratives')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h5 class="mb-0 fw-bold">Sessions Administratives</h5>
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

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">
                Liste des sessions
            </h5>
            <a href="{{ route('sessions_administratives.create') }}"
               class="btn btn-primary">
                Nouvelle session
            </a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-anptic-dark">
                    <tr>
                        <th>Année</th>
                        <th>Date début</th>
                        <th>Date fin</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($sessions as $session)
                @php
                    $ouverte = $session->estOuverte();
                @endphp
                    <tr>
                        <td>
                            {{ $session->annee }}
                        </td>
                        <td>
                            {{ $session->date_debut->format('d/m/Y') }}
                        </td>
                        <td>
                            {{ $session->date_fin->format('d/m/Y') }}
                        </td>
                        <td>
                            @if($ouverte)
                                <span class="badge bg-success">
                                    Ouvrir
                                </span>
                            @else
                                <span class="badge bg-danger">
                                    Fermée
                                </span>
                            @endif
                        </td>
                        <td>
                            @if($ouverte)
                                <form action="{{ route('sessions_administratives.fermer', $session) }}"
                                    method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        Fermer
                                    </button>
                                </form>
                            @else
                                <form action="{{ route('sessions_administratives.ouvrir', $session) }}"
                                    method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm btn-success">
                                        Ouvrir
                                    </button>
                                </form>
                            @endif

                            <form action="{{ route('sessions_Administratives.destroy', $session) }}"
                                method="POST"
                                class="d-inline"
                                onsubmit="return confirm('Voulez-vous vraiment supprimer cette session ?');">
                                @csrf
                                @method('DELETE')

                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center">
                            Aucune session enregistrée
                        </td>
                    </tr>

                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
<!-- @section('scripts')
<script>
document.getElementById('recherche').addEventListener('input', function () {
    let valeur = this.value.toLowerCase();
    document.querySelectorAll('#tableSessions tbody tr').forEach(function(ligne) {
        ligne.style.display = ligne.textContent.toLowerCase().includes(valeur) ? '' : 'none';
    });
});
</script>
@endsection -->