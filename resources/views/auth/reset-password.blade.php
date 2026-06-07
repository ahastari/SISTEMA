@extends('layouts.guest')

@section('content')

<div class="card shadow">

    <div class="card-header text-center">
        <h3>Restablecer contraseña</h3>
    </div>

    <div class="card-body">

        <form method="POST" action="{{ route('password.store') }}">
            @csrf

            <input
                type="hidden"
                name="token"
                value="{{ $request->route('token') }}">

            <div class="mb-3">
                <label class="form-label">
                    Correo electrónico
                </label>

                <input
                    type="email"
                    name="email"
                    value="{{ old('email', $request->email) }}"
                    class="form-control @error('email') is-invalid @enderror"
                    required>

                @error('email')
                    <div class="invalid-feedback">
                        {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">
                    Nueva contraseña
                </label>

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
                <label class="form-label">
                    Confirmar contraseña
                </label>

                <input
                    type="password"
                    name="password_confirmation"
                    class="form-control"
                    required>
            </div>

            <button
                type="submit"
                class="btn btn-success w-100">
                Restablecer contraseña
            </button>

        </form>

    </div>

</div>

@endsection