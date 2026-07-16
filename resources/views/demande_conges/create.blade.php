@extends('layouts.app')
@section('title', 'Nouvelle demande de congé')
@section('page-title', 'Demande de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header text-white text-center" style="background-color:#1B384F; padding: 20px;">
                <h5 class="mb-0">Nouvelle demande de congé</h5>
            </div>
            <div class="card-body p-4">

                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $erreur)
                                <li>{{ $erreur }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{--
                    AJOUTÉ : période de jouissance calculée à partir de la date de
                    prise de service de l'agent connecté (User::periodeJouissance()).
                    Affichée en gris, non modifiable : c'est une information calculée
                    automatiquement, pas un champ que l'agent saisit. Elle permet à
                    l'agent de savoir, avant même de soumettre sa demande, à quelle
                    période il pourra effectivement jouir de son congé une fois
                    celui-ci compilé par le RH.
                --}}
                @php
                    $periode = $user->periodeJouissance();
                @endphp
                <div class="mb-4">
                    <label class="form-label fw-bold">Période de jouissance</label>
                    <input type="text" class="form-control bg-light text-muted" readonly
                           value="@if($periode){{ $periode['debut']->format('d/m/Y') }} → {{ $periode['fin']->format('d/m/Y') }}@else Non calculable (date de prise de service non renseignée) @endif">
                    <small class="text-muted">Calculée automatiquement à partir de votre date de prise de service.</small>
                </div>

                <form action="{{ route('demande_conges.store') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Lieu(x) de jouissance <span class="text-danger">*</span>
                        </label>
                        <div class="row">
                            @php
                                $lieux = ['Afrique', 'Burkina', 'Canada', 'Europe', 'Asie', 'USA'];
                            @endphp
                            @foreach($lieux as $lieu)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input type="checkbox" name="lieu_jouissance[]"
                                               value="{{ $lieu }}"
                                               class="form-check-input"
                                               id="lieu_{{ $lieu }}"
                                               {{ in_array($lieu, old('lieu_jouissance', [])) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="lieu_{{ $lieu }}">
                                            {{ $lieu }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @error('lieu_jouissance')
                            <div class="text-danger" style="font-size: 13px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info" style="font-size: 13px;">
                        <i class="bi bi-info-circle me-1"></i>
                        Cette demande sera compilée par le service RH.
                    </div>

                    <div class="d-flex justify-content-center gap-3">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-send me-1"></i> Soumettre
                        </button>
                        <a href="{{ route('demande_conges.index') }}" class="btn btn-secondary px-4">
                            Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection