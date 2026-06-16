<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Sistema')</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

</head>

<body class="bg-light">

<div class="d-flex">

    <!-- Sidebar -->

    <aside
        class="bg-dark text-white vh-100 p-3"
        style="width: 260px;"
    >
        <div class="text-center mb-4">

            <img
                src="{{ asset('images/logo.jpeg') }}"
                class="img-fluid"
                style="max-width: 180px;"
                alt="Logo"
            >

        </div>
        <h3 class="text-center mb-4">

            ANDAMIOS Y MADERA VIRAMONTES

        </h3>

        <hr>

        <ul class="nav flex-column">

            <li class="nav-item mb-2">

                <a
                    href="{{ route('dashboard') }}"
                    class="nav-link sidebar-link"
                >
                    <i class="bi bi-speedometer2 me-2"></i>

                    Dashboard
                </a>

            </li>

            <li class="nav-item mb-2">

                <a
                    href="{{ route('rentas.index') }}"
                    class="nav-link text-white"
                >

                    <i class="bi bi-file-earmark-text me-2"></i>

                    Rentas

                </a>

            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('inventario.index') }}" class="nav-link text-white">
                    <i class="bi bi-box-seam me-2"></i>
                    Inventario
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('obras.index') }}" class="nav-link text-white">
                    <i class="bi bi-building me-2"></i>
                    Obras
                </a>
            </li>

            <li class="nav-item mb-2">

                <a
                    href="#"
                    class="nav-link text-white"
                >

                    <i class="bi bi-cart3 me-2"></i>

                    Punto de Venta

                </a>

            </li>

            <li class="nav-item mb-2">

                <a
                    href="{{ route('clientes.index') }}"
                    class="nav-link text-white"
                >

                    <i class="bi bi-people me-2"></i>

                    Clientes

                </a>

            </li>

        </ul>

    </aside>

    <!-- Contenido -->

    <main class="flex-grow-1">

        <!-- Navbar -->

        <nav class="navbar bg-white shadow-sm px-4">

            <div>

                <h5 class="mb-0">

                    @yield('page-title', 'Panel')

                </h5>

            </div>

            <div class="d-flex align-items-center">

                <span class="me-3">

                    {{ Auth::user()->name }}

                </span>

                <form
                    action="{{ route('logout') }}"
                    method="POST"
                >

                    @csrf

                    <button
                        class="btn btn-outline-danger btn-sm"
                    >

                        Cerrar sesión

                    </button>

                </form>

            </div>

        </nav>

        <div class="p-4">

            @yield('content')

        </div>

    </main>

</div>

</body>

</html>