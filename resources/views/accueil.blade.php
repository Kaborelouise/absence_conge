
{{-- resources/views/accueil.blade.php --}}


@extends('layouts.app')
@section('title', 'Accueil')
@section('page-title', 'Accueil')

@section('content')

{{-- Message de bienvenue
 --}}
<div class="text-center mb-5 mt-4">
    <h2 class="fw-bold mb-3">Bienvenue</h2> 
    <p class="text-muted">
        sur la plateforme de demande d'autorisation
        d'absence et de congé de l'ANPTIC
    </p>
</div>


<div class="row justify-content-center g-4">

    <div class="col-md-4">
        <div class="card h-100 text-center p-4">
            <i class="bi bi-person-x text-primary mb-3"
               style="font-size:2.5rem"></i>
            <h5 class="fw-bold mb-2">Autorisation d'absence</h5>
            <p class="text-muted small mb-4">
                Soumettez votre demande en ligne et
                suivez son avancement à chaque étape
            </p>
            <a href="{{ route('demande_absences.create') }}"
               class="btn btn-primary mt-auto">
                Demande d'absence
            </a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100 text-center p-4">
            <i class="bi bi-calendar2-check text-success mb-3"
               style="font-size:2.5rem"></i>
            <h5 class="fw-bold mb-2">Demande de congé administratif</h5>
            <p class="text-muted small mb-4">
                Planifiez votre congé annuel et
                soumettez votre demande directement
            </p>
            <a href="{{ route('demande_conges.create') }}"
               class="btn btn-success mt-auto">
                Demande congé
            </a>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card h-100 text-center p-4">
            <i class="bi bi-calendar2-week text-warning mb-3"
               style="font-size:2.5rem"></i>
            <h5 class="fw-bold mb-2">Demande de jouissance de congé</h5>
            <p class="text-muted small mb-4">
                Soumettez votre demande de
                jouissance de congé en ligne
            </p>
            <a href="{{ route('demande_jouissances.create') }}"
               class="btn btn-warning mt-auto">
                Demande jouissance
            </a>
        </div>
    </div>

</div>
@endsection