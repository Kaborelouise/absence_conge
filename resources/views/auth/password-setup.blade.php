@extends('layouts.app')
@section('title', 'Définir votre mot de passe')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm mt-5">
            <div class="card-header text-white text-center" style="background-color:#1B384F; padding:20px;">
                <h5 class="mb-0">Définir votre mot de passe</h5>
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

                <form method="POST" action="{{ route('password.setup.store') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control"
                               value="{{ old('email', $email) }}" required readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nouveau mot de passe</label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Confirmer le mot de passe</label>
                        <input type="password" name="password_confirmation" class="form-control" required minlength="8">
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Activer mon compte</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection