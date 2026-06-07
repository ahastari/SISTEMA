<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container-fluid">

        <a class="navbar-brand" href="#">
            Sistema de Rentas
        </a>

        <div class="ms-auto">
            <span class="text-white me-3">
                {{ Auth::user()->name }}
            </span>

            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                @csrf
                <button class="btn btn-danger btn-sm">
                    Cerrar sesión
                </button>
            </form>
        </div>

    </div>
</nav>

<div class="container-fluid mt-3">

    @yield('content')

</div>

</body>
</html>