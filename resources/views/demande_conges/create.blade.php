@extends('layouts.app')
@section('title', 'Nouvelle demande de congé')
@section('page-title', 'Demande de congé')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Soumettre ma demande de congé</h5>
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
                    <input type="hidden" name="user_id" value="{{ auth()->user()->id }}">

                    <div class="mb-3">
                       <label class="form-label fw-bold">
                                    Lieu de jouissance <span class="text-danger">*</span>
                      </label>

                     <select name="lieu_jouissance"
                         class="form-select @error('lieu_jouissance') is-invalid @enderror"
                          required>

                         <option value="">Sélectionner un lieu </option>

                            <option value="Afrique"
                               {{ old('lieu_jouissance') == 'Afrique' ? 'selected' : '' }}> Afrique</option>

                         <option value="Europe" {{ old('lieu_jouissance') == 'Europe' ? 'selected' : '' }}> Europe</option>

                         <option value="Asie"{{ old('lieu_jouissance') == 'Asie' ? 'selected' : '' }}>Asie</option>

                          <option value="Amérique du Nord" {{ old('lieu_jouissance') == 'Amérique du Nord' ? 'selected' : '' }}> Amérique du Nord</option>

                          <option value="Amérique du Sud"{{ old('lieu_jouissance') == 'Amérique du Sud' ? 'selected' : '' }}>Amérique du Sud</option>
                        </select>

                       @error('lieu_jouissance')
                        <div class="invalid-feedback">
                       {{ $message }}
                        </div>
                      @enderror
                   </div>
                <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send me-1"></i> Soumettre
                        </button>
                        <a href="{{ route('demande_conges.index') }}"
                           class="btn btn-secondary">Annuler</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection