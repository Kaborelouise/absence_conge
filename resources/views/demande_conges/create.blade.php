@extends('layouts.app')
@section('title', 'Nouvelle demande de congé')
@section('page-title', 'Demande de congé administratif')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header card-header-anptic text-center" style="padding: 20px;">
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

                <form action="{{ route('demande_conges.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label fw-bold">
                            Lieu(x) de jouissance
                            <span class="text-danger">*</span>

                        </label>

                        @php
                            
                            $lieux = ['Afrique', 'Burkina', 'Canada', 'Europe', 'Asie', 'USA'];
                          
                            $anciensChoix = old('lieu_jouissance', []);
                        @endphp
                        <div class="row">
                            @foreach($lieux as $lieu)
                            <div class="col-6 col-md-4 mb-2">
                                <div class="form-check">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           name="lieu_jouissance[]"
                                           value="{{ $lieu }}"
                                           id="lieu_{{ $lieu }}"
                                           {{ in_array($lieu, $anciensChoix) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="lieu_{{ $lieu }}">
                                        {{ $lieu }}
                                    </label>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @error('lieu_jouissance')
                            <div class="text-danger mt-1" style="font-size:12px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info" style="font-size:13px;">
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