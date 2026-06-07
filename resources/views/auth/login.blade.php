@extends('layouts.guest')

@section('content')

<div class="card shadow">
    <div class="card-header text-center">
        <h3>Sistema de Rentas</h3>
    </div>

    <div class="card-body">

        @if (session('status'))
            <div class="alert alert-success">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>
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

            <div class="mb-3">
                <label class="form-label">Contraseña</label>

                <input
                    type="password"
                    name="password"
                    class="form-control @error('password') is-invalid @enderror"
                    required>

                @error('password')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-check mb-3">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="remember"
                    id="remember">

                <label class="form-check-label" for="remember">
                    Mantener sesión activa
                </label>
            </div>

            @if (Route::has('password.request'))
                <div class="mb-3">
                    <a href="{{ route('password.request') }}">
                        ¿Olvidó su contraseña?
                    </a>
                </div>
            @endif

            <button
                type="submit"
                class="btn btn-primary w-100">
                Iniciar sesión
            </button>

            <hr>

            <div class="text-center">
                <span class="text-muted">
                    ¿No tienes cuenta?
                </span>

                <a href="{{ route('register') }}">
                    Crear cuenta
                </a>
            </div>

        </form>

    </div>
</div>

@endsection