@extends('layouts.guest')

@section('content')

<div class="card shadow">
    <div class="card-header text-center">
        <h3>Crear cuenta</h3>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="mb-3">
                <label class="form-label">Nombre</label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name') }}"
                    class="form-control @error('name') is-invalid @enderror"
                    required>

                @error('name')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Correo electrónico</label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    class="form-control @error('email') is-invalid @enderror"
                    required>

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

            <div class="mb-3">
                <label class="form-label">Confirmar contraseña</label>

                <input
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    required>
            </div>

            <button
                type="submit"
                class="btn btn-success w-100">
                Registrarse
            </button>

            <div class="text-center mt-3">
                <a href="{{ route('login') }}">
                    ¿Ya tienes cuenta? Inicia sesión
                </a>
            </div>

        </form>

    </div>
</div>

@endsection