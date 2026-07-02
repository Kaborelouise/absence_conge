@extends('layouts.app')
@section('title', 'Nouvelle demande de congé')
@section('page-title', 'Demande de congé administratif')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
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

                <form action="{{ route('demande_conges.store') }}" method="POST">
                    @csrf

                    <div class="mb-4">
                        <label class="form-label">Lieu de jouissance</label>
                        <select name="lieu_jouissance"
                                class="form-select @error('lieu_jouissance') is-invalid @enderror"
                                required>
                            <option value="">-- Choisir un lieu --</option>
                            <option value="Afrique" {{ old('lieu_jouissance') == 'Afrique' ? 'selected' : '' }}>Afrique</option>
                            <option value="Asie" {{ old('lieu_jouissance') == 'Asie' ? 'selected' : '' }}>Asie</option>
                            <option value="Amerique" {{ old('lieu_jouissance') == 'Amerique' ? 'selected' : '' }}>Amérique</option>
                            <option value="Europe" {{ old('lieu_jouissance') == 'Europe' ? 'selected' : '' }}>Europe</option>
                        </select>
                        @error('lieu_jouissance')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="alert alert-info" style="font-size:13px;">
                        <i class="bi bi-info-circle me-1"></i>
                        Cette demande sera compilée par le service RH, puis imprimée
                        pour signature hors plateforme.
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