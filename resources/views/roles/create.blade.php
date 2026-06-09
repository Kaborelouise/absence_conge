

@extends('layouts.app')
@section('title', 'Nouveau rôle')
@section('page-title', 'Gestion des rôles')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="bi bi-plus-circle me-2"></i>Créer un rôle
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

                <form action="{{ route('roles.store') }}"
                      method="POST" id="formRole">
                    @csrf

                    <div class="mb-3">
                        <label for="libelle" class="form-label fw-bold">
                            Libellé <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('libelle') is-invalid @enderror"
                               id="libelle"
                               name="libelle"
                               value="{{ old('libelle') }}"
                               placeholder="Ex: agent, chef_departement...">
                        @error('libelle')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-1"></i> Enregistrer
                        </button>
                        <a href="{{ route('roles.index') }}"
                           class="btn btn-secondary">
                            <i class="bi bi-x-lg me-1"></i> Annuler
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
    document.getElementById('formRole').addEventListener('submit', function(e) {
        const libelle = document.getElementById('libelle').value.trim();
        if (libelle === '') {
            e.preventDefault();
            document.getElementById('libelle').classList.add('is-invalid');
            document.getElementById('libelle').focus();
        }
    });
    document.getElementById('libelle').addEventListener('input', function() {
        this.classList.remove('is-invalid');
    });
</script>
@endsection