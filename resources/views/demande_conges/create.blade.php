@extends('layouts.app')

@section('title', 'Nouvelle demande de congé')
@section('page-title', 'Demande de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-7">

       

        {{-- Formulaire de demande --}}
        <div class="card shadow-sm">

            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="bi bi-calendar2-check me-2"></i>
                    Nouvelle demande de congé
                </h5>
            </div>

            <div class="card-body">

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

                    <input type="hidden"
                           name="user_id"
                           value="{{ $user->id }}">

                    {{-- Lieu de jouissance --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            Lieu de jouissance
                            <span class="text-danger">*</span>
                        </label>

                        <select name="lieu_jouissance"
                                class="form-select">
                            <option value="">
                                -- Sélectionner --
                            </option>

                            <option value="Afrique"
                                {{ old('lieu_jouissance') == 'Afrique' ? 'selected' : '' }}>
                                Afrique
                            </option>

                            <option value="Asie"
                                {{ old('lieu_jouissance') == 'Asie' ? 'selected' : '' }}>
                                Asie
                            </option>

                            <option value="Amerique"
                                {{ old('lieu_jouissance') == 'Amerique' ? 'selected' : '' }}>
                                Amérique
                            </option>

                            <option value="Europe"
                                {{ old('lieu_jouissance') == 'Europe' ? 'selected' : '' }}>
                                Europe
                            </option>
                        </select>

                        @error('lieu_jouissance')
                            <div class="text-danger small">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit"
                                class="btn btn-success">
                            <i class="bi bi-send me-1"></i>
                            Soumettre la demande
                        </button>

                        <a href="{{ route('demande_conges.index') }}"
                           class="btn btn-secondary">
                            <i class="bi bi-arrow-left me-1"></i>
                            Retour
                        </a>
                    </div>

                </form>

            </div>
        </div>

    </div>
</div>
@endsection