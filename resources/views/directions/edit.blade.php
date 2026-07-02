{{-- ce fichier c'est  pour permettre de modifier une direction existante ici la route c'est vers update  --}}

@extends('layouts.app')
@section('title', 'Modifier la direction')
@section('page-title', 'Directions')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header text-white text-center" style="background-color:#1B384F; padding: 20px;">
                <h5 class="mb-0">Modifier la direction</h5>
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

                <form action="{{ route('directions.update', $direction->id) }}"
                      method="POST"
                      id="formDirection">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="libelle_court" class="form-label fw-bold">
                            Libellé court <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('libelle_court') is-invalid @enderror"
                            id="libelle_court"
                            name="libelle_court"
                            value="{{ old('libelle_court', $direction->libelle_court) }}"
                            maxlength="50">

                        @error('libelle_court')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="libelle_long" class="form-label fw-bold">
                            Libellé long <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('libelle_long') is-invalid @enderror"
                            id="libelle_long"
                            name="libelle_long"
                            value="{{ old('libelle_long', $direction->libelle_long) }}"
                            maxlength="255">

                        @error('libelle_long')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2 justify-content-center gap-3">
                        {{-- btn-success un bouton vert pour distinguer la modification de la création --}}
                        <button type="submit" class="btn btn-primary  px-4">
                             Enregistrer
                        </button>
                        <a href="{{ route('directions.index') }}"
                           class="btn btn-secondary px-4"> Annuler
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Même logique que create.blade.php
    const form = document.getElementById('formDirection');

    form.addEventListener('submit', function(e) {
        const libelleCourt = document.getElementById('libelle_court').value.trim();
        const libelleLong  = document.getElementById('libelle_long').value.trim();

        if (libelleCourt === '') {
            e.preventDefault();
            document.getElementById('libelle_court').classList.add('is-invalid');
        }

        if (libelleLong === '') {
            e.preventDefault();
            document.getElementById('libelle_long').classList.add('is-invalid');
        }
    });

    ['libelle_court', 'libelle_long'].forEach(function(id) {
        document.getElementById(id).addEventListener('input', function() {
            this.classList.remove('is-invalid');
        });
    });
</script>
@endsection