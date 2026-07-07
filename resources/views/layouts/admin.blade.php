<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistema') | {{ \App\Helpers\ContentHelper::getCompanyData('empresa_nombre', 'Panel de Control') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        .required-asterisk {
            color: red;
            margin-left: 2px;
        }
        .required-label::after {
            content: " *";
            color: red;
            font-weight: bold;
        }
    </style>
</head>

<body class="bg-light">

<div class="d-flex">

    <aside class="bg-dark text-white vh-100 p-3 shadow" style="width: 260px; position: sticky; top: 0;">
        
        <div class="text-center mb-3">
            @if(\App\Helpers\ContentHelper::getCompanyData('empresa_logo'))
                <img
                    src="{{ asset('storage/' . \App\Helpers\ContentHelper::getCompanyData('empresa_logo')) }}"
                    class="img-fluid rounded-3"
                    style="max-height: 75px; object-fit: contain;"
                    alt="Logo Corporativo"
                >
            @else
                <div class="d-inline-flex align-items-center justify-content-center bg-secondary bg-opacity-25 rounded-circle" style="width: 60px; height: 60px;">
                    <i class="bi bi-building fs-3 text-white-50"></i>
                </div>
            @endif
        </div>

        <h6 class="text-center text-uppercase fw-bold text-wrap px-2 mb-4 tracking-tight" style="color: #f8fafc; font-size: 13px; letter-spacing: 0.5px;">
            {{ \App\Helpers\ContentHelper::getCompanyData('empresa_nombre', 'Configurar Empresa') }}
        </h6>

        <hr class="text-white-50">

        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('rentas.index') }}" class="nav-link {{ request()->routeIs('rentas.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Rentas
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('inventario.index') }}" class="nav-link {{ request()->routeIs('inventario.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-box-seam me-2"></i>
                    Inventario
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('obras.index') }}" class="nav-link {{ request()->routeIs('obras.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-building me-2"></i>
                    Obras
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('puntoventa.index') }}" class="nav-link {{ request()->routeIs('puntoventa.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-cart3 me-2"></i>
                    Punto de Venta
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-people me-2"></i>
                    Clientes
                </a>
            </li>

            <li class="nav-item mb-2">
                <a href="{{ route('configuracion.index') }}" class="nav-link {{ request()->routeIs('configuracion.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-sliders me-2"></i>
                    Configuración
                </a>
            </li>
        </ul>
    </aside>

    <main class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
        
        <nav class="navbar navbar-expand bg-white shadow-sm px-4 py-2 layout-navbar">
            <div class="container-fluid p-0">
                <div>
                    <h5 class="mb-0 fw-bold text-dark" style="font-size: 16px;">
                        @yield('page-title', 'Panel de Control')
                    </h5>
                </div>

                <div class="d-flex align-items-center">
                    <div class="me-4 text-end d-none d-md-block border-end pe-3">
                        <span class="text-muted d-block" style="font-size: 11px; text-transform: uppercase; font-weight: 700; letter-spacing: 0.5px;">Sucursal Activa</span>
                        <span class="badge bg-dark bg-opacity-10 text-dark fw-bold small rounded-pill px-3">
                            <i class="bi bi-geo-alt-fill text-primary me-1"></i>
                            {{ session('activo_sucursal_nombre', 'Cargando tienda...') }}
                        </span>
                    </div>

                    <div class="text-end me-3">
                        <span class="d-block fw-semibold text-dark" style="font-size: 13px;">{{ Auth::user()->name }}</span>
                        <span class="text-muted text-capitalize d-block" style="font-size: 11px;">{{ Auth::user()->role ?? 'Operador' }}</span>
                    </div>

                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button class="btn btn-outline-danger btn-sm rounded-3 fw-bold px-3" style="font-size: 12px;">
                            <i class="bi bi-box-arrow-right me-1"></i> Cerrar sesión
                        </button>
                    </form>
                </div>
            </div>
        </nav>

        <div class="p-4 flex-grow-1">
            @yield('content')
        </div>
    </main>

</div>

</body>
</html>