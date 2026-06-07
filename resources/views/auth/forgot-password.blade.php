@extends('layouts.guest')

@section('content')

<div class="card shadow">

    <div class="card-header text-center">
        <h3>Recuperar contraseña</h3>
    </div>

    <div class="card-body">

        <p class="text-muted">
            Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.
        </p>

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">
                    Correo electrónico
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    required
                    autofocus>

                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <button
                type="submit"
                class="btn btn-primary w-100">
                Enviar enlace de recuperación
            </button>

        </form>

    </div>

</div>

@endsection