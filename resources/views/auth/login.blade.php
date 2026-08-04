@extends('layouts.guest')

@section('content')

<!-- IMPORTAR BOOTSTRAP ICONS CDN DIRECTO -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    :root {
        --login-bg-start: #0f172a;
        --login-bg-end: #1e293b;
        --card-bg: #ffffff;
        --brand-blue: #0284c7;
        --brand-blue-hover: #0369a1;
        --text-label: #334155;
        --input-border: #cbd5e1;
    }

    body {
        background: linear-gradient(135deg, var(--login-bg-start) 0%, var(--login-bg-end) 100%) !important;
        min-height: 100vh;
    }

    .executive-card {
        background: var(--card-bg) !important;
        border: none !important;
        border-radius: 1.25rem;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.4) !important;
        max-width: 440px;
        width: 100%;
        margin: 2rem auto;
    }

    .logo-frame {
        max-height: 75px;
        max-width: 220px;
        object-fit: contain;
    }

    .form-label-exec {
        color: var(--text-label);
        font-size: 0.78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* CONTENEDOR CON POSICIONAMIENTO ABSOLUTO DE PRECISIÓN */
    .field-wrapper {
        position: relative !important;
        display: flex;
        align-items: center;
        width: 100%;
    }

    .field-icon-left {
        position: absolute;
        left: 14px;
        z-index: 5;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    /* ESTILO ESTÉTICO DEL SVG DEL CANDADO */
    .svg-lock-icon {
        width: 18px;
        height: 18px;
        stroke: #64748b;
        fill: none;
        stroke-width: 1.8;
        stroke-linecap: round;
        stroke-linejoin: round;
        transition: stroke 0.2s ease;
    }

    .field-wrapper:focus-within .svg-lock-icon {
        stroke: var(--brand-blue);
    }

    .input-field-custom {
        width: 100% !important;
        padding: 0.75rem 45px 0.75rem 42px !important;
        border: 1px solid var(--input-border) !important;
        border-radius: 0.5rem !important;
        font-size: 0.95rem !important;
        color: #0f172a !important;
        background-color: #f8fafc !important;
        transition: all 0.2s ease;
    }

    .input-field-custom:focus {
        background-color: #ffffff !important;
        border-color: var(--brand-blue) !important;
        box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15) !important;
        outline: none;
    }

    /* BOTÓN INTERACTIVO DEL OJO */
    .btn-eye-absolute {
        position: absolute !important;
        right: 8px !important;
        z-index: 10 !important;
        background: transparent !important;
        border: none !important;
        color: #64748b !important;
        padding: 8px 10px !important;
        cursor: pointer !important;
        border-radius: 0.375rem;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        transition: color 0.2s ease, background-color 0.2s ease;
    }

    .btn-eye-absolute:hover {
        color: var(--brand-blue) !important;
        background-color: rgba(2, 132, 199, 0.08) !important;
    }

    .svg-eye {
        width: 20px;
        height: 20px;
        fill: currentColor;
    }

    .form-check-label-exec {
        color: #475569;
        font-size: 0.85rem;
        font-weight: 500;
        cursor: pointer;
    }

    .btn-exec-submit {
        background-color: var(--brand-blue);
        border: none;
        color: #ffffff;
        font-weight: 700;
        font-size: 0.95rem;
        padding: 0.8rem 1.5rem;
        border-radius: 0.5rem;
        letter-spacing: 0.3px;
        transition: all 0.2s ease;
    }

    .btn-exec-submit:hover {
        background-color: var(--brand-blue-hover);
        color: #ffffff;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(2, 132, 199, 0.3);
    }
</style>

<div class="card executive-card shadow-lg my-auto">
    <div class="card-body p-4 p-sm-5">

        <!-- HEADER Y LOGO -->
        <div class="text-center mb-4">
            @php
                $logoUrl = \App\Helpers\ContentHelper::getLogoActual();
            @endphp

            <div class="mb-3 d-flex justify-content-center align-items-center" style="min-height: 60px;">
                @if(!empty($logoUrl))
                    <img src="{{ $logoUrl }}" alt="Logo Empresa" class="img-fluid logo-frame">
                @else
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-3 p-3" style="width: 56px; height: 56px;">
                        <i class="bi bi-shield-lock-fill fs-3"></i>
                    </div>
                @endif
            </div>

            <h4 class="fw-bold text-dark mb-1">Acceso al Sistema</h4>
            <p class="text-muted small mb-0">Introduce tus credenciales autorizadas</p>
        </div>

        <!-- NOTIFICACIÓN DE ESTADO -->
        @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show rounded-3 small mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="formExecLogin">
            @csrf

            <!-- CAMPO CORREO ELECTRÓNICO -->
            <div class="mb-3">
                <label class="form-label form-label-exec mb-1">Correo Electrónico</label>
                <div class="field-wrapper">
                    <i class="bi bi-envelope-at-fill field-icon-left"></i>
                    <input
                        type="email"
                        name="email"
                        value="{{ old('email') }}"
                        class="form-control input-field-custom @error('email') is-invalid @enderror"
                        placeholder="ejemplo@empresa.com"
                        required
                        autofocus
                        autocomplete="username">
                </div>
                @error('email')
                    <div class="text-danger small mt-1 fw-semibold" style="font-size: 0.8rem;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- CAMPO CONTRASEÑA CON CANDADO ESTÉTICO VECTORIAL + BOTÓN DE OJO -->
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label form-label-exec mb-0">Contraseña</label>
                    <!-- @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-decoration-none small text-primary fw-bold" style="font-size: 0.8rem;">
                            ¿Olvidaste tu contraseña?
                        </a>
                    @endif -->
                </div>

                <div class="field-wrapper">
                    <!-- ICONO DE CANDADO MODERNO Y ESTÉTICO (SVG MINIMALISTA) -->
                    <div class="field-icon-left">
                        <svg class="svg-lock-icon" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            <circle cx="12" cy="16" r="1" fill="currentColor"></circle>
                        </svg>
                    </div>
                    
                    <input
                        type="password"
                        name="password"
                        id="passwordInput"
                        class="form-control input-field-custom @error('password') is-invalid @enderror"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password">
                    
                    <!-- BOTÓN DEL OJO -->
                    <button type="button" class="btn-eye-absolute" id="btnTogglePassword" title="Mostrar contraseña">
                        <svg id="svgEyeOpen" class="svg-eye d-none" viewBox="0 0 16 16">
                            <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                            <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
                        </svg>
                        <svg id="svgEyeClosed" class="svg-eye" viewBox="0 0 16 16">
                            <path d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z"/>
                            <path d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z"/>
                            <path d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.707z"/>
                        </svg>
                    </button>
                </div>

                @error('password')
                    <div class="text-danger small mt-1 fw-semibold" style="font-size: 0.8rem;">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i>{{ $message }}
                    </div>
                @enderror
            </div>

            <!-- RECORDAR SESIÓN -->
            <div class="form-check mb-4 ms-1">
                <input
                    class="form-check-input"
                    type="checkbox"
                    name="remember"
                    id="remember" {{ old('remember') ? 'checked' : '' }}>

                <label class="form-check-label form-check-label-exec" for="remember">
                    Mantener sesión activa en este equipo
                </label>
            </div>

            <!-- BOTÓN ACCESO -->
            <button
                type="submit"
                class="btn btn-exec-submit w-100 shadow-sm"
                id="btnSubmitLogin">
                Iniciar Sesión
            </button>

            <!-- @if (Route::has('register'))
                <div class="text-center mt-4 pt-3 border-top">
                    <span class="text-muted small">¿No dispones de un usuario?</span>
                    <a href="{{ route('register') }}" class="text-primary fw-bold small text-decoration-none ms-1">
                        Solicitar registro
                    </a>
                </div>
            @endif -->

        </form>

    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const btnTogglePassword = document.getElementById('btnTogglePassword');
    const passwordInput = document.getElementById('passwordInput');
    const svgEyeOpen = document.getElementById('svgEyeOpen');
    const svgEyeClosed = document.getElementById('svgEyeClosed');

    if (btnTogglePassword && passwordInput && svgEyeOpen && svgEyeClosed) {
        btnTogglePassword.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            const isPassword = passwordInput.getAttribute('type') === 'password';
            
            if (isPassword) {
                passwordInput.setAttribute('type', 'text');
                svgEyeClosed.classList.add('d-none');
                svgEyeOpen.classList.remove('d-none');
                btnTogglePassword.setAttribute('title', 'Ocultar contraseña');
            } else {
                passwordInput.setAttribute('type', 'password');
                svgEyeOpen.classList.add('d-none');
                svgEyeClosed.classList.remove('d-none');
                btnTogglePassword.setAttribute('title', 'Mostrar contraseña');
            }
        });
    }

    const formExecLogin = document.getElementById('formExecLogin');
    const btnSubmitLogin = document.getElementById('btnSubmitLogin');

    if (formExecLogin && btnSubmitLogin) {
        formExecLogin.addEventListener('submit', function () {
            btnSubmitLogin.disabled = true;
            btnSubmitLogin.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status"></span> Validando...';
        });
    }
});
</script>

@endsection