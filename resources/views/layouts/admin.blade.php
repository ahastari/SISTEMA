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

        /* Estilo para el botón flotante */
        .floating-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            box-shadow: 0 4px 12px rgba(0,0,0,0.25);
            transition: all 0.3s ease;
            z-index: 1050;
        }
        .floating-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 6px 20px rgba(0,0,0,0.35);
        }

        /* Submenú activo */
        .nav-link.active-submenu {
            background-color: rgba(13, 110, 253, 0.15);
            border-left: 3px solid #0d6efd;
            color: #0d6efd !important;
        }
    </style>
</head>

<body class="bg-light">

<div class="d-flex">

    <!-- ==================== SIDEBAR ==================== -->
    <aside class="bg-dark text-white vh-100 p-3 shadow" style="width: 260px; position: sticky; top: 0; overflow-y: auto;">
        
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
            <!-- Dashboard -->
            <li class="nav-item mb-2">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-speedometer2 me-2"></i>
                    Dashboard
                </a>
            </li>

            <!-- Rentas -->
            <li class="nav-item mb-2">
                <a href="{{ route('rentas.index') }}" class="nav-link {{ request()->routeIs('rentas.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-file-earmark-text me-2"></i>
                    Rentas
                </a>
            </li>

            <!-- Inventario -->
            <li class="nav-item mb-2">
                <a href="{{ route('inventario.index') }}" class="nav-link {{ request()->routeIs('inventario.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-box-seam me-2"></i>
                    Inventario
                </a>
            </li>

            <!-- ✅ NUEVO: MOVIMIENTOS ENTRE SUCURSALES -->
            <li class="nav-item mb-2">
                <a href="{{ route('movimientos.index') }}" class="nav-link {{ request()->routeIs('movimientos.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-arrow-left-right me-2"></i>
                    Movimientos
                </a>
            </li>

            <!-- Obras -->
            <li class="nav-item mb-2">
                <a href="{{ route('obras.index') }}" class="nav-link {{ request()->routeIs('obras.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-building me-2"></i>
                    Obras
                </a>
            </li>

            <!-- Punto de Venta -->
            <li class="nav-item mb-2">
                <a href="{{ route('puntoventa.index') }}" class="nav-link {{ request()->routeIs('puntoventa.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-cart3 me-2"></i>
                    Punto de Venta
                </a>
            </li>

            <!-- Clientes -->
            <li class="nav-item mb-2">
                <a href="{{ route('clientes.index') }}" class="nav-link {{ request()->routeIs('clientes.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-people me-2"></i>
                    Clientes
                </a>
            </li>

            <!-- Configuración -->
            <li class="nav-item mb-2">
                <a href="{{ route('configuracion.index') }}" class="nav-link {{ request()->routeIs('configuracion.*') ? 'text-primary fw-bold' : 'text-white' }}">
                    <i class="bi bi-sliders me-2"></i>
                    Configuración
                </a>
            </li>
        </ul>

        <hr class="text-white-50">

        <!-- Footer del sidebar -->
        <div class="mt-auto pt-2">
            <small class="text-white-50 d-block text-center" style="font-size: 10px;">
                <i class="bi bi-clock me-1"></i>
                {{ now()->format('d/m/Y H:i') }}
            </small>
        </div>
    </aside>

    <!-- ==================== MAIN CONTENT ==================== -->
    <main class="flex-grow-1 d-flex flex-column" style="min-width: 0;">
        
        <!-- Navbar superior -->
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

        <!-- Contenido principal -->
        <div class="p-4 flex-grow-1">
            @yield('content')
        </div>
    </main>

</div>

<!-- ==================== BOTÓN FLOTANTE DE ACCESO RÁPIDO ==================== -->
<div class="position-fixed bottom-0 end-0 p-4" style="z-index: 1050;">
    <div class="dropdown">
        <button class="btn btn-primary floating-btn d-flex align-items-center justify-content-center" 
                type="button" 
                data-bs-toggle="dropdown" 
                aria-expanded="false"
                id="floatingActionBtn">
            <i class="bi bi-plus-lg fs-3"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 py-2" style="min-width: 220px;">
            <li>
                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('movimientos.create') }}">
                    <span class="bg-primary bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-arrow-left-right text-primary"></i>
                    </span>
                    <div>
                        <span class="d-block fw-semibold">Nuevo Movimiento</span>
                        <small class="text-muted">Transferir entre sucursales</small>
                    </div>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('movimientos.index') }}">
                    <span class="bg-info bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-clock-history text-info"></i>
                    </span>
                    <div>
                        <span class="d-block fw-semibold">Historial</span>
                        <small class="text-muted">Ver todos los movimientos</small>
                    </div>
                </a>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('inventario.create') }}">
                    <span class="bg-success bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-box-seam text-success"></i>
                    </span>
                    <div>
                        <span class="d-block fw-semibold">Nuevo Producto</span>
                        <small class="text-muted">Agregar al inventario</small>
                    </div>
                </a>
            </li>
            <li>
                <a class="dropdown-item d-flex align-items-center py-2" href="{{ route('rentas.create') }}">
                    <span class="bg-warning bg-opacity-10 rounded-circle p-2 me-3">
                        <i class="bi bi-file-earmark-text text-warning"></i>
                    </span>
                    <div>
                        <span class="d-block fw-semibold">Nueva Renta</span>
                        <small class="text-muted">Registrar renta de equipo</small>
                    </div>
                </a>
            </li>
        </ul>
    </div>
</div>

<!-- ==================== SCRIPTS ==================== -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Cerrar el dropdown al hacer clic fuera
        document.addEventListener('click', function(e) {
            const dropdown = document.querySelector('.dropdown');
            const btn = document.getElementById('floatingActionBtn');
            if (dropdown && btn) {
                if (!dropdown.contains(e.target)) {
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) {
                        const bsDropdown = bootstrap.Dropdown.getInstance(btn);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    }
                }
            }
        });
    });
</script>

<!-- Bootstrap JS (asegúrate de que esté cargado) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>